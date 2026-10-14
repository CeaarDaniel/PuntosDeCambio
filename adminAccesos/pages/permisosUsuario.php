<?php
/**
 * pages/permisosUsuario.php — Excepciones individuales de un usuario
 * (conceder o denegar un permiso puntual, con motivo y vigencia).
 *
 * Solo el marcado. Datos: api/operacionesPermisosUsuario.php. Lógica:
 * assets/js/permisosUsuario.js. Acepta ?nomina=XXX para llegar con el usuario
 * ya elegido.
 */
if (!defined('EN_APLICACION')) {
    http_response_code(404);
    exit;
}
?>
<div class="page-header">
    <div>
        <h1>Permisos individuales</h1>
        <p class="page-subtitle">Excepciones para una sola persona: dar o quitar un permiso puntual sin cambiar su rol.</p>
    </div>
    <div class="page-header-actions">
        <button class="btn btn-primary" type="button" id="btnNuevaExcepcion" disabled>
            <i class="bi bi-plus-lg" aria-hidden="true"></i>Nueva excepción
        </button>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-4">
        <?php
        $selectorTitulo = '1. Elige el usuario';
        require __DIR__ . '/../includes/selectorUsuario.php';
        ?>

        <section class="card mt-4" aria-labelledby="ayudaExcepcionesTitulo">
            <div class="card-header" id="ayudaExcepcionesTitulo">
                <i class="bi bi-lightbulb me-1" aria-hidden="true"></i>¿Cómo funcionan?
            </div>
            <div class="card-body excepciones-ayuda">
                <p><span class="badge badge-conceder"><i class="bi bi-check-circle" aria-hidden="true"></i>Conceder</span>
                    le da el permiso aunque su rol no lo incluya.</p>
                <p><span class="badge badge-denegar"><i class="bi bi-slash-circle" aria-hidden="true"></i>Denegar</span>
                    se lo quita aunque su rol lo incluya. <strong>Siempre gana.</strong></p>
                <p>Con fecha de vigencia, la excepción deja de aplicar sola al día siguiente. Úsala para
                    coberturas, suplencias o capacitaciones.</p>
                <p class="mb-0">Si haces la misma excepción para varias personas, probablemente lo que hace
                    falta es un rol.</p>
            </div>
        </section>
    </div>

    <div class="col-12 col-lg-8">
        <section class="card paso-bloqueado tabla-contenida" id="panelExcepciones" aria-labelledby="panelExcepcionesTitulo">
            <div class="card-header" id="panelExcepcionesTitulo">
                <i class="bi bi-person-lock me-1" aria-hidden="true"></i>2. Sus excepciones
            </div>
            <div class="card-body">
                <div class="alert alert-warning d-none" id="avisoExcepcionesInactivo" role="alert">
                    <i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>
                    Este usuario está <strong>inactivo</strong>: no se le pueden agregar ni modificar excepciones,
                    solo retirarlas.
                </div>

                <div class="filtro-pildoras excepciones-filtro d-none" id="filtroVigencia" role="group" aria-label="Filtrar por vigencia">
                    <button type="button" class="filtro-pildora active" data-vigencia="" aria-pressed="true">
                        Todas <span class="filtro-pildora-total" data-total="todas">0</span>
                    </button>
                    <button type="button" class="filtro-pildora" data-vigencia="vigente" aria-pressed="false">
                        <i class="bi bi-calendar-check" aria-hidden="true"></i>Vigentes
                        <span class="filtro-pildora-total" data-total="vigente">0</span>
                    </button>
                    <button type="button" class="filtro-pildora" data-vigencia="permanente" aria-pressed="false">
                        <i class="bi bi-infinity" aria-hidden="true"></i>Permanentes
                        <span class="filtro-pildora-total" data-total="permanente">0</span>
                    </button>
                    <button type="button" class="filtro-pildora" data-vigencia="vencido" aria-pressed="false">
                        <i class="bi bi-calendar-x" aria-hidden="true"></i>Vencidas
                        <span class="filtro-pildora-total" data-total="vencido">0</span>
                    </button>
                </div>

                <div id="excepcionesEstado">
                    <div class="empty-state empty-state-compacto">
                        <i class="bi bi-person-lock" aria-hidden="true"></i>
                        <div class="empty-title">Elige un usuario</div>
                        <div>Aquí verás sus excepciones vigentes, permanentes y vencidas.</div>
                    </div>
                </div>

                <table class="table table-hover align-middle w-100 d-none" id="tablaExcepciones">
                    <thead>
                        <tr>
                            <th>Permiso</th>
                            <th>Tipo</th>
                            <th>Vigencia</th>
                            <th>Motivo</th>
                            <th>Otorgado por</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<!-- ==================================================================
     Modal: nueva excepción / editar excepción
     ================================================================== -->
