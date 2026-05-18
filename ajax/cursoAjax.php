<?php
$peticionAjax = true;

// Mostrar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once "../config/APP.php";
    
    
    
    // Una sola entrada para validar curso (con o sin id)
    if (isset($_GET['accion']) && $_GET['accion'] === 'validar_curso' && isset($_GET['nombre']) && isset($_GET['tipo'])) {
        
        header('Content-Type: application/json');
        require_once "../controladores/cursoControlador.php";
        
        $nombre = mainModel::limpiar_cadena($_GET['nombre']);
        $tipo = mainModel::limpiar_cadena($_GET['tipo']);
        $id = isset($_GET['id']) ? mainModel::limpiar_cadena($_GET['id']) : null;
        
        // Llamar al controlador (el id puede ser null)
        $controlador = new cursoControlador();
        $duplicado = $controlador->validar_curso_no_duplicado($nombre, $tipo, $id);
        
        // Devolver true si NO está duplicado (válido), false si está duplicado
        echo json_encode(!$duplicado);
        exit();
    }
    


    
    
    
    

    // 🔹 Acciones por POST
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







    
    
    
    
    
    
    
    

    // 🔹 Validación en tiempo real (GET)
    /*
    if (isset($_GET['accion']) && $_GET['accion'] === 'validar_curso' && isset($_GET['nombre']) && isset($_GET['tipo'])) {
        header('Content-Type: application/json');
        require_once "../modelos/mainModel.php";

        $nombre = mainModel::limpiar_cadena($_GET['nombre']);
        $tipo = mainModel::limpiar_cadena($_GET['tipo']);

        // Validar formato del nombre
        if (mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{4,60}", $nombre)) {
            echo json_encode([
                "valido" => false,
                "mensaje" => "El nombre no cumple con el formato requerido."
            ]);
            exit();
        }

        // Validar duplicado con parámetros seguros
        $pdo = mainModel::conectar();
        $sql = $pdo->prepare("SELECT id_curso FROM curso WHERE nombre_curso = :nombre AND estado_curso != 0 AND tipo_curso = :tipo");
        $sql->bindParam(":nombre", $nombre);
        $sql->bindParam(":tipo", $tipo);
        $sql->execute();

        if ($sql->rowCount() > 0) {
            echo json_encode([
                "valido" => false,
                "mensaje" => "Ya existe un curso con ese nombre y tipo."
            ]);
            exit();
        }

        echo json_encode(["valido" => true]);
        exit();
    }

    if (
        isset($_GET['accion']) &&
        $_GET['accion'] === 'validar_curso_edicion' &&
        isset($_GET['nombre']) &&
        isset($_GET['tipo']) &&
        isset($_GET['id'])
    ) {
        header('Content-Type: application/json');
        require_once "../modelos/mainModel.php";

        $nombre = mainModel::limpiar_cadena($_GET['nombre']);
        $tipo = mainModel::limpiar_cadena($_GET['tipo']);
        $id    = mainModel::limpiar_cadena($_GET['id']);

        
        
        // Validar formato                            pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ](?:[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,59}) aparte de q el patron original esta mal este no es necesario debido a q esto es mejor validalo en controlador directo

        /*if (mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{4,60}", $nombre)) {
            echo json_encode([
                "valido" => false,
                "mensaje" => "El nombre no cumple con el formato requerido."
            ]);
            exit();
        }*

        // Validar duplicado excluyendo el curso actual
        $pdo = mainModel::conectar();
        $sql = $pdo->prepare("
            SELECT id_curso FROM curso
            WHERE nombre_curso = :nombre AND tipo_curso = :tipo AND estado_curso != 0 AND id_curso != :id
        ");
        $sql->bindParam(":nombre", $nombre);
        $sql->bindParam(":tipo", $tipo);
        $sql->bindParam(":id", $id);
        $sql->execute();

        if ($sql->rowCount() > 0) {
            echo json_encode([
                "valido" => false,
                "mensaje" => "Ya existe otro curso con ese nombre y tipo."
            ]);
            exit();
        }

        echo json_encode(["valido" => true]);
        exit();
    }

*/






?>
