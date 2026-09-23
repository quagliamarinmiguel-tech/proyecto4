<?php

    require_once "mainModel.php";

    class cursoModelo extends mainModel{
        
        
        
                /*---------------------------------------  validaciones ------------------------------*/


        public static function verificar_curso_duplicado_supabase($nombre, $tipo, $accessToken, $id_excluir = null): array
        {
            $filtros = [
                'select' => 'id',
                'name' => 'eq.' . $nombre,
                'type' => 'eq.' . $tipo,
                'state' => 'neq.0',
            ];

            if ($id_excluir !== null && $id_excluir !== '') {
                $filtros['id'] = 'neq.' . $id_excluir;
            }

            return SupabaseApi::getWithToken('course', $filtros, $accessToken);
        }

        public static function agregar_curso_supabase(array $datos, string $accessToken): array
        {
            return SupabaseApi::postWithToken('course', [
                'name' => $datos['Nombre'],
                'type' => $datos['Tipo'],
                'state' => (int) $datos['Estado'],
            ], $accessToken);
        }

        public static function actualizar_curso_supabase(array $datos, string $accessToken): array
        {
            return SupabaseApi::patchWithToken('course', [
                'id' => 'eq.' . (int) $datos['ID'],
            ], [
                'name' => $datos['Nombre'],
                'type' => $datos['Tipo'],
            ], $accessToken);
        }

        public static function obtener_curso_supabase(int $id, string $accessToken): array
        {
            return SupabaseApi::getWithToken('course', [
                'select' => 'id,name,type,state',
                'id' => 'eq.' . $id,
            ], $accessToken);
        }

        public static function verificar_curso_periodo_supabase(int $id, string $accessToken): array
        {
            return SupabaseApi::getWithToken('period_course', [
                'select' => 'id',
                'course_id' => 'eq.' . $id,
                'state' => 'neq.0',
                'limit' => '1',
            ], $accessToken);
        }

     

        /*public static function archivar_curso_cascada_supabase(int $cursoId, string $accessToken): array
        {
            return SupabaseApi::rpcWithToken('archive_course_cascade', [
                'course_id_param' => $cursoId,
            ], $accessToken);
        }*/
        public static function archivar_curso_cascada_supabase(int $courseId, string $accessToken): array{
            return SupabaseApi::rpcWithToken('archivar_curso_cascada', ['p_course_id' => $courseId], $accessToken);
        }

        //restaurar curso,bueno, el course y course marketing, no period course para evitar restauraciones imprevistas de cursos vigentes
        public static function restaurar_curso_supabase(int $courseId, string $accessToken): array{
            return SupabaseApi::rpcWithToken('restaurar_curso', ['p_course_id' => $courseId], $accessToken);
        }
        
        public static function obtener_nombre_curso_supabase(int $id, string $accessToken): array
        {
            return SupabaseApi::getWithToken('course', [
                'select' => 'id,name,type',
                'id' => 'eq.' . $id,
            ], $accessToken);
        }

        
        
// la lista de course list y course list archived
    public static function listar_cursos_supabase(array $filtros, string $accessToken, int $limit, int $offset): array{
        $params = [
            'p_tipo' => $filtros['tipo'] ?? null,
            'p_has_marketing' => $filtros['has_marketing'] ?? null,
            'p_id' => $filtros['id'] ?? null,
            'p_orden' => $filtros['orden'] ?? 'name',
            'p_limit' => $limit,
            'p_offset' => $offset,
            'p_state' => (int) ($filtros['state'] ?? 1), 
        ];
        return SupabaseApi::rpcWithToken('listar_cursos', $params, $accessToken);
    }
        
        
        
        
        
        
        
        
        
                /*--------- ----------------------------pdo viejo------------------------------------- ---------/

       
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
            }*/
        
        
        
        
        
   
            

      
    
        
        
        

        
        
        /*--------- Modelo agregar curso antiguo ---------/
        protected static function agregar_curso_modelo($datos){
            $sql=mainModel::conectar()->prepare("INSERT INTO curso(nombre_curso,tipo_curso,estado_curso) VALUES(:Nombre,:Tipo,:Estado)");

            $sql->bindParam(":Nombre",$datos['Nombre']);
            $sql->bindParam(":Tipo",$datos['Tipo']);
     //       $sql->bindParam(":Cupos",$datos['Cupos']);
            $sql->bindParam(":Estado",$datos['Estado']);

            $sql->execute();
            return $sql;
        }*/

        /*--------- Modelo eliminar curso ---------/
        protected static function eliminar_curso_modelo($id){
            $sql=mainModel::conectar()->prepare("DELETE FROM curso WHERE id_curso=:ID");
            $sql->bindParam(":ID",$id);
            $sql->execute();
            return $sql;
        }

        /--------- Modelo datos curso ---------/
        protected static function datos_curso_modelo($tipo,$id){
            if($tipo=="Unico"){
                $sql=mainModel::conectar()->prepare("SELECT * FROM curso WHERE id_curso=:ID");
                $sql->bindParam(":ID",$id);
            }elseif($tipo=="Conteo"){
                $sql=mainModel::conectar()->prepare("SELECT id_curso FROM curso");
            }

            $sql->execute();
            return $sql;
        }*/

        /*--------- Modelo actualizar curso --------/
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
        }*/




    }
?>
