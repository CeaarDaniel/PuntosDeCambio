<?php
/**
 * api/operacionesPermisosEfectivos.php — Pantalla "Permisos efectivos"
 * (pages/permisosEfectivos.php). Solo lectura.
 *
 * Opciones:
 *   '1'  Catálogos: usuarios (para el buscador).
 *   '2'  Resolución completa de un usuario:
 *          - permisos permitidos y denegados, con su origen
 *            (consultarPermisosEfectivos(), la misma regla del login);
 *          - excepciones vencidas, como filas "vencido" (no tienen efecto);
 *          - roles asignados en todas las aplicaciones (activos e inactivos);
 *          - resumen por aplicación (acceso, permitidos, denegados, roles).
 *
 * Regla (contexto.md, módulo 9):
 *   1. unión de los permisos de sus roles activos;
 *   2. + excepciones "conceder" vigentes;
 *   3. − excepciones "denegar" vigentes;
 *   4. se ignoran aplicaciones, módulos y roles inactivos;
 *   5. se muestra el origen de cada permiso.
 *
 * PENDIENTE (PENDIENTES.md §3): exigirPermiso('permisos_efectivos', 'ver').
 */

require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/consultas/catalogos.php';
require_once __DIR__ . '/consultas/usuarios.php';
require_once __DIR__ . '/consultas/permisosEfectivos.php';

prepararEndpoint();

/**
 * Filas de la tabla: los permisos resueltos más las excepciones vencidas.
 */
function filasPermisosEfectivos(array $efectivos, array $excepciones): array
{
    $filas = [];

    foreach ($efectivos as $permiso) {
        $filas[] = $permiso;
    }

    foreach ($excepciones as $excepcion) {
        if ($excepcion['estadoVigencia'] !== 'vencido') {
            continue;
        }

        $filas[] = [
            'codigoSistema' => $excepcion['codigoSistema'],
            'aplicacion'    => $excepcion['aplicacion'],
            'codigoModulo'  => $excepcion['codigoModulo'],
            'modulo'        => $excepcion['modulo'],
            'accion'        => $excepcion['accion'],
            'descripcion'   => $excepcion['descripcion'],
            'roles'         => [],
            'excepcion'     => $excepcion['tipo'],
            'vigenciaHasta' => $excepcion['vigenciaHasta'],
            'resultado'     => 'vencido',
            'origen'        => 'Excepción individual vencida (' . $excepcion['tipo'] . '); ya no tiene efecto',
        ];
    }

    return $filas;
}

/**
 * Resumen por aplicación: cuántos permisos le quedan permitidos y denegados,
 * y qué roles tiene en ella. Incluye toda aplicación donde tenga un rol o
 * algún permiso resuelto.
 */
function resumenPorAplicacion(array $efectivos, array $roles, array $catalogoAplicaciones): array
{
    $resumen = [];

    foreach ($catalogoAplicaciones as $aplicacion) {
        $resumen[$aplicacion['codigoSistema']] = [
            'codigoSistema' => $aplicacion['codigoSistema'],
            'nombre'        => $aplicacion['nombre'],
            'activo'        => $aplicacion['activo'],
            'permitidos'    => 0,
            'denegados'     => 0,
            'roles'         => [],
        ];
    }

    foreach ($efectivos as $permiso) {
        $clave = $permiso['resultado'] === 'permitido' ? 'permitidos' : 'denegados';
        $resumen[$permiso['codigoSistema']][$clave]++;
    }

    foreach ($roles as $rol) {
        $resumen[$rol['codigoSistema']]['roles'][] = [
            'codigoRol' => $rol['codigoRol'],
            'nombreRol' => $rol['nombreRol'],
            'activo'    => $rol['rolActivo'],
        ];
    }

    /* Solo las aplicaciones con las que el usuario tiene alguna relación. */
    return array_values(array_filter($resumen, function ($a) {
        return $a['permitidos'] > 0 || $a['denegados'] > 0 || count($a['roles']) > 0;
    }));
}

$opcion = obtenerTextoPost('opcion') ?? '';
$conn = null;

try {
    require __DIR__ . '/conexion.php';

    switch ($opcion) {
        case '1':
            responderOk(['usuarios' => consultarUsuarios($conn)]);

        case '2':
            $nomina = obtenerTextoPost('nomina') ?? '';

            if (!textoEsValido($nomina, NOMINA_LONGITUD_MAXIMA)) {
                responderError('Selecciona un usuario válido.', 400);
            }

            $usuario = consultarUsuario($conn, $nomina);

            if ($usuario === null) {
                responderError('El usuario no existe.', 404);
            }

            $efectivos = consultarPermisosEfectivos($conn, $nomina);
            $roles = consultarRolesDeUsuario($conn, $nomina);

            responderOk([
                'usuario'      => $usuario,
                'permisos'     => filasPermisosEfectivos($efectivos, consultarExcepcionesDeUsuario($conn, $nomina)),
                'roles'        => $roles,
                'aplicaciones' => resumenPorAplicacion($efectivos, $roles, consultarAplicacionesCatalogo($conn)),
            ]);

        default:
            responderError('La opción solicitada no existe.', 400);
    }
} catch (Throwable $error) {
    responderFalloInterno($error, $conn, 'Permisos efectivos, opción ' . opcionParaLog($opcion));
}
