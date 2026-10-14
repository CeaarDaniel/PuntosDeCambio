<?php
/**
 * pages/usuarios.php — Administración de usuarios.
 *
 * Solo el marcado. Los datos llegan por AJAX desde api/operacionesUsuarios.php
 * y los maneja assets/js/usuarios.js. Se incluye desde index.php, que ya
 * validó la sesión.
 *
 * Tres modales:
 *   #modalUsuario         Alta y edición (en edición la nómina no se cambia).
 *   #modalContrasena      Restablecer contraseña, en dos pasos: captura y confirmación.
 *   #modalDetalleUsuario  Consulta de datos, roles, excepciones y permisos efectivos.
 */
if (!defined('EN_APLICACION')) {
    http_response_code(404);
    exit;
}
?>
<div class="page-header">
    <div>
        <h1>Usuarios</h1>
        <p class="page-subtitle">Cuentas compartidas por todas las aplicaciones: alta, datos, estatus y contraseñas.</p>
    </div>
    <div class="page-header-actions">
        <button class="btn btn-primary" type="button" id="btnNuevoUsuario">
            <i class="bi bi-person-plus" aria-hidden="true"></i>Nuevo usuario
        </button>
    </div>
</div>

<!-- Filtro rápido por estatus. Los números se llenan al cargar la tabla. -->
<div class="filtro-pildoras" role="group" aria-label="Filtrar por estatus">
    <button type="button" class="filtro-pildora active" data-estatus="" aria-pressed="true">
        Todos <span class="filtro-pildora-total" data-total="todos">—</span>
    </button>
    <button type="button" class="filtro-pildora" data-estatus="activo" aria-pressed="false">
        <i class="bi bi-check-circle" aria-hidden="true"></i>Activos
        <span class="filtro-pildora-total" data-total="activo">—</span>
    </button>
    <button type="button" class="filtro-pildora" data-estatus="inactivo" aria-pressed="false">
        <i class="bi bi-dash-circle" aria-hidden="true"></i>Inactivos
        <span class="filtro-pildora-total" data-total="inactivo">—</span>
    </button>
</div>

<section class="card tabla-contenida" aria-label="Listado de usuarios">
    <div class="card-body">
        <div id="usuariosEstado">
            <div class="app-loader">
                <div class="spinner-border" role="status"><span class="visually-hidden">Cargando…</span></div>
                <div>Cargando usuarios…</div>
            </div>
        </div>

        <table class="table table-hover align-middle w-100 d-none" id="tablaUsuarios">
            <thead>
                <tr>
                    <th>Nómina</th>
                    <th>Nombre</th>
                    <th>Estatus</th>
                    <th>Roles</th>
                    <th title="Permisos individuales vigentes">Permisos ind.</th>
                    <th>Alta</th>
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
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content" id="formUsuario" novalidate autocomplete="off">
            <div class="modal-header">
                <h2 class="modal-title" id="modalUsuarioTitulo">Nuevo usuario</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-danger d-none" id="modalUsuarioMensaje" role="alert" aria-live="polite"></div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required" for="usuarioNomina">Nómina</label>
                        <input class="form-control" type="text" id="usuarioNomina" name="nomina"
                               maxlength="<?php echo NOMINA_LONGITUD_MAXIMA; ?>"
                               pattern="[A-Za-z0-9][A-Za-z0-9._\-]*"
                               autocapitalize="off" spellcheck="false" required>
                        <div class="form-text" id="usuarioNominaAyuda">Sin espacios. No se puede cambiar después.</div>
                        <div class="invalid-feedback">Escribe la nómina: letras, números, punto, guion o guion bajo, sin espacios.</div>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label required" for="usuarioNombre">Nombre completo</label>
                        <input class="form-control" type="text" id="usuarioNombre" name="nombre"
                               maxlength="100" required>
                        <div class="invalid-feedback">Escribe el nombre.</div>
                    </div>

                    <!-- Solo en el alta. En edición la contraseña se cambia con su propio botón. -->
                    <div class="col-12 js-solo-alta">
                        <div class="usuarios-bloque-contrasena">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label required" for="usuarioContrasena">Contraseña inicial</label>
                                    <div class="input-group has-validation">
                                        <input class="form-control" type="password" id="usuarioContrasena" name="contrasena"
                                               minlength="<?php echo PASSWORD_LONGITUD_MINIMA; ?>"
                                               maxlength="<?php echo PASSWORD_LONGITUD_MAXIMA; ?>"
                                               autocomplete="new-password" required>
                                        <button class="btn btn-soft js-ver-contrasena" type="button"
                                                data-campos="#usuarioContrasena,#usuarioConfirmacion"
                                                title="Mostrar contraseña" aria-label="Mostrar contraseña" aria-pressed="false">
                                            <i class="bi bi-eye" aria-hidden="true"></i>
                                        </button>
                                        <div class="invalid-feedback">Mínimo <?php echo PASSWORD_LONGITUD_MINIMA; ?> caracteres.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required" for="usuarioConfirmacion">Confirmar contraseña</label>
                                    <input class="form-control" type="password" id="usuarioConfirmacion" name="confirmacion"
                                           maxlength="<?php echo PASSWORD_LONGITUD_MAXIMA; ?>"
                                           autocomplete="new-password" required>
                                    <div class="invalid-feedback">Las contraseñas no coinciden.</div>
                                </div>
                                <div class="col-12 d-flex flex-wrap align-items-center gap-2">
                                    <button class="btn btn-outline-primary btn-sm js-generar-contrasena" type="button"
                                            data-campo="#usuarioContrasena" data-confirmacion="#usuarioConfirmacion">
                                        <i class="bi bi-magic" aria-hidden="true"></i>Generar una contraseña
                                    </button>
                                    <span class="form-text m-0">
                                        Mínimo <?php echo PASSWORD_LONGITUD_MINIMA; ?> caracteres. Comunícala al usuario de forma segura.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 js-solo-alta">
                        <label class="form-label required" for="usuarioEstatus">Estatus</label>
                        <select class="form-select" id="usuarioEstatus" name="estatus" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="usuarioComentarios">Comentarios</label>
                        <textarea class="form-control" id="usuarioComentarios" name="comentarios"
                                  rows="3" maxlength="1000"
                                  placeholder="Opcional. Por ejemplo: área, puesto o motivo del alta."></textarea>
                        <div class="form-text text-end"><span id="usuarioComentariosContador">0</span>/1000</div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnGuardarUsuario">
                    <i class="bi bi-check2" aria-hidden="true"></i>Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================================================================
     Modal: restablecer contraseña (dos pasos dentro del mismo modal)
     ================================================================== -->
