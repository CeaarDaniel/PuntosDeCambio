<?php
/**
 * api/consultas/asignaciones.php — SQL de las pantallas de asignaciones:
 * roles de usuario (usuario_roles) y permisos individuales (usuario_permisos).
 *
 * Solo define funciones. Las usan api/operacionesRolesUsuario.php y
 * api/operacionesPermisosUsuario.php. Los idRol / idPermiso que reciben salen
 * siempre de consultas del servidor (catalogos.php o las de aquí), nunca del
 * navegador.
 */

/* ==========================================================================
   Roles de usuario
   ========================================================================== */

/**
 * Roles de una aplicación, marcando los que tiene el usuario. Es la lista de
 * checks de la pantalla.
 */
function consultarRolesDeAplicacionParaUsuario(PDO $conn, string $codigoSistema, string $nomina): array
{
    $consulta = $conn->prepare(
        "SELECT r.codigoRol,
                r.nombreRol,
                r.descripcion,
                r.activo,
                (SELECT COUNT(*) FROM rol_permisos AS rp
                  INNER JOIN permisos AS p ON p.idPermiso = rp.idPermiso
                  INNER JOIN modulos AS m  ON m.idModulo = p.idModulo
                  WHERE rp.idRol = r.idRol AND m.codigoSistema = r.codigoSistema) AS permisos,
                CASE WHEN EXISTS (SELECT 1 FROM usuario_roles AS ur
                                  WHERE ur.idRol = r.idRol AND ur.nominaUsuario = :nomina)
                     THEN 1 ELSE 0 END AS asignado
         FROM roles AS r
         WHERE r.codigoSistema = :sistema
         ORDER BY r.nombreRol"
    );
    $consulta->execute([':nomina' => $nomina, ':sistema' => $codigoSistema]);
    $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    foreach ($filas as &$fila) {
        $fila['activo'] = (int) $fila['activo'] === 1;
        $fila['permisos'] = (int) $fila['permisos'];
        $fila['asignado'] = (int) $fila['asignado'] === 1;
    }
    unset($fila);

    return $filas;
}

/**
 * Roles de una aplicación como mapa codigoRol => [idRol, activo]. Es la ÚNICA
 * fuente de idRol para guardar: un código que no esté aquí no es de esa aplicación.
 */
function mapaRolesDeAplicacion(PDO $conn, string $codigoSistema): array
{
    $consulta = $conn->prepare(
        "SELECT codigoRol, idRol, activo FROM roles WITH (UPDLOCK, HOLDLOCK) WHERE codigoSistema = :sistema"
    );
    $consulta->execute([':sistema' => $codigoSistema]);
    $mapa = [];

    foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $fila) {
        $mapa[$fila['codigoRol']] = ['idRol' => (int) $fila['idRol'], 'activo' => (int) $fila['activo'] === 1];
    }

    $consulta->closeCursor();

    return $mapa;
}

/**
 * idRol que el usuario tiene asignados dentro de una aplicación.
 */
function rolesActualesDeUsuarioEnAplicacion(PDO $conn, string $nomina, string $codigoSistema): array
{
    $consulta = $conn->prepare(
        "SELECT ur.idRol
         FROM usuario_roles AS ur WITH (UPDLOCK, HOLDLOCK)
         INNER JOIN roles AS r ON r.idRol = ur.idRol
         WHERE ur.nominaUsuario = :nomina
           AND r.codigoSistema = :sistema"
    );
    $consulta->execute([':nomina' => $nomina, ':sistema' => $codigoSistema]);
    $ids = array_map('intval', $consulta->fetchAll(PDO::FETCH_COLUMN));
    $consulta->closeCursor();

    return $ids;
}

function asignarRolAUsuario(PDO $conn, string $nomina, int $idRol): void
{
    $consulta = $conn->prepare("INSERT INTO usuario_roles (idRol, nominaUsuario) VALUES (:rol, :nomina)");
    $consulta->execute([':rol' => $idRol, ':nomina' => $nomina]);
}

function quitarRolAUsuario(PDO $conn, string $nomina, int $idRol): void
{
    $consulta = $conn->prepare("DELETE FROM usuario_roles WHERE idRol = :rol AND nominaUsuario = :nomina");
    $consulta->execute([':rol' => $idRol, ':nomina' => $nomina]);
}

