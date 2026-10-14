/**
 * assets/js/modulos.js — Catálogo de módulos (pages/modulos.php).
 *
 * Habla con api/operacionesModulos.php:
 *   1 listar · 2 permisos del módulo · 3 crear · 4 modificar · 5 activar/desactivar · 6 eliminar
 *
 * Un módulo se identifica por (codigoSistema, codigoModulo); no hay IDs en el
 * navegador. Acepta ?sistema=XXX en la URL para llegar ya filtrada.
 */
(function () {
    'use strict';

    var URL_API = 'api/operacionesModulos.php';
    var ui = App.ui;

    var $tabla = $('#tablaModulos');
    var $filtro = $('#filtroAplicacion');
    var tabla = null;
    var modulos = [];
    var aplicaciones = [];

    function buscar(sistema, codigo) {
        for (var i = 0; i < modulos.length; i++) {
            if (modulos[i].codigoSistema === sistema && modulos[i].codigoModulo === codigo) {
                return modulos[i];
            }
        }
        return null;
    }

    function atributosClave(m) {
        return 'data-sistema="' + App.escaparHtml(m.codigoSistema) + '" data-modulo="' + App.escaparHtml(m.codigoModulo) + '"';
    }

    /* ==================================================================
       Tabla
       ================================================================== */
    function htmlAcciones(m) {
        var claves = atributosClave(m);
        var html = '<div class="acciones-fila">' +
            '<button type="button" class="btn btn-outline-primary btn-icon js-detalle" ' + claves +
                ' title="Ver permisos del módulo" aria-label="Ver permisos del módulo"><i class="bi bi-key" aria-hidden="true"></i></button>' +
            '<button type="button" class="btn btn-soft btn-icon js-editar" ' + claves +
                ' title="Editar" aria-label="Editar"><i class="bi bi-pencil" aria-hidden="true"></i></button>' +
            (m.activo
                ? '<button type="button" class="btn btn-soft btn-icon text-danger js-activo" ' + claves +
                    ' data-activo="0" title="Desactivar" aria-label="Desactivar"><i class="bi bi-toggle-on" aria-hidden="true"></i></button>'
                : '<button type="button" class="btn btn-soft btn-icon text-success js-activo" ' + claves +
                    ' data-activo="1" title="Activar" aria-label="Activar"><i class="bi bi-toggle-off" aria-hidden="true"></i></button>');

        if (m.permisos === 0) {
            html += '<button type="button" class="btn btn-soft btn-icon text-danger js-eliminar" ' + claves +
                ' title="Eliminar" aria-label="Eliminar"><i class="bi bi-trash" aria-hidden="true"></i></button>';
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
            order: [[0, 'asc'], [3, 'asc']],
            columnDefs: [
                { responsivePriority: 1, targets: 1 },
                { responsivePriority: 2, targets: 6 },
                { responsivePriority: 3, targets: 0 },
                { responsivePriority: 4, targets: 5 }
            ],
            columns: [
                {
                    data: 'codigoSistema',
                    width: '10%',
                    render: function (dato, tipo, fila) {
                        if (tipo === 'filter') {
                            return dato + ' ' + fila.aplicacion;
                        }
                        return tipo === 'display' ? ui.clave(dato) : dato;
                    }
                },
                {
                    data: 'codigoModulo',
                    width: '30%',
                    render: function (dato, tipo, fila) {
                        if (tipo === 'filter') {
                            return dato + ' ' + fila.nombre;
                        }
                        if (tipo !== 'display') {
                            return fila.nombre;
                        }
                        return '<div class="d-flex align-items-center gap-2">' +
                            '<span class="icono-preview" aria-hidden="true"><i class="bi ' + App.escaparHtml(fila.icono || 'bi-square') + '"></i></span>' +
                            '<div><div class="fw-semibold">' + App.escaparHtml(fila.nombre) + '</div>' + ui.clave(dato) + '</div></div>';
                    }
                },
                {
                    data: 'ruta',
                    width: '18%',
                    render: function (dato, tipo) {
                        if (tipo !== 'display') {
                            return dato || '';
                        }
                        return dato ? '<span class="celda-secundaria">' + App.escaparHtml(dato) + '</span>' : '<span class="text-muted-soft">—</span>';
                    }
                },
                { data: 'orden' },
                {
                    data: 'permisos',
                    render: function (dato, tipo, fila) {
                        if (tipo !== 'display') {
                            return dato;
                        }
                        return String(dato) + (fila.tieneVer ? '' :
                            '<div><span class="badge badge-por-vencer" title="Por convención todo módulo tiene la acción ver; sin ella queda inalcanzable">' +
                            '<i class="bi bi-exclamation-triangle" aria-hidden="true"></i>Sin "ver"</span></div>');
                    }
                },
                {
                    data: 'activo',
                    render: function (dato, tipo, fila) {
                        if (tipo !== 'display') {
                            return dato ? 'activo' : 'inactivo';
                        }
                        return ui.badgeActivo(dato) + (fila.aplicacionActiva ? '' :
                            '<div><span class="badge badge-inactivo mt-1"><i class="bi bi-dash-circle" aria-hidden="true"></i>Aplicación inactiva</span></div>');
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

    /* Filtro por aplicación sobre el dato de la fila (no sobre el texto de
       búsqueda de la columna, que también incluye el nombre). Solo actúa
       sobre esta tabla. */
    $.fn.dataTable.ext.search.push(function (ajustes, datos, indice, fila) {
        if (ajustes.nTable !== $tabla[0]) {
            return true;
        }

        var sistema = $filtro.val();
        return !sistema || fila.codigoSistema === sistema;
    });

    function aplicarFiltro() {
        if (tabla !== null) {
            tabla.draw();
        }
    }

    function cargar() {
        return App.api(URL_API, { opcion: '1' })
            .done(function (data) {
                modulos = data.modulos || [];
                aplicaciones = data.aplicaciones || [];

                // Conserva la aplicación elegida (o la que llegó por la URL).
                var seleccion = $filtro.val() || App.parametroUrl('sistema');
                $filtro.html(ui.opcionesAplicaciones(aplicaciones, 'Todas las aplicaciones')).val(seleccion);
                if ($filtro.val() === null) { $filtro.val(''); }

                $('#modulosEstado').empty();
                $tabla.removeClass('d-none');

                if (tabla === null) {
                    inicializarTabla();
                }

                tabla.clear().rows.add(modulos);
                aplicarFiltro();
                tabla.columns.adjust().responsive.recalc();
            })
            .fail(function (mensaje, respuesta) {
                App.error(mensaje, respuesta);

                if (tabla === null) {
                    $('#modulosEstado').html(ui.vacio('bi-cloud-slash', 'No se pudieron cargar los módulos',
                        'Recarga la página para intentarlo de nuevo.'));
                }
            });
    }

    $filtro.on('change', aplicarFiltro);

    /* ==================================================================
       Modal: alta y edición
       ================================================================== */
    var $modal = $('#modalModulo');
    var $form = $('#formModulo');
    var $mensaje = $('#modalModuloMensaje');
    var $btnGuardar = $('#btnGuardarModulo');
    var modo = 'alta';

    function actualizarVistaPrevia() {
        var sistema = $('#moduloAplicacion').val() || '…';
        var codigo = $.trim($('#moduloCodigo').val()) || '…';
        var icono = $.trim($('#moduloIcono').val());

        $('#moduloClavePrevia').text(sistema + '.' + codigo);
        $('#moduloIconoPrevia').attr('class', 'bi ' + (/^bi-[a-z0-9-]+$/.test(icono) ? icono : 'bi-square'));
    }

    function prepararModal(nuevoModo, modulo) {
        modo = nuevoModo;
        var esAlta = modo === 'alta';

        $form[0].reset();
        $form.removeClass('was-validated');
        $mensaje.addClass('d-none').text('');

        $('#moduloAplicacion')
            .html(ui.opcionesAplicaciones(aplicaciones, 'Selecciona…'))
            .prop('disabled', !esAlta);
        $('#modalModuloTitulo').text(esAlta ? 'Nuevo módulo' : 'Editar módulo');
        $('#moduloCodigo').prop('readonly', !esAlta);
        $('#moduloCodigoAyuda').text(esAlta
            ? 'Letras sin acentos, números y guion bajo. No se puede cambiar después.'
            : 'El código no se puede cambiar.');
        $form.find('.js-solo-alta').toggleClass('d-none', !esAlta).find('input, select').prop('disabled', !esAlta);

        if (esAlta) {
            $('#moduloAplicacion').val($filtro.val() || '');
            $('#moduloCrearVer').prop('checked', true);
        } else {
            $('#moduloAplicacion').val(modulo.codigoSistema);
            $('#moduloCodigo').val(modulo.codigoModulo);
            $('#moduloNombre').val(modulo.nombre);
            $('#moduloOrden').val(modulo.orden);
            $('#moduloRuta').val(modulo.ruta || '');
            $('#moduloIcono').val(modulo.icono || '');
        }

        actualizarVistaPrevia();
        bootstrap.Modal.getOrCreateInstance($modal[0]).show();
    }

    $('#btnNuevoModulo').on('click', function () {
        prepararModal('alta');
    });

    $modal.on('shown.bs.modal', function () {
        if (modo !== 'alta') {
            $('#moduloNombre').trigger('focus');
        } else {
            $($('#moduloAplicacion').val() ? '#moduloCodigo' : '#moduloAplicacion').trigger('focus');
        }
    });

    $('#moduloAplicacion, #moduloCodigo, #moduloIcono').on('input change', actualizarVistaPrevia);

    $form.on('input change', function () {
        $mensaje.addClass('d-none');
    });

    $form.on('submit', function (evento) {
        evento.preventDefault();

        $('#moduloCodigo, #moduloNombre, #moduloRuta, #moduloIcono').each(function () {
            this.value = this.value.trim();
        });

        if (!this.checkValidity()) {
            $form.addClass('was-validated');
            $form.find(':invalid').first().trigger('focus');
            return;
        }

        var datos = {
            opcion: modo === 'alta' ? '3' : '4',
            codigoSistema: $('#moduloAplicacion').val(),
            codigoModulo: $('#moduloCodigo').val(),
            nombre: $('#moduloNombre').val(),
            orden: $('#moduloOrden').val(),
            ruta: $('#moduloRuta').val(),
            icono: $('#moduloIcono').val()
        };

        if (modo === 'alta') {
            datos.activo = $('#moduloActivo').val();
            datos.crearVer = $('#moduloCrearVer').is(':checked') ? '1' : '0';
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
    function cambiarActivo(m, activo, $boton) {
        var desactivar = activo === '0';
        var clave = m.codigoSistema + '.' + m.codigoModulo;

        App.confirmar({
            titulo: desactivar ? 'Desactivar módulo' : 'Activar módulo',
            mensaje: (desactivar ? '¿Desactivar ' : '¿Activar ') + m.nombre + ' (' + clave + ')?',
            detalle: desactivar
                ? 'Sus ' + m.permisos + ' permiso(s) dejarán de tener efecto para todos cuando vuelvan a iniciar sesión. No se borra nada.'
                : 'Sus permisos volverán a tener efecto cuando los usuarios inicien sesión.',
            textoConfirmar: desactivar ? 'Sí, desactivar' : 'Sí, activar',
            peligro: desactivar
        }).done(function () {
            $boton.prop('disabled', true);

            App.api(URL_API, { opcion: '5', codigoSistema: m.codigoSistema, codigoModulo: m.codigoModulo, activo: activo })
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

    function eliminar(m, $boton) {
        App.confirmar({
            titulo: 'Eliminar módulo',
            mensaje: '¿Eliminar ' + m.nombre + ' (' + m.codigoSistema + '.' + m.codigoModulo + ')?',
            detalle: 'No tiene permisos, así que se puede eliminar. Si la aplicación arma su menú con esta tabla, ' +
                'el módulo desaparecerá de él; si solo quieres ocultarlo, mejor desactívalo. Esta acción no se puede deshacer.',
            textoConfirmar: 'Sí, eliminar',
            peligro: true
        }).done(function () {
            $boton.prop('disabled', true);

            App.api(URL_API, { opcion: '6', codigoSistema: m.codigoSistema, codigoModulo: m.codigoModulo })
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
       Modal: permisos del módulo
       ================================================================== */
    var $modalDetalle = $('#modalDetalleModulo');

    function abrirDetalle(m) {
        $('#detalleModuloNombre').text(m.nombre);
        $('#detalleModuloClave').text(m.codigoSistema + '.' + m.codigoModulo);
        $('#detalleModuloIcono').attr('class', 'bi ' + (m.icono || 'bi-window-stack'));
        $('#linkAdministrarPermisos').attr('href', 'index.php?p=permisos&sistema=' + encodeURIComponent(m.codigoSistema) +
            '&modulo=' + encodeURIComponent(m.codigoModulo));
        $('#detalleModuloPermisos').html(ui.cargador('Cargando permisos…'));
        bootstrap.Modal.getOrCreateInstance($modalDetalle[0]).show();

        App.api(URL_API, { opcion: '2', codigoSistema: m.codigoSistema, codigoModulo: m.codigoModulo })
            .done(function (data) {
                var permisos = data.permisos || [];

                if (permisos.length === 0) {
                    $('#detalleModuloPermisos').html(ui.vacio('bi-key', 'Sin permisos',
                        'Agrégalos desde Catálogos › Permisos.'));
                    return;
                }

                var filas = permisos.map(function (p) {
                    return '<tr>' +
                        '<td>' + ui.clave(p.accion) + '</td>' +
                        '<td class="small">' + App.escaparHtml(p.descripcion || '') + '</td>' +
                        '<td>' + p.roles + '</td>' +
                        '<td>' + p.excepciones + '</td>' +
                        '</tr>';
                }).join('');

                $('#detalleModuloPermisos').html(
                    '<div class="table-responsive"><table class="table table-hover align-middle">' +
                    '<thead><tr><th>Acción</th><th>Descripción</th><th>Roles</th><th>Excepciones</th></tr></thead>' +
                    '<tbody>' + filas + '</tbody></table></div>'
                );
            })
            .fail(function (mensaje, respuesta) {
                App.error(mensaje, respuesta);
                $('#detalleModuloPermisos').html(ui.vacio('bi-cloud-slash', 'No se pudieron cargar los permisos', mensaje));
            });
    }

    /* ==================================================================
       Acciones de cada fila
       ================================================================== */
    function moduloDelBoton($boton) {
        return buscar($boton.attr('data-sistema'), $boton.attr('data-modulo'));
    }

    $tabla.on('click', '.js-detalle', function () {
        var m = moduloDelBoton($(this));
        if (m) { abrirDetalle(m); }
    });

    $tabla.on('click', '.js-editar', function () {
        var m = moduloDelBoton($(this));
        if (m) { prepararModal('edicion', m); }
    });

    $tabla.on('click', '.js-activo', function () {
        var $boton = $(this);
        var m = moduloDelBoton($boton);
        if (m) { cambiarActivo(m, $boton.attr('data-activo'), $boton); }
    });

    $tabla.on('click', '.js-eliminar', function () {
        var $boton = $(this);
        var m = moduloDelBoton($boton);
        if (m) { eliminar(m, $boton); }
    });

    cargar();
})();
