<?php
// Forzar salida como JSON
header('Content-Type: application/json; charset=utf-8');

// Activar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir dependencias
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

if (isset($_GET['id_seccion'])) {
    $idSeccion = mainModel::limpiar_cadena($_GET['id_seccion']);

    try {
        $sql = mainModel::conectar()->prepare("
            SELECT 
                id_evaluacion,
                tipo_evaluacion,
                obj,
                porcentaje,
                fecha_evaluacion
            FROM evaluacion
            WHERE id_seccion = :idSeccion
            ORDER BY fecha_evaluacion DESC
        ");
        $sql->bindParam(":idSeccion", $idSeccion);
        $sql->execute();

        $evaluaciones = $sql->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($evaluaciones);
    } catch (PDOException $e) {
        echo json_encode([
            "error" => "Error al obtener evaluaciones: " . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        "error" => "ID de sección no proporcionado"
    ]);
}