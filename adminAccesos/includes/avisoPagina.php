<?php
/**
 * includes/avisoPagina.php — Aviso a página completa (estado vacío).
 *
 * Lo usa index.php para las pantallas que todavía no existen o que no se
 * encontraron. Variables que se definen antes del include:
 *   $avisoIcono   Clase de Bootstrap Icons, por ejemplo 'bi-cone-striped'.
 *   $avisoTitulo  Título del aviso.
 *   $avisoTexto   Explicación breve.
 */
$avisoIcono = $avisoIcono ?? 'bi-info-circle';
$avisoTitulo = $avisoTitulo ?? '';
$avisoTexto = $avisoTexto ?? '';
?>
<div class="card">
    <div class="empty-state">
        <i class="bi <?php echo htmlspecialchars($avisoIcono, ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
        <div class="empty-title"><?php echo htmlspecialchars($avisoTitulo, ENT_QUOTES, 'UTF-8'); ?></div>
        <p class="mb-4"><?php echo htmlspecialchars($avisoTexto, ENT_QUOTES, 'UTF-8'); ?></p>
        <a class="btn btn-outline-primary" href="index.php">
            <i class="bi bi-house-door" aria-hidden="true"></i>Volver al inicio
        </a>
    </div>
</div>
