<?php
$pageTitle = 'Cursos y contenido | CapacitaLíder';
$sectionTitle = 'Cursos y contenido';
$activePage = 'cursos';
$basePath = '../';
$pageStyle = 'css/cursos.css';

include __DIR__ . '/../includes/app-header.php';
?>
        <div class="courses-container">
            <section class="courses-hero" aria-labelledby="courses-title">
                <div class="courses-hero-copy">
                    <span class="section-eyebrow">
                        <i class="fa-solid fa-book-open" aria-hidden="true"></i>
                        Formación continua
                    </span>
                    <h2 id="courses-title">Cursos y contenido</h2>
                    <p>
                        Consulta los cursos disponibles, revisa tus avances y continúa tu capacitación
                        de manera sencilla desde un mismo lugar.
                    </p>
                </div>

                <div class="courses-hero-visual" aria-hidden="true">
                    <span class="hero-shape hero-shape-one"></span>
                    <span class="hero-shape hero-shape-two"></span>
                    <div class="hero-main-icon">
                        <i class="fa-solid fa-book-open-reader"></i>
                    </div>
                    <span class="hero-accent-icon"><i class="fa-solid fa-check"></i></span>
                </div>
            </section>

            <section class="courses-section" aria-labelledby="active-courses-title">
                <div class="section-heading-row">
                    <div>
                        <span class="section-label">Continúa aprendiendo</span>
                        <h2 id="active-courses-title">Cursos activos</h2>
                        <p>Retoma tus cursos pendientes o continúa desde tu último avance.</p>
                    </div>
                    <span class="section-count">3 cursos</span>
                </div>

                <div class="active-courses-grid">
                    <article class="course-card active-course-card">
                        <div class="course-card-top">
                            <span class="course-icon course-icon-cyan">
                                <i class="fa-solid fa-helmet-safety" aria-hidden="true"></i>
                            </span>
                            <span class="status-badge status-progress">
                                <i class="fa-solid fa-circle-play" aria-hidden="true"></i>
                                En progreso
                            </span>
                        </div>
                        <div class="course-card-content">
                            <span class="course-category">Seguridad</span>
                            <h3>Seguridad industrial básica</h3>
                            <p>Conoce las medidas esenciales para prevenir riesgos dentro del área de trabajo.</p>
                            <dl class="course-details">
                                <div>
                                    <dt>Clasificación</dt>
                                    <dd>Prevención y seguridad</dd>
                                </div>
                                <div>
                                    <dt>Puesto</dt>
                                    <dd>Operador</dd>
                                </div>
                            </dl>
                        </div>
                        <div class="course-progress">
                            <div class="progress-heading">
                                <span>Avance</span>
                                <strong>65%</strong>
                            </div>
                            <div class="progress" role="progressbar" aria-label="Avance de Seguridad industrial básica" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: 65%"></div>
                            </div>
                        </div>
                        <a class="button course-button" href="contenidoCurso.php">
                            <span>Continuar</span>
                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </article>

                    <article class="course-card active-course-card">
                        <div class="course-card-top">
                            <span class="course-icon course-icon-purple">
                                <i class="fa-solid fa-magnifying-glass-chart" aria-hidden="true"></i>
                            </span>
                            <span class="status-badge status-progress">
                                <i class="fa-solid fa-circle-play" aria-hidden="true"></i>
                                En progreso
                            </span>
                        </div>
                        <div class="course-card-content">
                            <span class="course-category">Calidad</span>
                            <h3>Calidad en el proceso</h3>
                            <p>Identifica puntos clave para mantener la calidad y reducir errores durante la operación.</p>
                            <dl class="course-details">
                                <div>
                                    <dt>Clasificación</dt>
                                    <dd>Procesos y calidad</dd>
                                </div>
                                <div>
                                    <dt>Puesto</dt>
                                    <dd>Operador</dd>
                                </div>
                            </dl>
                        </div>
                        <div class="course-progress">
                            <div class="progress-heading">
                                <span>Avance</span>
                                <strong>30%</strong>
                            </div>
                            <div class="progress" role="progressbar" aria-label="Avance de Calidad en el proceso" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: 30%"></div>
                            </div>
                        </div>
                        <a class="button course-button" href="contenidoCurso.php">
                            <span>Continuar</span>
                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </article>

                    <article class="course-card active-course-card">
                        <div class="course-card-top">
                            <span class="course-icon course-icon-gold">
                                <i class="fa-solid fa-comments" aria-hidden="true"></i>
                            </span>
                            <span class="status-badge status-pending">
                                <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                Pendiente
                            </span>
                        </div>
                        <div class="course-card-content">
                            <span class="course-category">Habilidades</span>
                            <h3>Comunicación efectiva</h3>
                            <p>Fortalece la comunicación diaria para colaborar mejor con tu equipo de trabajo.</p>
                            <dl class="course-details">
                                <div>
                                    <dt>Clasificación</dt>
                                    <dd>Habilidades blandas</dd>
                                </div>
                                <div>
                                    <dt>Puesto</dt>
                                    <dd>Todo el personal</dd>
                                </div>
                            </dl>
                        </div>
                        <div class="course-progress">
                            <div class="progress-heading">
                                <span>Avance</span>
                                <strong>0%</strong>
                            </div>
                            <div class="progress" role="progressbar" aria-label="Avance de Comunicación efectiva" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: 0%"></div>
                            </div>
                        </div>
                        <a class="button course-button" href="contenidoCurso.php">
                            <span>Iniciar</span>
                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </article>
                </div>
            </section>

            <section class="courses-section catalog-section" aria-labelledby="all-courses-title">
                <div class="section-heading-row catalog-heading">
                    <div>
                        <span class="section-label">Catálogo de capacitación</span>
                        <h2 id="all-courses-title">Todos los cursos</h2>
                        <p>Explora el contenido disponible de acuerdo con tu puesto e intereses.</p>
                    </div>
                </div>

                <form class="course-filters" aria-label="Filtros de cursos">
                    <div class="filter-field filter-search">
                        <label for="course-search">Buscar curso</label>
                        <div class="filter-control">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            <input id="course-search" class="form-control" type="search" placeholder="Ej. Seguridad industrial">
                        </div>
                    </div>

                    <div class="filter-field">
                        <label for="course-classification">Clasificación</label>
                        <div class="filter-control">
                            <i class="fa-solid fa-tags" aria-hidden="true"></i>
                            <select id="course-classification" class="form-select">
                                <option selected>Todas</option>
                                <option>Seguridad</option>
                                <option>Calidad</option>
                                <option>Habilidades</option>
                                <option>Liderazgo</option>
                            </select>
                        </div>
                    </div>

                    <div class="filter-field">
                        <label for="course-position">Puesto</label>
                        <div class="filter-control">
                            <i class="fa-solid fa-user-tag" aria-hidden="true"></i>
                            <select id="course-position" class="form-select">
                                <option selected>Todos los puestos</option>
                                <option>Operador</option>
                                <option>Líder</option>
                                <option>Capacitador</option>
                                <option>Supervisor</option>
                            </select>
                        </div>
                    </div>

                    <div class="filter-field">
                        <label for="course-order">Ordenar por nombre</label>
                        <div class="filter-control">
                            <i class="fa-solid fa-arrow-down-a-z" aria-hidden="true"></i>
                            <select id="course-order" class="form-select">
                                <option selected>A-Z</option>
                                <option>Z-A</option>
                            </select>
                        </div>
                    </div>
                </form>

                <div class="catalog-grid">
                    <article class="course-card catalog-card">
                        <div class="course-card-top">
                            <span class="course-icon course-icon-cyan"><i class="fa-solid fa-person-circle-check" aria-hidden="true"></i></span>
                            <span class="availability-badge">Disponible</span>
                        </div>
                        <div class="course-card-content">
                            <span class="course-category">Inducción</span>
                            <h3>Inducción a la operación</h3>
                            <p>Conoce los procesos, normas y responsabilidades principales del área operativa.</p>
                            <div class="catalog-meta">
                                <span><i class="fa-solid fa-user" aria-hidden="true"></i> Operador</span>
                                <span><i class="fa-regular fa-clock" aria-hidden="true"></i> 45 min</span>
                            </div>
                        </div>
                        <a class="button catalog-button" href="contenidoCurso.php">Ver curso</a>
                    </article>

                    <article class="course-card catalog-card">
                        <div class="course-card-top">
                            <span class="course-icon course-icon-gold"><i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i></span>
                            <span class="availability-badge badge-recommended">Recomendado</span>
                        </div>
                        <div class="course-card-content">
                            <span class="course-category">Seguridad</span>
                            <h3>Manejo seguro de materiales</h3>
                            <p>Aplica prácticas seguras durante la manipulación, traslado y almacenamiento de materiales.</p>
                            <div class="catalog-meta">
                                <span><i class="fa-solid fa-user" aria-hidden="true"></i> Operador</span>
                                <span><i class="fa-regular fa-clock" aria-hidden="true"></i> 1 h 20 min</span>
                            </div>
                        </div>
                        <a class="button catalog-button" href="contenidoCurso.php">Iniciar</a>
                    </article>

                    <article class="course-card catalog-card">
                        <div class="course-card-top">
                            <span class="course-icon course-icon-purple"><i class="fa-solid fa-broom" aria-hidden="true"></i></span>
                            <span class="availability-badge">Disponible</span>
                        </div>
                        <div class="course-card-content">
                            <span class="course-category">Calidad</span>
                            <h3>Metodología 5S</h3>
                            <p>Organiza tu espacio de trabajo para mejorar la seguridad, el orden y la productividad.</p>
                            <div class="catalog-meta">
                                <span><i class="fa-solid fa-user" aria-hidden="true"></i> Todo el personal</span>
                                <span><i class="fa-regular fa-clock" aria-hidden="true"></i> 55 min</span>
                            </div>
                        </div>
                        <a class="button catalog-button" href="contenidoCurso.php">Ver curso</a>
                    </article>

                    <article class="course-card catalog-card">
                        <div class="course-card-top">
                            <span class="course-icon course-icon-orange"><i class="fa-solid fa-lightbulb" aria-hidden="true"></i></span>
                            <span class="availability-badge badge-new">Nuevo</span>
                        </div>
                        <div class="course-card-content">
                            <span class="course-category">Habilidades</span>
                            <h3>Solución de problemas</h3>
                            <p>Desarrolla un proceso sencillo para analizar situaciones y tomar mejores decisiones.</p>
                            <div class="catalog-meta">
                                <span><i class="fa-solid fa-user" aria-hidden="true"></i> Todo el personal</span>
                                <span><i class="fa-regular fa-clock" aria-hidden="true"></i> 1 h</span>
                            </div>
                        </div>
                        <a class="button catalog-button" href="contenidoCurso.php">Iniciar</a>
                    </article>

                    <article class="course-card catalog-card">
                        <div class="course-card-top">
                            <span class="course-icon course-icon-cyan"><i class="fa-solid fa-people-group" aria-hidden="true"></i></span>
                            <span class="availability-badge">Disponible</span>
                        </div>
                        <div class="course-card-content">
                            <span class="course-category">Liderazgo</span>
                            <h3>Liderazgo de equipos</h3>
                            <p>Aprende herramientas prácticas para orientar, comunicar y acompañar a tu equipo.</p>
                            <div class="catalog-meta">
                                <span><i class="fa-solid fa-user" aria-hidden="true"></i> Líder</span>
                                <span><i class="fa-regular fa-clock" aria-hidden="true"></i> 1 h 30 min</span>
                            </div>
                        </div>
                        <a class="button catalog-button" href="contenidoCurso.php">Ver curso</a>
                    </article>

                    <article class="course-card catalog-card">
                        <div class="course-card-top">
                            <span class="course-icon course-icon-purple"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></span>
                            <span class="availability-badge badge-new">Nuevo</span>
                        </div>
                        <div class="course-card-content">
                            <span class="course-category">Cumplimiento</span>
                            <h3>Protección de la información</h3>
                            <p>Identifica buenas prácticas para cuidar la información y evitar riesgos innecesarios.</p>
                            <div class="catalog-meta">
                                <span><i class="fa-solid fa-user" aria-hidden="true"></i> Todo el personal</span>
                                <span><i class="fa-regular fa-clock" aria-hidden="true"></i> 40 min</span>
                            </div>
                        </div>
                        <a class="button catalog-button" href="contenidoCurso.php">Iniciar</a>
                    </article>
                </div>
            </section>
        </div>
<?php include __DIR__ . '/../includes/app-footer.php'; ?>
