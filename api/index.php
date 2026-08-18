


<?php
	
//	require_once "./config/APP.php";
//	require_once "./controladores/vistasControlador.php";

//	$plantilla = new vistasControlador();
//	$plantilla->obtener_plantilla_controlador();

require './config/SERVER.php';

$dsn = "pgsql:host=" . HOST . ";port=" . PORT . ";dbname=" . DB . ";sslmode=require";
//SERVER antesde HOST
try {
    $pdo = new PDO($dsn, USER, PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $resultado = $pdo->query("SELECT 1 AS ok");
    $fila = $resultado->fetch();

    echo "✅ Conexión OK";
    var_dump($fila);

} catch (Throwable $e) {
    echo "❌ Error de conexión: " . $e->getMessage();
}






