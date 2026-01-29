<?php 
  $codigo = !empty($_GET['codigo']) ? urldecode($_GET['codigo']) : '';
  $nombre = !empty($_GET['nombre']) ? urldecode($_GET['nombre']) : '';
  date_default_timezone_set('Etc/GMT+6');

  $sql= "SELECT descripcion, encargado_supervisor from SPC_LINEAS WHERE CODIGO_LINEA = :codigo_linea";

  require_once('../api/conexion.php');
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(':codigo_linea', $codigo);
  $stmt->execute();
  $linea = $stmt->fetch(PDO::FETCH_ASSOC);
  $descripcion = !empty($linea['descripcion']) ? $linea['descripcion'] : '';
  $encargado_supervisor = !empty($linea['encargado_supervisor']) ? $linea['encargado_supervisor'] : '';

?>

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

    <!-- MENU LAYAOUT-->
      <?PHP include('./menuLayaout.php')?>
    <!-- FIN MENU LAYOUT-->

    <!-- Área principal -->
    <div class="layout-main">
      <div class="layout-header">

        <div>
          <h2 class="layout-title">Línea de Producción <?php echo $nombre?></h2>

          <select class="form-select m-0 py-0 ps-1" name="turnoLayout" id="turnoLayout" style="max-width:100px;">
              <option value="1">Turno 1</option>
              <option value="2">Turno 2</option>
          </select>

          <!-- IDENTIFICADOR DE LA LINEA PARA EXTRAER LOS DATOS VISIBLES -->
            <input type="hidden" id="codigoLinea" value="<?php echo $codigo?>">
            <input type="hidden" id="nombreLinea" value="<?php echo $nombre?>">
        </div>
       
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

  <!--Agregar una estacion -->
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
                  <i class="bi bi-cpu"></i>Información de la Estación
                </h3>
                
                <div class="mb-3">
                  <label for="nombreEstacion" class="form-label required-field">
                    <i class="bi bi-tag"></i>Nombre de la Estación/Proceso
                  </label>
                  <input  type="text" 
                    class="form-control form-control-custom"  id="nombreEstacion" placeholder="Ej: Moldeo De Uretano Y"
                    required>
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
                  <label for="stationdescripcion" class="form-label">
                    <i class="bi bi-pencil"></i>
                    Comentarios/Descripción
                  </label>
                  <textarea 
                    class="form-control form-control-custom form-textarea" 
                    id="stationdescripcion" 
                    placeholder="Describe las actividades, procedimientos específicos, consideraciones especiales o comentarios relevantes para esta estación..."
                    rows="4"
                  ></textarea>
                  <div class="form-help">Opcional: Detalla el proceso, herramientas utilizadas o instrucciones especiales</div>
                </div>
              </div>

              <!-- Sección: Certificaciones -->
              <div class="form-section">
                <h3 class="section-title">
                  <i class="bi bi-award"></i> Requerimientos de Certificación
                </h3>

                <div class="mb-4">
                    <label for="requiereCertificacion" class="form-label required-field">
                      ¿ Requiere certificación?
                    </label>
                      <select class="form-control form-control-custom select" id="requiereCertificacion" required>
                        <option value="">--- SELECCIONE UNA OPCION ---</option>
                        <option value="0">NO</option>
                        <option value="1">SI</option>
                      </select>
                </div>
                
                <div class="mb-3">
                  <label for="certificacion" class="form-label">
                    <i class="bi bi-shield-check"></i>
                    Certificación/Capacitación Requerida
                  </label>
                  <select class="form-control form-control-custom" id="certificacion">
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
            <button id="btnGuardarEstacion" type="button" class="btn btn-primary-custom mx-2">
              <i class="bi bi-check-circle"></i>Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!--Asignar operador a una estacion -->
  <div class="modal fade" id="modalAsignarOperador" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Asignar operador a una estacion</h5>
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
                      <label for="nomina" class="form-label required-field">
                        <i class="bi bi-clock"></i>
                        No. Reloj / ID Empleado
                      </label>
                      <div class="input-group-custom">
                        <input 
                          type="number" 
                          min=0
                          step=1 class="form-control form-control-custom" id="nominaModalAsignar" 
                          placeholder="Ej: EMP-0256" required>
                        <button type="button" class="input-icon" id="searchEmployee">
                          <i class="bi bi-search"></i>
                        </button>
                      </div>
                      <div class="form-help">Ingresa el número de reloj o ID único del empleado</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                      <label for="nombre" class="form-label required-field">
                        <i class="bi bi-person"></i>
                        Nombre del Operador
                      </label>
                      <div class="input-group-custom">
                        <input 
                          type="text" 
                          class="form-control form-control-custom" 
                          id="nombreModalAsignar" 
                          placeholder="Selecciona o busca un operador"
                          readonly>
                        <button type="button" class="input-icon">
                          <i class="bi bi-people"></i>
                        </button>
                      </div>
                    </div>

                    <!--Listado de estaciones asignadas -->
                      <div class="col-12">
                        <label for="nomina" class="form-label">
                          <i class="bi bi-list"></i>
                            Listado de estaciones asignadas
                        </label>
                          <div class="form-control" style="min-height: 120px; resize: vertical; overflow-y: auto;">
                            Listado de estaciones asignadas al operador colocado para el registro en este forulario <br>
                            -Estacion 1 <br>
                            -Estacion 2 <br>
                            -Estacion 3 <br>
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
                        <i class="bi bi-cpu"></i>Estación
                      </label>
                      <select class="form-control form-control-custom" id="stationSelect" required>
                        <option value="">Selecciona una estación...</option>
                      </select>
                      <div class="form-help">Selecciona la estación donde se asignará el operador</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                      <label for="assignmentDate" class="form-label required-field">
                          <i class="bi bi-calendar"></i>
                          Fecha de Asignación
                      </label>
                      <input type="date" class="form-control form-control-custom" 
                        id="assignmentDate" value="<?php echo date('Y-m-d'); ?>" required>
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
                      <label for="turnoAsignar" class="form-label">
                        <i class="bi bi-clock-history"></i>
                        Turno
                      </label>
                      <input class="form-control form-control-custom" id="turnoasignar" placeholder="Turno" readonly>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="comentarios" class="form-label">
                      <i class="bi bi-sticky"></i> Observaciones
                    </label>
                    <textarea 
                      class="form-control form-control-custom" 
                      id="comentarios" 
                      rows="3"
                      placeholder="Notas adicionales sobre esta asignación..."
                    ></textarea>
                  </div>
                </div>
              </form>
            <!-- Fin formulario -->
            <div class="d-flex justify-content-end mt-2 ">
              <button type="button" class="btn btn-secondary mx-2" data-bs-dismiss="modal">
                <i class="bi bi-x-circle"></i> Cancelar
              </button>
              <button type="button" class="btn btn-primary mx-2" id="btnAsignarOperador">
                <i class="bi bi-check-circle"></i> Guardar
              </button>
            </div>
          </div>
        </div>
      </div>
  </div>

  <!-- Consultar Puntos de Cambio -->
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

  <!-- Registro de Asistencia -->
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
              <div class="col-md-8">
                  <!-- FECHA-->
                  <div class="btn-group">
                    <button class="btn btn-outline-secondary">
                      <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="btn btn-outline-dark fw-bold" style="min-width: 180px;">
                      <i class="bi bi-calendar3 me-2"></i>29 de Enero, 2026
                    </button>
                    <button class="btn btn-outline-secondary">
                      <i class="bi bi-chevron-right"></i>
                    </button>
                  </div>
              </div>

              <div class="col-4">
                  <div class="d-flex justify-content-end align-items-center">
                    <button class="btn btn-outline-primary me-2">
                      <i class="bi bi-printer"></i> Imprimir
                    </button>
                    <button class="btn btn-outline-secondary me-2">
                      <i class="bi bi-download"></i> Exportar
                    </button>
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
                      <th width="300">Asistencia</th>
                      <th>Comentarios</th>
                      <th width="200" class="text-center">Cambio de turno</th>
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
                          <div>
                            <div class="fw-bold">Juan Pérez</div>
                            <small class="text-muted">ID: EMP-001</small>
                          </div>
                        </div>
                      </td>
                      <td>
                        <select class="form-control form-control-custom attendance-status" data-employee="EMP-001">
                          <option value="present" selected>✅ Asistió - Puntual</option>
                          <option value="present-late">🟡 Asistió - Tardanza</option>
                          <option value="permission">🟢 Permiso Autorizado</option>
                          <option value="permission-medical">🏥 Permiso Médico</option>
                          <option value="absence">❌ Falta Injustificada</option>
                          <option value="vacation">🏖️ Vacaciones</option>
                          <option value="other">⚪ Otro</option>
                        </select>
                      </td>
                      <td>
                        <input type="text" class="form-control form-control-custom" placeholder="Observaciones..." value="">
                      </td>
                      <td class="text-center">
                            <!--   
                              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Ver historial">
                                <i class="bi bi-clock-history"></i>
                              </button>
                            -->
                          <div class="form-check d-flex align-items-center justify-content-center gap-2">
                              <input class="form-check-input" type="checkbox" id="cambioTurno">
                              <label class="form-check-label d-flex align-items-center gap-1" for="cambioTurno" data-bs-toggle="tooltip" data-bs-placement="top" title="Cambio de turno"> 
                                  <i class="bi bi-clock-history"></i>
                              </label>
                          </div>
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
                          <div>
                            <div class="fw-bold">María González</div>
                            <small class="text-muted">ID: EMP-002</small>
                          </div>
                        </div>
                      </td>
                      <td>
                        <select class="form-control form-control-custom attendance-status" data-employee="EMP-002">
                          <option value="present">✅ Asistió - Puntual</option>
                          <option value="present-late">🟡 Asistió - Tardanza</option>
                          <option value="permission" selected>🟢 Permiso Autorizado</option>
                          <option value="permission-medical">🏥 Permiso Médico</option>
                          <option value="absence">❌ Falta Injustificada</option>
                          <option value="vacation">🏖️ Vacaciones</option>
                          <option value="other">⚪ Otro</option>
                        </select>
                      </td>
                      <td>
                        <input type="text" class="form-control form-control-custom" placeholder="Observaciones..." value="Consulta médica programada">
                      </td>
                      <td class="text-center">
                          <div class="form-check d-flex align-items-center justify-content-center gap-2">
                              <input class="form-check-input" type="checkbox" id="cambioTurno">
                              <label class="form-check-label d-flex align-items-center gap-1" for="cambioTurno" data-bs-toggle="tooltip" data-bs-placement="top" title="Cambio de turno"> 
                                  <i class="bi bi-clock-history"></i>
                              </label>
                          </div>
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
                          <div>
                            <div class="fw-bold">Carlos Ruiz</div>
                            <small class="text-muted">ID: EMP-003</small>
                          </div>
                        </div>
                      </td>
                      <td>
                        <select class="form-control form-control-custom attendance-status" data-employee="EMP-003">
                          <option value="present">✅ Asistió - Puntual</option>
                          <option value="present-late">🟡 Asistió - Tardanza</option>
                          <option value="permission">🟢 Permiso Autorizado</option>
                          <option value="permission-medical">🏥 Permiso Médico</option>
                          <option value="absence" selected>❌ Falta Injustificada</option>
                          <option value="vacation">🏖️ Vacaciones</option>
                          <option value="other">⚪ Otro</option>
                        </select>
                      </td>
                      <td>
                        <input type="text" class="form-control form-control-custom" placeholder="Observaciones..." value="">
                      </td>
                      <td class="text-center">
                          <div class="form-check d-flex align-items-center justify-content-center gap-2">
                              <input class="form-check-input" type="checkbox" id="cambioTurno">
                              <label class="form-check-label d-flex align-items-center gap-1" for="cambioTurno" data-bs-toggle="tooltip" data-bs-placement="top" title="Cambio de turno"> 
                                  <i class="bi bi-clock-history"></i>
                              </label>
                          </div>
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
                          <div>
                            <div class="fw-bold">Ana López</div>
                            <small class="text-muted">ID: EMP-004</small>
                          </div>
                        </div>
                      </td>
                      <td>
                        <select class="form-control form-control-custom attendance-status" data-employee="EMP-004">
                          <option value="present" selected>✅ Asistió - Puntual</option>
                          <option value="present-late">🟡 Asistió - Tardanza</option>
                          <option value="permission">🟢 Permiso Autorizado</option>
                          <option value="permission-medical">🏥 Permiso Médico</option>
                          <option value="absence">❌ Falta Injustificada</option>
                          <option value="vacation">🏖️ Vacaciones</option>
                          <option value="other">⚪ Otro</option>
                        </select>
                      </td>
                      <td>
                        <input type="text" class="form-control form-control-custom" placeholder="Observaciones..." value="">
                      </td>
                      <td class="text-center">
                          <div class="form-check d-flex align-items-center justify-content-center gap-2">
                              <input class="form-check-input" type="checkbox" id="cambioTurno">
                              <label class="form-check-label d-flex align-items-center gap-1" for="cambioTurno" data-bs-toggle="tooltip" data-bs-placement="top" title="Cambio de turno"> 
                                  <i class="bi bi-clock-history"></i>
                              </label>
                          </div>
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
                          <div>
                            <div class="fw-bold">Luis Fernández</div>
                            <small class="text-muted">ID: EMP-005</small>
                          </div>
                        </div>
                      </td>
                      <td>
                        <select class="form-control form-control-custom attendance-status" data-employee="EMP-005">
                          <option value="present" selected>✅ Asistió - Puntual</option>
                          <option value="present-late">🟡 Asistió - Tardanza</option>
                          <option value="permission">🟢 Permiso Autorizado</option>
                          <option value="permission-medical">🏥 Permiso Médico</option>
                          <option value="absence">❌ Falta Injustificada</option>
                          <option value="vacation">🏖️ Vacaciones</option>
                          <option value="other">⚪ Otro</option>
                        </select>
                      </td>
                      <td>
                        <input type="text" class="form-control form-control-custom" placeholder="Observaciones..." value="">
                      </td>
                      <td class="text-center">
                          <div class="form-check d-flex align-items-center justify-content-center gap-2">
                              <input class="form-check-input" type="checkbox">
                              <label class="form-check-label d-flex align-items-center gap-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Cambio de turno"> 
                                  <i class="bi bi-clock-history"></i>
                              </label>
                          </div>
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
                    <button class="btn btn-outline-success">
                      <i class="bi bi-clock"></i> Cambio de turno
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Footer del Modal -->
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary">
              <i class="bi bi-floppy"></i> Guardar Cambios
            </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Gestion de punto de Cambio y estacion -->
  <div class="modal fade" id="changeControlModal" tabindex="-1" aria-labelledby="changeControlModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
      <div class="modal-content">
        <!-- Header del Modal -->
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color), #1a2530); color: white;">
          <div class="d-flex align-items-center">
            <i class="bi bi-arrow-repeat me-2" style="font-size: 1.5rem;"></i>
            <h5 class="modal-title" id="changeControlModalLabel">Control de punto de cambio</h5>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <!-- Body del Modal -->
        <div class="modal-body">

            <!-- NOMBRE DE LA ESTACION-->
            <div id="nombreEstacionModalPC" class="text-center fw-bold"></div>
            <input type="hidden" id="idEstacionModalPC" value="">

            <!--Contenedor de los botones del menu -->
            <div class="m-0 p-0" id="menuModalPC">
                  <!-- MAQUINARIA-->
                  <i class="bi bi-gear-fill" id="btnRegistroPc"></i>

                  <!--MATERIA PRIMA --> 
                  <i class="bi bi-box-seam"></i>

                  <!--METODO -->
                  <i class="bi bi-diagram-3"></i>
                  
                  <!-- MANO DE OBRA-->
                  <i class="bi bi-person-gear"></i>

                  <!-- Informacion de la estacion -->
                  <i class="bi bi-info-circle m-0 p-0" id="btnInfoRPC"></i>

                  <!--Liberar punto de cambio -->
                  <i class="bi bi-unlock" id="btnLiberarPC"></i>

                  <!-- Alternativa a person-gear
                    <span class="position-relative">
                      <i class="bi bi-person"></i>
                      <i class="bi bi-arrow-repeat position-absolute top-10 start-100 translate-middle"></i>
                    </span>
                  -->
            </div>

            <hr>

            <div id="ventanasModalPC">
                <!-- Formulario de registro de punto de cambio -->
                  <div id="contregistroCambioForm" class="fade-page show">
                      <form class="form-body" id="registroCambioForm">
                        <!-- Header de la sección -->
                        <div class="form-section">
                          <h3 class="section-title justify-content-center">
                            <i class="bi bi-arrow-repeat"></i>Registro de un punto de cambio
                          </h3>
                          <p class="text-muted mb-2">Complete la información requerida</p>
                        </div>

                        <!-- Información del Cambio -->
                        <div class="form-section">
                          <div class="row">
                              <div class="col-md-6 mb-3">
                                <label for="nominaPC" class="form-label required-field">
                                  <i class="bi bi-clock"></i>No. Reloj / ID Empleado
                                </label>
                                <div class="input-group-custom">
                                  <input type="number" min="0" step="1" class="form-control form-control-custom" id="nominaPC" placeholder="Ej: 256" required>
                                  <button type="button" class="input-icon" id="searchEmployee">
                                    <i class="bi bi-search"></i>
                                  </button>
                                </div>
                                <div class="form-help">Ingresa el número de reloj o ID único del empleado</div>
                              </div>
                              
                              <div class="col-md-6 mb-3">
                                <label for="nombrePC" class="form-label">
                                  <i class="bi bi-person"></i> Nombre del trabajador
                                </label>
                                <div class="input-group-custom">
                                  <input type="text" class="form-control form-control-custom" id="nombrePC" placeholder="Selecciona o busca un operador" readonly>
                                  <button type="button" class="input-icon">
                                    <i class="bi bi-people"></i>
                                  </button>
                                </div>
                              </div>
                          </div>

                          <div class="row">
                            <div class="col-md-6 mb-3">
                              <label for="no_controlCambio" class="form-label required-field">
                                <i class="bi bi-hash"></i> No. Control de Cambio
                              </label>
                              <div class="input-group-custom">
                                <input type="text" class="form-control form-control-custom" 
                                      id="no_controlCambio" placeholder="Ej: CAM-001" maxlength="50" readonly>
                                <button type="button" class="input-icon"><i class="bi bi-search"></i></button>
                              </div>
                            </div>

                            <div class="col-md-6 mb-3">
                              <label for="tipo_cambio" class="form-label required-field">
                                <i class="bi bi-shuffle"></i> Tipo de Cambio
                              </label>

                              <select type="text" class="form-select" id="tipo_cambio" required>
                                <option value="1">Inesperado</option>
                                <option value="2">Programado</option>
                              </select>
                            </div>
                          </div>

                          <div class="row">
                            <div class="col-md-6 mb-3">
                              <label for="fechaHora_inicio" class="form-label required-field">
                                <i class="bi bi-calendar-event"></i> Fecha y Hora Inicio
                              </label>
                              <input type="datetime-local" class="form-control form-control-custom" id="fechaHora_inicio" required>
                            </div>

                            <!--TURNO PUNTO DE CAMBIO -->
                            <div class="col-md-6 mb-3">
                                <label for="turnoPuntoCambio" class="form-label required-field">
                                    <i class="bi bi-clock-history"></i>Turno</label>
                                    <select id="turnoPuntoCambio" class="form-select" required>
                                      <option value="" selected>--- Selecciona un turno ---</option>
                                      <option value="1">Turno 1</option>
                                      <option value="2">Turno 2</option>
                                    </select>
                            </div>
                          </div>

                          <div class="mb-3">
                            <label for="motivo" class="form-label">
                              <i class="bi bi-chat-left-text"></i> Descripcion
                            </label>
                            <textarea class="form-control form-control-custom" id="motivo" rows="3" placeholder="Descripcion del punto de cambio" ></textarea>
                          </div>

                          <div class="row">
                            <div class="col-md-6 mb-3">
                              <label for="codigolineaPC" class="form-label">
                                <i class="bi bi-diagram-3"></i> Línea
                              </label>
                              <input type="text" class="form-control form-control-custom" id="codigolineaPC" value= <?php echo $nombre?>  readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                              <label for="nombre_estacion" class="form-label"><i class="bi bi-geo-alt"></i> Estación</label>
                              <input type="text" class="form-control form-control-custom" id="nombre_estacion">
                              <input type="hidden" id="id_estacion">
                            </div>
                          </div>
                        </div>
                      </form>

                      <!-- Footer del Modal con Navegación -->
                      <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="confirmChange">
                          <i class="bi bi-check-lg"></i> Confirmar Registro
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                      </div>
                  </div>
                <!--Fin formulario de registro de punto de cambio -->


                <!--Informacion del personal asignado-->
                  <div id="contInfoEstacion" class="fade-page d-none" style="background: white; border-radius: 10px; padding: 25px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08); border-left: 4px solid #000000; margin-bottom: 30px;">
                      <h4><i class="bi bi-person-badge" style="margin-right: 10px; font-size: 1.4rem;"></i>
                          Información del Operador de Estación
                      </h4>

                      <input type="hidden" id="idTrabajadorAsignado" value="">
                      
                      <div class="row">
                          <div class="col-md-12">
                              <!-- Foto del operador -->
                              <div style="margin-bottom: 20px; text-align: center;">
                                  <img id ="imgInfochangeControlModal" src="../img/personal/na.jpg" 
                                      alt="Foto del operador" 
                                      style="width: 120px; height: 120px; border-radius: 10px; object-fit: cover; border: 3px solid #e9ecef; margin-bottom: 10px;">
                                  <div style="font-weight: 600; color: #495057;">Foto del operador</div>
                              </div>
                              
                              <!-- Nómina -->
                              <div style="margin-bottom: 15px; display: flex; align-items: flex-start;">
                                  <span style="font-weight: 600; color: #495057; min-width: 200px; margin-right: 15px;">Nómina:</span>
                                  <span style="color: #212529; flex: 1;">EMP-04582</span>
                              </div>
                              
                              <!-- Nombre -->
                              <div style="margin-bottom: 15px; display: flex; align-items: flex-start;">
                                  <span style="font-weight: 600; color: #495057; min-width: 200px; margin-right: 15px;">Nombre:</span>
                                  <span style="color: #212529; flex: 1;">Carlos Alberto Rodríguez Martínez</span>
                              </div>
                              
                              <div style="margin-bottom: 15px; display: flex; align-items: flex-start;">
                                  <span style="font-weight: 600; color: #495057; min-width: 200px; margin-right: 15px;">Fecha de asignacion:</span>
                                  <span style="color: #212529; flex: 1;">15/12/2024</span>
                              </div>

                              <!-- Turno -->
                              <div style="margin-bottom: 15px; display: flex; align-items: center;">
                                  <span style="font-weight: 600; color: #495057; min-width: 200px; margin-right: 15px;">Turno:</span>
                                  <span style="color: #212529; flex: 1;">
                                      <span class="badge bg-primary" style="font-size: 0.85rem; padding: 5px 12px; border-radius: 20px;">Matutino</span>
                                  </span>
                              </div>
                          </div>
                      
                          <div class="col-md-12">
                              <!-- Nivel ILU -->
                              <div style="margin-bottom: 15px; display: flex; align-items: center;">
                                  <span style="font-weight: 600; color: #495057; min-width: 200px; margin-right: 15px;">Nivel ILU:</span>
                                  <span style="color: #212529; flex: 1;">
                                      <span class="badge bg-info text-dark" style="font-size: 0.85rem; padding: 5px 12px; border-radius: 20px;"> ILU</span>
                                  </span>
                              </div>
                              
                              <!-- Fecha de vencimiento de certificación -->
                              <div style="margin-bottom: 15px; display: flex; align-items: flex-start;">
                                  <span style="font-weight: 600; color: #495057; min-width: 200px; margin-right: 15px;">Fecha de vencimiento de certificación:</span>
                                  <span style="color: #212529; flex: 1;">
                                      <span style="color: #dc3545; font-weight: 600;">15/12/2024</span>
                                      <small style="color: #6c757d; display: block; margin-top: 5px;">(Faltan 45 días)</small>
                                  </span>
                              </div>
                          </div>

                          <div class="col-md-12">  
                              <!-- Comentario -->
                              <div style="margin-bottom: 15px; display: flex; align-items: flex-start;">
                                  <span style="font-weight: 600; color: #495057; min-width: 200px; margin-right: 15px;">Comentario:</span>
                                  <div style="color: #212529; flex: 1;">
                                      <div style="background-color: #f8f9fa; border-left: 4px solid #6c757d; padding: 15px; border-radius: 5px;">
                                          Operador con excelente desempeño en los últimos 6 meses. Ha completado satisfactoriamente el entrenamiento de seguridad avanzada.
                                      </div>
                                  </div>
                              </div>
                          </div>

                        <!--Boton remover trabajador de estacion -->
                          <div class="d-flex justify-content-end mt-1">
                            <button class="btn btn-danger mx-1" id="btnRemoverTrabajadorPC">
                              <b>REMOVER TRABAJADOR</b>
                            </button>
                            <button class="btn btn-warning mx-1">
                              <b>RETIRAR CERTIFICACION</b>
                            </button>
                          </div>
                      </div>
                  </div>
                <!--Fin  Informacion del personal asignado-->

                <!-- Contenedor de liberacion de punto de cambio -->
                  <div id="contLiberarPC" class="fade-page d-none" style="background: white; border-radius: 10px; padding: 25px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08); border-left: 4px solid #000000; margin-bottom: 30px;">

                      <div class="form-section">
                        <h4 class="section-title justify-content-center">
                            <i class="bi bi-unlock"></i>  CIERRE DEL PUNTO DE CAMBIO
                        </h4>
                      </div>

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

                      <!-- Formulario de cierre de punto de cambio-->
                      <div class= "decision-option option-close">
                        <form class="form" id="cierreControlCambioForm">

                            <!--ID del punto de cambio-->
                            <input type="hidden" name="idPC" id="idPC">

                            <!--Fecha de cierre del punto de cambio-->
                            <div class="row">
                              <div class="col-md-6">
                                <label for="fechaCierre" class="form-label fw-bold">Fecha de Cierre</label>
                                <input type="datetime-local" class="form-control form-control-custom" id="fechaCierre" required>
                              </div>
                            </div>

                            <!-- Notas Adicionales -->
                            <div class="mt-4">
                              <label for="notasAdicionales" class="form-label fw-bold">
                                <i class="bi bi-chat-text me-2"></i>Notas Adicionales
                              </label>
                              <textarea class="form-control form-control-custom" id="notasAdicionales" rows="3" placeholder="Agregue cualquier observación o comentario adicional sobre el cierre del control de cambio..."></textarea>
                            </div>

                            <!--FIRMAS PARA EL CIERRE DEL PC -->
                              <div class="row mt-4">
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
                        </form>
                      </div>

                      <!-- Sección de Decisión 
                        <div class="decision-section">
                          <h4 class="decision-title">
                            <i class="bi bi-clipboard-check me-2"></i>
                            Juicio para Cerrar el Control de Cambio
                          </h4>
                          
                          <Opción Cerrar
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
                          
                          Opción Continuar 
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

                        Sección de Firmas 
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

                        Notas Adicionales
                        <div class="mt-4">
                          <label for="notasAdicionales" class="form-label fw-bold">
                            <i class="bi bi-chat-text me-2"></i>Notas Adicionales
                          </label>
                          <textarea class="form-control form-control-custom" id="notasAdicionales" rows="3" placeholder="Agregue cualquier observación o comentario adicional sobre el cierre del control de cambio..."></textarea>
                        </div>
                      -->

                      <!-- Footer del Modal -->
                      <div class="modal-footer">
                        <button type="button" class="btn btn-close-custom" data-bs-dismiss="modal">
                          <i class="bi bi-x-circle me-2"></i>Cancelar
                        </button>
                        <button type="button" class="btn btn-confirm-custom" id="btnConfirmClose">
                          <i class="bi bi-check-lg me-2"></i>Cerrar punto de cambio
                        </button>
                        <button type="button" class="btn btn-continue-custom d-none" id="btnConfirmContinue">
                          <i class="bi bi-arrow-clockwise me-2"></i>Continuar Control
                        </button>
                      </div>
                  </div>
                <!-- Fin Formulario de liberacion -->
            </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Personal disponible -->
  <div class="modal fade modal-close-control" id="modalPersonalDisponible" tabindex="-1" aria-labelledby="modalPersonalDisponibleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
      <div class="modal-content">
        <!-- Header del Modal -->
        <div class="modal-header">
          <div class="d-flex align-items-center">
            <h5 class="modal-title" id="closeControlModalLabel"><i class="bi bi-people"></i>Personal sin asignar</h5>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <!-- Body del Modal -->
        <div class="modal-body">
          <!--Contenedor de los botones del MENU -->
            <div id="m-0 p-0">
                  <i class="bi bi-table" id="btnTablaPNA" ></i> 
                  <i class="bi bi-person-plus" id="btnRegistroPNA"></i> 
            </div>
          <!--Fin MENU -->

            <hr>

          <!--Ventanad para el modal del personal no asignado -->
            <div id="ventanadModalPersonalNA">
                <!-- Tabla/Listado de personal disponible o no asignado-->
                  <div id="contTablaDisponibles" class="fade-page show">
                      <!--TITULO SECCION -->
                      <div class="card-header bg-white border-0 py-3">
                          <h5 class="mb-0">
                              <i class="bi bi-people-fill text-primary me-2"></i>
                              Personal Disponible para Asignación
                          </h5>
                          <p class="text-muted mb-0 mt-2">
                              Listado de operadores no asignados a operaciones en línea
                          </p>
                      </div>

                      <!--TABLA -->
                      <div class="table-responsive">
                          <table class="table table-hover mb-0" id="tablaPersonalNoAsignado">
                              <thead class="table-light">
                                  <tr>
                                      <th style="width: 15%;" class="py-3 px-4">Nómina</th>
                                      <th style="width: 30%;" class="py-3 px-4">Nombre del Empleado</th>
                                      <th style="width: 25%;" class="py-3 px-4 text-center">Acciones</th>
                                  </tr>
                              </thead>
                              <tbody id="tablaBodyPersonalNoAsignado">
                                  <!-- Fila 1 -->
                                  <tr>
                                      <td class="px-4 align-middle">
                                          <span class="fw-semibold">EMP-02345</span>
                                      </td>
                                      <td class="px-4 align-middle">
                                          <div class="d-flex align-items-center">
                                              <div>
                                                  <div class="fw-medium">Carlos Rodríguez</div>
                                              </div>
                                          </div>
                                      </td>
                                      <td class="px-4 align-middle text-center">
                                          <button class="btn btn-sm btn-outline-primary d-inline-flex align-items-center" id="btnAsignarModalDisponibles">
                                              <i class="bi bi-gear me-1"></i>
                                              Asignar a Estación
                                          </button>
                                      </td>
                                  </tr>
                                  
                                  <!-- Fila 2 -->
                                  <tr>
                                      <td class="px-4 align-middle">
                                          <span class="fw-semibold">EMP-01892</span>
                                      </td>
                                      <td class="px-4 align-middle">
                                          <div class="d-flex align-items-center">
                                              <div>
                                                  <div class="fw-medium">María González</div>
                                              </div>
                                          </div>
                                      </td>
                                      <td class="px-4 align-middle text-center">
                                          <button class="btn btn-sm btn-outline-primary d-inline-flex align-items-center">
                                              <i class="bi bi-gear me-1"></i>
                                              Asignar a Estación
                                          </button>
                                      </td>
                                  </tr>
                                  
                                  <!-- Fila 3 -->
                                  <tr>
                                      <td class="px-4 align-middle">
                                          <span class="fw-semibold">EMP-03215</span>
                                      </td>
                                      <td class="px-4 align-middle">
                                          <div class="d-flex align-items-center">
                                              <div>
                                                  <div class="fw-medium">Juan Pérez</div>
                                              </div>
                                          </div>
                                      </td>
                                      <td class="px-4 align-middle text-center">
                                          <button class="btn btn-sm btn-outline-primary d-inline-flex align-items-center">
                                              <i class="bi bi-gear me-1"></i>
                                              Asignar a Estación
                                          </button>
                                      </td>
                                  </tr>
                                  
                                  <!-- Fila 4 -->
                                  <tr>
                                      <td class="px-4 align-middle">
                                          <span class="fw-semibold">EMP-04128</span>
                                      </td>
                                      <td class="px-4 align-middle">
                                          <div class="d-flex align-items-center">
                                              <div>
                                                  <div class="fw-medium">Ana López</div>
                                              </div>
                                          </div>
                                      </td>
                                      <td class="px-4 align-middle text-center">
                                          <button class="btn btn-sm btn-outline-primary d-inline-flex align-items-center">
                                              <i class="bi bi-gear me-1"></i>
                                              Asignar a Estación
                                          </button>
                                      </td>
                                  </tr>
                                  
                                  <!-- Fila 5 -->
                                  <tr>
                                      <td class="px-4 align-middle">
                                          <span class="fw-semibold">EMP-02763</span>
                                      </td>
                                      <td class="px-4 align-middle">
                                          <div class="d-flex align-items-center">
                                              <div>
                                                  <div class="fw-medium">Roberto Martínez</div>
                                              </div>
                                          </div>
                                      </td>
                                      <td class="px-4 align-middle text-center">
                                          <button class="btn btn-sm btn-outline-primary d-inline-flex align-items-center">
                                              <i class="bi bi-gear me-1"></i>
                                              Asignar a Estación
                                          </button>
                                      </td>
                                  </tr>
                              </tbody>
                          </table>
                      </div>
                  </div>
                <!-- Fin tabla/listado-->

                <!--Formulario de registro de personal no asignado -->
                  <div id="contRegistroPersonalDisponible" class="fade-page d-none">
                        <div class="form-section">
                            <h3 class="section-title">
                                Registrar personal disponible o sin asignar
                            </h3>
                            <p class="text-muted mb-4">Complete la información para registrar el personal sin estacion</p>
                        </div>

                        <form class="form" id="fmPersonalNoAsignado">
                              <div class="row">
                                  <div class="col-md-6 mb-3">
                                      <label for="nomina" class="form-label required-field">
                                          <i class="bi bi-clock"></i> No. Reloj / ID Empleado
                                      </label>
                                      <div class="input-group-custom">
                                          <input type="number" 
                                                min=1 
                                                step="1" class="form-control form-control-custom" id="nominaNoAsignado" 
                                              placeholder="Ej: EMP-0256" required>
                                              <button type="button" class="input-icon" id="searchEmployee">
                                                  <i class="bi bi-search"></i>
                                              </button>
                                      </div>
                                      <div class="form-help">Ingresa el número de reloj o ID único del empleado</div>
                                  </div>
                                  
                                  <div class="col-md-6 mb-3">
                                      <label for="nombre" class="form-label required-field">
                                          <i class="bi bi-person"></i>Nombre del Operador
                                      </label>
                                      <div class="input-group-custom">
                                          <input 
                                              type="text" 
                                              class="form-control form-control-custom" 
                                              id="nombreNoAsignado" 
                                              placeholder="Selecciona o busca un operador">
                                          <button type="button" class="input-icon">
                                              <i class="bi bi-people"></i>
                                          </button>
                                      </div>
                                  </div>
                              </div>

                              <div class="row">
                                  <div class="col-md-6 mb-3">
                                      <label for="stationSelect" class="form-label required-field">
                                          <i class="bi bi-diagram-3 text-muted"></i>Linea
                                      </label>
                                      <input type="text" 
                                                        class="form-control form-control-custom border-start-1 ps-1" 
                                                        placeholder="LN-001" 
                                                        maxlength="20"
                                                        value="<?php echo $nombre?>"
                                                        style="border-color: #dee2e6;" readonly>
                                      
                                      <div class="form-help">Selecciona la estación donde se asignará el operador</div>
                                  </div>
                                  
                                  <div class="col-md-6 mb-3">
                                      <label for="assignmentDatePNA" class="form-label required-field">
                                      <i class="bi bi-calendar"></i> Fecha de registro
                                      </label>
                                      <input 
                                        type="datetime-local" 
                                        class="form-control form-control-custom" 
                                        id="assignmentDatePNA" 
                                        value="<?php echo date('Y-m-d H:i'); ?>"
                                        required>
                                      <div class="form-help">Fecha del registro</div>
                                  </div>
                              </div>
                        
                              <div class="row">
                                  <div class="col-md-6 mb-3">
                                      <label for="turnoAsignarPersonalDisponible" class="form-label">
                                          <i class="bi bi-clock-history"></i>Turno</label>
                                         <select id="turnoAsignarPersonalDisponible" class="form-control form-control-custom form-select">
                                            <option value="" selected>Selecciona una turno</option>
                                            <option value="1">Turno 1</option>
                                            <option value="2">Turno 2</option>
                                          </select>
                                  </div>
                              </div>

                              <div class="mb-3">
                                  <label for="comentarios" class="form-label">
                                      <i class="bi bi-chat-text text-muted"></i> Comentarios
                                  </label>
                                  <textarea 
                                      class="form-control form-control-custom" 
                                      id="comentariosNoAsignado" 
                                      rows="3" placeholder="Ingrese algun comentario"
                                  ></textarea>
                              </div>
                        </form>

                        <!-- Footer del Modal -->
                        <div class="modal-footer">
                          <div class="d-flex justify-content-end mt-2">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                              <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                            <button type="button" class="btn btn-primary-custom ms-1" style="color:white" id="btnGuardarDisponible">
                              <i class="bi bi-check-circle"></i> Guardar
                            </button>
                          </div>
                        </div>
                  </div>
                <!--Fin de formulario de personal no asignado -->
            </div>
          <!--Fin ventanas modal -->
        </div>
      </div>
    </div>
  </div> 
  
  <!-- Editar datos de la linea-->
  <div class="modal fade modal-close-control" id="editarLineaModal" tabindex="-1" aria-labelledby="editarLineaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <!-- Header del Modal -->
        <div class="modal-header">
          <div class="d-flex align-items-center">
            <h5 class="modal-title" id="closeControlModalLabel">Modificar datos de la linea</h5>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <!-- Body del Modal -->
        <div class="modal-body">
          <!-- Formulario de registro para agregar la linea-->
            <form class="form-body" id="lineForm">
              <!--  Información Básica -->
              <div class="form-section">
                <h5 class="modal-title mb-2 text-center">
                  <i class="bi bi-info-circle"></i>Información Básica
                </h5>

                <div class="row">
                  <div class="col-md-6 mb-2">
                    <label for="lineCode" class="form-label required-field">Código de Línea</label>
                    <div class="input-group-custom">
                      <input type="text" class="form-control form-control-custom" id="lineCode" placeholder="Ej: LN-001" readonly style="background-color:snow;" value="<?php echo $codigo?>">
                      <button type="button" class="input-icon" data-bs-toggle="tooltip" title="Código único para identificar la línea">
                        <i class="bi bi-question-circle"></i>
                      </button>
                    </div>
                  </div>

                  <div class="col-md-6 mb-2">
                    <label for="lineName" class="form-label required-field">Nombre de la Línea</label>
                    <input type="text" class="form-control form-control-custom" id="lineName" placeholder="Ej: Línea de CRV" value="<?php echo $nombre?>" required>
                  </div>
                </div>
              </div>

              <!-- Sección: Encargado -->
              <div class="form-section">
                <h5 class="modal-title mb-2">
                  <i class="bi bi-person-gear"></i>
                  Personal a Cargo
                </h5>

                <div class="mb-2">
                  <label for="supervisorSearch" class="form-label required-field">Encargado/Supervisor</label>
                  <div class="input-group-custom">
                    <input type="text" class="form-control form-control-custom" id="supervisorSearch"
                      placeholder="Buscar empleado..." value="<?php echo $encargado_supervisor?>">
                    <button type="button" class="input-icon">
                      <i class="bi bi-search"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Sección: Descripción -->
              <div class="form-section">
                <h5 class="modal-title mb-1">
                  <i class="bi bi-text-paragraph"></i>
                  Descripción
                </h5>

                <div class="mb-1">
                  <label for="lineDescription" class="form-label">Descripción de la Línea</label>
                  <textarea class="form-control form-control-custom form-textarea" id="lineDescription"
                    placeholder="Describe el propósito, procesos principales y características de esta línea de producción..."
                    rows="4"><?php echo $descripcion?></textarea>
                  <div class="form-help">Opcional: Proporciona detalles sobre esta línea</div>
                </div>
              </div>
            </form>
          <!-- Fin formulario -->
        </div>
        
        <!-- Footer del Modal -->
        <div class="modal-footer">
          <div class="d-flex justify-content-end mt-2">
            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
              <i class="bi bi-x-circle"></i> Cancelar
            </button>
            <button type="button" class="btn btn-primary-custom ms-1" id="btnGuardarEdicionLinea" style="color:white"  >
              <i class="bi bi-check-circle"></i> Guardar
            </button>
          </div>
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