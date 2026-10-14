<?php
/**
 * api/operacionesPermisos.php — Pantalla de permisos (pages/permisos.php).
 *
 * Opciones:
 *   '1'  Listar permisos + catálogos de aplicaciones y módulos.
 *   '2'  Relaciones: roles, usuarios por rol y excepciones individuales.
 *   '3'  Crear.
 *   '4'  Modificar la descripción.
 *   '5'  Eliminar (solo si no está asignado a ningún rol ni usuario).
 *
 * Reglas (contexto.md, módulo 5):
 *  - (idModulo, accion) es único. Un permiso se identifica siempre por
 *    (codigoSistema, codigoModulo, accion); el idPermiso no sale al navegador.
 *  - accion es una clave estable: minúsculas, dígitos y guion bajo, sin
 *    espacios ni acentos. No se cambia después del alta.
 *  - No se elimina un permiso asignado. permisos no tiene columna activo, así
 *    que no hay baja controlada (PENDIENTES.md §1); la pantalla muestra antes
 *    sus relaciones.
 *
 * PENDIENTE (PENDIENTES.md §3): exigirPermiso('permisos', …) en cada opción.
 * PENDIENTE (PENDIENTES.md §1): registrar cada cambio en la auditoría.
 */

require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/consultas/catalogos.php';
require_once __DIR__ . '/consultas/permisos.php';

prepararEndpoint();

/**
 * Lee la clave completa del permiso (aplicación, módulo y acción).
 */
function obtenerClavePermiso(): array
{
    return [
        'codigoSistema' => obtenerClavePost('codigoSistema', PATRON_CODIGO_SISTEMA, CODIGO_LONGITUD_MAXIMA, 'Selecciona una aplicación válida.'),
        'codigoModulo'  => obtenerClavePost('codigoModulo', PATRON_CODIGO_MODULO, CODIGO_LONGITUD_MAXIMA, 'Selecciona un módulo válido.'),
        'accion'        => obtenerClavePost(
            'accion',
            PATRON_ACCION,
            ACCION_LONGITUD_MAXIMA,
            'La acción solo puede tener minúsculas sin acentos, números y guion bajo, empezando con letra (máximo 30). Ej. registro_inicial'
        ),
    ];
}

function textoClave(array $clave): string
{
    return $clave['codigoSistema'] . '.' . $clave['codigoModulo'] . '.' . $clave['accion'];
}

$opcion = obtenerTextoPost('opcion') ?? '';
$conn = null;

try {
    require __DIR__ . '/conexion.php';

    switch ($opcion) {
        case '1':
            responderOk([
                'permisos'     => consultarPermisos($conn),
                'aplicaciones' => consultarAplicacionesCatalogo($conn),
                'modulos'      => consultarModulosCatalogo($conn),
            ]);

        case '2':
            $clave = obtenerClavePermiso();
            $permiso = obtenerPermiso($conn, $clave['codigoSistema'], $clave['codigoModulo'], $clave['accion']);

            if ($permiso === null) {
                responderError('El permiso no existe.', 404);
            }

            responderOk(consultarRelacionesPermiso($conn, $permiso['idPermiso']));

        case '3':
            $clave = obtenerClavePermiso();
            $descripcion = obtenerTextoOpcional('descripcion', 250, 'La descripción no puede pasar de 250 caracteres.');

            $conn->beginTransaction();

            /* El módulo se busca dentro de la aplicación indicada: así un módulo
               de otra aplicación nunca puede recibir el permiso. */
            $modulo = obtenerModulo($conn, $clave['codigoSistema'], $clave['codigoModulo'], true);

            if ($modulo === null) {
                $conn->rollBack();
                responderError('El módulo seleccionado no existe en esa aplicación.', 400);
            }

            if (existePermiso($conn, $modulo['idModulo'], $clave['accion'])) {
                $conn->rollBack();
                responderError('El permiso ' . textoClave($clave) . ' ya existe.', 409);
            }

            insertarPermiso($conn, $modulo['idModulo'], $clave['accion'], $descripcion);
            $conn->commit();

            responderOk([], 'Permiso ' . textoClave($clave) . ' registrado.');

        case '4':
            $clave = obtenerClavePermiso();
            $descripcion = obtenerTextoOpcional('descripcion', 250, 'La descripción no puede pasar de 250 caracteres.');

            $conn->beginTransaction();
            $permiso = obtenerPermiso($conn, $clave['codigoSistema'], $clave['codigoModulo'], $clave['accion'], true);

            if ($permiso === null) {
                $conn->rollBack();
                responderError('El permiso no existe.', 404);
            }

            actualizarDescripcionPermiso($conn, $permiso['idPermiso'], $descripcion);
            $conn->commit();

            responderOk([], 'Descripción de ' . textoClave($clave) . ' actualizada.');

        case '5':
            $clave = obtenerClavePermiso();

            $conn->beginTransaction();
            $permiso = obtenerPermiso($conn, $clave['codigoSistema'], $clave['codigoModulo'], $clave['accion'], true);

            if ($permiso === null) {
                $conn->rollBack();
                responderError('El permiso no existe.', 404);
            }

            $asignaciones = contarAsignacionesPermiso($conn, $permiso['idPermiso']);

            if ($asignaciones['roles'] > 0 || $asignaciones['excepciones'] > 0) {
                $conn->rollBack();
                responderError(
                    'No se puede eliminar: está asignado a ' . $asignaciones['roles'] . ' rol(es) y tiene '
                    . $asignaciones['excepciones'] . ' excepción(es) individual(es). Quita primero esas asignaciones.',
                    409
                );
            }

            eliminarPermiso($conn, $permiso['idPermiso']);
            $conn->commit();

            responderOk([], 'Permiso ' . textoClave($clave) . ' eliminado.');

        default:
            responderError('La opción solicitada no existe.', 400);
    }
} catch (PDOException $error) {
    if ($conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }

    if (esViolacionUnica($error)) {
        responderError('Ese permiso ya existe en el módulo.', 409);
    }

    if (esViolacionReferencia($error)) {
        responderError('No se puede eliminar: el permiso tiene asignaciones.', 409);
    }

    responderFalloInterno($error, $conn, 'Permisos, opción ' . opcionParaLog($opcion));
} catch (Throwable $error) {
    responderFalloInterno($error, $conn, 'Permisos, opción ' . opcionParaLog($opcion));
}
