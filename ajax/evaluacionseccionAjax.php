<?php
header('Content-Type: application/json');
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

if (isset($_GET['id_seccion'])) {
    $id = mainModel::limpiar_cadena($_GET['id_seccion']);
    $sql = mainModel::conectar()->prepare("
        SELECT id_evaluacion, tipo_evaluacion, obj AS objetivo, porcentaje, fecha_evaluacion
        FROM evaluacion
        WHERE id_seccion = :id
        ORDER BY fecha_evaluacion DESC
    ");
    $sql->execute([':id' => $id]);
    echo json_encode($sql->fetchAll(PDO::FETCH_ASSOC));
}