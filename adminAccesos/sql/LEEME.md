# Scripts SQL — Administración de accesos

Todos los scripts trabajan sobre `ELECTRONICOSDB` y se ejecutan **a mano** desde SSMS
o `sqlcmd`. No hay ejecutor de migraciones. El diagnóstico funciona desde SQL Server
2016; la aplicación necesita **SQL Server 2017 o superior**, porque usa `STRING_AGG`.
La guía completa de instalación y pruebas está en `../INSTALACION.md`.

## Orden de ejecución

| # | Script | Qué hace | Modifica datos | Estado |
|---|---|---|---|---|
| 1 | `01_diagnostico.sql` | Revisa duplicados, relaciones entre aplicaciones distintas, asignaciones huérfanas, excepciones vencidas, usuarios sin acceso, módulos sin `ver`, contraseñas sin hash y claves con caracteres no recomendados. | No (solo lectura) | ✅ Disponible |
| 2 | `02_configuracion_inicial_adm_accesos.sql` | Registrará la aplicación `ADM_ACCESOS`, sus módulos, permisos y el rol `admin_accesos`, y asignará al primer administrador. | Sí (solo inserta, idempotente) | ⏳ Diferido hasta definir los permisos (ver `PENDIENTES.md` §3) |
| — | Migraciones de estructura | Auditoría, columnas de contraseña, auditoría de `usuario_roles`, `permisos.activo`. | Sí (estructura) | ⏸️ En espera de autorización (ver `PENDIENTES.md` §1) |

Regla: **ejecutar siempre `01_diagnostico.sql` antes de cualquier migración** y
revisar sus resultados. Ningún script corrige ni elimina datos existentes por su
cuenta.

## Cómo ejecutar

Desde SSMS: abrir el archivo y ejecutar (F5). Cada bloque devuelve un resultado
con la columna `diagnostico`. **Un resultado vacío significa que no hay problema.**

Desde la línea de comandos, con autenticación de Windows:

```
sqlcmd -S LELEDANIEL -E -f 65001 -W -s "|" -i sql\01_diagnostico.sql
```

## Resultado de referencia (2026-10-01, servidor `LELEDANIEL`)

- Las 8 restricciones UNIQUE/PK esperadas existen.
- Sin duplicados, sin relaciones entre aplicaciones distintas y sin asignaciones
  huérfanas.
- 1 excepción vencida (`T10`, `PC.anormalidades.primera_revision`, 2026-09-24).
- 1 usuario sin roles ni permisos y con contraseña sin hash válido (`reportes`).
- 10 módulos sin la acción `ver`.
