<!--Menu Certificaciones -->
<div id="certifications" class="content-section">

    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3" data-bs-toggle="modal" data-bs-target="#newCertificationModal">
            <div class="card-option">
                <i class="bi bi-journal-check card-icon"></i>
                <div class="card-label">Registrar operador certificado</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card-option">
                <i class="bi bi-people card-icon"></i>
                <div class="card-label">Consultar operadores certificados</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card-option">
                <i class="bi bi-calendar3 card-icon"></i>
                <div class="card-label">Certificaciones próximas de vencimiento</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card-option">
                <i class="bi bi-play-circle card-icon"></i>
                <div class="card-label">Realizar prueba</div>
            </div>
        </div>
    </div>

    <div class="table-container mt-4">
        <div class="table-header">
            <h5 class="table-title">Certificaciones Registradas</h5>
            <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                <i class="bi bi-plus-circle"></i> Agregar Certificación
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Certificaciones</th>
                        <th>Proceso</th>
                        <th>Línea</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>CERT-001</td>
                        <td>Proceso X</td>
                        <td>Desforre</td>
                        <td>Línea A</td>
                        <td><span class="badge badge-custom status-active">Activa</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">Modificar</button>
                            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                        </td>
                    </tr>
                    <tr>
                        <td>CERT-002</td>
                        <td>Proceso Y</td>
                        <td>Encintado</td>
                        <td>Línea B</td>
                        <td><span class="badge badge-custom status-active">Activa</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">Modificar</button>
                            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                        </td>
                    </tr>
                    <tr>
                        <td>CERT-003</td>
                        <td>Proceso Z</td>
                        <td>Crimpado</td>
                        <td>Línea C</td>
                        <td><span class="badge badge-custom status-pending">Pendiente</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">Modificar</button>
                            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                        </td>
                    </tr>
                    <tr>
                        <td>CERT-004</td>
                        <td>Proceso W</td>
                        <td>Soldadura</td>
                        <td>Línea A</td>
                        <td><span class="badge badge-custom status-expired">Vencida</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">Modificar</button>
                            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!--Fin Menu Certificaciones-->



<!--Seccion de modales  -->
    <!-- Modal agregar certificacion-->
        <div class="modal fade" id="modalAgregar" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Nueva Certificación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="certificationCode" class="form-label">Código de Certificación</label>
                            <input type="text" class="form-control" id="certificationCode" placeholder="Ej: CERT-005">
                        </div>
                        <div class="mb-3">
                            <label for="certificationName" class="form-label">Nombre de Certificación</label>
                            <input type="text" class="form-control" id="certificationName" placeholder="Ej: Proceso X">
                        </div>
                        <div class="mb-3">
                            <label for="processType" class="form-label">Tipo de Proceso</label>
                            <select class="form-select" id="processType">
                            <option selected>Seleccionar proceso</option>
                            <option>Ensamblaje</option>
                            <option>Control de Calidad</option>
                            <option>Embalaje</option>
                            <option>Soldadura</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="lineAssignment" class="form-label">Línea Asignada</label>
                            <select class="form-select" id="lineAssignment">
                            <option selected>Seleccionar línea</option>
                            <option>Línea A</option>
                            <option>Línea B</option>
                            <option>Línea C</option>
                            <option>Línea D</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary-custom">Guardar Certificación</button>
                </div>
                </div>
            </div>
        </div>

    <!-- Modal registrar operador Certificado -->
        <div class="modal fade" id="newCertificationModal" tabindex="-1" aria-labelledby="newCertificationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newCertificationModalLabel">Nueva Certificación / Actualización ILU</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="mb-3">
                                <label for="operatorSelect" class="form-label">Operador</label>
                                <select class="form-select" id="operatorSelect">
                                    <option selected>Seleccionar operador</option>
                                    <option>Juan Pérez</option>
                                    <option>María González</option>
                                    <option>Carlos Ruiz</option>
                                    <option>Ana López</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="processSelect" class="form-label">Proceso</label>
                                <select class="form-select" id="processSelect">
                                    <option selected>Seleccionar proceso</option>
                                    <option>Proceso X</option>
                                    <option>Proceso Y</option>
                                    <option>Proceso Z</option>
                                    <option>Proceso W</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nivel ILU</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="iluLevel" id="iluI" value="I">
                                        <label class="form-check-label" for="iluI">I (No conoce)</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="iluLevel" id="iluU" value="U">
                                        <label class="form-check-label" for="iluU">U (Conoce)</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="iluLevel" id="iluL" value="L">
                                        <label class="form-check-label" for="iluL">L (Capacita)</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="certDate" class="form-label">Fecha certificación</label>
                                    <input type="date" class="form-control" id="certDate">
                                </div>
                                <div class="col-md-6">
                                    <label for="expiryDate" class="form-label">Fecha vencimiento</label>
                                    <input type="date" class="form-control" id="expiryDate">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="score" class="form-label">Calificación</label>
                                <input type="number" class="form-control" id="score" min="0" max="100" placeholder="85/100">
                            </div>
                            <div class="mb-3">
                                <label for="observations" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observations" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="certFile" class="form-label">Documentos</label>
                                <input class="form-control" type="file" id="certFile">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary-custom">Guardar Certificación</button>
                    </div>
                </div>
            </div>
        </div>
<!--Fin seccion de modales -->