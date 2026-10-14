<?php
/**
 * api/consultas/aplicaciones.php — SQL de la pantalla de aplicaciones.
 *
 * Solo define funciones. Las usa api/operacionesAplicaciones.php.
 */

/**
 * Listado con los contadores de la tabla: módulos, roles, permisos y
 * usuarios relacionados (por rol o por excepción individual).
 */
function consultarAplicaciones(PDO $conn): array
{
    $consulta = $conn->query(
        "SELECT a.codigoSistema,
                a.nombre,
                a.descripcion,
                a.activo,
                (SELECT COUNT(*) FROM modulos AS m WHERE m.codigoSistema = a.codigoSistema) AS modulos,
                (SELECT COUNT(*) FROM modulos AS m
                  WHERE m.codigoSistema = a.codigoSistema AND m.activo = 1) AS modulosActivos,
                (SELECT COUNT(*) FROM roles AS r WHERE r.codigoSistema = a.codigoSistema) AS roles,
                (SELECT COUNT(*) FROM permisos AS p
                  INNER JOIN modulos AS m ON m.idModulo = p.idModulo
                  WHERE m.codigoSistema = a.codigoSistema) AS permisos,
                (SELECT COUNT(*) FROM
                 (
                     SELECT ur.nominaUsuario
                     FROM usuario_roles AS ur
                     INNER JOIN roles AS r ON r.idRol = ur.idRol
                     WHERE r.codigoSistema = a.codigoSistema
                     UNION
                     SELECT up.nominaUsuario
                     FROM usuario_permisos AS up
                     INNER JOIN permisos AS p ON p.idPermiso = up.idPermiso
                     INNER JOIN modulos AS m  ON m.idModulo = p.idModulo
                     WHERE m.codigoSistema = a.codigoSistema
                 ) AS relacionados) AS usuarios
         FROM aplicaciones AS a
         ORDER BY a.codigoSistema"
    );

    $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    foreach ($filas as &$fila) {
        $fila['activo'] = (int) $fila['activo'] === 1;

        foreach (['modulos', 'modulosActivos', 'roles', 'permisos', 'usuarios'] as $campo) {
            $fila[$campo] = (int) $fila[$campo];
        }
    }
    unset($fila);

    return $filas;
}

/**
 * Módulos de una aplicación con su número de permisos y si tienen "ver".
 */
function consultarModulosDeAplicacion(PDO $conn, string $codigoSistema): array
{
    $consulta = $conn->prepare(
        "SELECT m.codigoModulo,
                m.nombre,
                m.ruta,
                m.icono,
                m.orden,
                m.activo,
                (SELECT COUNT(*) FROM permisos AS p WHERE p.idModulo = m.idModulo) AS permisos,
                CASE WHEN EXISTS (SELECT 1 FROM permisos AS p
                                  WHERE p.idModulo = m.idModulo AND p.accion = N'ver')
                     THEN 1 ELSE 0 END AS tieneVer
         FROM modulos AS m
         WHERE m.codigoSistema = :sistema
         ORDER BY m.orden, m.codigoModulo"
    );
    $consulta->execute([':sistema' => $codigoSistema]);
    $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    foreach ($filas as &$fila) {
        $fila['activo'] = (int) $fila['activo'] === 1;
        $fila['orden'] = (int) $fila['orden'];
        $fila['permisos'] = (int) $fila['permisos'];
        $fila['tieneVer'] = (int) $fila['tieneVer'] === 1;
    }
    unset($fila);

    return $filas;
}

/**
 * Roles de una aplicación con su número de permisos y de usuarios.
 */
function consultarRolesDeAplicacion(PDO $conn, string $codigoSistema): array
{
    $consulta = $conn->prepare(
        "SELECT r.codigoRol,
                r.nombreRol,
                r.descripcion,
                r.activo,
                (SELECT COUNT(*) FROM rol_permisos AS rp WHERE rp.idRol = r.idRol) AS permisos,
                (SELECT COUNT(*) FROM usuario_roles AS ur WHERE ur.idRol = r.idRol) AS usuarios
         FROM roles AS r
         WHERE r.codigoSistema = :sistema
         ORDER BY r.nombreRol"
    );
    $consulta->execute([':sistema' => $codigoSistema]);
    $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    foreach ($filas as &$fila) {
        $fila['activo'] = (int) $fila['activo'] === 1;
        $fila['permisos'] = (int) $fila['permisos'];
        $fila['usuarios'] = (int) $fila['usuarios'];
    }
    unset($fila);

    return $filas;
}

/**
 * Indica si ya existe el código. Dentro de una transacción, UPDLOCK + HOLDLOCK
 * reservan el valor para que dos altas simultáneas no pasen ambas.
 */
function existeAplicacion(PDO $conn, string $codigoSistema): bool
{
    $consulta = $conn->prepare(
        "SELECT CASE WHEN EXISTS (SELECT 1 FROM aplicaciones WITH (UPDLOCK, HOLDLOCK)
                                  WHERE codigoSistema = :sistema)
                THEN 1 ELSE 0 END"
    );
    $consulta->execute([':sistema' => $codigoSistema]);
    $existe = (int) $consulta->fetchColumn() === 1;
    $consulta->closeCursor();

    return $existe;
}

/**
 * Cuántos módulos y roles cuelgan de la aplicación (lo que impide eliminarla).
 */
function contarRelacionesAplicacion(PDO $conn, string $codigoSistema): array
{
    $consulta = $conn->prepare(
        "SELECT (SELECT COUNT(*) FROM modulos WITH (HOLDLOCK) WHERE codigoSistema = :sistemaModulos) AS modulos,
                (SELECT COUNT(*) FROM roles WITH (HOLDLOCK) WHERE codigoSistema = :sistemaRoles) AS roles"
    );
    $consulta->execute([':sistemaModulos' => $codigoSistema, ':sistemaRoles' => $codigoSistema]);
    $fila = $consulta->fetch(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    return ['modulos' => (int) $fila['modulos'], 'roles' => (int) $fila['roles']];
}

function insertarAplicacion(PDO $conn, string $codigoSistema, string $nombre, ?string $descripcion, int $activo): void
{
    $consulta = $conn->prepare(
        "INSERT INTO aplicaciones (codigoSistema, nombre, descripcion, activo)
         VALUES (:sistema, :nombre, :descripcion, :activo)"
    );
    $consulta->execute([
        ':sistema'     => $codigoSistema,
        ':nombre'      => $nombre,
        ':descripcion' => $descripcion,
        ':activo'      => $activo,
    ]);
}

function actualizarAplicacion(PDO $conn, string $codigoSistema, string $nombre, ?string $descripcion): void
{
    $consulta = $conn->prepare(
        "UPDATE aplicaciones SET nombre = :nombre, descripcion = :descripcion
         WHERE codigoSistema = :sistema"
    );
    $consulta->execute([':nombre' => $nombre, ':descripcion' => $descripcion, ':sistema' => $codigoSistema]);
}

function actualizarActivoAplicacion(PDO $conn, string $codigoSistema, int $activo): void
{
    $consulta = $conn->prepare("UPDATE aplicaciones SET activo = :activo WHERE codigoSistema = :sistema");
    $consulta->execute([':activo' => $activo, ':sistema' => $codigoSistema]);
}

function eliminarAplicacion(PDO $conn, string $codigoSistema): void
{
    $consulta = $conn->prepare("DELETE FROM aplicaciones WHERE codigoSistema = :sistema");
    $consulta->execute([':sistema' => $codigoSistema]);
}
