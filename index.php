<?php
  require_once('./api/sesion.php');

  /* Sin sesión activa no se entrega la aplicación. */
  exigirSesion('login.php');

  $usuarioSesion = usuarioEnSesion();
  $nombreUsuario = $usuarioSesion['nombre'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Puntos de cambio y certificaciones</title>

  <!-- Favicon-->
  <link rel="icon" type="image/png" href="../img/favicon/logoPuntosCambio.png">

  <!-- Bootstrap -->
  <link href="./css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="./css/bootstrap-icons.min.css" />

  <!-- Font Awesome (para íconos) -->
  <link href="./css/all.min.css" rel="stylesheet"> 

  <!--Libreria Jquery --> 
  <script src="./scripts/jquery-3.7.1.min.js"></script>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="./css/style.css">

  <!-- DataTables -->
  <link rel="stylesheet" href="./DataTables/datatables.min.css">
</head>
<body>

<div class="app-container">
    <!-- Barra de navegacion -->
      <?php include('navBar.php')?>
    
    <!-- Main Content Area -->
    <main class="main-content">
      <header class="top-bar">
        <div class="top-bar-start">
          <button type="button" class="top-bar-menu" id="btnOpenMobileSidebar"
                  aria-label="Abrir menú de navegación" aria-controls="mainSidebar"
                  aria-expanded="false">
            <i class="bi bi-list" aria-hidden="true"></i>
          </button>

          <div class="page-heading">
            <span class="page-heading-icon">
              <i class="bi bi-grid-3x3-gap"></i>
            </span>
            <div>
              <span class="page-eyebrow">Panel de operación</span>
              <h1 id="page-title" class="page-title">Dashboard</h1>
            </div>
          </div>
        </div>

        <!-- Usuario de la sesión actual -->
        <div class="dropdown">
          <button type="button" class="top-bar-status" id="menuUsuario"
                  data-bs-toggle="dropdown" aria-expanded="false"
                  title="<?php echo htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8'); ?>">
            <span class="status-dot"></span>
            <span class="top-bar-user-name"><?php echo htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8'); ?></span>
            <i class="bi bi-chevron-down top-bar-user-caret" aria-hidden="true"></i>
          </button>

          <ul class="dropdown-menu dropdown-menu-end user-menu" aria-labelledby="menuUsuario">
            <!-- Mi perfil (pages/perfil.php): no requiere permisos, solo sesión -->
            <li>
              <a class="dropdown-item" href="#/perfil">
                <i class="bi bi-key" aria-hidden="true"></i>
                Cambiar contrase&ntilde;a
              </a>
            </li>
            <li>
              <button type="button" class="dropdown-item user-menu-exit" id="btnCerrarSesion">
                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                Cerrar sesi&oacute;n
              </button>
            </li>
          </ul>
        </div>
      </header>
      
      <section id="content-area" class="content-area animacion">
        <?php include('./pages/menuLineas.php') ?>
      </section>
    </main>
</div>

  <!-- Bootstrap JS -->
  <script src="./scripts/bootstrap.bundle.min.js"></script>

  <!-- DataTables JS -->
  <script src="./DataTables/datatables.min.js"></script>

  <!-- Identidad de la sesión para las vistas.
       Es solo para mostrar: al guardar, el servidor vuelve a tomarla de
       $_SESSION y descarta lo que mande el navegador. 
  -->
  <script>
    window.USUARIO_SESION = <?php echo json_encode([
        'nomina' => $usuarioSesion['nomina'] ?? '',
        'nombre' => $nombreUsuario
    ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>;
  </script>

  <!--Custom js -->
  <script src="./scripts/main.js"></script>
</body>
</html>