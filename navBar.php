    <!-- Control de expansión de la barra -->
    <input type="checkbox" class="sidebar-toggle-input" id="sidebarToggle"
           aria-label="Expandir o comprimir la navegación">

    <!-- Barra de navegacion -->
    <aside class="sidebar" id="mainSidebar" aria-label="Navegación principal">
      <button type="button" class="sidebar-mobile-close"
              aria-label="Cerrar menú de navegación" aria-controls="mainSidebar">
        <i class="bi bi-x-lg" aria-hidden="true"></i>
      </button>

      <div class="sidebar-header">
        <span class="brand-mark">
          <i class="bi bi-diagram-3"></i>
        </span>
        <div class="brand-copy">
          <strong>Sistema PC</strong>
          <span>Control de producción</span>
        </div>
      </div>

      <label for="sidebarToggle" class="sidebar-toggle" title="Expandir o comprimir navegación">
        <i class="bi bi-chevron-right icon-expand"></i>
        <i class="bi bi-chevron-left icon-collapse"></i>
        <span class="visually-hidden">Expandir o comprimir navegación</span>
      </label>

      <div class="sidebar-section-label">Operación</div>

      <ul class="sidebar-menu">
        <!-- Mi perfil: no requiere permisos, solo sesión (pages/perfil.php) -->
        <li>
          <a href="#/perfil" data-tooltip="Mi perfil" aria-label="Mi perfil">
            <span class="nav-icon">
              <i class="bi bi-person-circle"></i>
            </span>
            <span class="nav-copy">
              <strong>Mi perfil</strong>
              <small>Cambiar contraseña</small>
            </span>
          </a>
        </li>

        <!-- Dashboard oculto temporalmente mientras se termina; para mostrarlo, quitar d-none
             y regresar 'dashboard' como sección inicial en scripts/main.js (obtenerSeccionActual). -->
        <li class="d-none">
          <a href="#/dashboard" data-tooltip="Dashboard" aria-label="Dashboard">
            <span class="nav-icon">
              <i class="bi bi-speedometer2"></i>
            </span>
            <span class="nav-copy">
              <strong>Dashboard</strong>
              <small>Resumen general</small>
            </span>
          </a>
        </li>

        <li>
          <a href="#/menuLineas" data-tooltip="Líneas" aria-label="Líneas">
            <span class="nav-icon">
              <i class="bi bi-grid-3x3-gap"></i>
            </span>
            <span class="nav-copy">
              <strong>Líneas</strong>
              <small>Gestión y seguimiento</small>
            </span>
          </a>
        </li>

        <li>
          <a href="#/anormalidades" data-tooltip="Registro de anormalidades" aria-label="Registro de anormalidades">
            <span class="nav-icon">
              <i class="bi bi-exclamation-triangle"></i>
            </span>
            <span class="nav-copy">
              <strong>Anormalidades</strong>
              <small>Registro de anormalidades</small>
            </span>
          </a>
        </li>

        <li>
          <a href="#/reportesAnormalidades" data-tooltip="Reportes de anormalidades" aria-label="Reportes de anormalidades">
            <span class="nav-icon">
              <i class="bi bi-clipboard-data"></i>
            </span>
            <span class="nav-copy">
              <strong>Reportes de anormalidades</strong>
              <small>Indicadores y seguimiento</small>
            </span>
          </a>
        </li>

        <li>
          <a href="#/menuCertificaciones" data-tooltip="Certificaciones" aria-label="Certificaciones">
            <span class="nav-icon">
              <i class="bi bi-award"></i>
            </span>
            <span class="nav-copy">
              <strong>Certificaciones</strong>
              <small>Control de competencias</small>
            </span>
          </a>
        </li>

        <li>
          <a href="#/menuReportes" data-tooltip="Reportes" aria-label="Reportes">
            <span class="nav-icon">
              <i class="bi bi-graph-up"></i>
            </span>
            <span class="nav-copy">
              <strong>Reportes</strong>
              <small>Indicadores y estadísticas</small>
            </span>
          </a>
        </li>
      </ul>

      <div class="sidebar-footer">
        <i class="bi bi-info-circle"></i>
        <span>Selecciona una opción para comenzar.</span>
      </div>
    </aside>

    <button type="button" class="sidebar-mobile-backdrop" tabindex="-1"
            aria-label="Cerrar menú de navegación"></button>
