<?php
date_default_timezone_set('America/Mexico_City'); // Ajusta tu zona horaria
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header('Content-Type: application/json; charset=utf-8');

include('./conexion.php');

$opcion = $_POST['opcion'];

//REGISTRO DE UNA NUEVA LINEA
if($opcion=='1') {
    $codigoLinea = $_POST['codigoLinea'] ?? null;
    $encargado = !empty($_POST['encargado']) ? $_POST['encargado'] : NULL;
    $nombreLinea = !empty($_POST['nombreLinea']) ? $_POST['nombreLinea'] : null;
    $descripcion = !empty($_POST['descripcion']) ? $_POST['descripcion'] : null;

    // Validar que se recibieron todos los datos
    if (!$codigoLinea || !$encargado) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Faltan datos obligatorios.'
        ]);
        exit; 
    }


    try { // Iniciar transacción
        $conn->beginTransaction();

        // Preparar la sentencia con parámetros
        $sql = "INSERT INTO SPC_LINEAS (codigo_linea, nombre_linea, descripcion, encargado_supervisor) 
                VALUES (:codigo_linea, :nombre_linea, :descripcion, :encargado_supervisor)";
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
            'status' => 'ok',
            'mensaje' => 'Registro insertado correctamente.',
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

//REGISTRO DE UNA NUEVA ESTACION
else 
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
            $sql = "INSERT INTO SPC_ESTACIONES (nombre_estacion, descripcion, requiere_certificacion, id_certificacion, posicion_x , posicion_y, codigo_linea) 
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

//Asignar un operador a una estacion
else 
    if($opcion == '3'){
        $nomina =  $_POST['nomina'] ?? null;
        $nombre = $_POST['nombre'] ?? null;
        $estacion = $_POST['estacion'] ?? '';
        $fecha = $_POST['fecha'] ??  date('Y-m-d H:i:s');
        $turno = $_POST['turno'] ?? null;
        $comentarios = $_POST['comentarios'] ?? null;
        $codigoLinea = $_POST['codigoLinea'] ?? null;

        // Validar que se recibieron todos los datos
        if (!$nomina || !$fecha || !$turno) {
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Faltan datos obligatorios.'
            ]);
            exit; 
        }

        $fecha = str_replace('T', ' ', $fecha);


        try {
                // Iniciar transacción
                $conn->beginTransaction();
                /*estacion_ocupada = *CASE WHEN EXISTS (SELECT 1 FROM SPC_PERSONAL_ESTACION WHERE id_estacion = :id_estacion AND fecha_fin IS NULL AND turno = :turno) THEN 1 ELSE 0 END*/

                //Verificar si el trabajdor esta asignado en otra linea o si la estacion ya tiene un trabajador asignado
                $sql_check = "SELECT estacion_ocupada = CASE WHEN EXISTS (SELECT 1 FROM SPC_PERSONAL_ESTACION 
                                WHERE id_estacion = :id_estacion AND fecha_fin IS NULL AND turno = :turno AND nomina = :nomina1) THEN 1 ELSE 0 END,
                                    trabajador_asignado = CASE WHEN EXISTS
                                        (SELECT 1 FROM
                                            (SELECT PE.nomina FROM SPC_PERSONAL_ESTACION PE 
                                                    INNER JOIN SPC_ESTACIONES E on PE.id_estacion = E.id_estacion
                                                WHERE PE.fecha_fin IS NULL AND PE.nomina= :nomina AND E.codigo_linea <> :codigoLinea
                                                    UNION ALL
                                             SELECT nomina FROM SPC_PUNTOS_CAMBIO WHERE fechaHora_fin IS NULL AND nomina = :nomina3 
                                                    AND codigo_linea <> :codigoLinea3
                                            ) X
                                        )THEN 1 ELSE 0 END, 
                                        PCexist = CASE WHEN EXISTS (SELECT 1 from SPC_PUNTOS_CAMBIO PC 
                                                    INNER JOIN SPC_ESTACIONES_PC epc ON epc.idPC = pc.idPC
                                            WHERE epc.idE = :id_estacion AND pc.fechaHora_fin IS NULL AND pc.turno = :turno) 
                                        THEN 1 ELSE 0 END;";
            
                /*Revisar que el trabajador no este registrado en otro turno en esta linea*/
                $sqlCheckT = 'SELECT otroTurno = CASE WHEN EXISTS 
                                (SELECT 1 FROM 
                                    (SELECT nomina FROM SPC_PERSONAL_ESTACION PE INNER JOIN SPC_ESTACIONES E ON PE.id_estacion = E.id_estacion 
                                            WHERE PE.fecha_fin IS NULL AND PE.nomina= :nomina AND E.codigo_linea = :codigoLinea AND PE.turno <> :turno
                                                UNION ALL
                                        SELECT nomina FROM SPC_PUNTOS_CAMBIO WHERE fechaHora_fin IS NULL AND nomina= :nomina and codigo_linea =:codigoLinea AND turno <> :turno
                                                UNION ALL
                                        SELECT nomina FROM SPC_PERSONAL_NAD WHERE fechaE IS NULL AND nomina = :nomina AND codigo_linea = :codigoLinea and turno <> :turno
                                    ) as Z
                                ) THEN 1 ELSE 0 END;';


                
                $stmt_check = $conn->prepare($sql_check);
                $stmtCT = $conn->prepare($sqlCheckT);

                $stmt_check->execute([":turno" => $turno, 
                                      ":id_estacion" => $estacion, 
                                      ":nomina1" => $nomina,
                                      ":nomina" => $nomina,
                                      ":codigoLinea" => $codigoLinea,
                                      ":nomina3" => $nomina,
                                      ":codigoLinea3" => $codigoLinea
                                      ]);

                $stmtCT->execute([
                    ':nomina' => $nomina,
                    ':codigoLinea' => $codigoLinea,
                    ':turno' => $turno,
                ]);

                $result = $stmt_check->fetch(PDO::FETCH_ASSOC);
                $resultT = $stmtCT->fetch(PDO::FETCH_ASSOC);
            
                if (!$result || !$resultT) {
                    throw new Exception("Error al verificar la asignación.");
                }

                if ($result['estacion_ocupada'] == 1) {
                    // Ya existe
                    $conn->rollBack();
                            echo json_encode([
                                'estatus' => 'error',
                                //'mensaje' => 'Esta persona ya cuenta con una asignación en la estación seleccionada'
                                //'mensaje' => 'Esta estación ya cuenta con un trabajador asignado'
                                'mensaje' => 'Este trabajador ya ha sido asignado a esta estación'
                            ]);
                        exit;
                }

                if ($result['PCexist'] == 1) {
                    // Ya existe
                    $conn->rollBack();
                            echo json_encode([
                                'estatus' => 'error',
                                //'mensaje' => 'Esta persona ya cuenta con una asignación en la estación seleccionada'
                                //'mensaje' => 'Esta estación ya cuenta con un trabajador asignado'
                                'mensaje' => 'El punto de cambio debe ser cerrado antes de asignar al trabajador'
                            ]);
                        exit;
                }

                if ($result['trabajador_asignado'] == 1){
                    // Ya existe
                    $conn->rollBack();
                            echo json_encode([
                                'estatus' => 'error',
                                //'mensaje' => 'Esta persona ya cuenta con una asignación en la estación seleccionada'
                                'mensaje' => 'Este trabajador se encuentra asignado en otra linea'
                            ]);
                        exit;
                }

                
                if($resultT['otroTurno'] == 1){
                        // Ya existe
                        $conn->rollBack();
                            echo json_encode([
                                'estatus' => 'error',
                                //'mensaje' => 'Esta persona ya cuenta con una asignación en la estación seleccionada'
                                'mensaje' => 'Este trabajador se encuentra asignado en otro turno'
                            ]);
                        exit;
                }
            
                // Insertar
                $sql_insert = "INSERT INTO SPC_PERSONAL_ESTACION (id_estacion, nomina, nombre, fecha_asignacion, turno, comentarios)
                            VALUES (:id_estacion, :nomina, :nombre, :fecha_asignacion, :turno, :comentarios)";

                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->execute([
                    ':id_estacion' => $estacion,
                    ':nomina' => $nomina,
                    ':nombre' => $nombre,
                    ':fecha_asignacion' => $fecha,
                    ':turno' => $turno,
                    ':comentarios' => $comentarios,
                ]);
            

                //Verificar si hay algun registro en la tabla de PERSONAL_NAD de la persona asignada

                //Actaulizar el estatus de la persona a asignado (1)
                $sqlUpdateEP = "UPDATE SPC_PERSONAL SET estatus = 1 WHERE nomina = :nomina 
                                    AND estatus NOT IN (1, 2)";
                $stmtUpdateEP = $conn->prepare($sqlUpdateEP);
                $stmtUpdateEP->execute([':nomina' => $nomina]);

                $conn->commit();
                echo json_encode([
                    'estatus' => 'ok',
                    'mensaje' => 'Registro insertado correctamente.',
                ]);
            
        } catch (PDOException $e) {
            // Si ocurre algún error, revertir la transacción
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            // Respuesta JSON con el error
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Error al insertar el registro.',
                'error' => $e->getMessage()
            ]);
        }
    }

//LISTADO DE LINEAS REGISTRADAS
else 
    if($opcion=="4"){
        $sql= "SELECT codigo_linea, nombre_linea, imagen from SPC_LINEAS";

        $registro = $conn->prepare($sql);
        $response= array();

        if($registro -> execute()){
            while($dsc= $registro->fetch(PDO::FETCH_ASSOC))
                $response[] = $dsc;
        }

        else 
            $response = $registro->errorInfo()[2];

        echo json_encode($response);
    }

