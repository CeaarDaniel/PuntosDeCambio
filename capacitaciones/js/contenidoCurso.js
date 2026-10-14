$(function () {
    const moduleNames = [
        "Módulo 1: Introducción",
        "Módulo 2: Procedimiento básico",
        "Módulo 3: Buenas prácticas",
        "Módulo 4: Validación de conocimientos",
        "Módulo 5: Cuestionario de prueba"
    ];
    const initialProgress = 65;
    const completedModules = new Set();
    const $moduleButtons = $(".module-button");
    const $modulePanels = $(".module-content-panel");
    const $previousButton = $("#previous-module");
    const $nextButton = $("#next-module");
    const $markViewedButton = $("#mark-module-viewed");
    const $evaluationButton = $("#start-evaluation");
    const $learningLayout = $(".learning-layout");
    const $moduleNavigation = $(".module-navigation");
    const $moduleNavigationToggle = $("#module-navigation-toggle");
    let currentModuleIndex = 0;

    function updateCourseProgress() {
        const completedProgress = Math.round((100 - initialProgress) * (completedModules.size / moduleNames.length));
        const progress = Math.min(100, initialProgress + completedProgress);
        const progressText = progress + "%";

        $("#course-progress-text, #course-progress-ring").text(progressText);
        $("#course-progress")
            .attr("aria-valuenow", progress)
            .find(".progress-bar")
            .css("width", progressText);
        $(".progress-ring").css(
            "background",
            "conic-gradient(var(--success) " + progress + "%, rgba(255, 255, 255, 0.16) 0)"
        );

        if (progress === 100) {
            $("#course-status")
                .addClass("is-completed")
                .html('<i class="fa-solid fa-circle-check" aria-hidden="true"></i> Completado');
        }
    }

    function updateViewedButton() {
        const isCompleted = completedModules.has(currentModuleIndex);

        $markViewedButton
            .toggleClass("is-completed", isCompleted)
            .find("span")
            .text(isCompleted ? "Módulo visto" : "Marcar como visto");
        $markViewedButton
            .find("i")
            .attr("class", isCompleted ? "fa-solid fa-circle-check" : "fa-regular fa-circle-check");
    }

    function showModule(index) {
        if (index < 0 || index >= moduleNames.length) {
            return;
        }

        currentModuleIndex = index;
        $moduleButtons
            .removeClass("is-active")
            .attr("aria-selected", "false")
            .eq(index)
            .addClass("is-active")
            .attr("aria-selected", "true");
        $modulePanels
            .removeClass("is-active")
            .prop("hidden", true)
            .eq(index)
            .addClass("is-active")
            .prop("hidden", false);

        $("#current-module-name").text(moduleNames[index]);
        $("#current-module-position").text(index + 1 + " de " + moduleNames.length);
        $previousButton.prop("disabled", index === 0);
        $nextButton.prop("hidden", index === moduleNames.length - 1);
        $evaluationButton.prop("hidden", index !== moduleNames.length - 1);
        $("#evaluation-feedback").removeClass("feedback-success").empty();
        updateViewedButton();
    }

    function completeModule(index) {
        completedModules.add(index);
        $moduleButtons.eq(index).addClass("is-completed");
        updateViewedButton();
        updateCourseProgress();
    }

    function completeActivity($activity, message) {
        $activity.addClass("is-completed");
        $activity.find(".activity-status").first().text("Actividad completada");
        $activity
            .find(".activity-feedback")
            .first()
            .removeClass("feedback-error")
            .addClass("feedback-success")
            .text(message);
    }

    $moduleButtons.on("click", function () {
        showModule(Number($(this).data("module-index")));
    });

    $previousButton.on("click", function () {
        showModule(currentModuleIndex - 1);
    });

    $nextButton.on("click", function () {
        completeModule(currentModuleIndex);
        showModule(currentModuleIndex + 1);
    });

    $markViewedButton.on("click", function () {
        completeModule(currentModuleIndex);
    });

    $moduleNavigationToggle.on("click", function () {
        const willCollapse = !$moduleNavigation.hasClass("is-collapsed");

        $moduleNavigation.toggleClass("is-collapsed", willCollapse);
        $learningLayout.toggleClass("modules-collapsed", willCollapse);
        $(this)
            .attr("aria-expanded", !willCollapse)
            .attr("aria-label", willCollapse ? "Expandir módulos" : "Comprimir módulos")
            .find("i")
            .attr("class", willCollapse ? "fa-solid fa-chevron-right" : "fa-solid fa-chevron-left");
    });

    $("#simulated-video").on("click", function () {
        const isPlaying = !$(this).hasClass("is-playing");

        $(this)
            .toggleClass("is-playing", isPlaying)
            .attr("aria-pressed", isPlaying)
            .find(".video-play-icon i")
            .attr("class", isPlaying ? "fa-solid fa-pause" : "fa-solid fa-play");
        $("#video-status").text(
            isPlaying ? "Reproduciendo video simulado" : "Video en pausa · Presiona para continuar"
        );
    });

    $(".quiz-option").on("click", function () {
        const $option = $(this);
        const $quiz = $option.closest(".quick-quiz");
        const isCorrect = String($option.data("correct")) === "true";

        $quiz.find(".quiz-option").removeClass("is-correct is-incorrect");
        $option.addClass(isCorrect ? "is-correct" : "is-incorrect");

        if (isCorrect) {
            completeActivity($quiz, "Respuesta correcta. Puedes continuar con el contenido.");
        } else {
            $quiz
                .find(".activity-feedback")
                .removeClass("feedback-success")
                .addClass("feedback-error")
                .text("Revisa el material e intenta nuevamente.");
        }
    });

    $(".learning-checklist input").on("change", function () {
        const $activity = $(this).closest(".checklist-activity");
        const checkedItems = $activity.find("input:checked").length;

        $activity.removeClass("is-completed");
        $activity.find(".activity-status").text("Checklist interactivo");
        $activity
            .find(".learning-checklist label")
            .removeClass("is-answer-correct is-answer-missed is-answer-incorrect is-answer-correct-unselected");
        $activity.find(".checklist-item-feedback").prop("hidden", true).text("");
        $activity
            .find(".activity-feedback")
            .removeClass("feedback-success feedback-error")
            .text(
                checkedItems === 0
                    ? "Selecciona únicamente los pasos correctos y presiona “Revisar respuestas”."
                    : "Has seleccionado " + checkedItems + (checkedItems === 1 ? " opción. " : " opciones. ") + "Presiona “Revisar respuestas”."
            );
    });

    $("#review-learning-checklist").on("click", function () {
        const $activity = $(this).closest(".checklist-activity");
        const $labels = $activity.find(".learning-checklist label");
        let correctSelections = 0;
        let errors = 0;

        $labels.each(function () {
            const $label = $(this);
            const $input = $label.find("input");
            const $itemFeedback = $label.find(".checklist-item-feedback");
            const shouldBeSelected = String($input.data("correct")) === "true";
            const isSelected = $input.prop("checked");

            $label.removeClass("is-answer-correct is-answer-missed is-answer-incorrect is-answer-correct-unselected");
            $itemFeedback.prop("hidden", false);

            if (shouldBeSelected && isSelected) {
                correctSelections += 1;
                $label.addClass("is-answer-correct");
                $itemFeedback.text("Acierto");
            } else if (shouldBeSelected && !isSelected) {
                errors += 1;
                $label.addClass("is-answer-missed");
                $itemFeedback.text("Debías seleccionarlo");
            } else if (!shouldBeSelected && isSelected) {
                errors += 1;
                $label.addClass("is-answer-incorrect");
                $itemFeedback.text("No debía seleccionarse");
            } else {
                $label.addClass("is-answer-correct-unselected");
                $itemFeedback.text("Correcto sin marcar");
            }
        });

        if (errors === 0) {
            completeActivity($activity, "Checklist completado. Identificaste correctamente los cuatro pasos de seguridad.");
        } else {
            $activity.removeClass("is-completed");
            $activity.find(".activity-status").text("Revisión completada");
            $activity
                .find(".activity-feedback")
                .removeClass("feedback-success")
                .addClass("feedback-error")
                .text(
                    "Obtuviste " +
                    correctSelections +
                    " aciertos y " +
                    errors +
                    (errors === 1 ? " respuesta por corregir." : " respuestas por corregir.")
                );
        }
    });

    $("#complete-reflection").on("click", function () {
        const $activity = $(this).closest(".reflection-activity");
        const answer = $.trim($("#reflection-answer").val());

        if (answer === "") {
            $activity
                .find(".activity-feedback")
                .removeClass("feedback-success")
                .addClass("feedback-error")
                .text("Escribe una respuesta breve antes de continuar.");
            $("#reflection-answer").trigger("focus");
            return;
        }

        completeActivity($activity, "Respuesta registrada de forma simulada.");
    });

    $("#finish-practice-quiz").on("click", function () {
        const $quiz = $("#practice-quiz");
        const $questions = $quiz.find(".practice-question");
        let answeredQuestions = 0;
        let correctAnswers = 0;

        $questions.each(function () {
            const $question = $(this);
            const $selectedOption = $question.find("input:checked");

            $question.removeClass("has-error");
            if ($selectedOption.length) {
                answeredQuestions += 1;
            } else {
                $question.addClass("has-error");
            }
        });

        if (answeredQuestions !== $questions.length) {
            $("#practice-quiz-result")
                .prop("hidden", false)
                .removeClass("is-success")
                .addClass("is-error");
            $("#practice-quiz-score").text("Faltan preguntas por responder");
            $("#practice-quiz-message").text("Selecciona una opción en cada pregunta antes de finalizar.");
            $quiz.find(".practice-question.has-error").first().attr("tabindex", "-1").trigger("focus");
            return;
        }

        $questions.each(function () {
            const $question = $(this);
            const correctValue = String($question.data("correct"));
            const correctAnswer = $question.data("correct-answer");
            const $selectedOption = $question.find("input:checked");
            const selectedValue = String($selectedOption.val());

            $question.find(".practice-option").removeClass("is-correct is-incorrect");
            $question.find('input[value="' + correctValue + '"]').closest(".practice-option").addClass("is-correct");

            if (selectedValue === correctValue) {
                correctAnswers += 1;
            } else {
                $selectedOption.closest(".practice-option").addClass("is-incorrect");
            }

            $question
                .find(".practice-answer-feedback")
                .prop("hidden", false)
                .text("Respuesta correcta: " + correctAnswer);
        });

        $quiz.addClass("is-completed");
        $("#practice-quiz-result")
            .prop("hidden", false)
            .removeClass("is-error")
            .addClass("is-success");
        $("#practice-quiz-score").text("Resultado: " + correctAnswers + " de " + $questions.length);
        $("#practice-quiz-message").text(
            correctAnswers === $questions.length
                ? "Excelente. Identificaste correctamente todos los puntos de seguridad."
                : "Revisa las respuestas correctas resaltadas antes de iniciar la evaluación."
        );
        completeModule(currentModuleIndex);
    });

    $(".knowledge-card").on("click", function () {
        const $card = $(this);
        const willFlip = !$card.hasClass("is-flipped");
        const question = $card.data("question");

        $card
            .toggleClass("is-flipped", willFlip)
            .attr("aria-pressed", willFlip)
            .attr("aria-label", willFlip ? "Mostrar pregunta: " + question : "Mostrar respuesta: " + question);
        $card.find(".knowledge-card-front").attr("aria-hidden", willFlip);
        $card.find(".knowledge-card-back").attr("aria-hidden", !willFlip);
    });

    $(".resource-card").on("click", function () {
        const resourceName = $(this).data("resource-name");

        $(".resource-card").removeClass("is-opened");
        $(this).addClass("is-opened");
        $("#resource-feedback")
            .addClass("feedback-success")
            .text(resourceName + " abierto de forma simulada.");
    });

    $evaluationButton.on("click", function () {
        completeModule(currentModuleIndex);
        $("#evaluation-feedback")
            .addClass("feedback-success")
            .text("La evaluación se abrirá cuando esa pantalla esté disponible.");
    });

    showModule(0);
    updateCourseProgress();
});
