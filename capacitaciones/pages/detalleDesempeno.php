<?php
$pageTitle = 'Detalle de desempeño | CapacitaLíder';
$sectionTitle = 'Detalle de desempeño';
$activePage = 'evaluacionDesempeno';
$basePath = '../';
$pageStyle = 'css/detalleDesempeno.css';
$pageScript = 'js/detalleDesempeno.js';

include __DIR__ . '/../includes/app-header.php';
?>
        <div class="performance-detail-page">
            <a class="performance-detail-back" href="evaluacionDesempeno.php">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Volver a trabajadores</span>
            </a>

            <section class="performance-detail-hero" aria-labelledby="performance-detail-title">
                <div class="performance-detail-hero-copy">
                    <span class="performance-detail-eyebrow">
                        <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
                        Seguimiento posterior a la capacitación
                    </span>
                    <h2 id="performance-detail-title">Detalle de desempeño</h2>
                    <p class="performance-detail-subtitle">Evalúa el impacto de la capacitación en el desempeño operativo</p>
                    <div class="performance-detail-worker">
                        <span class="hero-worker-avatar" aria-hidden="true">AM</span>
                        <div>
                            <strong>Ana Martínez López</strong>
                            <span>Operadora de producción · Área de Producción</span>
                        </div>
                    </div>
                </div>
                <a class="button performance-hero-back-button" href="evaluacionDesempeno.php">
                    <i class="fa-solid fa-users" aria-hidden="true"></i>
                    Volver a la lista
                </a>
            </section>

            <div class="worker-training-overview">
                <article class="worker-profile-card" aria-labelledby="worker-profile-title">
                    <div class="worker-profile-heading">
                        <span class="worker-profile-avatar" role="img" aria-label="Avatar de Ana Martínez López">AM</span>
                        <div>
                            <span class="worker-profile-label">Información general</span>
                            <h2 id="worker-profile-title">Ana Martínez López</h2>
                            <p>EMP014 · Operadora de producción</p>
                        </div>
                        <span class="training-status-badge">
                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                            Capacitación completada
                        </span>
                    </div>

                    <dl class="worker-profile-details">
                        <div>
                            <dt><i class="fa-solid fa-id-card" aria-hidden="true"></i> No. de empleado</dt>
                            <dd>EMP014</dd>
                        </div>
                        <div>
                            <dt><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Puesto</dt>
                            <dd>Operadora de producción</dd>
                        </div>
                        <div>
                            <dt><i class="fa-solid fa-building" aria-hidden="true"></i> Área</dt>
                            <dd>Producción</dd>
                        </div>
                        <div>
                            <dt><i class="fa-solid fa-user-tie" aria-hidden="true"></i> Supervisor</dt>
                            <dd>Roberto Cruz</dd>
                        </div>
                        <div>
                            <dt><i class="fa-regular fa-clock" aria-hidden="true"></i> Turno</dt>
                            <dd>Matutino · 06:00 a 14:00</dd>
                        </div>
                        <div>
                            <dt><i class="fa-regular fa-calendar" aria-hidden="true"></i> Fecha de ingreso</dt>
                            <dd>18 de marzo de 2022</dd>
                        </div>
                    </dl>
                </article>

                <section class="training-statistics" aria-labelledby="training-statistics-title">
                    <div class="training-statistics-heading">
                        <div>
                            <span>Historial de aprendizaje</span>
                            <h2 id="training-statistics-title">Estadísticas de capacitación</h2>
                        </div>
                        <span class="statistics-period"><i class="fa-regular fa-calendar" aria-hidden="true"></i> 2026</span>
                    </div>

                    <div class="training-metric-grid">
                        <article class="training-metric metric-completed">
                            <span class="training-metric-icon"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
                            <div><strong>8</strong><span>Cursos finalizados</span></div>
                        </article>
                        <article class="training-metric metric-progress">
                            <span class="training-metric-icon"><i class="fa-solid fa-circle-play" aria-hidden="true"></i></span>
                            <div><strong>2</strong><span>Cursos en proceso</span></div>
                        </article>
                        <article class="training-metric metric-pending">
                            <span class="training-metric-icon"><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i></span>
                            <div><strong>1</strong><span>Curso pendiente</span></div>
                        </article>
                        <article class="training-metric metric-advance">
                            <span class="training-metric-icon"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i></span>
                            <div><strong>82%</strong><span>Avance general</span></div>
                            <div class="metric-progress-bar" aria-hidden="true"><span style="width: 82%"></span></div>
                        </article>
                    </div>

                    <div class="training-history-strip">
                        <div>
                            <span>Último curso completado</span>
                            <strong>Seguridad industrial básica</strong>
                        </div>
                        <div>
                            <span>Última evaluación</span>
                            <strong>16 de junio de 2026</strong>
                        </div>
                        <div>
                            <span>Promedio de conocimientos</span>
                            <strong class="knowledge-average">91%</strong>
                        </div>
                    </div>
                </section>
            </div>

            <section class="evaluated-course-card" aria-labelledby="evaluated-course-title">
                <div class="evaluated-course-heading">
                    <span class="evaluated-course-icon"><i class="fa-solid fa-helmet-safety" aria-hidden="true"></i></span>
                    <div>
                        <span>Curso relacionado con el seguimiento</span>
                        <h2 id="evaluated-course-title">Seguridad industrial básica</h2>
                    </div>
                    <span class="course-followup-status">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                        Aprobado · Pendiente de seguimiento
                    </span>
                </div>

                <div class="evaluated-course-content">
                    <div class="evaluated-course-main">
                        <div class="evaluated-course-meta">
                            <span><i class="fa-regular fa-calendar-check" aria-hidden="true"></i> Finalizado: 12 de junio de 2026</span>
                            <span><i class="fa-solid fa-award" aria-hidden="true"></i> Calificación: <strong>92%</strong></span>
                        </div>
                        <div class="course-objective">
                            <span>Objetivo principal</span>
                            <p>Aplicar medidas de prevención, identificar riesgos y ejecutar las actividades operativas de forma segura.</p>
                        </div>
                    </div>
                    <div class="expected-competencies">
                        <span>Competencias esperadas</span>
                        <div>
                            <span><i class="fa-solid fa-check" aria-hidden="true"></i> Prevención de riesgos</span>
                            <span><i class="fa-solid fa-check" aria-hidden="true"></i> Apego a procedimientos</span>
                            <span><i class="fa-solid fa-check" aria-hidden="true"></i> Trabajo seguro</span>
                            <span><i class="fa-solid fa-check" aria-hidden="true"></i> Comunicación de incidentes</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="operational-evaluation" aria-labelledby="operational-evaluation-title">
                <div id="completed-course-selection" class="completed-course-selection is-active">
                    <div class="completed-course-selection-heading">
                        <div>
                            <span class="evaluation-step-label">Evaluación posterior al curso</span>
                            <h2 id="operational-evaluation-title">Selecciona un curso finalizado</h2>
                            <p>Elige la capacitación cuyo impacto deseas evaluar en el desempeño operativo de Ana Martínez.</p>
                        </div>
                        <span class="completed-courses-total"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> 4 cursos disponibles</span>
                    </div>

                    <div class="completed-course-options" aria-label="Cursos finalizados de Ana Martínez">
                        <article class="completed-course-option" role="button" tabindex="0" data-performance-course="Seguridad industrial básica" aria-label="Evaluar desempeño posterior al curso Seguridad industrial básica">
                            <span class="completed-course-option-icon course-option-cyan"><i class="fa-solid fa-helmet-safety" aria-hidden="true"></i></span>
                            <div class="completed-course-option-copy">
                                <span>Seguridad y prevención</span>
                                <h3>Seguridad industrial básica</h3>
                                <p>Procedimientos esenciales para identificar riesgos y trabajar de forma segura.</p>
                            </div>
                            <div class="completed-course-option-meta">
                                <span><i class="fa-regular fa-calendar-check" aria-hidden="true"></i> Finalizado: 18 jun. 2026</span>
                                <strong>92% <small>calificación</small></strong>
                            </div>
                            <span class="completed-course-option-state"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Finalizado</span>
                        </article>

                        <article class="completed-course-option" role="button" tabindex="0" data-performance-course="Fundamentos de calidad" aria-label="Evaluar desempeño posterior al curso Fundamentos de calidad">
                            <span class="completed-course-option-icon course-option-purple"><i class="fa-solid fa-medal" aria-hidden="true"></i></span>
                            <div class="completed-course-option-copy">
                                <span>Calidad</span>
                                <h3>Fundamentos de calidad</h3>
                                <p>Buenas prácticas para prevenir defectos y mantener los estándares del proceso.</p>
                            </div>
                            <div class="completed-course-option-meta">
                                <span><i class="fa-regular fa-calendar-check" aria-hidden="true"></i> Finalizado: 03 jun. 2026</span>
                                <strong>88% <small>calificación</small></strong>
                            </div>
                            <span class="completed-course-option-state"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Finalizado</span>
                        </article>

                        <article class="completed-course-option" role="button" tabindex="0" data-performance-course="Metodología 5S en el área de trabajo" aria-label="Evaluar desempeño posterior al curso Metodología 5S en el área de trabajo">
                            <span class="completed-course-option-icon course-option-orange"><i class="fa-solid fa-broom" aria-hidden="true"></i></span>
                            <div class="completed-course-option-copy">
                                <span>Mejora continua</span>
                                <h3>Metodología 5S en el área de trabajo</h3>
                                <p>Orden, limpieza y disciplina para mejorar la productividad cotidiana.</p>
                            </div>
                            <div class="completed-course-option-meta">
                                <span><i class="fa-regular fa-calendar-check" aria-hidden="true"></i> Finalizado: 14 may. 2026</span>
                                <strong>95% <small>calificación</small></strong>
                            </div>
                            <span class="completed-course-option-state"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Finalizado</span>
                        </article>

                        <article class="completed-course-option" role="button" tabindex="0" data-performance-course="Comunicación efectiva en planta" aria-label="Evaluar desempeño posterior al curso Comunicación efectiva en planta">
                            <span class="completed-course-option-icon course-option-pink"><i class="fa-solid fa-comments" aria-hidden="true"></i></span>
                            <div class="completed-course-option-copy">
                                <span>Habilidades blandas</span>
                                <h3>Comunicación efectiva en planta</h3>
                                <p>Herramientas prácticas para compartir indicaciones y reportar incidencias.</p>
                            </div>
                            <div class="completed-course-option-meta">
                                <span><i class="fa-regular fa-calendar-check" aria-hidden="true"></i> Finalizado: 26 abr. 2026</span>
                                <strong>90% <small>calificación</small></strong>
                            </div>
                            <span class="completed-course-option-state"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Finalizado</span>
                        </article>
                    </div>
                    <p class="course-selection-help"><i class="fa-solid fa-hand-pointer" aria-hidden="true"></i> Presiona cualquier tarjeta para comenzar la evaluación posterior al curso.</p>
                </div>

                <div id="operational-evaluation-form-view" class="operational-evaluation-form-view" hidden>
                <div class="back-to-course-selection-sticky">
                    <button id="back-to-completed-courses" class="back-to-course-selection" type="button">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Volver a cursos finalizados
                    </button>
                </div>
                <div class="operational-evaluation-heading">
                    <div>
                        <span class="evaluation-step-label">Evaluación posterior al curso</span>
                        <h2 id="operational-evaluation-form-title">Desempeño operativo</h2>
                        <p>Califica únicamente cambios que hayas observado en las actividades habituales del colaborador.</p>
                        <span class="responsible-evaluator"><i class="fa-solid fa-user-tie" aria-hidden="true"></i> Evaluador responsable: Roberto Cruz · Supervisor de área</span>
                    </div>
                    <div class="evaluation-completion">
                        <div>
                            <span id="evaluation-progress-text">0 de 19 criterios respondidos</span>
                            <strong id="evaluation-progress-percent">0%</strong>
                        </div>
                        <div id="evaluation-progress-bar" class="progress" role="progressbar" aria-label="Avance de la evaluación" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <div class="rating-legend" aria-label="Escala de evaluación">
                    <span class="rating-legend-title"><i class="fa-solid fa-gauge-high" aria-hidden="true"></i> Escala de evaluación</span>
                    <span><strong>1</strong> Deficiente</span>
                    <span><strong>2</strong> Bajo</span>
                    <span><strong>3</strong> Aceptable</span>
                    <span><strong>4</strong> Bueno</span>
                    <span><strong>5</strong> Excelente</span>
                </div>

                <form id="performance-evaluation-form" novalidate>
                    <div class="performance-evaluation-workspace">
                        <aside class="evaluation-category-navigation" aria-labelledby="category-navigation-title">
                            <div class="category-navigation-heading">
                                <span><i class="fa-solid fa-list-check" aria-hidden="true"></i></span>
                                <div>
                                    <small>Evaluación guiada</small>
                                    <h3 id="category-navigation-title">Categorías</h3>
                                </div>
                            </div>
                            <ol class="category-navigation-list">
                                <li>
                                    <button class="category-navigation-button is-active" type="button" data-category-index="0" aria-selected="true">
                                        <span class="category-letter category-letter-purple">A</span>
                                        <span class="category-navigation-copy"><strong>Aplicación</strong><small>5 criterios</small></span>
                                        <span class="category-navigation-state"><i class="fa-solid fa-circle-half-stroke" aria-hidden="true"></i><span>En progreso</span></span>
                                    </button>
                                </li>
                                <li>
                                    <button class="category-navigation-button" type="button" data-category-index="1" aria-selected="false">
                                        <span class="category-letter category-letter-cyan">B</span>
                                        <span class="category-navigation-copy"><strong>Efectividad</strong><small>4 criterios</small></span>
                                        <span class="category-navigation-state"><i class="fa-regular fa-clock" aria-hidden="true"></i><span>Pendiente</span></span>
                                    </button>
                                </li>
                                <li>
                                    <button class="category-navigation-button" type="button" data-category-index="2" aria-selected="false">
                                        <span class="category-letter category-letter-orange">C</span>
                                        <span class="category-navigation-copy"><strong>Impacto</strong><small>5 criterios</small></span>
                                        <span class="category-navigation-state"><i class="fa-regular fa-clock" aria-hidden="true"></i><span>Pendiente</span></span>
                                    </button>
                                </li>
                                <li>
                                    <button class="category-navigation-button" type="button" data-category-index="3" aria-selected="false">
                                        <span class="category-letter category-letter-pink">D</span>
                                        <span class="category-navigation-copy"><strong>Habilidades blandas</strong><small>5 criterios</small></span>
                                        <span class="category-navigation-state"><i class="fa-regular fa-clock" aria-hidden="true"></i><span>Pendiente</span></span>
                                    </button>
                                </li>
                            </ol>
                            <div class="category-navigation-help">
                                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                <p>Responde todos los criterios para habilitar la finalización.</p>
                            </div>
                        </aside>

                        <div class="evaluation-category-content">
                            <section class="evaluation-category-panel is-active" data-result-id="application-result" aria-labelledby="application-title">
                                <div class="evaluation-category-header">
                                    <span class="category-main-icon category-main-purple"><i class="fa-solid fa-gears" aria-hidden="true"></i></span>
                                    <div><span>Categoría A</span><h3 id="application-title">Aplicación del conocimiento</h3><p>Evalúa qué tanto aplica en su trabajo lo aprendido durante el curso.</p></div>
                                </div>

                                <div class="performance-criterion">
                                    <div class="criterion-copy"><span>A1</span><p>Aplica correctamente los procedimientos aprendidos.</p></div>
                                    <div class="criterion-rating" role="radiogroup" aria-label="Aplica correctamente los procedimientos aprendidos">
                                        <label title="Deficiente"><input type="radio" name="application-1" value="1" aria-label="1 Deficiente"><span>1</span></label>
                                        <label title="Bajo"><input type="radio" name="application-1" value="2" aria-label="2 Bajo"><span>2</span></label>
                                        <label title="Aceptable"><input type="radio" name="application-1" value="3" aria-label="3 Aceptable"><span>3</span></label>
                                        <label title="Bueno"><input type="radio" name="application-1" value="4" aria-label="4 Bueno"><span>4</span></label>
                                        <label title="Excelente"><input type="radio" name="application-1" value="5" aria-label="5 Excelente"><span>5</span></label>
                                    </div><small class="criterion-error">Selecciona una calificación.</small>
                                </div>
                                <div class="performance-criterion">
                                    <div class="criterion-copy"><span>A2</span><p>Reduce errores relacionados con el tema capacitado.</p></div>
                                    <div class="criterion-rating" role="radiogroup" aria-label="Reduce errores relacionados con el tema capacitado">
                                        <label title="Deficiente"><input type="radio" name="application-2" value="1" aria-label="1 Deficiente"><span>1</span></label><label title="Bajo"><input type="radio" name="application-2" value="2" aria-label="2 Bajo"><span>2</span></label><label title="Aceptable"><input type="radio" name="application-2" value="3" aria-label="3 Aceptable"><span>3</span></label><label title="Bueno"><input type="radio" name="application-2" value="4" aria-label="4 Bueno"><span>4</span></label><label title="Excelente"><input type="radio" name="application-2" value="5" aria-label="5 Excelente"><span>5</span></label>
                                    </div><small class="criterion-error">Selecciona una calificación.</small>
                                </div>
                                <div class="performance-criterion">
                                    <div class="criterion-copy"><span>A3</span><p>Realiza sus actividades con mayor seguridad.</p></div>
                                    <div class="criterion-rating" role="radiogroup" aria-label="Realiza sus actividades con mayor seguridad">
                                        <label title="Deficiente"><input type="radio" name="application-3" value="1" aria-label="1 Deficiente"><span>1</span></label><label title="Bajo"><input type="radio" name="application-3" value="2" aria-label="2 Bajo"><span>2</span></label><label title="Aceptable"><input type="radio" name="application-3" value="3" aria-label="3 Aceptable"><span>3</span></label><label title="Bueno"><input type="radio" name="application-3" value="4" aria-label="4 Bueno"><span>4</span></label><label title="Excelente"><input type="radio" name="application-3" value="5" aria-label="5 Excelente"><span>5</span></label>
                                    </div><small class="criterion-error">Selecciona una calificación.</small>
                                </div>
                                <div class="performance-criterion">
                                    <div class="criterion-copy"><span>A4</span><p>Sigue instrucciones y estándares establecidos en el área.</p></div>
                                    <div class="criterion-rating" role="radiogroup" aria-label="Sigue instrucciones y estándares del área">
                                        <label title="Deficiente"><input type="radio" name="application-4" value="1" aria-label="1 Deficiente"><span>1</span></label><label title="Bajo"><input type="radio" name="application-4" value="2" aria-label="2 Bajo"><span>2</span></label><label title="Aceptable"><input type="radio" name="application-4" value="3" aria-label="3 Aceptable"><span>3</span></label><label title="Bueno"><input type="radio" name="application-4" value="4" aria-label="4 Bueno"><span>4</span></label><label title="Excelente"><input type="radio" name="application-4" value="5" aria-label="5 Excelente"><span>5</span></label>
                                    </div><small class="criterion-error">Selecciona una calificación.</small>
                                </div>
                                <div class="performance-criterion">
                                    <div class="criterion-copy"><span>A5</span><p>Demuestra comprensión práctica del contenido revisado.</p></div>
                                    <div class="criterion-rating" role="radiogroup" aria-label="Demuestra comprensión práctica del contenido">
                                        <label title="Deficiente"><input type="radio" name="application-5" value="1" aria-label="1 Deficiente"><span>1</span></label><label title="Bajo"><input type="radio" name="application-5" value="2" aria-label="2 Bajo"><span>2</span></label><label title="Aceptable"><input type="radio" name="application-5" value="3" aria-label="3 Aceptable"><span>3</span></label><label title="Bueno"><input type="radio" name="application-5" value="4" aria-label="4 Bueno"><span>4</span></label><label title="Excelente"><input type="radio" name="application-5" value="5" aria-label="5 Excelente"><span>5</span></label>
                                    </div><small class="criterion-error">Selecciona una calificación.</small>
                                </div>
                            </section>

                            <section class="evaluation-category-panel" data-result-id="effectiveness-result" aria-labelledby="effectiveness-title" hidden>
                                <div class="evaluation-category-header">
                                    <span class="category-main-icon category-main-cyan"><i class="fa-solid fa-bullseye" aria-hidden="true"></i></span>
                                    <div><span>Categoría B</span><h3 id="effectiveness-title">Efectividad del curso</h3><p>Valora si la capacitación fue útil para mejorar el desempeño.</p></div>
                                </div>
                                <div class="performance-criterion">
                                    <div class="criterion-copy"><span>B1</span><p>El curso ayudó a mejorar su desempeño.</p></div>
                                    <div class="criterion-rating" role="radiogroup" aria-label="El curso ayudó a mejorar su desempeño">
                                        <label title="Deficiente"><input type="radio" name="effectiveness-1" value="1" aria-label="1 Deficiente"><span>1</span></label><label title="Bajo"><input type="radio" name="effectiveness-1" value="2" aria-label="2 Bajo"><span>2</span></label><label title="Aceptable"><input type="radio" name="effectiveness-1" value="3" aria-label="3 Aceptable"><span>3</span></label><label title="Bueno"><input type="radio" name="effectiveness-1" value="4" aria-label="4 Bueno"><span>4</span></label><label title="Excelente"><input type="radio" name="effectiveness-1" value="5" aria-label="5 Excelente"><span>5</span></label>
                                    </div><small class="criterion-error">Selecciona una calificación.</small>
                                </div>
                                <div class="performance-criterion">
                                    <div class="criterion-copy"><span>B2</span><p>El contenido fue aplicable a sus actividades reales.</p></div>
                                    <div class="criterion-rating" role="radiogroup" aria-label="El contenido fue aplicable a sus actividades reales">
                                        <label title="Deficiente"><input type="radio" name="effectiveness-2" value="1" aria-label="1 Deficiente"><span>1</span></label><label title="Bajo"><input type="radio" name="effectiveness-2" value="2" aria-label="2 Bajo"><span>2</span></label><label title="Aceptable"><input type="radio" name="effectiveness-2" value="3" aria-label="3 Aceptable"><span>3</span></label><label title="Bueno"><input type="radio" name="effectiveness-2" value="4" aria-label="4 Bueno"><span>4</span></label><label title="Excelente"><input type="radio" name="effectiveness-2" value="5" aria-label="5 Excelente"><span>5</span></label>
                                    </div><small class="criterion-error">Selecciona una calificación.</small>
                                </div>
                                <div class="performance-criterion">
                                    <div class="criterion-copy"><span>B3</span><p>El trabajador muestra mayor dominio del tema.</p></div>
                                    <div class="criterion-rating" role="radiogroup" aria-label="El trabajador muestra mayor dominio del tema">
                                        <label title="Deficiente"><input type="radio" name="effectiveness-3" value="1" aria-label="1 Deficiente"><span>1</span></label><label title="Bajo"><input type="radio" name="effectiveness-3" value="2" aria-label="2 Bajo"><span>2</span></label><label title="Aceptable"><input type="radio" name="effectiveness-3" value="3" aria-label="3 Aceptable"><span>3</span></label><label title="Bueno"><input type="radio" name="effectiveness-3" value="4" aria-label="4 Bueno"><span>4</span></label><label title="Excelente"><input type="radio" name="effectiveness-3" value="5" aria-label="5 Excelente"><span>5</span></label>
                                    </div><small class="criterion-error">Selecciona una calificación.</small>
                                </div>
                                <div class="performance-criterion">
                                    <div class="criterion-copy"><span>B4</span><p>La capacitación resolvió dudas o deficiencias previas.</p></div>
                                    <div class="criterion-rating" role="radiogroup" aria-label="La capacitación resolvió dudas o deficiencias previas">
                                        <label title="Deficiente"><input type="radio" name="effectiveness-4" value="1" aria-label="1 Deficiente"><span>1</span></label><label title="Bajo"><input type="radio" name="effectiveness-4" value="2" aria-label="2 Bajo"><span>2</span></label><label title="Aceptable"><input type="radio" name="effectiveness-4" value="3" aria-label="3 Aceptable"><span>3</span></label><label title="Bueno"><input type="radio" name="effectiveness-4" value="4" aria-label="4 Bueno"><span>4</span></label><label title="Excelente"><input type="radio" name="effectiveness-4" value="5" aria-label="5 Excelente"><span>5</span></label>
                                    </div><small class="criterion-error">Selecciona una calificación.</small>
                                </div>
                            </section>

                            <section class="evaluation-category-panel" data-result-id="impact-result" aria-labelledby="impact-title" hidden>
                                <div class="evaluation-category-header">
                                    <span class="category-main-icon category-main-orange"><i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i></span>
                                    <div><span>Categoría C</span><h3 id="impact-title">Impacto en el área de trabajo</h3><p>Identifica mejoras observables en calidad, productividad y autonomía.</p></div>
                                </div>
                                <div class="performance-criterion">
                                    <div class="criterion-copy"><span>C1</span><p>Mejoró la calidad del trabajo realizado.</p></div>
                                    <div class="criterion-rating" role="radiogroup" aria-label="Mejoró la calidad del trabajo">
                                        <label title="Deficiente"><input type="radio" name="impact-1" value="1" aria-label="1 Deficiente"><span>1</span></label><label title="Bajo"><input type="radio" name="impact-1" value="2" aria-label="2 Bajo"><span>2</span></label><label title="Aceptable"><input type="radio" name="impact-1" value="3" aria-label="3 Aceptable"><span>3</span></label><label title="Bueno"><input type="radio" name="impact-1" value="4" aria-label="4 Bueno"><span>4</span></label><label title="Excelente"><input type="radio" name="impact-1" value="5" aria-label="5 Excelente"><span>5</span></label>
                                    </div><small class="criterion-error">Selecciona una calificación.</small>
                                </div>
                                <div class="performance-criterion">
                                    <div class="criterion-copy"><span>C2</span><p>Mejoró la productividad durante sus actividades.</p></div>
                                    <div class="criterion-rating" role="radiogroup" aria-label="Mejoró la productividad">
                                        <label title="Deficiente"><input type="radio" name="impact-2" value="1" aria-label="1 Deficiente"><span>1</span></label><label title="Bajo"><input type="radio" name="impact-2" value="2" aria-label="2 Bajo"><span>2</span></label><label title="Aceptable"><input type="radio" name="impact-2" value="3" aria-label="3 Aceptable"><span>3</span></label><label title="Bueno"><input type="radio" name="impact-2" value="4" aria-label="4 Bueno"><span>4</span></label><label title="Excelente"><input type="radio" name="impact-2" value="5" aria-label="5 Excelente"><span>5</span></label>
                                    </div><small class="criterion-error">Selecciona una calificación.</small>
                                </div>
                                <div class="performance-criterion">
                                    <div class="criterion-copy"><span>C3</span><p>Disminuyeron retrabajos o errores relacionados.</p></div>
                                    <div class="criterion-rating" role="radiogroup" aria-label="Disminuyeron retrabajos o errores">
                                        <label title="Deficiente"><input type="radio" name="impact-3" value="1" aria-label="1 Deficiente"><span>1</span></label><label title="Bajo"><input type="radio" name="impact-3" value="2" aria-label="2 Bajo"><span>2</span></label><label title="Aceptable"><input type="radio" name="impact-3" value="3" aria-label="3 Aceptable"><span>3</span></label><label title="Bueno"><input type="radio" name="impact-3" value="4" aria-label="4 Bueno"><span>4</span></label><label title="Excelente"><input type="radio" name="impact-3" value="5" aria-label="5 Excelente"><span>5</span></label>
                                    </div><small class="criterion-error">Selecciona una calificación.</small>
                                </div>
                                <div class="performance-criterion">
                                    <div class="criterion-copy"><span>C4</span><p>Mejoró el cumplimiento de procedimientos.</p></div>
                                    <div class="criterion-rating" role="radiogroup" aria-label="Mejoró el cumplimiento de procedimientos">
                                        <label title="Deficiente"><input type="radio" name="impact-4" value="1" aria-label="1 Deficiente"><span>1</span></label><label title="Bajo"><input type="radio" name="impact-4" value="2" aria-label="2 Bajo"><span>2</span></label><label title="Aceptable"><input type="radio" name="impact-4" value="3" aria-label="3 Aceptable"><span>3</span></label><label title="Bueno"><input type="radio" name="impact-4" value="4" aria-label="4 Bueno"><span>4</span></label><label title="Excelente"><input type="radio" name="impact-4" value="5" aria-label="5 Excelente"><span>5</span></label>
                                    </div><small class="criterion-error">Selecciona una calificación.</small>
                                </div>
                                <div class="performance-criterion">
                                    <div class="criterion-copy"><span>C5</span><p>Se observa mayor autonomía en sus actividades.</p></div>
                                    <div class="criterion-rating" role="radiogroup" aria-label="Se observa mayor autonomía">
                                        <label title="Deficiente"><input type="radio" name="impact-5" value="1" aria-label="1 Deficiente"><span>1</span></label><label title="Bajo"><input type="radio" name="impact-5" value="2" aria-label="2 Bajo"><span>2</span></label><label title="Aceptable"><input type="radio" name="impact-5" value="3" aria-label="3 Aceptable"><span>3</span></label><label title="Bueno"><input type="radio" name="impact-5" value="4" aria-label="4 Bueno"><span>4</span></label><label title="Excelente"><input type="radio" name="impact-5" value="5" aria-label="5 Excelente"><span>5</span></label>
                                    </div><small class="criterion-error">Selecciona una calificación.</small>
                                </div>
                            </section>

                            <section class="evaluation-category-panel" data-result-id="soft-skills-result" aria-labelledby="soft-skills-title" hidden>
                                <div class="evaluation-category-header">
                                    <span class="category-main-icon category-main-pink"><i class="fa-solid fa-people-group" aria-hidden="true"></i></span>
                                    <div><span>Categoría D</span><h3 id="soft-skills-title">Habilidades blandas y actitud</h3><p>Registra cambios observables en comunicación, colaboración y responsabilidad.</p></div>
                                </div>
                                <div class="performance-criterion">
                                    <div class="criterion-copy"><span>D1</span><p>Muestra mejor comunicación con el equipo.</p></div>
                                    <div class="criterion-rating" role="radiogroup" aria-label="Muestra mejor comunicación con el equipo">
                                        <label title="Deficiente"><input type="radio" name="soft-skills-1" value="1" aria-label="1 Deficiente"><span>1</span></label><label title="Bajo"><input type="radio" name="soft-skills-1" value="2" aria-label="2 Bajo"><span>2</span></label><label title="Aceptable"><input type="radio" name="soft-skills-1" value="3" aria-label="3 Aceptable"><span>3</span></label><label title="Bueno"><input type="radio" name="soft-skills-1" value="4" aria-label="4 Bueno"><span>4</span></label><label title="Excelente"><input type="radio" name="soft-skills-1" value="5" aria-label="5 Excelente"><span>5</span></label>
                                    </div><small class="criterion-error">Selecciona una calificación.</small>
                                </div>
                                <div class="performance-criterion">
                                    <div class="criterion-copy"><span>D2</span><p>Tiene mayor disposición para aprender.</p></div>
                                    <div class="criterion-rating" role="radiogroup" aria-label="Tiene mayor disposición para aprender">
                                        <label title="Deficiente"><input type="radio" name="soft-skills-2" value="1" aria-label="1 Deficiente"><span>1</span></label><label title="Bajo"><input type="radio" name="soft-skills-2" value="2" aria-label="2 Bajo"><span>2</span></label><label title="Aceptable"><input type="radio" name="soft-skills-2" value="3" aria-label="3 Aceptable"><span>3</span></label><label title="Bueno"><input type="radio" name="soft-skills-2" value="4" aria-label="4 Bueno"><span>4</span></label><label title="Excelente"><input type="radio" name="soft-skills-2" value="5" aria-label="5 Excelente"><span>5</span></label>
                                    </div><small class="criterion-error">Selecciona una calificación.</small>
                                </div>
                                <div class="performance-criterion">
                                    <div class="criterion-copy"><span>D3</span><p>Colabora mejor con sus compañeros.</p></div>
                                    <div class="criterion-rating" role="radiogroup" aria-label="Colabora mejor con sus compañeros">
                                        <label title="Deficiente"><input type="radio" name="soft-skills-3" value="1" aria-label="1 Deficiente"><span>1</span></label><label title="Bajo"><input type="radio" name="soft-skills-3" value="2" aria-label="2 Bajo"><span>2</span></label><label title="Aceptable"><input type="radio" name="soft-skills-3" value="3" aria-label="3 Aceptable"><span>3</span></label><label title="Bueno"><input type="radio" name="soft-skills-3" value="4" aria-label="4 Bueno"><span>4</span></label><label title="Excelente"><input type="radio" name="soft-skills-3" value="5" aria-label="5 Excelente"><span>5</span></label>
                                    </div><small class="criterion-error">Selecciona una calificación.</small>
                                </div>
                                <div class="performance-criterion">
                                    <div class="criterion-copy"><span>D4</span><p>Acepta y aplica la retroalimentación recibida.</p></div>
                                    <div class="criterion-rating" role="radiogroup" aria-label="Acepta y aplica la retroalimentación">
                                        <label title="Deficiente"><input type="radio" name="soft-skills-4" value="1" aria-label="1 Deficiente"><span>1</span></label><label title="Bajo"><input type="radio" name="soft-skills-4" value="2" aria-label="2 Bajo"><span>2</span></label><label title="Aceptable"><input type="radio" name="soft-skills-4" value="3" aria-label="3 Aceptable"><span>3</span></label><label title="Bueno"><input type="radio" name="soft-skills-4" value="4" aria-label="4 Bueno"><span>4</span></label><label title="Excelente"><input type="radio" name="soft-skills-4" value="5" aria-label="5 Excelente"><span>5</span></label>
                                    </div><small class="criterion-error">Selecciona una calificación.</small>
                                </div>
                                <div class="performance-criterion">
                                    <div class="criterion-copy"><span>D5</span><p>Muestra mayor responsabilidad en sus actividades.</p></div>
                                    <div class="criterion-rating" role="radiogroup" aria-label="Muestra mayor responsabilidad en sus actividades">
                                        <label title="Deficiente"><input type="radio" name="soft-skills-5" value="1" aria-label="1 Deficiente"><span>1</span></label><label title="Bajo"><input type="radio" name="soft-skills-5" value="2" aria-label="2 Bajo"><span>2</span></label><label title="Aceptable"><input type="radio" name="soft-skills-5" value="3" aria-label="3 Aceptable"><span>3</span></label><label title="Bueno"><input type="radio" name="soft-skills-5" value="4" aria-label="4 Bueno"><span>4</span></label><label title="Excelente"><input type="radio" name="soft-skills-5" value="5" aria-label="5 Excelente"><span>5</span></label>
                                    </div><small class="criterion-error">Selecciona una calificación.</small>
                                </div>
                            </section>

                            <div class="category-panel-actions">
                                <button id="previous-performance-category" class="button category-secondary-button" type="button" disabled>
                                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Anterior
                                </button>
                                <div id="category-answer-status" class="category-answer-status" role="status" aria-live="polite">
                                    <i class="fa-regular fa-circle" aria-hidden="true"></i><span>Faltan 5 criterios</span>
                                </div>
                                <button id="next-performance-category" class="button category-primary-button" type="button">
                                    Siguiente categoría <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                </button>
                                <button id="go-to-performance-comments" class="button category-primary-button" type="button" hidden>
                                    Continuar a observaciones <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <section id="performance-comments" class="performance-comments" aria-labelledby="performance-comments-title">
                        <div class="subsection-heading">
                            <span class="subsection-icon subsection-icon-orange"><i class="fa-solid fa-message" aria-hidden="true"></i></span>
                            <div><span>Información complementaria</span><h2 id="performance-comments-title">Comentarios y observaciones</h2><p>Agrega evidencia breve que ayude a comprender las calificaciones seleccionadas.</p></div>
                        </div>
                        <div class="performance-comment-grid">
                            <div class="comment-field comment-field-full"><label for="general-observations">Observaciones generales</label><textarea id="general-observations" class="form-control" rows="3" placeholder="Describe los cambios más relevantes observados..."></textarea></div>
                            <div class="comment-field"><label for="detected-strengths">Fortalezas detectadas</label><textarea id="detected-strengths" class="form-control" rows="3" placeholder="Ej. Mayor apego al procedimiento..."></textarea></div>
                            <div class="comment-field"><label for="opportunity-areas">Áreas de oportunidad</label><textarea id="opportunity-areas" class="form-control" rows="3" placeholder="Ej. Reforzar la comunicación de riesgos..."></textarea></div>
                            <div class="comment-field"><label for="recommended-actions">Acciones recomendadas</label><textarea id="recommended-actions" class="form-control" rows="3" placeholder="Indica acciones concretas de acompañamiento..."></textarea></div>
                            <div class="comment-field"><label for="followup-required">Seguimiento requerido</label><select id="followup-required" class="form-select"><option value="">Selecciona una opción</option><option>Sin seguimiento adicional</option><option>Revisión en 15 días</option><option>Revisión en 30 días</option><option>Reforzamiento de capacitación</option></select><small>Esta selección es únicamente visual.</small></div>
                        </div>
                    </section>

                    <section class="performance-result-summary" aria-labelledby="performance-result-title">
                        <div class="subsection-heading">
                            <span class="subsection-icon subsection-icon-purple"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i></span>
                            <div><span>Resultado simulado</span><h2 id="performance-result-title">Resumen del seguimiento</h2><p>Los porcentajes cambian visualmente conforme seleccionas calificaciones.</p></div>
                        </div>
                        <div class="performance-result-grid">
                            <article class="performance-result-card result-purple"><span>Aplicación del conocimiento</span><strong id="application-result">0%</strong><div class="result-progress"><span style="width: 0%"></span></div></article>
                            <article class="performance-result-card result-cyan"><span>Efectividad del curso</span><strong id="effectiveness-result">0%</strong><div class="result-progress"><span style="width: 0%"></span></div></article>
                            <article class="performance-result-card result-orange"><span>Impacto en el área</span><strong id="impact-result">0%</strong><div class="result-progress"><span style="width: 0%"></span></div></article>
                            <article class="performance-result-card result-pink"><span>Habilidades blandas</span><strong id="soft-skills-result">0%</strong><div class="result-progress"><span style="width: 0%"></span></div></article>
                            <article class="performance-result-card overall-result-card"><span>Resultado general</span><strong id="overall-performance-result">0%</strong><div class="result-progress"><span style="width: 0%"></span></div><small id="overall-performance-level">Sin evaluar</small></article>
                        </div>
                    </section>

                </form>
                </div>
            </section>

            <section class="performance-actions-section" aria-labelledby="performance-actions-title">
                <div class="performance-actions-heading">
                    <span><i class="fa-solid fa-list-check" aria-hidden="true"></i></span>
                    <div>
                        <h2 id="performance-actions-title">Acciones de la evaluación</h2>
                        <p>Guarda el avance simulado o finaliza cuando todos los criterios estén respondidos.</p>
                    </div>
                </div>
                <div class="performance-form-actions">
                    <a class="button form-back-button" href="evaluacionDesempeno.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Volver a trabajadores</a>
                    <button id="print-performance-report" class="button form-print-button" type="button"><i class="fa-solid fa-print" aria-hidden="true"></i> Imprimir reporte</button>
                    <button id="save-performance-draft" class="button form-save-button" type="button"><i class="fa-regular fa-floppy-disk" aria-hidden="true"></i> Guardar evaluación simulada</button>
                    <button id="finish-performance-evaluation" class="button form-finish-button" type="button" disabled><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Finalizar evaluación</button>
                </div>
                <div id="performance-action-feedback" class="performance-action-feedback" role="status" aria-live="polite"></div>
            </section>

            <div id="performance-finished-alert" class="performance-finished-alert" role="status" aria-live="polite" hidden>
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                <div><strong>Evaluación finalizada de forma simulada</strong><p>Los datos no fueron almacenados. Puedes volver a la lista de trabajadores para continuar.</p></div>
                <a class="button" href="evaluacionDesempeno.php">Volver a trabajadores</a>
            </div>
        </div>

        <div class="modal fade performance-confirm-modal" id="finish-performance-modal" tabindex="-1" aria-labelledby="finish-performance-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <span class="performance-modal-icon"><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i></span>
                        <h2 id="finish-performance-title">¿Finalizar la evaluación?</h2>
                        <p>Confirma que las calificaciones y observaciones están listas. Esta acción solo se simulará y no guardará información.</p>
                        <div class="performance-modal-summary"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>Los 19 criterios principales fueron respondidos.</span></div>
                    </div>
                    <div class="modal-footer">
                        <button class="button performance-modal-cancel" type="button" data-bs-dismiss="modal">Cancelar</button>
                        <button id="confirm-performance-finish" class="button performance-modal-confirm" type="button"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Finalizar evaluación</button>
                    </div>
                </div>
            </div>
        </div>
<?php include __DIR__ . '/../includes/app-footer.php'; ?>
