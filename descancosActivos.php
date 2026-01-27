<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Registro de Pausas - Ley Silla</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --success-color: #27ae60;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: var(--primary-color);
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border: none;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .btn-success {
            background-color: var(--success-color);
            border: none;
        }
        
        .table th {
            background-color: var(--primary-color);
            color: white;
        }
        
        .badge-pause {
            background-color: var(--secondary-color);
        }
        
        .badge-missed {
            background-color: var(--accent-color);
        }
        
        .signature-pad {
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: crosshair;
        }
        
        .required::after {
            content: " *";
            color: var(--accent-color);
        }
        
        .section-title {
            border-left: 4px solid var(--secondary-color);
            padding-left: 10px;
            margin: 20px 0 15px;
        }
        
        .employee-card {
            transition: all 0.3s;
        }
        
        .employee-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .pause-indicator {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        
        .pause-taken {
            background-color: var(--success-color);
        }
        
        .pause-pending {
            background-color: #f39c12;
        }
        
        .pause-missed {
            background-color: var(--accent-color);
        }
        
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
        }
        
        .employee-id-input {
            font-size: 1.5rem;
            text-align: center;
            letter-spacing: 3px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-chair me-2"></i>Registro de Pausas - Ley Silla
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3" id="current-user">Supervisor: No asignado</span>
                <button class="btn btn-outline-light btn-sm" id="logout-btn">
                    <i class="fas fa-sign-out-alt me-1"></i>Cerrar Sesión
                </button>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Login Section -->
        <section id="login-section">
            <div class="card login-container">
                <div class="card-header text-center">
                    <h4 class="mb-0"><i class="fas fa-id-card me-2"></i>Identificación</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="employee-id" class="form-label">Código de Empleado</label>
                        <input type="text" class="form-control employee-id-input" id="employee-id" placeholder="Ingrese su código" maxlength="6">
                        <div class="form-text">Use su código de empleado o acerque su gafete al lector</div>
                    </div>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" id="login-btn">
                            <i class="fas fa-sign-in-alt me-1"></i>Ingresar
                        </button>
                    </div>
                    <div class="mt-3 text-center">
                        <small class="text-muted">Ejemplo: Use "123456" para supervisor, "100001" a "100005" para empleados</small>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Application (hidden by default) -->
        <section id="app-section" style="display: none;">
            <!-- Quick Registration Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="mb-0"><i class="fas fa-bolt me-2"></i>Registro Rápido de Pausa</h3>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <label for="quick-employee-id" class="form-label">Código de Empleado para Pausa</label>
                                    <input type="text" class="form-control employee-id-input" id="quick-employee-id" placeholder="Ingrese código del empleado" maxlength="6">
                                </div>
                                <div class="col-md-4">
                                    <div class="d-grid gap-2 mt-4">
                                        <button class="btn btn-success btn-lg" id="register-pause-btn">
                                            <i class="fas fa-clock me-1"></i>Registrar Pausa Actual
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Form Section -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Registro de Turno</h3>
                        </div>
                        <div class="card-body">
                            <form id="pause-form">
                                <!-- Información básica -->
                                <h4 class="section-title">Información del Turno</h4>
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="department" class="form-label required">Área o Departamento</label>
                                        <select class="form-select" id="department" name="department" required>
                                            <option value="">Seleccione un área</option>
                                            <option value="Producción">Producción</option>
                                            <option value="Calidad">Calidad</option>
                                            <option value="Almacén">Almacén</option>
                                            <option value="Mantenimiento">Mantenimiento</option>
                                            <option value="Administración">Administración</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="date" class="form-label required">Fecha</label>
                                        <input type="date" class="form-control" id="date" name="date" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="shift" class="form-label required">Turno</label>
                                        <select class="form-select" id="shift" name="shift" required>
                                            <option value="">Seleccione turno</option>
                                            <option value="Matutino">Matutino</option>
                                            <option value="Vespertino">Vespertino</option>
                                            <option value="Nocturno">Nocturno</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Lista de trabajadores y pausas -->
                                <h4 class="section-title">Registro de Pausas por Trabajador</h4>
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered" id="workers-table">
                                        <thead>
                                            <tr>
                                                <th>Nombre del Trabajador</th>
                                                <th>Pausa 1<br><small>10:00 - 10:15</small></th>
                                                <th>Pausa 2<br><small>12:00 - 12:15</small></th>
                                                <th>Pausa 3<br><small>15:00 - 15:15</small></th>
                                                <th>Pausa 4<br><small>17:00 - 17:15</small></th>
                                                <th>Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="workers-table-body">
                                            <!-- Las filas se generarán dinámicamente con JavaScript -->
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Observaciones generales -->
                                <h4 class="section-title">Observaciones Generales</h4>
                                <div class="mb-4">
                                    <label for="general-observations" class="form-label">Incidencias o Observaciones</label>
                                    <textarea class="form-control" id="general-observations" name="general_observations" rows="3" placeholder="Ej: El trabajador Juan Pérez no realizó la pausa 2 por mantenimiento urgente en línea 3..."></textarea>
                                </div>

                                <!-- Firma -->
                                <h4 class="section-title">Validación</h4>
                                <div class="mb-4">
                                    <label for="supervisor-signature" class="form-label required">Firma del Supervisor</label>
                                    <div class="signature-pad bg-light" id="supervisor-signature-pad" style="height: 150px;"></div>
                                    <input type="hidden" id="supervisor-signature" name="supervisor_signature" required>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-supervisor-signature">Limpiar Firma</button>
                                    </div>
                                </div>

                                <!-- Botones de acción -->
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" id="reset-form">Limpiar Formulario</button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-1"></i>Guardar Registro de Turno
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Employee Status Section -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="mb-0"><i class="fas fa-users me-2"></i>Estado de Empleados</h3>
                        </div>
                        <div class="card-body">
                            <div id="employees-status">
                                <!-- Tarjetas de empleados se generarán dinámicamente -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Records Section -->
            <section id="records-section" class="mt-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-history me-2"></i>Registros de Pausas</h3>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label for="filter-department" class="form-label">Filtrar por Área</label>
                                <select class="form-select" id="filter-department">
                                    <option value="">Todas las áreas</option>
                                    <option value="Producción">Producción</option>
                                    <option value="Calidad">Calidad</option>
                                    <option value="Almacén">Almacén</option>
                                    <option value="Mantenimiento">Mantenimiento</option>
                                    <option value="Administración">Administración</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter-date" class="form-label">Filtrar por Fecha</label>
                                <input type="date" class="form-control" id="filter-date">
                            </div>
                            <div class="col-md-3">
                                <label for="filter-shift" class="form-label">Filtrar por Turno</label>
                                <select class="form-select" id="filter-shift">
                                    <option value="">Todos los turnos</option>
                                    <option value="Matutino">Matutino</option>
                                    <option value="Vespertino">Vespertino</option>
                                    <option value="Nocturno">Nocturno</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" class="btn btn-primary w-100" id="apply-filters">
                                    <i class="fas fa-filter me-1"></i>Aplicar Filtros
                                </button>
                            </div>
                        </div>

                        <!-- Tabla de registros -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="records-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Área</th>
                                        <th>Turno</th>
                                        <th>Supervisor</th>
                                        <th>Trabajadores</th>
                                        <th>Pausas Registradas</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los registros se cargarán dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </section>
    </div>

    <!-- Modal para ver detalles del registro -->
    <div class="modal fade" id="recordDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles del Registro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="record-detail-content">
                    <!-- Contenido cargado dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="print-record">
                        <i class="fas fa-print me-1"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
   
    <!-- Signature Pad Library 
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
-->    
    <script>
        // Datos de ejemplo
        const employees = {
            "123456": { id: "123456", name: "Ana García", role: "supervisor", department: "Producción" },
            "100001": { id: "100001", name: "Carlos López", role: "employee", department: "Producción" },
            "100002": { id: "100002", name: "María Rodríguez", role: "employee", department: "Producción" },
            "100003": { id: "100003", name: "José Martínez", role: "employee", department: "Calidad" },
            "100004": { id: "100004", name: "Laura Hernández", role: "employee", department: "Almacén" },
            "100005": { id: "100005", name: "Pedro Sánchez", role: "employee", department: "Mantenimiento" }
        };

        // Estado de la aplicación
        let currentUser = null;
        let currentShift = {
            department: "",
            date: new Date().toISOString().split('T')[0],
            shift: "",
            supervisor: "",
            employees: {},
            observations: ""
        };
        
        let supervisorSignaturePad = null;
        let records = JSON.parse(localStorage.getItem('pauseRecords')) || [];

 
            // Configurar fecha actual
            document.getElementById('date').value = currentShift.date;
            
            // Inicializar el pad de firma
            //const supervisorCanvas = document.getElementById('supervisor-signature-pad');
            //supervisorSignaturePad = new SignaturePad(supervisorCanvas);
            
            // Configurar eventos
           // document.getElementById('clear-supervisor-signature').addEventListener('click', function() {
           //     supervisorSignaturePad.clear();
           //     document.getElementById('supervisor-signature').value = '';
           // });
            
           // supervisorSignaturePad.addEventListener('endStroke', function() {
           //     document.getElementById('supervisor-signature').value = supervisorSignaturePad.toDataURL();
           // });
            
            document.getElementById('login-btn').addEventListener('click', handleLogin);
            document.getElementById('employee-id').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') handleLogin();
            });
            
            document.getElementById('logout-btn').addEventListener('click', handleLogout);
            
            document.getElementById('register-pause-btn').addEventListener('click', registerQuickPause);
            document.getElementById('quick-employee-id').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') registerQuickPause();
            });
            
            document.getElementById('pause-form').addEventListener('submit', saveShiftRecord);
            document.getElementById('reset-form').addEventListener('click', resetForm);
            
            document.getElementById('apply-filters').addEventListener('click', applyFilters);
            document.getElementById('print-record').addEventListener('click', printRecord);
            
            // Cargar registros existentes
            loadRecords();
    
        
        function handleLogin() {
            
             const employeeId = document.getElementById('employee-id').value;
            
            if (!employeeId) {
                alert('Por favor, ingrese su código de empleado');
                return;
            }
            
            if (!employees[employeeId]) {
                alert('Código de empleado no válido');
                return;
            }
            
            currentUser = employees[employeeId];
            
            // Mostrar la aplicación y ocultar el login
            document.getElementById('login-section').style.display = 'none';
            document.getElementById('app-section').style.display = 'block';
            document.getElementById('current-user').textContent = `Supervisor: ${currentUser.name}`;
            
            // Si es supervisor, configurar el turno actual
            if (currentUser.role === 'supervisor') {
                currentShift.supervisor = currentUser.name;
                document.getElementById('department').value = currentUser.department;
                
                // Cargar empleados del departamento
                loadDepartmentEmployees(currentUser.department);
            }
            
            // Limpiar el campo de login
            document.getElementById('employee-id').value = '';
        }
        
        function handleLogout() {
            currentUser = null;
            document.getElementById('login-section').style.display = 'block';
            document.getElementById('app-section').style.display = 'none';
            document.getElementById('current-user').textContent = 'Supervisor: No asignado';
            resetForm();
        }
        
        function loadDepartmentEmployees(department) {
            const tableBody = document.getElementById('workers-table-body');
            tableBody.innerHTML = '';
            
            const employeesStatus = document.getElementById('employees-status');
            employeesStatus.innerHTML = '';
            
            // Filtrar empleados por departamento
            const departmentEmployees = Object.values(employees).filter(emp => 
                emp.department === department && emp.role === 'employee'
            );
            
            // Inicializar el estado del turno para estos empleados
            departmentEmployees.forEach(employee => {
                if (!currentShift.employees[employee.id]) {
                    currentShift.employees[employee.id] = {
                        name: employee.name,
                        pauses: { 1: null, 2: null, 3: null, 4: null },
                        observations: ''
                    };
                }
                
                // Agregar fila a la tabla
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${employee.name}</td>
                    <td>
                        <input type="time" class="form-control" data-employee="${employee.id}" data-pause="1" 
                               value="${currentShift.employees[employee.id].pauses[1] || ''}">
                    </td>
                    <td>
                        <input type="time" class="form-control" data-employee="${employee.id}" data-pause="2" 
                               value="${currentShift.employees[employee.id].pauses[2] || ''}">
                    </td>
                    <td>
                        <input type="time" class="form-control" data-employee="${employee.id}" data-pause="3" 
                               value="${currentShift.employees[employee.id].pauses[3] || ''}">
                    </td>
                    <td>
                        <input type="time" class="form-control" data-employee="${employee.id}" data-pause="4" 
                               value="${currentShift.employees[employee.id].pauses[4] || ''}">
                    </td>
                    <td>
                        <input type="text" class="form-control" data-employee="${employee.id}" 
                               value="${currentShift.employees[employee.id].observations}" 
                               placeholder="Observaciones...">
                    </td>
                `;
                tableBody.appendChild(row);
                
                // Agregar tarjeta de estado
                const card = document.createElement('div');
                card.className = 'card employee-card mb-2';
                card.innerHTML = `
                    <div class="card-body p-3">
                        <h6 class="card-title">${employee.name}</h6>
                        <div class="d-flex justify-content-between">
                            <span>Pausa 1: <span class="pause-indicator ${currentShift.employees[employee.id].pauses[1] ? 'pause-taken' : 'pause-pending'}"></span></span>
                            <span>Pausa 2: <span class="pause-indicator ${currentShift.employees[employee.id].pauses[2] ? 'pause-taken' : 'pause-pending'}"></span></span>
                            <span>Pausa 3: <span class="pause-indicator ${currentShift.employees[employee.id].pauses[3] ? 'pause-taken' : 'pause-pending'}"></span></span>
                            <span>Pausa 4: <span class="pause-indicator ${currentShift.employees[employee.id].pauses[4] ? 'pause-taken' : 'pause-pending'}"></span></span>
                        </div>
                    </div>
                `;
                employeesStatus.appendChild(card);
            });
            
            // Agregar event listeners a los inputs de tiempo
            document.querySelectorAll('input[type="time"]').forEach(input => {
                input.addEventListener('change', function() {
                    const employeeId = this.getAttribute('data-employee');
                    const pauseNum = this.getAttribute('data-pause');
                    currentShift.employees[employeeId].pauses[pauseNum] = this.value;
                    updateEmployeeStatus(employeeId);
                });
            });
            
            // Agregar event listeners a los inputs de observaciones
            document.querySelectorAll('input[type="text"][data-employee]').forEach(input => {
                input.addEventListener('change', function() {
                    const employeeId = this.getAttribute('data-employee');
                    currentShift.employees[employeeId].observations = this.value;
                });
            });
        }
        
        function updateEmployeeStatus(employeeId) {
            const employee = currentShift.employees[employeeId];
            const indicators = document.querySelectorAll(`[data-employee="${employeeId}"]`);
            
            // Actualizar indicadores visuales
            document.querySelectorAll('.employee-card').forEach(card => {
                if (card.querySelector('.card-title').textContent === employee.name) {
                    const indicators = card.querySelectorAll('.pause-indicator');
                    indicators[0].className = `pause-indicator ${employee.pauses[1] ? 'pause-taken' : 'pause-pending'}`;
                    indicators[1].className = `pause-indicator ${employee.pauses[2] ? 'pause-taken' : 'pause-pending'}`;
                    indicators[2].className = `pause-indicator ${employee.pauses[3] ? 'pause-taken' : 'pause-pending'}`;
                    indicators[3].className = `pause-indicator ${employee.pauses[4] ? 'pause-taken' : 'pause-pending'}`;
                }
            });
        }
        
        function registerQuickPause() {
            if (!currentUser || currentUser.role !== 'supervisor') {
                alert('Solo los supervisores pueden registrar pausas');
                return;
            }
            
            const employeeId = document.getElementById('quick-employee-id').value;
            
            if (!employeeId) {
                alert('Por favor, ingrese el código del empleado');
                return;
            }
            
            if (!employees[employeeId] || employees[employeeId].role !== 'employee') {
                alert('Código de empleado no válido');
                return;
            }
            
            if (employees[employeeId].department !== currentShift.department) {
                alert('Este empleado no pertenece al departamento actual');
                return;
            }
            
            // Determinar qué pausa registrar según la hora actual
            const now = new Date();
            const currentTime = now.getHours() * 60 + now.getMinutes(); // Tiempo en minutos desde medianoche
            
            // Definir horarios de pausas (en minutos desde medianoche)
            const pauseTimes = {
                1: { start: 10*60, end: 10*60+15 },   // 10:00 - 10:15
                2: { start: 12*60, end: 12*60+15 },   // 12:00 - 12:15
                3: { start: 15*60, end: 15*60+15 },   // 15:00 - 15:15
                4: { start: 17*60, end: 17*60+15 }    // 17:00 - 17:15
            };
            
            let pauseToRegister = null;
            
            // Encontrar la pausa actual
            for (const [pauseNum, timeRange] of Object.entries(pauseTimes)) {
                if (currentTime >= timeRange.start && currentTime <= timeRange.end) {
                    pauseToRegister = pauseNum;
                    break;
                }
            }
            
            if (!pauseToRegister) {
                alert('No hay una pausa programada en este momento');
                return;
            }
            
            // Registrar la pausa
            const timeString = now.getHours().toString().padStart(2, '0') + ':' + 
                              now.getMinutes().toString().padStart(2, '0');
            
            currentShift.employees[employeeId].pauses[pauseToRegister] = timeString;
            
            // Actualizar la interfaz
            const input = document.querySelector(`input[data-employee="${employeeId}"][data-pause="${pauseToRegister}"]`);
            if (input) {
                input.value = timeString;
            }
            
            updateEmployeeStatus(employeeId);
            
            // Limpiar el campo y mostrar confirmación
            document.getElementById('quick-employee-id').value = '';
            alert(`Pausa ${pauseToRegister} registrada para ${employees[employeeId].name} a las ${timeString}`);
        }
        
        function saveShiftRecord(e) {
            e.preventDefault();
            
            if (!currentUser || currentUser.role !== 'supervisor') {
                alert('Solo los supervisores pueden guardar registros');
                return;
            }
            
            // Validar campos requeridos
            const department = document.getElementById('department').value;
            const date = document.getElementById('date').value;
            const shift = document.getElementById('shift').value;
            const signature = document.getElementById('supervisor-signature').value;
            
            if (!department || !date || !shift) {
                alert('Por favor, complete todos los campos requeridos');
                return;
            }
            
            // Actualizar el turno actual con los datos del formulario
            currentShift.department = department;
            currentShift.date = date;
            currentShift.shift = shift;
            currentShift.observations = document.getElementById('general-observations').value;
            
            // Crear el registro
            const record = {
                id: generateId(),
                ...currentShift,
                timestamp: new Date().toISOString()
            };
            
            // Guardar en el almacenamiento local
            records.push(record);
            localStorage.setItem('pauseRecords', JSON.stringify(records));
            
            // Recargar la tabla de registros
            loadRecords();
            
            // Mostrar confirmación y resetear el formulario
            alert('Registro guardado correctamente');
            resetForm();
        }
        
        function resetForm() {
            document.getElementById('pause-form').reset();
            document.getElementById('date').value = new Date().toISOString().split('T')[0];
            supervisorSignaturePad.clear();
            document.getElementById('supervisor-signature').value = '';
            
            // Resetear el estado del turno
            if (currentUser && currentUser.role === 'supervisor') {
                currentShift.department = currentUser.department;
                currentShift.date = new Date().toISOString().split('T')[0];
                currentShift.shift = "";
                currentShift.supervisor = currentUser.name;
                currentShift.observations = "";
                
                // Resetear pausas de empleados
                Object.keys(currentShift.employees).forEach(employeeId => {
                    currentShift.employees[employeeId].pauses = { 1: null, 2: null, 3: null, 4: null };
                    currentShift.employees[employeeId].observations = "";
                });
                
                // Recargar empleados
                loadDepartmentEmployees(currentUser.department);
            }
        }
        
        function loadRecords() {
            const tableBody = document.querySelector('#records-table tbody');
            tableBody.innerHTML = '';
            
            if (records.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="7" class="text-center">No hay registros disponibles</td></tr>';
                return;
            }
            
            records.forEach(record => {
                const employeeCount = Object.keys(record.employees).length;
                const pauseCount = Object.values(record.employees).reduce((total, emp) => {
                    return total + Object.values(emp.pauses).filter(p => p !== null).length;
                }, 0);
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${formatDate(record.date)}</td>
                    <td>${record.department}</td>
                    <td>${record.shift}</td>
                    <td>${record.supervisor}</td>
                    <td>${employeeCount}</td>
                    <td>${pauseCount}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewRecordDetails('${record.id}')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }
        
        function applyFilters() {
            // En una aplicación real, aquí se filtrarían los registros
            // Por simplicidad, recargamos todos los registros
            loadRecords();
        }
        
        function viewRecordDetails(recordId) {
            const record = records.find(r => r.id === recordId);
            if (!record) return;
            
            const modalContent = document.getElementById('record-detail-content');
            
            let employeesHTML = '';
            Object.values(record.employees).forEach(employee => {
                employeesHTML += `
                    <tr>
                        <td>${employee.name}</td>
                        <td>${employee.pauses[1] || 'No registrada'}</td>
                        <td>${employee.pauses[2] || 'No registrada'}</td>
                        <td>${employee.pauses[3] || 'No registrada'}</td>
                        <td>${employee.pauses[4] || 'No registrada'}</td>
                        <td>${employee.observations || 'Ninguna'}</td>
                    </tr>
                `;
            });
            
            modalContent.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Fecha:</strong> ${formatDate(record.date)}</p>
                        <p><strong>Área:</strong> ${record.department}</p>
                        <p><strong>Turno:</strong> ${record.shift}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Supervisor:</strong> ${record.supervisor}</p>
                        <p><strong>Total de trabajadores:</strong> ${Object.keys(record.employees).length}</p>
                        <p><strong>Pausas registradas:</strong> ${Object.values(record.employees).reduce((total, emp) => 
                            total + Object.values(emp.pauses).filter(p => p !== null).length, 0)}</p>
                    </div>
                </div>
                <div class="mt-3">
                    <h6>Detalles de pausas por trabajador:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Trabajador</th>
                                    <th>Pausa 1</th>
                                    <th>Pausa 2</th>
                                    <th>Pausa 3</th>
                                    <th>Pausa 4</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${employeesHTML}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mt-3">
                    <h6>Observaciones Generales:</h6>
                    <p>${record.observations || 'Ninguna'}</p>
                </div>
                <div class="mt-3">
                    <h6>Firma del Supervisor:</h6>
                    <div class="border p-2 text-center">
                        <img src="${record.supervisor_signature}" alt="Firma del supervisor" style="max-height: 100px;">
                    </div>
                </div>
            `;
            
            // Mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById('recordDetailModal'));
            modal.show();
        }
        
        function printRecord() {
            window.print();
        }
        
        function generateId() {
            return Date.now().toString(36) + Math.random().toString(36).substr(2);
        }
        
        function formatDate(dateString) {
            const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        }
    </script>
</body>
</html>