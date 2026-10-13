<?php
/* Contenido de la pestaña Resumen general. */
?>
  <!-- ==================================================================
       PESTA&Ntilde;A 1: Resumen general
       ================================================================== -->
  <div id="raPanelResumen" class="ra-panel-group" role="tabpanel" aria-labelledby="raTabResumen">

    <!-- Hallazgos principales del periodo -->
    <article class="ra-card">
      <header class="ra-card-header">
        <div>
          <span class="ra-card-kicker">Lectura r&aacute;pida</span>
          <h3>Hallazgos del periodo</h3>
          <p>Observaciones generadas a partir de la informaci&oacute;n mostrada.</p>
        </div>
      </header>
      <div class="ra-kpi-grid" id="raHallazgos"></div>
    </article>

    <!-- Hist&oacute;rico anual por mes -->
    <article class="ra-card">
      <header class="ra-card-header">
        <div>
          <span class="ra-card-kicker">Hist&oacute;rico anual</span>
          <h3>Anormalidades registradas por mes</h3>
          <p id="raHistoricoDescripcion">Total mensual de todas las l&iacute;neas durante el a&ntilde;o.</p>
        </div>
        <span class="ra-chip" id="raHistoricoTotal">0 registros</span>
      </header>

      <!-- Cambia entre el total mensual y el desglose por l&iacute;nea. -->
      <div class="ra-subtabs" role="tablist" aria-label="Vista del hist&oacute;rico anual">
        <button type="button" class="ra-subtab is-active" role="tab"
                data-ra-historico="total" aria-selected="true">
          <i class="bi bi-graph-up" aria-hidden="true"></i>
          Total por mes
        </button>
        <button type="button" class="ra-subtab" role="tab"
                data-ra-historico="detalle" aria-selected="false">
          <i class="bi bi-bar-chart" aria-hidden="true"></i>
          Detalle por l&iacute;nea
        </button>
      </div>

      <!-- La escala queda fuera del contenedor desplazable para permanecer visible. -->
      <div class="ra-combo-frame">
        <div class="ra-combo-scale" id="raHistoricoEscala" aria-hidden="true"></div>
        <div class="ra-combo-wrap">
          <div id="raHistoricoMeses" role="img"
               aria-label="Gr&aacute;fica de anormalidades registradas por mes"></div>
        </div>
      </div>
      <div class="ra-legend ra-legend-inline" id="raHistoricoLeyenda"></div>
    </article>

    <div class="ra-grid-2">
      <!-- Pastel por &aacute;rea -->
      <article class="ra-card">
        <header class="ra-card-header">
          <div>
            <span class="ra-card-kicker">Distribuci&oacute;n por &aacute;rea</span>
            <h3>Anormalidades por &aacute;rea</h3>
            <p>Porcentaje del total registrado en Sensor y Cuarto Limpio.</p>
          </div>
        </header>
        <div class="ra-donut-block">
          <div class="ra-donut" id="raAreaDonut" role="img" aria-label="Distribuci&oacute;n de anormalidades por &aacute;rea">
            <div class="ra-donut-center">
              <strong id="raAreaTotal">0</strong>
              <span>Total</span>
            </div>
          </div>
          <div class="ra-legend" id="raAreaLeyenda"></div>
        </div>
      </article>

      <!-- Cerradas vs pendientes -->
      <article class="ra-card">
        <header class="ra-card-header">
          <div>
            <span class="ra-card-kicker">Avance del flujo</span>
            <h3>Finalizadas vs en proceso</h3>
            <p>Comparativo entre anormalidades finalizadas y las que siguen en alguna etapa del flujo.</p>
          </div>
        </header>
        <div class="ra-donut-block">
          <div class="ra-donut" id="raEstatusDonut" role="img" aria-label="Anormalidades finalizadas contra anormalidades en proceso">
            <div class="ra-donut-center">
              <strong id="raEstatusPorcentaje">0%</strong>
              <span>Finalizadas</span>
            </div>
          </div>
          <div class="ra-legend" id="raEstatusLeyenda"></div>
        </div>
      </article>
    </div>
  </div>
