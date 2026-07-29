<!-- Barra izquierda: herramientas de dibujo -->
<aside class="tools-sidebar designer-tools-sidebar" id="tools-sidebar" aria-label="Herramientas de diseño">
  <div class="tools-sidebar-header">
    <span class="tools-brand-mark"><i class="bi bi-bounding-box"></i></span>
    <div class="tools-brand-copy">
      <strong>Diseño</strong>
      <span>elementos del layout</span>
    </div>
  </div>

  <div class="tools-section-label">Elementos</div>
  <div class="tools-menu">
    <button type="button" class="tool-btn drawing-btn" id="addRectFill"
            data-tooltip="Rectángulo relleno" aria-label="Agregar rectángulo relleno">
      <span class="tool-btn-content">
        <span class="tool-icon"><i class="bi bi-square-fill"></i></span>
        <span class="tool-copy"><strong>Rectángulo</strong><small>Con relleno</small></span>
      </span>
    </button>

    <button type="button" class="tool-btn drawing-btn" id="addRectOutline"
            data-tooltip="Rectángulo sin relleno" aria-label="Agregar rectángulo sin relleno">
      <span class="tool-btn-content">
        <span class="tool-icon"><i class="bi bi-square"></i></span>
        <span class="tool-copy"><strong>Rectángulo</strong><small>Solo contorno</small></span>
      </span>
    </button>

    <button type="button" class="tool-btn drawing-btn" id="addCircleFill"
            data-tooltip="Círculo relleno" aria-label="Agregar círculo relleno">
      <span class="tool-btn-content">
        <span class="tool-icon"><i class="bi bi-circle-fill"></i></span>
        <span class="tool-copy"><strong>Círculo</strong><small>Con relleno</small></span>
      </span>
    </button>

    <button type="button" class="tool-btn drawing-btn" id="addCircleOutline"
            data-tooltip="Círculo sin relleno" aria-label="Agregar círculo sin relleno">
      <span class="tool-btn-content">
        <span class="tool-icon"><i class="bi bi-circle"></i></span>
        <span class="tool-copy"><strong>Círculo</strong><small>Solo contorno</small></span>
      </span>
    </button>

    <button type="button" class="tool-btn drawing-btn" id="addLine"
            data-tooltip="Línea" aria-label="Agregar línea">
      <span class="tool-btn-content">
        <span class="tool-icon"><i class="bi bi-slash-lg"></i></span>
        <span class="tool-copy"><strong>Línea</strong><small>Trazo simple</small></span>
      </span>
    </button>

    <button type="button" class="tool-btn drawing-btn" id="addArrow"
            data-tooltip="Flecha" aria-label="Agregar flecha">
      <span class="tool-btn-content">
        <span class="tool-icon"><i class="bi bi-arrow-right"></i></span>
        <span class="tool-copy"><strong>Flecha</strong><small>Indicar dirección</small></span>
      </span>
    </button>

    <button type="button" class="tool-btn drawing-btn" id="addText"
            data-tooltip="Texto" aria-label="Agregar texto">
      <span class="tool-btn-content">
        <span class="tool-icon"><i class="bi bi-fonts"></i></span>
        <span class="tool-copy"><strong>Texto</strong><small>Agregar etiqueta</small></span>
      </span>
    </button>

    <button type="button" class="tool-btn drawing-btn tool-btn-danger" id="deleteShape"
            data-tooltip="Eliminar elemento" aria-label="Eliminar elemento seleccionado">
      <span class="tool-btn-content">
        <span class="tool-icon"><i class="bi bi-trash3"></i></span>
        <span class="tool-copy"><strong>Eliminar</strong><small>Elemento seleccionado</small></span>
      </span>
    </button>
  </div>

  <div class="tools-sidebar-footer">
    <i class="bi bi-info-circle"></i>
    <span>Selecciona una figura para agregarla al layout.</span>
  </div>
</aside>