//Listar estaciones
else 
  if($opcion=='5'){
      $codigoLinea = $_POST['codigoLinea'];
      $turno = $_POST['turno'];
      $ahora = new DateTime();
      $inicio;
      $fin;
      //AGREGAR FILTROS DE TURNO
        // Preparar la sentencia con parámetros

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

        $sql= "SELECT E.id_estacion, E.nombre_estacion, E.requiere_certificacion AS isCertificate,
                        COALESCE(PC.nomina, EP.nomina) AS nomina,
                        COALESCE(PC.nombre, EP.nombre) AS nombre,
                        COALESCE(PC.fechaHora_inicio, EP.fecha_asignacion) AS fecha_asignacion,
                        E.codigo_linea, E.id_certificacion as codigo_certificacion, E.posicion_x, E.posicion_y,
                        PC.estatusPC, PC.idPC, A.estatus AS asistencia
                    FROM SPC_ESTACIONES E
                        OUTER APPLY (
                            SELECT TOP (1) PE.nomina, PE.nombre, PE.fecha_asignacion
                            FROM SPC_PERSONAL_ESTACION PE
                            WHERE PE.id_estacion = E.id_estacion
                                AND PE.fecha_fin IS NULL
                                AND PE.turno = :turno
                            ORDER BY PE.fecha_asignacion DESC, PE.id_asignacion DESC
                        ) AS EP
                        OUTER APPLY (
                            SELECT TOP (1) P.idPC, P.nomina, P.nombre, P.estatusPC, P.fechaHora_inicio
                                FROM SPC_PUNTOS_CAMBIO P 
                        INNER JOIN SPC_ESTACIONES_PC EPC ON EPC.idPC = P.idPC
                            WHERE EPC.idE = E.id_estacion
                                AND P.fechaHora_fin IS NULL
                                AND P.turno = :turno
                            ORDER BY P.fechaHora_inicio DESC, P.idPC DESC
                        ) AS PC
                        OUTER APPLY (
                            SELECT TOP (1) RA.estatus
                            FROM SPC_REGISTRO_ASISTENCIA RA
                            WHERE RA.nomina = COALESCE(PC.nomina, EP.nomina)
                                AND RA.turno = :turno
                                AND RA.fecha_operacion >= :fecha_inicio
                                AND RA.fecha_operacion < :fecha_fin
                            ORDER BY RA.fecha_operacion DESC, RA.id_registro DESC
                        ) AS A
                WHERE E.codigo_linea = :codigoLinea";

        $stmt = $conn->prepare($sql);
        $response= array();

        $fechaInicio = $inicio->format('Y-m-d H:i:s');
        $fechaFin    = $fin->format('Y-m-d H:i:s');


        // Ejecutar con los parámetros
        if($stmt->execute([':codigoLinea' => $codigoLinea, ':turno' => $turno, ':fecha_inicio' => $fechaInicio, ':fecha_fin' => $fechaFin])){
            while($estacion= $stmt->fetch(PDO::FETCH_ASSOC)){
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

                $response[] = array( 'id' => $estacion['id_estacion'],
                                     'nomina' => $estacion['nomina'],
                                     'name' => $estacion['nombre_estacion'], 
                                     'operator' =>  !empty($estacion['nomina']) ? $estacion['nombre'] : '',  
                                     'status' => $asistencia, //pending: sin asignar, occupied: operador asignado
                                     'certification' => $estacion['codigo_certificacion'], 
                                     'x' => $estacion['posicion_x'],
                                     'y' => $estacion['posicion_y'] ,
                                     'colorClass' => $coloClass,  //1 asistencia, 3 falta, 2 o 6 punto de cambio
                                     'idPC' => $estacion['idPC'],
                                     'estatusPC' => $estacion['estatusPC'],
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

//Bucscar operador
else 
    if($opcion== '7'){
        //Variable donde se trae $connE
        include('./conexionEmpleado.php');
        $nomina = $_POST['nomina'] ?? null;

        if(empty($nomina)) {
                echo json_encode(['estatus' => 'error',
                                  'error' => "Error al buscar al trabajador"
                                ]);
            exit;
        }

        try {
                $sql = "SELECT nombre FROM empleado_mst WHERE No_Nomina = :nomina";
                $stmt = $connE->prepare($sql);
                $stmt->execute([':nomina' => $nomina]);
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($resultado) {
                    echo json_encode([
                        'estatus' => 'ok',
                        'nombre' => $resultado['nombre'],
                    ]);
                } else {
                    echo json_encode([
                        'estatus' => 'error',
                        'error' => "Error al buscar al trabajador"
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

//Registrar personal sin asignar a una estacion
else 
    if($opcion =='8'){
        $nomina = $_POST['nomina'] ?? null;
        $nombre = $_POST['nombre'] ?? null;
        $turno = $_POST['turno'] ?? null;
        $fechaR = $_POST['fechaR'] ?? null;
        $comentarios = $_POST['comentarios'] ?? null;
        $codigoLinea = $_POST['codigoLinea'] ?? null;
        $eliminado = 0;

        // 2026-01-26 14:30:00

        // Validar que se recibieron todos los datos
        if (empty($nomina) || empty($fechaR) || empty($codigoLinea)) {
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Faltan datos obligatorios.'
            ]);
            exit;
        }

        $fechaR = str_replace('T', ' ', $fechaR) . ':00';

        //Validar que no exita un registro previo sin cerrar
            $sqlCheck = "SELECT NADexist = CASE WHEN EXISTS (SELECT 1 from SPC_PERSONAL_NAD WHERE eliminado = 0
                            AND nomina = :nominaNAD AND codigo_linea = :codigoLineaNAD) THEN 1 ELSE 0 END,
                            otraLinea = CASE WHEN EXISTS
                            (SELECT 1 FROM
                                (SELECT PE.nomina FROM SPC_PERSONAL_ESTACION PE
                                        INNER JOIN SPC_ESTACIONES E on PE.id_estacion = E.id_estacion
                                    WHERE PE.fecha_fin IS NULL AND PE.nomina= :nomina AND E.codigo_linea <> :codigoLinea
                                        UNION ALL
                                    SELECT nomina FROM SPC_PERSONAL_NAD WHERE fechaE IS NULL AND nomina= :nomina2 
                                            AND codigo_linea <> :codigoLinea2
                                        UNION ALL
                                    SELECT nomina FROM SPC_PUNTOS_CAMBIO WHERE fechaHora_fin IS NULL AND nomina = :nomina3 
                                            AND codigo_linea <> :codigoLinea3
                                ) X
                            )THEN 1 ELSE 0 END,
                            lineaActual = CASE WHEN EXISTS
                            (SELECT 1 FROM
                                (SELECT PE.nomina FROM SPC_PERSONAL_ESTACION PE
                                        INNER JOIN SPC_ESTACIONES E on PE.id_estacion = E.id_estacion
                                    WHERE PE.fecha_fin IS NULL AND PE.nomina= :nomina4 AND E.codigo_linea = :codigoLinea4
                                        UNION  ALL

                                /* SELECT nomina FROM SPC_PERSONAL_NAD 
                                        WHERE fechaE IS NULL AND nomina= :nomina5  AND codigo_linea = :codigoLinea5 
                                    UNION ALL
                                 */
                                    SELECT nomina FROM SPC_PUNTOS_CAMBIO WHERE fechaHora_fin IS NULL AND nomina = :nomina6 
                                            AND codigo_linea = :codigoLinea6
                                ) Y
                            )THEN 1 ELSE 0 END;";

            $stmtCheck = $conn->prepare($sqlCheck);
            $stmtCheck->execute([   ':nominaNAD' => $nomina,
                                    ':codigoLineaNAD' => $codigoLinea,
                                    ':nomina' => $nomina,
                                    ':codigoLinea' => $codigoLinea,
                                    ':nomina2' => $nomina,
                                    ':codigoLinea2' => $codigoLinea,
                                    ':nomina3' => $nomina,
                                    ':codigoLinea3' => $codigoLinea,
                                    ':nomina4' => $nomina,
                                    ':codigoLinea4' => $codigoLinea,
                                    //':nomina5' => $nomina,
                                    //':codigoLinea5' => $codigoLinea,
                                    ':nomina6' => $nomina,
                                    ':codigoLinea6' => $codigoLinea
                                ]);

            $registro = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            /*
                if (!$registro) {
                        echo json_encode([
                            'estatus' => 'error', 
                            'mensaje' => 'Ocurrio un error al verificar la informacion',
                            'error' => $stmtCheck->errorInfo()
                        ]);
                        exit;
                } 
            */

            if ((int)$registro['NADexist'] == 1) {
                echo json_encode([
                    'estatus' => 'error', 
                    'mensaje' => 'Este empleado ya ha sido agregado al personal no asignado'
                ]);
                exit;
            }

            if ((int)$registro['otraLinea'] == 1) {
                echo json_encode([
                    'estatus' => 'error', 
                    'mensaje' => 'Este trabajador se encuentra asignado en otra linea'
                ]);
                exit;
            }

            if ((int)$registro['lineaActual'] == 1) {
                echo json_encode([
                    'estatus' => 'error', 
                    'mensaje' => 'Este trabajador ya se encuentra asignado a una estación.'
                ]);
                exit;
            }
        //Fin validar registro duplicado


        try { // Iniciar transacción
            $conn->beginTransaction();

            // Preparar la sentencia con parámetros
            $sql = "INSERT INTO SPC_PERSONAL_NAD (nomina, nombre, codigo_linea, turno, comentarios, fechaR, eliminado) 
                            VALUES (:nomina, :nombre, :codigo_linea, :turno, :comentarios, :fechaR, :eliminado)";

            $stmt = $conn->prepare($sql);

            // Ejecutar con los parámetros
            $stmt->execute([
                ':nomina' => $nomina, 
                ':nombre' => $nombre,
                ':codigo_linea' => $codigoLinea, 
                ':turno' => $turno, 
                ':comentarios' => $comentarios, 
                ':fechaR' => $fechaR, 
                ':eliminado'=> $eliminado,
            ]);

            // Confirmar la transacción
            $conn->commit();

            echo json_encode([  
                                'estatus' => 'ok',
                                'mensaje' => 'Registro insertado correctamente.',
                            ]);

        } catch (PDOException $e) {
            // Si ocurre algún error, revertir la transacción
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            // Respuesta JSON con el error
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Error al insertar el registro.',
                'detalle' => $e->getMessage()
            ]);
        }

    }

//Listar personal no asignado
else 
     if($opcion== '9'){
        $codigoLinea =  !empty($_POST['codigoLinea']) ? $_POST['codigoLinea'] : null;
        $turno = !empty($_POST['turno']) ? $_POST['turno'] : null;

            $sql= "SELECT id_registro, nomina, nombre, turno from SPC_PERSONAL_NAD where eliminado = 0 and codigo_linea= :codigoLinea and turno= :turno";

            $registro = $conn->prepare($sql);
            $response= array();

            if($registro -> execute([':codigoLinea' => $codigoLinea, ':turno' => $turno])){
                while($dsc= $registro->fetch(PDO::FETCH_ASSOC))
                    $response[] = $dsc;
            }

            else 
                $response = $registro->errorInfo()[2];

        echo json_encode($response);
    }

//Remover trabajador de una estacion
else 
    if($opcion=='10'){
        $idEstacion = $_POST['idEstacion'] ?? null;
        $nomina = $_POST['nomina'] ?? null;
        $turno = $_POST['turno'] ?? null;

        // Validar que se recibieron todos los datos
        if (!$idEstacion || !$nomina) {
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Faltan datos obligatorios.'
            ]);
            exit; 
        }

        try { // Iniciar transacción
            $conn->beginTransaction();

            // Preparar la sentencia con parámetros
            $sql = "UPDATE SPC_PERSONAL_ESTACION SET fecha_fin = GETDATE() WHERE id_estacion = :id_estacion 
                        AND nomina = :nomina AND fecha_fin IS NULL AND turno = :turno";
            $stmt = $conn->prepare($sql);

            // Ejecutar con los parámetros
            $stmt->execute([
                ':id_estacion' => $idEstacion,
                ':nomina' => $nomina,
                ':turno' => $turno
            ]);

            //Verificar si la persona se encuentra asingada en otra estacion o tiene algun punto de cambio
                $sqlVerificar = "SELECT asignacion = CASE WHEN EXISTS 
                        (SELECT 1 FROM 
                            (SELECT nomina FROM SPC_PERSONAL_ESTACION PE WHERE PE.fecha_fin IS NULL AND PE.nomina = :nomina AND PE.turno = :turno
                                        UNION ALL
                            SELECT nomina FROM SPC_PUNTOS_CAMBIO PC WHERE fechaHora_fin IS NULL and PC.nomina = :nomina AND PC.turno = :turno
                            ) as Z
                        ) THEN 1 ELSE 0 END";

                $stmtVerificar = $conn->prepare($sqlVerificar);
                $stmtVerificar->execute([':nomina' => $nomina,':turno' => $turno]);

                $resultado = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
                $asignacion = $resultado['asignacion'];

                //Si no hay registro de asignaciones actualizar el estatus del empleado
                if($asignacion == 0){ 
                    $sqlUpdateEP = "UPDATE SPC_PERSONAL SET estatus = 0 WHERE nomina = :nomina AND estatus NOT IN (0, 2)";
                    $stmtUpdateEP = $conn->prepare($sqlUpdateEP);
                    $stmtUpdateEP->execute([':nomina' => $nomina]);
                }
            //Fin verificacion

            // Confirmar la transacción
            $conn->commit();
            echo json_encode([
                'estatus' => 'ok',
                'mensaje' => 'Trabajador removido correctamente.',
                'asignacion' => $asignacion
            ]);
        } catch (PDOException $e) {
            // Si ocurre algún error, revertir la transacción
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }   
            // Respuesta JSON con el error
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Error al remover el trabajador.',
                'detalle' => $e->getMessage()
            ]);
        }
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

//Generar No. Control de cambio
else 
    if($opcion == '12'){
        $codigoLinea = !empty($_POST['codigoLinea']) ? $_POST['codigoLinea'] : null;

        $sql = "SELECT FORMAT(GETDATE(), 'yyyy/MM') + '/' +
                            RIGHT(CAST( ISNULL( MAX( CAST( SUBSTRING(
                                                    no_controlCambio,
                                                    LEN(no_controlCambio) - CHARINDEX('/', REVERSE(no_controlCambio)) + 2,
                                                    LEN(no_controlCambio)
                                                ) AS INT ) ), 0 ) + 1 AS VARCHAR), 3 ) AS no_control
                                FROM SPC_PUNTOS_CAMBIO WITH (UPDLOCK, HOLDLOCK)
                            WHERE codigo_linea = :codigoLinea AND fechaHora_inicio >= DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1)
                            AND fechaHora_inicio <  DATEADD(MONTH, 1,
                                    DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1));";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':codigoLinea' => $codigoLinea]);
        $noControlResult = $stmt->fetch(PDO::FETCH_ASSOC);

        if($noControlResult === false) {
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'No se pudo generar el No. de Control'
            ]);
        } 
        else {
            echo json_encode([
                'estatus' => 'ok',
                'noControl' => $noControlResult['no_control']
            ]);
            
        }
    }

