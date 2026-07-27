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
    $idArea = !empty($_POST['idArea']) ? $_POST['idArea'] : null;
    $encargado = !empty($_POST['encargado']) ? $_POST['encargado'] : null;
    $nombreLinea = !empty($_POST['nombreLinea']) ? $_POST['nombreLinea'] : null;
    $descripcion = !empty($_POST['descripcion']) ? $_POST['descripcion'] : null;
    $imagen = !empty($_FILES['imageLine']) ? $_FILES['imageLine'] : null;
    $nombreImagen = null;

    // Validar que se recibieron todos los datos
    if (!$codigoLinea || !$idArea) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Faltan datos obligatorios.'
        ]);
        exit; 
    }

    // Validar formato del código de línea
    if (!preg_match('/^[A-Za-z0-9-]+$/', $codigoLinea)) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'El código de línea solo puede contener letras, números y guion medio (-). No se permiten espacios ni caracteres especiales.'
        ]);
        exit;
    }

    try { // Iniciar transacción
        $conn->beginTransaction();

        if($imagen && $imagen['error'] !== UPLOAD_ERR_NO_FILE) {
            if($imagen['error'] !== UPLOAD_ERR_OK){
                $conn->rollBack();
                echo json_encode([
                    'status' => 'error',
                    'mensaje' => 'Ocurrió un error al cargar la imagen',
                ]);
                exit;
            }

            // Validar tamaño máximo de 5 MB
            $nombreTemporal = $imagen['tmp_name'];
            $tamanio = $imagen['size'];
            $maxSize = 5 * 1024 * 1024;

            if ($tamanio > $maxSize) {
                $conn->rollBack();
                echo json_encode([
                    'status' => 'error',
                    'mensaje' => 'La imagen es demasiado grande',
                ]);
                exit;
            } 

            // Validar tipo MIME real
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $nombreTemporal);
            finfo_close($finfo);
            $tiposPermitidos = ['image/jpeg' => 'jpg',
                                'image/png'  => 'png',
                                'image/webp' => 'webp',
                                'image/avif' => 'avif'];

            if (!array_key_exists($mimeType, $tiposPermitidos)) {
                $conn->rollBack();
                echo json_encode([
                    'status' => 'error',
                    'mensaje' => 'Formato de imagen no permitido.',
                     'mimeDetectado' => $mimeType
                ]);
                exit;
            }

            // Crear nombre de imagen
            $extension = $tiposPermitidos[$mimeType];

            //Validar que la imagen se halla subido 
            if (!move_uploaded_file($nombreTemporal, '../img/lineas/'.$codigoLinea.'.'.$extension)) {
                $conn->rollBack();
                echo json_encode([
                    'status' => 'error',
                    'mensaje' => 'No se pudo guardar la imagen en el servidor.',
                ]);
                exit;
            }

            $nombreImagen = $codigoLinea.'.'.$extension;
        }

        // Preparar la sentencia con parámetros
        $sql = "INSERT INTO SPC_LINEAS (codigo_linea, nombre_linea, descripcion, encargado_supervisor, imagen, idArea) 
                VALUES (:codigo_linea, :nombre_linea, :descripcion, :encargado_supervisor, :imagen, :idArea)";
        $stmt = $conn->prepare($sql);

        // Ejecutar con los parámetros
        $stmt->execute([
            ':codigo_linea' => $codigoLinea,
            ':nombre_linea' => $nombreLinea,
            ':descripcion' => $descripcion,
            ':encargado_supervisor' => $encargado,
            ':imagen' => $nombreImagen,
            ':idArea' => $idArea
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

//OBTENER LISTADO DE AREAS
else 
 if($opcion == '2'){
    try {
        // Preparar la sentencia con parámetros
        $sql = "SELECT idArea, nombreArea FROM SPC_AREAS";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);


        echo json_encode([
            'status' => 'ok',
            'response' => $areas,
        ]);

    } catch (PDOException $e) {
        // Respuesta JSON con el error
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Error al insertar el registro.',
            'detalle' => $e->getMessage()
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

//BUSCAR DATOS DEL EMPLEADO
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
?>