<?php
/**
 * api/consultas/modulos.php — SQL de la pantalla de módulos.
 *
 * Solo define funciones. Las usa api/operacionesModulos.php. Los idModulo
 * que reciben estas funciones salen siempre de obtenerModulo()
 * (api/consultas/catalogos.php), nunca del navegador.
 */

/**
 * Todos los módulos, con su aplicación, su número de permisos y si tienen "ver".
 */
function consultarModulos(PDO $conn): array
{
    $consulta = $conn->query(
        "SELECT m.codigoSistema,
                a.nombre AS aplicacion,
                a.activo AS aplicacionActiva,
                m.codigoModulo,
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
         INNER JOIN aplicaciones AS a ON a.codigoSistema = m.codigoSistema
         ORDER BY m.codigoSistema, m.orden, m.codigoModulo"
    );
    $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    foreach ($filas as &$fila) {
        $fila['aplicacionActiva'] = (int) $fila['aplicacionActiva'] === 1;
        $fila['activo'] = (int) $fila['activo'] === 1;
        $fila['orden'] = (int) $fila['orden'];
        $fila['permisos'] = (int) $fila['permisos'];
        $fila['tieneVer'] = (int) $fila['tieneVer'] === 1;
    }
    unset($fila);

    return $filas;
}

/**
 * Permisos de un módulo con cuántos roles y excepciones los usan.
 */
function consultarPermisosDeModulo(PDO $conn, int $idModulo): array
{
    $consulta = $conn->prepare(
        "SELECT p.accion,
                p.descripcion,
                (SELECT COUNT(*) FROM rol_permisos AS rp WHERE rp.idPermiso = p.idPermiso) AS roles,
                (SELECT COUNT(*) FROM usuario_permisos AS up WHERE up.idPermiso = p.idPermiso) AS excepciones
         FROM permisos AS p
         WHERE p.idModulo = :modulo
         ORDER BY CASE WHEN p.accion = N'ver' THEN 0 ELSE 1 END, p.accion"
    );
    $consulta->execute([':modulo' => $idModulo]);
    $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    foreach ($filas as &$fila) {
        $fila['roles'] = (int) $fila['roles'];
        $fila['excepciones'] = (int) $fila['excepciones'];
    }
    unset($fila);

    return $filas;
}

/**
 * Indica si ya existe el par (codigoSistema, codigoModulo). Dentro de una
 * transacción, UPDLOCK + HOLDLOCK reservan el valor.
 */
function existeModulo(PDO $conn, string $codigoSistema, string $codigoModulo): bool
{
    $consulta = $conn->prepare(
        "SELECT CASE WHEN EXISTS (SELECT 1 FROM modulos WITH (UPDLOCK, HOLDLOCK)
                                  WHERE codigoSistema = :sistema AND codigoModulo = :modulo)
                THEN 1 ELSE 0 END"
    );
    $consulta->execute([':sistema' => $codigoSistema, ':modulo' => $codigoModulo]);
    $existe = (int) $consulta->fetchColumn() === 1;
    $consulta->closeCursor();

    return $existe;
}

/**
 * Inserta el módulo y devuelve su idModulo (uso interno, para crear su
 * acción "ver" en la misma transacción).
 */
function insertarModulo(PDO $conn, string $codigoSistema, string $codigoModulo, string $nombre, ?string $ruta, ?string $icono, int $orden, int $activo): int
{
    $consulta = $conn->prepare(
        "INSERT INTO modulos (codigoSistema, codigoModulo, nombre, ruta, icono, orden, activo)
         OUTPUT INSERTED.idModulo
         VALUES (:sistema, :modulo, :nombre, :ruta, :icono, :orden, :activo)"
    );
    $consulta->execute([
        ':sistema' => $codigoSistema,
        ':modulo'  => $codigoModulo,
        ':nombre'  => $nombre,
        ':ruta'    => $ruta,
        ':icono'   => $icono,
        ':orden'   => $orden,
        ':activo'  => $activo,
    ]);
    $idModulo = (int) $consulta->fetchColumn();
    $consulta->closeCursor();

    return $idModulo;
}

function actualizarModulo(PDO $conn, int $idModulo, string $nombre, ?string $ruta, ?string $icono, int $orden): void
{
    $consulta = $conn->prepare(
        "UPDATE modulos
            SET nombre = :nombre, ruta = :ruta, icono = :icono, orden = :orden
          WHERE idModulo = :modulo"
    );
    $consulta->execute([
        ':nombre' => $nombre,
        ':ruta'   => $ruta,
        ':icono'  => $icono,
        ':orden'  => $orden,
        ':modulo' => $idModulo,
    ]);
}

function actualizarActivoModulo(PDO $conn, int $idModulo, int $activo): void
{
    $consulta = $conn->prepare("UPDATE modulos SET activo = :activo WHERE idModulo = :modulo");
    $consulta->execute([':activo' => $activo, ':modulo' => $idModulo]);
}

/**
 * Permisos del módulo (lo que impide eliminarlo).
 */
function contarPermisosDeModulo(PDO $conn, int $idModulo): int
{
    $consulta = $conn->prepare("SELECT COUNT(*) FROM permisos WITH (HOLDLOCK) WHERE idModulo = :modulo");
    $consulta->execute([':modulo' => $idModulo]);
    $total = (int) $consulta->fetchColumn();
    $consulta->closeCursor();

    return $total;
}

function eliminarModulo(PDO $conn, int $idModulo): void
{
    $consulta = $conn->prepare("DELETE FROM modulos WHERE idModulo = :modulo");
    $consulta->execute([':modulo' => $idModulo]);
}
