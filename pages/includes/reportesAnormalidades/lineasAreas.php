<?php
/* Contenido de la pestaña Líneas y áreas. */
?>
  <!-- ==================================================================
       PESTA&Ntilde;A 2: L&iacute;neas y &aacute;reas
       ================================================================== -->
  <div id="raPanelLineas" class="ra-panel-group" role="tabpanel" aria-labelledby="raTabLineas" hidden>

    <!-- Cr&iacute;ticas vs no cr&iacute;ticas por l&iacute;nea -->
    <article class="ra-card">
      <header class="ra-card-header">
        <div>
          <span class="ra-card-kicker">Comparativo por l&iacute;nea</span>
          <h3>Anormalidades cr&iacute;ticas y no cr&iacute;ticas</h3>
          <p>Cantidad registrada en cada l&iacute;nea, separada por nivel de criticidad.</p>
        </div>
        <span class="ra-chip">
          <span class="ra-legend-dot" style="background: var(--ra-critica);"></span> Cr&iacute;ticas
          &nbsp;
          <span class="ra-legend-dot" style="background: var(--ra-nocritica);"></span> No cr&iacute;ticas
        </span>
      </header>
      <div class="ra-groups-wrap" tabindex="0"
           aria-label="Desplazamiento horizontal de la gr&aacute;fica por l&iacute;nea">
        <div class="ra-groups" id="raCriticasPorLinea" role="img"
             aria-label="Gr&aacute;fica comparativa de anormalidades cr&iacute;ticas y no cr&iacute;ticas por l&iacute;nea"></div>
      </div>
    </article>

    <!-- Total por l&iacute;nea: ocupa una fila completa. -->
    <article class="ra-card">
      <header class="ra-card-header">
        <div>
          <span class="ra-card-kicker">Volumen</span>
          <h3>Total de anormalidades por l&iacute;nea</h3>
          <p>Participaci&oacute;n de cada l&iacute;nea sobre el total registrado.</p>
        </div>
      </header>
      <div class="ra-hbars" id="raTotalPorLinea"></div>
    </article>

    <!-- Tendencia: se muestra en una fila independiente. -->
    <article class="ra-card">
      <header class="ra-card-header">
        <div>
          <span class="ra-card-kicker">Tendencia</span>
          <h3>Evoluci&oacute;n mensual</h3>
          <p>Comportamiento por &aacute;rea o l&iacute;nea seg&uacute;n los filtros seleccionados.</p>
        </div>
      </header>
      <div id="raTendencia" role="img" aria-label="Gr&aacute;fica de tendencia mensual por &aacute;rea o l&iacute;nea"></div>
      <div class="ra-legend" id="raTendenciaLeyenda"></div>
    </article>

    <!-- Clasificaci&oacute;n 5M: pastel y leyenda generados con los datos de SQL Server. -->
    <article class="ra-card">
      <header class="ra-card-header">
        <div>
          <span class="ra-card-kicker">Clasificaci&oacute;n 5M</span>
          <h3>Porcentaje por tipo M</h3>
          <p>Cantidad y participaci&oacute;n de las anormalidades en cada categor&iacute;a 5M.</p>
        </div>
      </header>
      <div class="ra-donut-block ra-5m-pie-block" id="ra5m">
        <div class="ra-donut ra-pie" id="ra5mPie" role="img"
             aria-label="Distribuci&oacute;n de anormalidades por clasificaci&oacute;n 5M"></div>
        <div class="ra-legend ra-5m-pie-legend" id="ra5mLeyenda"
             aria-label="Detalle de la clasificaci&oacute;n 5M"></div>
      </div>
    </article>
  </div>
