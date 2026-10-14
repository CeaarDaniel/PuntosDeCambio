# Pendientes — Administración de accesos

Actividades que **no se pueden implementar todavía** o que requieren una decisión.
La regla vigente (indicada el 2026-10-01) es: **no modificar la estructura de la base de
datos por ahora**. Todo lo que dependa de un cambio de estructura se anota aquí y la
aplicación se construye sin esa parte.

Cuando se resuelva un punto, marcarlo con `[x]`, anotar la fecha y reflejarlo en
`PROGRESO.md`.

---

## 1. Bloqueados por la estructura de la base de datos

Ninguno de estos cambios se ha aplicado. Las propuestas de columnas son orientativas;
la migración real se escribirá cuando se autorice, como script idempotente e
independiente (`IF COL_LENGTH(...) IS NULL ALTER TABLE ... ADD ...`).

- [ ] **Tabla de auditoría (historial de cambios).** No existe ninguna tabla de
  auditoría para el modelo de accesos (la única tabla de historial en
  `ELECTRONICOSDB` es `SPC_HISTORIAL_LAYOUT`, ajena a este modelo).
  - Bloquea: módulo 10 "Auditoría" completo y el registro de quién hizo cada cambio
    (usuarios, aplicaciones, módulos, permisos, roles, asignaciones y excepciones).
  - Propuesta de tabla `auditoria_accesos`: `idAuditoria` (IDENTITY, PK),
    `fecha` (`datetime2(0)` DEFAULT `SYSDATETIME()`), `nominaAdministrador`
    (`nvarchar(15)`), `accion` (`nvarchar(50)`), `entidad` (`nvarchar(50)`),
    `identificador` (`nvarchar(200)`, clave lógica, por ejemplo `SRD.defectos.ver`),
    `valoresAnteriores` (`nvarchar(max)`, JSON), `valoresNuevos` (`nvarchar(max)`, JSON),
    `ip` (`nvarchar(45)`, opcional). Nunca guardar contraseñas ni hashes.
  - **Decidido (2026-10-01):** la auditoría queda pendiente por completo. Mientras no
    exista la tabla no se registra nada, ni en la base ni en logs.
  - **Estado de la pantalla (Etapa 7):** la entrada "Auditoría" del menú muestra un
    aviso de bloqueo (`'aviso'` en `includes/paginas.php`). Para habilitarla:
    1. Crear la tabla (migración independiente e idempotente).
    2. Agregar una función `registrarAuditoria()` y llamarla en cada escritura de los
       endpoints (todos están marcados con "PENDIENTE (PENDIENTES.md §1)").
    3. Crear `pages/auditoria.php`, su JS y su endpoint, con filtros por fecha,
       administrador, entidad y acción.
    4. Quitar `'aviso'` y poner `'disponible' => true`.

- [ ] **Auditoría de asignación de roles en `usuario_roles`.** Solo tiene
  `idUR`, `idRol` y `nominaUsuario`.
  - Bloquea: mostrar cuándo y quién asignó cada rol (módulo 7) y también en la pestaña
    "Roles" del detalle de usuario. La pantalla "Roles de usuario" (Etapa 6) funciona
    sin esa información.
- [ ] **Historial de las excepciones individuales.** `usuario_permisos` guarda una
  sola fila por usuario y permiso:
  - Al **editar** una excepción, `otorgadoPor` y `fechaRegistro` pasan a ser los de
    quien edita, y se pierde quién la otorgó originalmente.
  - Al **retirar** una excepción (se vence ayer) no queda registrado quién la retiró.
  - Al **reconceder** sobre una excepción vencida, se reutiliza su fila y se pierde el
    motivo anterior.

  Todo eso quedará cubierto con la tabla de auditoría (punto anterior). No hace falta
  otra columna.
  - Propuesta: `fechaAsignacion datetime2(0) NOT NULL DEFAULT SYSDATETIME()` y
    `asignadoPor nvarchar(15) NULL`.
  - `usuario_permisos` **sí** tiene `otorgadoPor` y `fechaRegistro`, así que las
    excepciones individuales sí registran quién y cuándo.

