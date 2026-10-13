<?php
date_default_timezone_set('America/Mexico_City');

header('Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Allow: POST, OPTIONS');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

/**
 * Envía una respuesta JSON uniforme y termina la ejecución.
 */
function responderJson(array $respuesta, int $codigoHttp = 200): void
{
    http_response_code($codigoHttp);
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Obtiene un texto enviado por POST sin aceptar arreglos u otros tipos.
 */
function obtenerTextoPost(string $campo): ?string
{
    if (!isset($_POST[$campo]) || is_array($_POST[$campo])) {
        return null;
    }

    return trim((string) $_POST[$campo]);
}

/**
 * Valida enteros positivos dentro del rango de SQL Server int.
 */
function obtenerEnteroPositivoPost(string $campo): ?int
{
    $valor = obtenerTextoPost($campo);

    if ($valor === null || !preg_match('/^\d+$/', $valor)) {
        return null;
    }

    $entero = filter_var($valor, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 2147483647]
    ]);

    return $entero === false ? null : $entero;
}

/**
 * Valida enteros que pueden iniciar en cero dentro del rango de SQL Server int.
 */
function obtenerEnteroNoNegativoPost(string $campo): ?int
{
    $valor = obtenerTextoPost($campo);

    if ($valor === null || !preg_match('/^\d+$/', $valor)) {
        return null;
    }

    $entero = filter_var($valor, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 0, 'max_range' => 2147483647]
    ]);

    return $entero === false ? null : $entero;
}

/**
 * Comprueba UTF-8, contenido y longitud máxima sin modificar el valor.
 */
function textoEsValido(?string $valor, int $longitudMaxima): bool
{
    if ($valor === null || $valor === '') {
        return false;
    }

    if (function_exists('mb_check_encoding') && !mb_check_encoding($valor, 'UTF-8')) {
        return false;
    }

    $longitud = function_exists('mb_strlen')
        ? mb_strlen($valor, 'UTF-8')
        : strlen($valor);
    $contieneCaracteresControl = preg_match(
        '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u',
        $valor
    );

    return $longitud <= $longitudMaxima && $contieneCaracteresControl === 0;
}

/**
 * Obtiene la fila única y activa de un estatus mediante su CLAVE.
 * id_estatus_actual almacena IDe, por lo que nunca debe recibir la clave.
 */
function obtenerEstatusActivoPorClave(PDO $conn, string $clave): ?array
{
    $consulta = $conn->prepare(
        "SELECT IDe, CLAVE, NOMBRE
         FROM SA_estatus_anormalidad
         WHERE LTRIM(RTRIM(CLAVE)) = :clave
           AND activo = 1"
    );
    $consulta->execute([':clave' => $clave]);
    $estatus = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    return count($estatus) === 1 ? $estatus[0] : null;
}

/**
 * Bloquea la anormalidad padre durante una operación de escritura y evita
 * cualquier cambio posterior a su finalización. Debe llamarse dentro de la
 * misma transacción que realiza el INSERT o UPDATE correspondiente.
 */
function bloquearAnormalidadParaEscritura(PDO $conn, int $idAnormalidad): array
{
    $consulta = $conn->prepare(
        "SELECT
            A.id_anormalidad,
            A.no_seguimiento,
            A.id_estatus_actual,
            E.CLAVE AS clave_estatus,
            E.NOMBRE AS estatus_actual
         FROM SA_anormalidad A WITH (UPDLOCK, HOLDLOCK)
         INNER JOIN SA_estatus_anormalidad E
            ON E.IDe = A.id_estatus_actual
         WHERE A.id_anormalidad = :idAnormalidad"
    );
    $consulta->execute([':idAnormalidad' => $idAnormalidad]);
    $anormalidad = $consulta->fetch(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    if (!$anormalidad) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        responderJson([
            'status' => 'error',
            'mensaje' => 'No se encontró la anormalidad seleccionada.'
        ], 404);
    }

    if (trim((string) $anormalidad['clave_estatus']) === '6') {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        responderJson([
            'status' => 'error',
            'codigo' => 'ANORMALIDAD_FINALIZADA',
            'mensaje' => 'La anormalidad está finalizada y ya no admite nuevos registros ni modificaciones.',
            'data' => [
                'id_anormalidad' => (int) $anormalidad['id_anormalidad'],
                'id_estatus_actual' => (int) $anormalidad['id_estatus_actual'],
                'clave_estatus' => trim((string) $anormalidad['clave_estatus']),
                'estatus_actual' => $anormalidad['estatus_actual']
            ]
        ], 409);
    }

    return $anormalidad;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson([
        'status' => 'error',
        'mensaje' => 'Método no permitido.'
    ], 405);
}

/*
 * Evita envíos simples desde formularios de otros sitios mientras se integra
 * el token CSRF definitivo con la futura sesión de usuario.
 */
if (strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest') !== 0) {
    responderJson([
        'status' => 'error',
        'mensaje' => 'La solicitud no fue originada por la aplicación.'
    ], 403);
}

/* Arranca la sesion y expone exigirPermiso(). */
require_once('./sesion.php');

include('./conexion.php');

/*
 * La firma del cierre solo la registra el gerente del área responsable.
 * La clave proviene de SA_factor, y SA_cierre_reporte_calidad guarda cuál
 * quedó asignada en id_factor_gerente.
 */
const GERENTE_POR_FACTOR = [
    'MFT' => 'ger_manufactura',
    'QA'  => 'ger_calidad',
    'PE'  => 'ger_mantenimiento'
];

/*
 * El cierre documentado por Staff QA solo puede registrarlo personal de
 * Calidad. El administrador se conserva como respaldo operativo del sistema.
 */
const ROLES_CIERRE_QA = ['sup_calidad', 'ger_calidad', 'admin'];

$opcion = $_POST['opcion'] ?? '';
$rutaFirmaCreada = null;

