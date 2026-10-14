crear una aplicacion independiente para administrar de forma centralizada los usuarios, aplicaciones, modulos, roles y permisos utilizados por las aplicaciones internas de la empresa.

Antes de modificar código, revisa la estructura del proyecto actual y los proyectos similares disponibles. También revisa el archivo `autenticacion-y-permisos.md`, los scripts SQL existentes y la estructura real de las tablas. No asumas nombres de columnas si puedes verificarlos.

La nueva aplicación debe estar en una carpeta/proyecto independiente y conservar la misma estructura, diseño y forma de trabajo de las demás aplicaciones.  En la carpeta actual ya esta la estructura necesaria, construye sobre esta misma carpeta C:\xampp\htdocs\usuariosRolesPermisos

============================================================
STACK
============================================================

- PHP
- SQL Server
- HTML
- CSS
- JavaScript
- jQuery
- Bootstrap
- DataTables
- Bootstrap Icons

No utilizar:

- React
- Angular
- Vue
- Node.js
- Frameworks PHP
- APIs externas
- Dependencias nuevas
- Build tools

Usa consultas preparadas con PDO y conserva el estilo actual de los proyectos.

============================================================
OBJETIVO
============================================================

Crear una aplicación llamada “Administración de accesos” que permita administrar:

- Usuarios.
- Aplicaciones.
- Módulos.
- Permisos.
- Roles.
- Permisos asignados a roles.
- Roles asignados a usuarios.
- Permisos individuales de usuarios.
- Excepciones para conceder o denegar permisos.
- Vigencia de permisos individuales.
- Consulta de los permisos efectivos de cada usuario.
- Historial de los cambios realizados.

La aplicación debe evitar que el administrador tenga que capturar o conocer valores IDENTITY manualmente.

En toda la interfaz se deben mostrar claves estables como:

- `codigoSistema`
- `codigoModulo`
- `codigoRol`
- `accion`
- `nominaUsuario`

Los IDs numéricos deben utilizarse únicamente de manera interna para relacionar las tablas.

Nunca deben existir IDs de aplicaciones, módulos, roles o permisos escritos directamente en el código.

============================================================
ESTRUCTURA ACTUAL DE REFERENCIA
============================================================

La base de datos utiliza aproximadamente estas tablas:

- `usuarios`
- `aplicaciones`
- `modulos`
- `permisos`
- `roles`
- `rol_permisos`
- `usuario_roles`
- `usuario_permisos`

Revisa su estructura real antes de generar consultas.

Como referencia, el modelo esperado contiene información similar a esta:

usuarios:
- nominaUsuario
- nombre
- passwordHash
- estatus
- fechaCreacion
- comentarios

aplicaciones:
- codigoSistema
- nombre
- descripcion
- activo

modulos:
- idModulo
- codigoSistema
- codigoModulo
- nombre
- ruta
- icono
- orden
- activo

permisos:
- idPermiso
- idModulo
- accion
- descripcion

roles:
- idRol
- codigoSistema
- codigoRol
- nombreRol
- descripcion
- activo

rol_permisos:
- idRol
- idPermiso

usuario_roles:
- idRol
- nominaUsuario

usuario_permisos:
- nominaUsuario
- idPermiso
- tipo
- motivo
- vigenciaHasta
- otorgadoPor
- fechaRegistro

No recrees tablas existentes ni elimines información. Si hacen falta columnas, índices o restricciones, genera una migración SQL independiente, segura e idempotente.

============================================================
MÓDULOS DE LA APLICACIÓN
============================================================

1. Dashboard

Mostrar un resumen con:

- Usuarios activos.
- Usuarios inactivos.
- Aplicaciones activas.
- Módulos registrados.
- Roles registrados.
- Permisos registrados.
- Asignaciones con vigencia próxima a terminar.
- Usuarios sin roles ni permisos.
- Posibles inconsistencias encontradas.

Incluir accesos rápidos a las operaciones principales.

2. Administración de usuarios

Permitir:

- Crear usuarios.
- Consultar usuarios.
- Modificar el nombre.
- Modificar comentarios.
- Activar o desactivar usuarios.
- Consultar sus roles.
- Consultar sus permisos individuales.
- Consultar sus permisos efectivos.
- Restablecer su contraseña.
- Modificar su contraseña desde el módulo administrativo.
- Obligar al usuario a cambiar su contraseña en el siguiente inicio de sesión, si la estructura lo permite.
- Consultar la fecha de creación.
- Consultar la fecha del último cambio de contraseña, si se agrega esa información.

Campos mínimos:

- Nómina.
- Nombre.
- Contraseña inicial.
- Confirmación de contraseña.
- Estatus.
- Comentarios.

