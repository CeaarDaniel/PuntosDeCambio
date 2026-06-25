    <!-- Sidebar MENU de herramientas -->
    <div class="tools-sidebar" id="tools-sidebar">

      <!-- REGISTO DE PERSONAL -->
      <button id="btnMenuRegistrar" class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Registrar personal">
        <span data-bs-toggle="modal" data-bs-target="#modalRegistrarOperador">
          <i class="bi bi-person-plus"></i>
          <span>Registrar</span>
        </span>
      </button>

      <!-- LISTADO DE PERSONAL -->
      <button id="btnMenuPersonal" class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Listado de personal registrado">
        <span data-bs-toggle="modal" data-bs-target="#modalListadoPersonal">
          <i class="bi bi-people"></i>
          <span>Personal</span>
        </span>
      </button>

      <!-- TABLA DE ASIGNACIONES -->
      <button class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Asignar operador">
        <span data-bs-toggle="modal" data-bs-target="#modalListaOperaciones">
          <i class="bi-diagram-3"></i>
          <span>Asignaciones</span>
        </span>
      </button>

      <!-- ASIGNAR OPERADOR -->
      <button id="btnMenuAsignar" class="tool-btn d-none" data-bs-toggle="tooltip" data-bs-placement="right" title="Asignar operador">
        <span data-bs-toggle="modal" data-bs-target="#modalAsignarOperador">
          <i class="bi-clipboard"></i> <!-- bi-clipboard-check -->
          <span>Asignar</span>
        </span>
      </button>

      <!--CONSULTAR PUNTO DE CAMBIO -->
        <button id="btnMenuTablaPC" class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Punto de cambio">
          <span data-bs-toggle="modal" data-bs-target="#changePointsModal">
          <i class="bi bi-arrow-repeat"></i>
          <span>Cambio</span>
          </span>
        </button> 

      <!--REGISTRO DE ASISTENCIA -->
      <button id="btnMenuRegistroAs" class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Registro de asistencia">
        <span data-bs-toggle="modal" data-bs-target="#attendanceModal">
          <i class="bi bi-check2-square"></i>
          <span>Asistencia</span>
        </span>
      </button>

      <!--BOTON PARA REGISTRA/VER PERSONAL DISPOPNIBLE O NO ASIGNADO A UNA ESTACION -->
      <button id ='btnMenuRegiswtroNAD' class="tool-btn d-none" data-bs-toggle="tooltip" data-bs-placement="right" title="Ver personal disponible">
        <span data-bs-toggle="modal" data-bs-target="#modalPersonalDisponible">
          <i class="bi-person-check"></i>
          <span>Disponibles</span>
        </span>
      </button>

      <!--BOTON PARA MOSTRAR HISTORIAL DE REGISTROS DEL LAYOUT
      <button id="btnHistorialLayout" class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Consultar el acomodo del layout en alguna fecha">
         <span data-bs-toggle="modal" data-bs-target="#historialLayoutModal">
            <i class="bi bi-clock-history"></i>
            <span>Historial</span>
         </span> 
      </button> 
      -->

      <!-- Botón para mostrar modal de error 
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#errorModal">
            <i class="bi bi-exclamation-triangle-fill" style=""></i>
        </button>
      -->
    </div>

    <button class="close-sidebar-btn" id="btncloseSidebar">
        <i class="bi bi-arrows-fullscreen" id="iconFullscreen"></i>
    </button>

    <!-- ICONO FLOTANTE (aparece cuando menú oculto) -->
    <button class="floating-menu-btn d-none" id="btnfloatingMenu">
        <i class="bi-fullscreen-exit"></i>
    </button>