<!-- Barra derecha: atributos del elemento seleccionado -->
<aside class="attributes-sidebar is-expanded" id="tools-panel" aria-label="Atributos del elemento">
  <div class="attributes-sidebar-header">
    <div class="attributes-heading">
      <span class="attributes-heading-icon"><i class="bi bi-sliders"></i></span>
      <div>
        <strong>Atributos</strong>
        <small>Elemento seleccionado</small>
      </div>
    </div>
    <button type="button" class="attributes-close-btn attributes-sidebar-toggle" id="btnToggleAttributesSidebar"
            aria-expanded="true" aria-controls="tools-panel" title="Comprimir atributos"
            aria-label="Expandir o comprimir barra de atributos">
      <i class="bi bi-chevron-right icon-expand"></i>
      <i class="bi bi-chevron-left icon-collapse"></i>
    </button>
  </div>

  <div class="attributes-sidebar-body">
    <div id="noSelectionMsg" class="attributes-empty-state">
      <i class="bi bi-cursor"></i>
      <strong>Sin elemento seleccionado</strong>
      <span>Selecciona una figura del layout para editar sus atributos.</span>
    </div>

    <div class="attribute-panel">
      <section class="common-attrs attribute-group">
        <div class="attribute-group-title">
          <i class="bi bi-arrows-move"></i>
          <span>Posición y rotación</span>
        </div>

        <label class="form-label fw-semibold">Posición X/Y</label>
        <div class="row g-2 mb-3">
          <div class="col-6">
            <input type="number" min="0" class="form-control" id="common-x" placeholder="X" step="1" aria-label="Posición X">
          </div>
          <div class="col-6">
            <input type="number" min="0" class="form-control" id="common-y" placeholder="Y" step="1" aria-label="Posición Y">
          </div>
        </div>

        <label for="common-rotate" class="form-label fw-semibold">Rotación (grados)</label>
        <input type="range" class="rotation-slider" id="common-rotate" min="0" max="360" value="0" step="1">
        <div class="d-flex justify-content-between small-note">
          <span>0°</span><span id="rotateValue">0°</span><span>360°</span>
        </div>
      </section>

      <section class="attr-section attribute-group" id="rect-attrs">
        <div class="attribute-group-title">
          <i class="bi bi-square"></i>
          <span>Rectángulo</span>
        </div>

        <div class="row g-2">
          <div class="col-6">
            <label for="rect-width" class="form-label">Ancho</label>
            <input type="number" min="0" class="form-control shape-attr" id="rect-width" data-attr="width" value="100">
          </div>
          <div class="col-6">
            <label for="rect-height" class="form-label">Alto</label>
            <input type="number" min="0" class="form-control shape-attr" id="rect-height" data-attr="height" value="70">
          </div>
          <div class="col-6">
            <label for="rect-rx" class="form-label">Radio X</label>
            <input type="number" min="0" class="form-control shape-attr" id="rect-rx" data-attr="rx" value="10">
          </div>
          <div class="col-6">
            <label for="rect-ry" class="form-label">Radio Y</label>
            <input type="number" min="0" class="form-control shape-attr" id="rect-ry" data-attr="ry" value="10">
          </div>
          <div class="col-6">
            <label for="rect-fill" class="form-label">Relleno</label>
            <input type="color" class="form-control-color shape-attr" id="rect-fill" data-attr="fill" value="#ffaa00">
          </div>
          <div class="col-6">
            <label for="rect-stroke" class="form-label">Borde</label>
            <input type="color" class="form-control-color shape-attr" id="rect-stroke" data-attr="stroke" value="#000000">
          </div>
          <div class="col-12">
            <label for="rect-stroke-width" class="form-label">Grosor del borde</label>
            <input type="number" min="0" class="form-control shape-attr" id="rect-stroke-width" data-attr="stroke-width" value="2" step="0.5">
          </div>
        </div>
      </section>

      <section class="attr-section attribute-group" id="circle-attrs">
        <div class="attribute-group-title">
          <i class="bi bi-circle"></i>
          <span>Círculo</span>
        </div>

        <label for="circle-r" class="form-label">Radio</label>
        <input type="number" min="0" class="form-control mb-3 shape-attr" id="circle-r" data-attr="r" value="35">
        <div class="row g-2">
          <div class="col-6">
            <label for="circle-fill" class="form-label">Relleno</label>
            <input type="color" class="form-control-color shape-attr" id="circle-fill" data-attr="fill" value="#44cc88">
          </div>
          <div class="col-6">
            <label for="circle-stroke" class="form-label">Borde</label>
            <input type="color" class="form-control-color shape-attr" id="circle-stroke" data-attr="stroke" value="#000000">
          </div>
          <div class="col-12">
            <label for="circle-stroke-width" class="form-label">Grosor del borde</label>
            <input type="number" min="0" class="form-control shape-attr" id="circle-stroke-width" data-attr="stroke-width" value="2" step="0.5">
          </div>
        </div>
      </section>

      <section class="attr-section attribute-group" id="line-attrs">
        <div class="attribute-group-title">
          <i class="bi bi-slash-lg"></i>
          <span>Línea o flecha</span>
        </div>

        <div class="row g-2">
          <div class="col-6">
            <label for="line-x1" class="form-label">X1</label>
            <input type="number" min="0" class="form-control shape-attr" id="line-x1" data-attr="x1">
          </div>
          <div class="col-6">
            <label for="line-y1" class="form-label">Y1</label>
            <input type="number" min="0" class="form-control shape-attr" id="line-y1" data-attr="y1">
          </div>
          <div class="col-6">
            <label for="line-x2" class="form-label">X2</label>
            <input type="number" min="0" class="form-control shape-attr" id="line-x2" data-attr="x2">
          </div>
          <div class="col-6">
            <label for="line-y2" class="form-label">Y2</label>
            <input type="number" min="0" class="form-control shape-attr" id="line-y2" data-attr="y2">
          </div>
          <div class="col-6">
            <label for="line-stroke" class="form-label">Color</label>
            <input type="color" class="form-control-color shape-attr" id="line-stroke" data-attr="stroke" value="#333333">
          </div>
          <div class="col-6">
            <label for="line-stroke-width" class="form-label">Grosor</label>
            <input type="number" min="0" class="form-control shape-attr" id="line-stroke-width" data-attr="stroke-width" value="3" step="0.5">
          </div>
        </div>
        <div class="badge-info mt-3 text-center" id="arrow-indicator" style="display: none;">
          <i class="bi bi-arrow-right"></i> Flecha activa
        </div>
      </section>

      <section class="attr-section attribute-group" id="text-attrs">
        <div class="attribute-group-title">
          <i class="bi bi-fonts"></i>
          <span>Texto</span>
        </div>

        <label for="text-content" class="form-label">Contenido</label>
        <input type="text" class="form-control mb-3 shape-attr" id="text-content" data-attr="content" value="Texto">

        <label for="text-font-family" class="form-label">Fuente</label>
        <select class="form-select mb-3 shape-attr" id="text-font-family" data-attr="font-family">
          <option value="Arial">Arial</option>
          <option value="Verdana">Verdana</option>
          <option value="Courier New">Courier New</option>
          <option value="Georgia">Georgia</option>
        </select>

        <label for="text-font-size" class="form-label">Tamaño</label>
        <input type="number" min="0" class="form-control mb-3 shape-attr" id="text-font-size" data-attr="font-size" value="20">

        <div class="row g-2">
          <div class="col-6">
            <label for="text-fill" class="form-label">Color</label>
            <input type="color" class="form-control-color shape-attr" id="text-fill" data-attr="fill" value="#000000">
          </div>
          <div class="col-6">
            <label for="text-stroke" class="form-label">Borde</label>
            <input type="color" class="form-control-color shape-attr" id="text-stroke" data-attr="stroke" value="#cccccc">
          </div>
          <div class="col-12">
            <label for="text-stroke-width" class="form-label">Grosor del borde</label>
            <input type="number" min="0" class="form-control shape-attr" id="text-stroke-width" data-attr="stroke-width" value="0.5" step="0.5">
          </div>
        </div>
      </section>
    </div>

    <div class="d-none" aria-hidden="true">
      <div class="element-list" id="elementList"></div>
    </div>
  </div>
</aside>