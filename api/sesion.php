<?php
/*
 * Arranque y utilidades de la sesión.
 *
 * Lo incluyen tanto las páginas (index.php, login.php) como la API de
 * usuarios, para que el arranque de la sesión y la forma de $_SESSION queden
 * definidos en un solo lugar.
 *
 * Aquí es donde se agregarán más adelante los guards de permisos
 * (exigirAcceso / exigirPermiso) cuando se conecten las tablas de roles.
 */

if (session_status() === PHP_SESSION_NONE) {
    /*
     * httponly impide que la cookie de sesión se lea desde JavaScript.
     * 'secure' se queda apagado porque la aplicación corre sobre http en la
     * red local; si algún día se publica por https, conviene activarlo.
     */
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

/**
 * Devuelve los datos del usuario autenticado, o null si no hay sesión.
 */
function usuarioEnSesion(): ?array
{
    return isset($_SESSION['usuario']) && is_array($_SESSION['usuario'])
        ? $_SESSION['usuario']
        : null;
}

/**
 * Indica si hay una sesión iniciada.
 */
function haySesion(): bool
{
    return usuarioEnSesion() !== null;
}

/**
 * Corta la entrega de la página y manda al login cuando no hay sesión.
 * La ruta se recibe como parámetro porque cambia según la carpeta desde
 * la que se invoque (raíz o /pages).
 */
function exigirSesion(string $rutaLogin = 'login.php'): void
{
    if (!haySesion()) {
        header('Location: ' . $rutaLogin);
        exit;
    }
}

/**
 * Elimina por completo la sesión actual, incluida su cookie.
 */
function cerrarSesion(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $parametros = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $parametros['path'],
            $parametros['domain'],
            $parametros['secure'],
            $parametros['httponly']
        );
    }

    session_destroy();
}

/* =====================================================================
   Permisos

   $_SESSION['permisos'] la llena el inicio de sesión, en el case '1' de
   api/operacionesUsuarios.php. Es un arreglo plano de textos con el
   formato 'modulo.accion', por ejemplo 'anormalidades.analisis_qa'.

   Estas tres funciones solo consultan ese arreglo: ninguna toca la base
   de datos.
   ===================================================================== */

/**
 * Consulta un permiso sin interrumpir la ejecución.
 * Sirve para decidir si se dibuja un botón o una opción del menú.
 */
function tienePermiso(string $modulo, string $accion): bool
{
    return in_array($modulo . '.' . $accion, $_SESSION['permisos'] ?? [], true);
}

/**
 * Nómina del usuario autenticado.
 *
 * Todo lo que quede escrito en la base a nombre de alguien debe salir de aquí
 * y nunca del formulario: lo que manda el navegador es falsificable.
 */
function nominaEnSesion(): string
{
    $usuario = usuarioEnSesion();

    return $usuario === null ? '' : trim((string) $usuario['nomina']);
}

/**
 * Nombre del usuario autenticado, para acompañar a la nómina en los registros.
 */
function nombreEnSesion(): string
{
    $usuario = usuarioEnSesion();

    return $usuario === null ? '' : trim((string) $usuario['nombre']);
}

/**
 * Indica si el usuario tiene asignado un rol concreto.
 *
 * Los permisos responden "qué puede hacer"; el rol responde "quién es", que es
 * lo que hace falta cuando una validación depende del puesto y no de la acción.
 */
function tieneRol(string $codigoRol): bool
{
    return in_array($codigoRol, $_SESSION['roles'] ?? [], true);
}

/**
 * Guarda de las vistas: corta la entrega del módulo cuando el usuario no
 * tiene su permiso de consulta.
 *
 * Responde HTML y no JSON porque scripts/main.js inyecta el resultado
 * directamente en #content-area; un JSON se vería como texto crudo.
 *
 * Al leer una sesión vacía también corta, así que una petición sin sesión
 * queda bloqueada por el mismo camino.
 */
function exigirAcceso(string $modulo): void
{
    if (tienePermiso($modulo, 'ver')) {
        return;
    }

    http_response_code(403);

    echo '<div class="lines-page">'
       .   '<div class="card border-0 shadow-sm text-center p-5 mx-auto" style="max-width: 520px;">'
       .     '<div class="mb-3">'
       .       '<i class="bi bi-shield-lock" style="font-size: 2.5rem; color: var(--primary-light);"></i>'
       .     '</div>'
       .     '<h3 class="h5 mb-2">No tienes acceso a este m&oacute;dulo</h3>'
       .     '<p class="text-secondary mb-0">Si necesitas entrar, solic&iacute;talo a tu supervisor.</p>'
       .   '</div>'
       . '</div>';

    exit;
}

/**
 * Guarda de la API: corta la operación cuando falta el permiso de la acción.
 * Responde con el mismo formato JSON que el resto de los endpoints, para que
 * el front lo maneje igual que cualquier otro error.
 */
function exigirPermiso(string $modulo, string $accion): void
{
    if (tienePermiso($modulo, $accion)) {
        return;
    }

    http_response_code(403);

    echo json_encode([
        'status'  => 'error',
        'mensaje' => 'No tienes permiso para realizar esta acción.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}
