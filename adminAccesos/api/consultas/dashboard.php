<?php
/**
 * api/consultas/dashboard.php — Consultas del dashboard (solo lectura).
 *
 * Solo define funciones: no imprime nada ni lee $_POST. Las usa
 * api/operacionesDashboard.php, que es quien valida la petición y responde.
 *
 * Estatus de usuario: se considera activo únicamente 'activo' (sin importar
 * espacios ni mayúsculas, igual que el login de las demás aplicaciones);
 * cualquier otro valor cuenta como inactivo.
 */

/**
 * Totales de las tarjetas del dashboard.
 */
function consultarResumenDashboard(PDO $conn): array
{
    $consulta = $conn->query(
        "SELECT
             (SELECT COUNT(*) FROM usuarios
               WHERE LOWER(LTRIM(RTRIM(estatus))) = N'activo')  AS usuariosActivos,
             (SELECT COUNT(*) FROM usuarios
               WHERE LOWER(LTRIM(RTRIM(estatus))) <> N'activo') AS usuariosInactivos,
             (SELECT COUNT(*) FROM aplicaciones WHERE activo = 1) AS aplicacionesActivas,
             (SELECT COUNT(*) FROM aplicaciones)                  AS aplicacionesTotal,
             (SELECT COUNT(*) FROM modulos WHERE activo = 1)      AS modulosActivos,
             (SELECT COUNT(*) FROM modulos)                       AS modulosTotal,
             (SELECT COUNT(*) FROM roles WHERE activo = 1)        AS rolesActivos,
             (SELECT COUNT(*) FROM roles)                         AS rolesTotal,
             (SELECT COUNT(*) FROM permisos)                      AS permisosTotal"
    );

    $fila = $consulta->fetch(PDO::FETCH_ASSOC) ?: [];
    $consulta->closeCursor();

    /* PDO_SQLSRV devuelve los números como texto. */
    return array_map('intval', $fila);
}

/**
 * Excepciones individuales que vencen entre hoy y hoy + $dias (inclusive).
 * Solo usuario_permisos tiene vigencia; los roles no vencen.
 */
function consultarExcepcionesPorVencer(PDO $conn, int $dias): array
{
    /* $dias es una constante del servidor (DIAS_VIGENCIA_PROXIMA), no un dato
       del usuario; se convierte a entero antes de insertarlo. */
    $dias = max(0, $dias);

    $consulta = $conn->query(
        "SELECT up.nominaUsuario,
                u.nombre,
                m.codigoSistema,
                m.codigoModulo,
                p.accion,
                up.tipo,
                CONVERT(char(10), up.vigenciaHasta, 23) AS vigenciaHasta,
                DATEDIFF(DAY, CAST(SYSDATETIME() AS date), up.vigenciaHasta) AS diasRestantes
         FROM usuario_permisos AS up
         INNER JOIN usuarios AS u ON u.nominaUsuario = up.nominaUsuario
         INNER JOIN permisos AS p ON p.idPermiso = up.idPermiso
         INNER JOIN modulos AS m  ON m.idModulo = p.idModulo
         WHERE up.vigenciaHasta >= CAST(SYSDATETIME() AS date)
           AND up.vigenciaHasta <= DATEADD(DAY, " . $dias . ", CAST(SYSDATETIME() AS date))
         ORDER BY up.vigenciaHasta, u.nombre, m.codigoSistema, m.codigoModulo, p.accion"
    );

    $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    foreach ($filas as &$fila) {
        $fila['diasRestantes'] = (int) $fila['diasRestantes'];
    }
    unset($fila);

    return $filas;
}

/**
 * Usuarios que no tienen ningún rol ni ninguna excepción individual.
 */
function consultarUsuariosSinAcceso(PDO $conn): array
{
    $consulta = $conn->query(
        "SELECT u.nominaUsuario,
                u.nombre,
                LOWER(LTRIM(RTRIM(u.estatus))) AS estatus
         FROM usuarios AS u
         WHERE NOT EXISTS (SELECT 1 FROM usuario_roles AS ur
                           WHERE ur.nominaUsuario = u.nominaUsuario)
           AND NOT EXISTS (SELECT 1 FROM usuario_permisos AS up
                           WHERE up.nominaUsuario = u.nominaUsuario)
         ORDER BY u.nombre, u.nominaUsuario"
    );

    $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    return $filas;
}