//Registrar punto de cambio
else 
    if($opcion == '13'){
        $codigoLinea = !empty($_POST['codigoLinea']) ? $_POST['codigoLinea'] : null;
        $tipoCambio = !empty($_POST['tipoCambio']) ? $_POST['tipoCambio'] : null;
        $nominaPC = !empty ($_POST['nominaPC']) ? $_POST['nominaPC'] : null;
        $nombrePC = $_POST['nombrePC'] ?? null;
        $fechaInicio = !empty($_POST['fechaInicio']) ? $_POST['fechaInicio'] :  null;
        $turno = !empty($_POST['turno']) ? $_POST['turno'] : null;
        $motivo = !empty($_POST['motivo']) ? $_POST['motivo'] : null;
        $estaciones =  json_decode($_POST['estaciones'], true);

        $fechaHoraInicio = str_replace('T', ' ', $fechaInicio) . ':00';

        // Validar que se recibieron todos los datos
        if ( is_null($codigoLinea) || is_null($fechaInicio) || is_null($turno) || 
             is_null($nominaPC) || is_null($tipoCambio)) {
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Faltan datos obligatorios.'
            ]);
            exit; 
        }   

        if (!is_array($estaciones) || empty($estaciones)) {
            die("No se recibieron estaciones válidas");
        }

        // Tomar solo los values
        $idsEstaciones = array_column($estaciones, 'value');

        // Limpiar: únicos e ինտ
        $idsEstaciones = array_values(array_unique(array_map('intval', $idsEstaciones)));

        if (empty($idsEstaciones)) {
            die("No se encontraron IDs válidos en estaciones");
        }

        $placeholdersEstaciones = implode(',', array_fill(0, count($idsEstaciones), '?'));

        //Validar la existencia de un punto de cambio 
        $sqlCheckPC = "SELECT 
            PCexist = CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM SPC_PUNTOS_CAMBIO pc
                    INNER JOIN SPC_ESTACIONES_PC epc 
                        ON epc.idPC = pc.idPC
                    WHERE pc.fechaHora_fin IS NULL 
                    AND pc.turno = ? 
                    AND (epc.idE IN ($placeholdersEstaciones))
                ) THEN 1 ELSE 0 
            END,

            asignado = CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM SPC_PERSONAL_ESTACION 
                    WHERE id_estacion IN ($placeholdersEstaciones)
                    AND fecha_fin IS NULL 
                    AND turno = ? 
                    AND nomina = ?
                ) THEN 1 ELSE 0 
            END,

            otraLinea = CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM (
                        SELECT PE.nomina 
                        FROM SPC_PERSONAL_ESTACION PE   
                        INNER JOIN SPC_ESTACIONES E 
                            ON PE.id_estacion = E.id_estacion
                        WHERE PE.fecha_fin IS NULL 
                        AND PE.nomina = ? 
                        AND E.codigo_linea <> ?

                        UNION ALL

                        SELECT nomina 
                        FROM SPC_PUNTOS_CAMBIO 
                        WHERE fechaHora_fin IS NULL 
                        AND nomina = ? 
                        AND codigo_linea <> ?
                    ) X
                ) THEN 1 ELSE 0 
            END,

            codigo_linea = (
                SELECT TOP 1 P.codigo_linea
                FROM SPC_PERSONAL P
                WHERE P.nomina = ?
            );
        ";

            /*Revisar que el trabajador no este registrado en otro turno en esta linea*/
            $sqlCheckT = 'SELECT otroTurno = CASE WHEN EXISTS
                            (SELECT 1 FROM
                                (SELECT nomina FROM SPC_PERSONAL_ESTACION PE inner JOIN SPC_ESTACIONES E ON PE.id_estacion = E.id_estacion
                                        WHERE PE.fecha_fin IS NULL AND PE.nomina= :nomina AND E.codigo_linea = :codigoLinea AND PE.turno <> :turno
                                            UNION ALL
                                    SELECT nomina FROM SPC_PUNTOS_CAMBIO WHERE fechaHora_fin IS NULL AND nomina= :nomina and codigo_linea =:codigoLinea AND turno <> :turno
                                ) as Z
                            ) THEN 1 ELSE 0 END;';


            $stmtCheckPC = $conn->prepare($sqlCheckPC);
            $stmtCT = $conn->prepare($sqlCheckT);

            $params = array_merge(
                [$turno],
                $idsEstaciones,
                $idsEstaciones,
                [$turno, $nominaPC],
                [$nominaPC, $codigoLinea],
                [$nominaPC, $codigoLinea],
                [$nominaPC]
            );

            $stmtCheckPC->execute($params);

            
            $stmtCT->execute([  ':nomina' => $nominaPC,
                                ':codigoLinea' => $codigoLinea,
                                ':turno' => $turno,
                            ]);

            $registroPC = $stmtCheckPC->fetch(PDO::FETCH_ASSOC);
            $resultT = $stmtCT->fetch(PDO::FETCH_ASSOC);

            if (!$registroPC  || !$resultT) {
                    echo json_encode([
                        'estatus' => 'error', 
                        'mensaje' => 'Ocurrio un error al verificar la informacion',
                        'error' => $stmtCheckPC->errorInfo()
                    ]);
                    exit;
            }

            if ($registroPC['PCexist'] == 1) {
                echo json_encode([
                    'estatus' => 'error', 
                    'mensaje' => 'Esta estacion cuenta con un punto de cambio abierto. Favor de cerrarlo antes de registrar uno nuevo.'
                ]);
                exit;
            }

            if ($registroPC['asignado'] == 1) {
                echo json_encode([
                    'estatus' => 'error', 
                    'mensaje' => 'No se puede crear el punto de cambio ya que el trabajador está asignado a esta estación.'
                ]);
                exit;
            }

            if ($registroPC['otraLinea'] == 1) {
                echo json_encode([
                    'estatus' => 'error', 
                    'mensaje' => 'Este trabajador se encuentra asignado en otra linea'
                ]);
                exit;
            }

            if($resultT['otroTurno'] == 1){
                    // Ya existe
                        echo json_encode([
                            'estatus' => 'error',
                            'mensaje' => 'Este trabajador se encuentra asignado en otro turno'
                        ]);
                    exit;
            }

        try { 
            // Iniciar transacción
            $conn->beginTransaction();

            // Generar No. de Control
            /* $sqlNoControl = "SELECT FORMAT(GETDATE(), 'yyyy/MM') + '/' + RIGHT(CAST((SELECT COUNT(*) + 1 FROM SPC_PUNTOS_CAMBIO WHERE FORMAT(fechaHora_inicio, 'yyyy/MM')  =  FORMAT(GETDATE(), 'yyyy/MM')) AS VARCHAR), 3) as no_control;"; */

            $sqlNoControl = "SELECT FORMAT(GETDATE(), 'yyyy/MM') + '/' +
                                    RIGHT(CAST( ISNULL( MAX( CAST( SUBSTRING(
                                                            no_controlCambio,
                                                            LEN(no_controlCambio) - CHARINDEX('/', REVERSE(no_controlCambio)) + 2,
                                                            LEN(no_controlCambio)
                                                        ) AS INT ) ), 0 ) + 1 AS VARCHAR), 3 ) AS no_control
                                        FROM SPC_PUNTOS_CAMBIO WITH (UPDLOCK, HOLDLOCK)
                                 WHERE codigo_linea = :codigoLinea AND fechaHora_inicio >= DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1)
                                    AND fechaHora_inicio <  DATEADD(MONTH, 1,
                                         DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1));";

            $stmtNoControl = $conn->prepare($sqlNoControl);
            $stmtNoControl->execute([':codigoLinea' => $codigoLinea]);
            $noControlResult = $stmtNoControl->fetch(PDO::FETCH_ASSOC);

            if($noControlResult === false) {
                $conn->rollBack();
                echo json_encode([
                    'estatus' => 'error',
                    'mensaje' => 'No se pudo generar el No. de Control'
                ]);
                exit;
            } 

            else 
                $noControl = $noControlResult['no_control'];
            

            // Preparar la sentencia con parámetros
            $sql = "INSERT INTO SPC_PUNTOS_CAMBIO(no_controlCambio, fechaHora_inicio, motivo, tipo_cambio,
                                                    codigo_linea, estatusPC, turno, nomina,
                                                    nombre) 
                                                    OUTPUT INSERTED.idPC
                                values ( :no_control, :fechaHora_inicio, :motivo, :tipoCambio, :codigo_linea, 
                                         '1', :turno, :nomina_operador, :nombre)";

            $stmt = $conn->prepare($sql);

            // Ejecutar con los parámetros
            $stmt->execute([
                ':no_control' => $noControl,
                ':fechaHora_inicio' => $fechaHoraInicio,
                ':motivo' => $motivo,
                ':tipoCambio' => $tipoCambio,
                ':codigo_linea' => $codigoLinea,
                ':turno' => $turno,
                ':nomina_operador' => $nominaPC, 
                ':nombre' => $nombrePC
            ]);

            // Obtener el idPC generado
            $idPC = $stmt->fetchColumn();

            //Incertar las estaciones del PC
            $sqlIE = "INSERT INTO SPC_ESTACIONES_PC(idPC, idE) VALUES (:idPC, :idE)";
            $stmtE = $conn->prepare($sqlIE);

            foreach ($estaciones as $row) {
                //if (empty($row['nomina']) || empty($row['nombre']) || empty($row['estatus'])) { throw new Exception('Datos incompletos en asistencia');}
                $stmtE->execute([
                    ':idPC' => $idPC,
                    ':idE' => $row['value'],
                ]);
            }

                    $estatusEM = 1; //Asignado en la linea actual del empleado

                    //Si el codigoLinea de la linea es difernete al codigo_linea actual del empleado
                    if($codigoLinea != $registroPC['codigo_linea']) $estatusEM = 3; //Asignado en otra linea

                       $sqlUpdateEP = "UPDATE SPC_PERSONAL SET estatus = :estatus WHERE nomina = :nomina 
                                         AND estatus NOT IN (1, 2)";
                       $stmtUpdateEP = $conn->prepare($sqlUpdateEP);
                       $stmtUpdateEP->execute([':nomina' => $nominaPC, ':estatus' => $estatusEM]);

            // Confirmar la transacción
            $conn->commit();

            echo json_encode([
                'estatus' => 'ok',
                'mensaje' => 'Punto de cambio registrado correctamente.',
                'idPC' => $idPC
            ]);

        } catch (PDOException $e) {
            // Si ocurre algún error, revertir la transacción
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

              $conn->rollBack();

            // Respuesta JSON con el error
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Error al registrar el punto de cambio.',
                'detalle' => $e->getMessage()
            ]);
        }
    }

//Cerrar el punto de cambio
else 
    if($opcion=='14'){
        $idEstacion = !empty($_POST['idEstacion']) ? $_POST['idEstacion'] : null;
        $fechaFin = !empty($_POST['fechaCierre']) ? $_POST['fechaCierre'] : null;
        $comentarios = $_POST['notasAdicionales'] ?? null;
        $nomina =  $_POST['nomina'] ?? null;

        $idPC = $_POST['idPC'] ?? null;

        $fechaCierre = str_replace('T', ' ', $fechaFin) . ':00';

        // Validar que se recibieron todos los datos
        if (!$idEstacion || !$fechaFin || !$idPC) {
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Faltan datos obligatorios.'
            ]);
            exit; 
        }   

        try { 
            // Iniciar transacción
            $conn->beginTransaction();

            // Preparar la sentencia con parámetros
              $sql = "UPDATE SPC_PUNTOS_CAMBIO 
                        SET fechaHora_fin = :fechaHora_fin,
                            estatusPC = '3'
                    WHERE idPC = :idPC 
                        AND fechaHora_fin IS NULL";

            $stmt = $conn->prepare($sql);

            // Ejecutar con los parámetros
            $stmt->execute([
                ':fechaHora_fin' => $fechaCierre,
                ':idPC' => $idPC
            ]);


            //Insertar datos del cierre en la tabla de PC_CIERRE
            $sqlCierre = "INSERT INTO SPC_CIERRE_PC (idPC, fechaCierre, comentarios) 
                            VALUES (:idPC, :fechaCierre, :comentarios)";

            $stmtCierre = $conn->prepare($sqlCierre);


            // Ejecutar con los parámetros
            $stmtCierre->execute([
                ':idPC' => $idPC,
                ':fechaCierre' => $fechaCierre,
                ':comentarios' => $comentarios
            ]);

            //Verificar si la persona se encuentra asingada en otra estacion o tiene algun punto de cambio
                $sqlVerificar = "SELECT asignacion = CASE WHEN EXISTS 
                        (SELECT 1 FROM 
                            (SELECT nomina FROM SPC_PERSONAL_ESTACION PE WHERE PE.fecha_fin IS NULL AND PE.nomina = :nomina
                                        UNION ALL
                            SELECT nomina FROM SPC_PUNTOS_CAMBIO PC WHERE fechaHora_fin IS NULL AND PC.nomina = :nomina
                            ) as Z
                        ) THEN 1 ELSE 0 END";

                $stmtVerificar = $conn->prepare($sqlVerificar);
                $stmtVerificar->execute([':nomina' => $nomina]);

                $resultado = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
                $asignacion = $resultado['asignacion'];

                //Si no hay registro de asignaciones actualizar el estatus del empleado
                if($asignacion == 0){  //Disponible
                    $sqlUpdateEP = "UPDATE SPC_PERSONAL SET estatus = 0 WHERE nomina = :nomina AND estatus NOT IN (0, 2)";
                    $stmtUpdateEP = $conn->prepare($sqlUpdateEP);
                    $stmtUpdateEP->execute([':nomina' => $nomina]);
                }
            //Fin verificacion

            // Confirmar la transacción
            $conn->commit();

            echo json_encode([
                'estatus' => 'ok',
                'mensaje' => 'Punto de cambio cerrado correctamente.',
                'asignacion' => $asignacion
            ]);

        } catch (PDOException $e) {
            // Si ocurre algún error, revertir la transacción
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            // Respuesta JSON con el error
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Error al cerrar el punto de cambio.',
                'detalle' => $e->getMessage()
            ]);
        }
    }

