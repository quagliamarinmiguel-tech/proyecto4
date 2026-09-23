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

    // 🔹 Redirección por acceso inválido
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
