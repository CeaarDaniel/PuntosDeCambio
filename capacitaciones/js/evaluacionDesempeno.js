$(function () {
    const $employeeTable = $("#employees-performance-table");

    if (!$employeeTable.length || !$.fn.DataTable) {
        return;
    }

    const employeeTable = $employeeTable.DataTable({
        autoWidth: false,
        lengthChange: false,
        pageLength: 10,
        order: [[0, "asc"]],
        columnDefs: [
            {
                targets: 5,
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
            emptyTable: "No hay colaboradores disponibles.",
            info: "Mostrando _START_ a _END_ de _TOTAL_ colaboradores",
            infoEmpty: "No hay colaboradores para mostrar",
            infoFiltered: "(filtrado de _MAX_ colaboradores)",
            zeroRecords: "No se encontraron colaboradores con esos filtros.",
            paginate: {
                first: "Primera",
                last: "Última",
                next: "Siguiente",
                previous: "Anterior"
            }
        }
    });

    function applyPerformanceFilters() {
        const employeeSearch = $.trim($("#employee-search").val());
        const supervisor = $("#supervisor-filter option:selected").val()
            ? $("#supervisor-filter option:selected").text()
            : "";
        const area = $("#area-filter option:selected").val()
            ? $("#area-filter option:selected").text()
            : "";

        employeeTable.search(employeeSearch);
        employeeTable.column(3).search(area ? "^" + $.fn.dataTable.util.escapeRegex(area) + "$" : "", true, false);
        employeeTable.column(4).search(supervisor ? "^" + $.fn.dataTable.util.escapeRegex(supervisor) + "$" : "", true, false);
        employeeTable.draw();
    }

    $(".performance-search-button").on("click", applyPerformanceFilters);

    $("#employee-search").on("keydown", function (event) {
        if (event.key === "Enter") {
            event.preventDefault();
            applyPerformanceFilters();
        }
    });

    $(".performance-filter-form").on("reset", function () {
        window.setTimeout(function () {
            employeeTable.search("");
            employeeTable.columns().search("");
            employeeTable.draw();
        }, 0);
    });

    $(window).on("resize.performanceEmployeeTable", function () {
        if (window.innerWidth >= 992) {
            employeeTable.columns.adjust();
        }
    });
});