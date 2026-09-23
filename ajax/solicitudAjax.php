<?php
$peticionAjax = true;
require_once "../config/APP.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);
    require_once "../controladores/solicitudControlador.php";
//   	  require_once "../controladores/recuperarControlador.php";
    $ins_recuperar = new solicitudControlador();
//        $ins_recuperar = new recuperarControlador();

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



//POSIBLES FUTURAS LLAMADAS
/*
    // registrar solicitud de inscripcion (no nuevo ingreso)
    if (isset($_GET['accion']) && $_GET['accion'] === 'registrar_solicitud_insc') {
        echo $ctrl->registrar_solicitud_insc_controlador();
        exit;
    }

    // aceptar solicitud de inscripcion (no nuevo ingreso)
    if (isset($_GET['accion']) && $_GET['accion'] === 'aceptar_solicitud_insc') {
        echo $ctrl->aceptar_solicitud_insc_controlador();
        exit;
    }

    // Rechazar  solicitud de inscripcion (no nuevo ingreso) (por FormularioAjax con data-form="rechazar")
    if (isset($_POST['solicitud_insc_id_rechazar'])) {
        echo $ctrl->rechazar_solicitud_insc_controlador();
        exit;
    }



    // registrar solicitud de pago
    if (isset($_GET['accion']) && $_GET['accion'] === 'registrar_solicitud_pago') {
        echo $ctrl->registrar_solicitud_pago_controlador();
        exit;
    }

    // aceptar  solicitud de pago
    if (isset($_GET['accion']) && $_GET['accion'] === 'aceptar_solicitud_pago') {
        echo $ctrl->aceptar_solicitud_pago_controlador();
        exit;
    }

    // Rechazar solicitud de pago (por FormularioAjax con data-form="rechazar")
    if (isset($_POST['solicitud_pago_id_rechazar'])) {
        echo $ctrl->rechazar_solicitud_pago_controlador();
        exit;
    }
*/





//header('Content-Type: text/plain');
//var_dump($_POST);
//exit();


/*
// 🔹 Validación en tiempo real de IP bloqueada
if (isset($_GET['accion']) && $_GET['accion'] === 'validar_ip' && isset($_GET['tipo'])) {
    header('Content-Type: application/json');
    require_once "../modelos/mainModel.php";

    $ip = $_SERVER['REMOTE_ADDR'];
    $tipo = mainModel::limpiar_cadena($_GET['tipo']);

    // Validar tipo permitido
    if (!in_array($tipo, ['login', 'recuperacion', 'cc'])) {
        echo json_encode([
            "bloqueado" => false,
            "mensaje" => "Tipo de validación inválido."
        ]);
        exit();
    }

    // Verificar si está bloqueada
    $bloqueo = mainModel::ip_bloqueada($ip, $tipo);

    if ($bloqueo['bloqueado']) {
        $minutos = ceil($bloqueo['tiempo_restante'] / 60);
        echo json_encode([
            "bloqueado" => true,
            "mensaje" => "Demasiados intentos fallidos. Intente más tarde en $minutos minutos."
        ]);
    } else {
        echo json_encode(["bloqueado" => false]);
    }
    exit();
}

elseif (isset($_POST['nombre']) && isset($_POST['correo']) && isset($_POST['id_curso']) && isset($_POST['fecha_nacimiento']) && isset($_POST['monto']) && isset($_FILES['captura_pago']) && isset($_POST['clave'])) {
    // Registro de solicitud de inscripción
    echo $ins_recuperar->registrar_solicitud_inscripcion_controlador();
}

//&&    (!empty($_POST['seccion_id']) || !empty($_POST['id_profesor_fk_reg']))
elseif (isset(
    $_POST['solicitud_id_check'], 
    $_POST['id_curso_vigente_fk'],
    $_POST['seccion_id'],  
    $_POST['nombre_estudiante'],
    $_POST['apellido_estudiante'],
    $_POST['cedula_estudiante'], 
    $_POST['telefono_estudiante'],
    $_POST['correo_Estudiante'],
    $_POST['fecha_nacimiento'],
    $_POST['monto'],
    $_POST['fecha_pago'],
    $_POST['metodo_pago'],
    $_POST['captura_pago'],
    $_POST['clave_estudiante']
		) 
) {
    // Aceptar solicitud y procesar inscripción
    echo $ins_recuperar->aceptar_solicitud_controlador();
}

elseif (isset($_POST['id_solicitud_reprobar'])) {
    // Reprobar solicitud de inscripción
    echo $ins_recuperar->reprobar_solicitud_controlador();
}

elseif (isset($_POST['solicitud_id_delete'])) {
    // Eliminar solicitud de inscripción
    echo $ins_recuperar->eliminar_solicitud_controlador();
}


elseif (isset($_POST['est-cedula']) && isset($_POST['est-telefono']) && isset($_POST['est-correo'])) {
    // Paso 1: Verificar identidad
    echo $ins_recuperar->crear_cuenta_est_controlador();

} elseif (isset($_POST['cedula']) && isset($_POST['telefono']) && isset($_POST['correo'])) {
    // Paso 1: Verificar identidad
    echo $ins_recuperar->verificar_identidad_controlador();
    
} elseif (isset($_POST['codigo_otp'])) {
    // Paso 2: Validar código OTP
    echo $ins_recuperar->validar_codigo_controlador();

}  elseif (isset($_POST['usuario_log']) && isset($_POST['clave_log'])) {
    // Login normal con control de intentos
    require_once "../controladores/loginControlador.php";
    $ins_login = new loginControlador();
    echo $ins_login->iniciar_sesion_controlador();

} elseif (isset($_POST['token']) && isset($_POST['usuario'])) {
    // Cierre de sesión
    require_once "../controladores/loginControlador.php";
    $ins_login = new loginControlador();
    echo $ins_login->cerrar_sesion_controlador();

}*/


else {
    session_start(['name'=>'SPM']);
    session_unset();
    session_destroy();
    header("Location: " . SERVERURL . "login/");
    exit();
}