<?php
/**
 * pages/aplicaciones.php — Catálogo de aplicaciones.
 *
 * Solo el marcado. Datos: api/operacionesAplicaciones.php. Lógica:
 * assets/js/aplicaciones.js. Se incluye desde index.php, que ya validó la sesión.
 */
if (!defined('EN_APLICACION')) {
    http_response_code(404);
    exit;
}
?>
<div class="page-header">
    <div>
        <h1>Aplicaciones</h1>
        <p class="page-subtitle">Sistemas internos que usan el modelo de usuarios, roles y permisos.</p>
    </div>
    <div class="page-header-actions">
        <button class="btn btn-primary" type="button" id="btnNuevaAplicacion">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>Nueva aplicación
        </button>
    </div>
</div>

<section class="card tabla-contenida" aria-label="Listado de aplicaciones">
    <div class="card-body">
        <div id="aplicacionesEstado">
            <div class="app-loader">
                <div class="spinner-border" role="status"><span class="visually-hidden">Cargando…</span></div>
                <div>Cargando aplicaciones…</div>
            </div>
        </div>

        <table class="table table-hover align-middle w-100 d-none" id="tablaAplicaciones">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Módulos</th>
                    <th>Roles</th>
                    <th>Permisos</th>
                    <th title="Usuarios con algún rol o permiso individual de la aplicación">Usuarios</th>
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
<div class="modal fade" id="modalAplicacion" tabindex="-1" aria-labelledby="modalAplicacionTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content" id="formAplicacion" novalidate autocomplete="off">
            <div class="modal-header">
                <h2 class="modal-title" id="modalAplicacionTitulo">Nueva aplicación</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-danger d-none" id="modalAplicacionMensaje" role="alert" aria-live="polite"></div>

                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label required" for="aplicacionCodigo">Código</label>
                        <input class="form-control" type="text" id="aplicacionCodigo" name="codigoSistema"
                               maxlength="<?php echo CODIGO_LONGITUD_MAXIMA; ?>"
                               pattern="<?php echo escapar(PATRON_CODIGO_SISTEMA); ?>"
                               placeholder="Ej. SRD" autocapitalize="characters" spellcheck="false" required>
                        <div class="form-text" id="aplicacionCodigoAyuda">
                            MAYÚSCULAS, números y guion bajo. No se puede cambiar después: el código de la
                            aplicación lo usa tal cual.
                        </div>
                        <div class="invalid-feedback">Usa solo MAYÚSCULAS, números y guion bajo, empezando con letra.</div>
                    </div>

                    <div class="col-md-7">
                        <label class="form-label required" for="aplicacionNombre">Nombre</label>
                        <input class="form-control" type="text" id="aplicacionNombre" name="nombre" maxlength="100" required>
                        <div class="invalid-feedback">Escribe el nombre.</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="aplicacionDescripcion">Descripción</label>
                        <textarea class="form-control" id="aplicacionDescripcion" name="descripcion" rows="2"
                                  maxlength="250" placeholder="Opcional. Para qué sirve la aplicación."></textarea>
                    </div>

                    <div class="col-md-5 js-solo-alta">
                        <label class="form-label required" for="aplicacionActivo">Estado</label>
                        <select class="form-select" id="aplicacionActivo" name="activo" required>
                            <option value="1">Activa</option>
                            <option value="0">Inactiva</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnGuardarAplicacion">
                    <i class="bi bi-check2" aria-hidden="true"></i>Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================================================================
     Modal: detalle (módulos y roles)
     ================================================================== -->
<div class="modal fade" id="modalDetalleAplicacion" tabindex="-1" aria-labelledby="modalDetalleAplicacionTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalDetalleAplicacionTitulo">Detalle de la aplicación</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div id="detalleAplicacionEstado"></div>

                <div class="d-none" id="detalleAplicacionContenido">
                    <div class="identidad-modal">
                        <span class="identidad-modal-icono" aria-hidden="true"><i class="bi bi-grid-1x2"></i></span>
                        <div class="identidad-modal-texto">
                            <div class="fw-semibold" id="detalleAplicacionNombre"></div>
                            <span class="clave" id="detalleAplicacionCodigo"></span>
                            <div class="celda-secundaria" id="detalleAplicacionDescripcion"></div>
                        </div>
                        <div id="detalleAplicacionEstatus"></div>
                    </div>

                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tabAplicacionModulos" data-bs-toggle="tab"
                                    data-bs-target="#panelAplicacionModulos" type="button" role="tab"
                                    aria-controls="panelAplicacionModulos" aria-selected="true">
                                <i class="bi bi-window-stack" aria-hidden="true"></i><span>Módulos</span>
                                <span class="badge badge-estado-neutro" id="detalleAplicacionTotalModulos">0</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tabAplicacionRoles" data-bs-toggle="tab"
                                    data-bs-target="#panelAplicacionRoles" type="button" role="tab"
                                    aria-controls="panelAplicacionRoles" aria-selected="false">
                                <i class="bi bi-person-badge" aria-hidden="true"></i><span>Roles</span>
                                <span class="badge badge-estado-neutro" id="detalleAplicacionTotalRoles">0</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="panelAplicacionModulos" role="tabpanel" aria-labelledby="tabAplicacionModulos"></div>
                        <div class="tab-pane fade" id="panelAplicacionRoles" role="tabpanel" aria-labelledby="tabAplicacionRoles"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <a class="btn btn-outline-primary" id="linkAdministrarModulos" href="index.php?p=modulos">
                    <i class="bi bi-window-stack" aria-hidden="true"></i>Administrar módulos
                </a>
                <a class="btn btn-outline-primary" id="linkAdministrarRoles" href="index.php?p=roles">
                    <i class="bi bi-person-badge" aria-hidden="true"></i>Administrar roles
                </a>
                <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
