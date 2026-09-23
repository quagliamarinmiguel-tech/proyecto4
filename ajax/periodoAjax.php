<?php
$peticionAjax = true;

// Mostrar errores si estás en desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once "../config/APP.php";
    require_once "../controladores/periodoControlador.php";
    require_once "../controladores/evaluacionControlador.php";

    $ins_seccion = new periodoControlador();
    $ins_evaluacion = new evaluacionControlador();

    session_start(['name' => 'SPM']);
    
    /*--------- Sección: Crear ---------*/
    if(isset($_POST['id_curso_vigente']) && isset($_POST['nombre_seccion']) && isset($_POST['id_profesor_fk'])){
        echo $ins_seccion->agregar_seccion_controlador();
        exit();
    }


    /*--------- Sección: Consulta AJAX de cursos vigentes ---------*/
    if(isset($_GET['accion']) && $_GET['accion'] === 'cursos_vigentes' && isset($_GET['id_cohorte'])){
        echo $ins_seccion->listar_cursos_vigentes_por_cohorte_ajax($_GET['id_cohorte']);
        exit();
    }

    /*--------- Sección: Obtener último periodo ---------*/
    if(isset($_GET['accion']) && $_GET['accion'] === 'obtener_ultimo_periodo' && isset($_GET['tipo'])){
        echo $ins_seccion->obtener_ultimo_periodo_controlador();
        exit();
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'validar_nombre_periodo') {
        header('Content-Type: application/json');
        $nombre = $_GET['nombre'] ?? '';
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null; 
        echo $ins_seccion->validar_nombre_periodo_controlador($nombre, $id);
        exit();
    }
    /*--------- Periodo: Crear ---------*/
    if (isset($_POST['periodo_tipo']) && isset($_POST['periodo_nombre'])) {
        echo $ins_seccion->crear_periodo_controlador();
        exit();
    }
    /*--------- Periodo: Actualizar ---------*/
    if (isset($_POST['periodo_id_up']) && isset($_POST['periodo_nombre_up'])) {
        echo $ins_seccion->actualizar_periodo_controlador();
        exit();
    }


    if (isset($_POST['periodo_id_archive'])) {
        echo $ins_seccion->archivar_periodo_controlador();
        exit();
    }
    
    if (isset($_POST['periodo_id_restore'])) {
        echo $ins_seccion->restaurar_periodo_controlador();
        exit();
    }
    
    
    /*--------- Obtener secciones para el modal para period list---------*/
    if (isset($_POST['curso_vigente_id'])) {
        echo $ins_seccion->obtener_secciones_ajax();
        exit();
    }
	
   //archivar periodo de curso sin logica de reembolso
    if (isset($_POST['period_course_id_archive'])) {
        echo $ins_seccion->archivar_period_course_controlador();
        exit();
    }
    
    
    
      // Crear sección (simple)
    if (isset($_POST['period_course_id']) && isset($_POST['section_name']) && isset($_POST['section_day']) && isset($_POST['teacher_id'])) {
        echo $ins_seccion->crear_seccion_controlador();
        exit();
    }
    
	//esta creo q puede descartarse
    if (isset($_POST['accion']) && $_POST['accion'] === 'cargar_puntajes' && isset($_POST['evaluacion_id'])) {
        echo $ins_seccion->obtener_puntajes_ajax();
        exit();
    }

    // Actualizar sección (profesor y día)
    if (isset($_POST['id_seccion_up']) && isset($_POST['profesor_up'])) {
        $ins_seccion = new periodoControlador(); // O el controlador que tenga el método
        echo $ins_seccion->actualizar_seccion_controlador();
        exit();
    }
    
    if (isset($_POST['id_seccion_eliminar'])) {
    	echo $ins_seccion->eliminar_seccion_controlador();
    	exit();
	}

    
    // Obtener datos para el modal de transferencia
    if (isset($_POST['accion']) && $_POST['accion'] === 'obtener_datos_transferencia' && isset($_POST['section_id'])) {
        echo $ins_seccion->obtener_datos_transferencia_ajax();
        exit();
    }

    // Transferir estudiantes seleccionados
    if (isset($_POST['id_seccion_origen']) && isset($_POST['id_seccion_destino']) && isset($_POST['registration_ids'])) {
        echo $ins_seccion->transferir_estudiantes_seleccionados_controlador();
        exit();
    }
    
    /*---------------------------------------------evaluaciones----------------------------------------------------------------*/
    /*--------- Evaluación: Actualizar ---------*/
    if (isset($_POST['evaluacion_id_up']) && isset($_POST['tipo_evaluacion'])) {
        echo $ins_seccion->actualizar_evaluacion_controlador();
        exit();
    }

    /*--------- Evaluación: Crear ---------*/
    if (isset($_POST['id_seccion']) && isset($_POST['tipo_evaluacion'])) {
        echo $ins_seccion->agregar_evaluacion_controlador();
        exit();
    }



    /*--------- Evaluación: Archivar ---------*/
    if(isset($_POST['id_evaluacion_archivar'])){
        echo $ins_seccion->archivar_evaluacion_controlador($_POST['id_evaluacion_archivar']);
        exit();
    }

    /*--------- Evaluación: Restaurar ---------*/
    if(isset($_POST['id_evaluacion_restaurar'])){
        echo $ins_seccion->restaurar_evaluacion_controlador($_POST['id_evaluacion_restaurar']);
        exit();
    }




    // En el switch principal de seccionAjax.php
    elseif (isset($_POST['evaluacion_id_delete'])) {
        // Eliminar evaluación y sus puntajes
        echo $ins_seccion->eliminar_evaluacion_controlador();
        exit();
    }

    

    /*--------- Redirección por acceso inválido ---------*/
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