<div class="modal fade" id="modalExcepcion" tabindex="-1" aria-labelledby="modalExcepcionTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <form class="modal-content" id="formExcepcion" novalidate autocomplete="off">
            <div class="modal-header">
                <h2 class="modal-title" id="modalExcepcionTitulo">Nueva excepción</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="identidad-modal">
                    <span class="identidad-modal-icono" aria-hidden="true"><i class="bi bi-person"></i></span>
                    <div class="identidad-modal-texto">
                        <div class="fw-semibold" id="excepcionUsuarioNombre"></div>
                        <span class="clave" id="excepcionUsuarioNomina"></span>
                    </div>
                </div>

                <div class="alert alert-danger d-none" id="modalExcepcionMensaje" role="alert" aria-live="polite"></div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required" for="excepcionAplicacion">Aplicación</label>
                        <select class="form-select" id="excepcionAplicacion" required></select>
                        <div class="invalid-feedback">Selecciona la aplicación.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required" for="excepcionModulo">Módulo</label>
                        <select class="form-select" id="excepcionModulo" required disabled>
                            <option value="">Primero la aplicación</option>
                        </select>
                        <div class="invalid-feedback">Selecciona el módulo.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required" for="excepcionAccion">Permiso</label>
                        <select class="form-select" id="excepcionAccion" required disabled>
                            <option value="">Primero el módulo</option>
                        </select>
                        <div class="invalid-feedback">Selecciona el permiso.</div>
                    </div>

                    <div class="col-12">
                        <div class="clave-completa">
                            <span>Permiso:</span>
                            <span class="clave" id="excepcionClavePrevia">—</span>
                            <span class="w-100 small" id="excepcionPista"></span>
                        </div>
                    </div>

                    <div class="col-12">
                        <span class="form-label required d-block">¿Qué quieres hacer?</span>
                        <div class="excepcion-tipos" role="radiogroup" aria-label="Tipo de excepción">
                            <label class="excepcion-tipo excepcion-tipo-conceder" for="excepcionTipoConceder">
                                <input class="form-check-input" type="radio" name="excepcionTipo" id="excepcionTipoConceder" value="conceder" required>
                                <span>
                                    <span class="fw-semibold"><i class="bi bi-check-circle" aria-hidden="true"></i> Conceder</span>
                                    <span class="d-block celda-secundaria">Le da el permiso aunque su rol no lo incluya.</span>
                                </span>
                            </label>
                            <label class="excepcion-tipo excepcion-tipo-denegar" for="excepcionTipoDenegar">
                                <input class="form-check-input" type="radio" name="excepcionTipo" id="excepcionTipoDenegar" value="denegar" required>
                                <span>
                                    <span class="fw-semibold"><i class="bi bi-slash-circle" aria-hidden="true"></i> Denegar</span>
                                    <span class="d-block celda-secundaria">Se lo quita aunque su rol lo incluya. Siempre gana.</span>
                                </span>
                            </label>
                        </div>
                        <div class="invalid-feedback d-block d-none" id="excepcionTipoError">Elige conceder o denegar.</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label required" for="excepcionMotivo">Motivo</label>
                        <textarea class="form-control" id="excepcionMotivo" rows="2" maxlength="250" required
                                  placeholder="Ej. Cubre la supervisión de calidad durante las vacaciones de Juan Pérez."></textarea>
                        <div class="form-text text-end"><span id="excepcionMotivoContador">0</span>/250</div>
                        <div class="invalid-feedback">Escribe el motivo: ayuda a saber después por qué existe.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="excepcionVigencia">Vigente hasta (inclusive)</label>
                        <input class="form-control" type="date" id="excepcionVigencia" required>
                        <div class="invalid-feedback">Elige una fecha de hoy en adelante, o marca "sin vencimiento".</div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="excepcionPermanente">
                            <label class="form-check-label" for="excepcionPermanente">
                                Sin vencimiento (permanente)
                                <span class="d-block form-text mt-0">Úsalo solo si de verdad no es temporal.</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnGuardarExcepcion">
                    <i class="bi bi-check2" aria-hidden="true"></i>Guardar
                </button>
            </div>
        </form>
    </div>
</div>
