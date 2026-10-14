$(function () {
    const $courseTableElement = $("#created-courses-table");
    const $courseCards = $(".created-course-card");
    const courseFormElement = document.getElementById("course-form");
    const courseFormModalElement = document.getElementById("course-form-modal");
    const courseDeleteModalElement = document.getElementById("course-delete-modal");
    const feedbackToastElement = document.getElementById("course-feedback-toast");
    const courseFormModal = bootstrap.Modal.getOrCreateInstance(courseFormModalElement);
    const courseDeleteModal = bootstrap.Modal.getOrCreateInstance(courseDeleteModalElement);
    const feedbackToast = bootstrap.Toast.getOrCreateInstance(feedbackToastElement, { delay: 3200 });
    let courseTable = null;
    let formMode = "add";

    if ($courseTableElement.length && $.fn.DataTable) {
        courseTable = $courseTableElement.DataTable({
            autoWidth: false,
            lengthChange: false,
            pageLength: 7,
            order: [[0, "asc"]],
            columnDefs: [
                {
                    targets: 6,
                    orderable: false,
                    searchable: false
                }
            ],
            layout: {
                topStart: null,
                topEnd: null,
                bottomStart: "info",
                bottomEnd: "paging"
            },
            language: {
                emptyTable: "No hay cursos creados.",
                info: "Mostrando _START_ a _END_ de _TOTAL_ cursos",
                infoEmpty: "No hay cursos para mostrar",
                infoFiltered: "(filtrado de _MAX_ cursos)",
                zeroRecords: "No se encontraron cursos con esos filtros.",
                paginate: {
                    first: "Primera",
                    last: "Última",
                    next: "Siguiente",
                    previous: "Anterior"
                }
            }
        });
    }

    function normalizeText(value) {
        return String(value || "")
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .toLowerCase();
    }

    function updateVisibleCourseCount(visibleCount) {
        $("#course-count").html(
            '<i class="fa-solid fa-book" aria-hidden="true"></i> ' +
            visibleCount +
            (visibleCount === 1 ? " curso" : " cursos")
        );
    }

    function applyCourseFilters() {
        const rawTitle = $.trim($("#course-search").val());
        const normalizedTitle = normalizeText(rawTitle);
        const classification = $("#course-classification-filter").val();
        const position = $("#course-position-filter").val();
        const status = $("#course-status-filter").val();
        let visibleCards = 0;

        if (courseTable) {
            const classificationText = classification ? $("#course-classification-filter option:selected").text() : "";
            const positionText = position ? $("#course-position-filter option:selected").text() : "";
            const statusText = status ? $("#course-status-filter option:selected").text() : "";

            courseTable.column(0).search(rawTitle);
            courseTable.column(2).search(classificationText ? "^" + $.fn.dataTable.util.escapeRegex(classificationText) + "$" : "", true, false);
            courseTable.column(3).search(positionText ? "^" + $.fn.dataTable.util.escapeRegex(positionText) + "$" : "", true, false);
            courseTable.column(5).search(statusText ? "^" + $.fn.dataTable.util.escapeRegex(statusText) + "$" : "", true, false);
            courseTable.draw();
        }

        $courseCards.each(function () {
            const $card = $(this);
            const matchesTitle = !normalizedTitle || normalizeText($card.data("title")).includes(normalizedTitle);
            const matchesClassification = !classification || $card.data("classification") === classification;
            const matchesPosition = !position || $card.data("position") === position;
            const matchesStatus = !status || $card.data("status") === status;
            const isVisible = matchesTitle && matchesClassification && matchesPosition && matchesStatus;

            $card.prop("hidden", !isVisible);
            if (isVisible) {
                visibleCards += 1;
            }
        });

        $("#course-card-empty-state").prop("hidden", visibleCards !== 0);
        updateVisibleCourseCount(visibleCards);
    }

    function updateIconPreview() {
        const iconClass = $("#course-icon-input").val() || "fa-book-open";
        $("#course-icon-preview i").attr("class", "fa-solid " + iconClass);
    }

    function resetCourseForm() {
        courseFormElement.reset();
        $(courseFormElement).removeClass("was-validated");
        $("#course-status-input").val("borrador");
        $("#course-icon-input").val("fa-book-open");
        updateIconPreview();
    }

    function openAddCourseModal() {
        formMode = "add";
        resetCourseForm();
        $("#course-form-modal-title").text("Agregar nuevo curso");
        $("#save-course-button span").text("Guardar curso");
        courseFormModal.show();
    }

    function openEditCourseModal($trigger) {
        formMode = "edit";
        resetCourseForm();
        $("#course-form-modal-title").text("Editar curso");
        $("#save-course-button span").text("Guardar cambios");
        $("#course-title-input").val($trigger.attr("data-course-title"));
        $("#course-description-input").val($trigger.attr("data-course-description"));
        $("#course-classification-input").val($trigger.attr("data-course-classification"));
        $("#course-position-input").val($trigger.attr("data-course-position"));
        $("#course-duration-input").val($trigger.attr("data-course-duration"));
        $("#course-status-input").val($trigger.attr("data-course-status"));
        $("#course-icon-input").val($trigger.attr("data-course-icon") || "fa-book-open");
        $("#course-objective-input").val($trigger.attr("data-course-objective"));
        updateIconPreview();
        courseFormModal.show();
    }

    function showCourseFeedback(message) {
        $("#course-feedback-message").text(message);
        feedbackToast.show();
    }

    $("#search-courses-button").on("click", applyCourseFilters);

    $("#course-search").on("keydown", function (event) {
        if (event.key === "Enter") {
            event.preventDefault();
            applyCourseFilters();
        }
    });

    $("#course-filter-form").on("reset", function () {
        window.setTimeout(applyCourseFilters, 0);
    });

    $("#add-course-button, #add-course-alternative-button").on("click", openAddCourseModal);

    $(document).on("click", ".course-edit-action", function () {
        openEditCourseModal($(this));
    });

    $("#course-icon-input").on("change", updateIconPreview);

    $(courseFormElement).on("submit", function (event) {
        event.preventDefault();

        if (!courseFormElement.checkValidity()) {
            event.stopPropagation();
            $(courseFormElement).addClass("was-validated");
            courseFormElement.querySelector(":invalid").focus();
            return;
        }

        courseFormModal.hide();
        showCourseFeedback(formMode === "edit" ? "Cambios guardados de forma simulada." : "Curso registrado correctamente de forma simulada.");
    });

    $(document).on("click", ".course-delete-action", function () {
        $("#course-delete-name").text($(this).attr("data-course-title"));
        courseDeleteModal.show();
    });

    $("#confirm-course-delete").on("click", function () {
        courseDeleteModal.hide();
        showCourseFeedback("Eliminación simulada. El curso permanece en el listado.");
    });

    $(courseFormModalElement).on("shown.bs.modal", function () {
        $("#course-title-input").trigger("focus");
    });

    $(window).on("resize.createdCoursesTable", function () {
        if (courseTable && window.innerWidth >= 992) {
            courseTable.columns.adjust();
        }
    });
});
