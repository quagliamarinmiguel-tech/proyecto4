<?php
require_once __DIR__ . "/../controladores/ListEstudianteSeccionAdministracionControlador.php";
require_once __DIR__ . "/../exportadores/exportar_pdf_estudiantes.php";

$id_seccion = $_GET['id'] ?? null;
if (!$id_seccion) {
    exit("ID de sección no especificado.");
}

$controlador = new ListEstudiantesSeccionControlador();
$nombre_seccion = $controlador->obtener_nombre_seccion($id_seccion);
$resultado = $controlador->paginador_estudiantes_por_seccion($id_seccion, 1, 10000); // carga todos
$estudiantes = $resultado['datos'];

exportar_pdf_estudiantes($estudiantes, $nombre_seccion);
