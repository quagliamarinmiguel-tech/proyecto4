<?php
require_once __DIR__ . "/../controladores/NotificacionesEstudianteControlador.php";
$controlador = new NotificacionesEstudianteControlador();

header('Content-Type: text/html; charset=utf-8');

$id_usuario = $_POST['id_usuario'] ?? null;

if (!$id_usuario || !is_numeric($id_usuario)) {
    echo "<p class='text-danger'>No se pudo identificar al usuario.</p>";
    exit;
}

$notificaciones = $controlador->obtener_notificaciones_pendientes($id_usuario);

if (empty($notificaciones)) {
    echo "<p class='text-muted'>No tienes notificaciones pendientes 🎉</p>";
} else {
    echo "<ul class='list-group'>";
    foreach ($notificaciones as $n) {
        $mensaje = generarMensaje($n['motivo'], $n['referencia']);
        echo "<li class='list-group-item list-group-item-warning'>{$mensaje}</li>";
    }
    echo "</ul>";
}

function generarMensaje($motivo, $referencia) {
    switch ($motivo) {
        case 'inscripcion':
            return "Falta pagar la inscripción del curso: {$referencia}";
        case 'modulo':
            return "Falta pagar el módulo: {$referencia}";
        case 'tarifa':
            return "Tarifa pendiente: {$referencia}";
        default:
            return "Notificación pendiente: {$referencia}";
    }
}
