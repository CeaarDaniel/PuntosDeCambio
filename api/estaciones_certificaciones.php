<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/conexion.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method === 'GET') {
        $accion = trim((string) ($_GET['accion'] ?? 'listar'));

        if ($accion === 'catalogos') {
            obtenerCatalogos($conn);
        }

        if ($accion === 'listar') {
            listarEstaciones($conn);
        }

        responder(false, 'La acción solicitada no es válida.', null, 400);
    }

    if ($method === 'PUT') {
        actualizarEstaciones($conn, obtenerJson());
    }

    responder(false, 'Método no permitido.', null, 405);
} catch (InvalidArgumentException $exception) {
    responder(false, $exception->getMessage(), null, 422);
} catch (PDOException $exception) {
    error_log(
        'Error SQL en estaciones_certificaciones: ' .
        $exception->getMessage()
    );

    $sqlState = (string) $exception->getCode();
    $codigoMotor = (int) ($exception->errorInfo[1] ?? 0);

    if (
        $sqlState === '23000' ||
        in_array($codigoMotor, [547, 2601, 2627], true)
    ) {
        responder(
            false,
            'No fue posible aplicar el cambio porque la línea, estación o certificación ya no es válida.',
            null,
            409
        );
    }

    responder(
        false,
        'Ocurrió un error al consultar la base de datos.',
        null,
        500
    );
} catch (Throwable $exception) {
    error_log(
        'Error inesperado en estaciones_certificaciones: ' .
        $exception->getMessage()
    );

    responder(
        false,
        'Ocurrió un error inesperado en el servidor.',
        null,
        500
    );
}

/**
 * Devuelve las líneas y el catálogo de certificaciones.
 * codigo_linea se devuelve como texto porque en SQL Server es NVARCHAR(20).
 */
function obtenerCatalogos(PDO $conn): void
{
    $sqlLineas = "SELECT codigo_linea, nombre_linea, descripcion, encargado_supervisor, imagen, idArea
                    FROM dbo.SPC_LINEAS ORDER BY nombre_linea ASC, codigo_linea ASC";

    $sqlCertificaciones = "SELECT idCR, codigo_certificacion, nombre_certificacion, tipo_proceso
                    FROM dbo.SPC_CERTIFICACIONES ORDER BY nombre_certificacion ASC, codigo_certificacion ASC";

    $stmtLineas = $conn->query($sqlLineas);
    $lineas = $stmtLineas->fetchAll();
    $stmtLineas->closeCursor();

    $stmtCertificaciones = $conn->query($sqlCertificaciones);
    $certificaciones = $stmtCertificaciones->fetchAll();
    $stmtCertificaciones->closeCursor();

    responder(true, 'Catálogos obtenidos correctamente.',
        [
            'lineas' => $lineas,
            'certificaciones' => $certificaciones,
        ]
    );
}

/**
 * Devuelve todas las estaciones de la línea seleccionada.
 * DataTables realiza la búsqueda, el ordenamiento y la paginación en el cliente.
 */
