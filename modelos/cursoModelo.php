<?php

    require_once "mainModel.php";

    class cursoModelo extends mainModel{
        
        
                
        
        public static function cursos_pdf_prueba(string $accessToken): array{
            return SupabaseApi::getWithToken('course', [
                'select' => 'id,name,type,state',
                'state' => 'neq.0',
            ], $accessToken);
        }

        public static function verificar_curso_duplicado_supabase($nombre, $tipo, $accessToken, $id_excluir = null): array{
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

        public static function agregar_curso_supabase(array $datos, string $accessToken): array{
            return SupabaseApi::postWithToken('course', [
                'name' => $datos['Nombre'],
                'type' => $datos['Tipo'],
                'state' => (int) $datos['Estado'],
            ], $accessToken);
        }

        public static function actualizar_curso_supabase(array $datos, string $accessToken): array{
            return SupabaseApi::patchWithToken('course', [
                'id' => 'eq.' . (int) $datos['ID'],
            ], [
                'name' => $datos['Nombre'],
                'type' => $datos['Tipo'],
            ], $accessToken);
        }

        public static function obtener_curso_supabase(int $id, string $accessToken): array{
            return SupabaseApi::getWithToken('course', [
                'select' => 'id,name,type,state',
                'id' => 'eq.' . $id,
            ], $accessToken);
        }

        public static function verificar_curso_periodo_supabase(int $id, string $accessToken): array{
            return SupabaseApi::getWithToken('period_course', [
                'select' => 'id',
                'course_id' => 'eq.' . $id,
                'state' => 'neq.0',
                'limit' => '1',
            ], $accessToken);
        }

     

 
        public static function archivar_curso_cascada_supabase(int $courseId, string $accessToken): array{
            return SupabaseApi::rpcWithToken('archivar_curso_cascada', ['p_course_id' => $courseId], $accessToken);
        }

        //restaurar curso,bueno, el course y course marketing, no period course para evitar restauraciones imprevistas de cursos vigentes
        public static function restaurar_curso_supabase(int $courseId, string $accessToken): array{
            return SupabaseApi::rpcWithToken('restaurar_curso', ['p_course_id' => $courseId], $accessToken);
        }
        
        public static function obtener_nombre_curso_supabase(int $id, string $accessToken): array{
            return SupabaseApi::getWithToken('course', [
                'select' => 'id,name,type',
                'id' => 'eq.' . $id,
            ], $accessToken);
        }

        

    public static function listar_cursos_supabase(array $filtros, string $accessToken, int $limit, int $offset): array {
        $params = [
            'p_tipo'          => $filtros['tipo'] ?? null,
            'p_has_marketing' => isset($filtros['has_marketing']) && $filtros['has_marketing'] !== ''
                                    ? (bool) $filtros['has_marketing']
                                    : null,
            'p_id'            => isset($filtros['id']) && $filtros['id'] !== ''
                                    ? (int) $filtros['id']
                                    : null,
            'p_orden'         => $filtros['orden'] ?? 'name',
            'p_limit'         => $limit,
            'p_offset'        => $offset,
            'p_state'         => (int) ($filtros['state'] ?? 1),
        ];

        // Clave de caché basada en TODOS los parámetros que afectan el resultado
        $claveCache = 'cursos_publicos_' . md5(json_encode($params));

        // 1. Intentar leer de caché
        $dataCache = mainModel::leerCache($claveCache);
        if ($dataCache !== null) {
            return $dataCache;
        }

        // 2. Consultar a Supabase
        $resultado = SupabaseApi::rpcWithToken('listar_cursos', $params, $accessToken);

        // 3. Guardar en caché si fue exitoso
        if (!empty($resultado['ok'])) {
            mainModel::guardarCache($claveCache, $resultado, 3600);
        }

        return $resultado;
    }
        
        
//@@@@@@@@@@@@@@ rpcs para la generacion de reportes, ya sea para cursos individuales o generales @@@@@@@
    // RPC GENERALES
    public static function reportes_cursos_generales_supabase(
        int $tipo,
        int $meses,
        int $limit,
        string $accessToken
    ): array {
        return SupabaseApi::rpcWithToken('reportes_cursos_generales', [
            'p_tipo'  => $tipo,
            'p_meses' => $meses,
            'p_limit' => $limit,
        ], $accessToken);
    }

    // RPC INDIVIDUALES
    public static function reportes_curso_individual_supabase(
        int $courseId,
        int $tipo,
        int $meses,
        string $accessToken
    ): array {
        return SupabaseApi::rpcWithToken('reportes_curso_individual', [
            'p_course_id' => $courseId,
            'p_tipo'      => $tipo,
            'p_meses'     => $meses,
        ], $accessToken);
    }
        
        
        
        
        
        
        
        
        
        
        
        

    }
?>
