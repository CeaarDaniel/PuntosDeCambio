$(function () {
    const $evaluationForm = $("#performance-evaluation-form");
    const $categoryPanels = $(".evaluation-category-panel");
    const $categoryButtons = $(".category-navigation-button");
    const $previousButton = $("#previous-performance-category");
    const $nextButton = $("#next-performance-category");
    const $commentsButton = $("#go-to-performance-comments");
    const $finishButton = $("#finish-performance-evaluation");
    const $categoryStatus = $("#category-answer-status");
    const $actionFeedback = $("#performance-action-feedback");
    const $courseSelection = $("#completed-course-selection");
    const $evaluationFormView = $("#operational-evaluation-form-view");
    const $operationalEvaluation = $(".operational-evaluation");
    const totalCategories = $categoryPanels.length;
    const totalCriteria = $(".performance-criterion").length;
    let currentCategoryIndex = 0;
    let evaluationFinished = false;
    let isOpeningEvaluation = false;

    function openPerformanceEvaluation() {
        if (isOpeningEvaluation || !$courseSelection.is(":visible")) {
            return;
        }

        isOpeningEvaluation = true;
        $courseSelection.removeClass("is-active").addClass("is-leaving");

        window.setTimeout(function () {
            $courseSelection.prop("hidden", true).removeClass("is-leaving");
            $evaluationFormView.prop("hidden", false);
            $operationalEvaluation.scrollTop(0);

            window.requestAnimationFrame(function () {
                $evaluationFormView.addClass("is-active");
                isOpeningEvaluation = false;
            });
        }, 280);
    }

    function showCompletedCourses() {
        if (isOpeningEvaluation || !$evaluationFormView.is(":visible")) {
            return;
        }

        isOpeningEvaluation = true;
        $evaluationFormView.removeClass("is-active").addClass("is-leaving");

        window.setTimeout(function () {
            $evaluationFormView.prop("hidden", true).removeClass("is-leaving");
            $courseSelection.prop("hidden", false);
            $operationalEvaluation.scrollTop(0);

            window.requestAnimationFrame(function () {
                $courseSelection.addClass("is-active");
                isOpeningEvaluation = false;
            });
        }, 280);
    }

    function countAnsweredCriteria($scope) {
        let answeredCriteria = 0;

        $scope.find(".performance-criterion").each(function () {
            if ($(this).find("input:checked").length) {
                answeredCriteria += 1;
            }
        });

        return answeredCriteria;
    }

    function calculateScopePercentage($scope) {
        let selectedValueTotal = 0;
        let answeredCriteria = 0;

        $scope.find(".performance-criterion input:checked").each(function () {
            selectedValueTotal += Number($(this).val());
            answeredCriteria += 1;
        });

        if (answeredCriteria === 0) {
            return 0;
        }

        return Math.round((selectedValueTotal / (answeredCriteria * 5)) * 100);
    }

    function updateResultCard($panel) {
        const resultId = $panel.data("result-id");
        const percentage = calculateScopePercentage($panel);
        const $resultValue = $("#" + resultId);

        $resultValue.text(percentage + "%");
        $resultValue.closest(".performance-result-card").find(".result-progress span").css("width", percentage + "%");
    }

    function updateOverallResult() {
        const overallPercentage = calculateScopePercentage($evaluationForm);
        let performanceLevel = "Sin evaluar";

        if (overallPercentage >= 80) {
            performanceLevel = "Impacto alto";
        } else if (overallPercentage >= 60) {
            performanceLevel = "Impacto favorable";
        } else if (overallPercentage > 0) {
            performanceLevel = "Necesita seguimiento";
        }

        $("#overall-performance-result").text(overallPercentage + "%");
        $("#overall-performance-level").text(performanceLevel);
        $("#overall-performance-result")
            .closest(".performance-result-card")
            .find(".result-progress span")
            .css("width", overallPercentage + "%");
    }

    function updateCategoryStates() {
        $categoryPanels.each(function (index) {
            const $panel = $(this);
            const $button = $categoryButtons.eq(index);
            const $state = $button.find(".category-navigation-state");
            const criteriaCount = $panel.find(".performance-criterion").length;
            const answeredCount = countAnsweredCriteria($panel);
            const isCompleted = answeredCount === criteriaCount;
            const isActive = index === currentCategoryIndex;

            $button.toggleClass("is-completed", isCompleted);

            if (isCompleted) {
                $state.html('<i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>Completada</span>');
            } else if (isActive) {
                $state.html('<i class="fa-solid fa-circle-half-stroke" aria-hidden="true"></i><span>En progreso</span>');
            } else {
                $state.html('<i class="fa-regular fa-clock" aria-hidden="true"></i><span>Pendiente</span>');
            }
        });
    }

    function updateCategoryAnswerStatus(showError) {
        const $currentPanel = $categoryPanels.eq(currentCategoryIndex);
        const criteriaCount = $currentPanel.find(".performance-criterion").length;
        const answeredCount = countAnsweredCriteria($currentPanel);
        const pendingCount = criteriaCount - answeredCount;

        $categoryStatus.removeClass("is-complete has-error");

        if (pendingCount === 0) {
            $categoryStatus
                .addClass("is-complete")
                .html('<i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>Categoría completada</span>');
        } else {
            $categoryStatus
                .toggleClass("has-error", Boolean(showError))
                .html(
                    '<i class="fa-regular fa-circle" aria-hidden="true"></i><span>Faltan ' +
                    pendingCount +
                    (pendingCount === 1 ? " criterio" : " criterios") +
                    "</span>"
                );
        }
    }

    function updateEvaluationProgress() {
        const answeredCriteria = countAnsweredCriteria($evaluationForm);
        const progress = Math.round((answeredCriteria / totalCriteria) * 100);
        const allCriteriaAnswered = answeredCriteria === totalCriteria;

        $("#evaluation-progress-text").text(answeredCriteria + " de " + totalCriteria + " criterios respondidos");
        $("#evaluation-progress-percent").text(progress + "%");
        $("#evaluation-progress-bar")
            .attr("aria-valuenow", progress)
            .find(".progress-bar")
            .css("width", progress + "%");

        $finishButton.prop("disabled", !allCriteriaAnswered || evaluationFinished);
    }

    function updateEvaluationView(showError) {
        $categoryPanels.each(function () {
            updateResultCard($(this));
        });

        updateOverallResult();
        updateCategoryStates();
        updateCategoryAnswerStatus(showError);
        updateEvaluationProgress();
    }

    function showCategory(index) {
        if (index < 0 || index >= totalCategories) {
            return;
        }

        currentCategoryIndex = index;
        $categoryButtons
            .removeClass("is-active")
            .attr("aria-selected", "false")
            .eq(index)
            .addClass("is-active")
            .attr("aria-selected", "true");
        $categoryPanels
            .removeClass("is-active")
            .prop("hidden", true)
            .eq(index)
            .addClass("is-active")
            .prop("hidden", false);

        $previousButton.prop("disabled", index === 0);
        $nextButton.prop("hidden", index === totalCategories - 1);
        $commentsButton.prop("hidden", index !== totalCategories - 1);
        updateEvaluationView(false);
    }

    function validateCategory(index) {
        const $panel = $categoryPanels.eq(index);
        let $firstMissingCriterion = $();

        $panel.find(".performance-criterion").each(function () {
            const $criterion = $(this);
            const isAnswered = $criterion.find("input:checked").length > 0;

            $criterion.toggleClass("has-error", !isAnswered);
            if (!isAnswered && !$firstMissingCriterion.length) {
                $firstMissingCriterion = $criterion;
            }
        });

        updateEvaluationView($firstMissingCriterion.length > 0);

        if ($firstMissingCriterion.length) {
            $firstMissingCriterion.attr("tabindex", "-1").trigger("focus");
            return false;
        }

        return true;
    }

    $(".criterion-rating input").on("change", function () {
        const $criterion = $(this).closest(".performance-criterion");

        $criterion.find(".criterion-rating label").removeClass("is-selected");
        $(this).closest("label").addClass("is-selected");
        $criterion.removeClass("has-error");
        updateEvaluationView(false);
    });

    $(".completed-course-option").on("click", openPerformanceEvaluation).on("keydown", function (event) {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            openPerformanceEvaluation();
        }
    });

    $("#back-to-completed-courses").on("click", showCompletedCourses);

    $categoryButtons.on("click", function () {
        showCategory(Number($(this).data("category-index")));
    });

    $previousButton.on("click", function () {
        showCategory(currentCategoryIndex - 1);
    });

    $nextButton.on("click", function () {
        if (validateCategory(currentCategoryIndex)) {
            showCategory(currentCategoryIndex + 1);
        }
    });

    $commentsButton.on("click", function () {
        if (validateCategory(currentCategoryIndex)) {
            document.getElementById("performance-comments").scrollIntoView({ behavior: "smooth", block: "start" });
        }
    });

    $("#save-performance-draft").on("click", function () {
        $actionFeedback
            .removeClass("has-error")
            .addClass("is-success")
            .html('<i class="fa-solid fa-circle-check" aria-hidden="true"></i> Evaluación guardada de forma simulada. Se perderá al recargar la página.');
    });

    $("#print-performance-report").on("click", function () {
        $actionFeedback
            .removeClass("is-success has-error")
            .html('<i class="fa-solid fa-circle-info" aria-hidden="true"></i> La impresión del reporte estará disponible en una etapa posterior.');
    });

    $finishButton.on("click", function () {
        let firstIncompleteCategory = -1;

        $categoryPanels.each(function (index) {
            if (firstIncompleteCategory === -1 && countAnsweredCriteria($(this)) !== $(this).find(".performance-criterion").length) {
                firstIncompleteCategory = index;
            }
        });

        if (firstIncompleteCategory !== -1) {
            showCategory(firstIncompleteCategory);
            validateCategory(firstIncompleteCategory);
            $actionFeedback
                .removeClass("is-success")
                .addClass("has-error")
                .html('<i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> Responde todos los criterios antes de finalizar.');
            return;
        }

        bootstrap.Modal.getOrCreateInstance(document.getElementById("finish-performance-modal")).show();
    });

    $("#confirm-performance-finish").on("click", function () {
        const modalElement = document.getElementById("finish-performance-modal");
        const modalInstance = bootstrap.Modal.getInstance(modalElement);

        evaluationFinished = true;
        if (modalInstance) {
            modalInstance.hide();
        }

        $evaluationForm.addClass("performance-form-is-finished");
        $evaluationForm.find("input, textarea, select").prop("disabled", true);
        $("#save-performance-draft").prop("disabled", true);
        $finishButton
            .prop("disabled", true)
            .html('<i class="fa-solid fa-circle-check" aria-hidden="true"></i> Evaluación finalizada');
        $actionFeedback.empty().removeClass("is-success has-error");
        $("#performance-finished-alert").prop("hidden", false).get(0).scrollIntoView({ behavior: "smooth", block: "center" });
    });

    showCategory(0);
});
