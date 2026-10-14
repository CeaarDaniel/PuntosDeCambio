<?php
/**
 * includes/selectorUsuario.php — Buscador para elegir un usuario.
 *
 * Componente reutilizable (pantallas de asignaciones). Solo el marcado: la
 * lógica es App.selectorUsuario() en assets/js/app.js y los estilos están en
 * css/style.css (sección 10d).
 *
 * Variable opcional que se define antes del include:
 *   $selectorTitulo  Texto del encabezado de la tarjeta.
 *
 * Se incluye una sola vez por pantalla (usa ids fijos).
 */
$selectorTitulo = $selectorTitulo ?? 'Elige el usuario';
?>
<section class="card selector-usuario" aria-labelledby="selectorUsuarioTitulo">
    <div class="card-header" id="selectorUsuarioTitulo">
        <i class="bi bi-person-lines-fill me-1" aria-hidden="true"></i><?php echo htmlspecialchars($selectorTitulo, ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <div class="card-body">
        <div id="selectorUsuarioBusqueda">
            <label class="form-label" for="selectorUsuarioBuscar">Busca por nómina o por nombre</label>
            <div class="input-group">
                <span class="input-group-text" aria-hidden="true"><i class="bi bi-search"></i></span>
                <input class="form-control" type="search" id="selectorUsuarioBuscar"
                       placeholder="Cargando usuarios…" autocomplete="off" spellcheck="false" disabled>
            </div>
            <div class="selector-usuario-resultados" id="selectorUsuarioResultados" aria-live="polite"></div>
        </div>

        <div class="selector-usuario-elegido d-none" id="selectorUsuarioElegido">
            <span class="identidad-modal-icono" aria-hidden="true"><i class="bi bi-person"></i></span>
            <div class="identidad-modal-texto">
                <div class="fw-semibold" id="selectorUsuarioNombre"></div>
                <span class="clave" id="selectorUsuarioNomina"></span>
                <span id="selectorUsuarioEstatus"></span>
            </div>
            <button class="btn btn-soft btn-sm" type="button" id="selectorUsuarioCambiar">
                <i class="bi bi-arrow-left-right" aria-hidden="true"></i>Cambiar
            </button>
        </div>
    </div>
</section>
