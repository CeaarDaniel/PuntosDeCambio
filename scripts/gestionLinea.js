  let confirmChange = document.getElementById('confirmChange');
  let btnAsignarOperadorPC = document.getElementById('btnAsignarOperadorPC') //Boton de asignacion desde el modal de gestion pc
  let btnConfirmClose = document.getElementById('btnConfirmClose');
  let btnEvaluacion = document.getElementById('btnEvaluacion');
  let btnAsistencia = document.getElementById('btnAsistencia') //Boton para registrar una asistencia individual
  
  let tableListadoPersonal = document.getElementById('tableListadoPersonal');
  let btnRegistrarOperador = document.getElementById('btnRegistrarOperador') //Boton para registrar personal
  let btnRegistrarOperadorFile = document.getElementById('btnRegistrarOperadorFile');
  let seccionDetallePC = document.getElementById('seccionDetallePC');

  let btnAgregarPersonalP1 = document.getElementById('btnAgregarPersonalP1');
  let btnAgregarPersonalP2 = document.getElementById('btnAgregarPersonalP2');

  //BOTONES FLOTANTES
    let btnToggleToolsSidebar = document.getElementById('btnToggleToolsSidebar')
    let btncloseSidebar = document.getElementById('btncloseSidebar')
    let btnfloatingMenu = document.getElementById('btnfloatingMenu')
    let btnOpenToolsSidebarMobile = document.getElementById('btnOpenToolsSidebarMobile')
    let toolsSidebarBackdrop = document.getElementById('toolsSidebarBackdrop')
  //FIN BOTONES FLOTANTES

  let checkPadre = document.getElementById("checkPadre");
  var seleccionadosGlobal = [];
  var datosAsistenciaCheck; 
  let btnCambioTurno = document.getElementById('btnCambioTurno');
  let btnMenuRegistroAs = document.getElementById('btnMenuRegistroAs');
  let btnRegistrarAsistencia = document.getElementById('btnRegistrarAsistencia');
  let btnHistorialLayout = document.getElementById('btnHistorialLayout');

  let btnMenuAsignaciones = document.getElementById('btnMenuAsignaciones')
  //Arreglo para los chips
  let chipsEstaciones = [];
  let chipsEstacionesPC = [];

  //AREA DE DIBUJO DE LAS ESTACIONES
  let workspaceGrid;

  let nominaPC = document.getElementById('nominaPC'); 
 
 //INPUTS DE MODAL REGISTRAR OPERADOR
  let nominaModalRegistrar = document.getElementById('nominaModalRegistrar');
  let selectRegistrar = document.getElementById('selectRegistrar');
  let selectEstacionesPC = document.getElementById('selectEstacionesPC');

  //Inputs del formulario de PC de maquinaria
  let nominaResponsableMaq = document.getElementById('nominaResponsableMaq');
  let nombreResponsableMaq = document.getElementById('nombreResponsableMaq');

  //Inputs del modal consultar historial
  let fechaHistorial = document.getElementById('fechaHistorial');
  let turnoHistorial = document.getElementById('turnoHistorial');
  
  //Botones de menu del modal
      //Modal de registro de PC
        let btnInfoRPC = document.getElementById('btnInfoRPC');
        let btnRegistroPc = document.getElementById('btnRegistroPc');
        let btnRegistroMaquinaria = document.getElementById('btnRegistroMaquinaria');
        let btnRegistroMateriaPrima = document.getElementById('btnRegistroMateriaPrima');
        let btnRegistroMetodo = document.getElementById('btnRegistroMetodo');
        let btnMenuAsignarControlModal = document.getElementById('btnMenuAsignarControlModal');
  //Fin botones menu modal

  //IDENTIFICADOR DE LA LINEA
  let codigoLinea = document.getElementById('codigoLinea');

  // Datos para las estaciones
  var stationsData;

  //Contenedor para la seccion donde se muestra el PC
  let registroPC = document.getElementById('registroPC')

  //Opciones para cuando esta activo el punto de cambio
  let opcionActivPC = document.getElementById('opcionActivPC')
  
  //Inputs para el registro de evaluacion del PC
  let numeroDia = document.getElementById('numeroDiaEvaluacion')
  let numeroEvaluacion = document.getElementById('numeroEvaluacion')

