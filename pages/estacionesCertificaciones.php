<div id="estacionesCertificacionesView" class="certification-view">
    <a href="#/menuCertificaciones" class="btn-float btn-back-certifications" id="btnBack" title="Volver al men&uacute; de certificaciones" aria-label="Volver al men&uacute; de certificaciones">
        <i class="bi bi-chevron-left" aria-hidden="true"></i>
    </a>

    <section class="hero-panel" aria-labelledby="stationsCertificationsPageTitle">
        <div class="hero-content">
            <div class="hero-heading">
                <div> 
                    <span class="section-label">GESTION DE CERTIFICACIONES POR ESTACION</span>
                    <h1 id="stationsCertificationsPageTitle">Certificaciones por estación</h1>
                    <p>Asigna, modifica o retira el curso/certificacion asociado a una o varias estaciones.
                    </p>
                </div>
            </div>
        </div>
        <div class="hero-decoration hero-decoration-one"></div>
        <div class="hero-decoration hero-decoration-two"></div>
    </section>

    <section class="line-selector-card" aria-labelledby="line-selector-title">
        <div class="line-selector-grid">
            <div>
                <label class="line-selector-label" id="line-selector-title" for="selectLinea">
                    1. Selecciona la línea de producción
                </label>
                <div class="line-select-wrap">
                    <i class="bi bi-diagram-3" aria-hidden="true"></i>
                    <select class="form-select" id="selectLinea">
                        <option value="">Selecciona una línea...</option>
                    </select>
                </div>
            </div>

            <div class="selected-line-info" id="informacionLinea">
                <span class="info-icon">
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                </span>
                <div>
                    <strong>Ninguna línea seleccionada</strong>
                    <small>Las estaciones se mostrarán después de seleccionar una línea.</small>
                </div>
            </div>
        </div>
    </section>

    <section class="stats-grid" aria-label="Resumen de la línea seleccionada">
        <article class="stat-card">
            <div class="stat-icon stat-icon-primary">
                <i class="bi bi-geo-alt" aria-hidden="true"></i>
            </div>
            <div class="stat-copy">
                <span>Estaciones</span>
                <strong id="totalEstaciones">0</strong>
                <small>Procesos de la línea</small>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-icon stat-icon-secondary">
                <i class="bi bi-patch-check" aria-hidden="true"></i>
            </div>
            <div class="stat-copy">
                <span>Con curso</span>
                <strong id="totalConCurso">0</strong>
                <small>Curso asignado</small>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-icon stat-icon-accent">
                <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
            </div>
            <div class="stat-copy">
                <span>Sin curso</span>
                <strong id="totalSinCurso">0</strong>
                <small>Pendientes de asignación</small>
            </div>
        </article>
    </section>

    <section class="data-card" aria-labelledby="table-title">
        <div class="data-card-header">
            <div>
                <span class="section-label section-label-dark">Asignación de cursos</span>
                <h2 id="table-title">Estaciones de la línea</h2>
                <p>Selecciona una o varias estaciones para aplicar cambios en conjunto.</p>
            </div>
            <span class="record-count" id="contadorVisible">0 registros</span>
        </div>

        <div class="toolbar">
            <div class="search-box">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input
                    type="search"
                    class="form-control"
                    id="busquedaEstacion"
                    placeholder="Buscar estación, descripción o certificación"
                    aria-label="Buscar estaciones"
                    autocomplete="off"
                    disabled
                >
                <button class="clear-search d-none" type="button" id="btnLimpiarBusqueda" aria-label="Limpiar búsqueda">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>

            <div class="toolbar-actions">
                <div class="select-box">
                    <i class="bi bi-funnel" aria-hidden="true"></i>
                    <label for="filtroEstado" class="visually-hidden">Filtrar por estado</label>
                    <select class="form-select" id="filtroEstado" disabled>
                        <option value="todos">Todos los estados</option>
                        <option value="con_curso">Con curso asignado</option>
                        <option value="sin_curso">Sin curso asignado</option>
                        <option value="certificada">Operaciones certificadas</option>
                        <option value="no_certificada">Operaciones no certificadas</option>
                    </select>
                </div>

                <button class="btn btn-outline-secondary btn-reset" type="button" id="btnLimpiarFiltros" disabled>
                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                    <span>Restablecer</span>
                </button>
            </div>
        </div>

        <div class="bulk-panel is-hidden mt-2" id="panelAccionesMasivas">
            <div class="bulk-selection">
                <span class="bulk-selection-icon">
                    <i class="bi bi-check2-square" aria-hidden="true"></i>
                </span>
                <div>
                    <strong id="textoSeleccionados">0 estaciones seleccionadas</strong>
                    <small>La selección se conserva al cambiar de página.</small>
                </div>
            </div>

            <div class="bulk-controls">
                <select class="form-select" id="certificacionMasiva" aria-label="Certificación para asignar">
                    <option value="">Selecciona una certificación...</option>
                </select>

                <button type="button" class="btn btn-bulk-assign" id="btnAsignarMasivo">
                    <i class="bi bi-patch-check" aria-hidden="true"></i>
                    Asignar o reemplazar
                </button>

                <button type="button" class="btn btn-bulk-remove" id="btnQuitarMasivo">
                    <i class="bi bi-x-circle" aria-hidden="true"></i>
                    Quitar curso
                </button>
            </div>
        </div>

        <div class="table-shell px-3">
            <table id="tablaEstaciones" class="table app-table align-middle w-100">
                <thead>
                <tr>
                    <th class="text-center">
                        <input class="form-check-input table-check" type="checkbox" id="seleccionarPagina" aria-label="Seleccionar estaciones de esta página">
                    </th>
                    <th>Estación</th>
                    <th>Descripción</th>
                    <th>Tipo de operación</th>
                    <th>Curso asignado</th>
                    <th class="text-end">Acciones</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>

    <footer class="app-footer">
        <span>SPC Quality · Certificaciones por estación</span>
        <span>Solo se muestran estaciones de la línea seleccionada.</span>
    </footer>

    <div class="modal fade" id="modalEditarEstacion" tabindex="-1" aria-labelledby="modalEditarEstacionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content app-modal">
                <form id="formEditarEstacion" novalidate>
                    <div class="modal-header">
                        <div class="modal-title-group">
                            <span class="modal-icon">
                                <i class="bi bi-geo-alt" aria-hidden="true"></i>
                            </span>
                            <div>
                                <span class="modal-eyebrow">Configuración individual</span>
                                <h2 class="modal-title" id="modalEditarEstacionLabel">Editar curso</h2>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="idEstacionEditar">

                        <div class="form-intro">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            <span id="nombreEstacionEditar">Estación seleccionada</span>
                        </div>

                        <div id="contenedorCertificacionEditar">
                            <label class="form-label" for="certificacionEditar">Curso o certificación asignada</label>
                            <select class="form-select" id="certificacionEditar">
                                <option value="">Sin curso asignado</option>
                            </select>
                            <div class="form-hint">Esta pantalla solo modifica el curso asignado. El tipo de operación certificada o no certificada no se modifica.</div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-save" id="btnGuardarEstacion">
                            <i class="bi bi-check2-circle" aria-hidden="true"></i>
                            <span>Guardar cambios</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalConfirmarMasivo" tabindex="-1" aria-labelledby="modalConfirmarMasivoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content app-modal delete-modal">
                <div class="modal-body text-center">
                    <span class="delete-icon" id="iconoConfirmacionMasiva">
                        <i class="bi bi-patch-check" aria-hidden="true"></i>
                    </span>
                    <span class="modal-eyebrow">Confirmar cambio masivo</span>
                    <h2 class="delete-title" id="modalConfirmarMasivoLabel">¿Aplicar cambios?</h2>
                    <p id="mensajeConfirmacionMasiva"></p>

                    <div class="delete-actions">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="btnConfirmarMasivo">
                            <i class="bi bi-check2" aria-hidden="true"></i>
                            Confirmar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toastNotificacion" class="toast app-toast border-0" role="status" aria-live="polite" aria-atomic="true">
            <div class="toast-body">
                <span class="toast-icon">
                    <i class="bi bi-check-lg" aria-hidden="true"></i>
                </span>
                <div>
                    <strong id="toastTitulo">Cambios guardados</strong>
                    <p id="toastMensaje" class="mb-0"></p>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    </div>
</div>
