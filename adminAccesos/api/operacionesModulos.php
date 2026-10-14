<?php
/**
 * api/operacionesModulos.php — Pantalla de módulos (pages/modulos.php).
 *
 * Opciones:
 *   '1'  Listar módulos (todas las aplicaciones) + catálogo de aplicaciones.
 *   '2'  Detalle: permisos del módulo.
 *   '3'  Crear (opcionalmente con su acción "ver").
 *   '4'  Modificar nombre, ruta, icono y orden.
 *   '5'  Activar o desactivar.
 *   '6'  Eliminar (solo si no tiene permisos).
 *
 * Reglas (contexto.md, módulo 4):
 *  - (codigoSistema, codigoModulo) es único y el módulo pertenece a una
 *    aplicación existente.
 *  - codigoModulo NO se cambia después del alta: el código de la aplicación
 *    lo usa literalmente ('anormalidades.analisis_qa').
 *  - Un módulo se identifica siempre por (codigoSistema, codigoModulo); el
 *    idModulo se busca aquí y no sale al navegador.
 *
 * PENDIENTE (PENDIENTES.md §3): exigirPermiso('modulos', …) en cada opción.
 * PENDIENTE (PENDIENTES.md §1): registrar cada cambio en la auditoría.
 */

require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/consultas/catalogos.php';
require_once __DIR__ . '/consultas/modulos.php';
require_once __DIR__ . '/consultas/permisos.php';

prepararEndpoint();

function obtenerCodigoSistemaModulo(): string
{
    return obtenerClavePost('codigoSistema', PATRON_CODIGO_SISTEMA, CODIGO_LONGITUD_MAXIMA, 'Selecciona una aplicación válida.');
}

function obtenerCodigoModulo(): string
{
    return obtenerClavePost(
        'codigoModulo',
        PATRON_CODIGO_MODULO,
        CODIGO_LONGITUD_MAXIMA,
        'El código del módulo solo puede tener letras sin acentos, números y guion bajo, empezando con letra (máximo 50).'
    );
}

/**
 * Campos editables del módulo: nombre, ruta, icono y orden.
 */
function obtenerDatosEditablesModulo(): array
{
    $icono = obtenerTextoPost('icono') ?? '';

    if ($icono !== '' && (strlen($icono) > 50 || !preg_match(patronClave(PATRON_ICONO), $icono))) {
        responderError('El icono debe ser una clase de Bootstrap Icons, por ejemplo bi-diagram-3.', 400);
    }

    $ruta = obtenerTextoOpcional('ruta', 100, 'La ruta no puede pasar de 100 caracteres.');

    if ($ruta !== null && preg_match('/[\r\n\t]/', $ruta)) {
        responderError('La ruta debe ir en una sola línea.', 400);
    }

    return [
        'nombre' => obtenerTextoObligatorio('nombre', 100, 'Escribe el nombre del módulo (máximo 100 caracteres).'),
        'ruta'   => $ruta,
        'icono'  => $icono === '' ? null : $icono,
        'orden'  => obtenerEnteroPost('orden', 0, 9999, 'El orden debe ser un número entre 0 y 9999.'),
    ];
}

$opcion = obtenerTextoPost('opcion') ?? '';
$conn = null;

