<?php
$pageTitle = 'Edición de contenido | CapacitaLíder';
$sectionTitle = 'Edición de contenido';
$activePage = 'creacionCursos';
$basePath = '../';
$pageVendorStyles = ['css/contenidoCurso.css'];
$pageStyle = 'css/edicionCurso.css';
$pageScript = 'js/edicionCurso.js';

include __DIR__ . '/../includes/app-header.php';
?>
        <div class="course-editor-page">
            <section class="course-editor-hero" aria-labelledby="course-editor-title">
                <div class="course-editor-hero-copy">
                    <span class="course-editor-eyebrow">
                        <i class="fa-solid fa-pen-ruler" aria-hidden="true"></i>
                        Edición de contenido
                    </span>
                    <h2 id="course-editor-title">Seguridad industrial básica</h2>
                    <p>Organiza los módulos, actividades y recursos que verá el personal durante su capacitación.</p>
                    <div class="course-editor-hero-meta" aria-label="Información del curso">
                        <span><i class="fa-solid fa-layer-group" aria-hidden="true"></i> 5 módulos</span>
                        <span><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Operador</span>
                        <span class="course-editor-status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Activo</span>
                    </div>
                </div>

                <div class="course-editor-hero-actions" aria-label="Acciones del curso">
                    <a class="editor-button editor-button-light" href="creacionCursos.php">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                        Volver a cursos
                    </a>
                    <a class="editor-button editor-button-light" href="contenidoCurso.php" target="_blank" rel="noopener">
                        <i class="fa-regular fa-eye" aria-hidden="true"></i>
                        Vista previa
                    </a>
                    <button id="save-course-content" class="editor-button editor-button-primary" type="button">
                        <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                        Guardar cambios
                    </button>
                </div>

                <span class="course-editor-hero-watermark" aria-hidden="true">
                    <i class="fa-solid fa-book-open-reader"></i>
                </span>
            </section>

            <div class="course-editor-layout">
                <aside class="editor-modules-panel" aria-labelledby="editor-modules-title">
                    <div class="editor-panel-heading">
                        <div>
                            <span>Estructura del curso</span>
                            <h2 id="editor-modules-title">Módulos</h2>
                        </div>
                        <span class="editor-count" aria-label="5 módulos">5</span>
                    </div>

                    <p class="editor-panel-help">Selecciona un módulo para editar su contenido.</p>

                    <ol id="editor-module-list" class="editor-module-list">
                        <li class="editor-module-card is-complete" data-module-index="0">
                            <button class="editor-module-select" type="button"
                                    data-module-index="0"
                                    data-module-title="Introducción a la seguridad industrial"
                                    data-module-label="Módulo 1 · Introducción"
                                    data-module-description="Reconoce la importancia de prevenir riesgos antes de iniciar una actividad."
                                    data-module-status="completo"
                                    data-module-icon="fa-person-chalkboard"
                                    aria-label="Editar módulo 1, Introducción a la seguridad industrial">
                                <span class="editor-module-number">1</span>
                                <span class="editor-module-copy">
                                    <small>Módulo 1</small>
                                    <strong>Introducción</strong>
                                    <span class="editor-module-state"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Completo</span>
                                </span>
                            </button>
                            <div class="editor-module-actions" aria-label="Acciones del módulo 1">
                                <button class="editor-module-action" type="button" data-action="edit" data-module-index="0" data-module-title="Introducción" aria-label="Editar datos del módulo Introducción"><i class="fa-solid fa-pen" aria-hidden="true"></i></button>
                                <button class="editor-module-action" type="button" data-action="duplicate" data-module-index="0" data-module-title="Introducción" aria-label="Duplicar módulo Introducción"><i class="fa-regular fa-copy" aria-hidden="true"></i></button>
                                <button class="editor-module-action is-danger" type="button" data-action="delete" data-module-index="0" data-module-title="Introducción" aria-label="Eliminar módulo Introducción"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
                            </div>
                        </li>

                        <li class="editor-module-card is-active is-editing" data-module-index="1">
                            <button class="editor-module-select" type="button"
                                    data-module-index="1"
                                    data-module-title="Procedimiento básico de trabajo seguro"
                                    data-module-label="Módulo 2 · Procedimiento"
                                    data-module-description="Presenta la secuencia que debe seguirse antes, durante y después de una actividad operativa."
                                    data-module-status="en-edicion"
                                    data-module-icon="fa-route"
                                    aria-current="true"
                                    aria-label="Editando módulo 2, Procedimiento básico">
                                <span class="editor-module-number">2</span>
                                <span class="editor-module-copy">
                                    <small>Módulo 2</small>
                                    <strong>Procedimiento básico</strong>
                                    <span class="editor-module-state"><i class="fa-solid fa-pen" aria-hidden="true"></i> En edición</span>
                                </span>
                            </button>
                            <div class="editor-module-actions" aria-label="Acciones del módulo 2">
                                <button class="editor-module-action" type="button" data-action="edit" data-module-index="1" data-module-title="Procedimiento básico" aria-label="Editar datos del módulo Procedimiento básico"><i class="fa-solid fa-pen" aria-hidden="true"></i></button>
                                <button class="editor-module-action" type="button" data-action="duplicate" data-module-index="1" data-module-title="Procedimiento básico" aria-label="Duplicar módulo Procedimiento básico"><i class="fa-regular fa-copy" aria-hidden="true"></i></button>
                                <button class="editor-module-action is-danger" type="button" data-action="delete" data-module-index="1" data-module-title="Procedimiento básico" aria-label="Eliminar módulo Procedimiento básico"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
                            </div>
                        </li>

                        <li class="editor-module-card is-complete" data-module-index="2">
                            <button class="editor-module-select" type="button"
                                    data-module-index="2"
                                    data-module-title="Buenas prácticas en el área de trabajo"
                                    data-module-label="Módulo 3 · Buenas prácticas"
                                    data-module-description="Refuerza acciones sencillas para mantener un entorno ordenado y seguro."
                                    data-module-status="completo"
                                    data-module-icon="fa-shield-halved"
                                    aria-label="Editar módulo 3, Buenas prácticas">
                                <span class="editor-module-number">3</span>
                                <span class="editor-module-copy">
                                    <small>Módulo 3</small>
                                    <strong>Buenas prácticas</strong>
                                    <span class="editor-module-state"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Completo</span>
                                </span>
                            </button>
                            <div class="editor-module-actions" aria-label="Acciones del módulo 3">
                                <button class="editor-module-action" type="button" data-action="edit" data-module-index="2" data-module-title="Buenas prácticas" aria-label="Editar datos del módulo Buenas prácticas"><i class="fa-solid fa-pen" aria-hidden="true"></i></button>
                                <button class="editor-module-action" type="button" data-action="duplicate" data-module-index="2" data-module-title="Buenas prácticas" aria-label="Duplicar módulo Buenas prácticas"><i class="fa-regular fa-copy" aria-hidden="true"></i></button>
                                <button class="editor-module-action is-danger" type="button" data-action="delete" data-module-index="2" data-module-title="Buenas prácticas" aria-label="Eliminar módulo Buenas prácticas"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
                            </div>
                        </li>

                        <li class="editor-module-card is-pending" data-module-index="3">
                            <button class="editor-module-select" type="button"
                                    data-module-index="3"
                                    data-module-title="Validación de conocimientos"
                                    data-module-label="Módulo 4 · Validación"
                                    data-module-description="Permite confirmar los conceptos esenciales antes de realizar la evaluación."
                                    data-module-status="pendiente"
                                    data-module-icon="fa-clipboard-question"
                                    aria-label="Editar módulo 4, Validación">
                                <span class="editor-module-number">4</span>
                                <span class="editor-module-copy">
                                    <small>Módulo 4</small>
                                    <strong>Validación</strong>
                                    <span class="editor-module-state"><i class="fa-regular fa-clock" aria-hidden="true"></i> Pendiente</span>
                                </span>
                            </button>
                            <div class="editor-module-actions" aria-label="Acciones del módulo 4">
                                <button class="editor-module-action" type="button" data-action="edit" data-module-index="3" data-module-title="Validación" aria-label="Editar datos del módulo Validación"><i class="fa-solid fa-pen" aria-hidden="true"></i></button>
                                <button class="editor-module-action" type="button" data-action="duplicate" data-module-index="3" data-module-title="Validación" aria-label="Duplicar módulo Validación"><i class="fa-regular fa-copy" aria-hidden="true"></i></button>
                                <button class="editor-module-action is-danger" type="button" data-action="delete" data-module-index="3" data-module-title="Validación" aria-label="Eliminar módulo Validación"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
                            </div>
                        </li>

                        <li class="editor-module-card is-pending is-quiz-module" data-module-index="4">
                            <button class="editor-module-select" type="button"
                                    data-module-index="4"
                                    data-module-title="Cuestionario de prueba"
                                    data-module-label="Módulo 5 · Repaso"
                                    data-module-description="Cuestionario breve para repasar el contenido antes de la evaluación real."
                                    data-module-status="pendiente"
                                    data-module-icon="fa-list-check"
                                    aria-label="Editar módulo 5, Cuestionario de prueba">
                                <span class="editor-module-number">5</span>
                                <span class="editor-module-copy">
                                    <small>Módulo 5</small>
                                    <strong>Cuestionario de prueba</strong>
                                    <span class="editor-module-state"><i class="fa-regular fa-clock" aria-hidden="true"></i> Pendiente</span>
                                </span>
                            </button>
                            <div class="editor-module-actions" aria-label="Acciones del módulo 5">
                                <button class="editor-module-action" type="button" data-action="edit" data-module-index="4" data-module-title="Cuestionario de prueba" aria-label="Editar datos del módulo Cuestionario de prueba"><i class="fa-solid fa-pen" aria-hidden="true"></i></button>
                                <button class="editor-module-action" type="button" data-action="duplicate" data-module-index="4" data-module-title="Cuestionario de prueba" aria-label="Duplicar módulo Cuestionario de prueba"><i class="fa-regular fa-copy" aria-hidden="true"></i></button>
                                <button class="editor-module-action is-danger" type="button" data-action="delete" data-module-index="4" data-module-title="Cuestionario de prueba" aria-label="Eliminar módulo Cuestionario de prueba"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
                            </div>
                        </li>
                    </ol>

                    <button id="open-module-modal" class="editor-add-module" type="button" data-bs-toggle="modal" data-bs-target="#module-form-modal">
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                        Agregar módulo
                    </button>
                </aside>

                <section class="editor-workspace" aria-labelledby="editor-current-title">
                    <header class="editor-current-module">
                        <span id="editor-current-icon" class="editor-current-icon"><i class="fa-solid fa-route" aria-hidden="true"></i></span>
                        <div class="editor-current-copy">
                            <span id="editor-current-label" class="content-type-label">Módulo 2 · Procedimiento</span>
                            <h2 id="editor-current-title">Procedimiento básico de trabajo seguro</h2>
                            <p id="editor-current-description">Presenta la secuencia que debe seguirse antes, durante y después de una actividad operativa.</p>
                        </div>
                        <span id="editor-current-status" class="editor-current-status is-editing"><i class="fa-solid fa-pen" aria-hidden="true"></i> En edición</span>
                    </header>

                    <div class="editor-tabs" role="tablist" aria-label="Opciones de edición del curso y del módulo">
                        <button id="editor-tab-data" class="editor-tab-button is-active" type="button" role="tab" data-editor-tab="data" aria-controls="editor-panel-data" aria-selected="true">
                            <i class="fa-regular fa-rectangle-list" aria-hidden="true"></i>
                            Datos del módulo
                        </button>
                        <button id="editor-tab-content" class="editor-tab-button" type="button" role="tab" data-editor-tab="content" aria-controls="editor-panel-content" aria-selected="false">
                            <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                            Contenido
                        </button>
                        <button id="editor-tab-resources" class="editor-tab-button" type="button" role="tab" data-editor-tab="resources" aria-controls="editor-panel-resources" aria-selected="false">
                            <i class="fa-regular fa-folder-open" aria-hidden="true"></i>
                            Recursos del curso
                        </button>
                        <button id="editor-tab-quiz" class="editor-tab-button" type="button" role="tab" data-editor-tab="quiz" aria-controls="editor-panel-quiz" aria-selected="false">
                            <i class="fa-solid fa-list-check" aria-hidden="true"></i>
                            Cuestionario
                        </button>
                    </div>

                    <div class="editor-tab-content">
                        <section id="editor-panel-data" class="editor-tab-panel is-active" role="tabpanel" data-editor-panel="data" aria-labelledby="editor-tab-data">
                            <div class="editor-section-heading">
                                <div>
                                    <span>Configuración</span>
                                    <h3>Datos generales del módulo</h3>
                                    <p>Esta información se mostrará en el encabezado del contenido.</p>
                                </div>
                            </div>

                            <form id="module-details-form" class="editor-form" novalidate>
                                <div class="editor-form-grid">
                                    <div class="editor-field">
                                        <label for="module-label-field">Etiqueta superior</label>
                                        <input id="module-label-field" class="form-control" type="text" value="Módulo 2 · Procedimiento" placeholder="Ej. Módulo 2 · Seguridad">
                                        <small>Ayuda al usuario a reconocer el tema actual.</small>
                                    </div>
                                    <div class="editor-field">
                                        <label for="module-icon-field">Icono representativo</label>
                                        <select id="module-icon-field" class="form-select">
                                            <option value="fa-route" selected>Procedimiento</option>
                                            <option value="fa-person-chalkboard">Introducción</option>
                                            <option value="fa-shield-halved">Seguridad</option>
                                            <option value="fa-medal">Calidad</option>
                                            <option value="fa-list-check">Cuestionario</option>
                                        </select>
                                    </div>
                                    <div class="editor-field editor-field-full">
                                        <label for="module-title-field">Título del módulo</label>
                                        <input id="module-title-field" class="form-control" type="text" value="Procedimiento básico de trabajo seguro">
                                    </div>
                                    <div class="editor-field editor-field-full">
                                        <label for="module-description-field">Descripción breve</label>
                                        <textarea id="module-description-field" class="form-control" rows="3">Presenta la secuencia que debe seguirse antes, durante y después de una actividad operativa.</textarea>
                                    </div>
                                    <div class="editor-field">
                                        <label for="module-status-field">Estado del módulo</label>
                                        <select id="module-status-field" class="form-select">
                                            <option value="pendiente">Pendiente</option>
                                            <option value="en-edicion" selected>En edición</option>
                                            <option value="completo">Completo</option>
                                        </select>
                                    </div>
                                    <div class="editor-field">
                                        <label for="module-type-field">Tipo de módulo</label>
                                        <select id="module-type-field" class="form-select">
                                            <option value="content" selected>Contenido de capacitación</option>
                                            <option value="quiz">Repaso o cuestionario</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="editor-inline-actions">
                                    <button class="editor-button editor-button-secondary" type="button" data-simulated-action="update-module-data">
                                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                                        Aplicar datos
                                    </button>
                                </div>
                            </form>

                            <article class="editor-heading-preview" aria-label="Vista previa del encabezado del módulo">
                                <span class="editor-preview-label">Vista previa</span>
                                <header class="module-content-heading">
                                    <span class="content-type-label"><i class="fa-solid fa-route" aria-hidden="true"></i> Módulo 2 · Procedimiento</span>
                                    <h2>Procedimiento básico de trabajo seguro</h2>
                                    <p>Presenta la secuencia que debe seguirse antes, durante y después de una actividad operativa.</p>
                                </header>
                            </article>
                        </section>

                        <section id="editor-panel-content" class="editor-tab-panel" role="tabpanel" data-editor-panel="content" aria-labelledby="editor-tab-content" hidden>
                            <div class="editor-section-heading">
                                <div>
                                    <span>Constructor visual</span>
                                    <h3>Contenido introductorio</h3>
                                    <p>Agrega un bloque inicial antes de las secciones del módulo.</p>
                                </div>
                            </div>

                            <div class="intro-insert-toolbar" aria-label="Agregar contenido introductorio">
                                <button class="intro-insert-action" type="button" data-insert-type="paragraph" data-insert-label="Párrafo introductorio"><i class="fa-solid fa-align-left" aria-hidden="true"></i><span>Agregar párrafo</span></button>
                                <button class="intro-insert-action" type="button" data-insert-type="video" data-insert-label="Video"><i class="fa-solid fa-circle-play" aria-hidden="true"></i><span>Agregar video</span></button>
                                <button class="intro-insert-action" type="button" data-insert-type="practice-grid" data-insert-label="Cuadrícula de prácticas"><i class="fa-solid fa-grip" aria-hidden="true"></i><span>Agregar práctica</span></button>
                                <button class="intro-insert-action" type="button" data-insert-type="procedure-steps" data-insert-label="Pasos de procedimiento"><i class="fa-solid fa-list-ol" aria-hidden="true"></i><span>Agregar pasos</span></button>
                            </div>

                            <div id="intro-builder-list" class="intro-builder-list">
                                <article class="editor-content-block" data-content-type="paragraph">
                                    <div class="editor-content-block-toolbar">
                                        <span><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i> Párrafo introductorio</span>
                                        <div><button type="button" aria-label="Editar párrafo"><i class="fa-solid fa-pen" aria-hidden="true"></i></button><button type="button" data-editor-delete="Párrafo introductorio" aria-label="Eliminar párrafo"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button></div>
                                    </div>
                                    <section class="written-material" aria-labelledby="editor-written-material-title">
                                        <div class="material-heading">
                                            <span class="material-icon"><i class="fa-solid fa-book-open" aria-hidden="true"></i></span>
                                            <div><span>Material escrito</span><h3 id="editor-written-material-title">Antes de realizar el procedimiento</h3></div>
                                        </div>
                                        <p>Observa el área, confirma que cuentas con el equipo necesario y comunica cualquier condición insegura antes de comenzar.</p>
                                    </section>
                                </article>

                                <article class="editor-content-block" data-content-type="video">
                                    <div class="editor-content-block-toolbar">
                                        <span><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i> Video</span>
                                        <div><button type="button" aria-label="Editar video"><i class="fa-solid fa-pen" aria-hidden="true"></i></button><button type="button" data-editor-delete="Video de capacitación" aria-label="Eliminar video"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button></div>
                                    </div>
                                    <div class="simulated-video" aria-label="Vista previa del video de capacitación">
                                        <span class="video-pattern" aria-hidden="true"></span>
                                        <span class="video-play-icon"><i class="fa-solid fa-play" aria-hidden="true"></i></span>
                                        <span class="video-copy"><strong>Preparación del área de trabajo</strong><small>Video simulado · Duración: 06:30</small></span>
                                        <span class="video-duration">06:30</span>
                                    </div>
                                </article>

                                <article class="editor-content-block" data-content-type="practice-grid">
                                    <div class="editor-content-block-toolbar">
                                        <span><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i> Cuadrícula de prácticas</span>
                                        <div><button type="button" aria-label="Editar cuadrícula"><i class="fa-solid fa-pen" aria-hidden="true"></i></button><button type="button" data-editor-delete="Cuadrícula de prácticas" aria-label="Eliminar cuadrícula"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button></div>
                                    </div>
                                    <div class="practice-grid">
                                        <article><span><i class="fa-solid fa-eye" aria-hidden="true"></i></span><h3>Observa</h3><p>Identifica riesgos y obstáculos.</p></article>
                                        <article><span><i class="fa-solid fa-helmet-safety" aria-hidden="true"></i></span><h3>Prepárate</h3><p>Utiliza el equipo indicado.</p></article>
                                        <article><span><i class="fa-solid fa-comments" aria-hidden="true"></i></span><h3>Comunica</h3><p>Reporta cualquier condición insegura.</p></article>
                                    </div>
                                </article>

                                <article class="editor-content-block" data-content-type="procedure-steps">
                                    <div class="editor-content-block-toolbar">
                                        <span><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i> Pasos de procedimiento</span>
                                        <div><button type="button" aria-label="Editar pasos"><i class="fa-solid fa-pen" aria-hidden="true"></i></button><button type="button" data-editor-delete="Pasos de procedimiento" aria-label="Eliminar pasos"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button></div>
                                    </div>
                                    <ol class="procedure-steps">
                                        <li><span>01</span><div><h3>Observa</h3><p>Revisa el área y confirma que las condiciones sean adecuadas.</p></div></li>
                                        <li><span>02</span><div><h3>Prepárate</h3><p>Coloca tu equipo de protección y verifica las herramientas.</p></div></li>
                                        <li><span>03</span><div><h3>Comunica</h3><p>Informa cualquier riesgo antes de comenzar.</p></div></li>
                                    </ol>
                                </article>
                            </div>

                            <div class="editor-section-heading editor-section-heading-with-action">
                                <div>
                                    <span>Contenido del módulo</span>
                                    <h3>Secciones</h3>
                                    <p>Organiza materiales y actividades en bloques fáciles de consultar.</p>
                                </div>
                                <button id="open-section-modal" class="editor-button editor-button-secondary" type="button" data-bs-toggle="modal" data-bs-target="#section-form-modal">
                                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                    Agregar sección
                                </button>
                            </div>

                            <div id="editor-section-list" class="accordion editor-section-list">
                                <article class="accordion-item editor-section-item">
                                    <h4 class="accordion-header" id="editor-section-heading-one">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#editor-section-one" aria-expanded="true" aria-controls="editor-section-one">
                                            <span class="editor-section-icon"><i class="fa-solid fa-book-open" aria-hidden="true"></i></span>
                                            <span><small>Material escrito</small><strong>Preparación antes de comenzar</strong></span>
                                            <span class="editor-section-badge is-complete">Completa</span>
                                        </button>
                                    </h4>
                                    <div id="editor-section-one" class="accordion-collapse collapse show" aria-labelledby="editor-section-heading-one">
                                        <div class="accordion-body">
                                            <div class="editor-item-actions"><button type="button"><i class="fa-solid fa-pen" aria-hidden="true"></i> Editar</button><button type="button"><i class="fa-regular fa-copy" aria-hidden="true"></i> Duplicar</button><button type="button" data-editor-delete="Preparación antes de comenzar"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Eliminar</button></div>
                                            <section class="written-material" aria-labelledby="section-material-preview-title">
                                                <div class="material-heading"><span class="material-icon"><i class="fa-solid fa-book-open" aria-hidden="true"></i></span><div><span>Material escrito</span><h3 id="section-material-preview-title">Verifica antes de iniciar</h3></div></div>
                                                <p>Una preparación breve permite reconocer riesgos y disponer correctamente de las herramientas.</p>
                                                <ul class="learning-points">
                                                    <li><i class="fa-solid fa-check" aria-hidden="true"></i> Revisa que el espacio esté libre.</li>
                                                    <li><i class="fa-solid fa-check" aria-hidden="true"></i> Confirma el equipo de protección.</li>
                                                    <li><i class="fa-solid fa-check" aria-hidden="true"></i> Reporta condiciones inseguras.</li>
                                                </ul>
                                            </section>
                                        </div>
                                    </div>
                                </article>

                                <article class="accordion-item editor-section-item">
                                    <h4 class="accordion-header" id="editor-section-heading-two">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#editor-section-two" aria-expanded="false" aria-controls="editor-section-two">
                                            <span class="editor-section-icon is-success"><i class="fa-solid fa-list-check" aria-hidden="true"></i></span>
                                            <span><small>Checklist interactivo</small><strong>Confirmación del procedimiento</strong></span>
                                            <span class="editor-section-badge is-editing">En edición</span>
                                        </button>
                                    </h4>
                                    <div id="editor-section-two" class="accordion-collapse collapse" aria-labelledby="editor-section-heading-two">
                                        <div class="accordion-body">
                                            <div class="editor-item-actions"><button type="button"><i class="fa-solid fa-pen" aria-hidden="true"></i> Editar</button><button type="button"><i class="fa-regular fa-copy" aria-hidden="true"></i> Duplicar</button><button type="button" data-editor-delete="Confirmación del procedimiento"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Eliminar</button></div>
                                            <section class="activity-card checklist-activity" aria-labelledby="editor-checklist-title">
                                                <div class="activity-heading"><span class="activity-icon activity-icon-success"><i class="fa-solid fa-list-check" aria-hidden="true"></i></span><div><span class="activity-status">Checklist interactivo</span><h3 id="editor-checklist-title">Marca los pasos que deben confirmarse</h3></div></div>
                                                <div class="learning-checklist">
                                                    <label><input type="checkbox" checked disabled> <span>El área se encuentra libre de obstáculos.</span><small>Respuesta correcta</small></label>
                                                    <label><input type="checkbox" checked disabled> <span>Utilizo el equipo de protección requerido.</span><small>Respuesta correcta</small></label>
                                                    <label><input type="checkbox" disabled> <span>Inicio aunque detecte una condición insegura.</span><small>Respuesta incorrecta</small></label>
                                                </div>
                                            </section>
                                        </div>
                                    </div>
                                </article>

                                <article class="accordion-item editor-section-item">
                                    <h4 class="accordion-header" id="editor-section-heading-three">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#editor-section-three" aria-expanded="false" aria-controls="editor-section-three">
                                            <span class="editor-section-icon is-purple"><i class="fa-solid fa-bolt" aria-hidden="true"></i></span>
                                            <span><small>Pregunta rápida</small><strong>Validación intermedia</strong></span>
                                            <span class="editor-section-badge is-complete">Completa</span>
                                        </button>
                                    </h4>
                                    <div id="editor-section-three" class="accordion-collapse collapse" aria-labelledby="editor-section-heading-three">
                                        <div class="accordion-body">
                                            <div class="editor-item-actions"><button type="button"><i class="fa-solid fa-pen" aria-hidden="true"></i> Editar</button><button type="button"><i class="fa-regular fa-copy" aria-hidden="true"></i> Duplicar</button><button type="button" data-editor-delete="Validación intermedia"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Eliminar</button></div>
                                            <section class="activity-card quick-quiz" aria-labelledby="editor-quick-question">
                                                <div class="activity-heading"><span class="activity-icon"><i class="fa-solid fa-bolt" aria-hidden="true"></i></span><div><span class="activity-status">Pregunta rápida</span><h3 id="editor-quick-question">¿Qué debe hacerse al detectar una condición insegura?</h3></div></div>
                                                <div class="quiz-options"><button class="quiz-option" type="button">Continuar con precaución</button><button class="quiz-option is-correct" type="button">Detenerse y reportarla</button><button class="quiz-option" type="button">Esperar al siguiente turno</button></div>
                                                <p class="activity-feedback">La opción marcada es la respuesta correcta.</p>
                                            </section>
                                        </div>
                                    </div>
                                </article>

                                <article class="accordion-item editor-section-item">
                                    <h4 class="accordion-header" id="editor-section-heading-four">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#editor-section-four" aria-expanded="false" aria-controls="editor-section-four">
                                            <span class="editor-section-icon is-orange"><i class="fa-solid fa-clone" aria-hidden="true"></i></span>
                                            <span><small>Repaso de conocimiento</small><strong>Conceptos clave</strong></span>
                                            <span class="editor-section-badge is-pending">Pendiente</span>
                                        </button>
                                    </h4>
                                    <div id="editor-section-four" class="accordion-collapse collapse" aria-labelledby="editor-section-heading-four">
                                        <div class="accordion-body">
                                            <div class="editor-item-actions"><button type="button"><i class="fa-solid fa-pen" aria-hidden="true"></i> Editar</button><button type="button"><i class="fa-regular fa-copy" aria-hidden="true"></i> Duplicar</button><button type="button" data-editor-delete="Conceptos clave"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Eliminar</button></div>
                                            <section class="knowledge-review" aria-labelledby="editor-knowledge-title">
                                                <div class="knowledge-review-heading"><div><span>Repaso interactivo</span><h3 id="editor-knowledge-title">Tarjetas de pregunta y respuesta</h3></div><i class="fa-solid fa-rotate" aria-hidden="true"></i></div>
                                                <div class="editor-knowledge-preview">
                                                    <article><span><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span><small>Pregunta</small><h4>¿Qué es una condición insegura?</h4><p>Situación que puede provocar un incidente.</p></article>
                                                    <article><span><i class="fa-solid fa-helmet-safety" aria-hidden="true"></i></span><small>Pregunta</small><h4>¿Cuándo usar el EPP?</h4><p>Siempre que la actividad y el área lo indiquen.</p></article>
                                                </div>
                                            </section>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        </section>

                        <section id="editor-panel-resources" class="editor-tab-panel" role="tabpanel" data-editor-panel="resources" aria-labelledby="editor-tab-resources" hidden>
                            <div class="editor-section-heading editor-section-heading-with-action">
                                <div>
                                    <span>Material compartido</span>
                                    <h3>Recursos generales del curso</h3>
                                    <p>Estos materiales se conservan para todos los módulos del curso.</p>
                                </div>
                                <button id="open-resource-modal" class="editor-button editor-button-secondary" type="button" data-bs-toggle="modal" data-bs-target="#resource-form-modal">
                                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                    Agregar recurso
                                </button>
                            </div>

                            <section class="course-resources" aria-labelledby="editor-resources-title">
                                <div class="resources-heading">
                                    <div><span>Material complementario del curso</span><h2 id="editor-resources-title">Recursos del curso</h2></div>
                                    <p>Disponibles sin importar el módulo seleccionado.</p>
                                </div>
                                <div id="editor-resource-list" class="resource-grid">
                                    <article class="editor-resource-item" data-resource-reference="guia-seguridad.pdf" title="Archivo simulado: guia-seguridad.pdf">
                                        <div class="resource-card">
                                            <span class="resource-icon resource-icon-cyan"><i class="fa-solid fa-file-lines" aria-hidden="true"></i></span>
                                            <span><strong>Guía rápida</strong><small>Documento · 4 páginas</small></span>
                                            <span class="resource-link-icon"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></span>
                                        </div>
                                        <div class="editor-resource-reference"><i class="fa-solid fa-paperclip" aria-hidden="true"></i><span>guia-seguridad.pdf</span></div>
                                        <div class="editor-resource-actions"><button type="button" aria-label="Editar Guía rápida"><i class="fa-solid fa-pen" aria-hidden="true"></i></button><button type="button" data-editor-delete="Guía rápida" aria-label="Eliminar Guía rápida"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button></div>
                                    </article>
                                    <article class="editor-resource-item" data-resource-reference="infografia-prevencion.png" title="Archivo simulado: infografia-prevencion.png">
                                        <div class="resource-card">
                                            <span class="resource-icon resource-icon-purple"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i></span>
                                            <span><strong>Infografía preventiva</strong><small>Imagen · Resumen visual</small></span>
                                            <span class="resource-link-icon"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></span>
                                        </div>
                                        <div class="editor-resource-reference"><i class="fa-solid fa-paperclip" aria-hidden="true"></i><span>infografia-prevencion.png</span></div>
                                        <div class="editor-resource-actions"><button type="button" aria-label="Editar Infografía preventiva"><i class="fa-solid fa-pen" aria-hidden="true"></i></button><button type="button" data-editor-delete="Infografía preventiva" aria-label="Eliminar Infografía preventiva"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button></div>
                                    </article>
                                    <article class="editor-resource-item" data-resource-reference="procedimiento-visual.pdf" title="Archivo simulado: procedimiento-visual.pdf">
                                        <div class="resource-card">
                                            <span class="resource-icon resource-icon-orange"><i class="fa-solid fa-images" aria-hidden="true"></i></span>
                                            <span><strong>Procedimiento visual</strong><small>Presentación · 8 diapositivas</small></span>
                                            <span class="resource-link-icon"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></span>
                                        </div>
                                        <div class="editor-resource-reference"><i class="fa-solid fa-paperclip" aria-hidden="true"></i><span>procedimiento-visual.pdf</span></div>
                                        <div class="editor-resource-actions"><button type="button" aria-label="Editar Procedimiento visual"><i class="fa-solid fa-pen" aria-hidden="true"></i></button><button type="button" data-editor-delete="Procedimiento visual" aria-label="Eliminar Procedimiento visual"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button></div>
                                    </article>
                                </div>
                            </section>
                        </section>

                        <section id="editor-panel-quiz" class="editor-tab-panel" role="tabpanel" data-editor-panel="quiz" aria-labelledby="editor-tab-quiz" hidden>
                            <div class="editor-section-heading editor-section-heading-with-action">
                                <div>
                                    <span>Repaso del módulo</span>
                                    <h3>Cuestionario de prueba</h3>
                                    <p>Configura preguntas breves para preparar al usuario antes de la evaluación.</p>
                                </div>
                                <button id="add-quiz-question" class="editor-button editor-button-secondary" type="button">
                                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                    Agregar pregunta
                                </button>
                            </div>

                            <div class="quiz-settings-card">
                                <span class="quiz-settings-icon"><i class="fa-solid fa-sliders" aria-hidden="true"></i></span>
                                <div><label for="quiz-passing-score">Criterio de aprobación</label><p>Porcentaje mínimo sugerido para completar este repaso.</p></div>
                                <div class="quiz-passing-input"><input id="quiz-passing-score" class="form-control" type="number" min="0" max="100" value="80" aria-describedby="quiz-passing-symbol"><span id="quiz-passing-symbol">%</span></div>
                            </div>

                            <div id="editor-question-list" class="accordion editor-question-list">
                                <article class="accordion-item editor-question-item" data-question-number="1">
                                    <h4 class="accordion-header" id="question-editor-heading-one">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#question-editor-one" aria-expanded="true" aria-controls="question-editor-one"><span class="editor-question-number">1</span><span><small>Pregunta 1</small><strong>¿Qué debes hacer antes de iniciar una actividad?</strong></span><span class="editor-question-points">2 puntos</span></button>
                                    </h4>
                                    <div id="question-editor-one" class="accordion-collapse collapse show" aria-labelledby="question-editor-heading-one">
                                        <div class="accordion-body">
                                            <div class="editor-field"><label for="quiz-question-one">Texto de la pregunta</label><textarea id="quiz-question-one" class="form-control" rows="2">¿Qué debes hacer antes de iniciar una actividad?</textarea></div>
                                            <fieldset class="quiz-option-editor"><legend>Opciones y respuesta correcta</legend>
                                                <div class="quiz-option-row"><label><input type="radio" name="correct-answer-one" value="1" aria-label="Marcar opción A como correcta"><span>A</span></label><input class="form-control" type="text" value="Comenzar inmediatamente" aria-label="Texto de la opción A"></div>
                                                <div class="quiz-option-row"><label><input type="radio" name="correct-answer-one" value="2" checked aria-label="Marcar opción B como correcta"><span>B</span></label><input class="form-control" type="text" value="Revisar el entorno e identificar riesgos" aria-label="Texto de la opción B"></div>
                                                <div class="quiz-option-row"><label><input type="radio" name="correct-answer-one" value="3" aria-label="Marcar opción C como correcta"><span>C</span></label><input class="form-control" type="text" value="Esperar a que otra persona revise" aria-label="Texto de la opción C"></div>
                                            </fieldset>
                                            <div class="editor-question-footer"><label for="quiz-points-one">Puntuación <input id="quiz-points-one" class="form-control" type="number" min="0" value="2"></label><button type="button" data-editor-delete="Pregunta 1"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Eliminar pregunta</button></div>
                                        </div>
                                    </div>
                                </article>

                                <article class="accordion-item editor-question-item" data-question-number="2">
                                    <h4 class="accordion-header" id="question-editor-heading-two">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-editor-two" aria-expanded="false" aria-controls="question-editor-two"><span class="editor-question-number">2</span><span><small>Pregunta 2</small><strong>¿Cuál es una práctica correcta durante el trabajo?</strong></span><span class="editor-question-points">2 puntos</span></button>
                                    </h4>
                                    <div id="question-editor-two" class="accordion-collapse collapse" aria-labelledby="question-editor-heading-two">
                                        <div class="accordion-body">
                                            <div class="editor-field"><label for="quiz-question-two">Texto de la pregunta</label><textarea id="quiz-question-two" class="form-control" rows="2">¿Cuál es una práctica correcta durante el trabajo?</textarea></div>
                                            <fieldset class="quiz-option-editor"><legend>Opciones y respuesta correcta</legend>
                                                <div class="quiz-option-row"><label><input type="radio" name="correct-answer-two" value="1" aria-label="Marcar opción A como correcta"><span>A</span></label><input class="form-control" type="text" value="Improvisar si falta una herramienta" aria-label="Texto de la opción A"></div>
                                                <div class="quiz-option-row"><label><input type="radio" name="correct-answer-two" value="2" checked aria-label="Marcar opción B como correcta"><span>B</span></label><input class="form-control" type="text" value="Mantener libres las rutas de acceso" aria-label="Texto de la opción B"></div>
                                                <div class="quiz-option-row"><label><input type="radio" name="correct-answer-two" value="3" aria-label="Marcar opción C como correcta"><span>C</span></label><input class="form-control" type="text" value="Ignorar riesgos menores" aria-label="Texto de la opción C"></div>
                                            </fieldset>
                                            <div class="editor-question-footer"><label for="quiz-points-two">Puntuación <input id="quiz-points-two" class="form-control" type="number" min="0" value="2"></label><button type="button" data-editor-delete="Pregunta 2"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Eliminar pregunta</button></div>
                                        </div>
                                    </div>
                                </article>

                                <article class="accordion-item editor-question-item" data-question-number="3">
                                    <h4 class="accordion-header" id="question-editor-heading-three">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#question-editor-three" aria-expanded="false" aria-controls="question-editor-three"><span class="editor-question-number">3</span><span><small>Pregunta 3</small><strong>¿A quién corresponde reportar una condición insegura?</strong></span><span class="editor-question-points">2 puntos</span></button>
                                    </h4>
                                    <div id="question-editor-three" class="accordion-collapse collapse" aria-labelledby="question-editor-heading-three">
                                        <div class="accordion-body">
                                            <div class="editor-field"><label for="quiz-question-three">Texto de la pregunta</label><textarea id="quiz-question-three" class="form-control" rows="2">¿A quién corresponde reportar una condición insegura?</textarea></div>
                                            <fieldset class="quiz-option-editor"><legend>Opciones y respuesta correcta</legend>
                                                <div class="quiz-option-row"><label><input type="radio" name="correct-answer-three" value="1" aria-label="Marcar opción A como correcta"><span>A</span></label><input class="form-control" type="text" value="Solo al supervisor" aria-label="Texto de la opción A"></div>
                                                <div class="quiz-option-row"><label><input type="radio" name="correct-answer-three" value="2" aria-label="Marcar opción B como correcta"><span>B</span></label><input class="form-control" type="text" value="Solo al personal de seguridad" aria-label="Texto de la opción B"></div>
                                                <div class="quiz-option-row"><label><input type="radio" name="correct-answer-three" value="3" checked aria-label="Marcar opción C como correcta"><span>C</span></label><input class="form-control" type="text" value="A toda persona que la identifique" aria-label="Texto de la opción C"></div>
                                            </fieldset>
                                            <div class="editor-question-footer"><label for="quiz-points-three">Puntuación <input id="quiz-points-three" class="form-control" type="number" min="0" value="2"></label><button type="button" data-editor-delete="Pregunta 3"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Eliminar pregunta</button></div>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        </section>
                    </div>
                </section>
            </div>
        </div>

        <div class="modal fade editor-modal" id="module-form-modal" tabindex="-1" aria-labelledby="module-form-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form id="module-form" novalidate>
                        <div class="modal-header">
                            <div class="editor-modal-heading"><span><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span><div><small>Estructura del curso</small><h2 class="modal-title" id="module-form-modal-title">Agregar módulo</h2></div></div>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <p class="editor-modal-introduction">La numeración se asignará automáticamente. Esta acción es únicamente visual.</p>
                            <div class="editor-form-grid">
                                <div class="editor-field"><label for="new-module-label">Etiqueta superior</label><input id="new-module-label" class="form-control" type="text" placeholder="Ej. Seguridad" required></div>
                                <div class="editor-field"><label for="new-module-icon">Icono</label><select id="new-module-icon" class="form-select"><option value="fa-book-open">Libro</option><option value="fa-person-chalkboard">Introducción</option><option value="fa-shield-halved">Seguridad</option><option value="fa-route">Procedimiento</option><option value="fa-list-check">Cuestionario</option></select></div>
                                <div class="editor-field editor-field-full"><label for="new-module-title">Título del módulo</label><input id="new-module-title" class="form-control" type="text" placeholder="Ej. Uso correcto del equipo de protección" required></div>
                                <div class="editor-field editor-field-full"><label for="new-module-description">Descripción breve</label><textarea id="new-module-description" class="form-control" rows="3" placeholder="Explica brevemente qué aprenderá el usuario" required></textarea></div>
                                <div class="editor-field"><label for="new-module-status">Estado</label><select id="new-module-status" class="form-select"><option value="pendiente">Pendiente</option><option value="en-edicion">En edición</option><option value="completo">Completo</option></select></div>
                                <div class="editor-field"><label for="new-module-type">Tipo de módulo</label><select id="new-module-type" class="form-select"><option value="content">Contenido de capacitación</option><option value="quiz">Repaso o cuestionario</option></select></div>
                            </div>
                        </div>
                        <div class="modal-footer"><button class="editor-button editor-button-muted" type="button" data-bs-dismiss="modal">Cancelar</button><button class="editor-button editor-button-primary" type="submit"><i class="fa-solid fa-plus" aria-hidden="true"></i> Agregar módulo</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade editor-modal" id="section-form-modal" tabindex="-1" aria-labelledby="section-form-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form id="section-form" novalidate>
                        <div class="modal-header">
                            <div class="editor-modal-heading"><span><i class="fa-solid fa-puzzle-piece" aria-hidden="true"></i></span><div><small>Contenido del módulo</small><h2 class="modal-title" id="section-form-modal-title">Agregar sección</h2></div></div>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <p class="editor-modal-introduction">Elige el componente que mejor represente el contenido que deseas mostrar.</p>
                            <div class="editor-form-grid">
                                <div class="editor-field"><label for="new-section-type">Tipo de contenido</label><select id="new-section-type" class="form-select" required><option value="">Selecciona un tipo</option><option value="written-material">Material escrito</option><option value="unordered-list">Lista no ordenada</option><option value="activity-feedback">Pregunta rápida</option><option value="learning-checklist">Checklist interactivo</option><option value="written-dynamic">Dinámica escrita</option><option value="true-false">Verdadero o falso</option><option value="knowledge-review">Repaso con tarjetas</option><option value="procedure-steps">Pasos de procedimiento</option><option value="practice-grid">Cuadrícula de prácticas</option></select></div>
                                <div class="editor-field"><label for="new-section-icon">Icono</label><select id="new-section-icon" class="form-select"><option value="fa-book-open">Libro</option><option value="fa-list-check">Checklist</option><option value="fa-bolt">Pregunta</option><option value="fa-pen">Escritura</option><option value="fa-clone">Tarjetas</option><option value="fa-route">Procedimiento</option></select></div>
                                <div class="editor-field editor-field-full"><label for="new-section-label">Etiqueta pequeña</label><input id="new-section-label" class="form-control" type="text" placeholder="Ej. Material escrito" required></div>
                                <div class="editor-field editor-field-full"><label for="new-section-title">Título de la sección</label><input id="new-section-title" class="form-control" type="text" placeholder="Ej. Antes de comenzar" required></div>
                                <div class="editor-field editor-field-full"><label for="new-section-content">Contenido o instrucciones</label><textarea id="new-section-content" class="form-control" rows="4" placeholder="Escribe el contenido principal de la sección"></textarea></div>
                            </div>

                            <div class="section-type-configurations">
                                <section class="section-config-panel" data-section-config="unordered-list" aria-labelledby="section-list-config-title" hidden>
                                    <header>
                                        <h3 id="section-list-config-title">Elementos de la lista</h3>
                                        <p>Selecciona un icono y escribe el texto de cada elemento.</p>
                                    </header>
                                    <div class="section-list-item">
                                        <label for="section-list-icon-1">Icono 1</label>
                                        <select id="section-list-icon-1" class="form-select"><option value="fa-check">Check</option><option value="fa-circle-info">Información</option><option value="fa-triangle-exclamation">Advertencia</option><option value="fa-shield-halved">Seguridad</option></select>
                                        <label for="section-list-text-1">Texto 1</label>
                                        <input id="section-list-text-1" class="form-control" type="text" value="Revisa el área antes de iniciar.">
                                    </div>
                                    <div class="section-list-item">
                                        <label for="section-list-icon-2">Icono 2</label>
                                        <select id="section-list-icon-2" class="form-select"><option value="fa-check">Check</option><option value="fa-circle-info">Información</option><option value="fa-triangle-exclamation">Advertencia</option><option value="fa-shield-halved">Seguridad</option></select>
                                        <label for="section-list-text-2">Texto 2</label>
                                        <input id="section-list-text-2" class="form-control" type="text" value="Utiliza el equipo de protección indicado.">
                                    </div>
                                    <div class="section-list-item">
                                        <label for="section-list-icon-3">Icono 3</label>
                                        <select id="section-list-icon-3" class="form-select"><option value="fa-check">Check</option><option value="fa-circle-info">Información</option><option value="fa-triangle-exclamation">Advertencia</option><option value="fa-shield-halved">Seguridad</option></select>
                                        <label for="section-list-text-3">Texto 3</label>
                                        <input id="section-list-text-3" class="form-control" type="text" value="Comunica cualquier condición insegura.">
                                    </div>
                                </section>

                                <section class="section-config-panel" data-section-config="activity-feedback" aria-labelledby="section-question-config-title" hidden>
                                    <header>
                                        <h3 id="section-question-config-title">Opciones de la pregunta rápida</h3>
                                        <p>Escribe tres respuestas y marca la opción correcta.</p>
                                    </header>
                                    <fieldset>
                                        <legend class="visually-hidden">Respuesta correcta de la pregunta rápida</legend>
                                        <div class="section-answer-option"><input id="section-answer-option-1" type="radio" name="section-correct-option" value="1" aria-label="Marcar opción A como correcta"><label for="section-answer-text-1">Opción A</label><input id="section-answer-text-1" class="form-control" type="text" value="Comenzar inmediatamente"></div>
                                        <div class="section-answer-option"><input id="section-answer-option-2" type="radio" name="section-correct-option" value="2" checked aria-label="Marcar opción B como correcta"><label for="section-answer-text-2">Opción B</label><input id="section-answer-text-2" class="form-control" type="text" value="Revisar el entorno e identificar riesgos"></div>
                                        <div class="section-answer-option"><input id="section-answer-option-3" type="radio" name="section-correct-option" value="3" aria-label="Marcar opción C como correcta"><label for="section-answer-text-3">Opción C</label><input id="section-answer-text-3" class="form-control" type="text" value="Esperar a que otra persona revise"></div>
                                    </fieldset>
                                </section>

                                <section class="section-config-panel" data-section-config="learning-checklist" aria-labelledby="section-checklist-config-title" hidden>
                                    <header>
                                        <h3 id="section-checklist-config-title">Elementos del checklist</h3>
                                        <p>Indica cuáles elementos deben seleccionarse y cuáles son distractores.</p>
                                    </header>
                                    <div class="section-checklist-row">
                                        <label for="section-checklist-text-1">Elemento 1</label>
                                        <input id="section-checklist-text-1" class="form-control section-checklist-text" type="text" value="El área está libre de obstáculos.">
                                        <label for="section-checklist-validity-1">Validez</label>
                                        <select id="section-checklist-validity-1" class="form-select section-checklist-validity"><option value="correct" selected>Correcto</option><option value="incorrect">Incorrecto</option></select>
                                    </div>
                                    <div class="section-checklist-row">
                                        <label for="section-checklist-text-2">Elemento 2</label>
                                        <input id="section-checklist-text-2" class="form-control section-checklist-text" type="text" value="Utilizo el equipo de protección requerido.">
                                        <label for="section-checklist-validity-2">Validez</label>
                                        <select id="section-checklist-validity-2" class="form-select section-checklist-validity"><option value="correct" selected>Correcto</option><option value="incorrect">Incorrecto</option></select>
                                    </div>
                                    <div class="section-checklist-row">
                                        <label for="section-checklist-text-3">Elemento 3</label>
                                        <input id="section-checklist-text-3" class="form-control section-checklist-text" type="text" value="Inicio aunque detecte una condición insegura.">
                                        <label for="section-checklist-validity-3">Validez</label>
                                        <select id="section-checklist-validity-3" class="form-select section-checklist-validity"><option value="correct">Correcto</option><option value="incorrect" selected>Incorrecto</option></select>
                                    </div>
                                    <button class="add-section-config-item" type="button" data-config-type="checklist"><i class="fa-solid fa-plus" aria-hidden="true"></i> Agregar elemento</button>
                                </section>

                                <section class="section-config-panel" data-section-config="written-dynamic" aria-labelledby="section-reflection-config-title" hidden>
                                    <header>
                                        <h3 id="section-reflection-config-title">Configuración de la dinámica escrita</h3>
                                        <p>Define el texto que orientará al usuario dentro del área de respuesta.</p>
                                    </header>
                                    <label for="section-reflection-placeholder">Texto de ejemplo o ayuda</label>
                                    <input id="section-reflection-placeholder" class="form-control" type="text" value="Ej. Describe una acción que puedes aplicar hoy.">
                                </section>

                                <section class="section-config-panel" data-section-config="true-false" aria-labelledby="section-true-false-config-title" hidden>
                                    <header>
                                        <h3 id="section-true-false-config-title">Respuesta de verdadero o falso</h3>
                                        <p>Selecciona la respuesta correcta para el enunciado escrito arriba.</p>
                                    </header>
                                    <fieldset>
                                        <legend>Respuesta correcta</legend>
                                        <label for="section-true-answer"><input id="section-true-answer" type="radio" name="section-true-false-correct" value="true" checked> Verdadero</label>
                                        <label for="section-false-answer"><input id="section-false-answer" type="radio" name="section-true-false-correct" value="false"> Falso</label>
                                    </fieldset>
                                </section>

                                <section class="section-config-panel" data-section-config="knowledge-review" aria-labelledby="section-knowledge-config-title" hidden>
                                    <header>
                                        <h3 id="section-knowledge-config-title">Tarjetas de repaso</h3>
                                        <p>Agrega un icono, una pregunta frontal y la respuesta del reverso.</p>
                                    </header>
                                    <div class="section-knowledge-row">
                                        <label for="section-knowledge-icon-1">Icono 1</label><select id="section-knowledge-icon-1" class="form-select section-knowledge-icon"><option value="fa-triangle-exclamation">Advertencia</option><option value="fa-helmet-safety">Seguridad</option><option value="fa-lightbulb">Idea</option></select>
                                        <label for="section-knowledge-question-1">Pregunta 1</label><input id="section-knowledge-question-1" class="form-control section-knowledge-question" type="text" value="¿Qué es una condición insegura?">
                                        <label for="section-knowledge-answer-1">Respuesta 1</label><textarea id="section-knowledge-answer-1" class="form-control section-knowledge-answer" rows="2">Una situación que puede provocar un incidente.</textarea>
                                    </div>
                                    <div class="section-knowledge-row">
                                        <label for="section-knowledge-icon-2">Icono 2</label><select id="section-knowledge-icon-2" class="form-select section-knowledge-icon"><option value="fa-triangle-exclamation">Advertencia</option><option value="fa-helmet-safety" selected>Seguridad</option><option value="fa-lightbulb">Idea</option></select>
                                        <label for="section-knowledge-question-2">Pregunta 2</label><input id="section-knowledge-question-2" class="form-control section-knowledge-question" type="text" value="¿Cuándo se debe utilizar el EPP?">
                                        <label for="section-knowledge-answer-2">Respuesta 2</label><textarea id="section-knowledge-answer-2" class="form-control section-knowledge-answer" rows="2">Siempre que la actividad y el área lo indiquen.</textarea>
                                    </div>
                                    <div class="section-knowledge-row">
                                        <label for="section-knowledge-icon-3">Icono 3</label><select id="section-knowledge-icon-3" class="form-select section-knowledge-icon"><option value="fa-triangle-exclamation">Advertencia</option><option value="fa-helmet-safety">Seguridad</option><option value="fa-lightbulb" selected>Idea</option></select>
                                        <label for="section-knowledge-question-3">Pregunta 3</label><input id="section-knowledge-question-3" class="form-control section-knowledge-question" type="text" value="¿Qué hacer al identificar un riesgo?">
                                        <label for="section-knowledge-answer-3">Respuesta 3</label><textarea id="section-knowledge-answer-3" class="form-control section-knowledge-answer" rows="2">Detenerse y comunicarlo de forma inmediata.</textarea>
                                    </div>
                                    <button class="add-section-config-item" type="button" data-config-type="knowledge"><i class="fa-solid fa-plus" aria-hidden="true"></i> Agregar tarjeta</button>
                                </section>

                                <section class="section-config-panel" data-section-config="procedure-steps" aria-labelledby="section-procedure-config-title" hidden>
                                    <header>
                                        <h3 id="section-procedure-config-title">Pasos del procedimiento</h3>
                                        <p>Elige cuántos pasos se mostrarán y personaliza su texto.</p>
                                    </header>
                                    <label for="section-step-count">Número de pasos</label>
                                    <select id="section-step-count" class="form-select"><option value="2">2 pasos</option><option value="3" selected>3 pasos</option><option value="4">4 pasos</option><option value="5">5 pasos</option></select>
                                    <div class="section-step-row" data-step-number="1"><span>01</span><label for="section-step-text-1">Paso 1</label><input id="section-step-text-1" class="form-control section-step-text" type="text" value="Observa el área e identifica riesgos."></div>
                                    <div class="section-step-row" data-step-number="2"><span>02</span><label for="section-step-text-2">Paso 2</label><input id="section-step-text-2" class="form-control section-step-text" type="text" value="Prepara el equipo y las herramientas."></div>
                                    <div class="section-step-row" data-step-number="3"><span>03</span><label for="section-step-text-3">Paso 3</label><input id="section-step-text-3" class="form-control section-step-text" type="text" value="Comunica cualquier condición insegura."></div>
                                    <div class="section-step-row" data-step-number="4"><span>04</span><label for="section-step-text-4">Paso 4</label><input id="section-step-text-4" class="form-control section-step-text" type="text" placeholder="Describe el paso 4"></div>
                                    <div class="section-step-row" data-step-number="5"><span>05</span><label for="section-step-text-5">Paso 5</label><input id="section-step-text-5" class="form-control section-step-text" type="text" placeholder="Describe el paso 5"></div>
                                </section>

                                <section class="section-config-panel" data-section-config="practice-grid" aria-labelledby="section-practice-config-title" hidden>
                                    <header>
                                        <h3 id="section-practice-config-title">Tarjetas de prácticas</h3>
                                        <p>Elige la cantidad y personaliza icono, título, descripción y color.</p>
                                    </header>
                                    <label for="section-practice-count">Número de elementos</label>
                                    <select id="section-practice-count" class="form-select"><option value="2">2 elementos</option><option value="3" selected>3 elementos</option><option value="4">4 elementos</option></select>
                                    <div class="section-practice-row" data-practice-number="1">
                                        <label for="section-practice-icon-1">Icono 1</label><select id="section-practice-icon-1" class="form-select section-practice-icon"><option value="fa-eye">Observación</option><option value="fa-helmet-safety">Seguridad</option><option value="fa-comments">Comunicación</option></select>
                                        <label for="section-practice-title-1">Título 1</label><input id="section-practice-title-1" class="form-control section-practice-title" type="text" value="Observa">
                                        <label for="section-practice-text-1">Descripción 1</label><input id="section-practice-text-1" class="form-control section-practice-text" type="text" value="Identifica riesgos y obstáculos.">
                                        <label for="section-practice-color-1">Color 1</label><select id="section-practice-color-1" class="form-select section-practice-color"><option value="cyan">Cyan</option><option value="purple">Morado</option><option value="orange">Naranja</option><option value="pink">Rosa</option></select>
                                    </div>
                                    <div class="section-practice-row" data-practice-number="2">
                                        <label for="section-practice-icon-2">Icono 2</label><select id="section-practice-icon-2" class="form-select section-practice-icon"><option value="fa-eye">Observación</option><option value="fa-helmet-safety" selected>Seguridad</option><option value="fa-comments">Comunicación</option></select>
                                        <label for="section-practice-title-2">Título 2</label><input id="section-practice-title-2" class="form-control section-practice-title" type="text" value="Prepárate">
                                        <label for="section-practice-text-2">Descripción 2</label><input id="section-practice-text-2" class="form-control section-practice-text" type="text" value="Utiliza el equipo indicado.">
                                        <label for="section-practice-color-2">Color 2</label><select id="section-practice-color-2" class="form-select section-practice-color"><option value="cyan">Cyan</option><option value="purple" selected>Morado</option><option value="orange">Naranja</option><option value="pink">Rosa</option></select>
                                    </div>
                                    <div class="section-practice-row" data-practice-number="3">
                                        <label for="section-practice-icon-3">Icono 3</label><select id="section-practice-icon-3" class="form-select section-practice-icon"><option value="fa-eye">Observación</option><option value="fa-helmet-safety">Seguridad</option><option value="fa-comments" selected>Comunicación</option></select>
                                        <label for="section-practice-title-3">Título 3</label><input id="section-practice-title-3" class="form-control section-practice-title" type="text" value="Comunica">
                                        <label for="section-practice-text-3">Descripción 3</label><input id="section-practice-text-3" class="form-control section-practice-text" type="text" value="Reporta cualquier condición insegura.">
                                        <label for="section-practice-color-3">Color 3</label><select id="section-practice-color-3" class="form-select section-practice-color"><option value="cyan">Cyan</option><option value="purple">Morado</option><option value="orange" selected>Naranja</option><option value="pink">Rosa</option></select>
                                    </div>
                                    <div class="section-practice-row" data-practice-number="4">
                                        <label for="section-practice-icon-4">Icono 4</label><select id="section-practice-icon-4" class="form-select section-practice-icon"><option value="fa-eye">Observación</option><option value="fa-helmet-safety">Seguridad</option><option value="fa-comments">Comunicación</option></select>
                                        <label for="section-practice-title-4">Título 4</label><input id="section-practice-title-4" class="form-control section-practice-title" type="text" placeholder="Título de la práctica">
                                        <label for="section-practice-text-4">Descripción 4</label><input id="section-practice-text-4" class="form-control section-practice-text" type="text" placeholder="Descripción breve">
                                        <label for="section-practice-color-4">Color 4</label><select id="section-practice-color-4" class="form-select section-practice-color"><option value="cyan">Cyan</option><option value="purple">Morado</option><option value="orange">Naranja</option><option value="pink" selected>Rosa</option></select>
                                    </div>
                                </section>
                            </div>

                            <div id="section-type-help" class="editor-context-help" role="status"><i class="fa-regular fa-lightbulb" aria-hidden="true"></i><p>Selecciona un tipo de contenido para mostrar sus opciones de configuración.</p></div>
                        </div>
                        <div class="modal-footer"><button class="editor-button editor-button-muted" type="button" data-bs-dismiss="modal">Cancelar</button><button class="editor-button editor-button-primary" type="submit"><i class="fa-solid fa-plus" aria-hidden="true"></i> Agregar sección</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade editor-modal" id="resource-form-modal" tabindex="-1" aria-labelledby="resource-form-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form id="resource-form" novalidate>
                        <div class="modal-header">
                            <div class="editor-modal-heading"><span><i class="fa-regular fa-folder-open" aria-hidden="true"></i></span><div><small>Material complementario</small><h2 class="modal-title" id="resource-form-modal-title">Agregar recurso</h2></div></div>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <p class="editor-modal-introduction">Registra la referencia visual del recurso. No se cargará ningún archivo realmente.</p>
                            <div class="editor-form-grid">
                                <div class="editor-field editor-field-full"><label for="new-resource-name">Nombre del recurso</label><input id="new-resource-name" class="form-control" type="text" placeholder="Ej. Guía de inspección" required></div>
                                <div class="editor-field editor-field-full"><label for="new-resource-description">Descripción breve</label><textarea id="new-resource-description" class="form-control" rows="3" placeholder="Indica qué encontrará el usuario" required></textarea></div>
                                <div class="editor-field"><label for="new-resource-type">Tipo de recurso</label><select id="new-resource-type" class="form-select"><option value="document">Documento</option><option value="image">Imagen o infografía</option><option value="presentation">Presentación</option><option value="video">Video</option><option value="link">Enlace</option></select></div>
                                <div class="editor-field"><label for="new-resource-icon">Icono</label><select id="new-resource-icon" class="form-select"><option value="fa-file-lines">Documento</option><option value="fa-chart-pie">Infografía</option><option value="fa-images">Presentación</option><option value="fa-circle-play">Video</option><option value="fa-link">Enlace</option></select></div>
                                <div class="editor-field editor-field-full"><label for="new-resource-reference">Enlace o archivo simulado</label><input id="new-resource-reference" class="form-control" type="text" placeholder="Ej. guia-inspeccion.pdf o https://ejemplo.local/recurso"></div>
                            </div>
                        </div>
                        <div class="modal-footer"><button class="editor-button editor-button-muted" type="button" data-bs-dismiss="modal">Cancelar</button><button class="editor-button editor-button-primary" type="submit"><i class="fa-solid fa-plus" aria-hidden="true"></i> Agregar recurso</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade editor-modal editor-delete-modal" id="delete-editor-item-modal" tabindex="-1" aria-labelledby="delete-editor-item-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <span class="editor-delete-icon"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></span>
                        <h2 id="delete-editor-item-title">¿Eliminar este elemento?</h2>
                        <p>Esta acción es simulada. <strong id="delete-editor-item-name">El elemento</strong> se quitará de la vista, pero el cambio no se guardará de forma permanente.</p>
                    </div>
                    <div class="modal-footer"><button class="editor-button editor-button-muted" type="button" data-bs-dismiss="modal">Cancelar</button><button id="confirm-editor-item-delete" class="editor-button editor-button-danger" type="button"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Simular eliminación</button></div>
                </div>
            </div>
        </div>

        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="editor-feedback-toast" class="toast editor-feedback-toast" role="status" aria-live="polite" aria-atomic="true">
                <div class="toast-body">
                    <span class="editor-toast-icon"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
                    <p id="editor-feedback-message">Cambios guardados correctamente de forma simulada.</p>
                    <button class="btn-close" type="button" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
            </div>
        </div>
<?php include __DIR__ . '/../includes/app-footer.php'; ?>
