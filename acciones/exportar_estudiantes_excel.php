<?php
require_once __DIR__ . "/../controladores/ListEstudianteSeccionAdministracionControlador.php";
require_once __DIR__ . "/../exportadores/exportar_excel_estudiantes.php";

$id_seccion = $_GET['id'] ?? null;
if (!$id_seccion) {
    exit("ID de sección no especificado.");
}

$controlador = new ListEstudiantesSeccionControlador();
$resultado = $controlador->paginador_estudiantes_por_seccion($id_seccion, 1, 10000); // Carga todos
$estudiantes = $resultado['datos'];

exportar_excel_estudiantes($estudiantes);
