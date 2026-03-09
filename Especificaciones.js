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
        --Se supone que en una estacion solo debe de haber una sola persona, pero puede haber una persona diferente en la estacion
          dependiendo del día, cada día descansan entre 5 y 8 personas, por ejemplo en la estacion puede haber en total 43 personas
          por decir algo, pero no todas trabajan sino que se van rolando los dias de descanso undia descansan tantos y
          otro otros tantos ya asi  
          
          ¿Una persona puede estar en una estacion o puede estar en varias estaciones? Por lo que vi hoy parece que solo una persona
          solo puede operar en una estacion a la vez, a menos que los procesos esten combinados entonces una persona puede estar en varios procesos

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

    Al mover los empleados de las estaciones este proceso debe de ser rapido, puede que se mueva un operador disponible a otra estacion en la misma linea 
    o puede moversa a otra linea, por ejemplo hoy sucedio que un operador se movio a otra linea en una estacion donde si habia operador y el operador de la estacion
    se movio a la estacion vacia de esa linea
    Tambien puede pasar que el operador disponible se mueva a alguna estacion dentro de la misma linea donde si halla algun trabajador y este trabajor se mueva a una estacion
    de otra linea donde se prestara

    Para esto podria registrarlo como putno de cambio y quitar la restriccion de que no se pueda reigstrar un trabajador si es de otra linea, solo para la parte de 
    los puntos de cambio, y tal vez agregar otro campo que indique que es prestado, si queda muy confuso o no agrada esto, tal vez podría agregar otra seccion que 
    sea para el prestado de trabajadores a otras lineas que sea un registro similar al PC pero exclusivo para cuando el trabajador no estaq en su estacion

    --De mometno me parece mas optimo lo primero solo agregare una opcion al tipo de cambio que diga otro para indicar que no necesariamente es un punto de cambio tal cual, 

    Tienen el reporte de asistencia y aparte tienen otro reporte donde registran tambien a las personas prestadas y cuantan cuantos lideres hay en la linea, ademas de las faltas, asistencias, descanso, vacaciones, incapacidades etc.
    ¿Si la operacion o en la estacion no es necesaria una certificacion como se libera o comprueba el conocimiento del operador en a linea?
*/  

