# Usuarios, roles y permisos — cómo está construido y por qué

Contexto para desarrollar una aplicación que comparta el esquema de autenticación alojado en
`ELECTRONICOSDB`. La implementación de referencia es el sistema de Puntos de Cambio
(`codigoSistema = 'PC'`), en PHP con PDO sobre SQL Server, pero el modelo es agnóstico del
lenguaje.

---

## 1. Qué problema resuelve

La empresa tiene varias aplicaciones internas y **las mismas personas usan varias de ellas**.
Si cada aplicación tuviera su propia tabla de usuarios, la misma persona tendría cuatro
contraseñas distintas, y dar de baja a alguien implicaría recordar en cuántos sistemas está.

Por eso se separó en dos planos:

- **La identidad es global.** Una persona, una nómina, una contraseña, una tabla `usuarios`
  para todas las aplicaciones.
- **La autorización es por aplicación.** Lo que esa persona puede hacer se define por separado
  en cada sistema.

La columna `codigoSistema` es la que separa ambos planos, y es el concepto central del modelo:
todo lo que describe *qué se puede hacer* cuelga de un sistema; lo que describe *quién es
alguien* no.

El segundo problema que resuelve es la **granularidad**. En el sistema de referencia, una sola
pantalla —el seguimiento de anormalidades— recibe registros de cinco departamentos distintos:
calidad captura sus confirmaciones, mantenimiento su revisión de equipo, manufactura el
análisis de operación, y cada uno solo debe poder escribir en su parte. Un modelo de "puede
entrar / no puede entrar" no alcanza para eso.

---

## 2. Las ocho tablas y para qué existe cada una

### `aplicaciones` — el catálogo de sistemas
Una fila por aplicación. Existe para que `codigoSistema` no sea texto libre repetido en varias
tablas sin autoridad: es la llave primaria que las demás referencian.

```sql
codigoSistema nvarchar(50)  NOT NULL  -- PK: 'PC', 'SGC', ...
nombre        nvarchar(100) NOT NULL
descripcion   nvarchar(250) NULL
activo        bit           NOT NULL DEFAULT (1)   -- CHECK (0 ó 1)
```

### `usuarios` — la identidad, compartida
Quién es cada persona y cómo se autentica. **No tiene nada de una aplicación en particular.**

```sql
nominaUsuario  nvarchar(15)   NOT NULL  -- UNIQUE; es el número de nómina
nombre         nvarchar(100)  NOT NULL
passwordHash   nvarchar(max)  NOT NULL  -- bcrypt/argon2, nunca texto plano
estatus        nvarchar(10)   NOT NULL  -- 'activo' habilita el ingreso
fechacreacion  date           NULL
comentarios    nvarchar(1000) NULL
```

`nominaUsuario` es texto y no entero, lo que permite cuentas de sistema con códigos no
numéricos. Las llaves foráneas de las otras tablas apuntan a la restricción `UNIQUE`; conviene
promoverla a `PRIMARY KEY`, que es lo que corresponde semánticamente.

### `modulos` — las secciones de cada aplicación
Una fila por pantalla o sección. Cumple **dos funciones a la vez**, y por eso tiene columnas
que parecen de presentación:

1. Agrupa los permisos: cada permiso pertenece a un módulo.
2. Define el menú de navegación, con `ruta`, `icono` y `orden`.

```sql
idModulo      int IDENTITY  NOT NULL  -- PK
codigoSistema nvarchar(50)  NOT NULL  -- FK -> aplicaciones
codigoModulo  nvarchar(50)  NOT NULL  -- 'anormalidades', 'reportes', ...
nombre        nvarchar(100) NOT NULL  -- texto visible en el menú
ruta          nvarchar(100) NULL      -- '#/anormalidades'
icono         nvarchar(50)  NULL      -- 'bi-exclamation-triangle'
orden         int           NOT NULL DEFAULT (0)
activo        bit           NOT NULL DEFAULT (1)   -- CHECK (0 ó 1)
-- UNIQUE (codigoSistema, codigoModulo)
```

La unicidad es del **par**: dos aplicaciones distintas sí pueden tener un módulo `reportes`.

### `permisos` — las acciones del sistema
**Aquí está el corazón del modelo.** Un permiso es la combinación de un módulo y una acción.

```sql
idPermiso   int IDENTITY  NOT NULL  -- PK
idModulo    int           NOT NULL  -- FK -> modulos
accion      nvarchar(30)  NOT NULL  -- el paso concreto del flujo
descripcion nvarchar(250) NULL
-- UNIQUE (idModulo, accion)
```

