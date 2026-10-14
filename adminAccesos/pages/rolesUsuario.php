<?php
/**
 * pages/rolesUsuario.php — Asignar o quitar roles a un usuario.
 *
 * Solo el marcado. Datos: api/operacionesRolesUsuario.php. Lógica:
 * assets/js/rolesUsuario.js. Acepta ?nomina=XXX&sistema=YYY para llegar con
 * el usuario y la aplicación ya elegidos.
 */
if (!defined('EN_APLICACION')) {
    http_response_code(404);
    exit;
}
?>
<div class="page-header">
    <div>
        <h1>Roles de usuario</h1>
        <p class="page-subtitle">Elige un usuario y una aplicación, y marca los roles que debe tener en ella.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-4">
        <?php
        $selectorTitulo = '1. Elige el usuario';
        require __DIR__ . '/../includes/selectorUsuario.php';
        ?>

        <!-- Roles del usuario en todas las aplicaciones (contexto). -->
        <section class="card mt-4 d-none" id="resumenRolesUsuario" aria-labelledby="resumenRolesTitulo">
            <div class="card-header" id="resumenRolesTitulo">
                <i class="bi bi-person-badge me-1" aria-hidden="true"></i>Sus roles en todas las aplicaciones
            </div>
            <div class="card-body" id="resumenRolesCuerpo"></div>
        </section>
    </div>

    <div class="col-12 col-lg-8">
        <section class="card paso-bloqueado" id="panelRolesAplicacion" aria-labelledby="panelRolesTitulo">
            <div class="card-header" id="panelRolesTitulo">
                <i class="bi bi-ui-checks me-1" aria-hidden="true"></i>2. Elige la aplicación y marca sus roles
            </div>
            <div class="card-body">
                <div class="barra-filtros">
                    <div class="barra-filtros-campo">
                        <label class="form-label" for="rolesUsuarioAplicacion">Aplicación</label>
                        <select class="form-select" id="rolesUsuarioAplicacion" disabled>
                            <option value="">Primero elige un usuario</option>
                        </select>
                    </div>
                </div>

                <div class="alert alert-warning d-none" id="avisoUsuarioInactivo" role="alert">
                    <i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>
                    Este usuario está <strong>inactivo</strong>: no se le pueden asignar roles nuevos, solo quitar los que tiene.
                </div>

                <div id="rolesUsuarioLista">
                    <div class="empty-state empty-state-compacto">
                        <i class="bi bi-person-badge" aria-hidden="true"></i>
                        <div class="empty-title">Elige un usuario y una aplicación</div>
                        <div>Aquí aparecerán los roles de esa aplicación para marcarlos.</div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-none rol-usuario-pie" id="rolesUsuarioPie">
                <div class="rol-usuario-resumen" id="rolesUsuarioResumen" aria-live="polite"></div>
                <button class="btn btn-primary" type="button" id="btnGuardarRolesUsuario" disabled>
                    <i class="bi bi-check2" aria-hidden="true"></i>Guardar roles
                </button>
            </div>
        </section>
    </div>
</div>
