<?php
date_default_timezone_set('America/Mexico_City'); // Ajusta tu zona horaria
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header('Content-Type: application/json; charset=utf-8');
include('./conexion.php');
$opcion = $_POST['opcion'];

//REGISTRO DE UNA NUEVA ESTACION
    if($opcion=='2'){
        $nombreEstacion = $_POST['nombreEstacion'] ?? null;
        $descripcion = $_POST['descripcion'] ?? null;
        $requiereC = $_POST['requiereC'] ?? null;
        $certificacion = null;
        $x = $_POST['x'] ?? null;
        $y = $_POST['y'] ?? null;
        $linea = $_POST['linea'] ?? null;

        // Validar que se recibieron todos los datos
        if (!$nombreEstacion) {
            echo json_encode([
                'status' => 'error',
                'mensaje' => 'Faltan datos obligatorios.'
            ]);
            exit; 
        }


        try { // Iniciar transacción
            $conn->beginTransaction();

            // Preparar la sentencia con parámetros
            $sql = "INSERT INTO SPC_ESTACIONES (nombre_estacion, descripcion, requiere_certificacion, codigo_certificacion, posicion_x , posicion_y, codigo_linea) 
                            OUTPUT INSERTED.id_estacion 
                            VALUES (:nombre_estacion, :descripcion, :requiere_certificacion, :codigo_certificacion, :x, :y, :codigo_linea)";

            $stmt = $conn->prepare($sql);

            // Ejecutar con los parámetros
            $stmt->execute([
                ':nombre_estacion' => $nombreEstacion,
                ':descripcion' => $descripcion,
                ':requiere_certificacion' => $requiereC,
                ':codigo_certificacion' => $certificacion,
                ':x' => $x ,
                ':y' => $y,
                ':codigo_linea' => $linea,
            ]);

            $idInsertado = $stmt->fetchColumn();

            // Confirmar la transacción
            $conn->commit();

            echo json_encode([
                'status' => 'ok',
                'mensaje' => 'Registro insertado correctamente.',
                'dataEstacion' => [ 'id' => $idInsertado, 
                                     'name'=> $nombreEstacion, 
                                     'operator' => 'No asignado', 
                                     'status' => 'pending', 
                                     'isCertificate' => $requiereC,
                                     'certification'=> $certificacion, 
                                     'x' => $x, 
                                     'y'=> $y, 
                                     'colorClass'=> 'station-color-7' ]
            ]);
        } catch (PDOException $e) {
            // Si ocurre algún error, revertir la transacción
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            // Respuesta JSON con el error
            echo json_encode([
                'status' => 'error',
                'mensaje' => 'Error al insertar el registro.',
                'detalle' => $e->getMessage()
            ]);
        }
    }

//Listar estaciones
else 
  if($opcion=='5'){
      $codigoLinea = $_POST['codigoLinea'];
      $sql= "SELECT E.id_estacion,  E.nombre_estacion, E.requiere_certificacion AS isCertificate,
                   E.codigo_certificacion,  E.posicion_x,  E.posicion_y
                FROM SPC_ESTACIONES E WHERE E.codigo_linea = :codigoLinea";
        $stmt = $conn->prepare($sql);
        $response= array();

        // Ejecutar con los parámetros
        if($stmt->execute([':codigoLinea' => $codigoLinea])){
            while($estacion= $stmt->fetch(PDO::FETCH_ASSOC)){
                $coloClass = 'station-color-7';
                $asistencia = 'absent'; //pending: sin asignar, occupied: operador asignado
                $response[] = array( 'id' => $estacion['id_estacion'],
                                     'name' => $estacion['nombre_estacion'], 
                                     'status' => $asistencia,
                                     'certification' => $estacion['codigo_certificacion'], 
                                     'x' => $estacion['posicion_x'],
                                     'y' => $estacion['posicion_y'] ,
                                     'colorClass' => $coloClass,
                                     'isCertificate' => $estacion['isCertificate']
                                   );
            }
        }

        else 
            $response = $stmt->errorInfo()[2];

    echo json_encode($response);
 }

