<?php
/* Contenido de la pestaña Reportes. */
?>
  <!-- ==================================================================
       PESTA&Ntilde;A 5: Reportes
       ================================================================== -->
  <div id="raPanelReportes" class="ra-panel-group" role="tabpanel" aria-labelledby="raTabReportes" hidden>

    <!-- Estas estadísticas usan los filtros generales del tablero. -->
    <div class="ra-grid-2">
      <article class="ra-card">
        <header class="ra-card-header">
          <div>
            <span class="ra-card-kicker">Origen de la anormalidad</span>
            <h3>Estaciones donde m&aacute;s se generan</h3>
            <p>Procesos de generaci&oacute;n con mayor cantidad de anormalidades registradas.</p>
          </div>
        </header>
        <div class="ra-hbars" id="raTopEstacionesGeneracion"></div>
      </article>

      <article class="ra-card">
        <header class="ra-card-header">
          <div>
            <span class="ra-card-kicker">Detecci&oacute;n de la anormalidad</span>
            <h3>Estaciones donde m&aacute;s se detectan</h3>
            <p>Procesos donde se detecta con mayor frecuencia una condici&oacute;n anormal.</p>
          </div>
        </header>
        <div class="ra-hbars" id="raTopEstacionesDeteccion"></div>
      </article>
    </div>

    <!-- Estos filtros complementan el alcance definido en los filtros principales. -->
    <form id="raReporteFiltros" class="ra-filters" aria-label="Filtros del reporte de anormalidades">
      <div class="ra-filter-intro">
        <span class="ra-filter-icon" aria-hidden="true">
          <i class="bi bi-funnel"></i>
        </span>
        <span>
          <strong>Filtrar registros</strong>
          <small>Complementa los filtros principales con criticidad y estatus.</small>
        </span>
      </div>

      <div class="ra-filter-field ra-report-status-field">
        <span class="ra-filter-label" id="raReporteEstatusLabel">Estatus</span>
        <div id="raReporteEstatus" class="ra-report-status-options" role="group"
             aria-labelledby="raReporteEstatusLabel">
          <!-- "Todos" representa la ausencia de un filtro específico. -->
          <label class="ra-report-status-option is-all">
            <input type="checkbox" value="todos" data-ra-estatus-todos checked>
            <span>Todos</span>
          </label>
          <div id="raReporteEstatusOpciones" class="ra-report-status-list"></div>
        </div>
      </div>

      <div class="ra-filter-field">
        <label for="raReporteCriticidad">Criticidad</label>
        <select id="raReporteCriticidad" class="form-select">
          <option value="todas">Todas</option>
        </select>
      </div>

      <div class="ra-filter-field ra-filter-acciones ra-report-filter-actions">
        <button type="button" class="ra-btn ra-btn-light ra-report-clear-btn"
                id="raReporteLimpiar" title="Limpiar filtros del listado"
                aria-label="Limpiar filtros del listado">
          <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
        </button>
      </div>
    </form>

    <!-- Listado detallado de anormalidades. -->
    <article class="ra-card">
      <header class="ra-card-header">
        <div>
          <span class="ra-card-kicker">Detalle de registros</span>
          <h3>Anormalidades registradas</h3>
          <p>Consulta el listado general de anormalidades de acuerdo con los filtros aplicados.</p>
        </div>
        <span class="ra-chip" id="raReporteConteo">0 registros</span>
      </header>

      <div class="ra-table-wrap">
        <table id="raTablaReporte" class="ra-table display" style="width:100%;">
          <thead>
            <tr>
              <th class="text-center">Acciones</th>
              <th>Consecutivo</th>
              <th>Estatus</th>
              <th>&Aacute;rea</th>
              <th>L&iacute;nea</th>
              <th>Modelo</th>
              <th>Turno</th>
              <th>Fecha y hora de detecci&oacute;n</th>
              <th>Proceso / estaci&oacute;n donde se gener&oacute;</th>
              <th>Personal que detect&oacute;</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </article>

    <!-- Reporte de una anormalidad: únicamente lectura, sin formularios. -->
    <div class="modal fade" id="raModalReporteAnormalidad" tabindex="-1"
         aria-labelledby="raModalReporteTitulo" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content ra-report-modal">
          <div class="modal-header">
            <div class="ra-report-modal-title">
              <span class="ra-filter-icon" aria-hidden="true">
                <i class="bi bi-file-earmark-text"></i>
              </span>
              <div>
                <small>Reporte de la anormalidad</small>
                <h5 class="modal-title" id="raModalReporteTitulo">Cargando registro...</h5>
              </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Cerrar reporte"></button>
          </div>

          <div class="modal-body">
            <div id="raDetalleReporteEstado" class="ra-report-loading" role="status"
                 aria-live="polite">
              <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
              Consultando el flujo de la anormalidad...
            </div>

            <div id="raDetalleReporteContenido" hidden>
              <nav class="nav ra-report-detail-tabs" role="tablist"
                   aria-label="Contenido del reporte de la anormalidad">
                <button class="ra-report-detail-tab active" id="raTabFlujoNormal"
                        data-bs-toggle="tab" data-bs-target="#raPanelFlujoNormal"
                        type="button" role="tab" aria-controls="raPanelFlujoNormal"
                        aria-selected="true">
                  <i class="bi bi-diagram-3" aria-hidden="true"></i>
                  <span>
                    <strong>Flujo normal</strong>
                    <small>De la detecci&oacute;n al cierre</small>
                  </span>
                </button>

                <button class="ra-report-detail-tab" id="raTabFlujoReincidencia"
                        data-bs-toggle="tab" data-bs-target="#raPanelFlujoReincidencia"
                        type="button" role="tab" aria-controls="raPanelFlujoReincidencia"
                        aria-selected="false">
                  <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
                  <span>
                    <strong>Reincidencias</strong>
                    <small>Registro y seguimientos</small>
                  </span>
                  <span class="ra-report-tab-count" id="raDetalleTotalReincidencias">0</span>
                </button>
              </nav>

              <div class="tab-content ra-report-detail-content">
                <section class="tab-pane fade show active" id="raPanelFlujoNormal"
                         role="tabpanel" aria-labelledby="raTabFlujoNormal" tabindex="0">
                  <div id="raDetalleFlujoNormal"></div>
                </section>

                <section class="tab-pane fade" id="raPanelFlujoReincidencia"
                         role="tabpanel" aria-labelledby="raTabFlujoReincidencia" tabindex="0">
                  <div id="raDetalleFlujoReincidencia"></div>
                </section>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="ra-btn ra-btn-light" data-bs-dismiss="modal">
              Cerrar reporte
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
