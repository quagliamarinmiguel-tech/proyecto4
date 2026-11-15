<?php

require_once "mainModel.php";

class publicoModelo extends mainModel {

    
    
    
//estas 2 funciones traen los datos para las vistas publicas
    public static function obtener_datos_local_publico_supabase(): array {
        return SupabaseApi::get('page_info', [
            'select' => 'id,phone,email,facebook_link,instagram_link,city_address,country_address,page_color,rif,page_texts',
            'limit' => '1',
        ]);
    }

    public static function obtener_imagenes_pagina_publico_supabase(int $pageId): array {
        $respuesta = SupabaseApi::get('page_info_img', [
            'select' => 'img_id,img_name,page_id',
            'page_id' => 'eq.' . $pageId,
        ]);

        if (!$respuesta['ok'] || empty($respuesta['data'])) {
            return [];
        }

        $ids = array_values(array_unique(array_filter(array_map(function ($item) {
            return isset($item['img_id']) && $item['img_id'] !== null ? (int) $item['img_id'] : null;
        }, $respuesta['data']))));

        if (empty($ids)) {
            return [];
        }

        $imgResp = SupabaseApi::get('img', [
            'select' => 'id,route,type',
            'id' => 'in.(' . implode(',', $ids) . ')',
            'type' => 'eq.ifpg',
        ]);

        if (!$imgResp['ok'] || empty($imgResp['data'])) {
            return [];
        }

        $rutaPorId = [];
        foreach ($imgResp['data'] as $img) {
            $rutaPorId[(int) ($img['id'] ?? 0)] = (string) ($img['route'] ?? '');
        }

        $map = [];
        foreach ($respuesta['data'] as $fila) {
            $id = isset($fila['img_id']) ? (int) $fila['img_id'] : 0;
            $imgName = trim((string) ($fila['img_name'] ?? ''));
            if ($id > 0 && $imgName !== '' && array_key_exists($id, $rutaPorId)) {
                $map[$imgName] = $rutaPorId[$id];
            }
        }

        return $map;
    }
    
    
//esto trae los cursos, profes y sliders de inicio view
//si no recibe ip  solo devuelve los datos normales
    public static function obtener_datos_inicio_supabase(?string $ip = null): array {
                
       return SupabaseApi::rpcWithoutToken('obtener_datos_inicio', [
            'p_ip' => $ip,
        ]);
    }
    
    
    
 //esto lista los cursos de cursos view
    public static function listar_cursos_publico_supabase($tipo = null, $page = 1, $limit = 20, $busqueda = null): array {
        $params = [
            'p_tipo' => $tipo,
            'p_page' => (int) $page,
            'p_limit' => (int) $limit,
            'p_busqueda' => $busqueda,
        ];
        return SupabaseApi::rpcWithoutToken('listar_cursos_publico', $params);

    }

    
    
    
    
    
    
    
//esto es para registrar el click a un curso y a partir de aqui estan las funciones de cursos detail
    
// Buscar o crear un ip_visitor
    public static function obtener_o_crear_ip_visitor_supabase(string $ip): int {
        // Buscar existente
        $resp = SupabaseApi::get('ip_visitor', [
            'select' => 'id',
            'ip_direction' => 'eq.' . $ip,
            'limit' => '1',
        ]);

        if ($resp['ok'] && !empty($resp['data'])) {
            return (int) $resp['data'][0]['id'];
        }

        // Crear nuevo
        $insert = SupabaseApi::post('ip_visitor', [
            'ip_direction' => $ip,
            'failed_login_access_attemps' => 0,
            'failed_recover_account_attemps' => 0,
            'failed_creating_account_attemps' => 0,
        ]);

        if ($insert['ok'] && !empty($insert['data'])) {
            return (int) $insert['data'][0]['id'];
        }

        return 0;
    }

    
//verifica si existe la ip, sino la crea y luego registra la interaccion al seleccionar un curso en inicio o cursos view
    public static function registrar_interaccion_supabase(string $ip, int $marketingId): array {
        return SupabaseApi::rpcWithoutToken('registrar_interaccion_para_cursos_e_inicio_view', [
            'p_ip' => $ip,
            'p_marketing_id' => $marketingId,
        ]);
    }
    
    
//son los datos para cursos detail
    public static function obtener_curso_detalle_publico_supabase(int $marketingId): array {
        return SupabaseApi::rpcWithoutToken('obtener_curso_detalle_publico_para_cursos_detail', [
            'p_marketing_id' => $marketingId,
        ]);
    }
    
    
    
//una lista de los cursos disponibles para inscribirse en solicitud inscripcion view, 
//trae todos para un select
public static function listar_cursos_inscripcion_publico_supabase(): array {
    return SupabaseApi::rpcWithoutToken('listar_cursos_para_solicitud_inscripcion_publico', []);
}
    
    
    
    
    
    
    
    
    
    

}//fin clase