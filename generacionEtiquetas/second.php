<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Etiquetas de Seguridad</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --color-peligro: #dc3545;
            --color-atencion: #fd7e14;
            --color-primario: #1e3a5f;
            --color-fondo: #f4f7fc;
        }
        body {
            background-color: var(--color-fondo);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .app-header {
            background: linear-gradient(135deg, var(--color-primario), #2c5282);
            color: white;
            padding: 1.2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card-etiqueta {
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        .card-etiqueta:hover {
            transform: translateY(-3px);
        }
        .pictograma-check {
            position: relative;
            display: inline-block;
            margin: 8px;
            text-align: center;
            cursor: pointer;
        }
        .pictograma-check input[type="checkbox"] {
            display: none;
        }
        .pictograma-check .picto-content {
            font-size: 2.5rem;
            padding: 10px;
            border: 2px solid #dee2e6;
            border-radius: 16px;
            transition: all 0.2s;
            display: block;
            min-width: 70px;
        }
        .pictograma-check input:checked + .picto-content {
            border-color: var(--color-primario);
            background-color: #e9f0fa;
            box-shadow: 0 0 10px rgba(30,58,95,0.3);
            transform: scale(1.05);
        }
        .frases-container {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ced4da;
            border-radius: 12px;
            padding: 0.8rem;
            background: white;
        }
        .frase-item {
            margin-bottom: 5px;
        }
        .btn-export {
            border-radius: 30px;
            padding: 0.6rem 1.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-export:hover {
            transform: scale(1.03);
        }
        .seccion-titulo {
            border-left: 6px solid var(--color-primario);
            padding-left: 1rem;
            margin-bottom: 1.5rem;
        }
        @media print {
            body * {
                visibility: hidden;
            }
            #seccion-impresion, #seccion-impresion * {
                visibility: visible;
            }
            #seccion-impresion {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 20px;
                background: white;
            }
            .no-imprimir {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="app-header text-center">
    <div class="container">
        <h2 class="mb-0"><i class="fa-solid fa-flask-vial me-2"></i>Generador de Etiquetas de Seguridad</h2>
        <p class="mb-0 mt-2 opacity-75">Sistema Globalmente Armonizado (GHS)</p>
    </div>
</div>

<div class="container">
    <!-- Pestañas -->
    <ul class="nav nav-tabs mb-4 no-imprimir" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab1-btn" data-bs-toggle="tab" data-bs-target="#tab1" type="button" role="tab">
                <i class="fa-solid fa-tag me-1"></i> Generar Etiqueta
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab2-btn" data-bs-toggle="tab" data-bs-target="#tab2" type="button" role="tab">
                <i class="fa-solid fa-plus-circle me-1"></i> Nueva Hoja de Seguridad
            </button>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">
        <!-- TAB 1: Generar etiqueta -->
        <div class="tab-pane fade show active" id="tab1" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card card-etiqueta p-3">
                        <div class="card-body">
                            <h5 class="seccion-titulo"><i class="fa-solid fa-list-check me-2"></i>Cargar frases H y P</h5>
                            <label for="selectSustancia" class="form-label fw-bold">Seleccionar sustancia:</label>
                            <select id="selectSustancia" class="form-select form-select-lg mb-3">
                                <option value="" selected disabled>-- Elija una sustancia --</option>
                                <!-- Opciones se llenan desde JS (sustancias de ejemplo + personalizadas) -->
                            </select>
                            <div class="d-grid gap-2 mt-4">
                                <button id="btnExcel" class="btn btn-success btn-export" disabled>
                                    <i class="fa-solid fa-file-excel"></i> Exportar a Excel
                                </button>
                                <button id="btnImprimir" class="btn btn-primary btn-export" disabled>
                                    <i class="fa-solid fa-print"></i> Imprimir / PDF
                                </button>
                                <button id="btnIrNuevaHoja" class="btn btn-outline-secondary btn-export mt-3">
                                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Ir a Nueva Hoja de Seguridad
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card card-etiqueta p-3" id="seccion-impresion">
                        <div class="card-body">
                            <div id="infoEtiqueta" class="text-center text-muted py-5">
                                <i class="fa-solid fa-hand-pointer" style="font-size: 3rem;"></i>
                                <p class="mt-3 fs-5">Seleccione una sustancia para ver su etiqueta</p>
                            </div>
                            <div id="contenidoEtiqueta" style="display: none;">
                                <h4 id="nombreSustancia" class="text-center mb-3 fw-bold"></h4>
                                <div class="text-center mb-3" id="contenedorPictogramas"></div>
                                <div class="text-center mb-4" id="contenedorPalabra"></div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold"><i class="fa-solid fa-triangle-exclamation me-1"></i>Indicaciones de peligro (H)</h6>
                                        <div class="frases-box" id="frasesH"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-bold"><i class="fa-solid fa-shield-haltered me-1"></i>Consejos de prudencia (P)</h6>
                                        <div class="frases-box" id="frasesP"></div>
                                    </div>
                                </div>
                                <div id="datosExtra" class="mt-3 small text-muted">
                                    <p class="mb-1"><strong>Proveedor:</strong> <span id="proveedorLabel"></span></p>
                                    <p><strong>Tel. emergencia:</strong> <span id="telefonoLabel"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: Nueva Hoja de Seguridad -->
        <div class="tab-pane fade" id="tab2" role="tabpanel">
            <div class="card card-etiqueta p-4">
                <h4 class="seccion-titulo"><i class="fa-solid fa-file-circle-plus me-2"></i>Crear nueva etiqueta</h4>
                <form id="formNuevaEtiqueta" class="row g-3">
                    <!-- Nombre de la sustancia -->
                    <div class="col-12">
                        <label for="nombreProducto" class="form-label fw-bold">Nombre del producto / sustancia *</label>
                        <input type="text" class="form-control form-control-lg" id="nombreProducto" placeholder="Ej: Alcohol isopropílico 70%" required>
                    </div>

                    <!-- Palabra de advertencia (adelante / peligroso → Peligro / Atención) -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Palabra de advertencia *</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="palabra" id="palabraPeligro" value="Peligro" required>
                                <label class="form-check-label" for="palabraPeligro">
                                    <span class="badge bg-danger fs-6">Peligro</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="palabra" id="palabraAtencion" value="Atención">
                                <label class="form-check-label" for="palabraAtencion">
                                    <span class="badge bg-warning text-dark fs-6">Atención</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Pictogramas -->
                    <div class="col-12">
                        <label class="form-label fw-bold">Pictogramas de peligro (selecciona los que apliquen)</label>
                        <div class="d-flex flex-wrap gap-2" id="contenedorPictogramasCheck">
                            <!-- Se generan con JS -->
                        </div>
                    </div>

                    <!-- Frases H -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Frases H (indicaciones de peligro)</label>
                        <div class="frases-container" id="listaFrasesH">
                            <!-- Checkboxes generados con JS -->
                        </div>
                    </div>

                    <!-- Frases P -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Frases P (consejos de prudencia)</label>
                        <div class="frases-container" id="listaFrasesP">
                            <!-- Checkboxes generados con JS -->
                        </div>
                    </div>

                    <!-- Información adicional -->
                    <div class="col-md-6">
                        <label for="proveedor" class="form-label fw-bold">Proveedor / Fabricante</label>
                        <input type="text" class="form-control" id="proveedor" placeholder="Ej: Químicos Ejemplo S.A." value="Químicos Ejemplo S.A.">
                    </div>
                    <div class="col-md-6">
                        <label for="telefonoEmergencia" class="form-label fw-bold">Teléfono de emergencia</label>
                        <input type="text" class="form-control" id="telefonoEmergencia" placeholder="+52 55 1234 5678" value="+52 55 1234 5678">
                    </div>

                    <!-- Botones -->
                    <div class="col-12 d-flex gap-3 mt-3">
                        <button type="submit" class="btn btn-primary btn-export">
                            <i class="fa-solid fa-floppy-disk"></i> Guardar y usar en etiqueta
                        </button>
                        <button type="button" id="btnLimpiarForm" class="btn btn-outline-secondary btn-export">
                            <i class="fa-solid fa-eraser"></i> Limpiar formulario
                        </button>
                        <button type="button" id="btnVolverTab1_2" class="btn btn-outline-primary btn-export ms-auto">
                            <i class="fa-solid fa-arrow-left"></i> Volver a Generar Etiqueta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS + Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
    $(document).ready(function () {

        // ==================== CATÁLOGOS BASE ====================
        // Catálogo de frases H (código + texto oficial)
        const catalogoH = [
            {code:"H200", text:"Explosivo inestable."},
            {code:"H225", text:"Líquido y vapores muy inflamables."},
            {code:"H226", text:"Líquidos y vapores inflamables."},
            {code:"H271", text:"Puede provocar un incendio o una explosión; muy comburente."},
            {code:"H302", text:"Nocivo en caso de ingestión."},
            {code:"H314", text:"Provoca quemaduras graves en la piel y lesiones oculares graves."},
            {code:"H319", text:"Provoca irritación ocular grave."},
            {code:"H335", text:"Puede irritar las vías respiratorias."},
            {code:"H336", text:"Puede provocar somnolencia o vértigo."},
            {code:"H410", text:"Muy tóxico para los organismos acuáticos, con efectos nocivos duraderos."}
        ];

        const catalogoP = [
            {code:"P210", text:"Mantener alejado de fuentes de calor, chispas, llama abierta o superficies calientes. No fumar."},
            {code:"P233", text:"Mantener el recipiente herméticamente cerrado."},
            {code:"P280", text:"Llevar guantes/prendas/gafas/máscara de protección."},
            {code:"P301+P330+P331", text:"EN CASO DE INGESTIÓN: Enjuagarse la boca. NO provocar el vómito."},
            {code:"P305+P351+P338", text:"EN CASO DE CONTACTO CON LOS OJOS: Aclarar cuidadosamente con agua durante varios minutos..."},
            {code:"P403+P235", text:"Almacenar en un lugar bien ventilado. Mantener fresco."},
            {code:"P501", text:"Eliminar el contenido/el recipiente en un punto de recogida de residuos especiales."}
        ];

        // Pictogramas disponibles (emoji + clave interna)
        const pictogramasDisponibles = [
            {id:"explosivo", emoji:"💥", label:"Explosivo"},
            {id:"inflamable", emoji:"🔥", label:"Inflamable"},
            {id:"comburente", emoji:"🟠", label:"Comburente"},
            {id:"toxicidad_aguda", emoji:"☠️", label:"Toxicidad aguda"},
            {id:"corrosivo", emoji:"⚠️", label:"Corrosivo"},
            {id:"irritante", emoji:"❗", label:"Irritante / Nocivo"},
            {id:"peligro_salud", emoji:"🫁", label:"Peligro para la salud"},
            {id:"medio_ambiente", emoji:"🌍", label:"Peligro para el medio ambiente"}
        ];

        // ==================== DATOS INICIALES (sustancias de ejemplo + contenedor personalizadas) ====================
        const sustanciasEjemplo = {
            "acido-clorhidrico": {
                nombre: "Ácido Clorhídrico 37%",
                pictogramas: ["☠️", "⚠️"],
                palabra: "Peligro",
                clasePalabra: "peligro",
                frasesH: ["H314: Provoca quemaduras graves en la piel y lesiones oculares graves.", "H335: Puede irritar las vías respiratorias."],
                frasesP: ["P280: Llevar guantes/prendas/gafas/máscara de protección.", "P301+P330+P331: EN CASO DE INGESTIÓN: Enjuagarse la boca. NO provocar el vómito.", "P305+P351+P338: EN CASO DE CONTACTO CON LOS OJOS: Aclarar cuidadosamente con agua durante varios minutos. Quitar las lentes de contacto, si lleva y resulta fácil. Seguir aclarando."],
                proveedor: "Químicos Ejemplo S.A. de C.V.",
                telefono: "+52 55 1234 5678"
            },
            "acetona": {
                nombre: "Acetona",
                pictogramas: ["🔥", "❗"],
                palabra: "Peligro",
                clasePalabra: "peligro",
                frasesH: ["H225: Líquido y vapores muy inflamables.", "H319: Provoca irritación ocular grave.", "H336: Puede provocar somnolencia o vértigo."],
                frasesP: ["P210: Mantener alejado de fuentes de calor... No fumar.", "P305+P351+P338: EN CASO DE CONTACTO CON LOS OJOS: Aclarar cuidadosamente..."],
                proveedor: "Solventes Industriales S.A.",
                telefono: "+52 55 9876 5432"
            },
            "hidroxido-sodio": {
                nombre: "Hidróxido de Sodio (sosa cáustica)",
                pictogramas: ["☠️", "⚠️"],
                palabra: "Peligro",
                clasePalabra: "peligro",
                frasesH: ["H314: Provoca quemaduras graves en la piel y lesiones oculares graves."],
                frasesP: ["P280: Llevar guantes/prendas/gafas/máscara de protección.", "P301+P330+P331: EN CASO DE INGESTIÓN: Enjuagarse la boca. NO provocar el vómito."],
                proveedor: "Álcalis y Derivados S.L.",
                telefono: "+34 91 112 2334"
            }
        };
        // Objeto para almacenar las hojas creadas por el usuario
        let sustanciasPersonalizadas = {};

        // ==================== FUNCIÓN PARA POBLAR SELECTOR Y MERGEAR ====================
        function obtenerTodasLasSustancias() {
            return { ...sustanciasEjemplo, ...sustanciasPersonalizadas };
        }

        function llenarSelector() {
            const $select = $('#selectSustancia');
            $select.empty();
            $select.append('<option value="" selected disabled>-- Elija una sustancia --</option>');
            const todas = obtenerTodasLasSustancias();
            // Ordenar alfabéticamente por nombre
            const ordenadas = Object.entries(todas).sort((a,b) => a[1].nombre.localeCompare(b[1].nombre));
            ordenadas.forEach(([clave, data]) => {
                $select.append(`<option value="${clave}">${data.nombre}</option>`);
            });
        }

        // Llamada inicial
        llenarSelector();

        // ==================== GENERAR CHECKBOXES EN TAB 2 ====================
        function generarCheckboxesPictogramas() {
            const $cont = $('#contenedorPictogramasCheck');
            pictogramasDisponibles.forEach(picto => {
                $cont.append(`
                    <label class="pictograma-check">
                        <input type="checkbox" name="pictogramas[]" value="${picto.emoji}" data-id="${picto.id}">
                        <span class="picto-content" title="${picto.label}">${picto.emoji}</span>
                    </label>
                `);
            });
        }
        generarCheckboxesPictogramas();

        function generarCheckboxesFrases(containerId, catalogo, prefix) {
            const $cont = $(containerId);
            catalogo.forEach(frase => {
                $cont.append(`
                    <div class="form-check frase-item">
                        <input class="form-check-input" type="checkbox" name="${prefix}[]" value="${frase.code}: ${frase.text}" id="${prefix}_${frase.code}">
                        <label class="form-check-label" for="${prefix}_${frase.code}"><strong>${frase.code}</strong>: ${frase.text}</label>
                    </div>
                `);
            });
        }
        generarCheckboxesFrases('#listaFrasesH', catalogoH, 'frasesH');
        generarCheckboxesFrases('#listaFrasesP', catalogoP, 'frasesP');

        // ==================== GUARDAR NUEVA ETIQUETA ====================
        $('#formNuevaEtiqueta').on('submit', function(e) {
            e.preventDefault();

            const nombre = $('#nombreProducto').val().trim();
            const palabra = $('input[name="palabra"]:checked').val();
            const proveedor = $('#proveedor').val().trim() || 'No especificado';
            const telefono = $('#telefonoEmergencia').val().trim() || 'No especificado';

            // Pictogramas seleccionados
            const pictogramas = [];
            $('input[name="pictogramas[]"]:checked').each(function() {
                pictogramas.push($(this).val());
            });

            // Frases H seleccionadas
            const frasesH = [];
            $('input[name="frasesH[]"]:checked').each(function() {
                frasesH.push($(this).val());
            });

            // Frases P seleccionadas
            const frasesP = [];
            $('input[name="frasesP[]"]:checked').each(function() {
                frasesP.push($(this).val());
            });

            if (!nombre || !palabra || pictogramas.length === 0 || frasesH.length === 0) {
                alert('Por favor complete al menos: nombre, palabra de advertencia, al menos un pictograma y una frase H.');
                return;
            }

            // Generar clave única (basada en nombre, reemplazando espacios y caracteres)
            let clave = nombre.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
            // Si ya existe, añadir número
            if (obtenerTodasLasSustancias()[clave]) {
                let contador = 1;
                while (obtenerTodasLasSustancias()[`${clave}-${contador}`]) {
                    contador++;
                }
                clave = `${clave}-${contador}`;
            }

            const clasePalabra = palabra === 'Peligro' ? 'peligro' : 'atencion';

            // Guardar en el objeto de personalizadas
            sustanciasPersonalizadas[clave] = {
                nombre: nombre,
                pictogramas: pictogramas,
                palabra: palabra,
                clasePalabra: clasePalabra,
                frasesH: frasesH,
                frasesP: frasesP,
                proveedor: proveedor,
                telefono: telefono
            };

            // Actualizar selector y seleccionar la nueva
            llenarSelector();
            $('#selectSustancia').val(clave).trigger('change');

            // Cambiar a la pestaña 1
            const tab1Trigger = new bootstrap.Tab($('#tab1-btn')[0]);
            tab1Trigger.show();

            // Reiniciar formulario
            $('#formNuevaEtiqueta')[0].reset();
        });

        // Botón limpiar
        $('#btnLimpiarForm').on('click', function() {
            $('#formNuevaEtiqueta')[0].reset();
        });

        // ==================== MANEJO DE LA PESTAÑA 1 (VISUALIZACIÓN, EXCEL, PDF) ====================
        let sustanciaActual = null;

        $('#selectSustancia').on('change', function() {
            const clave = $(this).val();
            const todas = obtenerTodasLasSustancias();
            if (clave && todas[clave]) {
                sustanciaActual = todas[clave];
                cargarEtiqueta(sustanciaActual);
                $('#btnExcel, #btnImprimir').prop('disabled', false);
            } else {
                sustanciaActual = null;
                $('#btnExcel, #btnImprimir').prop('disabled', true);
                $('#infoEtiqueta').show();
                $('#contenidoEtiqueta').hide();
            }
        });

        function cargarEtiqueta(data) {
            $('#infoEtiqueta').hide();
            $('#contenidoEtiqueta').show();

            $('#nombreSustancia').text(data.nombre);

            // Pictogramas
            let pictosHtml = '';
            data.pictogramas.forEach(p => {
                pictosHtml += `<span class="pictograma" title="Pictograma GHS">${p}</span>`;
            });
            $('#contenedorPictogramas').html(pictosHtml);

            // Palabra de advertencia
            const claseBadge = data.clasePalabra === 'peligro' ? 'bg-danger' : 'bg-warning text-dark';
            $('#contenedorPalabra').html(`<span class="badge ${claseBadge} fs-5 px-4 py-2 rounded-pill">${data.palabra}</span>`);

            // Frases H
            let hList = '<ul class="list-unstyled mb-0">';
            data.frasesH.forEach(h => hList += `<li class="mb-1">• ${h}</li>`);
            hList += '</ul>';
            $('#frasesH').html(hList);

            // Frases P
            let pList = '<ul class="list-unstyled mb-0">';
            data.frasesP.forEach(p => pList += `<li class="mb-1">• ${p}</li>`);
            pList += '</ul>';
            $('#frasesP').html(pList);

            // Datos extra
            $('#proveedorLabel').text(data.proveedor || 'No especificado');
            $('#telefonoLabel').text(data.telefono || 'No especificado');
        }

        // Exportar Excel
        $('#btnExcel').on('click', function() {
            if (!sustanciaActual) return;
            let tabla = '<table border="1">';
            tabla += '<tr><td colspan="2"><strong>ETIQUETA DE SEGURIDAD (GHS)</strong></td></tr>';
            tabla += `<tr><td><strong>Nombre de la sustancia:</strong></td><td>${sustanciaActual.nombre}</td></tr>`;
            tabla += `<tr><td><strong>Palabra de advertencia:</strong></td><td>${sustanciaActual.palabra}</td></tr>`;
            tabla += `<tr><td><strong>Pictogramas:</strong></td><td>${sustanciaActual.pictogramas.join(' ')}</td></tr>`;
            tabla += `<tr><td><strong>Frases H:</strong></td><td>${sustanciaActual.frasesH.join('<br>')}</td></tr>`;
            tabla += `<tr><td><strong>Frases P:</strong></td><td>${sustanciaActual.frasesP.join('<br>')}</td></tr>`;
            tabla += `<tr><td><strong>Proveedor:</strong></td><td>${sustanciaActual.proveedor || 'N/A'}</td></tr>`;
            tabla += `<tr><td><strong>Tel. emergencia:</strong></td><td>${sustanciaActual.telefono || 'N/A'}</td></tr>`;
            tabla += '</table>';
            const blob = new Blob([tabla], { type: 'application/vnd.ms-excel' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `etiqueta_${sustanciaActual.nombre.replace(/\s+/g, '_')}.xls`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });

        // Imprimir / PDF
        $('#btnImprimir').on('click', function() {
            window.print();
        });

        // Navegación entre pestañas
        $('#btnIrNuevaHoja').on('click', () => {
            new bootstrap.Tab($('#tab2-btn')[0]).show();
        });
        $('#btnVolverTab1_2').on('click', () => {
            new bootstrap.Tab($('#tab1-btn')[0]).show();
        });
        $('#tab2-btn').on('click', function() {
            new bootstrap.Tab(this).show();
        });
        $('#tab1-btn').on('click', function() {
            new bootstrap.Tab(this).show();
        });
    });
</script>
</body>
</html>