Reglas:

- La nómina debe ser única.
- `nominaUsuario` debe manejarse como texto, no como entero.
- Nunca mostrar ni devolver `passwordHash`.
- Nunca guardar contraseñas en texto plano.
- Utilizar `password_hash()` con `PASSWORD_DEFAULT`.
- El inicio de sesión debe seguir utilizando `password_verify()`.
- Validar una longitud mínima razonable para la contraseña.
- Solicitar confirmación antes de restablecerla.
- No escribir contraseñas en logs ni respuestas de error.
- No permitir desactivar al usuario de la sesión actual.
- Evitar que se desactive al último administrador activo.
- Preferir desactivar usuarios en lugar de eliminarlos.
- Si se permite eliminación, bloquearla cuando existan relaciones o registros de auditoría.

3. Aplicaciones

Permitir:

- Crear aplicaciones.
- Editar nombre y descripción.
- Activar o desactivar.
- Consultar sus módulos y roles.
- Mostrar cantidad de módulos, roles, permisos y usuarios relacionados.

Reglas:

- `codigoSistema` debe ser único.
- No cambiar el código si ya tiene relaciones, salvo que exista una validación segura.
- No eliminar físicamente una aplicación con registros relacionados.
- Usar estatus activo/inactivo.

4. Módulos

Permitir:

- Seleccionar una aplicación.
- Crear módulos.
- Editar nombre, ruta, icono y orden.
- Activar o desactivar.
- Consultar los permisos del módulo.

Reglas:

- La combinación `(codigoSistema, codigoModulo)` debe ser única.
- No permitir módulos sin una aplicación válida.
- Mostrar siempre el código de la aplicación.
- Evitar códigos duplicados dentro de la misma aplicación.

5. Permisos

Permitir:

- Seleccionar aplicación.
- Seleccionar módulo.
- Crear permisos.
- Editar descripción.
- Consultar qué roles y usuarios tienen el permiso.
- Mostrar la clave completa, por ejemplo:

  `SRD.defectos.eliminar_defecto`

Reglas:

- La combinación `(idModulo, accion)` debe ser única.
- La acción debe almacenarse con una clave estable.
- No utilizar espacios, acentos ni caracteres ambiguos en `accion`.
- No eliminar permisos que estén asignados sin mostrar antes sus relaciones.
- Preferir una baja controlada si se agrega una columna de activo.

6. Roles

Permitir:

- Seleccionar una aplicación.
- Crear roles.
- Editar nombre y descripción.
- Activar o desactivar.
- Asignar permisos mediante una lista de checks.
- Consultar usuarios que poseen el rol.
- Copiar los permisos de un rol a otro, pero solamente si se solicita confirmación.

Reglas:

- La combinación `(codigoSistema, codigoRol)` debe ser única.
- Un rol solo puede recibir permisos pertenecientes a módulos de la misma aplicación.
- No permitir relaciones entre roles y permisos de aplicaciones diferentes.
- Las asignaciones deben guardarse dentro de una transacción.
- No eliminar un rol que tenga usuarios relacionados sin resolver primero esas relaciones.

7. Roles de usuario

Permitir:

- Buscar usuario por nómina o nombre.
- Seleccionar una aplicación.
- Mostrar únicamente los roles de esa aplicación.
- Asignar uno o varios roles.
- Quitar roles con confirmación.
- Mostrar cuándo y quién realizó la asignación, si se agregan campos de auditoría.

Reglas:

- No duplicar la combinación `(nominaUsuario, idRol)`.
- No permitir asignaciones a usuarios inactivos.
- No permitir asignar roles inactivos.
- No mostrar IDs en la interfaz.
- Guardar múltiples asignaciones dentro de una transacción.

8. Permisos individuales de usuario

Permitir:

- Seleccionar usuario.
- Seleccionar aplicación.
- Seleccionar módulo.
- Seleccionar permiso.
- Elegir el tipo:
  - `conceder`
  - `denegar`
- Capturar motivo.
- Capturar fecha de vigencia opcional.
- Consultar permisos vigentes, vencidos y permanentes.
- Modificar o retirar una excepción.

Reglas:

- La combinación `(nominaUsuario, idPermiso)` debe ser única.
- `vigenciaHasta = NULL` debe significar que no tiene vencimiento.
- No permitir una vigencia anterior a la fecha actual al crear una asignación.
- Registrar la nómina del administrador desde la sesión, nunca desde el formulario.
- No aceptar aplicaciones, módulos o permisos que no coincidan entre sí.
- No confiar en los IDs enviados por el navegador sin validarlos nuevamente.

9. Permisos efectivos

