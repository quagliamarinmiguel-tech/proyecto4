<?php
// Activar reporte de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Forzar que la respuesta sea JSON
header('Content-Type: application/json');

// Validar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "Alerta" => "simple",
        "Titulo" => "Método no permitido",
        "Texto"  => "Solo se permiten solicitudes POST",
        "Tipo"   => "error"
    ]);
    exit;
}

// Cargar dependencias necesarias
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";
require_once "../modelos/calificacionModelo.php";
require_once __DIR__ . "/../controladores/CalificacionControlador.php";

// Instanciar el controlador
$cal = new calificacionControlador();

// Ruteo de acciones según los datos recibidos
if (
    isset($_POST['id_inscripcion_reg']) &&
    isset($_POST['id_evaluacion_reg']) &&
    isset($_POST['puntaje_reg'])
) {
    // Agregar calificación
    echo json_encode($cal->agregar_calificacion_controlador());

} elseif (
    isset($_POST['id_inscripcion_up']) &&
    isset($_POST['id_evaluacion_up']) &&
    isset($_POST['puntaje_up'])
) {
    // Actualizar calificación
    echo json_encode($cal->actualizar_calificacion_controlador());

} elseif (
    isset($_POST['ins_enc']) &&
    isset($_POST['ev_enc'])
) {
    // Eliminar calificación
    echo json_encode($cal->eliminar_calificacion_controlador());

} else {
    // Datos insuficientes o inválidos
    echo json_encode([
        "Alerta" => "simple",
        "Titulo" => "Datos incompletos",
        "Texto"  => "No se recibieron los campos requeridos",
        "Tipo"   => "error"
    ]);
}