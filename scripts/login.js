/*
 * Inicio de sesión.
 *
 * Valida contra api/operacionesUsuarios.php (opción 1). Esta pantalla no
 * carga jQuery, por eso todo está en JavaScript nativo.
 */
(function () {
    'use strict';

    const RUTA_API = './api/operacionesUsuarios.php';

    const formulario     = document.getElementById('loginForm');
    const campoUsuario   = document.getElementById('username');
    const campoPassword  = document.getElementById('password');
    const botonVerClave  = document.getElementById('togglePassword');
    const avisoError     = document.getElementById('errorMessage');
    const textoError     = document.getElementById('errorText');

    if (!formulario) {
        return;
    }

    const botonEntrar      = formulario.querySelector('button[type="submit"]');
    const contenidoOriginal = botonEntrar ? botonEntrar.innerHTML : '';

    let enviando = false;

    /* -------------------- Mensajes -------------------- */

    function mostrarError(mensaje) {
        textoError.textContent = mensaje;
        avisoError.classList.remove('alert-success');
        avisoError.classList.add('alert-danger');
        avisoError.style.display = 'flex';
    }

    function mostrarExito(mensaje) {
        textoError.textContent = mensaje;
        avisoError.classList.remove('alert-danger');
        avisoError.classList.add('alert-success');
        avisoError.style.display = 'flex';
    }

    function ocultarAviso() {
        avisoError.style.display = 'none';
    }

    /* -------------------- Estado del botón -------------------- */

    function bloquearFormulario(bloquear) {
        enviando = bloquear;

        if (!botonEntrar) {
            return;
        }

        botonEntrar.disabled = bloquear;
        botonEntrar.innerHTML = bloquear
            ? '<span>Validando...</span>'
            : contenidoOriginal;
    }

    /* -------------------- Mostrar u ocultar la contraseña -------------------- */

    if (botonVerClave && campoPassword) {
        botonVerClave.addEventListener('click', function () {
            const visible = campoPassword.getAttribute('type') === 'text';

            campoPassword.setAttribute('type', visible ? 'password' : 'text');

            const icono = this.querySelector('i');
            if (icono) {
                icono.className = visible ? 'bi bi-eye' : 'bi bi-eye-slash';
            }
        });
    }

    /* -------------------- Envío del formulario -------------------- */

    formulario.addEventListener('submit', function (evento) {
        evento.preventDefault();

        if (enviando) {
            return;
        }

        const usuario  = campoUsuario.value.trim();
        const password = campoPassword.value;

        if (!usuario || !password) {
            mostrarError('Escribe tu usuario y tu contraseña.');
            return;
        }

        ocultarAviso();
        bloquearFormulario(true);

        const datos = new FormData();
        datos.append('opcion', '1');
        datos.append('usuario', usuario);
        datos.append('password', password);

        fetch(RUTA_API, {
            method: 'POST',
            body: datos,
            /* El servidor rechaza las peticiones que no traen esta cabecera. */
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (respuesta) {
                return respuesta.json().catch(function () {
                    throw new Error('Respuesta no válida del servidor.');
                });
            })
            .then(function (datosRespuesta) {
                if (datosRespuesta.status !== 'ok') {
                    mostrarError(datosRespuesta.mensaje || 'No fue posible iniciar sesión.');
                    bloquearFormulario(false);
                    campoPassword.value = '';
                    campoPassword.focus();
                    return;
                }

                /* La contraseña deja de estar en el formulario antes de salir. */
                campoPassword.value = '';
                mostrarExito('Bienvenido, ' + datosRespuesta.usuario.nombre + '. Entrando...');

                window.location.href = datosRespuesta.redirigir || 'index.php';
            })
            .catch(function (error) {
                mostrarError('No se pudo conectar con el servidor. Intenta de nuevo.');
                bloquearFormulario(false);
                console.error(error);
            });
    });

    /* Al corregir los datos se limpia el mensaje anterior. */
    [campoUsuario, campoPassword].forEach(function (campo) {
        if (campo) {
            campo.addEventListener('input', ocultarAviso);
        }
    });

    if (campoUsuario) {
        campoUsuario.focus();
    }
})();
