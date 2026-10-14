<?php
/**
 * pages/roles.php — Catálogo de roles y asignación de sus permisos.
 *
 * Solo el marcado. Datos: api/operacionesRoles.php. Lógica: assets/js/roles.js.
 * Acepta ?sistema=XXX para llegar ya filtrada.
 *
 * Tres modales:
 *   #modalRol           Alta y edición.
 *   #modalPermisosRol   Lista de checks con los permisos de la aplicación del rol.
 *                       Las confirmaciones (copiar de otro rol, cerrar sin guardar)
 *                       son pasos dentro del mismo modal: Bootstrap no apila modales.
 *   #modalDetalleRol    Usuarios que tienen el rol y sus permisos.
 */
if (!defined('EN_APLICACION')) {
    http_response_code(404);
    exit;
}
?>
<div class="page-header">
    <div>
        <h1>Roles</h1>
        <p class="page-subtitle">Paquetes de permisos por aplicación. Un usuario puede tener varios roles.</p>
    </div>
    <div class="page-header-actions">
        <button class="btn btn-primary" type="button" id="btnNuevoRol">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>Nuevo rol
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

<section class="card tabla-contenida" aria-label="Listado de roles">
    <div class="card-body">
        <div id="rolesEstado">
            <div class="app-loader">
                <div class="spinner-border" role="status"><span class="visually-hidden">Cargando…</span></div>
                <div>Cargando roles…</div>
            </div>
        </div>

        <table class="table table-hover align-middle w-100 d-none" id="tablaRoles">
            <thead>
                <tr>
                    <th>Aplicación</th>
                    <th>Rol</th>
                    <th>Permisos</th>
                    <th>Usuarios</th>
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
<div class="modal fade" id="modalRol" tabindex="-1" aria-labelledby="modalRolTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content" id="formRol" novalidate autocomplete="off">
            <div class="modal-header">
                <h2 class="modal-title" id="modalRolTitulo">Nuevo rol</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-danger d-none" id="modalRolMensaje" role="alert" aria-live="polite"></div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required" for="rolAplicacion">Aplicación</label>
                        <select class="form-select" id="rolAplicacion" required></select>
                        <div class="invalid-feedback">Selecciona la aplicación.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required" for="rolCodigo">Código del rol</label>
                        <input class="form-control" type="text" id="rolCodigo"
                               maxlength="<?php echo CODIGO_LONGITUD_MAXIMA; ?>"
                               pattern="<?php echo escapar(PATRON_CODIGO_ROL); ?>"
                               placeholder="Ej. sup_calidad" autocapitalize="off" spellcheck="false" required>
                        <div class="form-text" id="rolCodigoAyuda">Minúsculas sin acentos, números y guion bajo. No se puede cambiar después.</div>
                        <div class="invalid-feedback">Usa minúsculas sin acentos, números y guion bajo, empezando con letra.</div>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label required" for="rolNombre">Nombre del rol</label>
                        <input class="form-control" type="text" id="rolNombre" maxlength="100"
                               placeholder="Ej. Supervisor de calidad" required>
                        <div class="invalid-feedback">Escribe el nombre.</div>
                    </div>

                    <div class="col-md-4 js-solo-alta">
                        <label class="form-label required" for="rolActivo">Estado</label>
                        <select class="form-select" id="rolActivo" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="rolDescripcion">Descripción</label>
                        <textarea class="form-control" id="rolDescripcion" rows="2" maxlength="250"
                                  placeholder="Opcional. Qué puesto representa y qué puede hacer."></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnGuardarRol">
                    <i class="bi bi-check2" aria-hidden="true"></i>Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================================================================
     Modal: permisos del rol (lista de checks)
     ================================================================== -->
