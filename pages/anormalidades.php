<?php
  require_once('../api/sesion.php');

  /* Sin el permiso de consulta no se entrega la vista. */
  exigirAcceso('anormalidades');
?>
<!-- Vista del listado y flujo de seguimiento de anormalidades. -->
<div id="anormalidadesView" class="anomalies-view">
    <!-- Encabezado de la sección -->
    <section class="anomalies-hero" aria-labelledby="anomaliesPageTitle">
        <div class="anomalies-hero-copy">
            <span class="anomalies-eyebrow">
                <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                Control de anormalidades
            </span>
            <h2 id="anomaliesPageTitle">Registro de anormalidades</h2>
            <p>Consulta las situaciones detectadas fuera de las condiciones normales de operación.</p>
        </div>

    </section>

    <!-- Primer apartado: filtros y listado principal -->
    <section class="anomalies-card" aria-labelledby="anomaliesTableTitle">
        <header class="anomalies-card-header">
            <div>
                <span class="anomalies-section-label">CONSULTA DE REGISTROS</span>
                <h3 id="anomaliesTableTitle">Anormalidades registradas</h3>
                <p>Localiza un registro por sus datos principales o consulta sus acciones disponibles.</p>
            </div>
            <span class="anomalies-record-count">
                <i class="bi bi-list-check" aria-hidden="true"></i>
                <span id="anormalidadesTotalRegistros">0 registros</span>
            </span>
        </header>

        <!-- Los catálogos se cargan al iniciar y estos criterios delimitan la consulta del listado. -->
        <div class="anomalies-filters" aria-label="Filtros del listado de anormalidades">
            <div class="anomalies-filter-intro">
                <span class="anomalies-filter-icon">
                    <i class="bi bi-funnel" aria-hidden="true"></i>
                </span>
                <span>
                    <strong>Filtrar información</strong>
                    <small>Selecciona uno o varios criterios.</small>
                </span>
            </div>

            <div class="anomalies-filter-field">
                <label for="anormalidadFechaInicio">Fecha inicial</label>
                <input type="date" class="form-control" id="anormalidadFechaInicio" value="">
            </div>

            <div class="anomalies-filter-field">
                <label for="anormalidadFechaFin">Fecha final</label>
                <input type="date" class="form-control" id="anormalidadFechaFin" value="">
            </div>

            <div class="anomalies-filter-field">
                <label for="anormalidadTurno">Turno</label>
                <select class="form-select" id="anormalidadTurno">
                    <option value="">Todos los turnos</option>
                </select>
            </div>

            <!-- Filtra por el área responsable que quedó asignada en el cierre
                 del reporte de calidad, o sea a qué gerencia le toca autorizar. -->
            <div class="anomalies-filter-field">
                <label for="anormalidadFactorGerente">Gerente / encargado</label>
                <select class="form-select" id="anormalidadFactorGerente">
                    <option value="">Todos</option>
                </select>
            </div>

            <div class="anomalies-filter-field">
                <label for="anormalidadArea">Área</label>
                <select class="form-select" id="anormalidadArea">
                    <option value="">Todas</option>
                </select>
            </div>

            <div class="anomalies-filter-field">
                <label for="anormalidadLinea">Línea</label>
                <select class="form-select" id="anormalidadLinea">
                    <option value="">Todas</option>
                </select>
            </div>
        </div>

        <!-- Referencia clara para reconocer las acciones disponibles en cada fila. -->
        <div class="anomalies-actions-guide" aria-label="Guía de acciones de la tabla">
            <span class="actions-guide-title">Acciones:</span>
            <span><i class="bi bi-info-circle action-info" aria-hidden="true"></i> Detalle</span>
            <span><i class="bi bi-arrow-repeat action-repeat" aria-hidden="true"></i> Reincidencias</span>
            <span><i class="bi bi-plus-circle action-recurrence-add" aria-hidden="true"></i> Registrar reincidencia</span>
        </div>

        <!-- DataTable: las filas se cargan una vez desde la API y se filtran localmente. -->
        <div class="anomalies-table-shell">
            <table id="tablaAnormalidades" class="table anomalies-table align-middle w-100">
                <thead>
                    <tr>
                        <th class="text-center">Acciones</th>
                        <th>ID</th>
                        <th>Consecutivo</th>
                        <th>Estatus</th>
                        <th>Área</th>
                        <th>Línea</th>
                        <th>Turno</th>
                        <th>Detección</th>
                        <th>Proceso / estación</th>
                        <th>Personal que detectó</th>
                        <th>Factor</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>

    <!-- Botón flotante que abre el formulario visual del registro inicial. -->
    <button type="button" class="btn-float anomaly-new-button"
            data-bs-toggle="modal" data-bs-target="#modalNuevaAnormalidad"
            title="Registrar una nueva anormalidad" aria-label="Registrar una nueva anormalidad">
        <i class="bi bi-plus-lg" aria-hidden="true"></i>
        <span>Nueva anormalidad</span>
    </button>

    <!-- Formulario visual del primer paso del flujo de anormalidades. -->
    <div class="modal fade" id="modalNuevaAnormalidad" tabindex="-1"
         aria-labelledby="modalNuevaAnormalidadLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable anomaly-modal-dialog">
            <div class="modal-content anomaly-modal">
                <form id="formRegistroInicialAnormalidad" novalidate>
                    <div class="modal-header">
                        <div class="anomaly-modal-title-group">
                            <span class="anomaly-modal-icon">
                                <i class="bi bi-clipboard2-plus" aria-hidden="true"></i>
                            </span>
                            <div>
                                <span class="anomaly-modal-eyebrow">Inicio del flujo de registro</span>
                                <h2 class="modal-title" id="modalNuevaAnormalidadLabel">Nueva anormalidad</h2>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar formulario"></button>
                    </div>

                    <div class="modal-body">
                        <div class="anomaly-form-intro">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            <span>Registra la información conocida al momento de detectar la condición anormal. Los campos con <strong>*</strong> son obligatorios.</span>
                        </div>

                        <!-- Bloque 1: identificación general del registro. -->
                        <section class="anomaly-form-section" aria-labelledby="generalDataTitle">
                            <div class="anomaly-form-section-header">
                                <span class="anomaly-section-number">1</span>
                                <div>
                                    <span class="anomaly-form-kicker">INFORMACIÓN DEL REGISTRO</span>
                                    <h3 id="generalDataTitle">Datos generales</h3>
                                    <p>Identifica cuándo se detectó la situación.</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-lg-4">
                                    <label class="form-label" for="registroNumeroSeguimiento">No. seguimiento (referencia)</label>
                                    <div class="anomaly-input-icon">
                                        <i class="bi bi-hash" aria-hidden="true"></i>
                                        <input type="text" class="form-control" id="registroNumeroSeguimiento"
                                               name="numero_seguimiento" maxlength="30"
                                               placeholder="Selecciona una línea"
                                               autocomplete="off" readonly aria-readonly="true">
                                    </div>
                                    <small class="form-text text-muted">El valor definitivo se generará y validará al guardar.</small>
                                </div>

                                <div class="col-lg-5">
                                    <label class="form-label" for="registroFechaDeteccion">Fecha y hora de detección <span class="required">*</span></label>
                                    <div class="anomaly-input-icon">
                                        <i class="bi bi-calendar-event" aria-hidden="true"></i>
                                        <input type="datetime-local" class="form-control" id="registroFechaDeteccion"
                                               name="fecha_deteccion" readonly aria-readonly="true" required>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <label class="form-label" for="registroTurno">Turno <span class="required">*</span></label>
                                    <select class="form-select" id="registroTurno" disabled aria-readonly="true">
                                        <option value="" selected>Cargando turno...</option>
                                    </select>
                                    <!-- Conserva el IDe del turno porque los selects deshabilitados no se envían. -->
                                    <input type="hidden" id="registroTurnoId" name="turno" value="">
                                </div>
                            </div>
                        </section>

                        <!-- Bloque 2: información capturada por Manufactura o personal administrativo. -->
                        <section class="anomaly-form-section" aria-labelledby="abnormalConditionTitle">
                            <div class="anomaly-form-section-header">
                                <span class="anomaly-section-number anomaly-section-number-cyan">2</span>
                                <div>
                                    <span class="anomaly-form-kicker">REGISTRO DE LÍDER, SUBLÍDER DE MANUFACTURA O PERSONAL ADMINISTRATIVO</span>
                                    <h3 id="abnormalConditionTitle">Registro de la condición anormal</h3>
                                    <p>Describe dónde ocurrió y qué situación fue detectada.</p>
                                </div>
                            </div>

                            <!-- Ubicación de la condición detectada. -->
                            <fieldset class="anomaly-field-group">
                                <legend><i class="bi bi-geo-alt" aria-hidden="true"></i> Condición detectada</legend>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label" for="registroArea">Área <span class="required">*</span></label>
                                        <select class="form-select" id="registroArea" name="id_area" disabled required>
                                            <option value="" selected>Cargando áreas...</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label" for="registroLinea">Línea <span class="required">*</span></label>
                                        <select class="form-select" id="registroLinea" name="codigo_linea" disabled required>
                                            <option value="" selected>Primero selecciona el área</option>
                                        </select>
                                    </div>

                                    <!-- Modelos asignados a la línea (SRD_MODELOS_LINEA). Opcional: vacío se guarda como NULL. -->
                                    <div class="col-md-4">
                                        <label class="form-label" for="registroModelo">Modelo</label>
                                        <select class="form-select" id="registroModelo" name="id_modelo" disabled>
                                            <option value="" selected>Primero selecciona la línea</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <div class="anomaly-label-row">
                                            <label class="form-label" for="registroDetalleAnormalidad">Detalles de la anormalidad <span class="required">*</span></label>
                                            <span id="detalleAnormalidadContador">0 / 500</span>
                                        </div>
                                        <textarea class="form-control" id="registroDetalleAnormalidad" name="detalle_anormalidad"
                                                  rows="4" maxlength="500"
                                                  placeholder="Describe claramente qué sucedió, qué se observó y cualquier dato relevante."
                                                  required></textarea>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label" for="registroLote">No. Lote <span class="required">*</span></label>
                                        <input type="text" class="form-control" id="registroLote" name="numero_lote"
                                               maxlength="50" placeholder="Captura el lote" autocomplete="off" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label" for="registroSerial">No. Serial <span class="required">*</span></label>
                                        <input type="text" class="form-control" id="registroSerial" name="numero_serial"
                                               maxlength="80" placeholder="Captura el serial" autocomplete="off" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label" for="registroCantidad">Cantidad <span class="required">*</span></label>
                                        <input type="number" class="form-control" id="registroCantidad" name="cantidad"
                                               min="1" step="1" placeholder="Ej. 1" required>
                                    </div>
                                </div>
                            </fieldset>

                            <!-- Procesos relacionados con la detección y el origen. -->
                            <fieldset class="anomaly-field-group">
                                <legend><i class="bi bi-diagram-3" aria-hidden="true"></i> Procesos involucrados</legend>
                                <div class="row g-3">
                                    <div class="col-lg-6">
                                        <label class="form-label" for="registroProcesoDetectado">Proceso / estación donde se detectó <span class="required">*</span></label>
                                        <select class="form-select" id="registroProcesoDetectado"
                                                name="id_estacion_deteccion" disabled required>
                                            <option value="" selected>Primero selecciona la línea</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-6">
                                        <label class="form-label" for="registroProcesoGenerado">Proceso / estación donde se generó <span class="required">*</span></label>
                                        <select class="form-select" id="registroProcesoGenerado"
                                                name="id_estacion_generacion" disabled required>
                                            <option value="" selected>Primero selecciona la línea</option>
                                        </select>
                                    </div>
                                </div>
                            </fieldset>

                            <!-- Identificación de la persona que realiza el registro. -->
                            <fieldset class="anomaly-field-group">
                                <legend><i class="bi bi-person-check" aria-hidden="true"></i> Persona que registra la anormalidad</legend>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label" for="registroNominaRegistra">Nómina de quien registra <span class="required">*</span></label>
                                        <input type="text" class="form-control" id="registroNominaRegistra" name="nomina_registra"
                                               autocomplete="off" readonly aria-readonly="true" required>
                                    </div>

                                    <div class="col-md-8">
                                        <label class="form-label" for="registroNombreRegistra">Nombre de quien registra <span class="required">*</span></label>
                                        <input type="text" class="form-control" id="registroNombreRegistra" name="nombre_registra"
                                               maxlength="120" placeholder="Se obtiene de la sesión"
                                               autocomplete="off" readonly aria-readonly="true" required>
                                    </div>
                                </div>
                            </fieldset>

                        </section>
                    </div>

                    <div class="modal-footer">
                        <span class="anomaly-form-footer-note">
                            <i class="bi bi-shield-check" aria-hidden="true"></i>
                            Paso inicial del flujo
                        </span>
                        <div class="anomaly-form-footer-actions">
                            <button type="button" class="btn btn-outline-custom btn-custom" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="button" class="btn btn-primary-custom btn-custom" id="btnGuardarRegistroInicial">
                                <i class="bi bi-check2-circle" aria-hidden="true"></i>
                                Guardar registro inicial
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de consulta del flujo. Se abre desde el botón Info de cada fila. -->
    <div class="modal fade" id="modalDetalleAnormalidad" tabindex="-1"
         aria-labelledby="modalDetalleAnormalidadLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable anomaly-detail-dialog">
            <div class="modal-content anomaly-modal anomaly-detail-modal">
                <div class="modal-header">
                    <div class="anomaly-modal-title-group">
                        <span class="anomaly-modal-icon anomaly-detail-modal-icon">
                            <i class="bi bi-info-lg" aria-hidden="true"></i>
                        </span>
                        <div>
                            <span class="anomaly-modal-eyebrow">Flujo de seguimiento</span>
                            <h2 class="modal-title me-2" id="modalDetalleAnormalidadLabel">Detalle de la anormalidad</h2>
                        </div>
                    </div>
                    <div class="anomaly-detail-header-actions">
                        <span class="anomaly-detail-id" id="detalleRegistroId">ID 1048</span>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar detalle"></button>
                    </div>
                </div>

                <form id="formDetalleAnormalidad" class="anomaly-detail-form">
                    <!-- Navegación superior entre las etapas del flujo. -->
                    <nav class="anomaly-flow-pagination" aria-label="Etapas de seguimiento de la anormalidad">
                        <div class="nav anomaly-flow-tabs" id="anomalyFlowTabs" role="tablist">
                            <button class="anomaly-flow-tab active" id="tabAperturaButton"
                                    data-bs-toggle="tab" data-bs-target="#tabApertura" type="button"
                                    role="tab" aria-controls="tabApertura" aria-selected="true">
                                <span class="anomaly-flow-number">1</span>
                                <span class="anomaly-flow-copy">
                                    <strong>Detección</strong>
                                    <small>Registro inicial</small>
                                </span>
                                <i class="bi bi-check-circle-fill anomaly-flow-state" aria-hidden="true"></i>
                            </button>

                            <button class="anomaly-flow-tab" id="tabSeguimientoButton"
                                    data-bs-toggle="tab" data-bs-target="#tabSeguimiento" type="button"
                                    role="tab" aria-controls="tabSeguimiento" aria-selected="false">
                                <span class="anomaly-flow-number">2</span>
                                <span class="anomaly-flow-copy">
                                    <strong>Análisis</strong>
                                    <small>Contramedidas</small>
                                </span>
                                <i class="bi bi-chevron-right anomaly-flow-state" aria-hidden="true"></i>
                            </button>

                            <button class="anomaly-flow-tab" id="tabCierreCalidadButton"
                                    data-bs-toggle="tab" data-bs-target="#tabCierreCalidad" type="button"
                                    role="tab" aria-controls="tabCierreCalidad" aria-selected="false">
                                <span class="anomaly-flow-number">3</span>
                                <span class="anomaly-flow-copy">
                                    <strong>Verificación Calidad</strong>
                                    <small>Liberación</small>
                                </span>
                                <i class="bi bi-chevron-right anomaly-flow-state" aria-hidden="true"></i>
                            </button>

                            <button class="anomaly-flow-tab" id="tabCierreButton"
                                    data-bs-toggle="tab" data-bs-target="#tabCierre" type="button"
                                    role="tab" aria-controls="tabCierre" aria-selected="false">
                                <span class="anomaly-flow-number">4</span>
                                <span class="anomaly-flow-copy">
                                    <strong>Cierre</strong>
                                    <small>Autorización final</small>
                                </span>
                                <i class="bi bi-flag anomaly-flow-state" aria-hidden="true"></i>
                            </button>
                        </div>
                    </nav>

                    <div class="modal-body anomaly-detail-body">
                        <div class="tab-content" id="anomalyFlowTabContent">
                            <!-- Pestaña 1: apertura y primera revisión de la anormalidad. -->
                            <section class="tab-pane fade show active" id="tabApertura" role="tabpanel"
                                     aria-labelledby="tabAperturaButton" tabindex="0">
                                <!-- El mismo resumen de detección controla el detalle y comienza contraído. -->
                                <button class="anomaly-detail-summary anomaly-opening-summary-toggle collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#detalleAperturaColapsable"
                                        aria-expanded="false" aria-controls="detalleAperturaColapsable">
                                    <span class="anomaly-detail-summary-icon">
                                        <i class="bi bi-clipboard-check" aria-hidden="true"></i>
                                    </span>
                                    <span>
                                        <strong>Detección de la anormalidad</strong>
                                        <span>Datos capturados al iniciar el registro.</span>
                                    </span>
                                    <span class="anomaly-detail-recurrence">
                                        <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
                                        <span>
                                            <small>Reincidencias</small>
                                            <strong id="detalleReincidencias">1</strong>
                                        </span>
                                    </span>
                                    <span class="anomaly-detail-status">
                                        <i class="bi bi-check2" aria-hidden="true"></i>
                                        Registrado
                                    </span>
                                    <i class="bi bi-chevron-down anomaly-opening-summary-chevron" aria-hidden="true"></i>
                                </button>

                                <!-- Mantiene compacta solamente la información del registro inicial. -->
                                <div class="collapse anomaly-opening-compact" id="detalleAperturaColapsable">

                                    <section class="anomaly-detail-section" aria-labelledby="detailGeneralTitle">
                                        <header class="anomaly-detail-section-title">
                                            <span><i class="bi bi-card-checklist" aria-hidden="true"></i></span>
                                            <div>
                                                <h3 id="detailGeneralTitle">Datos generales</h3>
                                                <p>Identificación y momento de la detección.</p>
                                            </div>
                                        </header>

                                        <div class="anomaly-detail-grid anomaly-detail-grid-three">
                                            <div class="anomaly-detail-field">
                                                <span class="anomaly-detail-label">No. seguimiento</span>
                                                <strong class="anomaly-detail-value" id="detalleNumeroSeguimiento">ANO-CRV23-008-2026</strong>
                                            </div>
                                            <div class="anomaly-detail-field">
                                                <span class="anomaly-detail-label">Fecha y hora de detección</span>
                                                <strong class="anomaly-detail-value" id="detalleFechaDeteccion">21 ago 2026 · 06:42</strong>
                                            </div>
                                            <div class="anomaly-detail-field">
                                                <span class="anomaly-detail-label">Turno</span>
                                                <strong class="anomaly-detail-value" id="detalleTurno">Turno 1</strong>
                                            </div>
                                        </div>
                                    </section>

                                    <section class="anomaly-detail-section anomaly-condition-section" aria-labelledby="detailConditionTitle">
                                        <header class="anomaly-detail-section-title">
                                            <span><i class="bi bi-exclamation-diamond" aria-hidden="true"></i></span>
                                            <div>
                                                <h3 id="detailConditionTitle">Condición anormal registrada</h3>
                                                <p>Ubicación y descripción de la situación detectada.</p>
                                            </div>
                                        </header>

                                        <!-- Distribución compacta para mantener relacionados ubicación, descripción y trazabilidad. -->
                                        <div class="anomaly-detail-grid anomaly-condition-grid">
                                            <div class="anomaly-detail-field anomaly-condition-area">
                                                <span class="anomaly-detail-label">Área</span>
                                                <strong class="anomaly-detail-value" id="detalleArea">SENSOR</strong>
                                            </div>
                                            <div class="anomaly-detail-field anomaly-condition-line">
                                                <span class="anomaly-detail-label">Línea</span>
                                                <strong class="anomaly-detail-value" id="detalleLinea">CRV23</strong>
                                            </div>
                                            <div class="anomaly-detail-field anomaly-condition-model">
                                                <span class="anomaly-detail-label">Modelo</span>
                                                <strong class="anomaly-detail-value" id="detalleModelo">--</strong>
                                            </div>
                                            <div class="anomaly-detail-field anomaly-detail-note anomaly-condition-details">
                                                <span class="anomaly-detail-label">Detalles de la anormalidad</span>
                                                <p class="anomaly-detail-value" id="detalleDescripcion">Se detectó una condición fuera del estándar durante la operación de la estación.</p>
                                            </div>
                                            <div class="anomaly-detail-field anomaly-condition-lot">
                                                <span class="anomaly-detail-label">No. Lote</span>
                                                <strong class="anomaly-detail-value" id="detalleLote">LT-1048</strong>
                                            </div>
                                            <div class="anomaly-detail-field anomaly-condition-serial">
                                                <span class="anomaly-detail-label">No. Serial</span>
                                                <strong class="anomaly-detail-value" id="detalleSerial">SN-1048-01</strong>
                                            </div>
                                            <div class="anomaly-detail-field anomaly-condition-quantity">
                                                <span class="anomaly-detail-label">Cantidad</span>
                                                <strong class="anomaly-detail-value" id="detalleCantidad">1 pieza</strong>
                                            </div>
                                        </div>
                                    </section>

                                    <!-- Procesos y persona que reportó se consultan juntos en una sola fila. -->
                                    <div class="anomaly-opening-related-grid">
                                        <section class="anomaly-detail-section" aria-labelledby="detailProcessTitle">
                                            <header class="anomaly-detail-section-title">
                                                <span><i class="bi bi-diagram-3" aria-hidden="true"></i></span>
                                                <div>
                                                    <h3 id="detailProcessTitle">Procesos involucrados</h3>
                                                    <p>Punto de detección y posible origen de la condición.</p>
                                                </div>
                                            </header>

                                            <div class="anomaly-detail-grid anomaly-detail-grid-two">
                                                <div class="anomaly-detail-field">
                                                    <span class="anomaly-detail-label">Proceso / estación donde se detectó</span>
                                                    <strong class="anomaly-detail-value" id="detalleProcesoDetectado">Ensamble final · Estación 24</strong>
                                                </div>
                                                <div class="anomaly-detail-field">
                                                    <span class="anomaly-detail-label">Proceso / estación donde se generó</span>
                                                    <strong class="anomaly-detail-value" id="detalleProcesoGenerado">Proceso anterior de CRV23</strong>
                                                </div>
                                            </div>
                                        </section>

                                        <section class="anomaly-detail-section" aria-labelledby="detailDetectorTitle">
                                            <header class="anomaly-detail-section-title">
                                                <span><i class="bi bi-person-exclamation" aria-hidden="true"></i></span>
                                                <div>
                                                    <h3 id="detailDetectorTitle">Personal que registró</h3>
                                                    <p>Persona que realizó el registro inicial.</p>
                                                </div>
                                            </header>

                                            <div class="anomaly-person-card">
                                                <span class="anomaly-person-avatar"><i class="bi bi-person" aria-hidden="true"></i></span>
                                                <span>
                                                    <strong id="detalleNombreDetecta">María Fernanda Ruiz</strong>
                                                    <small id="detalleNominaDetecta">Nómina 107530</small>
                                                </span>
                                            </div>
                                        </section>
                                    </div>
                                </div>

                                <!-- Separador entre la consulta de apertura y la captura de la primera revisión. -->
                                <div class="anomaly-opening-review-divider" role="separator"
                                     aria-label="Inicio de la primera revisión de Calidad">
                                    <span>Primera revisión</span>
                                </div>

                                <!-- Clasificación inicial, criticidad y nivel de respuesta. -->
                                <section class="anomaly-detail-section" aria-labelledby="revisionClassificationTitle">
                                    <header class="anomaly-detail-section-title">
                                        <span><i class="bi bi-tags" aria-hidden="true"></i></span>
                                        <div>
                                            <h3 id="revisionClassificationTitle">Clasificación y prioridad</h3>
                                            <p>Selecciona el tipo de anormalidad y el nivel de atención requerido.</p>
                                        </div>
                                    </header>

                                    <div class="row g-3 align-items-stretch">
                                        <!-- Cada dato ocupa una fila, con un ancho limitado para facilitar la lectura. -->
                                        <div class="col-12">
                                            <div class="anomaly-review-field anomaly-review-field-wide">
                                                <label class="form-label" for="revisionTipoAnormalidad">Tipo o clasificación de la anormalidad <span class="required">*</span></label>
                                                <select class="form-select" id="revisionTipoAnormalidad"
                                                        name="revision_tipo_anormalidad" required>
                                                    <option value="" selected disabled>Selecciona una clasificación</option>
                                                    <option value="holder_desgastado">Holder desgastado</option>
                                                    <option value="falta_escala">Falta de escala</option>
                                                    <option value="riesgo_calidad">Riesgo de calidad</option>
                                                    <option value="material_mezclado">Material mezclado</option>
                                                    <option value="longitud_cable_equivocada">Longitud de cable equivocada</option>
                                                    <option value="falta_holder">Falta de holder</option>
                                                    <option value="falta_epp">Falta de EPP</option>
                                                    <option value="control_defectos">Control de defectos</option>
                                                    <option value="certificacion_vencida">Certificación vencida</option>
                                                    <option value="falta_certificacion">Falta de certificación</option>
                                                    <option value="falta_etiquetero">Falta de etiquetero</option>
                                                    <option value="identificacion_tna">Identificación TNA</option>
                                                    <option value="falta_matriz">Falta de matriz</option>
                                                    <option value="falta_fecha_reemplazo">Falta fecha de reemplazo</option>
                                                    <option value="falta_informacion_estandar">Falta de información en estándar</option>
                                                    <option value="estacion_critica">Estación crítica</option>
                                                    <option value="error_estandar">Error en estándar</option>
                                                    <option value="problemas_cpg">Problemas con CPG</option>
                                                    <option value="control_puntos_cambio">Control de puntos de cambio</option>
                                                    <option value="otro">Otro</option>
                                                </select>
                                                <small class="anomaly-form-hint">Catálogo visual sujeto a ampliación.</small>
                                            </div>
                                        </div>

                                        <!-- Solo se muestra cuando el tipo seleccionado tiene la CLAVE 20 (Otro). -->
                                        <div class="col-12" id="revisionOtroTipoAnormalidadContenedor" hidden>
                                            <div class="anomaly-review-field anomaly-review-field-wide">
                                                <label class="form-label" for="revisionOtroTipoAnormalidad">
                                                    Comentarios / notas <span class="required">*</span>
                                                </label>
                                                <textarea class="form-control" id="revisionOtroTipoAnormalidad"
                                                          name="otros_tipo_anormalidad" rows="4" maxlength="500"
                                                          placeholder="Describe el tipo de anormalidad no incluido en el catálogo."></textarea>
                                                <small class="anomaly-form-hint">Máximo 500 caracteres.</small>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="anomaly-review-field anomaly-review-field-compact">
                                                <fieldset class="anomaly-result-box">
                                                    <legend class="form-label">Criticidad <span class="required">*</span></legend>
                                                    <div class="anomaly-inline-result-options" id="revisionCriticidadOpciones">
                                                        <input type="radio" class="btn-check" name="revision_criticidad"
                                                               id="revisionCritico" value="critico" autocomplete="off" required>
                                                        <label class="btn btn-outline-danger" for="revisionCritico">Crítico</label>

                                                        <input type="radio" class="btn-check" name="revision_criticidad"
                                                               id="revisionNoCritico" value="no_critico" autocomplete="off" required>
                                                        <label class="btn btn-outline-secondary" for="revisionNoCritico">No crítico</label>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="anomaly-review-field anomaly-review-field-compact">
                                                <fieldset class="anomaly-result-box">
                                                    <legend class="form-label">Nivel de respuesta <span class="required">*</span></legend>
                                                    <div class="anomaly-inline-result-options anomaly-response-level-options"
                                                         id="revisionNivelRespuestaOpciones">
                                                        <input type="radio" class="btn-check" name="revision_nivel_respuesta"
                                                               id="revisionNivel1" value="1" autocomplete="off" required>
                                                        <label class="btn btn-outline-primary" for="revisionNivel1">1</label>

                                                        <input type="radio" class="btn-check" name="revision_nivel_respuesta"
                                                               id="revisionNivel2" value="2" autocomplete="off" required>
                                                        <label class="btn btn-outline-primary" for="revisionNivel2">2</label>

                                                        <input type="radio" class="btn-check" name="revision_nivel_respuesta"
                                                               id="revisionNivel3" value="3" autocomplete="off" required>
                                                        <label class="btn btn-outline-primary" for="revisionNivel3">3</label>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>
                                    </div>


                                    <header class="anomaly-detail-section-title mt-4">
                                    </header>
                                    
                                    <!--CLASIFICACION DE 5M A LA QUE PERTENECE LA ANORMALIDAD -->
                                    <header class="anomaly-detail-section-title">
                                        <span><i class="bi bi-diagram-3" aria-hidden="true"></i></span>
                                        <div>
                                            <h3 id="revisionFourMTitle">Clasificación 5M</h3>
                                            <p>Marca uno o varios elementos relacionados con la anormalidad.</p>
                                        </div>
                                    </header>

                                    <div class="anomaly-disposition-options" id="revisionClasificacion5mOpciones">
                                        <label class="anomaly-disposition-option" for="revision4mMetodo">
                                            <input class="form-check-input" type="checkbox" value="metodo"
                                                   id="revision4mMetodo" name="revision_4m[]">
                                            <span>
                                                <strong>Método</strong>
                                                <small>Procedimiento, instrucción o estándar de trabajo.</small>
                                            </span>
                                        </label>

                                        <label class="anomaly-disposition-option" for="revision4mMaquinaria">
                                            <input class="form-check-input" type="checkbox" value="maquinaria"
                                                   id="revision4mMaquinaria" name="revision_4m[]">
                                            <span>
                                                <strong>Maquinaria</strong>
                                                <small>Equipo, dispositivo o herramienta utilizada.</small>
                                            </span>
                                        </label>

                                        <label class="anomaly-disposition-option" for="revision4mMateriaPrima">
                                            <input class="form-check-input" type="checkbox" value="materia_prima"
                                                   id="revision4mMateriaPrima" name="revision_4m[]">
                                            <span>
                                                <strong>Material</strong>
                                                <small>Material, pieza o componente involucrado.</small>
                                            </span>
                                        </label>

                                        <label class="anomaly-disposition-option" for="revision4mManoObra">
                                            <input class="form-check-input" type="checkbox" value="mano_obra"
                                                   id="revision4mManoObra" name="revision_4m[]">
                                            <span>
                                                <strong>Mano de obra</strong>
                                                <small>Ejecución, habilidad o intervención del personal.</small>
                                            </span>
                                        </label>

                                        <label class="anomaly-disposition-option" for="revision4mMedioAmbiente">
                                            <input class="form-check-input" type="checkbox" value="medio_ambiente"
                                                   id="revision4mMedioAmbiente" name="revision_4m[]">
                                            <span>
                                                <strong>Medio ambiente</strong>
                                                <small>Condiciones físicas que afectan donde se realiza el proceso.</small>
                                            </span>
                                        </label>

                                        <label class="anomaly-disposition-option" for="revision4mOtro">
                                            <input class="form-check-input" type="checkbox" value="otro"
                                                   id="revision4mOtro" name="revision_4m[]">
                                            <span>
                                                <strong>Otros</strong>
                                                <small>Condiciones no contempladas en las clasificaciones anteriores</small>
                                            </span>
                                        </label>
                                    </div>

                                    <div class="anomaly-quality-actions">
                                        <span>
                                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                                            Verifica la clasificación antes de registrar la primera revisión.
                                        </span>
                                        <button type="button" class="btn btn-primary-custom btn-custom"
                                                id="btnRegistrarPrimeraRevision">
                                            <i class="bi bi-check2-circle" aria-hidden="true"></i>
                                            Registrar primera revisión
                                        </button>
                                    </div>

                                </section>

                                <!-- Departamentos que participarán en el seguimiento. -->
                                <section class="anomaly-detail-section anomaly-quality-conclusion" aria-labelledby="revisionFactorsTitle">
                                    <header class="anomaly-detail-section-title">
                                        <span><i class="bi bi-people" aria-hidden="true"></i></span>
                                        <div>
                                            <h3 id="revisionFactorsTitle"> Área/Departamento que atiende</h3>
                                            <p>Consulta las áreas asignadas y el personal responsable de su atención.</p>
                                        </div>
                                    </header>

                                    <!-- Los factores se crean desde los registros relacionados con la anormalidad. -->
                                    <div class="anomaly-serial-list" id="detalleFactoresAtencion" aria-live="polite">
                                        <div class="anomaly-serial-entry">
                                            <span class="anomaly-form-hint">Cargando áreas responsables...</span>
                                        </div>
                                    </div>
                                </section>

                            </section>

                            <!-- Pestaña 2: captura del análisis, seguimiento y contramedidas. -->
                            <section class="tab-pane fade" id="tabSeguimiento" role="tabpanel"
                                     aria-labelledby="tabSeguimientoButton" tabindex="0">
                                <div class="anomaly-detail-summary anomaly-quality-summary">
                                    <span class="anomaly-detail-summary-icon anomaly-quality-summary-icon">
                                        <i class="bi bi-patch-check" aria-hidden="true"></i>
                                    </span>
                                    <span>
                                        <small>Etapa 2 de 4</small>
                                        <strong>Ingeniería / Manufactura / Calidad</strong>
                                        <span>Investigación y seguimiento de la condición anormal.</span>
                                    </span>
                                    <span class="anomaly-quality-status">
                                        <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                        En captura
                                    </span>
                                </div>

                                <section class="anomaly-detail-section" aria-labelledby="qualityAnalysisTitle">
                                    <header class="anomaly-detail-section-title">
                                        <span><i class="bi bi-search" aria-hidden="true"></i></span>
                                        <div>
                                            <h3 id="qualityAnalysisTitle">Análisis de la anormalidad</h3>
                                            <p>Registra el resultado de la investigación realizada por cada especialidad.</p>
                                        </div>
                                    </header>

                                    <div class="anomaly-quality-intro">
                                        <span class="anomaly-quality-intro-icon">
                                            <i class="bi bi-clipboard-data" aria-hidden="true"></i>
                                        </span>
                                        <span>
                                            <strong>Resultado de la investigación</strong>
                                            <small>Describe la revisión y selecciona el resultado correspondiente.</small>
                                        </span>
                                    </div>

                                    <!-- Revisión del equipo realizada por Ingeniería de Proceso. -->
                                    <fieldset class="anomaly-field-group anomaly-investigation-item">
                                        <legend>
                                            <span class="anomaly-investigation-code">PE</span>
                                            Método de revisión del equipo
                                        </legend>
                                        <div class="row g-3 align-items-stretch">
                                            <div class="col-lg-8">
                                                <label class="form-label" for="calidadRevisionEquipo">Revisión realizada <span class="required">*</span></label>
                                                <textarea class="form-control" id="calidadRevisionEquipo" name="calidad_revision_equipo"
                                                          rows="3" maxlength="500"
                                                          placeholder="Describe el método utilizado para revisar el equipo."
                                                          required></textarea>
                                                <small class="anomaly-form-hint">Máximo 500 caracteres.</small>
                                            </div>
                                            <div class="col-lg-4">
                                                <fieldset class="anomaly-result-box">
                                                    <legend class="form-label">Resultado <span class="required">*</span></legend>
                                                    <div class="anomaly-result-options">
                                                        <input type="radio" class="btn-check" name="resultado_revision_equipo"
                                                               id="revisionEquipoOk" value="OK" autocomplete="off" required>
                                                        <label class="btn btn-outline-success flex-fill" for="revisionEquipoOk">
                                                            <i class="bi bi-check-circle" aria-hidden="true"></i> OK
                                                        </label>

                                                        <input type="radio" class="btn-check" name="resultado_revision_equipo"
                                                               id="revisionEquipoNg" value="NG" autocomplete="off" required>
                                                        <label class="btn btn-outline-danger flex-fill" for="revisionEquipoNg">
                                                            <i class="bi bi-x-circle" aria-hidden="true"></i> NG
                                                        </label>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>
                                        <div class="anomaly-quality-actions mt-3">
                                            <span>
                                                <i class="bi bi-info-circle" aria-hidden="true"></i>
                                                Guarda únicamente la revisión realizada por PE.
                                            </span>
                                            <button type="button" class="btn btn-primary-custom btn-custom"
                                                    id="btnRegistrarRevisionEquipo">
                                                <i class="bi bi-check2-circle" aria-hidden="true"></i>
                                                Registrar revisión del equipo
                                            </button>
                                        </div>
                                    </fieldset>

                                    <!-- Análisis del proceso de operación realizado por Manufactura. -->
                                    <fieldset class="anomaly-field-group anomaly-investigation-item">
                                        <legend>
                                            <span class="anomaly-investigation-code anomaly-investigation-code-mf">MF</span>
                                            Análisis de operación
                                        </legend>
                                        <div class="row g-3 align-items-stretch">
                                            <div class="col-lg-8">
                                                <label class="form-label" for="calidadAnalisisOperacion">Análisis realizado <span class="required">*</span></label>
                                                <textarea class="form-control" id="calidadAnalisisOperacion" name="calidad_analisis_operacion"
                                                          rows="3" maxlength="500"
                                                          placeholder="Describe el análisis efectuado sobre la operación."
                                                          required></textarea>
                                                <small class="anomaly-form-hint">Máximo 500 caracteres.</small>
                                            </div>
                                            <div class="col-lg-4">
                                                <fieldset class="anomaly-result-box">
                                                    <legend class="form-label">Resultado <span class="required">*</span></legend>
                                                    <div class="anomaly-result-options">
                                                        <input type="radio" class="btn-check" name="resultado_analisis_operacion"
                                                               id="analisisOperacionOk" value="OK" autocomplete="off" required>
                                                        <label class="btn btn-outline-success flex-fill" for="analisisOperacionOk">
                                                            <i class="bi bi-check-circle" aria-hidden="true"></i> OK
                                                        </label>

                                                        <input type="radio" class="btn-check" name="resultado_analisis_operacion"
                                                               id="analisisOperacionNg" value="NG" autocomplete="off" required>
                                                        <label class="btn btn-outline-danger flex-fill" for="analisisOperacionNg">
                                                            <i class="bi bi-x-circle" aria-hidden="true"></i> NG
                                                        </label>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>
                                        <div class="anomaly-quality-actions mt-3">
                                            <span>
                                                <i class="bi bi-info-circle" aria-hidden="true"></i>
                                                Guarda únicamente el análisis realizado por Manufactura.
                                            </span>
                                            <button type="button" class="btn btn-primary-custom btn-custom"
                                                    id="btnRegistrarAnalisisOperacion">
                                                <i class="bi bi-check2-circle" aria-hidden="true"></i>
                                                Registrar análisis de operación
                                            </button>
                                        </div>
                                    </fieldset>

                                    <!-- Ambas confirmaciones de QA se registrarán como una sola sección. -->
                                    <section aria-labelledby="qualityQaConfirmationsTitle">
                                        <header class="anomaly-detail-section-title mt-4">
                                            <span><i class="bi bi-patch-check" aria-hidden="true"></i></span>
                                            <div>
                                                <h3 id="qualityQaConfirmationsTitle">Confirmaciones de Calidad (QA)</h3>
                                                <p>Completa ambas revisiones antes de registrar la confirmación de Calidad.</p>
                                            </div>
                                        </header>

                                    <!-- Confirmación de piezas o componentes anormales por Calidad. -->
                                    <fieldset class="anomaly-field-group anomaly-investigation-item">
                                        <legend>
                                            <span class="anomaly-investigation-code anomaly-investigation-code-qa">QA</span>
                                            Confirmación de piezas y/o componentes anormales
                                        </legend>
                                        <div class="row g-3 align-items-stretch">
                                            <div class="col-12">
                                                <label class="form-label" for="calidadConfirmacionPiezas">Resultado de la confirmación <span class="required">*</span></label>
                                                <textarea class="form-control" id="calidadConfirmacionPiezas" name="calidad_confirmacion_piezas"
                                                          rows="3" maxlength="500"
                                                          placeholder="Describe las piezas o componentes revisados y los hallazgos."
                                                          required></textarea>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="calidadCantidadPiezas">Cantidad <span class="required">*</span></label>
                                                <input type="number" class="form-control" id="calidadCantidadPiezas"
                                                       name="calidad_cantidad_piezas" min="0" step="1"
                                                       placeholder="Ej. 5" required>
                                            </div>
                                            <div class="col-md-4">
                                                <fieldset class="anomaly-result-box">
                                                    <legend class="form-label">Resultado <span class="required">*</span></legend>
                                                    <div class="anomaly-result-options">
                                                        <input type="radio" class="btn-check" name="resultado_confirmacion_piezas"
                                                               id="confirmacionPiezasOk" value="OK" autocomplete="off" required>
                                                        <label class="btn btn-outline-success flex-fill" for="confirmacionPiezasOk">
                                                            <i class="bi bi-check-circle" aria-hidden="true"></i> OK
                                                        </label>

                                                        <input type="radio" class="btn-check" name="resultado_confirmacion_piezas"
                                                               id="confirmacionPiezasNg" value="NG" autocomplete="off" required>
                                                        <label class="btn btn-outline-danger flex-fill" for="confirmacionPiezasNg">
                                                            <i class="bi bi-x-circle" aria-hidden="true"></i> NG
                                                        </label>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <!-- Confirmación del material que continúa dentro del proceso. -->
                                    <fieldset class="anomaly-field-group anomaly-investigation-item">
                                        <legend>
                                            <span class="anomaly-investigation-code anomaly-investigation-code-qa">QA</span>
                                            Confirmación de componentes en proceso
                                        </legend>
                                        <div class="row g-3 align-items-stretch">
                                            <div class="col-12">
                                                <label class="form-label" for="calidadConfirmacionProceso">Resultado de la confirmación <span class="required">*</span></label>
                                                <textarea class="form-control" id="calidadConfirmacionProceso" name="calidad_confirmacion_proceso"
                                                          rows="3" maxlength="500"
                                                          placeholder="Describe la revisión de los componentes que se encuentran en proceso."
                                                          required></textarea>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="calidadCantidadProceso">Cantidad <span class="required">*</span></label>
                                                <input type="number" class="form-control" id="calidadCantidadProceso"
                                                       name="calidad_cantidad_proceso" min="0" step="1"
                                                       placeholder="Ej. 12" required>
                                            </div>
                                            <div class="col-md-4">
                                                <fieldset class="anomaly-result-box">
                                                    <legend class="form-label">Resultado <span class="required">*</span></legend>
                                                    <div class="anomaly-result-options">
                                                        <input type="radio" class="btn-check" name="resultado_confirmacion_proceso"
                                                               id="confirmacionProcesoOk" value="OK" autocomplete="off" required>
                                                        <label class="btn btn-outline-success flex-fill" for="confirmacionProcesoOk">
                                                            <i class="bi bi-check-circle" aria-hidden="true"></i> OK
                                                        </label>

                                                        <input type="radio" class="btn-check" name="resultado_confirmacion_proceso"
                                                               id="confirmacionProcesoNg" value="NG" autocomplete="off" required>
                                                        <label class="btn btn-outline-danger flex-fill" for="confirmacionProcesoNg">
                                                            <i class="bi bi-x-circle" aria-hidden="true"></i> NG
                                                        </label>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>
                                    </fieldset>

                                        <div class="anomaly-quality-actions mt-3">
                                            <span>
                                                <i class="bi bi-info-circle" aria-hidden="true"></i>
                                                Guarda las dos confirmaciones realizadas por QA.
                                            </span>
                                            <button type="button" class="btn btn-primary-custom btn-custom"
                                                    id="btnRegistrarConfirmacionesQa">
                                                <i class="bi bi-check2-circle" aria-hidden="true"></i>
                                                Registrar revisiones de QA
                                            </button>
                                        </div>
                                    </section>
                                </section>

                                <!-- Conclusiones de la investigación y acción aplicada. -->
                                <section class="anomaly-detail-section anomaly-quality-conclusion" aria-labelledby="qualityConclusionTitle">
                                    <header class="anomaly-detail-section-title">
                                        <span><i class="bi bi-lightbulb" aria-hidden="true"></i></span>
                                        <div>
                                            <h3 id="qualityConclusionTitle">Causa y contramedida</h3>
                                            <p>Documenta la causa encontrada y la acción realizada.</p>
                                        </div>
                                    </header>

                                    <div class="row g-3">
                                        <div class="col-lg-6">
                                            <label class="form-label" for="calidadCausa">Causa <span class="required">*</span></label>
                                            <textarea class="form-control" id="calidadCausa" name="calidad_causa"
                                                      rows="5" maxlength="800"
                                                      placeholder="Describe la causa identificada durante la investigación."
                                                      required></textarea>
                                            <small class="anomaly-form-hint">Máximo 800 caracteres.</small>
                                        </div>

                                        <div class="col-lg-6">
                                            <label class="form-label" for="calidadContramedida">Contramedida que se realizó <span class="required">*</span></label>
                                            <textarea class="form-control" id="calidadContramedida" name="calidad_contramedida"
                                                      rows="5" maxlength="800"
                                                      placeholder="Describe la contramedida aplicada para contener o corregir la anormalidad."
                                                      required></textarea>
                                            <small class="anomaly-form-hint">Máximo 800 caracteres.</small>
                                        </div>
                                    </div>

                                    <!-- Acción independiente para registrar la causa y la contramedida. -->
                                    <div class="anomaly-quality-actions mt-3">
                                        <span>
                                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                                            Guarda conjuntamente la causa identificada y la contramedida aplicada.
                                        </span>
                                        <button type="button" class="btn btn-primary-custom btn-custom"
                                                id="btnRegistrarCausaContramedida">
                                            <i class="bi bi-check2-circle" aria-hidden="true"></i>
                                            Registrar causa y contramedida
                                        </button>
                                    </div>
                                </section>

                            </section>

                            <!-- Pestaña 3: cierre, disposición y liberación realizada por Calidad. -->
                            <section class="tab-pane fade" id="tabCierreCalidad" role="tabpanel"
                                     aria-labelledby="tabCierreCalidadButton" tabindex="0">
                                <div class="anomaly-detail-summary anomaly-quality-summary">
                                    <span class="anomaly-detail-summary-icon anomaly-quality-summary-icon">
                                        <i class="bi bi-shield-check" aria-hidden="true"></i>
                                    </span>
                                    <span>
                                        <small>Etapa 3 de 4</small>
                                        <strong>Calidad</strong>
                                        <span>Disposición del producto y aprobación del reinicio de operación.</span>
                                    </span>
                                    <span class="anomaly-quality-status">
                                        <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                        En captura
                                    </span>
                                </div>

                                <!-- Selección de la disposición aplicada al material afectado. -->
                                <section class="anomaly-detail-section" aria-labelledby="maintenanceDispositionTitle">
                                    <header class="anomaly-detail-section-title">
                                        <span><i class="bi bi-box-seam" aria-hidden="true"></i></span>
                                        <div>
                                            <h3 id="maintenanceDispositionTitle">Disposición producto NG / Anormal</h3>
                                            <p>Indica qué disposición se aplicó al producto o material afectado.</p>
                                        </div>
                                    </header>

                                    <fieldset class="anomaly-field-group">
                                        <legend><i class="bi bi-ui-radios" aria-hidden="true"></i> Disposición del producto / material afectado</legend>
                                        <div class="anomaly-disposition-options" id="cierreCalidadDisposicionOpciones">
                                            <label class="anomaly-disposition-option" for="disposicionEfuPnc">
                                                <input type="radio" class="form-check-input" id="disposicionEfuPnc"
                                                       name="disposicion_producto" value="efu_pnc" required>
                                                <span>
                                                    <strong>Colocar EFU y enviar a PNC</strong>
                                                    <small>Colocar EFU en la pieza y ubicarla en producto no conforme.</small>
                                                </span>
                                            </label>

                                            <label class="anomaly-disposition-option" for="disposicionReparacion">
                                                <input type="radio" class="form-check-input" id="disposicionReparacion"
                                                       name="disposicion_producto" value="chokotei_reparacion" required>
                                                <span>
                                                    <strong>Chokotei o reparación</strong>
                                                    <small>El producto requiere reparación antes de continuar el proceso.</small>
                                                </span>
                                            </label>
                                        </div>
                                    </fieldset>

                                    <!-- Acción independiente para registrar la disposición seleccionada. -->
                                    <div class="anomaly-quality-actions mt-3">
                                        <span>
                                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                                            Guarda la disposición aplicada al producto o material afectado.
                                        </span>
                                        <button type="button" class="btn btn-primary-custom btn-custom"
                                                id="btnRegistrarDisposicionProducto">
                                            <i class="bi bi-check2-circle" aria-hidden="true"></i>
                                            Registrar disposición
                                        </button>
                                    </div>
                                </section>

                                <!-- Verificación de tres piezas previas a la detección. -->
                                <section class="anomaly-detail-section anomaly-maintenance-section" aria-labelledby="materialBeforeTitle">
                                    <header class="anomaly-detail-section-title">
                                        <span><i class="bi bi-upc-scan" aria-hidden="true"></i></span>
                                        <div>
                                            <h3 id="materialBeforeTitle">Revisión de material antes de la anormalidad</h3>
                                            <p>Captura el serial de cada pieza revisada y selecciona su resultado.</p>
                                        </div>
                                    </header>

                                    <div class="anomaly-serial-list">
                                        <div class="anomaly-serial-entry">
                                            <div class="row g-3 align-items-stretch">
                                                <div class="col-md-7">
                                                    <label class="form-label" for="serialAntes1">Serial 1 <span class="required">*</span></label>
                                                    <input type="text" class="form-control" id="serialAntes1" name="serial_antes_1"
                                                           maxlength="80" placeholder="Captura el código del serial" autocomplete="off" required>
                                                </div>
                                                <div class="col-md-5">
                                                    <fieldset class="anomaly-result-box">
                                                        <legend class="form-label">Resultado <span class="required">*</span></legend>
                                                        <div class="anomaly-inline-result-options">
                                                            <input type="radio" class="btn-check" name="resultado_serial_antes_1"
                                                                   id="serialAntes1Ok" value="OK" autocomplete="off" required>
                                                            <label class="btn btn-outline-success" for="serialAntes1Ok"><i class="bi bi-check-circle"></i> OK</label>
                                                            <input type="radio" class="btn-check" name="resultado_serial_antes_1"
                                                                   id="serialAntes1Ng" value="NG" autocomplete="off" required>
                                                            <label class="btn btn-outline-danger" for="serialAntes1Ng"><i class="bi bi-x-circle"></i> NG</label>
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="anomaly-serial-entry">
                                            <div class="row g-3 align-items-stretch">
                                                <div class="col-md-7">
                                                    <label class="form-label" for="serialAntes2">Serial 2 <span class="required">*</span></label>
                                                    <input type="text" class="form-control" id="serialAntes2" name="serial_antes_2"
                                                           maxlength="80" placeholder="Captura el código del serial" autocomplete="off" required>
                                                </div>
                                                <div class="col-md-5">
                                                    <fieldset class="anomaly-result-box">
                                                        <legend class="form-label">Resultado <span class="required">*</span></legend>
                                                        <div class="anomaly-inline-result-options">
                                                            <input type="radio" class="btn-check" name="resultado_serial_antes_2"
                                                                   id="serialAntes2Ok" value="OK" autocomplete="off" required>
                                                            <label class="btn btn-outline-success" for="serialAntes2Ok"><i class="bi bi-check-circle"></i> OK</label>
                                                            <input type="radio" class="btn-check" name="resultado_serial_antes_2"
                                                                   id="serialAntes2Ng" value="NG" autocomplete="off" required>
                                                            <label class="btn btn-outline-danger" for="serialAntes2Ng"><i class="bi bi-x-circle"></i> NG</label>
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="anomaly-serial-entry">
                                            <div class="row g-3 align-items-stretch">
                                                <div class="col-md-7">
                                                    <label class="form-label" for="serialAntes3">Serial 3 <span class="required">*</span></label>
                                                    <input type="text" class="form-control" id="serialAntes3" name="serial_antes_3"
                                                           maxlength="80" placeholder="Captura el código del serial" autocomplete="off" required>
                                                </div>
                                                <div class="col-md-5">
                                                    <fieldset class="anomaly-result-box">
                                                        <legend class="form-label">Resultado <span class="required">*</span></legend>
                                                        <div class="anomaly-inline-result-options">
                                                            <input type="radio" class="btn-check" name="resultado_serial_antes_3"
                                                                   id="serialAntes3Ok" value="OK" autocomplete="off" required>
                                                            <label class="btn btn-outline-success" for="serialAntes3Ok"><i class="bi bi-check-circle"></i> OK</label>
                                                            <input type="radio" class="btn-check" name="resultado_serial_antes_3"
                                                                   id="serialAntes3Ng" value="NG" autocomplete="off" required>
                                                            <label class="btn btn-outline-danger" for="serialAntes3Ng"><i class="bi bi-x-circle"></i> NG</label>
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Acción independiente para guardar los tres materiales revisados. -->
                                    <div class="anomaly-quality-actions mt-3">
                                        <span>
                                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                                            Guarda conjuntamente los tres seriales revisados antes de la anormalidad.
                                        </span>
                                        <button type="button" class="btn btn-primary-custom btn-custom"
                                                id="btnRegistrarRevisionMaterialAntes">
                                            <i class="bi bi-check2-circle" aria-hidden="true"></i>
                                            Registrar revisión de material
                                        </button>
                                    </div>
                                </section>

                                <!-- Confirmación final antes de autorizar nuevamente la operación. -->
                                <section class="anomaly-detail-section anomaly-maintenance-section" aria-labelledby="operationRestartTitle">
                                    <header class="anomaly-detail-section-title">
                                        <span><i class="bi bi-arrow-clockwise" aria-hidden="true"></i></span>
                                        <div>
                                            <h3 id="operationRestartTitle">Reinicio de operación</h3>
                                            <p>Confirma que el producto, operación o máquina ya no presente la anormalidad.</p>
                                        </div>
                                    </header>

                                    <div class="anomaly-quality-intro">
                                        <span class="anomaly-quality-intro-icon">
                                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                                        </span>
                                        <span>
                                            <strong>Confirmación posterior a la disposición</strong>
                                            <small>Documenta la verificación realizada antes de reanudar la operación.</small>
                                        </span>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-lg-8">
                                            <label class="form-label" for="confirmacionReinicio">Confirmación de producto, operación o máquina <span class="required">*</span></label>
                                            <textarea class="form-control" id="confirmacionReinicio" name="confirmacion_reinicio"
                                                      rows="4" maxlength="700"
                                                      placeholder="Describe las revisiones realizadas y confirma que no existe anormalidad o defecto."
                                                      required></textarea>
                                        </div>
                                        <div class="col-lg-4">
                                            <label class="form-label" for="fechaConfirmacionReinicio">Fecha de confirmación <span class="required">*</span></label>
                                            <input type="date" class="form-control" id="fechaConfirmacionReinicio"
                                                   name="fecha_confirmacion_reinicio" readonly required>
                                        </div>
                                    </div>

                                    <fieldset class="anomaly-field-group anomaly-restart-review">
                                        <legend><i class="bi bi-list-check" aria-hidden="true"></i> Seriales revisados para el reinicio</legend>
                                        <div class="anomaly-serial-list">
                                            <div class="anomaly-serial-entry">
                                                <div class="row g-3 align-items-stretch">
                                                    <div class="col-md-7">
                                                        <label class="form-label" for="serialReinicio1">Serial 1 <span class="required">*</span></label>
                                                        <input type="text" class="form-control" id="serialReinicio1" name="serial_reinicio_1"
                                                               maxlength="80" placeholder="Captura el código del serial" autocomplete="off" required>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <fieldset class="anomaly-result-box">
                                                            <legend class="form-label">Resultado <span class="required">*</span></legend>
                                                            <div class="anomaly-inline-result-options">
                                                                <input type="radio" class="btn-check" name="resultado_serial_reinicio_1"
                                                                       id="serialReinicio1Ok" value="OK" autocomplete="off" required>
                                                                <label class="btn btn-outline-success" for="serialReinicio1Ok"><i class="bi bi-check-circle"></i> OK</label>
                                                                <input type="radio" class="btn-check" name="resultado_serial_reinicio_1"
                                                                       id="serialReinicio1Ng" value="NG" autocomplete="off" required>
                                                                <label class="btn btn-outline-danger" for="serialReinicio1Ng"><i class="bi bi-x-circle"></i> NG</label>
                                                            </div>
                                                        </fieldset>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="anomaly-serial-entry">
                                                <div class="row g-3 align-items-stretch">
                                                    <div class="col-md-7">
                                                        <label class="form-label" for="serialReinicio2">Serial 2 <span class="required">*</span></label>
                                                        <input type="text" class="form-control" id="serialReinicio2" name="serial_reinicio_2"
                                                               maxlength="80" placeholder="Captura el código del serial" autocomplete="off" required>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <fieldset class="anomaly-result-box">
                                                            <legend class="form-label">Resultado <span class="required">*</span></legend>
                                                            <div class="anomaly-inline-result-options">
                                                                <input type="radio" class="btn-check" name="resultado_serial_reinicio_2"
                                                                       id="serialReinicio2Ok" value="OK" autocomplete="off" required>
                                                                <label class="btn btn-outline-success" for="serialReinicio2Ok"><i class="bi bi-check-circle"></i> OK</label>
                                                                <input type="radio" class="btn-check" name="resultado_serial_reinicio_2"
                                                                       id="serialReinicio2Ng" value="NG" autocomplete="off" required>
                                                                <label class="btn btn-outline-danger" for="serialReinicio2Ng"><i class="bi bi-x-circle"></i> NG</label>
                                                            </div>
                                                        </fieldset>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="anomaly-serial-entry">
                                                <div class="row g-3 align-items-stretch">
                                                    <div class="col-md-7">
                                                        <label class="form-label" for="serialReinicio3">Serial 3 <span class="required">*</span></label>
                                                        <input type="text" class="form-control" id="serialReinicio3" name="serial_reinicio_3"
                                                               maxlength="80" placeholder="Captura el código del serial" autocomplete="off" required>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <fieldset class="anomaly-result-box">
                                                            <legend class="form-label">Resultado <span class="required">*</span></legend>
                                                            <div class="anomaly-inline-result-options">
                                                                <input type="radio" class="btn-check" name="resultado_serial_reinicio_3"
                                                                       id="serialReinicio3Ok" value="OK" autocomplete="off" required>
                                                                <label class="btn btn-outline-success" for="serialReinicio3Ok"><i class="bi bi-check-circle"></i> OK</label>
                                                                <input type="radio" class="btn-check" name="resultado_serial_reinicio_3"
                                                                       id="serialReinicio3Ng" value="NG" autocomplete="off" required>
                                                                <label class="btn btn-outline-danger" for="serialReinicio3Ng"><i class="bi bi-x-circle"></i> NG</label>
                                                            </div>
                                                        </fieldset>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <fieldset class="anomaly-field-group anomaly-approval-group anomaly-restart-approval">
                                        <legend><i class="bi bi-person-check" aria-hidden="true"></i> Aprobación de reinicio de operación</legend>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label" for="nominaInspectorCalidad">Nómina del inspector de Calidad <span class="required">*</span></label>
                                                <input type="text" class="form-control" id="nominaInspectorCalidad"
                                                       name="nomina_inspector_calidad" maxlength="20" inputmode="numeric"
                                                       placeholder="Ej. 107530" autocomplete="off" readonly required>
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label" for="nombreInspectorCalidad">Nombre del inspector de Calidad <span class="required">*</span></label>
                                                <input type="text" class="form-control" id="nombreInspectorCalidad"
                                                       name="nombre_inspector_calidad" maxlength="120"
                                                       placeholder="Nombre completo" autocomplete="off" readonly required>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <!-- Acción final de la sección para registrar el reinicio de operación. -->
                                    <div class="anomaly-quality-actions mt-3">
                                        <span>
                                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                                            Guarda la confirmación, los seriales y la aprobación del reinicio.
                                        </span>
                                        <button type="button" class="btn btn-primary-custom btn-custom"
                                                id="btnRegistrarReinicioOperacion">
                                            <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
                                            Registrar reinicio de operación
                                        </button>
                                    </div>
                                </section>
                            </section>

                            <!-- Pestaña 4: cierre y autorización final realizada por el gerente. -->
                            <section class="tab-pane fade" id="tabCierre" role="tabpanel"
                                     aria-labelledby="tabCierreButton" tabindex="0">
                                <div class="anomaly-detail-summary anomaly-close-summary">
                                    <span class="anomaly-detail-summary-icon anomaly-close-summary-icon">
                                        <i class="bi bi-flag-fill" aria-hidden="true"></i>
                                    </span>
                                    <span>
                                        <small>Etapa 4 de 4</small>
                                        <strong>Cierre de la anormalidad</strong>
                                        <span>Validación y autorización final del flujo.</span>
                                    </span>
                                    <span class="anomaly-close-status">
                                        <i class="bi bi-pen" aria-hidden="true"></i>
                                        Pendiente de firma
                                    </span>
                                </div>

                                <!-- Identificación de reincidencia y relación con otro seguimiento. -->
                                <section class="anomaly-detail-section" aria-labelledby="closeRecurrenceTitle">
                                    <header class="anomaly-detail-section-title">
                                        <span><i class="bi bi-arrow-repeat" aria-hidden="true"></i></span>
                                        <div>
                                            <h3 id="closeRecurrenceTitle">Anormalidad reincidente</h3>
                                            <p>Indica si la condición ya había ocurrido y registra su seguimiento relacionado.</p>
                                        </div>
                                    </header>

                                    <div class="row g-3 align-items-stretch">
                                        <div class="col-lg-5">
                                            <fieldset class="anomaly-result-box anomaly-close-option-box">
                                                <legend class="form-label">Es reincidente</legend>
                                                <div class="anomaly-inline-result-options">
                                                    <input type="radio" class="btn-check" name="cierre_reincidente"
                                                           id="cierreReincidenteSi" value="1" autocomplete="off" disabled>
                                                    <label class="btn btn-outline-primary" for="cierreReincidenteSi">Sí</label>

                                                    <input type="radio" class="btn-check" name="cierre_reincidente"
                                                           id="cierreReincidenteNo" value="0" autocomplete="off" checked disabled>
                                                    <label class="btn btn-outline-secondary" for="cierreReincidenteNo">No</label>
                                                </div>
                                            </fieldset>
                                        </div>

                                        <div class="col-lg-7">
                                            <label class="form-label" for="cierreNumeroSeguimiento">No. de seguimiento</label>
                                            <div class="anomaly-input-icon">
                                                <i class="bi bi-hash" aria-hidden="true"></i>
                                                <input type="text" class="form-control" id="cierreNumeroSeguimiento"
                                                       name="cierre_numero_seguimiento" maxlength="40"
                                                       placeholder="Sin número de seguimiento" autocomplete="off" readonly>
                                            </div>
                                            <small class="anomaly-form-hint">Información automática de solo lectura.</small>
                                        </div>
                                    </div>
                                </section>

                                <!-- Datos de Staff QA que prepara el cierre del reporte. -->
                                <section class="anomaly-detail-section anomaly-close-section" aria-labelledby="closeReportTitle">
                                    <header class="anomaly-detail-section-title">
                                        <span><i class="bi bi-clipboard2-check" aria-hidden="true"></i></span>
                                        <div>
                                            <h3 id="closeReportTitle">Cierre del reporte</h3>
                                            <p>Identifica al personal de Calidad que documenta el cierre.</p>
                                        </div>
                                    </header>

                                    <div class="row g-3 align-items-stretch">
                                        <!-- Identificación de Calidad a la izquierda. -->
                                        <div class="col-lg-6">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label" for="cierreNominaStaffQa">Nómina Staff QA <span class="required">*</span></label>
                                                    <input type="text" class="form-control" id="cierreNominaStaffQa"
                                                           name="cierre_nomina_staff_qa" maxlength="20" inputmode="numeric"
                                                           placeholder="Ej. 107530" autocomplete="off" readonly required>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label" for="cierreNombreStaffQa">Nombre Staff QA <span class="required">*</span></label>
                                                    <input type="text" class="form-control" id="cierreNombreStaffQa"
                                                           name="cierre_nombre_staff_qa" maxlength="120"
                                                           placeholder="Nombre completo" autocomplete="off" readonly required>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label" for="cierreFechaStaffQa">Fecha de cierre</label>
                                                    <input type="text" class="form-control" id="cierreFechaStaffQa"
                                                           placeholder="Pendiente de registro" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Firma de Calidad alineada en la columna derecha. -->
                                        <div class="col-lg-6">
                                            <div class="anomaly-signature-box">
                                                <div class="anomaly-signature-header">
                                                    <span class="form-label" id="firmaStaffQaLabel">Firma Staff QA <span class="required">*</span></span>
                                                    <button type="button" class="btn anomaly-signature-clear"
                                                            data-signature-clear="cierreFirmaStaffQaCanvas">
                                                        <i class="bi bi-eraser" aria-hidden="true"></i>
                                                        Limpiar firma
                                                    </button>
                                                </div>
                                                <div class="anomaly-signature-canvas-wrap">
                                                    <canvas class="anomaly-signature-canvas" id="cierreFirmaStaffQaCanvas"
                                                            data-signature-canvas data-signature-output="cierreFirmaStaffQa"
                                                            data-signature-format="image/jpeg"
                                                            aria-labelledby="firmaStaffQaLabel" tabindex="0">
                                                        Tu navegador no permite capturar la firma en canvas.
                                                    </canvas>
                                                    <span class="anomaly-signature-placeholder" aria-hidden="true">Firma dentro de este recuadro</span>
                                                </div>
                                                <input type="hidden" id="cierreFirmaStaffQa" name="cierre_firma_staff_qa">
                                            </div>
                                        </div>
                                    </div>

                                    <fieldset class="anomaly-field-group anomaly-close-responsible">
                                        <legend><i class="bi bi-person-badge" aria-hidden="true"></i> Responsable</legend>
                                        <div class="anomaly-responsible-options" id="cierreResponsableOpciones">
                                            <input type="radio" class="btn-check" name="cierre_responsable"
                                                   id="cierreResponsablePe" value="PE" autocomplete="off" required>
                                            <label class="btn anomaly-responsible-button" for="cierreResponsablePe">
                                                <strong>PE</strong><small>Ingeniería de Proceso</small>
                                            </label>

                                            <input type="radio" class="btn-check" name="cierre_responsable"
                                                   id="cierreResponsableQa" value="QA" autocomplete="off" required>
                                            <label class="btn anomaly-responsible-button" for="cierreResponsableQa">
                                                <strong>QA</strong><small>Calidad</small>
                                            </label>

                                            <input type="radio" class="btn-check" name="cierre_responsable"
                                                   id="cierreResponsableMft" value="MFT" autocomplete="off" required>
                                            <label class="btn anomaly-responsible-button" for="cierreResponsableMft">
                                                <strong>MFT</strong><small>Manufactura</small>
                                            </label>
                                        </div>
                                    </fieldset>

                                    <div class="anomaly-quality-actions mt-3">
                                        <span>
                                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                                            Guarda el cierre documentado por Staff QA y su firma.
                                        </span>
                                        <button type="button" class="btn btn-primary-custom btn-custom"
                                                id="btnRegistrarCierreReporteCalidad">
                                            <i class="bi bi-clipboard2-check" aria-hidden="true"></i>
                                            Registrar cierre de QA
                                        </button>
                                    </div>
                                </section>

                                <!-- Firma final del gerente que autoriza el cierre de la anormalidad. -->
                                <section class="anomaly-detail-section anomaly-close-section anomaly-manager-section" aria-labelledby="managerApprovalTitle">
                                    <header class="anomaly-detail-section-title">
                                        <span><i class="bi bi-person-vcard" aria-hidden="true"></i></span>
                                        <div>
                                            <h3 id="managerApprovalTitle">Autorización del gerente</h3>
                                            <p>El gerente valida la revisión completa y autoriza el cierre.</p>
                                        </div>
                                    </header>

                                    <div class="anomaly-manager-card">
                                        <div class="row g-3 align-items-stretch">
                                            <!-- Datos del gerente alineados en la columna izquierda. -->
                                            <div class="col-lg-6">
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <label class="form-label" for="cierreNominaGerente">Nómina del gerente <span class="required">*</span></label>
                                                        <input type="text" class="form-control" id="cierreNominaGerente"
                                                               name="cierre_nomina_gerente" maxlength="20" inputmode="numeric"
                                                               placeholder="Ej. 107530" autocomplete="off" readonly required>
                                                    </div>

                                                    <div class="col-12">
                                                        <label class="form-label" for="cierreNombreGerente">Nombre del gerente <span class="required">*</span></label>
                                                        <input type="text" class="form-control" id="cierreNombreGerente"
                                                               name="cierre_nombre_gerente" maxlength="120"
                                                               placeholder="Nombre completo del gerente" autocomplete="off" readonly required>
                                                        <small class="anomaly-manager-role">Manager</small>
                                                    </div>

                                                    <div class="col-12">
                                                        <label class="form-label" for="cierreFechaGerente">Fecha de autorización</label>
                                                        <input type="text" class="form-control" id="cierreFechaGerente"
                                                               placeholder="Pendiente de registro" readonly>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Firma del gerente alineada en la columna derecha. -->
                                            <div class="col-lg-6">
                                                <div class="anomaly-signature-box anomaly-manager-signature">
                                                    <div class="anomaly-signature-header">
                                                        <span class="form-label" id="firmaGerenteLabel">Firma <span class="required">*</span></span>
                                                        <button type="button" class="btn anomaly-signature-clear"
                                                                data-signature-clear="cierreFirmaGerenteCanvas">
                                                            <i class="bi bi-eraser" aria-hidden="true"></i>
                                                            Limpiar firma
                                                        </button>
                                                    </div>
                                                    <div class="anomaly-signature-canvas-wrap">
                                                        <canvas class="anomaly-signature-canvas" id="cierreFirmaGerenteCanvas"
                                                                data-signature-canvas data-signature-output="cierreFirmaGerente"
                                                                data-signature-format="image/jpeg"
                                                                aria-labelledby="firmaGerenteLabel" tabindex="0">
                                                            Tu navegador no permite capturar la firma en canvas.
                                                        </canvas>
                                                        <span class="anomaly-signature-placeholder" aria-hidden="true">Firma dentro de este recuadro</span>
                                                    </div>
                                                    <input type="hidden" id="cierreFirmaGerente" name="cierre_firma_gerente">
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </section>

                                <div class="anomaly-quality-actions anomaly-close-actions">
                                    <span>
                                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                                        La autorización requiere que el cierre de Staff QA ya esté firmado.
                                    </span>
                                    <button type="button" class="btn btn-primary-custom btn-custom"
                                            id="btnRegistrarAutorizacionGerente">
                                        <i class="bi bi-flag" aria-hidden="true"></i>
                                        Registrar autorización
                                    </button>
                                </div>
                            </section>
                        </div>
                    </div>

                    <div class="modal-footer anomaly-detail-footer">
                        <span>
                            <i class="bi bi-eye" aria-hidden="true"></i>
                            Consulta del seguimiento de la anormalidad
                        </span>
                        <button type="button" class="btn btn-outline-custom btn-custom" data-bs-dismiss="modal">
                            Cerrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Formulario para registrar la única cabecera de reincidencia de la anormalidad. -->
    <div class="modal fade" id="modalNuevaReincidencia" tabindex="-1"
         aria-labelledby="modalNuevaReincidenciaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable anomaly-modal-dialog anomaly-recurrence-dialog">
            <div class="modal-content anomaly-modal">
                <form id="formRegistroReincidencia" novalidate>
                    <div class="modal-header">
                        <div class="anomaly-modal-title-group">
                            <span class="anomaly-modal-icon">
                                <i class="bi bi-plus-circle" aria-hidden="true"></i>
                            </span>
                            <div>
                                <span class="anomaly-modal-eyebrow">Registro relacionado</span>
                                <h2 class="modal-title" id="modalNuevaReincidenciaLabel">Registrar reincidencia</h2>
                            </div>
                        </div>
                        <div class="anomaly-detail-header-actions">
                            <span class="anomaly-detail-id" id="reincidenciaRegistroId">ID --</span>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Cerrar formulario de reincidencia"></button>
                        </div>
                    </div>

                    <div class="modal-body">
                        <div class="anomaly-form-intro">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            <span>Los datos generales corresponden a la anormalidad seleccionada. Captura únicamente el detalle de la reincidencia.</span>
                        </div>

                        <section class="anomaly-form-section" aria-labelledby="recurrenceConditionTitle">
                            <div class="anomaly-form-section-header">
                                <span class="anomaly-section-number">1</span>
                                <div>
                                    <span class="anomaly-form-kicker">CONDICIÓN REGISTRADA</span>
                                    <h3 id="recurrenceConditionTitle">Datos de la reincidencia</h3>
                                    <p>Verifica la ubicación y describe la condición que volvió a presentarse.</p>
                                </div>
                            </div>

                            <input type="hidden" id="reincidenciaRegistroBase" name="registro_base_id">

                            <!-- Estos datos se toman visualmente de la fila seleccionada. -->
                            <div class="row g-3">
                                <div class="col-lg-4">
                                    <label class="form-label" for="reincidenciaNumeroSeguimiento">No. de seguimiento</label>
                                    <div class="anomaly-locked-field">
                                        <i class="bi bi-hash" aria-hidden="true"></i>
                                        <input type="text" class="form-control" id="reincidenciaNumeroSeguimiento"
                                               name="numero_seguimiento" inputmode="numeric" readonly>
                                    </div>
                                    <small class="anomaly-form-hint">Corresponde al número de seguimiento de la anormalidad.</small>
                                </div>

                                <div class="col-lg-5">
                                    <label class="form-label" for="reincidenciaFechaHora">Fecha y hora <span class="required">*</span></label>
                                    <div class="anomaly-input-icon">
                                        <i class="bi bi-calendar-event" aria-hidden="true"></i>
                                        <input type="datetime-local" class="form-control" id="reincidenciaFechaHora"
                                               name="reincidencia_fecha_hora" readonly required>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <label class="form-label" for="reincidenciaTurno">Turno <span class="required">*</span></label>
                                    <select class="form-select" id="reincidenciaTurno" name="reincidencia_turno"
                                            disabled aria-readonly="true" required>
                                        <option value="" selected disabled>Selecciona</option>
                                        <option value="1">Turno 1</option>
                                        <option value="2">Turno 2</option>
                                        <option value="3">Turno 3</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="reincidenciaArea">Área</label>
                                    <div class="anomaly-locked-field">
                                        <i class="bi bi-building" aria-hidden="true"></i>
                                        <input type="text" class="form-control" id="reincidenciaArea"
                                               name="reincidencia_area" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="reincidenciaLinea">Línea</label>
                                    <div class="anomaly-locked-field">
                                        <i class="bi bi-diagram-3" aria-hidden="true"></i>
                                        <input type="text" class="form-control" id="reincidenciaLinea"
                                               name="reincidencia_linea" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="reincidenciaTipo">Tipo de anormalidad <span class="required">*</span></label>
                                    <select class="form-select" id="reincidenciaTipo" disabled aria-readonly="true">
                                        <option value="" selected>Cargando tipo...</option>
                                    </select>
                                    <!-- Conserva la CLAVE porque los controles deshabilitados no se envían. -->
                                    <input type="hidden" id="reincidenciaTipoClave" name="reincidencia_tipo" value="">
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="reincidenciaAnormalidad">Detalle de la anormalidad <span class="required">*</span></label>
                                    <textarea class="form-control" id="reincidenciaAnormalidad"
                                              name="reincidencia_anormalidad" rows="4" maxlength="600"
                                              placeholder="Describe la anormalidad que volvió a presentarse."
                                              required></textarea>
                                    <small class="anomaly-form-hint">Máximo 600 caracteres.</small>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="modal-footer">
                        <span class="anomaly-form-footer-note">
                            <i class="bi bi-link-45deg" aria-hidden="true"></i>
                            Vinculada con la anormalidad seleccionada
                        </span>
                        <div class="anomaly-form-footer-actions">
                            <button type="button" class="btn btn-outline-custom btn-custom" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="button" class="btn btn-primary-custom btn-custom"
                                    id="btnGuardarReincidencia" disabled>
                                <i class="bi bi-plus-circle" aria-hidden="true"></i>
                                Registrar reincidencia
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Consulta visual de las reincidencias relacionadas con una anormalidad. -->
    <div class="modal fade" id="modalListadoReincidencias" tabindex="-1"
         aria-labelledby="modalListadoReincidenciasLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable anomaly-modal-dialog anomaly-recurrence-list-dialog">
            <div class="modal-content anomaly-modal">
                <div class="modal-header">
                    <div class="anomaly-modal-title-group">
                        <span class="anomaly-modal-icon">
                            <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
                        </span>
                        <div>
                            <span class="anomaly-modal-eyebrow" id="listadoReincidenciasEyebrow">Historial relacionado</span>
                            <h2 class="modal-title" id="modalListadoReincidenciasLabel">Reincidencias registradas</h2>
                        </div>
                    </div>
                    <div class="anomaly-detail-header-actions">
                        <span class="anomaly-detail-id" id="listadoReincidenciasRegistroId">ID --</span>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Cerrar listado de reincidencias"></button>
                    </div>
                </div>

                <div class="modal-body anomaly-recurrence-view" id="vistaListadoReincidencias">
                    <!-- Consulta compacta del encabezado de reincidencia registrado. -->
                    <div class="anomaly-recurrence-overview-actions">
                        <button class="anomaly-detail-summary anomaly-recurrence-base-toggle collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#detalleBaseReincidencias"
                                aria-expanded="false" aria-controls="detalleBaseReincidencias">
                            <span class="anomaly-detail-summary-icon">
                                <i class="bi bi-clipboard-data" aria-hidden="true"></i>
                            </span>
                            <span>
                                <small>Registro general</small>
                                <strong>Detalle de la reincidencia</strong>
                                <span>Consulta la información capturada para este historial.</span>
                            </span>
                            <i class="bi bi-chevron-down anomaly-recurrence-base-chevron" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="collapse anomaly-recurrence-base-collapse" id="detalleBaseReincidencias">
                        <section class="anomaly-detail-section anomaly-recurrence-base-detail"
                                 aria-label="Datos generales de la anormalidad">
                            <div class="anomaly-detail-grid anomaly-detail-grid-three">
                                <article class="anomaly-detail-field">
                                    <span class="anomaly-detail-label">ID de la anormalidad</span>
                                    <strong class="anomaly-detail-value" id="seguimientoAnormalidadId">--</strong>
                                </article>
                                <article class="anomaly-detail-field">
                                    <span class="anomaly-detail-label">ID de reincidencia</span>
                                    <strong class="anomaly-detail-value" id="detalleBaseIdReincidencia">--</strong>
                                </article>
                                <article class="anomaly-detail-field">
                                    <span class="anomaly-detail-label">No. seguimiento</span>
                                    <strong class="anomaly-detail-value" id="detalleBaseNumeroSeguimiento">--</strong>
                                </article>
                                <article class="anomaly-detail-field">
                                    <span class="anomaly-detail-label">Fecha y hora del registro</span>
                                    <strong class="anomaly-detail-value" id="seguimientoFechaDeteccion">--</strong>
                                </article>
                                <article class="anomaly-detail-field">
                                    <span class="anomaly-detail-label">Turno</span>
                                    <strong class="anomaly-detail-value" id="seguimientoTurnoBase">--</strong>
                                </article>
                                <article class="anomaly-detail-field">
                                    <span class="anomaly-detail-label">Área</span>
                                    <strong class="anomaly-detail-value" id="seguimientoAreaBase">--</strong>
                                </article>
                                <article class="anomaly-detail-field">
                                    <span class="anomaly-detail-label">Línea</span>
                                    <strong class="anomaly-detail-value" id="seguimientoLineaBase">--</strong>
                                </article>
                                <article class="anomaly-detail-field">
                                    <span class="anomaly-detail-label">Registrado por</span>
                                    <strong class="anomaly-detail-value" id="detalleBaseRegistradoPor">--</strong>
                                </article>
                            </div>

                            <!-- Condición capturada en el encabezado único de reincidencia. -->
                            <div class="anomaly-detail-grid anomaly-detail-grid-two mt-2">
                                <article class="anomaly-detail-field">
                                    <span class="anomaly-detail-label">Tipo de reincidencia</span>
                                    <strong class="anomaly-detail-value" id="detalleBaseTipoReincidencia">--</strong>
                                </article>
                                <article class="anomaly-detail-field anomaly-detail-note">
                                    <span class="anomaly-detail-label">Detalle de la anormalidad</span>
                                    <p class="anomaly-detail-value" id="detalleBaseDescripcionReincidencia">--</p>
                                </article>
                            </div>
                        </section>
                    </div>

                    <section class="anomaly-form-section" aria-labelledby="recurrenceListTitle">
                        <div class="anomaly-form-section-header anomaly-recurrence-list-header">
                            <div class="anomaly-recurrence-list-heading">
                                <span class="anomaly-section-number">1</span>
                                <div>
                                    <span class="anomaly-form-kicker">HISTORIAL DE REINCIDENCIAS</span>
                                    <h3 id="recurrenceListTitle">Registros de seguimiento</h3>
                                    <p>Consulta cada reincidencia y accede posteriormente a su seguimiento.</p>
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary-custom btn-custom anomaly-recurrence-add-demo-button"
                                    id="btnAgregarReincidencia">
                                <i class="bi bi-plus-circle" aria-hidden="true"></i>
                                Agregar reincidencia
                            </button>
                        </div>

                        <!-- El cuerpo se genera con los seguimientos consultados en la base de datos. -->
                        <div class="table-responsive anomaly-recurrence-table-wrap">
                            <table class="table anomalies-table anomaly-recurrence-table align-middle" id="tablaReincidencias">
                                <thead>
                                    <tr>
                                        <th class="text-center">Acción</th>
                                        <th>No. consecutivo</th>
                                        <th>Fecha y hora</th>
                                        <th>Cantidad</th>
                                        <th>Disposición</th>
                                        <th>Causa</th>
                                        <th>Contramedida</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr id="listadoReincidenciasVacio">
                                        <td colspan="7" class="anomaly-recurrence-empty">
                                            <i class="bi bi-inbox" aria-hidden="true"></i>
                                            <strong>Sin seguimientos registrados</strong>
                                            <span>Esta reincidencia todavía no cuenta con anormalidades relacionadas.</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <!-- Vista que reemplaza el listado al elegir una reincidencia. -->
                <div class="modal-body anomaly-recurrence-view anomaly-recurrence-detail-view" id="vistaDetalleReincidencia" hidden>
                    <button type="button" class="btn btn-outline-custom btn-custom anomaly-recurrence-back-button"
                            id="btnVolverListadoReincidencias">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>
                        Volver al listado
                    </button>

                    <div class="tab-content anomaly-recurrence-tab-content">
                        <!-- Vista de captura repetible del seguimiento de la reincidencia. -->
                        <section class="tab-pane fade show active" id="tabSeguimientoReincidencia" role="tabpanel"
                                 aria-label="Seguimiento de la reincidencia" tabindex="0">
                            <form id="formSeguimientoReincidencia" class="anomaly-recurrence-follow-form" novalidate>
                                <div class="anomaly-detail-summary anomaly-quality-summary">
                                    <span class="anomaly-detail-summary-icon anomaly-quality-summary-icon">
                                        <i class="bi bi-clipboard2-pulse" aria-hidden="true"></i>
                                    </span>
                                    <span>
                                        <strong>ANÁLISIS Y VERIFICACIÓN DE LA ANORMALIDAD REINCIDENTE</strong>
                                    </span>
                                    <span class="anomaly-quality-status">
                                        <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                        En captura
                                    </span>
                                </div>

                                <!-- Navegación entre las tres etapas del seguimiento seleccionado. -->
                                <nav class="anomaly-flow-pagination anomaly-recurrence-section-pagination"
                                     aria-label="Secciones del seguimiento de la reincidencia">
                                    <div class="nav anomaly-flow-tabs anomaly-recurrence-section-tabs"
                                         id="recurrenceFollowTabs" role="tablist">
                                        <button class="anomaly-flow-tab active"
                                                id="tabDatosSeguimientoReincidenciaButton"
                                                data-bs-toggle="tab"
                                                data-bs-target="#tabDatosSeguimientoReincidencia"
                                                type="button" role="tab"
                                                aria-controls="tabDatosSeguimientoReincidencia"
                                                aria-selected="true">
                                            <span class="anomaly-flow-number">1</span>
                                            <span class="anomaly-flow-copy">
                                                <strong>Datos del seguimiento</strong>
                                                <small>Fecha y disposición</small>
                                            </span>
                                            <i class="bi bi-calendar-check anomaly-flow-state" aria-hidden="true"></i>
                                        </button>

                                        <button class="anomaly-flow-tab"
                                                id="tabCausaContramedidaReincidenciaButton"
                                                data-bs-toggle="tab"
                                                data-bs-target="#tabCausaContramedidaReincidencia"
                                                type="button" role="tab"
                                                aria-controls="tabCausaContramedidaReincidencia"
                                                aria-selected="false">
                                            <span class="anomaly-flow-number">2</span>
                                            <span class="anomaly-flow-copy">
                                                <strong>Causa y contramedida</strong>
                                                <small>Análisis y atención</small>
                                            </span>
                                            <i class="bi bi-tools anomaly-flow-state" aria-hidden="true"></i>
                                        </button>

                                        <button class="anomaly-flow-tab"
                                                id="tabConfirmacionQaReincidenciaButton"
                                                data-bs-toggle="tab"
                                                data-bs-target="#tabConfirmacionQaReincidencia"
                                                type="button" role="tab"
                                                aria-controls="tabConfirmacionQaReincidencia"
                                                aria-selected="false">
                                            <span class="anomaly-flow-number">3</span>
                                            <span class="anomaly-flow-copy">
                                                <strong>Confirmación de QA</strong>
                                                <small>Antes y después</small>
                                            </span>
                                            <i class="bi bi-patch-check anomaly-flow-state" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </nav>

                                <div class="tab-content anomaly-recurrence-sections-content"
                                     id="recurrenceFollowTabContent">
                                <!-- Datos que identifican cada registro de seguimiento. -->
                                <section class="tab-pane fade show active anomaly-detail-section"
                                         id="tabDatosSeguimientoReincidencia" role="tabpanel"
                                         aria-labelledby="tabDatosSeguimientoReincidenciaButton" tabindex="0">
                                    <header class="anomaly-detail-section-title">
                                        <span><i class="bi bi-calendar-check" aria-hidden="true"></i></span>
                                        <div>
                                            <h3 id="recurrenceFollowGeneralTitle">Datos del seguimiento</h3>
                                            <p>Registra cuándo se realizó la atención y cuánto producto fue involucrado.</p>
                                        </div>
                                    </header>

                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                                <label class="form-label" for="seguimientoReincidenciaFechaHora">Fecha y hora <span class="required">*</span></label>
                                                <div class="anomaly-input-icon">
                                                    <i class="bi bi-calendar-event" aria-hidden="true"></i>
                                                    <input type="datetime-local" class="form-control"
                                                           id="seguimientoReincidenciaFechaHora"
                                                           name="seguimiento_reincidencia_fecha_hora"
                                                           readonly aria-readonly="true" required>
                                                </div>
                                        </div>
                                                                              
                                        <div class="col-12 col-md-6">
                                                <label class="form-label" for="seguimientoReincidenciaCantidad">Cantidad <span class="required">*</span></label>
                                                <input type="number" class="form-control" id="seguimientoReincidenciaCantidad"
                                                       name="seguimiento_reincidencia_cantidad" min="0" step="1"
                                                       placeholder="Ej. 10" required>
                                        </div>
                                    </div>

                                    <!-- Solo puede seleccionarse una disposición por seguimiento. -->
                                    <fieldset class="anomaly-field-group mt-3">
                                        <legend><i class="bi bi-ui-radios" aria-hidden="true"></i> Disposición <span class="required">*</span></legend>
                                        <div class="anomaly-disposition-options"
                                             id="seguimientoReincidenciaDisposicionOpciones">
                                            <label class="anomaly-disposition-option" for="seguimientoDisposicionEfu">
                                                <input class="form-check-input" type="radio" value="efu_pnc"
                                                       id="seguimientoDisposicionEfu"
                                                       name="seguimiento_reincidencia_disposicion" required>
                                                <span>
                                                    <strong>Colocar EFU y colocar en PNC</strong>
                                                    <small>Identificar y separar el producto o material afectado.</small>
                                                </span>
                                            </label>

                                            <label class="anomaly-disposition-option" for="seguimientoDisposicionReparacion">
                                                <input class="form-check-input" type="radio" value="chokotei_reparacion"
                                                       id="seguimientoDisposicionReparacion"
                                                       name="seguimiento_reincidencia_disposicion" required>
                                                <span>
                                                    <strong>Chokotei o reparación</strong>
                                                    <small>Aplicar la reparación o disposición correspondiente.</small>
                                                </span>
                                            </label>
                                        </div>
                                    </fieldset>

                                    <!-- Acción independiente para los datos generales del seguimiento. -->
                                    <div class="anomaly-quality-actions mt-3">
                                        <span>
                                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                                            Guarda la fecha, cantidad y disposición de esta reincidencia.
                                        </span>
                                        <button type="button" class="btn btn-primary-custom btn-custom"
                                                id="btnRegistrarDatosSeguimientoReincidencia">
                                            <i class="bi bi-save" aria-hidden="true"></i>
                                            Registrar datos del seguimiento
                                        </button>
                                    </div>
                                </section>

                                <!-- Causa, contramedida y persona responsable de la atención. -->
                                <section class="tab-pane fade anomaly-detail-section anomaly-quality-conclusion"
                                         id="tabCausaContramedidaReincidencia" role="tabpanel"
                                         aria-labelledby="tabCausaContramedidaReincidenciaButton" tabindex="0">
                                    <header class="anomaly-detail-section-title">
                                        <span><i class="bi bi-tools" aria-hidden="true"></i></span>
                                        <div>
                                            <h3 id="recurrenceDispositionTitle">Causa y contramedida</h3>
                                            <p>Documenta la causa identificada y la acción realizada.</p>
                                        </div>
                                    </header>

                                    <div class="row g-3 mt-1">
                                        <div class="col-lg-6">
                                            <label class="form-label" for="seguimientoReincidenciaCausa">Causa <span class="required">*</span></label>
                                            <textarea class="form-control" id="seguimientoReincidenciaCausa"
                                                      name="seguimiento_reincidencia_causa" rows="4" maxlength="700"
                                                      placeholder="Describe la causa identificada." required></textarea>
                                            <small class="anomaly-form-hint">Máximo 700 caracteres.</small>
                                        </div>

                                        <div class="col-lg-6">
                                            <label class="form-label" for="seguimientoReincidenciaContramedida">Contramedida <span class="required">*</span></label>
                                            <textarea class="form-control" id="seguimientoReincidenciaContramedida"
                                                      name="seguimiento_reincidencia_contramedida" rows="4" maxlength="700"
                                                      placeholder="Describe la contramedida aplicada." required></textarea>
                                            <small class="anomaly-form-hint">Máximo 700 caracteres.</small>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label" for="seguimientoNominaResponsable">Nómina de quien atiende <span class="required">*</span></label>
                                            <input type="text" class="form-control" id="seguimientoNominaResponsable"
                                                   name="seguimiento_nomina_responsable" maxlength="20" inputmode="numeric"
                                                   placeholder="Se obtiene de la sesión" autocomplete="off"
                                                   readonly aria-readonly="true" required>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label" for="seguimientoNombreResponsable">Nombre de quien atiende <span class="required">*</span></label>
                                            <input type="text" class="form-control" id="seguimientoNombreResponsable"
                                                   name="seguimiento_nombre_responsable" maxlength="120"
                                                   placeholder="Se obtiene de la sesión" autocomplete="off"
                                                   readonly aria-readonly="true" required>
                                        </div>
                                    </div>

                                    <!-- Acción independiente para el análisis y la acción aplicada. -->
                                    <div class="anomaly-quality-actions mt-3">
                                        <span>
                                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                                            Guarda conjuntamente la causa, contramedida y responsable.
                                        </span>
                                        <button type="button" class="btn btn-primary-custom btn-custom"
                                                id="btnRegistrarCausaContramedidaReincidencia">
                                            <i class="bi bi-save" aria-hidden="true"></i>
                                            Registrar causa y contramedida
                                        </button>
                                    </div>
                                </section>

                                <!-- Confirmación de Calidad e identificación del inspector responsable. -->
                                <section class="tab-pane fade anomaly-detail-section anomaly-quality-conclusion"
                                         id="tabConfirmacionQaReincidencia" role="tabpanel"
                                         aria-labelledby="tabConfirmacionQaReincidenciaButton" tabindex="0">
                                    <header class="anomaly-detail-section-title">
                                        <span><i class="bi bi-patch-check" aria-hidden="true"></i></span>
                                        <div>
                                            <h3 id="recurrenceQaConfirmationTitle">Confirmación de QA</h3>
                                            <p>Registra tres verificaciones antes y tres después de aplicar la contramedida.</p>
                                        </div>
                                    </header>

                                    <div class="row g-3 align-items-stretch">
                                        <div class="col-lg-6">
                                            <fieldset class="anomaly-field-group h-100">
                                                <legend><i class="bi bi-arrow-bar-left" aria-hidden="true"></i> Antes</legend>
                                                <div class="anomaly-serial-list">
                                                    <div class="anomaly-serial-entry">
                                                        <fieldset class="anomaly-result-box">
                                                            <legend class="form-label">Revisión 1 <span class="required">*</span></legend>
                                                            <div class="anomaly-inline-result-options">
                                                                <input type="radio" class="btn-check" name="seguimiento_qa_antes_1"
                                                                       id="seguimientoQaAntes1Ok" value="OK" autocomplete="off" required>
                                                                <label class="btn btn-outline-success" for="seguimientoQaAntes1Ok">OK</label>
                                                                <input type="radio" class="btn-check" name="seguimiento_qa_antes_1"
                                                                       id="seguimientoQaAntes1Ng" value="NG" autocomplete="off" required>
                                                                <label class="btn btn-outline-danger" for="seguimientoQaAntes1Ng">NG</label>
                                                            </div>
                                                        </fieldset>
                                                    </div>

                                                    <div class="anomaly-serial-entry">
                                                        <fieldset class="anomaly-result-box">
                                                            <legend class="form-label">Revisión 2 <span class="required">*</span></legend>
                                                            <div class="anomaly-inline-result-options">
                                                                <input type="radio" class="btn-check" name="seguimiento_qa_antes_2"
                                                                       id="seguimientoQaAntes2Ok" value="OK" autocomplete="off" required>
                                                                <label class="btn btn-outline-success" for="seguimientoQaAntes2Ok">OK</label>
                                                                <input type="radio" class="btn-check" name="seguimiento_qa_antes_2"
                                                                       id="seguimientoQaAntes2Ng" value="NG" autocomplete="off" required>
                                                                <label class="btn btn-outline-danger" for="seguimientoQaAntes2Ng">NG</label>
                                                            </div>
                                                        </fieldset>
                                                    </div>

                                                    <div class="anomaly-serial-entry">
                                                        <fieldset class="anomaly-result-box">
                                                            <legend class="form-label">Revisión 3 <span class="required">*</span></legend>
                                                            <div class="anomaly-inline-result-options">
                                                                <input type="radio" class="btn-check" name="seguimiento_qa_antes_3"
                                                                       id="seguimientoQaAntes3Ok" value="OK" autocomplete="off" required>
                                                                <label class="btn btn-outline-success" for="seguimientoQaAntes3Ok">OK</label>
                                                                <input type="radio" class="btn-check" name="seguimiento_qa_antes_3"
                                                                       id="seguimientoQaAntes3Ng" value="NG" autocomplete="off" required>
                                                                <label class="btn btn-outline-danger" for="seguimientoQaAntes3Ng">NG</label>
                                                            </div>
                                                        </fieldset>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>

                                        <div class="col-lg-6">
                                            <fieldset class="anomaly-field-group h-100">
                                                <legend><i class="bi bi-arrow-bar-right" aria-hidden="true"></i> Después</legend>
                                                <div class="anomaly-serial-list">
                                                    <div class="anomaly-serial-entry">
                                                        <fieldset class="anomaly-result-box">
                                                            <legend class="form-label">Revisión 1 <span class="required">*</span></legend>
                                                            <div class="anomaly-inline-result-options">
                                                                <input type="radio" class="btn-check" name="seguimiento_qa_despues_1"
                                                                       id="seguimientoQaDespues1Ok" value="OK" autocomplete="off" required>
                                                                <label class="btn btn-outline-success" for="seguimientoQaDespues1Ok">OK</label>
                                                                <input type="radio" class="btn-check" name="seguimiento_qa_despues_1"
                                                                       id="seguimientoQaDespues1Ng" value="NG" autocomplete="off" required>
                                                                <label class="btn btn-outline-danger" for="seguimientoQaDespues1Ng">NG</label>
                                                            </div>
                                                        </fieldset>
                                                    </div>

                                                    <div class="anomaly-serial-entry">
                                                        <fieldset class="anomaly-result-box">
                                                            <legend class="form-label">Revisión 2 <span class="required">*</span></legend>
                                                            <div class="anomaly-inline-result-options">
                                                                <input type="radio" class="btn-check" name="seguimiento_qa_despues_2"
                                                                       id="seguimientoQaDespues2Ok" value="OK" autocomplete="off" required>
                                                                <label class="btn btn-outline-success" for="seguimientoQaDespues2Ok">OK</label>
                                                                <input type="radio" class="btn-check" name="seguimiento_qa_despues_2"
                                                                       id="seguimientoQaDespues2Ng" value="NG" autocomplete="off" required>
                                                                <label class="btn btn-outline-danger" for="seguimientoQaDespues2Ng">NG</label>
                                                            </div>
                                                        </fieldset>
                                                    </div>

                                                    <div class="anomaly-serial-entry">
                                                        <fieldset class="anomaly-result-box">
                                                            <legend class="form-label">Revisión 3 <span class="required">*</span></legend>
                                                            <div class="anomaly-inline-result-options">
                                                                <input type="radio" class="btn-check" name="seguimiento_qa_despues_3"
                                                                       id="seguimientoQaDespues3Ok" value="OK" autocomplete="off" required>
                                                                <label class="btn btn-outline-success" for="seguimientoQaDespues3Ok">OK</label>
                                                                <input type="radio" class="btn-check" name="seguimiento_qa_despues_3"
                                                                       id="seguimientoQaDespues3Ng" value="NG" autocomplete="off" required>
                                                                <label class="btn btn-outline-danger" for="seguimientoQaDespues3Ng">NG</label>
                                                            </div>
                                                        </fieldset>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>

                                    <fieldset class="anomaly-field-group mt-3" aria-labelledby="recurrenceInspectorTitle">
                                        <legend id="recurrenceInspectorTitle">
                                            <i class="bi bi-person-check" aria-hidden="true"></i> Inspector de Calidad
                                        </legend>
                                        <p class="anomaly-form-hint mb-3">Registra a la persona que confirma los resultados antes y después.</p>

                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label" for="seguimientoNominaInspector">Nómina <span class="required">*</span></label>
                                                <input type="text" class="form-control" id="seguimientoNominaInspector"
                                                       name="seguimiento_nomina_inspector" maxlength="20" inputmode="numeric"
                                                       placeholder="Se obtiene de la sesión" autocomplete="off"
                                                       readonly aria-readonly="true" required>
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label" for="seguimientoNombreInspector">Nombre <span class="required">*</span></label>
                                                <input type="text" class="form-control" id="seguimientoNombreInspector"
                                                       name="seguimiento_nombre_inspector" maxlength="120"
                                                       placeholder="Se obtiene de la sesión" autocomplete="off"
                                                       readonly aria-readonly="true" required>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <!-- Acción independiente para las verificaciones del inspector QA. -->
                                    <div class="anomaly-quality-actions mt-3">
                                        <span>
                                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                                            Guarda las revisiones antes y después junto con el inspector.
                                        </span>
                                        <button type="button" class="btn btn-primary-custom btn-custom"
                                                id="btnRegistrarConfirmacionQaReincidencia">
                                            <i class="bi bi-save" aria-hidden="true"></i>
                                            Registrar confirmación de QA
                                        </button>
                                    </div>
                                </section>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>

                <div class="modal-footer">
                    <span class="anomaly-form-footer-note">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                        Seguimiento de la reincidencia
                    </span>
                    <button type="button" class="btn btn-outline-custom btn-custom" data-bs-dismiss="modal">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notificaciones reutilizables para registros, validaciones y errores. -->
    <div class="toast-container position-fixed top-0 end-0 p-3 anomaly-toast-container">
        <div id="toastAnormalidades" class="toast anomaly-toast anomaly-toast--info border-0"
             role="status" aria-live="polite" aria-atomic="true">
            <div class="toast-body">
                <span class="anomaly-toast-icon" aria-hidden="true">
                    <i id="toastAnormalidadesIcon" class="bi bi-info-lg"></i>
                </span>
                <div class="anomaly-toast-copy">
                    <strong id="toastAnormalidadesTitulo">Información</strong>
                    <p id="toastAnormalidadesMensaje" class="mb-0"></p>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast"
                        aria-label="Cerrar notificación"></button>
            </div>
        </div>
    </div>
</div>
