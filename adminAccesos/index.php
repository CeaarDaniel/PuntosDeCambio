<?php
/**
 * index.php — Punto de entrada único de la aplicación.
 *
 * Cada pantalla se abre como index.php?p=<clave>, con recarga normal. La clave
 * solo se busca en el catálogo de includes/paginas.php: nunca se usa para
 * armar una ruta, así que no se puede incluir un archivo arbitrario.
 *
 * PENDIENTE (PENDIENTES.md §3): al definir los permisos, aquí se agregará
 * exigirAcceso() con el módulo que protege cada pantalla.
 */
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/paginas.php';

exigirSesionVista();

/* Las vistas de pages/ comprueban esta constante para no poder abrirse
   directamente desde el navegador. */
define('EN_APLICACION', true);

$claveSolicitada = isset($_GET['p']) && is_string($_GET['p']) ? $_GET['p'] : 'dashboard';
$paginaActual = $PAGINAS[$claveSolicitada] ?? null;
$claveActual = $paginaActual !== null ? $claveSolicitada : '';

if ($paginaActual === null) {
    http_response_code(404);
}

$tituloPestana = $paginaActual !== null ? $paginaActual['titulo'] . ' | ' . APP_NOMBRE : 'Página no encontrada | ' . APP_NOMBRE;
$cargarRecursos = $paginaActual !== null && $paginaActual['disponible'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Administración de usuarios, aplicaciones, roles y permisos.">
    <!-- Token CSRF: assets/js/app.js lo envía en cada petición AJAX. -->
    <meta name="csrf-token" content="<?php echo escapar(tokenCsrf()); ?>">
    <title><?php echo escapar($tituloPestana); ?></title>

    <?php include_once __DIR__ . '/includes/favicon.php'; ?>

    <!-- ==========================================================
         HOJAS DE ESTILO
         Orden obligatorio: librerías primero, luego el CSS general del
         proyecto y al final el CSS específico de la página, para que cada
         nivel pueda sobrescribir al anterior sin usar !important.
         ========================================================== -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/DataTables/datatables.min.css">
    <link rel="stylesheet" href="css/style.css?v=6">
    <?php if ($cargarRecursos && $paginaActual['css']) : ?>
        <link rel="stylesheet" href="css/<?php echo escapar($claveActual); ?>.css?v=<?php echo escapar($paginaActual['version']); ?>">
    <?php endif; ?>
</head>
<body>

<div class="app-shell">

    <?php require_once __DIR__ . '/includes/header.php'; ?>
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="app-main" id="contenido">
        <?php
        if ($paginaActual === null) {
            $avisoIcono = 'bi-signpost-split';
            $avisoTitulo = 'Página no encontrada';
            $avisoTexto = 'La pantalla que buscas no existe. Usa el menú para elegir otra.';
            require __DIR__ . '/includes/avisoPagina.php';
        } elseif (!$paginaActual['disponible']) {
            ?>
            <div class="page-header">
                <div>
                    <h1><?php echo escapar($paginaActual['titulo']); ?></h1>
                    <p class="page-subtitle"><?php echo escapar($paginaActual['descripcion']); ?></p>
                </div>
            </div>
            <?php
            // Aviso propio de la pantalla (si lo tiene) o el genérico de "en construcción".
            $aviso = $paginaActual['aviso'] ?? [
                'icono'  => 'bi-cone-striped',
                'titulo' => 'Pantalla en construcción',
                'texto'  => 'Esta sección todavía se está desarrollando y estará disponible pronto.',
            ];
            $avisoIcono = $aviso['icono'];
            $avisoTitulo = $aviso['titulo'];
            $avisoTexto = $aviso['texto'];
            require __DIR__ . '/includes/avisoPagina.php';
        } else {
            require __DIR__ . '/pages/' . $claveActual . '.php';
        }
        ?>
    </main>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

</div>

<?php require_once __DIR__ . '/includes/toast.php'; ?>
<?php require_once __DIR__ . '/includes/modalConfirmar.php'; ?>

<!-- ==============================================================
     SCRIPTS
     Orden obligatorio: jQuery antes que todo lo que dependa de él,
     Bootstrap antes que DataTables, y el script de la página al final.
     ============================================================== -->
<script src="assets/js/jquery-3.7.1.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/DataTables/datatables.min.js"></script>
<!-- Utilidades compartidas (toasts, peticiones, CSRF). Antes del script de la página. -->
<script src="assets/js/app.js?v=5"></script>
<?php if ($cargarRecursos && $paginaActual['js']) : ?>
    <script src="assets/js/<?php echo escapar($claveActual); ?>.js?v=<?php echo escapar($paginaActual['version']); ?>"></script>
<?php endif; ?>

</body>
</html>
