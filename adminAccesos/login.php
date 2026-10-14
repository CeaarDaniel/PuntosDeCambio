<?php
require_once __DIR__ . '/includes/sesion.php';

if (haySesion()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Acceso a la administración de usuarios, roles y permisos.">
    <!-- Token CSRF: assets/js/app.js lo envía en cada petición AJAX. -->
    <meta name="csrf-token" content="<?php echo escapar(tokenCsrf()); ?>">
    <title>Iniciar sesión | <?php echo escapar(APP_NOMBRE); ?></title>

    <?php include_once __DIR__ . '/includes/favicon.php'; ?>

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css?v=6">
    <link rel="stylesheet" href="css/login.css?v=1">
</head>
<body>

<div class="app-shell">

    <?php require_once __DIR__ . '/includes/header.php'; ?>

    <main class="app-main login-main">
        <section class="login-layout" aria-labelledby="tituloLogin">
            <div class="login-presentacion">
                <div class="login-presentacion-contenido">
                    <span class="login-etiqueta">
                        <i class="bi bi-shield-check" aria-hidden="true"></i>
                        Acceso seguro
                    </span>
                    <h1>Administración de accesos</h1>
                    <p>
                        Administra en un solo lugar los usuarios, las aplicaciones,
                        los roles y los permisos de los sistemas internos.
                    </p>

                    <div class="login-beneficios" aria-label="Funciones principales">
                        <div>
                            <i class="bi bi-people" aria-hidden="true"></i>
                            <span>Usuarios y contraseñas en un solo lugar</span>
                        </div>
                        <div>
                            <i class="bi bi-person-badge" aria-hidden="true"></i>
                            <span>Roles y permisos por aplicación</span>
                        </div>
                        <div>
                            <i class="bi bi-shield-check" aria-hidden="true"></i>
                            <span>Consulta de lo que cada persona puede hacer</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="login-panel">
                <div class="login-card">
                    <div class="login-icono" aria-hidden="true">
                        <i class="bi bi-person-lock"></i>
                    </div>

                    <div class="login-card-encabezado">
                        <h2 id="tituloLogin">Iniciar sesión</h2>
                        <p>Escribe tu número de nómina y tu contraseña.</p>
                    </div>

                    <div class="alert d-none login-mensaje" id="loginMensaje" role="alert" aria-live="polite"></div>

                    <form id="formLogin" action="api/autenticacion.php" method="post" novalidate>
                        <div class="mb-3">
                            <label class="form-label required" for="loginNomina">Nómina</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text" aria-hidden="true">
                                    <i class="bi bi-person-badge"></i>
                                </span>
                                <input
                                    class="form-control"
                                    type="text"
                                    id="loginNomina"
                                    name="nomina"
                                    maxlength="<?php echo NOMINA_LONGITUD_MAXIMA; ?>"
                                    placeholder="Tu número de nómina"
                                    autocomplete="username"
                                    autocapitalize="off"
                                    spellcheck="false"
                                    required
                                    autofocus>
                                <div class="invalid-feedback">Escribe tu nómina.</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label required" for="loginContrasena">Contraseña</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text" aria-hidden="true">
                                    <i class="bi bi-key"></i>
                                </span>
                                <input
                                    class="form-control"
                                    type="password"
                                    id="loginContrasena"
                                    name="contrasena"
                                    placeholder="Ingresa tu contraseña"
                                    autocomplete="current-password"
                                    required>
                                <button class="btn btn-soft" type="button" id="loginVerContrasena"
                                        title="Mostrar contraseña" aria-label="Mostrar contraseña"
                                        aria-pressed="false">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </button>
                                <div class="invalid-feedback">Ingresa tu contraseña.</div>
                            </div>
                        </div>

                        <button class="btn btn-primary login-boton" type="submit">
                            <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                            Ingresar
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

</div>

<?php require_once __DIR__ . '/includes/toast.php'; ?>

<script src="assets/js/jquery-3.7.1.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js?v=5"></script>
<script src="assets/js/login.js?v=1"></script>

</body>
</html>