La columna **`accion`** guarda el identificador de lo que se puede hacer, y la restricción
`UNIQUE (idModulo, accion)` garantiza que dentro de un módulo ese nombre sea único y por tanto
sin ambigüedad. Un módulo puede tener todas las acciones que necesite: la sección 4 explica
cómo nombrarlas.

### `roles` — los paquetes de permisos
Un rol es un puesto: *supervisor de calidad*, *gerente de manufactura*, *operador*. No es más
que un nombre al que se le cuelgan permisos.

```sql
idRol         int IDENTITY  NOT NULL  -- PK
codigoSistema nvarchar(50)  NOT NULL  -- FK -> aplicaciones
codigoRol     nvarchar(50)  NOT NULL  -- 'sup_calidad', 'ger_manufactura', ...
nombreRol     nvarchar(100) NOT NULL
descripcion   nvarchar(250) NULL
activo        bit           NOT NULL DEFAULT (1)
-- UNIQUE (codigoSistema, codigoRol)
```

`codigoRol` se escribe sin acentos ni diagonales porque aparece literal en el código y en las
consultas.

### `rol_permisos` — qué trae cada rol
La tabla donde vive la política: qué puede hacer cada puesto.

```sql
idRol     int NOT NULL  -- FK -> roles
idPermiso int NOT NULL  -- FK -> permisos
-- PK (idRol, idPermiso)
```

Cambiar quién puede qué es un `INSERT` o un `DELETE` aquí, **sin tocar código**.

### `usuario_roles` — qué puesto tiene cada persona
```sql
idUR          int IDENTITY NOT NULL  -- PK
idRol         int          NOT NULL  -- FK -> roles
nominaUsuario nvarchar(15) NOT NULL  -- FK -> usuarios
-- UNIQUE (nominaUsuario, idRol)
```

Es muchos-a-muchos a propósito: **una persona puede tener varios roles** y sus permisos son la
unión de todos. Alguien que es supervisor de manufactura y además inspector de calidad recibe
dos roles, sin necesidad de inventar un rol combinado.

### `usuario_permisos` — las excepciones individuales
```sql
idUP          int IDENTITY  NOT NULL  -- PK
nominaUsuario nvarchar(15)  NOT NULL  -- FK -> usuarios
idPermiso     int           NOT NULL  -- FK -> permisos
tipo          nvarchar(10)  NOT NULL  -- CHECK: 'conceder' | 'denegar'
motivo        nvarchar(250) NULL
vigenciaHasta date          NULL      -- NULL = permanente
otorgadoPor   nvarchar(15)  NULL
fechaRegistro datetime2(0)  NOT NULL DEFAULT (SYSDATETIME())
-- UNIQUE (nominaUsuario, idPermiso)
```

Por qué existe: sin ella, darle a **una sola persona** un permiso extra obligaría a modificar
su rol, y eso se lo daría a todos sus compañeros. El caso típico es una cobertura temporal.

Tres decisiones de su diseño:

- **`denegar` además de `conceder`**, para el caso inverso: quitarle temporalmente un permiso
  a alguien sin quitarle el rol completo.
- **`vigenciaHasta`**, porque en planta casi toda excepción es temporal. Que caduquen solas es
  lo que evita que se acumulen para siempre sin que nadie recuerde por qué.
- **`UNIQUE (nominaUsuario, idPermiso)`**, que impide tener `conceder` y `denegar` a la vez
  sobre el mismo permiso: no hay ambigüedad posible.

---

## 3. El flujo de alta

Para poner en marcha una aplicación nueva, en este orden:

**1. Registrar la aplicación** en `aplicaciones`, con su `codigoSistema`.

**2. Crear sus módulos** en `modulos`, uno por pantalla, con su ruta, icono y orden si el menú
se va a generar desde la base.

**3. Crear sus permisos** en `permisos`. Por cada módulo:
   - la acción de entrada (`ver`), que decide si la pantalla se entrega;
   - una acción por cada punto de captura de la interfaz.

**4. Crear los roles** en `roles` y armar `rol_permisos` con lo que trae cada uno.

**5. Asignar personas** en `usuario_roles`. Los usuarios **ya existen**: no se crean de nuevo
   para cada sistema.

**6. En el código**: una constante con el `codigoSistema`, la consulta de resolución en el
   login, y las guardas en vistas y endpoints.

Los pasos 2 a 5 son datos, no código. Ampliar la política después no requiere desplegar nada.

---

## 4. Cómo se nombran las acciones, y por qué el código no usa IDs

### La acción es el paso del flujo, no un verbo CRUD

Esta es la decisión de diseño más importante y la que más conviene respetar.

