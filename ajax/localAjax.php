<?php
$peticionAjax = true;

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once "../config/APP.php";

    if (
        isset($_POST['local_id_up']) ||
        isset($_POST['info_ceproca_reset']) // ← para futuras acciones si querés reiniciar datos
    ) {
        require_once "../controladores/localControlador.php";
        $ins_local = new localControlador();

        /*--------- Actualizar información institucional ---------*/
        if (isset($_POST['local_id_up'])) {
            // Si alguna imagen fue cambiada (se detecta por input oculto o archivo subido)
            if (
                (isset($_FILES['imagen_local']) && $_FILES['imagen_local']['error'] === 0) ||
                (isset($_FILES['imagen_local_mapa']) && $_FILES['imagen_local_mapa']['error'] === 0) ||
                (isset($_FILES['imagen_local_satelite']) && $_FILES['imagen_local_satelite']['error'] === 0)
            ) {
                echo $ins_local->actualizar_local_con_imagenes_controlador();
            } else {
                echo $ins_local->actualizar_local_sin_imagenes_controlador();
            }
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