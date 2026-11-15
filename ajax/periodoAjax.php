<?php
$peticionAjax = true;

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once "../config/APP.php";
    require_once "../controladores/periodoControlador.php";

    $ins_seccion = new periodoControlador();

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

    
    else if (isset($_GET['accion'])) {
        $accion = $_GET['accion'];
        $meses  = (int) ($_GET['meses'] ?? 12);
        $limit  = (int) ($_GET['limit'] ?? 10);
        $periodId = (int) ($_GET['period_id'] ?? 0);
        $pcId = (int) ($_GET['period_course_id'] ?? 0);
        
        switch ($accion) {
            //individual
            case 'pdf_periodo_estudiantes':   $ins_seccion->generar_pdf_periodo_estudiantes_controlador($periodId); exit;
			case 'excel_periodo_estudiantes': $ins_seccion->generar_excel_periodo_estudiantes_controlador($periodId); exit;
			case 'csv_periodo_estudiantes':   $ins_seccion->generar_csv_periodo_estudiantes_controlador($periodId); exit;
    
            case 'pdf_periodo_cursos':        $ins_seccion->generar_pdf_periodo_cursos_controlador($periodId); exit;
            case 'excel_periodo_cursos':      $ins_seccion->generar_excel_periodo_cursos_controlador($periodId); exit;
            case 'csv_periodo_cursos':        $ins_seccion->generar_csv_periodo_cursos_controlador($periodId); exit;
                
            case 'pdf_periodo_recaudacion':   $ins_seccion->generar_pdf_periodo_recaudacion_controlador($periodId); exit;
			case 'excel_periodo_recaudacion': $ins_seccion->generar_excel_periodo_recaudacion_controlador($periodId); exit;
			case 'csv_periodo_recaudacion':   $ins_seccion->generar_csv_periodo_recaudacion_controlador($periodId); exit;

            //cursos
            case 'pdf_pc_aprobados':  		  $ins_seccion->generar_pdf_pc_aprobados_controlador($pcId); exit;
            case 'excel_pc_aprobados': 		  $ins_seccion->generar_excel_pc_aprobados_controlador($pcId); exit;
            case 'csv_pc_aprobados':   		  $ins_seccion->generar_csv_pc_aprobados_controlador($pcId); exit;

            case 'pdf_pc_pagos':   			  $ins_seccion->generar_pdf_pc_pagos_controlador($pcId); exit;
            case 'excel_pc_pagos': 			  $ins_seccion->generar_excel_pc_pagos_controlador($pcId); exit;
            case 'csv_pc_pagos':   			  $ins_seccion->generar_csv_pc_pagos_controlador($pcId); exit;
        }
    }
    
    

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

