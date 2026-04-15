<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Diagramador · lista de elementos · bordes redondos</title>
  <!-- Bootstrap 5 + jQuery -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <style>
    body {
      background: #eef2f6;
      font-family: 'Segoe UI', Roboto, sans-serif;
      user-select: none;
    }
    .workspace-wrapper {
      background: #1e2b3a;
      padding: 20px;
      border-radius: 32px;
      box-shadow: 0 20px 30px rgba(0,0,0,0.3);
    }
    #workspace {
      display: block;
      width: 100%;
      height: auto;
      background: white;
      border-radius: 24px;
      box-shadow: inset 0 0 0 1px rgba(255,255,255,0.3), 0 10px 20px rgba(0,0,0,0.2);
      cursor: default;
    }
    .toolbox-card {
      background: rgba(255,255,255,0.75);
      backdrop-filter: blur(12px);
      border-radius: 32px;
      border: 1px solid rgba(255,255,255,0.5);
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
      padding: 24px 16px;
    }
    .btn-tool {
      border-radius: 60px !important;
      margin-bottom: 10px;
      padding: 12px 0;
      font-weight: 600;
      border: none;
      background: white;
      color: #1e293b;
      box-shadow: 0 4px 8px rgba(0,0,0,0.02);
      transition: all 0.15s;
    }
    .btn-tool:hover {
      background: #f8fafc;
      transform: scale(1.02);
      box-shadow: 0 8px 14px rgba(0,0,0,0.05);
    }
    .btn-tool i { 
        margin-right: 6px; 
        font-style: normal; 
    }
    .attribute-panel {
      background: rgba(255,255,255,0.8);
      backdrop-filter: blur(8px);
      border-radius: 40px;
      padding: 24px 18px;
      margin-top: 24px;
      border: 1px solid rgba(255,255,255,0.6);
    }
    
    .attr-section {
      display: none;
    }
    
    .attr-section.active-section {
      display: block;
    }
    
    .form-control, .form-select, .form-control-color {
      border-radius: 40px !important;
      border: 1px solid #d0d9e8;
      background: white;
    }
    .selected {
      filter: drop-shadow(0 0 12px #2563eb) drop-shadow(0 0 4px #1e40af);
      transition: filter 0.3s;
    }

    /* 
      clase para SVG, define la parte que detecta los clics, la parte visivle (no transparente ni oculto), el borde el relleno, todo o ninguno, por defecto es visible
      Dependiendo del valor de la propiedad el leento puede dejar pasar los clics o no, por ejemplo si hay un elemento sin relleno solo con borde y denao hay otro elemento
      el click lo podria recibir el rellano o el objeto que esta por debajo del elemento que no tiene relleno o esta dentro del borde del elemnto que esta encima en el caso de la propuedad visible
    */

    .grid-bg {
      pointer-events: visible;
    }
    
    .badge-info {
      background: #dbeafe;
      color: #1e40af;
      border-radius: 40px;
      padding: 4px 12px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    .rotation-slider {
      width: 100%;
      accent-color: #2563eb;
    }
    .small-note {
      font-size: 0.7rem;
      color: #4b5563;
      margin-top: 4px;
    }
    /* Lista de elementos */
    .element-list {
      max-height: 500px;
      overflow-y: auto;
      background: rgba(255,255,255,0.5);
      border-radius: 30px;
      padding: 10px;
      margin-bottom: 15px;
    }
    .element-list-item {
      display: flex;
      align-items: center;
      padding: 8px 12px;
      margin-bottom: 4px;
      background: white;
      border-radius: 40px;
      cursor: pointer;
      transition: 0.1s;
      border: 1px solid transparent;
    }
    .element-list-item:hover {
      background: #e9ecef;
      border-color: #2563eb;
    }
    .element-list-item.selected-in-list {
      background: #dbeafe;
      border-color: #2563eb;
      font-weight: 500;
    }
    .element-icon {
      width: 24px;
      text-align: center;
      margin-right: 10px;
      font-size: 1.2rem;
    }
  </style>
</head>
<body class="p-4">

<div class="container-fluid">
      <div class="row g-4">
        <!-- Columna herramientas + atributos -->
        <div class="col-lg-1 col-md-4">
          <div class="toolbox-card">
            <h5 class="fw-bold mb-4" style="color: #0f172a;">🧰</h5>
            <div class="d-grid">
              <button class="btn btn-tool" id="addRectFill"><i>▭</i></button>
              <button class="btn btn-tool" id="addRectOutline"><i>▯</i></button>
              <button class="btn btn-tool" id="addCircleFill"><i>●</i></button>
              <button class="btn btn-tool" id="addCircleOutline"><i>○</i></button>
              <button class="btn btn-tool" id="addLine"><i>∕</i></button>
              <button class="btn btn-tool" id="addArrow"><i>⇢</i></button>
              <button class="btn btn-tool" id="addText"><i>T</i></button>
              <hr class="my-2">
              <button class="btn btn-outline-danger btn-tool" id="deleteShape"><i>✕</i></button>
            </div>
          </div>
        </div>

        <!-- Área de trabajo (cuadrícula) -->
        <div class="col-lg-9 col-md-8">
          <div class="workspace-wrapper">

          <div class="d-flex gap-2 mb-2">
            <button class="btn btn-sm btn-outline-primary" id="zoomIn">➕ Zoom In</button>
            <button class="btn btn-sm btn-outline-primary" id="zoomOut">➖ Zoom Out</button>
            <button class="btn btn-sm btn-outline-secondary" id="zoomReset">⟲ Reset</button>
        </div>

        <svg id="workspace" width="900" height="600" viewBox="0 0 900 600" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <pattern id="grid" patternUnits="userSpaceOnUse" width="20" height="20">
              <path d="M 20 0 L 0 0 0 20" fill="none" stroke="#cbd5e1" stroke-width="0.8"/>
            </pattern>
            <marker id="arrowMarker" markerWidth="10" markerHeight="10" refX="9" refY="5" orient="auto">
              <polygon points="0 0, 9 5, 0 10" fill="context-stroke" stroke="none" />
            </marker>
          </defs>
          <rect x="0" y="0" width="100%" height="100%" fill="url(#grid)" class="grid-bg" id="gridBackground" />
          <!-- Contenedor donde se cargan las figuras-->
          <g id="shapes-group"></g>
        </svg>
      </div>
      <div class="d-flex justify-content-between mt-2 text-muted small">
        <span>🖱️ Click para seleccionar · Arrastra para mover · Atributos abajo</span>
        <span>🧊 Contenedores: rectángulos sin relleno</span>
      </div>
    </div>

    <div class="col-lg-2">
      <!-- Navegación de tabs -->
        <ul class="nav nav-tabs mb-2" id="panelTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-attrs">
                    ⚙️
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-elements">
                    📋
                </button>
            </li>
        </ul>

         <div class="tab-content">
            <!-- Lista de atributos-->
            <div class="tab-pane fade show active" id="tab-attrs">
                <div class="attribute-panel">
                    <h6 class="fw-semibold">⚙️ Atributos</h6>
                    <div id="noSelectionMsg" class="text-muted small text-center py-1">
                      Ningún elemento seleccionado
                    </div>

                    <!-- Sección común a todas las formas (posición + rotación) -->
                    <div class="common-attrs" style="display: none;">
                        <label class="form-label fw-semibold">Posición X/Y</label>
                        <div class="row g-2">
                        <div class="col-12"><input type="number" class="form-control" id="common-x" placeholder="X" step="1" readonly></div>
                        <div class="col-12"><input type="number" class="form-control" id="common-y" placeholder="Y" step="1" readonly></div>
                        </div>
                        <label class="form-label fw-semibold">Rotación (grados)</label>
                        <input type="range" class="rotation-slider" id="common-rotate" min="0" max="360" value="0" step="1">
                        <div class="d-flex justify-content-between small-note">
                        <span>0°</span><span id="rotateValue">0°</span><span>360°</span>
                        </div>
                    </div>

                    <!-- Rectángulo: dimensiones, relleno, borde, y ahora rx/ry -->
                    <div class="attr-section" id="rect-attrs">
                        <label class="form-label">Ancho</label>
                        <input type="number" class="form-control mb-2 shape-attr" id="rect-width" data-attr="width" value="100" step="1" min="5">
                        <label class="form-label">Alto</label>
                        <input type="number" class="form-control mb-2 shape-attr" id="rect-height" data-attr="height" value="70" step="1" min="5">
                        <label class="form-label">Radio borde (rx, ry)</label>
                        <div class="row g-2 mb-2">
                        <div class="col-6"><input type="number" class="form-control shape-attr" id="rect-rx" data-attr="rx" value="10" step="1" min="0"></div>
                        <div class="col-6"><input type="number" class="form-control shape-attr" id="rect-ry" data-attr="ry" value="10" step="1" min="0"></div>
                        </div>
                        <label class="form-label">Relleno</label>
                        <input type="color" class="form-control-color mb-2 shape-attr" id="rect-fill" data-attr="fill" value="#ffaa00">
                        <label class="form-label">Borde</label>
                        <input type="color" class="form-control-color mb-2 shape-attr" id="rect-stroke" data-attr="stroke" value="#000000">
                        <label class="form-label">Grosor borde</label>
                        <input type="number" class="form-control shape-attr" id="rect-stroke-width" data-attr="stroke-width" value="2" step="0.5" min="0">
                    </div>

                    <!-- Círculo: dimensiones, relleno, borde, radius : rx/ry -->
                    <div class="attr-section" id="circle-attrs">
                        <label class="form-label">Radio</label>
                        <input type="number" class="form-control mb-2 shape-attr" id="circle-r" data-attr="r" value="35" step="1" min="5">
                        <label class="form-label">Relleno</label>
                        <input type="color" class="form-control-color mb-2 shape-attr" id="circle-fill" data-attr="fill" value="#44cc88">
                        <label class="form-label">Borde</label>
                        <input type="color" class="form-control-color mb-2 shape-attr" id="circle-stroke" data-attr="stroke" value="#000000">
                        <label class="form-label">Grosor borde</label>
                        <input type="number" class="form-control shape-attr" id="circle-stroke-width" data-attr="stroke-width" value="2" step="0.5" min="0">
                    </div>

                    <!-- Línea / flecha -->
                    <div class="attr-section" id="line-attrs">
                        <label class="form-label">X1</label>
                        <input type="number" class="form-control mb-2 shape-attr" id="line-x1" data-attr="x1" step="1">
                        <label class="form-label">Y1</label>
                        <input type="number" class="form-control mb-2 shape-attr" id="line-y1" data-attr="y1" step="1">
                        <label class="form-label">X2</label>
                        <input type="number" class="form-control mb-2 shape-attr" id="line-x2" data-attr="x2" step="1">
                        <label class="form-label">Y2</label>
                        <input type="number" class="form-control mb-2 shape-attr" id="line-y2" data-attr="y2" step="1">
                        <label class="form-label">Color</label>
                        <input type="color" class="form-control-color mb-2 shape-attr" id="line-stroke" data-attr="stroke" value="#333333">
                        <label class="form-label">Grosor</label>
                        <input type="number" class="form-control shape-attr" id="line-stroke-width" data-attr="stroke-width" value="3" step="0.5" min="1">
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
                        <input type="number" class="form-control mb-2 shape-attr" id="text-font-size" data-attr="font-size" value="20" step="1" min="6">
                        <label class="form-label">Color</label>
                        <input type="color" class="form-control-color mb-2 shape-attr" id="text-fill" data-attr="fill" value="#000000">
                        <label class="form-label">Borde (trazo)</label>
                        <input type="color" class="form-control-color mb-2 shape-attr" id="text-stroke" data-attr="stroke" value="#cccccc">
                        <label class="form-label">Grosor borde</label>
                        <input type="number" class="form-control shape-attr" id="text-stroke-width" data-attr="stroke-width" value="0.5" step="0.5" min="0">
                    </div>
                </div>
            </div>

            <!-- Lista de elementos -->
            <div class="tab-pane fade" id="tab-elements">
                <div class="mt-4">
                    <h6 class="fw-semibold mb-2">📋 Elementos</h6>
                    <div class="element-list" id="elementList"></div>
                </div>
            </div>
         </div>

        <!-- Panel de atributos dinámico -->
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    const svg = document.getElementById('workspace');
    const shapesGroup = $('#shapes-group');
    let selectedElement = null;
    let dragging = null;
    let currentShapeType = '';

    // Contador para IDs únicos de elementos
    let elementCounter = 0;

    // Variables de zoom
    let currentZoom = 1; // factor de zoom, 1 = 100%
    const zoomStep = 0.2; //Proporcion de incremento o decremento del zoom 
    const svgElement = document.getElementById('workspace');
    const originalViewBox = svgElement.getAttribute('viewBox').split(' ').map(Number); // [x, y, width, height]

    //Funcion para dar zoom al elemento
    function applyZoom() {
        const [x, y, baseWidth, baseHeight] = originalViewBox;
        const newWidth = baseWidth / currentZoom;
        const newHeight = baseHeight / currentZoom;
        // Centrar el viewBox para que el zoom se aplique desde el centro
        const newX = x + (baseWidth - newWidth) / 2;
        const newY = y + (baseHeight - newHeight) / 2;
        svgElement.setAttribute('viewBox', `${newX} ${newY} ${newWidth} ${newHeight}`);
    }

    // ----- Funciones de lista de elementos -----
    function refreshElementList() {
      const list = $('#elementList');
      list.empty();
      shapesGroup.children().each(function(index, elt) {
        const tag = elt.tagName;
        let icon = '';
        let typeName = '';
        if (tag === 'rect') {
          const hasFill = elt.getAttribute('fill') !== 'none';
          icon = hasFill ? '▭' : '▯';
          typeName = hasFill ? 'Rectángulo' : 'Contorno';
        } else if (tag === 'circle') {
          const hasFill = elt.getAttribute('fill') !== 'none';
          icon = hasFill ? '●' : '○';
          typeName = hasFill ? 'Círculo' : 'Circ. contorno';
        } else if (tag === 'line') {
          if (elt.classList.contains('arrow-line')) {
            icon = '⇢';
            typeName = 'Flecha';
          } else {
            icon = '∕';
            typeName = 'Línea';
          }
        } else if (tag === 'text') {
          icon = 'T';
          typeName = 'Texto';
        }
        // Usar un id único si no existe
        let itemId = elt.getAttribute('data-list-id');
        if (!itemId) {
          itemId = 'elem-' + (elementCounter++);
          elt.setAttribute('data-list-id', itemId);
        }
        const listItem = $('<div class="element-list-item" data-element-id="' + itemId + '"></div>');
        listItem.append('<span class="element-icon">' + icon + '</span>');
        listItem.append('<span>' + typeName + ' ' + (index + 1) + '</span>');
        if (selectedElement === elt) {
          listItem.addClass('selected-in-list');
        }
        listItem.on('click', function(e) {
          e.stopPropagation();
          const targetId = $(this).data('element-id');
          const targetElt = shapesGroup.find('[data-list-id="' + targetId + '"]').get(0);
          if (targetElt) {
            selectShape(targetElt);
          }
        });
        list.append(listItem);
      });
    }

    // ----- Llamar refresh después de cualquier cambio en la lista de elementos
    function afterShapeChange() {
      refreshElementList();
    }

    // ----- Helper: obtener coordenadas SVG -----
    function getSVGCoords(e) {
      const pt = svg.createSVGPoint();
      pt.x = e.clientX;
      pt.y = e.clientY;
      return pt.matrixTransform(svg.getScreenCTM().inverse());
    }

    // ----- Obtener centro actual del elemento -----
    function getElementCenter(elt) {
      const tag = elt.tagName;
      let cx = 0, cy = 0;
      if (tag === 'rect') {
        const x = parseFloat(elt.getAttribute('x') || 0);
        const y = parseFloat(elt.getAttribute('y') || 0);
        const w = parseFloat(elt.getAttribute('width') || 0);
        const h = parseFloat(elt.getAttribute('height') || 0);
        cx = x + w/2;
        cy = y + h/2;
      } else if (tag === 'circle') {
        cx = parseFloat(elt.getAttribute('cx') || 0);
        cy = parseFloat(elt.getAttribute('cy') || 0);
      } else if (tag === 'line') {
        const x1 = parseFloat(elt.getAttribute('x1') || 0);
        const y1 = parseFloat(elt.getAttribute('y1') || 0);
        const x2 = parseFloat(elt.getAttribute('x2') || 0);
        const y2 = parseFloat(elt.getAttribute('y2') || 0);
        cx = (x1 + x2) / 2;
        cy = (y1 + y2) / 2;
      } else if (tag === 'text') {
        cx = parseFloat(elt.getAttribute('x') || 0);
        cy = parseFloat(elt.getAttribute('y') || 0);
      }
      return { cx, cy };
    }

    // ----- Obtener ángulo de rotación actual (desde atributo data) -----
    function getRotationAngle(elt) {
      let angle = parseFloat(elt.getAttribute('data-rotation'));
      if (!isNaN(angle)) return angle;
      return 0;
    }

    // ----- Aplicar rotación al elemento con el centro actual -----
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

    // ----- Guardar estado original para drag -----
    function getOriginalPosition(elt) {
      const tag = elt.tagName;
      const pos = {};
      if (tag === 'rect') {
        pos.x = parseFloat(elt.getAttribute('x') || 0);
        pos.y = parseFloat(elt.getAttribute('y') || 0);
      } else if (tag === 'circle') {
        pos.cx = parseFloat(elt.getAttribute('cx') || 0);
        pos.cy = parseFloat(elt.getAttribute('cy') || 0);
      } else if (tag === 'line') {
        pos.x1 = parseFloat(elt.getAttribute('x1') || 0);
        pos.y1 = parseFloat(elt.getAttribute('y1') || 0);
        pos.x2 = parseFloat(elt.getAttribute('x2') || 0);
        pos.y2 = parseFloat(elt.getAttribute('y2') || 0);
      } else if (tag === 'text') {
        pos.x = parseFloat(elt.getAttribute('x') || 0);
        pos.y = parseFloat(elt.getAttribute('y') || 0);
      }
      return pos;
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

    // ----- Actualizar campos comunes de posición/rotación -----
    function updateCommonFields(elt) {
      if (!elt) return;
      const tag = elt.tagName;
      let x, y;
      if (tag === 'rect') {
        x = parseFloat(elt.getAttribute('x') || 0);
        y = parseFloat(elt.getAttribute('y') || 0);
      } else if (tag === 'circle') {
        x = parseFloat(elt.getAttribute('cx') || 0);
        y = parseFloat(elt.getAttribute('cy') || 0);
      } else if (tag === 'line') {
        let x1 = parseFloat(elt.getAttribute('x1') || 0);
        let y1 = parseFloat(elt.getAttribute('y1') || 0);
        let x2 = parseFloat(elt.getAttribute('x2') || 0);
        let y2 = parseFloat(elt.getAttribute('y2') || 0);
        x = (x1 + x2) / 2;
        y = (y1 + y2) / 2;
      } else if (tag === 'text') {
        x = parseFloat(elt.getAttribute('x') || 0);
        y = parseFloat(elt.getAttribute('y') || 0);
      }
      $('#common-x').val(Math.round(x));
      $('#common-y').val(Math.round(y));

      const angle = getRotationAngle(elt);
      $('#common-rotate').val(angle);
      $('#rotateValue').text(angle + '°');
    }

    // ----- Cargar atributos específicos al panel -----
    function loadAttributesToPanel(elt) {
      if (!elt) return;
      const tag = elt.tagName;
      const isArrow = elt.classList.contains('arrow-line');

      updateCommonFields(elt);

      if (tag === 'rect') {
        $('#rect-width').val(elt.getAttribute('width') || 100);
        $('#rect-height').val(elt.getAttribute('height') || 70);
        $('#rect-rx').val(elt.getAttribute('rx') || 10);
        $('#rect-ry').val(elt.getAttribute('ry') || 10);
        $('#rect-fill').val(elt.getAttribute('fill') || '#ffaa00');
        $('#rect-stroke').val(elt.getAttribute('stroke') || '#000000');
        $('#rect-stroke-width').val(elt.getAttribute('stroke-width') || '2');
      }
      else if (tag === 'circle') {
        $('#circle-r').val(elt.getAttribute('r') || 35);
        $('#circle-fill').val(elt.getAttribute('fill') || '#44cc88');
        $('#circle-stroke').val(elt.getAttribute('stroke') || '#000000');
        $('#circle-stroke-width').val(elt.getAttribute('stroke-width') || '2');
      }
      else if (tag === 'line') {
        $('#line-x1').val(elt.getAttribute('x1') || 0);
        $('#line-y1').val(elt.getAttribute('y1') || 0);
        $('#line-x2').val(elt.getAttribute('x2') || 100);
        $('#line-y2').val(elt.getAttribute('y2') || 50);
        $('#line-stroke').val(elt.getAttribute('stroke') || '#333333');
        $('#line-stroke-width').val(elt.getAttribute('stroke-width') || '3');
        if (isArrow) $('#arrow-indicator').show(); else $('#arrow-indicator').hide();
      }
      else if (tag === 'text') {
        $('#text-content').val($(elt).text() || '');
        $('#text-font-family').val(elt.getAttribute('font-family') || 'Arial');
        $('#text-font-size').val(elt.getAttribute('font-size') || 20);
        $('#text-fill').val(elt.getAttribute('fill') || '#000000');
        $('#text-stroke').val(elt.getAttribute('stroke') || '#cccccc');
        $('#text-stroke-width').val(elt.getAttribute('stroke-width') || '0.5');
      }
    }

    // ----- Seleccionar figura -----
    function selectShape(elt) {
      $('.selected').removeClass('selected');
      $(elt).addClass('selected');
      selectedElement = elt;
      let type = elt.tagName;
      if (type === 'line' && elt.classList.contains('arrow-line')) type = 'arrow';
      currentShapeType = type;

      $('.attr-section').removeClass('active-section').hide();
      $('#noSelectionMsg').hide();
      $('.common-attrs').show();

      let panelId = '';
      if (type === 'rect') panelId = '#rect-attrs';
      else if (type === 'circle') panelId = '#circle-attrs';
      else if (type === 'line' || type === 'arrow') panelId = '#line-attrs';
      else if (type === 'text') panelId = '#text-attrs';

      if (panelId) $(panelId).addClass('active-section').show();
      loadAttributesToPanel(elt);
      
      // Resaltar en la lista
      $('.element-list-item').removeClass('selected-in-list');
      const itemId = elt.getAttribute('data-list-id');
      if (itemId) {
        $('.element-list-item[data-element-id="' + itemId + '"]').addClass('selected-in-list');
      }
    }

    function clearSelection() {
      $('.selected').removeClass('selected');
      selectedElement = null;
      currentShapeType = '';
      $('.attr-section').hide();
      $('#noSelectionMsg').show();
      $('.common-attrs').hide();
      $('.element-list-item').removeClass('selected-in-list');
    }

    // ----- Creación de figuras -----
    function randomPos(maxX = 800, maxY = 500) {
      return { x: 80 + Math.random() * (maxX - 160), y: 60 + Math.random() * (maxY - 120) };
    }

    function createElement(tag, attrs, isArrow = false) {
      const elt = document.createElementNS('http://www.w3.org/2000/svg', tag);
      for (let key in attrs) {
        elt.setAttribute(key, attrs[key]);
      }
      elt.setAttribute('data-rotation', 0);
      if (isArrow) {
        elt.classList.add('arrow-line');
        elt.setAttribute('marker-end', 'url(#arrowMarker)');
      }
      shapesGroup.append(elt);
      afterShapeChange();
      return elt;
    }

    //Eventos de zoom
        $('#zoomIn').click(() => {
            currentZoom = Math.min(currentZoom + zoomStep, 3); // límite máximo 300%
            applyZoom();
        });

        $('#zoomOut').click(() => {
            currentZoom = Math.max(currentZoom - zoomStep, 0.5); // límite mínimo 50%
            applyZoom();
        });

        $('#zoomReset').click(() => {
            currentZoom = 1;
            svgElement.setAttribute('viewBox', originalViewBox.join(' '));
        });
    //fin eventos de zoom

    $('#addRectFill').click(() => {
      const p = randomPos();
      const elt = createElement('rect', {
        x: p.x, 
        y: p.y, 
        width: 100, 
        height: 70,
        rx: 10, 
        ry: 10,
        fill: '#ffaa00', 
        stroke: '#000000', 'stroke-width': 2
      });
      selectShape(elt);
    });

    $('#addRectOutline').click(() => {
      const p = randomPos();
      const elt = createElement('rect', {
        x: p.x, 
        y: p.y, 
        width: 120, 
        height: 80,
        rx: 10, 
        ry: 10,
        fill: 'none', 
        stroke: '#2563eb', 
        'stroke-width': 3, 
        //'stroke-dasharray': '5,3'
      });
      selectShape(elt);
    });

    $('#addCircleFill').click(() => {
      const p = randomPos();
      const elt = createElement('circle', {
        cx: p.x, 
        cy: p.y, 
        r: 35,
        fill: '#44cc88', 
        stroke: '#000000', 
        'stroke-width': 2
      });
      selectShape(elt);
    });

    $('#addCircleOutline').click(() => {
      const p = randomPos();
      const elt = createElement('circle', {
        cx: p.x, cy: p.y, r: 40,
        fill: 'none', stroke: '#dc2626', 'stroke-width': 3
      });
      selectShape(elt);
    });

    $('#addLine').click(() => {
      const p = randomPos(700, 450);
      const elt = createElement('line', {
        x1: p.x, 
        y1: p.y, 
        x2: p.x + 120, 
        y2: p.y + 40,
        stroke: '#333333', 
        'stroke-width': 3
      });
      selectShape(elt);
    });

    $('#addArrow').click(() => {
      const p = randomPos(700, 450);
      const elt = createElement('line', {
        x1: p.x, 
        y1: p.y, 
        x2: p.x + 120, 
        y2: p.y + 40,
        stroke: '#000000', 
        'stroke-width': 3
      }, true); // isArrow = true
      selectShape(elt);
    });

    $('#addText').click(() => {
      const p = randomPos();
      const elt = createElement('text', {
        x: p.x, 
        y: p.y,
        'font-family': 'Arial', 
        'font-size': 20,
        fill: '#000000', 
        stroke: '#cccccc', 
        'stroke-width': 0.5
      });

      elt.textContent = 'Texto';
      selectShape(elt);
    });

    $('#deleteShape').click(() => {
      if (selectedElement) { 
        selectedElement.remove(); 
        clearSelection();
        afterShapeChange();
      }
    });

    // ----- Drag & drop (mover) -----
    $('#shapes-group')
      .on('mousedown', 'rect, circle, line, text', function(e) {
        e.preventDefault();
        const elt = this;
        selectShape(elt);
        const start = getSVGCoords(e);
        dragging = {
          element: elt,
          start: start,
          original: getOriginalPosition(elt)
        };
    });

    $(window).on('mousemove', function(e) {
      if (!dragging) return;
      e.preventDefault();
      const current = getSVGCoords(e);
      const dx = current.x - dragging.start.x;
      const dy = current.y - dragging.start.y;
      setPosition(dragging.element, dragging.original, dx, dy);
      
      // Reaplicar rotación con el nuevo centro
      const angle = getRotationAngle(dragging.element);
      applyRotation(dragging.element, angle);
      
      updateCommonFields(dragging.element);
      // La lista no cambia, solo posición
    });

    $(window).on('mouseup', function() { dragging = null; });

    $('#gridBackground').on('click', clearSelection);

    // ----- Atributos: cambios en inputs específicos -----
    $(document).on('input change', '.shape-attr', function() {
      if (!selectedElement) return;
      const input = $(this);
      const attr = input.data('attr');
      let value = input.val();
      const tag = selectedElement.tagName;
      const isArrow = selectedElement.classList.contains('arrow-line');

      if (tag === 'text' && attr === 'content') {
        $(selectedElement).text(value);
        afterShapeChange(); // actualiza lista (el nombre no cambia, pero por si acaso)
        return;
      }
      if (tag === 'text' && (attr === 'font-family' || attr === 'font-size')) {
        selectedElement.setAttribute(attr, value);
        const angle = getRotationAngle(selectedElement);
        applyRotation(selectedElement, angle);
        return;
      }
      if (attr) selectedElement.setAttribute(attr, value);
      if (isArrow) selectedElement.setAttribute('marker-end', 'url(#arrowMarker)');
      
      const angle = getRotationAngle(selectedElement);
      applyRotation(selectedElement, angle);
      
      // Si cambia fill, puede cambiar el ícono en la lista (rect/circle con/sin relleno)
      afterShapeChange();
    });

    // ----- Posición común y rotación -----
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
        const dx = x - (parseFloat(selectedElement.getAttribute('x1')) + parseFloat(selectedElement.getAttribute('x2'))) / 2;
        const dy = y - (parseFloat(selectedElement.getAttribute('y1')) + parseFloat(selectedElement.getAttribute('y2'))) / 2;
        let x1 = parseFloat(selectedElement.getAttribute('x1')) + dx;
        let y1 = parseFloat(selectedElement.getAttribute('y1')) + dy;
        let x2 = parseFloat(selectedElement.getAttribute('x2')) + dx;
        let y2 = parseFloat(selectedElement.getAttribute('y2')) + dy;
        selectedElement.setAttribute('x1', x1);
        selectedElement.setAttribute('y1', y1);
        selectedElement.setAttribute('x2', x2);
        selectedElement.setAttribute('y2', y2);
      } else if (tag === 'text') {
        selectedElement.setAttribute('x', x);
        selectedElement.setAttribute('y', y);
      }
      const angle = getRotationAngle(selectedElement);
      applyRotation(selectedElement, angle);
      updateCommonFields(selectedElement);
    });

    $('#common-rotate').on('input', function() {
      if (!selectedElement) return;
      const angle = parseInt($(this).val());
      $('#rotateValue').text(angle + '°');
      applyRotation(selectedElement, angle);
    });

    // Inicializar lista
    afterShapeChange();
  });
</script>

<!-- Bootstrap JS (opcional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>