Mal:
```
anormalidades.registrar     ← ¿registrar qué? El módulo tiene 15 puntos de captura
anormalidades.editar
```

Bien:
```
anormalidades.registro_inicial
anormalidades.primera_revision
anormalidades.analisis_pe          ← mantenimiento
anormalidades.analisis_mf          ← manufactura
anormalidades.analisis_qa          ← calidad
anormalidades.cierre_gerente
anormalidades.reincidencia_registrar
```

Con verbos genéricos, `(anormalidades, registrar)` sería ambiguo —¿la detección o la
reincidencia?— y la restricción `UNIQUE` impediría tener las dos. Con el paso del flujo como
nombre, cada permiso identifica una sola cosa y `UNIQUE (idModulo, accion)` lo garantiza.

Cada acción corresponde a **un botón de la interfaz y un endpoint del servidor**. Si un módulo
tiene quince puntos de captura, tiene quince acciones más la de consulta.

### La acción `ver`

Por convención **todo módulo tiene la acción `ver`**: es el permiso de entrada. Decide si la
pantalla se entrega y si el módulo aparece en el menú. Un módulo sin ella queda inalcanzable.

Consulta de control, debe salir vacía:
```sql
SELECT m.codigoModulo, m.nombre
FROM dbo.modulos AS m
WHERE m.codigoSistema = @sistema
  AND NOT EXISTS (SELECT 1 FROM dbo.permisos AS p
                  WHERE p.idModulo = m.idModulo AND p.accion = N'ver');
```

### Por qué el código compara contra texto y no contra `idPermiso`

Dentro de la base, las relaciones usan enteros: `rol_permisos` y `usuario_permisos` guardan
`idPermiso`, y está bien así.

Pero **el código nunca compara contra ese entero.** Compara contra la clave de texto
`codigoModulo.accion`, por ejemplo `anormalidades.analisis_qa`.

La razón es concreta: `idPermiso` es `IDENTITY`, y los `IDENTITY` **no son portables entre
bases de datos**. Si la base de producción se siembra por separado, o las filas se insertan en
otro orden, el `idPermiso = 9` puede ser `analisis_qa` en desarrollo y `analisis_mf` en el
servidor. El código no fallaría: seguiría funcionando, **autorizando la acción equivocada**.
Ese es el peor tipo de error, porque no avisa.

El par `codigoModulo.accion`, en cambio, vale lo mismo en todas las instalaciones porque lo
define el script de semillas y no el motor. Y de paso el código se lee sin abrir la base:

```php
exigirPermiso('anormalidades', 'analisis_qa');   // se entiende solo
exigirPermiso(9);                                 // ¿qué es 9?
```

**Corolario para los scripts de carga**: nunca escriben IDs a mano, los resuelven por código
con un `JOIN`. Así el mismo script corre igual en cualquier base.

```sql
INSERT INTO dbo.permisos (idModulo, accion, descripcion)
SELECT m.idModulo, v.accion, v.descripcion
FROM (VALUES
    (N'anormalidades', N'ver',              N'Consultar el listado'),
    (N'anormalidades', N'registro_inicial', N'Registrar la detección')
) AS v(codigoModulo, accion, descripcion)
INNER JOIN dbo.modulos AS m
        ON m.codigoModulo = v.codigoModulo AND m.codigoSistema = N'PC'
WHERE NOT EXISTS (SELECT 1 FROM dbo.permisos AS p
                  WHERE p.idModulo = m.idModulo AND p.accion = v.accion);
```

Todos los scripts del esquema son **idempotentes**: se pueden volver a correr sin duplicar.

---

## 5. Las dos formas de que alguien tenga un permiso

Un usuario llega a un permiso por **dos caminos independientes**:

**Por rol — `usuario_roles` → `roles` → `rol_permisos`.** Es la vía normal. Describe puestos
que se repiten: si mañana entra otro supervisor de calidad, se le asigna el mismo rol y queda
configurado.

**Por excepción — `usuario_permisos`.** Salta el rol y afecta a una sola persona. Sirve cuando
alguien necesita algo puntual que su puesto no incluye, o cuando hay que retirarle algo
temporalmente.

### La regla de precedencia

Es fija y no debe cambiar:

> **denegar** gana sobre **conceder**, que gana sobre lo que da el **rol**.

Una negación explícita siempre manda. Es el comportamiento predecible y el seguro por defecto.

### Cuándo usar cuál

| Situación | Solución |
|---|---|
| La combinación describe un **puesto** que se va a repetir | Un rol (o dos roles) |
| Es **una persona concreta**, por una circunstancia particular | Excepción |
| Es temporal: cobertura, capacitación, suplencia | Excepción con `vigenciaHasta` |

