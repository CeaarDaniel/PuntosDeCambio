<!-- Menu principal -->
<div class="container menu-principal py-4 py-lg-5">
  <div class="row g-4 justify-content-center mb-4">
    <!-- Imprimir etiquetas -->
    <div class="col-12 col-md-6 col-xl-4">
      <div class="card menu-card menu-card-blue h-100" onclick="cargarRuta('imprimirEtiqueta', '0')" role="button" tabindex="0">
        <div class="card-body d-flex flex-column text-center p-4">
          <h4 class="menu-card-title">Consulta / Impresi&oacute;n de Etiquetas</h4>
          <div class="icon-wrapper menu-hero-img my-3">
            <img src="./img/iconos/imprimir.png" alt="Imprimir etiquetas" class="img-fluid">
          </div>
          <p class="menu-card-text mb-4">Generar etiquetas SGA</p>
          <button type="button" class="btn menu-action-btn btn-menu-blue mt-auto">
            Ver etiquetas <i class="fa-solid fa-chevron-right ms-2"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Crear etiquetas -->
    <div class="col-12 col-md-6 col-xl-4">
      <div class="card menu-card menu-card-orange h-100" onclick="cargarRuta('crearEtiqueta', '0')" role="button" tabindex="0">
        <div class="card-body d-flex flex-column text-center p-4">
          <h4 class="menu-card-title">Creaci&oacute;n de Etiquetas</h4>
          <div class="icon-wrapper menu-hero-img my-3">
            <img src="./img/iconos/registrar.png" alt="Crear etiquetas" class="img-fluid">
          </div>
          <p class="menu-card-text mb-4">Dise&ntilde;a nuevas etiquetas</p>
          <button type="button" class="btn menu-action-btn btn-menu-orange mt-auto">
            Crear etiqueta <i class="fa-solid fa-chevron-right ms-2"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Administrar catalogos -->
    <div class="col-12 col-md-6 col-xl-4">
      <div class="card menu-card menu-card-green h-100" onclick="cargarRuta('catalogos', '0')" role="button" tabindex="0">
        <div class="card-body d-flex flex-column text-center p-4">
          <h4 class="menu-card-title">Administrar Cat&aacute;logos</h4>
          <div class="icon-wrapper menu-hero-img my-3">
            <img src="./img/iconos/catalogos.png" alt="Administrar catalogos" class="img-fluid">
          </div>
          <p class="menu-card-text mb-4">Gestiona sustancias y datos</p>
          <button type="button" class="btn menu-action-btn btn-menu-green mt-auto">
            Ver cat&aacute;logos <i class="fa-solid fa-chevron-right ms-2"></i>
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-12 col-md-4">
      <div class="card menu-info-card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-solid fa-box-open text-primary"></i>
            <h6 class="mb-0">Etiquetas Generadas</h6>
          </div>
          <div class="menu-stat">128</div>
          <div class="menu-card-line"></div>
          <p class="mb-0 small fw-semibold">Esta semana: 24</p>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-4">
      <div class="card menu-info-card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-solid fa-clipboard-list text-warning"></i>
            <h6 class="mb-0">Sustancias Registradas</h6>
          </div>
          <div class="menu-stat">56</div>
          <div class="menu-card-line"></div>
          <p class="mb-0 small">Actualizado: Hoy</p>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-4">
      <div class="card menu-info-card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-solid fa-triangle-exclamation text-danger"></i>
            <h6 class="mb-0">Alertas de Seguridad</h6>
          </div>
          <div class="menu-stat">8</div>
          <div class="menu-card-line"></div>
          <p class="mb-0 small fw-semibold text-primary">
            <i class="fa-solid fa-circle-plus text-danger me-1"></i>Revisar pendientes
          </p>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-12 col-lg-6">
      <div class="card menu-panel-card h-100">
        <div class="card-body">
          <h5 class="menu-panel-title">Actividad Reciente</h5>
          <ul class="menu-list">
            <li><span class="menu-dot bg-warning"></span>Etiqueta creada: &Aacute;cido Clorh&iacute;drico</li>
            <li><span class="menu-dot bg-danger"></span>Etiqueta impresa: Metanol</li>
            <li><span class="menu-dot bg-warning"></span>Sustancia actualizada: Acetona</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card menu-panel-card h-100">
        <div class="card-body">
          <h5 class="menu-panel-title">Gu&iacute;as y Recursos</h5>
          <ul class="menu-list menu-resource-list">
            <li><i class="fa-solid fa-file-lines me-2"></i>Gu&iacute;a del Sistema SGA</li>
            <li><i class="fa-solid fa-file-lines me-2"></i>Instructivo de Impresi&oacute;n</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