<div class="modal fade" id="modalContrasena" tabindex="-1" aria-labelledby="modalContrasenaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="formContrasena" novalidate autocomplete="off">
            <div class="modal-header">
                <h2 class="modal-title" id="modalContrasenaTitulo">Restablecer contraseña</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="usuarios-identidad">
                    <span class="usuarios-identidad-avatar" aria-hidden="true"><i class="bi bi-person"></i></span>
                    <div>
                        <div class="fw-semibold" id="contrasenaNombre"></div>
                        <span class="clave" id="contrasenaNomina"></span>
                    </div>
                </div>

                <div class="alert alert-danger d-none" id="modalContrasenaMensaje" role="alert" aria-live="polite"></div>

                <!-- Paso 1: captura -->
                <div id="contrasenaPaso1">
                    <div class="mb-3">
                        <label class="form-label required" for="contrasenaNueva">Nueva contraseña</label>
                        <div class="input-group has-validation">
                            <input class="form-control" type="password" id="contrasenaNueva" name="contrasena"
                                   minlength="<?php echo PASSWORD_LONGITUD_MINIMA; ?>"
                                   maxlength="<?php echo PASSWORD_LONGITUD_MAXIMA; ?>"
                                   autocomplete="new-password" required>
                            <button class="btn btn-soft js-ver-contrasena" type="button"
                                    data-campos="#contrasenaNueva,#contrasenaConfirmacion"
                                    title="Mostrar contraseña" aria-label="Mostrar contraseña" aria-pressed="false">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                            <div class="invalid-feedback">Mínimo <?php echo PASSWORD_LONGITUD_MINIMA; ?> caracteres.</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required" for="contrasenaConfirmacion">Confirmar contraseña</label>
                        <input class="form-control" type="password" id="contrasenaConfirmacion" name="confirmacion"
                               maxlength="<?php echo PASSWORD_LONGITUD_MAXIMA; ?>"
                               autocomplete="new-password" required>
                        <div class="invalid-feedback">Las contraseñas no coinciden.</div>
                    </div>
                    <button class="btn btn-outline-primary btn-sm js-generar-contrasena" type="button"
                            data-campo="#contrasenaNueva" data-confirmacion="#contrasenaConfirmacion">
                        <i class="bi bi-magic" aria-hidden="true"></i>Generar una contraseña
                    </button>
                    <p class="form-text mt-2 mb-0">
                        Mínimo <?php echo PASSWORD_LONGITUD_MINIMA; ?> caracteres. La contraseña no se vuelve a
                        mostrar después de guardarla: cópiala antes si la vas a entregar.
                    </p>
                </div>

                <!-- Paso 2: confirmación -->
                <div class="d-none" id="contrasenaPaso2">
                    <div class="confirmar-cuerpo">
                        <span class="confirmar-icono confirmar-icono-peligro" aria-hidden="true">
                            <i class="bi bi-exclamation-triangle"></i>
                        </span>
                        <div>
                            <p class="confirmar-mensaje">¿Restablecer la contraseña de este usuario?</p>
                            <p class="confirmar-detalle">
                                La contraseña actual dejará de funcionar de inmediato en todas las
                                aplicaciones. Comunica la nueva al usuario de forma segura.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <div class="d-flex gap-2" id="contrasenaAccionesPaso1">
                    <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        Continuar<i class="bi bi-arrow-right ms-2 me-0" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="d-flex gap-2 d-none" id="contrasenaAccionesPaso2">
                    <button type="button" class="btn btn-soft" id="btnContrasenaRegresar">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>Regresar
                    </button>
                    <button type="button" class="btn btn-danger" id="btnContrasenaConfirmar">
                        <i class="bi bi-key" aria-hidden="true"></i>Sí, restablecer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ==================================================================
     Modal: detalle del usuario
     ================================================================== -->
