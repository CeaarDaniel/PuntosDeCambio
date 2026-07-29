<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

define('APP_DEBUG', isset($_GET['debug']) && (string) $_GET['debug'] === '1');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    require_once __DIR__ . '/conexion.php';

    if (!isset($conn) || !$conn instanceof PDO) {
        throw new RuntimeException('La conexión PDO no está disponible en conexion.php.');
    }

    if ($method === 'GET') {
        $accion = trim((string) ($_GET['accion'] ?? 'listar'));

        if ($accion === 'catalogos') {
            obtenerCatalogos($conn);
        }

        if ($accion === 'listar') {
            listarPersonal($conn);
        }

        if ($accion === 'detalle') {
            obtenerDetalleCursos($conn);
        }

        if ($accion === 'diagnostico') {
            diagnosticarIlu($conn);
        }

        responder(false, 'La acción solicitada no es válida.', null, 400);
    }

    if ($method === 'POST') {
        procesarCambioIlu($conn, obtenerJson());
    }

    responder(false, 'Método no permitido.', null, 405);
} catch (InvalidArgumentException $exception) {
    responder(false, $exception->getMessage(), null, 422);
} catch (PDOException $exception) {
    error_log('Error SQL en ilu_personal: ' . $exception->getMessage());

    $sqlState = (string) $exception->getCode();
    $codigoMotor = (int) ($exception->errorInfo[1] ?? 0);

    if (in_array($codigoMotor, [2601, 2627], true)) {
        responder(
            false,
            'El curso ya estaba asignado a una o más personas.',
            crearDetalleError($exception),
            409
        );
    }

    if ($codigoMotor === 547) {
        responder(
            false,
            'No fue posible aplicar el cambio porque hay una relación no válida o restringida.',
            crearDetalleError($exception),
            409
        );
    }

    responder(
        false,
        APP_DEBUG
            ? $exception->getMessage()
            : 'Ocurrió un error al consultar la base de datos.',
        crearDetalleError($exception),
        500
    );
} catch (Throwable $exception) {
    error_log('Error inesperado en ilu_personal: ' . $exception->getMessage());

    responder(
        false,
        APP_DEBUG
            ? $exception->getMessage()
            : 'Ocurrió un error inesperado en el servidor.',
        crearDetalleError($exception),
        500
    );
}

function obtenerCatalogos(PDO $conn): void
{
    $sqlLineas = "
        SELECT
            l.codigo_linea,
            l.nombre_linea,
            l.descripcion,
            (
                SELECT COUNT(*)
                FROM dbo.SPC_PERSONAL AS p
                WHERE LTRIM(RTRIM(CONVERT(NVARCHAR(100), p.codigo_linea))) =
                      LTRIM(RTRIM(CONVERT(NVARCHAR(100), l.codigo_linea)))
            ) AS total_personal,
            (
                SELECT COUNT(*)
                FROM dbo.SPC_PERSONAL AS p
                WHERE LTRIM(RTRIM(CONVERT(NVARCHAR(100), p.codigo_linea))) =
                      LTRIM(RTRIM(CONVERT(NVARCHAR(100), l.codigo_linea)))
                  AND LTRIM(RTRIM(ISNULL(CONVERT(NVARCHAR(10), p.turno), ''))) = '1'
            ) AS total_turno_1,
            (
                SELECT COUNT(*)
                FROM dbo.SPC_PERSONAL AS p
                WHERE LTRIM(RTRIM(CONVERT(NVARCHAR(100), p.codigo_linea))) =
                      LTRIM(RTRIM(CONVERT(NVARCHAR(100), l.codigo_linea)))
                  AND LTRIM(RTRIM(ISNULL(CONVERT(NVARCHAR(10), p.turno), ''))) = '2'
            ) AS total_turno_2
        FROM dbo.SPC_LINEAS AS l
        ORDER BY
            CASE
                WHEN (
                    SELECT COUNT(*)
                    FROM dbo.SPC_PERSONAL AS p
                    WHERE LTRIM(RTRIM(CONVERT(NVARCHAR(100), p.codigo_linea))) =
                          LTRIM(RTRIM(CONVERT(NVARCHAR(100), l.codigo_linea)))
                      AND LTRIM(RTRIM(ISNULL(CONVERT(NVARCHAR(10), p.turno), ''))) = '1'
                ) > 0 THEN 0
                ELSE 1
            END,
            l.nombre_linea ASC,
            l.codigo_linea ASC
    ";

    $sqlCertificaciones = "
        SELECT
            idCR AS idE,
            idCR,
            codigo_certificacion,
            nombre_certificacion,
            tipo_proceso
        FROM dbo.SPC_CERTIFICACIONES
        ORDER BY nombre_certificacion ASC, codigo_certificacion ASC
    ";

    $stmtLineas = $conn->query($sqlLineas);
    $lineas = $stmtLineas->fetchAll(PDO::FETCH_ASSOC);
    $stmtLineas->closeCursor();

    $stmtCertificaciones = $conn->query($sqlCertificaciones);
    $certificaciones = $stmtCertificaciones->fetchAll(PDO::FETCH_ASSOC);
    $stmtCertificaciones->closeCursor();

    responder(
        true,
        'Catálogos obtenidos correctamente.',
        [
            'lineas' => $lineas,
            'certificaciones' => $certificaciones,
        ]
    );
}