Crear una pantalla donde se pueda seleccionar un usuario y mostrar:

- Aplicaciones a las que tiene acceso.
- Roles asignados.
- Permisos obtenidos por cada rol.
- Permisos concedidos individualmente.
- Permisos denegados individualmente.
- Permisos vencidos.
- Resultado efectivo final.

La regla para resolver permisos debe ser:

1. Obtener la unión de permisos de todos los roles activos del usuario.
2. Agregar permisos individuales vigentes con tipo `conceder`.
3. Quitar permisos individuales vigentes con tipo `denegar`.
4. Ignorar aplicaciones, módulos, roles y permisos inactivos.
5. Mostrar el origen de cada permiso.

Ejemplo visual:

| Aplicación | Módulo | Acción | Resultado | Origen |
|------------|--------|--------|-----------|--------|
| SRD | defectos | eliminar_defecto | Permitido | Rol sup_manufactura |
| SRD | defectos | ver_contramedidas | Permitido | Permiso individual |
| SRD | defectos | modificar_gfuga | Denegado | Excepción individual |

Agregar filtros por aplicación, módulo, rol y resultado.

10. Auditoría

Registrar cambios administrativos como:

- Creación y modificación de usuarios.
- Activación o desactivación.
- Restablecimiento de contraseña.
- Creación o modificación de aplicaciones.
- Creación o modificación de módulos.
- Creación o modificación de roles.
- Asignación o eliminación de permisos.
- Asignación o eliminación de roles.
- Creación o eliminación de excepciones individuales.

El historial debe guardar como mínimo:

- Usuario administrador.
- Acción.
- Entidad afectada.
- Identificador lógico.
- Fecha y hora.
- Valores anteriores.
- Valores nuevos.

Nunca guardar contraseñas ni hashes dentro del historial.

Si no existe una tabla de auditoría, genera una migración independiente para crearla.

============================================================
SEGURIDAD
============================================================

La aplicación debe tener autenticación real utilizando la tabla `usuarios`.

Implementar:

- Sesiones PHP.
- Cookie `HttpOnly`.
- `SameSite=Lax` o más restrictivo si es compatible.
- `Secure` cuando se utilice HTTPS.
- `session_regenerate_id(true)` después del login.
- Cierre correcto de sesión.
- Token CSRF en todas las operaciones que modifiquen información.
- Consultas preparadas con PDO.
- Escape de datos al imprimir HTML.
- Validación tanto en frontend como en backend.
- Control de permisos en el backend.
- Respuestas JSON uniformes.
- No exponer errores internos de base de datos al usuario final.
- Registrar detalles técnicos solamente en los logs del servidor.

La autorización no debe depender de botones ocultos. Cada endpoint debe validar la sesión y el permiso requerido.

============================================================
PERMISOS DE LA PROPIA APLICACIÓN
============================================================

La aplicación de administración debe registrarse también dentro del mismo modelo de permisos.

Utiliza una clave estable configurable, por ejemplo:

- Aplicación: `ADM_ACCESOS`
- Módulo: `usuarios`
- Módulo: `aplicaciones`
- Módulo: `roles_permisos`
- Módulo: `auditoria`

Permisos sugeridos:

- `ver`
- `crear`
- `modificar`
- `activar_desactivar`
- `restablecer_password`
- `asignar_roles`
- `asignar_permisos`
- `ver_permisos_efectivos`
- `ver_auditoria`

Crea un rol estable como:

- `admin_accesos`

Genera un script de configuración inicial que:

- Registre la aplicación.
- Registre sus módulos.
- Registre sus permisos.
- Registre el rol administrador.
- Relacione el rol con sus permisos.
- Permita indicar la nómina de un usuario administrador inicial.

El script no debe utilizar IDs fijos. Debe buscar cada ID mediante:

- `codigoSistema`
- `codigoModulo`
- `codigoRol`
- `accion`

Debe ser idempotente y reejecutable.

No incluyas contraseñas reales dentro del script.

============================================================
CONSISTENCIA DE BASE DE DATOS
============================================================

Antes de agregar restricciones, revisa si existen datos duplicados o inconsistentes.

Genera consultas de diagnóstico para detectar:

- Códigos de aplicaciones duplicados.
- Módulos duplicados dentro de una aplicación.
- Acciones duplicadas dentro de un módulo.
- Roles duplicados dentro de una aplicación.
- Relaciones duplicadas.
- Roles relacionados con permisos de otra aplicación.
- Asignaciones hacia usuarios inexistentes.
- Permisos individuales vencidos.
- Usuarios sin roles ni permisos.
- Usuarios inactivos con asignaciones vigentes.

