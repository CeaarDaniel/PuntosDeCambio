/**
 * assets/js/permisosUsuario.js — Permisos individuales (pages/permisosUsuario.php).
 *
 * Habla con api/operacionesPermisosUsuario.php:
 *   1 catálogos · 2 excepciones del usuario · 3 crear · 4 modificar ·
 *   5 retirar (vencer ayer) · 6 eliminar del historial (solo vencidas)
 *
 * El permiso se identifica por (codigoSistema, codigoModulo, accion); no hay
 * IDs en el navegador. otorgadoPor lo pone el servidor desde la sesión.
 */
(function () {
    'use strict';

    var URL_API = 'api/operacionesPermisosUsuario.php';
    var ui = App.ui;

    var $tabla = $('#tablaExcepciones');
    var $btnNueva = $('#btnNuevaExcepcion');
    var tabla = null;

    var aplicaciones = [];
    var modulos = [];
    var permisos = [];
    var usuario = null;
    var excepciones = [];
    var porRol = {};              // 'sistema.modulo.accion' => [roles]
    var hoy = '';
    var filtroVigencia = '';

    function claveCompleta(e) {
        return e.codigoSistema + '.' + e.codigoModulo + '.' + e.accion;
    }

    function buscarExcepcion(clave) {
        for (var i = 0; i < excepciones.length; i++) {
            if (claveCompleta(excepciones[i]) === clave) {
                return excepciones[i];
            }
        }
        return null;
    }

    function htmlTipo(tipo) {
        return tipo === 'denegar'
            ? '<span class="badge badge-denegar"><i class="bi bi-slash-circle" aria-hidden="true"></i>Denegar</span>'
            : '<span class="badge badge-conceder"><i class="bi bi-check-circle" aria-hidden="true"></i>Conceder</span>';
    }

    function diasHasta(fecha) {
        var a = new Date(hoy + 'T00:00:00');
        var b = new Date(fecha + 'T00:00:00');
        return Math.round((b - a) / 86400000);
    }

    function htmlVigencia(e) {
        if (e.estadoVigencia === 'permanente') {
            return '<span class="badge badge-permanente"><i class="bi bi-infinity" aria-hidden="true"></i>Permanente</span>';
        }
        if (e.estadoVigencia === 'vencido') {
            return '<span class="badge badge-vencido"><i class="bi bi-calendar-x" aria-hidden="true"></i>Venció ' +
                App.escaparHtml(App.formatoFecha(e.vigenciaHasta)) + '</span>';
        }

        var dias = diasHasta(e.vigenciaHasta);
        var proxima = dias <= 15;

        return '<span class="badge ' + (proxima ? 'badge-por-vencer' : 'badge-vigente') + '">' +
            '<i class="bi bi-calendar-check" aria-hidden="true"></i>Hasta ' + App.escaparHtml(App.formatoFecha(e.vigenciaHasta)) + '</span>' +
            (proxima ? '<div class="celda-secundaria">' + (dias === 0 ? 'Último día hoy' : 'Quedan ' + dias + ' día(s)') + '</div>' : '');
    }

    /* ==================================================================
       Tabla de excepciones
       ================================================================== */

    /* Filtro por vigencia sobre el dato de la fila; solo para esta tabla. */
    $.fn.dataTable.ext.search.push(function (ajustes, datos, indice, fila) {
        return ajustes.nTable !== $tabla[0] || !filtroVigencia || fila.estadoVigencia === filtroVigencia;
    });

    function htmlAcciones(e) {
        var clave = App.escaparHtml(claveCompleta(e));
        var activo = usuario && usuario.estatus === 'activo';
        var html = '<div class="acciones-fila">';

        if (activo) {
            html += '<button type="button" class="btn btn-soft btn-icon js-editar" data-clave="' + clave + '" ' +
                'title="' + (e.estadoVigencia === 'vencido' ? 'Renovar o editar' : 'Editar') + '" aria-label="Editar">' +
                '<i class="bi bi-pencil" aria-hidden="true"></i></button>';
        }

        if (e.estadoVigencia === 'vencido') {
            html += '<button type="button" class="btn btn-soft btn-icon text-danger js-eliminar" data-clave="' + clave + '" ' +
                'title="Eliminar del historial" aria-label="Eliminar del historial"><i class="bi bi-trash" aria-hidden="true"></i></button>';
        } else {
            html += '<button type="button" class="btn btn-soft btn-icon text-danger js-retirar" data-clave="' + clave + '" ' +
                'title="Retirar (deja de aplicar hoy)" aria-label="Retirar"><i class="bi bi-x-circle" aria-hidden="true"></i></button>';
        }

        return html + '</div>';
    }

    function inicializarTabla() {
        tabla = $tabla.DataTable({
            data: [],
            language: $.extend({}, App.TEXTOS_TABLA, { emptyTable: 'Este usuario no tiene excepciones individuales.' }),
            responsive: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todas']],
            order: [],
            columnDefs: [
                { responsivePriority: 1, targets: 0 },
                { responsivePriority: 2, targets: 5 },
                { responsivePriority: 3, targets: 1 },
                { responsivePriority: 4, targets: 2 }
            ],
            createdRow: function (fila, datos) {
                if (datos.estadoVigencia === 'vencido') {
                    $(fila).addClass('fila-vencida');
                }
            },
            columns: [
                {
                    data: 'accion',
                    width: '34%',
                    render: function (dato, tipo, fila) {
                        if (tipo !== 'display') {
                            return claveCompleta(fila) + ' ' + fila.aplicacion + ' ' + fila.modulo;
                        }
                        var sinEfecto = !fila.aplicacionActiva ? 'Aplicación inactiva' : (!fila.moduloActivo ? 'Módulo inactivo' : '');
                        return ui.clave(claveCompleta(fila)) +
                            '<div class="celda-secundaria">' + App.escaparHtml(fila.aplicacion) + ' › ' + App.escaparHtml(fila.modulo) + '</div>' +
                            (sinEfecto ? '<span class="badge badge-inactivo" title="La excepción no tiene efecto mientras siga inactivo">' +
                                '<i class="bi bi-dash-circle" aria-hidden="true"></i>' + sinEfecto + '</span>' : '');
                    }
                },
                {
                    data: 'tipo',
                    render: function (dato, tipo) {
                        return tipo === 'display' ? htmlTipo(dato) : dato;
                    }
                },
                {
                    data: 'vigenciaHasta',
                    render: function (dato, tipo, fila) {
                        if (tipo === 'sort') {
                            return fila.estadoVigencia === 'permanente' ? '9999-12-31' : (dato || '');
                        }
                        return tipo === 'display' ? htmlVigencia(fila) : fila.estadoVigencia + ' ' + (dato || '');
                    }
                },
                {
                    data: 'motivo',
                    width: '20%',
                    render: function (dato, tipo) {
                        if (tipo !== 'display') {
                            return dato || '';
                        }
                        return dato
                            ? '<span class="celda-recortada" title="' + App.escaparHtml(dato) + '">' + App.escaparHtml(dato) + '</span>'
                            : '<span class="text-muted-soft">Sin motivo</span>';
                    }
                },
                {
                    data: 'otorgadoPor',
                    render: function (dato, tipo, fila) {
                        if (tipo !== 'display') {
                            return (fila.otorgadoPorNombre || '') + ' ' + (dato || '');
                        }
                        var registro = fila.fechaRegistro
                            ? App.formatoFecha(fila.fechaRegistro) + fila.fechaRegistro.slice(10)
                            : '';
                        return (dato
                            ? '<div>' + App.escaparHtml(fila.otorgadoPorNombre || '') + '</div>' + ui.clave(dato)
                            : '<span class="text-muted-soft">Sin registro</span>') +
                            '<div class="celda-secundaria">' + App.escaparHtml(registro) + '</div>';
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

    function actualizarFiltros() {
        var cuenta = { vigente: 0, permanente: 0, vencido: 0 };

        excepciones.forEach(function (e) {
            cuenta[e.estadoVigencia] = (cuenta[e.estadoVigencia] || 0) + 1;
        });

        $('[data-total="todas"]').text(excepciones.length);
        $('[data-total="vigente"]').text(cuenta.vigente);
        $('[data-total="permanente"]').text(cuenta.permanente);
        $('[data-total="vencido"]').text(cuenta.vencido);
    }

    function cargarExcepciones() {
        if (!usuario) {
            return;
        }

        App.api(URL_API, { opcion: '2', nomina: usuario.nominaUsuario })
            .done(function (data) {
                usuario.estatus = data.usuario.estatus;
                excepciones = data.excepciones || [];
                porRol = data.porRol || {};
                hoy = data.hoy;

                var activo = usuario.estatus === 'activo';
                $('#avisoExcepcionesInactivo').toggleClass('d-none', activo);
                $btnNueva.prop('disabled', !activo);

                $('#excepcionesEstado').empty();
                $('#filtroVigencia').removeClass('d-none');
                $tabla.removeClass('d-none');

                if (tabla === null) {
                    inicializarTabla();
                }

                tabla.clear().rows.add(excepciones).draw(false);
                tabla.columns.adjust().responsive.recalc();
                actualizarFiltros();
            })
            .fail(function (mensaje, respuesta) {
                App.error(mensaje, respuesta);
                $('#excepcionesEstado').html(ui.vacio('bi-cloud-slash', 'No se pudieron cargar las excepciones', mensaje));
            });
    }

    $('#filtroVigencia').on('click', '.filtro-pildora', function () {
        filtroVigencia = $(this).attr('data-vigencia') || '';
        $('#filtroVigencia .filtro-pildora').removeClass('active').attr('aria-pressed', 'false');
        $(this).addClass('active').attr('aria-pressed', 'true');

        if (tabla !== null) {
            tabla.draw();
        }
    });

    /* ==================================================================
       Selección de usuario y catálogos
       ================================================================== */
    var selector = App.selectorUsuario({
        alElegir: function (elegido) {
            usuario = $.extend({}, elegido);
            $('#panelExcepciones').removeClass('paso-bloqueado');
            $('#excepcionesEstado').html(ui.cargador('Cargando excepciones…'));
            cargarExcepciones();
        },
        alCambiar: function () {
            usuario = null;
            excepciones = [];
            $btnNueva.prop('disabled', true);
            $('#panelExcepciones').addClass('paso-bloqueado');
            $('#avisoExcepcionesInactivo, #filtroVigencia').addClass('d-none');
            $tabla.addClass('d-none');

            if (tabla !== null) {
                tabla.clear().draw();
            }

            $('#excepcionesEstado').html(ui.vacio('bi-person-lock', 'Elige un usuario',
                'Aquí verás sus excepciones vigentes, permanentes y vencidas.'));
        }
    });

    function cargarCatalogos() {
        return App.api(URL_API, { opcion: '1' })
            .done(function (data) {
                aplicaciones = data.aplicaciones || [];
                modulos = data.modulos || [];
                permisos = data.permisos || [];
                selector.cargar(data.usuarios || [], App.parametroUrl('nomina'));
            })
            .fail(function (mensaje, respuesta) {
                App.error(mensaje, respuesta);
            });
    }

    /* ==================================================================
       Modal: nueva excepción / editar
       ================================================================== */
    var $modal = $('#modalExcepcion');
    var $form = $('#formExcepcion');
    var $mensaje = $('#modalExcepcionMensaje');
    var $btnGuardar = $('#btnGuardarExcepcion');
    var $selAplicacion = $('#excepcionAplicacion');
    var $selModulo = $('#excepcionModulo');
    var $selAccion = $('#excepcionAccion');
    var $vigencia = $('#excepcionVigencia');
    var $permanente = $('#excepcionPermanente');
    var modo = 'alta';

    function claveElegida() {
        if (!$selAplicacion.val() || !$selModulo.val() || !$selAccion.val()) {
            return '';
        }
        return $selAplicacion.val() + '.' + $selModulo.val() + '.' + $selAccion.val();
    }

    function llenarModulos(seleccion) {
        var sistema = $selAplicacion.val();
        var html = '<option value="">' + (sistema ? 'Selecciona…' : 'Primero la aplicación') + '</option>';

        modulos.forEach(function (m) {
            if (m.codigoSistema === sistema) {
                html += '<option value="' + App.escaparHtml(m.codigoModulo) + '">' +
                    App.escaparHtml(m.codigoModulo + ' — ' + m.nombre + (m.activo ? '' : ' (inactivo)')) + '</option>';
            }
        });

        $selModulo.html(html).val(seleccion || '');
        if ($selModulo.val() === null) { $selModulo.val(''); }
        $selModulo.prop('disabled', modo !== 'alta' || !sistema);
    }

    /**
     * Permisos del módulo. En el alta se bloquean los que ya tienen una
     * excepción con efecto (hay que editarla); los que solo tienen una vencida
     * se permiten porque se reutiliza ese registro.
     */
    function llenarAcciones(seleccion) {
        var sistema = $selAplicacion.val();
        var modulo = $selModulo.val();
        var html = '<option value="">' + (modulo ? 'Selecciona…' : 'Primero el módulo') + '</option>';

        permisos.forEach(function (p) {
            if (p.codigoSistema !== sistema || p.codigoModulo !== modulo) {
                return;
            }

            var existente = buscarExcepcion(claveCompleta(p));
            var ocupado = modo === 'alta' && existente && existente.estadoVigencia !== 'vencido';
            var texto = p.accion + (p.descripcion ? ' — ' + p.descripcion : '') +
                (ocupado ? ' (ya tiene excepción)' : (existente && modo === 'alta' ? ' (tenía una vencida)' : ''));

            html += '<option value="' + App.escaparHtml(p.accion) + '"' + (ocupado ? ' disabled' : '') + '>' +
                App.escaparHtml(texto) + '</option>';
        });

        $selAccion.html(html).val(seleccion || '');
        if ($selAccion.val() === null) { $selAccion.val(''); }
        $selAccion.prop('disabled', modo !== 'alta' || !modulo);
    }

    /** Clave elegida y una pista para decidir entre conceder y denegar. */
    function actualizarPista() {
        var clave = claveElegida();
        var $pista = $('#excepcionPista');

        $('#excepcionClavePrevia').text(clave || '—');

        if (!clave) {
            $pista.empty();
            return;
        }

        var permiso = null;
        permisos.forEach(function (p) {
            if (claveCompleta(p) === clave) { permiso = p; }
        });

        var roles = porRol[clave] || [];
        var html = roles.length > 0
            ? '<i class="bi bi-person-badge" aria-hidden="true"></i> Ya tiene este permiso por su rol <strong>' +
                App.escaparHtml(roles.join(', ')) + '</strong>. Denegarlo se lo quitará; concederlo no cambia nada.'
            : '<i class="bi bi-info-circle" aria-hidden="true"></i> No lo tiene por ningún rol. Concederlo se lo dará.';

        if (permiso && (!permiso.aplicacionActiva || !permiso.moduloActivo)) {
            html += '<br><i class="bi bi-exclamation-triangle text-warning" aria-hidden="true"></i> ' +
                (!permiso.aplicacionActiva ? 'La aplicación está inactiva' : 'El módulo está inactivo') +
                ': la excepción no tendrá efecto hasta que se active.';
        }

        var existente = buscarExcepcion(clave);
        if (modo === 'alta' && existente && existente.estadoVigencia === 'vencido') {
            html += '<br><i class="bi bi-clock-history" aria-hidden="true"></i> Tenía una excepción vencida sobre este permiso: se reemplazará con estos datos.';
        }

        $pista.html(html);
    }

    function actualizarVigencia() {
        var permanente = $permanente.is(':checked');
        $vigencia.prop('disabled', permanente).prop('required', !permanente);
        if (permanente) {
            $vigencia.val('');
        }
    }

    function actualizarTipoVisual() {
        $('.excepcion-tipo').each(function () {
            $(this).toggleClass('activo', $(this).find('input').is(':checked'));
        });
        $('#excepcionTipoError').addClass('d-none');
    }

    function prepararModal(nuevoModo, excepcion) {
        modo = nuevoModo;
        var esAlta = modo === 'alta';

        $form[0].reset();
        $form.removeClass('was-validated');
        $mensaje.addClass('d-none').text('');
        $('#excepcionTipoError').addClass('d-none');

        $('#modalExcepcionTitulo').text(esAlta ? 'Nueva excepción' :
            (excepcion.estadoVigencia === 'vencido' ? 'Renovar o editar excepción' : 'Editar excepción'));
        $('#excepcionUsuarioNombre').text(usuario.nombre);
        $('#excepcionUsuarioNomina').text(usuario.nominaUsuario);

        $selAplicacion.html(ui.opcionesAplicaciones(aplicaciones, 'Selecciona…')).prop('disabled', !esAlta);

        // La vigencia no puede ser anterior a hoy (el servidor lo vuelve a validar).
        $vigencia.attr('min', hoy);

        if (esAlta) {
            $selAplicacion.val('');
            llenarModulos('');
            llenarAcciones('');
            $permanente.prop('checked', false);
        } else {
            $selAplicacion.val(excepcion.codigoSistema);
            llenarModulos(excepcion.codigoModulo);
            llenarAcciones(excepcion.accion);
            $('input[name="excepcionTipo"][value="' + excepcion.tipo + '"]').prop('checked', true);
            $('#excepcionMotivo').val(excepcion.motivo || '');
            $permanente.prop('checked', excepcion.estadoVigencia === 'permanente');
            // Una vencida se edita para renovarla: se pide una fecha nueva.
            $vigencia.val(excepcion.estadoVigencia === 'vigente' ? excepcion.vigenciaHasta : '');
        }

        $('#excepcionMotivoContador').text(String($('#excepcionMotivo').val() || '').length);
        actualizarVigencia();
        actualizarTipoVisual();
        actualizarPista();
        bootstrap.Modal.getOrCreateInstance($modal[0]).show();
    }

    $btnNueva.on('click', function () {
        if (usuario) {
            prepararModal('alta');
        }
    });

    $modal.on('shown.bs.modal', function () {
        (modo === 'alta' ? $selAplicacion : $('#excepcionMotivo')).trigger('focus');
    });

    $selAplicacion.on('change', function () {
        llenarModulos('');
        llenarAcciones('');
        actualizarPista();
    });

    $selModulo.on('change', function () {
        llenarAcciones('');
        actualizarPista();
    });

    $selAccion.on('change', actualizarPista);
    $permanente.on('change', actualizarVigencia);
    $form.on('change', 'input[name="excepcionTipo"]', actualizarTipoVisual);

    $('#excepcionMotivo').on('input', function () {
        $('#excepcionMotivoContador').text(this.value.length);
    });

    $form.on('input change', function () {
        $mensaje.addClass('d-none');
    });

    $form.on('submit', function (evento) {
        evento.preventDefault();
        $('#excepcionMotivo').val($.trim($('#excepcionMotivo').val()));

        var tipo = $('input[name="excepcionTipo"]:checked').val();

        if (!this.checkValidity() || !tipo) {
            $form.addClass('was-validated');
            $('#excepcionTipoError').toggleClass('d-none', !!tipo);
            $form.find(':invalid').first().trigger('focus');
            return;
        }

        ui.botonOcupado($btnGuardar, true);

        App.api(URL_API, {
            opcion: modo === 'alta' ? '3' : '4',
            nomina: usuario.nominaUsuario,
            codigoSistema: $selAplicacion.val(),
            codigoModulo: $selModulo.val(),
            accion: $selAccion.val(),
            tipo: tipo,
            motivo: $('#excepcionMotivo').val(),
            vigenciaHasta: $permanente.is(':checked') ? '' : $vigencia.val()
        })
            .done(function (data, respuesta) {
                bootstrap.Modal.getOrCreateInstance($modal[0]).hide();
                App.exito(respuesta.mensaje);
                cargarExcepciones();
                cargarCatalogos();   // actualiza los contadores del buscador
            })
            .fail(function (mensaje) {
                $mensaje.text(mensaje).removeClass('d-none');
            })
            .always(function () {
                ui.botonOcupado($btnGuardar, false);
            });
    });

    /* ==================================================================
       Retirar y eliminar
       ================================================================== */
    function accionSobreExcepcion(opcion, e, $boton) {
        $boton.prop('disabled', true);

        App.api(URL_API, {
            opcion: opcion,
            nomina: usuario.nominaUsuario,
            codigoSistema: e.codigoSistema,
            codigoModulo: e.codigoModulo,
            accion: e.accion
        })
            .done(function (data, respuesta) {
                App.exito(respuesta.mensaje);
                cargarExcepciones();
                cargarCatalogos();
            })
            .fail(function (mensaje, respuesta) {
                App.error(mensaje, respuesta);
                $boton.prop('disabled', false);
            });
    }

    $tabla.on('click', '.js-editar', function () {
        var e = buscarExcepcion($(this).attr('data-clave'));
        if (e) { prepararModal('edicion', e); }
    });

    $tabla.on('click', '.js-retirar', function () {
        var $boton = $(this);
        var e = buscarExcepcion($boton.attr('data-clave'));

        if (!e) {
            return;
        }

        App.confirmar({
            titulo: 'Retirar excepción',
            mensaje: '¿Retirar la excepción "' + (e.tipo === 'denegar' ? 'denegar' : 'conceder') + '" sobre ' + claveCompleta(e) + '?',
            detalle: 'Dejará de aplicar desde hoy (cuando el usuario vuelva a iniciar sesión). ' +
                'Se conserva en el historial como vencida, con su motivo.',
            textoConfirmar: 'Sí, retirar',
            peligro: true
        }).done(function () {
            accionSobreExcepcion('5', e, $boton);
        });
    });

    $tabla.on('click', '.js-eliminar', function () {
        var $boton = $(this);
        var e = buscarExcepcion($boton.attr('data-clave'));

        if (!e) {
            return;
        }

        App.confirmar({
            titulo: 'Eliminar del historial',
            mensaje: '¿Eliminar definitivamente la excepción vencida sobre ' + claveCompleta(e) + '?',
            detalle: 'Ya no tiene efecto. Al eliminarla se pierde su motivo y quién la otorgó. Esta acción no se puede deshacer.',
            textoConfirmar: 'Sí, eliminar',
            peligro: true
        }).done(function () {
            accionSobreExcepcion('6', e, $boton);
        });
    });

    cargarCatalogos();
})();
