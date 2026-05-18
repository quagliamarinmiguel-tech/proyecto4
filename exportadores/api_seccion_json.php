

<?php
    
    ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json; charset=UTF-8");
require_once "../controladores/seccionControlador2.php";

$curso_vigente_id = $_GET['curso_vigente'] ?? 0;
$seccion_letra = $_GET['seccion'] ?? '';

header("Content-Type: application/json; charset=UTF-8");

if ($curso_vigente_id == 0 || $seccion_letra == '') {
    echo json_encode(["error" => "Parámetros inválidos."]);
    exit();
}

$ins_seccion = new seccionControlador();
$datos = $ins_seccion->exsel_prueba_1($curso_vigente_id, $seccion_letra);

if (!$datos) {
    echo json_encode(["error" => "No se encontró la sección."]);
    exit();
}

echo json_encode($datos);
