/*  
    ******SISTEMA DE PUNTOS DE CAMBIO Y CERTIFICACIONES******
    
     PUNTOS DE CAMBIO 
        Los puntos de cambio de mano de obra se realizan cuando hay un cambio de personal en una estacion
        existen varias condiciones para que pueda considerarse el punto de cambio, como que el operador
        tenga mas de un mes sin haber operado en la linea, que sea alguien que halla dejado el procesos y
        se reincorpore o que sea una persona inexperta o de nuevo ingreso, tambien existe otro punto de cambio
        "especial" que se registra cuando un operador va al baño y es suplido por otro por un periodo corto
        de tiempo de 10 minutos aproximadamente. 

        El punto de cambio de mano de obra puede ser de dos tipos, inseperado o programado, dependiendo de
        esto llenan el formato de diferente manera. 

        Al asignar el punto de cambio se hace un reigstro en el segundo formato donde se registra la fecha
        programada y la fecha real 
        ¿porque? 
        ¿Que pasa si solo se registra la fecha real? 
        ¿afecta si solo se captura la fecfha real?

        Para que el operador pueda estar operando en la linea este debe de estar certificado en el proceso 
        o liberado segun lo requiera la estacion. 
        Para registrar el punto de cambio se requiere de llenar dos formatos, 
            -- uno donde se registra el punto de cambio y se verifica la correcta operacion por tres dias 
               en este punto se evlauan varios parametros por parte del personal de calidad, el lider o 
               el capacitador y el staff, en caso de que el operador dure menos de los tres dias, solo se 
               cancela o no se llena el restro del registro. 
            -- otro donde se le da un seguimiento durante 30 dias registrando la sistenia y cumplimiento,
                en este se evaluan diferentes parametros en cuanto a la operacion, fallas, anormalidades
                problemas o fallas en la operaciones, etc 
                
        Ambos documentos deben de ser firmados por el personal correspondiente para su liberacion o finalizacion
        confirmando estar de acuerdo con la informacion capturada en los documentos. 
        Para el primer docunento debe de ser firmado tambien por el staff o supervisor de la linea 

        Despues del periodo del punto de cambio puede dejarse como asignado en esa estacion al operador, 
        regresar la persona asignada o asignar otro punto de cambio. 

        Al finalizar el punto de cambio deben de hacerse una revision y registrar las condiciones en la que
        se libera la estacion. 

        Aqui el titular lo manejan diferente a como esta en el otro sistema, con titular se refieren a que
        una persona puede estar capacitada y liberada o certificada para algun procesos aunque no este 
        asignado o fijo en dicha estacion, por lo que una persona puede ser titular de varios procesos.
        
        De echo es casi obligatorio que una persona este liberada en al menos tres procesos. 

        Los staf o supervisores llevan un registro de astencia de la linea(s) que tienen asignadas y 
        pueden registrar varios valores para la asistencia, como falta injustificada, vacaciones, asistencia, 
        permiso sin gose de sueldo etc. 
        

    CERTIFICACIONES 
        para las certificaciones se tiene un registro llamado ILU, tengo entendido que el personal de capacitacion 
        lleva un registro fisico y en un archivo en excel donde guardan los resultados de las capacitaciones del
        personal, sus examenes y sus certificados, el personal de manufactura tambien tiene un registro en un archivo
        donde tienen capturadas a las personas certificadas o liberadas en algun procesos, correspondiente a las 
        lineas que tienen asignadas.

        Los valores para el ILU son algo como lo siguiente 
            --(I) No conoce el proceso 
            --(U)Conoce el proceso 
            --L(puede capacitar en el proceso)

        Una persona se puede certificar en varios procesos, no es lo mismo que esta certificada a que este 
        liberada, hay operaciones que requieren de certificacion y otras que no la requieren. 

        Si una persona falla en algun proceso, tiene errores, una mala calidad de la ejecucion, en repetidas
        ocaciones se le pude retirar la certificacion, si esto pasa ya no puede volver a certificacrse en
        la misma operacion y tampoco puede operar en la estacion que requiera de esta certificacion. 


        ¿Los ILU aplican solo para las certificaciones o tambien para liberar a personal en algun proceso?
    ******************


      ESTRUCTURA DE LA PAGINA 
        -LINEAS
            -AGREGAR NUEVA LINEA
            -ACTUALIZAR DATOS DE LA LINEA
            -VISUALIZACION DE LAS LINEAS CREADAS
                --CREAR ESTACIONES
                --ASIGNAR OPERADOR
                --ACOMODO DE LAYOUT
                --PUNTO DE CAMBIO
                --REGISTRO DE ASISTENCIA
                --REPORTE DE ASISTENCIA POR DIA Y FECHAS
        -CERTIFICACIONES
            --AGREGAR CERTIFICACION
            --REGISTRAR RESULTADOS DE PRUEBA
            --CONSULTAR OPERADORES CERTIFICADOS
            --FECHAS DE CERTIFICACIONES/VENCIMIENTO
            --REALIZAR PRUEBA
        -REPORTES (todos los anteriores)
          --Asistencia
          --Puntos de cambio
          --Operadores certificados
          --Operadores con certificaciones vencidas
        -REGISTRO DE PROCESOS (para las certificaciones, replantear esta opcion)
  

  
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


    Al mover los empleados de las estaciones este proceso debe de ser rapido, puede que se mueva un operador disponible a otra estacion en la misma linea 
    o puede moversa a otra linea, por ejemplo hoy sucedio que un operador se movio a otra linea en una estacion donde si habia operador y el operador de la estacion
    se movio a la estacion vacia de esa linea. Tambien puede pasar que el operador disponible se mueva a alguna estacion dentro de la misma linea donde si halla algun trabajador y este trabajor se mueva a una estacion
    de otra linea donde se prestara

    --De mometno me parece mas optimo lo primero solo agregare una opcion al tipo de cambio que diga otro para indicar que no necesariamente es un punto de cambio tal cual, 

    Tienen el reporte de asistencia y aparte tienen otro reporte donde registran tambien a las personas prestadas y cuantan cuantos lideres hay en la linea, ademas de las faltas, asistencias, descanso, vacaciones, incapacidades etc.
*/  

