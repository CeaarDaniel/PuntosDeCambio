$(document).ready(function () {
    const pictogramasDefault = [
        { value: 'inflamable', label: 'Inflamable', icon: 'fa-fire-flame-curved' },
        { value: 'corrosivo', label: 'Corrosivo', icon: 'fa-flask-vial' },
        { value: 'toxico', label: 'Toxicidad aguda', icon: 'fa-skull-crossbones' },
        { value: 'irritante', label: 'Irritante / Nocivo', icon: 'fa-exclamation' },
        { value: 'salud', label: 'Peligro para la salud', icon: 'fa-user-shield' },
        { value: 'ambiente', label: 'Peligro ambiental', icon: 'fa-tree' }
    ];

    const eppDefault = [
        { value: 'lentes', label: 'Lentes de seguridad', icon: 'fa-glasses' },
        { value: 'guantes', label: 'Guantes de proteccion', icon: 'fa-hand' },
        { value: 'careta', label: 'Careta facial', icon: 'fa-user-shield' },
        { value: 'bata', label: 'Bata o mandil', icon: 'fa-shirt' },
        { value: 'respirador', label: 'Respirador', icon: 'fa-head-side-mask' },
        { value: 'casco', label: 'Casco', icon: 'fa-helmet-safety' }
    ];

    const frasesHDefault = [
        { code: 'H225', text: 'Liquido y vapores muy inflamables.' },
        { code: 'H290', text: 'Puede ser corrosivo para los metales.' },
        { code: 'H302', text: 'Nocivo en caso de ingestion.' },
        { code: 'H314', text: 'Provoca quemaduras graves en la piel y lesiones oculares graves.' },
        { code: 'H319', text: 'Provoca irritacion ocular grave.' },
        { code: 'H335', text: 'Puede irritar las vias respiratorias.' },
        { code: 'H336', text: 'Puede provocar somnolencia o vertigo.' },
        { code: 'H411', text: 'Toxico para los organismos acuaticos, con efectos nocivos duraderos.' }
    ];

    const frasesPDefault = [
        { code: 'P102', text: 'Mantener fuera del alcance de los ninos.' },
        { code: 'P210', text: 'Mantener alejado de fuentes de calor, chispas y llama abierta.' },
        { code: 'P233', text: 'Mantener el recipiente hermeticamente cerrado.' },
        { code: 'P273', text: 'Evitar su liberacion al medio ambiente.' },
        { code: 'P280', text: 'Usar guantes, gafas y equipo de proteccion adecuado.' },
        { code: 'P301+P330+P331', text: 'En caso de ingestion: enjuagar la boca. No provocar el vomito.' },
        { code: 'P305+P351+P338', text: 'En caso de contacto con los ojos: enjuagar con agua varios minutos.' },
        { code: 'P314', text: 'Consultar a un medico si la persona se encuentra mal.' }
    ];

    function obtenerDefinicionPorValor(lista, valor) {
        return lista.find(function (item) {
            return item.value === valor;
        });
    }

    function renderPictogramas() {
        const $contenedor = $('#contenedorPictogramasCheck').empty();

        pictogramasDefault.forEach(function (picto) {
            $contenedor.append(`
                <label class="crear-pictogram-option">
                    <input type="checkbox" name="pictogramas[]" value="${picto.value}">
                    <span class="crear-pictogram-card">
                        <span class="ghs-pictogram">
                            <span class="ghs-pictogram-inner">
                                <i class="fa-solid ${picto.icon}"></i>
                            </span>
                        </span>
                        <span class="crear-pictogram-label">${picto.label}</span>
                    </span>
                </label>
            `);
        });
    }

    function renderEpp() {
        const $contenedor = $('#contenedorEppCheck').empty();

        eppDefault.forEach(function (item) {
            $contenedor.append(`
                <label class="crear-epp-option">
                    <input type="checkbox" name="epp[]" value="${item.value}">
                    <span class="crear-epp-card">
                        <span class="crear-epp-icon">
                            <i class="fa-solid ${item.icon}"></i>
                        </span>
                        <span class="crear-epp-label">${item.label}</span>
                    </span>
                </label>
            `);
        });
    }

    function renderFrases(selector, frases, prefix) {
        const $contenedor = $(selector).empty();

        frases.forEach(function (frase) {
            $contenedor.append(`
                <label class="crear-frase-item">
                    <input class="form-check-input" type="checkbox" name="${prefix}[]" value="${frase.code}: ${frase.text}" data-code="${frase.code}">
                    <span class="crear-frase-content">
                        <strong>${frase.code}</strong>
                        <span>${frase.text}</span>
                    </span>
                </label>
            `);
        });
    }

    function filtrarFrases(inputSelector, itemName) {
        const valor = $(inputSelector).val().toLowerCase().trim();

        $(`input[name="${itemName}[]"]`).each(function () {
            const texto = $(this).closest('.crear-frase-item').text().toLowerCase();
            $(this).closest('.crear-frase-item').toggle(texto.indexOf(valor) > -1);
        });
    }

    function obtenerFrasesSeleccionadas(nombre) {
        return $(`input[name="${nombre}[]"]:checked`).map(function () {
            return $(this).val();
        }).get();
    }

    function crearLineaPreview(frase, tipo) {
        const partes = frase.split(': ');
        const codigo = partes.shift() || '';
        const texto = partes.join(': ');
        return `<p class="mb-2"><strong class="crear-preview-code ${tipo}">${codigo}</strong> ${texto}</p>`;
    }

    function crearPictogramaPreview(valor) {
        const definicion = obtenerDefinicionPorValor(pictogramasDefault, valor);
        const icono = definicion ? definicion.icon : 'fa-triangle-exclamation';

        return `
            <span class="ghs-pictogram">
                <span class="ghs-pictogram-inner">
                    <i class="fa-solid ${icono}"></i>
                </span>
            </span>
        `;
    }

    function crearEppPreview(valor) {
        const definicion = obtenerDefinicionPorValor(eppDefault, valor);
        if (!definicion) return '';

        return `
            <span class="crear-epp-preview-chip" title="${definicion.label}">
                <i class="fa-solid ${definicion.icon}"></i>
            </span>
        `;
    }

    function activarPanel(panelId) {
        $('.crear-tab-btn').removeClass('active');
        $(`.crear-tab-btn[data-panel="${panelId}"]`).addClass('active');

        $('.crear-tab-panel').removeClass('active');
        $(`#${panelId}`).addClass('active');
    }

    function actualizarVistaPrevia() {
        const nombre = $('#nombreProducto').val().trim() || 'Nombre de la sustancia';
        const identificador = $('#identificadorSustancia').val().trim() || 'No especificado';
        const departamento = $('#areaDepartamento').val() || 'No asignado';
        const proveedor = $('#proveedor').val().trim() || 'No especificado';
        const telefono = $('#telefonoEmergencia').val().trim() || 'No especificado';
        const palabra = $('input[name="palabra"]:checked').val() || 'Pendiente';
        const frasesH = obtenerFrasesSeleccionadas('frasesH');
        const frasesP = obtenerFrasesSeleccionadas('frasesP');
        const pictogramas = $('input[name="pictogramas[]"]:checked').map(function () {
            return $(this).val();
        }).get();
        const epp = $('input[name="epp[]"]:checked').map(function () {
            return $(this).val();
        }).get();

        $('#previewNombreHoja').text(nombre);
        $('#previewIdentificadorHoja').text(identificador);
        $('#previewDepartamentoHoja').text(departamento);
        $('#previewProveedorHoja').text(proveedor);
        $('#previewTelefonoHoja').text(telefono);

        $('#previewPalabraAdvertencia')
            .text(palabra)
            .removeClass('danger warning muted')
            .addClass(
                palabra === 'Peligro' ? 'danger' :
                palabra === 'Atencion' ? 'warning' : 'muted'
            );

        if (frasesH.length) {
            $('#previewFrasesHHoja').removeClass('muted').html(frasesH.map(function (frase) {
                return crearLineaPreview(frase, 'danger');
            }).join(''));
        } else {
            $('#previewFrasesHHoja').addClass('muted').html('Selecciona frases H para visualizar esta seccion.');
        }

        if (frasesP.length) {
            $('#previewFrasesPHoja').removeClass('muted').html(frasesP.map(function (frase) {
                return crearLineaPreview(frase, 'primary');
            }).join(''));
        } else {
            $('#previewFrasesPHoja').addClass('muted').html('Selecciona frases P para visualizar esta seccion.');
        }

        if (pictogramas.length) {
            $('#previewPictogramasHoja')
                .removeClass('empty')
                .html(pictogramas.map(function (valor) {
                    return crearPictogramaPreview(valor);
                }).join(''));
        } else {
            $('#previewPictogramasHoja')
                .addClass('empty')
                .html('Selecciona los pictogramas aplicables.');
        }

        if (epp.length) {
            const etiquetas = epp.map(function (valor) {
                const definicion = obtenerDefinicionPorValor(eppDefault, valor);
                return definicion ? definicion.label : valor;
            });

            $('#previewEppHoja')
                .removeClass('empty')
                .html(epp.map(function (valor) {
                    return crearEppPreview(valor);
                }).join(''));

            $('#previewEppTexto').text(etiquetas.join(', '));
        } else {
            $('#previewEppHoja')
                .addClass('empty')
                .html('Selecciona el equipo de proteccion personal requerido.');

            $('#previewEppTexto').text('Los iconos seleccionados apareceran aqui.');
        }
    }

    function limpiarFormulario() {
        $('#formNuevaEtiqueta')[0].reset();
        $('#buscarFraseH').val('');
        $('#buscarFraseP').val('');
        filtrarFrases('#buscarFraseH', 'frasesH');
        filtrarFrases('#buscarFraseP', 'frasesP');
        activarPanel('panel-informacion');
        actualizarVistaPrevia();
    }

    renderPictogramas();
    renderEpp();
    renderFrases('#listaFrasesH', frasesHDefault, 'frasesH');
    renderFrases('#listaFrasesP', frasesPDefault, 'frasesP');
    activarPanel('panel-informacion');
    actualizarVistaPrevia();

    $(document).on('click', '.crear-tab-btn', function () {
        activarPanel($(this).data('panel'));
    });

    $(document).on('input change', '#formNuevaEtiqueta input, #formNuevaEtiqueta select', actualizarVistaPrevia);

    $('#buscarFraseH').on('input', function () {
        filtrarFrases('#buscarFraseH', 'frasesH');
    });

    $('#buscarFraseP').on('input', function () {
        filtrarFrases('#buscarFraseP', 'frasesP');
    });

    $('#btnLimpiarForm').on('click', function () {
        limpiarFormulario();
    });

    $('#formNuevaEtiqueta').on('submit', function (e) {
        e.preventDefault();

        const nombre = $('#nombreProducto').val().trim();
        const departamento = $('#areaDepartamento').val();
        const palabra = $('input[name="palabra"]:checked').val();
        const pictogramas = $('input[name="pictogramas[]"]:checked').length;
        const frasesH = $('input[name="frasesH[]"]:checked').length;
        const frasesP = $('input[name="frasesP[]"]:checked').length;
        const epp = $('input[name="epp[]"]:checked').length;

        if (!nombre || !departamento || !palabra || !pictogramas || !frasesH || !frasesP || !epp) {
            alert('Completa los campos obligatorios: sustancia, area o departamento, palabra de advertencia, al menos un pictograma, una frase H, una frase P y un elemento de EPP.');
            return;
        }

        alert('La hoja de seguridad quedo validada visualmente. Aun no se conecta a guardado en base de datos.');
    });
});
