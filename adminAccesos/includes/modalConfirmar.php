<?php
/**
 * includes/modalConfirmar.php — Cuadro de confirmación genérico.
 *
 * Componente reutilizable. Se incluye una vez por página (index.php) y se abre
 * desde JavaScript con App.confirmar(), que rellena los textos:
 *
 *   App.confirmar({
 *       titulo: 'Desactivar usuario',
 *       mensaje: '¿Desactivar a Juan Pérez?',
 *       detalle: 'Ya no podrá iniciar sesión en ninguna aplicación.',
 *       textoConfirmar: 'Sí, desactivar',
 *       peligro: true
 *   }).done(function () { … });   // solo si el usuario confirma
 *
 * No debe abrirse encima de otro modal: Bootstrap no apila modales. Dentro de
 * un modal, la confirmación se resuelve como un paso del propio modal.
 */
?>
<div class="modal fade" id="modalConfirmar" tabindex="-1" aria-labelledby="modalConfirmarTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalConfirmarTitulo">Confirmar</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="confirmar-cuerpo">
                    <span class="confirmar-icono" id="modalConfirmarIcono" aria-hidden="true">
                        <i class="bi bi-question-circle"></i>
                    </span>
                    <div>
                        <p class="confirmar-mensaje" id="modalConfirmarMensaje"></p>
                        <p class="confirmar-detalle" id="modalConfirmarDetalle"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="modalConfirmarAceptar">Confirmar</button>
            </div>
        </div>
    </div>
</div>
