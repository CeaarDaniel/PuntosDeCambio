(function ($) {
    "use strict";

    $(function () {
        const $userTableElement = $("#users-management-table");
        const userFormElement = document.getElementById("user-form");
        const passwordResetFormElement = document.getElementById("password-reset-form");
        const userFormModalElement = document.getElementById("user-form-modal");
        const userDetailModalElement = document.getElementById("user-detail-modal");
        const userPasswordModalElement = document.getElementById("user-password-modal");
        const userDeleteModalElement = document.getElementById("user-delete-modal");
        const feedbackToastElement = document.getElementById("user-feedback-toast");
        const userFormModal = bootstrap.Modal.getOrCreateInstance(userFormModalElement);
        const userDetailModal = bootstrap.Modal.getOrCreateInstance(userDetailModalElement);
        const userPasswordModal = bootstrap.Modal.getOrCreateInstance(userPasswordModalElement);
        const userDeleteModal = bootstrap.Modal.getOrCreateInstance(userDeleteModalElement);
        const feedbackToast = bootstrap.Toast.getOrCreateInstance(feedbackToastElement, { delay: 3200 });
        let userTable = null;
        let formMode = "add";
        let pendingDeleteEmployee = "";

        if ($userTableElement.length && $.fn.DataTable) {
            userTable = $userTableElement.DataTable({
                autoWidth: false,
                lengthChange: false,
                pageLength: 8,
                order: [[2, "asc"]],
                columnDefs: [
                    { targets: 0, orderable: false, searchable: false },
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
                    searchPlaceholder: "Nombre, empleado, correo...",
                    emptyTable: "No hay usuarios registrados.",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ usuarios",
                    infoEmpty: "No hay usuarios para mostrar",
                    infoFiltered: "(filtrado de _MAX_ usuarios)",
                    zeroRecords: "No se encontraron usuarios con esos filtros.",
                    paginate: {
                        first: "Primera",
                        last: "Última",
                        next: "Siguiente",
                        previous: "Anterior"
                    }
                }
            });
        }

        function allUserRecords() {
            if (userTable) {
                return userTable.rows().nodes().to$();
            }

            return $userTableElement.find("tbody .user-record");
        }

        function userRecordByEmployee(employeeNumber) {
            return allUserRecords().filter(function () {
                return $(this).attr("data-user-employee") === employeeNumber;
            });
        }

        function readUserRecord($record) {
            return {
                employee: String($record.attr("data-user-employee") || ""),
                name: String($record.attr("data-user-name") || ""),
                email: String($record.attr("data-user-email") || ""),
                role: String($record.attr("data-user-role") || ""),
                roleLabel: String($record.attr("data-user-role-label") || ""),
                position: String($record.attr("data-user-position") || ""),
                positionLabel: String($record.attr("data-user-position-label") || ""),
                area: String($record.attr("data-user-area") || ""),
                areaLabel: String($record.attr("data-user-area-label") || ""),
                supervisor: String($record.attr("data-user-supervisor") || ""),
                shift: String($record.attr("data-user-shift") || ""),
                status: String($record.attr("data-user-status") || "activo"),
                lastAccess: String($record.attr("data-user-last-access") || "Sin registro")
            };
        }

        function updateVisibleUserCount() {
            const visibleTotal = userTable
                ? userTable.rows({ search: "applied" }).count()
                : allUserRecords().length;

            $("#visible-users-count").html(
                '<i class="fa-solid fa-users" aria-hidden="true"></i> ' +
                visibleTotal +
                (visibleTotal === 1 ? " usuario" : " usuarios")
            );
        }

        function updateUserSummary() {
            const $records = allUserRecords();
            const total = $records.length;
            const activeTotal = $records.filter(function () {
                return $(this).attr("data-user-status") === "activo";
            }).length;

            $("#total-users-count").text(total);
            $("#active-users-count").text(activeTotal);
            updateVisibleUserCount();
        }

        function showFeedback(message) {
            $("#user-feedback-message").text(message);
            feedbackToast.show();
        }

        function selectedFilterText(selector) {
            const $select = $(selector);

            return $select.val() ? $select.find("option:selected").text() : "";
        }

        function exactSearch(value) {
            return value ? "^" + $.fn.dataTable.util.escapeRegex(value) + "$" : "";
        }

        function applyUserFilters() {
            if (!userTable) {
                return;
            }

            userTable.search($.trim($("#user-search").val()));
            userTable.column(4).search(exactSearch(selectedFilterText("#user-role-filter")), true, false);
            userTable.column(5).search(exactSearch(selectedFilterText("#user-position-filter")), true, false);
            userTable.column(6).search(exactSearch(selectedFilterText("#user-area-filter")), true, false);
            userTable.column(7).search(exactSearch(selectedFilterText("#user-status-filter")), true, false);
            userTable.draw();
        }

        function setPasswordMatchValidity(passwordSelector, confirmationSelector) {
            const password = $(passwordSelector).val();
            const confirmationElement = document.querySelector(confirmationSelector);

            if (!confirmationElement) {
                return;
            }

            confirmationElement.setCustomValidity(password === confirmationElement.value ? "" : "Las contraseñas no coinciden");
        }

        function resetUserForm() {
            userFormElement.reset();
            $(userFormElement).removeClass("was-validated");
            $("#user-password-confirm-input").get(0).setCustomValidity("");
        }

        function openAddUserModal() {
            formMode = "add";
            resetUserForm();
            $("#user-form-modal-title").text("Agregar nuevo usuario");
            $("#save-user-button-label").text("Guardar usuario");
            $("#user-employee-input").prop("readonly", false);
            $("#temporary-password-fields").prop("hidden", false).find(":input").prop("disabled", false).prop("required", true);
            userFormModal.show();
        }

        function openEditUserModal($record) {
            const user = readUserRecord($record);

            formMode = "edit";
            resetUserForm();
            $("#user-form-modal-title").text("Editar usuario");
            $("#save-user-button-label").text("Guardar cambios");
            $("#user-employee-input").val(user.employee).prop("readonly", true);
            $("#user-name-input").val(user.name);
            $("#user-email-input").val(user.email);
            $("#user-role-input").val(user.role);
            $("#user-position-input").val(user.position);
            $("#user-area-input").val(user.area);
            $("#user-supervisor-input").val(user.supervisor);
            $("#user-shift-input").val(user.shift);
            $("#user-status-input").val(user.status);
            $("#temporary-password-fields").prop("hidden", true).find(":input").prop("disabled", true).prop("required", false);
            userFormModal.show();
        }

        function openUserDetail($record) {
            const user = readUserRecord($record);
            const $sourceAvatar = $record.find(".user-avatar").first();
            const isActive = user.status === "activo";

            $("#detail-user-avatar").attr("class", $sourceAvatar.attr("class")).text($sourceAvatar.text());
            $("#detail-user-name").text(user.name);
            $("#detail-user-employee").text(user.employee);
            $("#detail-user-email").text(user.email);
            $("#detail-user-role").text(user.roleLabel);
            $("#detail-user-position").text(user.positionLabel);
            $("#detail-user-area").text(user.areaLabel);
            $("#detail-user-supervisor").text(user.supervisor || "Sin asignar");
            $("#detail-user-shift").text(user.shift || "Sin asignar");
            $("#detail-user-last-access").text(user.lastAccess);
            $("#detail-user-status")
                .attr("class", "user-status " + (isActive ? "status-active" : "status-inactive"))
                .html('<i class="fa-solid ' + (isActive ? "fa-circle-check" : "fa-circle-minus") + '" aria-hidden="true"></i> ' + (isActive ? "Activo" : "Inactivo"));
            userDetailModal.show();
        }

        function updateUserStatus($record, newStatus) {
            const isActive = newStatus === "activo";
            const userName = String($record.attr("data-user-name"));

            $record.attr("data-user-status", newStatus);
            $record.find(".user-status")
                .attr("class", "user-status " + (isActive ? "status-active" : "status-inactive"))
                .html('<i class="fa-solid ' + (isActive ? "fa-circle-check" : "fa-circle-minus") + '" aria-hidden="true"></i> ' + (isActive ? "Activo" : "Inactivo"));
            $record.find(".user-toggle-action")
                .attr("title", isActive ? "Desactivar usuario" : "Activar usuario")
                .attr("aria-label", (isActive ? "Desactivar " : "Activar ") + userName)
                .html('<i class="fa-solid ' + (isActive ? "fa-user-slash" : "fa-user-check") + '" aria-hidden="true"></i><span>' + (isActive ? "Desactivar" : "Activar") + "</span>");
        }

        $("#search-users-button").on("click", applyUserFilters);

        $("#user-search").on("keydown", function (event) {
            if (event.key === "Enter") {
                event.preventDefault();
                applyUserFilters();
            }
        });

        $("#user-filter-form").on("reset", function () {
            window.setTimeout(function () {
                if (userTable) {
                    userTable.search("");
                    userTable.columns().search("");
                    userTable.draw();
                }
            }, 0);
        });

        if (userTable) {
            userTable.on("draw", updateVisibleUserCount);
        }

        $("#add-user-button").on("click", openAddUserModal);

        $(document).on("click", ".user-detail-action", function () {
            openUserDetail($(this).closest(".user-record"));
        });

        $(document).on("click", ".user-edit-action", function () {
            openEditUserModal($(this).closest(".user-record"));
        });

        $("#user-password-input, #user-password-confirm-input").on("input", function () {
            setPasswordMatchValidity("#user-password-input", "#user-password-confirm-input");
        });

        $(userFormElement).on("submit", function (event) {
            event.preventDefault();

            if (formMode === "add") {
                setPasswordMatchValidity("#user-password-input", "#user-password-confirm-input");
            }

            if (!userFormElement.checkValidity()) {
                event.stopPropagation();
                $(userFormElement).addClass("was-validated");
                userFormElement.querySelector(":invalid").focus();
                return;
            }

            userFormModal.hide();
            showFeedback(formMode === "edit" ? "Cambios del usuario guardados de forma simulada." : "Usuario registrado correctamente de forma simulada.");
        });

        $(document).on("click", ".user-password-action", function () {
            const user = readUserRecord($(this).closest(".user-record"));

            passwordResetFormElement.reset();
            $(passwordResetFormElement).removeClass("was-validated");
            $("#reset-password-confirm-input").get(0).setCustomValidity("");
            $("#password-user-name").text(user.name + " (" + user.employee + ")");
            userPasswordModal.show();
        });

        $("#reset-password-input, #reset-password-confirm-input").on("input", function () {
            setPasswordMatchValidity("#reset-password-input", "#reset-password-confirm-input");
        });

        $(passwordResetFormElement).on("submit", function (event) {
            event.preventDefault();
            setPasswordMatchValidity("#reset-password-input", "#reset-password-confirm-input");

            if (!passwordResetFormElement.checkValidity()) {
                event.stopPropagation();
                $(passwordResetFormElement).addClass("was-validated");
                passwordResetFormElement.querySelector(":invalid").focus();
                return;
            }

            userPasswordModal.hide();
            showFeedback("Contraseña temporal restablecida de forma simulada.");
        });

        $(document).on("click", ".user-toggle-action", function () {
            const $record = $(this).closest(".user-record");
            const currentStatus = String($record.attr("data-user-status"));
            const newStatus = currentStatus === "activo" ? "inactivo" : "activo";
            const userName = String($record.attr("data-user-name"));

            updateUserStatus($record, newStatus);
            if (userTable) {
                userTable.row($record.get(0)).invalidate("dom").draw(false);
            }
            updateUserSummary();
            showFeedback(userName + (newStatus === "activo" ? " fue activado" : " fue desactivado") + " de forma simulada.");
        });

        $(document).on("click", ".user-delete-action", function () {
            const user = readUserRecord($(this).closest(".user-record"));

            pendingDeleteEmployee = user.employee;
            $("#delete-user-name").text(user.name + " (" + user.employee + ")");
            userDeleteModal.show();
        });

        $("#confirm-user-delete").on("click", function () {
            const $records = userRecordByEmployee(pendingDeleteEmployee);

            if (userTable) {
                $records.each(function () {
                    userTable.row(this).remove();
                });
                userTable.draw(false);
            } else {
                $records.remove();
            }

            pendingDeleteEmployee = "";
            userDeleteModal.hide();
            updateUserSummary();
            showFeedback("Usuario eliminado temporalmente de esta maqueta.");
        });

        $(userFormModalElement).on("shown.bs.modal", function () {
            $("#user-employee-input").trigger("focus");
        });

        $(userPasswordModalElement).on("shown.bs.modal", function () {
            $("#reset-password-input").trigger("focus");
        });

        $(window).on("resize.userManagementTable", function () {
            if (userTable && window.innerWidth >= 992) {
                userTable.columns.adjust();
            }
        });

        updateUserSummary();
    });
})(jQuery);
