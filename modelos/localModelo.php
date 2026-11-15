<?php

require_once "mainModel.php";

class localModelo extends mainModel {
    
    
//trae los datos que necesita vistas home para las estadisticas de visitas e interacciones
    public static function obtener_estadisticas_home_supabase(string $accessToken): array {
        return SupabaseApi::rpcWithToken('obtener_estadisticas_de_visitas', [], $accessToken);
    }
    
    
// trae contadores y graficas seegun rol para home view
    public static function dashboard_datos_supabase(int $role, string $accessToken): array{
        return SupabaseApi::rpcWithToken('dashboard_datos', [
            'p_role' => $role,
        ], $accessToken);
    }

    
    
    
    public static function guardar_sliders_supabase(array $sliders, int $pageId, string $accessToken): array {
        return SupabaseApi::rpcWithToken('guardar_slider', [
            'p_page_id' => $pageId,
	        'p_sliders' => $sliders,
        ], $accessToken);
    }
    
    
  
    
    public static function obtener_imagenes_por_tipo_supabase(string $type, string $accessToken): array{
        $params = [
            'select' => 'id, route, type',
            'type' => 'eq.' . $type,
            'order' => 'id.asc',
        ];
        return SupabaseApi::getWithToken('img', $params, $accessToken);
    }
    
    
    
    public static function obtener_datos_local_supabase(string $accessToken): array {
        return SupabaseApi::getWithToken('page_info', [
            'select' => 'id,phone,email,facebook_link,instagram_link,city_address,country_address,page_color,rif,page_texts',
            'id' => 'eq.6',
            'limit' => '1',
        ], $accessToken);
    }
    

    
    
    
   public static function actualizar_local_supabase(array $datos, string $accessToken): array {
        return SupabaseApi::rpcWithToken('actualizar_page_info_con_imagenes', [
            'p_page_id' => (int) $datos['page_id'],
            'p_phone' => $datos['phone'],
            'p_email' => $datos['email'],
            'p_facebook_link' => $datos['facebook_link'],
            'p_instagram_link' => $datos['instagram_link'],
            'p_city_address' => $datos['city_address'],
            'p_country_address' => $datos['country_address'],
            'p_page_color' => $datos['page_color'],
            'p_rif' => $datos['rif'],
            'p_page_texts' => $datos['page_texts'],
            'p_images' => $datos['images'],
        ], $accessToken);
    }
    
    
}
?>