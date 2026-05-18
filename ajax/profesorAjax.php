
<?php
$peticionAjax = true;

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once "../config/APP.php";




    // 🔹 Validación en tiempo real (GET)
    if (
        isset($_GET['accion']) && $_GET['accion'] === 'validar_profesor_viejo_q_no_valida_numeros_y_correos' &&
        isset($_GET['ci']) && isset($_GET['telefono']) && isset($_GET['correo'])
    ) {
        header('Content-Type: application/json');
        require_once "../modelos/mainModel.php";

        $ci       = mainModel::limpiar_cadena($_GET['ci']);
        $telefono = mainModel::limpiar_cadena($_GET['telefono']);
        $correo   = mainModel::limpiar_cadena($_GET['correo']);

        // Validar formato de correo
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "valido" => false,
                "campo"  => "correo",
                "mensaje" => "El correo no tiene un formato válido."
            ]);
            exit();
        }


        // Validar duplicados en la base de datos
        $pdo = mainModel::conectar();

        // Cédula
        $sql_ci = $pdo->prepare("SELECT id_profesor FROM profesor WHERE ci_profesor = :ci AND estado_profesor = 1");
        $sql_ci->bindParam(":ci", $ci);
        $sql_ci->execute();
        if ($sql_ci->rowCount() > 0) {
            echo json_encode([
                "valido" => false,
                "campo"  => "ci",
                "mensaje" => "Ya existe un profesor con esa cédula."
            ]);
            exit();
        }

        // Teléfono
        $sql_tel = $pdo->prepare("SELECT id_profesor FROM profesor WHERE telefono_profesor = :tel AND estado_profesor = 1");
        $sql_tel->bindParam(":tel", $telefono);
        $sql_tel->execute();
        if ($sql_tel->rowCount() > 0) {
            echo json_encode([
                "valido" => false,
                "campo"  => "telefono",
                "mensaje" => "Ya existe un profesor con ese teléfono."
            ]);
            exit();
        }

        // Correo
        $sql_correo = $pdo->prepare("SELECT id_profesor FROM profesor WHERE correo = :correo AND estado_profesor = 1");
        $sql_correo->bindParam(":correo", $correo);
        $sql_correo->execute();
        if ($sql_correo->rowCount() > 0) {
            echo json_encode([
                "valido" => false,
                "campo"  => "correo",
                "mensaje" => "Ya existe un profesor con ese correo."
            ]);
            exit();
        }

        // Todo válido
        echo json_encode(["valido" => true]);
        exit();
    }


    
    
    
    
    
    // 🔹 Validación en tiempo real (GET sin ID)
