<?php
$pageTitle = 'Evaluación de conocimientos | CapacitaLíder';
$sectionTitle = 'Evaluación de conocimientos';
$activePage = 'evaluacionConocimientos';
$basePath = '../';
$pageStyle = 'css/evaluacionConocimientos.css';

include __DIR__ . '/../includes/app-header.php';
?>
        <div class="knowledge-evaluation-page">
            <section class="evaluation-hero" aria-labelledby="evaluation-title">
                <div class="evaluation-hero-copy">
                    <span class="evaluation-eyebrow">
                        <i class="fa-solid fa-clipboard-question" aria-hidden="true"></i>
                        Valida lo que aprendiste
                    </span>
                    <h2 id="evaluation-title">Evaluación de conocimientos</h2>
                    <p class="evaluation-subtitle">Selecciona un curso finalizado para iniciar su evaluación</p>
                    <p class="evaluation-description">
                        Revisa el estado de tus cursos asignados. Las evaluaciones estarán disponibles
                        cuando hayas completado todo el contenido del curso.
                    </p>
                </div>

                <div class="evaluation-overview" aria-label="Resumen de evaluaciones">
                    <div class="overview-item overview-available">
                        <span class="overview-icon"><i class="fa-solid fa-unlock-keyhole" aria-hidden="true"></i></span>
                        <div>
                            <strong>2</strong>
                            <span>Disponibles</span>
                        </div>
                    </div>
                    <div class="overview-item overview-pending">
                        <span class="overview-icon"><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i></span>
                        <div>
                            <strong>3</strong>
                            <span>Por completar</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="evaluation-filters" aria-labelledby="filters-title">
                <div class="filter-panel-heading">
                    <span class="filter-heading-icon"><i class="fa-solid fa-sliders" aria-hidden="true"></i></span>
                    <div>
                        <h2 id="filters-title">Encuentra tu curso</h2>
                        <p>Usa uno o varios filtros para localizarlo más fácilmente.</p>
                    </div>
                </div>

                <form class="evaluation-filter-form" action="#" method="get">
                    <div class="evaluation-filter-field filter-name-field">
                        <label for="course-name">Nombre del curso</label>
                        <div class="evaluation-filter-control">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            <input
                                id="course-name"
                                class="form-control"
                                type="search"
                                name="courseName"
                                placeholder="Ej. Seguridad industrial"
                            >
                        </div>
                    </div>

                    <div class="evaluation-filter-field">
                        <label for="course-status">Estatus</label>
                        <div class="evaluation-filter-control">
                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                            <select id="course-status" class="form-select" name="status">
                                <option value="">Todos</option>
                                <option value="finalizado">Finalizado</option>
                                <option value="pendiente">Pendiente</option>
                            </select>
                        </div>
                    </div>

                    <div class="evaluation-filter-field">
                        <label for="course-position">Puesto</label>
                        <div class="evaluation-filter-control">
                            <i class="fa-solid fa-user-tie" aria-hidden="true"></i>
                            <select id="course-position" class="form-select" name="position">
                                <option value="">Todos los puestos</option>
                                <option value="operador">Operador</option>
                                <option value="lider">Líder</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="general">Todo el personal</option>
                            </select>
                        </div>
                    </div>

                    <div class="evaluation-filter-actions">
                        <button class="button filter-search-button" type="button">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            Buscar
                        </button>
                        <button class="button filter-clear-button" type="reset">
                            <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                            Limpiar filtros
                        </button>
                    </div>
                </form>
            </section>

            <section class="evaluation-courses" aria-labelledby="assigned-courses-title">
                <div class="evaluation-section-heading">
                    <div>
                        <span class="evaluation-section-label">Tus evaluaciones</span>
                        <h2 id="assigned-courses-title">Cursos asignados</h2>
                        <p>Primero completa el curso; después podrás validar tus conocimientos.</p>
                    </div>
                    <span class="evaluation-course-count">6 cursos</span>
                </div>

                <div class="evaluation-course-grid">
                    <article class="evaluation-course-card course-completed">
                        <div class="evaluation-card-top">
                            <span class="evaluation-course-icon icon-success">
                                <i class="fa-solid fa-person-chalkboard" aria-hidden="true"></i>
                            </span>
                            <span class="evaluation-status status-completed">
                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                Completado
                            </span>
                        </div>
                        <div class="evaluation-card-content">
                            <span class="evaluation-category">Inducción</span>
                            <h3>Inducción a la operación</h3>
                            <p>Repasa los conceptos esenciales, responsabilidades y lineamientos para iniciar tus actividades.</p>
                            <div class="evaluation-meta">
                                <span><i class="fa-solid fa-user" aria-hidden="true"></i> Operador</span>
                                <span><i class="fa-regular fa-clock" aria-hidden="true"></i> 45 min</span>
                            </div>
                        </div>
                        <div class="evaluation-progress">
                            <div class="progress-heading">
                                <span>Progreso del curso</span>
                                <strong>100%</strong>
                            </div>
                            <div class="progress" role="progressbar" aria-label="Progreso de Inducción a la operación" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                        </div>
                        <p class="evaluation-availability-message message-completed">
                            <i class="fa-solid fa-award" aria-hidden="true"></i>
                            Ya realizaste la evaluación de este curso.
                        </p>
                        <button class="button evaluation-action-button completed-action" type="button" disabled>
                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                            Evaluación completada
                        </button>
                    </article>

                    <article class="evaluation-course-card course-available">
                        <div class="evaluation-card-top">
                            <span class="evaluation-course-icon icon-purple">
                                <i class="fa-solid fa-broom-ball" aria-hidden="true"></i>
                            </span>
                            <span class="evaluation-status status-finished">
                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                Finalizado
                            </span>
                        </div>
                        <div class="evaluation-card-content">
                            <span class="evaluation-category">Organización</span>
                            <h3>Metodología 5S</h3>
                            <p>Comprueba tus conocimientos sobre orden, limpieza y mejora continua en el área de trabajo.</p>
                            <div class="evaluation-meta">
                                <span><i class="fa-solid fa-users" aria-hidden="true"></i> Todo el personal</span>
                                <span><i class="fa-regular fa-clock" aria-hidden="true"></i> 55 min</span>
                            </div>
                        </div>
                        <div class="evaluation-progress">
                            <div class="progress-heading">
                                <span>Progreso del curso</span>
                                <strong>100%</strong>
                            </div>
                            <div class="progress" role="progressbar" aria-label="Progreso de Metodología 5S" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                        </div>
                        <p class="evaluation-availability-message message-ready">
                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                            La evaluación ya está disponible.
                        </p>
                        <a class="button evaluation-action-button available-action" href="evaluacionPreguntas.php">
                            Realizar evaluación
                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </article>

                    <article class="evaluation-course-card course-available">
                        <div class="evaluation-card-top">
                            <span class="evaluation-course-icon icon-cyan">
                                <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                            </span>
                            <span class="evaluation-status status-finished">
                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                Finalizado
                            </span>
                        </div>
                        <div class="evaluation-card-content">
                            <span class="evaluation-category">Cumplimiento</span>
                            <h3>Protección de la información</h3>
                            <p>Valida las prácticas necesarias para proteger datos y prevenir riesgos de seguridad.</p>
                            <div class="evaluation-meta">
                                <span><i class="fa-solid fa-users" aria-hidden="true"></i> Todo el personal</span>
                                <span><i class="fa-regular fa-clock" aria-hidden="true"></i> 40 min</span>
                            </div>
                        </div>
                        <div class="evaluation-progress">
                            <div class="progress-heading">
                                <span>Progreso del curso</span>
                                <strong>100%</strong>
                            </div>
                            <div class="progress" role="progressbar" aria-label="Progreso de Protección de la información" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                        </div>
                        <p class="evaluation-availability-message message-ready">
                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                            La evaluación ya está disponible.
                        </p>
                        <a class="button evaluation-action-button available-action" href="evaluacionPreguntas.php">
                            Realizar evaluación
                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </article>

                    <article class="evaluation-course-card course-locked">
                        <div class="evaluation-card-top">
                            <span class="evaluation-course-icon icon-orange">
                                <i class="fa-solid fa-helmet-safety" aria-hidden="true"></i>
                            </span>
                            <span class="evaluation-status status-pending">
                                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                Pendiente
                            </span>
                        </div>
                        <div class="evaluation-card-content">
                            <span class="evaluation-category">Seguridad</span>
                            <h3>Seguridad industrial básica</h3>
                            <p>Aprende las medidas esenciales para prevenir riesgos dentro del área de trabajo.</p>
                            <div class="evaluation-meta">
                                <span><i class="fa-solid fa-user" aria-hidden="true"></i> Operador</span>
                                <span><i class="fa-regular fa-clock" aria-hidden="true"></i> 1 h 20 min</span>
                            </div>
                        </div>
                        <div class="evaluation-progress">
                            <div class="progress-heading">
                                <span>Progreso del curso</span>
                                <strong>98%</strong>
                            </div>
                            <div class="progress" role="progressbar" aria-label="Progreso de Seguridad industrial básica" aria-valuenow="98" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: 98%"></div>
                            </div>
                        </div>
                        <p class="evaluation-availability-message message-locked">
                            <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            Debes finalizar el curso para realizar la evaluación.
                        </p>
                        <button class="button evaluation-action-button locked-action" type="button" disabled>
                            <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            Evaluación bloqueada
                        </button>
                    </article>

                    <article class="evaluation-course-card course-locked">
                        <div class="evaluation-card-top">
                            <span class="evaluation-course-icon icon-pink">
                                <i class="fa-solid fa-award" aria-hidden="true"></i>
                            </span>
                            <span class="evaluation-status status-pending">
                                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                Pendiente
                            </span>
                        </div>
                        <div class="evaluation-card-content">
                            <span class="evaluation-category">Calidad</span>
                            <h3>Calidad en el proceso</h3>
                            <p>Identifica controles, criterios y acciones para mantener la calidad durante la operación.</p>
                            <div class="evaluation-meta">
                                <span><i class="fa-solid fa-user" aria-hidden="true"></i> Operador</span>
                                <span><i class="fa-regular fa-clock" aria-hidden="true"></i> 1 h</span>
                            </div>
                        </div>
                        <div class="evaluation-progress">
                            <div class="progress-heading">
                                <span>Progreso del curso</span>
                                <strong>30%</strong>
                            </div>
                            <div class="progress" role="progressbar" aria-label="Progreso de Calidad en el proceso" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: 30%"></div>
                            </div>
                        </div>
                        <p class="evaluation-availability-message message-locked">
                            <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            Debes finalizar el curso para realizar la evaluación.
                        </p>
                        <button class="button evaluation-action-button locked-action" type="button" disabled>
                            <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            Evaluación bloqueada
                        </button>
                    </article>

                    <article class="evaluation-course-card course-locked">
                        <div class="evaluation-card-top">
                            <span class="evaluation-course-icon icon-purple">
                                <i class="fa-solid fa-comments" aria-hidden="true"></i>
                            </span>
                            <span class="evaluation-status status-pending">
                                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                Pendiente
                            </span>
                        </div>
                        <div class="evaluation-card-content">
                            <span class="evaluation-category">Habilidades</span>
                            <h3>Comunicación efectiva</h3>
                            <p>Fortalece la escucha, la claridad y la colaboración en conversaciones de trabajo.</p>
                            <div class="evaluation-meta">
                                <span><i class="fa-solid fa-users" aria-hidden="true"></i> Todo el personal</span>
                                <span><i class="fa-regular fa-clock" aria-hidden="true"></i> 50 min</span>
                            </div>
                        </div>
                        <div class="evaluation-progress">
                            <div class="progress-heading">
                                <span>Progreso del curso</span>
                                <strong>47%</strong>
                            </div>
                            <div class="progress" role="progressbar" aria-label="Progreso de Comunicación efectiva" aria-valuenow="47" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: 47%"></div>
                            </div>
                        </div>
                        <p class="evaluation-availability-message message-locked">
                            <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            Debes finalizar el curso para realizar la evaluación.
                        </p>
                        <button class="button evaluation-action-button locked-action" type="button" disabled>
                            <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            Evaluación bloqueada
                        </button>
                    </article>
                </div>
            </section>
        </div>
<?php include __DIR__ . '/../includes/app-footer.php'; ?>
