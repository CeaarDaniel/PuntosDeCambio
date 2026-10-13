<!-- =========================================================
     Reportes de anormalidades
     Interfaz de usuario. Los contenedores se llenan desde
     scripts/reportesAnormalidades.js. Los catálogos de los filtros se
     obtienen desde api/operacionesReportesAnormalidades.php.
     ========================================================= -->

<div id="reportesAnormalidadesView">

  <!-- ============================ Encabezado ============================ -->
  <section class="ra-hero" aria-labelledby="raTitulo">
    <div class="ra-hero-copy">
      <span class="ra-eyebrow">
        <i class="bi bi-clipboard-data" aria-hidden="true"></i>
        Indicadores de anormalidades
      </span>
      <h2 id="raTitulo">Reportes de anormalidades</h2>
      <p>Consulta el comportamiento de las anormalidades registradas por l&iacute;nea, &aacute;rea, criticidad y tipo, junto con su seguimiento y contramedidas.</p>
    </div>

    <div class="ra-hero-badge">
      <i class="bi bi-funnel" aria-hidden="true"></i>
      <span>
        <strong id="raScope">Todas las &aacute;reas</strong>
        <small id="raScopePeriodo">Periodo anual</small>
      </span>
    </div>
  </section>

  <!-- ============================== Filtros ============================== -->
  <form id="raFiltros" class="ra-filters ra-main-filters"
        aria-label="Filtros de reportes de anormalidades">
    <div class="ra-filter-intro">
      <span class="ra-filter-icon" aria-hidden="true">
        <i class="bi bi-sliders"></i>
      </span>
      <span>
        <strong>Configurar reporte</strong>
        <small>Selecciona el periodo y el alcance de la informaci&oacute;n.</small>
      </span>
      <button type="button" id="raCerrarFiltros" class="ra-filter-toggle-close"
              aria-label="Ocultar filtros" aria-controls="raFiltros" aria-expanded="true"
              title="Ocultar filtros">
        <i class="bi bi-x-lg" aria-hidden="true"></i>
      </button>
    </div>

    <div class="ra-filter-field">
      <label for="raFechaInicio">Desde</label>
      <input type="date" id="raFechaInicio" class="form-control">
    </div>

    <div class="ra-filter-field">
      <label for="raFechaFin">Hasta</label>
      <input type="date" id="raFechaFin" class="form-control">
    </div>

    <div class="ra-filter-field">
      <label for="raArea">&Aacute;rea</label>
      <select id="raArea" class="form-select">
        <option value="todas">Todas las &aacute;reas</option>
      </select>
    </div>

    <div class="ra-filter-field">
      <label for="raLinea">L&iacute;nea</label>
      <select id="raLinea" class="form-select" disabled>
        <option value="todas">Todas las l&iacute;neas</option>
      </select>
    </div>

    <div class="ra-filter-field">
      <label for="raTurno">Turno</label>
      <select id="raTurno" class="form-select">
        <option value="todos">Todos los turnos</option>
      </select>
    </div>
  </form>

  <!-- Aparece al contraer la barra y permite recuperarla desde cualquier posición. -->
  <button type="button" id="raAbrirFiltros" class="ra-filter-floating-trigger"
          aria-label="Mostrar filtros" aria-controls="raFiltros" aria-expanded="false"
          title="Mostrar filtros" hidden>
    <i class="bi bi-sliders" aria-hidden="true"></i>
    <span>Filtros</span>
  </button>

  <!-- =============================== KPIs =============================== -->
  <section id="raKpis" class="ra-kpi-grid" aria-label="Resumen general de anormalidades"></section>

  <!-- ============================= Pesta&ntilde;as ============================= -->
  <nav class="ra-tabs" role="tablist" aria-label="Categor&iacute;as de reportes">
    <button type="button" class="ra-tab is-active" role="tab" data-ra-tab="resumen"
            id="raTabResumen" aria-controls="raPanelResumen" aria-selected="true">
      <i class="bi bi-graph-up" aria-hidden="true"></i>
      <span>
        <strong>Resumen general</strong>
        <small>Hist&oacute;rico, &aacute;reas y estatus</small>
      </span>
    </button>

    <button type="button" class="ra-tab" role="tab" data-ra-tab="lineas"
            id="raTabLineas" aria-controls="raPanelLineas" aria-selected="false">
      <i class="bi bi-diagram-3" aria-hidden="true"></i>
      <span>
        <strong>L&iacute;neas y &aacute;reas</strong>
        <small>Comparativos y factores</small>
      </span>
    </button>

    <button type="button" class="ra-tab" role="tab" data-ra-tab="criticas"
            id="raTabCriticas" aria-controls="raPanelCriticas" aria-selected="false">
      <i class="bi bi-exclamation-octagon" aria-hidden="true"></i>
      <span>
        <strong>Cr&iacute;ticas y tipos</strong>
        <small>Frecuencia y seguimiento</small>
      </span>
    </button>

    <button type="button" class="ra-tab" role="tab" data-ra-tab="reincidencias"
            id="raTabReincidencias" aria-controls="raPanelReincidencias" aria-selected="false">
      <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
      <span>
        <strong>Reincidencias</strong>
        <small>Frecuentes y contramedidas</small>
      </span>
    </button>

    <button type="button" class="ra-tab" role="tab" data-ra-tab="reportes"
            id="raTabReportes" aria-controls="raPanelReportes" aria-selected="false">
      <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
      <span>
        <strong>Reportes</strong>
        <small>Listado general</small>
      </span>
    </button>
  </nav>

  <?php
  /*
   * La página central conserva los filtros y la navegación.
   * Cada panel vive en un archivo independiente para facilitar su mantenimiento.
   * Los include se procesan en el servidor: cambiar de pestaña no genera otra
   * petición HTTP; JavaScript se encarga de cargar sus datos cuando se necesita.
   */
  include __DIR__ . '/includes/reportesAnormalidades/resumenGeneral.php';
  include __DIR__ . '/includes/reportesAnormalidades/lineasAreas.php';
  include __DIR__ . '/includes/reportesAnormalidades/criticasTipos.php';
  include __DIR__ . '/includes/reportesAnormalidades/reincidencias.php';
  include __DIR__ . '/includes/reportesAnormalidades/reporteDetallado.php';
  ?>

</div>
<!-- Fin reportes de anormalidades -->
