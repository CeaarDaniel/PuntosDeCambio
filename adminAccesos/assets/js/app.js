/**
 * assets/js/app.js — Utilidades compartidas por todas las páginas.
 *
 * Se carga DESPUÉS de jQuery y Bootstrap y ANTES del script específico de
 * cada página, porque esos scripts usan App.
 *
 * Expone un único objeto global, App:
 *
 *   App.exito('Usuario registrado.');
 *   App.aviso('Faltan datos obligatorios.');
 *   App.info('No hay cambios que guardar.');
 *   App.error('No se pudo guardar.', detalleTecnico);
 *
 *   App.api('api/operacionesUsuarios.php', { opcion: '1' })
 *       .done(function (data, respuesta) { … })   // solo si status === 'ok'
 *       .fail(function (mensaje) { … });           // mensaje ya listo para mostrar
 *
 *   App.confirmar({ titulo: '…', mensaje: '…', peligro: true })
 *       .done(function () { … });                  // solo si el usuario confirma
 *
 * App.error() además imprime el detalle en la consola del navegador.
 *
 * OJO: un toast comunica el RESULTADO de una operación. Los mensajes de
 * progreso ("Guardando…", "Cargando…") no van aquí: se muestran en la propia
 * página, junto al control que lanzó la operación, y se retiran al terminar.
 */
