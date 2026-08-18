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



    /*--------- Sección: Crear ---------*/
    if(isset($_POST['id_curso_vigente']) && isset($_POST['nombre_seccion']) && isset($_POST['id_profesor_fk'])){
        echo $ins_seccion->agregar_seccion_controlador();
        exit();
    }

    /*--------- Sección: Actualizar ---------*/
    if(isset($_POST['id_seccion_up']) && isset($_POST['profesor_up'])){
        echo $ins_seccion->modificar_profesor_seccion_controlador();
        exit();
    }

    /*--------- Sección: Archivar ---------*/
    if(isset($_POST['id_seccion_archivar'])){
        echo $ins_seccion->archivar_seccion_controlador();
        exit();
    }

    /*--------- Sección: Restaurar ---------*/
    if(isset($_POST['id_seccion_restore'])){
        echo $ins_seccion->restaurar_seccion_controlador();
        exit();
    }

    /*--------- Sección: Consulta AJAX de cursos vigentes ---------*/
    if(isset($_GET['accion']) && $_GET['accion'] === 'cursos_vigentes' && isset($_GET['id_cohorte'])){
        echo $ins_seccion->listar_cursos_vigentes_por_cohorte_ajax($_GET['id_cohorte']);
        exit();
    }








/*--------- Sección: Consulta AJAX de secciones archivadas por curso vigente ---------
if (isset($_POST['curso_vigente_id']) && isset($_POST['solo_archivadas'])) {
    require_once "../modelos/mainModel.php";
    $pdo = mainModel::conectar();

    $idCV = mainModel::limpiar_cadena($_POST['curso_vigente_id']);

    $stmt = $pdo->prepare("
        SELECT s.id_seccion, s.nombre_seccion, p.nombre_profesor, p.apellido_profesor,
            (SELECT COUNT(*) FROM evaluacion WHERE id_seccion = s.id_seccion AND archivar_seccion = 0) AS evaluaciones
        FROM seccion s
        INNER JOIN profesor p ON s.ci_profesor = p.id_profesor
        WHERE s.id_curso_vigente_fk = :idCV AND s.archivar_seccion = 0
        ORDER BY s.nombre_seccion ASC
    ");
    $stmt->execute([':idCV' => $idCV]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit();
}

*/

    /*--------- Sección: Consulta AJAX de secciones ARCHIVADAS por curso vigente ---------*/
    if (isset($_POST['curso_vigente_id']) && isset($_POST['solo_archivadas'])) {
        require_once "../modelos/mainModel.php";
        $pdo = mainModel::conectar();

        $idCV = mainModel::limpiar_cadena($_POST['curso_vigente_id']);

        $stmt = $pdo->prepare("
            SELECT s.id_seccion, s.dia, s.nombre_seccion, p.nombre_profesor, p.apellido_profesor,
                (SELECT COUNT(*) FROM inscripcion WHERE id_seccion_fk = s.id_seccion AND archivada = 1) AS inscritos,
                (SELECT COUNT(*) FROM evaluacion WHERE id_seccion = s.id_seccion AND archivar_evaluacion = 0) AS evaluaciones
            FROM seccion s
            INNER JOIN profesor p ON s.ci_profesor = p.id_profesor
            WHERE s.id_curso_vigente_fk = :idCV AND s.archivar_seccion = 0
            ORDER BY s.nombre_seccion ASC
        ");
        $stmt->execute([':idCV' => $idCV]);

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit();
    }



/*--------- Sección: Consulta AJAX de secciones por curso vigente ---------*/
if(isset($_POST['curso_vigente_id'])) {
    require_once "../modelos/mainModel.php";
    $pdo = mainModel::conectar();

    $idCV = mainModel::limpiar_cadena($_POST['curso_vigente_id']);

    $stmt = $pdo->prepare("
        SELECT s.id_seccion, s.nombre_seccion, s.dia, p.nombre_profesor, p.apellido_profesor,
            (SELECT COUNT(*) FROM inscripcion WHERE id_seccion_fk = s.id_seccion AND archivada = 1) AS inscritos,
            (SELECT COUNT(*) FROM evaluacion WHERE id_seccion = s.id_seccion AND archivar_evaluacion=1) AS evaluaciones
        FROM seccion s
        LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
        WHERE s.id_curso_vigente_fk = :idCV AND s.archivar_seccion = 1
        ORDER BY s.nombre_seccion ASC
    ");
    $stmt->execute([':idCV' => $idCV]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit();
}










    /*--------- Validar porcentaje acumulado de evaluación que funciona con crear y actualizar---------*
    if(isset($_GET['accion']) && $_GET['accion'] === 'validar_porcentaje' && isset($_GET['id_seccion']) && isset($_GET['nuevo'])){
        header('Content-Type: application/json');
        //require_once "../modelos/evaluacionModelo.php";
        require_once "../modelos/periodoModelo.php";
        $id = mainModel::limpiar_cadena($_GET['id_seccion']);
        $nuevo = (float) mainModel::limpiar_cadena($_GET['nuevo']);
        $excluir = isset($_GET['excluir']) ? mainModel::decryption($_GET['excluir']) : null;
        $seccion = new periodoModelo();
        $evaluaciones = $seccion->obtener_evaluaciones_sin_limite_modelo($id)->fetchAll();
        $total = 0;

        foreach ($evaluaciones as $eval) {
               error_log("Evaluación: {$eval['id_evaluacion']} - Porcentaje: {$eval['porcentaje']}");
            if ($excluir === null || (int)$eval['id_evaluacion'] !== (int)$excluir) {
                $total += (float) $eval['porcentaje'];
            }
        }
        $nuevo_total = $total + $nuevo;
        $excede = $nuevo_total > 100;

        echo json_encode([
            "total" => $nuevo_total,
            "excede" => $excede, // ✅ ahora es booleano
        ]);
        exit();
    }
*/

    /*--------- Evaluación: Actualizar ---------*/
    if(isset($_POST['evaluacion_id_up'])){
        echo $ins_evaluacion->actualizar_evaluacion_controlador($_POST);
        exit();
    }

    /*--------- Evaluación: Crear ---------*/
    if(isset($_POST['id_seccion']) && isset($_POST['tipo_evaluacion']) && isset($_POST['obj']) && isset($_POST['porcentaje']) && isset($_POST['fecha_evaluacion'])){
        echo $ins_evaluacion->agregar_evaluacion_controlador($_POST);
        exit();
    }


    /*--------- Evaluación: Archivar ---------*
    if(isset($_POST['evaluacion_id_archivar'])){
        echo $ins_evaluacion->archivar_evaluacion_controlador($_POST['evaluacion_id_archivar']);
        exit();
    }

    /*--------- Evaluación: Restaurar ---------/
    if(isset($_POST['evaluacion_id_restore'])){
        echo $ins_evaluacion->restaurar_evaluacion_controlador($_POST['evaluacion_id_restore']);
        exit();
    }*/

// En el switch principal de seccionAjax.php
elseif (isset($_POST['evaluacion_id_delete'])) {
    // Eliminar evaluación y sus puntajes
    echo $ins_evaluacion->eliminar_evaluacion_controlador();
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

