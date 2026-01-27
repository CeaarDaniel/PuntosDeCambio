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
    $encargado = $_POST['encargado'] ?? null;
    $nombreLinea = $_POST['nombreLinea'] ?? null;
    $descripcion = $_POST['descripcion'] ?? null;

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
                                     'operator' => 'Juan Pérez', 
                                     'status' => 'occupied', 
                                     'certification'=> $certificacion, 
                                     'x' => $x, 
                                     'y'=> $y, 
                                     'colorClass'=> 'station-color-1' ]
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
        $nomina = $_POST['nomina'] ?? null;
        $nombre = $_POST['nombre'] ?? null;
        $estacion = $_POST['estacion'] ?? null;
        $fecha = $_POST['fecha'] ?? null;
        $turno = $_POST['turno'] ?? null;
        $comentarios = $_POST['comentarios'] ?? null;

        // Validar que se recibieron todos los datos
        if (!$nomina) {
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Faltan datos obligatorios.'
            ]);
            exit; 
        }


        try { // Iniciar transacción
            $conn->beginTransaction();

          // Verificar si ya existe
            $sql_check = "SELECT 1 FROM SPC_PERSONAL_ESTACION 
                        WHERE id_estacion = :id_estacion 
                            AND nomina = :nomina
                            AND fecha_fin IS NULL";

            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->execute([
                ':id_estacion' => $estacion,
                ':nomina' => $nomina
            ]);

            if ($stmt_check->fetch()) {
                // Ya existe
                echo json_encode([
                    'estatus' => 'error',
                    'mensaje' => 'Esta persona ya cuenta con una asignación en la estación seleccionada'
                ]);
            } else {
                // Insertar
                $sql_insert = "INSERT INTO SPC_PERSONAL_ESTACION 
                            (id_estacion, nomina, nombre, fecha_asignacion, turno, comentarios)
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

                $conn->commit();

                include('./conexionEmpleado.php');

                        //Guardar la imagen del operador
        
                        // --- Obtener la foto del empleado ---
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
                

                echo json_encode([
                    'estatus' => 'ok',
                    'mensaje' => 'Registro insertado correctamente.',
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
                'mensaje' => 'Error al insertar el registro.',
                'detalle' => $e->getMessage()
            ]);
        }
    }

//LISTADO DE LINEAS REGISTRADAS
else 
    if($opcion=="4"){
        $sql= "SELECT codigo_linea, nombre_linea from SPC_LINEAS";

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

        // Preparar la sentencia con parámetros
        $sql= "SELECT E.id_estacion, E.nombre_estacion, EP.nomina, EP.nombre, E.codigo_linea, E.codigo_certificacion, E.posicion_x, E.posicion_y 
                        FROM SPC_ESTACIONES E 
                    LEFT JOIN (SELECT* from SPC_PERSONAL_ESTACION WHERE fecha_fin IS NULL) AS EP ON E.id_estacion = EP.id_estacion
                WHERE codigo_linea= :codigoLinea ";

        $stmt = $conn->prepare($sql);
        $response= array();

        // Ejecutar con los parámetros
        if($stmt->execute([':codigoLinea' => $codigoLinea])){
            while($estacion= $stmt->fetch(PDO::FETCH_ASSOC))
                $response[] = array( 'id' => $estacion['id_estacion'],
                                     'nomina' => $estacion['nomina'],
                                     'name' => $estacion['nombre_estacion'], 
                                     'operator' =>  !empty($estacion['nomina']) ? $estacion['nombre'] : '',  
                                     'status' => !empty($estacion['nomina']) ? 'occupied' : 'pending', //pending: sin asignar, occupied: operador asignado
                                     'certification' => $estacion['codigo_certificacion'], 
                                     'x' => $estacion['posicion_x'],
                                     'y' => $estacion['posicion_y'] ,
                                     'colorClass' => !empty($estacion['nomina']) ? 'station-color-1' : 'station-color-7'   //1 asistencia, 3 falta, 2 o 6 punto de cambio
                                   );
        }

        else 
            $response = $stmt->errorInfo()[2];

    echo json_encode($response);
 }

