<?php
/**
 * api/consultas/roles.php — SQL de la pantalla de roles.
 *
 * Solo define funciones. Las usa api/operacionesRoles.php. Los idRol e
 * idPermiso que reciben salen siempre de obtenerRol() o de
 * mapaPermisosDeAplicacion(), nunca del navegador.
 */

/**
 * Todos los roles con su aplicación y sus contadores. Solo se cuentan los
 * permisos de módulos de la MISMA aplicación del rol (los de otra aplicación
 * serían una inconsistencia y no tienen efecto).
 */
function consultarRoles(PDO $conn): array
{
    $consulta = $conn->query(
        "SELECT r.codigoSistema,
                a.nombre AS aplicacion,
                a.activo AS aplicacionActiva,
                r.codigoRol,
                r.nombreRol,
                r.descripcion,
                r.activo,
                (SELECT COUNT(*) FROM rol_permisos AS rp
                  INNER JOIN permisos AS p ON p.idPermiso = rp.idPermiso
                  INNER JOIN modulos AS m  ON m.idModulo = p.idModulo
                  WHERE rp.idRol = r.idRol AND m.codigoSistema = r.codigoSistema) AS permisos,
                (SELECT COUNT(*) FROM usuario_roles AS ur WHERE ur.idRol = r.idRol) AS usuarios
         FROM roles AS r
         INNER JOIN aplicaciones AS a ON a.codigoSistema = r.codigoSistema
         ORDER BY r.codigoSistema, r.nombreRol"
    );
    $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    foreach ($filas as &$fila) {
        $fila['aplicacionActiva'] = (int) $fila['aplicacionActiva'] === 1;
        $fila['activo'] = (int) $fila['activo'] === 1;
        $fila['permisos'] = (int) $fila['permisos'];
        $fila['usuarios'] = (int) $fila['usuarios'];
    }
    unset($fila);

    return $filas;
}

function consultarUsuariosDeRol(PDO $conn, int $idRol): array
{
    $consulta = $conn->prepare(
        "SELECT u.nominaUsuario,
                u.nombre,
                CASE WHEN LOWER(LTRIM(RTRIM(u.estatus))) = N'activo' THEN N'activo' ELSE N'inactivo' END AS estatus
         FROM usuario_roles AS ur
         INNER JOIN usuarios AS u ON u.nominaUsuario = ur.nominaUsuario
         WHERE ur.idRol = :rol
         ORDER BY u.nombre"
    );
    $consulta->execute([':rol' => $idRol]);
    $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    return $filas;
}

/**
 * Todos los permisos de la aplicación del rol, marcando los que ya tiene.
 * Es la lista de checks de la asignación.
 */
function consultarPermisosAsignables(PDO $conn, string $codigoSistema, int $idRol): array
{
    $consulta = $conn->prepare(
        "SELECT m.codigoModulo,
                m.nombre AS modulo,
                m.activo AS moduloActivo,
                p.accion,
                p.descripcion,
                CASE WHEN EXISTS (SELECT 1 FROM rol_permisos AS rp
                                  WHERE rp.idRol = :rol AND rp.idPermiso = p.idPermiso)
                     THEN 1 ELSE 0 END AS asignado
         FROM permisos AS p
         INNER JOIN modulos AS m ON m.idModulo = p.idModulo
         WHERE m.codigoSistema = :sistema
         ORDER BY m.orden, m.codigoModulo,
                  CASE WHEN p.accion = N'ver' THEN 0 ELSE 1 END, p.accion"
    );
    $consulta->execute([':rol' => $idRol, ':sistema' => $codigoSistema]);
    $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    foreach ($filas as &$fila) {
        $fila['moduloActivo'] = (int) $fila['moduloActivo'] === 1;
        $fila['asignado'] = (int) $fila['asignado'] === 1;
    }
    unset($fila);

    return $filas;
}

/**
 * Los demás roles de la misma aplicación con sus permisos (clave
 * codigoModulo.accion). Sirven para "copiar los permisos de otro rol".
 */
function consultarPermisosDeOtrosRoles(PDO $conn, string $codigoSistema, int $idRol): array
{
    $consulta = $conn->prepare(
        "SELECT r.codigoRol,
                r.nombreRol,
                m.codigoModulo + N'.' + p.accion AS clave
         FROM roles AS r
         LEFT JOIN rol_permisos AS rp ON rp.idRol = r.idRol
         LEFT JOIN permisos AS p      ON p.idPermiso = rp.idPermiso
         LEFT JOIN modulos AS m       ON m.idModulo = p.idModulo
                                     AND m.codigoSistema = r.codigoSistema
         WHERE r.codigoSistema = :sistema
           AND r.idRol <> :rol
         ORDER BY r.nombreRol"
    );
    $consulta->execute([':sistema' => $codigoSistema, ':rol' => $idRol]);
    $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    $roles = [];

    foreach ($filas as $fila) {
        $codigo = $fila['codigoRol'];

        if (!isset($roles[$codigo])) {
            $roles[$codigo] = ['codigoRol' => $codigo, 'nombreRol' => $fila['nombreRol'], 'permisos' => []];
        }

        if ($fila['clave'] !== null) {
            $roles[$codigo]['permisos'][] = $fila['clave'];
        }
    }

    return array_values($roles);
}

