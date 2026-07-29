$(function () {
    'use strict';

    var API_URL = './api/ilu_personal.php';
    var SEARCH_DELAY = 450;
    var DEBUG = new URLSearchParams(window.location.search).get('debug') === '1';

    var tabla = null;
    var lineas = [];
    var certificaciones = [];
    var personalSeleccionado = new Set();
    var accionPendiente = null;

    var modalCursosPersona = new bootstrap.Modal(
        document.getElementById('modalCursosPersona')
    );

    var modalConfirmar = new bootstrap.Modal(
        document.getElementById('modalConfirmarMasivo')
    );

    var toastNotificacion = new bootstrap.Toast(
        document.getElementById('toastNotificacion'),
        { delay: 3600 }
    );

    cargarCatalogos();
    iniciarTabla();

    function cargarCatalogos() {
        var datosCatalogos = { accion: 'catalogos' };

        if (DEBUG) {
            datosCatalogos.debug = '1';
        }

        $.ajax({
            url: API_URL,
            method: 'GET',
            dataType: 'json',
            cache: false,
            data: datosCatalogos
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

                if (DEBUG) {
                    console.info('[ILU] Catálogos cargados:', {
                        lineas: lineas.length,
                        certificaciones: certificaciones.length,
                        data: respuesta.data
                    });
                }

                llenarLineas();
                llenarCertificaciones();
                seleccionarLineaInicialYCargar();
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
            var codigoLinea = String(
                linea.codigo_linea == null ? '' : linea.codigo_linea
            ).trim();

            if (!codigoLinea) {
                console.warn('Línea ignorada por código vacío:', linea);
                return;
            }

            $('<option>', {
                value: codigoLinea,
                text: linea.nombre_linea || codigoLinea
            }).appendTo($select);
        });
    }

    function seleccionarLineaInicialYCargar() {
        var codigoActual = obtenerCodigoLineaSeleccionado();

        if (!codigoActual) {
            var lineaInicial = obtenerLineaInicial();

            if (lineaInicial) {
                $('#selectLinea')
                    .val(String(lineaInicial.codigo_linea).trim())
                    .trigger('change');
                return;
            }
        }

        if (codigoActual) {
            $('#selectLinea').trigger('change');
        }
    }

    function obtenerLineaInicial() {
        if (!Array.isArray(lineas) || lineas.length === 0) {
            return null;
        }

        var conTurnoUno = lineas.find(function (linea) {
            return Number(linea.total_turno_1 || 0) > 0;
        });

        if (conTurnoUno) {
            return conTurnoUno;
        }

        return lineas.find(function (linea) {
            return String(linea.codigo_linea == null ? '' : linea.codigo_linea).trim() !== '';
        }) || null;
    }

    function llenarCertificaciones() {
        var $select = $('#certificacionMasiva');
        $select.find('option:not(:first)').remove();

        certificaciones.forEach(function (certificacion) {
            var idE = Number(certificacion.idE || certificacion.idCR);

            if (!Number.isInteger(idE) || idE <= 0) {
                console.warn('Certificación ignorada por id inválido:', certificacion);
                return;
            }

            var codigo = certificacion.codigo_certificacion || ('ID ' + idE);
            var nombre = certificacion.nombre_certificacion || 'Sin nombre';
            var tipo = certificacion.tipo_proceso ? ' · ' + certificacion.tipo_proceso : '';

            $('<option>', {
                value: String(idE),
                text: codigo + ' · ' + nombre + tipo
            }).appendTo($select);
        });
    }

    function iniciarTabla() {
        tabla = $('#tablaPersonal').DataTable({
            /*
             * La tabla ya no usa ajax interno de DataTables.
             * Esto evita que el buscador nativo dispare consultas al backend.
             * Los datos se cargan manualmente con cargarPersonal() cuando cambia
             * la línea, el turno o el filtro de estado. Después DataTables filtra
             * localmente lo que ya está en memoria.
             */
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
                    data: 'nomina',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function (data, type) {
                        var nomina = normalizarNomina(data);

                        if (type !== 'display') {
                            return nomina;
                        }

                        var checked = personalSeleccionado.has(nomina)
                            ? ' checked'
                            : '';

                        return '<input class="form-check-input table-check seleccionar-persona" ' +
                            'type="checkbox" value="' + escapeAttribute(nomina) + '"' + checked +
                            ' aria-label="Seleccionar trabajador">';
                    }
                },
                {
                    data: 'nomina',
                    name: 'nomina',
                    render: function (data, type) {
                        var nomina = normalizarNomina(data);
                        if (type !== 'display') {
                            return nomina;
                        }
                        return '<span class="person-name">' + escapeHtml(nomina) + '</span>';
                    }
                },
                {
                    data: 'nombre',
                    name: 'nombre',
                    defaultContent: '',
                    render: function (data, type, row) {
                        var nombre = (data || '').trim();
                        var linea = row.codigo_linea || '';

                        if (type !== 'display') {
                            return nombre;
                        }

                        if (!nombre) {
                            return '<span class="empty-text">Sin nombre registrado</span>';
                        }

                        return '<span class="person-name">' + escapeHtml(nombre) + '</span>' +
                            '<span class="person-subtext">Línea ' + escapeHtml(linea) + '</span>';
                    }
                },
                {
                    data: 'turno',
                    name: 'turno',
                    defaultContent: '',
                    render: function (data, type) {
                        var turno = (data || '').trim();
                        if (type !== 'display') {
                            return turno;
                        }
                        return turno
                            ? '<span class="turno-badge"><i class="bi bi-clock"></i> ' + escapeHtml(turno) + '</span>'
                            : '<span class="empty-text">Sin turno</span>';
                    }
                },
                {
                    data: 'estatus',
                    name: 'estatus',
                    defaultContent: '',
                    render: function (data, type) {
                        var estatus = String(data == null ? '' : data).trim();
                        if (type !== 'display') {
                            return estatus;
                        }
                        return renderEstatusPersonal(estatus);
                    }
                },
                {
                    data: null,
                    name: 'cursos_asignados',
                    render: function (data, type, row) {
                        var total = Number(row.cursos_asignados) || 0;
                        var detalle = row.cursos_detalle || '';

                        if (type !== 'display') {
                            return String(total) + ' ' + detalle;
                        }

                        if (total <= 0) {
                            return '<span class="course-badge pending">' +
                                '<i class="bi bi-exclamation-circle"></i> Sin cursos</span>';
                        }

                        return '<span class="course-badge" title="' + escapeAttribute(detalle) + '">' +
                            '<i class="bi bi-patch-check"></i> ' + total +
                            (total === 1 ? ' curso' : ' cursos') + '</span>' +
                            '<span class="course-list-preview" title="' + escapeAttribute(detalle) + '">' +
                            escapeHtml(detalle) + '</span>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: function () {
                        return '<div class="action-group">' +
                            '<button type="button" class="btn btn-action btn-action-edit btn-ver-cursos" ' +
                            'title="Ver cursos" aria-label="Ver cursos">' +
                            '<i class="bi bi-list-check" aria-hidden="true"></i>' +
                            '</button>' +
                            '</div>';
                    }
                }
            ],

            order: [[2, 'asc']],

            responsive: {
                details: {
                    type: 'inline',
                    target: 'tr'
                }
            },
            /*dom: '<"datatable-search-row"f>rt<"d-flex flex-column flex-md-row align-items-center justify-content-between gap-2"ip>', */
            language: {
                emptyTable: 'Selecciona una línea para consultar el personal.',
                zeroRecords: 'No se encontró personal con esos criterios.',
                search: 'Buscar personal:',
                searchPlaceholder: 'Nómina, nombre, turno, estatus o curso',
                info: 'Mostrando _START_–_END_ de _TOTAL_ personas',
                infoEmpty: 'Mostrando 0 personas',
                infoFiltered: '— filtrado de _MAX_ personas cargadas',
                paginate: {
                    first: 'Primera',
                    last: 'Última',
                    next: '<i class="bi bi-chevron-right" aria-hidden="true"></i>',
                    previous: '<i class="bi bi-chevron-left" aria-hidden="true"></i>'
                }
            },

            drawCallback: function () {
                /*
                 * Durante la inicialización DataTables ejecuta drawCallback
                 * antes de que la variable global `tabla` reciba la instancia.
                 * Por eso usamos this.api() en este punto.
                 */
                var api = this.api();
                sincronizarChecksPagina(api);
                actualizarContadorVisible(api);
            }
        });

        configurarBusquedaDataTable(false);
    }

    function cargarPersonal(resetearPagina) {
        var codigoLinea = obtenerCodigoLineaSeleccionado();

        if (!tabla) {
            return;
        }

        limpiarBusquedaDataTable();

        if (!codigoLinea) {
            tabla.clear().draw();
            actualizarResumen({});
            actualizarContadorVisible();
            return;
        }

        var $contenedorTabla = $(tabla.table().container());
        $contenedorTabla.addClass('tabla-cargando');

        var datosPeticion = {
            accion: 'listar',
            modo_busqueda: 'front',
            codigo_linea: codigoLinea,
            turno: obtenerTurnoSeleccionado(),
            estado: $('#filtroEstado').val() || 'todos'
        };

        if (DEBUG) {
            datosPeticion.debug = '1';
            console.info('[ILU] Solicitud de personal:', datosPeticion);
        }

        $.ajax({
            url: API_URL,
            method: 'GET',
            dataType: 'json',
            cache: false,
            data: datosPeticion
        })
            .done(function (respuesta) {
                if (!respuesta || respuesta.success !== true) {
                    tabla.clear().draw();
                    actualizarResumen({});

                    mostrarToast(
                        'No se pudo cargar el personal',
                        respuesta && respuesta.message
                            ? respuesta.message
                            : 'La API devolvió una respuesta no válida.',
                        'error'
                    );
                    return;
                }

                actualizarResumen(respuesta.summary || {});

                var registros = Array.isArray(respuesta.data) ? respuesta.data : [];

                if (DEBUG) {
                    console.info('[ILU] Respuesta de personal:', {
                        registros: registros.length,
                        resumen: respuesta.summary || {},
                        debug: respuesta.debug || null
                    });
                }

                tabla.clear();
                tabla.rows.add(registros);

                if (DEBUG && registros.length === 0) {
                    mostrarToast(
                        'Sin personal para el filtro actual',
                        'La API respondió correctamente, pero no encontró personal para la línea ' +
                            codigoLinea + ', turno ' + obtenerTurnoSeleccionado() + '.',
                        'info'
                    );
                }

                if (resetearPagina) {
                    tabla.page('first');
                }

                tabla.draw();
            })
            .fail(function (xhr) {
                console.error(
                    'Error al cargar personal:',
                    xhr.status,
                    xhr.responseText
                );

                tabla.clear().draw();
                actualizarResumen({});

                mostrarToast(
                    'Error al cargar el personal',
                    obtenerMensajeError(
                        xhr,
                        'No fue posible consultar el personal.'
                    ),
                    'error'
                );
            })
            .always(function () {
                $contenedorTabla.removeClass('tabla-cargando');
                actualizarContadorVisible();
            });
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
        $('#filtroTurno, #filtroEstado, #btnLimpiarFiltros')
            .prop('disabled', !habilitado);

        configurarBusquedaDataTable(habilitado);

        if (linea) {
            $('#informacionLinea').html(
                '<span class="info-icon"><i class="bi bi-diagram-3" aria-hidden="true"></i></span>' +
                '<div><strong>' + escapeHtml(linea.nombre_linea || codigoLinea) + '</strong>' +
                '<small>' + escapeHtml(linea.descripcion || 'Línea de producción seleccionada.') + '</small></div>'
            );
        } else {
            $('#informacionLinea').html(
                '<span class="info-icon"><i class="bi bi-info-circle" aria-hidden="true"></i></span>' +
                '<div><strong>Ninguna línea seleccionada</strong>' +
                '<small>El personal se mostrará después de seleccionar una línea.</small></div>'
            );
            actualizarResumen({});
        }

        cargarPersonal(true);
    });

    $('#filtroTurno, #filtroEstado').on('change', function () {
        limpiarSeleccion();
        cargarPersonal(true);
    });

    $('#btnLimpiarFiltros').on('click', function () {
        limpiarFiltros(true);
    });

    function limpiarFiltros(recargar) {
        $('#filtroTurno').val('1');
        $('#filtroEstado').val('todos');

        if (tabla) {
            limpiarBusquedaDataTable();
            if (recargar) {
                limpiarSeleccion();
                cargarPersonal(true);
            }
        }
    }

    $('#tablaPersonal tbody').on('change', '.seleccionar-persona', function () {
        var nomina = normalizarNomina($(this).val());

        if (this.checked) {
            personalSeleccionado.add(nomina);
        } else {
            personalSeleccionado.delete(nomina);
        }

        actualizarPanelMasivo();
        actualizarCheckCabecera();
    });

    $('#seleccionarPagina').on('change', function () {
        var seleccionar = this.checked;
        var filas = tabla.rows({ page: 'current' }).data().toArray();

        filas.forEach(function (fila) {
            var nomina = normalizarNomina(fila.nomina);
            if (seleccionar) {
                personalSeleccionado.add(nomina);
            } else {
                personalSeleccionado.delete(nomina);
            }
        });

        sincronizarChecksPagina();
        actualizarPanelMasivo();
    });

    $('#btnAsignarMasivo').on('click', function () {
        prepararAccionMasiva('asignar');
    });

    $('#btnQuitarMasivo').on('click', function () {
        prepararAccionMasiva('quitar');
    });

    function prepararAccionMasiva(accion) {
        var idE = obtenerIdCertificacionSeleccionada();

        if (personalSeleccionado.size === 0) {
            mostrarToast('Sin selección', 'Selecciona al menos una persona.', 'error');
            return;
        }

        if (!idE) {
            mostrarToast(
                'Selecciona un curso',
                'Elige el curso o certificación que se aplicará a las personas seleccionadas.',
                'error'
            );
            $('#certificacionMasiva').focus();
            return;
        }

        var certificacion = certificaciones.find(function (item) {
            return Number(item.idE || item.idCR) === idE;
        });

        var nombreCurso = certificacion
            ? ((certificacion.codigo_certificacion || idE) + ' · ' + certificacion.nombre_certificacion)
            : ('ID ' + idE);

        accionPendiente = {
            accion: accion,
            idE: idE
        };

        if (accion === 'asignar') {
            $('#iconoConfirmacionMasiva').html('<i class="bi bi-patch-check" aria-hidden="true"></i>');
            $('#modalConfirmarMasivoLabel').text('¿Asignar curso?');
            $('#mensajeConfirmacionMasiva').text(
                'Se asignará "' + nombreCurso + '" a ' + personalSeleccionado.size +
                (personalSeleccionado.size === 1 ? ' persona.' : ' personas.')
            );
        } else {
            $('#iconoConfirmacionMasiva').html('<i class="bi bi-x-circle" aria-hidden="true"></i>');
            $('#modalConfirmarMasivoLabel').text('¿Quitar curso?');
            $('#mensajeConfirmacionMasiva').text(
                'Se quitará "' + nombreCurso + '" de ' + personalSeleccionado.size +
                (personalSeleccionado.size === 1 ? ' persona seleccionada.' : ' personas seleccionadas.')
            );
        }

        modalConfirmar.show();
    }

    $('#btnConfirmarMasivo').on('click', function () {
        if (!accionPendiente) {
            return;
        }

        guardarCambiosMasivos(
            accionPendiente.accion,
            Array.from(personalSeleccionado),
            accionPendiente.idE,
            $('#btnConfirmarMasivo'),
            function () {
                modalConfirmar.hide();
                accionPendiente = null;
            }
        );
    });

    function guardarCambiosMasivos(accion, nominas, idE, $boton, alCompletar) {
        var codigoLinea = obtenerCodigoLineaSeleccionado();

        if (!codigoLinea || !nominas.length || !idE) {
            mostrarToast('Datos incompletos', 'Selecciona una línea, personal y un curso.', 'error');
            return;
        }

        cambiarEstadoBoton($boton, true, 'Guardando...');

        $.ajax({
            url: API_URL,
            method: 'POST',
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            data: JSON.stringify({
                accion: accion,
                codigo_linea: codigoLinea,
                nominas: nominas,
                idE: idE,
                estatus: '0'
            })
        })
            .done(function (respuesta) {
                if (!respuesta || respuesta.success !== true) {
                    mostrarToast(
                        'No se guardaron los cambios',
                        respuesta && respuesta.message
                            ? respuesta.message
                            : 'No fue posible actualizar SPC_ILU.',
                        'error'
                    );
                    return;
                }

                if (typeof alCompletar === 'function') {
                    alCompletar();
                }

                mostrarToast(
                    'Cambios guardados',
                    respuesta.message,
                    'success'
                );

                limpiarSeleccion();
                cargarPersonal(false);
            })
            .fail(function (xhr) {
                mostrarToast(
                    'No se guardaron los cambios',
                    obtenerMensajeError(xhr, 'No fue posible actualizar SPC_ILU.'),
                    'error'
                );
            })
            .always(function () {
                cambiarEstadoBoton($boton, false);
            });
    }

    $('#tablaPersonal tbody').on('click', '.btn-ver-cursos', function (event) {
        event.stopPropagation();

        var registro = obtenerRegistroDesdeBoton(this);
        if (!registro) {
            return;
        }

        cargarDetallePersona(registro);
    });

    function cargarDetallePersona(registro) {
        var nomina = normalizarNomina(registro.nomina);
        $('#nominaDetalle').val(nomina);
        $('#nombrePersonaDetalle').text(
            nomina + ' · ' + (registro.nombre || 'Sin nombre registrado')
        );
        $('#listaCursosDetalle').html(
            '<div class="detail-empty"><i class="bi bi-hourglass-split"></i>Cargando cursos...</div>'
        );

        modalCursosPersona.show();

        $.ajax({
            url: API_URL,
            method: 'GET',
            dataType: 'json',
            cache: false,
            data: {
                accion: 'detalle',
                codigo_linea: obtenerCodigoLineaSeleccionado() || '',
                nomina: nomina
            }
        })
            .done(function (respuesta) {
                if (!respuesta || respuesta.success !== true) {
                    $('#listaCursosDetalle').html(
                        '<div class="detail-empty"><i class="bi bi-exclamation-circle"></i>' +
                        escapeHtml(respuesta && respuesta.message ? respuesta.message : 'No fue posible cargar el detalle.') +
                        '</div>'
                    );
                    return;
                }

                renderDetalleCursos(respuesta.data || []);
            })
            .fail(function (xhr) {
                $('#listaCursosDetalle').html(
                    '<div class="detail-empty"><i class="bi bi-exclamation-circle"></i>' +
                    escapeHtml(obtenerMensajeError(xhr, 'No fue posible cargar el detalle.')) +
                    '</div>'
                );
            });
    }

    function renderDetalleCursos(cursos) {
        var $contenedor = $('#listaCursosDetalle');
        $contenedor.empty();

        if (!Array.isArray(cursos) || cursos.length === 0) {
            $contenedor.html(
                '<div class="detail-empty"><i class="bi bi-journal-x"></i>Esta persona no tiene cursos asignados.</div>'
            );
            return;
        }

        cursos.forEach(function (curso) {
            var idE = Number(curso.idE);
            var codigo = curso.codigo_certificacion || ('ID ' + idE);
            var nombre = curso.nombre_certificacion || 'Sin nombre';
            var fecha = curso.fecha_registro || 'Sin fecha';
            var estatus = curso.estatus || '0';

            var $item = $('<div>', {
                class: 'detail-course-item'
            });

            $('<div>').html(
                '<strong>' + escapeHtml(codigo + ' · ' + nombre) + '</strong>' +
                '<small>Registrado: ' + escapeHtml(fecha) + ' · Estatus: ' + escapeHtml(estatus) + '</small>'
            ).appendTo($item);

            $('<button>', {
                type: 'button',
                class: 'btn btn-sm btn-outline-danger btn-quitar-detalle',
                'data-id-e': String(idE),
                html: '<i class="bi bi-x-circle" aria-hidden="true"></i> Quitar'
            }).appendTo($item);

            $contenedor.append($item);
        });
    }

    $('#listaCursosDetalle').on('click', '.btn-quitar-detalle', function () {
        var nomina = normalizarNomina($('#nominaDetalle').val());
        var idE = Number($(this).data('id-e'));

        if (!nomina || !idE) {
            mostrarToast('Datos incompletos', 'No fue posible identificar el curso a quitar.', 'error');
            return;
        }

        guardarCambiosMasivos(
            'quitar',
            [nomina],
            idE,
            $(this),
            function () {
                var registro = tabla.rows().data().toArray().find(function (fila) {
                    return normalizarNomina(fila.nomina) === nomina;
                });

                if (registro) {
                    cargarDetallePersona(registro);
                }
            }
        );
    });

    function obtenerIdCertificacionSeleccionada() {
        var valor = $('#certificacionMasiva').val();
        var idE = Number(valor);
        return Number.isInteger(idE) && idE > 0 ? idE : null;
    }

    function obtenerTurnoSeleccionado() {
        var turno = String($('#filtroTurno').val() || '1').trim();

        if (turno !== '1' && turno !== '2') {
            return '1';
        }

        return turno;
    }

    function configurarBusquedaDataTable(habilitado) {
        var $filtro = $('#tablaPersonal_filter');
        var $input = $filtro.find('input');

        $filtro.toggleClass('dt-search-disabled', !habilitado);
        $input
            .attr({
                placeholder: 'Nómina, nombre, turno, estatus o curso',
                'aria-label': 'Buscar personal en DataTable'
            })
            .prop('disabled', !habilitado);
    }

    function limpiarBusquedaDataTable() {
        if (!tabla) {
            return;
        }

        tabla.search('');
        $('#tablaPersonal_filter input').val('');
    }

    function obtenerCodigoLineaSeleccionado() {
        var valor = $('#selectLinea').val();

        if (Array.isArray(valor)) {
            valor = valor.length ? valor[0] : '';
        }

        var codigoLinea = String(
            valor == null ? '' : valor
        ).trim();

        if (!codigoLinea || codigoLinea.length > 100) {
            return null;
        }

        return codigoLinea;
    }

    function obtenerRegistroDesdeBoton(boton) {
        var api = obtenerTablaApi();
        if (!api) {
            return null;
        }

        var $fila = $(boton).closest('tr');
        if ($fila.hasClass('child')) {
            $fila = $fila.prev();
        }
        return api.row($fila).data();
    }

    function obtenerTablaApi(api) {
        if (api && typeof api.rows === 'function') {
            return api;
        }

        if (tabla && typeof tabla.rows === 'function') {
            return tabla;
        }

        return null;
    }

    function sincronizarChecksPagina(api) {
        $('#tablaPersonal tbody .seleccionar-persona').each(function () {
            this.checked = personalSeleccionado.has(normalizarNomina(this.value));
        });
        actualizarCheckCabecera(api);
        actualizarPanelMasivo();
    }

    function actualizarCheckCabecera(api) {
        var tablaApi = obtenerTablaApi(api);
        var checkbox = document.getElementById('seleccionarPagina');

        if (!checkbox) {
            return;
        }

        if (!tablaApi) {
            checkbox.checked = false;
            checkbox.indeterminate = false;
            checkbox.disabled = true;
            return;
        }

        var filas = tablaApi.rows({ page: 'current' }).data().toArray();
        var totalPagina = filas.length;
        var seleccionadasPagina = filas.filter(function (fila) {
            return personalSeleccionado.has(normalizarNomina(fila.nomina));
        }).length;

        checkbox.checked = totalPagina > 0 && seleccionadasPagina === totalPagina;
        checkbox.indeterminate = seleccionadasPagina > 0 && seleccionadasPagina < totalPagina;
        checkbox.disabled = totalPagina === 0;
    }

    function actualizarPanelMasivo() {
        var total = personalSeleccionado.size;
        $('#panelAccionesMasivas').toggleClass('is-hidden', total === 0);
        $('#textoSeleccionados').text(
            total + (total === 1 ? ' persona seleccionada' : ' personas seleccionadas')
        );
    }

    function limpiarSeleccion() {
        personalSeleccionado.clear();
        $('#seleccionarPagina').prop({ checked: false, indeterminate: false });
        actualizarPanelMasivo();
    }

    function actualizarResumen(resumen) {
        animarNumero($('#totalPersonal'), Number(resumen.total_personal) || 0);
        animarNumero($('#totalConCurso'), Number(resumen.con_curso) || 0);
        animarNumero($('#totalSinCurso'), Number(resumen.sin_curso) || 0);
        animarNumero($('#totalRegistrosIlu'), Number(resumen.registros_ilu) || 0);
    }

    function actualizarContadorVisible(api) {
        var tablaApi = obtenerTablaApi(api);
        if (!tablaApi) {
            $('#contadorVisible').text('0 registros');
            return;
        }

        var total = tablaApi.page.info().recordsDisplay || 0;
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

    function renderEstatusPersonal(estatus) {
        if (estatus === '0') {
            return '<span class="status-badge available"><i class="bi bi-check-circle"></i> Disponible</span>';
        }

        if (estatus === '1') {
            return '<span class="status-badge assigned"><i class="bi bi-person-check"></i> Asignado</span>';
        }

        if (estatus === '2') {
            return '<span class="status-badge deleted"><i class="bi bi-x-circle"></i> Eliminado</span>';
        }

        if (!estatus) {
            return '<span class="status-badge neutral"><i class="bi bi-dash-circle"></i> Sin estatus</span>';
        }

        return '<span class="status-badge neutral"><i class="bi bi-info-circle"></i> ' + escapeHtml(estatus) + '</span>';
    }

    function normalizarNomina(valor) {
        return String(valor == null ? '' : valor).trim();
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
