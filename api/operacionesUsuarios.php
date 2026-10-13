<?php
/*
 * Operaciones de usuarios y sesión.
 *
 * Opciones disponibles:
 *   1 - Iniciar sesión
 *   2 - Cerrar sesión
 *   3 - Consultar los datos de la sesión actual
 *   4 - Cambiar la contraseña del usuario en sesión (pages/perfil.php)
 *
 * Aquí se irán agregando el resto de operaciones sobre las tablas de
 * usuarios, roles, permisos y excepciones.
 */

date_default_timezone_set('America/Mexico_City');

header('Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Allow: POST, OPTIONS');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once('./sesion.php');

/*
 * Valor de usuarios.estatus que habilita el ingreso.
 * Si en la base se guarda con otra palabra, se ajusta aquí.
 */
const ESTATUS_ACTIVO = 'activo';

/*
 * Aplicación a la que pertenece esta instalación. Las tablas de roles y
 * permisos están compartidas entre sistemas, así que todas las consultas
 * filtran por este código.
 */
const CODIGO_SISTEMA = 'PC';

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
 * Obtiene una contraseña enviada por POST. No se recorta: los espacios pueden
 * formar parte del valor (aunque no cuentan como carácter especial).
 */
function obtenerPasswordPost(string $campo): ?string
{
    if (!isset($_POST[$campo]) || is_array($_POST[$campo])) {
        return null;
    }

    return (string) $_POST[$campo];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson([
        'status' => 'error',
        'mensaje' => 'Método no permitido.'
    ], 405);
}

/* Las peticiones deben venir de la aplicación, no de la barra de direcciones. */
if (strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest') !== 0) {
    responderJson([
        'status' => 'error',
        'mensaje' => 'La solicitud no fue originada por la aplicación.'
    ], 403);
}

include('./conexion.php');

$opcion = $_POST['opcion'] ?? '';

