(function ($) {
    "use strict";

    $(function () {
        const $catalogTableElement = $("#catalog-items-table");
        const catalogFormElement = document.getElementById("catalog-item-form");
        const formModalElement = document.getElementById("catalog-form-modal");
        const confirmModalElement = document.getElementById("catalog-confirm-modal");
        const feedbackToastElement = document.getElementById("catalog-feedback-toast");
        const formModal = bootstrap.Modal.getOrCreateInstance(formModalElement);
        const confirmModal = bootstrap.Modal.getOrCreateInstance(confirmModalElement);
        const feedbackToast = bootstrap.Toast.getOrCreateInstance(feedbackToastElement, { delay: 3200 });
        let catalogTable = null;
        let currentCatalog = "puestos";
        let formMode = "add";
        let pendingAction = null;

        if ($catalogTableElement.length && $.fn.DataTable) {
            catalogTable = $catalogTableElement.DataTable({
                autoWidth: false,
                lengthChange: false,
                pageLength: 10,
                order: [[1, "asc"]],
                columnDefs: [
                    { targets: 0, visible: false },
                    { targets: 6, orderable: false, searchable: false }
                ],
                layout: {
                    topStart: null,
                    topEnd: "search",
                    bottomStart: "info",
                    bottomEnd: "paging"
                },
                language: {
                    search: "Buscar:",
                    searchPlaceholder: "Buscar en el catálogo...",
                    emptyTable: "No hay elementos registrados.",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ elementos",
                    infoEmpty: "No hay elementos para mostrar",
                    infoFiltered: "(filtrado de _MAX_ elementos)",
                    zeroRecords: "No se encontraron elementos con esos filtros.",
                    paginate: {
                        first: "Primera",
                        last: "Última",
                        next: "Siguiente",
                        previous: "Anterior"
                    }
                }
            });
        }

        function allCatalogRows() {
            if (catalogTable) {
                return catalogTable.rows().nodes().to$();
            }

            return $catalogTableElement.find("tbody .catalog-record");
        }

        function readCatalogRow($row) {
            return {
                catalog: String($row.attr("data-catalog") || ""),
                name: String($row.attr("data-name") || ""),
                description: String($row.attr("data-description") || ""),
                code: String($row.attr("data-code") || ""),
                status: String($row.attr("data-status") || "activo"),
                order: Number($row.attr("data-order")) || 1
            };
        }

        function showFeedback(message) {
            $("#catalog-feedback-message").text(message);
            feedbackToast.show();
        }

        function exactSearch(value) {
            return value ? "^" + $.fn.dataTable.util.escapeRegex(value) + "$" : "";
        }

        function selectedCatalogButton(catalogId) {
            return $('.catalog-type-button[data-catalog="' + catalogId + '"]').first();
        }

        function updateSelectedCatalogCount() {
            const total = catalogTable
                ? catalogTable.rows({ search: "applied" }).count()
                : allCatalogRows().filter('[data-catalog="' + currentCatalog + '"]').length;

            $("#selected-catalog-count").html(
                '<i class="fa-solid fa-list" aria-hidden="true"></i> ' +
                total +
                (total === 1 ? " elemento" : " elementos")
            );
        }

        function updateSummary() {
            const $rows = allCatalogRows();
            const activeTotal = $rows.filter(function () {
                return $(this).attr("data-status") === "activo";
            }).length;
            const inactiveTotal = $rows.length - activeTotal;

            $("#active-catalog-items").text(activeTotal);
            $("#inactive-catalog-items").text(inactiveTotal);
            updateSelectedCatalogCount();
        }

        function updateCatalogTypeCounts() {
            const $rows = allCatalogRows();

            $(".catalog-type-button").each(function () {
                const $button = $(this);
                const catalogId = String($button.attr("data-catalog"));
                const total = $rows.filter(function () {
                    return $(this).attr("data-catalog") === catalogId;
                }).length;

                $button.find("small").text(total + (total === 1 ? " elemento" : " elementos"));
            });
        }

        function selectCatalog(catalogId) {
            const $button = selectedCatalogButton(catalogId);
            const $firstCatalogRow = allCatalogRows().filter(function () {
                return $(this).attr("data-catalog") === catalogId;
            }).first();
            const tableCatalogLabel = $.trim($firstCatalogRow.children("td").first().text()) || String($button.attr("data-title"));

            if (!$button.length) {
                return;
            }

            currentCatalog = catalogId;
            $(".catalog-type-button").removeClass("is-active").attr("aria-pressed", "false");
            $button.addClass("is-active").attr("aria-pressed", "true");
            $("#catalog-type-filter").val(catalogId);
            $("#selected-catalog-title").text($button.attr("data-title"));
            $("#selected-catalog-description").text($button.attr("data-description"));

            if (catalogTable) {
                catalogTable.column(0).search(exactSearch(tableCatalogLabel), true, false).draw();
            } else {
                allCatalogRows().prop("hidden", true).filter('[data-catalog="' + catalogId + '"]').prop("hidden", false);
                updateSelectedCatalogCount();
            }
        }

        function applyCatalogFilters() {
            if (!catalogTable) {
                return;
            }

            const status = $("#catalog-status-filter").val();
            const statusLabel = status ? $("#catalog-status-filter option:selected").text() : "";

            catalogTable.search($.trim($("#catalog-item-search").val()));
            catalogTable.column(4).search(exactSearch(statusLabel), true, false);
            catalogTable.draw();
        }

        function resetCatalogForm() {
            catalogFormElement.reset();
            $(catalogFormElement).removeClass("was-validated");
            $("#catalog-item-type-input").prop("disabled", false).val(currentCatalog);
        }

        function openAddCatalogItem() {
            formMode = "add";
            resetCatalogForm();
            $("#catalog-form-modal-title").text("Agregar elemento de catálogo");
            $("#catalog-form-save-label").text("Guardar elemento");
            formModal.show();
        }

        function openEditCatalogItem($row) {
            const item = readCatalogRow($row);

            formMode = "edit";
            resetCatalogForm();
            $("#catalog-form-modal-title").text("Editar elemento de catálogo");
            $("#catalog-form-save-label").text("Guardar cambios");
            $("#catalog-item-type-input").val(item.catalog).prop("disabled", true);
            $("#catalog-item-name-input").val(item.name);
            $("#catalog-item-description-input").val(item.description);
            $("#catalog-item-code-input").val(item.code);
            $("#catalog-item-status-input").val(item.status);
            $("#catalog-item-order-input").val(item.order);
            formModal.show();
        }

        function openConfirmation($row, actionType) {
            const item = readCatalogRow($row);
            const isDelete = actionType === "delete";
            const isActivating = actionType === "toggle" && item.status === "inactivo";

            pendingAction = { type: actionType, $row: $row };
            $("#catalog-confirm-item-name").text(item.name);
            $("#catalog-confirm-title").text(
                isDelete ? "¿Eliminar este elemento?" : isActivating ? "¿Activar este elemento?" : "¿Desactivar este elemento?"
            );
            $("#catalog-confirm-icon")
                .attr("class", "catalog-confirm-icon " + (isDelete ? "danger-icon" : "warning-icon"))
                .html('<i class="fa-solid ' + (isDelete ? "fa-trash-can" : "fa-triangle-exclamation") + '" aria-hidden="true"></i>');
            $("#confirm-catalog-action")
                .attr("class", "button catalog-confirm-button " + (isDelete ? "danger-confirm" : "warning-confirm"))
                .html('<i class="fa-solid ' + (isDelete ? "fa-trash-can" : "fa-check") + '" aria-hidden="true"></i> Confirmar');
            confirmModal.show();
        }

        function updateRowStatus($row, newStatus) {
            const isActive = newStatus === "activo";

            $row.attr("data-status", newStatus);
            $row.find(".catalog-item-status")
                .attr("class", "catalog-item-status " + (isActive ? "status-active" : "status-inactive"))
                .html('<i class="fa-solid ' + (isActive ? "fa-circle-check" : "fa-circle-minus") + '" aria-hidden="true"></i> ' + (isActive ? "Activo" : "Inactivo"));
            $row.find(".catalog-toggle-action")
                .html('<i class="fa-solid ' + (isActive ? "fa-toggle-on" : "fa-toggle-off") + '" aria-hidden="true"></i><span>' + (isActive ? "Desactivar" : "Activar") + "</span>");
        }

        $(document).on("click", ".catalog-type-button", function () {
            selectCatalog(String($(this).attr("data-catalog")));
        });

        $("#catalog-type-filter").on("change", function () {
            selectCatalog(String($(this).val()));
        });

        $("#search-catalog-items-button").on("click", applyCatalogFilters);

        $("#catalog-item-search").on("keydown", function (event) {
            if (event.key === "Enter") {
                event.preventDefault();
                applyCatalogFilters();
            }
        });

        $("#catalog-filter-form").on("reset", function () {
            window.setTimeout(function () {
                $("#catalog-type-filter").val(currentCatalog);
                if (catalogTable) {
                    catalogTable.search("");
                    catalogTable.column(4).search("");
                    catalogTable.draw();
                }
            }, 0);
        });

        if (catalogTable) {
            catalogTable.on("draw", updateSelectedCatalogCount);
        }

        $("#add-catalog-item-button, #add-catalog-item-secondary").on("click", openAddCatalogItem);

        $(document).on("click", ".catalog-edit-action", function () {
            openEditCatalogItem($(this).closest(".catalog-record"));
        });

        $(catalogFormElement).on("submit", function (event) {
            event.preventDefault();

            if (!catalogFormElement.checkValidity()) {
                event.stopPropagation();
                $(catalogFormElement).addClass("was-validated");
                catalogFormElement.querySelector(":invalid").focus();
                return;
            }

            formModal.hide();
            showFeedback(formMode === "edit" ? "Elemento actualizado de forma simulada." : "Elemento agregado de forma simulada.");
        });

        $(document).on("click", ".catalog-toggle-action", function () {
            openConfirmation($(this).closest(".catalog-record"), "toggle");
        });

        $(document).on("click", ".catalog-delete-action", function () {
            openConfirmation($(this).closest(".catalog-record"), "delete");
        });

        $("#confirm-catalog-action").on("click", function () {
            if (!pendingAction || !pendingAction.$row.length) {
                confirmModal.hide();
                return;
            }

            if (pendingAction.type === "delete") {
                if (catalogTable) {
                    catalogTable.row(pendingAction.$row.get(0)).remove().draw(false);
                } else {
                    pendingAction.$row.remove();
                }
                showFeedback("Elemento eliminado temporalmente de la maqueta.");
            } else {
                const item = readCatalogRow(pendingAction.$row);
                const newStatus = item.status === "activo" ? "inactivo" : "activo";

                updateRowStatus(pendingAction.$row, newStatus);
                if (catalogTable) {
                    catalogTable.row(pendingAction.$row.get(0)).invalidate("dom").draw(false);
                }
                showFeedback("Estatus actualizado de forma simulada.");
            }

            pendingAction = null;
            confirmModal.hide();
            updateCatalogTypeCounts();
            updateSummary();
        });

        $(formModalElement).on("shown.bs.modal", function () {
            $("#catalog-item-name-input").trigger("focus");
        });

        $(window).on("resize.catalogTable", function () {
            if (catalogTable && window.innerWidth >= 992) {
                catalogTable.columns.adjust();
            }
        });

        updateCatalogTypeCounts();
        selectCatalog(currentCatalog);
        updateSummary();
    });
})(jQuery);
