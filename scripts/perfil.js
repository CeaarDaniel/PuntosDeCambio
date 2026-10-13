(function () {
    /*
     * Mi perfil: cambio de contraseña (pages/perfil.php).
     * Datos: api/operacionesUsuarios.php, opción 4.
     *
     * Las reglas de aquí solo guían al usuario mientras escribe; la validación
     * que cuenta es la del servidor (misma política que la aplicación de defectos).
     */
    var $vista = $('#perfilView');

    if (!$vista.length) {
        return;
    }

    var RUTA_API = './api/operacionesUsuarios.php';

    var $formulario = $('#formCambioContrasena');
    var $actual = $('#contrasenaActual');
    var $nueva = $('#contrasenaNueva');
    var $confirmacion = $('#confirmarContrasena');
    var $mensaje = $('#perfilMensaje');
    var $botonGuardar = $formulario.find('button[type="submit"]');
    var contenidoBoton = $botonGuardar.html();
    var guardando = false;

    function reglasPassword(valor) {
        return {
            longitud: Array.from(valor).length >= 8,
            mayuscula: /[A-ZÁÉÍÓÚÜÑ]/.test(valor),
            minuscula: /[a-záéíóúüñ]/.test(valor),
            especial: /[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9\s]/.test(valor)
        };
    }

    /* Marca en verde los requisitos que ya cumple la nueva contraseña. */
    function actualizarRequisitos() {
        var reglas = reglasPassword(String($nueva.val() || ''));

        $('#requisitosContrasena [data-regla]').each(function () {
            var $regla = $(this);
            var cumplida = Boolean(reglas[$regla.attr('data-regla')]);

            $regla.toggleClass('cumplida', cumplida);
            $regla.find('i')
                .toggleClass('bi-circle', !cumplida)
                .toggleClass('bi-check-circle-fill', cumplida);
        });

        return reglas;
    }

    function limpiarValidacionPersonalizada() {
        $nueva[0].setCustomValidity('');
        $confirmacion[0].setCustomValidity('');
        $actual.add($nueva).add($confirmacion).removeClass('is-invalid');
    }

    function ocultarPasswords() {
        $vista.find('.perfil-ver-contrasena').each(function () {
            var $boton = $(this);

            $('#' + $boton.attr('data-password-target')).attr('type', 'password');
            $boton
                .attr('aria-label', 'Mostrar contraseña')
                .find('i')
                .removeClass('bi-eye-slash')
                .addClass('bi-eye');
        });
    }

    function ocultarMensaje() {
        $mensaje.addClass('d-none').empty();
    }

    /* El texto se asigna con .text() para no interpretar HTML del servidor. */
    function mostrarMensaje(mensaje, tipo) {
        var exito = tipo === 'exito';

        $mensaje
            .removeClass('d-none alert-success alert-danger')
            .addClass(exito ? 'alert-success' : 'alert-danger')
            .empty()
            .append($('<i>', {
                'class': 'bi ' + (exito ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'),
                'aria-hidden': 'true'
            }))
            .append($('<span>').text(mensaje));
    }

    function bloquearFormulario(bloquear) {
        guardando = bloquear;
        $formulario.find('input, button').prop('disabled', bloquear);
        $botonGuardar.html(bloquear
            ? '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Actualizando…'
            : contenidoBoton);
    }

    /* Mostrar u ocultar el texto de cada contraseña. */
    $vista.find('.perfil-ver-contrasena').on('click', function () {
        var $boton = $(this);
        var $campo = $('#' + $boton.attr('data-password-target'));
        var mostrar = $campo.attr('type') === 'password';

        $campo.attr('type', mostrar ? 'text' : 'password');
        $boton
            .attr('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña')
            .find('i')
            .toggleClass('bi-eye', !mostrar)
            .toggleClass('bi-eye-slash', mostrar);
    });

    $actual.add($nueva).add($confirmacion).on('input', function () {
        limpiarValidacionPersonalizada();
        actualizarRequisitos();
        ocultarMensaje();
    });

    $formulario.on('reset', function () {
        if (guardando) {
            return false;
        }

        /* El reset nativo limpia los valores después de este evento. */
        window.setTimeout(function () {
            limpiarValidacionPersonalizada();
            actualizarRequisitos();
            ocultarPasswords();
            $formulario.removeClass('was-validated');
            ocultarMensaje();
        }, 0);
    });

    $formulario.on('submit', function (evento) {
        evento.preventDefault();

        if (guardando) {
            return;
        }

        limpiarValidacionPersonalizada();

        var actual = String($actual.val() || '');
        var nueva = String($nueva.val() || '');
        var confirmacion = String($confirmacion.val() || '');
        var reglas = actualizarRequisitos();
        var cumpleReglas = reglas.longitud && reglas.mayuscula &&
            reglas.minuscula && reglas.especial;

        if (!cumpleReglas || (actual !== '' && nueva !== '' && actual === nueva)) {
            $nueva[0].setCustomValidity('La nueva contraseña no cumple los requisitos.');
            $nueva.addClass('is-invalid');
        }

        if (nueva !== confirmacion) {
            $confirmacion[0].setCustomValidity('Las contraseñas no coinciden.');
            $confirmacion.addClass('is-invalid');
        }

        $formulario.addClass('was-validated');

        if (!this.checkValidity()) {
            var primerInvalido = this.querySelector(':invalid');

            if (primerInvalido) {
                primerInvalido.focus();
            }

            return;
        }

        /* Se arma antes de bloquear: los controles deshabilitados no se envían. */
        var datos = {
            opcion: '4',
            csrf: String($('#perfilCsrf').val() || ''),
            contrasenaActual: actual,
            contrasenaNueva: nueva,
            confirmarContrasena: confirmacion
        };

        bloquearFormulario(true);
        ocultarMensaje();

        $.ajax({
            url: RUTA_API,
            type: 'POST',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: datos
        }).done(function (respuesta) {
            if (!respuesta || respuesta.status !== 'ok') {
                mostrarMensaje(
                    respuesta && respuesta.mensaje
                        ? respuesta.mensaje
                        : 'No fue posible actualizar la contraseña.',
                    'error'
                );
                return;
            }

            /* El servidor renueva el token después de cada cambio exitoso. */
            if (respuesta.csrf) {
                $('#perfilCsrf').val(respuesta.csrf);
            }

            $actual.add($nueva).add($confirmacion).val('');
            limpiarValidacionPersonalizada();
            actualizarRequisitos();
            ocultarPasswords();
            $formulario.removeClass('was-validated');
            mostrarMensaje(respuesta.mensaje || 'Tu contraseña fue actualizada correctamente.', 'exito');
        }).fail(function (peticion) {
            var respuesta = peticion.responseJSON || {};
            var mensaje = respuesta.detalle || respuesta.mensaje ||
                'No fue posible actualizar la contraseña. Verifica tu conexión e inténtalo nuevamente.';

            /* Un 500 trae el error técnico en "detalle": al usuario solo se le muestra el mensaje. */
            if (peticion.status === 500) {
                mensaje = respuesta.mensaje || 'No fue posible actualizar la contraseña.';
            }

            mostrarMensaje(mensaje, 'error');

            var campo = respuesta.campo ? document.getElementById(String(respuesta.campo)) : null;

            if (campo && $formulario[0].contains(campo)) {
                /* Con "was-validated" Bootstrap pinta en verde los campos válidos
                   aunque tengan "is-invalid"; se quita para resaltar el campo señalado.
                   El foco se aplica después de desbloquear el formulario (always). */
                $formulario.removeClass('was-validated');
                window.setTimeout(function () {
                    $(campo).addClass('is-invalid').trigger('focus');
                }, 0);
            }
        }).always(function () {
            bloquearFormulario(false);
        });
    });

    actualizarRequisitos();
})();
