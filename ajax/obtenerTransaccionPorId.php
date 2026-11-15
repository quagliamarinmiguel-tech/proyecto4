<?php

require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_POST['id_transaccion'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID no recibido']);
    exit();
}

$id = mainModel::limpiar_cadena($_POST['id_transaccion']);
$conexion = mainModel::conectar();

$consulta = $conexion->prepare("
    SELECT 
        id_transaccion,
        monto,
        fecha_pago,
        metodo_pago
    FROM transaccion
    WHERE id_transaccion = :id
    LIMIT 1
");

$consulta->execute([':id' => $id]);
$transaccion = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$transaccion) {
    http_response_code(404);
    echo json_encode(['error' => 'Transacción no encontrada']);
    exit();
}

echo json_encode($transaccion);