Si te descubres creando la misma excepción para la tercera persona, era un rol.

### Un usuario sin rol pero con excepción

Resuelve exactamente los permisos que le concedan sus excepciones vigentes. Eso permite el caso
legítimo de alguien que necesita una sola pantalla sin inventarle un puesto. La contrapartida
es que su política deja de verse en el catálogo de roles, así que conviene usarlo con
cuentagotas.

---

## 6. Cómo se valida

### 6.1 Tres capas, y solo una protege

| Capa | Cuándo corre | Qué hace |
|---|---|---|
| **Resolución** | Una vez, al iniciar sesión | Convierte rol + excepciones en una lista plana en la sesión |
| **Guardas del servidor** | En cada petición | Comparan contra esa lista y cortan con 403. **La única que protege** |
| **Pistas de la interfaz** | Al dibujar la pantalla | Oculta menús, deshabilita bloques. Comodidad, no seguridad |

Ocultar un botón no protege nada: quien conozca la dirección del endpoint puede llamarlo igual
desde las herramientas del navegador.

### 6.2 El inicio de sesión, paso a paso

El orden importa. **Los permisos se resuelven antes de abrir la sesión**, para no dejar una
sesión a medias que sí pasaría el filtro de las páginas:

1. Validar que lleguen usuario y contraseña.
2. Buscar la nómina en `usuarios`.
3. `password_verify()` contra `passwordHash`.
   Si el usuario no existe **o** la contraseña es incorrecta → **un solo mensaje genérico**.
   Distinguirlos permitiría averiguar qué nóminas están dadas de alta.
4. Verificar que `estatus` sea `activo`.
5. **Resolver los permisos efectivos** (consulta de 6.3).
6. Si la lista queda **vacía** → rechazar con un mensaje propio. Cubre al usuario sin rol y al
   que solo tiene roles de otra aplicación.
7. `session_regenerate_id(true)`, contra fijación de sesión.
8. Guardar en la sesión: usuario, permisos y roles.

### 6.3 La consulta de resolución

`permisos efectivos = (los del rol) ∪ (concedidos vigentes) − (denegados vigentes)`

```sql
WITH DelRol AS (
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
ORDER BY m.codigoModulo, p.accion;
```

Lo que codifica, además de la precedencia:

- Las excepciones **vencidas se ignoran** sin borrarse: quedan en la tabla con su motivo.
- Un rol o un módulo con `activo = 0` deja de aportar permisos sin borrar filas.
- El filtro por `codigoSistema` aparece **dos veces**, para roles y para módulos.

> Los parámetros repetidos llevan nombre distinto (`:nomina` / `:nominaExcepcion`) porque la
> conexión trabaja con sentencias preparadas del lado del cliente.

Esta consulta corre **una sola vez por sesión**. De ahí en adelante autorizar no toca la base.

### 6.4 El contrato de la sesión

```php
$_SESSION['usuario']  = ['nomina' => '11607', 'nombre' => '...', 'inicio' => 'Y-m-d H:i:s'];
$_SESSION['permisos'] = ['anormalidades.ver', 'anormalidades.analisis_qa', ...];
$_SESSION['roles']    = ['sup_calidad', 'ger_calidad'];
```

- **`permisos`** responde *qué puede hacer*.
- **`roles`** responde *quién es*. Se guarda aparte porque algunas validaciones dependen del
  puesto y no de la acción (sección 7).
- La identidad que se grabe en cualquier registro sale de **`$_SESSION['usuario']`**, nunca del
  formulario. Lo que manda el navegador es falsificable.

### 6.5 Las guardas

Ninguna toca la base: todas leen el arreglo de la sesión.

```php
/* Consulta sin interrumpir. Para decidir si se dibuja un botón o una opción del menú. */
function tienePermiso(string $modulo, string $accion): bool
{
    return in_array($modulo . '.' . $accion, $_SESSION['permisos'] ?? [], true);
}

/* Guarda de las vistas. Primera línea de cada página de módulo. */
function exigirAcceso(string $modulo): void
{
    if (tienePermiso($modulo, 'ver')) { return; }
    http_response_code(403);
    echo '<!-- aviso de "sin acceso", en el formato que espere el front -->';
    exit;
}

/* Guarda de la API. Primera línea de cada endpoint. */
function exigirPermiso(string $modulo, string $accion): void
{
    if (tienePermiso($modulo, $accion)) { return; }
    http_response_code(403);
    echo json_encode(['status'  => 'error',
                      'mensaje' => 'No tienes permiso para realizar esta acción.'],
                     JSON_UNESCAPED_UNICODE);
    exit;
}

/* Identidad de quien opera, para lo que se grabe en la base. */
function nominaEnSesion(): string { /* $_SESSION['usuario']['nomina'] */ }
function nombreEnSesion(): string { /* $_SESSION['usuario']['nombre'] */ }
function tieneRol(string $codigoRol): bool { /* in_array en $_SESSION['roles'] */ }
```

