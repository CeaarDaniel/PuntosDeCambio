<?php
/**
 * pages/modulos.php — Catálogo de módulos.
 *
 * Solo el marcado. Datos: api/operacionesModulos.php. Lógica:
 * assets/js/modulos.js. Acepta ?sistema=XXX para llegar ya filtrada.
 */
if (!defined('EN_APLICACION')) {
    http_response_code(404);
    exit;
}
?>
<div class="page-header">
    <div>
        <h1>Módulos</h1>
        <p class="page-subtitle">Pantallas o secciones de cada aplicación. Agrupan los permisos y forman el menú.</p>
    </div>
    <div class="page-header-actions">
        <button class="btn btn-primary" type="button" id="btnNuevoModulo">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>Nuevo módulo
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
</div>

<section class="card tabla-contenida" aria-label="Listado de módulos">
    <div class="card-body">
        <div id="modulosEstado">
            <div class="app-loader">
                <div class="spinner-border" role="status"><span class="visually-hidden">Cargando…</span></div>
                <div>Cargando módulos…</div>
            </div>
        </div>

        <table class="table table-hover align-middle w-100 d-none" id="tablaModulos">
            <thead>
                <tr>
                    <th>Aplicación</th>
                    <th>Módulo</th>
                    <th>Ruta</th>
                    <th>Orden</th>
                    <th>Permisos</th>
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
<div class="modal fade" id="modalModulo" tabindex="-1" aria-labelledby="modalModuloTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content" id="formModulo" novalidate autocomplete="off">
            <div class="modal-header">
                <h2 class="modal-title" id="modalModuloTitulo">Nuevo módulo</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-danger d-none" id="modalModuloMensaje" role="alert" aria-live="polite"></div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required" for="moduloAplicacion">Aplicación</label>
                        <select class="form-select" id="moduloAplicacion" required></select>
                        <div class="invalid-feedback">Selecciona la aplicación.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required" for="moduloCodigo">Código del módulo</label>
                        <input class="form-control" type="text" id="moduloCodigo"
                               maxlength="<?php echo CODIGO_LONGITUD_MAXIMA; ?>"
                               pattern="<?php echo escapar(PATRON_CODIGO_MODULO); ?>"
                               placeholder="Ej. defectos" autocapitalize="off" spellcheck="false" required>
                        <div class="form-text" id="moduloCodigoAyuda">Letras sin acentos, números y guion bajo. No se puede cambiar después.</div>
                        <div class="invalid-feedback">Usa letras sin acentos, números y guion bajo, empezando con letra.</div>
                    </div>

                    <div class="col-12">
                        <div class="clave-completa">
                            <span>Clave del módulo:</span>
                            <span class="clave" id="moduloClavePrevia">—</span>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label required" for="moduloNombre">Nombre visible</label>
                        <input class="form-control" type="text" id="moduloNombre" maxlength="100"
                               placeholder="Texto que aparece en el menú" required>
                        <div class="invalid-feedback">Escribe el nombre.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required" for="moduloOrden">Orden en el menú</label>
                        <input class="form-control" type="number" id="moduloOrden" min="0" max="9999" step="1" value="0" required>
                        <div class="invalid-feedback">Número entre 0 y 9999.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="moduloRuta">Ruta</label>
                        <input class="form-control" type="text" id="moduloRuta" maxlength="100" placeholder="Opcional. Ej. #/defectos">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="moduloIcono">Icono</label>
                        <div class="d-flex gap-2 align-items-start">
                            <span class="icono-preview" aria-hidden="true"><i class="bi bi-square" id="moduloIconoPrevia"></i></span>
                            <div class="flex-grow-1">
                                <input class="form-control" type="text" id="moduloIcono" maxlength="50"
                                       pattern="<?php echo escapar(PATRON_ICONO); ?>"
                                       placeholder="Opcional. Ej. bi-diagram-3" autocapitalize="off" spellcheck="false">
                                <div class="invalid-feedback">Debe ser una clase de Bootstrap Icons, por ejemplo bi-diagram-3.</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 js-solo-alta">
                        <label class="form-label required" for="moduloActivo">Estado</label>
                        <select class="form-select" id="moduloActivo" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>

                    <div class="col-md-6 js-solo-alta d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="moduloCrearVer" checked>
                            <label class="form-check-label" for="moduloCrearVer">
                                Crear también la acción <span class="clave">ver</span>
                                <span class="d-block form-text mt-0">Es el permiso para entrar al módulo.</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnGuardarModulo">
                    <i class="bi bi-check2" aria-hidden="true"></i>Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================================================================
     Modal: permisos del módulo
     ================================================================== -->
<div class="modal fade" id="modalDetalleModulo" tabindex="-1" aria-labelledby="modalDetalleModuloTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalDetalleModuloTitulo">Permisos del módulo</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="identidad-modal">
                    <span class="identidad-modal-icono" aria-hidden="true"><i class="bi bi-window-stack" id="detalleModuloIcono"></i></span>
                    <div class="identidad-modal-texto">
                        <div class="fw-semibold" id="detalleModuloNombre"></div>
                        <span class="clave" id="detalleModuloClave"></span>
                    </div>
                </div>
                <div id="detalleModuloPermisos"></div>
            </div>

            <div class="modal-footer">
                <a class="btn btn-outline-primary" id="linkAdministrarPermisos" href="index.php?p=permisos">
                    <i class="bi bi-key" aria-hidden="true"></i>Administrar permisos
                </a>
                <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
