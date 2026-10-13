<?php
date_default_timezone_set('America/Mexico_City');

header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Allow: POST, OPTIONS');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

/**
 * Devuelve una respuesta JSON uniforme para las operaciones del reporte.
 */
function responderReporteJson(array $respuesta, int $codigoHttp = 200): void
{
    http_response_code($codigoHttp);
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Obtiene un entero positivo enviado por POST sin aceptar arreglos.
 */
function obtenerEnteroReportePost(string $campo): ?int
{
    if (!isset($_POST[$campo]) || is_array($_POST[$campo])) {
        return null;
    }

    $valor = trim((string) $_POST[$campo]);

    if (!preg_match('/^\d+$/', $valor)) {
        return null;
    }

    $entero = filter_var($valor, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 2147483647]
    ]);

    return $entero === false ? null : $entero;
}

/**
 * Valida enteros usados por la paginación sin aceptar arreglos ni decimales.
 */
function obtenerEnteroRangoReportePost(
    string $campo,
    int $predeterminado,
    int $minimo,
    int $maximo
): ?int
{
    if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
        return $predeterminado;
    }

    if (is_array($_POST[$campo])) {
        return null;
    }

    $valor = trim((string) $_POST[$campo]);

    if (!preg_match('/^\d+$/', $valor)) {
        return null;
    }

    $entero = filter_var($valor, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => $minimo, 'max_range' => $maximo]
    ]);

    return $entero === false ? null : $entero;
}

/**
 * Obtiene texto simple enviado por POST; devuelve null si no fue enviado.
 */
function obtenerTextoReportePost(string $campo): ?string
{
    if (!isset($_POST[$campo]) || is_array($_POST[$campo])) {
        return null;
    }

    return trim((string) $_POST[$campo]);
}

/**
 * Valida una fecha opcional con el formato exacto YYYY-MM-DD.
 */
function obtenerFechaReportePost(string $campo): ?string
{
    $valor = obtenerTextoReportePost($campo);

    if ($valor === null || $valor === '') {
        return null;
    }

    $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);

    return $fecha !== false && $fecha->format('Y-m-d') === $valor
        ? $valor
        : null;
}

/**
 * Obtiene el periodo común para todos los reportes de anormalidades.
 *
 * Si falta cualquiera de las dos fechas, se ignora la fecha aislada y se
 * consulta el año calendario actual completo. Sólo cuando ambas fechas están
 * presentes se aplica el rango indicado por el usuario.
 */
function obtenerRangoFechasReportePost(): array
{
    $fechaInicioTexto = obtenerTextoReportePost('fechaInicio');
    $fechaFinTexto = obtenerTextoReportePost('fechaFin');
    $fechaInicio = obtenerFechaReportePost('fechaInicio');
    $fechaFin = obtenerFechaReportePost('fechaFin');

    /* Un valor enviado pero inválido nunca debe tratarse como fecha vacía. */
    if ($fechaInicioTexto !== null && $fechaInicioTexto !== '' && $fechaInicio === null) {
        responderReporteJson([
            'status' => 'error',
            'mensaje' => 'La fecha inicial no es válida.'
        ], 400);
    }

    if ($fechaFinTexto !== null && $fechaFinTexto !== '' && $fechaFin === null) {
        responderReporteJson([
            'status' => 'error',
            'mensaje' => 'La fecha final no es válida.'
        ], 400);
    }

    if ($fechaInicio === null || $fechaFin === null) {
        $anioActual = (new DateTimeImmutable('now'))->format('Y');

        return [
            'fechaInicio' => $anioActual . '-01-01',
            'fechaFin' => $anioActual . '-12-31',
            'predeterminado' => true
        ];
    }

    if ($fechaInicio > $fechaFin) {
        responderReporteJson([
            'status' => 'error',
            'mensaje' => 'La fecha inicial no puede ser posterior a la fecha final.'
        ], 400);
    }

    return [
        'fechaInicio' => $fechaInicio,
        'fechaFin' => $fechaFin,
        'predeterminado' => false
    ];
}

/**
 * Valida los filtros compartidos por las tarjetas y las gráficas.
 * Centralizar esta lógica evita que cada reporte interprete distinto el mismo
 * rango de fechas, área, línea o turno.
 */
function obtenerFiltrosComunesReportePost(): array
{
    foreach (['fechaInicio', 'fechaFin', 'idArea', 'codigoLinea', 'idTurno'] as $campo) {
        if (isset($_POST[$campo]) && is_array($_POST[$campo])) {
            responderReporteJson([
                'status' => 'error',
                'mensaje' => 'Los filtros enviados no tienen un formato válido.'
            ], 400);
        }
    }

    $rangoFechas = obtenerRangoFechasReportePost();
    $idAreaTexto = obtenerTextoReportePost('idArea');
    $idTurnoTexto = obtenerTextoReportePost('idTurno');
    $codigoLinea = obtenerTextoReportePost('codigoLinea');
    $idArea = null;
    $idTurno = null;

    if ($idAreaTexto !== null && $idAreaTexto !== '') {
        $idArea = obtenerEnteroReportePost('idArea');

        if ($idArea === null) {
            responderReporteJson([
                'status' => 'error',
                'mensaje' => 'El área seleccionada no es válida.'
            ], 400);
        }
    }

    if ($idTurnoTexto !== null && $idTurnoTexto !== '') {
        $idTurno = obtenerEnteroReportePost('idTurno');

        if ($idTurno === null) {
            responderReporteJson([
                'status' => 'error',
                'mensaje' => 'El turno seleccionado no es válido.'
            ], 400);
        }
    }

    if ($codigoLinea !== null &&
        ($codigoLinea === '' || strlen($codigoLinea) > 100 ||
         preg_match('/[\x00-\x1F\x7F]/', $codigoLinea))) {
        responderReporteJson([
            'status' => 'error',
            'mensaje' => 'La línea seleccionada no es válida.'
        ], 400);
    }

    return [
        'rangoFechas' => $rangoFechas,
        'idArea' => $idArea,
        'codigoLinea' => $codigoLinea,
        'idTurno' => $idTurno
    ];
}

/**
 * Construye exclusivamente las condiciones permitidas y sus parámetros.
 * Los valores nunca se concatenan al SQL; se envían al motor por separado.
 */
function construirFiltroAnormalidadesReporte(array $filtros, string $alias = 'A'): array
{
    $rangoFechas = $filtros['rangoFechas'];
    $condiciones = [
        "{$alias}.fecha_hora_deteccion >= CONVERT(date, :fechaInicio, 23)",
        "{$alias}.fecha_hora_deteccion < DATEADD(DAY, 1, CONVERT(date, :fechaFin, 23))"
    ];
    $parametros = [
        ':fechaInicio' => $rangoFechas['fechaInicio'],
        ':fechaFin' => $rangoFechas['fechaFin']
    ];

    if ($filtros['idArea'] !== null) {
        $condiciones[] = "EXISTS
            (SELECT 1
             FROM SPC_LINEAS AS LF
             WHERE LF.codigo_linea = {$alias}.codigo_linea
               AND LF.idArea = :idArea)";
        $parametros[':idArea'] = $filtros['idArea'];
    }

    if ($filtros['codigoLinea'] !== null) {
        $condiciones[] = "{$alias}.codigo_linea = :codigoLinea";
        $parametros[':codigoLinea'] = $filtros['codigoLinea'];
    }

    if ($filtros['idTurno'] !== null) {
        $condiciones[] = "{$alias}.turno = :idTurno";
        $parametros[':idTurno'] = $filtros['idTurno'];
    }

    return [
        'sql' => 'WHERE ' . implode(' AND ', $condiciones),
        'parametros' => $parametros
    ];
}

/**
 * Valida los filtros adicionales que solamente utiliza el listado detallado.
 * El estatus se recibe por CLAVE porque ése es el identificador del flujo.
 */
function obtenerFiltrosClasificacionReportePost(): array
{
    foreach (['idCriticidad', 'claveEstatus', 'sinEstatus'] as $campo) {
        if (isset($_POST[$campo]) && is_array($_POST[$campo])) {
            responderReporteJson([
                'status' => 'error',
                'mensaje' => 'Los filtros del listado no tienen un formato válido.'
            ], 400);
        }
    }

    $idCriticidadTexto = obtenerTextoReportePost('idCriticidad');
    $claveEstatusTexto = obtenerTextoReportePost('claveEstatus');
    $sinEstatusTexto = obtenerTextoReportePost('sinEstatus');
    $idCriticidad = null;
    $clavesEstatus = [];
    $sinEstatus = false;

    if ($idCriticidadTexto !== null && $idCriticidadTexto !== '') {
        $idCriticidad = obtenerEnteroReportePost('idCriticidad');

        if ($idCriticidad === null) {
            responderReporteJson([
                'status' => 'error',
                'mensaje' => 'La criticidad seleccionada no es válida.'
            ], 400);
        }
    }

    if ($claveEstatusTexto !== null && $claveEstatusTexto !== '') {
        if (strlen($claveEstatusTexto) > 250 ||
            !preg_match('/^\d+(,\d+)*$/', $claveEstatusTexto)) {
            responderReporteJson([
                'status' => 'error',
                'mensaje' => 'Los estatus seleccionados no son válidos.'
            ], 400);
        }

        $clavesEstatus = array_values(array_unique(explode(',', $claveEstatusTexto)));
    }

    if ($sinEstatusTexto !== null && $sinEstatusTexto !== '') {
        if ($sinEstatusTexto !== '1') {
            responderReporteJson([
                'status' => 'error',
                'mensaje' => 'La selección de estatus no es válida.'
            ], 400);
        }

        $sinEstatus = true;
    }

    return [
        'idCriticidad' => $idCriticidad,
        'clavesEstatus' => $clavesEstatus,
        'sinEstatus' => $sinEstatus
    ];
}

/**
 * Agrega criticidad y estatus al filtro común usando parámetros enlazados.
 * EXISTS evita duplicar filas por la relación con la primera revisión.
 */
function agregarFiltrosClasificacionReporte(
    array $filtro,
    array $filtrosClasificacion,
    string $aliasAnormalidad = 'A',
    string $aliasEstatus = 'E'
): array {
    if ($filtrosClasificacion['sinEstatus']) {
        /* Ningún check seleccionado debe devolver un conjunto vacío. */
        $filtro['sql'] .= ' AND 1 = 0';
    }

    if ($filtrosClasificacion['idCriticidad'] !== null) {
        $filtro['sql'] .= " AND EXISTS
            (SELECT 1
             FROM SA_primera_revision AS PRF
             WHERE PRF.id_anormalidad = {$aliasAnormalidad}.id_anormalidad
               AND PRF.id_criticidad = :idCriticidad)";
        $filtro['parametros'][':idCriticidad'] =
            $filtrosClasificacion['idCriticidad'];
    }

    if ($filtrosClasificacion['clavesEstatus']) {
        $parametrosEstatus = [];

        foreach ($filtrosClasificacion['clavesEstatus'] as $indice => $claveEstatus) {
            $nombreParametro = ':claveEstatus' . $indice;
            $parametrosEstatus[] = $nombreParametro;
            $filtro['parametros'][$nombreParametro] = $claveEstatus;
        }

        $filtro['sql'] .= " AND LTRIM(RTRIM({$aliasEstatus}.CLAVE)) IN (" .
            implode(', ', $parametrosEstatus) . ')';
    }

    return $filtro;
}

/**
 * Documenta una operación de reporte que todavía no consulta información.
 *
 * Cada pestaña tendrá una operación principal. El parámetro opcional
 * "seccion" permitirá solicitar toda la pestaña (completo) o solamente un
 * bloque, sin crear una ruta diferente para cada gráfica.
 */
function responderReportePendiente(string $operacion, array $secciones): void
{
    $seccion = obtenerTextoReportePost('seccion') ?: 'completo';

    if (!in_array($seccion, $secciones, true)) {
        responderReporteJson([
            'status' => 'error',
            'mensaje' => 'La sección solicitada no es válida para este reporte.'
        ], 400);
    }

    responderReporteJson([
        'status' => 'pendiente',
        'mensaje' => 'La consulta de este reporte todavía no está implementada.',
        'data' => [
            'operacion' => $operacion,
            'seccion_solicitada' => $seccion,
            'secciones_disponibles' => $secciones
        ]
    ], 501);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderReporteJson([
        'status' => 'error',
        'mensaje' => 'Método no permitido.'
    ], 405);
}

/* Restringe estas operaciones a solicitudes realizadas desde la aplicación. */
if (strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest') !== 0) {
    responderReporteJson([
        'status' => 'error',
        'mensaje' => 'La solicitud no fue originada por la aplicación.'
    ], 403);
}

include('./conexion.php');

$opcion = isset($_POST['opcion']) && !is_array($_POST['opcion'])
    ? trim((string) $_POST['opcion'])
    : '';

/*
 * Enrutador de operaciones del módulo.
 *
 * Los alias numéricos mantienen compatibilidad con llamadas anteriores. Los
 * nombres descriptivos son los que debe usar el código nuevo del cliente.
 * Las operaciones por pestaña aceptarán "seccion=completo" para traer todos
 * sus bloques en una petición, o el nombre de un bloque para actualizarlo de
 * forma independiente.
 */
$rutasReporte = [
    '1' => 'catalogos',
    'catalogos' => 'catalogos',
    '2' => 'lineas_por_area',
    'lineas_por_area' => 'lineas_por_area',
    '3' => 'tarjetas_resumen',
    'tarjetas_resumen' => 'tarjetas_resumen',
    '4' => 'resumen_general',
    'resumen_general' => 'resumen_general',
    '5' => 'lineas_areas',
    'lineas_areas' => 'lineas_areas',
    '6' => 'criticas_tipos',
    'criticas_tipos' => 'criticas_tipos',
    '7' => 'reincidencias',
    'reincidencias' => 'reincidencias',
    '8' => 'reporte_detallado',
    'reporte_detallado' => 'reporte_detallado'
];

$operacion = $rutasReporte[$opcion] ?? '';

