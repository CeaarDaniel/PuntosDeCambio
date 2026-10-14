<div class="container-fluid px-3 px-lg-4 pb-4 crear-page">
    <div class="impresion-header-card mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="impresion-header-icon">
                <i class="fa-solid fa-file-pen"></i>
            </div>
            <div>
                <h3 class="mb-1">Creacion de Etiquetas</h3>
                <p class="mb-0">Registra la informacion de seguridad de una sustancia y revisa su vista previa antes de guardarla.</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="crear-tab-card">
                <form id="formNuevaEtiqueta" class="h-100" novalidate>
                    <div class="crear-tab-nav" role="tablist" aria-label="Secciones del formulario">
                        <button type="button" class="crear-tab-btn active" data-panel="panel-informacion">
                            <i class="fa-solid fa-flask-vial"></i>Informacion
                        </button>
                        <button type="button" class="crear-tab-btn" data-panel="panel-frases-h">
                            <i class="fa-solid fa-circle-radiation"></i>Frases H
                        </button>
                        <button type="button" class="crear-tab-btn" data-panel="panel-frases-p">
                            <i class="fa-solid fa-shield-heart"></i>Frases P
                        </button>
                        <button type="button" class="crear-tab-btn" data-panel="panel-pictogramas">
                            <i class="fa-solid fa-diamond-exclamation"></i>Pictogramas
                        </button>
                        <button type="button" class="crear-tab-btn" data-panel="panel-epp">
                            <i class="fa-solid fa-helmet-safety"></i>EPP
                        </button>
                    </div>

                    <div class="crear-tab-body">
                        <section id="panel-informacion" class="crear-tab-panel active">
                            <div class="crear-section-head">
                                <h4><i class="fa-solid fa-flask-vial me-2"></i>Informacion de la sustancia</h4>
                                <p class="mb-0">Datos base para identificar y ubicar la hoja de seguridad.</p>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="nombreProducto" class="form-label">Nombre de la sustancia *</label>
                                    <input type="text" class="form-control" id="nombreProducto" placeholder="Ej. Acido clorhidrico 37%" required>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="identificadorSustancia" class="form-label">Identificador / No. CAS</label>
                                    <input type="text" class="form-control" id="identificadorSustancia" placeholder="Ej. CAS 7647-01-0">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="areaDepartamento" class="form-label">Area / Departamento *</label>
                                    <select id="areaDepartamento" class="form-select" required>
                                        <option value="">Seleccione un area</option>
                                        <option value="Laboratorio">Laboratorio</option>
                                        <option value="Produccion">Produccion</option>
                                        <option value="Mantenimiento">Mantenimiento</option>
                                        <option value="Almacen">Almacen</option>
                                        <option value="Calidad">Calidad</option>
                                        <option value="Seguridad e Higiene">Seguridad e Higiene</option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="proveedor" class="form-label">Proveedor / Fabricante</label>
                                    <input type="text" class="form-control" id="proveedor" placeholder="Ej. Quimicos Ejemplo S.A.">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="telefonoEmergencia" class="form-label">Telefono de emergencia</label>
                                    <input type="text" class="form-control" id="telefonoEmergencia" placeholder="Ej. +52 55 1234 5678">
                                </div>
                            </div>
                        </section>

                        <section id="panel-frases-h" class="crear-tab-panel">
                            <div class="crear-section-head">
                                <h4><i class="fa-solid fa-circle-radiation me-2"></i>Indicaciones de peligro (Frases H)</h4>
                                <p class="mb-0">Selecciona una o varias frases H para la hoja de seguridad.</p>
                            </div>

                            <div class="crear-search-wrap mb-3">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="buscarFraseH" class="form-control" placeholder="Buscar frase H...">
                            </div>
                            <div class="frases-container crear-frases-list" id="listaFrasesH"></div>
                        </section>

                        <section id="panel-frases-p" class="crear-tab-panel">
                            <div class="crear-section-head">
                                <h4><i class="fa-solid fa-shield-heart me-2"></i>Consejos de prudencia (Frases P)</h4>
                                <p class="mb-0">Incluye las recomendaciones de manejo, almacenamiento y respuesta.</p>
                            </div>

                            <div class="crear-search-wrap mb-3">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="buscarFraseP" class="form-control" placeholder="Buscar frase P...">
                            </div>
                            <div class="frases-container crear-frases-list" id="listaFrasesP"></div>
                        </section>

                        <section id="panel-pictogramas" class="crear-tab-panel">
                            <div class="crear-section-head">
                                <h4><i class="fa-solid fa-diamond-exclamation me-2"></i>Pictogramas y advertencia</h4>
                                <p class="mb-0">Define la palabra de advertencia y los pictogramas GHS aplicables.</p>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Palabra de advertencia *</label>
                                <div class="crear-radio-group">
                                    <label class="crear-radio-card">
                                        <input class="form-check-input" type="radio" name="palabra" value="Peligro" required>
                                        <span class="crear-radio-pill danger">Peligro</span>
                                    </label>
                                    <label class="crear-radio-card">
                                        <input class="form-check-input" type="radio" name="palabra" value="Atencion">
                                        <span class="crear-radio-pill warning">Atencion</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="form-label">Pictogramas de peligro *</label>
                                <div class="crear-pictogram-grid" id="contenedorPictogramasCheck"></div>
                            </div>
                        </section>

                        <section id="panel-epp" class="crear-tab-panel">
                            <div class="crear-section-head">
                                <h4><i class="fa-solid fa-helmet-safety me-2"></i>Equipo de proteccion personal</h4>
                                <p class="mb-0">Selecciona el EPP requerido para el manejo o uso de la sustancia.</p>
                            </div>

                            <div>
                                <label class="form-label">Equipo de proteccion personal requerido *</label>
                                <div class="crear-epp-grid" id="contenedorEppCheck"></div>
                            </div>
                        </section>
                    </div>

                    <div class="crear-actions">
                        <button type="button" id="btnLimpiarForm" class="btn btn-outline-secondary crear-secondary-btn">
                            <i class="fa-solid fa-eraser me-2"></i>Limpiar formulario
                        </button>
                        <button type="submit" class="btn crear-primary-btn">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Guardar hoja de seguridad
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <aside class="card crear-preview-card">
                <div class="card-body">
                    <div class="crear-preview-head">
                        <h4>Vista Previa de la Hoja</h4>
                        <p class="mb-0">La vista se actualiza conforme llenas el formulario.</p>
                    </div>

                    <div class="crear-preview-sheet">
                        <div class="crear-preview-title" id="previewNombreHoja">Nombre de la sustancia</div>

                        <div class="crear-preview-meta-grid">
                            <div>
                                <span class="crear-preview-meta-label">Identificador</span>
                                <strong id="previewIdentificadorHoja">No especificado</strong>
                            </div>
                            <div>
                                <span class="crear-preview-meta-label">Departamento</span>
                                <strong id="previewDepartamentoHoja">No asignado</strong>
                            </div>
                        </div>

                        <div class="crear-preview-warning-wrap">
                            <div class="crear-preview-warning" id="previewPalabraAdvertencia">Pendiente</div>
                        </div>

                        <div class="crear-preview-block">
                            <span class="crear-preview-block-title">Indicaciones de peligro</span>
                            <div id="previewFrasesHHoja" class="crear-preview-phrases muted">
                                Selecciona frases H para visualizar esta seccion.
                            </div>
                        </div>

                        <div class="crear-preview-block">
                            <span class="crear-preview-block-title">Consejos de prudencia</span>
                            <div id="previewFrasesPHoja" class="crear-preview-phrases muted">
                                Selecciona frases P para visualizar esta seccion.
                            </div>
                        </div>

                        <div class="crear-preview-block">
                            <span class="crear-preview-block-title">Pictogramas GHS</span>
                            <div id="previewPictogramasHoja" class="crear-preview-pictograms empty">
                                Selecciona los pictogramas aplicables.
                            </div>
                        </div>

                        <div class="crear-preview-footer">
                            <div class="crear-preview-footer-row">
                                <span class="crear-preview-block-title">EPP requerido</span>
                                <div id="previewEppHoja" class="crear-preview-epp-icons empty">
                                    Selecciona el equipo de proteccion personal requerido.
                                </div>
                                <p id="previewEppTexto" class="mb-0 crear-preview-epp-text">Los iconos seleccionados apareceran aqui.</p>
                            </div>
                            <div class="crear-preview-contact">
                                <p class="mb-1"><strong>Proveedor:</strong> <span id="previewProveedorHoja">No especificado</span></p>
                                <p class="mb-0"><strong>Emergencia:</strong> <span id="previewTelefonoHoja">No especificado</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
