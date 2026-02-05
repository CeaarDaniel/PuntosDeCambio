/*  SISTEMA SAPC
    Registros por línea, para cada línea se captura para turno 1 y 2:
    Asistencia
    Agregar una seccion done el operaor realice una prueba para que este se libere o certifique en alguna operacion

    Condiciones 
    El operador debe de estar certificado(ILU) o liberado en la operacion
    Si a un operador se le retira su certificacion en una operacion ya no se puede volder a certificar
    
    PUNTOS DE CAMBIO
    En el diseño actual falta
        Dar seguimiento a las estaciones asignadas a los operadoras (consultar en que estacion esta)
            --De momento lo dejara para que sea visible solo en el layout, se supone que en una estacion solo debe de
              haber una sola persona a si que con el layout debe de ser suficiente.
            --en la info del operadro asignado agregar un boton que permita hacer el registro de una asignacion. 
              
              Tener esta tabla tambien puede servir para ver la cantidad de gente en el area independientete de si esta o no en 
              alguna estacion o linea, o si esta faltando por algun motivo como una incapacidad.
              (colocar la persona y en que linea o si no esta en ninguna linea, e irlas eliminando al agregarla a alguna estacion y 
              agregarlas a esta tabla si se quitan de todas las estaciones asignadas por ejemplo
                Se ha elimiado a esta persona del registro de todas las estaciones, ¿desea agregalo al listado de personal disponible o personal no asignado?)
        
        Registro del punto de cambio.
        Mejorar la manera en la que se le da seguimiento a los puntos de cambio (consulta, liberacion o finalizacion)
            --Si el punto de cambio seleccionado (4M) esta abierto mostrar la ventana de finalizacion de punto de cambio en vez de la pantalla de registro 
                (en caso de que no lo quieran asi podria agregar la opcion de registrar otro punto de cambio o finalizar ese punto de cambio dentro de ese modal)
            --Si es necesario dar seguimiento a todo el proceso: agregar paginacion y un estatus a cada tab para dar seguimietno a todo el proceso que se sigue en el formato
            --Hay que validar que la persona que se va ha colocar para el punto de cambio este capasitada para la operacion

        Registro de asistencia 
            --Tal vez en un futuro sea mejor crear una lista de personal, que sea especifica para la persolnas quue estan
              en el area de sensor o electronicos para registrar informacion mas especifica y poder hacer consultas mas rapido
              ya existe una pero es general de varias plantas

        Agregar seccion de registrar y visualizar al personal disponible.
         --Restringir la asignacion a esta tabla si la persona se encuentra aisgnada a alguna otra estacion

    ¿Si la operacion o en la estacion no es necesaria una certificacion como se libera o comprueba el conocimiento del operador en a linea?

    --Agregar alguna alerta o etiqueta de warning para mostrar cuando el PC lleva ya 30 días
    --validar la asignacion de un trabajador en una estacion cuando se registra un punto de cambio
    --modificar el diseño para que sea mas rapido como se asignan y se eliminan los operadores de las estaciones, tal vez haciendo esto dentro del mismo modal de la estacion
    --mostrar en el el layout algun indicador si el trabajadore falto o asistio
*/  

//Registro de asistencia del operador
//Validar ultima fecha de operacion (con la asistencia)
//Validar certificaciones 
//Cambio de turno
//Reflejar cambios y registros incertados en el layout

//Generar mostrar el contenido dinamico de los formularios
//Hacer restricciones y validaciones correspondientes
//Generar permisos de usuarios
//Generar usuarios
//Registrar y Retirar certificacion 
//Validar capacitaciones

//Pruebas
//Falta registrar las fechas con la hora
//Falta validar que el empleado no este dado de baja 

//Continuar con la parte de las certificaciones
//Hacer el contenido responsivo

//Dar opcion de registrar a un operador sin punto de cambio o no si no existe la ultima fecha de operacion en la linea
//esto para al inicio cunado se empieza a registrar a todo el personaol 
//No limpiar todos los campos de los formularios solo los que son ingresados por el usuario
//Tal vez a futuro seria buen o agregar una opcion que diga mover operaedor de estacion
//Modifcar el codigo para generar el consecutivo de PC para que sea por linea y no uno general
//Agregar opcion para consultar el acomodo del layout guardado por dia o fecha
//Eliminar(ocultar ) una estacion

//Y dejar otra opcion para que se puede finalizar y/o asignar al operador como titular de la estacion
//Falta agregar el filtro de turno a las consultas

//Revisar que pasa con las personas cuando no estan trabajando en la estacion y tienen un punto de cambio
//Si a una estacion se le crea un punto de cambio mostrara la persona del punto de cambio pero no mostrara a la persona 
//que esta como titular de la estacion, si esta no esta porque se le sansiono o suspendio no se le va a poder hacer el registro de la sistencia 
//Tal vez seria conveniente mostrar todas las personas y dar prioridad a mostrar los PC y si el titular 
// no esta en ninguna estacion mas que en la estacion con el PC mostrar la persona que esta como PC y al titular
//indicando si es titular o PC

//De momento solo guardare el registro de asistencia del personal sin las estaciones, pero hay que considerar lo siguiente: 
//como se guardaria la asistencia o el registro del layout si la persona esta asignada en dos estaciones sin que excista un punto de cambio
//si no que esta como titular
//o tener dos tabla uno para el registro de la estacion y otro del acomodo del layout 
//otra opcion seria guardar la sistencia colocando todas las estaciones del trabajador en vez de mostrar solo una
//Agregar una tabla para guardar el historial del layut para que se registre el comodo con todo el data de las estaciones al presionar guardar layout

//QUITAR EL INPUT DE OBSERVACIONES de la tabla de asistencia
//Agregar una restriccion para no poder registrar la asistencia si no se esta dentro del orario correspondiente al turno
//Agregar la funcion de recargar la lista de asistencia al cambiar el turno