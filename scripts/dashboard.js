(function () {
    const dashboard = document.getElementById('dashboard');

    if (!dashboard) {
        return;
    }

    const periodData = {
        today: {
            staff: 181,
            attendance: 97.2,
            coverage: 87,
            changes: 5,
            activeChanges: 2,
            expiring: 8,
            labels: ['06 h', '08 h', '10 h', '12 h', '14 h', '16 h'],
            attendanceValues: [96, 98, 97, 99, 97, 96],
            chartTitle: 'Asistencia durante el día',
            chartDescription: 'Comportamiento simulado por bloque horario.'
        },
        week: {
            staff: 186,
            attendance: 96.4,
            coverage: 87,
            changes: 24,
            activeChanges: 3,
            expiring: 8,
            labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            attendanceValues: [95, 97, 96, 98, 97, 94, 98],
            chartTitle: 'Asistencia de la semana',
            chartDescription: 'Porcentaje de personal presente respecto a la dotación programada.'
        },
        month: {
            staff: 192,
            attendance: 95.8,
            coverage: 89,
            changes: 91,
            activeChanges: 4,
            expiring: 11,
            labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
            attendanceValues: [95, 96, 95, 97],
            chartTitle: 'Asistencia del mes',
            chartDescription: 'Promedio semanal de asistencia durante los últimos 30 días.'
        }
    };

    const lineData = [
        { code: 'A01', staff: 48, attendance: 98, coverage: 94, changes: 7, activeChanges: 0, score: 96, status: 'Estable' },
        { code: 'A02', staff: 44, attendance: 96, coverage: 89, changes: 5, activeChanges: 1, score: 92, status: 'Estable' },
        { code: 'B01', staff: 51, attendance: 94, coverage: 82, changes: 8, activeChanges: 2, score: 86, status: 'Atención' },
        { code: 'B02', staff: 43, attendance: 97, coverage: 84, changes: 4, activeChanges: 0, score: 90, status: 'Seguimiento' }
    ];

    const alerts = [
        {
            type: 'danger',
            icon: 'bi-award',
            title: '3 certificaciones vencen esta semana',
            detail: 'Línea B01 · Estaciones de ensamble',
            meta: 'Prioridad alta'
        },
        {
            type: 'warning',
            icon: 'bi-person-exclamation',
            title: 'Cobertura debajo de la meta',
            detail: 'Línea B02 · Cobertura actual de 84%',
            meta: 'Revisar hoy'
        },
        {
            type: 'info',
            icon: 'bi-arrow-left-right',
            title: '2 puntos de cambio permanecen activos',
            detail: 'Línea B01 · Turno 2',
            meta: 'En proceso'
        }
    ];

    const elements = {
        period: dashboard.querySelector('#dashboardPeriod'),
        line: dashboard.querySelector('#dashboardLine'),
        reset: dashboard.querySelector('#dashboardReset'),
        updated: dashboard.querySelector('#dashboardUpdated'),
        staff: dashboard.querySelector('#dashboardStaff'),
        staffNote: dashboard.querySelector('#dashboardStaffNote'),
        attendance: dashboard.querySelector('#dashboardAttendance'),
        coverage: dashboard.querySelector('#dashboardCoverage'),
        coverageNote: dashboard.querySelector('#dashboardCoverageNote'),
        changes: dashboard.querySelector('#dashboardChanges'),
        changesNote: dashboard.querySelector('#dashboardChangesNote'),
        expiring: dashboard.querySelector('#dashboardExpiring'),
        expiringNote: dashboard.querySelector('#dashboardExpiringNote'),
        chartTitle: dashboard.querySelector('#dashboardChartTitle'),
        chartDescription: dashboard.querySelector('#dashboardChartDescription'),
        chart: dashboard.querySelector('#dashboardAttendanceChart .analytics-columns'),
        gauge: dashboard.querySelector('#dashboardCoverageGauge'),
        gaugeValue: dashboard.querySelector('#dashboardGaugeValue'),
        certified: dashboard.querySelector('#dashboardCertified'),
        due: dashboard.querySelector('#dashboardDue'),
        gap: dashboard.querySelector('#dashboardGap'),
        performance: dashboard.querySelector('#dashboardLinePerformance'),
        alertCount: dashboard.querySelector('#dashboardAlertCount'),
        alerts: dashboard.querySelector('#dashboardAlerts'),
        table: dashboard.querySelector('#dashboardLineTable')
    };

    function selectedLines() {
        if (elements.line.value === 'all') {
            return lineData;
        }

        return lineData.filter(function (line) {
            return line.code === elements.line.value;
        });
    }

    function formatPercent(value) {
        return Number(value).toLocaleString('es-MX', {
            minimumFractionDigits: value % 1 === 0 ? 0 : 1,
            maximumFractionDigits: 1
        }) + '%';
    }

    function statusClass(status) {
        if (status === 'Atención') {
            return 'is-danger';
        }

        if (status === 'Seguimiento') {
            return 'is-warning';
        }

        return '';
    }

    function renderAttendanceChart(data, line) {
        const adjustment = line ? line.attendance - data.attendance : 0;
        const values = data.attendanceValues.map(function (value) {
            return Math.max(80, Math.min(100, Math.round(value + adjustment)));
        });

        elements.chart.innerHTML = data.labels.map(function (label, index) {
            const value = values[index];

            return `
                <div class="analytics-column">
                    <div class="analytics-column-track" style="--value: ${value};">
                        <span class="analytics-column-value">${value}%</span>
                    </div>
                    <span class="analytics-column-label">${label}</span>
                </div>
            `;
        }).join('');

        const lineLabel = line ? ` para la línea ${line.code}` : '';
        elements.chart.parentElement.setAttribute(
            'aria-label',
            `Gráfica de asistencia${lineLabel}: ${values.join(', ')} por ciento`
        );
    }

    function renderPerformance(lines) {
        elements.performance.innerHTML = lines.map(function (line) {
            return `
                <div class="analytics-progress-item">
                    <div class="analytics-progress-label">
                        <strong>Línea ${line.code}</strong>
                        <small>${line.staff} personas</small>
                    </div>
                    <div class="analytics-progress-track" aria-hidden="true">
                        <div class="analytics-progress-fill" style="--value: ${line.score};"></div>
                    </div>
                    <span class="analytics-progress-value">${line.score}</span>
                </div>
            `;
        }).join('');
    }

    function renderAlerts(lineCode) {
        const visibleAlerts = lineCode === 'all'
            ? alerts
            : alerts.filter(function (alert) {
                return alert.detail.indexOf(lineCode) !== -1;
            });

        elements.alertCount.textContent = `${visibleAlerts.length} ${visibleAlerts.length === 1 ? 'pendiente' : 'pendientes'}`;

        if (!visibleAlerts.length) {
            elements.alerts.innerHTML = `
                <div class="analytics-empty">
                    <i class="bi bi-check2-circle" aria-hidden="true"></i>
                    No hay alertas simuladas para esta línea.
                </div>
            `;
            return;
        }

        elements.alerts.innerHTML = visibleAlerts.map(function (alert) {
            return `
                <div class="analytics-alert is-${alert.type}">
                    <span class="analytics-alert-icon">
                        <i class="bi ${alert.icon}" aria-hidden="true"></i>
                    </span>
                    <span class="analytics-alert-copy">
                        <strong>${alert.title}</strong>
                        <small>${alert.detail}</small>
                    </span>
                    <span class="analytics-alert-meta">${alert.meta}</span>
                </div>
            `;
        }).join('');
    }

    function renderTable(lines) {
        elements.table.innerHTML = lines.map(function (line) {
            return `
                <tr>
                    <td><span class="analytics-table-primary">Línea ${line.code}</span></td>
                    <td>${line.staff}</td>
                    <td>${line.attendance}%</td>
                    <td>${line.changes} registrados</td>
                    <td>${line.coverage}%</td>
                    <td><span class="analytics-status ${statusClass(line.status)}">${line.status}</span></td>
                </tr>
            `;
        }).join('');
    }

    function renderDashboard() {
        const period = periodData[elements.period.value] || periodData.week;
        const lines = selectedLines();
        const selectedLine = lines.length === 1 ? lines[0] : null;
        const staff = selectedLine ? selectedLine.staff : period.staff;
        const attendance = selectedLine ? selectedLine.attendance : period.attendance;
        const coverage = selectedLine ? selectedLine.coverage : period.coverage;
        const changeMultiplier = elements.period.value === 'today' ? 0.25 : (elements.period.value === 'month' ? 3.8 : 1);
        const changes = selectedLine
            ? Math.max(1, Math.round(selectedLine.changes * changeMultiplier))
            : period.changes;
        const activeChanges = selectedLine ? selectedLine.activeChanges : period.activeChanges;
        const certified = Math.round(staff * coverage / 100);
        const gap = Math.max(0, staff - certified);
        const expiring = selectedLine
            ? Math.max(1, Math.round(period.expiring * staff / period.staff))
            : period.expiring;

        elements.staff.textContent = staff.toLocaleString('es-MX');
        elements.staffNote.textContent = selectedLine
            ? `Dotación de la línea ${selectedLine.code}`
            : `En ${lines.length} líneas activas`;
        elements.attendance.textContent = formatPercent(attendance);
        elements.coverage.textContent = formatPercent(coverage);
        elements.coverageNote.textContent = `${certified} de ${staff} personas`;
        elements.changes.textContent = changes.toLocaleString('es-MX');
        elements.changesNote.textContent = `${Math.max(0, changes - activeChanges)} cerrados, ${activeChanges} activos`;
        elements.expiring.textContent = expiring.toLocaleString('es-MX');
        elements.expiringNote.textContent = 'Durante los próximos 30 días';
        elements.chartTitle.textContent = period.chartTitle;
        elements.chartDescription.textContent = period.chartDescription;
        elements.gauge.style.setProperty('--percentage', coverage);
        elements.gauge.setAttribute('aria-label', `${coverage} por ciento de cobertura certificada`);
        elements.gaugeValue.textContent = formatPercent(coverage);
        elements.certified.textContent = certified.toLocaleString('es-MX');
        elements.due.textContent = expiring.toLocaleString('es-MX');
        elements.gap.textContent = gap.toLocaleString('es-MX');
        elements.updated.textContent = 'Actualizado ahora';

        renderAttendanceChart(period, selectedLine);
        renderPerformance(lines);
        renderAlerts(elements.line.value);
        renderTable(lines);
    }

    elements.period.addEventListener('change', renderDashboard);
    elements.line.addEventListener('change', renderDashboard);
    elements.reset.addEventListener('click', function () {
        elements.period.value = 'week';
        elements.line.value = 'all';
        renderDashboard();
    });

    renderDashboard();
})();