if (
    isset($_GET['accion']) && $_GET['accion'] === 'validar_profesor' &&
    isset($_GET['ci']) && isset($_GET['telefono']) && isset($_GET['correo'])
) {
    header('Content-Type: application/json');
    require_once "../modelos/mainModel.php";

    $ci       = mainModel::limpiar_cadena($_GET['ci']);
    $telefono = mainModel::limpiar_cadena($_GET['telefono']);
    $correo   = mainModel::limpiar_cadena($_GET['correo']);

    $pdo = mainModel::conectar();

    // 🔹 Validar formato de correo y dominio permitido
    $dominios_validos = [
        '@gmail.com', '@yahoo.com', '@hotmail.com', '@outlook.com',
        '@live.com', '@icloud.com', '@aol.com', '@protonmail.com',
        '@gmx.com', '@zoho.com'
    ];
    $correo_valido = filter_var($correo, FILTER_VALIDATE_EMAIL);
    $dominio_valido = false;
    foreach ($dominios_validos as $dom) {
        if (str_ends_with(strtolower($correo), $dom)) {
            $dominio_valido = true;
            break;
        }
    }
    if (! $correo_valido || ! $dominio_valido) {
        echo json_encode([
            "valido" => false,
            "campo"  => "correo",
            "mensaje" => "Correo ya registrado o con datos inválidos."
        ]);
        exit();
    }

    // 🔹 Validar teléfono: prefijo válido y no termina en 0000000
    $prefijos_validos = [
        '0212','0412','0414','0415','0416','0417','0418',
        '0424','0426','0246','0247','0235','0238','0239'
    ];
    $prefijo = substr($telefono, 0, 4);
    $sufijo = substr($telefono, 4);
    $telefono_valido = in_array($prefijo, $prefijos_validos) && $sufijo !== '0000000';

    $sql_tel = $pdo->prepare("SELECT id_profesor FROM profesor WHERE telefono_profesor = :tel AND estado_profesor = 1");
    $sql_tel->bindParam(":tel", $telefono);
    $sql_tel->execute();
    if ($sql_tel->rowCount() > 0 || ! $telefono_valido) {
        echo json_encode([
            "valido" => false,
            "campo"  => "telefono",
            "mensaje" => "Teléfono ya registrado o con datos inválidos."
        ]);
        exit();
    }

    // 🔹 Validar cédula duplicada
    $sql_ci = $pdo->prepare("SELECT id_profesor FROM profesor WHERE ci_profesor = :ci AND estado_profesor = 1");
    $sql_ci->bindParam(":ci", $ci);
    $sql_ci->execute();
    if ($sql_ci->rowCount() > 0) {
        echo json_encode([
            "valido" => false,
            "campo"  => "ci",
            "mensaje" => "Ya existe un profesor con esa cédula."
        ]);
        exit();
    }

    // 🔹 Validar correo duplicado
    $sql_correo = $pdo->prepare("SELECT id_profesor FROM profesor WHERE correo = :correo AND estado_profesor = 1");
    $sql_correo->bindParam(":correo", $correo);
    $sql_correo->execute();
    if ($sql_correo->rowCount() > 0) {
        echo json_encode([
            "valido" => false,
            "campo"  => "correo",
            "mensaje" => "Correo ya registrado o con datos inválidos."
        ]);
        exit();
    }

    // ✅ Todo válido
    echo json_encode(["valido" => true]);
    exit();
}
    
    
    
    
    
    
    if (
    isset($_GET['accion']) && $_GET['accion'] === 'validar_profesor_edicion' &&
    isset($_GET['ci']) && isset($_GET['telefono']) && isset($_GET['correo']) && isset($_GET['id'])
) {
    header('Content-Type: application/json');
    require_once "../modelos/mainModel.php";

    $ci       = mainModel::limpiar_cadena($_GET['ci']);
    $telefono = mainModel::limpiar_cadena($_GET['telefono']);
    $correo   = mainModel::limpiar_cadena($_GET['correo']);
    $id       = mainModel::limpiar_cadena($_GET['id']);

    $pdo = mainModel::conectar();

    // 🔹 Validar formato de correo
    $dominios_validos = [
        '@gmail.com', '@yahoo.com', '@hotmail.com', '@outlook.com',
        '@live.com', '@icloud.com', '@aol.com', '@protonmail.com',
        '@gmx.com', '@zoho.com'
    ];
    $correo_valido = filter_var($correo, FILTER_VALIDATE_EMAIL);
    $dominio_valido = false;
    foreach ($dominios_validos as $dom) {
        if (str_ends_with(strtolower($correo), $dom)) {
            $dominio_valido = true;
            break;
        }
    }
    if (! $correo_valido || ! $dominio_valido) {
        echo json_encode([
            "valido" => false,
            "campo"  => "correo",
            "mensaje" => "Correo ya registrado o con datos inválidos."
        ]);
        exit();
    }

    // 🔹 Validar teléfono
    $prefijos_validos = [
        '0212','0412','0414','0415','0416','0417','0418',
        '0424','0426','0246','0247','0235','0238','0239'
    ];
    $prefijo = substr($telefono, 0, 4);
    $sufijo = substr($telefono, 4);

    $telefono_valido = in_array($prefijo, $prefijos_validos) && $sufijo !== '0000000';

    $sql_tel = $pdo->prepare("SELECT id_profesor FROM profesor WHERE telefono_profesor = :tel AND id_profesor != :id AND estado_profesor = 1");
    $sql_tel->bindParam(":tel", $telefono);
    $sql_tel->bindParam(":id", $id);
    $sql_tel->execute();

    if ($sql_tel->rowCount() > 0 || ! $telefono_valido) {
        echo json_encode([
            "valido" => false,
            "campo"  => "telefono",
            "mensaje" => "Teléfono ya registrado o con datos inválidos."
        ]);
        exit();
    }

    // 🔹 Validar cédula duplicada
    $sql_ci = $pdo->prepare("SELECT id_profesor FROM profesor WHERE ci_profesor = :ci AND id_profesor != :id AND estado_profesor = 1");
    $sql_ci->bindParam(":ci", $ci);
    $sql_ci->bindParam(":id", $id);
    $sql_ci->execute();
    if ($sql_ci->rowCount() > 0) {
        echo json_encode([
            "valido" => false,
            "campo"  => "ci",
            "mensaje" => "Ya existe otro profesor con esa cédula."
        ]);
        exit();
    }

    // 🔹 Validar correo duplicado
    $sql_correo = $pdo->prepare("SELECT id_profesor FROM profesor WHERE correo = :correo AND id_profesor != :id AND estado_profesor = 1");
    $sql_correo->bindParam(":correo", $correo);
    $sql_correo->bindParam(":id", $id);
    $sql_correo->execute();
    if ($sql_correo->rowCount() > 0) {
        echo json_encode([
            "valido" => false,
            "campo"  => "correo",
            "mensaje" => "Correo ya registrado o con datos inválidos."
        ]);
        exit();
    }

    echo json_encode(["valido" => true]);
    exit();
}
    
    
    // 🔹 Validación en tiempo real para edición
    if (
        isset($_GET['accion']) && $_GET['accion'] === 'validar_profesor_edicion_viejo_noborartodavia281025' &&
        isset($_GET['ci']) && isset($_GET['telefono']) && isset($_GET['correo']) && isset($_GET['id'])
    ) {
        header('Content-Type: application/json');
        require_once "../modelos/mainModel.php";

        $ci       = mainModel::limpiar_cadena($_GET['ci']);
        $telefono = mainModel::limpiar_cadena($_GET['telefono']);
        $correo   = mainModel::limpiar_cadena($_GET['correo']);
        $id       = mainModel::limpiar_cadena($_GET['id']);

        // Validar formato de correo
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "valido" => false,
                "campo"  => "correo",
                "mensaje" => "El correo no tiene un formato válido."
            ]);
            exit();
        }

        $pdo = mainModel::conectar();

        // Cédula
        $sql_ci = $pdo->prepare("SELECT id_profesor FROM profesor WHERE ci_profesor = :ci AND id_profesor != :id  AND estado_profesor = 1");
        $sql_ci->bindParam(":ci", $ci);
        $sql_ci->bindParam(":id", $id);
        $sql_ci->execute();
        if ($sql_ci->rowCount() > 0) {
            echo json_encode([
                "valido" => false,
                "campo"  => "ci",
                "mensaje" => "Ya existe otro profesor con esa cédula."
            ]);
            exit();
        }

        // Teléfono
        $sql_tel = $pdo->prepare("SELECT id_profesor FROM profesor WHERE telefono_profesor = :tel AND id_profesor != :id  AND estado_profesor = 1");
        $sql_tel->bindParam(":tel", $telefono);
        $sql_tel->bindParam(":id", $id);
        $sql_tel->execute();
        if ($sql_tel->rowCount() > 0) {
            echo json_encode([
                "valido" => false,
                "campo"  => "telefono",
                "mensaje" => "Ya existe otro profesor con ese teléfono."
            ]);
            exit();
        }

        // Correo
        $sql_correo = $pdo->prepare("SELECT id_profesor FROM profesor WHERE correo = :correo AND id_profesor != :id  AND estado_profesor = 1");
        $sql_correo->bindParam(":correo", $correo);
        $sql_correo->bindParam(":id", $id);
        $sql_correo->execute();
        if ($sql_correo->rowCount() > 0) {
            echo json_encode([
                "valido" => false,
                "campo"  => "correo",
                "mensaje" => "Ya existe otro profesor con ese correo."
            ]);
            exit();
        }

        echo json_encode(["valido" => true]);
        exit();
    }



    if (
        isset($_POST['ci_reg']) || 
        isset($_POST['profesor_id_del']) || 
        isset($_POST['profesor_id_up']) || 
        isset($_POST['profesor_id_archive']) || 
        isset($_POST['profesor_id_restore'])
    ) {
        require_once "../controladores/profesorControlador.php";
        $ins_profesor = new profesorControlador();

        /*--------- Actualizar profesor ---------*/
        if (isset($_POST['profesor_id_up'])) {
            if (isset($_FILES['archivo_imagen']) && $_FILES['archivo_imagen']['error'] === 0) {
                // Imagen nueva presente y válida → actualiza imagen + datos
                echo $ins_profesor->actualizar_profesor_con_imagen_controlador();
            } else {
                // Sin imagen → actualiza solo datos
                echo $ins_profesor->actualizar_profesor_controlador();
            }
            exit();
        }


    
    /*--------- Eliminar profesor ---------*/
        if (isset($_POST['profesor_id_del'])) {
            echo $ins_profesor->eliminar_profesor_controlador();
            exit();
        }
        
        

        /*--------- Archivar profesor ---------*/
        if (isset($_POST['profesor_id_archive'])) {
            echo $ins_profesor->archivar_profesor_controlador();
            exit();
        }

        /*--------- Restaurar profesor ---------*/
        if (isset($_POST['profesor_id_restore'])) {
            echo $ins_profesor->restaurar_profesor_controlador();
            exit();
        }



        /*--------- Agregar profesor (con o sin imagen) ---------*/
        if (isset($_POST['ci_reg'])) {
            if (isset($_FILES['archivo_imagen']) && $_FILES['archivo_imagen']['error'] === 0) {
                echo $ins_profesor->agregar_profesor_controlador2();
            }
            else {
               echo $ins_profesor->agregar_profesor_controlador2();
            }
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
        "Texto" => "Detalles: " . $e->getMessage(),
        "Tipo" => "error"
    ]);
}






















