<div class="container-fluid px-3 px-lg-4 pb-4 impresion-page">
    <div class="impresion-header-card mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="impresion-header-icon">
                <i class="fa-solid fa-print"></i>
            </div>
            <div>
                <h3 class="mb-1">Consulta / Impresion de Etiquetas</h3>
                <p class="mb-0">Consulta, filtra e imprime etiquetas registradas en el sistema.</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-9">
            <div class="card impresion-filter-card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="impresion-filter-title">Filtro de avanzadas</span>
                        <span class="impresion-filter-badge"><i class="fa-solid fa-magnifying-glass"></i></span>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-6 col-xl-2">
                            <label for="filtroSustancia" class="form-label">Sustancia:</label>
                            <select id="filtroSustancia" class="form-select">
                                <option value="">Todas</option>
                                <option value="Methanol">Methanol</option>
                                <option value="Acido Sulfurico">Acido Sulfurico</option>
                                <option value="Cloro">Cloro</option>
                                <option value="Tolueno">Tolueno</option>
                                <option value="Acetona">Acetona</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-2">
                            <label for="filtroDepartamento" class="form-label">Departamento:</label>
                            <select id="filtroDepartamento" class="form-select">
                                <option value="">Todos</option>
                                <option value="Laboratorio">Laboratorio</option>
                                <option value="Produccion">Produccion</option>
                                <option value="Mantenimiento">Mantenimiento</option>
                                <option value="Almacen">Almacen</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-3 col-xl-2">
                            <label for="filtroFechaDesde" class="form-label">Fecha:</label>
                            <input type="date" id="filtroFechaDesde" class="form-control" value="2022-01-01">
                        </div>

                        <div class="col-6 col-md-3 col-xl-2">
                            <label for="filtroFechaHasta" class="form-label">&nbsp;</label>
                            <input type="date" id="filtroFechaHasta" class="form-control" value="2022-12-31">
                        </div>

                        <div class="col-12 col-md-6 col-xl-2">
                            <label for="filtroRegistrado" class="form-label">Registrado por:</label>
                            <select id="filtroRegistrado" class="form-select">
                                <option value="">Todos</option>
                                <option value="Admin">Admin</option>
                                <option value="J. Perez">J. Perez</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-2">
                            <div class="d-grid">
                                <button type="button" id="btnBuscarEtiquetas" class="btn impresion-search-btn">Buscar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
            <div class="card impresion-table-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaConsultaEtiquetas" class="table table-hover align-middle mb-0 impresion-table">
                            <thead>
                                <tr>
                                    <th>Sustancia</th>
                                    <th>Identificador</th>
                                    <th>Departamento</th>
                                    <th>Registrado por</th>
                                    <th>Fecha de Actualizacion</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="etiqueta-row is-selected"
                                    data-nombre="Methanol"
                                    data-identificador="CAS: 67-56-1"
                                    data-palabra="Peligro"
                                    data-departamento="Laboratorio"
                                    data-registrado="Admin"
                                    data-fecha="15/12/2022"
                                    data-fecha-iso="2022-12-15"
                                    data-observaciones="Manipular con precaucion."
                                    data-empresa="Quimica Industrial S.A."
                                    data-direccion="Calle 123, Ciudad, Pais"
                                    data-telefono="+52 55 1234 5678"
                                    data-email="contacto@quimicaindustrial.com"
                                    data-frases-h="H225::Liquido y vapores muy inflamables|H301::Toxico si se ingiere"
                                    data-frases-p="P210::Mantener alejado del calor|P280::Usar guantes de proteccion"
                                    data-pictogramas="flame|exclamation|skull|health">
                                    <td class="fw-semibold">Methanol</td>
                                    <td>CAS: 67-56-1</td>
                                    <td>Laboratorio</td>
                                    <td>Admin</td>
                                    <td data-order="2022-12-15">15/12/2022</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-link p-0 table-action-btn btn-preview-etiqueta" title="Ver detalle">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-link p-0 table-action-btn btn-print-etiqueta" title="Imprimir">
                                                <i class="fa-solid fa-print"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="etiqueta-row"
                                    data-nombre="Acido Sulfurico"
                                    data-identificador="CAS: 7664-93-9"
                                    data-palabra="Peligro"
                                    data-departamento="Produccion"
                                    data-registrado="J. Perez"
                                    data-fecha="10/12/2022"
                                    data-fecha-iso="2022-12-10"
                                    data-observaciones="Requiere almacenamiento ventilado."
                                    data-empresa="Quimica Industrial S.A."
                                    data-direccion="Calle 123, Ciudad, Pais"
                                    data-telefono="+56 9 1234 5678"
                                    data-email="info@quimica.com"
                                    data-frases-h="H290::Puede ser corrosivo para los metales|H314::Provoca quemaduras graves en la piel"
                                    data-frases-p="P234::Conservar unicamente en el recipiente original|P280::Usar proteccion ocular"
                                    data-pictogramas="corrosion|exclamation|health">
                                    <td class="fw-semibold">Acido Sulfurico</td>
                                    <td>CAS: 7664-93-9</td>
                                    <td>Produccion</td>
                                    <td>J. Perez</td>
                                    <td data-order="2022-12-10">10/12/2022</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-link p-0 table-action-btn btn-preview-etiqueta" title="Ver detalle">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-link p-0 table-action-btn btn-print-etiqueta" title="Imprimir">
                                                <i class="fa-solid fa-print"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="etiqueta-row"
                                    data-nombre="Cloro"
                                    data-identificador="CAS: 7782-50-5"
                                    data-palabra="Peligro"
                                    data-departamento="Mantenimiento"
                                    data-registrado="Admin"
                                    data-fecha="08/12/2022"
                                    data-fecha-iso="2022-12-08"
                                    data-observaciones="Evitar contacto con materiales organicos."
                                    data-empresa="Servicios Tecnicos del Norte"
                                    data-direccion="Av. Central 88, Monterrey, Mexico"
                                    data-telefono="+52 81 4444 2000"
                                    data-email="seguridad@stn.mx"
                                    data-frases-h="H270::Puede provocar o agravar un incendio|H331::Toxico si se inhala"
                                    data-frases-p="P220::Mantener alejado de materiales combustibles|P304::Trasladar al aire libre"
                                    data-pictogramas="flame-circle|skull|health">
                                    <td class="fw-semibold">Cloro</td>
                                    <td>CAS: 7782-50-5</td>
                                    <td>Mantenimiento</td>
                                    <td>Admin</td>
                                    <td data-order="2022-12-08">08/12/2022</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-link p-0 table-action-btn btn-preview-etiqueta" title="Ver detalle">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-link p-0 table-action-btn btn-print-etiqueta" title="Imprimir">
                                                <i class="fa-solid fa-print"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="etiqueta-row"
                                    data-nombre="Tolueno"
                                    data-identificador="CAS: 108-88-3"
                                    data-palabra="Peligro"
                                    data-departamento="Almacen"
                                    data-registrado="J. Perez"
                                    data-fecha="02/12/2022"
                                    data-fecha-iso="2022-12-02"
                                    data-observaciones="Mantener el recipiente bien cerrado."
                                    data-empresa="Solventes del Centro"
                                    data-direccion="Parque Industrial 45, Queretaro, Mexico"
                                    data-telefono="+52 442 220 1098"
                                    data-email="ventas@solventescentro.mx"
                                    data-frases-h="H225::Liquido y vapores muy inflamables|H315::Provoca irritacion cutanea"
                                    data-frases-p="P210::Mantener alejado del calor|P302::Lavar con abundante agua"
                                    data-pictogramas="flame|exclamation|health">
                                    <td class="fw-semibold">Tolueno</td>
                                    <td>CAS: 108-88-3</td>
                                    <td>Almacen</td>
                                    <td>J. Perez</td>
                                    <td data-order="2022-12-02">02/12/2022</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-link p-0 table-action-btn btn-preview-etiqueta" title="Ver detalle">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-link p-0 table-action-btn btn-print-etiqueta" title="Imprimir">
                                                <i class="fa-solid fa-print"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="etiqueta-row"
                                    data-nombre="Acetona"
                                    data-identificador="CAS: 67-64-1"
                                    data-palabra="Atencion"
                                    data-departamento="Laboratorio"
                                    data-registrado="Admin"
                                    data-fecha="25/11/2022"
                                    data-fecha-iso="2022-11-25"
                                    data-observaciones="Almacenar en lugar fresco y ventilado."
                                    data-empresa="Reactivos del Bajio"
                                    data-direccion="Boulevard Ciencia 210, Leon, Mexico"
                                    data-telefono="+52 477 600 7788"
                                    data-email="contacto@reactivosbajio.mx"
                                    data-frases-h="H225::Liquido y vapores muy inflamables|H319::Provoca irritacion ocular grave"
                                    data-frases-p="P233::Mantener el recipiente hermeticamente cerrado|P305::Enjuagar con agua varios minutos"
                                    data-pictogramas="flame|exclamation">
                                    <td class="fw-semibold">Acetona</td>
                                    <td>CAS: 67-64-1</td>
                                    <td>Laboratorio</td>
                                    <td>Admin</td>
                                    <td data-order="2022-11-25">25/11/2022</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-link p-0 table-action-btn btn-preview-etiqueta" title="Ver detalle">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-link p-0 table-action-btn btn-print-etiqueta" title="Imprimir">
                                                <i class="fa-solid fa-print"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-3">
            <aside class="card impresion-preview-card" id="seccion-impresion">
                <div class="card-body">
                    <h4 id="previewNombre" class="impresion-preview-name">Methanol</h4>

                    <div class="impresion-preview-section">
                        <p class="mb-0">
                            <span class="impresion-preview-label">Identificador:</span>
                            <span id="previewIdentificador" class="fw-semibold">CAS: 67-56-1</span>
                        </p>
                    </div>

                    <div class="impresion-preview-section">
                        <h6>Frases H:</h6>
                        <div id="previewFrasesH" class="d-flex flex-wrap gap-2"></div>
                    </div>

                    <div class="impresion-preview-section">
                        <h6>Frases P:</h6>
                        <div id="previewFrasesP" class="d-flex flex-wrap gap-2"></div>
                    </div>

                    <div class="impresion-preview-section">
                        <h6>Pictogramas GHS:</h6>
                        <div id="previewPictogramas" class="impresion-ghs-grid"></div>
                    </div>

                    <div class="impresion-preview-meta">
                        <p><span class="impresion-preview-label">Departamento:</span> <span id="previewDepartamento">Laboratorio</span></p>
                        <p><span class="impresion-preview-label">Registrado por:</span> <span id="previewRegistrado">Admin</span></p>
                        <p><span class="impresion-preview-label">Fecha de Actualizacion:</span> <span id="previewFecha">15/12/2022</span></p>
                        <p class="mb-0"><span class="impresion-preview-label">Observaciones:</span> <span id="previewObservaciones">Manipular con precaucion.</span></p>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <div class="modal fade" id="modalVistaEtiqueta" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content impresion-modal-content">
                <div class="modal-header impresion-modal-header">
                    <h5 class="modal-title">Detalles de la Etiqueta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-3 p-lg-4">
                    <div class="impresion-modal-sheet">
                        <div class="impresion-modal-sheet-inner">
                            <div class="impresion-modal-top">
                                <h2 id="modalEtiquetaNombre" class="impresion-modal-name">METHANOL</h2>
                                <div class="impresion-modal-topline"></div>
                                <div class="row g-4 align-items-center">
                                    <div class="col-12 col-lg-8">
                                        <div id="modalEtiquetaPictogramas" class="impresion-modal-pictogramas"></div>
                                    </div>
                                    <div class="col-12 col-lg-4">
                                        <div id="modalEtiquetaPalabra" class="impresion-modal-warning">PELIGRO</div>
                                    </div>
                                </div>
                            </div>

                            <div class="impresion-modal-block">
                                <div id="modalEtiquetaFrasesH" class="impresion-modal-phrases"></div>
                                <div id="modalEtiquetaFrasesP" class="impresion-modal-phrases mt-3"></div>
                            </div>

                            <div class="impresion-modal-block impresion-modal-contacto">
                                <p id="modalEtiquetaEmpresa" class="mb-2 fw-bold">Quimica Industrial S.A.</p>
                                <p class="mb-1" id="modalEtiquetaDireccion">Calle 123, Ciudad, Pais</p>
                                <p class="mb-1">Tel: <span id="modalEtiquetaTelefono">+52 55 1234 5678</span></p>
                                <p class="mb-0">Email: <span id="modalEtiquetaEmail">contacto@quimicaindustrial.com</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="impresion-modal-meta-card">
                                <span class="impresion-modal-meta-label">Identificador</span>
                                <strong id="modalMetaIdentificador">CAS: 67-56-1</strong>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="impresion-modal-meta-card">
                                <span class="impresion-modal-meta-label">Departamento</span>
                                <strong id="modalMetaDepartamento">Laboratorio</strong>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="impresion-modal-meta-card">
                                <span class="impresion-modal-meta-label">Registrado por</span>
                                <strong id="modalMetaRegistrado">Admin</strong>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="impresion-modal-meta-card">
                                <span class="impresion-modal-meta-label">Actualizacion</span>
                                <strong id="modalMetaFecha">15/12/2022</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="impresion-modal-meta-card">
                                <span class="impresion-modal-meta-label">Observaciones</span>
                                <strong id="modalMetaObservaciones">Manipular con precaucion.</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
