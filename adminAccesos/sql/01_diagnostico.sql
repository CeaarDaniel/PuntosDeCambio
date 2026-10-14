/* ==========================================================================
   01_diagnostico.sql — Diagnóstico del modelo de usuarios, roles y permisos
   ==========================================================================

   SOLO LECTURA. No inserta, no modifica y no elimina nada. Se puede ejecutar
   cuantas veces se quiera, en cualquier base que tenga el modelo.

   Cada bloque devuelve un resultado con la columna "diagnostico" para saber de
   qué revisión se trata. Un resultado VACÍO significa que no hay problema.

   Algunas revisiones (duplicados, usuarios inexistentes) ya están protegidas
   por restricciones UNIQUE y llaves foráneas en ELECTRONICOSDB; se dejan
   porque el script también sirve para otras instalaciones donde esas
   restricciones podrían faltar.

   Nunca se selecciona passwordHash: solo se indica si su formato es válido.
   ========================================================================== */

USE [ELECTRONICOSDB];
GO

SET NOCOUNT ON;

DECLARE @hoy date = CAST(SYSDATETIME() AS date);

/* --------------------------------------------------------------------------
   0. Restricciones que el modelo necesita. Debe haber una fila por cada una;
      si alguna falta, aparece con existe = 0.
   -------------------------------------------------------------------------- */
SELECT N'0. Restricciones esperadas' AS diagnostico,
       e.tabla,
       e.columnas,
       CASE WHEN EXISTS
       (
           SELECT 1
           FROM sys.indexes AS i
           INNER JOIN sys.tables AS t ON t.object_id = i.object_id
           WHERE t.name = e.tabla
             AND (i.is_unique = 1 OR i.is_primary_key = 1)
             AND STUFF((SELECT N', ' + c.name
                        FROM sys.index_columns AS ic
                        INNER JOIN sys.columns AS c
                                ON c.object_id = ic.object_id
                               AND c.column_id = ic.column_id
                        WHERE ic.object_id = i.object_id
                          AND ic.index_id = i.index_id
                          AND ic.is_included_column = 0
                        ORDER BY ic.key_ordinal
                        FOR XML PATH('')), 1, 2, N'') = e.columnas
       ) THEN 1 ELSE 0 END AS existe
FROM (VALUES
    (N'usuarios',         N'nominaUsuario'),
    (N'aplicaciones',     N'codigoSistema'),
    (N'modulos',          N'codigoSistema, codigoModulo'),
    (N'permisos',         N'idModulo, accion'),
    (N'roles',            N'codigoSistema, codigoRol'),
    (N'rol_permisos',     N'idRol, idPermiso'),
    (N'usuario_roles',    N'nominaUsuario, idRol'),
    (N'usuario_permisos', N'nominaUsuario, idPermiso')
) AS e(tabla, columnas)
ORDER BY existe, e.tabla;

/* --------------------------------------------------------------------------
   1. Códigos de aplicación duplicados (ignorando espacios al inicio o final).
   -------------------------------------------------------------------------- */
SELECT N'1. Aplicaciones duplicadas' AS diagnostico,
       LTRIM(RTRIM(codigoSistema)) AS codigoSistema,
       COUNT(*) AS veces
FROM dbo.aplicaciones
GROUP BY LTRIM(RTRIM(codigoSistema))
HAVING COUNT(*) > 1;

/* --------------------------------------------------------------------------
   2. Módulos duplicados dentro de una aplicación.
   -------------------------------------------------------------------------- */
SELECT N'2. Módulos duplicados' AS diagnostico,
       LTRIM(RTRIM(codigoSistema)) AS codigoSistema,
       LTRIM(RTRIM(codigoModulo)) AS codigoModulo,
       COUNT(*) AS veces
FROM dbo.modulos
GROUP BY LTRIM(RTRIM(codigoSistema)), LTRIM(RTRIM(codigoModulo))
HAVING COUNT(*) > 1;

