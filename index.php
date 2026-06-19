<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Puntos de cambio y certificaciones</title>

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
</head>
<body>

<div class="app-container">

    <!-- Barra de navegacion -->
      <?php include('navBar.php')?>
    
    <!-- Main Content Area -->
    <div class="main-content">
      <div class="top-bar">
        <h4 id="page-title" class="page-title">Dashboard</h4>
        <div class="user-info">
          <span>Usuario: Admin</span>
          <i class="bi bi-bell ms-3"></i>
        </div>
      </div>
      
      <div id="content-area" class="content-area animacion">
        <?php include('./pages/menuLineas.php') ?>
      </div>
    </div>
</div>

  <!-- Bootstrap JS -->
  <script src="./scripts/bootstrap.bundle.min.js"></script>

  <!--Custom js -->
  <script src="./scripts/main.js"></script>
</body>
</html>