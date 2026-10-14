/**
 * assets/js/permisosEfectivos.js — Permisos efectivos (pages/permisosEfectivos.php).
 *
 * Habla con api/operacionesPermisosEfectivos.php:
 *   1 catálogo de usuarios · 2 resolución completa de un usuario
 *
 * Solo lectura. Todos los filtros (resultado, aplicación, módulo, origen)
 * trabajan sobre los datos de cada fila con $.fn.dataTable.ext.search.
 */
(function () {
    'use strict';

    var URL_API = 'api/operacionesPermisosEfectivos.php';
    var ui = App.ui;
    var ORIGEN_EXCEPCION = '__excepcion__';

    var $tabla = $('#tablaEfectivos');
    var $filAplicacion = $('#filtroEfAplicacion');
    var $filModulo = $('#filtroEfModulo');
    var $filOrigen = $('#filtroEfRol');
    var tabla = null;

    var usuario = null;
    var filas = [];
    var roles = [];
    var filtroResultado = '';

    /* ==================================================================
       Presentación
       ================================================================== */
    function htmlResultado(resultado) {
        if (resultado === 'permitido') {
            return '<span class="badge badge-activo"><i class="bi bi-check-circle" aria-hidden="true"></i>Permitido</span>';
        }
        if (resultado === 'denegado') {
            return '<span class="badge badge-denegar"><i class="bi bi-slash-circle" aria-hidden="true"></i>Denegado</span>';
        }
        return '<span class="badge badge-vencido"><i class="bi bi-calendar-x" aria-hidden="true"></i>Vencido</span>' +
            '<div class="celda-secundaria">Sin efecto</div>';
    }

    /** Origen con sus piezas: roles que lo dan y excepción individual. */
    function htmlOrigen(f) {
        var partes = [];

        if (f.roles.length > 0) {
            partes.push('<div><span class="celda-secundaria">' + (f.roles.length === 1 ? 'Rol' : 'Roles') + ':</span> ' +
                f.roles.map(function (r) { return ui.clave(r); }).join(' ') + '</div>');
        }

        if (f.excepcion) {
            var vigencia = f.vigenciaHasta
                ? (f.resultado === 'vencido' ? 'venció ' : 'hasta ') + App.formatoFecha(f.vigenciaHasta)
                : 'permanente';
            var etiqueta = f.excepcion === 'denegar'
                ? '<span class="badge badge-denegar"><i class="bi bi-slash-circle" aria-hidden="true"></i>Denegado individualmente</span>'
                : '<span class="badge badge-conceder"><i class="bi bi-check-circle" aria-hidden="true"></i>Concedido individualmente</span>';

            partes.push('<div>' + etiqueta + ' <span class="celda-secundaria">' + App.escaparHtml(vigencia) + '</span></div>');
        }

        return partes.join('');
    }

    /* ==================================================================
       Filtros
       ================================================================== */
    $.fn.dataTable.ext.search.push(function (ajustes, datos, indice, f) {
        if (ajustes.nTable !== $tabla[0]) {
            return true;
        }

        if (filtroResultado && f.resultado !== filtroResultado) {
            return false;
        }

        var sistema = $filAplicacion.val();
        if (sistema && f.codigoSistema !== sistema) {
            return false;
        }

        var modulo = $filModulo.val();
        if (modulo && f.codigoModulo !== modulo) {
            return false;
        }

        var origen = $filOrigen.val();
        if (origen === ORIGEN_EXCEPCION) {
            return !!f.excepcion;
        }
        if (origen) {
            // Valor "SISTEMA|codigoRol": el rol tiene que ser de la misma aplicación.
            var partes = origen.split('|');
            return f.codigoSistema === partes[0] && f.roles.indexOf(partes[1]) !== -1;
        }

        return true;
    });

    function opcionesUnicas(lista, clave, texto) {
        var vistos = {};
        var html = '';

        lista.forEach(function (elemento) {
            var valor = elemento[clave];
            if (!vistos[valor]) {
                vistos[valor] = true;
                html += '<option value="' + App.escaparHtml(valor) + '">' + App.escaparHtml(texto(elemento)) + '</option>';
            }
        });

        return html;
    }

    function llenarFiltroModulos() {
        var sistema = $filAplicacion.val();
        var lista = filas.filter(function (f) { return !sistema || f.codigoSistema === sistema; });

        $filModulo.html('<option value="">Todos</option>' + opcionesUnicas(lista, 'codigoModulo', function (f) {
            return (sistema ? '' : f.codigoSistema + ' · ') + f.codigoModulo + ' — ' + f.modulo;
        }));
    }

    function llenarFiltros() {
        $filAplicacion.html('<option value="">Todas</option>' + opcionesUnicas(filas, 'codigoSistema', function (f) {
            return f.codigoSistema + ' — ' + f.aplicacion;
        }));

        llenarFiltroModulos();

        $filOrigen.html('<option value="">Cualquiera</option>' +
            '<option value="' + ORIGEN_EXCEPCION + '">Solo excepciones individuales</option>' +
            roles.map(function (r) {
                return '<option value="' + App.escaparHtml(r.codigoSistema + '|' + r.codigoRol) + '">' +
                    App.escaparHtml('Rol ' + r.nombreRol + ' (' + r.codigoSistema + '.' + r.codigoRol + ')' +
                        (r.rolActivo ? '' : ' — inactivo')) + '</option>';
            }).join(''));
    }

    function actualizarTotales() {
        var cuenta = { permitido: 0, denegado: 0, vencido: 0 };
        filas.forEach(function (f) { cuenta[f.resultado]++; });

        $('[data-total="todos"]').text(filas.length);
        $('[data-total="permitido"]').text(cuenta.permitido);
        $('[data-total="denegado"]').text(cuenta.denegado);
        $('[data-total="vencido"]').text(cuenta.vencido);
    }

    function redibujar() {
        if (tabla !== null) {
            tabla.draw();
        }
    }

    $('#filtroResultado').on('click', '.filtro-pildora', function () {
        filtroResultado = $(this).attr('data-resultado') || '';
        $('#filtroResultado .filtro-pildora').removeClass('active').attr('aria-pressed', 'false');
        $(this).addClass('active').attr('aria-pressed', 'true');
        redibujar();
    });

    $filAplicacion.on('change', function () {
        llenarFiltroModulos();
        redibujar();
    });

    $filModulo.add($filOrigen).on('change', redibujar);

    /* ==================================================================
       Resumen por aplicación
       ================================================================== */
    function pintarAplicaciones(aplicaciones) {
        var $cuerpo = $('#efectivosAplicacionesCuerpo');
        $('#efectivosAplicaciones').removeClass('d-none');

        if (aplicaciones.length === 0) {
            $cuerpo.html('<p class="text-muted-soft mb-0">No tiene roles ni permisos en ninguna aplicación.</p>');
            return;
        }

        $cuerpo.html('<div class="efectivos-apps">' + aplicaciones.map(function (a) {
            var acceso = a.permitidos > 0
                ? '<span class="badge badge-activo"><i class="bi bi-check-circle" aria-hidden="true"></i>' + a.permitidos + ' permitido(s)</span>'
                : '<span class="badge badge-inactivo"><i class="bi bi-dash-circle" aria-hidden="true"></i>Sin permisos</span>';

            var rolesHtml = a.roles.length === 0
                ? '<span class="celda-secundaria">Sin roles; solo excepciones individuales.</span>'
                : a.roles.map(function (r) {
                    return '<span class="efectivos-rol' + (r.activo ? '' : ' efectivos-rol-inactivo') + '" title="' +
                        App.escaparHtml(r.activo ? r.nombreRol : r.nombreRol + ' (inactivo: no aporta permisos)') + '">' +
                        App.escaparHtml(r.nombreRol) + '</span>';
                }).join(' ');

            return '<div class="efectivos-app">' +
                '<button type="button" class="efectivos-app-titulo js-filtrar-app" data-sistema="' + App.escaparHtml(a.codigoSistema) + '" ' +
                    'title="Ver solo esta aplicación en la tabla">' +
                    ui.clave(a.codigoSistema) + ' <span>' + App.escaparHtml(a.nombre) + '</span></button>' +
                '<div class="efectivos-app-estado">' + acceso +
                    (a.denegados > 0 ? ' <span class="badge badge-denegar">' + a.denegados + ' denegado(s)</span>' : '') +
                    (a.activo ? '' : ' <span class="badge badge-inactivo">Aplicación inactiva</span>') +
                '</div>' +
                '<div class="efectivos-app-roles">' + rolesHtml + '</div>' +
                '</div>';
        }).join('') + '</div>');
    }

    $('#efectivosAplicacionesCuerpo').on('click', '.js-filtrar-app', function () {
        var sistema = $(this).attr('data-sistema');

        $filAplicacion.val(sistema);
        if ($filAplicacion.val() === null) {
            // La aplicación no tiene permisos resueltos (solo roles sin permisos).
            App.info('Esa aplicación no tiene permisos para mostrar en la tabla.');
            $filAplicacion.val('');
            return;
        }

        llenarFiltroModulos();
        redibujar();
        $('#panelEfectivos')[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    /* ==================================================================
       Tabla
       ================================================================== */
    function inicializarTabla() {
        tabla = $tabla.DataTable({
            data: [],
            language: $.extend({}, App.TEXTOS_TABLA, {
                emptyTable: 'Este usuario no tiene ningún permiso en aplicaciones activas.'
            }),
            responsive: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Todos']],
            order: [[0, 'asc'], [1, 'asc'], [2, 'asc']],
            columnDefs: [
                { responsivePriority: 1, targets: 2 },
                { responsivePriority: 2, targets: 3 },
                { responsivePriority: 3, targets: 4 }
            ],
            createdRow: function (fila, datos) {
                if (datos.resultado === 'vencido') {
                    $(fila).addClass('fila-vencida');
                }
            },
            columns: [
                {
                    data: 'codigoSistema',
                    width: '8%',
                    render: function (dato, tipo, f) {
                        if (tipo !== 'display') {
                            return tipo === 'filter' ? dato + ' ' + f.aplicacion : dato;
                        }
                        // Solo el código: el nombre completo está en el resumen y en el title.
                        return '<span title="' + App.escaparHtml(f.aplicacion) + '">' + ui.clave(dato) + '</span>';
                    }
                },
                {
                    data: 'codigoModulo',
                    width: '22%',
                    render: function (dato, tipo, f) {
                        if (tipo !== 'display') {
                            return tipo === 'filter' ? dato + ' ' + f.modulo : dato;
                        }
                        return '<div>' + App.escaparHtml(f.modulo) + '</div>' + ui.clave(dato);
                    }
                },
                {
                    data: 'accion',
                    width: '32%',
                    render: function (dato, tipo, f) {
                        if (tipo !== 'display') {
                            return tipo === 'filter' ? dato + ' ' + (f.descripcion || '') : dato;
                        }
                        return ui.clave(dato) +
                            (f.descripcion ? '<div class="celda-secundaria">' + App.escaparHtml(f.descripcion) + '</div>' : '');
                    }
                },
                {
                    data: 'resultado',
                    width: '12%',
                    render: function (dato, tipo) {
                        return tipo === 'display' ? htmlResultado(dato) : dato;
                    }
                },
                {
                    data: 'origen',
                    orderable: false,
                    render: function (dato, tipo, f) {
                        return tipo === 'display' ? htmlOrigen(f) : dato + ' ' + f.roles.join(' ');
                    }
                }
            ]
        });
    }

    function limpiar() {
        usuario = null;
        filas = [];
        roles = [];
        $('#panelEfectivos').addClass('paso-bloqueado');
        $('#efectivosAplicaciones, #avisoEfectivosInactivo, #filtroResultado, #filtrosEfectivos').addClass('d-none');
        $tabla.addClass('d-none');

        if (tabla !== null) {
            tabla.clear().draw();
        }

        $('#efectivosEstado').html(ui.vacio('bi-shield-check', 'Elige un usuario',
            'Verás cada permiso con su resultado y de dónde lo obtiene.'));
    }

    function cargar(nomina) {
        $('#panelEfectivos').removeClass('paso-bloqueado');
        $('#efectivosEstado').html(ui.cargador('Calculando sus permisos…'));

        App.api(URL_API, { opcion: '2', nomina: nomina })
            .done(function (data) {
                usuario = data.usuario;
                filas = data.permisos || [];
                roles = data.roles || [];

                $('#avisoEfectivosInactivo').toggleClass('d-none', usuario.estatus === 'activo');
                pintarAplicaciones(data.aplicaciones || []);

                // Al cambiar de usuario los filtros vuelven a "todos".
                filtroResultado = '';
                $('#filtroResultado .filtro-pildora').removeClass('active').attr('aria-pressed', 'false')
                    .filter('[data-resultado=""]').addClass('active').attr('aria-pressed', 'true');
                llenarFiltros();
                actualizarTotales();

                $('#efectivosEstado').empty();
                $('#filtroResultado, #filtrosEfectivos').removeClass('d-none');
                $tabla.removeClass('d-none');

                if (tabla === null) {
                    inicializarTabla();
                }

                tabla.clear().rows.add(filas).draw();
                tabla.columns.adjust().responsive.recalc();
            })
            .fail(function (mensaje, respuesta) {
                App.error(mensaje, respuesta);
                $('#efectivosEstado').html(ui.vacio('bi-cloud-slash', 'No se pudieron calcular los permisos', mensaje));
            });
    }

    var selector = App.selectorUsuario({
        alElegir: function (elegido) {
            cargar(elegido.nominaUsuario);
        },
        alCambiar: limpiar
    });

    App.api(URL_API, { opcion: '1' })
        .done(function (data) {
            selector.cargar(data.usuarios || [], App.parametroUrl('nomina'));
        })
        .fail(function (mensaje, respuesta) {
            App.error(mensaje, respuesta);
        });
})();
