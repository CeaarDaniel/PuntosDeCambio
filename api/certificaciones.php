<?php

declare(strict_types=1);

/*
 * MODO DE DEPURACIÓN TEMPORAL
 *
 * Déjalo en true solamente mientras identificas el error.
 * En producción debes cambiarlo a false para no exponer
 * rutas internas, consultas, estructura de servidor ni mensajes SQL.
 */
define('APP_DEBUG', true);

error_reporting(E_ALL);

/*
 * Se evita que PHP imprima avisos o errores como HTML,
 * porque romperían la respuesta JSON esperada por el frontend.
 * Los errores se envían dentro del JSON cuando APP_DEBUG = true.
 */
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');

header(
    'Content-Type: application/json; charset=utf-8'
);

header(
    'Cache-Control: no-store, no-cache, must-revalidate, max-age=0'
);

if (APP_DEBUG) {
    header('X-API-Debug: enabled');
}

/*
 * Convierte warnings, notices y otros errores recuperables
 * en excepciones para que lleguen a los bloques catch.
 */
set_error_handler(
    static function (
        int $severity,
        string $message,
        string $file,
        int $line
    ): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new ErrorException(
            $message,
            0,
            $severity,
            $file,
            $line
        );
    }
);

/*
 * Captura errores fatales que normalmente no entrarían al try/catch.
 * No captura errores de sintaxis del propio archivo antes de que PHP
 * pueda iniciar su ejecución, pero sí muchos errores fatales en includes.
 */
register_shutdown_function(
    static function (): void {
        $error = error_get_last();

        if ($error === null) {
            return;
        }

        $tiposFatales = [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_USER_ERROR,
        ];

        if (!in_array($error['type'], $tiposFatales, true)) {
            return;
        }

        if (!headers_sent()) {
            header(
                'Content-Type: application/json; charset=utf-8'
            );

            http_response_code(500);
        }

        $debug = [
            'tipo' => 'FatalError',
            'mensaje' => $error['message'] ?? 'Error fatal desconocido.',
            'archivo' => $error['file'] ?? null,
            'linea' => $error['line'] ?? null,
            'php_version' => PHP_VERSION,
        ];

        error_log(
            '[certificaciones.php] Error fatal: ' .
            json_encode(
                $debug,
                JSON_UNESCAPED_UNICODE |
                JSON_INVALID_UTF8_SUBSTITUTE
            )
        );

        echo json_encode(
            [
                'success' => false,
                'message' => APP_DEBUG
                    ? ($debug['mensaje'] ?? 'Error fatal.')
                    : 'Ocurrió un error fatal en el servidor.',
                'data' => APP_DEBUG
                    ? ['debug' => $debug]
                    : null,
            ],
            JSON_UNESCAPED_UNICODE |
            JSON_INVALID_UTF8_SUBSTITUTE
        );
    }
);

try {
    /*
     * La conexión ahora está dentro del try.
     * Así también se muestra el detalle si falla conexion.php,
     * el controlador PDO o la conexión a SQL Server.
     */
    require_once __DIR__ . '/conexion.php';

    if (
        !isset($conn) ||
        !($conn instanceof PDO)
    ) {
        throw new RuntimeException(
            'conexion.php no creó una instancia PDO válida en la variable $conn.'
        );
    }

    $method =
        $_SERVER['REQUEST_METHOD'] ?? 'GET';

    /*
     * Diagnóstico manual:
     * api/certificaciones.php?diagnostico=1
     *
     * Solo está disponible mientras APP_DEBUG sea true.
     */
    if (
        APP_DEBUG &&
        $method === 'GET' &&
        isset($_GET['diagnostico'])
    ) {
        ejecutarDiagnostico($conn);
    }

    switch ($method) {
        case 'GET':
            listarCertificaciones($conn);
            break;

        case 'POST':
            crearCertificacion(
                $conn,
                obtenerJson()
            );
            break;

        case 'PUT':
            actualizarCertificacion(
                $conn,
                obtenerJson()
            );
            break;

        case 'DELETE':
            eliminarCertificacion($conn);
            break;

        default:
            responder(
                false,
                'Método no permitido.',
                null,
                405
            );
    }
} catch (InvalidArgumentException $exception) {
    responderExcepcion(
        $exception,
        422,
        'Los datos enviados no son válidos.'
    );
} catch (PDOException $exception) {
    $errorInfo =
        is_array($exception->errorInfo ?? null)
            ? $exception->errorInfo
            : [];

    $sqlState = (string) (
        $errorInfo[0] ??
        $exception->getCode()
    );

    $codigoMotor = (int) (
        $errorInfo[1] ?? 0
    );

    /*
     * 2601 y 2627 corresponden normalmente
     * a índices o restricciones únicas.
     */
    if (
        $sqlState === '23000' ||
        in_array(
            $codigoMotor,
            [2601, 2627],
            true
        )
    ) {
        responderExcepcion(
            $exception,
            409,
            'El código de certificación ya existe.',
            [
                'sql_state' => $sqlState,
                'codigo_motor' => $codigoMotor,
            ]
        );
    }

    responderExcepcion(
        $exception,
        500,
        'Ocurrió un error al consultar la base de datos.',
        [
            'sql_state' => $sqlState,
            'codigo_motor' => $codigoMotor,
        ]
    );
} catch (Throwable $exception) {
    responderExcepcion(
        $exception,
        500,
        'Ocurrió un error inesperado en el servidor.'
    );
}

