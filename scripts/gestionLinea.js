
  let btnAsignarOperador = document.getElementById('btnAsignarOperador');
  let btnGuardarDisponible = document.getElementById('btnGuardarDisponible');
  let confirmChange = document.getElementById('confirmChange');
  let btnAsignarOperadorPC = document.getElementById('btnAsignarOperadorPC') //Boton de asignacion desde el modal de gestion pc
  let btnConfirmClose = document.getElementById('btnConfirmClose');
  let btnEvaluacion = document.getElementById('btnEvaluacion');
  let btnAsistencia = document.getElementById('btnAsistencia') //Boton para registrar una asistencia individual

  let tableListadoPersonal = document.getElementById('tableListadoPersonal');
  btnRegistrarOperador = document.getElementById('btnRegistrarOperador') //Boton para registrar personal

  //BOTONES FLOTANTES
    let btncloseSidebar = document.getElementById('btncloseSidebar')
    let btnfloatingMenu = document.getElementById('btnfloatingMenu')
  //FIN BOTONES FLOTANTES

  let checkPadre = document.getElementById("checkPadre");
  var seleccionadosGlobal = [];
  var datosAsistenciaCheck; 
  let btnCambioTurno = document.getElementById('btnCambioTurno');
  let btnMenuRegistroAs = document.getElementById('btnMenuRegistroAs');
  let btnRegistrarAsistencia = document.getElementById('btnRegistrarAsistencia');
  let btnHistorialLayout = document.getElementById('btnHistorialLayout');

  //Boton para los chips
  let chipsEstaciones = [];

  //AREA DE DIBUJO DE LAS ESTACIONES
  let workspaceGrid;

  //Inputs del modal asignar operador
  let nominaModalAsignar = document.getElementById('nominaModalAsignar');
  let nominaPC = document.getElementById('nominaPC'); 
 
 //INPUTS DE MODAL REGISTRAR OPERADOR
  let nominaModalRegistrar = document.getElementById('nominaModalRegistrar');
  let selectRegistrar = document.getElementById('selectRegistrar');

  //Inputs del modal consultar historial
  let fechaHistorial = document.getElementById('fechaHistorial');
  let turnoHistorial = document.getElementById('turnoHistorial');

  //document.getElementById('turnoasignar').value = $('#turnoLayout option:selected').text();
  document.getElementById('turnoAsignarPersonalDisponible').value = $('#turnoLayout').val();
  
  //Botones de menu del modal
      //Modal de registro de PC
        let btnInfoRPC = document.getElementById('btnInfoRPC');
        let btnRegistroPc = document.getElementById('btnRegistroPc');
        let btnMenuAsignarControlModal = document.getElementById('btnMenuAsignarControlModal');

        //Modal de personal personal sin asignar
        let btnTablaPNA = document.getElementById('btnTablaPNA');
        let btnRegistroPNA = document.getElementById('btnRegistroPNA');
  //Fin botones menu modal

  //Inputs del modal personal disponible
      //Formulario de registro de personal no asignado
      var nominaNoAsignado= document.getElementById('nominaNoAsignado');
  //Fin inputs personal disponible 

  //IDENTIFICADOR DE LA LINEA
  let codigoLinea = document.getElementById('codigoLinea');

  //FORMULARIO PARA EL REGISTRO DE ASIGNACION DE UN OPERADOR
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
  const workspaceState = {zoomLevel: 0.4};

    // Sistema de drag & drop optimizado con soporte para modales
    class OptimizedDragSystem {
      init() {
        $('#workspaceGrid').on('click', '.station', (e) => {
            const stationId = $(e.currentTarget).data('station-id');
            const stationData = stationsData.find(s => s.id == stationId);
            if(stationData) {
                this.showStationModal(stationData);
            }
        });
      }
      
      //Actualizar y mostrar el modal de la estacion al abrirlo
      showStationModal(stationData) {

        //Validar que la ruta de la imagen exista
        const img = document.getElementById('imgInfochangeControlModal');

        img.onerror = function() {
            this.onerror = null;
            this.src = '../img/personal/ok.jpg';
        };


        document.getElementById('imgInfochangeControlModal').src= (stationData.nomina) ? `../img/personal/${stationData.nomina}.jpg` : `../img/personal/na.jpg`;
        document.getElementById('nombreEstacionModalPC').textContent = (stationData.name) ? (stationData.name).toUpperCase() : '';
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

          //Cargar el select del formulario del registro de asignacion
          setLiberados(stationData.id, 'nominaModalPC', codigoLinea.value)

          //Cargar el select del formulario de registro del pc
          setLiberados(stationData.id, 'nominaPC', null)


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
      //Funcion para cambiar el som al presionar shift + scroll mouse
      document.getElementById('workspaceGrid').addEventListener('wheel', function(e) {
        if (e.shiftKey) {
          e.preventDefault();
          if (e.deltaY < 0) zoomIn();
          else zoomOut();
        }
      });
    }

    function zoomIn() {
      if (workspaceState.zoomLevel < 2) {
        workspaceState.zoomLevel += 0.01;
        applyZoom();
      }
    }

    function zoomOut() {
      if (workspaceState.zoomLevel > 0.20) {
        workspaceState.zoomLevel -= 0.01;
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
      station.innerHTML = `<div class="station-header py-2"></div>
                            <div class="station-content">
                              <div class="station-name text-break"> ${stationData.name} </div>
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
    function getEstaciones() {
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
          //Limpeamos el contenedor donde se cargan las estaciones evitando modificar el contenedor donde se cargan las formas svg
          $('#dynamicContainer').siblings().remove();

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
    async function getEstacion(id){
     //Se usa cuando, tras finalizar un punto de cambio, es necesario recuperar la información real del usuario asignado a la estación, ya que el listado muestra datos sobrescritos por el usuario del PC
     let formDataEstacion = new FormData();
      formDataEstacion.append('opcion', 15);
      formDataEstacion.append('idEstacion', id);
      formDataEstacion.append('turno', $('#turnoLayout').val());
       return fetch("../api/operacionesLinea.php", {
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
                       return true;
                    }

                    else { 
                        alert(data.mensaje);
                        return false;
                    }
                }
              ).catch((error) => {
                  console.log(error);
                  return false;
                });
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
                      mostrarTablaOperaciones();
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

        /* Actualizar el arreglo*/ // Buscamos solo el objeto necesario
        let estation = stationsData.find(obj => String(obj.id) === String(stationId));
        //Mostrar el valor real del objeto estation
        //console.log('Antes', JSON.parse(JSON.stringify(estation)));
        if (estation) {
           (newData.operator) ? estation.operator = newData.operator : '';
          //estation.name =  (newData.name)'Estación en uso';
           (newData.colorClass) ? estation.colorClass = newData.colorClass : '';
           (newData.status) ? estation.status = newData.status : '';
            estation.nomina = (newData.nomina) ?? null;
            estation.idPC = (newData.idPC) ?? null;
            estation.estatusPC = (newData.estatusPC) ?? null;
            estation.isCertificate = newData.isCertificate
        } 
    } 

    //Mostrar listado de estaciones registradas para colocar en los select
    function listarEstaciones(){
      const select = document.getElementById('stationSelect');
      const selectRegistrar = document.getElementById('selectRegistrar'); //Selecet para el registro de operaciones en el modal de registrar personal
      const estacionAsistencia = document.getElementById('estacionAsistencia');

      select.innerHTML='';
      selectRegistrar.innerHTML='';
      estacionAsistencia.innerHTML='';


        //Agregar opcion vacia por defecto
          let none = document.createElement('option');
          none.value = '';
          none.textContent =  'Selecciona una estación...';
          select.appendChild(none)

          let noneR = document.createElement('option');
          noneR.value = '';
          noneR.textContent =  'Selecciona una estación...';
          selectRegistrar.appendChild(noneR)

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

          const optionR = document.createElement('option');
          optionR.value = station.id;   
          optionR.textContent = station.name; 
          selectRegistrar.appendChild(optionR)
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

                      if(!data.personal || (data.personal).length<1){
                         $('#attendanceTable').DataTable().clear().draw();
                        return;
                      }

                      datosAsistenciaCheck = data.personal.map(item => Number(item.nomina));

                      //Mostrar el formulario de registro para una asistencia individual si ya se ha echo el registro de asistencia
                      if (data.personal.length > 0 && 'id_registro' in data.personal[0]){
                            formAsistencia.classList.remove('d-none'); 
                          }
                        
                      //Ocultar el formulario de registro si no se ha echo el registro de asistencia
                      else{
                          formAsistencia.classList.add('d-none');
                      }

                      resumenAsistencia();
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
                              render: row => `<select name="estatusAsistencia" class="form-control form-control-custom attendance-status" data-id_estacion="${row.id_estacion}">
                                                <option value="1" ${(row.estatus && row.estatus=='1') ? 'selected' : ''}>✅ ASISTENCIA</option>
                                                <option value="2" ${(row.estatus && row.estatus=='2') ? 'selected' : ''}>❌ FALTA INJUSTIFICADA</option>
                                                <option value="3" ${(row.estatus && row.estatus=='3') ? 'selected' : ''}>🟢 PERMISO SIN GOCE DE SUELDO</option>
                                                <option value="4" ${(row.estatus && row.estatus=='4') ? 'selected' : ''}>🏖️ VACACIONES</option>
                                                <option value="5" ${(row.estatus && row.estatus=='5') ? 'selected' : ''}>🟡 PARO TÉCNICO</option>
                                                <option value="6" ${(row.estatus && row.estatus=='6') ? 'selected' : ''}>⚪ DESCANSO</option>
                                                <option value="7" ${(row.estatus && row.estatus=='7') ? 'selected' : ''}>🚫 SUSPENSIÓN</option>
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
                     $('#attendanceTable').DataTable().destroy();
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

                  //actualizarVistaLayout();
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
    
    async function updateAsistencia(element, clave) {
        let table = $('#attendanceTable').DataTable();
        let $row = $(element).closest('tr');
        let rowData = table.row($row).data();

        // Validar que exista información de la fila y el id_registro
        if (!rowData || !rowData['id_registro']) {
            // console.log('Aun no se ha registrado la asistencia');
            return false;
        }

        // Obtener nuevo valor y campo modificado
        let nuevoValor = $(element).val();
        let campo = $(element).attr('name');

        let formDataUpdate = new FormData();
        formDataUpdate.append('opcion', 18);
        formDataUpdate.append('id_registro', rowData['id_registro']);
        formDataUpdate.append(clave, nuevoValor);

        try {
            const response = await fetch("../api/operacionesLinea.php", {
                method: "POST",
                body: formDataUpdate,
            });

            const text = await response.text();

            console.log(text);

            const responseData = JSON.parse(text);

            if (responseData.error) {
                alert(responseData.mensaje);
                return false;
            }

            // Actualizar el data interno de DataTables
            if (campo) {
                rowData[campo] = nuevoValor;

                // Actualizar la fila internamente en DataTables
                //table.row($row).data(rowData).invalidate();
                resumenAsistencia();

                return true;
            }

            return false;

        } catch (error) {
            console.log(error);
            return false;
        }
    }

    function resumenAsistencia(){
      let resumenAsistencia = document.getElementById('resumenAsistencia');
      let formResumen = new FormData();
      formResumen.append('opcion', 36);
      formResumen.append('turno', $('#turnoLayout').val());
      formResumen.append('codigoLinea', codigoLinea.value);
      
        fetch("../api/operacionesLinea.php", {
          method: "POST",
          body: formResumen,
        })
          .then((response) => response.text())
          .then((data) => {
            data = JSON.parse(data)

            if (data.error){
                console.log(data);
                resumenAsistencia.classList.add('d-none');
              }

            else {
                // Ocultar la información de resumen de asistencia
                if (!data.resumen.porcentajeA || data.resumen.length <= 0) {
                  resumenAsistencia.classList.add('d-none');
                } 
                
                else {
                  // Mostrar la información de resumen de asistencia
                  resumenAsistencia.classList.remove('d-none');
                  document.getElementById('countAsistencia').textContent = data.resumen.asistencias
                  document.getElementById('countPermisos').textContent = data.resumen.permisos
                  document.getElementById('countFaltas').textContent = data.resumen.faltas
                  document.getElementById('countVacaciones').textContent = data.resumen.vacaciones
                  document.getElementById('countIncapacidades').textContent = data.resumen.incapacidad
                  document.getElementById('countPAsistencia').textContent = `% ${parseFloat(data.resumen.porcentajeA).toFixed(2)}`;
                  console.log('Resumen',data)
                }
              }
          }).catch((error) => {
            console.log(error);
            resumenAsistencia.classList.add('d-none');
          });
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
                        let modalListadoPersonal = bootstrap.Modal.getInstance(document.getElementById('modalListadoPersonal'));
                        modalListadoPersonal.hide();
                        alert(data.mensaje)

                        getEstaciones();
                        mostrarTablaOperaciones();
                        mostrarTablaPersonal();
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

    //Consultar historial de layout
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
         seleccionadosGlobal = [];
         document.getElementById('turnoasignar').value = $("#turnoLayout").val()

          if(turno){
            /*
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
            */

            $('#dynamicContainer').siblings().remove();
            getEstaciones();
            mostrarTablaOperaciones();
            mostrarTablaPersonal();
            generarTablaAsistencia();
            resumenAsistencia();

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

    //Funcion para los chips de agregar personal
        //Función para agregar una chip
            function agregarChipTalla() {
              //Se modifican las cadenas a mayusculas para que sea indistinto valores como Xsd y xSD en la busqueda del valor en el arreglo tallasAlta
              if (!chipsEstaciones.some(item => ((item.value).toUpperCase()).trim() === ((selectRegistrar.value).toUpperCase()).trim()) ) { //Se usa tallasAlta some ya que el arreglo es un objeto de pares clave valor 
                    if (selectRegistrar.value.trim() !== '') { //Evalua si no esta vacio el input de la talla
                      chipsEstaciones.push({ label: $("#selectRegistrar option:selected").text(), 
                                             value: selectRegistrar.value 
                                          }); //Agregamos el valor del input sin los espacios que se hayan ingresado al final o al inicio (tallaLabel.value).trim()
                      selectRegistrar.value = ''; //Se limpia el input donde se agrega la talla
                      actualizarTallasAltaChips();
                  }
              }
                else alert('Ya existe este registro');
            }

            // Función para remover un chip
            function removerChipTalla(label) {
                chipsEstaciones.splice(label, 1);
                actualizarTallasAltaChips();
            }

            // Función para actualizar los chips
            function actualizarTallasAltaChips() { //Esta funcion actualiza la vista de las tallas despues de agregar o eliminar una talla
                const container = document.getElementById('operationsListContainer');
                container.innerHTML = chipsEstaciones.map((t, index) => `
                  <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-info bg-opacity-10 text-dark">
                    <span> ${t.label}</span>
                    <i class="bi bi-x-circle-fill remove-icon" data-index = ${index} style="cursor: pointer; color: #dc3545;" title="Eliminar"></i>
                  </div>
                `).join(''); //Se agrega el indice (index) para poder eliminar mas facil lo elemetnos del arreglo

                const removeIcons = container.querySelectorAll('.remove-icon');

                removeIcons.forEach(icon => {
                    icon.addEventListener("click", function(event) {
                        event.preventDefault();
                        // Remover el chip correspondiente
                        const label = icon.getAttribute("data-index");
                        removerChipTalla(label);
                    });
                });
            }
    //FIN FUNCION

    //FUNCION PARA LOS CHIPS DE ACTUALIZAR OPERACIONES
          //Agregar chip
           function agregarChipTallaUpdate(estaciones, nomina = null) {
                //Se modifican las cadenas a mayusculas para que sea indistinto valores como Xsd y xSD
                if(!estaciones.some(item => ((item.idE).toUpperCase()).trim() === (($('#selectRegistrarUpdate').val()).toUpperCase()).trim())) {
                    if ($('#selectRegistrarUpdate').val().trim() !== '') { //Validar que se haya enviado algo

                            //Enviar la peticion para el registro de la informacion en la base de datos
                            const formData = new FormData();
                            formData.append('opcion', 32);
                            formData.append('nomina', nomina);
                            formData.append('estacionId', $('#selectRegistrarUpdate').val());
                              fetch("../api/operacionesLinea.php", {
                                  method: "POST",
                                  body: formData
                              })
                              .then(response => response.json())
                                .then(data => {
                                  if (data.estatus === 'ok') {
                                    estaciones.push({
                                      nombre_estacion: $("#selectRegistrarUpdate option:selected").text(),
                                      idE: $('#selectRegistrarUpdate').val()
                                    });

                                    $('#selectRegistrarUpdate').val('');
                                    actualizarTallasAltaChipsUpdate(estaciones, nomina);
                                  }

                                  else {
                                    console.log(data.mensaje)
                                  }
                                })
                                .catch(error => {
                                  console.error(error);
                                  alert('Error de conexión');
                              });
                    }
                } else {
                    alert('Ya existe este registro');
                }
            }

            // Función para remover un chip en la tabla de listado de presonal
            function removerChipTallaUpdate(estaciones, index, nomina) {
                  const formData = new FormData();
                  formData.append('opcion', 33);
                  formData.append('nomina', nomina);
                  formData.append('estacionId', estaciones[index].idE);
                    fetch("../api/operacionesLinea.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.estatus === 'ok') {
                            estaciones.splice(index, 1);
                            actualizarTallasAltaChipsUpdate(estaciones, nomina);
                        } else {
                            console.log(data)
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        alert('Error de conexión');
                    });
            }

            // Función para actualizar los chips en la tabla de listao de personal
            function actualizarTallasAltaChipsUpdate(data, nomina = null) { //Esta funcion actualiza la vista de las tallas despues de agregar o eliminar una talla
                const container = document.getElementById('operationsListContainerUpdate');

                //limpeamos el contenedor de los chips
                container.innerHTML ='';
                container.innerHTML = data.map((t, index) => `
                  <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-info bg-opacity-10 text-dark">
                    <span> ${t.nombre_estacion}</span>
                    <i class="bi bi-x-circle-fill remove-iconUpdate" data-index='${index}' data-nomina='${nomina}' data-estacion='${t.idE}' style="cursor: pointer; color: #dc3545;" title="Eliminar"></i>
                  </div>
                `).join(''); //Se agrega el indice (index) para poder eliminar mas facil lo elemetnos del arreglo

                //Evento para eliminar los chips de lista de operaciones update
                const removeIcons = container.querySelectorAll('.remove-iconUpdate');
                removeIcons.forEach(icon => {
                    icon.addEventListener("click", function(event) {
                        event.preventDefault();
                        // Remover el chip correspondiente
                        const index = icon.getAttribute("data-index");
                        removerChipTallaUpdate(data, index, nomina);
                    });
                });
            }
    //FIN FUNCION

    //Funcion para cargar el listado de personal
    function mostrarTablaPersonal(){
          let formDataPersonal = new FormData 
          formDataPersonal.append('codigoLinea', codigoLinea.value)
          formDataPersonal.append('turno', $('#turnoLayout').val())
          formDataPersonal.append('opcion', 29)
          document.getElementById('checkPadre').checked = false

            fetch("../api/operacionesLinea.php", {
                    method: "POST",
                    body: formDataPersonal,
                })
                .then((response) => response.text())
                .then((data) => {
          
                    data = JSON.parse(data)
                     datosAsistenciaCheck = data.map(item => Number(item.nomina));
                    $('#tableListadoPersonal').DataTable().destroy();
                    const table = $('#tableListadoPersonal').DataTable({
                          data: data,
                          columns: [
                              { 
                                  data: 'nomina',
                                  className: 'px-4 py-3 fw-medium',
                                  render: data.nomina
                              },
                              { 
                                  data: 'nombre',
                                  render: function(data) {
                                      return `<span class="fw-semibold">${data}</span>`;
                                  }
                              },
                              { 
                                  data: null,
                                  render: function(data) {
                                      return `<span class="badge bg-secondary bg-opacity-10 text-dark px-3 py-1 rounded-pill text-uppercase">${codigoLinea.value}</span>`;
                                  }
                              },
                              { 
                                  data: 'estatus',
                                  render: function(data){ 
                                      let estatus = '';
                                        if(data == '0')  estatus = '<span class="badge btn-success bg-opacity-15 text-dark px-3 py-1 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Disponible</span>';
                                        else if(data == '1') estatus = '<span class="badge btn-info bg-opacity-15 text-dark px-3 py-1 rounded-pill"><i class="bi bi-person-check-fill me-1"></i> Asignado</span>';
                                        else if(data == '2') estatus = '<span class="badge bg-danger bg-opacity-10 text-dark px-3 py-1 rounded-pill"><i class="bi bi-person-x-fill me-1"></i> Eliminado</span>';
                                        else if(data == '3') estatus = '<span class="badge bg-warning bg-opacity-10 text-dark px-3 py-1 rounded-pill"><i class="bi bi-person-x-fill me-1"></i> Asignado en otra linea</span>';
                                     return estatus;
                                  }
                              },
                              { 
                                  data: null,
                                  render: function(data) {
                                      return `<button class="btn btn-sm btn-outline-danger rounded-circle btnElimPer" data-nomina="${data.nomina}"><i class="bi bi-trash3"></i></button>
                                              <!-- <button class="btn btn-sm btn-outline-secondary rounded-circle" data-nomina="${data.nomina}"><i class="bi bi-arrow-left-right"></i></button> -->
                                              <button class="btn btn-sm btn-outline-info rounded-circle tableBtnUpdateOperaciones" data-nomina="${data.nomina}" data-nombre="${data.nombre}" ><i class="bi bi-diagram-3"></i></button>`;
                                  },
                                  className: 'text-end',
                                  orderable: false
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
                          ],
                          pageLength: 10,
                          lengthMenu: [5, 10, 25, 50],
                          responsive: false,
                          scrollX: true,
                          deferRender: false,
                          paging: true
                    });
                })
                .catch((error) => {
                  console.log(error);
            });
    }

    //Funcion para mostrar el listado de operaciones
    function mostrarTablaOperaciones() {
      let formDataOperaciones = new FormData();
      formDataOperaciones.append('codigoLinea', codigoLinea.value);
      formDataOperaciones.append('opcion', 31);
      formDataOperaciones.append('turno', $('#turnoLayout').val());

      fetch("../api/operacionesLinea.php", {
        method: "POST",
        body: formDataOperaciones,
      })
        .then((response) => response.text())
        .then((data) => {

          data = JSON.parse(data);
          const response = data.data;

          // Inicializar DataTable
          $('#operationsTable').DataTable().destroy();
          const table = $('#operationsTable').DataTable({
            data: response,
            columns: [
              {
                data: 'nombre_estacion',
                className: 'px-4 py-3 fw-semibold text-dark'
              },
              {
                data: null,
                className: 'px-4 py-3',
                // Añadir clase dinámica al <td> mediante createdCell
                createdCell: function (td, cellData, rowData, row, col) {
                  $(td).addClass('chips-col-' + rowData.id_estacion);
                },
                render: function (rowData) {
                  if (!rowData.asignados || rowData.asignados.length === 0) {
                    return `<span class="text-muted fst-italic small placeholder-asignados">Sin asignar</span>`;
                  }
                  let html = '<div class="d-flex flex-wrap gap-2">';
                  rowData.asignados.forEach(person => {
                    html += `
                      <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-warning bg-opacity-25 shadow-sm chip-asignado"
                           data-nomina="${person.nomina}">
                        <span class="small">${person.nombre}</span>
                        <i class="bi bi-x-circle-fill remove-assigned" 
                          style="cursor: pointer; font-size: 0.8rem; color: #dc3545;"
                          data-nomina="${person.nomina}" 
                          data-estacion="${rowData.id_estacion}" title="Quitar asignación"></i>
                      </div>
                    `;
                  });
                  html += '</div>';
                  return html;
                }
              },
              {
                data: null,
                className: 'px-4 py-3',
                render: function (rowData) {
                  let selectHtml = `<select class="form-select form-select-sm bg-info bg-opacity-10 border-0 rounded-3">`;
                  selectHtml += `<option value="">Seleccionar operario...</option>`;

                  if (rowData.liberados && rowData.liberados.length > 0) {
                    rowData.liberados.forEach(person => {
                      selectHtml += `<option value="${person.nomina}">${person.nombre} (${person.nomina})</option>`;
                    });
                  } else {
                    selectHtml += `<option disabled>Sin liberados disponibles</option>`;
                  }
                  selectHtml += `</select>`;

                  const btnHtml = `<button class="btn btn-sm btn-info rounded-3 btn-add-worker" data-estacion="${rowData.id_estacion}">
                                    <i class="bi bi-plus-lg"></i>
                                  </button>`;

                  return `
                    <div class="d-flex gap-2 align-items-center">
                      ${selectHtml}
                      ${btnHtml}
                    </div>
                  `;
                }
              }
            ],
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            responsive: false,
            scrollX: true
          });

          // Badge inicial (basado en el DOM recién generado)
          actualizarBadgeAsignadosDOM();
        })
        .catch((error) => {
          console.error(error);
        });
    }


    // Agrega un chip a la columna de la estación especificada
    function agregarPersonaOperacion(estacionId, nomina, nombreCompleto) {
      const $td = $(`#operationsTable td.chips-col-${estacionId}`);
      if ($td.length === 0) {
        console.warn(`No se encontró la columna para estación ${estacionId}`);
        return;
      }

      // Verificar si la persona ya está en un chip (evita duplicado visual)
      if ($td.find(`.chip-asignado[data-nomina="${nomina}"]`).length > 0) {
        alert('Esta persona ya está asignada a esta estación.');
        return;
      }

      // Eliminar el placeholder "Sin asignar" si existe
      $td.find('.placeholder-asignados').remove();

      // Crear el nuevo chip
      const chipHtml = `
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-warning bg-opacity-25 shadow-sm chip-asignado"
            data-estacion="${estacionId}" data-nomina="${nomina}">
          <span class="small">${nombreCompleto}</span>
          <i class="bi bi-x-circle-fill remove-assigned" 
            style="cursor: pointer; font-size: 0.8rem; color: #dc3545;"
            data-nomina="${nomina}" data-estacion="${estacionId}" title="Quitar asignación"></i>
        </div>
      `;

      // Agregar al contenedor de chips (asumimos que el td contiene un div.d-flex.flex-wrap)
      const $chipsContainer = $td.find('.d-flex.flex-wrap');
      if ($chipsContainer.length) {
        $chipsContainer.append(chipHtml);
      } else {
        // Si no hay contenedor (por ejemplo, solo el placeholder), lo creamos
        $td.html(`<div class="d-flex flex-wrap gap-2">${chipHtml}</div>`);
      }

      actualizarBadgeAsignadosDOM();
    }

    // Elimina un chip a partir de su elemento contenedor
    function removerPersonaOperacion($chipContainer) {
      const $td = $chipContainer.closest('td');
      $chipContainer.remove();

      // Si ya no quedan chips, mostrar placeholder
      const $chipsContainer = $td.find('.d-flex.flex-wrap');
      if ($chipsContainer.length && $chipsContainer.children('.chip-asignado').length === 0) {
        $td.html('<span class="text-muted fst-italic small placeholder-asignados">Sin asignar</span>');
      } else if (!$chipsContainer.length) {
        // Si no había contenedor, restaurar placeholder directamente
        $td.html('<span class="text-muted fst-italic small placeholder-asignados">Sin asignar</span>');
      }

      actualizarBadgeAsignadosDOM();
    }

    // Actualiza el badge contando nóminas únicas de todos los chips visibles
    function actualizarBadgeAsignadosDOM() {
       const table = $('#operationsTable').DataTable();
            const nominasSet = new Set();
            table.rows().every(function () {
              const asignados = this.data().asignados || [];
              asignados.forEach(p => nominasSet.add(p.nomina));
            });
            $('#totalAsignadosBadge').text(`${nominasSet.size} personas asignadas`);
    }

    //Funcion para mostrar el listado de personal liberado en una operacion
    function setLiberados(idE, select, codigoLinea = null){
      let nombreSelect = document.getElementById(select);
      let formDataLiberados = new FormData();
      formDataLiberados.append('idE', idE);
      formDataLiberados.append('opcion', 34);
      formDataLiberados.append('turno', $('#turnoLayout').val());
      (codigoLinea && codigoLinea !='') ? formDataLiberados.append('codigoLinea', codigoLinea) : '';
      
      fetch("../api/operacionesLinea.php", {
        method: "POST",
        body: formDataLiberados,
      })
        .then((response) => response.text())
        .then((data) => {
          data = JSON.parse(data);
          //console.log(data)
          nombreSelect.innerHTML = '<option value="">Selecciona una opción</option>';
          if (data.estatus !== 'ok') {
            return;
          }
          
          data.data.forEach((persona) => {
            const opcion = document.createElement('option');
            opcion.value = persona.nomina;
            opcion.textContent = persona.nombre;
            nombreSelect.appendChild(opcion);
          });
        })
        .catch((error) => {
          console.error(error);
        });
    }

    //Funcion para eliminar personal
    function eliminarPersonal(nomina){
          let formDataE = new FormData();
          formDataE.append('opcion', 37);
          formDataE.append('nomina', nomina); 
                   
            fetch("../api/operacionesLinea.php", {
                method: "POST",
                body: formDataE
            })
            .then(response => response.json())
            .then(data => {
                if(data.estatus === 'ok') {
                  mostrarTablaPersonal();
                  alert('Trabajador eliminado')                  
                } else {
                    console.log(data)
                }
            })
            .catch(error => {
                console.error(error);
                alert('Error de conexión');
            });
    }

    // ========== DIAGRAMADOR SVG ==========
    let svg = document.getElementById('workspace-svg');
    let shapesGroup = $('#shapes-group');
    let selectedElement = null;
    let draggingShape = null;
    let elementCounter = 0;

    /*Funcion para obtener los elementos del SV*/
    function getAllSVGElements() {
      // Obtener el elemento SVG

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

      //console.log(result)
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

            console.log(`Cargadas ${shapesArray.formas.length} formas. Próximo elementCounter = ${window.elementCounter}`);
      })
      .catch((error) => { 
        console.log(error); 
      });
    }

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

      //Generar tabla de personal no asignado
      mostrarTablaPNA();

      //Generar listado de personal perteneciente a la linea
      generarTablaAsistencia();

      getHistorialLayout();

      mostrarTablaPersonal();
      mostrarTablaOperaciones();

      //DECLARACION DE EVENTOS
        //OBTENER NUMERO DE NOMINA
            nominaModalRegistrar.addEventListener('change', function (){
                let nombreModalRegistrar = document.getElementById('nombreModalRegistrar');
                if(nominaModalRegistrar && nominaModalRegistrar !='') {
                    let formDataConsultarNombre = new FormData;
                    formDataConsultarNombre.append('nomina',nominaModalRegistrar.value)
                    formDataConsultarNombre.append('opcion', 7)
                    formDataConsultarNombre.append('codigoLinea', codigoLinea.value)
            
                    nominaModalRegistrar.disabled = true
                    nombreModalRegistrar.value= ''; 
                    nombreModalRegistrar.placeholder= "Consultando datos del empleado...";  

                    console.log(nombreModalRegistrar.value)
                    console.log(codigoLinea.value)

                        fetch("../api/operacionesLinea.php", {
                                method: "POST",
                                body: formDataConsultarNombre,
                            })
                            .then((response) => response.text())
                            .then((data) => {
                              console.log(data)
                                data= JSON.parse(data)
                                if(data.estatus=='ok'){
                                    nombreModalRegistrar.value= data.nombre;
                                  }
                              
                              else{
                                  nombreModalRegistrar.placeholder= "Nombre del empleado...";  
                                  console.log(data.error); 
                              }

                              nominaModalRegistrar.disabled = false;
                            })
                            .catch((error) => {
                              nombreModalRegistrar.placeholder= "Nombre del empleado..."; 
                              nominaModalRegistrar.disabled = false;
                              console.log(error);
                        });
                }
            })

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
                              console.log(data)
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

            //obtner la ultima fecha de operacion en la estacion actual (antes-> obtener nombre modalPC)
            $('#nominaModalPC').on('change', function () {
                if(nominaModalPC && nominaModalPC !='') {
                    let idEstacion = document.getElementById('id_estacion').value;
                    let formDataConsultarNombre = new FormData;

                    formDataConsultarNombre.append('nomina',nominaModalPC.value)
                    formDataConsultarNombre.append('idE',idEstacion)
                    formDataConsultarNombre.append('opcion', 35)
                    //formDataConsultarNombre.append('codigoLinea', codigoLinea.value)
     
                        fetch("../api/operacionesLinea.php", {
                                method: "POST",
                                body: formDataConsultarNombre,
                            })
                            .then((response) => response.text())
                            .then((data) => {
                                data= JSON.parse(data)

                                if(data.estatus=='ok'){
                                  // 1. Verificar si en el listado de estaciones donde ha sido asignado el trabajador se encuentra la estacion actual
                                  const estacion = data.allEst;

                                  //Si no existe la etacion
                                    if (!estacion) {
                                          document.getElementById('alertPC').textContent = "ESTE TRABAJADOR NO CUENTA CON UN REGISTRO ANTERIOR EN ESTE PROCESO";
                                      }

                                    //Si la estacion exsite
                                    else //2. Evaluar si el registro no esta activo, si esta activo el valor debera ser null, de no estar activo tendra un valor la fecha
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
                                }
                              
                                else{
                                    document.getElementById('alertPC').textContent = ""
                                    console.log(data.error); 
                                }

                            }).catch((error) => {
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

          /*
            nominaPC.addEventListener('change', function(){
                if(nominaPC && nominaPC !='') {
                    let idEstacion = document.getElementById('id_estacion').value;
                    let formDataConsultarNombre = new FormData;
                    
                    formDataConsultarNombre.append('nomina',nominaPC.value)
                    formDataConsultarNombre.append('idE',idEstacion)
                    formDataConsultarNombre.append('opcion', 35)

                        fetch("../api/operacionesLinea.php", {
                                method: "POST",
                                body: formDataConsultarNombre,
                            })
                            .then((response) => response.text())
                            .then((data) => {
                                  data= JSON.parse(data)
                            
                                if(data.estatus=='ok')
                                   
                              
                                else{

                                }

                            })
                            .catch((error) => {
                              console.log(error);
                        });
                  }
            }) 
          */
        //FIN OBTENER NUMERO DE NOMINA

        //ASIGNAR TRABAJADOR DESDE EL MODAL DEL PC
          btnAsignarOperadorPC.addEventListener('click', function(){
              var formDataAsig = new FormData;
              let assignmentFormPC= document.getElementById('assignmentFormPC');
              let nomina = document.getElementById('nominaModalPC').value;
              let nombre = $('#nominaModalPC').find('option:selected').text();
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
                                getEstacion(estacion);
                                mostrarTablaOperaciones();
                                mostrarTablaPersonal();

                                let modalActual = bootstrap.Modal.getInstance(document.getElementById('changeControlModal'));
                                (modalActual) ? modalActual.hide() : '';
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


        //REGISTRAR TRABAJADOR AL LISTADO DE PERSONAL EN EL MODAL REGISTRARS 
        btnRegistrarOperador.addEventListener('click', function(){
              var formDataAsig = new FormData
              let nomina = document.getElementById('nominaModalRegistrar').value;
              let nombre = document.getElementById('nombreModalRegistrar').value;
              let fecha  = document.getElementById('fecharegistrar').value;
              
              formDataAsig.append("opcion", "28");
              formDataAsig.append("nomina", nomina);
              formDataAsig.append("nombre", nombre);
              formDataAsig.append("fecha", fecha);
              formDataAsig.append('codigoLinea', codigoLinea.value)
              formDataAsig.append('operaciones', JSON.stringify(chipsEstaciones))
              formDataAsig.append('turno', $('#turnoLayout').val())
              //console.log('Operaciones:'+  JSON.stringify(chipsEstaciones))

                if(!nombre) { 
                  alert("No se encontro registro del empleado ingresado o se perdió la conexión con el servidor.")
                  return;
                }

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

                            $('#nominaModalRegistrar').val('');
                            $('#nombreModalRegistrar').val('');
                            chipsEstaciones = [];
                            $('#operationsListContainer').html('');
                            mostrarTablaPersonal()
                        }

                      else {
                        alert(data.mensaje)
                        console.log(data.error)
                      }
                    })
                    .catch((error) => {
                      console.log(error);
                });                
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
                              mostrarTablaOperaciones();
                              mostrarTablaPersonal();
                            }

                            else 
                              alert(data.mensaje);
                    })
                    .catch((error) => {
                      console.log(error);
                    });
        })

        //Registrar un punto de cambio
        confirmChange.addEventListener('click', function(){
          let registroCambioForm = document.getElementById('registroCambioForm');
          let formDataPuntoCambio = new FormData;
          let  idEstacion = document.getElementById('id_estacion').value;

          if(!registroCambioForm.reportValidity()) return;

          if(document.getElementById('nominaPC').value == '' || document.getElementById('nominaPC').value == null){
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
          formDataPuntoCambio.append('nombrePC', $('#nominaPC').find('option:selected').text());
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

                        //getEstacion(idEstacion)

                         // Si no es arreglo, lo convertimos en uno
                          let estacionesA = Array.isArray(idEstacion) ? idEstacion : [idEstacion];
                          estacionesA.forEach(id => {getEstacion(id);});
                          mostrarTablaPersonal();
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
                          //getEstacion(idEstacion);

                          // Si no es arreglo, lo convertimos en uno
                          let estacionesA = Array.isArray(idEstacion) ? idEstacion : [idEstacion];
                          estacionesA.forEach(id => {getEstacion(id);});
                            mostrarTablaPersonal();
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

        //Detectar cuando se abra el modal de registrar operador
        document.getElementById('btnMenuRegistrar').addEventListener('click', function (){
          document.getElementById('fecharegistrar').value = (new Date()).toLocaleString('sv-SE').slice(0, 16);
        })

        //Generar fecha de registro de personal NAD en el formulario
        document.getElementById('btnMenuRegiswtroNAD').addEventListener('click', function(){
          document.getElementById('assignmentDatePNA').value = (new Date()).toLocaleString('sv-SE').slice(0, 16);
            document.getElementById('turnoAsignarPersonalDisponible').value =  $('#turnoLayout').val();
            mostrarTablaPNA();
        })

        $('#btnChipModalRegistrar').click(agregarChipTalla)
        
        btnAsignarOperador.addEventListener('click', asignarEstaciones);
        btnGuardarDisponible.addEventListener('click', registrarPNA); 
        btnRegistrarAsistencia.addEventListener('click', registrarAsistencia);
        btnMenuAsignarControlModal.addEventListener('click', function(){changeContent('ventanasModalPC','contAsignacion')});
        btnMenuAsignarControlModal.addEventListener('click', function(){changeContent('ventanasModalPC','contAsignacion')});
        btnInfoRPC.addEventListener('click', function(){changeContent('ventanasModalPC','contInfoEstacion')});
        btnRegistroPc.addEventListener('click', function(){changeContent('ventanasModalPC','contregistroCambioForm')});
        //btnLiberarPC.addEventListener('click', function(){changeContent('ventanasModalPC', 'contLiberarPC')});
        btnTablaPNA.addEventListener('click', function(){changeContent('ventanadModalPersonalNA', 'contTablaDisponibles')});
        btnRegistroPNA.addEventListener('click', function(){changeContent('ventanadModalPersonalNA', 'contRegistroPersonalDisponible')});
        btnMenuRegistroAs.addEventListener('click', generarTablaAsistencia);
        btnCambioTurno.addEventListener('click', cambiarTurno);
        fechaHistorial.addEventListener('change', getHistorialLayout);
        turnoHistorial.addEventListener('change', getHistorialLayout);
        btnEvaluacion.addEventListener('click', registrarEvaluacionPC);
        //btnHistorialLayout.addEventListener('click', getHistorialLayout);

        // SELECT → change
        $('#attendanceTable tbody').on('change', 'select', async function (e) {     
          
          const actualizado = await updateAsistencia(this, 'estatus'); 
          
          if(!actualizado) {
                console.log(actualizado)
              return;
          }

          console.log(actualizado)

          
          const estaciones = $(this).data('id_estacion'); //Si se queda informacion en el cache con esta funcion puede no actualizarse los valores de data
          //const estaciones = $(this).attr('data-id_estacion'); //como estos se acutalizan de manera dinamica es mejor usar attr


          if (estaciones === undefined || estaciones === null || estaciones === '') {
              return;
          }

          //Obtener los id separados por ","
          let idsEstaciones = String(estaciones).split(',').map(id => id.trim()).filter(id => id !== '' && id.toLowerCase() !== 'null' && id.toLowerCase() !== 'undefined');

            await Promise.all(
               idsEstaciones.map( function (idEstacion) { getEstacion(idEstacion)})
            );

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
                  $('#tableListadoPersonal').DataTable().rows({ page: 'all' }).every(function () {
                      $(this.node()).find('input[type="checkbox"]').prop('checked', true);
                  });
            }

          else {
                  seleccionadosGlobal = [];
                  $('#tableListadoPersonal').DataTable().rows({ page: 'all' }).every(function () {
                    $(this.node()).find('input[type="checkbox"]').prop('checked', false);
                  });
          }
        })

        // Delegación de eventos para checkboxes dinámicos
        $('#tableListadoPersonal tbody').on('change', 'input[type="checkbox"]', function(){
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

        //Evento para cambiar de pagina con el boton actualizar operaciones de la tabla de personal
        $('#tableListadoPersonal').on('click', '.tableBtnUpdateOperaciones', function () {
            changeContent('ventanasModalPersonal','ventanaActualizarOperaciones')
            
            //Obtener atributos data
              let nomina = $(this).data('nomina');
              let nombre = $(this).data('nombre');
              let selectRegistrarUpdate = document.getElementById('selectRegistrarUpdate');
              let formDataOperaciones = new FormData;
              formDataOperaciones.append('opcion', 30);
              formDataOperaciones.append('nomina', nomina);
              formDataOperaciones.append('codigoLinea', codigoLinea.value)

              document.getElementById('nominaModalRegistrarUpdate').textContent= nomina;
              document.getElementById('nombreModalRegistrarUpdate').value= nombre;

              //CARGAR LISTADO DE OPERACIONES EN EL SELECT
              selectRegistrarUpdate.innerHTML='';

              let noneA = document.createElement('option');
              noneA.value = '';
              noneA.textContent = 'Selecciona una estación...';
              selectRegistrarUpdate.appendChild(noneA);

              stationsData.forEach(station => {
                const option = document.createElement('option');
                option.value = station.id;   
                option.textContent = station.name; 
                selectRegistrarUpdate.appendChild(option);
              });
              //FIN LISTADO DE OPERACIONES

              //OBTENER LISTA DE OPERACIONES CARGADAS 
                fetch("../api/operacionesLinea.php", {
                        method: "POST",
                        body: formDataOperaciones,
                    })
                    .then((response) => response.text())
                    .then((data) => {
                        data= JSON.parse(data)
                        if(data.estatus=='ok'){
                          //Listar los chips

                           // Si no existe el arreglo, crear uno vacío
                            if (!Array.isArray(data.data)) {
                                data.data = [];
                            }

                            actualizarTallasAltaChipsUpdate(data.data, nomina);
                            console.log("datos operaciones registradas:",data)

                            //Usamos off para eliminar los eventos previos y evitar que se aniden
                             $('#btnChipModalRegistrarUpdate').off('click').on('click', function () {
                                agregarChipTallaUpdate(data.data, nomina);
                            });
                        }

                        else {
                            console.log(data.mensaje);
                            $('#operationsListContainerUpdate').text('SIN REGISTRO DE OPERACIONES LIBERADAS');
                        }

                    }).catch((error) => {
                        console.log(error);
                        $('#operationsListContainerUpdate').text('SIN REGISTRO DE OPERACIONES LIBERADAS');
                });
              //FIN PETICION
        })
        
        //Boton para volver a la tabla del modal de registro de personal
        $('#btnBackModalPersonal').on('click', function (){changeContent('ventanasModalPersonal', 'ventanaTablaListadoPersonal')})

        // Botón "+" para agregar en la tabla de lista de operaciones
        $('#modalListaOperaciones').on('click', '#operationsTable .btn-add-worker', function () {
          const estacionId = $(this).data('estacion');
          const $select = $(this).closest('td').find('select');
          const nomina = $select.val();
          const nombreMostrado = $select.find('option:selected').text();
          const nombre = nombreMostrado.includes('(') ? nombreMostrado.split(' (')[0] : nombreMostrado;

          if (!nomina || nomina === '') {
            alert('Por favor selecciona un operario válido.');
            return;
          }
 
          const formData = new FormData();
          formData.append('opcion', 3);
          formData.append('nomina', nomina);
          formData.append('estacion', estacionId);
          formData.append('nombre', nombre);
          formData.append('turno', $('#turnoLayout').val());
          formData.append('codigoLinea', codigoLinea.value);

            fetch("../api/operacionesLinea.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.estatus === 'ok') {
                   agregarPersonaOperacion(estacionId, nomina, nombre);
                   getEstacion(estacionId);
                   mostrarTablaPersonal();
                } else {
                    console.log(data)
                }
            })
            .catch(error => {
                console.error(error);
                alert('Error de conexión');
            });
        });

        // Icono ✖ para quitar en la tabla de lista de operaciones
        $('#modalListaOperaciones').on('click', '#operationsTable .remove-assigned', function (e) {
          e.stopPropagation();

          const estacionId = $(this).data('estacion');
          const nomina = $(this).data('nomina');

          const formData = new FormData();
          formData.append('opcion', 10);
          formData.append('nomina', nomina);
          formData.append('idEstacion', estacionId);
          formData.append('turno', $('#turnoLayout').val());

            fetch("../api/operacionesLinea.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.estatus === 'ok') {
                  removerPersonaOperacion($(this).closest('.chip-asignado'));
                  getEstacion(estacionId);
                  mostrarTablaPersonal();
                } else {
                    console.log(data)
                }
            })
            .catch(error => {
                console.error(error);
                alert('Error de conexión');
            });
        });

        //Evento boton eliminar listado de personal
        $('#tableListadoPersonal').on('click', '.btnElimPer', function () {
          const nomina = $(this).data('nomina');
          console.log(nomina)
          eliminarPersonal(nomina);
        });

      // Cargar las formas
      setTimeout(() => {
              loadShapesFromJSON();
      }, 500);

        applyZoom();
    });