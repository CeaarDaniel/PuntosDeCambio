# Progreso del desarrollo — Administración de accesos

Bitácora para retomar el trabajo sin perder contexto, aunque cambie la persona, la
instancia o la herramienta de IA. **Actualizar este archivo al terminar cada paso.**

## Cómo retomar

Leer en este orden:

1. `contexto.md`: el requerimiento completo, las etapas y los criterios de aceptación.
2. `autenticacion-y-permisos.md`: el modelo de usuarios, roles y permisos, y por qué
   está construido así.
3. `PENDIENTES.md`: lo bloqueado por la base de datos y las decisiones abiertas.
4. Este archivo: dónde va el desarrollo y qué sigue.

Reglas vigentes que no están en `contexto.md`:

- **No modificar la estructura de la base de datos** hasta nuevo aviso (indicado el
  2026-10-01). Lo que dependa de eso se anota en `PENDIENTES.md`.
- El proyecto se construye en `C:\xampp\htdocs\adminAccesos` (no en
  `usuariosRolesPermisos`, como dice `contexto.md`).
- Proyecto de referencia para estilo, layout y autenticación: `C:\xampp\htdocs\defectos`
  (ver su `CLAUDE.md`, `includes/sesion.php` y `api/autenticacion.php`).

---

## Estado actual

| | |
|---|---|
| **Último paso completado** | Etapa 8: **revisión final**. Incluye revisión estática de endpoints, SQL y escape; pruebas de seguridad por HTTP; `.htaccess` de protección; recorrido de todas las pantallas a 1400 y 390 px; y la guía **`INSTALACION.md`** (2026-10-01). |
| **Siguiente paso** | Definir los **permisos de `ADM_ACCESOS`**, que el usuario pidió dejar para el final (`PENDIENTES.md` §3). Comprende: `sql/02_configuracion_inicial_adm_accesos.sql` (idempotente, sin IDs fijos, con la nómina del primer administrador como variable); `tienePermiso`, `exigirAcceso` y `exigirPermiso` en `includes/sesion.php`; resolución de permisos en el login; guardas en `index.php`, en cada `case` de los endpoints y en el menú; y la regla "no desactivar al último administrador". **Esperar la confirmación del usuario y su revisión de la matriz** (sección "Decisiones de diseño", punto 5). |
| **Bloqueos** | Ver `PENDIENTES.md`. Lo principal: no hay tabla de auditoría ni columnas para forzar el cambio de contraseña, y la validación de permisos se dejó para el final. |

### Etapas

| Etapa | Contenido | Estado |
|---|---|---|
| 1 | Análisis de estructura, tablas, restricciones e índices | ✅ Completada |
| 2 | Script de diagnóstico (`sql/01_diagnostico.sql`). La configuración inicial de `ADM_ACCESOS` y las migraciones se difieren | ✅ Completada |
| 3 | Autenticación, sesión, layout, navegación y dashboard (la autorización por permisos se difiere) | ✅ Completada |
| 4 | Usuarios y administración de contraseñas | ✅ Completada |
| 5 | Aplicaciones, módulos, permisos y roles | ✅ Completada |
| 6 | Asignación de roles y permisos individuales | ✅ Completada |
| 7 | Permisos efectivos ✅ · Auditoría ⏸️ (bloqueada por la BD; aviso propio en la pantalla) | ✅ Completada |
| 8 | Validación de sintaxis, revisión de SQL y guía de instalación y pruebas | ✅ Completada |
| — | Permisos propios de `ADM_ACCESOS` (definidos al final, por decisión del usuario) | ⏳ Siguiente (falta confirmación del usuario) |

---

## Estructura actual del proyecto (al terminar la Etapa 8)

