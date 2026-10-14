<?php
/**
 * includes/api.php — Utilidades compartidas por todos los endpoints de api/.
 *
 * Cada endpoint empieza con:
 *
 *     require_once __DIR__ . '/../includes/api.php';
 *     prepararEndpoint();            // POST + X-Requested-With + sesión + CSRF
 *     require __DIR__ . '/conexion.php';
 *
 * y despacha con un switch sobre $_POST['opcion'] (números en texto). Todas
 * las respuestas tienen la forma {status, mensaje?, data?}.
 *
 * PENDIENTE (PENDIENTES.md §3): cuando se definan los permisos, cada case
 * deberá empezar con exigirPermiso('modulo', 'accion').
 */

require_once __DIR__ . '/sesion.php';

header('Content-Type: application/json; charset=utf-8');
header('Allow: POST');
header('X-Content-Type-Options: nosniff');

/**
 * Envía una respuesta JSON uniforme y termina la petición.
 */
function responderJson(array $respuesta, int $codigoHttp = 200): void
{
    http_response_code($codigoHttp);
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

function responderError(string $mensaje, int $codigoHttp = 400): void
{
    responderJson(['status' => 'error', 'mensaje' => $mensaje], $codigoHttp);
}

function responderOk(array $data = [], string $mensaje = ''): void
{
    $respuesta = ['status' => 'ok', 'data' => $data];

    if ($mensaje !== '') {
        $respuesta['mensaje'] = $mensaje;
    }

    responderJson($respuesta);
}

/**
 * Comprobaciones comunes antes de atender cualquier opción:
 *  - solo POST;
 *  - solo peticiones AJAX de la propia aplicación;
 *  - sesión iniciada (401 para que el front mande a la pantalla de acceso);
 *  - token CSRF válido en la cabecera X-CSRF-Token.
 *
 * $requiereSesion = false solo lo usa el endpoint de autenticación.
 */
function prepararEndpoint(bool $requiereSesion = true): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        responderError('Método no permitido.', 405);
    }

    if (strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest') !== 0) {
        responderError('La solicitud no fue originada por la aplicación.', 403);
    }

    if ($requiereSesion && !haySesion()) {
        responderError('Tu sesión terminó. Vuelve a iniciar sesión.', 401);
    }

    if (!csrfEsValido($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
        responderError('La página expiró. Recárgala e intenta de nuevo.', 403);
    }
}

/**
 * Registra el detalle técnico en el log del servidor y responde con un
 * mensaje entendible. Revierte la transacción si quedó alguna abierta.
 */
function responderFalloInterno(Throwable $error, ?PDO $conn, string $contexto): void
{
    if ($conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log('[adminAccesos] ' . $contexto . ': ' . $error->getMessage());

    responderError('No fue posible completar la operación. Intenta de nuevo más tarde.', 500);
}

/**
 * Obtiene un texto enviado por POST sin aceptar arreglos. Recorta espacios.
 */
function obtenerTextoPost(string $campo): ?string
{
    if (!isset($_POST[$campo]) || is_array($_POST[$campo])) {
        return null;
    }

    return trim((string) $_POST[$campo]);
}

/**
 * Comprueba UTF-8, contenido y longitud máxima sin modificar el valor.
 */
function textoEsValido(?string $valor, int $longitudMaxima): bool
{
    if ($valor === null || $valor === '') {
        return false;
    }

    if (!mb_check_encoding($valor, 'UTF-8')) {
        return false;
    }

    $contieneCaracteresControl = preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $valor);

    return mb_strlen($valor, 'UTF-8') <= $longitudMaxima && $contieneCaracteresControl === 0;
}

/* ==========================================================================
   Lectores de campos ya validados. Si el valor no es válido responden 400
   con $mensaje y terminan la petición, así cada endpoint queda en una línea
   por campo.
   ========================================================================== */

/**
 * Texto obligatorio de una sola línea (nombres, títulos).
 */
function obtenerTextoObligatorio(string $campo, int $maximo, string $mensaje): string
{
    $valor = obtenerTextoPost($campo) ?? '';

    if (!textoEsValido($valor, $maximo) || preg_match('/[\r\n\t]/', $valor)) {
        responderError($mensaje, 400);
    }

    return $valor;
}

/**
 * Texto opcional (puede tener saltos de línea). Vacío se devuelve como null
 * para guardarlo como NULL.
 */
function obtenerTextoOpcional(string $campo, int $maximo, string $mensaje): ?string
{
    $valor = obtenerTextoPost($campo) ?? '';

    if ($valor === '') {
        return null;
    }

    if (!textoEsValido($valor, $maximo)) {
        responderError($mensaje, 400);
    }

    return $valor;
}

/**
 * Clave estable (codigoSistema, codigoModulo, codigoRol, accion) con uno de
 * los patrones de includes/configuracion.php.
 */
function obtenerClavePost(string $campo, string $patron, int $maximo, string $mensaje): string
{
    $valor = obtenerTextoPost($campo) ?? '';

    if ($valor === '' || strlen($valor) > $maximo || !preg_match(patronClave($patron), $valor)) {
        responderError($mensaje, 400);
    }

    return $valor;
}

/**
 * Bandera '1' / '0' (columnas bit como activo). Cualquier otro valor es inválido.
 */
function obtenerBanderaPost(string $campo, string $mensaje): int
{
    $valor = obtenerTextoPost($campo);

    if ($valor !== '1' && $valor !== '0') {
        responderError($mensaje, 400);
    }

    return (int) $valor;
}

/**
 * Entero dentro de un rango.
 */
function obtenerEnteroPost(string $campo, int $minimo, int $maximo, string $mensaje): int
{
    $valor = obtenerTextoPost($campo) ?? '';
    $entero = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => $minimo, 'max_range' => $maximo]]);

    if ($entero === false) {
        responderError($mensaje, 400);
    }

    return $entero;
}

/**
 * Violación de una restricción UNIQUE/PK de SQL Server (2601, 2627).
 */
function esViolacionUnica(PDOException $error): bool
{
    return in_array((int) ($error->errorInfo[1] ?? 0), [2601, 2627], true);
}

/**
 * Violación de una llave foránea de SQL Server (547): se intentó borrar algo
 * que todavía tiene registros relacionados.
 */
function esViolacionReferencia(PDOException $error): bool
{
    return (int) ($error->errorInfo[1] ?? 0) === 547;
}

/**
 * Número de opción saneado para escribirlo en el log.
 */
function opcionParaLog(string $opcion): string
{
    return substr(preg_replace('/\D/', '', $opcion), 0, 5);
}
