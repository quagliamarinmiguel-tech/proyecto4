<?php
    $peticionAjax=true;
    
    // Habilitar reporte de errores para debug
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    try {
        require_once "../config/APP.php";
        
        require_once "../modelos/mainModel.php";


        
        if(isset($_POST['modulo'])){
            
            $modulo = mainModel::limpiar_cadena($_POST['modulo']);
            
            /*--------- Módulo de búsqueda de cohortes ---------*/
            if($modulo == "cohorte"){

                session_start(['name'=>'SPM']);

                // Eliminar búsqueda
                if(isset($_POST['eliminar_busqueda'])){
                    unset($_SESSION['busqueda_cohorte_num']);
                    unset($_SESSION['busqueda_cohorte_anio']);

                    $alerta = [
                        "Alerta" => "redireccionar",
                        "URL" => SERVERURL."index.php?views=cohorteSearchAdministracion"
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                // Iniciar nueva búsqueda
                if(isset($_POST['busqueda_numero_cohorte']) && isset($_POST['busqueda_anio_cohorte'])){
                    $numero = mainModel::limpiar_cadena($_POST['busqueda_numero_cohorte']);
                    $anio   = mainModel::limpiar_cadena($_POST['busqueda_anio_cohorte']);

                    if($numero=="" || $anio==""){
                        $alerta = [
                            "Alerta" => "simple",
                            "Titulo" => "Campos vacíos",
                            "Texto" => "Debes ingresar número y año para buscar una cohorte.",
                            "Tipo" => "error"
                        ];
                        echo json_encode($alerta);
                        exit();
                    }

                    $_SESSION['busqueda_cohorte_num'] = $numero;
                    $_SESSION['busqueda_cohorte_anio'] = $anio;

                    $alerta = [
                        "Alerta" => "redireccionar",
                        "URL" => SERVERURL."index.php?views=cohorteSearchAdministracion"
                    ];
                    echo json_encode($alerta);
                    exit();
                }
            }

        }else{
            session_start(['name'=>'SPM']);
            session_unset();
            session_destroy();
            header("Location: ".SERVERURL."login/");
            exit();
        }
        
    } catch (Exception $e) {
        // En caso de error, devolver JSON válido
        $alerta=[
            "Alerta"=>"simple",
            "Titulo"=>"Error del sistema",
            "Texto"=>"Error: " . $e->getMessage(),
            "Tipo"=>"error"
        ];
        echo json_encode($alerta);
    }
?>