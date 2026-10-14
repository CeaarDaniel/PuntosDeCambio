<?php
/**
 * api/consultas/usuarios.php — SQL de la pantalla de usuarios.
 *
 * Solo define funciones: no imprime nada ni lee $_POST. Las usa
 * api/operacionesUsuarios.php, que valida la petición y responde.
 *
 * REGLA: ninguna consulta devuelve passwordHash. Cuando hace falta saber si la
 * contraseña está bien guardada, solo se compara su formato en SQL.
 *
 * Estatus: en la base es texto libre (sin CHECK). Se considera activo solo
 * 'activo' (sin importar espacios ni mayúsculas), igual que el login de las
 * aplicaciones; cualquier otro valor se presenta como 'inactivo'. Esta
 * aplicación solo escribe 'activo' o 'inactivo'.
 */

/* Expresión SQL reutilizada para normalizar el estatus. */
const SQL_ESTATUS_NORMALIZADO =
    "CASE WHEN LOWER(LTRIM(RTRIM(u.estatus))) = N'activo' THEN N'activo' ELSE N'inactivo' END";

/* 1 cuando passwordHash tiene formato de hash (bcrypt o argon2). Se usa
   concatenación para que PHP no interprete los signos $. */
const SQL_PASSWORD_VALIDA =
    "CASE WHEN u.passwordHash LIKE N'" . '$2y$%' . "' OR u.passwordHash LIKE N'" . '$argon2%' . "' THEN 1 ELSE 0 END";

/**
 * Listado completo con los contadores que muestra la tabla.
 */
function consultarUsuarios(PDO $conn): array
{
    $consulta = $conn->query(
        "SELECT u.nominaUsuario,
                u.nombre,
                " . SQL_ESTATUS_NORMALIZADO . " AS estatus,
                CONVERT(char(10), u.fechacreacion, 23) AS fechaCreacion,
                u.comentarios,
                (SELECT COUNT(*) FROM usuario_roles AS ur
                  WHERE ur.nominaUsuario = u.nominaUsuario) AS totalRoles,
                (SELECT COUNT(*) FROM usuario_permisos AS up
                  WHERE up.nominaUsuario = u.nominaUsuario
                    AND (up.vigenciaHasta IS NULL
                         OR up.vigenciaHasta >= CAST(SYSDATETIME() AS date))) AS excepcionesVigentes,
                " . SQL_PASSWORD_VALIDA . " AS passwordValida
         FROM usuarios AS u
         ORDER BY u.nombre, u.nominaUsuario"
    );

    $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    foreach ($filas as &$fila) {
        $fila['totalRoles'] = (int) $fila['totalRoles'];
        $fila['excepcionesVigentes'] = (int) $fila['excepcionesVigentes'];
        $fila['passwordValida'] = (int) $fila['passwordValida'] === 1;
    }
    unset($fila);

    return $filas;
}

/**
 * Datos de un usuario, o null si no existe.
 *
 * $bloquear = true se usa dentro de una transacción antes de modificarlo:
 * UPDLOCK evita que otra petición lo cambie entre la validación y la escritura.
 */
