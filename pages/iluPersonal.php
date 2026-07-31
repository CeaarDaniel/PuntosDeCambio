<div id="iluPersonalView" class="certification-view">
    <a href="#/menuCertificaciones" class="btn-float btn-back-certifications" id="btnBack"
       title="Volver al men&uacute; de certificaciones" aria-label="Volver al men&uacute; de certificaciones">
        <i class="bi bi-chevron-left" aria-hidden="true"></i>
    </a>

            <section class="hero-panel" aria-labelledby="iluPersonalPageTitle">
                <div class="hero-content">
                    <div class="hero-heading">
                        <div>
                            <span class="section-label">Asignación por trabajador</span>
                            <h1 id="iluPersonalPageTitle">Operaciones liberadas del personal</h1>
                            <p>Selecciona una línea, elige uno o varios trabajadores y asigna o elimina los procesos donde se encuentra liberado</p>
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
                            <small>El personal se mostrará después de seleccionar una línea.</small>
                        </div>
                    </div>
                </div>
            </section>

            <section class="stats-grid stats-grid-four" aria-label="Resumen de la línea seleccionada">
                <article class="stat-card">
                    <div class="stat-icon stat-icon-primary">
                        <i class="bi bi-people" aria-hidden="true"></i>
                    </div>
                    <div class="stat-copy">
                        <span>Personal</span>
                        <strong id="totalPersonal">0</strong>
                        <small>Trabajadores de la línea</small>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon stat-icon-secondary">
                        <i class="bi bi-patch-check" aria-hidden="true"></i>
                    </div>
                    <div class="stat-copy">
                        <span>Con cursos</span>
                        <strong id="totalConCurso">0</strong>
                        <small>Al menos un curso</small>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon stat-icon-accent">
                        <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                    </div>
                    <div class="stat-copy">
                        <span>Sin cursos</span>
                        <strong id="totalSinCurso">0</strong>
                        <small>Pendientes de asignación</small>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon stat-icon-primary">
                        <i class="bi bi-journal-check" aria-hidden="true"></i>
                    </div>
                    <div class="stat-copy">
                        <span>Registros ILU</span>
                        <strong id="totalRegistrosIlu">0</strong>
                        <small>Relaciones persona-curso</small>
                    </div>
                </article>
            </section>

            <section class="data-card" aria-labelledby="table-title">
                <div class="data-card-header">
                    <div>
                        <span class="section-label section-label-dark">Asignación de cursos</span>
                        <h2 id="table-title">Personal de la línea</h2>
                        <p>Selecciona una o varias personas para asignar o quitar un curso en conjunto.</p>
                    </div>
                    <span class="record-count" id="contadorVisible">0 registros</span>
                </div>

                <div class="toolbar toolbar-filters-only">
                    <div class="toolbar-actions">
                        <div class="select-box">
                            <i class="bi bi-clock-history" aria-hidden="true"></i>
                            <label for="filtroTurno" class="visually-hidden">Filtrar por turno</label>
                            <select class="form-select" id="filtroTurno" disabled>
                                <option value="todos">Todos los turnos</option>
                                <option value="1" selected>Turno 1</option>
                                <option value="2">Turno 2</option>
                            </select>
                        </div>

                        <div class="select-box">
                            <i class="bi bi-funnel" aria-hidden="true"></i>
                            <label for="filtroEstado" class="visually-hidden">Filtrar por estado del curso</label>
                            <select class="form-select" id="filtroEstado" disabled>
                                <option value="todos">Todos los estados de curso</option>
                                <option value="con_curso">Con cursos</option>
                                <option value="sin_curso">Sin cursos</option>
                            </select>
                        </div>

                        <button class="btn btn-outline-secondary btn-reset" type="button" id="btnLimpiarFiltros" disabled>
                            <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                            <span>Restablecer</span>
                        </button>
                    </div>

                    <fieldset class="personal-status-filter" id="filtroEstatusPersonal" disabled>
                        <legend>
                            <i class="bi bi-person-check" aria-hidden="true"></i>
                            Estatus del personal
                        </legend>
                        <div class="status-check-list" id="listaEstatusPersonal" role="group"
                             aria-label="Filtrar por estatus del personal">
                            <span class="status-check-empty">Cargando estatus...</span>
                        </div>
                    </fieldset>
                </div>

                <div class="bulk-panel personal-bulk-panel is-hidden mt-3" id="panelAccionesMasivas">
                    <div class="bulk-selection">
                        <span class="bulk-selection-icon">
                            <i class="bi bi-check2-square" aria-hidden="true"></i>
                        </span>
                        <div>
                            <strong id="textoSeleccionados">0 personas seleccionadas</strong>
                            <small>La selección se conserva al cambiar de página.</small>
                        </div>
                    </div>

                    <div class="bulk-actions-grid">
                        <section class="bulk-action-card" aria-labelledby="accionCursosTitulo">
                            <div class="bulk-action-heading">
                                <i class="bi bi-journal-check" aria-hidden="true"></i>
                                <div>
                                    <strong id="accionCursosTitulo">Cursos asignados</strong>
                                    <small>Asigna o retira una certificación.</small>
                                </div>
                            </div>
                            <div class="bulk-controls">
                                <select class="form-select" id="certificacionMasiva" aria-label="Curso o certificación para asignar o quitar">
                                    <option value="">Selecciona un curso...</option>
                                </select>

                                <button type="button" class="btn btn-bulk-assign" id="btnAsignarMasivo">
                                    <i class="bi bi-patch-check" aria-hidden="true"></i>
                                    Asignar curso
                                </button>

                                <button type="button" class="btn btn-bulk-remove" id="btnQuitarMasivo">
                                    <i class="bi bi-x-circle" aria-hidden="true"></i>
                                    Quitar curso
                                </button>
                            </div>
                        </section>

                        <section class="bulk-action-card" aria-labelledby="accionEstatusTitulo">
                            <div class="bulk-action-heading">
                                <i class="bi bi-person-gear" aria-hidden="true"></i>
                                <div>
                                    <strong id="accionEstatusTitulo">Estatus del personal</strong>
                                    <small>Actualiza el estado operativo.</small>
                                </div>
                            </div>
                            <div class="bulk-controls">
                                <select class="form-select" id="estatusPersonalMasivo"
                                        aria-label="Nuevo estatus del personal">
                                    <option value="">Selecciona un estatus...</option>
                                    <option value="0">Disponible</option>
                                    <option value="2">Eliminado</option>
                                </select>

                                <button type="button" class="btn btn-bulk-assign" id="btnActualizarEstatusMasivo">
                                    <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
                                    Actualizar estatus
                                </button>
                            </div>
                        </section>

                        <section class="bulk-action-card" aria-labelledby="accionLineaTitulo">
                            <div class="bulk-action-heading">
                                <i class="bi bi-diagram-3" aria-hidden="true"></i>
                                <div>
                                    <strong id="accionLineaTitulo">Línea asignada</strong>
                                    <small>Mueve el personal a otra línea.</small>
                                </div>
                            </div>
                            <div class="bulk-controls">
                                <select class="form-select" id="lineaPersonalMasiva"
                                        aria-label="Nueva línea del personal">
                                    <option value="">Selecciona una línea...</option>
                                </select>

                                <button type="button" class="btn btn-bulk-assign" id="btnActualizarLineaMasiva">
                                    <i class="bi bi-arrow-left-right" aria-hidden="true"></i>
                                    Actualizar línea
                                </button>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="table-shell px-3">
                    <table id="tablaPersonal" class="table app-table align-middle w-100">
                        <thead>
                        <tr>
                            <th class="text-center">
                                <input class="form-check-input table-check" type="checkbox" id="seleccionarPagina" aria-label="Seleccionar personal de esta página">
                            </th>
                            <th>Nómina</th>
                            <th>Nombre</th>
                            <th>Turno</th>
                            <th>Estatus</th>
                            <th>Cursos asignados</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </section>

            <footer class="app-footer">
                <span>Cursos del personal</span>
            </footer>

<div class="modal fade" id="modalCursosPersona" tabindex="-1" aria-labelledby="modalCursosPersonaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content app-modal">
            <div class="modal-header">
                <div class="modal-title-group">
                    <span class="modal-icon">
                        <i class="bi bi-person-badge" aria-hidden="true"></i>
                    </span>
                    <div>
                        <span class="modal-eyebrow">Detalle individual</span>
                        <h2 class="modal-title" id="modalCursosPersonaLabel">Cursos asignados</h2>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="nominaDetalle">

                <div class="form-intro">
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    <span id="nombrePersonaDetalle">Persona seleccionada</span>
                </div>

                <div id="listaCursosDetalle" class="detail-list"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-cancel" data-bs-dismiss="modal">Cerrar</button>
            </div>
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
                <span class="modal-eyebrow">Confirmar cambio</span>
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