//Consulta para obtener los datos de una sola estacion
else 
if ($opcion == '15') {
            $idEstacion = !empty($_POST['idEstacion']) ? $_POST['idEstacion'] : null;
            $idPC = !empty($_POST['idPC']) ? $_POST['idPC'] : null;
            $turno = !empty($_POST['turno']) ? $_POST['turno'] : null;

            $ahora = new DateTime();

            // Validar turno
            if ($turno == '1') {
                $inicio = new DateTime('today 08:00');
                $fin    = new DateTime('today 19:59');
            } else if ($turno == '2') {
                if ($ahora >= new DateTime('today 20:00')) {
                    $inicio = new DateTime('today 20:00');
                    $fin    = new DateTime('tomorrow 07:59');
                } else {
                    $inicio = new DateTime('yesterday 20:00');
                    $fin    = new DateTime('today 07:59');
                }
            } else {
                echo json_encode([
                    'estatus' => 'error',
                    'mensaje' => 'Turno no válido'
                ]);
                exit;
            }

            // Validar que venga al menos idEstacion o idPC
            if (!$idEstacion && !$idPC) {
                echo json_encode([
                    'estatus' => 'error',
                    'mensaje' => 'Debe enviar idEstacion o idPC.'
                ]);
                exit;
            }

            $fechaInicio = $inicio->format('Y-m-d H:i:s');
            $fechaFin    = $fin->format('Y-m-d H:i:s');

            /*
                Si viene idPC, significa que queremos actualizar todas las estaciones
                asociadas a ese punto de cambio.

                Si no viene idPC, usamos idEstacion para actualizar solo una estación.
            */
            if ($idPC) {
                $whereEstacion = "
                    E.id_estacion IN (
                        SELECT EPC.idE
                        FROM SPC_ESTACIONES_PC EPC
                        WHERE EPC.idPC = :idPC
                    )
                ";
            } else {
                $whereEstacion = "E.id_estacion = :idEstacion";
            }

            $sql = "SELECT E.id_estacion, E.nombre_estacion, E.requiere_certificacion AS isCertificate,
                    COALESCE(PC.nomina, EP.nomina) AS nomina,
                    COALESCE(PC.nombre, EP.nombre) AS nombre,
                    COALESCE(PC.fechaHora_inicio, EP.fecha_asignacion) AS fecha_asignacion,
                    E.codigo_linea, E.id_certificacion AS codigo_certificacion, PC.estatusPC, PC.idPC, 
                     A.estatus AS asistencia
                FROM SPC_ESTACIONES E

                OUTER APPLY (
                    SELECT TOP (1) PE.nomina, PE.nombre, PE.fecha_asignacion
                    FROM SPC_PERSONAL_ESTACION PE
                    WHERE PE.id_estacion = E.id_estacion AND PE.fecha_fin IS NULL AND PE.turno = :turno
                    ORDER BY PE.fecha_asignacion DESC, PE.id_asignacion DESC
                ) AS EP

                OUTER APPLY (
                    SELECT TOP (1) 
                        P.idPC, 
                        P.nomina, 
                        P.nombre, 
                        P.estatusPC, 
                        P.fechaHora_inicio
                    FROM SPC_PUNTOS_CAMBIO P
                    INNER JOIN SPC_ESTACIONES_PC EPC 
                        ON EPC.idPC = P.idPC
                    WHERE EPC.idE = E.id_estacion
                        AND P.fechaHora_fin IS NULL
                        AND P.turno = :turno
                    ORDER BY 
                        P.fechaHora_inicio DESC, 
                        P.idPC DESC
                ) AS PC

                OUTER APPLY (
                    SELECT TOP (1) 
                        RA.estatus
                    FROM SPC_REGISTRO_ASISTENCIA RA
                    WHERE RA.nomina = COALESCE(PC.nomina, EP.nomina)
                        AND RA.turno = :turno
                        AND RA.fecha_operacion >= :fecha_inicio
                        AND RA.fecha_operacion < :fecha_fin
                    ORDER BY 
                        RA.fecha_operacion DESC, 
                        RA.id_registro DESC
                ) AS A
                WHERE $whereEstacion ORDER BY E.id_estacion ";

            $stmt = $conn->prepare($sql);

            $params = [ ':turno' => $turno,
                        ':fecha_inicio' => $fechaInicio,
                        ':fecha_fin' => $fechaFin
                      ];

            if ($idPC) {
                $params[':idPC'] = $idPC;
            } else {
                $params[':idEstacion'] = $idEstacion;
            }

            $response = [];

            if ($stmt->execute($params)) {
                $estacionesResponse = [];

                while ($estacion = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Definir color de estación
                    if ($estacion['estatusPC'] == '1') {
                        $colorClass = 'station-color-2';
                    } else if (!empty($estacion['nomina'])) {
                        $colorClass = 'station-color-1';
                    } else {
                        $colorClass = 'station-color-7';
                    }

                    // Definir estatus de asistencia
                    $asistencia = '';

                    if (empty($estacion['nomina'])) {
                        $asistencia = 'pending';
                    } else if (!empty($estacion['asistencia'])) {
                        $asistencia = ($estacion['asistencia'] == '1' || $estacion['asistencia'] == '8') 
                            ? 'occupied' 
                            : 'absent';
                    } else if (empty($estacion['asistencia'])) {
                        $fechaAsignacion = new DateTime($estacion['fecha_asignacion']);

                        if ($fechaAsignacion >= $inicio && $fechaAsignacion <= $fin) {
                            $asistencia = 'occupied';
                        } else {
                            $asistencia = 'absent';
                        }
                    }

                    $estacionesResponse[] = [
                        'id'            => $estacion['id_estacion'],
                        'nomina'        => $estacion['nomina'],
                        'name'          => $estacion['nombre_estacion'],
                        'operator'      => !empty($estacion['nomina']) ? $estacion['nombre'] : '',
                        'status'        => $asistencia,
                        'certification' => $estacion['codigo_certificacion'],
                        'idPC'          => $estacion['idPC'],
                        'colorClass'    => $colorClass,
                        'estatusPC'     => $estacion['estatusPC'],
                        'isCertificate' => $estacion['isCertificate']
                    ];
                }

                $response = [
                    'estatus' => 'ok',
                    'estaciones' => $estacionesResponse
                ];
            } else {
                $response = [
                    'estatus' => 'error',
                    'mensaje' => $stmt->errorInfo()[2]
                ];
            }

            echo json_encode($response);
}

//Consulta para generar una lista de asistencia con los PC, PNAD y asignados 
else 
    if($opcion =='16'){
        $codigoLinea = empty(!$_POST['codigoLinea']) ? $_POST['codigoLinea'] : null;
        $ahora = new DateTime();
        $turno = !empty($_POST['turno']) ? $_POST['turno'] : null;

        //Verificar si ya existe un registro de asistencia
            //Turno 1
                $inicio = new DateTime('today 08:00');
                $fin    = new DateTime('today 19:59');

            //Turno 2
            if ($turno == '2') {
                if ($ahora >= new DateTime('today 20:00')) { // Después de las 8 pm
                    $inicio = new DateTime('today 20:00');
                    $fin    = new DateTime('tomorrow 07:59');
                } else { // Antes de las 8 pm
                    $inicio = new DateTime('yesterday 20:00');
                    $fin    = new DateTime('today 07:59');
                } //Despues de la 8 de la noche la fecha de inicio es hoy 8:00 pm fecha de fin mañana 8:00 am
                //Antes de 8 de la noche la fecha de inicio es ayer 8:00 pm y la fecha de fin hoy 8:00 am 
            }

        //Consultar el registro de asistencia de la linea en la tabla de asistencia
            $sqlV = "SELECT id_registro, nomina, nombre, codigo_linea, estatus, id_estacion, turno, nombres_estaciones AS nombre_estacion, comentarioAsistencia
                                FROM SPC_REGISTRO_ASISTENCIA 
                                    WHERE turno = :turno AND fecha_operacion >= :fechaInicio AND fecha_operacion <= :fechaFin AND codigo_linea = :codigoLinea";
            $stmtV = $conn->prepare($sqlV);
            $stmtV->execute([':turno' => $turno,
                             ':fechaInicio' => $inicio->format('Y-m-d H:i'),
                             ':fechaFin'    => $fin->format('Y-m-d H:i'),
                             ':codigoLinea' => $codigoLinea
                           ]);
            
        //Generar lista de asistencia 
            //Si existe un registro de asistencia mostrar el registro
            $personal = $stmtV->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($personal)) {
                 $response = ['personal' => $personal];
            }
           
            //Si no existe un registro de asistencia generar uno
            else {

                $sql ="SELECT p.nomina, p.nombre,
                            STRING_AGG(CAST(x.id_estacion AS NVARCHAR(10)), ',') AS id_estacion,
                            COALESCE(STRING_AGG(e.nombre_estacion, ', '), 'SIN ASIGNAR') AS nombre_estacion
                        FROM SPC_PERSONAL p LEFT JOIN (
                                                        SELECT nomina, id_estacion FROM SPC_PERSONAL_ESTACION WHERE fecha_fin IS NULL AND turno = :turno
                                                            UNION
                                                        SELECT nomina, EPC.idE as id_estacion FROM SPC_PUNTOS_CAMBIO PC INNER JOIN SPC_ESTACIONES_PC EPC ON PC.idPC = EPC.idPC 
                                                        WHERE fechaHora_fin IS NULL AND turno = :turno AND codigo_linea = :codigoLinea
                                                      ) x
                            ON x.nomina = p.nomina LEFT JOIN SPC_ESTACIONES e ON e.id_estacion = x.id_estacion
                        WHERE p.codigo_linea = :codigoLinea AND p.turno = :turno and p.estatus NOT IN (2)
                            GROUP BY p.nomina, p.nombre ORDER BY p.nomina;";

                $stmt = $conn->prepare($sql);
                $response= array();

                // Ejecutar con los parámetros
                if($stmt->execute([':codigoLinea' => $codigoLinea, ':turno' => $turno])){
                    while($personal= $stmt->fetch(PDO::FETCH_ASSOC)){
                        $response['personal'][] = $personal;
                    }
                }

                else {
                    $response['personal'] = $stmt->errorInfo()[2];
                }


                $response['resumen'] =  null;
            }

        echo json_encode($response);
    }

//Registro de asistencia
else 
    if($opcion =='17'){
           $datosAsistencia =  json_decode($_POST['datosAsistencia'], true);
           $codigoLinea = !empty($_POST['codigoLinea']) ? $_POST['codigoLinea'] : null;
           $stationsData= !empty($_POST['stationsData']) ? $_POST['stationsData'] : null;
           $turno = !empty($_POST['turno']) ? $_POST['turno'] : null;
           $results = '';

            if (!$datosAsistencia || !is_array($datosAsistencia) || !$codigoLinea || !$turno ) {
                echo json_encode(['estatus' => 'error', 
                                  'mensaje'=>'Datos inválidos'
                                ]);
                exit;
            }

            // Validar horario según turno
            $hora_actual = new DateTime();
            $turno = $_POST['turno'] ?? null;

                if ($turno == '1') {
                    $inicio_turno = new DateTime('today 08:00');
                    $fin_turno = new DateTime('today 19:59');
                    if ($hora_actual < $inicio_turno || $hora_actual > $fin_turno) {
                        echo json_encode([
                            'estatus' => 'error',
                            'mensaje' => 'El registro de asistencia para el Turno 1 solo puede realizarse entre las 8:00 AM y las 7:59 PM.'
                        ]);
                        exit;
                    }
                } 

                else 
                    if ($turno == '2') {               
                        if ($hora_actual>= new DateTime('today 20:00')) { // Después de las 8 pm
                            $inicio_turno = new DateTime('today 20:00');
                            $fin_turno    = new DateTime('tomorrow 07:59');
                        } else { // Antes de las 8 pm
                            $inicio_turno = new DateTime('yesterday 20:00');
                            $fin_turno    = new DateTime('today 07:59');
                        } 

                    if ($hora_actual < $inicio_turno || $hora_actual > $fin_turno) {
                        echo json_encode([
                            'estatus' => 'error',
                            'mensaje' => 'El registro de asistencia para el Turno 2 solo puede realizarse entre las 8:00 PM y las 7:59 AM del día siguiente.'
                        ]);
                        exit;
                    }
                } 
                
                else {
                    echo json_encode([
                        'estatus' => 'error',
                        'mensaje' => 'Turno no válido.'
                    ]);
                    exit;
                }

        try {
            $conn->beginTransaction();
            $sql = "INSERT INTO SPC_REGISTRO_ASISTENCIA (nomina, nombre, estatus, codigo_linea, turno, id_estacion, nombres_estaciones, comentarioAsistencia) 
                        VALUES (:nomina, :nombre, :estatus, :codigo_linea, :turno, :id_estacion, :nombres_estaciones, :observacionesAsistencia)";
            $stmt = $conn->prepare($sql);

            $sqlI = "INSERT INTO SPC_HISTORIAL_LAYOUT(codigo_linea, turno, layout) VALUES(:codigoLinea, :turno, :stationsData)";
            $stmtI = $conn->prepare($sqlI);

            foreach ($datosAsistencia as $row) {
                //if (empty($row['nomina']) || empty($row['nombre']) || empty($row['estatus'])) { throw new Exception('Datos incompletos en asistencia');}
                $stmt->execute([
                    ':nomina' => $row['nomina'],
                    ':nombre' => $row['nombre'],
                    ':estatus' => $row['estatus'],
                    ':id_estacion' => $row['id_estacion'],
                    ':nombres_estaciones' => $row['nombres_estaciones'],
                    ':observacionesAsistencia' => $row['observacionesAsistencia'],
                    ':codigo_linea' => $codigoLinea,
                    ':turno' => $turno
                ]);
            }
        
            $stmtI->execute([':codigoLinea' => $codigoLinea, 
                             ':stationsData' => $stationsData,
                             ':turno' => $turno
                            ]);

            $conn->commit();
            $results = array('estatus' => 'ok',
                              'mensaje' => 'Se ha hecho el registro de asistencia');

        } catch (Exception $e) {
            $conn->rollBack();
              $results = array('estatus' => 'error',
                               'mensaje' => 'Ocurrió un error al realizar el registro',
                               'error' => $e->getMessage());
        }

        // Devolver resultado
        echo json_encode($results);
    }

