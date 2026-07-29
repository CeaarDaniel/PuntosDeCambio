<div id="certificacionesView" class="certification-view">
    <a href="#/menuCertificaciones" class="btn-float btn-back-certifications" id="btnBack"
       title="Volver al men&uacute; de certificaciones" aria-label="Volver al men&uacute; de certificaciones">
        <i class="bi bi-chevron-left" aria-hidden="true"></i>
    </a>

                <section class="hero-panel" aria-labelledby="certificationsPageTitle">
                    <div class="hero-content">
                        <div class="hero-heading">
                            <div>
                               <span class="section-label">GESTION DE CERTIFICACIONES</span>
                                <h1 id="certificationsPageTitle">Certificaciones</h1>
                                <p>Consulta, registra y administra las certificaciones utilizadas en los procesos de producción.</p>
                            </div>

                            <button type="button" class="btn btn-primary btn-create" id="btnNuevaCertificacion">
                                <i class="bi bi-plus-lg" aria-hidden="true"></i>
                                <span>Nueva certificación</span>
                            </button>
                        </div>
                    </div>
                    <div class="hero-decoration hero-decoration-one"></div>
                    <div class="hero-decoration hero-decoration-two"></div>
                </section>

                <section class="stats-grid" aria-label="Resumen de certificaciones">
                    <article class="stat-card">
                        <div class="stat-icon stat-icon-primary">
                            <i class="bi bi-award" aria-hidden="true"></i>
                        </div>
                        <div class="stat-copy">
                            <span>Total de registros</span>
                            <strong id="totalCertificaciones">0</strong>
                            <small>Certificaciones disponibles</small>
                        </div>
                    </article>

                    <article class="stat-card">
                        <div class="stat-icon stat-icon-secondary">
                            <i class="bi bi-diagram-3" aria-hidden="true"></i>
                        </div>
                        <div class="stat-copy">
                            <span>Tipos de proceso</span>
                            <strong id="totalProcesos">0</strong>
                            <small>Categorías registradas</small>
                        </div>
                    </article>

                    <article class="stat-card">
                        <div class="stat-icon stat-icon-accent">
                            <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                        </div>
                        <div class="stat-copy">
                            <span>Con descripción</span>
                            <strong id="totalConDescripcion">0</strong>
                            <small>Registros documentados</small>
                        </div>
                    </article>
                </section>

                <section class="data-card" aria-labelledby="table-title">
                    <div class="data-card-header">
                        <div>
                            <span class="section-label section-label-dark">Administración</span>
                            <h2 id="table-title">Listado de certificaciones</h2>
                            <p>Usa la búsqueda o el filtro para localizar un registro rápidamente.</p>
                        </div>
                        <span class="record-count" id="contadorVisible">0 registros</span>
                    </div>

                    <div class="toolbar">
                        <div class="search-box">
                            <i class="bi bi-search" aria-hidden="true"></i>
                            <input
                                type="search"
                                class="form-control"
                                id="busquedaCertificacion"
                                placeholder="Buscar por código, nombre o descripción"
                                aria-label="Buscar certificaciones"
                                autocomplete="off"
                            >
                            <button class="clear-search d-none" type="button" id="btnLimpiarBusqueda" aria-label="Limpiar búsqueda">
                                <i class="bi bi-x-lg" aria-hidden="true"></i>
                            </button>
                        </div>

                        <div class="toolbar-actions">
                            <div class="select-box">
                                <i class="bi bi-funnel" aria-hidden="true"></i>
                                <label for="filtroProceso" class="visually-hidden">Filtrar por proceso</label>
                                <select class="form-select" id="filtroProceso">
                                    <option value="">Todos los procesos</option>
                                </select>
                            </div>

                            <button class="btn btn-outline-secondary btn-reset" type="button" id="btnLimpiarFiltros">
                                <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                                <span>Restablecer</span>
                            </button>
                        </div>
                    </div>

                    <div class="table-shell px-3 mt-3">
                        <table id="tablaCertificaciones" class="table app-table align-middle w-100">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Certificación</th>
                                    <th>Proceso</th>
                                    <th>Descripción</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </section>

                <footer class="app-footer">
                    <span>SPC Quality · Interfaz demostrativa</span>
                    <span>Los cambios se conservan únicamente durante la sesión.</span>
                </footer>

    <div class="modal fade" id="modalCertificacion" tabindex="-1" aria-labelledby="modalCertificacionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content app-modal">
                <form id="formCertificacion" novalidate>
                    <div class="modal-header">
                        <div class="modal-title-group">
                            <span class="modal-icon">
                                <i class="bi bi-award" aria-hidden="true"></i>
                            </span>
                            <div>
                                <span class="modal-eyebrow">Información del registro</span>
                                <h2 class="modal-title" id="modalCertificacionLabel">Nueva certificación</h2>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="idCR" name="idCR">

                        <div class="form-intro">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            <span>Los campos marcados con <strong>*</strong> son obligatorios.</span>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-5">
                                <label class="form-label" for="codigo_certificacion">
                                    Código de certificación <span class="required">*</span>
                                </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="codigo_certificacion"
                                    name="codigo_certificacion"
                                    maxlength="30"
                                    placeholder="Ej. CERT-SOLD-01"
                                    autocomplete="off"
                                    required
                                >
                                <div class="form-hint">Identificador único, máximo 30 caracteres.</div>
                                <div class="invalid-feedback">Ingresa un código válido.</div>
                            </div>

                            <div class="col-md-7">
                                <label class="form-label" for="nombre_certificacion">
                                    Nombre de la certificación <span class="required">*</span>
                                </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="nombre_certificacion"
                                    name="nombre_certificacion"
                                    maxlength="150"
                                    placeholder="Nombre descriptivo"
                                    autocomplete="off"
                                    required
                                >
                                <div class="form-hint">Usa un nombre breve y fácil de identificar.</div>
                                <div class="invalid-feedback">Ingresa el nombre de la certificación.</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="tipo_proceso">Tipo de proceso</label>
                                <div class="input-icon-group">
                                    <i class="bi bi-diagram-3" aria-hidden="true"></i>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="tipo_proceso"
                                        name="tipo_proceso"
                                        maxlength="50"
                                        list="listaProcesos"
                                        placeholder="Ej. Ensamble, Soldadura o Inspección"
                                        autocomplete="off"
                                    >
                                </div>
                                <datalist id="listaProcesos"></datalist>
                            </div>

                            <div class="col-12">
                                <div class="label-row">
                                    <label class="form-label mb-0" for="descripcion">Descripción</label>
                                    <span class="character-counter"><span id="contadorDescripcion">0</span> caracteres</span>
                                </div>
                                <textarea
                                    class="form-control"
                                    id="descripcion"
                                    name="descripcion"
                                    rows="5"
                                    maxlength="800"
                                    placeholder="Describe el objetivo, alcance o requisitos de esta certificación..."
                                ></textarea>
                                <div class="form-hint">Puedes incluir información útil para el personal operativo.</div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-save" id="btnGuardarCertificacion">
                            <i class="bi bi-check2-circle" aria-hidden="true"></i>
                            <span>Guardar certificación</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content app-modal delete-modal">
                <div class="modal-body text-center">
                    <span class="delete-icon">
                        <i class="bi bi-trash3" aria-hidden="true"></i>
                    </span>
                    <span class="modal-eyebrow">Confirmar eliminación</span>
                    <h2 class="delete-title" id="modalEliminarLabel">¿Eliminar certificación?</h2>
                    <p>Se eliminará <strong id="nombreCertificacionEliminar"></strong> de esta sesión.</p>
                    <input type="hidden" id="idEliminar">

                    <div class="delete-actions">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">
                            <i class="bi bi-trash3" aria-hidden="true"></i>
                            Eliminar
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