function listarEstaciones(PDO $conn): void
{
    $codigoLineaRaw = $_GET['codigo_linea'] ?? null;

    if (
        $codigoLineaRaw === null ||
        (is_string($codigoLineaRaw) && trim($codigoLineaRaw) === '')
    ) {
        responderDataTablesVacio();
    }

    $codigoLinea = validarCodigoLinea($codigoLineaRaw);
    validarLineaExiste($conn, $codigoLinea);

    $estado = trim((string) ($_GET['estado'] ?? 'todos'));

    $estadosPermitidos = [
        'todos',
        'con_curso',
        'sin_curso',
        'certificada',
        'no_certificada',
    ];

    if (!in_array($estado, $estadosPermitidos, true)) {
        $estado = 'todos';
    }

    [$whereSql, $parametros] = construirFiltros(
        $codigoLinea,
        $estado
    );

    $sqlDatos = "SELECT
            e.id_estacion,
            e.nombre_estacion,
            e.descripcion,
            CAST(
                ISNULL(e.requiere_certificacion, 0)
                AS INT
            ) AS requiere_certificacion,
            e.id_certificacion,
            c.codigo_certificacion,
            c.nombre_certificacion,
            c.tipo_proceso
        FROM dbo.SPC_ESTACIONES AS e
        LEFT JOIN dbo.SPC_CERTIFICACIONES AS c
            ON c.idCR = e.id_certificacion
        {$whereSql}
        ORDER BY
            e.nombre_estacion ASC,
            e.id_estacion ASC";

    $stmtDatos = $conn->prepare($sqlDatos);
    ejecutarConParametros($stmtDatos, $parametros);
    $registros = $stmtDatos->fetchAll();
    $stmtDatos->closeCursor();

    $resumen = obtenerResumenLinea($conn, $codigoLinea);

    http_response_code(200);

    echo json_encode(
        [
            'success' => true,
            'message' => 'Estaciones obtenidas correctamente.',
            'data' => $registros,
            'summary' => $resumen,
        ],
        JSON_UNESCAPED_UNICODE |
        JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
}

function construirFiltros(
    string $codigoLinea,
    string $estado
): array {
    $condiciones = [
        'e.codigo_linea = :codigo_linea'
    ];

    $parametros = [
        'codigo_linea' => $codigoLinea
    ];

    if ($estado === 'con_curso') {
        $condiciones[] = "
            e.id_certificacion IS NOT NULL
        ";
    } elseif ($estado === 'sin_curso') {
        $condiciones[] = "
            e.id_certificacion IS NULL
        ";
    } elseif ($estado === 'certificada') {
        $condiciones[] =
            'ISNULL(e.requiere_certificacion, 0) = 1';
    } elseif ($estado === 'no_certificada') {
        $condiciones[] =
            'ISNULL(e.requiere_certificacion, 0) = 0';
    }

    return [
        'WHERE ' . implode(' AND ', $condiciones),
        $parametros,
    ];
}

/**
 * Resumen calculado para la línea seleccionada.
 * requiere_certificacion se consulta, pero nunca se modifica aquí.
 */
function obtenerResumenLinea(
    PDO $conn,
    string $codigoLinea
): array {
    $sql = " SELECT
            COUNT(*) AS total_estaciones,
            COALESCE(
                SUM(
                    CASE
                        WHEN id_certificacion IS NOT NULL
                        THEN 1
                        ELSE 0
                    END
                ),
                0
            ) AS con_curso,
            COALESCE(
                SUM(
                    CASE
                        WHEN id_certificacion IS NULL
                        THEN 1
                        ELSE 0
                    END
                ),
                0
            ) AS sin_curso
        FROM dbo.SPC_ESTACIONES
        WHERE codigo_linea = :codigo_linea
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(
        ':codigo_linea',
        $codigoLinea,
        PDO::PARAM_STR
    );
    $stmt->execute();

    $fila = $stmt->fetch() ?: [];
    $stmt->closeCursor();

    return [
        'total_estaciones' =>
            (int) ($fila['total_estaciones'] ?? 0),
        'con_curso' =>
            (int) ($fila['con_curso'] ?? 0),
        'sin_curso' =>
            (int) ($fila['sin_curso'] ?? 0),
    ];
}

/**
 * Asigna, reemplaza o quita id_certificacion.
 * Este método nunca modifica requiere_certificacion.
 */
function actualizarEstaciones(PDO $conn, array $data): void
{
    $accion = trim((string) ($data['accion'] ?? ''));

    if (!in_array($accion, ['asignar', 'quitar'], true)) {
        throw new InvalidArgumentException(
            'La acción de actualización no es válida.'
        );
    }

    $codigoLinea = validarCodigoLinea(
        $data['codigo_linea'] ?? null
    );

    validarLineaExiste($conn, $codigoLinea);

    $ids = validarIdsEstacion(
        $data['ids_estacion'] ?? null
    );

    $placeholders = [];
    $parametros = [
        'codigo_linea' => $codigoLinea
    ];

    foreach ($ids as $indice => $idEstacion) {
        $nombre = 'id_' . $indice;
        $placeholders[] = ':' . $nombre;
        $parametros[$nombre] = $idEstacion;
    }

    $listaIds = implode(', ', $placeholders);

    if ($accion === 'asignar') {
        $idCertificacion = validarIdCertificacion(
            $data['id_certificacion'] ?? null
        );

        validarCertificacionExiste(
            $conn,
            $idCertificacion
        );

        $parametros['id_certificacion'] =
            $idCertificacion;

        $sql = "UPDATE dbo.SPC_ESTACIONES
            SET id_certificacion = :id_certificacion
            WHERE codigo_linea = :codigo_linea
              AND id_estacion IN ({$listaIds})
        ";
    } else {
        $sql = "UPDATE dbo.SPC_ESTACIONES
            SET id_certificacion = NULL
            WHERE codigo_linea = :codigo_linea
              AND id_estacion IN ({$listaIds})
        ";
    }

    /* Verifica que todos los IDs pertenezcan a la línea seleccionada. */
    $sqlValidacion = "
        SELECT COUNT(*)
        FROM dbo.SPC_ESTACIONES
        WHERE codigo_linea = :codigo_linea
          AND id_estacion IN ({$listaIds})
    ";

    $stmtValidacion = $conn->prepare($sqlValidacion);

    foreach ($parametros as $nombre => $valor) {
        if ($nombre === 'id_certificacion') {
            continue;
        }

        $tipo = is_int($valor)
            ? PDO::PARAM_INT
            : PDO::PARAM_STR;

        $stmtValidacion->bindValue(
            ':' . $nombre,
            $valor,
            $tipo
        );
    }

    $stmtValidacion->execute();
    $cantidadEncontrada =
        (int) $stmtValidacion->fetchColumn();
    $stmtValidacion->closeCursor();

    if ($cantidadEncontrada !== count($ids)) {
        throw new InvalidArgumentException(
            'Una o más estaciones no pertenecen a la línea seleccionada o ya no existen.'
        );
    }

    $conn->beginTransaction();

    try {
        $stmt = $conn->prepare($sql);
        ejecutarConParametros($stmt, $parametros);
        $stmt->closeCursor();

        $actualizados = count($ids);
        $conn->commit();
    } catch (Throwable $exception) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        throw $exception;
    }

    responder(
        true,
        $accion === 'asignar'
            ? "El curso se asignó a {$actualizados} estación(es)."
            : "El curso se quitó de {$actualizados} estación(es).",
        [
            'actualizados' => $actualizados,
            'accion' => $accion,
        ]
    );
}