try {
    switch ($opcion) {
        case '1':
            /* ============================================================
               Registro inicial de una anormalidad
               ============================================================ */
            exigirPermiso('anormalidades', 'registro_inicial');

            $idArea = obtenerEnteroPositivoPost('idArea');
            $codigoLinea = obtenerTextoPost('codigoLinea');
            $detalleAnormalidad = obtenerTextoPost('detalleAnormalidad');
            $numeroLote = obtenerTextoPost('numeroLote');
            $numeroSerial = obtenerTextoPost('numeroSerial');
            $cantidad = obtenerEnteroPositivoPost('cantidad');
            $idEstacionDeteccion = obtenerEnteroPositivoPost('idEstacionDeteccion');
            $idEstacionGeneracion = obtenerEnteroPositivoPost('idEstacionGeneracion');
            /* Modelo opcional: vacío se guarda como NULL (hay líneas sin modelos). */
            $modeloRecibido = obtenerTextoPost('idModelo');
            $idModelo = ($modeloRecibido === null || $modeloRecibido === '')
                ? null
                : obtenerEnteroPositivoPost('idModelo');
            /* Quien registra es quien tiene la sesión abierta, no lo que
               llegue en el formulario. */
            $nominaRegistra = nominaEnSesion();
            $nombreRegistra = nombreEnSesion();
            $erroresValidacion = [];

            if ($idArea === null) {
                $erroresValidacion[] = 'El área no es válida.';
            }

            if (!textoEsValido($codigoLinea, 20)) {
                $erroresValidacion[] = 'La línea no es válida.';
            }

            if (!textoEsValido($detalleAnormalidad, 500)) {
                $erroresValidacion[] = 'El detalle debe contener entre 1 y 500 caracteres.';
            }

            if (!textoEsValido($numeroLote, 50)) {
                $erroresValidacion[] = 'El número de lote debe contener entre 1 y 50 caracteres.';
            }

            if (!textoEsValido($numeroSerial, 80)) {
                $erroresValidacion[] = 'El número de serial debe contener entre 1 y 80 caracteres.';
            }

            if ($cantidad === null) {
                $erroresValidacion[] = 'La cantidad debe ser un entero mayor que cero.';
            }

            if ($idEstacionDeteccion === null) {
                $erroresValidacion[] = 'La estación de detección no es válida.';
            }

            if ($idEstacionGeneracion === null) {
                $erroresValidacion[] = 'La estación de generación no es válida.';
            }

            if ($modeloRecibido !== null && $modeloRecibido !== '' && $idModelo === null) {
                $erroresValidacion[] = 'El modelo no es válido.';
            }

            if ($nominaRegistra === null || !preg_match('/^\d{1,30}$/', $nominaRegistra)) {
                $erroresValidacion[] = 'La nómina de quien registra no es válida.';
            }

            if ($erroresValidacion) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La información enviada está incompleta o no es válida.',
                    'errores' => $erroresValidacion
                ], 422);
            }

            /* Validar relaciones que no deben confiarse al navegador. */
            $sqlValidaciones = "SELECT
                codigo_linea_canonico =
                (
                    SELECT TOP (1) L.codigo_linea
                    FROM SPC_LINEAS L
                    INNER JOIN SPC_AREAS A ON A.idArea = L.idArea
                    WHERE L.codigo_linea = :codigoLineaCanonico
                      AND A.idArea = :idAreaCanonico
                ),
                linea_valida = CASE WHEN EXISTS
                (
                    SELECT 1
                    FROM SPC_LINEAS L
                    INNER JOIN SPC_AREAS A ON A.idArea = L.idArea
                    WHERE L.codigo_linea = :codigoLineaArea
                      AND A.idArea = :idArea
                ) THEN 1 ELSE 0 END,
                estacion_deteccion_valida = CASE WHEN EXISTS
                (
                    SELECT 1
                    FROM SPC_ESTACIONES E
                    WHERE E.id_estacion = :idEstacionDeteccion
                      AND E.codigo_linea = :codigoLineaDeteccion
                ) THEN 1 ELSE 0 END,
                estacion_generacion_valida = CASE WHEN EXISTS
                (
                    SELECT 1
                    FROM SPC_ESTACIONES E
                    WHERE E.id_estacion = :idEstacionGeneracion
                      AND E.codigo_linea = :codigoLineaGeneracion
                ) THEN 1 ELSE 0 END";
            $consultaValidaciones = $conn->prepare($sqlValidaciones);
            $consultaValidaciones->execute([
                ':codigoLineaCanonico' => $codigoLinea,
                ':idAreaCanonico' => $idArea,
                ':codigoLineaArea' => $codigoLinea,
                ':idArea' => $idArea,
                ':idEstacionDeteccion' => $idEstacionDeteccion,
                ':codigoLineaDeteccion' => $codigoLinea,
                ':idEstacionGeneracion' => $idEstacionGeneracion,
                ':codigoLineaGeneracion' => $codigoLinea
            ]);
            $validaciones = $consultaValidaciones->fetch(PDO::FETCH_ASSOC);
            $consultaValidaciones->closeCursor();

            if (!$validaciones || (int) $validaciones['linea_valida'] !== 1) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La línea seleccionada no pertenece al área indicada.'
                ], 422);
            }

            // Usar siempre el código almacenado para unificar bloqueo, FK y folio.
            $codigoLinea = (string) $validaciones['codigo_linea_canonico'];

            if ((int) $validaciones['estacion_deteccion_valida'] !== 1 ||
                (int) $validaciones['estacion_generacion_valida'] !== 1) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Una o más estaciones no pertenecen a la línea seleccionada.'
                ], 422);
            }

            /*
             * Si se eligió modelo, debe estar activo y asignado a la línea en
             * SRD_MODELOS_LINEA. La FK de SA_anormalidad solo apunta a
             * SRD_MODELOS, así que esta pertenencia la garantiza la aplicación.
             */
            if ($idModelo !== null) {
                $consultaModelo = $conn->prepare(
                    "SELECT CASE WHEN EXISTS
                     (
                         SELECT 1
                         FROM SRD_MODELOS_LINEA ML
                         INNER JOIN SRD_MODELOS M
                             ON M.idModelo = ML.idModelo
                         WHERE ML.idModelo = :idModelo
                           AND ML.codigo_linea = :codigoLinea
                           AND M.activo = 1
                     ) THEN 1 ELSE 0 END"
                );
                $consultaModelo->execute([
                    ':idModelo' => $idModelo,
                    ':codigoLinea' => $codigoLinea
                ]);
                $modeloValido = (int) $consultaModelo->fetchColumn() === 1;
                $consultaModelo->closeCursor();

                if (!$modeloValido) {
                    responderJson([
                        'status' => 'error',
                        'mensaje' => 'El modelo seleccionado no pertenece a la línea o ya no está activo.'
                    ], 422);
                }
            }

            /*
             * El nombre ya viene de la sesión, así que se deja de consultar el
             * catálogo de empleados: la identidad de quien opera el sistema la
             * define la tabla usuarios, no el padrón de nómina.
             */
            if (!textoEsValido($nominaRegistra, 30) || !textoEsValido($nombreRegistra, 250)) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No fue posible identificar al usuario de la sesión.'
                ], 422);
            }

            $conn->beginTransaction();

            $estatusInicial = obtenerEstatusActivoPorClave($conn, '1');

            if (!$estatusInicial) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La clave 1 del estatus inicial no está configurada de forma única y activa.'
                ], 409);
            }

            /* La fecha y el turno se determinan con el reloj del servidor SQL. */
            $sqlFechaTurno = "DECLARE @ahora datetime2(0) = SYSDATETIME();
                SELECT
                    fecha_servidor = CONVERT(varchar(19), @ahora, 126),
                    anio = YEAR(@ahora),
                    clave_turno = CASE
                        WHEN CAST(@ahora AS time) >= CAST('08:00:00' AS time)
                         AND CAST(@ahora AS time) < CAST('20:00:00' AS time)
                        THEN '1' ELSE '2'
                    END";
            $consultaFechaTurno = $conn->query($sqlFechaTurno);
            $fechaTurno = $consultaFechaTurno->fetch(PDO::FETCH_ASSOC);
            $consultaFechaTurno->closeCursor();

            if (!$fechaTurno) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No fue posible determinar la fecha y el turno del registro.'
                ], 500);
            }

            $consultaTurno = $conn->prepare(
                "SELECT TOP (1) IDe
                 FROM SA_turno
                 WHERE CLAVE = :claveTurno AND activo = 1
                 ORDER BY IDe"
            );
            $consultaTurno->execute([':claveTurno' => $fechaTurno['clave_turno']]);
            $turno = $consultaTurno->fetch(PDO::FETCH_ASSOC);
            $consultaTurno->closeCursor();

            if (!$turno) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No existe un turno activo para el horario actual.'
                ], 409);
            }

            $anio = (int) $fechaTurno['anio'];
            $recursoBloqueo = 'SA_ANORMALIDAD|' . $anio . '|' . $codigoLinea;
            $sqlBloqueo = "DECLARE @resultado int;
                EXEC @resultado = sys.sp_getapplock
                    @Resource = :recurso,
                    @LockMode = 'Exclusive',
                    @LockOwner = 'Transaction',
                    @LockTimeout = 10000;
                SELECT @resultado AS resultado_bloqueo";
            $consultaBloqueo = $conn->prepare($sqlBloqueo);
            $consultaBloqueo->execute([':recurso' => $recursoBloqueo]);
            $bloqueo = $consultaBloqueo->fetch(PDO::FETCH_ASSOC);
            $consultaBloqueo->closeCursor();

            if (!$bloqueo || (int) $bloqueo['resultado_bloqueo'] < 0) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El registro está ocupado por otra operación. Inténtalo nuevamente.'
                ], 409);
            }

            /*
             * MAX + 1 evita reutilizar un consecutivo existente si en algún
             * momento se elimina físicamente un registro intermedio.
             */
            $sqlConsecutivo = "SELECT consecutivo = ISNULL(MAX(TRY_CONVERT(bigint,
                    CASE
                        WHEN CHARINDEX('/', REVERSE(no_seguimiento)) > 0
                        THEN RIGHT(no_seguimiento, CHARINDEX('/', REVERSE(no_seguimiento)) - 1)
                    END
                )), 0) + 1
                FROM SA_anormalidad WITH (UPDLOCK, HOLDLOCK)
                WHERE codigo_linea = :codigoLinea
                  AND fecha_hora_deteccion >= DATEFROMPARTS(:anioInicio, 1, 1)
                  AND fecha_hora_deteccion < DATEFROMPARTS(:anioFin, 1, 1)";
            $consultaConsecutivo = $conn->prepare($sqlConsecutivo);
            $consultaConsecutivo->execute([
                ':codigoLinea' => $codigoLinea,
                ':anioInicio' => $anio,
                ':anioFin' => $anio + 1
            ]);
            $registroConsecutivo = $consultaConsecutivo->fetch(PDO::FETCH_ASSOC);
            $consultaConsecutivo->closeCursor();
            $consecutivo = (int) ($registroConsecutivo['consecutivo'] ?? 0);

            if ($consecutivo <= 0) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No fue posible generar el número de seguimiento.'
                ], 500);
            }

            $numeroSeguimiento = $anio . '/' . $codigoLinea . '/' .
                str_pad((string) $consecutivo, 3, '0', STR_PAD_LEFT);

            if (strlen($numeroSeguimiento) > 50) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El número de seguimiento generado excede la longitud permitida.'
                ], 500);
            }

            $sqlInsertar = "INSERT INTO SA_anormalidad
                (
                    no_seguimiento,
                    fecha_hora_deteccion,
                    turno,
                    codigo_linea,
                    id_tipo_anormalidad,
                    detalle_anormalidad,
                    no_lote,
                    no_serial,
                    cantidad,
                    id_estacion_deteccion,
                    id_estacion_generacion,
                    nomina_registra,
                    nombre_registra,
                    id_estatus_actual,
                    idModelo
                )
                OUTPUT
                    INSERTED.id_anormalidad,
                    INSERTED.no_seguimiento,
                    CONVERT(varchar(19), INSERTED.fecha_hora_deteccion, 126) AS fecha_hora_deteccion,
                    INSERTED.turno,
                    INSERTED.codigo_linea,
                    INSERTED.id_estatus_actual,
                    INSERTED.idModelo
                VALUES
                (
                    :numeroSeguimiento,
                    :fechaDeteccion,
                    :turno,
                    :codigoLinea,
                    NULL,
                    :detalleAnormalidad,
                    :numeroLote,
                    :numeroSerial,
                    :cantidad,
                    :idEstacionDeteccion,
                    :idEstacionGeneracion,
                    :nominaRegistra,
                    :nombreRegistra,
                    :idEstatusInicial,
                    :idModelo
                )";
            $consultaInsertar = $conn->prepare($sqlInsertar);
            $consultaInsertar->execute([
                ':numeroSeguimiento' => $numeroSeguimiento,
                ':fechaDeteccion' => $fechaTurno['fecha_servidor'],
                ':turno' => (int) $turno['IDe'],
                ':codigoLinea' => $codigoLinea,
                ':detalleAnormalidad' => $detalleAnormalidad,
                ':numeroLote' => $numeroLote,
                ':numeroSerial' => $numeroSerial,
                ':cantidad' => $cantidad,
                ':idEstacionDeteccion' => $idEstacionDeteccion,
                ':idEstacionGeneracion' => $idEstacionGeneracion,
                ':nominaRegistra' => $nominaRegistra,
                ':nombreRegistra' => $nombreRegistra,
                ':idEstatusInicial' => (int) $estatusInicial['IDe'],
                ':idModelo' => $idModelo
            ]);
            $anormalidadRegistrada = $consultaInsertar->fetch(PDO::FETCH_ASSOC);
            $consultaInsertar->closeCursor();

            if (!$anormalidadRegistrada) {
                throw new RuntimeException('El INSERT no devolvió el registro creado.');
            }

            $conn->commit();

            responderJson([
                'status' => 'ok',
                'mensaje' => 'La anormalidad se registró correctamente.',
                'data' => $anormalidadRegistrada
            ], 201);

        case '2':
            exigirPermiso('anormalidades', 'ver');

            $sql = "SELECT idArea AS id_area,
                           nombreArea AS nombre_area
                    FROM SPC_AREAS
                    ORDER BY nombreArea";
            $consulta = $conn->prepare($sql);
            $consulta->execute();

            responderJson([
                'status' => 'ok',
                'data' => $consulta->fetchAll(PDO::FETCH_ASSOC)
            ]);

        case '3':
            exigirPermiso('anormalidades', 'ver');

            $idArea = filter_input(INPUT_POST, 'idArea', FILTER_VALIDATE_INT);

            if ($idArea === false || $idArea === null || $idArea <= 0) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El área seleccionada no es válida.'
                ], 400);
            }

            $sql = "SELECT codigo_linea,
                           nombre_linea
                    FROM SPC_LINEAS
                    WHERE idArea = :idArea
                    ORDER BY nombre_linea";
            $consulta = $conn->prepare($sql);
            $consulta->execute([':idArea' => $idArea]);

            responderJson([
                'status' => 'ok',
                'data' => $consulta->fetchAll(PDO::FETCH_ASSOC)
            ]);

        case '4':
            exigirPermiso('anormalidades', 'ver');

            $codigoLinea = trim($_POST['codigoLinea'] ?? '');

            if ($codigoLinea === '') {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La línea seleccionada no es válida.'
                ], 400);
            }

            $sql = "SELECT id_estacion,
                           nombre_estacion
                    FROM SPC_ESTACIONES
                    WHERE codigo_linea = :codigoLinea
                    ORDER BY nombre_estacion";
            $consulta = $conn->prepare($sql);
            $consulta->execute([':codigoLinea' => $codigoLinea]);

            responderJson([
                'status' => 'ok',
                'data' => $consulta->fetchAll(PDO::FETCH_ASSOC)
            ]);

        case '5':
            exigirPermiso('anormalidades', 'ver');

            // Todos los catálogos SA se consultan únicamente con registros activos.
            $sql = "SELECT IDe AS id_turno,
                           CLAVE AS clave,
                           NOMBRE AS nombre_turno
                    FROM SA_turno
                    WHERE activo = 1
                    ORDER BY IDe";
            $consulta = $conn->prepare($sql);
            $consulta->execute();

            responderJson([
                'status' => 'ok',
                'data' => $consulta->fetchAll(PDO::FETCH_ASSOC)
            ]);

        case '6':
            exigirPermiso('anormalidades', 'ver');

            $codigoLinea = trim($_POST['codigoLinea'] ?? '');

            if ($codigoLinea === '' || strlen($codigoLinea) > 20) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La línea seleccionada no es válida.'
                ], 400);
            }

            /*
             * Esta consulta genera solo una referencia visual. No se aplican
             * bloqueos porque el consecutivo definitivo deberá recalcularse
             * dentro de la futura transacción que inserte la anormalidad.
             */
            $sql = "SELECT L.codigo_linea,
                           YEAR(GETDATE()) AS anio,
                           (
                               SELECT ISNULL(MAX(TRY_CONVERT(bigint,
                                   CASE
                                       WHEN CHARINDEX('/', REVERSE(A.no_seguimiento)) > 0
                                       THEN RIGHT(
                                           A.no_seguimiento,
                                           CHARINDEX('/', REVERSE(A.no_seguimiento)) - 1
                                       )
                                   END
                               )), 0) + 1
                               FROM SA_anormalidad A
                               WHERE A.codigo_linea = L.codigo_linea
                                 AND A.fecha_hora_deteccion >= DATEFROMPARTS(YEAR(GETDATE()), 1, 1)
                                 AND A.fecha_hora_deteccion < DATEFROMPARTS(YEAR(GETDATE()) + 1, 1, 1)
                           ) AS consecutivo
                    FROM SPC_LINEAS L
                    WHERE L.codigo_linea = :codigoLinea";
            $consulta = $conn->prepare($sql);
            $consulta->execute([':codigoLinea' => $codigoLinea]);
            $referencia = $consulta->fetch(PDO::FETCH_ASSOC);

            if (!$referencia) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No se encontró la línea seleccionada.'
                ], 404);
            }

            $consecutivo = (string) $referencia['consecutivo'];
            $numeroReferencia = $referencia['anio'] . '/' .
                $referencia['codigo_linea'] . '/' .
                str_pad($consecutivo, 3, '0', STR_PAD_LEFT);

            responderJson([
                'status' => 'ok',
                'data' => [
                    'numero_referencia' => $numeroReferencia,
                    'consecutivo' => (int) $referencia['consecutivo'],
                    'anio' => (int) $referencia['anio'],
                    'codigo_linea' => $referencia['codigo_linea']
                ]
            ]);

        case '7':
            /* ============================================================
               Listado general para DataTables
               Los filtros generales se aplican en SQL Server. La búsqueda,
               el orden y la paginación de los resultados son locales.
               ============================================================ */
            exigirPermiso('anormalidades', 'ver');

            $fechaInicio = obtenerTextoPost('fechaInicio');
            $fechaFin = obtenerTextoPost('fechaFin');
            $codigoLinea = obtenerTextoPost('codigoLinea');
            $filtrosEnteros = [
                'idTurno' => 'El turno seleccionado no es válido.',
                'idFactorGerente' => 'El gerente seleccionado no es válido.',
                'idArea' => 'El área seleccionada no es válida.'
            ];
            $valoresEnteros = [];

            foreach (['fechaInicio', 'fechaFin', 'codigoLinea'] as $campoTexto) {
                if (isset($_POST[$campoTexto]) && is_array($_POST[$campoTexto])) {
                    responderJson([
                        'status' => 'error',
                        'mensaje' => 'Uno de los filtros enviados no es válido.'
                    ], 400);
                }
            }

            foreach ($filtrosEnteros as $campo => $mensajeError) {
                if (isset($_POST[$campo]) && is_array($_POST[$campo])) {
                    responderJson([
                        'status' => 'error',
                        'mensaje' => $mensajeError
                    ], 400);
                }

                $valor = obtenerTextoPost($campo);

                if ($valor === null || $valor === '') {
                    $valoresEnteros[$campo] = null;
                    continue;
                }

                if (!preg_match('/^\d+$/', $valor)) {
                    responderJson([
                        'status' => 'error',
                        'mensaje' => $mensajeError
                    ], 400);
                }

                $valorEntero = filter_var($valor, FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1, 'max_range' => 2147483647]
                ]);

                if ($valorEntero === false) {
                    responderJson([
                        'status' => 'error',
                        'mensaje' => $mensajeError
                    ], 400);
                }

                $valoresEnteros[$campo] = $valorEntero;
            }

            if ($codigoLinea !== null && $codigoLinea !== '' &&
                !textoEsValido($codigoLinea, 20)) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La línea seleccionada no es válida.'
                ], 400);
            }

            $validarFechaIso = static function (?string $fecha): bool {
                if ($fecha === null || $fecha === '') {
                    return true;
                }

                $fechaValidada = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
                return $fechaValidada !== false && $fechaValidada->format('Y-m-d') === $fecha;
            };

            if (!$validarFechaIso($fechaInicio) || !$validarFechaIso($fechaFin)) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El rango de fechas enviado no es válido.'
                ], 400);
            }

            $condiciones = [];
            $parametros = [];

            if ($fechaInicio !== null && $fechaInicio !== '' &&
                $fechaFin !== null && $fechaFin !== '') {
                if ($fechaInicio > $fechaFin) {
                    responderJson([
                        'status' => 'error',
                        'mensaje' => 'La fecha inicial no puede ser posterior a la fecha final.'
                    ], 422);
                }

                $condiciones[] = 'A.fecha_hora_deteccion >= CONVERT(date, :fechaInicio, 23)';
                $condiciones[] = 'A.fecha_hora_deteccion < DATEADD(day, 1, CONVERT(date, :fechaFin, 23))';
                $parametros[':fechaInicio'] = $fechaInicio;
                $parametros[':fechaFin'] = $fechaFin;
            } else {
                /* Un rango incompleto siempre utiliza los últimos dos meses. */
                $condiciones[] = 'A.fecha_hora_deteccion >= DATEADD(month, -2, SYSDATETIME())';
            }

            if ($valoresEnteros['idTurno'] !== null) {
                $condiciones[] = 'A.turno = :idTurno';
                $parametros[':idTurno'] = $valoresEnteros['idTurno'];
            }

            if ($valoresEnteros['idArea'] !== null) {
                $condiciones[] = 'L.idArea = :idArea';
                $parametros[':idArea'] = $valoresEnteros['idArea'];
            }

            if ($codigoLinea !== null && $codigoLinea !== '') {
                $condiciones[] = 'A.codigo_linea = :codigoLinea';
                $parametros[':codigoLinea'] = $codigoLinea;
            }

            /*
             * Filtra por la gerencia que debe autorizar el cierre: el factor
             * que el cierre de Staff QA dejó asignado en id_factor_gerente.
             * Las anormalidades que todavía no llegan a esa etapa no tienen
             * fila en SA_cierre_reporte_calidad, así que quedan fuera.
             */
            if ($valoresEnteros['idFactorGerente'] !== null) {
                $condiciones[] = "EXISTS
                    (
                        SELECT 1
                        FROM SA_cierre_reporte_calidad CRC_FILTRO
                        WHERE CRC_FILTRO.id_anormalidad = A.id_anormalidad
                          AND CRC_FILTRO.id_factor_gerente = :idFactorGerente
                    )";
                $parametros[':idFactorGerente'] = $valoresEnteros['idFactorGerente'];
            }

            $clausulaFiltros = ' WHERE ' . implode(' AND ', $condiciones);

            $sql = "SELECT
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
                        CONVERT(varchar(19), A.fecha_hora_deteccion, 126) AS fecha_hora_deteccion,
                        A.id_estatus_actual,
                        EA.CLAVE AS clave_estatus,
                        EA.NOMBRE AS estatus_actual,
                        A.turno AS id_turno,
                        COALESCE(NULLIF(T.CLAVE, ''), CONVERT(nvarchar(20), A.turno)) AS turno,
                        L.idArea AS id_area,
                        AR.nombreArea AS nombre_area,
                        A.codigo_linea,
                        L.nombre_linea,
                        A.id_estacion_deteccion,
                        ED.nombre_estacion AS estacion_deteccion,
                        COALESCE(NULLIF(A.[nomina detector], ''), A.nomina_registra) AS nomina_personal,
                        COALESCE(NULLIF(A.nombre_detector, ''), A.nombre_registra) AS nombre_personal,
                        COALESCE(FAC.factores, 'Sin asignar') AS factores,
                        CASE WHEN EXISTS
                        (
                            SELECT 1
                            FROM SA_registro_general_reincidencia R
                            WHERE R.id_anormalidad = A.id_anormalidad
                        ) THEN 1 ELSE 0 END AS tiene_reincidencia
                    FROM SA_anormalidad A
                    INNER JOIN SPC_LINEAS L
                        ON L.codigo_linea = A.codigo_linea
                    INNER JOIN SPC_AREAS AR
                        ON AR.idArea = L.idArea
                    INNER JOIN SA_turno T
                        ON T.IDe = A.turno
                    INNER JOIN SA_estatus_anormalidad EA
                        ON EA.IDe = A.id_estatus_actual
                    LEFT JOIN SPC_ESTACIONES ED
                        ON ED.id_estacion = A.id_estacion_deteccion
                       AND ED.codigo_linea = A.codigo_linea
                    OUTER APPLY
                    (
                        SELECT STUFF
                        (
                            (
                                SELECT ', ' + F.NOMBRE
                                FROM SA_anormalidad_factor AF
                                INNER JOIN SA_factor F
                                    ON F.IDe = AF.id_factor
                                WHERE AF.id_anormalidad = A.id_anormalidad
                                ORDER BY F.NOMBRE
                                FOR XML PATH(''), TYPE
                            ).value('.', 'nvarchar(max)'),
                            1,
                            2,
                            ''
                        ) AS factores
                    ) FAC" .
                    $clausulaFiltros .
                    "
                    ORDER BY A.fecha_hora_deteccion DESC,
                             A.id_anormalidad DESC";
            $consulta = $conn->prepare($sql);
            $consulta->execute($parametros);

            responderJson([
                'status' => 'ok',
                'data' => $consulta->fetchAll(PDO::FETCH_ASSOC)
            ]);

        case '8':
            /* ============================================================
               Detalle del registro inicial de una anormalidad
               ============================================================ */
            exigirPermiso('anormalidades', 'ver');

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');

            if ($idAnormalidad === null) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El identificador de la anormalidad no es válido.'
                ], 400);
            }

            $sql = "SELECT
                        A.id_anormalidad,
                        A.no_seguimiento,
                        CONVERT(varchar(19), A.fecha_hora_deteccion, 126) AS fecha_hora_deteccion,
                        A.turno AS id_turno,
                        COALESCE(NULLIF(T.CLAVE, ''), CONVERT(nvarchar(20), A.turno)) AS turno,
                        A.codigo_linea,
                        L.nombre_linea,
                        L.idArea AS id_area,
                        AR.nombreArea AS nombre_area,
                        A.idModelo,
                        MO.modelo,
                        A.id_tipo_anormalidad,
                        TA.NOMBRE AS tipo_anormalidad,
                        A.detalle_anormalidad,
                        A.no_lote,
                        A.no_serial,
                        A.cantidad,
                        A.id_estacion_deteccion,
                        ED.nombre_estacion AS estacion_deteccion,
                        A.id_estacion_generacion,
                        EG.nombre_estacion AS estacion_generacion,
                        A.[nomina detector] AS nomina_detector,
                        A.nombre_detector,
                        A.nomina_registra,
                        A.nombre_registra,
                        A.id_estatus_actual,
                        EA.CLAVE AS clave_estatus,
                        EA.NOMBRE AS estatus_actual,
                        (
                            SELECT COUNT(*)
                            FROM SA_registro_general_reincidencia R
                            INNER JOIN SA_registro_detalle_reincidencia DR
                                ON DR.id_reincidencia = R.id_reincidencia
                            WHERE R.id_anormalidad = A.id_anormalidad
                        ) AS total_reincidencias
                    FROM SA_anormalidad A
                    INNER JOIN SPC_LINEAS L
                        ON L.codigo_linea = A.codigo_linea
                    INNER JOIN SPC_AREAS AR
                        ON AR.idArea = L.idArea
                    INNER JOIN SA_turno T
                        ON T.IDe = A.turno
                    INNER JOIN SA_estatus_anormalidad EA
                        ON EA.IDe = A.id_estatus_actual
                    LEFT JOIN SA_tipo_anormalidad TA
                        ON TA.IDe = A.id_tipo_anormalidad
                    /* Opcional: registros previos y líneas sin modelos lo tienen en NULL.
                       Se muestra aunque el modelo se haya desactivado después. */
                    LEFT JOIN SRD_MODELOS MO
                        ON MO.idModelo = A.idModelo
                    LEFT JOIN SPC_ESTACIONES ED
                        ON ED.id_estacion = A.id_estacion_deteccion
                       AND ED.codigo_linea = A.codigo_linea
                    LEFT JOIN SPC_ESTACIONES EG
                        ON EG.id_estacion = A.id_estacion_generacion
                       AND EG.codigo_linea = A.codigo_linea
                    WHERE A.id_anormalidad = :idAnormalidad";
            $consulta = $conn->prepare($sql);
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);
            $anormalidad = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$anormalidad) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No se encontró la anormalidad solicitada.'
                ], 404);
            }

            /*
             * Se muestran todos los factores registrados, aunque posteriormente
             * alguno haya sido desactivado en el catálogo.
             */
            $consulta = $conn->prepare(
                "SELECT
                    AF.id_factor,
                    F.CLAVE AS clave_factor,
                    F.NOMBRE AS nombre_factor,
                    AF.nomina_responsable,
                    AF.nombre_responsable
                 FROM SA_anormalidad_factor AF
                 INNER JOIN SA_factor F
                    ON F.IDe = AF.id_factor
                 WHERE AF.id_anormalidad = :idAnormalidad
                 ORDER BY F.NOMBRE, AF.id_factor"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);
            $anormalidad['factores_atencion'] = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            responderJson([
                'status' => 'ok',
                'data' => $anormalidad
            ]);

        case '9':
            /* ============================================================
               Catálogos utilizados en el flujo de anormalidades
               Se agrupan en una respuesta para cargarlos una sola vez.
               ============================================================ */
            exigirPermiso('anormalidades', 'ver');

            $consultasCatalogos = [
                'tipos_anormalidad' => "SELECT IDe AS id, CLAVE AS clave,
                                                NOMBRE AS nombre, DESCRIPCION AS descripcion
                                         FROM SA_tipo_anormalidad
                                         WHERE activo = 1
                                         ORDER BY TRY_CONVERT(int, CLAVE), CLAVE, NOMBRE",
                'criticidades' => "SELECT IDe AS id, CLAVE AS clave,
                                           NOMBRE AS nombre, DESCRIPCION AS descripcion
                                    FROM SA_criticidad
                                    WHERE activo = 1
                                    ORDER BY TRY_CONVERT(int, CLAVE), CLAVE, NOMBRE",
                'niveles_respuesta' => "SELECT IDe AS id, CLAVE AS clave,
                                                NOMBRE AS nombre, DESCRIPCION AS descripcion
                                         FROM SA_nivel_respuesta
                                         WHERE activo = 1
                                         ORDER BY TRY_CONVERT(int, CLAVE), CLAVE, NOMBRE",
                'clasificaciones_5m' => "SELECT IDe AS id, CLAVE AS clave,
                                                 NOMBRE AS nombre, DESCRIPCION AS descripcion
                                          FROM SA_5m
                                          WHERE activo = 1
                                          ORDER BY TRY_CONVERT(int, CLAVE), CLAVE, NOMBRE",
                'disposiciones' => "SELECT IDe AS id, CLAVE AS clave,
                                            NOMBRE AS nombre, DESCRIPCION AS descripcion
                                     FROM SA_disposicion
                                     WHERE activo = 1
                                     ORDER BY TRY_CONVERT(int, CLAVE), CLAVE, NOMBRE",
                'factores' => "SELECT IDe AS id, CLAVE AS clave,
                                       NOMBRE AS nombre, DESCRIPCION AS descripcion
                                FROM SA_factor
                                WHERE activo = 1
                                ORDER BY IDe"
            ];
            $catalogos = [];

            foreach ($consultasCatalogos as $nombreCatalogo => $sqlCatalogo) {
                $consulta = $conn->prepare($sqlCatalogo);
                $consulta->execute();
                $catalogos[$nombreCatalogo] = $consulta->fetchAll(PDO::FETCH_ASSOC);
                $consulta->closeCursor();
            }

            responderJson([
                'status' => 'ok',
                'data' => $catalogos
            ]);

        case '10':
            /* ============================================================
               Registro de la primera revisión
               ============================================================ */
            exigirPermiso('anormalidades', 'primera_revision');

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');
            $idTipoAnormalidad = obtenerEnteroPositivoPost('idTipoAnormalidad');
            $idCriticidad = obtenerEnteroPositivoPost('idCriticidad');
            $idNivelRespuesta = obtenerEnteroPositivoPost('idNivelRespuesta');
            $otrosTipoAnormalidad = obtenerTextoPost('otrosTipoAnormalidad');
            $categoriasJson = obtenerTextoPost('idsCategoria5m');
            $categoriasRecibidas = $categoriasJson !== null
                ? json_decode($categoriasJson, true)
                : null;
            $idsCategoria5m = [];
            $erroresValidacion = [];

            if ($idAnormalidad === null) {
                $erroresValidacion[] = 'La anormalidad seleccionada no es válida.';
            }

            if ($idTipoAnormalidad === null) {
                $erroresValidacion[] = 'El tipo de anormalidad no es válido.';
            }

            if ($idCriticidad === null) {
                $erroresValidacion[] = 'La criticidad no es válida.';
            }

            if ($idNivelRespuesta === null) {
                $erroresValidacion[] = 'El nivel de respuesta no es válido.';
            }

            if (!is_array($categoriasRecibidas) || !$categoriasRecibidas) {
                $erroresValidacion[] = 'Selecciona al menos una clasificación 5M.';
            } else {
                foreach ($categoriasRecibidas as $idCategoria) {
                    $idCategoriaTexto = is_scalar($idCategoria) ? (string) $idCategoria : '';

                    if (!preg_match('/^\d+$/', $idCategoriaTexto) ||
                        (int) $idCategoriaTexto < 1 || (int) $idCategoriaTexto > 2147483647) {
                        $erroresValidacion[] = 'Una de las clasificaciones 5M no es válida.';
                        break;
                    }

                    $idsCategoria5m[] = (int) $idCategoriaTexto;
                }

                $idsCategoria5m = array_values(array_unique($idsCategoria5m));

                if (count($idsCategoria5m) > 20) {
                    $erroresValidacion[] = 'La cantidad de clasificaciones 5M no es válida.';
                }
            }

            if ($erroresValidacion) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La información de la primera revisión está incompleta o no es válida.',
                    'errores' => $erroresValidacion
                ], 422);
            }

            $conn->beginTransaction();

            /* Bloquea la anormalidad mientras se valida e inserta su revisión. */
            bloquearAnormalidadParaEscritura($conn, $idAnormalidad);

            $consulta = $conn->prepare(
                "SELECT id_anormalidad
                 FROM SA_anormalidad WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);

            if (!$consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No se encontró la anormalidad seleccionada.'
                ], 404);
            }
            $consulta->closeCursor();

            /* La PK permite una sola primera revisión por anormalidad. */
            $consulta = $conn->prepare(
                "SELECT id_anormalidad
                 FROM SA_primera_revision WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);

            if ($consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Esta anormalidad ya tiene registrada una primera revisión.'
                ], 409);
            }
            $consulta->closeCursor();

            $sqlValidarCatalogos = "SELECT
                tipo_valido = CASE WHEN EXISTS
                (
                    SELECT 1 FROM SA_tipo_anormalidad
                    WHERE IDe = :idTipoAnormalidad AND activo = 1
                ) THEN 1 ELSE 0 END,
                tipo_clave =
                (
                    SELECT LTRIM(RTRIM(CLAVE))
                    FROM SA_tipo_anormalidad
                    WHERE IDe = :idTipoAnormalidadClave AND activo = 1
                ),
                criticidad_valida = CASE WHEN EXISTS
                (
                    SELECT 1 FROM SA_criticidad
                    WHERE IDe = :idCriticidad AND activo = 1
                ) THEN 1 ELSE 0 END,
                nivel_valido = CASE WHEN EXISTS
                (
                    SELECT 1 FROM SA_nivel_respuesta
                    WHERE IDe = :idNivelRespuesta AND activo = 1
                ) THEN 1 ELSE 0 END";
            $consulta = $conn->prepare($sqlValidarCatalogos);
            $consulta->execute([
                ':idTipoAnormalidad' => $idTipoAnormalidad,
                ':idTipoAnormalidadClave' => $idTipoAnormalidad,
                ':idCriticidad' => $idCriticidad,
                ':idNivelRespuesta' => $idNivelRespuesta
            ]);
            $catalogosValidos = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$catalogosValidos || (int) $catalogosValidos['tipo_valido'] !== 1 ||
                (int) $catalogosValidos['criticidad_valida'] !== 1 ||
                (int) $catalogosValidos['nivel_valido'] !== 1) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Uno de los valores seleccionados ya no está disponible.'
                ], 422);
            }

            /*
             * "Otro" se identifica por CLAVE 20, nunca por IDe. Así el flujo
             * sigue funcionando aunque los IDENTITY cambien al migrar la base.
             */
            $esTipoOtro = trim((string) $catalogosValidos['tipo_clave']) === '20';

            if ($esTipoOtro && !textoEsValido($otrosTipoAnormalidad, 500)) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Describe el tipo de anormalidad seleccionado como Otro en un máximo de 500 caracteres.'
                ], 422);
            }

            if (!$esTipoOtro) {
                $otrosTipoAnormalidad = null;
            }

            $marcadores5m = [];
            $parametros5m = [];

            foreach ($idsCategoria5m as $indice => $idCategoria5m) {
                $marcador = ':categoria5m' . $indice;
                $marcadores5m[] = $marcador;
                $parametros5m[$marcador] = $idCategoria5m;
            }

            $sqlValidar5m = "SELECT COUNT(*)
                             FROM SA_5m
                             WHERE activo = 1
                               AND IDe IN (" . implode(', ', $marcadores5m) . ")";
            $consulta = $conn->prepare($sqlValidar5m);
            $consulta->execute($parametros5m);
            $cantidadCategoriasValidas = (int) $consulta->fetchColumn();
            $consulta->closeCursor();

            if ($cantidadCategoriasValidas !== count($idsCategoria5m)) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Una de las clasificaciones 5M ya no está disponible.'
                ], 422);
            }

            $fechaHoraRevision = date('Y-m-d H:i:s');

            /* La identidad se toma de la sesión y nunca del formulario. */
            $nominaRegistra = nominaEnSesion();
            $nombreRegistra = nombreEnSesion();

            $consulta = $conn->prepare(
                "UPDATE SA_anormalidad
                 SET id_tipo_anormalidad = :idTipoAnormalidad,
                     otros_tipo_anormalidad = :otrosTipoAnormalidad
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([
                ':idTipoAnormalidad' => $idTipoAnormalidad,
                ':otrosTipoAnormalidad' => $otrosTipoAnormalidad,
                ':idAnormalidad' => $idAnormalidad
            ]);
            $consulta->closeCursor();

            $sqlInsertarRevision = "INSERT INTO SA_primera_revision
                (id_anormalidad, id_criticidad, id_nivel_respuesta,
                 fechaHora_revision, nomina_registra, nombre_registra)
                VALUES
                (:idAnormalidad, :idCriticidad, :idNivelRespuesta,
                 :fechaHoraRevision, :nominaRegistra, :nombreRegistra)";
            $consulta = $conn->prepare($sqlInsertarRevision);
            $consulta->execute([
                ':idAnormalidad' => $idAnormalidad,
                ':idCriticidad' => $idCriticidad,
                ':idNivelRespuesta' => $idNivelRespuesta,
                ':fechaHoraRevision' => $fechaHoraRevision,
                ':nominaRegistra' => $nominaRegistra,
                ':nombreRegistra' => $nombreRegistra
            ]);
            $consulta->closeCursor();

            /*
             * Clasificación 5M (relación N:M): una fila por categoría en la
             * tabla puente. id_anormalidad es el mismo id de la primera
             * revisión, por eso la fila anterior debe existir primero (FK).
             */
            $consulta = $conn->prepare(
                "INSERT INTO SA_primera_revision_5m (id_anormalidad, id_categoria_5m)
                 VALUES (:idAnormalidad, :idCategoria5m)"
            );

            foreach ($idsCategoria5m as $idCategoria5m) {
                $consulta->execute([
                    ':idAnormalidad' => $idAnormalidad,
                    ':idCategoria5m' => $idCategoria5m
                ]);
                $consulta->closeCursor();
            }

            /*
             * La primera revisión inicia la fase de Análisis/Contramedidas.
             * La condición evita sobrescribir un estatus posterior si otra
             * parte del flujo ya avanzó la anormalidad.
             */
            $estatusAnalisis = obtenerEstatusActivoPorClave($conn, '2');

            if (!$estatusAnalisis) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La clave 2 del estatus de análisis no está configurada de forma única y activa.'
                ], 409);
            }

            $consulta = $conn->prepare(
                "UPDATE A
                 SET id_estatus_actual = :idEstatusAnalisis
                 FROM SA_anormalidad A
                 INNER JOIN SA_estatus_anormalidad E
                    ON E.IDe = A.id_estatus_actual
                 WHERE A.id_anormalidad = :idAnormalidad
                   AND LTRIM(RTRIM(E.CLAVE)) = '1'"
            );
            $consulta->execute([
                ':idEstatusAnalisis' => (int) $estatusAnalisis['IDe'],
                ':idAnormalidad' => $idAnormalidad
            ]);
            $consulta->closeCursor();

            /* Obtiene el estatus real conservado después de la actualización condicional. */
            $consulta = $conn->prepare(
                "SELECT
                    A.id_estatus_actual,
                    E.CLAVE AS clave_estatus,
                    E.NOMBRE AS estatus_actual
                 FROM SA_anormalidad A
                 INNER JOIN SA_estatus_anormalidad E
                    ON E.IDe = A.id_estatus_actual
                 WHERE A.id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);
            $estatusActual = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$estatusActual) {
                throw new RuntimeException('No fue posible consultar el estatus actualizado de la anormalidad.');
            }

            $conn->commit();

            responderJson([
                'status' => 'ok',
                'mensaje' => 'La primera revisión se registró correctamente.',
                'data' => [
                    'id_anormalidad' => $idAnormalidad,
                    'id_tipo_anormalidad' => $idTipoAnormalidad,
                    'otros_tipo_anormalidad' => $otrosTipoAnormalidad,
                    'id_criticidad' => $idCriticidad,
                    'id_nivel_respuesta' => $idNivelRespuesta,
                    'ids_categoria_5m' => $idsCategoria5m,
                    'fechaHora_revision' => $fechaHoraRevision,
                    'nomina_registra' => $nominaRegistra,
                    'nombre_registra' => $nombreRegistra,
                    'id_estatus_actual' => (int) $estatusActual['id_estatus_actual'],
                    'clave_estatus' => $estatusActual['clave_estatus'],
                    'estatus_actual' => $estatusActual['estatus_actual']
                ]
            ]);

        case '11':
            /* ============================================================
               Consulta de la primera revisión por anormalidad
               ============================================================ */
            exigirPermiso('anormalidades', 'ver');

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');

            if ($idAnormalidad === null) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El identificador de la anormalidad no es válido.'
                ], 400);
            }

            $sql = "SELECT
                        A.id_anormalidad,
                        A.id_tipo_anormalidad,
                        A.otros_tipo_anormalidad,
                        TA.CLAVE AS tipo_anormalidad_clave,
                        TA.NOMBRE AS tipo_anormalidad,
                        PR.id_criticidad,
                        C.NOMBRE AS criticidad,
                        PR.id_nivel_respuesta,
                        NR.NOMBRE AS nivel_respuesta,
                        CONVERT(varchar(19), PR.fechaHora_revision, 126) AS fechaHora_revision,
                        PR.nomina_registra,
                        PR.nombre_registra
                    FROM SA_anormalidad A
                    LEFT JOIN SA_tipo_anormalidad TA
                        ON TA.IDe = A.id_tipo_anormalidad
                    LEFT JOIN SA_primera_revision PR
                        ON PR.id_anormalidad = A.id_anormalidad
                    LEFT JOIN SA_criticidad C
                        ON C.IDe = PR.id_criticidad
                    LEFT JOIN SA_nivel_respuesta NR
                        ON NR.IDe = PR.id_nivel_respuesta
                    WHERE A.id_anormalidad = :idAnormalidad";
            $consulta = $conn->prepare($sql);
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);
            $revision = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$revision) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No se encontró la anormalidad seleccionada.'
                ], 404);
            }

            $revisionRegistrada = $revision['id_criticidad'] !== null;
            $idsCategoria5m = [];
            $categorias5m = [];

            if ($revisionRegistrada) {
                /* Clasificación 5M desde la tabla puente (relación N:M). */
                $consulta = $conn->prepare(
                    "SELECT M.IDe AS id, M.CLAVE AS clave, M.NOMBRE AS nombre,
                            M.DESCRIPCION AS descripcion, M.activo
                     FROM SA_primera_revision_5m AS R5
                     INNER JOIN SA_5m AS M
                        ON M.IDe = R5.id_categoria_5m
                     WHERE R5.id_anormalidad = :idAnormalidad
                     ORDER BY M.IDe"
                );
                $consulta->execute([':idAnormalidad' => $idAnormalidad]);
                $categorias5m = $consulta->fetchAll(PDO::FETCH_ASSOC);
                $consulta->closeCursor();

                foreach ($categorias5m as $categoria) {
                    $idsCategoria5m[] = (int) $categoria['id'];
                }
            }

            responderJson([
                'status' => 'ok',
                'data' => [
                    'registrada' => $revisionRegistrada,
                    'id_anormalidad' => (int) $revision['id_anormalidad'],
                    'id_tipo_anormalidad' => $revision['id_tipo_anormalidad'] !== null
                        ? (int) $revision['id_tipo_anormalidad']
                        : null,
                    'tipo_anormalidad' => $revision['tipo_anormalidad'],
                    'tipo_anormalidad_clave' => $revision['tipo_anormalidad_clave'],
                    'otros_tipo_anormalidad' => $revision['otros_tipo_anormalidad'],
                    'id_criticidad' => $revision['id_criticidad'] !== null
                        ? (int) $revision['id_criticidad']
                        : null,
                    'criticidad' => $revision['criticidad'],
                    'id_nivel_respuesta' => $revision['id_nivel_respuesta'] !== null
                        ? (int) $revision['id_nivel_respuesta']
                        : null,
                    'nivel_respuesta' => $revision['nivel_respuesta'],
                    'ids_categoria_5m' => $idsCategoria5m,
                    'categorias_5m' => $categorias5m,
                    'fechaHora_revision' => $revision['fechaHora_revision'],
                    'nomina_registra' => $revision['nomina_registra'],
                    'nombre_registra' => $revision['nombre_registra']
                ]
            ]);

        case '12':
            /* ============================================================
               Registro del análisis de revisión realizado por PE
               ============================================================ */
            exigirPermiso('anormalidades', 'analisis_pe');

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');
            $revisionRealizada = obtenerTextoPost('revisionRealizada');
            $resultado = obtenerTextoPost('resultado');
            $erroresValidacion = [];

            if ($idAnormalidad === null) {
                $erroresValidacion[] = 'La anormalidad seleccionada no es válida.';
            }

            if (!textoEsValido($revisionRealizada, 500)) {
                $erroresValidacion[] = 'La revisión realizada debe contener entre 1 y 500 caracteres.';
            }

            if ($resultado === null || !in_array($resultado, ['OK', 'NG'], true)) {
                $erroresValidacion[] = 'El resultado de la revisión debe ser OK o NG.';
            }

            if ($erroresValidacion) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La información del análisis PE está incompleta o no es válida.',
                    'errores' => $erroresValidacion
                ], 422);
            }

            $conn->beginTransaction();

            /*
             * El bloqueo del registro padre serializa las revisiones PE de una
             * misma anormalidad. La PK de la tabla mantiene una segunda defensa.
             */
            bloquearAnormalidadParaEscritura($conn, $idAnormalidad);

            $consulta = $conn->prepare(
                "SELECT id_anormalidad
                 FROM SA_anormalidad WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);

            if (!$consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No se encontró la anormalidad seleccionada.'
                ], 404);
            }
            $consulta->closeCursor();

            $consulta = $conn->prepare(
                "SELECT id_anormalidad
                 FROM SA_analisis_revision_PE WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);

            if ($consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Esta anormalidad ya tiene registrado un análisis de revisión PE.'
                ], 409);
            }
            $consulta->closeCursor();

            /* PE debe estar definido de forma inequívoca y activo en el catálogo. */
            $consulta = $conn->query(
                "SELECT IDe
                 FROM SA_factor WITH (UPDLOCK, HOLDLOCK)
                 WHERE activo = 1
                   AND
                   (
                       UPPER(LTRIM(RTRIM(CLAVE))) = 'PE'
                       OR UPPER(LTRIM(RTRIM(NOMBRE))) = 'PE'
                   )"
            );
            $factoresPe = $consulta->fetchAll(PDO::FETCH_COLUMN);
            $consulta->closeCursor();

            if (count($factoresPe) !== 1) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => count($factoresPe) === 0
                        ? 'No existe un factor PE activo en el catálogo.'
                        : 'Existe más de un factor PE activo; corrige el catálogo antes de registrar.'
                ], 409);
            }

            $idFactorPe = (int) $factoresPe[0];

            $consulta = $conn->prepare(
                "SELECT id_anormalidad
                 FROM SA_anormalidad_factor WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad
                   AND id_factor = :idFactor"
            );
            $consulta->execute([
                ':idAnormalidad' => $idAnormalidad,
                ':idFactor' => $idFactorPe
            ]);

            if ($consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La anormalidad ya tiene asignado el factor PE.'
                ], 409);
            }
            $consulta->closeCursor();

            /* La identidad del responsable se toma de la sesión. */
            $nominaResponsable = nominaEnSesion();
            $nombreResponsable = nombreEnSesion();

            $consulta = $conn->prepare(
                "INSERT INTO SA_analisis_revision_PE
                    (id_anormalidad, revision_realizada, resultado)
                 VALUES
                    (:idAnormalidad, :revisionRealizada, :resultado)"
            );
            $consulta->execute([
                ':idAnormalidad' => $idAnormalidad,
                ':revisionRealizada' => $revisionRealizada,
                ':resultado' => $resultado
            ]);
            $consulta->closeCursor();

            $consulta = $conn->prepare(
                "INSERT INTO SA_anormalidad_factor
                    (id_anormalidad, id_factor, nomina_responsable, nombre_responsable)
                 VALUES
                    (:idAnormalidad, :idFactor, :nominaResponsable, :nombreResponsable)"
            );
            $consulta->execute([
                ':idAnormalidad' => $idAnormalidad,
                ':idFactor' => $idFactorPe,
                ':nominaResponsable' => $nominaResponsable,
                ':nombreResponsable' => $nombreResponsable
            ]);
            $consulta->closeCursor();

            $conn->commit();

            responderJson([
                'status' => 'ok',
                'mensaje' => 'El análisis de revisión PE se registró correctamente.',
                'data' => [
                    'id_anormalidad' => $idAnormalidad,
                    'revision_realizada' => $revisionRealizada,
                    'resultado' => $resultado,
                    'id_factor' => $idFactorPe,
                    'nomina_responsable' => $nominaResponsable,
                    'nombre_responsable' => $nombreResponsable
                ]
            ]);

        case '13':
            /* ============================================================
               Registro del análisis de operación realizado por Manufactura
               ============================================================ */
            exigirPermiso('anormalidades', 'analisis_mf');

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');
            $analisisRealizado = obtenerTextoPost('analisisRealizado');
            $resultado = obtenerTextoPost('resultado');
            $erroresValidacion = [];

            if ($idAnormalidad === null) {
                $erroresValidacion[] = 'La anormalidad seleccionada no es válida.';
            }

            if (!textoEsValido($analisisRealizado, 500)) {
                $erroresValidacion[] = 'El análisis realizado debe contener entre 1 y 500 caracteres.';
            }

            if ($resultado === null || !in_array($resultado, ['OK', 'NG'], true)) {
                $erroresValidacion[] = 'El resultado del análisis debe ser OK o NG.';
            }

            if ($erroresValidacion) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La información del análisis de operación está incompleta o no es válida.',
                    'errores' => $erroresValidacion
                ], 422);
            }

            $conn->beginTransaction();

            /* El bloqueo padre serializa los registros MF de esta anormalidad. */
            bloquearAnormalidadParaEscritura($conn, $idAnormalidad);

            $consulta = $conn->prepare(
                "SELECT id_anormalidad
                 FROM SA_anormalidad WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);

            if (!$consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No se encontró la anormalidad seleccionada.'
                ], 404);
            }
            $consulta->closeCursor();

            $consulta = $conn->prepare(
                "SELECT id_anormalidad
                 FROM SA_analisis_revision_MF WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);

            if ($consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Esta anormalidad ya tiene registrado un análisis de operación.'
                ], 409);
            }
            $consulta->closeCursor();

            /* Admite la clave visual MF y la clave de catálogo MFT. */
            $consulta = $conn->query(
                "SELECT IDe
                 FROM SA_factor WITH (UPDLOCK, HOLDLOCK)
                 WHERE activo = 1
                   AND
                   (
                       UPPER(LTRIM(RTRIM(CLAVE))) IN ('MF', 'MFT')
                       OR UPPER(LTRIM(RTRIM(NOMBRE))) = 'MANUFACTURA'
                   )"
            );
            $factoresMf = $consulta->fetchAll(PDO::FETCH_COLUMN);
            $consulta->closeCursor();

            if (count($factoresMf) !== 1) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => count($factoresMf) === 0
                        ? 'No existe un factor activo para Manufactura en el catálogo.'
                        : 'Existe más de un factor activo para Manufactura; corrige el catálogo antes de registrar.'
                ], 409);
            }

            $idFactorMf = (int) $factoresMf[0];

            $consulta = $conn->prepare(
                "SELECT id_anormalidad
                 FROM SA_anormalidad_factor WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad
                   AND id_factor = :idFactor"
            );
            $consulta->execute([
                ':idAnormalidad' => $idAnormalidad,
                ':idFactor' => $idFactorMf
            ]);

            if ($consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La anormalidad ya tiene asignado el factor de Manufactura.'
                ], 409);
            }
            $consulta->closeCursor();

            /* La identidad del responsable se toma de la sesión. */
            $nominaResponsable = nominaEnSesion();
            $nombreResponsable = nombreEnSesion();

            $consulta = $conn->prepare(
                "INSERT INTO SA_analisis_revision_MF
                    (id_anormalidad, analisis_realizado, resultado)
                 VALUES
                    (:idAnormalidad, :analisisRealizado, :resultado)"
            );
            $consulta->execute([
                ':idAnormalidad' => $idAnormalidad,
                ':analisisRealizado' => $analisisRealizado,
                ':resultado' => $resultado
            ]);
            $consulta->closeCursor();

            $consulta = $conn->prepare(
                "INSERT INTO SA_anormalidad_factor
                    (id_anormalidad, id_factor, nomina_responsable, nombre_responsable)
                 VALUES
                    (:idAnormalidad, :idFactor, :nominaResponsable, :nombreResponsable)"
            );
            $consulta->execute([
                ':idAnormalidad' => $idAnormalidad,
                ':idFactor' => $idFactorMf,
                ':nominaResponsable' => $nominaResponsable,
                ':nombreResponsable' => $nombreResponsable
            ]);
            $consulta->closeCursor();

            $conn->commit();

            responderJson([
                'status' => 'ok',
                'mensaje' => 'El análisis de operación se registró correctamente.',
                'data' => [
                    'id_anormalidad' => $idAnormalidad,
                    'analisis_realizado' => $analisisRealizado,
                    'resultado' => $resultado,
                    'id_factor' => $idFactorMf,
                    'nomina_responsable' => $nominaResponsable,
                    'nombre_responsable' => $nombreResponsable
                ]
            ]);

        case '14':
            /* ============================================================
               Registro conjunto de las dos confirmaciones realizadas por QA
               ============================================================ */
            exigirPermiso('anormalidades', 'analisis_qa');

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');
            $confirmacionPiezas = obtenerTextoPost('confirmacionPiezas');
            $cantidadPiezas = obtenerEnteroNoNegativoPost('cantidadPiezas');
            $resultadoPiezas = obtenerTextoPost('resultadoPiezas');
            $confirmacionProceso = obtenerTextoPost('confirmacionProceso');
            $cantidadProceso = obtenerEnteroNoNegativoPost('cantidadProceso');
            $resultadoProceso = obtenerTextoPost('resultadoProceso');
            $erroresValidacion = [];

            if ($idAnormalidad === null) {
                $erroresValidacion[] = 'La anormalidad seleccionada no es válida.';
            }

            if (!textoEsValido($confirmacionPiezas, 500)) {
                $erroresValidacion[] = 'La confirmación de piezas debe contener entre 1 y 500 caracteres.';
            }

            if ($cantidadPiezas === null) {
                $erroresValidacion[] = 'La cantidad de piezas debe ser un entero mayor o igual que cero.';
            }

            if ($resultadoPiezas === null || !in_array($resultadoPiezas, ['OK', 'NG'], true)) {
                $erroresValidacion[] = 'El resultado de la confirmación de piezas debe ser OK o NG.';
            }

            if (!textoEsValido($confirmacionProceso, 500)) {
                $erroresValidacion[] = 'La confirmación de componentes en proceso debe contener entre 1 y 500 caracteres.';
            }

            if ($cantidadProceso === null) {
                $erroresValidacion[] = 'La cantidad de componentes en proceso debe ser un entero mayor o igual que cero.';
            }

            if ($resultadoProceso === null || !in_array($resultadoProceso, ['OK', 'NG'], true)) {
                $erroresValidacion[] = 'El resultado de la confirmación en proceso debe ser OK o NG.';
            }

            if ($erroresValidacion) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La información de las confirmaciones QA está incompleta o no es válida.',
                    'errores' => $erroresValidacion
                ], 422);
            }

            $conn->beginTransaction();

            /* Serializa las confirmaciones QA pertenecientes a la anormalidad. */
            bloquearAnormalidadParaEscritura($conn, $idAnormalidad);

            $consulta = $conn->prepare(
                "SELECT id_anormalidad
                 FROM SA_anormalidad WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);

            if (!$consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No se encontró la anormalidad seleccionada.'
                ], 404);
            }
            $consulta->closeCursor();

            /*
             * momento almacena el IDe del catálogo; las claves 1 y 2 definen
             * respectivamente la confirmación de piezas y la de proceso.
             */
            $consulta = $conn->query(
                "SELECT IDe, LTRIM(RTRIM(CLAVE)) AS CLAVE
                 FROM SA_momento_revision WITH (HOLDLOCK)
                 WHERE activo = 1
                   AND LTRIM(RTRIM(CLAVE)) IN ('1', '2')"
            );
            $momentosConsultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $consulta->closeCursor();
            $momentosPorClave = [];
            $catalogoMomentosValido = true;

            foreach ($momentosConsultados as $momentoConsultado) {
                $claveMomento = (string) $momentoConsultado['CLAVE'];

                if (isset($momentosPorClave[$claveMomento])) {
                    $catalogoMomentosValido = false;
                    break;
                }

                $momentosPorClave[$claveMomento] = (int) $momentoConsultado['IDe'];
            }

            if (!$catalogoMomentosValido || !isset($momentosPorClave['1'], $momentosPorClave['2'])) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Las claves activas 1 y 2 del catálogo de momentos no están configuradas de forma única.'
                ], 409);
            }

            $idMomentoPiezas = $momentosPorClave['1'];
            $idMomentoProceso = $momentosPorClave['2'];

            $consulta = $conn->prepare(
                "SELECT COUNT(*)
                 FROM SA_analisis_revision_QA
                 WHERE id_anormalidad = :idAnormalidad
                   AND momento IN (:idMomentoPiezas, :idMomentoProceso)"
            );
            $consulta->execute([
                ':idAnormalidad' => $idAnormalidad,
                ':idMomentoPiezas' => $idMomentoPiezas,
                ':idMomentoProceso' => $idMomentoProceso
            ]);
            $confirmacionesExistentes = (int) $consulta->fetchColumn();
            $consulta->closeCursor();

            if ($confirmacionesExistentes > 0) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Esta anormalidad ya tiene registrada una o ambas confirmaciones de Calidad.'
                ], 409);
            }

            /* El factor QA se obtiene exclusivamente por su clave activa. */
            $consulta = $conn->query(
                "SELECT IDe
                 FROM SA_factor WITH (HOLDLOCK)
                 WHERE activo = 1
                   AND UPPER(LTRIM(RTRIM(CLAVE))) = 'QA'"
            );
            $factoresQa = $consulta->fetchAll(PDO::FETCH_COLUMN);
            $consulta->closeCursor();

            if (count($factoresQa) !== 1) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => count($factoresQa) === 0
                        ? 'No existe un factor QA activo en el catálogo.'
                        : 'Existe más de un factor QA activo; corrige el catálogo antes de registrar.'
                ], 409);
            }

            $idFactorQa = (int) $factoresQa[0];

            $consulta = $conn->prepare(
                "SELECT id_anormalidad
                 FROM SA_anormalidad_factor WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad
                   AND id_factor = :idFactor"
            );
            $consulta->execute([
                ':idAnormalidad' => $idAnormalidad,
                ':idFactor' => $idFactorQa
            ]);

            if ($consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La anormalidad ya tiene asignado el factor QA.'
                ], 409);
            }
            $consulta->closeCursor();

            /* La identidad del responsable se toma de la sesión. */
            $nominaResponsable = nominaEnSesion();
            $nombreResponsable = nombreEnSesion();

            $consulta = $conn->prepare(
                "INSERT INTO SA_analisis_revision_QA
                    (id_anormalidad, resultado_confirmacion, cantidad, resultado, momento)
                 OUTPUT INSERTED.idRegistro, INSERTED.momento
                 VALUES
                    (:idAnormalidadPiezas, :confirmacionPiezas, :cantidadPiezas,
                     :resultadoPiezas, :idMomentoPiezas),
                    (:idAnormalidadProceso, :confirmacionProceso, :cantidadProceso,
                     :resultadoProceso, :idMomentoProceso)"
            );
            $consulta->execute([
                ':idAnormalidadPiezas' => $idAnormalidad,
                ':confirmacionPiezas' => $confirmacionPiezas,
                ':cantidadPiezas' => $cantidadPiezas,
                ':resultadoPiezas' => $resultadoPiezas,
                ':idMomentoPiezas' => $idMomentoPiezas,
                ':idAnormalidadProceso' => $idAnormalidad,
                ':confirmacionProceso' => $confirmacionProceso,
                ':cantidadProceso' => $cantidadProceso,
                ':resultadoProceso' => $resultadoProceso,
                ':idMomentoProceso' => $idMomentoProceso
            ]);
            $confirmacionesRegistradas = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (count($confirmacionesRegistradas) !== 2) {
                throw new RuntimeException('El INSERT de QA no devolvió las dos confirmaciones creadas.');
            }

            $consulta = $conn->prepare(
                "INSERT INTO SA_anormalidad_factor
                    (id_anormalidad, id_factor, nomina_responsable, nombre_responsable)
                 VALUES
                    (:idAnormalidad, :idFactor, :nominaResponsable, :nombreResponsable)"
            );
            $consulta->execute([
                ':idAnormalidad' => $idAnormalidad,
                ':idFactor' => $idFactorQa,
                ':nominaResponsable' => $nominaResponsable,
                ':nombreResponsable' => $nombreResponsable
            ]);
            $consulta->closeCursor();

            $conn->commit();

            responderJson([
                'status' => 'ok',
                'mensaje' => 'Las dos confirmaciones de Calidad se registraron correctamente.',
                'data' => [
                    'id_anormalidad' => $idAnormalidad,
                    'confirmaciones' => $confirmacionesRegistradas,
                    'id_factor' => $idFactorQa,
                    'nomina_responsable' => $nominaResponsable,
                    'nombre_responsable' => $nombreResponsable
                ]
            ]);

        case '15':
            /* ============================================================
               Registro de la causa y la contramedida de la anormalidad
               ============================================================ */
            exigirPermiso('anormalidades', 'causa_contramedida');

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');
            $causa = obtenerTextoPost('causa');
            $contramedida = obtenerTextoPost('contramedida');
            $erroresValidacion = [];

            if ($idAnormalidad === null) {
                $erroresValidacion[] = 'La anormalidad seleccionada no es válida.';
            }

            if (!textoEsValido($causa, 800)) {
                $erroresValidacion[] = 'La causa debe contener entre 1 y 800 caracteres.';
            }

            if (!textoEsValido($contramedida, 800)) {
                $erroresValidacion[] = 'La contramedida debe contener entre 1 y 800 caracteres.';
            }

            if ($erroresValidacion) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La información de causa y contramedida está incompleta o no es válida.',
                    'errores' => $erroresValidacion
                ], 422);
            }

            $conn->beginTransaction();

            /*
             * El bloqueo padre coordina esta operación con los registros PE,
             * MF y QA pertenecientes a la misma anormalidad.
             */
            bloquearAnormalidadParaEscritura($conn, $idAnormalidad);

            $consulta = $conn->prepare(
                "SELECT id_anormalidad
                 FROM SA_anormalidad WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);

            if (!$consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No se encontró la anormalidad seleccionada.'
                ], 404);
            }
            $consulta->closeCursor();

            /* La PK permite una sola causa y contramedida por anormalidad. */
            $consulta = $conn->prepare(
                "SELECT id_anormalidad
                 FROM SA_Analisis_causa_contramedida WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);

            if ($consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Esta anormalidad ya tiene registrada una causa y contramedida.'
                ], 409);
            }
            $consulta->closeCursor();

            /*
             * PE o MF requieren un registro. QA cuenta como antecedente solo
             * cuando existen sus confirmaciones de los momentos 1 y 2.
             */
            $consulta = $conn->prepare(
                "SELECT
                    tiene_pe = CASE WHEN EXISTS
                    (
                        SELECT 1
                        FROM SA_analisis_revision_PE
                        WHERE id_anormalidad = :idAnormalidadPe
                    ) THEN 1 ELSE 0 END,
                    tiene_mf = CASE WHEN EXISTS
                    (
                        SELECT 1
                        FROM SA_analisis_revision_MF
                        WHERE id_anormalidad = :idAnormalidadMf
                    ) THEN 1 ELSE 0 END,
                    tiene_qa_momento_1 = CASE WHEN EXISTS
                    (
                        SELECT 1
                        FROM SA_analisis_revision_QA QA
                        INNER JOIN SA_momento_revision MR ON MR.IDe = QA.momento
                        WHERE QA.id_anormalidad = :idAnormalidadQa1
                          AND LTRIM(RTRIM(MR.CLAVE)) = '1'
                    ) THEN 1 ELSE 0 END,
                    tiene_qa_momento_2 = CASE WHEN EXISTS
                    (
                        SELECT 1
                        FROM SA_analisis_revision_QA QA
                        INNER JOIN SA_momento_revision MR ON MR.IDe = QA.momento
                        WHERE QA.id_anormalidad = :idAnormalidadQa2
                          AND LTRIM(RTRIM(MR.CLAVE)) = '2'
                    ) THEN 1 ELSE 0 END"
            );
            $consulta->execute([
                ':idAnormalidadPe' => $idAnormalidad,
                ':idAnormalidadMf' => $idAnormalidad,
                ':idAnormalidadQa1' => $idAnormalidad,
                ':idAnormalidadQa2' => $idAnormalidad
            ]);
            $seguimientos = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            $tieneAnalisisPe = $seguimientos && (int) $seguimientos['tiene_pe'] === 1;
            $tieneAnalisisMf = $seguimientos && (int) $seguimientos['tiene_mf'] === 1;
            $tieneConfirmacionQa = $seguimientos &&
                (int) $seguimientos['tiene_qa_momento_1'] === 1 &&
                (int) $seguimientos['tiene_qa_momento_2'] === 1;

            if (!$tieneAnalisisPe && !$tieneAnalisisMf && !$tieneConfirmacionQa) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Registra primero al menos un análisis PE, MF o las dos confirmaciones QA.'
                ], 409);
            }

            /* La identidad de quien registra se toma de la sesión. */
            $nominaQuienRegistra = nominaEnSesion();
            $nombreQuienRegistra = nombreEnSesion();

            $consulta = $conn->prepare(
                "INSERT INTO SA_Analisis_causa_contramedida
                    (id_anormalidad, causa, contramedida, fecha_registro,
                     nomina_quienregistra, nombre_quienregistra)
                 OUTPUT
                    INSERTED.id_anormalidad,
                    CONVERT(varchar(19), INSERTED.fecha_registro, 126) AS fecha_registro
                 VALUES
                    (:idAnormalidad, :causa, :contramedida, SYSDATETIME(),
                     :nominaQuienRegistra, :nombreQuienRegistra)"
            );
            $consulta->execute([
                ':idAnormalidad' => $idAnormalidad,
                ':causa' => $causa,
                ':contramedida' => $contramedida,
                ':nominaQuienRegistra' => $nominaQuienRegistra,
                ':nombreQuienRegistra' => $nombreQuienRegistra
            ]);
            $registroCausaContramedida = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$registroCausaContramedida) {
                throw new RuntimeException('El INSERT no devolvió la causa y contramedida creadas.');
            }

            /*
             * El registro de causa y contramedida inicia la Verificación de
             * Calidad. Solo avanzan los estados 1 y 2; nunca se retrocede un
             * registro que ya se encuentre en una etapa posterior del flujo.
             */
            $estatusVerificacion = obtenerEstatusActivoPorClave($conn, '3');

            if (!$estatusVerificacion) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La clave 3 del estatus de verificación no está configurada de forma única y activa.'
                ], 409);
            }

            $consulta = $conn->prepare(
                "UPDATE A
                 SET id_estatus_actual = :idEstatusVerificacion
                 FROM SA_anormalidad A
                 INNER JOIN SA_estatus_anormalidad E
                    ON E.IDe = A.id_estatus_actual
                 WHERE A.id_anormalidad = :idAnormalidad
                   AND LTRIM(RTRIM(E.CLAVE)) IN ('1', '2')"
            );
            $consulta->execute([
                ':idEstatusVerificacion' => (int) $estatusVerificacion['IDe'],
                ':idAnormalidad' => $idAnormalidad
            ]);
            $consulta->closeCursor();

            /* Obtiene el estatus real conservado después de la actualización condicional. */
            $consulta = $conn->prepare(
                "SELECT
                    A.id_estatus_actual,
                    E.CLAVE AS clave_estatus,
                    E.NOMBRE AS estatus_actual
                 FROM SA_anormalidad A
                 INNER JOIN SA_estatus_anormalidad E
                    ON E.IDe = A.id_estatus_actual
                 WHERE A.id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);
            $estatusActual = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$estatusActual) {
                throw new RuntimeException('No fue posible consultar el estatus actualizado de la anormalidad.');
            }

            $conn->commit();

            responderJson([
                'status' => 'ok',
                'mensaje' => 'La causa y la contramedida se registraron correctamente.',
                'data' => [
                    'id_anormalidad' => (int) $registroCausaContramedida['id_anormalidad'],
                    'causa' => $causa,
                    'contramedida' => $contramedida,
                    'fecha_registro' => $registroCausaContramedida['fecha_registro'],
                    'nomina_quienregistra' => $nominaQuienRegistra,
                    'nombre_quienregistra' => $nombreQuienRegistra,
                    'id_estatus_actual' => (int) $estatusActual['id_estatus_actual'],
                    'clave_estatus' => $estatusActual['clave_estatus'],
                    'estatus_actual' => $estatusActual['estatus_actual']
                ]
            ]);

        case '16':
            /* ============================================================
               Consulta de los registros existentes en la pestaña Análisis
               ============================================================ */
            exigirPermiso('anormalidades', 'ver');

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');

            if ($idAnormalidad === null) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El identificador de la anormalidad no es válido.'
                ], 400);
            }

            $sql = "SELECT
                        A.id_anormalidad,
                        PE.id_anormalidad AS pe_id_anormalidad,
                        PE.revision_realizada AS pe_revision_realizada,
                        PE.resultado AS pe_resultado,
                        MF.id_anormalidad AS mf_id_anormalidad,
                        MF.analisis_realizado AS mf_analisis_realizado,
                        MF.resultado AS mf_resultado,
                        QAP.idRegistro AS qa_piezas_id_registro,
                        QAP.confirmacion AS qa_piezas_confirmacion,
                        QAP.cantidad AS qa_piezas_cantidad,
                        QAP.resultado AS qa_piezas_resultado,
                        QAPR.idRegistro AS qa_proceso_id_registro,
                        QAPR.confirmacion AS qa_proceso_confirmacion,
                        QAPR.cantidad AS qa_proceso_cantidad,
                        QAPR.resultado AS qa_proceso_resultado,
                        ACC.id_anormalidad AS causa_id_anormalidad,
                        ACC.causa,
                        ACC.contramedida,
                        CONVERT(varchar(19), ACC.fecha_registro, 126) AS causa_fecha_registro
                    FROM SA_anormalidad A
                    LEFT JOIN SA_analisis_revision_PE PE
                        ON PE.id_anormalidad = A.id_anormalidad
                    LEFT JOIN SA_analisis_revision_MF MF
                        ON MF.id_anormalidad = A.id_anormalidad
                    LEFT JOIN SA_Analisis_causa_contramedida ACC
                        ON ACC.id_anormalidad = A.id_anormalidad
                    OUTER APPLY
                    (
                        SELECT TOP (1)
                            QA.idRegistro,
                            QA.resultado_confirmacion AS confirmacion,
                            QA.cantidad,
                            QA.resultado
                        FROM SA_analisis_revision_QA QA
                        INNER JOIN SA_momento_revision MR ON MR.IDe = QA.momento
                        WHERE QA.id_anormalidad = A.id_anormalidad
                          AND LTRIM(RTRIM(MR.CLAVE)) = '1'
                        ORDER BY QA.idRegistro DESC
                    ) QAP
                    OUTER APPLY
                    (
                        SELECT TOP (1)
                            QA.idRegistro,
                            QA.resultado_confirmacion AS confirmacion,
                            QA.cantidad,
                            QA.resultado
                        FROM SA_analisis_revision_QA QA
                        INNER JOIN SA_momento_revision MR ON MR.IDe = QA.momento
                        WHERE QA.id_anormalidad = A.id_anormalidad
                          AND LTRIM(RTRIM(MR.CLAVE)) = '2'
                        ORDER BY QA.idRegistro DESC
                    ) QAPR
                    WHERE A.id_anormalidad = :idAnormalidad";
            $consulta = $conn->prepare($sql);
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);
            $registrosAnalisis = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$registrosAnalisis) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No se encontró la anormalidad seleccionada.'
                ], 404);
            }

            $analisisPe = $registrosAnalisis['pe_id_anormalidad'] !== null
                ? [
                    'revision_realizada' => $registrosAnalisis['pe_revision_realizada'],
                    'resultado' => $registrosAnalisis['pe_resultado']
                ]
                : null;
            $analisisMf = $registrosAnalisis['mf_id_anormalidad'] !== null
                ? [
                    'analisis_realizado' => $registrosAnalisis['mf_analisis_realizado'],
                    'resultado' => $registrosAnalisis['mf_resultado']
                ]
                : null;
            $confirmacionPiezas = $registrosAnalisis['qa_piezas_id_registro'] !== null
                ? [
                    'id_registro' => (int) $registrosAnalisis['qa_piezas_id_registro'],
                    'confirmacion' => $registrosAnalisis['qa_piezas_confirmacion'],
                    'cantidad' => (int) $registrosAnalisis['qa_piezas_cantidad'],
                    'resultado' => $registrosAnalisis['qa_piezas_resultado']
                ]
                : null;
            $confirmacionProceso = $registrosAnalisis['qa_proceso_id_registro'] !== null
                ? [
                    'id_registro' => (int) $registrosAnalisis['qa_proceso_id_registro'],
                    'confirmacion' => $registrosAnalisis['qa_proceso_confirmacion'],
                    'cantidad' => (int) $registrosAnalisis['qa_proceso_cantidad'],
                    'resultado' => $registrosAnalisis['qa_proceso_resultado']
                ]
                : null;
            $causaContramedida = $registrosAnalisis['causa_id_anormalidad'] !== null
                ? [
                    'causa' => $registrosAnalisis['causa'],
                    'contramedida' => $registrosAnalisis['contramedida'],
                    'fecha_registro' => $registrosAnalisis['causa_fecha_registro']
                ]
                : null;

            responderJson([
                'status' => 'ok',
                'data' => [
                    'id_anormalidad' => (int) $registrosAnalisis['id_anormalidad'],
                    'pe' => $analisisPe,
                    'mf' => $analisisMf,
                    'qa' => [
                        'piezas' => $confirmacionPiezas,
                        'proceso' => $confirmacionProceso
                    ],
                    'causa_contramedida' => $causaContramedida
                ]
            ]);

        case '17':
            /* ============================================================
               Registro inicial del cierre de Calidad: disposición aplicada
               ============================================================ */
            exigirPermiso('anormalidades', 'disposicion_producto');

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');
            $idDisposicion = obtenerEnteroPositivoPost('idDisposicion');
            $erroresValidacion = [];

            if ($idAnormalidad === null) {
                $erroresValidacion[] = 'La anormalidad seleccionada no es válida.';
            }

            if ($idDisposicion === null) {
                $erroresValidacion[] = 'La disposición seleccionada no es válida.';
            }

            if ($erroresValidacion) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La información de la disposición está incompleta o no es válida.',
                    'errores' => $erroresValidacion
                ], 422);
            }

            $conn->beginTransaction();

            /*
             * El bloqueo de la anormalidad serializa dos intentos simultáneos
             * para crear el cierre correspondiente al mismo registro.
             */
            bloquearAnormalidadParaEscritura($conn, $idAnormalidad);

            $consulta = $conn->prepare(
                "SELECT id_anormalidad
                 FROM SA_anormalidad WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);

            if (!$consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No se encontró la anormalidad seleccionada.'
                ], 404);
            }
            $consulta->closeCursor();

            /* Solo se aceptan valores que todavía están activos en el catálogo. */
            $consulta = $conn->prepare(
                "SELECT IDe
                 FROM SA_disposicion WITH (HOLDLOCK)
                 WHERE IDe = :idDisposicion
                   AND activo = 1"
            );
            $consulta->execute([':idDisposicion' => $idDisposicion]);

            if (!$consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La disposición seleccionada no existe o ya no está activa.'
                ], 409);
            }
            $consulta->closeCursor();

            /* La restricción UNIQUE de id_anormalidad refuerza esta validación. */
            $consulta = $conn->prepare(
                "SELECT id_cierre_calidad
                 FROM SA_cierre_calidad WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);

            if ($consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Esta anormalidad ya tiene registrada una disposición de Calidad.'
                ], 409);
            }
            $consulta->closeCursor();

            $consulta = $conn->prepare(
                "INSERT INTO SA_cierre_calidad
                    (id_anormalidad, id_disposicion)
                 OUTPUT
                    INSERTED.id_cierre_calidad,
                    INSERTED.id_anormalidad,
                    INSERTED.id_disposicion
                 VALUES
                    (:idAnormalidad, :idDisposicion)"
            );
            $consulta->execute([
                ':idAnormalidad' => $idAnormalidad,
                ':idDisposicion' => $idDisposicion
            ]);
            $cierreCalidad = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$cierreCalidad) {
                throw new RuntimeException('El INSERT no devolvió el cierre de Calidad creado.');
            }

            $conn->commit();

            responderJson([
                'status' => 'ok',
                'mensaje' => 'La disposición se registró correctamente.',
                'data' => [
                    'id_cierre_calidad' => (int) $cierreCalidad['id_cierre_calidad'],
                    'id_anormalidad' => (int) $cierreCalidad['id_anormalidad'],
                    'id_disposicion' => (int) $cierreCalidad['id_disposicion']
                ]
            ]);

        case '18':
            /* ============================================================
               Registro de muestras revisadas antes de la anormalidad
               ============================================================ */
            exigirPermiso('anormalidades', 'liberacion_muestras');

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');
            $muestras = [];
            $erroresValidacion = [];

            if ($idAnormalidad === null) {
                $erroresValidacion[] = 'La anormalidad seleccionada no es válida.';
            }

            for ($numeroSerial = 1; $numeroSerial <= 3; $numeroSerial++) {
                $codigoSerial = obtenerTextoPost('serial' . $numeroSerial);
                $resultado = obtenerTextoPost('resultado' . $numeroSerial);

                if (!textoEsValido($codigoSerial, 80)) {
                    $erroresValidacion[] = 'El serial ' . $numeroSerial . ' debe contener entre 1 y 80 caracteres.';
                }

                if ($resultado === null || !in_array($resultado, ['OK', 'NG'], true)) {
                    $erroresValidacion[] = 'El resultado del serial ' . $numeroSerial . ' debe ser OK o NG.';
                }

                $muestras[] = [
                    'no_serial' => $numeroSerial,
                    'codigo_serial' => $codigoSerial,
                    'resultado' => $resultado
                ];
            }

            if ($erroresValidacion) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La revisión de material está incompleta o contiene datos no válidos.',
                    'errores' => $erroresValidacion
                ], 422);
            }

            $conn->beginTransaction();

            /* Serializa los registros de liberación pertenecientes a la anormalidad. */
            bloquearAnormalidadParaEscritura($conn, $idAnormalidad);

            $consulta = $conn->prepare(
                "SELECT id_anormalidad
                 FROM SA_anormalidad WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);

            if (!$consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No se encontró la anormalidad seleccionada.'
                ], 404);
            }
            $consulta->closeCursor();

            /* Las muestras dependen del cierre creado al registrar la disposición. */
            $consulta = $conn->prepare(
                "SELECT id_cierre_calidad
                 FROM SA_cierre_calidad WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);
            $idCierreCalidad = $consulta->fetchColumn();
            $consulta->closeCursor();

            if ($idCierreCalidad === false) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Registra primero la disposición del producto antes de guardar la revisión de material.'
                ], 409);
            }

            $idCierreCalidad = (int) $idCierreCalidad;

            /* La clave 1 identifica el momento de revisión previo a la anormalidad. */
            $consulta = $conn->query(
                "SELECT IDe
                 FROM SA_momento_revision WITH (HOLDLOCK)
                 WHERE activo = 1
                   AND LTRIM(RTRIM(CLAVE)) = '1'"
            );
            $momentosPrimeraRevision = $consulta->fetchAll(PDO::FETCH_COLUMN);
            $consulta->closeCursor();

            if (count($momentosPrimeraRevision) !== 1) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => count($momentosPrimeraRevision) === 0
                        ? 'No existe un momento activo con clave 1 para la primera revisión.'
                        : 'Existe más de un momento activo con clave 1; corrige el catálogo antes de registrar.'
                ], 409);
            }

            $idMomento = (int) $momentosPrimeraRevision[0];

            /* Cualquier fila existente indica un registro previo o incompleto. */
            $consulta = $conn->prepare(
                "SELECT COUNT(*)
                 FROM SA_muestras_liberacion_calidad WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_cierre_calidad = :idCierreCalidad
                   AND id_momento = :idMomento"
            );
            $consulta->execute([
                ':idCierreCalidad' => $idCierreCalidad,
                ':idMomento' => $idMomento
            ]);
            $muestrasExistentes = (int) $consulta->fetchColumn();
            $consulta->closeCursor();

            if ($muestrasExistentes > 0) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La revisión de material antes de la anormalidad ya fue registrada.'
                ], 409);
            }

            $consulta = $conn->prepare(
                "INSERT INTO SA_muestras_liberacion_calidad
                    (noSerial, id_momento, id_cierre_calidad, codigo_serial, Resultado)
                 OUTPUT INSERTED.noSerial
                 VALUES
                    (1, :idMomento1, :idCierreCalidad1, :codigoSerial1, :resultado1),
                    (2, :idMomento2, :idCierreCalidad2, :codigoSerial2, :resultado2),
                    (3, :idMomento3, :idCierreCalidad3, :codigoSerial3, :resultado3)"
            );
            $consulta->execute([
                ':idMomento1' => $idMomento,
                ':idCierreCalidad1' => $idCierreCalidad,
                ':codigoSerial1' => $muestras[0]['codigo_serial'],
                ':resultado1' => $muestras[0]['resultado'],
                ':idMomento2' => $idMomento,
                ':idCierreCalidad2' => $idCierreCalidad,
                ':codigoSerial2' => $muestras[1]['codigo_serial'],
                ':resultado2' => $muestras[1]['resultado'],
                ':idMomento3' => $idMomento,
                ':idCierreCalidad3' => $idCierreCalidad,
                ':codigoSerial3' => $muestras[2]['codigo_serial'],
                ':resultado3' => $muestras[2]['resultado']
            ]);
            $muestrasRegistradas = $consulta->fetchAll(PDO::FETCH_COLUMN);
            $consulta->closeCursor();

            if (count($muestrasRegistradas) !== 3) {
                throw new RuntimeException('El INSERT no devolvió las tres muestras de Calidad creadas.');
            }

            $conn->commit();

            responderJson([
                'status' => 'ok',
                'mensaje' => 'La revisión de material se registró correctamente.',
                'data' => [
                    'id_anormalidad' => $idAnormalidad,
                    'id_cierre_calidad' => $idCierreCalidad,
                    'id_momento' => $idMomento,
                    'muestras' => $muestras
                ]
            ]);

        case '19':
            /* ============================================================
               Registro de confirmación y muestras del reinicio de operación
               ============================================================ */
            exigirPermiso('anormalidades', 'reinicio_operacion');

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');
            $confirmacionReinicio = obtenerTextoPost('confirmacion');
            $fechaConfirmacionTexto = obtenerTextoPost('fechaConfirmacion');
            $muestras = [];
            $erroresValidacion = [];

            if ($idAnormalidad === null) {
                $erroresValidacion[] = 'La anormalidad seleccionada no es válida.';
            }

            if (!textoEsValido($confirmacionReinicio, 700)) {
                $erroresValidacion[] = 'La confirmación del reinicio debe contener entre 1 y 700 caracteres.';
            }

            $fechaConfirmacionValida = $fechaConfirmacionTexto !== null &&
                preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaConfirmacionTexto) === 1;

            if ($fechaConfirmacionValida) {
                $fechaConfirmacion = DateTime::createFromFormat('!Y-m-d', $fechaConfirmacionTexto);
                $fechaConfirmacionValida = $fechaConfirmacion !== false &&
                    $fechaConfirmacion->format('Y-m-d') === $fechaConfirmacionTexto;
            }

            if (!$fechaConfirmacionValida) {
                $erroresValidacion[] = 'La fecha de confirmación no es válida.';
            }

            for ($numeroSerial = 1; $numeroSerial <= 3; $numeroSerial++) {
                $codigoSerial = obtenerTextoPost('serial' . $numeroSerial);
                $resultado = obtenerTextoPost('resultado' . $numeroSerial);

                if (!textoEsValido($codigoSerial, 80)) {
                    $erroresValidacion[] = 'El serial ' . $numeroSerial . ' debe contener entre 1 y 80 caracteres.';
                }

                if ($resultado === null || !in_array($resultado, ['OK', 'NG'], true)) {
                    $erroresValidacion[] = 'El resultado del serial ' . $numeroSerial . ' debe ser OK o NG.';
                }

                $muestras[] = [
                    'no_serial' => $numeroSerial,
                    'codigo_serial' => $codigoSerial,
                    'resultado' => $resultado
                ];
            }

            if ($erroresValidacion) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La información del reinicio está incompleta o contiene datos no válidos.',
                    'errores' => $erroresValidacion
                ], 422);
            }

            $conn->beginTransaction();

            /* El bloqueo padre serializa dos registros simultáneos del reinicio. */
            bloquearAnormalidadParaEscritura($conn, $idAnormalidad);

            $consulta = $conn->prepare(
                "SELECT id_anormalidad
                 FROM SA_anormalidad WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);

            if (!$consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No se encontró la anormalidad seleccionada.'
                ], 404);
            }
            $consulta->closeCursor();

            $consulta = $conn->prepare(
                "SELECT
                    id_cierre_calidad,
                    confirmacion_reinicio,
                    fecha_confirmacion_reinicio,
                    nomina_aprobReinicio,
                    nombre_aprobReinicio
                 FROM SA_cierre_calidad WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);
            $cierreCalidad = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$cierreCalidad) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Registra primero la disposición del producto antes de guardar el reinicio.'
                ], 409);
            }

            $reinicioExistente = $cierreCalidad['confirmacion_reinicio'] !== null ||
                $cierreCalidad['fecha_confirmacion_reinicio'] !== null ||
                $cierreCalidad['nomina_aprobReinicio'] !== null ||
                $cierreCalidad['nombre_aprobReinicio'] !== null;

            if ($reinicioExistente) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El reinicio de operación ya fue registrado para esta anormalidad.'
                ], 409);
            }

            $idCierreCalidad = (int) $cierreCalidad['id_cierre_calidad'];

            /* La clave 2 identifica las muestras revisadas después del reinicio. */
            $consulta = $conn->query(
                "SELECT IDe
                 FROM SA_momento_revision WITH (HOLDLOCK)
                 WHERE activo = 1
                   AND LTRIM(RTRIM(CLAVE)) = '2'"
            );
            $momentosSegundaRevision = $consulta->fetchAll(PDO::FETCH_COLUMN);
            $consulta->closeCursor();

            if (count($momentosSegundaRevision) !== 1) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => count($momentosSegundaRevision) === 0
                        ? 'No existe un momento activo con clave 2 para la revisión del reinicio.'
                        : 'Existe más de un momento activo con clave 2; corrige el catálogo antes de registrar.'
                ], 409);
            }

            $idMomento = (int) $momentosSegundaRevision[0];

            $consulta = $conn->prepare(
                "SELECT COUNT(*)
                 FROM SA_muestras_liberacion_calidad WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_cierre_calidad = :idCierreCalidad
                   AND id_momento = :idMomento"
            );
            $consulta->execute([
                ':idCierreCalidad' => $idCierreCalidad,
                ':idMomento' => $idMomento
            ]);
            $muestrasExistentes = (int) $consulta->fetchColumn();
            $consulta->closeCursor();

            if ($muestrasExistentes > 0) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Las muestras del reinicio ya fueron registradas para esta anormalidad.'
                ], 409);
            }

            /* La aprobación se registra con la identidad de la sesión. */
            $nominaAprobacion = nominaEnSesion();
            $nombreAprobacion = nombreEnSesion();

            $consulta = $conn->prepare(
                "UPDATE SA_cierre_calidad
                 SET confirmacion_reinicio = :confirmacionReinicio,
                     fecha_confirmacion_reinicio = CONVERT(datetime2(0), :fechaConfirmacion, 23),
                     nomina_aprobReinicio = :nominaAprobacion,
                     nombre_aprobReinicio = :nombreAprobacion
                 OUTPUT INSERTED.id_cierre_calidad
                 WHERE id_cierre_calidad = :idCierreCalidad
                   AND confirmacion_reinicio IS NULL
                   AND fecha_confirmacion_reinicio IS NULL
                   AND nomina_aprobReinicio IS NULL
                   AND nombre_aprobReinicio IS NULL"
            );
            $consulta->execute([
                ':confirmacionReinicio' => $confirmacionReinicio,
                ':fechaConfirmacion' => $fechaConfirmacionTexto,
                ':nominaAprobacion' => $nominaAprobacion,
                ':nombreAprobacion' => $nombreAprobacion,
                ':idCierreCalidad' => $idCierreCalidad
            ]);
            $cierreActualizado = $consulta->fetchColumn();
            $consulta->closeCursor();

            if ($cierreActualizado === false) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El reinicio fue actualizado por otro usuario antes de completar esta operación.'
                ], 409);
            }

            $consulta = $conn->prepare(
                "INSERT INTO SA_muestras_liberacion_calidad
                    (noSerial, id_momento, id_cierre_calidad, codigo_serial, Resultado)
                 OUTPUT INSERTED.noSerial
                 VALUES
                    (1, :idMomento1, :idCierreCalidad1, :codigoSerial1, :resultado1),
                    (2, :idMomento2, :idCierreCalidad2, :codigoSerial2, :resultado2),
                    (3, :idMomento3, :idCierreCalidad3, :codigoSerial3, :resultado3)"
            );
            $consulta->execute([
                ':idMomento1' => $idMomento,
                ':idCierreCalidad1' => $idCierreCalidad,
                ':codigoSerial1' => $muestras[0]['codigo_serial'],
                ':resultado1' => $muestras[0]['resultado'],
                ':idMomento2' => $idMomento,
                ':idCierreCalidad2' => $idCierreCalidad,
                ':codigoSerial2' => $muestras[1]['codigo_serial'],
                ':resultado2' => $muestras[1]['resultado'],
                ':idMomento3' => $idMomento,
                ':idCierreCalidad3' => $idCierreCalidad,
                ':codigoSerial3' => $muestras[2]['codigo_serial'],
                ':resultado3' => $muestras[2]['resultado']
            ]);
            $muestrasRegistradas = $consulta->fetchAll(PDO::FETCH_COLUMN);
            $consulta->closeCursor();

            if (count($muestrasRegistradas) !== 3) {
                throw new RuntimeException('El INSERT no devolvió las tres muestras del reinicio creadas.');
            }

            /*
             * El reinicio de operación habilita la etapa de Cierre calidad.
             * Solo avanzan los estados anteriores a 4; nunca se retrocede una
             * anormalidad que ya se encuentre en una fase posterior del flujo.
             */
            $estatusCierreCalidad = obtenerEstatusActivoPorClave($conn, '4');

            if (!$estatusCierreCalidad) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La clave 4 del estatus de cierre de calidad no está configurada de forma única y activa.'
                ], 409);
            }

            $consulta = $conn->prepare(
                "UPDATE A
                 SET id_estatus_actual = :idEstatusCierreCalidad
                 FROM SA_anormalidad A
                 INNER JOIN SA_estatus_anormalidad E
                    ON E.IDe = A.id_estatus_actual
                 WHERE A.id_anormalidad = :idAnormalidad
                   AND LTRIM(RTRIM(E.CLAVE)) IN ('1', '2', '3')"
            );
            $consulta->execute([
                ':idEstatusCierreCalidad' => (int) $estatusCierreCalidad['IDe'],
                ':idAnormalidad' => $idAnormalidad
            ]);
            $consulta->closeCursor();

            /* Devuelve el estatus que realmente permaneció guardado. */
            $consulta = $conn->prepare(
                "SELECT
                    A.id_estatus_actual,
                    E.CLAVE AS clave_estatus,
                    E.NOMBRE AS estatus_actual
                 FROM SA_anormalidad A
                 INNER JOIN SA_estatus_anormalidad E
                    ON E.IDe = A.id_estatus_actual
                 WHERE A.id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);
            $estatusActual = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$estatusActual) {
                throw new RuntimeException('No fue posible consultar el estatus actualizado de la anormalidad.');
            }

            $conn->commit();

            responderJson([
                'status' => 'ok',
                'mensaje' => 'El reinicio de operación se registró correctamente.',
                'data' => [
                    'id_anormalidad' => $idAnormalidad,
                    'id_cierre_calidad' => $idCierreCalidad,
                    'id_momento' => $idMomento,
                    'confirmacion_reinicio' => $confirmacionReinicio,
                    'fecha_confirmacion_reinicio' => $fechaConfirmacionTexto,
                    'nomina_aprobReinicio' => $nominaAprobacion,
                    'nombre_aprobReinicio' => $nombreAprobacion,
                    'id_estatus_actual' => (int) $estatusActual['id_estatus_actual'],
                    'clave_estatus' => $estatusActual['clave_estatus'],
                    'estatus_actual' => $estatusActual['estatus_actual'],
                    'muestras' => $muestras
                ]
            ]);

        case '20':
            /* ============================================================
               Consulta de la información registrada en Verificación Calidad
               ============================================================ */
            exigirPermiso('anormalidades', 'ver');

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');

            if ($idAnormalidad === null) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El identificador de la anormalidad no es válido.'
                ], 400);
            }

            /*
             * Una sola consulta mantiene coherentes el cierre y sus muestras
             * durante esta lectura y también devuelve anormalidades sin cierre.
             */
            $consulta = $conn->prepare(
                "SELECT
                    A.id_anormalidad,
                    CC.id_cierre_calidad,
                    CC.id_disposicion,
                    CC.confirmacion_reinicio,
                    CONVERT(varchar(10), CC.fecha_confirmacion_reinicio, 23)
                        AS fecha_confirmacion_reinicio,
                    CC.nomina_aprobReinicio,
                    CC.nombre_aprobReinicio,
                    M.noSerial AS muestra_no_serial,
                    M.id_momento AS muestra_id_momento,
                    M.codigo_serial AS muestra_codigo_serial,
                    M.Resultado AS muestra_resultado,
                    LTRIM(RTRIM(MR.CLAVE)) AS muestra_momento_clave
                 FROM SA_anormalidad A
                 LEFT JOIN SA_cierre_calidad CC
                    ON CC.id_anormalidad = A.id_anormalidad
                 LEFT JOIN SA_muestras_liberacion_calidad M
                    ON M.id_cierre_calidad = CC.id_cierre_calidad
                 LEFT JOIN SA_momento_revision MR
                    ON MR.IDe = M.id_momento
                 WHERE A.id_anormalidad = :idAnormalidad
                 ORDER BY
                    CASE LTRIM(RTRIM(MR.CLAVE))
                        WHEN '1' THEN 1
                        WHEN '2' THEN 2
                        ELSE 3
                    END,
                    M.noSerial"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);
            $filasCierreCalidad = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$filasCierreCalidad) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No se encontró la anormalidad seleccionada.'
                ], 404);
            }

            $primeraFila = $filasCierreCalidad[0];
            $cierre = $primeraFila['id_cierre_calidad'] !== null
                ? [
                    'id_cierre_calidad' => (int) $primeraFila['id_cierre_calidad'],
                    'id_disposicion' => (int) $primeraFila['id_disposicion'],
                    'confirmacion_reinicio' => $primeraFila['confirmacion_reinicio'],
                    'fecha_confirmacion_reinicio' => $primeraFila['fecha_confirmacion_reinicio'],
                    'nomina_aprobReinicio' => $primeraFila['nomina_aprobReinicio'],
                    'nombre_aprobReinicio' => $primeraFila['nombre_aprobReinicio']
                ]
                : null;
            $muestrasAntes = [];
            $muestrasReinicio = [];

            foreach ($filasCierreCalidad as $filaCierreCalidad) {
                if ($filaCierreCalidad['muestra_no_serial'] === null) {
                    continue;
                }

                $muestra = [
                    'no_serial' => (int) $filaCierreCalidad['muestra_no_serial'],
                    'id_momento' => (int) $filaCierreCalidad['muestra_id_momento'],
                    'codigo_serial' => $filaCierreCalidad['muestra_codigo_serial'],
                    'resultado' => $filaCierreCalidad['muestra_resultado']
                ];

                if ($filaCierreCalidad['muestra_momento_clave'] === '1') {
                    $muestrasAntes[] = $muestra;
                } elseif ($filaCierreCalidad['muestra_momento_clave'] === '2') {
                    $muestrasReinicio[] = $muestra;
                }
            }

            responderJson([
                'status' => 'ok',
                'data' => [
                    'id_anormalidad' => (int) $primeraFila['id_anormalidad'],
                    'cierre' => $cierre,
                    'muestras' => [
                        'antes' => $muestrasAntes,
                        'reinicio' => $muestrasReinicio
                    ]
                ]
            ]);

        case '21':
            /* ============================================================
               Consulta informativa de reincidencia para la pestaña Cierre
               ============================================================ */
            exigirPermiso('anormalidades', 'ver');

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');

            if ($idAnormalidad === null) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El identificador de la anormalidad no es válido.'
                ], 400);
            }

            $consulta = $conn->prepare(
                "SELECT
                    A.id_anormalidad,
                    A.no_seguimiento,
                    CASE WHEN EXISTS
                    (
                        SELECT 1
                        FROM SA_registro_general_reincidencia R
                        WHERE R.id_anormalidad = A.id_anormalidad
                    ) THEN 1 ELSE 0 END AS es_reincidente,
                    CQ.id_anormalidad AS cierre_qa_id,
                    CONVERT(varchar(19), CQ.fecha_cierre, 126) AS cierre_qa_fecha,
                    CQ.nomina_cierra AS cierre_qa_nomina,
                    CQ.nombre_cierra AS cierre_qa_nombre,
                    CQ.firma AS cierre_qa_firma,
                    CQ.id_factor_gerente AS cierre_qa_id_factor,
                    F.NOMBRE AS cierre_qa_factor,
                    CG.id_anormalidad AS gerente_id,
                    CONVERT(varchar(19), CG.fecha_cierre, 126) AS gerente_fecha,
                    CG.nomina_cierra AS gerente_nomina,
                    CG.nombre_cierra AS gerente_nombre,
                    CG.firma AS gerente_firma
                 FROM SA_anormalidad A
                 LEFT JOIN SA_cierre_reporte_calidad CQ
                    ON CQ.id_anormalidad = A.id_anormalidad
                 LEFT JOIN SA_factor F
                    ON F.IDe = CQ.id_factor_gerente
                 LEFT JOIN SA_cierre_autorizacion_gerente CG
                    ON CG.id_anormalidad = A.id_anormalidad
                 WHERE A.id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);
            $datosReincidencia = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$datosReincidencia) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No se encontró la anormalidad seleccionada.'
                ], 404);
            }

            $firmaQa = is_string($datosReincidencia['cierre_qa_firma'])
                ? trim($datosReincidencia['cierre_qa_firma'])
                : '';
            $firmaGerente = is_string($datosReincidencia['gerente_firma'])
                ? trim($datosReincidencia['gerente_firma'])
                : '';
            $firmaQaSegura = $firmaQa !== '' && basename($firmaQa) === $firmaQa &&
                preg_match('/\.jpe?g$/i', $firmaQa) === 1
                ? $firmaQa
                : null;
            $firmaGerenteSegura = $firmaGerente !== '' && basename($firmaGerente) === $firmaGerente &&
                preg_match('/\.jpe?g$/i', $firmaGerente) === 1
                ? $firmaGerente
                : null;
            $cierreQa = $datosReincidencia['cierre_qa_id'] !== null
                ? [
                    'fecha_cierre' => $datosReincidencia['cierre_qa_fecha'],
                    'nomina_cierra' => $datosReincidencia['cierre_qa_nomina'],
                    'nombre_cierra' => $datosReincidencia['cierre_qa_nombre'],
                    'firma' => $firmaQaSegura,
                    'id_factor' => (int) $datosReincidencia['cierre_qa_id_factor'],
                    'factor' => $datosReincidencia['cierre_qa_factor']
                ]
                : null;
            $autorizacionGerente = $datosReincidencia['gerente_id'] !== null
                ? [
                    'fecha_cierre' => $datosReincidencia['gerente_fecha'],
                    'nomina_cierra' => $datosReincidencia['gerente_nomina'],
                    'nombre_cierra' => $datosReincidencia['gerente_nombre'],
                    'firma' => $firmaGerenteSegura
                ]
                : null;

            responderJson([
                'status' => 'ok',
                'data' => [
                    'id_anormalidad' => (int) $datosReincidencia['id_anormalidad'],
                    'no_seguimiento' => $datosReincidencia['no_seguimiento'],
                    'es_reincidente' => (int) $datosReincidencia['es_reincidente'],
                    'cierre_qa_firmado' => $cierreQa !== null && $firmaQaSegura !== null ? 1 : 0,
                    'autorizacion_gerente_registrada' => $autorizacionGerente !== null ? 1 : 0,
                    'cierre_qa' => $cierreQa,
                    'autorizacion_gerente' => $autorizacionGerente
                ]
            ]);

        case '22':
            /* ============================================================
               Registro del cierre documentado y firmado por Staff QA
               ============================================================ */
            exigirPermiso('anormalidades', 'cierre_reporte_calidad');

            $usuarioEsPersonalQa = false;

            foreach (ROLES_CIERRE_QA as $rolCierreQa) {
                if (tieneRol($rolCierreQa)) {
                    $usuarioEsPersonalQa = true;
                    break;
                }
            }

            if (!$usuarioEsPersonalQa) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Solo el personal de Calidad puede registrar el cierre de Staff QA.'
                ], 403);
            }

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');
            $idFactor = obtenerEnteroPositivoPost('idFactor');
            $firmaDataUrl = obtenerTextoPost('firma');
            $erroresValidacion = [];
            $firmaBinaria = null;

            if ($idAnormalidad === null) {
                $erroresValidacion[] = 'La anormalidad seleccionada no es válida.';
            }

            if ($idFactor === null) {
                $erroresValidacion[] = 'El factor responsable no es válido.';
            }

            if ($firmaDataUrl === null || strlen($firmaDataUrl) > 2800000 ||
                preg_match('#^data:image/jpeg;base64,([A-Za-z0-9+/=\r\n]+)$#D', $firmaDataUrl, $coincidenciaFirma) !== 1) {
                $erroresValidacion[] = 'La firma debe ser una imagen JPEG válida y no exceder 2 MB.';
            } else {
                $firmaBinaria = base64_decode($coincidenciaFirma[1], true);

                if ($firmaBinaria === false || strlen($firmaBinaria) < 100 ||
                    strlen($firmaBinaria) > 2097152) {
                    $erroresValidacion[] = 'El contenido de la firma está vacío, dañado o excede 2 MB.';
                } else {
                    $detectorMime = new finfo(FILEINFO_MIME_TYPE);
                    $mimeFirma = $detectorMime->buffer($firmaBinaria);
                    $dimensionesFirma = @getimagesizefromstring($firmaBinaria);

                    if ($mimeFirma !== 'image/jpeg' || !$dimensionesFirma ||
                        ($dimensionesFirma['mime'] ?? '') !== 'image/jpeg' ||
                        $dimensionesFirma[0] < 1 || $dimensionesFirma[1] < 1 ||
                        $dimensionesFirma[0] > 4096 || $dimensionesFirma[1] > 2048) {
                        $erroresValidacion[] = 'El archivo recibido no corresponde a una firma JPEG válida.';
                    }
                }
            }

            if ($erroresValidacion) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La información del cierre de QA está incompleta o no es válida.',
                    'errores' => $erroresValidacion
                ], 422);
            }

            $conn->beginTransaction();

            /* Serializa los intentos simultáneos de cierre para la anormalidad. */
            bloquearAnormalidadParaEscritura($conn, $idAnormalidad);

            $consulta = $conn->prepare(
                "SELECT id_anormalidad, no_seguimiento
                 FROM SA_anormalidad WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);
            $anormalidadCierreQa = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$anormalidadCierreQa) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No se encontró la anormalidad seleccionada.'
                ], 404);
            }

            $estatusCierreGerente = obtenerEstatusActivoPorClave($conn, '5');

            if (!$estatusCierreGerente) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La clave 5 del estatus de cierre gerente no está configurada de forma única y activa.'
                ], 409);
            }

            $consulta = $conn->prepare(
                "SELECT id_anormalidad
                 FROM SA_cierre_reporte_calidad WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);

            if ($consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Esta anormalidad ya tiene registrado el cierre de Staff QA.'
                ], 409);
            }
            $consulta->closeCursor();

            /* Evita almacenar accidentalmente el nombre como bytes en el esquema anterior. */
            $consulta = $conn->query(
                "SELECT DATA_TYPE, CHARACTER_MAXIMUM_LENGTH
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = 'dbo'
                   AND TABLE_NAME = 'SA_cierre_reporte_calidad'
                   AND COLUMN_NAME = 'firma'"
            );
            $columnaFirma = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$columnaFirma || strtolower((string) $columnaFirma['DATA_TYPE']) !== 'nvarchar' ||
                (int) $columnaFirma['CHARACTER_MAXIMUM_LENGTH'] < 255) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La columna firma debe cambiarse a nvarchar(255) antes de registrar cierres QA.'
                ], 409);
            }

            /* El responsable debe seguir activo al momento de registrar. */
            $consulta = $conn->prepare(
                "SELECT IDe
                 FROM SA_factor WITH (HOLDLOCK)
                 WHERE IDe = :idFactor
                   AND activo = 1"
            );
            $consulta->execute([':idFactor' => $idFactor]);

            if (!$consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El factor responsable no existe o ya no está activo.'
                ], 409);
            }
            $consulta->closeCursor();

            /* La identidad ya validada de Staff QA se toma de la sesión. */
            $nominaCierreQa = nominaEnSesion();
            $nombreCierreQa = nombreEnSesion();
            $referenciaArchivo = strtoupper((string) $anormalidadCierreQa['no_seguimiento']);
            $referenciaArchivo = preg_replace('/[^A-Z0-9]+/', '-', $referenciaArchivo);
            $referenciaArchivo = trim((string) $referenciaArchivo, '-');
            $nominaArchivo = preg_replace('/[^A-Za-z0-9]+/', '-', $nominaCierreQa);

            if ($referenciaArchivo === '') {
                $referenciaArchivo = 'ANORMALIDAD-' . $idAnormalidad;
            }

            $nombreFirma = $referenciaArchivo . '-' . $nominaArchivo . '-QA.jpg';
            $directorioFirmas = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'img' .
                DIRECTORY_SEPARATOR . 'anormalidades' . DIRECTORY_SEPARATOR . 'firmasQA';

            if (!is_dir($directorioFirmas) &&
                !mkdir($directorioFirmas, 0775, true) && !is_dir($directorioFirmas)) {
                throw new RuntimeException('No fue posible crear el directorio de firmas QA.');
            }

            if (!is_writable($directorioFirmas)) {
                throw new RuntimeException('El directorio de firmas QA no tiene permisos de escritura.');
            }

            $rutaFirma = $directorioFirmas . DIRECTORY_SEPARATOR . $nombreFirma;

            if (is_file($rutaFirma)) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Ya existe un archivo de firma para este cierre; no se sobrescribió.'
                ], 409);
            }

            /* La apertura exclusiva impide sobrescribir un archivo concurrente. */
            $archivoFirma = @fopen($rutaFirma, 'x+b');

            if ($archivoFirma === false) {
                throw new RuntimeException('No fue posible crear de forma exclusiva el archivo de firma QA.');
            }

            $rutaFirmaCreada = $rutaFirma;
            $totalFirma = strlen($firmaBinaria);
            $posicionFirma = 0;

            try {
                while ($posicionFirma < $totalFirma) {
                    $bytesEscritos = fwrite(
                        $archivoFirma,
                        substr($firmaBinaria, $posicionFirma)
                    );

                    if ($bytesEscritos === false || $bytesEscritos === 0) {
                        throw new RuntimeException('No fue posible escribir completamente la firma QA.');
                    }

                    $posicionFirma += $bytesEscritos;
                }

                if (!fflush($archivoFirma)) {
                    throw new RuntimeException('No fue posible confirmar la escritura de la firma QA.');
                }
            } finally {
                fclose($archivoFirma);
            }

            $consulta = $conn->prepare(
                "INSERT INTO SA_cierre_reporte_calidad
                    (id_anormalidad, fecha_cierre, nomina_cierra, nombre_cierra,
                     firma, id_factor_gerente)
                 VALUES
                    (:idAnormalidad, SYSDATETIME(), :nominaCierra, :nombreCierra,
                     :firma, :idFactor)"
            );
            $consulta->execute([
                ':idAnormalidad' => $idAnormalidad,
                ':nominaCierra' => $nominaCierreQa,
                ':nombreCierra' => $nombreCierreQa,
                ':firma' => $nombreFirma,
                ':idFactor' => $idFactor
            ]);
            $consulta->closeCursor();

            /*
             * El cierre firmado por Staff QA deja la anormalidad pendiente de
             * autorización gerencial. Las etapas posteriores no se retroceden.
             */
            $consulta = $conn->prepare(
                "UPDATE A
                 SET id_estatus_actual = :idEstatusCierreGerente
                 FROM SA_anormalidad A
                 INNER JOIN SA_estatus_anormalidad E
                    ON E.IDe = A.id_estatus_actual
                 WHERE A.id_anormalidad = :idAnormalidad
                   AND LTRIM(RTRIM(E.CLAVE)) IN ('1', '2', '3', '4')"
            );
            $consulta->execute([
                ':idEstatusCierreGerente' => (int) $estatusCierreGerente['IDe'],
                ':idAnormalidad' => $idAnormalidad
            ]);
            $consulta->closeCursor();

            /* Obtiene el estatus real conservado después de la actualización. */
            $consulta = $conn->prepare(
                "SELECT
                    A.id_estatus_actual,
                    E.CLAVE AS clave_estatus,
                    E.NOMBRE AS estatus_actual
                 FROM SA_anormalidad A
                 INNER JOIN SA_estatus_anormalidad E
                    ON E.IDe = A.id_estatus_actual
                 WHERE A.id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);
            $estatusActual = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$estatusActual) {
                throw new RuntimeException('No fue posible consultar el estatus actualizado de la anormalidad.');
            }

            $conn->commit();
            $rutaFirmaCreada = null;

            responderJson([
                'status' => 'ok',
                'mensaje' => 'El cierre de Staff QA se registró correctamente.',
                'data' => [
                    'id_anormalidad' => $idAnormalidad,
                    'nomina_cierra' => $nominaCierreQa,
                    'nombre_cierra' => $nombreCierreQa,
                    'firma' => $nombreFirma,
                    'id_factor_gerente' => $idFactor,
                    'id_estatus_actual' => (int) $estatusActual['id_estatus_actual'],
                    'clave_estatus' => $estatusActual['clave_estatus'],
                    'estatus_actual' => $estatusActual['estatus_actual']
                ]
            ]);

        case '23':
            /* ============================================================
               Registro de la autorización y firma final del gerente
               ============================================================ */
            exigirPermiso('anormalidades', 'cierre_gerente');

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');
            $firmaDataUrl = obtenerTextoPost('firma');
            $erroresValidacion = [];
            $firmaBinaria = null;

            if ($idAnormalidad === null) {
                $erroresValidacion[] = 'La anormalidad seleccionada no es válida.';
            }

            if ($firmaDataUrl === null || strlen($firmaDataUrl) > 2800000 ||
                preg_match('#^data:image/jpeg;base64,([A-Za-z0-9+/=\r\n]+)$#D', $firmaDataUrl, $coincidenciaFirma) !== 1) {
                $erroresValidacion[] = 'La firma debe ser una imagen JPEG válida y no exceder 2 MB.';
            } else {
                $firmaBinaria = base64_decode($coincidenciaFirma[1], true);

                if ($firmaBinaria === false || strlen($firmaBinaria) < 100 ||
                    strlen($firmaBinaria) > 2097152) {
                    $erroresValidacion[] = 'El contenido de la firma está vacío, dañado o excede 2 MB.';
                } else {
                    $detectorMime = new finfo(FILEINFO_MIME_TYPE);
                    $mimeFirma = $detectorMime->buffer($firmaBinaria);
                    $dimensionesFirma = @getimagesizefromstring($firmaBinaria);

                    if ($mimeFirma !== 'image/jpeg' || !$dimensionesFirma ||
                        ($dimensionesFirma['mime'] ?? '') !== 'image/jpeg' ||
                        $dimensionesFirma[0] < 1 || $dimensionesFirma[1] < 1 ||
                        $dimensionesFirma[0] > 4096 || $dimensionesFirma[1] > 2048) {
                        $erroresValidacion[] = 'El archivo recibido no corresponde a una firma JPEG válida.';
                    }
                }
            }

            if ($erroresValidacion) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La autorización del gerente está incompleta o no es válida.',
                    'errores' => $erroresValidacion
                ], 422);
            }

            $conn->beginTransaction();

            /* Serializa dos autorizaciones simultáneas para la misma anormalidad. */
            $consulta = $conn->prepare(
                "SELECT
                    A.id_anormalidad,
                    A.no_seguimiento,
                    A.id_estatus_actual,
                    E.CLAVE AS clave_estatus,
                    E.NOMBRE AS estatus_actual
                 FROM SA_anormalidad A WITH (UPDLOCK, HOLDLOCK)
                 INNER JOIN SA_estatus_anormalidad E
                    ON E.IDe = A.id_estatus_actual
                 WHERE A.id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);
            $anormalidadCierreGerente = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$anormalidadCierreGerente) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No se encontró la anormalidad seleccionada.'
                ], 404);
            }

            if (trim((string) $anormalidadCierreGerente['clave_estatus']) === '6') {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'codigo' => 'ANORMALIDAD_FINALIZADA',
                    'mensaje' => 'La anormalidad ya se encuentra finalizada.',
                    'data' => [
                        'id_anormalidad' => $idAnormalidad,
                        'id_estatus_actual' => (int) $anormalidadCierreGerente['id_estatus_actual'],
                        'clave_estatus' => '6',
                        'estatus_actual' => $anormalidadCierreGerente['estatus_actual']
                    ]
                ], 409);
            }

            $estatusFinalizado = obtenerEstatusActivoPorClave($conn, '6');

            if (!$estatusFinalizado) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La clave 6 del estatus finalizado no está configurada de forma única y activa.'
                ], 409);
            }

            $consulta = $conn->prepare(
                "SELECT id_anormalidad
                 FROM SA_cierre_autorizacion_gerente WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);

            if ($consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Esta anormalidad ya tiene registrada la autorización del gerente.'
                ], 409);
            }
            $consulta->closeCursor();

            /*
             * El permiso 'cierre_gerente' habilita a autorizar, pero no a
             * autorizar cualquier anormalidad: el factor responsable que dejó
             * el cierre de Staff QA determina a qué gerencia le toca firmar.
             */
            $consulta = $conn->prepare(
                "SELECT F.CLAVE AS clave_factor, F.NOMBRE AS nombre_factor
                   FROM SA_cierre_reporte_calidad AS C
                        INNER JOIN SA_factor AS F ON F.IDe = C.id_factor_gerente
                  WHERE C.id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);
            $factorResponsable = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$factorResponsable) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Aún no se registra el cierre del reporte de calidad, '
                               . 'así que esta anormalidad todavía no tiene un área responsable.'
                ], 409);
            }

            $claveFactor = trim((string) $factorResponsable['clave_factor']);
            $rolQueFirma = GERENTE_POR_FACTOR[$claveFactor] ?? null;

            if ($rolQueFirma === null) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El factor responsable de esta anormalidad no tiene una '
                               . 'gerencia configurada para autorizar el cierre.'
                ], 409);
            }

            if (!tieneRol($rolQueFirma)) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Esta anormalidad quedó a cargo de '
                               . $factorResponsable['nombre_factor']
                               . ', así que solo el gerente / encargado de esa área '
                               . 'puede registrar la firma.'
                ], 403);
            }

            /* Impide convertir silenciosamente el nombre al antiguo varbinary. */
            $consulta = $conn->query(
                "SELECT DATA_TYPE, CHARACTER_MAXIMUM_LENGTH
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = 'dbo'
                   AND TABLE_NAME = 'SA_cierre_autorizacion_gerente'
                   AND COLUMN_NAME = 'firma'"
            );
            $columnaFirmaGerente = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$columnaFirmaGerente ||
                strtolower((string) $columnaFirmaGerente['DATA_TYPE']) !== 'nvarchar' ||
                (int) $columnaFirmaGerente['CHARACTER_MAXIMUM_LENGTH'] < 255) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La columna firma del gerente debe cambiarse a nvarchar(255) antes de registrar.'
                ], 409);
            }

            /*
             * El cierre de QA y su archivo JPEG son requisitos obligatorios
             * para permitir la autorización final.
             */
            $consulta = $conn->prepare(
                "SELECT firma
                 FROM SA_cierre_reporte_calidad WITH (HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);
            $firmaCierreQa = $consulta->fetchColumn();
            $consulta->closeCursor();

            if (!is_string($firmaCierreQa) || trim($firmaCierreQa) === '' ||
                basename($firmaCierreQa) !== $firmaCierreQa) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Registra primero el cierre y la firma de Staff QA.'
                ], 409);
            }

            $directorioFirmasQa = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'img' .
                DIRECTORY_SEPARATOR . 'anormalidades' . DIRECTORY_SEPARATOR . 'firmasQA';
            $rutaFirmaQa = $directorioFirmasQa . DIRECTORY_SEPARATOR . $firmaCierreQa;
            $detectorFirmaQa = new finfo(FILEINFO_MIME_TYPE);

            if (!is_file($rutaFirmaQa) || $detectorFirmaQa->file($rutaFirmaQa) !== 'image/jpeg') {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No se encontró la imagen JPEG de la firma de Staff QA.'
                ], 409);
            }

            /* La identidad del gerente se toma de la sesión. */
            $nominaCierreGerente = nominaEnSesion();
            $nombreCierreGerente = nombreEnSesion();
            $referenciaArchivo = strtoupper((string) $anormalidadCierreGerente['no_seguimiento']);
            $referenciaArchivo = preg_replace('/[^A-Z0-9]+/', '-', $referenciaArchivo);
            $referenciaArchivo = trim((string) $referenciaArchivo, '-');
            $nominaArchivo = preg_replace('/[^A-Za-z0-9]+/', '-', $nominaCierreGerente);

            if ($referenciaArchivo === '') {
                $referenciaArchivo = 'ANORMALIDAD-' . $idAnormalidad;
            }

            $nombreFirma = $referenciaArchivo . '-' . $nominaArchivo . '-GERENTE.jpg';
            $directorioFirmas = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'img' .
                DIRECTORY_SEPARATOR . 'anormalidades' . DIRECTORY_SEPARATOR . 'firmasGerente';

            if (!is_dir($directorioFirmas) &&
                !mkdir($directorioFirmas, 0775, true) && !is_dir($directorioFirmas)) {
                throw new RuntimeException('No fue posible crear el directorio de firmas del gerente.');
            }

            if (!is_writable($directorioFirmas)) {
                throw new RuntimeException('El directorio de firmas del gerente no tiene permisos de escritura.');
            }

            $rutaFirma = $directorioFirmas . DIRECTORY_SEPARATOR . $nombreFirma;

            if (is_file($rutaFirma)) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Ya existe un archivo de firma para esta autorización; no se sobrescribió.'
                ], 409);
            }

            $archivoFirma = @fopen($rutaFirma, 'x+b');

            if ($archivoFirma === false) {
                throw new RuntimeException('No fue posible crear de forma exclusiva la firma del gerente.');
            }

            $rutaFirmaCreada = $rutaFirma;
            $totalFirma = strlen($firmaBinaria);
            $posicionFirma = 0;

            try {
                while ($posicionFirma < $totalFirma) {
                    $bytesEscritos = fwrite(
                        $archivoFirma,
                        substr($firmaBinaria, $posicionFirma)
                    );

                    if ($bytesEscritos === false || $bytesEscritos === 0) {
                        throw new RuntimeException('No fue posible escribir completamente la firma del gerente.');
                    }

                    $posicionFirma += $bytesEscritos;
                }

                if (!fflush($archivoFirma)) {
                    throw new RuntimeException('No fue posible confirmar la escritura de la firma del gerente.');
                }
            } finally {
                fclose($archivoFirma);
            }

            $consulta = $conn->prepare(
                "INSERT INTO SA_cierre_autorizacion_gerente
                    (id_anormalidad, fecha_cierre, nomina_cierra, nombre_cierra, firma)
                 VALUES
                    (:idAnormalidad, SYSDATETIME(), :nominaCierra, :nombreCierra, :firma)"
            );
            $consulta->execute([
                ':idAnormalidad' => $idAnormalidad,
                ':nominaCierra' => $nominaCierreGerente,
                ':nombreCierra' => $nombreCierreGerente,
                ':firma' => $nombreFirma
            ]);
            $consulta->closeCursor();

            /* La autorización gerencial completa el flujo de la anormalidad. */
            $consulta = $conn->prepare(
                "UPDATE A
                 SET id_estatus_actual = :idEstatusFinalizado
                 FROM SA_anormalidad A
                 INNER JOIN SA_estatus_anormalidad E
                    ON E.IDe = A.id_estatus_actual
                 WHERE A.id_anormalidad = :idAnormalidad
                   AND LTRIM(RTRIM(E.CLAVE)) <> '6'"
            );
            $consulta->execute([
                ':idEstatusFinalizado' => (int) $estatusFinalizado['IDe'],
                ':idAnormalidad' => $idAnormalidad
            ]);
            $consulta->closeCursor();

            /* Devuelve el estatus final realmente almacenado. */
            $consulta = $conn->prepare(
                "SELECT
                    A.id_estatus_actual,
                    E.CLAVE AS clave_estatus,
                    E.NOMBRE AS estatus_actual
                 FROM SA_anormalidad A
                 INNER JOIN SA_estatus_anormalidad E
                    ON E.IDe = A.id_estatus_actual
                 WHERE A.id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);
            $estatusActual = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$estatusActual || trim((string) $estatusActual['clave_estatus']) !== '6') {
                throw new RuntimeException('No fue posible finalizar el estatus de la anormalidad.');
            }

            $conn->commit();
            $rutaFirmaCreada = null;

            responderJson([
                'status' => 'ok',
                'mensaje' => 'La autorización del gerente se registró correctamente.',
                'data' => [
                    'id_anormalidad' => $idAnormalidad,
                    'nomina_cierra' => $nominaCierreGerente,
                    'nombre_cierra' => $nombreCierreGerente,
                    'firma' => $nombreFirma,
                    'id_estatus_actual' => (int) $estatusActual['id_estatus_actual'],
                    'clave_estatus' => $estatusActual['clave_estatus'],
                    'estatus_actual' => $estatusActual['estatus_actual']
                ]
            ]);

        case '24':
            /* ============================================================
               Registro general único de reincidencia por anormalidad
               ============================================================ */
            exigirPermiso('anormalidades', 'reincidencia_registrar');

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');
            $numeroSeguimientoReferencia = obtenerTextoPost('numeroSeguimiento');
            $claveTipoReincidencia = obtenerTextoPost('claveTipoReincidencia');
            $detalleAnormalidad = obtenerTextoPost('detalleAnormalidad');
            $erroresValidacion = [];

            if ($idAnormalidad === null) {
                $erroresValidacion[] = 'La anormalidad seleccionada no es válida.';
            }

            if (!textoEsValido($numeroSeguimientoReferencia, 50)) {
                $erroresValidacion[] = 'El número de seguimiento no es válido.';
            }

            if (!textoEsValido($claveTipoReincidencia, 50)) {
                $erroresValidacion[] = 'La clave del tipo de reincidencia no es válida.';
            }

            if (!textoEsValido($detalleAnormalidad, 600)) {
                $erroresValidacion[] = 'El detalle debe contener entre 1 y 600 caracteres.';
            }

            if ($erroresValidacion) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La información de la reincidencia está incompleta o no es válida.',
                    'errores' => $erroresValidacion
                ], 422);
            }

            $conn->beginTransaction();

            /*
             * El bloqueo sobre la anormalidad serializa los intentos concurrentes
             * antes de consultar o insertar su único encabezado de reincidencia.
             */
            bloquearAnormalidadParaEscritura($conn, $idAnormalidad);

            $consulta = $conn->prepare(
                "SELECT id_anormalidad, no_seguimiento
                 FROM SA_anormalidad WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);
            $anormalidadReincidente = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$anormalidadReincidente) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No se encontró la anormalidad seleccionada.'
                ], 404);
            }

            /* Evita relacionar un folio manipulado o desactualizado. */
            if ((string) $anormalidadReincidente['no_seguimiento'] !==
                (string) $numeroSeguimientoReferencia) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El número de seguimiento no corresponde a la anormalidad seleccionada.'
                ], 409);
            }

            $consulta = $conn->prepare(
                "SELECT id_reincidencia
                 FROM SA_registro_general_reincidencia WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);

            if ($consulta->fetchColumn()) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Esta anormalidad ya tiene registrado su encabezado de reincidencia.'
                ], 409);
            }
            $consulta->closeCursor();

            /*
             * Recupera la clave canónica desde el catálogo. El formulario de
             * apertura de reincidencia únicamente admite la CLAVE 1 activa.
             */
            $consulta = $conn->prepare(
                "SELECT TOP (1) LTRIM(RTRIM(CLAVE)) AS clave
                 FROM SA_tipo_reincidencia WITH (HOLDLOCK)
                 WHERE LTRIM(RTRIM(CLAVE)) = :claveTipoReincidencia
                   AND LTRIM(RTRIM(CLAVE)) = '1'
                   AND activo = 1"
            );
            $consulta->execute([':claveTipoReincidencia' => $claveTipoReincidencia]);
            $tipoReincidencia = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$tipoReincidencia) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La clave del tipo de reincidencia no existe o está inactiva.'
                ], 409);
            }

            /* Fecha y turno definitivos se obtienen del servidor, no del navegador. */
            $consulta = $conn->query(
                "DECLARE @ahora datetime2(0) = SYSDATETIME();
                 SELECT
                    fecha_servidor = CONVERT(varchar(19), @ahora, 126),
                    clave_turno = CASE
                        WHEN CAST(@ahora AS time) >= CAST('08:00:00' AS time)
                         AND CAST(@ahora AS time) < CAST('20:00:00' AS time)
                        THEN '1' ELSE '2'
                    END"
            );
            $fechaTurno = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$fechaTurno) {
                throw new RuntimeException('No fue posible determinar la fecha y el turno de la reincidencia.');
            }

            $consulta = $conn->prepare(
                "SELECT TOP (1) IDe
                 FROM SA_turno WITH (HOLDLOCK)
                 WHERE CLAVE = :claveTurno
                   AND activo = 1
                 ORDER BY IDe"
            );
            $consulta->execute([':claveTurno' => $fechaTurno['clave_turno']]);
            $turnoServidor = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$turnoServidor) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No existe un turno activo para el horario actual.'
                ], 409);
            }

            /* Se reemplazará por la identidad de la sesión al implementar login. */
            $registradoPor = nombreEnSesion();

            $consulta = $conn->prepare(
                "INSERT INTO SA_registro_general_reincidencia
                    (id_anormalidad, no_seguimiento, fecha_hora, turno,
                     tipo_reincidencia, [detalle anormalidad], registrado_por)
                 OUTPUT
                    INSERTED.id_reincidencia,
                    INSERTED.id_anormalidad,
                    INSERTED.no_seguimiento,
                    CONVERT(varchar(19), INSERTED.fecha_hora, 126) AS fecha_hora,
                    INSERTED.turno,
                    INSERTED.tipo_reincidencia,
                    INSERTED.[detalle anormalidad] AS detalle_anormalidad,
                    INSERTED.registrado_por
                 VALUES
                    (:idAnormalidad, :numeroSeguimiento, :fechaHora, :turno,
                     :tipoReincidencia, :detalleAnormalidad, :registradoPor)"
            );
            $consulta->execute([
                ':idAnormalidad' => $idAnormalidad,
                ':numeroSeguimiento' => $anormalidadReincidente['no_seguimiento'],
                ':fechaHora' => $fechaTurno['fecha_servidor'],
                ':turno' => (int) $turnoServidor['IDe'],
                ':tipoReincidencia' => $tipoReincidencia['clave'],
                ':detalleAnormalidad' => $detalleAnormalidad,
                ':registradoPor' => $registradoPor
            ]);
            $reincidenciaRegistrada = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$reincidenciaRegistrada) {
                throw new RuntimeException('El INSERT no devolvió la reincidencia creada.');
            }

            $conn->commit();

            responderJson([
                'status' => 'ok',
                'mensaje' => 'La reincidencia se registró correctamente.',
                'data' => $reincidenciaRegistrada
            ], 201);

        case '25':
            /* ============================================================
               Consulta del encabezado y seguimientos de una reincidencia
               ============================================================ */
            exigirPermiso('anormalidades', 'ver');

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');

            if ($idAnormalidad === null) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El identificador de la anormalidad no es válido.'
                ], 400);
            }

            /*
             * Se parte de la anormalidad para poder devolver un historial vacío
             * cuando todavía no existe su encabezado de reincidencia.
             */
            $consulta = $conn->prepare(
                "SELECT
                    A.id_anormalidad,
                    A.no_seguimiento,
                    A.id_estatus_actual,
                    E.CLAVE AS clave_estatus,
                    E.NOMBRE AS estatus_actual,
                    A.codigo_linea,
                    L.nombre_linea,
                    L.idArea AS id_area,
                    AR.nombreArea AS nombre_area,
                    R.id_reincidencia,
                    CONVERT(varchar(19), R.fecha_hora, 126) AS fecha_hora_reincidencia,
                    R.turno AS id_turno_reincidencia,
                    COALESCE(NULLIF(T.CLAVE, ''), CONVERT(nvarchar(20), R.turno))
                        AS turno_reincidencia,
                    R.tipo_reincidencia,
                    TR.nombre AS tipo_reincidencia_nombre,
                    R.[detalle anormalidad] AS detalle_anormalidad,
                    R.registrado_por
                 FROM SA_anormalidad A
                 INNER JOIN SA_estatus_anormalidad E
                    ON E.IDe = A.id_estatus_actual
                 INNER JOIN SPC_LINEAS L
                    ON L.codigo_linea = A.codigo_linea
                 INNER JOIN SPC_AREAS AR
                    ON AR.idArea = L.idArea
                 LEFT JOIN SA_registro_general_reincidencia R
                    ON R.id_anormalidad = A.id_anormalidad
                 LEFT JOIN SA_turno T
                    ON T.IDe = R.turno
                 OUTER APPLY
                 (
                    SELECT TOP (1) TP.NOMBRE AS nombre
                    FROM SA_tipo_reincidencia TP
                    WHERE LTRIM(RTRIM(TP.CLAVE)) = LTRIM(RTRIM(R.tipo_reincidencia))
                    ORDER BY TP.activo DESC, TP.IDe
                 ) TR
                 WHERE A.id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([':idAnormalidad' => $idAnormalidad]);
            $encabezado = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$encabezado) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No se encontró la anormalidad seleccionada.'
                ], 404);
            }

            $reincidencia = null;
            $seguimientos = [];

            if ($encabezado['id_reincidencia'] !== null) {
                $idReincidencia = (int) $encabezado['id_reincidencia'];
                $reincidencia = [
                    'id_reincidencia' => $idReincidencia,
                    'fecha_hora' => $encabezado['fecha_hora_reincidencia'],
                    'id_turno' => (int) $encabezado['id_turno_reincidencia'],
                    'turno' => $encabezado['turno_reincidencia'],
                    'tipo_reincidencia' => $encabezado['tipo_reincidencia'],
                    'tipo_reincidencia_nombre' => $encabezado['tipo_reincidencia_nombre'],
                    'detalle_anormalidad' => $encabezado['detalle_anormalidad'],
                    'registrado_por' => $encabezado['registrado_por']
                ];

                $consulta = $conn->prepare(
                    "SELECT
                        ROW_NUMBER() OVER
                        (
                            ORDER BY D.id_anormalidad_reincidente
                        ) AS consecutivo,
                        D.id_anormalidad_reincidente,
                        D.id_reincidencia,
                        CONVERT(varchar(23), D.fecha_hora, 126) AS fecha_hora,
                        D.cantidad,
                        D.id_dispocicion,
                        DP.NOMBRE AS disposicion,
                        DP.DESCRIPCION AS disposicion_descripcion,
                        D.nomina_registraDET,
                        D.nombre_reguitraDET,
                        D.nomina_inspectorQA,
                        D.nombre_inspectorQA,
                        C.fecha_hora AS fecha_causa_contramedida,
                        C.causa,
                        C.contramedida,
                        C.[nomina atiende] AS nomina_atiende,
                        C.nombre_atiende
                     FROM SA_registro_detalle_reincidencia D
                     LEFT JOIN SA_disposicion DP
                        ON DP.IDe = D.id_dispocicion
                     LEFT JOIN SA_registro_causa_contramedida C
                        ON C.id_anormalidad_reincidente = D.id_anormalidad_reincidente
                       AND C.id_reincidencia = D.id_reincidencia
                     WHERE D.id_reincidencia = :idReincidencia
                     ORDER BY D.id_anormalidad_reincidente"
                );
                $consulta->execute([':idReincidencia' => $idReincidencia]);
                $seguimientos = $consulta->fetchAll(PDO::FETCH_ASSOC);
                $consulta->closeCursor();

                /* Las seis confirmaciones se consultan por separado para no duplicar filas del historial. */
                $consulta = $conn->prepare(
                    "SELECT
                        Q.id_anormalidad_reincidente,
                        Q.id_reincidencia,
                        Q.No_revision,
                        Q.id_momento,
                        LTRIM(RTRIM(M.CLAVE)) AS momento_clave,
                        M.NOMBRE AS momento,
                        Q.Resultado AS resultado
                     FROM SA_registro_confirmacion_QA Q
                     INNER JOIN SA_momento_revision M
                        ON M.IDe = Q.id_momento
                     WHERE Q.id_reincidencia = :idReincidencia
                     ORDER BY
                        Q.id_anormalidad_reincidente,
                        TRY_CONVERT(int, M.CLAVE),
                        Q.No_revision"
                );
                $consulta->execute([':idReincidencia' => $idReincidencia]);
                $confirmacionesQa = $consulta->fetchAll(PDO::FETCH_ASSOC);
                $consulta->closeCursor();
                $confirmacionesPorDetalle = [];

                foreach ($confirmacionesQa as $confirmacionQa) {
                    $idDetalleQa = (int) $confirmacionQa['id_anormalidad_reincidente'];
                    $confirmacionesPorDetalle[$idDetalleQa][] = [
                        'no_revision' => (int) $confirmacionQa['No_revision'],
                        'id_momento' => (int) $confirmacionQa['id_momento'],
                        'momento_clave' => $confirmacionQa['momento_clave'],
                        'momento' => $confirmacionQa['momento'],
                        'resultado' => $confirmacionQa['resultado']
                    ];
                }

                foreach ($seguimientos as &$seguimiento) {
                    $seguimiento['consecutivo'] = (int) $seguimiento['consecutivo'];
                    $seguimiento['id_anormalidad_reincidente'] =
                        (int) $seguimiento['id_anormalidad_reincidente'];
                    $seguimiento['id_reincidencia'] = (int) $seguimiento['id_reincidencia'];
                    $seguimiento['cantidad'] = $seguimiento['cantidad'] === null
                        ? null
                        : (int) $seguimiento['cantidad'];
                    $seguimiento['id_dispocicion'] = $seguimiento['id_dispocicion'] === null
                        ? null
                        : (int) $seguimiento['id_dispocicion'];
                    $seguimiento['confirmaciones_qa'] =
                        $confirmacionesPorDetalle[$seguimiento['id_anormalidad_reincidente']] ?? [];
                }
                unset($seguimiento);
            }

            responderJson([
                'status' => 'ok',
                'data' => [
                    'anormalidad' => [
                        'id_anormalidad' => (int) $encabezado['id_anormalidad'],
                        'no_seguimiento' => $encabezado['no_seguimiento'],
                        'id_estatus_actual' => (int) $encabezado['id_estatus_actual'],
                        'clave_estatus' => trim((string) $encabezado['clave_estatus']),
                        'estatus_actual' => $encabezado['estatus_actual'],
                        'codigo_linea' => $encabezado['codigo_linea'],
                        'nombre_linea' => $encabezado['nombre_linea'],
                        'id_area' => (int) $encabezado['id_area'],
                        'nombre_area' => $encabezado['nombre_area']
                    ],
                    'reincidencia' => $reincidencia,
                    'seguimientos' => $seguimientos
                ]
            ]);

        case '26':
            /* Catálogo independiente para el registro de reincidencias. */
            exigirPermiso('anormalidades', 'ver');

            $consulta = $conn->prepare(
                "SELECT
                    LTRIM(RTRIM(CLAVE)) AS clave,
                    NOMBRE AS nombre,
                    DESCRIPCION AS descripcion
                 FROM SA_tipo_reincidencia
                 WHERE activo = 1
                 ORDER BY TRY_CONVERT(int, CLAVE), CLAVE, NOMBRE"
            );
            $consulta->execute();
            $tiposReincidencia = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            responderJson([
                'status' => 'ok',
                'data' => $tiposReincidencia
            ]);

        case '27':
            /* ============================================================
               Alta de una anormalidad dentro del historial de reincidencia
               ============================================================ */
            exigirPermiso('anormalidades', 'reincidencia_agregar');

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');
            $idReincidencia = obtenerEnteroPositivoPost('idReincidencia');

            if ($idAnormalidad === null || $idReincidencia === null) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La anormalidad o la reincidencia seleccionada no es válida.'
                ], 400);
            }

            $conn->beginTransaction();

            /*
             * El encabezado se bloquea durante el alta para comprobar en el
             * servidor que ambos identificadores siguen relacionados.
             */
            bloquearAnormalidadParaEscritura($conn, $idAnormalidad);

            $consulta = $conn->prepare(
                "SELECT R.id_reincidencia, A.no_seguimiento
                 FROM SA_registro_general_reincidencia R WITH (UPDLOCK, HOLDLOCK)
                 INNER JOIN SA_anormalidad A
                    ON A.id_anormalidad = R.id_anormalidad
                 WHERE R.id_reincidencia = :idReincidencia
                   AND R.id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([
                ':idReincidencia' => $idReincidencia,
                ':idAnormalidad' => $idAnormalidad
            ]);
            $reincidencia = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$reincidencia) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La reincidencia no pertenece a la anormalidad seleccionada.'
                ], 409);
            }

            /*
             * id_anormalidad_reincidente es IDENTITY. Los demás datos se
             * completan posteriormente desde su sección correspondiente.
             */
            $consulta = $conn->prepare(
                "INSERT INTO SA_registro_detalle_reincidencia
                    (id_reincidencia)
                 OUTPUT
                    INSERTED.id_anormalidad_reincidente,
                    INSERTED.id_reincidencia
                 VALUES
                    (:idReincidencia)"
            );
            $consulta->execute([':idReincidencia' => $idReincidencia]);
            $registroCreado = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$registroCreado) {
                throw new RuntimeException('El INSERT no devolvió la reincidencia creada.');
            }

            $conn->commit();

            responderJson([
                'status' => 'ok',
                'mensaje' => 'La anormalidad reincidente se registró correctamente.',
                'data' => [
                    'id_anormalidad_reincidente' =>
                        (int) $registroCreado['id_anormalidad_reincidente'],
                    'id_reincidencia' => (int) $registroCreado['id_reincidencia'],
                    'no_seguimiento' => $reincidencia['no_seguimiento']
                ]
            ], 201);

        case '28':
            /* ============================================================
               Registro único de los datos generales del seguimiento
               ============================================================ */
            exigirPermiso('anormalidades', 'reincidencia_seguimiento');

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');
            $idReincidencia = obtenerEnteroPositivoPost('idReincidencia');
            $idAnormalidadReincidente =
                obtenerEnteroPositivoPost('idAnormalidadReincidente');
            $idDisposicion = obtenerEnteroPositivoPost('idDisposicion');
            $cantidad = obtenerEnteroNoNegativoPost('cantidad');
            $fechaHoraTexto = obtenerTextoPost('fechaHora');
            $fechaHora = null;
            $erroresValidacion = [];

            if ($idAnormalidad === null || $idReincidencia === null ||
                $idAnormalidadReincidente === null) {
                $erroresValidacion[] = 'No se identificó correctamente el registro de reincidencia.';
            }

            if ($idDisposicion === null) {
                $erroresValidacion[] = 'La disposición seleccionada no es válida.';
            }

            if ($cantidad === null) {
                $erroresValidacion[] = 'La cantidad debe ser un entero igual o mayor que cero.';
            }

            if ($fechaHoraTexto !== null &&
                preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $fechaHoraTexto)) {
                $fechaHoraObjeto = DateTimeImmutable::createFromFormat(
                    '!Y-m-d\TH:i',
                    $fechaHoraTexto
                );
                $erroresFecha = DateTimeImmutable::getLastErrors();

                if ($fechaHoraObjeto !== false &&
                    ($erroresFecha === false ||
                     ($erroresFecha['warning_count'] === 0 && $erroresFecha['error_count'] === 0)) &&
                    $fechaHoraObjeto->format('Y-m-d\TH:i') === $fechaHoraTexto) {
                    $fechaHora = $fechaHoraObjeto->format('Y-m-d H:i:s');
                }
            }

            if ($fechaHora === null) {
                $erroresValidacion[] = 'La fecha y hora del seguimiento no son válidas.';
            }

            if ($erroresValidacion) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Los datos del seguimiento están incompletos o no son válidos.',
                    'errores' => $erroresValidacion
                ], 422);
            }

            $conn->beginTransaction();

            /*
             * El bloqueo impide que dos solicitudes completen simultáneamente
             * la misma fila y valida toda la cadena de relaciones.
             */
            bloquearAnormalidadParaEscritura($conn, $idAnormalidad);

            $consulta = $conn->prepare(
                "SELECT
                    D.fecha_hora,
                    D.cantidad,
                    D.id_dispocicion,
                    D.nomina_registraDET,
                    D.nombre_reguitraDET
                 FROM SA_registro_detalle_reincidencia D WITH (UPDLOCK, HOLDLOCK)
                 INNER JOIN SA_registro_general_reincidencia R WITH (HOLDLOCK)
                    ON R.id_reincidencia = D.id_reincidencia
                 WHERE D.id_anormalidad_reincidente = :idAnormalidadReincidente
                   AND D.id_reincidencia = :idReincidencia
                   AND R.id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([
                ':idAnormalidadReincidente' => $idAnormalidadReincidente,
                ':idReincidencia' => $idReincidencia,
                ':idAnormalidad' => $idAnormalidad
            ]);
            $detalleReincidencia = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$detalleReincidencia) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El seguimiento no pertenece a la reincidencia seleccionada.'
                ], 409);
            }

            $camposSeguimiento = [
                $detalleReincidencia['cantidad'],
                $detalleReincidencia['id_dispocicion'],
                $detalleReincidencia['nomina_registraDET'],
                $detalleReincidencia['nombre_reguitraDET']
            ];
            $camposRegistrados = count(array_filter(
                $camposSeguimiento,
                static function ($valor): bool {
                    return $valor !== null;
                }
            ));

            if ($camposRegistrados === count($camposSeguimiento) &&
                $detalleReincidencia['fecha_hora'] !== null) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Los datos generales de este seguimiento ya fueron registrados.'
                ], 409);
            }

            /* Cualquier combinación parcial distinta de la fecha heredada se rechaza. */
            if ($camposRegistrados > 0) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El seguimiento contiene información parcial inconsistente y no fue modificado.'
                ], 409);
            }

            $consulta = $conn->prepare(
                "SELECT IDe
                 FROM SA_disposicion WITH (HOLDLOCK)
                 WHERE IDe = :idDisposicion
                   AND activo = 1"
            );
            $consulta->execute([':idDisposicion' => $idDisposicion]);
            $disposicionValida = $consulta->fetchColumn();
            $consulta->closeCursor();

            if ($disposicionValida === false) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La disposición seleccionada no existe o está inactiva.'
                ], 409);
            }

            /* La identidad de quien registra se toma de la sesión. */
            $nominaRegistra = nominaEnSesion();
            $nombreRegistra = nombreEnSesion();

            $consulta = $conn->prepare(
                "UPDATE SA_registro_detalle_reincidencia
                 SET fecha_hora = :fechaHora,
                     cantidad = :cantidad,
                     id_dispocicion = :idDisposicion,
                     nomina_registraDET = :nominaRegistra,
                     nombre_reguitraDET = :nombreRegistra
                 OUTPUT
                    INSERTED.id_anormalidad_reincidente,
                    INSERTED.id_reincidencia,
                    CONVERT(varchar(23), INSERTED.fecha_hora, 126) AS fecha_hora,
                    INSERTED.cantidad,
                    INSERTED.id_dispocicion,
                    INSERTED.nomina_registraDET,
                    INSERTED.nombre_reguitraDET
                 WHERE id_anormalidad_reincidente = :idAnormalidadReincidente
                   AND id_reincidencia = :idReincidencia
                   AND cantidad IS NULL
                   AND id_dispocicion IS NULL
                   AND nomina_registraDET IS NULL
                   AND nombre_reguitraDET IS NULL"
            );
            $consulta->execute([
                ':fechaHora' => $fechaHora,
                ':cantidad' => $cantidad,
                ':idDisposicion' => $idDisposicion,
                ':nominaRegistra' => $nominaRegistra,
                ':nombreRegistra' => $nombreRegistra,
                ':idAnormalidadReincidente' => $idAnormalidadReincidente,
                ':idReincidencia' => $idReincidencia
            ]);
            $seguimientoRegistrado = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$seguimientoRegistrado) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El seguimiento fue modificado por otra solicitud y no se duplicó el registro.'
                ], 409);
            }

            $conn->commit();

            responderJson([
                'status' => 'ok',
                'mensaje' => 'Los datos del seguimiento se registraron correctamente.',
                'data' => [
                    'id_anormalidad_reincidente' =>
                        (int) $seguimientoRegistrado['id_anormalidad_reincidente'],
                    'id_reincidencia' => (int) $seguimientoRegistrado['id_reincidencia'],
                    'fecha_hora' => $seguimientoRegistrado['fecha_hora'],
                    'cantidad' => (int) $seguimientoRegistrado['cantidad'],
                    'id_dispocicion' => (int) $seguimientoRegistrado['id_dispocicion'],
                    'nomina_registraDET' => $seguimientoRegistrado['nomina_registraDET'],
                    'nombre_reguitraDET' => $seguimientoRegistrado['nombre_reguitraDET']
                ]
            ]);

        case '29':
            /* ============================================================
               Registro único de causa y contramedida de una reincidencia
               ============================================================ */
            exigirPermiso('anormalidades', 'reincidencia_causa');

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');
            $idReincidencia = obtenerEnteroPositivoPost('idReincidencia');
            $idAnormalidadReincidente =
                obtenerEnteroPositivoPost('idAnormalidadReincidente');
            $causa = obtenerTextoPost('causa');
            $contramedida = obtenerTextoPost('contramedida');
            $erroresValidacion = [];

            if ($idAnormalidad === null || $idReincidencia === null ||
                $idAnormalidadReincidente === null) {
                $erroresValidacion[] = 'No se identificó correctamente el registro de reincidencia.';
            }

            if (!textoEsValido($causa, 700)) {
                $erroresValidacion[] = 'La causa debe contener entre 1 y 700 caracteres.';
            }

            if (!textoEsValido($contramedida, 700)) {
                $erroresValidacion[] = 'La contramedida debe contener entre 1 y 700 caracteres.';
            }

            if ($erroresValidacion) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La causa o la contramedida están incompletas o no son válidas.',
                    'errores' => $erroresValidacion
                ], 422);
            }

            $conn->beginTransaction();

            /* Valida y bloquea la relación completa del registro seleccionado. */
            bloquearAnormalidadParaEscritura($conn, $idAnormalidad);

            $consulta = $conn->prepare(
                "SELECT D.id_anormalidad_reincidente, D.id_reincidencia
                 FROM SA_registro_detalle_reincidencia D WITH (UPDLOCK, HOLDLOCK)
                 INNER JOIN SA_registro_general_reincidencia R WITH (HOLDLOCK)
                    ON R.id_reincidencia = D.id_reincidencia
                 WHERE D.id_anormalidad_reincidente = :idAnormalidadReincidente
                   AND D.id_reincidencia = :idReincidencia
                   AND R.id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([
                ':idAnormalidadReincidente' => $idAnormalidadReincidente,
                ':idReincidencia' => $idReincidencia,
                ':idAnormalidad' => $idAnormalidad
            ]);
            $detalleReincidencia = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$detalleReincidencia) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El registro no pertenece a la reincidencia seleccionada.'
                ], 409);
            }

            /*
             * El bloqueo de rango y la PK evitan insertar dos análisis para
             * el mismo id_anormalidad_reincidente bajo solicitudes simultáneas.
             */
            $consulta = $conn->prepare(
                "SELECT id_reincidencia
                 FROM SA_registro_causa_contramedida WITH (UPDLOCK, HOLDLOCK)
                 WHERE id_anormalidad_reincidente = :idAnormalidadReincidente"
            );
            $consulta->execute([
                ':idAnormalidadReincidente' => $idAnormalidadReincidente
            ]);
            $causaExistente = $consulta->fetchColumn();
            $consulta->closeCursor();

            if ($causaExistente !== false) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La causa y la contramedida de este registro ya fueron capturadas.'
                ], 409);
            }

            /* La identidad de quien atiende se toma de la sesión. */
            $nominaAtiende = nominaEnSesion();
            $nombreAtiende = nombreEnSesion();

            $consulta = $conn->prepare(
                "INSERT INTO SA_registro_causa_contramedida
                    (id_anormalidad_reincidente, id_reincidencia, fecha_hora,
                     causa, contramedida, [nomina atiende], nombre_atiende)
                 OUTPUT
                    INSERTED.id_anormalidad_reincidente,
                    INSERTED.id_reincidencia,
                    CONVERT(varchar(23), INSERTED.fecha_hora, 126) AS fecha_hora,
                    INSERTED.causa,
                    INSERTED.contramedida,
                    INSERTED.[nomina atiende] AS nomina_atiende,
                    INSERTED.nombre_atiende
                 VALUES
                    (:idAnormalidadReincidente, :idReincidencia, SYSDATETIME(),
                     :causa, :contramedida, :nominaAtiende, :nombreAtiende)"
            );
            $consulta->execute([
                ':idAnormalidadReincidente' => $idAnormalidadReincidente,
                ':idReincidencia' => $idReincidencia,
                ':causa' => $causa,
                ':contramedida' => $contramedida,
                ':nominaAtiende' => $nominaAtiende,
                ':nombreAtiende' => $nombreAtiende
            ]);
            $causaRegistrada = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$causaRegistrada) {
                throw new RuntimeException('El INSERT no devolvió la causa y contramedida registradas.');
            }

            $conn->commit();

            responderJson([
                'status' => 'ok',
                'mensaje' => 'La causa y la contramedida se registraron correctamente.',
                'data' => [
                    'id_anormalidad_reincidente' =>
                        (int) $causaRegistrada['id_anormalidad_reincidente'],
                    'id_reincidencia' => (int) $causaRegistrada['id_reincidencia'],
                    'fecha_hora' => $causaRegistrada['fecha_hora'],
                    'causa' => $causaRegistrada['causa'],
                    'contramedida' => $causaRegistrada['contramedida'],
                    'nomina_atiende' => $causaRegistrada['nomina_atiende'],
                    'nombre_atiende' => $causaRegistrada['nombre_atiende']
                ]
            ], 201);

        case '30':
            /* ============================================================
               Registro atómico de las seis confirmaciones de QA
               ============================================================ */
            exigirPermiso('anormalidades', 'reincidencia_qa');

            $idAnormalidad = obtenerEnteroPositivoPost('idAnormalidad');
            $idReincidencia = obtenerEnteroPositivoPost('idReincidencia');
            $idAnormalidadReincidente =
                obtenerEnteroPositivoPost('idAnormalidadReincidente');
            $resultadosQa = [
                'antes_1' => strtoupper((string) obtenerTextoPost('resultadoAntes1')),
                'antes_2' => strtoupper((string) obtenerTextoPost('resultadoAntes2')),
                'antes_3' => strtoupper((string) obtenerTextoPost('resultadoAntes3')),
                'despues_1' => strtoupper((string) obtenerTextoPost('resultadoDespues1')),
                'despues_2' => strtoupper((string) obtenerTextoPost('resultadoDespues2')),
                'despues_3' => strtoupper((string) obtenerTextoPost('resultadoDespues3'))
            ];
            $erroresValidacion = [];

            if ($idAnormalidad === null || $idReincidencia === null ||
                $idAnormalidadReincidente === null) {
                $erroresValidacion[] = 'No se identificó correctamente el registro de reincidencia.';
            }

            foreach ($resultadosQa as $nombreResultado => $resultadoQa) {
                if (!in_array($resultadoQa, ['OK', 'NG'], true)) {
                    $erroresValidacion[] =
                        'El resultado ' . str_replace('_', ' ', $nombreResultado) . ' no es válido.';
                }
            }

            if ($erroresValidacion) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Las confirmaciones de QA están incompletas o no son válidas.',
                    'errores' => $erroresValidacion
                ], 422);
            }

            $conn->beginTransaction();

            /* Bloquea el detalle y valida toda su cadena de relaciones. */
            bloquearAnormalidadParaEscritura($conn, $idAnormalidad);

            $consulta = $conn->prepare(
                "SELECT
                    D.nomina_inspectorQA,
                    D.nombre_inspectorQA
                 FROM SA_registro_detalle_reincidencia D WITH (UPDLOCK, HOLDLOCK)
                 INNER JOIN SA_registro_general_reincidencia R WITH (HOLDLOCK)
                    ON R.id_reincidencia = D.id_reincidencia
                 WHERE D.id_anormalidad_reincidente = :idAnormalidadReincidente
                   AND D.id_reincidencia = :idReincidencia
                   AND R.id_anormalidad = :idAnormalidad"
            );
            $consulta->execute([
                ':idAnormalidadReincidente' => $idAnormalidadReincidente,
                ':idReincidencia' => $idReincidencia,
                ':idAnormalidad' => $idAnormalidad
            ]);
            $detalleReincidencia = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$detalleReincidencia) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El registro no pertenece a la reincidencia seleccionada.'
                ], 409);
            }

            /*
             * Un registro parcial no se completa automáticamente porque podría
             * ocultar una operación previa interrumpida o una carga manual.
             */
            $consulta = $conn->prepare(
                "SELECT
                    Q.No_revision,
                    Q.id_momento,
                    LTRIM(RTRIM(M.CLAVE)) AS momento_clave
                 FROM SA_registro_confirmacion_QA Q WITH (UPDLOCK, HOLDLOCK)
                 INNER JOIN SA_momento_revision M
                    ON M.IDe = Q.id_momento
                 WHERE Q.id_anormalidad_reincidente = :idAnormalidadReincidente"
            );
            $consulta->execute([
                ':idAnormalidadReincidente' => $idAnormalidadReincidente
            ]);
            $confirmacionesExistentes = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $consulta->closeCursor();
            $inspectorCompleto = $detalleReincidencia['nomina_inspectorQA'] !== null &&
                $detalleReincidencia['nombre_inspectorQA'] !== null;
            $inspectorParcial = $detalleReincidencia['nomina_inspectorQA'] !== null ||
                $detalleReincidencia['nombre_inspectorQA'] !== null;
            $clavesConfirmacionExistentes = [];

            foreach ($confirmacionesExistentes as $confirmacionExistente) {
                $clavesConfirmacionExistentes[] =
                    $confirmacionExistente['momento_clave'] . '_' .
                    $confirmacionExistente['No_revision'];
            }

            $clavesConfirmacionEsperadas = ['1_1', '1_2', '1_3', '2_1', '2_2', '2_3'];
            $confirmacionesCompletas = count($confirmacionesExistentes) === 6 &&
                count(array_diff($clavesConfirmacionEsperadas, $clavesConfirmacionExistentes)) === 0;

            if ($confirmacionesCompletas && $inspectorCompleto) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Las confirmaciones de QA de este registro ya fueron capturadas.'
                ], 409);
            }

            if ($confirmacionesExistentes || $inspectorParcial) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La confirmación de QA contiene información parcial inconsistente y no fue modificada.'
                ], 409);
            }

            /* Las claves 1 y 2 determinan los momentos antes y después. */
            $consulta = $conn->prepare(
                "SELECT IDe, LTRIM(RTRIM(CLAVE)) AS clave
                 FROM SA_momento_revision WITH (HOLDLOCK)
                 WHERE LTRIM(RTRIM(CLAVE)) IN ('1', '2')
                   AND activo = 1"
            );
            $consulta->execute();
            $momentosCatalogo = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $consulta->closeCursor();
            $idsMomento = [];

            foreach ($momentosCatalogo as $momentoCatalogo) {
                $idsMomento[$momentoCatalogo['clave']] = (int) $momentoCatalogo['IDe'];
            }

            if (count($momentosCatalogo) !== 2 || !isset($idsMomento['1'], $idsMomento['2'])) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No están disponibles los momentos activos para antes y después.'
                ], 409);
            }

            /* La identidad del inspector de QA se toma de la sesión. */
            $nominaInspectorQa = nominaEnSesion();
            $nombreInspectorQa = nombreEnSesion();

            $consulta = $conn->prepare(
                "UPDATE SA_registro_detalle_reincidencia
                 SET nomina_inspectorQA = :nominaInspectorQa,
                     nombre_inspectorQA = :nombreInspectorQa
                 OUTPUT
                    INSERTED.nomina_inspectorQA,
                    INSERTED.nombre_inspectorQA
                 WHERE id_anormalidad_reincidente = :idAnormalidadReincidente
                   AND id_reincidencia = :idReincidencia
                   AND nomina_inspectorQA IS NULL
                   AND nombre_inspectorQA IS NULL"
            );
            $consulta->execute([
                ':nominaInspectorQa' => $nominaInspectorQa,
                ':nombreInspectorQa' => $nombreInspectorQa,
                ':idAnormalidadReincidente' => $idAnormalidadReincidente,
                ':idReincidencia' => $idReincidencia
            ]);
            $inspectorRegistrado = $consulta->fetch(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (!$inspectorRegistrado) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'El inspector fue modificado por otra solicitud y no se duplicó el registro.'
                ], 409);
            }

            $consulta = $conn->prepare(
                "INSERT INTO SA_registro_confirmacion_QA
                    (id_anormalidad_reincidente, id_reincidencia,
                     No_revision, id_momento, Resultado)
                 OUTPUT
                    INSERTED.id_anormalidad_reincidente,
                    INSERTED.id_reincidencia,
                    INSERTED.No_revision,
                    INSERTED.id_momento,
                    INSERTED.Resultado AS resultado
                 SELECT
                    :idAnormalidadReincidente,
                    :idReincidencia,
                    V.No_revision,
                    V.id_momento,
                    V.Resultado
                 FROM (VALUES
                    (1, :idMomentoAntes, :resultadoAntes1),
                    (2, :idMomentoAntes2, :resultadoAntes2),
                    (3, :idMomentoAntes3, :resultadoAntes3),
                    (1, :idMomentoDespues, :resultadoDespues1),
                    (2, :idMomentoDespues2, :resultadoDespues2),
                    (3, :idMomentoDespues3, :resultadoDespues3)
                 ) V (No_revision, id_momento, Resultado)"
            );
            $consulta->execute([
                ':idAnormalidadReincidente' => $idAnormalidadReincidente,
                ':idReincidencia' => $idReincidencia,
                ':idMomentoAntes' => $idsMomento['1'],
                ':resultadoAntes1' => $resultadosQa['antes_1'],
                ':idMomentoAntes2' => $idsMomento['1'],
                ':resultadoAntes2' => $resultadosQa['antes_2'],
                ':idMomentoAntes3' => $idsMomento['1'],
                ':resultadoAntes3' => $resultadosQa['antes_3'],
                ':idMomentoDespues' => $idsMomento['2'],
                ':resultadoDespues1' => $resultadosQa['despues_1'],
                ':idMomentoDespues2' => $idsMomento['2'],
                ':resultadoDespues2' => $resultadosQa['despues_2'],
                ':idMomentoDespues3' => $idsMomento['2'],
                ':resultadoDespues3' => $resultadosQa['despues_3']
            ]);
            $confirmacionesRegistradas = $consulta->fetchAll(PDO::FETCH_ASSOC);
            $consulta->closeCursor();

            if (count($confirmacionesRegistradas) !== 6) {
                throw new RuntimeException('No se registraron las seis confirmaciones de QA.');
            }

            $conn->commit();

            foreach ($confirmacionesRegistradas as &$confirmacionRegistrada) {
                $confirmacionRegistrada['id_anormalidad_reincidente'] =
                    (int) $confirmacionRegistrada['id_anormalidad_reincidente'];
                $confirmacionRegistrada['id_reincidencia'] =
                    (int) $confirmacionRegistrada['id_reincidencia'];
                $confirmacionRegistrada['No_revision'] =
                    (int) $confirmacionRegistrada['No_revision'];
                $confirmacionRegistrada['id_momento'] =
                    (int) $confirmacionRegistrada['id_momento'];
            }
            unset($confirmacionRegistrada);

            responderJson([
                'status' => 'ok',
                'mensaje' => 'Las confirmaciones de QA se registraron correctamente.',
                'data' => [
                    'nomina_inspectorQA' => $inspectorRegistrado['nomina_inspectorQA'],
                    'nombre_inspectorQA' => $inspectorRegistrado['nombre_inspectorQA'],
                    'confirmaciones' => $confirmacionesRegistradas
                ]
            ], 201);

        case '31':
            /* ============================================================
               Modelos activos asignados a una línea (SRD_MODELOS_LINEA)
               ============================================================ */
            exigirPermiso('anormalidades', 'ver');

            $codigoLinea = obtenerTextoPost('codigoLinea');

            if ($codigoLinea === null || $codigoLinea === '' || strlen($codigoLinea) > 20) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La línea seleccionada no es válida.'
                ], 400);
            }

            $sql = "SELECT M.idModelo,
                           M.modelo
                    FROM SRD_MODELOS_LINEA ML
                    INNER JOIN SRD_MODELOS M
                        ON M.idModelo = ML.idModelo
                    WHERE ML.codigo_linea = :codigoLinea
                      AND M.activo = 1
                    ORDER BY M.modelo";
            $consulta = $conn->prepare($sql);
            $consulta->execute([':codigoLinea' => $codigoLinea]);

            responderJson([
                'status' => 'ok',
                'data' => $consulta->fetchAll(PDO::FETCH_ASSOC)
            ]);

        default:
            responderJson([
                'status' => 'error',
                'mensaje' => 'La opción solicitada no existe.'
            ], 400);
    }
} catch (PDOException $error) {
    if (isset($conn) && $conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }

    if (is_string($rutaFirmaCreada) && $rutaFirmaCreada !== '' && is_file($rutaFirmaCreada) &&
        !@unlink($rutaFirmaCreada)) {
        error_log('No fue posible eliminar la firma QA creada tras revertir la transacción: ' . $rutaFirmaCreada);
    }

    // El detalle técnico se registra en el servidor, pero no se expone al cliente.
    error_log('Error PDO en operacionesAnormalidades.php, opción ' . $opcion . ': ' . $error->getMessage());

    $codigoSqlServer = isset($error->errorInfo[1]) ? (int) $error->errorInfo[1] : 0;

    if ($opcion === '1' && in_array($codigoSqlServer, [2601, 2627], true)) {
        responderJson([
            'status' => 'error',
            'mensaje' => 'El número de seguimiento ya fue utilizado. Inténtalo nuevamente.'
        ], 409);
    }

    if ($opcion === '12' && in_array($codigoSqlServer, [2601, 2627], true)) {
        responderJson([
            'status' => 'error',
            'mensaje' => 'El análisis PE o su factor ya fueron registrados para esta anormalidad.'
        ], 409);
    }

    if ($opcion === '13' && in_array($codigoSqlServer, [2601, 2627], true)) {
        responderJson([
            'status' => 'error',
            'mensaje' => 'El análisis de operación o su factor ya fueron registrados para esta anormalidad.'
        ], 409);
    }

    if ($opcion === '14' && in_array($codigoSqlServer, [2601, 2627], true)) {
        responderJson([
            'status' => 'error',
            'mensaje' => 'Las confirmaciones QA o su factor ya fueron registrados para esta anormalidad.'
        ], 409);
    }

    if ($opcion === '15' && in_array($codigoSqlServer, [2601, 2627], true)) {
        responderJson([
            'status' => 'error',
            'mensaje' => 'La causa y la contramedida ya fueron registradas para esta anormalidad.'
        ], 409);
    }

    if ($opcion === '17' && in_array($codigoSqlServer, [2601, 2627], true)) {
        responderJson([
            'status' => 'error',
            'mensaje' => 'Esta anormalidad ya tiene registrada una disposición de Calidad.'
        ], 409);
    }

    if ($opcion === '18' && in_array($codigoSqlServer, [2601, 2627], true)) {
        responderJson([
            'status' => 'error',
            'mensaje' => 'La revisión de material antes de la anormalidad ya fue registrada.'
        ], 409);
    }

    if ($opcion === '19' && in_array($codigoSqlServer, [2601, 2627], true)) {
        responderJson([
            'status' => 'error',
            'mensaje' => 'El reinicio de operación o sus muestras ya fueron registrados.'
        ], 409);
    }

    if ($opcion === '22' && in_array($codigoSqlServer, [2601, 2627], true)) {
        responderJson([
            'status' => 'error',
            'mensaje' => 'Esta anormalidad ya tiene registrado el cierre de Staff QA.'
        ], 409);
    }

    if ($opcion === '23' && in_array($codigoSqlServer, [2601, 2627], true)) {
        responderJson([
            'status' => 'error',
            'mensaje' => 'Esta anormalidad ya tiene registrada la autorización del gerente.'
        ], 409);
    }

    if ($opcion === '24' && in_array($codigoSqlServer, [2601, 2627], true)) {
        responderJson([
            'status' => 'error',
            'mensaje' => 'Esta anormalidad ya tiene registrado su encabezado de reincidencia.'
        ], 409);
    }

    if ($opcion === '29' && in_array($codigoSqlServer, [2601, 2627], true)) {
        responderJson([
            'status' => 'error',
            'mensaje' => 'La causa y la contramedida de este registro ya fueron capturadas.'
        ], 409);
    }

    if ($opcion === '30' && in_array($codigoSqlServer, [2601, 2627], true)) {
        responderJson([
            'status' => 'error',
            'mensaje' => 'Las confirmaciones de QA de este registro ya fueron capturadas.'
        ], 409);
    }

    responderJson([
        'status' => 'error',
        'mensaje' => in_array($opcion, ['1', '10', '12', '13', '14', '15', '17', '18', '19', '22', '23', '24', '27', '28', '29', '30'], true)
            ? 'No fue posible registrar la información solicitada.'
            : 'No fue posible consultar la información solicitada.'
    ], 500);
} catch (Throwable $error) {
    if (isset($conn) && $conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }

    if (is_string($rutaFirmaCreada) && $rutaFirmaCreada !== '' && is_file($rutaFirmaCreada) &&
        !@unlink($rutaFirmaCreada)) {
        error_log('No fue posible eliminar la firma QA creada tras revertir la operación: ' . $rutaFirmaCreada);
    }

    error_log('Error en operacionesAnormalidades.php, opción ' . $opcion . ': ' . $error->getMessage());

    responderJson([
        'status' => 'error',
        'mensaje' => 'No fue posible completar la operación solicitada.'
    ], 500);
}
