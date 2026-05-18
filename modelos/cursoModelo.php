<?php

    require_once "mainModel.php";

    class cursoModelo extends mainModel{
        
        
        
                /*---------------------------------------  validaciones ------------------------------*/

        
         public static function verificar_curso_duplicado($nombre, $tipo, $id_excluir = null) {
        
            $nombre = self::limpiar_cadena($nombre);
            $tipo = self::limpiar_cadena($tipo);

            $consulta = "SELECT id_curso FROM curso 
                         WHERE nombre_curso = '$nombre' 
                         AND tipo_curso = '$tipo' 
                         AND estado_curso != 0";

            if($id_excluir !== null) {
                $id_excluir = self::limpiar_cadena($id_excluir);
                $consulta .= " AND id_curso != '$id_excluir'";
            }

            $resultado = parent::ejecutar_consulta_simple($consulta);
            return ($resultado->rowCount() > 0);
 	   }

                /*--------- ----------------------------traer datos------------------------------------- ---------*/

       
            //para course-update, verifica si el curso ya cuenta con un periodo activo, en tal caso no puede cambiarse su tipo
            public static function verificar_curso_periodo_existente_modelo($id){
				$consulta = "SELECT tipo_curso FROM curso_vigente cv INNER JOIN curso c ON cv.id_curso_fk = c.id_curso WHERE cv.estado_curso_vigente = 1 AND c.id_curso = '$id' LIMIT 1";
                $resultado = parent::ejecutar_consulta_simple($consulta);
				return $resultado;
        	}

        	// para course update, trae el nombre y tipo del curso si al cargar la vista se filtro un curso por nombre
            public static function obtener_nombre_curso_para_filtro_modelo($id_curso) {
                $consulta="SELECT nombre_curso, tipo_curso FROM curso WHERE id_curso = '$id_curso'";
                $resultado = parent::ejecutar_consulta_simple($consulta);
				return $resultado;
            }
        
        
        
        
        
   
            

      
    
        
        
        

        
        
        /*--------- Modelo agregar curso ---------*/
        protected static function agregar_curso_modelo($datos){
            $sql=mainModel::conectar()->prepare("INSERT INTO curso(nombre_curso,tipo_curso,estado_curso) VALUES(:Nombre,:Tipo,:Estado)");

            $sql->bindParam(":Nombre",$datos['Nombre']);
            $sql->bindParam(":Tipo",$datos['Tipo']);
     //       $sql->bindParam(":Cupos",$datos['Cupos']);
            $sql->bindParam(":Estado",$datos['Estado']);

            $sql->execute();
            return $sql;
        }

        /*--------- Modelo eliminar curso ---------*/
        protected static function eliminar_curso_modelo($id){
            $sql=mainModel::conectar()->prepare("DELETE FROM curso WHERE id_curso=:ID");
            $sql->bindParam(":ID",$id);
            $sql->execute();
            return $sql;
        }

        /*--------- Modelo datos curso ---------*/
        protected static function datos_curso_modelo($tipo,$id){
            if($tipo=="Unico"){
                $sql=mainModel::conectar()->prepare("SELECT * FROM curso WHERE id_curso=:ID");
                $sql->bindParam(":ID",$id);
            }elseif($tipo=="Conteo"){
                $sql=mainModel::conectar()->prepare("SELECT id_curso FROM curso");
            }

            $sql->execute();
            return $sql;
        }

        /*--------- Modelo actualizar curso ---------*/
        protected static function actualizar_curso_modelo($datos){
            $sql=mainModel::conectar()->prepare("UPDATE curso SET nombre_curso=:Nombre,tipo_curso=:Tipo WHERE id_curso=:ID");

            $sql->bindParam(":Nombre",$datos['Nombre']);
            $sql->bindParam(":Tipo",$datos['Tipo']);
       //     $sql->bindParam(":Cupos",$datos['Cupos']);
            $sql->bindParam(":ID",$datos['ID']);

            $sql->execute();
            return $sql;
        }

        //Traerse los cursos para el cohorte segun el tipo
        public static function obtener_cursos_por_tipo($tipo){
            $sql = mainModel::conectar()->prepare("
                SELECT id_curso, nombre_curso 
                FROM curso 
                WHERE tipo_curso = :Tipo AND estado_curso = 1 
                ORDER BY nombre_curso ASC
            ");
            $sql->bindParam(":Tipo", $tipo);
            $sql->execute();
            return $sql;
        }




    }
?>
