<?php
/**
 * api/consultas/permisosEfectivos.php — Resolución de permisos efectivos.
 *
 * Solo define funciones (no imprime ni lee $_POST). La usan la pantalla de
 * usuarios (pestaña "Permisos efectivos" del detalle) y, en la Etapa 7, la
 * pantalla de permisos efectivos.
 *
 * Regla (autenticacion-y-permisos.md §5 y §6.3, contexto.md módulo 9):
 *   efectivos = (permisos de sus roles activos) ∪ (excepciones "conceder" vigentes)
 *               − (excepciones "denegar" vigentes)
 *   - "denegar" gana sobre "conceder", que gana sobre lo que da el rol.
 *   - Se ignoran roles, módulos y aplicaciones inactivos.
 *   - Un rol solo aporta permisos de módulos de SU aplicación (si por error
 *     tiene permisos de otra aplicación, no cuentan).
 *   - Las excepciones vencidas se ignoran (siguen en la tabla como historial).
 *
 * A diferencia del login de cada aplicación, aquí se resuelven TODAS las
 * aplicaciones a la vez, y además de los permitidos se devuelven los
 * denegados, para mostrar el origen de cada resultado.
 */

/**
 * @return array<int, array{
 *   codigoSistema: string, aplicacion: string, codigoModulo: string,
 *   modulo: string, accion: string, descripcion: ?string,
 *   roles: string[], excepcion: ?string, vigenciaHasta: ?string,
 *   resultado: string, origen: string
 * }>
 */
function consultarPermisosEfectivos(PDO $conn, string $nomina): array
{
    $consulta = $conn->prepare(
        ";WITH DelRol AS
         (
             SELECT rp.idPermiso,
                    r.codigoSistema,
                    r.codigoRol
             FROM usuario_roles AS ur
             INNER JOIN roles AS r
                     ON r.idRol = ur.idRol
                    AND r.activo = 1
             INNER JOIN rol_permisos AS rp
                     ON rp.idRol = r.idRol
             WHERE ur.nominaUsuario = :nominaRol
         ),
         Excepciones AS
         (
             SELECT up.idPermiso,
                    LOWER(LTRIM(RTRIM(up.tipo))) AS tipo,
                    up.vigenciaHasta
             FROM usuario_permisos AS up
             WHERE up.nominaUsuario = :nominaExcepcion
               AND (up.vigenciaHasta IS NULL
                    OR up.vigenciaHasta >= CAST(SYSDATETIME() AS date))
         )
         SELECT a.codigoSistema,
                a.nombre AS aplicacion,
                m.codigoModulo,
                m.nombre AS modulo,
                p.accion,
                p.descripcion,
                rr.roles,
                e.tipo AS excepcion,
                CONVERT(char(10), e.vigenciaHasta, 23) AS vigenciaHasta
         FROM permisos AS p
         INNER JOIN modulos AS m
                 ON m.idModulo = p.idModulo
                AND m.activo = 1
         INNER JOIN aplicaciones AS a
                 ON a.codigoSistema = m.codigoSistema
                AND a.activo = 1
         /* Roles del usuario que aportan este permiso, solo de la misma aplicación. */
         OUTER APPLY
         (
             SELECT STRING_AGG(d.codigoRol, N', ') WITHIN GROUP (ORDER BY d.codigoRol) AS roles
             FROM DelRol AS d
             WHERE d.idPermiso = p.idPermiso
               AND d.codigoSistema = m.codigoSistema
         ) AS rr
         LEFT JOIN Excepciones AS e
                ON e.idPermiso = p.idPermiso
         WHERE rr.roles IS NOT NULL
            OR e.idPermiso IS NOT NULL
         ORDER BY a.codigoSistema, m.orden, m.codigoModulo, p.accion"
    );
    $consulta->execute([
        ':nominaRol'       => $nomina,
        ':nominaExcepcion' => $nomina,
    ]);

    $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
    $consulta->closeCursor();

    $resultado = [];

    foreach ($filas as $fila) {
        $roles = $fila['roles'] !== null && $fila['roles'] !== ''
            ? explode(', ', (string) $fila['roles'])
            : [];
        $excepcion = $fila['excepcion'] !== null ? (string) $fila['excepcion'] : null;

        /* Precedencia fija: denegar > conceder > rol. */
        if ($excepcion === 'denegar') {
            $resultadoPermiso = 'denegado';
            $origen = 'Excepción individual (denegar)';
        } elseif ($excepcion === 'conceder' && count($roles) === 0) {
            $resultadoPermiso = 'permitido';
            $origen = 'Permiso individual';
        } else {
            $resultadoPermiso = 'permitido';
            $origen = (count($roles) === 1 ? 'Rol ' : 'Roles ') . implode(', ', $roles)
                . ($excepcion === 'conceder' ? ' y permiso individual' : '');
        }

        $resultado[] = [
            'codigoSistema' => (string) $fila['codigoSistema'],
            'aplicacion'    => (string) $fila['aplicacion'],
            'codigoModulo'  => (string) $fila['codigoModulo'],
            'modulo'        => (string) $fila['modulo'],
            'accion'        => (string) $fila['accion'],
            'descripcion'   => $fila['descripcion'],
            'roles'         => $roles,
            'excepcion'     => $excepcion,
            'vigenciaHasta' => $fila['vigenciaHasta'],
            'resultado'     => $resultadoPermiso,
            'origen'        => $origen,
        ];
    }

    return $resultado;
}
