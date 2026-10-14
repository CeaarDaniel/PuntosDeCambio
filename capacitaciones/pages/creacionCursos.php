<?php
$pageTitle = 'Creación de cursos | CapacitaLíder';
$sectionTitle = 'Creación de cursos';
$activePage = 'creacionCursos';
$basePath = '../';
$pageVendorStyles = ['DataTables/datatables.min.css'];
$pageVendorScripts = ['DataTables/datatables.min.js'];
$pageStyle = 'css/creacionCursos.css';
$pageScript = 'js/creacionCursos.js';

include __DIR__ . '/../includes/app-header.php';
?>
        <div class="course-management-page">
            <section class="course-header-alternative" aria-labelledby="course-management-title">
                <div class="alternative-header-copy">
                    <span class="alternative-header-eyebrow"><i class="fa-solid fa-file-circle-plus" aria-hidden="true"></i> Administración de capacitación</span>
                    <h2 id="course-management-title">Creación y gestión de cursos</h2>
                    <p>Consulta los cursos creados y administra visualmente su información principal desde un solo lugar.</p>
                    <div class="alternative-header-actions">
                        <button id="add-course-button" class="button alternative-add-course-button" type="button"><i class="fa-solid fa-plus" aria-hidden="true"></i> Agregar curso</button>
                        <span><strong>7</strong> cursos creados</span>
                    </div>
                </div>
                <div class="alternative-header-watermark" aria-hidden="true">
                    <span class="alternative-watermark-icon"><i class="fa-solid fa-book-open"></i></span>
                    <span class="alternative-watermark-plus"><i class="fa-solid fa-plus"></i></span>
                </div>
            </section>

            <section class="course-management-filters" aria-labelledby="course-filters-title">
                <div class="course-filter-heading">
                    <span><i class="fa-solid fa-sliders" aria-hidden="true"></i></span>
                    <div>
                        <h2 id="course-filters-title">Buscar cursos</h2>
                        <p>Usa uno o varios filtros para localizar rápidamente un curso creado.</p>
                    </div>
                </div>

                <form id="course-filter-form" class="course-filter-form" action="#" method="get">
                    <div class="course-filter-field course-search-field">
                        <label for="course-search">Título del curso</label>
                        <div class="course-filter-control">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            <input id="course-search" class="form-control" type="search" placeholder="Ej. Seguridad industrial">
                        </div>
                    </div>
                    <div class="course-filter-field">
                        <label for="course-classification-filter">Clasificación</label>
                        <div class="course-filter-control">
                            <i class="fa-solid fa-tags" aria-hidden="true"></i>
                            <select id="course-classification-filter" class="form-select">
                                <option value="">Todas</option>
                                <option value="seguridad">Seguridad</option>
                                <option value="calidad">Calidad</option>
                                <option value="mejora-continua">Mejora continua</option>
                                <option value="habilidades-blandas">Habilidades blandas</option>
                                <option value="mantenimiento">Mantenimiento</option>
                                <option value="induccion">Inducción</option>
                            </select>
                        </div>
                    </div>
                    <div class="course-filter-field">
                        <label for="course-position-filter">Puesto</label>
                        <div class="course-filter-control">
                            <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
                            <select id="course-position-filter" class="form-select">
                                <option value="">Todos</option>
                                <option value="operador-produccion">Operador de producción</option>
                                <option value="inspector-calidad">Inspector de calidad</option>
                                <option value="lider-celula">Líder de célula</option>
                                <option value="tecnico-mantenimiento">Técnico de mantenimiento</option>
                                <option value="auxiliar-logistica">Auxiliar de logística</option>
                                <option value="operador-ensamble">Operador de ensamble</option>
                            </select>
                        </div>
                    </div>
                    <div class="course-filter-field">
                        <label for="course-status-filter">Estatus</label>
                        <div class="course-filter-control">
                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                            <select id="course-status-filter" class="form-select">
                                <option value="">Todos</option>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                                <option value="borrador">Borrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="course-filter-actions">
                        <button id="search-courses-button" class="button course-search-button" type="button"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Buscar</button>
                        <button class="button course-clear-button" type="reset"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Limpiar filtros</button>
                    </div>
                </form>
            </section>

            <section class="created-courses-section" aria-labelledby="created-courses-title">
                <div class="created-courses-heading">
                    <div>
                        <span class="created-courses-label">Catálogo administrativo</span>
                        <h2 id="created-courses-title">Cursos creados</h2>
                        <p>Revisa los datos generales y selecciona la acción que deseas realizar.</p>
                    </div>
                    <span id="course-count" class="created-course-count"><i class="fa-solid fa-book" aria-hidden="true"></i> 7 cursos</span>
                </div>

                <div class="created-courses-table-panel">
                    <table id="created-courses-table" class="table align-middle created-courses-table">
                        <caption class="visually-hidden">Listado de cursos creados</caption>
                        <thead>
                            <tr>
                                <th scope="col">Título</th>
                                <th scope="col">Descripción breve</th>
                                <th scope="col">Clasificación</th>
                                <th scope="col">Puesto</th>
                                <th scope="col">Duración</th>
                                <th scope="col">Estatus</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td data-search="Seguridad industrial básica"><div class="table-course-title"><span class="table-course-icon icon-cyan"><i class="fa-solid fa-helmet-safety" aria-hidden="true"></i></span><strong>Seguridad industrial básica</strong></div></td>
                                <td>Identificación de riesgos y procedimientos para realizar actividades de forma segura.</td>
                                <td><span class="course-classification classification-safety">Seguridad</span></td>
                                <td>Operador de producción</td>
                                <td><span class="course-duration"><i class="fa-regular fa-clock" aria-hidden="true"></i> 2 h 30 min</span></td>
                                <td><span class="course-status status-active"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Activo</span></td>
                                <td>
                                    <div class="course-table-actions">
                                        <button class="course-action course-edit-action" type="button" title="Editar curso" aria-label="Editar Seguridad industrial básica" data-course-title="Seguridad industrial básica" data-course-description="Identificación de riesgos y procedimientos para realizar actividades de forma segura." data-course-classification="seguridad" data-course-position="operador-produccion" data-course-duration="2 h 30 min" data-course-status="activo" data-course-icon="fa-helmet-safety" data-course-objective="Fortalecer la prevención de riesgos y el cumplimiento de procedimientos seguros."><i class="fa-solid fa-pen" aria-hidden="true"></i><span>Editar</span></button>
                                        <a class="course-action course-content-action" href="edicionCurso.php" title="Editar contenido" aria-label="Editar contenido de Seguridad industrial básica"><i class="fa-solid fa-eye" aria-hidden="true"></i><span>Contenido</span></a>
                                        <a class="course-action course-evaluation-action" href="edicionEvaluacion.php" title="Editar evaluación" aria-label="Editar evaluación de Seguridad industrial básica"><i class="fa-solid fa-clipboard-question" aria-hidden="true"></i><span>Evaluación</span></a>
                                        <button class="course-action course-delete-action" type="button" title="Eliminar curso" aria-label="Eliminar Seguridad industrial básica" data-course-title="Seguridad industrial básica"><i class="fa-solid fa-trash-can" aria-hidden="true"></i><span>Eliminar</span></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td data-search="Fundamentos de calidad"><div class="table-course-title"><span class="table-course-icon icon-purple"><i class="fa-solid fa-medal" aria-hidden="true"></i></span><strong>Fundamentos de calidad</strong></div></td>
                                <td>Principios básicos para prevenir defectos y mantener los estándares del proceso.</td>
                                <td><span class="course-classification classification-quality">Calidad</span></td>
                                <td>Inspector de calidad</td>
                                <td><span class="course-duration"><i class="fa-regular fa-clock" aria-hidden="true"></i> 1 h 45 min</span></td>
                                <td><span class="course-status status-active"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Activo</span></td>
                                <td>
                                    <div class="course-table-actions">
                                        <button class="course-action course-edit-action" type="button" title="Editar curso" aria-label="Editar Fundamentos de calidad" data-course-title="Fundamentos de calidad" data-course-description="Principios básicos para prevenir defectos y mantener los estándares del proceso." data-course-classification="calidad" data-course-position="inspector-calidad" data-course-duration="1 h 45 min" data-course-status="activo" data-course-icon="fa-medal" data-course-objective="Comprender y aplicar criterios esenciales de calidad durante la operación."><i class="fa-solid fa-pen" aria-hidden="true"></i><span>Editar</span></button>
                                        <a class="course-action course-content-action" href="edicionCurso.php" title="Editar contenido" aria-label="Editar contenido de Fundamentos de calidad"><i class="fa-solid fa-eye" aria-hidden="true"></i><span>Contenido</span></a>
                                        <a class="course-action course-evaluation-action" href="edicionEvaluacion.php" title="Editar evaluación" aria-label="Editar evaluación de Fundamentos de calidad"><i class="fa-solid fa-clipboard-question" aria-hidden="true"></i><span>Evaluación</span></a>
                                        <button class="course-action course-delete-action" type="button" title="Eliminar curso" aria-label="Eliminar Fundamentos de calidad" data-course-title="Fundamentos de calidad"><i class="fa-solid fa-trash-can" aria-hidden="true"></i><span>Eliminar</span></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td data-search="Metodología 5S"><div class="table-course-title"><span class="table-course-icon icon-orange"><i class="fa-solid fa-broom" aria-hidden="true"></i></span><strong>Metodología 5S</strong></div></td>
                                <td>Orden, limpieza y disciplina para mejorar el entorno de trabajo y la productividad.</td>
                                <td><span class="course-classification classification-improvement">Mejora continua</span></td>
                                <td>Operador de producción</td>
                                <td><span class="course-duration"><i class="fa-regular fa-clock" aria-hidden="true"></i> 2 h</span></td>
                                <td><span class="course-status status-draft"><i class="fa-solid fa-pen-ruler" aria-hidden="true"></i> Borrador</span></td>
                                <td>
                                    <div class="course-table-actions">
                                        <button class="course-action course-edit-action" type="button" title="Editar curso" aria-label="Editar Metodología 5S" data-course-title="Metodología 5S" data-course-description="Orden, limpieza y disciplina para mejorar el entorno de trabajo y la productividad." data-course-classification="mejora-continua" data-course-position="operador-produccion" data-course-duration="2 h" data-course-status="borrador" data-course-icon="fa-broom" data-course-objective="Aplicar los principios de 5S para conservar un área ordenada y eficiente."><i class="fa-solid fa-pen" aria-hidden="true"></i><span>Editar</span></button>
                                        <a class="course-action course-content-action" href="edicionCurso.php" title="Editar contenido" aria-label="Editar contenido de Metodología 5S"><i class="fa-solid fa-eye" aria-hidden="true"></i><span>Contenido</span></a>
                                        <a class="course-action course-evaluation-action" href="edicionEvaluacion.php" title="Editar evaluación" aria-label="Editar evaluación de Metodología 5S"><i class="fa-solid fa-clipboard-question" aria-hidden="true"></i><span>Evaluación</span></a>
                                        <button class="course-action course-delete-action" type="button" title="Eliminar curso" aria-label="Eliminar Metodología 5S" data-course-title="Metodología 5S"><i class="fa-solid fa-trash-can" aria-hidden="true"></i><span>Eliminar</span></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td data-search="Comunicación efectiva"><div class="table-course-title"><span class="table-course-icon icon-pink"><i class="fa-solid fa-comments" aria-hidden="true"></i></span><strong>Comunicación efectiva</strong></div></td>
                                <td>Herramientas prácticas para compartir instrucciones y retroalimentación con el equipo.</td>
                                <td><span class="course-classification classification-soft">Habilidades blandas</span></td>
                                <td>Líder de célula</td>
                                <td><span class="course-duration"><i class="fa-regular fa-clock" aria-hidden="true"></i> 1 h 30 min</span></td>
                                <td><span class="course-status status-active"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Activo</span></td>
                                <td>
                                    <div class="course-table-actions">
                                        <button class="course-action course-edit-action" type="button" title="Editar curso" aria-label="Editar Comunicación efectiva" data-course-title="Comunicación efectiva" data-course-description="Herramientas prácticas para compartir instrucciones y retroalimentación con el equipo." data-course-classification="habilidades-blandas" data-course-position="lider-celula" data-course-duration="1 h 30 min" data-course-status="activo" data-course-icon="fa-comments" data-course-objective="Mejorar la claridad de la comunicación y la colaboración del equipo."><i class="fa-solid fa-pen" aria-hidden="true"></i><span>Editar</span></button>
                                        <a class="course-action course-content-action" href="edicionCurso.php" title="Editar contenido" aria-label="Editar contenido de Comunicación efectiva"><i class="fa-solid fa-eye" aria-hidden="true"></i><span>Contenido</span></a>
                                        <a class="course-action course-evaluation-action" href="edicionEvaluacion.php" title="Editar evaluación" aria-label="Editar evaluación de Comunicación efectiva"><i class="fa-solid fa-clipboard-question" aria-hidden="true"></i><span>Evaluación</span></a>
                                        <button class="course-action course-delete-action" type="button" title="Eliminar curso" aria-label="Eliminar Comunicación efectiva" data-course-title="Comunicación efectiva"><i class="fa-solid fa-trash-can" aria-hidden="true"></i><span>Eliminar</span></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td data-search="Mantenimiento autónomo"><div class="table-course-title"><span class="table-course-icon icon-gold"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i></span><strong>Mantenimiento autónomo</strong></div></td>
                                <td>Rutinas básicas de inspección y cuidado preventivo de equipos productivos.</td>
                                <td><span class="course-classification classification-maintenance">Mantenimiento</span></td>
                                <td>Técnico de mantenimiento</td>
                                <td><span class="course-duration"><i class="fa-regular fa-clock" aria-hidden="true"></i> 3 h</span></td>
                                <td><span class="course-status status-inactive"><i class="fa-solid fa-circle-minus" aria-hidden="true"></i> Inactivo</span></td>
                                <td>
                                    <div class="course-table-actions">
                                        <button class="course-action course-edit-action" type="button" title="Editar curso" aria-label="Editar Mantenimiento autónomo" data-course-title="Mantenimiento autónomo" data-course-description="Rutinas básicas de inspección y cuidado preventivo de equipos productivos." data-course-classification="mantenimiento" data-course-position="tecnico-mantenimiento" data-course-duration="3 h" data-course-status="inactivo" data-course-icon="fa-screwdriver-wrench" data-course-objective="Desarrollar hábitos de inspección que ayuden a prevenir fallas operativas."><i class="fa-solid fa-pen" aria-hidden="true"></i><span>Editar</span></button>
                                        <a class="course-action course-content-action" href="edicionCurso.php" title="Editar contenido" aria-label="Editar contenido de Mantenimiento autónomo"><i class="fa-solid fa-eye" aria-hidden="true"></i><span>Contenido</span></a>
                                        <a class="course-action course-evaluation-action" href="edicionEvaluacion.php" title="Editar evaluación" aria-label="Editar evaluación de Mantenimiento autónomo"><i class="fa-solid fa-clipboard-question" aria-hidden="true"></i><span>Evaluación</span></a>
                                        <button class="course-action course-delete-action" type="button" title="Eliminar curso" aria-label="Eliminar Mantenimiento autónomo" data-course-title="Mantenimiento autónomo"><i class="fa-solid fa-trash-can" aria-hidden="true"></i><span>Eliminar</span></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td data-search="Manejo seguro de materiales"><div class="table-course-title"><span class="table-course-icon icon-success"><i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i></span><strong>Manejo seguro de materiales</strong></div></td>
                                <td>Prácticas para movilizar, almacenar y controlar materiales sin generar riesgos.</td>
                                <td><span class="course-classification classification-safety">Seguridad</span></td>
                                <td>Auxiliar de logística</td>
                                <td><span class="course-duration"><i class="fa-regular fa-clock" aria-hidden="true"></i> 2 h 15 min</span></td>
                                <td><span class="course-status status-active"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Activo</span></td>
                                <td>
                                    <div class="course-table-actions">
                                        <button class="course-action course-edit-action" type="button" title="Editar curso" aria-label="Editar Manejo seguro de materiales" data-course-title="Manejo seguro de materiales" data-course-description="Prácticas para movilizar, almacenar y controlar materiales sin generar riesgos." data-course-classification="seguridad" data-course-position="auxiliar-logistica" data-course-duration="2 h 15 min" data-course-status="activo" data-course-icon="fa-boxes-stacked" data-course-objective="Reducir riesgos durante el manejo y almacenamiento de materiales."><i class="fa-solid fa-pen" aria-hidden="true"></i><span>Editar</span></button>
                                        <a class="course-action course-content-action" href="edicionCurso.php" title="Editar contenido" aria-label="Editar contenido de Manejo seguro de materiales"><i class="fa-solid fa-eye" aria-hidden="true"></i><span>Contenido</span></a>
                                        <a class="course-action course-evaluation-action" href="edicionEvaluacion.php" title="Editar evaluación" aria-label="Editar evaluación de Manejo seguro de materiales"><i class="fa-solid fa-clipboard-question" aria-hidden="true"></i><span>Evaluación</span></a>
                                        <button class="course-action course-delete-action" type="button" title="Eliminar curso" aria-label="Eliminar Manejo seguro de materiales" data-course-title="Manejo seguro de materiales"><i class="fa-solid fa-trash-can" aria-hidden="true"></i><span>Eliminar</span></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td data-search="Inducción al área de ensamble"><div class="table-course-title"><span class="table-course-icon icon-blue"><i class="fa-solid fa-industry" aria-hidden="true"></i></span><strong>Inducción al área de ensamble</strong></div></td>
                                <td>Introducción al flujo, responsabilidades y lineamientos generales del área.</td>
                                <td><span class="course-classification classification-induction">Inducción</span></td>
                                <td>Operador de ensamble</td>
                                <td><span class="course-duration"><i class="fa-regular fa-clock" aria-hidden="true"></i> 1 h</span></td>
                                <td><span class="course-status status-draft"><i class="fa-solid fa-pen-ruler" aria-hidden="true"></i> Borrador</span></td>
                                <td>
                                    <div class="course-table-actions">
                                        <button class="course-action course-edit-action" type="button" title="Editar curso" aria-label="Editar Inducción al área de ensamble" data-course-title="Inducción al área de ensamble" data-course-description="Introducción al flujo, responsabilidades y lineamientos generales del área." data-course-classification="induccion" data-course-position="operador-ensamble" data-course-duration="1 h" data-course-status="borrador" data-course-icon="fa-industry" data-course-objective="Facilitar la integración del personal nuevo a las actividades de ensamble."><i class="fa-solid fa-pen" aria-hidden="true"></i><span>Editar</span></button>
                                        <a class="course-action course-content-action" href="edicionCurso.php" title="Editar contenido" aria-label="Editar contenido de Inducción al área de ensamble"><i class="fa-solid fa-eye" aria-hidden="true"></i><span>Contenido</span></a>
                                        <a class="course-action course-evaluation-action" href="edicionEvaluacion.php" title="Editar evaluación" aria-label="Editar evaluación de Inducción al área de ensamble"><i class="fa-solid fa-clipboard-question" aria-hidden="true"></i><span>Evaluación</span></a>
                                        <button class="course-action course-delete-action" type="button" title="Eliminar curso" aria-label="Eliminar Inducción al área de ensamble" data-course-title="Inducción al área de ensamble"><i class="fa-solid fa-trash-can" aria-hidden="true"></i><span>Eliminar</span></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="created-course-grid" aria-label="Cursos creados en formato de tarjetas">
                    <article class="created-course-card accent-cyan" data-title="Seguridad industrial básica" data-classification="seguridad" data-position="operador-produccion" data-status="activo">
                        <div class="course-card-heading"><span class="course-card-icon icon-cyan"><i class="fa-solid fa-helmet-safety" aria-hidden="true"></i></span><span class="course-status status-active"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Activo</span></div>
                        <span class="course-classification classification-safety">Seguridad</span><h3>Seguridad industrial básica</h3><p>Identificación de riesgos y procedimientos para realizar actividades de forma segura.</p>
                        <div class="course-card-meta"><span><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Operador de producción</span><span><i class="fa-regular fa-clock" aria-hidden="true"></i> 2 h 30 min</span></div>
                        <div class="course-card-actions"><button class="course-action course-edit-action" type="button" data-course-title="Seguridad industrial básica" data-course-description="Identificación de riesgos y procedimientos para realizar actividades de forma segura." data-course-classification="seguridad" data-course-position="operador-produccion" data-course-duration="2 h 30 min" data-course-status="activo" data-course-icon="fa-helmet-safety" data-course-objective="Fortalecer la prevención de riesgos y el cumplimiento de procedimientos seguros."><i class="fa-solid fa-pen" aria-hidden="true"></i> Editar</button><a class="course-action course-content-action" href="edicionCurso.php"><i class="fa-solid fa-eye" aria-hidden="true"></i> Contenido</a><a class="course-action course-evaluation-action" href="edicionEvaluacion.php" aria-label="Editar evaluación de Seguridad industrial básica"><i class="fa-solid fa-clipboard-question" aria-hidden="true"></i> Evaluación</a><button class="course-action course-delete-action" type="button" data-course-title="Seguridad industrial básica"><i class="fa-solid fa-trash-can" aria-hidden="true"></i><span class="visually-hidden">Eliminar</span></button></div>
                    </article>
                    <article class="created-course-card accent-purple" data-title="Fundamentos de calidad" data-classification="calidad" data-position="inspector-calidad" data-status="activo">
                        <div class="course-card-heading"><span class="course-card-icon icon-purple"><i class="fa-solid fa-medal" aria-hidden="true"></i></span><span class="course-status status-active"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Activo</span></div>
                        <span class="course-classification classification-quality">Calidad</span><h3>Fundamentos de calidad</h3><p>Principios básicos para prevenir defectos y mantener los estándares del proceso.</p>
                        <div class="course-card-meta"><span><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Inspector de calidad</span><span><i class="fa-regular fa-clock" aria-hidden="true"></i> 1 h 45 min</span></div>
                        <div class="course-card-actions"><button class="course-action course-edit-action" type="button" data-course-title="Fundamentos de calidad" data-course-description="Principios básicos para prevenir defectos y mantener los estándares del proceso." data-course-classification="calidad" data-course-position="inspector-calidad" data-course-duration="1 h 45 min" data-course-status="activo" data-course-icon="fa-medal" data-course-objective="Comprender y aplicar criterios esenciales de calidad durante la operación."><i class="fa-solid fa-pen" aria-hidden="true"></i> Editar</button><a class="course-action course-content-action" href="edicionCurso.php"><i class="fa-solid fa-eye" aria-hidden="true"></i> Contenido</a><a class="course-action course-evaluation-action" href="edicionEvaluacion.php" aria-label="Editar evaluación de Fundamentos de calidad"><i class="fa-solid fa-clipboard-question" aria-hidden="true"></i> Evaluación</a><button class="course-action course-delete-action" type="button" data-course-title="Fundamentos de calidad"><i class="fa-solid fa-trash-can" aria-hidden="true"></i><span class="visually-hidden">Eliminar</span></button></div>
                    </article>
                    <article class="created-course-card accent-orange" data-title="Metodología 5S" data-classification="mejora-continua" data-position="operador-produccion" data-status="borrador">
                        <div class="course-card-heading"><span class="course-card-icon icon-orange"><i class="fa-solid fa-broom" aria-hidden="true"></i></span><span class="course-status status-draft"><i class="fa-solid fa-pen-ruler" aria-hidden="true"></i> Borrador</span></div>
                        <span class="course-classification classification-improvement">Mejora continua</span><h3>Metodología 5S</h3><p>Orden, limpieza y disciplina para mejorar el entorno de trabajo y la productividad.</p>
                        <div class="course-card-meta"><span><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Operador de producción</span><span><i class="fa-regular fa-clock" aria-hidden="true"></i> 2 h</span></div>
                        <div class="course-card-actions"><button class="course-action course-edit-action" type="button" data-course-title="Metodología 5S" data-course-description="Orden, limpieza y disciplina para mejorar el entorno de trabajo y la productividad." data-course-classification="mejora-continua" data-course-position="operador-produccion" data-course-duration="2 h" data-course-status="borrador" data-course-icon="fa-broom" data-course-objective="Aplicar los principios de 5S para conservar un área ordenada y eficiente."><i class="fa-solid fa-pen" aria-hidden="true"></i> Editar</button><a class="course-action course-content-action" href="edicionCurso.php"><i class="fa-solid fa-eye" aria-hidden="true"></i> Contenido</a><a class="course-action course-evaluation-action" href="edicionEvaluacion.php" aria-label="Editar evaluación de Metodología 5S"><i class="fa-solid fa-clipboard-question" aria-hidden="true"></i> Evaluación</a><button class="course-action course-delete-action" type="button" data-course-title="Metodología 5S"><i class="fa-solid fa-trash-can" aria-hidden="true"></i><span class="visually-hidden">Eliminar</span></button></div>
                    </article>
                    <article class="created-course-card accent-pink" data-title="Comunicación efectiva" data-classification="habilidades-blandas" data-position="lider-celula" data-status="activo">
                        <div class="course-card-heading"><span class="course-card-icon icon-pink"><i class="fa-solid fa-comments" aria-hidden="true"></i></span><span class="course-status status-active"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Activo</span></div>
                        <span class="course-classification classification-soft">Habilidades blandas</span><h3>Comunicación efectiva</h3><p>Herramientas prácticas para compartir instrucciones y retroalimentación con el equipo.</p>
                        <div class="course-card-meta"><span><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Líder de célula</span><span><i class="fa-regular fa-clock" aria-hidden="true"></i> 1 h 30 min</span></div>
                        <div class="course-card-actions"><button class="course-action course-edit-action" type="button" data-course-title="Comunicación efectiva" data-course-description="Herramientas prácticas para compartir instrucciones y retroalimentación con el equipo." data-course-classification="habilidades-blandas" data-course-position="lider-celula" data-course-duration="1 h 30 min" data-course-status="activo" data-course-icon="fa-comments" data-course-objective="Mejorar la claridad de la comunicación y la colaboración del equipo."><i class="fa-solid fa-pen" aria-hidden="true"></i> Editar</button><a class="course-action course-content-action" href="edicionCurso.php"><i class="fa-solid fa-eye" aria-hidden="true"></i> Contenido</a><a class="course-action course-evaluation-action" href="edicionEvaluacion.php" aria-label="Editar evaluación de Comunicación efectiva"><i class="fa-solid fa-clipboard-question" aria-hidden="true"></i> Evaluación</a><button class="course-action course-delete-action" type="button" data-course-title="Comunicación efectiva"><i class="fa-solid fa-trash-can" aria-hidden="true"></i><span class="visually-hidden">Eliminar</span></button></div>
                    </article>
                    <article class="created-course-card accent-gold" data-title="Mantenimiento autónomo" data-classification="mantenimiento" data-position="tecnico-mantenimiento" data-status="inactivo">
                        <div class="course-card-heading"><span class="course-card-icon icon-gold"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i></span><span class="course-status status-inactive"><i class="fa-solid fa-circle-minus" aria-hidden="true"></i> Inactivo</span></div>
                        <span class="course-classification classification-maintenance">Mantenimiento</span><h3>Mantenimiento autónomo</h3><p>Rutinas básicas de inspección y cuidado preventivo de equipos productivos.</p>
                        <div class="course-card-meta"><span><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Técnico de mantenimiento</span><span><i class="fa-regular fa-clock" aria-hidden="true"></i> 3 h</span></div>
                        <div class="course-card-actions"><button class="course-action course-edit-action" type="button" data-course-title="Mantenimiento autónomo" data-course-description="Rutinas básicas de inspección y cuidado preventivo de equipos productivos." data-course-classification="mantenimiento" data-course-position="tecnico-mantenimiento" data-course-duration="3 h" data-course-status="inactivo" data-course-icon="fa-screwdriver-wrench" data-course-objective="Desarrollar hábitos de inspección que ayuden a prevenir fallas operativas."><i class="fa-solid fa-pen" aria-hidden="true"></i> Editar</button><a class="course-action course-content-action" href="edicionCurso.php"><i class="fa-solid fa-eye" aria-hidden="true"></i> Contenido</a><a class="course-action course-evaluation-action" href="edicionEvaluacion.php" aria-label="Editar evaluación de Mantenimiento autónomo"><i class="fa-solid fa-clipboard-question" aria-hidden="true"></i> Evaluación</a><button class="course-action course-delete-action" type="button" data-course-title="Mantenimiento autónomo"><i class="fa-solid fa-trash-can" aria-hidden="true"></i><span class="visually-hidden">Eliminar</span></button></div>
                    </article>
                    <article class="created-course-card accent-success" data-title="Manejo seguro de materiales" data-classification="seguridad" data-position="auxiliar-logistica" data-status="activo">
                        <div class="course-card-heading"><span class="course-card-icon icon-success"><i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i></span><span class="course-status status-active"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Activo</span></div>
                        <span class="course-classification classification-safety">Seguridad</span><h3>Manejo seguro de materiales</h3><p>Prácticas para movilizar, almacenar y controlar materiales sin generar riesgos.</p>
                        <div class="course-card-meta"><span><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Auxiliar de logística</span><span><i class="fa-regular fa-clock" aria-hidden="true"></i> 2 h 15 min</span></div>
                        <div class="course-card-actions"><button class="course-action course-edit-action" type="button" data-course-title="Manejo seguro de materiales" data-course-description="Prácticas para movilizar, almacenar y controlar materiales sin generar riesgos." data-course-classification="seguridad" data-course-position="auxiliar-logistica" data-course-duration="2 h 15 min" data-course-status="activo" data-course-icon="fa-boxes-stacked" data-course-objective="Reducir riesgos durante el manejo y almacenamiento de materiales."><i class="fa-solid fa-pen" aria-hidden="true"></i> Editar</button><a class="course-action course-content-action" href="edicionCurso.php"><i class="fa-solid fa-eye" aria-hidden="true"></i> Contenido</a><a class="course-action course-evaluation-action" href="edicionEvaluacion.php" aria-label="Editar evaluación de Manejo seguro de materiales"><i class="fa-solid fa-clipboard-question" aria-hidden="true"></i> Evaluación</a><button class="course-action course-delete-action" type="button" data-course-title="Manejo seguro de materiales"><i class="fa-solid fa-trash-can" aria-hidden="true"></i><span class="visually-hidden">Eliminar</span></button></div>
                    </article>
                    <article class="created-course-card accent-blue" data-title="Inducción al área de ensamble" data-classification="induccion" data-position="operador-ensamble" data-status="borrador">
                        <div class="course-card-heading"><span class="course-card-icon icon-blue"><i class="fa-solid fa-industry" aria-hidden="true"></i></span><span class="course-status status-draft"><i class="fa-solid fa-pen-ruler" aria-hidden="true"></i> Borrador</span></div>
                        <span class="course-classification classification-induction">Inducción</span><h3>Inducción al área de ensamble</h3><p>Introducción al flujo, responsabilidades y lineamientos generales del área.</p>
                        <div class="course-card-meta"><span><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Operador de ensamble</span><span><i class="fa-regular fa-clock" aria-hidden="true"></i> 1 h</span></div>
                        <div class="course-card-actions"><button class="course-action course-edit-action" type="button" data-course-title="Inducción al área de ensamble" data-course-description="Introducción al flujo, responsabilidades y lineamientos generales del área." data-course-classification="induccion" data-course-position="operador-ensamble" data-course-duration="1 h" data-course-status="borrador" data-course-icon="fa-industry" data-course-objective="Facilitar la integración del personal nuevo a las actividades de ensamble."><i class="fa-solid fa-pen" aria-hidden="true"></i> Editar</button><a class="course-action course-content-action" href="edicionCurso.php"><i class="fa-solid fa-eye" aria-hidden="true"></i> Contenido</a><a class="course-action course-evaluation-action" href="edicionEvaluacion.php" aria-label="Editar evaluación de Inducción al área de ensamble"><i class="fa-solid fa-clipboard-question" aria-hidden="true"></i> Evaluación</a><button class="course-action course-delete-action" type="button" data-course-title="Inducción al área de ensamble"><i class="fa-solid fa-trash-can" aria-hidden="true"></i><span class="visually-hidden">Eliminar</span></button></div>
                    </article>
                </div>
                <div id="course-card-empty-state" class="course-card-empty-state" hidden><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><strong>No encontramos cursos</strong><p>Prueba con otros criterios o limpia los filtros.</p></div>
            </section>
        </div>

        <div class="modal fade course-form-modal" id="course-form-modal" tabindex="-1" aria-labelledby="course-form-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form id="course-form" novalidate>
                        <div class="modal-header">
                            <div class="course-modal-heading"><span><i class="fa-solid fa-file-circle-plus" aria-hidden="true"></i></span><div><small>Datos principales</small><h2 class="modal-title" id="course-form-modal-title">Agregar nuevo curso</h2></div></div>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <p class="course-modal-introduction">Completa la información general. Esta acción es únicamente visual y no almacenará datos.</p>
                            <div class="course-form-grid">
                                <div class="course-form-field course-form-field-full"><label for="course-title-input">Título del curso <span>*</span></label><input id="course-title-input" class="form-control" type="text" placeholder="Ej. Seguridad industrial básica" required><div class="invalid-feedback">Ingresa el título del curso.</div></div>
                                <div class="course-form-field course-form-field-full"><label for="course-description-input">Descripción breve <span>*</span></label><textarea id="course-description-input" class="form-control" rows="3" placeholder="Describe brevemente el contenido del curso" required></textarea><div class="invalid-feedback">Ingresa una descripción breve.</div></div>
                                <div class="course-form-field"><label for="course-classification-input">Clasificación <span>*</span></label><select id="course-classification-input" class="form-select" required><option value="">Selecciona una clasificación</option><option value="seguridad">Seguridad</option><option value="calidad">Calidad</option><option value="mejora-continua">Mejora continua</option><option value="habilidades-blandas">Habilidades blandas</option><option value="mantenimiento">Mantenimiento</option><option value="induccion">Inducción</option></select><div class="invalid-feedback">Selecciona una clasificación.</div></div>
                                <div class="course-form-field"><label for="course-position-input">Puesto al que aplica <span>*</span></label><select id="course-position-input" class="form-select" required><option value="">Selecciona un puesto</option><option value="operador-produccion">Operador de producción</option><option value="inspector-calidad">Inspector de calidad</option><option value="lider-celula">Líder de célula</option><option value="tecnico-mantenimiento">Técnico de mantenimiento</option><option value="auxiliar-logistica">Auxiliar de logística</option><option value="operador-ensamble">Operador de ensamble</option></select><div class="invalid-feedback">Selecciona un puesto.</div></div>
                                <div class="course-form-field"><label for="course-duration-input">Duración <span>*</span></label><div class="course-modal-control"><i class="fa-regular fa-clock" aria-hidden="true"></i><input id="course-duration-input" class="form-control" type="text" placeholder="Ej. 2 h 30 min" required></div><div class="invalid-feedback">Indica la duración estimada.</div></div>
                                <div class="course-form-field"><label for="course-status-input">Estatus</label><select id="course-status-input" class="form-select"><option value="borrador">Borrador</option><option value="activo">Activo</option><option value="inactivo">Inactivo</option></select></div>
                                <div class="course-form-field"><label for="course-icon-input">Ícono del curso</label><div class="course-icon-picker"><span id="course-icon-preview"><i class="fa-solid fa-book-open" aria-hidden="true"></i></span><select id="course-icon-input" class="form-select"><option value="fa-book-open">Libro</option><option value="fa-helmet-safety">Seguridad</option><option value="fa-medal">Calidad</option><option value="fa-broom">Orden y limpieza</option><option value="fa-screwdriver-wrench">Mantenimiento</option><option value="fa-comments">Comunicación</option><option value="fa-industry">Producción</option></select></div></div>
                                <div class="course-form-field course-form-field-full"><label for="course-objective-input">Objetivo del curso</label><textarea id="course-objective-input" class="form-control" rows="3" placeholder="Describe qué se espera lograr al finalizar el curso"></textarea></div>
                            </div>
                        </div>
                        <div class="modal-footer"><button class="button course-modal-cancel" type="button" data-bs-dismiss="modal">Cancelar</button><button id="save-course-button" class="button course-modal-save" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i><span>Guardar curso</span></button></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade course-delete-modal" id="course-delete-modal" tabindex="-1" aria-labelledby="course-delete-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body"><span class="course-delete-icon"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></span><h2 id="course-delete-title">¿Eliminar este curso?</h2><p>Esta acción es simulada. El curso <strong id="course-delete-name"></strong> permanecerá en el listado.</p></div>
                    <div class="modal-footer"><button class="button course-modal-cancel" type="button" data-bs-dismiss="modal">Cancelar</button><button id="confirm-course-delete" class="button course-delete-confirm" type="button"><i class="fa-solid fa-trash-can" aria-hidden="true"></i> Simular eliminación</button></div>
                </div>
            </div>
        </div>

        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="course-feedback-toast" class="toast course-feedback-toast" role="status" aria-live="polite" aria-atomic="true">
                <div class="toast-body"><span><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span><p id="course-feedback-message">Curso registrado correctamente.</p><button class="btn-close" type="button" data-bs-dismiss="toast" aria-label="Cerrar"></button></div>
            </div>
        </div>
<?php include __DIR__ . '/../includes/app-footer.php'; ?>
