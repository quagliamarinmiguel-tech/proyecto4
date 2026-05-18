<?php
$peticionAjax = true;

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once "../config/APP.php";

    // 🔹 Validación en tiempo real (GET)
    if (
        isset($_GET['accion']) && $_GET['accion'] === 'validar_usuario_personal' &&
        isset($_GET['nombre']) && isset($_GET['ci']) && isset($_GET['correo'])
    ) {
        header('Content-Type: application/json');
        require_once "../modelos/mainModel.php";

        $nombre = mainModel::limpiar_cadena($_GET['nombre']);
        $ci     = mainModel::limpiar_cadena($_GET['ci']);
        $correo = mainModel::limpiar_cadena($_GET['correo']);

        // Validar formato de correo
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "valido" => false,
                "campo"  => "correo",
                "mensaje" => "El correo no tiene un formato válido."
            ]);
            exit();
        }
        
        
        $dominios_validos = [
            '@gmail.com', '@yahoo.com', '@hotmail.com', '@outlook.com',
            '@live.com', '@icloud.com', '@aol.com', '@protonmail.com',
            '@gmx.com', '@zoho.com'
        ];
        $dominio_valido = false;
        foreach ($dominios_validos as $dom) {
            if (str_ends_with(strtolower($correo), $dom)) {
                $dominio_valido = true;
                break;
            }
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || ! $dominio_valido) {
            echo json_encode([
                "valido" => false,
                "campo"  => "correo",
                "mensaje" => "Formato no válido o email incorrecto."
            ]);
            exit();
        }

        $pdo = mainModel::conectar();

        // Nombre de usuario
        $sql_usuario = $pdo->prepare("SELECT ID_usuario FROM usuario WHERE Nombre = :nombre");
        $sql_usuario->bindParam(":nombre", $nombre);
        $sql_usuario->execute();
        if ($sql_usuario->rowCount() > 0) {
            echo json_encode([
                "valido" => false,
                "campo"  => "nombre",
                "mensaje" => "El nombre de usuario ya está registrado."
            ]);
            exit();
        }

        // Cédula
        $sql_ci = $pdo->prepare("SELECT ci_personal FROM personal WHERE ci_personal = :ci");
        $sql_ci->bindParam(":ci", $ci);
        $sql_ci->execute();
        if ($sql_ci->rowCount() > 0) {
            echo json_encode([
                "valido" => false,
                "campo"  => "ci",
                "mensaje" => "La cédula ya está registrada."
            ]);
            exit();
        }

        // Correo
        $sql_correo = $pdo->prepare("SELECT correo_personal FROM personal WHERE correo_personal = :correo");
        $sql_correo->bindParam(":correo", $correo);
        $sql_correo->execute();
        if ($sql_correo->rowCount() > 0) {
            echo json_encode([
                "valido" => false,
                "campo"  => "correo",
                "mensaje" => "El correo ya está registrado."
            ]);
            exit();
        }

        // Todo válido
        echo json_encode(["valido" => true]);
        exit();
    }

    
    
   


      // 🔹 Búsqueda dinámica de estudiantes para Select2
    if (
    isset($_GET['accion']) && $_GET['accion'] === 'buscar_estudiante' &&
    isset($_GET['q'])
    ) {
        header('Content-Type: application/json');
        require_once "../modelos/mainModel.php";

        $q = mainModel::limpiar_cadena($_GET['q']);
        $pdo = mainModel::conectar();

        $sql = $pdo->prepare("
            SELECT id_estudiante, nombre, apellido, ci_estudiante
            FROM estudiante
            WHERE estado_estudiante = 1
              AND id_estudiante NOT IN (
                  SELECT id_estudiante_fk FROM usuario_estudiante
              )
              AND (
                  nombre LIKE :nombre OR
                  apellido LIKE :apellido OR
                  ci_estudiante LIKE :ci
              )
            ORDER BY nombre ASC
            LIMIT 20
        ");

        $like = "%$q%";
        $sql->bindParam(":nombre", $like);
        $sql->bindParam(":apellido", $like);
        $sql->bindParam(":ci", $like);
        $sql->execute();

        $estudiantes = $sql->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($estudiantes);
        exit();
    }
    
    
    
    
    
    
    
        // 🔹 Validación en tiempo real para actualización
    if (
        isset($_GET['accion']) && $_GET['accion'] === 'validar_usuario_personal_update' &&
        isset($_GET['id']) && isset($_GET['nombre']) && isset($_GET['ci']) && isset($_GET['correo'])
    ) {
        header('Content-Type: application/json');
        require_once "../modelos/mainModel.php";

        $id_usuario = mainModel::decryption($_GET['id']);
        $nombre     = mainModel::limpiar_cadena($_GET['nombre']);
        $ci         = mainModel::limpiar_cadena($_GET['ci']);
        $correo     = mainModel::limpiar_cadena($_GET['correo']);

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "valido" => false,
                "campo"  => "correo",
                "mensaje" => "El correo no tiene un formato válido."
            ]);
            exit();
        }
        
        $dominios_validos = [
            '@gmail.com', '@yahoo.com', '@hotmail.com', '@outlook.com',
            '@live.com', '@icloud.com', '@aol.com', '@protonmail.com',
            '@gmx.com', '@zoho.com'
        ];
        $dominio_valido = false;
        foreach ($dominios_validos as $dom) {
            if (str_ends_with(strtolower($correo), $dom)) {
                $dominio_valido = true;
                break;
            }
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || ! $dominio_valido) {
            echo json_encode([
                "valido" => false,
                "campo"  => "correo",
                "mensaje" => "Formato no válido o email incorrecto."
            ]);
            exit();
        }

        $pdo = mainModel::conectar();

        // Nombre de usuario
        $sql_usuario = $pdo->prepare("SELECT ID_usuario FROM usuario WHERE Nombre = :nombre AND ID_usuario != :id");
        $sql_usuario->bindParam(":nombre", $nombre);
        $sql_usuario->bindParam(":id", $id_usuario);
        $sql_usuario->execute();
        if ($sql_usuario->rowCount() > 0) {
            echo json_encode([
                "valido" => false,
                "campo"  => "nombre",
                "mensaje" => "El nombre de usuario ya está registrado."
            ]);
            exit();
        }

        // Cédula
        $sql_ci = $pdo->prepare("
            SELECT p.id_personal FROM personal p
            INNER JOIN usuario u ON u.ID_usuario = p.id_usuario_fk
            WHERE p.ci_personal = :ci AND u.ID_usuario != :id
        ");
        $sql_ci->bindParam(":ci", $ci);
        $sql_ci->bindParam(":id", $id_usuario);
        $sql_ci->execute();
        if ($sql_ci->rowCount() > 0) {
            echo json_encode([
                "valido" => false,
                "campo"  => "ci",
                "mensaje" => "La cédula ya está registrada."
            ]);
            exit();
        }

        // Correo
        $sql_correo = $pdo->prepare("
            SELECT p.id_personal FROM personal p
            INNER JOIN usuario u ON u.ID_usuario = p.id_usuario_fk
            WHERE p.correo_personal = :correo AND u.ID_usuario != :id
        ");
        $sql_correo->bindParam(":correo", $correo);
        $sql_correo->bindParam(":id", $id_usuario);
        $sql_correo->execute();
        if ($sql_correo->rowCount() > 0) {
            echo json_encode([
                "valido" => false,
                "campo"  => "correo",
                "mensaje" => "El correo ya está registrado."
            ]);
            exit();
        }

        echo json_encode(["valido" => true]);
        exit();
    }
    
    
    
    
    
    
    
    
    
    
    
    // 🔹 Acciones POST (crear, editar, eliminar usuario)
    if (
        isset($_POST['usuario_nombre_reg']) ||
        isset($_POST['usuario_id_del']) ||
        isset($_POST['usuario_est_id_up']) ||
        isset($_POST['usuario_per_id_up']) ||
        isset($_POST['id_estudiante']) ||
        isset($_POST['Nombre_u']) ||
        isset($_POST['usuario_est_id_del'])
    ) {
        require_once "../controladores/usuarioControlador3.php";
        $ins_usuario = new usuarioControlador3();

        
        if (isset($_POST['usuario_per_id_up'])) {
            echo $ins_usuario->actualizar_cuenta_per_controlador();
            exit();
        }
        
        if (isset($_POST['usuario_est_id_up'])) {
            echo $ins_usuario->actualizar_cuenta_est_controlador();
            exit();
        }
        
        
        
        if (
            isset($_POST['Nombre_u']) &&
            isset($_POST['clave1']) &&
            isset($_POST['clave2']) &&
            isset($_POST['cargo']) &&
            isset($_POST['ci_personal']) &&
            isset($_POST['nombre_personal']) &&
            isset($_POST['apellido_personal']) &&
            isset($_POST['correo_personal']) 
        ) {
            echo $ins_usuario->agregar_personal_controlador();
            exit();
        }
        
        if (
            isset($_POST['id_estudiante']) &&
            isset($_POST['clave1']) &&
            isset($_POST['clave2'])
        ) {
            require_once "../controladores/usuarioControlador3.php";
            echo $ins_usuario->agregar_cuenta_estudiante_controlador();
            exit();
        }

        if (isset($_POST['usuario_id_del'])) {
            echo $ins_usuario->eliminar_usuario_controlador();
            exit();
        }
                // En usuarioAjax2.php, en el switch principal
        if (isset($_POST['usuario_est_id_del'])) {
            // Eliminar cuenta de usuario de estudiante
            echo $ins_usuario->eliminar_cuenta_estudiante_controlador();
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