<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Puntos de Cambio y Certificaciones</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }
        
        .sidebar {
            background-color: var(--primary-color);
            color: white;
            min-height: 100vh;
            padding: 0;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 0;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            padding: 20px;
        }
        
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        
        .card-icon {
            font-size: 2.5rem;
            opacity: 0.7;
        }
        
        .line-card {
            border-left: 4px solid var(--secondary-color);
        }
        
        .station-box {
            width: 60px;
            height: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin: 5px;
            font-size: 0.8rem;
            background-color: white;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .station-box:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .station-occupied {
            background-color: #e8f5e9;
            border-color: var(--success-color);
        }
        
        .station-pending {
            background-color: #fff3e0;
            border-color: var(--warning-color);
        }
        
        .station-vacant {
            background-color: #f5f5f5;
        }
        
        .status-badge {
            font-size: 0.7rem;
            padding: 4px 8px;
            border-radius: 12px;
        }
        
        .certification-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
        }
        
        .cert-item {
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .cert-approved {
            border-left: 3px solid var(--success-color);
        }
        
        .cert-pending {
            border-left: 3px solid var(--warning-color);
        }
        
        .cert-not-certified {
            border-left: 3px solid #ccc;
        }
        
        .nav-tabs-custom .nav-link {
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .nav-tabs-custom .nav-link.active {
            border-bottom: 3px solid var(--secondary-color);
            color: var(--secondary-color);
        }
        
        .attendance-select {
            min-width: 140px;
        }
        
        .evaluation-section {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: white;
        }
        
        .progress-sm {
            height: 8px;
        }
        
        .action-btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
            
            .station-box {
                width: 50px;
                height: 50px;
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <h4 class="text-center mb-4 mt-3">Sistema PCM</h4>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" data-section="dashboard">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-section="lines">
                                <i class="bi bi-grid-3x3-gap"></i> Líneas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-section="certifications">
                                <i class="bi bi-award"></i> Certificaciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-section="reports">
                                <i class="bi bi-graph-up"></i> Reportes
                            </a>
                        </li>
                    </ul>
                    
                    <div class="mt-5 p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <img src="https://ui-avatars.com/api/?name=Admin+User&background=0D8ABC&color=fff" 
                                     alt="Usuario" width="40" height="40" class="rounded-circle">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Admin User</h6>
                                <small class="text-muted">Administrador</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 col-lg-10 ms-sm-auto px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 id="page-title" class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-calendar"></i> 15 Mar, 2024
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                            <i class="bi bi-bell"></i>
                            <span class="badge bg-danger">3</span>
                        </button>
                    </div>
                </div>
                
                <!-- Dashboard Section -->
                <div id="dashboard-section" class="content-section">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title">Líneas Activas</h5>
                                            <h2 class="text-primary">12</h2>
                                        </div>
                                        <div class="card-icon text-primary">
                                            <i class="bi bi-grid-3x3-gap"></i>
                                        </div>
                                    </div>
                                    <p class="card-text"><small class="text-muted">+2 desde el mes pasado</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title">Puntos Cambio</h5>
                                            <h2 class="text-warning">5</h2>
                                        </div>
                                        <div class="card-icon text-warning">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </div>
                                    </div>
                                    <p class="card-text"><small class="text-muted">3 en progreso</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title">Certif. Vencidas</h5>
                                            <h2 class="text-danger">3</h2>
                                        </div>
                                        <div class="card-icon text-danger">
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </div>
                                    </div>
                                    <p class="card-text"><small class="text-muted">Requieren atención</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title">Operadores</h5>
                                            <h2 class="text-success">48</h2>
                                        </div>
                                        <div class="card-icon text-success">
                                            <i class="bi bi-people"></i>
                                        </div>
                                    </div>
                                    <p class="card-text"><small class="text-muted">92% de asistencia</small></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Actividad Reciente</h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-arrow-left-right text-warning"></i>
                                                <span class="ms-2">Punto cambio Línea A - Juan Pérez</span>
                                            </div>
                                            <small class="text-muted">Hace 2 horas</small>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-award text-success"></i>
                                                <span class="ms-2">Nueva certificación María González</span>
                                            </div>
                                            <small class="text-muted">Hace 5 horas</small>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-clipboard-check text-primary"></i>
                                                <span class="ms-2">Asistencia registrada Línea B</span>
                                            </div>
                                            <small class="text-muted">Ayer</small>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-arrow-left-right text-warning"></i>
                                                <span class="ms-2">Punto cambio Línea C - Carlos Ruiz</span>
                                            </div>
                                            <small class="text-muted">Ayer</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Próximos Vencimientos</h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-award text-warning"></i>
                                                <span class="ms-2">Cert. Carlos Ruiz - Proceso X</span>
                                            </div>
                                            <small class="text-danger">15/03/2024</small>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-award text-warning"></i>
                                                <span class="ms-2">Cert. Ana López - Proceso Y</span>
                                            </div>
                                            <small class="text-danger">18/03/2024</small>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-award text-warning"></i>
                                                <span class="ms-2">Cert. Luis Fernández - Proceso Z</span>
                                            </div>
                                            <small class="text-warning">22/03/2024</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Lines Section -->
                <div id="lines-section" class="content-section d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>Gestión de Líneas</h3>
                        <div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newLineModal">
                                <i class="bi bi-plus-circle"></i> Nueva Línea
                            </button>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card line-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Línea A</h5>
                                    <p class="card-text">
                                        <small class="text-muted">Estaciones: 15</small><br>
                                        <small class="text-muted">Supervisor: Carlos Mendoza</small><br>
                                        <small class="text-muted">Operadores: 8/10</small>
                                    </p>
                                    <div class="d-flex flex-wrap mb-3">
                                        <span class="badge bg-success me-1 mb-1">Activa</span>
                                        <span class="badge bg-warning me-1 mb-1">2 Ptos Cambio</span>
                                    </div>
                                    <div class="d-grid gap-2 d-md-flex">
                                        <button class="btn btn-sm btn-outline-primary action-btn view-line" data-line="A">
                                            <i class="bi bi-eye"></i> Ver
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary action-btn">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning action-btn">
                                            <i class="bi bi-arrow-left-right"></i> Pto Cambio
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-4">
                            <div class="card line-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Línea B</h5>
                                    <p class="card-text">
                                        <small class="text-muted">Estaciones: 12</small><br>
                                        <small class="text-muted">Supervisor: Ana Rodríguez</small><br>
                                        <small class="text-muted">Operadores: 10/12</small>
                                    </p>
                                    <div class="d-flex flex-wrap mb-3">
                                        <span class="badge bg-success me-1 mb-1">Activa</span>
                                        <span class="badge bg-info me-1 mb-1">1 Pto Cambio</span>
                                    </div>
                                    <div class="d-grid gap-2 d-md-flex">
                                        <button class="btn btn-sm btn-outline-primary action-btn view-line" data-line="B">
                                            <i class="bi bi-eye"></i> Ver
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary action-btn">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning action-btn">
                                            <i class="bi bi-arrow-left-right"></i> Pto Cambio
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-4">
                            <div class="card line-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Línea C</h5>
                                    <p class="card-text">
                                        <small class="text-muted">Estaciones: 10</small><br>
                                        <small class="text-muted">Supervisor: Luis García</small><br>
                                        <small class="text-muted">Operadores: 7/10</small>
                                    </p>
                                    <div class="d-flex flex-wrap mb-3">
                                        <span class="badge bg-success me-1 mb-1">Activa</span>
                                        <span class="badge bg-secondary me-1 mb-1">Sin cambios</span>
                                    </div>
                                    <div class="d-grid gap-2 d-md-flex">
                                        <button class="btn btn-sm btn-outline-primary action-btn view-line" data-line="C">
                                            <i class="bi bi-eye"></i> Ver
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary action-btn">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning action-btn">
                                            <i class="bi bi-arrow-left-right"></i> Pto Cambio
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Line Detail Section -->
                <div id="line-detail-section" class="content-section d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <button class="btn btn-outline-secondary btn-sm back-to-lines">
                                <i class="bi bi-arrow-left"></i> Volver a Líneas
                            </button>
                            <h3 class="d-inline ms-2" id="line-detail-title">Línea A - Detalle</h3>
                        </div>
                        <div>
                            <button class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-printer"></i> Imprimir
                            </button>
                        </div>
                    </div>
                    
                    <ul class="nav nav-tabs nav-tabs-custom" id="lineDetailTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="stations-tab" data-bs-toggle="tab" data-bs-target="#stations" type="button" role="tab">Estaciones</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="operators-tab" data-bs-toggle="tab" data-bs-target="#operators" type="button" role="tab">Operadores</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="change-points-tab" data-bs-toggle="tab" data-bs-target="#change-points" type="button" role="tab">Puntos Cambio</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab">Asistencia</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content p-3 border border-top-0 rounded-bottom" id="lineDetailTabContent">
                        <div class="tab-pane fade show active" id="stations" role="tabpanel">
                            <h5>Distribución de Estaciones</h5>
                            <div class="d-flex flex-wrap mb-4" id="stations-container">
                                <!-- Las estaciones se generarán dinámicamente con JavaScript -->
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Información de Estación</h5>
                                </div>
                                <div class="card-body" id="station-info">
                                    <p class="text-muted">Seleccione una estación para ver los detalles</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="operators" role="tabpanel">
                            <h5>Operadores Disponibles</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Certificaciones</th>
                                            <th>Estación Actual</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Carlos Ruiz</td>
                                            <td>Proceso X, Proceso Y, Proceso Z</td>
                                            <td>Estación 03</td>
                                            <td><span class="badge bg-success">Activo</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">Ver</button>
                                                <button class="btn btn-sm btn-outline-warning">Reasignar</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Luis Fernández</td>
                                            <td>Proceso X, Proceso Y</td>
                                            <td>Estación 05</td>
                                            <td><span class="badge bg-success">Activo</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">Ver</button>
                                                <button class="btn btn-sm btn-outline-warning">Reasignar</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Ana López</td>
                                            <td>Proceso X, Proceso Z</td>
                                            <td>Estación 04</td>
                                            <td><span class="badge bg-success">Activo</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">Ver</button>
                                                <button class="btn btn-sm btn-outline-warning">Reasignar</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="change-points" role="tabpanel">
                            <h5>Puntos de Cambio Recientes</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Estación</th>
                                            <th>Operador Anterior</th>
                                            <th>Operador Nuevo</th>
                                            <th>Fecha</th>
                                            <th>Tipo</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>PC-001</td>
                                            <td>Estación 02</td>
                                            <td>Juan Pérez</td>
                                            <td>María González</td>
                                            <td>15/03/2024</td>
                                            <td><span class="badge bg-info">Programado</span></td>
                                            <td><span class="badge bg-warning">En progreso</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">Seguimiento</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>PC-002</td>
                                            <td>Estación 07</td>
                                            <td>Roberto Sánchez</td>
                                            <td>Carlos Ruiz</td>
                                            <td>10/03/2024</td>
                                            <td><span class="badge bg-warning">Inesperado</span></td>
                                            <td><span class="badge bg-success">Completado</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">Ver</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <button class="btn btn-primary mt-3">
                                <i class="bi bi-plus-circle"></i> Nuevo Punto de Cambio
                            </button>
                        </div>
                        
                        <div class="tab-pane fade" id="attendance" role="tabpanel">
                            <h5>Registro de Asistencia</h5>
                            <div class="d-flex justify-content-between mb-3">
                                <div>
                                    <button class="btn btn-outline-secondary">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                    <span class="mx-3 fw-bold">15 de Marzo, 2024</span>
                                    <button class="btn btn-outline-secondary">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                </div>
                                <button class="btn btn-success">
                                    <i class="bi bi-check-lg"></i> Guardar
                                </button>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Estación</th>
                                            <th>Operador</th>
                                            <th>Asistencia</th>
                                            <th>Comentarios</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>01</td>
                                            <td>Juan Pérez</td>
                                            <td>
                                                <select class="form-select form-select-sm attendance-select">
                                                    <option selected>✅ Asistió</option>
                                                    <option>🟡 Permiso</option>
                                                    <option>❌ Falta</option>
                                                    <option>🏖️ Vacaciones</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" placeholder="Comentarios">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>02</td>
                                            <td>María González</td>
                                            <td>
                                                <select class="form-select form-select-sm attendance-select">
                                                    <option>✅ Asistió</option>
                                                    <option selected>🟡 Permiso</option>
                                                    <option>❌ Falta</option>
                                                    <option>🏖️ Vacaciones</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" value="Médico" placeholder="Comentarios">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>03</td>
                                            <td>Carlos Ruiz</td>
                                            <td>
                                                <select class="form-select form-select-sm attendance-select">
                                                    <option>✅ Asistió</option>
                                                    <option>🟡 Permiso</option>
                                                    <option selected>❌ Falta</option>
                                                    <option>🏖️ Vacaciones</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" placeholder="Comentarios">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Certifications Section -->
                <div id="certifications-section" class="content-section d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>Gestión de Certificaciones</h3>
                        <div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCertificationModal">
                                <i class="bi bi-plus-circle"></i> Nueva Certificación
                            </button>
                        </div>
                    </div>
                    
                    <ul class="nav nav-tabs nav-tabs-custom mb-3" id="certificationsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="operators-tab" data-bs-toggle="tab" data-bs-target="#operators-list" type="button" role="tab">Operadores</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="certifications-list-tab" data-bs-toggle="tab" data-bs-target="#certifications-list" type="button" role="tab">Lista de Certificaciones</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="certificationsTabContent">
                        <div class="tab-pane fade show active" id="operators-list" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">Juan Pérez</h5>
                                            <p class="card-text text-muted">ID: OP-001</p>
                                            <div class="certification-grid">
                                                <div class="cert-item cert-approved">
                                                    <div>Proceso X</div>
                                                    <div class="fw-bold text-success">Liberado</div>
                                                    <div class="small text-muted">15/03/2024</div>
                                                </div>
                                                <div class="cert-item cert-pending">
                                                    <div>Proceso Y</div>
                                                    <div class="fw-bold text-warning">En curso</div>
                                                    <div class="small text-muted">20/03/2024</div>
                                                </div>
                                                <div class="cert-item cert-not-certified">
                                                    <div>Proceso Z</div>
                                                    <div class="fw-bold text-muted">No cert.</div>
                                                    <div class="small text-muted">-</div>
                                                </div>
                                                <div class="cert-item">
                                                    <div>Proceso W</div>
                                                    <div class="fw-bold">I</div>
                                                    <div class="small text-muted">-</div>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <button class="btn btn-sm btn-outline-primary">Ver Detalles</button>
                                                <button class="btn btn-sm btn-outline-warning">Actualizar ILU</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">María González</h5>
                                            <p class="card-text text-muted">ID: OP-002</p>
                                            <div class="certification-grid">
                                                <div class="cert-item cert-approved">
                                                    <div>Proceso X</div>
                                                    <div class="fw-bold text-success">Liberado</div>
                                                    <div class="small text-muted">10/02/2024</div>
                                                </div>
                                                <div class="cert-item cert-approved">
                                                    <div>Proceso Y</div>
                                                    <div class="fw-bold text-success">Liberado</div>
                                                    <div class="small text-muted">05/03/2024</div>
                                                </div>
                                                <div class="cert-item">
                                                    <div>Proceso Z</div>
                                                    <div class="fw-bold">U</div>
                                                    <div class="small text-muted">-</div>
                                                </div>
                                                <div class="cert-item">
                                                    <div>Proceso W</div>
                                                    <div class="fw-bold">L</div>
                                                    <div class="small text-muted">-</div>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <button class="btn btn-sm btn-outline-primary">Ver Detalles</button>
                                                <button class="btn btn-sm btn-outline-warning">Actualizar ILU</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="certifications-list" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Lista de Certificaciones</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Proceso</th>
                                                    <th>Operadores Certificados</th>
                                                    <th>Operadores Liberados</th>
                                                    <th>Próximos Vencimientos</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Proceso X</td>
                                                    <td>24</td>
                                                    <td>18</td>
                                                    <td>3</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary">Ver Lista</button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Proceso Y</td>
                                                    <td>18</td>
                                                    <td>15</td>
                                                    <td>2</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary">Ver Lista</button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Proceso Z</td>
                                                    <td>12</td>
                                                    <td>10</td>
                                                    <td>1</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary">Ver Lista</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Reports Section -->
                <div id="reports-section" class="content-section d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>Reportes y Estadísticas</h3>
                        <div>
                            <button class="btn btn-outline-primary">
                                <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                            </button>
                            <button class="btn btn-outline-secondary ms-2">
                                <i class="bi bi-printer"></i> Imprimir
                            </button>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Filtros</h5>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Línea</label>
                                            <select class="form-select">
                                                <option selected>Todas</option>
                                                <option>Línea A</option>
                                                <option>Línea B</option>
                                                <option>Línea C</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Fecha Inicio</label>
                                            <input type="date" class="form-control" value="2024-03-01">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Fecha Fin</label>
                                            <input type="date" class="form-control" value="2024-03-15">
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <button class="btn btn-primary">Aplicar Filtros</button>
                                        <button class="btn btn-outline-secondary">Limpiar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Resumen General</h5>
                                    <div class="mb-2">
                                        <span class="fw-bold">Asistencia promedio:</span> 92%
                                    </div>
                                    <div class="mb-2">
                                        <span class="fw-bold">Puntos cambio este mes:</span> 5
                                    </div>
                                    <div class="mb-2">
                                        <span class="fw-bold">Certificaciones vencidas:</span> 3
                                    </div>
                                    <div class="mb-2">
                                        <span class="fw-bold">Operadores activos:</span> 48/52
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Asistencia por Línea</h5>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Línea A</span>
                                        <span>95%</span>
                                    </div>
                                    <div class="progress mb-3 progress-sm">
                                        <div class="progress-bar" role="progressbar" style="width: 95%"></div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Línea B</span>
                                        <span>87%</span>
                                    </div>
                                    <div class="progress mb-3 progress-sm">
                                        <div class="progress-bar" role="progressbar" style="width: 87%"></div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Línea C</span>
                                        <span>91%</span>
                                    </div>
                                    <div class="progress mb-3 progress-sm">
                                        <div class="progress-bar" role="progressbar" style="width: 91%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Puntos de Cambio por Tipo</h5>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Programados</span>
                                        <span>60%</span>
                                    </div>
                                    <div class="progress mb-3 progress-sm">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: 60%"></div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Inesperados</span>
                                        <span>30%</span>
                                    </div>
                                    <div class="progress mb-3 progress-sm">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 30%"></div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Especiales (baño)</span>
                                        <span>10%</span>
                                    </div>
                                    <div class="progress mb-3 progress-sm">
                                        <div class="progress-bar bg-secondary" role="progressbar" style="width: 10%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Datos Detallados</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Línea</th>
                                            <th>Asistencia</th>
                                            <th>Puntos Cambio</th>
                                            <th>Cert. Próximas a Vencer</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Línea A</td>
                                            <td>95%</td>
                                            <td>2</td>
                                            <td>1</td>
                                            <td><span class="badge bg-success">Óptimo</span></td>
                                        </tr>
                                        <tr>
                                            <td>Línea B</td>
                                            <td>87%</td>
                                            <td>1</td>
                                            <td>2</td>
                                            <td><span class="badge bg-warning">Atención</span></td>
                                        </tr>
                                        <tr>
                                            <td>Línea C</td>
                                            <td>91%</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td><span class="badge bg-success">Óptimo</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales -->
    
    <!-- Modal Nueva Línea -->
    <div class="modal fade" id="newLineModal" tabindex="-1" aria-labelledby="newLineModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newLineModalLabel">Nueva Línea</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="lineName" class="form-label">Nombre de Línea</label>
                            <input type="text" class="form-control" id="lineName" placeholder="Ej: Línea D">
                        </div>
                        <div class="mb-3">
                            <label for="supervisor" class="form-label">Supervisor</label>
                            <select class="form-select" id="supervisor">
                                <option selected>Seleccionar supervisor</option>
                                <option>Carlos Mendoza</option>
                                <option>Ana Rodríguez</option>
                                <option>Luis García</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="stationsCount" class="form-label">Número de Estaciones</label>
                            <input type="number" class="form-control" id="stationsCount" min="1" value="10">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary">Guardar Línea</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Nueva Certificación -->
    <div class="modal fade" id="newCertificationModal" tabindex="-1" aria-labelledby="newCertificationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newCertificationModalLabel">Nueva Certificación / Actualización ILU</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="operatorSelect" class="form-label">Operador</label>
                            <select class="form-select" id="operatorSelect">
                                <option selected>Seleccionar operador</option>
                                <option>Juan Pérez</option>
                                <option>María González</option>
                                <option>Carlos Ruiz</option>
                                <option>Ana López</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="processSelect" class="form-label">Proceso</label>
                            <select class="form-select" id="processSelect">
                                <option selected>Seleccionar proceso</option>
                                <option>Proceso X</option>
                                <option>Proceso Y</option>
                                <option>Proceso Z</option>
                                <option>Proceso W</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nivel ILU</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="iluLevel" id="iluI" value="I">
                                    <label class="form-check-label" for="iluI">I (No conoce)</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="iluLevel" id="iluU" value="U">
                                    <label class="form-check-label" for="iluU">U (Conoce)</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="iluLevel" id="iluL" value="L">
                                    <label class="form-check-label" for="iluL">L (Capacita)</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="certDate" class="form-label">Fecha certificación</label>
                                <input type="date" class="form-control" id="certDate">
                            </div>
                            <div class="col-md-6">
                                <label for="expiryDate" class="form-label">Fecha vencimiento</label>
                                <input type="date" class="form-control" id="expiryDate">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="score" class="form-label">Calificación</label>
                            <input type="number" class="form-control" id="score" min="0" max="100" placeholder="85/100">
                        </div>
                        <div class="mb-3">
                            <label for="observations" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observations" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="certFile" class="form-label">Documentos</label>
                            <input class="form-control" type="file" id="certFile">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary">Guardar Certificación</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Datos de ejemplo para las estaciones
        const stationsData = {
            'A': [
                { id: 1, operator: 'JP', status: 'occupied', certification: 'Liberado' },
                { id: 2, operator: 'MG', status: 'pending', certification: 'En curso' },
                { id: 3, operator: 'CR', status: 'occupied', certification: 'Liberado' },
                { id: 4, operator: 'AL', status: 'occupied', certification: 'Liberado' },
                { id: 5, operator: '', status: 'vacant', certification: '' },
                { id: 6, operator: 'LF', status: 'occupied', certification: 'Liberado' },
                { id: 7, operator: 'RS', status: 'occupied', certification: 'Liberado' },
                { id: 8, operator: '', status: 'vacant', certification: '' },
                { id: 9, operator: 'JG', status: 'occupied', certification: 'Liberado' },
                { id: 10, operator: 'MP', status: 'occupied', certification: 'Liberado' }
            ],
            'B': [
                { id: 1, operator: 'JP', status: 'occupied', certification: 'Liberado' },
                { id: 2, operator: 'MG', status: 'occupied', certification: 'Liberado' },
                { id: 3, operator: 'CR', status: 'occupied', certification: 'Liberado' },
                { id: 4, operator: 'AL', status: 'occupied', certification: 'Liberado' },
                { id: 5, operator: 'LF', status: 'occupied', certification: 'Liberado' },
                { id: 6, operator: '', status: 'vacant', certification: '' },
                { id: 7, operator: '', status: 'vacant', certification: '' },
                { id: 8, operator: 'RS', status: 'occupied', certification: 'Liberado' },
                { id: 9, operator: 'JG', status: 'occupied', certification: 'Liberado' },
                { id: 10, operator: 'MP', status: 'occupied', certification: 'Liberado' }
            ],
            'C': [
                { id: 1, operator: 'JP', status: 'occupied', certification: 'Liberado' },
                { id: 2, operator: 'MG', status: 'occupied', certification: 'Liberado' },
                { id: 3, operator: 'CR', status: 'occupied', certification: 'Liberado' },
                { id: 4, operator: 'AL', status: 'occupied', certification: 'Liberado' },
                { id: 5, operator: '', status: 'vacant', certification: '' },
                { id: 6, operator: 'LF', status: 'occupied', certification: 'Liberado' },
                { id: 7, operator: '', status: 'vacant', certification: '' },
                { id: 8, operator: 'RS', status: 'occupied', certification: 'Liberado' },
                { id: 9, operator: 'JG', status: 'occupied', certification: 'Liberado' },
                { id: 10, operator: 'MP', status: 'occupied', certification: 'Liberado' }
            ]
        };
        
        // Navegación entre secciones
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Actualizar clase activa en sidebar
                document.querySelectorAll('.sidebar .nav-link').forEach(item => {
                    item.classList.remove('active');
                });
                this.classList.add('active');
                
                // Ocultar todas las secciones
                document.querySelectorAll('.content-section').forEach(section => {
                    section.classList.add('d-none');
                });
                
                // Mostrar sección correspondiente
                const sectionId = this.getAttribute('data-section') + '-section';
                document.getElementById(sectionId).classList.remove('d-none');
                
                // Actualizar título de página
                const pageTitle = document.getElementById('page-title');
                switch(this.getAttribute('data-section')) {
                    case 'dashboard':
                        pageTitle.textContent = 'Dashboard';
                        break;
                    case 'lines':
                        pageTitle.textContent = 'Gestión de Líneas';
                        break;
                    case 'certifications':
                        pageTitle.textContent = 'Certificaciones';
                        break;
                    case 'reports':
                        pageTitle.textContent = 'Reportes';
                        break;
                }
            });
        });
        
        // Navegación a detalle de línea
        document.querySelectorAll('.view-line').forEach(button => {
            button.addEventListener('click', function() {
                const lineId = this.getAttribute('data-line');
                
                // Ocultar sección de líneas
                document.getElementById('lines-section').classList.add('d-none');
                
                // Mostrar sección de detalle de línea
                document.getElementById('line-detail-section').classList.remove('d-none');
                
                // Actualizar título
                document.getElementById('line-detail-title').textContent = `Línea ${lineId} - Detalle`;
                document.getElementById('page-title').textContent = `Línea ${lineId}`;
                
                // Generar estaciones para la línea seleccionada
                generateStations(lineId);
            });
        });
        
        // Volver a líneas desde detalle
        document.querySelector('.back-to-lines').addEventListener('click', function() {
            // Ocultar sección de detalle
            document.getElementById('line-detail-section').classList.add('d-none');
            
            // Mostrar sección de líneas
            document.getElementById('lines-section').classList.remove('d-none');
            
            // Actualizar título
            document.getElementById('page-title').textContent = 'Gestión de Líneas';
        });
        
        // Función para generar estaciones
        function generateStations(lineId) {
            const stationsContainer = document.getElementById('stations-container');
            stationsContainer.innerHTML = '';
            
            const stations = stationsData[lineId];
            
            stations.forEach(station => {
                const stationElement = document.createElement('div');
                stationElement.className = `station-box ${station.status === 'occupied' ? 'station-occupied' : 
                                          station.status === 'pending' ? 'station-pending' : 'station-vacant'}`;
                stationElement.innerHTML = `
                    <div class="fw-bold">${station.id}</div>
                    <div>${station.operator}</div>
                `;
                
                stationElement.addEventListener('click', function() {
                    showStationInfo(station, lineId);
                });
                
                stationsContainer.appendChild(stationElement);
            });
        }
        
        // Mostrar información de estación seleccionada
        function showStationInfo(station, lineId) {
            const stationInfo = document.getElementById('station-info');
            
            let operatorName = '';
            switch(station.operator) {
                case 'JP': operatorName = 'Juan Pérez'; break;
                case 'MG': operatorName = 'María González'; break;
                case 'CR': operatorName = 'Carlos Ruiz'; break;
                case 'AL': operatorName = 'Ana López'; break;
                case 'LF': operatorName = 'Luis Fernández'; break;
                case 'RS': operatorName = 'Roberto Sánchez'; break;
                case 'JG': operatorName = 'Jorge Gómez'; break;
                case 'MP': operatorName = 'Marta Paredes'; break;
                default: operatorName = 'Vacante';
            }
            
            let statusBadge = '';
            if (station.status === 'occupied') {
                statusBadge = '<span class="badge bg-success">Ocupada</span>';
            } else if (station.status === 'pending') {
                statusBadge = '<span class="badge bg-warning">En punto de cambio</span>';
            } else {
                statusBadge = '<span class="badge bg-secondary">Vacante</span>';
            }
            
            stationInfo.innerHTML = `
                <h6>Estación: ${station.id}</h6>
                <p><strong>Operador:</strong> ${operatorName}</p>
                <p><strong>Estado:</strong> ${statusBadge}</p>
                <p><strong>Certificación:</strong> ${station.certification || 'No aplica'}</p>
                <div class="d-grid gap-2 d-md-flex mt-3">
                    <button class="btn btn-sm btn-outline-primary action-btn">Ver Detalles</button>
                    <button class="btn btn-sm btn-outline-warning action-btn">Registrar Incidencia</button>
                    <button class="btn btn-sm btn-outline-info action-btn">Punto de Cambio</button>
                </div>
            `;
        }
        
        // Inicializar la página mostrando el dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Por defecto, mostrar el dashboard
            document.getElementById('dashboard-section').classList.remove('d-none');
            
            // Inicializar tooltips de Bootstrap
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        /*
            Lorem ipsum dolor, sit amet consectetur adipisicing elit. Ut ratione quos officiis cumque suscipit ab necessitatibus, corporis eum voluptatem dolorem placeat eligendi et, veritatis, ex sint illum similique blanditiis labore.
            Perspiciatis minus maiores dicta soluta, numquam tempora mollitia nisi repellendus consequatur dolores illo culpa ipsum, aut praesentium temporibus, explicabo error totam quisquam?
            Blanditiis nesciunt qui hic quia veniam reprehenderit totam commodi nulla perferendis assumenda doloremque, quasi ab, molestias cumque. Vitae, tempore? Quasi, eveniet quas.
            Sapiente, ad omnis, praesentium fugiat aliquid facere exercitationem natus dolorem minima impedit similique architecto beatae soluta nemo. Molestiae reiciendis ad vitae sint.
            Quam, porro fugiat? Quibusdam illum, ad officia sequi sit adipisci nihil reprehenderit necessitatibus quod saepe voluptatum, quidem qui maxime eaque voluptate quae?
            Voluptate cum perferendis fugit reiciendis facilis, in eveniet quibusdam non maxime saepe veniam quos qui ducimus numquam cumque molestiae vel quas facere!
            Dolore, a ducimus voluptatum quia corporis quasi praesentium quod, quaerat enim hic in dolorum, eum nam est minus laudantium. Unde, hic animi.
            Magni corrupti nulla earum assumenda iste exercitationem quasi sapiente architecto quae vero explicabo, dolore, maiores necessitatibus quo a fugit aspernatur cumque expedita?
            Odit, doloremque aspernatur. Quas quasi ea, aspernatur amet dolor, reprehenderit autem accusamus enim dolorem voluptatum vero voluptate voluptatibus reiciendis obcaecati? Minus, pariatur?
            Consequuntur, blanditiis! Unde itaque, accusamus eum laboriosam obcaecati magni nulla ratione fugit consequuntur, enim esse. Eligendi atque temporibus rem rerum ad iure.
            Ducimus ipsam laudantium possimus ad illo molestiae deleniti eveniet hic placeat, optio sequi ab necessitatibus maiores dolores explicabo animi esse dolorum voluptatibus.
        */
    </script>
</body>
</html>