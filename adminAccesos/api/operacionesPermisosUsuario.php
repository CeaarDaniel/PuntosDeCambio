<?php
/**
 * api/operacionesPermisosUsuario.php — Pantalla "Permisos individuales"
 * (pages/permisosUsuario.php).
 *
 * Opciones:
 *   '1'  Catálogos: usuarios, aplicaciones, módulos y permisos.
 *   '2'  Excepciones del usuario (vigentes, permanentes y vencidas) y los
 *        permisos que ya recibe por sus roles (para orientar la decisión).
 *   '3'  Crear excepción (conceder o denegar).
 *   '4'  Modificar tipo, motivo y vigencia.
 *   '5'  Retirar: la vence ayer; se conserva como historial.
 *   '6'  Eliminar del historial (solo si ya está vencida).
 *
 * Reglas (contexto.md, módulo 8, y autenticacion-y-permisos.md §2 y §5):
 *  - (nominaUsuario, idPermiso) es único: no puede haber conceder y denegar a
 *    la vez sobre el mismo permiso. Si ya hay una excepción VENCIDA, crear una
 *    nueva reutiliza ese registro.
 *  - vigenciaHasta NULL = sin vencimiento. No se acepta una vigencia anterior a
 *    hoy (al editar, se respeta la fecha que ya tenía).
 *  - El permiso llega como (codigoSistema, codigoModulo, accion) y se resuelve
 *    con obtenerPermiso(): aplicación, módulo y permiso tienen que coincidir.
 *  - otorgadoPor sale de la sesión, nunca del formulario.
 *  - No se crean ni modifican excepciones de usuarios inactivos; retirarlas o
 *    eliminarlas sí se permite.
 *
 * PENDIENTE (PENDIENTES.md §3): exigirPermiso('permisos_usuario', …) en cada opción.
 * PENDIENTE (PENDIENTES.md §1): registrar cada cambio en la auditoría.
 */

require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/consultas/catalogos.php';
require_once __DIR__ . '/consultas/usuarios.php';
require_once __DIR__ . '/consultas/permisos.php';
require_once __DIR__ . '/consultas/permisosEfectivos.php';
require_once __DIR__ . '/consultas/asignaciones.php';

prepararEndpoint();

const TIPOS_EXCEPCION = ['conceder', 'denegar'];
const MOTIVO_LONGITUD_MAXIMA = 250;

function obtenerNominaExcepcion(): string
{
    $nomina = obtenerTextoPost('nomina') ?? '';

    if (!textoEsValido($nomina, NOMINA_LONGITUD_MAXIMA)) {
        responderError('Selecciona un usuario válido.', 400);
    }

    return $nomina;
}

function obtenerClavePermisoExcepcion(): array
{
    return [
        'codigoSistema' => obtenerClavePost('codigoSistema', PATRON_CODIGO_SISTEMA, CODIGO_LONGITUD_MAXIMA, 'Selecciona una aplicación válida.'),
        'codigoModulo'  => obtenerClavePost('codigoModulo', PATRON_CODIGO_MODULO, CODIGO_LONGITUD_MAXIMA, 'Selecciona un módulo válido.'),
        'accion'        => obtenerClavePost('accion', PATRON_ACCION, ACCION_LONGITUD_MAXIMA, 'Selecciona un permiso válido.'),
    ];
}

/**
 * Tipo, motivo y vigencia de la excepción, ya validados.
 * $vigenciaPermitida: fecha que se acepta aunque sea pasada (la que ya tenía
 * al editar), para no obligar a cambiarla si solo se corrige el motivo.
 */
function obtenerDatosExcepcion(?string $vigenciaPermitida = null): array
{
    $tipo = obtenerTextoPost('tipo') ?? '';

    if (!in_array($tipo, TIPOS_EXCEPCION, true)) {
        responderError('Elige si el permiso se concede o se deniega.', 400);
    }

    $motivo = obtenerTextoPost('motivo') ?? '';

    if (!textoEsValido($motivo, MOTIVO_LONGITUD_MAXIMA)) {
        responderError('Escribe el motivo de la excepción (máximo ' . MOTIVO_LONGITUD_MAXIMA . ' caracteres).', 400);
    }

    $vigencia = obtenerTextoPost('vigenciaHasta') ?? '';

    if ($vigencia === '') {
        $vigencia = null;
    } else {
        $fecha = DateTime::createFromFormat('!Y-m-d', $vigencia);

        if ($fecha === false || $fecha->format('Y-m-d') !== $vigencia) {
            responderError('La fecha de vigencia no es válida.', 400);
        }

        if ($vigencia < date('Y-m-d') && $vigencia !== $vigenciaPermitida) {
            responderError('La vigencia no puede ser anterior a hoy. Déjala vacía si no vence.', 400);
        }
    }

    return ['tipo' => $tipo, 'motivo' => $motivo, 'vigenciaHasta' => $vigencia];
}

