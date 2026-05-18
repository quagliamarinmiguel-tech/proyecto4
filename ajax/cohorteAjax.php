<?php
    $peticionAjax = true;

    // Mostrar errores si estás en desarrollo
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    try {
        require_once "../config/APP.php";
        require_once "../controladores/cohorteControlador.php";


        $ins_cohorte = new cohorteControlador();


        /*--------- Cargar cursos disponibles y asignados por cohorte ---------*/
        if(isset($_GET['cargar_cursos_cohorte'])){
            echo $ins_cohorte->cargar_cursos_para_cohorte_controlador($_GET['cargar_cursos_cohorte']);
            exit();
        }


        //validar  ajax cohorte
        if(isset($_GET['accion'])){
        	header('Content-Type: application/json');
    		require_once "../controladores/cohorteControlador.php";
            $controlador = new cohorteControlador();

            // 1. Validar cohorte único
                if ($_GET['accion'] === 'validar_cohorte_unico') {
                    $numero = mainModel::limpiar_cadena($_GET['numero']);
                    $anio = mainModel::limpiar_cadena($_GET['anio']);
                    $tipo = mainModel::limpiar_cadena($_GET['tipo']);
                    $alterno = isset($_GET['cohorte_alterno']) ? mainModel::limpiar_cadena($_GET['cohorte_alterno']) : "";
                    $id = isset($_GET['id']) ? mainModel::limpiar_cadena($_GET['id']) : null;

                    $cohorteUnico = $controlador->validar_cohorte_no_duplicado($numero, $anio, $tipo, $alterno, $id);
                    echo json_encode($cohorteUnico);
                    exit();
                }

                // 2. Validar solapamiento de fechas
                if($_GET['accion'] === 'validar_solapamiento_fechas') {
                    $tipo = mainModel::limpiar_cadena($_GET['tipo']);
                    $fechaInicio = mainModel::limpiar_cadena($_GET['fecha_inicio']);
                    $fechaFin = mainModel::limpiar_cadena($_GET['fecha_fin']);
                    $alterno = isset($_GET['cohorte_alterno']) ? mainModel::limpiar_cadena($_GET['cohorte_alterno']) : "";
                    $excluir = isset($_GET['excluir']) ? mainModel::limpiar_cadena($_GET['excluir']) : ''; 

                    $fechaSolapada = $controlador->validar_fecha_no_solapada($tipo, $fechaInicio, $fechaFin, $alterno, $excluir);
                    echo json_encode($fechaSolapada);
                    exit();
                }

                // 3. Obtener último cohorte
                if ($_GET['accion'] === 'obtener_ultimo_cohorte') {
                    $tipo = mainModel::limpiar_cadena($_GET['tipo']);
                    $excluir = isset($_GET['excluir']) ? mainModel::limpiar_cadena($_GET['excluir']) : '';

                    $contexto = $controlador->validar_traer_ultimo_cohorte($tipo, $excluir);
                    echo json_encode($contexto);
                    exit();
                }            
            
            
            /* no tiene lo de cohorte alterno
            //vigia que no haya un mismo cohorte del mismo numero, anio y tipo
            //retorna true o false, if true means el cohorte esta duplicado
            if ($_GET['accion'] === 'validar_cohorte_unico' && isset($_GET['tipo']) && isset($_GET['anio'])) {
                $numero = mainModel::limpiar_cadena($_GET['numero']);
                $anio = mainModel::limpiar_cadena($_GET['anio']);
                $tipo = mainModel::limpiar_cadena($_GET['tipo']);
                $id = isset($_GET['id']) ? mainModel::limpiar_cadena($_GET['id']) : null;

                $cohorteUnico = $controlador->validar_cohorte_no_duplicado($numero,$anio,$tipo, $id);
                echo json_encode($cohorteUnico);
                exit();
            }
            
            //vigila que las fechas no se solapen
            if($_GET['accion'] === 'validar_solapamiento_fechas' && isset($_GET['tipo']) && isset($_GET['fecha_inicio']) && isset($_GET['fecha_fin'])) {
                $tipo = mainModel::limpiar_cadena($_GET['tipo']);
                $fechaInicio = mainModel::limpiar_cadena($_GET['fecha_inicio']);
                $fechaFin = mainModel::limpiar_cadena($_GET['fecha_fin']);
                $excluir = isset($_GET['excluir']) ? mainModel::limpiar_cadena($_GET['excluir']) : ''; //es el id
                
                $fechaSolapada = $controlador->validar_fecha_no_solapada($tipo, $fechaInicio, $fechaFin,$excluir); //nose si poner $excluir=null
                echo json_encode($fechaSolapada);
                exit();
            }
            
            // Obtener último cohorte para mostrar información (para cohorte-new y cohorte-update)
            if ($_GET['accion'] === 'obtener_ultimo_cohorte' && isset($_GET['tipo']) && isset($_GET['anio'])) {
                $tipo = mainModel::limpiar_cadena($_GET['tipo']);
 			    //$anio = mainModel::limpiar_cadena($_GET['anio']);
    			$excluir = isset($_GET['excluir']) ? mainModel::limpiar_cadena($_GET['excluir']) : '';
                
                $contexto = $controlador->validar_traer_ultimo_cohorte($tipo,$excluir); //nose si poner $excluir=null
                echo json_encode($contexto);
                exit();
            }*/
 
            
        }





        /*--------- Agregar cohorte ---------*/
        if(isset($_POST['cohorte_tipo_reg'])){
            echo $ins_cohorte->agregar_cohorte_controlador();
            exit();
        }

        /*--------- Actualizar cohorte ---------*/
        if(isset($_POST['cohorte_id_up'])){
            echo $ins_cohorte->actualizar_cohorte_controlador();
            exit();
        }

        //archivar
        if(isset($_POST['cohorte_id_archivar'])){
            echo $ins_cohorte->archivar_cohorte_controlador();
            exit();
        }

        /*--------- Restaurar cohorte archivada ---------*/
        if(isset($_POST['cohorte_id_restore'])){
            echo $ins_cohorte->restaurar_cohorte_controlador();
            exit();
        }

        /*--------- Guardar cursos seleccionados para una cohorte ---------*/
        if(isset($_POST['cohorte_id_cursos_up'])){
            echo $ins_cohorte->actualizar_cursos_cohorte_controlador();
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