//Boton volver en la seccion de detalledel modal de consulta de pc
 let btnVolverConsultaPC = document.getElementById('btnVolverConsultaPC');

  // Estado del workspace
  const workspaceState = {zoomLevel: 0.4};

  //Configurar y mostrar el modal reutilizable de mensajes de estatus
  function mostrarModalEstatus(
    tituloHeader = 'Información',
    clasesIconoHeader = 'bi-info-circle',
    mensaje = '',
    clasesIconoPrincipal = 'bi-info-circle text-primary',
    reabrirModalAnterior = true
  ) {
    const modalElement = document.getElementById('errorModal');
    const tituloElement = document.getElementById('errorModalTitle');
    const mensajeElement = document.getElementById('errorModalMessage');
    const iconoHeader = document.getElementById('errorModalHeaderIcon');
    const iconoPrincipal = document.getElementById('errorModalMainIcon');

    if (!modalElement || !tituloElement || !mensajeElement || !iconoHeader || !iconoPrincipal) {
      return;
    }

    const asignarClasesIcono = function(elemento, clasesBase, clasesPersonalizadas) {
      elemento.className = clasesBase;
      String(clasesPersonalizadas || '')
        .split(/\s+/)
        .filter(Boolean)
        .forEach(clase => elemento.classList.add(clase));
    };

    tituloElement.textContent = tituloHeader;
    mensajeElement.innerHTML = mensaje;
    asignarClasesIcono(iconoHeader, 'bi', clasesIconoHeader);
    asignarClasesIcono(iconoPrincipal, 'bi display-1', clasesIconoPrincipal);

    // Si el modal de estatus ya está abierto, solo actualizar su contenido.
    if (modalElement.classList.contains('show')) {
      return;
    }

    const modalVisible = document.querySelector('.modal.show:not(#errorModal)');
    let modalAnterior = null;

    if (modalVisible) {
      const instanciaAnterior = bootstrap.Modal.getInstance(modalVisible) ||
        new bootstrap.Modal(modalVisible);

      if (reabrirModalAnterior) {
        modalAnterior = {
          elemento: modalVisible,
          instancia: instanciaAnterior
        };
      }

      modalVisible.addEventListener('hidden.bs.modal', abrirModalEstatus, { once: true });
      instanciaAnterior.hide();
    } else {
      abrirModalEstatus();
    }

    function abrirModalEstatus() {
      let modalEstatus = bootstrap.Modal.getInstance(modalElement);

      if (!modalEstatus) {
        modalEstatus = new bootstrap.Modal(modalElement);
      }

      modalElement.addEventListener('hidden.bs.modal', function() {
        if (!modalAnterior || !modalAnterior.elemento.isConnected) {
          return;
        }

        const otroModalVisible = document.querySelector('.modal.show');

        if (!otroModalVisible) {
          modalAnterior.instancia.show();
        }
      }, { once: true });

      modalEstatus.show();
    }
  }

    // Sistema de drag & drop optimizado con soporte para modales
    class OptimizedDragSystem {
      init() {
        $('#workspaceGrid').on('click', '.station', async (e) => {
            const stationId = $(e.currentTarget).data('station-id');
            await getEstacion(stationId);
            const stationData = stationsData.find(s => s.id == stationId);
            if(stationData) {
                this.showStationModal(stationData);
            }
        });
      }
      
      //Actualizar y mostrar el modal de la estacion al abrirlo
      showStationModal(stationData) {
        //Botones del menú con un PC abierto en esta estación (se muestran en otro color)
        actualizarBotonesPCAbiertos(stationData);

        //Validar que la ruta de la imagen exista
        const img = document.getElementById('imgInfochangeControlModal');

        //Limpiar el chip de estaciones para cunado se inicializa o cambia de estación abierta
        chipsEstacionesPC = [];

        img.onerror = function() {
            this.onerror = null;
            this.src = '../img/personal/ok.jpg';
        };

        selectEstacionesPC.innerHTML='<option value="">Selecciona una estación...</option>';
        $('#alertChipsAgregarEstacionesPC').addClass('d-none');

        //Agregar los datos de la estación actual al los chips para forzar a que exista por lo menos el registro de la estación actual
        chipsEstacionesPC.push({ label: (stationData.name) ? (stationData.name).toUpperCase() : '', 
                                 value: stationData.id || '' ,
                                 remove : false,
                              });

        //Obtener todas las estaciones a las que esta asignada la persona en la estacion seleccionada que no sean un PC
        if(stationData.nomina && stationData.nomina != null){
          const estacionesFiltradas = stationsData.filter(
              estacion => (String(estacion.nomina) === String(stationData.nomina)  && estacion.idPC == null )
          );
        
        //Para registrar el punto de cambio, se agregan las estaciones asignadas del operador actual, excepto la estación actual.
        estacionesFiltradas.forEach(estacion => {
            if(stationData.id != estacion.id){
              chipsEstacionesPC.push({ label: (estacion.name) ? (estacion.name).toUpperCase() : '',
                  value: estacion.id || '' ,
                  remove : true,
              });   
            }
        });
      }

        actualizarEstacionChips();

        //PC de maquinaria: muestra el registro o el seguimiento según tenga un PC abierto
          cargarPCMaquinaria(stationData.id, stationData.name);

        document.getElementById('imgInfochangeControlModal').src= (stationData.nomina) ? `../img/personal/${stationData.nomina}.jpg` : `../img/personal/na.jpg`;
        document.getElementById('nombreEstacionModalPC').textContent = (stationData.name) ? (stationData.name).toUpperCase() : '';
        document.getElementById('idEstacionModalPC').value = stationData.id;
        document.getElementById('idTrabajadorAsignado').value = stationData.nomina || '';
        document.getElementById('alertPC').textContent = "";

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

        if (stationData.idPC || !stationData.nomina) {
            document.getElementById('btnRemoverTrabajadorPC').disabled = true;
        } 
        
        else {
            document.getElementById('btnRemoverTrabajadorPC').disabled = false;
        }

          getOperator(stationData.nomina, stationData.id, stationData.idPC || null)

          //Cargar el select del formulario del registro de asignacion
          setLiberados('nominaModalPC', codigoLinea.value, null, stationData.certification)

          //Cargar el select del formulario de registro del pc
          setLiberados('nominaPC', null, codigoLinea.value, stationData.certification)

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
      station.style.transform = `rotate(${ (stationData.rotate) ? stationData.rotate : 0}deg)`;
      station.setAttribute('data-station-id', stationData.id);
      station.setAttribute('data-idCertificacion', stationData.certification);

      //Íconos de PC 3M abiertos: van dentro del header, que crece solo si hay alguno (clase con-pc3m)
      const iconosPC3M = htmlIconosPC3M(stationData.pcs3M);

      station.innerHTML = `<div class="station-header py-2${iconosPC3M ? ' con-pc3m' : ''}">${iconosPC3M}</div>
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

    function formatearFecha(fecha){
       if (!fecha) return '';
                  
        const fechaF = new Date(fecha.replace(' ', 'T'));
        const dia = String(fechaF.getDate()).padStart(2, '0');
        const mes = String(fechaF.getMonth() + 1).padStart(2, '0');
        const anio = fechaF.getFullYear();
        const horas = String(fechaF.getHours()).padStart(2, '0');
        const minutos = String(fechaF.getMinutes()).padStart(2, '0');
        return `<span class="text-nowrap">${dia}/${mes}/${anio} ${horas}:${minutos}</span>`;
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

    //Funcion para obtener datos de una estacion en especifico, al hacer alguna modficicacion en algun registro que afecte a las estaciones 
    async function getEstacion(idEstacion = null, idPC = null){

      let formDataEstacion = new FormData();
      formDataEstacion.append('opcion', 15);

      //Solo mandar idEstacion si realmente tiene valor
      if (idEstacion !== null && idEstacion !== undefined && idEstacion !== '') {
          formDataEstacion.append('idEstacion', idEstacion);
      }

      // Solo mandar idPC si realmente tiene valor
      if (idPC !== null && idPC !== undefined && idPC !== '') {
          formDataEstacion.append('idPC', idPC);
      }

      formDataEstacion.append('turno', $('#turnoLayout').val());
       return fetch("../api/operacionesLinea.php", {
                  method: "POST",
                  body: formDataEstacion,
              })
              .then((response) => response.text())
              .then((data) => {
                    data= JSON.parse(data)
                    if(data.estatus=='ok'){

                      if (data.estatus === 'ok' && Array.isArray(data.estaciones)) {
                            data.estaciones.forEach(estacion => {
                                  actualizarEstacion(estacion.id, {
                                    'nomina': (estacion.nomina) ? estacion.nomina : null,
                                    'operator': (estacion.operator) ? estacion.operator : 'No asignado',
                                    'colorClass': estacion.colorClass,
                                    'status' : estacion.status,
                                    'idPC' : estacion.idPC,
                                    'estatusPC': estacion.estatusPC,
                                    'isCertificate' : estacion.isCertificate,
                                    'pcs3M' : estacion.pcs3M
                                  })
                            });
                      }

                      else {
                          console.error(data.mensaje || 'No se pudieron obtener las estaciones.');
                      }

                       return true;
                    }

                    else { 
                        console.log(data);
                        return false;
                    }
                }
              ).catch((error) => {
                  console.log(error);
                  return false;
                });
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

              //Íconos de PC 3M abiertos (dentro del header): se reemplazan para que aparezcan o
              //desaparezcan al registrar/cerrar; el header vuelve a su altura normal si no queda ninguno
              if (Array.isArray(newData.pcs3M)) {
                  const header = station.querySelector('.station-header');
                  const iconosPC3M = htmlIconosPC3M(newData.pcs3M);
                  header.innerHTML = iconosPC3M;
                  header.classList.toggle('con-pc3m', iconosPC3M !== '');
              }
        }

        else 
          console.warn(`No se encontró la estación ${stationId}`);

        /* Actualizar el arreglo*/ // Buscamos solo el objeto necesario
        let estation = stationsData.find(obj => String(obj.id) === String(stationId));
        //Mostrar el valor real del objeto estation

        if (estation) {
           (newData.operator) ? estation.operator = newData.operator : '';
          //estation.name =  (newData.name)'Estación en uso';
           (newData.colorClass) ? estation.colorClass = newData.colorClass : '';
           (newData.status) ? estation.status = newData.status : '';
            estation.nomina = (newData.nomina) ?? null;
            estation.idPC = (newData.idPC) ?? null;
            estation.estatusPC = (newData.estatusPC) ?? null;
            estation.isCertificate = newData.isCertificate
            if (Array.isArray(newData.pcs3M)) estation.pcs3M = newData.pcs3M;

            //Si es la estación abierta en el modal, actualiza el color de los botones de PC
            //(al registrar o cerrar un PC de cualquier tipo sin cerrar el modal)
            if (String(document.getElementById('idEstacionModalPC').value) === String(stationId)) {
                actualizarBotonesPCAbiertos(estation);
            }
        }
    }

    //Mostrar listado de estaciones registradas para colocar en los select como el de la asistencia individual
    function listarEstaciones(){
      const estacionAsistencia = document.getElementById('estacionAsistencia');
      estacionAsistencia.innerHTML='';

        //Agregar opcion vacia por defecto
          let noneA = document.createElement('option');
          noneA.value = '';
          noneA.textContent =  'Selecciona una estación...';
          estacionAsistencia.appendChild(noneA)

        stationsData.forEach(station => {
          const optionA = document.createElement('option');
          optionA.value = station.id;   
          optionA.textContent = station.name; 
          estacionAsistencia.appendChild(optionA)
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
                        pageLength: 50,
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
                            }
                          ]
                      });
                  }).catch((error) => {
                     $('#attendanceTable').DataTable().destroy();
                    console.log(error);
              });            
    }

    //Funcion para el registro de la asistencia
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
                mostrarModalEstatus(
                    'Asistencia ya registrada',
                    'bi-person-check-fill text-warning',
                    'La asistencia de hoy ya fue registrada. No es necesario registrarla nuevamente.',
                    'bi-info-circle-fill text-warning',
                    true
                );

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
                  mostrarModalEstatus(
                      'Asistencia registrada',
                      'bi-check-circle-fill text-success',
                       data.mensaje || 'La asistencia se registró correctamente.',
                      'bi-check-lg text-success',
                      true
                  );
                  
                  generarTablaAsistencia();

                  //Actualizar y guardar el layout solo cuando la asistencia se registro correctamente
                  return getEstaciones().then(() => {
                    saveLayout(true);
                  });
              }

              else if(data.estatus && data.estatus == 'error'){
                  mostrarModalEstatus(
                      'Error al registrar la asistencia',
                      'bi-x-octagon-fill text-danger',
                      data.mensaje || 'No fue posible registrar la asistencia. Inténtalo nuevamente.',
                      'bi-exclamation-circle-fill text-danger',
                      true
                  );
                  
                  return;
              }

              else {
                  mostrarModalEstatus(
                      'Error de conexión',
                      'bi-cloud-slash-fill text-danger',
                      'No fue posible recibir los datos del servidor.',
                      'bi-exclamation-circle-fill text-danger',
                      true
                  );

                  console.log(data);
                  return;
                }
            })
            .catch(error => {
                mostrarModalEstatus(
                    'Error de conexión',
                    'bi-cloud-slash-fill text-danger',
                    'No fue posible recibir los datos del servidor.',
                    'bi-exclamation-circle-fill text-danger',
                    true
                );

              console.log(error);
            });
    }

    //Funcion para cargar el archivo del listado del personal
    function fileInputOperatos(event){
      event.preventDefault();

      const inputListado = document.getElementById('ListadoPersonal');
      const archivo = inputListado.files[0];
      const turno = $('#turnoLayout').val();

      if (!archivo) {
        mostrarModalEstatus('Advertencia',
                            'bi-file-earmark-text-fill text-warning',
                            'Selecciona un archivo de texto con el listado de nóminas.',
                            'bi-exclamation-circle-fill text-warning',
                            true
                );
        return;
      }

      if (archivo.name.split('.').pop().toLowerCase() !== 'txt') {
        mostrarModalEstatus('Formato de archivo no válido',
                            'bi-file-earmark-x-fill text-warning',
                            'El archivo seleccionado debe tener la extensión .txt.',
                            'bi-exclamation-triangle-fill text-warning',
                            true);
        return;
      }

      if (!codigoLinea.value || !turno) {
        alert('No se encontró el código de línea o el turno seleccionado.');
        return;
      }

      const formDataListado = new FormData();
      formDataListado.append('opcion', '43');
      formDataListado.append('listadoPersonal', archivo);
      formDataListado.append('codigoLinea', codigoLinea.value);
      formDataListado.append('turno', turno);

      const contenidoBoton = btnRegistrarOperadorFile.innerHTML;
      btnRegistrarOperadorFile.disabled = true;
      btnRegistrarOperadorFile.setAttribute('aria-busy', 'true');
      btnRegistrarOperadorFile.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        Cargando...
      `;

      fetch('../api/operacionesLinea.php', {
        method: 'POST',
        body: formDataListado
      })
        .then(response => response.json())
        .then(data => {
          let mensaje = `<b>${data.mensaje}</b> <br>` || '<b>La carga del archivo ha finalizado.</b> <br>';

          if (Array.isArray(data.noInsertados) && data.noInsertados.length > 0) {
            const detalleNoInsertados = data.noInsertados
              .map(registro => `${registro.nomina}: ${registro.motivo}`)
              .join('<br>');

            mensaje += `<br> <b>Registros no insertados</b> (${data.noInsertados.length}): <br> ${detalleNoInsertados}`;
          }

          if (Array.isArray(data.imagenesNoCargadas) && data.imagenesNoCargadas.length > 0) {
            const detalleImagenes = data.imagenesNoCargadas
              .map(registro => `${registro.nomina}: ${registro.motivo}`)
              .join('<br>');

            mensaje += `<br> <b>Empleados insertados sin imagen:</b> <br> ${detalleImagenes}`;
          }

          if (data.estatus === 'ok') {

            mostrarModalEstatus(
                'Carga completada',
                'bi-file-earmark-check-fill text-success',
                mensaje,
                'bi-check-circle-fill text-success',
                false
            );

            inputListado.value = '';

            if (data.cantidadInsertados > 0) {
              mostrarTablaPersonal();
            }
          } 
          
        else {
              mostrarModalEstatus(
                  'Error al cargar el archivo',
                  'bi-file-earmark-x-fill text-danger',
                  data.mensaje || 'No fue posible procesar el archivo.',
                  'bi-exclamation-octagon-fill text-danger',
                  true
              );

              if (data.error) {
                  console.log(data.error);
              }
          }
        })
        .catch(error => {
          console.log(error);
          mostrarModalEstatus(
                'Error al cargar el archivo',
                'bi-file-earmark-x-fill text-danger',
                'Ocurrió un error al cargar el listado de personal.',
                'bi-exclamation-octagon-fill text-danger',
                true
            );
        })
        .finally(() => {
          btnRegistrarOperadorFile.disabled = false;
          btnRegistrarOperadorFile.removeAttribute('aria-busy');
          btnRegistrarOperadorFile.innerHTML = contenidoBoton;
        });
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

      return fetch("../api/operacionesLinea.php", {
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

            const responseData = JSON.parse(text);

            if (responseData.error) {
                 mostrarModalEstatus( 'Error en la operación',
                                      'bi-x-octagon-fill text-danger',
                                      'No fue posible completar la operación',
                                      'bi-exclamation-circle-fill text-danger',
                                      true);
                  console.log(responseData.mensaje) 
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
                  document.getElementById('countPDisponibles').textContent = data.resumen.disponibles;
                  document.getElementById('countPAsistencia').textContent = `% ${parseFloat(data.resumen.porcentajeA).toFixed(2)}`;
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
        document.getElementById('alertCambiarTurno').textContent = 'Selecciona al menos un registro para actualizar.';
        document.getElementById('alertCambiarTurno').classList.remove('d-none');
        return;
      }

      else{
        document.getElementById('alertCambiarTurno').textContent = '';
        document.getElementById('alertCambiarTurno').classList.add('d-none');
      }

      let turno = ($('#turnoLayout').val() == '1') ? '2' : '1';
      let fromDataCambioTurno = new FormData();
      fromDataCambioTurno.append('opcion', 19);
      fromDataCambioTurno.append('datosAsistenciaCheck', JSON.stringify(seleccionadosGlobal))
      fromDataCambioTurno.append('turnoCambio', turno)
      fromDataCambioTurno.append('codigoLinea', codigoLinea.value)
      fromDataCambioTurno.append('turnoActual', $('#turnoLayout').val())
      fromDataCambioTurno.append('changeAll', checkPadre.checked);

        //saveLayout(true);
          fetch("../api/operacionesLinea.php", {
                method: "POST",
                body: fromDataCambioTurno,
            })
            .then((response) => response.text())
            .then((data) => {
                    data= JSON.parse(data)
                    if(data.estatus == 'ok'){
                          //Ocurtar modal
                          getEstaciones();
                          mostrarTablaOperaciones();
                          mostrarTablaPersonal();
                          seleccionadosGlobal = [];
                        
                          mostrarModalEstatus(
                              'Operación completada',
                              'bi-check-circle-fill text-success',
                              `${data.mensaje}`,
                              'bi-check-lg text-success',
                              false
                          );
                      }

                    else{
                        mostrarModalEstatus(
                              'No se pudo completar la operación',
                              'bi-exclamation-octagon-fill text-danger',
                              `La información enviada no pudo procesarse. O se perdio la conexión con el servidor`,
                              'bi-exclamation-circle-fill text-danger',
                              true
                          );
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

                          if (parseInt(data.turno) === 2) {
                            fecha_inicio = new Date(fecha_inicio.getTime() - 8 * 60 * 60 * 1000);
                            ahora = new Date(ahora.getTime() - 8 * 60 * 60 * 1000);
                          }

                      //let diferencia_ms = ahora - fecha_inicio; // diferencia en milisegundos
                      //let dias = Math.floor(diferencia_ms / (1000 * 60 * 60 * 24));

                      let dias = Math.floor( 
                          (new Date(ahora).setHours(0, 0, 0, 0) - 
                           new Date(fecha_inicio).setHours(0, 0, 0, 0)) / (1000 * 60 * 60 * 24) + 1
                      );

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
      if(!fechaHistorial.value || !turnoHistorial.value  || !codigoLinea.value) return;

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
      if (btnEvaluacion.disabled) return;

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

          const contenidoBoton = btnEvaluacion.innerHTML;
          btnEvaluacion.disabled = true;
          btnEvaluacion.setAttribute('aria-busy', 'true');
          btnEvaluacion.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Registrando...
          `;
        
          fetch('../api/operacionesLinea.php', {
            method: 'POST',
            body: formData
          })
          .then(res => res.json())
          .then(data => {
              if(data.estatus == 'ok'){
                  mostrarModalEstatus(
                      'Operación completada',
                      'bi-check-circle-fill text-success',
                      `${data.mensaje}`,
                      'bi-check-lg text-success',
                      false
                  );

                  return getEvaluacion(idPC);
              }

              else {
                    mostrarModalEstatus('No se pudo completar la operación',
                                        'bi-exclamation-octagon-fill text-danger',
                                        data.mensaje || 'No fue posible hacer el registro de la evaluación',
                                        'bi-exclamation-circle-fill text-danger',
                                        true);
                    console.log(data);
              }
                
          })
          .catch(err => {
                mostrarModalEstatus(
                                  'No se pudo completar la operación',
                                  'bi-exclamation-octagon-fill text-danger',
                                  `La información enviada no pudo procesarse. O se perdio la conexión con el servidor`,
                                  'bi-exclamation-circle-fill text-danger',
                                  true
                                );
                console.error(err)
          })
          .finally(() => {
              btnEvaluacion.disabled = false;
              btnEvaluacion.removeAttribute('aria-busy');
              btnEvaluacion.innerHTML = contenidoBoton;
          });
      }
    }

    //Funcion para actualizar el layout al cambiar de turno registrar una asistencia volver a renderisar la estaciones y el svg
    function actualizarVistaLayout(){
         let turno = $("#turnoLayout").val()
         seleccionadosGlobal = [];

          if(turno){
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
            function agregarChipPersonal() {
              //Se modifican las cadenas a mayusculas para que sea indistinto valores como Xsd y xSD en la busqueda del valor en el arreglo tallasAlta
              if (!chipsEstaciones.some(item => ((item.value).toUpperCase()).trim() === ((selectRegistrar.value).toUpperCase()).trim()) ) { //Se usa tallasAlta some ya que el arreglo es un objeto de pares clave valor 
                    if (selectRegistrar.value.trim() !== '') { //Evalua si no esta vacio el input de la talla
                      chipsEstaciones.push({ label: $("#selectRegistrar option:selected").text(), 
                                             value: selectRegistrar.value 
                                          }); //Agregamos el valor del input sin los espacios que se hayan ingresado al final o al inicio (tallaLabel.value).trim()
                      selectRegistrar.value = ''; //Se limpia el input donde se agrega la talla
                      document.getElementById('alertChipsAgregarPersonal').classList.add('d-none')
                      actualizarChipsPersonal();

                  }
              }
                else {
                    document.getElementById('alertChipsAgregarPersonal').textContent = 'Esta operación ya ha sido agregada';
                    document.getElementById('alertChipsAgregarPersonal').classList.remove('d-none');
                }
            }

            // Función para remover un chip
            function removerChipPersonal(label) {
                chipsEstaciones.splice(label, 1);
                document.getElementById('alertChipsAgregarPersonal').classList.add('d-none');
                actualizarChipsPersonal();
            }

            // Función para actualizar los chips
            function actualizarChipsPersonal() { //Esta funcion actualiza la vista de las tallas despues de agregar o eliminar una talla
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
                        removerChipPersonal(label);
                    });
                });
            }
    //FIN FUNCION

    //FUNCION PARA LOS CHIPS DE ESTACIONES PC
        //Función para agregar una chip
            function agregarChipEstacion() {
              //Se modifican las cadenas a mayusculas para que sea indistinto valores como Xsd y xSD en la busqueda del valor en el arreglo
              if (!chipsEstacionesPC.some(item => ((item.value).toUpperCase()).trim() === ((selectEstacionesPC.value).toUpperCase()).trim()) ) {
                    if (selectEstacionesPC.value.trim() !== '') {
                      chipsEstacionesPC.push({label: $("#selectEstacionesPC option:selected").text(), 
                                             value: selectEstacionesPC.value,
                                             remove: true
                                          });

                      selectEstacionesPC.value = ''; //Se limpia el input donde se agrega la talla
                      document.getElementById('alertChipsAgregarEstacionesPC').classList.add('d-none')
                      actualizarEstacionChips();
                  }
              }

              else {
                    document.getElementById('alertChipsAgregarEstacionesPC').textContent = 'Esta estación ya ha sigo agregada';
                    document.getElementById('alertChipsAgregarEstacionesPC').classList.remove('d-none');
                }
            }

            // Función para remover un chip
            function removerChipEstacion(label) {
                chipsEstacionesPC.splice(label, 1);
                document.getElementById('alertChipsAgregarEstacionesPC').classList.add('d-none')
                actualizarEstacionChips();
            }

            // Función para actualizar los chips
            function actualizarEstacionChips() { //Esta funcion actualiza la vista de las tallas despues de agregar o eliminar una talla
                const container = document.getElementById('listEstacionesPC');
                container.innerHTML = chipsEstacionesPC.map((t, index) => `
                  <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-info bg-opacity-10 text-dark">
                    <span> ${t.label}</span>
                    <i class="bi bi-x-circle-fill ${(t.remove) ? 'remove-icon' : ''}" data-index = ${index} style="cursor: pointer; color: #dc3545;" title="Eliminar"></i>
                  </div>
                `).join(''); //Se agrega el indice (index) para poder eliminar mas facil lo elemetnos del arreglo

                const removeIcons = container.querySelectorAll('.remove-icon');

                removeIcons.forEach(icon => {
                    icon.addEventListener("click", function(event) {
                        event.preventDefault();
                        // Remover el chip correspondiente
                        const label = icon.getAttribute("data-index");
                        removerChipEstacion(label);
                    });
                });
            }
    //FIN FUNCION

    //FUNCION PARA LOS CHIPS DE ACTUALIZAR OPERACIONES
          //Agregar chip
           function agregarChipOperacion(estaciones, nomina = null) {
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
                                    $('#alertChipsAgregarOperacion').addClass('d-none');
                                    actualizarChipsOperacion(estaciones, nomina);
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
                } 
                
                else {
                    $('#alertChipsAgregarOperacion').removeClass('d-none')
                    $('#alertChipsAgregarOperacion').text('Esta operación ya ha sido agregada')
                }
            }

            // Función para remover un chip en la tabla de listado de presonal
            function removerChipOperacion(estaciones, index, nomina) {
                  $('#alertChipsAgregarOperacion').addClass('d-none')
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
                            actualizarChipsOperacion(estaciones, nomina);
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
            function actualizarChipsOperacion(data, nomina = null) { //Esta funcion actualiza la vista de las tallas despues de agregar o eliminar una talla
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
                        removerChipOperacion(data, index, nomina);
                    });
                });
            }
    //FIN FUNCION

    //Funciones para renderizar el detalle del PC
            function escapeHTML(value) {
              return String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
              }[char]));
            }

            function obtenerIniciales(nombre) {
              const partes = String(nombre || '').trim().split(/\s+/).filter(Boolean);
              if (!partes.length) return '--';
              return partes.slice(0, 2).map(p => p[0].toUpperCase()).join('');
            }

            function obtenerTextoTipoCambio(tipo) {
              if (String(tipo) === '2') return 'Programado';
              if (String(tipo) === '1') return 'Inesperado';
              if (String(tipo) === '3') return 'Otro';
              if (String(tipo) === '4') return 'Después de un largo periodo';
              if (String(tipo) === '5') return 'Nuevo/Sin experiencia';
              return 'Sin definir';
            }

            function obtenerEstatusPC(estatus) {
              switch (String(estatus)) {
                case '1':
                  return { texto: 'Activo', clase: 'bg-warning text-dark' };
                case '2':
                  return { texto: 'Eliminado / Cancelado', clase: 'bg-danger' };
                case '3':
                  return { texto: 'Finalizado', clase: 'bg-success' };
                default:
                  return { texto: 'Sin estatus', clase: 'bg-secondary' };
              }
            }

            function agruparEvaluacionesPorDia(evaluaciones = []) {
              const dias = { 1: [], 2: [], 3: [] };

              evaluaciones.forEach(ev => {
                const dia = Number(ev.numeroDia);
                if (!dias[dia]) dias[dia] = [];
                dias[dia].push(ev);
              });

              Object.values(dias).forEach(lista => {
                lista.sort((a, b) => Number(a.numeroEvaluacion) - Number(b.numeroEvaluacion));
              });

              return dias;
            }

            function renderMetrica(titulo, valor) {
              const cumple = String(valor) === '1';

              return `
                <div class="col-12 pc-metric-item">
                  <div class="border rounded-3 p-2 bg-white h-100 pc-metric">
                    <div class="small text-muted pc-metric-label">${titulo}
                        <span class="fw-semibold pc-metric-status ${cumple ? 'text-success' : 'text-danger'}">
                          ${cumple ? 'OK' : 'NG'}
                        </span>
                    </div>
                  </div>
                </div>
              `;
            }

            function renderEvaluacion(ev) {
              const comentarios = String(ev.comentarios || '').trim() || 'Sin comentarios.';

              return `
                <div class="border rounded-3 p-3 bg-light mb-3 pc-evaluation-card">
                  <div class="d-flex justify-content-between align-items-center mb-2 gap-2 flex-wrap pc-evaluation-heading">
                    <strong>Evaluación ${escapeHTML(ev.numeroEvaluacion || '-')}</strong>
                    <span class="badge bg-secondary">${formatearFecha(ev.fechaEvaluacion)}</span>
                  </div>

                  <div class="row g-2 mb-2">
                    ${renderMetrica('Cumple la operación', ev.metrica1)}
                    ${renderMetrica('Acabado del producto ', ev.metrica2)}
                    ${renderMetrica('Dificultad de la operación ', ev.metrica3)}
                  </div>

                  <div class="pc-evaluation-comments">
                    <div class="small text-muted mb-1">Comentarios</div>
                    <div class="small">${escapeHTML(comentarios)}</div>
                  </div>

                  ${ev.fechaRegistro ? `
                    <div class="alert alert-light border rounded-3 mt-3 mb-0 pc-registration-date">
                      <div class="small text-muted mb-1">Fecha de registro</div>
                      <div class="small fw-semibold">${formatearFecha(ev.fechaRegistro)}</div>
                    </div>
                  ` : ''}
                </div>
              `;
            }

            function renderBloqueDia(numeroDia, evaluacionesDia = []) {
              const cantidad = evaluacionesDia.length;

              return `
                <div class="col-12 col-xl-4">
                  <div class="card h-100 pc-day-card">
                    <div class="card-body pc-day-body">
                      <div class="d-flex justify-content-between align-items-center mb-3 pc-day-header">
                        <h5 class="mb-0">Día ${numeroDia}</h5>
                        <span class="badge bg-secondary pc-count-badge">${cantidad} evaluación${cantidad === 1 ? '' : 'es'}</span>
                      </div>

                      ${cantidad
                  ? evaluacionesDia.map(renderEvaluacion).join('')
                  : `
                          <div class="alert alert-light border mb-0 pc-empty-state">
                            No hay evaluaciones registradas para el día ${numeroDia}.
                          </div>
                        `
                }
                    </div>
                  </div>
                </div>
              `;
            }

            function renderIndicadorDias(evaluacionesPorDia) {
              return [1, 2, 3].map((dia, index) => {
                const cantidad = (evaluacionesPorDia[dia] || []).length;

                return `
                  ${index > 0 ? '<div class="step-connector"></div>' : ''}
                  <div class="step-indicator ${cantidad ? 'active' : ''}">
                    <div class="step-circle">${dia}</div>
                    <div class="step-label">
                      Día ${dia}<br>
                      <small>${cantidad} eval.</small>
                    </div>
                  </div>
                `;
              }).join('');
            }

            function renderDetallePC(respuesta) {
              const pc = respuesta.dataPC?.[0] || {};
              const estaciones = Array.isArray(respuesta.estaciones) ? respuesta.estaciones : [];
              const evaluaciones = Array.isArray(respuesta.evaluaciones) ? respuesta.evaluaciones : [];
              const cierre = Array.isArray(respuesta.cierre) && respuesta.cierre.length ? respuesta.cierre[0] : null;

              const estatusPC = obtenerEstatusPC(pc.estatusPC);
              const tipoCambioTexto = obtenerTextoTipoCambio(pc.tipo_cambio);
              const turnoTexto = pc.turno ? `Turno ${escapeHTML(pc.turno)}` : 'Sin turno';
              const nombre = String(pc.nombre || '').trim() || 'Sin nombre registrado';
              const nomina = String(pc.nomina || '').trim() || 'Sin nómina';
              const motivo = String(pc.motivo || '').trim() || 'Sin motivo / comentario registrado.';
              const iniciales = obtenerIniciales(nombre);
              const evaluacionesPorDia = agruparEvaluacionesPorDia(evaluaciones);
              const finMostrar = cierre?.fechaCierre || pc.fechaHora_fin || '';
              const badgeCierre = cierre?.fechaCierre ? 'bg-success' : 'bg-secondary';
              const textoCierre = cierre?.fechaCierre ? 'Registrado' : 'Pendiente';

              seccionDetallePC.innerHTML = `
                <div class="pc-detail-report">
                  <div class="mx-auto pc-report-container">
                    <div class="form-body px-0 mx-0 pc-report-body">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 pc-report-context">
                          <div>
                            <span class="pc-section-kicker">Resumen del registro</span>
                            <p class="pc-report-help mb-0">Información del punto de cambio seleccionado.</p>
                          </div>
                          <div class="d-flex flex-wrap gap-2 pc-meta-list" aria-label="Datos principales del punto de cambio">
                            <span class="badge bg-light text-dark pc-meta-badge">No. Control: ${escapeHTML(pc.no_controlCambio || 'Sin dato')}</span>
                            <span class="badge ${estatusPC.clase} pc-meta-badge">${estatusPC.texto}</span>
                            <span class="badge bg-light text-dark pc-meta-badge">Tipo: ${tipoCambioTexto}</span>
                            <span class="badge bg-light text-dark pc-meta-badge">${turnoTexto}</span>
                          </div>
                        </div>

                      <div class="row g-3 mb-4 pc-summary-grid">
                        <div class="col-12 col-sm-6 col-xl-3">
                          <div class="card h-100 pc-summary-card pc-summary-card-cyan">
                            <div class="card-body">
                              <div class="pc-summary-heading"><i class="bi bi-diagram-3" aria-hidden="true"></i><span class="text-muted small">Estaciones</span></div>
                              <div class="fs-4 fw-semibold pc-summary-value">${estaciones.length}</div>
                              <div class="small text-muted">Relacionadas al punto</div>
                            </div>
                          </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                          <div class="card h-100 pc-summary-card pc-summary-card-blue">
                            <div class="card-body">
                              <div class="pc-summary-heading"><i class="bi bi-clipboard2-check" aria-hidden="true"></i><span class="text-muted small">Evaluaciones</span></div>
                              <div class="fs-4 fw-semibold pc-summary-value">${evaluaciones.length}</div>
                              <div class="small text-muted">Registradas</div>
                            </div>
                          </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                          <div class="card h-100 pc-summary-card pc-summary-card-start">
                            <div class="card-body">
                              <div class="pc-summary-heading"><i class="bi bi-calendar-event" aria-hidden="true"></i><span class="text-muted small">Inicio</span></div>
                              <div class="fw-semibold pc-summary-date">${pc.fechaHora_inicio ? formatearFecha(pc.fechaHora_inicio) : 'Sin registro'}</div>
                            </div>
                          </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                          <div class="card h-100 pc-summary-card pc-summary-card-end">
                            <div class="card-body">
                              <div class="pc-summary-heading"><i class="bi bi-calendar-check" aria-hidden="true"></i><span class="text-muted small">Fin / cierre</span></div>
                              <div class="fw-semibold pc-summary-date">${finMostrar ? formatearFecha(finMostrar) : 'Sin registro'}</div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row g-3 mb-4 pc-overview-grid">
                        <div class="col-12 col-xl-12">
                          <div class="info-card h-100 mb-0 pc-info-panel">
                            <div class="info-title">
                              <i class="bi bi-info-circle"></i>
                              Información general
                            </div>

                            <div class="row g-3">
                              <div class="col-md-6">
                                <div class="info-item">
                                  <span class="info-label">No. Control</span>
                                  <span class="info-value">${escapeHTML(pc.no_controlCambio || 'Sin dato')}</span>
                                </div>
                                <div class="info-item">
                                  <span class="info-label">Nómina</span>
                                  <span class="info-value">${escapeHTML(nomina)}</span>
                                </div>
                                <div class="info-item">
                                  <span class="info-label">Nombre</span>
                                  <span class="info-value">${escapeHTML(nombre)}</span>
                                </div>
                                <div class="info-item">
                                  <span class="info-label">Tipo de cambio</span>
                                  <span class="info-value">${tipoCambioTexto}</span>
                                </div>
                              </div>

                              <div class="col-md-6">
                                <div class="info-item">
                                  <span class="info-label">Fecha / hora inicio</span>
                                  <span class="info-value">${pc.fechaHora_inicio ? formatearFecha(pc.fechaHora_inicio) : 'Sin registro'}</span>
                                </div>
                                <div class="info-item">
                                  <span class="info-label">Fecha / hora fin</span>
                                  <span class="info-value">${pc.fechaHora_fin ? formatearFecha(pc.fechaHora_fin) : 'Sin registro'}</span>
                                </div>
                                <div class="info-item">
                                  <span class="info-label">Turno</span>
                                  <span class="info-value">${turnoTexto}</span>
                                </div>
                                <div class="info-item">
                                  <span class="info-label">Estatus</span>
                                  <span class="info-value">${estatusPC.texto}</span>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="col-12 col-xl-12">
                          <div class="card mb-3 pc-side-card mx-1">
                            <div class="card-body">
                              <h5 class="section-title mb-3">
                                <i class="bi bi-person-badge"></i>
                                Responsable
                              </h5>

                              <div class="employee-preview mt-0">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-semibold pc-avatar">
                                  ${escapeHTML(iniciales)}
                                </div>
                                <div class="employee-info">
                                  <p class="employee-name mb-0">${escapeHTML(nombre)}</p>
                                  <p class="employee-role mb-0">Nómina ${escapeHTML(nomina)}</p>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="card pc-side-card mx-1">
                            <div class="card-body">
                              <h5 class="section-title mb-3">
                                <i class="bi bi-chat-left-text"></i>
                                Motivo
                              </h5>

                              <div class="alert alert-light border rounded-3 mb-0 pc-reason-box">
                                ${escapeHTML(motivo)}
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="form-section pc-content-section">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3 pc-section-header">
                          <div class="pc-section-heading-copy">
                            <h5 class="section-title mb-1">
                              <i class="bi bi-diagram-3"></i>
                              Estaciones relacionadas
                            </h5>
                            <p class="pc-section-help mb-0">Ubicaciones vinculadas con este punto de cambio.</p>
                          </div>
                          <span class="badge bg-secondary pc-count-badge">${estaciones.length} estación${estaciones.length === 1 ? '' : 'es'}</span>
                        </div>

                        <div class="row g-3">
                          ${estaciones.length
                  ? estaciones.map((estacion, index) => `
                                  <div class="col-12 col-md-6 col-xl-4">
                                    <div class="card h-100 pc-station-card">
                                      <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                                          <h6 class="mb-0">${escapeHTML(estacion.nombre_estacion || `Estación ${estacion.idE || index + 1}`)}</h6>
                                          <span class="badge bg-light text-dark border">ID ${escapeHTML(estacion.idE || '-')}</span>
                                        </div>
                                        <div class="small text-muted">
                                          Estacion vinculada al punto de cambio ${escapeHTML(pc.no_controlCambio || '')}.
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                `).join('')
                  : `
                                <div class="col-12">
                                  <div class="alert alert-light border mb-0 pc-empty-state">
                                    No hay estaciones registradas para este punto de cambio.
                                  </div>
                                </div>
                              `
                }
                        </div>
                      </div>

                      <div class="form-section pc-content-section">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3 pc-section-header">
                          <div class="pc-section-heading-copy">
                            <h5 class="section-title mb-1">
                              <i class="bi bi-clipboard2-pulse"></i>
                              Seguimiento de evaluaciones
                            </h5>
                            <p class="pc-section-help mb-0">Resultados registrados durante los tres días de seguimiento.</p>
                          </div>
                          <span class="badge bg-primary pc-count-badge">${evaluaciones.length} evaluación${evaluaciones.length === 1 ? '' : 'es'}</span>
                        </div>

                        <div class="progress-container rounded-3 p-3 mb-4 pc-day-progress">
                          <div class="d-flex align-items-start justify-content-between gap-2 pc-progress-track">
                            ${renderIndicadorDias(evaluacionesPorDia)}
                          </div>
                        </div>

                        <div class="row g-3">
                          ${renderBloqueDia(1, evaluacionesPorDia[1])}
                          ${renderBloqueDia(2, evaluacionesPorDia[2])}
                          ${renderBloqueDia(3, evaluacionesPorDia[3])}
                        </div>
                      </div>

                      <div class="form-section border-0 mb-2 pb-1 pc-content-section pc-closing-section">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3 pc-section-header">
                          <div class="pc-section-heading-copy">
                            <h5 class="section-title mb-1">
                              <i class="bi bi-check2-circle"></i>
                              Cierre del Punto de Cambio
                            </h5>
                            <p class="pc-section-help mb-0">Fecha y observaciones con las que concluyó el registro.</p>
                          </div>
                          <span class="badge ${badgeCierre} pc-count-badge">${textoCierre}</span>
                        </div>

                        ${cierre
                  ? `
                              <div class="row g-3">
                                <div class="col-12 col-lg-4">
                                  <div class="info-card h-100 mb-0 pc-closing-card">
                                    <div class="info-title">
                                      <i class="bi bi-calendar-check"></i>
                                      Fecha de cierre
                                    </div>
                                    <div class="fw-semibold">${formatearFecha(cierre.fechaCierre)}</div>
                                  </div>
                                </div>

                                <div class="col-12 col-lg-8">
                                  <div class="info-card h-100 mb-0 pc-closing-card">
                                    <div class="info-title">
                                      <i class="bi bi-chat-square-text"></i>
                                      Comentarios de cierre
                                    </div>
                                    <div class="text-muted">
                                      ${escapeHTML(String(cierre.comentarios || '').trim() || 'Sin comentarios de cierre.')}
                                    </div>
                                  </div>
                                </div>
                              </div>
                            `
                  : `
                              <div class="alert alert-warning mb-0 pc-empty-state">
                                Este punto de cambio aún no tiene registro de cierre.
                              </div>
                            `
                }
                      </div>

                    </div>
                  </div>
                </div>
              `;
            }

            function renderDetallePCError(mensaje) {
              seccionDetallePC.innerHTML = `
                <div class="pc-feedback-wrap">
                  <div class="alert alert-danger mb-0 pc-feedback" role="alert">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    <span>${escapeHTML(mensaje)}</span>
                  </div>
                </div>
              `;
            }

            async function cargarDetallePC(noControlCambio) {
              try {
                seccionDetallePC.innerHTML = `
                  <div class="pc-feedback-wrap">
                    <div class="alert alert-info mb-0 pc-feedback" role="status" aria-live="polite">
                      <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                      <span>Cargando detalle del punto de cambio...</span>
                    </div>
                  </div>
                `;

                const formData = new FormData();
                formData.append('opcion', 42);
                formData.append('idPC', noControlCambio);

                const response = await fetch('../api/operacionesLinea.php', {
                  method: 'POST',
                  body: formData
                });

                //console.log(response)

                if (!response.ok) {
                  throw new Error(`Error HTTP ${response.status}`);
                }

                const respuesta = await response.json();

                if (respuesta.estatus !== 'ok') {
                  throw new Error('El servidor no devolvió un resultado válido.');
                }

                renderDetallePC(respuesta);
              } catch (error) {
                console.error(error);
                renderDetallePCError(error.message || 'No fue posible cargar el detalle.');
              }
            }

            //Detalle de un PC 3M (maquinaria, materia prima o método), opción 52: registro, planeación,
            //implementación, evaluaciones (una por día) y cierre. Usa las mismas clases que el de mano de obra.
            function renderDetallePC3M(datos) {
              const pc = datos.pc || {};
              const estaciones = Array.isArray(datos.estaciones) ? datos.estaciones : [];
              const planeacion = datos.planeacion || null;
              const implementacion = datos.implementacion || null;
              const documentos = implementacion && Array.isArray(implementacion.documentos) ? implementacion.documentos : [];
              const evaluaciones = Array.isArray(datos.evaluaciones) ? datos.evaluaciones : [];
              const requeridas = Number(datos.evaluaciones_requeridas) || 3;
              const cierre = datos.cierre || null;

              const estatusPC = obtenerEstatusPC(pc.estatusPC);
              const tipo = TIPOS_PC_3M[String(pc.clave4M || '').trim()] || null;
              const nombreTipo = tipo ? tipo.nombre : String(pc.tipo4M || '').toLowerCase();
              const turnoTexto = pc.turno ? `Turno ${escapeHTML(pc.turno)}` : 'Sin turno';
              const nombre = String(pc.nombre || '').trim() || 'Sin nombre registrado';
              const nomina = String(pc.nomina || '').trim() || 'Sin nómina';
              const finMostrar = cierre?.fechaCierre || pc.fechaHora_fin || '';
              const estacionesTexto = estaciones.map(e => e.nombre_estacion || `Estación ${e.idE}`).join(', ');

              //Fecha sin hora (AAAA-MM-DD) a DD/MM/AAAA sin pasar por Date, para no desfasar el día por zona horaria
              const fechaCorta = (texto) => {
                const partes = String(texto || '').split('-');
                return partes.length === 3 ? `${partes[2]}/${partes[1]}/${partes[0]}` : 'Sin registro';
              };

              const dato = (etiqueta, valor) => `
                <div class="info-item">
                  <span class="info-label">${etiqueta}</span>
                  <span class="info-value">${escapeHTML(String(valor ?? '').trim() || 'Sin registro')}</span>
                </div>`;

              //Igual que dato(), para valores que ya vienen como HTML seguro (fechas de formatearFecha)
              const datoHTML = (etiqueta, html) => `
                <div class="info-item">
                  <span class="info-label">${etiqueta}</span>
                  <span class="info-value">${html || 'Sin registro'}</span>
                </div>`;

              const encabezadoSeccion = (icono, titulo, ayuda, claseBadge, textoBadge) => `
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3 pc-section-header">
                  <div class="pc-section-heading-copy">
                    <h5 class="section-title mb-1"><i class="bi ${icono}"></i> ${titulo}</h5>
                    <p class="pc-section-help mb-0">${ayuda}</p>
                  </div>
                  <span class="badge ${claseBadge} pc-count-badge">${textoBadge}</span>
                </div>`;

              const sinRegistro = (texto, clase = 'alert-light border') =>
                `<div class="alert ${clase} mb-0 pc-empty-state">${texto}</div>`;

              const tarjetaResumen = (claseColor, icono, titulo, valorHTML, nota) => `
                <div class="col-12 col-sm-6 col-xl-3">
                  <div class="card h-100 pc-summary-card ${claseColor}">
                    <div class="card-body">
                      <div class="pc-summary-heading"><i class="bi ${icono}" aria-hidden="true"></i><span class="text-muted small">${titulo}</span></div>
                      ${valorHTML}
                      ${nota ? `<div class="small text-muted">${nota}</div>` : ''}
                    </div>
                  </div>
                </div>`;

              //Un bloque por día con sus 5 puntos OK / NG (METRICAS_EVAL_MAQ)
              const bloquesEvaluacion = Array.from({ length: requeridas }, (_, indiceDia) => {
                const dia = indiceDia + 1;
                const evaluacion = evaluaciones.find(e => Number(e.numeroDia) === dia);

                return `
                  <div class="col-12 col-lg-4">
                    <div class="card h-100 pc-station-card">
                      <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2 gap-2">
                          <h6 class="mb-0">Día ${dia}</h6>
                          <span class="badge ${evaluacion ? 'bg-success' : 'bg-secondary'}">${evaluacion ? formatearFecha(evaluacion.fechaEvaluacion) : 'Pendiente'}</span>
                        </div>
                        ${evaluacion
                          ? `<div class="row g-2">${METRICAS_EVAL_MAQ.map((titulo, indice) => renderMetrica(titulo, evaluacion['metrica' + (indice + 1)])).join('')}</div>
                             ${evaluacion.comentarios ? `<div class="small text-muted mt-2">${escapeHTML(evaluacion.comentarios)}</div>` : ''}`
                          : '<div class="small text-muted">Sin evaluación registrada.</div>'}
                      </div>
                    </div>
                  </div>`;
              }).join('');

              seccionDetallePC.innerHTML = `
                <div class="pc-detail-report">
                  <div class="mx-auto pc-report-container">
                    <div class="form-body px-0 mx-0 pc-report-body">
                      <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 pc-report-context">
                        <div>
                          <span class="pc-section-kicker">Punto de cambio de ${escapeHTML(nombreTipo)}</span>
                          <p class="pc-report-help mb-0">Información registrada en su flujo: planeación, implementación, evaluación y cierre.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2 pc-meta-list" aria-label="Datos principales del punto de cambio">
                          <span class="badge bg-light text-dark pc-meta-badge">No. Control: ${escapeHTML(pc.no_controlCambio || 'Sin dato')}</span>
                          <span class="badge ${estatusPC.clase} pc-meta-badge">${estatusPC.texto}</span>
                          <span class="badge bg-light text-dark pc-meta-badge">Tipo: ${escapeHTML(pc.tipo4M || nombreTipo)}</span>
                          <span class="badge bg-light text-dark pc-meta-badge">${turnoTexto}</span>
                        </div>
                      </div>

                      <div class="row g-3 mb-4 pc-summary-grid">
                        ${tarjetaResumen('pc-summary-card-cyan', 'bi-file-earmark-check', 'Documentos',
                          `<div class="fs-4 fw-semibold pc-summary-value">${documentos.length}</div>`, 'Marcados en la implementación')}
                        ${tarjetaResumen('pc-summary-card-blue', 'bi-clipboard2-check', 'Evaluaciones',
                          `<div class="fs-4 fw-semibold pc-summary-value">${evaluaciones.length} de ${requeridas}</div>`, 'Una por día')}
                        ${tarjetaResumen('pc-summary-card-start', 'bi-calendar-event', 'Inicio',
                          `<div class="fw-semibold pc-summary-date">${pc.fechaHora_inicio ? formatearFecha(pc.fechaHora_inicio) : 'Sin registro'}</div>`, '')}
                        ${tarjetaResumen('pc-summary-card-end', 'bi-calendar-check', 'Fin / cierre',
                          `<div class="fw-semibold pc-summary-date">${finMostrar ? formatearFecha(finMostrar) : 'Sin registro'}</div>`, '')}
                      </div>

                      <div class="row g-3 mb-4 pc-overview-grid">
                        <div class="col-12">
                          <div class="info-card h-100 mb-0 pc-info-panel">
                            <div class="info-title"><i class="bi bi-info-circle"></i> Información general</div>
                            <div class="row g-3">
                              <div class="col-md-6">
                                ${dato('No. Control', pc.no_controlCambio)}
                                ${dato('Tipo de punto de cambio', pc.tipo4M || nombreTipo)}
                                ${dato('Línea', pc.nombre_linea || pc.codigo_linea)}
                                ${dato(estaciones.length === 1 ? 'Estación' : 'Estaciones', estacionesTexto)}
                              </div>
                              <div class="col-md-6">
                                ${datoHTML('Fecha / hora inicio', pc.fechaHora_inicio ? formatearFecha(pc.fechaHora_inicio) : '')}
                                ${datoHTML('Fecha / hora fin', pc.fechaHora_fin ? formatearFecha(pc.fechaHora_fin) : '')}
                                ${datoHTML('Turno de registro', turnoTexto)}
                                ${datoHTML('Estatus', escapeHTML(estatusPC.texto))}
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="col-12">
                          <div class="card pc-side-card mx-1">
                            <div class="card-body">
                              <h5 class="section-title mb-3"><i class="bi bi-person-badge"></i> Personal responsable</h5>
                              <div class="employee-preview mt-0">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-semibold pc-avatar">
                                  ${escapeHTML(obtenerIniciales(nombre))}
                                </div>
                                <div class="employee-info">
                                  <p class="employee-name mb-0">${escapeHTML(nombre)}</p>
                                  <p class="employee-role mb-0">Nómina ${escapeHTML(nomina)}</p>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="form-section pc-content-section">
                        ${encabezadoSeccion('bi-clipboard-check', 'Planeación', 'Producto o material, cambio a realizar, equipo y tipo de cambio.',
                          planeacion ? 'bg-success' : 'bg-secondary', planeacion ? 'Registrada' : 'Pendiente')}
                        ${planeacion
                          ? `<div class="row g-3">
                               <div class="col-md-6">
                                 ${dato('Producto / material', planeacion.nombre_producto)}
                                 ${dato('Equipo', planeacion.equipo)}
                                 ${dato('Tipo de cambio', planeacion.tipo_cambio)}
                                 ${planeacion.otro_detalle ? dato('Especificación del tipo de cambio', planeacion.otro_detalle) : ''}
                               </div>
                               <div class="col-md-6">
                                 <span class="info-label d-block mb-1">Detalles del cambio</span>
                                 <div class="alert alert-light border rounded-3 mb-0 pc-reason-box">${escapeHTML(planeacion.detalles_cambio || 'Sin detalles registrados.')}</div>
                               </div>
                             </div>`
                          : sinRegistro('Este punto de cambio no tiene planeación registrada.')}
                      </div>

                      <div class="form-section pc-content-section">
                        ${encabezadoSeccion('bi-tools', 'Implementación', 'Verificación de documentos, responsable y fecha compromiso.',
                          implementacion ? 'bg-success' : 'bg-secondary', implementacion ? 'Registrada' : 'Pendiente')}
                        ${implementacion
                          ? `<div class="row g-3">
                               <div class="col-md-6">
                                 <span class="info-label d-block mb-2">Verificación de documentos</span>
                                 <div class="d-flex flex-wrap gap-2">
                                   ${documentos.length
                                     ? documentos.map(documento => `<span class="badge bg-light text-dark border px-3 py-2">${escapeHTML(documento.nombre)}</span>`).join('')
                                     : '<span class="text-muted small">Sin documentos marcados.</span>'}
                                 </div>
                                 ${implementacion.otro_detalle ? `<div class="mt-3">${dato('Otro documento', implementacion.otro_detalle)}</div>` : ''}
                               </div>
                               <div class="col-md-6">
                                 ${dato('Responsable', `${implementacion.nombre_responsable || ''} (${implementacion.nomina_responsable || ''})`)}
                                 ${dato('Fecha compromiso', fechaCorta(implementacion.fecha_compromiso))}
                                 ${datoHTML('Fecha de registro', implementacion.fecha_registro ? formatearFecha(implementacion.fecha_registro) : '')}
                               </div>
                             </div>`
                          : sinRegistro('Este punto de cambio no tiene implementación registrada.')}
                      </div>

                      <div class="form-section pc-content-section">
                        ${encabezadoSeccion('bi-clipboard2-pulse', 'Seguimiento de evaluaciones', 'Resultado OK / NG de cada punto, una evaluación por día.',
                          'bg-primary', `${evaluaciones.length} de ${requeridas}`)}
                        <div class="row g-3">${bloquesEvaluacion}</div>
                      </div>

                      <div class="form-section border-0 mb-2 pb-1 pc-content-section pc-closing-section">
                        ${encabezadoSeccion('bi-check2-circle', 'Cierre del Punto de Cambio', 'Fecha y observaciones con las que concluyó el registro.',
                          cierre ? 'bg-success' : 'bg-secondary', cierre ? 'Registrado' : 'Pendiente')}
                        ${cierre
                          ? `<div class="row g-3">
                               <div class="col-12 col-lg-4">
                                 <div class="info-card h-100 mb-0 pc-closing-card">
                                   <div class="info-title"><i class="bi bi-calendar-check"></i> Fecha de cierre</div>
                                   <div class="fw-semibold">${formatearFecha(cierre.fechaCierre)}</div>
                                 </div>
                               </div>
                               <div class="col-12 col-lg-8">
                                 <div class="info-card h-100 mb-0 pc-closing-card">
                                   <div class="info-title"><i class="bi bi-chat-square-text"></i> Comentarios de cierre</div>
                                   <div class="text-muted">${escapeHTML(String(cierre.comentarios || '').trim() || 'Sin comentarios de cierre.')}</div>
                                 </div>
                               </div>
                             </div>`
                          : sinRegistro('Este punto de cambio aún no tiene registro de cierre.', 'alert-warning')}
                      </div>
                    </div>
                  </div>
                </div>
              `;
            }

            //Carga el detalle de un PC 3M (opción 52) en la misma ventana de detalle del listado
            async function cargarDetallePC3M(idPC) {
              try {
                seccionDetallePC.innerHTML = `
                  <div class="pc-feedback-wrap">
                    <div class="alert alert-info mb-0 pc-feedback" role="status" aria-live="polite">
                      <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                      <span>Cargando detalle del punto de cambio...</span>
                    </div>
                  </div>
                `;

                const formData = new FormData();
                formData.append('opcion', 52);
                formData.append('idPC', idPC);

                const response = await fetch('../api/operacionesLinea.php', {
                  method: 'POST',
                  body: formData
                });

                if (!response.ok) {
                  throw new Error(`Error HTTP ${response.status}`);
                }

                const respuesta = await response.json();

                if (respuesta.estatus !== 'ok') {
                  throw new Error(respuesta.mensaje || 'El servidor no devolvió un resultado válido.');
                }

                renderDetallePC3M(respuesta.data);
              } catch (error) {
                console.error(error);
                renderDetallePCError(error.message || 'No fue posible cargar el detalle.');
              }
            }
    //FIN Funciones para renderizar el detalle del PC

    //Funcion para cargar el listado de personal
    function mostrarTablaPersonal(){
          let formDataPersonal = new FormData 
          formDataPersonal.append('codigoLinea', codigoLinea.value)
          formDataPersonal.append('turno', $('#turnoLayout').val())
          formDataPersonal.append('opcion', 29)
          document.getElementById('checkPadre').checked = false
          seleccionadosGlobal = [];

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
                  let selectHtml = `<select class="w-100 form-select form-select-sm bg-info bg-opacity-10 border-0 rounded-3 select-operaciones"
                                      data-estacion="${rowData.id_estacion}">`;
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
                    <div id="contenedorAlertTableOperacion">
                      ${selectHtml}
                      ${btnHtml}
                      <div class="text-danger fw-bold w-100 d-none alerTableOperaciones"></div>
                    </div>`;
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

    //Coloca el rango de fechas del listado de PC en el mes actual (primer y último día)
    function inicializarFiltrosFechaPC() {
      const hoy = new Date();
      document.getElementById('filtroPCDesde').value = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toLocaleDateString('sv-SE');
      document.getElementById('filtroPCHasta').value = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0).toLocaleDateString('sv-SE');
    }

    let tokenListadoPC = 0; //Descarta respuestas atrasadas si se cambian los filtros rápidamente

    //Listado de puntos de cambio (changePointsModal) filtrado por tipo 4M y rango de fechas de inicio
    function mostrarTablaPC(){
      const campoDesde = document.getElementById('filtroPCDesde');
      const campoHasta = document.getElementById('filtroPCHasta');

      //Si falta alguna fecha se vuelve al mes actual
      if (!campoDesde.value || !campoHasta.value) {
        inicializarFiltrosFechaPC();
      }

      //El rango debe ser válido: se avisa junto al campo en vez de consultar
      campoHasta.setCustomValidity(campoDesde.value > campoHasta.value ? 'La fecha "Hasta" debe ser igual o posterior a "Desde".' : '');
      if (!campoHasta.reportValidity()) return;

      //Siempre hay un tipo seleccionado (no existe "Todos"); por defecto mano de obra
      const filtroTipo = document.querySelector('input[name="filtroTipoPC"]:checked');
      const tipoConsulta = filtroTipo ? filtroTipo.value : 'MO';
      const tokenActual = ++tokenListadoPC;

      let formData= new FormData();
      formData.append('codigoLinea', codigoLinea.value);
      formData.append('opcion', 38);
      formData.append('turno', $('#turnoLayout').val());
      formData.append('clave4M', tipoConsulta);
      formData.append('fechaInicio', campoDesde.value);
      formData.append('fechaFin', campoHasta.value);

      fetch("../api/operacionesLinea.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.text())
        .then((data) => {
          if (tokenActual !== tokenListadoPC) return;

          data = JSON.parse(data);

          if (data.estatus !== 'ok') {
            console.log(data);
            return;
          }

          const response = data.data;

          // Inicializar DataTable listado de puntos de cambio
          $('#tablePC').DataTable().destroy();
          
          const table = $('#tablePC').DataTable({
            data: response,
            columns: [
              { 
                data: 'no_controlCambio',
                className: 'px-4 py-3 fw-medium',
                orderable: false
              },
              { 
                data: 'nomina',
                className: 'px-4 py-3 fw-medium'
              },
              { 
                data: 'nombre',
                width: '200px',
                className: 'px-4 py-3',
                render: function(data) {
                  return `<span class="fw-semibold">${data ?? ''}</span>`;
                }
              },
              {
                data: 'motivo',
                className: 'px-4 py-3',
                //En los PC 3M el motivo son los detalles del cambio registrados en la planeación
                render: function(data, tipo, fila) {
                  return `<span class="text-wrap">${escapeHTML(data ?? fila.detalles_cambio ?? '')}</span>`;
                }
              },
              {
                data: 'fechaHora_inicio',
                className: 'px-4 py-3',
                orderable: false,
                render: function(data) {
                  return formatearFecha(data);
                }
              },
              { 
                data: 'fechaHora_fin',
                className: 'px-4 py-3',
                orderable: false,
                render: function(data) {
                  return formatearFecha(data);
                }
              },
              {
                data: 'tipo_cambio',
                className: 'px-4 py-3',
                render: function(data, tipo, fila) {
                  let badge = '';

                  //PC 3M: tipo de cambio de su planeación (SPC_TIPO_3M)
                  if (tipoConsulta !== 'MO') {
                    return fila.tipo_cambio_3m
                      ? `<span class="badge bg-primary bg-opacity-10 text-dark px-3 py-1 rounded-pill">${escapeHTML(fila.tipo_cambio_3m)}</span>`
                      : '<span class="badge bg-secondary bg-opacity-10 text-dark px-3 py-1 rounded-pill">Sin planeación</span>';
                  }

                  if (data == '1') {
                    badge = '<span class="badge bg-dark bg-opacity-10 text-dark px-3 py-1 rounded-pill">Inesperado</span>';
                  } else if (data == '2') {
                    badge = '<span class="badge bg-success bg-opacity-10 text-dark px-3 py-1 rounded-pill">Programado</span>';
                  }
                  else if (data == '3') {
                    badge = '<span class="badge bg-info bg-opacity-10 text-dark px-3 py-1 rounded-pill">Otro</span>';
                  }
                  else if (data == '4') {
                    badge = '<span class="badge bg-warning acity-10 text-dark px-3 py-1 rounded-pill">Después de un largo periodo</span>';
                  }
                else if (data == '5') {
                    badge = '<span class="badge bg-danger bg-opacity-10 text-dark px-3 py-1 rounded-pill">Nuevo/Sin experiencia</span>';
                  }
                  else {
                    badge = `<span class="badge bg-secondary bg-opacity-10 text-dark px-3 py-1 rounded-pill">${'Sin definir'}</span>`;
                  }
                  return badge;
                }
              },
              { 
                data: 'estatusPC',
                className: 'px-4 py-3',
                render: function(data) {
                  let estatus = '';
                  if (data == '1') {
                    estatus = '<span class="badge bg-success bg-opacity-10 text-dark px-3 py-1 rounded-pill">Activo</span>';
                  } else if (data == '2') {
                    estatus = '<span class="badge bg-danger bg-opacity-10 text-dark px-3 py-1 rounded-pill">Cancelado</span>';
                  } else if (data == '3') {
                    estatus = '<span class="badge bg-info bg-opacity-10 text-dark px-3 py-1 rounded-pill">Finalizado</span>';
                  } else {
                    estatus = `<span class="badge bg-secondary bg-opacity-10 text-dark px-3 py-1 rounded-pill">N/A</span>`;
                  }
                  return estatus;
                }
              },
              {
                data: null,
                className: 'px-4 py-3',
                render: function(data, row) {
                  //Los PC 3M tienen su propio detalle (planeación, implementación, evaluación, cierre)
                  const claseDetalle = tipoConsulta === 'MO' ? 'btnDetallePC' : 'btnDetallePC3M';

                  return `
                    <button type="button"
                            class="btn btn-info btn-sm ${claseDetalle}"
                            data-idpc="${data.idPC}"
                            title="Ver detalle">
                      <i class="bi bi-eye"></i>
                    </button>
                  `;
                }
              }
            ],
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            responsive: false,
            scrollX: true,
            scrollY: 320,
            deferRender: false,
            paging: true,
            order: []
          });
          
          // Badge inicial (basado en el DOM recién generado)
          //actualizarBadgeAsignadosDOM();
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
    function setLiberados(select, codigoLinea = null, ordenLinea = null, certification){
      let nombreSelect = document.getElementById(select);
      let formDataLiberados = new FormData();
      formDataLiberados.append('opcion', 34);
      formDataLiberados.append('turno', $('#turnoLayout').val());

      (ordenLinea && ordenLinea !='') ? formDataLiberados.append('ordenLinea', ordenLinea) : '';
      (codigoLinea && codigoLinea !='') ? formDataLiberados.append('codigoLinea', codigoLinea) : '';
      (certification && certification !='') ? formDataLiberados.append('idCR', certification) : '';

      
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
              nombreSelect.innerHTML = '<option value="">Selecciona una opción</option>';
              console.log(data);
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
           nombreSelect.innerHTML = '<option value="">Selecciona una opción</option>';
          console.error(error);
        });
    }

    //PC de maquinaria: la descripción solo aplica cuando el tipo de cambio es "OTRO".
    //Se identifica por la clave del catálogo SPC_TIPO_3M y no por su id, que puede variar entre bases.
    const CLAVE_TIPO3M_OTRO = '6';

    function actualizarDescripcionMaq() {
      const opcion = document.getElementById('tipo_cambioMaq').selectedOptions[0];
      const descripcion = document.getElementById('motivoMaq');
      const esOtro = Boolean(opcion && String(opcion.dataset.clave || '').trim() === CLAVE_TIPO3M_OTRO);

      document.getElementById('contMotivoMaq').classList.toggle('d-none', !esOtro);
      descripcion.required = esOtro;

      //Al dejar de ser "OTRO" se limpia para no enviar una descripción que ya no aplica
      if (!esOtro) descripcion.value = '';
    }

    //Cargar el catálogo de tipos de cambio del PC de maquinaria (SPC_TIPO_3M, opción 44).
    //Se consulta una sola vez al abrir la página porque el catálogo no cambia durante el uso.
    function cargarTiposCambioMaq() {
      const selectTipo = document.getElementById('tipo_cambioMaq');
      let formDataTipos = new FormData();
      formDataTipos.append('opcion', 44);

      fetch("../api/operacionesLinea.php", {
        method: "POST",
        body: formDataTipos,
      })
        .then((response) => response.text())
        .then((data) => {
          data = JSON.parse(data);

          if (data.estatus !== 'ok') {
            selectTipo.innerHTML = '<option value="">No fue posible cargar los tipos de cambio</option>';
            console.log(data);
            return;
          }

          selectTipo.innerHTML = '<option value="">Selecciona un tipo de cambio...</option>';

          data.data.forEach((tipo) => {
            const opcion = document.createElement('option');
            opcion.value = tipo.idTIPO3M;
            opcion.textContent = tipo.nombre;
            opcion.dataset.clave = tipo.clave;
            selectTipo.appendChild(opcion);
          });
        })
        .catch((error) => {
          selectTipo.innerHTML = '<option value="">No fue posible cargar los tipos de cambio</option>';
          console.error(error);
        });
    }

    /* =====================================================================
       PUNTO DE CAMBIO 3M: MAQUINARIA, MATERIA PRIMA Y MÉTODO (opciones 45 a 51 de operacionesLinea.php)
       Una sola sección (#contregistroMaquinaria) sirve para los dos tipos; tipo4MActual indica
       cuál se muestra y se envía al servidor como clave4M.
       Vistas dentro de la sección:
       - registroPCMaq: primer registro, cuando la estación no tiene un PC abierto de ese tipo.
       - seguimientoPCMaq: pestañas Planeación, Implementación, Evaluación y Cierre del PC abierto.
       ===================================================================== */

    /* Tipos que usan esta sección (clave de SPC_TIPO_4MPC).
       backendListo: false mientras operacionesLinea.php no acepte ese tipo; la sección se
       muestra solo para revisión visual y no consulta ni guarda nada en el servidor. */
    const TIPOS_PC_3M = {
      MA: { nombre: 'maquinaria', icono: 'bi-gear-fill', idBoton: 'btnRegistroMaquinaria', backendListo: true },
      MP: { nombre: 'materia prima', icono: 'bi-box-seam', idBoton: 'btnRegistroMateriaPrima', backendListo: true },
      ME: { nombre: 'método', icono: 'bi-diagram-3', idBoton: 'btnRegistroMetodo', backendListo: true }
    };

    //Íconos de los PC 3M abiertos de una estación del layout (claves: ["MA","MP","ME"]).
    //Se muestran en el orden de TIPOS_PC_3M; sin PC 3M abiertos no se agrega nada.
    function htmlIconosPC3M(claves) {
      const abiertos = Array.isArray(claves) ? claves.map(clave => String(clave).trim().toUpperCase()) : [];
      const iconos = Object.keys(TIPOS_PC_3M)
        .filter(clave => abiertos.indexOf(clave) !== -1)
        .map(clave => {
          const descripcion = 'PC de ' + TIPOS_PC_3M[clave].nombre + ' abierto';
          return `<span class="station-pc3m-icon station-pc3m-${clave}" role="img" aria-label="${descripcion}" title="${descripcion}">` +
                 `<i class="bi ${TIPOS_PC_3M[clave].icono}" aria-hidden="true"></i></span>`;
        })
        .join('');

      return iconos ? `<div class="station-pc3m">${iconos}</div>` : '';
    }

    //Resalta en el menú del modal los botones con un PC abierto en la estación:
    //mano de obra (idPC del turno que muestra el layout) y PC 3M (pcs3M, en cualquier turno)
    function actualizarBotonesPCAbiertos(estacion) {
      const pcs3M = (estacion && Array.isArray(estacion.pcs3M)) ? estacion.pcs3M : [];

      marcarBotonPCAbierto(document.getElementById('btnRegistroPc'), Boolean(estacion && estacion.idPC));

      Object.keys(TIPOS_PC_3M).forEach(clave => {
        marcarBotonPCAbierto(document.getElementById(TIPOS_PC_3M[clave].idBoton), pcs3M.indexOf(clave) !== -1);
      });
    }

    function marcarBotonPCAbierto(boton, abierto) {
      if (!boton) return;

      boton.classList.toggle('pc-abierto', abierto);
      //Para lectores de pantalla, ya que el aviso visual es el color y la etiqueta "Abierto" (CSS)
      boton.setAttribute('aria-label', boton.textContent.trim() + (abierto ? ' (punto de cambio abierto)' : ''));
    }

    let tipo4MActual = 'MA';                         //Tipo de PC 3M que muestra la sección
    let idPCMaqActual = null;                        //PC abierto (del tipo actual) de la estación mostrada
    let estacionPCMaq = { id: null, nombre: '' };    //Estación consultada (para recargar tras guardar)
    let tokenConsultaPCMaq = 0;                      //Descarta respuestas de una estación o tipo anterior

    //Cambia el tipo de PC 3M de la sección: textos, ícono y clave enviada al servidor
    function aplicarTipoPC3M(clave) {
      const tipo = TIPOS_PC_3M[clave];
      tipo4MActual = clave;
      document.getElementById('claveTipo4MMaq').value = clave;

      document.querySelectorAll('#contregistroMaquinaria .texto-tipo-pc3m').forEach(texto => {
        texto.textContent = tipo.nombre;
      });

      document.querySelectorAll('#contregistroMaquinaria .icono-tipo-pc3m').forEach(icono => {
        Object.values(TIPOS_PC_3M).forEach(otroTipo => icono.classList.remove(otroTipo.icono));
        icono.classList.add(tipo.icono);
      });
    }

    //Botones MAQUINARIA / MATERIA PRIMA: misma sección, se cambia el tipo y se consulta su PC abierto
    function abrirSeccionPC3M(clave) {
      aplicarTipoPC3M(clave);
      changeContent('ventanasModalPC', 'contregistroMaquinaria');
      cargarPCMaquinaria(estacionPCMaq.id, estacionPCMaq.nombre);
    }

    function mostrarVistaPCMaq(vista) {
      document.getElementById('estadoPCMaq').classList.toggle('d-none', vista !== 'cargando');
      document.getElementById('registroPCMaq').classList.toggle('d-none', vista !== 'registro');
      document.getElementById('seguimientoPCMaq').classList.toggle('d-none', vista !== 'seguimiento');
    }

    //Limpia el primer registro y lo llena con los datos de la estación
    function prepararRegistroPCMaq(idEstacion, nombreEstacion) {
      document.getElementById('registroCambioMaqForm').reset();
      nombreResponsableMaq.placeholder = 'Nombre del responsable';
      $('#alertResponsableMaq').addClass('d-none');

      document.getElementById('fechaHora_inicioMaq').value = (new Date()).toLocaleString('sv-SE').slice(0, 16);
      document.getElementById('id_estacionMaq').value = idEstacion;
      document.getElementById('nombre_estacionMaq').value = nombreEstacion || '';
      document.getElementById('turnoPuntoCambioMaq').value = $('#turnoLayout').val();

      //Folio de referencia: el definitivo lo asigna el servidor al registrar
      document.getElementById('no_controlCambioMaq').value = '';
      getNoControl().then(resultado => { document.getElementById('no_controlCambioMaq').value = resultado; });
    }

    //Valida que una nómina exista en el catálogo de empleados (opción 7) y muestra el nombre.
    //Al editar la nómina se borra el nombre validado, así no se confirma con el de otra persona.
    function configurarValidacionNomina(campoNomina, campoNombre, selectorAlerta) {
      campoNomina.addEventListener('input', function(){
        campoNombre.value = '';
        $(selectorAlerta).addClass('d-none');
      });

      campoNomina.addEventListener('change', function(){
        if (campoNomina.value.trim() === '') return;

        let formDataNomina = new FormData();
        formDataNomina.append('opcion', 7);
        formDataNomina.append('nomina', campoNomina.value.trim());
        formDataNomina.append('codigoLinea', codigoLinea.value);

        campoNomina.disabled = true;
        campoNombre.value = '';
        campoNombre.placeholder = 'Consultando datos del empleado...';

        fetch("../api/operacionesLinea.php", {
          method: "POST",
          body: formDataNomina,
        })
          .then((response) => response.text())
          .then((data) => {
            data = JSON.parse(data);

            if (data.estatus == 'ok') {
              campoNombre.value = data.nombre;
              $(selectorAlerta).addClass('d-none');
            }

            else {
              campoNombre.placeholder = 'Nombre del responsable';
              $(selectorAlerta).text('Nómina inválida').removeClass('d-none');
            }
          })
          .catch((error) => {
            campoNombre.placeholder = 'Nombre del responsable';
            $(selectorAlerta).text('No fue posible validar la nómina').removeClass('d-none');
            console.log(error);
          })
          .finally(() => {
            campoNomina.disabled = false;
          });
      });
    }

    //Claves de SPC_VERIFICACION_DOCUMENTOS con reglas propias (las mismas que valida el servidor)
    const CLAVE_VERIF_SIN_PROBLEMA = 'SIN_PROBLEMA';
    const CLAVE_VERIF_OTRO = 'OTRO';

    //Carga las opciones de "Verificación de documentos" (opción 49) como casillas de selección múltiple
    function cargarVerificacionDocsMaq() {
      const contenedor = document.getElementById('verificacionDocsMaq');
      let formDataVerificacion = new FormData();
      formDataVerificacion.append('opcion', 49);

      fetch("../api/operacionesLinea.php", {
        method: "POST",
        body: formDataVerificacion,
      })
        .then((response) => response.text())
        .then((data) => {
          data = JSON.parse(data);

          if (data.estatus !== 'ok') {
            throw new Error(data.mensaje || 'Respuesta inválida del servidor.');
          }

          contenedor.innerHTML = '';

          data.data.forEach((opcion) => {
            const envoltura = document.createElement('div');
            const entrada = document.createElement('input');
            const etiqueta = document.createElement('label');

            envoltura.className = 'form-check';
            entrada.className = 'form-check-input';
            entrada.type = 'checkbox';
            entrada.name = 'verificacionDocMaq';
            entrada.id = 'verificacionDocMaq' + opcion.idVerificacion;
            entrada.value = opcion.idVerificacion;
            entrada.dataset.clave = String(opcion.clave || '').trim();
            etiqueta.className = 'form-check-label';
            etiqueta.htmlFor = entrada.id;
            etiqueta.textContent = opcion.nombre;

            envoltura.appendChild(entrada);
            envoltura.appendChild(etiqueta);
            contenedor.appendChild(envoltura);
          });
        })
        .catch((error) => {
          contenedor.innerHTML = '<span class="text-danger">No fue posible cargar las opciones de verificación.</span>';
          console.error(error);
        });
    }

    //"Sin problema" es exclusiva y "Otro" exige especificar el documento
    function actualizarVerificacionDocsMaq(entradaCambiada) {
      const entradas = Array.from(document.querySelectorAll('#verificacionDocsMaq input[name="verificacionDocMaq"]'));

      if (entradaCambiada && entradaCambiada.checked) {
        const esSinProblema = entradaCambiada.dataset.clave === CLAVE_VERIF_SIN_PROBLEMA;

        //Al marcar "Sin problema" se desmarcan las demás; al marcar otra, se desmarca "Sin problema"
        entradas.forEach(entrada => {
          if (entrada !== entradaCambiada &&
              (esSinProblema || entrada.dataset.clave === CLAVE_VERIF_SIN_PROBLEMA)) {
            entrada.checked = false;
          }
        });
      }

      const otroMarcado = entradas.some(entrada => entrada.checked && entrada.dataset.clave === CLAVE_VERIF_OTRO);
      const detalle = document.getElementById('otroDocMaq');

      document.getElementById('contOtroDocMaq').classList.toggle('d-none', !otroMarcado);
      detalle.required = otroMarcado;

      if (!otroMarcado) detalle.value = '';
    }

    //Puntos que se evalúan en el PC de maquinaria (metrica1 a metrica5 de SPC_EVALUACIONPC_3M)
    const METRICAS_EVAL_MAQ = [
      'Manejo / estado de uso',
      'Inspección previa de la operación',
      'Estado de operación del equipo',
      'Dificultad de la operación',
      'Acabado del producto'
    ];

    //Genera las preguntas OK / NG de la evaluación (1 = OK, 0 = NG, igual que mano de obra)
    function renderMetricasEvalMaq() {
      document.getElementById('metricasEvalMaq').innerHTML = METRICAS_EVAL_MAQ.map((titulo, indice) => {
        const nombre = 'metricaMaq' + (indice + 1);

        return `
          <div class="col-md-6 mb-3">
            <span class="form-label required-field d-block">${titulo}</span>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="${nombre}" id="${nombre}OK" value="1" required>
              <label class="form-check-label" for="${nombre}OK">OK</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="${nombre}" id="${nombre}NG" value="0">
              <label class="form-check-label" for="${nombre}NG">NG</label>
            </div>
          </div>`;
      }).join('');
    }

    //Muestra las evaluaciones ya registradas con el resultado de cada punto
    function renderListaEvalMaq(evaluaciones) {
      const contenedor = document.getElementById('listaEvalMaq');

      contenedor.innerHTML = evaluaciones.map(evaluacion => {
        const metricas = METRICAS_EVAL_MAQ.map((titulo, indice) => {
          const ok = String(evaluacion['metrica' + (indice + 1)]) === '1';
          return `<span class="badge ${ok ? 'bg-success' : 'bg-danger'} me-1 mb-1">${titulo}: ${ok ? 'OK' : 'NG'}</span>`;
        }).join('');

        const comentarios = evaluacion.comentarios
          ? `<div class="small text-muted mt-1">${escapeHTML(evaluacion.comentarios)}</div>`
          : '';

        return `
          <div class="border rounded-3 p-2 mb-2 bg-white">
            <div class="fw-semibold mb-1">Día ${escapeHTML(evaluacion.numeroDia)} · ${escapeHTML(evaluacion.fechaEvaluacion)}</div>
            <div class="d-flex flex-wrap">${metricas}</div>
            ${comentarios}
          </div>`;
      }).join('');
    }

    //Llena el seguimiento: resumen, planeación, implementación, evaluación y cierre
    function mostrarSeguimientoPCMaq(pc) {
      idPCMaqActual = pc.idPC;

      document.getElementById('folioPCMaq').textContent = pc.no_controlCambio || '';
      document.getElementById('responsablePCMaq').textContent = (pc.nombre || '') + ' (' + (pc.nomina || '') + ')';
      document.getElementById('inicioPCMaq').textContent = pc.fechaHora_inicio || '';
      document.getElementById('turnoPCMaq').textContent = pc.turno || '';

      const planeacionRegistrada = String(pc.planeacion_registrada) === '1';
      const formPlaneacion = document.getElementById('planeacionMaqForm');
      const selectTipo = document.getElementById('tipo_cambioMaq');
      const btnPlaneacion = document.getElementById('btnRegistrarPlaneacionMaq');

      formPlaneacion.reset();

      if (planeacionRegistrada) {
        document.getElementById('nombreProductoMaq').value = pc.nombre_producto || '';
        document.getElementById('detallesCambioMaq').value = pc.detalles_cambio || '';
        document.getElementById('equipoMaq').value = pc.equipo || '';
        selectTipo.value = String(pc.idTIPO3M || '');

        //Si el catálogo aún no se cargó, se agrega la opción registrada para poder mostrarla
        if (selectTipo.value !== String(pc.idTIPO3M)) {
          const opcion = new Option(pc.tipo_cambio || 'Tipo registrado', pc.idTIPO3M, true, true);
          opcion.dataset.clave = pc.clave_tipo3m || '';
          selectTipo.appendChild(opcion);
        }
      }

      actualizarDescripcionMaq(); //Muestra "Especifica el tipo de cambio" solo cuando es OTRO

      if (planeacionRegistrada) {
        document.getElementById('motivoMaq').value = pc.otro_detalle || '';
      }

      //Una vez registrada, la planeación queda de solo lectura
      formPlaneacion.querySelectorAll('input, textarea, select').forEach(campo => { campo.disabled = planeacionRegistrada; });
      btnPlaneacion.disabled = planeacionRegistrada;
      btnPlaneacion.innerHTML = planeacionRegistrada
        ? '<i class="bi bi-lock"></i> Planeación registrada'
        : '<i class="bi bi-check-lg"></i> Registrar planeación';

      //IMPLEMENTACIÓN: requiere la planeación; una vez registrada queda de solo lectura
      const implementacion = pc.implementacion || null;
      const implementacionRegistrada = Boolean(implementacion);
      const formImpl = document.getElementById('implementacionMaqForm');
      const btnImpl = document.getElementById('btnRegistrarImplMaq');

      formImpl.reset();
      $('#alertImplMaq').addClass('d-none');
      document.getElementById('nombreImplMaq').placeholder = 'Nombre del responsable';
      //La fecha compromiso no puede ser anterior al inicio del PC (el servidor también lo valida)
      document.getElementById('fechaCompromisoMaq').min = String(pc.fechaHora_inicio || '').slice(0, 10);

      if (implementacionRegistrada) {
        const idsMarcados = (implementacion.documentos || []).map(documento => String(documento.idVerificacion));

        document.querySelectorAll('#verificacionDocsMaq input[name="verificacionDocMaq"]').forEach(entrada => {
          entrada.checked = idsMarcados.indexOf(entrada.value) !== -1;
        });
        document.getElementById('nominaImplMaq').value = implementacion.nomina_responsable || '';
        document.getElementById('nombreImplMaq').value = implementacion.nombre_responsable || '';
        document.getElementById('fechaCompromisoMaq').value = implementacion.fecha_compromiso || '';
      }

      actualizarVerificacionDocsMaq(); //Muestra "Especifica el documento" solo cuando se marcó OTRO

      if (implementacionRegistrada) {
        document.getElementById('otroDocMaq').value = implementacion.otro_detalle || '';
      }

      formImpl.querySelectorAll('input, textarea').forEach(campo => {
        campo.disabled = implementacionRegistrada || !planeacionRegistrada;
      });
      btnImpl.disabled = implementacionRegistrada || !planeacionRegistrada;
      btnImpl.innerHTML = implementacionRegistrada
        ? '<i class="bi bi-lock"></i> Implementación registrada'
        : '<i class="bi bi-check-lg"></i> Registrar implementación';
      document.getElementById('avisoImplMaq').classList.toggle('d-none', planeacionRegistrada);

      //EVALUACIÓN: una por día (1 a 3) en orden; requiere la implementación
      const evaluaciones = pc.evaluaciones || [];
      const evaluacionesRequeridas = Number(pc.evaluaciones_requeridas) || 3;
      const evaluacionCompleta = evaluaciones.length >= evaluacionesRequeridas;
      const formEval = document.getElementById('evaluacionMaqForm');

      formEval.reset();
      renderListaEvalMaq(evaluaciones);
      document.getElementById('diaEvalMaq').textContent = Math.min(evaluaciones.length + 1, evaluacionesRequeridas);
      document.getElementById('avisoEvalMaq').classList.toggle('d-none', implementacionRegistrada);
      document.getElementById('evalCompletaMaq').classList.toggle('d-none', !evaluacionCompleta);
      formEval.classList.toggle('d-none', evaluacionCompleta);
      document.getElementById('footerEvalMaq').classList.toggle('d-none', evaluacionCompleta);
      formEval.querySelectorAll('input, textarea').forEach(campo => { campo.disabled = !implementacionRegistrada; });
      document.getElementById('btnRegistrarEvalMaq').disabled = !implementacionRegistrada;

      //CIERRE: requiere planeación, implementación y las evaluaciones completas
      const pendientes = [];
      if (!planeacionRegistrada) pendientes.push('la planeación');
      if (!implementacionRegistrada) pendientes.push('la implementación');
      if (!evaluacionCompleta) pendientes.push('las ' + evaluacionesRequeridas + ' evaluaciones');
      const puedeCerrar = pendientes.length === 0;
      const textoPendientes = pendientes.length > 1
        ? pendientes.slice(0, -1).join(', ') + ' y ' + pendientes[pendientes.length - 1]
        : (pendientes[0] || '');

      document.getElementById('cierreMaqForm').reset();
      document.getElementById('fechaCierreMaq').value = (new Date()).toLocaleString('sv-SE').slice(0, 16).replace(' ', 'T');
      document.getElementById('comentariosCierreMaq').disabled = !puedeCerrar;
      document.getElementById('btnCerrarPCMaq').disabled = !puedeCerrar;
      document.getElementById('textoAvisoCierreMaq').textContent = 'Para cerrar este punto de cambio falta registrar ' + textoPendientes + '.';
      document.getElementById('avisoCierreMaq').classList.toggle('d-none', puedeCerrar);

      //Se abre la primera pestaña pendiente del flujo
      const pestanaActiva = !planeacionRegistrada ? 'planeacionMaq-tab'
        : !implementacionRegistrada ? 'implementacionMaq-tab'
        : !evaluacionCompleta ? 'evaluacionMaq-tab'
        : 'cierreMaq-tab';
      bootstrap.Tab.getOrCreateInstance(document.getElementById(pestanaActiva)).show();

      mostrarVistaPCMaq('seguimiento');
    }

    //Consulta si la estación tiene un PC abierto del tipo actual (opción 46) y muestra la vista que corresponde
    function cargarPCMaquinaria(idEstacion, nombreEstacion) {
      const tokenActual = ++tokenConsultaPCMaq;
      idPCMaqActual = null;
      estacionPCMaq = { id: idEstacion, nombre: nombreEstacion || '' };

      //Tipo sin backend todavía: se muestra el registro solo para revisión visual, sin consultar
      if (!TIPOS_PC_3M[tipo4MActual].backendListo) {
        prepararRegistroPCMaq(idEstacion, nombreEstacion);
        mostrarVistaPCMaq('registro');
        return Promise.resolve();
      }

      mostrarVistaPCMaq('cargando');

      let formDataPCMaq = new FormData();
      formDataPCMaq.append('opcion', 46);
      formDataPCMaq.append('idEstacion', idEstacion);
      formDataPCMaq.append('clave4M', tipo4MActual);

      return fetch("../api/operacionesLinea.php", {
        method: "POST",
        body: formDataPCMaq,
      })
        .then((response) => response.text())
        .then((data) => {
          if (tokenActual !== tokenConsultaPCMaq) return;

          data = JSON.parse(data);

          if (data.estatus !== 'ok') {
            throw new Error(data.mensaje || 'Respuesta inválida del servidor.');
          }

          if (data.data) {
            mostrarSeguimientoPCMaq(data.data);
          } else {
            prepararRegistroPCMaq(idEstacion, nombreEstacion);
            mostrarVistaPCMaq('registro');
          }
        })
        .catch((error) => {
          if (tokenActual !== tokenConsultaPCMaq) return;

          //Si no se pudo consultar se muestra el registro; el servidor impide duplicar un PC abierto
          console.error(error);
          prepararRegistroPCMaq(idEstacion, nombreEstacion);
          mostrarVistaPCMaq('registro');
        });
    }

    //Envía una operación del PC 3M (del tipo actual) y muestra el resultado en el modal de estatus.
    //Al terminar con éxito recarga la vista de la estación (registro o seguimiento).
    function enviarOperacionPCMaq(boton, textoProceso, formData) {
      //Tipo sin backend todavía: no se envía nada al servidor
      if (!TIPOS_PC_3M[tipo4MActual].backendListo) {
        mostrarModalEstatus(
          'Registro no disponible',
          'bi-info-circle-fill text-primary',
          'El registro de puntos de cambio de ' + TIPOS_PC_3M[tipo4MActual].nombre + ' todavía no está disponible.',
          'bi-info-circle-fill text-primary',
          true
        );
        return Promise.resolve();
      }

      //El servidor valida la clave del tipo 4M (MA, MP) en cada operación
      formData.append('clave4M', tipo4MActual);

      const contenidoBoton = boton.innerHTML;
      boton.disabled = true;
      boton.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> ' + textoProceso;

      return fetch("../api/operacionesLinea.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.text())
        .then((data) => {
          data = JSON.parse(data);

          if (data.estatus !== 'ok') {
            boton.disabled = false;
            boton.innerHTML = contenidoBoton;
            mostrarModalEstatus(
              'No se pudo completar la operación',
              'bi-exclamation-octagon-fill text-danger',
              escapeHTML(data.mensaje || 'No fue posible guardar la información.'),
              'bi-exclamation-circle-fill text-danger',
              true
            );
            return;
          }

          //cargarPCMaquinaria oculta la vista mientras consulta, así que no hay doble envío;
          //la vista recargada define después qué botones quedan habilitados.
          boton.disabled = false;
          boton.innerHTML = contenidoBoton;
          cargarPCMaquinaria(estacionPCMaq.id, estacionPCMaq.nombre);

          //Actualiza la estación en el layout (íconos de PC 3M al registrar o cerrar)
          getEstacion(estacionPCMaq.id);

          mostrarModalEstatus(
            'Operación completada',
            'bi-check-circle-fill text-success',
            escapeHTML(data.mensaje + (data.no_controlCambio ? ' No. de control: ' + data.no_controlCambio : '')),
            'bi-check-circle-fill text-success',
            true
          );
        })
        .catch((error) => {
          console.error(error);
          boton.disabled = false;
          boton.innerHTML = contenidoBoton;
          mostrarModalEstatus(
            'No se pudo completar la operación',
            'bi-exclamation-octagon-fill text-danger',
            'Se perdió la conexión con el servidor o la respuesta no es válida.',
            'bi-exclamation-circle-fill text-danger',
            true
          );
        });
    }

    //Funcion para eliminar personal
    function eliminarPersonal(nomina){
      const respuesta = confirm('¿Seguro que deseas eliminar este trabajador?');
        if (respuesta) {
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
      
                    mostrarModalEstatus(
                        'Trabajador eliminado',
                        'bi-person-x-fill text-success',
                        'El trabajador se eliminó correctamente.',
                        'bi-check-circle-fill text-success',
                        true
                    );

                  actualizarVistaLayout();         
                } else {
                    console.log(data)
                    mostrarTablaPersonal();
                }
            })
            .catch(error => {
                console.error(error);
                mostrarModalEstatus(
                        'No se pudo completar la operación',
                        'bi-exclamation-octagon-fill text-danger',
                        `La información enviada no pudo procesarse. O se perdió la conexión con el servidor`,
                        'bi-exclamation-circle-fill text-danger',
                        true
                );

            });
        }
    }

    //Función para obtener el listado de operaciones asignadas actualmente de un trabajador
    function getOperaciones(nomina){
      let formData= new FormData();
      formData.append('opcion', 39);
      formData.append('codigoLinea', codigoLinea.value);
      formData.append('turno', $('#turnoLayout').val());
      formData.append('nomina', nomina);

      fetch("../api/operacionesLinea.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.text())
        .then((data) => {

          data = JSON.parse(data);
          const response = data.data;

          // Inicializar DataTable listado de puntos de cambio
          $('#tableListadoOperacionesTrabajador').DataTable().destroy();

          $('#tableListadoOperacionesTrabajador').DataTable({
            data: response,

            columns: [
              {
                data: null,
                className: 'px-4 py-3',
                render: function(data, type, row) {
                  return `
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge bg-secondary bg-opacity-10 text-dark px-3 py-2 rounded-pill">
                        ${row.nombre_estacion ?? ''}
                      </span>
                    </div>
                  `;
                }
              },
              {
                data: 'fecha_inicio',
                className: 'px-4 py-3',
                render: function(data, type) {
                  if (!data) {
                    return `
                      <span class="badge bg-light text-muted px-3 py-2 rounded-pill">
                        Sin fecha
                      </span>
                    `;
                  }

                  // Para ordenar correctamente por fecha
                  if (type === 'sort' || type === 'type') {
                    return data;
                  }

                  return `
                    <span class="badge bg-info bg-opacity-10 text-dark px-3 py-2 rounded-pill">
                      <i class="bi bi-calendar-event me-1"></i>
                      ${formatearFecha(data)}
                    </span>
                  `;
                }
              },
              {
                data: 'comentarios',
                className: 'px-4 py-3',
                render: function(data) {
                  if (!data || data.trim() === '') {
                    return `
                      <span class="text-muted fst-italic">
                        Sin comentarios
                      </span>
                    `;
                  }

                  return `
                    <span class="text-dark">
                      ${data}
                    </span>
                  `;
                }
              }
            ],
            createdRow: function (row, data) {
               (data.tabla == 2)  ?  $(row).addClass('bg-success bg-opacity-10') : '';
            },

            pageLength: 10,
            lengthChange: false,
            responsive: true,
            scrollX: true,
            deferRender: false,
            paging: true,
            searching: false,
            ordering: false,
            autoWidth: false,
          });
          
          // Badge inicial (basado en el DOM recién generado)
          //actualizarBadgeAsignadosDOM();
        })
        .catch((error) => {
          console.error(error);
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

    //Obtener el listado de certificaciones por linea
    async function listadoCertificaciones(idSelect){
        let formData = new FormData();
        formData.append('opcion', 40)
        formData.append('codigoLinea', codigoLinea.value)
        let select = document.getElementById(idSelect)

         return fetch("../api/operacionesLinea.php", {
                    method: "POST",
                    body: formData,
                })
                .then((response) => response.text())
                .then((data) => {
                    data= JSON.parse(data)

                    if(data.estatus=='ok'){
                        //CARGAR LISTADO DE OPERACIONES EN EL SELECT
                            select.innerHTML='';

                            let noneA = document.createElement('option');
                            noneA.value = '';
                            noneA.textContent = 'Seleccione una opcion';
                            select.appendChild(noneA);

                            data.certificaciones.forEach(cert => {
                              const option = document.createElement('option');
                              option.value = cert.idCR;   
                              option.textContent = cert.nombre_certificacion; 
                              select.appendChild(option);
                            });
                        //FIN LISTADO DE OPERACIONES
                    }
                  
                    else{
                        console.log(data); 
                    }

                }).catch((error) => {
                  console.log(error);
            });
    }

    // Inicializar lista de elementos
    refreshElementList();

    // Inicializar el workspace
    document.addEventListener('DOMContentLoaded', function() {
      workspaceGrid = document.getElementById('workspaceGrid');

        const ahora = new Date();
        const hora = ahora.getHours();
        const minutos = ahora.getMinutes();
        const minutosActuales = (hora * 60) + minutos;

        const inicioTurno1 = 8 * 60;        // 08:00
        const finTurno1 = (19 * 60) + 59;   // 19:59

        const inicioTurno2 = 20 * 60;       // 20:00
        const finTurno2 = (7 * 60) + 59;    // 07:59

        if (minutosActuales >= inicioTurno1 && minutosActuales <= finTurno1) {
          $('#turnoLayout').val('1')
        } 

        else 
          if(minutosActuales >= inicioTurno2 || minutosActuales <= finTurno2) {
              $('#turnoLayout').val('2')
        }
      
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
  
      //Generar listado de personal perteneciente a la linea
      generarTablaAsistencia();

      getHistorialLayout();

      mostrarTablaPersonal();
      mostrarTablaOperaciones();
      mostrarTablaPC();

      //Catálogo del select "Tipo de cambio" del PC de maquinaria
      cargarTiposCambioMaq();

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

                        fetch("../api/operacionesLinea.php", {
                                method: "POST",
                                body: formDataConsultarNombre,
                            })
                            .then((response) => response.text())
                            .then((data) => {
                             // console.log(data)
                                data= JSON.parse(data)
                                if(data.estatus=='ok'){
                                    nombreModalRegistrar.value= data.nombre;
                                    $('#alertAgregarPersonal').addClass('d-none')
                                  }
                              
                              else{
                                  nombreModalRegistrar.placeholder= "Nombre del empleado...";  
                                  $('#alertAgregarPersonal').removeClass('d-none');
                                  $('#alertAgregarPersonal').text('Nómina invalida')
                                  console.log(data.error); 
                              }

                              nominaModalRegistrar.disabled = false;
                            })
                            .catch((error) => {
                              nombreModalRegistrar.placeholder= "Nombre del empleado..."; 
                              nominaModalRegistrar.disabled = false;
                              $('#alertAgregarPersonal').addClass('d-none')
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
                $('#alertNominaAsistencia').addClass('d-none');

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

          //Cargar el listado de operaciones donde esta liberado el trabajador seleccionado
            nominaPC.addEventListener('change', function(){

              $('#alertChipsAgregarEstacionesPC').addClass('d-none');

                if(nominaPC && nominaPC.value !='') {
                    let formDataEstaciones = new FormData();
                    formDataEstaciones.append('opcion', 41)
                    formDataEstaciones.append('codigoLinea', codigoLinea.value)
                    formDataEstaciones.append('nomina', nominaPC.value)

                       fetch("../api/operacionesLinea.php", {
                            method: "POST",
                            body: formDataEstaciones,
                        })
                        .then((response) => response.text())
                        .then((data) => {
                            data= JSON.parse(data)
                            if(data.estatus=='ok'){
                                //CARGAR LISTADO DE OPERACIONES EN EL SELECT DEL PC
                                  selectEstacionesPC.innerHTML='';

                                  let noneA = document.createElement('option');
                                  noneA.value = '';
                                  noneA.textContent = 'Selecciona una estación...';
                                  selectEstacionesPC.appendChild(noneA);

                                  data.estaciones.forEach(station => {
                                    const option = document.createElement('option');
                                    option.value = station.id_estacion;   
                                    option.textContent = station.nombre_estacion; 
                                    selectEstacionesPC.appendChild(option);
                                  });
                                //FIN LISTADO DE OPERACIONES
                            }

                          else {
                            console.log(data)
                          }
                        })
                        .catch((error) => {
                          console.log(error);
                    });
                }

                else {
                  selectEstacionesPC.innerHTML='<option value="">Selecciona una estación...</option>';
                }
            }) 

          $('#selectEstacionesPC').on('change', function () {
              $('#alertChipsAgregarEstacionesPC').addClass('d-none');
          });

          //PC de maquinaria: mostrar la descripción solo cuando el tipo de cambio es "OTRO"
            document.getElementById('tipo_cambioMaq').addEventListener('change', actualizarDescripcionMaq);

          //PC de maquinaria: validación de nómina del responsable del registro y de la implementación
            configurarValidacionNomina(nominaResponsableMaq, nombreResponsableMaq, '#alertResponsableMaq');
            configurarValidacionNomina(document.getElementById('nominaImplMaq'), document.getElementById('nombreImplMaq'), '#alertImplMaq');

          //PC de maquinaria: catálogo de verificación de documentos y puntos de la evaluación
            cargarVerificacionDocsMaq();
            renderMetricasEvalMaq();
            document.getElementById('verificacionDocsMaq').addEventListener('change', function (event) {
                const entrada = event.target.closest('input[name="verificacionDocMaq"]');

                if (entrada) {
                    actualizarVerificacionDocsMaq(entrada);
                }
            });

          $('#selectRegistrarUpdate').on('change', function () {
              $('#alertChipsAgregarOperacion').addClass('d-none');
          });

          $('#selectRegistrar').on('change', function () {
              $('#alertChipsAgregarPersonal').addClass('d-none');
          });
        
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
                    mostrarModalEstatus(
                                  'No se pudo completar la operación',
                                  'bi-exclamation-octagon-fill text-danger',
                                  `No se encontro registro del empleado ingresado o se perdió la conexión con el servidor`,
                                  'bi-exclamation-circle-fill text-danger',
                                  true
                    );
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
                                //assignmentFormPC.reset();
                                  mostrarModalEstatus(
                                        'Operación completada',
                                        'bi-check-circle-fill text-success',
                                        `${data.mensaje}`,
                                        'bi-check-circle-fill text-success',
                                        false
                                  );

                                $('#nominaModalPC').val('');
                                $('#nombreModalPC').val('');
                                $('#comentariospc').val('');
                                getEstacion(estacion, null);
                                mostrarTablaOperaciones();
                                mostrarTablaPersonal();
                            }

                          else {
                            mostrarModalEstatus(
                                      'No se pudo completar la operación',
                                      'bi-exclamation-octagon-fill text-danger',
                                      `${data.mensaje}`,
                                      'bi-exclamation-circle-fill text-danger',
                                      true
                            );

                            console.log(data.error)
                          }
                        })
                        .catch((error) => {
                          console.log(error);
                    });
                }
          })

        //REGISTRAR TRABAJADOR AL LISTADO DE PERSONAL EN EL MODAL modalRegistrarOperador 
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

                if(!nombre){
                    mostrarModalEstatus(
                          'No se pudo completar la operación',
                          'bi-exclamation-octagon-fill text-danger',
                          `No se encontro registro del empleado ingresado o se perdió la conexión con el servidor`,
                          'bi-exclamation-circle-fill text-danger',
                           true
                    );
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
                            mostrarModalEstatus(
                                  'Operación completada',
                                  'bi-check-circle-fill text-success',
                                  `${data.mensaje}`,
                                  'bi-check-circle-fill text-success',
                                  true
                            );
                            //assignmentFormPC.reset();

                            $('#nominaModalRegistrar').val('');
                            $('#nombreModalRegistrar').val('');
                            $('#alertChipsAgregarPersonal').addClass('d-none');
                            chipsEstaciones = [];
                            $('#operationsListContainer').html('');
                            mostrarTablaPersonal()
                        }

                      else {
                            mostrarModalEstatus(
                                      'No se pudo completar la operación',
                                      'bi-exclamation-octagon-fill text-danger',
                                      `${data.mensaje}`,
                                      'bi-exclamation-circle-fill text-danger',
                                      true
                            );
                        console.log(data.error)
                      }
                    })
                    .catch((error) => {
                      console.log(error);
                });
        })

        //CARGAR LISTADO DE PERSONAL CON ARCHIVO
        btnRegistrarOperadorFile.addEventListener('click', fileInputOperatos)

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
              mostrarModalEstatus(
                  'Punto de cambio activo',
                  'bi-stopwatch-fill text-warning',
                  'Debes finalizar el punto de cambio activo antes de remover al trabajador de la operación',
                  'bi-exclamation-triangle-fill text-warning',
                  true
              );

              return;
          }

          if(nominaTrabajador == '' || nominaTrabajador == null){
              mostrarModalEstatus(
                                  'Trabajador no asignado',
                                  'bi-stopwatch-fill text-warning',
                                  'No hay trabajador asignado a esta estación',
                                  'bi-exclamation-triangle-fill text-warning',
                                  true
                                );
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
                      mostrarModalEstatus(
                          'Operación completada',
                          'bi-check-circle-fill text-success',
                          `${data.mensaje}`,
                          'bi-check-circle-fill text-success',
                          false
                      );

                      getEstacion(estacionId, null)
                      mostrarTablaOperaciones();
                      mostrarTablaPersonal();
                    }

                    else {
                      mostrarModalEstatus( 
                                  'No se pudo completar la operación',
                                  'bi-exclamation-octagon-fill text-danger',
                                  `${data.mensaje}`,
                                  'bi-exclamation-circle-fill text-danger',
                                  true
                      );

                    }
            })
            .catch((error) => {
              console.log(error);
            });
        })

        //Registrar un punto de cambio
        confirmChange.addEventListener('click', function(){
          let registroCambioForm = document.getElementById('registroCambioForm');
          let formDataPuntoCambio = new FormData;
          let idEstacion = document.getElementById('id_estacion').value;

          if(!registroCambioForm.reportValidity()) return;

          if(document.getElementById('nominaPC').value == '' || document.getElementById('nominaPC').value == null){
                mostrarModalEstatus(
                    'No se pudo completar la operación',
                    'bi-exclamation-octagon-fill text-danger',
                    `No se encontro registro del empleado ingresado o se perdió la conexión con el servidor.`,
                    'bi-exclamation-circle-fill text-danger',
                    true
                );

                return;
          }

          let nominaEtiqueta = $("#changeControlInfoNomina").text().trim();
          let nominaInput = $("#nominaPC").val().trim();

          if (nominaInput !== "" && Number(nominaEtiqueta) === Number(nominaInput)) {
              mostrarModalEstatus(
                'Trabajador ya registrado',
                'bi-stopwatch-fill text-warning',
                'No se puede crear el punto de cambio ya que el trabajador está asignado a esta estación.',
                'bi-exclamation-triangle-fill text-warning',
                true
              );
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
          formDataPuntoCambio.append('estaciones', JSON.stringify(chipsEstacionesPC));

          fetch("../api/operacionesLinea.php", {
                    method: "POST",
                    body: formDataPuntoCambio,  
                })
                .then((response) => response.text())
                .then((data) => { 
                    //console.log(data);
                    data= JSON.parse(data)  
                    if(data.estatus=='ok'){ 
                        mostrarModalEstatus(
                            'Operación completada',
                            'bi-check-circle-fill text-success',
                            `${data.mensaje}`,
                            'bi-check-circle-fill text-success',
                            false
                        );

                         // Si no es arreglo, lo convertimos en uno
                          getEstacion(null, data.idPC);
                          mostrarTablaPersonal();
                          
                          chipsEstacionesPC = [];
                          actualizarEstacionChips();
                    } 

                    else {
                          mostrarModalEstatus(
                              'No se pudo completar la operación',
                              'bi-exclamation-octagon-fill text-danger',
                              `${data.mensaje}`,
                              'bi-exclamation-circle-fill text-danger',
                              true
                          );
                    }
                })
                .catch((error) => {
                  mostrarModalEstatus(
                      'No se pudo completar la operación',
                      'bi-exclamation-octagon-fill text-danger',
                      `La información enviada no pudo procesarse. O se perdió la conexión con el servidor`,
                      'bi-exclamation-circle-fill text-danger',
                      true
                  );
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
                mostrarModalEstatus(
                  'Sin registro de punto de cambio',
                  'bi-stopwatch-fill text-warning',
                  'No hay un punto de cambio activo en esta estación',
                  'bi-exclamation-triangle-fill text-warning',
                  true
                );

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
                          mostrarModalEstatus(
                              'Operación completada',
                              'bi-check-circle-fill text-success',
                              `${data.mensaje}`,
                              'bi-check-circle-fill text-success',
                              false
                            );
        
                          // Si no es arreglo, lo convertimos en uno
                          //let estacionesA = Array.isArray(idEstacion) ? idEstacion : [idEstacion];
                            getEstacion(null, idPC.value);
                            mostrarTablaPersonal();                            
                        }

                      else {
                            mostrarModalEstatus(
                                    'No se pudo completar la operación',
                                    'bi-exclamation-octagon-fill text-danger',
                                    `${data.mensaje}`,
                                    'bi-exclamation-circle-fill text-danger',
                                    true
                            );
                      }

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
              
              if(nominaAsistencia.value == '' || !nombreAsistencia.value.trim()){
                  $('#alertNominaAsistencia').text('No se encontro la nómina');
                  $('#alertNominaAsistencia').removeClass('d-none');
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
                  
                                mostrarModalEstatus(
                                    'Operación completada',
                                    'bi-check-circle-fill text-success',
                                    `${data.mensaje}`,
                                    'bi-check-circle-fill text-success',
                                    true
                                );

                                formAsistencia.reset();
                                generarTablaAsistencia();

                                $('#alertNominaAsistencia').addClass('d-none');
                            }

                          else {
                            mostrarModalEstatus(
                                'No se pudo completar la operación',
                                'bi-exclamation-octagon-fill text-danger',
                                `${data.mensaje}`,
                                'bi-exclamation-circle-fill text-danger',
                                true
                            );

                            console.log(data.error)
                          }
                        })
                        .catch((error) => {
                          console.log(error);
                    });
                }
        })

        //Detectar cuando se abra el modal de registrar operador
        document.getElementById('btnMenuRegistrar').addEventListener('click', function (){
          document.getElementById('fecharegistrar').value = (new Date()).toLocaleString('sv-SE').slice(0, 16);
          listadoCertificaciones('selectRegistrar');
        })

        $('#btnChipModalRegistrar').click(agregarChipPersonal)
        $('#btnEstacionesPC').click(agregarChipEstacion)

        btnRegistrarAsistencia.addEventListener('click', registrarAsistencia);
        btnMenuAsignarControlModal.addEventListener('click', function(){changeContent('ventanasModalPC','contAsignacion')});
        //btnMenuAsignarControlModal.addEventListener('click', function(){changeContent('ventanasModalPC','contAsignacion')});
        btnMenuAsignaciones.addEventListener('click', mostrarTablaOperaciones)
        btnInfoRPC.addEventListener('click', function(){changeContent('ventanasModalPC','contInfoEstacion')});
        btnRegistroPc.addEventListener('click', function(){changeContent('ventanasModalPC','contregistroCambioForm')});
        //Maquinaria, materia prima y método comparten la sección del PC 3M; el botón define el tipo
        btnRegistroMaquinaria.addEventListener('click', function(){ abrirSeccionPC3M('MA'); });
        btnRegistroMateriaPrima.addEventListener('click', function(){ abrirSeccionPC3M('MP'); });
        btnRegistroMetodo.addEventListener('click', function(){ abrirSeccionPC3M('ME'); });

        //PC de maquinaria: primer registro (opción 45). Al guardarse pasa al seguimiento.
        document.getElementById('confirmChangeMaq').addEventListener('click', function(){
          if (!document.getElementById('registroCambioMaqForm').reportValidity()) return;

          //El nombre solo se llena cuando la nómina existe en el catálogo de empleados
          if (nombreResponsableMaq.value.trim() === '') {
            mostrarModalEstatus(
              'Personal responsable no validado',
              'bi-exclamation-triangle-fill text-warning',
              'Ingresa una nómina válida del personal responsable y espera a que se muestre su nombre.',
              'bi-exclamation-triangle-fill text-warning',
              true
            );
            return;
          }

          let formDataRegistroMaq = new FormData();
          formDataRegistroMaq.append('opcion', 45);
          formDataRegistroMaq.append('idEstacion', document.getElementById('id_estacionMaq').value);
          formDataRegistroMaq.append('codigoLinea', codigoLinea.value);
          formDataRegistroMaq.append('turno', document.getElementById('turnoPuntoCambioMaq').value);
          formDataRegistroMaq.append('nominaResponsable', nominaResponsableMaq.value.trim());

          enviarOperacionPCMaq(this, 'Registrando...', formDataRegistroMaq);
        });

        //PC de maquinaria: registro de la planeación (opción 47). Se registra una sola vez.
        document.getElementById('btnRegistrarPlaneacionMaq').addEventListener('click', function(){
          if (!idPCMaqActual) return;
          if (!document.getElementById('planeacionMaqForm').reportValidity()) return;

          let formDataPlaneacionMaq = new FormData();
          formDataPlaneacionMaq.append('opcion', 47);
          formDataPlaneacionMaq.append('idPC', idPCMaqActual);
          formDataPlaneacionMaq.append('nombreProducto', document.getElementById('nombreProductoMaq').value.trim());
          formDataPlaneacionMaq.append('detallesCambio', document.getElementById('detallesCambioMaq').value.trim());
          formDataPlaneacionMaq.append('equipo', document.getElementById('equipoMaq').value.trim());
          formDataPlaneacionMaq.append('idTipo3M', document.getElementById('tipo_cambioMaq').value);
          formDataPlaneacionMaq.append('otroDetalle', document.getElementById('motivoMaq').value.trim());

          enviarOperacionPCMaq(this, 'Registrando...', formDataPlaneacionMaq);
        });

        //PC de maquinaria: registro de la implementación (opción 50). Se registra una sola vez.
        document.getElementById('btnRegistrarImplMaq').addEventListener('click', function(){
          if (!idPCMaqActual) return;

          const documentos = Array.from(
            document.querySelectorAll('#verificacionDocsMaq input[name="verificacionDocMaq"]:checked')
          ).map(entrada => entrada.value);

          if (!documentos.length) {
            mostrarModalEstatus(
              'Verificación de documentos',
              'bi-exclamation-triangle-fill text-warning',
              'Marca al menos una opción de verificación de documentos.',
              'bi-exclamation-triangle-fill text-warning',
              true
            );
            return;
          }

          if (!document.getElementById('implementacionMaqForm').reportValidity()) return;

          //El nombre solo se llena cuando la nómina existe en el catálogo de empleados
          if (document.getElementById('nombreImplMaq').value.trim() === '') {
            mostrarModalEstatus(
              'Responsable no validado',
              'bi-exclamation-triangle-fill text-warning',
              'Ingresa una nómina válida del responsable y espera a que se muestre su nombre.',
              'bi-exclamation-triangle-fill text-warning',
              true
            );
            return;
          }

          let formDataImplMaq = new FormData();
          formDataImplMaq.append('opcion', 50);
          formDataImplMaq.append('idPC', idPCMaqActual);
          formDataImplMaq.append('documentos', JSON.stringify(documentos));
          formDataImplMaq.append('otroDetalle', document.getElementById('otroDocMaq').value.trim());
          formDataImplMaq.append('nominaResponsable', document.getElementById('nominaImplMaq').value.trim());
          formDataImplMaq.append('fechaCompromiso', document.getElementById('fechaCompromisoMaq').value);

          enviarOperacionPCMaq(this, 'Registrando...', formDataImplMaq);
        });

        //PC de maquinaria: evaluación del día siguiente (opción 51). El servidor asigna el número de día.
        document.getElementById('btnRegistrarEvalMaq').addEventListener('click', function(){
          if (!idPCMaqActual) return;

          const formEval = document.getElementById('evaluacionMaqForm');

          if (!formEval.reportValidity()) return;

          let formDataEvalMaq = new FormData();
          formDataEvalMaq.append('opcion', 51);
          formDataEvalMaq.append('idPC', idPCMaqActual);

          METRICAS_EVAL_MAQ.forEach((titulo, indice) => {
            const seleccion = formEval.querySelector('input[name="metricaMaq' + (indice + 1) + '"]:checked');
            formDataEvalMaq.append('metrica' + (indice + 1), seleccion ? seleccion.value : '');
          });

          formDataEvalMaq.append('comentarios', document.getElementById('comentariosEvalMaq').value.trim());

          enviarOperacionPCMaq(this, 'Registrando...', formDataEvalMaq);
        });

        //PC de maquinaria: cierre (opción 48). El servidor exige planeación, implementación y 3 evaluaciones.
        document.getElementById('btnCerrarPCMaq').addEventListener('click', function(){
          if (!idPCMaqActual) return;

          //La fecha de cierre es el momento en que se confirma (formato AAAA-MM-DDTHH:MM que espera el servidor)
          const fechaCierre = (new Date()).toLocaleString('sv-SE').slice(0, 16).replace(' ', 'T');
          document.getElementById('fechaCierreMaq').value = fechaCierre;

          let formDataCierreMaq = new FormData();
          formDataCierreMaq.append('opcion', 48);
          formDataCierreMaq.append('idPC', idPCMaqActual);
          formDataCierreMaq.append('fechaCierre', fechaCierre);
          formDataCierreMaq.append('comentarios', document.getElementById('comentariosCierreMaq').value.trim());

          enviarOperacionPCMaq(this, 'Cerrando...', formDataCierreMaq);
        });

        btnAgregarPersonalP1.addEventListener('click', function(){changeContent('ventanasModalAgregarPersonal','agregarPersonalIndividual')});
        btnAgregarPersonalP2.addEventListener('click', function(){changeContent('ventanasModalAgregarPersonal','agregarListadoPersonal')});
        
        btnVolverConsultaPC.addEventListener('click', function (){
            changeContent('ventanasConsultaPC', 'ventanaTablaPC')
            btnVolverConsultaPC.classList.add("d-none")
        })

        btnMenuRegistroAs.addEventListener('click', generarTablaAsistencia);
        btnCambioTurno.addEventListener('click', cambiarTurno);
        fechaHistorial.addEventListener('change', getHistorialLayout);
        turnoHistorial.addEventListener('change', getHistorialLayout);
        btnEvaluacion.addEventListener('click', registrarEvaluacionPC);

        // SELECT → change
        $('#attendanceTable tbody').on('change', 'select', async function (e) {     
          
          const actualizado = await updateAsistencia(this, 'estatus'); 
          
          if(!actualizado) {
                console.log(actualizado)
              return;
          }
          
          const estaciones = $(this).data('id_estacion'); //Si se queda informacion en el cache con esta funcion puede no actualizarse los valores de data
          //const estaciones = $(this).attr('data-id_estacion'); //como estos se acutalizan de manera dinamica es mejor usar attr

          if (estaciones === undefined || estaciones === null || estaciones === '') {
              return;
          }

          //Obtener los id separados por ","
          let idsEstaciones = String(estaciones).split(',').map(id => id.trim()).filter(id => id !== '' && id.toLowerCase() !== 'null' && id.toLowerCase() !== 'undefined');

            await Promise.all(
               idsEstaciones.map( function (idEstacion) { getEstacion(idEstacion, null)})
            );

        });

        //Guardar las observaciones cuando el usuario termine de editar el campo
        $('#attendanceTable tbody').on('focusout', 'input[name="observacionesAsistencia"]', function () {
            updateAsistencia(this, 'observacionesAsistencia');
        });

        //Permitir que Enter termine la edición y active el mismo guardado
        $('#attendanceTable tbody').on('keydown', 'input[name="observacionesAsistencia"]', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).blur();
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
                //console.log(data);
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

        //INICIO CONTROL PARA EXPANDIR Y COMPRIMIR LA BARRA DE HERRAMIENTAS
            const toolsSidebar = document.getElementById('tools-sidebar');
            const toolsSidebarMedia = window.matchMedia('(max-width: 991.98px)');
            const toolsMenuButtons = toolsSidebar.querySelectorAll('.tools-menu .tool-btn');
            let desktopSidebarHidden = toolsSidebar.classList.contains('d-none');
            let sidebarTransitionTimer = null;

            function setToolsSidebarExpanded(expanded) {
              toolsSidebar.classList.toggle('is-expanded', expanded);
              btnToggleToolsSidebar.setAttribute('aria-expanded', expanded);
              btnToggleToolsSidebar.setAttribute('title', expanded ? 'Comprimir herramientas' : 'Expandir herramientas');
            }

            function setMobileToolsSidebar(open, restoreFocus) {
              const shouldOpen = toolsSidebarMedia.matches && open;

              if (shouldOpen) {
                clearTimeout(sidebarTransitionTimer);
                toolsSidebar.classList.remove('d-none', 'sidebar-transition-hidden');
              }

              document.body.classList.toggle('tools-sidebar-mobile-open', shouldOpen);
              btnOpenToolsSidebarMobile.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
              toolsSidebar.setAttribute(
                'aria-hidden',
                toolsSidebarMedia.matches && !shouldOpen ? 'true' : 'false'
              );

              if (toolsSidebarMedia.matches && !shouldOpen) {
                toolsSidebar.setAttribute('inert', '');
              } else {
                toolsSidebar.removeAttribute('inert');
              }

              if (shouldOpen) {
                btncloseSidebar.focus();
              } else if (restoreFocus && toolsSidebarMedia.matches) {
                btnOpenToolsSidebarMobile.focus();
              }
            }

            function hideDesktopToolsSidebar() {
              desktopSidebarHidden = true;
              toolsSidebar.classList.add('sidebar-transition-hidden');

              clearTimeout(sidebarTransitionTimer);
              sidebarTransitionTimer = setTimeout(() => {
                toolsSidebar.classList.add('d-none');
                btnfloatingMenu.classList.remove('d-none');
              }, 320);
            }

            function showDesktopToolsSidebar() {
              desktopSidebarHidden = false;
              clearTimeout(sidebarTransitionTimer);
              toolsSidebar.classList.remove('d-none');
              btnfloatingMenu.classList.add('d-none');

              requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                  toolsSidebar.classList.remove('sidebar-transition-hidden');
                });
              });
            }

            btnToggleToolsSidebar.addEventListener('click', function () {
              setToolsSidebarExpanded(!toolsSidebar.classList.contains('is-expanded'));
            })

            btncloseSidebar.addEventListener('click', function () {
              if (toolsSidebarMedia.matches) {
                setMobileToolsSidebar(false, true);
                return;
              }

              hideDesktopToolsSidebar();
            })

            btnfloatingMenu.addEventListener('click', function () {
              if (toolsSidebarMedia.matches) {
                setMobileToolsSidebar(true, false);
                return;
              }

              showDesktopToolsSidebar();
            })

            btnOpenToolsSidebarMobile.addEventListener('click', function () {
              setMobileToolsSidebar(true, false);
            })

            toolsSidebarBackdrop.addEventListener('click', function () {
              setMobileToolsSidebar(false, true);
            })

            toolsMenuButtons.forEach(function (button) {
              button.addEventListener('click', function () {
                if (toolsSidebarMedia.matches) {
                  setMobileToolsSidebar(false, true);
                }
              });
            });

            document.addEventListener('keydown', function (event) {
              if (
                event.key === 'Escape' &&
                document.body.classList.contains('tools-sidebar-mobile-open')
              ) {
                setMobileToolsSidebar(false, true);
              }
            });

            function syncToolsSidebarMode() {
              document.body.classList.remove('tools-sidebar-mobile-open');
              btnOpenToolsSidebarMobile.setAttribute('aria-expanded', 'false');

              if (toolsSidebarMedia.matches) {
                clearTimeout(sidebarTransitionTimer);
                toolsSidebar.classList.remove('d-none', 'sidebar-transition-hidden');
                btnfloatingMenu.classList.add('d-none');
                toolsSidebar.setAttribute('aria-hidden', 'true');
                toolsSidebar.setAttribute('inert', '');
                return;
              }

              toolsSidebar.removeAttribute('inert');
              toolsSidebar.setAttribute('aria-hidden', 'false');

              if (desktopSidebarHidden) {
                toolsSidebar.classList.add('d-none', 'sidebar-transition-hidden');
                btnfloatingMenu.classList.remove('d-none');
              } else {
                toolsSidebar.classList.remove('d-none', 'sidebar-transition-hidden');
                btnfloatingMenu.classList.add('d-none');
              }
            }

            if (typeof toolsSidebarMedia.addEventListener === 'function') {
              toolsSidebarMedia.addEventListener('change', syncToolsSidebarMode);
            } else {
              toolsSidebarMedia.addListener(syncToolsSidebarMode);
            }

            syncToolsSidebarMode();
        //FIN CONTROL PARA EXPANDIR Y COMPRIMIR LA BARRA DE HERRAMIENTAS

        //Evento para cambiar de pagina con el boton actualizar operaciones de la tabla de personal
        $('#tableListadoPersonal').on('click', '.tableBtnUpdateOperaciones', async function () {
            changeContent('ventanasModalPersonal','ventanaActualizarOperaciones')

            //Obtener atributos data
              let nomina = $(this).data('nomina');
              let nombre = $(this).data('nombre');
              let formDataOperaciones = new FormData;
              formDataOperaciones.append('opcion', 30);
              formDataOperaciones.append('nomina', nomina);
              formDataOperaciones.append('codigoLinea', codigoLinea.value)

              document.getElementById('nominaModalRegistrarUpdate').textContent= nomina;
              document.getElementById('nombreModalRegistrarUpdate').value= nombre;
              $('#alertChipsAgregarOperacion').addClass('d-none');


              let img = document.getElementById('imgmodalListadoPersonal');
              img.onerror = function() {
                  this.onerror = null;
                  this.src = '../img/personal/ok.jpg';
                };

              document.getElementById('imgmodalListadoPersonal').src= (nomina) ? `../img/personal/${nomina}.jpg` : `../img/personal/na.jpg`;

              listadoCertificaciones('selectRegistrarUpdate')
              getOperaciones(nomina);

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

                            actualizarChipsOperacion(data.data, nomina);
                            //console.log("datos operaciones registradas:",data)

                            //Usamos off para eliminar los eventos previos y evitar que se aniden
                             $('#btnChipModalRegistrarUpdate').off('click').on('click', function () {
                                agregarChipOperacion(data.data, nomina);
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

        //Evento para cambiar de pagina con el boton de detalla pc de la tabla pc
        $('#tablePC').on('click', '.btnDetallePC', async function () {
              const noControlCambio = this.dataset.idpc;
              await cargarDetallePC(noControlCambio);

              btnVolverConsultaPC.classList.remove('d-none');
              changeContent('ventanasConsultaPC','ventanaDetallePC')

            return;
        })

        //Detalle de un PC 3M (maquinaria, materia prima o método): misma ventana, contenido propio
        $('#tablePC').on('click', '.btnDetallePC3M', async function () {
              await cargarDetallePC3M(this.dataset.idpc);

              btnVolverConsultaPC.classList.remove('d-none');
              changeContent('ventanasConsultaPC','ventanaDetallePC');
        })

        //Boton para volver a la tabla del modal de registro de personal
        $('#btnBackModalPersonal').on('click', function (){changeContent('ventanasModalPersonal', 'ventanaTablaListadoPersonal')})

        //Evennto para obtener el trabajador seleccionado
        $('#operationsTable').on('change', '.select-operaciones', function(){
            const $select = $(this);
            const $mensaje = $select.siblings('.alerTableOperaciones');
            const nomina = $(this).val();
            const estacion = $(this).data('estacion');

            let formData = new FormData();
            formData.append('nomina',nomina)
            formData.append('idE',estacion)
            formData.append('opcion', 35)
     
            if(!nomina){
              $mensaje.addClass('d-none').text('');
              return;
            }

            fetch("../api/operacionesLinea.php", {
                    method: "POST",
                    body: formData,
                })
                .then((response) => response.text())
                .then((data) => {
                    data= JSON.parse(data)

                    if(data.estatus=='ok'){
                      // 1. Verificar si en el listado de estaciones donde ha sido asignado el trabajador se encuentra la estacion actual
                      const estacion = data.allEst;

                      //Si no existe la etacion
                        if (!estacion) {
                              $mensaje.removeClass('d-none').text('ESTE TRABAJADOR NO CUENTA CON UN REGISTRO ANTERIOR EN ESTE PROCESO');
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
                              $mensaje.removeClass('d-none').text('EL ULTIMO REGISTRO EN ESTA OPERACIÓN ES MAYOR A 30 DÍAS');
                            }

                            else 
                              $mensaje.addClass('d-none').text('');
                          }

                          else{
                            $mensaje.addClass('d-none').text('');
                          }
                    }
                  
                    else{
                        $mensaje.addClass('d-none').text('');
                        console.log(data.error); 
                    }

                }).catch((error) => {
                  console.log(error);
                  $mensaje.addClass('d-none').text('');
            });
        })

        // Botón "+" para agregar en la tabla de lista de operaciones
        $('#modalListaOperaciones').on('click', '#operationsTable .btn-add-worker', function () {
          const estacionId = $(this).data('estacion');
          const $select = $(this).closest('td').find('select');
          const nomina = $select.val();
          const nombreMostrado = $select.find('option:selected').text();
          const nombre = nombreMostrado.includes('(') ? nombreMostrado.split(' (')[0] : nombreMostrado;
          const $mensaje = $select.siblings('.alerTableOperaciones');

          if (!nomina || nomina === '') {
             $mensaje.removeClass('d-none').text('POR FAVOR SELECCIONA UN TRABAJADOR');
            return;
          }

          else{
            $mensaje.addClass('d-none').text('');
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
                   getEstacion(estacionId, null);
                   mostrarTablaPersonal();
                } else {
                      mostrarModalEstatus('Persona ya asignada',
                          'bi-person-check-fill text-warning',
                           data.mensaje,
                          'bi-exclamation-triangle-fill text-warning',
                          true
                      );

                    console.log(data)
                }
            })
            .catch(error => {
                console.error(error);
                mostrarModalEstatus( 'No se pudo completar la operación',
                        'bi-exclamation-octagon-fill text-danger',
                        `La información enviada no pudo procesarse. O se perdio la conexión con el servidor`,
                        'bi-exclamation-circle-fill text-danger',
                        true
                        );
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
                  getEstacion(estacionId, null);
                  mostrarTablaPersonal();
                } else {
                    mostrarModalEstatus(
                          'No se pudo completar la operación',
                          'bi-exclamation-octagon-fill text-danger',
                          `${data.mensaje}`,
                          'bi-exclamation-circle-fill text-danger',
                          true
                    );

                    console.log(data)
                }
            })
            .catch(error => {
                console.error(error);
                mostrarModalEstatus(
                  'No se pudo completar la operación',
                  'bi-exclamation-octagon-fill text-danger',
                  `La información enviada no pudo procesarse. O se perdió la conexión con el servidor`,
                  'bi-exclamation-circle-fill text-danger',
                  true
                );
            });
        });

        //Evento boton eliminar listado de personal
        $('#tableListadoPersonal').on('click', '.btnElimPer', function () {
          const nomina = $(this).data('nomina');
          eliminarPersonal(nomina);
        });

        //Evento para cargar la tabla de listado de puntos de cambio al abrir el modal
        $('#btnMenuTablaPC').on('click', function (){ mostrarTablaPC(); })

        //Filtros del listado de PC: tipo 4M y rango de fechas (se consulta de nuevo al cambiar)
        $('input[name="filtroTipoPC"], #filtroPCDesde, #filtroPCHasta').on('change', function (){ mostrarTablaPC(); })

      // Cargar las formas
      setTimeout(() => {
              loadShapesFromJSON();
      }, 500);

        applyZoom();
    });