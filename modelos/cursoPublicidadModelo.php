<?php
require_once "mainModel.php";

class cursoPublicidadModelo extends mainModel {

    
    public static function crear_marketing_curso_supabase(array $datos, string $accessToken): array
    {
        $params = [
            'p_course_id' => $datos['course_id'],
            'p_img_route' => $datos['img_route'],
            'p_img_type' => $datos['img_type'],
            'p_introduction' => $datos['introduction'],
            'p_class' => $datos['class'],
            'p_description' => $datos['description'],
            'p_content_1' => $datos['content_1'] ?? null,
            'p_content_2' => $datos['content_2'] ?? null,
            'p_content_3' => $datos['content_3'] ?? null,
            'p_content_4' => $datos['content_4'] ?? null,
            'p_content_5' => $datos['content_5'] ?? null,
            'p_content_6' => $datos['content_6'] ?? null,
            'p_content_7' => $datos['content_7'] ?? null,
            'p_content_8' => $datos['content_8'] ?? null,
            'p_content_9' => $datos['content_9'] ?? null,
            'p_content_10' => $datos['content_10'] ?? null,
            'p_requirement_1' => $datos['requirement_1'] ?? null,
            'p_requirement_2' => $datos['requirement_2'] ?? null,
            'p_requirement_3' => $datos['requirement_3'] ?? null,
            'p_requirement_4' => $datos['requirement_4'] ?? null,
            'p_icon_1' => $datos['icon_1'] ?? false,
            'p_icon_2' => $datos['icon_2'] ?? false,
            'p_icon_3' => $datos['icon_3'] ?? false,
            'p_state' => $datos['state'] ?? 1,
        ];
        return SupabaseApi::rpcWithToken('crear_marketing_curso', $params, $accessToken);
    }
    
    
    
    
    

    /**
     * Obtiene cursos activos que NO tienen publicidad.
     * @param string $accessToken Token de Supabase.
     * @return array Array de cursos con id, name, type.
     */
    public static function obtener_cursos_sin_publicidad_supabase(string $accessToken): array{
        // 1. Obtener IDs de cursos con publicidad
        $conPublicidad = SupabaseApi::getWithToken('marketing_course', [
            'select' => 'course_id',
            'state' => 'eq.1', // solo publicidad activa (ajusta según tu lógica)
        ], $accessToken);

        $idsConPublicidad = array_column($conPublicidad['data'] ?? [], 'course_id');
        if (empty($idsConPublicidad)) {
            $idsConPublicidad = [0]; // Para evitar error en NOT.IN
        }

        // 2. Obtener cursos activos que NO estén en esa lista
        $resultado = SupabaseApi::getWithToken('course', [
            'select' => 'id,name,type',
            'state' => 'eq.1',
            'id' => 'not.in.(' . implode(',', $idsConPublicidad) . ')',
            'order' => 'name.asc',
        ], $accessToken);

        if (!$resultado['ok']) {
            return [];
        }

        // Formatear para que coincida con lo que espera la vista (id_curso, nombre_curso, tipo_curso)
        $cursos = [];
        foreach ($resultado['data'] as $curso) {
            $cursos[] = [
                'id_curso' => $curso['id'],
                'nombre_curso' => $curso['name'],
                'tipo_curso' => $curso['type'],
            ];
        }
        return $cursos;
    }
    
    

    public static function listar_marketing_cursos_supabase(array $filtros, string $accessToken, int $limit, int $offset): array{
        $params = [
            'p_busqueda' => $filtros['busqueda'] ?? null,
            'p_tipo' => $filtros['tipo'] ?? null,
            'p_estado' => $filtros['estado'] ?? null,
            'p_id' => isset($filtros['id']) && $filtros['id'] !== '' ? (int) $filtros['id'] : null,
            'p_orden' => $filtros['orden'] ?? 'reciente',
            'p_limit' => $limit,
            'p_offset' => $offset,
             'p_state' => (int) ($filtros['state'] ?? 1),
        ];
        //            'p_state' => (int) ($filtros['state'] ?? 1),
        //            'p_state' => 1,
        //var_dump($params);die();
        return SupabaseApi::rpcWithToken('listar_marketing_cursos', $params, $accessToken);
    }
    


    // Obtener datos de una publicidad por ID (con datos del curso e imagen)
    public static function obtener_marketing_curso_supabase(int $marketingId, string $accessToken, $state=1): array{
        return SupabaseApi::getWithToken('marketing_course', [
            'select' => 'id,course_id,introduction,class,description,content_1,content_2,content_3,content_4,content_5,content_6,content_7,content_8,content_9,content_10,requirement_1,requirement_2,requirement_3,requirement_4,icon_1,icon_2,icon_3,state,img_id,course!inner(id,name,type),img!inner(route)',
            'id' => 'eq.' . $marketingId,
            'state' => 'eq.' . $state,
        ], $accessToken);
        //'state' => 'neq.0',
    }

