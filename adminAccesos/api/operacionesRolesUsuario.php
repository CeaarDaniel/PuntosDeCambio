<?php
/**
 * api/operacionesRolesUsuario.php — Pantalla "Roles de usuario" (pages/rolesUsuario.php).
 *
 * Opciones:
 *   '1'  Catálogos: usuarios (para el buscador) y aplicaciones.
 *   '2'  Roles de una aplicación marcando los del usuario, más el resumen de
 *        sus roles en todas las aplicaciones.
 *   '3'  Guardar los roles del usuario en esa aplicación (se aplica la diferencia).
 *
 * Reglas (contexto.md, módulo 7):
 *  - No se duplica (nominaUsuario, idRol) (también lo impide UQ_usuario_roles).
 *  - No se asignan roles a usuarios inactivos ni roles inactivos. Quitar sí se
 *    permite en ambos casos, para poder limpiar.
 *  - Los roles llegan como codigoRol y se resuelven con mapaRolesDeAplicacion()
 *    de la aplicación indicada: un código de otra aplicación se rechaza.
 *  - Las asignaciones múltiples se guardan en una transacción.
 *  - Quién y cuándo asignó cada rol: no hay columnas para eso
 *    (PENDIENTES.md §1).
 *
 * PENDIENTE (PENDIENTES.md §3): exigirPermiso('roles_usuario', …) en cada opción.
 * PENDIENTE (PENDIENTES.md §1): registrar cada cambio en la auditoría.
 */

require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/consultas/catalogos.php';
require_once __DIR__ . '/consultas/usuarios.php';
require_once __DIR__ . '/consultas/asignaciones.php';

prepararEndpoint();

const MAXIMO_ROLES_POR_GUARDADO = 500;

function obtenerNominaAsignacion(): string
{
    $nomina = obtenerTextoPost('nomina') ?? '';

    if (!textoEsValido($nomina, NOMINA_LONGITUD_MAXIMA)) {
        responderError('Selecciona un usuario válido.', 400);
    }

    return $nomina;
}

/**
 * Lista de codigoRol elegidos, enviada como JSON para que la lista vacía sea
 * explícita.
 *
 * @return string[]
 */
function obtenerListaRoles(): array
{
    $texto = obtenerTextoPost('roles');
    $lista = $texto !== null ? json_decode($texto, true) : null;

    if (!is_array($lista) || array_values($lista) !== $lista || count($lista) > MAXIMO_ROLES_POR_GUARDADO) {
        responderError('La lista de roles no es válida.', 400);
    }

    foreach ($lista as $codigo) {
        if (!is_string($codigo) || strlen($codigo) > CODIGO_LONGITUD_MAXIMA || !preg_match(patronClave(PATRON_CODIGO_ROL), $codigo)) {
            responderError('La lista de roles tiene un código no válido.', 400);
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
                'usuarios'     => consultarUsuarios($conn),
                'aplicaciones' => consultarAplicacionesCatalogo($conn),
            ]);

        case '2':
            $nomina = obtenerNominaAsignacion();
            $codigoSistema = obtenerClavePost('codigoSistema', PATRON_CODIGO_SISTEMA, CODIGO_LONGITUD_MAXIMA, 'Selecciona una aplicación válida.');

            $usuario = consultarUsuario($conn, $nomina);
            if ($usuario === null) {
                responderError('El usuario no existe.', 404);
            }

            $aplicacion = obtenerAplicacion($conn, $codigoSistema);
            if ($aplicacion === null) {
                responderError('La aplicación no existe.', 404);
            }

            responderOk([
                'usuario'    => $usuario,
                'aplicacion' => $aplicacion,
                'roles'      => consultarRolesDeAplicacionParaUsuario($conn, $codigoSistema, $nomina),
                'resumen'    => consultarRolesDeUsuario($conn, $nomina),
            ]);

        case '3':
            $nomina = obtenerNominaAsignacion();
            $codigoSistema = obtenerClavePost('codigoSistema', PATRON_CODIGO_SISTEMA, CODIGO_LONGITUD_MAXIMA, 'Selecciona una aplicación válida.');
            $elegidos = obtenerListaRoles();

            $conn->beginTransaction();

            $usuario = consultarUsuario($conn, $nomina, true);
            if ($usuario === null) {
                $conn->rollBack();
                responderError('El usuario no existe.', 404);
            }

            if (obtenerAplicacion($conn, $codigoSistema) === null) {
                $conn->rollBack();
                responderError('La aplicación no existe.', 404);
            }

            /* Validar todo antes de escribir. */
            $mapa = mapaRolesDeAplicacion($conn, $codigoSistema);
            $idsElegidos = [];

            foreach ($elegidos as $codigoRol) {
                if (!isset($mapa[$codigoRol])) {
                    $conn->rollBack();
                    responderError('El rol ' . $codigoRol . ' no pertenece a la aplicación ' . $codigoSistema . '.', 400);
                }

                $idsElegidos[$mapa[$codigoRol]['idRol']] = $codigoRol;
            }

            $actuales = rolesActualesDeUsuarioEnAplicacion($conn, $nomina, $codigoSistema);
            $agregar = array_values(array_diff(array_keys($idsElegidos), $actuales));
            $quitar = array_values(array_diff($actuales, array_keys($idsElegidos)));

            if (count($agregar) > 0 && $usuario['estatus'] !== 'activo') {
                $conn->rollBack();
                responderError('No se pueden asignar roles a un usuario inactivo. Actívalo primero o solo quita roles.', 400);
            }

            foreach ($agregar as $idRol) {
                if (!$mapa[$idsElegidos[$idRol]]['activo']) {
                    $conn->rollBack();
                    responderError('El rol ' . $idsElegidos[$idRol] . ' está inactivo y no se puede asignar.', 400);
                }
            }

            foreach ($quitar as $idRol) {
                quitarRolAUsuario($conn, $nomina, $idRol);
            }

            foreach ($agregar as $idRol) {
                asignarRolAUsuario($conn, $nomina, $idRol);
            }

            $conn->commit();

            responderOk(
                ['agregados' => count($agregar), 'quitados' => count($quitar)],
                count($agregar) === 0 && count($quitar) === 0
                    ? 'No había cambios que guardar.'
                    : 'Roles de ' . $nomina . ' en ' . $codigoSistema . ' guardados: ' . count($agregar) . ' asignado(s), '
                        . count($quitar) . ' quitado(s). El usuario verá el cambio al volver a iniciar sesión.'
            );

        default:
            responderError('La opción solicitada no existe.', 400);
    }
} catch (PDOException $error) {
    if ($conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }

    if (esViolacionUnica($error)) {
        responderError('El usuario ya tenía uno de esos roles. Recarga la pantalla e intenta de nuevo.', 409);
    }

    responderFalloInterno($error, $conn, 'Roles de usuario, opción ' . opcionParaLog($opcion));
} catch (Throwable $error) {
    responderFalloInterno($error, $conn, 'Roles de usuario, opción ' . opcionParaLog($opcion));
}
