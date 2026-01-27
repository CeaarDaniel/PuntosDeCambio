<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puntos de Cambio y Certificaciones - Login</title>

    <!-- Bootstrap -->
    <link href="./css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="./css/bootstrap-icons.min.css" />

    <!-- Font Awesome (para íconos) -->
    <link href="./css/all.min.css" rel="stylesheet"> 

    <!--Custom css -->
    <link rel="stylesheet" href="./css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <i class="bi bi-shield-check login-logo"></i>
                <h1 class="login-title">Puntos de Cambio</h1>
                <p class="login-subtitle">Sistema de Gestión de Puntos de Cambio y Certificaciones</p>
            </div>
            
            <!-- Body -->
            <div class="login-body">
                <!-- Mensaje de error -->
                <div class="alert alert-danger alert-custom" id="errorMessage">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span id="errorText">Usuario o contraseña incorrectos</span>
                </div>
                
                <form id="loginForm">
                    <div class="mb-3">
                        <label for="username" class="form-label fw-bold">Usuario</label>
                        <div class="input-group-custom">
                            <input type="text" class="form-control form-control-custom" id="username" 
                                   placeholder="Ingrese su usuario" required>
                            <button type="button" class="input-icon">
                                <i class="bi bi-person"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label fw-bold">Contraseña</label>
                        <div class="input-group-custom">
                            <input type="password" class="form-control form-control-custom" id="password" 
                                   placeholder="Ingrese su contraseña" required>
                            <button type="button" class="input-icon" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Recordar mis datos</label>
                    </div>
                    
                    <button type="submit" class="btn btn-login mb-3 text-light">
                        <b><i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión</b>
                    </button>
                </form>
            </div>
            
            <!-- Footer -->
            <div class="login-footer">
                <p class="small text-muted mb-2">Sistema de Gestión de Puntos de Cambio y Certificaciones</p>
                <div class="footer-links">
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="./scripts/bootstrap.bundle.min.js"></script>

    <!--Custom js -->
    <script src="./scripts/login.js"></script>
</body>
</html>