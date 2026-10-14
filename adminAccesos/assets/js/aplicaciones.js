/**
 * assets/js/aplicaciones.js — Catálogo de aplicaciones (pages/aplicaciones.php).
 *
 * Habla con api/operacionesAplicaciones.php:
 *   1 listar · 2 detalle · 3 crear · 4 modificar · 5 activar/desactivar · 6 eliminar
 *
 * Las aplicaciones se identifican por codigoSistema; no hay IDs en el navegador.
 * Las validaciones de aquí son de comodidad: el servidor vuelve a validarlo todo.
 */
(function () {
    'use strict';

    var URL_API = 'api/operacionesAplicaciones.php';
    var ui = App.ui;

    var $tabla = $('#tablaAplicaciones');
    var tabla = null;
    var aplicaciones = [];
    var codigoPropio = '';

    function buscar(codigo) {
        for (var i = 0; i < aplicaciones.length; i++) {
            if (aplicaciones[i].codigoSistema === codigo) {
                return aplicaciones[i];
            }
        }
        return null;
    }

    function tieneRelaciones(a) {
        return a.modulos > 0 || a.roles > 0;
    }

    /* ==================================================================
       Tabla
       ================================================================== */
    function htmlNumero(valor) {
        return valor > 0 ? String(valor) : '<span class="text-muted-soft">0</span>';
    }

    function htmlAcciones(a) {
        var codigo = App.escaparHtml(a.codigoSistema);
        var esPropia = a.codigoSistema === codigoPropio;
        var html = '<div class="acciones-fila">' +
            '<button type="button" class="btn btn-outline-primary btn-icon js-detalle" data-codigo="' + codigo + '" ' +
                'title="Ver módulos y roles" aria-label="Ver módulos y roles"><i class="bi bi-eye" aria-hidden="true"></i></button>' +
            '<button type="button" class="btn btn-soft btn-icon js-editar" data-codigo="' + codigo + '" ' +
                'title="Editar" aria-label="Editar"><i class="bi bi-pencil" aria-hidden="true"></i></button>';

        if (a.activo) {
            html += esPropia
                ? '<button type="button" class="btn btn-soft btn-icon" disabled title="No se puede desactivar esta aplicación de administración">' +
                    '<i class="bi bi-toggle-on" aria-hidden="true"></i></button>'
                : '<button type="button" class="btn btn-soft btn-icon text-danger js-activo" data-codigo="' + codigo + '" data-activo="0" ' +
                    'title="Desactivar" aria-label="Desactivar"><i class="bi bi-toggle-on" aria-hidden="true"></i></button>';
        } else {
            html += '<button type="button" class="btn btn-soft btn-icon text-success js-activo" data-codigo="' + codigo + '" data-activo="1" ' +
                'title="Activar" aria-label="Activar"><i class="bi bi-toggle-off" aria-hidden="true"></i></button>';
        }

        // Eliminar solo aparece cuando de verdad se puede: sin módulos ni roles.
        if (!tieneRelaciones(a) && !esPropia) {
            html += '<button type="button" class="btn btn-soft btn-icon text-danger js-eliminar" data-codigo="' + codigo + '" ' +
                'title="Eliminar" aria-label="Eliminar"><i class="bi bi-trash" aria-hidden="true"></i></button>';
        }

        return html + '</div>';
    }

    function inicializarTabla() {
        tabla = $tabla.DataTable({
            data: [],
            language: App.TEXTOS_TABLA,
            responsive: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
            order: [[0, 'asc']],
            columnDefs: [
                { responsivePriority: 1, targets: 0 },
                { responsivePriority: 2, targets: 7 },
                { responsivePriority: 3, targets: 1 },
                { responsivePriority: 4, targets: 2 }
            ],
            columns: [
                {
                    data: 'codigoSistema',
                    render: function (dato, tipo) {
                        return tipo === 'display' ? ui.clave(dato) : dato;
                    }
                },
                {
                    data: 'nombre',
                    width: '34%',
                    render: function (dato, tipo, fila) {
                        if (tipo === 'filter') {
                            return dato + ' ' + (fila.descripcion || '');
                        }
                        if (tipo !== 'display') {
                            return dato;
                        }
                        return '<div class="fw-semibold">' + App.escaparHtml(dato) + '</div>' +
                            (fila.descripcion
                                ? '<div class="celda-secundaria celda-recortada" title="' + App.escaparHtml(fila.descripcion) + '">' +
                                    App.escaparHtml(fila.descripcion) + '</div>'
                                : '');
                    }
                },
                {
                    data: 'activo',
                    render: function (dato, tipo) {
                        if (tipo !== 'display') {
                            return dato ? 'activa' : 'inactiva';
                        }
                        return ui.badgeActivo(dato, 'Activa', 'Inactiva');
                    }
                },
                {
                    data: 'modulos',
                    render: function (dato, tipo, fila) {
                        if (tipo !== 'display') {
                            return dato;
                        }
                        return htmlNumero(dato) + (dato > fila.modulosActivos
                            ? '<div class="celda-secundaria">' + fila.modulosActivos + ' activos</div>'
                            : '');
                    }
                },
                { data: 'roles', render: function (d, t) { return t === 'display' ? htmlNumero(d) : d; } },
                { data: 'permisos', render: function (d, t) { return t === 'display' ? htmlNumero(d) : d; } },
                { data: 'usuarios', render: function (d, t) { return t === 'display' ? htmlNumero(d) : d; } },
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

    function cargar() {
        return App.api(URL_API, { opcion: '1' })
            .done(function (data) {
                aplicaciones = data.aplicaciones || [];
                codigoPropio = data.codigoPropio || '';

                $('#aplicacionesEstado').empty();
                $tabla.removeClass('d-none');

                if (tabla === null) {
                    inicializarTabla();
                }

                tabla.clear().rows.add(aplicaciones).draw(false);
                tabla.columns.adjust().responsive.recalc();
            })
            .fail(function (mensaje, respuesta) {
                App.error(mensaje, respuesta);

                if (tabla === null) {
                    $('#aplicacionesEstado').html(ui.vacio('bi-cloud-slash', 'No se pudieron cargar las aplicaciones',
                        'Recarga la página para intentarlo de nuevo.'));
                }
            });
    }

    /* ==================================================================
       Modal: alta y edición
       ================================================================== */
    var $modal = $('#modalAplicacion');
    var $form = $('#formAplicacion');
    var $mensaje = $('#modalAplicacionMensaje');
    var $btnGuardar = $('#btnGuardarAplicacion');
    var modo = 'alta';

    function prepararModal(nuevoModo, aplicacion) {
        modo = nuevoModo;
        var esAlta = modo === 'alta';

        $form[0].reset();
        $form.removeClass('was-validated');
        $mensaje.addClass('d-none').text('');

        $('#modalAplicacionTitulo').text(esAlta ? 'Nueva aplicación' : 'Editar aplicación');
        $('#aplicacionCodigo').prop('readonly', !esAlta);
        $('#aplicacionCodigoAyuda').text(esAlta
            ? 'MAYÚSCULAS, números y guion bajo. No se puede cambiar después: el código de la aplicación lo usa tal cual.'
            : 'El código no se puede cambiar.');
        $form.find('.js-solo-alta').toggleClass('d-none', !esAlta).find('select').prop('disabled', !esAlta);

        if (!esAlta && aplicacion) {
            $('#aplicacionCodigo').val(aplicacion.codigoSistema);
            $('#aplicacionNombre').val(aplicacion.nombre);
            $('#aplicacionDescripcion').val(aplicacion.descripcion || '');
        }

        bootstrap.Modal.getOrCreateInstance($modal[0]).show();
    }

    $('#btnNuevaAplicacion').on('click', function () {
        prepararModal('alta');
    });

    $modal.on('shown.bs.modal', function () {
        $(modo === 'alta' ? '#aplicacionCodigo' : '#aplicacionNombre').trigger('focus');
    });

    // El código se escribe en mayúsculas sin que el usuario tenga que acordarse.
    $('#aplicacionCodigo').on('input', function () {
        var posicion = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(posicion, posicion);
    });

    $form.on('input', function () {
        $mensaje.addClass('d-none');
    });

    $form.on('submit', function (evento) {
        evento.preventDefault();

        $('#aplicacionCodigo, #aplicacionNombre').each(function () {
            this.value = this.value.trim();
        });

        if (!this.checkValidity()) {
            $form.addClass('was-validated');
            $form.find(':invalid').first().trigger('focus');
            return;
        }

        var datos = {
            opcion: modo === 'alta' ? '3' : '4',
            codigoSistema: $('#aplicacionCodigo').val(),
            nombre: $('#aplicacionNombre').val(),
            descripcion: $('#aplicacionDescripcion').val()
        };

        if (modo === 'alta') {
            datos.activo = $('#aplicacionActivo').val();
        }

        ui.botonOcupado($btnGuardar, true);

        App.api(URL_API, datos)
            .done(function (data, respuesta) {
                bootstrap.Modal.getOrCreateInstance($modal[0]).hide();
                App.exito(respuesta.mensaje || 'Cambios guardados.');
                cargar();
            })
            .fail(function (mensaje) {
                $mensaje.text(mensaje).removeClass('d-none');
            })
            .always(function () {
                ui.botonOcupado($btnGuardar, false);
            });
    });

    /* ==================================================================
       Activar / desactivar y eliminar
       ================================================================== */
    function cambiarActivo(a, activo, $boton) {
        var desactivar = activo === '0';

        App.confirmar({
            titulo: desactivar ? 'Desactivar aplicación' : 'Activar aplicación',
            mensaje: (desactivar ? '¿Desactivar ' : '¿Activar ') + a.nombre + ' (' + a.codigoSistema + ')?',
            detalle: desactivar
                ? 'Sus roles y permisos dejarán de tener efecto para todos los usuarios (' + a.usuarios +
                    ' relacionados) cuando vuelvan a iniciar sesión. No se borra nada.'
                : 'Sus roles y permisos activos volverán a tener efecto cuando los usuarios inicien sesión.',
            textoConfirmar: desactivar ? 'Sí, desactivar' : 'Sí, activar',
            peligro: desactivar
        }).done(function () {
            $boton.prop('disabled', true);

            App.api(URL_API, { opcion: '5', codigoSistema: a.codigoSistema, activo: activo })
                .done(function (data, respuesta) {
                    App.exito(respuesta.mensaje);
                    cargar();
                })
                .fail(function (mensaje, respuesta) {
                    App.error(mensaje, respuesta);
                    $boton.prop('disabled', false);
                });
        });
    }

    function eliminar(a, $boton) {
        App.confirmar({
            titulo: 'Eliminar aplicación',
            mensaje: '¿Eliminar ' + a.nombre + ' (' + a.codigoSistema + ')?',
            detalle: 'No tiene módulos ni roles, así que se puede eliminar. Esta acción no se puede deshacer.',
            textoConfirmar: 'Sí, eliminar',
            peligro: true
        }).done(function () {
            $boton.prop('disabled', true);

            App.api(URL_API, { opcion: '6', codigoSistema: a.codigoSistema })
                .done(function (data, respuesta) {
                    App.exito(respuesta.mensaje);
                    cargar();
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
    var $modalDetalle = $('#modalDetalleAplicacion');

    function pintarModulos(modulos, codigoSistema) {
        $('#detalleAplicacionTotalModulos').text(modulos.length);

        if (modulos.length === 0) {
            $('#panelAplicacionModulos').html(ui.vacio('bi-window-stack', 'Sin módulos',
                'Agrégalos desde Catálogos › Módulos.'));
            return;
        }

        var filas = modulos.map(function (m) {
            return '<tr>' +
                '<td class="text-center"><i class="bi ' + App.escaparHtml(m.icono || 'bi-square') + ' fs-5 text-primary" aria-hidden="true"></i></td>' +
                '<td><div class="fw-semibold">' + App.escaparHtml(m.nombre) + '</div>' + ui.clave(m.codigoModulo) + '</td>' +
                '<td class="small">' + (m.ruta ? App.escaparHtml(m.ruta) : '<span class="text-muted-soft">—</span>') + '</td>' +
                '<td>' + m.orden + '</td>' +
                '<td>' + m.permisos +
                    (m.tieneVer ? '' : '<div><span class="badge badge-por-vencer" title="Por convención todo módulo tiene la acción ver">' +
                        '<i class="bi bi-exclamation-triangle" aria-hidden="true"></i>Sin "ver"</span></div>') + '</td>' +
                '<td>' + ui.badgeActivo(m.activo) + '</td>' +
                '<td class="text-end"><a class="btn btn-soft btn-sm" href="index.php?p=permisos&sistema=' +
                    encodeURIComponent(codigoSistema) + '&modulo=' + encodeURIComponent(m.codigoModulo) + '">' +
                    '<i class="bi bi-key" aria-hidden="true"></i>Permisos</a></td>' +
                '</tr>';
        }).join('');

        $('#panelAplicacionModulos').html(
            '<div class="table-responsive"><table class="table table-hover align-middle">' +
            '<thead><tr><th>Icono</th><th>Módulo</th><th>Ruta</th><th>Orden</th><th>Permisos</th><th>Estado</th><th></th></tr></thead>' +
            '<tbody>' + filas + '</tbody></table></div>'
        );
    }

    function pintarRoles(roles) {
        $('#detalleAplicacionTotalRoles').text(roles.length);

        if (roles.length === 0) {
            $('#panelAplicacionRoles').html(ui.vacio('bi-person-badge', 'Sin roles',
                'Agrégalos desde Catálogos › Roles.'));
            return;
        }

        var filas = roles.map(function (r) {
            return '<tr>' +
                '<td><div class="fw-semibold">' + App.escaparHtml(r.nombreRol) + '</div>' + ui.clave(r.codigoRol) + '</td>' +
                '<td class="small">' + App.escaparHtml(r.descripcion || '') + '</td>' +
                '<td>' + r.permisos + '</td>' +
                '<td>' + r.usuarios + '</td>' +
                '<td>' + ui.badgeActivo(r.activo) + '</td>' +
                '</tr>';
        }).join('');

        $('#panelAplicacionRoles').html(
            '<div class="table-responsive"><table class="table table-hover align-middle">' +
            '<thead><tr><th>Rol</th><th>Descripción</th><th>Permisos</th><th>Usuarios</th><th>Estado</th></tr></thead>' +
            '<tbody>' + filas + '</tbody></table></div>'
        );
    }

    function abrirDetalle(codigo) {
        $('#detalleAplicacionContenido').addClass('d-none');
        $('#detalleAplicacionEstado').html(ui.cargador('Cargando módulos y roles…'));
        $('#linkAdministrarModulos').attr('href', 'index.php?p=modulos&sistema=' + encodeURIComponent(codigo));
        $('#linkAdministrarRoles').attr('href', 'index.php?p=roles&sistema=' + encodeURIComponent(codigo));
        bootstrap.Tab.getOrCreateInstance(document.getElementById('tabAplicacionModulos')).show();
        bootstrap.Modal.getOrCreateInstance($modalDetalle[0]).show();

        App.api(URL_API, { opcion: '2', codigoSistema: codigo })
            .done(function (data) {
                var a = data.aplicacion;

                $('#detalleAplicacionNombre').text(a.nombre);
                $('#detalleAplicacionCodigo').text(a.codigoSistema);
                $('#detalleAplicacionDescripcion').text(a.descripcion || '');
                $('#detalleAplicacionEstatus').html(ui.badgeActivo(a.activo, 'Activa', 'Inactiva'));

                pintarModulos(data.modulos || [], a.codigoSistema);
                pintarRoles(data.roles || []);

                $('#detalleAplicacionEstado').empty();
                $('#detalleAplicacionContenido').removeClass('d-none');
            })
            .fail(function (mensaje, respuesta) {
                App.error(mensaje, respuesta);
                $('#detalleAplicacionEstado').html(ui.vacio('bi-cloud-slash', 'No se pudo cargar el detalle', mensaje));
            });
    }

    /* ==================================================================
       Acciones de cada fila (delegadas: funcionan también en la vista
       compacta de Responsive).
       ================================================================== */
    $tabla.on('click', '.js-detalle', function () {
        abrirDetalle($(this).attr('data-codigo'));
    });

    $tabla.on('click', '.js-editar', function () {
        var a = buscar($(this).attr('data-codigo'));
        if (a) { prepararModal('edicion', a); }
    });

    $tabla.on('click', '.js-activo', function () {
        var $boton = $(this);
        var a = buscar($boton.attr('data-codigo'));
        if (a) { cambiarActivo(a, $boton.attr('data-activo'), $boton); }
    });

    $tabla.on('click', '.js-eliminar', function () {
        var $boton = $(this);
        var a = buscar($boton.attr('data-codigo'));
        if (a) { eliminar(a, $boton); }
    });

    cargar();
})();
