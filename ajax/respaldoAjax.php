<?php
$peticionAjax = true;

// Mostrar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once "../config/APP.php";

    // Acción para generar respaldo
    if (isset($_GET['accion']) && $_GET['accion'] === 'generar_respaldo') {
        require_once "../controladores/respaldoControlador.php";
        $ins_respaldo = new respaldoControlador();
        echo $ins_respaldo->generar_respaldo_controlador();
        exit();
    }

    // Redirección por acceso inválido
    else {
        session_start(['name' => 'SPM']);
        session_unset();
        session_destroy();
        header("Location: " . SERVERURL . "login/");
        exit();
    }

} catch (Exception $e) {
    header('Content-Type: application/json');
    $alerta = [
        "Alerta" => "simple",
        "Titulo" => "Error del sistema",
        "Texto" => "Error: " . $e->getMessage(),
        "Tipo" => "error"
    ];
    echo json_encode($alerta);
}
?>
