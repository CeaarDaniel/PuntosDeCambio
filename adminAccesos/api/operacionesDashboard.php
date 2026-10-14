<?php
/**
 * api/operacionesDashboard.php — Datos del dashboard (pages/dashboard.php).
 *
 * Opciones:
 *   '1'  Resumen completo: totales, excepciones por vencer, usuarios sin
 *        roles ni permisos y posibles inconsistencias. Solo lectura.
 *
 * PENDIENTE (PENDIENTES.md §3): exigirPermiso('dashboard', 'ver').
 */

require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/consultas/dashboard.php';

prepararEndpoint();

$opcion = obtenerTextoPost('opcion') ?? '';
$conn = null;

try {
    require __DIR__ . '/conexion.php';

    switch ($opcion) {
        case '1':
            responderOk([
                'resumen'         => consultarResumenDashboard($conn),
                'diasVigencia'    => DIAS_VIGENCIA_PROXIMA,
                'porVencer'       => consultarExcepcionesPorVencer($conn, DIAS_VIGENCIA_PROXIMA),
                'sinAcceso'       => consultarUsuariosSinAcceso($conn),
                'inconsistencias' => consultarInconsistencias($conn),
            ]);

        default:
            responderError('La opción solicitada no existe.', 400);
    }
} catch (Throwable $error) {
    responderFalloInterno($error, $conn, 'Dashboard, opción ' . substr(preg_replace('/\D/', '', $opcion), 0, 5));
}