### 6.6 Las dos puertas

Hay exactamente **dos formas** de llegar a algo protegido, y cada una tiene su guarda:

| Puerta | Dónde va | Guarda | Respuesta si falta |
|---|---|---|---|
| Entrar a un módulo | Inicio del archivo de la vista | `exigirAcceso('modulo')` | HTML con el aviso |
| Ejecutar una acción | Primera línea del endpoint | `exigirPermiso('modulo', 'accion')` | JSON 403 |

**Cerrar la primera no cubre a la segunda.** Quien tenga acceso al módulo entra a la pantalla,
pero cada operación debe validarse por separado.

Las guardas también cortan cuando **no hay sesión**, porque un arreglo inexistente nunca
contiene el permiso buscado. Fallan cerrado.

---

## 7. Lo que el permiso no puede expresar

El permiso responde *"¿puede hacer esta acción?"*, no *"¿puede hacerla sobre **este**
registro?"*. Lo segundo depende de los datos y va en la lógica de negocio, no en el catálogo.

Ejemplo del sistema de referencia: el permiso `anormalidades.cierre_gerente` habilita a
autorizar cierres, pero **cuál** gerente firma lo decide el área responsable que quedó asignada
a ese registro. La validación consulta ese dato y lo compara contra los roles de la sesión:

```php
const GERENTE_POR_FACTOR = [
    'MFT' => 'ger_manufactura',
    'QA'  => 'ger_calidad',
    'PE'  => 'ger_mantenimiento'
];

// ... se obtiene la clave del área responsable del registro ...
if (!tieneRol(GERENTE_POR_FACTOR[$claveArea] ?? '')) {
    // 403 con un mensaje que nombre el área
}
```

Este eje —**alcance de datos**— es independiente de los permisos, y conviene mantenerlo así:
intentar meterlo en el catálogo lo vuelve inmanejable.

---

## 8. Cinco errores que dejan el modelo sin efecto

1. **Ocultar el botón y no poner la guarda.** La pantalla se ve correcta, pero el endpoint
   queda abierto a cualquiera que sepa su dirección.

2. **Confiar en la identidad que manda el cliente.** Si el endpoint graba la nómina que viene
   por POST, cualquiera puede registrar a nombre de otro. Debe salir de la sesión.

3. **Olvidar un endpoint.** Cada operación sin su `exigirPermiso()` queda abierta y nada avisa.
   Conviene revisar la lista completa de endpoints de cada archivo al terminar.

4. **Omitir `codigoSistema` en una consulta.** Mezcla la política de dos aplicaciones sin
   producir ningún error visible.

5. **Comparar contra `idPermiso` en el código.** Ver sección 4: autoriza lo equivocado en
   silencio al cambiar de base.

---

## 9. Limitaciones conocidas

- **Los permisos se cachean en la sesión.** Un cambio en la base no surte efecto hasta que la
  persona vuelva a iniciar sesión. Para una aplicación de planta con turnos suele estar bien;
  si hiciera falta revocación inmediata, habría que revalidar contra la base en cada petición
  o manejar una marca de versión.

- **Las excepciones se acumulan en silencio.** Por eso `usuario_permisos` tiene `motivo` y
  `vigenciaHasta`. Conviene llenar siempre el motivo y preferir excepciones temporales. Una
  pantalla que liste las vigentes es lo que mantiene esto auditable.

- **La relación entre un permiso y el rol que debería tenerlo no está enforzada.** Nada impide
  asignar `analisis_qa` a mantenimiento; es una decisión de datos. Conviene tener una consulta
  de revisión que muestre qué roles tienen cada acción:

```sql
SELECT p.accion,
       STRING_AGG(r.codigoRol, ', ') WITHIN GROUP (ORDER BY r.codigoRol) AS roles
FROM dbo.permisos AS p
     INNER JOIN dbo.modulos AS m       ON m.idModulo   = p.idModulo
     INNER JOIN dbo.rol_permisos AS rp ON rp.idPermiso = p.idPermiso
     INNER JOIN dbo.roles AS r         ON r.idRol      = rp.idRol
WHERE m.codigoSistema = @sistema
GROUP BY p.accion
ORDER BY p.accion;
```

Es la política de autorización completa en una pantalla, y vale la pena revisarla con calma
cada vez que cambie.