Cuando sea compatible con los datos existentes, asegurar restricciones como:

- `UNIQUE (codigoSistema, codigoModulo)`
- `UNIQUE (idModulo, accion)`
- `UNIQUE (codigoSistema, codigoRol)`
- `UNIQUE (idRol, idPermiso)`
- `UNIQUE (nominaUsuario, idRol)`
- `UNIQUE (nominaUsuario, idPermiso)`

Usar llaves foráneas y transacciones.

No corregir ni eliminar automáticamente datos existentes sin mostrar primero un diagnóstico claro.

============================================================
DISEÑO Y EXPERIENCIA DE USUARIO
============================================================

La aplicación será utilizada por personal administrativo con distintos niveles de experiencia tecnológica.

Debe tener:

- Diseño limpio, moderno y profesional.
- Navegación sencilla.
- Formularios claros.
- Tablas con DataTables.
- Búsqueda y filtros.
- Etiquetas visuales para activo, inactivo, concedido, denegado y vencido.
- Modales solo cuando simplifiquen la interacción.
- Confirmaciones claras para acciones sensibles.
- Diseño responsivo.
- Botones principales visibles.
- Estados de carga.
- Mensajes de éxito y error.
- Validaciones visuales.
- Evitar pantallas saturadas.

Reutiliza la apariencia, colores, componentes y utilidades existentes en los demás proyectos.

============================================================
REGLAS DE IMPLEMENTACIÓN
============================================================

1. No modifiques las aplicaciones existentes para construir esta herramienta.
2. Crea un proyecto independiente.
3. No cambies nombres de tablas o columnas existentes sin justificarlo.
4. No elimines datos.
5. No agregues dependencias.
6. Mantén separadas:
   - presentación;
   - JavaScript;
   - operaciones PHP;
   - consultas SQL.
7. No confíes en valores enviados desde el frontend.
8. Usa claves estables y no IDs fijos.
9. Usa transacciones para asignaciones múltiples.
10. Conserva compatibilidad con SQL Server.
11. Utiliza código sencillo y fácil de mantener.
12. Evita una arquitectura innecesariamente compleja.
13. Incrementa las versiones `?v=` de CSS y JavaScript cuando se modifiquen.
14. Documenta el orden de ejecución de las migraciones.
15. No realices cambios fuera de la nueva aplicación y de los scripts necesarios.

============================================================
ORDEN DE TRABAJO
============================================================

Trabaja por etapas:

Etapa 1:
- Analizar la estructura actual.
- Identificar tablas, columnas, restricciones e índices.
- Mostrar un resumen de lo encontrado.
- Indicar los archivos que será necesario crear o modificar.
- Señalar cualquier diferencia con este requerimiento.

Etapa 2:
- Generar scripts de diagnóstico.
- Generar migraciones idempotentes.
- Generar el script de configuración inicial.
- No eliminar ni modificar datos históricos.

Etapa 3:
- Implementar autenticación, sesión y autorización.
- Implementar layout, navegación y dashboard.

Etapa 4:
- Implementar usuarios y administración de contraseñas.

Etapa 5:
- Implementar aplicaciones, módulos, permisos y roles.

Etapa 6:
- Implementar asignaciones de roles y permisos individuales.

Etapa 7:
- Implementar permisos efectivos y auditoría.

Etapa 8:
- Ejecutar validaciones de sintaxis.
- Revisar consultas SQL.
- Probar los flujos principales.
- Entregar una guía de instalación y pruebas manuales.

Antes de iniciar cada etapa, explica brevemente qué archivos modificarás. No hagas reestructuraciones generales ni funcionalidades fuera del alcance.

============================================================
CRITERIOS DE ACEPTACIÓN
============================================================

La aplicación se considerará terminada cuando:

- Un administrador pueda crear y modificar usuarios.
- Las contraseñas se almacenen exclusivamente como hashes seguros.
- Se puedan activar y desactivar usuarios.
- Se puedan restablecer contraseñas.
- Se puedan crear aplicaciones, módulos, permisos y roles.
- Se puedan asignar permisos a roles.
- Se puedan asignar roles a usuarios.
- Se puedan conceder o denegar permisos individuales.
- Se pueda definir vigencia.
- Se puedan consultar los permisos efectivos.
- No se utilicen IDs fijos en el código.
- No se puedan crear relaciones entre aplicaciones diferentes.
- Todas las operaciones sensibles estén protegidas en el backend.
- Los cambios administrativos queden auditados.
- Los scripts SQL sean idempotentes.
- No se afecte la información existente.
- La interfaz sea clara, responsiva y consistente con los demás proyectos.
- Se entregue una guía de instalación, migración y prueba.