<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

if (isset($_GET['id_seccion'])) {
    $id_seccion = mainModel::limpiar_cadena($_GET['id_seccion']);

    try {
        $sql = mainModel::conectar()->prepare("
            SELECT 
                i.id_inscripcion,
                e.nombre,
                e.apellido,
                e.ci_estudiante
            FROM inscripcion i
            INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
            WHERE i.id_seccion_fk = :id_seccion
              AND i.estado_inscripcion = 'Activa'
              AND i.archivada = 0
            ORDER BY e.apellido ASC
        ");
        $sql->bindParam(':id_seccion', $id_seccion);
        $sql->execute();

        $estudiantes = $sql->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($estudiantes);
    } catch (PDOException $e) {
        echo json_encode([
            "error" => "Error al obtener estudiantes: " . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        "error" => "ID de sección no proporcionado"
    ]);
}