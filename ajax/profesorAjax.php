<?php
$peticionAjax = true;

// Mostrar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . "/../config/APP.php";

    session_start(['name' => 'SPM']);

    // ==============================================
    // PETICIONES GET (Validaciones en tiempo real)
    // ==============================================
    if (isset($_GET['accion'])) {
        header('Content-Type: application/json');
        require_once "../controladores/profesorControlador.php";
        $controlador = new profesorControlador();
        $accessToken = $_SESSION['auth_access_token'] ?? null;

        if (!$accessToken) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'message' => 'Sesión de Supabase no disponible.']);
            exit();
        }

        // Validar correo duplicado
        if ($_GET['accion'] === 'validar_correo_duplicado') {
            $correo = mainModel::limpiar_cadena($_GET['correo'] ?? '');
            $excluir = isset($_GET['excluir']) ? mainModel::limpiar_cadena($_GET['excluir']) : null;
            $resultado = $controlador->validar_correo_no_duplicado($correo, $excluir);
            echo json_encode(['duplicado' => !empty($resultado['data'])]);
            exit();
        }

        // Validar cédula duplicada
        if ($_GET['accion'] === 'validar_cedula_duplicada') {
            $ci = mainModel::limpiar_cadena($_GET['ci'] ?? '');
            $excluir = isset($_GET['excluir']) ? mainModel::limpiar_cadena($_GET['excluir']) : null;
            $resultado = $controlador->validar_cedula_no_duplicado($ci, $excluir);
            echo json_encode(['duplicado' => !empty($resultado['data'])]);
            exit();
        }

        // Validar teléfono duplicado
        if ($_GET['accion'] === 'validar_telefono_duplicado') {
            $telefono = mainModel::limpiar_cadena($_GET['telefono'] ?? '');
            $excluir = isset($_GET['excluir']) ? mainModel::limpiar_cadena($_GET['excluir']) : null;
            $resultado = $controlador->validar_telefono_no_duplicado($telefono, $excluir);
            echo json_encode(['duplicado' => !empty($resultado['data'])]);
            exit();
        }

        if ($_GET['accion'] === 'validar_telefono_formato') {
            $telefono = mainModel::limpiar_cadena($_GET['telefono'] ?? '');
            $resultado = $controlador->validar_telefono_formato($telefono);
            echo json_encode(['formato_valido' => !$resultado]);
            exit();
        }
        // Si ninguna acción coincide
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Acción no válida']);
        exit();
    }

    // ==============================================
    // PETICIONES POST (CRUD)
    // ==============================================
    if (
        isset($_POST['ci_reg']) ||
        isset($_POST['profesor_id_del']) ||
        isset($_POST['profesor_id_up']) ||
        isset($_POST['profesor_id_archive']) ||
        isset($_POST['profesor_id_restore'])
    ) {
        require_once "../controladores/profesorControlador.php";
        $ins_profesor = new profesorControlador();


        // Eliminar profesor
        if (isset($_POST['profesor_id_del'])) {
            echo $ins_profesor->eliminar_profesor_controlador();
            exit();
        }

        // Actualizar profesor (con o sin imagen)
        if (isset($_POST['profesor_id_up'])) {
            //if (isset($_FILES['archivo_imagen']) && $_FILES['archivo_imagen']['error'] === 0) {
           //     echo $ins_profesor->actualizar_profesor_con_imagen_controlador();
         //   } else {
       //         echo $ins_profesor->actualizar_profesor_controlador();
     //       }
            echo $ins_profesor->actualizar_profesor_controlador();
            exit();
        }

        // Archivar profesor
        if (isset($_POST['profesor_id_archive'])) {
            echo $ins_profesor->archivar_profesor_controlador();
            exit();
        }

        // Restaurar profesor
        if (isset($_POST['profesor_id_restore'])) {
            echo $ins_profesor->restaurar_profesor_controlador();
            exit();
        }
        
        // Agregar profesor
        if (isset($_POST['ci_reg'])) {
            echo $ins_profesor->agregar_profesor_controlador();
            exit();
        }

        
    }

    // ==============================================
    // SI NO ES GET NI POST VÁLIDO -> REDIRIGIR A LOGIN
    // ==============================================
    session_start(['name' => 'SPM']);
    session_unset();
    session_destroy();
    header("Location: " . SERVERURL . "login/");
    exit();

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        "Alerta" => "simple",
        "Titulo" => "Error del sistema",
        "Texto" => "Error: " . $e->getMessage(),
        "Tipo" => "error"
    ]);
}
?>


