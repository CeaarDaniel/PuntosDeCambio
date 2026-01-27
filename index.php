<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Puntos de cambio y certificaciones</title>

  <!-- Bootstrap -->
  <link href="./css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="./css/bootstrap-icons.min.css" />

  <!-- Font Awesome (para íconos) -->
  <link href="./css/all.min.css" rel="stylesheet"> 

  <!--Libreria Jquery --> 
  <script src="./scripts/jquery-3.7.1.min.js"></script>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="./css/style.css">
</head>
<body>

<div class="app-container">

    <!-- Barra de navegacion -->
      <?php include('navBar.php')?>
    
    <!-- Main Content Area -->
    <div class="main-content">
      <div class="top-bar">
        <h4 id="page-title" class="page-title">Dashboard</h4>
        <div class="user-info">
          <span>Usuario: Admin</span>
          <i class="bi bi-bell ms-3"></i>
        </div>
      </div>
      
      <div id="content-area" class="content-area animacion">
        <?php include('./pages/dashboard.php') ?>
      </div>
    </div>
</div>

  <!-- Bootstrap JS -->
  <script src="./scripts/bootstrap.bundle.min.js"></script>

  <!--Custom js -->
  <script src="./scripts/main.js"></script>
</body>
</html>

  <!-- 
    /******SISTEMA DE PUNTOS DE CAMBIO Y CERTIFICACIONES******/
    /*

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

        El registro de los puntos de cambio lleva un registro de un numero consecutivo que se reinicia
        cada mes. ¿que pasa con este consecutivo si el punto de cambio duro poco, como 10 minutos?

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
       
        
        ---- DISEÑO DE LOS WIREFRAMES ---- 
        En general el sistema creo que puede dividirse en dos partes que son la seccion de las certificaciones 
        y la de los puntos de cambio,


        ¿Los ILU aplican solo para las certificaciones o tambien para liberar a personal en algun proceso?
    ******************/


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

        --Puede ser que sea util Registrar una tabla para las lineas 
  -->