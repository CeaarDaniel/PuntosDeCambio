<?php 
 $dsnE= "sqlsrv:Server=172.30.244.91;Database=SAPDB"; 
 $usuarioE= "sa";
 $contraseñaE ="AdminKs2014";

 try {
    $connE = new PDO($dsnE, $usuarioE, $contraseñaE);

    // Configuracion necesaria para recibir datos binarios para las imagenes 
    //$connE->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_BINARY);

    $connE->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar a SQL Server: " . $e->getMessage());
}
