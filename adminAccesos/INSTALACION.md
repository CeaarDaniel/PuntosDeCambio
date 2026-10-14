# Administración de accesos: instalación, base de datos y pruebas

Guía para instalar la aplicación, preparar la base de datos y verificarla a mano.
Para el detalle técnico y el estado del desarrollo, consulta `PROGRESO.md`. Para lo
que falta y los riesgos conocidos, consulta `PENDIENTES.md`.

> ⚠️ **Antes de usarla con datos reales, lee la sección 6.** Todavía no se validan
> los permisos de la propia aplicación: **cualquier usuario activo** puede entrar y
> administrar todo.

---

## 1. Requisitos

| Componente | Requisito | Probado con |
|---|---|---|
| Servidor web | Apache 2.4 con `AllowOverride All` en `htdocs` (lo necesitan los `.htaccess`) | XAMPP, Apache en el puerto 8080 |
| PHP | 7.4 o superior, con las extensiones `pdo_sqlsrv`, `sqlsrv` y `mbstring` | PHP 8.2.12 |
| Controlador | Microsoft ODBC Driver 17 o 18 for SQL Server | ODBC Driver 17 |
| Base de datos | SQL Server **2017 o superior**, porque se usa `STRING_AGG`. Base `ELECTRONICOSDB` con las 8 tablas del modelo de `autenticacion-y-permisos.md` | SQL Server 2022 (16.0), compatibilidad 160 |
| Navegador | Edge o Chrome actuales | Edge |

No usa Node, Composer ni librerías externas: Bootstrap, jQuery, DataTables y los
iconos están dentro de `assets/`.

## 2. Instalación

1. **Copiar** la carpeta `adminAccesos` a `C:\xampp\htdocs\`.
2. **Configurar la conexión** en `api/conexion.php`: servidor (`Server=`), base
   (`Database=ELECTRONICOSDB`), usuario y contraseña. Con usuario y contraseña vacíos
   se usa la autenticación de Windows.
   - Si falla la conexión, el detalle se escribe en el log y la pantalla muestra un
     mensaje genérico.
3. **Quitar `api/conexionEmpleado.php`** (recomendado). Esta aplicación no lo usa y
   contiene credenciales `sa` de otra base en texto plano. Ya está bloqueado por web
   (`api/.htaccess`), pero lo más seguro es que no exista.
4. **Comprobar los `.htaccess`.** Abre `http://<servidor>/adminAccesos/PROGRESO.md`:
   debe responder **403 Prohibido**. Si se ve el archivo, Apache no está leyendo los
   `.htaccess`; revisa `AllowOverride` en `httpd.conf`.
   - Están bloqueados la documentación (`.md`), `sql/`, `includes/`, `pages/`,
     `api/consultas/`, los archivos de conexión y el listado de carpetas.
5. **En producción, usar HTTPS.** La cookie de sesión (`ADMACCSESSID`) se marca
   `Secure` automáticamente cuando la conexión es HTTPS, y siempre es `HttpOnly` y
   `SameSite=Lax`.
6. **Abrir** `http://<servidor>:<puerto>/adminAccesos/`. Sin sesión, la aplicación
   redirige a `login.php`.

**Logs.** Los errores técnicos van al log de Apache y PHP
(`C:\xampp\apache\logs\error.log`), con el prefijo `[adminAccesos]`. Al usuario solo
le llega un mensaje entendible. Nunca se registran contraseñas.

## 3. Base de datos: scripts y orden

Los scripts están en `sql/` y se ejecutan a mano desde SSMS o con `sqlcmd`. Su detalle
está en `sql/LEEME.md`.

| Orden | Script | Qué hace | ¿Modifica? |
|---|---|---|---|
| 1 | `01_diagnostico.sql` | Revisa duplicados, relaciones entre aplicaciones distintas, asignaciones huérfanas, excepciones vencidas, usuarios sin acceso, módulos sin `ver` y contraseñas sin hash | No, solo lee |
| 2 | `02_configuracion_inicial_adm_accesos.sql` | Registrará la aplicación `ADM_ACCESOS`, sus permisos y su rol de administrador | **Todavía no existe** (`PENDIENTES.md` §3) |
| — | Migraciones de estructura | Tabla de auditoría, columnas para forzar el cambio de contraseña y quién asignó cada rol | **Todavía no existen** (`PENDIENTES.md` §1) |

- **No hace falta ninguna migración para que la aplicación funcione.** Todas las
  restricciones UNIQUE y llaves foráneas que necesita ya existen en
  `ELECTRONICOSDB`; la revisión 0 del diagnóstico lo confirma.
- **Ejecuta siempre `01_diagnostico.sql` antes de cualquier migración** y revisa sus
  resultados. Ningún script corrige ni borra datos por su cuenta.