```
INSTALACION.md            Guía de instalación, scripts SQL y pruebas manuales (Etapa 8).
.htaccess                 Sin listado de carpetas; bloquea .md/.sql/.txt/.log por web.
                          También hay .htaccess "Require all denied" en includes/, pages/,
                          api/consultas/ y sql/, y api/.htaccess bloquea conexion*.php.
index.php                 Enrutador único: index.php?p=<clave>. Exige sesión.
login.php / logout.php    Acceso (nómina + contraseña) y cierre (POST + CSRF).
manifest.json             Datos de instalación como app (icono img/favicon.svg).
api/
  conexion.php            PDO a ELECTRONICOSDB. Si falla: error_log + RuntimeException
                          (antes hacía die() con el mensaje interno; se cambió).
                          OJO: define $usuario y $contraseña; incluirla ANTES de usar
                          variables con esos nombres.
  conexionEmpleado.php    Conexión a SAPDB (no se usa).
  autenticacion.php       opcion 1 = iniciar sesión.
  operacionesDashboard.php  opcion 1 = resumen completo del dashboard.
  operacionesUsuarios.php   1 listar · 2 detalle · 3 crear · 4 modificar ·
                            5 activar/desactivar · 6 restablecer contraseña.
  consultas/dashboard.php Funciones con el SQL del dashboard (separado del endpoint).
  consultas/usuarios.php  SQL de usuarios. Nunca devuelve passwordHash (solo
                          passwordValida = formato bcrypt/argon2).
  consultas/permisosEfectivos.php  consultarPermisosEfectivos($conn, $nomina):
                          resolución de TODAS las aplicaciones con resultado
                          (permitido/denegado) y origen. La reutiliza la Etapa 7.
                          Verificada contra la consulta del login de defectos.
  operacionesAplicaciones.php  1 listar · 2 detalle · 3 crear · 4 modificar ·
                               5 activar/desactivar · 6 eliminar (sin relaciones).
  operacionesModulos.php       ídem (3 crea opcionalmente la acción "ver").
  operacionesPermisos.php      1 listar · 2 relaciones · 3 crear · 4 descripción · 5 eliminar.
  operacionesRoles.php         1–6 como los demás · 7 lista de checks · 8 guardar permisos
                               (JSON de claves 'modulo.accion', se aplica la diferencia).
  consultas/catalogos.php  ÚNICO lugar que convierte claves estables en IDs internos:
                          obtenerAplicacion(), obtenerModulo(), obtenerPermiso(),
                          obtenerRol() (con $bloquear = UPDLOCK), catálogos para los
                          selectores y sinIdsInternos() antes de responder.
  consultas/aplicaciones.php, modulos.php, permisos.php, roles.php  SQL de cada catálogo.
  operacionesRolesUsuario.php  1 catálogos · 2 roles de una aplicación para el usuario +
                               resumen · 3 guardar (JSON de codigoRol; diferencia).
  operacionesPermisosUsuario.php  1 catálogos · 2 excepciones del usuario + porRol ·
                               3 crear · 4 modificar · 5 retirar (vence ayer) ·
                               6 eliminar (solo vencidas).
  consultas/asignaciones.php  SQL de usuario_roles y usuario_permisos (mapaRolesDeAplicacion(),
                          obtenerExcepcion(), insertar/actualizar/vencer/eliminarExcepcion()).
  operacionesPermisosEfectivos.php  1 usuarios · 2 resolución completa (permisos +
                          vencidas + roles + resumen por aplicación). Solo lectura.
includes/
  configuracion.php       APP_NOMBRE, CODIGO_SISTEMA, longitudes, DIAS_VIGENCIA_PROXIMA y
                          PATRON_* de las claves estables (un solo lugar; patronClave()
                          les agrega las anclas para PHP; sin anclas van al atributo
                          pattern de los formularios).
  sesion.php              Sesión ADMACCSESSID, CSRF, nominaEnSesion(), exigirSesionVista(),
                          cerrarSesion(), escapar().
  api.php                 prepararEndpoint(), responderOk/Error/Json(),
                          responderFalloInterno(), obtenerTextoPost(), textoEsValido(),
                          obtenerTextoObligatorio/Opcional(), obtenerClavePost(),
                          obtenerBanderaPost(), obtenerEnteroPost(), esViolacionUnica()
                          (2601/2627), esViolacionReferencia() (547), opcionParaLog().
  paginas.php             Catálogo de pantallas ($PAGINAS, $GRUPOS_MENU) y urlPagina().
  header.php, navbar.php, footer.php, toast.php, favicon.php, avisoPagina.php
  modalConfirmar.php      Confirmación genérica (incluida en index.php); se abre con App.confirmar().
  selectorUsuario.php     Buscador de usuario (nómina o nombre, sin acentos); se conecta con
                          App.selectorUsuario(). Escribe ?nomina= en la URL al elegir.
pages/dashboard.php       Vista (solo marcado; se protege con la constante EN_APLICACION).
pages/usuarios.php        Tabla + filtro por estatus + modales de alta/edición, contraseña y detalle.
pages/aplicaciones.php, modulos.php, permisos.php, roles.php  Catálogos (Etapa 5).
pages/rolesUsuario.php, permisosUsuario.php  Asignaciones (Etapa 6).
pages/permisosEfectivos.php  Resultado final por usuario (Etapa 7).
                          (auditoria no tiene vista: index.php muestra el 'aviso' de
                          includes/paginas.php mientras falte la tabla.)
css/style.css             General (copiado de defectos y adaptado; sección 10d = catálogos,
                          selector de usuario y filtro de píldoras).
                          css/login.css, dashboard.css, usuarios.css, roles.css,
                          rolesUsuario.css, permisosUsuario.css, permisosEfectivos.css.
assets/js/app.js          App.api(), App.confirmar(), App.ui (clave, badgeActivo, cargador,
                          vacio, botonOcupado, opcionesAplicaciones), App.parametroUrl(),
                          App.selectorUsuario(), App.normalizarTexto(), toasts, escaparHtml,
                          formatoFecha, TEXTOS_TABLA, CSRF.
assets/js/login.js, dashboard.js, usuarios.js, aplicaciones.js, modulos.js, permisos.js, roles.js,
          rolesUsuario.js, permisosUsuario.js, permisosEfectivos.js
sql/01_diagnostico.sql    Diagnóstico de solo lectura. sql/LEEME.md: orden de ejecución.
```

