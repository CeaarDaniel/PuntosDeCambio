<?php
/**
 * includes/header.php — Encabezado superior de la aplicación.
 *
 * Componente reutilizable; no necesita variables. El nombre sale de
 * APP_NOMBRE (includes/configuracion.php).
 */
require_once __DIR__ . '/configuracion.php';
?>
<header class="app-header">
    <div class="app-header-inner">
        <a class="app-brand" href="index.php">
            <i class="bi bi-shield-lock" aria-hidden="true"></i>
            <span><?php echo htmlspecialchars(APP_NOMBRE, ENT_QUOTES, 'UTF-8'); ?></span>
        </a>
    </div>
</header>
