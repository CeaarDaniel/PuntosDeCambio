(function ($) {
    "use strict";

    $(function () {
        const sectionModalElement = document.getElementById("section-editor-modal");
        const questionModalElement = document.getElementById("question-editor-modal");
        const previewModalElement = document.getElementById("evaluation-preview-modal");
        const deleteModalElement = document.getElementById("delete-evaluation-item-modal");
        const toastElement = document.getElementById("evaluation-feedback-toast");
        const sectionModal = getModal(sectionModalElement);
        const questionModal = getModal(questionModalElement);
        const previewModal = getModal(previewModalElement);
        const deleteModal = getModal(deleteModalElement);
        const feedbackToast = getToast(toastElement);
        let currentSectionId = "section-1";
        let sectionCounter = 3;
        let questionCounter = 5;
        let pendingDeletion = null;

        function getModal(element) {
            if (!element || !window.bootstrap || !window.bootstrap.Modal) {
                return null;
            }

            return window.bootstrap.Modal.getOrCreateInstance(element);
        }

        function getToast(element) {
            if (!element || !window.bootstrap || !window.bootstrap.Toast) {
                return null;
            }

            return window.bootstrap.Toast.getOrCreateInstance(element, { delay: 3200 });
        }

        function showModal(instance, element) {
            if (instance) {
                instance.show();
            } else if (element) {
                $(element).addClass("show").css("display", "block").attr("aria-hidden", "false");
            }
        }

        function hideModal(instance, element) {
            if (instance) {
                instance.hide();
            } else if (element) {
                $(element).removeClass("show").css("display", "none").attr("aria-hidden", "true");
            }
        }

        function escapeHtml(value) {
            const characters = {
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                "\"": "&quot;",
                "'": "&#039;"
            };

            return String(value == null ? "" : value).replace(/[&<>"']/g, function (character) {
                return characters[character];
            });
        }

        function showFeedback(message) {
            $("#evaluation-feedback-message").text(message);

            if (feedbackToast) {
                feedbackToast.show();
                return;
            }

            $(toastElement).addClass("show");
            window.setTimeout(function () {
                $(toastElement).removeClass("show");
            }, 3200);
        }

        function sectionItem(sectionId) {
            return $('.evaluation-section-item[data-section-id="' + sectionId + '"]').first();
        }

        function sectionPanel(sectionId) {
            return $('.evaluation-section-questions[data-section-id="' + sectionId + '"]').first();
        }

        function pluralize(total, singular, plural) {
            return total === 1 ? singular : plural;
        }

        function updateSectionMetrics($item) {
            const sectionId = String($item.attr("data-section-id"));
            const $panel = sectionPanel(sectionId);
            const $questions = $panel.find(".evaluation-question-card");
            const questionTotal = $questions.length;
            const pointsTotal = $questions.toArray().reduce(function (total, question) {
                return total + (Number($(question).attr("data-question-points")) || 0);
            }, 0);
            const incompleteTotal = $questions.filter(".is-incomplete").length;
            const isComplete = questionTotal > 0 && incompleteTotal === 0;
            const $state = $item.find(".exam-section-state");

            $item.find(".section-question-total").text(questionTotal);
            $item.find(".section-points-total").text(pointsTotal);
            $item.find(".evaluation-section-actions > span").contents().filter(function () {
                return this.nodeType === 3;
            }).remove();
            $item.find(".section-question-total").after(document.createTextNode(" " + pluralize(questionTotal, "pregunta", "preguntas") + " · "));
            $item.find(".section-points-total").after(document.createTextNode(" pts"));
            $item.find(".evaluation-section-select").toggleClass("is-completed", isComplete);
            $state
                .attr("class", "exam-section-state " + (isComplete ? "state-completed" : "state-progress"))
                .html(
                    isComplete
                        ? '<i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>Completa</span>'
                        : '<i class="fa-solid fa-circle-half-stroke" aria-hidden="true"></i><span>Incompleta</span>'
                );

            return { questions: questionTotal, points: pointsTotal, incomplete: incompleteTotal };
        }

        function updateCurrentSectionHeader() {
            const $item = sectionItem(currentSectionId);

            if (!$item.length) {
                $("#current-section-eyebrow").text("Sin secciones");
                $("#current-section-title").text("Agrega una sección para comenzar");
                $("#current-section-description").text("La evaluación necesita al menos una sección para organizar sus preguntas.");
                $("#current-section-summary").text("0 preguntas · 0 puntos");
                $("#edit-current-section, #add-evaluation-question").prop("disabled", true);
                $("#empty-section-state").prop("hidden", false);
                return;
            }

            const position = $("#evaluation-section-list .evaluation-section-item").index($item) + 1;
            const totalSections = $("#evaluation-section-list .evaluation-section-item").length;
            const metrics = updateSectionMetrics($item);

            $("#current-section-eyebrow").text("Sección " + position + " de " + totalSections);
            $("#current-section-title").text($item.attr("data-section-title"));
            $("#current-section-description").text($item.attr("data-section-description") || "Sin descripción adicional.");
            $("#current-section-summary").text(
                metrics.questions + " " + pluralize(metrics.questions, "pregunta", "preguntas") + " · " + metrics.points + " puntos"
            );
            $("#edit-current-section, #add-evaluation-question").prop("disabled", false);
            $("#empty-section-state").prop("hidden", metrics.questions > 0);
        }

        function updateSummary() {
            let questionTotal = 0;
            let pointsTotal = 0;
            let pendingTotal = 0;
            const sectionTotal = $("#evaluation-section-list .evaluation-section-item").length;

            $("#evaluation-section-list .evaluation-section-item").each(function () {
                const metrics = updateSectionMetrics($(this));
                questionTotal += metrics.questions;
                pointsTotal += metrics.points;
                pendingTotal += metrics.incomplete;
            });

            const configuredTotal = questionTotal - pendingTotal;
            const progress = questionTotal > 0 ? Math.round((configuredTotal / questionTotal) * 100) : 0;
            const passingScore = Math.min(100, Math.max(0, Number($("#evaluation-passing-score").val()) || 0));

            $("#summary-section-count, #header-section-count").text(sectionTotal);
            $("#summary-question-count, #header-question-count").text(questionTotal);
            $("#summary-points-count").text(pointsTotal);
            $("#evaluation-total-score").val(pointsTotal);
            $("#summary-passing-score").text(passingScore + "%");
            $("#summary-configured-count").text(configuredTotal);
            $("#summary-pending-count").text(pendingTotal);
            $("#evaluation-configuration-status").text(
                configuredTotal + " de " + questionTotal + " " + pluralize(questionTotal, "pregunta configurada", "preguntas configuradas")
            );
            $("#evaluation-configuration-progress")
                .attr("aria-valuenow", progress)
                .find(".progress-bar")
                .css("width", progress + "%");

            updateCurrentSectionHeader();
        }

        function selectSection($item) {
            if (!$item.length) {
                currentSectionId = "";
                $(".evaluation-section-questions").removeClass("is-active").prop("hidden", true);
                updateCurrentSectionHeader();
                return;
            }

            currentSectionId = String($item.attr("data-section-id"));
            $(".evaluation-section-item").removeClass("is-active");
            $(".evaluation-section-select").removeClass("is-active").attr("aria-selected", "false");
            $item.addClass("is-active").find(".evaluation-section-select").addClass("is-active").attr("aria-selected", "true");
            $(".evaluation-section-questions").removeClass("is-active").prop("hidden", true);
            sectionPanel(currentSectionId).addClass("is-active").prop("hidden", false);
            updateCurrentSectionHeader();
        }

        function sortAndRenumberSections() {
            const $list = $("#evaluation-section-list");
            const items = $list.children(".evaluation-section-item").get().sort(function (first, second) {
                return (Number($(first).attr("data-section-order")) || 999) - (Number($(second).attr("data-section-order")) || 999);
            });

            $.each(items, function (index, item) {
                const number = index + 1;
                const $item = $(item);
                $item.attr("data-section-order", number);
                $item.find(".exam-section-number").text(number);
                $item.find(".exam-section-copy > small").text("Sección " + number);
                $list.append($item);
            });
        }

        function renumberQuestions($panel) {
            $panel.find(".evaluation-question-card").each(function (index) {
                const number = index + 1;
                $(this).find(".question-number").first().text("Pregunta " + number);
                $(this).find(".edit-evaluation-question").attr("aria-label", "Editar pregunta " + number);
                $(this).find(".delete-evaluation-question").attr("aria-label", "Eliminar pregunta " + number);
            });
        }

        function sectionMarkup(values) {
            const title = escapeHtml(values.title);
            const description = escapeHtml(values.description);

            return '<li class="evaluation-section-item" data-section-id="' + values.id + '" data-section-title="' + title + '" data-section-description="' + description + '" data-section-order="' + values.order + '">' +
                '<button class="exam-section-button evaluation-section-select" type="button" aria-selected="false"><span class="exam-section-number">' + values.order + '</span><span class="exam-section-copy"><small>Sección ' + values.order + '</small><strong>' + title + '</strong><span class="exam-section-state state-progress"><i class="fa-solid fa-circle-half-stroke" aria-hidden="true"></i><span>Incompleta</span></span></span></button>' +
                '<div class="evaluation-section-actions"><span><b class="section-question-total">0</b> preguntas · <b class="section-points-total">0</b> pts</span><div><button class="edit-evaluation-section" type="button" aria-label="Editar ' + title + '"><i class="fa-solid fa-pen" aria-hidden="true"></i></button><button class="delete-evaluation-section" type="button" aria-label="Eliminar ' + title + '"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button></div></div>' +
            '</li>';
        }

        function questionMarkup(values) {
            const questionText = escapeHtml(values.text);
            const feedback = escapeHtml(values.feedback);
            const isTrueFalse = values.type === "true-false";
            const typeLabel = isTrueFalse ? "Verdadero / falso" : "Opción múltiple";
            const typeIcon = isTrueFalse ? "fa-toggle-on" : "fa-list-ul";
            let optionsMarkup = "";

            $.each(values.options, function (_, option) {
                if (!option.text) {
                    return;
                }

                const isCorrect = option.value === values.correct;
                optionsMarkup += '<div class="answer-option' + (isCorrect ? " is-correct-answer" : "") + '" data-option-value="' + option.value + '"><span class="answer-marker"><i class="fa-solid fa-check" aria-hidden="true"></i></span><span class="answer-text">' + escapeHtml(option.text) + '</span>' + (isCorrect ? "<small>Correcta</small>" : "") + "</div>";
            });

            return '<article class="exam-question evaluation-question-card" data-question-id="' + values.id + '" data-question-type="' + values.type + '" data-question-points="' + values.points + '" data-correct-answer="' + values.correct + '" data-feedback="' + feedback + '">' +
                '<div class="evaluation-question-toolbar"><span class="question-number">Pregunta</span><span class="evaluation-question-type"><i class="fa-solid ' + typeIcon + '" aria-hidden="true"></i> ' + typeLabel + '</span><span class="evaluation-question-points"><i class="fa-solid fa-star" aria-hidden="true"></i> ' + values.points + ' ' + pluralize(values.points, "punto", "puntos") + '</span><div><button class="edit-evaluation-question" type="button" aria-label="Editar pregunta"><i class="fa-solid fa-pen" aria-hidden="true"></i></button><button class="delete-evaluation-question" type="button" aria-label="Eliminar pregunta"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button></div></div>' +
                '<h3 class="evaluation-question-text">' + questionText + '</h3><div class="answer-options evaluation-answer-options' + (isTrueFalse ? " answer-options-inline" : "") + '">' + optionsMarkup + "</div>" +
                (feedback ? '<p class="evaluation-question-feedback"><i class="fa-regular fa-message" aria-hidden="true"></i> ' + feedback + "</p>" : "") +
            "</article>";
        }

        function resetSectionForm() {
            const form = document.getElementById("section-editor-form");

            form.reset();
            $(form).removeClass("was-validated");
            $("#section-edit-id").val("");
            $("#section-order-field").val($(".evaluation-section-item").length + 1);
            $("#section-editor-modal-title").text("Agregar sección");
        }

        function openSectionEditor($item) {
            resetSectionForm();

            if ($item && $item.length) {
                $("#section-edit-id").val($item.attr("data-section-id"));
                $("#section-title-field").val($item.attr("data-section-title"));
                $("#section-description-field").val($item.attr("data-section-description"));
                $("#section-order-field").val($item.attr("data-section-order"));
                $("#section-editor-modal-title").text("Editar sección");
            }

            showModal(sectionModal, sectionModalElement);
        }

        function configureQuestionType(type, resetValues) {
            const isTrueFalse = type === "true-false";
            const $optionalRows = $(".optional-answer-row");

            $optionalRows.prop("hidden", isTrueFalse).find(":input").prop("disabled", isTrueFalse);
            if (isTrueFalse) {
                $optionalRows.find('input[type="radio"]').prop("checked", false);
                $("#question-option-a").val("Verdadero");
                $("#question-option-b").val("Falso");
            } else if (resetValues) {
                $(".question-option-input").val("");
                $('input[name="question-correct-answer"]').prop("checked", false);
            }
        }

        function resetQuestionForm() {
            const form = document.getElementById("question-editor-form");

            form.reset();
            $(form).removeClass("was-validated");
            $("#question-edit-id").val("");
            $("#question-section-id").val(currentSectionId);
            $("#question-points-field").val(1);
            $("#question-editor-modal-title").text("Agregar pregunta");
            configureQuestionType("multiple", true);
        }

        function openQuestionEditor($question) {
            resetQuestionForm();

            if ($question && $question.length) {
                const type = String($question.attr("data-question-type"));
                const correct = String($question.attr("data-correct-answer"));
                const options = {};

                $question.find(".answer-option").each(function () {
                    options[String($(this).attr("data-option-value"))] = $.trim($(this).find(".answer-text").text());
                });
                $("#question-edit-id").val($question.attr("data-question-id"));
                $("#question-section-id").val($question.closest(".evaluation-section-questions").attr("data-section-id"));
                $("#question-text-field").val($.trim($question.find(".evaluation-question-text").text()));
                $("#question-type-field").val(type);
                $("#question-points-field").val($question.attr("data-question-points"));
                $("#question-feedback-field").val($question.attr("data-feedback"));
                configureQuestionType(type, false);

                if (type === "true-false") {
                    $('input[name="question-correct-answer"][value="' + (correct === "true" ? "a" : "b") + '"]').prop("checked", true);
                } else {
                    $("#question-option-a").val(options.a || "");
                    $("#question-option-b").val(options.b || "");
                    $("#question-option-c").val(options.c || "");
                    $("#question-option-d").val(options.d || "");
                    $('input[name="question-correct-answer"][value="' + correct + '"]').prop("checked", true);
                }
                $("#question-editor-modal-title").text("Editar pregunta");
            }

            showModal(questionModal, questionModalElement);
        }

        function openDeleteConfirmation($target, type, name) {
            pendingDeletion = { $target: $target, type: type, name: name };
            $("#delete-evaluation-item-name").text(name);
            showModal(deleteModal, deleteModalElement);
        }

        function buildPreview() {
            const evaluationName = $.trim($("#evaluation-name").val()) || "Evaluación sin nombre";
            let previewMarkup = "";

            $("#evaluation-section-list .evaluation-section-item").each(function (sectionIndex) {
                const $item = $(this);
                const $questions = sectionPanel(String($item.attr("data-section-id"))).find(".evaluation-question-card");
                let questionsMarkup = "";

                $questions.each(function (questionIndex) {
                    const $question = $(this);

                    if ($question.hasClass("is-incomplete")) {
                        questionsMarkup += '<article class="evaluation-preview-question"><h4>Pregunta ' + (questionIndex + 1) + ': pendiente de configurar</h4><div class="evaluation-incomplete-callout"><i class="fa-regular fa-clock" aria-hidden="true"></i><p>Esta pregunta todavía necesita opciones y respuesta correcta.</p></div></article>';
                        return;
                    }

                    let optionsMarkup = "";
                    $question.find(".answer-option").each(function () {
                        const isCorrect = $(this).hasClass("is-correct-answer");
                        optionsMarkup += '<div class="evaluation-preview-option' + (isCorrect ? " is-correct" : "") + '"><i class="fa-' + (isCorrect ? "solid fa-circle-check" : "regular fa-circle") + '" aria-hidden="true"></i><span>' + escapeHtml($.trim($(this).find(".answer-text").text())) + (isCorrect ? " · Correcta" : "") + "</span></div>";
                    });
                    questionsMarkup += '<article class="evaluation-preview-question"><h4>Pregunta ' + (questionIndex + 1) + ". " + escapeHtml($.trim($question.find(".evaluation-question-text").text())) + "</h4>" + optionsMarkup + "</article>";
                });

                previewMarkup += '<section class="evaluation-preview-section"><header><span>Sección ' + (sectionIndex + 1) + "</span><h3>" + escapeHtml(String($item.attr("data-section-title"))) + "</h3><p>" + escapeHtml(String($item.attr("data-section-description") || "")) + "</p></header>" + (questionsMarkup || '<p class="evaluation-navigation-help">Sin preguntas configuradas.</p>') + "</section>";
            });

            $("#evaluation-preview-title").text(evaluationName);
            $("#evaluation-preview-content").html(previewMarkup || '<div class="evaluation-empty-state"><i class="fa-solid fa-layer-group" aria-hidden="true"></i><h3>Sin secciones para mostrar</h3></div>');
            showModal(previewModal, previewModalElement);
        }

        $(document).on("click", ".evaluation-section-select", function () {
            selectSection($(this).closest(".evaluation-section-item"));
        });

        $(document).on("click", "#add-evaluation-section, .evaluation-add-section-button", function () {
            openSectionEditor(null);
        });

        $(document).on("click", ".edit-evaluation-section", function () {
            openSectionEditor($(this).closest(".evaluation-section-item"));
        });

        $("#edit-current-section").on("click", function () {
            openSectionEditor(sectionItem(currentSectionId));
        });

        $("#section-editor-form").on("submit", function (event) {
            event.preventDefault();

            if (!this.checkValidity()) {
                $(this).addClass("was-validated");
                this.querySelector(":invalid").focus();
                return;
            }

            const editId = String($("#section-edit-id").val());
            const values = {
                id: editId || "section-" + (++sectionCounter),
                title: $.trim($("#section-title-field").val()),
                description: $.trim($("#section-description-field").val()),
                order: Math.max(1, Number($("#section-order-field").val()) || 1)
            };
            let $item;

            if (editId) {
                $item = sectionItem(editId)
                    .attr("data-section-title", values.title)
                    .attr("data-section-description", values.description)
                    .attr("data-section-order", values.order);
                $item.find(".exam-section-copy > strong").text(values.title);
                $item.find(".edit-evaluation-section").attr("aria-label", "Editar " + values.title);
                $item.find(".delete-evaluation-section").attr("aria-label", "Eliminar " + values.title);
            } else {
                $item = $(sectionMarkup(values)).appendTo("#evaluation-section-list");
                $('<div class="evaluation-section-questions" data-section-id="' + values.id + '" hidden></div>').appendTo("#evaluation-question-panels");
            }

            sortAndRenumberSections();
            selectSection($item);
            updateSummary();
            hideModal(sectionModal, sectionModalElement);
            showFeedback(editId ? "Sección actualizada de forma simulada." : "Sección agregada de forma simulada.");
        });

        $(document).on("click", ".evaluation-add-question-button", function () {
            if (currentSectionId) {
                openQuestionEditor(null);
            }
        });

        $(document).on("click", ".edit-evaluation-question", function () {
            openQuestionEditor($(this).closest(".evaluation-question-card"));
        });

        $("#question-type-field").on("change", function () {
            configureQuestionType($(this).val(), true);
        });

        $("#question-editor-form").on("submit", function (event) {
            event.preventDefault();

            if (!this.checkValidity()) {
                $(this).addClass("was-validated");
                this.querySelector(":invalid").focus();
                return;
            }

            const type = String($("#question-type-field").val());
            const selectedCorrect = $('input[name="question-correct-answer"]:checked').val();
            const optionA = $.trim($("#question-option-a").val());
            const optionB = $.trim($("#question-option-b").val());

            if (!selectedCorrect || !optionA || !optionB) {
                showFeedback("Completa las opciones A y B y selecciona la respuesta correcta.");
                (!optionA ? $("#question-option-a") : !optionB ? $("#question-option-b") : $('input[name="question-correct-answer"]').first()).trigger("focus");
                return;
            }

            if (type === "multiple" && !$.trim($("#question-option-" + selectedCorrect).val())) {
                showFeedback("La respuesta marcada como correcta necesita texto.");
                $("#question-option-" + selectedCorrect).trigger("focus");
                return;
            }

            const editId = String($("#question-edit-id").val());
            const sectionId = String($("#question-section-id").val());
            const isTrueFalse = type === "true-false";
            const values = {
                id: editId || "question-" + (++questionCounter),
                text: $.trim($("#question-text-field").val()),
                type: type,
                points: Math.max(1, Number($("#question-points-field").val()) || 1),
                correct: isTrueFalse ? (selectedCorrect === "a" ? "true" : "false") : selectedCorrect,
                feedback: $.trim($("#question-feedback-field").val()),
                options: isTrueFalse
                    ? [{ value: "true", text: "Verdadero" }, { value: "false", text: "Falso" }]
                    : [
                        { value: "a", text: optionA },
                        { value: "b", text: optionB },
                        { value: "c", text: $.trim($("#question-option-c").val()) },
                        { value: "d", text: $.trim($("#question-option-d").val()) }
                    ]
            };
            const $panel = sectionPanel(sectionId);
            const $newQuestion = $(questionMarkup(values));

            if (editId) {
                $panel.find('.evaluation-question-card[data-question-id="' + editId + '"]').replaceWith($newQuestion);
            } else {
                $panel.append($newQuestion);
            }

            renumberQuestions($panel);
            selectSection(sectionItem(sectionId));
            updateSummary();
            hideModal(questionModal, questionModalElement);
            showFeedback(editId ? "Pregunta actualizada de forma simulada." : "Pregunta agregada de forma simulada.");
        });

        $(document).on("click", ".delete-evaluation-section", function () {
            const $item = $(this).closest(".evaluation-section-item");

            openDeleteConfirmation($item, "section", String($item.attr("data-section-title")));
        });

        $(document).on("click", ".delete-evaluation-question", function () {
            const $question = $(this).closest(".evaluation-question-card");

            openDeleteConfirmation($question, "question", $.trim($question.find(".question-number").text()));
        });

        $("#confirm-evaluation-item-delete").on("click", function () {
            if (!pendingDeletion || !pendingDeletion.$target.length) {
                hideModal(deleteModal, deleteModalElement);
                return;
            }

            if (pendingDeletion.type === "section") {
                const sectionId = String(pendingDeletion.$target.attr("data-section-id"));
                const wasActive = sectionId === currentSectionId;
                const $nextItem = pendingDeletion.$target.next(".evaluation-section-item").length
                    ? pendingDeletion.$target.next(".evaluation-section-item")
                    : pendingDeletion.$target.prev(".evaluation-section-item");

                sectionPanel(sectionId).remove();
                pendingDeletion.$target.remove();
                sortAndRenumberSections();
                if (wasActive) {
                    selectSection($nextItem);
                }
            } else {
                const $panel = pendingDeletion.$target.closest(".evaluation-section-questions");
                pendingDeletion.$target.remove();
                renumberQuestions($panel);
            }

            hideModal(deleteModal, deleteModalElement);
            showFeedback(pendingDeletion.name + " se eliminó de la maqueta.");
            pendingDeletion = null;
            updateSummary();
        });

        $("#evaluation-passing-score").on("input", updateSummary);

        $("#evaluation-name").on("input", function () {
            $("#evaluation-editor-title").text($(this).val() || "Evaluación sin nombre");
        });

        $("#evaluation-status").on("change", function () {
            const status = $(this).val();
            const icon = status === "Activa" ? "fa-circle-check" : status === "Inactiva" ? "fa-circle-minus" : "fa-pen";

            $(".evaluation-status-badge").html('<i class="fa-solid ' + icon + '" aria-hidden="true"></i> ' + status);
        });

        $("#open-evaluation-preview, .open-evaluation-preview").on("click", buildPreview);
        $("#save-evaluation, .save-evaluation").on("click", function () {
            showFeedback("Cambios guardados correctamente de forma simulada.");
        });

        $(sectionModalElement).on("shown.bs.modal", function () {
            $("#section-title-field").trigger("focus");
        });

        $(questionModalElement).on("shown.bs.modal", function () {
            $("#question-text-field").trigger("focus");
        });

        sortAndRenumberSections();
        selectSection(sectionItem(currentSectionId));
        updateSummary();
    });
})(jQuery);