### Convenciones para las siguientes pantallas

- **Pantalla nueva:**
  1. Agregar su entrada en `includes/paginas.php`.
  2. Crear `pages/<clave>.php`, que empieza con la guarda `if (!defined('EN_APLICACION'))`.
  3. Crear `assets/js/<clave>.js` (IIFE con `'use strict'`) y, si hace falta, `css/<clave>.css`.
  4. Subir `'version'` en el catálogo cada vez que cambie su CSS o JS.
- **Endpoint nuevo:**
  1. `require_once includes/api.php` y luego `prepararEndpoint()`.
  2. `require conexion.php` dentro del `try`.
  3. `switch` sobre `opcion`: `'1'`, `'2'`, ….
  4. Si algo falla, `responderFalloInterno()`.
  5. El SQL va en `api/consultas/<dominio>.php`.
- **Escrituras:** se sigue el orden de `defectos`: `beginTransaction`, validar todo,
  escribir y `commit`.
- **Front:** se usa `App.api(url, datos).done(fn(data)).fail(fn(mensaje))`. El
  resultado se avisa con un toast. El progreso se muestra junto al botón que inició la
  operación, no en un toast.
- **Etiquetas de estado** (en `style.css`): `badge-activo`, `badge-inactivo`,
  `badge-conceder`, `badge-denegar`, `badge-vencido`, `badge-por-vencer`,
  `badge-vigente` y `badge-permanente`, siempre con icono. Las claves estables se
  muestran con `<span class="clave">`.
- **Tablas con DataTables:** van dentro de una `.card.tabla-contenida`, con
  `language: App.TEXTOS_TABLA` y `responsive: true`. En `render()`, fuera del tipo
  `'display'` se devuelve el texto plano. No se usa `text-center` en las columnas
  numéricas, porque DataTables ya las alinea a la derecha. Las acciones de cada fila se
  delegan en la tabla, para que funcionen también en la vista compacta de Responsive.
- **Confirmaciones:**
  - Una acción sensible lanzada desde la página usa `App.confirmar({...}).done(...)`.
  - Dentro de un modal **no** se abre otro modal, porque Bootstrap no los apila. La
    confirmación se hace como un segundo paso del mismo modal; el modelo es
    `#modalContrasena` en `pages/usuarios.php`.
- **Errores en modales:** los errores del servidor se muestran dentro del modal (en
  una alerta). El éxito se avisa con un toast al cerrarlo.
- **Atributos `data-`:** se leen con `.attr('data-…')` y no con `.data()`, porque
  `.data()` convierte a número las nóminas o claves que parecen numéricas.
- **Sin IDs en el navegador (Etapa 5):**
  - Los botones llevan las claves estables (`data-sistema`, `data-modulo`,
    `data-accion`, `data-rol`).
  - El endpoint las resuelve con `api/consultas/catalogos.php`, siempre dentro de la
    aplicación indicada.
  - Antes de responder se quitan los IDs con `sinIdsInternos()`.
- **Filtros por aplicación o módulo:** se hacen con `$.fn.dataTable.ext.search`, sobre
  los datos de la fila y limitado a la propia tabla (`ajustes.nTable`). **No** se usa
  `column().search('^X$')`: el texto de búsqueda de la columna también incluye el
  nombre y el filtro deja de coincidir (error encontrado en la Etapa 5).
- **Anchos de columna:** en las tablas con columnas numéricas se fija `width` en las
  columnas de texto principales, porque si no DataTables las deja angostas.
- **Códigos inmutables y borrado:**
  - Los códigos (`codigoSistema`, `codigoModulo`, `accion`, `codigoRol`) no se editan.
  - "Eliminar" solo aparece y solo procede si el elemento no tiene ninguna relación.
    Si tiene, se desactiva. Las llaves foráneas son la última barrera (547 → 409).
  - `ADM_ACCESOS` (`CODIGO_SISTEMA`) no se puede desactivar ni eliminar.
- **Listas enviadas al servidor:** se mandan como JSON en un solo campo, por ejemplo
  `permisos: JSON.stringify([...])`. Así una lista vacía es explícita.
- **Filtro inicial por URL:** las pantallas aceptan parámetros como `?sistema=` y
  `?modulo=`, y se leen con `App.parametroUrl()`. Los enlaces entre pantallas (por
  ejemplo, detalle de una aplicación → sus módulos) los usan.
