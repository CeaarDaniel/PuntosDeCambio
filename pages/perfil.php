<?php
  require_once('../api/sesion.php');

  /*
   * Mi perfil: cambio de contraseña del usuario en sesión.
   * No exige permisos (cualquier usuario puede cambiar su propia contraseña),
   * solo una sesión iniciada. Se responde 403 porque es el código con el que
   * scripts/main.js muestra el aviso sin cargar el JS de la vista.
   */
  if (!haySesion()) {
    http_response_code(403);

    echo '<div class="lines-page">'
       .   '<div class="card border-0 shadow-sm text-center p-5 mx-auto" style="max-width: 520px;">'
       .     '<div class="mb-3">'
       .       '<i class="bi bi-person-lock" style="font-size: 2.5rem; color: var(--primary-light);"></i>'
       .     '</div>'
       .     '<h3 class="h5 mb-2">Tu sesi&oacute;n termin&oacute;</h3>'
       .     '<p class="text-secondary mb-3">Inicia sesi&oacute;n nuevamente para cambiar tu contrase&ntilde;a.</p>'
       .     '<a class="btn btn-primary-custom btn-custom" href="login.php">Ir al inicio de sesi&oacute;n</a>'
       .   '</div>'
       . '</div>';

    exit;
  }

  /* Token CSRF de la vista; lo valida y lo renueva la opción 4 de api/operacionesUsuarios.php. */
  if (!isset($_SESSION['csrf_perfil']) || !is_string($_SESSION['csrf_perfil'])) {
    $_SESSION['csrf_perfil'] = bin2hex(random_bytes(32));
  }

  $nombrePerfil = nombreEnSesion() !== '' ? nombreEnSesion() : 'Usuario';
  $nominaPerfil = nominaEnSesion();
?>
<!-- MI PERFIL
     Cambio de contraseña del usuario en sesión.
     Datos: api/operacionesUsuarios.php (opción 4) · Lógica: scripts/perfil.js · Estilos: css/perfil.css
     Diseño en dos columnas para que todo quepa en pantalla sin scroll (en celular se apila). -->
<div id="perfilView" class="perfil-view">

    <!-- Encabezado con el mismo diseño que el de Líneas (clases lines-intro de css/style.css).
         En el recuadro de la derecha va el usuario en sesión. -->
    <div class="lines-intro">
        <div class="lines-intro-icon">
            <i class="bi bi-person-circle"></i>
        </div>
        <div class="lines-intro-copy">
            <span class="section-kicker">Seguridad de la cuenta</span>
            <h2 id="tituloCambioContrasena">Cambiar contraseña</h2>
            <p>Captura tu contraseña actual y define una nueva para iniciar sesión.</p>
        </div>
        <div class="lines-intro-guide perfil-intro-usuario">
            <i class="bi bi-person-badge" aria-hidden="true"></i>
            <span>
                <strong><?php echo htmlspecialchars($nombrePerfil, ENT_QUOTES, 'UTF-8'); ?></strong>
                <?php if ($nominaPerfil !== '') : ?>
                    <small>Nómina <?php echo htmlspecialchars($nominaPerfil, ENT_QUOTES, 'UTF-8'); ?></small>
                <?php endif; ?>
            </span>
        </div>
    </div>

    <div class="perfil-layout">

        <!-- Columna izquierda: qué debe cumplir la contraseña -->
        <aside class="perfil-resumen">
            <!-- Se marcan en verde conforme se cumplen (scripts/perfil.js). -->
            <div class="perfil-requisitos-caja">
                <h3 id="tituloRequisitos">Tu nueva contraseña debe tener</h3>
                <ul class="perfil-requisitos" id="requisitosContrasena" aria-labelledby="tituloRequisitos">
                    <li data-regla="longitud"><i class="bi bi-circle" aria-hidden="true"></i> 8 caracteres como mínimo</li>
                    <li data-regla="mayuscula"><i class="bi bi-circle" aria-hidden="true"></i> Una letra mayúscula</li>
                    <li data-regla="minuscula"><i class="bi bi-circle" aria-hidden="true"></i> Una letra minúscula</li>
                    <li data-regla="especial"><i class="bi bi-circle" aria-hidden="true"></i> Un carácter especial (ej. ! # $ % * .)</li>
                </ul>
            </div>
        </aside>

        <!-- Columna derecha: formulario -->
        <section class="perfil-card" aria-labelledby="tituloCambioContrasena">
            <div class="alert d-none perfil-mensaje" id="perfilMensaje" role="status" aria-live="polite"></div>

            <form id="formCambioContrasena" novalidate>
                <input type="hidden" id="perfilCsrf" name="csrf"
                       value="<?php echo htmlspecialchars($_SESSION['csrf_perfil'], ENT_QUOTES, 'UTF-8'); ?>">

                <div class="perfil-campo">
                    <label class="form-label required" for="contrasenaActual">Contraseña actual</label>
                    <div class="input-group perfil-password-group">
                        <span class="input-group-text" aria-hidden="true">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input class="form-control" type="password" id="contrasenaActual"
                               name="contrasenaActual" autocomplete="current-password"
                               placeholder="Ingresa tu contraseña actual" maxlength="4096" required>
                        <button class="btn perfil-ver-contrasena" type="button"
                                data-password-target="contrasenaActual"
                                aria-label="Mostrar contraseña">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                        <div class="invalid-feedback">Revisa tu contraseña actual.</div>
                    </div>
                </div>

                <div class="perfil-campo">
                    <label class="form-label required" for="contrasenaNueva">Nueva contraseña</label>
                    <div class="input-group perfil-password-group">
                        <span class="input-group-text" aria-hidden="true">
                            <i class="bi bi-shield-lock"></i>
                        </span>
                        <input class="form-control" type="password" id="contrasenaNueva"
                               name="contrasenaNueva" autocomplete="new-password"
                               placeholder="Captura una nueva contraseña"
                               aria-describedby="requisitosContrasena"
                               minlength="8" maxlength="4096" required>
                        <button class="btn perfil-ver-contrasena" type="button"
                                data-password-target="contrasenaNueva"
                                aria-label="Mostrar contraseña">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                        <div class="invalid-feedback">
                            No cumple los requisitos o es igual a la actual.
                        </div>
                    </div>
                </div>

                <div class="perfil-campo">
                    <label class="form-label required" for="confirmarContrasena">Confirmar nueva contraseña</label>
                    <div class="input-group perfil-password-group">
                        <span class="input-group-text" aria-hidden="true">
                            <i class="bi bi-check2-circle"></i>
                        </span>
                        <input class="form-control" type="password" id="confirmarContrasena"
                               name="confirmarContrasena" autocomplete="new-password"
                               placeholder="Repite la nueva contraseña"
                               minlength="8" maxlength="4096" required>
                        <button class="btn perfil-ver-contrasena" type="button"
                                data-password-target="confirmarContrasena"
                                aria-label="Mostrar contraseña">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                        <div class="invalid-feedback">Las contraseñas nuevas deben coincidir.</div>
                    </div>
                </div>

                <div class="perfil-acciones">
                    <button class="btn btn-outline-custom btn-custom" type="reset">
                        <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                        Limpiar
                    </button>
                    <button class="btn btn-primary-custom btn-custom" type="submit">
                        <i class="bi bi-check-lg" aria-hidden="true"></i>
                        Actualizar contraseña
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
