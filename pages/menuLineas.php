<div id="lines" class="lines-page">

  <div class="lines-intro">
    <div class="lines-intro-icon">
      <i class="bi bi-buildings"></i>
    </div>
    <div class="lines-intro-copy">
      <span class="section-kicker">Vista general</span>
      <h2>Líneas de producción</h2>
      <p>Selecciona una línea para consultar estaciones, personal asignado y puntos de cambio.</p>
    </div>
    <div class="lines-intro-guide">
      <i class="bi bi-cursor"></i>
      <span>Haz clic en una tarjeta para abrir su layout</span>
    </div>
  </div>

  <!-- Listao de lineas-->
  <div class="row g-4 justify-content-center" id="contenedorLineas">
  </div>


  <!-- BOTON FLOTANTE-->
  <button type="button" class="btn-float" data-bs-toggle="modal" data-bs-target="#modalAgregarLinea"
          aria-label="Agregar una nueva línea" title="Agregar línea">
    <i class="bi bi-plus-lg"></i>
  </button>
  <!-- BOTON FLOTANTE -->

  <!--
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card-option">
                <i class="fa fa-industry card-icon"></i>
                <div class="card-label">CRV 23</div>
                  <div>
                      <a href="#/gestionLineas">
                          <i class="fa fa-cogs mx-3" style="font-size:25px;"></i>
                      </a>
                      <a href="#/puntosCambio">
                          <i class="bi bi-arrow-left-right mx-3" style="font-size:25px;"></i>
                      </a>
                  </div>
            </div>
        </div>
    </div>
  -->
</div>
<!--Fin listado de lineas-->



<!-- MODALES -->

<!--Modal agregar Linea -->
<div class="modal fade" id="modalAgregarLinea" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title-group">
          <span class="modal-title-icon">
            <i class="bi bi-building-add"></i>
          </span>
          <div>
            <span class="modal-kicker">Configuración inicial</span>
            <h4 class="modal-title">Agregar nueva línea</h4>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
          <div class="modal-line-guide" role="note">
            <span class="modal-line-guide-icon d-flex justify-content-center" aria-hidden="true">
              <i class="bi bi-lightbulb"></i>
            </span>
            <div>
              <strong>Completa los datos de la línea</strong>
              <span>Los campos marcados con * son necesarios para guardar el registro.</span>
            </div>
          </div>
          <!-- Formulario de registro para agregar la linea-->
          <form class="form-body" id="lineForm">
            <!--  Información Básica -->
            <div class="form-section">
              <h5 class="modal-title mb-3">
                <i class="bi bi-info-circle"></i>
                Información de la línea
              </h5>

              <div class="row">

              <!-- CODIGO DE LA LINEA-->
                <div class="col-12 col-md-6 mb-3">
                  <label for="lineCode" class="form-label required-field">
                    <i class="bi bi-upc-scan" aria-hidden="true"></i> Código de la Línea
                  </label>
                  <div class="input-group-custom">
                    <input type="text" class="form-control form-control-custom" id="lineCode" placeholder="Ej: LN-001"
                      required>
                    <button type="button" class="input-icon" data-bs-toggle="tooltip"
                      title="Código único para identificar la línea" aria-label="Ayuda sobre el código de línea">
                      <i class="bi bi-question-circle"></i>
                    </button>
                  </div>
                  <div class="form-help">
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    <span>Usa un código único para identificar esta línea.</span>
                  </div>
                </div>

                <!--NOBRE DE LA LINEA-->
                <div class="col-12 col-md-6 mb-3">
                  <label for="lineName" class="form-label required-field">
                    <i class="bi bi-tag" aria-hidden="true"></i> Nombre de la Línea
                  </label>
                  <input type="text" class="form-control form-control-custom" id="lineName" placeholder="Ej: Línea de CRV"
                    required>
                  <div class="form-help">
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    <span>Escribe un nombre claro y fácil de reconocer.</span>
                  </div>
                </div>

                <div class="col-12 col-md-8 mb-3">
                  <label for="idArea" class="form-label required-field">
                    <i class="bi bi-bounding-box"></i>Área
                  </label>
                  <select id="idArea" class="form-control form-select" required>
                    <option value="">Seleccione una opción...</option>
                  </select> 

                  <div class="form-help">
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    <span>Área a la que pertenece la linea (SENSOR, CUARTO LIMPIO)</span>
                  </div>
                </div>


                <!-- IMAGEN DE LA LINEA-->
                <div class="col-12 col-md-8 mb-3">
                  <label for="imageLine" class="form-label">
                    <i class="bi bi-image" aria-hidden="true"></i> Imagen
                  </label>
                  <input type="file" class="form-control form-control-custom" id="imageLine">
                  <div class="form-help">
                    <i class="bi bi-image" aria-hidden="true"></i>
                    <span>Ingrese una imagen representativa</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Sección: Encargado -->
            <div class="form-section">
              <h5 class="modal-title mb-3">
                <i class="bi bi-person-gear"></i>
                Personal a Cargo
              </h5>

              <div class="mb-3">
                <label for="supervisorSearch" class="form-label">
                  <i class="bi bi-person-badge" aria-hidden="true"></i> Encargado/Supervisor
                </label>

                <!--Nomina del trabajador -->
                <div class="input-group-custom">
                  <input type="number" min=0 class="form-control form-control-custom" id="supervisorSearch"
                    placeholder="Buscar empleado...">
                  <button type="button" class="input-icon" title="Buscar encargado" aria-label="Buscar encargado">
                    <i class="bi bi-search"></i>
                  </button>
                </div>

                <!--Nombre del supervisor -->
                <div class="lines-intro-guide mt-2">
                  <i class="bi bi-person" aria-hidden="true"></i>
                  <span class="fw-bold" id="nombreSupervisorSearch"> Nombre del empleado... </span>
                </div>

                <div class="form-help">
                  <i class="bi bi-person-check" aria-hidden="true"></i>
                  <span>Busca a la persona responsable de esta línea.</span>
                </div>
              </div>
            </div>

            <!-- Sección: Descripción -->
            <div class="form-section">
              <h5 class="modal-title mb-3">
                <i class="bi bi-text-paragraph"></i>
                Descripción
              </h5>

              <div class="mb-3">
                <label for="lineDescription" class="form-label">
                  <i class="bi bi-card-text" aria-hidden="true"></i> Descripción de la Línea
                </label>
                <textarea class="form-control form-control-custom form-textarea" id="lineDescription"
                  placeholder="Describe el propósito, procesos principales y características de esta línea de producción..."
                  rows="4"></textarea>
                <div class="form-help">
                  <i class="bi bi-pencil-square" aria-hidden="true"></i>
                  <span>Opcional: proporciona detalles que ayuden a distinguir esta línea.</span>
                </div>
              </div>
            </div>
          </form>
          <!-- Fin formulario -->
          <div class="modal-actions">
            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
              <i class="bi bi-x-circle"></i> Cancelar
            </button>
            <button type="button" class="btn btn-primary-custom ms-1" id="btnGuardarLinea">
              <i class="bi bi-check-circle"></i> Guardar
            </button>
          </div>
      </div>
    </div>
  </div>
</div>
<!--Fin Modal Agregar Linea -->