<?php
$peticionAjax = true;

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once "../config/APP.php";

    if (
        isset($_POST['local_id_up']) ||
        isset($_POST['info_ceproca_reset']) ||
        isset($_POST['slider_update'])
    ) {
        require_once "../controladores/localControlador.php";
        $ins_local = new localControlador();

        /*--------- Actualizar información institucional ---------*/
        if (isset($_POST['local_id_up'])) {
            echo $ins_local->actualizar_local_controlador();
            exit();
        }

        /*--------- Actualizar sliders mediante modelo local ---------*/
        if (isset($_POST['slider_update'])) {
            echo $ins_local->actualizar_sliders_controlador();
            exit();
        }

        /*--------- Otra acción futura: reset completo ---------*/
        if (isset($_POST['info_ceproca_reset'])) {
            echo $ins_local->resetear_info_ceproca_controlador();
            exit();
        }

    } else {
        session_start(['name' => 'SPM']);
        session_unset();
        session_destroy();
        header("Location: " . SERVERURL . "login/");
        exit();
    }

} catch (Exception $e) {
    echo json_encode([
        "Alerta" => "simple",
        "Titulo" => "Error en el sistema",
        "Texto" => "Detalles: " . $e->getMessage(),
        "Tipo" => "error"
    ]);
}