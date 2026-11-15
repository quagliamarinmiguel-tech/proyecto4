<?php
$peticionAjax = true;

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once "../config/APP.php";

    
    
    
    
            // Cursos intensivos
        if (
   			 isset($_POST['curso_id_fk']) ||
   			 isset($_POST['cv_int_id_up']) ||
   			 isset($_POST['cv_int_id_archive']) ||
		     isset($_POST['cv_int_id_restore'])
		){
            require_once "../controladores/cvintControlador.php";
            $ins_cvint = new cvintControlador();
           
                    if (isset($_POST['curso_id_fk'])) {
                        echo $ins_cvint->agregar_cv_int_controlador();
                        exit();
                    }

                    if (isset($_POST['cv_int_id_up'])) {
                        echo $ins_cvint->actualizar_cv_int_controlador();
                        exit();
                    }

                    if (isset($_POST['cv_int_id_archive'])) {
                        echo $ins_cvint->archivar_cv_int_controlador();
                    }

                    if (isset($_POST['cv_int_id_restore'])) {
                        echo $ins_cvint->restaurar_cv_int_controlador();
                    }
            
            
        }


      //      require_once "../controladores/cvintControlador.php";
    //    $ins_cvint = new cvintControlador();


    
    
    
        if (isset($_GET['accion']) && $_GET['accion'] === 'validar_periodo_intensivo') {
                    header('Content-Type: application/json');
	        require_once "../modelos/mainModel.php";
            $cursoId = mainModel::limpiar_cadena($_GET['curso_id'] ?? '');
            $inicio = $_GET['inicio'] ?? '';
            $fin = $_GET['fin'] ?? '';

            if (!$cursoId || !$inicio || !$fin) {
                echo json_encode(["valido" => false, "mensaje" => "Faltan datos para validar."]);
                exit();
            }

            $pdo = mainModel::conectar();

            $stmt = $pdo->prepare("
                SELECT pi.fecha_inicio_int, pi.fecha_fin_int
                FROM periodo_intensivo pi
                INNER JOIN curso_vigente cv ON pi.id_curso_vigente_fk = cv.id_curso_vigente
                WHERE cv.id_curso_fk = :cursoId
            ");
            $stmt->execute([':cursoId' => $cursoId]);

            $inicioNueva = strtotime($inicio);
            $finNueva = strtotime($fin);
            $conflicto = false;

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $inicioExistente = strtotime($row['fecha_inicio_int']);
                $finExistente = strtotime($row['fecha_fin_int']);

                // Verifica si hay solapamiento de fechas
                if ($inicioNueva <= $finExistente && $finNueva >= $inicioExistente) {
                    $conflicto = true;
                    break;
                }
            }

            if ($conflicto) {
                echo json_encode([
                    "valido" => false,
                    "mensaje" => "Las fechas seleccionadas se solapan con otro periodo intensivo del mismo curso."
                ]);
            } else {
                echo json_encode(["valido" => true]);
            }
            exit();
        }
    
    

        if (isset($_GET['accion']) && $_GET['accion'] === 'validar_periodo_intensivo_para_actualizar') {
        header('Content-Type: application/json');
        require_once "../modelos/mainModel.php";

        $cursoId = mainModel::limpiar_cadena($_GET['curso_id'] ?? '');
        $inicio = $_GET['inicio'] ?? '';
        $fin = $_GET['fin'] ?? '';
        $excluirId = mainModel::limpiar_cadena($_GET['excluir'] ?? '');

        if (!$cursoId || !$inicio || !$fin) {
            echo json_encode(["valido" => false, "mensaje" => "Faltan datos para validar."]);
            exit();
        }

        $pdo = mainModel::conectar();

        $query = "
            SELECT pi.fecha_inicio_int, pi.fecha_fin_int
            FROM periodo_intensivo pi
            INNER JOIN curso_vigente cv ON pi.id_curso_vigente_fk = cv.id_curso_vigente
            WHERE cv.id_curso_fk = :cursoId
        ";

        if ($excluirId) {
            $query .= " AND pi.id_periodo_intensivo != :excluirId";
        }

        $stmt = $pdo->prepare($query);

        $params = [':cursoId' => $cursoId];
        if ($excluirId) {
            $params[':excluirId'] = $excluirId;
        }

        $stmt->execute($params);

        $inicioNueva = strtotime($inicio);
        $finNueva = strtotime($fin);
        $conflicto = false;

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $inicioExistente = strtotime($row['fecha_inicio_int']);
            $finExistente = strtotime($row['fecha_fin_int']);

            if ($inicioNueva <= $finExistente && $finNueva >= $inicioExistente) {
                $conflicto = true;
                break;
            }
        }

        if ($conflicto) {
            echo json_encode([
                "valido" => false,
                "mensaje" => "Las fechas seleccionadas se solapan con otro periodo intensivo del mismo curso."
            ]);
        } else {
            echo json_encode(["valido" => true]);
        }
        exit();
    }
    
    
    
    
    
    
    
    
    
    
    
    
    if (
        isset($_POST['curso_vigente_id_del']) || 
        isset($_POST['curso_vigente_id_up']) || 
        isset($_POST['curso_vigente_id_restore']) || 
        isset($_POST['curso_vigente_id_archive']) || 
        isset($_POST['curso_id_fk_reg']) 

    ) {
        require_once "../controladores/cohorteControlador.php";
        $ins_curso_vigente = new cohorteControlador();

        // Agregar una relación curso-cohorte vigente
        if (isset($_POST['curso_id_fk_reg'])) {
            //echo json_encode($ins_curso_vigente->agregar_curso_vigente_controlador_cv());
            echo $ins_curso_vigente->agregar_curso_vigente_controlador_cv();
        }

        // Actualizar relación
        if (isset($_POST['curso_vigente_id_up'])) {
            echo $ins_curso_vigente->actualizar_curso_vigente_controlador_cv();
        }

        // Archivar (si lo implementás)
        if (isset($_POST['curso_vigente_id_archive'])) {
            echo $ins_curso_vigente->archivar_curso_vigente_controlador();
        }

        // Restaurar
        if (isset($_POST['curso_vigente_id_restore'])) {
            echo $ins_curso_vigente->restaurar_curso_vigente_controlador();
        }

    } else {
        session_start(['name' => 'SPM']);
        session_unset();
        session_destroy();
        header("Location: " . SERVERURL . "login/");
        exit();
    }

} catch (Exception $e) {
    $alerta = [
        "Alerta" => "simple",
        "Titulo" => "Error inesperado",
        "Texto" => "Ocurrió un error: " . $e->getMessage(),
        "Tipo" => "error"
    ];
    echo json_encode($alerta);
}
?>