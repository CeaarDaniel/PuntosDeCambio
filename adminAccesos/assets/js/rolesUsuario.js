/**
 * assets/js/rolesUsuario.js — Roles de usuario (pages/rolesUsuario.php).
 *
 * Habla con api/operacionesRolesUsuario.php:
 *   1 catálogos (usuarios, aplicaciones) · 2 roles de la aplicación para el
 *   usuario · 3 guardar (lista de codigoRol en JSON; se aplica la diferencia)
 *
 * Quitar roles pide confirmación antes de guardar. Cambiar de usuario o de
 * aplicación con cambios sin guardar también pide confirmación.
 */
(function () {
    'use strict';

    var URL_API = 'api/operacionesRolesUsuario.php';
    var ui = App.ui;

    var $selAplicacion = $('#rolesUsuarioAplicacion');
    var $lista = $('#rolesUsuarioLista');
    var $pie = $('#rolesUsuarioPie');
    var $btnGuardar = $('#btnGuardarRolesUsuario');

    var aplicaciones = [];
    var usuario = null;          // usuario elegido (fila de consultarUsuarios)
    var roles = [];              // roles de la aplicación elegida
    var aplicacionActual = '';   // valor confirmado del select (para poder revertirlo)

    function htmlVacio(icono, titulo, texto) {
        return ui.vacio(icono, titulo, texto);
    }

    /* ==================================================================
       Estado de la selección
       ================================================================== */
    function seleccion() {
        return $lista.find('.js-rol:checked').map(function () { return this.value; }).get();
    }

    function cambios() {
        var marcados = seleccion();
        var agregar = [];
        var quitar = [];

        roles.forEach(function (r) {
            var marcado = marcados.indexOf(r.codigoRol) !== -1;
            if (marcado && !r.asignado) { agregar.push(r); }
            if (!marcado && r.asignado) { quitar.push(r); }
        });

        return { agregar: agregar, quitar: quitar };
    }

    function hayCambios() {
        var c = cambios();
        return c.agregar.length > 0 || c.quitar.length > 0;
    }

    function actualizarResumen() {
        var c = cambios();
        var total = seleccion().length;

        $('#rolesUsuarioResumen').html(
            '<strong>' + total + '</strong> rol(es) marcados' +
            (c.agregar.length || c.quitar.length
                ? ' · <span class="text-success">+' + c.agregar.length + '</span> <span class="text-danger">−' +
                    c.quitar.length + '</span> sin guardar'
                : '')
        );
        $btnGuardar.prop('disabled', !(c.agregar.length || c.quitar.length));
    }

    /**
     * Si hay cambios sin guardar, pregunta antes de descartarlos. Devuelve una
     * promesa que se resuelve si se puede continuar.
     */
    function confirmarDescartar() {
        if (!hayCambios()) {
            return $.Deferred().resolve().promise();
        }

        return App.confirmar({
            titulo: 'Cambios sin guardar',
            mensaje: 'Marcaste o desmarcaste roles y no los has guardado.',
            detalle: '¿Descartar esos cambios?',
            textoConfirmar: 'Descartar cambios',
            peligro: true
        });
    }

    /* ==================================================================
       Pintado
       ================================================================== */
    function pintarResumen(resumen) {
        var $cuerpo = $('#resumenRolesCuerpo');
        $('#resumenRolesUsuario').removeClass('d-none');

        if (resumen.length === 0) {
            $cuerpo.html('<p class="text-muted-soft mb-0">No tiene roles en ninguna aplicación.</p>');
            return;
        }

        $cuerpo.html('<ul class="rol-usuario-resumen-lista">' + resumen.map(function (r) {
            var efecto = r.rolActivo && r.aplicacionActiva;
            return '<li class="' + (efecto ? '' : 'text-muted-soft') + '">' +
                ui.clave(r.codigoSistema) + ' <span class="fw-semibold">' + App.escaparHtml(r.nombreRol) + '</span>' +
                (r.rolActivo ? '' : ' <span class="badge badge-inactivo">Rol inactivo</span>') +
                (r.aplicacionActiva ? '' : ' <span class="badge badge-inactivo">Aplicación inactiva</span>') +
                '</li>';
        }).join('') + '</ul>');
    }

    function pintarRoles(aplicacion) {
        var usuarioActivo = usuario.estatus === 'activo';

        $('#avisoUsuarioInactivo').toggleClass('d-none', usuarioActivo);

        if (roles.length === 0) {
            $lista.html(htmlVacio('bi-person-badge', 'Esta aplicación no tiene roles',
                'Créalos primero en Catálogos › Roles.'));
            $pie.addClass('d-none');
            return;
        }

        var avisoAplicacion = aplicacion.activo ? '' :
            '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>' +
            'La aplicación está inactiva: los roles se pueden asignar, pero no tendrán efecto hasta activarla.</div>';

        $lista.html(avisoAplicacion + '<div class="rol-usuario-lista">' + roles.map(function (r, i) {
            /* Un rol nuevo solo se puede marcar si está activo y el usuario
               también. Uno ya asignado siempre se puede desmarcar (quitar). */
            var bloqueado = !r.asignado && (!r.activo || !usuarioActivo);
            var motivo = !r.activo ? 'El rol está inactivo y no se puede asignar.' :
                (!usuarioActivo ? 'El usuario está inactivo.' : '');
            var id = 'rolUsuario' + i;

            return '<label class="rol-usuario-opcion' + (bloqueado ? ' rol-usuario-opcion-bloqueada' : '') + '" for="' + id + '"' +
                (bloqueado ? ' title="' + App.escaparHtml(motivo) + '"' : '') + '>' +
                '<input class="form-check-input js-rol" type="checkbox" id="' + id + '" value="' + App.escaparHtml(r.codigoRol) + '"' +
                    (r.asignado ? ' checked' : '') + (bloqueado ? ' disabled' : '') + '>' +
                '<span class="rol-usuario-opcion-texto">' +
                    '<span class="fw-semibold">' + App.escaparHtml(r.nombreRol) + '</span> ' + ui.clave(r.codigoRol) +
                    (r.descripcion ? '<span class="celda-secundaria d-block">' + App.escaparHtml(r.descripcion) + '</span>' : '') +
                    '<span class="celda-secundaria d-block">' + r.permisos + ' permiso(s)</span>' +
                '</span>' +
                '<span class="rol-usuario-opcion-estado">' +
                    (r.asignado ? '<span class="badge badge-vigente"><i class="bi bi-check2" aria-hidden="true"></i>Asignado</span>' : '') +
                    (r.activo ? '' : '<span class="badge badge-inactivo"><i class="bi bi-dash-circle" aria-hidden="true"></i>Inactivo</span>') +
                '</span>' +
                '</label>';
        }).join('') + '</div>');

        $pie.removeClass('d-none');
        actualizarResumen();
    }

    /* ==================================================================
       Carga
       ================================================================== */
    function cargarRoles() {
        var sistema = $selAplicacion.val();

        if (!usuario || !sistema) {
            roles = [];
            $pie.addClass('d-none');
            $lista.html(htmlVacio('bi-grid-1x2', 'Elige una aplicación', 'Se mostrarán solo los roles de esa aplicación.'));
            return;
        }

        $lista.html(ui.cargador('Cargando roles de ' + sistema + '…'));
        $pie.addClass('d-none');

        App.api(URL_API, { opcion: '2', nomina: usuario.nominaUsuario, codigoSistema: sistema })
            .done(function (data) {
                usuario.estatus = data.usuario.estatus;
                roles = data.roles || [];
                pintarResumen(data.resumen || []);
                pintarRoles(data.aplicacion);
            })
            .fail(function (mensaje, respuesta) {
                App.error(mensaje, respuesta);
                $lista.html(htmlVacio('bi-cloud-slash', 'No se pudieron cargar los roles', mensaje));
            });
    }

    var selector = App.selectorUsuario({
        alElegir: function (elegido) {
            usuario = $.extend({}, elegido);
            $('#panelRolesAplicacion').removeClass('paso-bloqueado');
            $selAplicacion.prop('disabled', false);

            if (!$selAplicacion.val()) {
                $selAplicacion.trigger('focus');
            }

            cargarRoles();
        },
        antesDeCambiar: confirmarDescartar,
        alCambiar: function () {
            usuario = null;
            roles = [];
            $('#panelRolesAplicacion').addClass('paso-bloqueado');
            $('#resumenRolesUsuario, #avisoUsuarioInactivo').addClass('d-none');
            $selAplicacion.prop('disabled', true);
            $pie.addClass('d-none');
            $lista.html(htmlVacio('bi-person-badge', 'Elige un usuario y una aplicación',
                'Aquí aparecerán los roles de esa aplicación para marcarlos.'));
        }
    });

    function cargarCatalogos() {
        return App.api(URL_API, { opcion: '1' })
            .done(function (data) {
                aplicaciones = data.aplicaciones || [];

                if ($selAplicacion.children().length <= 1) {
                    $selAplicacion.html(ui.opcionesAplicaciones(aplicaciones, 'Selecciona la aplicación…'));
                    $selAplicacion.val(App.parametroUrl('sistema'));
                    if ($selAplicacion.val() === null) { $selAplicacion.val(''); }
                    aplicacionActual = $selAplicacion.val();
                }

                selector.cargar(data.usuarios || [], App.parametroUrl('nomina'));
            })
            .fail(function (mensaje, respuesta) {
                App.error(mensaje, respuesta);
            });
    }

    /* ==================================================================
       Eventos
       ================================================================== */
    $selAplicacion.on('change', function () {
        var nueva = $selAplicacion.val();

        // Con cambios pendientes se pregunta; si cancela, se regresa al valor anterior.
        $selAplicacion.val(aplicacionActual);

        confirmarDescartar().done(function () {
            $selAplicacion.val(nueva);
            aplicacionActual = nueva;
            cargarRoles();
        });
    });

    $lista.on('change', '.js-rol', actualizarResumen);

    function guardar() {
        ui.botonOcupado($btnGuardar, true);

        App.api(URL_API, {
            opcion: '3',
            nomina: usuario.nominaUsuario,
            codigoSistema: $selAplicacion.val(),
            roles: JSON.stringify(seleccion())
        })
            .done(function (data, respuesta) {
                App.exito(respuesta.mensaje);
                cargarRoles();
                cargarCatalogos();   // actualiza los contadores del buscador
            })
            .fail(function (mensaje, respuesta) {
                App.error(mensaje, respuesta);
            })
            .always(function () {
                ui.botonOcupado($btnGuardar, false);
                actualizarResumen();
            });
    }

    // Quitar roles siempre pide confirmación (contexto.md, módulo 7).
    $btnGuardar.on('click', function () {
        var c = cambios();

        if (c.quitar.length === 0) {
            guardar();
            return;
        }

        var nombres = function (lista) {
            return lista.map(function (r) { return r.nombreRol + ' (' + r.codigoRol + ')'; }).join(', ');
        };

        App.confirmar({
            titulo: 'Quitar roles',
            mensaje: '¿Quitar ' + c.quitar.length + ' rol(es) a ' + usuario.nombre + '?',
            detalle: 'Se quitarán: ' + nombres(c.quitar) + '.' +
                (c.agregar.length ? ' Y se asignarán: ' + nombres(c.agregar) + '.' : '') +
                ' El cambio tendrá efecto cuando vuelva a iniciar sesión.',
            textoConfirmar: 'Sí, guardar',
            peligro: true
        }).done(guardar);
    });

    // Aviso del navegador si se intenta salir con cambios sin guardar.
    $(window).on('beforeunload', function (evento) {
        if (usuario && hayCambios()) {
            evento.preventDefault();
            return '';
        }
    });

    cargarCatalogos();
})();
