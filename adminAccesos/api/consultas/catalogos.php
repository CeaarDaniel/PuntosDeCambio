<?php
/**
 * api/consultas/catalogos.php — Catálogos compartidos y resolución de claves.
 *
 * Solo define funciones. Las usan los endpoints de aplicaciones, módulos,
 * permisos y roles (y las de asignaciones de la Etapa 6).
 *
 * REGLA CENTRAL (contexto.md y autenticacion-y-permisos.md §4): el navegador
 * nunca envía IDs. Identifica cada elemento con sus claves estables
 * (codigoSistema, codigoModulo, accion, codigoRol) y aquí se buscan los IDs
 * internos en la base, cada vez, dentro de la aplicación correcta. Los IDs
 * solo viajan entre consultas del servidor; nunca se devuelven al navegador.
 *
 * $bloquear = true se usa dentro de una transacción antes de modificar:
 * UPDLOCK evita que otra petición cambie el registro entre la validación y la
 * escritura.
 */

function sugerenciaBloqueo(bool $bloquear): string
{
    return $bloquear ? ' WITH (UPDLOCK, ROWLOCK)' : '';
}

/**
 * Aplicaciones para los selectores de las pantallas.
 */
function consultarAplicacionesCatalogo(PDO $conn): array
{
    $consulta = $conn->query(
        "SELECT codigoSistema, nombre, activo
         FROM aplicaciones
         ORDER BY codigoSistema"
    );
    $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    foreach ($filas as &$fila) {
        $fila['activo'] = (int) $fila['activo'] === 1;
    }
    unset($fila);

    return $filas;
}

/**
 * Módulos para los selectores (todas las aplicaciones).
 */
function consultarModulosCatalogo(PDO $conn): array
{
    $consulta = $conn->query(
        "SELECT codigoSistema, codigoModulo, nombre, activo
         FROM modulos
         ORDER BY codigoSistema, orden, codigoModulo"
    );
    $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    foreach ($filas as &$fila) {
        $fila['activo'] = (int) $fila['activo'] === 1;
    }
    unset($fila);

    return $filas;
}

function obtenerAplicacion(PDO $conn, string $codigoSistema, bool $bloquear = false): ?array
{
    $consulta = $conn->prepare(
        "SELECT codigoSistema, nombre, descripcion, activo
         FROM aplicaciones" . sugerenciaBloqueo($bloquear) . "
         WHERE codigoSistema = :sistema"
    );
    $consulta->execute([':sistema' => $codigoSistema]);
    $fila = $consulta->fetch(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    if (!$fila) {
        return null;
    }

    $fila['activo'] = (int) $fila['activo'] === 1;

    return $fila;
}

/**
 * Módulo por (codigoSistema, codigoModulo). Incluye idModulo para uso interno.
 */
function obtenerModulo(PDO $conn, string $codigoSistema, string $codigoModulo, bool $bloquear = false): ?array
{
    $consulta = $conn->prepare(
        "SELECT idModulo, codigoSistema, codigoModulo, nombre, ruta, icono, orden, activo
         FROM modulos" . sugerenciaBloqueo($bloquear) . "
         WHERE codigoSistema = :sistema
           AND codigoModulo = :modulo"
    );
    $consulta->execute([':sistema' => $codigoSistema, ':modulo' => $codigoModulo]);
    $fila = $consulta->fetch(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    if (!$fila) {
        return null;
    }

    $fila['idModulo'] = (int) $fila['idModulo'];
    $fila['orden'] = (int) $fila['orden'];
    $fila['activo'] = (int) $fila['activo'] === 1;

    return $fila;
}

/**
 * Permiso por (codigoSistema, codigoModulo, accion). Incluye idPermiso para
 * uso interno. El JOIN con modulos garantiza que el permiso es de esa aplicación.
 */
function obtenerPermiso(PDO $conn, string $codigoSistema, string $codigoModulo, string $accion, bool $bloquear = false): ?array
{
    $consulta = $conn->prepare(
        "SELECT p.idPermiso, p.idModulo, m.codigoSistema, m.codigoModulo, p.accion, p.descripcion
         FROM permisos AS p" . sugerenciaBloqueo($bloquear) . "
         INNER JOIN modulos AS m ON m.idModulo = p.idModulo
         WHERE m.codigoSistema = :sistema
           AND m.codigoModulo = :modulo
           AND p.accion = :accion"
    );
    $consulta->execute([':sistema' => $codigoSistema, ':modulo' => $codigoModulo, ':accion' => $accion]);
    $fila = $consulta->fetch(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    if (!$fila) {
        return null;
    }

    $fila['idPermiso'] = (int) $fila['idPermiso'];
    $fila['idModulo'] = (int) $fila['idModulo'];

    return $fila;
}

/**
 * Rol por (codigoSistema, codigoRol). Incluye idRol para uso interno.
 */
function obtenerRol(PDO $conn, string $codigoSistema, string $codigoRol, bool $bloquear = false): ?array
{
    $consulta = $conn->prepare(
        "SELECT idRol, codigoSistema, codigoRol, nombreRol, descripcion, activo
         FROM roles" . sugerenciaBloqueo($bloquear) . "
         WHERE codigoSistema = :sistema
           AND codigoRol = :rol"
    );
    $consulta->execute([':sistema' => $codigoSistema, ':rol' => $codigoRol]);
    $fila = $consulta->fetch(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    if (!$fila) {
        return null;
    }

    $fila['idRol'] = (int) $fila['idRol'];
    $fila['activo'] = (int) $fila['activo'] === 1;

    return $fila;
}

/**
 * Quita los IDs internos de una fila antes de devolverla al navegador.
 */
function sinIdsInternos(array $fila): array
{
    unset($fila['idModulo'], $fila['idPermiso'], $fila['idRol']);

    return $fila;
}
