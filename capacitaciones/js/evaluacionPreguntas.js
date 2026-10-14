$(function () {
    const $sectionButtons = $(".exam-section-button");
    const $sectionPanels = $(".exam-section-panel");
    const $previousButton = $("#previous-exam-section");
    const $nextButton = $("#next-exam-section");
    const $finishButton = $("#finish-exam");
    const $answerStatus = $("#section-answer-status");
    const $examWorkspace = $(".exam-workspace");
    const $examNavigation = $(".exam-section-navigation");
    const $examNavigationToggle = $("#exam-navigation-toggle");
    const totalSections = $sectionButtons.length;
    let currentSectionIndex = 0;
    let examSubmitted = false;

    function updateSectionStates() {
        $sectionButtons.each(function (index) {
            const $button = $(this);
            const $state = $button.find(".exam-section-state");
            const isCompleted = $button.hasClass("is-completed");
            const isActive = index === currentSectionIndex;

            $state.removeClass("state-pending state-progress state-completed");

            if (isCompleted) {
                $state
                    .addClass("state-completed")
                    .html('<i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>Completada</span>');
            } else if (isActive) {
                $state
                    .addClass("state-progress")
                    .html('<i class="fa-solid fa-circle-half-stroke" aria-hidden="true"></i><span>En progreso</span>');
            } else {
                $state
                    .addClass("state-pending")
                    .html('<i class="fa-regular fa-clock" aria-hidden="true"></i><span>Pendiente</span>');
            }
        });
    }

    function updateExamProgress() {
        const position = currentSectionIndex + 1;
        const progress = Math.round((position / totalSections) * 100);
        const completedSections = $sectionButtons.filter(".is-completed").length;
        const allSectionsCompleted = completedSections === totalSections;

        $("#exam-current-section").text("Sección " + position + " de " + totalSections);
        $("#exam-progress-value").text(progress + "%");
        $("#exam-progress-label").text(progress + "% del recorrido");
        $("#completed-sections-count").text(completedSections);
        $("#exam-progress")
            .attr("aria-valuenow", progress)
            .find(".progress-bar")
            .css("width", progress + "%");

        $finishButton.prop("disabled", !allSectionsCompleted || examSubmitted);
    }

    function updateAnswerStatus(showError) {
        const $activePanel = $sectionPanels.eq(currentSectionIndex);
        const $questions = $activePanel.find(".exam-question");
        let answeredQuestions = 0;

        $questions.each(function () {
            if ($(this).find("input:checked").length) {
                answeredQuestions += 1;
            }
        });

        const pendingQuestions = $questions.length - answeredQuestions;
        $answerStatus.removeClass("is-complete has-error");

        if (pendingQuestions === 0) {
            $sectionButtons.eq(currentSectionIndex).addClass("is-completed");
            $answerStatus
                .addClass("is-complete")
                .html('<i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>Sección completada</span>');
        } else {
            $sectionButtons.eq(currentSectionIndex).removeClass("is-completed");
            $answerStatus
                .toggleClass("has-error", Boolean(showError))
                .html(
                    '<i class="fa-regular fa-circle" aria-hidden="true"></i><span>Faltan ' +
                    pendingQuestions +
                    (pendingQuestions === 1 ? " pregunta" : " preguntas") +
                    " por responder</span>"
                );
        }

        updateSectionStates();
        updateExamProgress();
    }

    function showSection(index) {
        if (index < 0 || index >= totalSections || examSubmitted) {
            return;
        }

        currentSectionIndex = index;
        $sectionButtons
            .removeClass("is-active")
            .attr("aria-selected", "false")
            .eq(index)
            .addClass("is-active")
            .attr("aria-selected", "true");
        $sectionPanels
            .removeClass("is-active")
            .prop("hidden", true)
            .eq(index)
            .addClass("is-active")
            .prop("hidden", false);

        $previousButton.prop("disabled", index === 0);
        $nextButton.prop("hidden", index === totalSections - 1);
        $finishButton.prop("hidden", index !== totalSections - 1);
        $sectionPanels.eq(index).find(".exam-question").removeClass("has-error");

        updateAnswerStatus(false);
    }

    function validateCurrentSection() {
        const $activePanel = $sectionPanels.eq(currentSectionIndex);
        let firstMissingQuestion = null;

        $activePanel.find(".exam-question").each(function () {
            const $question = $(this);
            const isAnswered = $question.find("input:checked").length > 0;

            $question.toggleClass("has-error", !isAnswered);
            if (!isAnswered && firstMissingQuestion === null) {
                firstMissingQuestion = $question;
            }
        });

        updateAnswerStatus(firstMissingQuestion !== null);

        if (firstMissingQuestion !== null) {
            firstMissingQuestion.attr("tabindex", "-1").trigger("focus");
            return false;
        }

        return true;
    }

    $sectionButtons.on("click", function () {
        showSection(Number($(this).data("section-index")));
    });

    $examNavigationToggle.on("click", function () {
        const willCollapse = !$examNavigation.hasClass("is-collapsed");

        $examNavigation.toggleClass("is-collapsed", willCollapse);
        $examWorkspace.toggleClass("sections-collapsed", willCollapse);
        $(this)
            .attr("aria-expanded", !willCollapse)
            .attr("aria-label", willCollapse ? "Expandir secciones" : "Contraer secciones")
            .find("i")
            .attr("class", willCollapse ? "fa-solid fa-chevron-right" : "fa-solid fa-chevron-left");
    });

    $(".answer-option input").on("change", function () {
        const $question = $(this).closest(".exam-question");

        $question.find(".answer-option").removeClass("is-selected");
        $(this).closest(".answer-option").addClass("is-selected");
        $question.removeClass("has-error");
        updateAnswerStatus(false);
    });

    $previousButton.on("click", function () {
        showSection(currentSectionIndex - 1);
    });

    $nextButton.on("click", function () {
        if (validateCurrentSection()) {
            showSection(currentSectionIndex + 1);
        }
    });

    $finishButton.on("click", function () {
        if (!validateCurrentSection() || $sectionButtons.filter(".is-completed").length !== totalSections) {
            return;
        }

        bootstrap.Modal.getOrCreateInstance(document.getElementById("submit-exam-modal")).show();
    });

    $("#confirm-exam-submit").on("click", function () {
        const modalElement = document.getElementById("submit-exam-modal");
        const modalInstance = bootstrap.Modal.getInstance(modalElement);

        examSubmitted = true;
        if (modalInstance) {
            modalInstance.hide();
        }

        $(".answer-option input, .exam-section-button").prop("disabled", true);
        $previousButton.prop("disabled", true);
        $nextButton.prop("disabled", true);
        $finishButton
            .prop("disabled", true)
            .html('<i class="fa-solid fa-circle-check" aria-hidden="true"></i> Prueba enviada');
        $("#exam-submission-alert").prop("hidden", false).get(0).scrollIntoView({ behavior: "smooth", block: "center" });
    });

    showSection(0);
});
