<?php
require_once __DIR__ . '/../controladores/CrearEvaluacionControlador.php';
header('Content-Type: application/json');

$id = $_GET['id'] ?? null;
$controlador = new CrearEvaluacionControlador();
echo json_encode($id ? $controlador->obtener_secciones_por_curso($id) : []);
