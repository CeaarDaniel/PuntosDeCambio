<?php
/**
 * includes/toast.php — Contenedor de las notificaciones emergentes (toasts).
 *
 * Componente reutilizable y autónomo: solo aporta el hueco donde
 * assets/js/app.js va insertando los avisos. Se incluye una vez por página,
 * dentro del <body>, y no necesita ninguna variable.
 *
 * Los mensajes se lanzan desde cualquier script con App.exito(), App.error(),
 * App.aviso() o App.info().
 */
?>
<div class="toast-container app-toasts position-fixed top-0 end-0 p-3"
     id="contenedorToasts" aria-live="polite" aria-atomic="true"></div>