/**
 * Envía información técnica del error cuando APP_DEBUG está activo.
 */
function responderExcepcion(
    Throwable $exception,
    int $statusCode,
    string $mensajePublico,
    array $extra = []
): never {
    $detalle = [
        'tipo_excepcion' =>
            get_class($exception),

        'mensaje_original' =>
            $exception->getMessage(),

        'codigo_excepcion' =>
            $exception->getCode(),

        'archivo' =>
            $exception->getFile(),

        'linea' =>
            $exception->getLine(),

        'metodo_http' =>
            $_SERVER['REQUEST_METHOD'] ?? null,

        'ruta_solicitada' =>
            $_SERVER['REQUEST_URI'] ?? null,

        'parametros_get' =>
            $_GET,

        'php_version' =>
            PHP_VERSION,

        'pdo_drivers_disponibles' =>
            PDO::getAvailableDrivers(),

        'servidor_web' =>
            $_SERVER['SERVER_SOFTWARE'] ?? null,

        'traza' =>
            array_slice(
                explode(
                    PHP_EOL,
                    $exception->getTraceAsString()
                ),
                0,
                15
            ),
    ];

    if ($exception instanceof PDOException) {
        $errorInfo =
            is_array($exception->errorInfo ?? null)
                ? $exception->errorInfo
                : [];

        $detalle['pdo'] = [
            'sql_state' =>
                $errorInfo[0] ??
                $exception->getCode(),

            'codigo_driver' =>
                $errorInfo[1] ?? null,

            'mensaje_driver' =>
                $errorInfo[2] ??
                $exception->getMessage(),
        ];
    }

    if ($extra !== []) {
        $detalle['extra'] = $extra;
    }

    error_log(
        '[certificaciones.php] ' .
        json_encode(
            $detalle,
            JSON_UNESCAPED_UNICODE |
            JSON_INVALID_UTF8_SUBSTITUTE
        )
    );

    responder(
        false,

        APP_DEBUG
            ? $exception->getMessage()
            : $mensajePublico,

        APP_DEBUG
            ? ['debug' => $detalle]
            : null,

        $statusCode
    );
}

/**
 * Comprueba conexión, base de datos, tabla y columnas.
 *
 * Abrir temporalmente:
 * api/certificaciones.php?diagnostico=1
 */
