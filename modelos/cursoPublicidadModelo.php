<?php
require_once "mainModel.php";

class cursoPublicidadModelo extends mainModel {

    
    
    public static function validar_publicidad_duplicada($courseId, $accessToken): array {
    	return SupabaseApi::getWithToken('marketing_course', [
                'select' => 'id',
                'course_id' => 'eq.' . $courseId,
                'state' => 'eq.1',
        ], $accessToken);             
    }

    
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
        $conPublicidad = SupabaseApi::getWithToken('marketing_course', [
            'select' => 'course_id',
            'state' => 'eq.1', // solo publicidad activa 
        ], $accessToken);

        $idsConPublicidad = array_column($conPublicidad['data'] ?? [], 'course_id');
        if (empty($idsConPublicidad)) {
            $idsConPublicidad = [0]; // Para evitar error en NOT.IN
        }

        // Obtener cursos activos que NO estén en esa lista
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
    
    
//lista courses marketing (se que esta mal traducido) y pone cache
    public static function listar_marketing_cursos_supabase(array $filtros, string $accessToken, int $limit, int $offset): array {
        $params = [
            'p_busqueda' => $filtros['busqueda'] ?? null,
            'p_tipo'     => $filtros['tipo'] ?? null,
            'p_estado'   => $filtros['estado'] ?? null,
            'p_id'       => isset($filtros['id']) && $filtros['id'] !== '' ? (int) $filtros['id'] : null,
            'p_orden'    => $filtros['orden'] ?? 'reciente',
            'p_limit'    => $limit,
            'p_offset'   => $offset,
            'p_state'    => (int) ($filtros['state'] ?? 1),
        ];

        // Clave única basada en TODOS los parámetros que afectan el resultado
        $claveCache = 'marketing_cursos_' . md5(json_encode($params));

        //  Intentar leer de caché
        $dataCache = mainModel::leerCache($claveCache);
        if ($dataCache !== null) {
            return $dataCache;
        }

        //  Consultar a Supabase
        $resultado = SupabaseApi::rpcWithToken('listar_marketing_cursos', $params, $accessToken);

        //  Guardar en caché si fue exitoso (TTL 1 hora)
        if (!empty($resultado['ok'])) {
            mainModel::guardarCache($claveCache, $resultado, 3600);
        }

        return $resultado;
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
    


}