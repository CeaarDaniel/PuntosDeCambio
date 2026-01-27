  let btnGuardarEstacion = document.getElementById('btnGuardarEstacion');
  let btnAsignarOperador = document.getElementById('btnAsignarOperador');
  let btnGuardarDisponible = document.getElementById('btnGuardarDisponible');
  let btnGuardarEdicionLinea = document.getElementById('btnGuardarEdicionLinea');


 
  let tablaPersonalNoAsignado = document.getElementById('tablaPersonalNoAsignado');

  //Inputs del modal asignar operador
  let nominaModalAsignar = document.getElementById('nominaModalAsignar')

  document.getElementById('turnoasignar').value = $('#turnoLayout option:selected').text();
  document.getElementById('turnoAsignarPersonalDisponible').value =  $('#turnoLayout').val();


  // var turnoLinea = $('#turnoasignar').val()
  //$("#miSelect").val(turnoLinea);

  
  //Botones de menu del modal
    //Modal de registro de PC
      let btnInfoRPC = document.getElementById('btnInfoRPC');
      let btnRegistroPc = document.getElementById('btnRegistroPc');
      let btnLiberarPC = document.getElementById('btnLiberarPC');

      //Modal de personal personal sin asignar
      let btnTablaPNA = document.getElementById('btnTablaPNA');
      let btnRegistroPNA = document.getElementById('btnRegistroPNA');
  //Fin botones menu modal

  //Inputs del modal personal disponible
  

    //Formulario de registro de personal no asignado
    var nominaNoAsignado= document.getElementById('nominaNoAsignado');

  //Fin inputs personal disponible 

  let stationName = document.getElementById('nombreEstacion')
  let letstationForm = document.getElementById('stationForm');
  let stationDescription = document.getElementById('stationdescripcion')
  let requiredCertification = document.getElementById('requiereCertificacion');
  let certificacionF = document.getElementById('certificacion')
  let codigoLinea = document.getElementById('codigoLinea')

  let assignmentForm = document.getElementById('assignmentForm');


   // Datos para las estaciones
      var stationsData;

  /*
    const stationsData = [
      { id: 1, name: 'Corte de cable', operator: 'Juan Pérez', status: 'occupied', certification: 'Proceso X - Liberado', x: 50, y: 50, colorClass: 'station-color-1' },
      { id: 2, name: 'Removedor de tela', operator: 'María González', status: 'occupied', certification: 'Proceso Y - En curso', x: 300, y: 50, colorClass: 'station-color-2' },
      { id: 3, name: 'Re-Corte', operator: 'Carlos Ruiz', status: 'occupied', certification: 'Proceso Z - Liberado', x: 550, y: 50, colorClass: 'station-color-3' },
      { id: 4, name: 'Desforre de Circuito', operator: 'Ana López', status: 'pending', certification: 'Proceso X - Pendiente', x: 50, y: 300, colorClass: 'station-color-4' },
      { id: 5, name: 'Crimpado SA No. 1', operator: '', status: 'vacant', certification: 'No asignado', x: 300, y: 300, colorClass: 'station-color-5' },
      { id: 6, name: 'Crimpado SA No. 2', operator: 'Luis Fernández', status: 'occupied', certification: 'Proceso Y - Liberado', x: 550, y: 300, colorClass: 'station-color-6' },
      { id: 7, name: 'Crimpado SA No. 3', operator: 'Roberto Sánchez', status: 'occupied', certification: 'Proceso W - Liberado', x: 50, y: 550, colorClass: 'station-color-7' },
      { id: 8, name: 'Marcado Laser ', operator: '', status: 'vacant', certification: 'No asignado', x: 300, y: 550, colorClass: 'station-color-8' }
    ]; 
  */

  //Eventlistener
  btnGuardarEstacion.addEventListener('click', agregarEstacion);
  btnAsignarOperador.addEventListener('click', asignarEstaciones);
  btnGuardarDisponible.addEventListener('click', registrarPNA);
  

  btnInfoRPC.addEventListener('click', function(){
      changeContent('ventanasModalPC','contInfoEstacion');
  })

  btnRegistroPc.addEventListener('click', function(){
     changeContent('ventanasModalPC','contregistroCambioForm');
  })

  btnLiberarPC.addEventListener('click', function(){
     changeContent('ventanasModalPC', 'contLiberarPC')
  })

  btnTablaPNA.addEventListener('click', function(){
    changeContent('ventanadModalPersonalNA', 'contTablaDisponibles' )
  })

  btnRegistroPNA.addEventListener('click', function(){
    changeContent('ventanadModalPersonalNA', 'contRegistroPersonalDisponible')
  });
   


   document.addEventListener('DOMContentLoaded', function() {
      let currentStep = 1;
      const totalSteps = 4;
      
      // Elementos de navegación
      const prevBtn = document.getElementById('prevStep');
      const nextBtn = document.getElementById('nextStep');
      const confirmBtn = document.getElementById('confirmChange');
      const stepIndicator = document.getElementById('stepIndicator');
      
      // Actualizar navegación
      function updateNavigation() {
        stepIndicator.textContent = `Paso ${currentStep} de ${totalSteps}`;
        
        // Actualizar indicadores de progreso
        document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
          if (index + 1 === currentStep) {
            indicator.classList.add('active');
          } else {
            indicator.classList.remove('active');
          }
        });
        
        // Mostrar/ocultar pestañas
        document.querySelectorAll('.tab-pane').forEach((pane, index) => {
          if (index + 1 === currentStep) {
            pane.classList.add('show', 'active');
          } else {
            pane.classList.remove('show', 'active');
          }
        });
        
        // Actualizar botones
        prevBtn.disabled = currentStep === 1;
        
        if (currentStep === totalSteps) {
          nextBtn.classList.add('d-none');
          confirmBtn.classList.remove('d-none');
        } else {
          nextBtn.classList.remove('d-none');
          confirmBtn.classList.add('d-none');
        }
      }
      
      // Event listeners para navegación
      prevBtn.addEventListener('click', function() {
        if (currentStep > 1) {
          currentStep--;
          updateNavigation();
        }
      });
      
      nextBtn.addEventListener('click', function() {
        if (currentStep < totalSteps) {
          currentStep++;
          updateNavigation();
        }
      });
      
      // Inicializar navegación
      updateNavigation();
      
      // Lógica para actualizar resumen en el paso 4
      nextBtn.addEventListener('click', function() {
        if (currentStep === 4) {
          // Aquí iría la lógica para recopilar datos y actualizar el resumen
          document.getElementById('resumenControlNo').textContent = document.getElementById('controlNo').value || 'CC-2025-001';
          document.getElementById('resumenLinea').textContent = document.getElementById('linea').options[document.getElementById('linea').selectedIndex].text || 'Línea A';
          // ... más actualizaciones de resumen
        }
      });
      
      // Confirmar cambio
      confirmBtn.addEventListener('click', function() {
        // Aquí iría la lógica para enviar el formulario
        alert('Cambio de operador registrado exitosamente');
        const modal = bootstrap.Modal.getInstance(document.getElementById('changeControlModal'));
        modal.hide();
      });
    });

  // Actualizar estadísticas cuando cambia el estado de asistencia
    document.querySelectorAll('.attendance-status').forEach(select => {
      select.addEventListener('change', function() {
        updateAttendanceStats();
      });
    });


    //Obtener las estaciones creadas y invocar la funcion para mostrarlas en el layput
    function getEstaciones(){
        const formData = new FormData;
        formData.append('opcion', 5)
        formData.append('codigoLinea', codigoLinea.value)

        fetch("../api/operacionesLinea.php", {
                    method: "POST",
                    body: formData,
                })
                .then((response) => response.text())
                .then((data) => {
                    console.log(data);
                     stationsData = JSON.parse(data);

                    // Crear estaciones
                    stationsData.forEach(station => {
                      createStation(station, workspaceGrid);
                    });

                    listarEstaciones();
                })
                .catch((error) => {
                  console.log(error);
            });
    }

    // Estado del workspace
    const workspaceState = {
      zoomLevel: 1,
      gridSize: 20,
      isGridSnapEnabled: false
    };

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
        
        // Si fue un click, mostrar modal
        if (this.isClick) {
          const stationData = stationsData.find(s => s.id == this.dragData.stationId);
          if (stationData) {
            this.showStationModal(stationData);
          }
        } else {
          // Si fue un drag, aplicar posición final
          this.updateDragPosition(e.clientX, e.clientY, true);
          
          if (workspaceState.isGridSnapEnabled) {
            this.snapToGrid(this.activeDrag);
          }
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
        
        if (isFinal || performance.now() - this.workspaceCache.timestamp > 100) {
          this.updateWorkspaceCache();
        }
        
        const deltaX = clientX - this.dragData.startX;
        const deltaY = clientY - this.dragData.startY;
        
        let newX = this.dragData.startLeft + deltaX;
        let newY = this.dragData.startTop + deltaY;
        
        // Aplicar límites
        newX = Math.max(0,newX); //newX = Math.max(0, Math.min(newX, this.workspaceCache.maxX));
        newY = Math.max(0,newY); //newY = Math.max(0, Math.min(newY, this.workspaceCache.maxY));


        
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
      
      //Actualizar y mostrar el modal de la estacion
      showStationModal(stationData) {
       
        document.getElementById('imgInfochangeControlModal').src=  (stationData.nomina) ? `../img/personal/${stationData.nomina}.jpg` : `../img/personal/na.jpg`;
        document.getElementById('nombreEstacionModalPC').textContent = (stationData.name).toUpperCase();
        document.getElementById('idEstacionModalPC').value = stationData.id;
        document.getElementById('idTrabajadorAsignado').value = stationData.nomina || '';


        //Setear valores del formulario de registro de PC

          getNoControl().then(resultado => { document.getElementById('no_controlCambio').value = resultado;});
          document.getElementById('fechaHora_inicio').value = (new Date()).toLocaleString('sv-SE').slice(0, 16);
          document.getElementById('id_estacion').value = stationData.id;
          document.getElementById('nombre_estacion').value = stationData.name;

  
        //Modal creado registro de punto de cambio
            const stationModal = new bootstrap.Modal(document.getElementById('changeControlModal'));
            stationModal.show();
      }
    }

    // Instancia del sistema de drag
    const dragSystem = new OptimizedDragSystem();

    // Inicializar el workspace
    document.addEventListener('DOMContentLoaded', function() {
        const workspaceGrid = document.getElementById('workspaceGrid');
        getEstaciones();

      // Inicializar tooltips
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
      
      // Configurar event listeners para los controles
      setupControls();
      
      // Inicializar sistema de drag
      dragSystem.init();
      
      // Actualizar indicador de zoom
      updateZoomIndicator();

      mostrarTablaPNA();
    });

    // Configurar controles de zoom y cuadrícula
    function setupControls() {
      document.getElementById('zoomInBtn').addEventListener('click', zoomIn);
      document.getElementById('zoomOutBtn').addEventListener('click', zoomOut);
      document.getElementById('snapToGridBtn').addEventListener('click', toggleGridSnap);
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
      if (workspaceState.zoomLevel > 0.5) {
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

    function toggleGridSnap() {
      workspaceState.isGridSnapEnabled = !workspaceState.isGridSnapEnabled;
      const btn = document.getElementById('snapToGridBtn');
      
      if (workspaceState.isGridSnapEnabled) {
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-secondary');
        btn.innerHTML = '<i class="bi bi-arrows-move"></i> Cuadrícula activa';
        snapAllStationsToGrid();
      } else {
        btn.classList.remove('btn-secondary');
        btn.classList.add('btn-outline-secondary');
        btn.innerHTML = '<i class="bi bi-arrows-move"></i> Ajustar a cuadrícula';
      }
    }

    function snapAllStationsToGrid() {
      const stations = document.querySelectorAll('.station');
      stations.forEach(station => {
        dragSystem.snapToGrid(station);
      });
    }

    function saveLayout() {

      const stations = document.querySelectorAll('.station');
      const layoutData = [];
      
      stations.forEach(station => {
        const stationId = station.getAttribute('data-station-id');
        const left = parseInt(station.style.left);
        const top = parseInt(station.style.top);
        
        layoutData.push({
          id: stationId,
          x: left,
          y: top
        });
      });

      var formDataPosicion = new FormData;
      formDataPosicion.append('opcion', 6);
      formDataPosicion.append('layoutPosition', JSON.stringify(layoutData));

      console.log("datos", layoutData)

          fetch("../api/operacionesLinea.php", {
                method: "POST",
                body: formDataPosicion,
            })
            .then((response) => response.text())
            .then((data) => {
            
                console.log(data);
                data= JSON.parse(data)
            
               if(data.estatus=='ok'){
                    alert('Layout guardado correctamente');
                }

                else alert(data.mensaje)

               // actualizarEstacion('5');
            })
            .catch((error) => {
               console.log(error);
        });
    }

    function createStation(stationData, parent) {
      const station = document.createElement('div');
      station.className = `station ${stationData.colorClass}`;
      station.style.left = `${stationData.x}px`;
      station.style.top = `${stationData.y}px`;
      station.setAttribute('data-station-id', stationData.id);
      
      let operatorIcon = 'bi-person';
      if (stationData.status === 'occupied') {
        operatorIcon = `<img src="../img/personal/${stationData.nomina}.jpg" alt="Foto del operador" 
                                      style="width: 100px; height: 100px; border-radius: 10px; 
                                             object-fit: cover; border: 3px solid #e9ecef; 
                                             margin-bottom: 10px;">`;
      } else if (stationData.status === 'pending') {
        operatorIcon = '<i class="bi-person-x"></i>'; //  operatorIcon = '<i>bi-person-x<i/>';
      }
      
      station.innerHTML = `
        <div class="station-header">${stationData.name}</div>
        <div class="station-content">
          <div class="station-operator"> ${operatorIcon} </div>
          <div class="station-name">${stationData.operator || 'No asignado'}</div>
        </div>
        <div class="station-status status-${stationData.status}"></div>
      `;
      
      parent.appendChild(station);
    }

    //Funcion para agrregar una nueva estacion
    function agregarEstacion(){
      var formDataEstacion = new FormData;
      let nombreEstacion = (stationName.value.trim() === "") ? null : stationName.value;
      let descripcion = (stationDescription.value.trim() === "")  ? null : stationDescription.value;
      let requiereC = (requiredCertification.value.trim() === "")  ? null : requiredCertification.value;
      let certificacion = (certificacionF.value.trim() === "")  ? null : certificacionF.value;
      let linea = (codigoLinea.value.trim() === "")  ? null : codigoLinea.value;

      formDataEstacion.append("opcion", "2");
      formDataEstacion.append("nombreEstacion", nombreEstacion);
      formDataEstacion.append("descripcion", descripcion);
      formDataEstacion.append("requiereC", requiereC);
      formDataEstacion.append("certificacion", certificacion);
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
              
                  console.log(data);
                  data= JSON.parse(data)
              
                  if(data.status=='ok'){
                      alert(data.mensaje);
                      console.log(data);
                      
                      document.getElementById('stationForm').reset();

                      //Ocurtar modal
                      let modalAgregarEstacion = bootstrap.Modal.getInstance(document.getElementById('modalAgregarEstacion'));
                      modalAgregarEstacion.hide();

                      stationsData.push(data.dataEstacion);
                      createStation(data.dataEstacion,workspaceGrid)
                      listarEstaciones();
                  }

                  else alert(data.mensaje)
              })
              .catch((error) => {
                console.log(error);
          });
      }

    }

    //Asignar un operador a una linea
    function asignarEstaciones(){
      var formDataAsig = new FormData;
      let nomina = document.getElementById('nominaModalAsignar').value;
      let nombre = document.getElementById('nombreModalAsignar').value;
      let estacion = document.getElementById('stationSelect').value;
      let fecha  = document.getElementById('assignmentDate').value;
      let turno = document.getElementById('turnoLayout').value;
      let comentarios = document.getElementById('comentarios').value;

      //console.log(fecha);
      
      formDataAsig.append("opcion", "3");
      formDataAsig.append("nomina", nomina);
      formDataAsig.append("nombre", nombre);
      formDataAsig.append("estacion", estacion);
      formDataAsig.append("fecha", fecha);
      formDataAsig.append("turno", turno);
      formDataAsig.append("comentarios", comentarios);

      if(assignmentForm.reportValidity()){
          fetch("../api/operacionesLinea.php", {
                  method: "POST",
                  body: formDataAsig,
              })
              .then((response) => response.text())
              .then((data) => {
              
                
                  data= JSON.parse(data)
              
                  if(data.estatus=='ok'){
                      alert(data.mensaje);
                      assignmentForm.reset();

                      actualizarEstacion(estacion, 
                                          { 'nomina': nomina, 
                                            'operator': nombre, 
                                            'colorClass': 'station-color-1',
                                            'status' : 'occupied'
                                          }
                                        )
                  }

                else alert(data.mensaje)

                  console.log(data)
              })
              .catch((error) => {
                console.log(error);
          });
      }
    }

    //Funciones para la navegacion entre las pantallas
      function changeContent(contenedorPadre, contenidoVisible){
          const contenedor = document.getElementById(contenedorPadre);
          const visible = document.getElementById(contenidoVisible);

          // 1. Ocultar todos los hijos
            Array.from(contenedor.children).forEach(el => {
              if (el.classList.contains('show')) {
                      el.classList.remove('show');
                      el.classList.add('d-none');
                }
            });

        // Forzar reflow para que la animación se dispare
        visible.offsetHeight;
            setTimeout(() => {
                visible.classList.add('show');
            }, 100); 
        visible.classList.remove('d-none');
      }


    //Funcion para actualizar el layout y los datos de la estacion en tiempo real
    function actualizarEstacion(stationId, newData){
        //Agregar condiciones de cada parametro de la estacion para validar que el cambio extien o tiene algun valor
        //"Si" se modifica "si no"  no hay que modificarlo
    
        const station = document.querySelector(`[data-station-id="${stationId}"]`);

        if (station) {
              // Contenido
              //station.querySelector('.station-header').textContent = 'Estación en uso';
              station.querySelector('.station-name').textContent = newData.operator;

              // Clases
              station.classList.remove('station-color-7');
              station.classList.add(newData.colorClass);

              // Estado
              const status = station.querySelector('.station-status');
              status.classList.remove('status-pending');
              status.classList.add(newData.status);

              const operator = station.querySelector('.station-operator');
              operator.innerHTML=`<img src="../img/personal/${newData.nomina}.jpg" alt="Foto del operador" 
                                        style="width: 70px; height: 70px; border-radius: 10px; 
                                              object-fit: cover; border: 3px solid #e9ecef; 
                                              margin-bottom: 10px;">`;

              // Estilos (opcional)
              //station.style.border = '2px solid #4CAF50';
        } 

        else 
          console.warn(`No se encontró la estación ${stationId}`);

          //stationsData = stationsData.map(obj => obj.id=== '5' ? { ...obj, ...{operator: 'CARLOS PÉREZ', name: 'Estación en uso', colorClass: 'station-color-1'} } : obj);
          //stationsData = stationsData.map(obj => obj.id=== '5' ? { ...obj, ...newData } : obj);

        /* Actualizar el arreglo*/ // Buscamos solo el objeto necesario
        let estation = stationsData.find(obj => obj.id === stationId);

        if (estation) {
          estation.operator = newData.nombre;
          //estation.name = 'Estación en uso';
          estation.colorClass = newData.colorClass;
          estation.nomina = newData.nomina;
          estation.status = newData.status;
        
        }
    } 

    //Mostrar listado de estaciones
    function listarEstaciones(){
      const select = document.getElementById('stationSelect');

      select.innerHTML='';

        stationsData.forEach(station => {
          const option = document.createElement('option');
          option.value = station.id;     // value = id
          option.textContent = station.name; // texto = name
          select.appendChild(option);
        });
    }

    //Obtener nombre del numero de nomina
    nominaModalAsignar.addEventListener('change', function (){
      
        let nombreModalAsignar = document.getElementById('nombreModalAsignar');

        if(nominaModalAsignar && nominaModalAsignar !='') {
            let formDataConsultarNombre = new FormData;
            formDataConsultarNombre.append('nomina',nominaModalAsignar.value)
            formDataConsultarNombre.append('opcion', 7)

                fetch("../api/operacionesLinea.php", {
                        method: "POST",
                        body: formDataConsultarNombre,
                    })
                    .then((response) => response.text())
                    .then((data) => {
                        data= JSON.parse(data)
                    
                        if(data.estatus=='ok')
                            nombreModalAsignar.value= data.nombre;
                      
                      else{
                        nombreModalAsignar.value= ''; 
                        console.log(data); 
                      }
                    })
                    .catch((error) => {
                      console.log(error);
                });
          }
    })

    nominaNoAsignado.addEventListener('change', function(){
      let nombreNoAsignado = document.getElementById('nombreNoAsignado');

        if(nominaNoAsignado && nominaNoAsignado !='') {
            let formDataConsultarNombre = new FormData;
            formDataConsultarNombre.append('nomina',nominaNoAsignado.value)
            formDataConsultarNombre.append('opcion', 7)

                fetch("../api/operacionesLinea.php", {
                        method: "POST",
                        body: formDataConsultarNombre,
                    })
                    .then((response) => response.text())
                    .then((data) => {
                          console.log(data);
                          data= JSON.parse(data)
                    
                        if(data.estatus=='ok')
                            nombreNoAsignado.value= data.nombre;
                      
                      else{
                        nombreNoAsignado.value= ''; 
                        console.log(data); 
                      }
                    })
                    .catch((error) => {
                      console.log(error);
                });
          }
    })

    function registrarPNA(){
      let formDataNoAsignado = new FormData
      let fmPersonalNoAsignado = document.getElementById('fmPersonalNoAsignado');

      if(fmPersonalNoAsignado.reportValidity()){

          if( document.getElementById('nombreNoAsignado').value == '' || document.getElementById('nombreNoAsignado').value == null){
                alert('No se encontro registro del empleado ingresado') 
                return;
          }

            formDataNoAsignado.append('nomina', document.getElementById("nominaNoAsignado").value)
            formDataNoAsignado.append('turno', document.getElementById("turnoAsignarPersonalDisponible").value)
            formDataNoAsignado.append('fechaR',document.getElementById("assignmentDatePNA").value)
            formDataNoAsignado.append('comentarios', document.getElementById("comentariosNoAsignado").value)
            formDataNoAsignado.append('codigoLinea',  codigoLinea.value)
            formDataNoAsignado.append('opcion', 8)
        
              fetch("../api/operacionesLinea.php", {
                    method: "POST",
                    body: formDataNoAsignado,
                })
                .then((response) => response.text())
                .then((data) => {
                        console.log(data);
                        data= JSON.parse(data)

                      if(data.estatus=='ok'){
                          alert(data.mensaje)
                          fmPersonalNoAsignado.reset();
                          mostrarTablaPNA();
                      }
                  
                        else{
                            alert(data.mensaje)
                      }
                })
                .catch((error) => {
                  console.log(error);
            });
      }
    }

    function mostrarTablaPNA(){
          let formDataNoAsignadoL = new FormData
          formDataNoAsignadoL.append('opcion', 9)
          
            fetch("../api/operacionesLinea.php", {
                    method: "POST",
                    body: formDataNoAsignadoL,
                })
                .then((response) => response.text())
                .then((data) => {
                    
                    data = JSON.parse(data)
                    let body = document.getElementById('tablaBodyPersonalNoAsignado');

                    let filasHTML = '';
                      data.forEach(emp => {
                        filasHTML += `
                          <tr>
                            <td class="px-4 align-middle">
                              <span class="fw-semibold">${emp.nomina}</span>
                            </td>
                            <td class="px-4 align-middle">
                              <div class="d-flex align-items-center">
                                <div>
                                  <div class="fw-medium">NOMBRE DEL EMPLEADO</div>
                                </div>
                              </div>
                            </td>
                            <td class="px-4 align-middle text-center">
                              <button 
                                class="btn btn-sm btn-outline-primary d-inline-flex align-items-center"
                                onclick="asignarEstacion('${emp.nomina}')"
                              >
                                <i class="bi bi-gear me-1"></i>
                                Asignar a Estación
                              </button>
                            </td>
                          </tr>
                        `;
                      });

                      // Insertamos todas las filas de una vez dentro del tbody
                      body.innerHTML = filasHTML;
                })
                .catch((error) => {
                  console.log(error);
            });
    }

  //Abrir modal de asignar personal a una estacion
    function asignarEstacion(nomina) {
          //console.log(nomina)
      let modalPersonalDisponible = document.getElementById('modalPersonalDisponible') 
      let modalAsignarOperador = document.getElementById('modalAsignarOperador');

      let modalActual = bootstrap.Modal.getInstance(modalPersonalDisponible);
      (modalActual) ? modalActual.hide() : '';

      newModal = new bootstrap.Modal(modalAsignarOperador);
      newModal.show();   
}

    //Remover trabajador de la estacion
    btnRemoverTrabajadorPC.addEventListener('click', function(){
       let formDataReniver = new FormData;
 
      let estacionId = document.getElementById('idEstacionModalPC').value;
      let nominaTrabajador = document.getElementById('idTrabajadorAsignado').value; 

        formDataReniver.append("opcion", "10");
        formDataReniver.append("idEstacion", estacionId);
        formDataReniver.append("nomina", nominaTrabajador);

      if(nominaTrabajador == '' || nominaTrabajador == null){
          alert('No hay trabajador asignado a esta estación');
          return;
      }

      fetch("../api/operacionesLinea.php", {
                    method: "POST",
                    body: formDataReniver,
                })
                .then((response) => response.text())
                .then((data) => {
                
                    data = JSON.parse(data)
                      if(data.estatus=='ok'){
                        
                          alert(data.mensaje);  
                          /* actualizarEstacion(estacionId, 
                                            { 'nomina': null, 
                                              'operator': 'No asignado', 
                                              'colorClass': 'station-color-7',
                                              'status' : 'available'
                                            }
                                          ) */
                        }

                        else 
                          alert(data.mensaje);
                })
                .catch((error) => {
                  console.log(error);
            });
    })

    //Actualizar informacion de la linea
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
        
                    data= JSON.parse(data)
                    if(data.estatus=='ok'){ 
                        alert(data.mensaje);
                        location.reload();
                    }
                    else  alert(data.mensaje);
                })
                .catch((error) => {
                  console.log(error);
            });
      }
    })



    //Funcion para obtener el numero de control del ultimo registro del punto de cambio
function getNoControl() {
  const formDataNoControles = new FormData();
  formDataNoControles.append('opcion', 12);

  return fetch("../api/operacionesLinea.php", {
    method: "POST",
    body: formDataNoControles,
  })
    .then(response => response.json())
    .then(data => {
      if (data.estatus === 'ok') {
        return data.noControl;
      }
      return '';
    })
    .catch(error => {
      console.error(error);
      return '';
    });
}