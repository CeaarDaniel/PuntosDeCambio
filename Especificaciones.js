/*  
    SISTEMA SAPC
    Agregar una seccion done el operaor realice una prueba para que este se libere o certifique en alguna operacion
    Validar certificaciones 
    Registrar y Retirar certificacion 

    CONDICIONES
    El operador debe de estar certificado(ILU) o liberado en la operacion
    Si a un operador se le retira su certificacion en una operacion ya no se puede volver a certificar
    
    PUNTOS DE CAMBIO
    En el diseño actual falta
        --Se supone que en una estacion solo debe de haber una sola persona
                
        Registro del punto de cambio.
        Mejorar la manera en la que se le da seguimiento a los puntos de cambio (consulta, liberacion o finalizacion)
            --Si el punto de cambio seleccionado (4M) esta abierto mostrar la ventana de finalizacion de punto de cambio en vez de la pantalla de registro 
                (en caso de que no lo quieran asi podria agregar la opcion de registrar otro punto de cambio o finalizar ese punto de cambio dentro de ese modal)
            --Si es necesario dar seguimiento a todo el proceso: agregar paginacion y un estatus a cada tab para dar seguimietno a todo el proceso que se sigue en el formato
            --Hay que validar que la persona que se va ha colocar para el punto de cambio este capasitada para la operacion

        Registro de asistencia 
            --Tal vez en un futuro sea mejor crear una lista de personal, que sea especifica para la personas que estan
              en el area de sensor o electronicos para registrar informacion mas especifica y poder hacer consultas mas rapido
              ya existe una pero es general de varias plantas

    ¿Si la operacion o en la estacion no es necesaria una certificacion como se libera o comprueba el conocimiento del operador en a linea?
*/  

//Revisar que la variable stationsData se actualice al hacer cualquier cambio en el layout
//Reviasr los valores enviados y recibidos de la variabla layoputData o en el json que lista las estaciones

//Validar los turnos de los empleados al hacer registros para que sean coeherentes no tener personal activo registrado en diferentes tablas con distintos turnos
//Osea que esten en el mismo turno en las tres tablas, que no suceda que por ejempplo en la tabla PC esta en el 2 y en la de NAD y la tabla personal_estacion en el turno 1
//Agregar una restriccion para no poder registrar la asistencia si no se esta dentro del horario correspondiente al turno
//Falta agregar el filtro de turno a las consultas
//Mostrar datos por turno


//Al asignar a una persona que esta en la tabla de personal NAD a otra tabla como de PC, eliminarla de esta tabla o cambiar su estatus
//Creo que falta agregar la validacion de el turno en los demas registros de PC y asignacion
//Hacer restricciones y validaciones correspondientes
//Revisar las consultas donde el json_decod manda un 'error
// revisar la validacion del registro de personal NAD ya que puede evitarse una consulta quitando el filtro de codigo_Linea <> :codigoLinea


//Dar opcion de registrar a un operador sin punto de cambio o no si no existe la ultima fecha de operacion en la linea
//Y dejar otra opcion para que se puede finalizar y/o asignar al operador como titular de la estacion
//esto para al inicio cunado se empieza a registrar a todo el personaol 
//Agregar alguna alerta o etiqueta de warning para mostrar cuando el PC lleva ya 30 días
//mostrar en el el layout algun indicador si el trabajadore falto o asistio
//Para mostrar el estatus de la asistencia en la estacion del layout hay que comparar el personal de las estaciones con su registro de la tabla de asistencia y el estatus de registro de asistencia (1,2,3... etc)
//Agregar opcion para consultar el acomodo del layout guardado por dia o fecha
//Actualizar el campo de observaciones de la tabla de asistencia
//Se ha elimiado a esta persona del registro de todas las estaciones, ¿desea agregalo al listado de personal disponible o personal no asignado?)

//Modificar el codigo de la linea por un numero consecutivo
//Para hacer el cambio de turno podria solo registrar otro turno en la asignacion actual, o finalizar el registro actual y crear uno nuevo con los mismos y con el turno cambiado 
//Hacer el contenido responsivo
//Pruebas
//Generar permisos de usuarios
//Generar usuarios

//Revisar que pasa con las personas cuando no estan trabajando en la estacion o faltan y tienen un punto de cambio
//Investigar que sucede con el punto de cambio si hay un cambio de turno

/*
    ESPICIFICACIONES/MODIFICACIONES OK
    //Generar mostrar el contenido dinamico de los formularios
    //Falta registrar las fechas con la hora
    //validar la asignacion de un trabajador en una estacion cuando se registra un punto de cambio
    //No limpiar todos los campos de los formularios solo los que son ingresados por el usuario
    //Falta validar que el empleado no este dado de baja 
    //Revisar las condiciones de la consulta para el cambio de turno
*/