- [ ] **Renovación y control de contraseñas en `usuarios`.** No existen columnas para:
  - Obligar a cambiar la contraseña en el siguiente inicio de sesión
    (propuesta: `debeCambiarPassword bit NOT NULL DEFAULT 0`).
  - Fecha del último cambio de contraseña (propuesta: `fechaCambioPassword datetime2(0) NULL`).
  - Fecha de la última modificación del registro (propuesta: `fechaModificacion datetime2(0) NULL`).
  - Bloquea: forzar el cambio en el siguiente login y mostrar la fecha del último
    cambio. **Sí se puede** restablecer y modificar la contraseña desde el módulo
    administrativo (solo se actualiza `passwordHash`).
  - Nota: forzar el cambio también requiere que el login de **cada** aplicación
    respete la marca; eso implica tocar las otras aplicaciones y está fuera del
    alcance actual.

- [ ] **Baja controlada de permisos.** `permisos` no tiene columna `activo`.
  - Bloquea: desactivar un permiso sin borrarlo. Mientras tanto, la aplicación solo
    permitirá eliminar un permiso que **no** tenga relaciones en `rol_permisos` ni en
    `usuario_permisos`, mostrando antes esas relaciones.
  - Propuesta: `activo bit NOT NULL DEFAULT 1` con `CHECK (activo IN (0,1))`, y
    agregar `p.activo = 1` a la consulta de resolución de **todas** las aplicaciones.

- [ ] **`usuarios` no tiene PRIMARY KEY**, solo la restricción
  `UQ__usuarios__EF82F1D6A79F9289` sobre `nominaUsuario` (las FK apuntan a ella).
  Recomendado promoverla a PK (lo sugiere también `autenticacion-y-permisos.md`).
  No bloquea nada.

- [ ] **`usuarios.estatus` no tiene restricción CHECK.** Hoy todos los valores son
  `'activo'`. La aplicación solo escribirá `'activo'` o `'inactivo'`.
  Propuesta: `CHECK (estatus IN (N'activo', N'inactivo'))`. No bloquea nada.

- [ ] **`usuario_permisos.otorgadoPor` no tiene llave foránea** hacia `usuarios`.
  Hoy todos los valores son válidos. Opcional. No bloquea nada.

---

## 2. Datos existentes a revisar (diagnóstico del 2026-10-01, no se corrigió nada)

- [ ] **Usuario `reportes`:** su `passwordHash` no tiene formato bcrypt/argon2
  (posible contraseña en texto plano) y no tiene roles ni permisos. Con
  `password_verify()` no puede iniciar sesión en ninguna aplicación. Sugerencia:
  restablecer su contraseña desde el módulo de usuarios cuando exista, o desactivarlo
  si ya no se usa.
- [ ] **10 de 11 módulos no tienen la acción `ver`** (todos los de `PC` excepto
  `anormalidades`, y `SRD.defectos`). Según la convención de
  `autenticacion-y-permisos.md`, un módulo sin `ver` queda inalcanzable. Se mostrará
  como inconsistencia en el dashboard; corregirlo es decisión del dueño de cada
  aplicación.
- [ ] **⚠️ Contraseñas escritas en los comentarios (hallazgo del 2026-10-01).**
  Los usuarios de prueba `T01` a `T09` tienen en `usuarios.comentarios` el texto
  "Contraseña: Prueba123". Los comentarios se muestran en la pantalla de usuarios, así
  que cualquiera que la abra ve esa contraseña. Sugerencia:
  - quitar ese texto con **Usuarios › Editar**;
  - restablecer la contraseña de esos usuarios si se van a seguir usando.

  No se corrigió automáticamente.
- [ ] **1 excepción individual vencida** (usuario `T10`, venció el 2026-09-24). Se
  conserva como historial; solo se reporta.
