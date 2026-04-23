  let btnGuardarEstacion = document.getElementById('btnGuardarEstacion');
  let btnAsignarOperador = document.getElementById('btnAsignarOperador');
  let btnGuardarDisponible = document.getElementById('btnGuardarDisponible');
  let btnGuardarEdicionLinea = document.getElementById('btnGuardarEdicionLinea');
  let confirmChange = document.getElementById('confirmChange');
  let btnAsignarOperadorPC = document.getElementById('btnAsignarOperadorPC') //Boton de asignacion desde el modal de gestion pc
  let btnConfirmClose = document.getElementById('btnConfirmClose');
  let btnEvaluacion = document.getElementById('btnEvaluacion');
  let btnAsistencia = document.getElementById('btnAsistencia') //Boton para registrar una asistencia individual

  let btncloseSidebar = document.getElementById('btncloseSidebar')
  let btnfloatingMenu = document.getElementById('btnfloatingMenu')

  let checkPadre = document.getElementById("checkPadre");
  var seleccionadosGlobal = [];
  var datosAsistenciaCheck; 
  let btnCambioTurno = document.getElementById('btnCambioTurno');
  let btnMenuRegistroAs = document.getElementById('btnMenuRegistroAs');

  let btnRegistrarAsistencia = document.getElementById('btnRegistrarAsistencia');
  let btnHistorialLayout = document.getElementById('btnHistorialLayout');
  let tablaPersonalNoAsignado = document.getElementById('tablaPersonalNoAsignado');
  let workspaceGrid;

  //Inputs del modal asignar operador
  let nominaModalAsignar = document.getElementById('nominaModalAsignar');
  let nominaPC = document.getElementById('nominaPC');

  //Inputs del modal consultar historial
  let fechaHistorial = document.getElementById('fechaHistorial');
  let turnoHistorial = document.getElementById('turnoHistorial');

  //document.getElementById('turnoasignar').value = $('#turnoLayout option:selected').text();
  document.getElementById('turnoAsignarPersonalDisponible').value =  $('#turnoLayout').val();

  // var turnoLinea = $('#turnoasignar').val()
  //$("#miSelect").val(turnoLinea);
  
  //Botones de menu del modal
      //Modal de registro de PC
        let btnInfoRPC = document.getElementById('btnInfoRPC');
        let btnRegistroPc = document.getElementById('btnRegistroPc');
        let btnMenuAsignarControlModal = document.getElementById('btnMenuAsignarControlModal');
        //let btnLiberarPC = document.getElementById('btnLiberarPC');

        //Modal de personal personal sin asignar
        let btnTablaPNA = document.getElementById('btnTablaPNA');
        let btnRegistroPNA = document.getElementById('btnRegistroPNA');
  //Fin botones menu modal

  //Inputs del modal personal disponible
      //Formulario de registro de personal no asignado
      var nominaNoAsignado= document.getElementById('nominaNoAsignado');
  //Fin inputs personal disponible 

  let stationName = document.getElementById('nombreEstacion');
  let letstationForm = document.getElementById('stationForm');
  let stationDescription = document.getElementById('stationdescripcion');
  let requiredCertification = document.getElementById('requiereCertificacion');
  let certificacionF = document.getElementById('certificacion');
  let codigoLinea = document.getElementById('codigoLinea');
  let assignmentForm = document.getElementById('assignmentForm');

  // Datos para las estaciones
  var stationsData;

  //Contenedor para la seccion donde se muestra el PC
  let registroPC = document.getElementById('registroPC')

  //Opciones para cuando esta activo el punto de cambio
  let opcionActivPC = document.getElementById('opcionActivPC')
  
  //Inputs para el registro de evaluacion del PC
  let numeroDia = document.getElementById('numeroDiaEvaluacion')
  let numeroEvaluacion = document.getElementById('numeroEvaluacion')

  // Estado del workspace
  const workspaceState = {zoomLevel: 1, gridSize: 20, isGridSnapEnabled: false};

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
      
      snapToGrid(element) {
        const gridSize = workspaceState.gridSize;
        let left = parseInt(element.style.left);
        let top = parseInt(element.style.top);
        
        left = Math.round(left / gridSize) * gridSize;
        top = Math.round(top / gridSize) * gridSize;
        
        element.style.left = `${left}px`;
        element.style.top = `${top}px`;
      }
      
      //Actualizar y mostrar el modal de la estacion al abrirlo
      showStationModal(stationData) {
        console.log('Datos de la estacion: ', stationData)
        document.getElementById('imgInfochangeControlModal').src= (stationData.nomina) ? `../img/personal/${stationData.nomina}.jpg` : `../img/personal/na.jpg`;
        document.getElementById('nombreEstacionModalPC').textContent = (stationData.name).toUpperCase();
        document.getElementById('idEstacionModalPC').value = stationData.id;
        document.getElementById('idTrabajadorAsignado').value = stationData.nomina || '';

        //Setear valores del formulario de registro de PC
          getNoControl().then(resultado => { document.getElementById('no_controlCambio').value = resultado;});
          document.getElementById('fechaHora_inicio').value = (new Date()).toLocaleString('sv-SE').slice(0, 16);
          document.getElementById('fechaEvaluacion').value = (new Date()).toLocaleString('sv-SE').slice(0, 16);
          document.getElementById('id_estacion').value = stationData.id;
          document.getElementById('nombre_estacion').value = stationData.name;
          document.getElementById('turnoPuntoCambio').value =  $('#turnoLayout').val();

          //Setear valores del formulario asignar operador
           document.getElementById('assignmentDatePC').value = (new Date()).toLocaleString('sv-SE').slice(0, 16);
           document.getElementById('turnoasignarPC').value =  $('#turnoLayout').val();

          
        //Setear input de id del punto de cambio en el formulario de cierre de PC
          document.getElementById('idPC').value = stationData.idPC || '';
          document.getElementById('fechaCierre').value = (new Date()).toLocaleString('sv-SE').slice(0, 16);

          if(stationData.idPC) {
              registroPC.classList.add("d-none");
              opcionActivPC.classList.remove("d-none");
              getEvaluacion(stationData.idPC);
          }

          else {
                  registroPC.classList.remove("d-none");
                  opcionActivPC.classList.add("d-none");
          }

          getOperator(stationData.nomina, stationData.id, stationData.idPC || null);


        //Modal creado registro de punto de cambio
            const stationModal = new bootstrap.Modal(document.getElementById('changeControlModal'));
            stationModal.show();
      }
    }

    // Instancia del sistema de drag
    const dragSystem = new OptimizedDragSystem();

    function saveLayout(showMessage) {
        const stations = document.querySelectorAll('.station');
        const layoutData = [];

        //Codigo alternativo mas rapidpo 
        /*
          const stationsMap = new Map(stationsData.map(s => [String(s.id), s]));
            stations.forEach(station => {
              const stationId = station.dataset.stationId; // más limpio que getAttribute
              const left = parseInt(station.style.left, 10);
              const top = parseInt(station.style.top, 10);

              layoutData.push({ id: stationId, x: left, y: top });

              const estationU = stationsMap.get(stationId);
              if (estationU) {
                estationU.x = left;
                estationU.y = top;
              }
            });
        */
       
        //Obtener las posiciones de cada estacion
        stations.forEach(station => {
          const stationId = station.getAttribute('data-station-id');
          const left = parseInt(station.style.left);
          const top = parseInt(station.style.top);
          layoutData.push({id: stationId, x: left, y: top});

          let estationU = stationsData.find(obj => obj.id === stationId);

          if (estationU) {
              estationU.x = left;
              estationU.y = top;
          } 

        });


        let formas = getAllSVGElements();
        var formDataPosicion = new FormData;
        formDataPosicion.append('opcion', 6);
        formDataPosicion.append('layoutPosition', JSON.stringify(layoutData));
        formDataPosicion.append('stationsData', JSON.stringify(stationsData));
        formDataPosicion.append('codigoLinea', codigoLinea.value)
        formDataPosicion.append('turno', $('#turnoLayout').val())
        formDataPosicion.append('layoutF', JSON.stringify(formas)); //FORMAS ELEMENTOS SVG

        //console.log('Estaciones:'+ stations)
        //console.log('stations encontradas:', document.querySelectorAll('.station').length);

          // console.log("datos", layoutData)
          fetch("../api/operacionesLinea.php", {
                method: "POST",
                body: formDataPosicion,
            })
            .then((response) => response.text())
            .then((data) => {
              console.log(data);

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

    function createStation(stationData, parent) {
      const station = document.createElement('div');
      station.className = `station ${stationData.colorClass}`;
      station.style.left = `${stationData.x}px`;
      station.style.top = `${stationData.y}px`;
      station.setAttribute('data-station-id', stationData.id);
      
      let operatorIcon = 'bi-person';
      if (stationData.status === 'occupied' || stationData.status === 'absent') {
        operatorIcon = `<img src="../img/personal/${stationData.nomina}.jpg" alt="Foto del operador" 
                                      style="width: 100px; height: 100px; border-radius: 10px; 
                                             object-fit: cover; border: 3px solid #e9ecef; 
                                             margin-bottom: 10px;">`;
      } else if (stationData.status === 'pending') {
        operatorIcon = '<i class="bi-person-x"></i>'; //  operatorIcon = '<i>bi-person-x<i/>';
      }
      
      station.innerHTML = `
        <div class="station-header py-2"><!--
          ${stationData.name} -->
        </div>
        <div class="station-content">
        <!--
          <div class="station-operator"> ${operatorIcon} </div> 
          -->
          <div class="station-name"> ${stationData.name} 
            <!-- ${stationData.operator || 'No asignado'} -->
          </div>
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
        formData.append('turno', $('#turnoLayout').val())
        formData.append('codigoLinea', codigoLinea.value)
        return fetch("../api/operacionesLinea.php", {
                    method: "POST",
                    body: formData,
                })
                .then((response) => response.text())
                .then((data) => {
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

    //Funcion para obtener datos de una estacion en especifico. 
    function getEstacion(id){
     //Se usa cuando, tras finalizar un punto de cambio, es necesario recuperar la información real del usuario asignado a la estación, ya que el listado muestra datos sobrescritos por el usuario del PC
     let formDataEstacion = new FormData;
      formDataEstacion.append('opcion', 15);
      formDataEstacion.append('idEstacion', id);
      formDataEstacion.append('turno', $('#turnoLayout').val())
          fetch("../api/operacionesLinea.php", {
                  method: "POST",
                  body: formDataEstacion,
              })
              .then((response) => response.text())
              .then((data) => {   
                    data= JSON.parse(data)
                    if(data.estatus=='ok'){
                        actualizarEstacion(id, {'nomina': (data.estacion.nomina) ? data.estacion.nomina : null, 
                                                'operator': (data.estacion.operator) ? data.estacion.operator : 'No asignado',
                                                'colorClass': data.estacion.colorClass,
                                                'status' : data.estacion.status,
                                                'idPC' : data.estacion.idPC,
                                                'estatusPC': data.estacion.estatusPC,
                                                'isCertificate' : data.estacion.isCertificate
                                          })
                    }
                      else  alert(data.mensaje);
                }
              ).catch((error) => {
                  console.log(error);
            });
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
                    data= JSON.parse(data)
                    if(data.status=='ok'){
                        alert(data.mensaje);
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
      let turno = document.getElementById('turnoasignar').value;
      let comentarios = document.getElementById('comentarios').value;
      
      formDataAsig.append("opcion", "3");
      formDataAsig.append("nomina", nomina);
      formDataAsig.append("nombre", nombre);
      formDataAsig.append("estacion", estacion);
      formDataAsig.append("fecha", fecha);
      formDataAsig.append("turno", turno);
      formDataAsig.append("comentarios", comentarios);
      formDataAsig.append('codigoLinea', codigoLinea.value)

      if(!nombre) { 
        alert("No se encontro registro del empleado ingresado o se perdió la conexión con el servidor.")
        return;
      }

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
                      //assignmentForm.reset();

                      $('#nominaModalAsignar').val('');
                      $('#nombreModalAsignar').val('');
                      $('#stationSelect').val('');
                      $('#comentarios').val('');
                      $('#listaOperacionesOperador').html('<span class="form-help">Lista de operaciones asignadas del trabajador en la linea </span>');
                      getEstacion(estacion);
                  }

                else {
                  alert(data.mensaje)
                  console.log(data.error)
                }
              })
              .catch((error) => {
                console.log(error);
          });
      }
    }

    //Funcion para actualizar el layout y los datos de la estacion en tiempo real
    function actualizarEstacion(stationId, newData){
        //Agregar condiciones de cada parametro de la estacion para validar que el cambio extien o tiene algun valor
        //"Si" se modifica "si no"  no hay que modificarlo
    
        const station = document.querySelector(`[data-station-id="${stationId}"]`);

        if (station) {
              // Contenido
              //station.querySelector('.station-header').textContent = 'Estación en uso';

              /*
              (newData.operator) ? station.querySelector('.station-name').textContent = newData.operator 
                                 : station.querySelector('.station-name').textContent = 'No asignado'; 
                */

              // Clases
              if(newData.colorClass){
                  //station.classList.remove('station-color-7');

                     // Buscar todas las clases que comiencen con "status-"
                    const colorActual = Array.from(station.classList).filter(clase => clase.startsWith('station-color-'));

                    // Eliminarlas
                    colorActual.forEach(clase => station.classList.remove(clase));
                    station.classList.add(newData.colorClass);
              }            

              // Revisar esta
              if(newData.status && newData.status!= null && newData.status !== ''){
                    const status = station.querySelector('.station-status');

                    // Buscar todas las clases que comiencen con "status-"
                    const clasesParaEliminar = Array.from(status.classList).filter(clase => clase.startsWith('status-'));

                    // Eliminarlas
                    clasesParaEliminar.forEach(clase => status.classList.remove(clase));
                    status.classList.add(`status-${newData.status}`);
              }
       
              //Modificar la foto de la estacion
              /*
              const operator = station.querySelector('.station-operator');
                   (newData.nomina) ? operator.innerHTML=`<img src="../img/personal/${newData.nomina}.jpg" alt="Foto del operador" 
                                              style="width: 100px; height: 100px; border-radius: 10px; 
                                                    object-fit: cover; border: 3px solid #e9ecef; 
                                                    margin-bottom: 10px;">` 
                                                :
                                      operator.innerHTML='<i class="bi-person-x"></i>'; 
              */

                 if (newData.isCertificate == 1) {
                      station.querySelector('.station-header').style.background = "#ffc107";
                      station.querySelector('.station-header').style.color = "rgb(0, 0, 0, 1)";
                      station.querySelector('.station-header').style.textShadow = "0 0px 0px rgba(0,0,0)";
                    } 
                  
                 else{
                  station.querySelector('.station-header').style.background = "";
                  station.querySelector('.station-header').style.color = "";
                  station.querySelector('.station-header').style.textShadow = "";
                }
        } 

        else 
          console.warn(`No se encontró la estación ${stationId}`);

          //stationsData = stationsData.map(obj => obj.id=== '5' ? { ...obj, ...{operator: 'CARLOS PÉREZ', name: 'Estación en uso', colorClass: 'station-color-1'} } : obj);
          //stationsData = stationsData.map(obj => obj.id=== '5' ? { ...obj, ...newData } : obj);

        /* Actualizar el arreglo*/ // Buscamos solo el objeto necesario
        let estation = stationsData.find(obj => obj.id === stationId);

        if (estation) {
           (newData.operator) ? estation.operator = newData.operator : '';
          //estation.name =  (newData.name)'Estación en uso';
           (newData.colorClass) ? estation.colorClass = newData.colorClass : '';
           (newData.status) ? estation.status = newData.status : '';
            estation.nomina = (newData.nomina) ?? null;
            estation.idPC = (newData.idPC) ?? null;
            estation.estatusPC = (newData.estatusPC) ?? null;
            estation.isCertificate = newData.isCertificate
        } //console.log(stationsData);  
    } 

    //Mostrar listado de estaciones registradas para colocar en los select
    function listarEstaciones(){
      const select = document.getElementById('stationSelect');
      const estacionAsistencia = document.getElementById('estacionAsistencia');

      select.innerHTML='';
      estacionAsistencia.innerHTML='';  

        //Agregar opcion vacia por defecto
          let none = document.createElement('option');
          none.value = '';  
          none.textContent =  'Selecciona una estación...';
          select.appendChild(none)

          let noneA = document.createElement('option');
          noneA.value = '';  
          noneA.textContent =  'Selecciona una estación...';
          estacionAsistencia.appendChild(noneA)

        stationsData.forEach(station => {
          const option = document.createElement('option');
          option.value = station.id;   
          option.textContent = station.name; 
          select.appendChild(option);

          const optionA = document.createElement('option');
          optionA.value = station.id;   
          optionA.textContent = station.name; 
          estacionAsistencia.appendChild(optionA)
        });
    }

    //Registrar personal no asignado
    function registrarPNA(){
      let formDataNoAsignado = new FormData
      let fmPersonalNoAsignado = document.getElementById('fmPersonalNoAsignado');

      if(fmPersonalNoAsignado.reportValidity()){

          if( document.getElementById('nombreNoAsignado').value == '' || document.getElementById('nombreNoAsignado').value == null){
                alert('No se encontro registro del empleado ingresado o se perdió la conexión con el servidor.') 
                return;
          }

            formDataNoAsignado.append('nomina', document.getElementById("nominaNoAsignado").value)
            formDataNoAsignado.append('nombre', document.getElementById("nombreNoAsignado").value)
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
                          //fmPersonalNoAsignado.reset();

                          $('#nominaNoAsignado').val('')
                          $('#nombreNoAsignado').val('')
                          $('#comentariosNoAsignado').val('')

                          mostrarTablaPNA();
                          generarTablaAsistencia();
                      }
                  
                        else{
                            alert(data.mensaje);
                            console.log(data);
                        }
                })
                .catch((error) => {
                  console.log(error);
            });
      }
    }

    //Registrar personal disponible sin formulario
    function registrarDisponible(nomina, nombre, turno){
      let formDataNoAsignado = new FormData
      let fecha = (new Date()).toLocaleString('sv-SE').slice(0, 16)

      formDataNoAsignado.append('nomina', nomina)
      formDataNoAsignado.append('nombre', nombre)
      formDataNoAsignado.append('turno', turno)
      formDataNoAsignado.append('fechaR', fecha)
      formDataNoAsignado.append('codigoLinea',  codigoLinea.value)
      formDataNoAsignado.append('opcion', 8)
        
          fetch("../api/operacionesLinea.php", {
                method: "POST",
                body: formDataNoAsignado,
            })
            .then((response) => response.text())
            .then((data) => {
                  data= JSON.parse(data)

                  if(data.estatus=='ok'){
                      console.log('Se ha registrado al trabajador')
                      mostrarTablaPNA();
                  }
              
                    else{
                        console.log(data);
                    }
            })
            .catch((error) => {
              console.log(error);
        });
    }

    //Generar tabla con los datos de la tabla de personal no asignado
    function mostrarTablaPNA(){
          let formDataNoAsignadoL = new FormData 
          formDataNoAsignadoL.append('codigoLinea', codigoLinea.value)
          formDataNoAsignadoL.append('turno', $('#turnoLayout').val())
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
                                  <div class="fw-medium">${(emp.nombre) ?? ''}</div>
                                </div>
                              </div>
                            </td>
                            <td class="px-4 align-middle text-center">
                              <div class="d-flex justify-content-center gap-2">
                                <button 
                                  class="btn btn-sm btn-outline-primary d-inline-flex align-items-center"
                                  onclick="openAsignarEstacion('${emp.nomina}')"">
                                  <i class="bi bi-gear me-1"></i>Asignar a Estación
                                </button>
                                <button 
                                  class="btn btn-sm btn-outline-danger d-inline-flex align-items-center"
                                  onclick="confirmarEliminar('${emp.id_registro}')">
                                   <i class="bi bi-trash me-1"></i>Borrar registro
                                </button>
                              </div>
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

    //Funcion para generar la tabla de la lista de asistencia
    function generarTablaAsistencia(){
      let fromDataAsistencia = new FormData;
      fromDataAsistencia.append('opcion', 16);
      fromDataAsistencia.append('codigoLinea', codigoLinea.value);
      fromDataAsistencia.append('turno', $('#turnoLayout').val());
      document.getElementById('checkPadre').checked = false

        fetch("../api/operacionesLinea.php", {
                method: "POST",
                body: fromDataAsistencia,
            })
            .then((response) => response.text())
            .then((data) => {   
                      data= JSON.parse(data)
                      let formAsistencia = document.getElementById('formAsistencia')
                      let resumenAsistencia = document.getElementById('resumenAsistencia');

                      console.log(data)
                      datosAsistenciaCheck = data.personal.map(item => Number(item.nomina));

                      //Mostrar el formulario de registro para una asistencia individual si no ya se ha echo el registro de asistencia
                      if (data.personal.length > 0 && 'id_registro' in data.personal[0])
                            formAsistencia.classList.remove('d-none'); 
                        
                      //Ocultar el formulario de registro si no se ha echo el registro de asistencia
                      else
                          formAsistencia.classList.add('d-none');

                    // Ocultar la información de resumen de asistencia
                    if (!data.resumen || data.resumen.length <= 0) {
                        resumenAsistencia.classList.add('d-none');
                        
                    } else {
                        // Mostrar la información de resumen de asistencia
                        resumenAsistencia.classList.remove('d-none'); 
                         document.getElementById('countAsistencia').textContent = data.resumen.asistencias
                          document.getElementById('countPermisos').textContent = data.resumen.permisos
                          document.getElementById('countFaltas').textContent = data.resumen.faltas
                          document.getElementById('countVacaciones').textContent = data.resumen.vacaciones
                          document.getElementById('countIncapacidades').textContent = data.resumen.incapacidad
                          document.getElementById('countPAsistencia').textContent = `% ${parseFloat(data.resumen.porcentajeA).toFixed(2)}`;
                    }

                      $('#attendanceTable').DataTable().destroy();
                      $('#attendanceTable').DataTable({
                        //scrollY: '300px',
                        //scrollCollapse: true,
                        autoWidth: false,
                        responsive: false,
                        data: data.personal,
                        deferRender: false,
                        paging: true,
                        pageLength: 10,
                        searching: false,
                        info: false,
                        /*columnDefs: [{ width: "80px", targets: 0 },
                                     { width: "100px", targets: 1 },
                                     { width: "250px", targets: 2 },
                                     { width: "350px", targets: 3 },
                                     { width: "250px", targets: 4 },
                                     { width: "180px", targets: 5 }
                                    ],
                        */
                        columns: [
                            /*
                              {
                                data: null,
                                render: (data, type, row, meta) => {
                                  const station = (meta.row + 1).toString().padStart(2, '0');
                                  return `
                                    <div class="station-badge bg-primary text-white rounded text-center py-1">
                                      <strong>${station}</strong>
                                    </div>`;
                                }
                              },
                            */
                            { data: null, /*{ data: 'nombre' } so le dice a la tabla: “en esta columna muestra row.nombre”. */
                              render: row => `<div class="fw-bold" data-nombre="${row.nombre}" 
                                                                   data-nomina="${row.nomina}"
                                                                   data-id_estacion="${row.id_estacion}"
                                                                   data-nombre_estacion= "${row.nombre_estacion}">
                                                      ${row.nombre}</div>
                                              <small class="text-muted">ID: ${row.nomina}</small>`
                            },
                            {
                              data:null,
                              render: row =>`<div> ${ (row.nombre_estacion) ? (row.nombre_estacion).toUpperCase(): 'SIN ASIGNAR'}</div>`
                            },
                            {
                              data: null,
                              render: row => `<select name="estatusAsistencia" class="form-control form-control-custom attendance-status">
                                                <option value="1" ${(row.estatus && row.estatus=='1') ? 'selected' : ''}>✅ ASISTENCIA</option>
                                                <option value="2" ${(row.estatus && row.estatus=='2') ? 'selected' : ''}>❌ FALTA INJUSTIFICADA</option>
                                                <option value="3" ${(row.estatus && row.estatus=='3') ? 'selected' : ''}>🟢 PERMISO SIN GOCE DE SUELDO</option>
                                                <option value="4" ${(row.estatus && row.estatus=='4') ? 'selected' : ''}>🏖️ VACACIONES</option>
                                                <option value="5" ${(row.estatus && row.estatus=='5') ? 'selected' : ''}>🟡 PARO TÉCNICO</option>
                                                <option value="6" ${(row.estatus && row.estatus=='6') ? 'selected' : ''}>⚪ DESCANSO</option>
                                                <option value="7" ${(row.estatus && row.estatus=='7') ? 'selected' : ''}>🚫 SANCIÓN</option>
                                                <option value="8" ${(row.estatus && row.estatus=='8') ? 'selected' : ''}>⏱️ TIEMPO EXTRA</option>
                                                <option value="9" ${(row.estatus && row.estatus=='9') ? 'selected' : ''}>🏥 INCAPACIDAD</option>
                                              </select>`
                            },
                            { 
                              data: null,
                              render: row => `
                                <input type="text" name="observacionesAsistencia"
                                      class="form-control form-control-custom"
                                      placeholder="Observaciones..." 
                                      value="${ (row.comentarioAsistencia) ? row.comentarioAsistencia : ''}">`
                            },
                            {
                              data: null,
                              className: "text-center",
                              render: (data, type, row) => `
                                <div class="form-check d-flex justify-content-center">
                                  <input class="form-check-input"
                                         data-nomina="${row.nomina}"
                                        type="checkbox" id="cambio_${row.nomina}">
                                        
                                  <label class="form-check-label mx-1" for="cambio_${row.nomina}"> 
                                      <i class="bi bi-clock-history"></i> 
                                  </label>
                                </div>`
                            }
                          ]
                      });
                  }).catch((error) => {
                    console.log(error);
              });            
    }

    function registrarAsistencia(){
          let datosAsistencia = [];
          let fromDataAsistencia = new FormData;
          let turno = $('#turnoLayout').val();
          let asistenciaRegistrada = false;

          $('#attendanceTable').DataTable().rows({page:'all'}).every(function () {
            //obtenere el data cargado en el datatable correspondiente a cada fila, data: data,
              const data = this.data(); 
              if(data.id_registro){
                asistenciaRegistrada = true;
                return;
              }
    
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

        if(asistenciaRegistrada) {
          alert('Ya se ha registrado la asistencia')
          return;
        }

          fromDataAsistencia.append('opcion', 17);
          fromDataAsistencia.append('turno', turno);
          fromDataAsistencia.append('codigoLinea', codigoLinea.value);
          fromDataAsistencia.append('datosAsistencia', JSON.stringify(datosAsistencia));
          //Registro del layout
          fromDataAsistencia.append('stationsData', JSON.stringify(stationsData));

          fetch("../api/operacionesLinea.php", {
            method: "POST",
            body: fromDataAsistencia,
          })
            .then(response => response.text())
            .then(data => {
              console.log(data)
              data = JSON.parse(data)
              if(data.estatus && data.estatus == 'ok'){
                  alert(data.mensaje);
                  generarTablaAsistencia();

                  //Actualizar el layout

                  actualizarVistaLayout();
                  //document.getElementById('workspaceGrid').innerHTML = '';
                  return getEstaciones();
              }

              else if (data.estatus && data.estatus == 'error'){
                  alert(data.mensaje)
                  return Promise.reject('Error en respuesta');
              }

              else {
                  alert('Ocurrio un error al realizar el registro') 
                  console.log(data);
                  return Promise.reject('Error desconocido');
                }
            }).then(() =>{ 
              //Encadeamos una promesa para que se haga el guardado del layout despues de cumplir la primer promesa de registro de asistencia
                saveLayout(true);
            })
            .catch(error => {
              console.log(error);
            });
    }

    //Abrir modal de asignar personal a una estacion desde la tabla de personal no asignado
    function openAsignarEstacion(nomina) {
      //console.log(nomina)
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

    //Funciones para la navegacion entre las pantallas de los modales
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
          setTimeout(() => { visible.classList.add('show')}, 100); 
          visible.classList.remove('d-none');
    }

    //Funcion para obtener el numero de control del ultimo registro del punto de cambio
    function getNoControl() {
      const formDataNoControles = new FormData();
      formDataNoControles.append('opcion', 12);
      formDataNoControles.append('codigoLinea', codigoLinea.value)
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

    //Funcion para obtener el numero de dia y numero de evaluacion
    function getEvaluacion(idPC){
      let formDataNoEvaluaciones = new FormData();
      formDataNoEvaluaciones.append('opcion', 25);
      formDataNoEvaluaciones.append('idPC', idPC)

      fetch("../api/operacionesLinea.php", {
        method: "POST",
        body: formDataNoEvaluaciones,
      })
        .then(response => response.json())
        .then(data => {
          if (data.estatus === 'ok') {
              document.getElementById('labelnumeroDia').textContent = data.numeroDia
              document.getElementById('labelnumeroEvaluacion').textContent = (data.numeroEvaluacion == '1') ? 'Inicio de turno' : 'Intermedio'

             numeroDia.value= data.numeroDia
             numeroEvaluacion.value=data.numeroEvaluacion

              if(document.getElementById('numeroDiaEvaluacion').value > 3){
                   document.getElementById('divPCEvaluado').classList.remove("d-none");
                   document.getElementById('evaluacionPuntoCambioForm').classList.add("d-none")
              }

              else {
                document.getElementById('divPCEvaluado').classList.add("d-none")
                document.getElementById('evaluacionPuntoCambioForm').classList.remove("d-none");
              }

          }

          else console.log(data)
        })
        .catch(error => {
          console.error(error);
          return '';
        });
    }
    
    function updateAsistencia(element, clave){
          let table = $('#attendanceTable').DataTable();
          let $row = $(element).closest('tr');
          let data = table.row($row).data();

            if(data['id_registro']){
              //console.log('Fila:', data);
              //console.log('Campo:', campo);
              //console.log('Nuevo valor:', nuevoValor);

              //Logica para actualizar el registro de la tabla
              let nuevoValor = $(element).val();
              let campo = $(element).attr('name');
              let formDataUpdate = new FormData();
              formDataUpdate.append('opcion', 18);
              formDataUpdate.append('id_registro', data['id_registro'])
              formDataUpdate.append(clave, nuevoValor);
              console.log(nuevoValor)

               fetch("../api/operacionesLinea.php", {
                      method: "POST",
                      body: formDataUpdate,
                  })
                  .then((response) => response.text())
                  .then((data) => {
                    console.log(data);
                      data= JSON.parse(data)

                      if(data.error) 
                          alert(data.mensaje);

                      else  // actualizar el data interno de DataTables
                        if (campo) {
                            data[campo] = nuevoValor;
                            //table.row($row).data(data).invalidate();
                        }

                    }).catch((error) => {
                      console.log(error);
                });
            }

          //else {
            //console.log('Aun no se ha registrado la asistencia')
          //}
    }

    //Funcion para cambiar el turno de los trabajadores registrados en la linea
    function cambiarTurno(){
      if(!seleccionadosGlobal || seleccionadosGlobal.length<=0){
        alert("No a seleccionado informacion para actualizar");
        return;
      }

      let turno = ($('#turnoLayout').val() == '1') ? '2' : '1';
      let fromDataCambioTurno = new FormData();
      fromDataCambioTurno.append('opcion', 19);
      fromDataCambioTurno.append('datosAsistenciaCheck', JSON.stringify(seleccionadosGlobal))
      fromDataCambioTurno.append('turnoCambio', turno)
      fromDataCambioTurno.append('codigoLinea', codigoLinea.value)
      fromDataCambioTurno.append('turnoActual', $('#turnoLayout').val())
      saveLayout(true);

        fetch("../api/operacionesLinea.php", {
              method: "POST",
              body: fromDataCambioTurno,
          })
          .then((response) => response.text())
          .then((data) => {
                  data= JSON.parse(data)
                  if(data.estatus == 'ok'){
                        //Ocurtar modal
                        let modalAgregarEstacion = bootstrap.Modal.getInstance(document.getElementById('attendanceModal'));
                        modalAgregarEstacion.hide();
                        alert(data.mensaje)
                    }

                  else{
                    alert(data.mensaje);
                    console.log(data)
                  }
            }).catch((error) => {
              console.log(error);
        });
    }

    //Funcion para obtener los datos del operador y mostrarlos en la estacion
    function getOperator(nomina, estacion, idPC){

        document.getElementById('tiempoPC').innerHTML = ''; 

        if(nomina){
          let fromDataGetOperador = new FormData();
          fromDataGetOperador.append('opcion', 20);
          fromDataGetOperador.append('nomina', nomina);
          fromDataGetOperador.append('idEstacion', estacion)

          fetch("../api/operacionesLinea.php", {
                method: "POST",
                body: fromDataGetOperador,
            })
            .then((response) => response.text())
            .then((data) => {
          
              data= JSON.parse(data)

              if(data.estatus == 'ok'){
                  $("#changeControlInfoNomina").text(data.nomina);
                  $("#changeControlInfoNombre").text(data.nombre);
                  $("#changeControlInfFecha").text(data.fecha_inicio);
                  $("#changeControlInfoTurno").text("TURNO "+data.turno);
                  $("#changeControlInfoComentarios").text( (data.descripcion && data.descripcion != '') ? data.descripcion : 'SIN COMENTARIOS');

                  if(data.fecha_inicio && idPC){ 
                      let fecha_inicio = new Date(data.fecha_inicio);
                      let ahora = new Date();

                      let diferencia_ms = ahora - fecha_inicio; // diferencia en milisegundos
                      let dias = Math.floor(diferencia_ms / (1000 * 60 * 60 * 24));

                      document.getElementById('tiempoPC').innerHTML = `⚠Tiempo activo del punto de cambio: `+dias+' dias'
                    }
              }

              else
                console.log(data.error);

              }).catch((error) => {
                console.log(error);
          });
        }

        else{
          $("#changeControlInfoNomina").text("");
          $("#changeControlInfoNombre").text("");
          $("#changeControlInfFecha").text("");
          $("#changeControlInfoTurno").text("NA");
          $("#changeControlInfoComentarios").text("SIN COMENTARIOS");
        }
    }

    //Eliminnar/finalizar registro de personal disponible
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
            data= JSON.parse(data)
        
            if(data.estatus=='ok'){
                alert(data.mensaje);
                mostrarTablaPNA();
              }
          
            else{
              alert(data.mensaje)
              console.log(data); 
            }
        })
        .catch((error) => {
          alert('No fue posible eliminar el registro')
          console.error(error);
        });
    }

    //Consultar historiakl de layout
    function createStationHistorial(stationData, parent) {
        const station = document.createElement('div');
        station.className = `station ${stationData.colorClass} station-readonly`; // clase adicional
        station.style.left = `${stationData.x}px`;
        station.style.top = `${stationData.y}px`;
        // No se asigna data-station-id ni eventos

        let operatorIcon = '';
        if ((stationData.status === 'occupied' || stationData.status === 'absent') && stationData.nomina) {
            // Intentar cargar la foto si existe
            operatorIcon = `<img src="../img/personal/${stationData.nomina}.jpg" alt="Foto" style="width: 100px; height: 100px; border-radius: 10px; object-fit: cover; border: 3px solid #e9ecef;">`;
        } else {
            operatorIcon = '<i class="bi-person-x" style="font-size: 2rem;"></i>';
        }

        station.innerHTML = `<div class="station-header">${stationData.name}</div>
                              <div class="station-content">
                                  <div class="station-operator">${operatorIcon}</div>
                                  <div class="station-name">${stationData.operator || 'No asignado'}</div>
                              </div>
                              <div class="station-status status-${stationData.status}"></div>`;

        // Si la estación requiere certificación, pintar header amarillo
        if (stationData.isCertificate == 1) {
            station.querySelector('.station-header').style.background = "#ffc107";
            station.querySelector('.station-header').style.color = "rgb(0,0,0,1)";
        }

        parent.appendChild(station);
    }

    //funcion para obtener los registros del historial del layout por dia
    function getHistorialLayout(){
      if(!fechaHistorial.value || !turnoHistorial.value  || !codigoLinea.value)
          return;

      let fromDataHistorial = new FormData();
      fromDataHistorial.append('opcion', 22);
      fromDataHistorial.append('codigoLinea', codigoLinea.value)
      fromDataHistorial.append('fecha', fechaHistorial.value)
      fromDataHistorial.append('turno', turnoHistorial.value)

        fetch("../api/operacionesLinea.php", {
              method: "POST",
              body: fromDataHistorial
          })
          .then(response => response.json())
          .then(data => {
              if(data.estatus === 'ok') {
                const selectHistorial = document.getElementById('idRH');
                selectHistorial.innerHTML='';  

                  //Agregar opcion vacia por defecto
                    let none = document.createElement('option');
                    none.value = '';  
                    none.textContent =  'Registros guardados';
                    selectHistorial.appendChild(none);

                  data.registros.forEach(historial => {
                    const option = document.createElement('option');
                    option.value = historial.idR;   
                    option.textContent = historial.fechaR; 
                    selectHistorial.appendChild(option);
                  });
              } else {
                  console.log('No se encontro algun registro')
                  const selectHistorial = document.getElementById('idRH');
                   selectHistorial.innerHTML='';  

                  //Agregar opcion vacia por defecto
                    let none = document.createElement('option');
                    none.value = '';  
                    none.textContent =  'Registros guardados';
                    selectHistorial.appendChild(none);
              }
          })
          .catch(error => {
              console.error(error);
              alert('Error de conexión');
        });

    }

    function registrarEvaluacionPC() {  
      
      if (document.getElementById('evaluacionPuntoCambioForm').reportValidity()){
          const formData = new FormData();
          const fechaEvaluacion = document.getElementById('fechaEvaluacion').value;
          const numeroDia = document.getElementById('numeroDiaEvaluacion').value;
          const numeroEvaluacion = document.getElementById('numeroEvaluacion').value;
          const comentarios = document.getElementById('comentariosEvaluacion').value;
          const idPC = document.getElementById('idPC').value

          //Radios
          const metrica1 = document.querySelector('input[name="metrica1"]:checked')?.value;
          const metrica2 = document.querySelector('input[name="metrica2"]:checked')?.value;
          const metrica3 = document.querySelector('input[name="metrica3"]:checked')?.value;

          formData.append('opcion', 26);
          formData.append('fechaEvaluacion', fechaEvaluacion);
          formData.append('numeroDia', numeroDia);
          formData.append('numeroEvaluacion', numeroEvaluacion);
          formData.append('metrica1', metrica1);
          formData.append('metrica2', metrica2);
          formData.append('metrica3', metrica3);
          formData.append('comentarios', comentarios);
          formData.append('idPC', idPC);
        
          fetch('../api/operacionesLinea.php', {
            method: 'POST',
            body: formData
          })
          .then(res => res.json())
          .then(data => {
              if(data.estatus == 'ok'){
                alert(data.mensaje)
                //Ocurtar modal
                let modalActual = bootstrap.Modal.getInstance(document.getElementById('changeControlModal'));
                (modalActual) ? modalActual.hide() : '';
              }

              else {
                  alert("No fue posible hacer el registro de la evaluación")
                  console.log(data);
              }
                
          })
          .catch(err => {
            
              console.error(err)
              alert("Ocurrió un error al hacer el registro")
          }); 
      }
    }

    //Funcion para actualizar el layout al cambiar de turno registrar una asistencia volver a renderisar la estaciones y el svg
    function actualizarVistaLayout(){
         let turno = $("#turnoLayout").val()

          if(turno){
            document.getElementById('workspaceGrid').innerHTML =  `
            <!-- Contenedor dinámico que se expandirá con el contenido -->
              <div id="dynamicContainer">
                <!-- SVG dinámico (se ajustará al contenido) -->
                <svg id="workspace-svg">
                  <defs>
                    <pattern id="grid" patternUnits="userSpaceOnUse" width="20" height="20">
                      <path d="M 20 0 L 0 0 0 20" fill="none" stroke="#cbd5e1" stroke-width="0.8"/>
                    </pattern>
                    <marker id="arrowMarker" markerWidth="10" markerHeight="10" refX="9" refY="5" orient="auto">
                      <polygon points="0 0, 9 5, 0 10" fill="context-stroke" stroke="none" />
                    </marker>
                  </defs>
                  <rect x="0" y="0" width="100%" height="100%" fill="none" />
                  <g id="shapes-group"></g>
                </svg>
                <!-- Aquí se insertarán dinámicamente las estaciones (divs) -->
              </div>`;
            getEstaciones();

            svg = document.getElementById('workspace-svg');
            shapesGroup = $('#shapes-group');
            selectedElement = null;
            draggingShape = null;
            elementCounter = 0;
            refreshElementList();
            setTimeout(() => {
              loadShapesFromJSON();
            }, 500);
          }
    }

    // ========== NUEVO CÓDIGO DEL DIAGRAMADOR SVG ==========
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
      // Obtener el elemento SVG

      /*
      const svgElement = typeof svgSelector === 'string'
        ? document.querySelector(svgSelector)
        : svgSelector;

      if (!svgElement || svgElement.tagName !== 'svg') {
        throw new Error('No se encontró un elemento SVG válido');
      } 
      */

      // Seleccionar todos los elementos dentro del SVG (excluyendo el propio <svg>)
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

    /*
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
        updateContainerAndSVGBounds();
      });
    */


    $(window).on('mousemove', function(e) {
      if (!draggingShape) return;
      e.preventDefault();

      const current = getSVGCoords(e);
      let dx = current.x - draggingShape.start.x;
      let dy = current.y - draggingShape.start.y;

      const elt = draggingShape.element;
      const original = draggingShape.original;

      // 👇 Obtener nueva posición según tipo
      let newX = 0;
      let newY = 0;

      if (elt.tagName === 'rect') {
        newX = original.x + dx;
        newY = original.y + dy;

        // 🚫 limitar a 0
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

      // ✅ aplicar posición ya limitada
      setPosition(elt, original, dx, dy);

      const angle = getRotationAngle(elt);
      applyRotation(elt, angle);

      updateCommonFields(elt);
      updateContainerAndSVGBounds();
    });


    $(window).on('mouseup', function() { draggingShape = null; });

    $('#workspaceGrid').on('click', '#workspace-svg', function(e) {
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

      /*
        for (let shape of shapes) {
          const tag = shape.tagName;
          let left, top, width, height;
          if (tag === 'rect') {
            left = parseFloat(shape.getAttribute('x')) || 0;
            top = parseFloat(shape.getAttribute('y')) || 0;
            width = parseFloat(shape.getAttribute('width')) || 0;
            height = parseFloat(shape.getAttribute('height')) || 0;
          } else if (tag === 'circle') {
            const cx = parseFloat(shape.getAttribute('cx')) || 0;
            const cy = parseFloat(shape.getAttribute('cy')) || 0;
            const r = parseFloat(shape.getAttribute('r')) || 0;
            left = cx - r;
            top = cy - r;
            width = 2 * r;
            height = 2 * r;
          } else if (tag === 'line') {
            const x1 = parseFloat(shape.getAttribute('x1')) || 0;
            const y1 = parseFloat(shape.getAttribute('y1')) || 0;
            const x2 = parseFloat(shape.getAttribute('x2')) || 0;
            const y2 = parseFloat(shape.getAttribute('y2')) || 0;
            left = Math.min(x1, x2);
            top = Math.min(y1, y2);
            width = Math.abs(x2 - x1);
            height = Math.abs(y2 - y1);
          } else if (tag === 'text') {
            left = parseFloat(shape.getAttribute('x')) || 0;
            top = parseFloat(shape.getAttribute('y')) || 0;
            width = 0;  // el texto puede tener ancho variable, pero no es crítico
            height = 0;
          } else {
            continue;
          }
          // Actualizar límites
          if (width > 0 && height > 0) {
            minX = Math.min(minX, left);
            minY = Math.min(minY, top);
            maxX = Math.max(maxX, left + width);
            maxY = Math.max(maxY, top + height);
          } else {
            // Para líneas y texto, al menos considerar el punto
            minX = Math.min(minX, left);
            minY = Math.min(minY, top);
            maxX = Math.max(maxX, left);
            maxY = Math.max(maxY, top);
          }
        } 
      */
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

      //Generar tabla de personal no asignado
      mostrarTablaPNA();

      //Generar listado de personal perteneciente a la linea
      generarTablaAsistencia();

      getHistorialLayout();

      //DECLARACION DE EVENTOS
        //OBTENER NUMERO DE NOMINA
            nominaModalAsignar.addEventListener('change', function (){
                let nombreModalAsignar = document.getElementById('nombreModalAsignar');
                if(nominaModalAsignar && nominaModalAsignar !='') {
                    let formDataConsultarNombre = new FormData;
                    formDataConsultarNombre.append('nomina',nominaModalAsignar.value)
                    formDataConsultarNombre.append('opcion', 7)
                    formDataConsultarNombre.append('codigoLinea', codigoLinea.value)
            
                    nominaModalAsignar.disabled = true
                    nombreModalAsignar.value= ''; 
                    nombreModalAsignar.placeholder= "Consultando datos del empleado...";  

                        fetch("../api/operacionesLinea.php", {
                                method: "POST",
                                body: formDataConsultarNombre,
                            })
                            .then((response) => response.text())
                            .then((data) => {
                                data= JSON.parse(data)
                                if(data.estatus=='ok'){
                                    nombreModalAsignar.value= data.nombre;                              
                                    $('#listaOperacionesOperador').html(`${(data.estaciones) ? data.estaciones : 'SIN OPERACIONES ASIGNADAS EN LA LINEA'}`)
                                  }
                              
                              else{
                                  nombreModalAsignar.placeholder= "Nombre del empleado...";  
                                  $('#listaOperacionesOperador').html(`<span class="form-help">Lista de operaciones asignadas del trabajador en la linea</span>`)
                                console.log(data.error); 
                              }

                              nominaModalAsignar.disabled = false;
                            })
                            .catch((error) => {
                              nombreModalAsignar.placeholder= "Nombre del empleado..."; 
                              nominaModalAsignar.disabled = false;
                              console.log(error);
                        });
                  }
            })

            //obtner nombre modalPC
            $('#nominaModalPC').on('change', function () {
                let nombreModalAsignar = document.getElementById('nombreModalPC');
                if(nominaModalPC && nominaModalPC !='') {
                    let formDataConsultarNombre = new FormData;
                    let idEstacion = document.getElementById('id_estacion').value;
                    formDataConsultarNombre.append('nomina',nominaModalPC.value)
                    formDataConsultarNombre.append('opcion', 7)
                    formDataConsultarNombre.append('codigoLinea', codigoLinea.value)
            
                    nominaModalPC.disabled = true
                    nombreModalAsignar.value= ''; 
                    nombreModalAsignar.placeholder= "Consultando datos del empleado...";  

                        fetch("../api/operacionesLinea.php", {
                                method: "POST",
                                body: formDataConsultarNombre,
                            })
                            .then((response) => response.text())
                            .then((data) => {
                                data= JSON.parse(data)
            
                                // 1. Buscar la estación
                                const estacion = data.allEst.find(e => e.id_estacion === idEstacion);
                                  if (!estacion) {
                                        document.getElementById('alertPC').textContent = "ESTE TRABAJADOR NO CUENTA CON UN REGISTRO ANTERIOR EN ESTE PROCESO";
                                    }

                                  else //2. Evaluar si el registro no esta activo
                                    if(estacion.fecha_fin){
                                      // 3. Convertir a objeto Date
                                      const fechaBase = new Date(estacion.fecha_fin);
                                      const fechaActual = new Date();

                                      // 4. Calcular diferencia en días
                                      const diffMs = fechaActual - fechaBase;
                                      const diffDias = Math.round(diffMs / (1000 * 60 * 60 * 24));

                                      if (diffDias > 30) {
                                        document.getElementById('alertPC').textContent = "EL ULTIMO REGISTRO DE OPERACION ES MAYOR A 30 DIAS";
                                      }

                                      else 
                                        document.getElementById('alertPC').textContent = "";
                                    }

                                  else {
                                    //El trabajador sigue activo en la estacion
                                    document.getElementById('alertPC').textContent = ""
                                  }

                                if(data.estatus=='ok'){
                                    nombreModalAsignar.value= data.nombre;                              
                                    $('#listaOperacionesOperadorPC').html(`${(data.estaciones) ? data.estaciones : 'SIN OPERACIONES ASIGNADAS EN LA LINEA'}`)
                                }
                              
                                else{
                                    nombreModalAsignar.placeholder= "Nombre del empleado...";  
                                    $('#listaOperacionesOperadorPC').html(`<span class="form-help">Lista de operaciones asignadas del trabajador en la linea</span>`)
                                    console.log(data.error); 
                                }

                              nominaModalPC.disabled = false;
                            }).catch((error) => {
                              nombreModalAsignar.placeholder= "Nombre del empleado..."; 
                              nominaModalPC.disabled = false;
                              console.log(error);
                        });
                  }
            })

            //Obtener nombre de registro asistencia 
            $('#nominaAsistencia').on('change', function () {
                let nombreModalAsignar = document.getElementById('nombreAsistencia');
                let nominaAsistencia = document.getElementById('nominaAsistencia');

                if(nominaAsistencia && nominaAsistencia !='') {
                    let formDataConsultarNombre = new FormData;
                    formDataConsultarNombre.append('nomina',nominaAsistencia.value)
                    formDataConsultarNombre.append('opcion', 7)
                    formDataConsultarNombre.append('codigoLinea', codigoLinea.value)                    
            
                    nominaAsistencia.disabled = true
                    nombreModalAsignar.value= ''; 
                    nombreModalAsignar.placeholder= "Consultando datos del empleado...";  

                        fetch("../api/operacionesLinea.php", {
                                method: "POST",
                                body: formDataConsultarNombre,
                            })
                            .then((response) => response.text())
                            .then((data) => {
                                data= JSON.parse(data)
                                if(data.estatus=='ok'){
                                    nombreModalAsignar.value= data.nombre;
                                  }
                              
                              else{
                                  nombreModalAsignar.placeholder= "Nombre del empleado...";  
                                console.log(data.error); 
                              }

                              nominaAsistencia.disabled = false;
                            })
                            .catch((error) => {
                              nombreModalAsignar.placeholder= "Nombre del empleado..."; 
                              nominaAsistencia.disabled = false;
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

                    nominaNoAsignado.disabled = true
                    nombreNoAsignado.value= ''; 
                    nombreNoAsignado.placeholder= "Consultando datos del empleado...";  

                        fetch("../api/operacionesLinea.php", {
                                method: "POST",
                                body: formDataConsultarNombre,
                            })
                            .then((response) => response.text())
                            .then((data) => {
                                data= JSON.parse(data)
                            
                                if(data.estatus=='ok')
                                    nombreNoAsignado.value= data.nombre;
                              
                              else{
                                nombreNoAsignado.placeholder= "Nombre del empleado..."; 
                                console.log(data); 
                              }

                              nominaNoAsignado.disabled = false
                            })
                            .catch((error) => {
                              nominaNoAsignado.disabled = false
                               nombreNoAsignado.placeholder= "Nombre del empleado...";  
                              console.log(error);
                        });
                  }
            })

            nominaPC.addEventListener('change', function(){
              let nombrePC = document.getElementById('nombrePC');
            
                if(nominaPC && nominaPC !='') {

                    nominaPC.disabled = true
                    nombrePC.value= ''; 
                    nombrePC.placeholder= "Consultando datos del empleado..."; 

                    let formDataConsultarNombre = new FormData;
                    formDataConsultarNombre.append('nomina',nominaPC.value)
                    formDataConsultarNombre.append('opcion', 7)

                        fetch("../api/operacionesLinea.php", {
                                method: "POST",
                                body: formDataConsultarNombre,
                            })
                            .then((response) => response.text())
                            .then((data) => {
                                  data= JSON.parse(data)
                            
                                if(data.estatus=='ok')
                                    nombrePC.value= data.nombre;
                              
                                else{
                                   nombrePC.placeholder= "Nombre del empleado..."; 
                                   console.log(data); 
                                }

                              nominaPC.disabled = false
                            })
                            .catch((error) => {
                                nominaPC.disabled = false
                                nombrePC.placeholder= "Nombre del empleado...";  
                              console.log(error);
                        });
                  }
            })
        //FIN OBTENER NUMERO DE NOMINA


        //ASIGNAR TRABAJADOR DESDE EL MODAL DEL PC
          btnAsignarOperadorPC.addEventListener('click', function(){
              var formDataAsig = new FormData;
              let assignmentFormPC= document.getElementById('assignmentFormPC');
              let nomina = document.getElementById('nominaModalPC').value;
              let nombre = document.getElementById('nombreModalPC').value;
              let estacion = document.getElementById('id_estacion').value;
              let fecha  = document.getElementById('assignmentDatePC').value;
              let turno = document.getElementById('turnoasignarPC').value;
              let comentarios = document.getElementById('comentariospc').value;
              
              formDataAsig.append("opcion", "3");
              formDataAsig.append("nomina", nomina);
              formDataAsig.append("nombre", nombre);
              formDataAsig.append("estacion", estacion);
              formDataAsig.append("fecha", fecha);
              formDataAsig.append("turno", turno);
              formDataAsig.append("comentarios", comentarios);
              formDataAsig.append('codigoLinea', codigoLinea.value)

                if(!nombre) { 
                  alert("No se encontro registro del empleado ingresado o se perdió la conexión con el servidor.")
                  return;
                }

                if(assignmentFormPC.reportValidity()){
                    fetch("../api/operacionesLinea.php", {
                            method: "POST",
                            body: formDataAsig,
                        })
                        .then((response) => response.text())
                        .then((data) => {
                            data= JSON.parse(data)
                            if(data.estatus=='ok'){
                                alert(data.mensaje);
                                //assignmentFormPC.reset();

                                $('#nominaModalPC').val('');
                                $('#nombreModalPC').val('');
                                $('#comentariospc').val('');
                                $('#listaOperacionesOperadorPC').html('<span class="form-help">Lista de operaciones asignadas del trabajador en la linea </span>');
                                getEstacion(estacion);
                            }

                          else {
                            alert(data.mensaje)
                            console.log(data.error)
                          }
                        })
                        .catch((error) => {
                          console.log(error);
                    });
                }
            })

        //Remover trabajador de la estacion
        btnRemoverTrabajadorPC.addEventListener('click', function(){
          let formDataReniver = new FormData;
          let idPC = document.getElementById('idPC');
          let estacionId = document.getElementById('idEstacionModalPC').value;
          let nominaTrabajador = document.getElementById('idTrabajadorAsignado').value; 
          let nombreTrabajador = $("#changeControlInfoNombre").text()
          let turno = $('#turnoLayout').val()

            formDataReniver.append("opcion", "10");
            formDataReniver.append("idEstacion", estacionId);
            formDataReniver.append("nomina", nominaTrabajador);
            formDataReniver.append("turno", turno);

          if(idPC.value){
              alert('Debe finalizar el punto de cambio activo');
              return;
          }

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
                               let modalActual = bootstrap.Modal.getInstance(document.getElementById('changeControlModal'));
                              (modalActual) ? modalActual.hide() : '';
                              getEstacion(estacionId)

                              if(data.asignacion==0){
                                 let registrar = confirm('¿Desea agregar a esta persona al personal disponible?');
                                    if(registrar) registrarDisponible(nominaTrabajador, nombreTrabajador, $('#turnoLayout').val())
                              }
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

        //Registrar un punto de cambio
        confirmChange.addEventListener('click', function(){
          let registroCambioForm = document.getElementById('registroCambioForm');
          let formDataPuntoCambio = new FormData;
          let  idEstacion = document.getElementById('id_estacion').value;

          if(!registroCambioForm.reportValidity()) return;

          if(document.getElementById('nombrePC').value == '' || document.getElementById('nombrePC').value == null){
                alert('No se encontro registro del empleado ingresado o se perdió la conexión con el servidor.');
                return;
          }

          let nominaEtiqueta = $("#changeControlInfoNomina").text().trim();
          let nominaInput = $("#nominaPC").val().trim();

          if (nominaInput !== "" && Number(nominaEtiqueta) === Number(nominaInput)) {
              alert("No se puede crear el punto de cambio ya que el trabajador está asignado a esta estación.");
              return;
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
                    data= JSON.parse(data)  
                    if(data.estatus=='ok'){ 
                        alert(data.mensaje);
                        let modalActual = bootstrap.Modal.getInstance(document.getElementById('changeControlModal'));
                        (modalActual) ? modalActual.hide() : '';
                        getEstacion(idEstacion)
                    } 
                    else  alert(data.mensaje);
                })
                .catch((error) => {
                  console.log(error);
            }); 
        });

        //funcion para cerrar el punto de cambio
        btnConfirmClose.addEventListener('click', function(){
            let formDataCerrarPC = new FormData;
            let idPC = document.getElementById('idPC');
            let idEstacion = document.getElementById('idEstacionModalPC').value;
            let cierreControlCambioForm = document.getElementById('cierreControlCambioForm')
            let nominaAPC = document.getElementById('idTrabajadorAsignado')
            let nombreTrabajador = $("#changeControlInfoNombre").text()

            if(!idPC.value){
                alert('No hay un punto de cambio activo en esta estación');
                return;
            }

            if(!cierreControlCambioForm.reportValidity()) return;

            formDataCerrarPC.append('opcion', 14);
            formDataCerrarPC.append('idEstacion', idEstacion);
            formDataCerrarPC.append('idPC', idPC.value);
            formDataCerrarPC.append('notasAdicionales', document.getElementById('notasAdicionales').value);
            formDataCerrarPC.append('fechaCierre', document.getElementById('fechaCierre').value); 
            formDataCerrarPC.append('nomina', nominaAPC.value)

              fetch("../api/operacionesLinea.php", {
                      method: "POST",
                      body: formDataCerrarPC,
                  })
                  .then((response) => response.text())
                  .then((data) => {
              
                      data= JSON.parse(data)
                      if(data.estatus=='ok'){
                          alert(data.mensaje);
                          let modalActual = bootstrap.Modal.getInstance(document.getElementById('changeControlModal'));
                          (modalActual) ? modalActual.hide() : '';

                          //Actualizar informacion de la estacion
                          getEstacion(idEstacion);
                            if(data.asignacion==0){
                              let registrar = confirm('¿Desea agregar a esta persona al personal disponible?');
                              if(registrar) registrarDisponible(nominaAPC.value, nombreTrabajador, $('#turnoLayout').val());
                            }
                        }

                      else  alert(data.mensaje);

                  }).catch((error) => {
                      console.log(error);
              });
        })

        //Registrar una asistencia individual
        btnAsistencia.addEventListener('click', function(e){
              e.preventDefault();
              var formDataAsig = new FormData();
              let formAsistencia= document.getElementById('formAsistencia');
              let nominaAsistencia = document.getElementById('nominaAsistencia');
              let nombreAsistencia = document.getElementById('nombreAsistencia') 
              let estatusAsistencia = document.getElementById('estatusAsistencia')
              let comentarioAsistencia = document.getElementById('comentarioAsistencia') 
              let estacionAsistencia = document.getElementById('estacionAsistencia') 

              formDataAsig.append("opcion", 27);
              formDataAsig.append("nomina", nominaAsistencia.value);
              formDataAsig.append("nombre", nombreAsistencia.value);
              formDataAsig.append('codigoLinea', codigoLinea.value)
              formDataAsig.append('estatusAsistencia', estatusAsistencia.value)
              formDataAsig.append("estacion", estacionAsistencia.value);
              formDataAsig.append("turno", $('#turnoLayout').val());
              formDataAsig.append("comentarios", comentarioAsistencia.value); 
              // Verifica si el valor seleccionado no es vacío antes de agregarlo
              let estacionSeleccionada = $('#estacionAsistencia option:selected').val();
              if (estacionSeleccionada && estacionSeleccionada !== '') {
                  formDataAsig.append("nombreEstacion", $('#estacionAsistencia option:selected').text());
              } 
              
              if(!nombreAsistencia.value.trim()) { 
                alert("No se encontro registro del empleado ingresado o se perdió la conexión con el servidor.")
                return;
              }

                if(formAsistencia.reportValidity()){
                    fetch("../api/operacionesLinea.php", {
                            method: "POST",
                            body: formDataAsig,
                        })
                        .then((response) => response.json())
                        .then((data) => {
                            if(data.estatus=='ok'){
                                alert(data.mensaje);
                                formAsistencia.reset();
                                generarTablaAsistencia();
                            }

                          else {
                            alert(data.mensaje)
                            console.log(data.error)
                          }
                        })
                        .catch((error) => {
                          console.log(error);
                    });
                }
        })

        //Detectar cuando se abra el modal de asignar estacion
        document.getElementById('btnMenuAsignar').addEventListener('click', function (){
          document.getElementById('assignmentDate').value = (new Date()).toLocaleString('sv-SE').slice(0, 16);
           document.getElementById('turnoasignar').value =  $('#turnoLayout').val();
        })

        //Generar fecha de registro de personal NAD en el formulario
        document.getElementById('btnMenuRegiswtroNAD').addEventListener('click', function(){
          document.getElementById('assignmentDatePNA').value = (new Date()).toLocaleString('sv-SE').slice(0, 16);
            document.getElementById('turnoAsignarPersonalDisponible').value =  $('#turnoLayout').val();
            mostrarTablaPNA();
        })

        btnGuardarEstacion.addEventListener('click', agregarEstacion);
        btnAsignarOperador.addEventListener('click', asignarEstaciones);
        btnGuardarDisponible.addEventListener('click', registrarPNA); 
        btnRegistrarAsistencia.addEventListener('click', registrarAsistencia);
 
        btnMenuAsignarControlModal.addEventListener('click', function(){changeContent('ventanasModalPC','contAsignacion')});
        btnInfoRPC.addEventListener('click', function(){changeContent('ventanasModalPC','contInfoEstacion')});
        btnRegistroPc.addEventListener('click', function(){changeContent('ventanasModalPC','contregistroCambioForm')});
        //btnLiberarPC.addEventListener('click', function(){changeContent('ventanasModalPC', 'contLiberarPC')});
        btnTablaPNA.addEventListener('click', function(){changeContent('ventanadModalPersonalNA', 'contTablaDisponibles')});
        btnRegistroPNA.addEventListener('click', function(){changeContent('ventanadModalPersonalNA', 'contRegistroPersonalDisponible')});
        btnMenuRegistroAs.addEventListener('click', generarTablaAsistencia);
        btnCambioTurno.addEventListener('click', cambiarTurno);
        
        fechaHistorial.addEventListener('change', getHistorialLayout)
        turnoHistorial.addEventListener('change', getHistorialLayout)
        btnEvaluacion.addEventListener('click', registrarEvaluacionPC);
        btnHistorialLayout.addEventListener('click', getHistorialLayout )

        // SELECT → change
        $('#attendanceTable tbody').on('change', 'select', function () {
            updateAsistencia(this, 'estatus');
        });

        // INPUT → Enter
        $('#attendanceTable tbody').on('keydown', 'input', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                updateAsistencia(this, 'observacionesAsistencia');
                //$(this).blur(); // opcional
            }
        });

        //Marcar/Desmarcar los check de la tabla de asistencia
        checkPadre.addEventListener('change', function(){
          if (checkPadre.checked) {
                  seleccionadosGlobal = [...datosAsistenciaCheck] //Esto genera un nuevo arreglo
                  $('#attendanceTable').DataTable().rows({ page: 'all' }).every(function () {
                      $(this.node()).find('input[type="checkbox"]').prop('checked', true);
                  });
            }

          else {
                  seleccionadosGlobal = [];
                  $('#attendanceTable').DataTable().rows({ page: 'all' }).every(function () {
                    $(this.node()).find('input[type="checkbox"]').prop('checked', false);
                  });
          }
        })

        // Delegación de eventos para checkboxes dinámicos
        $('#attendanceTable tbody').on('change', 'input[type="checkbox"]', function(){
            const nomina = $(this).data('nomina');
            const index = seleccionadosGlobal.indexOf(nomina);
            
            //AGREGA EL ELEMENTO
            if(this.checked && index === -1) 
                seleccionadosGlobal.push(nomina);
            
            //ELIMINA EL ELEMENTO
            else if(!this.checked && index > -1) 
                seleccionadosGlobal.splice(index, 1);
            checkPadre.checked = (seleccionadosGlobal.length < datosAsistenciaCheck.length) ? false : true;
        });

        //Funcion para actualizar el layout al cambiar el turno
        document.getElementById('turnoLayout').addEventListener('change', actualizarVistaLayout)

        //Funcion para mostrar el layout por la fecha seleccionada
        document.getElementById('idRH').addEventListener('change', function() {
          let idRH = document.getElementById('idRH').value;
            if(!fechaHistorial.value || !idRH) {
                alert('No se encontro algun registro en la fecha y turno seleccionados');
                return;
            }

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
                grid.innerHTML = ''; // Limpiar
                if(data.estatus === 'ok') {
                    const stations = data.layout;
                    stations.forEach(station => {
                        // Reutilizar createStation pero en un grid diferente, y sin eventos de drag
                        createStationHistorial(station, grid);
                    });
                    // Opcional: mostrar mensaje de éxito con la fecha del registro
                } else {
                    alert(data.mensaje || 'Error al cargar el historial');
                }
            })
            .catch(error => {
                console.error(error);
                alert('Error de conexión');
            });
        });

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


/* 
    seleccionadosGlobal = datosAsistenciaCheck 
    Esto es un error ya que en vez de generar un nuevo arreglo asignado a la variable seleccionadosGlobal 
    pasa la referencia de la ubicacion en memoria de la variable datosAsistenciaCheck entonces ambas variables 
    apuntan a la mimsa ubicacion de la memoria por lo que al modificar cualquiera de las dos, los cambios se 
    Se veran reflejados en ambas variables  

    las funciones selectShape y clearselection son las que ponen el borde degradado 
    al seleccionar un objeto hay que revisar este para
    ver que funcione al dar clic y quitar el clic de la figura svg

    A veces miramos el cielo buscando respuestas,
    como si las estrellas guardaran secretos que nosotros olvidamos.
    Brillan tranquilas, lejanas, eternas en su silencio,
    mientras nosotros corremos, dudamos, sentimos.

    Y entonces, en medio de la noche, parece que susurran algo suave:
    “Dicen las estrellas que los fugaces somos nosotros.”

    Y tiene sentido.
    Porque ellas permanecen,
    pero nosotros somos instante, latido, chispa.

    Quizá por eso duele tanto lo que se va,
    y por eso también vale tanto lo que se queda,
    aunque sea solo por un momento.

    Porque ser fugaz no es ser pequeño,
    es ser irrepetible. ✨
*/