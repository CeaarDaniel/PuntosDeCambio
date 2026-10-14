<?php
$pageTitle = 'Reportes y gráficas | CapacitaLíder';
$sectionTitle = 'Reportes y gráficas';
$activePage = 'reportes';
$basePath = '../';
$pageVendorStyles = ['DataTables/datatables.min.css'];
$pageVendorScripts = ['DataTables/datatables.min.js'];
$pageStyle = 'css/reportes.css';
$pageScript = 'js/reportes.js';

include __DIR__ . '/../includes/app-header.php';
?>
        <div class="reports-page">
            <section class="reports-hero" aria-labelledby="reports-page-title">
                <div class="reports-hero-copy">
                    <span class="reports-eyebrow"><i class="fa-solid fa-chart-column" aria-hidden="true"></i> Inteligencia de capacitación</span>
                    <h2 id="reports-page-title">Reportes y gráficas</h2>
                    <p>Consulta el avance del personal, los resultados de evaluaciones y el impacto de la capacitación mediante indicadores fáciles de interpretar.</p>
                    <div class="reports-hero-actions">
                        <button class="button report-action-button primary-report-action" type="button" data-report-action="Actualizar datos"><i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i> Actualizar datos</button>
                        <button class="button report-action-button secondary-report-action" type="button" data-report-action="Exportar reporte"><i class="fa-solid fa-file-export" aria-hidden="true"></i> Exportar reporte</button>
                    </div>
                </div>
                <div class="reports-hero-visual" aria-hidden="true">
                    <span class="report-visual-board"><i class="fa-solid fa-chart-pie"></i></span>
                    <span class="report-visual-bar bar-one"></span><span class="report-visual-bar bar-two"></span><span class="report-visual-bar bar-three"></span>
                    <span class="report-visual-trend"><i class="fa-solid fa-arrow-trend-up"></i></span>
                </div>
            </section>

            <section class="report-filters" aria-labelledby="report-filters-title">
                <div class="report-section-heading compact-heading"><span class="report-heading-icon"><i class="fa-solid fa-sliders" aria-hidden="true"></i></span><div><h2 id="report-filters-title">Filtros del reporte</h2><p>Segmenta visualmente la información por periodo, personal o capacitación.</p></div></div>
                <form id="report-filter-form" class="report-filter-form" action="#" method="get">
                    <div class="report-filter-field report-date-field"><label>Rango de fechas</label><div class="report-date-controls"><div class="report-filter-control"><i class="fa-regular fa-calendar" aria-hidden="true"></i><input id="report-date-from" class="form-control" type="date" value="2026-01-01" aria-label="Fecha inicial"></div><span>a</span><div class="report-filter-control"><i class="fa-regular fa-calendar-check" aria-hidden="true"></i><input id="report-date-to" class="form-control" type="date" value="2026-07-16" aria-label="Fecha final"></div></div></div>
                    <div class="report-filter-field"><label for="report-area-filter">Área</label><div class="report-filter-control"><i class="fa-solid fa-building" aria-hidden="true"></i><select id="report-area-filter" class="form-select"><option value="">Todas</option><option>Producción</option><option>Calidad</option><option>Mantenimiento</option><option>Logística</option></select></div></div>
                    <div class="report-filter-field"><label for="report-position-filter">Puesto</label><div class="report-filter-control"><i class="fa-solid fa-briefcase" aria-hidden="true"></i><select id="report-position-filter" class="form-select"><option value="">Todos</option><option>Operador de producción</option><option>Inspector de calidad</option><option>Técnico de mantenimiento</option><option>Auxiliar de logística</option></select></div></div>
                    <div class="report-filter-field"><label for="report-course-filter">Curso</label><div class="report-filter-control"><i class="fa-solid fa-book-open" aria-hidden="true"></i><select id="report-course-filter" class="form-select"><option value="">Todos</option><option>Seguridad industrial básica</option><option>Metodología 5S</option><option>Fundamentos de calidad</option><option>Comunicación efectiva</option></select></div></div>
                    <div class="report-filter-field"><label for="report-supervisor-filter">Supervisor</label><div class="report-filter-control"><i class="fa-solid fa-user-tie" aria-hidden="true"></i><select id="report-supervisor-filter" class="form-select"><option value="">Todos</option><option>Roberto Cruz</option><option>Laura Gómez</option><option>Marta Silva</option><option>Andrés Navarro</option></select></div></div>
                    <div class="report-filter-field"><label for="report-status-filter">Estatus del curso</label><div class="report-filter-control"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><select id="report-status-filter" class="form-select"><option value="">Todos</option><option>Finalizado</option><option>En proceso</option><option>Pendiente</option></select></div></div>
                    <div class="report-filter-field"><label for="report-evaluation-filter">Tipo de evaluación</label><div class="report-filter-control"><i class="fa-solid fa-clipboard-question" aria-hidden="true"></i><select id="report-evaluation-filter" class="form-select"><option value="">Todos</option><option>Conocimientos</option><option>Desempeño</option></select></div></div>
                    <div class="report-filter-actions"><button id="apply-report-filters" class="button report-filter-primary" type="button"><i class="fa-solid fa-filter" aria-hidden="true"></i> Aplicar filtros</button><button class="button report-filter-clear" type="reset"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Limpiar filtros</button></div>
                </form>
            </section>

            <section class="report-kpi-grid" aria-label="Indicadores principales">
                <article class="report-kpi kpi-cyan"><span><i class="fa-solid fa-user-graduate" aria-hidden="true"></i></span><div><strong>184</strong><h3>Usuarios capacitados</h3><p>22 durante este mes</p><small class="positive-change"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i> 12% vs mes anterior</small></div></article>
                <article class="report-kpi kpi-success"><span><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span><div><strong>326</strong><h3>Cursos completados</h3><p>86% de finalización</p><small class="positive-change"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i> 8% vs mes anterior</small></div></article>
                <article class="report-kpi kpi-purple"><span><i class="fa-solid fa-clipboard-list" aria-hidden="true"></i></span><div><strong>291</strong><h3>Evaluaciones realizadas</h3><p>24 pendientes de aplicar</p><small class="positive-change"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i> 15% de participación</small></div></article>
                <article class="report-kpi kpi-blue"><span><i class="fa-solid fa-medal" aria-hidden="true"></i></span><div><strong>87%</strong><h3>Promedio de evaluación</h3><p>Meta establecida: 80%</p><small class="positive-change"><i class="fa-solid fa-check" aria-hidden="true"></i> 7 puntos sobre meta</small></div></article>
                <article class="report-kpi kpi-cyan"><span><i class="fa-solid fa-chart-line" aria-hidden="true"></i></span><div><strong>78%</strong><h3>Avance del personal</h3><p>Promedio general</p><small class="positive-change"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i> 6% vs mes anterior</small></div></article>
                <article class="report-kpi kpi-success"><span><i class="fa-solid fa-bullseye" aria-hidden="true"></i></span><div><strong>84%</strong><h3>Efectividad promedio</h3><p>Conocimiento y desempeño</p><small class="positive-change"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i> Impacto favorable</small></div></article>
                <article class="report-kpi kpi-orange"><span><i class="fa-solid fa-user-clock" aria-hidden="true"></i></span><div><strong>38</strong><h3>Personal con pendientes</h3><p>18 pertenecen a Producción</p><small class="warning-change"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> Requiere seguimiento</small></div></article>
                <article class="report-kpi kpi-danger"><span><i class="fa-solid fa-arrow-trend-down" aria-hidden="true"></i></span><div><strong>2</strong><h3>Cursos con bajo rendimiento</h3><p>Promedio inferior a 75%</p><small class="danger-change"><i class="fa-solid fa-eye" aria-hidden="true"></i> Revisar contenido</small></div></article>
            </section>

            <section class="report-chart-section" aria-labelledby="main-charts-title">
                <div class="report-section-heading"><div><span class="report-section-label">Visualización general</span><h2 id="main-charts-title">Gráficas principales</h2><p>Resumen del avance, participación y resultados del personal.</p></div><span class="report-period-badge"><i class="fa-regular fa-calendar" aria-hidden="true"></i> Enero – Julio 2026</span></div>
                <div class="primary-chart-grid">
                    <article class="report-chart-card area-progress-chart">
                        <header class="chart-card-heading"><div><span>Avance de usuarios</span><h3>Promedio por área</h3></div><span class="chart-summary positive-summary">78% general</span></header>
                        <div class="horizontal-chart" role="img" aria-label="Producción 72%, Calidad 91%, Mantenimiento 68% y Logística 81%">
                            <div class="horizontal-chart-row"><span>Producción</span><div><i style="width:72%"></i></div><strong>72%</strong></div>
                            <div class="horizontal-chart-row"><span>Calidad</span><div><i style="width:91%"></i></div><strong>91%</strong></div>
                            <div class="horizontal-chart-row"><span>Mantenimiento</span><div><i style="width:68%"></i></div><strong>68%</strong></div>
                            <div class="horizontal-chart-row"><span>Logística</span><div><i style="width:81%"></i></div><strong>81%</strong></div>
                        </div>
                    </article>

                    <article class="report-chart-card evaluation-results-chart">
                        <header class="chart-card-heading"><div><span>Resultados de evaluaciones</span><h3>Estado de aplicación</h3></div><span class="chart-summary purple-summary">87% promedio</span></header>
                        <div class="donut-chart-layout">
                            <div class="report-donut" role="img" aria-label="72% aprobados, 9% no aprobados y 19% pendientes"><div><strong>291</strong><small>evaluaciones</small></div></div>
                            <ul class="chart-legend"><li><span class="legend-success"></span><div><strong>72%</strong><small>Aprobados</small></div></li><li><span class="legend-danger"></span><div><strong>9%</strong><small>No aprobados</small></div></li><li><span class="legend-orange"></span><div><strong>19%</strong><small>Pendientes</small></div></li></ul>
                        </div>
                    </article>

                    <article class="report-chart-card participation-chart">
                        <header class="chart-card-heading"><div><span>Participación en cursos</span><h3>Actividad mensual</h3></div><ul class="mini-legend"><li><i class="series-started"></i>Iniciados</li><li><i class="series-process"></i>En proceso</li><li><i class="series-finished"></i>Finalizados</li></ul></header>
                        <div class="grouped-bar-chart" role="img" aria-label="Participación mensual de enero a julio">
                            <div class="chart-axis-labels"><span>60</span><span>40</span><span>20</span><span>0</span></div>
                            <div class="grouped-bars"><div><span style="height:48%"></span><i style="height:35%"></i><b style="height:42%"></b><small>Ene</small></div><div><span style="height:62%"></span><i style="height:44%"></i><b style="height:51%"></b><small>Feb</small></div><div><span style="height:55%"></span><i style="height:49%"></i><b style="height:62%"></b><small>Mar</small></div><div><span style="height:73%"></span><i style="height:52%"></i><b style="height:68%"></b><small>Abr</small></div><div><span style="height:68%"></span><i style="height:59%"></i><b style="height:75%"></b><small>May</small></div><div><span style="height:82%"></span><i style="height:65%"></i><b style="height:79%"></b><small>Jun</small></div><div><span style="height:76%"></span><i style="height:61%"></i><b style="height:88%"></b><small>Jul</small></div></div>
                        </div>
                    </article>

                    <article class="report-chart-card performance-chart">
                        <header class="chart-card-heading"><div><span>Indicadores de desempeño</span><h3>Impacto posterior al curso</h3></div><span class="chart-summary orange-summary">Seguimiento operativo</span></header>
                        <div class="performance-bars" role="img" aria-label="Aplicación del conocimiento 86%, productividad 79%, calidad 88%, seguridad 92% y habilidades blandas 74%">
                            <div><header><span>Aplicación del conocimiento</span><strong>86%</strong></header><p><i style="width:86%"></i></p></div>
                            <div><header><span>Productividad</span><strong>79%</strong></header><p><i style="width:79%"></i></p></div>
                            <div><header><span>Calidad</span><strong>88%</strong></header><p><i style="width:88%"></i></p></div>
                            <div><header><span>Seguridad</span><strong>92%</strong></header><p><i style="width:92%"></i></p></div>
                            <div><header><span>Habilidades blandas</span><strong>74%</strong></header><p><i style="width:74%"></i></p></div>
                        </div>
                    </article>
                </div>
            </section>

            <section class="effectiveness-section" aria-labelledby="effectiveness-title">
                <div class="report-section-heading"><div><span class="report-section-label">Impacto de la capacitación</span><h2 id="effectiveness-title">Métricas de efectividad</h2><p>Compara el aprendizaje obtenido con la aplicación posterior en el puesto.</p></div><span class="effectiveness-main-score"><strong>84%</strong> efectividad general</span></div>
                <div class="effectiveness-layout">
                    <article class="effectiveness-formula-card"><span class="formula-icon"><i class="fa-solid fa-calculator" aria-hidden="true"></i></span><div><small>Fórmula visual simulada</small><h3>Efectividad de capacitación</h3><div class="formula-steps"><span><i class="fa-solid fa-chart-line"></i> Avance del curso</span><b>+</b><span><i class="fa-solid fa-medal"></i> Evaluación</span><b>+</b><span><i class="fa-solid fa-briefcase"></i> Desempeño posterior</span></div><p>La lectura combina finalización, conocimiento y cambios observados en el área de trabajo.</p></div></article>
                    <div class="effectiveness-metrics-grid"><article><strong>89%</strong><span>Aprobación</span><small>+5% mensual</small></article><article><strong>87%</strong><span>Calificación</span><small>Promedio general</small></article><article><strong>86%</strong><span>Finalización</span><small>326 cursos</small></article><article><strong>82%</strong><span>Aplicación</span><small>En el puesto</small></article><article><strong>79%</strong><span>Mejora observada</span><small>Desempeño</small></article><article><strong>85%</strong><span>Cumplimiento</span><small>Posterior al curso</small></article></div>
                </div>
                <article class="course-impact-card">
                    <header class="chart-card-heading"><div><span>Efectividad por curso</span><h3>Conocimiento frente a desempeño posterior</h3></div><span class="chart-summary positive-summary">Mayor impacto: Seguridad</span></header>
                    <div class="course-impact-list">
                        <div><header><strong>Seguridad industrial básica</strong><span>92% efectividad</span></header><div class="impact-comparison"><span><i style="width:94%"></i><small>Conocimiento 94%</small></span><span><i style="width:90%"></i><small>Desempeño 90%</small></span></div></div>
                        <div><header><strong>Metodología 5S</strong><span>86% efectividad</span></header><div class="impact-comparison"><span><i style="width:89%"></i><small>Conocimiento 89%</small></span><span><i style="width:83%"></i><small>Desempeño 83%</small></span></div></div>
                        <div><header><strong>Comunicación efectiva</strong><span>81% efectividad</span></header><div class="impact-comparison"><span><i style="width:85%"></i><small>Conocimiento 85%</small></span><span><i style="width:77%"></i><small>Desempeño 77%</small></span></div></div>
                        <div class="low-impact"><header><strong>Fundamentos de calidad</strong><span>71% efectividad</span></header><div class="impact-comparison"><span><i style="width:74%"></i><small>Conocimiento 74%</small></span><span><i style="width:68%"></i><small>Desempeño 68%</small></span></div></div>
                    </div>
                </article>
            </section>

            <section class="report-insights-section" aria-labelledby="report-insights-title">
                <div class="report-section-heading"><div><span class="report-section-label">Alertas e insights</span><h2 id="report-insights-title">Hallazgos que requieren atención</h2><p>Recomendaciones simuladas generadas a partir de los indicadores visibles.</p></div></div>
                <div class="report-insights-grid">
                    <article class="insight-success"><span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></span><div><strong>Alto nivel de aprobación</strong><p>Seguridad Industrial alcanzó 94% de aprobación y 92% de efectividad.</p></div></article>
                    <article class="insight-warning"><span><i class="fa-solid fa-users" aria-hidden="true"></i></span><div><strong>Pendientes en Producción</strong><p>18 usuarios del área aún tienen uno o más cursos pendientes.</p></div></article>
                    <article class="insight-danger"><span><i class="fa-solid fa-magnifying-glass-chart" aria-hidden="true"></i></span><div><strong>Contenido por revisar</strong><p>Fundamentos de Calidad presenta un promedio inferior a la meta establecida.</p></div></article>
                    <article class="insight-purple"><span><i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i></span><div><strong>Mejor desempeño posterior</strong><p>El personal con capacitación completa muestra 14% más autonomía operativa.</p></div></article>
                </div>
            </section>

            <section class="report-tables-section" aria-labelledby="report-table-title">
                <div class="report-section-heading report-table-heading"><div><span class="report-section-label">Detalle de información</span><h2 id="report-table-title">Reportes consultables</h2><p>Alterna la vista para analizar trabajadores, cursos o áreas.</p></div><div class="report-output-actions"><button class="report-output-button" type="button" data-report-action="Ver detalle"><i class="fa-regular fa-eye" aria-hidden="true"></i> Ver detalle</button><button class="report-output-button" type="button" data-report-action="Imprimir"><i class="fa-solid fa-print" aria-hidden="true"></i> Imprimir</button><button class="report-output-button" type="button" data-report-action="Descargar PDF"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i> PDF</button><button class="report-output-button" type="button" data-report-action="Descargar Excel"><i class="fa-solid fa-file-excel" aria-hidden="true"></i> Excel</button></div></div>
                <div class="report-tabs" role="tablist" aria-label="Tipo de reporte"><button class="report-tab is-active" type="button" role="tab" aria-selected="true" aria-controls="worker-report-panel" data-report-tab="worker"><i class="fa-solid fa-users" aria-hidden="true"></i> Por trabajador</button><button class="report-tab" type="button" role="tab" aria-selected="false" aria-controls="course-report-panel" data-report-tab="course"><i class="fa-solid fa-book-open" aria-hidden="true"></i> Por curso</button><button class="report-tab" type="button" role="tab" aria-selected="false" aria-controls="area-report-panel" data-report-tab="area"><i class="fa-solid fa-building" aria-hidden="true"></i> Por área</button></div>

                <div id="worker-report-panel" class="report-table-panel is-active" role="tabpanel" data-report-panel="worker">
                    <table id="worker-report-table" class="table align-middle report-data-table"><caption class="visually-hidden">Reporte de capacitación por trabajador</caption><thead><tr><th>No. empleado</th><th>Nombre</th><th>Puesto</th><th>Área</th><th>Asignados</th><th>Finalizados</th><th>Avance</th><th>Evaluación</th><th>Desempeño</th><th>Estado</th></tr></thead><tbody>
                        <tr><td>EMP014</td><td><strong>Ana Martínez López</strong></td><td>Operador de producción</td><td>Producción</td><td>4</td><td>2</td><td><span class="table-score score-progress">50%</span></td><td>86%</td><td>82%</td><td><span class="report-status status-process">En proceso</span></td></tr>
                        <tr><td>EMP001</td><td><strong>Carlos Mendoza Ruiz</strong></td><td>Líder de línea</td><td>Producción</td><td>5</td><td>5</td><td><span class="table-score score-high">100%</span></td><td>93%</td><td>91%</td><td><span class="report-status status-complete">Completado</span></td></tr>
                        <tr><td>EMP027</td><td><strong>Luis Hernández Vega</strong></td><td>Técnico de mantenimiento</td><td>Mantenimiento</td><td>3</td><td>0</td><td><span class="table-score score-low">0%</span></td><td>Pendiente</td><td>Pendiente</td><td><span class="report-status status-pending">Pendiente</span></td></tr>
                        <tr><td>EMP039</td><td><strong>Fernanda Ruiz Soto</strong></td><td>Inspector de calidad</td><td>Calidad</td><td>4</td><td>4</td><td><span class="table-score score-high">100%</span></td><td>95%</td><td>90%</td><td><span class="report-status status-complete">Completado</span></td></tr>
                        <tr><td>EMP052</td><td><strong>Jorge Castillo Mora</strong></td><td>Operador de ensamble</td><td>Producción</td><td>2</td><td>1</td><td><span class="table-score score-progress">50%</span></td><td>78%</td><td>74%</td><td><span class="report-status status-followup">Seguimiento</span></td></tr>
                        <tr><td>EMP061</td><td><strong>Daniela Flores Reyes</strong></td><td>Auxiliar de logística</td><td>Logística</td><td>3</td><td>2</td><td><span class="table-score score-progress">67%</span></td><td>88%</td><td>84%</td><td><span class="report-status status-process">En proceso</span></td></tr>
                    </tbody></table>
                </div>

                <div id="course-report-panel" class="report-table-panel" role="tabpanel" data-report-panel="course" hidden>
                    <table id="course-report-table" class="table align-middle report-data-table"><caption class="visually-hidden">Reporte general por curso</caption><thead><tr><th>Curso</th><th>Clasificación</th><th>Participantes</th><th>Finalizados</th><th>Evaluación</th><th>Efectividad</th><th>Impacto</th><th>Estado</th></tr></thead><tbody>
                        <tr><td><strong>Seguridad industrial básica</strong></td><td>Seguridad</td><td>68</td><td>62</td><td>94%</td><td>92%</td><td>Alto</td><td><span class="report-status status-complete">Óptimo</span></td></tr>
                        <tr><td><strong>Metodología 5S</strong></td><td>Mejora continua</td><td>54</td><td>47</td><td>89%</td><td>86%</td><td>Alto</td><td><span class="report-status status-complete">Óptimo</span></td></tr>
                        <tr><td><strong>Comunicación efectiva</strong></td><td>Habilidades blandas</td><td>42</td><td>34</td><td>85%</td><td>81%</td><td>Medio</td><td><span class="report-status status-process">Estable</span></td></tr>
                        <tr><td><strong>Fundamentos de calidad</strong></td><td>Calidad</td><td>49</td><td>36</td><td>74%</td><td>71%</td><td>Bajo</td><td><span class="report-status status-followup">Revisar</span></td></tr>
                        <tr><td><strong>Manejo seguro de materiales</strong></td><td>Seguridad</td><td>37</td><td>29</td><td>87%</td><td>84%</td><td>Alto</td><td><span class="report-status status-process">Estable</span></td></tr>
                    </tbody></table>
                </div>

                <div id="area-report-panel" class="report-table-panel" role="tabpanel" data-report-panel="area" hidden>
                    <table id="area-report-table" class="table align-middle report-data-table"><caption class="visually-hidden">Reporte de capacitación por área</caption><thead><tr><th>Área</th><th>Personal</th><th>Capacitados</th><th>Pendientes</th><th>Avance</th><th>Evaluación</th><th>Desempeño</th><th>Efectividad</th></tr></thead><tbody>
                        <tr><td><strong>Producción</strong></td><td>86</td><td>68</td><td>18</td><td>72%</td><td>84%</td><td>78%</td><td><span class="table-score score-progress">81%</span></td></tr>
                        <tr><td><strong>Calidad</strong></td><td>34</td><td>32</td><td>2</td><td>91%</td><td>93%</td><td>90%</td><td><span class="table-score score-high">91%</span></td></tr>
                        <tr><td><strong>Mantenimiento</strong></td><td>29</td><td>21</td><td>8</td><td>68%</td><td>81%</td><td>76%</td><td><span class="table-score score-progress">75%</span></td></tr>
                        <tr><td><strong>Logística</strong></td><td>35</td><td>31</td><td>4</td><td>81%</td><td>88%</td><td>84%</td><td><span class="table-score score-high">85%</span></td></tr>
                    </tbody></table>
                </div>
            </section>
        </div>

        <div class="toast-container position-fixed bottom-0 end-0 p-3"><div id="report-feedback-toast" class="toast report-feedback-toast" role="status" aria-live="polite" aria-atomic="true"><div class="toast-body"><span><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span><p id="report-feedback-message">Acción simulada correctamente.</p><button class="btn-close" type="button" data-bs-dismiss="toast" aria-label="Cerrar"></button></div></div></div>
<?php include __DIR__ . '/../includes/app-footer.php'; ?>
