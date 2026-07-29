<link rel="stylesheet" href="./css/analytics.css">

<div id="dashboard" class="analytics-view dashboard-view">
    <section class="analytics-hero" aria-labelledby="dashboardHeading">
        <div class="analytics-hero-copy">
            <span class="analytics-eyebrow">
                <i class="bi bi-speedometer2" aria-hidden="true"></i>
                Vista operativa
            </span>
            <h2 id="dashboardHeading">Pulso general de la operación</h2>
            <p>Consulta el estado de las líneas, la asistencia y la cobertura de certificaciones en un solo lugar.</p>
        </div>

        <div class="analytics-update">
            <span class="analytics-status-dot" aria-hidden="true"></span>
            <span>
                <small>Datos simulados</small>
                <strong id="dashboardUpdated">Actualizado ahora</strong>
            </span>
        </div>
    </section>

    <form id="dashboardFilters" class="analytics-filters" aria-label="Filtros del dashboard">
        <div class="analytics-filter-intro">
            <span class="analytics-filter-icon">
                <i class="bi bi-funnel" aria-hidden="true"></i>
            </span>
            <span>
                <strong>Filtrar información</strong>
                <small>Los indicadores se actualizan de forma simulada.</small>
            </span>
        </div>

        <div class="analytics-filter-field">
            <label for="dashboardPeriod">Periodo</label>
            <select id="dashboardPeriod" class="form-select">
                <option value="today">Hoy</option>
                <option value="week" selected>Últimos 7 días</option>
                <option value="month">Últimos 30 días</option>
            </select>
        </div>

        <div class="analytics-filter-field">
            <label for="dashboardLine">Línea</label>
            <select id="dashboardLine" class="form-select">
                <option value="all">Todas las líneas</option>
                <option value="A01">Línea A01</option>
                <option value="A02">Línea A02</option>
                <option value="B01">Línea B01</option>
                <option value="B02">Línea B02</option>
            </select>
        </div>

        <button type="button" id="dashboardReset" class="analytics-btn analytics-btn-light">
            <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
            Restablecer
        </button>
    </form>

    <section class="analytics-kpi-grid" aria-label="Indicadores principales">
        <article class="analytics-kpi analytics-kpi-primary">
            <div class="analytics-kpi-icon">
                <i class="bi bi-people" aria-hidden="true"></i>
            </div>
            <div class="analytics-kpi-copy">
                <span>Personal en operación</span>
                <strong id="dashboardStaff">186</strong>
                <small id="dashboardStaffNote">En 4 líneas activas</small>
            </div>
            <span class="analytics-trend is-positive">
                <i class="bi bi-arrow-up-short" aria-hidden="true"></i> 4.2%
            </span>
        </article>

        <article class="analytics-kpi analytics-kpi-cyan">
            <div class="analytics-kpi-icon">
                <i class="bi bi-person-check" aria-hidden="true"></i>
            </div>
            <div class="analytics-kpi-copy">
                <span>Asistencia promedio</span>
                <strong id="dashboardAttendance">96.4%</strong>
                <small id="dashboardAttendanceNote">Meta operativa: 95%</small>
            </div>
            <span class="analytics-trend is-positive">
                <i class="bi bi-check2" aria-hidden="true"></i> En meta
            </span>
        </article>

        <article class="analytics-kpi analytics-kpi-success">
            <div class="analytics-kpi-icon">
                <i class="bi bi-award" aria-hidden="true"></i>
            </div>
            <div class="analytics-kpi-copy">
                <span>Cobertura certificada</span>
                <strong id="dashboardCoverage">87%</strong>
                <small id="dashboardCoverageNote">162 de 186 personas</small>
            </div>
            <span class="analytics-trend is-positive">
                <i class="bi bi-arrow-up-short" aria-hidden="true"></i> 2.1%
            </span>
        </article>

        <article class="analytics-kpi analytics-kpi-warning">
            <div class="analytics-kpi-icon">
                <i class="bi bi-arrow-left-right" aria-hidden="true"></i>
            </div>
            <div class="analytics-kpi-copy">
                <span>Puntos de cambio</span>
                <strong id="dashboardChanges">24</strong>
                <small id="dashboardChangesNote">21 cerrados, 3 activos</small>
            </div>
            <span class="analytics-trend is-neutral">
                <i class="bi bi-clock" aria-hidden="true"></i> 3 activos
            </span>
        </article>

        <article class="analytics-kpi analytics-kpi-danger">
            <div class="analytics-kpi-icon">
                <i class="bi bi-calendar2-x" aria-hidden="true"></i>
            </div>
            <div class="analytics-kpi-copy">
                <span>Próximas a vencer</span>
                <strong id="dashboardExpiring">8</strong>
                <small id="dashboardExpiringNote">Durante los próximos 30 días</small>
            </div>
            <span class="analytics-trend is-warning">
                <i class="bi bi-exclamation-circle" aria-hidden="true"></i> Atención
            </span>
        </article>
    </section>

    <section class="analytics-layout analytics-layout-main">
        <article class="analytics-panel">
            <header class="analytics-panel-header">
                <div>
                    <span class="analytics-panel-kicker">Seguimiento diario</span>
                    <h3 id="dashboardChartTitle">Asistencia de la semana</h3>
                    <p id="dashboardChartDescription">Porcentaje de personal presente respecto a la dotación programada.</p>
                </div>
                <span class="analytics-badge analytics-badge-success">
                    <i class="bi bi-bullseye" aria-hidden="true"></i>
                    Meta 95%
                </span>
            </header>

            <div id="dashboardAttendanceChart" class="analytics-column-chart" role="img"
                 aria-label="Gráfica de asistencia de los últimos siete días">
                <div class="analytics-chart-grid" aria-hidden="true">
                    <span>100%</span>
                    <span>75%</span>
                    <span>50%</span>
                    <span>25%</span>
                    <span>0%</span>
                </div>
                <div class="analytics-columns"></div>
            </div>
        </article>

        <article class="analytics-panel">
            <header class="analytics-panel-header">
                <div>
                    <span class="analytics-panel-kicker">Competencias</span>
                    <h3>Cobertura de certificación</h3>
                    <p>Estado del personal asignado a estaciones.</p>
                </div>
            </header>

            <div class="analytics-donut-block">
                <div id="dashboardCoverageGauge" class="analytics-donut" style="--percentage: 87;"
                     role="img" aria-label="87 por ciento de cobertura certificada">
                    <div>
                        <strong id="dashboardGaugeValue">87%</strong>
                        <span>Cobertura</span>
                    </div>
                </div>

                <div class="analytics-legend" id="dashboardCoverageLegend">
                    <div>
                        <span class="analytics-legend-dot is-primary"></span>
                        <span>Vigentes</span>
                        <strong id="dashboardCertified">162</strong>
                    </div>
                    <div>
                        <span class="analytics-legend-dot is-warning"></span>
                        <span>Por vencer</span>
                        <strong id="dashboardDue">8</strong>
                    </div>
                    <div>
                        <span class="analytics-legend-dot is-muted"></span>
                        <span>Brecha actual</span>
                        <strong id="dashboardGap">24</strong>
                    </div>
                </div>
            </div>
        </article>
    </section>

    <section class="analytics-layout analytics-layout-secondary">
        <article class="analytics-panel">
            <header class="analytics-panel-header">
                <div>
                    <span class="analytics-panel-kicker">Comparativo</span>
                    <h3>Desempeño por línea</h3>
                    <p>Índice combinado de asistencia y cobertura de certificación.</p>
                </div>
                <span class="analytics-badge">Escala 0-100</span>
            </header>

            <div id="dashboardLinePerformance" class="analytics-progress-list"></div>
        </article>

        <article class="analytics-panel">
            <header class="analytics-panel-header">
                <div>
                    <span class="analytics-panel-kicker">Atención requerida</span>
                    <h3>Alertas prioritarias</h3>
                    <p>Situaciones simuladas que requieren seguimiento.</p>
                </div>
                <span class="analytics-badge analytics-badge-warning" id="dashboardAlertCount">3 pendientes</span>
            </header>

            <div id="dashboardAlerts" class="analytics-alert-list"></div>
        </article>
    </section>

    <section class="analytics-panel analytics-table-panel">
        <header class="analytics-panel-header">
            <div>
                <span class="analytics-panel-kicker">Resumen por línea</span>
                <h3>Estado operativo actual</h3>
                <p>Vista consolidada para detectar rápidamente desviaciones.</p>
            </div>
            <span class="analytics-badge">
                <i class="bi bi-info-circle" aria-hidden="true"></i>
                Información simulada
            </span>
        </header>

        <div class="analytics-table-wrap">
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th scope="col">Línea</th>
                        <th scope="col">Personal</th>
                        <th scope="col">Asistencia</th>
                        <th scope="col">Puntos de cambio</th>
                        <th scope="col">Cobertura</th>
                        <th scope="col">Estado</th>
                    </tr>
                </thead>
                <tbody id="dashboardLineTable"></tbody>
            </table>
        </div>
    </section>
</div>
