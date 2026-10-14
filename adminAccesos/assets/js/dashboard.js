/**
 * assets/js/dashboard.js — Vista de inicio (pages/dashboard.php).
 *
 * Pide todo el resumen en una sola petición (api/operacionesDashboard.php,
 * opcion 1) y pinta cada bloque. Todo lo que viene de la base se escapa con
 * App.escaparHtml antes de insertarlo.
 */
(function () {
    'use strict';

    var URL_API = 'api/operacionesDashboard.php';

    var $botonActualizar = $('#dashboardActualizar');
    var contenidoBoton = $botonActualizar.html();

    /* Presentación de cada severidad de las revisiones de consistencia. */
    var SEVERIDADES = {
        error: { icono: 'bi-x-octagon-fill',            clase: 'dashboard-revision-error' },
        aviso: { icono: 'bi-exclamation-triangle-fill', clase: 'dashboard-revision-aviso' },
        info:  { icono: 'bi-info-circle-fill',          clase: 'dashboard-revision-info' }
    };

    function htmlCargador(texto) {
        return '<div class="app-loader">' +
            '<div class="spinner-border" role="status"><span class="visually-hidden">Cargando…</span></div>' +
            '<div>' + App.escaparHtml(texto) + '</div></div>';
    }

    function htmlVacio(icono, titulo, texto) {
        return '<div class="empty-state dashboard-vacio">' +
            '<i class="bi ' + icono + '" aria-hidden="true"></i>' +
            '<div class="empty-title">' + App.escaparHtml(titulo) + '</div>' +
            (texto ? '<div>' + App.escaparHtml(texto) + '</div>' : '') +
            '</div>';
    }

    function htmlError() {
        return '<div class="empty-state dashboard-vacio">' +
            '<i class="bi bi-cloud-slash" aria-hidden="true"></i>' +
            '<div class="empty-title">No se pudo cargar esta información</div>' +
            '<div>Usa el botón "Actualizar" para intentarlo de nuevo.</div></div>';
    }

    function htmlClave(texto) {
        return '<span class="clave">' + App.escaparHtml(texto) + '</span>';
    }

    function htmlBadgeTipo(tipo) {
        return tipo === 'denegar'
            ? '<span class="badge badge-denegar"><i class="bi bi-slash-circle" aria-hidden="true"></i>Denegar</span>'
            : '<span class="badge badge-conceder"><i class="bi bi-check-circle" aria-hidden="true"></i>Conceder</span>';
    }

    function htmlBadgeEstatus(estatus) {
        return estatus === 'activo'
            ? '<span class="badge badge-activo"><i class="bi bi-check-circle" aria-hidden="true"></i>Activo</span>'
            : '<span class="badge badge-inactivo"><i class="bi bi-dash-circle" aria-hidden="true"></i>Inactivo</span>';
    }

    function textoDiasRestantes(dias) {
        if (dias <= 0) { return 'Vence hoy'; }
        if (dias === 1) { return 'Vence mañana'; }
        return 'En ' + dias + ' días';
    }

    /* ------------------------------------------------------------------
       Totales
       ------------------------------------------------------------------ */
    function pintarResumen(resumen) {
        var detalles = {
            aplicacionesActivas: 'de ' + resumen.aplicacionesTotal + ' registradas',
            modulosTotal: resumen.modulosActivos + ' activos',
            rolesTotal: resumen.rolesActivos + ' activos'
        };

        ['usuariosActivos', 'usuariosInactivos', 'aplicacionesActivas',
            'modulosTotal', 'rolesTotal', 'permisosTotal'].forEach(function (campo) {
            $('#total-' + campo).text(resumen[campo] !== undefined ? resumen[campo] : '—');
            $('#detalle-' + campo).text(detalles[campo] || '');
        });
    }

    /* ------------------------------------------------------------------
       Excepciones por vencer
       ------------------------------------------------------------------ */
    function pintarPorVencer(filas, dias) {
        var $cuerpo = $('#porVencerCuerpo');

        $('#porVencerDias').text('(próximos ' + dias + ' días)');
        $('#porVencerTotal').text(filas.length);

        if (filas.length === 0) {
            $cuerpo.html(htmlVacio('bi-calendar-check', 'Nada por vencer',
                'Ningún permiso individual vence en los próximos ' + dias + ' días.'));
            return;
        }

        var renglones = filas.map(function (fila) {
            var urgente = fila.diasRestantes <= 3;

            return '<tr>' +
                '<td><div class="fw-semibold">' + App.escaparHtml(fila.nombre) + '</div>' +
                    htmlClave(fila.nominaUsuario) + '</td>' +
                '<td>' + htmlClave(fila.codigoSistema + '.' + fila.codigoModulo + '.' + fila.accion) + '</td>' +
                '<td>' + htmlBadgeTipo(fila.tipo) + '</td>' +
                '<td class="text-nowrap">' + App.escaparHtml(App.formatoFecha(fila.vigenciaHasta)) +
                    '<div><span class="badge ' + (urgente ? 'badge-denegar' : 'badge-por-vencer') + '">' +
                    App.escaparHtml(textoDiasRestantes(fila.diasRestantes)) + '</span></div></td>' +
                '</tr>';
        }).join('');

        $cuerpo.html(
            '<div class="table-responsive">' +
            '<table class="table table-hover align-middle">' +
            '<thead><tr><th>Usuario</th><th>Permiso</th><th>Tipo</th><th>Vence</th></tr></thead>' +
            '<tbody>' + renglones + '</tbody></table></div>'
        );
    }

    /* ------------------------------------------------------------------
       Usuarios sin roles ni permisos
       ------------------------------------------------------------------ */
    function pintarSinAcceso(filas) {
        var $cuerpo = $('#sinAccesoCuerpo');

        $('#sinAccesoTotal').text(filas.length);

        if (filas.length === 0) {
            $cuerpo.html(htmlVacio('bi-people', 'Todo en orden',
                'Todos los usuarios tienen al menos un rol o un permiso individual.'));
            return;
        }

        var elementos = filas.map(function (fila) {
            return '<li class="dashboard-lista-item">' +
                '<div class="dashboard-lista-texto">' +
                    '<div class="fw-semibold">' + App.escaparHtml(fila.nombre) + '</div>' +
                    htmlClave(fila.nominaUsuario) +
                '</div>' +
                htmlBadgeEstatus(fila.estatus) +
                '</li>';
        }).join('');

        $cuerpo.html('<ul class="dashboard-lista">' + elementos + '</ul>');
    }

    /* ------------------------------------------------------------------
       Posibles inconsistencias
       ------------------------------------------------------------------ */
    function pintarInconsistencias(revisiones) {
        var $cuerpo = $('#inconsistenciasCuerpo');
        var conHallazgos = revisiones.filter(function (r) { return r.total > 0; });
        var sinHallazgos = revisiones.filter(function (r) { return r.total === 0; });

        /* El contador del encabezado solo suma lo que hay que corregir o
           revisar; lo informativo no es un problema. */
        var problemas = conHallazgos.filter(function (r) { return r.severidad !== 'info'; }).length;

        $('#inconsistenciasTotal')
            .text(problemas === 0 ? 'Sin problemas' : problemas + ' por revisar')
            .toggleClass('badge-estado-ok', problemas === 0)
            .toggleClass('badge-estado-pend', problemas > 0)
            .toggleClass('badge-estado-neutro', false);

        var html = '';

        if (conHallazgos.length === 0) {
            html += htmlVacio('bi-patch-check', 'No se encontraron inconsistencias', '');
        } else {
            html += '<div class="dashboard-revisiones">' + conHallazgos.map(htmlRevision).join('') + '</div>';
        }

        if (sinHallazgos.length > 0) {
            html += '<div class="dashboard-sin-hallazgos">' +
                '<div class="panel-titulo"><i class="bi bi-check2-all" aria-hidden="true"></i>Revisiones sin hallazgos</div>' +
                '<ul>' + sinHallazgos.map(function (r) {
                    return '<li><i class="bi bi-check-circle" aria-hidden="true"></i>' + App.escaparHtml(r.titulo) + '</li>';
                }).join('') + '</ul></div>';
        }

        $cuerpo.html(html);
    }

    function htmlRevision(revision) {
        var severidad = SEVERIDADES[revision.severidad] || SEVERIDADES.info;
        var idDetalle = 'revision-' + revision.clave;
        var restantes = revision.total - revision.ejemplos.length;

        var ejemplos = revision.ejemplos.map(function (ejemplo) {
            return '<li>' + htmlClave(ejemplo) + '</li>';
        }).join('');

        if (restantes > 0) {
            ejemplos += '<li class="text-muted-soft">… y ' + restantes + ' más. ' +
                'Ejecuta sql/01_diagnostico.sql para ver la lista completa.</li>';
        }

        return '<div class="dashboard-revision ' + severidad.clase + '">' +
            '<div class="dashboard-revision-encabezado">' +
                '<i class="bi ' + severidad.icono + ' dashboard-revision-icono" aria-hidden="true"></i>' +
                '<div class="dashboard-revision-texto">' +
                    '<div class="fw-semibold">' + App.escaparHtml(revision.titulo) +
                        ' <span class="badge dashboard-revision-total">' + revision.total + '</span></div>' +
                    '<div class="dashboard-revision-descripcion">' + App.escaparHtml(revision.descripcion) + '</div>' +
                '</div>' +
                '<button class="btn btn-soft btn-sm collapsed" type="button" data-bs-toggle="collapse" ' +
                    'data-bs-target="#' + idDetalle + '" aria-expanded="false" aria-controls="' + idDetalle + '">' +
                    '<i class="bi bi-list-ul" aria-hidden="true"></i>Ver detalle' +
                '</button>' +
            '</div>' +
            '<div class="collapse" id="' + idDetalle + '">' +
                '<ul class="dashboard-revision-ejemplos">' + ejemplos + '</ul>' +
            '</div>' +
            '</div>';
    }

    /* ------------------------------------------------------------------
       Carga
       ------------------------------------------------------------------ */
    function cargar() {
        $botonActualizar.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Actualizando…'
        );

        App.api(URL_API, { opcion: '1' })
            .done(function (data) {
                pintarResumen(data.resumen || {});
                pintarPorVencer(data.porVencer || [], data.diasVigencia || 0);
                pintarSinAcceso(data.sinAcceso || []);
                pintarInconsistencias(data.inconsistencias || []);
            })
            .fail(function (mensaje, respuesta) {
                App.error(mensaje, respuesta);
                $('#porVencerCuerpo, #sinAccesoCuerpo, #inconsistenciasCuerpo').html(htmlError());
                $('[id^="total-"]').text('—');
            })
            .always(function () {
                $botonActualizar.prop('disabled', false).html(contenidoBoton);
            });
    }

    $botonActualizar.on('click', function () {
        $('#porVencerCuerpo, #sinAccesoCuerpo').html(htmlCargador('Cargando…'));
        $('#inconsistenciasCuerpo').html(htmlCargador('Revisando la información…'));
        cargar();
    });

    cargar();
})();