//Actualizar registro de asistencia 
else 
    if($opcion == '18'){
        $idRegistro = $_POST['id_registro'];
        $estatus = !empty($_POST['estatus']) ? $_POST['estatus'] : null;
        $observacionesAsistencia = isset($_POST['observacionesAsistencia']) ? $_POST['observacionesAsistencia'] : null;

        if(!$estatus && !isset($observacionesAsistencia)){
              echo json_encode(array('estatus' => 'ok',
                                     'mensaje' => 'No se ha modificado ningun valor'
                                     )
                              );
            exit;
        }

        $set = [];
        $params = [':idRegistro' => $idRegistro];

        if (!empty($estatus)) {
            $set[] = 'estatus = :estatus';
            $params[':estatus'] = $estatus;
        }

        if (isset($observacionesAsistencia)) {
            $set[] = 'comentarioAsistencia = :comentarios';
            $params[':comentarios'] = $observacionesAsistencia;
        }

        $sql = 'UPDATE SPC_REGISTRO_ASISTENCIA 
                SET ' . implode(', ', $set) . ' 
                WHERE id_registro = :idRegistro';

        try {
            $conn->beginTransaction();

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $conn->commit();

            $results = [
                'estatus' => 'ok',
                'mensaje' => 'Se ha actualizado el registro',
            ];

        } catch (Exception $e) {
              $conn->rollBack();
              $results = array('estatus' => 'error',
                               'mensaje' => 'Ocurrió un error al realizar el registro: '.$sql,
                               'error' => $e->getMessage());
        }

        // Devolver resultado
        echo json_encode($results);
    }

//Registro de cambio de turno
else 
    if($opcion == '19'){
       $datosAsistenciaCheck = !empty($_POST['datosAsistenciaCheck']) ? json_decode($_POST['datosAsistenciaCheck']) : null;
       $turnoActual = !empty($_POST['turnoActual']) ? $_POST['turnoActual'] : null;
       $turnoCambio = !empty($_POST['turnoCambio']) ? $_POST['turnoCambio'] : null;
       $codigoLinea = !empty($_POST['codigoLinea']) ? $_POST['codigoLinea'] : null;
       //$placeholders = [];

        if(!$codigoLinea || !is_array($datosAsistenciaCheck) || !$turnoActual || !$turnoCambio) {
                echo json_encode(['estatus' => 'error', 
                                  'mensaje'=> 'Faltan datos obligatorios']);
                exit;
        }
        
        //foreach ($datosAsistenciaCheck as $indice => $nomina) $placeholders[] = ':nomina'.$indice;        
        $sql = "UPDATE SPC_PUNTOS_CAMBIO SET turno = :turnoCambio WHERE turno = :turnoActual 
                    AND codigo_linea = :codigoLinea AND fechaHora_fin IS NULL 
                    AND nomina IN (". implode(',', $datosAsistenciaCheck).")";
                //AND nomina IN (" . implode(',', $placeholders) . ")";

        $sql2 = "UPDATE SPC_PERSONAL SET turno = :turnoCambio WHERE turno = :turnoActual 
                    AND codigo_linea = :codigoLinea AND estatus != 2 
                    AND nomina IN (". implode(',', $datosAsistenciaCheck).")";

        $sql3 = "UPDATE PE SET PE.turno = :turnoCambio from SPC_PERSONAL_ESTACION AS PE
                        LEFT JOIN SPC_ESTACIONES as E ON PE.id_estacion = E.id_estacion
                    WHERE PE.turno = :turnoActual AND E.codigo_linea = :codigoLinea AND PE.fecha_fin IS NULL 
                         AND PE.nomina IN (". implode(',', $datosAsistenciaCheck).")";

        try {
            $conn->beginTransaction();
            $stmt = $conn->prepare($sql);
            $stmt2 = $conn->prepare($sql2);
            $stmt3 = $conn->prepare($sql3);

            $stmt->bindParam(':turnoCambio', $turnoCambio);
            $stmt->bindParam(':turnoActual', $turnoActual);
            $stmt->bindParam(':codigoLinea', $codigoLinea);

            $stmt2->bindParam(':turnoCambio', $turnoCambio);
            $stmt2->bindParam(':turnoActual', $turnoActual);
            $stmt2->bindParam(':codigoLinea', $codigoLinea);

            $stmt3->bindParam(':turnoCambio', $turnoCambio);
            $stmt3->bindParam(':turnoActual', $turnoActual);
            $stmt3->bindParam(':codigoLinea', $codigoLinea);

            // Vincular cada valor de nómina
            //foreach ($datosAsistenciaCheck as $indice => $nomina) $stmt->bindParam(':nomina' . $indice, $datosAsistenciaCheck[$indice]);
            $stmt->execute();
            $stmt2->execute();
            $stmt3->execute();

            $conn->commit();
            $results = array('estatus' => 'ok',
                             'mensaje' => 'Se ha actualizado el registro');

        } catch (Exception $e) {
            $conn->rollBack();
            $results = array('estatus' => 'error',
                            'mensaje' => 'Ocurrió un error al realizar el registro',
                            'error' => $e->getMessage());
        }

        // Devolver resultado
        echo json_encode($results);
    }

//Obtener los datos de asignacion de un trabajdor en una estacion 
else 
    if($opcion == '20'){
        $nomina = !empty($_POST['nomina']) ? $_POST['nomina'] : null;
        $idEstacion = !empty($_POST['idEstacion']) ? $_POST['idEstacion'] : null;

        $sql= "SELECT nomina, nombre,  FORMAT(fecha_asignacion, 'yyyy/MM/dd HH:mm') AS fecha_inicio, fecha_fin, turno, 
                        comentarios AS descripcion, 'ESTACION' AS origen FROM SPC_PERSONAL_ESTACION 
                WHERE nomina = :nomina AND id_estacion = :idEstacion AND fecha_fin IS NULL
                    UNION ALL
                SELECT nomina, nombre, FORMAT(fechaHora_inicio, 'yyyy/MM/dd HH:mm') AS fecha_inicio, fechaHora_fin AS fecha_fin, 
                        turno, motivo AS descripcion, 'PUNTO_CAMBIO' AS origen 
                    FROM SPC_PUNTOS_CAMBIO PC LEFT JOIN SPC_ESTACIONES_PC EPC on PC.idPC = EPC.idPC
                WHERE nomina = :nomina2 AND EPC.idE = :idEstacion2 AND fechaHora_fin IS NULL";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nomina', $nomina);
            $stmt->bindParam(':idEstacion', $idEstacion);
            $stmt->bindParam(':nomina2', $nomina);
            $stmt->bindParam(':idEstacion2', $idEstacion);
            $response= array();

            if($stmt->execute()){
                $fila = $stmt->fetch(PDO::FETCH_ASSOC);
                  $response = array('estatus' => 'ok',
                                    'nomina' => $fila['nomina'],
                                    'nombre'=> $fila['nombre'],
                                    'fecha_inicio'=> $fila['fecha_inicio'],
                                    //'fecha_fin'=> $fila['fecha_fin'],
                                    'turno'=> $fila['turno'],
                                    'descripcion'=> $fila['descripcion'],
                                    //'origen'=> $fial['origen'] Tabla de la que probiene el valor
                                    );
            }      

            else 
                $response = $stmt->errorInfo()[2];

        echo json_encode($response);
    }

//Eliminar personal NAD
else 
    if($opcion == '21'){
        $idRegistro = !empty($_POST['idRegistro']) ? $_POST['idRegistro'] : null;

        $sql = "UPDATE SPC_PERSONAL_NAD SET fechaE = getDate(), eliminado = 1 WHERE id_registro = :idRegistro";        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idRegistro', $idRegistro);

        if($stmt->execute()){
            $response = array('estatus' => 'ok',
                              'mensaje' => 'Se ha eliminado el registro'
                            );
        }      

        else 
            $response = array('estatus' => 'error',
                              'mensaje' => $stmt->errorInfo()[2]);

        echo json_encode($response);
    }

//Obtener los registros del historial de layout guardado
else 
    if($opcion == '22'){
        $codigoLinea = $_POST['codigoLinea'] ?? null;
        $fecha = $_POST['fecha'] ?? null;  // formato YYYY-MM-DD
        $turno = $_POST['turno'] ?? null;

        if(!$codigoLinea || !$fecha || !$turno){
            echo json_encode(['estatus' => 'error', 'mensaje' => 'Faltan datos obligatorios.']);
            exit;
        }

        // Consulta para obtener los registros del historial
        $sql = "SELECT idR, format(fechaR, 'yyyy-MM-dd HH:mm:ss') as fechaR FROM SPC_HISTORIAL_LAYOUT 
                    WHERE codigo_linea = :codigoLinea AND turno = :turno AND CAST(fechaR AS DATE) = :fecha
                ORDER BY fechaR DESC";
        $stmt = $conn->prepare($sql);

         $response= array();

        if($stmt->execute([':codigoLinea' => $codigoLinea,':turno' => $turno,':fecha' => $fecha])){
            while($rh = $stmt->fetch(PDO::FETCH_ASSOC))
                    $response[] = $rh;
        }

        if($response){
            echo json_encode([
                'estatus' => 'ok',
                'registros' => $response
            ]);
        } else {
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'No hay layout guardado para la fecha y turno seleccionados.'
            ]);
        }

    }

// Consultar historial de layout
else 
    if($opcion == '23'){
        $idR = $_POST['idR'] ?? null;

        if(!$idR){
            echo json_encode(['estatus' => 'error', 'mensaje' => 'Faltan datos obligatorios.']);
            exit;
        }

        // Buscar layout guardado 
        $sql = "SELECT layout, fechaR FROM SPC_HISTORIAL_LAYOUT WHERE idR = :idR ORDER BY fechaR DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':idR' => $idR,
        ]);

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if($resultado){
            echo json_encode([
                'estatus' => 'ok',
                'layout' => json_decode($resultado['layout']), // ya es array
                'fecha' => $resultado['fechaR']
            ]);
        } else {
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'No hay layout guardado para la fecha y turno seleccionados.'
            ]);
        }
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

