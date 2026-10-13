<?php
/*
 * Reportes de asistencia y de puntos de cambio (sección Reportes: pages/menuReportes.php).
 *
 * Solo lectura. Requiere sesión iniciada y peticiones AJAX (X-Requested-With).
 * Opciones ($_POST['opcion']):
 *   1  Catálogos de los filtros: líneas activas, estatus de asistencia y tipos 4M.
 *   2  Reporte de asistencia: totales, registros por estatus y detalle del periodo.
 *   3  Reporte de puntos de cambio: totales por estatus y por tipo 4M y listado del periodo.
 */
date_default_timezone_set('America/Mexico_City');
header('Content-Type: application/json; charset=utf-8');

/* Estatus de asistencia que cuentan como "presente" para el % de asistencia
   (claves de SPC_ESTATUS_ASISTENCIA): ASISTENCIA y TIEMPO EXTRA, el mismo criterio del layout. */
const ESTATUS_ASISTENCIA_PRESENTE = ['1', '8'];

/* Estatus de asistencia "FALTA INJUSTIFICADA" (se muestra aparte en los totales). */
const ESTATUS_ASISTENCIA_FALTA = '2';

/* Límite de filas del detalle para no saturar el navegador con periodos muy amplios. */
const LIMITE_FILAS_REPORTE = 5000;

