<link rel="stylesheet" href="./css/analytics.css">

<div id="reports" class="analytics-view reports-view">
    <section class="analytics-hero" aria-labelledby="reportsHeading">
        <div class="analytics-hero-copy">
            <span class="analytics-eyebrow">
                <i class="bi bi-bar-chart-line" aria-hidden="true"></i>
                Centro de reportes
            </span>
            <h2 id="reportsHeading">Indicadores para la toma de decisiones</h2>
            <p>Explora tendencias de asistencia, puntos de cambio y certificaciones mediante información simulada.</p>
        </div>

        <div class="analytics-update">
            <span class="analytics-status-dot" aria-hidden="true"></span>
            <span>
                <small>Modo borrador</small>
                <strong>Sin conexión a backend</strong>
            </span>
        </div>
    </section>

    <form id="reportsFilters" class="analytics-filters analytics-filters-wide" aria-label="Filtros de reportes">
        <div class="analytics-filter-intro">
            <span class="analytics-filter-icon">
                <i class="bi bi-sliders" aria-hidden="true"></i>
            </span>
            <span>
                <strong>Configurar reporte</strong>
                <small>Selecciona el alcance de la vista previa.</small>
            </span>
        </div>

        <div class="analytics-filter-field">
            <label for="reportDateFrom">Desde</label>
            <input type="date" id="reportDateFrom" class="form-control" value="2026-07-01">
        </div>

        <div class="analytics-filter-field">
            <label for="reportDateTo">Hasta</label>
            <input type="date" id="reportDateTo" class="form-control" value="2026-07-27">
        </div>

        <div class="analytics-filter-field">
            <label for="reportLine">Línea</label>
            <select id="reportLine" class="form-select">
                <option value="all">Todas</option>
                <option value="A01">Línea A01</option>
                <option value="A02">Línea A02</option>
                <option value="B01">Línea B01</option>
                <option value="B02">Línea B02</option>
            </select>
        </div>

        <div class="analytics-filter-field">
            <label for="reportShift">Turno</label>
            <select id="reportShift" class="form-select">
                <option value="all">Todos</option>
                <option value="1">Turno 1</option>
                <option value="2">Turno 2</option>
                <option value="3">Turno 3</option>
            </select>
        </div>

        <button type="submit" class="analytics-btn analytics-btn-primary">
            <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
            Actualizar
        </button>
    </form>

    <section class="analytics-report-types" aria-label="Tipos de reporte">
        <button type="button" class="analytics-report-type is-active" data-report-type="attendance" aria-pressed="true">
            <span><i class="bi bi-person-check" aria-hidden="true"></i></span>
            <span>
                <strong>Asistencia</strong>
                <small>Presencia y ausentismo</small>
            </span>
        </button>
        <button type="button" class="analytics-report-type" data-report-type="changes" aria-pressed="false">
            <span><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span>
            <span>
                <strong>Puntos de cambio</strong>
                <small>Registro y cumplimiento</small>
            </span>
        </button>
        <button type="button" class="analytics-report-type" data-report-type="certifications" aria-pressed="false">
            <span><i class="bi bi-award" aria-hidden="true"></i></span>
            <span>
                <strong>Certificaciones</strong>
                <small>Cobertura por personal</small>
            </span>
        </button>
        <button type="button" class="analytics-report-type" data-report-type="expirations" aria-pressed="false">
            <span><i class="bi bi-calendar2-x" aria-hidden="true"></i></span>
            <span>
                <strong>Vencimientos</strong>
                <small>Riesgos próximos</small>
            </span>
        </button>
    </section>

    <section class="analytics-kpi-grid analytics-kpi-grid-reports" aria-label="Resumen del reporte seleccionado">
        <article class="analytics-kpi analytics-kpi-primary">
            <div class="analytics-kpi-icon">
                <i id="reportKpiIcon1" class="bi bi-person-check" aria-hidden="true"></i>
            </div>
            <div class="analytics-kpi-copy">
                <span id="reportKpiLabel1">Asistencia promedio</span>
                <strong id="reportKpiValue1">96.4%</strong>
                <small id="reportKpiNote1">Meta: 95%</small>
            </div>
        </article>

        <article class="analytics-kpi analytics-kpi-cyan">
            <div class="analytics-kpi-icon">
                <i id="reportKpiIcon2" class="bi bi-people" aria-hidden="true"></i>
            </div>
            <div class="analytics-kpi-copy">
                <span id="reportKpiLabel2">Personal programado</span>
                <strong id="reportKpiValue2">186</strong>
                <small id="reportKpiNote2">4 líneas incluidas</small>
            </div>
        </article>

        <article class="analytics-kpi analytics-kpi-warning">
            <div class="analytics-kpi-icon">
                <i id="reportKpiIcon3" class="bi bi-person-x" aria-hidden="true"></i>
            </div>
            <div class="analytics-kpi-copy">
                <span id="reportKpiLabel3">Ausencias</span>
                <strong id="reportKpiValue3">7</strong>
                <small id="reportKpiNote3">3 justificadas</small>
            </div>
        </article>

        <article class="analytics-kpi analytics-kpi-success">
            <div class="analytics-kpi-icon">
                <i id="reportKpiIcon4" class="bi bi-graph-up-arrow" aria-hidden="true"></i>
            </div>
            <div class="analytics-kpi-copy">
                <span id="reportKpiLabel4">Variación</span>
                <strong id="reportKpiValue4">+1.8%</strong>
                <small id="reportKpiNote4">Contra el periodo anterior</small>
            </div>
        </article>
    </section>

    <section class="analytics-layout analytics-layout-main">
        <article class="analytics-panel">
            <header class="analytics-panel-header">
                <div>
                    <span class="analytics-panel-kicker" id="reportChartKicker">Comportamiento diario</span>
                    <h3 id="reportChartTitle">Tendencia de asistencia</h3>
                    <p id="reportChartDescription">Porcentaje promedio registrado durante el periodo.</p>
                </div>
                <span class="analytics-badge" id="reportScopeBadge">Todas las líneas</span>
            </header>

            <div id="reportTrendChart" class="analytics-column-chart" role="img"
                 aria-label="Gráfica del reporte seleccionado">
                <div class="analytics-chart-grid" aria-hidden="true">
                    <span>100</span>
                    <span>75</span>
                    <span>50</span>
                    <span>25</span>
                    <span>0</span>
                </div>
                <div class="analytics-columns"></div>
            </div>
        </article>

        <article class="analytics-panel">
            <header class="analytics-panel-header">
                <div>
                    <span class="analytics-panel-kicker">Distribución</span>
                    <h3 id="reportDistributionTitle">Estado de asistencia</h3>
                    <p id="reportDistributionDescription">Composición del registro seleccionado.</p>
                </div>
            </header>

            <div class="analytics-donut-block">
                <div id="reportDistributionGauge" class="analytics-donut" style="--percentage: 96;"
                     role="img" aria-label="96 por ciento de cumplimiento">
                    <div>
                        <strong id="reportGaugeValue">96%</strong>
                        <span id="reportGaugeLabel">Cumplimiento</span>
                    </div>
                </div>
                <div id="reportDistributionLegend" class="analytics-legend"></div>
            </div>
        </article>
    </section>

    <section class="analytics-layout analytics-layout-secondary">
        <article class="analytics-panel analytics-table-panel">
            <header class="analytics-panel-header">
                <div>
                    <span class="analytics-panel-kicker">Detalle simulado</span>
                    <h3 id="reportTableTitle">Resumen de asistencia por línea</h3>
                    <p id="reportTableDescription">Vista previa de los datos que contendría el reporte.</p>
                </div>
                <span class="analytics-badge analytics-badge-success">
                    <i class="bi bi-eye" aria-hidden="true"></i>
                    Vista previa
                </span>
            </header>

            <div class="analytics-table-wrap">
                <table class="analytics-table">
                    <thead id="reportTableHead"></thead>
                    <tbody id="reportTableBody"></tbody>
                </table>
            </div>
        </article>

        <article class="analytics-panel">
            <header class="analytics-panel-header">
                <div>
                    <span class="analytics-panel-kicker">Lectura rápida</span>
                    <h3>Hallazgos del periodo</h3>
                    <p>Observaciones generadas a partir de los datos de muestra.</p>
                </div>
            </header>
            <div id="reportInsights" class="analytics-insight-list"></div>
        </article>
    </section>
</div>