try {
    switch ($opcion) {

        case '1':
            /* ============================================================
               Iniciar sesión
               ============================================================ */
            $nomina = obtenerTextoPost('usuario');

            /* La contraseña no se recorta: los espacios pueden ser parte de ella. */
            $password = (isset($_POST['password']) && !is_array($_POST['password']))
                ? (string) $_POST['password']
                : '';

            if ($nomina === null || $nomina === '' || $password === '') {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Escribe tu usuario y tu contraseña.'
                ], 400);
            }

            $consulta = $conn->prepare(
                'SELECT nominaUsuario, nombre, passwordHash, estatus
                   FROM usuarios
                  WHERE nominaUsuario = :nomina'
            );
            $consulta->execute([':nomina' => $nomina]);
            $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

            /*
             * Un solo mensaje para "no existe" y "contraseña incorrecta":
             * distinguirlos permitiría averiguar qué nóminas están dadas de alta.
             */
            if (!$usuario || !password_verify($password, (string) $usuario['passwordHash'])) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Usuario o contraseña incorrectos.'
                ], 401);
            }

            if (strcasecmp(trim((string) $usuario['estatus']), ESTATUS_ACTIVO) !== 0) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Esta cuenta no está activa. Solicita ayuda al administrador.'
                ], 403);
            }

            /*
             * Permisos efectivos = los que da el rol, más los concedidos por
             * excepción, menos los denegados. Las excepciones vencidas no
             * cuentan.
             *
             * Se resuelve una sola vez, aquí, para que después cada guard sea
             * una búsqueda en memoria y no una consulta de cinco tablas.
             *
             * Los parámetros que se repiten llevan nombre distinto porque la
             * conexión trabaja con sentencias preparadas del lado del cliente.
             */
            $sqlPermisos =
                "WITH DelRol AS (
                     SELECT p.idPermiso
                     FROM usuario_roles AS ur
                          INNER JOIN roles AS r         ON r.idRol     = ur.idRol AND r.activo = 1
                          INNER JOIN rol_permisos AS rp ON rp.idRol    = r.idRol
                          INNER JOIN permisos AS p      ON p.idPermiso = rp.idPermiso
                     WHERE ur.nominaUsuario = :nomina
                       AND r.codigoSistema  = :sistemaRol
                 ),
                 Excepciones AS (
                     SELECT idPermiso, tipo
                     FROM usuario_permisos
                     WHERE nominaUsuario = :nominaExcepcion
                       AND (vigenciaHasta IS NULL OR vigenciaHasta >= CAST(SYSDATETIME() AS date))
                 )
                 SELECT m.codigoModulo, p.accion
                 FROM permisos AS p
                      INNER JOIN modulos AS m ON m.idModulo = p.idModulo AND m.activo = 1
                 WHERE m.codigoSistema = :sistemaModulo
                   AND p.idPermiso IN (
                           SELECT idPermiso FROM DelRol
                           UNION
                           SELECT idPermiso FROM Excepciones WHERE tipo = 'conceder'
                       )
                   AND p.idPermiso NOT IN (
                           SELECT idPermiso FROM Excepciones WHERE tipo = 'denegar'
                       )
                 ORDER BY m.codigoModulo, p.accion";

            $consultaPermisos = $conn->prepare($sqlPermisos);
            $consultaPermisos->execute([
                ':nomina'          => $usuario['nominaUsuario'],
                ':nominaExcepcion' => $usuario['nominaUsuario'],
                ':sistemaRol'      => CODIGO_SISTEMA,
                ':sistemaModulo'   => CODIGO_SISTEMA
            ]);

            /* Se aplanan a 'modulo.accion', que es el formato que usan los guards. */
            $permisos = array_map(
                static function (array $fila): string {
                    return $fila['codigoModulo'] . '.' . $fila['accion'];
                },
                $consultaPermisos->fetchAll(PDO::FETCH_ASSOC)
            );

            /*
             * Sin accesos en esta aplicación no se abre la sesión. Cubre tanto
             * al usuario sin rol asignado como al que solo tiene roles de otro
             * sistema. Se valida antes de tocar $_SESSION para no dejar una
             * sesión a medias que sí pasaría el filtro de index.php.
             */
            if (!$permisos) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Tu usuario no tiene accesos asignados en esta aplicación. '
                               . 'Solicítalos al administrador.'
                ], 403);
            }

            /* Cambiar el id al autenticar evita la fijación de sesión. */
            session_regenerate_id(true);

            $_SESSION['usuario'] = [
                'nomina' => $usuario['nominaUsuario'],
                'nombre' => $usuario['nombre'],
                'inicio' => date('Y-m-d H:i:s')
            ];

            $_SESSION['permisos'] = $permisos;

            /*
             * Los roles se guardan aparte de los permisos porque algunas
             * validaciones dependen del puesto y no solo de la acción: por
             * ejemplo, la firma del cierre de una anormalidad solo la puede
             * registrar el gerente del área responsable.
             */
            $consultaRoles = $conn->prepare(
                "SELECT r.codigoRol
                   FROM usuario_roles AS ur
                        INNER JOIN roles AS r ON r.idRol = ur.idRol AND r.activo = 1
                  WHERE ur.nominaUsuario = :nominaRol
                    AND r.codigoSistema  = :sistemaDelRol"
            );
            $consultaRoles->execute([
                ':nominaRol'     => $usuario['nominaUsuario'],
                ':sistemaDelRol' => CODIGO_SISTEMA
            ]);

            $_SESSION['roles'] = $consultaRoles->fetchAll(PDO::FETCH_COLUMN);

            responderJson([
                'status' => 'ok',
                'mensaje' => 'Sesión iniciada.',
                'usuario' => [
                    'nomina' => $usuario['nominaUsuario'],
                    'nombre' => $usuario['nombre']
                ],
                'redirigir' => 'index.php'
            ]);
            break;

        case '2':
            /* ============================================================
               Cerrar sesión
               ============================================================ */
            cerrarSesion();

            responderJson([
                'status' => 'ok',
                'mensaje' => 'Sesión cerrada.',
                'redirigir' => 'login.php'
            ]);
            break;

        case '3':
            /* ============================================================
               Datos de la sesión actual
               ============================================================ */
            $enSesion = usuarioEnSesion();

            if ($enSesion === null) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No hay una sesión activa.'
                ], 401);
            }

            responderJson([
                'status' => 'ok',
                'usuario' => [
                    'nomina' => $enSesion['nomina'],
                    'nombre' => $enSesion['nombre'],
                    'inicio' => $enSesion['inicio']
                ],
                /* Útil para comprobar qué resolvió el inicio de sesión. */
                'permisos' => $_SESSION['permisos'] ?? [],
                'totalPermisos' => count($_SESSION['permisos'] ?? [])
            ]);
            break;

        case '4':
            /* ============================================================
               Cambio de contraseña del usuario en sesión (pages/perfil.php)
               - No requiere permisos: solo una sesión iniciada.
               - El token CSRF lo genera pages/perfil.php en la sesión.
               - Misma política que la aplicación de defectos (la tabla usuarios
                 es global): 8 caracteres, mayúscula, minúscula y carácter especial.
               ============================================================ */
            $usuarioSesion = usuarioEnSesion();

            if ($usuarioSesion === null) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La sesión terminó. Inicia sesión nuevamente para cambiar tu contraseña.'
                ], 401);
            }

            $tokenRecibido = obtenerPasswordPost('csrf') ?? '';
            $tokenSesion = isset($_SESSION['csrf_perfil']) && is_string($_SESSION['csrf_perfil'])
                ? $_SESSION['csrf_perfil']
                : '';

            if ($tokenRecibido === '' || $tokenSesion === '' || !hash_equals($tokenSesion, $tokenRecibido)) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La solicitud ya no es válida. Recarga la página e inténtalo nuevamente.'
                ], 403);
            }

            $nomina = trim((string) ($usuarioSesion['nomina'] ?? ''));
            $contrasenaActual = obtenerPasswordPost('contrasenaActual');
            $contrasenaNueva = obtenerPasswordPost('contrasenaNueva');
            $confirmarContrasena = obtenerPasswordPost('confirmarContrasena');
            $errores = [];

            if ($nomina === '') {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La sesión no contiene un usuario válido.'
                ], 401);
            }

            if ($contrasenaActual === null || $contrasenaActual === '' || strlen($contrasenaActual) > 4096) {
                $errores[] = 'Ingresa tu contraseña actual.';
            }

            if ($contrasenaNueva === null || $contrasenaNueva === '') {
                $errores[] = 'Captura una nueva contraseña.';
            } elseif (strlen($contrasenaNueva) > 4096) {
                $errores[] = 'La nueva contraseña excede la longitud permitida.';
            } else {
                if (mb_strlen($contrasenaNueva, 'UTF-8') < 8) {
                    $errores[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
                }

                if (preg_match('/\p{Lu}/u', $contrasenaNueva) !== 1) {
                    $errores[] = 'La nueva contraseña debe incluir una letra mayúscula.';
                }

                if (preg_match('/\p{Ll}/u', $contrasenaNueva) !== 1) {
                    $errores[] = 'La nueva contraseña debe incluir una letra minúscula.';
                }

                if (preg_match('/[^\p{L}\p{N}\s]/u', $contrasenaNueva) !== 1) {
                    $errores[] = 'La nueva contraseña debe incluir un carácter especial.';
                }

                if (preg_match('/[\x00-\x1F\x7F]/u', $contrasenaNueva) === 1) {
                    $errores[] = 'La nueva contraseña contiene caracteres no permitidos.';
                }
            }

            if ($confirmarContrasena === null || $contrasenaNueva !== $confirmarContrasena) {
                $errores[] = 'La confirmación no coincide con la nueva contraseña.';
            }

            if ($errores) {
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'Revisa la información capturada.',
                    'detalle' => implode(' ', $errores),
                    'campo' => 'contrasenaNueva'
                ], 422);
            }

            $conn->beginTransaction();

            /* El bloqueo mantiene estable el hash entre la verificación y su reemplazo. */
            $consultaUsuario = $conn->prepare(
                'SELECT passwordHash, estatus
                   FROM usuarios WITH (UPDLOCK, HOLDLOCK)
                  WHERE nominaUsuario = :nomina'
            );
            $consultaUsuario->execute([':nomina' => $nomina]);
            $usuario = $consultaUsuario->fetch(PDO::FETCH_ASSOC);
            $consultaUsuario->closeCursor();

            if (!$usuario) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'No se encontró el usuario de la sesión.'
                ], 404);
            }

            if (strcasecmp(trim((string) $usuario['estatus']), ESTATUS_ACTIVO) !== 0) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La cuenta no está activa y su contraseña no puede modificarse.'
                ], 403);
            }

            $hashActual = (string) $usuario['passwordHash'];

            if (!password_verify($contrasenaActual, $hashActual)) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La contraseña actual es incorrecta.',
                    'campo' => 'contrasenaActual'
                ], 422);
            }

            if (password_verify($contrasenaNueva, $hashActual)) {
                $conn->rollBack();
                responderJson([
                    'status' => 'error',
                    'mensaje' => 'La nueva contraseña debe ser diferente a la actual.',
                    'campo' => 'contrasenaNueva'
                ], 422);
            }

            $nuevoHash = password_hash($contrasenaNueva, PASSWORD_DEFAULT);

            $actualizaPassword = $conn->prepare(
                'UPDATE usuarios
                    SET passwordHash = :passwordHash
                  WHERE nominaUsuario = :nomina'
            );
            $actualizaPassword->execute([
                ':passwordHash' => $nuevoHash,
                ':nomina' => $nomina
            ]);

            $conn->commit();

            /* Después de una operación sensible se renueva el id de sesión y el token. */
            session_regenerate_id(true);
            $_SESSION['csrf_perfil'] = bin2hex(random_bytes(32));

            responderJson([
                'status' => 'ok',
                'mensaje' => 'Tu contraseña fue actualizada correctamente.',
                'csrf' => $_SESSION['csrf_perfil']
            ]);
            break;

        default:
            responderJson([
                'status' => 'error',
                'mensaje' => 'Operación no reconocida.'
            ], 400);
    }
} catch (PDOException $e) {
    /* La opción 4 trabaja dentro de una transacción: no se deja abierta si falla. */
    if (isset($conn) && $conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }

    responderJson([
        'status' => 'error',
        'mensaje' => 'Ocurrió un error al consultar la información del usuario.',
        'detalle' => $e->getMessage()
    ], 500);
}
