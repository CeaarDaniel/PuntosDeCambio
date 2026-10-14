<?php
/**
 * includes/sesion.php — Sesión, token CSRF y utilidades de identidad.
 *
 * Se usa un nombre de cookie propio para no mezclar esta sesión con las de
 * las otras aplicaciones alojadas en el mismo servidor.
 *
 * Contrato de la sesión (autenticacion-y-permisos.md §6.4):
 *   $_SESSION['usuario']  = ['nomina' => '…', 'nombre' => '…', 'inicio' => 'Y-m-d H:i:s']
 *   $_SESSION['permisos'] = [];   // se llenará al definir los permisos
 *   $_SESSION['roles']    = [];   // ídem
 *   $_SESSION['csrf']     = '…';  // token de las operaciones que modifican datos
 *
 * PENDIENTE (PENDIENTES.md §3): todavía no hay validación de permisos. Por
 * ahora las vistas y los endpoints solo exigen una sesión válida.
 */

require_once __DIR__ . '/configuracion.php';

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('ADMACCSESSID');

    $conexionSegura = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'httponly' => true,
        'secure'   => $conexionSegura,
        'samesite' => 'Lax'
    ]);

    session_start();
}

/**
 * Devuelve los datos del usuario autenticado o null cuando no existe sesión.
 */
function usuarioEnSesion(): ?array
{
    return isset($_SESSION['usuario']) && is_array($_SESSION['usuario'])
        ? $_SESSION['usuario']
        : null;
}

function haySesion(): bool
{
    return usuarioEnSesion() !== null;
}

/**
 * Nómina de quien opera. Es la que se graba en la base (otorgadoPor, etc.);
 * nunca se toma del formulario, porque lo que manda el navegador se puede falsificar.
 */
function nominaEnSesion(): string
{
    return (string) (usuarioEnSesion()['nomina'] ?? '');
}

function nombreEnSesion(): string
{
    return (string) (usuarioEnSesion()['nombre'] ?? '');
}

/**
 * Devuelve el token CSRF de la sesión, creándolo si todavía no existe.
 */
function tokenCsrf(): string
{
    if (empty($_SESSION['csrf']) || !is_string($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

/**
 * Genera un token nuevo. Se llama después del login para que el token usado
 * en la pantalla de acceso no siga valiendo dentro de la sesión autenticada.
 */
function renovarTokenCsrf(): void
{
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/**
 * Compara en tiempo constante el token recibido con el de la sesión.
 */
function csrfEsValido(?string $tokenRecibido): bool
{
    return is_string($tokenRecibido)
        && $tokenRecibido !== ''
        && !empty($_SESSION['csrf'])
        && hash_equals((string) $_SESSION['csrf'], $tokenRecibido);
}

/**
 * Guarda de las vistas: sin sesión, manda a la pantalla de acceso.
 */
function exigirSesionVista(): void
{
    if (haySesion()) {
        return;
    }

    header('Location: login.php');
    exit;
}

/**
 * Elimina la información del usuario, la cookie y la sesión del servidor.
 */
function cerrarSesion(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $parametros = session_get_cookie_params();

        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $parametros['path'],
            'domain'   => $parametros['domain'],
            'secure'   => $parametros['secure'],
            'httponly' => $parametros['httponly'],
            'samesite' => $parametros['samesite'] ?? 'Lax'
        ]);
    }

    session_destroy();
}

/**
 * Escapa un valor para imprimirlo en HTML, incluidos los atributos.
 */
function escapar($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
