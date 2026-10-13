<?php
/* Contenido de la pestaña Reincidencias. */
?>
  <!-- ==================================================================
       PESTA&Ntilde;A 4: Reincidencias y frecuentes
       ================================================================== -->
  <div id="raPanelReincidencias" class="ra-panel-group" role="tabpanel" aria-labelledby="raTabReincidencias" hidden>

    <div class="ra-grid-wide">
      <!-- Top de reincidentes -->
      <article class="ra-card">
        <header class="ra-card-header">
          <div>
            <span class="ra-card-kicker">Reincidencia</span>
            <h3>Anormalidades con m&aacute;s reincidencias</h3>
            <p>Tipos de anormalidad con mayor cantidad de registros que cuentan con reincidencia.</p>
          </div>
        </header>
        <div class="ra-hbars" id="raTopReincidentes"></div>
      </article>

      <!-- Estaciones con mayor frecuencia de anormalidades reincidentes -->
      <article class="ra-card">
        <header class="ra-card-header">
          <div>
            <span class="ra-card-kicker">Estaciones</span>
            <h3>Estaciones con m&aacute;s reincidencias</h3>
            <p>Estaciones de generaci&oacute;n con mayor cantidad de anormalidades que cuentan con reincidencia.</p>
          </div>
        </header>
        <div class="ra-hbars" id="raTopEstacionesReincidencia"></div>
      </article>
    </div>

    <!-- Seguimiento y contramedidas -->
    <article class="ra-card">
      <header class="ra-card-header">
        <div>
          <span class="ra-card-kicker">Seguimiento</span>
          <h3>Anormalidades m&aacute;s comunes y sus contramedidas</h3>
          <p>Consulta los tipos m&aacute;s frecuentes y revisa las contramedidas aplicadas en registros similares.</p>
        </div>
      </header>

      <!-- Este filtro actualiza solamente la tabla de esta sección. -->
      <div class="row g-3">
        <div class="col-12 col-md-6 col-xl-4">
          <label class="form-label" for="raTipoAnormalidadFrecuente">Tipo de anormalidad</label>
          <select class="form-select" id="raTipoAnormalidadFrecuente" disabled>
            <option value="todos">Todos los tipos</option>
          </select>
        </div>
      </div>

      <div class="ra-table-wrap">
        <table id="raTablaFrecuentes" class="ra-table display" style="width:100%;">
          <thead>
            <tr>
              <th scope="col">Tipo de anormalidad</th>
              <th scope="col">&Aacute;rea</th>
              <th scope="col">L&iacute;nea</th>
              <th scope="col">Frecuencia</th>
              <th scope="col">Acciones</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </article>

    <!-- El detalle se consulta sólo cuando el usuario abre este modal. -->
    <div class="modal fade" id="raModalContramedidasFrecuentes" tabindex="-1"
         aria-hidden="true" aria-labelledby="raModalContramedidasTitulo">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <span class="ra-card-kicker">Registros similares</span>
              <h5 class="modal-title" id="raModalContramedidasTitulo">Causas y contramedidas aplicadas</h5>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>

          <div class="modal-body">
            <p class="ra-modal-registro" id="raContextoContramedidasFrecuentes"></p>

            <div class="ra-table-wrap mt-3">
              <table id="raTablaDetalleContramedidas" class="ra-table display" style="width:100%;">
                <thead>
                  <tr>
                    <th scope="col">No. seguimiento</th>
                    <th scope="col">Fecha de registro</th>
                    <th scope="col">Detalle de la anormalidad</th>
                    <th scope="col">Causa identificada</th>
                    <th scope="col">Contramedida aplicada</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="ra-btn ra-btn-light" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