function ejecutarDiagnostico(
    PDO $conn
): never {
    $resultado = [
        'php' => [
            'version' => PHP_VERSION,
            'sapi' => PHP_SAPI,
            'pdo_drivers' =>
                PDO::getAvailableDrivers(),
        ],

        'conexion' => [
            'driver' =>
                $conn->getAttribute(
                    PDO::ATTR_DRIVER_NAME
                ),

            'server_version' =>
                $conn->getAttribute(
                    PDO::ATTR_SERVER_VERSION
                ),

            'client_version' =>
                $conn->getAttribute(
                    PDO::ATTR_CLIENT_VERSION
                ),
        ],
    ];

    $resultado['base_datos'] = [
        'nombre' => $conn
            ->query('SELECT DB_NAME()')
            ->fetchColumn(),

        'servidor' => $conn
            ->query('SELECT @@SERVERNAME')
            ->fetchColumn(),
    ];

    $resultado['tabla'] = [
        'existe' => (
            $conn
                ->query(
                    "
                    SELECT CASE
                        WHEN OBJECT_ID(
                            'dbo.SPC_CERTIFICACIONES',
                            'U'
                        ) IS NOT NULL
                        THEN 1
                        ELSE 0
                    END
                    "
                )
                ->fetchColumn()
            === 1
        ),
    ];

    $stmtColumnas = $conn->query(
        "
        SELECT
            COLUMN_NAME,
            DATA_TYPE,
            CHARACTER_MAXIMUM_LENGTH,
            IS_NULLABLE
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = 'dbo'
          AND TABLE_NAME = 'SPC_CERTIFICACIONES'
        ORDER BY ORDINAL_POSITION
        "
    );

    $resultado['tabla']['columnas'] =
        $stmtColumnas->fetchAll();

    $stmtColumnas->closeCursor();

    $resultado['tabla']['total_registros'] =
        (int) $conn
            ->query(
                '
                SELECT COUNT(*)
                FROM dbo.SPC_CERTIFICACIONES
                '
            )
            ->fetchColumn();

    responder(
        true,
        'Diagnóstico completado.',
        $resultado,
        200
    );
}

/**
 * Devuelve todos los registros requeridos por la tabla.
 * DataTables realiza la búsqueda, el ordenamiento y la paginación
 * en el navegador.
 */
function listarCertificaciones(
    PDO $conn
): void {
    $tipoProceso = trim(
        (string) (
            $_GET['tipo_proceso'] ?? ''
        )
    );

    [
        $whereSql,
        $parametros
    ] = construirFiltros(
        $tipoProceso
    );

    $sqlDatos = "
        SELECT
            idCR,
            codigo_certificacion,
            nombre_certificacion,
            tipo_proceso,
            descripcion
        FROM dbo.SPC_CERTIFICACIONES
        {$whereSql}
        ORDER BY
            nombre_certificacion ASC,
            idCR ASC
    ";

    $stmtDatos =
        $conn->prepare($sqlDatos);

    ejecutarConParametros(
        $stmtDatos,
        $parametros
    );

    $registros =
        $stmtDatos->fetchAll();

    /*
     * Estos datos ya vienen preparados
     * desde SQL Server.
     */
    $resumen =
        obtenerResumen($conn);

    $procesos =
        obtenerTiposProceso($conn);

    http_response_code(200);

    echo json_encode(
        [
            'success' => true,

            'message' =>
                'Certificaciones obtenidas correctamente.',

            'data' =>
                $registros,

            /*
             * Información adicional para
             * tarjetas y filtros.
             */
            'summary' =>
                $resumen,

            'procesos' =>
                $procesos,
        ],
        JSON_UNESCAPED_UNICODE |
        JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
}

/**
 * Construye las condiciones WHERE necesarias.
 *
 * Solamente agrega filtros cuando realmente
 * fueron enviados por el frontend.
 */
function construirFiltros(
    string $tipoProceso
): array {
    $condiciones = [];
    $parametros = [];

    if ($tipoProceso !== '') {
        $condiciones[] = "
            LTRIM(RTRIM(tipo_proceso))
                = :tipo_proceso
        ";

        $parametros['tipo_proceso'] =
            $tipoProceso;
    }

    $whereSql = $condiciones
        ? 'WHERE ' .
            implode(
                ' AND ',
                $condiciones
            )
        : '';

    return [
        $whereSql,
        $parametros
    ];
}

/**
 * Calcula las tres tarjetas directamente
 * mediante una consulta SQL.
 *
 * Los valores representan el total general
 * de la tabla, no solamente la página actual.
 */
function obtenerResumen(
    PDO $conn
): array {
    $sql = "
        SELECT
            COUNT(*) AS total_certificaciones,

            COUNT(
                DISTINCT NULLIF(
                    LTRIM(
                        RTRIM(tipo_proceso)
                    ),
                    ''
                )
            ) AS total_procesos,

            COALESCE(
                SUM(
                    CASE
                        WHEN NULLIF(
                            LTRIM(
                                RTRIM(descripcion)
                            ),
                            ''
                        ) IS NOT NULL
                        THEN 1
                        ELSE 0
                    END
                ),
                0
            ) AS con_descripcion

        FROM dbo.SPC_CERTIFICACIONES
    ";

    $resumen =
        $conn->query($sql)->fetch() ?: [];

    return [
        'total_certificaciones' =>
            (int) (
                $resumen[
                    'total_certificaciones'
                ] ?? 0
            ),

        'total_procesos' =>
            (int) (
                $resumen[
                    'total_procesos'
                ] ?? 0
            ),

        'con_descripcion' =>
            (int) (
                $resumen[
                    'con_descripcion'
                ] ?? 0
            ),
    ];
}

/**
 * Catálogo de tipos de proceso para:
 *
 * - El select de filtros.
 * - El datalist del formulario.
 */
function obtenerTiposProceso(
    PDO $conn
): array {
    $sql = "
        SELECT DISTINCT
            LTRIM(
                RTRIM(tipo_proceso)
            ) AS tipo_proceso

        FROM dbo.SPC_CERTIFICACIONES

        WHERE NULLIF(
            LTRIM(
                RTRIM(tipo_proceso)
            ),
            ''
        ) IS NOT NULL

        ORDER BY
            tipo_proceso ASC
    ";

    $filas =
        $conn->query($sql)->fetchAll();

    return array_values(
        array_map(
            static function (
                array $fila
            ): string {
                return (string) $fila[
                    'tipo_proceso'
                ];
            },
            $filas
        )
    );
}

/**
 * Registrar certificación.
 */
function crearCertificacion(
    PDO $conn,
    array $data
): void {
    $registro =
        validarCertificacion($data);

    if (
        codigoExiste(
            $conn,
            $registro[
                'codigo_certificacion'
            ]
        )
    ) {
        responder(
            false,
            'El código de certificación ya existe.',
            null,
            409
        );
    }

    $sql = "INSERT INTO dbo.SPC_CERTIFICACIONES (
            codigo_certificacion,
            nombre_certificacion,
            tipo_proceso,
            descripcion
        )
        OUTPUT
            INSERTED.idCR,
            INSERTED.codigo_certificacion,
            INSERTED.nombre_certificacion,
            INSERTED.tipo_proceso,
            INSERTED.descripcion
        VALUES (
            :codigo_certificacion,
            :nombre_certificacion,
            :tipo_proceso,
            :descripcion
        )
    ";

    $stmt = $conn->prepare($sql);

    $stmt->execute($registro);

    responder(
        true,
        'La certificación se registró correctamente.',
        $stmt->fetch(),
        201
    );
}