//Guardar/actualizar la posicion de las estaciones en el layout
else 
    if($opcion == '6'){
            $layoutPosition =  json_decode($_POST['layoutPosition'], true);
            $codigoLinea = !empty($_POST['codigoLinea']) ? $_POST['codigoLinea'] : null;
            $stationsData= !empty($_POST['stationsData']) ? $_POST['stationsData'] : null;
            $layoutF= !empty($_POST['layoutF']) ? $_POST['layoutF'] : null;
            $turno= !empty($_POST['turno']) ? $_POST['turno'] : null;

            if (!$layoutPosition || !is_array($layoutPosition) || !$stationsData || !$codigoLinea) {
                echo json_encode(['error' => 'Datos invalidos']);
                exit;
            }
         
            $sql = "UPDATE SPC_ESTACIONES SET posicion_x = :x, posicion_y = :y
                      WHERE id_estacion = :id";
            $stmt = $conn->prepare($sql);

            $sqlI = "INSERT INTO SPC_HISTORIAL_LAYOUT (codigo_linea, turno, layout) 
                        VALUES(:codigoLinea, :turno, :stationsData)";
            $stmtI = $conn->prepare($sqlI);

            $sqlIL = "MERGE SPC_LAYOUTFORMAS AS target
                        USING (SELECT :codigoLinea AS codigo_linea, :layoutF AS layoutF) AS source
                        ON target.codigo_linea = source.codigo_linea
                        WHEN MATCHED THEN
                            UPDATE SET layoutF = source.layoutF
                        WHEN NOT MATCHED THEN
                            INSERT (codigo_linea, layoutF)
                            VALUES (source.codigo_linea, source.layoutF);";
            $stmtIL = $conn->prepare($sqlIL);

            $results = [];

            try {
                //Iniciar transacción
                $conn->beginTransaction();

                foreach ($layoutPosition as $item) {
                    if (isset($item['id'], $item['x'], $item['y'])) {
                            // Validar que x y y sean numéricos
                            $x = is_numeric($item['x']) ? $item['x'] : 0;
                            $y = is_numeric($item['y']) ? $item['y'] : 0;
                            $id = $item['id'];

                            $stmt->execute([
                                ':x' => $x,
                                ':y' => $y,
                                ':id' => $id
                            ]);

                            //$results[] = ['id' => $id, 'status' => 'ok'];
                    }
                }

                 $stmtI->execute([':codigoLinea' => $codigoLinea, 
                                  ':stationsData' => $stationsData,
                                  ':turno' => $turno]);

            if(!empty(json_decode($layoutF, true)))
                $stmtIL->execute([':codigoLinea' => $codigoLinea, ':layoutF' => $layoutF]);

                // Confirmar transacción
                $conn->commit();

                $results= array('estatus' => 'ok',
                                'mensaje' => 'se ha guardado el layout');


            } catch (PDOException $e) {
                // Revertir en caso de error
                $conn->rollBack();
                echo json_encode( array('estatus' => 'error',
                                        'error' => $e->getMessage(), 
                                        'mensaje' => 'Ocurrio un error al realizar la operacion'
                                        ));
                exit;
            } 

        // Devolver resultado
        echo json_encode($results);
    }

