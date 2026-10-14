(function ($) {
    "use strict";

    $(function () {
        const $assignmentTableElement = $("#course-assignment-table");
        const detailModalElement = document.getElementById("assignment-detail-modal");
        const editorModalElement = document.getElementById("assignment-editor-modal");
        const mandatoryModalElement = document.getElementById("mandatory-courses-modal");
        const bulkModalElement = document.getElementById("bulk-assignment-modal");
        const feedbackToastElement = document.getElementById("assignment-feedback-toast");
        const detailModal = bootstrap.Modal.getOrCreateInstance(detailModalElement);
        const editorModal = bootstrap.Modal.getOrCreateInstance(editorModalElement);
        const mandatoryModal = bootstrap.Modal.getOrCreateInstance(mandatoryModalElement);
        const bulkModal = bootstrap.Modal.getOrCreateInstance(bulkModalElement);
        const feedbackToast = bootstrap.Toast.getOrCreateInstance(feedbackToastElement, { delay: 3200 });
        let assignmentTable = null;
        let $currentRecord = $();

        if ($assignmentTableElement.length && $.fn.DataTable) {
            assignmentTable = $assignmentTableElement.DataTable({
                autoWidth: false,
                lengthChange: false,
                pageLength: 8,
                order: [[2, "asc"]],
                columnDefs: [
                    { targets: 0, orderable: false, searchable: false },
                    { targets: 8, orderable: false },
                    { targets: 9, orderable: false, searchable: false }
                ],
                layout: {
                    topStart: null,
                    topEnd: "search",
                    bottomStart: "info",
                    bottomEnd: "paging"
                },
                language: {
                    search: "Buscar:",
                    searchPlaceholder: "Nombre, empleado, puesto...",
                    emptyTable: "No hay trabajadores disponibles.",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ trabajadores",
                    infoEmpty: "No hay trabajadores para mostrar",
                    infoFiltered: "(filtrado de _MAX_ trabajadores)",
                    zeroRecords: "No se encontraron trabajadores con esos filtros.",
                    paginate: {
                        first: "Primera",
                        last: "Última",
                        next: "Siguiente",
                        previous: "Anterior"
                    }
                }
            });
        }

        function escapeHtml(value) {
            const characters = {
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                "\"": "&quot;",
                "'": "&#039;"
            };

            return String(value || "").replace(/[&<>"']/g, function (character) {
                return characters[character];
            });
        }

        function readAssignmentRecord($record) {
            return {
                employee: String($record.attr("data-employee") || ""),
                name: String($record.attr("data-name") || ""),
                position: String($record.attr("data-position") || ""),
                positionLabel: String($record.attr("data-position-label") || ""),
                area: String($record.attr("data-area") || ""),
                areaLabel: String($record.attr("data-area-label") || ""),
                supervisor: String($record.attr("data-supervisor") || ""),
                supervisorLabel: String($record.attr("data-supervisor-label") || ""),
                status: String($record.attr("data-status") || "sin-cursos"),
                statusLabel: String($record.attr("data-status-label") || "Sin cursos"),
                assigned: Number($record.attr("data-assigned")) || 0,
                completed: Number($record.attr("data-completed")) || 0,
                pending: Number($record.attr("data-pending")) || 0,
                progress: Number($record.attr("data-progress")) || 0
            };
        }

        function showFeedback(message) {
            $("#assignment-feedback-message").text(message);
            feedbackToast.show();
        }

        function selectedFilterText(selector) {
            const $select = $(selector);

            return $select.val() ? $select.find("option:selected").text() : "";
        }

        function exactSearch(value) {
            return value ? "^" + $.fn.dataTable.util.escapeRegex(value) + "$" : "";
        }

        function updateVisibleAssignmentCount() {
            const total = assignmentTable
                ? assignmentTable.rows({ search: "applied" }).count()
                : $assignmentTableElement.find("tbody .assignment-record").length;

            $("#visible-assignment-count").html(
                '<i class="fa-solid fa-users" aria-hidden="true"></i> ' +
                total +
                (total === 1 ? " trabajador" : " trabajadores")
            );
        }

        function applyAssignmentFilters() {
            if (!assignmentTable) {
                return;
            }

            assignmentTable.search($.trim($("#assignment-search").val()));
            assignmentTable.column(3).search(exactSearch(selectedFilterText("#assignment-position-filter")), true, false);
            assignmentTable.column(4).search(exactSearch(selectedFilterText("#assignment-area-filter")), true, false);
            assignmentTable.column(5).search(exactSearch(selectedFilterText("#assignment-supervisor-filter")), true, false);
            assignmentTable.column(7).search(exactSearch(selectedFilterText("#assignment-status-filter")), true, false);
            assignmentTable.draw();
        }

        function statusPresentation(status) {
            if (status === "completado") {
                return { className: "status-completed", icon: "fa-circle-check" };
            }

            if (status === "en-proceso") {
                return { className: "status-progress", icon: "fa-spinner" };
            }

            if (status === "pendiente") {
                return { className: "status-pending", icon: "fa-clock" };
            }

            return { className: "status-empty", icon: "fa-ban" };
        }

        function setPersonInformation(prefix, $record) {
            const data = readAssignmentRecord($record);
            const $sourceAvatar = $record.find(".assignment-avatar").first();

            $("#" + prefix + "-assignment-avatar").attr("class", $sourceAvatar.attr("class")).text($sourceAvatar.text());
            $("#" + prefix + "-assignment-name").text(data.name);
            $("#" + prefix + "-assignment-employee").text(data.employee);
            $("#" + prefix + "-assignment-position").text(data.positionLabel);
            $("#" + prefix + "-assignment-area").text(data.areaLabel);

            return data;
        }

        function openDetailModal($record) {
            const data = setPersonInformation("detail", $record);
            const presentation = statusPresentation(data.status);
            const hasCourses = data.assigned > 0;

            $currentRecord = $record;
            $("#detail-assignment-status")
                .attr("class", "training-status " + presentation.className)
                .html('<i class="fa-solid ' + presentation.icon + '" aria-hidden="true"></i> ' + escapeHtml(data.statusLabel));
            $("#detail-assigned-count").text(data.assigned);
            $("#detail-completed-count").text(data.completed);
            $("#detail-pending-count").text(data.pending);
            $("#detail-progress-value").text(data.progress + "%");
            $("#detail-progress-bar").css("width", data.progress + "%").closest(".progress").attr("aria-valuenow", data.progress);
            $("#detail-course-list").prop("hidden", !hasCourses);
            $("#detail-empty-courses").prop("hidden", hasCourses);
            detailModal.show();
        }

        function updateEditorCourseCount() {
            const data = readAssignmentRecord($currentRecord);
            const removedTotal = $("#assigned-course-list .is-marked-for-removal").length;
            const addedTotal = $('.available-course-list input[type="checkbox"]:checked').length;
            const visibleTotal = Math.max(0, data.assigned - removedTotal + addedTotal);

            $("#editor-assigned-count").text(visibleTotal + (visibleTotal === 1 ? " curso" : " cursos"));
        }

        function openEditorModal($record) {
            const data = setPersonInformation("editor", $record);
            const hasCourses = data.assigned > 0;

            $currentRecord = $record;
            $("#assigned-course-list article").removeClass("is-marked-for-removal");
            $("#assigned-course-list .remove-assigned-course").html('<i class="fa-solid fa-minus" aria-hidden="true"></i><span>Quitar</span>');
            $('.available-course-list input[type="checkbox"]').prop("checked", false);
            $("#assigned-course-list").prop("hidden", !hasCourses);
            $("#editor-empty-assigned").prop("hidden", hasCourses);
            updateEditorCourseCount();
            editorModal.show();
        }

        function updateMandatorySelectedCount() {
            const total = $("#mandatory-selected-list li").length;

            $("#mandatory-selected-count").text(total + (total === 1 ? " curso" : " cursos"));
            $("#mandatory-selected-list").prop("hidden", total === 0);
            $("#mandatory-empty-state").prop("hidden", total > 0);
        }

        $("#search-assignments-button").on("click", applyAssignmentFilters);

        $("#assignment-search").on("keydown", function (event) {
            if (event.key === "Enter") {
                event.preventDefault();
                applyAssignmentFilters();
            }
        });

        $("#assignment-filter-form").on("reset", function () {
            window.setTimeout(function () {
                if (assignmentTable) {
                    assignmentTable.search("");
                    assignmentTable.columns().search("");
                    assignmentTable.draw();
                }
            }, 0);
        });

        if (assignmentTable) {
            assignmentTable.on("draw", updateVisibleAssignmentCount);
        }

        $(document).on("click", ".assignment-detail-action", function () {
            openDetailModal($(this).closest(".assignment-record"));
        });

        $(document).on("click", ".assignment-edit-action", function () {
            openEditorModal($(this).closest(".assignment-record"));
        });

        $("#edit-from-detail-button").on("click", function () {
            $(detailModalElement).one("hidden.bs.modal", function () {
                openEditorModal($currentRecord);
            });
            detailModal.hide();
        });

        $(document).on("click", ".remove-assigned-course", function () {
            const $button = $(this);
            const $course = $button.closest("article");
            const willRemove = !$course.hasClass("is-marked-for-removal");

            $course.toggleClass("is-marked-for-removal", willRemove);
            $button.html(
                willRemove
                    ? '<i class="fa-solid fa-rotate-left" aria-hidden="true"></i><span>Restaurar</span>'
                    : '<i class="fa-solid fa-minus" aria-hidden="true"></i><span>Quitar</span>'
            );
            updateEditorCourseCount();
        });

        $('.available-course-list input[type="checkbox"]').on("change", updateEditorCourseCount);

        $("#assignment-editor-form").on("submit", function (event) {
            event.preventDefault();
            editorModal.hide();
            showFeedback("Cambios en los cursos guardados de forma simulada.");
        });

        $("#mandatory-courses-button").on("click", function () {
            $(".mandatory-available-list input").prop("checked", false);
            updateMandatorySelectedCount();
            mandatoryModal.show();
        });

        $("#add-mandatory-course-button").on("click", function () {
            const $selectedCourses = $(".mandatory-available-list input:checked");

            if (!$selectedCourses.length) {
                showFeedback("Selecciona al menos un curso disponible.");
                return;
            }

            $selectedCourses.each(function () {
                const $input = $(this);
                const courseName = String($input.val());
                const icon = String($input.attr("data-icon") || "fa-book");
                const alreadyAdded = $("#mandatory-selected-list li").filter(function () {
                    return $(this).attr("data-course") === courseName;
                }).length > 0;

                if (!alreadyAdded) {
                    $("#mandatory-selected-list").append(
                        '<li data-course="' + escapeHtml(courseName) + '"><span><i class="fa-solid ' + escapeHtml(icon) + '" aria-hidden="true"></i></span><strong>' + escapeHtml(courseName) + '</strong><button class="remove-mandatory-course" type="button" aria-label="Quitar ' + escapeHtml(courseName) + '"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></li>'
                    );
                }

                $input.prop("checked", false);
            });

            updateMandatorySelectedCount();
            showFeedback("Cursos agregados a la configuración visual.");
        });

        $(document).on("click", ".remove-mandatory-course", function () {
            $(this).closest("li").remove();
            updateMandatorySelectedCount();
        });

        $("#mandatory-courses-form").on("submit", function (event) {
            event.preventDefault();
            mandatoryModal.hide();
            showFeedback("Configuración obligatoria guardada de forma simulada.");
        });

        $("#bulk-assignment-button").on("click", function () {
            bulkModal.show();
        });

        $("#bulk-position-select").on("change", function () {
            const workers = Number($(this).find("option:selected").attr("data-workers")) || 0;

            $("#bulk-worker-count").text(workers + (workers === 1 ? " trabajador" : " trabajadores"));
        });

        $("#bulk-assignment-form").on("submit", function (event) {
            event.preventDefault();
            const position = $("#bulk-position-select option:selected").text();

            bulkModal.hide();
            showFeedback("Asignación grupal para " + position + " simulada correctamente.");
        });

        $(window).on("resize.courseAssignmentTable", function () {
            if (assignmentTable && window.innerWidth >= 992) {
                assignmentTable.columns.adjust();
            }
        });

        updateVisibleAssignmentCount();
        updateMandatorySelectedCount();
    });
})(jQuery);
