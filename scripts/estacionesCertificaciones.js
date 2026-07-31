$(function () {
    'use strict';

    var API_URL = './api/estaciones_certificaciones.php';

    var tabla = null;
    var lineas = [];
    var certificaciones = [];
    var estacionesSeleccionadas = new Set();
    var accionPendiente = null;

    var modalEditar = new bootstrap.Modal(
        document.getElementById('modalEditarEstacion')
    );

    var modalConfirmar = new bootstrap.Modal(
        document.getElementById('modalConfirmarMasivo')
    );

    var toastNotificacion = new bootstrap.Toast(
        document.getElementById('toastNotificacion'),
        { delay: 3200 }
    );

    cargarCatalogos();
    iniciarTabla();

    function cargarCatalogos() {
        $.ajax({
            url: API_URL,
            method: 'GET',
            dataType: 'json',
            cache: false,
            data: { accion: 'catalogos' }
        })
            .done(function (respuesta) {
                if (!respuesta || respuesta.success !== true) {
                    mostrarToast(
                        'No se cargaron los catálogos',
                        respuesta && respuesta.message
                            ? respuesta.message
                            : 'La respuesta del servidor no es válida.',
                        'error'
                    );
                    return;
                }

                lineas = Array.isArray(respuesta.data.lineas)
                    ? respuesta.data.lineas
                    : [];

                certificaciones = Array.isArray(respuesta.data.certificaciones)
                    ? respuesta.data.certificaciones
                    : [];

                llenarLineas();
                llenarCertificaciones();
            })
            .fail(function (xhr) {
                mostrarToast(
                    'Error de conexión',
                    obtenerMensajeError(
                        xhr,
                        'No fue posible cargar las líneas y certificaciones.'
                    ),
                    'error'
                );
            });
    }

    function llenarLineas() {
        var $select = $('#selectLinea');
        $select.find('option:not(:first)').remove();

        lineas.forEach(function (linea) {
            /*
             * codigo_linea es NVARCHAR(20), por lo que debe conservarse
             * como texto. Puede contener valores como L01, ASSY-1, etc.
             */
            var codigoLinea = String(
                linea.codigo_linea == null ? '' : linea.codigo_linea
            ).trim();

            if (!codigoLinea) {
                console.warn('Línea ignorada por código vacío:', linea);
                return;
            }

            $('<option>', {
                value: codigoLinea,
                text: linea.nombre_linea
            }).appendTo($select);
        });
    }

    function llenarCertificaciones() {
        var selects = $('#certificacionMasiva, #certificacionEditar');
        selects.find('option:not(:first)').remove();

        certificaciones.forEach(function (certificacion) {
            var texto = certificacion.nombre_certificacion || 'Sin nombre';

            selects.each(function () {
                $('<option>', {
                    value: certificacion.idCR,
                    text: texto
                }).appendTo(this);
            });
        });
    }

    function iniciarTabla() {
        tabla = $('#tablaEstaciones').DataTable({
            data: [],
            processing: false,
            serverSide: false,
            searching: true,
            pageLength: 10,
            lengthChange: true,
            lengthMenu: [
                            [8, 10, 15, 50],
                            [8, 10, 15, 50]
                        ],
            autoWidth: false,
            deferRender: true,
            scrollX: true,
            scrollY: '52vh',
            scrollCollapse: true,

            columns: [
                {
                    data: 'id_estacion',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function (data, type) {
                        if (type !== 'display') {
                            return data;
                        }

                        var checked = estacionesSeleccionadas.has(Number(data))
                            ? ' checked'
                            : '';

                        return '<input class="form-check-input table-check seleccionar-estacion" ' +
                            'type="checkbox" value="' + Number(data) + '"' + checked +
                            ' aria-label="Seleccionar estación">';
                    }
                },
                {
                    data: 'nombre_estacion',
                    name: 'nombre_estacion',
                    render: function (data, type) {
                        var nombre = data || '';
                        if (type !== 'display') {
                            return nombre;
                        }
                        return '<span class="station-name">' +
                            escapeHtml(nombre) + '</span>';
                    }
                },
                {
                    data: 'descripcion',
                    name: 'descripcion',
                    defaultContent: '',
                    render: function (data, type) {
                        var descripcion = (data || '').trim();
                        if (type !== 'display') {
                            return descripcion;
                        }
                        if (!descripcion) {
                            return '<span class="empty-text">Sin descripción</span>';
                        }
                        return '<span class="description-cell" title="' +
                            escapeAttribute(descripcion) + '">' +
                            escapeHtml(descripcion) + '</span>';
                    }
                },
                {
                    data: 'requiere_certificacion',
                    name: 'requiere_certificacion',
                    render: function (data, type) {
                        var requiere = Number(data) === 1;
                        if (type !== 'display') {
                            return requiere ? 'Certificada' : 'No certificada';
                        }
                        return requiere
                            ? '<span class="requirement-badge yes"><i class="bi bi-patch-check"></i> Certificada</span>'
                            : '<span class="requirement-badge no"><i class="bi bi-circle"></i> No certificada</span>';
                    }
                },
                {
                    data: null,
                    name: 'id_certificacion',
                    render: function (data, type, row) {
                        var idCertificacion = Number(row.id_certificacion) || 0;
                        var nombre = row.nombre_certificacion || '';

                        if (type !== 'display') {
                            return nombre;
                        }

                        if (!idCertificacion) {
                            return '<span class="certification-badge pending">' +
                                '<i class="bi bi-exclamation-circle"></i> Curso sin asignar</span>';
                        }

                        return '<span class="certification-badge" title="' +
                            escapeAttribute(nombre) + '">' +
                            '<i class="bi bi-patch-check"></i> ' +
                            escapeHtml(nombre) + '</span>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: function () {
                        return '<div class="action-group">' +
                            '<button type="button" class="btn btn-action btn-action-edit btn-editar-estacion" ' +
                            'title="Editar curso" aria-label="Editar curso">' +
                            '<i class="bi bi-pencil" aria-hidden="true"></i>' +
                            '</button>' +
                            '</div>';
                    }
                }
            ],

            order: [[1, 'asc']],

            responsive: {
                details: {
                    type: 'inline',
                    target: 'tr'
                }
            },

            //dom: 'rt<"d-flex flex-column flex-md-row align-items-center justify-content-between gap-2"ip>',

            language: {
                processing: '<div class="d-flex align-items-center gap-2">' +
                    '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>' +
                    '<span>Cargando estaciones...</span></div>',
                emptyTable: 'Selecciona una línea para consultar sus estaciones.',
                zeroRecords: 'No se encontraron estaciones con esos criterios.',
                search: 'Buscar estaciones:',
                searchPlaceholder: 'Estación, descripción o certificación',
                info: 'Mostrando _START_–_END_ de _TOTAL_ estaciones',
                infoEmpty: 'Mostrando 0 estaciones',
                infoFiltered: '— filtrado de _MAX_ estaciones cargadas',
                paginate: {
                    first: 'Primera',
                    last: 'Última',
                    next: '<i class="bi bi-chevron-right" aria-hidden="true"></i>',
                    previous: '<i class="bi bi-chevron-left" aria-hidden="true"></i>'
                }
            },

            drawCallback: function () {
                var api = this.api();
                sincronizarChecksPagina(api);
                actualizarContadorVisible(api);
            }
        });

        configurarBusquedaDataTable(false);
    }

    function cargarEstaciones(resetearPagina) {
        var codigoLinea = obtenerCodigoLineaSeleccionado();

        if (!tabla) {
            return;
        }

        limpiarBusquedaDataTable();

        if (!codigoLinea) {
            tabla.clear().draw();
            actualizarResumen({});
            return;
        }

        $.ajax({
            url: API_URL,
            method: 'GET',
            dataType: 'json',
            cache: false,
            data: {
                accion: 'listar',
                codigo_linea: codigoLinea,
                estado: $('#filtroEstado').val() || 'todos'
            }
        })
            .done(function (respuesta) {
                if (!respuesta || respuesta.success !== true) {
                    tabla.clear().draw();
                    actualizarResumen({});
                    mostrarToast(
                        'No se pudieron cargar las estaciones',
                        respuesta && respuesta.message
                            ? respuesta.message
                            : 'La API devolvió una respuesta no válida.',
                        'error'
                    );
                    return;
                }

                actualizarResumen(respuesta.summary || {});

                tabla.clear();
                tabla.rows.add(
                    Array.isArray(respuesta.data) ? respuesta.data : []
                );

                if (resetearPagina) {
                    tabla.page('first');
                }

                tabla.draw();
            })
            .fail(function (xhr) {
                console.error(
                    'Error al cargar estaciones:',
                    xhr.status,
                    xhr.responseText
                );

                tabla.clear().draw();
                actualizarResumen({});
                mostrarToast(
                    'Error al cargar las estaciones',
                    obtenerMensajeError(
                        xhr,
                        'No fue posible consultar las estaciones.'
                    ),
                    'error'
                );
            })
            .always(function () {
                actualizarContadorVisible();
            });
    }

    function configurarBusquedaDataTable(habilitado) {
        var $filtro = $('#tablaEstaciones_filter');
        var $input = $filtro.find('input');

        $input
            .attr({
                placeholder: 'Estación, descripción o certificación',
                'aria-label': 'Buscar estaciones en la tabla'
            })
            .prop('disabled', !habilitado);
    }

    function limpiarBusquedaDataTable() {
        if (!tabla) {
            return;
        }

        tabla.search('');
        $('#tablaEstaciones_filter input').val('');
    }

    $('#selectLinea').on('change', function () {
        limpiarSeleccion();
        limpiarFiltros(false);

        var codigoLinea = obtenerCodigoLineaSeleccionado();
        var linea = lineas.find(function (item) {
            return String(
                item.codigo_linea == null ? '' : item.codigo_linea
            ).trim() === codigoLinea;
        });

        var habilitado = Boolean(codigoLinea);
        $('#filtroEstado, #btnLimpiarFiltros')
            .prop('disabled', !habilitado);
        configurarBusquedaDataTable(habilitado);

        if (linea) {
            $('#informacionLinea').html(
                '<span class="info-icon"><i class="bi bi-diagram-3" aria-hidden="true"></i></span>' +
                '<div><strong>' + escapeHtml(linea.nombre_linea) + '</strong>' +
                '<small>' + escapeHtml(linea.descripcion || 'Línea de producción seleccionada.') + '</small></div>'
            );
        } else {
            $('#informacionLinea').html(
                '<span class="info-icon"><i class="bi bi-info-circle" aria-hidden="true"></i></span>' +
                '<div><strong>Ninguna línea seleccionada</strong>' +
                '<small>Las estaciones se mostrarán después de seleccionar una línea.</small></div>'
            );
            actualizarResumen({});
        }

        cargarEstaciones(true);
    });

    $('#filtroEstado').on('change', function () {
        limpiarSeleccion();
        cargarEstaciones(true);
    });

    $('#btnLimpiarFiltros').on('click', function () {
        limpiarFiltros(true);
    });

    function limpiarFiltros(recargar) {
        $('#filtroEstado').val('todos');
        limpiarBusquedaDataTable();

        if (recargar) {
            limpiarSeleccion();
            cargarEstaciones(true);
        }
    }

    $('#tablaEstaciones tbody').on('change', '.seleccionar-estacion', function () {
        var id = Number($(this).val());

        if (this.checked) {
            estacionesSeleccionadas.add(id);
        } else {
            estacionesSeleccionadas.delete(id);
        }

        actualizarPanelMasivo();
        actualizarCheckCabecera();
    });

    $('#seleccionarPagina').on('change', function () {
        var seleccionar = this.checked;
        var filas = tabla.rows({ page: 'current' }).data().toArray();

        filas.forEach(function (fila) {
            var id = Number(fila.id_estacion);
            if (seleccionar) {
                estacionesSeleccionadas.add(id);
            } else {
                estacionesSeleccionadas.delete(id);
            }
        });

        sincronizarChecksPagina();
        actualizarPanelMasivo();
    });

    $('#tablaEstaciones tbody').on('click', '.btn-editar-estacion', function (event) {
        event.stopPropagation();
        var registro = obtenerRegistroDesdeBoton(this);
        if (!registro) {
            return;
        }

        $('#idEstacionEditar').val(registro.id_estacion);
        $('#nombreEstacionEditar').text(registro.nombre_estacion);

        $('#certificacionEditar').val(registro.id_certificacion || '');
        modalEditar.show();
    });

    $('#formEditarEstacion').on('submit', function (event) {
        event.preventDefault();

        var idEstacion = Number($('#idEstacionEditar').val());
        var idCertificacion = Number($('#certificacionEditar').val()) || null;

        guardarCambios(
            idCertificacion ? 'asignar' : 'quitar',
            [idEstacion],
            idCertificacion,
            $('#btnGuardarEstacion'),
            function () {
                modalEditar.hide();
            }
        );
    });

    $('#btnAsignarMasivo').on('click', function () {
        var idCertificacion = Number($('#certificacionMasiva').val()) || null;

        if (estacionesSeleccionadas.size === 0) {
            mostrarToast('Sin selección', 'Selecciona al menos una estación.', 'error');
            return;
        }

        if (!idCertificacion) {
            mostrarToast(
                'Selecciona una certificación',
                'Elige el curso que se aplicará a las estaciones seleccionadas.',
                'error'
            );
            $('#certificacionMasiva').focus();
            return;
        }

        accionPendiente = {
            accion: 'asignar',
            id_certificacion: idCertificacion
        };

        var certificacion = certificaciones.find(function (item) {
            return Number(item.idCR) === idCertificacion;
        });

        $('#modalConfirmarMasivoLabel').text('¿Asignar certificación?');
        $('#mensajeConfirmacionMasiva').text(
            'Se asignará "' +
            (certificacion && certificacion.nombre_certificacion
                ? certificacion.nombre_certificacion
                : 'la certificación seleccionada') +
            '" a ' + estacionesSeleccionadas.size +
            (estacionesSeleccionadas.size === 1 ? ' estación.' : ' estaciones.')
        );

        modalConfirmar.show();
    });

    $('#btnQuitarMasivo').on('click', function () {
        if (estacionesSeleccionadas.size === 0) {
            mostrarToast('Sin selección', 'Selecciona al menos una estación.', 'error');
            return;
        }

        accionPendiente = {
            accion: 'quitar',
            id_certificacion: null
        };

        $('#modalConfirmarMasivoLabel').text('¿Quitar el curso?');
        $('#mensajeConfirmacionMasiva').text(
            'Se quitará el curso asignado de ' +
            estacionesSeleccionadas.size +
            (estacionesSeleccionadas.size === 1 ? ' estación.' : ' estaciones.')
        );

        modalConfirmar.show();
    });

    $('#btnConfirmarMasivo').on('click', function () {
        if (!accionPendiente) {
            return;
        }

        guardarCambios(
            accionPendiente.accion,
            Array.from(estacionesSeleccionadas),
            accionPendiente.id_certificacion,
            $('#btnConfirmarMasivo'),
            function () {
                modalConfirmar.hide();
                accionPendiente = null;
            }
        );
    });

    function guardarCambios(accion, ids, idCertificacion, $boton, alCompletar) {
        var codigoLinea = obtenerCodigoLineaSeleccionado();

        if (!codigoLinea || !ids.length) {
            mostrarToast('Datos incompletos', 'Selecciona una línea y al menos una estación.', 'error');
            return;
        }

        cambiarEstadoBoton($boton, true, 'Guardando...');

        $.ajax({
            url: API_URL,
            method: 'PUT',
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            data: JSON.stringify({
                accion: accion,
                codigo_linea: codigoLinea,
                ids_estacion: ids,
                id_certificacion: idCertificacion
            })
        })
            .done(function (respuesta) {
                if (typeof alCompletar === 'function') {
                    alCompletar();
                }

                mostrarToast(
                    'Cambios guardados',
                    respuesta.message,
                    'success'
                );

                limpiarSeleccion();
                cargarEstaciones(false);
            })
            .fail(function (xhr) {
                mostrarToast(
                    'No se guardaron los cambios',
                    obtenerMensajeError(xhr, 'No fue posible actualizar las estaciones.'),
                    'error'
                );
            })
            .always(function () {
                cambiarEstadoBoton($boton, false);
            });
    }

    function obtenerCodigoLineaSeleccionado() {
        var valor = $('#selectLinea').val();

        if (Array.isArray(valor)) {
            valor = valor.length ? valor[0] : '';
        }

        var codigoLinea = String(
            valor == null ? '' : valor
        ).trim();

        if (!codigoLinea || codigoLinea.length > 20) {
            return null;
        }

        return codigoLinea;
    }

    function obtenerRegistroDesdeBoton(boton) {
        var $fila = $(boton).closest('tr');
        if ($fila.hasClass('child')) {
            $fila = $fila.prev();
        }
        return tabla.row($fila).data();
    }

    function sincronizarChecksPagina(api) {
        $('#tablaEstaciones tbody .seleccionar-estacion').each(function () {
            this.checked = estacionesSeleccionadas.has(Number(this.value));
        });
        actualizarCheckCabecera(api);
        actualizarPanelMasivo();
    }

    function actualizarCheckCabecera(api) {
        var dataTable = api || tabla;
        var filas = dataTable.rows({ page: 'current' }).data().toArray();
        var totalPagina = filas.length;
        var seleccionadasPagina = filas.filter(function (fila) {
            return estacionesSeleccionadas.has(Number(fila.id_estacion));
        }).length;

        var checkbox = document.getElementById('seleccionarPagina');
        checkbox.checked = totalPagina > 0 && seleccionadasPagina === totalPagina;
        checkbox.indeterminate = seleccionadasPagina > 0 && seleccionadasPagina < totalPagina;
        checkbox.disabled = totalPagina === 0;
    }

    function actualizarPanelMasivo() {
        var total = estacionesSeleccionadas.size;
        $('#panelAccionesMasivas').toggleClass('is-hidden', total === 0);
        $('#textoSeleccionados').text(
            total + (total === 1 ? ' estación seleccionada' : ' estaciones seleccionadas')
        );
    }

    function limpiarSeleccion() {
        estacionesSeleccionadas.clear();
        $('#seleccionarPagina').prop({ checked: false, indeterminate: false });
        actualizarPanelMasivo();
    }

    function actualizarResumen(resumen) {
        animarNumero($('#totalEstaciones'), Number(resumen.total_estaciones) || 0);
        animarNumero($('#totalConCurso'), Number(resumen.con_curso) || 0);
        animarNumero($('#totalSinCurso'), Number(resumen.sin_curso) || 0);
    }

    function actualizarContadorVisible(api) {
        var dataTable = api || tabla;
        if (!dataTable) {
            return;
        }
        var total = dataTable.page.info().recordsDisplay || 0;
        $('#contadorVisible').text(
            total + (total === 1 ? ' registro' : ' registros')
        );
    }

    function animarNumero($elemento, valorFinal) {
        var valorInicial = Number($elemento.text()) || 0;
        if (valorInicial === valorFinal) {
            $elemento.text(valorFinal);
            return;
        }

        var duracion = 220;
        var inicio = performance.now();

        function paso(tiempoActual) {
            var progreso = Math.min((tiempoActual - inicio) / duracion, 1);
            var valor = Math.round(
                valorInicial + (valorFinal - valorInicial) * progreso
            );
            $elemento.text(valor);
            if (progreso < 1) {
                requestAnimationFrame(paso);
            }
        }

        requestAnimationFrame(paso);
    }

    function cambiarEstadoBoton($boton, cargando, texto) {
        if (cargando) {
            $boton.data('contenido-original', $boton.html());
            $boton.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> ' +
                '<span>' + escapeHtml(texto || 'Procesando...') + '</span>'
            );
            return;
        }

        var original = $boton.data('contenido-original');
        if (original) {
            $boton.html(original);
        }
        $boton.prop('disabled', false);
    }

    function obtenerMensajeError(xhr, predeterminado) {
        if (xhr.responseJSON && xhr.responseJSON.message) {
            return xhr.responseJSON.message;
        }
        if (xhr.status === 0) {
            return 'No fue posible comunicarse con el servidor.';
        }
        return predeterminado;
    }

    function mostrarToast(titulo, mensaje, tipo) {
        var $toast = $('#toastNotificacion');
        var $icono = $toast.find('.toast-icon i');
        var esError = tipo === 'error';

        $toast.toggleClass('toast-error', esError);
        $icono
            .toggleClass('bi-check-lg', !esError)
            .toggleClass('bi-exclamation-lg', esError);

        $('#toastTitulo').text(titulo);
        $('#toastMensaje').text(mensaje);
        toastNotificacion.show();
    }

    function escapeHtml(valor) {
        return $('<div>').text(valor == null ? '' : valor).html();
    }

    function escapeAttribute(valor) {
        return escapeHtml(valor).replace(/`/g, '&#96;');
    }
});