//Obtener el dia y numero de evaluacion realizadas del punto de cambio
else 
    if($opcion == '25'){
        $idPC = !empty($_POST['idPC']) ? $_POST['idPC'] : null;

        if(!$idPC) {
                echo json_encode(['estatus' => 'error', 'mensaje' => 'Faltan datos obligatorios']);
            exit;
        }

        try {
                $sql = "SELECT ((COUNT(*) ) / 2) + 1 AS numeroDia, 
                               ((COUNT(*) ) % 2) + 1 AS numeroEvaluacion 
                        FROM SPC_EVALUACIONPC where idPC = :idPC";

                $stmt = $conn->prepare($sql);
                $stmt->execute([':idPC' => $idPC]);
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
          
                if ($resultado) {
                    echo json_encode([
                        'estatus' => 'ok',
                        'numeroDia' => json_decode($resultado['numeroDia']),
                        'numeroEvaluacion' => json_decode($resultado['numeroEvaluacion']),
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

//Registrar evaluacion del PC
else 
 if($opcion == '26'){
    $idPC = !empty($_POST['idPC']) ? $_POST['idPC'] : null;
    $numeroDia = !empty($_POST['numeroDia']) ? $_POST['numeroDia'] : null;
    $numeroEvaluacion = !empty($_POST['numeroEvaluacion']) ? $_POST['numeroEvaluacion'] : null;

    $fechaEvaluacion = !empty($_POST['fechaEvaluacion']) ? $_POST['fechaEvaluacion'] : null;
    $metrica1 = isset($_POST['metrica1']) ? $_POST['metrica1'] : null;
    $metrica2 = isset($_POST['metrica2']) ? $_POST['metrica2'] : null;
    $metrica3 = isset($_POST['metrica3']) ? $_POST['metrica3'] : null;
    $comentarios = !empty($_POST['comentarios']) ? $_POST['comentarios'] : null; //comentarios o contramedidas

    $fechaEvaluacion = str_replace('T', ' ', $fechaEvaluacion);

        if(!$idPC || !$numeroDia || !$numeroEvaluacion) {
                echo json_encode(['estatus' => 'error', 
                                  'mensaje' => 'Faltan datos obligatorios']);
                exit;
        }

        try {
                $sql = "INSERT INTO SPC_EVALUACIONPC(idPC, fechaEvaluacion, numeroDia, numeroEvaluacion, metrica1, metrica2, metrica3, comentarios)
                            VALUES (:idPC, :fechaEvaluacion, :numeroDia, :numeroEvaluacion, :metrica1, :metrica2, :metrica3, :comentarios)";

                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':idPC' => $idPC,
                    ':fechaEvaluacion' => $fechaEvaluacion, 
                    ':numeroDia' => $numeroDia,
                    ':numeroEvaluacion' => $numeroEvaluacion,
                    ':metrica1' => $metrica1,
                    ':metrica2' => $metrica2,
                    ':metrica3' => $metrica3,
                    ':comentarios' => $comentarios
                ]);
                
                echo json_encode([
                        'estatus' => 'ok',
                        'mensaje' => "Se ha hecho el registro de la evaluacion"
                    ]);

        } catch (PDOException $e) {
            // Error de conexión o consulta
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => $e->getMessage()
            ]);
        }
}

//Registro individual de asistencia
else 
 if($opcion == '27'){
        $codigoLinea = !empty($_POST['codigoLinea']) ? $_POST['codigoLinea'] : null;
        $turno = !empty($_POST['turno']) ? $_POST['turno'] : null;
        $nomina = !empty($_POST['nomina']) ? $_POST['nomina'] : null;
        $nombre = !empty($_POST['nombre']) ? $_POST['nombre'] : null;
        $estatusAsistencia = !empty($_POST['estatusAsistencia']) ? $_POST['estatusAsistencia'] : null;
        $estacion = !empty($_POST['estacion']) ? $_POST['estacion'] : null;
        $nombreEstacion = !empty($_POST['nombreEstacion']) ? $_POST['nombreEstacion'] : null;
        $comentarios = !empty($_POST['comentarios']) ? $_POST['comentarios'] : null;

            if (!$codigoLinea || !$nomina || !$turno || !$estatusAsistencia) {
                echo json_encode(['estatus' => 'error',
                                  'mensaje'=>'Datos inválidos'
                                ]);
                exit;
            }

            // Validar horario según turno
            $hora_actual = new DateTime();

                if ($turno == '1') {
                    $inicio_turno = new DateTime('today 08:00');
                    $fin_turno = new DateTime('today 19:59');
                    if ($hora_actual < $inicio_turno || $hora_actual > $fin_turno) {
                        echo json_encode([
                            'estatus' => 'error',
                            'mensaje' => 'El registro de asistencia para el Turno 1 solo puede realizarse entre las 8:00 AM y las 7:59 PM.'
                        ]);
                        exit;
                    }
                }

                else
                    if ($turno == '2') {
                            if ($hora_actual>= new DateTime('today 20:00')) { // Después de las 8 pm
                                $inicio_turno = new DateTime('today 20:00');
                                $fin_turno    = new DateTime('tomorrow 07:59');
                            } else { // Antes de las 8 pm
                                $inicio_turno = new DateTime('yesterday 20:00');
                                $fin_turno    = new DateTime('today 07:59');
                            }

                            if ($hora_actual < $inicio_turno || $hora_actual > $fin_turno) {
                                echo json_encode([
                                    'estatus' => 'error',
                                    'mensaje' => 'El registro de asistencia para el Turno 2 solo puede realizarse entre las 8:00 PM y las 7:59 AM del día siguiente.'
                                ]);
                                exit;
                            }
                    }

                else {
                    echo json_encode([
                        'estatus' => 'error',
                        'mensaje' => 'Turno no válido.'
                    ]);
                    exit;
                }

        try {
            $conn->beginTransaction();

            // VALIDAR SI YA EXISTE ASISTENCIA PARA ESTA NÓMINA EN EL TURNO ACTUAL
            $sqlValidarAsistencia = "SELECT COUNT(*) AS total FROM SPC_REGISTRO_ASISTENCIA
                                WHERE nomina = :nomina AND turno = :turno AND fecha_operacion >= :inicio_turno
                            AND fecha_operacion <= :fin_turno";

            $stmtValidarAsistencia = $conn->prepare($sqlValidarAsistencia);
            $stmtValidarAsistencia->execute([
                ':nomina' => $nomina,
                ':turno' => $turno,
                ':inicio_turno' => $inicio_turno->format('Y-m-d H:i:s'),
                ':fin_turno' => $fin_turno->format('Y-m-d H:i:s')
            ]);

            $existeAsistencia = $stmtValidarAsistencia->fetch(PDO::FETCH_ASSOC);

            if ($existeAsistencia['total'] > 0) {
                $conn->rollBack();
                echo json_encode([
                    'estatus' => 'error',
                    'mensaje' => 'La asistencia de esta persona ya fue registrada en el turno actual'
                ]);
                exit;
            }

            $sql = "INSERT INTO SPC_REGISTRO_ASISTENCIA (nomina, nombre, estatus, codigo_linea, turno, id_estacion, nombres_estaciones, comentarioAsistencia)
                        VALUES (:nomina, :nombre, :estatus, :codigo_linea, :turno, :id_estacion, :nombres_estaciones, :comentarioAsistencia)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':nomina' => $nomina,
                ':nombre' => $nombre,
                ':estatus' => $estatusAsistencia,
                ':id_estacion' => $estacion,
                ':nombres_estaciones' => $nombreEstacion,
                ':comentarioAsistencia' => $comentarios,
                ':codigo_linea' => $codigoLinea,
                ':turno' => $turno
            ]);

            $conn->commit();
            $results = array('estatus' => 'ok',
                              'mensaje' => 'Se ha hecho el registro de asistencia');

        } catch (Exception $e) {
            $conn->rollBack();
              $results = array('estatus' => 'error',
                               'mensaje' => 'Ocurrió un error al realizar el registro',
                               'error' => $e->getMessage());
        }

        echo json_encode($results);
}

//Registro de personal
else 
 if($opcion == '28'){
        $codigoLinea = !empty($_POST['codigoLinea']) ? $_POST['codigoLinea'] : null;
        $nomina = !empty($_POST['nomina']) ? $_POST['nomina'] : null;
        $nombre = !empty($_POST['nombre']) ? $_POST['nombre'] : null;
        $operaciones = !empty($_POST['operaciones']) ? $_POST['operaciones'] : null;
        $fecha = !empty($_POST['fecha']) ? $_POST['fecha'] : null;
        $turno = !empty($_POST['turno']) ? $_POST['turno'] : null;

        $fecha = str_replace('T', ' ', $fecha);
       
            if (!$codigoLinea || !$nomina || !$nombre) {
                echo json_encode(['estatus' => 'error',
                                  'mensaje'=>'Datos inválidos'
                                ]);
                exit;
            }

        try {
            $conn->beginTransaction();

             // VALIDAR SI LA NOMINA YA EXISTE
                $sqlValidar = "SELECT COUNT(*) AS total FROM SPC_PERSONAL WHERE nomina = :nomina";

                $stmtValidar = $conn->prepare($sqlValidar);
                $stmtValidar->execute([':nomina' => $nomina]);
                $existe = $stmtValidar->fetch(PDO::FETCH_ASSOC);
                if ($existe['total'] > 0) {
                    $conn->rollBack();
                    echo json_encode([
                                        'estatus' => 'error',
                                        'mensaje' => 'La nómina ya se encuentra registrada'
                                    ]);
                    exit;
                }

            //INSERTAR REGISTRO
                $sql = "INSERT INTO SPC_PERSONAL(nomina, nombre, estatus, codigo_linea, fecha_registro, turno)
                            VALUES (:nomina, :nombre, :estatus, :codigo_linea, :fecha_registro, :turno)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':nomina' => $nomina,
                    ':nombre' => $nombre,
                    ':estatus' => '0', //0 DISPONIBLE 1 ASIGNADO 2 ELIMINADO 
                    ':codigo_linea' => $codigoLinea,
                    ':fecha_registro' => $fecha,
                    ':turno' => $turno
                ]);


            // DECODIFICAR JSON DE OPERACIONES
                $operacionesArray = json_decode($operaciones, true);

                if (!is_array($operacionesArray)) {
                    throw new Exception("El formato de operaciones es inválido");
                }

                // INSERTAR CADA OPERACION EN SPC_ILU
                $sqlIlu = "INSERT INTO SPC_ILU (nomina, idE, estatus) VALUES(:nomina, :idE, 0)";
                $stmtIlu = $conn->prepare($sqlIlu);

                foreach ($operacionesArray as $operacion) {
                    $idE = isset($operacion['value']) ? $operacion['value'] : null;
                    if (!$idE) {continue;}
                    $stmtIlu->execute([':nomina' => $nomina,':idE' => $idE]);
                }

                include('./conexionEmpleado.php');

                //Guardar la imagen del trabajador
                    $sqlCheck = "SELECT No_Nomina as nomina, nombre, foto FROM empleado_mst WHERE No_Nomina = :nomina";
                    $stmtCheck = $connE->prepare($sqlCheck);
                    $stmtCheck->execute([':nomina' => $nomina]);
                    $empleado = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                    $nombre_archivo = ""; // Valor por defecto

                    if ($empleado && !empty($empleado['foto'])) {
                        // Detectar tipo de imagen
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $tipo_mime = $finfo->buffer($empleado['foto']);

                        // Mapear extensión según MIME type
                        $extensiones = [
                            'image/jpeg' => 'jpg',
                            'image/jpg' => 'jpg',
                            'image/png' => 'png',
                            'image/gif' => 'gif',
                            'image/bmp' => 'bmp'
                        ];

                        $extension = $extensiones[$tipo_mime] ?? 'bin';
                        $nombre_archivo = $nomina . "." . $extension;

                        $ruta = "../img/personal/" . $nombre_archivo;

                        // Guardar la imagen en el directorio
                        file_put_contents($ruta, $empleado['foto']);
                    }
                //Fin guardar la imagen



                $conn->commit();
                $results = array('estatus' => 'ok',
                                'mensaje' => 'Se ha hecho el registro del trabajador');
        } catch (Exception $e) {
            $conn->rollBack();
              $results = array('estatus' => 'error',
                               'mensaje' => 'Ocurrió un error al realizar el registro',
                               'error' => $e->getMessage());
        }

        echo json_encode($results);
}

//Listado de personal
else 
    if($opcion=="29"){
         $codigoLinea = !empty($_POST['codigoLinea']) ? $_POST['codigoLinea'] : null;
         $turno = !empty($_POST['turno']) ? $_POST['turno'] : 0;

        // Validar que se recibieron todos los datos
        if (!$codigoLinea) {
                echo json_encode([
                    'estatus' => 'error',
                    'mensaje' => 'Faltan datos obligatorios.'
                ]);
            exit; 
        }

        try {
            $sql= "SELECT nomina, nombre, estatus FROM SPC_PERSONAL where codigo_linea = :codigo_linea and estatus !=2";

            $params[':codigo_linea'] = $codigoLinea;

            // Filtro nomina
            if ($turno != 0) {
                $sql .= " AND turno = :turno";
                $params[':turno'] = $turno;
            }

            $registro = $conn->prepare($sql);
            $response= array();

            if($registro->execute($params)){
                while($dsc= $registro->fetch(PDO::FETCH_ASSOC))
                    $response[] = $dsc;
            }

            else 
                $response = $registro->errorInfo()[2];

            echo json_encode($response);

         } catch (Exception $e) {
            $results = array('estatus' => 'error',
                               'mensaje' => 'Ocurrió un error al realizar el registro',
                               'error' => $e->getMessage());
        }
    }

//OBTENER LISTADO DE OPERACIONES LIBERADAS POR TRABAJADOR
else 
    if($opcion=='30'){
        $nomina = !empty($_POST['nomina']) ? $_POST['nomina'] : null;
        $codigoLinea = !empty($_POST['codigoLinea']) ? $_POST['codigoLinea'] : null;

        // Validar que se recibieron todos los datos
        if (!$codigoLinea || !$nomina) {
                echo json_encode([
                    'estatus' => 'error',
                    'mensaje' => 'Error al enviar los datos.'
                ]);
            exit; 
        }

        try {
            $sql= "SELECT ILU.idE, C.nombre_certificacion as nombre_estacion FROM SPC_ILU ILU
                        INNER JOIN SPC_CERTIFICACIONES as C on C.idCR = ILU.idE
                    WHERE ILU.nomina = :nomina AND ILU.estatus <> 1";

            $registro = $conn->prepare($sql);
            $response = [];

            if($registro -> execute([ /*':codigo_linea' => $codigoLinea,*/ ':nomina'=> $nomina])){
                while($dsc= $registro->fetch(PDO::FETCH_ASSOC))
                        $response[] = $dsc;
            }

            else {
                $response = $registro->errorInfo()[2];
                echo json_encode(array ('estatus'=> 'error', 'error' => $response));
                exit;
            }

            echo json_encode(array ('estatus'=> 'ok', 'data' => $response ));

        } catch (Exception $e) {
            $results = array('estatus' => 'error',
                            'mensaje' => 'Ocurrió un error al realizar el registro',
                            'error' => $e->getMessage());
            echo json_encode($results);
        }
}

