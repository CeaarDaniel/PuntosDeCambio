<?php
/**
 * api/operacionesRoles.php — Pantalla de roles (pages/roles.php).
 *
 * Opciones:
 *   '1'  Listar roles + catálogo de aplicaciones.
 *   '2'  Detalle: usuarios que tienen el rol y sus permisos.
 *   '3'  Crear.
 *   '4'  Modificar nombre y descripción.
 *   '5'  Activar o desactivar.
 *   '6'  Eliminar (solo si no tiene usuarios ni permisos).
 *   '7'  Lista de checks: permisos de la aplicación del rol + los de otros
 *        roles de la misma aplicación (para copiar).
 *   '8'  Guardar la asignación de permisos.
 *
 * Reglas (contexto.md, módulo 6):
 *  - (codigoSistema, codigoRol) es único; codigoRol no cambia después del
 *    alta porque el código de la aplicación puede compararlo (tieneRol()).
 *  - Un rol SOLO recibe permisos de módulos de su misma aplicación: los
 *    permisos llegan como claves 'codigoModulo.accion' y se resuelven contra
 *    mapaPermisosDeAplicacion() de la aplicación del rol. Una clave que no
 *    esté ahí rechaza toda la operación.
 *  - La asignación se guarda en una transacción, aplicando solo la diferencia.
 *  - No se elimina un rol con usuarios; tampoco con permisos (hay que
 *    quitarlos antes, para que el borrado nunca se lleve datos sin verlos).
 *
 * PENDIENTE (PENDIENTES.md §3): exigirPermiso('roles', …) en cada opción.
 * PENDIENTE (PENDIENTES.md §1): registrar cada cambio en la auditoría.
 */

require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/consultas/catalogos.php';
require_once __DIR__ . '/consultas/roles.php';

prepararEndpoint();

/* Tope de seguridad para la lista de permisos de un rol. */
const MAXIMO_PERMISOS_POR_ROL = 2000;

function obtenerClaveRol(): array
{
    return [
        'codigoSistema' => obtenerClavePost('codigoSistema', PATRON_CODIGO_SISTEMA, CODIGO_LONGITUD_MAXIMA, 'Selecciona una aplicación válida.'),
        'codigoRol'     => obtenerClavePost(
            'codigoRol',
            PATRON_CODIGO_ROL,
            CODIGO_LONGITUD_MAXIMA,
            'El código del rol solo puede tener minúsculas sin acentos, números y guion bajo, empezando con letra (máximo 50). Ej. sup_calidad'
        ),
    ];
}

function obtenerDatosEditablesRol(): array
{
    return [
        'nombreRol'   => obtenerTextoObligatorio('nombreRol', 100, 'Escribe el nombre del rol (máximo 100 caracteres).'),
        'descripcion' => obtenerTextoOpcional('descripcion', 250, 'La descripción no puede pasar de 250 caracteres.'),
    ];
}

/**
 * Lee la lista de permisos elegidos: un JSON con claves 'codigoModulo.accion'.
 * Se envía como JSON (y no como arreglo de formulario) para que una lista
 * vacía sea explícita y nunca se confunda con un campo que no llegó.
 *
 * @return string[] claves únicas
 */
function obtenerListaPermisos(): array
{
    $texto = obtenerTextoPost('permisos');
    $lista = $texto !== null ? json_decode($texto, true) : null;

    if (!is_array($lista) || array_values($lista) !== $lista || count($lista) > MAXIMO_PERMISOS_POR_ROL) {
        responderError('La lista de permisos no es válida.', 400);
    }

    $patron = '/^' . PATRON_CODIGO_MODULO . '\.' . PATRON_ACCION . '$/';

    foreach ($lista as $clave) {
        if (!is_string($clave) || strlen($clave) > CODIGO_LONGITUD_MAXIMA + 1 + ACCION_LONGITUD_MAXIMA || !preg_match($patron, $clave)) {
            responderError('La lista de permisos tiene una clave no válida.', 400);
        }
    }

    return array_values(array_unique($lista));
}

