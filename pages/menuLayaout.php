    <!-- Sidebar MENU de herramientas -->
    <div class="tools-sidebar">

      <!--CREAR ESTACION -->
      <button class="tool-btn" 
              data-bs-toggle="tooltip" 
              data-bs-placement="right" 
              title="Crear estaciones">
            <span data-bs-toggle="modal" data-bs-target="#modalAgregarEstacion">
              <i class="bi bi-building"></i>
            <span>Crear</span></span>
      </button>     

      <!-- ASIGNAR OPERADOR -->
      <button class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Asignar operador">
        <span data-bs-toggle="modal" data-bs-target="#modalAsignarOperador">
          <i class="bi bi-person-plus"></i>
          <span>Asignar</span>
        </span>
      </button>

      <!--EDITAR LINEA-->
      <button class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Editar información de la línea" >
        <span data-bs-toggle="modal" data-bs-target="#editarLineaModal">
          <i class="bi bi-pencil card-icon"></i>
          <span>Editar</span>
        </span>
      </button>

      <!--CONSULTAR PUNTO DE CAMBIO -->
      <button class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Punto de cambio">
        <span data-bs-toggle="modal" data-bs-target="#changePointsModal">
        <i class="bi bi-arrow-repeat"></i>
        <span>Cambio</span>
        </span>
      </button>

      <!--REGISTRO DE ASISTENCIA -->
      <button class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Registro de asistencia">
        <span data-bs-toggle="modal" data-bs-target="#attendanceModal">
          <i class="bi bi-check2-square"></i>
          <span>Asistencia</span>
        </span>
      </button>

      <!--BOTON PARA REGISTRA/VER PERSONAL DISPOPNIBLE O NO ASIGNADO A UNA ESTACION -->
      <button class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Ver personal disponible">
        <span data-bs-toggle="modal" data-bs-target="#modalPersonalDisponible">
          <i class="bi bi-people"></i>
          <span>Disponibles</span>
        </span>
      </button>

      <!-- Botón para mostrar modal de error -->
      <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#errorModal">
        <i class="bi bi-exclamation-triangle-fill" style=""></i>
      </button>
    </div>