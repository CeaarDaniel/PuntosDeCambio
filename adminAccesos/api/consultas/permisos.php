<?php
/**
 * api/consultas/permisos.php — SQL de la pantalla de permisos.
 *
 * Solo define funciones. Las usan api/operacionesPermisos.php y
 * api/operacionesModulos.php (para crear la acción "ver" de un módulo nuevo).
 * Los idModulo / idPermiso que reciben salen siempre de obtenerModulo() u
 * obtenerPermiso() (api/consultas/catalogos.php), nunca del navegador.
 */

/**
 * Todos los permisos con su clave completa y cuántos roles y excepciones los usan.
 */
function consultarPermisos(PDO $conn): array
{
    $consulta = $conn->query(
        "SELECT m.codigoSistema,
                a.nombre AS aplicacion,
                a.activo AS aplicacionActiva,
                m.codigoModulo,
                m.nombre AS modulo,
                m.activo AS moduloActivo,
                p.accion,
                p.descripcion,
                (SELECT COUNT(*) FROM rol_permisos AS rp WHERE rp.idPermiso = p.idPermiso) AS roles,
                (SELECT COUNT(*) FROM usuario_permisos AS up WHERE up.idPermiso = p.idPermiso) AS excepciones
         FROM permisos AS p
         INNER JOIN modulos AS m      ON m.idModulo = p.idModulo
         INNER JOIN aplicaciones AS a ON a.codigoSistema = m.codigoSistema
         ORDER BY m.codigoSistema, m.orden, m.codigoModulo,
                  CASE WHEN p.accion = N'ver' THEN 0 ELSE 1 END, p.accion"
    );
    $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    foreach ($filas as &$fila) {
        $fila['aplicacionActiva'] = (int) $fila['aplicacionActiva'] === 1;
        $fila['moduloActivo'] = (int) $fila['moduloActivo'] === 1;
        $fila['roles'] = (int) $fila['roles'];
        $fila['excepciones'] = (int) $fila['excepciones'];
    }
    unset($fila);

    return $filas;
}

/**
 * Quién tiene el permiso: roles que lo incluyen, usuarios que lo reciben por
 * esos roles y usuarios con una excepción individual sobre él.
 */
function consultarRelacionesPermiso(PDO $conn, int $idPermiso): array
{
    $consulta = $conn->prepare(
        "SELECT r.codigoSistema, r.codigoRol, r.nombreRol, r.activo,
                (SELECT COUNT(*) FROM usuario_roles AS ur WHERE ur.idRol = r.idRol) AS usuarios
         FROM rol_permisos AS rp
         INNER JOIN roles AS r ON r.idRol = rp.idRol
         WHERE rp.idPermiso = :permiso
         ORDER BY r.nombreRol"
    );
    $consulta->execute([':permiso' => $idPermiso]);
    $roles = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    foreach ($roles as &$rol) {
        $rol['activo'] = (int) $rol['activo'] === 1;
        $rol['usuarios'] = (int) $rol['usuarios'];
    }
    unset($rol);

    $consulta = $conn->prepare(
        "SELECT u.nominaUsuario,
                u.nombre,
                CASE WHEN LOWER(LTRIM(RTRIM(u.estatus))) = N'activo' THEN N'activo' ELSE N'inactivo' END AS estatus,
                STRING_AGG(r.codigoRol, N', ') WITHIN GROUP (ORDER BY r.codigoRol) AS roles
         FROM rol_permisos AS rp
         INNER JOIN usuario_roles AS ur ON ur.idRol = rp.idRol
         INNER JOIN roles AS r          ON r.idRol = rp.idRol
         INNER JOIN usuarios AS u       ON u.nominaUsuario = ur.nominaUsuario
         WHERE rp.idPermiso = :permiso
         GROUP BY u.nominaUsuario, u.nombre, u.estatus
         ORDER BY u.nombre"
    );
    $consulta->execute([':permiso' => $idPermiso]);
    $usuariosPorRol = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    $consulta = $conn->prepare(
        "SELECT u.nominaUsuario,
                u.nombre,
                CASE WHEN LOWER(LTRIM(RTRIM(u.estatus))) = N'activo' THEN N'activo' ELSE N'inactivo' END AS estatus,
                LOWER(LTRIM(RTRIM(up.tipo))) AS tipo,
                up.motivo,
                CONVERT(char(10), up.vigenciaHasta, 23) AS vigenciaHasta,
                CASE
                    WHEN up.vigenciaHasta IS NULL THEN N'permanente'
                    WHEN up.vigenciaHasta >= CAST(SYSDATETIME() AS date) THEN N'vigente'
                    ELSE N'vencido'
                END AS estadoVigencia
         FROM usuario_permisos AS up
         INNER JOIN usuarios AS u ON u.nominaUsuario = up.nominaUsuario
         WHERE up.idPermiso = :permiso
         ORDER BY u.nombre"
    );
    $consulta->execute([':permiso' => $idPermiso]);
    $excepciones = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    return [
        'roles'          => $roles,
        'usuariosPorRol' => $usuariosPorRol,
        'excepciones'    => $excepciones,
    ];
}

/**
 * Indica si ya existe la acción en el módulo. Dentro de una transacción,
 * UPDLOCK + HOLDLOCK reservan el valor.
 */
function existePermiso(PDO $conn, int $idModulo, string $accion): bool
{
    $consulta = $conn->prepare(
        "SELECT CASE WHEN EXISTS (SELECT 1 FROM permisos WITH (UPDLOCK, HOLDLOCK)
                                  WHERE idModulo = :modulo AND accion = :accion)
                THEN 1 ELSE 0 END"
    );
    $consulta->execute([':modulo' => $idModulo, ':accion' => $accion]);
    $existe = (int) $consulta->fetchColumn() === 1;
    $consulta->closeCursor();

    return $existe;
}

function insertarPermiso(PDO $conn, int $idModulo, string $accion, ?string $descripcion): void
{
    $consulta = $conn->prepare(
        "INSERT INTO permisos (idModulo, accion, descripcion) VALUES (:modulo, :accion, :descripcion)"
    );
    $consulta->execute([':modulo' => $idModulo, ':accion' => $accion, ':descripcion' => $descripcion]);
}

function actualizarDescripcionPermiso(PDO $conn, int $idPermiso, ?string $descripcion): void
{
    $consulta = $conn->prepare("UPDATE permisos SET descripcion = :descripcion WHERE idPermiso = :permiso");
    $consulta->execute([':descripcion' => $descripcion, ':permiso' => $idPermiso]);
}

/**
 * Asignaciones del permiso (lo que impide eliminarlo).
 */
function contarAsignacionesPermiso(PDO $conn, int $idPermiso): array
{
    $consulta = $conn->prepare(
        "SELECT (SELECT COUNT(*) FROM rol_permisos WITH (HOLDLOCK) WHERE idPermiso = :permisoRoles) AS roles,
                (SELECT COUNT(*) FROM usuario_permisos WITH (HOLDLOCK) WHERE idPermiso = :permisoUsuarios) AS excepciones"
    );
    $consulta->execute([':permisoRoles' => $idPermiso, ':permisoUsuarios' => $idPermiso]);
    $fila = $consulta->fetch(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    return ['roles' => (int) $fila['roles'], 'excepciones' => (int) $fila['excepciones']];
}

function eliminarPermiso(PDO $conn, int $idPermiso): void
{
    $consulta = $conn->prepare("DELETE FROM permisos WHERE idPermiso = :permiso");
    $consulta->execute([':permiso' => $idPermiso]);
}
