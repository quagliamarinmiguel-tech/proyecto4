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
            
            /*--------- Módulo de búsqueda de cursos ---------*/
            if($modulo == "curso"){
                
                /*--------- Eliminar búsqueda de curso ---------*/
                if(isset($_POST['eliminar_busqueda'])){
                    session_start(['name'=>'SPM']);
                    unset($_SESSION['busqueda_curso']);
                    
                    $alerta=[
                        "Alerta"=>"redireccionar",
                        "URL"=>SERVERURL."index.php?views=course-search"
                    ];
                    echo json_encode($alerta);
                    exit();
                }
                
                /*--------- Iniciar búsqueda de curso ---------*/
                if(isset($_POST['busqueda_inicial'])){
                    $busqueda = mainModel::limpiar_cadena($_POST['busqueda_inicial']);
                    
                    if($busqueda == ""){
                        $alerta=[
                            "Alerta"=>"simple",
                            "Titulo"=>"Ocurrió un error inesperado",
                            "Texto"=>"Debes introducir un término de búsqueda",
                            "Tipo"=>"error"
                        ];
                        echo json_encode($alerta);
                        exit();
                    }
                    
                    session_start(['name'=>'SPM']);
                    $_SESSION['busqueda_curso'] = $busqueda;
                    
                    $alerta=[
                        "Alerta"=>"redireccionar",
                        "URL"=>SERVERURL."index.php?views=course-search"
                    ];
                    echo json_encode($alerta);
                    exit();
                }
            }
            

            /*--------- Módulo de búsqueda de cohortes ---------*/
            if($modulo == "cohorte"){

                session_start(['name'=>'SPM']);

                // Eliminar búsqueda
                if(isset($_POST['eliminar_busqueda'])){
                    unset($_SESSION['busqueda_cohorte_num']);
                    unset($_SESSION['busqueda_cohorte_anio']);

                    $alerta = [
                        "Alerta" => "redireccionar",
                        "URL" => SERVERURL."index.php?views=cohorte-search"
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                // Iniciar nueva búsqueda
                if(isset($_POST['busqueda_anio_cohorte'])){
                    $numero = mainModel::limpiar_cadena($_POST['busqueda_numero_cohorte'] ?? '');
                    $anio   = mainModel::limpiar_cadena($_POST['busqueda_anio_cohorte']);

                    if($anio == ""){
                        $alerta = [
                            "Alerta" => "simple",
                            "Titulo" => "Campo obligatorio",
                            "Texto" => "Debes ingresar el año para realizar la búsqueda.",
                            "Tipo" => "error"
                        ];
                        echo json_encode($alerta);
                        exit();
                    }

                    $_SESSION['busqueda_cohorte_num'] = $numero;
                    $_SESSION['busqueda_cohorte_anio'] = $anio;

                    $alerta = [
                        "Alerta" => "redireccionar",
                        "URL" => SERVERURL."index.php?views=cohorte-search"
                    ];
                    echo json_encode($alerta);
                    exit();
                }
            }




            /*--------- Módulo de búsqueda de cursos vigentes ---------*/
            if($modulo == "curso_vigente"){

                session_start(['name'=>'SPM']);

                // Eliminar búsqueda
                if(isset($_POST['eliminar_busqueda'])){
                    unset($_SESSION['busqueda_nombre_curso']);
                    unset($_SESSION['busqueda_tipo_curso']);

                    $alerta = [
                        "Alerta" => "redireccionar",
                        "URL" => SERVERURL."index.php?views=curso-vigente-search"
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                // Iniciar nueva búsqueda
                if(isset($_POST['busqueda_nombre_curso'])){
                    $nombre = mainModel::limpiar_cadena($_POST['busqueda_nombre_curso']);
                    $tipo   = mainModel::limpiar_cadena($_POST['busqueda_tipo_curso'] ?? '');

                    if($nombre == ""){
                        $alerta = [
                            "Alerta" => "simple",
                            "Titulo" => "Campo obligatorio",
                            "Texto" => "Debes ingresar el nombre del curso para realizar la búsqueda.",
                            "Tipo" => "error"
                        ];
                        echo json_encode($alerta);
                        exit();
                    }

                    $_SESSION['busqueda_nombre_curso'] = $nombre;
                    $_SESSION['busqueda_tipo_curso']   = $tipo;

                    $alerta = [
                        "Alerta" => "redireccionar",
                        "URL" => SERVERURL."index.php?views=curso-vigente-search"
                    ];
                    echo json_encode($alerta);
                    exit();
                }
            }





            /*--------- Módulo de búsqueda de profesores ---------*/
            if($modulo == "profesor"){

                session_start(['name'=>'SPM']);

                // Eliminar búsqueda
                if(isset($_POST['eliminar_busqueda'])){
                    unset($_SESSION['busqueda_profesor']);

                    $alerta = [
                        "Alerta" => "redireccionar",
                        "URL" => SERVERURL."index.php?views=profesor-search"
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                // Iniciar búsqueda por nombre, apellido o cédula
                if(isset($_POST['busqueda_inicial'])){
                    $busqueda = mainModel::limpiar_cadena($_POST['busqueda_inicial']);

                    if($busqueda == ""){
                        $alerta = [
                            "Alerta" => "simple",
                            "Titulo" => "Ocurrió un error inesperado",
                            "Texto" => "Debes introducir un término de búsqueda",
                            "Tipo" => "error"
                        ];
                        echo json_encode($alerta);
                        exit();
                    }

                    $_SESSION['busqueda_profesor'] = $busqueda;

                    $alerta = [
                        "Alerta" => "redireccionar",
                        "URL" => SERVERURL."index.php?views=profesor-search"
                    ];
                    echo json_encode($alerta);
                    exit();
                }
            }




            /*--------- Módulo de búsqueda de usuarios (mantener compatibilidad) ---------*/
            if($modulo == "usuario"){
                
                /*--------- Eliminar búsqueda de usuario ---------*/
                if(isset($_POST['eliminar_busqueda'])){
                    session_start(['name'=>'SPM']);
                    unset($_SESSION['busqueda_usuario']);
                    
                    $alerta=[
                        "Alerta"=>"redireccionar",
                        "URL"=>SERVERURL."index.php?views=user-search"
                    ];
                    echo json_encode($alerta);
                    exit();
                }
                
                /*--------- Iniciar búsqueda de usuario ---------*/
                if(isset($_POST['busqueda_inicial'])){
                    $busqueda = mainModel::limpiar_cadena($_POST['busqueda_inicial']);
                    
                    if($busqueda == ""){
                        $alerta=[
                            "Alerta"=>"simple",
                            "Titulo"=>"Ocurrió un error inesperado",
                            "Texto"=>"Debes introducir un término de búsqueda",
                            "Tipo"=>"error"
                        ];
                        echo json_encode($alerta);
                        exit();
                    }
                    
                    session_start(['name'=>'SPM']);
                    $_SESSION['busqueda_usuario'] = $busqueda;
                    
                    $alerta=[
                        "Alerta"=>"redireccionar",
                        "URL"=>SERVERURL."index.php?views=user-search"
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
