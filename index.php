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

        <div class="top-bar-status">
          <span class="status-dot"></span>
          <span>CV</span>
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

  <!--Custom js -->
  <script src="./scripts/main.js"></script>
</body>
</html>