```
sqlcmd -S LELEDANIEL -E -f 65001 -W -s "|" -i sql\01_diagnostico.sql
```

## 4. Primer acceso

1. Entra con la **nómina** y la **contraseña** de un usuario con estatus `activo`.
2. Si el usuario no existe o la contraseña es incorrecta, el mensaje es el mismo
   ("Nómina o contraseña incorrectos"). Así no se revela qué nóminas existen.
3. Un usuario cuya contraseña no está guardada como hash (hoy: `reportes`) no puede
   entrar. Restablécela desde **Usuarios → 🔑**.

## 5. Pruebas manuales

**Recomendación para no tocar datos reales:**

- Crea una aplicación de prueba (por ejemplo `ZPRUEBA`) con sus propios módulos,
  permisos y roles, y úsala con un usuario de prueba.
- Al terminar, bórrala en este orden:
  1. quitar sus roles y excepciones a los usuarios;
  2. dejar los roles sin permisos;
  3. eliminar roles, permisos y módulos;
  4. eliminar la aplicación.
- Los usuarios no se eliminan; desactiva el de prueba.

Marca cada caso al verificarlo.

### 5.1 Acceso y sesión
- [ ] Una contraseña incorrecta y una nómina inexistente muestran el mismo mensaje.
- [ ] Un usuario inactivo recibe "Esta cuenta no está activa".
- [ ] Al entrar se llega al Inicio y el menú muestra tu nombre.
- [ ] **Cerrar sesión** (menú del usuario) regresa al login, y el botón Atrás no
      vuelve a mostrar datos.
- [ ] Abrir `index.php?p=noexiste` muestra "Página no encontrada".

### 5.2 Inicio (dashboard)
- [ ] Los totales coinciden con la base (usuarios activos e inactivos, aplicaciones,
      módulos, roles y permisos).
- [ ] "Posibles inconsistencias" coincide con `01_diagnostico.sql`.
- [ ] Los accesos rápidos llevan a cada pantalla.

### 5.3 Usuarios
- [ ] **Nuevo usuario:** sin nómina o con una contraseña de menos de 8 caracteres no
      se guarda; "Generar una contraseña" llena los dos campos; una nómina repetida
      muestra el aviso dentro del formulario.
- [ ] Al crearlo, aparece en la tabla con su fecha de alta y **puede iniciar sesión**.
- [ ] **Editar** cambia el nombre y los comentarios; la nómina no se puede cambiar.
- [ ] **Desactivar** pide confirmación; el usuario ya no puede entrar. **Activar** lo
      regresa.
- [ ] Tu propio usuario no se puede desactivar (el botón está deshabilitado).
- [ ] **Restablecer contraseña** pide confirmación en un segundo paso; después la
      contraseña anterior ya no funciona y la nueva sí.
- [ ] **Ver detalle:** pestañas Datos, Roles, Permisos individuales y Permisos
      efectivos; los botones del pie llevan a sus asignaciones.

### 5.4 Aplicaciones
- [ ] Un código en minúsculas o con espacios no se acepta (se convierte a
      mayúsculas al escribirlo).
- [ ] Un código repetido muestra el aviso.
- [ ] Editar cambia el nombre y la descripción; el código no se puede cambiar.
- [ ] Desactivar pide confirmación. Los usuarios con roles en ella dejan de tener
      esos permisos en **Permisos efectivos**.
- [ ] **Eliminar** solo aparece si la aplicación no tiene módulos ni roles.
- [ ] `ADM_ACCESOS`, cuando exista, no se puede desactivar ni eliminar.

### 5.5 Módulos
- [ ] Llegar desde **Aplicaciones → detalle → Administrar módulos** abre la pantalla
      ya filtrada.
- [ ] Al crear con "Crear también la acción ver", el módulo nace con 1 permiso.
- [ ] Un icono inválido (por ejemplo `fa fa-x`) no se acepta; uno válido
      (`bi-gear`) se ve en la vista previa.
- [ ] Un módulo sin `ver` muestra la etiqueta "Sin ver".
- [ ] Eliminar solo aparece si el módulo no tiene permisos. La confirmación avisa del
      menú.

### 5.6 Permisos
- [ ] La clave completa se va mostrando mientras capturas
      (`APLICACION.modulo.accion`).
- [ ] Una acción con espacios, acentos o mayúsculas no se acepta.
- [ ] "¿Quién lo tiene?" lista los roles, los usuarios por rol y las excepciones.
- [ ] Editar solo permite cambiar la descripción.
- [ ] Eliminar solo aparece si el permiso no está asignado a nadie.

### 5.7 Roles y asignación de permisos
- [ ] Crear un rol con un código en mayúsculas no se acepta (debe ser `sup_calidad`).
- [ ] **Asignar permisos (☑):**
  - solo aparecen permisos de la aplicación del rol;
  - "todo el módulo" marca y desmarca;
  - la búsqueda filtra la lista;
  - el pie indica "+N −M sin guardar".
