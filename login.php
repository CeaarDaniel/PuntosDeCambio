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
    <main class="login-container">
        <div class="login-shell">
            <section class="login-showcase" aria-label="Información del sistema">
                <div class="showcase-brand">
                    <span class="showcase-brand-icon">
                        <i class="bi bi-diagram-3"></i>
                    </span>
                    <span>Sistema puntos de cambio</span>
                </div>

                <div class="showcase-content">
                    <span class="showcase-eyebrow">Control de puntos de cambio</span>
                    <h2>LOGIN SPC</h2>
                    <p>Consulta líneas, personal y puntos de cambio</p>

                    <div class="showcase-features">
                        <div class="showcase-feature">
                            <i class="bi bi-grid-3x3-gap"></i>
                            <span>Visualiza el estado de cada línea</span>
                        </div>
                        <div class="showcase-feature">
                            <i class="bi bi-people"></i>
                            <span>Gestiona asignaciones de personal</span>
                        </div>
                        <div class="showcase-feature">
                            <i class="bi bi-arrow-left-right"></i>
                            <span>Da seguimiento a puntos de cambio</span>
                        </div>
                    </div>
                </div>

                <div class="showcase-note">
                    <i class="bi bi-shield-check"></i>
                    <span>Acceso exclusivo para personal autorizado</span>
                </div>
            </section>

            <section class="login-card" aria-label="Inicio de sesión">
                <div class="login-header">
                    <span class="login-logo">
                        <i class="bi bi-shield-check"></i>
                    </span>
                    <span class="login-kicker">Bienvenido</span>
                    <h1 class="login-title">Inicia sesión</h1>
                    <p class="login-subtitle">Ingresa tus datos para continuar al sistema.</p>
                </div>

                <div class="login-body">
                    <div class="alert alert-danger alert-custom" id="errorMessage" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span id="errorText">Usuario o contraseña incorrectos</span>
                    </div>

                    <form id="loginForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="bi bi-person me-1"></i>Usuario
                            </label>
                            <div class="input-group-custom">
                                <input type="text" class="form-control form-control-custom" id="username"
                                       placeholder="Ingrese su usuario" autocomplete="username" required>
                                <span class="input-icon" aria-hidden="true">
                                    <i class="bi bi-person"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock me-1"></i>Contraseña
                            </label>
                            <div class="input-group-custom">
                                <input type="password" class="form-control form-control-custom" id="password"
                                       placeholder="Ingrese su contraseña" autocomplete="current-password" required>
                                <button type="button" class="input-icon" id="togglePassword" aria-label="Mostrar u ocultar contraseña">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="login-options d-none">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="rememberMe">
                                <label class="form-check-label" for="rememberMe">Recordar mis datos</label>
                            </div>
                            <span class="login-security">
                                <i class="bi bi-lock-fill"></i> Acceso seguro
                            </span>
                        </div>

                        <button type="submit" class="btn btn-login text-light">
                            <span>Iniciar sesión</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </form>
                </div>

                <div class="login-footer">
                    <i class="bi bi-info-circle"></i>
                    <p>Utiliza las credenciales asignadas por el administrador.</p>
                </div>
            </section>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="./scripts/bootstrap.bundle.min.js"></script>

    <!--Custom js -->
    <script src="./scripts/login.js"></script>
</body>
</html>