    <!-- Sidebar MENU de herramientas -->
    <aside class="tools-sidebar" id="tools-sidebar" aria-label="Herramientas de gestión">
      <div class="tools-sidebar-header">
        <span class="tools-brand-mark"><i class="bi bi-tools"></i></span>
        <div class="tools-brand-copy">
          <strong>SPC</strong>
          <span>puntos de cambio</span>
        </div>
      </div>

      <button type="button" class="tools-sidebar-toggle" id="btnToggleToolsSidebar"
              aria-expanded="false" aria-controls="tools-sidebar" title="Expandir herramientas">
        <i class="bi bi-chevron-right icon-expand" id="iconFullscreen"></i>
        <i class="bi bi-chevron-left icon-collapse"></i>
        <span class="visually-hidden">Expandir o comprimir herramientas</span>
      </button>

      <div class="tools-section-label">Acciones</div>
      <div class="tools-menu">

      <!-- REGISTO DE PERSONAL -->
      <button id="btnMenuRegistrar" class="tool-btn" data-tooltip="Registrar personal" aria-label="Registrar personal" data-bs-toggle="modal" data-bs-target="#modalRegistrarOperador">
        <span class="tool-btn-content">
          <span class="tool-icon"><i class="bi bi-person-plus"></i></span>
          <span class="tool-copy"><strong>Registrar</strong><small>Agregar personal</small></span>
        </span>
      </button>

      <!-- LISTADO DE PERSONAL -->
      <button id="btnMenuPersonal" class="tool-btn" data-tooltip="Listado de personal" aria-label="Listado de personal" data-bs-toggle="modal" data-bs-target="#modalListadoPersonal">
        <span class="tool-btn-content">
          <span class="tool-icon"><i class="bi bi-people"></i></span>
          <span class="tool-copy"><strong>Personal</strong><small>Consultar registros</small></span>
        </span>
      </button>

      <!-- TABLA DE ASIGNACIONES -->
      <button id ="btnMenuAsignaciones" class="tool-btn" data-tooltip="Asignaciones" aria-label="Asignaciones" data-bs-toggle="modal" data-bs-target="#modalListaOperaciones">
        <span class="tool-btn-content">
          <span class="tool-icon"><i class="bi bi-diagram-3"></i></span>
          <span class="tool-copy"><strong>Asignaciones</strong><small>Operaciones y personal</small></span>
        </span>
      </button>

      <!-- ASIGNAR OPERADOR -->
      <button id="btnMenuAsignar" class="tool-btn d-none" data-tooltip="Asignar operador" aria-label="Asignar operador" data-bs-toggle="modal" data-bs-target="#modalAsignarOperador">
        <span class="tool-btn-content">
          <span class="tool-icon"><i class="bi bi-clipboard"></i></span>
          <span class="tool-copy"><strong>Asignar</strong><small>Operador a estación</small></span>
        </span>
      </button>

      <!--CONSULTAR PUNTO DE CAMBIO -->
        <button id="btnMenuTablaPC" class="tool-btn" data-tooltip="Puntos de cambio" aria-label="Puntos de cambio" data-bs-toggle="modal" data-bs-target="#changePointsModal">
          <span class="tool-btn-content">
          <span class="tool-icon"><i class="bi bi-arrow-repeat"></i></span>
          <span class="tool-copy"><strong>Puntos de cambio</strong><small>Consultar PC</small></span>
          </span>
        </button> 

      <!--REGISTRO DE ASISTENCIA -->
      <button id="btnMenuRegistroAs" class="tool-btn" data-tooltip="Registro de asistencia" aria-label="Registro de asistencia" data-bs-toggle="modal" data-bs-target="#attendanceModal">
        <span class="tool-btn-content">
          <span class="tool-icon"><i class="bi bi-check2-square"></i></span>
          <span class="tool-copy"><strong>Asistencia</strong><small>Asistencia de personal</small></span>
        </span>
      </button>

      <!--BOTON PARA REGISTRA/VER PERSONAL DISPOPNIBLE O NO ASIGNADO A UNA ESTACION -->
      <button id ='btnMenuRegiswtroNAD' class="tool-btn d-none" data-tooltip="Personal disponible" aria-label="Personal disponible" data-bs-toggle="modal" data-bs-target="#modalPersonalDisponible">
        <span class="tool-btn-content">
          <span class="tool-icon"><i class="bi bi-person-check"></i></span>
          <span class="tool-copy"><strong>Disponibles</strong><small>Personal sin asignar</small></span>
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

      <button type="button" class="close-sidebar-btn hide-sidebar-btn" id="btncloseSidebar"
              title="Ocultar barra de herramientas" aria-label="Ocultar barra de herramientas">
        <i class="bi bi-eye-slash"></i>
        <span>Ocultar barra</span>
      </button>

      <div class="tools-sidebar-footer">
        <i class="bi bi-info-circle"></i>
        <span>Selecciona una opción para consultar o registrar información.</span>
      </div>
    </aside>

    <!-- ICONO FLOTANTE (aparece cuando menú oculto) -->
    <button class="floating-menu-btn d-none" id="btnfloatingMenu">
        <i class="bi bi-chevron-right"></i>
    </button>