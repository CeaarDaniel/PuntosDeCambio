/**
 * assets/js/roles.js — Catálogo de roles y asignación de permisos (pages/roles.php).
 *
 * Habla con api/operacionesRoles.php:
 *   1 listar · 2 detalle · 3 crear · 4 modificar · 5 activar/desactivar ·
 *   6 eliminar · 7 lista de checks · 8 guardar permisos
 *
 * Un rol se identifica por (codigoSistema, codigoRol) y cada permiso por
 * 'codigoModulo.accion'; no hay IDs en el navegador. El servidor vuelve a
 * comprobar que cada permiso pertenezca a la aplicación del rol.
 */
(function () {
    'use strict';

    var URL_API = 'api/operacionesRoles.php';
    var ui = App.ui;

    var $tabla = $('#tablaRoles');
    var $filtro = $('#filtroAplicacion');
    var tabla = null;
    var roles = [];
    var aplicaciones = [];

    function buscar(sistema, codigo) {
        for (var i = 0; i < roles.length; i++) {
            if (roles[i].codigoSistema === sistema && roles[i].codigoRol === codigo) {
                return roles[i];
            }
        }
        return null;
    }

    function claveRol(r) {
        return r.codigoSistema + '.' + r.codigoRol;
    }

    /* ==================================================================
       Tabla
       ================================================================== */
    function htmlAcciones(r) {
        var claves = 'data-sistema="' + App.escaparHtml(r.codigoSistema) + '" data-rol="' + App.escaparHtml(r.codigoRol) + '"';
        var html = '<div class="acciones-fila">' +
            '<button type="button" class="btn btn-outline-primary btn-icon js-detalle" ' + claves +
                ' title="Ver usuarios y permisos" aria-label="Ver usuarios y permisos"><i class="bi bi-eye" aria-hidden="true"></i></button>' +
            '<button type="button" class="btn btn-primary btn-icon js-permisos" ' + claves +
                ' title="Asignar permisos" aria-label="Asignar permisos"><i class="bi bi-ui-checks" aria-hidden="true"></i></button>' +
            '<button type="button" class="btn btn-soft btn-icon js-editar" ' + claves +
                ' title="Editar" aria-label="Editar"><i class="bi bi-pencil" aria-hidden="true"></i></button>' +
            (r.activo
                ? '<button type="button" class="btn btn-soft btn-icon text-danger js-activo" ' + claves +
                    ' data-activo="0" title="Desactivar" aria-label="Desactivar"><i class="bi bi-toggle-on" aria-hidden="true"></i></button>'
                : '<button type="button" class="btn btn-soft btn-icon text-success js-activo" ' + claves +
                    ' data-activo="1" title="Activar" aria-label="Activar"><i class="bi bi-toggle-off" aria-hidden="true"></i></button>');

        if (r.usuarios === 0 && r.permisos === 0) {
            html += '<button type="button" class="btn btn-soft btn-icon text-danger js-eliminar" ' + claves +
                ' title="Eliminar" aria-label="Eliminar"><i class="bi bi-trash" aria-hidden="true"></i></button>';
        }

        return html + '</div>';
    }

    function inicializarTabla() {
        tabla = $tabla.DataTable({
            data: [],
            language: App.TEXTOS_TABLA,
            responsive: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
            order: [[0, 'asc'], [1, 'asc']],
            columnDefs: [
                { responsivePriority: 1, targets: 1 },
                { responsivePriority: 2, targets: 5 },
                { responsivePriority: 3, targets: 0 }
            ],
            columns: [
                {
                    data: 'codigoSistema',
                    width: '10%',
                    render: function (dato, tipo, fila) {
                        if (tipo === 'filter') {
                            return dato + ' ' + fila.aplicacion;
                        }
                        return tipo === 'display' ? ui.clave(dato) : dato;
                    }
                },
                {
                    data: 'nombreRol',
                    width: '40%',
                    render: function (dato, tipo, fila) {
                        if (tipo === 'filter') {
                            return dato + ' ' + fila.codigoRol + ' ' + (fila.descripcion || '');
                        }
                        if (tipo !== 'display') {
                            return dato;
                        }
                        return '<div class="fw-semibold">' + App.escaparHtml(dato) + '</div>' + ui.clave(fila.codigoRol) +
                            (fila.descripcion ? '<div class="celda-secundaria">' + App.escaparHtml(fila.descripcion) + '</div>' : '');
                    }
                },
                {
                    data: 'permisos',
                    render: function (dato, tipo) {
                        if (tipo !== 'display') {
                            return dato;
                        }
                        return dato > 0 ? String(dato) :
                            '<span class="badge badge-por-vencer" title="Un rol sin permisos no da acceso a nada">' +
                            '<i class="bi bi-exclamation-triangle" aria-hidden="true"></i>Sin permisos</span>';
                    }
                },
                { data: 'usuarios', render: function (d, t) { return t === 'display' && d === 0 ? '<span class="text-muted-soft">0</span>' : d; } },
                {
                    data: 'activo',
                    render: function (dato, tipo, fila) {
                        if (tipo !== 'display') {
                            return dato ? 'activo' : 'inactivo';
                        }
                        return ui.badgeActivo(dato) + (fila.aplicacionActiva ? '' :
                            '<div><span class="badge badge-inactivo mt-1"><i class="bi bi-dash-circle" aria-hidden="true"></i>Aplicación inactiva</span></div>');
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: function (dato, tipo, fila) {
                        return tipo === 'display' ? htmlAcciones(fila) : '';
                    }
                }
            ]
        });
    }

    /* Filtro por aplicación sobre el dato de la fila (no sobre el texto de
       búsqueda de la columna, que también incluye el nombre). Solo actúa
       sobre esta tabla. */
    $.fn.dataTable.ext.search.push(function (ajustes, datos, indice, fila) {
        if (ajustes.nTable !== $tabla[0]) {
            return true;
        }

        var sistema = $filtro.val();
        return !sistema || fila.codigoSistema === sistema;
    });

    function aplicarFiltro() {
        if (tabla !== null) {
            tabla.draw();
        }
    }

    function cargar() {
        return App.api(URL_API, { opcion: '1' })
            .done(function (data) {
                roles = data.roles || [];
                aplicaciones = data.aplicaciones || [];

                var seleccion = $filtro.val() || App.parametroUrl('sistema');
                $filtro.html(ui.opcionesAplicaciones(aplicaciones, 'Todas las aplicaciones')).val(seleccion);
                if ($filtro.val() === null) { $filtro.val(''); }

                $('#rolesEstado').empty();
                $tabla.removeClass('d-none');

                if (tabla === null) {
                    inicializarTabla();
                }

                tabla.clear().rows.add(roles);
                aplicarFiltro();
                tabla.columns.adjust().responsive.recalc();
            })
            .fail(function (mensaje, respuesta) {
                App.error(mensaje, respuesta);

                if (tabla === null) {
                    $('#rolesEstado').html(ui.vacio('bi-cloud-slash', 'No se pudieron cargar los roles',
                        'Recarga la página para intentarlo de nuevo.'));
                }
            });
    }

    $filtro.on('change', aplicarFiltro);

    /* ==================================================================
       Modal: alta y edición
       ================================================================== */
    var $modalRol = $('#modalRol');
    var $formRol = $('#formRol');
    var $mensajeRol = $('#modalRolMensaje');
    var $btnGuardarRol = $('#btnGuardarRol');
    var modo = 'alta';

    function prepararModalRol(nuevoModo, rol) {
        modo = nuevoModo;
        var esAlta = modo === 'alta';

        $formRol[0].reset();
        $formRol.removeClass('was-validated');
        $mensajeRol.addClass('d-none').text('');

        $('#modalRolTitulo').text(esAlta ? 'Nuevo rol' : 'Editar rol');
        $('#rolAplicacion').html(ui.opcionesAplicaciones(aplicaciones, 'Selecciona…')).prop('disabled', !esAlta);
        $('#rolCodigo').prop('readonly', !esAlta);
        $('#rolCodigoAyuda').text(esAlta
            ? 'Minúsculas sin acentos, números y guion bajo. No se puede cambiar después.'
            : 'La aplicación y el código no se pueden cambiar.');
        $formRol.find('.js-solo-alta').toggleClass('d-none', !esAlta).find('select').prop('disabled', !esAlta);

        if (esAlta) {
            $('#rolAplicacion').val($filtro.val() || '');
        } else {
            $('#rolAplicacion').val(rol.codigoSistema);
            $('#rolCodigo').val(rol.codigoRol);
            $('#rolNombre').val(rol.nombreRol);
            $('#rolDescripcion').val(rol.descripcion || '');
        }

        bootstrap.Modal.getOrCreateInstance($modalRol[0]).show();
    }

    $('#btnNuevoRol').on('click', function () {
        prepararModalRol('alta');
    });

    $modalRol.on('shown.bs.modal', function () {
        if (modo !== 'alta') {
            $('#rolNombre').trigger('focus');
        } else {
            $($('#rolAplicacion').val() ? '#rolCodigo' : '#rolAplicacion').trigger('focus');
        }
    });

    $formRol.on('input change', function () {
        $mensajeRol.addClass('d-none');
    });

    $formRol.on('submit', function (evento) {
        evento.preventDefault();

        $('#rolCodigo, #rolNombre').each(function () {
            this.value = this.value.trim();
        });

        if (!this.checkValidity()) {
            $formRol.addClass('was-validated');
            $formRol.find(':invalid').first().trigger('focus');
            return;
        }

        var datos = {
            opcion: modo === 'alta' ? '3' : '4',
            codigoSistema: $('#rolAplicacion').val(),
            codigoRol: $('#rolCodigo').val(),
            nombreRol: $('#rolNombre').val(),
            descripcion: $('#rolDescripcion').val()
        };

        if (modo === 'alta') {
            datos.activo = $('#rolActivo').val();
        }

        ui.botonOcupado($btnGuardarRol, true);

        App.api(URL_API, datos)
            .done(function (data, respuesta) {
                bootstrap.Modal.getOrCreateInstance($modalRol[0]).hide();
                App.exito(respuesta.mensaje || 'Cambios guardados.');
                cargar();
            })
            .fail(function (mensaje) {
                $mensajeRol.text(mensaje).removeClass('d-none');
            })
            .always(function () {
                ui.botonOcupado($btnGuardarRol, false);
            });
    });

    /* ==================================================================
       Activar / desactivar y eliminar
       ================================================================== */
    function cambiarActivo(r, activo, $boton) {
        var desactivar = activo === '0';

        App.confirmar({
            titulo: desactivar ? 'Desactivar rol' : 'Activar rol',
            mensaje: (desactivar ? '¿Desactivar el rol ' : '¿Activar el rol ') + r.nombreRol + ' (' + claveRol(r) + ')?',
            detalle: desactivar
                ? 'Sus ' + r.usuarios + ' usuario(s) dejarán de recibir sus permisos cuando vuelvan a iniciar sesión. ' +
                    'Las asignaciones se conservan.'
                : 'Sus usuarios volverán a recibir sus permisos cuando inicien sesión.',
            textoConfirmar: desactivar ? 'Sí, desactivar' : 'Sí, activar',
            peligro: desactivar
        }).done(function () {
            $boton.prop('disabled', true);

            App.api(URL_API, { opcion: '5', codigoSistema: r.codigoSistema, codigoRol: r.codigoRol, activo: activo })
                .done(function (data, respuesta) {
                    App.exito(respuesta.mensaje);
                    cargar();
                })
                .fail(function (mensaje, respuesta) {
                    App.error(mensaje, respuesta);
                    $boton.prop('disabled', false);
                });
        });
    }

    function eliminar(r, $boton) {
        App.confirmar({
            titulo: 'Eliminar rol',
            mensaje: '¿Eliminar el rol ' + r.nombreRol + ' (' + claveRol(r) + ')?',
            detalle: 'No tiene usuarios ni permisos, así que se puede eliminar. Esta acción no se puede deshacer.',
            textoConfirmar: 'Sí, eliminar',
            peligro: true
        }).done(function () {
            $boton.prop('disabled', true);

            App.api(URL_API, { opcion: '6', codigoSistema: r.codigoSistema, codigoRol: r.codigoRol })
                .done(function (data, respuesta) {
                    App.exito(respuesta.mensaje);
                    cargar();
                })
                .fail(function (mensaje, respuesta) {
                    App.error(mensaje, respuesta);
                    $boton.prop('disabled', false);
                });
        });
    }

    /* ==================================================================
       Modal: detalle (usuarios y permisos)
       ================================================================== */
    var $modalDetalle = $('#modalDetalleRol');

    function abrirDetalle(r) {
        $('#detalleRolNombre').text(r.nombreRol);
        $('#detalleRolClave').text(claveRol(r));
        $('#detalleRolDescripcion').text(r.descripcion || '');
        $('#panelRolUsuarios, #panelRolPermisos').html(ui.cargador('Cargando…'));
        bootstrap.Tab.getOrCreateInstance(document.getElementById('tabRolUsuarios')).show();
        bootstrap.Modal.getOrCreateInstance($modalDetalle[0]).show();

        App.api(URL_API, { opcion: '2', codigoSistema: r.codigoSistema, codigoRol: r.codigoRol })
            .done(function (data) {
                var usuarios = data.usuarios || [];
                var permisos = data.permisos || [];

                $('#detalleRolTotalUsuarios').text(usuarios.length);
                $('#detalleRolTotalPermisos').text(permisos.length);

                $('#panelRolUsuarios').html(usuarios.length === 0
                    ? ui.vacio('bi-people', 'Ningún usuario tiene este rol', 'Se asigna desde Asignaciones › Roles de usuario.')
                    : '<div class="table-responsive"><table class="table table-hover align-middle">' +
                        '<thead><tr><th>Usuario</th><th>Nómina</th><th>Estatus</th></tr></thead><tbody>' +
                        usuarios.map(function (u) {
                            return '<tr><td class="fw-semibold">' + App.escaparHtml(u.nombre) + '</td>' +
                                '<td>' + ui.clave(u.nominaUsuario) + '</td>' +
                                '<td>' + ui.badgeActivo(u.estatus === 'activo') + '</td></tr>';
                        }).join('') + '</tbody></table></div>');

                var moduloActual = null;
                var filas = '';

                permisos.forEach(function (p) {
                    if (p.codigoModulo !== moduloActual) {
                        moduloActual = p.codigoModulo;
                        filas += '<tr class="fila-grupo"><th colspan="2">' + App.escaparHtml(p.modulo) + ' ' + ui.clave(p.codigoModulo) +
                            (p.moduloActivo ? '' : ' <span class="badge badge-inactivo">Módulo inactivo</span>') + '</th></tr>';
                    }
                    filas += '<tr><td>' + ui.clave(p.accion) + '</td><td class="small">' + App.escaparHtml(p.descripcion || '') + '</td></tr>';
                });

                $('#panelRolPermisos').html(permisos.length === 0
                    ? ui.vacio('bi-key', 'Este rol no tiene permisos', 'Asígnalos con el botón de permisos del listado.')
                    : '<div class="table-responsive"><table class="table align-middle"><tbody>' + filas + '</tbody></table></div>');
            })
            .fail(function (mensaje, respuesta) {
                App.error(mensaje, respuesta);
                $('#panelRolUsuarios, #panelRolPermisos').html(ui.vacio('bi-cloud-slash', 'No se pudo cargar el detalle', mensaje));
            });
    }

    /* ==================================================================
       Modal: asignación de permisos (lista de checks)
       ================================================================== */
    var $modalPermisos = $('#modalPermisosRol');
    var $lista = $('#permisosRolLista');
    var $mensajePermisos = $('#permisosRolMensaje');
    var $btnGuardarPermisos = $('#btnGuardarPermisosRol');
    var $selCopiar = $('#permisosRolCopiar');

    var rolPermisos = null;       // rol que se está editando
    var iniciales = {};           // claves asignadas al abrir: { 'modulo.accion': true }
    var totalPermisos = 0;
    var otrosRoles = [];
    var permitirCerrar = false;   // true cuando se guardó o se eligió descartar

    function seleccionActual() {
        return $lista.find('.js-permiso:checked').map(function () { return this.value; }).get();
    }

    function hayCambios() {
        var actual = seleccionActual();
        var totalIniciales = Object.keys(iniciales).length;

        if (actual.length !== totalIniciales) {
            return true;
        }

        return actual.some(function (clave) { return !iniciales[clave]; });
    }

    /** Recalcula contadores, casillas de módulo y el resumen del pie. */
    function actualizarEstado() {
        var actual = seleccionActual();
        var agregados = actual.filter(function (c) { return !iniciales[c]; }).length;
        var quitados = Object.keys(iniciales).filter(function (c) { return actual.indexOf(c) === -1; }).length;

        $lista.find('.rol-modulo').each(function () {
            var $grupo = $(this);
            var $casillas = $grupo.find('.js-permiso');
            var marcadas = $casillas.filter(':checked').length;
            var $todos = $grupo.find('.js-modulo-todos');

            $todos.prop('checked', marcadas === $casillas.length && marcadas > 0);
            $todos.prop('indeterminate', marcadas > 0 && marcadas < $casillas.length);
            $grupo.find('.rol-modulo-contador').text(marcadas + ' de ' + $casillas.length);
        });

        $('#permisosRolResumen').html(
            '<strong>' + actual.length + '</strong> de ' + totalPermisos + ' permisos seleccionados' +
            (agregados || quitados
                ? ' · <span class="text-success">+' + agregados + '</span> <span class="text-danger">−' + quitados + '</span> sin guardar'
                : '')
        );

        $btnGuardarPermisos.prop('disabled', !(agregados || quitados));
    }

    function pintarLista(permisos) {
        var grupos = [];
        var indice = {};

        permisos.forEach(function (p) {
            if (!indice[p.codigoModulo]) {
                indice[p.codigoModulo] = { codigoModulo: p.codigoModulo, modulo: p.modulo, moduloActivo: p.moduloActivo, permisos: [] };
                grupos.push(indice[p.codigoModulo]);
            }
            indice[p.codigoModulo].permisos.push(p);
        });

        if (grupos.length === 0) {
            $lista.html(ui.vacio('bi-key', 'La aplicación no tiene permisos',
                'Crea primero sus módulos y permisos en Catálogos.'));
            return;
        }

        var contador = 0;

        $lista.html(grupos.map(function (g) {
            var idTodos = 'rolModulo' + (contador++);

            return '<fieldset class="rol-modulo">' +
                '<legend class="rol-modulo-encabezado">' +
                    '<input class="form-check-input js-modulo-todos" type="checkbox" id="' + idTodos + '">' +
                    '<label class="rol-modulo-titulo" for="' + idTodos + '">' + App.escaparHtml(g.modulo) + ' ' + ui.clave(g.codigoModulo) +
                        (g.moduloActivo ? '' : ' <span class="badge badge-inactivo">Módulo inactivo</span>') + '</label>' +
                    '<span class="rol-modulo-contador"></span>' +
                '</legend>' +
                '<div class="rol-permisos">' +
                g.permisos.map(function (p) {
                    var id = 'rolPermiso' + (contador++);
                    var clave = p.codigoModulo + '.' + p.accion;
                    var busqueda = (p.accion + ' ' + p.codigoModulo + ' ' + p.modulo + ' ' + (p.descripcion || '')).toLowerCase();

                    return '<div class="form-check rol-permiso" data-busqueda="' + App.escaparHtml(busqueda) + '">' +
                        '<input class="form-check-input js-permiso" type="checkbox" id="' + id + '" value="' + App.escaparHtml(clave) + '"' +
                            (p.asignado ? ' checked' : '') + '>' +
                        '<label class="form-check-label" for="' + id + '">' + ui.clave(p.accion) +
                            (p.descripcion ? '<span class="rol-permiso-descripcion">' + App.escaparHtml(p.descripcion) + '</span>' : '') +
                        '</label></div>';
                }).join('') +
                '</div></fieldset>';
        }).join(''));
    }

    function abrirPermisos(r) {
        rolPermisos = r;
        iniciales = {};
        otrosRoles = [];
        totalPermisos = 0;
        permitirCerrar = false;

        $('#permisosRolNombre').text(r.nombreRol);
        $('#permisosRolClave').text(claveRol(r));
        $('#permisosRolEstado').html(ui.badgeActivo(r.activo));
        $mensajePermisos.addClass('d-none').text('');
        $('#permisosRolSalir, #permisosRolCopiarConfirmacion').addClass('d-none');
        $('#permisosRolContenido').addClass('d-none');
        $('#permisosRolBuscar').val('');
        $('#permisosRolResumen').empty();
        $btnGuardarPermisos.prop('disabled', true);
        $('#permisosRolCarga').html(ui.cargador('Cargando los permisos de ' + r.codigoSistema + '…'));
        bootstrap.Modal.getOrCreateInstance($modalPermisos[0]).show();

        App.api(URL_API, { opcion: '7', codigoSistema: r.codigoSistema, codigoRol: r.codigoRol })
            .done(function (data) {
                var permisos = data.permisos || [];
                otrosRoles = data.otrosRoles || [];
                totalPermisos = permisos.length;

                permisos.forEach(function (p) {
                    if (p.asignado) {
                        iniciales[p.codigoModulo + '.' + p.accion] = true;
                    }
                });

                $selCopiar.html('<option value="">Selecciona un rol…</option>' + otrosRoles.map(function (o) {
                    return '<option value="' + App.escaparHtml(o.codigoRol) + '">' +
                        App.escaparHtml(o.nombreRol + ' (' + o.codigoRol + ') — ' + o.permisos.length + ' permisos') + '</option>';
                }).join('')).prop('disabled', otrosRoles.length === 0);

                pintarLista(permisos);
                $('#permisosRolCarga').empty();
                $('#permisosRolContenido').removeClass('d-none');
                actualizarEstado();
            })
            .fail(function (mensaje, respuesta) {
                App.error(mensaje, respuesta);
                $('#permisosRolCarga').html(ui.vacio('bi-cloud-slash', 'No se pudieron cargar los permisos', mensaje));
            });
    }

    $lista.on('change', '.js-permiso', function () {
        $('#permisosRolSalir').addClass('d-none');
        actualizarEstado();
    });

    // "Todo el módulo" marca o desmarca solo los permisos visibles (respeta la búsqueda).
    $lista.on('change', '.js-modulo-todos', function () {
        $(this).closest('.rol-modulo').find('.rol-permiso:not(.d-none) .js-permiso').prop('checked', this.checked);
        actualizarEstado();
    });

    $('#permisosRolBuscar').on('input', function () {
        var texto = $.trim($(this).val()).toLowerCase();

        $lista.find('.rol-modulo').each(function () {
            var $grupo = $(this);
            var visibles = 0;

            $grupo.find('.rol-permiso').each(function () {
                var coincide = texto === '' || $(this).attr('data-busqueda').indexOf(texto) !== -1;
                $(this).toggleClass('d-none', !coincide);
                if (coincide) { visibles++; }
            });

            $grupo.toggleClass('d-none', visibles === 0);
        });
    });

    /* --- Copiar de otro rol: confirmación como paso dentro del modal --- */
    function rolOrigenSeleccionado() {
        var codigo = $selCopiar.val();

        for (var i = 0; i < otrosRoles.length; i++) {
            if (otrosRoles[i].codigoRol === codigo) {
                return otrosRoles[i];
            }
        }
        return null;
    }

    $selCopiar.on('change', function () {
        var origen = rolOrigenSeleccionado();

        if (!origen) {
            $('#permisosRolCopiarConfirmacion').addClass('d-none');
            return;
        }

        $('#permisosRolCopiarTexto').text(
            'El rol ' + origen.nombreRol + ' tiene ' + origen.permisos.length + ' permiso(s). ' +
            '¿Reemplazar tu selección actual con ellos, o agregarlos a lo que ya está marcado? ' +
            'No se guarda nada hasta que presiones "Guardar permisos".'
        );
        $('#permisosRolCopiarConfirmacion').removeClass('d-none');
    });

    function aplicarCopia(reemplazar) {
        var origen = rolOrigenSeleccionado();

        if (!origen) {
            return;
        }

        var deOrigen = {};
        origen.permisos.forEach(function (c) { deOrigen[c] = true; });

        $lista.find('.js-permiso').each(function () {
            this.checked = reemplazar ? !!deOrigen[this.value] : (this.checked || !!deOrigen[this.value]);
        });

        $selCopiar.val('');
        $('#permisosRolCopiarConfirmacion').addClass('d-none');
        actualizarEstado();
        App.info('Selección copiada de ' + origen.nombreRol + '. Revisa y presiona "Guardar permisos".');
    }

    $('#btnCopiarReemplazar').on('click', function () { aplicarCopia(true); });
    $('#btnCopiarAgregar').on('click', function () { aplicarCopia(false); });
    $('#btnCopiarCancelar').on('click', function () {
        $selCopiar.val('');
        $('#permisosRolCopiarConfirmacion').addClass('d-none');
    });

    /* --- Guardar --- */
    $btnGuardarPermisos.on('click', function () {
        if (!rolPermisos) {
            return;
        }

        $mensajePermisos.addClass('d-none');
        ui.botonOcupado($btnGuardarPermisos, true);

        App.api(URL_API, {
            opcion: '8',
            codigoSistema: rolPermisos.codigoSistema,
            codigoRol: rolPermisos.codigoRol,
            permisos: JSON.stringify(seleccionActual())
        })
            .done(function (data, respuesta) {
                permitirCerrar = true;
                ui.botonOcupado($btnGuardarPermisos, false);
                bootstrap.Modal.getOrCreateInstance($modalPermisos[0]).hide();
                App.exito(respuesta.mensaje);
                cargar();
            })
            .fail(function (mensaje) {
                ui.botonOcupado($btnGuardarPermisos, false);
                $mensajePermisos.text(mensaje).removeClass('d-none');
                actualizarEstado();
            });
    });

    /* --- Cerrar con cambios sin guardar: se pide confirmar dentro del modal --- */
    $modalPermisos.on('hide.bs.modal', function (evento) {
        if (permitirCerrar || $('#permisosRolContenido').hasClass('d-none') || !hayCambios()) {
            return;
        }

        evento.preventDefault();
        $('#permisosRolSalir').removeClass('d-none');
        $modalPermisos.find('.modal-body').scrollTop(0);
    });

    $('#btnSeguirEditando').on('click', function () {
        $('#permisosRolSalir').addClass('d-none');
    });

    $('#btnDescartarCambios').on('click', function () {
        permitirCerrar = true;
        bootstrap.Modal.getOrCreateInstance($modalPermisos[0]).hide();
    });

    $modalPermisos.on('hidden.bs.modal', function () {
        rolPermisos = null;
        $lista.empty();
    });

    /* ==================================================================
       Acciones de cada fila
       ================================================================== */
    function rolDelBoton($boton) {
        return buscar($boton.attr('data-sistema'), $boton.attr('data-rol'));
    }

    $tabla.on('click', '.js-detalle', function () {
        var r = rolDelBoton($(this));
        if (r) { abrirDetalle(r); }
    });

    $tabla.on('click', '.js-permisos', function () {
        var r = rolDelBoton($(this));
        if (r) { abrirPermisos(r); }
    });

    $tabla.on('click', '.js-editar', function () {
        var r = rolDelBoton($(this));
        if (r) { prepararModalRol('edicion', r); }
    });

    $tabla.on('click', '.js-activo', function () {
        var $boton = $(this);
        var r = rolDelBoton($boton);
        if (r) { cambiarActivo(r, $boton.attr('data-activo'), $boton); }
    });

    $tabla.on('click', '.js-eliminar', function () {
        var $boton = $(this);
        var r = rolDelBoton($boton);
        if (r) { eliminar(r, $boton); }
    });

    cargar();
})();
