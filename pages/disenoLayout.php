<?php 
  require_once('../api/conexion.php');
  $codigo = !empty($_GET['codigo']) ? urldecode($_GET['codigo']) : '';
  $nombre = !empty($_GET['nombre']) ? urldecode($_GET['nombre']) : '';
  $sql= "SELECT descripcion, encargado_supervisor from SPC_LINEAS WHERE CODIGO_LINEA = :codigo_linea";

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
  
  <!-- Favicon-->
  <link rel="icon" type="image/png" href="../../img/favicon/logoPuntosCambio.png">

  <!-- Bootstrap -->
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="../css/bootstrap-icons.min.css" />

  <!-- Font Awesome (para íconos) -->
  <link href="../css/all.min.css" rel="stylesheet"> 

  <!--Libreria Jquery --> 
  <script src="../scripts/jquery-3.7.1.min.js"></script>

  <!--Custom Css -->
  <link rel="stylesheet" href="../css/disenoLayout.css">

  <!--Data table -->
  <link href="../DataTables/datatables.min.css" rel="stylesheet">

  <!--
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.css" />
    <script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script> 
  -->
</head>  
<body>
  <div class="layout-container">
    <!-- Área principal -->
    <div id="layout-main" class="layout-main">
      <div class="layout-header" id="layout-header">

        <div class="">
          <h2 class="layout-title">Línea de Producción <?php echo $nombre?>  <br>
           <!-- <?php setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain'); echo ucfirst(strftime('%d de %B, %Y')); ?> -->
          </h2>

          <!-- IDENTIFICADOR DE LA LINEA PARA EXTRAER LOS DATOS VISIBLES -->
            <input type="hidden" id="codigoLinea" value="<?php echo $codigo?>">
            <input type="hidden" id="nombreLinea" value="<?php echo $nombre?>">
        </div>
       
        <div class="layout-controls">
          <!-- BOTONES ALEJAR ACERCAR ZOOM-->
          <div class="btn-group">
            <div class="zoom-indicator me-3" id="zoomIndicator">100%</div>

            <!--ALEJAR-->
            <button class="btn btn-info btn-sm" id="zoomOutBtn">
              <i class="bi bi-zoom-out"></i>
            </button>

            <!--ACERCAR-->
            <button class="btn btn-info btn-sm" id="zoomInBtn">
              <i class="bi bi-zoom-in"></i>
            </button>
          </div>

          <!--BOTON GUARDAR LAYOUT -->
          <button class="btn btn-success btn-sm rounded-3" id="saveLayoutBtn">
            <i class="bi bi-floppy"></i> Guardar
          </button>

          <!-- BTN AGREGAR LINEA-->
          <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregarEstacion">
            <i class="bi bi-building"></i> 
            <span data-bs-toggle="tooltip" data-bs-placement="right" title="Crear estaciones">
              Crear
            </span>
          </button>

          <!-- BTN EDITAR LINEA -->
          <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#editarLineaModal">
            <i class="bi bi-pencil"></i>
            <span data-bs-toggle="tooltip" data-bs-placement="right" title="Editar información de la línea">
              Editar Línea
            </span>
          </button>

          <!-- Botón para mostrar modal de error -->
          <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#errorModal">
            <i class="bi bi-exclamation-triangle-fill" style=""></i>
          </button>
        </div>
      </div>
      
      <div class="workspace">
        <div class="workspace-grid" id="workspaceGrid">
          <!-- Estaciones se generarán dinámicamente -->

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
          </div>
        </div>
      </div>
    </div>

    <!-- PANEL DERECHO -->
        <?PHP include('./menuDiseno.php')?>
    <!-- FIN PANEL DERECHO-->
  </div>

  <!-- Modal alerta/error-->
  <div class="modal fade app-modal" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
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
          <div class="modal-line-guide text-start" role="note">
            <span class="modal-line-guide-icon" aria-hidden="true"><i class="bi bi-shield-exclamation"></i></span>
            <div><strong>Revisa la asignación</strong><span>Consulta el motivo antes de intentar nuevamente.</span></div>
          </div>
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
  <div class="modal fade app-modal" id="modalAgregarEstacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-plus-square"></i>Agregar nueva estación</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="modal-line-guide" role="note">
            <span class="modal-line-guide-icon" aria-hidden="true"><i class="bi bi-lightbulb"></i></span>
            <div><strong>Configura la estación</strong><span>Completa los datos requeridos antes de guardar.</span></div>
          </div>
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
                  <input  type="text" maxlength="50"
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
                      ¿Requiere certificación?
                    </label>
                      <select class="form-control form-control-custom select" id="requiereCertificacion" required>
                        <option value="">--- SELECCIONE UNA OPCION ---</option>
                        <option value="0">NO</option>
                        <option value="1">SI</option>
                      </select>
                </div>
                
                <!-- Modal certificacion requerida
                <div class="mb-3">
                  <label for="certificacion" class="form-label">
                    <i class="bi bi-shield-check"></i>
                    Certificación/Capacitación Requerida
                  </label>
                  <select class="form-control form-control-custom" id="certificacion">
                    <option value="">Selecciona una certificación...</option>
                  </select>
                  <div class="form-help">Selecciona la certificación mínima requerida para operar esta estación</div>
                </div>
                -->
              </div>
            </form>
          <!-- Fin formulario -->
          <div class="d-flex justify-content-end mt-2 ">
            <button id="btnGuardarEstacion" type="button" class="btn text-white btn-primary-custom mx-2">
                <i class="bi bi-check-circle"></i> <b>Guardar</b>
            </button>
            <button type="button" class="btn btn-secondary mx-2" data-bs-dismiss="modal">
                <i class="bi bi-x-circle"></i> <b>Cancelar</b>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Editar datos de la linea-->
  <div class="modal fade app-modal modal-close-control" id="editarLineaModal" tabindex="-1" aria-labelledby="editarLineaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <!-- Header del Modal -->
        <div class="modal-header">
          <div class="d-flex align-items-center">
            <h5 class="modal-title" id="closeControlModalLabel"><i class="bi bi-pencil-square"></i>Modificar datos de la linea</h5>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <!-- Body del Modal -->
        <div class="modal-body">
          <div class="modal-line-guide" role="note">
            <span class="modal-line-guide-icon" aria-hidden="true"><i class="bi bi-info-circle"></i></span>
            <div><strong>Actualiza la información</strong><span>Verifica los cambios antes de guardar la línea.</span></div>
          </div>
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
                  <i class="bi bi-text-paragraph"></i> Descripción
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
  <script src="../DataTables/datatables.min.js"></script>
  
  <!--Custmo js -->
  <script src="../scripts/disenoLayout.js"></script> 
</body>
</html>