//Editar informacion de la linea
else 
    if($opcion=='11'){
        $codigoLinea = $_POST['codigoLinea'] ?? null;
        $encargado = !empty($_POST['encargado']) ? $_POST['encargado'] :  null;
        $nombreLinea = $_POST['nombreLinea'] ?? null;
        $descripcion = $_POST['descripcion'] ?? null;

        // Validar que se recibieron todos los datos
        if (!$codigoLinea) {
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Faltan datos obligatorios.'
            ]);
            exit; 
        }

        try { // Iniciar transacción
            $conn->beginTransaction();

            // Preparar la sentencia con parámetros
            $sql = "UPDATE SPC_LINEAS 
                        SET nombre_linea = :nombre_linea, 
                            descripcion = :descripcion, 
                            encargado_supervisor = :encargado_supervisor
                    WHERE codigo_linea = :codigo_linea";
            $stmt = $conn->prepare($sql);

            // Ejecutar con los parámetros
            $stmt->execute([
                ':codigo_linea' => $codigoLinea,
                ':nombre_linea' => $nombreLinea,
                ':descripcion' => $descripcion,
                ':encargado_supervisor' => $encargado
            ]);

            // Confirmar la transacción
            $conn->commit();

            echo json_encode([
                'estatus' => 'ok',
                'mensaje' => 'Registro actualizado correctamente.',
            ]);

        } catch (PDOException $e) {
            // Si ocurre algún error, revertir la transacción
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            // Respuesta JSON con el error
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Error al actualizar el registro.',
                'detalle' => $e->getMessage()
            ]);
        }
    }


