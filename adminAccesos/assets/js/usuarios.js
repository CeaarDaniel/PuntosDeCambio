/**
 * assets/js/usuarios.js — Administración de usuarios (pages/usuarios.php).
 *
 * Habla con api/operacionesUsuarios.php:
 *   1 listar · 2 detalle · 3 crear · 4 modificar · 5 activar/desactivar · 6 contraseña
 *
 * Todo lo que viene de la base se escapa con App.escaparHtml antes de
 * insertarlo. Las contraseñas solo viajan al servidor: nunca se guardan en
 * variables globales ni se muestran en toasts, y los campos se limpian al
 * cerrar los modales.
 *
 * Las validaciones de aquí son de comodidad; el servidor vuelve a validarlo todo.
 */
(function () {
    'use strict';

    var URL_API = 'api/operacionesUsuarios.php';

    var $tabla = $('#tablaUsuarios');
    var $estado = $('#usuariosEstado');
    var tabla = null;

    /* Último listado recibido, para abrir los modales sin volver a pedirlo. */
    var usuarios = [];
    var nominaSesion = '';

    /* ==================================================================
       Utilidades
       ================================================================== */

    function buscarUsuario(nomina) {
        for (var i = 0; i < usuarios.length; i++) {
            if (usuarios[i].nominaUsuario === nomina) {
                return usuarios[i];
            }
        }
        return null;
    }

    function htmlClave(texto) {
        return '<span class="clave">' + App.escaparHtml(texto) + '</span>';
    }

    function htmlBadgeEstatus(estatus) {
        return estatus === 'activo'
            ? '<span class="badge badge-activo"><i class="bi bi-check-circle" aria-hidden="true"></i>Activo</span>'
            : '<span class="badge badge-inactivo"><i class="bi bi-dash-circle" aria-hidden="true"></i>Inactivo</span>';
    }

    function htmlBadgeTipo(tipo) {
        return tipo === 'denegar'
            ? '<span class="badge badge-denegar"><i class="bi bi-slash-circle" aria-hidden="true"></i>Denegar</span>'
            : '<span class="badge badge-conceder"><i class="bi bi-check-circle" aria-hidden="true"></i>Conceder</span>';
    }

    function htmlBadgeVigencia(estado, fecha) {
        if (estado === 'permanente') {
            return '<span class="badge badge-permanente"><i class="bi bi-infinity" aria-hidden="true"></i>Permanente</span>';
        }
        if (estado === 'vencido') {
            return '<span class="badge badge-vencido"><i class="bi bi-calendar-x" aria-hidden="true"></i>Venció ' +
                App.escaparHtml(App.formatoFecha(fecha)) + '</span>';
        }
        return '<span class="badge badge-vigente"><i class="bi bi-calendar-check" aria-hidden="true"></i>Hasta ' +
            App.escaparHtml(App.formatoFecha(fecha)) + '</span>';
    }

    function htmlCargador(texto) {
        return '<div class="app-loader">' +
            '<div class="spinner-border" role="status"><span class="visually-hidden">Cargando…</span></div>' +
            '<div>' + App.escaparHtml(texto) + '</div></div>';
    }

    function htmlVacio(icono, titulo, texto) {
        return '<div class="empty-state usuarios-vacio">' +
            '<i class="bi ' + icono + '" aria-hidden="true"></i>' +
            '<div class="empty-title">' + App.escaparHtml(titulo) + '</div>' +
            (texto ? '<div>' + App.escaparHtml(texto) + '</div>' : '') + '</div>';
    }

    /** Pone o quita el indicador de progreso de un botón. */
    function botonOcupado($boton, ocupado, textoProgreso) {
        if (ocupado) {
            $boton.data('contenido', $boton.html());
            $boton.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>' +
                App.escaparHtml(textoProgreso || 'Guardando…')
            );
        } else {
            $boton.prop('disabled', false).html($boton.data('contenido') || $boton.html());
        }
    }

    function mostrarMensajeModal($alerta, mensaje) {
        $alerta.text(mensaje).removeClass('d-none');
    }

    /* ==================================================================
       Tabla
       ================================================================== */

    function htmlAcciones(usuario) {
        var nomina = App.escaparHtml(usuario.nominaUsuario);
        var esSesion = usuario.nominaUsuario === nominaSesion;
        var botonEstatus;

        if (usuario.estatus === 'activo') {
            botonEstatus = esSesion
                ? '<button type="button" class="btn btn-soft btn-icon" disabled ' +
                    'title="No puedes desactivar tu propio usuario" aria-label="No puedes desactivar tu propio usuario">' +
                    '<i class="bi bi-person-dash" aria-hidden="true"></i></button>'
                : '<button type="button" class="btn btn-soft btn-icon text-danger js-estatus" data-nomina="' + nomina + '" ' +
                    'data-estatus="inactivo" title="Desactivar" aria-label="Desactivar">' +
                    '<i class="bi bi-person-dash" aria-hidden="true"></i></button>';
        } else {
            botonEstatus = '<button type="button" class="btn btn-soft btn-icon text-success js-estatus" data-nomina="' + nomina + '" ' +
                'data-estatus="activo" title="Activar" aria-label="Activar">' +
                '<i class="bi bi-person-check" aria-hidden="true"></i></button>';
        }

        return '<div class="usuarios-acciones">' +
            '<button type="button" class="btn btn-outline-primary btn-icon js-detalle" data-nomina="' + nomina + '" ' +
                'title="Ver detalle" aria-label="Ver detalle"><i class="bi bi-eye" aria-hidden="true"></i></button>' +
            '<button type="button" class="btn btn-soft btn-icon js-editar" data-nomina="' + nomina + '" ' +
                'title="Editar datos" aria-label="Editar datos"><i class="bi bi-pencil" aria-hidden="true"></i></button>' +
            '<button type="button" class="btn btn-soft btn-icon js-contrasena" data-nomina="' + nomina + '" ' +
                'title="Restablecer contraseña" aria-label="Restablecer contraseña"><i class="bi bi-key" aria-hidden="true"></i></button>' +
            botonEstatus +
            '</div>';
    }

    /**
     * render() recibe el tipo de operación: fuera de 'display' se devuelve
     * texto plano, para que la búsqueda y el orden trabajen sobre el dato y no
     * sobre las etiquetas HTML.
     */
    function inicializarTabla() {
        tabla = $tabla.DataTable({
            data: [],
            language: App.TEXTOS_TABLA,
            responsive: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Todos']],
            order: [[1, 'asc']],
            columnDefs: [
                { responsivePriority: 1, targets: 1 },
                { responsivePriority: 2, targets: 6 },
                { responsivePriority: 3, targets: 0 },
                { responsivePriority: 4, targets: 2 }
            ],
            columns: [
                {
                    data: 'nominaUsuario',
                    render: function (dato, tipo) {
                        return tipo === 'display' ? htmlClave(dato) : dato;
                    }
                },
                {
                    data: 'nombre',
                    render: function (dato, tipo, fila) {
                        if (tipo === 'filter') {
                            return dato + ' ' + (fila.comentarios || '');
                        }
                        if (tipo !== 'display') {
                            return dato;
                        }
                        return '<div class="fw-semibold">' + App.escaparHtml(dato) + '</div>' +
                            (fila.comentarios
                                ? '<div class="usuarios-comentario" title="' + App.escaparHtml(fila.comentarios) + '">' +
                                    App.escaparHtml(fila.comentarios) + '</div>'
                                : '');
                    }
                },
                {
                    data: 'estatus',
                    render: function (dato, tipo, fila) {
                        if (tipo !== 'display') {
                            return dato;
                        }
                        return '<div class="usuarios-estatus">' + htmlBadgeEstatus(dato) +
                            (fila.passwordValida ? '' :
                                '<span class="badge badge-por-vencer" title="Su contraseña no está guardada como hash; no puede iniciar sesión. Restablécela.">' +
                                '<i class="bi bi-exclamation-triangle" aria-hidden="true"></i>Contraseña sin cifrar</span>') +
                            '</div>';
                    }
                },
                {
                    data: 'totalRoles',
                    render: function (dato, tipo) {
                        if (tipo !== 'display') {
                            return dato;
                        }
                        return dato > 0 ? String(dato) : '<span class="text-muted-soft">0</span>';
                    }
                },
                {
                    data: 'excepcionesVigentes',
                    render: function (dato, tipo) {
                        if (tipo !== 'display') {
                            return dato;
                        }
                        return dato > 0 ? String(dato) : '<span class="text-muted-soft">0</span>';
                    }
                },
                {
                    data: 'fechaCreacion',
                    className: 'text-nowrap',
                    render: function (dato, tipo) {
                        if (tipo !== 'display') {
                            return dato || '';
                        }
                        return dato ? App.escaparHtml(App.formatoFecha(dato)) : '<span class="text-muted-soft">—</span>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: function (dato, tipo, fila) {
                        return tipo === 'display' ? htmlAcciones(fila) : '';
                    }
                }
            ]
        });
    }

    function actualizarTotalesFiltro() {
        var activos = usuarios.filter(function (u) { return u.estatus === 'activo'; }).length;

        $('[data-total="todos"]').text(usuarios.length);
        $('[data-total="activo"]').text(activos);
        $('[data-total="inactivo"]').text(usuarios.length - activos);
    }

    function cargarUsuarios() {
        return App.api(URL_API, { opcion: '1' })
            .done(function (data) {
                usuarios = data.usuarios || [];
                nominaSesion = data.nominaSesion || '';

                $estado.empty();
                $tabla.removeClass('d-none');

                if (tabla === null) {
                    inicializarTabla();
                }

                // Conserva página, orden y búsqueda al recargar tras un cambio.
                tabla.clear().rows.add(usuarios).draw(false);
                tabla.columns.adjust().responsive.recalc();
                actualizarTotalesFiltro();
            })
            .fail(function (mensaje, respuesta) {
                App.error(mensaje, respuesta);

                if (tabla === null) {
                    $estado.html(htmlVacio('bi-cloud-slash', 'No se pudieron cargar los usuarios',
                        'Recarga la página para intentarlo de nuevo.'));
                }
            });
    }

    /* Filtro rápido por estatus: busca la palabra exacta en la columna 2. */
    $('.filtro-pildoras').on('click', '.filtro-pildora', function () {
        var estatus = String($(this).attr('data-estatus') || '');

        $('.filtro-pildora').removeClass('active').attr('aria-pressed', 'false');
        $(this).addClass('active').attr('aria-pressed', 'true');

        if (tabla !== null) {
            tabla.column(2).search(estatus ? '^' + estatus + '$' : '', true, false).draw();
        }
    });

    /* ==================================================================
       Campos de contraseña: mostrar/ocultar y generar
       ================================================================== */

    function mostrarContrasenas($boton, visible) {
        $($boton.data('campos')).attr('type', visible ? 'text' : 'password');
        $boton
            .attr('aria-pressed', String(visible))
            .attr('title', visible ? 'Ocultar contraseña' : 'Mostrar contraseña')
            .attr('aria-label', visible ? 'Ocultar contraseña' : 'Mostrar contraseña')
            .find('i').toggleClass('bi-eye', !visible).toggleClass('bi-eye-slash', visible);
    }

    /**
     * Contraseña aleatoria de 10 caracteres con mayúsculas, minúsculas y
     * dígitos. Se omiten los caracteres que se confunden al dictarlos o
     * copiarlos a mano (0/O, 1/l/I).
     */
    function generarContrasena() {
        var caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        var longitud = 10;
        var contrasena;

        do {
            var valores = new Uint32Array(longitud);
            window.crypto.getRandomValues(valores);
            contrasena = '';

            for (var i = 0; i < longitud; i++) {
                contrasena += caracteres.charAt(valores[i] % caracteres.length);
            }
        } while (!/[A-Z]/.test(contrasena) || !/[a-z]/.test(contrasena) || !/[0-9]/.test(contrasena));

        return contrasena;
    }

    $(document).on('click', '.js-ver-contrasena', function () {
        var $boton = $(this);
        mostrarContrasenas($boton, $boton.attr('aria-pressed') !== 'true');
    });

    $(document).on('click', '.js-generar-contrasena', function () {
        var $boton = $(this);
        var contrasena = generarContrasena();
        var $form = $boton.closest('form');

        $($boton.data('campo')).val(contrasena);
        $($boton.data('confirmacion')).val(contrasena).trigger('input');

        // Se muestra para que el administrador pueda copiarla o dictarla.
        mostrarContrasenas($form.find('.js-ver-contrasena'), true);
    });

    /**
     * Marca la confirmación como inválida mientras no coincida. Así el aviso
     * aparece con el mismo estilo que el resto de las validaciones.
     */
    function validarConfirmacion($contrasena, $confirmacion) {
        var coincide = $contrasena.val() === $confirmacion.val();
        $confirmacion[0].setCustomValidity(coincide ? '' : 'Las contraseñas no coinciden.');
    }

    /* ==================================================================
       Modal: alta y edición
       ================================================================== */
    var $modalUsuario = $('#modalUsuario');
    var $formUsuario = $('#formUsuario');
    var $mensajeUsuario = $('#modalUsuarioMensaje');
    var $btnGuardarUsuario = $('#btnGuardarUsuario');
    var modoUsuario = 'alta';

    function actualizarContadorComentarios() {
        $('#usuarioComentariosContador').text(String($('#usuarioComentarios').val() || '').length);
    }

    function prepararModalUsuario(modo, usuario) {
        modoUsuario = modo;

        $formUsuario[0].reset();
        $formUsuario.removeClass('was-validated');
        $mensajeUsuario.addClass('d-none').text('');
        $('#usuarioConfirmacion')[0].setCustomValidity('');
        mostrarContrasenas($formUsuario.find('.js-ver-contrasena'), false);

        var esAlta = modo === 'alta';

        $('#modalUsuarioTitulo').text(esAlta ? 'Nuevo usuario' : 'Editar usuario');
        $('#usuarioNomina').prop('readonly', !esAlta);
        $('#usuarioNominaAyuda').text(esAlta
            ? 'Sin espacios. No se puede cambiar después.'
            : 'La nómina no se puede cambiar.');

        // Los campos ocultos se deshabilitan para que no cuenten en la validación.
        $formUsuario.find('.js-solo-alta').toggleClass('d-none', !esAlta)
            .find('input, select').prop('disabled', !esAlta);

        if (!esAlta && usuario) {
            $('#usuarioNomina').val(usuario.nominaUsuario);
            $('#usuarioNombre').val(usuario.nombre);
            $('#usuarioComentarios').val(usuario.comentarios || '');
        }

        actualizarContadorComentarios();
    }

    $('#btnNuevoUsuario').on('click', function () {
        prepararModalUsuario('alta');
        bootstrap.Modal.getOrCreateInstance($modalUsuario[0]).show();
    });

    $modalUsuario.on('shown.bs.modal', function () {
        $(modoUsuario === 'alta' ? '#usuarioNomina' : '#usuarioNombre').trigger('focus');
    });

    // Al cerrar, las contraseñas no se quedan en el DOM.
    $modalUsuario.on('hidden.bs.modal', function () {
        $('#usuarioContrasena, #usuarioConfirmacion').val('');
    });

    $('#usuarioComentarios').on('input', actualizarContadorComentarios);

    $('#usuarioContrasena, #usuarioConfirmacion').on('input', function () {
        validarConfirmacion($('#usuarioContrasena'), $('#usuarioConfirmacion'));
    });

    $formUsuario.on('input', function () {
        $mensajeUsuario.addClass('d-none');
    });

    $formUsuario.on('submit', function (evento) {
        evento.preventDefault();

        $('#usuarioNomina, #usuarioNombre').each(function () {
            this.value = this.value.trim();
        });

        if (modoUsuario === 'alta') {
            validarConfirmacion($('#usuarioContrasena'), $('#usuarioConfirmacion'));
        }

        if (!this.checkValidity()) {
            $formUsuario.addClass('was-validated');
            $formUsuario.find(':invalid').first().trigger('focus');
            return;
        }

        var datos = {
            opcion: modoUsuario === 'alta' ? '3' : '4',
            nomina: $('#usuarioNomina').val(),
            nombre: $('#usuarioNombre').val(),
            comentarios: $('#usuarioComentarios').val()
        };

        if (modoUsuario === 'alta') {
            datos.contrasena = $('#usuarioContrasena').val();
            datos.confirmacion = $('#usuarioConfirmacion').val();
            datos.estatus = $('#usuarioEstatus').val();
        }

        botonOcupado($btnGuardarUsuario, true);

        App.api(URL_API, datos)
            .done(function (data, respuesta) {
                bootstrap.Modal.getOrCreateInstance($modalUsuario[0]).hide();
                App.exito(respuesta.mensaje || 'Cambios guardados.');
                cargarUsuarios();
            })
            .fail(function (mensaje) {
                mostrarMensajeModal($mensajeUsuario, mensaje);
            })
            .always(function () {
                botonOcupado($btnGuardarUsuario, false);
            });
    });

    /* ==================================================================
       Modal: restablecer contraseña
       ================================================================== */
    var $modalContrasena = $('#modalContrasena');
    var $formContrasena = $('#formContrasena');
    var $mensajeContrasena = $('#modalContrasenaMensaje');
    var $btnContrasenaConfirmar = $('#btnContrasenaConfirmar');
    var nominaContrasena = '';

    function mostrarPasoContrasena(paso) {
        var primero = paso === 1;

        $('#contrasenaPaso1, #contrasenaAccionesPaso1').toggleClass('d-none', !primero);
        $('#contrasenaPaso2, #contrasenaAccionesPaso2').toggleClass('d-none', primero);

        if (primero) {
            $('#contrasenaNueva').trigger('focus');
        } else {
            $('#btnContrasenaRegresar').trigger('focus');
        }
    }

    function abrirContrasena(usuario) {
        nominaContrasena = usuario.nominaUsuario;

        $formContrasena[0].reset();
        $formContrasena.removeClass('was-validated');
        $mensajeContrasena.addClass('d-none').text('');
        $('#contrasenaConfirmacion')[0].setCustomValidity('');
        mostrarContrasenas($formContrasena.find('.js-ver-contrasena'), false);

        $('#contrasenaNombre').text(usuario.nombre);
        $('#contrasenaNomina').text(usuario.nominaUsuario);

        mostrarPasoContrasena(1);
        bootstrap.Modal.getOrCreateInstance($modalContrasena[0]).show();
    }

    $modalContrasena.on('shown.bs.modal', function () {
        $('#contrasenaNueva').trigger('focus');
    });

    $modalContrasena.on('hidden.bs.modal', function () {
        $('#contrasenaNueva, #contrasenaConfirmacion').val('');
        nominaContrasena = '';
    });

    $('#contrasenaNueva, #contrasenaConfirmacion').on('input', function () {
        validarConfirmacion($('#contrasenaNueva'), $('#contrasenaConfirmacion'));
        $mensajeContrasena.addClass('d-none');
    });

    // Paso 1 → 2: solo si la captura es válida.
    $formContrasena.on('submit', function (evento) {
        evento.preventDefault();
        validarConfirmacion($('#contrasenaNueva'), $('#contrasenaConfirmacion'));

        if (!this.checkValidity()) {
            $formContrasena.addClass('was-validated');
            $formContrasena.find(':invalid').first().trigger('focus');
            return;
        }

        mostrarPasoContrasena(2);
    });

    $('#btnContrasenaRegresar').on('click', function () {
        mostrarPasoContrasena(1);
    });

    $btnContrasenaConfirmar.on('click', function () {
        botonOcupado($btnContrasenaConfirmar, true);

        App.api(URL_API, {
            opcion: '6',
            nomina: nominaContrasena,
            contrasena: $('#contrasenaNueva').val(),
            confirmacion: $('#contrasenaConfirmacion').val()
        })
            .done(function (data, respuesta) {
                bootstrap.Modal.getOrCreateInstance($modalContrasena[0]).hide();
                App.exito(respuesta.mensaje || 'Contraseña actualizada.');
                cargarUsuarios();
            })
            .fail(function (mensaje) {
                mostrarMensajeModal($mensajeContrasena, mensaje);
                mostrarPasoContrasena(1);
            })
            .always(function () {
                botonOcupado($btnContrasenaConfirmar, false);
            });
    });

    /* ==================================================================
       Activar / desactivar
       ================================================================== */
    function cambiarEstatus(usuario, estatus, $boton) {
        var desactivar = estatus === 'inactivo';

        App.confirmar({
            titulo: desactivar ? 'Desactivar usuario' : 'Activar usuario',
            mensaje: (desactivar ? '¿Desactivar a ' : '¿Activar a ') + usuario.nombre + ' (' + usuario.nominaUsuario + ')?',
            detalle: desactivar
                ? 'Ya no podrá iniciar sesión en ninguna aplicación. Sus roles y permisos se conservan por si se vuelve a activar.'
                : 'Podrá volver a iniciar sesión con su contraseña actual y recuperará los roles y permisos que tenía.',
            textoConfirmar: desactivar ? 'Sí, desactivar' : 'Sí, activar',
            peligro: desactivar
        }).done(function () {
            $boton.prop('disabled', true);

            App.api(URL_API, { opcion: '5', nomina: usuario.nominaUsuario, estatus: estatus })
                .done(function (data, respuesta) {
                    App.exito(respuesta.mensaje || 'Estatus actualizado.');
                    cargarUsuarios();
                })
                .fail(function (mensaje, respuesta) {
                    App.error(mensaje, respuesta);
                    $boton.prop('disabled', false);
                });
        });
    }

    /* ==================================================================
       Modal: detalle
       ================================================================== */
    var $modalDetalle = $('#modalDetalleUsuario');

    function htmlFila(etiqueta, valorHtml) {
        return '<div class="datos-fila"><dt>' + App.escaparHtml(etiqueta) + '</dt><dd>' + valorHtml + '</dd></div>';
    }

    function pintarDatos(usuario) {
        var html = '<dl class="datos-lista">' +
            htmlFila('Nómina', htmlClave(usuario.nominaUsuario)) +
            htmlFila('Nombre', App.escaparHtml(usuario.nombre)) +
            htmlFila('Estatus', htmlBadgeEstatus(usuario.estatus)) +
            htmlFila('Fecha de alta', usuario.fechaCreacion
                ? App.escaparHtml(App.formatoFecha(usuario.fechaCreacion))
                : '<span class="text-muted-soft">Sin registro</span>') +
            htmlFila('Contraseña', usuario.passwordValida
                ? '<span class="badge badge-activo"><i class="bi bi-shield-lock" aria-hidden="true"></i>Guardada de forma segura</span>'
                : '<span class="badge badge-por-vencer"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i>Sin cifrar: restablécela</span>') +
            '</dl>';

        html += '<div class="dato-bloque mt-3"><span class="dato-bloque-etiqueta">Comentarios</span>' +
            (usuario.comentarios
                ? '<p class="dato-bloque-valor">' + App.escaparHtml(usuario.comentarios) + '</p>'
                : '<p class="text-muted-soft mb-0">Sin comentarios.</p>') +
            '</div>';

        $('#panelDatos').html(html);
    }

    function pintarRoles(roles) {
        $('#detalleTotalRoles').text(roles.length);

        if (roles.length === 0) {
            $('#panelRoles').html(htmlVacio('bi-person-badge', 'Sin roles asignados',
                'Usa el botón "Asignar roles" de abajo.'));
            return;
        }

        var filas = roles.map(function (rol) {
            var estado = !rol.aplicacionActiva
                ? '<span class="badge badge-inactivo"><i class="bi bi-dash-circle" aria-hidden="true"></i>Aplicación inactiva</span>'
                : htmlBadgeEstatus(rol.rolActivo ? 'activo' : 'inactivo');

            return '<tr>' +
                '<td>' + htmlClave(rol.codigoSistema) + '<div class="small text-muted-soft">' + App.escaparHtml(rol.aplicacion) + '</div></td>' +
                '<td><div class="fw-semibold">' + App.escaparHtml(rol.nombreRol) + '</div>' + htmlClave(rol.codigoRol) + '</td>' +
                '<td class="small">' + App.escaparHtml(rol.descripcion || '') + '</td>' +
                '<td>' + estado + '</td>' +
                '</tr>';
        }).join('');

        $('#panelRoles').html(
            '<div class="table-responsive"><table class="table table-hover align-middle">' +
            '<thead><tr><th>Aplicación</th><th>Rol</th><th>Descripción</th><th>Estado</th></tr></thead>' +
            '<tbody>' + filas + '</tbody></table></div>'
        );
    }

    function pintarExcepciones(excepciones) {
        var vigentes = excepciones.filter(function (e) { return e.estadoVigencia !== 'vencido'; }).length;

        $('#detalleTotalExcepciones').text(vigentes);

        if (excepciones.length === 0) {
            $('#panelExcepciones').html(htmlVacio('bi-person-lock', 'Sin permisos individuales',
                'Usa el botón "Permisos individuales" de abajo.'));
            return;
        }

        var filas = excepciones.map(function (e) {
            var otorgado = e.otorgadoPor
                ? App.escaparHtml(e.otorgadoPorNombre || '') + ' ' + htmlClave(e.otorgadoPor)
                : '<span class="text-muted-soft">—</span>';

            return '<tr class="' + (e.estadoVigencia === 'vencido' ? 'usuarios-fila-vencida' : '') + '">' +
                '<td>' + htmlClave(e.codigoSistema + '.' + e.codigoModulo + '.' + e.accion) +
                    (e.descripcion ? '<div class="small text-muted-soft">' + App.escaparHtml(e.descripcion) + '</div>' : '') + '</td>' +
                '<td>' + htmlBadgeTipo(e.tipo) + '</td>' +
                '<td>' + htmlBadgeVigencia(e.estadoVigencia, e.vigenciaHasta) + '</td>' +
                '<td class="small">' + (e.motivo ? App.escaparHtml(e.motivo) : '<span class="text-muted-soft">Sin motivo</span>') + '</td>' +
                '<td class="small">' + otorgado + '<div class="text-muted-soft">' + App.escaparHtml(App.formatoFecha(e.fechaRegistro) + (e.fechaRegistro ? e.fechaRegistro.slice(10) : '')) + '</div></td>' +
                '</tr>';
        }).join('');

        $('#panelExcepciones').html(
            '<div class="table-responsive"><table class="table table-hover align-middle">' +
            '<thead><tr><th>Permiso</th><th>Tipo</th><th>Vigencia</th><th>Motivo</th><th>Otorgado por</th></tr></thead>' +
            '<tbody>' + filas + '</tbody></table></div>'
        );
    }

    function pintarEfectivos(efectivos) {
        var permitidos = efectivos.filter(function (p) { return p.resultado === 'permitido'; }).length;

        $('#detalleTotalEfectivos').text(permitidos);

        if (efectivos.length === 0) {
            $('#panelEfectivos').html(htmlVacio('bi-shield', 'Sin permisos efectivos',
                'Este usuario no puede realizar ninguna acción en ninguna aplicación activa.'));
            return;
        }

        var filas = '';
        var sistemaActual = null;

        efectivos.forEach(function (p) {
            // Una fila de encabezado por aplicación.
            if (p.codigoSistema !== sistemaActual) {
                sistemaActual = p.codigoSistema;
                filas += '<tr class="usuarios-grupo"><th colspan="3">' +
                    '<i class="bi bi-grid-1x2 me-1" aria-hidden="true"></i>' + App.escaparHtml(p.aplicacion) +
                    ' ' + htmlClave(p.codigoSistema) + '</th></tr>';
            }

            var resultado = p.resultado === 'permitido'
                ? '<span class="badge badge-activo"><i class="bi bi-check-circle" aria-hidden="true"></i>Permitido</span>'
                : '<span class="badge badge-denegar"><i class="bi bi-slash-circle" aria-hidden="true"></i>Denegado</span>';

            filas += '<tr>' +
                '<td>' + htmlClave(p.codigoModulo + '.' + p.accion) +
                    '<div class="small text-muted-soft">' + App.escaparHtml(p.modulo) +
                    (p.descripcion ? ' · ' + App.escaparHtml(p.descripcion) : '') + '</div></td>' +
                '<td>' + resultado + '</td>' +
                '<td class="small">' + App.escaparHtml(p.origen) +
                    (p.vigenciaHasta ? '<div class="text-muted-soft">Hasta ' + App.escaparHtml(App.formatoFecha(p.vigenciaHasta)) + '</div>' : '') +
                '</td></tr>';
        });

        $('#panelEfectivos').html(
            '<p class="small text-muted-soft">' +
                '<i class="bi bi-info-circle me-1" aria-hidden="true"></i>' +
                'Resultado de sus roles y permisos individuales vigentes. Una denegación individual siempre gana. ' +
                'No se toman en cuenta roles, módulos ni aplicaciones inactivos, ni permisos vencidos.' +
            '</p>' +
            '<div class="table-responsive"><table class="table align-middle">' +
            '<thead><tr><th>Permiso</th><th>Resultado</th><th>Origen</th></tr></thead>' +
            '<tbody>' + filas + '</tbody></table></div>'
        );
    }

    function abrirDetalle(nomina) {
        $('#detalleContenido').addClass('d-none');
        $('#detalleEstado').html(htmlCargador('Cargando información del usuario…'));
        $('#linkRolesUsuario').attr('href', 'index.php?p=rolesUsuario&nomina=' + encodeURIComponent(nomina));
        $('#linkPermisosUsuario').attr('href', 'index.php?p=permisosUsuario&nomina=' + encodeURIComponent(nomina));
        bootstrap.Tab.getOrCreateInstance(document.getElementById('tabDatos')).show();
        bootstrap.Modal.getOrCreateInstance($modalDetalle[0]).show();

        App.api(URL_API, { opcion: '2', nomina: nomina })
            .done(function (data) {
                var usuario = data.usuario;

                $('#detalleNombre').text(usuario.nombre);
                $('#detalleNomina').text(usuario.nominaUsuario);
                $('#detalleEstatus').html(htmlBadgeEstatus(usuario.estatus));

                pintarDatos(usuario);
                pintarRoles(data.roles || []);
                pintarExcepciones(data.excepciones || []);
                pintarEfectivos(data.efectivos || []);

                $('#detalleEstado').empty();
                $('#detalleContenido').removeClass('d-none');
            })
            .fail(function (mensaje, respuesta) {
                App.error(mensaje, respuesta);
                $('#detalleEstado').html(htmlVacio('bi-cloud-slash', 'No se pudo cargar el detalle', mensaje));
            });
    }

    /* ==================================================================
       Acciones de cada fila. Delegadas en la tabla para que funcionen
       también con las filas que DataTables redibuja o mueve a la vista
       compacta (Responsive) en pantallas chicas.
       ================================================================== */
    $tabla.on('click', '.js-detalle', function () {
        abrirDetalle(String($(this).attr('data-nomina')));
    });

    $tabla.on('click', '.js-editar', function () {
        var usuario = buscarUsuario(String($(this).attr('data-nomina')));

        if (usuario) {
            prepararModalUsuario('edicion', usuario);
            bootstrap.Modal.getOrCreateInstance($modalUsuario[0]).show();
        }
    });

    $tabla.on('click', '.js-contrasena', function () {
        var usuario = buscarUsuario(String($(this).attr('data-nomina')));

        if (usuario) {
            abrirContrasena(usuario);
        }
    });

    $tabla.on('click', '.js-estatus', function () {
        var $boton = $(this);
        var usuario = buscarUsuario(String($boton.attr('data-nomina')));

        if (usuario) {
            cambiarEstatus(usuario, String($boton.attr('data-estatus')), $boton);
        }
    });

    cargarUsuarios();
})();
