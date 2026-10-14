<?php
/**
 * api/autenticacion.php — Inicio de sesión contra la tabla compartida usuarios.
 *
 * Sigue el orden de autenticacion-y-permisos.md §6.2:
 *  1. validar que lleguen nómina y contraseña;
 *  2. buscar la nómina;
 *  3. password_verify() — usuario inexistente y contraseña incorrecta reciben
 *     el MISMO mensaje, para no revelar qué nóminas existen;
 *  4. verificar estatus 'activo';
 *  5. (PENDIENTE) resolver los permisos de ADM_ACCESOS y rechazar si no tiene;
 *  6. session_regenerate_id(true) y guardar la sesión.
 *
 * PENDIENTE (PENDIENTES.md §3): mientras no se definan los permisos de la
 * aplicación, cualquier usuario activo con contraseña válida puede entrar.
 */

require_once __DIR__ . '/../includes/api.php';

/* No exige sesión (todavía no existe), pero sí CSRF: el token se emite en
   login.php y protege contra un inicio de sesión forzado desde otro sitio. */
prepararEndpoint(false);

$opcion = obtenerTextoPost('opcion') ?? '';

if ($opcion !== '1') {
    responderError('La operación solicitada no es válida.', 400);
}

$nomina = obtenerTextoPost('nomina') ?? '';

/* La contraseña no se recorta: los espacios pueden formar parte de ella. */
$contrasena = isset($_POST['contrasena']) && !is_array($_POST['contrasena'])
    ? (string) $_POST['contrasena']
    : '';

if (!textoEsValido($nomina, NOMINA_LONGITUD_MAXIMA) || $contrasena === '') {
    responderError('Escribe tu nómina y tu contraseña.', 400);
}

/* Una contraseña absurdamente larga no puede ser válida; se corta antes de
   hacer trabajo en el servidor, con el mismo mensaje genérico. */
if (strlen($contrasena) > 4096) {
    responderError('Nómina o contraseña incorrectos.', 401);
}

$conn = null;

try {
    require __DIR__ . '/conexion.php';

    $consulta = $conn->prepare(
        'SELECT nominaUsuario, nombre, passwordHash, estatus
           FROM usuarios
          WHERE nominaUsuario = :nomina'
    );
    $consulta->execute([':nomina' => $nomina]);
    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    if (!$usuario || !password_verify($contrasena, (string) $usuario['passwordHash'])) {
        responderError('Nómina o contraseña incorrectos.', 401);
    }

    if (strcasecmp(trim((string) $usuario['estatus']), 'activo') !== 0) {
        responderError('Esta cuenta no está activa. Solicita ayuda al administrador.', 403);
    }

    /* PENDIENTE (PENDIENTES.md §3): aquí se resolverán los permisos de
       CODIGO_SISTEMA y se rechazará al usuario que no tenga acceso. */

    /* Cambiar el identificador evita conservar una sesión fijada previamente. */
    session_regenerate_id(true);

    $_SESSION['usuario'] = [
        'nomina' => (string) $usuario['nominaUsuario'],
        'nombre' => trim((string) $usuario['nombre']),
        'inicio' => date('Y-m-d H:i:s')
    ];
    $_SESSION['permisos'] = [];
    $_SESSION['roles'] = [];

    renovarTokenCsrf();

    responderOk(['redirigir' => 'index.php'], 'Sesión iniciada.');
} catch (Throwable $error) {
    responderFalloInterno($error, $conn, 'Inicio de sesión');
}