$opcion = obtenerTextoPost('opcion') ?? '';
$conn = null;

try {
    require __DIR__ . '/conexion.php';

    switch ($opcion) {
        case '1':
            responderOk([
                'roles'        => consultarRoles($conn),
                'aplicaciones' => consultarAplicacionesCatalogo($conn),
            ]);

        case '2':
            $clave = obtenerClaveRol();
            $rol = obtenerRol($conn, $clave['codigoSistema'], $clave['codigoRol']);

            if ($rol === null) {
                responderError('El rol no existe.', 404);
            }

            $permisos = array_values(array_filter(
                consultarPermisosAsignables($conn, $rol['codigoSistema'], $rol['idRol']),
                function ($p) { return $p['asignado']; }
            ));

            responderOk([
                'rol'      => sinIdsInternos($rol),
                'usuarios' => consultarUsuariosDeRol($conn, $rol['idRol']),
                'permisos' => $permisos,
            ]);

        case '3':
            $clave = obtenerClaveRol();
            $datos = obtenerDatosEditablesRol();
            $activo = obtenerBanderaPost('activo', 'Selecciona un estado válido.');

            $conn->beginTransaction();

            if (obtenerAplicacion($conn, $clave['codigoSistema'], true) === null) {
                $conn->rollBack();
                responderError('La aplicación seleccionada no existe.', 400);
            }

            if (existeRol($conn, $clave['codigoSistema'], $clave['codigoRol'])) {
                $conn->rollBack();
                responderError('La aplicación ' . $clave['codigoSistema'] . ' ya tiene un rol con el código ' . $clave['codigoRol'] . '.', 409);
            }

            insertarRol($conn, $clave['codigoSistema'], $clave['codigoRol'], $datos['nombreRol'], $datos['descripcion'], $activo);
            $conn->commit();

            responderOk(
                ['codigoSistema' => $clave['codigoSistema'], 'codigoRol' => $clave['codigoRol']],
                'Rol ' . $clave['codigoRol'] . ' registrado en ' . $clave['codigoSistema'] . '. Ahora asígnale sus permisos.'
            );

        case '4':
            $clave = obtenerClaveRol();
            $datos = obtenerDatosEditablesRol();

            $conn->beginTransaction();
            $rol = obtenerRol($conn, $clave['codigoSistema'], $clave['codigoRol'], true);

            if ($rol === null) {
                $conn->rollBack();
                responderError('El rol no existe.', 404);
            }

            actualizarRol($conn, $rol['idRol'], $datos['nombreRol'], $datos['descripcion']);
            $conn->commit();

            responderOk([], 'Rol ' . $clave['codigoRol'] . ' actualizado.');

        case '5':
            $clave = obtenerClaveRol();
            $activo = obtenerBanderaPost('activo', 'El estado solicitado no es válido.');

            $conn->beginTransaction();
            $rol = obtenerRol($conn, $clave['codigoSistema'], $clave['codigoRol'], true);

            if ($rol === null) {
                $conn->rollBack();
                responderError('El rol no existe.', 404);
            }

            if ($rol['activo'] === ($activo === 1)) {
                $conn->rollBack();
                responderOk([], 'El rol ya estaba ' . ($activo ? 'activo' : 'inactivo') . '.');
            }

            actualizarActivoRol($conn, $rol['idRol'], $activo);
            $conn->commit();

            responderOk(
                [],
                $activo
                    ? 'Rol ' . $clave['codigoRol'] . ' activado.'
                    : 'Rol ' . $clave['codigoRol'] . ' desactivado. Deja de dar permisos a sus usuarios.'
            );

        case '6':
            $clave = obtenerClaveRol();

            $conn->beginTransaction();
            $rol = obtenerRol($conn, $clave['codigoSistema'], $clave['codigoRol'], true);

            if ($rol === null) {
                $conn->rollBack();
                responderError('El rol no existe.', 404);
            }

            $relaciones = contarRelacionesRol($conn, $rol['idRol']);

            if ($relaciones['usuarios'] > 0 || $relaciones['permisos'] > 0) {
                $conn->rollBack();
                responderError(
                    'No se puede eliminar: tiene ' . $relaciones['usuarios'] . ' usuario(s) y ' . $relaciones['permisos']
                    . ' permiso(s). Quítalos primero o desactiva el rol.',
                    409
                );
            }

            eliminarRol($conn, $rol['idRol']);
            $conn->commit();

            responderOk([], 'Rol ' . $clave['codigoRol'] . ' eliminado.');

        case '7':
            $clave = obtenerClaveRol();
            $rol = obtenerRol($conn, $clave['codigoSistema'], $clave['codigoRol']);

            if ($rol === null) {
                responderError('El rol no existe.', 404);
            }

            responderOk([
                'rol'        => sinIdsInternos($rol),
                'permisos'   => consultarPermisosAsignables($conn, $rol['codigoSistema'], $rol['idRol']),
                'otrosRoles' => consultarPermisosDeOtrosRoles($conn, $rol['codigoSistema'], $rol['idRol']),
            ]);

        case '8':
            $clave = obtenerClaveRol();
            $elegidos = obtenerListaPermisos();

            $conn->beginTransaction();
            $rol = obtenerRol($conn, $clave['codigoSistema'], $clave['codigoRol'], true);

            if ($rol === null) {
                $conn->rollBack();
                responderError('El rol no existe.', 404);
            }

            /* Validar todo antes de escribir: cada clave debe ser un permiso de
               la MISMA aplicación del rol. */
            $mapa = mapaPermisosDeAplicacion($conn, $rol['codigoSistema']);
            $idsElegidos = [];

            foreach ($elegidos as $claveElegida) {
                if (!isset($mapa[$claveElegida])) {
                    $conn->rollBack();
                    responderError('El permiso ' . $claveElegida . ' no pertenece a la aplicación ' . $rol['codigoSistema'] . '.', 400);
                }

                $idsElegidos[] = $mapa[$claveElegida];
            }

            /* Solo se consideran los permisos de la aplicación del rol: si
               existiera alguna relación con otra aplicación (inconsistencia),
               no se toca aquí; la reporta el dashboard. */
            $idsDeLaAplicacion = array_values($mapa);
            $actuales = array_values(array_intersect(permisosActualesDeRol($conn, $rol['idRol']), $idsDeLaAplicacion));

            $agregar = array_values(array_diff($idsElegidos, $actuales));
            $quitar = array_values(array_diff($actuales, $idsElegidos));

            foreach ($quitar as $idPermiso) {
                quitarPermisoDeRol($conn, $rol['idRol'], $idPermiso);
            }

            foreach ($agregar as $idPermiso) {
                agregarPermisoARol($conn, $rol['idRol'], $idPermiso);
            }

            $conn->commit();

            responderOk(
                ['agregados' => count($agregar), 'quitados' => count($quitar), 'total' => count($idsElegidos)],
                count($agregar) === 0 && count($quitar) === 0
                    ? 'No había cambios que guardar.'
                    : 'Permisos de ' . $clave['codigoRol'] . ' guardados: ' . count($agregar) . ' agregado(s), '
                        . count($quitar) . ' quitado(s). Los usuarios verán el cambio al volver a iniciar sesión.'
            );

        default:
            responderError('La opción solicitada no existe.', 400);
    }
} catch (PDOException $error) {
    if ($conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }

    if (esViolacionUnica($error)) {
        responderError('Ya existe un rol con ese código en la aplicación, o el permiso ya estaba asignado.', 409);
    }

    if (esViolacionReferencia($error)) {
        responderError('No se puede completar: el rol tiene registros relacionados.', 409);
    }

    responderFalloInterno($error, $conn, 'Roles, opción ' . opcionParaLog($opcion));
} catch (Throwable $error) {
    responderFalloInterno($error, $conn, 'Roles, opción ' . opcionParaLog($opcion));
}