- **Elegir un usuario (Etapa 6):** se usa `includes/selectorUsuario.php` más
  `App.selectorUsuario({ alElegir, alCambiar, antesDeCambiar })`. Su lista sale de
  `consultarUsuarios()`. Acepta `?nomina=` para llegar con el usuario ya elegido; el
  detalle de usuarios enlaza así a sus asignaciones.
- **Filtros rápidos tipo píldora:** se usan las clases `filtro-pildoras`,
  `filtro-pildora` y `filtro-pildora-total`, que están en `style.css`. Antes eran
  `usuarios-filtro-*` y se generalizaron.
- **Claves largas:** `App.ui.clave()` agrega `<wbr>` después de cada punto, para que
  una clave se corte entre sus partes y no a media palabra.
- **Cambios sin guardar en una pantalla:** se confirman con `App.confirmar()` al
  cambiar de usuario o de aplicación, y además se usa `beforeunload` (ver
  `rolesUsuario.js`).
- **Iconos:** Bootstrap Icons local es la versión **1.13.1**, y algunos iconos de la
  documentación más nueva no existen (por ejemplo `bi-person-search`). Hay que
  verificarlos con
  `grep -c "\.bi-NOMBRE::before" assets/css/bootstrap-icons.min.css`.

### Cómo probar

- Apache de XAMPP escucha en el **puerto 8080**:
  `http://localhost:8080/adminAccesos/`. El puerto 80 lo ocupa el sistema.
- Las vistas y los endpoints están probados con datos reales, pero **sin un login real**:
  no se tiene la contraseña de ningún usuario. Para la prueba se simuló una sesión con un
  archivo temporal en `C:\xampp\tmp`, que ya se borró.
- Pruebas que hizo Claude y resultado:
  - Edge sin ventana: capturas a 1400, 1000 y 390 px, sin errores de JavaScript ni de red.
  - `curl`: guardas de sesión, CSRF, `X-Requested-With`, método POST y vistas directas
    (404).
  - `php -l` sobre todos los archivos PHP, sin errores.

---

## Estructura real de la base de datos (verificada contra `ELECTRONICOSDB`)

SQL Server 2022 (16.0), Developer Edition, intercalación `Modern_Spanish_CI_AS`, que
**no distingue mayúsculas en las comparaciones**. Nivel de compatibilidad 160, así que
`STRING_AGG` y `OPENJSON` están disponibles.

| Tabla | Columnas | Llaves y restricciones |
|---|---|---|
| `usuarios` | `nominaUsuario nvarchar(15)`, `nombre nvarchar(100)`, `passwordHash nvarchar(max)`, `estatus nvarchar(10)`, `fechacreacion date NULL` (**c minúscula**), `comentarios nvarchar(1000) NULL` | Solo `UNIQUE (nominaUsuario)`; **sin PK** y **sin CHECK** en `estatus` |
| `aplicaciones` | `codigoSistema nvarchar(50)`, `nombre nvarchar(100)`, `descripcion nvarchar(250) NULL`, `activo bit` DEFAULT 1 | PK `codigoSistema`; CHECK `activo` |
| `modulos` | `idModulo` IDENTITY, `codigoSistema`, `codigoModulo nvarchar(50)`, `nombre nvarchar(100)`, `ruta nvarchar(100) NULL`, `icono nvarchar(50) NULL`, `orden int` DEFAULT 0, `activo bit` DEFAULT 1 | PK; `UQ_modulos (codigoSistema, codigoModulo)`; FK a `aplicaciones`; CHECK `activo` |
| `permisos` | `idPermiso` IDENTITY, `idModulo`, `accion nvarchar(30)`, `descripcion nvarchar(250) NULL` | PK; `UQ_permisos (idModulo, accion)`; FK a `modulos`; **sin `activo`** |
| `roles` | `idRol` IDENTITY, `codigoSistema`, `codigoRol nvarchar(50)`, `nombreRol nvarchar(100)`, `descripcion nvarchar(250) NULL`, `activo bit` DEFAULT 1 | PK; `UQ_roles (codigoSistema, codigoRol)`; FK a `aplicaciones` |
| `rol_permisos` | `idRol`, `idPermiso` | PK `(idRol, idPermiso)`; FK a `roles` y a `permisos` |
| `usuario_roles` | `idUR` IDENTITY, `idRol`, `nominaUsuario nvarchar(15)` | PK; `UQ_usuario_roles (nominaUsuario, idRol)`; FK a `roles` y a `usuarios`; **sin campos de auditoría** |
| `usuario_permisos` | `idUP` IDENTITY, `nominaUsuario`, `idPermiso`, `tipo nvarchar(10)`, `motivo nvarchar(250) NULL`, `vigenciaHasta date NULL`, `otorgadoPor nvarchar(15) NULL`, `fechaRegistro datetime2(0)` DEFAULT `SYSDATETIME()` | PK; `UQ_usuario_permisos (nominaUsuario, idPermiso)`; FK a `usuarios` y a `permisos`; CHECK `tipo IN ('conceder','denegar')` |

