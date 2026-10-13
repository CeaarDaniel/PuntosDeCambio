<!-- MENU REPORTES
     Reportes de asistencia y de puntos de cambio con datos reales.
     Datos: api/operacionesReportes.php · Lógica: scripts/menuReportes.js
     Estilos: css/analytics.css (compartido con el dashboard; no se modifica desde aquí). -->
<link rel="stylesheet" href="./css/analytics.css">

<style>
    /* Distribución en chips: cada estatus/tipo con su cantidad y % juntos, para que no se
       confunda a qué renglón pertenece cada valor. Solo aplica a esta página. */
    #reports .reporte-distribucion {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    #reports .reporte-distribucion > div {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border: 1px solid var(--border);
        border-radius: 999px;
        background: var(--surface-soft);
    }

    #reports .reporte-distribucion small {
        font-weight: 500;
        color: var(--text-secondary);
    }
</style>

<div id="reports" class="analytics-view reports-view">
    <section class="analytics-hero" aria-labelledby="reportsHeading">
        <div class="analytics-hero-copy">
            <span class="analytics-eyebrow">
                <i class="bi bi-bar-chart-line" aria-hidden="true"></i>
                Centro de reportes
            </span>
            <h2 id="reportsHeading">Reportes de asistencia y puntos de cambio</h2>
            <p>Consulta la asistencia registrada y los puntos de cambio por periodo, línea y tipo.</p>
        </div>
    </section>

    <!-- Pestañas: el auto-fit acomoda 2 columnas en pantalla grande y 1 en celular -->
    <section class="analytics-report-types" role="tablist" aria-label="Tipo de reporte"
             style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
        <button type="button" class="analytics-report-type is-active" role="tab" id="reportTabAsistencia"
                data-report-tab="reportPanelAsistencia" aria-controls="reportPanelAsistencia" aria-selected="true">
            <span><i class="bi bi-person-check" aria-hidden="true"></i></span>
            <span>
                <strong>Asistencia</strong>
                <small>Presencia y ausentismo</small>
            </span>
        </button>
        <button type="button" class="analytics-report-type" role="tab" id="reportTabPC"
                data-report-tab="reportPanelPC" aria-controls="reportPanelPC" aria-selected="false">
            <span><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span>
            <span>
                <strong>Puntos de cambio</strong>
                <small>Registro y cumplimiento</small>
            </span>
        </button>
    </section>

    <!-- ================================================================
         PESTAÑA 1: ASISTENCIA (SPC_REGISTRO_ASISTENCIA)
         ================================================================ -->
    <div id="reportPanelAsistencia" role="tabpanel" aria-labelledby="reportTabAsistencia">
        <form id="formReporteAsistencia" class="analytics-filters analytics-filters-wide" aria-label="Filtros del reporte de asistencia">
            <div class="analytics-filter-intro">
                <span class="analytics-filter-icon">
                    <i class="bi bi-sliders" aria-hidden="true"></i>
                </span>
                <span>
                    <strong>Configurar reporte</strong>
                    <small>Periodo, línea y turno de la asistencia.</small>
                </span>
            </div>

            <div class="analytics-filter-field">
                <label for="asistenciaDesde">Desde</label>
                <input type="date" id="asistenciaDesde" class="form-control" required>
            </div>

            <div class="analytics-filter-field">
                <label for="asistenciaHasta">Hasta</label>
                <input type="date" id="asistenciaHasta" class="form-control" required>
            </div>

            <div class="analytics-filter-field">
                <label for="asistenciaLinea">Línea</label>
                <select id="asistenciaLinea" class="form-select">
                    <option value="">Todas</option>
                </select>
            </div>

            <div class="analytics-filter-field">
                <label for="asistenciaTurno">Turno</label>
                <select id="asistenciaTurno" class="form-select">
                    <option value="">Todos</option>
                    <option value="1">Turno 1</option>
                    <option value="2">Turno 2</option>
                </select>
            </div>

            <button type="submit" class="analytics-btn analytics-btn-primary">
                <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
                Actualizar
            </button>
        </form>

        <section class="analytics-kpi-grid analytics-kpi-grid-reports" aria-label="Resumen de asistencia">
            <article class="analytics-kpi analytics-kpi-success">
                <div class="analytics-kpi-icon"><i class="bi bi-person-check" aria-hidden="true"></i></div>
                <div class="analytics-kpi-copy">
                    <span>% de asistencia</span>
                    <strong id="asisKpiPorcentaje">--</strong>
                    <small>Asistencia y tiempo extra</small>
                </div>
            </article>

            <article class="analytics-kpi analytics-kpi-primary">
                <div class="analytics-kpi-icon"><i class="bi bi-journal-check" aria-hidden="true"></i></div>
                <div class="analytics-kpi-copy">
                    <span>Registros</span>
                    <strong id="asisKpiRegistros">--</strong>
                    <small id="asisKpiPersonas">-- personas</small>
                </div>
            </article>

            <article class="analytics-kpi analytics-kpi-warning">
                <div class="analytics-kpi-icon"><i class="bi bi-person-x" aria-hidden="true"></i></div>
                <div class="analytics-kpi-copy">
                    <span>Faltas injustificadas</span>
                    <strong id="asisKpiFaltas">--</strong>
                    <small>En el periodo</small>
                </div>
            </article>

            <article class="analytics-kpi analytics-kpi-cyan">
                <div class="analytics-kpi-icon"><i class="bi bi-calendar-x" aria-hidden="true"></i></div>
                <div class="analytics-kpi-copy">
                    <span>Otras ausencias</span>
                    <strong id="asisKpiOtras">--</strong>
                    <small>Permisos, vacaciones, incapacidad, etc.</small>
                </div>
            </article>
        </section>

        <section class="analytics-layout">
            <article class="analytics-panel">
                <header class="analytics-panel-header">
                    <div>
                        <span class="analytics-panel-kicker">Distribución</span>
                        <h3>Registros por estatus</h3>
                        <p>Cantidad y porcentaje de cada estatus de asistencia en el periodo.</p>
                    </div>
                </header>
                <div id="asistenciaPorEstatus" class="analytics-legend reporte-distribucion"></div>
            </article>

            <article class="analytics-panel analytics-table-panel">
                <header class="analytics-panel-header">
                    <div>
                        <span class="analytics-panel-kicker">Detalle</span>
                        <h3>Registros de asistencia</h3>
                        <p id="asistenciaAviso">Registros del periodo, del más reciente al más antiguo.</p>
                    </div>
                    <span class="analytics-badge" id="asistenciaConteo">0 registros</span>
                </header>

                <div class="analytics-table-wrap">
                    <table id="tablaReporteAsistencia" class="analytics-table display" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Línea</th>
                                <th>Turno</th>
                                <th>Nómina</th>
                                <th>Nombre</th>
                                <th>Estación(es)</th>
                                <th>Estatus</th>
                                <th>Comentario</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </article>
        </section>
    </div>

    <!-- ================================================================
         PESTAÑA 2: PUNTOS DE CAMBIO (SPC_PUNTOS_CAMBIO y su flujo)
         ================================================================ -->
    <div id="reportPanelPC" role="tabpanel" aria-labelledby="reportTabPC" hidden>
        <form id="formReportePC" class="analytics-filters analytics-filters-wide" aria-label="Filtros del reporte de puntos de cambio">
            <div class="analytics-filter-intro">
                <span class="analytics-filter-icon">
                    <i class="bi bi-sliders" aria-hidden="true"></i>
                </span>
                <span>
                    <strong>Configurar reporte</strong>
                    <small>Periodo (fecha de inicio), línea y tipo de PC.</small>
                </span>
            </div>

            <div class="analytics-filter-field">
                <label for="pcDesde">Desde</label>
                <input type="date" id="pcDesde" class="form-control" required>
            </div>

            <div class="analytics-filter-field">
                <label for="pcHasta">Hasta</label>
                <input type="date" id="pcHasta" class="form-control" required>
            </div>

            <div class="analytics-filter-field">
                <label for="pcLinea">Línea</label>
                <select id="pcLinea" class="form-select">
                    <option value="">Todas</option>
                </select>
            </div>

            <div class="analytics-filter-field">
                <label for="pcTipo">Tipo de punto de cambio</label>
                <select id="pcTipo" class="form-select">
                    <option value="">Todos</option>
                </select>
            </div>

            <button type="submit" class="analytics-btn analytics-btn-primary">
                <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
                Actualizar
            </button>
        </form>

        <section class="analytics-kpi-grid analytics-kpi-grid-reports" aria-label="Resumen de puntos de cambio">
            <article class="analytics-kpi analytics-kpi-primary">
                <div class="analytics-kpi-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></div>
                <div class="analytics-kpi-copy">
                    <span>Puntos de cambio</span>
                    <strong id="pcKpiTotal">--</strong>
                    <small>Iniciados en el periodo</small>
                </div>
            </article>

            <article class="analytics-kpi analytics-kpi-warning">
                <div class="analytics-kpi-icon"><i class="bi bi-hourglass-split" aria-hidden="true"></i></div>
                <div class="analytics-kpi-copy">
                    <span>Activos</span>
                    <strong id="pcKpiActivos">--</strong>
                    <small>Sin cierre</small>
                </div>
            </article>

            <article class="analytics-kpi analytics-kpi-success">
                <div class="analytics-kpi-icon"><i class="bi bi-check2-circle" aria-hidden="true"></i></div>
                <div class="analytics-kpi-copy">
                    <span>Finalizados</span>
                    <strong id="pcKpiFinalizados">--</strong>
                    <small>Con cierre registrado</small>
                </div>
            </article>

            <article class="analytics-kpi analytics-kpi-cyan">
                <div class="analytics-kpi-icon"><i class="bi bi-x-circle" aria-hidden="true"></i></div>
                <div class="analytics-kpi-copy">
                    <span>Cancelados</span>
                    <strong id="pcKpiCancelados">--</strong>
                    <small>En el periodo</small>
                </div>
            </article>
        </section>

        <section class="analytics-layout">
            <article class="analytics-panel">
                <header class="analytics-panel-header">
                    <div>
                        <span class="analytics-panel-kicker">Distribución</span>
                        <h3>Puntos de cambio por tipo (4M)</h3>
                        <p>Todos los tipos del periodo y la línea, sin importar el tipo seleccionado.</p>
                    </div>
                </header>
                <div id="pcPorTipo" class="analytics-legend reporte-distribucion"></div>
            </article>

            <article class="analytics-panel analytics-table-panel">
                <header class="analytics-panel-header">
                    <div>
                        <span class="analytics-panel-kicker">Detalle</span>
                        <h3>Listado de puntos de cambio</h3>
                        <p id="pcAviso">Puntos de cambio del periodo, del más reciente al más antiguo.</p>
                    </div>
                    <span class="analytics-badge" id="pcConteo">0 registros</span>
                </header>

                <div class="analytics-table-wrap">
                    <table id="tablaReportePC" class="analytics-table display" style="width:100%;">
                        <thead>
                            <tr>
                                <th>No. control</th>
                                <th>Tipo</th>
                                <th>Línea</th>
                                <th>Nómina</th>
                                <th>Nombre</th>
                                <th>Motivo / detalles</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th>Tipo de cambio</th>
                                <th>Estatus</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </article>
        </section>
    </div>
</div>
<!--Fin menu reportes -->