/*

Revisar que la variable stationsData se actualice al hacer cualquier cambio en el layout
Reviasr los valores enviados y recibidos de la variabla layoputData o en el json que lista las estaciones

Al parecer hay algo que llaman los desplazados, todos los dias descansan 5 personas por lo que esto hace que deban de poder estar mas de una persona asignadas
a la estacion.
Hay que cambiar el cdigo para que se puedan registrar por lo menos dos trabajadores por estacion y modificar todas las validaciones, los registros, los formularios, cambiar
parte de la logica del codigo para gestionar las estaciones

Tal vez sea mejor quitar la opcion de inseperado en el registro del punto de cambio, o la de otro y cambiar el concepto del punto de cambio inseperado
ya que por lo que vi en la linea la mayoria de los puntos de cabio serian inesperados, que sean solo de uno dia, ya que estos se registraran cuando el operador falte por cualquier motivo
o se preste a alguna otra linea o le presten a la linea un operador que viene de otra, que como tal no es un punto de cambio, pero en el sistema anterior a eso es a lo que llamaban punto
de cambio,

Validar los turnos de los empleados al hacer registros para que sean coeherentes no tener personal activo registrado en diferentes tablas con distintos turnos
Osea que esten en el mismo turno en las tres tablas, que no suceda que por ejempplo en la tabla PC esta en el 2 y en la de NAD y la tabla personal_estacion en el turno 1
Agregar una restriccion para no poder registrar la asistencia si no se esta dentro del horario correspondiente al turno
Falta agregar el filtro de turno a las consultas
Mostrar datos por turno


Al asignar a una persona que esta en la tabla de personal NAD a otra tabla como de PC, eliminarla de esta tabla o cambiar su estatus
Se ha elimiado a esta persona del registro de todas las estaciones, ¿desea agregalo al listado de personal disponible o personal no asignado?)
Hacer restricciones y validaciones correspondientes
Revisar las consultas donde el json_decod manda un 'error'

Al asignar al operador mostrar un listado de las personas asignadas en la estacion similar al listado de estaciones de la persona
Dar opcion de registrar a un operador sin punto de cambio o no si no existe la ultima fecha de operacion en la linea
    -tal vez sea conveniente solo mostrar una alerta o un mensaje en rojo que indique la feha de ultina operacion en la estacion o si tiene registro de operacion en la estacion
Y dejar otra opcion para que se puede finalizar y/o asignar al operador como titular de la estacion
esto para al inicio cunado se empieza a registrar a todo el personaol 

Agregar alguna alerta o etiqueta de warning para mostrar cuando el PC lleva ya 30 días
mostrar en el el layout algun indicador si el trabajadore falto o asistio
Para mostrar el estatus de la asistencia en la estacion del layout hay que comparar el personal de las estaciones con su registro de la tabla de asistencia y el estatus de registro de asistencia (1,2,3... etc)
Agregar opcion para consultar el acomodo del layout guardado por dia o fecha
Actualizar el campo de observaciones de la tabla de asistencia

Modificar el codigo de la linea por un numero consecutivo
Para hacer el cambio de turno podria solo registrar otro turno en la asignacion actual, o finalizar el registro actual y crear uno nuevo con los mismos y con el turno cambiado 
Hacer el contenido responsivo
Pruebas
Generar permisos de usuarios
Generar usuarios
Revisar que pasa con las personas cuando no estan trabajando en la estacion o faltan y tienen un punto de cambio
Investigar que sucede con el punto de cambio si hay un cambio de turno
--Preguntar si el nomero de control de punto de cambio es por linea y turno o solo por linea

Creo que los datos que se agregan a la variable de stationData al crear/agregar la estacion no coinciden con los datos que existen al acutalizar los datos  de las estaciones, 

--En la consulta dopnde se valida que el trabajador no este registrado en otro turno, seria mejor que no ponga como filtro la linea y lo revise en general para todas las lineas

El punto de cambio de maquinaria lo registran los de ingieneria
El punto de cambio por mano de obra y metodo lo registra manufactura o el que hace el cambio del metodo
El punto de cambio por materia prima lo registra el personal de materiales que segun yo son los de control de produccion como el molis 
Creo que es posible que una persona que esta como disponible opere en alguna otra linea, hay que validar que el codigo permita hacer eso
Hay que evaluar la logica de la opcion 19 en las operacionesLinea para tratar de encontrar algun posible error o falla en la logica del codigo
Revisar la consulta 20

Lo priero es quitar las restricciones en la asignacion de los trabajadores a una estacion para que se puedan registrar varios trabajadores en una estacion
¿como mostraer los trabajadores en el layout, actualmente solo se puede mostrar uno? 
¿Tambien en el modal de gestion de la estion, esta echa para mostrar la informacion de un solo trabajador

lo que pienso que se podria hacer mostrar a ambos trabajdores y mostrar al que esta en la estacion hasta despues de pasar la lista
En caso de hacer esto cambiaria los datos de el stationdata, la nomina seria un arreglo [] con las nominas de la persona registrada en la estacion

verificar que el trabajador pueda registrarse en otro turno al registrarlo en otra linea
*/

/*
    ESPICIFICACIONES/MODIFICACIONES OK
    Generar mostrar el contenido dinamico de los formularios
    Falta registrar las fechas con la hora
    validar la asignacion de un trabajador en una estacion cuando se registra un punto de cambio
    No limpiar todos los campos de los formularios solo los que son ingresados por el usuario
    Falta validar que el empleado no este dado de baja 
    Revisar las condiciones de la consulta para el cambio de turno

    Al remover una persona de una estacion, la quita de ambos turnos, de cualquier manera no deberia de haber una persona registrada en ambos turnos pero igual hauy que validar que solo se elimine del turno actual


    TNA TERMINAL NO ACENTADA
    FALTA DE RECINA
    MOLDE ABIERTO
    FALTA DE PEGAMENTO

    TIEMPOS DE ESPERA
    BUGGY

    SAE 
    Es una oja que se usa cuando se quiere cambiar algun proceso, donde se debe de analisar todos los riesgos y beneficios que pouede
    incluir dicho cambio para determinar si se implementa o no


    el viernes solo es medio turno, un turno copleto son 24hrs por lo que medio turno es de 12hrs osea solo el perimer turno de 8:00 am a 8:00pm trabajan

    hay lineas que cuando paran por mas de un minuto se deben de volver a liberar para liberar las lineas en las 
    estaciones los operadores deben de revisar una checklist donde estan los parametros y condiciones que deben 
    de tener las estaciones antes de arrancar o de comnezar con la operacion 
    
    
    Que significa Passport? Creo que es el nombre de un modelo o parte de un arness

    Mejora
    Llenar o abastecer el material de las lineas cuando llegue a cierto porcentaje de cantedidad pero sin llegar a 0
*/