//Consulta para obtener los datos de una sola estacion
else 
    if($opcion=='15'){
        $idEstacion = !empty($_POST['idEstacion']) ? $_POST['idEstacion'] : null;
        $turno = !empty($_POST['turno']) ? $_POST['turno'] : null;
        $ahora = new DateTime();

        //Verificar si ya existe un registro de asistencia
            //Turno 1
            if($turno == '1'){
                $inicio = new DateTime('today 08:00');
                $fin    = new DateTime('today 19:59');
            }

            //Turno 2
            else
                if ($turno == '2') {
                    if ($ahora >= new DateTime('today 20:00')) { // Después de las 8 pm
                        $inicio = new DateTime('today 20:00');
                        $fin    = new DateTime('tomorrow 07:59');
                    } 
                    
                    else { // Antes de las 8 pm
                        $inicio = new DateTime('yesterday 20:00');
                        $fin    = new DateTime('today 07:59');
                    } //Despues de la 8 de la noche la fecha de inicio es hoy 8:00 pm fecha de fin mañana 8:00 am
                    //Antes de 8 de la noche la fecha de inicio es ayer 8:00 pm y la fecha de fin hoy 8:00 am 
                }
            
            else {
                    echo json_encode(['estatus' => 'error',
                                      'mensaje' => 'Turno no valido'
                                    ]);
                    exit; 
            }

        // Validar que se recibieron todos los datos
        if (!$idEstacion) {
            echo json_encode(['estatus' => 'error',
                              'mensaje' => 'Faltan datos obligatorios.']);
            exit; 
        }

        // Preparar la sentencia con parámetros
        $sql= "SELECT E.id_estacion, E.nombre_estacion, E.requiere_certificacion AS isCertificate,
                        CASE WHEN PC.nomina IS NULL THEN EP.nomina ELSE PC.nomina END AS nomina, 
                        CASE WHEN PC.nombre IS NULL THEN EP.nombre ELSE PC.nombre END AS nombre,
                        CASE WHEN PC.nomina IS NULL THEN EP.fecha_asignacion ELSE PC.fechaHora_inicio END AS fecha_asignacion,
                        E.codigo_linea, E.codigo_certificacion, PC.estatusPC, PC.idPC, A.estatus AS asistencia
                                            FROM SPC_ESTACIONES E 
                    LEFT JOIN (SELECT id_estacion, nomina, nombre, fecha_asignacion from SPC_PERSONAL_ESTACION WHERE fecha_fin IS NULL AND turno = :turno) AS EP ON E.id_estacion = EP.id_estacion
                    LEFT JOIN (SELECT idPC, id_estacion, nomina, nombre, estatusPC, fechaHora_inicio from SPC_PUNTOS_CAMBIO where fechaHora_fin IS NULL AND turno = :turno) AS PC on E.id_estacion = PC.id_estacion
                    LEFT JOIN (SELECT nomina, estatus FROM SPC_REGISTRO_ASISTENCIA WHERE turno = :turno AND fecha_operacion >= :fecha_inicio AND fecha_operacion < :fecha_fin) AS A ON A.nomina = COALESCE(PC.nomina, EP.nomina)
                WHERE E.id_estacion = :idEstacion";

        $stmt = $conn->prepare($sql);
        $response= array();

        $fechaInicio = $inicio->format('Y-m-d H:i:s');
        $fechaFin    = $fin->format('Y-m-d H:i:s');

        // Ejecutar con los parámetros
        if($stmt->execute([':idEstacion' => $idEstacion, ':turno' => $turno, ':fecha_inicio' => $fechaInicio, ':fecha_fin' => $fechaFin]))
        {
            if($estacion= $stmt->fetch(PDO::FETCH_ASSOC)){

            if($estacion['estatusPC'] == '1') 
                 $coloClass = 'station-color-2'; 

            else  
                if(!empty($estacion['nomina'])) $coloClass = 'station-color-1';
            
            else $coloClass = 'station-color-7';  



            //Estatus de asistencia
                $asistencia = '';

                if(empty($estacion['nomina'])){
                    $asistencia = 'pending';
                }

                else 
                    if(!empty($estacion['asistencia'])){
                        $asistencia = ($estacion['asistencia'] == '1' || $estacion['asistencia'] == '8') ? 'occupied' : 'absent';
                    }

                else 
                    if(empty($estacion['asistencia'])){
                        $fechaAsignacion = new DateTime($estacion['fecha_asignacion']);

                        if($fechaAsignacion >= $inicio && $fechaAsignacion <= $fin) {
                            $asistencia = 'occupied';
                        } else {
                            $asistencia = 'absent';
                        }
                    }


                $response = array ( 'estatus' => 'ok',
                                    'estacion' => array('id' => $estacion['id_estacion'],
                                                        'nomina' => $estacion['nomina'],
                                                        'name' => $estacion['nombre_estacion'], 
                                                        'operator' =>  !empty($estacion['nomina']) ? $estacion['nombre'] : '',  
                                                        'status' => $asistencia, //pending: sin asignar, occupied: operador asignado
                                                        'certification' => $estacion['codigo_certificacion'],
                                                        'idPC' => $estacion['idPC'],
                                                        'colorClass' => $coloClass,
                                                        'estatusPC' => $estacion['estatusPC'],
                                                        'isCertificate' => $estacion['isCertificate']
                                                        )
                                    );
            }
        }

        else 
            $response = array( 'estatus' => 'error',
                               'mensaje' =>  $stmt->errorInfo()[2]);

        echo json_encode($response);
    }

//Funcion para consultar el listado de formas elementos SVG
else 
    if($opcion == '24'){
        $codigoLinea = !empty($_POST['codigoLinea']) ? $_POST['codigoLinea'] : null;

        if(!$codigoLinea) {
                echo json_encode(['estatus' => 'error', 
                                  'mensaje'=> 'Faltan datos obligatorios']);
                exit;
        }

        try {
                $sql = "SELECT layoutF from SPC_LAYOUTFORMAS where codigo_linea = :codigoLinea";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':codigoLinea' => $codigoLinea]);
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
          
                if ($resultado) {
                    /*
                        Verificar que la decodificacion sea correcta y el json no esta corrupto
                        $formas = json_decode($resultado['layoutF'], true);
                        if (json_last_error() !== JSON_ERROR_NONE) throw new Exception('Error al decodificar JSON');
                    */

                    echo json_encode([
                        'estatus' => 'ok',
                        'formas' => json_decode($resultado['layoutF']),
                    ]);
                } 
                
                else {
                    echo json_encode([
                        'estatus' => 'error',
                        'mensaje' => "No se encontro algun registro"
                    ]);
                }

        } catch (PDOException $e) {
            // Error de conexión o consulta
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => $e->getMessage()
            ]);
        }
}
?>