var App = (function ($) {
    'use strict';

    /* ==================================================================
       Peticiones al servidor

       Todas las peticiones AJAX llevan la cabecera X-Requested-With (los
       endpoints rechazan las que no la traen) y el token CSRF que la página
       publica en <meta name="csrf-token">.
       ================================================================== */
    var metaCsrf = document.querySelector('meta[name="csrf-token"]');
    var TOKEN_CSRF = metaCsrf ? metaCsrf.getAttribute('content') : '';

    $.ajaxSetup({
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': TOKEN_CSRF
        }
    });

    /**
     * Envía una petición POST a un endpoint de api/ y normaliza la respuesta.
     *
     * Devuelve una promesa de jQuery que:
     *  - se resuelve con (data, respuesta) cuando status === 'ok';
     *  - se rechaza con (mensaje, respuesta) en cualquier otro caso, con un
     *    mensaje entendible para mostrar al usuario.
     *
     * Si la sesión terminó (401), manda a la pantalla de acceso.
     */
    function api(url, datos) {
        var diferido = $.Deferred();

        $.ajax({
            url: url,
            method: 'POST',
            dataType: 'json',
            data: datos || {}
        }).done(function (respuesta) {
            if (respuesta && respuesta.status === 'ok') {
                diferido.resolve(respuesta.data, respuesta);
                return;
            }

            diferido.reject(
                (respuesta && respuesta.mensaje) || 'No fue posible completar la operación.',
                respuesta
            );
        }).fail(function (xhr) {
            var respuesta = xhr.responseJSON || {};

            if (xhr.status === 401) {
                window.location.href = 'login.php';
            }

            diferido.reject(
                respuesta.mensaje || 'No se pudo conectar con el servidor. Intenta de nuevo.',
                respuesta
            );
        });

        return diferido.promise();
    }

    /* ==================================================================
       Notificaciones emergentes (toasts)
       ================================================================== */
    var ID_CONTENEDOR = 'contenedorToasts';
    var DURACION = 5000;

    var TIPOS = {
        exito: { clase: 'app-toast-exito', icono: 'bi-check-circle-fill',        urgencia: 'polite' },
        info:  { clase: 'app-toast-info',  icono: 'bi-info-circle-fill',         urgencia: 'polite' },
        aviso: { clase: 'app-toast-aviso', icono: 'bi-exclamation-triangle-fill', urgencia: 'assertive' },
        error: { clase: 'app-toast-error', icono: 'bi-x-circle-fill',            urgencia: 'assertive' }
    };

    /**
     * Devuelve el contenedor de los toasts. Si la página olvidó incluir
     * includes/toast.php, lo crea al vuelo para que un aviso nunca se pierda.
     */
    function obtenerContenedor() {
        var contenedor = document.getElementById(ID_CONTENEDOR);

        if (contenedor === null) {
            contenedor = document.createElement('div');
            contenedor.id = ID_CONTENEDOR;
            contenedor.className = 'toast-container app-toasts position-fixed top-0 end-0 p-3';
            contenedor.setAttribute('aria-live', 'polite');
            contenedor.setAttribute('aria-atomic', 'true');
            document.body.appendChild(contenedor);
        }

        return contenedor;
    }

    /**
     * Muestra una notificación.
     *
     * @param {string} mensaje Texto visible para el usuario.
     * @param {string} tipo    'exito', 'error', 'aviso' o 'info'.
     */
    function toast(mensaje, tipo) {
        var ajustes = TIPOS[tipo] || TIPOS.info;
        var contenedor = obtenerContenedor();

        var elemento = document.createElement('div');
        elemento.className = 'toast app-toast ' + ajustes.clase;
        elemento.setAttribute('role', 'alert');
        elemento.setAttribute('aria-live', ajustes.urgencia);
        elemento.setAttribute('aria-atomic', 'true');

        var cuerpo = document.createElement('div');
        cuerpo.className = 'toast-body';

        var icono = document.createElement('i');
        icono.className = 'bi ' + ajustes.icono + ' app-toast-icono';

        // textContent y no innerHTML: el mensaje puede venir del servidor.
        var texto = document.createElement('span');
        texto.className = 'app-toast-texto';
        texto.textContent = String(mensaje === null || mensaje === undefined ? '' : mensaje);

        var cerrar = document.createElement('button');
        cerrar.type = 'button';
        cerrar.className = 'btn-close';
        cerrar.setAttribute('data-bs-dismiss', 'toast');
        cerrar.setAttribute('aria-label', 'Cerrar');

        cuerpo.appendChild(icono);
        cuerpo.appendChild(texto);
        cuerpo.appendChild(cerrar);
        elemento.appendChild(cuerpo);
        contenedor.appendChild(elemento);

        /* Sin el bundle de Bootstrap no hay componente Toast; en ese caso el
           aviso se queda visible en lugar de perderse. */
        if (typeof bootstrap === 'undefined' || !bootstrap.Toast) {
            elemento.classList.add('show');
            return;
        }

        var notificacion = new bootstrap.Toast(elemento, { autohide: true, delay: DURACION });

        // El nodo se destruye al cerrarse para no ir acumulando basura en el DOM.
        elemento.addEventListener('hidden.bs.toast', function () {
            elemento.remove();
        });

        notificacion.show();
    }

    function exito(mensaje) { toast(mensaje, 'exito'); }
    function info(mensaje)  { toast(mensaje, 'info'); }
    function aviso(mensaje) { toast(mensaje, 'aviso'); }

    /**
     * Muestra un error y deja el detalle técnico en la consola.
     */
    function error(mensaje, detalle) {
        toast(mensaje, 'error');

        if (detalle !== undefined && detalle !== null && detalle !== '') {
            console.error('[adminAccesos] ' + mensaje, detalle);
        }
    }

    /* ==================================================================
       Utilidades de presentación
       ================================================================== */

    /**
     * Escapa un texto para poder insertarlo en HTML, incluidos los atributos.
     * Casi todo lo que se pinta viene de la base de datos.
     */
    function escaparHtml(valor) {
        return String(valor === null || valor === undefined ? '' : valor)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    /**
     * Convierte 'YYYY-MM-DD' (o el inicio de 'YYYY-MM-DD HH:MM:SS') en
     * 'DD/MM/YYYY'. Si el valor no tiene ese formato, lo devuelve tal cual.
     */
    function formatoFecha(valor) {
        var texto = String(valor === null || valor === undefined ? '' : valor);
        var partes = /^(\d{4})-(\d{2})-(\d{2})/.exec(texto);

        return partes ? partes[3] + '/' + partes[2] + '/' + partes[1] : texto;
    }

    /* Textos en español de DataTables. Se escriben aquí porque el paquete está
       vendorizado y no se puede traer el idioma desde una URL. */
    var TEXTOS_TABLA = {
        emptyTable: 'No hay registros para mostrar.',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
        infoEmpty: 'Sin registros',
        infoFiltered: '(filtrado de _MAX_ registros)',
        lengthMenu: 'Mostrar _MENU_ registros',
        loadingRecords: 'Cargando…',
        processing: 'Procesando…',
        search: 'Buscar:',
        searchPlaceholder: 'Filtrar…',
        zeroRecords: 'Ningún registro coincide con la búsqueda.',
        paginate: {
            first: 'Primero',
            last: 'Último',
            next: 'Siguiente',
            previous: 'Anterior'
        },
        aria: {
            orderable: 'Ordenar por esta columna',
            orderableReverse: 'Invertir el orden de esta columna'
        }
    };

    /* ==================================================================
       Confirmación de acciones sensibles (includes/modalConfirmar.php)
       ================================================================== */

    /**
     * Abre el cuadro de confirmación y devuelve una promesa que se resuelve
     * solo si el usuario confirma. Cancelar o cerrar la rechaza.
     *
     * @param {Object}  opciones
     * @param {string}  opciones.titulo
     * @param {string}  opciones.mensaje         Pregunta principal.
     * @param {string}  [opciones.detalle]       Consecuencias, en texto secundario.
     * @param {string}  [opciones.textoConfirmar]
     * @param {boolean} [opciones.peligro]       Botón rojo e icono de advertencia.
     */
    function confirmar(opciones) {
        var diferido = $.Deferred();
        var $modal = $('#modalConfirmar');
        var $aceptar = $('#modalConfirmarAceptar');
        var peligro = !!opciones.peligro;
        var confirmado = false;

        // Sin el componente no se puede preguntar: se cancela por seguridad.
        if ($modal.length === 0 || typeof bootstrap === 'undefined') {
            return diferido.reject().promise();
        }

        // .text() y no .html(): los textos llevan nombres que vienen de la base.
        $('#modalConfirmarTitulo').text(opciones.titulo || 'Confirmar');
        $('#modalConfirmarMensaje').text(opciones.mensaje || '');
        $('#modalConfirmarDetalle').text(opciones.detalle || '').toggle(!!opciones.detalle);
        $('#modalConfirmarIcono')
            .toggleClass('confirmar-icono-peligro', peligro)
            .find('i').attr('class', 'bi ' + (peligro ? 'bi-exclamation-triangle' : 'bi-question-circle'));
        $aceptar
            .text(opciones.textoConfirmar || 'Confirmar')
            .toggleClass('btn-danger', peligro)
            .toggleClass('btn-primary', !peligro);

        $aceptar.off('click.confirmar').on('click.confirmar', function () {
            confirmado = true;
            bootstrap.Modal.getOrCreateInstance($modal[0]).hide();
        });

        // Se resuelve al terminar de cerrarse, para que la acción siguiente
        // (por ejemplo, abrir otro modal) no choque con la animación.
        $modal.off('hidden.bs.modal.confirmar').one('hidden.bs.modal.confirmar', function () {
            if (confirmado) {
                diferido.resolve();
            } else {
                diferido.reject();
            }
        });

        bootstrap.Modal.getOrCreateInstance($modal[0]).show();

        return diferido.promise();
    }

    /* ==================================================================
       Piezas de presentación compartidas (App.ui)

       Devuelven HTML ya escapado. Se usan en las pantallas de catálogos y
       asignaciones para no repetir el mismo marcado en cada script.
       ================================================================== */
    var ui = {
        /**
         * Clave estable (código, nómina, modulo.accion) en monoespaciada.
         * <wbr> después de cada punto: si no cabe, corta entre las partes de
         * la clave (SRD. / defectos. / ver) y no a media palabra.
         */
        clave: function (texto) {
            return '<span class="clave">' + escaparHtml(texto).replace(/\./g, '.<wbr>') + '</span>';
        },

        /** Etiqueta Activo/Inactivo, con icono además del color. */
        badgeActivo: function (activo, textoActivo, textoInactivo) {
            return activo
                ? '<span class="badge badge-activo"><i class="bi bi-check-circle" aria-hidden="true"></i>' +
                    escaparHtml(textoActivo || 'Activo') + '</span>'
                : '<span class="badge badge-inactivo"><i class="bi bi-dash-circle" aria-hidden="true"></i>' +
                    escaparHtml(textoInactivo || 'Inactivo') + '</span>';
        },

        cargador: function (texto) {
            return '<div class="app-loader">' +
                '<div class="spinner-border" role="status"><span class="visually-hidden">Cargando…</span></div>' +
                '<div>' + escaparHtml(texto || 'Cargando…') + '</div></div>';
        },

        vacio: function (icono, titulo, texto) {
            return '<div class="empty-state empty-state-compacto">' +
                '<i class="bi ' + escaparHtml(icono) + '" aria-hidden="true"></i>' +
                '<div class="empty-title">' + escaparHtml(titulo) + '</div>' +
                (texto ? '<div>' + escaparHtml(texto) + '</div>' : '') + '</div>';
        },

        /** Pone o quita el indicador de progreso de un botón. */
        botonOcupado: function ($boton, ocupado, textoProgreso) {
            if (ocupado) {
                $boton.data('contenidoOriginal', $boton.html());
                $boton.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>' +
                    escaparHtml(textoProgreso || 'Guardando…')
                );
            } else {
                $boton.prop('disabled', false).html($boton.data('contenidoOriginal') || $boton.html());
            }
        },

        /**
         * Opciones de un <select> de aplicaciones. Las inactivas se marcan en
         * el texto para que se distingan sin abrir otra pantalla.
         */
        opcionesAplicaciones: function (aplicaciones, textoPrimera) {
            var html = textoPrimera !== undefined
                ? '<option value="">' + escaparHtml(textoPrimera) + '</option>'
                : '';

            aplicaciones.forEach(function (a) {
                html += '<option value="' + escaparHtml(a.codigoSistema) + '">' +
                    escaparHtml(a.codigoSistema + ' — ' + a.nombre + (a.activo ? '' : ' (inactiva)')) +
                    '</option>';
            });

            return html;
        }
    };

    /**
     * Valor de un parámetro de la URL (?sistema=SRD). Lo usan las pantallas
     * para llegar ya filtradas desde un enlace de otra pantalla.
     */
    function parametroUrl(nombre) {
        try {
            return new URLSearchParams(window.location.search).get(nombre) || '';
        } catch (e) {
            return '';
        }
    }

    /* ==================================================================
       Selector de usuario (includes/selectorUsuario.php)
       ================================================================== */

    /** Minúsculas y sin acentos, para buscar "jose" y encontrar "José". */
    function normalizarTexto(texto) {
        return String(texto === null || texto === undefined ? '' : texto)
            .toLowerCase()
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, '');
    }

    /**
     * Conecta el buscador de usuarios de includes/selectorUsuario.php.
     *
     *   var selector = App.selectorUsuario({
     *       alElegir: function (usuario) { … },     // usuario = fila de consultarUsuarios()
     *       alCambiar: function () { … },           // se volvió a la búsqueda
     *       antesDeCambiar: function () { return $.Deferred().resolve(); } // opcional
     *   });
     *   selector.cargar(usuarios, App.parametroUrl('nomina'));
     *
     * Al elegir, la nómina se escribe en la URL (?nomina=) para que recargar
     * la página conserve al usuario.
     */
    function selectorUsuario(opciones) {
        var MAXIMO_RESULTADOS = 8;
        var $buscar = $('#selectorUsuarioBuscar');
        var $resultados = $('#selectorUsuarioResultados');
        var usuarios = [];
        var elegido = null;

        function coincidencias() {
            var texto = normalizarTexto($.trim($buscar.val()));

            return usuarios.filter(function (u) {
                return texto === '' || normalizarTexto(u.nominaUsuario + ' ' + u.nombre).indexOf(texto) !== -1;
            });
        }

        function pintar() {
            var lista = coincidencias();

            if (lista.length === 0) {
                $resultados.html('<div class="selector-usuario-vacio">Ningún usuario coincide con la búsqueda.</div>');
                return;
            }

            var html = lista.slice(0, MAXIMO_RESULTADOS).map(function (u) {
                return '<button type="button" class="selector-usuario-opcion" data-nomina="' + escaparHtml(u.nominaUsuario) + '">' +
                    '<span class="selector-usuario-opcion-texto">' +
                        '<span class="fw-semibold">' + escaparHtml(u.nombre) + '</span>' +
                        '<span class="selector-usuario-opcion-datos">' + ui.clave(u.nominaUsuario) +
                            ' · ' + u.totalRoles + ' rol(es) · ' + u.excepcionesVigentes + ' permiso(s) ind.</span>' +
                    '</span>' +
                    ui.badgeActivo(u.estatus === 'activo') +
                    '</button>';
            }).join('');

            if (lista.length > MAXIMO_RESULTADOS) {
                html += '<div class="selector-usuario-vacio">… y ' + (lista.length - MAXIMO_RESULTADOS) +
                    ' más. Escribe más letras para acotar.</div>';
            }

            $resultados.html(html);
        }

        function mostrarElegido(usuario) {
            $('#selectorUsuarioNombre').text(usuario.nombre);
            $('#selectorUsuarioNomina').text(usuario.nominaUsuario);
            $('#selectorUsuarioEstatus').html(ui.badgeActivo(usuario.estatus === 'activo'));
            $('#selectorUsuarioBusqueda').addClass('d-none');
            $('#selectorUsuarioElegido').removeClass('d-none');
        }

        function escribirNominaEnUrl(nomina) {
            try {
                var url = new URL(window.location.href);
                if (nomina) {
                    url.searchParams.set('nomina', nomina);
                } else {
                    url.searchParams.delete('nomina');
                }
                window.history.replaceState(null, '', url.toString());
            } catch (e) {
                // Navegador sin URL/history: no pasa nada, solo no se recuerda.
            }
        }

        function elegir(usuario) {
            elegido = usuario;
            mostrarElegido(usuario);
            escribirNominaEnUrl(usuario.nominaUsuario);

            if (opciones.alElegir) {
                opciones.alElegir(usuario);
            }
        }

        function buscarPorNomina(nomina) {
            for (var i = 0; i < usuarios.length; i++) {
                if (usuarios[i].nominaUsuario === nomina) {
                    return usuarios[i];
                }
            }
            return null;
        }

        $buscar.on('input', pintar);

        // Enter elige el primer resultado: lo más rápido cuando se escribe la nómina completa.
        $buscar.on('keydown', function (evento) {
            if (evento.key === 'Enter') {
                evento.preventDefault();
                var lista = coincidencias();
                if (lista.length > 0) {
                    elegir(lista[0]);
                }
            }
        });

        $resultados.on('click', '.selector-usuario-opcion', function () {
            var usuario = buscarPorNomina($(this).attr('data-nomina'));
            if (usuario) {
                elegir(usuario);
            }
        });

        $('#selectorUsuarioCambiar').on('click', function () {
            var permiso = opciones.antesDeCambiar ? opciones.antesDeCambiar() : $.Deferred().resolve().promise();

            permiso.done(function () {
                elegido = null;
                escribirNominaEnUrl('');
                $('#selectorUsuarioElegido').addClass('d-none');
                $('#selectorUsuarioBusqueda').removeClass('d-none');
                $buscar.val('').trigger('focus');
                pintar();

                if (opciones.alCambiar) {
                    opciones.alCambiar();
                }
            });
        });

        return {
            /** Recibe (o actualiza) la lista de usuarios; elige nominaInicial si existe. */
            cargar: function (lista, nominaInicial) {
                usuarios = lista || [];
                $buscar.prop('disabled', false).attr('placeholder', 'Ej. 11607 o Juan Pérez');

                if (elegido) {
                    // Refresca los datos del elegido (contadores, estatus) sin volver a elegirlo.
                    var actualizado = buscarPorNomina(elegido.nominaUsuario);
                    if (actualizado) {
                        elegido = actualizado;
                        mostrarElegido(actualizado);
                    }
                    return;
                }

                var inicial = nominaInicial ? buscarPorNomina(nominaInicial) : null;

                if (inicial) {
                    elegir(inicial);
                } else {
                    pintar();
                }
            },

            actual: function () {
                return elegido;
            }
        };
    }

    /* En pantallas angostas el menú se desplaza en horizontal; se lleva a la
       vista la entrada de la pantalla actual para que se sepa dónde se está. */
    var enlaceActivo = document.querySelector('.app-nav-enlaces .nav-link.active');

    if (enlaceActivo) {
        var contenedorMenu = enlaceActivo.closest('.app-nav-enlaces');
        // Medidas en pantalla y no offsetLeft: los grupos van dentro de un
        // .dropdown con position: relative, que cambia la referencia de offsetLeft.
        var desborde = enlaceActivo.getBoundingClientRect().right -
            contenedorMenu.getBoundingClientRect().right;

        if (desborde > 0) {
            contenedorMenu.scrollLeft += desborde + 16;
        }
    }

    return {
        api: api,
        toast: toast,
        exito: exito,
        info: info,
        aviso: aviso,
        error: error,
        escaparHtml: escaparHtml,
        formatoFecha: formatoFecha,
        confirmar: confirmar,
        ui: ui,
        parametroUrl: parametroUrl,
        normalizarTexto: normalizarTexto,
        selectorUsuario: selectorUsuario,
        TEXTOS_TABLA: TEXTOS_TABLA
    };
})(jQuery);
