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

  //Registro de punto de cambio indicando solo como estatus si es insesperado o programado
  //Registrar fecha de inicio y fin del punto de cambio   

  //Mostrar estatus o si existe un punto de cambio abierto en el layout
  //Finalizar/cerrar punto de cambio

  //Registro de asistencia del operador
  //Validar ultima fecha de operacion (con la asistencia)

  //Validar certificaciones 

  //Reporte sencillo de los puntos de cambios realizados
  //Generar reportes de estadisticas simulando datos reales

  //Todo esto solo como estatus o flujo de navegacion sin dar seguimiento a todo el formato. 

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


//Remover trabajador 

//Editar informacion de la linea
//Registro de punto de cambio 

//Registro de asistencia
//Cambio de turno


//Reflejar cambios y registros incertados en el layout



//Generar mostrar el contenido dinamico de los formularios
//Hacer restricciones y validaciones correspondientes

//Registrar y Retirar certificacion 
//Valoidar capacitaciones
//Generar permisos de usuarios
//Generar usuarios


//Pruebas

//Falta registrar las fechas con la hora
//Falta validar que el empleado no este dado de baja 


//Continuar con la parte de las certificaciones
//Hacer el contenido responsivo


//Dar opcion de registrar a un operador sin punto de cambio o no si no existe la ultima fecha de operacion en la linea
//esto para al inicio cunado se empieza a registrar a todo el personaol 