function listarPersonal(PDO $conn): void
{
    $modoBusquedaFront = isset($_GET['modo_busqueda']) && (string) $_GET['modo_busqueda'] === 'front';

    $draw = obtenerEntero($_GET['draw'] ?? 0, 0, PHP_INT_MAX, 0);
    $start = obtenerEntero($_GET['start'] ?? 0, 0, PHP_INT_MAX, 0);
    $length = obtenerEntero($_GET['length'] ?? 10, 1, 100, 10);

    $codigoLineaRaw = $_GET['codigo_linea'] ?? null;

    if (
        $codigoLineaRaw === null ||
        (is_string($codigoLineaRaw) && trim($codigoLineaRaw) === '')
    ) {
        responderDataTablesVacio($draw);
    }

    $codigoLinea = validarCodigoLinea($codigoLineaRaw);
    validarLineaExiste($conn, $codigoLinea);

    /*
     * Cuando modo_busqueda=front, el buscador nativo de DataTables filtra
     * en el navegador. Por eso no se usa search[value] en SQL Server.
     */
    $busqueda = $modoBusquedaFront
        ? ''
        : trim((string) ($_GET['search']['value'] ?? ''));

    $turno = validarTurno($_GET['turno'] ?? '1');
    $estado = trim((string) ($_GET['estado'] ?? 'todos'));

    $estadosPermitidos = [
        'todos',
        'con_curso',
        'sin_curso',
        'disponible',
        'asignado',
        'eliminado',
    ];

    if (!in_array($estado, $estadosPermitidos, true)) {
        $estado = 'todos';
    }

    $columnasOrdenables = [
        1 => 'p.nomina',
        2 => 'CONVERT(NVARCHAR(4000), p.nombre)',
        3 => 'p.turno',
        4 => 'p.estatus',
        5 => '(SELECT COUNT(*) FROM dbo.SPC_ILU AS iOrden WHERE iOrden.nomina = p.nomina)',
    ];

    $columnasOrdenablesDatos = [
        1 => 'datos.nomina',
        2 => 'datos.nombre',
        3 => 'datos.turno',
        4 => 'datos.estatus',
        5 => 'datos.cursos_asignados',
    ];

    $indiceOrden = obtenerEntero($_GET['order'][0]['column'] ?? 2, 0, 6, 2);
    $columnaOrden = $columnasOrdenables[$indiceOrden] ?? 'CONVERT(NVARCHAR(4000), p.nombre)';
    $columnaOrdenDatos = $columnasOrdenablesDatos[$indiceOrden] ?? 'datos.nombre';

    $direccion = strtolower((string) ($_GET['order'][0]['dir'] ?? 'asc'));
    $direccion = $direccion === 'desc' ? 'DESC' : 'ASC';

    [$whereSql, $parametros] = construirFiltros($codigoLinea, $turno, $busqueda, $estado);

    $stmtTotal = $conn->prepare("
        SELECT COUNT(*)
        FROM dbo.SPC_PERSONAL AS p
        WHERE LTRIM(RTRIM(CONVERT(NVARCHAR(100), p.codigo_linea))) = :codigo_linea
          AND LTRIM(RTRIM(ISNULL(CONVERT(NVARCHAR(10), p.turno), ''))) = :turno
    ");
    ejecutarConParametros($stmtTotal, [
        'codigo_linea' => $codigoLinea,
        'turno' => $turno,
    ]);
    $totalRegistros = (int) $stmtTotal->fetchColumn();
    $stmtTotal->closeCursor();

    $sqlFiltrados = "
        SELECT COUNT(*)
        FROM dbo.SPC_PERSONAL AS p
        {$whereSql}
    ";

    $stmtFiltrados = $conn->prepare($sqlFiltrados);
    ejecutarConParametros($stmtFiltrados, $parametros);
    $totalFiltrados = (int) $stmtFiltrados->fetchColumn();
    $stmtFiltrados->closeCursor();

    $sqlSelectBase = "
        SELECT
            p.nomina AS nomina,
            p.nombre AS nombre,
            p.codigo_linea AS codigo_linea,
            p.turno,
            p.estatus,
            (
                SELECT COUNT(*)
                FROM dbo.SPC_ILU AS i
                WHERE i.nomina = p.nomina
            ) AS cursos_asignados,
            STUFF((
                SELECT ', ' + c.codigo_certificacion
                FROM dbo.SPC_ILU AS iDetalle
                INNER JOIN dbo.SPC_CERTIFICACIONES AS c
                    ON c.idCR = iDetalle.idE
                WHERE iDetalle.nomina = p.nomina
                ORDER BY c.codigo_certificacion ASC
                FOR XML PATH(''), TYPE
            ).value('.', 'NVARCHAR(MAX)'), 1, 2, '') AS cursos_detalle
        FROM dbo.SPC_PERSONAL AS p
        {$whereSql}
    ";

    if ($modoBusquedaFront) {
        $sqlDatos = "
            {$sqlSelectBase}
            ORDER BY {$columnaOrden} {$direccion}, p.nomina ASC
        ";
        $parametrosDatos = $parametros;
    } else {
        $filaInicio = $start;
        $filaFin = $start + $length;

        $sqlDatos = "
            WITH personal_paginado AS (
                SELECT
                    datos.*,
                    ROW_NUMBER() OVER (
                        ORDER BY
                            {$columnaOrdenDatos} {$direccion},
                            datos.nomina ASC
                    ) AS numero_fila
                FROM (
                    {$sqlSelectBase}
                ) AS datos
            )
            SELECT
                nomina,
                nombre,
                codigo_linea,
                turno,
                estatus,
                cursos_asignados,
                cursos_detalle
            FROM personal_paginado
            WHERE numero_fila > :fila_inicio
              AND numero_fila <= :fila_fin
            ORDER BY numero_fila ASC
        ";

        $parametrosDatos = $parametros;
        $parametrosDatos['fila_inicio'] = $filaInicio;
        $parametrosDatos['fila_fin'] = $filaFin;
    }

    $stmtDatos = $conn->prepare($sqlDatos);
    ejecutarConParametros($stmtDatos, $parametrosDatos);
    $registros = $stmtDatos->fetchAll(PDO::FETCH_ASSOC);
    $stmtDatos->closeCursor();

    $resumen = obtenerResumenLinea($conn, $codigoLinea, $turno);
    $debug = APP_DEBUG ? obtenerDiagnosticoFiltro($conn, $codigoLinea, $turno) : null;

    http_response_code(200);
    echo json_encode(
        [
            'success' => true,
            'message' => $modoBusquedaFront
                ? 'Personal cargado para búsqueda local.'
                : 'Personal obtenido correctamente.',
            'draw' => $draw,
            'recordsTotal' => $totalRegistros,
            'recordsFiltered' => $totalFiltrados,
            'data' => $registros,
            'summary' => $resumen,
            'searchMode' => $modoBusquedaFront ? 'front' : 'server',
            'debug' => $debug,
        ],
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );
    exit;
}

function construirFiltros(string $codigoLinea, string $turno, string $busqueda, string $estado): array
{
    $condiciones = [
        'LTRIM(RTRIM(CONVERT(NVARCHAR(100), p.codigo_linea))) = :codigo_linea',
        "LTRIM(RTRIM(ISNULL(CONVERT(NVARCHAR(10), p.turno), ''))) = :turno"
    ];

    $parametros = [
        'codigo_linea' => $codigoLinea,
        'turno' => $turno
    ];

    if ($busqueda !== '') {
        $valor = '%' . escaparLike($busqueda) . '%';

        $condiciones[] = "
            (
                p.nomina
                    LIKE :busqueda_nomina ESCAPE '\\'
                OR p.nombre
                    LIKE :busqueda_nombre ESCAPE '\\'
                OR p.turno
                    LIKE :busqueda_turno ESCAPE '\\'
                OR p.estatus
                    LIKE :busqueda_estatus ESCAPE '\\'
                OR EXISTS (
                    SELECT 1
                    FROM dbo.SPC_ILU AS iBusqueda
                    INNER JOIN dbo.SPC_CERTIFICACIONES AS cBusqueda
                        ON cBusqueda.idCR = iBusqueda.idE
                    WHERE iBusqueda.nomina = p.nomina
                      AND (
                            cBusqueda.codigo_certificacion
                                LIKE :busqueda_codigo ESCAPE '\\'
                            OR cBusqueda.nombre_certificacion
                                LIKE :busqueda_curso ESCAPE '\\'
                          )
                )
            )
        ";

        $parametros['busqueda_nomina'] = $valor;
        $parametros['busqueda_nombre'] = $valor;
        $parametros['busqueda_turno'] = $valor;
        $parametros['busqueda_estatus'] = $valor;
        $parametros['busqueda_codigo'] = $valor;
        $parametros['busqueda_curso'] = $valor;
    }

    if ($estado === 'con_curso') {
        $condiciones[] = "
            EXISTS (
                SELECT 1
                FROM dbo.SPC_ILU AS iEstado
                WHERE iEstado.nomina = p.nomina
            )
        ";
    } elseif ($estado === 'sin_curso') {
        $condiciones[] = "
            NOT EXISTS (
                SELECT 1
                FROM dbo.SPC_ILU AS iEstado
                WHERE iEstado.nomina = p.nomina
            )
        ";
    } elseif ($estado === 'disponible') {
        $condiciones[] = "LTRIM(RTRIM(ISNULL(p.estatus, ''))) = '0'";
    } elseif ($estado === 'asignado') {
        $condiciones[] = "LTRIM(RTRIM(ISNULL(p.estatus, ''))) = '1'";
    } elseif ($estado === 'eliminado') {
        $condiciones[] = "LTRIM(RTRIM(ISNULL(p.estatus, ''))) = '2'";
    }

    return [
        'WHERE ' . implode(' AND ', $condiciones),
        $parametros,
    ];
}

function obtenerResumenLinea(PDO $conn, string $codigoLinea, string $turno): array
{
    /*
     * SQL Server no permite usar SUM(CASE WHEN EXISTS(...))
     * porque EXISTS es una subconsulta dentro de una expresión agregada.
     * Por eso primero se calcula el total de cursos por persona en una
     * tabla derivada y después se agregan los resultados.
     */
    $sql = "
        SELECT
            COUNT(*) AS total_personal,
            COALESCE(
                SUM(
                    CASE
                        WHEN ISNULL(cursos.total_cursos, 0) > 0
                        THEN 1
                        ELSE 0
                    END
                ),
                0
            ) AS con_curso,
            COALESCE(
                SUM(
                    CASE
                        WHEN ISNULL(cursos.total_cursos, 0) = 0
                        THEN 1
                        ELSE 0
                    END
                ),
                0
            ) AS sin_curso,
            COALESCE(
                SUM(
                    ISNULL(cursos.total_cursos, 0)
                ),
                0
            ) AS registros_ilu
        FROM dbo.SPC_PERSONAL AS p
        LEFT JOIN (
            SELECT
                nomina,
                COUNT(*) AS total_cursos
            FROM dbo.SPC_ILU
            GROUP BY nomina
        ) AS cursos
            ON cursos.nomina = p.nomina
        WHERE LTRIM(RTRIM(CONVERT(NVARCHAR(100), p.codigo_linea))) = :codigo_linea
          AND LTRIM(RTRIM(ISNULL(CONVERT(NVARCHAR(10), p.turno), ''))) = :turno
    ";

    $stmt = $conn->prepare($sql);
    ejecutarConParametros($stmt, [
        'codigo_linea' => $codigoLinea,
        'turno' => $turno,
    ]);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $stmt->closeCursor();

    return [
        'total_personal' => (int) ($fila['total_personal'] ?? 0),
        'con_curso' => (int) ($fila['con_curso'] ?? 0),
        'sin_curso' => (int) ($fila['sin_curso'] ?? 0),
        'registros_ilu' => (int) ($fila['registros_ilu'] ?? 0),
    ];
}


function obtenerDiagnosticoFiltro(PDO $conn, string $codigoLinea, string $turno): array
{
    $stmt = $conn->prepare("
        SELECT
            COUNT(*) AS total_linea,
            SUM(CASE WHEN LTRIM(RTRIM(ISNULL(CONVERT(NVARCHAR(10), turno), ''))) = '1' THEN 1 ELSE 0 END) AS total_turno_1,
            SUM(CASE WHEN LTRIM(RTRIM(ISNULL(CONVERT(NVARCHAR(10), turno), ''))) = '2' THEN 1 ELSE 0 END) AS total_turno_2
        FROM dbo.SPC_PERSONAL
        WHERE LTRIM(RTRIM(CONVERT(NVARCHAR(100), codigo_linea))) = :codigo_linea
    ");

    ejecutarConParametros($stmt, ['codigo_linea' => $codigoLinea]);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $stmt->closeCursor();

    return [
        'codigo_linea' => $codigoLinea,
        'turno_solicitado' => $turno,
        'total_linea_sin_filtro_turno' => (int) ($fila['total_linea'] ?? 0),
        'total_turno_1' => (int) ($fila['total_turno_1'] ?? 0),
        'total_turno_2' => (int) ($fila['total_turno_2'] ?? 0),
    ];
}

function diagnosticarIlu(PDO $conn): void
{
    $codigoLineaRaw = $_GET['codigo_linea'] ?? '';
    $codigoLinea = is_string($codigoLineaRaw) && trim($codigoLineaRaw) !== ''
        ? validarCodigoLinea($codigoLineaRaw)
        : null;
    $turno = validarTurno($_GET['turno'] ?? '1');

    $resultado = [
        'base_datos' => $conn->query('SELECT DB_NAME()')->fetchColumn(),
        'tablas' => [],
        'turnos_disponibles' => [],
        'linea' => null,
    ];

    foreach (['SPC_LINEAS', 'SPC_PERSONAL', 'SPC_ILU', 'SPC_CERTIFICACIONES'] as $tabla) {
        $stmt = $conn->prepare("SELECT CASE WHEN OBJECT_ID(:tabla, 'U') IS NULL THEN 0 ELSE 1 END");
        ejecutarConParametros($stmt, ['tabla' => 'dbo.' . $tabla]);
        $resultado['tablas'][$tabla] = (int) $stmt->fetchColumn() === 1;
        $stmt->closeCursor();
    }

    $stmtTurnos = $conn->query("
        SELECT
            LTRIM(RTRIM(ISNULL(CONVERT(NVARCHAR(10), turno), ''))) AS turno,
            COUNT(*) AS total
        FROM dbo.SPC_PERSONAL
        GROUP BY LTRIM(RTRIM(ISNULL(CONVERT(NVARCHAR(10), turno), '')))
        ORDER BY turno ASC
    ");
    $resultado['turnos_disponibles'] = $stmtTurnos->fetchAll(PDO::FETCH_ASSOC);
    $stmtTurnos->closeCursor();

    if ($codigoLinea !== null) {
        $resultado['linea'] = obtenerDiagnosticoFiltro($conn, $codigoLinea, $turno);
    }

    responder(true, 'Diagnóstico ILU generado correctamente.', $resultado);
}

function obtenerDetalleCursos(PDO $conn): void
{
    $codigoLinea = validarCodigoLinea($_GET['codigo_linea'] ?? null);
    $nomina = validarNomina($_GET['nomina'] ?? null);

    validarLineaExiste($conn, $codigoLinea);
    validarPersonalPerteneceLinea($conn, $codigoLinea, [$nomina]);

    $sql = "
        SELECT
            i.nomina,
            i.idE,
            CONVERT(VARCHAR(19), i.fecha_registro, 120) AS fecha_registro,
            CONVERT(VARCHAR(10), i.fecha_vencimiento, 120) AS fecha_vencimiento,
            i.nivel,
            i.estatus,
            c.codigo_certificacion,
            c.nombre_certificacion,
            c.tipo_proceso
        FROM dbo.SPC_ILU AS i
        INNER JOIN dbo.SPC_CERTIFICACIONES AS c
            ON c.idCR = i.idE
        WHERE i.nomina = :nomina
        ORDER BY c.codigo_certificacion ASC, c.nombre_certificacion ASC
    ";

    $stmt = $conn->prepare($sql);
    ejecutarConParametros($stmt, ['nomina' => $nomina]);
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    responder(true, 'Cursos obtenidos correctamente.', $cursos);
}

function procesarCambioIlu(PDO $conn, array $data): void
{
    $accion = trim((string) ($data['accion'] ?? ''));

    if (!in_array($accion, ['asignar', 'quitar'], true)) {
        throw new InvalidArgumentException('La acción solicitada no es válida.');
    }

    $codigoLinea = validarCodigoLinea($data['codigo_linea'] ?? null);
    $idE = validarIdE($data['idE'] ?? null);
    $nominas = validarNominas($data['nominas'] ?? null);
    $estatus = validarEstatusIlu($data['estatus'] ?? '0');

    validarLineaExiste($conn, $codigoLinea);
    validarCertificacionExiste($conn, $idE);
    validarPersonalPerteneceLinea($conn, $codigoLinea, $nominas);

    if ($accion === 'asignar') {
        asignarCurso($conn, $nominas, $idE, $estatus);
        return;
    }

    quitarCurso($conn, $nominas, $idE);
}

function asignarCurso(PDO $conn, array $nominas, int $idE, string $estatus): void
{
    $sqlExiste = "
        SELECT 1
        FROM dbo.SPC_ILU
        WHERE nomina = :nomina
          AND idE = :idE
    ";

    $sqlInsertar = "
        INSERT INTO dbo.SPC_ILU (
            nomina,
            idE,
            fecha_registro,
            estatus
        )
        VALUES (
            :nomina,
            :idE,
            GETDATE(),
            :estatus
        )
    ";

    $insertados = 0;
    $existentes = 0;

    $conn->beginTransaction();

    try {
        $stmtExiste = $conn->prepare($sqlExiste);
        $stmtInsertar = $conn->prepare($sqlInsertar);

        foreach ($nominas as $nomina) {
            ejecutarConParametros($stmtExiste, [
                'nomina' => $nomina,
                'idE' => $idE,
            ]);

            $yaExiste = $stmtExiste->fetchColumn() !== false;
            $stmtExiste->closeCursor();

            if ($yaExiste) {
                $existentes++;
                continue;
            }

            ejecutarConParametros($stmtInsertar, [
                'nomina' => $nomina,
                'idE' => $idE,
                'estatus' => $estatus,
            ]);
            $stmtInsertar->closeCursor();
            $insertados++;
        }

        $conn->commit();
    } catch (Throwable $exception) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        throw $exception;
    }

    responder(
        true,
        "Curso asignado. Nuevos registros: {$insertados}. Ya existentes: {$existentes}.",
        [
            'insertados' => $insertados,
            'existentes' => $existentes,
        ]
    );
}

function quitarCurso(PDO $conn, array $nominas, int $idE): void
{
    $placeholders = [];
    $parametros = ['idE' => $idE];

    foreach ($nominas as $indice => $nomina) {
        $nombre = 'nomina_' . $indice;
        $placeholders[] = ':' . $nombre;
        $parametros[$nombre] = $nomina;
    }

    $sql = "
        DELETE FROM dbo.SPC_ILU
        WHERE idE = :idE
          AND nomina IN (" . implode(', ', $placeholders) . ")
    ";

    $conn->beginTransaction();

    try {
        $stmt = $conn->prepare($sql);
        ejecutarConParametros($stmt, $parametros);
        $eliminados = max(0, (int) $stmt->rowCount());
        $stmt->closeCursor();
        $conn->commit();
    } catch (Throwable $exception) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        throw $exception;
    }

    responder(
        true,
        "Curso quitado. Registros eliminados: {$eliminados}.",
        ['eliminados' => $eliminados]
    );
}

function validarLineaExiste(PDO $conn, string $codigoLinea): void
{
    $stmt = $conn->prepare(
        'SELECT TOP 1 codigo_linea
         FROM dbo.SPC_LINEAS
         WHERE codigo_linea = :codigo_linea'
    );
    ejecutarConParametros($stmt, ['codigo_linea' => $codigoLinea]);
    $existe = $stmt->fetchColumn();
    $stmt->closeCursor();

    if ($existe === false) {
        throw new InvalidArgumentException('La línea seleccionada no existe.');
    }
}

function validarCertificacionExiste(PDO $conn, int $idE): void
{
    $stmt = $conn->prepare(
        'SELECT TOP 1 idCR
         FROM dbo.SPC_CERTIFICACIONES
         WHERE idCR = :idE'
    );
    ejecutarConParametros($stmt, ['idE' => $idE]);
    $existe = $stmt->fetchColumn();
    $stmt->closeCursor();

    if ($existe === false) {
        throw new InvalidArgumentException('El curso o certificación seleccionada no existe.');
    }
}

function validarPersonalPerteneceLinea(PDO $conn, string $codigoLinea, array $nominas): void
{
    $placeholders = [];
    $parametros = ['codigo_linea' => $codigoLinea];

    foreach ($nominas as $indice => $nomina) {
        $nombre = 'nomina_' . $indice;
        $placeholders[] = ':' . $nombre;
        $parametros[$nombre] = $nomina;
    }

    $sql = "
        SELECT COUNT(*)
        FROM dbo.SPC_PERSONAL AS p
        WHERE LTRIM(RTRIM(CONVERT(NVARCHAR(100), p.codigo_linea))) = :codigo_linea
          AND p.nomina IN (" . implode(', ', $placeholders) . ")
    ";

    $stmt = $conn->prepare($sql);
    ejecutarConParametros($stmt, $parametros);
    $encontrados = (int) $stmt->fetchColumn();
    $stmt->closeCursor();

    if ($encontrados !== count($nominas)) {
        throw new InvalidArgumentException(
            'Una o más personas no pertenecen a la línea seleccionada o ya no existen.'
        );
    }
}

function validarTurno(mixed $valor): string
{
    if (is_array($valor) || is_object($valor)) {
        return '1';
    }

    $turno = trim((string) $valor);

    if ($turno !== '1' && $turno !== '2') {
        return '1';
    }

    return $turno;
}

function validarCodigoLinea(mixed $valor): string
{
    if (is_array($valor) || is_object($valor)) {
        throw new InvalidArgumentException('El código de línea no es válido.');
    }

    $codigoLinea = trim((string) $valor);

    if ($codigoLinea === '' || strlen($codigoLinea) > 100) {
        throw new InvalidArgumentException('El código de línea no es válido.');
    }

    return $codigoLinea;
}

function validarNomina(mixed $valor): string
{
    if (is_array($valor) || is_object($valor)) {
        throw new InvalidArgumentException('La nómina no es válida.');
    }

    $nomina = trim((string) $valor);

    if ($nomina === '' || strlen($nomina) > 50) {
        throw new InvalidArgumentException('La nómina no es válida.');
    }

    return $nomina;
}

function validarNominas(mixed $nominas): array
{
    if (!is_array($nominas) || $nominas === []) {
        throw new InvalidArgumentException('Selecciona al menos una persona.');
    }

    if (count($nominas) > 500) {
        throw new InvalidArgumentException('Solo puedes actualizar hasta 500 personas por operación.');
    }

    $resultado = [];

    foreach ($nominas as $nomina) {
        $resultado[] = validarNomina($nomina);
    }

    return array_values(array_unique($resultado));
}

function validarIdE(mixed $valor): int
{
    $id = filter_var(
        $valor,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($id === false) {
        throw new InvalidArgumentException('El curso o certificación no es válido.');
    }

    return (int) $id;
}

function validarEstatusIlu(mixed $valor): string
{
    if ($valor === null || trim((string) $valor) === '') {
        return '0';
    }

    $estatus = strtoupper(trim((string) $valor));

    if (strlen($estatus) > 3) {
        throw new InvalidArgumentException('El estatus de ILU no puede superar 3 caracteres.');
    }

    return $estatus;
}

function obtenerJson(): array
{
    $raw = file_get_contents('php://input');

    if ($raw === false || trim($raw) === '') {
        throw new InvalidArgumentException('No se recibieron datos para procesar.');
    }

    $data = json_decode($raw, true);

    if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
        throw new InvalidArgumentException('El contenido enviado no es un JSON válido.');
    }

    return $data;
}

function ejecutarConParametros(PDOStatement $stmt, array $parametros): void
{
    foreach ($parametros as $nombre => $valor) {
        $tipo = is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue(':' . $nombre, $valor, $tipo);
    }

    $stmt->execute();
}

function escaparLike(string $valor): string
{
    return str_replace(
        ['\\', '%', '_', '['],
        ['\\\\', '\\%', '\\_', '\\['],
        $valor
    );
}

function obtenerEntero(mixed $valor, int $minimo, int $maximo, int $predeterminado): int
{
    $entero = filter_var($valor, FILTER_VALIDATE_INT);

    if ($entero === false || $entero < $minimo || $entero > $maximo) {
        return $predeterminado;
    }

    return (int) $entero;
}

function responderDataTablesVacio(int $draw): void
{
    http_response_code(200);
    echo json_encode(
        [
            'success' => true,
            'message' => 'Selecciona una línea.',
            'draw' => $draw,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'summary' => [
                'total_personal' => 0,
                'con_curso' => 0,
                'sin_curso' => 0,
                'registros_ilu' => 0,
            ],
        ],
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );
    exit;
}

function crearDetalleError(Throwable $exception): ?array
{
    if (!APP_DEBUG) {
        return null;
    }

    $detalle = [
        'tipo' => get_class($exception),
        'mensaje' => $exception->getMessage(),
        'archivo' => $exception->getFile(),
        'linea' => $exception->getLine(),
    ];

    if ($exception instanceof PDOException) {
        $detalle['errorInfo'] = $exception->errorInfo;
    }

    return $detalle;
}

function responder(bool $success, string $message, mixed $data = null, int $statusCode = 200): void
{
    http_response_code($statusCode);

    echo json_encode(
        [
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ],
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
}
