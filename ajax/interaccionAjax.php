<!--php
$peticionAjax = true;
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once "../config/APP.php";
    require_once "../controladores/interaccionControlador.php";
    $ins_interaccion = new interaccionControlador();

    /*--------- Panel General: Métricas + Top Cursos ---------*/
    if(isset($_GET['panel_general'])) {
        echo $ins_interaccion->panel_general_interacciones_controlador();
        exit();
    }

    if(isset($_GET['top_cursos_mes'])) {
        echo $ins_interaccion->top_cursos_del_mes_controlador();
        exit();
    }

    /*--------- Búsqueda por IP ---------*/
    if(isset($_POST['ip_busqueda'])) {
        echo $ins_interaccion->buscar_por_ip_controlador($_POST['ip_busqueda']);
        exit();
    }

    /*--------- Búsqueda por Curso ---------*/
    if(isset($_POST['curso_busqueda'])) {
        echo $ins_interaccion->buscar_por_curso_controlador($_POST['curso_busqueda']);
        exit();
    }

    /*--------- Búsqueda de Visitas por IP y fecha ---------*/
    if(isset($_POST['visitas_por_usuario'])) {
        echo $ins_interaccion->buscar_visitas_por_usuario_controlador($_POST['visitas_por_usuario']);
        exit();
    }

    /*--------- Eliminar interacción (click individual) ---------*/
    if(isset($_POST['id_click_del'])) {
        echo $ins_interaccion->eliminar_click_controlador($_POST['id_click_del']);
        exit();
    }

    /*--------- Eliminar visita individual ---------*/
    if(isset($_POST['id_visita_del'])) {
        echo $ins_interaccion->eliminar_visita_controlador($_POST['id_visita_del']);
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
?-->



<?php
$peticionAjax = true;
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once "../config/APP.php";
    require_once "../controladores/interaccionControlador.php";
    $ins_interaccion = new interaccionControlador();

    /*--------- Panel general de métricas ---------*/
    if (isset($_GET['panel_general'])) {
        echo $ins_interaccion->panel_general_interacciones_controlador();
        exit();
    }

    /*--------- Ranking top 10 cursos ---------*/
    if (isset($_GET['top_cursos_mes'])) {
        echo $ins_interaccion->top_cursos_del_mes_controlador();
        exit();
    }

    /*--------- Eliminar interacción puntual ---------*/
    if (isset($_POST['id_click_del'])) {
        echo $ins_interaccion->eliminar_click_controlador($_POST['id_click_del']);
        exit();
    }

    /*--------- Llamada de tabla paginada ---------*/
    if (isset($_POST['tabla_interacciones'])) {
        echo $ins_interaccion->tabla_interacciones_controlador($_POST['pagina'] ?? 1, $_POST['registros'] ?? 20);
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