/**
 * Actualizar certificación.
 */
function actualizarCertificacion(
    PDO $conn,
    array $data
): void {
    $idCR = validarId(
        $data['idCR'] ?? null
    );

    $registro = validarCertificacion($data);

    if (
        codigoExiste(
            $conn,
            $registro[
                'codigo_certificacion'
            ],
            $idCR
        )
    ) {
        responder(
            false,
            'El código de certificación ya existe.',
            null,
            409
        );
    }

    $registro['idCR'] = $idCR;

    $sql = "
        UPDATE dbo.SPC_CERTIFICACIONES
        SET
            codigo_certificacion =
                :codigo_certificacion,

            nombre_certificacion =
                :nombre_certificacion,

            tipo_proceso =
                :tipo_proceso,

            descripcion =
                :descripcion

        OUTPUT
            INSERTED.idCR,
            INSERTED.codigo_certificacion,
            INSERTED.nombre_certificacion,
            INSERTED.tipo_proceso,
            INSERTED.descripcion

        WHERE idCR = :idCR
    ";

    $stmt =
        $conn->prepare($sql);

    $stmt->execute($registro);

    $actualizado =
        $stmt->fetch();

    if (!$actualizado) {
        responder(
            false,
            'La certificación no existe o ya fue eliminada.',
            null,
            404
        );
    }

    responder(
        true,
        'La certificación se actualizó correctamente.',
        $actualizado
    );
}

/**
 * Eliminar certificación.
 */
function eliminarCertificacion(
    PDO $conn
): void {
    $idCR = validarId(
        $_GET['idCR'] ?? null
    );

    $sql = "
        DELETE
        FROM dbo.SPC_CERTIFICACIONES

        OUTPUT
            DELETED.idCR

        WHERE idCR = :idCR
    ";

    $stmt =
        $conn->prepare($sql);

    $stmt->execute([
        'idCR' => $idCR
    ]);

    $eliminado =
        $stmt->fetchColumn();

    if (!$eliminado) {
        responder(
            false,
            'La certificación no existe o ya fue eliminada.',
            null,
            404
        );
    }

    responder(
        true,
        'La certificación se eliminó correctamente.',
        [
            'idCR' =>
                (int) $eliminado
        ]
    );
}

