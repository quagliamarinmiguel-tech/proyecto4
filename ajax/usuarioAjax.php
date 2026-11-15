<?php    
    
$peticionAjax = true;

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    
    require_once "../config/APP.php";
    require_once "../controladores/usuarioControlador.php";

    session_start(['name' => 'SPM']);

    $ctrl = new usuarioControlador();

//valida si el ci y el correo estan repetidos
    if (isset($_GET['accion']) && $_GET['accion'] === 'validar_usuario_personal') {
        echo $ctrl->validar_usuario_personal_controlador();
        exit;
    }

    
    //desactiva un usuario para que no pueda acceder 
    if (isset($_POST['user_unique_id'])) {
        echo $ctrl->actualizar_usuario_unico_controlador();
        exit;
    }
    
    //actualizar usuario
        if (isset($_POST['staff_id_up'])) {
            echo $ctrl->actualizar_usuario_staff_controlador();
            exit;
        }
    
    //crea un usuario
    if (isset($_POST['correo_personal'], $_POST['ci_personal'], $_POST['clave1'])) {
        echo $ctrl->crear_usuario_staff_controlador();
        exit;
    }

//desactiva un usuario para que no pueda acceder 
    if (isset($_POST['user_id_deactivate'])) {
        echo $ctrl->desactivar_usuario_controlador();
        exit;
    }

// reactiva usuario   
    if (isset($_POST['user_id_reactivate'])) {
        echo $ctrl->reactivar_usuario_controlador();
        exit;
    }

//eliminar usuario
    if (isset($_POST['user_id_delete'])) {
        echo $ctrl->eliminar_usuario_controlador();
        exit;
    } 
    
    
    
    // Acciones POST (crear, editar, eliminar usuario)xxxxx
    if (
        isset($_POST['usxuario_nombre_reg']) ||
        isset($_POST['usuxario_id_del']) ||
        isset($_POST['usuaxrio_est_id_up']) ||
        isset($_POST['usuarxio_per_id_up']) ||
        isset($_POST['id_estxudiante']) ||
        isset($_POST['Nombre_xu']) ||
        isset($_POST['usuario_xest_id_del'])
    ) {
        
        if (isset($_POST['usuario_per_id_up'])) {
            echo $ctrl->actualizar_cuenta_per_controlador();
            exit();
        }
        
        
        //isset($_POST['Nombre_u']) && xxxxxx
        if (
            isset($_POST['xclave1']) &&
            isset($_POST['cxlave2']) &&
            isset($_POST['caxrgo']) &&
            isset($_POST['ci_xpersonal']) &&
            isset($_POST['nombxre_personal']) &&
            isset($_POST['apellxido_personal']) &&
            isset($_POST['correox_personal']) 
        ) {
            echo $ctrl->agregar_personal_controlador();
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
        "Texto"  => "Detalles: " . $e->getMessage(),
        "Tipo"   => "error"
    ]);
}
    
    
    
    
    
    
    
    