    // Actualizar publicidad
    public static function actualizar_marketing_curso_supabase(array $datos, string $accessToken): array{
        $params = [
            'p_marketing_id' => $datos['marketing_id'],
            'p_introduction' => $datos['introduction'],
            'p_class' => $datos['class'],
            'p_description' => $datos['description'],
            'p_content_1' => $datos['content_1'] ?? null,
            'p_content_2' => $datos['content_2'] ?? null,
            'p_content_3' => $datos['content_3'] ?? null,
            'p_content_4' => $datos['content_4'] ?? null,
            'p_content_5' => $datos['content_5'] ?? null,
            'p_content_6' => $datos['content_6'] ?? null,
            'p_content_7' => $datos['content_7'] ?? null,
            'p_content_8' => $datos['content_8'] ?? null,
            'p_content_9' => $datos['content_9'] ?? null,
            'p_content_10' => $datos['content_10'] ?? null,
            'p_requirement_1' => $datos['requirement_1'] ?? null,
            'p_requirement_2' => $datos['requirement_2'] ?? null,
            'p_requirement_3' => $datos['requirement_3'] ?? null,
            'p_requirement_4' => $datos['requirement_4'] ?? null,
            'p_icon_1' => $datos['icon_1'] ?? false,
            'p_icon_2' => $datos['icon_2'] ?? false,
            'p_icon_3' => $datos['icon_3'] ?? false,
            'p_img_route' => $datos['img_route'] ?? null,
            'p_img_type' => $datos['img_type'] ?? 'curs-p',
        ];
        return SupabaseApi::rpcWithToken('actualizar_marketing_curso', $params, $accessToken);
    }
    
    
    

    public static function archivar_marketing_curso_supabase(int $marketingId, string $accessToken): array{
        return SupabaseApi::patchWithToken('marketing_course', ['id' => 'eq.' . $marketingId], ['state' => 0], $accessToken);
    }

    public static function restaurar_marketing_curso_supabase(int $marketingId, string $accessToken): array{
        return SupabaseApi::patchWithToken('marketing_course', ['id' => 'eq.' . $marketingId], ['state' => 1], $accessToken);
    }
    

    
    
    
    
    
    
    
    
    
    
    
    /*--------- Modelo obtener datos publicidad ---------*/
    public static function datos_publicidad_modelo($tipo, $id){
        if($tipo == "Unico"){
            $sql = mainModel::conectar()->prepare("
                SELECT cp.*, i.ruta AS ruta_imagen, c.nombre_curso, c.tipo_curso
                FROM curso_publicidad cp
                INNER JOIN imagen i ON cp.id_imagen_materia_fk = i.id_imagen
                INNER JOIN curso c ON cp.id_curso = c.id_curso
                WHERE cp.id_materia = :ID
            ");
            $sql->bindParam(":ID", $id, PDO::PARAM_INT);
        } elseif($tipo == "Conteo"){
            $sql = mainModel::conectar()->prepare("SELECT id_materia FROM curso_publicidad");
        }

        $sql->execute();
        return $sql;
    }

    /*--------- Modelo eliminar publicidad ---------*/
    protected static function eliminar_publicidad_modelo($id){
        $sql = mainModel::conectar()->prepare("DELETE FROM curso_publicidad WHERE id_materia = :ID");
        $sql->bindParam(":ID", $id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /*--------- Modelo archivar publicidad ---------*/
    protected static function archivar_publicidad_modelo($id){
        $sql = mainModel::conectar()->prepare("UPDATE curso_publicidad SET estado_materia = 0 WHERE id_materia = :ID");
        $sql->bindParam(":ID", $id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /*--------- Modelo restaurar publicidad ---------*/
    protected static function restaurar_publicidad_modelo($id){
        $sql = mainModel::conectar()->prepare("UPDATE curso_publicidad SET estado_materia = 1 WHERE id_materia = :ID");
        $sql->bindParam(":ID", $id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }


    /*--------- Modelo actualizar publicidad ---------*
    protected static function actualizar_publicidad_modelo($datos){
        $sql = mainModel::conectar()->prepare("
            UPDATE curso_publicidad SET
                prefacio = :prefacio,
                materia_descripcion = :descripcion,
                cont1 = :cont1, cont2 = :cont2, cont3 = :cont3, cont4 = :cont4, cont5 = :cont5,
                cont6 = :cont6, cont7 = :cont7, cont8 = :cont8, cont9 = :cont9, cont10 = :cont10,
                requisito1 = :req1, requisito2 = :req2, requisito3 = :req3, requisito4 = :req4,
                icono1 = :ico1, icono2 = :ico2, icono3 = :ico3,
                clase = :clase
            WHERE id_materia = :ID
        ");

        $sql->bindParam(":prefacio", $datos['prefacio']);
        $sql->bindParam(":descripcion", $datos['materia_descripcion']);
        $sql->bindParam(":clase", $datos['clase']);

        for($i = 1; $i <= 10; $i++){
            $sql->bindParam(":cont$i", $datos["cont$i"]);
        }

        for($i = 1; $i <= 4; $i++){
            $sql->bindParam(":req$i", $datos["requisito$i"]);
        }

        for($i = 1; $i <= 3; $i++){
            $sql->bindParam(":ico$i", $datos["icono$i"], PDO::PARAM_INT);
        }

        $sql->bindParam(":ID", $datos['id_materia'], PDO::PARAM_INT);

        $sql->execute();
        return $sql;
    }*/


}