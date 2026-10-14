<?php
$pageTitle = 'Contenido del curso | CapacitaLíder';
$sectionTitle = 'Contenido del curso';
$activePage = 'cursos';
$basePath = '../';
$pageStyle = 'css/contenidoCurso.css';
$pageScript = 'js/contenidoCurso.js';

include __DIR__ . '/../includes/app-header.php';
?>
        <div class="course-content-container">
            <a class="back-to-courses" href="cursos.php">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Volver a cursos</span>
            </a>

            <section class="course-overview-card" aria-labelledby="course-title">
                <div class="course-overview-copy">
                    <span class="course-overview-label">
                        <i class="fa-solid fa-helmet-safety" aria-hidden="true"></i>
                        Prevención y seguridad
                    </span>
                    <h2 id="course-title">Seguridad industrial básica</h2>
                    <p>Aprende las medidas esenciales para identificar riesgos y trabajar de forma segura dentro del área operativa.</p>

                    <div class="course-overview-meta">
                        <span><i class="fa-solid fa-user-tag" aria-hidden="true"></i> Operador</span>
                        <span><i class="fa-regular fa-clock" aria-hidden="true"></i> 1 h 20 min</span>
                        <span id="course-status" class="course-status"><i class="fa-solid fa-circle-play" aria-hidden="true"></i> En progreso</span>
                    </div>
                </div>

                <div class="course-overview-progress">
                    <div class="progress-ring" aria-hidden="true">
                        <span id="course-progress-ring">65%</span>
                    </div>
                    <div class="overview-progress-copy">
                        <div>
                            <span>Avance del curso</span>
                            <strong id="course-progress-text">65%</strong>
                        </div>
                        <div id="course-progress" class="progress" role="progressbar" aria-label="Avance del curso" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" style="width: 65%"></div>
                        </div>
                        <small>Tu avance se actualiza de forma simulada al completar cada módulo.</small>
                    </div>
                </div>
            </section>

            <div class="learning-layout">
                <aside class="module-navigation" aria-labelledby="module-navigation-title">
                    <div class="module-navigation-header">
                        <span class="module-navigation-icon"><i class="fa-solid fa-list-check" aria-hidden="true"></i></span>
                        <div class="module-navigation-title">
                            <span>Contenido</span>
                            <h2 id="module-navigation-title">Módulos del curso</h2>
                        </div>
                        <button id="module-navigation-toggle" class="module-navigation-toggle" type="button" aria-label="Comprimir módulos" aria-controls="course-module-list" aria-expanded="true">
                            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                        </button>
                    </div>

                    <nav aria-label="Módulos del curso">
                        <ol id="course-module-list" class="module-list">
                            <li>
                                <button class="module-button is-active" type="button" data-module-index="0" aria-selected="true">
                                    <span class="module-number">1</span>
                                    <span class="module-button-copy">
                                        <small>Módulo 1</small>
                                        <strong>Introducción</strong>
                                    </span>
                                    <span class="module-check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
                                </button>
                            </li>
                            <li>
                                <button class="module-button" type="button" data-module-index="1" aria-selected="false">
                                    <span class="module-number">2</span>
                                    <span class="module-button-copy">
                                        <small>Módulo 2</small>
                                        <strong>Procedimiento básico</strong>
                                    </span>
                                    <span class="module-check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
                                </button>
                            </li>
                            <li>
                                <button class="module-button" type="button" data-module-index="2" aria-selected="false">
                                    <span class="module-number">3</span>
                                    <span class="module-button-copy">
                                        <small>Módulo 3</small>
                                        <strong>Buenas prácticas</strong>
                                    </span>
                                    <span class="module-check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
                                </button>
                            </li>
                            <li>
                                <button class="module-button" type="button" data-module-index="3" aria-selected="false">
                                    <span class="module-number">4</span>
                                    <span class="module-button-copy">
                                        <small>Módulo 4</small>
                                        <strong>Validación</strong>
                                    </span>
                                    <span class="module-check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
                                </button>
                            </li>
                            <li>
                                <button class="module-button" type="button" data-module-index="4" aria-selected="false">
                                    <span class="module-number">5</span>
                                    <span class="module-button-copy">
                                        <small>Módulo 5</small>
                                        <strong>Cuestionario de prueba</strong>
                                    </span>
                                    <span class="module-check" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
                                </button>
                            </li>
                        </ol>
                    </nav>

                    <div class="module-navigation-help">
                        <i class="fa-regular fa-lightbulb" aria-hidden="true"></i>
                        <p>Completa las actividades y marca cada módulo como visto para actualizar tu avance.</p>
                    </div>
                </aside>

                <section class="learning-workspace" aria-label="Contenido de aprendizaje">
                    <div class="current-module-bar">
                        <div>
                            <span>Estás revisando</span>
                            <strong id="current-module-name">Módulo 1: Introducción</strong>
                        </div>
                        <span id="current-module-position" class="module-position">1 de 4</span>
                    </div>

                    <article class="module-content-panel is-active" data-module-panel="0">
                        <header class="module-content-heading">
                            <span class="content-type-label"><i class="fa-solid fa-play" aria-hidden="true"></i> Video y lectura</span>
                            <h2>Introducción a la seguridad industrial</h2>
                            <p>Reconoce por qué la prevención es una responsabilidad compartida y cómo iniciar una jornada segura.</p>
                        </header>

                        <button id="simulated-video" class="simulated-video" type="button" aria-pressed="false">
                            <span class="video-pattern" aria-hidden="true"></span>
                            <span class="video-play-icon"><i class="fa-solid fa-play" aria-hidden="true"></i></span>
                            <span class="video-copy">
                                <strong>Video de capacitación</strong>
                                <small id="video-status">Presiona para reproducir · Duración simulada: 06:30</small>
                            </span>
                            <span class="video-duration">06:30</span>
                        </button>

                        <section class="written-material" aria-labelledby="intro-material-title">
                            <div class="material-heading">
                                <span class="material-icon"><i class="fa-solid fa-book-open" aria-hidden="true"></i></span>
                                <div>
                                    <span>Material escrito</span>
                                    <h3 id="intro-material-title">Antes de comenzar</h3>
                                </div>
                            </div>
                            <p>La seguridad comienza al observar el entorno antes de iniciar cualquier actividad. Una revisión breve puede evitar incidentes y facilitar una respuesta oportuna.</p>
                            <ul class="learning-points">
                                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Identifica condiciones inseguras antes de operar.</li>
                                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Utiliza siempre el equipo de protección indicado.</li>
                                <li><i class="fa-solid fa-check" aria-hidden="true"></i> Reporta cualquier riesgo de forma clara e inmediata.</li>
                            </ul>
                        </section>

                        <section class="activity-card quick-quiz" aria-labelledby="quick-question-one">
                            <div class="activity-heading">
                                <span class="activity-icon"><i class="fa-solid fa-bolt" aria-hidden="true"></i></span>
                                <div>
                                    <span class="activity-status">Pregunta rápida</span>
                                    <h3 id="quick-question-one">¿Qué debes hacer antes de iniciar una actividad?</h3>
                                </div>
                            </div>
                            <div class="quiz-options">
                                <button class="quiz-option" type="button" data-correct="false">Comenzar inmediatamente para ahorrar tiempo</button>
                                <button class="quiz-option" type="button" data-correct="true">Revisar el entorno e identificar posibles riesgos</button>
                                <button class="quiz-option" type="button" data-correct="false">Esperar a que otra persona revise el área</button>
                            </div>
                            <p class="activity-feedback" role="status" aria-live="polite"></p>
                        </section>
                    </article>

                    <article class="module-content-panel" data-module-panel="1" hidden>
                        <header class="module-content-heading">
                            <span class="content-type-label"><i class="fa-solid fa-route" aria-hidden="true"></i> Procedimiento visual</span>
                            <h2>Procedimiento básico de trabajo seguro</h2>
                            <p>Sigue una secuencia sencilla antes, durante y después de realizar una actividad operativa.</p>
                        </header>

                        <ol class="procedure-steps">
                            <li>
                                <span>01</span>
                                <div><h3>Observa</h3><p>Revisa el área, identifica obstáculos y confirma que las condiciones sean adecuadas.</p></div>
                            </li>
                            <li>
                                <span>02</span>
                                <div><h3>Prepárate</h3><p>Coloca tu equipo de protección y verifica las herramientas necesarias.</p></div>
                            </li>
                            <li>
                                <span>03</span>
                                <div><h3>Comunica</h3><p>Informa cualquier riesgo y confirma con el equipo antes de comenzar.</p></div>
                            </li>
                        </ol>

                        <section class="activity-card checklist-activity" aria-labelledby="procedure-checklist-title">
                            <div class="activity-heading">
                                <span class="activity-icon activity-icon-success"><i class="fa-solid fa-list-check" aria-hidden="true"></i></span>
                                <div>
                                    <span class="activity-status">Checklist interactivo</span>
                                    <h3 id="procedure-checklist-title">Marca los pasos que debes confirmar</h3>
                                </div>
                            </div>
                            <div class="learning-checklist">
                                <label><input type="checkbox" data-correct="true"> <span>El área se encuentra libre de obstáculos.</span><small class="checklist-item-feedback" hidden></small></label>
                                <label><input type="checkbox" data-correct="true"> <span>Utilizo el equipo de protección requerido.</span><small class="checklist-item-feedback" hidden></small></label>
                                <label><input type="checkbox" data-correct="true"> <span>Las herramientas están en condiciones adecuadas.</span><small class="checklist-item-feedback" hidden></small></label>
                                <label><input type="checkbox" data-correct="true"> <span>Conozco a quién reportar una condición insegura.</span><small class="checklist-item-feedback" hidden></small></label>
                                <label><input type="checkbox" data-correct="false"> <span>Inicio la actividad aunque detecte un obstáculo menor.</span><small class="checklist-item-feedback" hidden></small></label>
                                <label><input type="checkbox" data-correct="false"> <span>Uso una herramienta dañada si la actividad es rápida.</span><small class="checklist-item-feedback" hidden></small></label>
                            </div>
                            <button id="review-learning-checklist" class="secondary-action checklist-review-button" type="button">
                                <i class="fa-solid fa-check-double" aria-hidden="true"></i> Revisar respuestas
                            </button>
                            <p class="activity-feedback" role="status" aria-live="polite">Selecciona únicamente los pasos correctos y presiona “Revisar respuestas”.</p>
                        </section>
                    </article>

                    <article class="module-content-panel" data-module-panel="2" hidden>
                        <header class="module-content-heading">
                            <span class="content-type-label"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Recomendaciones</span>
                            <h2>Buenas prácticas en el área de trabajo</h2>
                            <p>Pequeñas acciones constantes ayudan a construir un entorno más seguro para todas las personas.</p>
                        </header>

                        <div class="practice-grid">
                            <article><span><i class="fa-solid fa-person-walking-arrow-right" aria-hidden="true"></i></span><h3>Mantén rutas libres</h3><p>No bloquees pasillos, accesos ni salidas.</p></article>
                            <article><span><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span><h3>Señaliza riesgos</h3><p>Haz visible cualquier condición que requiera atención.</p></article>
                            <article><span><i class="fa-solid fa-people-group" aria-hidden="true"></i></span><h3>Cuida al equipo</h3><p>Comparte información y evita acciones improvisadas.</p></article>
                        </div>

                        <section class="activity-card reflection-activity" aria-labelledby="reflection-title">
                            <div class="activity-heading">
                                <span class="activity-icon activity-icon-purple"><i class="fa-solid fa-pen" aria-hidden="true"></i></span>
                                <div>
                                    <span class="activity-status">Dinámica escrita</span>
                                    <h3 id="reflection-title">¿Qué acción puedes aplicar hoy?</h3>
                                </div>
                            </div>
                            <label for="reflection-answer">Escribe una respuesta breve</label>
                            <textarea id="reflection-answer" class="form-control" rows="4" placeholder="Ej. Revisar que mi ruta de trabajo esté libre antes de comenzar."></textarea>
                            <button id="complete-reflection" class="secondary-action" type="button">
                                <i class="fa-solid fa-check" aria-hidden="true"></i> Guardar respuesta simulada
                            </button>
                            <p class="activity-feedback" role="status" aria-live="polite"></p>
                        </section>
                    </article>

                    <article class="module-content-panel" data-module-panel="3" hidden>
                        <header class="module-content-heading">
                            <span class="content-type-label"><i class="fa-solid fa-clipboard-question" aria-hidden="true"></i> Validación</span>
                            <h2>Comprueba lo aprendido</h2>
                            <p>Responde la pregunta y revisa la tarjeta antes de iniciar la evaluación del curso.</p>
                        </header>

                        <section class="activity-card quick-quiz" aria-labelledby="validation-question">
                            <div class="activity-heading">
                                <span class="activity-icon"><i class="fa-solid fa-circle-question" aria-hidden="true"></i></span>
                                <div>
                                    <span class="activity-status">Verdadero o falso</span>
                                    <h3 id="validation-question">Reportar una condición insegura es responsabilidad de todo el equipo.</h3>
                                </div>
                            </div>
                            <div class="quiz-options quiz-options-inline">
                                <button class="quiz-option" type="button" data-correct="true">Verdadero</button>
                                <button class="quiz-option" type="button" data-correct="false">Falso</button>
                            </div>
                            <p class="activity-feedback" role="status" aria-live="polite"></p>
                        </section>

                        <section class="knowledge-review" aria-labelledby="knowledge-review-title">
                            <div class="knowledge-review-heading">
                                <div>
                                    <span>Repaso interactivo</span>
                                    <h3 id="knowledge-review-title">Voltea las tarjetas para conocer la respuesta</h3>
                                </div>
                                <i class="fa-solid fa-rotate" aria-hidden="true"></i>
                            </div>

                            <div class="knowledge-cards-grid">
                                <button class="knowledge-card knowledge-card-gold" type="button" aria-pressed="false" aria-label="Mostrar respuesta: ¿Cuál es la primera acción ante un riesgo?" data-question="¿Cuál es la primera acción ante un riesgo?">
                                    <span class="knowledge-card-inner">
                                        <span class="knowledge-card-face knowledge-card-front" aria-hidden="false">
                                            <span class="knowledge-card-number">01</span>
                                            <span class="knowledge-card-icon"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
                                            <small>Pregunta</small>
                                            <strong>¿Cuál es la primera acción ante un riesgo?</strong>
                                            <span class="knowledge-card-hint"><i class="fa-solid fa-rotate" aria-hidden="true"></i> Presiona para ver la respuesta</span>
                                        </span>
                                        <span class="knowledge-card-face knowledge-card-back" aria-hidden="true">
                                            <span class="knowledge-card-icon"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></span>
                                            <small>Respuesta</small>
                                            <strong>Detenerse, evitar exponerse y reportar la condición mediante el canal indicado.</strong>
                                            <span class="knowledge-card-hint"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Presiona para volver</span>
                                        </span>
                                    </span>
                                </button>

                                <button class="knowledge-card knowledge-card-cyan" type="button" aria-pressed="false" aria-label="Mostrar respuesta: ¿Qué debes hacer si tu equipo de protección está dañado?" data-question="¿Qué debes hacer si tu equipo de protección está dañado?">
                                    <span class="knowledge-card-inner">
                                        <span class="knowledge-card-face knowledge-card-front" aria-hidden="false">
                                            <span class="knowledge-card-number">02</span>
                                            <span class="knowledge-card-icon"><i class="fa-solid fa-helmet-safety" aria-hidden="true"></i></span>
                                            <small>Pregunta</small>
                                            <strong>¿Qué debes hacer si tu equipo de protección está dañado?</strong>
                                            <span class="knowledge-card-hint"><i class="fa-solid fa-rotate" aria-hidden="true"></i> Presiona para ver la respuesta</span>
                                        </span>
                                        <span class="knowledge-card-face knowledge-card-back" aria-hidden="true">
                                            <span class="knowledge-card-icon"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
                                            <small>Respuesta</small>
                                            <strong>No utilizarlo. Debes informar la situación y solicitar un reemplazo adecuado.</strong>
                                            <span class="knowledge-card-hint"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Presiona para volver</span>
                                        </span>
                                    </span>
                                </button>

                                <button class="knowledge-card knowledge-card-pink" type="button" aria-pressed="false" aria-label="Mostrar respuesta: ¿Por qué deben reportarse los incidentes menores?" data-question="¿Por qué deben reportarse los incidentes menores?">
                                    <span class="knowledge-card-inner">
                                        <span class="knowledge-card-face knowledge-card-front" aria-hidden="false">
                                            <span class="knowledge-card-number">03</span>
                                            <span class="knowledge-card-icon"><i class="fa-solid fa-bullhorn" aria-hidden="true"></i></span>
                                            <small>Pregunta</small>
                                            <strong>¿Por qué deben reportarse los incidentes menores?</strong>
                                            <span class="knowledge-card-hint"><i class="fa-solid fa-rotate" aria-hidden="true"></i> Presiona para ver la respuesta</span>
                                        </span>
                                        <span class="knowledge-card-face knowledge-card-back" aria-hidden="true">
                                            <span class="knowledge-card-icon"><i class="fa-solid fa-magnifying-glass-chart" aria-hidden="true"></i></span>
                                            <small>Respuesta</small>
                                            <strong>Porque permiten identificar causas y prevenir que una situación similar se convierta en un accidente.</strong>
                                            <span class="knowledge-card-hint"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Presiona para volver</span>
                                        </span>
                                    </span>
                                </button>
                            </div>
                        </section>

                    </article>

                    <article class="module-content-panel" data-module-panel="4" hidden>
                        <header class="module-content-heading">
                            <span class="content-type-label"><i class="fa-solid fa-list-check" aria-hidden="true"></i> Repaso final</span>
                            <h2>Cuestionario de prueba</h2>
                            <p>Responde estas tres preguntas para repasar los puntos principales antes de iniciar la evaluación real.</p>
                        </header>

                        <form id="practice-quiz" class="practice-quiz" novalidate>
                            <fieldset class="practice-question" data-correct="b" data-correct-answer="Revisar el entorno, detenerse ante un riesgo y reportarlo.">
                                <legend><span>Pregunta 1 de 3</span>¿Qué acción debes realizar antes de comenzar una actividad?</legend>
                                <div class="practice-options">
                                    <label class="practice-option"><input type="radio" name="practice-question-1" value="a"><span>Iniciar de inmediato para evitar retrasos.</span></label>
                                    <label class="practice-option"><input type="radio" name="practice-question-1" value="b"><span>Revisar el entorno, identificar riesgos y confirmar condiciones seguras.</span></label>
                                    <label class="practice-option"><input type="radio" name="practice-question-1" value="c"><span>Esperar a que otra persona realice la revisión.</span></label>
                                </div>
                                <p class="practice-answer-feedback" role="status" hidden></p>
                            </fieldset>

                            <fieldset class="practice-question" data-correct="c" data-correct-answer="No utilizarlo, reportar el daño y solicitar un reemplazo.">
                                <legend><span>Pregunta 2 de 3</span>¿Qué debes hacer si tu equipo de protección está dañado?</legend>
                                <div class="practice-options">
                                    <label class="practice-option"><input type="radio" name="practice-question-2" value="a"><span>Utilizarlo con cuidado solo durante esa jornada.</span></label>
                                    <label class="practice-option"><input type="radio" name="practice-question-2" value="b"><span>Repararlo por cuenta propia antes de comenzar.</span></label>
                                    <label class="practice-option"><input type="radio" name="practice-question-2" value="c"><span>No utilizarlo, reportar el daño y solicitar un reemplazo.</span></label>
                                </div>
                                <p class="practice-answer-feedback" role="status" hidden></p>
                            </fieldset>

                            <fieldset class="practice-question" data-correct="a" data-correct-answer="Porque ayuda a identificar causas y prevenir accidentes futuros.">
                                <legend><span>Pregunta 3 de 3</span>¿Por qué es importante reportar también los incidentes menores?</legend>
                                <div class="practice-options">
                                    <label class="practice-option"><input type="radio" name="practice-question-3" value="a"><span>Porque ayuda a identificar causas y prevenir accidentes futuros.</span></label>
                                    <label class="practice-option"><input type="radio" name="practice-question-3" value="b"><span>Porque permite asignar responsables por cada situación.</span></label>
                                    <label class="practice-option"><input type="radio" name="practice-question-3" value="c"><span>Solo para llevar un registro de las actividades del día.</span></label>
                                </div>
                                <p class="practice-answer-feedback" role="status" hidden></p>
                            </fieldset>

                            <button id="finish-practice-quiz" class="finish-practice-quiz" type="button">
                                <i class="fa-solid fa-check-double" aria-hidden="true"></i>
                                <span>Finalizar cuestionario</span>
                            </button>

                            <div id="practice-quiz-result" class="practice-quiz-result" role="status" aria-live="polite" hidden>
                                <span class="practice-result-icon"><i class="fa-solid fa-chart-simple" aria-hidden="true"></i></span>
                                <div><h3 id="practice-quiz-score"></h3><p id="practice-quiz-message"></p></div>
                            </div>
                        </form>

                        <div class="evaluation-callout">
                            <span><i class="fa-solid fa-award" aria-hidden="true"></i></span>
                            <div><h3>Prepárate para la evaluación real</h3><p>Revisa la retroalimentación del cuestionario y continúa cuando te sientas listo.</p></div>
                        </div>
                    </article>

                    <section class="course-resources" aria-labelledby="course-resources-title">
                        <div class="resources-heading">
                            <div>
                                <span>Material complementario</span>
                                <h2 id="course-resources-title">Recursos del curso</h2>
                            </div>
                            <p>Selecciona un recurso para simular su consulta.</p>
                        </div>
                        <div class="resource-grid">
                            <button class="resource-card" type="button" data-resource-name="Guía rápida">
                                <span class="resource-icon resource-icon-cyan"><i class="fa-solid fa-file-lines" aria-hidden="true"></i></span>
                                <span><strong>Guía rápida</strong><small>Documento · 4 páginas</small></span>
                                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                            </button>
                            <button class="resource-card" type="button" data-resource-name="Infografía">
                                <span class="resource-icon resource-icon-purple"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i></span>
                                <span><strong>Infografía</strong><small>Resumen visual</small></span>
                                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                            </button>
                            <button class="resource-card" type="button" data-resource-name="Procedimiento visual">
                                <span class="resource-icon resource-icon-orange"><i class="fa-solid fa-images" aria-hidden="true"></i></span>
                                <span><strong>Procedimiento visual</strong><small>Secuencia ilustrada</small></span>
                                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                            </button>
                            <button class="resource-card" type="button" data-resource-name="Material de apoyo">
                                <span class="resource-icon resource-icon-pink"><i class="fa-solid fa-folder-open" aria-hidden="true"></i></span>
                                <span><strong>Material de apoyo</strong><small>Recursos adicionales</small></span>
                                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                            </button>
                        </div>
                        <p id="resource-feedback" class="resource-feedback" role="status" aria-live="polite"></p>
                    </section>

                    <footer class="learning-actions">
                        <button id="previous-module" class="navigation-action navigation-action-secondary" type="button" disabled>
                            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                            <span>Anterior</span>
                        </button>
                        <div class="learning-actions-main">
                            <button id="mark-module-viewed" class="navigation-action navigation-action-viewed" type="button">
                                <i class="fa-regular fa-circle-check" aria-hidden="true"></i>
                                <span>Marcar como visto</span>
                            </button>
                            <button id="next-module" class="navigation-action navigation-action-primary" type="button">
                                <span>Siguiente módulo</span>
                                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </button>
                            <button id="start-evaluation" class="navigation-action navigation-action-evaluation" type="button" hidden>
                                <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                                <span>Iniciar evaluación</span>
                            </button>
                        </div>
                    </footer>
                    <p id="evaluation-feedback" class="evaluation-feedback" role="status" aria-live="polite"></p>
                </section>
            </div>
        </div>
<?php include __DIR__ . '/../includes/app-footer.php'; ?>
