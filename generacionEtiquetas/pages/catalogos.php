<div class="container-fluid px-3 px-lg-4 pb-4 catalogos-page">
    <div class="impresion-header-card mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="impresion-header-icon">
                <i class="fa-solid fa-database"></i>
            </div>
            <div>
                <h3 class="mb-1">Administrar Catalogos</h3>
                <p class="mb-0">Gestiona pictogramas, frases H, frases P y equipos de proteccion personal para las hojas de seguridad.</p>
            </div>
        </div>
    </div>

    <div class="catalogos-shell">
        <div class="crear-tab-nav catalogos-tab-nav" role="tablist" aria-label="Catalogos disponibles">
            <button type="button" class="crear-tab-btn active" data-catalog-tab="pictogramas">
                <i class="fa-solid fa-diamond-exclamation"></i>Pictogramas
            </button>
            <button type="button" class="crear-tab-btn" data-catalog-tab="frasesH">
                <i class="fa-solid fa-circle-radiation"></i>Frases H
            </button>
            <button type="button" class="crear-tab-btn" data-catalog-tab="frasesP">
                <i class="fa-solid fa-shield-heart"></i>Frases P
            </button>
            <button type="button" class="crear-tab-btn" data-catalog-tab="epp">
                <i class="fa-solid fa-helmet-safety"></i>EPP
            </button>
        </div>

        <div class="catalogos-toolbar">
            <div class="crear-search-wrap catalogos-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="catalogoBuscar" class="form-control" placeholder="Buscar en el catalogo activo...">
            </div>

            <!-- <button type="button" class="btn catalogos-primary-btn"> -->
            <div class="catalogos-toolbar-actions"> 
                <button type="button" class="btn btn-warning" id="btnNuevoCatalogo">
                    <i class="fa-solid fa-plus me-2"></i> <b>Agregar elemento</b>
                </button>
            </div>
        </div>

        <div class="catalogos-panel card">
            <div class="card-body p-0">
                <div class="catalogos-panel-head">
                    <div>
                        <h4 id="catalogoTitulo" class="mb-1">Pictogramas de Seguridad</h4>
                        <p id="catalogoDescripcion" class="mb-0">Administra los pictogramas visuales disponibles al crear una hoja de seguridad.</p>
                    </div>
                    <span class="catalogos-counter" id="catalogoCounter">0 registros</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 catalogos-table">
                        <thead>
                            <tr>
                                <th style="width: 90px;">Vista</th>
                                <th>Nombre / Clave</th>
                                <th>Descripcion</th>
                                <th style="width: 150px;">Categoria</th>
                                <th style="width: 140px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="catalogoTablaBody"></tbody>
                    </table>
                </div>

                <div class="catalogos-empty d-none" id="catalogoEmpty">
                    <i class="fa-solid fa-folder-open"></i>
                    <h5>Sin coincidencias</h5>
                    <p class="mb-0">No hay elementos en este catalogo con el filtro actual.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="catalogoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content catalogos-modal-content">
                <div class="modal-header catalogos-modal-header">
                    <div>
                        <h5 class="modal-title" id="catalogoModalTitle">Agregar elemento</h5>
                        <p class="mb-0" id="catalogoModalSubtitle">Completa la informacion del elemento seleccionado.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form id="catalogoForm" novalidate>
                    <div class="modal-body">
                        <input type="hidden" id="catalogoItemId">

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="catalogoNombre" class="form-label">Nombre *</label>
                                <input type="text" id="catalogoNombre" class="form-control" placeholder="Nombre del elemento" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="catalogoClave" class="form-label">Clave *</label>
                                <input type="text" id="catalogoClave" class="form-control" placeholder="Ej. H314 / P210 / PIC01" required>
                            </div>

                            <div class="col-12">
                                <label for="catalogoDescripcionInput" class="form-label">Descripcion *</label>
                                <textarea id="catalogoDescripcionInput" class="form-control" rows="3" placeholder="Descripcion del elemento" required></textarea>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="catalogoCategoria" class="form-label">Categoria</label>
                                <select id="catalogoCategoria" class="form-select">
                                    <option value="General">General</option>
                                    <option value="Salud">Salud</option>
                                    <option value="Almacenamiento">Almacenamiento</option>
                                    <option value="Advertencia">Advertencia</option>
                                    <option value="Proteccion">Proteccion</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6 catalogos-icon-field d-none" id="catalogoIconWrap">
                                <label class="form-label">Icono / Vista</label>
                                <div class="catalogos-icon-preview" id="catalogoIconPreview">
                                    <i class="fa-solid fa-icons"></i>
                                </div>
                            </div>
                        </div>

                        <div class="catalogos-icon-picker-wrap d-none" id="catalogoIconPickerWrap">
                            <div class="catalogos-picker-head">
                                <h6>Selecciona un icono</h6>
                                <p class="mb-0">Aplica para pictogramas y elementos de EPP.</p>
                            </div>
                            <div class="catalogos-icon-picker" id="catalogoIconPicker"></div>
                        </div>
                    </div>

                    <div class="modal-footer catalogos-modal-footer">
                        <button type="button" class="btn btn-outline-secondary crear-secondary-btn" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn catalogos-primary-btn">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
