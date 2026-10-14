<?php
/**
 * pages/permisosEfectivos.php — Qué puede hacer un usuario y de dónde lo obtiene.
 *
 * Solo el marcado. Datos: api/operacionesPermisosEfectivos.php. Lógica:
 * assets/js/permisosEfectivos.js. Acepta ?nomina=XXX para llegar con el
 * usuario ya elegido.
 */
if (!defined('EN_APLICACION')) {
    http_response_code(404);
    exit;
}
?>
<div class="page-header">
    <div>
        <h1>Permisos efectivos</h1>
        <p class="page-subtitle">Lo que un usuario puede hacer al final, sumando sus roles y sus excepciones individuales.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-4">
        <?php
        $selectorTitulo = '1. Elige el usuario';
        require __DIR__ . '/../includes/selectorUsuario.php';
        ?>

        <!-- Resumen por aplicación: acceso, contadores y roles. -->
        <section class="card mt-4 d-none" id="efectivosAplicaciones" aria-labelledby="efectivosAplicacionesTitulo">
            <div class="card-header" id="efectivosAplicacionesTitulo">
                <i class="bi bi-grid-1x2 me-1" aria-hidden="true"></i>Aplicaciones y roles
            </div>
            <div class="card-body" id="efectivosAplicacionesCuerpo"></div>
        </section>

        <section class="card mt-4" aria-labelledby="efectivosReglaTitulo">
            <div class="card-header" id="efectivosReglaTitulo">
                <i class="bi bi-lightbulb me-1" aria-hidden="true"></i>¿Cómo se calcula?
            </div>
            <div class="card-body efectivos-regla">
                <ol>
                    <li>Se suman los permisos de todos sus <strong>roles activos</strong>.</li>
                    <li>Se agregan sus excepciones <span class="badge badge-conceder">Conceder</span> vigentes.</li>
                    <li>Se quitan sus excepciones <span class="badge badge-denegar">Denegar</span> vigentes: siempre ganan.</li>
                    <li>No cuentan aplicaciones, módulos ni roles inactivos, ni excepciones vencidas.</li>
                </ol>
                <p class="mb-0">Cada aplicación aplica los cambios cuando el usuario vuelve a iniciar sesión.</p>
            </div>
        </section>
    </div>

    <div class="col-12 col-lg-8">
        <section class="card paso-bloqueado tabla-contenida" id="panelEfectivos" aria-labelledby="panelEfectivosTitulo">
            <div class="card-header" id="panelEfectivosTitulo">
                <i class="bi bi-shield-check me-1" aria-hidden="true"></i>2. Resultado final
            </div>
            <div class="card-body">
                <div class="alert alert-warning d-none" id="avisoEfectivosInactivo" role="alert">
                    <i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>
                    Este usuario está <strong>inactivo</strong>: no puede iniciar sesión en ninguna aplicación,
                    aunque aquí se vean los permisos que tendría.
                </div>

                <!-- Totales por resultado; también funcionan como filtro rápido. -->
                <div class="filtro-pildoras d-none" id="filtroResultado" role="group" aria-label="Filtrar por resultado">
                    <button type="button" class="filtro-pildora active" data-resultado="" aria-pressed="true">
                        Todos <span class="filtro-pildora-total" data-total="todos">0</span>
                    </button>
                    <button type="button" class="filtro-pildora" data-resultado="permitido" aria-pressed="false">
                        <i class="bi bi-check-circle" aria-hidden="true"></i>Permitidos
                        <span class="filtro-pildora-total" data-total="permitido">0</span>
                    </button>
                    <button type="button" class="filtro-pildora" data-resultado="denegado" aria-pressed="false">
                        <i class="bi bi-slash-circle" aria-hidden="true"></i>Denegados
                        <span class="filtro-pildora-total" data-total="denegado">0</span>
                    </button>
                    <button type="button" class="filtro-pildora" data-resultado="vencido" aria-pressed="false">
                        <i class="bi bi-calendar-x" aria-hidden="true"></i>Vencidos
                        <span class="filtro-pildora-total" data-total="vencido">0</span>
                    </button>
                </div>

                <div class="barra-filtros d-none" id="filtrosEfectivos">
                    <div class="barra-filtros-campo">
                        <label class="form-label" for="filtroEfAplicacion">Aplicación</label>
                        <select class="form-select" id="filtroEfAplicacion"><option value="">Todas</option></select>
                    </div>
                    <div class="barra-filtros-campo">
                        <label class="form-label" for="filtroEfModulo">Módulo</label>
                        <select class="form-select" id="filtroEfModulo"><option value="">Todos</option></select>
                    </div>
                    <div class="barra-filtros-campo">
                        <label class="form-label" for="filtroEfRol">Origen</label>
                        <select class="form-select" id="filtroEfRol"><option value="">Cualquiera</option></select>
                    </div>
                </div>

                <div id="efectivosEstado">
                    <div class="empty-state empty-state-compacto">
                        <i class="bi bi-shield-check" aria-hidden="true"></i>
                        <div class="empty-title">Elige un usuario</div>
                        <div>Verás cada permiso con su resultado y de dónde lo obtiene.</div>
                    </div>
                </div>

                <table class="table table-hover align-middle w-100 d-none" id="tablaEfectivos">
                    <thead>
                        <tr>
                            <th>Aplicación</th>
                            <th>Módulo</th>
                            <th>Acción</th>
                            <th>Resultado</th>
                            <th>Origen</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
</div>
