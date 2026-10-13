<?php
/* Contenido de la pestaña Críticas y tipos. */
?>
  <!-- ==================================================================
       PESTA&Ntilde;A 3: Cr&iacute;ticas y tipos
       ================================================================== -->
  <div id="raPanelCriticas" class="ra-panel-group" role="tabpanel" aria-labelledby="raTabCriticas" hidden>

    <div class="ra-grid-2">
      <!-- Top cr&iacute;ticas -->
      <article class="ra-card">
        <header class="ra-card-header">
          <div>
            <span class="ra-card-kicker">Top de frecuencia</span>
            <h3>Anormalidades cr&iacute;ticas m&aacute;s frecuentes</h3>
            <p>Tipos de anormalidad cr&iacute;tica con mayor n&uacute;mero de registros.</p>
          </div>
          <span class="ra-chip">Cr&iacute;ticas</span>
        </header>
        <div class="ra-hbars" id="raTopCriticas"></div>
      </article>

      <!-- Top no cr&iacute;ticas -->
      <article class="ra-card">
        <header class="ra-card-header">
          <div>
            <span class="ra-card-kicker">Top de frecuencia</span>
            <h3>Anormalidades no cr&iacute;ticas m&aacute;s frecuentes</h3>
            <p>Tipos de anormalidad no cr&iacute;tica con mayor n&uacute;mero de registros.</p>
          </div>
          <span class="ra-chip">No cr&iacute;ticas</span>
        </header>
        <div class="ra-hbars" id="raTopNoCriticas"></div>
      </article>
    </div>

    <!-- Nivel de respuesta -->
    <article class="ra-card ra-card-nivel-respuesta">
      <header class="ra-card-header">
        <div>
          <span class="ra-card-kicker">Cat&aacute;logo de nivel de respuesta</span>
          <h3>Nivel de respuesta aplicado</h3>
          <p>C&oacute;mo se atendieron las anormalidades seg&uacute;n su nivel de respuesta.</p>
        </div>
      </header>
      <div class="ra-level-chart" id="raNivelRespuesta" role="img"
           aria-label="Distribuci&oacute;n de anormalidades por nivel de respuesta"></div>
    </article>

    <!-- Estatus de cr&iacute;ticas, ubicado al final de esta pesta&ntilde;a. -->
    <article class="ra-card">
      <header class="ra-card-header">
        <div>
          <span class="ra-card-kicker">Seguimiento</span>
          <h3>Estatus de anormalidades cr&iacute;ticas</h3>
          <p>Listado de anormalidades cr&iacute;ticas que todav&iacute;a no han finalizado.</p>
        </div>
      </header>
      <div class="ra-table-wrap">
        <table class="ra-table" id="raTablaEstatusCriticas">
          <thead>
            <tr>
              <th scope="col">No. seguimiento</th>
              <th scope="col">Fecha de detecci&oacute;n</th>
              <th scope="col">Turno</th>
              <th scope="col">&Aacute;rea</th>
              <th scope="col">L&iacute;nea</th>
              <th scope="col">Tipo de anormalidad</th>
              <th scope="col">Estatus</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </article>
  </div>