/**
 * Revisiones de consistencia. Son un subconjunto de sql/01_diagnostico.sql:
 * se omiten los duplicados y las asignaciones huérfanas porque en
 * ELECTRONICOSDB ya los impiden las restricciones UNIQUE y las llaves foráneas.
 *
 * Cada consulta devuelve una sola columna, "detalle", con la clave legible del
 * elemento afectado. Nunca se lee passwordHash: solo se compara su formato.
 *
 * severidad: 'error' (hay que corregir), 'aviso' (conviene revisar),
 *            'info' (se reporta, no es un error).
 */
function consultarInconsistencias(PDO $conn): array
{
    /* Nowdoc ('SQL'): el SQL lleva signos $ que PHP no debe interpretar. */
    $revisiones = [
        [
            'clave'       => 'rol_otra_aplicacion',
            'titulo'      => 'Roles con permisos de otra aplicación',
            'descripcion' => 'Un rol solo debe tener permisos de módulos de su propia aplicación.',
            'severidad'   => 'error',
            'sql'         => <<<'SQL'
                SELECT r.codigoSistema + N'.' + r.codigoRol + N' → '
                       + m.codigoSistema + N'.' + m.codigoModulo + N'.' + p.accion AS detalle
                FROM rol_permisos AS rp
                INNER JOIN roles AS r    ON r.idRol = rp.idRol
                INNER JOIN permisos AS p ON p.idPermiso = rp.idPermiso
                INNER JOIN modulos AS m  ON m.idModulo = p.idModulo
                WHERE r.codigoSistema <> m.codigoSistema
                ORDER BY detalle
                SQL
        ],
        [
            'clave'       => 'password_sin_hash',
            'titulo'      => 'Usuarios con contraseña sin cifrar',
            'descripcion' => 'Su contraseña no está guardada como hash, así que no pueden iniciar sesión. Conviene restablecerla.',
            'severidad'   => 'error',
            'sql'         => <<<'SQL'
                SELECT nominaUsuario + N' — ' + nombre AS detalle
                FROM usuarios
                WHERE passwordHash NOT LIKE N'$2y$%'
                  AND passwordHash NOT LIKE N'$argon2%'
                ORDER BY nombre
                SQL
        ],
        [
            'clave'       => 'modulo_sin_ver',
            'titulo'      => 'Módulos sin la acción "ver"',
            'descripcion' => 'Por convención "ver" es el permiso de entrada; sin él, el módulo queda inalcanzable.',
            'severidad'   => 'aviso',
            'sql'         => <<<'SQL'
                SELECT m.codigoSistema + N'.' + m.codigoModulo AS detalle
                FROM modulos AS m
                WHERE NOT EXISTS (SELECT 1 FROM permisos AS p
                                  WHERE p.idModulo = m.idModulo AND p.accion = N'ver')
                ORDER BY detalle
                SQL
        ],
        [
            'clave'       => 'estatus_no_reconocido',
            'titulo'      => 'Usuarios con estatus no reconocido',
            'descripcion' => 'El estatus debería ser "activo" o "inactivo".',
            'severidad'   => 'aviso',
            'sql'         => <<<'SQL'
                SELECT nominaUsuario + N' — "' + estatus + N'"' AS detalle
                FROM usuarios
                WHERE LOWER(LTRIM(RTRIM(estatus))) NOT IN (N'activo', N'inactivo')
                ORDER BY nominaUsuario
                SQL
        ],
        [
            'clave'       => 'inactivo_con_asignaciones',
            'titulo'      => 'Usuarios inactivos con asignaciones vigentes',
            'descripcion' => 'No pueden entrar, pero conservan roles o excepciones sin vencer.',
            'severidad'   => 'aviso',
            'sql'         => <<<'SQL'
                SELECT u.nominaUsuario + N' — ' + u.nombre AS detalle
                FROM usuarios AS u
                WHERE LOWER(LTRIM(RTRIM(u.estatus))) <> N'activo'
                  AND (EXISTS (SELECT 1 FROM usuario_roles AS ur
                               WHERE ur.nominaUsuario = u.nominaUsuario)
                       OR EXISTS (SELECT 1 FROM usuario_permisos AS up
                                  WHERE up.nominaUsuario = u.nominaUsuario
                                    AND (up.vigenciaHasta IS NULL
                                         OR up.vigenciaHasta >= CAST(SYSDATETIME() AS date))))
                ORDER BY u.nombre
                SQL
        ],
        [
            'clave'       => 'rol_inactivo_asignado',
            'titulo'      => 'Usuarios con roles inactivos',
            'descripcion' => 'El rol o su aplicación están inactivos, así que la asignación no aporta permisos.',
            'severidad'   => 'info',
            'sql'         => <<<'SQL'
                SELECT ur.nominaUsuario + N' → ' + r.codigoSistema + N'.' + r.codigoRol AS detalle
                FROM usuario_roles AS ur
                INNER JOIN roles AS r        ON r.idRol = ur.idRol
                INNER JOIN aplicaciones AS a ON a.codigoSistema = r.codigoSistema
                WHERE r.activo = 0 OR a.activo = 0
                ORDER BY detalle
                SQL
        ],
        [
            'clave'       => 'excepcion_elemento_inactivo',
            'titulo'      => 'Excepciones sobre módulos o aplicaciones inactivas',
            'descripcion' => 'La excepción existe, pero no tiene efecto mientras el módulo o la aplicación estén inactivos.',
            'severidad'   => 'info',
            'sql'         => <<<'SQL'
                SELECT up.nominaUsuario + N' → ' + m.codigoSistema + N'.'
                       + m.codigoModulo + N'.' + p.accion + N' (' + up.tipo + N')' AS detalle
                FROM usuario_permisos AS up
                INNER JOIN permisos AS p     ON p.idPermiso = up.idPermiso
                INNER JOIN modulos AS m      ON m.idModulo = p.idModulo
                INNER JOIN aplicaciones AS a ON a.codigoSistema = m.codigoSistema
                WHERE m.activo = 0 OR a.activo = 0
                ORDER BY detalle
                SQL
        ],
        [
            'clave'       => 'excepciones_vencidas',
            'titulo'      => 'Excepciones vencidas',
            'descripcion' => 'Se conservan como historial; ya no tienen efecto.',
            'severidad'   => 'info',
            'sql'         => <<<'SQL'
                SELECT up.nominaUsuario + N' → ' + m.codigoSistema + N'.' + m.codigoModulo
                       + N'.' + p.accion + N' (venció ' + CONVERT(char(10), up.vigenciaHasta, 103) + N')' AS detalle
                FROM usuario_permisos AS up
                INNER JOIN permisos AS p ON p.idPermiso = up.idPermiso
                INNER JOIN modulos AS m  ON m.idModulo = p.idModulo
                WHERE up.vigenciaHasta < CAST(SYSDATETIME() AS date)
                ORDER BY up.vigenciaHasta DESC
                SQL
        ],
    ];

    /* Cuántos ejemplos se devuelven por revisión; el total sí es completo. */
    $maximoEjemplos = 10;
    $resultado = [];

    foreach ($revisiones as $revision) {
        $consulta = $conn->query($revision['sql']);
        $detalles = $consulta->fetchAll(PDO::FETCH_COLUMN);
        $consulta->closeCursor();

        $resultado[] = [
            'clave'       => $revision['clave'],
            'titulo'      => $revision['titulo'],
            'descripcion' => $revision['descripcion'],
            'severidad'   => $revision['severidad'],
            'total'       => count($detalles),
            'ejemplos'    => array_slice($detalles, 0, $maximoEjemplos),
        ];
    }

    return $resultado;
}