function responderReporte(array $datos, int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

if (strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest') !== 0) {
    responderReporte(['status' => 'error', 'mensaje' => 'Solicitud no válida.'], 400);
}

require_once('./sesion.php');

if (!haySesion()) {
    responderReporte(['status' => 'error', 'mensaje' => 'Tu sesión terminó. Vuelve a iniciar sesión.'], 401);
}

include('./conexion.php');

/* Rango de fechas obligatorio (AAAA-MM-DD, "Hasta" incluido completo). */
function obtenerRangoFechasReporte(): array
{
    $inicio = trim((string) ($_POST['fechaInicio'] ?? ''));
    $fin = trim((string) ($_POST['fechaFin'] ?? ''));

    foreach ([$inicio, $fin] as $texto) {
        $fecha = DateTime::createFromFormat('!Y-m-d', $texto);

        if (!$fecha || $fecha->format('Y-m-d') !== $texto) {
            responderReporte(['status' => 'error', 'mensaje' => 'El rango de fechas no es válido.'], 422);
        }
    }

    if ($inicio > $fin) {
        responderReporte(['status' => 'error', 'mensaje' => 'La fecha "Desde" no puede ser posterior a "Hasta".'], 422);
    }

    return [$inicio, $fin];
}

/* Línea opcional: vacío = todas las líneas. */
function obtenerLineaReporte(): string
{
    $linea = trim((string) ($_POST['codigoLinea'] ?? ''));

    if (mb_strlen($linea) > 20) {
        responderReporte(['status' => 'error', 'mensaje' => 'La línea seleccionada no es válida.'], 422);
    }

    return $linea;
}

$opcion = (string) ($_POST['opcion'] ?? '');

try {
    switch ($opcion) {
        case '1':
            /* ================================================================
               Catálogos de los filtros
               ================================================================ */
            $lineas = $conn->query("SELECT codigo_linea, nombre_linea
                                    FROM SPC_LINEAS WHERE activo = 1
                                    ORDER BY nombre_linea")->fetchAll(PDO::FETCH_ASSOC);

            $estatusAsistencia = $conn->query("SELECT LTRIM(RTRIM(clave)) AS clave, nombre
                                               FROM SPC_ESTATUS_ASISTENCIA
                                               ORDER BY TRY_CONVERT(int, clave), clave")->fetchAll(PDO::FETCH_ASSOC);

            $tipos4M = $conn->query("SELECT LTRIM(RTRIM(clave)) AS clave, nombre
                                     FROM SPC_TIPO_4MPC
                                     ORDER BY idTIPO")->fetchAll(PDO::FETCH_ASSOC);

            responderReporte([
                'status' => 'ok',
                'data' => [
                    'lineas' => $lineas,
                    'estatusAsistencia' => $estatusAsistencia,
                    'tipos4M' => $tipos4M
                ]
            ]);

        case '2':
            /* ================================================================
               Reporte de asistencia (SPC_REGISTRO_ASISTENCIA)
               ================================================================ */
            [$fechaInicio, $fechaFin] = obtenerRangoFechasReporte();
            $codigoLinea = obtenerLineaReporte();
            $turno = trim((string) ($_POST['turno'] ?? ''));

            if ($turno !== '' && !in_array($turno, ['1', '2'], true)) {
                responderReporte(['status' => 'error', 'mensaje' => 'El turno no es válido.'], 422);
            }

            /* Filtros comunes sobre el registro de asistencia (alias RA) */
            $filtro = "RA.fecha_operacion >= :fechaInicio
                       AND RA.fecha_operacion < DATEADD(DAY, 1, CAST(:fechaFin AS date))";
            $parametros = [':fechaInicio' => $fechaInicio, ':fechaFin' => $fechaFin];

            if ($codigoLinea !== '') {
                $filtro .= " AND RA.codigo_linea = :codigoLinea";
                $parametros[':codigoLinea'] = $codigoLinea;
            }

            if ($turno !== '') {
                $filtro .= " AND RA.turno = :turno";
                $parametros[':turno'] = $turno;
            }

            /* Registros por estatus: todos los estatus del catálogo, aunque tengan cero */
            $stmtEstatus = $conn->prepare("SELECT LTRIM(RTRIM(EA.clave)) AS clave, EA.nombre,
                                                  COUNT(RA.id_registro) AS total
                                           FROM SPC_ESTATUS_ASISTENCIA EA
                                           LEFT JOIN SPC_REGISTRO_ASISTENCIA RA
                                                  ON LTRIM(RTRIM(RA.estatus)) = LTRIM(RTRIM(EA.clave)) AND $filtro
                                           GROUP BY EA.clave, EA.nombre
                                           ORDER BY TRY_CONVERT(int, EA.clave), EA.clave");
            $stmtEstatus->execute($parametros);
            $porEstatus = $stmtEstatus->fetchAll(PDO::FETCH_ASSOC);

            $total = 0;
            $presentes = 0;
            $faltas = 0;

            foreach ($porEstatus as &$fila) {
                $fila['total'] = (int) $fila['total'];
                $total += $fila['total'];

                if (in_array($fila['clave'], ESTATUS_ASISTENCIA_PRESENTE, true)) {
                    $presentes += $fila['total'];
                }

                if ($fila['clave'] === ESTATUS_ASISTENCIA_FALTA) {
                    $faltas += $fila['total'];
                }
            }
            unset($fila);

            $stmtPersonas = $conn->prepare("SELECT COUNT(DISTINCT RA.nomina) FROM SPC_REGISTRO_ASISTENCIA RA WHERE $filtro");
            $stmtPersonas->execute($parametros);
            $personas = (int) $stmtPersonas->fetchColumn();

            /* Detalle de registros del periodo (más recientes primero) */
            $stmtDetalle = $conn->prepare("SELECT TOP (" . (LIMITE_FILAS_REPORTE + 1) . ")
                                                  CONVERT(varchar(16), RA.fecha_operacion, 120) AS fecha,
                                                  RA.codigo_linea, L.nombre_linea, RA.turno,
                                                  RA.nomina, RA.nombre, RA.nombres_estaciones,
                                                  LTRIM(RTRIM(RA.estatus)) AS clave_estatus, EA.nombre AS estatus,
                                                  RA.comentarioAsistencia AS comentario
                                           FROM SPC_REGISTRO_ASISTENCIA RA
                                           LEFT JOIN SPC_LINEAS L ON L.codigo_linea = RA.codigo_linea
                                           LEFT JOIN SPC_ESTATUS_ASISTENCIA EA ON LTRIM(RTRIM(EA.clave)) = LTRIM(RTRIM(RA.estatus))
                                           WHERE $filtro
                                           ORDER BY RA.fecha_operacion DESC, RA.id_registro DESC");
            $stmtDetalle->execute($parametros);
            $registros = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);
            $limitado = count($registros) > LIMITE_FILAS_REPORTE;

            responderReporte([
                'status' => 'ok',
                'data' => [
                    'totales' => [
                        'registros' => $total,
                        'personas' => $personas,
                        'presentes' => $presentes,
                        'faltasInjustificadas' => $faltas,
                        'otrasAusencias' => $total - $presentes - $faltas,
                        /* % de asistencia = (ASISTENCIA + TIEMPO EXTRA) / total de registros */
                        'porcentajeAsistencia' => $total > 0 ? round($presentes * 100 / $total, 1) : null
                    ],
                    'porEstatus' => $porEstatus,
                    'registros' => array_slice($registros, 0, LIMITE_FILAS_REPORTE),
                    'limitado' => $limitado
                ]
            ]);

        case '3':
            /* ================================================================
               Reporte de puntos de cambio (SPC_PUNTOS_CAMBIO y su flujo)
               ================================================================ */
            [$fechaInicio, $fechaFin] = obtenerRangoFechasReporte();
            $codigoLinea = obtenerLineaReporte();
            /* Tipo 4M opcional: vacío = todos los tipos */
            $clave4M = strtoupper(trim((string) ($_POST['clave4M'] ?? '')));

            if ($clave4M !== '') {
                $stmtTipo = $conn->prepare("SELECT COUNT(*) FROM SPC_TIPO_4MPC WHERE clave = :clave");
                $stmtTipo->execute([':clave' => $clave4M]);

                if ((int) $stmtTipo->fetchColumn() === 0) {
                    responderReporte(['status' => 'error', 'mensaje' => 'El tipo de punto de cambio no es válido.'], 422);
                }
            }

            $filtro = "PC.fechaHora_inicio >= :fechaInicio
                       AND PC.fechaHora_inicio < DATEADD(DAY, 1, CAST(:fechaFin AS date))";
            $parametros = [':fechaInicio' => $fechaInicio, ':fechaFin' => $fechaFin];

            if ($codigoLinea !== '') {
                $filtro .= " AND PC.codigo_linea = :codigoLinea";
                $parametros[':codigoLinea'] = $codigoLinea;
            }

            /*
             * Se consultan todos los tipos del periodo y línea: la distribución por tipo se
             * calcula sobre todos, y los totales por estatus y el listado sobre el tipo elegido.
             * idTipo4M NULL corresponde a PC de mano de obra registrados antes de esa columna.
             * Para los PC 3M, "motivo" y "tipo de cambio" salen de su planeación.
             */
            $stmtPC = $conn->prepare("SELECT TOP (" . (LIMITE_FILAS_REPORTE + 1) . ")
                                             PC.idPC, PC.no_controlCambio,
                                             COALESCE(LTRIM(RTRIM(T4.clave)), 'MO') AS clave4M,
                                             PC.codigo_linea, L.nombre_linea, PC.turno,
                                             PC.nomina, PC.nombre,
                                             COALESCE(PC.motivo, PL.detalles_cambio) AS motivo,
                                             CONVERT(varchar(16), PC.fechaHora_inicio, 120) AS fechaHora_inicio,
                                             CONVERT(varchar(16), PC.fechaHora_fin, 120) AS fechaHora_fin,
                                             PC.tipo_cambio, T3.nombre AS tipo_cambio_3m,
                                             LTRIM(RTRIM(PC.estatusPC)) AS estatusPC, EP.nombre AS estatus
                                      FROM SPC_PUNTOS_CAMBIO PC
                                      LEFT JOIN SPC_TIPO_4MPC T4 ON T4.idTIPO = PC.idTipo4M
                                      LEFT JOIN SPC_LINEAS L ON L.codigo_linea = PC.codigo_linea
                                      LEFT JOIN SPC_PLANEACION PL ON PL.idPC = PC.idPC
                                      LEFT JOIN SPC_TIPO_3M T3 ON T3.idTIPO3M = PL.idTIPO3M
                                      LEFT JOIN SPC_ESTATUS_PC EP ON LTRIM(RTRIM(EP.clave)) = LTRIM(RTRIM(PC.estatusPC))
                                      WHERE $filtro
                                      ORDER BY PC.fechaHora_inicio DESC, PC.idPC DESC");
            $stmtPC->execute($parametros);
            $todos = $stmtPC->fetchAll(PDO::FETCH_ASSOC);
            $limitado = count($todos) > LIMITE_FILAS_REPORTE;
            $todos = array_slice($todos, 0, LIMITE_FILAS_REPORTE);

            $porTipo = [];

            foreach ($todos as $fila) {
                $porTipo[$fila['clave4M']] = ($porTipo[$fila['clave4M']] ?? 0) + 1;
            }

            $registros = $clave4M === ''
                ? $todos
                : array_values(array_filter($todos, function ($fila) use ($clave4M) {
                    return $fila['clave4M'] === $clave4M;
                }));

            $porEstatus = ['1' => 0, '2' => 0, '3' => 0];

            foreach ($registros as $fila) {
                if (isset($porEstatus[$fila['estatusPC']])) {
                    $porEstatus[$fila['estatusPC']]++;
                }
            }

            responderReporte([
                'status' => 'ok',
                'data' => [
                    'totales' => [
                        'total' => count($registros),
                        'activos' => $porEstatus['1'],
                        'cancelados' => $porEstatus['2'],
                        'finalizados' => $porEstatus['3']
                    ],
                    'porTipo' => $porTipo,
                    'registros' => $registros,
                    'limitado' => $limitado
                ]
            ]);

        default:
            responderReporte(['status' => 'error', 'mensaje' => 'La opción solicitada no existe.'], 400);
    }
} catch (PDOException $e) {
    error_log('[operacionesReportes] ' . $e->getMessage());
    responderReporte(['status' => 'error', 'mensaje' => 'No fue posible generar el reporte.'], 500);
}
