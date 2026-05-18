<?php
$peticionAjax = true;

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once "../config/APP.php";
    require_once "../controladores/anuncioControlador.php";
    $ins_publicidad = new anuncioControlador();

    /*--------- Registrar nuevo anuncio ---------*/
    if(isset($_POST['titulo_reg'])){
        echo $ins_publicidad->agregar_anuncio_controlador();
        exit();
    }

    /*--------- Actualizar anuncio ---------*/
    if(isset($_POST['publicidad_id_up'])){
        echo $ins_publicidad->actualizar_anuncio_controlador();
        exit();
    }

    /*--------- Eliminar anuncio ---------*/
    if(isset($_POST['publicidad_id_del'])){
        echo $ins_publicidad->eliminar_anuncio_controlador();
        exit();
    }

    /*--------- Restaurar anuncio (si lo manejás como archivo lógico) ---------*/
    if(isset($_POST['publicidad_id_restore'])){
        echo $ins_publicidad->restaurar_anuncio_controlador();
        exit();
    }

    /*--------- Redirección en caso de acceso inválido ---------*/
    session_start(['name'=>'SPM']);
    session_unset();
    session_destroy();
    header("Location: ".SERVERURL."login/");
    exit();

} catch (Exception $e) {
    $alerta = [
        "Alerta" => "simple",
        "Titulo" => "Error del sistema",
        "Texto" => "Error: " . $e->getMessage(),
        "Tipo" => "error"
    ];
    echo json_encode($alerta);
}
?>