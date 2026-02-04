/*
    SISTEMA SAPC
    Registros por línea, para cada línea se captura para turno 1 y 2:

    Estaciones 
    Titular o personal asignado a cada estación
    Punto de cambio de la estación
    Acomodo del layout de las estaciones
    Asistencia

    Agregar una seccion done el operaor realice una prueba para que este se libere o certifique en alguna operacion

    Condiciones 
    Una persona debe podese rasignar a varias estaciones
    El operador debe de estar certificado(ILU) o liberado en la operacion
    Si a un operador se le retira su certificacion en una operacion ya no se puede volder a certificar
    No todas las lineas requieren de certificacion solo algunas
    Para que una persona este en alguna estacion tiene que estar liberada en el procesos no siempre es necesaria 
    la certificacion


    Informacion relevante para capturar
    Los puntos de cambio pueden ser programados o inesperados
    Cada punto de cambio tiene un numero consecutivo que se reinicia cada mes
    Es importante colocar la hora de inicio de la operacion del punto de cambio por los defectos que 
    se puedan generar en el tiempo del transucrso del punto de cambio
    Indicar si una estacion requiere una certificacion o no
*/

//Limitarse a 


  //Registro de asistencia del operador
  //Validar ultima fecha de operacion (con la asistencia)

  //Validar certificaciones 
/*  
    PUNTOS DE CAMBIO
  
    En el diseño actual falta

        Dar seguimiento a las estaciones asignadas a los operadoras (consultar en que estacion esta)
            --De momento lo dejara para que sea visible solo en el layout, se supone que en una estacion solo debe de
              haber una sola persona a si que con el layout debe de ser suficiente.
            --Podria poner dentro del modal de la estacion una opcion en el tab o en el menu para mostrar la infromacion de la persona asignada, 
              aqui podria mostrar su nivel de certificacion o capacitacion (ILU), con su fecha de vencimiento, datos sobre el operador y un boton para finalizar la
              asignacion a esta linea y otro boton que permita hacer el registro de una asignacion. 
            --Voy a ha agregar una opcion para registrar personal disponible, en caso de que haya personal no asignado a una estacion
              pero que este en la linea y asociar a la persona con la linea y poder hacer el registro de asistencia, asignado el 
              operador.
              
              Tener esta tabla tambien puede servir para ver la cantidad de gente en el area independientete de si esta o no en 
              alguna estacion o linea, o si esta faltando por algun motivo como una incapacidad.
              (colocar la persona y en que linea o si no esta en ninguna linea, e irlas eliminando al agregarla a alguna estacion y 
              agregarlas a esta tabla si se quitan de todas las estaciones asignadas por ejemplo
                Se ha elimiado a esta persona del registro de todas las estaciones, ¿desea agregalo al listado de personal disponible o personal no asignado?)
        
        Registro del punto de cambio.
        Mejorar la manera en la que se le da seguimiento a los puntos de cambio (consulta, liberacion o finalizacion)
            --Para otros pc agrergar un tab u otro menu en el modal para registrar distintos tipos de PC
            --Si el punto de cambio seleccionado (4M) esta abierto mostrar la ventana de finalizacion de punto de cambio en vez de la pantalla de registro 
                (en caso de que no lo quieran asi podria agregar la opcion de registrar otro punto de cambio o finalizar ese punto de cambio dentro de ese modal)
            --Si es necesario dar seguimiento a todo el proceso: agregar paginacion y un estatus a cada tab para dar seguimietno a todo el proceso que se sigue en el formato
            --Hay que validar que la persona que se va ha colocar para el punto de cambio este capasitada para la operacion

        Registro de asistencia 
            --Este me parece que esta bien en el diseño actual, solo cambiarlo para que se ajuste a la aplicacion real
            --Tal vez en un futuro sea mejor crear una lista de personal, que sea especifica para la persolnas quue estan
              en el area de sensor o electronicos para registrar informacion mas especifica y poder hacer consultas mas rapido
              ya existe una pero es general de varias plantas
              

        Agregar seccion de registrar y visualizar al personal disponible.
         --Restringir la asignacion a esta tabla si la persona se encuentra aisgnada a alguna otra estacion
         --Falta definir los permisos de los usuarios 
         --Mostrar una tabla en el modal con el listado del personal sin asignar y una opcion de registrar personal 
            disponible que muestre otra ventana para el registro
           -dar opcion de asignar a una estacion el personal mostrado en la tabla , cerrando el modal y abriendo el 
            modal de asignar operador a una estacion intentando setear los input con los datos de la fila 
            seleccionada en la tabla

    ¿El numero de control de cambio se registra por linea, o es el mismo para todos los registrados?
    ¿Si la operacion o en la estacion no es necesaria una certificacion como se libera o comprueba el conocimiento del operador en a linea?
    Agregar campos para registrar algun detalle o informacion en la liberacion del punto de cambio esto podria registrarse en otra tabla como detalle PC liberado


    --Agregar en el formulario un campo o contenedor que muestre los demas procesos o estaciones a las que esta asignado el operador al ingresar su nomina
    --Agregar una clase a las estaciones al moverse para guardar la posicion solo de las estaciones que cambiaron de lugar 
      o podria guardar la posicion de todas las estaciones aunque no se hayan movido
    --Agregar alguna alerta o etiqueta de warning para mostrar cuando el PC lleva ya 30 días

    --validar la asignacion de un trabajador en una estacion cuando se registra un punto de cambio
    --modificar el diseño para que sea mas rapido como se asignan y se eliminan los operadores de las estaciones, tal vez haciendo esto dentro del mismo modal de la estacion

    --mostrar en el el layout algun indicador si hay una persona asignada en la estacion, si falto, si hay un punto de cabio o una asistencia, puede ser con algun color en la estacion
*/  

//Registro de asistencia
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


//El codigo remueve al trabajador que aparece en la estacion aunque no haya alguina operador asignado pero 
// si esta registrado el punto de camvio, hay que corregir para manejar mejor la logica de esto cuando no haya operador asignado pero este un punto de cambio
//gestionar quien queda en la estacion y si se muestra o no la inforacion de la persona

//Tal vez validar al momento de remover al empleado si este es un punto de cambio que se finalice el punto de cambio
//Y dejar otra opcion para que se puede finalizar y/o asignar al operador como titular de la estacion
//Falta agregar el filtro de turno a las consultas

//Revisar que pasa con las personas cuando no estan trabajando en la estacion y tienen un punto de cambio
//Si a una estacion se le crea un punto de cambio mostrara la persona del punto de cambio pero no mostrara a la persona 
//que esta como titular de la estacion, si esta no esta porque se le sansiono o suspendio no se le va a poder hacer el registro de la sistencia 
//Tal vez seria conveniente mostrar todas las personas y dar prioridad a mostrar los PC y si el titular 
// no esta en ninguna estacion mas que en la estacion con el PC mostrar la persona que esta como PC y al titular
//indicando si es titular o PC

//Y como se guardaria la asistencia o el registro del layout si la persona esta asignada en dos estaciones sin que excista un punto de cambio
//si no que esta como titular
//o tener dos tabla uno para el registro de la estacion y otro del acomodo del layout 

//Eliminar(ocultar ) una estacion