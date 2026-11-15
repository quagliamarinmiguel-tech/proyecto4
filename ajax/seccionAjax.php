

<?php
$peticionAjax = true;

// Mostrar errores si estás en desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once "../config/APP.php";
    require_once "../controladores/seccionControlador.php";

    $ins_seccion = new seccionControlador();

    session_start(['name' => 'SPM']);
    
      // Crear sección (simple)
    if (isset($_POST['period_course_id_crear_s']) || isset($_POST['period_id_crear_pc']) || isset($_POST['course_id_crear_pc'])) {
        echo $ins_seccion->crear_seccion_controlador();
        exit();
    }

    // Actualizar sección (profesor y día)
    if (isset($_POST['id_seccion_up']) && isset($_POST['profesor_up'])) {
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

    
    if (isset($_GET['accion']) && isset($_GET['seccion_id'])) {
        $accion    = $_GET['accion'];
        $sectionId = (int) ($_GET['seccion_id'] ?? $_GET['section_id'] ?? 0);
        $numFechas = (int) ($_GET['num_fechas'] ?? 6);

        if ($numFechas < 4 || $numFechas > 15) {
            $numFechas = 6;
        }

        if ($sectionId <= 0) {
            die("ID inválido");
        }

        switch ($accion) {

            // NOTAS DE LA SECCIÓN
            case 'pdf_seccion':			$ins_seccion->generar_pdf_notas_seccion_controlador($sectionId);exit;
            case 'excel_seccion':		$ins_seccion->generar_excel_notas_seccion_controlador($sectionId);exit;
            case 'csv_seccion':			$ins_seccion->generar_csv_notas_seccion_controlador($sectionId);exit;

            // NÓMINA
            case 'pdf_nomina':			$ins_seccion->generar_pdf_nomina_seccion_controlador($sectionId);exit;
            case 'excel_nomina':		$ins_seccion->generar_excel_nomina_seccion_controlador($sectionId);exit;
            case 'csv_nomina':			$ins_seccion->generar_csv_nomina_seccion_controlador($sectionId);exit;

            // PAGOS
            case 'pdf_pagos_seccion': 	$ins_seccion->generar_pdf_pagos_seccion_controlador($sectionId);exit;
            case 'excel_pagos_seccion': $ins_seccion->generar_excel_pagos_seccion_controlador($sectionId);exit;
            case 'csv_pagos_seccion':	$ins_seccion->generar_csv_pagos_seccion_controlador($sectionId);exit;

    		// ACTA DE CALIFICACIONES
            case 'pdf_acta_cal':		$ins_seccion->generar_pdf_acta_calificaciones_controlador($sectionId);exit;
            case 'excel_acta_cal':		$ins_seccion->generar_excel_acta_calificaciones_controlador($sectionId);exit;

            // ASISTENCIA
            case 'pdf_asistencia':		$ins_seccion->generar_pdf_asistencia_seccion_controlador($sectionId, $numFechas);exit;
            case 'excel_asistencia':	$ins_seccion->generar_excel_asistencia_seccion_controlador($sectionId, $numFechas);exit;
                
            // ACCIÓN NO RECONOCIDA
            default:
                http_response_code(400);
                die("Acción no reconocida");
        }
    }
    
    
    
//reportes de notas y pagos individuales del estudiante
    if (isset($_GET['accion']) && isset($_GET['registration_id'])) {
        $accion    = $_GET['accion'];
        $registrationId = (int) ($_GET['registration_id'] ?? 0);
        if ($registrationId <= 0) die("ID inválido");
        switch ($accion) {
            case 'pdf_pagos_estudiante':	$ins_seccion->generar_pdf_pagos_estudiante_controlador($registrationId);exit;
			case 'excel_pagos_estudiante':	$ins_seccion->generar_excel_pagos_estudiante_controlador($registrationId);exit;
			case 'csv_pagos_estudiante':	$ins_seccion->generar_csv_pagos_estudiante_controlador($registrationId);exit;
                
            case 'pdf_notas_estudiante':	$ins_seccion->generar_pdf_notas_estudiante_controlador($registrationId);exit;
			case 'excel_notas_estudiante':  $ins_seccion->generar_excel_notas_estudiante_controlador($registrationId);exit;
			case 'csv_notas_estudiante':	$ins_seccion->generar_csv_notas_estudiante_controlador($registrationId);exit;
                
            // ACCIÓN NO RECONOCIDA
            default:
                http_response_code(400);
                die("Acción no reconocida");
        }
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