- Todas las FK usan `ON DELETE NO ACTION`. No hay triggers.
- **Todas las restricciones UNIQUE que pide `contexto.md` ya existen.** No hace falta
  ninguna migración para garantizar la unicidad.
- No existe una tabla de auditoría.
- Datos al 2026-10-01:
  - 12 usuarios, todos con estatus `activo`.
  - 2 aplicaciones (`PC`, `SRD`), 11 módulos, 22 permisos y 12 roles.
  - 102 relaciones rol-permiso, 12 asignaciones usuario-rol y 2 excepciones individuales, ambas de tipo `conceder`.
- Las inconsistencias encontradas están en `PENDIENTES.md`, sección 2.

---

## Decisiones de diseño

Marcadas como **propuesta** mientras el usuario no las confirme.

0. **Confirmadas por el usuario el 2026-10-01.**
   - La auditoría queda pendiente.
   - Los permisos de `ADM_ACCESOS` se definen **al final**. Mientras tanto solo se
     exige sesión: cualquier usuario activo entra (ver `PENDIENTES.md` §3).
   - La contraseña mínima es de 8 caracteres.
   - En el login se escribe la nómina.
   - El CSS propio va en `css/`.
1. **Navegación (implementada en la Etapa 3).** Un solo punto de entrada,
   `index.php?p=<pagina>`, con recarga normal, como en `defectos`. `index.php`:
   - valida la sesión;
   - busca la clave en `includes/paginas.php`;
   - incluye la vista `pages/<pagina>.php`;
   - carga `css/<pagina>.css` y `assets/js/<pagina>.js`.

   El parámetro `p` nunca se usa para armar una ruta: solo se busca en el catálogo. El
   menú tiene grupos desplegables ("Catálogos" y "Asignaciones") y las pantallas sin
   terminar muestran el aviso "en construcción".
2. **API (implementada).** Un endpoint por dominio (`api/operaciones<Dominio>.php`),
   con dispatcher por `opcion` numérica en texto y respuesta `{status, mensaje, data}`,
   como en `defectos`. El SQL va aparte, en `api/consultas/<dominio>.php`.
   `prepararEndpoint()` exige en **todas** las peticiones POST, `X-Requested-With`,
   sesión (401 si no hay) y token CSRF (403 si no coincide). Cuando se definan los
   permisos, cada `case` empezará con `exigirPermiso()`.
3. **Sesión (implementada).** Cookie propia (`ADMACCSESSID`), `HttpOnly`,
   `SameSite=Lax`, `Secure` con HTTPS y `use_strict_mode`. Tras el login se ejecuta
   `session_regenerate_id(true)` y se renueva el token CSRF. El contrato de sesión es
   el de `autenticacion-y-permisos.md` §6.4; `permisos` y `roles` quedan vacíos por
   ahora. El token CSRF se publica en `<meta name="csrf-token">` y `app.js` lo envía en
   la cabecera `X-CSRF-Token`. El logout es un POST con el token en un campo oculto.
4. **Constante del sistema.** `CODIGO_SISTEMA = 'ADM_ACCESOS'` en un solo archivo de
   configuración. Ningún ID numérico en el código.
5. **Matriz de permisos de `ADM_ACCESOS` (propuesta, diferida al final del desarrollo).**
   Hay un módulo por pantalla, para que `modulos` también genere el menú. Todos tienen
   `ver`.

   | Módulo | Acciones | Endpoint y opciones que protege |
   |---|---|---|
   | `dashboard` | `ver` | `operacionesDashboard` 1 |
   | `usuarios` | `ver`, `crear`, `modificar`, `activar_desactivar`, `restablecer_password` | `operacionesUsuarios` 1–2 / 3 / 4 / 5 / 6 |
   | `aplicaciones` | `ver`, `crear`, `modificar`, `activar_desactivar`, `eliminar` | `operacionesAplicaciones` 1–2 / 3 / 4 / 5 / 6 |
   | `modulos` | `ver`, `crear`, `modificar`, `activar_desactivar`, `eliminar` | `operacionesModulos` 1–2 / 3 / 4 / 5 / 6 |
   | `permisos` | `ver`, `crear`, `modificar`, `eliminar` | `operacionesPermisos` 1–2 / 3 / 4 / 5 |
   | `roles` | `ver`, `crear`, `modificar`, `activar_desactivar`, `eliminar`, `asignar_permisos` | `operacionesRoles` 1, 2, 7 / 3 / 4 / 5 / 6 / 8 |
   | `roles_usuario` | `ver`, `asignar_roles`, `quitar_roles` | `operacionesRolesUsuario` 1–2 / 3 (según lo que agregue o quite) |
   | `permisos_usuario` | `ver`, `asignar_excepcion`, `modificar_excepcion`, `retirar_excepcion`, `eliminar_excepcion` | `operacionesPermisosUsuario` 1–2 / 3 / 4 / 5 / 6 |
   | `permisos_efectivos` | `ver` | `operacionesPermisosEfectivos` 1–2 |
   | `auditoria` | `ver` | (cuando exista) |

   - Actualizada en la Etapa 8: se agregaron las acciones `eliminar` y
     `eliminar_excepcion`, que nacieron en las Etapas 5 y 6.
   - Se quitó `copiar_permisos`: copiar ocurre en la pantalla y solo se guarda con
     `asignar_permisos`.
   - El rol `admin_accesos` recibe todos estos permisos. Los códigos de módulo van en
     snake_case (`roles_usuario`) y las claves de pantalla en `paginas.php` van en
     camelCase (`rolesUsuario`), así que al implementar hay que mapear cada pantalla
     a su módulo.
