/*
 * Reportes de anormalidades.
 *
 * Las tarjetas KPI, los catálogos y el histórico mensual se consultan
 * mediante api/operacionesReportesAnormalidades.php. Las gráficas
 * restantes todavía utilizan los datos de prueba definidos en este archivo.
 *
 * Las gráficas se dibujan con CSS y SVG (sin librerías externas), siguiendo
 * el mismo enfoque que css/analytics.css.
 */
(function () {
    'use strict';

    const vista = document.getElementById('reportesAnormalidadesView');

    if (!vista) {
        return;
    }

    const RUTA_API_REPORTES = './api/operacionesReportesAnormalidades.php';

    /*
     * Nombres públicos del enrutador de la API. Se evitan números sin contexto
     * y se conserva en el backend la compatibilidad con las opciones 1, 2 y 3.
     */
    const OPERACIONES_REPORTES = Object.freeze({
        catalogos: 'catalogos',
        lineasPorArea: 'lineas_por_area',
        tarjetasResumen: 'tarjetas_resumen',
        resumenGeneral: 'resumen_general',
        lineasAreas: 'lineas_areas',
        criticasTipos: 'criticas_tipos',
        reincidencias: 'reincidencias',
        reporteDetallado: 'reporte_detallado'
    });

    let solicitudLineasActual = 0;
    let solicitudKpisActual = 0;
    let solicitudHistoricoActual = 0;
    let solicitudHistoricoLineasActual = 0;
    let solicitudAreasResumenActual = 0;
    let solicitudEstatusResumenActual = 0;
    let solicitudHallazgosActual = 0;
    let solicitudCriticidadLineasActual = 0;
    let solicitudTotalesLineasActual = 0;
    let solicitudTendenciaActual = 0;
    let solicitudClasificacion5mActual = 0;
    let solicitudTopCriticasActual = 0;
    let solicitudTopNoCriticasActual = 0;
    let solicitudNivelRespuestaActual = 0;
    let solicitudTopReincidentesActual = 0;
    let solicitudTopEstacionesReincidenciaActual = 0;
    let solicitudFrecuentesActual = 0;
    let solicitudEstacionesGeneracionActual = 0;
    let solicitudEstacionesDeteccionActual = 0;
    let solicitudDetalleReporteActual = 0;

    const MESES = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
                   'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    /* Peso de cada mes sobre el total del año. Simula la estacionalidad. */
    const PESOS_MES = [0.070, 0.075, 0.092, 0.080, 0.086, 0.094,
                       0.098, 0.090, 0.083, 0.081, 0.076, 0.075];

    /*
     * Color de cada línea. La gama (azules y morados) está definida en
     * css/reportesAnormalidades.css para mantener los colores en un solo lugar.
     */
    const COLORES_LINEA = ['var(--ra-serie-1)', 'var(--ra-serie-2)', 'var(--ra-serie-3)',
                           'var(--ra-serie-4)', 'var(--ra-serie-5)', 'var(--ra-serie-6)',
                           'var(--ra-serie-7)'];
    const COLORES_AREA = ['var(--ra-sensor, #4b7ff0)', 'var(--ra-limpio, #3cb6ca)',
                          'var(--primary-light, #4b7ff0)', 'var(--accent-purple, #5225c5)'];
    const COLORES_ESTATUS = {
        '1': 'var(--ra-abierta, #d97706)',
        '2': 'var(--accent-orange, #e67e22)',
        '3': 'var(--primary-light, #4b7ff0)',
        '4': 'var(--accent-cyan, #3cb6ca)',
        '5': 'var(--accent-purple, #5225c5)',
        '6': 'var(--ra-cerrada, #16856b)'
    };

    /* =====================================================================
       DATOS DE PRUEBA
       Estructura equivalente a la que devolverían las consultas sobre
       SA_anormalidad y sus catálogos.
       ===================================================================== */
    const datosMock = {
        /* SPC_LINEAS + SPC_AREAS: líneas agrupadas por área. */
        lineas: [
            { codigo: 'CRV',     nombre: 'CRV 23',    area: 'Sensor',        criticas: 18, noCriticas: 47 },
            { codigo: 'MDX',     nombre: 'MDX',       area: 'Sensor',        criticas: 12, noCriticas: 39 },
            { codigo: 'ODYSSEY', nombre: 'Odyssey',   area: 'Sensor',        criticas: 21, noCriticas: 34 },
            { codigo: 'FORD',    nombre: 'Ford 1',    area: 'Cuarto Limpio', criticas: 15, noCriticas: 41 },
            { codigo: 'ASSY1',   nombre: 'Ford 2',    area: 'Cuarto Limpio', criticas: 9,  noCriticas: 28 },
            { codigo: 'SMT1',   nombre: 'Ford 2',    area: 'Cuarto Limpio', criticas: 9,  noCriticas: 28 },
            { codigo: 'SMT2',   nombre: 'Ford 2',    area: 'Cuarto Limpio', criticas: 9,  noCriticas: 28 },
            { codigo: 'ASSY2',   nombre: 'Ford 2',    area: 'Cuarto Limpio', criticas: 9,  noCriticas: 28 }
        ],

        /* SA_estatus_anormalidad (claves 1 a 6). */
        estatus: [
            { clave: '1', nombre: 'Detección registrada', proporcion: 0.09, color: 'var(--ra-abierta)' },
            { clave: '2', nombre: 'Análisis y contramedida', proporcion: 0.11, color: '#e67e22' },
            { clave: '3', nombre: 'Verificación', proporcion: 0.08, color: 'var(--primary-light)' },
            { clave: '4', nombre: 'Cierre de calidad', proporcion: 0.07, color: 'var(--accent-cyan)' },
            { clave: '5', nombre: 'Cierre de gerente', proporcion: 0.05, color: '#5225c5' },
            { clave: '6', nombre: 'Finalizada', proporcion: 0.60, color: 'var(--ra-cerrada)' }
        ]
    };

    /* ===================== Referencias del DOM ===================== */
    const elementos = {
        formulario: vista.querySelector('#raFiltros'),
        abrirFiltros: vista.querySelector('#raAbrirFiltros'),
        cerrarFiltros: vista.querySelector('#raCerrarFiltros'),
        fechaInicio: vista.querySelector('#raFechaInicio'),
        fechaFin: vista.querySelector('#raFechaFin'),
        area: vista.querySelector('#raArea'),
        linea: vista.querySelector('#raLinea'),
        turno: vista.querySelector('#raTurno'),
        scope: vista.querySelector('#raScope'),
        scopePeriodo: vista.querySelector('#raScopePeriodo'),
        kpis: vista.querySelector('#raKpis'),
        pestanas: Array.prototype.slice.call(vista.querySelectorAll('[data-ra-tab]')),
        paneles: {
            resumen: vista.querySelector('#raPanelResumen'),
            lineas: vista.querySelector('#raPanelLineas'),
            criticas: vista.querySelector('#raPanelCriticas'),
            reincidencias: vista.querySelector('#raPanelReincidencias'),
            reportes: vista.querySelector('#raPanelReportes')
        },
        historicoMeses: vista.querySelector('#raHistoricoMeses'),
        historicoEscala: vista.querySelector('#raHistoricoEscala'),
        historicoLeyenda: vista.querySelector('#raHistoricoLeyenda'),
        historicoTotal: vista.querySelector('#raHistoricoTotal'),
        historicoDescripcion: vista.querySelector('#raHistoricoDescripcion'),
        historicoSubpestanas: Array.prototype.slice.call(vista.querySelectorAll('[data-ra-historico]')),
        areaDonut: vista.querySelector('#raAreaDonut'),
        areaTotal: vista.querySelector('#raAreaTotal'),
        areaLeyenda: vista.querySelector('#raAreaLeyenda'),
        estatusDonut: vista.querySelector('#raEstatusDonut'),
        estatusPorcentaje: vista.querySelector('#raEstatusPorcentaje'),
        estatusLeyenda: vista.querySelector('#raEstatusLeyenda'),
        hallazgos: vista.querySelector('#raHallazgos'),
        criticasPorLinea: vista.querySelector('#raCriticasPorLinea'),
        totalPorLinea: vista.querySelector('#raTotalPorLinea'),
        tendencia: vista.querySelector('#raTendencia'),
        tendenciaLeyenda: vista.querySelector('#raTendenciaLeyenda'),
        tablaCriticas: vista.querySelector('#raTablaEstatusCriticas'),
        cinco5m: vista.querySelector('#ra5m'),
        cinco5mPie: vista.querySelector('#ra5mPie'),
        cinco5mLeyenda: vista.querySelector('#ra5mLeyenda'),
        topCriticas: vista.querySelector('#raTopCriticas'),
        topNoCriticas: vista.querySelector('#raTopNoCriticas'),
        nivelRespuesta: vista.querySelector('#raNivelRespuesta'),
        topReincidentes: vista.querySelector('#raTopReincidentes'),
        topEstacionesReincidencia: vista.querySelector('#raTopEstacionesReincidencia'),
        tipoAnormalidadFrecuente: vista.querySelector('#raTipoAnormalidadFrecuente'),
        tablaFrecuentes: vista.querySelector('#raTablaFrecuentes'),
        modalContramedidasFrecuentes: vista.querySelector('#raModalContramedidasFrecuentes'),
        contextoContramedidasFrecuentes: vista.querySelector('#raContextoContramedidasFrecuentes'),
        tablaDetalleContramedidas: vista.querySelector('#raTablaDetalleContramedidas'),

        /* Sección de reportes: filtros propios, tabla detallada y modal. */
        topEstacionesGeneracion: vista.querySelector('#raTopEstacionesGeneracion'),
        topEstacionesDeteccion: vista.querySelector('#raTopEstacionesDeteccion'),
        reporteFiltros: vista.querySelector('#raReporteFiltros'),
        reporteEstatus: vista.querySelector('#raReporteEstatus'),
        reporteEstatusOpciones: vista.querySelector('#raReporteEstatusOpciones'),
        reporteEstatusTodos: vista.querySelector('[data-ra-estatus-todos]'),
        reporteCriticidad: vista.querySelector('#raReporteCriticidad'),
        reporteLimpiar: vista.querySelector('#raReporteLimpiar'),
        reporteConteo: vista.querySelector('#raReporteConteo'),
        tablaReporte: vista.querySelector('#raTablaReporte'),
        modalReporte: vista.querySelector('#raModalReporteAnormalidad'),
        modalReporteTitulo: vista.querySelector('#raModalReporteTitulo'),
        detalleReporteEstado: vista.querySelector('#raDetalleReporteEstado'),
        detalleReporteContenido: vista.querySelector('#raDetalleReporteContenido'),
        detalleFlujoNormal: vista.querySelector('#raDetalleFlujoNormal'),
        detalleFlujoReincidencia: vista.querySelector('#raDetalleFlujoReincidencia'),
        detalleTotalReincidencias: vista.querySelector('#raDetalleTotalReincidencias'),
        tabFlujoNormal: vista.querySelector('#raTabFlujoNormal')
    };

    /*
     * Ancho mínimo del histórico; por debajo se activa el scroll horizontal.
     * El desglose por línea necesita más espacio porque dibuja varias barras
     * dentro de cada mes.
     */
    const ANCHO_MINIMO_HISTORICO = { total: 700, detalle: 940 };

    /*
     * Ancho objetivo de cada barra del desglose y proporción del mes que ocupa
     * el grupo de barras. Con estos valores se calcula cuánto ancho necesita la
     * gráfica cuando hay muchas líneas registradas.
     */
    const ANCHO_BARRA_DETALLE = 12;
    const PROPORCION_GRUPO = 0.74;

    let tablaEstatusCriticasDT = null;
    let tablaFrecuentesDT = null;
    let tablaDetalleContramedidasDT = null;
    let tablaReporteDT = null;
    let catalogosReporteCargados = false;
    let seriesHistorico = [];
    let historicoTotalServidor = null;
    let historicoLineasServidor = null;
    let areasResumenServidor = null;
    let claveAreasResumenServidor = '';
    let estatusResumenServidor = null;
    let claveEstatusResumenServidor = '';
    let hallazgosServidor = null;
    let claveHallazgosServidor = '';
    let criticidadLineasServidor = null;
    let claveCriticidadLineasServidor = '';
    let totalesLineasServidor = null;
    let claveTotalesLineasServidor = '';
    let tendenciaServidor = null;
    let claveTendenciaServidor = '';
    let clasificacion5mServidor = null;
    let claveClasificacion5mServidor = '';
    let topCriticasServidor = null;
    let claveTopCriticasServidor = '';
    let topNoCriticasServidor = null;
    let claveTopNoCriticasServidor = '';
    let nivelRespuestaServidor = null;
    let claveNivelRespuestaServidor = '';
    let topReincidentesServidor = null;
    let claveTopReincidentesServidor = '';
    let topEstacionesReincidenciaServidor = null;
    let claveTopEstacionesReincidenciaServidor = '';
    let catalogoTiposFrecuentesCargado = false;
    let contextoDetalleContramedidas = null;
    let temporizadorResize = null;
    let temporizadorFiltros = null;
    let modoHistorico = 'total';

    /*
     * Indica qué pestañas ya fueron dibujadas con los filtros actuales. Cuando
     * cambia un filtro se invalidan y sólo se vuelve a cargar la pestaña activa.
     */
    const pestanasCargadas = {
        resumen: false,
        lineas: false,
        criticas: false,
        reincidencias: false,
        reportes: false
    };

    /* ========================== Utilidades ========================== */

    function escaparHtml(texto) {
        return String(texto === null || texto === undefined ? '' : texto)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function porcentaje(parte, total, decimales) {
        const precision = Number.isInteger(decimales) ? decimales : 0;
        const valor = total > 0 ? (parte / total) * 100 : 0;

        return precision > 0 ? valor.toFixed(precision) : Math.round(valor);
    }

    /*
     * Factor por turno. Permite que los números cambien al filtrar y así
     * validar visualmente el comportamiento de la interfaz.
     */
    function factorTurno() {
        const opcionTurno = elementos.turno.options[elementos.turno.selectedIndex];
        const turno = opcionTurno ? opcionTurno.dataset.clave : '';

        if (turno === '1') {
            return 0.57;
        }

        if (turno === '2') {
            return 0.43;
        }

        return 1;
    }

    /* Líneas que cumplen con los filtros de área y línea seleccionados. */
    function lineasFiltradas() {
        const opcionArea = elementos.area.options[elementos.area.selectedIndex];
        const areaSeleccionada = elementos.area.value;
        const nombreArea = opcionArea ? opcionArea.dataset.nombre : '';
        const lineaSeleccionada = elementos.linea.value;
        const factor = factorTurno();

        return datosMock.lineas
            .filter(function (linea) {
                const coincideArea = areaSeleccionada === 'todas' || linea.area === nombreArea;
                const coincideLinea = lineaSeleccionada === 'todas' || linea.codigo === lineaSeleccionada;
                return coincideArea && coincideLinea;
            })
            .map(function (linea) {
                return {
                    codigo: linea.codigo,
                    nombre: linea.nombre,
                    area: linea.area,
                    criticas: Math.round(linea.criticas * factor),
                    noCriticas: Math.round(linea.noCriticas * factor)
                };
            });
    }

    function totalGeneral(lineas) {
        return lineas.reduce(function (suma, linea) {
            return suma + linea.criticas + linea.noCriticas;
        }, 0);
    }

    function totalCriticas(lineas) {
        return lineas.reduce(function (suma, linea) {
            return suma + linea.criticas;
        }, 0);
    }

    /*
     * Reparte un total anual entre los doce meses.
     * El ajuste final compensa el redondeo para que la suma de los meses sea
     * exactamente el total recibido; así la línea de gran total siempre
     * coincide con la suma de las barras que tiene debajo.
     */
    function repartirPorMes(total, pesos) {
        const distribucion = pesos.map(function (peso) {
            return Math.round(total * peso);
        });

        const suma = distribucion.reduce(function (acumulado, valor) {
            return acumulado + valor;
        }, 0);

        let indiceMayor = 0;
        pesos.forEach(function (peso, indice) {
            if (peso > pesos[indiceMayor]) {
                indiceMayor = indice;
            }
        });

        distribucion[indiceMayor] += total - suma;

        return distribucion;
    }

    /* Cada línea desplaza los pesos para que su curva no sea idéntica. */
    function pesosDeLinea(indice) {
        const desplazamiento = indice % PESOS_MES.length;
        return PESOS_MES.slice(desplazamiento).concat(PESOS_MES.slice(0, desplazamiento));
    }

    /*
     * Serie mensual de cada línea. Es la base común del histórico anual y de
     * la gráfica de tendencia, de modo que ambas muestren los mismos números.
     */
    function matrizMensual(lineas) {
        return lineas.map(function (linea, indice) {
            return {
                codigo: linea.codigo,
                nombre: linea.nombre,
                area: linea.area,
                color: COLORES_LINEA[indice % COLORES_LINEA.length],
                valores: repartirPorMes(linea.criticas + linea.noCriticas, pesosDeLinea(indice))
            };
        });
    }

    /* Gran total de cada mes: suma de todas las líneas visibles. */
    function totalesPorMes(series) {
        return MESES.map(function (mes, indice) {
            return series.reduce(function (suma, serie) {
                return suma + serie.valores[indice];
            }, 0);
        });
    }

    /**
     * Genera los meses visibles de acuerdo con el periodo devuelto por la API.
     * Para rangos de varios años agrega el año a cada etiqueta.
     */
    function construirPeriodosHistorico(periodo) {
        const inicio = String(periodo && periodo.fecha_inicio || '').split('-').map(Number);
        const fin = String(periodo && periodo.fecha_fin || '').split('-').map(Number);

        if (inicio.length !== 3 || fin.length !== 3 ||
            !inicio[0] || !inicio[1] || !fin[0] || !fin[1]) {
            throw new Error('El periodo del histórico no tiene un formato válido.');
        }

        const claves = [];
        const etiquetas = [];
        const incluyeVariosAnios = inicio[0] !== fin[0];
        let anioActual = inicio[0];
        let mesActual = inicio[1];

        while (anioActual < fin[0] ||
               (anioActual === fin[0] && mesActual <= fin[1])) {
            claves.push(anioActual + '-' + String(mesActual).padStart(2, '0'));
            etiquetas.push(
                MESES[mesActual - 1] +
                (incluyeVariosAnios ? ' ' + String(anioActual).slice(-2) : '')
            );

            mesActual++;
            if (mesActual > 12) {
                mesActual = 1;
                anioActual++;
            }
        }

        return { claves: claves, etiquetas: etiquetas };
    }

    /**
     * Convierte el total mensual agregado por SQL en los puntos de la gráfica.
     * Los meses del periodo sin registros se completan visualmente con cero.
     */
    function prepararHistoricoTotal(filas, periodo) {
        const periodos = construirPeriodosHistorico(periodo);
        const totalesPorPeriodo = {};

        filas.forEach(function (fila) {
            const anio = Number(fila.anio);
            const mes = Number(fila.mes);

            if (Number.isInteger(anio) && Number.isInteger(mes) && mes >= 1 && mes <= 12) {
                totalesPorPeriodo[anio + '-' + String(mes).padStart(2, '0')] =
                    Number(fila.total) || 0;
            }
        });

        return {
            etiquetas: periodos.etiquetas,
            valores: periodos.claves.map(function (clave) {
                return totalesPorPeriodo[clave] || 0;
            })
        };
    }

    /**
     * Convierte las filas agregadas por mes y línea en las series del segundo
     * subtab. JavaScript sólo acomoda resultados y completa meses con cero.
     */
    function prepararHistoricoLineas(filas, periodo) {
        const periodos = construirPeriodosHistorico(periodo);
        const indicePeriodo = {};
        const lineas = {};

        periodos.claves.forEach(function (clave, indice) {
            indicePeriodo[clave] = indice;
        });

        filas.forEach(function (fila) {
            const anio = Number(fila.anio);
            const mes = Number(fila.mes);
            const codigo = String(fila.codigo_linea || '').trim();
            const clavePeriodo = anio + '-' + String(mes).padStart(2, '0');
            const indice = indicePeriodo[clavePeriodo];

            if (!Number.isInteger(anio) || !Number.isInteger(mes) ||
                mes < 1 || mes > 12 || !codigo || indice === undefined) {
                return;
            }

            if (!lineas[codigo]) {
                lineas[codigo] = {
                    codigo: codigo,
                    nombre: String(fila.nombre_linea || codigo),
                    idArea: Number(fila.id_area) || 0,
                    area: String(fila.nombre_area || ''),
                    valores: periodos.claves.map(function () { return 0; })
                };
            }

            lineas[codigo].valores[indice] = Number(fila.total) || 0;
        });

        /* Agrupa visualmente las líneas por área y luego por su nombre. */
        const series = Object.keys(lineas).sort(function (codigoA, codigoB) {
            const lineaA = lineas[codigoA];
            const lineaB = lineas[codigoB];
            const ordenArea = lineaA.area.localeCompare(lineaB.area, 'es');

            if (ordenArea !== 0) {
                return ordenArea;
            }

            return lineaA.nombre.localeCompare(lineaB.nombre, 'es');
        }).map(function (codigo, indice) {
            return {
                codigo: codigo,
                nombre: lineas[codigo].nombre,
                valores: lineas[codigo].valores,
                color: COLORES_LINEA[indice % COLORES_LINEA.length]
            };
        });

        return {
            etiquetas: periodos.etiquetas,
            series: series
        };
    }

    /**
     * Convierte la agregación mensual de SQL Server en series para el SVG.
     * Las filas sin mes representan catálogos sin registros y se conservan
     * como una serie en cero para que no desaparezcan del comparativo.
     */
    function prepararTendenciaMensual(filas, periodo, nivel) {
        const periodos = construirPeriodosHistorico(periodo);
        const indicePeriodo = {};
        const seriesPorClave = {};
        const ordenSeries = [];
        const colores = nivel === 'area' ? COLORES_AREA : COLORES_LINEA;

        periodos.claves.forEach(function (clave, indice) {
            indicePeriodo[clave] = indice;
        });

        filas.forEach(function (fila) {
            const claveSerie = String(fila.clave_serie || '').trim();

            if (!claveSerie) {
                return;
            }

            if (!Object.prototype.hasOwnProperty.call(seriesPorClave, claveSerie)) {
                seriesPorClave[claveSerie] = {
                    nombre: String(fila.nombre_serie || claveSerie),
                    valores: periodos.claves.map(function () { return 0; })
                };
                ordenSeries.push(claveSerie);
            }

            const anio = Number(fila.anio);
            const mes = Number(fila.mes);
            const clavePeriodo = anio + '-' + String(mes).padStart(2, '0');
            const indice = indicePeriodo[clavePeriodo];

            if (Number.isInteger(anio) && Number.isInteger(mes) &&
                mes >= 1 && mes <= 12 && indice !== undefined) {
                seriesPorClave[claveSerie].valores[indice] = Number(fila.total) || 0;
            }
        });

        return {
            etiquetas: periodos.etiquetas,
            series: ordenSeries.map(function (clave, indice) {
                return {
                    nombre: seriesPorClave[clave].nombre,
                    valores: seriesPorClave[clave].valores,
                    color: colores[indice % colores.length]
                };
            })
        };
    }


    /* Redondea el tope del eje a un valor cerrado (50, 100, 250, 500...). */
    function escalaSuperior(valor) {
        if (valor <= 10) {
            return 10;
        }

        const magnitud = Math.pow(10, Math.floor(Math.log10(valor)));
        const paso = magnitud / 2;

        return Math.ceil(valor / paso) * paso;
    }

    /* =========================== Render: KPIs =========================== */

    function renderKpis(resumen, mensajeTotal) {
        const total = Number(resumen.total_anormalidades) || 0;
        const criticas = Number(resumen.anormalidades_criticas) || 0;
        const porcentajeCriticas = Number(resumen.porcentaje_criticas) || 0;
        const finalizadas = Number(resumen.anormalidades_finalizadas) || 0;
        const porcentajeFinalizadas = Number(resumen.porcentaje_finalizadas) || 0;
        const enProceso = Number(resumen.anormalidades_en_proceso) || 0;

        const tarjetas = [
            {
                clase: '', icono: 'bi-clipboard-data', etiqueta: 'Anormalidades registradas',
                valor: total, nota: mensajeTotal || 'Según los filtros aplicados'
            },
            {
                clase: 'ra-kpi-critica', icono: 'bi-exclamation-octagon', etiqueta: 'Críticas',
                valor: criticas, nota: porcentajeCriticas + '% del total'
            },
            {
                clase: 'ra-kpi-cerrada', icono: 'bi-check-circle', etiqueta: 'Finalizadas',
                valor: finalizadas, nota: porcentajeFinalizadas + '% de cierre'
            },
            {
                clase: 'ra-kpi-abierta', icono: 'bi-hourglass-split', etiqueta: 'En proceso',
                valor: enProceso, nota: 'Con estatus distinto a finalizada'
            }
        ];

        elementos.kpis.innerHTML = tarjetas.map(function (tarjeta) {
            return '<article class="ra-kpi ' + tarjeta.clase + '">' +
                '<span class="ra-kpi-icon"><i class="bi ' + tarjeta.icono + '" aria-hidden="true"></i></span>' +
                '<span class="ra-kpi-copy">' +
                    '<span>' + escaparHtml(tarjeta.etiqueta) + '</span>' +
                    '<strong>' + tarjeta.valor.toLocaleString('es-MX') + '</strong>' +
                    '<small>' + escaparHtml(tarjeta.nota) + '</small>' +
                '</span>' +
            '</article>';
        }).join('');
    }

    /* ===================== Render: gráficas base ===================== */

    /*
     * Histórico anual. Tiene dos vistas para no amontonar la información:
     *
     *  - "total":   una sola línea con el gran total de cada mes.
     *  - "detalle": barras agrupadas con la cantidad de cada línea por mes.
     *
     * En la vista de detalle la escala se calcula con el mayor valor de una
     * línea (no con el gran total), de modo que las barras aprovechen todo el
     * alto disponible y sean legibles.
     */
    function renderHistorico(series) {
        /* Se conserva la serie para poder redibujar al cambiar el ancho. */
        seriesHistorico = series;
        const detalle = modoHistorico === 'detalle';
        const estadoServidor = detalle ? historicoLineasServidor : historicoTotalServidor;

        if (estadoServidor && estadoServidor.mensaje) {
            elementos.historicoEscala.innerHTML = '';
            elementos.historicoMeses.innerHTML = '<p class="ra-hbar-note">' +
                escaparHtml(estadoServidor.mensaje) + '</p>';
            elementos.historicoLeyenda.innerHTML = '';
            return;
        }

        const etiquetasPeriodo = estadoServidor
            ? estadoServidor.etiquetas
            : MESES;
        const seriesGrafica = detalle && historicoLineasServidor
            ? historicoLineasServidor.series
            : series;

        if ((detalle && !seriesGrafica.length) || !etiquetasPeriodo.length) {
            elementos.historicoEscala.innerHTML = '';
            elementos.historicoMeses.innerHTML =
                '<p class="ra-hbar-note">Sin información para los filtros seleccionados.</p>';
            elementos.historicoLeyenda.innerHTML = '';
            return;
        }

        const totales = !detalle && historicoTotalServidor
            ? historicoTotalServidor.valores
            : totalesPorMes(seriesGrafica);

        /* Cada vista escala con su propio máximo para ganar altura útil. */
        const valorMaximo = detalle
            ? seriesGrafica.reduce(function (mayor, serie) {
                return Math.max(mayor, Math.max.apply(null, serie.valores));
            }, 1)
            : Math.max.apply(null, totales.concat([1]));

        const maximo = escalaSuperior(valorMaximo);

        /*
         * El viewBox toma el ancho real del contenedor para que la gráfica
         * ocupe todo el espacio disponible sin estirar el texto. El ancho
         * mínimo evita que las barras queden ilegibles en pantallas angostas:
         * en ese caso el contenedor muestra scroll horizontal.
         */
        const alto = 420;
        const margen = { arriba: 34, derecha: 18, abajo: 36, izquierda: 12 };

        let anchoMinimo = ANCHO_MINIMO_HISTORICO[modoHistorico];

        if (detalle) {
            /*
             * El ancho crece con la cantidad de líneas para que cada barra
             * conserve un grosor legible. Cuando el resultado supera el espacio
             * disponible, el contenedor muestra scroll horizontal en lugar de
             * comprimir las barras.
             */
            const anchoGrupoNecesario = (seriesGrafica.length * ANCHO_BARRA_DETALLE) /
                PROPORCION_GRUPO;

            anchoMinimo = Math.max(anchoMinimo, Math.round(
                (anchoGrupoNecesario * etiquetasPeriodo.length) + margen.izquierda + margen.derecha
            ));
        } else {
            /* Los rangos mayores a un año conservan una etiqueta legible por mes. */
            anchoMinimo = Math.max(
                anchoMinimo,
                (etiquetasPeriodo.length * 64) + margen.izquierda + margen.derecha
            );
        }

        const anchoDisponible = Math.round(elementos.historicoMeses.clientWidth);
        const ancho = Math.max(anchoMinimo, anchoDisponible || anchoMinimo);

        /* Con ancho fijo la gráfica se desplaza; si cabe, se mantiene fluida. */
        const estiloAncho = ancho > anchoDisponible
            ? 'width: ' + ancho + 'px;'
            : 'width: 100%;';

        const anchoUtil = ancho - margen.izquierda - margen.derecha;
        const altoUtil = alto - margen.arriba - margen.abajo;
        const anchoGrupo = anchoUtil / etiquetasPeriodo.length;
        const anchoBarra = detalle
            ? Math.min(24, (anchoGrupo * PROPORCION_GRUPO) / seriesGrafica.length)
            : 0;

        function coordenadaY(valor) {
            return margen.arriba + altoUtil - ((valor / maximo) * altoUtil);
        }

        function centroMes(indice) {
            return margen.izquierda + (anchoGrupo * (indice + 0.5));
        }

        /* Guías horizontales con su valor de referencia. */
        let guias = '';
        let etiquetasEscala = '';
        for (let i = 0; i <= 4; i++) {
            const valorGuia = Math.round((maximo / 4) * (4 - i));
            const y = coordenadaY(valorGuia);

            guias += '<line class="ra-trend-grid" x1="' + margen.izquierda + '" y1="' + y.toFixed(1) +
                '" x2="' + (ancho - margen.derecha) + '" y2="' + y.toFixed(1) + '"></line>';

            etiquetasEscala += '<span class="ra-combo-scale-label" style="top: ' + y.toFixed(1) +
                'px;">' + valorGuia + '</span>';
        }

        /* Estos valores quedan fuera del contenedor que se desplaza horizontalmente. */
        elementos.historicoEscala.innerHTML = etiquetasEscala;

        let contenido = '';

        if (detalle) {
            /* Barras agrupadas: una barra por línea dentro de cada mes. */
            etiquetasPeriodo.forEach(function (mes, indiceMes) {
                const inicioGrupo = centroMes(indiceMes) -
                    ((seriesGrafica.length * anchoBarra) / 2);

                seriesGrafica.forEach(function (serie, indiceSerie) {
                    const valor = serie.valores[indiceMes];
                    const y = coordenadaY(valor);
                    const altura = Math.max(1, (margen.arriba + altoUtil) - y);
                    const x = inicioGrupo + (indiceSerie * anchoBarra);

                    contenido += '<rect class="ra-combo-bar" x="' + x.toFixed(1) + '" y="' + y.toFixed(1) +
                        '" width="' + Math.max(1.5, anchoBarra - 2).toFixed(1) + '" height="' + altura.toFixed(1) +
                        '" rx="2" style="fill: ' + serie.color + ';">' +
                        '<title>' + escaparHtml(serie.nombre) + ' · ' + mes + ': ' + valor + '</title>' +
                    '</rect>';
                });
            });
        } else {
            /* Línea del gran total, con relleno tenue, punto y etiqueta. */
            const baseY = (margen.arriba + altoUtil).toFixed(1);
            const puntos = totales.map(function (total, indice) {
                return centroMes(indice).toFixed(1) + ',' + coordenadaY(total).toFixed(1);
            }).join(' ');

            const area = centroMes(0).toFixed(1) + ',' + baseY + ' ' + puntos + ' ' +
                centroMes(etiquetasPeriodo.length - 1).toFixed(1) + ',' + baseY;

            contenido += '<polygon class="ra-total-area" points="' + area + '"></polygon>' +
                '<polyline class="ra-total-line" points="' + puntos + '"></polyline>';

            totales.forEach(function (total, indice) {
                const x = centroMes(indice);
                const y = coordenadaY(total);
                const anchoEtiqueta = 15 + (String(total).length * 8);

                contenido += '<circle class="ra-total-dot" cx="' + x.toFixed(1) + '" cy="' + y.toFixed(1) +
                        '" r="3.5"></circle>' +
                    '<rect class="ra-total-badge" x="' + (x - (anchoEtiqueta / 2)).toFixed(1) +
                        '" y="' + (y - 27).toFixed(1) + '" width="' + anchoEtiqueta + '" height="18" rx="4"></rect>' +
                    '<text class="ra-total-badge-text" x="' + x.toFixed(1) + '" y="' + (y - 14).toFixed(1) +
                        '" text-anchor="middle">' + total + '</text>';
            });
        }

        const etiquetasMes = etiquetasPeriodo.map(function (mes, indice) {
            return '<text class="ra-trend-label" x="' + centroMes(indice).toFixed(1) +
                '" y="' + (alto - 10) + '" text-anchor="middle">' + mes + '</text>';
        }).join('');

        elementos.historicoMeses.innerHTML = '<svg class="ra-combo" style="' + estiloAncho +
            '" viewBox="0 0 ' + ancho + ' ' + alto +
            '" preserveAspectRatio="xMidYMid meet" role="presentation">' +
            guias + contenido + etiquetasMes +
        '</svg>';

        /* La leyenda cambia según la vista mostrada. */
        if (detalle) {
            elementos.historicoLeyenda.innerHTML = seriesGrafica.map(function (serie) {
                const totalSerie = serie.valores.reduce(function (suma, valor) {
                    return suma + valor;
                }, 0);

                return '<div class="ra-legend-item">' +
                    '<span class="ra-legend-dot" style="background: ' + serie.color + ';"></span>' +
                    '<span class="ra-legend-label">' + escaparHtml(serie.nombre) + '</span>' +
                    '<strong>' + totalSerie + '</strong>' +
                '</div>';
            }).join('');
            return;
        }

        const granTotal = totales.reduce(function (suma, valor) {
            return suma + valor;
        }, 0);

        const promedio = Math.round(granTotal / etiquetasPeriodo.length);
        const mesMaximo = totales.indexOf(Math.max.apply(null, totales));

        elementos.historicoLeyenda.innerHTML =
            '<div class="ra-legend-item">' +
                '<span class="ra-legend-dot is-total"></span>' +
                '<span class="ra-legend-label">Gran total del periodo</span>' +
                '<strong>' + granTotal + '</strong>' +
            '</div>' +
            '<div class="ra-legend-item">' +
                '<span class="ra-legend-label">Promedio mensual</span>' +
                '<strong>' + promedio + '</strong>' +
            '</div>' +
            '<div class="ra-legend-item">' +
                '<span class="ra-legend-label">Mes más alto</span>' +
                '<strong>' + escaparHtml(etiquetasPeriodo[mesMaximo]) + ' · ' +
                    totales[mesMaximo] + '</strong>' +
            '</div>';
    }

    /* Barras agrupadas de dos series (críticas / no críticas). */
    function renderBarrasAgrupadas(contenedor, lineas) {
        const maximo = lineas.reduce(function (mayor, linea) {
            return Math.max(mayor, linea.criticas, linea.noCriticas);
        }, 1);

        if (!lineas.length) {
            contenedor.innerHTML = '<p class="ra-hbar-note">Sin información para los filtros seleccionados.</p>';
            return;
        }

        contenedor.innerHTML = lineas.map(function (linea) {
            const altoCritica = linea.criticas > 0
                ? Math.max(4, Math.round((linea.criticas / maximo) * 100))
                : 0;
            const altoNoCritica = linea.noCriticas > 0
                ? Math.max(4, Math.round((linea.noCriticas / maximo) * 100))
                : 0;
            const nombreLinea = linea.nombre || linea.codigo || 'Sin nombre';

            return '<div class="ra-group">' +
                '<div class="ra-group-bars">' +
                    '<div class="ra-bar is-critica" style="--value: ' + altoCritica + ';" ' +
                        'title="Críticas: ' + linea.criticas + '">' +
                        '<span class="ra-bar-value">' + linea.criticas + '</span>' +
                    '</div>' +
                    '<div class="ra-bar is-nocritica" style="--value: ' + altoNoCritica + ';" ' +
                        'title="No críticas: ' + linea.noCriticas + '">' +
                        '<span class="ra-bar-value">' + linea.noCriticas + '</span>' +
                    '</div>' +
                '</div>' +
                '<span class="ra-group-label">' + escaparHtml(nombreLinea) + '</span>' +
            '</div>';
        }).join('');
    }

    /* Dibuja la criticidad por línea usando exclusivamente la respuesta agregada. */
    function renderCriticidadPorLinea(estado) {
        if (!estado || estado.mensaje) {
            elementos.criticasPorLinea.innerHTML = '<p class="ra-hbar-note">' +
                escaparHtml(estado && estado.mensaje ? estado.mensaje : 'Cargando información...') +
                '</p>';
            return;
        }

        const lineas = estado.filas.map(function (linea) {
            return {
                nombre: String(linea.nombre_linea || 'Sin nombre'),
                criticas: Number(linea.criticas) || 0,
                noCriticas: Number(linea.no_criticas) || 0
            };
        });

        renderBarrasAgrupadas(elementos.criticasPorLinea, lineas);
    }

    /* Dibuja el total por línea con el porcentaje calculado en SQL Server. */
    function renderTotalesPorLinea(estado) {
        if (!estado || estado.mensaje) {
            elementos.totalPorLinea.innerHTML = '<p class="ra-hbar-note">' +
                escaparHtml(estado && estado.mensaje ? estado.mensaje : 'Cargando información...') +
                '</p>';
            return;
        }

        const coloresPorArea = {};
        let siguienteColor = 0;
        const filas = estado.filas.map(function (linea) {
            const nombreArea = String(linea.nombre_area || 'Sin área');

            if (!Object.prototype.hasOwnProperty.call(coloresPorArea, nombreArea)) {
                coloresPorArea[nombreArea] = siguienteColor;
                siguienteColor += 1;
            }

            return {
                etiqueta: String(linea.nombre_linea || 'Sin nombre'),
                valor: Number(linea.total) || 0,
                nota: nombreArea + ' · ' + (Number(linea.porcentaje) || 0).toFixed(2) + '% del total',
                clase: coloresPorArea[nombreArea] % 2 === 1 ? 'is-cyan' : ''
            };
        });

        renderBarrasHorizontales(elementos.totalPorLinea, filas);
    }

    /* Presenta la tendencia agregada sin recalcular totales en el cliente. */
    function renderTendenciaDesdeServidor(estado) {
        if (!estado || estado.mensaje) {
            elementos.tendencia.innerHTML = '<p class="ra-hbar-note">' +
                escaparHtml(estado && estado.mensaje ? estado.mensaje : 'Cargando información...') +
                '</p>';
            elementos.tendenciaLeyenda.innerHTML = '';
            return;
        }

        if (!estado.series.length) {
            elementos.tendencia.innerHTML = '<p class="ra-hbar-note">' +
                'Sin información para los filtros seleccionados.</p>';
            elementos.tendenciaLeyenda.innerHTML = '';
            return;
        }

        renderTendencia(estado.series, estado.etiquetas);
    }

    /* Muestra como pastel el total y la participación de cada clasificación 5M. */
    function renderClasificacion5m(estado) {
        if (!estado || estado.mensaje) {
            elementos.cinco5mPie.style.background =
                'conic-gradient(var(--ra-neutro) 0 100%)';
            elementos.cinco5mLeyenda.innerHTML = '<p class="ra-hbar-note">' +
                escaparHtml(estado && estado.mensaje ? estado.mensaje : 'Cargando información...') +
                '</p>';
            return;
        }

        const segmentos = estado.filas.map(function (categoria, indice) {
            return {
                etiqueta: String(categoria.nombre_categoria_5m || 'Sin nombre'),
                valor: Number(categoria.total) || 0,
                color: COLORES_LINEA[indice % COLORES_LINEA.length]
            };
        });
        const total = renderDonut(
            elementos.cinco5mPie,
            elementos.cinco5mLeyenda,
            segmentos,
            2
        );

        elementos.cinco5mPie.setAttribute(
            'aria-label',
            'Distribución por clasificación 5M. Total de selecciones: ' + total
        );
    }

    /* Dibuja el TOP crítico usando únicamente la frecuencia recibida. */
    function renderTopCriticas(estado) {
        if (!estado || estado.mensaje) {
            elementos.topCriticas.innerHTML = '<p class="ra-hbar-note">' +
                escaparHtml(estado && estado.mensaje ? estado.mensaje : 'Cargando información...') +
                '</p>';
            return;
        }

        const filas = estado.filas.map(function (tipo) {
            return {
                etiqueta: String(tipo.nombre_tipo_anormalidad || 'Sin nombre'),
                valor: Number(tipo.total) || 0,
                clase: 'is-critica',
                rango: true
            };
        });

        renderBarrasHorizontales(elementos.topCriticas, filas);
    }

    /* Dibuja el TOP no crítico usando la frecuencia calculada en SQL Server. */
    function renderTopNoCriticas(estado) {
        if (!estado || estado.mensaje) {
            elementos.topNoCriticas.innerHTML = '<p class="ra-hbar-note">' +
                escaparHtml(estado && estado.mensaje ? estado.mensaje : 'Cargando información...') +
                '</p>';
            return;
        }

        const filas = estado.filas.map(function (tipo) {
            return {
                etiqueta: String(tipo.nombre_tipo_anormalidad || 'Sin nombre'),
                valor: Number(tipo.total) || 0,
                clase: 'is-nocritica',
                rango: true
            };
        });

        renderBarrasHorizontales(elementos.topNoCriticas, filas);
    }

    /*
     * Presenta los pocos niveles del catálogo como columnas verticales.
     * SQL Server continúa entregando cantidad y porcentaje; aquí sólo cambia
     * su representación para diferenciarla de los dos TOP anteriores.
     */
    function renderNivelRespuesta(estado) {
        if (!estado || estado.mensaje) {
            elementos.nivelRespuesta.innerHTML = '<p class="ra-hbar-note">' +
                escaparHtml(estado && estado.mensaje ? estado.mensaje : 'Cargando información...') +
                '</p>';
            return;
        }

        const niveles = estado.filas.map(function (nivel, indice) {
            return {
                nombre: String(nivel.nombre_nivel_respuesta || 'Sin nombre'),
                total: Number(nivel.total) || 0,
                porcentaje: Number(nivel.porcentaje) || 0,
                color: COLORES_LINEA[indice % COLORES_LINEA.length]
            };
        });
        const totalClasificado = niveles.reduce(function (suma, nivel) {
            return suma + nivel.total;
        }, 0);
        const maximo = niveles.reduce(function (mayor, nivel) {
            return Math.max(mayor, nivel.total);
        }, 0);

        if (!niveles.length || totalClasificado <= 0) {
            elementos.nivelRespuesta.innerHTML =
                '<p class="ra-hbar-note">Sin información para los filtros seleccionados.</p>';
            return;
        }

        elementos.nivelRespuesta.innerHTML = niveles.map(function (nivel) {
            const altura = nivel.total > 0
                ? Math.max(7, Math.round((nivel.total / maximo) * 100))
                : 0;
            const porcentajeNivel = nivel.porcentaje.toFixed(2);

            return '<div class="ra-level-item" style="--ra-level-color: ' + nivel.color +
                '; --ra-level-height: ' + altura + '%;">' +
                '<div class="ra-level-metric">' +
                    '<strong>' + nivel.total.toLocaleString('es-MX') + '</strong>' +
                    '<span>' + porcentajeNivel + '%</span>' +
                '</div>' +
                '<div class="ra-level-well">' +
                    '<div class="ra-level-column" title="' + escaparHtml(nivel.nombre) +
                        ': ' + nivel.total + ' (' + porcentajeNivel + '%)"></div>' +
                '</div>' +
                '<strong class="ra-level-name">' + escaparHtml(nivel.nombre) + '</strong>' +
                '<small>anormalidades clasificadas</small>' +
            '</div>';
        }).join('');

        elementos.nivelRespuesta.setAttribute(
            'aria-label',
            'Distribución por nivel de respuesta. Total clasificado: ' + totalClasificado
        );
    }

    /*
     * Barras horizontales reutilizables.
     * items: [{ etiqueta, valor, sufijo, nota, clase, rango }]
     */
    function renderBarrasHorizontales(contenedor, items) {
        if (!items.length) {
            contenedor.innerHTML = '<p class="ra-hbar-note">Sin información para los filtros seleccionados.</p>';
            return;
        }

        const maximo = items.reduce(function (mayor, item) {
            return Math.max(mayor, item.valor);
        }, 1);

        contenedor.innerHTML = items.map(function (item, indice) {
            const ancho = Math.max(3, Math.round((item.valor / maximo) * 100));
            const rango = item.rango ? '<span class="ra-hbar-rank">' + (indice + 1) + '</span>' : '';
            const nota = item.nota
                ? '<span class="ra-hbar-note">' + escaparHtml(item.nota) + '</span>'
                : '';

            return '<div>' +
                '<div class="ra-hbar-head">' +
                    '<strong>' + rango + escaparHtml(item.etiqueta) + '</strong>' +
                    '<span>' + item.valor + (item.sufijo || '') + '</span>' +
                '</div>' +
                '<div class="ra-hbar-track">' +
                    '<div class="ra-hbar-fill ' + (item.clase || '') + '" style="--value: ' + ancho + ';"></div>' +
                '</div>' +
                nota +
            '</div>';
        }).join('');
    }

    /*
     * Dona con conic-gradient.
     * segmentos: [{ etiqueta, valor, color }]
     * decimalesPorcentaje es opcional; los demás usos conservan enteros.
     */
    function renderDonut(donut, leyenda, segmentos, decimalesPorcentaje) {
        const total = segmentos.reduce(function (suma, segmento) {
            return suma + segmento.valor;
        }, 0);

        /* Sin datos se pinta un anillo neutro para no romper el gradiente. */
        if (total <= 0) {
            donut.style.background = 'conic-gradient(var(--ra-neutro) 0 100%)';
            leyenda.innerHTML = '<p class="ra-hbar-note">Sin información para los filtros seleccionados.</p>';
            return 0;
        }

        let acumulado = 0;
        const tramos = segmentos.map(function (segmento) {
            const inicio = total > 0 ? (acumulado / total) * 100 : 0;
            acumulado += segmento.valor;
            const fin = total > 0 ? (acumulado / total) * 100 : 0;

            return segmento.color + ' ' + inicio.toFixed(2) + '% ' + fin.toFixed(2) + '%';
        });

        donut.style.background = 'conic-gradient(' + tramos.join(', ') + ')';

        leyenda.innerHTML = segmentos.map(function (segmento) {
            return '<div class="ra-legend-item">' +
                '<span class="ra-legend-dot" style="background: ' + segmento.color + ';"></span>' +
                '<span class="ra-legend-label">' + escaparHtml(segmento.etiqueta) + '</span>' +
                '<strong>' + segmento.valor + '</strong>' +
                '<small>' + porcentaje(segmento.valor, total, decimalesPorcentaje) + '%</small>' +
            '</div>';
        }).join('');

        return total;
    }

    /* Muestra exclusivamente los totales por área calculados por SQL Server. */
    function renderAreasResumen(estado) {
        if (!estado || estado.mensaje) {
            elementos.areaDonut.style.background =
                'conic-gradient(var(--ra-neutro) 0 100%)';
            elementos.areaLeyenda.innerHTML = '<p class="ra-hbar-note">' +
                escaparHtml(estado && estado.mensaje ? estado.mensaje : 'Cargando información...') +
                '</p>';
            elementos.areaTotal.textContent = estado && estado.error ? '0' : '...';
            return;
        }

        const segmentos = estado.filas.map(function (area, indice) {
            return {
                etiqueta: String(area.nombre_area || 'Sin nombre'),
                valor: Number(area.total) || 0,
                color: COLORES_AREA[indice % COLORES_AREA.length]
            };
        });

        elementos.areaTotal.textContent = renderDonut(
            elementos.areaDonut,
            elementos.areaLeyenda,
            segmentos,
            2
        );
    }

    /* Renderiza cantidad y porcentaje de cada etapa usando las claves 1 a 6. */
    function renderEstatusResumen(estado) {
        if (!estado || estado.mensaje) {
            elementos.estatusDonut.style.background =
                'conic-gradient(var(--ra-neutro) 0 100%)';
            elementos.estatusLeyenda.innerHTML = '<p class="ra-hbar-note">' +
                escaparHtml(estado && estado.mensaje ? estado.mensaje : 'Cargando información...') +
                '</p>';
            elementos.estatusPorcentaje.textContent = estado && estado.error ? '0%' : '...';
            return;
        }

        const segmentos = estado.filas.map(function (estatus) {
            const clave = String(estatus.clave || '').trim();

            return {
                clave: clave,
                etiqueta: String(estatus.nombre_estatus || 'Sin nombre'),
                valor: Number(estatus.total) || 0,
                color: COLORES_ESTATUS[clave] || 'var(--ra-neutro)'
            };
        });
        const total = renderDonut(
            elementos.estatusDonut,
            elementos.estatusLeyenda,
            segmentos,
            2
        );
        const finalizada = segmentos.find(function (estatus) {
            return estatus.clave === '6';
        });

        elementos.estatusPorcentaje.textContent =
            porcentaje(finalizada ? finalizada.valor : 0, total, 2) + '%';
    }

    /* Gráfica de tendencia dibujada con SVG. */
    function renderTendencia(series, etiquetasPeriodo) {
        const anchoContenedor = Math.round(elementos.tendencia.getBoundingClientRect().width);
        const ancho = Math.max(280, anchoContenedor || 520);
        const alto = 230;
        const margenIzq = 38;
        const margenDer = 28;
        const margenSup = 12;
        const margenInf = 30;
        const etiquetas = Array.isArray(etiquetasPeriodo) && etiquetasPeriodo.length
            ? etiquetasPeriodo
            : MESES;
        const maximo = series.reduce(function (mayor, serie) {
            return Math.max(mayor, Math.max.apply(null, serie.valores));
        }, 1);

        const anchoUtil = ancho - margenIzq - margenDer;
        const altoUtil = alto - margenInf - margenSup;

        function coordenadaX(indice, total) {
            return margenIzq + (indice * (anchoUtil / Math.max(1, total - 1)));
        }

        function coordenadaY(valor) {
            return margenSup + altoUtil - ((valor / maximo) * altoUtil);
        }

        /* Líneas guía horizontales con su etiqueta de valor. */
        let guias = '';
        for (let i = 0; i <= 3; i++) {
            const valorGuia = Math.round((maximo / 3) * (3 - i));
            const y = coordenadaY(valorGuia);

            guias += '<line class="ra-trend-grid" x1="' + margenIzq + '" y1="' + y.toFixed(1) +
                '" x2="' + (ancho - margenDer) + '" y2="' + y.toFixed(1) + '"></line>' +
                '<text class="ra-trend-label" x="4" y="' + (y + 4).toFixed(1) + '">' + valorGuia + '</text>';
        }

        const lineas = series.map(function (serie) {
            const puntos = serie.valores.map(function (valor, indice) {
                return coordenadaX(indice, serie.valores.length).toFixed(1) + ',' + coordenadaY(valor).toFixed(1);
            }).join(' ');

            return '<polyline class="ra-trend-line" style="stroke: ' + serie.color + ';" points="' + puntos + '"></polyline>';
        }).join('');

        /* Mantiene una separación mínima para que los meses no se empalmen. */
        const maximoEtiquetasVisibles = Math.max(2, Math.floor(anchoUtil / 70));
        const saltoEtiquetas = Math.max(1, Math.ceil(etiquetas.length / maximoEtiquetasVisibles));
        const etiquetasMes = etiquetas.map(function (mes, indice) {
            if (indice % saltoEtiquetas !== 0 && indice !== etiquetas.length - 1) {
                return '';
            }

            const x = coordenadaX(indice, etiquetas.length);
            return '<text class="ra-trend-label" x="' + x.toFixed(1) + '" y="' + (alto - 4) + '" text-anchor="middle">' +
                mes + '</text>';
        }).join('');

        elementos.tendencia.innerHTML = '<svg class="ra-trend" viewBox="0 0 ' + ancho + ' ' + alto +
            '" preserveAspectRatio="xMidYMid meet" aria-hidden="true">' +
            guias + lineas + etiquetasMes +
        '</svg>';

        elementos.tendenciaLeyenda.innerHTML = series.map(function (serie) {
            const totalSerie = serie.valores.reduce(function (suma, valor) {
                return suma + valor;
            }, 0);

            return '<div class="ra-legend-item">' +
                '<span class="ra-legend-dot" style="background: ' + serie.color + ';"></span>' +
                '<span class="ra-legend-label">' + escaparHtml(serie.nombre) + '</span>' +
                '<strong>' + totalSerie + '</strong>' +
            '</div>';
        }).join('');
    }

    /* ================= Render: pestaña "Resumen general" ================= */

    function renderHallazgos(estado) {
        if (!estado || estado.mensaje) {
            elementos.hallazgos.innerHTML = '<p class="ra-hbar-note">' +
                escaparHtml(estado && estado.mensaje ? estado.mensaje : 'Cargando información...') +
                '</p>';
            return;
        }

        const datos = estado.datos || {};
        const linea = datos.linea_critica;
        const categoria = datos.categoria_5m;
        const tipoAnormalidad = datos.tipo_anormalidad;
        const nombreLinea = linea
            ? String(linea.nombre_linea || 'Sin nombre')
            : '';
        const tarjetas = [
            {
                clase: 'ra-kpi-critica',
                icono: 'bi-exclamation-octagon',
                etiqueta: 'Mayor concentración de críticas',
                valor: linea ? Number(linea.total_criticas) || 0 : 0,
                detalle: linea
                    ? nombreLinea + ' · ' + (Number(linea.porcentaje) || 0).toFixed(2) +
                        '% del total de críticas'
                    : 'Sin registros críticos para los filtros seleccionados'
            },
            {
                clase: '',
                icono: 'bi-diagram-3',
                etiqueta: 'Factor 5M más frecuente',
                valor: categoria ? Number(categoria.total_apariciones) || 0 : 0,
                detalle: categoria
                    ? String(categoria.nombre_categoria_5m || 'Sin nombre') + ' · ' +
                        (Number(categoria.porcentaje) || 0).toFixed(2) +
                        '% de las selecciones 5M'
                    : 'Sin clasificaciones 5M para los filtros seleccionados'
            },
            {
                clase: 'ra-kpi-abierta',
                icono: 'bi-tags',
                etiqueta: 'Tipo de anormalidad más frecuente',
                valor: tipoAnormalidad ? Number(tipoAnormalidad.total_registros) || 0 : 0,
                detalle: tipoAnormalidad
                    ? String(tipoAnormalidad.nombre_tipo_anormalidad || 'Sin nombre') + ' · ' +
                        (Number(tipoAnormalidad.porcentaje) || 0).toFixed(2) +
                        '% de las anormalidades clasificadas'
                    : 'Sin tipos de anormalidad para los filtros seleccionados'
            }
        ];

        elementos.hallazgos.innerHTML = tarjetas.map(function (tarjeta) {
            return '<article class="ra-kpi ' + tarjeta.clase + '">' +
                '<span class="ra-kpi-icon"><i class="bi ' + tarjeta.icono + '" aria-hidden="true"></i></span>' +
                '<span class="ra-kpi-copy">' +
                    '<span>' + escaparHtml(tarjeta.etiqueta) + '</span>' +
                    '<strong>' + tarjeta.valor.toLocaleString('es-MX') + '</strong>' +
                    '<small>' + escaparHtml(tarjeta.detalle) + '</small>' +
                '</span>' +
            '</article>';
        }).join('');
    }

    /**
     * Inicializa el listado con procesamiento del lado del servidor. SQL Server
     * aplica los filtros, la búsqueda y la paginación antes de devolver datos.
     */
    function inicializarTablaEstatusCriticas() {
        const usarDataTable = window.jQuery && window.jQuery.fn &&
            window.jQuery.fn.DataTable;

        if (!usarDataTable) {
            elementos.tablaCriticas.querySelector('tbody').innerHTML =
                '<tr><td colspan="7" class="ra-cell-note">' +
                'No fue posible iniciar la tabla de seguimiento.</td></tr>';
            return;
        }

        if (tablaEstatusCriticasDT) {
            tablaEstatusCriticasDT.ajax.reload(null, true);
            return;
        }

        tablaEstatusCriticasDT = window.jQuery(elementos.tablaCriticas).DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            pageLength: 10,
            lengthChange: false,
            searching: true,
            ordering: false,
            searchDelay: 350,
            autoWidth: false,
            ajax: function (solicitud, completar) {
                const parametros = obtenerParametrosReporte(
                    OPERACIONES_REPORTES.criticasTipos,
                    'estatus_criticas'
                );

                parametros.inicio = solicitud.start;
                parametros.longitud = solicitud.length;

                if (solicitud.search && solicitud.search.value.trim()) {
                    parametros.busqueda = solicitud.search.value.trim();
                }

                consultarApiReportes(parametros).then(function (respuesta) {
                    const listado = respuesta.estatus_criticas || {};

                    completar({
                        draw: solicitud.draw,
                        recordsTotal: Number(listado.total_registros) || 0,
                        recordsFiltered: Number(listado.total_filtrados) || 0,
                        data: Array.isArray(listado.registros) ? listado.registros : []
                    });
                }).catch(function (error) {
                    console.error(
                        '[Reportes de anormalidades] No se cargó el seguimiento de críticas.',
                        { filtros: parametros, error: error }
                    );

                    completar({
                        draw: solicitud.draw,
                        recordsTotal: 0,
                        recordsFiltered: 0,
                        data: []
                    });
                });
            },
            columns: [
                {
                    data: 'no_seguimiento',
                    render: function (valor, tipo) {
                        return tipo === 'display'
                            ? '<strong>' + escaparHtml(valor || 'Sin número') + '</strong>'
                            : (valor || '');
                    }
                },
                {
                    data: 'fecha_hora_deteccion',
                    render: function (valor, tipo) {
                        const fechaHora = String(valor || '').replace('T', ' ');
                        return tipo === 'display' ? escaparHtml(fechaHora || '—') : fechaHora;
                    }
                },
                {
                    data: 'turno',
                    render: function (valor, tipo) {
                        return tipo === 'display'
                            ? '<span class="ra-badge">' + escaparHtml(valor || 'Sin turno') + '</span>'
                            : (valor || '');
                    }
                },
                {
                    data: 'nombre_area',
                    render: function (valor, tipo) {
                        return tipo === 'display'
                            ? '<span class="ra-badge">' + escaparHtml(valor || 'Sin área') + '</span>'
                            : (valor || '');
                    }
                },
                {
                    data: 'nombre_linea',
                    render: function (valor, tipo) {
                        return tipo === 'display' ? escaparHtml(valor || '—') : (valor || '');
                    }
                },
                {
                    data: 'tipo_anormalidad',
                    render: function (valor, tipo) {
                        return tipo === 'display' ? escaparHtml(valor || '—') : (valor || '');
                    }
                },
                {
                    data: 'estatus',
                    render: function (valor, tipo) {
                        return tipo === 'display'
                            ? '<span class="ra-badge is-abierta">' +
                                '<i class="bi bi-clock-history" aria-hidden="true"></i>' +
                                escaparHtml(valor || 'En proceso') + '</span>'
                            : (valor || '');
                    }
                }
            ],
            language: {
                processing: 'Cargando anormalidades...',
                search: 'Buscar:',
                searchPlaceholder: 'Seguimiento, línea, tipo...',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'No hay registros disponibles',
                infoFiltered: '(filtrados de _MAX_ registros)',
                zeroRecords: 'No se encontraron anormalidades',
                emptyTable: 'No hay anormalidades críticas pendientes para los filtros seleccionados',
                paginate: {
                    first: 'Primero',
                    previous: 'Anterior',
                    next: 'Siguiente',
                    last: 'Último'
                }
            }
        });
    }

    /* ================ Render: pestaña "Reincidencias" ================ */

    /* Muestra los tipos con más anormalidades que cuentan con reincidencia. */
    function renderTopReincidentes(estado) {
        if (!estado || estado.mensaje) {
            elementos.topReincidentes.innerHTML = '<p class="ra-hbar-note">' +
                escaparHtml(estado && estado.mensaje
                    ? estado.mensaje
                    : 'Cargando reincidencias...') +
                '</p>';
            return;
        }

        const filas = estado.filas.map(function (tipo) {
            return {
                etiqueta: String(tipo.nombre_tipo_anormalidad || 'Sin nombre'),
                valor: Number(tipo.total) || 0,
                rango: true
            };
        });

        renderBarrasHorizontales(elementos.topReincidentes, filas);
    }

    /* Presenta las estaciones más frecuentes y su línea para diferenciarlas. */
    function renderTopEstacionesReincidencia(estado) {
        if (!estado || estado.mensaje) {
            elementos.topEstacionesReincidencia.innerHTML = '<p class="ra-hbar-note">' +
                escaparHtml(estado && estado.mensaje
                    ? estado.mensaje
                    : 'Cargando estaciones...') +
                '</p>';
            return;
        }

        const filas = estado.filas.map(function (estacion) {
            return {
                etiqueta: String(estacion.nombre_estacion || 'Sin nombre'),
                valor: Number(estacion.total) || 0,
                nota: 'Línea ' + String(estacion.codigo_linea || 'sin identificar'),
                rango: true
            };
        });

        renderBarrasHorizontales(elementos.topEstacionesReincidencia, filas);
    }

    function renderTablaFrecuentes(filas) {
        const cuerpo = elementos.tablaFrecuentes.querySelector('tbody');
        const usarDataTable = window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable;

        /* DataTables debe destruirse antes de reemplazar las filas. */
        if (usarDataTable && tablaFrecuentesDT) {
            tablaFrecuentesDT.destroy();
            tablaFrecuentesDT = null;
        }

        cuerpo.innerHTML = filas.map(function (registro) {
            return '<tr>' +
                '<td class="ra-cell-strong">' +
                    escaparHtml(registro.nombre_tipo_anormalidad) + '</td>' +
                '<td><span class="ra-badge">' + escaparHtml(registro.nombre_area) +
                    '</span></td>' +
                '<td>' + escaparHtml(registro.nombre_linea) +
                    '<span class="ra-cell-note">' + escaparHtml(registro.codigo_linea) +
                    '</span></td>' +
                '<td>' + registro.frecuencia + '</td>' +
                '<td class="text-center">' +
                    '<button type="button" class="ra-action ra-action-frecuente-info" ' +
                        'data-id-tipo="' + escaparHtml(registro.id_tipo_anormalidad) + '" ' +
                        'data-codigo-linea="' + escaparHtml(registro.codigo_linea) + '" ' +
                        'data-tipo="' + escaparHtml(registro.nombre_tipo_anormalidad) + '" ' +
                        'data-area="' + escaparHtml(registro.nombre_area) + '" ' +
                        'data-linea="' + escaparHtml(registro.nombre_linea) + '" ' +
                        'title="Ver causas y contramedidas" ' +
                        'aria-label="Ver causas y contramedidas de ' +
                            escaparHtml(registro.nombre_tipo_anormalidad) + '">' +
                        '<i class="bi bi-info-lg" aria-hidden="true"></i>' +
                    '</button>' +
                '</td>' +
            '</tr>';
        }).join('');

        if (!usarDataTable) {
            return;
        }

        tablaFrecuentesDT = window.jQuery(elementos.tablaFrecuentes).DataTable({
            pageLength: 10,
            lengthChange: false,
            order: [[3, 'desc']],
            columnDefs: [
                { targets: 4, orderable: false, searchable: false }
            ],
            language: {
                search: 'Buscar:',
                searchPlaceholder: 'Tipo, área o línea...',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'No hay registros disponibles',
                infoFiltered: '(filtrados de _MAX_ registros)',
                zeroRecords: 'No se encontraron anormalidades',
                emptyTable: 'No hay anormalidades para los filtros seleccionados',
                paginate: {
                    first: 'Primero',
                    previous: 'Anterior',
                    next: 'Siguiente',
                    last: 'Último'
                }
            }
        });
    }

    /**
     * El detalle usa paginación del servidor porque las causas y contramedidas
     * pueden ser extensas y sólo deben consultarse cuando el usuario las abre.
     */
    function inicializarTablaDetalleContramedidas() {
        const usarDataTable = window.jQuery && window.jQuery.fn &&
            window.jQuery.fn.DataTable;

        if (!usarDataTable || !contextoDetalleContramedidas) {
            return;
        }

        if (tablaDetalleContramedidasDT) {
            tablaDetalleContramedidasDT.search('');
            tablaDetalleContramedidasDT.ajax.reload(null, true);
            return;
        }

        tablaDetalleContramedidasDT =
            window.jQuery(elementos.tablaDetalleContramedidas).DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 10,
                lengthChange: false,
                searching: true,
                ordering: false,
                searchDelay: 350,
                autoWidth: false,
                ajax: function (solicitud, completar) {
                    const parametros = obtenerParametrosReporte(
                        OPERACIONES_REPORTES.reincidencias,
                        'detalle_contramedidas'
                    );

                    parametros.idTipoAnormalidad =
                        contextoDetalleContramedidas.idTipoAnormalidad;
                    parametros.codigoLineaDetalle =
                        contextoDetalleContramedidas.codigoLinea;
                    parametros.inicio = solicitud.start;
                    parametros.longitud = solicitud.length;

                    if (solicitud.search && solicitud.search.value.trim()) {
                        parametros.busqueda = solicitud.search.value.trim();
                    }

                    consultarApiReportes(parametros).then(function (respuesta) {
                        const detalle = respuesta.detalle_contramedidas || {};

                        completar({
                            draw: solicitud.draw,
                            recordsTotal: Number(detalle.total_registros) || 0,
                            recordsFiltered: Number(detalle.total_filtrados) || 0,
                            data: Array.isArray(detalle.registros) ? detalle.registros : []
                        });
                    }).catch(function (error) {
                        console.error(
                            '[Reportes de anormalidades] No se cargaron las contramedidas.',
                            { filtros: parametros, error: error }
                        );

                        completar({
                            draw: solicitud.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: []
                        });
                    });
                },
                columns: [
                    {
                        data: 'no_seguimiento',
                        render: function (valor, tipo) {
                            return tipo === 'display'
                                ? '<strong>' + escaparHtml(valor || 'Sin número') + '</strong>'
                                : (valor || '');
                        }
                    },
                    {
                        data: 'fecha_registro',
                        render: function (valor, tipo) {
                            const fechaHora = String(valor || '').replace('T', ' ');
                            return tipo === 'display' ? escaparHtml(fechaHora || '—') : fechaHora;
                        }
                    },
                    {
                        data: 'detalle_anormalidad',
                        render: function (valor, tipo) {
                            return tipo === 'display' ? escaparHtml(valor || '—') : (valor || '');
                        }
                    },
                    {
                        data: 'causa',
                        render: function (valor, tipo) {
                            return tipo === 'display' ? escaparHtml(valor || '—') : (valor || '');
                        }
                    },
                    {
                        data: 'contramedida',
                        render: function (valor, tipo) {
                            return tipo === 'display' ? escaparHtml(valor || '—') : (valor || '');
                        }
                    }
                ],
                language: {
                    processing: 'Cargando contramedidas...',
                    search: 'Buscar:',
                    searchPlaceholder: 'Seguimiento, causa o contramedida...',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    infoEmpty: 'No hay registros disponibles',
                    infoFiltered: '(filtrados de _MAX_ registros)',
                    zeroRecords: 'No se encontraron contramedidas',
                    emptyTable: 'No hay causas y contramedidas registradas para esta selección',
                    paginate: {
                        first: 'Primero',
                        previous: 'Anterior',
                        next: 'Siguiente',
                        last: 'Último'
                    }
                }
            });
    }

    function abrirDetalleContramedidas(boton) {
        contextoDetalleContramedidas = {
            idTipoAnormalidad: boton.dataset.idTipo,
            codigoLinea: boton.dataset.codigoLinea
        };

        elementos.contextoContramedidasFrecuentes.textContent =
            boton.dataset.tipo + ' · ' + boton.dataset.area + ' · ' + boton.dataset.linea;

        inicializarTablaDetalleContramedidas();

        if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal
                .getOrCreateInstance(elementos.modalContramedidasFrecuentes)
                .show();
        }
    }

    /* Render compartido para los TOP de estaciones de generación y detección. */
    function renderTopEstacionesReporte(contenedor, estado, claseBarra) {
        if (!estado || estado.mensaje) {
            contenedor.innerHTML = '<p class="ra-hbar-note">' +
                escaparHtml(estado && estado.mensaje
                    ? estado.mensaje
                    : 'Cargando estaciones...') +
                '</p>';
            return;
        }

        const filas = estado.filas.map(function (estacion) {
            return {
                etiqueta: String(estacion.nombre_estacion || 'Sin nombre'),
                valor: Number(estacion.total) || 0,
                nota: String(estacion.nombre_linea || 'Línea sin identificar') + ' · ' +
                    (Number(estacion.porcentaje) || 0).toFixed(2) + '% del total',
                clase: claseBarra,
                rango: true
            };
        });

        renderBarrasHorizontales(contenedor, filas);
    }

    /**
     * Solicita de forma independiente uno de los dos TOP. La API entrega la
     * agrupación terminada y solamente las cinco estaciones que se muestran.
     */
    async function actualizarTopEstacionesReporte(seccion, contenedor, claseBarra) {
        const esGeneracion = seccion === 'top_estaciones_generacion';
        const numeroSolicitud = esGeneracion
            ? ++solicitudEstacionesGeneracionActual
            : ++solicitudEstacionesDeteccionActual;
        const parametros = obtenerParametrosReporte(
            OPERACIONES_REPORTES.reporteDetallado,
            seccion
        );

        renderTopEstacionesReporte(
            contenedor,
            { mensaje: 'Cargando estaciones...' },
            claseBarra
        );

        try {
            const respuesta = await consultarApiReportes(parametros);
            const solicitudVigente = esGeneracion
                ? solicitudEstacionesGeneracionActual
                : solicitudEstacionesDeteccionActual;

            if (numeroSolicitud !== solicitudVigente) {
                return;
            }

            renderTopEstacionesReporte(
                contenedor,
                { filas: Array.isArray(respuesta[seccion]) ? respuesta[seccion] : [] },
                claseBarra
            );
        } catch (error) {
            const solicitudVigente = esGeneracion
                ? solicitudEstacionesGeneracionActual
                : solicitudEstacionesDeteccionActual;

            if (numeroSolicitud !== solicitudVigente) {
                return;
            }

            console.error('[Reportes de anormalidades] No se cargó el TOP de estaciones.', {
                seccion: seccion,
                filtros: parametros,
                error: error
            });
            renderTopEstacionesReporte(
                contenedor,
                { mensaje: 'No fue posible cargar las estaciones.', error: true },
                claseBarra
            );
        }
    }

    /* ==================================================================
       SECCIÓN DE REPORTES
       DataTables solicita exclusivamente la página visible. SQL Server aplica
       filtros, búsqueda y paginación; JavaScript sólo presenta la respuesta.
       ================================================================== */

    function capturarFiltrosReporteFormulario() {
        const filtros = {};
        const estatusSeleccionados = Array.prototype.slice.call(
            elementos.reporteEstatusOpciones.querySelectorAll(
                'input[data-ra-estatus]:checked'
            )
        ).map(function (check) {
            return check.value;
        });

        if (!elementos.reporteEstatusTodos.checked) {
            if (estatusSeleccionados.length) {
                /* La API valida y convierte esta lista en un IN parametrizado. */
                filtros.claveEstatus = estatusSeleccionados.join(',');
            } else {
                /* Evita interpretar una selección vacía como "Todos". */
                filtros.sinEstatus = '1';
            }
        }

        if (elementos.reporteCriticidad.value !== 'todas') {
            filtros.idCriticidad = elementos.reporteCriticidad.value;
        }

        return filtros;
    }

    function obtenerParametrosListadoReporte() {
        /*
         * El listado hereda fecha, área, línea y turno del filtro principal;
         * criticidad y estatus se agregan como condiciones adicionales.
         */
        const parametros = obtenerParametrosReporte(
            OPERACIONES_REPORTES.reporteDetallado,
            'listado'
        );

        Object.assign(parametros, capturarFiltrosReporteFormulario());

        return parametros;
    }

    /* Los catálogos propios del reporte se consultan una sola vez. */
    async function cargarCatalogosReporteDetallado() {
        if (catalogosReporteCargados) {
            return;
        }

        elementos.reporteEstatusTodos.disabled = true;
        elementos.reporteCriticidad.disabled = true;

        try {
            const respuesta = await consultarApiReportes({
                opcion: OPERACIONES_REPORTES.reporteDetallado,
                seccion: 'catalogos'
            });
            const estatus = Array.isArray(respuesta.estatus) ? respuesta.estatus : [];
            const criticidades = Array.isArray(respuesta.criticidades)
                ? respuesta.criticidades
                : [];

            elementos.reporteEstatusOpciones.innerHTML = estatus.map(function (item) {
                    return '<label class="ra-report-status-option">' +
                        '<input type="checkbox" data-ra-estatus checked value="' +
                        escaparHtml(item.clave_estatus) + '">' +
                        '<span>' + escaparHtml(item.nombre_estatus) + '</span>' +
                        '</label>';
                }).join('');
            elementos.reporteCriticidad.innerHTML = '<option value="todas">Todas</option>' +
                criticidades.map(function (criticidad) {
                    return '<option value="' + escaparHtml(criticidad.id_criticidad) + '">' +
                        escaparHtml(criticidad.nombre_criticidad) + '</option>';
                }).join('');
            catalogosReporteCargados = true;
        } catch (error) {
            console.error('[Reportes de anormalidades] No se cargaron los catálogos del listado.',
                error);
        } finally {
            elementos.reporteEstatusTodos.disabled = false;
            elementos.reporteCriticidad.disabled = false;
        }
    }

    function actualizarConteoReporte(total) {
        elementos.reporteConteo.textContent = total === 1
            ? '1 registro'
            : total + ' registros';
    }

    function inicializarTablaReporte() {
        const usarDataTable = window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable;

        if (tablaReporteDT || !usarDataTable) {
            return;
        }

        tablaReporteDT = window.jQuery(elementos.tablaReporte).DataTable({
            processing: true,
            serverSide: true,
            searching: true,
            ordering: false,
            searchDelay: 350,
            pageLength: 10,
            lengthChange: false,
            autoWidth: false,
            ajax: function (datosDataTable, callback) {
                const parametros = obtenerParametrosListadoReporte();
                parametros.inicio = datosDataTable.start || 0;
                parametros.longitud = datosDataTable.length || 10;
                parametros.busqueda = datosDataTable.search
                    ? datosDataTable.search.value || ''
                    : '';

                consultarApiReportes(parametros).then(function (respuesta) {
                    const listado = respuesta.listado || {};
                    const registros = Array.isArray(listado.registros)
                        ? listado.registros
                        : [];
                    const total = Number(listado.total_registros) || 0;
                    const totalFiltrado = Number(listado.total_filtrados) || 0;

                    actualizarConteoReporte(totalFiltrado);
                    callback({
                        draw: datosDataTable.draw,
                        recordsTotal: total,
                        recordsFiltered: totalFiltrado,
                        data: registros
                    });
                }).catch(function (error) {
                    console.error('[Reportes de anormalidades] No se cargó el listado general.', {
                        filtros: parametros,
                        error: error
                    });
                    actualizarConteoReporte(0);
                    callback({
                        draw: datosDataTable.draw,
                        recordsTotal: 0,
                        recordsFiltered: 0,
                        data: []
                    });
                });
            },
            columns: [
                {
                    data: null,
                    className: 'text-center',
                    searchable: false,
                    render: function (dato, tipo, fila) {
                        const idAnormalidad = escaparHtml(fila.id_anormalidad || '');
                        return '<button type="button" class="ra-action ra-action-reporte-info" ' +
                            'data-ra-id-anormalidad="' + idAnormalidad + '" ' +
                            'title="Ver reporte de la anormalidad" ' +
                            'aria-label="Ver reporte de la anormalidad ' + idAnormalidad + '">' +
                            '<i class="bi bi-info-lg" aria-hidden="true"></i>' +
                            '</button>';
                    }
                },
                {
                    data: 'consecutivo',
                    className: 'ra-cell-strong',
                    render: function (dato, tipo, fila) {
                        if (tipo !== 'display') {
                            return dato || fila.no_seguimiento || '';
                        }

                        return '<span class="ra-badge">' +
                            escaparHtml(dato || 'Sin consecutivo') + '</span>' +
                            '<span class="ra-cell-note">' +
                            escaparHtml(fila.no_seguimiento || '') + '</span>';
                    }
                },
                {
                    data: 'nombre_estatus',
                    render: function (dato, tipo, fila) {
                        const clase = String(fila.clave_estatus) === '6'
                            ? 'is-cerrada'
                            : 'is-abierta';
                        return '<span class="ra-badge ' + clase + '">' +
                            escaparHtml(dato || 'Sin estatus') + '</span>';
                    }
                },
                {
                    data: 'nombre_area',
                    render: function (dato) {
                        return '<span class="ra-badge">' +
                            escaparHtml(dato || 'Sin área') + '</span>';
                    }
                },
                {
                    data: 'nombre_linea',
                    render: function (dato) {
                        return escaparHtml(dato || 'Sin línea');
                    }
                },
                {
                    // Opcional: registros previos o líneas sin modelos lo tienen vacío.
                    data: 'modelo',
                    render: function (dato) {
                        return escaparHtml(dato || 'Sin modelo');
                    }
                },
                {
                    data: 'nombre_turno',
                    render: function (dato) {
                        return '<span class="ra-badge">' +
                            escaparHtml(dato || 'Sin turno') + '</span>';
                    }
                },
                {
                    data: 'fecha_hora_deteccion',
                    render: function (dato) {
                        return escaparHtml(dato || 'Sin fecha');
                    }
                },
                {
                    data: 'estacion_generacion',
                    render: function (dato) {
                        return escaparHtml(dato || 'Sin registrar');
                    }
                },
                {
                    data: 'nombre_detector',
                    render: function (dato) {
                        return escaparHtml(dato || 'Sin registrar');
                    }
                }
            ],
            language: {
                processing: 'Cargando registros...',
                search: 'Buscar:',
                searchPlaceholder: 'Consecutivo, línea, modelo, estación, persona...',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'No hay registros disponibles',
                infoFiltered: '(filtrados de _MAX_ registros)',
                zeroRecords: 'No se encontraron anormalidades',
                emptyTable: 'No hay anormalidades para los filtros seleccionados',
                paginate: {
                    first: 'Primero',
                    previous: 'Anterior',
                    next: 'Siguiente',
                    last: 'Último'
                }
            }
        });
    }

    function recargarTablaReporte() {
        if (!tablaReporteDT) {
            inicializarTablaReporte();
            return;
        }

        tablaReporteDT.ajax.reload(null, true);
    }

    /* ================= Reporte individual de la anormalidad ================= */

    function textoDatoReporte(valor, predeterminado) {
        if (valor === null || valor === undefined || String(valor).trim() === '') {
            return predeterminado || 'Sin registro';
        }

        return String(valor);
    }

    function fechaDatoReporte(valor) {
        const fecha = textoDatoReporte(valor, 'Sin registro');
        return fecha === 'Sin registro' ? fecha : fecha.replace('T', ' ');
    }

    function campoDatoReporte(etiqueta, valor, anchoCompleto, claseAdicional) {
        return '<div class="ra-report-field' + (anchoCompleto ? ' is-wide' : '') +
            (claseAdicional ? ' ' + claseAdicional : '') + '">' +
            '<span>' + escaparHtml(etiqueta) + '</span>' +
            '<strong>' + escaparHtml(textoDatoReporte(valor)) + '</strong>' +
            '</div>';
    }

    function resultadoDatoReporte(valor) {
        const resultado = textoDatoReporte(valor, 'Pendiente').toUpperCase();
        const clase = resultado === 'OK'
            ? ' is-ok'
            : (resultado === 'NG' ? ' is-ng' : ' is-pending');
        return '<span class="ra-report-result' + clase + '">' +
            '<i class="bi ' + (resultado === 'OK' ? 'bi-check-lg' :
                (resultado === 'NG' ? 'bi-x-lg' : 'bi-clock')) +
                '" aria-hidden="true"></i>' +
            escaparHtml(resultado) + '</span>';
    }

    function campoResultadoReporte(etiqueta, valor) {
        return '<div class="ra-report-field is-result"><span>' +
            escaparHtml(etiqueta) + '</span>' + resultadoDatoReporte(valor) + '</div>';
    }

    function estadoVacioReporte(mensaje) {
        return '<div class="ra-report-empty">' +
            '<i class="bi bi-inbox" aria-hidden="true"></i>' +
            '<span>' + escaparHtml(mensaje) + '</span>' +
            '</div>';
    }

    function etapaFlujoReporte(numero, titulo, descripcion, contenido, registrada) {
        const iconos = {
            '1': 'bi-exclamation-diamond',
            '2': 'bi-clipboard-check',
            '3': 'bi-tools',
            '4': 'bi-shield-check',
            '5': 'bi-patch-check'
        };
        const icono = iconos[String(numero)] || 'bi-circle';

        return '<article class="ra-report-stage is-stage-' + escaparHtml(numero) + '">' +
            '<header class="ra-report-stage-header">' +
                '<span class="ra-report-stage-number" aria-hidden="true">' +
                    '<i class="bi ' + icono + '"></i></span>' +
                '<div><span class="ra-report-stage-kicker">Paso ' +
                    escaparHtml(numero) + '</span><h4>' + escaparHtml(titulo) + '</h4>' +
                    '<small>' + escaparHtml(descripcion) + '</small></div>' +
                '<span class="ra-badge ' + (registrada ? 'is-cerrada' : 'is-abierta') +
                    ' ms-auto"><i class="bi ' + (registrada ? 'bi-check-circle' : 'bi-clock') +
                    '" aria-hidden="true"></i>' +
                    (registrada ? 'Registrado' : 'Sin registro') + '</span>' +
            '</header>' + contenido +
            '</article>';
    }

    function guiaFlujoReporte(estados) {
        const pasos = [
            { nombre: 'Detección', icono: 'bi-exclamation-diamond' },
            { nombre: 'Primera revisión', icono: 'bi-clipboard-check' },
            { nombre: 'Análisis', icono: 'bi-tools' },
            { nombre: 'Verificación', icono: 'bi-shield-check' },
            { nombre: 'Cierre', icono: 'bi-patch-check' }
        ];

        return '<div class="ra-report-flow-guide" aria-label="Avance del flujo">' +
            pasos.map(function (paso, indice) {
                const completado = Boolean(estados[indice]);
                return '<div class="ra-report-flow-step' +
                    (completado ? ' is-complete' : ' is-pending') + '">' +
                    '<span><i class="bi ' + paso.icono + '" aria-hidden="true"></i></span>' +
                    '<small>' + escaparHtml(paso.nombre) + '</small>' +
                    '</div>';
            }).join('') + '</div>';
    }

    function tablaResultadosReporte(encabezados, filas) {
        if (!filas.length) {
            return estadoVacioReporte('No existen registros en esta sección.');
        }

        return '<div class="ra-table-wrap"><table class="ra-report-mini-table">' +
            '<thead><tr>' + encabezados.map(function (encabezado) {
                return '<th>' + escaparHtml(encabezado) + '</th>';
            }).join('') + '</tr></thead><tbody>' + filas.join('') +
            '</tbody></table></div>';
    }

    function firmaReporte(nombreArchivo, carpeta, descripcion) {
        if (!nombreArchivo) {
            return campoDatoReporte('Firma', 'Sin firma registrada', true);
        }

        const ruta = './img/anormalidades/' + carpeta + '/' +
            encodeURIComponent(String(nombreArchivo));
        return '<div class="ra-report-field is-wide is-signature"><span>Firma registrada</span>' +
            '<img class="ra-report-signature" src="' + escaparHtml(ruta) + '" ' +
            'alt="' + escaparHtml(descripcion) + '" loading="lazy"></div>';
    }

    function renderAperturaReporte(apertura) {
        return '<div class="ra-report-field-grid">' +
            campoDatoReporte('No. seguimiento', apertura.no_seguimiento) +
            campoDatoReporte('Estatus actual', apertura.nombre_estatus) +
            campoDatoReporte('Fecha y hora de detección',
                fechaDatoReporte(apertura.fecha_hora_deteccion)) +
            campoDatoReporte('Turno', apertura.turno) +
            campoDatoReporte('Área', apertura.nombre_area) +
            campoDatoReporte('Línea', apertura.nombre_linea) +
            campoDatoReporte('Modelo', textoDatoReporte(apertura.modelo, 'Sin modelo registrado')) +
            campoDatoReporte('Tipo de anormalidad', apertura.tipo_anormalidad) +
            campoDatoReporte('Cantidad', apertura.cantidad) +
            campoDatoReporte('No. lote', apertura.no_lote) +
            campoDatoReporte('No. serial', apertura.no_serial) +
            campoDatoReporte('Estación donde se detectó', apertura.estacion_deteccion) +
            campoDatoReporte('Estación donde se generó', apertura.estacion_generacion) +
            campoDatoReporte('Detalle de la anormalidad', apertura.detalle_anormalidad,
                true, 'is-note') +
            campoDatoReporte('Nómina de quien detectó', apertura.nomina_detector) +
            campoDatoReporte('Nombre de quien detectó', apertura.nombre_detector) +
            campoDatoReporte('Nómina de quien registró', apertura.nomina_registra) +
            campoDatoReporte('Nombre de quien registró', apertura.nombre_registra) +
            '</div>';
    }

    function renderPrimeraRevisionReporte(revision, factores, tipoAnormalidad) {
        if (!revision) {
            return estadoVacioReporte('La primera revisión todavía no ha sido registrada.');
        }

        const categorias = Array.isArray(revision.categorias_5m)
            ? revision.categorias_5m.map(function (categoria) {
                return categoria.nombre_5m;
            }).join(', ')
            : '';
        const responsables = Array.isArray(factores) && factores.length
            ? '<div class="ra-report-subgrid">' + factores.map(function (factor) {
                return '<div class="ra-report-subsection"><h5>' +
                    escaparHtml(textoDatoReporte(factor.nombre_factor)) + '</h5>' +
                    '<div class="ra-report-field-grid">' +
                    campoDatoReporte('Nómina responsable', factor.nomina_responsable) +
                    campoDatoReporte('Nombre responsable', factor.nombre_responsable) +
                    '</div></div>';
            }).join('') + '</div>'
            : estadoVacioReporte('No hay áreas o departamentos responsables registrados.');

        return '<div class="ra-report-field-grid">' +
            campoDatoReporte('Tipo o clasificación', tipoAnormalidad) +
            campoDatoReporte('Criticidad', revision.criticidad) +
            campoDatoReporte('Nivel de respuesta', revision.nivel_respuesta) +
            campoDatoReporte('Clasificación 5M', categorias || 'Sin registro') +
            campoDatoReporte('Fecha y hora de revisión',
                fechaDatoReporte(revision.fecha_hora_revision)) +
            campoDatoReporte('Nómina de quien registró', revision.nomina_registra) +
            campoDatoReporte('Nombre de quien registró', revision.nombre_registra) +
            '</div><div class="ra-report-subsection mt-3"><h5>Área/departamento que atiende</h5>' +
            responsables + '</div>';
    }

    function renderAnalisisReporte(analisis) {
        const qa = Array.isArray(analisis.qa) ? analisis.qa : [];
        const pe = analisis.pe;
        const mf = analisis.mf;
        const causa = analisis.causa_contramedida;
        const hayRegistro = Boolean(pe || mf || qa.length || causa);

        if (!hayRegistro) {
            return estadoVacioReporte('No se han registrado análisis o contramedidas.');
        }

        const bloques = [];

        bloques.push('<div class="ra-report-subsection"><h5>Método de revisión del equipo (PE)</h5>' +
            (pe ? '<div class="ra-report-field-grid">' +
                campoDatoReporte('Revisión realizada', pe.revision_realizada, true) +
                campoResultadoReporte('Resultado', pe.resultado) + '</div>'
                : estadoVacioReporte('Sin análisis PE.')) + '</div>');
        bloques.push('<div class="ra-report-subsection"><h5>Análisis de operación (MF)</h5>' +
            (mf ? '<div class="ra-report-field-grid">' +
                campoDatoReporte('Análisis realizado', mf.analisis_realizado, true) +
                campoResultadoReporte('Resultado', mf.resultado) + '</div>'
                : estadoVacioReporte('Sin análisis MF.')) + '</div>');

        const filasQa = qa.map(function (registro) {
            return '<tr><td>' + escaparHtml(textoDatoReporte(registro.momento)) + '</td>' +
                '<td>' + escaparHtml(textoDatoReporte(registro.confirmacion)) + '</td>' +
                '<td>' + escaparHtml(textoDatoReporte(registro.cantidad)) + '</td>' +
                '<td>' + resultadoDatoReporte(registro.resultado) + '</td></tr>';
        });
        bloques.push('<div class="ra-report-subsection"><h5>Confirmaciones de Calidad (QA)</h5>' +
            tablaResultadosReporte(['Momento', 'Confirmación', 'Cantidad', 'Resultado'], filasQa) +
            '</div>');
        bloques.push('<div class="ra-report-subsection"><h5>Causa y contramedida</h5>' +
            (causa ? '<div class="ra-report-field-grid">' +
                campoDatoReporte('Causa', causa.causa, true, 'is-cause') +
                campoDatoReporte('Contramedida', causa.contramedida, true, 'is-action') +
                campoDatoReporte('Fecha de registro', fechaDatoReporte(causa.fecha_registro)) +
                campoDatoReporte('Nómina de quien registró', causa.nomina_registra) +
                campoDatoReporte('Nombre de quien registró', causa.nombre_registra) +
                '</div>' : estadoVacioReporte('Sin causa y contramedida.')) + '</div>');

        return '<div class="ra-report-subgrid">' + bloques.join('') + '</div>';
    }

    function renderVerificacionCalidadReporte(verificacion) {
        const cierre = verificacion ? verificacion.cierre : null;
        const muestras = verificacion && Array.isArray(verificacion.muestras)
            ? verificacion.muestras
            : [];

        if (!cierre && !muestras.length) {
            return estadoVacioReporte('La verificación de calidad todavía no ha sido registrada.');
        }

        const filasMuestras = muestras.map(function (muestra) {
            return '<tr><td>' + escaparHtml(textoDatoReporte(muestra.momento)) + '</td>' +
                '<td>' + escaparHtml(textoDatoReporte(muestra.no_revision)) + '</td>' +
                '<td>' + escaparHtml(textoDatoReporte(muestra.codigo_serial)) + '</td>' +
                '<td>' + resultadoDatoReporte(muestra.resultado) + '</td></tr>';
        });
        const datosCierre = cierre
            ? '<div class="ra-report-field-grid">' +
                campoDatoReporte('Disposición', cierre.disposicion) +
                campoDatoReporte('Descripción de disposición',
                    cierre.disposicion_descripcion, true) +
                campoDatoReporte('Confirmación de reinicio',
                    cierre.confirmacion_reinicio, true) +
                campoDatoReporte('Fecha de confirmación',
                    fechaDatoReporte(cierre.fecha_confirmacion_reinicio)) +
                campoDatoReporte('Nómina que aprobó el reinicio', cierre.nomina_aprobReinicio) +
                campoDatoReporte('Nombre que aprobó el reinicio', cierre.nombre_aprobReinicio) +
                '</div>'
            : estadoVacioReporte('La disposición y el reinicio no se han registrado.');

        return datosCierre + '<div class="ra-report-subsection mt-3"><h5>Muestras de liberación</h5>' +
            tablaResultadosReporte(['Momento', 'No.', 'Serial', 'Resultado'], filasMuestras) +
            '</div>';
    }

    function renderCierreFirmadoReporte(registro, tipo) {
        if (!registro) {
            return '<div class="ra-report-subsection"><h5>' + escaparHtml(tipo) + '</h5>' +
                estadoVacioReporte('Este cierre todavía no ha sido registrado.') + '</div>';
        }

        const esCalidad = tipo === 'Cierre de Staff QA';
        return '<div class="ra-report-subsection"><h5>' + escaparHtml(tipo) + '</h5>' +
            '<div class="ra-report-field-grid">' +
            campoDatoReporte('Fecha de cierre', fechaDatoReporte(registro.fecha_cierre)) +
            campoDatoReporte('Nómina', registro.nomina_cierra) +
            campoDatoReporte('Nombre', registro.nombre_cierra) +
            (esCalidad ? campoDatoReporte('Factor responsable', registro.factor_responsable) : '') +
            firmaReporte(registro.firma, esCalidad ? 'firmasQA' : 'firmasGerente',
                'Firma registrada de ' + tipo) +
            '</div></div>';
    }

    function renderFlujoNormalReporte(detalle) {
        const apertura = detalle.apertura || {};
        const revision = detalle.primera_revision;
        const analisis = detalle.analisis || { qa: [] };
        const verificacion = detalle.verificacion_calidad || {};
        const cierres = detalle.cierres || {};
        const analisisRegistrado = Boolean(
            analisis.pe || analisis.mf ||
            (Array.isArray(analisis.qa) && analisis.qa.length) ||
            analisis.causa_contramedida
        );
        const verificacionRegistrada = Boolean(
            verificacion.cierre ||
            (Array.isArray(verificacion.muestras) && verificacion.muestras.length)
        );
        const cierreRegistrado = Boolean(cierres.calidad || cierres.gerente);
        const claseEstatus = String(apertura.clave_estatus) === '6'
            ? 'is-cerrada'
            : 'is-abierta';

        return '<div class="ra-report-summary"><span><small>No. seguimiento</small><strong>' +
            escaparHtml(textoDatoReporte(apertura.no_seguimiento)) + '</strong></span>' +
            '<span class="ra-badge ra-report-summary-status ' + claseEstatus + '">' +
            '<i class="bi bi-activity" aria-hidden="true"></i>' +
            escaparHtml(textoDatoReporte(apertura.nombre_estatus)) + '</span></div>' +
            guiaFlujoReporte([
                true,
                Boolean(revision),
                analisisRegistrado,
                verificacionRegistrada,
                Boolean(cierres.gerente)
            ]) +
            '<div class="ra-report-flow">' +
            etapaFlujoReporte('1', 'Detección de la anormalidad',
                'Información capturada al abrir el registro.', renderAperturaReporte(apertura), true) +
            etapaFlujoReporte('2', 'Primera revisión',
                'Clasificación, prioridad y responsables.',
                renderPrimeraRevisionReporte(revision, detalle.factores || [],
                    apertura.tipo_anormalidad), Boolean(revision)) +
            etapaFlujoReporte('3', 'Análisis y contramedidas',
                'Revisiones PE, MF, QA y acciones realizadas.',
                renderAnalisisReporte(analisis), analisisRegistrado) +
            etapaFlujoReporte('4', 'Verificación y liberación de Calidad',
                'Disposición, muestras y confirmación del reinicio.',
                renderVerificacionCalidadReporte(verificacion), verificacionRegistrada) +
            etapaFlujoReporte('5', 'Cierre del reporte',
                'Firmas de Staff QA y autorización final del gerente.',
                '<div class="ra-report-subgrid">' +
                renderCierreFirmadoReporte(cierres.calidad, 'Cierre de Staff QA') +
                renderCierreFirmadoReporte(cierres.gerente, 'Autorización del gerente') +
                '</div>', cierreRegistrado) +
            '</div>';
    }

    function renderSeguimientoReincidenciaReporte(seguimiento) {
        const confirmaciones = Array.isArray(seguimiento.confirmaciones_qa)
            ? seguimiento.confirmaciones_qa
            : [];
        const filasQa = confirmaciones.map(function (confirmacion) {
            return '<tr><td>' + escaparHtml(textoDatoReporte(confirmacion.momento)) + '</td>' +
                '<td>' + escaparHtml(textoDatoReporte(confirmacion.no_revision)) + '</td>' +
                '<td>' + resultadoDatoReporte(confirmacion.resultado) + '</td></tr>';
        });

        return '<article class="ra-report-recurrence-item">' +
            '<header class="ra-report-recurrence-title"><h5>' +
            '<span class="ra-report-recurrence-icon"><i class="bi bi-arrow-repeat" ' +
            'aria-hidden="true"></i></span>Reincidencia ' +
            escaparHtml(textoDatoReporte(seguimiento.consecutivo)) + '</h5>' +
            '<span class="ra-badge">' +
            escaparHtml(fechaDatoReporte(seguimiento.fecha_hora)) + '</span></header>' +
            '<div class="ra-report-field-grid">' +
            campoDatoReporte('Cantidad', seguimiento.cantidad) +
            campoDatoReporte('Disposición', seguimiento.disposicion) +
            campoDatoReporte('Descripción de disposición',
                seguimiento.disposicion_descripcion, true) +
            campoDatoReporte('Nómina de quien registró', seguimiento.nomina_registra) +
            campoDatoReporte('Nombre de quien registró', seguimiento.nombre_registra) +
            '</div><div class="ra-report-subgrid">' +
            '<div class="ra-report-subsection"><h5>Causa y contramedida</h5>' +
            (seguimiento.causa ? '<div class="ra-report-field-grid">' +
                campoDatoReporte('Causa', seguimiento.causa, true, 'is-cause') +
                campoDatoReporte('Contramedida', seguimiento.contramedida,
                    true, 'is-action') +
                campoDatoReporte('Fecha de registro',
                    fechaDatoReporte(seguimiento.fecha_causa_contramedida)) +
                campoDatoReporte('Nómina de quien atiende', seguimiento.nomina_atiende) +
                campoDatoReporte('Nombre de quien atiende', seguimiento.nombre_atiende) +
                '</div>' : estadoVacioReporte('Sin causa y contramedida.')) + '</div>' +
            '<div class="ra-report-subsection"><h5>Confirmación de QA</h5>' +
            '<div class="ra-report-field-grid">' +
            campoDatoReporte('Nómina del inspector', seguimiento.nomina_inspector) +
            campoDatoReporte('Nombre del inspector', seguimiento.nombre_inspector) +
            '</div>' + tablaResultadosReporte(['Momento', 'Revisión', 'Resultado'], filasQa) +
            '</div></div></article>';
    }

    function renderFlujoReincidenciaReporte(detalle) {
        const reincidencia = detalle.reincidencia;

        if (!reincidencia) {
            return estadoVacioReporte('Esta anormalidad no tiene un registro de reincidencia.');
        }

        const general = reincidencia.general || {};
        const seguimientos = Array.isArray(reincidencia.seguimientos)
            ? reincidencia.seguimientos
            : [];

        return '<div class="ra-report-summary"><span><small>Registro general</small><strong>' +
            escaparHtml(textoDatoReporte(general.tipo_reincidencia, 'Reincidencia')) +
            '</strong></span><span><small>Total de reincidencias</small><strong>' +
            seguimientos.length + '</strong></span></div>' +
            etapaFlujoReporte('1', 'Registro general de reincidencia',
                'Datos que relacionan la reincidencia con la anormalidad original.',
                '<div class="ra-report-field-grid">' +
                campoDatoReporte('No. seguimiento', general.no_seguimiento) +
                campoDatoReporte('Fecha y hora', fechaDatoReporte(general.fecha_hora)) +
                campoDatoReporte('Turno', general.turno) +
                campoDatoReporte('Tipo de reincidencia', general.tipo_reincidencia) +
                campoDatoReporte('Registrado por', general.registrado_por) +
                campoDatoReporte('Detalle de la anormalidad',
                    general.detalle_anormalidad, true, 'is-note') + '</div>', true) +
            '<div class="ra-report-stage mt-3"><header class="ra-report-stage-header">' +
            '<span class="ra-report-stage-number" aria-hidden="true">' +
            '<i class="bi bi-list-check"></i></span><div>' +
            '<span class="ra-report-stage-kicker">Seguimientos</span>' +
            '<h4>Historial de reincidencias</h4>' +
            '<small>Datos, contramedidas y confirmaciones registradas.</small></div></header>' +
            (seguimientos.length
                ? seguimientos.map(renderSeguimientoReincidenciaReporte).join('')
                : estadoVacioReporte('Todavía no existen seguimientos de reincidencia.')) +
            '</div>';
    }

    function mostrarEstadoDetalleReporte(mensaje, esError) {
        elementos.detalleReporteEstado.hidden = false;
        elementos.detalleReporteContenido.hidden = true;
        elementos.detalleReporteEstado.classList.toggle('is-error', Boolean(esError));
        elementos.detalleReporteEstado.innerHTML = esError
            ? '<i class="bi bi-exclamation-triangle" aria-hidden="true"></i><span>' +
                escaparHtml(mensaje) + '</span>'
            : '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>' +
                escaparHtml(mensaje);
    }

    async function abrirReporteAnormalidad(idAnormalidad) {
        if (!/^\d+$/.test(String(idAnormalidad)) || !window.bootstrap ||
            !window.bootstrap.Modal) {
            return;
        }

        const numeroSolicitud = ++solicitudDetalleReporteActual;
        const instanciaModal = window.bootstrap.Modal.getOrCreateInstance(
            elementos.modalReporte
        );
        const parametros = obtenerParametrosListadoReporte();
        parametros.seccion = 'detalle';
        parametros.idAnormalidad = idAnormalidad;

        elementos.modalReporteTitulo.textContent = 'Cargando registro...';
        elementos.detalleFlujoNormal.innerHTML = '';
        elementos.detalleFlujoReincidencia.innerHTML = '';
        elementos.detalleTotalReincidencias.textContent = '0';
        mostrarEstadoDetalleReporte('Consultando el flujo de la anormalidad...', false);

        if (window.bootstrap.Tab && elementos.tabFlujoNormal) {
            window.bootstrap.Tab.getOrCreateInstance(elementos.tabFlujoNormal).show();
        }

        instanciaModal.show();

        try {
            const respuesta = await consultarApiReportes(parametros);

            if (numeroSolicitud !== solicitudDetalleReporteActual) {
                return;
            }

            const detalle = respuesta.detalle || {};
            const apertura = detalle.apertura || {};
            const seguimientos = detalle.reincidencia &&
                Array.isArray(detalle.reincidencia.seguimientos)
                ? detalle.reincidencia.seguimientos
                : [];

            elementos.modalReporteTitulo.textContent =
                textoDatoReporte(apertura.no_seguimiento, 'Anormalidad');
            elementos.detalleFlujoNormal.innerHTML = renderFlujoNormalReporte(detalle);
            elementos.detalleFlujoReincidencia.innerHTML =
                renderFlujoReincidenciaReporte(detalle);
            elementos.detalleTotalReincidencias.textContent = String(seguimientos.length);
            elementos.detalleReporteEstado.classList.remove('is-error');
            elementos.detalleReporteEstado.innerHTML = '';
            elementos.detalleReporteEstado.hidden = true;
            elementos.detalleReporteContenido.hidden = false;
        } catch (error) {
            if (numeroSolicitud !== solicitudDetalleReporteActual) {
                return;
            }

            console.error('[Reportes de anormalidades] No se cargó el reporte individual.', {
                idAnormalidad: idAnormalidad,
                filtros: parametros,
                error: error
            });
            elementos.modalReporteTitulo.textContent = 'Reporte no disponible';
            mostrarEstadoDetalleReporte(
                error.message || 'No fue posible consultar el reporte de la anormalidad.',
                true
            );
        }
    }

    /* ======================= Pestañas y filtros ======================= */

    function obtenerPestanaActiva() {
        const activa = elementos.pestanas.find(function (pestana) {
            return pestana.classList.contains('is-active');
        });

        return activa ? activa.dataset.raTab : 'resumen';
    }

    function invalidarContenidoPestanas() {
        Object.keys(pestanasCargadas).forEach(function (nombre) {
            pestanasCargadas[nombre] = false;
        });

        /* Invalida ambos subtabs para que ningún filtro reutilice datos viejos. */
        solicitudHistoricoActual++;
        solicitudHistoricoLineasActual++;
        historicoTotalServidor = null;
        historicoLineasServidor = null;
    }

    /* Cargadores independientes para actualizar cada pestaña por separado. */
    function cargarResumenGeneral(lineas) {
        cargarHistoricoSeleccionado();
        actualizarAreasResumenDesdeServidor();
        actualizarEstatusResumenDesdeServidor();
        actualizarHallazgosDesdeServidor();
    }

    function cargarLineasAreas(lineas) {
        actualizarCriticidadLineasDesdeServidor();
        actualizarTotalesLineasDesdeServidor();
        actualizarTendenciaDesdeServidor();
        actualizarClasificacion5mDesdeServidor();
    }

    function cargarCriticasTipos(lineas) {
        actualizarTopCriticasDesdeServidor();
        actualizarTopNoCriticasDesdeServidor();
        actualizarNivelRespuestaDesdeServidor();
        inicializarTablaEstatusCriticas();
    }

    function cargarReincidencias() {
        actualizarTopReincidentesDesdeServidor();
        actualizarTopEstacionesReincidenciaDesdeServidor();
        cargarCatalogoTiposFrecuentes();
        actualizarFrecuentesDesdeServidor();
    }

    function cargarReporteDetallado() {
        actualizarTopEstacionesReporte(
            'top_estaciones_generacion',
            elementos.topEstacionesGeneracion,
            'is-critica'
        );
        actualizarTopEstacionesReporte(
            'top_estaciones_deteccion',
            elementos.topEstacionesDeteccion,
            'is-cyan'
        );
        cargarCatalogosReporteDetallado();
        recargarTablaReporte();
    }

    /**
     * Coordinador de carga diferida. Evita calcular todas las gráficas al
     * entrar a la pantalla y permite actualizar una pestaña de forma aislada.
     */
    function cargarContenidoPestana(nombre, forzar) {
        if (!Object.prototype.hasOwnProperty.call(pestanasCargadas, nombre) ||
            (pestanasCargadas[nombre] && !forzar)) {
            return;
        }

        const lineas = lineasFiltradas();

        switch (nombre) {
            case 'resumen':
                cargarResumenGeneral(lineas);
                break;
            case 'lineas':
                cargarLineasAreas(lineas);
                break;
            case 'criticas':
                cargarCriticasTipos(lineas);
                break;
            case 'reincidencias':
                cargarReincidencias(lineas);
                break;
            case 'reportes':
                cargarReporteDetallado();
                break;
        }

        pestanasCargadas[nombre] = true;
    }

    function activarPestana(boton) {
        const destino = boton.dataset.raTab;

        elementos.pestanas.forEach(function (pestana) {
            const activa = pestana === boton;
            pestana.classList.toggle('is-active', activa);
            pestana.setAttribute('aria-selected', activa ? 'true' : 'false');
        });

        Object.keys(elementos.paneles).forEach(function (nombre) {
            elementos.paneles[nombre].hidden = nombre !== destino;
        });

        cargarContenidoPestana(destino, false);

        /*
         * Las gráficas y tablas que se calculan con el ancho del contenedor
         * necesitan recalcularse cuando su panel pasa de oculto a visible.
         */
        if (destino === 'resumen') {
            renderHistorico(seriesHistorico);
        }

        if (destino === 'lineas' && tendenciaServidor && !tendenciaServidor.mensaje) {
            renderTendenciaDesdeServidor(tendenciaServidor);
        }

        if (destino === 'criticas' && tablaEstatusCriticasDT) {
            tablaEstatusCriticasDT.columns.adjust();
        }

        if (destino === 'reincidencias' && tablaFrecuentesDT) {
            tablaFrecuentesDT.columns.adjust();
        }

        if (destino === 'reportes' && tablaReporteDT) {
            tablaReporteDT.columns.adjust();
        }
    }

    function actualizarAlcance() {
        const opcionArea = elementos.area.options[elementos.area.selectedIndex];
        const opcionLinea = elementos.linea.options[elementos.linea.selectedIndex];
        const opcionTurno = elementos.turno.options[elementos.turno.selectedIndex];
        const area = elementos.area.value === 'todas'
            ? 'Todas las áreas'
            : (opcionArea ? opcionArea.textContent : 'Área seleccionada');
        const linea = elementos.linea.value === 'todas'
            ? 'todas las líneas'
            : 'línea ' + (opcionLinea ? opcionLinea.textContent : elementos.linea.value);
        const turno = elementos.turno.value === 'todos'
            ? 'todos los turnos'
            : (opcionTurno ? opcionTurno.textContent : 'turno seleccionado');

        elementos.scope.textContent = area;
        elementos.scopePeriodo.textContent = linea + ' · ' + turno;
    }

    /** Ejecuta una operación de reportes y valida la estructura básica del JSON. */
    async function consultarApiReportes(parametros) {
        const cuerpo = new URLSearchParams();

        Object.keys(parametros).forEach(function (nombre) {
            cuerpo.append(nombre, parametros[nombre]);
        });

        const respuestaHttp = await fetch(RUTA_API_REPORTES, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: cuerpo.toString()
        });

        let respuesta;

        try {
            respuesta = await respuestaHttp.json();
        } catch (error) {
            throw new Error('El servidor devolvió una respuesta que no es JSON válido.');
        }

        if (!respuestaHttp.ok || respuesta.status !== 'ok') {
            throw new Error(respuesta.mensaje || 'No fue posible consultar la información.');
        }

        return respuesta.data;
    }

    /*
     * Construye los filtros comunes sin enviar las opciones "todas". La API
     * decide el periodo predeterminado cuando falta cualquiera de las fechas.
     */
    function obtenerParametrosReporte(opcion, seccion) {
        const parametros = { opcion: opcion };

        if (seccion) {
            parametros.seccion = seccion;
        }

        if (elementos.fechaInicio.value) {
            parametros.fechaInicio = elementos.fechaInicio.value;
        }

        if (elementos.fechaFin.value) {
            parametros.fechaFin = elementos.fechaFin.value;
        }

        if (elementos.area.value !== 'todas') {
            parametros.idArea = elementos.area.value;
        }

        if (elementos.linea.value !== 'todas') {
            parametros.codigoLinea = elementos.linea.value;
        }

        if (elementos.turno.value !== 'todos') {
            parametros.idTurno = elementos.turno.value;
        }

        return parametros;
    }

    function obtenerParametrosKpis() {
        return obtenerParametrosReporte(OPERACIONES_REPORTES.tarjetasResumen);
    }

    /*
     * El comparativo entre áreas siempre considera todas las áreas y líneas.
     * Por eso se eliminan esos dos filtros antes de enviar la petición.
     */
    function obtenerParametrosAreasResumen() {
        const parametros = obtenerParametrosReporte(
            OPERACIONES_REPORTES.resumenGeneral,
            'areas'
        );

        delete parametros.idArea;
        delete parametros.codigoLinea;

        return parametros;
    }

    /**
     * Actualiza sólo las tarjetas raKpis con el resumen calculado en SQL.
     * El consecutivo evita mostrar una respuesta vieja sobre filtros nuevos.
     */
    async function actualizarKpisDesdeServidor() {
        const numeroSolicitud = ++solicitudKpisActual;
        elementos.kpis.setAttribute('aria-busy', 'true');

        try {
            const resumen = await consultarApiReportes(obtenerParametrosKpis());

            if (numeroSolicitud !== solicitudKpisActual) {
                return false;
            }

            renderKpis(resumen);
            return true;
        } catch (error) {
            if (numeroSolicitud === solicitudKpisActual) {
                console.error('[Reportes de anormalidades] No se cargaron los KPI.', {
                    filtros: obtenerParametrosKpis(),
                    error: error
                });

                renderKpis({
                    total_anormalidades: 0,
                    anormalidades_criticas: 0,
                    porcentaje_criticas: 0,
                    anormalidades_finalizadas: 0,
                    porcentaje_finalizadas: 0,
                    anormalidades_en_proceso: 0
                }, 'No fue posible cargar la información');
            }

            return false;
        } finally {
            if (numeroSolicitud === solicitudKpisActual) {
                elementos.kpis.removeAttribute('aria-busy');
            }
        }
    }

    /**
     * Carga la distribución por área. La clave de caché sólo contiene
     * fecha y turno, por lo que cambiar área o línea no repite esta consulta.
     */
    async function actualizarAreasResumenDesdeServidor() {
        const parametros = obtenerParametrosAreasResumen();
        const claveSolicitud = JSON.stringify(parametros);

        if (claveAreasResumenServidor === claveSolicitud && areasResumenServidor) {
            renderAreasResumen(areasResumenServidor);
            return;
        }

        const numeroSolicitud = ++solicitudAreasResumenActual;
        claveAreasResumenServidor = claveSolicitud;
        areasResumenServidor = { mensaje: 'Cargando anormalidades por área...' };
        renderAreasResumen(areasResumenServidor);

        try {
            const respuesta = await consultarApiReportes(parametros);

            if (numeroSolicitud !== solicitudAreasResumenActual ||
                claveSolicitud !== claveAreasResumenServidor) {
                return;
            }

            areasResumenServidor = {
                filas: Array.isArray(respuesta.areas) ? respuesta.areas : []
            };
            renderAreasResumen(areasResumenServidor);
        } catch (error) {
            if (numeroSolicitud !== solicitudAreasResumenActual) {
                return;
            }

            console.error('[Reportes de anormalidades] No se cargaron los totales por área.', {
                filtros: parametros,
                error: error
            });

            /* Se limpia la clave para permitir un nuevo intento posterior. */
            claveAreasResumenServidor = '';
            areasResumenServidor = {
                mensaje: 'No fue posible cargar las anormalidades por área.',
                error: true
            };
            renderAreasResumen(areasResumenServidor);
        }
    }

    /**
     * Consulta los seis estatus con todos los filtros seleccionados. La clave
     * evita repetir la petición mientras el conjunto de filtros no cambie.
     */
    async function actualizarEstatusResumenDesdeServidor() {
        const parametros = obtenerParametrosReporte(
            OPERACIONES_REPORTES.resumenGeneral,
            'estatus'
        );
        const claveSolicitud = JSON.stringify(parametros);

        if (claveEstatusResumenServidor === claveSolicitud && estatusResumenServidor) {
            renderEstatusResumen(estatusResumenServidor);
            return;
        }

        const numeroSolicitud = ++solicitudEstatusResumenActual;
        claveEstatusResumenServidor = claveSolicitud;
        estatusResumenServidor = { mensaje: 'Cargando avance del flujo...' };
        renderEstatusResumen(estatusResumenServidor);

        try {
            const respuesta = await consultarApiReportes(parametros);

            if (numeroSolicitud !== solicitudEstatusResumenActual ||
                claveSolicitud !== claveEstatusResumenServidor) {
                return;
            }

            estatusResumenServidor = {
                filas: Array.isArray(respuesta.estatus) ? respuesta.estatus : []
            };
            renderEstatusResumen(estatusResumenServidor);
        } catch (error) {
            if (numeroSolicitud !== solicitudEstatusResumenActual) {
                return;
            }

            console.error('[Reportes de anormalidades] No se cargó el avance del flujo.', {
                filtros: parametros,
                error: error
            });

            claveEstatusResumenServidor = '';
            estatusResumenServidor = {
                mensaje: 'No fue posible cargar el avance del flujo.',
                error: true
            };
            renderEstatusResumen(estatusResumenServidor);
        }
    }

    /**
     * Carga en una sola petición los dos hallazgos agregados por SQL Server.
     * La API decide qué filtros corresponden a cada una de las consultas.
     */
    async function actualizarHallazgosDesdeServidor() {
        const parametros = obtenerParametrosReporte(
            OPERACIONES_REPORTES.resumenGeneral,
            'hallazgos'
        );
        const claveSolicitud = JSON.stringify(parametros);

        if (claveHallazgosServidor === claveSolicitud && hallazgosServidor) {
            renderHallazgos(hallazgosServidor);
            return;
        }

        const numeroSolicitud = ++solicitudHallazgosActual;
        claveHallazgosServidor = claveSolicitud;
        hallazgosServidor = { mensaje: 'Calculando hallazgos del periodo...' };
        renderHallazgos(hallazgosServidor);

        try {
            const respuesta = await consultarApiReportes(parametros);

            if (numeroSolicitud !== solicitudHallazgosActual ||
                claveSolicitud !== claveHallazgosServidor) {
                return;
            }

            hallazgosServidor = {
                datos: respuesta.hallazgos && typeof respuesta.hallazgos === 'object'
                    ? respuesta.hallazgos
                    : {}
            };
            renderHallazgos(hallazgosServidor);
        } catch (error) {
            if (numeroSolicitud !== solicitudHallazgosActual) {
                return;
            }

            console.error('[Reportes de anormalidades] No se cargaron los hallazgos.', {
                filtros: parametros,
                error: error
            });

            claveHallazgosServidor = '';
            hallazgosServidor = {
                mensaje: 'No fue posible cargar los hallazgos del periodo.',
                error: true
            };
            renderHallazgos(hallazgosServidor);
        }
    }

    /**
     * Carga la criticidad de todas las líneas del área seleccionada. El
     * filtro de línea se elimina deliberadamente antes de enviar la petición.
     */
    async function actualizarCriticidadLineasDesdeServidor() {
        const parametros = obtenerParametrosReporte(
            OPERACIONES_REPORTES.lineasAreas,
            'criticidad_por_linea'
        );

        delete parametros.codigoLinea;

        const claveSolicitud = JSON.stringify(parametros);

        if (claveCriticidadLineasServidor === claveSolicitud && criticidadLineasServidor) {
            renderCriticidadPorLinea(criticidadLineasServidor);
            return;
        }

        const numeroSolicitud = ++solicitudCriticidadLineasActual;
        claveCriticidadLineasServidor = claveSolicitud;
        criticidadLineasServidor = { mensaje: 'Cargando criticidad por línea...' };
        renderCriticidadPorLinea(criticidadLineasServidor);

        try {
            const respuesta = await consultarApiReportes(parametros);

            if (numeroSolicitud !== solicitudCriticidadLineasActual ||
                claveSolicitud !== claveCriticidadLineasServidor) {
                return;
            }

            criticidadLineasServidor = {
                filas: Array.isArray(respuesta.criticidad_por_linea)
                    ? respuesta.criticidad_por_linea
                    : []
            };
            renderCriticidadPorLinea(criticidadLineasServidor);
        } catch (error) {
            if (numeroSolicitud !== solicitudCriticidadLineasActual) {
                return;
            }

            console.error('[Reportes de anormalidades] No se cargó la criticidad por línea.', {
                filtros: parametros,
                error: error
            });

            claveCriticidadLineasServidor = '';
            criticidadLineasServidor = {
                mensaje: 'No fue posible cargar la criticidad por línea.',
                error: true
            };
            renderCriticidadPorLinea(criticidadLineasServidor);
        }
    }

    /**
     * Carga el total de todas las líneas del área elegida. El filtro de línea
     * se omite porque esta gráfica compara el conjunto completo de líneas.
     */
    async function actualizarTotalesLineasDesdeServidor() {
        const parametros = obtenerParametrosReporte(
            OPERACIONES_REPORTES.lineasAreas,
            'total_por_linea'
        );

        delete parametros.codigoLinea;

        const claveSolicitud = JSON.stringify(parametros);

        if (claveTotalesLineasServidor === claveSolicitud && totalesLineasServidor) {
            renderTotalesPorLinea(totalesLineasServidor);
            return;
        }

        const numeroSolicitud = ++solicitudTotalesLineasActual;
        claveTotalesLineasServidor = claveSolicitud;
        totalesLineasServidor = { mensaje: 'Cargando totales por línea...' };
        renderTotalesPorLinea(totalesLineasServidor);

        try {
            const respuesta = await consultarApiReportes(parametros);

            if (numeroSolicitud !== solicitudTotalesLineasActual ||
                claveSolicitud !== claveTotalesLineasServidor) {
                return;
            }

            totalesLineasServidor = {
                filas: Array.isArray(respuesta.total_por_linea)
                    ? respuesta.total_por_linea
                    : []
            };
            renderTotalesPorLinea(totalesLineasServidor);
        } catch (error) {
            if (numeroSolicitud !== solicitudTotalesLineasActual) {
                return;
            }

            console.error('[Reportes de anormalidades] No se cargaron los totales por línea.', {
                filtros: parametros,
                error: error
            });

            claveTotalesLineasServidor = '';
            totalesLineasServidor = {
                mensaje: 'No fue posible cargar los totales por línea.',
                error: true
            };
            renderTotalesPorLinea(totalesLineasServidor);
        }
    }

    /**
     * Solicita la tendencia con todos los filtros. La API decide el nivel:
     * áreas, líneas del área elegida o únicamente la línea seleccionada.
     */
    async function actualizarTendenciaDesdeServidor() {
        const parametros = obtenerParametrosReporte(
            OPERACIONES_REPORTES.lineasAreas,
            'tendencia_por_area'
        );
        const claveSolicitud = JSON.stringify(parametros);

        if (claveTendenciaServidor === claveSolicitud && tendenciaServidor) {
            renderTendenciaDesdeServidor(tendenciaServidor);
            return;
        }

        const numeroSolicitud = ++solicitudTendenciaActual;
        claveTendenciaServidor = claveSolicitud;
        tendenciaServidor = { mensaje: 'Cargando tendencia mensual...' };
        renderTendenciaDesdeServidor(tendenciaServidor);

        try {
            const respuesta = await consultarApiReportes(parametros);

            if (numeroSolicitud !== solicitudTendenciaActual ||
                claveSolicitud !== claveTendenciaServidor) {
                return;
            }

            const tendencia = respuesta.tendencia_por_area || {};
            const datosGrafica = prepararTendenciaMensual(
                Array.isArray(tendencia.filas) ? tendencia.filas : [],
                tendencia.periodo,
                tendencia.nivel === 'area' ? 'area' : 'linea'
            );

            tendenciaServidor = {
                etiquetas: datosGrafica.etiquetas,
                series: datosGrafica.series
            };
            renderTendenciaDesdeServidor(tendenciaServidor);
        } catch (error) {
            if (numeroSolicitud !== solicitudTendenciaActual) {
                return;
            }

            console.error('[Reportes de anormalidades] No se cargó la tendencia mensual.', {
                filtros: parametros,
                error: error
            });

            claveTendenciaServidor = '';
            tendenciaServidor = {
                mensaje: 'No fue posible cargar la tendencia mensual.',
                error: true
            };
            renderTendenciaDesdeServidor(tendenciaServidor);
        }
    }

    /**
     * Carga exclusivamente la distribución 5M. SQL Server ya devuelve los
     * totales y porcentajes; el cliente sólo construye las barras.
     */
    async function actualizarClasificacion5mDesdeServidor() {
        const parametros = obtenerParametrosReporte(
            OPERACIONES_REPORTES.lineasAreas,
            'clasificacion_5m'
        );
        const claveSolicitud = JSON.stringify(parametros);

        if (claveClasificacion5mServidor === claveSolicitud && clasificacion5mServidor) {
            renderClasificacion5m(clasificacion5mServidor);
            return;
        }

        const numeroSolicitud = ++solicitudClasificacion5mActual;
        claveClasificacion5mServidor = claveSolicitud;
        clasificacion5mServidor = { mensaje: 'Cargando clasificación 5M...' };
        renderClasificacion5m(clasificacion5mServidor);

        try {
            const respuesta = await consultarApiReportes(parametros);

            if (numeroSolicitud !== solicitudClasificacion5mActual ||
                claveSolicitud !== claveClasificacion5mServidor) {
                return;
            }

            clasificacion5mServidor = {
                filas: Array.isArray(respuesta.clasificacion_5m)
                    ? respuesta.clasificacion_5m
                    : []
            };
            renderClasificacion5m(clasificacion5mServidor);
        } catch (error) {
            if (numeroSolicitud !== solicitudClasificacion5mActual) {
                return;
            }

            console.error('[Reportes de anormalidades] No se cargó la clasificación 5M.', {
                filtros: parametros,
                error: error
            });

            claveClasificacion5mServidor = '';
            clasificacion5mServidor = {
                mensaje: 'No fue posible cargar la clasificación 5M.',
                error: true
            };
            renderClasificacion5m(clasificacion5mServidor);
        }
    }

    /**
     * Solicita solamente los cinco tipos críticos con mayor frecuencia.
     * La agrupación y el límite del TOP se resuelven en SQL Server.
     */
    async function actualizarTopCriticasDesdeServidor() {
        const parametros = obtenerParametrosReporte(
            OPERACIONES_REPORTES.criticasTipos,
            'top_criticas'
        );
        const claveSolicitud = JSON.stringify(parametros);

        if (claveTopCriticasServidor === claveSolicitud && topCriticasServidor) {
            renderTopCriticas(topCriticasServidor);
            return;
        }

        const numeroSolicitud = ++solicitudTopCriticasActual;
        claveTopCriticasServidor = claveSolicitud;
        topCriticasServidor = { mensaje: 'Cargando anormalidades críticas...' };
        renderTopCriticas(topCriticasServidor);

        try {
            const respuesta = await consultarApiReportes(parametros);

            if (numeroSolicitud !== solicitudTopCriticasActual ||
                claveSolicitud !== claveTopCriticasServidor) {
                return;
            }

            topCriticasServidor = {
                filas: Array.isArray(respuesta.top_criticas)
                    ? respuesta.top_criticas
                    : []
            };
            renderTopCriticas(topCriticasServidor);
        } catch (error) {
            if (numeroSolicitud !== solicitudTopCriticasActual) {
                return;
            }

            console.error('[Reportes de anormalidades] No se cargó el TOP de críticas.', {
                filtros: parametros,
                error: error
            });

            claveTopCriticasServidor = '';
            topCriticasServidor = {
                mensaje: 'No fue posible cargar las anormalidades críticas.',
                error: true
            };
            renderTopCriticas(topCriticasServidor);
        }
    }

    /**
     * Solicita únicamente los cinco tipos no críticos más frecuentes.
     * La API aplica los filtros y devuelve el resultado ya agrupado.
     */
    async function actualizarTopNoCriticasDesdeServidor() {
        const parametros = obtenerParametrosReporte(
            OPERACIONES_REPORTES.criticasTipos,
            'top_no_criticas'
        );
        const claveSolicitud = JSON.stringify(parametros);

        if (claveTopNoCriticasServidor === claveSolicitud && topNoCriticasServidor) {
            renderTopNoCriticas(topNoCriticasServidor);
            return;
        }

        const numeroSolicitud = ++solicitudTopNoCriticasActual;
        claveTopNoCriticasServidor = claveSolicitud;
        topNoCriticasServidor = { mensaje: 'Cargando anormalidades no críticas...' };
        renderTopNoCriticas(topNoCriticasServidor);

        try {
            const respuesta = await consultarApiReportes(parametros);

            if (numeroSolicitud !== solicitudTopNoCriticasActual ||
                claveSolicitud !== claveTopNoCriticasServidor) {
                return;
            }

            topNoCriticasServidor = {
                filas: Array.isArray(respuesta.top_no_criticas)
                    ? respuesta.top_no_criticas
                    : []
            };
            renderTopNoCriticas(topNoCriticasServidor);
        } catch (error) {
            if (numeroSolicitud !== solicitudTopNoCriticasActual) {
                return;
            }

            console.error('[Reportes de anormalidades] No se cargó el TOP de no críticas.', {
                filtros: parametros,
                error: error
            });

            claveTopNoCriticasServidor = '';
            topNoCriticasServidor = {
                mensaje: 'No fue posible cargar las anormalidades no críticas.',
                error: true
            };
            renderTopNoCriticas(topNoCriticasServidor);
        }
    }

    /**
     * Carga la distribución por nivel de respuesta ya calculada en SQL Server.
     */
    async function actualizarNivelRespuestaDesdeServidor() {
        const parametros = obtenerParametrosReporte(
            OPERACIONES_REPORTES.criticasTipos,
            'nivel_respuesta'
        );
        const claveSolicitud = JSON.stringify(parametros);

        if (claveNivelRespuestaServidor === claveSolicitud && nivelRespuestaServidor) {
            renderNivelRespuesta(nivelRespuestaServidor);
            return;
        }

        const numeroSolicitud = ++solicitudNivelRespuestaActual;
        claveNivelRespuestaServidor = claveSolicitud;
        nivelRespuestaServidor = { mensaje: 'Cargando niveles de respuesta...' };
        renderNivelRespuesta(nivelRespuestaServidor);

        try {
            const respuesta = await consultarApiReportes(parametros);

            if (numeroSolicitud !== solicitudNivelRespuestaActual ||
                claveSolicitud !== claveNivelRespuestaServidor) {
                return;
            }

            nivelRespuestaServidor = {
                filas: Array.isArray(respuesta.nivel_respuesta)
                    ? respuesta.nivel_respuesta
                    : []
            };
            renderNivelRespuesta(nivelRespuestaServidor);
        } catch (error) {
            if (numeroSolicitud !== solicitudNivelRespuestaActual) {
                return;
            }

            console.error('[Reportes de anormalidades] No se cargó el nivel de respuesta.', {
                filtros: parametros,
                error: error
            });

            claveNivelRespuestaServidor = '';
            nivelRespuestaServidor = {
                mensaje: 'No fue posible cargar los niveles de respuesta.',
                error: true
            };
            renderNivelRespuesta(nivelRespuestaServidor);
        }
    }

    /**
     * Solicita solamente el TOP agrupado por tipo de anormalidad. La API
     * considera únicamente anormalidades que tienen un registro general de
     * reincidencia y aplica los filtros comunes antes de agrupar.
     */
    async function actualizarTopReincidentesDesdeServidor() {
        const parametros = obtenerParametrosReporte(
            OPERACIONES_REPORTES.reincidencias,
            'top_reincidentes'
        );
        const claveSolicitud = JSON.stringify(parametros);

        if (claveTopReincidentesServidor === claveSolicitud && topReincidentesServidor) {
            renderTopReincidentes(topReincidentesServidor);
            return;
        }

        const numeroSolicitud = ++solicitudTopReincidentesActual;
        claveTopReincidentesServidor = claveSolicitud;
        topReincidentesServidor = { mensaje: 'Cargando anormalidades reincidentes...' };
        renderTopReincidentes(topReincidentesServidor);

        try {
            const respuesta = await consultarApiReportes(parametros);

            if (numeroSolicitud !== solicitudTopReincidentesActual ||
                claveSolicitud !== claveTopReincidentesServidor) {
                return;
            }

            topReincidentesServidor = {
                filas: Array.isArray(respuesta.top_reincidentes)
                    ? respuesta.top_reincidentes
                    : []
            };
            renderTopReincidentes(topReincidentesServidor);
        } catch (error) {
            if (numeroSolicitud !== solicitudTopReincidentesActual) {
                return;
            }

            console.error('[Reportes de anormalidades] No se cargó el TOP de reincidencias.', {
                filtros: parametros,
                error: error
            });

            claveTopReincidentesServidor = '';
            topReincidentesServidor = {
                mensaje: 'No fue posible cargar las anormalidades reincidentes.',
                error: true
            };
            renderTopReincidentes(topReincidentesServidor);
        }
    }

    /**
     * Obtiene el TOP de estaciones ya agrupado en SQL Server. Cada
     * anormalidad con registro general de reincidencia se cuenta una sola vez.
     */
    async function actualizarTopEstacionesReincidenciaDesdeServidor() {
        const parametros = obtenerParametrosReporte(
            OPERACIONES_REPORTES.reincidencias,
            'top_estaciones'
        );
        const claveSolicitud = JSON.stringify(parametros);

        if (claveTopEstacionesReincidenciaServidor === claveSolicitud &&
            topEstacionesReincidenciaServidor) {
            renderTopEstacionesReincidencia(topEstacionesReincidenciaServidor);
            return;
        }

        const numeroSolicitud = ++solicitudTopEstacionesReincidenciaActual;
        claveTopEstacionesReincidenciaServidor = claveSolicitud;
        topEstacionesReincidenciaServidor = { mensaje: 'Cargando estaciones...' };
        renderTopEstacionesReincidencia(topEstacionesReincidenciaServidor);

        try {
            const respuesta = await consultarApiReportes(parametros);

            if (numeroSolicitud !== solicitudTopEstacionesReincidenciaActual ||
                claveSolicitud !== claveTopEstacionesReincidenciaServidor) {
                return;
            }

            topEstacionesReincidenciaServidor = {
                filas: Array.isArray(respuesta.top_estaciones)
                    ? respuesta.top_estaciones
                    : []
            };
            renderTopEstacionesReincidencia(topEstacionesReincidenciaServidor);
        } catch (error) {
            if (numeroSolicitud !== solicitudTopEstacionesReincidenciaActual) {
                return;
            }

            console.error('[Reportes de anormalidades] No se cargó el TOP de estaciones.', {
                filtros: parametros,
                error: error
            });

            claveTopEstacionesReincidenciaServidor = '';
            topEstacionesReincidenciaServidor = {
                mensaje: 'No fue posible cargar las estaciones con reincidencias.',
                error: true
            };
            renderTopEstacionesReincidencia(topEstacionesReincidenciaServidor);
        }
    }

    /* El catálogo se carga una sola vez cuando se visita la pestaña. */
    async function cargarCatalogoTiposFrecuentes() {
        if (catalogoTiposFrecuentesCargado) {
            return;
        }

        elementos.tipoAnormalidadFrecuente.disabled = true;
        elementos.tipoAnormalidadFrecuente.setAttribute('aria-busy', 'true');

        try {
            const respuesta = await consultarApiReportes({
                opcion: OPERACIONES_REPORTES.reincidencias,
                seccion: 'catalogo_tipos'
            });
            const tipos = Array.isArray(respuesta.catalogo_tipos)
                ? respuesta.catalogo_tipos
                : [];

            elementos.tipoAnormalidadFrecuente.innerHTML =
                '<option value="todos">Todos los tipos</option>' +
                tipos.map(function (tipo) {
                    return '<option value="' + escaparHtml(tipo.id_tipo_anormalidad) + '">' +
                        escaparHtml(tipo.nombre_tipo_anormalidad) + '</option>';
                }).join('');
            catalogoTiposFrecuentesCargado = true;
        } catch (error) {
            console.error(
                '[Reportes de anormalidades] No se cargó el catálogo de tipos.',
                error
            );
            elementos.tipoAnormalidadFrecuente.innerHTML =
                '<option value="todos">Todos los tipos</option>';
        } finally {
            elementos.tipoAnormalidadFrecuente.disabled = false;
            elementos.tipoAnormalidadFrecuente.removeAttribute('aria-busy');
        }
    }

    /* Solicita exclusivamente los totales agrupados que muestra la tabla. */
    async function actualizarFrecuentesDesdeServidor() {
        const numeroSolicitud = ++solicitudFrecuentesActual;
        const parametros = obtenerParametrosReporte(
            OPERACIONES_REPORTES.reincidencias,
            'frecuentes'
        );

        if (elementos.tipoAnormalidadFrecuente.value !== 'todos') {
            parametros.idTipoAnormalidad = elementos.tipoAnormalidadFrecuente.value;
        }

        try {
            const respuesta = await consultarApiReportes(parametros);

            if (numeroSolicitud !== solicitudFrecuentesActual) {
                return;
            }

            renderTablaFrecuentes(
                Array.isArray(respuesta.frecuentes) ? respuesta.frecuentes : []
            );
        } catch (error) {
            if (numeroSolicitud !== solicitudFrecuentesActual) {
                return;
            }

            console.error(
                '[Reportes de anormalidades] No se cargó la tabla de tipos frecuentes.',
                { filtros: parametros, error: error }
            );
            renderTablaFrecuentes([]);
        }
    }

    /**
     * Solicita sólo el total agrupado por mes del primer subtab del histórico.
     * El consecutivo descarta respuestas antiguas si los filtros cambian rápido.
     */
    async function actualizarHistoricoTotalDesdeServidor() {
        const numeroSolicitud = ++solicitudHistoricoActual;
        const parametros = obtenerParametrosReporte(
            OPERACIONES_REPORTES.resumenGeneral,
            'historico_total'
        );

        historicoTotalServidor = { mensaje: 'Cargando histórico mensual...' };
        elementos.historicoTotal.textContent = 'Cargando...';
        renderHistorico(seriesHistorico);

        try {
            const respuesta = await consultarApiReportes(parametros);

            if (numeroSolicitud !== solicitudHistoricoActual) {
                return;
            }

            const filas = Array.isArray(respuesta.historico_total)
                ? respuesta.historico_total
                : [];

            historicoTotalServidor = prepararHistoricoTotal(filas, respuesta.periodo);
            const totalPeriodo = historicoTotalServidor.valores.reduce(function (suma, valor) {
                return suma + valor;
            }, 0);

            elementos.historicoTotal.textContent = totalPeriodo.toLocaleString('es-MX') +
                ' registros';
            renderHistorico(seriesHistorico);
        } catch (error) {
            if (numeroSolicitud !== solicitudHistoricoActual) {
                return;
            }

            console.error('[Reportes de anormalidades] No se cargó el histórico mensual.', {
                filtros: parametros,
                error: error
            });

            historicoTotalServidor = {
                mensaje: 'No fue posible cargar el histórico mensual.'
            };
            elementos.historicoTotal.textContent = 'Sin información';
            renderHistorico(seriesHistorico);
        }
    }

    /**
     * Solicita únicamente los totales que SQL Server ya agrupó por mes y línea.
     */
    async function actualizarHistoricoLineasDesdeServidor() {
        const numeroSolicitud = ++solicitudHistoricoLineasActual;
        const parametros = obtenerParametrosReporte(
            OPERACIONES_REPORTES.resumenGeneral,
            'historico_lineas'
        );

        historicoLineasServidor = { mensaje: 'Cargando detalle por línea...' };
        elementos.historicoTotal.textContent = 'Cargando...';
        renderHistorico(seriesHistorico);

        try {
            const respuesta = await consultarApiReportes(parametros);

            if (numeroSolicitud !== solicitudHistoricoLineasActual) {
                return;
            }

            const filas = Array.isArray(respuesta.historico_lineas)
                ? respuesta.historico_lineas
                : [];

            historicoLineasServidor = prepararHistoricoLineas(filas, respuesta.periodo);
            const totalPeriodo = historicoLineasServidor.series.reduce(function (suma, serie) {
                return suma + serie.valores.reduce(function (subtotal, valor) {
                    return subtotal + valor;
                }, 0);
            }, 0);

            elementos.historicoTotal.textContent = totalPeriodo.toLocaleString('es-MX') +
                ' registros';
            renderHistorico(seriesHistorico);
        } catch (error) {
            if (numeroSolicitud !== solicitudHistoricoLineasActual) {
                return;
            }

            console.error('[Reportes de anormalidades] No se cargó el histórico por línea.', {
                filtros: parametros,
                error: error
            });

            historicoLineasServidor = {
                mensaje: 'No fue posible cargar el detalle mensual por línea.'
            };
            elementos.historicoTotal.textContent = 'Sin información';
            renderHistorico(seriesHistorico);
        }
    }

    /* Solicita únicamente el subtab activo y reutiliza su resultado vigente. */
    function cargarHistoricoSeleccionado() {
        if (modoHistorico === 'detalle') {
            if (historicoLineasServidor) {
                renderHistorico(seriesHistorico);
                return;
            }

            actualizarHistoricoLineasDesdeServidor();
            return;
        }

        if (historicoTotalServidor) {
            renderHistorico(seriesHistorico);
            return;
        }

        actualizarHistoricoTotalDesdeServidor();
    }


    /**
     * Carga las áreas y los turnos una sola vez al abrir esta sección de la SPA.
     */
    async function cargarCatalogosFiltros() {
        elementos.area.disabled = true;
        elementos.turno.disabled = true;
        elementos.area.setAttribute('aria-busy', 'true');
        elementos.turno.setAttribute('aria-busy', 'true');

        try {
            const catalogos = await consultarApiReportes({
                opcion: OPERACIONES_REPORTES.catalogos
            });
            const areas = Array.isArray(catalogos.areas) ? catalogos.areas : [];
            const turnos = Array.isArray(catalogos.turnos) ? catalogos.turnos : [];

            elementos.area.innerHTML = '<option value="todas">Todas las áreas</option>' +
                areas.map(function (area) {
                    return '<option value="' + escaparHtml(area.id_area) + '" data-nombre="' +
                        escaparHtml(area.nombre_area) + '">' + escaparHtml(area.nombre_area) +
                        '</option>';
                }).join('');

            elementos.turno.innerHTML = '<option value="todos">Todos los turnos</option>' +
                turnos.map(function (turno) {
                    return '<option value="' + escaparHtml(turno.id_turno) + '" data-clave="' +
                        escaparHtml(turno.clave) + '">' + escaparHtml(turno.nombre_turno) +
                        '</option>';
                }).join('');
        } catch (error) {
            console.error('[Reportes de anormalidades] No se cargaron los catálogos.', error);
            elementos.area.innerHTML = '<option value="todas">Todas las áreas</option>';
            elementos.turno.innerHTML = '<option value="todos">Todos los turnos</option>';
        } finally {
            elementos.area.disabled = false;
            elementos.turno.disabled = false;
            elementos.area.removeAttribute('aria-busy');
            elementos.turno.removeAttribute('aria-busy');
            /* Los filtros inician en "todas"; no es necesario repetir consultas. */
            actualizarAlcance();
        }
    }

    /**
     * Reinicia y carga las líneas correspondientes al área elegida. Una
     * respuesta anterior se ignora si el usuario cambia rápidamente de área.
     */
    async function cargarLineasPorArea(idArea) {
        const numeroSolicitud = ++solicitudLineasActual;

        elementos.linea.innerHTML = '<option value="todas">Todas las líneas</option>';
        elementos.linea.disabled = true;

        if (idArea === 'todas') {
            elementos.linea.removeAttribute('aria-busy');
            return;
        }

        elementos.linea.setAttribute('aria-busy', 'true');

        try {
            const respuesta = await consultarApiReportes({
                opcion: OPERACIONES_REPORTES.lineasPorArea,
                idArea: idArea
            });

            if (numeroSolicitud !== solicitudLineasActual) {
                return;
            }

            const lineas = Array.isArray(respuesta) ? respuesta : [];
            elementos.linea.innerHTML = '<option value="todas">Todas las líneas</option>' +
                lineas.map(function (linea) {
                    return '<option value="' + escaparHtml(linea.codigo_linea) + '">' +
                        escaparHtml(linea.nombre_linea) + '</option>';
                }).join('');
            elementos.linea.disabled = lineas.length === 0;
        } catch (error) {
            if (numeroSolicitud === solicitudLineasActual) {
                console.error('[Reportes de anormalidades] No se cargaron las líneas.', error);
            }
        } finally {
            if (numeroSolicitud === solicitudLineasActual) {
                elementos.linea.removeAttribute('aria-busy');
            }
        }
    }

    function renderTodo() {
        actualizarAlcance();
        invalidarContenidoPestanas();
        cargarContenidoPestana(obtenerPestanaActiva(), true);
    }

    /* ============================= Eventos ============================= */

    /*
     * Alterna la barra sticky sin recrear el formulario. De esta manera se
     * conservan los valores seleccionados y todos sus eventos de filtrado.
     */
    function mostrarFiltrosPrincipales() {
        window.clearTimeout(temporizadorFiltros);
        elementos.abrirFiltros.hidden = true;
        elementos.formulario.hidden = false;
        elementos.formulario.classList.add('is-opening');
        elementos.formulario.setAttribute('aria-hidden', 'false');
        elementos.cerrarFiltros.setAttribute('aria-expanded', 'true');
        elementos.abrirFiltros.setAttribute('aria-expanded', 'true');

        window.requestAnimationFrame(function () {
            window.requestAnimationFrame(function () {
                elementos.formulario.classList.remove('is-opening');
                elementos.cerrarFiltros.focus();
            });
        });
    }

    function ocultarFiltrosPrincipales() {
        window.clearTimeout(temporizadorFiltros);
        elementos.formulario.classList.add('is-closing');
        elementos.formulario.setAttribute('aria-hidden', 'true');
        elementos.cerrarFiltros.setAttribute('aria-expanded', 'false');
        elementos.abrirFiltros.setAttribute('aria-expanded', 'false');

        temporizadorFiltros = window.setTimeout(function () {
            if (!vista.isConnected) {
                return;
            }

            elementos.formulario.hidden = true;
            elementos.formulario.classList.remove('is-closing');
            elementos.abrirFiltros.hidden = false;
            elementos.abrirFiltros.focus();
        }, 180);
    }

    elementos.cerrarFiltros.addEventListener('click', ocultarFiltrosPrincipales);
    elementos.abrirFiltros.addEventListener('click', mostrarFiltrosPrincipales);

    elementos.pestanas.forEach(function (boton) {
        boton.addEventListener('click', function () {
            activarPestana(boton);
        });
    });

    /* Sub-pestañas del histórico: total mensual o desglose por línea. */
    elementos.historicoSubpestanas.forEach(function (boton) {
        boton.addEventListener('click', function () {
            modoHistorico = boton.dataset.raHistorico;

            elementos.historicoSubpestanas.forEach(function (item) {
                const activa = item === boton;
                item.classList.toggle('is-active', activa);
                item.setAttribute('aria-selected', activa ? 'true' : 'false');
            });

            elementos.historicoDescripcion.textContent = modoHistorico === 'detalle'
                ? 'Cantidad de anormalidades registradas por cada línea en cada mes.'
                : 'Total mensual según el periodo y los filtros seleccionados.';

            cargarHistoricoSeleccionado();
        });
    });

    /* El área seleccionada solicita al servidor únicamente sus líneas. */
    elementos.area.addEventListener('change', function () {
        cargarLineasPorArea(elementos.area.value);
        renderTodo();
        actualizarKpisDesdeServidor();
    });

    elementos.linea.addEventListener('change', function () {
        renderTodo();
        actualizarKpisDesdeServidor();
    });

    elementos.turno.addEventListener('change', function () {
        renderTodo();
        actualizarKpisDesdeServidor();
    });

    /**
     * Valida el periodo y actualiza la pestaña activa y sus tarjetas KPI.
     * Se comparte entre ambos campos de fecha para mantener una sola lógica.
     */
    function aplicarFiltrosDeFecha() {
        const rangoInvalido = elementos.fechaInicio.value && elementos.fechaFin.value &&
            elementos.fechaInicio.value > elementos.fechaFin.value;

        elementos.fechaInicio.classList.toggle('is-invalid', rangoInvalido);
        elementos.fechaFin.classList.toggle('is-invalid', rangoInvalido);

        if (rangoInvalido) {
            elementos.fechaInicio.focus();
            return;
        }

        renderTodo();
        actualizarKpisDesdeServidor();
    }

    elementos.fechaInicio.addEventListener('change', aplicarFiltrosDeFecha);
    elementos.fechaFin.addEventListener('change', aplicarFiltrosDeFecha);

    /*
     * El histórico se redibuja al cambiar el tamaño de la ventana para que
     * siempre use todo el ancho disponible. El retraso evita recalcular en
     * cada pixel mientras el usuario ajusta la ventana.
     */
    function alRedimensionar() {
        window.clearTimeout(temporizadorResize);

        temporizadorResize = window.setTimeout(function () {
            /* Al cambiar de sección la vista se reemplaza: se libera el evento. */
            if (!vista.isConnected) {
                window.removeEventListener('resize', alRedimensionar);
                return;
            }

            if (!elementos.paneles.resumen.hidden) {
                renderHistorico(seriesHistorico);
            }

            if (!elementos.paneles.lineas.hidden &&
                tendenciaServidor && !tendenciaServidor.mensaje) {
                renderTendenciaDesdeServidor(tendenciaServidor);
            }
        }, 200);
    }

    window.addEventListener('resize', alRedimensionar);

    /* Evita que Enter recargue la página aunque el formulario ya no tenga botón. */
    elementos.formulario.addEventListener('submit', function (evento) {
        evento.preventDefault();
        aplicarFiltrosDeFecha();
    });

    /* El filtro interno sólo actualiza el resumen de tipos frecuentes. */
    elementos.tipoAnormalidadFrecuente.addEventListener('change', function () {
        actualizarFrecuentesDesdeServidor();
    });

    /* Delegación necesaria porque DataTables reemplaza las filas al paginar. */
    elementos.tablaFrecuentes.addEventListener('click', function (evento) {
        const boton = evento.target.closest('.ra-action-frecuente-info');

        if (boton) {
            abrirDetalleContramedidas(boton);
        }
    });

    elementos.modalContramedidasFrecuentes.addEventListener('shown.bs.modal', function () {
        if (tablaDetalleContramedidasDT) {
            tablaDetalleContramedidasDT.columns.adjust();
        }
    });

    /* -------- Eventos de la sección de reportes -------- */

    /*
     * "Todos" funciona como selector maestro: marca o desmarca el catálogo
     * completo. También refleja cuando todos se seleccionan individualmente.
     */
    elementos.reporteEstatus.addEventListener('change', function (evento) {
        const check = evento.target;

        if (!check.matches('input[type="checkbox"]')) {
            return;
        }

        const checksEstatus = Array.prototype.slice.call(
            elementos.reporteEstatusOpciones.querySelectorAll('input[data-ra-estatus]')
        );

        if (check === elementos.reporteEstatusTodos) {
            checksEstatus.forEach(function (item) {
                item.checked = elementos.reporteEstatusTodos.checked;
            });
        } else {
            const totalSeleccionados = checksEstatus.filter(function (item) {
                return item.checked;
            }).length;

            elementos.reporteEstatusTodos.checked =
                checksEstatus.length > 0 && totalSeleccionados === checksEstatus.length;
        }

        recargarTablaReporte();
    });

    elementos.reporteCriticidad.addEventListener('change', function () {
        recargarTablaReporte();
    });

    /* El formulario no debe provocar una navegación si recibe un submit implícito. */
    elementos.reporteFiltros.addEventListener('submit', function (evento) {
        evento.preventDefault();
        recargarTablaReporte();
    });

    elementos.reporteLimpiar.addEventListener('click', function () {
        elementos.reporteFiltros.reset();
        recargarTablaReporte();
    });

    /* DataTables sustituye sus filas al paginar; por eso se delega el evento. */
    elementos.tablaReporte.addEventListener('click', function (evento) {
        const boton = evento.target.closest('.ra-action-reporte-info');

        if (boton) {
            abrirReporteAnormalidad(boton.dataset.raIdAnormalidad);
        }
    });

    elementos.modalReporte.addEventListener('hidden.bs.modal', function () {
        /* Ignora una respuesta pendiente si el usuario cerró el reporte. */
        solicitudDetalleReporteActual++;
    });

    /* ========================== Inicialización ========================== */

    /* Las fechas deben iniciar vacías cada vez que se abre esta vista. */
    elementos.fechaInicio.value = '';
    elementos.fechaFin.value = '';
    renderKpis({
        total_anormalidades: 0,
        anormalidades_criticas: 0,
        porcentaje_criticas: 0,
        anormalidades_finalizadas: 0,
        porcentaje_finalizadas: 0,
        anormalidades_en_proceso: 0
    }, 'Cargando información...');
    renderTodo();
    actualizarKpisDesdeServidor();
    cargarCatalogosFiltros();
})();
