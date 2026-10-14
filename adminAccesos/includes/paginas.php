<?php
/**
 * includes/paginas.php — Catálogo de pantallas de la aplicación.
 *
 * Lo usan index.php (enrutador) y includes/navbar.php (menú). Agregar una
 * pantalla nueva es agregar aquí su entrada y crear sus archivos con el mismo
 * nombre que la clave:
 *
 *   pages/<clave>.php        vista (HTML)
 *   css/<clave>.css          estilos propios (solo si 'css' => true)
 *   assets/js/<clave>.js     script propio (solo si 'js' => true)
 *
 * 'version' es el número que va en ?v= de su CSS y su JS: se incrementa cada
 * vez que se modifica alguno de los dos, para que el navegador no use la copia
 * vieja.
 *
 * 'disponible' => false muestra el aviso de "en construcción" mientras la
 * etapa correspondiente no esté terminada (ver PROGRESO.md). Si la pantalla
 * trae 'aviso' => [icono, titulo, texto], se muestra ese aviso en su lugar
 * (por ejemplo, cuando falta algo en la base de datos).
 *
 * 'grupo' agrupa la entrada en un menú desplegable de la barra de navegación.
 *
 * PENDIENTE (PENDIENTES.md §3): al definir los permisos se agregará aquí el
 * módulo de ADM_ACCESOS que protege cada pantalla.
 */

$GRUPOS_MENU = [
    'catalogos'    => ['titulo' => 'Catálogos',    'icono' => 'bi-collection'],
    'asignaciones' => ['titulo' => 'Asignaciones', 'icono' => 'bi-person-gear'],
];

$PAGINAS = [
    'dashboard' => [
        'titulo'      => 'Inicio',
        'descripcion' => 'Resumen general del modelo de accesos.',
        'icono'       => 'bi-speedometer2',
        'grupo'       => null,
        'disponible'  => true,
        'css'         => true,
        'js'          => true,
        'version'     => '1',
    ],
    'usuarios' => [
        'titulo'      => 'Usuarios',
        'descripcion' => 'Alta, consulta, estatus y contraseñas de los usuarios.',
        'icono'       => 'bi-people',
        'grupo'       => null,
        'disponible'  => true,
        'css'         => true,
        'js'          => true,
        'version'     => '2',
    ],
    'aplicaciones' => [
        'titulo'      => 'Aplicaciones',
        'descripcion' => 'Sistemas internos registrados en el modelo.',
        'icono'       => 'bi-grid-1x2',
        'grupo'       => 'catalogos',
        'disponible'  => true,
        'css'         => false,
        'js'          => true,
        'version'     => '1',
    ],
    'modulos' => [
        'titulo'      => 'Módulos',
        'descripcion' => 'Pantallas o secciones de cada aplicación.',
        'icono'       => 'bi-window-stack',
        'grupo'       => 'catalogos',
        'disponible'  => true,
        'css'         => false,
        'js'          => true,
        'version'     => '1',
    ],
    'permisos' => [
        'titulo'      => 'Permisos',
        'descripcion' => 'Acciones disponibles en cada módulo.',
        'icono'       => 'bi-key',
        'grupo'       => 'catalogos',
        'disponible'  => true,
        'css'         => false,
        'js'          => true,
        'version'     => '1',
    ],
    'roles' => [
        'titulo'      => 'Roles',
        'descripcion' => 'Paquetes de permisos por aplicación.',
        'icono'       => 'bi-person-badge',
        'grupo'       => 'catalogos',
        'disponible'  => true,
        'css'         => true,
        'js'          => true,
        'version'     => '1',
    ],
    'rolesUsuario' => [
        'titulo'      => 'Roles de usuario',
        'descripcion' => 'Asignar o quitar roles a un usuario.',
        'icono'       => 'bi-person-check',
        'grupo'       => 'asignaciones',
        'disponible'  => true,
        'css'         => true,
        'js'          => true,
        'version'     => '1',
    ],
    'permisosUsuario' => [
        'titulo'      => 'Permisos individuales',
        'descripcion' => 'Conceder o denegar permisos puntuales, con vigencia.',
        'icono'       => 'bi-person-lock',
        'grupo'       => 'asignaciones',
        'disponible'  => true,
        'css'         => true,
        'js'          => true,
        'version'     => '2',
    ],
    'permisosEfectivos' => [
        'titulo'      => 'Permisos efectivos',
        'descripcion' => 'Lo que cada usuario puede hacer y de dónde lo obtiene.',
        'icono'       => 'bi-shield-check',
        'grupo'       => null,
        'disponible'  => true,
        'css'         => true,
        'js'          => true,
        'version'     => '1',
    ],
    'auditoria' => [
        'titulo'      => 'Auditoría',
        'descripcion' => 'Historial de cambios administrativos.',
        'icono'       => 'bi-clock-history',
        'grupo'       => null,
        'disponible'  => false,
        'css'         => false,
        'js'          => false,
        'version'     => '1',
        /* Bloqueada por la base de datos, no por desarrollo: se muestra este
           aviso en lugar del genérico de "en construcción". PENDIENTES.md §1. */
        'aviso'       => [
            'icono'  => 'bi-database-lock',
            'titulo' => 'Pendiente: falta la tabla de historial',
            'texto'  => 'Para registrar y consultar el historial de cambios se necesita una tabla de auditoría en la base de datos, que todavía no existe. Esta sección se habilitará cuando se cree.',
        ],
    ],
];

/**
 * URL de una pantalla a partir de su clave.
 */
function urlPagina(string $clave): string
{
    return $clave === 'dashboard' ? 'index.php' : 'index.php?p=' . rawurlencode($clave);
}