//Guardar/actualizar la posicion de las estaciones en el layout
else 
    if($opcion == '6'){
            $layoutPosition =  json_decode($_POST['layoutPosition'], true);

            if (!$layoutPosition || !is_array($layoutPosition)) {
                echo json_encode(['error' => 'Datos inválidos']);
                exit;
            }

         
            $sql = "UPDATE SPC_ESTACIONES SET posicion_x = :x, posicion_y = :y
                      WHERE id_estacion = :id";
            $stmt = $conn->prepare($sql);

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

                // Confirmar transacción
                $conn->commit();

                $results= array('estatus' => 'ok',
                                'mensaje' => 'se ha guardado el layout');


            } catch (PDOException $e) {
                // Revertir en caso de error
                $conn->rollBack();
                echo json_encode( array('error' => $e->getMessage(), 
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
        include('./conexionEmpleado.php');
        $nomina = $_POST['nomina'] ?? null;


        try {
                $sql = "SELECT nombre FROM empleado_mst WHERE No_Nomina = :nomina";
            
                $stmt = $connE->prepare($sql);
                $stmt->bindParam(':nomina', $nomina, PDO::PARAM_INT);
                $stmt->execute();

                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($resultado) {
                    echo json_encode([
                        'estatus' => 'ok',
                        'nombre' => $resultado['nombre']
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'false'
                    ]);
                }

        } catch (PDOException $e) {
            // Error de conexión o consulta
            echo json_encode([
                'status' => 'error',
                'mensaje' => $e->getMessage()
            ]);
        }
    }


//Registrar personal sin asignar a una estacion
else 
    if($opcion =='8'){
        $nomina = $_POST['nomina'] ?? null;
        $turno = $_POST['turno'] ?? null;
        $fechaR = $_POST['fechaR'] ?? null;
        $comentarios = $_POST['comentarios'] ?? null;
        $codigoLinea = $_POST['codigoLinea'] ?? null;
        $eliminado = 0;
        
        $fechaR = str_replace('T', ' ', $_POST['fechaR']) . ':00';
        // 2026-01-26 14:30:00


        // Validar que se recibieron todos los datos
        if (!$nomina || !$fechaR) {
            echo json_encode([
                'estatus' => 'error',
                'mensaje' => 'Faltan datos obligatorios.'
            ]);
            exit; 
        }

        //Validar que no exita un registro previo sin cerrar
            $sqlCheck = "SELECT id_registro, nomina from SPC_PERSONAL_NAD where eliminado = 0 and nomina = :nomina";

            $stmtCheck = $conn->prepare($sqlCheck);
            $stmtCheck->execute([':nomina' => $nomina]);
            $registro = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                if ($registro) {
                    // Nómina NO existe
                    echo json_encode([
                        'estatus' => 'error', 
                        'mensaje' => 'Este empleado ya ha sido agregado al personal no asignado'
                    ]);
                    exit;
                }
        //Fin validar registro duplicado


        try { // Iniciar transacción
            $conn->beginTransaction();

            // Preparar la sentencia con parámetros
            $sql = "INSERT INTO SPC_PERSONAL_NAD (nomina, codigo_linea, turno, comentarios, fechaR, eliminado) 
                            VALUES (:nomina, :codigo_linea, :turno, :comentarios, :fechaR, :eliminado)";

            $stmt = $conn->prepare($sql);

            // Ejecutar con los parámetros
            $stmt->execute([
                ':nomina' => $nomina, 
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
            $sql= "SELECT id_registro, nomina, turno from SPC_PERSONAL_NAD where eliminado = 0";

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

    //Remover trabajador de una estacion
    else 
        if($opcion=='10'){
            $idEstacion = $_POST['idEstacion'] ?? null;
            $nomina = $_POST['nomina'] ?? null;

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
                $sql = "UPDATE SPC_PERSONAL_ESTACION 
                            SET fecha_fin = GETDATE() 
                        WHERE id_estacion = :id_estacion 
                            AND nomina = :nomina
                            AND fecha_fin IS NULL";

                $stmt = $conn->prepare($sql);

                // Ejecutar con los parámetros
                $stmt->execute([
                    ':id_estacion' => $idEstacion,
                    ':nomina' => $nomina
                ]);

                // Confirmar la transacción
                $conn->commit();
                echo json_encode([
                    'estatus' => 'ok',
                    'mensaje' => 'Trabajador removido correctamente.',
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
?>