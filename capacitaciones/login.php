<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Acceso a la plataforma de capacitación y evaluación de personal">
    <title>Iniciar sesión | CapacitaLíder</title>

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body class="login-page">
    <main class="login-wrapper">
        <section class="login-card" aria-labelledby="login-title">
            <aside class="login-brand-panel">
                <div class="brand-content">
                    <div class="brand-icon" aria-hidden="true">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <h1 class="brand-name">Capacitaciones</h1>
                    <p class="brand-description">
                        Plataforma de Capacitación<br>
                        y Evaluación para lideres
                    </p>
                </div>

                <div class="demo-access">
                    <p class="demo-title">Acceso rápido de demostración</p>
                    <button class="demo-user" type="button" data-employee="EMP004" data-name="Diana Flores">
                        <strong>Capacitador</strong><span>Diana Flores</span>
                    </button>
                </div>
            </aside>

            <div class="login-form-panel">
                <div class="login-form-content">
                    <header class="form-heading">
                        <span class="mobile-brand-icon" aria-hidden="true">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </span>
                        <div>
                            <h2 id="login-title">Bienvenido</h2>
                            <p>Ingresa tus datos para continuar</p>
                        </div>
                    </header>

                    <div id="login-message" class="login-message" role="status" aria-live="polite"></div>

                    <form id="login-form" novalidate>
                        <div class="mb-4">
                            <label for="employee-number" class="form-label">Número de empleado</label>
                            <div class="input-icon-wrapper">
                                <i class="fa-regular fa-id-badge" aria-hidden="true"></i>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="employee-number"
                                    name="employee-number"
                                    placeholder="Ej. EMP001"
                                    autocomplete="username"
                                    required
                                >
                            </div>
                            <div class="invalid-feedback">Ingresa tu número de empleado.</div>
                        </div>

                        <div class="mb-2">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-icon-wrapper password-wrapper">
                                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="password"
                                    name="password"
                                    placeholder="Ingresa tu contraseña"
                                    autocomplete="current-password"
                                    required
                                >
                                <button id="toggle-password" class="toggle-password" type="button" aria-label="Mostrar contraseña" aria-pressed="false">
                                    <i class="fa-regular fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">Ingresa tu contraseña.</div>
                        </div>

                        <p class="demo-password">Para demo, usa la contraseña: <strong>1234</strong></p>

                        <button class="btn-login" type="submit">
                            <span>Ingresar</span>
                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </button>
                    </form>

                    <footer class="roles-note">
                        <i class="fa-solid fa-user-group" aria-hidden="true"></i>
                        <span>Roles disponibles: Operador · Líder · Capacitador · Supervisor</span>
                    </footer>
                </div>
            </div>
        </section>
    </main>

    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
