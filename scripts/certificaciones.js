$(function () {
    'use strict';

    const API_URL = './api/certificaciones.php';
    const SEARCH_DELAY = 450;

    const modalCertificacion = new bootstrap.Modal(
        document.getElementById('modalCertificacion')
    );

    const modalEliminar = new bootstrap.Modal(
        document.getElementById('modalEliminar')
    );

    const toastNotificacion = new bootstrap.Toast(
        document.getElementById('toastNotificacion'),
        {
            delay: 3200
        }
    );

    const $form = $('#formCertificacion');
    const $busqueda = $('#busquedaCertificacion');
    const $filtroProceso = $('#filtroProceso');
    const $btnLimpiarBusqueda = $('#btnLimpiarBusqueda');
    const $btnGuardar = $('#btnGuardarCertificacion');
    const $btnEliminar = $('#btnConfirmarEliminar');

    let tabla = null;
    let temporizadorBusqueda = null;
    let cargandoCatalogos = false;

    actualizarTextosModoServidor();
    iniciarTabla();

    /**
     * DataTables trabaja en modo servidor.
     *
     * El backend realiza:
     * - Búsqueda
     * - Filtro por proceso
     * - Ordenamiento
     * - Paginación
     * - Resúmenes
     * - Catálogo de procesos
     */
    function iniciarTabla() {
        tabla = $('#tablaCertificaciones').DataTable({
            processing: false, //atributo para mostrar el mensaje de procesando
            serverSide: false, //Dejar en false para Filtrar los datos cargado en el datatable
            searching: true,
            pageLength: 8,
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

            ajax: {
                url: API_URL,
                type: 'GET',
                dataType: 'json',
                cache: false,

                /**
                 * Agrega el filtro personalizado a los parámetros
                 * que DataTables ya envía al servidor.
                 */
                data: function (parametros) {
                    parametros.tipo_proceso =
                        $filtroProceso.val() || '';
                },

                /**
                 * La API devuelve:
                 *
                 * {
                 *     success: true,
                 *     draw: 1,
                 *     recordsTotal: 20,
                 *     recordsFiltered: 5,
                 *     data: [],
                 *     summary: {},
                 *     procesos: []
                 * }
                 */
                dataSrc: function (respuesta) {
                    if (
                        !respuesta ||
                        respuesta.success !== true
                    ) {
                        mostrarToast(
                            'No se pudieron cargar los datos',
                            respuesta?.message ||
                                'La API devolvió una respuesta no válida.',
                            'error'
                        );

                        return [];
                    }

                    actualizarResumen(
                        respuesta.summary || {}
                    );

                    actualizarOpcionesProceso(
                        respuesta.procesos || []
                    );

                    return Array.isArray(respuesta.data)
                        ? respuesta.data
                        : [];
                },

                error: function (xhr) {
                    mostrarToast(
                        'Error de conexión',
                        obtenerMensajeError(
                            xhr,
                            'No fue posible consultar las certificaciones.'
                        ),
                        'error'
                    );
                }
            },

            columns: [
                {
                    data: 'codigo_certificacion',
                    name: 'codigo_certificacion',

                    render: function (data, type) {
                        const codigo = data || '';

                        if (type !== 'display') {
                            return codigo;
                        }

                        return `
                            <span class="code-badge">
                                ${escapeHtml(codigo)}
                            </span>
                        `;
                    }
                },
                {
                    data: 'nombre_certificacion',
                    name: 'nombre_certificacion',

                    render: function (data, type) {
                        const nombre = data || '';

                        if (type !== 'display') {
                            return nombre;
                        }

                        return `
                            <span class="certification-name">
                                ${escapeHtml(nombre)}
                            </span>
                        `;
                    }
                },
                {
                    data: 'tipo_proceso',
                    name: 'tipo_proceso',

                    render: function (data, type) {
                        const proceso = (data || '').trim();

                        if (type !== 'display') {
                            return proceso;
                        }

                        if (!proceso) {
                            return `
                                <span class="process-badge process-gray">
                                    Sin proceso
                                </span>
                            `;
                        }

                        return `
                            <span
                                class="process-badge
                                ${obtenerClaseProceso(proceso)}"
                            >
                                ${escapeHtml(proceso)}
                            </span>
                        `;
                    }
                },
                {
                    data: 'descripcion',
                    name: 'descripcion',
                    defaultContent: '',

                    render: function (data, type) {
                        const descripcion =
                            (data || '').trim();

                        if (type !== 'display') {
                            return descripcion;
                        }

                        if (!descripcion) {
                            return `
                                <span class="empty-text">
                                    Sin descripción
                                </span>
                            `;
                        }

                        return `
                            <span
                                class="description-cell"
                                title="${escapeAttribute(descripcion)}"
                            >
                                ${escapeHtml(descripcion)}
                            </span>
                        `;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-end',

                    render: function () {
                        return `
                            <div class="action-group">
                                <button
                                    type="button"
                                    class="
                                        btn
                                        btn-action
                                        btn-action-edit
                                        btn-editar
                                    "
                                    title="Editar certificación"
                                    aria-label="Editar certificación"
                                >
                                    <i
                                        class="bi bi-pencil"
                                        aria-hidden="true"
                                    ></i>
                                </button>

                                <button
                                    type="button"
                                    class="
                                        btn
                                        btn-action
                                        btn-action-delete
                                        btn-eliminar
                                        d-none
                                    "
                                    title="Eliminar certificación"
                                    aria-label="Eliminar certificación"
                                >
                                    <i
                                        class="bi bi-trash3"
                                        aria-hidden="true"
                                    ></i>
                                </button>
                            </div>
                        `;
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

            //dom: `rt<"d-flex flex-column flex-md-row align-items-center justify-content-between gap-2"ip>`,

            language: {


                emptyTable:
                    'No hay certificaciones registradas.',

                zeroRecords:
                    'No encontramos registros con esos criterios.',

                info:
                    'Mostrando _START_–_END_ de _TOTAL_ registros',

                infoEmpty:
                    'Mostrando 0 registros',

                /*
                 * DataTables normalmente muestra:
                 * "(filtrados de X registros)".
                 *
                 * Se deja vacío porque el contador superior ya
                 * informa cuántos registros coinciden.
                 */
                infoFiltered: '',

                paginate: {
                    first: 'Primera',
                    last: 'Última',

                    next: `
                        <i
                            class="bi bi-chevron-right"
                            aria-hidden="true"
                        ></i>
                    `,

                    previous: `
                        <i
                            class="bi bi-chevron-left"
                            aria-hidden="true"
                        ></i>
                    `
                }
            },

            drawCallback: function () {
                actualizarContadorVisible();
            }
        });
    }

    /**
     * Búsqueda procesada por el backend.
     *
     * Se espera 450 ms después de que el usuario deja
     * de escribir para evitar una petición por cada tecla.
     */
    $busqueda.on('input', function () {
        const valor = this.value;

        $btnLimpiarBusqueda.toggleClass(
            'd-none',
            valor.length === 0
        );

        clearTimeout(temporizadorBusqueda);

        temporizadorBusqueda = setTimeout(
            function () {
                tabla.search(valor).draw();
            },
            SEARCH_DELAY
        );
    });

    /**
     * Limpiar búsqueda.
     */
    $btnLimpiarBusqueda.on('click', function () {
        clearTimeout(temporizadorBusqueda);

        $busqueda.val('');
        $btnLimpiarBusqueda.addClass('d-none');

        tabla.search('').draw();

        $busqueda.focus();
    });

    /**
     * El proceso seleccionado se añade a la petición GET.
     */
    $filtroProceso.on('change', function () {
        tabla.ajax.reload(null, true);
    });

    /**
     * Restablecer todos los filtros.
     */
    $('#btnLimpiarFiltros').on(
        'click',
        function () {
            clearTimeout(temporizadorBusqueda);

            $busqueda.val('');
            $btnLimpiarBusqueda.addClass('d-none');
            $filtroProceso.val('');

            tabla.search('');
            tabla.ajax.reload(null, true);
        }
    );

    /**
     * Abrir formulario para crear.
     */
    $('#btnNuevaCertificacion').on(
        'click',
        function () {
            prepararFormularioNuevo();
            modalCertificacion.show();
        }
    );

    /**
     * Abrir formulario para editar.
     */
    $('#tablaCertificaciones tbody').on(
        'click',
        '.btn-editar',
        function (event) {
            event.stopPropagation();

            const registro =
                obtenerRegistroDesdeBoton(this);

            if (!registro) {
                return;
            }

            prepararFormularioEdicion(registro);
            modalCertificacion.show();
        }
    );

    /**
     * Abrir confirmación para eliminar.
     */
    $('#tablaCertificaciones tbody').on(
        'click',
        '.btn-eliminar',
        function (event) {
            event.stopPropagation();

            const registro =
                obtenerRegistroDesdeBoton(this);

            if (!registro) {
                return;
            }

            $('#idEliminar').val(registro.idCR);

            $('#nombreCertificacionEliminar').text(
                registro.nombre_certificacion
            );

            modalEliminar.show();
        }
    );

    /**
     * Contador de caracteres.
     */
    $('#descripcion').on(
        'input',
        function () {
            $('#contadorDescripcion').text(
                this.value.length
            );
        }
    );

    /**
     * Código siempre en mayúsculas.
     */
    $('#codigo_certificacion').on(
        'input',
        function () {
            $(this).removeClass('is-invalid');

            this.value =
                this.value.toUpperCase();
        }
    );

    /**
     * Registrar o modificar.
     *
     * POST = nuevo registro.
     * PUT  = modificar registro.
     *
     * La validación de código duplicado se ejecuta
     * en el backend.
     */
    $form.on(
        'submit',
        function (event) {
            event.preventDefault();
            event.stopPropagation();

            const formulario = this;

            formulario.classList.add(
                'was-validated'
            );

            if (!formulario.checkValidity()) {
                formulario
                    .querySelector(':invalid')
                    ?.focus();

                return;
            }

            const idCR =
                Number($('#idCR').val()) || null;

            const registro = {
                codigo_certificacion:
                    $('#codigo_certificacion')
                        .val()
                        .trim(),

                nombre_certificacion:
                    $('#nombre_certificacion')
                        .val()
                        .trim(),

                tipo_proceso:
                    $('#tipo_proceso')
                        .val()
                        .trim(),

                descripcion:
                    $('#descripcion')
                        .val()
                        .trim()
            };

            if (idCR) {
                registro.idCR = idCR;
            }

            cambiarEstadoBoton(
                $btnGuardar,
                true,
                idCR
                    ? 'Guardando cambios...'
                    : 'Guardando...'
            );

            $.ajax({
                url: API_URL,

                method:
                    idCR
                        ? 'PUT'
                        : 'POST',

                contentType:
                    'application/json; charset=utf-8',

                dataType: 'json',

                data: JSON.stringify(registro)
            })
                .done(function (respuesta) {
                    modalCertificacion.hide();

                    mostrarToast(
                        idCR
                            ? 'Certificación actualizada'
                            : 'Certificación registrada',

                        respuesta.message,

                        'success'
                    );

                    /*
                     * En edición conserva la página actual.
                     * En creación regresa a la primera página.
                     */
                    tabla.ajax.reload(
                        null,
                        idCR ? false : true
                    );
                })
                .fail(function (xhr) {
                    const mensaje =
                        obtenerMensajeError(
                            xhr,
                            'No fue posible guardar la certificación.'
                        );

                    /*
                     * HTTP 409 significa código duplicado.
                     */
                    if (xhr.status === 409) {
                        $('#codigo_certificacion')
                            .addClass('is-invalid')
                            .focus();
                    }

                    mostrarToast(
                        'No se guardó la certificación',
                        mensaje,
                        'error'
                    );
                })
                .always(function () {
                    cambiarEstadoBoton(
                        $btnGuardar,
                        false
                    );
                });
        }
    );

    /**
     * Eliminación real en SQL Server.
     */
    $btnEliminar.on(
        'click',
        function () {
            const idCR =
                Number($('#idEliminar').val());

            if (!idCR) {
                mostrarToast(
                    'Registro no válido',
                    'No se encontró el identificador de la certificación.',
                    'error'
                );

                return;
            }

            cambiarEstadoBoton(
                $btnEliminar,
                true,
                'Eliminando...'
            );

            $.ajax({
                url:
                    `${API_URL}?idCR=` +
                    encodeURIComponent(idCR),

                method: 'DELETE',
                dataType: 'json'
            })
                .done(function (respuesta) {
                    modalEliminar.hide();

                    mostrarToast(
                        'Certificación eliminada',
                        respuesta.message,
                        'success'
                    );

                    /*
                     * Conserva la página actual cuando es posible.
                     */
                    tabla.ajax.reload(
                        null,
                        false
                    );
                })
                .fail(function (xhr) {
                    mostrarToast(
                        'No se eliminó la certificación',

                        obtenerMensajeError(
                            xhr,
                            'No fue posible eliminar el registro.'
                        ),

                        'error'
                    );
                })
                .always(function () {
                    cambiarEstadoBoton(
                        $btnEliminar,
                        false
                    );
                });
        }
    );

    /**
     * Enfocar el código cuando se abre el modal.
     */
    document
        .getElementById('modalCertificacion')
        .addEventListener(
            'shown.bs.modal',
            function () {
                document
                    .getElementById(
                        'codigo_certificacion'
                    )
                    .focus();
            }
        );

    /**
     * Obtiene los datos de la fila presionada.
     *
     * También funciona cuando DataTables Responsive
     * muestra una fila secundaria en dispositivos móviles.
     */
    function obtenerRegistroDesdeBoton(boton) {
        let $fila =
            $(boton).closest('tr');

        if ($fila.hasClass('child')) {
            $fila = $fila.prev();
        }

        return tabla.row($fila).data();
    }

    /**
     * Preparar formulario nuevo.
     */
    function prepararFormularioNuevo() {
        $form[0].reset();

        $form.removeClass(
            'was-validated'
        );

        $('#codigo_certificacion')
            .removeClass('is-invalid');

        $('#idCR').val('');
        $('#contadorDescripcion').text('0');

        $('#modalCertificacionLabel').text(
            'Nueva certificación'
        );

        $('#btnGuardarCertificacion span').text(
            'Guardar certificación'
        );
    }

    /**
     * Preparar formulario de edición.
     */
    function prepararFormularioEdicion(registro) {
        $form[0].reset();

        $form.removeClass(
            'was-validated'
        );

        $('#codigo_certificacion')
            .removeClass('is-invalid');

        $('#idCR').val(
            registro.idCR
        );

        $('#codigo_certificacion').val(
            registro.codigo_certificacion || ''
        );

        $('#nombre_certificacion').val(
            registro.nombre_certificacion || ''
        );

        $('#tipo_proceso').val(
            registro.tipo_proceso || ''
        );

        $('#descripcion').val(
            registro.descripcion || ''
        );

        $('#contadorDescripcion').text(
            (registro.descripcion || '').length
        );

        $('#modalCertificacionLabel').text(
            'Editar certificación'
        );

        $('#btnGuardarCertificacion span').text(
            'Guardar cambios'
        );
    }

    /**
     * Los números ya fueron calculados por SQL Server.
     *
     * El frontend solamente actualiza la interfaz.
     */
    function actualizarResumen(resumen) {
        animarNumero(
            $('#totalCertificaciones'),
            Number(
                resumen.total_certificaciones
            ) || 0
        );

        animarNumero(
            $('#totalProcesos'),
            Number(
                resumen.total_procesos
            ) || 0
        );

        animarNumero(
            $('#totalConDescripcion'),
            Number(
                resumen.con_descripcion
            ) || 0
        );
    }

    /**
     * Llena el select del filtro y el datalist
     * con el catálogo devuelto por el backend.
     */
    function actualizarOpcionesProceso(
        procesos
    ) {
        if (cargandoCatalogos) {
            return;
        }

        cargandoCatalogos = true;

        const valorFiltroActual =
            $filtroProceso.val();

        const valorFormularioActual =
            $('#tipo_proceso').val();

        $filtroProceso
            .find('option:not(:first)')
            .remove();

        $('#listaProcesos').empty();

        procesos.forEach(
            function (proceso) {
                $('<option>', {
                    value: proceso,
                    text: proceso
                }).appendTo(
                    $filtroProceso
                );

                $('<option>', {
                    value: proceso
                }).appendTo(
                    '#listaProcesos'
                );
            }
        );

        /*
         * Mantiene el filtro seleccionado después
         * de que DataTables actualiza la información.
         */
        if (
            valorFiltroActual &&
            procesos.includes(
                valorFiltroActual
            )
        ) {
            $filtroProceso.val(
                valorFiltroActual
            );
        } else if (valorFiltroActual) {
            $('<option>', {
                value: valorFiltroActual,
                text: valorFiltroActual
            }).appendTo(
                $filtroProceso
            );

            $filtroProceso.val(
                valorFiltroActual
            );
        }

        /*
         * Evita borrar el proceso escrito en el modal
         * mientras se actualiza la tabla.
         */
        if (valorFormularioActual) {
            $('#tipo_proceso').val(
                valorFormularioActual
            );
        }

        cargandoCatalogos = false;
    }

    /**
     * Número total de registros que coinciden
     * con la búsqueda y el filtro actuales.
     */
    function actualizarContadorVisible() {
        if (!tabla) {
            return;
        }

        const informacion =
            tabla.page.info();

        const total =
            informacion.recordsDisplay || 0;

        $('#contadorVisible').text(
            `${total} ${
                total === 1
                    ? 'registro'
                    : 'registros'
            }`
        );
    }

    /**
     * Animación para las tarjetas de resumen.
     */
    function animarNumero(
        $elemento,
        valorFinal
    ) {
        const valorInicial =
            Number($elemento.text()) || 0;

        if (valorInicial === valorFinal) {
            $elemento.text(valorFinal);
            return;
        }

        const duracion = 260;
        const inicio = performance.now();

        function paso(tiempoActual) {
            const progreso = Math.min(
                (
                    tiempoActual -
                    inicio
                ) / duracion,
                1
            );

            const valor = Math.round(
                valorInicial +
                (
                    valorFinal -
                    valorInicial
                ) * progreso
            );

            $elemento.text(valor);

            if (progreso < 1) {
                requestAnimationFrame(
                    paso
                );
            }
        }

        requestAnimationFrame(paso);
    }

    /**
     * Muestra un spinner dentro del botón
     * mientras se ejecuta una petición.
     */
    function cambiarEstadoBoton(
        $boton,
        cargando,
        textoCargando = ''
    ) {
        if (cargando) {
            $boton.data(
                'contenido-original',
                $boton.html()
            );

            $boton.prop(
                'disabled',
                true
            );

            $boton.html(`
                <span
                    class="
                        spinner-border
                        spinner-border-sm
                    "
                    aria-hidden="true"
                ></span>

                <span>
                    ${escapeHtml(textoCargando)}
                </span>
            `);

            return;
        }

        const contenidoOriginal =
            $boton.data(
                'contenido-original'
            );

        if (contenidoOriginal) {
            $boton.html(
                contenidoOriginal
            );
        }

        $boton.prop(
            'disabled',
            false
        );
    }

    /**
     * Cambia únicamente el texto visual que antes
     * indicaba que los datos eran simulados.
     */
    function actualizarTextosModoServidor() {
        $('.environment-badge').html(`
            <span class="environment-dot"></span>
        `);

        $('.app-footer span')
            .first()
            .text(
                'SPC Quality · Administración de certificaciones'
            );

        $('.app-footer span')
            .last()
            .text(
                'Los cambios se guardan en la base de datos.'
            );
    }

    /**
     * Selecciona un estilo para la etiqueta
     * según el texto del proceso.
     */
    function obtenerClaseProceso(texto) {
        const clases = [
            'process-blue',
            'process-yellow',
            'process-red',
            'process-green',
            'process-purple'
        ];

        const suma = [...texto].reduce(
            function (
                acumulado,
                caracter
            ) {
                return (
                    acumulado +
                    caracter.charCodeAt(0)
                );
            },
            0
        );

        return clases[
            suma % clases.length
        ];
    }

    /**
     * Obtiene el mensaje de error enviado por PHP.
     */
    function obtenerMensajeError(
        xhr,
        mensajePredeterminado
    ) {
        const respuesta =
            xhr.responseJSON;

        if (respuesta?.message) {
            return respuesta.message;
        }

        if (xhr.status === 0) {
            return 'No fue posible comunicarse con el servidor.';
        }

        return mensajePredeterminado;
    }

    /**
     * Notificación Bootstrap Toast.
     */
    function mostrarToast(
        titulo,
        mensaje,
        tipo
    ) {
        const $toast =
            $('#toastNotificacion');

        const $icono =
            $toast.find(
                '.toast-icon i'
            );

        const esError =
            tipo === 'error';

        $toast.toggleClass(
            'toast-error',
            esError
        );

        $icono
            .toggleClass(
                'bi-check-lg',
                !esError
            )
            .toggleClass(
                'bi-exclamation-lg',
                esError
            );

        $('#toastTitulo').text(
            titulo
        );

        $('#toastMensaje').text(
            mensaje
        );

        toastNotificacion.show();
    }

    /**
     * Evita insertar HTML proveniente
     * de la base de datos.
     */
    function escapeHtml(valor) {
        return $('<div>')
            .text(valor ?? '')
            .html();
    }

    function escapeAttribute(valor) {
        return escapeHtml(valor)
            .replace(
                /`/g,
                '&#96;'
            );
    }
});
