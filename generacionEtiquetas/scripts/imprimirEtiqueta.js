$(document).ready(function () {
    const tableId = '#tablaConsultaEtiquetas';
    let filtroFechaDesde = $('#filtroFechaDesde').val();
    let filtroFechaHasta = $('#filtroFechaHasta').val();
    const modalVistaEtiqueta = document.getElementById('modalVistaEtiqueta');
    const modalEtiqueta = modalVistaEtiqueta ? bootstrap.Modal.getOrCreateInstance(modalVistaEtiqueta) : null;

    function mostrarVistaVacia() {
        $('#previewNombre').text('Sin resultados');
        $('#previewIdentificador').text('-');
        $('#previewDepartamento').text('-');
        $('#previewRegistrado').text('-');
        $('#previewFecha').text('-');
        $('#previewObservaciones').text('No hay una etiqueta seleccionada.');
        $('#previewFrasesH').empty();
        $('#previewFrasesP').empty();
        $('#previewPictogramas').empty();
    }

    function obtenerDatosFila($row) {
        return {
            nombre: $row.data('nombre') || '',
            identificador: $row.data('identificador') || '',
            palabra: $row.data('palabra') || 'Atencion',
            departamento: $row.data('departamento') || '',
            registrado: $row.data('registrado') || '',
            fecha: $row.data('fecha') || '',
            observaciones: $row.data('observaciones') || '',
            empresa: $row.data('empresa') || '',
            direccion: $row.data('direccion') || '',
            telefono: $row.data('telefono') || '',
            email: $row.data('email') || '',
            frasesH: String($row.data('frases-h') || '').split('|').filter(Boolean),
            frasesP: String($row.data('frases-p') || '').split('|').filter(Boolean),
            pictogramas: String($row.data('pictogramas') || '').split('|').filter(Boolean)
        };
    }

    function obtenerFechaComparable(valor) {
        if (!valor) return null;
        return new Date(valor + 'T00:00:00');
    }

    function crearBadgeFrase(frase, tipo) {
        const partes = frase.split('::');
        const codigo = partes[0] || '';
        const texto = partes[1] || '';
        return `
            <span class="impresion-frase-badge impresion-frase-${tipo}">
                <strong>${codigo}</strong> ${texto}
            </span>
        `;
    }

    function crearLineaFraseModal(frase, tipo) {
        const partes = frase.split('::');
        const codigo = partes[0] || '';
        const texto = partes[1] || '';
        return `
            <p class="impresion-modal-line impresion-modal-line-${tipo}">
                <strong>${codigo}:</strong> ${texto}
            </p>
        `;
    }

    function obtenerIconoPictograma(tipo) {
        const iconos = {
            flame: 'fa-fire-flame-curved',
            exclamation: 'fa-exclamation',
            skull: 'fa-skull-crossbones',
            health: 'fa-user-shield',
            corrosion: 'fa-flask-vial',
            'flame-circle': 'fa-burst'
        };

        return iconos[tipo] || 'fa-triangle-exclamation';
    }

    function crearPictograma(tipo) {
        return `
            <span class="ghs-pictogram">
                <span class="ghs-pictogram-inner">
                    <i class="fa-solid ${obtenerIconoPictograma(tipo)}"></i>
                </span>
            </span>
        `;
    }

    function actualizarVistaPrevia($row) {
        if (!$row || !$row.length) return;

        $(".etiqueta-row").removeClass('is-selected');
        $row.addClass('is-selected');

        const data = obtenerDatosFila($row);

        $('#previewNombre').text(data.nombre);
        $('#previewIdentificador').text(data.identificador);
        $('#previewDepartamento').text(data.departamento);
        $('#previewRegistrado').text(data.registrado);
        $('#previewFecha').text(data.fecha);
        $('#previewObservaciones').text(data.observaciones);
        $('#previewFrasesH').html(data.frasesH.map(frase => crearBadgeFrase(frase, 'h')).join(''));
        $('#previewFrasesP').html(data.frasesP.map(frase => crearBadgeFrase(frase, 'p')).join(''));
        $('#previewPictogramas').html(data.pictogramas.map(tipo => crearPictograma(tipo)).join(''));
    }

    function actualizarModalEtiqueta($row) {
        if (!$row || !$row.length) return;

        const data = obtenerDatosFila($row);
        const palabraClase = String(data.palabra).toLowerCase() === 'peligro'
            ? 'impresion-modal-warning danger'
            : 'impresion-modal-warning caution';

        $('#modalEtiquetaNombre').text(String(data.nombre).toUpperCase());
        $('#modalEtiquetaPictogramas').html(data.pictogramas.map(tipo => crearPictograma(tipo)).join(''));
        $('#modalEtiquetaPalabra')
            .attr('class', palabraClase)
            .text(String(data.palabra).toUpperCase());

        $('#modalEtiquetaFrasesH').html(data.frasesH.map(frase => crearLineaFraseModal(frase, 'h')).join(''));
        $('#modalEtiquetaFrasesP').html(data.frasesP.map(frase => crearLineaFraseModal(frase, 'p')).join(''));

        $('#modalEtiquetaEmpresa').text(data.empresa);
        $('#modalEtiquetaDireccion').text(data.direccion);
        $('#modalEtiquetaTelefono').text(data.telefono);
        $('#modalEtiquetaEmail').text(data.email);

        $('#modalMetaIdentificador').text(data.identificador);
        $('#modalMetaDepartamento').text(data.departamento);
        $('#modalMetaRegistrado').text(data.registrado);
        $('#modalMetaFecha').text(data.fecha);
        $('#modalMetaObservaciones').text(data.observaciones);
    }

    if (window.consultaEtiquetasDateFilter) {
        const filtros = $.fn.dataTable.ext.search;
        const indice = filtros.indexOf(window.consultaEtiquetasDateFilter);
        if (indice > -1) {
            filtros.splice(indice, 1);
        }
    }

    window.consultaEtiquetasDateFilter = function (settings, data) {
        if (settings.nTable.id !== 'tablaConsultaEtiquetas') {
            return true;
        }

        const fechaFila = data[4];
        const partes = fechaFila.split('/');
        const fechaActualizacion = new Date(`${partes[2]}-${partes[1]}-${partes[0]}T00:00:00`);
        const fechaDesde = obtenerFechaComparable(filtroFechaDesde);
        const fechaHasta = obtenerFechaComparable(filtroFechaHasta);

        if (fechaDesde && fechaActualizacion < fechaDesde) {
            return false;
        }

        if (fechaHasta && fechaActualizacion > fechaHasta) {
            return false;
        }

        return true;
    };

    $.fn.dataTable.ext.search.push(window.consultaEtiquetasDateFilter);

    if ($.fn.DataTable.isDataTable(tableId)) {
        $(tableId).DataTable().destroy();
    }

    const tabla = $(tableId).DataTable({
        pageLength: 5,
        lengthChange: false,
        info: true,
        searching: false,
        order: [[4, 'desc']],
        dom: "t<'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: {
            info: 'Mostrando _START_ a _END_ de _TOTAL_ etiquetas',
            infoEmpty: 'Mostrando 0 a 0 de 0 etiquetas',
            paginate: {
                previous: 'Anterior',
                next: 'Siguiente'
            },
            zeroRecords: 'No se encontraron etiquetas con los filtros seleccionados'
        }
    });

    $('#btnBuscarEtiquetas').on('click', function () {
        filtroFechaDesde = $('#filtroFechaDesde').val();
        filtroFechaHasta = $('#filtroFechaHasta').val();

        tabla.column(0).search($('#filtroSustancia').val());
        tabla.column(2).search($('#filtroDepartamento').val());
        tabla.column(3).search($('#filtroRegistrado').val());
        tabla.draw();

        const $visibleRow = $('#tablaConsultaEtiquetas tbody tr.etiqueta-row:visible').first();
        if ($visibleRow.length) {
            actualizarVistaPrevia($visibleRow);
        } else {
            mostrarVistaVacia();
        }
    });

    $(document).on('click', '.btn-preview-etiqueta', function () {
        const $row = $(this).closest('tr');
        actualizarVistaPrevia($row);
        actualizarModalEtiqueta($row);
        if (modalEtiqueta) {
            modalEtiqueta.show();
        }
    });

    $(document).on('click', '.btn-print-etiqueta', function () {
        actualizarVistaPrevia($(this).closest('tr'));
        window.print();
    });

    $(document).on('click', '#tablaConsultaEtiquetas tbody tr', function (event) {
        if ($(event.target).closest('button').length) return;
        actualizarVistaPrevia($(this));
    });

    actualizarVistaPrevia($('#tablaConsultaEtiquetas tbody tr.etiqueta-row').first());
});
