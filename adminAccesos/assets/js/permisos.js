/**
 * assets/js/permisos.js — Catálogo de permisos (pages/permisos.php).
 *
 * Habla con api/operacionesPermisos.php:
 *   1 listar · 2 relaciones · 3 crear · 4 modificar descripción · 5 eliminar
 *
 * Un permiso se identifica por (codigoSistema, codigoModulo, accion); no hay
 * IDs en el navegador. Acepta ?sistema=XXX&modulo=yyy para llegar ya filtrada.
 */
(function () {
    'use strict';

    var URL_API = 'api/operacionesPermisos.php';
    var ui = App.ui;

    var $tabla = $('#tablaPermisos');
    var $filtroAplicacion = $('#filtroAplicacion');
    var $filtroModulo = $('#filtroModulo');
    var tabla = null;
    var permisos = [];
    var aplicaciones = [];
    var modulos = [];

    function claveCompleta(p) {
        return p.codigoSistema + '.' + p.codigoModulo + '.' + p.accion;
    }

    function buscar(sistema, modulo, accion) {
        for (var i = 0; i < permisos.length; i++) {
            var p = permisos[i];
            if (p.codigoSistema === sistema && p.codigoModulo === modulo && p.accion === accion) {
                return p;
            }
        }
        return null;
    }

    /** Opciones de módulos de una aplicación. */
    function opcionesModulos(sistema, textoPrimera) {
        var html = '<option value="">' + App.escaparHtml(textoPrimera) + '</option>';

        modulos.forEach(function (m) {
            if (m.codigoSistema === sistema) {
                html += '<option value="' + App.escaparHtml(m.codigoModulo) + '">' +
                    App.escaparHtml(m.codigoModulo + ' — ' + m.nombre + (m.activo ? '' : ' (inactivo)')) + '</option>';
            }
        });

        return html;
    }

    /* ==================================================================
       Tabla
       ================================================================== */

    /* Filtro por aplicación y módulo sobre los datos de la fila (no sobre el
       HTML). Solo actúa sobre esta tabla. */
    $.fn.dataTable.ext.search.push(function (ajustes, datos, indice, fila) {
        if (ajustes.nTable !== $tabla[0]) {
            return true;
        }

        var sistema = $filtroAplicacion.val();
        var modulo = $filtroModulo.val();

        return (!sistema || fila.codigoSistema === sistema) && (!modulo || fila.codigoModulo === modulo);
    });

    function htmlEstado(p) {
        if (!p.aplicacionActiva) {
            return '<span class="badge badge-inactivo" title="La aplicación está inactiva: el permiso no tiene efecto">' +
                '<i class="bi bi-dash-circle" aria-hidden="true"></i>Aplicación inactiva</span>';
        }
        if (!p.moduloActivo) {
            return '<span class="badge badge-inactivo" title="El módulo está inactivo: el permiso no tiene efecto">' +
                '<i class="bi bi-dash-circle" aria-hidden="true"></i>Módulo inactivo</span>';
        }
        return ui.badgeActivo(true, 'Con efecto');
    }

    function htmlAcciones(p) {
        var claves = 'data-sistema="' + App.escaparHtml(p.codigoSistema) + '" data-modulo="' +
            App.escaparHtml(p.codigoModulo) + '" data-accion="' + App.escaparHtml(p.accion) + '"';
        var html = '<div class="acciones-fila">' +
            '<button type="button" class="btn btn-outline-primary btn-icon js-relaciones" ' + claves +
                ' title="¿Quién lo tiene?" aria-label="¿Quién lo tiene?"><i class="bi bi-people" aria-hidden="true"></i></button>' +
            '<button type="button" class="btn btn-soft btn-icon js-editar" ' + claves +
                ' title="Editar descripción" aria-label="Editar descripción"><i class="bi bi-pencil" aria-hidden="true"></i></button>';

        if (p.roles === 0 && p.excepciones === 0) {
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
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Todos']],
            order: [],
            columnDefs: [
                { responsivePriority: 1, targets: 0 },
                { responsivePriority: 2, targets: 5 },
                { responsivePriority: 3, targets: 1 }
            ],
            columns: [
                {
                    data: 'accion',
                    width: '36%',
                    render: function (dato, tipo, fila) {
                        if (tipo === 'filter') {
                            return claveCompleta(fila) + ' ' + fila.aplicacion + ' ' + fila.modulo;
                        }
                        if (tipo !== 'display') {
                            return claveCompleta(fila);
                        }
                        return ui.clave(claveCompleta(fila)) +
                            '<div class="celda-secundaria">' + App.escaparHtml(fila.aplicacion) + ' › ' + App.escaparHtml(fila.modulo) + '</div>';
                    }
                },
                {
                    data: 'descripcion',
                    width: '30%',
                    render: function (dato, tipo) {
                        if (tipo !== 'display') {
                            return dato || '';
                        }
                        return dato ? App.escaparHtml(dato) : '<span class="text-muted-soft">Sin descripción</span>';
                    }
                },
                { data: 'roles', render: function (d, t) { return t === 'display' && d === 0 ? '<span class="text-muted-soft">0</span>' : d; } },
                { data: 'excepciones', render: function (d, t) { return t === 'display' && d === 0 ? '<span class="text-muted-soft">0</span>' : d; } },
                {
                    data: 'moduloActivo',
                    render: function (dato, tipo, fila) {
                        return tipo === 'display' ? htmlEstado(fila) : (fila.aplicacionActiva && fila.moduloActivo ? 'con efecto' : 'inactivo');
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

    /** Rellena el filtro de módulos según la aplicación elegida. */
    function actualizarFiltroModulo(seleccion) {
        var sistema = $filtroAplicacion.val();

        $filtroModulo
            .html(sistema ? opcionesModulos(sistema, 'Todos los módulos') : '<option value="">Todos los módulos</option>')
            .prop('disabled', !sistema)
            .val(seleccion || '');

        if ($filtroModulo.val() === null) {
            $filtroModulo.val('');
        }
    }

    function cargar() {
        return App.api(URL_API, { opcion: '1' })
            .done(function (data) {
                permisos = data.permisos || [];
                aplicaciones = data.aplicaciones || [];
                modulos = data.modulos || [];

                // Conserva los filtros elegidos (o los que llegaron por la URL).
                var sistema = $filtroAplicacion.val() || App.parametroUrl('sistema');
                var modulo = $filtroModulo.val() || App.parametroUrl('modulo');

                $filtroAplicacion.html(ui.opcionesAplicaciones(aplicaciones, 'Todas las aplicaciones')).val(sistema);
                if ($filtroAplicacion.val() === null) { $filtroAplicacion.val(''); }
                actualizarFiltroModulo(modulo);

                $('#permisosEstado').empty();
                $tabla.removeClass('d-none');

                if (tabla === null) {
                    inicializarTabla();
                }

                tabla.clear().rows.add(permisos).draw(false);
                tabla.columns.adjust().responsive.recalc();
            })
            .fail(function (mensaje, respuesta) {
                App.error(mensaje, respuesta);

                if (tabla === null) {
                    $('#permisosEstado').html(ui.vacio('bi-cloud-slash', 'No se pudieron cargar los permisos',
                        'Recarga la página para intentarlo de nuevo.'));
                }
            });
    }

    $filtroAplicacion.on('change', function () {
        actualizarFiltroModulo('');
        if (tabla !== null) { tabla.draw(); }
    });

    $filtroModulo.on('change', function () {
        if (tabla !== null) { tabla.draw(); }
    });

    /* ==================================================================
       Modal: alta y edición (en edición solo cambia la descripción)
       ================================================================== */
    var $modal = $('#modalPermiso');
    var $form = $('#formPermiso');
    var $mensaje = $('#modalPermisoMensaje');
    var $btnGuardar = $('#btnGuardarPermiso');
    var $selAplicacion = $('#permisoAplicacion');
    var $selModulo = $('#permisoModulo');
    var modo = 'alta';

    function actualizarVistaPrevia() {
        $('#permisoClavePrevia').text(
            ($selAplicacion.val() || '…') + '.' + ($selModulo.val() || '…') + '.' + ($.trim($('#permisoAccion').val()) || '…')
        );
    }

    function llenarModulosFormulario(seleccion) {
        var sistema = $selAplicacion.val();

        $selModulo
            .html(sistema ? opcionesModulos(sistema, 'Selecciona…') : '<option value="">Primero elige la aplicación</option>')
            .val(seleccion || '');

        if ($selModulo.val() === null) {
            $selModulo.val('');
        }

        $selModulo.prop('disabled', modo !== 'alta' || !sistema);
    }

    function prepararModal(nuevoModo, permiso) {
        modo = nuevoModo;
        var esAlta = modo === 'alta';

        $form[0].reset();
        $form.removeClass('was-validated');
        $mensaje.addClass('d-none').text('');

        $('#modalPermisoTitulo').text(esAlta ? 'Nuevo permiso' : 'Editar descripción del permiso');
        $selAplicacion.html(ui.opcionesAplicaciones(aplicaciones, 'Selecciona…')).prop('disabled', !esAlta);
        $('#permisoAccion').prop('readonly', !esAlta);
        $('#permisoAccionAyuda').text(esAlta
            ? 'El paso concreto del flujo, no un verbo genérico. Minúsculas sin acentos, números y guion bajo. No se puede cambiar después.'
            : 'La aplicación, el módulo y la acción no se pueden cambiar.');

        if (esAlta) {
            // Llega con la aplicación y el módulo del filtro, si había.
            $selAplicacion.val($filtroAplicacion.val() || '');
            llenarModulosFormulario($filtroModulo.val());
        } else {
            $selAplicacion.val(permiso.codigoSistema);
            llenarModulosFormulario(permiso.codigoModulo);
            $('#permisoAccion').val(permiso.accion);
            $('#permisoDescripcion').val(permiso.descripcion || '');
        }

        actualizarVistaPrevia();
        bootstrap.Modal.getOrCreateInstance($modal[0]).show();
    }

    $('#btnNuevoPermiso').on('click', function () {
        prepararModal('alta');
    });

    $modal.on('shown.bs.modal', function () {
        if (modo !== 'alta') {
            $('#permisoDescripcion').trigger('focus');
        } else if (!$selAplicacion.val()) {
            $selAplicacion.trigger('focus');
        } else {
            $(!$selModulo.val() ? '#permisoModulo' : '#permisoAccion').trigger('focus');
        }
    });

    $selAplicacion.on('change', function () {
        llenarModulosFormulario('');
        actualizarVistaPrevia();
    });

    $selModulo.add('#permisoAccion').on('input change', actualizarVistaPrevia);

    $form.on('input change', function () {
        $mensaje.addClass('d-none');
    });

    $form.on('submit', function (evento) {
        evento.preventDefault();
        $('#permisoAccion').val($.trim($('#permisoAccion').val()));

        if (!this.checkValidity()) {
            $form.addClass('was-validated');
            $form.find(':invalid').first().trigger('focus');
            return;
        }

        ui.botonOcupado($btnGuardar, true);

        App.api(URL_API, {
            opcion: modo === 'alta' ? '3' : '4',
            codigoSistema: $selAplicacion.val(),
            codigoModulo: $selModulo.val(),
            accion: $('#permisoAccion').val(),
            descripcion: $('#permisoDescripcion').val()
        })
            .done(function (data, respuesta) {
                bootstrap.Modal.getOrCreateInstance($modal[0]).hide();
                App.exito(respuesta.mensaje || 'Cambios guardados.');
                cargar();
            })
            .fail(function (mensaje) {
                $mensaje.text(mensaje).removeClass('d-none');
            })
            .always(function () {
                ui.botonOcupado($btnGuardar, false);
            });
    });

    /* ==================================================================
       Eliminar (solo sin asignaciones)
       ================================================================== */
    function eliminar(p, $boton) {
        App.confirmar({
            titulo: 'Eliminar permiso',
            mensaje: '¿Eliminar el permiso ' + claveCompleta(p) + '?',
            detalle: 'No está asignado a ningún rol ni usuario. Si alguna aplicación lo usa en su código, ' +
                'esa acción quedará sin permiso para nadie. Esta acción no se puede deshacer.',
            textoConfirmar: 'Sí, eliminar',
            peligro: true
        }).done(function () {
            $boton.prop('disabled', true);

            App.api(URL_API, { opcion: '5', codigoSistema: p.codigoSistema, codigoModulo: p.codigoModulo, accion: p.accion })
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
       Modal: ¿quién tiene el permiso?
       ================================================================== */
    var $modalRelaciones = $('#modalRelacionesPermiso');

    function htmlSeccion(titulo, icono, totalTexto, cuerpo) {
        return '<div class="mb-4">' +
            '<div class="panel-titulo"><i class="bi ' + icono + '" aria-hidden="true"></i>' + App.escaparHtml(titulo) +
            ' <span class="badge badge-estado-neutro">' + App.escaparHtml(totalTexto) + '</span></div>' +
            cuerpo + '</div>';
    }

    function htmlTabla(encabezados, filas) {
        return '<div class="table-responsive"><table class="table table-hover align-middle">' +
            '<thead><tr>' + encabezados.map(function (e) { return '<th>' + App.escaparHtml(e) + '</th>'; }).join('') + '</tr></thead>' +
            '<tbody>' + filas + '</tbody></table></div>';
    }

    function htmlVigencia(e) {
        if (e.estadoVigencia === 'permanente') {
            return '<span class="badge badge-permanente"><i class="bi bi-infinity" aria-hidden="true"></i>Permanente</span>';
        }
        if (e.estadoVigencia === 'vencido') {
            return '<span class="badge badge-vencido"><i class="bi bi-calendar-x" aria-hidden="true"></i>Venció ' +
                App.escaparHtml(App.formatoFecha(e.vigenciaHasta)) + '</span>';
        }
        return '<span class="badge badge-vigente"><i class="bi bi-calendar-check" aria-hidden="true"></i>Hasta ' +
            App.escaparHtml(App.formatoFecha(e.vigenciaHasta)) + '</span>';
    }

    function pintarRelaciones(data) {
        var roles = data.roles || [];
        var usuariosPorRol = data.usuariosPorRol || [];
        var excepciones = data.excepciones || [];
        var html = '';

        if (roles.length === 0 && excepciones.length === 0) {
            $('#relacionesContenido').html(ui.vacio('bi-person-x', 'Nadie tiene este permiso',
                'No está asignado a ningún rol ni tiene excepciones individuales. Se puede eliminar.'));
            return;
        }

        html += htmlSeccion('Roles que lo incluyen', 'bi-person-badge', roles.length,
            roles.length === 0
                ? '<p class="text-muted-soft mb-0">Ningún rol.</p>'
                : htmlTabla(['Rol', 'Usuarios con el rol', 'Estado'], roles.map(function (r) {
                    return '<tr><td><div class="fw-semibold">' + App.escaparHtml(r.nombreRol) + '</div>' + ui.clave(r.codigoRol) + '</td>' +
                        '<td>' + r.usuarios + '</td><td>' + ui.badgeActivo(r.activo) + '</td></tr>';
                }).join('')));

        html += htmlSeccion('Usuarios que lo reciben por rol', 'bi-people', usuariosPorRol.length,
            usuariosPorRol.length === 0
                ? '<p class="text-muted-soft mb-0">Ningún usuario.</p>'
                : htmlTabla(['Usuario', 'Por el rol', 'Estatus'], usuariosPorRol.map(function (u) {
                    return '<tr><td><div class="fw-semibold">' + App.escaparHtml(u.nombre) + '</div>' + ui.clave(u.nominaUsuario) + '</td>' +
                        '<td class="small">' + App.escaparHtml(u.roles) + '</td>' +
                        '<td>' + ui.badgeActivo(u.estatus === 'activo') + '</td></tr>';
                }).join('')));

        html += htmlSeccion('Excepciones individuales', 'bi-person-lock', excepciones.length,
            excepciones.length === 0
                ? '<p class="text-muted-soft mb-0">Ninguna.</p>'
                : htmlTabla(['Usuario', 'Tipo', 'Vigencia', 'Motivo'], excepciones.map(function (e) {
                    return '<tr><td><div class="fw-semibold">' + App.escaparHtml(e.nombre) + '</div>' + ui.clave(e.nominaUsuario) + '</td>' +
                        '<td>' + (e.tipo === 'denegar'
                            ? '<span class="badge badge-denegar"><i class="bi bi-slash-circle" aria-hidden="true"></i>Denegar</span>'
                            : '<span class="badge badge-conceder"><i class="bi bi-check-circle" aria-hidden="true"></i>Conceder</span>') + '</td>' +
                        '<td>' + htmlVigencia(e) + '</td>' +
                        '<td class="small">' + App.escaparHtml(e.motivo || '') + '</td></tr>';
                }).join('')));

        $('#relacionesContenido').html(html);
    }

    function abrirRelaciones(p) {
        $('#relacionesClave').text(claveCompleta(p));
        $('#relacionesDescripcion').text(p.descripcion || '');
        $('#relacionesContenido').html(ui.cargador('Buscando quién tiene el permiso…'));
        bootstrap.Modal.getOrCreateInstance($modalRelaciones[0]).show();

        App.api(URL_API, { opcion: '2', codigoSistema: p.codigoSistema, codigoModulo: p.codigoModulo, accion: p.accion })
            .done(pintarRelaciones)
            .fail(function (mensaje, respuesta) {
                App.error(mensaje, respuesta);
                $('#relacionesContenido').html(ui.vacio('bi-cloud-slash', 'No se pudieron cargar las relaciones', mensaje));
            });
    }

    /* ==================================================================
       Acciones de cada fila
       ================================================================== */
    function permisoDelBoton($boton) {
        return buscar($boton.attr('data-sistema'), $boton.attr('data-modulo'), $boton.attr('data-accion'));
    }

    $tabla.on('click', '.js-relaciones', function () {
        var p = permisoDelBoton($(this));
        if (p) { abrirRelaciones(p); }
    });

    $tabla.on('click', '.js-editar', function () {
        var p = permisoDelBoton($(this));
        if (p) { prepararModal('edicion', p); }
    });

    $tabla.on('click', '.js-eliminar', function () {
        var $boton = $(this);
        var p = permisoDelBoton($boton);
        if (p) { eliminar(p, $boton); }
    });

    cargar();
})();