function consultarUsuario(PDO $conn, string $nomina, bool $bloquear = false): ?array
{
    $consulta = $conn->prepare(
        "SELECT u.nominaUsuario,
                u.nombre,
                " . SQL_ESTATUS_NORMALIZADO . " AS estatus,
                CONVERT(char(10), u.fechacreacion, 23) AS fechaCreacion,
                u.comentarios,
                " . SQL_PASSWORD_VALIDA . " AS passwordValida
         FROM usuarios AS u" . ($bloquear ? ' WITH (UPDLOCK, ROWLOCK)' : '') . "
         WHERE u.nominaUsuario = :nomina"
    );
    $consulta->execute([':nomina' => $nomina]);
    $fila = $consulta->fetch(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    if (!$fila) {
        return null;
    }

    $fila['passwordValida'] = (int) $fila['passwordValida'] === 1;

    return $fila;
}

/**
 * Indica si ya existe la nómina. Dentro de una transacción, UPDLOCK + HOLDLOCK
 * reservan el valor para que dos altas simultáneas no pasen ambas la validación.
 */
function existeNomina(PDO $conn, string $nomina): bool
{
    $consulta = $conn->prepare(
        "SELECT CASE WHEN EXISTS
                (
                    SELECT 1 FROM usuarios WITH (UPDLOCK, HOLDLOCK)
                    WHERE nominaUsuario = :nomina
                ) THEN 1 ELSE 0 END"
    );
    $consulta->execute([':nomina' => $nomina]);
    $existe = (int) $consulta->fetchColumn() === 1;
    $consulta->closeCursor();

    return $existe;
}

/**
 * Roles asignados, de todas las aplicaciones, con su estado.
 */
function consultarRolesDeUsuario(PDO $conn, string $nomina): array
{
    $consulta = $conn->prepare(
        "SELECT r.codigoSistema,
                a.nombre AS aplicacion,
                a.activo AS aplicacionActiva,
                r.codigoRol,
                r.nombreRol,
                r.descripcion,
                r.activo AS rolActivo
         FROM usuario_roles AS ur
         INNER JOIN roles AS r        ON r.idRol = ur.idRol
         INNER JOIN aplicaciones AS a ON a.codigoSistema = r.codigoSistema
         WHERE ur.nominaUsuario = :nomina
         ORDER BY r.codigoSistema, r.nombreRol"
    );
    $consulta->execute([':nomina' => $nomina]);
    $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    foreach ($filas as &$fila) {
        $fila['aplicacionActiva'] = (int) $fila['aplicacionActiva'] === 1;
        $fila['rolActivo'] = (int) $fila['rolActivo'] === 1;
    }
    unset($fila);

    return $filas;
}

/**
 * Excepciones individuales (vigentes, permanentes y vencidas).
 *
 * estadoVigencia: 'permanente' (sin fecha), 'vigente' (fecha >= hoy) o
 * 'vencido' (fecha < hoy).
 */
function consultarExcepcionesDeUsuario(PDO $conn, string $nomina): array
{
    $consulta = $conn->prepare(
        "SELECT m.codigoSistema,
                a.nombre AS aplicacion,
                a.activo AS aplicacionActiva,
                m.codigoModulo,
                m.nombre AS modulo,
                m.activo AS moduloActivo,
                p.accion,
                p.descripcion,
                LOWER(LTRIM(RTRIM(up.tipo))) AS tipo,
                up.motivo,
                CONVERT(char(10), up.vigenciaHasta, 23) AS vigenciaHasta,
                CASE
                    WHEN up.vigenciaHasta IS NULL THEN N'permanente'
                    WHEN up.vigenciaHasta >= CAST(SYSDATETIME() AS date) THEN N'vigente'
                    ELSE N'vencido'
                END AS estadoVigencia,
                up.otorgadoPor,
                o.nombre AS otorgadoPorNombre,
                CONVERT(char(16), up.fechaRegistro, 120) AS fechaRegistro
         FROM usuario_permisos AS up
         INNER JOIN permisos AS p     ON p.idPermiso = up.idPermiso
         INNER JOIN modulos AS m      ON m.idModulo = p.idModulo
         INNER JOIN aplicaciones AS a ON a.codigoSistema = m.codigoSistema
         LEFT JOIN usuarios AS o      ON o.nominaUsuario = up.otorgadoPor
         WHERE up.nominaUsuario = :nomina
         ORDER BY CASE WHEN up.vigenciaHasta IS NULL OR up.vigenciaHasta >= CAST(SYSDATETIME() AS date)
                       THEN 0 ELSE 1 END,
                  m.codigoSistema, m.codigoModulo, p.accion"
    );
    $consulta->execute([':nomina' => $nomina]);
    $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    foreach ($filas as &$fila) {
        $fila['aplicacionActiva'] = (int) $fila['aplicacionActiva'] === 1;
        $fila['moduloActivo'] = (int) $fila['moduloActivo'] === 1;
    }
    unset($fila);

    return $filas;
}

function insertarUsuario(PDO $conn, string $nomina, string $nombre, string $passwordHash, string $estatus, ?string $comentarios): void
{
    $consulta = $conn->prepare(
        "INSERT INTO usuarios (nominaUsuario, nombre, passwordHash, estatus, fechacreacion, comentarios)
         VALUES (:nomina, :nombre, :passwordHash, :estatus, CAST(SYSDATETIME() AS date), :comentarios)"
    );
    $consulta->execute([
        ':nomina'       => $nomina,
        ':nombre'       => $nombre,
        ':passwordHash' => $passwordHash,
        ':estatus'      => $estatus,
        ':comentarios'  => $comentarios,
    ]);
}

function actualizarDatosUsuario(PDO $conn, string $nomina, string $nombre, ?string $comentarios): void
{
    $consulta = $conn->prepare(
        "UPDATE usuarios
            SET nombre = :nombre,
                comentarios = :comentarios
          WHERE nominaUsuario = :nomina"
    );
    $consulta->execute([
        ':nombre'      => $nombre,
        ':comentarios' => $comentarios,
        ':nomina'      => $nomina,
    ]);
}

function actualizarEstatusUsuario(PDO $conn, string $nomina, string $estatus): void
{
    $consulta = $conn->prepare(
        "UPDATE usuarios SET estatus = :estatus WHERE nominaUsuario = :nomina"
    );
    $consulta->execute([':estatus' => $estatus, ':nomina' => $nomina]);
}

function actualizarPasswordUsuario(PDO $conn, string $nomina, string $passwordHash): void
{
    $consulta = $conn->prepare(
        "UPDATE usuarios SET passwordHash = :passwordHash WHERE nominaUsuario = :nomina"
    );
    $consulta->execute([':passwordHash' => $passwordHash, ':nomina' => $nomina]);
}
