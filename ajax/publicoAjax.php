<?php
$peticionAjax = true;

error_reporting(E_ALL);
ini_set('display_errors', 1);

//esto apunta a localcontrolador porque es mas practico
try {
    require_once "../config/APP.php";
    
    //echo "maincra"; die();
    
    if (isset($_GET['accion']) && $_GET['accion'] === 'registrar_interaccion') {
          //      require_once "../controladores/localControlador.php";
        //$ins_local = new localControlador();
        require_once "../controladores/publicoControlador.php";
        $ins_local = new publicoControlador();
        echo $ins_local->registrar_interaccion_controlador();
        exit;
    }
    else {
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