6. **Validación de claves.**
   - `codigoSistema`: `^[A-Z][A-Z0-9_]*$`.
   - `codigoModulo`: `^[A-Za-z][A-Za-z0-9_]*$`. Acepta camelCase por los datos existentes.
   - `codigoRol` y `accion`: `^[a-z][a-z0-9_]*$`.
   - Las longitudes máximas son las de la tabla: 50 caracteres para los códigos y 30 para `accion`.
7. **Contraseñas.** Se usa `password_hash(PASSWORD_DEFAULT)` y `password_verify()`. El
   hash nunca se envía al navegador ni se escribe en logs.

---

## Archivos previstos por etapa

| Etapa | Archivos |
|---|---|
| 2 ✅ | `sql/01_diagnostico.sql` y `sql/LEEME.md`. **Diferido:** `sql/02_configuracion_inicial_adm_accesos.sql` (se hará al definir los permisos) |
| 3 ✅ | Ver "Estructura actual del proyecto" arriba |
| 4 ✅ | `pages/usuarios.php`, `api/operacionesUsuarios.php`, `api/consultas/usuarios.php`, `api/consultas/permisosEfectivos.php`, `assets/js/usuarios.js`, `css/usuarios.css`, `includes/modalConfirmar.php` |
| 5 ✅ | `pages/`, `assets/js/`, `api/operaciones*` y `api/consultas/` de `aplicaciones`, `modulos`, `permisos` y `roles`; `api/consultas/catalogos.php`; `css/roles.css` |
| 6 ✅ | `pages/`, `assets/js/`, `css/` y `api/operaciones*` de `rolesUsuario` y `permisosUsuario`; `api/consultas/asignaciones.php`; `includes/selectorUsuario.php` |
| 7 ✅ | `pages/permisosEfectivos.php`, `assets/js/permisosEfectivos.js`, `css/permisosEfectivos.css`, `api/operacionesPermisosEfectivos.php`; aviso de bloqueo de `auditoria` ('aviso' en `paginas.php`, soportado por `index.php`) |
| 8 ✅ | `INSTALACION.md`; `.htaccess` en la raíz, `includes/`, `pages/`, `api/`, `api/consultas/` y `sql/`; `sql/LEEME.md` (requisito SQL Server 2017+) |

---

## Bitácora

Agregar las entradas nuevas al final, con fecha.

- **2026-10-01.** Lectura de `contexto.md` y `autenticacion-y-permisos.md`.
- **2026-10-01. Etapa 1.** Se revisó la estructura de `adminAccesos` y de los proyectos
  de referencia (`defectos` y `PuntosDeCambio`). Se consultó el catálogo de
  `ELECTRONICOSDB` (columnas, PK, UNIQUE, FK, CHECK, triggers y conteos) y se ejecutó un
  diagnóstico de datos. Todo fue **de solo lectura**: no se modificó ni código ni base
  de datos. Los scripts SQL que existen en otros proyectos
  (`PuntosDeCambio/BDPuntosDeCambio.sql`) están desactualizados respecto a la base
  real, así que la fuente de verdad es la tabla de arriba. Se crearon `PENDIENTES.md` y
  `PROGRESO.md`.
- **2026-10-01. Decisiones del usuario.**
  - La auditoría queda pendiente.
  - Los permisos de la aplicación se definen al final.
  - Contraseña mínima de 8 caracteres.
  - La nómina se escribe en el login.
  - El CSS va en `css/`.
  - No se modifica la estructura de la base.