<div class="modal fade" id="modalPermisosRol" tabindex="-1" aria-labelledby="modalPermisosRolTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalPermisosRolTitulo">Permisos del rol</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="identidad-modal">
                    <span class="identidad-modal-icono" aria-hidden="true"><i class="bi bi-person-badge"></i></span>
                    <div class="identidad-modal-texto">
                        <div class="fw-semibold" id="permisosRolNombre"></div>
                        <span class="clave" id="permisosRolClave"></span>
                    </div>
                    <div id="permisosRolEstado"></div>
                </div>

                <div class="alert alert-danger d-none" id="permisosRolMensaje" role="alert" aria-live="polite"></div>

                <!-- Confirmación de cerrar con cambios sin guardar (paso dentro del modal). -->
                <div class="alert alert-warning d-none rol-confirmacion" id="permisosRolSalir" role="alert">
                    <div><i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>Hay cambios sin guardar. ¿Salir sin guardarlos?</div>
                    <div class="rol-confirmacion-botones">
                        <button type="button" class="btn btn-sm btn-soft" id="btnSeguirEditando">Seguir editando</button>
                        <button type="button" class="btn btn-sm btn-danger" id="btnDescartarCambios">Salir sin guardar</button>
                    </div>
                </div>

                <div class="d-none" id="permisosRolContenido">
                    <div class="rol-herramientas">
                        <div class="rol-herramientas-campo">
                            <label class="form-label" for="permisosRolBuscar">Buscar</label>
                            <input class="form-control" type="search" id="permisosRolBuscar" placeholder="Acción, módulo o descripción">
                        </div>
                        <div class="rol-herramientas-campo">
                            <label class="form-label" for="permisosRolCopiar">Copiar los permisos de otro rol</label>
                            <select class="form-select" id="permisosRolCopiar">
                                <option value="">Selecciona un rol…</option>
                            </select>
                        </div>
                    </div>

                    <!-- Confirmación de copiar (paso dentro del modal). -->
                    <div class="alert alert-info d-none rol-confirmacion" id="permisosRolCopiarConfirmacion" role="alert">
                        <div id="permisosRolCopiarTexto"></div>
                        <div class="rol-confirmacion-botones">
                            <button type="button" class="btn btn-sm btn-soft" id="btnCopiarCancelar">Cancelar</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnCopiarAgregar">Agregar a la selección</button>
                            <button type="button" class="btn btn-sm btn-primary" id="btnCopiarReemplazar">Reemplazar la selección</button>
                        </div>
                    </div>

                    <div id="permisosRolLista"></div>
                </div>

                <div id="permisosRolCarga"></div>
            </div>

            <div class="modal-footer rol-pie">
                <div class="rol-resumen" id="permisosRolResumen" aria-live="polite"></div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarPermisosRol" disabled>
                        <i class="bi bi-check2" aria-hidden="true"></i>Guardar permisos
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================================================================
     Modal: detalle (usuarios y permisos)
     ================================================================== -->
<div class="modal fade" id="modalDetalleRol" tabindex="-1" aria-labelledby="modalDetalleRolTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalDetalleRolTitulo">Detalle del rol</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="identidad-modal">
                    <span class="identidad-modal-icono" aria-hidden="true"><i class="bi bi-person-badge"></i></span>
                    <div class="identidad-modal-texto">
                        <div class="fw-semibold" id="detalleRolNombre"></div>
                        <span class="clave" id="detalleRolClave"></span>
                        <div class="celda-secundaria" id="detalleRolDescripcion"></div>
                    </div>
                </div>

                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tabRolUsuarios" data-bs-toggle="tab" data-bs-target="#panelRolUsuarios"
                                type="button" role="tab" aria-controls="panelRolUsuarios" aria-selected="true">
                            <i class="bi bi-people" aria-hidden="true"></i><span>Usuarios</span>
                            <span class="badge badge-estado-neutro" id="detalleRolTotalUsuarios">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tabRolPermisos" data-bs-toggle="tab" data-bs-target="#panelRolPermisos"
                                type="button" role="tab" aria-controls="panelRolPermisos" aria-selected="false">
                            <i class="bi bi-key" aria-hidden="true"></i><span>Permisos</span>
                            <span class="badge badge-estado-neutro" id="detalleRolTotalPermisos">0</span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="panelRolUsuarios" role="tabpanel" aria-labelledby="tabRolUsuarios"></div>
                    <div class="tab-pane fade" id="panelRolPermisos" role="tabpanel" aria-labelledby="tabRolPermisos"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
