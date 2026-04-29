    <!-- PANEL DERECHO -->
        <div class="tools-panel" id="tools-panel">
            <ul class="nav nav-tabs mb-2" id="panelTabs">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-objects">Figuars</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-attrs">Atributos</button></li>
                <!-- <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-elements">Elementos</button></li> -->
            </ul>
            <div class="tab-content">
                <!--Lista de objetos --> <!-- NUEVOS BOTONES DE DIBUJO -->
                <div class="tab-pane fade show active" id="tab-objects">
                    <button class="drawing-btn" id="addRectFill" title="Rectángulo relleno"><i class="bi bi-square-fill"></i></button> Rectangulo relleno <br>
                    <button class="drawing-btn" id="addRectOutline" title="Rectángulo contorno"><i class="bi bi-square"></i></button> Rectangulo sin relleno <br>
                    <button class="drawing-btn" id="addCircleFill" title="Círculo relleno"> <i class="bi bi-circle-fill"></i></button> Circulo relleno <br>
                    <button class="drawing-btn" id="addCircleOutline" title="Círculo contorno"><i class="bi bi-circle"></i></button> Circulo sin relleno <br>
                    <button class="drawing-btn" id="addLine" title="Línea"><i class="bi bi-slash-lg"></i></button> Linea <br>
                    <button class="drawing-btn" id="addArrow" title="Flecha"><i class="bi bi-arrow-right"></i></button> Flecha <br>
                    <button class="drawing-btn" id="addText" title="Texto"><i class="bi bi-fonts"></i></button> Texto <br>
                    <button class="drawing-btn" id="deleteShape" title="Eliminar"><i class="bi bi-trash3"></i></button> Eliminar 
                </div>

                <!-- Panel de atributos (copiado del primer código) -->
                <div class="tab-pane fade" id="tab-attrs">
                <div class="attribute-panel">
                    <div id="noSelectionMsg" class="text-muted small text-center py-3">Ningún elemento seleccionado</div>
                    <!-- Sección común (posición/rotación) -->
                    <div class="common-attrs">
                    <label class="form-label fw-semibold">Posición X/Y</label>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><input type="number" min="0" class="form-control" id="common-x" placeholder="X" step="1"></div>
                        <div class="col-6"><input type="number" min="0" class="form-control" id="common-y" placeholder="Y" step="1"></div>
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
                    <input type="number" min="0" class="form-control mb-2 shape-attr" id="rect-width" data-attr="width" value="100">
                    <label class="form-label">Alto</label>
                    <input type="number" min="0" class="form-control mb-2 shape-attr" id="rect-height" data-attr="height" value="70">
                    <label class="form-label">Radio borde (rx, ry)</label>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><input type="number" min="0" class="form-control shape-attr" id="rect-rx" data-attr="rx" value="10"></div>
                        <div class="col-6"><input type="number" min="0" class="form-control shape-attr" id="rect-ry" data-attr="ry" value="10"></div>
                    </div>
                    <label class="form-label">Relleno</label>
                    <input type="color" class="form-control-color mb-2 shape-attr" id="rect-fill" data-attr="fill" value="#ffaa00">
                    <label class="form-label">Borde</label>
                    <input type="color" class="form-control-color mb-2 shape-attr" id="rect-stroke" data-attr="stroke" value="#000000">
                    <label class="form-label">Grosor borde</label>
                    <input type="number" min="0" class="form-control shape-attr" id="rect-stroke-width" data-attr="stroke-width" value="2" step="0.5">
                    </div>

                    <!-- Círculo -->
                    <div class="attr-section" id="circle-attrs">
                    <label class="form-label">Radio</label>
                    <input type="number" min="0" class="form-control mb-2 shape-attr" id="circle-r" data-attr="r" value="35">
                    <label class="form-label">Relleno</label>
                    <input type="color" class="form-control-color mb-2 shape-attr" id="circle-fill" data-attr="fill" value="#44cc88">
                    <label class="form-label">Borde</label>
                    <input type="color" class="form-control-color mb-2 shape-attr" id="circle-stroke" data-attr="stroke" value="#000000">
                    <label class="form-label">Grosor borde</label>
                    <input type="number" min="0" class="form-control shape-attr" id="circle-stroke-width" data-attr="stroke-width" value="2" step="0.5">
                    </div>

                    <!-- Línea / Flecha -->
                    <div class="attr-section" id="line-attrs">
                    <label class="form-label">X1</label>
                    <input type="number" min="0" class="form-control mb-2 shape-attr" id="line-x1" data-attr="x1">
                    <label class="form-label">Y1</label>
                    <input type="number" min="0" class="form-control mb-2 shape-attr" id="line-y1" data-attr="y1">
                    <label class="form-label">X2</label>
                    <input type="number" min="0" class="form-control mb-2 shape-attr" id="line-x2" data-attr="x2">
                    <label class="form-label">Y2</label>
                    <input type="number" min="0" class="form-control mb-2 shape-attr" id="line-y2" data-attr="y2">
                    <label class="form-label">Color</label>
                    <input type="color" class="form-control-color mb-2 shape-attr" id="line-stroke" data-attr="stroke" value="#333333">
                    <label class="form-label">Grosor</label>
                    <input type="number" min="0" class="form-control shape-attr" id="line-stroke-width" data-attr="stroke-width" value="3" step="0.5">
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
                    <input type="number" min="0" class="form-control mb-2 shape-attr" id="text-font-size" data-attr="font-size" value="20">
                    <label class="form-label">Color</label>
                    <input type="color" class="form-control-color mb-2 shape-attr" id="text-fill" data-attr="fill" value="#000000">
                    <label class="form-label">Borde</label>
                    <input type="color" class="form-control-color mb-2 shape-attr" id="text-stroke" data-attr="stroke" value="#cccccc">
                    <label class="form-label">Grosor borde</label>
                    <input type="number" min="0" class="form-control shape-attr" id="text-stroke-width" data-attr="stroke-width" value="0.5" step="0.5">
                    </div>
                </div>
                </div>

                <!-- Lista de elementos SVG -->
                <div class="tab-pane fade" id="tab-elements">
                <div class="element-list" id="elementList"></div>
                </div>
            </div>
        </div>
    <!-- FIN PANEL DERECHO -->
    
    <!--BOTONES FLOTANTES -->
        <button class="close-sidebar-btn" id="btncloseSidebar">
            <i class="bi bi-arrows-fullscreen" id="iconFullscreen"></i>
        </button>

        <!-- ICONO FLOTANTE (aparece cuando menú oculto) -->
        <button class="floating-menu-btn d-none" id="btnfloatingMenu">
            <i class="bi-fullscreen-exit"></i>
        </button>
    <!--FIN BOTONES -->