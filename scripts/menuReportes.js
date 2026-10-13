(function () {
    /*
     * Reportes de asistencia y de puntos de cambio (pages/menuReportes.php).
     * Datos: api/operacionesReportes.php
     *   opción 1: catálogos de filtros · opción 2: asistencia · opción 3: puntos de cambio.
     */
    const vista = document.getElementById('reports');

    if (!vista) {
        return;
    }

    const RUTA_API = './api/operacionesReportes.php';

    /* Estatus de asistencia que cuentan como "presente" (mismo criterio que el servidor). */
    const ESTATUS_PRESENTE = ['1', '8'];

    /* Tipo de cambio de los PC de mano de obra (SPC_PUNTOS_CAMBIO.tipo_cambio).
       Mismos valores que obtenerTextoTipoCambio() en scripts/gestionLinea.js. */
    const TIPOS_CAMBIO_MO = {
        '1': 'Inesperado',
        '2': 'Programado',
        '3': 'Otro',
        '4': 'Después de un largo periodo',
        '5': 'Nuevo/Sin experiencia'
    };

    /* Color de la distribución por tipo 4M (clases analytics-legend-dot), igual que los íconos del layout */
    const COLOR_TIPO_4M = { MO: 'cyan', MA: 'warning', MP: 'success', ME: 'primary' };

    const IDIOMA_TABLA = {
        processing: 'Cargando registros...',
        search: 'Buscar:',
        lengthMenu: 'Mostrar _MENU_ registros',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
        infoEmpty: 'No hay registros',
        infoFiltered: '(filtrados de _MAX_ registros)',
        zeroRecords: 'No se encontraron registros',
        emptyTable: 'No hay registros para los filtros seleccionados',
        paginate: { first: 'Primero', previous: 'Anterior', next: 'Siguiente', last: 'Último' }
    };

    const el = {
        pestanas: Array.from(vista.querySelectorAll('[data-report-tab]')),
        asistencia: {
            form: vista.querySelector('#formReporteAsistencia'),
            desde: vista.querySelector('#asistenciaDesde'),
            hasta: vista.querySelector('#asistenciaHasta'),
            linea: vista.querySelector('#asistenciaLinea'),
            turno: vista.querySelector('#asistenciaTurno'),
            porcentaje: vista.querySelector('#asisKpiPorcentaje'),
            registros: vista.querySelector('#asisKpiRegistros'),
            personas: vista.querySelector('#asisKpiPersonas'),
            faltas: vista.querySelector('#asisKpiFaltas'),
            otras: vista.querySelector('#asisKpiOtras'),
            porEstatus: vista.querySelector('#asistenciaPorEstatus'),
            aviso: vista.querySelector('#asistenciaAviso'),
            conteo: vista.querySelector('#asistenciaConteo'),
            tabla: vista.querySelector('#tablaReporteAsistencia')
        },
        pc: {
            form: vista.querySelector('#formReportePC'),
            desde: vista.querySelector('#pcDesde'),
            hasta: vista.querySelector('#pcHasta'),
            linea: vista.querySelector('#pcLinea'),
            tipo: vista.querySelector('#pcTipo'),
            total: vista.querySelector('#pcKpiTotal'),
            activos: vista.querySelector('#pcKpiActivos'),
            finalizados: vista.querySelector('#pcKpiFinalizados'),
            cancelados: vista.querySelector('#pcKpiCancelados'),
            porTipo: vista.querySelector('#pcPorTipo'),
            aviso: vista.querySelector('#pcAviso'),
            conteo: vista.querySelector('#pcConteo'),
            tabla: vista.querySelector('#tablaReportePC')
        }
    };

    let tipos4M = [];                 //Catálogo SPC_TIPO_4MPC: [{clave, nombre}]
    let tablaAsistencia = null;
    let tablaPC = null;
    let tokenAsistencia = 0;          //Descartan respuestas atrasadas si se actualiza varias veces
    let tokenPC = 0;

    function escaparHtml(valor) {
        return String(valor ?? '').replace(/[&<>"']/g, function (caracter) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[caracter];
        });
    }

    /* "AAAA-MM-DD HH:MM" a "DD/MM/AAAA HH:MM" sin pasar por Date (evita desfases de zona horaria) */
    function formatearFechaHora(texto) {
        const [fecha, hora] = String(texto || '').split(' ');
        const partes = (fecha || '').split('-');
        return partes.length === 3 ? `${partes[2]}/${partes[1]}/${partes[0]}${hora ? ' ' + hora : ''}` : '';
    }

    /* Coloca el primer y último día del mes actual en un par de campos de fecha */
    function ponerMesActual(campoDesde, campoHasta) {
        const hoy = new Date();
        campoDesde.value = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toLocaleDateString('sv-SE');
        campoHasta.value = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0).toLocaleDateString('sv-SE');
    }

    /* El rango debe ser válido; si no, se avisa junto al campo "Hasta" */
    function rangoValido(campoDesde, campoHasta) {
        campoHasta.setCustomValidity(
            campoDesde.value && campoHasta.value && campoDesde.value > campoHasta.value
                ? 'La fecha "Hasta" debe ser igual o posterior a "Desde".'
                : ''
        );
        return campoDesde.reportValidity() && campoHasta.reportValidity();
    }

    async function consultarReporte(opcion, parametros) {
        const datos = new FormData();
        datos.append('opcion', opcion);

        Object.keys(parametros || {}).forEach(function (nombre) {
            datos.append(nombre, parametros[nombre]);
        });

        const respuesta = await fetch(RUTA_API, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: datos
        });
        const resultado = await respuesta.json();

        if (!respuesta.ok || resultado.status !== 'ok') {
            throw new Error(resultado.mensaje || 'No fue posible generar el reporte.');
        }

        return resultado.data;
    }

    function mostrarAviso(campo, texto, esError) {
        campo.textContent = texto;
        campo.classList.toggle('text-danger', Boolean(esError));
    }

    function porcentaje(valor, total) {
        return total > 0 ? Math.round(valor * 1000 / total) / 10 : 0;
    }

    /* Pestañas: muestra el panel elegido y reajusta las tablas (DataTables mide mal si estaban ocultas) */
    function activarPestana(boton) {
        el.pestanas.forEach(function (pestana) {
            const activa = pestana === boton;
            pestana.classList.toggle('is-active', activa);
            pestana.setAttribute('aria-selected', activa ? 'true' : 'false');
            document.getElementById(pestana.dataset.reportTab).hidden = !activa;
        });

        if (tablaAsistencia) tablaAsistencia.columns.adjust();
        if (tablaPC) tablaPC.columns.adjust();
    }

    /* ================================================================
       REPORTE DE ASISTENCIA
       ================================================================ */
    function clasePorEstatusAsistencia(clave) {
        if (ESTATUS_PRESENTE.indexOf(String(clave)) !== -1) return '';   //verde (estilo base)
        if (String(clave) === '2' || String(clave) === '7') return 'is-danger';
        return 'is-warning';
    }

    function colorLeyendaAsistencia(clave) {
        if (ESTATUS_PRESENTE.indexOf(String(clave)) !== -1) return 'success';
        if (String(clave) === '2' || String(clave) === '7') return 'danger';
        if (String(clave) === '6') return 'muted';
        return 'warning';
    }

    function crearTablaAsistencia() {
        tablaAsistencia = $(el.asistencia.tabla).DataTable({
            data: [],
            order: [],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            scrollX: true,
            language: IDIOMA_TABLA,
            columns: [
                { data: 'fecha', render: function (dato, tipo) { return tipo === 'display' ? escaparHtml(formatearFechaHora(dato)) : dato; } },
                { data: null, render: function (fila) { return escaparHtml(fila.nombre_linea || fila.codigo_linea || ''); } },
                { data: 'turno', render: function (dato) { return escaparHtml(dato ? 'Turno ' + dato : ''); } },
                { data: 'nomina', render: function (dato) { return escaparHtml(dato); } },
                { data: 'nombre', className: 'text-wrap', render: function (dato) { return escaparHtml(dato); } },
                { data: 'nombres_estaciones', className: 'text-wrap', render: function (dato) { return escaparHtml(dato); } },
                {
                    data: null,
                    render: function (fila, tipo) {
                        const texto = fila.estatus || fila.clave_estatus || 'Sin estatus';
                        return tipo === 'display'
                            ? `<span class="analytics-status ${clasePorEstatusAsistencia(fila.clave_estatus)}">${escaparHtml(texto)}</span>`
                            : texto;
                    }
                },
                { data: 'comentario', className: 'text-wrap', render: function (dato) { return escaparHtml(dato); } }
            ]
        });
    }

    async function generarReporteAsistencia() {
        const a = el.asistencia;

        if (!rangoValido(a.desde, a.hasta)) return;

        const tokenActual = ++tokenAsistencia;
        mostrarAviso(a.aviso, 'Consultando registros...', false);

        try {
            const datos = await consultarReporte('2', {
                fechaInicio: a.desde.value,
                fechaFin: a.hasta.value,
                codigoLinea: a.linea.value,
                turno: a.turno.value
            });

            if (tokenActual !== tokenAsistencia) return;

            const t = datos.totales;
            a.porcentaje.textContent = t.porcentajeAsistencia === null ? '--' : t.porcentajeAsistencia + '%';
            a.registros.textContent = t.registros;
            a.personas.textContent = t.personas + (t.personas === 1 ? ' persona' : ' personas');
            a.faltas.textContent = t.faltasInjustificadas;
            a.otras.textContent = t.otrasAusencias;

            //Cada estatus en un chip con su cantidad y % juntos (estilo .reporte-distribucion)
            a.porEstatus.innerHTML = datos.porEstatus.map(function (fila) {
                return `
                    <div>
                        <span class="analytics-legend-dot is-${colorLeyendaAsistencia(fila.clave)}"></span>
                        <span>${escaparHtml(fila.nombre)}</span>
                        <strong>${fila.total} <small>(${porcentaje(fila.total, t.registros)}%)</small></strong>
                    </div>`;
            }).join('');

            tablaAsistencia.clear().rows.add(datos.registros).draw();
            a.conteo.textContent = datos.registros.length + (datos.registros.length === 1 ? ' registro' : ' registros');
            mostrarAviso(a.aviso, datos.limitado
                ? 'Se muestran los registros más recientes; reduce el periodo para ver el resto.'
                : 'Registros del periodo, del más reciente al más antiguo.', false);
        } catch (error) {
            if (tokenActual !== tokenAsistencia) return;
            console.error(error);
            mostrarAviso(a.aviso, error.message || 'No fue posible generar el reporte de asistencia.', true);
        }
    }

    /* ================================================================
       REPORTE DE PUNTOS DE CAMBIO
       ================================================================ */
    function nombreTipo4M(clave) {
        const tipo = tipos4M.find(function (item) { return item.clave === clave; });
        return tipo ? tipo.nombre : clave;
    }

    function clasePorEstatusPC(clave) {
        if (String(clave) === '1') return 'is-warning';   //Activo
        if (String(clave) === '2') return 'is-danger';    //Cancelado
        return '';                                         //Finalizado (verde)
    }

    function crearTablaPC() {
        const fecha = function (dato, tipo) { return tipo === 'display' ? escaparHtml(formatearFechaHora(dato)) : (dato || ''); };

        tablaPC = $(el.pc.tabla).DataTable({
            data: [],
            order: [],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            scrollX: true,
            language: IDIOMA_TABLA,
            columns: [
                { data: 'no_controlCambio', render: function (dato) { return escaparHtml(dato); } },
                { data: 'clave4M', render: function (dato) { return escaparHtml(nombreTipo4M(dato)); } },
                { data: null, render: function (fila) { return escaparHtml(fila.nombre_linea || fila.codigo_linea || ''); } },
                { data: 'nomina', render: function (dato) { return escaparHtml(dato); } },
                { data: 'nombre', className: 'text-wrap', render: function (dato) { return escaparHtml(dato); } },
                { data: 'motivo', className: 'text-wrap', render: function (dato) { return escaparHtml(dato); } },
                { data: 'fechaHora_inicio', render: fecha },
                { data: 'fechaHora_fin', render: fecha },
                {
                    //Mano de obra: tipo de cambio del registro; PC 3M: tipo de cambio de su planeación
                    data: null,
                    render: function (fila) {
                        return escaparHtml(fila.clave4M === 'MO'
                            ? (TIPOS_CAMBIO_MO[String(fila.tipo_cambio)] || 'Sin definir')
                            : (fila.tipo_cambio_3m || 'Sin planeación'));
                    }
                },
                {
                    data: null,
                    render: function (fila, tipo) {
                        const texto = fila.estatus || 'Sin estatus';
                        return tipo === 'display'
                            ? `<span class="analytics-status ${clasePorEstatusPC(fila.estatusPC)}">${escaparHtml(texto)}</span>`
                            : texto;
                    }
                }
            ]
        });
    }

    async function generarReportePC() {
        const p = el.pc;

        if (!rangoValido(p.desde, p.hasta)) return;

        const tokenActual = ++tokenPC;
        mostrarAviso(p.aviso, 'Consultando puntos de cambio...', false);

        try {
            const datos = await consultarReporte('3', {
                fechaInicio: p.desde.value,
                fechaFin: p.hasta.value,
                codigoLinea: p.linea.value,
                clave4M: p.tipo.value
            });

            if (tokenActual !== tokenPC) return;

            const t = datos.totales;
            p.total.textContent = t.total;
            p.activos.textContent = t.activos;
            p.finalizados.textContent = t.finalizados;
            p.cancelados.textContent = t.cancelados;

            //Distribución por tipo: todos los tipos del catálogo, aunque tengan cero
            const totalTipos = Object.values(datos.porTipo).reduce(function (suma, valor) { return suma + valor; }, 0);
            p.porTipo.innerHTML = tipos4M.map(function (tipo) {
                const cantidad = datos.porTipo[tipo.clave] || 0;
                return `
                    <div>
                        <span class="analytics-legend-dot is-${COLOR_TIPO_4M[tipo.clave] || 'muted'}"></span>
                        <span>${escaparHtml(tipo.nombre)}</span>
                        <strong>${cantidad} <small>(${porcentaje(cantidad, totalTipos)}%)</small></strong>
                    </div>`;
            }).join('');

            tablaPC.clear().rows.add(datos.registros).draw();
            p.conteo.textContent = datos.registros.length + (datos.registros.length === 1 ? ' registro' : ' registros');
            mostrarAviso(p.aviso, datos.limitado
                ? 'Se muestran los puntos de cambio más recientes; reduce el periodo para ver el resto.'
                : 'Puntos de cambio del periodo, del más reciente al más antiguo.', false);
        } catch (error) {
            if (tokenActual !== tokenPC) return;
            console.error(error);
            mostrarAviso(p.aviso, error.message || 'No fue posible generar el reporte de puntos de cambio.', true);
        }
    }

    /* ================================================================
       INICIO
       ================================================================ */
    function llenarSelect(select, opciones, valor, texto) {
        opciones.forEach(function (opcion) {
            select.appendChild(new Option(opcion[texto], opcion[valor]));
        });
    }

    async function inicializar() {
        ponerMesActual(el.asistencia.desde, el.asistencia.hasta);
        ponerMesActual(el.pc.desde, el.pc.hasta);
        crearTablaAsistencia();
        crearTablaPC();

        try {
            const catalogos = await consultarReporte('1');
            tipos4M = catalogos.tipos4M || [];
            llenarSelect(el.asistencia.linea, catalogos.lineas || [], 'codigo_linea', 'nombre_linea');
            llenarSelect(el.pc.linea, catalogos.lineas || [], 'codigo_linea', 'nombre_linea');
            llenarSelect(el.pc.tipo, tipos4M, 'clave', 'nombre');
        } catch (error) {
            console.error(error);
            mostrarAviso(el.asistencia.aviso, error.message || 'No fue posible cargar los filtros.', true);
        }

        generarReporteAsistencia();
        generarReportePC();
    }

    el.pestanas.forEach(function (boton) {
        boton.addEventListener('click', function () { activarPestana(boton); });
    });

    el.asistencia.form.addEventListener('submit', function (evento) {
        evento.preventDefault();
        generarReporteAsistencia();
    });

    el.pc.form.addEventListener('submit', function (evento) {
        evento.preventDefault();
        generarReportePC();
    });

    //Al corregir una fecha se quita el aviso de rango inválido
    [el.asistencia.desde, el.asistencia.hasta, el.pc.desde, el.pc.hasta].forEach(function (campo) {
        campo.addEventListener('input', function () {
            el.asistencia.hasta.setCustomValidity('');
            el.pc.hasta.setCustomValidity('');
        });
    });

    //Cualquier cambio en los filtros actualiza su reporte (el botón Actualizar sigue disponible
    //para volver a consultar sin cambiar filtros). Si el rango es inválido no se consulta.
    [el.asistencia.desde, el.asistencia.hasta, el.asistencia.linea, el.asistencia.turno].forEach(function (campo) {
        campo.addEventListener('change', generarReporteAsistencia);
    });

    [el.pc.desde, el.pc.hasta, el.pc.linea, el.pc.tipo].forEach(function (campo) {
        campo.addEventListener('change', generarReportePC);
    });

    inicializar();
})();
