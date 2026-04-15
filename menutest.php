<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Menú Lateral OcUltable + Flotante Responsivo</title>
    <!-- Bootstrap 5 CSS + Icons + JS Bundle (opcional para toggles, usaremos propio) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f4f7fc;
            font-family: 'Segoe UI', system-ui, -apple-system, 'Roboto', sans-serif;
            overflow-x: hidden;
        }

        /* ========== LAYOUT PRINCIPAL ========== */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
            position: relative;
        }

        /* --- SIDEBAR (menú lateral) --- */
        /* Estilos BASE para desktop (>=500px) */
        .sidebar {
            background: linear-gradient(145deg, #1e2a3a 0%, #0f1a24 100%);
            color: #ecf0f1;
            width: 280px;
            transition: width 0.3s ease-in-out, transform 0.3s ease-in-out;
            overflow-x: hidden;
            overflow-y: auto;
            flex-shrink: 0;
            z-index: 1000;
            box-shadow: 2px 0 12px rgba(0, 0, 0, 0.08);
        }

        /* Contenido interno del sidebar (scroll) */
        .sidebar-inner {
            padding: 1.5rem 1rem 2rem 1rem;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* Estilo del botón cerrar dentro del menú */
        .close-sidebar-btn {
            align-self: flex-end;
            background: rgba(255,255,255,0.15);
            border: none;
            color: white;
            border-radius: 40px;
            padding: 6px 14px;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            transition: 0.2s;
        }
        .close-sidebar-btn:hover {
            background: #e74c3c;
            transform: scale(0.97);
        }

        .sidebar .nav-link {
            color: #d1e0eb;
            font-weight: 500;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 6px;
            transition: all 0.2s;
        }
        .sidebar .nav-link i {
            margin-right: 12px;
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }
        .sidebar .nav-link:hover {
            background: #2c3e50;
            color: white;
            transform: translateX(5px);
        }
        .sidebar .nav-link.active {
            background: #2c81ba;
            color: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        /* MAIN CONTENT */
        .main-content {
            flex: 1;
            transition: margin-left 0.3s ease, width 0.3s ease;
            background: #ffffff;
            padding: 20px 28px;
            min-height: 100vh;
            width: calc(100% - 280px);
        }

        /* ----- MODO ESCRITORIO (>=500px) - SIDEBAR COLABSABLE (desplaza contenido) ----- */
        /* Cuando el sidebar está oculto en desktop */
        .sidebar.collapsed-desktop {
            width: 0 !important;
        }
        /* Ocultar contenido interno cuando está colapsado */
        .sidebar.collapsed-desktop .sidebar-inner {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s;
        }
        /* Si el sidebar está colapsado, el main se expande (sin margin extra porque flex ya lo hace) */
        
        /* ========== MEDIA QUERY: MÓVIL / PANTALLAS < 500px ========== */
        @media (max-width: 499px) {
            .app-wrapper {
                position: relative;
                display: block;  /* Cambiamos a bloque para que sidebar flote */
            }
            /* Sidebar en modo flotante (overlay) */
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                width: 280px !important;   /* ancho fijo en móvil */
                transform: translateX(-100%);
                transition: transform 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
                box-shadow: 4px 0 20px rgba(0,0,0,0.3);
                z-index: 1050;
                border-radius: 0 12px 12px 0;
            }
            /* Clase para mostrar el menú (overlay) */
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            /* Ocultamos el contenido interno por defecto, se ve cuando está abierto */
            .sidebar .sidebar-inner {
                opacity: 1;
                visibility: visible;
            }
            /* Main content ya NO se desplaza, ocupa todo el ancho sin margin */
            .main-content {
                width: 100%;
                margin-left: 0 !important;
                padding: 20px 18px;
            }
            /* Ajuste para que el contenido principal no quede tapado por el sidebar abierto (pero es overlay) */
            body.menu-open-mobile {
                overflow: hidden; /* evita scroll detrás, opcional */
            }
        }

        /* ===== ICONO FLOTANTE (aparece cuando menú oculto) ===== */
        .floating-menu-btn {
            position: fixed;
            bottom: 25px;
            left: 25px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #1e88e5;
            color: white;
            border: none;
            box-shadow: 0 6px 14px rgba(0,0,0,0.25);
            font-size: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            z-index: 1100;
            backdrop-filter: blur(2px);
        }
        .floating-menu-btn:hover {
            background: #0b5e8a;
            transform: scale(1.05);
            box-shadow: 0 8px 18px rgba(0,0,0,0.3);
        }
        /* Oculto por defecto (lo manejamos con JS) */
        .floating-menu-btn.hidden-float {
            display: none;
        }

        /* Transiciones suaves */
        .main-content, .sidebar {
            transition: all 0.3s ease;
        }

        /* utilidades */
        .card-custom {
            border-radius: 20px;
            border: none;
            box-shadow: 0 5px 12px rgba(0,0,0,0.05);
        }
        h1 {
            font-weight: 600;
            color: #1f2d3d;
        }
        @media (max-width: 499px) {
            .main-content {
                padding: 18px 16px;
            }
        }
    </style>
</head>
<body>

<div class="app-wrapper" id="appWrapper">
    <!-- SIDEBAR (menú lateral izquierdo) -->
    <aside class="sidebar" id="mainSidebar">
        <div class="sidebar-inner">
            <button class="close-sidebar-btn" id="closeSidebarBtn">
                <i class="bi bi-x-lg"></i> Ocultar menú
            </button>
            <h5 class="text-white mb-3 ps-2"><i class="bi bi-grid-3x3-gap-fill"></i> Navegación</h5>
            <hr class="bg-light opacity-25">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="#" class="nav-link active">
                        <i class="bi bi-house-door-fill"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-bar-chart-fill"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-person-circle"></i> Perfil
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-gear-fill"></i> Configuración
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-envelope-fill"></i> Mensajes
                    </a>
                </li>
            </ul>
            <hr class="bg-light opacity-25 mt-4">
            <div class="mt-auto small text-white-50 text-center pt-4">
                <i class="bi bi-layout-sidebar"></i> Menú lateral<br>
                v1.0
            </div>
        </div>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main-content" id="mainContent">
        <div class="container-fluid px-0">
            <div class="d-flex align-items-center mb-4 flex-wrap">
                <h1 class="me-auto">Panel de control</h1>
                <span class="badge bg-info text-dark px-3 py-2"><i class="bi bi-phone"></i> <span id="viewportBadge">Escritorio</span></span>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card card-custom p-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="bi bi-people fs-3 text-primary"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Usuarios activos</h6>
                                <h3 class="mb-0">1,284</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card card-custom p-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="bi bi-graph-up fs-3 text-success"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Ventas</h6>
                                <h3 class="mb-0">$45.2K</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12">
                    <div class="card card-custom p-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="bi bi-bell fs-3 text-warning"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Notificaciones</h6>
                                <h3 class="mb-0">8 nuevas</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-custom mt-4 p-4">
                <h4><i class="bi bi-info-circle-fill me-2"></i> Contenido principal</h4>
                <p>Este es un ejemplo de contenido responsivo. El menú lateral izquierdo se puede ocultar y mostrar mediante el botón flotante (ícono). En pantallas grandes (>=500px), el menú <strong>desplaza el contenido</strong> hacia la derecha cuando está visible, y al ocultarlo el contenido se expande completamente.</p>
                <p>En pantallas <strong>menores a 500px</strong>, el menú se comporta de forma <strong>flotante</strong> (superpuesto) y <strong>desaparece automáticamente al quitar el mouse del menú lateral</strong>. Además, mantiene la misma lógica: el ícono flotante aparece cuando el menú está oculto, y al presionarlo muestra el menú y desaparece el ícono.</p>
                <div class="alert alert-secondary mt-2">
                    <i class="bi bi-mouse2-fill"></i> <strong>Prueba:</strong> En móvil (500px)
                </div>
            </div>
        </div>
    </main>
</div>

<!-- ICONO FLOTANTE (aparece cuando menú oculto) -->
<button class="floating-menu-btn" id="floatingMenuBtn">
    <i class="bi bi-list"></i>
</button>

<script>
    // -------- ELEMENTOS DOM ----------
    const sidebar = document.getElementById('mainSidebar');
    const closeBtn = document.getElementById('closeSidebarBtn');
    const floatingBtn = document.getElementById('floatingMenuBtn');
    const mainContent = document.getElementById('mainContent');
    const viewportBadge = document.getElementById('viewportBadge');

    // Estados
    let isMenuVisible = true;     // true = menú visible, false = menú oculto
    let currentMode = 'desktop';  // 'desktop' (>=500px) o 'mobile' (<500px)
    let mouseLeaveHandler = null;  // referencia al eventListener para móvil

    // ---------- FUNCIONES AUX ----------
    function getWindowMode() {
        return window.innerWidth < 500 ? 'mobile' : 'desktop';
    }

    // Sincroniza la UI (clases y estilos) según modo actual y estado isMenuVisible
    function syncUI() {
        // 1. Aplicar clases según modo y estado de visibilidad
        if (currentMode === 'desktop') {
            // --- MODO ESCRITORIO: usamos width colapsable y desplazamiento vía flex ---
            // Removemos posibles clases móviles
            sidebar.classList.remove('mobile-open');
            // Aseguramos que sidebar tenga transición width
            if (isMenuVisible) {
                sidebar.classList.remove('collapsed-desktop');
                // Aseguramos que el main no tenga margin extra (flex ya lo hace)
                mainContent.style.marginLeft = '';
            } else {
                sidebar.classList.add('collapsed-desktop');
                mainContent.style.marginLeft = '';
            }
            // Para modo escritorio el ícono flotante se muestra SOLO si menú oculto
            if (!isMenuVisible) {
                floatingBtn.classList.remove('hidden-float');
            } else {
                floatingBtn.classList.add('hidden-float');
            }
            // Eliminar cualquier eventListener de mouseleave (modo escritorio no necesita hover out)
      
        } 
        else {  
            // --- MODO MÓVIL (<500px): menú flotante overlay, sin desplazar contenido ---
            // Removemos clase collapsed-desktop si existe (por si acaso)
            sidebar.classList.remove('collapsed-desktop');
            // Controlamos clase mobile-open según visibilidad
            if (isMenuVisible) {
                sidebar.classList.add('mobile-open');
                // Bloquear scroll opcional
                document.body.classList.add('menu-open-mobile');
                // Agregar listener para mouseleave SOLO cuando menú visible en móvil
             
            } else {
                sidebar.classList.remove('mobile-open');
                document.body.classList.remove('menu-open-mobile');
                // Remover listener hover si estaba
            
            }
            // Mostrar/ocultar botón flotante: si menú oculto -> aparece botón
            if (!isMenuVisible) {
                floatingBtn.classList.remove('hidden-float');
            } else {
                floatingBtn.classList.add('hidden-float');
            }
            // En móvil el main siempre tiene ancho completo, no se modifica margin
            mainContent.style.marginLeft = '';
        }
        // Ajuste extra para asegurar que el contenido no tenga estilos residuales
    }

    // Función central para cambiar estado visible/oculto (true = mostrar menú, false = ocultar)
    function setMenuVisibility(visible) {
        if (isMenuVisible === visible) return; // no cambio necesario
        
        isMenuVisible = visible;
        
        // Aplicar cambios visuales según modo actual
        if (currentMode === 'desktop') {
            if (isMenuVisible) {
                sidebar.classList.remove('collapsed-desktop');
                // El main se expande automáticamente por flex
            } else {
                sidebar.classList.add('collapsed-desktop');
            }
            // Control ícono flotante
            if (!isMenuVisible) {
                floatingBtn.classList.remove('hidden-float');
            } else {
                floatingBtn.classList.add('hidden-float');
            }
        } 
        else { // móvil
            if (isMenuVisible) {
                sidebar.classList.add('mobile-open');
                document.body.classList.add('menu-open-mobile');
            } else {
                sidebar.classList.remove('mobile-open');
                document.body.classList.remove('menu-open-mobile');
           
            }
            // Ícono flotante
            if (!isMenuVisible) {
                floatingBtn.classList.remove('hidden-float');
            } else {
                floatingBtn.classList.add('hidden-float');
            }
        }
    }

    // Manejador cuando cambia el tamaño de ventana (responsivo total)
    function handleResize() {
        const newMode = getWindowMode();
        const oldMode = currentMode;
        currentMode = newMode;
        
        // Guardamos estado previo de visibilidad para re-aplicar correctamente
        const wasVisible = isMenuVisible;
        
        // IMPORTANTE: Al cambiar entre modos, debemos resetear clases específicas de cada modo
        // para evitar conflictos y mantener la coherencia del estado.
        if (oldMode !== newMode) {
            // Limpiar todas las clases específicas de modo anterior
            if (oldMode === 'desktop') { 
                sidebar.classList.remove('collapsed-desktop');
                // Remover cualquier estilo inline
                mainContent.style.marginLeft = '';
            } else {
                sidebar.classList.remove('mobile-open');
      
                document.body.classList.remove('menu-open-mobile');
            }
            
            // Ahora configuramos para el nuevo modo según el estado actual (wasVisible)
            if (newMode === 'desktop') {
                // Al pasar a escritorio, si el menú estaba visible, debe mostrarse con ancho normal.
                // Si estaba oculto, debe colapsarse.
                if (wasVisible) {
                    sidebar.classList.remove('collapsed-desktop');
                } else {
                    sidebar.classList.add('collapsed-desktop');
                }
                // Aseguramos que el ícono flotante se muestre según visibilidad
                if (!wasVisible) {
                    floatingBtn.classList.remove('hidden-float');
                } else {
                    floatingBtn.classList.add('hidden-float');
                }
                // Remover cualquier residuo móvil
                sidebar.classList.remove('mobile-open');
            } 
            else { // nuevo modo móvil
                if (wasVisible) {
                    sidebar.classList.add('mobile-open');
                    document.body.classList.add('menu-open-mobile');
               
                } else {
                    sidebar.classList.remove('mobile-open');
        
                }
                if (!wasVisible) {
                    floatingBtn.classList.remove('hidden-float');
                } else {
                    floatingBtn.classList.add('hidden-float');
                }
                // eliminamos collapsed-desktop si existe
                sidebar.classList.remove('collapsed-desktop');
            }
            return;
        }
        
        // Mismo modo pero puede haber cambios en tamaño dentro del mismo rango (p.ej 300px a 400px)
        // Solo resincronizamos para evitar comportamientos extraños
        if (currentMode === 'desktop') {
            // En desktop aseguramos que las clases estén acordes con isMenuVisible
            if (isMenuVisible) {
                sidebar.classList.remove('collapsed-desktop');
            } else {
                sidebar.classList.add('collapsed-desktop');
            }
            if (!isMenuVisible) {
                floatingBtn.classList.remove('hidden-float');
            } else {
                floatingBtn.classList.add('hidden-float');
            }
            // Eliminamos posibles restos de móvil
            sidebar.classList.remove('mobile-open');
         
            document.body.classList.remove('menu-open-mobile');
        } 
        else {
            // Modo móvil
            if (isMenuVisible) {
                sidebar.classList.add('mobile-open');
             
                document.body.classList.add('menu-open-mobile');
            } else {
                sidebar.classList.remove('mobile-open');
              
                document.body.classList.remove('menu-open-mobile');
            }
            if (!isMenuVisible) {
                floatingBtn.classList.remove('hidden-float');
            } else {
                floatingBtn.classList.add('hidden-float');
            }
            sidebar.classList.remove('collapsed-desktop');
        }
    }

    // Inicialización y eventos de redimensionamiento
    function init() {
        currentMode = getWindowMode();
        
        // Sincronizar interfaz
        syncUI();

        // Evento para cerrar menú (botón interno)
        closeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            setMenuVisibility(false);
        });
        
        // Evento para botón flotante: mostrar el menú
        floatingBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            setMenuVisibility(true);
        });
        
        // Evento resize con debounce simple para rendimiento
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                handleResize();
            }, 100);
        });
        
        // También reaccionar a cambios de orientación o redimensionamiento inicial
        handleResize(); // llamada final para asegurar consistencia
    }
    
    // Arrancar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', init);
</script>

<!-- Bootstrap JS Bundle (opcional para algunos componentes, pero lo incluimos por compatibilidad) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>