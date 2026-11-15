<?php
	$peticionAjax=true;

    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);

	require_once "../config/APP.php";



/*--------- Instancia al controlador ---------*/
	if(isset($_POST['token']) && isset($_POST['usuario'])){
        require_once "../controladores/loginControlador.php";
		$ins_login = new loginControlador();

		echo $ins_login->cerrar_sesion_controlador();
		exit;
	}


//esta creo que no la estoy usando
//verifica si la ip que intenta acceder esta bloqueada
    if (isset($_GET['accion']) && $_GET['accion'] === 'validar_ip') {
        echo $ctrl->validar_ip_controlador();
        exit;
    }

//para cambiar clave por solicitud desde el correo
    if (isset($_GET['accion']) && $_GET['accion'] === 'cambiar_clave') {
        require_once "../controladores/loginControlador.php";
		$ins_login = new loginControlador();
        
        echo $ins_login->cambiar_clave_controlador();
        exit;
    }

// ---> crear solicitud de inscripción 
	//, $_POST['id_curso'], $_POST['captura_pago'])
    if (isset($_POST['cedula'])) {
        require_once "../controladores/solicitudControlador.php";
		$ctrl = new solicitudControlador();
                
        echo $ctrl->registrar_solicitud_inscripcion_controlador();
        exit;  
    }


    else{
            session_start(['name'=>'SPM']);
            session_unset();
            session_destroy();
            header("Location: ".SERVERURL."login/");
            exit();
        }