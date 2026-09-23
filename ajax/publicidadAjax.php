<?php
$peticionAjax = true;

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once "../config/APP.php";

    if (
        isset($_POST['id_curso']) || 
        isset($_POST['publicidad_id_del']) || 
        isset($_POST['publicidad_id_up']) ||
        isset($_POST['publicidad_id_restore']) ||
        isset($_POST['publicidad_id_archive'])

    ) {
        require_once "../controladores/cursoPublicidadControlador.php";
        $ins_publicidad = new cursoPublicidadControlador();
	    session_start(['name' => 'SPM']);

        
            // Actualizar publicidad
            if (isset($_POST['publicidad_id_up']) && isset($_POST['prefacio'])) {
                echo $ins_publicidad->actualizar_marketing_controlador();
                exit();
            }
        
        
        
        
        /*--------- Eliminar publicidad ---------*/
        if (isset($_POST['publicidad_id_del'])) {
            echo $ins_publicidad->eliminar_publicidad_controlador();
            exit();
        }

        /*--------- Agregar publicidad ---------*/
        if (isset($_POST['id_curso'])) {
            if (isset($_FILES['archivo_imagen']) && $_FILES['archivo_imagen']['error'] === 0) {
                echo $ins_publicidad->agregar_publicidad_con_imagen_controlador();
            } else {
                echo $ins_publicidad->agregar_publicidad_controlador(); // En caso de ser permitido sin imagen
            }
            exit();
        }


            // Restaurar publicidad archivada
            if(isset($_POST['publicidad_id_restore'])){
                echo $ins_publicidad->restaurar_publicidad_controlador();
                exit();
            }
   

            // Archivar publicidad activa
            if(isset($_POST['publicidad_id_archive'])){
                echo $ins_publicidad->archivar_publicidad_controlador();
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