- [ ] **Copiar de otro rol:** "Reemplazar" y "Agregar" cambian la selección, pero
      **no se guarda nada** hasta "Guardar permisos".
- [ ] Cerrar con cambios sin guardar muestra el aviso "¿Salir sin guardarlos?".
- [ ] Eliminar solo aparece si el rol no tiene usuarios ni permisos.

### 5.8 Roles de usuario
- [ ] El buscador encuentra por nómina o por nombre, aunque se escriba sin acentos o
      en mayúsculas.
- [ ] Al elegir una aplicación solo aparecen sus roles. Un rol inactivo aparece
      bloqueado.
- [ ] Quitar un rol pide confirmación y dice cuál.
- [ ] Con un usuario inactivo solo se pueden quitar roles; agregar está bloqueado.
- [ ] Cambiar de usuario o de aplicación con cambios sin guardar pide confirmación.

### 5.9 Permisos individuales
- [ ] **Nueva excepción:**
  - un permiso que ya tiene una excepción con efecto aparece bloqueado;
  - la pista indica si el usuario ya lo tiene por rol.
- [ ] Sin elegir conceder o denegar, sin motivo, o sin fecha y sin marcar "sin
      vencimiento", no se guarda.
- [ ] Una fecha anterior a hoy no se acepta.
- [ ] **Denegar** un permiso que el usuario tiene por rol: en **Permisos efectivos**
      aparece como *Denegado — Excepción individual*.
- [ ] **Retirar** deja la excepción como *Vencida* (sigue en la lista).
- [ ] **Eliminar** solo está disponible para las vencidas.
- [ ] Los filtros Vigentes, Permanentes y Vencidas cuentan bien.

### 5.10 Permisos efectivos
- [ ] Coinciden con lo que la aplicación real permite:
  1. Elige un usuario que use, por ejemplo, SRD.
  2. Compara la tabla filtrada por `SRD` con lo que ve al iniciar sesión en
     `defectos`.
- [ ] Los filtros por resultado, aplicación, módulo y origen ("Solo excepciones
      individuales" o un rol) funcionan.
- [ ] Hacer clic en una aplicación del resumen filtra la tabla.

### 5.11 Seguridad
- [ ] `PROGRESO.md`, `sql/01_diagnostico.sql`, `includes/sesion.php` y
      `api/conexion.php` responden **403**.
- [ ] Con las herramientas del navegador, repetir una petición a `api/` sin el
      encabezado `X-CSRF-Token` responde **403**; sin sesión responde **401**.
- [ ] En la pestaña Red, ninguna respuesta contiene `passwordHash` ni IDs internos
      (`idRol`, `idPermiso`, `idModulo`).

## 6. Antes de pasar a producción

1. **Definir los permisos de la propia aplicación** (`PENDIENTES.md` §3):
   - script `02_configuracion_inicial_adm_accesos.sql`;
   - `exigirPermiso()` en cada endpoint y `exigirAcceso()` en cada pantalla;
   - rechazar en el login a quien no tenga acceso a `ADM_ACCESOS`.

   Hasta entonces, cualquier usuario activo administra todo.
2. Quitar `api/conexionEmpleado.php` (sección 2).
3. Revisar los datos que reporta el diagnóstico y `PENDIENTES.md` §2. Por ejemplo,
   hay contraseñas escritas en los comentarios de los usuarios de prueba `T01` a
   `T09`.
4. Servir la aplicación por HTTPS.

## 7. Solución de problemas

| Síntoma | Causa probable | Qué hacer |
|---|---|---|
| "No fue posible completar la operación…" | Error de base de datos o de conexión | Ver `error.log` (prefijo `[adminAccesos]`) y revisar `api/conexion.php` y el ODBC Driver |
| "La página expiró. Recárgala…" | El token CSRF cambió (otra pestaña inició sesión o se cerró la sesión) | Recargar la página |
| Al hacer clic, regresa al login | La sesión expiró | Volver a iniciar sesión |
| La pantalla se ve sin estilos o con un comportamiento viejo | Caché del navegador | Recargar con Ctrl+F5. Al modificar CSS o JS hay que subir su `?v=` (ver `PROGRESO.md`, "Convenciones") |
| Un icono no aparece | El icono no existe en Bootstrap Icons 1.13.1 (la versión local) | Usar un icono de esa versión |
| La documentación se puede abrir en el navegador | Apache no lee los `.htaccess` | `AllowOverride All` en el `<Directory>` de `htdocs` |
| Un usuario no ve un cambio de roles o permisos | Cada aplicación guarda los permisos en la sesión al entrar | Que cierre sesión y vuelva a entrar |