/*
Revisar si es necesario hacer el registro del layout dos veces al hacer el registro de la asistencia o dejar solo un registro
Hacer restricciones y validaciones correspondientes
Modificar el codigo de la linea por un numero consecutivo
Hacer el contenido responsivo
Pruebas
Generar permisos de usuarios
Generar usuarios
Cuando el codigo de la linea que se envia en la url ocurre un error que no esta atrapado en algun catch o condicion

Dividir la pantalla de layout en otras 3 pantallas 
  --Otro para la consulta del historial del layout y a lo mejor generacion de reportes
  --Una para todo el registro de los puntos de cambio y las operaciones de los operadores - ok
  --otra pantalla para la creacion del layout agrgar estaciones, editar informacion de la linea, agregar 
    las formas del layoput -ok

En la consulta donde se valida que el trabajador no este registrado en otro turno, seria mejor que no ponga como filtro la linea y lo revise en general para todas las lineas
Hay que evaluar la logica de la opcion 19 en las operacionesLinea para tratar de encontrar algun posible error o falla en la logica del codigo
verificar que el trabajador no pueda registrarse en otro turno al registrarlo en otra linea
Revisar que sucede cuando la fecha de asignacion no es en la fecha actual sino que es antes o despues de la fecha actual-- o dejar solo como campo de readonly para guardarlo con la fecha actual

Validar en la evaluacion si el numero de evaluacion corresponde con el numero de evaluacion realizada
Mostrar reportes e informacion de la asistencia y los PC registrados con sus evaluaciones
Crear seccion de certificaciones

    ESPICIFICACIONES/MODIFICACIONES OK
    Agregar una restriccion para no poder registrar la asistencia si no se esta dentro del horario correspondiente al turno
    Generar mostrar el contenido dinamico de los formularios
    Falta registrar las fechas con la hora
    validar la asignacion de un trabajador en una estacion cuando se registra un punto de cambio
    No limpiar todos los campos de los formularios solo los que son ingresados por el usuario
    Falta validar que el empleado no este dado de baja 
    Revisar las condiciones de la consulta para el cambio de turno
    Validar los turnos de los empleados al hacer registros para que sean coeherentes no tener personal activo registrado en diferentes tablas con distintos turnos
    Osea que esten en el mismo turno en las tres tablas, que no suceda que por ejempplo en la tabla PC esta en el 2 y en la de NAD y la tabla personal_estacion en el turno 1
    Al remover una persona de una estacion, la quita de ambos turnos, de cualquier manera no deberia de haber una persona registrada en ambos turnos pero igual hauy que validar que solo se elimine del turno actual
    Falta agregar el filtro de turno a las consultas
    Mostrar datos por turno
    Revisar las consultas donde el json_decod manda un 'error'
    Revisar que la variable stationsData se actualice al hacer cualquier cambio en el layout
    mostrar en el el layout algun indicador si el trabajadore falto o asistio hay que comparar el personal de las estaciones con su registro de la tabla de asistencia y el estatus de registro de asistencia (1,2,3... etc)
    Para hacer el cambio de turno podria solo registrar otro turno en la asignacion actual, o finalizar el registro actual y crear uno nuevo con los mismos y con el turno cambiado
    --Preguntar si el nomero de control de punto de cambio es por linea y turno o solo por linea: solo por linea sigue auentando independientemente del turno
    Investigar que sucede con el punto de cambio si hay un cambio de turno
    Agregar alguna alerta o etiqueta de warning para mostrar cuando el PC lleva ya 30 días o solo mostrar el tiempo de duracion del PC
    Revisar que pasa con las personas cuando no estan trabajando en la estacion o faltan y tienen un punto de cambio 
    (Se cancela si falta dos dias seguidos)
    Actualizar el campo de observaciones de la tabla de asistencia
    Cambiar validacion de los comentarios para que acepte valores vacios 
    Registrar el acomodo del layout despues de hacer el registro de la asistencia
    Parece ser que hay un retraso al guardar el layout
    Actualizar los registros de historial de layout al abrir el modal de historial de layou
    Revisar la consulta 17 falta una condicion en el turno 2 -- update creo que ya lo hice
    descomentar el codigo en el registro de punto de cambio para registro de PNAD
    agregar opcion para registrar un operador desde la estacion
    agregar un boton para incertar un registro individual en la tabla de asistencia despues de haber registrado la asistenia en caso de que halla faltado algun valor 
    ya que puede haber problema si les falto removoer algun registro del día anterior, como por ejemplo que no hayan cargado el personal disponible, o no hallan cerrado algun punto de cambio
    y por lo tanto no se haya cargado el trabajador como disponible, dejar esta opcion deshabilitada y solo habilitarla despues de que se halla registrado la asistencia
    mostrar la estadistica real en el resumen de la asistencia
    Mostrar una alerta o un mensaje en rojo que indique la feha de la ultima operacion en la estacion 
    o si tiene registro de operacion en la estacion (mostrar su ultima fecha de operacion en la estacion del operador)
    Dejar otra opcion para que se puede finalizar y/o asignar al operador como titular de la estacion
    Al parecer hay algo que llaman los desplazados, todos los dias descansan 5 personas por lo que esto hace que deban 
    de poder estar mas de una persona asignadas a la estacion. Hay que cambiar el cdigo para que se puedan registrar 
    por lo menos dos trabajadores por estacion y modificar todas las validaciones, los registros, los formularios, 
    cambiar parte de la logica del codigo para gestionar las estaciones
    Considerar el registrar el turno de asignacion de la persona dentro de la tabla de personal en vez de guardarlo
    en la tabla de personal_estacion, ver si es factible y util guardar la informacion de esta manera o no
    Al asignar a una persona que esta en la tabla de personal NAD a otra tabla como de PC, eliminarla de esta tabla o cambiar su estatus
    Se ha elimiado a esta persona del registro de todas las estaciones, ¿desea agregalo al listado de personal disponible o personal no asignado?)
    Al asignar al operador mostrar un listado de las personas certificadas o liberadas en el proceso o en la estacion
    Mostrar una lista del personal liberado o certificado al asignar la persona a la proceso desde el modal de la estacion
    dejar que las asignaciones se hagan con el ILU, tener el listado de las operaciones en una tabla y desde ahi
    hacer la asignacion y que este registro se quede fijo hasta hacer algun cambio, listare todos los procesos y
    en la tabla dentro del processo listare internamente en la fila a las personas asignadas al proceso, hay que
    dar opcion para que dentro de este mismo listado se pueda agregar y eliminar a una persona activa en el 
    proceso del listado de personas certificadas
    Quitar las restricciones en la asignacion de los trabajadores a una estacion para que se puedan registrar varios trabajadores en una estacion
    ¿como mostrar los trabajadores en el layout, actualmente solo se puede mostrar uno? 
    Creo que los datos que se agregan a la variable de stationData al crear/agregar la estacion no coinciden con los datos que existen al acutalizar los datos de las estaciones, --update: creo que esyo ya lo corregi
*/