- **2026-10-01. Etapa 2.** Se creó `sql/01_diagnostico.sql`, que es de solo lectura, y
  se probó con `sqlcmd` contra `LELEDANIEL`. Se creó también `sql/LEEME.md`.
- **2026-10-01. Etapa 3.**
  - Se implementaron la sesión, el CSRF, el login y el logout, el layout, el menú con
    grupos, el enrutador y el dashboard: totales, accesos rápidos, excepciones por
    vencer, usuarios sin acceso e inconsistencias.
  - Se cambió `api/conexion.php` para que no exponga el error de SQL Server.
  - Se probó con `php -l`, con `curl` y con Edge sin ventana usando una sesión simulada.
  - Ajustes visuales tras revisar las capturas:
    - icono del estado vacío;
    - nombre del usuario oculto en celular;
    - menú más compacto por debajo de 1200 px;
    - la entrada activa del menú se desplaza a la vista.
- **2026-10-01. Etapa 4.**
  - **Pantalla de usuarios:**
    - tabla con filtro por estatus;
    - alta con contraseña inicial y botón para generar una;
    - edición de nombre y comentarios (la nómina no cambia);
    - activar y desactivar con confirmación (no se puede desactivar al usuario de la
      sesión);
    - restablecer contraseña en dos pasos;
    - detalle con datos, roles, permisos individuales y permisos efectivos.
  - **Pruebas:**
    - `php -l` sin errores.
    - Validaciones del endpoint (14 casos) con sesión simulada.
    - Permisos efectivos comparados contra la consulta del login de `defectos`: 24
      combinaciones de usuario y aplicación, sin diferencias.
    - Escrituras SQL y precedencia (denegar > conceder > rol, vencidos ignorados) dentro
      de una transacción revertida.
    - Interfaz en Edge sin ventana: modales, filtro y alta duplicada (409), sin errores
      de JavaScript.
    - Al terminar se confirmó que la base quedó igual: 12 usuarios y 2 excepciones.
  - **Versiones:** `style.css` y `app.js` pasaron a `?v=2`.
- **2026-10-01. Etapa 5.**
  - **Pantallas:**
    - Catálogos de aplicaciones, módulos, permisos y roles: alta, edición, activar y
      desactivar, eliminar solo sin relaciones, y detalle.
    - En roles, asignación de permisos con checks por módulo: búsqueda, marcar todo
      el módulo, copiar desde otro rol (reemplazar o agregar), resumen de cambios y
      aviso al cerrar sin guardar.
  - **Piezas compartidas nuevas:**
    - `api/consultas/catalogos.php`, que resuelve claves en IDs;
    - lectores de campos en `includes/api.php`;
    - `App.ui` en `app.js`;
    - sección 10d de `style.css`;
    - patrones de claves en `configuracion.php`.
  - `esViolacionUnica()` se movió de `operacionesUsuarios.php` a `includes/api.php`.
  - **Pruebas:**
    - `php -l` sin errores.
    - Lecturas de los cuatro endpoints con datos reales.
    - Ciclo completo de escrituras con una aplicación aislada `ZZPRUEBA`: alta, edición,
      estado, permisos y roles, asignación (rechaza permisos de otra aplicación, JSON
      inválido e inyección), borrados bloqueados (409) y borrados permitidos.
    - Al final se eliminó todo con los propios endpoints. Los conteos de las 8 tablas
      quedaron idénticos a los de antes.
    - Interfaz probada en Edge sin ventana.
  - **Correcciones tras las capturas:**
    - el filtro por aplicación de módulos y roles devolvía 0 filas;
    - anchos de columna;
    - descripción recortada a 2 líneas;
    - aviso del menú al eliminar un módulo.
  - **Versiones:** `style.css` y `app.js` pasaron a `?v=3`.
