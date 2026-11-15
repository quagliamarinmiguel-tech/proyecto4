<?php
$peticionAjax = true;
require_once "../config/APP.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);
    require_once "../controladores/solicitudControlador.php";
    $ins_recuperar = new solicitudControlador();

//no creo que se incluya aqui las vistas de s. de incrpicion y pago, bueno podria decile a oliver para hacerlas yo que no es problema alguno, la logica ya la tengo, salve china

session_start(['name' => 'SPM']);
$ctrl = new solicitudControlador();

// ACEPTAR SOLICITUD
if (isset($_GET['accion']) && $_GET['accion'] === 'aceptar_solicitud') {
    echo $ctrl->aceptar_solicitud_controlador();
    exit;
}

// RECHAZAR SOLICITUD (por FormularioAjax con data-form="rechazar")
if (isset($_POST['solicitud_id_rechazar'])) {
    echo $ctrl->rechazar_solicitud_controlador();
    exit;
}

// ELIMINAR SOLICITUD (desde la lista, con data-form="delete2")
if (isset($_POST['solicitud_id_delete'])) {
    echo $ctrl->eliminar_solicitud_controlador();
    exit;
}




else {
    session_start(['name'=>'SPM']);
    session_unset();
    session_destroy();
    header("Location: " . SERVERURL . "login/");
    exit();
}