function textoClavePermiso(array $clave): string
{
    return $clave['codigoSistema'] . '.' . $clave['codigoModulo'] . '.' . $clave['accion'];
}

/**
 * Mapa 'sistema.modulo.accion' => [roles] de los permisos que el usuario ya
 * recibe por sus roles activos. Sale de la misma resolución de permisos
 * efectivos, así que respeta aplicaciones, módulos y roles inactivos.
 */
function permisosPorRol(PDO $conn, string $nomina): array
{
    $mapa = [];

    foreach (consultarPermisosEfectivos($conn, $nomina) as $permiso) {
        if (count($permiso['roles']) > 0) {
            $mapa[$permiso['codigoSistema'] . '.' . $permiso['codigoModulo'] . '.' . $permiso['accion']] = $permiso['roles'];
        }
    }

    return $mapa;
}

$opcion = obtenerTextoPost('opcion') ?? '';
$conn = null;

try {
    require __DIR__ . '/conexion.php';

    switch ($opcion) {
        case '1':
            responderOk([
                'usuarios'     => consultarUsuarios($conn),
                'aplicaciones' => consultarAplicacionesCatalogo($conn),
                'modulos'      => consultarModulosCatalogo($conn),
                'permisos'     => consultarPermisos($conn),
            ]);

        case '2':
            $nomina = obtenerNominaExcepcion();
            $usuario = consultarUsuario($conn, $nomina);

            if ($usuario === null) {
                responderError('El usuario no existe.', 404);
            }

            responderOk([
                'usuario'     => $usuario,
                'excepciones' => consultarExcepcionesDeUsuario($conn, $nomina),
                // Objeto JSON aunque esté vacío (no arreglo), para que el JS lo use igual.
                'porRol'      => (object) permisosPorRol($conn, $nomina),
                'hoy'         => date('Y-m-d'),
            ]);

        case '3':
            $nomina = obtenerNominaExcepcion();
            $clave = obtenerClavePermisoExcepcion();
            $datos = obtenerDatosExcepcion();

            $conn->beginTransaction();
            $usuario = consultarUsuario($conn, $nomina, true);

            if ($usuario === null) {
                $conn->rollBack();
                responderError('El usuario no existe.', 404);
            }

            if ($usuario['estatus'] !== 'activo') {
                $conn->rollBack();
                responderError('El usuario está inactivo: no se le pueden asignar permisos. Actívalo primero.', 400);
            }

            $permiso = obtenerPermiso($conn, $clave['codigoSistema'], $clave['codigoModulo'], $clave['accion']);

            if ($permiso === null) {
                $conn->rollBack();
                responderError('El permiso ' . textoClavePermiso($clave) . ' no existe.', 400);
            }

            $existente = obtenerExcepcion($conn, $nomina, $permiso['idPermiso'], true);

            if ($existente !== null && $existente['estadoVigencia'] !== 'vencido') {
                $conn->rollBack();
                responderError(
                    'El usuario ya tiene una excepción vigente sobre ' . textoClavePermiso($clave) . ' ('
                    . $existente['tipo'] . '). Edítala en lugar de crear otra.',
                    409
                );
            }

            /* Una excepción vencida ocupa el lugar por la restricción UNIQUE:
               se reutiliza su registro con los datos nuevos. */
            if ($existente !== null) {
                actualizarExcepcion($conn, $nomina, $permiso['idPermiso'], $datos['tipo'], $datos['motivo'], $datos['vigenciaHasta'], nominaEnSesion());
            } else {
                insertarExcepcion($conn, $nomina, $permiso['idPermiso'], $datos['tipo'], $datos['motivo'], $datos['vigenciaHasta'], nominaEnSesion());
            }

            $conn->commit();

            responderOk(
                [],
                'Permiso ' . textoClavePermiso($clave) . ($datos['tipo'] === 'conceder' ? ' concedido' : ' denegado')
                . ' a ' . $nomina . ($datos['vigenciaHasta'] ? ' hasta el ' . date('d/m/Y', strtotime($datos['vigenciaHasta'])) : ' sin vencimiento')
                . '. Tendrá efecto cuando vuelva a iniciar sesión.'
            );

        case '4':
            $nomina = obtenerNominaExcepcion();
            $clave = obtenerClavePermisoExcepcion();

            $conn->beginTransaction();
            $usuario = consultarUsuario($conn, $nomina, true);

            if ($usuario === null) {
                $conn->rollBack();
                responderError('El usuario no existe.', 404);
            }

            if ($usuario['estatus'] !== 'activo') {
                $conn->rollBack();
                responderError('El usuario está inactivo: sus excepciones no se pueden modificar, solo retirar.', 400);
            }

            $permiso = obtenerPermiso($conn, $clave['codigoSistema'], $clave['codigoModulo'], $clave['accion']);
            $existente = $permiso !== null ? obtenerExcepcion($conn, $nomina, $permiso['idPermiso'], true) : null;

            if ($existente === null) {
                $conn->rollBack();
                responderError('La excepción no existe.', 404);
            }

            $datos = obtenerDatosExcepcion($existente['vigenciaHasta']);
            actualizarExcepcion($conn, $nomina, $permiso['idPermiso'], $datos['tipo'], $datos['motivo'], $datos['vigenciaHasta'], nominaEnSesion());
            $conn->commit();

            responderOk([], 'Excepción sobre ' . textoClavePermiso($clave) . ' actualizada.');

        case '5':
            $nomina = obtenerNominaExcepcion();
            $clave = obtenerClavePermisoExcepcion();

            $conn->beginTransaction();
            $permiso = obtenerPermiso($conn, $clave['codigoSistema'], $clave['codigoModulo'], $clave['accion']);
            $existente = $permiso !== null ? obtenerExcepcion($conn, $nomina, $permiso['idPermiso'], true) : null;

            if ($existente === null) {
                $conn->rollBack();
                responderError('La excepción no existe.', 404);
            }

            if ($existente['estadoVigencia'] === 'vencido') {
                $conn->rollBack();
                responderOk([], 'La excepción ya estaba vencida.');
            }

            vencerExcepcion($conn, $nomina, $permiso['idPermiso']);
            $conn->commit();

            responderOk([], 'Excepción sobre ' . textoClavePermiso($clave) . ' retirada. Se conserva en el historial como vencida.');

        case '6':
            $nomina = obtenerNominaExcepcion();
            $clave = obtenerClavePermisoExcepcion();

            $conn->beginTransaction();
            $permiso = obtenerPermiso($conn, $clave['codigoSistema'], $clave['codigoModulo'], $clave['accion']);
            $existente = $permiso !== null ? obtenerExcepcion($conn, $nomina, $permiso['idPermiso'], true) : null;

            if ($existente === null) {
                $conn->rollBack();
                responderError('La excepción no existe.', 404);
            }

            if ($existente['estadoVigencia'] !== 'vencido') {
                $conn->rollBack();
                responderError('Solo se pueden eliminar excepciones vencidas. Retírala primero.', 409);
            }

            eliminarExcepcion($conn, $nomina, $permiso['idPermiso']);
            $conn->commit();

            responderOk([], 'Excepción vencida sobre ' . textoClavePermiso($clave) . ' eliminada del historial.');

        default:
            responderError('La opción solicitada no existe.', 400);
    }
} catch (PDOException $error) {
    if ($conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }

    if (esViolacionUnica($error)) {
        responderError('El usuario ya tiene una excepción sobre ese permiso. Recarga la pantalla.', 409);
    }

    responderFalloInterno($error, $conn, 'Permisos individuales, opción ' . opcionParaLog($opcion));
} catch (Throwable $error) {
    responderFalloInterno($error, $conn, 'Permisos individuales, opción ' . opcionParaLog($opcion));
}