- **2026-10-01. Etapa 6.**
  - **Roles de usuario:**
    - buscador de usuario, aplicación y checks con los roles de esa aplicación;
    - resumen de sus roles en todas las aplicaciones;
    - quitar roles pide confirmación;
    - aviso al cambiar de usuario o aplicación con cambios sin guardar.
  - **Permisos individuales:**
    - tabla con filtros: Vigentes, Permanentes y Vencidas;
    - modal con aplicación, módulo y permiso, con una pista de si ya lo tiene por rol;
    - conceder o denegar, motivo obligatorio y vigencia (o "sin vencimiento");
    - editar o renovar;
    - retirar, que vence ayer y lo conserva como historial;
    - eliminar, solo las ya vencidas.
  - **Decisiones:**
    - Al reconceder un permiso con una excepción vencida se reutiliza ese registro
      (por UNIQUE).
    - Al editar, `otorgadoPor` y `fechaRegistro` pasan a ser los de quien edita.
    - No se crean ni editan asignaciones de usuarios inactivos, pero sí se pueden
      quitar o retirar.
  - **Otros cambios:**
    - `consultarExcepcionesDeUsuario()` ahora incluye la aplicación, el módulo y si
      están activos.
    - El filtro de píldoras se generalizó a `style.css`.
    - El detalle de usuario tiene enlaces a "Asignar roles" y "Permisos individuales"
      (versión 2 de la pantalla Usuarios).
    - Se corrigió el icono `bi-person-search`, que no existe; se verificaron todos los
      iconos del proyecto.
  - **Pruebas:**
    - Con datos aislados: aplicación `ZZPRUEBA` y usuario de prueba `T10`, solo con
      roles y permisos de `ZZPRUEBA`.
    - Reglas de inactivo (rol y usuario), roles y permisos de otra aplicación, JSON
      inválido, duplicados, fechas pasadas o inválidas, precedencia en los permisos
      efectivos y `otorgadoPor` desde la sesión.
    - Interfaz probada en Edge sin ventana.
    - Al final se limpió todo con los endpoints. La foto de conteos y de los datos de
      T10 quedó **idéntica** a la inicial.
  - **Versiones:** `style.css` y `app.js` pasaron a `?v=4`.
- **2026-10-01. Etapa 7.**
  - **Pantalla Permisos efectivos:**
    - selector de usuario y resumen por aplicación (permitidos, denegados y roles,
      con los inactivos tachados);
    - tabla con las columnas Aplicación, Módulo, Acción, Resultado y Origen, como en
      `contexto.md`;
    - las excepciones vencidas aparecen como "Vencido, sin efecto";
    - filtros por resultado (píldoras), aplicación, módulo y origen (un rol o "Solo
      excepciones individuales");
    - al hacer clic en una aplicación del resumen, la tabla se filtra.
  - Reutiliza `consultarPermisosEfectivos()`, ya validada contra el login de
    `defectos`. Los resultados de 11607 (22: 17 de `PC` y 5 de `SRD`) y de T10 (3 más
    1 vencida) coinciden con esa validación.
  - **Auditoría:** `paginas.php` admite `'aviso'` por pantalla y `index.php` lo
    muestra en lugar del "en construcción" genérico.
  - `.fila-vencida` se movió a `style.css`, porque la usan dos pantallas
    (Permisos individuales pasó a la versión 2).
  - **Pruebas:**
    - endpoint con 3 usuarios más uno inexistente;
    - filtros en Edge;
    - anchos de columna corregidos tras las capturas.
  - Esta etapa es de solo lectura: la base quedó idéntica a la foto inicial.
  - **Versiones:** `style.css` y `app.js` pasaron a `?v=5`.
- **2026-10-01. Etapa 8 (revisión final).**
  - **Revisión estática:**
    - Los 10 endpoints usan `prepararEndpoint()`, abren la conexión dentro del `try` y
      atrapan errores.
    - Solo las contraseñas se leen directo de `$_POST`, a propósito, para no
      recortarlas.
    - `passwordHash` nunca se devuelve.
    - La única variable concatenada en SQL es `$dias`, una constante entera.
    - Las vistas PHP escapan todo lo que imprimen.
    - En el JavaScript se revisaron 20 casos marcados por la búsqueda; todos son
      seguros (texto de búsqueda u orden, `.text()` o valores escapados).
  - **Pruebas HTTP:**
    - Los 9 endpoints con sesión responden 401 sin sesión, 403 sin CSRF o con un
      token falso, 403 sin `X-Requested-With` y 405 con GET.
  - **Hallazgos corregidos:**
    1. La documentación (`.md`) y `sql/` se podían descargar por web. Revelaban el
       servidor, la base y la contraseña de prueba de `PENDIENTES.md`.
    2. Apache listaba el contenido de las carpetas (`Options Indexes` en
       `httpd.conf`).
    3. `api/conexionEmpleado.php` era accesible. Intentaba conectarse a otro
       servidor y mostraba el error interno; además contiene credenciales `sa`.
    4. `includes/*.php` se podían abrir directamente.

    La solución fueron los `.htaccess` (ver estructura). Se verificó que todo lo
    interno responde 403 y que las páginas, los recursos y los endpoints siguen
    funcionando.
  - **Recorrido en Edge:** las 11 pantallas más el login, a 1400 y 390 px, sin
    errores de JavaScript ni de red y sin desborde horizontal.
  - **Ajuste final:** las claves en tablas solo se cortan en sus puntos
    (`.table .clave { overflow-wrap: normal }`).
  - **Requisitos confirmados:**
    - el código no usa nada exclusivo de PHP 8 (requisito PHP 7.4+);
    - SQL Server 2017+ por `STRING_AGG`.
  - El diagnóstico se volvió a ejecutar y sus resultados son idénticos a los del
    primer día.
  - **Versiones:** `style.css` pasó a `?v=6`.
