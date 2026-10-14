$(document).ready(function () {
    const catalogMeta = {
        pictogramas: {
            title: 'Pictogramas de Seguridad',
            description: 'Administra los pictogramas visuales disponibles al crear una hoja de seguridad.',
            iconType: 'ghs',
            categoryOptions: ['Advertencia', 'Salud', 'Inflamables', 'Corrosivos', 'Ambiental']
        },
        frasesH: {
            title: 'Frases H / Indicaciones de peligro',
            description: 'Gestiona las indicaciones de peligro asociadas a sustancias y mezclas quimicas.',
            iconType: null,
            categoryOptions: ['Salud', 'Fisico', 'Ambiental', 'General']
        },
        frasesP: {
            title: 'Frases P / Consejos de prudencia',
            description: 'Configura las recomendaciones de manejo, almacenamiento y respuesta a incidentes.',
            iconType: null,
            categoryOptions: ['Prevencion', 'Respuesta', 'Almacenamiento', 'Eliminacion', 'General']
        },
        epp: {
            title: 'Equipo de Proteccion Personal',
            description: 'Define los elementos de proteccion disponibles para la captura de hojas de seguridad.',
            iconType: 'epp',
            categoryOptions: ['Proteccion ocular', 'Proteccion manual', 'Proteccion respiratoria', 'Proteccion corporal', 'General']
        }
    };

    const iconSets = {
        ghs: [
            { value: 'fa-fire-flame-curved', label: 'Inflamable' },
            { value: 'fa-flask-vial', label: 'Corrosivo' },
            { value: 'fa-skull-crossbones', label: 'Toxico' },
            { value: 'fa-exclamation', label: 'Irritante' },
            { value: 'fa-user-shield', label: 'Peligro salud' },
            { value: 'fa-tree', label: 'Ambiental' }
        ],
        epp: [
            { value: 'fa-glasses', label: 'Lentes' },
            { value: 'fa-hand', label: 'Guantes' },
            { value: 'fa-user-shield', label: 'Careta' },
            { value: 'fa-shirt', label: 'Bata' },
            { value: 'fa-head-side-mask', label: 'Respirador' },
            { value: 'fa-helmet-safety', label: 'Casco' }
        ]
    };

    const defaultCatalogs = {
        pictogramas: [
            { id: 'pic-1', name: 'Inflamable', code: 'PIC001', description: 'Sustancia que puede incendiarse con facilidad.', category: 'Inflamables', icon: 'fa-fire-flame-curved' },
            { id: 'pic-2', name: 'Corrosivo', code: 'PIC002', description: 'Puede provocar quemaduras y deterioro de materiales.', category: 'Corrosivos', icon: 'fa-flask-vial' },
            { id: 'pic-3', name: 'Toxico', code: 'PIC003', description: 'Riesgo de toxicidad aguda por exposicion o ingestion.', category: 'Salud', icon: 'fa-skull-crossbones' },
            { id: 'pic-4', name: 'Irritante', code: 'PIC004', description: 'Puede causar irritacion en piel, ojos o vias respiratorias.', category: 'Advertencia', icon: 'fa-exclamation' }
        ],
        frasesH: [
            { id: 'h-1', name: 'H225', code: 'H225', description: 'Liquido y vapores muy inflamables.', category: 'Fisico' },
            { id: 'h-2', name: 'H302', code: 'H302', description: 'Nocivo en caso de ingestion.', category: 'Salud' },
            { id: 'h-3', name: 'H314', code: 'H314', description: 'Provoca quemaduras graves en la piel y lesiones oculares graves.', category: 'Salud' },
            { id: 'h-4', name: 'H411', code: 'H411', description: 'Toxico para los organismos acuaticos, con efectos nocivos duraderos.', category: 'Ambiental' }
        ],
        frasesP: [
            { id: 'p-1', name: 'P210', code: 'P210', description: 'Mantener alejado de fuentes de calor, chispas y llama abierta.', category: 'Prevencion' },
            { id: 'p-2', name: 'P233', code: 'P233', description: 'Mantener el recipiente hermeticamente cerrado.', category: 'Almacenamiento' },
            { id: 'p-3', name: 'P280', code: 'P280', description: 'Usar guantes, gafas y equipo de proteccion adecuado.', category: 'Prevencion' },
            { id: 'p-4', name: 'P305+P351+P338', code: 'P305+P351+P338', description: 'En caso de contacto con los ojos: enjuagar con agua varios minutos.', category: 'Respuesta' }
        ],
        epp: [
            { id: 'epp-1', name: 'Lentes de seguridad', code: 'EPP001', description: 'Proteccion ocular contra salpicaduras.', category: 'Proteccion ocular', icon: 'fa-glasses' },
            { id: 'epp-2', name: 'Guantes de proteccion', code: 'EPP002', description: 'Proteccion manual para contacto quimico.', category: 'Proteccion manual', icon: 'fa-hand' },
            { id: 'epp-3', name: 'Respirador', code: 'EPP003', description: 'Proteccion para vapores o particulas peligrosas.', category: 'Proteccion respiratoria', icon: 'fa-head-side-mask' },
            { id: 'epp-4', name: 'Bata o mandil', code: 'EPP004', description: 'Proteccion corporal para manejo de sustancias.', category: 'Proteccion corporal', icon: 'fa-shirt' }
        ]
    };

    let activeCatalog = 'pictogramas';
    let searchQuery = '';
    let selectedIcon = '';
    let catalogsState = JSON.parse(JSON.stringify(defaultCatalogs));

    const modalElement = document.getElementById('catalogoModal');
    const catalogModal = modalElement ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;

    function slugify(value) {
        return String(value || '')
            .toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^a-z0-9-]/g, '')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    }

    function getIconHtml(iconClass, type) {
        if (!iconClass) {
            return '<i class="fa-solid fa-icons"></i>';
        }

        if (type === 'ghs') {
            return `
                <span class="ghs-pictogram catalogos-ghs-mini">
                    <span class="ghs-pictogram-inner">
                        <i class="fa-solid ${iconClass}"></i>
                    </span>
                </span>
            `;
        }

        return `
            <span class="catalogos-epp-mini">
                <i class="fa-solid ${iconClass}"></i>
            </span>
        `;
    }

    function updateHeader() {
        const meta = catalogMeta[activeCatalog];
        $('#catalogoTitulo').text(meta.title);
        $('#catalogoDescripcion').text(meta.description);
    }

    function populateCategoryOptions() {
        const meta = catalogMeta[activeCatalog];
        const $select = $('#catalogoCategoria').empty();

        meta.categoryOptions.forEach(function (option) {
            $select.append(`<option value="${option}">${option}</option>`);
        });
    }

    function renderIconPicker() {
        const meta = catalogMeta[activeCatalog];
        const icons = meta.iconType ? iconSets[meta.iconType] : [];
        const $wrap = $('#catalogoIconPickerWrap');
        const $field = $('#catalogoIconWrap');
        const $picker = $('#catalogoIconPicker').empty();

        if (!meta.iconType) {
            $wrap.addClass('d-none');
            $field.addClass('d-none');
            return;
        }

        $wrap.removeClass('d-none');
        $field.removeClass('d-none');

        icons.forEach(function (item) {
            $picker.append(`
                <button type="button" class="catalogos-icon-option ${selectedIcon === item.value ? 'active' : ''}" data-icon="${item.value}" data-label="${item.label}">
                    ${getIconHtml(item.value, meta.iconType)}
                    <span>${item.label}</span>
                </button>
            `);
        });

        updateIconPreview();
    }

    function updateIconPreview() {
        const meta = catalogMeta[activeCatalog];
        $('#catalogoIconPreview').html(getIconHtml(selectedIcon, meta.iconType));
    }

    function getFilteredItems() {
        const items = catalogsState[activeCatalog] || [];
        const query = searchQuery.trim().toLowerCase();

        if (!query) {
            return items;
        }

        return items.filter(function (item) {
            return [item.name, item.code, item.description, item.category]
                .join(' ')
                .toLowerCase()
                .indexOf(query) > -1;
        });
    }

    function renderTable() {
        const meta = catalogMeta[activeCatalog];
        const items = getFilteredItems();
        const $body = $('#catalogoTablaBody').empty();

        $('#catalogoCounter').text(`${items.length} registro${items.length === 1 ? '' : 's'}`);

        if (!items.length) {
            $('#catalogoEmpty').removeClass('d-none');
            return;
        }

        $('#catalogoEmpty').addClass('d-none');

        items.forEach(function (item) {
            $body.append(`
                <tr>
                    <td>
                        <div class="catalogos-cell-visual">
                            ${meta.iconType ? getIconHtml(item.icon, meta.iconType) : '<span class="catalogos-code-badge">' + item.code + '</span>'}
                        </div>
                    </td>
                    <td>
                        <div class="catalogos-main-cell">
                            <strong>${item.name}</strong>
                            <span>${item.code}</span>
                        </div>
                    </td>
                    <td>${item.description}</td>
                    <td><span class="catalogos-category-pill">${item.category || 'General'}</span></td>
                    <td>
                        <div class="catalogos-action-group">
                            <button type="button" class="btn btn-link p-0 catalogos-action-btn edit" data-id="${item.id}" title="Editar">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button type="button" class="btn btn-link p-0 catalogos-action-btn delete" data-id="${item.id}" title="Eliminar">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `);
        });
    }

    function openModal(mode, itemId) {
        const meta = catalogMeta[activeCatalog];
        const items = catalogsState[activeCatalog];
        const item = items.find(function (entry) {
            return entry.id === itemId;
        });

        $('#catalogoForm')[0].reset();
        $('#catalogoItemId').val('');
        selectedIcon = '';
        populateCategoryOptions();

        if (mode === 'edit' && item) {
            $('#catalogoModalTitle').text(`Editar ${meta.title.slice(0, -1) || 'elemento'}`);
            $('#catalogoModalSubtitle').text('Actualiza la informacion del elemento seleccionado.');
            $('#catalogoItemId').val(item.id);
            $('#catalogoNombre').val(item.name);
            $('#catalogoClave').val(item.code);
            $('#catalogoDescripcionInput').val(item.description);
            $('#catalogoCategoria').val(item.category || meta.categoryOptions[0]);
            selectedIcon = item.icon || '';
        } else {
            $('#catalogoModalTitle').text(`Agregar ${meta.title.slice(0, -1) || 'elemento'}`);
            $('#catalogoModalSubtitle').text('Completa la informacion del nuevo elemento del catalogo.');
            $('#catalogoCategoria').val(meta.categoryOptions[0]);
            if (meta.iconType) {
                selectedIcon = iconSets[meta.iconType][0].value;
            }
        }

        renderIconPicker();
        if (catalogModal) {
            catalogModal.show();
        }
    }

    function removeItem(itemId) {
        catalogsState[activeCatalog] = catalogsState[activeCatalog].filter(function (item) {
            return item.id !== itemId;
        });
        renderTable();
    }

    function saveItem() {
        const id = $('#catalogoItemId').val();
        const name = $('#catalogoNombre').val().trim();
        const code = $('#catalogoClave').val().trim();
        const description = $('#catalogoDescripcionInput').val().trim();
        const category = $('#catalogoCategoria').val();
        const meta = catalogMeta[activeCatalog];

        if (!name || !code || !description) {
            alert('Completa los campos obligatorios: nombre, clave y descripcion.');
            return;
        }

        if (meta.iconType && !selectedIcon) {
            alert('Selecciona un icono para este elemento del catalogo.');
            return;
        }

        const payload = {
            id: id || `${activeCatalog}-${slugify(code)}-${Date.now()}`,
            name: name,
            code: code,
            description: description,
            category: category,
            icon: meta.iconType ? selectedIcon : ''
        };

        const existingIndex = catalogsState[activeCatalog].findIndex(function (item) {
            return item.id === payload.id;
        });

        if (existingIndex > -1) {
            catalogsState[activeCatalog][existingIndex] = payload;
        } else {
            catalogsState[activeCatalog].push(payload);
        }

        renderTable();
        if (catalogModal) {
            catalogModal.hide();
        }
    }

    function switchTab(tab) {
        activeCatalog = tab;
        searchQuery = '';
        $('#catalogoBuscar').val('');

        $('.crear-tab-btn').removeClass('active');
        $(`.crear-tab-btn[data-catalog-tab="${tab}"]`).addClass('active');

        updateHeader();
        renderTable();
    }

    updateHeader();
    renderTable();

    $(document).on('click', '.crear-tab-btn[data-catalog-tab]', function () {
        switchTab($(this).data('catalog-tab'));
    });

    $('#catalogoBuscar').on('input', function () {
        searchQuery = $(this).val();
        renderTable();
    });

    $('#btnNuevoCatalogo').on('click', function () {
        openModal('create');
    });

    $(document).on('click', '.catalogos-action-btn.edit', function () {
        openModal('edit', $(this).data('id'));
    });

    $(document).on('click', '.catalogos-action-btn.delete', function () {
        const itemId = $(this).data('id');
        removeItem(itemId);
    });

    $(document).on('click', '.catalogos-icon-option', function () {
        selectedIcon = $(this).data('icon');
        $('.catalogos-icon-option').removeClass('active');
        $(this).addClass('active');
        updateIconPreview();
    });

    $('#catalogoForm').on('submit', function (e) {
        e.preventDefault();
        saveItem();
    });
});
