<?php
/**
 * pages/permisos.php — Catálogo de permisos (acciones de cada módulo).
 *
 * Solo el marcado. Datos: api/operacionesPermisos.php. Lógica:
 * assets/js/permisos.js. Acepta ?sistema=XXX&modulo=yyy para llegar ya filtrada.
 */
if (!defined('EN_APLICACION')) {
    http_response_code(404);
    exit;
}
?>
<div class="page-header">
    <div>
        <h1>Permisos</h1>
        <p class="page-subtitle">Acciones de cada módulo. La clave completa es <span class="clave">aplicación.módulo.acción</span>.</p>
    </div>
    <div class="page-header-actions">
        <button class="btn btn-primary" type="button" id="btnNuevoPermiso">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>Nuevo permiso
        </button>
    </div>
</div>

<div class="barra-filtros">
    <div class="barra-filtros-campo">
        <label class="form-label" for="filtroAplicacion">Aplicación</label>
        <select class="form-select" id="filtroAplicacion">
            <option value="">Todas las aplicaciones</option>
        </select>
    </div>
    <div class="barra-filtros-campo">
        <label class="form-label" for="filtroModulo">Módulo</label>
        <select class="form-select" id="filtroModulo" disabled>
            <option value="">Todos los módulos</option>
        </select>
    </div>
</div>

<section class="card tabla-contenida" aria-label="Listado de permisos">
    <div class="card-body">
        <div id="permisosEstado">
            <div class="app-loader">
                <div class="spinner-border" role="status"><span class="visually-hidden">Cargando…</span></div>
                <div>Cargando permisos…</div>
            </div>
        </div>

        <table class="table table-hover align-middle w-100 d-none" id="tablaPermisos">
            <thead>
                <tr>
                    <th>Permiso</th>
                    <th>Descripción</th>
                    <th>Roles</th>
                    <th title="Excepciones individuales (conceder o denegar)">Excepciones</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</section>

<!-- ==================================================================
     Modal: alta y edición
     ================================================================== -->
<div class="modal fade" id="modalPermiso" tabindex="-1" aria-labelledby="modalPermisoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content" id="formPermiso" novalidate autocomplete="off">
            <div class="modal-header">
                <h2 class="modal-title" id="modalPermisoTitulo">Nuevo permiso</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-danger d-none" id="modalPermisoMensaje" role="alert" aria-live="polite"></div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required" for="permisoAplicacion">Aplicación</label>
                        <select class="form-select" id="permisoAplicacion" required></select>
                        <div class="invalid-feedback">Selecciona la aplicación.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required" for="permisoModulo">Módulo</label>
                        <select class="form-select" id="permisoModulo" required disabled>
                            <option value="">Primero elige la aplicación</option>
                        </select>
                        <div class="invalid-feedback">Selecciona el módulo.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required" for="permisoAccion">Acción</label>
                        <input class="form-control" type="text" id="permisoAccion"
                               maxlength="<?php echo ACCION_LONGITUD_MAXIMA; ?>"
                               pattern="<?php echo escapar(PATRON_ACCION); ?>"
                               placeholder="Ej. registro_inicial" autocapitalize="off" spellcheck="false" required>
                        <div class="form-text" id="permisoAccionAyuda">
                            El paso concreto del flujo, no un verbo genérico. Minúsculas sin acentos, números y guion
                            bajo. No se puede cambiar después.
                        </div>
                        <div class="invalid-feedback">Usa minúsculas sin acentos, números y guion bajo, empezando con letra.</div>
                    </div>

                    <div class="col-md-6 d-flex align-items-end">
                        <div class="clave-completa w-100">
                            <span>Clave completa:</span>
                            <span class="clave" id="permisoClavePrevia">—</span>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="permisoDescripcion">Descripción</label>
                        <textarea class="form-control" id="permisoDescripcion" rows="2" maxlength="250"
                                  placeholder="Qué permite hacer, en palabras del usuario. Ej. Registrar la detección de la anormalidad."></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnGuardarPermiso">
                    <i class="bi bi-check2" aria-hidden="true"></i>Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================================================================
     Modal: quién tiene el permiso
     ================================================================== -->
<div class="modal fade" id="modalRelacionesPermiso" tabindex="-1" aria-labelledby="modalRelacionesTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalRelacionesTitulo">¿Quién tiene este permiso?</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="identidad-modal">
                    <span class="identidad-modal-icono" aria-hidden="true"><i class="bi bi-key"></i></span>
                    <div class="identidad-modal-texto">
                        <span class="clave" id="relacionesClave"></span>
                        <div class="celda-secundaria" id="relacionesDescripcion"></div>
                    </div>
                </div>
                <div id="relacionesContenido"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
