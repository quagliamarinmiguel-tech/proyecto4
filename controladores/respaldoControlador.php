<?php

if($peticionAjax){
    require_once "../modelos/respaldoModelo.php";
}else{
    require_once "./modelos/respaldoModelo.php";
}

class respaldoControlador extends respaldoModelo{
    
    
    
        public function generar_respaldo_controlador(){
        
        // Verificar que el usuario tenga sesión activa y sea administrador
        session_start(['name' => 'SPM']);
        
        if(!isset($_SESSION['id_spm']) || $_SESSION['cargo_spm'] != 1){
            $alerta=[
                "Alerta"=>"simple",
                "Titulo"=>"Acceso denegado",
                "Texto"=>"No tienes permisos para realizar esta acción",
                "Tipo"=>"error"
            ];
            echo json_encode($alerta);
            exit();
        }

        try {
            // Aumentar límites de tiempo y memoria
            set_time_limit(300);
            ini_set('memory_limit', '256M');

            // Generar el respaldo
            $resultado = respaldoModelo::generar_respaldo_modelo();

            if($resultado['success']){
                respaldoModelo::actualizar_fecha_respaldo_modelo();
                
                // Preparar descarga del archivo
                $filename = 'ceproca_backup_' . date('Y-m-d_H-i-s') . '.sql';
                
                header('Content-Type: application/sql');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . strlen($resultado['sql']));
                
                echo $resultado['sql'];
                exit();
            }else{
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Error al generar respaldo",
                    "Texto"=>$resultado['mensaje'],
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

        } catch (Exception $e) {
            $alerta=[
                "Alerta"=>"simple",
                "Titulo"=>"Error inesperado",
                "Texto"=>"No se pudo generar el respaldo: " . $e->getMessage(),
                "Tipo"=>"error"
            ];
            echo json_encode($alerta);
            exit();
        }
    }

    /*--------- Controlador obtener fecha ultimo respaldo ---------*/
    public function obtener_fecha_respaldo_controlador(){
        $resultado = respaldoModelo::obtener_fecha_respaldo_modelo();
        return $resultado;
    }

    /*--------- Controlador generar respaldo ---------
    public function generar_respaldo_controlador(){
        
        // Verificar que el usuario tenga sesión activa y sea administrador
        session_start(['name' => 'SPM']);
        
        if(!isset($_SESSION['id_spm']) || $_SESSION['cargo_spm'] != 1){
            $alerta=[
                "Alerta"=>"simple",
                "Titulo"=>"Acceso denegado",
                "Texto"=>"No tienes permisos para realizar esta acción",
                "Tipo"=>"error"
            ];
            echo json_encode($alerta);
            exit();
        }

        try {
            // Aumentar límites de tiempo y memoria
            set_time_limit(300);
            ini_set('memory_limit', '256M');

            // Generar el respaldo
            $resultado = respaldoModelo::generar_respaldo_modelo();

            if($resultado['success']){
                // Preparar descarga del archivo
                $filename = 'ceproca_backup_' . date('Y-m-d_H-i-s') . '.sql';
                
                header('Content-Type: application/sql');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . strlen($resultado['sql']));
                
                echo $resultado['sql'];
                exit();
            }else{
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Error al generar respaldo",
                    "Texto"=>$resultado['mensaje'],
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

        } catch (Exception $e) {
            $alerta=[
                "Alerta"=>"simple",
                "Titulo"=>"Error inesperado",
                "Texto"=>"No se pudo generar el respaldo: " . $e->getMessage(),
                "Tipo"=>"error"
            ];
            echo json_encode($alerta);
            exit();
        }
    }*/

} 
?>
