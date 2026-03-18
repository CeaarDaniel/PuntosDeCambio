<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Líneas · Dashboard</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Font Awesome 6 (Free) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="../css/style.css">

    <!-- CSS personalizado mínimo: solo lo necesario para el estilo moderno -->
    <style>
        /* ---------------------------------------------
           VARIABLES Y CONFIGURACIÓN GENERAL
           - Colores suaves y profesionales
           - Degradados sutiles
        --------------------------------------------- */
        :root {
            --primary-soft: #3a5a78;
            --secondary-soft: #6c9bcf;
            --accent-soft: #e86c5c;
            --light-bg: #fafbfc;
            --card-hover-shadow: 0 12px 20px rgba(0,0,0,0.04);
            --transition-base: all 0.2s ease;
        }

        /* Fondo general muy suave */
        body {
            background-color: var(--light-bg);
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
        }

        /* Degradado sutil para cabeceras de tarjetas/modales */
        .bg-gradient-soft {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
        }

        /* Botón con gradiente acento (flotante o cualquier uso) */
        .btn-gradient-accent {
            background: linear-gradient(145deg, var(--accent-soft), #d45c4c);
            border: none;
            color: white;
            transition: var(--transition-base);
        }
        .btn-gradient-accent:hover {
            background: linear-gradient(145deg, #d45c4c, var(--accent-soft));
            color: white;
            transform: scale(1.05);
        }

        /* Efecto "hover-lift" para tarjetas: elevarse suavemente */
        .hover-lift {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: var(--card-hover-shadow);
        }

        /* Sidebar personalizada con Bootstrap: se ve fija en desktop y offcanvas en móvil */
        .sidebar-desktop {
            width: 260px;
            background-color: white;
            border-right: 1px solid rgba(0,0,0,0.05);
            box-shadow: 2px 0 10px rgba(0,0,0,0.02);
        }
        @media (max-width: 991.98px) {
            .sidebar-desktop {
                display: none;
            }
        }

        /* Ajuste para el contenido principal */
        .main-content {
            flex: 1;
            min-width: 0; /* evita overflow en flex */
            background-color: var(--light-bg);
        }

        /* Barra superior con sombra suave */
        .top-bar-custom {
            background-color: white;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            border-bottom: 1px solid rgba(0,0,0,0.03);
        }

        /* Transiciones para cambios de página (si se usan) */
        .animacion {
            transition: transform 0.4s ease-in-out;
        }
        .ocultar-mostrar {
            transform: scale(0);
            transition: transform 0.4s ease-in-out;
        }

        /* Pequeños retoques a elementos de Bootstrap */
        .modal-header {
            border-bottom: none;
            padding: 1.2rem 1.5rem;
        }
        .modal-content {
            border: none;
            box-shadow: 0 20px 30px rgba(0,0,0,0.05);
        }
        .form-text {
            color: #6c757d;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>

    <!-- 
        ESTRUCTURA PRINCIPAL: SIDEBAR + MAIN CONTENT
        - Sidebar fija en desktop, oculta en móvil (se activa offcanvas)
        - Botón de menú hamburguesa visible solo en móvil
    -->
    <div class="d-flex position-relative">

        <!-- SIDEBAR DESKTOP (visible en lg en adelante) -->
        <div class="sidebar-desktop vh-100 position-sticky top-0 flex-shrink-0 d-none d-lg-block">
            <div class="d-flex flex-column h-100 p-3">
                <div class="d-flex align-items-center mb-4 pb-2 border-bottom" style="border-color: rgba(0,0,0,0.03) !important;">
                    <i class="fa-solid fa-cubes fs-3 me-2" style="color: var(--primary-soft);"></i>
                    <span class="fs-5 fw-semibold" style="color: var(--primary-soft);">Producción</span>
                </div>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="#" class="nav-link active" aria-current="page" style="background-color: var(--primary-soft); color: white;">
                            <i class="fa-solid fa-industry me-2"></i>
                            Líneas
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a href="#" class="nav-link text-dark" style="background-color: transparent;">
                            <i class="fa-solid fa-arrow-right-arrow-left me-2"></i>
                            Puntos de cambio
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a href="#" class="nav-link text-dark" style="background-color: transparent;">
                            <i class="fa-solid fa-chart-line me-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a href="#" class="nav-link text-dark" style="background-color: transparent;">
                            <i class="fa-solid fa-gear me-2"></i>
                            Configuración
                        </a>
                    </li>
                </ul>
                <div class="mt-auto pt-4 border-top" style="border-color: rgba(0,0,0,0.03) !important;">
                    <div class="d-flex align-items-center">
                        <i class="fa-regular fa-circle-user fs-3 me-2" style="color: var(--secondary-soft);"></i>
                        <div>
                            <div class="fw-semibold small">Admin</div>
                            <div class="text-muted small">admin@planta.com</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- OFF-CANVAS SIDEBAR PARA MÓVIL -->
        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title" id="mobileSidebarLabel">
                    <i class="fa-solid fa-cubes me-2" style="color: var(--primary-soft);"></i>
                    Producción
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
            </div>
            <div class="offcanvas-body p-0">
                <ul class="nav nav-pills flex-column p-3">
                    <li class="nav-item">
                        <a href="#" class="nav-link active" style="background-color: var(--primary-soft); color: white;">
                            <i class="fa-solid fa-industry me-2"></i> Líneas
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a href="#" class="nav-link text-dark">
                            <i class="fa-solid fa-arrow-right-arrow-left me-2"></i> Puntos de cambio
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a href="#" class="nav-link text-dark">
                            <i class="fa-solid fa-chart-line me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item mt-1">
                        <a href="#" class="nav-link text-dark">
                            <i class="fa-solid fa-gear me-2"></i> Configuración
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="main-content d-flex flex-column">

            <!-- BARRA SUPERIOR (TOP BAR) -->
            <div class="top-bar-custom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <!-- Botón menú hamburguesa solo en móvil -->
                    <button class="btn btn-light d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h4 class="fs-4 fw-semibold mb-0" style="color: var(--primary-soft);" id="page-title">
                        Gestión de Líneas
                    </h4>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted small d-none d-sm-inline">
                        <i class="fa-regular fa-user me-1"></i> Admin
                    </span>
                    <i class="bi bi-bell fs-5 text-muted"></i>
                </div>
            </div>

            <!-- ÁREA DE CONTENIDO DINÁMICO (aquí va la vista de líneas) -->
            <div id="content-area" class="p-4 animacion">

                <!-- ********** VISTA: GESTIÓN DE LÍNEAS ********** -->
                <div id="lines">

                    <!-- TÍTULO DE SECCIÓN (sutil) -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-semibold mb-0" style="color: var(--primary-soft);">
                            <i class="fa-solid fa-industry me-2"></i>
                            Líneas de producción
                        </h5>
                        <!-- Podría ir un filtro o algo, pero lo dejamos limpio -->
                    </div>

                    <!-- CONTENEDOR DE TARJETAS DE LÍNEAS (usando Bootstrap cards + hover-lift) -->
                    <div class="row g-4" id="contenedorLineas">
                        <!-- Línea 1 -->
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="card h-100 border-0 shadow-sm hover-lift" data-codigo="CRV23" data-nombre="CRV 23">
                                <div class="card-body text-center p-4">
                                    <i class="fa-solid fa-industry fs-1 mb-3" style="color: var(--secondary-soft);"></i>
                                    <h6 class="card-title fw-semibold mb-0">CRV 23</h6>
                                    <span class="badge bg-light text-dark mt-2 px-3 py-2 rounded-pill">Activa</span>
                                </div>
                            </div>
                        </div>
                        <!-- Línea 2 -->
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="card h-100 border-0 shadow-sm hover-lift" data-codigo="FORD" data-nombre="FORD">
                                <div class="card-body text-center p-4">
                                    <i class="fa-solid fa-industry fs-1 mb-3" style="color: var(--secondary-soft);"></i>
                                    <h6 class="card-title fw-semibold mb-0">FORD</h6>
                                    <span class="badge bg-light text-dark mt-2 px-3 py-2 rounded-pill">Activa</span>
                                </div>
                            </div>
                        </div>
                        <!-- Línea 3 -->
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="card h-100 border-0 shadow-sm hover-lift" data-codigo="ODYSSEY" data-nombre="ODYSSEY">
                                <div class="card-body text-center p-4">
                                    <i class="fa-solid fa-industry fs-1 mb-3" style="color: var(--secondary-soft);"></i>
                                    <h6 class="card-title fw-semibold mb-0">ODYSSEY</h6>
                                    <span class="badge bg-light text-dark mt-2 px-3 py-2 rounded-pill">Activa</span>
                                </div>
                            </div>
                        </div>
                        <!-- Línea 4 -->
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="card h-100 border-0 shadow-sm hover-lift" data-codigo="PILOT" data-nombre="PILOT/MDX">
                                <div class="card-body text-center p-4">
                                    <i class="fa-solid fa-industry fs-1 mb-3" style="color: var(--secondary-soft);"></i>
                                    <h6 class="card-title fw-semibold mb-0">PILOT/MDX</h6>
                                    <span class="badge bg-light text-dark mt-2 px-3 py-2 rounded-pill">Activa</span>
                                </div>
                            </div>
                        </div>
                        <!-- Línea 5 -->
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="card h-100 border-0 shadow-sm hover-lift" data-codigo="Xs2Za4$%&#" data-nombre="PRUEBA">
                                <div class="card-body text-center p-4">
                                    <i class="fa-solid fa-industry fs-1 mb-3" style="color: var(--secondary-soft);"></i>
                                    <h6 class="card-title fw-semibold mb-0">PRUEBA</h6>
                                    <span class="badge bg-warning bg-opacity-25 text-dark mt-2 px-3 py-2 rounded-pill">Prueba</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- FIN TARJETAS DE LÍNEAS -->

                    <!-- BOTÓN FLOTANTE (con gradiente y sombra) -->
                    <button class="btn btn-gradient-accent rounded-circle shadow-lg position-fixed bottom-0 end-0 m-4 d-flex align-items-center justify-content-center"
                            style="width: 56px; height: 56px; z-index: 1030;"
                            data-bs-toggle="modal" data-bs-target="#modalAgregarLinea">
                        <i class="fa-solid fa-plus fs-4"></i>
                    </button>

                    <!-- ========================================= -->
                    <!-- MODAL PARA AGREGAR LÍNEA (totalmente Bootstrap) -->
                    <!-- ========================================= -->
                    <div class="modal fade" id="modalAgregarLinea" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <!-- Cabecera con degradado sutil -->
                                <div class="modal-header bg-gradient-soft">
                                    <h4 class="modal-title fs-5 fw-semibold" style="color: var(--primary-soft);">
                                        <i class="fa-solid fa-plus-circle me-2" style="color: var(--secondary-soft);"></i>
                                        Agregar nueva línea
                                    </h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <!-- Formulario sin clases personalizadas, solo Bootstrap + utilidades -->
                                    <form id="lineForm">
                                        <!-- Sección: Información Básica -->
                                        <div class="mb-4">
                                            <h5 class="d-flex align-items-center gap-2 fs-6 fw-semibold mb-3" style="color: var(--primary-soft);">
                                                <i class="bi bi-info-circle"></i>
                                                Información Básica
                                            </h5>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="lineCode" class="form-label fw-semibold">
                                                        Código de Línea <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="lineCode" placeholder="Ej: LN-001" required>
                                                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="tooltip" title="Código único para identificar la línea">
                                                            <i class="bi bi-question-circle"></i>
                                                        </button>
                                                    </div>
                                                    <div class="form-text">Usa un código único para identificar esta línea</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="lineName" class="form-label fw-semibold">
                                                        Nombre de la Línea <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" class="form-control" id="lineName" placeholder="Ej: Línea de CRV" required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Sección: Personal a Cargo -->
                                        <div class="mb-4">
                                            <h5 class="d-flex align-items-center gap-2 fs-6 fw-semibold mb-3" style="color: var(--primary-soft);">
                                                <i class="bi bi-person-gear"></i>
                                                Personal a Cargo
                                            </h5>
                                            <label for="supervisorSearch" class="form-label fw-semibold">Encargado/Supervisor</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="supervisorSearch" placeholder="Buscar empleado...">
                                                <button class="btn btn-outline-secondary" type="button">
                                                    <i class="bi bi-search"></i>
                                                </button>
                                            </div>
                                            <div class="form-text">Puedes buscar y asignar un responsable.</div>
                                        </div>

                                        <!-- Sección: Descripción -->
                                        <div class="mb-3">
                                            <h5 class="d-flex align-items-center gap-2 fs-6 fw-semibold mb-3" style="color: var(--primary-soft);">
                                                <i class="bi bi-text-paragraph"></i>
                                                Descripción
                                            </h5>
                                            <label for="lineDescription" class="form-label fw-semibold">Descripción de la Línea</label>
                                            <textarea class="form-control" id="lineDescription" rows="4" placeholder="Describe el propósito, procesos principales y características..."></textarea>
                                            <div class="form-text">Opcional: información adicional sobre la línea.</div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                        <i class="bi bi-x-circle"></i> Cancelar
                                    </button>
                                    <button type="button" class="btn" id="btnGuardarLinea" style="background-color: var(--secondary-soft); color: white; border: none;">
                                        <i class="bi bi-check-circle"></i> Guardar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- FIN MODAL AGREGAR LÍNEA -->
                </div>
                <!-- ********** FIN VISTA DE LÍNEAS ********** -->
            </div>
            <!-- FIN ÁREA DE CONTENIDO -->
        </div>
        <!-- FIN MAIN CONTENT -->
    </div>
    <!-- FIN ESTRUCTURA PRINCIPAL -->

    <!-- Bootstrap JS y Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Inicializar tooltips (solo si los usas) -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>