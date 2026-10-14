<?php 
    $dsn= "sqlsrv:Server=LELEDANIEL;Database=ELECTRONICOSDB"; 
    // Zona horaria oficial de Ciudad de México para fechas y horas del proyecto.
    date_default_timezone_set('America/Mexico_City');
    $usuario= "";
    $contraseña ="";

    try {
        $conn = new PDO($dsn, $usuario, $contraseña);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
    } catch (PDOException $e) {
        // El detalle técnico solo va al log del servidor; quien llama recibe un
        // error genérico que los endpoints convierten en una respuesta JSON.
        error_log("Error al conectar a SQL Server: " . $e->getMessage());
        throw new RuntimeException('No fue posible conectar con la base de datos.');
    }