function validarLineaExiste(
    PDO $conn,
    string $codigoLinea
): void {
    $stmt = $conn->prepare(
        'SELECT TOP 1 codigo_linea
         FROM dbo.SPC_LINEAS
         WHERE codigo_linea = :codigo_linea'
    );

    $stmt->bindValue(
        ':codigo_linea',
        $codigoLinea,
        PDO::PARAM_STR
    );

    $stmt->execute();
    $existe = $stmt->fetchColumn();
    $stmt->closeCursor();

    if ($existe === false) {
        throw new InvalidArgumentException(
            'La línea seleccionada no existe.'
        );
    }
}

function validarCertificacionExiste(
    PDO $conn,
    int $idCertificacion
): void {
    $stmt = $conn->prepare(
        'SELECT TOP 1 idCR
         FROM dbo.SPC_CERTIFICACIONES
         WHERE idCR = :id_certificacion'
    );

    $stmt->bindValue(
        ':id_certificacion',
        $idCertificacion,
        PDO::PARAM_INT
    );

    $stmt->execute();
    $existe = $stmt->fetchColumn();
    $stmt->closeCursor();

    if ($existe === false) {
        throw new InvalidArgumentException(
            'La certificación seleccionada no existe.'
        );
    }
}

function validarIdCertificacion(mixed $valor): int
{
    $id = filter_var(
        $valor,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($id === false) {
        throw new InvalidArgumentException(
            'El identificador de la certificación no es válido.'
        );
    }

    return (int) $id;
}

/**
 * Valida un código NVARCHAR(20). No se convierte a número.
 */
function validarCodigoLinea(mixed $valor): string
{
    if (is_array($valor) || is_object($valor)) {
        throw new InvalidArgumentException(
            'El código de línea no es válido.'
        );
    }

    $codigoLinea = trim((string) $valor);

    if (
        $codigoLinea === '' ||
        mb_strlen($codigoLinea) > 20
    ) {
        throw new InvalidArgumentException(
            'El código de línea no es válido.'
        );
    }

    return $codigoLinea;
}

function validarIdsEstacion(mixed $ids): array
{
    if (!is_array($ids) || $ids === []) {
        throw new InvalidArgumentException(
            'Selecciona al menos una estación.'
        );
    }

    if (count($ids) > 500) {
        throw new InvalidArgumentException(
            'Solo puedes actualizar hasta 500 estaciones por operación.'
        );
    }

    $resultado = [];

    foreach ($ids as $id) {
        $idValidado = filter_var(
            $id,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($idValidado === false) {
            throw new InvalidArgumentException(
                'Uno de los identificadores de estación no es válido.'
            );
        }

        $resultado[] = (int) $idValidado;
    }

    return array_values(array_unique($resultado));
}

function responderDataTablesVacio(): never
{
    http_response_code(200);

    echo json_encode(
        [
            'success' => true,
            'message' => 'Selecciona una línea.',
            'data' => [],
            'summary' => [
                'total_estaciones' => 0,
                'con_curso' => 0,
                'sin_curso' => 0,
            ],
        ],
        JSON_UNESCAPED_UNICODE |
        JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
}

function ejecutarConParametros(
    PDOStatement $stmt,
    array $parametros
): void {
    foreach ($parametros as $nombre => $valor) {
        $tipo = is_int($valor)
            ? PDO::PARAM_INT
            : PDO::PARAM_STR;

        $stmt->bindValue(
            ':' . $nombre,
            $valor,
            $tipo
        );
    }

    $stmt->execute();
}

function obtenerJson(): array
{
    $raw = file_get_contents('php://input');

    if ($raw === false || trim($raw) === '') {
        throw new InvalidArgumentException(
            'No se recibieron datos para procesar.'
        );
    }

    $data = json_decode($raw, true);

    if (
        !is_array($data) ||
        json_last_error() !== JSON_ERROR_NONE
    ) {
        throw new InvalidArgumentException(
            'El contenido enviado no es un JSON válido.'
        );
    }

    return $data;
}

function responder(
    bool $success,
    string $message,
    mixed $data = null,
    int $statusCode = 200
): never {
    http_response_code($statusCode);

    echo json_encode(
        [
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ],
        JSON_UNESCAPED_UNICODE |
        JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
}