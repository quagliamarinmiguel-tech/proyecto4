<?php
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

if (isset($_GET['id_seccion'])) {
    $id = mainModel::limpiar_cadena($_GET['id_seccion']);

    try {
        $sql = mainModel::conectar()->prepare("
            SELECT id_evaluacion, tipo_evaluacion, porcentaje 
            FROM evaluacion 
            WHERE id_seccion = :id
            ORDER BY fecha_evaluacion DESC
        ");
        $sql->bindParam(":id", $id);
        $sql->execute();

        $evaluaciones = $sql->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($evaluaciones);
    } catch (PDOException $e) {
        echo json_encode([
            "error" => "Error en la consulta: " . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        "error" => "ID de sección no proporcionado"
    ]);
}