<?php
/**
 * api/operacionesUsuarios.php — Pantalla de usuarios (pages/usuarios.php).
 *
 * Opciones:
 *   '1'  Listar usuarios.
 *   '2'  Detalle: datos, roles, excepciones y permisos efectivos.
 *   '3'  Crear usuario.
 *   '4'  Modificar nombre y comentarios.
 *   '5'  Activar o desactivar.
 *   '6'  Restablecer o cambiar la contraseña.
 *
 * Reglas (contexto.md, módulo 2):
 *  - nominaUsuario es texto, única, y no se modifica después del alta.
 *  - passwordHash nunca se devuelve; las contraseñas nunca se escriben en
 *    logs ni en respuestas, y se guardan con password_hash(PASSWORD_DEFAULT).
 *  - No se puede desactivar al usuario de la sesión actual.
 *  - No hay eliminación de usuarios: se desactivan.
 *
 * PENDIENTE (PENDIENTES.md §3):
 *  - exigirPermiso('usuarios', …) en cada opción.
 *  - Evitar desactivar al último administrador activo (requiere el rol admin_accesos).
 * PENDIENTE (PENDIENTES.md §1): registrar cada cambio en la auditoría.
 */

require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/consultas/usuarios.php';
require_once __DIR__ . '/consultas/permisosEfectivos.php';

prepararEndpoint();

/* Nómina: letras, dígitos, punto, guion y guion bajo, empezando por letra o
   dígito. Cubre las existentes ('11607', 'T10', 'reportes'). */
const PATRON_NOMINA = '/^[A-Za-z0-9][A-Za-z0-9._-]*$/';
const NOMBRE_LONGITUD_MAXIMA = 100;
const COMENTARIOS_LONGITUD_MAXIMA = 1000;
const ESTATUS_VALIDOS = ['activo', 'inactivo'];

/**
 * Lee y valida la nómina enviada. Responde 400 si no es válida.
 */
function obtenerNominaValida(): string
{
    $nomina = obtenerTextoPost('nomina') ?? '';

    if (!textoEsValido($nomina, NOMINA_LONGITUD_MAXIMA) || !preg_match(PATRON_NOMINA, $nomina)) {
        responderError('La nómina solo puede tener letras, números, punto, guion y guion bajo, sin espacios (máximo ' . NOMINA_LONGITUD_MAXIMA . ' caracteres).', 400);
    }

    return $nomina;
}

function obtenerNombreValido(): string
{
    $nombre = obtenerTextoPost('nombre') ?? '';

    if (!textoEsValido($nombre, NOMBRE_LONGITUD_MAXIMA) || preg_match('/[\r\n\t]/', $nombre)) {
        responderError('Escribe el nombre (máximo ' . NOMBRE_LONGITUD_MAXIMA . ' caracteres, en una sola línea).', 400);
    }

    return $nombre;
}

/**
 * Comentarios opcionales: vacío se guarda como NULL.
 */
function obtenerComentariosValidos(): ?string
{
    $comentarios = obtenerTextoPost('comentarios') ?? '';

    if ($comentarios === '') {
        return null;
    }

    if (!textoEsValido($comentarios, COMENTARIOS_LONGITUD_MAXIMA)) {
        responderError('Los comentarios no pueden pasar de ' . COMENTARIOS_LONGITUD_MAXIMA . ' caracteres.', 400);
    }

    return $comentarios;
}

/**
 * Lee la contraseña nueva y su confirmación, las valida y devuelve el hash.
 * La contraseña no se recorta (los espacios pueden ser parte de ella) y
 * nunca se incluye en mensajes ni logs.
 */
function obtenerHashDeContrasenaNueva(): string
{
    $contrasena = isset($_POST['contrasena']) && !is_array($_POST['contrasena']) ? (string) $_POST['contrasena'] : '';
    $confirmacion = isset($_POST['confirmacion']) && !is_array($_POST['confirmacion']) ? (string) $_POST['confirmacion'] : '';

    if (!mb_check_encoding($contrasena, 'UTF-8') || preg_match('/[\x00-\x1F\x7F]/', $contrasena)) {
        responderError('La contraseña contiene caracteres no permitidos.', 400);
    }

    if (mb_strlen($contrasena, 'UTF-8') < PASSWORD_LONGITUD_MINIMA) {
        responderError('La contraseña debe tener al menos ' . PASSWORD_LONGITUD_MINIMA . ' caracteres.', 400);
    }

    /* bcrypt (PASSWORD_DEFAULT) solo toma en cuenta los primeros 72 bytes. */
    if (strlen($contrasena) > PASSWORD_LONGITUD_MAXIMA) {
        responderError('La contraseña es demasiado larga (máximo ' . PASSWORD_LONGITUD_MAXIMA . ' caracteres sin acentos).', 400);
    }

    if (!hash_equals($contrasena, $confirmacion)) {
        responderError('La contraseña y su confirmación no coinciden.', 400);
    }

    return password_hash($contrasena, PASSWORD_DEFAULT);
}

$opcion = obtenerTextoPost('opcion') ?? '';
$conn = null;