/* ==========================================================================
   Permisos individuales (excepciones)
   ========================================================================== */

/**
 * Excepción del usuario sobre un permiso, o null. estadoVigencia:
 * 'permanente', 'vigente' o 'vencido'.
 */
function obtenerExcepcion(PDO $conn, string $nomina, int $idPermiso, bool $bloquear = false): ?array
{
    $consulta = $conn->prepare(
        "SELECT LOWER(LTRIM(RTRIM(tipo))) AS tipo,
                motivo,
                CONVERT(char(10), vigenciaHasta, 23) AS vigenciaHasta,
                CASE
                    WHEN vigenciaHasta IS NULL THEN N'permanente'
                    WHEN vigenciaHasta >= CAST(SYSDATETIME() AS date) THEN N'vigente'
                    ELSE N'vencido'
                END AS estadoVigencia
         FROM usuario_permisos" . ($bloquear ? ' WITH (UPDLOCK, HOLDLOCK)' : '') . "
         WHERE nominaUsuario = :nomina
           AND idPermiso = :permiso"
    );
    $consulta->execute([':nomina' => $nomina, ':permiso' => $idPermiso]);
    $fila = $consulta->fetch(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    return $fila ?: null;
}

function insertarExcepcion(PDO $conn, string $nomina, int $idPermiso, string $tipo, string $motivo, ?string $vigenciaHasta, string $otorgadoPor): void
{
    $consulta = $conn->prepare(
        "INSERT INTO usuario_permisos (nominaUsuario, idPermiso, tipo, motivo, vigenciaHasta, otorgadoPor)
         VALUES (:nomina, :permiso, :tipo, :motivo, :vigencia, :otorgadoPor)"
    );
    $consulta->execute([
        ':nomina'      => $nomina,
        ':permiso'     => $idPermiso,
        ':tipo'        => $tipo,
        ':motivo'      => $motivo,
        ':vigencia'    => $vigenciaHasta,
        ':otorgadoPor' => $otorgadoPor,
    ]);
}

/**
 * Reemplaza los datos de la excepción. otorgadoPor y fechaRegistro pasan a ser
 * los de quien hace el cambio: la fila refleja la autorización vigente. El
 * historial de cambios corresponderá a la auditoría (PENDIENTES.md §1).
 */
function actualizarExcepcion(PDO $conn, string $nomina, int $idPermiso, string $tipo, string $motivo, ?string $vigenciaHasta, string $otorgadoPor): void
{
    $consulta = $conn->prepare(
        "UPDATE usuario_permisos
            SET tipo = :tipo,
                motivo = :motivo,
                vigenciaHasta = :vigencia,
                otorgadoPor = :otorgadoPor,
                fechaRegistro = SYSDATETIME()
          WHERE nominaUsuario = :nomina
            AND idPermiso = :permiso"
    );
    $consulta->execute([
        ':tipo'        => $tipo,
        ':motivo'      => $motivo,
        ':vigencia'    => $vigenciaHasta,
        ':otorgadoPor' => $otorgadoPor,
        ':nomina'      => $nomina,
        ':permiso'     => $idPermiso,
    ]);
}

/**
 * Retira la excepción venciéndola ayer: deja de tener efecto de inmediato,
 * pero se conserva en la tabla con su motivo (autenticacion-y-permisos.md §6.3:
 * las excepciones vencidas se ignoran sin borrarse).
 */
function vencerExcepcion(PDO $conn, string $nomina, int $idPermiso): void
{
    $consulta = $conn->prepare(
        "UPDATE usuario_permisos
            SET vigenciaHasta = DATEADD(DAY, -1, CAST(SYSDATETIME() AS date))
          WHERE nominaUsuario = :nomina
            AND idPermiso = :permiso"
    );
    $consulta->execute([':nomina' => $nomina, ':permiso' => $idPermiso]);
}

function eliminarExcepcion(PDO $conn, string $nomina, int $idPermiso): void
{
    $consulta = $conn->prepare("DELETE FROM usuario_permisos WHERE nominaUsuario = :nomina AND idPermiso = :permiso");
    $consulta->execute([':nomina' => $nomina, ':permiso' => $idPermiso]);
}
