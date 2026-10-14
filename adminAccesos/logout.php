<?php
/**
 * logout.php — Cierra la sesión.
 *
 * Solo acepta POST con el token CSRF de la sesión: así otro sitio no puede
 * cerrar la sesión del usuario con un simple enlace o imagen.
 */
require_once __DIR__ . '/includes/sesion.php';

$tokenRecibido = isset($_POST['csrf']) && !is_array($_POST['csrf']) ? (string) $_POST['csrf'] : null;

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !csrfEsValido($tokenRecibido)) {
    header('Location: index.php');
    exit;
}

cerrarSesion();

header('Location: login.php');
exit;
