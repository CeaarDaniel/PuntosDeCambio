<?php 
  require_once('../api/conexion.php');
  
  $codigo = !empty($_GET['codigo']) ? urldecode($_GET['codigo']) : '';
  $nombre = !empty($_GET['nombre']) ? urldecode($_GET['nombre']) : '';

  $sql= "SELECT descripcion, encargado_supervisor from SPC_LINEAS WHERE CODIGO_LINEA = :codigo_linea";

  $stmt = $conn->prepare($sql);
  $stmt->bindParam(':codigo_linea', $codigo);
  $stmt->execute();
  $linea = $stmt->fetch(PDO::FETCH_ASSOC);
  $descripcion = !empty($linea['descripcion']) ? $linea['descripcion'] : '';
  $encargado_supervisor = !empty($linea['encargado_supervisor']) ? $linea['encargado_supervisor'] : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Layout de Líneas + Diagramador - Sistema PCM</title>
  
  <!-- Bootstrap -->
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="../css/bootstrap-icons.min.css" />

  <!-- Font Awesome -->
  <link href="../css/all.min.css" rel="stylesheet"> 

  <!-- jQuery -->
  <script src="../scripts/jquery-3.7.1.min.js"></script>

  <!-- Custom Css -->
  <link rel="stylesheet" href="../css/layout.css">

  <!-- DataTable -->
  <link href="../DataTables/datatables.min.css" rel="stylesheet">

  <!-- Estilos adicionales para el diagramador (integrados aquí para no depender de otro archivo) -->
  <style>
    /* Ajustes para el nuevo panel derecho y el SVG */
    .layout-container {
      display: flex;
      height: 100vh;
      overflow: hidden;
    }
    .tools-sidebar {
      width: 90px; /* Un poco más ancho para los nuevos botones */
    }
    .layout-main {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    .tools-panel {
      width: 280px;
      background: #f8fafd;
      border-left: 1px solid #ced4da;
      padding: 15px;
      overflow-y: auto;
      box-shadow: -2px 0 10px rgba(0,0,0,0.05);
    }
    .attribute-panel, .element-list {
      background: rgba(255,255,255,0.7);
      backdrop-filter: blur(4px);
      border-radius: 24px;
      padding: 20px 15px;
      border: 1px solid rgba(255,255,255,0.8);
    }
    .element-list {
      max-height: 400px;
      overflow-y: auto;
    }
    .element-list-item {
      display: flex;
      align-items: center;
      padding: 8px 12px;
      background: white;
      border-radius: 40px;
      margin-bottom: 4px;
      cursor: pointer;
      border: 1px solid transparent;
    }
    .element-list-item:hover {
      background: #e9ecef;
      border-color: #2563eb;
    }
    .element-list-item.selected-in-list {
      background: #dbeafe;
      border-color: #2563eb;
    }
    .element-icon {
      width: 30px;
      text-align: center;
      font-size: 1.2rem;
    }
    .attr-section { display: none; }
    .attr-section.active-section { display: block; }
    .btn-tool {
      border-radius: 60px !important;
      margin-bottom: 5px;
    }
    .rotation-slider { width: 100%; }
    .common-attrs { display: none; }
    #noSelectionMsg { display: block; }

    /* SVG sobre el grid */
    .workspace-grid {
      position: relative;
      width: 2000px;
      height: 1500px;
      transform-origin: 0 0;
      background-image: 
        linear-gradient(#cbd5e1 1px, transparent 1px),
        linear-gradient(90deg, #cbd5e1 1px, transparent 1px);
      background-size: 20px 20px;
    }
    #workspace-svg {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 1;
      pointer-events: visible;
    }
    .station {
      z-index: 2;
      pointer-events: auto;
    }
    .selected {
      filter: drop-shadow(0 0 12px #2563eb) drop-shadow(0 0 4px #1e40af);
      transition: filter 0.15s;
    }
    /* Botones de dibujo en la barra izquierda */
    .drawing-btn {
      background: #2d3b4a;
      border: none;
      border-radius: 30px;
      color: white;
      width: 70px;
      padding: 8px 0;
      margin: 2px 0;
      font-size: 1.2rem;
      font-weight: bold;
      transition: 0.2s;
    }
    .drawing-btn:hover {
      background: #3b4b5e;
      transform: scale(1.05);
    }
  </style>
</head>  
<body>
  <div class="layout-container">

    <!-- MENU LAYAOUT - BARRA IZQUIERDA (original + nuevos botones de dibujo) -->
    <div class="tools-sidebar">
      <!-- Botones originales -->
      <button class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Crear estaciones">
        <span data-bs-toggle="modal" data-bs-target="#modalAgregarEstacion">
          <i class="bi bi-building"></i><span>Crear</span>
        </span>
      </button>     
      <button id="btnMenuAsignar" class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Asignar operador">
        <span data-bs-toggle="modal" data-bs-target="#modalAsignarOperador">
          <i class="bi bi-person-plus"></i><span>Asignar</span>
        </span>
      </button>
      <button class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Editar información de la línea">
        <span data-bs-toggle="modal" data-bs-target="#editarLineaModal">
          <i class="bi bi-pencil card-icon"></i><span>Editar</span>
        </span>
      </button>
      <button id="btnMenuRegistroAs" class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Registro de asistencia">
        <span data-bs-toggle="modal" data-bs-target="#attendanceModal">
          <i class="bi bi-check2-square"></i><span>Asistencia</span>
        </span>
      </button>
      <button id="btnMenuRegiswtroNAD" class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Ver personal disponible">
        <span data-bs-toggle="modal" data-bs-target="#modalPersonalDisponible">
          <i class="bi bi-people"></i><span>Disponibles</span>
        </span>
      </button>
      <button id="historialLayoutBtn" class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Consultar el acomodo del layout en alguna fecha">
        <span data-bs-toggle="modal" data-bs-target="#historialLayoutModal">
          <i class="bi bi-clock-history"></i><span>Historial</span>
        </span>
      </button>

      <hr>

      <!-- NUEVOS BOTONES DE DIBUJO -->
      <button class="drawing-btn" id="addRectFill" title="Rectángulo relleno">▭</button>
      <button class="drawing-btn" id="addRectOutline" title="Rectángulo contorno">▯</button>
      <button class="drawing-btn" id="addCircleFill" title="Círculo relleno">●</button>
      <button class="drawing-btn" id="addCircleOutline" title="Círculo contorno">○</button>
      <button class="drawing-btn" id="addLine" title="Línea">∕</button>
      <button class="drawing-btn" id="addArrow" title="Flecha">⇢</button>
      <button class="drawing-btn" id="addText" title="Texto">T</button>
      <button class="drawing-btn" id="deleteShape" title="Eliminar">✕</button>
    </div>
    <!-- FIN MENU LAYOUT-->

    <!-- Área principal (centro) -->
    <div class="layout-main">
      <div class="layout-header">
        <div>
          <h2 class="layout-title">Línea de Producción <?php echo $nombre?></h2>
          <select class="form-select m-0 py-0 ps-1" name="turnoLayout" id="turnoLayout" style="max-width:100px;">
            <option value="1">Turno 1</option>
            <option value="2">Turno 2</option>
          </select>
          <input type="hidden" id="codigoLinea" value="<?php echo $codigo?>">
          <input type="hidden" id="nombreLinea" value="<?php echo $nombre?>">
        </div>
        <div class="layout-controls">
          <div class="btn-group">
            <div class="zoom-indicator me-3" id="zoomIndicator">100%</div>
            <button class="btn btn-outline-primary btn-sm" id="zoomOutBtn"><i class="bi bi-zoom-out"></i> Alejar</button>
            <button class="btn btn-outline-primary btn-sm" id="zoomInBtn"><i class="bi bi-zoom-in"></i> Acercar</button>
          </div>
          <button class="btn btn-outline-secondary btn-sm" id="snapToGridBtn"><i class="bi bi-arrows-move"></i> Ajustar a cuadrícula</button>
          <button class="btn btn-success btn-sm" id="saveLayoutBtn"><i class="bi bi-floppy"></i> Guardar Layout</button>
        </div>
      </div>
      
      <div class="workspace">
        <div class="workspace-grid" id="workspaceGrid">
          <!-- SVG para dibujo (detrás de las estaciones) -->
          <svg id="workspace-svg" width="100%" height="100%" viewBox="0 0 2000 1500" preserveAspectRatio="none">
            <defs>
              <pattern id="grid" patternUnits="userSpaceOnUse" width="20" height="20">
                <path d="M 20 0 L 0 0 0 20" fill="none" stroke="#cbd5e1" stroke-width="0.8"/>
              </pattern>
              <marker id="arrowMarker" markerWidth="10" markerHeight="10" refX="9" refY="5" orient="auto">
                <polygon points="0 0, 9 5, 0 10" fill="context-stroke" stroke="none" />
              </marker>
            </defs>
            <rect x="0" y="0" width="100%" height="100%" fill="url(#grid)" />
            <g id="shapes-group"></g>
          </svg>
          <!-- Las estaciones se inyectarán aquí con JS -->
        </div>
      </div>
    </div>

    <!-- NUEVO PANEL DERECHO (atributos y lista de elementos) -->
    <div class="tools-panel">
      <ul class="nav nav-tabs mb-2" id="panelTabs">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-attrs">⚙️ Atributos</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-elements">📋 Lista</button></li>
      </ul>
      <div class="tab-content">
        <!-- Panel de atributos (copiado del primer código) -->
        <div class="tab-pane fade show active" id="tab-attrs">
          <div class="attribute-panel">
            <div id="noSelectionMsg" class="text-muted small text-center py-3">Ningún elemento seleccionado</div>
            <!-- Sección común (posición/rotación) -->
            <div class="common-attrs">
              <label class="form-label fw-semibold">Posición X/Y</label>
              <div class="row g-2 mb-2">
                <div class="col-6"><input type="number" class="form-control" id="common-x" placeholder="X" step="1"></div>
                <div class="col-6"><input type="number" class="form-control" id="common-y" placeholder="Y" step="1"></div>
              </div>
              <label class="form-label fw-semibold">Rotación (grados)</label>
              <input type="range" class="rotation-slider" id="common-rotate" min="0" max="360" value="0" step="1">
              <div class="d-flex justify-content-between small-note">
                <span>0°</span><span id="rotateValue">0°</span><span>360°</span>
              </div>
            </div>

            <!-- Rectángulo -->
            <div class="attr-section" id="rect-attrs">
              <label class="form-label">Ancho</label>
              <input type="number" class="form-control mb-2 shape-attr" id="rect-width" data-attr="width" value="100">
              <label class="form-label">Alto</label>
              <input type="number" class="form-control mb-2 shape-attr" id="rect-height" data-attr="height" value="70">
              <label class="form-label">Radio borde (rx, ry)</label>
              <div class="row g-2 mb-2">
                <div class="col-6"><input type="number" class="form-control shape-attr" id="rect-rx" data-attr="rx" value="10"></div>
                <div class="col-6"><input type="number" class="form-control shape-attr" id="rect-ry" data-attr="ry" value="10"></div>
              </div>
              <label class="form-label">Relleno</label>
              <input type="color" class="form-control-color mb-2 shape-attr" id="rect-fill" data-attr="fill" value="#ffaa00">
              <label class="form-label">Borde</label>
              <input type="color" class="form-control-color mb-2 shape-attr" id="rect-stroke" data-attr="stroke" value="#000000">
              <label class="form-label">Grosor borde</label>
              <input type="number" class="form-control shape-attr" id="rect-stroke-width" data-attr="stroke-width" value="2" step="0.5">
            </div>

            <!-- Círculo -->
            <div class="attr-section" id="circle-attrs">
              <label class="form-label">Radio</label>
              <input type="number" class="form-control mb-2 shape-attr" id="circle-r" data-attr="r" value="35">
              <label class="form-label">Relleno</label>
              <input type="color" class="form-control-color mb-2 shape-attr" id="circle-fill" data-attr="fill" value="#44cc88">
              <label class="form-label">Borde</label>
              <input type="color" class="form-control-color mb-2 shape-attr" id="circle-stroke" data-attr="stroke" value="#000000">
              <label class="form-label">Grosor borde</label>
              <input type="number" class="form-control shape-attr" id="circle-stroke-width" data-attr="stroke-width" value="2" step="0.5">
            </div>

            <!-- Línea / Flecha -->
            <div class="attr-section" id="line-attrs">
              <label class="form-label">X1</label>
              <input type="number" class="form-control mb-2 shape-attr" id="line-x1" data-attr="x1">
              <label class="form-label">Y1</label>
              <input type="number" class="form-control mb-2 shape-attr" id="line-y1" data-attr="y1">
              <label class="form-label">X2</label>
              <input type="number" class="form-control mb-2 shape-attr" id="line-x2" data-attr="x2">
              <label class="form-label">Y2</label>
              <input type="number" class="form-control mb-2 shape-attr" id="line-y2" data-attr="y2">
              <label class="form-label">Color</label>
              <input type="color" class="form-control-color mb-2 shape-attr" id="line-stroke" data-attr="stroke" value="#333333">
              <label class="form-label">Grosor</label>
              <input type="number" class="form-control shape-attr" id="line-stroke-width" data-attr="stroke-width" value="3" step="0.5">
              <div class="badge-info mt-2 text-center" id="arrow-indicator" style="display: none;">🞂 Flecha activa</div>
            </div>

            <!-- Texto -->
            <div class="attr-section" id="text-attrs">
              <label class="form-label">Contenido</label>
              <input type="text" class="form-control mb-2 shape-attr" id="text-content" data-attr="content" value="Texto">
              <label class="form-label">Fuente</label>
              <select class="form-select mb-2 shape-attr" id="text-font-family" data-attr="font-family">
                <option value="Arial">Arial</option>
                <option value="Verdana">Verdana</option>
                <option value="Courier New">Courier New</option>
                <option value="Georgia">Georgia</option>
              </select>
              <label class="form-label">Tamaño</label>
              <input type="number" class="form-control mb-2 shape-attr" id="text-font-size" data-attr="font-size" value="20">
              <label class="form-label">Color</label>
              <input type="color" class="form-control-color mb-2 shape-attr" id="text-fill" data-attr="fill" value="#000000">
              <label class="form-label">Borde</label>
              <input type="color" class="form-control-color mb-2 shape-attr" id="text-stroke" data-attr="stroke" value="#cccccc">
              <label class="form-label">Grosor borde</label>
              <input type="number" class="form-control shape-attr" id="text-stroke-width" data-attr="stroke-width" value="0.5" step="0.5">
            </div>
          </div>
        </div>
        <!-- Lista de elementos SVG -->
        <div class="tab-pane fade" id="tab-elements">
          <div class="element-list" id="elementList"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- ========== MODALES ORIGINALES (sin cambios) ========== -->
  <!-- Modal alerta/error -->
  <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content shadow-lg border-0" style="border-radius: 15px;">
        <div class="modal-header text-white" style="background: linear-gradient(135deg, #dc3545, #dc3545); border-top-left-radius: 15px; border-top-right-radius: 15px;">
          <h5 class="modal-title d-flex align-items-center gap-2" id="errorModalLabel" style="font-size: clamp(18px, 2vw, 22px);">
            <i class="bi bi-exclamation-octagon-fill fs-3"></i>Error en la asignación
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center p-2">
          <p class="text-muted" style="font-size: clamp(14px, 2vw, 18px);">
            No es posible asignar el operador a esta estación ya que no cuenta con registro para la capacitación o certificado requerido para el proceso.
          </p>
          <div class="my-2"><i class="bi bi-x-circle text-danger" style="font-size: clamp(45px, 6vw, 70px);"></i></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Agregar Estación -->
  <div class="modal fade" id="modalAgregarEstacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Agregar nueva estación</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form class="form-body" id="stationForm">
            <!-- ... (contenido original) ... -->
            <div class="form-section">
              <h3 class="section-title"><i class="bi bi-cpu"></i>Información de la Estación</h3>
              <div class="mb-3">
                <label for="nombreEstacion" class="form-label required-field"><i class="bi bi-tag"></i>Nombre de la Estación/Proceso</label>
                <input type="text" class="form-control form-control-custom" id="nombreEstacion" placeholder="Ej: Moldeo De Uretano Y" required>
                <div class="form-help">Nombre descriptivo para identificar la estación o proceso</div>
              </div>
            </div>
            <div class="form-section">
              <h3 class="section-title"><i class="bi bi-chat-left-text"></i>Descripción y Comentarios</h3>
              <div class="mb-3">
                <label for="stationdescripcion" class="form-label"><i class="bi bi-pencil"></i>Comentarios/Descripción</label>
                <textarea class="form-control form-control-custom form-textarea" id="stationdescripcion" placeholder="Describe las actividades, procedimientos específicos..." rows="4"></textarea>
                <div class="form-help">Opcional: Detalla el proceso, herramientas utilizadas o instrucciones especiales</div>
              </div>
            </div>
            <div class="form-section">
              <h3 class="section-title"><i class="bi bi-award"></i>Requerimientos de Certificación</h3>
              <div class="mb-4">
                <label for="requiereCertificacion" class="form-label required-field">¿Requiere certificación?</label>
                <select class="form-control form-control-custom select" id="requiereCertificacion" required>
                  <option value="">--- SELECCIONE UNA OPCION ---</option>
                  <option value="0">NO</option>
                  <option value="1">SI</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="certificacion" class="form-label"><i class="bi bi-shield-check"></i>Certificación/Capacitación Requerida</label>
                <select class="form-control form-control-custom" id="certificacion">
                  <option value="">Selecciona una certificación...</option>
                  <option value="cert-proceso-a">Proceso A</option>
                  <option value="cert-proceso-b">Proceso B</option>
                  <option value="cert-proceso-c">Proceso C</option>
                  <option value="cert-calidad">Control de Calidad</option>
                  <option value="cert-seguridad">Seguridad Industrial</option>
                  <option value="cert-maquinaria">Operación de Maquinaria Especializada</option>
                  <option value="none">No requiere certificación</option>
                </select>
                <div class="form-help">Selecciona la certificación mínima requerida para operar esta estación</div>
              </div>
            </div>
          </form>
          <div class="d-flex justify-content-end mt-2">
            <button type="button" class="btn btn-secondary mx-2" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Cancelar</button>
            <button id="btnGuardarEstacion" type="button" class="btn btn-primary-custom mx-2"><i class="bi bi-check-circle"></i>Guardar</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Asignar Operador -->
  <div class="modal fade" id="modalAsignarOperador" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Asignar operador a una estación</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form class="form-body" id="assignmentForm">
            <!-- ... (contenido original) ... -->
            <div class="form-section">
              <h3 class="section-title"><i class="bi bi-person-plus"></i>Asignar Operador a Estación</h3>
              <p class="text-muted mb-4">Complete la información para asignar un operador a una estación específica</p>
            </div>
            <div class="form-section">
              <h4 class="section-subtitle"><i class="bi bi-person-badge"></i>Datos del Operador</h4>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="nomina" class="form-label required-field"><i class="bi bi-clock"></i>No. Reloj / ID Empleado</label>
                  <div class="input-group-custom">
                    <input type="number" min="0" step="1" class="form-control form-control-custom" id="nominaModalAsignar" placeholder="Ej: EMP-0256" required>
                    <button type="button" class="input-icon" id="searchEmployee"><i class="bi bi-search"></i></button>
                  </div>
                  <div class="form-help">Ingresa el número de reloj o ID único del empleado</div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="nombre" class="form-label required-field"><i class="bi bi-person"></i>Nombre del Operador</label>
                  <div class="input-group-custom">
                    <input type="text" class="form-control form-control-custom" id="nombreModalAsignar" placeholder="Selecciona o busca un operador" readonly>
                    <button type="button" class="input-icon"><i class="bi bi-people"></i></button>
                  </div>
                </div>
                <div class="col-12">
                  <label for="nomina" class="form-label"><i class="bi bi-list"></i>Listado de estaciones asignadas</label>
                  <div class="form-control" id="listaOperacionesOperador" style="min-height: 100px; resize: vertical; overflow-y: auto;">
                    <span class="form-help">Lista de operaciones asignadas del trabajador en la línea</span>
                  </div>
                </div>
              </div>
              <div id="operatorPreview" class="operator-preview d-none">
                <div class="d-flex align-items-center p-3 bg-light rounded">
                  <div class="operator-avatar bg-primary text-white">JD</div>
                  <div class="ms-3 flex-grow-1">
                    <h6 class="mb-1" id="previewName">Juan Domínguez</h6>
                    <p class="mb-0 text-muted small" id="previewDetails">ID: EMP-0256 | 3 Certificaciones</p>
                  </div>
                  <button type="button" class="btn btn-sm btn-outline-danger" id="clearOperator"><i class="bi bi-x"></i> Cambiar</button>
                </div>
              </div>
            </div>
            <div class="form-section">
              <h4 class="section-subtitle"><i class="bi bi-geo-alt"></i>Detalles de la Asignación</h4>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="stationSelect" class="form-label required-field"><i class="bi bi-cpu"></i>Estación</label>
                  <select class="form-control form-control-custom" id="stationSelect" required>
                    <option value="">Selecciona una estación...</option>
                  </select>
                  <div class="form-help">Selecciona la estación donde se asignará el operador</div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="assignmentDate" class="form-label required-field"><i class="bi bi-calendar"></i>Fecha de Asignación</label>
                  <input type="datetime-local" class="form-control form-control-custom" id="assignmentDate" required>
                  <div class="form-help">Fecha en la que inicia la asignación</div>
                </div>
              </div>
            </div>
            <div class="form-section">
              <h4 class="section-subtitle"><i class="bi bi-sliders"></i>Configuración Adicional</h4>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="turnoAsignar" class="form-label"><i class="bi bi-clock-history"></i>Turno</label>
                  <select id="turnoasignar" class="form-control form-control-custom form-select">
                    <option value="1">Turno 1</option>
                    <option value="2">Turno 2</option>
                  </select>
                </div>
              </div>
              <div class="mb-3">
                <label for="comentarios" class="form-label"><i class="bi bi-sticky"></i>Observaciones</label>
                <textarea class="form-control form-control-custom" id="comentarios" rows="3" placeholder="Notas adicionales sobre esta asignación..."></textarea>
              </div>
            </div>
          </form>
          <div class="d-flex justify-content-end mt-2">
            <button type="button" class="btn btn-secondary mx-2" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Cancelar</button>
            <button type="button" class="btn btn-primary mx-2" id="btnAsignarOperador"><i class="bi bi-check-circle"></i> Guardar</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Consultar Puntos de Cambio -->
  <div class="modal fade" id="changePointsModal" tabindex="-1" aria-labelledby="changePointsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color), #1a2530); color: white;">
          <div class="d-flex align-items-center">
            <i class="bi bi-arrow-left-right me-2" style="font-size: 1.5rem;"></i>
            <h5 class="modal-title" id="changePointsModalLabel">Seguimiento de Puntos de Cambio</h5>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <!-- ... (contenido original) ... -->
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex gap-2">
              <div class="input-group" style="width: 300px;">
                <input type="text" class="form-control" placeholder="Buscar puntos de cambio...">
                <button class="btn btn-outline-secondary" type="button"><i class="bi bi-search"></i></button>
              </div>
              <select class="form-select" style="width: 200px;">
                <option selected>Filtrar por estado</option>
                <option>En progreso</option><option>Completado</option><option>Cancelado</option>
              </select>
              <select class="form-select" style="width: 200px;">
                <option selected>Filtrar por tipo</option>
                <option>Programado</option><option>Inesperado</option><option>Especial</option>
              </select>
            </div>
          </div>
          <div class="card">
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                  <thead class="table-dark">
                    <tr><th>ID</th><th>Estación</th><th>Operador Anterior</th><th>Operador Nuevo</th><th>Fecha</th><th>Tipo</th><th>Estado</th><th>Acciones</th></tr>
                  </thead>
                  <tbody>
                    <tr><td><strong>PC-001</strong></td><td>Estación 02</td><td><div class="d-flex align-items-center"><div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">JP</div><span>Juan Pérez</span></div></td><td><div class="d-flex align-items-center"><div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">MG</div><span>María González</span></div></td><td>15/11/2025</td><td><span class="badge bg-info">Programado</span></td><td><span class="badge bg-warning">En progreso</span></td><td><div class="btn-group"><button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Seguimiento"><i class="bi bi-clipboard-check"></i></button><button class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Detalles"><i class="bi bi-eye"></i></button><button class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="Editar"><i class="bi bi-pencil"></i></button></div></td></tr>
                    <!-- más filas... -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <nav class="mt-4"><ul class="pagination justify-content-center"><li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Anterior</a></li><li class="page-item active"><a class="page-link" href="#">1</a></li><li class="page-item"><a class="page-link" href="#">2</a></li><li class="page-item"><a class="page-link" href="#">3</a></li><li class="page-item"><a class="page-link" href="#">Siguiente</a></li></ul></nav>
        </div>
        <div class="modal-footer">
          <div class="me-auto"><span class="text-muted">Mostrando 4 de 24 registros</span></div>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary"><i class="bi bi-download"></i> Exportar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Registro de Asistencia -->
  <div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color), #1a2530); color: white;">
          <div class="d-flex align-items-center"><i class="bi bi-clipboard-check me-2" style="font-size: 1.5rem;"></i><h5 class="modal-title" id="attendanceModalLabel">Registro de Asistencia - Línea de Producción</h5></div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <!-- ... (contenido original) ... -->
          <div class="row mb-4">
            <div class="col-md-8">
              <div class="btn-group">
                <button class="btn btn-outline-secondary"><i class="bi bi-chevron-left"></i></button>
                <button class="btn btn-outline-dark fw-bold" style="min-width: 180px;"><i class="bi bi-calendar3 me-2"></i><?php setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain'); echo ucfirst(strftime('%d de %B, %Y')); ?></button>
                <button class="btn btn-outline-secondary"><i class="bi bi-chevron-right"></i></button>
              </div>
            </div>
            <div class="col-4">
              <div class="d-flex justify-content-end align-items-center">
                <button class="btn btn-outline-primary me-2"><i class="bi bi-printer"></i> Imprimir</button>
                <button class="btn btn-outline-secondary me-2"><i class="bi bi-download"></i> Exportar</button>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
              <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Detalle de Asistencia por Estación</h6>
              <span class="badge bg-light text-dark me-2">Total: 15 trabajadores</span>
              <div class="m-1" name="checkBoxContainer"><input type='checkbox' id="checkPadre" class='select-checkbox'><label for="checkPadre">Seleccionar todos</label></div>
            </div>
            <div class="card-body">
              <div class="d-grid gap-2 d-md-flex"><button class="btn btn-outline-success" id="btnCambioTurno"><i class="bi bi-clock"></i> Cambio de turno</button></div>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive"> 
                <table id="attendanceTable" class="table table-striped table-hover mb-0" style="width:100%;">
                  <thead class="table-light"><tr><th>Trabajador</th><th>Estación</th><th width="300">Asistencia</th><th>Comentarios</th><th width="200" class="text-center">Cambio de turno</th></tr></thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="row mt-4">
            <div class="col-md-12">
              <div class="card"><div class="card-header"><h6 class="mb-0"><i class="bi bi-info-circle me-2"></i> Resumen del Día</h6></div><div class="card-body"><div class="row text-center"><div class="col"><div class="h4 text-success">12</div><small class="text-muted">Presentes</small></div><div class="col"><div class="h4 text-warning">2</div><small class="text-muted">Permisos</small></div><div class="col"><div class="h4 text-danger">1</div><small class="text-muted">Faltas</small></div><div class="col"><div class="h4 text-info">0</div><small class="text-muted">Vacaciones</small></div><div class="col"><div class="h4 text-primary">87%</div><small class="text-muted">Asistencia</small></div></div></div></div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-success btn-sm" id="btnRegistrarAsistencia"><i class="bi bi-check-lg"></i> Registrar asistencia</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Control de Punto de Cambio -->
  <div class="modal fade" id="changeControlModal" tabindex="-1" aria-labelledby="changeControlModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color), #1a2530); color: white;">
          <div class="d-flex align-items-center"><i class="bi bi-arrow-repeat me-2" style="font-size: 1.5rem;"></i><h5 class="modal-title" id="changeControlModalLabel">Control de punto de cambio</h5></div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="idEstacionModalPC" value="">
          <div id="tiempoPC" class="badge bg-warning text-dark etiqueta-advertencia text-end"></div>
          <div class="container-fluid py-3">
            <div class="row g-3 d-flex justify-content-center text-center" id="menuModalPC">
              <div class="col-7 col-sm-6 col-md-4 col-lg-2"><button class="menu-btn" id="btnRegistroPc" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-original-title="Cambio de mano de obra"><i class="bi bi-person-gear"></i><span>MANO DE OBRA</span></button></div>
              <div class="col-7 col-sm-6 col-md-4 col-lg-2"><button class="menu-btn" id="btnInfoRPC" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-original-title="Informacion del trabajador asignado"><i class="bi bi-info-circle"></i><span>INFORMACION</span></button></div>
              <div class="col-7 col-sm-6 col-md-4 col-lg-2"><button class="menu-btn danger" id="btnLiberarPC" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-original-title="Liberar punto de cambio"><i class="bi bi-unlock"></i><span>LIBERAR</span></button></div>
            </div>
          </div>
          <hr>
          <p class="text-center fw-bold fs-5">ESTACIÓN DE <span id="nombreEstacionModalPC"></span></p>
          <div id="ventanasModalPC">
            <!-- Formulario de registro de punto de cambio -->
            <div id="contregistroCambioForm" class="fade-page show" style="background: white; border-radius: 10px; padding: 25px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08); border-left: 4px solid #000000; margin-bottom: 30px;">
              <form class="form-body" id="registroCambioForm">
                <div class="form-section"><h3 class="section-title justify-content-center"><i class="bi bi-arrow-repeat"></i>Registro de un punto de cambio</h3><p class="text-muted mb-2">Complete la información requerida</p></div>
                <div class="form-section">
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="nominaPC" class="form-label required-field"><i class="bi bi-clock"></i>No. Reloj / ID Empleado</label>
                      <div class="input-group-custom"><input type="number" min="0" step="1" class="form-control form-control-custom" id="nominaPC" placeholder="Ej: 256" required><button type="button" class="input-icon" id="searchEmployee"><i class="bi bi-search"></i></button></div>
                      <div class="form-help">Ingresa el número de reloj o ID único del empleado</div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="nombrePC" class="form-label"><i class="bi bi-person"></i>Nombre del trabajador</label>
                      <div class="input-group-custom"><input type="text" class="form-control form-control-custom" id="nombrePC" placeholder="Selecciona o busca un operador" readonly><button type="button" class="input-icon"><i class="bi bi-people"></i></button></div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="no_controlCambio" class="form-label required-field"><i class="bi bi-hash"></i>No. Control de Cambio</label>
                      <div class="input-group-custom"><input type="text" class="form-control form-control-custom" id="no_controlCambio" placeholder="Ej: CAM-001" maxlength="50" readonly><button type="button" class="input-icon"><i class="bi bi-search"></i></button></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="tipo_cambio" class="form-label required-field"><i class="bi bi-shuffle"></i>Tipo de Cambio</label>
                      <select type="text" class="form-select" id="tipo_cambio" required><option value="1">Inesperado</option><option value="2">Programado</option><option value="3">Otro</option></select>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="fechaHora_inicio" class="form-label required-field"><i class="bi bi-calendar-event"></i>Fecha y Hora Inicio</label>
                      <input type="datetime-local" class="form-control form-control-custom" id="fechaHora_inicio" required>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="turnoPuntoCambio" class="form-label required-field"><i class="bi bi-clock-history"></i>Turno</label>
                      <select id="turnoPuntoCambio" class="form-select" required><option value="" selected>--- Selecciona un turno ---</option><option value="1">Turno 1</option><option value="2">Turno 2</option></select>
                    </div>
                  </div>
                  <div class="mb-3">
                    <label for="motivo" class="form-label"><i class="bi bi-chat-left-text"></i>Descripción</label>
                    <textarea class="form-control form-control-custom" id="motivo" rows="3" placeholder="Descripción del punto de cambio"></textarea>
                  </div>
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="codigolineaPC" class="form-label"><i class="bi bi-diagram-3"></i>Línea</label>
                      <input type="text" class="form-control form-control-custom" id="codigolineaPC" value="<?php echo $nombre?>" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="nombre_estacion" class="form-label"><i class="bi bi-geo-alt"></i>Estación</label>
                      <input type="text" class="form-control form-control-custom" id="nombre_estacion" readonly>
                      <input type="hidden" id="id_estacion">
                    </div>
                  </div>
                </div>
              </form>
              <div class="modal-footer">
                <button type="button" class="btn btn-success" id="confirmChange"><i class="bi bi-check-lg"></i> Confirmar Registro</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              </div>
            </div>
            <!-- Información del personal asignado -->
            <div id="contInfoEstacion" class="fade-page d-none" style="background: white; border-radius: 10px; padding: 25px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08); border-left: 4px solid #000000; margin-bottom: 30px;">
              <h4><i class="bi bi-person-badge" style="margin-right: 10px; font-size: 1.4rem;"></i>Información del Operador de Estación</h4>
              <input type="hidden" id="idTrabajadorAsignado" value="">
              <div class="row">
                <div class="col-md-12">
                  <div style="margin-bottom: 20px; text-align: center;"><img id="imgInfochangeControlModal" src="../img/personal/na.jpg" alt="Foto del operador" style="width: 120px; height: 120px; border-radius: 10px; object-fit: cover; border: 3px solid #e9ecef; margin-bottom: 10px;"><div style="font-weight: 600; color: #495057;">Foto del operador</div></div>
                  <div style="margin-bottom: 15px; display: flex; align-items: flex-start;"><span style="font-weight: 600; color: #495057; min-width: 200px; margin-right: 15px;">Nómina:</span><span id="changeControlInfoNomina" style="color: #212529; flex: 1;"></span></div>
                  <div style="margin-bottom: 15px; display: flex; align-items: flex-start;"><span style="font-weight: 600; color: #495057; min-width: 200px; margin-right: 15px;">Nombre:</span><span id="changeControlInfoNombre" style="color: #212529; flex: 1;"></span></div>
                  <div style="margin-bottom: 15px; display: flex; align-items: flex-start;"><span style="font-weight: 600; color: #495057; min-width: 200px; margin-right: 15px;">Fecha de asignación:</span><span id="changeControlInfFecha" style="color: #212529; flex: 1;"></span></div>
                  <div style="margin-bottom: 15px; display: flex; align-items: center;"><span style="font-weight: 600; color: #495057; min-width: 200px; margin-right: 15px;">Turno:</span><span style="color: #212529; flex: 1;"><span id="changeControlInfoTurno" class="badge bg-primary" style="font-size: 0.85rem; padding: 5px 12px; border-radius: 20px;"></span></span></div>
                </div>
                <div class="col-md-12">
                  <div style="margin-bottom: 15px; display: flex; align-items: flex-start;"><span style="font-weight: 600; color: #495057; min-width: 200px; margin-right: 15px;">Comentario:</span><div style="color: #212529; flex: 1;"><div style="background-color: #f8f9fa; border-left: 4px solid #6c757d; padding: 15px; border-radius: 5px;"><p id="changeControlInfoComentarios"></p></div></div></div>
                </div>
                <div class="d-flex justify-content-end mt-1">
                  <button class="btn btn-danger mx-1" id="btnRemoverTrabajadorPC"><b>REMOVER TRABAJADOR</b></button>
                  <button class="btn btn-warning mx-1"><b>RETIRAR CERTIFICACION</b></button>
                </div>
              </div>
            </div>
            <!-- Contenedor de liberación de punto de cambio -->
            <div id="contLiberarPC" class="fade-page d-none" style="background: white; border-radius: 10px; padding: 25px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08); border-left: 4px solid #000000; margin-bottom: 30px;">
              <div class="form-section"><h4 class="section-title justify-content-center"><i class="bi bi-unlock"></i>CIERRE DEL PUNTO DE CAMBIO</h4></div>
              <div class="info-card">
                <h6 class="info-title"><i class="bi bi-info-circle"></i>Información del Control de Cambio</h6>
                <div class="row"><div class="col-md-6"><div class="info-item"><span class="info-label">Control No:</span><span class="info-value">CC-2025-001</span></div><div class="info-item"><span class="info-label">Operador:</span><span class="info-value">Juan Pérez</span></div><div class="info-item"><span class="info-label">Estación:</span><span class="info-value">Estación 02</span></div></div><div class="col-md-6"><div class="info-item"><span class="info-label">Inicio:</span><span class="info-value">15/11/2025 08:30</span></div><div class="info-item"><span class="info-label">Duración:</span><span class="info-value">3 días</span></div><div class="info-item"><span class="info-label">Estado:</span><span class="info-value"><span class="badge bg-warning">En Proceso</span></span></div></div></div>
              </div>
              <div class="decision-option option-close">
                <form class="form" id="cierreControlCambioForm">
                  <input type="hidden" name="idPC" id="idPC">
                  <div class="row"><div class="col-md-6"><label for="fechaCierre" class="form-label fw-bold">Fecha de Cierre</label><input type="datetime-local" class="form-control form-control-custom" id="fechaCierre" required></div></div>
                  <div class="mt-4"><label for="notasAdicionales" class="form-label fw-bold"><i class="bi bi-chat-text me-2"></i>Notas Adicionales</label><textarea class="form-control form-control-custom" id="notasAdicionales" rows="3" placeholder="Agregue cualquier observación o comentario adicional sobre el cierre del control de cambio..."></textarea></div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-close-custom" data-bs-dismiss="modal"><i class="bi bi-x-circle me-2"></i>Cancelar</button>
                <button type="button" class="btn btn-confirm-custom" id="btnConfirmClose"><i class="bi bi-check-lg me-2"></i>Cerrar punto de cambio</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Personal Disponible -->
  <div class="modal fade modal-close-control" id="modalPersonalDisponible" tabindex="-1" aria-labelledby="modalPersonalDisponibleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <div class="d-flex align-items-center"><h5 class="modal-title" id="closeControlModalLabel"><i class="bi bi-people"></i>Personal sin asignar</h5></div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="container-fluid py-3">
            <div class="row g-3 d-flex justify-content-center" id="menuModalPNA">
              <div class="col-7 col-sm-6 col-md-4 col-lg-2"><button class="menu-btn" id="btnTablaPNA" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-original-title="Listado de personal no asignado"><i class="bi bi-table"></i><span>Listado de personal</span></button></div>
              <div class="col-7 col-sm-6 col-md-4 col-lg-2"><button class="menu-btn" id="btnRegistroPNA" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-original-title="Registrar personal sin una estación asignada"><i class="bi bi-person-plus"></i><span>Registrar personal</span></button></div>
            </div>
          </div>
          <hr>
          <div id="ventanadModalPersonalNA">
            <!-- Tabla/Listado de personal disponible -->
            <div id="contTablaDisponibles" class="fade-page show">
              <div class="card-header bg-white border-0 py-3"><h5 class="mb-0"><i class="bi bi-people-fill text-primary me-2"></i>Personal Disponible para Asignación</h5><p class="text-muted mb-0 mt-2">Listado de operadores no asignados a operaciones en línea</p></div>
              <div class="table-responsive">
                <table class="table table-hover mb-0" id="tablaPersonalNoAsignado">
                  <thead class="table-light"><tr><th style="width: 15%;" class="py-3 px-4">Nómina</th><th style="width: 30%;" class="py-3 px-4">Nombre del Empleado</th><th style="width: 55%;" class="py-3 px-4 text-center">Acciones</th></tr></thead>
                  <tbody id="tablaBodyPersonalNoAsignado"></tbody>
                </table>
              </div>
            </div>
            <!-- Formulario de registro de personal no asignado -->
            <div id="contRegistroPersonalDisponible" class="fade-page d-none">
              <div class="form-section"><h3 class="section-title">Registrar personal disponible o sin asignar</h3><p class="text-muted mb-4">Complete la información para registrar el personal sin estación</p></div>
              <form class="form" id="fmPersonalNoAsignado">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="nomina" class="form-label required-field"><i class="bi bi-clock"></i>No. Reloj / ID Empleado</label>
                    <div class="input-group-custom"><input type="number" min="1" step="1" class="form-control form-control-custom" id="nominaNoAsignado" placeholder="Ej: EMP-0256" required><button type="button" class="input-icon" id="searchEmployee"><i class="bi bi-search"></i></button></div>
                    <div class="form-help">Ingresa el número de reloj o ID único del empleado</div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label required-field"><i class="bi bi-person"></i>Nombre del Operador</label>
                    <div class="input-group-custom"><input type="text" class="form-control form-control-custom" id="nombreNoAsignado" placeholder="Selecciona o busca un operador"><button type="button" class="input-icon"><i class="bi bi-people"></i></button></div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="stationSelect" class="form-label required-field"><i class="bi bi-diagram-3 text-muted"></i>Línea</label>
                    <input type="text" class="form-control form-control-custom border-start-1 ps-1" placeholder="LN-001" maxlength="20" value="<?php echo $nombre?>" style="border-color: #dee2e6;" readonly>
                    <div class="form-help">Selecciona la estación donde se asignará el operador</div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="assignmentDatePNA" class="form-label required-field"><i class="bi bi-calendar"></i>Fecha de registro</label>
                    <input type="datetime-local" class="form-control form-control-custom" id="assignmentDatePNA" value="<?php echo date('Y-m-d H:i'); ?>" required>
                    <div class="form-help">Fecha del registro</div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="turnoAsignarPersonalDisponible" class="form-label"><i class="bi bi-clock-history"></i>Turno</label>
                    <select id="turnoAsignarPersonalDisponible" class="form-control form-control-custom form-select"><option value="" selected>Selecciona un turno</option><option value="1">Turno 1</option><option value="2">Turno 2</option></select>
                  </div>
                </div>
                <div class="mb-3">
                  <label for="comentarios" class="form-label"><i class="bi bi-chat-text text-muted"></i>Comentarios</label>
                  <textarea class="form-control form-control-custom" id="comentariosNoAsignado" rows="3" placeholder="Ingrese algún comentario"></textarea>
                </div>
              </form>
              <div class="modal-footer">
                <div class="d-flex justify-content-end mt-2">
                  <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Cancelar</button>
                  <button type="button" class="btn btn-primary-custom ms-1" style="color:white" id="btnGuardarDisponible"><i class="bi bi-check-circle"></i> Guardar</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Editar Línea -->
  <div class="modal fade modal-close-control" id="editarLineaModal" tabindex="-1" aria-labelledby="editarLineaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <div class="d-flex align-items-center"><h5 class="modal-title" id="closeControlModalLabel">Modificar datos de la línea</h5></div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form class="form-body" id="lineForm">
            <div class="form-section">
              <h5 class="modal-title mb-2 text-center"><i class="bi bi-info-circle"></i>Información Básica</h5>
              <div class="row">
                <div class="col-md-6 mb-2">
                  <label for="lineCode" class="form-label required-field">Código de Línea</label>
                  <div class="input-group-custom"><input type="text" class="form-control form-control-custom" id="lineCode" placeholder="Ej: LN-001" readonly style="background-color:snow;" value="<?php echo $codigo?>"><button type="button" class="input-icon" data-bs-toggle="tooltip" title="Código único para identificar la línea"><i class="bi bi-question-circle"></i></button></div>
                </div>
                <div class="col-md-6 mb-2">
                  <label for="lineName" class="form-label required-field">Nombre de la Línea</label>
                  <input type="text" class="form-control form-control-custom" id="lineName" placeholder="Ej: Línea de CRV" value="<?php echo $nombre?>" required>
                </div>
              </div>
            </div>
            <div class="form-section">
              <h5 class="modal-title mb-2"><i class="bi bi-person-gear"></i>Personal a Cargo</h5>
              <div class="mb-2">
                <label for="supervisorSearch" class="form-label required-field">Encargado/Supervisor</label>
                <div class="input-group-custom"><input type="text" class="form-control form-control-custom" id="supervisorSearch" placeholder="Buscar empleado..." value="<?php echo $encargado_supervisor?>"><button type="button" class="input-icon"><i class="bi bi-search"></i></button></div>
              </div>
            </div>
            <div class="form-section">
              <h5 class="modal-title mb-1"><i class="bi bi-text-paragraph"></i>Descripción</h5>
              <div class="mb-1">
                <label for="lineDescription" class="form-label">Descripción de la Línea</label>
                <textarea class="form-control form-control-custom form-textarea" id="lineDescription" placeholder="Describe el propósito, procesos principales y características de esta línea de producción..." rows="4"><?php echo $descripcion?></textarea>
                <div class="form-help">Opcional: Proporciona detalles sobre esta línea</div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <div class="d-flex justify-content-end mt-2">
            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Cancelar</button>
            <button type="button" class="btn btn-primary-custom ms-1" id="btnGuardarEdicionLinea" style="color:white"><i class="bi bi-check-circle"></i> Guardar</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Historial Layout -->
  <div class="modal fade" id="historialLayoutModal" tabindex="-1" aria-labelledby="historialLayoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color), #1a2530); color: white;">
          <h5 class="modal-title" id="historialLayoutModalLabel"><i class="bi bi-clock-history me-2"></i>Historial de Layout</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-4 my-1"><label for="fechaHistorial" class="form-label">Fecha</label><input type="date" class="form-control" id="fechaHistorial" value="<?php echo date('Y-m-d'); ?>"></div>
            <div class="col-md-4 my-1"><label for="turnoHistorial" class="form-label">Turno</label><select class="form-select" id="turnoHistorial"><option value="1">Turno 1</option><option value="2">Turno 2</option></select></div>
            <div class="col-md-4 my-1"><label for="turnoHistorial" class="form-label">Registros</label><select class="form-select" id="idRH" name="mostrarHistorialLayout"><option value="">Registros guardados</option></select></div>
          </div>
          <div class="workspace readonly-workspace" style="height: 70vh; overflow: auto; border: 1px solid #dee2e6; background: #f8f9fa;">
            <div class="workspace-grid" id="historialWorkspaceGrid" style="position: relative; transform: scale(1); transform-origin: 0 0;"></div>
          </div>
          <div class="mt-2 text-muted small"><i class="bi bi-info-circle"></i> Vista de solo consulta. No se pueden modificar las estaciones.</div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button></div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS y DataTables -->
  <script src="../scripts/bootstrap.bundle.min.js"></script>
  <script src="../DataTables/datatables.min.js"></script>
  
  <!-- Script original (con todas las funciones) más las nuevas del diagramador -->
  <script>
    // ========== CÓDIGO ORIGINAL (sin modificaciones) ==========
    let btnGuardarEstacion = document.getElementById('btnGuardarEstacion');
    let btnAsignarOperador = document.getElementById('btnAsignarOperador');
    let btnGuardarDisponible = document.getElementById('btnGuardarDisponible');
    let btnGuardarEdicionLinea = document.getElementById('btnGuardarEdicionLinea');
    let confirmChange = document.getElementById('confirmChange');
    let btnConfirmClose = document.getElementById('btnConfirmClose');

    let checkPadre = document.getElementById("checkPadre");
    var seleccionadosGlobal = [];
    var datosAsistenciaCheck; 
    let btnCambioTurno = document.getElementById('btnCambioTurno');
    let btnMenuRegistroAs = document.getElementById('btnMenuRegistroAs');

    let btnRegistrarAsistencia = document.getElementById('btnRegistrarAsistencia');
    let tablaPersonalNoAsignado = document.getElementById('tablaPersonalNoAsignado');
    let workspaceGrid;

    //Inputs del modal asignar operador
    let nominaModalAsignar = document.getElementById('nominaModalAsignar');
    let nominaPC = document.getElementById('nominaPC');

    //Inputs del modal consultar historial
    let fechaHistorial = document.getElementById('fechaHistorial');
    let turnoHistorial = document.getElementById('turnoHistorial');

    document.getElementById('turnoAsignarPersonalDisponible').value =  $('#turnoLayout').val();

    //Botones de menu del modal
    let btnInfoRPC = document.getElementById('btnInfoRPC');
    let btnRegistroPc = document.getElementById('btnRegistroPc');
    let btnLiberarPC = document.getElementById('btnLiberarPC');
    let btnTablaPNA = document.getElementById('btnTablaPNA');
    let btnRegistroPNA = document.getElementById('btnRegistroPNA');

    var nominaNoAsignado = document.getElementById('nominaNoAsignado');

    let stationName = document.getElementById('nombreEstacion');
    let letstationForm = document.getElementById('stationForm');
    let stationDescription = document.getElementById('stationdescripcion');
    let requiredCertification = document.getElementById('requiereCertificacion');
    let certificacionF = document.getElementById('certificacion');
    let codigoLinea = document.getElementById('codigoLinea');
    let assignmentForm = document.getElementById('assignmentForm');

    var stationsData;
    const workspaceState = {zoomLevel: 1, gridSize: 20, isGridSnapEnabled: false};

    // Sistema de drag & drop optimizado con soporte para modales (original)
    class OptimizedDragSystem {
      constructor() {
        this.activeDrag = null;
        this.dragData = null;
        this.animationFrame = null;
        this.lastX = 0;
        this.lastY = 0;
        this.isClick = true;
        this.clickThreshold = 5;
        this.workspaceCache = { rect: null, scrollLeft: 0, scrollTop: 0, timestamp: 0 };
        this.updateThreshold = 1000 / 60;
        this.lastUpdate = 0;
      }
      init() {
        document.addEventListener('mousedown', this.handleMouseDown.bind(this));
        document.addEventListener('mousemove', this.handleMouseMove.bind(this));
        document.addEventListener('mouseup', this.handleMouseUp.bind(this));
        document.addEventListener('dragstart', (e) => e.preventDefault());
      }
      handleMouseDown(e) {
        const station = e.target.closest('.station');
        if (!station) return;
        e.preventDefault();
        e.stopPropagation();
        this.activeDrag = station;
        this.isClick = true;
        this.updateWorkspaceCache();
        const rect = station.getBoundingClientRect();
        this.dragData = {
          startX: e.clientX,
          startY: e.clientY,
          startLeft: parseInt(station.style.left) || 0,
          startTop: parseInt(station.style.top) || 0,
          elementWidth: rect.width,
          elementHeight: rect.height,
          stationId: station.getAttribute('data-station-id')
        };
        document.body.style.cursor = 'grabbing';
        document.body.style.userSelect = 'none';
      }
      handleMouseMove(e) {
        if (!this.activeDrag || !this.dragData) return;
        const deltaX = Math.abs(e.clientX - this.dragData.startX);
        const deltaY = Math.abs(e.clientY - this.dragData.startY);
        if (deltaX > this.clickThreshold || deltaY > this.clickThreshold) {
          this.isClick = false;
          this.activeDrag.classList.add('dragging');
        }
        if (!this.isClick) {
          const now = performance.now();
          if (now - this.lastUpdate < this.updateThreshold) return;
          this.lastUpdate = now;
          if (this.animationFrame) cancelAnimationFrame(this.animationFrame);
          this.animationFrame = requestAnimationFrame(() => { this.updateDragPosition(e.clientX, e.clientY); });
        }
      }
      handleMouseUp(e) {
        if (!this.activeDrag) return;
        if (this.animationFrame) { cancelAnimationFrame(this.animationFrame); this.animationFrame = null; }
        if (this.isClick) {
          const stationData = stationsData.find(s => s.id == this.dragData.stationId);
          if (stationData) this.showStationModal(stationData);
        } else {
          this.updateDragPosition(e.clientX, e.clientY, true);
          if (workspaceState.isGridSnapEnabled) this.snapToGrid(this.activeDrag);
        }
        this.activeDrag.classList.remove('dragging');
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
        this.activeDrag = null;
        this.dragData = null;
        this.isClick = true;
      }
      updateDragPosition(clientX, clientY, isFinal = false) {
        if (!this.activeDrag || !this.dragData) return;
        if (isFinal || performance.now() - this.workspaceCache.timestamp > 100) this.updateWorkspaceCache();
        const deltaX = clientX - this.dragData.startX;
        const deltaY = clientY - this.dragData.startY;
        let newX = this.dragData.startLeft + deltaX;
        let newY = this.dragData.startTop + deltaY;
        newX = Math.max(0, newX);
        newY = Math.max(0, newY);
        if (newX !== this.lastX || newY !== this.lastY || isFinal) {
          if (isFinal) {
            this.activeDrag.style.left = `${newX}px`;
            this.activeDrag.style.top = `${newY}px`;
            this.activeDrag.style.transform = 'none';
          } else {
            this.activeDrag.style.transform = `translate(${deltaX}px, ${deltaY}px)`;
          }
          this.lastX = newX;
          this.lastY = newY;
        }
      }
      updateWorkspaceCache() {
        const workspace = document.querySelector('.workspace');
        this.workspaceCache.rect = workspace.getBoundingClientRect();
        this.workspaceCache.scrollLeft = workspace.scrollLeft;
        this.workspaceCache.scrollTop = workspace.scrollTop;
        this.workspaceCache.maxX = workspace.scrollWidth - this.dragData?.elementWidth || 0;
        this.workspaceCache.maxY = workspace.scrollHeight - this.dragData?.elementHeight || 0;
        this.workspaceCache.timestamp = performance.now();
      }
      snapToGrid(element) {
        const gridSize = workspaceState.gridSize;
        let left = parseInt(element.style.left);
        let top = parseInt(element.style.top);
        left = Math.round(left / gridSize) * gridSize;
        top = Math.round(top / gridSize) * gridSize;
        element.style.left = `${left}px`;
        element.style.top = `${top}px`;
      }
      showStationModal(stationData) {
        console.log('Datos de la estacion: ', stationData);
        document.getElementById('imgInfochangeControlModal').src = (stationData.nomina) ? `../img/personal/${stationData.nomina}.jpg` : `../img/personal/na.jpg`;
        document.getElementById('nombreEstacionModalPC').textContent = (stationData.name).toUpperCase();
        document.getElementById('idEstacionModalPC').value = stationData.id;
        document.getElementById('idTrabajadorAsignado').value = stationData.nomina || '';
        getNoControl().then(resultado => { document.getElementById('no_controlCambio').value = resultado; });
        document.getElementById('fechaHora_inicio').value = (new Date()).toLocaleString('sv-SE').slice(0, 16);
        document.getElementById('id_estacion').value = stationData.id;
        document.getElementById('nombre_estacion').value = stationData.name;
        document.getElementById('turnoPuntoCambio').value = $('#turnoLayout').val();
        document.getElementById('idPC').value = stationData.idPC || '';
        document.getElementById('fechaCierre').value = (new Date()).toLocaleString('sv-SE').slice(0, 16);
        getOperator(stationData.nomina, stationData.id, stationData.idPC || null);
        const stationModal = new bootstrap.Modal(document.getElementById('changeControlModal'));
        stationModal.show();
      }
    }

    const dragSystem = new OptimizedDragSystem();

    function saveLayout(showMessage) {
      const stations = document.querySelectorAll('.station');
      const layoutData = [];
      stations.forEach(station => {
        const stationId = station.getAttribute('data-station-id');
        const left = parseInt(station.style.left);
        const top = parseInt(station.style.top);
        layoutData.push({id: stationId, x: left, y: top});
      });
      var formDataPosicion = new FormData;
      formDataPosicion.append('opcion', 6);
      formDataPosicion.append('layoutPosition', JSON.stringify(layoutData));
      formDataPosicion.append('stationsData', JSON.stringify(stationsData));
      formDataPosicion.append('codigoLinea', codigoLinea.value);
      formDataPosicion.append('turno', $('#turnoLayout').val());
      fetch("../api/operacionesLinea.php", {
        method: "POST",
        body: formDataPosicion,
      })
      .then((response) => response.text())
      .then((data) => {
        console.log(data);
        if(showMessage == true) return;
        data = JSON.parse(data);
        if(data.estatus=='ok') alert('Layout guardado correctamente');
        else alert(data.mensaje);
      })
      .catch((error) => { console.log(error); });
    }

    function setupControls() {
      document.getElementById('zoomInBtn').addEventListener('click', zoomIn);
      document.getElementById('zoomOutBtn').addEventListener('click', zoomOut);
      document.getElementById('snapToGridBtn').addEventListener('click', toggleGridSnap);
      document.getElementById('saveLayoutBtn').addEventListener('click', saveLayout);
      document.getElementById('workspaceGrid').addEventListener('wheel', function(e) {
        if (e.ctrlKey) { e.preventDefault(); if (e.deltaY < 0) zoomIn(); else zoomOut(); }
      });
    }

    function zoomIn() { if (workspaceState.zoomLevel < 2) { workspaceState.zoomLevel += 0.1; applyZoom(); } }
    function zoomOut() { if (workspaceState.zoomLevel > 0.5) { workspaceState.zoomLevel -= 0.1; applyZoom(); } }
    function applyZoom() {
      const workspaceGrid = document.getElementById('workspaceGrid');
      workspaceGrid.style.transform = `scale(${workspaceState.zoomLevel})`;
      updateZoomIndicator();
    }

    function updateZoomIndicator() {
      const zoomPercent = Math.round(workspaceState.zoomLevel * 100);
      document.getElementById('zoomIndicator').textContent = `${zoomPercent}%`;
    }

    function toggleGridSnap() {
      workspaceState.isGridSnapEnabled = !workspaceState.isGridSnapEnabled;
      const btn = document.getElementById('snapToGridBtn');
      if (workspaceState.isGridSnapEnabled) {
        btn.classList.remove('btn-outline-secondary'); btn.classList.add('btn-secondary');
        btn.innerHTML = '<i class="bi bi-arrows-move"></i> Cuadrícula activa';
        snapAllStationsToGrid();
      } else {
        btn.classList.remove('btn-secondary'); btn.classList.add('btn-outline-secondary');
        btn.innerHTML = '<i class="bi bi-arrows-move"></i> Ajustar a cuadrícula';
      }
    }

    function snapAllStationsToGrid() {
      const stations = document.querySelectorAll('.station');
      stations.forEach(station => { dragSystem.snapToGrid(station); });
    }

    function createStation(stationData, parent) {
      const station = document.createElement('div');
      station.className = `station ${stationData.colorClass}`;
      station.style.left = `${stationData.x}px`;
      station.style.top = `${stationData.y}px`;
      station.setAttribute('data-station-id', stationData.id);
      let operatorIcon = 'bi-person';
      if (stationData.status === 'occupied' || stationData.status === 'absent') {
        operatorIcon = `<img src="../img/personal/${stationData.nomina}.jpg" alt="Foto del operador" style="width: 100px; height: 100px; border-radius: 10px; object-fit: cover; border: 3px solid #e9ecef; margin-bottom: 10px;">`;
      } else if (stationData.status === 'pending') {
        operatorIcon = '<i class="bi-person-x"></i>';
      }
      station.innerHTML = `<div class="station-header">${stationData.name}</div><div class="station-content"><div class="station-operator">${operatorIcon}</div><div class="station-name">${stationData.operator || 'No asignado'}</div></div><div class="station-status status-${stationData.status}"></div>`;
      if (stationData.isCertificate == 1) {
        station.querySelector('.station-header').style.background = "#ffc107";
        station.querySelector('.station-header').style.color = "rgb(0, 0, 0, 1)";
      }
      parent.appendChild(station);
    }

    function getEstaciones(){
      const formData = new FormData;
      formData.append('opcion', 5);
      formData.append('turno', $('#turnoLayout').val());
      formData.append('codigoLinea', codigoLinea.value);
      return fetch("../api/operacionesLinea.php", {
        method: "POST",
        body: formData,
      })
      .then((response) => response.text())
      .then((data) => {
        stationsData = JSON.parse(data);
        stationsData.forEach(station => { createStation(station, workspaceGrid); });
        listarEstaciones();
      })
      .catch((error) => { console.log(error); });
    }

    function getEstacion(id){
      let formDataEstacion = new FormData;
      formDataEstacion.append('opcion', 15);
      formDataEstacion.append('idEstacion', id);
      formDataEstacion.append('turno', $('#turnoLayout').val());
      fetch("../api/operacionesLinea.php", {
        method: "POST",
        body: formDataEstacion,
      })
      .then((response) => response.text())
      .then((data) => {
        data = JSON.parse(data);
        if(data.estatus=='ok'){
          actualizarEstacion(id, {
            'nomina': (data.estacion.nomina) ? data.estacion.nomina : null,
            'operator': (data.estacion.operator) ? data.estacion.operator : 'No asignado',
            'colorClass': data.estacion.colorClass,
            'status': data.estacion.status,
            'idPC': data.estacion.idPC,
            'estatusPC': data.estacion.estatusPC,
            'isCertificate': data.estacion.isCertificate
          });
        } else alert(data.mensaje);
      }).catch((error) => { console.log(error); });
    }

    function agregarEstacion(){
      var formDataEstacion = new FormData;
      let nombreEstacion = (stationName.value.trim() === "") ? null : stationName.value;
      let descripcion = (stationDescription.value.trim() === "") ? null : stationDescription.value;
      let requiereC = (requiredCertification.value.trim() === "") ? null : requiredCertification.value;
      let certificacion = (certificacionF.value.trim() === "") ? null : certificacionF.value;
      let linea = (codigoLinea.value.trim() === "") ? null : codigoLinea.value;
      formDataEstacion.append("opcion", "2");
      formDataEstacion.append("nombreEstacion", nombreEstacion);
      formDataEstacion.append("descripcion", descripcion);
      formDataEstacion.append("requiereC", requiereC);
      formDataEstacion.append("certificacion", certificacion);
      formDataEstacion.append("linea", linea);
      formDataEstacion.append("x", 0);
      formDataEstacion.append("y", 0);
      if (letstationForm.reportValidity()) {
        fetch("../api/operacionesLinea.php", {
          method: "POST",
          body: formDataEstacion,
        })
        .then((response) => response.text())
        .then((data) => {
          data = JSON.parse(data);
          if(data.status=='ok'){
            alert(data.mensaje);
            document.getElementById('stationForm').reset();
            let modalAgregarEstacion = bootstrap.Modal.getInstance(document.getElementById('modalAgregarEstacion'));
            modalAgregarEstacion.hide();
            stationsData.push(data.dataEstacion);
            createStation(data.dataEstacion, workspaceGrid);
            listarEstaciones();
          } else alert(data.mensaje);
        })
        .catch((error) => { console.log(error); });
      }
    }

    function asignarEstaciones(){
      var formDataAsig = new FormData;
      let nomina = document.getElementById('nominaModalAsignar').value;
      let nombre = document.getElementById('nombreModalAsignar').value;
      let estacion = document.getElementById('stationSelect').value;
      let fecha = document.getElementById('assignmentDate').value;
      let turno = document.getElementById('turnoasignar').value;
      let comentarios = document.getElementById('comentarios').value;
      formDataAsig.append("opcion", "3");
      formDataAsig.append("nomina", nomina);
      formDataAsig.append("nombre", nombre);
      formDataAsig.append("estacion", estacion);
      formDataAsig.append("fecha", fecha);
      formDataAsig.append("turno", turno);
      formDataAsig.append("comentarios", comentarios);
      formDataAsig.append('codigoLinea', codigoLinea.value);
      if(!nombre) { alert("No se encontró registro del empleado ingresado o se perdió la conexión con el servidor."); return; }
      if(assignmentForm.reportValidity()){
        fetch("../api/operacionesLinea.php", {
          method: "POST",
          body: formDataAsig,
        })
        .then((response) => response.text())
        .then((data) => {
          data = JSON.parse(data);
          if(data.estatus=='ok'){
            alert(data.mensaje);
            $('#nominaModalAsignar').val('');
            $('#nombreModalAsignar').val('');
            $('#stationSelect').val('');
            $('#comentarios').val('');
            $('#listaOperacionesOperador').html('<span class="form-help">Lista de operaciones asignadas del trabajador en la linea </span>');
            getEstacion(estacion);
          } else { alert(data.mensaje); console.log(data.error); }
        })
        .catch((error) => { console.log(error); });
      }
    }

    function actualizarEstacion(stationId, newData){
      const station = document.querySelector(`[data-station-id="${stationId}"]`);
      if (station) {
        (newData.operator) ? station.querySelector('.station-name').textContent = newData.operator : station.querySelector('.station-name').textContent = 'No asignado';
        if(newData.colorClass){
          const colorActual = Array.from(station.classList).filter(clase => clase.startsWith('station-color-'));
          colorActual.forEach(clase => station.classList.remove(clase));
          station.classList.add(newData.colorClass);
        }
        if(newData.status && newData.status != null && newData.status !== ''){
          const status = station.querySelector('.station-status');
          const clasesParaEliminar = Array.from(status.classList).filter(clase => clase.startsWith('status-'));
          clasesParaEliminar.forEach(clase => status.classList.remove(clase));
          status.classList.add(`status-${newData.status}`);
        }
        const operator = station.querySelector('.station-operator');
        (newData.nomina) ? operator.innerHTML = `<img src="../img/personal/${newData.nomina}.jpg" alt="Foto del operador" style="width: 100px; height: 100px; border-radius: 10px; object-fit: cover; border: 3px solid #e9ecef; margin-bottom: 10px;">` : operator.innerHTML = '<i class="bi-person-x"></i>';
        if (newData.isCertificate == 1) {
          station.querySelector('.station-header').style.background = "#ffc107";
          station.querySelector('.station-header').style.color = "rgb(0, 0, 0, 1)";
        } else {
          station.querySelector('.station-header').style.background = "";
          station.querySelector('.station-header').style.color = "";
        }
      } else console.warn(`No se encontró la estación ${stationId}`);
      let estation = stationsData.find(obj => obj.id === stationId);
      if (estation) {
        (newData.operator) ? estation.operator = newData.operator : '';
        (newData.colorClass) ? estation.colorClass = newData.colorClass : '';
        (newData.status) ? estation.status = newData.status : '';
        estation.nomina = (newData.nomina) ?? null;
        estation.idPC = (newData.idPC) ?? null;
        estation.estatusPC = (newData.estatusPC) ?? null;
        estation.isCertificate = newData.isCertificate;
      }
    }

    function listarEstaciones(){
      const select = document.getElementById('stationSelect');
      select.innerHTML = '';
      let none = document.createElement('option'); none.value = ''; none.textContent = 'Selecciona una estación...'; select.appendChild(none);
      stationsData.forEach(station => {
        const option = document.createElement('option'); option.value = station.id; option.textContent = station.name; select.appendChild(option);
      });
    }

    function registrarPNA(){
      let formDataNoAsignado = new FormData;
      let fmPersonalNoAsignado = document.getElementById('fmPersonalNoAsignado');
      if(fmPersonalNoAsignado.reportValidity()){
        if(document.getElementById('nombreNoAsignado').value == '' || document.getElementById('nombreNoAsignado').value == null){
          alert('No se encontró registro del empleado ingresado o se perdió la conexión con el servidor.'); return;
        }
        formDataNoAsignado.append('nomina', document.getElementById("nominaNoAsignado").value);
        formDataNoAsignado.append('nombre', document.getElementById("nombreNoAsignado").value);
        formDataNoAsignado.append('turno', document.getElementById("turnoAsignarPersonalDisponible").value);
        formDataNoAsignado.append('fechaR', document.getElementById("assignmentDatePNA").value);
        formDataNoAsignado.append('comentarios', document.getElementById("comentariosNoAsignado").value);
        formDataNoAsignado.append('codigoLinea', codigoLinea.value);
        formDataNoAsignado.append('opcion', 8);
        fetch("../api/operacionesLinea.php", {
          method: "POST",
          body: formDataNoAsignado,
        })
        .then((response) => response.text())
        .then((data) => {
          console.log(data);
          data = JSON.parse(data);
          if(data.estatus=='ok'){
            alert(data.mensaje);
            $('#nominaNoAsignado').val('');
            $('#nombreNoAsignado').val('');
            $('#comentariosNoAsignado').val('');
            mostrarTablaPNA();
            generarTablaAsistencia();
          } else { alert(data.mensaje); console.log(data); }
        })
        .catch((error) => { console.log(error); });
      }
    }

    function registrarDisponible(nomina, nombre, turno){
      let formDataNoAsignado = new FormData;
      let fecha = (new Date()).toLocaleString('sv-SE').slice(0, 16);
      formDataNoAsignado.append('nomina', nomina);
      formDataNoAsignado.append('nombre', nombre);
      formDataNoAsignado.append('turno', turno);
      formDataNoAsignado.append('fechaR', fecha);
      formDataNoAsignado.append('codigoLinea', codigoLinea.value);
      formDataNoAsignado.append('opcion', 8);
      fetch("../api/operacionesLinea.php", {
        method: "POST",
        body: formDataNoAsignado,
      })
      .then((response) => response.text())
      .then((data) => {
        data = JSON.parse(data);
        if(data.estatus=='ok') { console.log('Se ha registrado al trabajador'); mostrarTablaPNA(); }
        else console.log(data);
      })
      .catch((error) => { console.log(error); });
    }

    function mostrarTablaPNA(){
      let formDataNoAsignadoL = new FormData;
      formDataNoAsignadoL.append('codigoLinea', codigoLinea.value);
      formDataNoAsignadoL.append('turno', $('#turnoLayout').val());
      formDataNoAsignadoL.append('opcion', 9);
      fetch("../api/operacionesLinea.php", {
        method: "POST",
        body: formDataNoAsignadoL,
      })
      .then((response) => response.text())
      .then((data) => {
        data = JSON.parse(data);
        let body = document.getElementById('tablaBodyPersonalNoAsignado');
        let filasHTML = '';
        data.forEach(emp => {
          filasHTML += `<tr><td class="px-4 align-middle"><span class="fw-semibold">${emp.nomina}</span></td><td class="px-4 align-middle"><div class="d-flex align-items-center"><div><div class="fw-medium">${(emp.nombre) ?? ''}</div></div></div></td><td class="px-4 align-middle text-center"><div class="d-flex justify-content-center gap-2"><button class="btn btn-sm btn-outline-primary d-inline-flex align-items-center" onclick="openAsignarEstacion('${emp.nomina}')"><i class="bi bi-gear me-1"></i>Asignar a Estación</button><button class="btn btn-sm btn-outline-danger d-inline-flex align-items-center" onclick="confirmarEliminar('${emp.id_registro}')"><i class="bi bi-trash me-1"></i>Borrar registro</button></div></td></tr>`;
        });
        body.innerHTML = filasHTML;
      })
      .catch((error) => { console.log(error); });
    }

    function generarTablaAsistencia(){
      let fromDataAsistencia = new FormData;
      fromDataAsistencia.append('opcion', 16);
      fromDataAsistencia.append('codigoLinea', codigoLinea.value);
      fromDataAsistencia.append('turno', $('#turnoLayout').val());
      document.getElementById('checkPadre').checked = false;
      fetch("../api/operacionesLinea.php", {
        method: "POST",
        body: fromDataAsistencia,
      })
      .then((response) => response.text())
      .then((data) => {
        data = JSON.parse(data);
        datosAsistenciaCheck = data.map(item => Number(item.nomina));
        $('#attendanceTable').DataTable().destroy();
        $('#attendanceTable').DataTable({
          autoWidth: false,
          responsive: false,
          data: data,
          deferRender: false,
          paging: true,
          pageLength: 10,
          searching: false,
          info: false,
          columns: [
            { data: null, render: row => `<div class="fw-bold" data-nombre="${row.nombre}" data-nomina="${row.nomina}" data-id_estacion="${row.id_estacion}" data-nombre_estacion="${row.nombre_estacion}">${row.nombre}</div><small class="text-muted">ID: ${row.nomina}</small>` },
            { data: null, render: row => `<div>${(row.nombre_estacion).toUpperCase()}</div>` },
            { data: null, render: row => `<select name="estatusAsistencia" class="form-control form-control-custom attendance-status"><option value="1" ${(row.estatus && row.estatus=='1') ? 'selected' : ''}>✅ ASISTENCIA</option><option value="2" ${(row.estatus && row.estatus=='2') ? 'selected' : ''}>❌ FALTA INJUSTIFICADA</option><option value="3" ${(row.estatus && row.estatus=='3') ? 'selected' : ''}>🟢 PERMISO SIN GOCE DE SUELDO</option><option value="4" ${(row.estatus && row.estatus=='4') ? 'selected' : ''}>🏖️ VACACIONES</option><option value="5" ${(row.estatus && row.estatus=='5') ? 'selected' : ''}>🟡 PARO TÉCNICO</option><option value="6" ${(row.estatus && row.estatus=='6') ? 'selected' : ''}>⚪ DESCANSO</option><option value="7" ${(row.estatus && row.estatus=='7') ? 'selected' : ''}>🚫 SANCIÓN</option><option value="8" ${(row.estatus && row.estatus=='8') ? 'selected' : ''}>⏱️ TIEMPO EXTRA</option><option value="9" ${(row.estatus && row.estatus=='9') ? 'selected' : ''}>🏥 INCAPACIDAD</option></select>` },
            { data: null, render: () => `<input type="text" name="observacionesAsistencia" class="form-control form-control-custom" placeholder="Observaciones...">` },
            { data: null, className: "text-center", render: (data, type, row) => `<div class="form-check d-flex justify-content-center"><input class="form-check-input" data-nomina="${row.nomina}" type="checkbox" id="cambio_${row.nomina}"><label class="form-check-label mx-1" for="cambio_${row.nomina}"><i class="bi bi-clock-history"></i></label></div>` }
          ]
        });
      }).catch((error) => { console.log(error); });
    }

    function registrarAsistencia(){
      let datosAsistencia = [];
      let fromDataAsistencia = new FormData;
      let turno = $('#turnoLayout').val();
      let asistenciaRegistrada = false;
      $('#attendanceTable').DataTable().rows({page:'all'}).every(function () {
        const data = this.data();
        if(data.id_registro){ asistenciaRegistrada = true; return; }
        const fila = this.node();
        datosAsistencia.push({
          nomina: data.nomina,
          nombre: data.nombre,
          id_estacion: data.id_estacion,
          nombres_estaciones: data.nombre_estacion,
          estatus: $(fila).find('select[name="estatusAsistencia"]').val(),
          observacionesAsistencia: $(fila).find('input[name="observacionesAsistencia"]').val()
        });
      });
      if(asistenciaRegistrada) { alert('Ya se ha registrado la asistencia'); return; }
      fromDataAsistencia.append('opcion', 17);
      fromDataAsistencia.append('turno', turno);
      fromDataAsistencia.append('codigoLinea', codigoLinea.value);
      fromDataAsistencia.append('datosAsistencia', JSON.stringify(datosAsistencia));
      fromDataAsistencia.append('stationsData', JSON.stringify(stationsData));
      fetch("../api/operacionesLinea.php", {
        method: "POST",
        body: fromDataAsistencia,
      })
      .then(response => response.text())
      .then(data => {
        console.log(data);
        data = JSON.parse(data);
        if(data.estatus && data.estatus == 'ok'){
          alert(data.mensaje);
          generarTablaAsistencia();
          document.getElementById('workspaceGrid').innerHTML = '';
          return getEstaciones();
        } else if (data.estatus && data.estatus == 'error'){ alert(data.mensaje); return Promise.reject('Error en respuesta'); }
        else { alert('Ocurrió un error al realizar el registro'); console.log(data); return Promise.reject('Error desconocido'); }
      }).then(() =>{ saveLayout(); })
      .catch(error => { console.log(error); });
    }

    function openAsignarEstacion(nomina) {
      let modalPersonalDisponible = document.getElementById('modalPersonalDisponible');
      let modalAsignarOperador = document.getElementById('modalAsignarOperador');
      let modalActual = bootstrap.Modal.getInstance(modalPersonalDisponible);
      (modalActual) ? modalActual.hide() : '';
      newModal = new bootstrap.Modal(modalAsignarOperador);
      newModal.show();
      $('#assignmentDate').val((new Date()).toLocaleString('sv-SE').slice(0, 16));
      $('#stationSelect').val('');
      $('#turnoasignar').val($('#turnoLayout').val());
      $('#nominaModalAsignar').val(nomina);
      nominaModalAsignar.dispatchEvent(new Event("change"));
    }

    function changeContent(contenedorPadre, contenidoVisible){
      const contenedor = document.getElementById(contenedorPadre);
      const visible = document.getElementById(contenidoVisible);
      Array.from(contenedor.children).forEach(el => { if (el.classList.contains('show')) { el.classList.remove('show'); el.classList.add('d-none'); } });
      visible.offsetHeight;
      setTimeout(() => { visible.classList.add('show'); }, 100);
      visible.classList.remove('d-none');
    }

    function getNoControl() {
      const formDataNoControles = new FormData();
      formDataNoControles.append('opcion', 12);
      formDataNoControles.append('codigoLinea', codigoLinea.value);
      return fetch("../api/operacionesLinea.php", {
        method: "POST",
        body: formDataNoControles,
      })
      .then(response => response.json())
      .then(data => { if (data.estatus === 'ok') return data.noControl; return ''; })
      .catch(error => { console.error(error); return ''; });
    }

    function updateAsistencia(element, clave){
      let table = $('#attendanceTable').DataTable();
      let $row = $(element).closest('tr');
      let data = table.row($row).data();
      if(data['id_registro']){
        let nuevoValor = $(element).val();
        let campo = $(element).attr('name');
        let formDataUpdate = new FormData();
        formDataUpdate.append('opcion', 18);
        formDataUpdate.append('id_registro', data['id_registro']);
        formDataUpdate.append(clave, nuevoValor);
        fetch("../api/operacionesLinea.php", {
          method: "POST",
          body: formDataUpdate,
        })
        .then((response) => response.text())
        .then((data) => {
          console.log(data);
          data = JSON.parse(data);
          if(data.error) alert(data.mensaje);
          else if (campo) { data[campo] = nuevoValor; }
        }).catch((error) => { console.log(error); });
      }
    }

    function cambiarTurno(){
      if(!seleccionadosGlobal || seleccionadosGlobal.length<=0){ alert("No ha seleccionado información para actualizar"); return; }
      let turno = ($('#turnoLayout').val() == '1') ? '2' : '1';
      let fromDataCambioTurno = new FormData();
      fromDataCambioTurno.append('opcion', 19);
      fromDataCambioTurno.append('datosAsistenciaCheck', JSON.stringify(seleccionadosGlobal));
      fromDataCambioTurno.append('turnoCambio', turno);
      fromDataCambioTurno.append('codigoLinea', codigoLinea.value);
      fromDataCambioTurno.append('turnoActual', $('#turnoLayout').val());
      saveLayout(true);
      fetch("../api/operacionesLinea.php", {
        method: "POST",
        body: fromDataCambioTurno,
      })
      .then((response) => response.text())
      .then((data) => {
        data = JSON.parse(data);
        if(data.estatus == 'ok'){
          let modalAgregarEstacion = bootstrap.Modal.getInstance(document.getElementById('attendanceModal'));
          modalAgregarEstacion.hide();
          alert(data.mensaje);
        } else { alert(data.mensaje); console.log(data); }
      }).catch((error) => { console.log(error); });
    }

    function getOperator(nomina, estacion, idPC){
      if(nomina){
        let fromDataGetOperador = new FormData();
        fromDataGetOperador.append('opcion', 20);
        fromDataGetOperador.append('nomina', nomina);
        fromDataGetOperador.append('idEstacion', estacion);
        fetch("../api/operacionesLinea.php", {
          method: "POST",
          body: fromDataGetOperador,
        })
        .then((response) => response.text())
        .then((data) => {
          data = JSON.parse(data);
          if(data.estatus == 'ok'){
            $("#changeControlInfoNomina").text(data.nomina);
            $("#changeControlInfoNombre").text(data.nombre);
            $("#changeControlInfFecha").text(data.fecha_inicio);
            $("#changeControlInfoTurno").text("TURNO "+data.turno);
            $("#changeControlInfoComentarios").text((data.descripcion && data.descripcion != '') ? data.descripcion : 'SIN COMENTARIOS');
            if(data.fecha_inicio && idPC){
              let fecha_inicio = new Date(data.fecha_inicio);
              let ahora = new Date();
              let diferencia_ms = ahora - fecha_inicio;
              let dias = Math.floor(diferencia_ms / (1000 * 60 * 60 * 24));
              document.getElementById('tiempoPC').innerHTML = `⚠Tiempo activo del punto de cambio: `+dias+' dias';
            } else document.getElementById('tiempoPC').innerHTML = '';
          } else console.log(data.error);
        }).catch((error) => { console.log(error); });
      } else {
        $("#changeControlInfoNomina").text("");
        $("#changeControlInfoNombre").text("");
        $("#changeControlInfFecha").text("");
        $("#changeControlInfoTurno").text("NA");
        $("#changeControlInfoComentarios").text("SIN COMENTARIOS");
      }
    }

    function confirmarEliminar(idRegistro){
      let fromDataEliminar = new FormData();
      fromDataEliminar.append('opcion', 21);
      fromDataEliminar.append('idRegistro', idRegistro);
      fetch("../api/operacionesLinea.php", {
        method: "POST",
        body: fromDataEliminar,
      })
      .then((response) => response.text())
      .then((data) => {
        data = JSON.parse(data);
        if(data.estatus=='ok'){ alert(data.mensaje); mostrarTablaPNA(); }
        else { alert(data.mensaje); console.log(data); }
      })
      .catch((error) => { alert('No fue posible eliminar el registro'); console.error(error); });
    }

    function createStationHistorial(stationData, parent) {
      const station = document.createElement('div');
      station.className = `station ${stationData.colorClass} station-readonly`;
      station.style.left = `${stationData.x}px`;
      station.style.top = `${stationData.y}px`;
      let operatorIcon = '';
      if ((stationData.status === 'occupied' || stationData.status === 'absent') && stationData.nomina) {
        operatorIcon = `<img src="../img/personal/${stationData.nomina}.jpg" alt="Foto" style="width: 100px; height: 100px; border-radius: 10px; object-fit: cover; border: 3px solid #e9ecef;">`;
      } else { operatorIcon = '<i class="bi-person-x" style="font-size: 2rem;"></i>'; }
      station.innerHTML = `<div class="station-header">${stationData.name}</div><div class="station-content"><div class="station-operator">${operatorIcon}</div><div class="station-name">${stationData.operator || 'No asignado'}</div></div><div class="station-status status-${stationData.status}"></div>`;
      if (stationData.isCertificate == 1) { station.querySelector('.station-header').style.background = "#ffc107"; station.querySelector('.station-header').style.color = "rgb(0,0,0,1)"; }
      parent.appendChild(station);
    }

    function getHistorialLayout(){
      if(!fechaHistorial.value || !turnoHistorial.value || !codigoLinea.value) return;
      let fromDataHistorial = new FormData();
      fromDataHistorial.append('opcion', 22);
      fromDataHistorial.append('codigoLinea', codigoLinea.value);
      fromDataHistorial.append('fecha', fechaHistorial.value);
      fromDataHistorial.append('turno', turnoHistorial.value);
      fetch("../api/operacionesLinea.php", {
        method: "POST",
        body: fromDataHistorial
      })
      .then(response => response.json())
      .then(data => {
        if(data.estatus === 'ok') {
          const selectHistorial = document.getElementById('idRH');
          selectHistorial.innerHTML = '';
          let none = document.createElement('option'); none.value = ''; none.textContent = 'Registros guardados'; selectHistorial.appendChild(none);
          data.registros.forEach(historial => {
            const option = document.createElement('option'); option.value = historial.idR; option.textContent = historial.fechaR; selectHistorial.appendChild(option);
          });
        } else {
          console.log('No se encontró algún registro');
          const selectHistorial = document.getElementById('idRH');
          selectHistorial.innerHTML = '';
          let none = document.createElement('option'); none.value = ''; none.textContent = 'Registros guardados'; selectHistorial.appendChild(none);
        }
      })
      .catch(error => { console.error(error); alert('Error de conexión'); });
    }

    // ========== NUEVO CÓDIGO DEL DIAGRAMADOR SVG ==========
    const svg = document.getElementById('workspace-svg');
    const shapesGroup = $('#shapes-group');
    let selectedElement = null;
    let draggingShape = null;
    let elementCounter = 0;

    function getSVGCoords(e) {
      const pt = svg.createSVGPoint();
      pt.x = e.clientX;
      pt.y = e.clientY;
      return pt.matrixTransform(svg.getScreenCTM().inverse());
    }

    function getElementCenter(elt) {
      const tag = elt.tagName;
      if (tag === 'rect') {
        const x = parseFloat(elt.getAttribute('x')||0);
        const y = parseFloat(elt.getAttribute('y')||0);
        const w = parseFloat(elt.getAttribute('width')||0);
        const h = parseFloat(elt.getAttribute('height')||0);
        return { cx: x + w/2, cy: y + h/2 };
      } else if (tag === 'circle') {
        return { cx: parseFloat(elt.getAttribute('cx')||0), cy: parseFloat(elt.getAttribute('cy')||0) };
      } else if (tag === 'line') {
        const x1 = parseFloat(elt.getAttribute('x1')||0);
        const y1 = parseFloat(elt.getAttribute('y1')||0);
        const x2 = parseFloat(elt.getAttribute('x2')||0);
        const y2 = parseFloat(elt.getAttribute('y2')||0);
        return { cx: (x1+x2)/2, cy: (y1+y2)/2 };
      } else if (tag === 'text') {
        return { cx: parseFloat(elt.getAttribute('x')||0), cy: parseFloat(elt.getAttribute('y')||0) };
      }
      return { cx:0, cy:0 };
    }

    function getRotationAngle(elt) {
      let angle = parseFloat(elt.getAttribute('data-rotation'));
      return isNaN(angle) ? 0 : angle;
    }

    function applyRotation(elt, angle) {
      if (!elt) return;
      const { cx, cy } = getElementCenter(elt);
      if (angle === 0 || isNaN(angle)) {
        elt.removeAttribute('transform');
        elt.setAttribute('data-rotation', 0);
      } else {
        elt.setAttribute('transform', `rotate(${angle}, ${cx}, ${cy})`);
        elt.setAttribute('data-rotation', angle);
      }
      if (elt.classList.contains('arrow-line')) {
        elt.setAttribute('marker-end', 'url(#arrowMarker)');
      }
    }

    function getOriginalPosition(elt) {
      const tag = elt.tagName;
      if (tag === 'rect') return { x: parseFloat(elt.getAttribute('x')||0), y: parseFloat(elt.getAttribute('y')||0) };
      if (tag === 'circle') return { cx: parseFloat(elt.getAttribute('cx')||0), cy: parseFloat(elt.getAttribute('cy')||0) };
      if (tag === 'line') return {
        x1: parseFloat(elt.getAttribute('x1')||0),
        y1: parseFloat(elt.getAttribute('y1')||0),
        x2: parseFloat(elt.getAttribute('x2')||0),
        y2: parseFloat(elt.getAttribute('y2')||0)
      };
      if (tag === 'text') return { x: parseFloat(elt.getAttribute('x')||0), y: parseFloat(elt.getAttribute('y')||0) };
      return {};
    }

    function setPosition(elt, original, dx, dy) {
      const tag = elt.tagName;
      if (tag === 'rect') {
        elt.setAttribute('x', original.x + dx);
        elt.setAttribute('y', original.y + dy);
      } else if (tag === 'circle') {
        elt.setAttribute('cx', original.cx + dx);
        elt.setAttribute('cy', original.cy + dy);
      } else if (tag === 'line') {
        elt.setAttribute('x1', original.x1 + dx);
        elt.setAttribute('y1', original.y1 + dy);
        elt.setAttribute('x2', original.x2 + dx);
        elt.setAttribute('y2', original.y2 + dy);
      } else if (tag === 'text') {
        elt.setAttribute('x', original.x + dx);
        elt.setAttribute('y', original.y + dy);
      }
    }

    function updateCommonFields(elt) {
      if (!elt) return;
      const tag = elt.tagName;
      let x, y;
      if (tag === 'rect') {
        x = parseFloat(elt.getAttribute('x')||0) + parseFloat(elt.getAttribute('width')||0)/2;
        y = parseFloat(elt.getAttribute('y')||0) + parseFloat(elt.getAttribute('height')||0)/2;
      } else if (tag === 'circle') {
        x = parseFloat(elt.getAttribute('cx')||0);
        y = parseFloat(elt.getAttribute('cy')||0);
      } else if (tag === 'line') {
        let x1 = parseFloat(elt.getAttribute('x1')||0);
        let y1 = parseFloat(elt.getAttribute('y1')||0);
        let x2 = parseFloat(elt.getAttribute('x2')||0);
        let y2 = parseFloat(elt.getAttribute('y2')||0);
        x = (x1 + x2)/2;
        y = (y1 + y2)/2;
      } else if (tag === 'text') {
        x = parseFloat(elt.getAttribute('x')||0);
        y = parseFloat(elt.getAttribute('y')||0);
      }
      $('#common-x').val(Math.round(x));
      $('#common-y').val(Math.round(y));
      const angle = getRotationAngle(elt);
      $('#common-rotate').val(angle);
      $('#rotateValue').text(angle + '°');
    }

    function loadAttributesToPanel(elt) {
      if (!elt) return;
      const tag = elt.tagName;
      updateCommonFields(elt);
      if (tag === 'rect') {
        $('#rect-width').val(elt.getAttribute('width')||100);
        $('#rect-height').val(elt.getAttribute('height')||70);
        $('#rect-rx').val(elt.getAttribute('rx')||10);
        $('#rect-ry').val(elt.getAttribute('ry')||10);
        $('#rect-fill').val(elt.getAttribute('fill')||'#ffaa00');
        $('#rect-stroke').val(elt.getAttribute('stroke')||'#000000');
        $('#rect-stroke-width').val(elt.getAttribute('stroke-width')||2);
      } else if (tag === 'circle') {
        $('#circle-r').val(elt.getAttribute('r')||35);
        $('#circle-fill').val(elt.getAttribute('fill')||'#44cc88');
        $('#circle-stroke').val(elt.getAttribute('stroke')||'#000000');
        $('#circle-stroke-width').val(elt.getAttribute('stroke-width')||2);
      } else if (tag === 'line') {
        $('#line-x1').val(elt.getAttribute('x1')||0);
        $('#line-y1').val(elt.getAttribute('y1')||0);
        $('#line-x2').val(elt.getAttribute('x2')||100);
        $('#line-y2').val(elt.getAttribute('y2')||50);
        $('#line-stroke').val(elt.getAttribute('stroke')||'#333333');
        $('#line-stroke-width').val(elt.getAttribute('stroke-width')||3);
        if (elt.classList.contains('arrow-line')) $('#arrow-indicator').show(); else $('#arrow-indicator').hide();
      } else if (tag === 'text') {
        $('#text-content').val($(elt).text() || 'Texto');
        $('#text-font-family').val(elt.getAttribute('font-family')||'Arial');
        $('#text-font-size').val(elt.getAttribute('font-size')||20);
        $('#text-fill').val(elt.getAttribute('fill')||'#000000');
        $('#text-stroke').val(elt.getAttribute('stroke')||'#cccccc');
        $('#text-stroke-width').val(elt.getAttribute('stroke-width')||0.5);
      }
    }

    function selectShape(elt) {
      $('.selected').removeClass('selected');
      $(elt).addClass('selected');
      selectedElement = elt;
      $('.attr-section').removeClass('active-section').hide();
      $('#noSelectionMsg').hide();
      $('.common-attrs').show();
      let panelId = '';
      if (elt.tagName === 'rect') panelId = '#rect-attrs';
      else if (elt.tagName === 'circle') panelId = '#circle-attrs';
      else if (elt.tagName === 'line') panelId = '#line-attrs';
      else if (elt.tagName === 'text') panelId = '#text-attrs';
      if (panelId) $(panelId).addClass('active-section').show();
      loadAttributesToPanel(elt);
      refreshElementList();
    }

    function clearSelection() {
      $('.selected').removeClass('selected');
      selectedElement = null;
      $('.attr-section').hide();
      $('#noSelectionMsg').show();
      $('.common-attrs').hide();
      $('.element-list-item').removeClass('selected-in-list');
    }

    function randomPos(maxX = 1800, maxY = 1300) {
      return { x: 100 + Math.random() * (maxX - 200), y: 100 + Math.random() * (maxY - 200) };
    }

    function createElement(tag, attrs, isArrow = false) {
      const elt = document.createElementNS('http://www.w3.org/2000/svg', tag);
      for (let key in attrs) elt.setAttribute(key, attrs[key]);
      elt.setAttribute('data-rotation', 0);
      if (isArrow) {
        elt.classList.add('arrow-line');
        elt.setAttribute('marker-end', 'url(#arrowMarker)');
      }
      shapesGroup.append(elt);
      refreshElementList();
      return elt;
    }

    // Botones de dibujo
    $('#addRectFill').click(() => {
      const p = randomPos();
      createElement('rect', { x: p.x, y: p.y, width: 100, height: 70, rx: 10, ry: 10, fill: '#ffaa00', stroke: '#000', 'stroke-width': 2 });
    });
    $('#addRectOutline').click(() => {
      const p = randomPos();
      createElement('rect', { 
        x: p.x, 
        y: p.y, 
        width: 120, 
        height: 80, 
        rx: 10, 
        ry: 10, 
        fill: 'none',
         stroke: '#2563eb', 
         'stroke-width': 3 });
    });
    $('#addCircleFill').click(() => {
      const p = randomPos();
      createElement('circle', { cx: p.x, cy: p.y, r: 35, fill: '#44cc88', stroke: '#000', 'stroke-width': 2 });
    });
    $('#addCircleOutline').click(() => {
      const p = randomPos();
      createElement('circle', { cx: p.x, cy: p.y, r: 40, fill: 'none', stroke: '#dc2626', 'stroke-width': 3 });
    });
    $('#addLine').click(() => {
      const p = randomPos();
      createElement('line', { x1: p.x, y1: p.y, x2: p.x+120, y2: p.y+40, stroke: '#333', 'stroke-width': 3 });
    });
    $('#addArrow').click(() => {
      const p = randomPos();
      createElement('line', { x1: p.x, y1: p.y, x2: p.x+120, y2: p.y+40, stroke: '#000', 'stroke-width': 3 }, true);
    });
    $('#addText').click(() => {
      const p = randomPos();
      const elt = createElement('text', { x: p.x, y: p.y, 'font-family': 'Arial', 'font-size': 20, fill: '#000', stroke: '#ccc', 'stroke-width': 0.5 });
      elt.textContent = 'Texto';
    });
    $('#deleteShape').click(() => {
      if (selectedElement) { selectedElement.remove(); clearSelection(); refreshElementList(); }
    });

    // Drag de formas SVG
    $('#shapes-group').on('mousedown', 'rect, circle, line, text', function(e) {
      e.preventDefault();
      e.stopPropagation(); // Evita interferir con el drag de estaciones
      const elt = this;
      selectShape(elt);
      const start = getSVGCoords(e);
      draggingShape = { element: elt, start: start, original: getOriginalPosition(elt) };
    });

    $(window).on('mousemove', function(e) {
      if (!draggingShape) return;
      e.preventDefault();
      const current = getSVGCoords(e);
      const dx = current.x - draggingShape.start.x;
      const dy = current.y - draggingShape.start.y;
      setPosition(draggingShape.element, draggingShape.original, dx, dy);
      const angle = getRotationAngle(draggingShape.element);
      applyRotation(draggingShape.element, angle);
      updateCommonFields(draggingShape.element);
    });

    $(window).on('mouseup', function() { draggingShape = null; });

    $('#workspace-svg').on('click', function(e) {
      if (e.target === this || e.target.tagName === 'rect' || e.target.tagName === 'circle' || e.target.tagName === 'line' || e.target.tagName === 'text') return;
      clearSelection();
    });

    // Atributos
    $(document).on('input change', '.shape-attr', function() {
      if (!selectedElement) return;
      const input = $(this);
      const attr = input.data('attr');
      let value = input.val();
      const tag = selectedElement.tagName;
      if (tag === 'text' && attr === 'content') {
        $(selectedElement).text(value);
      } else {
        selectedElement.setAttribute(attr, value);
      }
      if (selectedElement.classList.contains('arrow-line')) {
        selectedElement.setAttribute('marker-end', 'url(#arrowMarker)');
      }
      applyRotation(selectedElement, getRotationAngle(selectedElement));
      refreshElementList();
    });

    $('#common-x, #common-y').on('input', function() {
      if (!selectedElement) return;
      const x = parseFloat($('#common-x').val());
      const y = parseFloat($('#common-y').val());
      const tag = selectedElement.tagName;
      if (tag === 'rect') {
        const w = parseFloat(selectedElement.getAttribute('width')||0);
        const h = parseFloat(selectedElement.getAttribute('height')||0);
        selectedElement.setAttribute('x', x - w/2);
        selectedElement.setAttribute('y', y - h/2);
      } else if (tag === 'circle') {
        selectedElement.setAttribute('cx', x);
        selectedElement.setAttribute('cy', y);
      } else if (tag === 'line') {
        const dx = x - (parseFloat(selectedElement.getAttribute('x1')) + parseFloat(selectedElement.getAttribute('x2')))/2;
        const dy = y - (parseFloat(selectedElement.getAttribute('y1')) + parseFloat(selectedElement.getAttribute('y2')))/2;
        selectedElement.setAttribute('x1', parseFloat(selectedElement.getAttribute('x1')) + dx);
        selectedElement.setAttribute('y1', parseFloat(selectedElement.getAttribute('y1')) + dy);
        selectedElement.setAttribute('x2', parseFloat(selectedElement.getAttribute('x2')) + dx);
        selectedElement.setAttribute('y2', parseFloat(selectedElement.getAttribute('y2')) + dy);
      } else if (tag === 'text') {
        selectedElement.setAttribute('x', x);
        selectedElement.setAttribute('y', y);
      }
      applyRotation(selectedElement, getRotationAngle(selectedElement));
      updateCommonFields(selectedElement);
    });

    $('#common-rotate').on('input', function() {
      if (!selectedElement) return;
      const angle = parseInt($(this).val());
      $('#rotateValue').text(angle + '°');
      applyRotation(selectedElement, angle);
    });

    function refreshElementList() {
      const list = $('#elementList');
      list.empty();
      shapesGroup.children().each(function() {
        const elt = this;
        const tag = elt.tagName;
        let icon = '', typeName = '';
        if (tag === 'rect') {
          icon = elt.getAttribute('fill') !== 'none' ? '▭' : '▯';
          typeName = icon === '▭' ? 'Rectángulo' : 'Contorno';
        } else if (tag === 'circle') {
          icon = elt.getAttribute('fill') !== 'none' ? '●' : '○';
          typeName = icon === '●' ? 'Círculo' : 'Circ. contorno';
        } else if (tag === 'line') {
          icon = elt.classList.contains('arrow-line') ? '⇢' : '∕';
          typeName = icon === '⇢' ? 'Flecha' : 'Línea';
        } else if (tag === 'text') {
          icon = 'T';
          typeName = 'Texto';
        }
        if (!elt.getAttribute('data-list-id')) {
          elt.setAttribute('data-list-id', 'elem-' + (elementCounter++));
        }
        const itemId = elt.getAttribute('data-list-id');
        const listItem = $('<div class="element-list-item" data-element-id="' + itemId + '"></div>')
          .append('<span class="element-icon">' + icon + '</span>')
          .append('<span>' + typeName + '</span>');
        if (selectedElement === elt) listItem.addClass('selected-in-list');
        listItem.on('click', function() {
          const targetId = $(this).data('element-id');
          const targetElt = shapesGroup.find('[data-list-id="' + targetId + '"]').get(0);
          if (targetElt) selectShape(targetElt);
        });
        list.append(listItem);
      });
    }

    // Inicializar lista de elementos
    refreshElementList();

    // ========== INICIALIZACIÓN ORIGINAL (con adiciones) ==========
    document.addEventListener('DOMContentLoaded', function() {
      workspaceGrid = document.getElementById('workspaceGrid');
      getEstaciones();

      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl); });

      setupControls();
      dragSystem.init();
      updateZoomIndicator();
      mostrarTablaPNA();
      generarTablaAsistencia();
      getHistorialLayout();

      // Eventos originales
      nominaModalAsignar.addEventListener('change', function () {
        let nombreModalAsignar = document.getElementById('nombreModalAsignar');
        if(nominaModalAsignar && nominaModalAsignar !='') {
          let formDataConsultarNombre = new FormData;
          formDataConsultarNombre.append('nomina',nominaModalAsignar.value);
          formDataConsultarNombre.append('opcion', 7);
          formDataConsultarNombre.append('codigoLinea', codigoLinea.value);
          nominaModalAsignar.disabled = true;
          nombreModalAsignar.value= '';
          nombreModalAsignar.placeholder= "Consultando datos del empleado...";
          fetch("../api/operacionesLinea.php", {
            method: "POST",
            body: formDataConsultarNombre,
          })
          .then((response) => response.text())
          .then((data) => {
            data = JSON.parse(data);
            if(data.estatus=='ok'){ nombreModalAsignar.value = data.nombre; $('#listaOperacionesOperador').html(`${(data.estaciones) ? data.estaciones : 'SIN OPERACIONES ASIGNADAS EN LA LINEA'}`); }
            else { nombreModalAsignar.placeholder = "Nombre del empleado..."; $('#listaOperacionesOperador').html(`<span class="form-help">Lista de operaciones asignadas del trabajador en la linea</span>`); console.log(data.error); }
            nominaModalAsignar.disabled = false;
          })
          .catch((error) => { nombreModalAsignar.placeholder = "Nombre del empleado..."; nominaModalAsignar.disabled = false; console.log(error); });
        }
      });

      nominaNoAsignado.addEventListener('change', function(){
        let nombreNoAsignado = document.getElementById('nombreNoAsignado');
        if(nominaNoAsignado && nominaNoAsignado !='') {
          let formDataConsultarNombre = new FormData;
          formDataConsultarNombre.append('nomina',nominaNoAsignado.value);
          formDataConsultarNombre.append('opcion', 7);
          nominaNoAsignado.disabled = true;
          nombreNoAsignado.value= '';
          nombreNoAsignado.placeholder= "Consultando datos del empleado...";
          fetch("../api/operacionesLinea.php", {
            method: "POST",
            body: formDataConsultarNombre,
          })
          .then((response) => response.text())
          .then((data) => {
            data = JSON.parse(data);
            if(data.estatus=='ok') nombreNoAsignado.value = data.nombre;
            else { nombreNoAsignado.placeholder = "Nombre del empleado..."; console.log(data); }
            nominaNoAsignado.disabled = false;
          })
          .catch((error) => { nominaNoAsignado.disabled = false; nombreNoAsignado.placeholder = "Nombre del empleado..."; console.log(error); });
        }
      });

      nominaPC.addEventListener('change', function(){
        let nombrePC = document.getElementById('nombrePC');
        if(nominaPC && nominaPC !='') {
          nominaPC.disabled = true;
          nombrePC.value = '';
          nombrePC.placeholder = "Consultando datos del empleado...";
          let formDataConsultarNombre = new FormData;
          formDataConsultarNombre.append('nomina',nominaPC.value);
          formDataConsultarNombre.append('opcion', 7);
          fetch("../api/operacionesLinea.php", {
            method: "POST",
            body: formDataConsultarNombre,
          })
          .then((response) => response.text())
          .then((data) => {
            data = JSON.parse(data);
            if(data.estatus=='ok') nombrePC.value = data.nombre;
            else { nombrePC.placeholder = "Nombre del empleado..."; console.log(data); }
            nominaPC.disabled = false;
          })
          .catch((error) => { nominaPC.disabled = false; nombrePC.placeholder = "Nombre del empleado..."; console.log(error); });
        }
      });

      btnRemoverTrabajadorPC.addEventListener('click', function(){
        let formDataReniver = new FormData;
        let idPC = document.getElementById('idPC');
        let estacionId = document.getElementById('idEstacionModalPC').value;
        let nominaTrabajador = document.getElementById('idTrabajadorAsignado').value;
        let nombreTrabajador = $("#changeControlInfoNombre").text();
        let turno = $('#turnoLayout').val();
        formDataReniver.append("opcion", "10");
        formDataReniver.append("idEstacion", estacionId);
        formDataReniver.append("nomina", nominaTrabajador);
        formDataReniver.append("turno", turno);
        if(idPC.value){ alert('Debe finalizar el punto de cambio activo'); return; }
        if(nominaTrabajador == '' || nominaTrabajador == null){ alert('No hay trabajador asignado a esta estación'); return; }
        fetch("../api/operacionesLinea.php", {
          method: "POST",
          body: formDataReniver,
        })
        .then((response) => response.text())
        .then((data) => {
          data = JSON.parse(data);
          if(data.estatus=='ok'){
            alert(data.mensaje);
            let modalActual = bootstrap.Modal.getInstance(document.getElementById('changeControlModal'));
            (modalActual) ? modalActual.hide() : '';
            getEstacion(estacionId);
            if(data.asignacion==0){
              let registrar = confirm('¿Desea agregar a esta persona al personal disponible?');
              if(registrar) registrarDisponible(nominaTrabajador, nombreTrabajador, $('#turnoLayout').val());
            }
          } else alert(data.mensaje);
        })
        .catch((error) => { console.log(error); });
      });

      btnGuardarEdicionLinea.addEventListener('click', function(){
        let formDataActualizarLinea = new FormData;
        let lineForm = document.getElementById('lineForm');
        let descripcionLinea = document.getElementById('lineDescription').value;
        let encargadoLinea = document.getElementById('supervisorSearch').value;
        let lineName = document.getElementById('lineName').value;
        if (lineForm.reportValidity()){
          formDataActualizarLinea.append('opcion', 11);
          formDataActualizarLinea.append('codigoLinea', codigoLinea.value);
          formDataActualizarLinea.append('descripcion', descripcionLinea);
          formDataActualizarLinea.append('encargado', encargadoLinea);
          formDataActualizarLinea.append('nombreLinea', lineName);
          fetch("../api/operacionesLinea.php", {
            method: "POST",
            body: formDataActualizarLinea,
          })
          .then((response) => response.text())
          .then((data) => {
            data = JSON.parse(data);
            if(data.estatus=='ok'){ alert(data.mensaje); location.reload(); }
            else alert(data.mensaje);
          })
          .catch((error) => { console.log(error); });
        }
      });

      confirmChange.addEventListener('click', function(){
        let registroCambioForm = document.getElementById('registroCambioForm');
        let formDataPuntoCambio = new FormData;
        let idEstacion = document.getElementById('id_estacion').value;
        if(!registroCambioForm.reportValidity()) return;
        if(document.getElementById('nombrePC').value == '' || document.getElementById('nombrePC').value == null){
          alert('No se encontró registro del empleado ingresado o se perdió la conexión con el servidor.'); return;
        }
        let nominaEtiqueta = $("#changeControlInfoNomina").text().trim();
        let nominaInput = $("#nominaPC").val().trim();
        if (nominaInput !== "" && Number(nominaEtiqueta) === Number(nominaInput)) {
          alert("No se puede crear el punto de cambio ya que el trabajador está asignado a esta estación."); return;
        }
        formDataPuntoCambio.append('nominaPC', document.getElementById('nominaPC').value);
        formDataPuntoCambio.append('nombrePC', document.getElementById('nombrePC').value);
        formDataPuntoCambio.append('tipoCambio', document.getElementById('tipo_cambio').value);
        formDataPuntoCambio.append('fechaInicio', document.getElementById('fechaHora_inicio').value);
        formDataPuntoCambio.append('turno', document.getElementById('turnoPuntoCambio').value);
        formDataPuntoCambio.append('motivo', document.getElementById('motivo').value);
        formDataPuntoCambio.append('idEstacion', idEstacion);
        formDataPuntoCambio.append('codigoLinea', codigoLinea.value);
        formDataPuntoCambio.append('opcion', 13);
        fetch("../api/operacionesLinea.php", {
          method: "POST",
          body: formDataPuntoCambio,
        })
        .then((response) => response.text())
        .then((data) => {
          console.log(data);
          data = JSON.parse(data);
          if(data.estatus=='ok'){
            alert(data.mensaje);
            let modalActual = bootstrap.Modal.getInstance(document.getElementById('changeControlModal'));
            (modalActual) ? modalActual.hide() : '';
            getEstacion(idEstacion);
          } else alert(data.mensaje);
        })
        .catch((error) => { console.log(error); });
      });

      btnConfirmClose.addEventListener('click', function(){
        let formDataCerrarPC = new FormData;
        let idPC = document.getElementById('idPC');
        let idEstacion = document.getElementById('idEstacionModalPC').value;
        let cierreControlCambioForm = document.getElementById('cierreControlCambioForm');
        let nominaAPC = document.getElementById('idTrabajadorAsignado');
        let nombreTrabajador = $("#changeControlInfoNombre").text();
        if(!idPC.value){ alert('No hay un punto de cambio activo en esta estación'); return; }
        if(!cierreControlCambioForm.reportValidity()) return;
        formDataCerrarPC.append('opcion', 14);
        formDataCerrarPC.append('idEstacion', idEstacion);
        formDataCerrarPC.append('idPC', idPC.value);
        formDataCerrarPC.append('notasAdicionales', document.getElementById('notasAdicionales').value);
        formDataCerrarPC.append('fechaCierre', document.getElementById('fechaCierre').value);
        formDataCerrarPC.append('nomina', nominaAPC.value);
        fetch("../api/operacionesLinea.php", {
          method: "POST",
          body: formDataCerrarPC,
        })
        .then((response) => response.text())
        .then((data) => {
          data = JSON.parse(data);
          if(data.estatus=='ok'){
            alert(data.mensaje);
            let modalActual = bootstrap.Modal.getInstance(document.getElementById('changeControlModal'));
            (modalActual) ? modalActual.hide() : '';
            getEstacion(idEstacion);
            if(data.asignacion==0){
              let registrar = confirm('¿Desea agregar a esta persona al personal disponible?');
              if(registrar) registrarDisponible(nominaAPC.value, nombreTrabajador, $('#turnoLayout').val());
            }
          } else alert(data.mensaje);
        }).catch((error) => { console.log(error); });
      });

      document.getElementById('btnMenuAsignar').addEventListener('click', function (){
        document.getElementById('assignmentDate').value = (new Date()).toLocaleString('sv-SE').slice(0, 16);
        document.getElementById('turnoasignar').value = $('#turnoLayout').val();
      });

      document.getElementById('btnMenuRegiswtroNAD').addEventListener('click', function(){
        document.getElementById('assignmentDatePNA').value = (new Date()).toLocaleString('sv-SE').slice(0, 16);
        document.getElementById('turnoAsignarPersonalDisponible').value = $('#turnoLayout').val();
        mostrarTablaPNA();
      });

      btnGuardarEstacion.addEventListener('click', agregarEstacion);
      btnAsignarOperador.addEventListener('click', asignarEstaciones);
      btnGuardarDisponible.addEventListener('click', registrarPNA);
      btnRegistrarAsistencia.addEventListener('click', registrarAsistencia);
      btnInfoRPC.addEventListener('click', function(){ changeContent('ventanasModalPC','contInfoEstacion'); });
      btnRegistroPc.addEventListener('click', function(){ changeContent('ventanasModalPC','contregistroCambioForm'); });
      btnLiberarPC.addEventListener('click', function(){ changeContent('ventanasModalPC', 'contLiberarPC'); });
      btnTablaPNA.addEventListener('click', function(){ changeContent('ventanadModalPersonalNA', 'contTablaDisponibles'); });
      btnRegistroPNA.addEventListener('click', function(){ changeContent('ventanadModalPersonalNA', 'contRegistroPersonalDisponible'); });
      btnMenuRegistroAs.addEventListener('click', generarTablaAsistencia);
      btnCambioTurno.addEventListener('click', cambiarTurno);

      fechaHistorial.addEventListener('change', getHistorialLayout);
      turnoHistorial.addEventListener('change', getHistorialLayout);

      $('#attendanceTable tbody').on('change', 'select', function () { updateAsistencia(this, 'estatus'); });
      $('#attendanceTable tbody').on('keydown', 'input', function (e) { if (e.key === 'Enter') { e.preventDefault(); updateAsistencia(this, 'observacionesAsistencia'); } });

      checkPadre.addEventListener('change', function(){
        if (checkPadre.checked) {
          seleccionadosGlobal = [...datosAsistenciaCheck];
          $('#attendanceTable').DataTable().rows({ page: 'all' }).every(function () { $(this.node()).find('input[type="checkbox"]').prop('checked', true); });
        } else {
          seleccionadosGlobal = [];
          $('#attendanceTable').DataTable().rows({ page: 'all' }).every(function () { $(this.node()).find('input[type="checkbox"]').prop('checked', false); });
        }
      });

      $('#attendanceTable tbody').on('change', 'input[type="checkbox"]', function(){
        const nomina = $(this).data('nomina');
        const index = seleccionadosGlobal.indexOf(nomina);
        if(this.checked && index === -1) seleccionadosGlobal.push(nomina);
        else if(!this.checked && index > -1) seleccionadosGlobal.splice(index, 1);
        checkPadre.checked = (seleccionadosGlobal.length < datosAsistenciaCheck.length) ? false : true;
      });

      document.getElementById('turnoLayout').addEventListener('change', function(){
        let turno = $("#turnoLayout").val();
        if(turno){
          document.getElementById('workspaceGrid').innerHTML = '';
          getEstaciones();
        }
      });

      document.getElementById('idRH').addEventListener('change', function() {
        let idRH = document.getElementById('idRH').value;
        if(!fechaHistorial.value || !idRH) { alert('No se encontró algún registro en la fecha y turno seleccionados'); return; }
        const formData = new FormData();
        formData.append('opcion', 23);
        formData.append('idR', idRH);
        fetch("../api/operacionesLinea.php", {
          method: "POST",
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          console.log(data);
          const grid = document.getElementById('historialWorkspaceGrid');
          grid.innerHTML = '';
          if(data.estatus === 'ok') {
            const stations = data.layout;
            stations.forEach(station => { createStationHistorial(station, grid); });
          } else { alert(data.mensaje || 'Error al cargar el historial'); }
        })
        .catch(error => { console.error(error); alert('Error de conexión'); });
      });
    });
  </script>
</body>
</html>