try {
    require __DIR__ . '/conexion.php';

    switch ($opcion) {
        case '1':
            responderOk([
                'modulos'      => consultarModulos($conn),
                'aplicaciones' => consultarAplicacionesCatalogo($conn),
            ]);

        case '2':
            $modulo = obtenerModulo($conn, obtenerCodigoSistemaModulo(), obtenerCodigoModulo());

            if ($modulo === null) {
                responderError('El módulo no existe.', 404);
            }

            responderOk([
                'modulo'   => sinIdsInternos($modulo),
                'permisos' => consultarPermisosDeModulo($conn, $modulo['idModulo']),
            ]);

        case '3':
            $codigoSistema = obtenerCodigoSistemaModulo();
            $codigoModulo = obtenerCodigoModulo();
            $datos = obtenerDatosEditablesModulo();
            $activo = obtenerBanderaPost('activo', 'Selecciona un estado válido.');
            $crearVer = obtenerBanderaPost('crearVer', 'Indica si se crea la acción "ver".');

            $conn->beginTransaction();

            /* Bloquea la aplicación para que no se elimine mientras se le agrega el módulo. */
            if (obtenerAplicacion($conn, $codigoSistema, true) === null) {
                $conn->rollBack();
                responderError('La aplicación seleccionada no existe.', 400);
            }

            if (existeModulo($conn, $codigoSistema, $codigoModulo)) {
                $conn->rollBack();
                responderError('La aplicación ' . $codigoSistema . ' ya tiene un módulo con el código ' . $codigoModulo . '.', 409);
            }

            $idModulo = insertarModulo(
                $conn, $codigoSistema, $codigoModulo,
                $datos['nombre'], $datos['ruta'], $datos['icono'], $datos['orden'], $activo
            );

            /* Por convención todo módulo tiene la acción "ver" (permiso de entrada). */
            if ($crearVer === 1) {
                insertarPermiso($conn, $idModulo, 'ver', 'Entrar al módulo ' . $datos['nombre'] . '.');
            }

            $conn->commit();

            responderOk(
                ['codigoSistema' => $codigoSistema, 'codigoModulo' => $codigoModulo],
                'Módulo ' . $codigoSistema . '.' . $codigoModulo . ' registrado' . ($crearVer === 1 ? ' con su acción "ver".' : '.')
            );

        case '4':
            $codigoSistema = obtenerCodigoSistemaModulo();
            $codigoModulo = obtenerCodigoModulo();
            $datos = obtenerDatosEditablesModulo();

            $conn->beginTransaction();
            $modulo = obtenerModulo($conn, $codigoSistema, $codigoModulo, true);

            if ($modulo === null) {
                $conn->rollBack();
                responderError('El módulo no existe.', 404);
            }

            actualizarModulo($conn, $modulo['idModulo'], $datos['nombre'], $datos['ruta'], $datos['icono'], $datos['orden']);
            $conn->commit();

            responderOk([], 'Módulo ' . $codigoSistema . '.' . $codigoModulo . ' actualizado.');

        case '5':
            $codigoSistema = obtenerCodigoSistemaModulo();
            $codigoModulo = obtenerCodigoModulo();
            $activo = obtenerBanderaPost('activo', 'El estado solicitado no es válido.');

            $conn->beginTransaction();
            $modulo = obtenerModulo($conn, $codigoSistema, $codigoModulo, true);

            if ($modulo === null) {
                $conn->rollBack();
                responderError('El módulo no existe.', 404);
            }

            if ($modulo['activo'] === ($activo === 1)) {
                $conn->rollBack();
                responderOk([], 'El módulo ya estaba ' . ($activo ? 'activo' : 'inactivo') . '.');
            }

            actualizarActivoModulo($conn, $modulo['idModulo'], $activo);
            $conn->commit();

            responderOk(
                [],
                $activo
                    ? 'Módulo ' . $codigoSistema . '.' . $codigoModulo . ' activado.'
                    : 'Módulo ' . $codigoSistema . '.' . $codigoModulo . ' desactivado. Sus permisos dejan de tener efecto.'
            );

        case '6':
            $codigoSistema = obtenerCodigoSistemaModulo();
            $codigoModulo = obtenerCodigoModulo();

            $conn->beginTransaction();
            $modulo = obtenerModulo($conn, $codigoSistema, $codigoModulo, true);

            if ($modulo === null) {
                $conn->rollBack();
                responderError('El módulo no existe.', 404);
            }

            $permisos = contarPermisosDeModulo($conn, $modulo['idModulo']);

            if ($permisos > 0) {
                $conn->rollBack();
                responderError(
                    'No se puede eliminar: tiene ' . $permisos . ' permiso(s). Elimina primero los que no estén asignados, o desactiva el módulo.',
                    409
                );
            }

            eliminarModulo($conn, $modulo['idModulo']);
            $conn->commit();

            responderOk([], 'Módulo ' . $codigoSistema . '.' . $codigoModulo . ' eliminado.');

        default:
            responderError('La opción solicitada no existe.', 400);
    }
} catch (PDOException $error) {
    if ($conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }

    if (esViolacionUnica($error)) {
        responderError('Ya existe un módulo con ese código en la aplicación.', 409);
    }

    if (esViolacionReferencia($error)) {
        responderError('No se puede completar: el módulo tiene registros relacionados o la aplicación no existe.', 409);
    }

    responderFalloInterno($error, $conn, 'Módulos, opción ' . opcionParaLog($opcion));
} catch (Throwable $error) {
    responderFalloInterno($error, $conn, 'Módulos, opción ' . opcionParaLog($opcion));
}
