<?php
require_once __DIR__ . '/../controladores/ListEvaluacionesSeccionAdministracionControlador.php';
require_once __DIR__ . '/../exportadores/exportar_excel_evaluaciones.php';

$id_seccion = $_GET['id'] ?? null;
if (!$id_seccion) exit("ID no proporcionado");

$controlador = new ListEvaluacionesSeccionControlador();
$evaluaciones = $controlador->listar_evaluaciones_por_seccion($id_seccion);
$nombre = $controlador->obtener_nombre_seccion($id_seccion);

exportar_excel_evaluaciones($evaluaciones, $nombre);