/* --------------------------------------------------------------------------
   3. Acciones duplicadas dentro de un módulo.
   -------------------------------------------------------------------------- */
SELECT N'3. Acciones duplicadas' AS diagnostico,
       m.codigoSistema,
       m.codigoModulo,
       LTRIM(RTRIM(p.accion)) AS accion,
       COUNT(*) AS veces
FROM dbo.permisos AS p
INNER JOIN dbo.modulos AS m ON m.idModulo = p.idModulo
GROUP BY m.codigoSistema, m.codigoModulo, LTRIM(RTRIM(p.accion))
HAVING COUNT(*) > 1;

/* --------------------------------------------------------------------------
   4. Roles duplicados dentro de una aplicación.
   -------------------------------------------------------------------------- */
SELECT N'4. Roles duplicados' AS diagnostico,
       LTRIM(RTRIM(codigoSistema)) AS codigoSistema,
       LTRIM(RTRIM(codigoRol)) AS codigoRol,
       COUNT(*) AS veces
FROM dbo.roles
GROUP BY LTRIM(RTRIM(codigoSistema)), LTRIM(RTRIM(codigoRol))
HAVING COUNT(*) > 1;

/* --------------------------------------------------------------------------
   5. Relaciones duplicadas.
   -------------------------------------------------------------------------- */
SELECT N'5a. rol_permisos duplicados' AS diagnostico,
       r.codigoSistema, r.codigoRol, m.codigoModulo, p.accion, COUNT(*) AS veces
FROM dbo.rol_permisos AS rp
INNER JOIN dbo.roles AS r    ON r.idRol = rp.idRol
INNER JOIN dbo.permisos AS p ON p.idPermiso = rp.idPermiso
INNER JOIN dbo.modulos AS m  ON m.idModulo = p.idModulo
GROUP BY r.codigoSistema, r.codigoRol, m.codigoModulo, p.accion
HAVING COUNT(*) > 1;

SELECT N'5b. usuario_roles duplicados' AS diagnostico,
       ur.nominaUsuario, r.codigoSistema, r.codigoRol, COUNT(*) AS veces
FROM dbo.usuario_roles AS ur
INNER JOIN dbo.roles AS r ON r.idRol = ur.idRol
GROUP BY ur.nominaUsuario, r.codigoSistema, r.codigoRol
HAVING COUNT(*) > 1;

SELECT N'5c. usuario_permisos duplicados' AS diagnostico,
       up.nominaUsuario, m.codigoSistema, m.codigoModulo, p.accion, COUNT(*) AS veces
FROM dbo.usuario_permisos AS up
INNER JOIN dbo.permisos AS p ON p.idPermiso = up.idPermiso
INNER JOIN dbo.modulos AS m  ON m.idModulo = p.idModulo
GROUP BY up.nominaUsuario, m.codigoSistema, m.codigoModulo, p.accion
HAVING COUNT(*) > 1;

/* --------------------------------------------------------------------------
   6. Roles relacionados con permisos de otra aplicación. Un rol solo debe
      tener permisos de módulos de su propia aplicación.
   -------------------------------------------------------------------------- */
SELECT N'6. Rol con permiso de otra aplicación' AS diagnostico,
       r.codigoSistema AS sistemaRol,
       r.codigoRol,
       m.codigoSistema AS sistemaPermiso,
       m.codigoModulo,
       p.accion
FROM dbo.rol_permisos AS rp
INNER JOIN dbo.roles AS r    ON r.idRol = rp.idRol
INNER JOIN dbo.permisos AS p ON p.idPermiso = rp.idPermiso
INNER JOIN dbo.modulos AS m  ON m.idModulo = p.idModulo
WHERE r.codigoSistema <> m.codigoSistema;

/* --------------------------------------------------------------------------
   7. Asignaciones hacia usuarios inexistentes.
   -------------------------------------------------------------------------- */
