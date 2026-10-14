(function ($) {
    "use strict";

    $(function () {
        const feedbackToastElement = document.getElementById("report-feedback-toast");
        const feedbackToast = bootstrap.Toast.getOrCreateInstance(feedbackToastElement, { delay: 3200 });
        const reportTables = {};

        function prepareResponsiveLabels(tableSelector) {
            const $table = $(tableSelector);
            const headings = $table.find("thead th").map(function () {
                return $.trim($(this).text());
            }).get();

            $table.find("tbody tr").each(function () {
                $(this).children("td").each(function (index) {
                    $(this).attr("data-label", headings[index] || "Dato");
                });
            });
        }

        function initializeReportTable(key, selector) {
            prepareResponsiveLabels(selector);

            if (!$.fn.DataTable || !$(selector).length) {
                return;
            }

            reportTables[key] = $(selector).DataTable({
                autoWidth: false,
                lengthChange: false,
                pageLength: 6,
                order: [[0, "asc"]],
                layout: {
                    topStart: null,
                    topEnd: "search",
                    bottomStart: "info",
                    bottomEnd: "paging"
                },
                language: {
                    search: "Buscar:",
                    searchPlaceholder: "Buscar en este reporte...",
                    emptyTable: "No hay información disponible.",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    infoEmpty: "No hay registros para mostrar",
                    infoFiltered: "(filtrado de _MAX_ registros)",
                    zeroRecords: "No se encontraron registros con esos filtros.",
                    paginate: {
                        first: "Primera",
                        last: "Última",
                        next: "Siguiente",
                        previous: "Anterior"
                    }
                }
            });
        }

        function showFeedback(message) {
            $("#report-feedback-message").text(message);
            feedbackToast.show();
        }

        function exactSearch(value) {
            return value ? "^" + $.fn.dataTable.util.escapeRegex(value) + "$" : "";
        }

        function activeReportKey() {
            return String($(".report-tab.is-active").attr("data-report-tab") || "worker");
        }

        function clearTableFilters() {
            $.each(reportTables, function (_, table) {
                table.search("");
                table.columns().search("");
                table.draw();
            });
        }

        function applySimpleFilters() {
            const area = String($("#report-area-filter").val() || "");
            const position = String($("#report-position-filter").val() || "");
            const course = String($("#report-course-filter").val() || "");
            const status = String($("#report-status-filter").val() || "");
            const key = activeReportKey();

            clearTableFilters();

            if (key === "worker" && reportTables.worker) {
                const workerStatus = status === "Finalizado" ? "Completado" : status;
                reportTables.worker.column(2).search(exactSearch(position), true, false);
                reportTables.worker.column(3).search(exactSearch(area), true, false);
                reportTables.worker.column(9).search(workerStatus ? workerStatus : "", false, true).draw();
            } else if (key === "course" && reportTables.course) {
                reportTables.course.column(0).search(course ? course : "", false, true);
                reportTables.course.draw();
            } else if (key === "area" && reportTables.area) {
                reportTables.area.column(0).search(exactSearch(area), true, false).draw();
            }

            $(".report-kpi, .report-chart-card").addClass("is-report-updated");
            window.setTimeout(function () {
                $(".report-kpi, .report-chart-card").removeClass("is-report-updated");
            }, 650);
            showFeedback("Filtros aplicados de forma simulada al reporte visible.");
        }

        initializeReportTable("worker", "#worker-report-table");
        initializeReportTable("course", "#course-report-table");
        initializeReportTable("area", "#area-report-table");

        $(document).on("click", ".report-tab", function () {
            const $tab = $(this);
            const key = String($tab.attr("data-report-tab"));

            $(".report-tab").removeClass("is-active").attr("aria-selected", "false");
            $tab.addClass("is-active").attr("aria-selected", "true");
            $(".report-table-panel").removeClass("is-active").prop("hidden", true);
            $('.report-table-panel[data-report-panel="' + key + '"]').addClass("is-active").prop("hidden", false);

            if (reportTables[key]) {
                window.setTimeout(function () {
                    reportTables[key].columns.adjust();
                }, 0);
            }
        });

        $("#apply-report-filters").on("click", applySimpleFilters);

        $("#report-filter-form").on("reset", function () {
            window.setTimeout(function () {
                clearTableFilters();
                showFeedback("Filtros limpiados; se muestra nuevamente la información general.");
            }, 0);
        });

        $(document).on("click", "[data-report-action]", function () {
            const action = String($(this).attr("data-report-action"));

            if (action === "Actualizar datos") {
                const $button = $(this);
                $button.addClass("is-updating").prop("disabled", true);
                window.setTimeout(function () {
                    $button.removeClass("is-updating").prop("disabled", false);
                    showFeedback("Datos actualizados de forma simulada.");
                }, 750);
                return;
            }

            showFeedback(action + " es una acción visual; no se generó ningún archivo.");
        });

        $(window).on("resize.reportTables", function () {
            if (window.innerWidth >= 992) {
                const key = activeReportKey();
                if (reportTables[key]) {
                    reportTables[key].columns.adjust();
                }
            }
        });
    });
})(jQuery);
