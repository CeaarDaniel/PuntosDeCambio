<div id="lines" class="">

  <!-- Listao de lineas-->
  <div class="row d-flex justify-content-center" id="contenedorLineas">
  </div>


  <!-- BOTON FLOTANTE-->
  <span class="btn-float" data-bs-toggle="modal" data-bs-target="#modalAgregarLinea">
    <i class="fa fa-plus" style="font-size:20px"></i>
  </span>
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
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Agregar nueva línea</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <!-- Formulario de registro para agregar la linea-->
          <form class="form-body" id="lineForm">
            <!--  Información Básica -->
            <div class="form-section">
              <h5 class="modal-title mb-3">
                <i class="bi bi-info-circle"></i>
                Información Básica
              </h5>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="lineCode" class="form-label required-field">Código de Línea</label>
                  <div class="input-group-custom">
                    <input type="text" class="form-control form-control-custom" id="lineCode" placeholder="Ej: LN-001"
                      required>
                    <button type="button" class="input-icon" data-bs-toggle="tooltip"
                      title="Código único para identificar la línea">
                      <i class="bi bi-question-circle"></i>
                    </button>
                  </div>
                  <div class="form-help">Usa un código único para identificar esta línea</div>
                </div>

                <div class="col-md-6 mb-3">
                  <label for="lineName" class="form-label required-field">Nombre de la Línea</label>
                  <input type="text" class="form-control form-control-custom" id="lineName" placeholder="Ej: Línea de CRV"
                    required>
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
                <label for="supervisorSearch" class="form-label">Encargado/Supervisor</label>
                <div class="input-group-custom">
                  <input type="text" class="form-control form-control-custom" id="supervisorSearch"
                    placeholder="Buscar empleado...">
                  <button type="button" class="input-icon">
                    <i class="bi bi-search"></i>
                  </button>
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
                <label for="lineDescription" class="form-label">Descripción de la Línea</label>
                <textarea class="form-control form-control-custom form-textarea" id="lineDescription"
                  placeholder="Describe el propósito, procesos principales y características de esta línea de producción..."
                  rows="4"></textarea>
                <div class="form-help">Opcional: Proporciona detalles sobre esta línea</div>
              </div>
            </div>
          </form>
          <!-- Fin formulario -->
          <div class="d-flex justify-content-end mt-2">
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