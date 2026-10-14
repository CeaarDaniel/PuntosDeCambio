$(function () {
    const $loginForm = $("#login-form");
    const $employeeNumber = $("#employee-number");
    const $password = $("#password");
    const $togglePassword = $("#toggle-password");
    const $loginMessage = $("#login-message");

    $togglePassword.on("click", function () {
        const showPassword = $password.attr("type") === "password";

        $password.attr("type", showPassword ? "text" : "password");
        $(this)
            .attr("aria-label", showPassword ? "Ocultar contraseña" : "Mostrar contraseña")
            .attr("aria-pressed", showPassword)
            .find("i")
            .toggleClass("fa-eye", !showPassword)
            .toggleClass("fa-eye-slash", showPassword);
    });

    $(".demo-user").on("click", function () {
        const employeeNumber = $(this).data("employee");
        const employeeName = $(this).data("name");

        $employeeNumber.val(employeeNumber).removeClass("is-invalid");
        $password.val("1234").removeClass("is-invalid");
        $(".invalid-feedback").removeClass("d-block");
        $loginMessage
            .removeClass("is-error is-success")
            .empty();

        $password.trigger("focus");
        $employeeNumber.attr("aria-label", "Empleado seleccionado: " + employeeName);
    });

    $loginForm.on("submit", function (event) {
        event.preventDefault();

        const employeeValue = $.trim($employeeNumber.val());
        const passwordValue = $password.val();
        const employeeIsValid = employeeValue !== "";
        const passwordIsValid = passwordValue !== "";

        $employeeNumber.toggleClass("is-invalid", !employeeIsValid);
        $password.toggleClass("is-invalid", !passwordIsValid);
        $employeeNumber.closest(".mb-4").find(".invalid-feedback").toggleClass("d-block", !employeeIsValid);
        $password.closest(".mb-2").find(".invalid-feedback").toggleClass("d-block", !passwordIsValid);
        $loginMessage.removeClass("is-error is-success").empty();

        if (!employeeIsValid || !passwordIsValid) {
            $loginMessage
                .addClass("is-error")
                .text("Revisa los campos marcados para poder continuar.");
            return;
        }

        if (passwordValue !== "1234") {
            $password.addClass("is-invalid");
            $loginMessage
                .addClass("is-error")
                .text("La contraseña de demostración es 1234.");
            return;
        }

        $loginMessage
            .addClass("is-success")
            .text("Acceso correcto. Abriendo el menú principal...");

        window.setTimeout(function () {
            window.location.href = "dashboard.php";
        }, 650);
    });

    $(".form-control").on("input", function () {
        $(this).removeClass("is-invalid");
        $(this).closest(".mb-4, .mb-2").find(".invalid-feedback").removeClass("d-block");
    });
});
