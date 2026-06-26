  let btnGuardarEstacion = document.getElementById('btnGuardarEstacion');
  let btnGuardarEdicionLinea = document.getElementById('btnGuardarEdicionLinea');

  //Botones flotantes
  let btncloseSidebar = document.getElementById('btncloseSidebar')
  let btnfloatingMenu = document.getElementById('btnfloatingMenu')

  //AREA DE DIBUJO DE LAS ESTACIONES
  let workspaceGrid;

  //INPUTS PARA AGREGAR UNA NUEVA ESTACION 
        let letstationForm = document.getElementById('stationForm');
        let stationName = document.getElementById('nombreEstacion');
        let stationDescription = document.getElementById('stationdescripcion');
        let requiredCertification = document.getElementById('requiereCertificacion');
        let codigoLinea = document.getElementById('codigoLinea');
        let assignmentForm = document.getElementById('assignmentForm');
        //let certificacionF = document.getElementById('certificacion');
  //FIN

  // Datos para las estaciones
  var stationsData;

  // Estado del workspace
  const workspaceState = {zoomLevel: 1, gridSize: 20 /*, isGridSnapEnabled: false */};

    // Sistema de drag & drop optimizado con soporte para modales
    class OptimizedDragSystem {
      constructor() {
        this.activeDrag = null;
        this.dragData = null;
        this.animationFrame = null;
        this.lastX = 0;
        this.lastY = 0;
        this.isClick = true; // Para diferenciar entre click y drag
        this.clickThreshold = 5; // Pixeles de movimiento para considerar drag
        this.workspaceCache = {
          rect: null,
          scrollLeft: 0,
          scrollTop: 0,
          timestamp: 0
        };
        this.updateThreshold = 1000 / 60;
        this.lastUpdate = 0;
      }
      
      init() {
        // Usar event delegation para mejor rendimiento
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
        this.isClick = true; // Inicialmente asumimos que es un click
        
        // Calcular una sola vez al inicio
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
        
        // Si el movimiento supera el umbral, es un drag, no un click
        const deltaX = Math.abs(e.clientX - this.dragData.startX);
        const deltaY = Math.abs(e.clientY - this.dragData.startY);
        
        //Activar el evento/funcion de drag para mover el contenedor
        if (deltaX > this.clickThreshold || deltaY > this.clickThreshold) {
          this.isClick = false;
          this.activeDrag.classList.add('dragging');
        }
        
        if (!this.isClick) {
          // Throttle para alto rendimiento
          const now = performance.now();
          if (now - this.lastUpdate < this.updateThreshold) return;
          this.lastUpdate = now;
          
          if (this.animationFrame) {
            cancelAnimationFrame(this.animationFrame);
          }
          
          this.animationFrame = requestAnimationFrame(() => {
            this.updateDragPosition(e.clientX, e.clientY);
          });
        }
      }
      
      handleMouseUp(e) {
        if (!this.activeDrag) return;
        
        if (this.animationFrame) {
          cancelAnimationFrame(this.animationFrame);
          this.animationFrame = null;
        }
        
          // Si fue un drag, aplicar posición final
          this.updateDragPosition(e.clientX, e.clientY, true);
          
          /*
          if (workspaceState.isGridSnapEnabled) {
            this.snapToGrid(this.activeDrag);
          } */
        
        
        this.activeDrag.classList.remove('dragging');
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
        
        this.activeDrag = null;
        this.dragData = null;
        this.isClick = true;
      }
      
      updateDragPosition(clientX, clientY, isFinal = false) {
        if (!this.activeDrag || !this.dragData) return;
        
        // Obtén el zoom actual (asegúrate de tener acceso a workspaceState.zoomLevel)
        const zoom = workspaceState.zoomLevel; // o this.workspaceState?.zoomLevel

        if (isFinal || performance.now() - this.workspaceCache.timestamp > 100) {
          this.updateWorkspaceCache();
        }
        
        const deltaX = clientX - this.dragData.startX;
        const deltaY = clientY - this.dragData.startY;
        
        // Convierte deltas a coordenadas internas del workspace (dividiendo por zoom)
        const deltaInternalX = deltaX / zoom;
        const deltaInternalY = deltaY / zoom;

        let newX = this.dragData.startLeft +  deltaInternalX //deltaX;
        let newY = this.dragData.startTop + deltaInternalY //deltaY;
        
        // Aplicar límites
        newX = Math.max(0,newX); //newX = Math.max(0, Math.min(newX, this.workspaceCache.maxX));
        newY = Math.max(0,newY); //newY = Math.max(0, Math.min(newY, this.workspaceCache.maxY));
        
        if (newX !== this.lastX || newY !== this.lastY || isFinal) {
          if (isFinal) {
            this.activeDrag.style.left = `${newX}px`;
            this.activeDrag.style.top = `${newY}px`;
            this.activeDrag.style.transform = 'none';
          } else {
              this.activeDrag.style.transform = `translate(${deltaInternalX}px, ${deltaInternalY}px)`;
              //this.activeDrag.style.transform = `translate(${deltaX}px, ${deltaY}px)`;
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
      
      /*
      snapToGrid(element) {
        const gridSize = workspaceState.gridSize;
        let left = parseInt(element.style.left);
        let top = parseInt(element.style.top);
        
        left = Math.round(left / gridSize) * gridSize;
        top = Math.round(top / gridSize) * gridSize;
        
        element.style.left = `${left}px`;
        element.style.top = `${top}px`;
      } 
        */
    }

    // Instancia del sistema de drag
    const dragSystem = new OptimizedDragSystem();

    function saveLayout(showMessage) {
        const stations = document.querySelectorAll('.station');
        const layoutData = [];
       
        //Obtener las posiciones de cada estacion
        stations.forEach(station => {
            const stationId = station.getAttribute('data-station-id');
            const left = parseInt(station.style.left);
            const top = parseInt(station.style.top);
            layoutData.push({id: stationId, x: left, y: top});

            //Funcion para actualizar la posicion acutal de la estacion
            let estationU = stationsData.find(obj => obj.id === stationId);
            if (estationU) {
                estationU.x = left;
                estationU.y = top;
            } 
        });

        //Guardar los elementos SVG
        let formas = getAllSVGElements();
        var formDataPosicion = new FormData;
        formDataPosicion.append('opcion', 6);
        formDataPosicion.append('layoutPosition', JSON.stringify(layoutData));
        formDataPosicion.append('stationsData', JSON.stringify(stationsData));
        formDataPosicion.append('codigoLinea', codigoLinea.value);
        formDataPosicion.append('layoutF', JSON.stringify(formas)); //FORMAS ELEMENTOS SVG

          // console.log("datos", layoutData)
          fetch("../api/operacionesLinea.php", {
                method: "POST",
                body: formDataPosicion,
            })
            .then((response) => response.text())
            .then((data) => {
              //console.log(data);
              if(showMessage == true) return;

               data= JSON.parse(data)
               if(data.estatus=='ok'){
                    alert('Layout guardado correctamente');
                  }

                else alert(data.mensaje)
            })
            .catch((error) => {
               console.log(error);
          });
    }

    // Configurar controles de zoom y cuadrícula
    function setupControls() {
      document.getElementById('zoomInBtn').addEventListener('click', zoomIn);
      document.getElementById('zoomOutBtn').addEventListener('click', zoomOut);
      document.getElementById('saveLayoutBtn').addEventListener('click', saveLayout);
      document.getElementById('workspaceGrid').addEventListener('wheel', function(e) {
        if (e.ctrlKey) {
          e.preventDefault();
          if (e.deltaY < 0) zoomIn();
          else zoomOut();
        }
      });
    }

    function zoomIn() {
      if (workspaceState.zoomLevel < 2) {
        workspaceState.zoomLevel += 0.1;
        applyZoom();
      }
    }

    function zoomOut() {
      if (workspaceState.zoomLevel > 0.20) {
        workspaceState.zoomLevel -= 0.1;
        applyZoom();
      }
    }

    function applyZoom() {
      const workspaceGrid = document.getElementById('workspaceGrid');
      workspaceGrid.style.transform = `scale(${workspaceState.zoomLevel})`;
      updateZoomIndicator();
    }

    function updateZoomIndicator() {
      const zoomPercent = Math.round(workspaceState.zoomLevel * 100);
      document.getElementById('zoomIndicator').textContent = `${zoomPercent}%`;
    }

    function createStation(stationData, parent) {
      const station = document.createElement('div');
      station.className = `station ${stationData.colorClass}`;
      station.style.left = `${stationData.x}px`;
      station.style.top = `${stationData.y}px`;
      station.setAttribute('data-station-id', stationData.id);
      station.innerHTML = `
        <div class="station-header py-2"></div>
        <div class="station-content">
          <div class="station-name text-break"> ${stationData.name}</div>
        </div>
        <div class="station-status status-${stationData.status}"></div>`;

        //Estilo del div de la estacion para cuando esta es certificada
        if (stationData.isCertificate == 1) {
            station.querySelector('.station-header').style.background = "#ffc107";
            station.querySelector('.station-header').style.color = "rgb(0, 0, 0, 1)";
            station.querySelector('.station-header').style.textShadow = "0 0px 0px rgba(0,0,0)";
          }

      parent.appendChild(station);
    }

    //Obtener las estaciones creadas e invocar la funcion para mostrarlas en el layout
    function getEstaciones(){
        const formData = new FormData;
        formData.append('opcion', 5)
        formData.append('codigoLinea', codigoLinea.value)
        return fetch("../api/operacionesDiseno.php", {
                    method: "POST",
                    body: formData,
                })
                .then((response) => response.text())
                .then((data) => {
                    //console.log(data);
                     stationsData = JSON.parse(data);

                      // Crear estaciones
                      stationsData.forEach(station => {
                        createStation(station, workspaceGrid);
                      });
                })
                .catch((error) => {
                  console.log(error);
        });
    }

    //Funcion para agrregar una nueva estacion
    function agregarEstacion(){
      var formDataEstacion = new FormData;
      let nombreEstacion = (stationName.value.trim() === "") ? null : stationName.value;
      let descripcion = (stationDescription.value.trim() === "")  ? null : stationDescription.value;
      let requiereC = (requiredCertification.value.trim() === "")  ? null : requiredCertification.value;
      //let certificacion = (certificacionF.value.trim() === "")  ? null : certificacionF.value;
      let linea = (codigoLinea.value.trim() === "")  ? null : codigoLinea.value;
      formDataEstacion.append("opcion", "2");
      formDataEstacion.append("nombreEstacion", nombreEstacion);
      formDataEstacion.append("descripcion", descripcion);
      formDataEstacion.append("requiereC", requiereC);
      //formDataEstacion.append("certificacion", certificacion);
      formDataEstacion.append("linea", linea);
      formDataEstacion.append("x", 0);
      formDataEstacion.append("y", 0);

        //console.log(certrificacionF);
        if (letstationForm.reportValidity()) {
            fetch("../api/operacionesLinea.php", {
                    method: "POST",
                    body: formDataEstacion,
                })
                .then((response) => response.text())
                .then((data) => {
                    data= JSON.parse(data)
                    if(data.status=='ok'){
                        alert(data.mensaje);
                        document.getElementById('stationForm').reset();
                        
                        //Ocurtar modal
                        let modalAgregarEstacion = bootstrap.Modal.getInstance(document.getElementById('modalAgregarEstacion'));
                        modalAgregarEstacion.hide();
                        stationsData.push(data.dataEstacion);
                        createStation(data.dataEstacion,workspaceGrid)
                    }

                    else alert(data.mensaje)
                })
                .catch((error) => {
                  console.log(error);
            });
        }
    }

    // ========== CÓDIGO DEL DIAGRAMADOR SVG ==========
    let svg = document.getElementById('workspace-svg');
    let shapesGroup = $('#shapes-group');
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
      $('#workspaceGrid .selected').removeClass('selected');
      selectedElement = null;
      //$('.attr-section').hide();
      //$('#noSelectionMsg').show();
      //$('.common-attrs').hide();
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
      updateContainerAndSVGBounds();
      return elt;
    }

    //const p = randomPos();
    const p = {x:200, y:200};

    // Botones de dibujo
    $('#addRectFill').click(() => {
      createElement('rect', { x: p.x, y: p.y, width: 100, height: 70, rx: 10, ry: 10, fill: '#2661ec', stroke: '#000', 'stroke-width': 2 });
    });
    $('#addRectOutline').click(() => {
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
      createElement('circle', { cx: p.x, cy: p.y, r: 35, fill: '#44cc88', stroke: '#000', 'stroke-width': 2 });
    });
    $('#addCircleOutline').click(() => {
      createElement('circle', { cx: p.x, cy: p.y, r: 40, fill: 'none', stroke: '#dc2626', 'stroke-width': 3 });
    });
    $('#addLine').click(() => {
      createElement('line', { x1: p.x, y1: p.y, x2: p.x+120, y2: p.y+40, stroke: '#333', 'stroke-width': 3 });
    });
    $('#addArrow').click(() => {
      createElement('line', { x1: p.x, y1: p.y, x2: p.x+120, y2: p.y+40, stroke: '#000', 'stroke-width': 3 }, true);
    });
    $('#addText').click(() => {
      const elt = createElement('text', { x: p.x, y: p.y, 'font-family': 'Arial', 'font-size': 20, fill: '#000', stroke: '#ccc', 'stroke-width': 0.5 });
      elt.textContent = 'Texto';
    });
    $('#deleteShape').click(() => {
      if (selectedElement) { selectedElement.remove(); clearSelection(); refreshElementList(); }
    });

    // Drag de formas SVG
    $('#workspaceGrid').on('mousedown', '#shapes-group rect, #shapes-group circle, #shapes-group line, #shapes-group text', function(e) {
      e.preventDefault();
      e.stopPropagation(); // Evita interferir con el drag de estaciones
      const elt = this;
      selectShape(elt);
      const start = getSVGCoords(e);
      draggingShape = { element: elt, start: start, original: getOriginalPosition(elt) };
    });

    /*Funcion para obtener los elementos del SV*/
    function getAllSVGElements() {
      // Seleccionar todos los elementos dentro del SVG (excluyendo el propio <svg>)
      clearSelection();
      const allElements = document.querySelectorAll('#shapes-group *');
      console.log("longitus svg"+allElements.length)
      const result = [];

      allElements.forEach(el => {
        // Obtener todos los nombres de atributos
        const attributes = {};
        const attributeNames = el.getAttributeNames();

        attributeNames.forEach(attrName => {
          if (attrName === 'data-list-id') return;
          attributes[attrName] = el.getAttribute(attrName);
        });

        if (el.tagName === 'text') {
          attributes['data-text-content'] = el.textContent; // atributo personalizado
        }


        result.push({
          tag: el.tagName.toLowerCase(), // nombre de la etiqueta (ej: 'rect', 'circle', 'g')
          attributes: attributes
        });
      });

      console.log(result)
      return result;
    }

    //Funcion para cargar los datos del SVG desde el servidor
    function loadShapesFromJSON() {
      const shapesGroup = document.getElementById('shapes-group');
      
      // Limpiar el grupo actual (para reemplazar con los datos del servidor)
      shapesGroup.innerHTML = '';
      let maxCounter = -1;

        if (!shapesGroup) {
          console.error('No se encontró el grupo #shapes-group');
          return;
        }

      var formDataGetF = new FormData;
      formDataGetF.append('opcion', 24);
      formDataGetF.append('codigoLinea', codigoLinea.value);
      fetch("../api/operacionesLinea.php", {
          method: "POST",
          body: formDataGetF,
      })
      .then((response) => response.text())
      .then((data) => {
            shapesArray = JSON.parse(data);

            shapesArray.formas.forEach(shape => {
              const { tag, attributes } = shape;
              const element = document.createElementNS('http://www.w3.org/2000/svg', tag);

              // Asignar todos los atributos excepto el texto especial
              for (const [key, value] of Object.entries(attributes)) {
                if (key === 'data-text-content' || key === 'data-list-id') continue; // este lo tratamos aparte
                element.setAttribute(key, value);
              }

              // Si es texto, restaurar el contenido
              if (tag === 'text') {
                element.textContent = attributes['data-text-content'] || '';
              }

              shapesGroup.appendChild(element);

              // Actualizar el contador máximo basado en data-list-id

              /*
                const listId = attributes['data-list-id'];
                if (listId && typeof listId === 'string') {
                  const match = listId.match(/elem-(\d+)/);
                  if (match) {
                    const num = parseInt(match[1], 10);
                    if (num > maxCounter) maxCounter = num;
                  }
                } 
              */
              
            });

            // Actualizar el contador global para que los nuevos elementos sigan la secuencia
            if (maxCounter >= 0) {
              window.elementCounter = maxCounter + 1;
            } else {
              window.elementCounter = shapesArray.formas.length; // fallback
            }

            // Refrescar la lista de elementos y actualizar bounds (funciones existentes)
            if (typeof refreshElementList === 'function') refreshElementList();
            if (typeof updateContainerAndSVGBounds === 'function') updateContainerAndSVGBounds();
            if (typeof clearSelection === 'function') clearSelection();

            console.log(`Cargadas ${shapesArray.formas.length} formas. Próximo elementCounter = ${window.elementCounter}`);
      })
      .catch((error) => { 
        console.log(error); 
      });
    }

    $(window).on('mousemove', function(e) {
      if (!draggingShape) return;
      e.preventDefault();
      const current = getSVGCoords(e);
      let dx = current.x - draggingShape.start.x;
      let dy = current.y - draggingShape.start.y;
      const elt = draggingShape.element;
      const original = draggingShape.original;

      //  Obtener nueva posición según tipo
      let newX = 0;
      let newY = 0;

      if (elt.tagName === 'rect') {
        newX = original.x + dx;
        newY = original.y + dy;

        //  limitar a 0
        dx = Math.max(-original.x, dx);
        dy = Math.max(-original.y, dy);

      } else if (elt.tagName === 'circle') {
        newX = original.cx + dx;
        newY = original.cy + dy;
        dx = Math.max(-original.cx, dx);
        dy = Math.max(-original.cy, dy);

      } else if (elt.tagName === 'text') {
        newX = original.x + dx;
        newY = original.y + dy;
        dx = Math.max(-original.x, dx);
        dy = Math.max(-original.y, dy);
      } else if (elt.tagName === 'line') {
        dx = Math.max(-original.x1, dx);
        dy = Math.max(-original.y1, dy);
      }

      // aplicar posición ya limitada
      setPosition(elt, original, dx, dy);
      const angle = getRotationAngle(elt);
      applyRotation(elt, angle);
      updateCommonFields(elt);
      updateContainerAndSVGBounds();
    });

    $(window).on('mouseup', function() { draggingShape = null; });

    $(document).on('click', function(e) {
      const clickedOnShape = $(e.target).closest('[data-list-id]').length > 0;
      const clickedOnTools = $(e.target).closest('#tools-panel').length > 0;
        if (!clickedOnShape && !clickedOnTools) {
          clearSelection();
        }
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

    function updateContainerAndSVGBounds() {
      const container = document.getElementById('dynamicContainer');
      const svg = document.getElementById('workspace-svg');
      const shapesGroup = document.getElementById('shapes-group');
      let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;

      // 1. Procesar estaciones (divs)
      document.querySelectorAll('.station').forEach(station => {
        const left = parseFloat(station.style.left) || 0;
        const top = parseFloat(station.style.top) || 0;
        const width = station.offsetWidth;
        const height = station.offsetHeight;
        minX = Math.min(minX, left);
        minY = Math.min(minY, top);
        maxX = Math.max(maxX, left + width);
        maxY = Math.max(maxY, top + height);
      });

      // 2. Procesar formas SVG (dentro de shapesGroup)
      const shapes = shapesGroup.children;

      for (let shape of shapes) {
          let bbox;
          try {
            bbox = shape.getBBox();
          } catch (e) {
            continue; // por si algún elemento no es renderizable
          }

          const left = bbox.x;
          const top = bbox.y;
          const width = bbox.width;
          const height = bbox.height;
          minX = Math.min(minX, left);
          minY = Math.min(minY, top);
          maxX = Math.max(maxX, left + width);
          maxY = Math.max(maxY, top + height);
        }

      // Añadir un margen alrededor
      const padding = 30;
      minX = Math.max(0, minX - padding);
      minY = Math.max(0, minY - padding);
      maxX = maxX + padding;
      maxY = maxY + padding;
      const newWidth = maxX - minX;
      const newHeight = maxY - minY;

      // Establecer el tamaño del contenedor dinámico (en píxeles)
      container.style.width = `${maxX}px`;
      container.style.height = `${maxY}px`;

      // Actualizar el viewBox del SVG para que abarque toda el área
      svg.setAttribute('viewBox', `${minX} ${minY} ${newWidth} ${newHeight}`);
    }

    // Inicializar lista de elementos
    refreshElementList();

    // Inicializar el workspace
    document.addEventListener('DOMContentLoaded', function() {
      workspaceGrid = document.getElementById('workspaceGrid');
      getEstaciones();

      // Inicializar tooltips
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function (tooltipTriggerEl) {return new bootstrap.Tooltip(tooltipTriggerEl);});
      
      // Configurar event listeners para los controles
      setupControls();
      
      // Inicializar sistema de drag
      dragSystem.init();
      
      // Actualizar indicador de zoom
      updateZoomIndicator();
      //DECLARACION DE EVENTOS
        btnGuardarEstacion.addEventListener('click', agregarEstacion);

        //Mostrar y ocultar los botones flotantes
          btncloseSidebar.addEventListener('click', function () {
            $('#btncloseSidebar').addClass('d-none')
            $('#btnfloatingMenu').removeClass('d-none')

            $('#tools-sidebar').addClass('fade-out')
            $('#tools-panel').addClass('fade-out')
            //$('#layout-header').addClass('fade-out')

              setTimeout(() => {
                  $('#tools-sidebar').addClass('d-none')
                  $('#tools-panel').addClass('d-none')
                  //$('#layout-header').addClass('d-none')

                  $('#tools-sidebar').removeClass('fade-out')
                  $('#tools-panel').removeClass('fade-out')
                 // $('#layout-header').removeClass('fade-out')
              }, 300); 
          })

          btnfloatingMenu.addEventListener('click', function () {
            $('#btncloseSidebar').removeClass('d-none')
            $('#btnfloatingMenu').addClass('d-none')

            $('#tools-sidebar').removeClass('d-none')
            $('#tools-panel').removeClass('d-none')
            //$('#layout-header').removeClass('d-none')
          })

      // Cargar las formas
      setTimeout(() => {
              loadShapesFromJSON();
      }, 500);
    });