- [ ] **Códigos de módulo en camelCase** (`gestionLinea`, `menuLineas`, …). No es un
  error, pero obliga a que la validación de `codigoModulo` acepte mayúsculas
  (`^[A-Za-z][A-Za-z0-9_]*$`). Para `accion` y `codigoRol` se mantiene minúsculas,
  dígitos y guion bajo, que es lo que ya cumplen todos los datos actuales.
- [ ] **`ADM_ACCESOS` no está registrada.** Por ahora no importa, porque el login
  no exige acceso a la aplicación (ver sección 3). Se registrará con el script de
  configuración inicial cuando se definan los permisos. Ese script **inserta datos**,
  no cambia la estructura.

---

## 3. Permisos propios de la aplicación (diferido al final por decisión del usuario)

- [ ] **⚠️ CRÍTICO: la aplicación no valida permisos todavía.** Decidido el
  2026-10-01: primero se construye la parte funcional y al final se definen los
  permisos de `ADM_ACCESOS`. Mientras tanto, **cualquier usuario con estatus `activo`
  y contraseña válida puede entrar y administrar todo**. No desplegar en producción
  en este estado.
  - Hoy cada vista y endpoint solo exige sesión (`exigirSesion()`). Al definir los
    permisos hay que agregar `exigirAcceso()` en cada vista y `exigirPermiso()` en cada
    `case` de cada endpoint, y en el login rechazar a quien no tenga acceso a
    `ADM_ACCESOS`.
  - Falta el script `sql/02_configuracion_inicial_adm_accesos.sql`: registra la
    aplicación, sus módulos, permisos y el rol `admin_accesos`, y asigna al primer
    administrador. Debe ser idempotente y no usar IDs fijos.
  - Propuesta de matriz de permisos: ver `PROGRESO.md`, sección "Decisiones de
    diseño", punto 5.
  - La regla "evitar que se desactive al último administrador activo" depende de saber
    quién es administrador (rol `admin_accesos`). Se implementará junto con los
    permisos.

## 4. Limitaciones conocidas y mejoras sugeridas (no bloquean)

- [ ] **Probar el login con un usuario real.** Claude no tiene contraseñas, así que el
  flujo completo se probó con una sesión simulada. Falta que alguien entre con su nómina
  y su contraseña en `http://localhost:8080/adminAccesos/`.
- [ ] **Probar en la interfaz las escrituras de Usuarios.** Para no dejar datos de
  prueba en la tabla compartida `usuarios`, Claude probó el SQL dentro de una
  transacción que se revirtió. Faltan estas pruebas manuales desde la pantalla:
  1. Crear un usuario de prueba y entrar con él.
  2. Editar su nombre o sus comentarios.
  3. Desactivarlo y comprobar que ya no puede entrar.
  4. Reactivarlo.
  5. Restablecer su contraseña y comprobar que la anterior deja de funcionar.

  Como no hay eliminación de usuarios, el de prueba se queda inactivo al terminar.
- [ ] **Los cambios de roles y permisos surten efecto al volver a iniciar sesión.**
  Cada aplicación guarda los permisos en la sesión al entrar
  (`autenticacion-y-permisos.md` §9). Las pantallas de catálogos lo avisan en sus
  mensajes. Es una limitación del modelo, no de esta aplicación.
- [ ] **Los códigos existentes deben cumplir los patrones para poder administrarse.**
  Los endpoints validan `codigoRol` y `accion` en minúsculas y `codigoSistema` en
  mayúsculas. Hoy **todos** los datos cumplen (verificado con
  `sql/01_diagnostico.sql`, revisión 15). Si otra aplicación registrara directamente en
  la base un código fuera de patrón, esta aplicación no podría editarlo; habría que
  ajustar el dato o el patrón en `includes/configuracion.php`.
