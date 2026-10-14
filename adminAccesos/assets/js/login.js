/**
 * assets/js/login.js — Pantalla de inicio de sesión (login.php).
 *
 * Envía nómina y contraseña a api/autenticacion.php (opcion 1). Los errores
 * se muestran dentro de la tarjeta, junto al formulario, porque el usuario
 * necesita corregir el dato ahí mismo.
 */
(function () {
    'use strict';

    var $formulario = $('#formLogin');
    var $boton = $formulario.find('button[type="submit"]');
    var $mensaje = $('#loginMensaje');
    var $nomina = $('#loginNomina');
    var $contrasena = $('#loginContrasena');
    var $verContrasena = $('#loginVerContrasena');
    var contenidoBoton = $boton.html();
    var enviando = false;

    function mostrarMensaje(mensaje, tipo) {
        $mensaje
            .removeClass('d-none alert-danger alert-success')
            .addClass(tipo === 'exito' ? 'alert-success' : 'alert-danger')
            .text(mensaje);
    }

    function bloquearFormulario(bloquear) {
        enviando = bloquear;
        $boton.prop('disabled', bloquear);
        $boton.html(bloquear
            ? '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Validando…'
            : contenidoBoton);
    }

    $formulario.on('submit', function (evento) {
        evento.preventDefault();

        if (enviando) {
            return;
        }

        $nomina.val(String($nomina.val() || '').trim());

        if (!this.checkValidity()) {
            $formulario.addClass('was-validated');
            this.querySelector(':invalid').focus();
            return;
        }

        $formulario.removeClass('was-validated');
        $mensaje.addClass('d-none');
        bloquearFormulario(true);

        App.api($formulario.attr('action'), {
            opcion: '1',
            nomina: $nomina.val(),
            contrasena: $contrasena.val()
        }).done(function (data) {
            $contrasena.val('');
            mostrarMensaje('Acceso correcto. Entrando…', 'exito');
            window.location.href = (data && data.redirigir) || 'index.php';
        }).fail(function (mensaje) {
            mostrarMensaje(mensaje, 'error');
            bloquearFormulario(false);
            $contrasena.val('').trigger('focus');
        });
    });

    /* Mostrar u ocultar la contraseña ayuda a quien escribe con dificultad
       a comprobar lo que capturó antes de enviarlo. */
    $verContrasena.on('click', function () {
        var visible = $contrasena.attr('type') === 'text';

        $contrasena.attr('type', visible ? 'password' : 'text');
        $verContrasena
            .attr('aria-pressed', String(!visible))
            .attr('title', visible ? 'Mostrar contraseña' : 'Ocultar contraseña')
            .attr('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña')
            .find('i').toggleClass('bi-eye', visible).toggleClass('bi-eye-slash', !visible);
    });

    $nomina.add($contrasena).on('input', function () {
        $mensaje.addClass('d-none');
    });
})();