/**
 * Indica si ya existe el par (codigoSistema, codigoRol). Dentro de una
 * transacción, UPDLOCK + HOLDLOCK reservan el valor.
 */
function existeRol(PDO $conn, string $codigoSistema, string $codigoRol): bool
{
    $consulta = $conn->prepare(
        "SELECT CASE WHEN EXISTS (SELECT 1 FROM roles WITH (UPDLOCK, HOLDLOCK)
                                  WHERE codigoSistema = :sistema AND codigoRol = :rol)
                THEN 1 ELSE 0 END"
    );
    $consulta->execute([':sistema' => $codigoSistema, ':rol' => $codigoRol]);
    $existe = (int) $consulta->fetchColumn() === 1;
    $consulta->closeCursor();

    return $existe;
}

function insertarRol(PDO $conn, string $codigoSistema, string $codigoRol, string $nombreRol, ?string $descripcion, int $activo): void
{
    $consulta = $conn->prepare(
        "INSERT INTO roles (codigoSistema, codigoRol, nombreRol, descripcion, activo)
         VALUES (:sistema, :rol, :nombre, :descripcion, :activo)"
    );
    $consulta->execute([
        ':sistema'     => $codigoSistema,
        ':rol'         => $codigoRol,
        ':nombre'      => $nombreRol,
        ':descripcion' => $descripcion,
        ':activo'      => $activo,
    ]);
}

function actualizarRol(PDO $conn, int $idRol, string $nombreRol, ?string $descripcion): void
{
    $consulta = $conn->prepare("UPDATE roles SET nombreRol = :nombre, descripcion = :descripcion WHERE idRol = :rol");
    $consulta->execute([':nombre' => $nombreRol, ':descripcion' => $descripcion, ':rol' => $idRol]);
}

function actualizarActivoRol(PDO $conn, int $idRol, int $activo): void
{
    $consulta = $conn->prepare("UPDATE roles SET activo = :activo WHERE idRol = :rol");
    $consulta->execute([':activo' => $activo, ':rol' => $idRol]);
}

/**
 * Usuarios y permisos del rol (lo que impide eliminarlo).
 */
function contarRelacionesRol(PDO $conn, int $idRol): array
{
    $consulta = $conn->prepare(
        "SELECT (SELECT COUNT(*) FROM usuario_roles WITH (HOLDLOCK) WHERE idRol = :rolUsuarios) AS usuarios,
                (SELECT COUNT(*) FROM rol_permisos WITH (HOLDLOCK) WHERE idRol = :rolPermisos) AS permisos"
    );
    $consulta->execute([':rolUsuarios' => $idRol, ':rolPermisos' => $idRol]);
    $fila = $consulta->fetch(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    return ['usuarios' => (int) $fila['usuarios'], 'permisos' => (int) $fila['permisos']];
}

function eliminarRol(PDO $conn, int $idRol): void
{
    $consulta = $conn->prepare("DELETE FROM roles WHERE idRol = :rol");
    $consulta->execute([':rol' => $idRol]);
}

/**
 * Permisos de una aplicación como mapa clave => idPermiso, donde la clave es
 * 'codigoModulo.accion'. Es la ÚNICA fuente de idPermiso para guardar la
 * asignación: lo que no esté aquí no pertenece a la aplicación del rol.
 */
function mapaPermisosDeAplicacion(PDO $conn, string $codigoSistema): array
{
    $consulta = $conn->prepare(
        "SELECT m.codigoModulo + N'.' + p.accion AS clave, p.idPermiso
         FROM permisos AS p
         INNER JOIN modulos AS m ON m.idModulo = p.idModulo
         WHERE m.codigoSistema = :sistema"
    );
    $consulta->execute([':sistema' => $codigoSistema]);
    $mapa = [];

    foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $fila) {
        $mapa[$fila['clave']] = (int) $fila['idPermiso'];
    }

    $consulta->closeCursor();

    return $mapa;
}

/**
 * idPermiso que tiene asignados el rol actualmente. HOLDLOCK mantiene el
 * conjunto estable hasta terminar la transacción.
 */
function permisosActualesDeRol(PDO $conn, int $idRol): array
{
    $consulta = $conn->prepare("SELECT idPermiso FROM rol_permisos WITH (UPDLOCK, HOLDLOCK) WHERE idRol = :rol");
    $consulta->execute([':rol' => $idRol]);
    $ids = array_map('intval', $consulta->fetchAll(PDO::FETCH_COLUMN));
    $consulta->closeCursor();

    return $ids;
}

function agregarPermisoARol(PDO $conn, int $idRol, int $idPermiso): void
{
    $consulta = $conn->prepare("INSERT INTO rol_permisos (idRol, idPermiso) VALUES (:rol, :permiso)");
    $consulta->execute([':rol' => $idRol, ':permiso' => $idPermiso]);
}

function quitarPermisoDeRol(PDO $conn, int $idRol, int $idPermiso): void
{
    $consulta = $conn->prepare("DELETE FROM rol_permisos WHERE idRol = :rol AND idPermiso = :permiso");
    $consulta->execute([':rol' => $idRol, ':permiso' => $idPermiso]);
}