//FUNCION PARA GENERAR LOS DATOS PARA EL LISTADO DE OPERACIONES, PERSONAS ASIGNADAS Y LISTADO DE PERSONAS LIBERADAS POR ESTACION
else 
    if($opcion == '31'){
        $codigoLinea = !empty($_POST['codigoLinea']) ? $_POST['codigoLinea'] : null;
        $turno = !empty($_POST['turno']) ? $_POST['turno'] : null;

        // Validar datos
        if (!$codigoLinea || !$turno) {
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Error al enviar los datos.'
            ]);
            exit;
        }

        try {
            $response = [];

            // ESTACIONES + ASIGNADOS
            $sqlOperaciones = "SELECT E.id_estacion, E.nombre_estacion, PE.nomina, PE.nombre
                                FROM SPC_ESTACIONES E
                                    LEFT JOIN SPC_PERSONAL_ESTACION PE ON E.id_estacion = PE.id_estacion
                                     AND PE.fecha_fin IS NULL AND PE.turno = :turno
                                 WHERE E.codigo_linea = :codigo_linea";

            $operaciones = $conn->prepare($sqlOperaciones);
            $operaciones->execute([
                ':codigo_linea' => $codigoLinea,
                ':turno' => $turno
            ]);

            // LIBERADOS
            $sqlLiberados = "SELECT E.id_estacion, E.nombre_estacion, I.nomina, P.nombre
                                    FROM SPC_ESTACIONES E
                                        INNER JOIN SPC_ILU I ON E.id_certificacion = I.idE
                                        INNER JOIN SPC_PERSONAL P ON P.nomina = I.nomina
                            WHERE E.codigo_linea = :codigo_linea AND P.turno = :turno
                                    AND P.codigo_linea = :codigo_linea AND I.estatus != 1";

            $liberados = $conn->prepare($sqlLiberados);
            $liberados->execute([':codigo_linea' => $codigoLinea,':turno' => $turno]);

            // ARMAR ESTACIONES
            while($row = $operaciones->fetch(PDO::FETCH_ASSOC)) {
                $idEstacion = $row['id_estacion'];
                if(!isset($response[$idEstacion])) {
                    $response[$idEstacion] = [
                        'id_estacion' => $idEstacion,
                        'nombre_estacion' => $row['nombre_estacion'],
                        'asignados' => [],
                        'liberados' => []
                    ];
                }

                // Si hay asignado
                if(!empty($row['nomina'])) {
                    $response[$idEstacion]['asignados'][] = [
                        'nomina' => $row['nomina'],
                        'nombre' => $row['nombre']
                    ];
                }
            }

            // AGREGAR LIBERADOS
            while($row = $liberados->fetch(PDO::FETCH_ASSOC)) {
                $idEstacion = $row['id_estacion'];
                if(isset($response[$idEstacion])) {
                    $response[$idEstacion]['liberados'][] = [
                        'nomina' => $row['nomina'],
                        'nombre' => $row['nombre']
                    ];
                }
            }

            // Reindexar
            $response = array_values($response);
            echo json_encode([
                'estatus' => 'ok',
                'data' => $response
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Ocurrió un error al realizar el registro',
                'error' => $e->getMessage()
            ]);
        }
}

//Agregar operaciones
else 
if ($opcion == '32') {
    $nomina = !empty($_POST['nomina']) ? $_POST['nomina'] : null;
    $operacion = !empty($_POST['estacionId']) ? $_POST['estacionId'] : null;

    // Guardar fecha y hora actual
    $fecha = date('Y-m-d H:i:s');

    // Validar que existan los datos necesarios
    if (!$operacion || !$nomina) {
        echo json_encode([
            'estatus' => 'error',
            'mensaje' => 'Error al recibir los datos'
        ]);
        exit;
    }

    try {
        $conn->beginTransaction();

        // VALIDAR SI EXISTE EL REGISTRO
        $sqlValidar = "SELECT estatus FROM SPC_ILU WHERE nomina = :nomina AND idE = :idE";
        $stmtValidar = $conn->prepare($sqlValidar);
        $stmtValidar->execute([':nomina' => $nomina, ':idE' => $operacion]);

        $existe = $stmtValidar->fetch(PDO::FETCH_ASSOC);

        // Si existe
        if ($existe) {
            // Evaluar si el estatus es diferente de 1 (eliminado)
            if ($existe['estatus'] != 0) {
                // No está eliminado: actualizar fecha del registro correspondiente a la nomina y el idE
                $sqlUpdate = "UPDATE SPC_ILU SET estatus = 0 WHERE nomina = :nomina AND idE = :idE";
                $stmtUpdate = $conn->prepare($sqlUpdate);
                $stmtUpdate->execute([
                                        ':nomina' => $nomina,
                                        ':idE' => $operacion
                                    ]);

                $mensaje = 'Registro actualizado correctamente.';
            } 

            else {
                $mensaje = 'El trabajador ya cuenta con este registro';
            }

            $conn->commit();
            echo json_encode([
                'estatus' => 'ok',
                'mensaje' => $mensaje
            ]);
            exit;
        }

        // Si NO existe: INSERTAR EL REGISTRO EN LA TABLA SPC_ILU
        $sqlIlu = "INSERT INTO SPC_ILU (nomina, idE, estatus, fecha_registro) VALUES (:nomina, :idE, 0, :fecha)";
        $stmtIlu = $conn->prepare($sqlIlu);
        $stmtIlu->execute([
            ':nomina' => $nomina,
            ':idE' => $operacion,
            ':fecha' => $fecha
        ]);

        $conn->commit();
        echo json_encode([
            'estatus' => 'ok',
            'mensaje' => 'Se ha agregado la operacion'
        ]);

    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode([
            'estatus' => 'error',
            'mensaje' => 'Ocurrió un error al realizar el registro',
            'error' => $e->getMessage()
        ]);
    }
}

//Eliminar operaciones
else 
if ($opcion == '33') {
    $nomina = !empty($_POST['nomina']) ? $_POST['nomina'] : null;
    $operacion = !empty($_POST['estacionId']) ? $_POST['estacionId'] : null;

    if (!$operacion || !$nomina) {
        echo json_encode([
            'estatus' => 'error',
            'mensaje' => 'Error al recibir los datos'
        ]);
        exit;
    }

    try {
        $conn->beginTransaction();

        // Verificar si el registro existe y obtener su estatus actual
        $sqlCheck = "SELECT estatus FROM SPC_ILU WHERE nomina = :nomina AND idE = :idE";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->execute([':nomina' => $nomina, ':idE' => $operacion]);
        $registro = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$registro) {
            // No existe el registro
            $conn->rollBack();
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'El registro no existe'
            ]);
            exit;
        }

        if ($registro['estatus'] == 1) {
            // Ya estaba eliminado, no se hace nada adicional
            $conn->commit(); // O rollback, pero como no hubo cambios, commit es válido
            echo json_encode([
                'estatus' => 'ok',
                'mensaje' => 'El registro ya se encontraba eliminado'
            ]);
            exit;
        }

        // Cambiar el estatus del registro a 1 (eliminado)
        //$fecha = date('Y-m-d H:i:s');
        $sqlUpdate = "UPDATE SPC_ILU SET estatus = 1 WHERE nomina = :nomina AND idE = :idE";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->execute([
            ':nomina' => $nomina,
            ':idE'    => $operacion
        ]);

        $conn->commit();
        echo json_encode([
            'estatus' => 'ok',
            'mensaje' => 'Registro eliminado correctamente'
        ]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode([
            'estatus' => 'error',
            'mensaje' => 'Ocurrió un error al eliminar el registro',
            'error'   => $e->getMessage()
        ]);
    }
}

//Listado de personal liberado por certificacion
else 
if($opcion == '34'){
        $codigoLineaPost = isset($_POST['codigoLinea']) ? trim((string)$_POST['codigoLinea']) : '';
        $ordenLineaPost = isset($_POST['ordenLinea']) ? trim((string)$_POST['ordenLinea']) : '';
        $turnoPost = isset($_POST['turno']) ? trim((string)$_POST['turno']) : '';
        $idCRPost = isset($_POST['idCR']) ? trim((string)$_POST['idCR']) : '';

        $codigoLinea = ($codigoLineaPost !== '' && $codigoLineaPost !== '0') ? $codigoLineaPost : null;
        $ordenLinea = ($ordenLineaPost !== '' && $ordenLineaPost  !== '0') ? $ordenLineaPost : null;
        $turno = ($turnoPost !== '' && $turnoPost !== '0') ? $turnoPost : null;
        $idCR = ($idCRPost !== '' && $idCRPost !== '0') ? $idCRPost : null;

        if (!$idCR) {
            echo json_encode(['estatus' => 'error',
                              'mensaje' => 'Error al recibir los datos'
                            ]);
            exit;
        }

        try {
            $params = [':idCR' => $idCR];

            /* ordenLinea:
                0 = Personal de la línea actual
                1 = Personal de otra línea
            */
         if ($ordenLinea !== null) {
                $campoOrdenLinea = " CASE WHEN p.codigo_linea = :ordenLineaSelect 
                                        THEN 0
                                        ELSE 1 
                                    END AS ordenLinea";

                $params[':ordenLineaSelect'] = $ordenLinea;
            } else {
                $campoOrdenLinea = " 0 AS ordenLinea";
            }

            $sqlBase = "SELECT i.nomina, p.nombre, p.codigo_linea, p.turno, p.estatus,
                            $campoOrdenLinea FROM SPC_ILU AS i
                        INNER JOIN SPC_PERSONAL AS p ON i.nomina = p.nomina
                        WHERE i.estatus = 0  AND i.idE = :idCR ";

            // Filtrar datos por línea
            if ($codigoLinea !== null) {
                $sqlBase .= " AND p.codigo_linea = :codigoLinea AND p.estatus <> 2";

                $params[':codigoLinea'] = $codigoLinea;
            }

            // Filtro por turno
            if ($turno !== null) {
                $sqlBase .= " AND p.turno = :turno";

                $params[':turno'] = $turno;
            }

            // Consulta final usando subconsulta
            $sqlOperaciones = "SELECT q.nomina, q.nombre, q.codigo_linea, q.turno, q.estatus, q.ordenLinea
                                FROM ( $sqlBase) AS q";

            // Si no existe filtro de línea, aplicar regla por ordenLinea
            if ($codigoLinea === null) {
                $sqlOperaciones .= " WHERE (q.ordenLinea = 1 AND q.estatus IN (0,3) )
                                        OR
                                    ( q.ordenLinea = 0 AND q.estatus <> 2 )";
            }

            $sqlOperaciones .= " ORDER BY q.ordenLinea, q.nombre";

            $liberados = $conn->prepare($sqlOperaciones);
            $liberados->execute($params);

            $response = $liberados->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'estatus' => 'ok',
                'data' => $response
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Ocurrió un error al generar los datos',
                'error'   => $e->getMessage()
            ]);
        }
}

//Obtener ultima fecha de operacion de un trabajador en una estacion
else 
  if($opcion == '35'){
        $nomina = $_POST['nomina'] ?? null;
        $idE = (!empty($_POST['idE'])) ? $_POST['idE'] : null;

        if(empty($nomina) || empty($idE)) {
                echo json_encode(['estatus' => 'error',
                                  'error' => "Error al buscar al trabajador"
                                ]);
            exit;
        }

        try {
            //Consulta para obtener el listado de estaciones actuales del trabajador
                $sqlEstaciones = "SELECT TOP 1 fecha_inicio, fecha_fin FROM (
                                        SELECT fecha_asignacion AS fecha_inicio, fecha_fin FROM SPC_PERSONAL_ESTACION WHERE id_estacion = :idE AND nomina = :nomina
                                            UNION ALL
                                        SELECT fechaHora_inicio AS fecha_inicio, fechaHora_fin AS fecha_fin FROM SPC_PUNTOS_CAMBIO PC 
                                            INNER JOIN SPC_ESTACIONES_PC EPC ON PC.idPC = EPC.idPC 
                                        WHERE EPC.idE = :idE AND nomina = :nomina 
                                    ) AS registros ORDER BY CASE WHEN fecha_fin IS NULL THEN 1 ELSE 0 END DESC, ISNULL(fecha_fin, fecha_inicio) DESC";

                $stmtE = $conn->prepare($sqlEstaciones);            
                $stmtE->execute([':nomina' => $nomina, ':idE' => $idE]);
                $dataEstaciones = $stmtE->fetch(PDO::FETCH_ASSOC);
                echo json_encode([
                                    'estatus' => 'ok',
                                    'allEst' => $dataEstaciones
                                ]);
        } catch (PDOException $e) {
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => $e->getMessage()
            ]);
        }
}

