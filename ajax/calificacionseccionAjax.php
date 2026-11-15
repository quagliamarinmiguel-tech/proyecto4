<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

if (isset($_GET['id_curso'])) {
    $idCurso = mainModel::limpiar_cadena($_GET['id_curso']);

    try {
        $sql = mainModel::conectar()->prepare("
            SELECT id_seccion, nombre_seccion
            FROM seccion
            WHERE id_curso_vigente_fk = :idCurso
            ORDER BY nombre_seccion ASC
        ");
        $sql->bindParam(":idCurso", $idCurso);
        $sql->execute();

        $secciones = $sql->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($secciones);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error al obtener secciones: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "ID de curso no proporcionado"]);
}