SELECT N'7a. usuario_roles sin usuario' AS diagnostico, ur.nominaUsuario
FROM dbo.usuario_roles AS ur
WHERE NOT EXISTS (SELECT 1 FROM dbo.usuarios AS u WHERE u.nominaUsuario = ur.nominaUsuario);

SELECT N'7b. usuario_permisos sin usuario' AS diagnostico, up.nominaUsuario
FROM dbo.usuario_permisos AS up
WHERE NOT EXISTS (SELECT 1 FROM dbo.usuarios AS u WHERE u.nominaUsuario = up.nominaUsuario);

SELECT N'7c. otorgadoPor sin usuario' AS diagnostico, up.nominaUsuario, up.otorgadoPor
FROM dbo.usuario_permisos AS up
WHERE up.otorgadoPor IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM dbo.usuarios AS u WHERE u.nominaUsuario = up.otorgadoPor);

/* --------------------------------------------------------------------------
   8. Permisos individuales vencidos. Informativo: se conservan como
      historial y la consulta de resolución ya los ignora.
   -------------------------------------------------------------------------- */
SELECT N'8. Excepción vencida' AS diagnostico,
       up.nominaUsuario, m.codigoSistema, m.codigoModulo, p.accion,
       up.tipo, up.vigenciaHasta, up.motivo
FROM dbo.usuario_permisos AS up
INNER JOIN dbo.permisos AS p ON p.idPermiso = up.idPermiso
INNER JOIN dbo.modulos AS m  ON m.idModulo = p.idModulo
WHERE up.vigenciaHasta < @hoy
ORDER BY up.vigenciaHasta;

/* --------------------------------------------------------------------------
   9. Usuarios sin roles ni permisos individuales.
   -------------------------------------------------------------------------- */
SELECT N'9. Usuario sin roles ni permisos' AS diagnostico,
       u.nominaUsuario, u.nombre, u.estatus
FROM dbo.usuarios AS u
WHERE NOT EXISTS (SELECT 1 FROM dbo.usuario_roles AS ur WHERE ur.nominaUsuario = u.nominaUsuario)
  AND NOT EXISTS (SELECT 1 FROM dbo.usuario_permisos AS up WHERE up.nominaUsuario = u.nominaUsuario);

/* --------------------------------------------------------------------------
   10. Usuarios inactivos con asignaciones vigentes (roles o excepciones sin
       vencer). No dan acceso porque el login rechaza al usuario inactivo,
       pero conviene revisarlas.
   -------------------------------------------------------------------------- */
SELECT N'10. Usuario inactivo con asignaciones' AS diagnostico,
       u.nominaUsuario, u.nombre, u.estatus,
       (SELECT COUNT(*) FROM dbo.usuario_roles AS ur
         WHERE ur.nominaUsuario = u.nominaUsuario) AS roles,
       (SELECT COUNT(*) FROM dbo.usuario_permisos AS up
         WHERE up.nominaUsuario = u.nominaUsuario
           AND (up.vigenciaHasta IS NULL OR up.vigenciaHasta >= @hoy)) AS excepcionesVigentes
FROM dbo.usuarios AS u
WHERE LOWER(LTRIM(RTRIM(u.estatus))) <> N'activo'
  AND (EXISTS (SELECT 1 FROM dbo.usuario_roles AS ur WHERE ur.nominaUsuario = u.nominaUsuario)
       OR EXISTS (SELECT 1 FROM dbo.usuario_permisos AS up
                  WHERE up.nominaUsuario = u.nominaUsuario
                    AND (up.vigenciaHasta IS NULL OR up.vigenciaHasta >= @hoy)));

/* --------------------------------------------------------------------------
   11. Módulos sin la acción "ver". Por convención es el permiso de entrada;
       sin él, el módulo queda inalcanzable.
   -------------------------------------------------------------------------- */
SELECT N'11. Módulo sin acción ver' AS diagnostico,
       m.codigoSistema, m.codigoModulo, m.nombre
FROM dbo.modulos AS m
WHERE NOT EXISTS (SELECT 1 FROM dbo.permisos AS p
                  WHERE p.idModulo = m.idModulo AND p.accion = N'ver')