try {
    switch ($operacion) {
        case 'catalogos':
            /*
             * Catálogos iniciales del filtro. SPC_AREAS no maneja el campo
             * activo; los turnos sí se limitan a los registros activos.
             */
            $consultaAreas = $conn->prepare(
                "SELECT
                    idArea AS id_area,
                    nombreArea AS nombre_area
                 FROM SPC_AREAS
                 ORDER BY nombreArea, idArea"
            );
            $consultaAreas->execute();
            $areas = $consultaAreas->fetchAll(PDO::FETCH_ASSOC);
            $consultaAreas->closeCursor();

            /* La tabla existente en el proyecto se llama SA_turno. */
            $consultaTurnos = $conn->prepare(
                "SELECT
                    IDe AS id_turno,
                    LTRIM(RTRIM(CLAVE)) AS clave,
                    NOMBRE AS nombre_turno
                 FROM SA_turno
                 WHERE activo = 1
                 ORDER BY TRY_CONVERT(int, CLAVE), CLAVE, IDe"
            );
            $consultaTurnos->execute();
            $turnos = $consultaTurnos->fetchAll(PDO::FETCH_ASSOC);
            $consultaTurnos->closeCursor();

            responderReporteJson([
                'status' => 'ok',
                'data' => [
                    'areas' => $areas,
                    'turnos' => $turnos
                ]
            ]);

        case 'lineas_por_area':
            /* Las líneas se consultan únicamente para el área seleccionada. */
            $idArea = obtenerEnteroReportePost('idArea');

            if ($idArea === null) {
                responderReporteJson([
                    'status' => 'error',
                    'mensaje' => 'El área seleccionada no es válida.'
                ], 400);
            }

            $consultaLineas = $conn->prepare(
                "SELECT
                    L.codigo_linea,
                    L.nombre_linea
                 FROM SPC_LINEAS AS L
                 INNER JOIN SPC_AREAS AS A
                    ON A.idArea = L.idArea
                 WHERE L.idArea = :idArea
                 ORDER BY L.nombre_linea, L.codigo_linea"
            );
            $consultaLineas->execute([':idArea' => $idArea]);
            $lineas = $consultaLineas->fetchAll(PDO::FETCH_ASSOC);
            $consultaLineas->closeCursor();

            responderReporteJson([
                'status' => 'ok',
                'data' => $lineas
            ]);

        case 'tarjetas_resumen':
            /* ============================================================
               Tarjetas de resumen (KPI)
               ============================================================ */
            $filtros = obtenerFiltrosComunesReportePost();
            $rangoFechas = $filtros['rangoFechas'];
            $filtroConsulta = construirFiltroAnormalidadesReporte($filtros);
            $filtroSql = $filtroConsulta['sql'];
            $parametros = $filtroConsulta['parametros'];
            $fechaInicio = $rangoFechas['fechaInicio'];
            $fechaFin = $rangoFechas['fechaFin'];

            $sqlResumen = "SELECT
                    COUNT(*) AS total_anormalidades,
                    COALESCE(SUM(CASE
                        WHEN UPPER(LTRIM(RTRIM(C.NOMBRE)))
                             COLLATE Latin1_General_100_CI_AI IN (N'CRITICO', N'CRITICA')
                        THEN 1 ELSE 0 END), 0) AS anormalidades_criticas,
                    COALESCE(SUM(CASE
                        WHEN LTRIM(RTRIM(E.CLAVE)) = N'6'
                        THEN 1 ELSE 0 END), 0) AS anormalidades_finalizadas,
                    COALESCE(SUM(CASE
                        WHEN LTRIM(RTRIM(E.CLAVE)) <> N'6'
                        THEN 1 ELSE 0 END), 0) AS anormalidades_en_proceso
                FROM SA_anormalidad AS A
                INNER JOIN SA_estatus_anormalidad AS E
                    ON E.IDe = A.id_estatus_actual
                LEFT JOIN SA_primera_revision AS PR
                    ON PR.id_anormalidad = A.id_anormalidad
                LEFT JOIN SA_criticidad AS C
                    ON C.IDe = PR.id_criticidad
                {$filtroSql}";

            $consultaResumen = $conn->prepare($sqlResumen);
            $consultaResumen->execute($parametros);
            $resumen = $consultaResumen->fetch(PDO::FETCH_ASSOC);
            $consultaResumen->closeCursor();

            $total = (int) ($resumen['total_anormalidades'] ?? 0);
            $criticas = (int) ($resumen['anormalidades_criticas'] ?? 0);
            $finalizadas = (int) ($resumen['anormalidades_finalizadas'] ?? 0);
            $enProceso = (int) ($resumen['anormalidades_en_proceso'] ?? 0);

            responderReporteJson([
                'status' => 'ok',
                'data' => [
                    'total_anormalidades' => $total,
                    'anormalidades_criticas' => $criticas,
                    'porcentaje_criticas' => $total > 0
                        ? round(($criticas * 100) / $total, 1)
                        : 0,
                    'anormalidades_finalizadas' => $finalizadas,
                    'porcentaje_finalizadas' => $total > 0
                        ? round(($finalizadas * 100) / $total, 1)
                        : 0,
                    'anormalidades_en_proceso' => $enProceso,
                    'periodo' => [
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin,
                        'predeterminado' => $rangoFechas['predeterminado']
                    ]
                ]
            ]);

        /* ============================================================
           Reportes por pestaña
           Cada ruta se implementa progresivamente sin cambiar el contrato
           general de operación completa o sección independiente.
           ============================================================ */
        case 'resumen_general':
            /*
             * Los dos subtabs del histórico se pueden consultar por separado.
             * "completo" reúne exclusivamente los bloques ya implementados.
             */
            $seccionesResumen = [
                'completo', 'historico_total', 'historico_lineas',
                'areas', 'estatus', 'hallazgos'
            ];

            if (isset($_POST['seccion']) && is_array($_POST['seccion'])) {
                responderReporteJson([
                    'status' => 'error',
                    'mensaje' => 'La sección solicitada no tiene un formato válido.'
                ], 400);
            }

            $seccion = obtenerTextoReportePost('seccion') ?: 'completo';

            if (!in_array($seccion, $seccionesResumen, true)) {
                responderReporteJson([
                    'status' => 'error',
                    'mensaje' => 'La sección solicitada no es válida para el resumen general.'
                ], 400);
            }

            if (!in_array(
                $seccion,
                ['completo', 'historico_total', 'historico_lineas', 'areas', 'estatus', 'hallazgos'],
                true
            )) {
                responderReportePendiente($operacion, $seccionesResumen);
            }

            $filtros = obtenerFiltrosComunesReportePost();
            $filtroConsulta = construirFiltroAnormalidadesReporte($filtros);
            $filtroSql = $filtroConsulta['sql'];
            $parametros = $filtroConsulta['parametros'];
            $rangoFechas = $filtros['rangoFechas'];
            $datosResumen = [
                'periodo' => [
                    'fecha_inicio' => $rangoFechas['fechaInicio'],
                    'fecha_fin' => $rangoFechas['fechaFin'],
                    'predeterminado' => $rangoFechas['predeterminado']
                ]
            ];

            if ($seccion === 'completo' || $seccion === 'historico_total') {
                /* Primera vista: una sola suma por mes. */
                $sqlHistoricoTotal = "SELECT
                        YEAR(A.fecha_hora_deteccion) AS anio,
                        MONTH(A.fecha_hora_deteccion) AS mes,
                        COUNT_BIG(*) AS total
                    FROM SA_anormalidad AS A
                    {$filtroSql}
                    GROUP BY
                        YEAR(A.fecha_hora_deteccion),
                        MONTH(A.fecha_hora_deteccion)
                    ORDER BY
                        YEAR(A.fecha_hora_deteccion),
                        MONTH(A.fecha_hora_deteccion)";

                $consultaHistoricoTotal = $conn->prepare($sqlHistoricoTotal);
                $consultaHistoricoTotal->execute($parametros);
                $filasHistoricoTotal = $consultaHistoricoTotal->fetchAll(PDO::FETCH_ASSOC);
                $consultaHistoricoTotal->closeCursor();
                $historicoTotal = [];

                foreach ($filasHistoricoTotal as $fila) {
                    $historicoTotal[] = [
                        'anio' => (int) $fila['anio'],
                        'mes' => (int) $fila['mes'],
                        'total' => (int) $fila['total']
                    ];
                }

                $datosResumen['historico_total'] = $historicoTotal;
            }

            if ($seccion === 'completo' || $seccion === 'historico_lineas') {
                /*
                 * Segunda vista: una suma por mes y línea. Se incluye el área
                 * exclusivamente para mantener juntas sus líneas en la gráfica.
                 */
                $sqlHistoricoLineas = "SELECT
                        YEAR(A.fecha_hora_deteccion) AS anio,
                        MONTH(A.fecha_hora_deteccion) AS mes,
                        A.codigo_linea,
                        L.nombre_linea,
                        L.idArea AS id_area,
                        AR.nombreArea AS nombre_area,
                        COUNT_BIG(*) AS total
                    FROM SA_anormalidad AS A
                    INNER JOIN SPC_LINEAS AS L
                        ON L.codigo_linea = A.codigo_linea
                    INNER JOIN SPC_AREAS AS AR
                        ON AR.idArea = L.idArea
                    {$filtroSql}
                    GROUP BY
                        YEAR(A.fecha_hora_deteccion),
                        MONTH(A.fecha_hora_deteccion),
                        A.codigo_linea,
                        L.nombre_linea,
                        L.idArea,
                        AR.nombreArea
                    ORDER BY
                        AR.nombreArea,
                        L.nombre_linea,
                        A.codigo_linea,
                        YEAR(A.fecha_hora_deteccion),
                        MONTH(A.fecha_hora_deteccion)";

                $consultaHistoricoLineas = $conn->prepare($sqlHistoricoLineas);
                $consultaHistoricoLineas->execute($parametros);
                $filasHistoricoLineas = $consultaHistoricoLineas->fetchAll(PDO::FETCH_ASSOC);
                $consultaHistoricoLineas->closeCursor();
                $historicoLineas = [];

                foreach ($filasHistoricoLineas as $fila) {
                    $historicoLineas[] = [
                        'anio' => (int) $fila['anio'],
                        'mes' => (int) $fila['mes'],
                        'codigo_linea' => (string) $fila['codigo_linea'],
                        'nombre_linea' => (string) $fila['nombre_linea'],
                        'id_area' => (int) $fila['id_area'],
                        'nombre_area' => (string) $fila['nombre_area'],
                        'total' => (int) $fila['total']
                    ];
                }

                $datosResumen['historico_lineas'] = $historicoLineas;
            }

            if ($seccion === 'completo' || $seccion === 'areas') {
                /*
                 * Esta gráfica compara todas las áreas, por lo que ignora
                 * intencionalmente los filtros de área y línea. Sólo conserva
                 * el periodo y el turno seleccionados.
                 */
                $filtrosAreas = $filtros;
                $filtrosAreas['idArea'] = null;
                $filtrosAreas['codigoLinea'] = null;
                $filtroConsultaAreas = construirFiltroAnormalidadesReporte($filtrosAreas);

                /*
                 * SQL Server entrega un total por área. El LEFT JOIN exterior
                 * conserva también las áreas que no tienen registros.
                 */
                $sqlAreas = "WITH TotalesPorArea AS
                    (
                        SELECT
                            L.idArea,
                            COUNT_BIG(*) AS total
                        FROM SA_anormalidad AS A
                        INNER JOIN SPC_LINEAS AS L
                            ON L.codigo_linea = A.codigo_linea
                        {$filtroConsultaAreas['sql']}
                        GROUP BY L.idArea
                    )
                    SELECT
                        AR.nombreArea AS nombre_area,
                        COALESCE(TA.total, 0) AS total
                    FROM SPC_AREAS AS AR
                    LEFT JOIN TotalesPorArea AS TA
                        ON TA.idArea = AR.idArea
                    ORDER BY AR.nombreArea, AR.idArea";

                $consultaAreasResumen = $conn->prepare($sqlAreas);
                $consultaAreasResumen->execute($filtroConsultaAreas['parametros']);
                $filasAreasResumen = $consultaAreasResumen->fetchAll(PDO::FETCH_ASSOC);
                $consultaAreasResumen->closeCursor();
                $areasResumen = [];

                foreach ($filasAreasResumen as $fila) {
                    $areasResumen[] = [
                        'nombre_area' => (string) $fila['nombre_area'],
                        'total' => (int) $fila['total']
                    ];
                }

                $datosResumen['areas'] = $areasResumen;
            }

            if ($seccion === 'completo' || $seccion === 'estatus') {
                /*
                 * El CTE cuenta las anormalidades con todos los filtros. La
                 * consulta exterior conserva los seis estatus aunque alguno
                 * no tenga registros dentro del periodo solicitado.
                 */
                $sqlEstatus = "WITH TotalesPorEstatus AS
                    (
                        SELECT
                            A.id_estatus_actual,
                            COUNT_BIG(*) AS total
                        FROM SA_anormalidad AS A
                        {$filtroSql}
                        GROUP BY A.id_estatus_actual
                    )
                    SELECT
                        LTRIM(RTRIM(E.CLAVE)) AS clave,
                        E.NOMBRE AS nombre_estatus,
                        COALESCE(TE.total, 0) AS total
                    FROM SA_estatus_anormalidad AS E
                    LEFT JOIN TotalesPorEstatus AS TE
                        ON TE.id_estatus_actual = E.IDe
                    WHERE E.activo = 1
                      AND TRY_CONVERT(int, LTRIM(RTRIM(E.CLAVE))) BETWEEN 1 AND 6
                    ORDER BY TRY_CONVERT(int, LTRIM(RTRIM(E.CLAVE))), E.IDe";

                $consultaEstatus = $conn->prepare($sqlEstatus);
                $consultaEstatus->execute($parametros);
                $filasEstatus = $consultaEstatus->fetchAll(PDO::FETCH_ASSOC);
                $consultaEstatus->closeCursor();
                $estatusResumen = [];

                foreach ($filasEstatus as $fila) {
                    $estatusResumen[] = [
                        'clave' => (string) $fila['clave'],
                        'nombre_estatus' => (string) $fila['nombre_estatus'],
                        'total' => (int) $fila['total']
                    ];
                }

                $datosResumen['estatus'] = $estatusResumen;
            }

            if ($seccion === 'completo' || $seccion === 'hallazgos') {
                /*
                 * Hallazgo 1: la línea con más anormalidades críticas. Se
                 * ignora sólo el filtro de línea para poder compararlas dentro
                 * del área, turno y periodo seleccionados.
                 */
                $filtrosLineaCritica = $filtros;
                $filtrosLineaCritica['codigoLinea'] = null;
                $filtroLineaCritica = construirFiltroAnormalidadesReporte($filtrosLineaCritica);
                $sqlLineaCritica = "SELECT TOP (1)
                        A.codigo_linea,
                        L.nombre_linea,
                        COUNT_BIG(*) AS total_criticas,
                        CAST(
                            (COUNT_BIG(*) * 100.0) /
                            NULLIF(SUM(COUNT_BIG(*)) OVER (), 0)
                            AS decimal(6, 2)
                        ) AS porcentaje
                    FROM SA_anormalidad AS A
                    INNER JOIN SPC_LINEAS AS L
                        ON L.codigo_linea = A.codigo_linea
                    INNER JOIN SA_primera_revision AS PR
                        ON PR.id_anormalidad = A.id_anormalidad
                    INNER JOIN SA_criticidad AS C
                        ON C.IDe = PR.id_criticidad
                    {$filtroLineaCritica['sql']}
                      AND UPPER(LTRIM(RTRIM(C.NOMBRE)))
                          COLLATE Latin1_General_100_CI_AI IN (N'CRITICO', N'CRITICA')
                    GROUP BY A.codigo_linea, L.nombre_linea
                    ORDER BY COUNT_BIG(*) DESC, L.nombre_linea, A.codigo_linea";

                $consultaLineaCritica = $conn->prepare($sqlLineaCritica);
                $consultaLineaCritica->execute($filtroLineaCritica['parametros']);
                $filaLineaCritica = $consultaLineaCritica->fetch(PDO::FETCH_ASSOC);
                $consultaLineaCritica->closeCursor();
                $lineaCritica = null;

                if ($filaLineaCritica) {
                    $lineaCritica = [
                        'nombre_linea' => (string) $filaLineaCritica['nombre_linea'],
                        'total_criticas' => (int) $filaLineaCritica['total_criticas'],
                        'porcentaje' => (float) $filaLineaCritica['porcentaje']
                    ];
                }

                /*
                 * Hallazgo 2: cada 5M de la primera revisión es una fila en la
                 * tabla puente SA_primera_revision_5m. Esta consulta sí
                 * conserva todos los filtros, incluido el de línea.
                 */
                $sqlCategoria5m = "SELECT TOP (1)
                        M.NOMBRE AS nombre_categoria_5m,
                        COUNT_BIG(*) AS total_apariciones,
                        CAST(
                            (COUNT_BIG(*) * 100.0) /
                            NULLIF(SUM(COUNT_BIG(*)) OVER (), 0)
                            AS decimal(6, 2)
                        ) AS porcentaje
                    FROM SA_anormalidad AS A
                    INNER JOIN SA_primera_revision AS PR
                        ON PR.id_anormalidad = A.id_anormalidad
                    INNER JOIN SA_primera_revision_5m AS R5
                        ON R5.id_anormalidad = PR.id_anormalidad
                    INNER JOIN SA_5m AS M
                        ON M.IDe = R5.id_categoria_5m
                    {$filtroSql}
                    GROUP BY M.IDe, M.NOMBRE
                    ORDER BY COUNT_BIG(*) DESC, M.NOMBRE, M.IDe";

                $consultaCategoria5m = $conn->prepare($sqlCategoria5m);
                $consultaCategoria5m->execute($parametros);
                $filaCategoria5m = $consultaCategoria5m->fetch(PDO::FETCH_ASSOC);
                $consultaCategoria5m->closeCursor();
                $categoria5m = null;

                if ($filaCategoria5m) {
                    $categoria5m = [
                        'nombre_categoria_5m' => (string) $filaCategoria5m['nombre_categoria_5m'],
                        'total_apariciones' => (int) $filaCategoria5m['total_apariciones'],
                        'porcentaje' => (float) $filaCategoria5m['porcentaje']
                    ];
                }

                /*
                 * Hallazgo 3: tipo de anormalidad más frecuente. La agrupación
                 * se realiza completamente en SQL y aplica todos los filtros.
                 */
                $sqlTipoAnormalidad = "SELECT TOP (1)
                        TA.NOMBRE AS nombre_tipo_anormalidad,
                        COUNT_BIG(*) AS total_registros,
                        CAST(
                            (COUNT_BIG(*) * 100.0) /
                            NULLIF(SUM(COUNT_BIG(*)) OVER (), 0)
                            AS decimal(6, 2)
                        ) AS porcentaje
                    FROM SA_anormalidad AS A
                    INNER JOIN SA_tipo_anormalidad AS TA
                        ON TA.IDe = A.id_tipo_anormalidad
                    {$filtroSql}
                    GROUP BY TA.IDe, TA.NOMBRE
                    ORDER BY COUNT_BIG(*) DESC, TA.NOMBRE, TA.IDe";

                $consultaTipoAnormalidad = $conn->prepare($sqlTipoAnormalidad);
                $consultaTipoAnormalidad->execute($parametros);
                $filaTipoAnormalidad = $consultaTipoAnormalidad->fetch(PDO::FETCH_ASSOC);
                $consultaTipoAnormalidad->closeCursor();
                $tipoAnormalidad = null;

                if ($filaTipoAnormalidad) {
                    $tipoAnormalidad = [
                        'nombre_tipo_anormalidad' =>
                            (string) $filaTipoAnormalidad['nombre_tipo_anormalidad'],
                        'total_registros' => (int) $filaTipoAnormalidad['total_registros'],
                        'porcentaje' => (float) $filaTipoAnormalidad['porcentaje']
                    ];
                }

                $datosResumen['hallazgos'] = [
                    'linea_critica' => $lineaCritica,
                    'categoria_5m' => $categoria5m,
                    'tipo_anormalidad' => $tipoAnormalidad
                ];
            }

            responderReporteJson([
                'status' => 'ok',
                'data' => $datosResumen
            ]);

        case 'lineas_areas':
            $seccionesLineasAreas = [
                'completo', 'criticidad_por_linea', 'total_por_linea',
                'tendencia_por_area', 'clasificacion_5m'
            ];

            if (isset($_POST['seccion']) && is_array($_POST['seccion'])) {
                responderReporteJson([
                    'status' => 'error',
                    'mensaje' => 'La sección solicitada no tiene un formato válido.'
                ], 400);
            }

            $seccion = obtenerTextoReportePost('seccion') ?: 'completo';

            if (!in_array($seccion, $seccionesLineasAreas, true)) {
                responderReporteJson([
                    'status' => 'error',
                    'mensaje' => 'La sección solicitada no es válida para líneas y áreas.'
                ], 400);
            }

            if (!in_array(
                $seccion,
                [
                    'completo', 'criticidad_por_linea', 'total_por_linea',
                    'tendencia_por_area', 'clasificacion_5m'
                ],
                true
            )) {
                responderReportePendiente($operacion, $seccionesLineasAreas);
            }

            $filtros = obtenerFiltrosComunesReportePost();
            $filtrosComparativos = $filtros;
            $filtrosComparativos['codigoLinea'] = null;
            $filtroCriticidad = construirFiltroAnormalidadesReporte($filtrosComparativos);
            $parametrosCriticidad = $filtroCriticidad['parametros'];
            $filtroCatalogoLineas = '';
            $datosLineasAreas = [];

            /* Limita también el catálogo visible cuando se elige un área. */
            if ($filtros['idArea'] !== null) {
                $filtroCatalogoLineas = 'WHERE L.idArea = :idAreaCatalogo';
                $parametrosCriticidad[':idAreaCatalogo'] = $filtros['idArea'];
            }

            /*
             * SQL Server clasifica y agrupa antes de responder. El LEFT JOIN
             * conserva las líneas sin registros dentro del periodo filtrado.
             */
            if ($seccion === 'completo' || $seccion === 'criticidad_por_linea') {
                $sqlCriticidadPorLinea = "WITH CriticidadPorLinea AS
                (
                    SELECT
                        A.codigo_linea,
                        SUM(CASE
                            WHEN UPPER(LTRIM(RTRIM(C.NOMBRE)))
                                 COLLATE Latin1_General_100_CI_AI
                                 IN (N'CRITICO', N'CRITICA')
                            THEN CONVERT(bigint, 1) ELSE CONVERT(bigint, 0)
                        END) AS criticas,
                        SUM(CASE
                            WHEN UPPER(LTRIM(RTRIM(C.NOMBRE)))
                                 COLLATE Latin1_General_100_CI_AI
                                 IN (N'NO CRITICO', N'NO CRITICA')
                            THEN CONVERT(bigint, 1) ELSE CONVERT(bigint, 0)
                        END) AS no_criticas
                    FROM SA_anormalidad AS A
                    INNER JOIN SA_primera_revision AS PR
                        ON PR.id_anormalidad = A.id_anormalidad
                    INNER JOIN SA_criticidad AS C
                        ON C.IDe = PR.id_criticidad
                    {$filtroCriticidad['sql']}
                    GROUP BY A.codigo_linea
                )
                SELECT
                    L.nombre_linea,
                    COALESCE(CPL.criticas, 0) AS criticas,
                    COALESCE(CPL.no_criticas, 0) AS no_criticas
                FROM SPC_LINEAS AS L
                INNER JOIN SPC_AREAS AS AR
                    ON AR.idArea = L.idArea
                LEFT JOIN CriticidadPorLinea AS CPL
                    ON CPL.codigo_linea = L.codigo_linea
                {$filtroCatalogoLineas}
                ORDER BY AR.nombreArea, L.nombre_linea, L.codigo_linea";

                $consultaCriticidad = $conn->prepare($sqlCriticidadPorLinea);
                $consultaCriticidad->execute($parametrosCriticidad);
                $filasCriticidad = $consultaCriticidad->fetchAll(PDO::FETCH_ASSOC);
                $consultaCriticidad->closeCursor();
                $criticidadPorLinea = [];

                foreach ($filasCriticidad as $fila) {
                    $criticidadPorLinea[] = [
                        'nombre_linea' => (string) $fila['nombre_linea'],
                        'criticas' => (int) $fila['criticas'],
                        'no_criticas' => (int) $fila['no_criticas']
                    ];
                }

                $datosLineasAreas['criticidad_por_linea'] = $criticidadPorLinea;
            }

            if ($seccion === 'completo' || $seccion === 'total_por_linea') {
                /*
                 * SQL Server entrega solamente el total y porcentaje por línea.
                 * El filtro de línea se omite para conservar el comparativo.
                 */
                $sqlTotalPorLinea = "WITH TotalesPorLinea AS
                (
                    SELECT
                        A.codigo_linea,
                        COUNT_BIG(*) AS total
                    FROM SA_anormalidad AS A
                    {$filtroCriticidad['sql']}
                    GROUP BY A.codigo_linea
                ),
                TotalFiltrado AS
                (
                    SELECT COALESCE(SUM(total), 0) AS total
                    FROM TotalesPorLinea
                )
                SELECT
                    L.nombre_linea,
                    AR.nombreArea AS nombre_area,
                    COALESCE(TPL.total, 0) AS total,
                    CAST(
                        CASE
                            WHEN TF.total > 0
                            THEN COALESCE(TPL.total, 0) * 100.0 / TF.total
                            ELSE 0
                        END
                        AS decimal(6, 2)
                    ) AS porcentaje
                FROM SPC_LINEAS AS L
                INNER JOIN SPC_AREAS AS AR
                    ON AR.idArea = L.idArea
                LEFT JOIN TotalesPorLinea AS TPL
                    ON TPL.codigo_linea = L.codigo_linea
                CROSS JOIN TotalFiltrado AS TF
                {$filtroCatalogoLineas}
                ORDER BY AR.nombreArea, L.nombre_linea, L.codigo_linea";

                $consultaTotales = $conn->prepare($sqlTotalPorLinea);
                $consultaTotales->execute($parametrosCriticidad);
                $filasTotales = $consultaTotales->fetchAll(PDO::FETCH_ASSOC);
                $consultaTotales->closeCursor();
                $totalPorLinea = [];

                foreach ($filasTotales as $fila) {
                    $totalPorLinea[] = [
                        'nombre_linea' => (string) $fila['nombre_linea'],
                        'nombre_area' => (string) $fila['nombre_area'],
                        'total' => (int) $fila['total'],
                        'porcentaje' => (float) $fila['porcentaje']
                    ];
                }

                $datosLineasAreas['total_por_linea'] = $totalPorLinea;
            }

            if ($seccion === 'completo' || $seccion === 'tendencia_por_area') {
                $filtroTendencia = construirFiltroAnormalidadesReporte($filtros);
                $parametrosTendencia = $filtroTendencia['parametros'];
                $rangoTendencia = $filtros['rangoFechas'];
                $nivelTendencia = 'area';

                if ($filtros['idArea'] === null && $filtros['codigoLinea'] === null) {
                    /* Sin área seleccionada se entrega una serie mensual por área. */
                    $sqlTendencia = "WITH TendenciaMensual AS
                    (
                        SELECT
                            L.idArea AS clave_serie,
                            YEAR(A.fecha_hora_deteccion) AS anio,
                            MONTH(A.fecha_hora_deteccion) AS mes,
                            COUNT_BIG(*) AS total
                        FROM SA_anormalidad AS A
                        INNER JOIN SPC_LINEAS AS L
                            ON L.codigo_linea = A.codigo_linea
                        {$filtroTendencia['sql']}
                        GROUP BY
                            L.idArea,
                            YEAR(A.fecha_hora_deteccion),
                            MONTH(A.fecha_hora_deteccion)
                    )
                    SELECT
                        CONVERT(nvarchar(100), AR.idArea) AS clave_serie,
                        AR.nombreArea AS nombre_serie,
                        TM.anio,
                        TM.mes,
                        COALESCE(TM.total, 0) AS total
                    FROM SPC_AREAS AS AR
                    LEFT JOIN TendenciaMensual AS TM
                        ON TM.clave_serie = AR.idArea
                    ORDER BY
                        AR.nombreArea,
                        AR.idArea,
                        TM.anio,
                        TM.mes";
                } else {
                    /*
                     * Con un área se comparan sus líneas; si además se elige
                     * una línea, el catálogo exterior conserva sólo esa serie.
                     */
                    $nivelTendencia = 'linea';
                    $condicionesCatalogoTendencia = [];

                    if ($filtros['idArea'] !== null) {
                        $condicionesCatalogoTendencia[] = 'L.idArea = :idAreaCatalogoTendencia';
                        $parametrosTendencia[':idAreaCatalogoTendencia'] = $filtros['idArea'];
                    }

                    if ($filtros['codigoLinea'] !== null) {
                        $condicionesCatalogoTendencia[] = 'L.codigo_linea = :codigoLineaCatalogoTendencia';
                        $parametrosTendencia[':codigoLineaCatalogoTendencia'] = $filtros['codigoLinea'];
                    }

                    $filtroCatalogoTendencia = $condicionesCatalogoTendencia
                        ? 'WHERE ' . implode(' AND ', $condicionesCatalogoTendencia)
                        : '';

                    $sqlTendencia = "WITH TendenciaMensual AS
                    (
                        SELECT
                            A.codigo_linea AS clave_serie,
                            YEAR(A.fecha_hora_deteccion) AS anio,
                            MONTH(A.fecha_hora_deteccion) AS mes,
                            COUNT_BIG(*) AS total
                        FROM SA_anormalidad AS A
                        {$filtroTendencia['sql']}
                        GROUP BY
                            A.codigo_linea,
                            YEAR(A.fecha_hora_deteccion),
                            MONTH(A.fecha_hora_deteccion)
                    )
                    SELECT
                        L.codigo_linea AS clave_serie,
                        L.nombre_linea AS nombre_serie,
                        TM.anio,
                        TM.mes,
                        COALESCE(TM.total, 0) AS total
                    FROM SPC_LINEAS AS L
                    INNER JOIN SPC_AREAS AS AR
                        ON AR.idArea = L.idArea
                    LEFT JOIN TendenciaMensual AS TM
                        ON TM.clave_serie = L.codigo_linea
                    {$filtroCatalogoTendencia}
                    ORDER BY
                        AR.nombreArea,
                        L.nombre_linea,
                        L.codigo_linea,
                        TM.anio,
                        TM.mes";
                }

                $consultaTendencia = $conn->prepare($sqlTendencia);
                $consultaTendencia->execute($parametrosTendencia);
                $filasTendencia = $consultaTendencia->fetchAll(PDO::FETCH_ASSOC);
                $consultaTendencia->closeCursor();
                $tendencia = [];

                foreach ($filasTendencia as $fila) {
                    $tendencia[] = [
                        'clave_serie' => (string) $fila['clave_serie'],
                        'nombre_serie' => (string) $fila['nombre_serie'],
                        'anio' => $fila['anio'] === null ? null : (int) $fila['anio'],
                        'mes' => $fila['mes'] === null ? null : (int) $fila['mes'],
                        'total' => (int) $fila['total']
                    ];
                }

                $datosLineasAreas['tendencia_por_area'] = [
                    'nivel' => $nivelTendencia,
                    'periodo' => [
                        'fecha_inicio' => $rangoTendencia['fechaInicio'],
                        'fecha_fin' => $rangoTendencia['fechaFin']
                    ],
                    'filas' => $tendencia
                ];
            }

            if ($seccion === 'completo' || $seccion === 'clasificacion_5m') {
                $filtroClasificacion5m = construirFiltroAnormalidadesReporte($filtros);

                /*
                 * Las 5M de cada anormalidad salen de la tabla puente
                 * SA_primera_revision_5m. Su PK (anormalidad, categoría) ya
                 * impide contar dos veces la misma categoría.
                 */
                $sqlClasificacion5m = "WITH CategoriasPorAnormalidad AS
                (
                    SELECT
                        A.id_anormalidad,
                        R5.id_categoria_5m
                    FROM SA_anormalidad AS A
                    INNER JOIN SA_primera_revision AS PR
                        ON PR.id_anormalidad = A.id_anormalidad
                    INNER JOIN SA_primera_revision_5m AS R5
                        ON R5.id_anormalidad = PR.id_anormalidad
                    {$filtroClasificacion5m['sql']}
                ),
                TotalesPorCategoria AS
                (
                    SELECT
                        CPA.id_categoria_5m,
                        COUNT_BIG(*) AS total
                    FROM CategoriasPorAnormalidad AS CPA
                    INNER JOIN SA_5m AS M5
                        ON M5.IDe = CPA.id_categoria_5m
                       AND M5.activo = 1
                    WHERE CPA.id_categoria_5m IS NOT NULL
                    GROUP BY CPA.id_categoria_5m
                ),
                TotalClasificaciones AS
                (
                    SELECT COALESCE(SUM(total), 0) AS total
                    FROM TotalesPorCategoria
                )
                SELECT
                    M.NOMBRE AS nombre_categoria_5m,
                    COALESCE(TPC.total, 0) AS total,
                    CAST(
                        CASE
                            WHEN TC.total > 0
                            THEN COALESCE(TPC.total, 0) * 100.0 / TC.total
                            ELSE 0
                        END
                        AS decimal(6, 2)
                    ) AS porcentaje
                FROM SA_5m AS M
                LEFT JOIN TotalesPorCategoria AS TPC
                    ON TPC.id_categoria_5m = M.IDe
                CROSS JOIN TotalClasificaciones AS TC
                WHERE M.activo = 1
                ORDER BY
                    COALESCE(TPC.total, 0) DESC,
                    M.NOMBRE,
                    M.IDe";

                $consultaClasificacion5m = $conn->prepare($sqlClasificacion5m);
                $consultaClasificacion5m->execute($filtroClasificacion5m['parametros']);
                $filasClasificacion5m = $consultaClasificacion5m->fetchAll(PDO::FETCH_ASSOC);
                $consultaClasificacion5m->closeCursor();
                $clasificacion5m = [];

                foreach ($filasClasificacion5m as $fila) {
                    $clasificacion5m[] = [
                        'nombre_categoria_5m' => (string) $fila['nombre_categoria_5m'],
                        'total' => (int) $fila['total'],
                        'porcentaje' => (float) $fila['porcentaje']
                    ];
                }

                $datosLineasAreas['clasificacion_5m'] = $clasificacion5m;
            }

            responderReporteJson([
                'status' => 'ok',
                'data' => $datosLineasAreas
            ]);

        case 'criticas_tipos':
            $seccionesCriticasTipos = [
                'completo', 'clasificacion_5m', 'factores', 'top_criticas',
                'top_no_criticas', 'nivel_respuesta', 'estatus_criticas'
            ];

            if (isset($_POST['seccion']) && is_array($_POST['seccion'])) {
                responderReporteJson([
                    'status' => 'error',
                    'mensaje' => 'La sección solicitada no tiene un formato válido.'
                ], 400);
            }

            $seccion = obtenerTextoReportePost('seccion') ?: 'completo';

            if (!in_array($seccion, $seccionesCriticasTipos, true)) {
                responderReporteJson([
                    'status' => 'error',
                    'mensaje' => 'La sección solicitada no es válida para críticas y tipos.'
                ], 400);
            }

            if (!in_array(
                $seccion,
                [
                    'completo', 'top_criticas', 'top_no_criticas',
                    'nivel_respuesta', 'estatus_criticas'
                ],
                true
            )) {
                responderReportePendiente($operacion, $seccionesCriticasTipos);
            }

            $filtros = obtenerFiltrosComunesReportePost();
            $datosCriticasTipos = [];

            if ($seccion === 'completo' || $seccion === 'top_criticas') {
                $filtroTopCriticas = construirFiltroAnormalidadesReporte($filtros);

                /*
                 * SQL Server filtra las anormalidades críticas y entrega sólo
                 * los cinco tipos con mayor frecuencia para la gráfica.
                 */
                $sqlTopCriticas = "SELECT TOP (5)
                        TA.NOMBRE AS nombre_tipo_anormalidad,
                        COUNT_BIG(*) AS total
                    FROM SA_anormalidad AS A
                    INNER JOIN SA_primera_revision AS PR
                        ON PR.id_anormalidad = A.id_anormalidad
                    INNER JOIN SA_criticidad AS C
                        ON C.IDe = PR.id_criticidad
                    INNER JOIN SA_tipo_anormalidad AS TA
                        ON TA.IDe = A.id_tipo_anormalidad
                    {$filtroTopCriticas['sql']}
                      AND UPPER(LTRIM(RTRIM(C.NOMBRE)))
                          COLLATE Latin1_General_100_CI_AI IN (N'CRITICO', N'CRITICA')
                      AND C.activo = 1
                      AND TA.activo = 1
                    GROUP BY TA.IDe, TA.NOMBRE
                    ORDER BY
                        COUNT_BIG(*) DESC,
                        TA.NOMBRE,
                        TA.IDe";

                $consultaTopCriticas = $conn->prepare($sqlTopCriticas);
                $consultaTopCriticas->execute($filtroTopCriticas['parametros']);
                $filasTopCriticas = $consultaTopCriticas->fetchAll(PDO::FETCH_ASSOC);
                $consultaTopCriticas->closeCursor();
                $topCriticas = [];

                foreach ($filasTopCriticas as $fila) {
                    $topCriticas[] = [
                        'nombre_tipo_anormalidad' => (string) $fila['nombre_tipo_anormalidad'],
                        'total' => (int) $fila['total']
                    ];
                }

                $datosCriticasTipos['top_criticas'] = $topCriticas;
            }

            if ($seccion === 'completo' || $seccion === 'top_no_criticas') {
                $filtroTopNoCriticas = construirFiltroAnormalidadesReporte($filtros);

                /* SQL Server devuelve únicamente los cinco tipos no críticos más frecuentes. */
                $sqlTopNoCriticas = "SELECT TOP (5)
                        TA.NOMBRE AS nombre_tipo_anormalidad,
                        COUNT_BIG(*) AS total
                    FROM SA_anormalidad AS A
                    INNER JOIN SA_primera_revision AS PR
                        ON PR.id_anormalidad = A.id_anormalidad
                    INNER JOIN SA_criticidad AS C
                        ON C.IDe = PR.id_criticidad
                    INNER JOIN SA_tipo_anormalidad AS TA
                        ON TA.IDe = A.id_tipo_anormalidad
                    {$filtroTopNoCriticas['sql']}
                      AND UPPER(LTRIM(RTRIM(C.NOMBRE)))
                          COLLATE Latin1_General_100_CI_AI
                          IN (N'NO CRITICO', N'NO CRITICA')
                      AND C.activo = 1
                      AND TA.activo = 1
                    GROUP BY TA.IDe, TA.NOMBRE
                    ORDER BY
                        COUNT_BIG(*) DESC,
                        TA.NOMBRE,
                        TA.IDe";

                $consultaTopNoCriticas = $conn->prepare($sqlTopNoCriticas);
                $consultaTopNoCriticas->execute($filtroTopNoCriticas['parametros']);
                $filasTopNoCriticas = $consultaTopNoCriticas->fetchAll(PDO::FETCH_ASSOC);
                $consultaTopNoCriticas->closeCursor();
                $topNoCriticas = [];

                foreach ($filasTopNoCriticas as $fila) {
                    $topNoCriticas[] = [
                        'nombre_tipo_anormalidad' => (string) $fila['nombre_tipo_anormalidad'],
                        'total' => (int) $fila['total']
                    ];
                }

                $datosCriticasTipos['top_no_criticas'] = $topNoCriticas;
            }

            if ($seccion === 'completo' || $seccion === 'nivel_respuesta') {
                $filtroNivelRespuesta = construirFiltroAnormalidadesReporte($filtros);

                /*
                 * SQL Server cuenta una anormalidad por su nivel asignado. El
                 * catálogo exterior conserva los niveles activos sin registros.
                 */
                $sqlNivelRespuesta = "WITH TotalesPorNivel AS
                (
                    SELECT
                        PR.id_nivel_respuesta,
                        COUNT_BIG(*) AS total
                    FROM SA_anormalidad AS A
                    INNER JOIN SA_primera_revision AS PR
                        ON PR.id_anormalidad = A.id_anormalidad
                    INNER JOIN SA_nivel_respuesta AS NRC
                        ON NRC.IDe = PR.id_nivel_respuesta
                       AND NRC.activo = 1
                    {$filtroNivelRespuesta['sql']}
                    GROUP BY PR.id_nivel_respuesta
                ),
                TotalClasificado AS
                (
                    SELECT COALESCE(SUM(total), 0) AS total
                    FROM TotalesPorNivel
                )
                SELECT
                    NR.NOMBRE AS nombre_nivel_respuesta,
                    COALESCE(TPN.total, 0) AS total,
                    CAST(
                        CASE
                            WHEN TC.total > 0
                            THEN COALESCE(TPN.total, 0) * 100.0 / TC.total
                            ELSE 0
                        END
                        AS decimal(6, 2)
                    ) AS porcentaje
                FROM SA_nivel_respuesta AS NR
                LEFT JOIN TotalesPorNivel AS TPN
                    ON TPN.id_nivel_respuesta = NR.IDe
                CROSS JOIN TotalClasificado AS TC
                WHERE NR.activo = 1
                ORDER BY
                    TRY_CONVERT(int, NR.CLAVE),
                    NR.CLAVE,
                    NR.IDe";

                $consultaNivelRespuesta = $conn->prepare($sqlNivelRespuesta);
                $consultaNivelRespuesta->execute($filtroNivelRespuesta['parametros']);
                $filasNivelRespuesta = $consultaNivelRespuesta->fetchAll(PDO::FETCH_ASSOC);
                $consultaNivelRespuesta->closeCursor();
                $nivelesRespuesta = [];

                foreach ($filasNivelRespuesta as $fila) {
                    $nivelesRespuesta[] = [
                        'nombre_nivel_respuesta' => (string) $fila['nombre_nivel_respuesta'],
                        'total' => (int) $fila['total'],
                        'porcentaje' => (float) $fila['porcentaje']
                    ];
                }

                $datosCriticasTipos['nivel_respuesta'] = $nivelesRespuesta;
            }

            if ($seccion === 'completo' || $seccion === 'estatus_criticas') {
                $inicio = obtenerEnteroRangoReportePost('inicio', 0, 0, 100000000);
                $longitud = obtenerEnteroRangoReportePost('longitud', 10, 1, 50);

                if ($inicio === null || $longitud === null) {
                    responderReporteJson([
                        'status' => 'error',
                        'mensaje' => 'La paginación solicitada no es válida.'
                    ], 400);
                }

                if (isset($_POST['busqueda']) && is_array($_POST['busqueda'])) {
                    responderReporteJson([
                        'status' => 'error',
                        'mensaje' => 'La búsqueda solicitada no tiene un formato válido.'
                    ], 400);
                }

                $busqueda = obtenerTextoReportePost('busqueda') ?? '';

                if (strlen($busqueda) > 100 || preg_match('/[\x00-\x1F\x7F]/', $busqueda)) {
                    responderReporteJson([
                        'status' => 'error',
                        'mensaje' => 'El texto de búsqueda no es válido.'
                    ], 400);
                }

                $filtroEstatusCriticas = construirFiltroAnormalidadesReporte($filtros);
                $desdeEstatusCriticas = "FROM SA_anormalidad AS A
                    INNER JOIN SA_primera_revision AS PR
                        ON PR.id_anormalidad = A.id_anormalidad
                    INNER JOIN SA_criticidad AS C
                        ON C.IDe = PR.id_criticidad
                    INNER JOIN SA_tipo_anormalidad AS TA
                        ON TA.IDe = A.id_tipo_anormalidad
                    INNER JOIN SA_estatus_anormalidad AS E
                        ON E.IDe = A.id_estatus_actual
                    INNER JOIN SPC_LINEAS AS L
                        ON L.codigo_linea = A.codigo_linea
                    INNER JOIN SPC_AREAS AS AR
                        ON AR.idArea = L.idArea
                    LEFT JOIN SA_turno AS T
                        ON T.IDe = A.turno";
                $condicionBaseEstatusCriticas = "{$filtroEstatusCriticas['sql']}
                      AND UPPER(LTRIM(RTRIM(C.NOMBRE)))
                          COLLATE Latin1_General_100_CI_AI IN (N'CRITICO', N'CRITICA')
                      AND C.activo = 1
                      AND LTRIM(RTRIM(E.CLAVE)) <> N'6'";

                /*
                 * El total se calcula en SQL Server y representa solamente
                 * anormalidades críticas no finalizadas dentro de los filtros.
                 */
                $consultaTotalEstatusCriticas = $conn->prepare(
                    "SELECT COUNT_BIG(*) AS total
                     {$desdeEstatusCriticas}
                     {$condicionBaseEstatusCriticas}"
                );
                $consultaTotalEstatusCriticas->execute($filtroEstatusCriticas['parametros']);
                $totalEstatusCriticas = (int) $consultaTotalEstatusCriticas->fetchColumn();
                $consultaTotalEstatusCriticas->closeCursor();

                $condicionBusquedaEstatusCriticas = '';
                $parametrosListadoEstatusCriticas = $filtroEstatusCriticas['parametros'];

                if ($busqueda !== '') {
                    $condicionBusquedaEstatusCriticas = " AND
                        (
                            A.no_seguimiento LIKE :buscarSeguimiento
                            OR CONVERT(nvarchar(16), A.fecha_hora_deteccion, 120)
                                LIKE :buscarFecha
                            OR COALESCE(NULLIF(T.NOMBRE, N''), NULLIF(T.CLAVE, N''),
                                CONVERT(nvarchar(20), A.turno)) LIKE :buscarTurno
                            OR AR.nombreArea LIKE :buscarArea
                            OR L.nombre_linea LIKE :buscarLinea
                            OR A.codigo_linea LIKE :buscarCodigoLinea
                            OR TA.NOMBRE LIKE :buscarTipo
                            OR E.NOMBRE LIKE :buscarEstatus
                        )";
                    $textoBusqueda = '%' . $busqueda . '%';
                    $parametrosListadoEstatusCriticas += [
                        ':buscarSeguimiento' => $textoBusqueda,
                        ':buscarFecha' => $textoBusqueda,
                        ':buscarTurno' => $textoBusqueda,
                        ':buscarArea' => $textoBusqueda,
                        ':buscarLinea' => $textoBusqueda,
                        ':buscarCodigoLinea' => $textoBusqueda,
                        ':buscarTipo' => $textoBusqueda,
                        ':buscarEstatus' => $textoBusqueda
                    ];
                }

                if ($busqueda === '') {
                    $totalFiltradoEstatusCriticas = $totalEstatusCriticas;
                } else {
                    $consultaFiltradoEstatusCriticas = $conn->prepare(
                        "SELECT COUNT_BIG(*) AS total
                         {$desdeEstatusCriticas}
                         {$condicionBaseEstatusCriticas}
                         {$condicionBusquedaEstatusCriticas}"
                    );
                    $consultaFiltradoEstatusCriticas->execute($parametrosListadoEstatusCriticas);
                    $totalFiltradoEstatusCriticas =
                        (int) $consultaFiltradoEstatusCriticas->fetchColumn();
                    $consultaFiltradoEstatusCriticas->closeCursor();
                }

                /*
                 * OFFSET/FETCH limita la respuesta a la página solicitada por
                 * DataTables; el navegador nunca recibe el listado completo.
                 */
                $sqlListadoEstatusCriticas = "SELECT
                        A.no_seguimiento,
                        CONVERT(varchar(16), A.fecha_hora_deteccion, 120)
                            AS fecha_hora_deteccion,
                        COALESCE(NULLIF(T.NOMBRE, N''), NULLIF(T.CLAVE, N''),
                            CONVERT(nvarchar(20), A.turno)) AS turno,
                        AR.nombreArea AS nombre_area,
                        L.nombre_linea,
                        TA.NOMBRE AS tipo_anormalidad,
                        E.NOMBRE AS estatus
                    {$desdeEstatusCriticas}
                    {$condicionBaseEstatusCriticas}
                    {$condicionBusquedaEstatusCriticas}
                    ORDER BY A.fecha_hora_deteccion DESC, A.id_anormalidad DESC
                    OFFSET :inicio ROWS FETCH NEXT :longitud ROWS ONLY";
                $consultaListadoEstatusCriticas = $conn->prepare($sqlListadoEstatusCriticas);

                foreach ($parametrosListadoEstatusCriticas as $nombre => $valor) {
                    $consultaListadoEstatusCriticas->bindValue($nombre, $valor, PDO::PARAM_STR);
                }

                $consultaListadoEstatusCriticas->bindValue(':inicio', $inicio, PDO::PARAM_INT);
                $consultaListadoEstatusCriticas->bindValue(':longitud', $longitud, PDO::PARAM_INT);
                $consultaListadoEstatusCriticas->execute();
                $filasEstatusCriticas =
                    $consultaListadoEstatusCriticas->fetchAll(PDO::FETCH_ASSOC);
                $consultaListadoEstatusCriticas->closeCursor();
                $registrosEstatusCriticas = [];

                foreach ($filasEstatusCriticas as $fila) {
                    $registrosEstatusCriticas[] = [
                        'no_seguimiento' => (string) $fila['no_seguimiento'],
                        'fecha_hora_deteccion' => (string) $fila['fecha_hora_deteccion'],
                        'turno' => (string) $fila['turno'],
                        'nombre_area' => (string) $fila['nombre_area'],
                        'nombre_linea' => (string) $fila['nombre_linea'],
                        'tipo_anormalidad' => (string) $fila['tipo_anormalidad'],
                        'estatus' => (string) $fila['estatus']
                    ];
                }

                $datosCriticasTipos['estatus_criticas'] = [
                    'total_registros' => $totalEstatusCriticas,
                    'total_filtrados' => $totalFiltradoEstatusCriticas,
                    'registros' => $registrosEstatusCriticas
                ];
            }

            responderReporteJson([
                'status' => 'ok',
                'data' => $datosCriticasTipos
            ]);

        case 'reincidencias':
            $seccionesReincidencias = [
                'completo', 'top_reincidentes', 'top_estaciones',
                'catalogo_tipos', 'frecuentes', 'detalle_contramedidas'
            ];

            if (isset($_POST['seccion']) && is_array($_POST['seccion'])) {
                responderReporteJson([
                    'status' => 'error',
                    'mensaje' => 'La sección solicitada no tiene un formato válido.'
                ], 400);
            }

            $seccion = obtenerTextoReportePost('seccion') ?: 'completo';

            if (!in_array($seccion, $seccionesReincidencias, true)) {
                responderReporteJson([
                    'status' => 'error',
                    'mensaje' => 'La sección solicitada no es válida para reincidencias.'
                ], 400);
            }

            if (!in_array(
                $seccion,
                [
                    'completo', 'top_reincidentes', 'top_estaciones',
                    'catalogo_tipos', 'frecuentes', 'detalle_contramedidas'
                ],
                true
            )) {
                responderReportePendiente($operacion, $seccionesReincidencias);
            }

            $filtros = obtenerFiltrosComunesReportePost();
            $datosReincidencias = [];

            if ($seccion === 'completo' || $seccion === 'catalogo_tipos') {
                /* El selector sólo necesita identificador y nombre de registros activos. */
                $consultaCatalogoTipos = $conn->prepare(
                    "SELECT
                        IDe AS id_tipo_anormalidad,
                        NOMBRE AS nombre_tipo_anormalidad
                     FROM SA_tipo_anormalidad
                     WHERE activo = 1
                     ORDER BY NOMBRE, IDe"
                );
                $consultaCatalogoTipos->execute();
                $filasCatalogoTipos = $consultaCatalogoTipos->fetchAll(PDO::FETCH_ASSOC);
                $consultaCatalogoTipos->closeCursor();
                $catalogoTipos = [];

                foreach ($filasCatalogoTipos as $fila) {
                    $catalogoTipos[] = [
                        'id_tipo_anormalidad' => (int) $fila['id_tipo_anormalidad'],
                        'nombre_tipo_anormalidad' =>
                            (string) $fila['nombre_tipo_anormalidad']
                    ];
                }

                $datosReincidencias['catalogo_tipos'] = $catalogoTipos;
            }

            if ($seccion === 'completo' || $seccion === 'top_reincidentes') {
                $filtroTopReincidentes = construirFiltroAnormalidadesReporte($filtros);

                /*
                 * EXISTS cuenta cada anormalidad una sola vez aunque la tabla
                 * relacionada llegara a contener más de una coincidencia.
                 * SQL Server devuelve exclusivamente los cinco tipos con más
                 * anormalidades que tienen un registro general de reincidencia.
                 */
                $sqlTopReincidentes = "SELECT TOP (5)
                        TA.NOMBRE AS nombre_tipo_anormalidad,
                        COUNT_BIG(*) AS total
                    FROM SA_anormalidad AS A
                    INNER JOIN SA_tipo_anormalidad AS TA
                        ON TA.IDe = A.id_tipo_anormalidad
                    {$filtroTopReincidentes['sql']}
                      AND TA.activo = 1
                      AND EXISTS
                          (
                              SELECT 1
                              FROM SA_registro_general_reincidencia AS RGR
                              WHERE RGR.id_anormalidad = A.id_anormalidad
                          )
                    GROUP BY TA.IDe, TA.NOMBRE
                    ORDER BY
                        COUNT_BIG(*) DESC,
                        TA.NOMBRE,
                        TA.IDe";

                $consultaTopReincidentes = $conn->prepare($sqlTopReincidentes);
                $consultaTopReincidentes->execute($filtroTopReincidentes['parametros']);
                $filasTopReincidentes = $consultaTopReincidentes->fetchAll(PDO::FETCH_ASSOC);
                $consultaTopReincidentes->closeCursor();
                $topReincidentes = [];

                foreach ($filasTopReincidentes as $fila) {
                    $topReincidentes[] = [
                        'nombre_tipo_anormalidad' =>
                            (string) $fila['nombre_tipo_anormalidad'],
                        'total' => (int) $fila['total']
                    ];
                }

                $datosReincidencias['top_reincidentes'] = $topReincidentes;
            }

            if ($seccion === 'completo' || $seccion === 'top_estaciones') {
                $filtroTopEstaciones = construirFiltroAnormalidadesReporte($filtros);

                /*
                 * La estación se toma del registro original de la anormalidad.
                 * EXISTS evita multiplicar el conteo por registros relacionados
                 * y TOP limita la respuesta a lo que utiliza la gráfica.
                 */
                $sqlTopEstaciones = "SELECT TOP (5)
                        E.nombre_estacion,
                        E.codigo_linea,
                        COUNT_BIG(*) AS total
                    FROM SA_anormalidad AS A
                    INNER JOIN SPC_ESTACIONES AS E
                        ON E.id_estacion = A.id_estacion_generacion
                       AND E.codigo_linea = A.codigo_linea
                    {$filtroTopEstaciones['sql']}
                      AND EXISTS
                          (
                              SELECT 1
                              FROM SA_registro_general_reincidencia AS RGR
                              WHERE RGR.id_anormalidad = A.id_anormalidad
                          )
                    GROUP BY
                        E.id_estacion,
                        E.nombre_estacion,
                        E.codigo_linea
                    ORDER BY
                        COUNT_BIG(*) DESC,
                        E.nombre_estacion,
                        E.id_estacion";

                $consultaTopEstaciones = $conn->prepare($sqlTopEstaciones);
                $consultaTopEstaciones->execute($filtroTopEstaciones['parametros']);
                $filasTopEstaciones = $consultaTopEstaciones->fetchAll(PDO::FETCH_ASSOC);
                $consultaTopEstaciones->closeCursor();
                $topEstaciones = [];

                foreach ($filasTopEstaciones as $fila) {
                    $topEstaciones[] = [
                        'nombre_estacion' => (string) $fila['nombre_estacion'],
                        'codigo_linea' => (string) $fila['codigo_linea'],
                        'total' => (int) $fila['total']
                    ];
                }

                $datosReincidencias['top_estaciones'] = $topEstaciones;
            }

            if ($seccion === 'completo' || $seccion === 'frecuentes') {
                if (isset($_POST['idTipoAnormalidad']) &&
                    is_array($_POST['idTipoAnormalidad'])) {
                    responderReporteJson([
                        'status' => 'error',
                        'mensaje' => 'El tipo de anormalidad seleccionado no tiene un formato válido.'
                    ], 400);
                }

                $idTipoTexto = obtenerTextoReportePost('idTipoAnormalidad');
                $idTipoAnormalidad = null;

                if ($idTipoTexto !== null && $idTipoTexto !== '') {
                    $idTipoAnormalidad = obtenerEnteroReportePost('idTipoAnormalidad');

                    if ($idTipoAnormalidad === null) {
                        responderReporteJson([
                            'status' => 'error',
                            'mensaje' => 'El tipo de anormalidad seleccionado no es válido.'
                        ], 400);
                    }
                }

                $filtroFrecuentes = construirFiltroAnormalidadesReporte($filtros);
                $condicionTipoFrecuentes = '';
                $parametrosFrecuentes = $filtroFrecuentes['parametros'];

                if ($idTipoAnormalidad !== null) {
                    $condicionTipoFrecuentes =
                        ' AND A.id_tipo_anormalidad = :idTipoAnormalidad';
                    $parametrosFrecuentes[':idTipoAnormalidad'] = $idTipoAnormalidad;
                }

                /*
                 * SQL Server agrupa los registros por tipo, área y línea. La
                 * respuesta no contiene el detalle individual de anormalidades.
                 */
                $sqlFrecuentes = "SELECT
                        TA.IDe AS id_tipo_anormalidad,
                        TA.NOMBRE AS nombre_tipo_anormalidad,
                        AR.nombreArea AS nombre_area,
                        L.codigo_linea,
                        L.nombre_linea,
                        COUNT_BIG(*) AS frecuencia
                    FROM SA_anormalidad AS A
                    INNER JOIN SA_tipo_anormalidad AS TA
                        ON TA.IDe = A.id_tipo_anormalidad
                       AND TA.activo = 1
                    INNER JOIN SPC_LINEAS AS L
                        ON L.codigo_linea = A.codigo_linea
                    INNER JOIN SPC_AREAS AS AR
                        ON AR.idArea = L.idArea
                    {$filtroFrecuentes['sql']}
                    {$condicionTipoFrecuentes}
                    GROUP BY
                        TA.IDe,
                        TA.NOMBRE,
                        AR.nombreArea,
                        L.codigo_linea,
                        L.nombre_linea
                    ORDER BY
                        COUNT_BIG(*) DESC,
                        TA.NOMBRE,
                        AR.nombreArea,
                        L.nombre_linea,
                        L.codigo_linea";

                $consultaFrecuentes = $conn->prepare($sqlFrecuentes);
                $consultaFrecuentes->execute($parametrosFrecuentes);
                $filasFrecuentes = $consultaFrecuentes->fetchAll(PDO::FETCH_ASSOC);
                $consultaFrecuentes->closeCursor();
                $frecuentes = [];

                foreach ($filasFrecuentes as $fila) {
                    $frecuentes[] = [
                        'id_tipo_anormalidad' => (int) $fila['id_tipo_anormalidad'],
                        'nombre_tipo_anormalidad' =>
                            (string) $fila['nombre_tipo_anormalidad'],
                        'nombre_area' => (string) $fila['nombre_area'],
                        'codigo_linea' => (string) $fila['codigo_linea'],
                        'nombre_linea' => (string) $fila['nombre_linea'],
                        'frecuencia' => (int) $fila['frecuencia']
                    ];
                }

                $datosReincidencias['frecuentes'] = $frecuentes;
            }

            if ($seccion === 'detalle_contramedidas') {
                $idTipoAnormalidad = obtenerEnteroReportePost('idTipoAnormalidad');
                $codigoLineaDetalle = obtenerTextoReportePost('codigoLineaDetalle');
                $inicio = obtenerEnteroRangoReportePost('inicio', 0, 0, 100000000);
                $longitud = obtenerEnteroRangoReportePost('longitud', 10, 1, 50);

                if ($idTipoAnormalidad === null) {
                    responderReporteJson([
                        'status' => 'error',
                        'mensaje' => 'El tipo de anormalidad solicitado no es válido.'
                    ], 400);
                }

                if ($codigoLineaDetalle === null || $codigoLineaDetalle === '' ||
                    strlen($codigoLineaDetalle) > 100 ||
                    preg_match('/[\x00-\x1F\x7F]/', $codigoLineaDetalle)) {
                    responderReporteJson([
                        'status' => 'error',
                        'mensaje' => 'La línea solicitada no es válida.'
                    ], 400);
                }

                if ($inicio === null || $longitud === null) {
                    responderReporteJson([
                        'status' => 'error',
                        'mensaje' => 'La paginación solicitada no es válida.'
                    ], 400);
                }

                if (isset($_POST['busqueda']) && is_array($_POST['busqueda'])) {
                    responderReporteJson([
                        'status' => 'error',
                        'mensaje' => 'La búsqueda solicitada no tiene un formato válido.'
                    ], 400);
                }

                $busqueda = obtenerTextoReportePost('busqueda') ?? '';

                if (strlen($busqueda) > 100 || preg_match('/[\x00-\x1F\x7F]/', $busqueda)) {
                    responderReporteJson([
                        'status' => 'error',
                        'mensaje' => 'El texto de búsqueda no es válido.'
                    ], 400);
                }

                $filtroDetalle = construirFiltroAnormalidadesReporte($filtros);
                $desdeDetalle = "FROM SA_anormalidad AS A
                    INNER JOIN SA_Analisis_causa_contramedida AS ACC
                        ON ACC.id_anormalidad = A.id_anormalidad";
                $condicionBaseDetalle = "{$filtroDetalle['sql']}
                      AND A.id_tipo_anormalidad = :idTipoDetalle
                      AND A.codigo_linea = :codigoLineaDetalle";
                $parametrosDetalle = $filtroDetalle['parametros'] + [
                    ':idTipoDetalle' => $idTipoAnormalidad,
                    ':codigoLineaDetalle' => $codigoLineaDetalle
                ];

                $consultaTotalDetalle = $conn->prepare(
                    "SELECT COUNT_BIG(*) AS total
                     {$desdeDetalle}
                     {$condicionBaseDetalle}"
                );
                $consultaTotalDetalle->execute($parametrosDetalle);
                $totalDetalle = (int) $consultaTotalDetalle->fetchColumn();
                $consultaTotalDetalle->closeCursor();

                $condicionBusquedaDetalle = '';
                $parametrosListadoDetalle = $parametrosDetalle;

                if ($busqueda !== '') {
                    $condicionBusquedaDetalle = " AND
                        (
                            A.no_seguimiento LIKE :buscarSeguimientoDetalle
                            OR A.detalle_anormalidad LIKE :buscarAnormalidadDetalle
                            OR ACC.causa LIKE :buscarCausaDetalle
                            OR ACC.contramedida LIKE :buscarContramedidaDetalle
                            OR CONVERT(nvarchar(16), ACC.fecha_registro, 120)
                                LIKE :buscarFechaDetalle
                        )";
                    $textoBusquedaDetalle = '%' . $busqueda . '%';
                    $parametrosListadoDetalle += [
                        ':buscarSeguimientoDetalle' => $textoBusquedaDetalle,
                        ':buscarAnormalidadDetalle' => $textoBusquedaDetalle,
                        ':buscarCausaDetalle' => $textoBusquedaDetalle,
                        ':buscarContramedidaDetalle' => $textoBusquedaDetalle,
                        ':buscarFechaDetalle' => $textoBusquedaDetalle
                    ];
                }

                if ($busqueda === '') {
                    $totalFiltradoDetalle = $totalDetalle;
                } else {
                    $consultaFiltradoDetalle = $conn->prepare(
                        "SELECT COUNT_BIG(*) AS total
                         {$desdeDetalle}
                         {$condicionBaseDetalle}
                         {$condicionBusquedaDetalle}"
                    );
                    $consultaFiltradoDetalle->execute($parametrosListadoDetalle);
                    $totalFiltradoDetalle = (int) $consultaFiltradoDetalle->fetchColumn();
                    $consultaFiltradoDetalle->closeCursor();
                }

                /* El modal recibe solamente la página solicitada por DataTables. */
                $sqlListadoDetalle = "SELECT
                        A.no_seguimiento,
                        CONVERT(varchar(16), ACC.fecha_registro, 120) AS fecha_registro,
                        A.detalle_anormalidad,
                        ACC.causa,
                        ACC.contramedida
                    {$desdeDetalle}
                    {$condicionBaseDetalle}
                    {$condicionBusquedaDetalle}
                    ORDER BY ACC.fecha_registro DESC, A.id_anormalidad DESC
                    OFFSET :inicio ROWS FETCH NEXT :longitud ROWS ONLY";
                $consultaListadoDetalle = $conn->prepare($sqlListadoDetalle);

                foreach ($parametrosListadoDetalle as $nombre => $valor) {
                    $tipoParametro = is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR;
                    $consultaListadoDetalle->bindValue($nombre, $valor, $tipoParametro);
                }

                $consultaListadoDetalle->bindValue(':inicio', $inicio, PDO::PARAM_INT);
                $consultaListadoDetalle->bindValue(':longitud', $longitud, PDO::PARAM_INT);
                $consultaListadoDetalle->execute();
                $filasDetalle = $consultaListadoDetalle->fetchAll(PDO::FETCH_ASSOC);
                $consultaListadoDetalle->closeCursor();
                $registrosDetalle = [];

                foreach ($filasDetalle as $fila) {
                    $registrosDetalle[] = [
                        'no_seguimiento' => (string) $fila['no_seguimiento'],
                        'fecha_registro' => (string) $fila['fecha_registro'],
                        'detalle_anormalidad' => (string) $fila['detalle_anormalidad'],
                        'causa' => (string) $fila['causa'],
                        'contramedida' => (string) $fila['contramedida']
                    ];
                }

                $datosReincidencias['detalle_contramedidas'] = [
                    'total_registros' => $totalDetalle,
                    'total_filtrados' => $totalFiltradoDetalle,
                    'registros' => $registrosDetalle
                ];
            }

            responderReporteJson([
                'status' => 'ok',
                'data' => $datosReincidencias
            ]);

        case 'reporte_detallado':
            $seccionesReporteDetallado = [
                'completo', 'catalogos', 'top_estaciones_generacion',
                'top_estaciones_deteccion', 'listado', 'detalle'
            ];

            if (isset($_POST['seccion']) && is_array($_POST['seccion'])) {
                responderReporteJson([
                    'status' => 'error',
                    'mensaje' => 'La sección solicitada no tiene un formato válido.'
                ], 400);
            }

            $seccion = obtenerTextoReportePost('seccion') ?: 'completo';

            if (!in_array($seccion, $seccionesReporteDetallado, true)) {
                responderReporteJson([
                    'status' => 'error',
                    'mensaje' => 'La sección solicitada no es válida para reportes.'
                ], 400);
            }

            if (!in_array(
                $seccion,
                [
                    'completo', 'catalogos', 'top_estaciones_generacion',
                    'top_estaciones_deteccion', 'listado', 'detalle'
                ],
                true
            )) {
                responderReportePendiente($operacion, $seccionesReporteDetallado);
            }

            if ($seccion === 'catalogos') {
                /* El filtro interno sólo necesita criticidades y estatus activos. */
                $consultaCriticidadesReporte = $conn->prepare(
                    "SELECT IDe AS id_criticidad, NOMBRE AS nombre_criticidad
                     FROM SA_criticidad
                     WHERE activo = 1
                     ORDER BY TRY_CONVERT(int, CLAVE), CLAVE, IDe"
                );
                $consultaCriticidadesReporte->execute();
                $criticidadesReporte =
                    $consultaCriticidadesReporte->fetchAll(PDO::FETCH_ASSOC);
                $consultaCriticidadesReporte->closeCursor();

                $consultaEstatusReporte = $conn->prepare(
                    "SELECT LTRIM(RTRIM(CLAVE)) AS clave_estatus,
                            NOMBRE AS nombre_estatus
                     FROM SA_estatus_anormalidad
                     WHERE activo = 1
                     ORDER BY TRY_CONVERT(int, CLAVE), CLAVE, IDe"
                );
                $consultaEstatusReporte->execute();
                $estatusReporte = $consultaEstatusReporte->fetchAll(PDO::FETCH_ASSOC);
                $consultaEstatusReporte->closeCursor();

                responderReporteJson([
                    'status' => 'ok',
                    'data' => [
                        'criticidades' => $criticidadesReporte,
                        'estatus' => $estatusReporte
                    ]
                ]);
            }

            $filtros = obtenerFiltrosComunesReportePost();
            $datosReporteDetallado = [];

            if ($seccion === 'detalle') {
                $idAnormalidad = obtenerEnteroReportePost('idAnormalidad');

                if ($idAnormalidad === null) {
                    responderReporteJson([
                        'status' => 'error',
                        'mensaje' => 'La anormalidad solicitada no es válida.'
                    ], 400);
                }

                /*
                 * El detalle respeta exactamente el alcance aplicado al listado.
                 * Así no es posible consultar desde este reporte un registro que
                 * quedó fuera del periodo, área, línea, turno o clasificación.
                 */
                $filtroDetalleReporte = agregarFiltrosClasificacionReporte(
                    construirFiltroAnormalidadesReporte($filtros),
                    obtenerFiltrosClasificacionReportePost()
                );
                $filtroDetalleReporte['sql'] .=
                    ' AND A.id_anormalidad = :idAnormalidadDetalle';
                $filtroDetalleReporte['parametros'][':idAnormalidadDetalle'] =
                    $idAnormalidad;

                $sqlApertura = "SELECT
                        A.id_anormalidad,
                        A.no_seguimiento,
                        CONVERT(varchar(19), A.fecha_hora_deteccion, 126)
                            AS fecha_hora_deteccion,
                        COALESCE(NULLIF(T.NOMBRE, N''), NULLIF(T.CLAVE, N''),
                            CONVERT(nvarchar(20), A.turno)) AS turno,
                        A.codigo_linea,
                        L.nombre_linea,
                        AR.nombreArea AS nombre_area,
                        MO.modelo,
                        TA.NOMBRE AS tipo_anormalidad,
                        A.detalle_anormalidad,
                        A.no_lote,
                        A.no_serial,
                        A.cantidad,
                        ED.nombre_estacion AS estacion_deteccion,
                        EG.nombre_estacion AS estacion_generacion,
                        A.[nomina detector] AS nomina_detector,
                        A.nombre_detector,
                        A.nomina_registra,
                        A.nombre_registra,
                        LTRIM(RTRIM(E.CLAVE)) AS clave_estatus,
                        E.NOMBRE AS nombre_estatus
                    FROM SA_anormalidad AS A
                    INNER JOIN SA_estatus_anormalidad AS E
                        ON E.IDe = A.id_estatus_actual
                    INNER JOIN SPC_LINEAS AS L
                        ON L.codigo_linea = A.codigo_linea
                    INNER JOIN SPC_AREAS AS AR
                        ON AR.idArea = L.idArea
                    LEFT JOIN SA_turno AS T
                        ON T.IDe = A.turno
                    LEFT JOIN SA_tipo_anormalidad AS TA
                        ON TA.IDe = A.id_tipo_anormalidad
                    /* Modelo opcional (NULL en registros previos o líneas sin modelos). */
                    LEFT JOIN SRD_MODELOS AS MO
                        ON MO.idModelo = A.idModelo
                    LEFT JOIN SPC_ESTACIONES AS ED
                        ON ED.id_estacion = A.id_estacion_deteccion
                       AND ED.codigo_linea = A.codigo_linea
                    LEFT JOIN SPC_ESTACIONES AS EG
                        ON EG.id_estacion = A.id_estacion_generacion
                       AND EG.codigo_linea = A.codigo_linea
                    {$filtroDetalleReporte['sql']}";
                $consultaApertura = $conn->prepare($sqlApertura);
                $consultaApertura->execute($filtroDetalleReporte['parametros']);
                $apertura = $consultaApertura->fetch(PDO::FETCH_ASSOC);
                $consultaApertura->closeCursor();

                if (!$apertura) {
                    responderReporteJson([
                        'status' => 'error',
                        'mensaje' => 'La anormalidad no existe o no pertenece a los filtros aplicados.'
                    ], 404);
                }

                /* Factores/responsables registrados durante el análisis. */
                $consultaFactores = $conn->prepare(
                    "SELECT
                        F.CLAVE AS clave_factor,
                        F.NOMBRE AS nombre_factor,
                        AF.nomina_responsable,
                        AF.nombre_responsable
                     FROM SA_anormalidad_factor AS AF
                     INNER JOIN SA_factor AS F
                        ON F.IDe = AF.id_factor
                     WHERE AF.id_anormalidad = :idAnormalidad
                     ORDER BY F.NOMBRE, F.IDe"
                );
                $consultaFactores->execute([':idAnormalidad' => $idAnormalidad]);
                $factores = $consultaFactores->fetchAll(PDO::FETCH_ASSOC);
                $consultaFactores->closeCursor();

                /* Primera revisión y nombres de las clasificaciones 5M elegidas. */
                $consultaPrimeraRevision = $conn->prepare(
                    "SELECT
                        C.NOMBRE AS criticidad,
                        NR.NOMBRE AS nivel_respuesta,
                        CONVERT(varchar(19), PR.fechaHora_revision, 126)
                            AS fecha_hora_revision,
                        PR.nomina_registra,
                        PR.nombre_registra
                     FROM SA_primera_revision AS PR
                     INNER JOIN SA_criticidad AS C
                        ON C.IDe = PR.id_criticidad
                     INNER JOIN SA_nivel_respuesta AS NR
                        ON NR.IDe = PR.id_nivel_respuesta
                     WHERE PR.id_anormalidad = :idAnormalidad"
                );
                $consultaPrimeraRevision->execute([':idAnormalidad' => $idAnormalidad]);
                $primeraRevision = $consultaPrimeraRevision->fetch(PDO::FETCH_ASSOC) ?: null;
                $consultaPrimeraRevision->closeCursor();
                $categorias5m = [];

                if ($primeraRevision !== null) {
                    $consultaCategorias5m = $conn->prepare(
                        "SELECT M.CLAVE AS clave_5m, M.NOMBRE AS nombre_5m
                         FROM SA_primera_revision_5m AS R5
                         INNER JOIN SA_5m AS M
                            ON M.IDe = R5.id_categoria_5m
                         WHERE R5.id_anormalidad = :idAnormalidad
                         ORDER BY M.NOMBRE"
                    );
                    $consultaCategorias5m->execute([':idAnormalidad' => $idAnormalidad]);
                    $categorias5m = $consultaCategorias5m->fetchAll(PDO::FETCH_ASSOC);
                    $consultaCategorias5m->closeCursor();
                    $primeraRevision['categorias_5m'] = $categorias5m;
                }

                /* Análisis PE, MF y causa/contramedida: máximo una fila por sección. */
                $consultaAnalisis = $conn->prepare(
                    "SELECT
                        PE.revision_realizada AS pe_revision,
                        PE.resultado AS pe_resultado,
                        MF.analisis_realizado AS mf_analisis,
                        MF.resultado AS mf_resultado,
                        ACC.causa,
                        ACC.contramedida,
                        CONVERT(varchar(19), ACC.fecha_registro, 126)
                            AS fecha_causa_contramedida,
                        ACC.nomina_quienregistra,
                        ACC.nombre_quienregistra
                     FROM SA_anormalidad AS A
                     LEFT JOIN SA_analisis_revision_PE AS PE
                        ON PE.id_anormalidad = A.id_anormalidad
                     LEFT JOIN SA_analisis_revision_MF AS MF
                        ON MF.id_anormalidad = A.id_anormalidad
                     LEFT JOIN SA_Analisis_causa_contramedida AS ACC
                        ON ACC.id_anormalidad = A.id_anormalidad
                     WHERE A.id_anormalidad = :idAnormalidad"
                );
                $consultaAnalisis->execute([':idAnormalidad' => $idAnormalidad]);
                $filaAnalisis = $consultaAnalisis->fetch(PDO::FETCH_ASSOC);
                $consultaAnalisis->closeCursor();

                $consultaAnalisisQa = $conn->prepare(
                    "SELECT
                        MR.CLAVE AS clave_momento,
                        MR.NOMBRE AS momento,
                        QA.[resultado confirmación] AS confirmacion,
                        QA.cantidad,
                        QA.resultado
                     FROM SA_analisis_revision_QA AS QA
                     INNER JOIN SA_momento_revision AS MR
                        ON MR.IDe = QA.momento
                     WHERE QA.id_anormalidad = :idAnormalidad
                     ORDER BY TRY_CONVERT(int, MR.CLAVE), MR.CLAVE, QA.idRegistro"
                );
                $consultaAnalisisQa->execute([':idAnormalidad' => $idAnormalidad]);
                $analisisQa = $consultaAnalisisQa->fetchAll(PDO::FETCH_ASSOC);
                $consultaAnalisisQa->closeCursor();
                $analisis = [
                    'pe' => $filaAnalisis && $filaAnalisis['pe_revision'] !== null
                        ? [
                            'revision_realizada' => $filaAnalisis['pe_revision'],
                            'resultado' => $filaAnalisis['pe_resultado']
                        ]
                        : null,
                    'mf' => $filaAnalisis && $filaAnalisis['mf_analisis'] !== null
                        ? [
                            'analisis_realizado' => $filaAnalisis['mf_analisis'],
                            'resultado' => $filaAnalisis['mf_resultado']
                        ]
                        : null,
                    'qa' => $analisisQa,
                    'causa_contramedida' =>
                        $filaAnalisis && $filaAnalisis['causa'] !== null
                        ? [
                            'causa' => $filaAnalisis['causa'],
                            'contramedida' => $filaAnalisis['contramedida'],
                            'fecha_registro' => $filaAnalisis['fecha_causa_contramedida'],
                            'nomina_registra' => $filaAnalisis['nomina_quienregistra'],
                            'nombre_registra' => $filaAnalisis['nombre_quienregistra']
                        ]
                        : null
                ];

                /* Liberación de calidad y sus muestras antes/después del reinicio. */
                $consultaCierreCalidad = $conn->prepare(
                    "SELECT
                        CC.id_cierre_calidad,
                        D.NOMBRE AS disposicion,
                        D.DESCRIPCION AS disposicion_descripcion,
                        CC.confirmacion_reinicio,
                        CONVERT(varchar(19), CC.fecha_confirmacion_reinicio, 126)
                            AS fecha_confirmacion_reinicio,
                        CC.nomina_aprobReinicio,
                        CC.nombre_aprobReinicio
                     FROM SA_cierre_calidad AS CC
                     INNER JOIN SA_disposicion AS D
                        ON D.IDe = CC.id_disposicion
                     WHERE CC.id_anormalidad = :idAnormalidad"
                );
                $consultaCierreCalidad->execute([':idAnormalidad' => $idAnormalidad]);
                $cierreCalidad = $consultaCierreCalidad->fetch(PDO::FETCH_ASSOC) ?: null;
                $consultaCierreCalidad->closeCursor();
                $muestrasCalidad = [];

                if ($cierreCalidad !== null) {
                    $consultaMuestras = $conn->prepare(
                        "SELECT
                            M.noSerial AS no_revision,
                            MR.CLAVE AS clave_momento,
                            MR.NOMBRE AS momento,
                            M.codigo_serial,
                            M.Resultado AS resultado
                         FROM SA_muestras_liberacion_calidad AS M
                         INNER JOIN SA_momento_revision AS MR
                            ON MR.IDe = M.id_momento
                         WHERE M.id_cierre_calidad = :idCierreCalidad
                         ORDER BY TRY_CONVERT(int, MR.CLAVE), MR.CLAVE, M.noSerial"
                    );
                    $consultaMuestras->execute([
                        ':idCierreCalidad' => (int) $cierreCalidad['id_cierre_calidad']
                    ]);
                    $muestrasCalidad = $consultaMuestras->fetchAll(PDO::FETCH_ASSOC);
                    $consultaMuestras->closeCursor();
                    unset($cierreCalidad['id_cierre_calidad']);
                }

                /* Cierres firmados de Staff QA y del gerente. */
                $consultaCierres = $conn->prepare(
                    "SELECT
                        CONVERT(varchar(19), CQ.fecha_cierre, 126) AS qa_fecha,
                        CQ.nomina_cierra AS qa_nomina,
                        CQ.nombre_cierra AS qa_nombre,
                        CQ.firma AS qa_firma,
                        F.NOMBRE AS qa_factor,
                        CONVERT(varchar(19), CG.fecha_cierre, 126) AS gerente_fecha,
                        CG.nomina_cierra AS gerente_nomina,
                        CG.nombre_cierra AS gerente_nombre,
                        CG.firma AS gerente_firma
                     FROM SA_anormalidad AS A
                     LEFT JOIN SA_cierre_reporte_calidad AS CQ
                        ON CQ.id_anormalidad = A.id_anormalidad
                     LEFT JOIN SA_factor AS F
                        ON F.IDe = CQ.id_factor_gerente
                     LEFT JOIN SA_cierre_autorizacion_gerente AS CG
                        ON CG.id_anormalidad = A.id_anormalidad
                     WHERE A.id_anormalidad = :idAnormalidad"
                );
                $consultaCierres->execute([':idAnormalidad' => $idAnormalidad]);
                $filaCierres = $consultaCierres->fetch(PDO::FETCH_ASSOC);
                $consultaCierres->closeCursor();
                $normalizarFirma = static function ($nombreArchivo): ?string {
                    $nombre = is_string($nombreArchivo) ? trim($nombreArchivo) : '';
                    return $nombre !== '' && basename($nombre) === $nombre &&
                        preg_match('/\.jpe?g$/i', $nombre) === 1
                        ? $nombre
                        : null;
                };
                $cierres = [
                    'calidad' => $filaCierres && $filaCierres['qa_fecha'] !== null
                        ? [
                            'fecha_cierre' => $filaCierres['qa_fecha'],
                            'nomina_cierra' => $filaCierres['qa_nomina'],
                            'nombre_cierra' => $filaCierres['qa_nombre'],
                            'factor_responsable' => $filaCierres['qa_factor'],
                            'firma' => $normalizarFirma($filaCierres['qa_firma'])
                        ]
                        : null,
                    'gerente' => $filaCierres && $filaCierres['gerente_fecha'] !== null
                        ? [
                            'fecha_cierre' => $filaCierres['gerente_fecha'],
                            'nomina_cierra' => $filaCierres['gerente_nomina'],
                            'nombre_cierra' => $filaCierres['gerente_nombre'],
                            'firma' => $normalizarFirma($filaCierres['gerente_firma'])
                        ]
                        : null
                ];

                /* Encabezado y todos los seguimientos del flujo de reincidencia. */
                $consultaReincidencia = $conn->prepare(
                    "SELECT
                        R.id_reincidencia,
                        R.no_seguimiento,
                        CONVERT(varchar(19), R.fecha_hora, 126) AS fecha_hora,
                        COALESCE(NULLIF(T.NOMBRE, N''), NULLIF(T.CLAVE, N''),
                            CONVERT(nvarchar(20), R.turno)) AS turno,
                        TR.NOMBRE AS tipo_reincidencia,
                        R.[detalle anormalidad] AS detalle_anormalidad,
                        R.registrado_por
                     FROM SA_registro_general_reincidencia AS R
                     LEFT JOIN SA_turno AS T
                        ON T.IDe = R.turno
                     OUTER APPLY
                     (
                        SELECT TOP (1) TP.NOMBRE
                        FROM SA_tipo_reincidencia AS TP
                        WHERE LTRIM(RTRIM(TP.CLAVE)) =
                            LTRIM(RTRIM(R.tipo_reincidencia))
                        ORDER BY TP.activo DESC, TP.IDe
                     ) AS TR
                     WHERE R.id_anormalidad = :idAnormalidad"
                );
                $consultaReincidencia->execute([':idAnormalidad' => $idAnormalidad]);
                $reincidenciaGeneral = $consultaReincidencia->fetch(PDO::FETCH_ASSOC) ?: null;
                $consultaReincidencia->closeCursor();
                $reincidencia = null;

                if ($reincidenciaGeneral !== null) {
                    $idReincidencia = (int) $reincidenciaGeneral['id_reincidencia'];
                    $consultaDetallesReincidencia = $conn->prepare(
                        "SELECT
                            ROW_NUMBER() OVER
                                (ORDER BY D.id_anormalidad_reincidente) AS consecutivo,
                            D.id_anormalidad_reincidente,
                            CONVERT(varchar(23), D.fecha_hora, 126) AS fecha_hora,
                            D.cantidad,
                            DP.NOMBRE AS disposicion,
                            DP.DESCRIPCION AS disposicion_descripcion,
                            D.nomina_registraDET AS nomina_registra,
                            D.nombre_reguitraDET AS nombre_registra,
                            D.nomina_inspectorQA AS nomina_inspector,
                            D.nombre_inspectorQA AS nombre_inspector,
                            CONVERT(varchar(23), C.fecha_hora, 126)
                                AS fecha_causa_contramedida,
                            C.causa,
                            C.contramedida,
                            C.[nomina atiende] AS nomina_atiende,
                            C.nombre_atiende
                         FROM SA_registro_detalle_reincidencia AS D
                         LEFT JOIN SA_disposicion AS DP
                            ON DP.IDe = D.id_dispocicion
                         LEFT JOIN SA_registro_causa_contramedida AS C
                            ON C.id_anormalidad_reincidente =
                                D.id_anormalidad_reincidente
                           AND C.id_reincidencia = D.id_reincidencia
                         WHERE D.id_reincidencia = :idReincidencia
                         ORDER BY D.id_anormalidad_reincidente"
                    );
                    $consultaDetallesReincidencia->execute([
                        ':idReincidencia' => $idReincidencia
                    ]);
                    $detallesReincidencia =
                        $consultaDetallesReincidencia->fetchAll(PDO::FETCH_ASSOC);
                    $consultaDetallesReincidencia->closeCursor();

                    $consultaQaReincidencia = $conn->prepare(
                        "SELECT
                            Q.id_anormalidad_reincidente,
                            Q.No_revision AS no_revision,
                            MR.CLAVE AS clave_momento,
                            MR.NOMBRE AS momento,
                            Q.Resultado AS resultado
                         FROM SA_registro_confirmacion_QA AS Q
                         INNER JOIN SA_momento_revision AS MR
                            ON MR.IDe = Q.id_momento
                         WHERE Q.id_reincidencia = :idReincidencia
                         ORDER BY Q.id_anormalidad_reincidente,
                            TRY_CONVERT(int, MR.CLAVE), MR.CLAVE, Q.No_revision"
                    );
                    $consultaQaReincidencia->execute([
                        ':idReincidencia' => $idReincidencia
                    ]);
                    $confirmacionesReincidencia =
                        $consultaQaReincidencia->fetchAll(PDO::FETCH_ASSOC);
                    $consultaQaReincidencia->closeCursor();
                    $qaPorDetalle = [];

                    foreach ($confirmacionesReincidencia as $confirmacion) {
                        $idDetalle = (int) $confirmacion['id_anormalidad_reincidente'];
                        unset($confirmacion['id_anormalidad_reincidente']);
                        $qaPorDetalle[$idDetalle][] = $confirmacion;
                    }

                    foreach ($detallesReincidencia as &$detalleReincidencia) {
                        $idDetalle = (int) $detalleReincidencia[
                            'id_anormalidad_reincidente'
                        ];
                        $detalleReincidencia['consecutivo'] =
                            (int) $detalleReincidencia['consecutivo'];
                        $detalleReincidencia['cantidad'] =
                            $detalleReincidencia['cantidad'] !== null
                            ? (int) $detalleReincidencia['cantidad']
                            : null;
                        $detalleReincidencia['confirmaciones_qa'] =
                            $qaPorDetalle[$idDetalle] ?? [];
                        unset($detalleReincidencia['id_anormalidad_reincidente']);
                    }
                    unset($detalleReincidencia);

                    unset($reincidenciaGeneral['id_reincidencia']);
                    $reincidencia = [
                        'general' => $reincidenciaGeneral,
                        'seguimientos' => $detallesReincidencia
                    ];
                }

                $apertura['id_anormalidad'] = (int) $apertura['id_anormalidad'];
                $apertura['cantidad'] = (int) $apertura['cantidad'];

                responderReporteJson([
                    'status' => 'ok',
                    'data' => [
                        'detalle' => [
                            'apertura' => $apertura,
                            'primera_revision' => $primeraRevision,
                            'factores' => $factores,
                            'analisis' => $analisis,
                            'verificacion_calidad' => [
                                'cierre' => $cierreCalidad,
                                'muestras' => $muestrasCalidad
                            ],
                            'cierres' => $cierres,
                            'reincidencia' => $reincidencia
                        ]
                    ]
                ]);
            }

            /*
             * Los identificadores de columna están definidos internamente y
             * nunca provienen de la solicitud. Esto permite reutilizar la misma
             * consulta segura para generación y detección.
             */
            $definicionesEstaciones = [
                'top_estaciones_generacion' => 'id_estacion_generacion',
                'top_estaciones_deteccion' => 'id_estacion_deteccion'
            ];

            foreach ($definicionesEstaciones as $claveReporte => $columnaEstacion) {
                if ($seccion !== 'completo' && $seccion !== $claveReporte) {
                    continue;
                }

                $filtroEstaciones = construirFiltroAnormalidadesReporte($filtros);

                /*
                 * SQL Server agrupa antes de responder. La suma de los grupos
                 * se usa como denominador antes de limitar el resultado al TOP 5.
                 */
                $sqlEstaciones = "SELECT TOP (5)
                        E.nombre_estacion,
                        L.nombre_linea,
                        COUNT_BIG(*) AS total,
                        CAST(
                            COUNT_BIG(*) * 100.0 /
                            NULLIF(SUM(COUNT_BIG(*)) OVER (), 0)
                            AS decimal(6, 2)
                        ) AS porcentaje
                    FROM SA_anormalidad AS A
                    INNER JOIN SPC_ESTACIONES AS E
                        ON E.id_estacion = A.{$columnaEstacion}
                       AND E.codigo_linea = A.codigo_linea
                    INNER JOIN SPC_LINEAS AS L
                        ON L.codigo_linea = A.codigo_linea
                    {$filtroEstaciones['sql']}
                    GROUP BY
                        E.id_estacion,
                        E.nombre_estacion,
                        L.codigo_linea,
                        L.nombre_linea
                    ORDER BY
                        COUNT_BIG(*) DESC,
                        E.nombre_estacion,
                        L.nombre_linea,
                        E.id_estacion";

                $consultaEstaciones = $conn->prepare($sqlEstaciones);
                $consultaEstaciones->execute($filtroEstaciones['parametros']);
                $filasEstaciones = $consultaEstaciones->fetchAll(PDO::FETCH_ASSOC);
                $consultaEstaciones->closeCursor();
                $estaciones = [];

                foreach ($filasEstaciones as $fila) {
                    $estaciones[] = [
                        'nombre_estacion' => (string) $fila['nombre_estacion'],
                        'nombre_linea' => (string) $fila['nombre_linea'],
                        'total' => (int) $fila['total'],
                        'porcentaje' => (float) $fila['porcentaje']
                    ];
                }

                $datosReporteDetallado[$claveReporte] = $estaciones;
            }

            if ($seccion === 'completo' || $seccion === 'listado') {
                $inicio = obtenerEnteroRangoReportePost('inicio', 0, 0, 100000000);
                $longitud = obtenerEnteroRangoReportePost('longitud', 10, 1, 50);

                if ($inicio === null || $longitud === null) {
                    responderReporteJson([
                        'status' => 'error',
                        'mensaje' => 'La paginación solicitada no es válida.'
                    ], 400);
                }

                if (isset($_POST['busqueda']) && is_array($_POST['busqueda'])) {
                    responderReporteJson([
                        'status' => 'error',
                        'mensaje' => 'El texto de búsqueda no tiene un formato válido.'
                    ], 400);
                }

                $busqueda = obtenerTextoReportePost('busqueda') ?? '';

                if (strlen($busqueda) > 100 || preg_match('/[\x00-\x1F\x7F]/', $busqueda)) {
                    responderReporteJson([
                        'status' => 'error',
                        'mensaje' => 'El texto de búsqueda no es válido.'
                    ], 400);
                }

                $filtrosClasificacion = obtenerFiltrosClasificacionReportePost();
                $filtroListado = agregarFiltrosClasificacionReporte(
                    construirFiltroAnormalidadesReporte($filtros),
                    $filtrosClasificacion
                );
                $desdeListado = "FROM SA_anormalidad AS A
                    INNER JOIN SA_estatus_anormalidad AS E
                        ON E.IDe = A.id_estatus_actual
                    INNER JOIN SPC_LINEAS AS L
                        ON L.codigo_linea = A.codigo_linea
                    INNER JOIN SPC_AREAS AS AR
                        ON AR.idArea = L.idArea
                    LEFT JOIN SA_turno AS T
                        ON T.IDe = A.turno
                    LEFT JOIN SRD_MODELOS AS MO
                        ON MO.idModelo = A.idModelo
                    LEFT JOIN SPC_ESTACIONES AS EG
                        ON EG.id_estacion = A.id_estacion_generacion
                       AND EG.codigo_linea = A.codigo_linea";
                $condicionBaseListado = $filtroListado['sql'];
                $parametrosBaseListado = $filtroListado['parametros'];

                $consultaTotalListado = $conn->prepare(
                    "SELECT COUNT_BIG(*) AS total
                     {$desdeListado}
                     {$condicionBaseListado}"
                );
                $consultaTotalListado->execute($parametrosBaseListado);
                $totalListado = (int) $consultaTotalListado->fetchColumn();
                $consultaTotalListado->closeCursor();

                $condicionBusquedaListado = '';
                $parametrosListado = $parametrosBaseListado;

                if ($busqueda !== '') {
                    $condicionBusquedaListado = " AND
                        (
                            A.no_seguimiento LIKE :buscarSeguimientoReporte
                            OR E.NOMBRE LIKE :buscarEstatusReporte
                            OR AR.nombreArea LIKE :buscarAreaReporte
                            OR L.nombre_linea LIKE :buscarLineaReporte
                            OR A.codigo_linea LIKE :buscarCodigoLineaReporte
                            OR MO.modelo LIKE :buscarModeloReporte
                            OR COALESCE(NULLIF(T.NOMBRE, N''), NULLIF(T.CLAVE, N''),
                                CONVERT(nvarchar(20), A.turno)) LIKE :buscarTurnoReporte
                            OR CONVERT(nvarchar(16), A.fecha_hora_deteccion, 120)
                                LIKE :buscarFechaReporte
                            OR EG.nombre_estacion LIKE :buscarEstacionReporte
                            OR COALESCE(NULLIF(A.nombre_detector, N''), A.nombre_registra)
                                LIKE :buscarDetectorReporte
                        )";
                    $textoBusqueda = '%' . $busqueda . '%';
                    $parametrosListado += [
                        ':buscarSeguimientoReporte' => $textoBusqueda,
                        ':buscarEstatusReporte' => $textoBusqueda,
                        ':buscarAreaReporte' => $textoBusqueda,
                        ':buscarLineaReporte' => $textoBusqueda,
                        ':buscarCodigoLineaReporte' => $textoBusqueda,
                        ':buscarModeloReporte' => $textoBusqueda,
                        ':buscarTurnoReporte' => $textoBusqueda,
                        ':buscarFechaReporte' => $textoBusqueda,
                        ':buscarEstacionReporte' => $textoBusqueda,
                        ':buscarDetectorReporte' => $textoBusqueda
                    ];
                }

                if ($busqueda === '') {
                    $totalFiltradoListado = $totalListado;
                } else {
                    $consultaFiltradoListado = $conn->prepare(
                        "SELECT COUNT_BIG(*) AS total
                         {$desdeListado}
                         {$condicionBaseListado}
                         {$condicionBusquedaListado}"
                    );
                    $consultaFiltradoListado->execute($parametrosListado);
                    $totalFiltradoListado = (int) $consultaFiltradoListado->fetchColumn();
                    $consultaFiltradoListado->closeCursor();
                }

                /* SQL Server devuelve solamente la pagina solicitada por DataTables. */
                $sqlListado = "SELECT
                        A.id_anormalidad,
                        A.no_seguimiento,
                        CASE
                            WHEN CHARINDEX('/', REVERSE(A.no_seguimiento)) > 0
                            THEN RIGHT(
                                A.no_seguimiento,
                                CHARINDEX('/', REVERSE(A.no_seguimiento)) - 1
                            )
                            ELSE A.no_seguimiento
                        END AS consecutivo,
                        LTRIM(RTRIM(E.CLAVE)) AS clave_estatus,
                        E.NOMBRE AS nombre_estatus,
                        AR.nombreArea AS nombre_area,
                        L.nombre_linea,
                        MO.modelo,
                        COALESCE(NULLIF(T.NOMBRE, N''), NULLIF(T.CLAVE, N''),
                            CONVERT(nvarchar(20), A.turno)) AS nombre_turno,
                        CONVERT(varchar(16), A.fecha_hora_deteccion, 120)
                            AS fecha_hora_deteccion,
                        EG.nombre_estacion AS estacion_generacion,
                        COALESCE(NULLIF(A.nombre_detector, N''), A.nombre_registra)
                            AS nombre_detector
                    {$desdeListado}
                    {$condicionBaseListado}
                    {$condicionBusquedaListado}
                    ORDER BY A.fecha_hora_deteccion DESC, A.id_anormalidad DESC
                    OFFSET :inicio ROWS FETCH NEXT :longitud ROWS ONLY";
                $consultaListado = $conn->prepare($sqlListado);

                foreach ($parametrosListado as $nombre => $valor) {
                    $tipoParametro = is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR;
                    $consultaListado->bindValue($nombre, $valor, $tipoParametro);
                }

                $consultaListado->bindValue(':inicio', $inicio, PDO::PARAM_INT);
                $consultaListado->bindValue(':longitud', $longitud, PDO::PARAM_INT);
                $consultaListado->execute();
                $filasListado = $consultaListado->fetchAll(PDO::FETCH_ASSOC);
                $consultaListado->closeCursor();
                $registrosListado = [];

                foreach ($filasListado as $fila) {
                    $registrosListado[] = [
                        'id_anormalidad' => (int) $fila['id_anormalidad'],
                        'no_seguimiento' => (string) $fila['no_seguimiento'],
                        'consecutivo' => (string) $fila['consecutivo'],
                        'clave_estatus' => (string) $fila['clave_estatus'],
                        'nombre_estatus' => (string) $fila['nombre_estatus'],
                        'nombre_area' => (string) $fila['nombre_area'],
                        'nombre_linea' => (string) $fila['nombre_linea'],
                        'modelo' => (string) ($fila['modelo'] ?? ''),
                        'nombre_turno' => (string) $fila['nombre_turno'],
                        'fecha_hora_deteccion' => (string) $fila['fecha_hora_deteccion'],
                        'estacion_generacion' =>
                            (string) ($fila['estacion_generacion'] ?? ''),
                        'nombre_detector' => (string) ($fila['nombre_detector'] ?? '')
                    ];
                }

                $datosReporteDetallado['listado'] = [
                    'total_registros' => $totalListado,
                    'total_filtrados' => $totalFiltradoListado,
                    'registros' => $registrosListado
                ];
            }

            responderReporteJson([
                'status' => 'ok',
                'data' => $datosReporteDetallado
            ]);

        default:
            responderReporteJson([
                'status' => 'error',
                'mensaje' => 'La operación solicitada no es válida.'
            ], 400);
    }
} catch (PDOException $error) {
    error_log('[Reportes de anormalidades] Error de base de datos: ' . $error->getMessage());

    responderReporteJson([
        'status' => 'error',
        'mensaje' => 'No fue posible consultar la información del reporte.'
    ], 500);
} catch (Throwable $error) {
    error_log('[Reportes de anormalidades] Error inesperado: ' . $error->getMessage());

    responderReporteJson([
        'status' => 'error',
        'mensaje' => 'Ocurrió un error al consultar la información del reporte.'
    ], 500);
}
