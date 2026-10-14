<?php
/**
 * api/operacionesAplicaciones.php — Pantalla de aplicaciones (pages/aplicaciones.php).
 *
 * Opciones:
 *   '1'  Listar con contadores.
 *   '2'  Detalle: módulos y roles de la aplicación.
 *   '3'  Crear.
 *   '4'  Modificar nombre y descripción.
 *   '5'  Activar o desactivar.
 *   '6'  Eliminar (solo si no tiene módulos ni roles).
 *
 * Reglas (contexto.md, módulo 3):
 *  - codigoSistema es único y NO se cambia después del alta: el código de cada
 *    aplicación lo usa literalmente (CODIGO_SISTEMA). Si se capturó mal y aún
 *    no tiene relaciones, se elimina y se vuelve a crear.
 *  - No se elimina físicamente una aplicación con registros relacionados; se
 *    desactiva. La llave foránea de la base es la última barrera.
 *  - La propia aplicación (CODIGO_SISTEMA) no se puede desactivar ni eliminar
 *    desde aquí, para no dejar fuera a los administradores.
 *
 * PENDIENTE (PENDIENTES.md §3): exigirPermiso('aplicaciones', …) en cada opción.
 * PENDIENTE (PENDIENTES.md §1): registrar cada cambio en la auditoría.
 */

require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/consultas/catalogos.php';
require_once __DIR__ . '/consultas/aplicaciones.php';

prepararEndpoint();

const MENSAJE_CODIGO_SISTEMA = 'El código de la aplicación solo puede tener MAYÚSCULAS, números y guion bajo, empezando con letra (máximo 50).';

function obtenerCodigoSistema(): string
{
    return obtenerClavePost('codigoSistema', PATRON_CODIGO_SISTEMA, CODIGO_LONGITUD_MAXIMA, MENSAJE_CODIGO_SISTEMA);
}

$opcion = obtenerTextoPost('opcion') ?? '';
$conn = null;

try {
    require __DIR__ . '/conexion.php';

    switch ($opcion) {
        case '1':
            responderOk([
                'aplicaciones' => consultarAplicaciones($conn),
                'codigoPropio' => CODIGO_SISTEMA,
            ]);

        case '2':
            $codigoSistema = obtenerCodigoSistema();
            $aplicacion = obtenerAplicacion($conn, $codigoSistema);

            if ($aplicacion === null) {
                responderError('La aplicación no existe.', 404);
            }

            responderOk([
                'aplicacion' => $aplicacion,
                'modulos'    => consultarModulosDeAplicacion($conn, $codigoSistema),
                'roles'      => consultarRolesDeAplicacion($conn, $codigoSistema),
            ]);

        case '3':
            $codigoSistema = obtenerCodigoSistema();
            $nombre = obtenerTextoObligatorio('nombre', 100, 'Escribe el nombre de la aplicación (máximo 100 caracteres).');
            $descripcion = obtenerTextoOpcional('descripcion', 250, 'La descripción no puede pasar de 250 caracteres.');
            $activo = obtenerBanderaPost('activo', 'Selecciona un estado válido.');

            $conn->beginTransaction();

            if (existeAplicacion($conn, $codigoSistema)) {
                $conn->rollBack();
                responderError('Ya existe una aplicación con el código ' . $codigoSistema . '.', 409);
            }

            insertarAplicacion($conn, $codigoSistema, $nombre, $descripcion, $activo);
            $conn->commit();

            responderOk(['codigoSistema' => $codigoSistema], 'Aplicación ' . $codigoSistema . ' registrada.');

        case '4':
            $codigoSistema = obtenerCodigoSistema();
            $nombre = obtenerTextoObligatorio('nombre', 100, 'Escribe el nombre de la aplicación (máximo 100 caracteres).');
            $descripcion = obtenerTextoOpcional('descripcion', 250, 'La descripción no puede pasar de 250 caracteres.');

            $conn->beginTransaction();

            if (obtenerAplicacion($conn, $codigoSistema, true) === null) {
                $conn->rollBack();
                responderError('La aplicación no existe.', 404);
            }

            actualizarAplicacion($conn, $codigoSistema, $nombre, $descripcion);
            $conn->commit();

            responderOk(['codigoSistema' => $codigoSistema], 'Aplicación ' . $codigoSistema . ' actualizada.');

        case '5':
            $codigoSistema = obtenerCodigoSistema();
            $activo = obtenerBanderaPost('activo', 'El estado solicitado no es válido.');

            if ($activo === 0 && $codigoSistema === CODIGO_SISTEMA) {
                responderError('No se puede desactivar esta misma aplicación de administración.', 400);
            }

            $conn->beginTransaction();
            $aplicacion = obtenerAplicacion($conn, $codigoSistema, true);

            if ($aplicacion === null) {
                $conn->rollBack();
                responderError('La aplicación no existe.', 404);
            }

            if ($aplicacion['activo'] === ($activo === 1)) {
                $conn->rollBack();
                responderOk(['codigoSistema' => $codigoSistema], 'La aplicación ya estaba ' . ($activo ? 'activa' : 'inactiva') . '.');
            }

            actualizarActivoAplicacion($conn, $codigoSistema, $activo);
            $conn->commit();

            responderOk(
                ['codigoSistema' => $codigoSistema],
                $activo
                    ? 'Aplicación ' . $codigoSistema . ' activada.'
                    : 'Aplicación ' . $codigoSistema . ' desactivada. Sus roles y permisos dejan de tener efecto.'
            );

        case '6':
            $codigoSistema = obtenerCodigoSistema();

            if ($codigoSistema === CODIGO_SISTEMA) {
                responderError('No se puede eliminar esta misma aplicación de administración.', 400);
            }

            $conn->beginTransaction();

            if (obtenerAplicacion($conn, $codigoSistema, true) === null) {
                $conn->rollBack();
                responderError('La aplicación no existe.', 404);
            }

            $relaciones = contarRelacionesAplicacion($conn, $codigoSistema);

            if ($relaciones['modulos'] > 0 || $relaciones['roles'] > 0) {
                $conn->rollBack();
                responderError(
                    'No se puede eliminar: tiene ' . $relaciones['modulos'] . ' módulo(s) y ' . $relaciones['roles']
                    . ' rol(es). Desactívala en su lugar.',
                    409
                );
            }

            eliminarAplicacion($conn, $codigoSistema);
            $conn->commit();

            responderOk(['codigoSistema' => $codigoSistema], 'Aplicación ' . $codigoSistema . ' eliminada.');

        default:
            responderError('La opción solicitada no existe.', 400);
    }
} catch (PDOException $error) {
    if ($conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }

    if (esViolacionUnica($error)) {
        responderError('Ya existe una aplicación con ese código.', 409);
    }

    if (esViolacionReferencia($error)) {
        responderError('No se puede eliminar porque tiene registros relacionados. Desactívala en su lugar.', 409);
    }

    responderFalloInterno($error, $conn, 'Aplicaciones, opción ' . opcionParaLog($opcion));
} catch (Throwable $error) {
    responderFalloInterno($error, $conn, 'Aplicaciones, opción ' . opcionParaLog($opcion));
}