ORDER BY m.codigoSistema, m.codigoModulo;

/* --------------------------------------------------------------------------
   12. Usuarios cuyo passwordHash no tiene formato bcrypt ni argon2
       (posible contraseña en texto plano). No pueden iniciar sesión con
       password_verify(). Solo se muestra la nómina.
   -------------------------------------------------------------------------- */
SELECT N'12. Contraseña sin hash válido' AS diagnostico,
       u.nominaUsuario, u.nombre, u.estatus
FROM dbo.usuarios AS u
WHERE u.passwordHash NOT LIKE N'$2y$%'
  AND u.passwordHash NOT LIKE N'$argon2%';

/* --------------------------------------------------------------------------
   13. Estatus de usuario fuera de los valores esperados ('activo', 'inactivo').
   -------------------------------------------------------------------------- */
SELECT N'13. Estatus no reconocido' AS diagnostico,
       u.nominaUsuario, N'[' + u.estatus + N']' AS estatus
FROM dbo.usuarios AS u
WHERE u.estatus COLLATE Latin1_General_BIN NOT IN (N'activo', N'inactivo');

/* --------------------------------------------------------------------------
   14. Asignaciones que no aportan nada porque apuntan a elementos inactivos:
       usuarios con roles inactivos, y excepciones sobre módulos o
       aplicaciones inactivas. Informativo.
   -------------------------------------------------------------------------- */
SELECT N'14a. Usuario con rol inactivo' AS diagnostico,
       ur.nominaUsuario, r.codigoSistema, r.codigoRol
FROM dbo.usuario_roles AS ur
INNER JOIN dbo.roles AS r ON r.idRol = ur.idRol
INNER JOIN dbo.aplicaciones AS a ON a.codigoSistema = r.codigoSistema
WHERE r.activo = 0 OR a.activo = 0;

SELECT N'14b. Excepción sobre módulo o aplicación inactiva' AS diagnostico,
       up.nominaUsuario, m.codigoSistema, m.codigoModulo, p.accion, up.tipo
FROM dbo.usuario_permisos AS up
INNER JOIN dbo.permisos AS p     ON p.idPermiso = up.idPermiso
INNER JOIN dbo.modulos AS m      ON m.idModulo = p.idModulo
INNER JOIN dbo.aplicaciones AS a ON a.codigoSistema = m.codigoSistema
WHERE m.activo = 0 OR a.activo = 0;

/* --------------------------------------------------------------------------
   15. Claves con caracteres no recomendados (espacios, acentos, símbolos).
       codigoModulo admite camelCase porque así están los datos existentes.
   -------------------------------------------------------------------------- */
SELECT N'15. Clave con caracteres no recomendados' AS diagnostico,
       x.tipo, x.codigoSistema, x.clave
FROM
(
    SELECT N'aplicacion' AS tipo, codigoSistema, codigoSistema AS clave
    FROM dbo.aplicaciones
    WHERE codigoSistema COLLATE Latin1_General_BIN LIKE N'%[^A-Z0-9_]%'
    UNION ALL
    SELECT N'modulo', codigoSistema, codigoModulo
    FROM dbo.modulos
    WHERE codigoModulo COLLATE Latin1_General_BIN LIKE N'%[^A-Za-z0-9_]%'
    UNION ALL
    SELECT N'rol', codigoSistema, codigoRol
    FROM dbo.roles
    WHERE codigoRol COLLATE Latin1_General_BIN LIKE N'%[^a-z0-9_]%'
    UNION ALL
    SELECT N'accion', m.codigoSistema, m.codigoModulo + N'.' + p.accion
    FROM dbo.permisos AS p
    INNER JOIN dbo.modulos AS m ON m.idModulo = p.idModulo
    WHERE p.accion COLLATE Latin1_General_BIN LIKE N'%[^a-z0-9_]%'
) AS x
ORDER BY x.tipo, x.codigoSistema, x.clave;
GO
