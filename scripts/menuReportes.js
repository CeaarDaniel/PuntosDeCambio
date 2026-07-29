(function () {
    const reports = document.getElementById('reports');

    if (!reports) {
        return;
    }

    const reportData = {
        attendance: {
            chartKicker: 'Comportamiento semanal',
            chartTitle: 'Tendencia de asistencia',
            chartDescription: 'Porcentaje promedio registrado durante el periodo.',
            labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
            values: [95, 97, 96, 98],
            max: 100,
            suffix: '%',
            distributionTitle: 'Estado de asistencia',
            distributionDescription: 'Composición de la dotación programada.',
            gauge: 96,
            gaugeLabel: 'Asistencia',
            legend: [
                { label: 'Presentes', value: '179', color: 'success' },
                { label: 'Ausencias justificadas', value: '3', color: 'warning' },
                { label: 'Ausencias', value: '4', color: 'danger' }
            ],
            kpis: [
                { icon: 'bi-person-check', label: 'Asistencia promedio', value: '96.4%', note: 'Meta: 95%' },
                { icon: 'bi-people', label: 'Personal programado', value: '186', note: '4 líneas incluidas' },
                { icon: 'bi-person-x', label: 'Ausencias', value: '7', note: '3 justificadas' },
                { icon: 'bi-graph-up-arrow', label: 'Variación', value: '+1.8%', note: 'Contra el periodo anterior' }
            ],
            tableTitle: 'Resumen de asistencia por línea',
            tableDescription: 'Vista previa de presencia, ausencias y cumplimiento.',
            headers: ['Línea', 'Programados', 'Presentes', 'Ausencias', 'Asistencia', 'Estado'],
            rows: [
                ['A01', '48', '47', '1', '98%', 'En meta', 'success'],
                ['A02', '44', '42', '2', '96%', 'En meta', 'success'],
                ['B01', '51', '48', '3', '94%', 'Atención', 'danger'],
                ['B02', '43', '42', '1', '97%', 'En meta', 'success']
            ],
            insights: [
                ['bi-graph-up-arrow', 'Mejora sostenida', 'La asistencia aumentó 1.8% frente al periodo anterior.'],
                ['bi-exclamation-circle', 'Línea B01 requiere seguimiento', 'Su asistencia se encuentra un punto debajo de la meta.'],
                ['bi-calendar-check', 'Mejor día del periodo', 'El jueves alcanzó un promedio simulado de 98%.']
            ]
        },
        changes: {
            chartKicker: 'Registros por semana',
            chartTitle: 'Actividad de puntos de cambio',
            chartDescription: 'Cantidad de movimientos registrados en el periodo.',
            labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
            values: [18, 24, 21, 28],
            max: 30,
            suffix: '',
            distributionTitle: 'Estado de los registros',
            distributionDescription: 'Proporción de puntos de cambio cerrados.',
            gauge: 88,
            gaugeLabel: 'Cerrados',
            legend: [
                { label: 'Cerrados', value: '80', color: 'success' },
                { label: 'En seguimiento', value: '7', color: 'warning' },
                { label: 'Activos', value: '4', color: 'primary' }
            ],
            kpis: [
                { icon: 'bi-arrow-left-right', label: 'Cambios registrados', value: '91', note: 'En el periodo seleccionado' },
                { icon: 'bi-check2-circle', label: 'Cambios cerrados', value: '80', note: '88% del total' },
                { icon: 'bi-clock-history', label: 'Tiempo promedio', value: '2.4 h', note: 'Desde apertura a cierre' },
                { icon: 'bi-clipboard-check', label: 'Evaluaciones completas', value: '84', note: '92% de cumplimiento' }
            ],
            tableTitle: 'Resumen de puntos de cambio por línea',
            tableDescription: 'Vista previa de registros, cierres y tiempos promedio.',
            headers: ['Línea', 'Registrados', 'Cerrados', 'Activos', 'Promedio', 'Estado'],
            rows: [
                ['A01', '26', '25', '1', '1.8 h', 'Estable', 'success'],
                ['A02', '19', '18', '1', '2.1 h', 'Estable', 'success'],
                ['B01', '29', '23', '3', '3.2 h', 'Atención', 'danger'],
                ['B02', '17', '14', '1', '2.5 h', 'Seguimiento', 'warning']
            ],
            insights: [
                ['bi-lightning-charge', 'Mayor actividad en B01', 'La línea concentra 32% de los registros del periodo.'],
                ['bi-stopwatch', 'Oportunidad en tiempo de cierre', 'B01 supera por 48 minutos el promedio general.'],
                ['bi-check2-circle', 'Buen nivel de seguimiento', '88% de los puntos de cambio ya se encuentran cerrados.']
            ]
        },
        certifications: {
            chartKicker: 'Evolución mensual',
            chartTitle: 'Cobertura de certificación',
            chartDescription: 'Porcentaje de personal con certificaciones vigentes.',
            labels: ['Abr', 'May', 'Jun', 'Jul'],
            values: [78, 82, 85, 87],
            max: 100,
            suffix: '%',
            distributionTitle: 'Cobertura actual',
            distributionDescription: 'Estado general de las competencias requeridas.',
            gauge: 87,
            gaugeLabel: 'Cobertura',
            legend: [
                { label: 'Certificados', value: '162', color: 'primary' },
                { label: 'En proceso', value: '14', color: 'cyan' },
                { label: 'Sin cobertura', value: '10', color: 'muted' }
            ],
            kpis: [
                { icon: 'bi-award', label: 'Cobertura vigente', value: '87%', note: '162 personas certificadas' },
                { icon: 'bi-patch-check', label: 'Certificaciones activas', value: '238', note: 'En todas las estaciones' },
                { icon: 'bi-person-gear', label: 'En capacitación', value: '14', note: '7.5% de la dotación' },
                { icon: 'bi-graph-up-arrow', label: 'Avance mensual', value: '+2.0%', note: 'Respecto a junio' }
            ],
            tableTitle: 'Cobertura de certificación por línea',
            tableDescription: 'Vista previa de personal certificado y brechas actuales.',
            headers: ['Línea', 'Personal', 'Certificados', 'En proceso', 'Cobertura', 'Estado'],
            rows: [
                ['A01', '48', '45', '2', '94%', 'Óptimo', 'success'],
                ['A02', '44', '39', '3', '89%', 'Estable', 'success'],
                ['B01', '51', '42', '5', '82%', 'Atención', 'danger'],
                ['B02', '43', '36', '4', '84%', 'Seguimiento', 'warning']
            ],
            insights: [
                ['bi-trophy', 'A01 lidera la cobertura', 'La línea alcanza 94% de personal certificado.'],
                ['bi-person-plus', '24 personas cubren la brecha', 'Son necesarias para llegar a cobertura total.'],
                ['bi-graph-up-arrow', 'Avance mensual positivo', 'La cobertura creció dos puntos durante julio.']
            ]
        },
        expirations: {
            chartKicker: 'Proyección mensual',
            chartTitle: 'Certificaciones próximas a vencer',
            chartDescription: 'Cantidad estimada de vencimientos por mes.',
            labels: ['Ago', 'Sep', 'Oct', 'Nov'],
            values: [8, 13, 7, 5],
            max: 15,
            suffix: '',
            distributionTitle: 'Nivel de riesgo',
            distributionDescription: 'Vencimientos clasificados por proximidad.',
            gauge: 76,
            gaugeLabel: 'Programadas',
            legend: [
                { label: 'Renovación programada', value: '25', color: 'success' },
                { label: 'Pendientes de programar', value: '6', color: 'warning' },
                { label: 'Vencidas', value: '2', color: 'danger' }
            ],
            kpis: [
                { icon: 'bi-calendar2-x', label: 'Próximos 30 días', value: '8', note: '4.9% de certificados' },
                { icon: 'bi-calendar-range', label: 'Próximos 90 días', value: '28', note: 'Requieren planeación' },
                { icon: 'bi-calendar-check', label: 'Ya programadas', value: '25', note: '76% del riesgo total' },
                { icon: 'bi-exclamation-triangle', label: 'Vencidas', value: '2', note: 'Atención inmediata' }
            ],
            tableTitle: 'Riesgo de vencimiento por línea',
            tableDescription: 'Vista previa de certificaciones por renovar.',
            headers: ['Línea', '30 días', '60 días', '90 días', 'Programadas', 'Riesgo'],
            rows: [
                ['A01', '1', '2', '2', '5', 'Bajo', 'success'],
                ['A02', '2', '3', '2', '6', 'Medio', 'warning'],
                ['B01', '4', '5', '4', '9', 'Alto', 'danger'],
                ['B02', '1', '2', '2', '5', 'Medio', 'warning']
            ],
            insights: [
                ['bi-exclamation-triangle', 'B01 concentra el mayor riesgo', 'Tiene cuatro vencimientos durante los próximos 30 días.'],
                ['bi-calendar-check', '76% ya tiene fecha', 'La mayoría de las renovaciones cuenta con programación.'],
                ['bi-clock-history', 'Dos registros requieren acción', 'Existen certificaciones simuladas que ya están vencidas.']
            ]
        }
    };

    const lineFactors = {
        all: 1,
        A01: 1.03,
        A02: 1,
        B01: 0.94,
        B02: 0.97
    };

    const elements = {
        form: reports.querySelector('#reportsFilters'),
        dateFrom: reports.querySelector('#reportDateFrom'),
        dateTo: reports.querySelector('#reportDateTo'),
        line: reports.querySelector('#reportLine'),
        shift: reports.querySelector('#reportShift'),
        typeButtons: Array.from(reports.querySelectorAll('[data-report-type]')),
        scope: reports.querySelector('#reportScopeBadge'),
        chartKicker: reports.querySelector('#reportChartKicker'),
        chartTitle: reports.querySelector('#reportChartTitle'),
        chartDescription: reports.querySelector('#reportChartDescription'),
        chart: reports.querySelector('#reportTrendChart'),
        chartColumns: reports.querySelector('#reportTrendChart .analytics-columns'),
        distributionTitle: reports.querySelector('#reportDistributionTitle'),
        distributionDescription: reports.querySelector('#reportDistributionDescription'),
        gauge: reports.querySelector('#reportDistributionGauge'),
        gaugeValue: reports.querySelector('#reportGaugeValue'),
        gaugeLabel: reports.querySelector('#reportGaugeLabel'),
        legend: reports.querySelector('#reportDistributionLegend'),
        tableTitle: reports.querySelector('#reportTableTitle'),
        tableDescription: reports.querySelector('#reportTableDescription'),
        tableHead: reports.querySelector('#reportTableHead'),
        tableBody: reports.querySelector('#reportTableBody'),
        insights: reports.querySelector('#reportInsights')
    };

    let activeType = 'attendance';

    function setKpi(kpi, index) {
        const number = index + 1;
        const icon = reports.querySelector(`#reportKpiIcon${number}`);

        icon.className = `bi ${kpi.icon}`;
        reports.querySelector(`#reportKpiLabel${number}`).textContent = kpi.label;
        reports.querySelector(`#reportKpiValue${number}`).textContent = kpi.value;
        reports.querySelector(`#reportKpiNote${number}`).textContent = kpi.note;
    }

    function lineLabel() {
        return elements.line.value === 'all'
            ? 'Todas las líneas'
            : `Línea ${elements.line.value}`;
    }

    function scopeLabel() {
        const shift = elements.shift.value === 'all'
            ? 'Todos los turnos'
            : `Turno ${elements.shift.value}`;

        return `${lineLabel()} · ${shift}`;
    }

    function chartValues(data) {
        const factor = lineFactors[elements.line.value] || 1;

        return data.values.map(function (value) {
            if (data.suffix === '%') {
                return Math.max(0, Math.min(100, Math.round(value * factor)));
            }

            return Math.max(0, Math.round(value * factor));
        });
    }

    function renderChart(data) {
        const values = chartValues(data);
        const gridLabels = elements.chart.querySelectorAll('.analytics-chart-grid span');
        const gridValues = [
            data.max,
            Math.round(data.max * 0.75),
            Math.round(data.max * 0.5),
            Math.round(data.max * 0.25),
            0
        ];

        gridLabels.forEach(function (label, index) {
            label.textContent = gridValues[index] + data.suffix;
        });

        elements.chartColumns.innerHTML = data.labels.map(function (label, index) {
            const value = values[index];
            const height = Math.max(5, Math.round(value / data.max * 100));

            return `
                <div class="analytics-column">
                    <div class="analytics-column-track" style="--value: ${height};">
                        <span class="analytics-column-value">${value}${data.suffix}</span>
                    </div>
                    <span class="analytics-column-label">${label}</span>
                </div>
            `;
        }).join('');

        elements.chart.setAttribute(
            'aria-label',
            `${data.chartTitle}: ${values.map(function (value) { return value + data.suffix; }).join(', ')}`
        );
    }

    function renderLegend(data) {
        elements.legend.innerHTML = data.legend.map(function (item) {
            return `
                <div>
                    <span class="analytics-legend-dot is-${item.color}"></span>
                    <span>${item.label}</span>
                    <strong>${item.value}</strong>
                </div>
            `;
        }).join('');
    }

    function renderTable(data) {
        const selectedLine = elements.line.value;
        const rows = selectedLine === 'all'
            ? data.rows
            : data.rows.filter(function (row) {
                return row[0] === selectedLine;
            });

        elements.tableHead.innerHTML = `
            <tr>${data.headers.map(function (header) {
                return `<th scope="col">${header}</th>`;
            }).join('')}</tr>
        `;

        elements.tableBody.innerHTML = rows.map(function (row) {
            const statusType = row[row.length - 1];
            const cells = row.slice(0, -2).map(function (cell, index) {
                const cssClass = index === 0 ? ' class="analytics-table-primary"' : '';
                const content = index === 0 ? `Línea ${cell}` : cell;
                return `<td${cssClass}>${content}</td>`;
            }).join('');

            return `
                <tr>
                    ${cells}
                    <td><span class="analytics-status is-${statusType}">${row[row.length - 2]}</span></td>
                </tr>
            `;
        }).join('');
    }

    function renderInsights(data) {
        elements.insights.innerHTML = data.insights.map(function (insight) {
            return `
                <div class="analytics-insight">
                    <span class="analytics-insight-icon">
                        <i class="bi ${insight[0]}" aria-hidden="true"></i>
                    </span>
                    <span class="analytics-insight-copy">
                        <strong>${insight[1]}</strong>
                        <small>${insight[2]}</small>
                    </span>
                </div>
            `;
        }).join('');
    }

    function renderReport() {
        const data = reportData[activeType];

        data.kpis.forEach(setKpi);
        elements.scope.textContent = scopeLabel();
        elements.chartKicker.textContent = data.chartKicker;
        elements.chartTitle.textContent = data.chartTitle;
        elements.chartDescription.textContent = data.chartDescription;
        elements.distributionTitle.textContent = data.distributionTitle;
        elements.distributionDescription.textContent = data.distributionDescription;
        elements.gauge.style.setProperty('--percentage', data.gauge);
        elements.gauge.setAttribute('aria-label', `${data.gauge} por ciento, ${data.gaugeLabel}`);
        elements.gaugeValue.textContent = `${data.gauge}%`;
        elements.gaugeLabel.textContent = data.gaugeLabel;
        elements.tableTitle.textContent = data.tableTitle;
        elements.tableDescription.textContent = data.tableDescription;

        renderChart(data);
        renderLegend(data);
        renderTable(data);
        renderInsights(data);
    }

    function setActiveType(button) {
        activeType = button.dataset.reportType;

        elements.typeButtons.forEach(function (item) {
            const isActive = item === button;
            item.classList.toggle('is-active', isActive);
            item.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        renderReport();
    }

    elements.typeButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            setActiveType(button);
        });
    });

    elements.line.addEventListener('change', renderReport);
    elements.shift.addEventListener('change', renderReport);

    elements.form.addEventListener('submit', function (event) {
        event.preventDefault();

        const isInvalidRange = elements.dateFrom.value && elements.dateTo.value
            && elements.dateFrom.value > elements.dateTo.value;

        elements.dateFrom.classList.toggle('is-invalid', isInvalidRange);
        elements.dateTo.classList.toggle('is-invalid', isInvalidRange);

        if (isInvalidRange) {
            elements.dateFrom.focus();
            return;
        }

        renderReport();

        const submitButton = elements.form.querySelector('[type="submit"]');
        const originalContent = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="bi bi-check2" aria-hidden="true"></i> Actualizado';
        window.setTimeout(function () {
            if (submitButton.isConnected) {
                submitButton.innerHTML = originalContent;
            }
        }, 1200);
    });

    renderReport();
})();
