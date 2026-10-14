<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Etiquetas de Seguridad</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
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
        .pictograma {
            font-size: 2.8rem;
            margin: 0 8px;
            display: inline-block;
        }
        .badge-palabra {
            font-size: 1.3rem;
            padding: 0.5rem 1.4rem;
            border-radius: 30px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .peligro-badge {
            background-color: var(--color-peligro);
            color: white;
        }
        .atencion-badge {
            background-color: var(--color-atencion);
            color: white;
        }
        .frases-box {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1rem;
            max-height: 200px;
            overflow-y: auto;
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
        /* Ocultar elementos en impresión */
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

    <!-- Cabecera -->
    <div class="app-header text-center">
        <div class="container">
            <h2 class="mb-0"><i class="fa-solid fa-flask-vial me-2"></i>Generador de Etiquetas de Seguridad</h2>
            <p class="mb-0 mt-2 opacity-75">Sistema Globalmente Armonizado (GHS)</p>
        </div>
    </div>

    <div class="container">
        <!-- Pestañas (Tabs) -->
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

        <!-- Contenido de las pestañas -->
        <div class="tab-content" id="myTabContent">
            <!-- ==================== TAB 1: GENERAR ETIQUETA ==================== -->
            <div class="tab-pane fade show active" id="tab1" role="tabpanel">
                <div class="row g-4">
                    <!-- Panel de selección -->
                    <div class="col-lg-5">
                        <div class="card card-etiqueta p-3">
                            <div class="card-body">
                                <h5 class="seccion-titulo"><i class="fa-solid fa-list-check me-2"></i>Cargar frases H y P</h5>
                                <label for="selectSustancia" class="form-label fw-bold">Seleccionar sustancia de ejemplo:</label>
                                <select id="selectSustancia" class="form-select form-select-lg mb-3">
                                    <option value="" selected disabled>-- Elija una sustancia --</option>
                                    <option value="acido-clorhidrico">Ácido Clorhídrico 37%</option>
                                    <option value="acetona">Acetona</option>
                                    <option value="hidroxido-sodio">Hidróxido de Sodio (sosa cáustica)</option>
                                    <option value="etanol">Etanol (alcohol etílico)</option>
                                    <option value="peroxido-hidrogeno">Peróxido de Hidrógeno 30%</option>
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

                    <!-- Vista previa de la etiqueta -->
                    <div class="col-lg-7">
                        <div class="card card-etiqueta p-3" id="seccion-impresion">
                            <div class="card-body">
                                <div id="infoEtiqueta" class="text-center text-muted py-5">
                                    <i class="fa-solid fa-hand-pointer" style="font-size: 3rem;"></i>
                                    <p class="mt-3 fs-5">Seleccione una sustancia para visualizar su etiqueta</p>
                                </div>
                                <!-- Contenido dinámico (se llena con jQuery) -->
                                <div id="contenidoEtiqueta" style="display: none;">
                                    <h4 id="nombreSustancia" class="text-center mb-3 fw-bold"></h4>
                                    <!-- Pictogramas -->
                                    <div class="text-center mb-3" id="contenedorPictogramas"></div>
                                    <!-- Palabra de advertencia -->
                                    <div class="text-center mb-4" id="contenedorPalabra"></div>
                                    <!-- Frases H y P en dos columnas -->
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
                                    <!-- Información adicional (para Excel) -->
                                    <div id="datosExtra" class="mt-3 small text-muted">
                                        <p class="mb-1"><strong>Proveedor:</strong> Químicos Ejemplo S.A. de C.V.</p>
                                        <p><strong>Tel. emergencia:</strong> +52 55 1234 5678</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== TAB 2: NUEVA HOJA DE SEGURIDAD (placeholder) ==================== -->
            <div class="tab-pane fade" id="tab2" role="tabpanel">
                <div class="card card-etiqueta p-4 text-center">
                    <h3 class="mt-3"><i class="fa-solid fa-hard-hat"></i> Crear Nueva Hoja de Seguridad</h3>
                    <p class="lead">Esta sección estará disponible en el siguiente paso.<br>Aquí podrás definir nombre, frases H, frases P, palabra de advertencia y todos los datos de la FDS.</p>
                    <button id="btnVolverTab1" class="btn btn-outline-primary btn-export mt-2">
                        <i class="fa-solid fa-arrow-left"></i> Volver a Generar Etiqueta
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $(document).ready(function () {
            
            // ========== BASE DE DATOS DE EJEMPLO ==========
            const sustancias = {
                "acido-clorhidrico": {
                    nombre: "Ácido Clorhídrico 37%",
                    pictogramas: ["☠️", "⚠️"], // tóxico, corrosivo
                    palabra: "Peligro",
                    clasePalabra: "peligro",
                    frasesH: [
                        "H314: Provoca quemaduras graves en la piel y lesiones oculares graves.",
                        "H335: Puede irritar las vías respiratorias."
                    ],
                    frasesP: [
                        "P280: Llevar guantes/prendas/gafas/máscara de protección.",
                        "P301+P330+P331: EN CASO DE INGESTIÓN: Enjuagarse la boca. NO provocar el vómito.",
                        "P305+P351+P338: EN CASO DE CONTACTO CON LOS OJOS: Aclarar cuidadosamente con agua durante varios minutos. Quitar las lentes de contacto, si lleva y resulta fácil. Seguir aclarando."
                    ]
                },
                "acetona": {
                    nombre: "Acetona",
                    pictogramas: ["🔥", "⚠️"],
                    palabra: "Peligro",
                    clasePalabra: "peligro",
                    frasesH: [
                        "H225: Líquido y vapores muy inflamables.",
                        "H319: Provoca irritación ocular grave.",
                        "H336: Puede provocar somnolencia o vértigo."
                    ],
                    frasesP: [
                        "P210: Mantener alejado de fuentes de calor, chispas, llama abierta o superficies calientes. No fumar.",
                        "P305+P351+P338: EN CASO DE CONTACTO CON LOS OJOS: Aclarar cuidadosamente con agua durante varios minutos. Quitar las lentes de contacto, si lleva y resulta fácil. Seguir aclarando."
                    ]
                },
                "hidroxido-sodio": {
                    nombre: "Hidróxido de Sodio (sosa cáustica)",
                    pictogramas: ["☠️", "⚠️"],
                    palabra: "Peligro",
                    clasePalabra: "peligro",
                    frasesH: [
                        "H314: Provoca quemaduras graves en la piel y lesiones oculares graves."
                    ],
                    frasesP: [
                        "P280: Llevar guantes/prendas/gafas/máscara de protección.",
                        "P301+P330+P331: EN CASO DE INGESTIÓN: Enjuagarse la boca. NO provocar el vómito.",
                        "P305+P351+P338: EN CASO DE CONTACTO CON LOS OJOS: Aclarar cuidadosamente con agua durante varios minutos. Quitar las lentes de contacto, si lleva y resulta fácil. Seguir aclarar."
                    ]
                },
                "etanol": {
                    nombre: "Etanol (alcohol etílico)",
                    pictogramas: ["🔥"],
                    palabra: "Peligro",
                    clasePalabra: "peligro",
                    frasesH: [
                        "H225: Líquido y vapores muy inflamables."
                    ],
                    frasesP: [
                        "P210: Mantener alejado de fuentes de calor, chispas, llama abierta o superficies calientes. No fumar.",
                        "P233: Mantener el recipiente herméticamente cerrado."
                    ]
                },
                "peroxido-hidrogeno": {
                    nombre: "Peróxido de Hidrógeno 30%",
                    pictogramas: ["☠️", "⚠️", "🔥"],
                    palabra: "Peligro",
                    clasePalabra: "peligro",
                    frasesH: [
                        "H271: Puede provocar un incendio o una explosión; muy comburente.",
                        "H302: Nocivo en caso de ingestión.",
                        "H314: Provoca quemaduras graves en la piel y lesiones oculares graves."
                    ],
                    frasesP: [
                        "P210: Mantener alejado de fuentes de calor, chispas, llama abierta o superficies calientes. No fumar.",
                        "P280: Llevar guantes/prendas/gafas/máscara de protección.",
                        "P305+P351+P338: EN CASO DE CONTACTO CON LOS OJOS: Aclarar cuidadosamente con agua durante varios minutos. Quitar las lentes de contacto, si lleva y resulta fácil. Seguir aclarar."
                    ]
                }
            };

            // Variable global para almacenar la sustancia seleccionada
            let sustanciaActual = null;

            // ========== MANEJADOR DE SELECCIÓN ==========
            $('#selectSustancia').on('change', function() {
                const clave = $(this).val();
                if (clave && sustancias[clave]) {
                    sustanciaActual = sustancias[clave];
                    cargarEtiqueta(sustanciaActual);
                    // Habilitar botones de exportación e impresión
                    $('#btnExcel, #btnImprimir').prop('disabled', false);
                } else {
                    sustanciaActual = null;
                    $('#btnExcel, #btnImprimir').prop('disabled', true);
                    $('#infoEtiqueta').show();
                    $('#contenidoEtiqueta').hide();
                }
            });

            // Función para poblar la vista previa
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
                const claseBadge = data.clasePalabra === 'peligro' ? 'peligro-badge' : 'atencion-badge';
                $('#contenedorPalabra').html(`<span class="badge badge-palabra ${claseBadge}">${data.palabra}</span>`);

                // Frases H
                let hList = '<ul class="list-unstyled mb-0">';
                data.frasesH.forEach(h => {
                    hList += `<li class="mb-1">• ${h}</li>`;
                });
                hList += '</ul>';
                $('#frasesH').html(hList);

                // Frases P
                let pList = '<ul class="list-unstyled mb-0">';
                data.frasesP.forEach(p => {
                    pList += `<li class="mb-1">• ${p}</li>`;
                });
                pList += '</ul>';
                $('#frasesP').html(pList);
            }

            // ========== EXPORTAR A EXCEL ==========
            $('#btnExcel').on('click', function() {
                if (!sustanciaActual) return;

                // Construir tabla HTML para exportar
                let tabla = '<table border="1">';
                tabla += '<tr><td colspan="2"><strong>ETIQUETA DE SEGURIDAD (GHS)</strong></td></tr>';
                tabla += `<tr><td><strong>Nombre de la sustancia:</strong></td><td>${sustanciaActual.nombre}</td></tr>`;
                tabla += `<tr><td><strong>Palabra de advertencia:</strong></td><td>${sustanciaActual.palabra}</td></tr>`;
                tabla += `<tr><td><strong>Pictogramas:</strong></td><td>${sustanciaActual.pictogramas.join(' ')}</td></tr>`;
                tabla += `<tr><td><strong>Frases H:</strong></td><td>${sustanciaActual.frasesH.join('<br>')}</td></tr>`;
                tabla += `<tr><td><strong>Frases P:</strong></td><td>${sustanciaActual.frasesP.join('<br>')}</td></tr>`;
                tabla += `<tr><td><strong>Proveedor:</strong></td><td>Químicos Ejemplo S.A. de C.V.</td></tr>`;
                tabla += `<tr><td><strong>Tel. emergencia:</strong></td><td>+52 55 1234 5678</td></tr>`;
                tabla += '</table>';

                // Crear blob y descargar como .xls
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

            // ========== IMPRIMIR / PDF ==========
            $('#btnImprimir').on('click', function() {
                window.print();
            });

            // ========== NAVEGACIÓN ENTRE PESTAÑAS ==========
            // Botón para ir a Tab 2 (Nueva Hoja)
            $('#btnIrNuevaHoja').on('click', function() {
                const tab2Trigger = new bootstrap.Tab($('#tab2-btn')[0]);
                tab2Trigger.show();
            });

            // Botón para volver a Tab 1 desde Tab 2
            $('#btnVolverTab1').on('click', function() {
                const tab1Trigger = new bootstrap.Tab($('#tab1-btn')[0]);
                tab1Trigger.show();
            });

            // Si se hace clic en la pestaña "Nueva Hoja" también activa el cambio
            $('#tab2-btn').on('click', function() {
                const tab2 = new bootstrap.Tab(this);
                tab2.show();
            });

        });
    </script>
</body>
</html>