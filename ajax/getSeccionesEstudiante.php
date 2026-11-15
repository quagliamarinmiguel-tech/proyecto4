<?php


require_once "../modelos/mainModel.php";
$conexion = mainModel::conectar();

// Validar que se recibió un ID de curso
if (!isset($_GET['id_curso']) || !is_numeric($_GET['id_curso'])) {
    http_response_code(400);
    echo json_encode(["error" => "ID de curso inválido"]);
    exit;
}

$idCurso = $_GET['id_curso'];

// Consultar secciones relacionadas con ese curso
$consulta = $conexion->prepare("SELECT id_seccion, nombre_seccion FROM seccion WHERE id_curso_vigente_fk = :idCurso");
$consulta->execute([':idCurso' => $idCurso]);

$secciones = $consulta->fetchAll(PDO::FETCH_ASSOC);

// Devolver como JSON
header('Content-Type: application/json');
echo json_encode($secciones);