<div class="modal fade" id="modalDetalleUsuario" tabindex="-1" aria-labelledby="modalDetalleTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalDetalleTitulo">Detalle del usuario</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div id="detalleEstado"></div>

                <div class="d-none" id="detalleContenido">
                    <div class="usuarios-identidad">
                        <span class="usuarios-identidad-avatar" aria-hidden="true"><i class="bi bi-person"></i></span>
                        <div class="flex-grow-1">
                            <div class="fw-semibold" id="detalleNombre"></div>
                            <span class="clave" id="detalleNomina"></span>
                        </div>
                        <div id="detalleEstatus"></div>
                    </div>

                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tabDatos" data-bs-toggle="tab" data-bs-target="#panelDatos"
                                    type="button" role="tab" aria-controls="panelDatos" aria-selected="true">
                                <i class="bi bi-person-vcard" aria-hidden="true"></i><span>Datos</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tabRoles" data-bs-toggle="tab" data-bs-target="#panelRoles"
                                    type="button" role="tab" aria-controls="panelRoles" aria-selected="false">
                                <i class="bi bi-person-badge" aria-hidden="true"></i><span>Roles</span>
                                <span class="badge badge-estado-neutro" id="detalleTotalRoles">0</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tabExcepciones" data-bs-toggle="tab" data-bs-target="#panelExcepciones"
                                    type="button" role="tab" aria-controls="panelExcepciones" aria-selected="false">
                                <i class="bi bi-person-lock" aria-hidden="true"></i><span>Permisos individuales</span>
                                <span class="badge badge-estado-neutro" id="detalleTotalExcepciones">0</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tabEfectivos" data-bs-toggle="tab" data-bs-target="#panelEfectivos"
                                    type="button" role="tab" aria-controls="panelEfectivos" aria-selected="false">
                                <i class="bi bi-shield-check" aria-hidden="true"></i><span>Permisos efectivos</span>
                                <span class="badge badge-estado-neutro" id="detalleTotalEfectivos">0</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="panelDatos" role="tabpanel" aria-labelledby="tabDatos"></div>
                        <div class="tab-pane fade" id="panelRoles" role="tabpanel" aria-labelledby="tabRoles"></div>
                        <div class="tab-pane fade" id="panelExcepciones" role="tabpanel" aria-labelledby="tabExcepciones"></div>
                        <div class="tab-pane fade" id="panelEfectivos" role="tabpanel" aria-labelledby="tabEfectivos"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <!-- Accesos directos a las asignaciones de este usuario (?nomina=). -->
                <a class="btn btn-outline-primary" id="linkRolesUsuario" href="index.php?p=rolesUsuario">
                    <i class="bi bi-person-check" aria-hidden="true"></i>Asignar roles
                </a>
                <a class="btn btn-outline-primary" id="linkPermisosUsuario" href="index.php?p=permisosUsuario">
                    <i class="bi bi-person-lock" aria-hidden="true"></i>Permisos individuales
                </a>
                <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
