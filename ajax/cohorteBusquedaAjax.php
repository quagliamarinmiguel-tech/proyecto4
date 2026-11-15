<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../config/APP.php";
require_once "../controladores/cohorteControladorAdministracion.php";

$numero = $_POST['numero'] ?? '';
$anio = $_POST['anio'] ?? '';

try {
    $controlador = new cohorteControladorAdministracion();
    echo $controlador->paginador_cohorte_search_controlador(1, 100, "ListCohorteAdministracion", $numero, $anio);
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: ".$e->getMessage()."</div>";
}
