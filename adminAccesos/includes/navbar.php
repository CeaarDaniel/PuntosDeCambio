<?php
/**
 * includes/navbar.php — Barra de navegación principal.
 *
 * Componente reutilizable. Se construye a partir de includes/paginas.php:
 * las pantallas sin 'grupo' son enlaces directos y las que comparten 'grupo'
 * se reúnen en un menú desplegable, en el orden en que aparecen en $PAGINAS.
 *
 * Variable que puede definir la página antes del include:
 *   $claveActual  Clave de la pantalla que se está viendo, para marcarla activa.
 *
 * Solo se incluye con sesión iniciada (index.php la exige antes).
 *
 * PENDIENTE (PENDIENTES.md §3): al definir los permisos, ocultar aquí las
 * entradas sin permiso "ver". Ocultarlas es solo comodidad: la protección real
 * va en index.php y en cada endpoint.
 */
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/paginas.php';

$claveActual = $claveActual ?? '';

$nombreUsuario = nombreEnSesion() !== '' ? nombreEnSesion() : nominaEnSesion();

/* Arma la lista de entradas del menú: enlaces sueltos y grupos, respetando el
   orden del catálogo. Un grupo aparece en el lugar de su primera pantalla. */
$entradasMenu = [];

foreach ($PAGINAS as $clave => $pagina) {
    $grupo = $pagina['grupo'];

    if ($grupo === null) {
        $entradasMenu[] = ['tipo' => 'enlace', 'clave' => $clave];
        continue;
    }

    if (!isset($entradasMenu['grupo:' . $grupo])) {
        $entradasMenu['grupo:' . $grupo] = ['tipo' => 'grupo', 'grupo' => $grupo, 'claves' => []];
    }

    $entradasMenu['grupo:' . $grupo]['claves'][] = $clave;
}
?>
<nav class="app-nav" aria-label="Navegación principal">
    <div class="app-nav-inner">

        <div class="app-nav-enlaces">
            <?php foreach ($entradasMenu as $entrada) : ?>

                <?php if ($entrada['tipo'] === 'enlace') :
                    $clave = $entrada['clave'];
                    $pagina = $PAGINAS[$clave];
                    $activa = $clave === $claveActual;
                ?>
                    <a class="nav-link<?php echo $activa ? ' active' : ''; ?><?php echo $pagina['disponible'] ? '' : ' nav-link-pendiente'; ?>"
                       href="<?php echo escapar(urlPagina($clave)); ?>"
                       <?php echo $activa ? 'aria-current="page"' : ''; ?>>
                        <i class="bi <?php echo escapar($pagina['icono']); ?>" aria-hidden="true"></i>
                        <span><?php echo escapar($pagina['titulo']); ?></span>
                    </a>

                <?php else :
                    $grupo = $GRUPOS_MENU[$entrada['grupo']];
                    $grupoActivo = in_array($claveActual, $entrada['claves'], true);
                    $idGrupo = 'menuGrupo' . ucfirst($entrada['grupo']);
                ?>
                    <div class="dropdown">
                        <!-- strategy "fixed": la barra se desplaza en horizontal en
                             pantallas chicas (overflow-x), y con la estrategia por
                             omisión el menú quedaría recortado dentro de ella. -->
                        <button class="nav-link dropdown-toggle<?php echo $grupoActivo ? ' active' : ''; ?>"
                                type="button" id="<?php echo escapar($idGrupo); ?>"
                                data-bs-toggle="dropdown" aria-expanded="false"
                                data-bs-popper-config='{"strategy":"fixed"}'>
                            <i class="bi <?php echo escapar($grupo['icono']); ?>" aria-hidden="true"></i>
                            <span><?php echo escapar($grupo['titulo']); ?></span>
                        </button>
                        <ul class="dropdown-menu nav-grupo-menu" aria-labelledby="<?php echo escapar($idGrupo); ?>">
                            <?php foreach ($entrada['claves'] as $clave) :
                                $pagina = $PAGINAS[$clave];
                                $activa = $clave === $claveActual;
                            ?>
                                <li>
                                    <a class="dropdown-item<?php echo $activa ? ' active' : ''; ?>"
                                       href="<?php echo escapar(urlPagina($clave)); ?>"
                                       <?php echo $activa ? 'aria-current="page"' : ''; ?>>
                                        <i class="bi <?php echo escapar($pagina['icono']); ?>" aria-hidden="true"></i>
                                        <span><?php echo escapar($pagina['titulo']); ?></span>
                                        <?php if (!$pagina['disponible']) : ?>
                                            <span class="nav-etiqueta-pendiente">Pronto</span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

            <?php endforeach; ?>
        </div>

        <!-- Menú de la sesión. Va fuera de .app-nav-enlaces para que su
             desplegable no quede recortado por el desplazamiento horizontal. -->
        <div class="dropdown nav-usuario">
            <button class="nav-link nav-usuario-boton dropdown-toggle" type="button"
                    id="menuUsuario" data-bs-toggle="dropdown" aria-expanded="false"
                    title="<?php echo escapar($nombreUsuario); ?>">
                <span class="nav-usuario-avatar" aria-hidden="true">
                    <i class="bi bi-person"></i>
                </span>
                <span class="nav-usuario-nombre"><?php echo escapar($nombreUsuario); ?></span>
            </button>

            <ul class="dropdown-menu dropdown-menu-end nav-usuario-menu" aria-labelledby="menuUsuario">
                <li class="nav-usuario-datos">
                    <strong><?php echo escapar($nombreUsuario); ?></strong>
                    <span>Nómina <?php echo escapar(nominaEnSesion()); ?></span>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form class="nav-form-sesion" action="logout.php" method="post">
                        <input type="hidden" name="csrf" value="<?php echo escapar(tokenCsrf()); ?>">
                        <button class="dropdown-item nav-cerrar-sesion" type="submit">
                            <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                            <span>Cerrar sesión</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>

    </div>
</nav>
