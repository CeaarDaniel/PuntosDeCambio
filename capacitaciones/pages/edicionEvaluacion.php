<?php
$pageTitle = 'Edición de evaluación | CapacitaLíder';
$sectionTitle = 'Edición de evaluación';
$activePage = 'creacionCursos';
$basePath = '../';
$pageVendorStyles = ['css/evaluacionPreguntas.css'];
$pageStyle = 'css/edicion-evaluacion.css';
$pageScript = 'js/edicionEvaluacion.js';

include __DIR__ . '/../includes/app-header.php';
?>
        <div class="evaluation-editor-page exam-page">
            <a class="exam-back-link" href="creacionCursos.php">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Volver a cursos</span>
            </a>

            <section class="exam-header-card evaluation-editor-header" aria-labelledby="evaluation-editor-title">
                <div class="exam-header-copy">
                    <span class="exam-course-label">
                        <i class="fa-solid fa-clipboard-question" aria-hidden="true"></i>
                        Constructor de evaluación
                    </span>
                    <h2 id="evaluation-editor-title">Evaluación de Metodología 5S</h2>
                    <p>Organiza las secciones, preguntas y respuestas que se mostrarán al personal al finalizar el curso.</p>
                    <div class="exam-header-meta">
                        <span><i class="fa-solid fa-book-open" aria-hidden="true"></i> Curso: Metodología 5S</span>
                        <span><i class="fa-solid fa-layer-group" aria-hidden="true"></i> <strong id="header-section-count">3</strong> secciones</span>
                        <span><i class="fa-solid fa-list-check" aria-hidden="true"></i> <strong id="header-question-count">5</strong> preguntas</span>
                    </div>
                </div>

                <div class="exam-progress-card evaluation-editor-actions-card">
                    <div class="evaluation-status-heading">
                        <div>
                            <span>Estado de configuración</span>
                            <strong id="evaluation-configuration-status">4 de 5 preguntas configuradas</strong>
                        </div>
                        <span class="evaluation-status-badge"><i class="fa-solid fa-pen" aria-hidden="true"></i> Borrador</span>
                    </div>
                    <div class="progress" role="progressbar" aria-label="Configuración de la evaluación" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" id="evaluation-configuration-progress">
                        <div class="progress-bar" style="width: 80%"></div>
                    </div>
                    <div class="evaluation-header-actions">
                        <button id="open-evaluation-preview" class="button evaluation-preview-button" type="button">
                            <i class="fa-regular fa-eye" aria-hidden="true"></i>
                            Vista previa
                        </button>
                        <button id="save-evaluation" class="button evaluation-save-button" type="button">
                            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                            Guardar cambios
                        </button>
                    </div>
                </div>
            </section>

            <section class="evaluation-settings-card" aria-labelledby="evaluation-settings-title">
                <div class="evaluation-card-heading">
                    <span class="evaluation-heading-icon"><i class="fa-solid fa-sliders" aria-hidden="true"></i></span>
                    <div>
                        <span>Configuración general</span>
                        <h2 id="evaluation-settings-title">Datos de la evaluación</h2>
                        <p>Define la información principal y las condiciones para aprobar.</p>
                    </div>
                </div>

                <form id="evaluation-settings-form" class="evaluation-settings-form" novalidate>
                    <div class="evaluation-field evaluation-field-wide">
                        <label for="evaluation-name">Nombre de la evaluación</label>
                        <input id="evaluation-name" class="form-control" type="text" value="Evaluación de Metodología 5S">
                    </div>
                    <div class="evaluation-field">
                        <label for="evaluation-course">Curso relacionado</label>
                        <select id="evaluation-course" class="form-select">
                            <option selected>Metodología 5S</option>
                            <option>Seguridad industrial básica</option>
                            <option>Fundamentos de calidad</option>
                        </select>
                    </div>
                    <div class="evaluation-field evaluation-field-description">
                        <label for="evaluation-description">Descripción breve</label>
                        <textarea id="evaluation-description" class="form-control" rows="3">Valida los conceptos, procedimientos y buenas prácticas revisadas durante el curso.</textarea>
                    </div>
                    <div class="evaluation-field">
                        <label for="evaluation-time-limit">Tiempo límite</label>
                        <div class="evaluation-input-suffix"><input id="evaluation-time-limit" class="form-control" type="number" min="0" value="15"><span>min</span></div>
                    </div>
                    <div class="evaluation-field">
                        <label for="evaluation-passing-score">Porcentaje mínimo</label>
                        <div class="evaluation-input-suffix"><input id="evaluation-passing-score" class="form-control" type="number" min="0" max="100" value="80"><span>%</span></div>
                    </div>
                    <div class="evaluation-field">
                        <label for="evaluation-total-score">Puntuación total</label>
                        <input id="evaluation-total-score" class="form-control" type="number" value="10" readonly>
                    </div>
                    <div class="evaluation-field">
                        <label for="evaluation-status">Estatus</label>
                        <select id="evaluation-status" class="form-select">
                            <option selected>Borrador</option>
                            <option>Activa</option>
                            <option>Inactiva</option>
                        </select>
                    </div>
                </form>
            </section>

            <section class="evaluation-summary-grid" aria-label="Resumen de la evaluación">
                <article class="evaluation-summary-card accent-cyan">
                    <span><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span>
                    <div><strong id="summary-section-count">3</strong><small>Secciones</small></div>
                </article>
                <article class="evaluation-summary-card accent-purple">
                    <span><i class="fa-solid fa-list-check" aria-hidden="true"></i></span>
                    <div><strong id="summary-question-count">5</strong><small>Preguntas</small></div>
                </article>
                <article class="evaluation-summary-card accent-orange">
                    <span><i class="fa-solid fa-star" aria-hidden="true"></i></span>
                    <div><strong id="summary-points-count">10</strong><small>Puntos totales</small></div>
                </article>
                <article class="evaluation-summary-card accent-success">
                    <span><i class="fa-solid fa-percent" aria-hidden="true"></i></span>
                    <div><strong id="summary-passing-score">80%</strong><small>Mínimo para aprobar</small></div>
                </article>
                <article class="evaluation-summary-card accent-pink">
                    <span><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
                    <div><strong id="summary-configured-count">4</strong><small>Configuradas</small></div>
                </article>
                <article class="evaluation-summary-card accent-gold">
                    <span><i class="fa-regular fa-clock" aria-hidden="true"></i></span>
                    <div><strong id="summary-pending-count">1</strong><small>Pendientes</small></div>
                </article>
            </section>

            <div class="evaluation-editor-workspace">
                <aside class="exam-section-navigation evaluation-section-navigation" aria-labelledby="evaluation-sections-title">
                    <div class="exam-navigation-heading evaluation-navigation-heading">
                        <span class="exam-navigation-icon"><i class="fa-solid fa-bars-staggered" aria-hidden="true"></i></span>
                        <div>
                            <span>Estructura</span>
                            <h2 id="evaluation-sections-title">Secciones</h2>
                        </div>
                        <button id="add-evaluation-section" class="evaluation-heading-action" type="button" aria-label="Agregar sección">
                            <i class="fa-solid fa-plus" aria-hidden="true"></i>
                        </button>
                    </div>

                    <p class="evaluation-navigation-help">Selecciona una sección para administrar sus preguntas.</p>
                    <nav aria-label="Secciones de la evaluación">
                        <ol id="evaluation-section-list" class="exam-section-list evaluation-section-list">
                            <li class="evaluation-section-item is-active" data-section-id="section-1" data-section-title="Conceptos básicos" data-section-description="Principios esenciales de la metodología 5S." data-section-order="1">
                                <button class="exam-section-button evaluation-section-select is-active" type="button" aria-selected="true">
                                    <span class="exam-section-number">1</span>
                                    <span class="exam-section-copy">
                                        <small>Sección 1</small>
                                        <strong>Conceptos básicos</strong>
                                        <span class="exam-section-state state-completed"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>Completa</span></span>
                                    </span>
                                </button>
                                <div class="evaluation-section-actions">
                                    <span><b class="section-question-total">2</b> preguntas · <b class="section-points-total">4</b> pts</span>
                                    <div><button class="edit-evaluation-section" type="button" aria-label="Editar Conceptos básicos"><i class="fa-solid fa-pen" aria-hidden="true"></i></button><button class="delete-evaluation-section" type="button" aria-label="Eliminar Conceptos básicos"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button></div>
                                </div>
                            </li>
                            <li class="evaluation-section-item" data-section-id="section-2" data-section-title="Procedimientos" data-section-description="Aplicación ordenada de las etapas de 5S." data-section-order="2">
                                <button class="exam-section-button evaluation-section-select" type="button" aria-selected="false">
                                    <span class="exam-section-number">2</span>
                                    <span class="exam-section-copy">
                                        <small>Sección 2</small>
                                        <strong>Procedimientos</strong>
                                        <span class="exam-section-state state-completed"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>Completa</span></span>
                                    </span>
                                </button>
                                <div class="evaluation-section-actions">
                                    <span><b class="section-question-total">2</b> preguntas · <b class="section-points-total">4</b> pts</span>
                                    <div><button class="edit-evaluation-section" type="button" aria-label="Editar Procedimientos"><i class="fa-solid fa-pen" aria-hidden="true"></i></button><button class="delete-evaluation-section" type="button" aria-label="Eliminar Procedimientos"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button></div>
                                </div>
                            </li>
                            <li class="evaluation-section-item" data-section-id="section-3" data-section-title="Caso práctico" data-section-description="Situaciones observables en el área de trabajo." data-section-order="3">
                                <button class="exam-section-button evaluation-section-select" type="button" aria-selected="false">
                                    <span class="exam-section-number">3</span>
                                    <span class="exam-section-copy">
                                        <small>Sección 3</small>
                                        <strong>Caso práctico</strong>
                                        <span class="exam-section-state state-progress"><i class="fa-solid fa-circle-half-stroke" aria-hidden="true"></i><span>Incompleta</span></span>
                                    </span>
                                </button>
                                <div class="evaluation-section-actions">
                                    <span><b class="section-question-total">1</b> pregunta · <b class="section-points-total">2</b> pts</span>
                                    <div><button class="edit-evaluation-section" type="button" aria-label="Editar Caso práctico"><i class="fa-solid fa-pen" aria-hidden="true"></i></button><button class="delete-evaluation-section" type="button" aria-label="Eliminar Caso práctico"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button></div>
                                </div>
                            </li>
                        </ol>
                    </nav>

                    <button class="button evaluation-add-section-button" type="button">
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                        Agregar sección
                    </button>
                </aside>

                <section class="exam-question-area evaluation-question-editor" aria-labelledby="current-section-title">
                    <div class="exam-instruction-banner evaluation-editor-banner">
                        <span><i class="fa-solid fa-pen-ruler" aria-hidden="true"></i></span>
                        <div><strong>Modo de edición</strong><p>La respuesta correcta se muestra únicamente como referencia para quien configura la evaluación.</p></div>
                    </div>

                    <header class="evaluation-current-section-header">
                        <div>
                            <span id="current-section-eyebrow" class="exam-section-eyebrow">Sección 1 de 3</span>
                            <h2 id="current-section-title">Conceptos básicos</h2>
                            <p id="current-section-description">Principios esenciales de la metodología 5S.</p>
                        </div>
                        <div class="evaluation-current-actions">
                            <span id="current-section-summary" class="section-question-count">2 preguntas · 4 puntos</span>
                            <button id="edit-current-section" class="evaluation-icon-text-button" type="button"><i class="fa-solid fa-pen" aria-hidden="true"></i> Editar sección</button>
                            <button id="add-evaluation-question" class="button evaluation-add-question-button" type="button"><i class="fa-solid fa-plus" aria-hidden="true"></i> Agregar pregunta</button>
                        </div>
                    </header>

                    <div id="evaluation-question-panels">
                        <div class="evaluation-section-questions is-active" data-section-id="section-1">
                            <article class="exam-question evaluation-question-card" data-question-id="question-1" data-question-type="multiple" data-question-points="2" data-correct-answer="a" data-feedback="Seiri permite retirar lo innecesario antes de organizar.">
                                <div class="evaluation-question-toolbar">
                                    <span class="question-number">Pregunta 1</span>
                                    <span class="evaluation-question-type"><i class="fa-solid fa-list-ul" aria-hidden="true"></i> Opción múltiple</span>
                                    <span class="evaluation-question-points"><i class="fa-solid fa-star" aria-hidden="true"></i> 2 puntos</span>
                                    <div><button class="edit-evaluation-question" type="button" aria-label="Editar pregunta 1"><i class="fa-solid fa-pen" aria-hidden="true"></i></button><button class="delete-evaluation-question" type="button" aria-label="Eliminar pregunta 1"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button></div>
                                </div>
                                <h3 class="evaluation-question-text">¿Cuál es el propósito principal de la primera S: Seiri?</h3>
                                <div class="answer-options evaluation-answer-options">
                                    <div class="answer-option is-correct-answer" data-option-value="a"><span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span><span class="answer-text">Clasificar y retirar lo que no es necesario</span><small>Correcta</small></div>
                                    <div class="answer-option" data-option-value="b"><span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span><span class="answer-text">Limpiar únicamente al finalizar el turno</span></div>
                                    <div class="answer-option" data-option-value="c"><span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span><span class="answer-text">Guardar todos los materiales disponibles</span></div>
                                </div>
                                <p class="evaluation-question-feedback"><i class="fa-regular fa-message" aria-hidden="true"></i> Seiri permite retirar lo innecesario antes de organizar.</p>
                            </article>

                            <article class="exam-question evaluation-question-card" data-question-id="question-2" data-question-type="true-false" data-question-points="2" data-correct-answer="false" data-feedback="5S puede aplicarse en cualquier espacio de trabajo.">
                                <div class="evaluation-question-toolbar">
                                    <span class="question-number">Pregunta 2</span>
                                    <span class="evaluation-question-type"><i class="fa-solid fa-toggle-on" aria-hidden="true"></i> Verdadero / falso</span>
                                    <span class="evaluation-question-points"><i class="fa-solid fa-star" aria-hidden="true"></i> 2 puntos</span>
                                    <div><button class="edit-evaluation-question" type="button" aria-label="Editar pregunta 2"><i class="fa-solid fa-pen" aria-hidden="true"></i></button><button class="delete-evaluation-question" type="button" aria-label="Eliminar pregunta 2"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button></div>
                                </div>
                                <h3 class="evaluation-question-text">La metodología 5S solo debe aplicarse en áreas de producción.</h3>
                                <div class="answer-options answer-options-inline evaluation-answer-options">
                                    <div class="answer-option" data-option-value="true"><span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span><span class="answer-text">Verdadero</span></div>
                                    <div class="answer-option is-correct-answer" data-option-value="false"><span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span><span class="answer-text">Falso</span><small>Correcta</small></div>
                                </div>
                                <p class="evaluation-question-feedback"><i class="fa-regular fa-message" aria-hidden="true"></i> 5S puede aplicarse en cualquier espacio de trabajo.</p>
                            </article>
                        </div>

                        <div class="evaluation-section-questions" data-section-id="section-2" hidden>
                            <article class="exam-question evaluation-question-card" data-question-id="question-3" data-question-type="multiple" data-question-points="2" data-correct-answer="a" data-feedback="Seiton busca que cada objeto tenga un lugar definido.">
                                <div class="evaluation-question-toolbar"><span class="question-number">Pregunta 1</span><span class="evaluation-question-type"><i class="fa-solid fa-list-ul" aria-hidden="true"></i> Opción múltiple</span><span class="evaluation-question-points"><i class="fa-solid fa-star" aria-hidden="true"></i> 2 puntos</span><div><button class="edit-evaluation-question" type="button" aria-label="Editar pregunta"><i class="fa-solid fa-pen" aria-hidden="true"></i></button><button class="delete-evaluation-question" type="button" aria-label="Eliminar pregunta"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button></div></div>
                                <h3 class="evaluation-question-text">Después de clasificar los materiales, ¿qué acción corresponde a Seiton?</h3>
                                <div class="answer-options evaluation-answer-options"><div class="answer-option is-correct-answer" data-option-value="a"><span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span><span class="answer-text">Definir un lugar visible y accesible para cada elemento</span><small>Correcta</small></div><div class="answer-option" data-option-value="b"><span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span><span class="answer-text">Mover todos los elementos a una bodega temporal</span></div><div class="answer-option" data-option-value="c"><span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span><span class="answer-text">Esperar una auditoría antes de ordenarlos</span></div></div>
                                <p class="evaluation-question-feedback"><i class="fa-regular fa-message" aria-hidden="true"></i> Seiton busca que cada objeto tenga un lugar definido.</p>
                            </article>
                            <article class="exam-question evaluation-question-card" data-question-id="question-4" data-question-type="multiple" data-question-points="2" data-correct-answer="a" data-feedback="Los apoyos visuales facilitan conservar el orden.">
                                <div class="evaluation-question-toolbar"><span class="question-number">Pregunta 2</span><span class="evaluation-question-type"><i class="fa-solid fa-list-ul" aria-hidden="true"></i> Opción múltiple</span><span class="evaluation-question-points"><i class="fa-solid fa-star" aria-hidden="true"></i> 2 puntos</span><div><button class="edit-evaluation-question" type="button" aria-label="Editar pregunta"><i class="fa-solid fa-pen" aria-hidden="true"></i></button><button class="delete-evaluation-question" type="button" aria-label="Eliminar pregunta"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button></div></div>
                                <h3 class="evaluation-question-text">¿Qué recurso facilita que cada herramienta regrese a su lugar?</h3>
                                <div class="answer-options evaluation-answer-options"><div class="answer-option is-correct-answer" data-option-value="a"><span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span><span class="answer-text">Señalización, etiquetas y siluetas visuales</span><small>Correcta</small></div><div class="answer-option" data-option-value="b"><span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span><span class="answer-text">Una lista guardada fuera del área</span></div><div class="answer-option" data-option-value="c"><span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span><span class="answer-text">La memoria de cada colaborador</span></div></div>
                                <p class="evaluation-question-feedback"><i class="fa-regular fa-message" aria-hidden="true"></i> Los apoyos visuales facilitan conservar el orden.</p>
                            </article>
                        </div>

                        <div class="evaluation-section-questions" data-section-id="section-3" hidden>
                            <article class="exam-question evaluation-question-card is-incomplete" data-question-id="question-5" data-question-type="multiple" data-question-points="2" data-correct-answer="" data-feedback="">
                                <div class="evaluation-question-toolbar"><span class="question-number">Pregunta 1</span><span class="evaluation-question-type"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> Pendiente de configurar</span><span class="evaluation-question-points"><i class="fa-solid fa-star" aria-hidden="true"></i> 2 puntos</span><div><button class="edit-evaluation-question" type="button" aria-label="Configurar pregunta pendiente"><i class="fa-solid fa-pen" aria-hidden="true"></i></button><button class="delete-evaluation-question" type="button" aria-label="Eliminar pregunta"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button></div></div>
                                <h3 class="evaluation-question-text">Describe aquí la situación práctica que deberá resolver el participante.</h3>
                                <div class="evaluation-incomplete-callout"><i class="fa-regular fa-lightbulb" aria-hidden="true"></i><p>Agrega las opciones y selecciona una respuesta correcta para completar esta pregunta.</p></div>
                            </article>
                        </div>
                    </div>

                    <div id="empty-section-state" class="evaluation-empty-state" hidden>
                        <i class="fa-solid fa-clipboard-question" aria-hidden="true"></i>
                        <h3>Esta sección todavía no tiene preguntas</h3>
                        <p>Agrega la primera pregunta para comenzar a construirla.</p>
                        <button class="button evaluation-add-question-button" type="button"><i class="fa-solid fa-plus" aria-hidden="true"></i> Agregar pregunta</button>
                    </div>
                </section>
            </div>

            <div class="evaluation-sticky-actions">
                <a class="button evaluation-back-button" href="creacionCursos.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Volver a cursos</a>
                <div><button class="button evaluation-preview-button open-evaluation-preview" type="button"><i class="fa-regular fa-eye" aria-hidden="true"></i> Vista previa</button><button class="button evaluation-save-button save-evaluation" type="button"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Guardar cambios simulados</button></div>
            </div>
        </div>

        <div class="modal fade evaluation-editor-modal" id="section-editor-modal" tabindex="-1" aria-labelledby="section-editor-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form id="section-editor-form" novalidate>
                        <div class="modal-header"><div class="evaluation-modal-heading"><span><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span><div><small>Estructura de la evaluación</small><h2 id="section-editor-modal-title" class="modal-title">Agregar sección</h2></div></div><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                        <div class="modal-body">
                            <input id="section-edit-id" type="hidden">
                            <div class="evaluation-modal-grid">
                                <div class="evaluation-field evaluation-field-full"><label for="section-title-field">Título de la sección</label><input id="section-title-field" class="form-control" type="text" placeholder="Ej. Seguridad y calidad" required></div>
                                <div class="evaluation-field evaluation-field-full"><label for="section-description-field">Descripción breve</label><textarea id="section-description-field" class="form-control" rows="3" placeholder="Indica qué conocimientos agrupa esta sección"></textarea></div>
                                <div class="evaluation-field"><label for="section-order-field">Orden visual</label><input id="section-order-field" class="form-control" type="number" min="1" value="1"></div>
                            </div>
                            <p class="evaluation-modal-help"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> La numeración visible se actualizará automáticamente.</p>
                        </div>
                        <div class="modal-footer"><button class="button evaluation-modal-cancel" type="button" data-bs-dismiss="modal">Cancelar</button><button class="button evaluation-modal-save" type="submit"><i class="fa-solid fa-check" aria-hidden="true"></i> Guardar sección</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade evaluation-editor-modal" id="question-editor-modal" tabindex="-1" aria-labelledby="question-editor-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form id="question-editor-form" novalidate>
                        <div class="modal-header"><div class="evaluation-modal-heading"><span><i class="fa-solid fa-circle-question" aria-hidden="true"></i></span><div><small>Banco de preguntas</small><h2 id="question-editor-modal-title" class="modal-title">Agregar pregunta</h2></div></div><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                        <div class="modal-body">
                            <input id="question-edit-id" type="hidden"><input id="question-section-id" type="hidden">
                            <div class="evaluation-modal-grid">
                                <div class="evaluation-field evaluation-field-full"><label for="question-text-field">Texto de la pregunta</label><textarea id="question-text-field" class="form-control" rows="3" placeholder="Escribe una pregunta clara y directa" required></textarea></div>
                                <div class="evaluation-field"><label for="question-type-field">Tipo de pregunta</label><select id="question-type-field" class="form-select"><option value="multiple">Opción múltiple</option><option value="true-false">Verdadero / falso</option></select></div>
                                <div class="evaluation-field"><label for="question-points-field">Puntos</label><input id="question-points-field" class="form-control" type="number" min="1" value="1" required></div>
                            </div>

                            <fieldset class="evaluation-option-builder"><legend>Opciones y respuesta correcta</legend>
                                <div class="evaluation-option-row" data-option="a"><label for="question-option-a">Opción A</label><input id="question-option-a" class="form-control question-option-input" type="text" placeholder="Escribe la opción A"><label class="evaluation-correct-selector"><input type="radio" name="question-correct-answer" value="a"><span>Correcta</span></label></div>
                                <div class="evaluation-option-row" data-option="b"><label for="question-option-b">Opción B</label><input id="question-option-b" class="form-control question-option-input" type="text" placeholder="Escribe la opción B"><label class="evaluation-correct-selector"><input type="radio" name="question-correct-answer" value="b"><span>Correcta</span></label></div>
                                <div class="evaluation-option-row optional-answer-row" data-option="c"><label for="question-option-c">Opción C</label><input id="question-option-c" class="form-control question-option-input" type="text" placeholder="Escribe la opción C"><label class="evaluation-correct-selector"><input type="radio" name="question-correct-answer" value="c"><span>Correcta</span></label></div>
                                <div class="evaluation-option-row optional-answer-row" data-option="d"><label for="question-option-d">Opción D <small>(opcional)</small></label><input id="question-option-d" class="form-control question-option-input" type="text" placeholder="Escribe la opción D"><label class="evaluation-correct-selector"><input type="radio" name="question-correct-answer" value="d"><span>Correcta</span></label></div>
                            </fieldset>

                            <div class="evaluation-field evaluation-feedback-field"><label for="question-feedback-field">Retroalimentación breve <small>(opcional)</small></label><textarea id="question-feedback-field" class="form-control" rows="2" placeholder="Explica por qué la respuesta es correcta"></textarea></div>
                        </div>
                        <div class="modal-footer"><button class="button evaluation-modal-cancel" type="button" data-bs-dismiss="modal">Cancelar</button><button class="button evaluation-modal-save" type="submit"><i class="fa-solid fa-check" aria-hidden="true"></i> Guardar pregunta</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade evaluation-preview-modal" id="evaluation-preview-modal" tabindex="-1" aria-labelledby="evaluation-preview-title" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header"><div><span>Vista previa del participante</span><h2 id="evaluation-preview-title" class="modal-title">Evaluación de Metodología 5S</h2></div><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                    <div class="modal-body"><div id="evaluation-preview-content" class="evaluation-preview-content"></div></div>
                    <div class="modal-footer"><p><i class="fa-solid fa-eye" aria-hidden="true"></i> La respuesta correcta se marca solo porque estás en modo editor.</p><button class="button evaluation-modal-save" type="button" data-bs-dismiss="modal">Cerrar vista previa</button></div>
                </div>
            </div>
        </div>

        <div class="modal fade evaluation-delete-modal" id="delete-evaluation-item-modal" tabindex="-1" aria-labelledby="delete-evaluation-item-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body"><span><i class="fa-regular fa-trash-can" aria-hidden="true"></i></span><h2 id="delete-evaluation-item-title">¿Eliminar este elemento?</h2><p><strong id="delete-evaluation-item-name">El elemento</strong> se quitará de la maqueta, sin guardar cambios reales.</p></div><div class="modal-footer"><button class="button evaluation-modal-cancel" type="button" data-bs-dismiss="modal">Cancelar</button><button id="confirm-evaluation-item-delete" class="button evaluation-delete-confirm" type="button"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Eliminar</button></div></div></div>
        </div>

        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="evaluation-feedback-toast" class="toast evaluation-feedback-toast" role="status" aria-live="polite" aria-atomic="true"><div class="toast-body"><span><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span><p id="evaluation-feedback-message">Cambios guardados de forma simulada.</p><button class="btn-close" type="button" data-bs-dismiss="toast" aria-label="Cerrar"></button></div></div>
        </div>
<?php include __DIR__ . '/../includes/app-footer.php'; ?>
