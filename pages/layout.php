<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Layout de Líneas - Sistema PCM</title>
  
  <!-- Bootstrap -->
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="../css/bootstrap-icons.min.css" />

  <!-- Font Awesome (para íconos) -->
  <link href="../css/all.min.css" rel="stylesheet"> 

    <!--Libreria Jquery --> 
  <script src="../scripts/jquery-3.7.1.min.js"></script>


  <!--Custom Css -->
  <link rel="stylesheet" href="../css/layout.css">
</head>

<body>
  <div class="layout-container">
    <!-- Sidebar de herramientas -->
    <div class="tools-sidebar">
      <button class="tool-btn" 
              data-bs-toggle="tooltip" 
              data-bs-placement="right" 
              title="Crear estaciones">
            <span data-bs-toggle="modal" data-bs-target="#modalAgregarEstacion">
              <i class="bi bi-building"></i>
            <span>Crear</span></span>
      </button>     
      <button class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Asignar operador">
        <span data-bs-toggle="modal" data-bs-target="#modalAsignarOperador">
          <i class="bi bi-person-plus"></i>
          <span>Asignar</span>
        </span>
      </button>


      <button class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Editar información de la línea" >
        <span data-bs-toggle="modal" data-bs-target="#modalAsignarOperador">
          <i class="bi bi-pencil card-icon"></i>
          <span>Editar</span>
        </span>
      </button>


      <button class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Punto de cambio">
        <span data-bs-toggle="modal" data-bs-target="#changePointsModal">
        <i class="bi bi-arrow-repeat"></i>
        <span>Cambio</span>
        </span>
      </button>
      <button class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Registro de asistencia">
        <span data-bs-toggle="modal" data-bs-target="#attendanceModal">
          <i class="bi bi-check2-square"></i>
          <span>Asistencia</span>
        </span>
      </button>

      <!-- Opcion para liberar y consultar informacion sobre puntos de cambio pendientes-->
      <button class="tool-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="Liberar punto de cambio">
        <span data-bs-toggle="modal" data-bs-target="#closeControlModal">
          <i class="bi bi-unlock"></i>
          <span>Liberar</span>
        </span>
      </button>

      <!-- Botón para mostrar el modal (opcional) -->
      <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#errorModal">
        <i class="bi bi-exclamation-triangle-fill" style=""></i>
      </button>
    </div>
    
    <!-- Área principal -->
    <div class="layout-main">
      <div class="layout-header">
        <h2 class="layout-title">Línea de Producción A</h2>
        <div class="layout-controls">
          <div class="btn-group">
            <div class="zoom-indicator me-3" id="zoomIndicator">100%</div>
            <button class="btn btn-outline-primary btn-sm" id="zoomOutBtn">
              <i class="bi bi-zoom-out"></i> Alejar
            </button>
            <button class="btn btn-outline-primary btn-sm" id="zoomInBtn">
              <i class="bi bi-zoom-in"></i> Acercar
            </button>
          </div>
          <button class="btn btn-outline-secondary btn-sm" id="snapToGridBtn">
            <i class="bi bi-arrows-move"></i> Ajustar a cuadrícula
          </button>
          <button class="btn btn-success btn-sm" id="saveLayoutBtn">
            <i class="bi bi-floppy"></i> Guardar Layout
          </button>
        </div>
      </div>
      
      <div class="workspace">
        <div class="workspace-grid" id="workspaceGrid">
          <!-- Estaciones se generarán dinámicamente -->
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para detalles de estación -->
  <div class="modal fade station-modal" id="stationModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="station-detail-header station-color-1" id="modalHeader">
          <h5 class="modal-title" id="modalStationName">Estación 1</h5>
        </div>
        <div class="station-detail-body">
          <div class="detail-item">
            <span>Operador:</span>
            <strong id="modalOperator">Juan Pérez</strong>
          </div>
          <div class="detail-item">
            <span>Estado:</span>
            <span class="badge bg-success" id="modalStatus">Activo</span>
          </div>
          <div class="detail-item">
            <span>Certificación:</span>
            <span id="modalCertification">Proceso X - Liberado</span>
          </div>
          <div class="detail-item">
            <span>Último cambio:</span>
            <span id="modalLastChange">15/11/2025</span>
          </div>
          <div class="mt-3 d-grid gap-2">
            <button class="btn btn-primary btn-sm">
              <i class="bi bi-pencil"></i> Editar
            </button>
            <button class="btn btn-warning btn-sm">
              <i class="bi bi-arrow-repeat"></i> Punto de Cambio
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal alerta/error-->
  <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content shadow-lg border-0" style="border-radius: 15px;">

        <!-- Header -->
        <div class="modal-header text-white" style="background: linear-gradient(135deg, #dc3545, #dc3545); border-top-left-radius: 15px; border-top-right-radius: 15px;">
          <h5 class="modal-title d-flex align-items-center gap-2" id="errorModalLabel" style="font-size: clamp(18px, 2vw, 22px);">
            <i class="bi bi-exclamation-octagon-fill fs-3"></i>
            Error en la asignación
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <!-- Body -->
        <div class="modal-body text-center p-2">
          <!-- Mensaje -->
          <p class="text-muted" style="font-size: clamp(14px, 2vw, 18px);">
            No es posible asignar el operador a esta estación ya que no cuenta con registro
            para la capacitación o certificado requerido para el proceso.
          </p>

          <!-- Icono principal -->
          <div class="my-2">
            <i class="bi bi-x-circle text-danger" style="font-size: clamp(45px, 6vw, 70px);"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!--Modal agregar una estacion -->
  <div class="modal fade" id="modalAgregarEstacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Agregar nueva estación</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Fin formulario -->
            <form class="form-body" id="stationForm">
              <!-- Sección: Información de la Estación -->
              <div class="form-section">
                <h3 class="section-title">
                  <i class="bi bi-cpu"></i>
                  Información de la Estación
                </h3>
                
                <div class="mb-3">
                  <label for="stationName" class="form-label required-field">
                    <i class="bi bi-tag"></i>
                    Nombre de la Estación/Proceso
                  </label>
                  <input 
                    type="text" 
                    class="form-control form-control-custom" 
                    id="stationName" 
                    placeholder="Ej: Moldeo De Uretano Y"
                    required
                  >
                  <div class="form-help">Nombre descriptivo para identificar la estación o proceso</div>
                </div>
              </div>

              <!-- Sección: Descripción -->
              <div class="form-section">
                <h3 class="section-title">
                  <i class="bi bi-chat-left-text"></i>
                  Descripción y Comentarios
                </h3>
                
                <div class="mb-3">
                  <label for="stationDescription" class="form-label">
                    <i class="bi bi-pencil"></i>
                    Comentarios/Descripción
                  </label>
                  <textarea 
                    class="form-control form-control-custom form-textarea" 
                    id="stationDescription" 
                    placeholder="Describe las actividades, procedimientos específicos, consideraciones especiales o comentarios relevantes para esta estación..."
                    rows="4"
                  ></textarea>
                  <div class="form-help">Opcional: Detalla el proceso, herramientas utilizadas o instrucciones especiales</div>
                </div>
              </div>

              <!-- Sección: Certificaciones -->
              <div class="form-section">
                <h3 class="section-title">
                  <i class="bi bi-award"></i>
                  Requerimientos de Certificación
                </h3>
                
                <div class="mb-3">
                  <label for="requiredCertification" class="form-label required-field">
                    <i class="bi bi-shield-check"></i>
                    Certificación/Capacitación Requerida
                  </label>
                  <select class="form-control form-control-custom" id="requiredCertification" required>
                    <option value="">Selecciona una certificación...</option>
                    <option value="cert-proceso-a">Proceso A</option>
                    <option value="cert-proceso-b">Proceso B</option>
                    <option value="cert-proceso-c">Proceso C</option>
                    <option value="cert-calidad">Control de Calidad</option>
                    <option value="cert-seguridad">Seguridad Industrial</option>
                    <option value="cert-maquinaria">Operación de Maquinaria Especializada</option>
                    <option value="none">No requiere certificación</option>
                  </select>
                  <div class="form-help">Selecciona la certificación mínima requerida para operar esta estación</div>
                </div>
              </div>
            </form>
          <!-- Fin formulario -->
          <div class="d-flex justify-content-end mt-2 ">
            <button type="button" class="btn btn-secondary mx-2" data-bs-dismiss="modal">
                <i class="bi bi-x-circle"></i> Cancelar
              </button>
            <button type="button" class="btn btn-primary-custom mx-2">
              <i class="bi bi-check-circle"></i>Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!--Modal agregar operador a una estacion -->
  <div class="modal fade" id="modalAsignarOperador" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Asignar operado a Una Estacion</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <!-- Fin formulario -->
              <form class="form-body" id="assignmentForm">
                <!-- Header de la sección -->
                <div class="form-section">
                  <h3 class="section-title">
                    <i class="bi bi-person-plus"></i>
                    Asignar Operador a Estación
                  </h3>
                  <p class="text-muted mb-4">Complete la información para asignar un operador a una estación específica</p>
                </div>

                <!-- Información del Operador -->
                <div class="form-section">
                  <h4 class="section-subtitle">
                    <i class="bi bi-person-badge"></i>
                    Datos del Operador
                  </h4>
                  
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="employeeId" class="form-label required-field">
                        <i class="bi bi-clock"></i>
                        No. Reloj / ID Empleado
                      </label>
                      <div class="input-group-custom">
                        <input 
                          type="text" 
                          class="form-control form-control-custom" 
                          id="employeeId" 
                          placeholder="Ej: EMP-0256"
                          required
                        >
                        <button type="button" class="input-icon" id="searchEmployee">
                          <i class="bi bi-search"></i>
                        </button>
                      </div>
                      <div class="form-help">Ingresa el número de reloj o ID único del empleado</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                      <label for="employeeName" class="form-label required-field">
                        <i class="bi bi-person"></i>
                        Nombre del Operador
                      </label>
                      <div class="input-group-custom">
                        <input 
                          type="text" 
                          class="form-control form-control-custom" 
                          id="employeeName" 
                          placeholder="Selecciona o busca un operador"
                          readonly
                          required
                        >
                        <button type="button" class="input-icon">
                          <i class="bi bi-people"></i>
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Vista previa del operador seleccionado -->
                  <div id="operatorPreview" class="operator-preview d-none">
                    <div class="d-flex align-items-center p-3 bg-light rounded">
                      <div class="operator-avatar bg-primary text-white">JD</div>
                      <div class="ms-3 flex-grow-1">
                        <h6 class="mb-1" id="previewName">Juan Domínguez</h6>
                        <p class="mb-0 text-muted small" id="previewDetails">ID: EMP-0256 | 3 Certificaciones</p>
                      </div>
                      <button type="button" class="btn btn-sm btn-outline-danger" id="clearOperator">
                        <i class="bi bi-x"></i> Cambiar
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Información de la Asignación -->
                <div class="form-section">
                  <h4 class="section-subtitle">
                    <i class="bi bi-geo-alt"></i>
                    Detalles de la Asignación
                  </h4>
                  
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="stationSelect" class="form-label required-field">
                        <i class="bi bi-cpu"></i>
                        Estación
                      </label>
                      <select class="form-control form-control-custom" id="stationSelect" required>
                        <option value="">Selecciona una estación...</option>
                        <option value="station-1">Estación 1 - Ensamblaje Principal</option>
                        <option value="station-2">Estación 2 - Control de Calidad</option>
                        <option value="station-3">Estación 3 - Embalaje Final</option>
                        <option value="station-4">Estación 4 - Pruebas Eléctricas</option>
                        <option value="station-5">Estación 5 - Soldadura</option>
                      </select>
                      <div class="form-help">Selecciona la estación donde se asignará el operador</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                      <label for="assignmentDate" class="form-label required-field">
                        <i class="bi bi-calendar"></i>
                        Fecha de Asignación
                      </label>
                      <input 
                        type="date" 
                        class="form-control form-control-custom" 
                        id="assignmentDate" 
                        required
                      >
                      <div class="form-help">Fecha en la que inicia la asignación</div>
                    </div>
                  </div>

                  <!-- Información de la estación seleccionada -->
                  <div id="stationInfo" class="station-info d-none mt-3">
                    <div class="alert alert-info">
                      <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle me-2"></i>
                        <div>
                          <strong id="stationName">Estación 1 - Ensamblaje Principal</strong>
                          <div class="small" id="stationDetails">
                            Certificación requerida: <span class="badge bg-success">Proceso A</span> | 
                            Operador actual: <span class="text-muted">Vacante</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Configuración Adicional -->
                <div class="form-section">
                  <h4 class="section-subtitle">
                    <i class="bi bi-sliders"></i>
                    Configuración Adicional
                  </h4>
                  
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="shiftSelect" class="form-label">
                        <i class="bi bi-clock-history"></i>
                        Turno
                      </label>
                      <select class="form-control form-control-custom" id="shiftSelect">
                        <option value="morning">Diurno</option>
                        <option value="night">Nocturno</option>
                      </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                      <label for="assignmentType" class="form-label">
                        <i class="bi bi-arrow-repeat"></i>
                        Tipo de Asignación
                      </label>
                      <select class="form-control form-control-custom" id="assignmentType">
                        <option value="permanent">Permanente</option>
                        <option value="temporary">Temporal</option>
                        <option value="replacement">Reemplazo</option>
                        <option value="training">Punto de cambio</option>
                      </select>
                    </div>
                  </div>

                  <!-- Para asignaciones temporales -->
                  <div class="row" id="temporalFields" style="display: none;">
                    <div class="col-md-6 mb-3">
                      <label for="endDate" class="form-label">
                        <i class="bi bi-calendar-x"></i>
                        Fecha de Finalización
                      </label>
                      <input 
                        type="date" 
                        class="form-control form-control-custom" 
                        id="endDate"
                      >
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="reason" class="form-label">
                        <i class="bi bi-chat-left-text"></i>
                        Motivo
                      </label>
                      <input 
                        type="text" 
                        class="form-control form-control-custom" 
                        id="reason"
                        placeholder="Ej: Cubrir vacaciones, proyecto especial..."
                      >
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="assignmentNotes" class="form-label">
                      <i class="bi bi-sticky"></i>
                      Observaciones
                    </label>
                    <textarea 
                      class="form-control form-control-custom" 
                      id="assignmentNotes" 
                      rows="3"
                      placeholder="Notas adicionales sobre esta asignación..."
                    ></textarea>
                  </div>
                </div>

                <!-- Verificación de Certificaciones -->
                <div class="form-section">
                  <div class="alert alert-warning">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-shield-check me-2"></i>
                      <div>
                        <strong>Verificación de Certificaciones</strong>
                        <div id="certificationStatus" class="small mt-1">
                          Verificando certificaciones requeridas...
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
            <!-- Fin formulario -->
            <div class="d-flex justify-content-end mt-2 ">
              <button type="button" class="btn btn-secondary mx-2" data-bs-dismiss="modal">
                <i class="bi bi-x-circle"></i> Cancelar
                </button>
              <button type="button" class="btn btn-primary-custom mx-2">
                <i class="bi bi-check-circle"></i>Guardar
              </button>
            </div>
          </div>
        </div>
      </div>
  </div>

  <!-- Modal de Puntos de Cambio -->
  <div class="modal fade" id="changePointsModal" tabindex="-1" aria-labelledby="changePointsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
        <!-- Header del Modal -->
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color), #1a2530); color: white;">
          <div class="d-flex align-items-center">
            <i class="bi bi-arrow-left-right me-2" style="font-size: 1.5rem;"></i>
            <h5 class="modal-title" id="changePointsModalLabel">Seguimiento de Puntos de Cambio</h5>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <!-- Body del Modal -->
        <div class="modal-body p-4">
          <!-- Barra de herramientas -->
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex gap-2">
              <div class="input-group" style="width: 300px;">
                <input type="text" class="form-control" placeholder="Buscar puntos de cambio...">
                <button class="btn btn-outline-secondary" type="button">
                  <i class="bi bi-search"></i>
                </button>
              </div>
              <select class="form-select" style="width: 200px;">
                <option selected>Filtrar por estado</option>
                <option>En progreso</option>
                <option>Completado</option>
                <option>Cancelado</option>
              </select>
              <select class="form-select" style="width: 200px;">
                <option selected>Filtrar por tipo</option>
                <option>Programado</option>
                <option>Inesperado</option>
                <option>Especial</option>
              </select>
            </div>
          </div>

          <!-- Tarjetas de resumen 
          <div class="row mb-4">
            <div class="col-md-3">
              <div class="card bg-primary text-white">
                <div class="card-body">
                  <div class="d-flex justify-content-between">
                    <div>
                      <h6 class="card-title">Total</h6>
                      <h3 class="mb-0">24</h3>
                    </div>
                    <i class="bi bi-arrow-left-right" style="font-size: 2rem; opacity: 0.7;"></i>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-warning text-dark">
                <div class="card-body">
                  <div class="d-flex justify-content-between">
                    <div>
                      <h6 class="card-title">En Progreso</h6>
                      <h3 class="mb-0">5</h3>
                    </div>
                    <i class="bi bi-clock" style="font-size: 2rem; opacity: 0.7;"></i>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-success text-white">
                <div class="card-body">
                  <div class="d-flex justify-content-between">
                    <div>
                      <h6 class="card-title">Completados</h6>
                      <h3 class="mb-0">17</h3>
                    </div>
                    <i class="bi bi-check-circle" style="font-size: 2rem; opacity: 0.7;"></i>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-info text-white">
                <div class="card-body">
                  <div class="d-flex justify-content-between">
                    <div>
                      <h6 class="card-title">Programados</h6>
                      <h3 class="mb-0">12</h3>
                    </div>
                    <i class="bi bi-calendar-check" style="font-size: 2rem; opacity: 0.7;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          -->

          <!-- Tabla de Puntos de Cambio -->
          <div class="card">
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                  <thead class="table-dark">
                    <tr>
                      <th>ID</th>
                      <th>Estación</th>
                      <th>Operador Anterior</th>
                      <th>Operador Nuevo</th>
                      <th>Fecha</th>
                      <th>Tipo</th>
                      <th>Estado</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td><strong>PC-001</strong></td>
                      <td>Estación 02</td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">JP</div>
                          <span>Juan Pérez</span>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">MG</div>
                          <span>María González</span>
                        </div>
                      </td>
                      <td>15/11/2025</td>
                      <td><span class="badge bg-info">Programado</span></td>
                      <td><span class="badge bg-warning">En progreso</span></td>
                      <td>
                        <div class="btn-group">
                          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Seguimiento">
                            <i class="bi bi-clipboard-check"></i>
                          </button>
                          <button class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Detalles">
                            <i class="bi bi-eye"></i>
                          </button>
                          <button class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="Editar">
                            <i class="bi bi-pencil"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td><strong>PC-002</strong></td>
                      <td>Estación 07</td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">RS</div>
                          <span>Roberto Sánchez</span>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">CR</div>
                          <span>Carlos Ruiz</span>
                        </div>
                      </td>
                      <td>10/03/2025</td>
                      <td><span class="badge bg-warning">Inesperado</span></td>
                      <td><span class="badge bg-success">Completado</span></td>
                      <td>
                        <div class="btn-group">
                          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Ver">
                            <i class="bi bi-eye"></i>
                          </button>
                          <button class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Reporte">
                            <i class="bi bi-file-earmark-text"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td><strong>PC-003</strong></td>
                      <td>Estación 05</td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">AL</div>
                          <span>Ana López</span>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">LF</div>
                          <span>Luis Fernández</span>
                        </div>
                      </td>
                      <td>08/11/2025</td>
                      <td><span class="badge bg-secondary">Especial</span></td>
                      <td><span class="badge bg-success">Completado</span></td>
                      <td>
                        <div class="btn-group">
                          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Ver">
                            <i class="bi bi-eye"></i>
                          </button>
                          <button class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Reporte">
                            <i class="bi bi-file-earmark-text"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td><strong>PC-004</strong></td>
                      <td>Estación 12</td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">JG</div>
                          <span>Jorge Gómez</span>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">MP</div>
                          <span>Marta Paredes</span>
                        </div>
                      </td>
                      <td>05/11/2025</td>
                      <td><span class="badge bg-info">Programado</span></td>
                      <td><span class="badge bg-secondary">Cancelado</span></td>
                      <td>
                        <div class="btn-group">
                          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Ver">
                            <i class="bi bi-eye"></i>
                          </button>
                          <button class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="Reactivar">
                            <i class="bi bi-arrow-clockwise"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Paginación -->
          <nav class="mt-4">
            <ul class="pagination justify-content-center">
              <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1">Anterior</a>
              </li>
              <li class="page-item active"><a class="page-link" href="#">1</a></li>
              <li class="page-item"><a class="page-link" href="#">2</a></li>
              <li class="page-item"><a class="page-link" href="#">3</a></li>
              <li class="page-item">
                <a class="page-link" href="#">Siguiente</a>
              </li>
            </ul>
          </nav>
        </div>
        
        <!-- Footer del Modal -->
        <div class="modal-footer">
          <div class="me-auto">
            <span class="text-muted">Mostrando 4 de 24 registros</span>
          </div>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary">
            <i class="bi bi-download"></i> Exportar
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Registro de Asistencia -->
  <div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
        <!-- Header del Modal -->
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color), #1a2530); color: white;">
          <div class="d-flex align-items-center">
            <i class="bi bi-clipboard-check me-2" style="font-size: 1.5rem;"></i>
            <h5 class="modal-title" id="attendanceModalLabel">Registro de Asistencia - Línea de Producción</h5>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <!-- Body del Modal -->
        <div class="modal-body p-4">
          <!-- Barra de herramientas superior -->
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="d-flex align-items-center">
                <div class="input-group" style="width: 300px;">
                  <span class="input-group-text">
                    <i class="bi bi-building"></i>
                  </span>
                  <select class="form-control form-control-custom">
                    <option selected>Estacion A</option>
                    <option>Estacion B</option>
                    <option>Estacion C</option>
                    <option>Estacion D</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex justify-content-end align-items-center">
                <button class="btn btn-outline-primary me-2">
                  <i class="bi bi-printer"></i> Imprimir
                </button>
                <button class="btn btn-outline-secondary me-2">
                  <i class="bi bi-download"></i> Exportar
                </button>
                <div class="btn-group">
                  <button class="btn btn-outline-secondary">
                    <i class="bi bi-chevron-left"></i>
                  </button>
                  <button class="btn btn-outline-dark fw-bold" style="min-width: 180px;">
                    <i class="bi bi-calendar3 me-2"></i>15 de Noviembre, 2025
                  </button>
                  <button class="btn btn-outline-secondary">
                    <i class="bi bi-chevron-right"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Tarjetas de resumen 
            <div class="row mb-4">
              <div class="col-md-3">
                <div class="card bg-success text-white">
                  <div class="card-body">
                    <div class="d-flex justify-content-between">
                      <div>
                        <h6 class="card-title">Asistencia</h6>
                        <h3 class="mb-0">12</h3>
                        <small>78% del personal</small>
                      </div>
                      <i class="bi bi-check-circle" style="font-size: 2.5rem; opacity: 0.7;"></i>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="card bg-warning text-dark">
                  <div class="card-body">
                    <div class="d-flex justify-content-between">
                      <div>
                        <h6 class="card-title">Permisos</h6>
                        <h3 class="mb-0">2</h3>
                        <small>13% del personal</small>
                      </div>
                      <i class="bi bi-clock" style="font-size: 2.5rem; opacity: 0.7;"></i>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="card bg-danger text-white">
                  <div class="card-body">
                    <div class="d-flex justify-content-between">
                      <div>
                        <h6 class="card-title">Faltas</h6>
                        <h3 class="mb-0">1</h3>
                        <small>7% del personal</small>
                      </div>
                      <i class="bi bi-x-circle" style="font-size: 2.5rem; opacity: 0.7;"></i>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="card bg-info text-white">
                  <div class="card-body">
                    <div class="d-flex justify-content-between">
                      <div>
                        <h6 class="card-title">Vacaciones</h6>
                        <h3 class="mb-0">0</h3>
                        <small>0% del personal</small>
                      </div>
                      <i class="bi bi-sun" style="font-size: 2.5rem; opacity: 0.7;"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          -->

          <!-- Tabla de Asistencia -->
          <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
              <h6 class="mb-0">
                <i class="bi bi-list-check me-2"></i>
                Detalle de Asistencia por Estación
              </h6>
              <div>
                <span class="badge bg-light text-dark me-2">Total: 15 operadores</span>
                <button class="btn btn-success btn-sm">
                  <i class="bi bi-check-lg"></i> Guardar Cambios
                </button>
              </div>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th width="80">Estación</th>
                      <th>Operador</th>
                      <th width="200">Estado</th>
                      <th width="300">Asistencia</th>
                      <th>Comentarios</th>
                      <th width="120" class="text-center">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>
                        <div class="station-badge bg-primary text-white rounded text-center py-1">
                          <strong>01</strong>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-size: 0.9rem;">
                            JP
                          </div>
                          <div>
                            <div class="fw-bold">Juan Pérez</div>
                            <small class="text-muted">ID: EMP-001</small>
                          </div>
                        </div>
                      </td>
                      <td>
                        <span class="badge bg-success">Activo</span>
                      </td>
                      <td>
                        <select class="form-control form-control-custom attendance-status" data-employee="EMP-001">
                          <option value="present" selected>✅ Asistió - Puntual</option>
                          <option value="present-late">🟡 Asistió - Tardanza</option>
                          <option value="permission">🟢 Permiso Autorizado</option>
                          <option value="permission-medical">🏥 Permiso Médico</option>
                          <option value="absence">❌ Falta Injustificada</option>
                          <option value="vacation">🏖️ Vacaciones</option>
                          <option value="training">📚 Capacitación</option>
                          <option value="other">⚪ Otro</option>
                        </select>
                      </td>
                      <td>
                        <input type="text" class="form-control form-control-custom" placeholder="Observaciones..." value="">
                      </td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Ver historial">
                          <i class="bi bi-clock-history"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div class="station-badge bg-success text-white rounded text-center py-1">
                          <strong>02</strong>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-size: 0.9rem;">
                            MG
                          </div>
                          <div>
                            <div class="fw-bold">María González</div>
                            <small class="text-muted">ID: EMP-002</small>
                          </div>
                        </div>
                      </td>
                      <td>
                        <span class="badge bg-success">Activo</span>
                      </td>
                      <td>
                        <select class="form-control form-control-custom attendance-status" data-employee="EMP-002">
                          <option value="present">✅ Asistió - Puntual</option>
                          <option value="present-late">🟡 Asistió - Tardanza</option>
                          <option value="permission" selected>🟢 Permiso Autorizado</option>
                          <option value="permission-medical">🏥 Permiso Médico</option>
                          <option value="absence">❌ Falta Injustificada</option>
                          <option value="vacation">🏖️ Vacaciones</option>
                          <option value="training">📚 Capacitación</option>
                          <option value="other">⚪ Otro</option>
                        </select>
                      </td>
                      <td>
                        <input type="text" class="form-control form-control-custom" placeholder="Observaciones..." value="Consulta médica programada">
                      </td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Ver historial">
                          <i class="bi bi-clock-history"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div class="station-badge bg-warning text-dark rounded text-center py-1">
                          <strong>03</strong>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-size: 0.9rem;">
                            CR
                          </div>
                          <div>
                            <div class="fw-bold">Carlos Ruiz</div>
                            <small class="text-muted">ID: EMP-003</small>
                          </div>
                        </div>
                      </td>
                      <td>
                        <span class="badge bg-success">Activo</span>
                      </td>
                      <td>
                        <select class="form-control form-control-custom attendance-status" data-employee="EMP-003">
                          <option value="present">✅ Asistió - Puntual</option>
                          <option value="present-late">🟡 Asistió - Tardanza</option>
                          <option value="permission">🟢 Permiso Autorizado</option>
                          <option value="permission-medical">🏥 Permiso Médico</option>
                          <option value="absence" selected>❌ Falta Injustificada</option>
                          <option value="vacation">🏖️ Vacaciones</option>
                          <option value="training">📚 Capacitación</option>
                          <option value="other">⚪ Otro</option>
                        </select>
                      </td>
                      <td>
                        <input type="text" class="form-control form-control-custom" placeholder="Observaciones..." value="">
                      </td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Ver historial">
                          <i class="bi bi-clock-history"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div class="station-badge bg-info text-white rounded text-center py-1">
                          <strong>04</strong>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-size: 0.9rem;">
                            AL
                          </div>
                          <div>
                            <div class="fw-bold">Ana López</div>
                            <small class="text-muted">ID: EMP-004</small>
                          </div>
                        </div>
                      </td>
                      <td>
                        <span class="badge bg-success">Activo</span>
                      </td>
                      <td>
                        <select class="form-control form-control-custom attendance-status" data-employee="EMP-004">
                          <option value="present" selected>✅ Asistió - Puntual</option>
                          <option value="present-late">🟡 Asistió - Tardanza</option>
                          <option value="permission">🟢 Permiso Autorizado</option>
                          <option value="permission-medical">🏥 Permiso Médico</option>
                          <option value="absence">❌ Falta Injustificada</option>
                          <option value="vacation">🏖️ Vacaciones</option>
                          <option value="training">📚 Capacitación</option>
                          <option value="other">⚪ Otro</option>
                        </select>
                      </td>
                      <td>
                        <input type="text" class="form-control form-control-custom" placeholder="Observaciones..." value="">
                      </td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Ver historial">
                          <i class="bi bi-clock-history"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div class="station-badge bg-secondary text-white rounded text-center py-1">
                          <strong>05</strong>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-size: 0.9rem;">
                            LF
                          </div>
                          <div>
                            <div class="fw-bold">Luis Fernández</div>
                            <small class="text-muted">ID: EMP-005</small>
                          </div>
                        </div>
                      </td>
                      <td>
                        <span class="badge bg-success">Activo</span>
                      </td>
                      <td>
                        <select class="form-control form-control-custom attendance-status" data-employee="EMP-005">
                          <option value="present" selected>✅ Asistió - Puntual</option>
                          <option value="present-late">🟡 Asistió - Tardanza</option>
                          <option value="permission">🟢 Permiso Autorizado</option>
                          <option value="permission-medical">🏥 Permiso Médico</option>
                          <option value="absence">❌ Falta Injustificada</option>
                          <option value="vacation">🏖️ Vacaciones</option>
                          <option value="training">📚 Capacitación</option>
                          <option value="other">⚪ Otro</option>
                        </select>
                      </td>
                      <td>
                        <input type="text" class="form-control form-control-custom" placeholder="Observaciones..." value="">
                      </td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Ver historial">
                          <i class="bi bi-clock-history"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Resumen rápido -->
          <div class="row mt-4">
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Resumen del Día
                  </h6>
                </div>
                <div class="card-body">
                  <div class="row text-center">
                    <div class="col">
                      <div class="h4 text-success">12</div>
                      <small class="text-muted">Presentes</small>
                    </div>
                    <div class="col">
                      <div class="h4 text-warning">2</div>
                      <small class="text-muted">Permisos</small>
                    </div>
                    <div class="col">
                      <div class="h4 text-danger">1</div>
                      <small class="text-muted">Faltas</small>
                    </div>
                    <div class="col">
                      <div class="h4 text-info">0</div>
                      <small class="text-muted">Vacaciones</small>
                    </div>
                    <div class="col">
                      <div class="h4 text-primary">87%</div>
                      <small class="text-muted">Asistencia</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">
                    <i class="bi bi-lightning-charge me-2"></i>
                    Acciones Rápidas
                  </h6>
                </div>
                <div class="card-body">
                  <div class="d-grid gap-2 d-md-flex">
                    <button class="btn btn-outline-primary">
                      <i class="bi bi-check-all"></i> Marcar Todos como Presentes
                    </button>
                    <button class="btn btn-outline-warning">
                      <i class="bi bi-clock"></i> Registrar Tardanzas Masivas
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Footer del Modal -->
        <div class="modal-footer">
          <div class="me-auto">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="sendNotification">
              <label class="form-check-label" for="sendNotification">
                Enviar notificación de cambios
              </label>
            </div>
          </div>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary">
            <i class="bi bi-floppy"></i> Guardar Cambios
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Control de Cambio -->
  <div class="modal fade" id="changeControlModal" tabindex="-1" aria-labelledby="changeControlModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <!-- Header del Modal -->
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color), #1a2530); color: white;">
          <div class="d-flex align-items-center">
            <i class="bi bi-arrow-repeat me-2" style="font-size: 1.5rem;"></i>
            <h5 class="modal-title" id="changeControlModalLabel">Control de Cambio de Operador</h5>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <!-- Body del Modal -->
        <div class="modal-body p-0">
          <!-- Barra de progreso -->
          <div class="progress-container px-4 pt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div class="step-indicator active" data-step="1">
                <div class="step-circle">1</div>
                <span class="step-label">Planeación</span>
              </div>
              <div class="step-connector"></div>
              <div class="step-indicator" data-step="2">
                <div class="step-circle">2</div>
                <span class="step-label">Implementación</span>
              </div>
              <div class="step-connector"></div>
              <div class="step-indicator" data-step="3">
                <div class="step-circle">3</div>
                <span class="step-label">Verificación</span>
              </div>
              <div class="step-connector"></div>
              <div class="step-indicator" data-step="4">
                <div class="step-circle">4</div>
                <span class="step-label">Confirmación</span>
              </div>
            </div>
          </div>

          <!-- Contenido Paginado -->
          <div class="tab-content p-4" id="changeControlTabs">
            <!-- Paso 1: Información General -->
            <div class="tab-pane fade show active" id="step1" role="tabpanel">
              <div class="card">
                <div class="card-header bg-primary text-white">
                  <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Información General del Cambio
                  </h6>
                </div>
                <div class="card-body">
                  <div class="row mb-4">
                    <div class="col-md-6">
                      <label for="controlNo" class="form-label fw-bold">Control de Cambio No.</label>
                      <input type="text" class="form-control form-control-custom" id="controlNo" placeholder="CC-2025-001">
                    </div>

                    <div class="col-md-3">
                      <label for="fecha" class="form-label fw-bold">Fecha</label>
                      <input type="date" class="form-control form-control-custom" id="fecha">
                    </div>
                    <div class="col-md-3">
                      <label for="hora" class="form-label fw-bold">Hora</label>
                      <input type="time" class="form-control form-control-custom" id="hora">
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="nombreOperador" class="form-label fw-bold">Nombre del Operador</label>
                        <div class="input-group-custom">
                          <input type="text" class="form-control form-control-custom" id="nombreOperador" placeholder="Buscar operador...">
                          <button class="input-icon" type="button">
                            <i class="bi bi-search"></i>
                          </button>
                        </div>
                      </div>

                      <div class="mb-3">
                        <label class="form-label fw-bold">Motivo del Cambio</label>
                        <div class="form-check mb-2">
                          <input class="form-check-input" type="checkbox" value="nuevo_sin_experiencia" id="checkNuevoSinExperiencia">
                          <label class="form-check-label" for="checkNuevoSinExperiencia">
                            <i class="bi bi-person-plus me-1"></i> Nuevo / sin experiencia
                          </label>
                        </div>
                        <div class="form-check mb-2">
                          <input class="form-check-input" type="checkbox" value="despues_largo_periodo" id="checkLargoPeriodo">
                          <label class="form-check-label" for="checkLargoPeriodo">
                            <i class="bi bi-clock-history me-1"></i> Después de un largo periodo
                          </label>
                        </div>
                        <div class="form-check mb-2">
                          <input class="form-check-input" type="checkbox" value="otro" id="checkOtro">
                          <label class="form-check-label" for="checkOtro">
                            <i class="bi bi-three-dots me-1"></i> Otro
                          </label>
                        </div>
                      </div>

                      <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de Cambio</label>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input" type="radio" name="tipoCambio" id="checkPlaneado" value="planeado">
                          <label class="form-check-label" for="checkPlaneado">
                            <i class="bi bi-calendar-check me-1"></i> Planeado
                          </label>
                        </div>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input" type="radio" name="tipoCambio" id="checkInesperado" value="inesperado">
                          <label class="form-check-label" for="checkInesperado">
                            <i class="bi bi-exclamation-triangle me-1"></i> Inesperado
                          </label>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="proceso" class="form-label fw-bold">Proceso</label>
                        <select class="form-control form-control-custom" id="proceso">
                          <option value="">Seleccionar proceso</option>
                          <option value="ensamblaje">Ensamblaje</option>
                          <option value="calidad">Control de Calidad</option>
                          <option value="empaque">Empaque</option>
                          <option value="pruebas">Pruebas</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label for="equipo" class="form-label fw-bold">Equipo</label>
                        <input type="text" class="form-control form-control-custom" id="equipo" placeholder="Identificación del equipo">
                      </div>
                      <div class="mb-3">
                        <label for="estacion" class="form-label fw-bold">Estación</label>
                        <input class="form-control form-control-custom" 
                              type="text" 
                              id="estacion" 
                              name="estacion" 
                              value="estacion" readonly>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Paso 2: Capacitacion -->
            <div class="tab-pane fade" id="step2" role="tabpanel">
              <div class="card">
                <div class="card-header bg-info text-white">
                  <h6 class="mb-0">
                    <i class="bi bi-book me-2"></i>
                    Elementos de Capacitación
                  </h6>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                      <thead class="table-light">
                        <tr>
                          <th rowspan="2" class="align-middle" style="width: 10%;">Tipo</th>
                          <th colspan="5" class="bg-primary text-white">Capacitación</th>
                          <th colspan="3" class="bg-success text-white">Información</th>
                        </tr>
                        <tr>
                          <th class="bg-primary text-white">Proced. de operación</th>
                          <th class="bg-primary text-white">Kakotoras</th>
                          <th class="bg-primary text-white">Prohibiciones</th>
                          <th class="bg-primary text-white">Puntos de calidad</th>
                          <th class="bg-primary text-white">Detalles de la ubicación del cambio</th>
                          <th class="bg-success text-white">Condición actual de línea</th>
                          <th class="bg-success text-white">Defectos presentados</th>
                          <th class="bg-success text-white">Items de seguridad</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td class="fw-bold"></td>
                          <td><div class="form-check form-check-inline justify-content-center"><input class="form-check-input" type="radio" name="capacitacion1" id="cap1_1"></div></td>
                          <td><div class="form-check form-check-inline justify-content-center"><input class="form-check-input" type="radio" name="capacitacion2" id="cap1_2"></div></td>
                          <td><div class="form-check form-check-inline justify-content-center"><input class="form-check-input" type="radio" name="capacitacion3" id="cap1_3"></div></td>
                          <td><div class="form-check form-check-inline justify-content-center"><input class="form-check-input" type="radio" name="capacitacion4" id="cap1_4"></div></td>
                          <td><div class="form-check form-check-inline justify-content-center"><input class="form-check-input" type="radio" name="capacitacion5" id="cap1_5"></div></td>
                          <td><div class="form-check form-check-inline justify-content-center"><input class="form-check-input" type="radio" name="capacitacion6" id="cap1_6"></div></td>
                          <td><div class="form-check form-check-inline justify-content-center"><input class="form-check-input" type="radio" name="capacitacion7" id="cap1_7"></div></td>
                          <td><div class="form-check form-check-inline justify-content-center"><input class="form-check-input" type="radio" name="capacitacion8" id="cap1_8"></div></td>
                        </tr>
                        <!--
                          <tr>
                            <td class="fw-bold">Refuerzo</td>
                            <td><div class="form-check form-check-inline justify-content-center"><input class="form-check-input" type="radio" name="capacitacion1_ref" id="cap2_1"></div></td>
                            <td><div class="form-check form-check-inline justify-content-center"><input class="form-check-input" type="radio" name="capacitacion2_ref" id="cap2_2"></div></td>
                            <td><div class="form-check form-check-inline justify-content-center"><input class="form-check-input" type="radio" name="capacitacion3_ref" id="cap2_3"></div></td>
                            <td><div class="form-check form-check-inline justify-content-center"><input class="form-check-input" type="radio" name="capacitacion4_ref" id="cap2_4"></div></td>
                            <td><div class="form-check form-check-inline justify-content-center"><input class="form-check-input" type="radio" name="capacitacion5_ref" id="cap2_5"></div></td>
                            <td><div class="form-check form-check-inline justify-content-center"><input class="form-check-input" type="radio" name="capacitacion6_ref" id="cap2_6"></div></td>
                            <td><div class="form-check form-check-inline justify-content-center"><input class="form-check-input" type="radio" name="capacitacion7_ref" id="cap2_7"></div></td>
                            <td><div class="form-check form-check-inline justify-content-center"><input class="form-check-input" type="radio" name="capacitacion8_ref" id="cap2_8"></div></td>
                          </tr>
                        -->
                      </tbody>
                    </table>
                  </div>

                  <div class="row mt-4">
                    <div class="col-md-8">
                      <label for="comentariosEntrenador" class="form-label fw-bold">Comentarios del Entrenador</label>
                      <textarea class="form-control form-control-custom" id="comentariosEntrenador" rows="4" placeholder="Observaciones, recomendaciones, aspectos a mejorar..."></textarea>
                    </div>
                    <div class="col-md-4">
                      <div class="row g-2">
                        <div class="col-6">
                          <label for="inicioCap" class="form-label fw-bold">Inicio</label>
                          <input type="time" class="form-control form-control-custom" id="inicioCap">
                        </div>
                        <div class="col-6">
                          <label for="finCap" class="form-label fw-bold">Fin</label>
                          <input type="time" class="form-control form-control-custom" id="finCap">
                        </div>
                        <div class="col-12 mt-3">
                          <div class="signature-box p-3 text-center border rounded">
                            <small class="text-muted">Firma del Entrenador</small>
                            <div class="mt-2" style="height: 60px; border-bottom: 1px solid #dee2e6;"></div>
                          </div>
                        </div>
                        <div class="col-12 mt-3">
                          <div class="signature-box p-3 text-center border rounded">
                            <small class="text-muted">Firma del Operador</small>
                            <div class="mt-2" style="height: 60px; border-bottom: 1px solid #dee2e6;"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Paso 3: Elementos de Capacitación -->
            <div class="tab-pane fade" id="step3" role="tabpanel">
              <div class="card">
                <div class="card-body">
                    <div class="verification-container">
                        <!-- Header de la sección -->
                        <div class="verification-header bg-primary text-white p-3 rounded-top">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clipboard-check me-2 fs-5"></i>
                                <h5 class="mb-0">Verificación del Producto</h5>
                            </div>
                        </div>

                        <!-- Contenido principal -->
                        <div class="verification-content p-4">
                            <!-- Día 1 -->
                            <div class="day-section mb-4 p-3 border rounded">
                                <div class="day-header bg-light p-3 rounded mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h6 class="mb-0 fw-bold text-primary">
                                                <i class="bi bi-calendar-day me-2"></i>
                                                Día 1
                                            </h6>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <span class="me-2 fw-bold">Fecha:</span>
                                                <input type="date" class="form-control form-control-sm w-auto form-control-custom" name="prod_d1_fecha">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Verificación Inicio -->
                                <div class="verification-time-section mb-3">
                                    <h6 class="fw-bold text-secondary mb-3">
                                        <i class="bi bi-clock me-2"></i>
                                        Verificación de Inicio
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Hora de inicio</label>
                                            <input type="time" class="form-control form-control-sm form-control-custom" name="prod_d1_h_inicio">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">No. de parte</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d1_p1_parte">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Método de verificación</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d1_p1_metodo">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Resultado</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d1_p1_resultado" placeholder="# Defectos / # Verif.">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">No. de serie o lote</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d1_p1_lote">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Acción o contramedida</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d1_p1_accion">
                                        </div>
                                    </div>
                                </div>

                                <!-- Verificación Intermedio -->
                                <div class="verification-time-section">
                                    <h6 class="fw-bold text-secondary mb-3">
                                        <i class="bi bi-clock-history me-2"></i>
                                        Verificación Intermedia
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Hora intermedia</label>
                                            <input type="time" class="form-control form-control-sm form-control-custom" name="prod_d1_h_intermedio">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">No. de parte</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d1_p2_parte">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Método de verificación</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d1_p2_metodo">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Resultado</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d1_p2_resultado" placeholder="# Defectos / # Verif.">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">No. de serie o lote</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d1_p2_lote">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Acción o contramedida</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d1_p2_accion">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Día 2 -->
                            <div class="day-section mb-4 p-3 border rounded">
                                <div class="day-header bg-light p-3 rounded mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h6 class="mb-0 fw-bold text-primary">
                                                <i class="bi bi-calendar-day me-2"></i>
                                                Día 2
                                            </h6>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <span class="me-2 fw-bold">Fecha:</span>
                                                <input type="date" class="form-control form-control-sm w-auto form-control-custom" name="prod_d2_fecha">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Verificación Inicio -->
                                <div class="verification-time-section mb-3">
                                    <h6 class="fw-bold text-secondary mb-3">
                                        <i class="bi bi-clock me-2"></i>
                                        Verificación de Inicio
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Hora de inicio</label>
                                            <input type="time" class="form-control form-control-sm form-control-custom" name="prod_d2_h_inicio">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">No. de parte</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d2_p1_parte">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Método de verificación</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d2_p1_metodo">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Resultado</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d2_p1_resultado" placeholder="# Defectos / # Verif.">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">No. de serie o lote</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d2_p1_lote">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Acción o contramedida</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d2_p1_accion">
                                        </div>
                                    </div>
                                </div>

                                <!-- Verificación Intermedio -->
                                <div class="verification-time-section">
                                    <h6 class="fw-bold text-secondary mb-3">
                                        <i class="bi bi-clock-history me-2"></i>
                                        Verificación Intermedia
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Hora intermedia</label>
                                            <input type="time" class="form-control form-control-sm form-control-custom" name="prod_d2_h_intermedio">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">No. de parte</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d2_p2_parte">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Método de verificación</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d2_p2_metodo">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Resultado</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d2_p2_resultado" placeholder="# Defectos / # Verif.">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">No. de serie o lote</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d2_p2_lote">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Acción o contramedida</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d2_p2_accion">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Día 3 -->
                            <div class="day-section mb-4 p-3 border rounded">
                                <div class="day-header bg-light p-3 rounded mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h6 class="mb-0 fw-bold text-primary">
                                                <i class="bi bi-calendar-day me-2"></i>
                                                Día 3
                                            </h6>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <span class="me-2 fw-bold">Fecha:</span>
                                                <input type="date" class="form-control form-control-sm w-auto form-control-custom" name="prod_d3_fecha">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Verificación Inicio -->
                                <div class="verification-time-section mb-3">
                                    <h6 class="fw-bold text-secondary mb-3">
                                        <i class="bi bi-clock me-2"></i>
                                        Verificación de Inicio
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Hora de inicio</label>
                                            <input type="time" class="form-control form-control-sm form-control-custom" name="prod_d3_h_inicio">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">No. de parte</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d3_p1_parte">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Método de verificación</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d3_p1_metodo">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Resultado</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d3_p1_resultado" placeholder="# Defectos / # Verif.">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">No. de serie o lote</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d3_p1_lote">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Acción o contramedida</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d3_p1_accion">
                                        </div>
                                    </div>
                                </div>

                                <!-- Verificación Intermedio -->
                                <div class="verification-time-section">
                                    <h6 class="fw-bold text-secondary mb-3">
                                        <i class="bi bi-clock-history me-2"></i>
                                        Verificación Intermedia
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Hora intermedia</label>
                                            <input type="time" class="form-control form-control-sm form-control-custom" name="prod_d3_h_intermedio">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">No. de parte</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d3_p2_parte">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Método de verificación</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d3_p2_metodo">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Resultado</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d3_p2_resultado" placeholder="# Defectos / # Verif.">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">No. de serie o lote</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d3_p2_lote">
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label small fw-bold">Acción o contramedida</label>
                                            <input type="text" class="form-control form-control-sm form-control-custom" name="prod_d3_p2_accion">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección de firmas -->
                            <div class="signatures-section mb-4 p-3 border rounded">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="bi bi-pen-fill me-2"></i>
                                    Autorizaciones
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="signature-box border rounded p-3 text-center bg-light">
                                            <small class="text-muted d-block mb-2">Firma del Inspector</small>
                                            <div class="signature-placeholder border-bottom pb-4"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="signature-box border rounded p-3 text-center bg-light">
                                            <small class="text-muted d-block mb-2">Firma de Utility</small>
                                            <div class="signature-placeholder border-bottom pb-4"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notas adicionales y estado general -->
                            <div class="notes-section p-3 border rounded">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Observaciones del Inspector:</label>
                                        <textarea class="form-control form-control-custom" rows="3" placeholder="Anotar observaciones relevantes..."></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Estado General:</label>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="radio" name="estadoGeneral" id="estadoAprobado" value="aprobado">
                                            <label class="form-check-label text-success fw-bold" for="estadoAprobado">
                                                <i class="bi bi-check-circle me-1"></i> Aprobado
                                            </label>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="radio" name="estadoGeneral" id="estadoCondicionado" value="condicionado">
                                            <label class="form-check-label text-warning fw-bold" for="estadoCondicionado">
                                                <i class="bi bi-exclamation-triangle me-1"></i> Condicionado
                                            </label>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="radio" name="estadoGeneral" id="estadoRechazado" value="rechazado">
                                            <label class="form-check-label text-danger fw-bold" for="estadoRechazado">
                                                <i class="bi bi-x-circle me-1"></i> Rechazado
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
              </div>
            </div>

                <!-- Paso 4: Confirmación -->
                <div class="tab-pane fade" id="step4" role="tabpanel">
                  <div class="card">
                    <div class="card-header bg-success text-white">
                      <h6 class="mb-0">
                        <i class="bi bi-check-circle me-2"></i>
                        Confirmación del Cambio
                      </h6>
                    </div>
                    <div class="card-body">
                      <div class="text-center mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        <h3 class="mt-3 text-success">¡Cambio Listo para Confirmar!</h3>
                        <p class="text-muted">Revisa la información antes de confirmar el cambio de operador</p>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="card mb-3">
                            <div class="card-header bg-light">
                              <h6 class="mb-0">Resumen del Cambio</h6>
                            </div>
                            <div class="card-body">
                              <table class="table table-sm">
                                <tr>
                                  <td class="fw-bold">Control No:</td>
                                  <td id="resumenControlNo">CC-2025-001</td>
                                </tr>
                                <tr>
                                  <td class="fw-bold">Línea:</td>
                                  <td id="resumenLinea">Línea A</td>
                                </tr>
                                <tr>
                                  <td class="fw-bold">Fecha/Hora:</td>
                                  <td id="resumenFecha">15/11/2025 08:30</td>
                                </tr>
                                <tr>
                                  <td class="fw-bold">Operador:</td>
                                  <td id="resumenOperador">Juan Pérez</td>
                                </tr>
                                <tr>
                                  <td class="fw-bold">Proceso:</td>
                                  <td id="resumenProceso">Ensamblaje</td>
                                </tr>
                                <tr>
                                  <td class="fw-bold">Estación:</td>
                                  <td id="resumenEstacion">Estación 02</td>
                                </tr>
                              </table>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="card mb-3">
                            <div class="card-header bg-light">
                              <h6 class="mb-0">Documentos Verificados</h6>
                            </div>
                            <div class="card-body">
                              <ul id="resumenDocumentos" class="list-unstyled">
                                <li><i class="bi bi-check-circle text-success me-2"></i> Sin problema</li>
                                <!-- Se llenará dinámicamente -->
                              </ul>
                            </div>
                          </div>
                          <div class="card">
                            <div class="card-header bg-light">
                              <h6 class="mb-0">Capacitación Realizada</h6>
                            </div>
                            <div class="card-body">
                              <ul id="resumenCapacitacion" class="list-unstyled">
                                <li><i class="bi bi-clock me-2"></i> Horario: 08:00 - 10:30</li>
                                <!-- Se llenará dinámicamente -->
                              </ul>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="alert alert-info mt-4">
                        <i class="bi bi-info-circle me-2"></i>
                        Al confirmar, se registrará el cambio de operador y se notificará a los supervisores correspondientes.
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Footer del Modal con Navegación -->
            <div class="modal-footer">
              <div class="me-auto">
                <span class="text-muted" id="stepIndicator">Paso 1 de 4</span>
              </div>
              <button type="button" class="btn btn-outline-secondary" id="prevStep" disabled>
                <i class="bi bi-chevron-left"></i> Anterior
              </button>
              <button type="button" class="btn btn-primary" id="nextStep">
                Siguiente <i class="bi bi-chevron-right"></i>
              </button>
              <button type="button" class="btn btn-success d-none" id="confirmChange">
                <i class="bi bi-check-lg"></i> Confirmar Cambio
              </button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
          </div>
    </div>
  </div>

  <!-- Modal de Cierre de Control de Cambio - Diseño Vertical -->
  <div class="modal fade modal-close-control" id="closeControlModal" tabindex="-1" aria-labelledby="closeControlModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <!-- Header del Modal -->
        <div class="modal-header">
          <div class="d-flex align-items-center">
            <i class="bi bi-flag-fill me-2" style="font-size: 1.5rem;"></i>
            <h5 class="modal-title" id="closeControlModalLabel">Cierre de Control de Cambio</h5>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <!-- Body del Modal -->
        <div class="modal-body">
          <!-- Información del Cambio -->
          <div class="info-card">
            <h6 class="info-title">
              <i class="bi bi-info-circle"></i>
              Información del Control de Cambio
            </h6>
            <div class="row">
              <div class="col-md-6">
                <div class="info-item">
                  <span class="info-label">Control No:</span>
                  <span class="info-value">CC-2025-001</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Operador:</span>
                  <span class="info-value">Juan Pérez</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Estación:</span>
                  <span class="info-value">Estación 02</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-item">
                  <span class="info-label">Inicio:</span>
                  <span class="info-value">15/11/2025 08:30</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Duración:</span>
                  <span class="info-value">3 días</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Estado:</span>
                  <span class="info-value"><span class="badge bg-warning">En Proceso</span></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Sección de Decisión -->
          <div class="decision-section">
            <h4 class="decision-title">
              <i class="bi bi-clipboard-check me-2"></i>
              Juicio para Cerrar el Control de Cambio
            </h4>
            
            <!-- Opción Cerrar -->
            <div class="decision-option option-close" id="optionClose">
              <div class="option-header">
                <div class="option-icon">
                  <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                  <h5 class="option-title">Cerrar Control de Cambio</h5>
                  <p class="option-description">El operador ha completado satisfactoriamente el período de prueba y está listo para operar de forma independiente.</p>
                </div>
              </div>
              
              <div class="option-content">
                <div class="mb-3">
                  <label for="contramedidasCerrar" class="form-label fw-bold">Contramedidas Aplicadas</label>
                  <textarea class="form-control form-control-custom" id="contramedidasCerrar" rows="3" placeholder="Describa las contramedidas implementadas durante el período de prueba..."></textarea>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <label for="fechaCierre" class="form-label fw-bold">Fecha de Cierre</label>
                    <input type="date" class="form-control form-control-custom" id="fechaCierre">
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Opción Continuar -->
            <div class="decision-option option-continue" id="optionContinue">
              <div class="option-header">
                <div class="option-icon">
                  <i class="bi bi-arrow-clockwise"></i>
                </div>
                <div>
                  <h5 class="option-title">Continuar Control de Cambio</h5>
                  <p class="option-description">Se requiere más tiempo de supervisión antes de que el operador pueda trabajar de forma independiente.</p>
                </div>
              </div>
              
              <div class="option-content">
                <div class="mb-3">
                  <label for="contramedidasContinuar" class="form-label fw-bold">Contramedidas Adicionales</label>
                  <textarea class="form-control form-control-custom" id="contramedidasContinuar" rows="3" placeholder="Describa las contramedidas adicionales que se implementarán..."></textarea>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <label for="fechaContinuar" class="form-label fw-bold">Fecha de Revisión</label>
                    <input type="date" class="form-control form-control-custom" id="fechaContinuar">
                  </div>
                  <div class="col-md-6">
                    <label for="diasContinuar" class="form-label fw-bold">Días Adicionales</label>
                    <input type="number" class="form-control form-control-custom" id="diasContinuar" min="1" max="30" placeholder="Número de días">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sección de Firmas -->
          <div class="signature-section">
            <h5 class="signature-title">
              <i class="bi bi-pen-fill me-2"></i>
              Autorizaciones Requeridas
            </h5>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="signature-box">
                  <div class="signature-label">Firma de Utility/Líder</div>
                  <div class="signature-hint">Haga clic para firmar</div>
                  <i class="bi bi-pen text-muted mt-2"></i>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="signature-box">
                  <div class="signature-label">Firma de Supervisor</div>
                  <div class="signature-hint">Haga clic para firmar</div>
                  <i class="bi bi-pen text-muted mt-2"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Notas Adicionales -->
          <div class="mt-4">
            <label for="notasAdicionales" class="form-label fw-bold">
              <i class="bi bi-chat-text me-2"></i>Notas Adicionales
            </label>
            <textarea class="form-control form-control-custom" id="notasAdicionales" rows="3" placeholder="Agregue cualquier observación o comentario adicional sobre el cierre del control de cambio..."></textarea>
          </div>
        </div>
        
        <!-- Footer del Modal -->
        <div class="modal-footer">
          <button type="button" class="btn btn-close-custom" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i>Cancelar
          </button>
          <button type="button" class="btn btn-confirm-custom d-none" id="btnConfirmClose">
            <i class="bi bi-check-lg me-2"></i>Confirmar Cierre
          </button>
          <button type="button" class="btn btn-continue-custom d-none" id="btnConfirmContinue">
            <i class="bi bi-arrow-clockwise me-2"></i>Continuar Control
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="../scripts/bootstrap.bundle.min.js"></script>

  <!--Custmo js -->
  <script src="../scripts/layout.js"></script>
</body>
</html>