<?php
$pageTitle = 'Examen del curso | CapacitaLíder';
$sectionTitle = 'Examen del curso';
$activePage = 'evaluacionConocimientos';
$basePath = '../';
$pageStyle = 'css/evaluacionPreguntas.css';
$pageScript = 'js/evaluacionPreguntas.js';

include __DIR__ . '/../includes/app-header.php';
?>
        <div class="exam-page">
            <a class="exam-back-link" href="evaluacionConocimientos.php">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Volver a evaluaciones</span>
            </a>

            <section class="exam-header-card" aria-labelledby="exam-title">
                <div class="exam-header-copy">
                    <span class="exam-course-label">
                        <i class="fa-solid fa-clipboard-question" aria-hidden="true"></i>
                        Evaluación de conocimientos
                    </span>
                    <h2 id="exam-title">Metodología 5S</h2>
                    <p>
                        Responde este examen para validar los conceptos, procedimientos y buenas
                        prácticas revisadas durante el curso.
                    </p>
                    <div class="exam-header-meta">
                        <span><i class="fa-solid fa-layer-group" aria-hidden="true"></i> 5 secciones</span>
                        <span><i class="fa-solid fa-list-check" aria-hidden="true"></i> 10 preguntas</span>
                        <span><i class="fa-regular fa-clock" aria-hidden="true"></i> 15 min aprox.</span>
                    </div>
                </div>

                <div class="exam-progress-card">
                    <div class="exam-progress-heading">
                        <div>
                            <span>Avance general</span>
                            <strong id="exam-current-section">Sección 1 de 5</strong>
                        </div>
                        <span id="exam-progress-value" class="exam-progress-value">20%</span>
                    </div>
                    <div
                        id="exam-progress"
                        class="progress"
                        role="progressbar"
                        aria-label="Avance del examen"
                        aria-valuenow="20"
                        aria-valuemin="0"
                        aria-valuemax="100"
                    >
                        <div class="progress-bar" style="width: 20%"></div>
                    </div>
                    <div class="exam-progress-footnote">
                        <span><strong id="completed-sections-count">0</strong> de 5 completadas</span>
                        <span id="exam-progress-label">20% del recorrido</span>
                    </div>
                </div>
            </section>

            <div id="exam-submission-alert" class="exam-submission-alert" role="status" aria-live="polite" hidden>
                <span class="submission-alert-icon">
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                </span>
                <div>
                    <strong>Prueba enviada correctamente</strong>
                    <p>El envío es una simulación visual. En esta etapa no se calculó ni guardó una calificación.</p>
                </div>
                <a class="button submission-return-button" href="evaluacionConocimientos.php">
                    Volver a evaluaciones
                </a>
            </div>

            <div class="exam-workspace">
                <aside class="exam-section-navigation" aria-labelledby="exam-navigation-title">
                    <div class="exam-navigation-heading">
                        <span class="exam-navigation-icon">
                            <i class="fa-solid fa-bars-staggered" aria-hidden="true"></i>
                        </span>
                        <div>
                            <span>Contenido del examen</span>
                            <h2 id="exam-navigation-title">Secciones</h2>
                        </div>
                        <button
                            id="exam-navigation-toggle"
                            class="exam-navigation-toggle"
                            type="button"
                            aria-label="Contraer secciones"
                            aria-controls="exam-section-list"
                            aria-expanded="true"
                        >
                            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                        </button>
                    </div>

                    <nav aria-label="Secciones del examen">
                        <ol id="exam-section-list" class="exam-section-list">
                            <li>
                                <button class="exam-section-button is-active" type="button" data-section-index="0" aria-selected="true">
                                    <span class="exam-section-number">1</span>
                                    <span class="exam-section-copy">
                                        <small>Sección 1</small>
                                        <strong>Conceptos básicos</strong>
                                        <span class="exam-section-state state-progress">
                                            <i class="fa-solid fa-circle-half-stroke" aria-hidden="true"></i>
                                            <span>En progreso</span>
                                        </span>
                                    </span>
                                </button>
                            </li>
                            <li>
                                <button class="exam-section-button" type="button" data-section-index="1" aria-selected="false">
                                    <span class="exam-section-number">2</span>
                                    <span class="exam-section-copy">
                                        <small>Sección 2</small>
                                        <strong>Procedimientos</strong>
                                        <span class="exam-section-state state-pending">
                                            <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                            <span>Pendiente</span>
                                        </span>
                                    </span>
                                </button>
                            </li>
                            <li>
                                <button class="exam-section-button" type="button" data-section-index="2" aria-selected="false">
                                    <span class="exam-section-number">3</span>
                                    <span class="exam-section-copy">
                                        <small>Sección 3</small>
                                        <strong>Seguridad y calidad</strong>
                                        <span class="exam-section-state state-pending">
                                            <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                            <span>Pendiente</span>
                                        </span>
                                    </span>
                                </button>
                            </li>
                            <li>
                                <button class="exam-section-button" type="button" data-section-index="3" aria-selected="false">
                                    <span class="exam-section-number">4</span>
                                    <span class="exam-section-copy">
                                        <small>Sección 4</small>
                                        <strong>Caso práctico</strong>
                                        <span class="exam-section-state state-pending">
                                            <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                            <span>Pendiente</span>
                                        </span>
                                    </span>
                                </button>
                            </li>
                            <li>
                                <button class="exam-section-button" type="button" data-section-index="4" aria-selected="false">
                                    <span class="exam-section-number">5</span>
                                    <span class="exam-section-copy">
                                        <small>Sección 5</small>
                                        <strong>Repaso final</strong>
                                        <span class="exam-section-state state-pending">
                                            <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                            <span>Pendiente</span>
                                        </span>
                                    </span>
                                </button>
                            </li>
                        </ol>
                    </nav>

                    <div class="exam-navigation-help">
                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                        <p>Puedes regresar a una sección para revisar o cambiar tus respuestas antes de enviar.</p>
                    </div>
                </aside>

                <section class="exam-question-area" aria-label="Preguntas del examen">
                    <div class="exam-instruction-banner">
                        <span><i class="fa-solid fa-lightbulb" aria-hidden="true"></i></span>
                        <div>
                            <strong>Instrucciones</strong>
                            <p>Selecciona una sola respuesta por pregunta. Responde las dos preguntas para completar la sección.</p>
                        </div>
                    </div>

                    <div class="exam-section-panel is-active" data-section-title="Conceptos básicos">
                        <div class="exam-section-header">
                            <div>
                                <span class="exam-section-eyebrow">Sección 1 de 5</span>
                                <h2>Introducción y conceptos básicos</h2>
                                <p>Identifica el propósito y los principios esenciales de la metodología 5S.</p>
                            </div>
                            <span class="section-question-count">2 preguntas</span>
                        </div>

                        <fieldset class="exam-question">
                            <legend>
                                <span class="question-number">Pregunta 1</span>
                                <span>¿Cuál es el propósito principal de la primera S: Seiri?</span>
                            </legend>
                            <div class="answer-options">
                                <label class="answer-option">
                                    <input type="radio" name="question-1" value="a">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Clasificar y retirar lo que no es necesario</span>
                                </label>
                                <label class="answer-option">
                                    <input type="radio" name="question-1" value="b">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Limpiar únicamente al finalizar el turno</span>
                                </label>
                                <label class="answer-option">
                                    <input type="radio" name="question-1" value="c">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Guardar todos los materiales disponibles</span>
                                </label>
                            </div>
                            <small class="question-required-message">Selecciona una respuesta para continuar.</small>
                        </fieldset>

                        <fieldset class="exam-question">
                            <legend>
                                <span class="question-number">Pregunta 2 · Verdadero o falso</span>
                                <span>La metodología 5S solo debe aplicarse en áreas de producción.</span>
                            </legend>
                            <div class="answer-options answer-options-inline">
                                <label class="answer-option">
                                    <input type="radio" name="question-2" value="true">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Verdadero</span>
                                </label>
                                <label class="answer-option">
                                    <input type="radio" name="question-2" value="false">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Falso</span>
                                </label>
                            </div>
                            <small class="question-required-message">Selecciona una respuesta para continuar.</small>
                        </fieldset>
                    </div>

                    <div class="exam-section-panel" data-section-title="Procedimientos" hidden>
                        <div class="exam-section-header">
                            <div>
                                <span class="exam-section-eyebrow">Sección 2 de 5</span>
                                <h2>Procedimientos</h2>
                                <p>Reconoce cómo organizar y mantener correctamente el área de trabajo.</p>
                            </div>
                            <span class="section-question-count">2 preguntas</span>
                        </div>

                        <fieldset class="exam-question">
                            <legend>
                                <span class="question-number">Pregunta 3</span>
                                <span>Después de clasificar los materiales, ¿qué acción corresponde a Seiton?</span>
                            </legend>
                            <div class="answer-options">
                                <label class="answer-option">
                                    <input type="radio" name="question-3" value="a">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Definir un lugar visible y accesible para cada elemento</span>
                                </label>
                                <label class="answer-option">
                                    <input type="radio" name="question-3" value="b">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Mover todos los elementos a una bodega temporal</span>
                                </label>
                                <label class="answer-option">
                                    <input type="radio" name="question-3" value="c">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Esperar una auditoría antes de ordenarlos</span>
                                </label>
                            </div>
                            <small class="question-required-message">Selecciona una respuesta para continuar.</small>
                        </fieldset>

                        <fieldset class="exam-question">
                            <legend>
                                <span class="question-number">Pregunta 4</span>
                                <span>¿Qué recurso facilita que cada herramienta regrese a su lugar?</span>
                            </legend>
                            <div class="answer-options">
                                <label class="answer-option">
                                    <input type="radio" name="question-4" value="a">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Señalización, etiquetas y siluetas visuales</span>
                                </label>
                                <label class="answer-option">
                                    <input type="radio" name="question-4" value="b">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Una lista guardada fuera del área</span>
                                </label>
                                <label class="answer-option">
                                    <input type="radio" name="question-4" value="c">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>La memoria de cada colaborador</span>
                                </label>
                            </div>
                            <small class="question-required-message">Selecciona una respuesta para continuar.</small>
                        </fieldset>
                    </div>

                    <div class="exam-section-panel" data-section-title="Seguridad y calidad" hidden>
                        <div class="exam-section-header">
                            <div>
                                <span class="exam-section-eyebrow">Sección 3 de 5</span>
                                <h2>Seguridad y calidad</h2>
                                <p>Relaciona el orden del espacio con la prevención de riesgos y la calidad.</p>
                            </div>
                            <span class="section-question-count">2 preguntas</span>
                        </div>

                        <fieldset class="exam-question">
                            <legend>
                                <span class="question-number">Pregunta 5</span>
                                <span>Encuentras un objeto bloqueando un pasillo. ¿Qué debes hacer primero?</span>
                            </legend>
                            <div class="answer-options">
                                <label class="answer-option">
                                    <input type="radio" name="question-5" value="a">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Retirarlo de forma segura y llevarlo al lugar definido</span>
                                </label>
                                <label class="answer-option">
                                    <input type="radio" name="question-5" value="b">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Dejarlo ahí si todavía es posible pasar</span>
                                </label>
                                <label class="answer-option">
                                    <input type="radio" name="question-5" value="c">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Moverlo hasta el siguiente turno</span>
                                </label>
                            </div>
                            <small class="question-required-message">Selecciona una respuesta para continuar.</small>
                        </fieldset>

                        <fieldset class="exam-question">
                            <legend>
                                <span class="question-number">Pregunta 6 · Verdadero o falso</span>
                                <span>Estandarizar ayuda a detectar desviaciones y mantener condiciones seguras.</span>
                            </legend>
                            <div class="answer-options answer-options-inline">
                                <label class="answer-option">
                                    <input type="radio" name="question-6" value="true">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Verdadero</span>
                                </label>
                                <label class="answer-option">
                                    <input type="radio" name="question-6" value="false">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Falso</span>
                                </label>
                            </div>
                            <small class="question-required-message">Selecciona una respuesta para continuar.</small>
                        </fieldset>
                    </div>

                    <div class="exam-section-panel" data-section-title="Caso práctico" hidden>
                        <div class="exam-section-header">
                            <div>
                                <span class="exam-section-eyebrow">Sección 4 de 5</span>
                                <h2>Caso práctico</h2>
                                <p>Aplica lo aprendido ante situaciones habituales del entorno de trabajo.</p>
                            </div>
                            <span class="section-question-count">2 preguntas</span>
                        </div>

                        <fieldset class="exam-question">
                            <legend>
                                <span class="question-number">Pregunta 7</span>
                                <span>Varias herramientas no tienen ubicación identificada. ¿Cuál es la mejor acción?</span>
                            </legend>
                            <div class="answer-options">
                                <label class="answer-option">
                                    <input type="radio" name="question-7" value="a">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Definir ubicaciones, señalizarlas y comunicar el estándar</span>
                                </label>
                                <label class="answer-option">
                                    <input type="radio" name="question-7" value="b">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Colocarlas juntas sin identificar su posición</span>
                                </label>
                                <label class="answer-option">
                                    <input type="radio" name="question-7" value="c">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Permitir que cada persona elija dónde guardarlas</span>
                                </label>
                            </div>
                            <small class="question-required-message">Selecciona una respuesta para continuar.</small>
                        </fieldset>

                        <fieldset class="exam-question">
                            <legend>
                                <span class="question-number">Pregunta 8</span>
                                <span>¿Qué práctica ayuda a mantener las mejoras de las 5S a largo plazo?</span>
                            </legend>
                            <div class="answer-options">
                                <label class="answer-option">
                                    <input type="radio" name="question-8" value="a">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Realizar revisiones periódicas y reforzar los hábitos</span>
                                </label>
                                <label class="answer-option">
                                    <input type="radio" name="question-8" value="b">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Aplicar la metodología una sola vez</span>
                                </label>
                                <label class="answer-option">
                                    <input type="radio" name="question-8" value="c">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Revisar únicamente cuando exista una falla</span>
                                </label>
                            </div>
                            <small class="question-required-message">Selecciona una respuesta para continuar.</small>
                        </fieldset>
                    </div>

                    <div class="exam-section-panel" data-section-title="Repaso final" hidden>
                        <div class="exam-section-header">
                            <div>
                                <span class="exam-section-eyebrow">Sección 5 de 5</span>
                                <h2>Repaso final</h2>
                                <p>Confirma las acciones que ayudan a conservar las mejoras de forma constante.</p>
                            </div>
                            <span class="section-question-count">2 preguntas</span>
                        </div>

                        <fieldset class="exam-question">
                            <legend>
                                <span class="question-number">Pregunta 9 · Verdadero o falso</span>
                                <span>Las revisiones breves y periódicas ayudan a mantener los estándares de las 5S.</span>
                            </legend>
                            <div class="answer-options answer-options-inline">
                                <label class="answer-option">
                                    <input type="radio" name="question-9" value="true">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Verdadero</span>
                                </label>
                                <label class="answer-option">
                                    <input type="radio" name="question-9" value="false">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Falso</span>
                                </label>
                            </div>
                            <small class="question-required-message">Selecciona una respuesta para continuar.</small>
                        </fieldset>

                        <fieldset class="exam-question">
                            <legend>
                                <span class="question-number">Pregunta 10</span>
                                <span>¿Qué acción fortalece la disciplina necesaria para sostener las 5S?</span>
                            </legend>
                            <div class="answer-options">
                                <label class="answer-option">
                                    <input type="radio" name="question-10" value="a">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Cumplir los estándares y corregir oportunamente las desviaciones</span>
                                </label>
                                <label class="answer-option">
                                    <input type="radio" name="question-10" value="b">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Esperar a que otra persona ordene el área</span>
                                </label>
                                <label class="answer-option">
                                    <input type="radio" name="question-10" value="c">
                                    <span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                    <span>Aplicar los estándares únicamente antes de una auditoría</span>
                                </label>
                            </div>
                            <small class="question-required-message">Selecciona una respuesta para continuar.</small>
                        </fieldset>
                    </div>

                    <div class="exam-navigation-actions">
                        <button id="previous-exam-section" class="button exam-secondary-button" type="button" disabled>
                            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                            Anterior
                        </button>

                        <div id="section-answer-status" class="section-answer-status" role="status" aria-live="polite">
                            <i class="fa-regular fa-circle" aria-hidden="true"></i>
                            <span>Faltan 2 preguntas por responder</span>
                        </div>

                        <button id="next-exam-section" class="button exam-primary-button" type="button">
                            Siguiente sección
                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </button>

                        <button id="finish-exam" class="button exam-submit-button" type="button" disabled hidden>
                            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                            Finalizar y enviar
                        </button>
                    </div>
                </section>
            </div>
        </div>

        <div class="modal fade exam-submit-modal" id="submit-exam-modal" tabindex="-1" aria-labelledby="submit-exam-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <span class="modal-warning-icon">
                            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                        </span>
                        <h2 id="submit-exam-title">¿Enviar la prueba?</h2>
                        <p>¿Estás seguro de enviar la prueba? Después de enviarla no podrás modificar tus respuestas.</p>
                        <div class="modal-exam-summary">
                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                            <span>Completaste las 5 secciones y respondiste 10 preguntas.</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="button modal-cancel-button" type="button" data-bs-dismiss="modal">Cancelar</button>
                        <button id="confirm-exam-submit" class="button modal-submit-button" type="button">
                            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                            Enviar prueba
                        </button>
                    </div>
                </div>
            </div>
        </div>
<?php include __DIR__ . '/../includes/app-footer.php'; ?>