- [ ] **Eliminar módulos sin permisos.** Los 9 módulos de `PC` sin permisos se
  pueden eliminar desde la pantalla (lo permite la regla "sin relaciones"). Si
  Puntos de Cambio arma su menú con la tabla `modulos`, eliminar uno lo quitaría de su
  menú. La confirmación lo advierte y sugiere desactivar. Conviene decidir con el dueño
  de `PC` si esos módulos deben tener su acción `ver`.
- [ ] **En celular, las acciones de cada fila quedan en la vista compacta.** DataTables
  Responsive solo muestra el nombre; las acciones aparecen al tocar ▶. Es el mismo
  comportamiento que en `defectos`. Si se prefiere, se pueden dejar siempre visibles.
- [ ] **La sesión no se revalida en cada petición.** Si se desactiva a un usuario que
  tiene la sesión abierta, sigue dentro hasta que cierre sesión o la sesión expire.
  Es la misma limitación que describe `autenticacion-y-permisos.md` §9. Opción: consultar
  `usuarios.estatus` en `prepararEndpoint()` y en `exigirSesionVista()` (una consulta
  por petición).
- [ ] **Sin límite de intentos de inicio de sesión.** No hay bloqueo temporal tras
  varios intentos fallidos. Para guardarlo de forma persistente se necesita una tabla,
  así que depende de la regla de no modificar la estructura.
- [ ] **Iconos PNG para instalar la app** (`img/icon-192.png` e `img/icon-512.png`,
  como en `defectos`). Hoy `manifest.json` usa `img/favicon.svg`, que funciona en Edge
  y Chrome.

## 5. Hallazgos de seguridad de la revisión final (Etapa 8)

- [x] **Archivos internos expuestos por web** (documentación `.md`, `sql/`,
  `includes/`, archivos de conexión y listado de carpetas). Se corrigió con `.htaccess`
  dentro de la aplicación y se verificó con respuestas 403.
- [ ] **Quitar `api/conexionEmpleado.php`.** Venía en la estructura inicial, esta
  aplicación no lo usa y tiene credenciales `sa` de `SAPDB` en texto plano. Ya está
  bloqueado por web, pero lo más seguro es eliminarlo. **No se borró: es decisión del
  usuario.**
- [ ] **Las otras aplicaciones del servidor tienen la misma exposición.** En
  `defectos` y `PuntosDeCambio`, por ejemplo, los `.sql`, `.md` y `CLAUDE.md` se
  pueden descargar por web, y Apache lista sus carpetas (`Options Indexes` en
  `httpd.conf`). No se tocó nada fuera de `adminAccesos` (regla de `contexto.md`).
  Se puede aplicar el mismo tipo de `.htaccess`, o quitar `Indexes` en `httpd.conf`.
  Verificado el 2026-10-01 con peticiones de solo lectura: `defectos/SRD_defectos.sql`,
  `defectos/CLAUDE.md`, `PuntosDeCambio/BDPuntosDeCambio.sql` y
  `PuntosDeCambio/HASH-LOGIN PHP.txt` responden 200, y `defectos/api/` muestra
  "Index of".
- [ ] **⚠️ Contraseña en texto plano descargable por web.**
  `PuntosDeCambio/BDPuntosDeCambio.sql` contiene, en un `INSERT` de `usuarios`, una
  contraseña en texto plano del usuario **11607**. Hoy ese usuario tiene un hash
  bcrypt en la base, así que probablemente es una contraseña anterior, pero no se puede
  confirmar.
  - Si alguna vez fue o es su contraseña real (en esta u otra aplicación),
    **cambiarla**.
  - Proteger o quitar ese archivo del sitio web.

  No se tocó porque está fuera de `adminAccesos`.

## 6. Decisiones tomadas (2026-10-01)

- [x] Auditoría: pendiente, no se registra nada mientras no exista la tabla.
- [x] Permisos de la aplicación: se definen al final (ver sección 3).
- [x] Longitud mínima de contraseña: 8 caracteres.
- [x] Login: campo de texto para la nómina (no se muestra lista de usuarios).
- [x] CSS propio en `css/` en la raíz, igual que `defectos`.
