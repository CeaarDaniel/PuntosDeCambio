<?php
/**
 * pages/dashboard.php — Vista de inicio.
 *
 * Solo el marcado. Los datos llegan por AJAX desde
 * api/operacionesDashboard.php (opcion 1) y los pinta assets/js/dashboard.js.
 * Se incluye desde index.php, que ya validó la sesión.
 */
if (!defined('EN_APLICACION')) {
    http_response_code(404);
    exit;
}

/* Accesos rápidos a las operaciones principales. Apuntan a pantallas del
   catálogo (includes/paginas.php), así que nunca llevan a una URL inexistente. */
$accesosRapidos = [
    ['pagina' => 'usuarios',          'icono' => 'bi-person-plus',   'texto' => 'Crear o editar usuarios'],
    ['pagina' => 'rolesUsuario',      'icono' => 'bi-person-check',  'texto' => 'Asignar roles a un usuario'],
    ['pagina' => 'permisosUsuario',   'icono' => 'bi-person-lock',   'texto' => 'Conceder o denegar un permiso'],
    ['pagina' => 'permisosEfectivos', 'icono' => 'bi-shield-check',  'texto' => 'Consultar permisos efectivos'],
    ['pagina' => 'roles',             'icono' => 'bi-person-badge',  'texto' => 'Administrar roles'],
    ['pagina' => 'aplicaciones',      'icono' => 'bi-grid-1x2',      'texto' => 'Registrar una aplicación'],
];

/* Tarjetas de totales. 'valor' y 'detalle' son los campos de la respuesta
   que dashboard.js escribe en cada una. */
$tarjetas = [
    ['id' => 'usuariosActivos',     'icono' => 'bi-person-check', 'etiqueta' => 'Usuarios activos',     'variante' => 'exito'],
    ['id' => 'usuariosInactivos',   'icono' => 'bi-person-dash',  'etiqueta' => 'Usuarios inactivos',   'variante' => 'neutro'],
    ['id' => 'aplicacionesActivas', 'icono' => 'bi-grid-1x2',     'etiqueta' => 'Aplicaciones activas', 'variante' => 'primario'],
    ['id' => 'modulosTotal',        'icono' => 'bi-window-stack', 'etiqueta' => 'Módulos registrados',  'variante' => 'primario'],
    ['id' => 'rolesTotal',          'icono' => 'bi-person-badge', 'etiqueta' => 'Roles registrados',    'variante' => 'primario'],
    ['id' => 'permisosTotal',       'icono' => 'bi-key',          'etiqueta' => 'Permisos registrados', 'variante' => 'primario'],
];
?>
<div class="page-header">
    <div>
        <h1>Hola, <?php echo escapar(nombreEnSesion()); ?></h1>
        <p class="page-subtitle">Resumen general de usuarios, aplicaciones, roles y permisos.</p>
    </div>
    <div class="page-header-actions">
        <button class="btn btn-soft" type="button" id="dashboardActualizar">
            <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>Actualizar
        </button>
    </div>
</div>

<!-- ==============================================================
     Totales
     ============================================================== -->
<section class="row g-3 mb-4" aria-label="Totales">
    <?php foreach ($tarjetas as $tarjeta) : ?>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="stat-card dashboard-stat dashboard-stat-<?php echo escapar($tarjeta['variante']); ?>">
                <div class="stat-icon" aria-hidden="true">
                    <i class="bi <?php echo escapar($tarjeta['icono']); ?>"></i>
                </div>
                <div class="dashboard-stat-texto">
                    <div class="stat-value" id="total-<?php echo escapar($tarjeta['id']); ?>">
                        <span class="placeholder col-6 rounded" aria-hidden="true"></span>
                        <span class="visually-hidden">Cargando…</span>
                    </div>
                    <div class="stat-label"><?php echo escapar($tarjeta['etiqueta']); ?></div>
                    <div class="dashboard-stat-detalle" id="detalle-<?php echo escapar($tarjeta['id']); ?>"></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<!-- ==============================================================
     Accesos rápidos
     ============================================================== -->
<section class="card mb-4" aria-labelledby="tituloAccesos">
    <div class="card-header" id="tituloAccesos">
        <i class="bi bi-lightning-charge me-1" aria-hidden="true"></i>Accesos rápidos
    </div>
    <div class="card-body">
        <div class="dashboard-accesos">
            <?php foreach ($accesosRapidos as $acceso) : ?>
                <a class="dashboard-acceso" href="<?php echo escapar(urlPagina($acceso['pagina'])); ?>">
                    <span class="dashboard-acceso-icono" aria-hidden="true">
                        <i class="bi <?php echo escapar($acceso['icono']); ?>"></i>
                    </span>
                    <span><?php echo escapar($acceso['texto']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="row g-4">

    <!-- ==============================================================
         Excepciones próximas a vencer
         ============================================================== -->
    <div class="col-12 col-xl-7">
        <section class="card h-100 tabla-contenida" aria-labelledby="tituloPorVencer">
            <div class="card-header d-flex align-items-center justify-content-between gap-2" id="tituloPorVencer">
                <span>
                    <i class="bi bi-hourglass-split me-1" aria-hidden="true"></i>
                    Permisos individuales por vencer
                    <small class="text-muted-soft fw-normal" id="porVencerDias"></small>
                </span>
                <span class="badge badge-estado-neutro" id="porVencerTotal"></span>
            </div>
            <div class="card-body" id="porVencerCuerpo">
                <div class="app-loader">
                    <div class="spinner-border" role="status"><span class="visually-hidden">Cargando…</span></div>
                    <div>Cargando…</div>
                </div>
            </div>
        </section>
    </div>

    <!-- ==============================================================
         Usuarios sin roles ni permisos
         ============================================================== -->
    <div class="col-12 col-xl-5">
        <section class="card h-100" aria-labelledby="tituloSinAcceso">
            <div class="card-header d-flex align-items-center justify-content-between gap-2" id="tituloSinAcceso">
                <span>
                    <i class="bi bi-person-x me-1" aria-hidden="true"></i>
                    Usuarios sin roles ni permisos
                </span>
                <span class="badge badge-estado-neutro" id="sinAccesoTotal"></span>
            </div>
            <div class="card-body" id="sinAccesoCuerpo">
                <div class="app-loader">
                    <div class="spinner-border" role="status"><span class="visually-hidden">Cargando…</span></div>
                    <div>Cargando…</div>
                </div>
            </div>
        </section>
    </div>

    <!-- ==============================================================
         Posibles inconsistencias
         ============================================================== -->
    <div class="col-12">
        <section class="card" aria-labelledby="tituloInconsistencias">
            <div class="card-header d-flex align-items-center justify-content-between gap-2" id="tituloInconsistencias">
                <span>
                    <i class="bi bi-clipboard-pulse me-1" aria-hidden="true"></i>
                    Posibles inconsistencias
                </span>
                <span class="badge badge-estado-neutro" id="inconsistenciasTotal"></span>
            </div>
            <div class="card-body" id="inconsistenciasCuerpo">
                <div class="app-loader">
                    <div class="spinner-border" role="status"><span class="visually-hidden">Cargando…</span></div>
                    <div>Revisando la información…</div>
                </div>
            </div>
        </section>
    </div>

</div>
