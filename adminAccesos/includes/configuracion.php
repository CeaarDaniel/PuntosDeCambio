<?php
/**
 * includes/configuracion.php — Valores fijos de la aplicación.
 *
 * Un solo lugar para los nombres y límites que usan varias pantallas y
 * endpoints. No contiene credenciales: la conexión vive en api/conexion.php.
 */

date_default_timezone_set('America/Mexico_City');

/* Nombre visible en el título de la pestaña, el header y el footer. */
const APP_NOMBRE = 'Administración de accesos';

/* Clave estable de esta aplicación dentro del propio modelo de permisos.
   Todavía no se usa: la validación de permisos se definirá al final del
   desarrollo (ver PENDIENTES.md §3). */
const CODIGO_SISTEMA = 'ADM_ACCESOS';

/* Reglas de las contraseñas. La longitud mínima la confirmó el usuario; la
   máxima evita procesar cadenas enormes (bcrypt solo usa los primeros 72 bytes). */
const PASSWORD_LONGITUD_MINIMA = 8;
const PASSWORD_LONGITUD_MAXIMA = 72;

/* Longitud de usuarios.nominaUsuario en la base. */
const NOMINA_LONGITUD_MAXIMA = 15;

/* Días hacia adelante para considerar que una excepción individual está
   "próxima a vencer" en el dashboard. */
const DIAS_VIGENCIA_PROXIMA = 15;

/* ==========================================================================
   Claves estables. Un solo lugar para el formato de cada una: el mismo patrón
   se usa en el atributo pattern de los formularios (sin anclas) y en PHP
   (con patronClave(), que agrega las anclas).

   - codigoSistema: mayúsculas, dígitos y guion bajo ('PC', 'SRD', 'ADM_ACCESOS').
   - codigoModulo:  admite camelCase porque así están los módulos existentes
                    ('gestionLinea', 'menuReportes').
   - codigoRol y accion: minúsculas, dígitos y guion bajo ('sup_calidad',
                    'analisis_qa'), como indica autenticacion-y-permisos.md.
   Las longitudes son las de las columnas de la base.
   ========================================================================== */
const PATRON_CODIGO_SISTEMA = '[A-Z][A-Z0-9_]*';
const PATRON_CODIGO_MODULO  = '[A-Za-z][A-Za-z0-9_]*';
const PATRON_CODIGO_ROL     = '[a-z][a-z0-9_]*';
const PATRON_ACCION         = '[a-z][a-z0-9_]*';

const CODIGO_LONGITUD_MAXIMA = 50;   // codigoSistema, codigoModulo, codigoRol
const ACCION_LONGITUD_MAXIMA = 30;   // permisos.accion

/* Clases de Bootstrap Icons ('bi-diagram-3'). */
const PATRON_ICONO = 'bi-[a-z0-9-]+';

/**
 * Convierte un patrón de los de arriba en una expresión regular de PHP
 * que exige que el texto completo coincida.
 */
function patronClave(string $patron): string
{
    return '/^' . $patron . '$/';
}