try {
    require __DIR__ . '/conexion.php';

    switch ($opcion) {
        /* ---------------------------------------------------------------
           1. Listar
           --------------------------------------------------------------- */
        case '1':
            responderOk([
                'usuarios'     => consultarUsuarios($conn),
                'nominaSesion' => nominaEnSesion(),
            ]);

        /* ---------------------------------------------------------------
           2. Detalle
           --------------------------------------------------------------- */
        case '2':
            $nomina = obtenerNominaValida();
            $usuario = consultarUsuario($conn, $nomina);

            if ($usuario === null) {
                responderError('El usuario no existe.', 404);
            }

            responderOk([
                'usuario'     => $usuario,
                'roles'       => consultarRolesDeUsuario($conn, $nomina),
                'excepciones' => consultarExcepcionesDeUsuario($conn, $nomina),
                'efectivos'   => consultarPermisosEfectivos($conn, $nomina),
            ]);

        /* ---------------------------------------------------------------
           3. Crear
           --------------------------------------------------------------- */
        case '3':
            $nomina = obtenerNominaValida();
            $nombre = obtenerNombreValido();
            $comentarios = obtenerComentariosValidos();
            $estatus = obtenerTextoPost('estatus') ?? '';

            if (!in_array($estatus, ESTATUS_VALIDOS, true)) {
                responderError('Selecciona un estatus válido.', 400);
            }

            /* El hash se calcula antes de abrir la transacción para no
               mantener el bloqueo mientras bcrypt trabaja. */
            $passwordHash = obtenerHashDeContrasenaNueva();

            $conn->beginTransaction();

            if (existeNomina($conn, $nomina)) {
                $conn->rollBack();
                responderError('Ya existe un usuario con la nómina ' . $nomina . '.', 409);
            }

            insertarUsuario($conn, $nomina, $nombre, $passwordHash, $estatus, $comentarios);
            $conn->commit();

            responderOk(['nomina' => $nomina], 'Usuario ' . $nomina . ' creado correctamente.');

        /* ---------------------------------------------------------------
           4. Modificar nombre y comentarios
           --------------------------------------------------------------- */
        case '4':
            $nomina = obtenerNominaValida();
            $nombre = obtenerNombreValido();
            $comentarios = obtenerComentariosValidos();

            $conn->beginTransaction();

            if (consultarUsuario($conn, $nomina, true) === null) {
                $conn->rollBack();
                responderError('El usuario no existe.', 404);
            }

            actualizarDatosUsuario($conn, $nomina, $nombre, $comentarios);
            $conn->commit();

            /* Si se editó a sí mismo, el nombre del menú se actualiza ya. */
            if ($nomina === nominaEnSesion()) {
                $_SESSION['usuario']['nombre'] = $nombre;
            }

            responderOk(['nomina' => $nomina], 'Datos de ' . $nombre . ' actualizados.');

        /* ---------------------------------------------------------------
           5. Activar o desactivar
           --------------------------------------------------------------- */
        case '5':
            $nomina = obtenerNominaValida();
            $estatus = obtenerTextoPost('estatus') ?? '';

            if (!in_array($estatus, ESTATUS_VALIDOS, true)) {
                responderError('El estatus solicitado no es válido.', 400);
            }

            /* Quien está operando no puede dejarse fuera a sí mismo. */
            if ($estatus === 'inactivo' && $nomina === nominaEnSesion()) {
                responderError('No puedes desactivar tu propio usuario mientras tienes la sesión iniciada.', 400);
            }

            $conn->beginTransaction();
            $usuario = consultarUsuario($conn, $nomina, true);

            if ($usuario === null) {
                $conn->rollBack();
                responderError('El usuario no existe.', 404);
            }

            if ($usuario['estatus'] === $estatus) {
                $conn->rollBack();
                responderOk(['nomina' => $nomina, 'estatus' => $estatus], 'El usuario ya estaba ' . $estatus . '.');
            }

            actualizarEstatusUsuario($conn, $nomina, $estatus);
            $conn->commit();

            responderOk(
                ['nomina' => $nomina, 'estatus' => $estatus],
                $estatus === 'activo'
                    ? 'Usuario ' . $nomina . ' activado. Ya puede iniciar sesión.'
                    : 'Usuario ' . $nomina . ' desactivado. Ya no podrá iniciar sesión.'
            );

        /* ---------------------------------------------------------------
           6. Restablecer o cambiar la contraseña
           --------------------------------------------------------------- */
        case '6':
            $nomina = obtenerNominaValida();
            $passwordHash = obtenerHashDeContrasenaNueva();

            $conn->beginTransaction();

            if (consultarUsuario($conn, $nomina, true) === null) {
                $conn->rollBack();
                responderError('El usuario no existe.', 404);
            }

            actualizarPasswordUsuario($conn, $nomina, $passwordHash);
            $conn->commit();

            responderOk(['nomina' => $nomina], 'Contraseña de ' . $nomina . ' actualizada. La anterior ya no funciona.');

        default:
            responderError('La opción solicitada no existe.', 400);
    }
} catch (PDOException $error) {
    /* Dos altas simultáneas de la misma nómina: la segunda choca con UNIQUE. */
    if (esViolacionUnica($error)) {
        if ($conn instanceof PDO && $conn->inTransaction()) {
            $conn->rollBack();
        }
        responderError('Ya existe un usuario con esa nómina.', 409);
    }

    responderFalloInterno($error, $conn, 'Usuarios, opción ' . substr(preg_replace('/\D/', '', $opcion), 0, 5));
} catch (Throwable $error) {
    responderFalloInterno($error, $conn, 'Usuarios, opción ' . substr(preg_replace('/\D/', '', $opcion), 0, 5));
}