/**
 * Comprueba que el código no esté repetido.
 *
 * Al editar se excluye el registro actual.
 */
function codigoExiste(
    PDO $conn,
    string $codigo,
    ?int $idExcluir = null
): bool {
    $sql = "
        SELECT TOP 1
            idCR

        FROM dbo.SPC_CERTIFICACIONES

        WHERE UPPER(
            codigo_certificacion
        ) = UPPER(:codigo)
    ";

    $parametros = [
        'codigo' => $codigo
    ];

    if ($idExcluir !== null) {
        $sql .= "
            AND idCR <> :id_excluir
        ";

        $parametros['id_excluir'] =
            $idExcluir;
    }

    $stmt =
        $conn->prepare($sql);

    $stmt->execute($parametros);

    return (
        $stmt->fetchColumn() !== false
    );
}

/**
 * Asigna parámetros de texto a una consulta
 * preparada y después la ejecuta.
 */
function ejecutarConParametros(
    PDOStatement $stmt,
    array $parametros
): void {
    foreach (
        $parametros
        as $nombre => $valor
    ) {
        $stmt->bindValue(
            ':' . $nombre,
            $valor,
            PDO::PARAM_STR
        );
    }

    $stmt->execute();
}

/**
 * Lee el JSON enviado por POST o PUT.
 */
function obtenerJson(): array
{
    $raw =
        file_get_contents(
            'php://input'
        );

    if (
        $raw === false ||
        trim($raw) === ''
    ) {
        throw new InvalidArgumentException(
            'No se recibieron datos para procesar.'
        );
    }

    $data = json_decode(
        $raw,
        true
    );

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

/**
 * Valida y normaliza los datos del formulario.
 */
function validarCertificacion(
    array $data
): array {
    $codigo = mb_strtoupper(
        trim(
            (string) (
                $data[
                    'codigo_certificacion'
                ] ?? ''
            )
        )
    );

    $nombre = trim(
        (string) (
            $data[
                'nombre_certificacion'
            ] ?? ''
        )
    );

    $tipoProceso = normalizarNullable(
        $data[
            'tipo_proceso'
        ] ?? null
    );

    $descripcion = normalizarNullable(
        $data[
            'descripcion'
        ] ?? null
    );

    if ($codigo === '') {
        throw new InvalidArgumentException(
            'El código de certificación es obligatorio.'
        );
    }

    if ($nombre === '') {
        throw new InvalidArgumentException(
            'El nombre de la certificación es obligatorio.'
        );
    }

    if (mb_strlen($codigo) > 30) {
        throw new InvalidArgumentException(
            'El código no puede superar los 30 caracteres.'
        );
    }

    if (mb_strlen($nombre) > 150) {
        throw new InvalidArgumentException(
            'El nombre no puede superar los 150 caracteres.'
        );
    }

    if (
        $tipoProceso !== null &&
        mb_strlen($tipoProceso) > 50
    ) {
        throw new InvalidArgumentException(
            'El tipo de proceso no puede superar los 50 caracteres.'
        );
    }

    return [
        'codigo_certificacion' =>
            $codigo,

        'nombre_certificacion' =>
            $nombre,

        'tipo_proceso' =>
            $tipoProceso,

        'descripcion' =>
            $descripcion,
    ];
}

/**
 * Validar idCR.
 */
function validarId(
    mixed $id
): int {
    $idValidado = filter_var(
        $id,
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1
            ],
        ]
    );

    if ($idValidado === false) {
        throw new InvalidArgumentException(
            'El identificador de la certificación no es válido.'
        );
    }

    return (int) $idValidado;
}

/**
 * Convierte cadenas vacías en NULL.
 */
function normalizarNullable(
    mixed $value
): ?string {
    if ($value === null) {
        return null;
    }

    $value = trim(
        (string) $value
    );

    return (
        $value === ''
            ? null
            : $value
    );
}

/**
 * Respuesta JSON general para altas,
 * modificaciones, eliminaciones y errores.
 */
function responder(
    bool $success,
    string $message,
    mixed $data = null,
    int $statusCode = 200
): never {
    http_response_code(
        $statusCode
    );

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