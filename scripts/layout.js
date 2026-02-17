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

  let stationName = document.getElementById('nombreEstacion');
  let letstationForm = document.getElementById('stationForm');
  let stationDescription = document.getElementById('stationdescripcion');
  let requiredCertification = document.getElementById('requiereCertificacion');
  let certificacionF = document.getElementById('certificacion');
  let codigoLinea = document.getElementById('codigoLinea');
  let assignmentForm = document.getElementById('assignmentForm');

  // Datos para las estaciones
  var stationsData;

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
      
      //Actualizar y mostrar el modal de la estacion al abrirlo
      showStationModal(stationData) {
        console.log('Datos de la estacion: ',stationData)
        document.getElementById('imgInfochangeControlModal').src= (stationData.nomina) ? `../img/personal/${stationData.nomina}.jpg` : `../img/personal/na.jpg`;
        document.getElementById('nombreEstacionModalPC').textContent = (stationData.name).toUpperCase();
        document.getElementById('idEstacionModalPC').value = stationData.id;
        document.getElementById('idTrabajadorAsignado').value = stationData.nomina || '';

        //Setear valores del formulario de registro de PC
          getNoControl().then(resultado => { document.getElementById('no_controlCambio').value = resultado;});
          document.getElementById('fechaHora_inicio').value = (new Date()).toLocaleString('sv-SE').slice(0, 16);
          document.getElementById('id_estacion').value = stationData.id;
          document.getElementById('nombre_estacion').value = stationData.name;
          document.getElementById('turnoPuntoCambio').value =  $('#turnoLayout').val();
          
        //Setear input de id del punto de cambio en el formulario de cierre de PC
          document.getElementById('idPC').value = stationData.idPC || '';
          document.getElementById('fechaCierre').value = (new Date()).toLocaleString('sv-SE').slice(0, 16);

          getOperator(stationData.nomina, stationData.id);
  
        //Modal creado registro de punto de cambio
            const stationModal = new bootstrap.Modal(document.getElementById('changeControlModal'));
            stationModal.show();
      }
    }

    // Instancia del sistema de drag
    const dragSystem = new OptimizedDragSystem();

    function saveLayout() {
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
        formDataPosicion.append('codigoLinea', codigoLinea.value)
        
          // console.log("datos", layoutData)
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
        <div class="station-header">
          ${stationData.name}
        </div>
        <div class="station-content">
          <div class="station-operator"> ${operatorIcon} </div>
          <div class="station-name">${stationData.operator || 'No asignado'}</div>
        </div>
        <div class="station-status status-${stationData.status}"></div>`;

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
        fetch("../api/operacionesLinea.php", {
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
      let turno = document.getElementById('turnoLayout').value;
      let comentarios = document.getElementById('comentarios').value;
      
      formDataAsig.append("opcion", "3");
      formDataAsig.append("nomina", nomina);
      formDataAsig.append("nombre", nombre);
      formDataAsig.append("estacion", estacion);
      formDataAsig.append("fecha", fecha);
      formDataAsig.append("turno", turno);
      formDataAsig.append("comentarios", comentarios);
      formDataAsig.append('codigoLinea', codigoLinea.value)

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
                      $('#listaOperacionesOperador').html('<span class="form-help">Lista de operaciones asignadas del trabajador </span>');
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

              (newData.operator) ? station.querySelector('.station-name').textContent = newData.operator 
                                 : station.querySelector('.station-name').textContent = 'No asignado';

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
              const operator = station.querySelector('.station-operator');
                   (newData.nomina) ? operator.innerHTML=`<img src="../img/personal/${newData.nomina}.jpg" alt="Foto del operador" 
                                              style="width: 100px; height: 100px; border-radius: 10px; 
                                                    object-fit: cover; border: 3px solid #e9ecef; 
                                                    margin-bottom: 10px;">` 
                                                :
                                      operator.innerHTML='<i class="bi-person-x"></i>';

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
      select.innerHTML='';  

        //Agregar opcion vacia por defecto
          let none = document.createElement('option');
          none.value = '';  
          none.textContent =  'Selecciona una estación...';
          select.appendChild(none);

        stationsData.forEach(station => {
          const option = document.createElement('option');
          option.value = station.id;   
          option.textContent = station.name; 
          select.appendChild(option);
        });
    }

    //Registrar personal no asignado
    function registrarPNA(){
      let formDataNoAsignado = new FormData
      let fmPersonalNoAsignado = document.getElementById('fmPersonalNoAsignado');

      if(fmPersonalNoAsignado.reportValidity()){

          if( document.getElementById('nombreNoAsignado').value == '' || document.getElementById('nombreNoAsignado').value == null){
                alert('No se encontro registro del empleado ingresado') 
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

    //Generar tabla con los datos de la tabla de personal no asignado
    function mostrarTablaPNA(){
          let formDataNoAsignadoL = new FormData 
          formDataNoAsignadoL.append('codigoLinea', codigoLinea.value)
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
                                  onclick="confirmarEliminar('107528')">
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

    //Funciion para generar la tabla de la lista de asistencia
    function generarTablaAsistencia(){
      let fromDataAsistencia = new FormData;
      fromDataAsistencia.append('opcion', 16);
      fromDataAsistencia.append('codigoLinea', codigoLinea.value);
      fromDataAsistencia.append('turno', $('#turnoLayout').val());

        fetch("../api/operacionesLinea.php", {
                method: "POST",
                body: fromDataAsistencia,
            })
            .then((response) => response.text())
            .then((data) => {   
                      data= JSON.parse(data)
                      datosAsistenciaCheck = data.map(item => Number(item.nomina));

                      $('#attendanceTable').DataTable().destroy();
                      $('#attendanceTable').DataTable({
                        //scrollY: '300px',
                        //scrollCollapse: true,
                        autoWidth: false,
                        responsive: false,
                        data: data,
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
                            { data: null,
                              render: row => `<div class="fw-bold" data-nombre="${row.nombre}" 
                                                                   data-nomina="${row.nomina}"
                                                                   data-id_estacion="${row.id_estacion}"
                                                                   data-nombre_estacion= "${row.nombre_estacion}">
                                                      ${row.nombre}</div>
                                              <small class="text-muted">ID: ${row.nomina}</small>`
                            },
                            {
                              data:null,
                              render: row =>`<div> ${(row.nombre_estacion).toUpperCase()}</div>`
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
                              render: () => `
                                <input type="text" name="observacionesAsistencia"
                                      class="form-control form-control-custom"
                                      placeholder="Observaciones...">`
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
                  estatus: $(fila).find('select[name="estatusAsistencia"]').val()
                  //observaciones: $(fila).find('input[name="observacionesAsistencia"]').val()
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
              data = JSON.parse(data)
              if(data.estatus && data.estatus == 'ok'){
                  alert(data.mensaje);
                  generarTablaAsistencia();
              }

              else {
                  alert('Ocurrio un error al realizar el registro') 
                  console.log(data);
                }
            })
            .catch(error => {
              console.log(error);
            });
    }

    //Abrir modal de asignar personal a una estacion
    function openAsignarEstacion(nomina) {
      //console.log(nomina)
      let modalPersonalDisponible = document.getElementById('modalPersonalDisponible');
      let modalAsignarOperador = document.getElementById('modalAsignarOperador');

      let modalActual = bootstrap.Modal.getInstance(modalPersonalDisponible);
      (modalActual) ? modalActual.hide() : '';

      newModal = new bootstrap.Modal(modalAsignarOperador);
      newModal.show();   
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

    function updateAsistencia(element){
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
              formDataUpdate.append('estatus', nuevoValor);

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

          else {
            //console.log('Aun no se ha registrado la asistencia')
          }
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
      
        fetch("../api/operacionesLinea.php", {
              method: "POST",
              body: fromDataCambioTurno,
          })
          .then((response) => response.text())
          .then((data) => {
            data= JSON.parse(data)

            if(data.estatus == 'ok')
                alert(data.mensaje)

            else{
              alert(data.mensaje);
              console.log(data.error)
            }

            }).catch((error) => {
              console.log(error);
        });
    }

    //Funcion para obtener los datos del operador y mostrarlos en la estacion
    function getOperator(nomina, estacion){
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
                  $("#changeControlInfoComentarios").text( (data.descripcion && data.descripcion != '') 
                                                            ? data.descripcion : 'SIN COMENTARIOS');
                  return;
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

      //Eventlistener
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

        nominaPC.addEventListener('change', function(){
          let nombrePC = document.getElementById('nombrePC');

            if(nominaPC && nominaPC !='') {
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
                            nombrePC.value= ''; 
                            console.log(data); 
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

            formDataReniver.append("opcion", "10");
            formDataReniver.append("idEstacion", estacionId);
            formDataReniver.append("nomina", nominaTrabajador);

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
                alert('No se encontro registro del empleado ingresado') 
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

              fetch("../api/operacionesLinea.php", {
                      method: "POST",
                      body: formDataCerrarPC,
                  })
                  .then((response) => response.text())
                  .then((data) => {
                    console.log(data);
                      data= JSON.parse(data)
                      if(data.estatus=='ok'){ 
                          alert(data.mensaje);

                          let modalActual = bootstrap.Modal.getInstance(document.getElementById('changeControlModal'));
                          (modalActual) ? modalActual.hide() : '';

                          //Actualizar informacion de la estacion
                          getEstacion(idEstacion);
                      }

                        else  alert(data.mensaje);

                        }).catch((error) => {
                          console.log(error);
                });
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
        btnInfoRPC.addEventListener('click', function(){changeContent('ventanasModalPC','contInfoEstacion')});
        btnRegistroPc.addEventListener('click', function(){changeContent('ventanasModalPC','contregistroCambioForm')});
        btnLiberarPC.addEventListener('click', function(){changeContent('ventanasModalPC', 'contLiberarPC')});
        btnTablaPNA.addEventListener('click', function(){changeContent('ventanadModalPersonalNA', 'contTablaDisponibles')});
        btnRegistroPNA.addEventListener('click', function(){changeContent('ventanadModalPersonalNA', 'contRegistroPersonalDisponible')});
        btnMenuRegistroAs.addEventListener('click', generarTablaAsistencia);
        btnCambioTurno.addEventListener('click', cambiarTurno);

        // SELECT → change
        $('#attendanceTable tbody').on('change', 'select', function () {
            updateAsistencia(this);
        });

        // INPUT → Enter
        $('#attendanceTable tbody').on('keydown', 'input', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                updateAsistencia(this);
                //$(this).blur(); // opcional
            }
        });

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
    });

/* 
  seleccionadosGlobal = datosAsistenciaCheck 
  Esto es un error ya que en vez de generar un nuevo arreglo asignado a la variable seleccionadosGlobal 
  pasa la referencia de la ubicacion en memoria de la variable datosAsistenciaCheck entonces ambas variables 
  apuntan a la mimsa ubicacion de la memoria por lo que al modificar cualquiera de las dos, los cambios se 
  Se veran reflejados en ambas variables
*/