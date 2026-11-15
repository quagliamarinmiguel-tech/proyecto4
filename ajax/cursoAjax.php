<?php
$peticionAjax = true;

// Mostrar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once "../config/APP.php";

    session_start(['name' => 'SPM']);
    
    
    
    // Una sola entrada para validar curso (con o sin id)
    if (isset($_GET['accion']) && $_GET['accion'] === 'validar_curso' && isset($_GET['nombre']) && isset($_GET['tipo'])) {
        
        header('Content-Type: application/json');
        require_once "../controladores/cursoControlador.php";
        
        $nombre = mainModel::limpiar_cadena($_GET['nombre']);
        $tipo = mainModel::limpiar_cadena($_GET['tipo']);
        $id = isset($_GET['id']) ? mainModel::limpiar_cadena($_GET['id']) : null;
        
        // Llamar al controlador (el id puede ser null)
        $controlador = new cursoControlador();
        $accessToken = $_SESSION['auth_access_token'] ?? null;

        if (!$accessToken) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'message' => 'Sesión de Supabase no disponible.']);
            exit();
        }

        $resultado = $controlador->validar_curso_no_duplicado_supabase($nombre, $tipo, $accessToken, $id);

        if (!$resultado['ok']) {
            http_response_code($resultado['status'] ?: 500);
            echo json_encode(['ok' => false, 'message' => $resultado['message']]);
            exit();
        }
        
        // Devolver true si NO está duplicado (válido), false si está duplicado
        echo json_encode(empty($resultado['data']));
        exit();
    }
    


    // Acciones por POST
    else if (isset($_POST['curso_nombre_reg']) || isset($_POST['curso_id_del']) || isset($_POST['curso_id_up']) || isset($_POST['curso_id_restore']) || isset($_POST['curso_id_archive'])) {
        require_once "../controladores/cursoControlador.php";
        $ins_curso = new cursoControlador();

        if (isset($_POST['curso_nombre_reg'])) {
            echo $ins_curso->agregar_curso_controlador();
        }

        if (isset($_POST['curso_id_del'])) {
            echo $ins_curso->eliminar_curso_controlador();
        }

        if (isset($_POST['curso_id_up'])) {
            echo $ins_curso->actualizar_curso_controlador();
            exit();
        }

        if (isset($_POST['curso_id_archive'])) {
            echo $ins_curso->archivar_curso_controlador();
        }

        if (isset($_POST['curso_id_restore'])) {
            echo $ins_curso->restaurar_curso_controlador();
        }
    }
    
    
    else if (isset($_GET['accion'])) {
        $accion = $_GET['accion'];
        $meses  = (int) ($_GET['meses'] ?? 12);
        $limit  = (int) ($_GET['limit'] ?? 10);
        $courseId = (int) ($_GET['course_id'] ?? 0);
        require_once "../controladores/cursoControlador.php";
        $ins_curso = new cursoControlador();
        
        
        switch ($accion) {
            //general
            case 'pdf_top_inscritos':     $ins_curso->generar_pdf_top_inscritos_controlador($meses, $limit); exit;
            case 'excel_top_inscritos':   $ins_curso->generar_excel_top_inscritos_controlador($meses, $limit); exit;
            case 'csv_top_inscritos':     $ins_curso->generar_csv_top_inscritos_controlador($meses, $limit); exit;

            case 'pdf_top_aprobados':     $ins_curso->generar_pdf_top_aprobados_controlador($meses, $limit); exit;
            case 'excel_top_aprobados':   $ins_curso->generar_excel_top_aprobados_controlador($meses, $limit); exit;
			case 'csv_top_aprobados':     $ins_curso->generar_csv_top_aprobados_controlador($meses, $limit); exit;

            case 'pdf_historial_cursos':  $ins_curso->generar_pdf_historial_cursos_controlador($meses); exit;
            case 'excel_historial_cursos':$ins_curso->generar_excel_historial_cursos_controlador($meses); exit;
            case 'csv_historial_cursos':  $ins_curso->generar_csv_historial_cursos_controlador($meses); exit;

            //individual
            case 'pdf_curso_inscritos':   $ins_curso->generar_pdf_curso_inscritos_controlador($courseId); exit;
            case 'excel_curso_inscritos': $ins_curso->generar_excel_curso_inscritos_controlador($courseId); exit;
            case 'csv_curso_inscritos':   $ins_curso->generar_csv_curso_inscritos_controlador($courseId); exit;
                
            case 'pdf_curso_recaudacion': $ins_curso->generar_pdf_curso_recaudacion_controlador($courseId, $meses); exit;
            case 'excel_curso_recaudacion': $ins_curso->generar_excel_curso_recaudacion_controlador($courseId, $meses); exit;
            case 'csv_curso_recaudacion': $ins_curso->generar_csv_curso_recaudacion_controlador($courseId, $meses); exit;

            case 'pdf_curso_aprobados':   $ins_curso->generar_pdf_curso_aprobados_controlador($courseId, $meses); exit;
            case 'excel_curso_aprobados': $ins_curso->generar_excel_curso_aprobados_controlador($courseId, $meses); exit;
            case 'csv_curso_aprobados':   $ins_curso->generar_csv_curso_aprobados_controlador($courseId, $meses); exit;
                
            case 'pdf_curso_profesores':  $ins_curso->generar_pdf_curso_profesores_controlador($courseId); exit;
            case 'excel_curso_profesores':$ins_curso->generar_excel_curso_profesores_controlador($courseId); exit;
            case 'csv_curso_profesores':  $ins_curso->generar_csv_curso_profesores_controlador($courseId); exit;
                
        }
    }
    
    
    
    
    
    
    
    

    // Redirección por acceso inválido
    else {
        session_start(['name' => 'SPM']);
        session_unset();
        session_destroy();
        header("Location: " . SERVERURL . "login/");
        exit();
    }

} catch (Exception $e) {
    header('Content-Type: application/json');
    $alerta = [
        "Alerta" => "simple",
        "Titulo" => "Error del sistema",
        "Texto" => "Error: " . $e->getMessage(),
        "Tipo" => "error"
    ];
    echo json_encode($alerta);
}









?>
