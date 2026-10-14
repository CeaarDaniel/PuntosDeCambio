<?php
/**
 * includes/footer.php — Pie de página de la aplicación.
 *
 * Componente reutilizable. El año se calcula solo para no tener que
 * actualizarlo a mano cada enero.
 */
require_once __DIR__ . '/configuracion.php';
?>
<footer class="app-footer">
    &copy; <?php echo date('Y'); ?> &middot;
    <?php echo htmlspecialchars(APP_NOMBRE, ENT_QUOTES, 'UTF-8'); ?>
</footer>