//Generar resumen de asistencia
else 
  if($opcion == '36'){
        $codigoLinea = empty(!$_POST['codigoLinea']) ? $_POST['codigoLinea'] : null;
        $ahora = new DateTime();
        $turno = !empty($_POST['turno']) ? $_POST['turno'] : null;

        //Setear rango de fecha y hora para el resumen
            //Turno 1
                $inicio = new DateTime('today 08:00');
                $fin    = new DateTime('today 19:59');

            //Turno 2
            if ($turno == '2') {
                if ($ahora >= new DateTime('today 20:00')) { // Después de las 8 pm
                    $inicio = new DateTime('today 20:00');
                    $fin    = new DateTime('tomorrow 07:59');
                } else { // Antes de las 8 pm
                    $inicio = new DateTime('yesterday 20:00');
                    $fin    = new DateTime('today 07:59');
                } //Despues de la 8 de la noche la fecha de inicio es hoy 8:00 pm fecha de fin mañana 8:00 am
                //Antes de 8 de la noche la fecha de inicio es ayer 8:00 pm y la fecha de fin hoy 8:00 am 
            }

        $sqlR = "SELECT
                    -- Contar las asistencias
                    SUM(CASE WHEN estatus IN ('1', '8') THEN 1 ELSE 0 END) AS asistencias,

                    -- Contar las faltas
                    SUM(CASE WHEN estatus IN ('2', '5', '6', '7') THEN 1 ELSE 0 END) AS faltas,

                    -- Contar los permisos
                    SUM(CASE WHEN estatus = '3' THEN 1 ELSE 0 END) AS permisos,

                    -- Contar las vacaciones
                    SUM(CASE WHEN estatus = '4' THEN 1 ELSE 0 END) AS vacaciones,

                    -- Contar las incapacidades
                    SUM(CASE WHEN estatus = '9' THEN 1 ELSE 0 END) AS incapacidad,

                    --Calcular el personal disponible
                    SUM(CASE WHEN (nombres_estaciones = 'SIN ASIGNAR' and estatus IN ('1', '8')) THEN 1 ELSE 0 END) AS disponibles,

                    -- Calcular el porcentaje de asistencia
                    ROUND (CAST(SUM(CASE WHEN estatus IN ('1', '8') THEN 1 ELSE 0 END) AS FLOAT) /
                            NULLIF(COUNT(estatus), 0) * 100, 2
                            ) AS porcentajeA
                FROM SPC_REGISTRO_ASISTENCIA
                    WHERE fecha_operacion > :fechaInicio AND fecha_operacion < :fechaFin 
                        AND codigo_linea = :codigoLinea";

        try {
            $stmtR = $conn->prepare($sqlR);
            $stmtR->execute([':fechaInicio' => $inicio->format('Y-m-d H:i'),
                             ':fechaFin'    => $fin->format('Y-m-d H:i'),
                             ':codigoLinea' => $codigoLinea
                            ]);
            $resumen = $stmtR->fetch(PDO::FETCH_ASSOC);

            $results = [
                'estatus' => 'ok',
                'resumen' => $resumen
            ];

        } catch (Exception $e) {
              $conn->rollBack();
              $results = array('estatus' => 'error',
                               'mensaje' => 'Ocurrió un error al consultar la informacion',
                               'error' => $e->getMessage());
        }
        // Devolver resultado
        echo json_encode($results);
}

//Eliminar personal
else 
 if($opcion == '37'){
        $nomina = $_POST['nomina'] ?? null;

        // Validar que se recibieron todos los datos
        if (!$nomina) {
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Error de conexion al enviar los datos'
            ]);
            exit; 
        }

        try { 
            // Iniciar transacción
            $conn->beginTransaction();

             //ACtualizar estatus a eliminado
                $sqlUpdate = "UPDATE SPC_PERSONAL SET estatus = 2 WHERE nomina = :nomina AND estatus NOT IN (2)";
                $stmtUpdate = $conn->prepare($sqlUpdate);
                $ejecutado = $stmtUpdate->execute([':nomina' => $nomina]);

                if (!$ejecutado) {
                    throw new Exception('No se pudo ejecutar la consulta de actualización.');
                }
            
            // Conf irmar la transacción 
            $filasAfectadas = $stmtUpdate->rowCount();

            if ($filasAfectadas > 0) {
            
                //Cerrar los registros de asignaciones en las estaciones
                $sql1 = "UPDATE SPC_PERSONAL_ESTACION SET fecha_fin = GETDATE() 
                            WHERE nomina = :nomina AND fecha_fin IS NULL";

                $stmt1 = $conn->prepare($sql1);
                $stmt1->execute([':nomina' => $nomina]);
                    
                //Cerrar los registros de los puntos de cambio
                $sql2 = "UPDATE SPC_PUNTOS_CAMBIO SET fechaHora_fin = GETDATE(), estatusPC = 3 
                            WHERE nomina = :nomina AND fechaHora_fin IS NULL";

                $stmt2 = $conn->prepare($sql2);
                $stmt2->execute([':nomina' => $nomina]);

            $conn->commit();

                echo json_encode([
                    'estatus' => 'ok',
                    'mensaje' => 'Trabajador eliminado',
                ]);
            } 

            else {
                $conn->rollBack();

                echo json_encode([
                    'estatus' => 'warning',
                    'mensaje' => 'No se modificó ningún registro. La nómina no existe o ya estaba eliminada.',
                    'filas_afectadas' => 0
                ]);
            }

        } catch (PDOException $e) {
            // Si ocurre algún error, revertir la transacción
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }   
            // Respuesta JSON con el error
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Error inseperado',
                'detalle' => $e->getMessage()
            ]);
        }
}

//Obtener registro de puntos de cambio
else 
if($opcion == '38'){
    $codigoLinea = !empty($_POST['codigoLinea']) ? $_POST['codigoLinea'] : 0;
    $turno = !empty($_POST['turno']) ? $_POST['turno'] : 0;
    
    if (!$codigoLinea) {
        echo json_encode([
            'estatus' => 'error',
            'mensaje' => 'Error al recibir los datos'
        ]);
     exit;
    }

    try {
         $response = [];
            $sqlPC = "SELECT PC.idPC, PC.no_controlCambio, PC.nomina, PC.nombre, PC.motivo,
                            PC.fechaHora_inicio, PC.fechaHora_fin, PC.tipo_cambio, PC.estatusPC
                        from SPC_PUNTOS_CAMBIO PC where PC.codigo_linea = :codigoLinea";

            $params[':codigoLinea'] = $codigoLinea;

            // Filtro turno
            if ($turno != 0) {
                $sqlPC .= " AND turno = :turno";
                $params[':turno'] = $turno;
            }

            $sqlPC .=" ORDER BY PC.idPC";

            $pc = $conn->prepare($sqlPC);
            $pc->execute($params);
            $response = $pc->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'estatus' => 'ok',
                'data' => $response
            ]);

    } catch (Exception $e) {
        echo json_encode([
            'estatus' => 'error',
            'mensaje' => 'Ocurrió un error al generar los datos',
            'error'   => $e->getMessage()
        ]);
    }
}

//Obtener listado de operaciones asignadas por operador
else 
if($opcion == '39'){
     $codigoLinea = !empty($_POST['codigoLinea']) ? $_POST['codigoLinea'] : 0;
     $turno = !empty($_POST['turno']) ? $_POST['turno'] : 0;
     $nomina = !empty($_POST['nomina']) ? $_POST['nomina'] : 0;
    
    if (!$nomina || !$turno || !$codigoLinea) {
        echo json_encode([
            'estatus' => 'error',
            'mensaje' => 'Error al recibir los datos'
        ]);
        exit;
    }

    try {
         $response = [];
         $sqlOperaciones = "SELECT PE.id_estacion, E.nombre_estacion, PE.fecha_asignacion AS fecha_inicio, PE.fecha_fin, PE.comentarios
                                FROM SPC_PERSONAL_ESTACION PE INNER JOIN SPC_ESTACIONES E ON PE.id_estacion = E.id_estacion
                                    WHERE E.codigo_linea = :codigoLinea AND PE.fecha_fin IS NULL AND PE.turno = :turno AND PE.nomina = :nomina
                            UNION
                            SELECT EPC.idE AS id_estacion, E.nombre_estacion, PC.fechaHora_inicio AS fecha_inicio, PC.fechaHora_fin as fecha_fin, PC.motivo AS comentarios
                                FROM SPC_PUNTOS_CAMBIO PC INNER JOIN SPC_ESTACIONES_PC EPC ON PC.idPC = EPC.idPC
                            INNER JOIN SPC_ESTACIONES E ON E.id_estacion = EPC.idE
                            WHERE PC.codigo_linea = :codigoLinea AND PC.fechaHora_fin IS NULL AND PC.turno = :turno AND PC.nomina = :nomina";

            $operaciones = $conn->prepare($sqlOperaciones);
            $operaciones->execute([":codigoLinea" => $codigoLinea, 
                                   ":turno" => $turno, 
                                   ":nomina" => $nomina 
                                  ]);
            $response = $operaciones->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'estatus' => 'ok',
                'data' => $response
            ]);

    } catch (Exception $e) {
        echo json_encode([
            'estatus' => 'error',
            'mensaje' => 'Ocurrió un error al generar los datos',
            'error'   => $e->getMessage()
        ]);
    }
}

//Obtener listado de certificaciones
else 
if($opcion == '40'){
     $codigoLinea = !empty($_POST['codigoLinea']) ? $_POST['codigoLinea'] : 0;
     //Agregar campo de area y si existe filtrar certificaciones por area sino mostrar todas las certificaciones
    
     /*
        if (!$codigoLinea) {
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Error al recibir los datos'
            ]);
        exit;
    } */

    try {
         $response = [];
         $sql = "SELECT idCR, nombre_certificacion from SPC_CERTIFICACIONES";

            $certificaciones = $conn->prepare($sql);
           $certificaciones->execute();
            $response = $certificaciones->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'estatus' => 'ok',
                'certificaciones' => $response
            ]);

    } catch (Exception $e) {
        echo json_encode([
            'estatus' => 'error',
            'mensaje' => 'Ocurrió un error al generar los datos',
            'error'   => $e->getMessage()
        ]);
    }
}

//Obtener listado de estaciones en las que el trabajador puede operar o esta liberado
else 
if($opcion == '41'){
     $codigoLinea = !empty($_POST['codigoLinea']) ? $_POST['codigoLinea'] : 0;
     $nomina = !empty($_POST['nomina']) ? $_POST['nomina'] : 0;

        if (!$codigoLinea || !$nomina) {
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Error al recibir los datos'
            ]);
            exit;
        } 

    try {
         $response = [];
         $sql = "SELECT E.id_estacion, E.nombre_estacion FROM SPC_ILU I 
                    INNER JOIN SPC_ESTACIONES E ON E.id_certificacion = I.idE 
                 WHERE E.codigo_linea = :codigoLinea AND I.nomina = :nomina";

           $estaciones = $conn->prepare($sql);
           $estaciones->execute([':codigoLinea'=> $codigoLinea, ':nomina' => $nomina]);
           $response = $estaciones->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'estatus' => 'ok',
                'estaciones' => $response
            ]);

    } catch (Exception $e) {
        echo json_encode([
            'estatus' => 'error',
            'mensaje' => 'Ocurrió un error al generar los datos',
            'error'   => $e->getMessage()
        ]);
    }
}

//Obtener detalle del punto de cambio
else 
    if($opcion == '42'){
        $idPC = !empty($_POST['idPC']) ? $_POST['idPC'] : 0;
        
        if (!$idPC) {
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Error al consultar la informacion'
            ]);
            exit;
        } 

        try {     
                $response = [];

                //DATOS DEL PC
                $sqlPC = "SELECT PC.no_controlCambio, PC.fechaHora_inicio, PC.fechaHora_fin, 
                                PC.motivo, PC.tipo_cambio, PC.estatusPC, 
                                PC.turno, PC.nomina, p.nombre
                            FROM SPC_PUNTOS_CAMBIO PC LEFT JOIN SPC_PERSONAL P ON PC.nomina = P.nomina
                        WHERE idPC = :idPC";

                //ESTACIONES ASIGNADAS AL PC
                $sqlEs = "SELECT EPC.idE, E.nombre_estacion FROM SPC_ESTACIONES_PC EPC INNER JOIN 
                            SPC_ESTACIONES E ON EPC.idE = E.id_estacion
                        WHERE EPC.idPC = :idPC";

                //EVALUACION DEL PC
                $sqlEva = "SELECT idE, fechaEvaluacion, numeroDia, numeroEvaluacion, 
                                metrica1, metrica2, metrica3, comentarios, fechaRegistro 
                            FROM SPC_EVALUACIONPC WHERE idPC = :idPC";

                //CIERRE DEL PC
                $sqlCierre = "SELECT fechaCierre, comentarios FROM SPC_CIERRE_PC WHERE idPC = :idPC";

                //Obtener los datos del punto de cambio
                $PC = $conn->prepare($sqlPC);
                $PC->execute([':idPC'=> $idPC]);
                $dataPC = $PC->fetchAll(PDO::FETCH_ASSOC);

                //Obtener estaciones involucradas en el pc
                $estaciones = $conn->prepare($sqlEs);
                $estaciones->execute([':idPC'=> $idPC]);
                $dataEstaciones = $estaciones->fetchAll(PDO::FETCH_ASSOC);

                //Obtener estaciones involucradas en el pc
                $evaluacionPC = $conn->prepare($sqlEva);
                $evaluacionPC->execute([':idPC'=> $idPC]);
                $evaluacion = $evaluacionPC->fetchAll(PDO::FETCH_ASSOC);

                //Obtener estaciones involucradas en el pc
                $cierrePC = $conn->prepare($sqlCierre);
                $cierrePC->execute([':idPC'=> $idPC]);
                $cierre = $cierrePC->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode([
                    'estatus' => 'ok',
                    'dataPC' => $dataPC,
                    'estaciones' => $dataEstaciones,
                    'evaluaciones' => $evaluacion,
                    'cierre' => $cierre,
                ]);

        } catch (Exception $e) {
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Ocurrió un error al generar los datos',
                'error'   => $e->getMessage()
            ]);
        }
}
?>