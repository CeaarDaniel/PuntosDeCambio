<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Etiquetas de Seguridad</title>
        <!-- Bootstrap -->
        <link href="./css/bootstrap.min.css" rel="stylesheet">
        
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="./css/bootstrap-icons.min.css" />

        <!-- Font Awesome (para íconos) -->
        <link href="./css/all.min.css" rel="stylesheet">

        <!--Libreria Jquery --> 
        <script src="./scripts/jquery-3.7.1.min.js"></script>

        <!--Data table -->
        <link href="./DataTables/datatables.min.css" rel="stylesheet">

        <!-- Custom CSS -->
        <link rel="stylesheet" href="./css/style.css">
</head>
<body>

    <!-- HEADER-->
    <?php include('./header.php');?>

    <!-- CONTENIDO PRINCIPAL-->
        <div id="main-content">
            <div id="content-area" class="animacion">
                <?php include('./pages/menuIndex.php') ?>
            </div>
        </div>
    <!--FIN CONTENIDO PRINCIPAL -->


    <!--
        <ul class="nav nav-tabs mb-4 no-imprimir" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab1-btn" data-bs-toggle="tab" data-bs-target="#tab1" type="button" role="tab">
                    <i class="fa-solid fa-tag me-1"></i> Imprimir/Consultar etiquetas
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab2-btn" data-bs-toggle="tab" data-bs-target="#tab2" type="button" role="tab">
                    <i class="fa-solid fa-plus-circle me-1"></i> Generar Etiqueta
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab3-btn" data-bs-toggle="tab" data-bs-target="#tab3" type="button" role="tab">
                    <i class="fa-solid fa-database me-1"></i> Administrar Catálogos
                </button>
            </li>
        </ul>
    -->
    <!--
        <div class="tab-content" id="myTabContent">
            <!-- ==================== TAB 1: GENERAR ETIQUETA ==================== --
            <div class="tab-pane fade show active" id="tab1" role="tabpanel">
                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="card card-etiqueta p-3">
                            <div class="card-body">
                                <h5 class="seccion-titulo"><i class="fa-solid fa-list-check me-2"></i>Cargar frases H y P</h5>
                                <label for="selectSustancia" class="form-label fw-bold">Seleccionar sustancia:</label>
                                <select id="selectSustancia" class="form-select form-select-lg mb-3">
                                    <option value="" selected disabled>-- Elija una sustancia --</option>
                                </select>
                                <div class="d-grid gap-2 mt-4">
                                    <button id="btnExcel" class="btn btn-success btn-export" disabled>
                                        <i class="fa-solid fa-file-excel"></i> Exportar a Excel
                                    </button>
                                    <button id="btnImprimir" class="btn btn-primary btn-export" disabled>
                                        <i class="fa-solid fa-print"></i> Imprimir / PDF
                                    </button>
                                    <button id="btnIrNuevaHoja" class="btn btn-outline-secondary btn-export mt-3">
                                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Ir a Nueva Hoja
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

            <!-- ==================== TAB 2: NUEVA HOJA ==================== --
            <div class="tab-pane fade" id="tab2" role="tabpanel">
                <div class="card card-etiqueta p-4">
                    <h4 class="seccion-titulo"><i class="fa-solid fa-file-circle-plus me-2"></i>Crear nueva etiqueta</h4>
                    <form id="formNuevaEtiqueta" class="row g-3">
                        <div class="col-12">
                            <label for="nombreProducto" class="form-label fw-bold">Nombre del producto / sustancia *</label>
                            <input type="text" class="form-control form-control-lg" id="nombreProducto" placeholder="Ej: Alcohol isopropílico 70%" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Palabra de advertencia *</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="palabra" id="palabraPeligro" value="Peligro" required>
                                    <label class="form-check-label" for="palabraPeligro"><span class="badge bg-danger fs-6">Peligro</span></label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="palabra" id="palabraAtencion" value="Atención">
                                    <label class="form-check-label" for="palabraAtencion"><span class="badge bg-warning text-dark fs-6">Atención</span></label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Pictogramas de peligro</label>
                            <div class="d-flex flex-wrap gap-2" id="contenedorPictogramasCheck"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Frases H</label>
                            <div class="frases-container" id="listaFrasesH"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Frases P</label>
                            <div class="frases-container" id="listaFrasesP"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="proveedor" class="form-label fw-bold">Proveedor / Fabricante</label>
                            <input type="text" class="form-control" id="proveedor" placeholder="Químicos Ejemplo S.A." value="Químicos Ejemplo S.A.">
                        </div>
                        <div class="col-md-6">
                            <label for="telefonoEmergencia" class="form-label fw-bold">Teléfono de emergencia</label>
                            <input type="text" class="form-control" id="telefonoEmergencia" placeholder="+52 55 1234 5678" value="+52 55 1234 5678">
                        </div>
                        <div class="col-12 d-flex gap-3 mt-3">
                            <button type="submit" class="btn btn-primary btn-export"><i class="fa-solid fa-floppy-disk"></i> Guardar y usar en etiqueta</button>
                            <button type="button" id="btnLimpiarForm" class="btn btn-outline-secondary btn-export"><i class="fa-solid fa-eraser"></i> Limpiar formulario</button>
                            <button type="button" id="btnVolverTab1_2" class="btn btn-outline-primary btn-export ms-auto"><i class="fa-solid fa-arrow-left"></i> Volver a Generar Etiqueta</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ==================== TAB 3: ADMINISTRAR CATÁLOGOS ==================== --
            <div class="tab-pane fade" id="tab3" role="tabpanel">
                <div class="card card-etiqueta p-4">
                    <h4 class="seccion-titulo"><i class="fa-solid fa-database me-2"></i>Administrar Catálogos</h4>
                    <p class="text-muted mb-4">Agregue, elimine o restaure los pictogramas, frases H y frases P que aparecen en el formulario de creación de etiquetas.</p>

                    <!-- Acordeón de administración --
                    <div class="accordion" id="accordionAdmin">
                        <!-- Pictogramas --
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panPictogramas">
                                    <i class="fa-solid fa-image me-2"></i> Pictogramas
                                </button>
                            </h2>
                            <div id="panPictogramas" class="accordion-collapse collapse show" data-bs-parent="#accordionAdmin">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover tabla-admin" id="tablaPictogramas">
                                            <thead><tr><th>Emoji</th><th>Etiqueta</th><th style="width:100px"></th></tr></thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <form id="formAgregarPictograma" class="row g-2 mt-3">
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" id="nuevoEmoji" placeholder="Emoji (ej. 🔥)" required>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" id="nuevoLabelPicto" placeholder="Etiqueta (ej. Inflamable)" required>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-plus"></i> Agregar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Frases H --
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panFrasesH">
                                    <i class="fa-solid fa-triangle-exclamation me-2"></i> Frases H (Indicaciones de peligro)
                                </button>
                            </h2>
                            <div id="panFrasesH" class="accordion-collapse collapse" data-bs-parent="#accordionAdmin">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover tabla-admin" id="tablaFrasesH">
                                            <thead><tr><th>Código</th><th>Texto</th><th style="width:100px"></th></tr></thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <form id="formAgregarFraseH" class="row g-2 mt-3">
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" id="nuevoCodigoH" placeholder="Código (ej. H302)" required>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" id="nuevoTextoH" placeholder="Texto de la frase H" required>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-plus"></i> Agregar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Frases P --
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panFrasesP">
                                    <i class="fa-solid fa-shield-haltered me-2"></i> Frases P (Consejos de prudencia)
                                </button>
                            </h2>
                            <div id="panFrasesP" class="accordion-collapse collapse" data-bs-parent="#accordionAdmin">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover tabla-admin" id="tablaFrasesP">
                                            <thead><tr><th>Código</th><th>Texto</th><th style="width:100px"></th></tr></thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <form id="formAgregarFraseP" class="row g-2 mt-3">
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" id="nuevoCodigoP" placeholder="Código (ej. P102)" required>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" id="nuevoTextoP" placeholder="Texto de la frase P" required>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-plus"></i> Agregar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button id="btnRestaurarCatalogos" class="btn btn-outline-warning btn-export mt-4">
                        <i class="fa-solid fa-rotate-left"></i> Restaurar valores predeterminados
                    </button>
                </div>
            </div>
        </div> 
    -->


  <!-- Bootstrap JS -->
  <script src="./scripts/bootstrap.bundle.min.js"></script>

  <!-- DataTables -->
  <script src="./DataTables/datatables.min.js"></script>

<!--Custom css -->
  <script src="./scripts/index.js"></script>
  <!--
  <script src="./scripts/etiquetas.js"></script> 
  -->
</body>
</html>