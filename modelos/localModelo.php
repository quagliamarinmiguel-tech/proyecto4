<?php

require_once "mainModel.php";

class localModelo extends mainModel {

    //DEBES HACER UNA FUNCION BUENA Q TRAIGA EL ID DE LA VISTA REAL, 
    //njd no recuerdo porque escribi eso, puede se q hablaba del id de page_info, dios mio las mrdas q escribo....
    
//trae los datos que necesita vistas home para las estadisticas de visitas e interacciones
    public static function obtener_estadisticas_home_supabase(string $accessToken): array {
        return SupabaseApi::rpcWithToken('obtener_estadisticas_de_visitas', [], $accessToken);
    }
    
    /*
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
//sj no recibe ip  solo devuelve los datos normales
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
        //$resultado=SupabaseApi::rpcWithoutToken('listar_cursos_publico', $params);
        //var_dump($resultado); die();

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
    
//verifica si existe la ip, sino la crea y luego registra la interaccion
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
    
 */   
    
    
    /*----------------------------------------------------------------------------------------------*/
    
    
    
    public static function guardar_sliders_supabase(array $sliders, int $pageId, string $accessToken): array {
        //var_dump($pageId,$sliders);die();
        //var_dump($sliders);die();
        //'p_sliders' => json_encode($sliders),
        //$resultado=SupabaseApi::rpcWithToken('guardar_slider', ['p_page_id' => $pageId,'p_sliders' => $sliders], $accessToken);
         //var_dump($resultado); die();
        return SupabaseApi::rpcWithToken('guardar_slider', [
            'p_page_id' => $pageId,
	        'p_sliders' => $sliders,
        ], $accessToken);
    }
    
    
  
    
    public static function obtener_imagenes_por_tipo_supabase(string $type, string $accessToken): array
    {
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
       //var_dump($datos);die();
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
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /*--------- Modelo actualizar información institucional ---------*
    protected static function actualizar_local_modelo($datos){
        $sql = mainModel::conectar()->prepare("
            UPDATE informacion_pagina SET
                telefono_contacto = :Telefono,
                correo_contacto = :Correo,
                link_facebook = :Facebook,
                link_instagram = :Instagram,
                direccion_local_ciudad = :Ciudad,
                direccion_local_pais = :Pais,
                nombre_local = :NombreCompleto,
                nombre_local_corto = :NombreCorto,
                rif = :Rif,
                colores_pagina = :ColoresJson,
                textos_pagina = :TextosJson
            WHERE id_informacion_pagina = 1
        ");

        $sql->bindParam(":Telefono", $datos['Telefono']);
        $sql->bindParam(":Correo", $datos['Correo']);
        $sql->bindParam(":Facebook", $datos['Facebook']);
        $sql->bindParam(":Instagram", $datos['Instagram']);
        $sql->bindParam(":Ciudad", $datos['Ciudad']);
        $sql->bindParam(":Pais", $datos['Pais']);
        $sql->bindParam(":NombreCompleto", $datos['NombreCompleto']);
        $sql->bindParam(":NombreCorto", $datos['NombreCorto']);
        $sql->bindParam(":Rif", $datos['Rif']);
        $sql->bindParam(":ColoresJson", $datos['ColoresJson']);
        $sql->bindParam(":TextosJson", $datos['TextosJson']);

        $sql->execute();
        return $sql;
    }

    /*--------- Modelo obtener datos del local ---------*
    protected static function datos_local_modelo(){
        $sql = mainModel::conectar()->prepare("SELECT * FROM informacion_pagina WHERE id_informacion_pagina = 1");
        $sql->execute();
        return $sql;
    }

    /*--------- Modelo obtener imagenes mapeadas del local ---------*
    public static function obtener_imagenes_local_modelo(){
        $sql = mainModel::conectar()->prepare("
            SELECT ip.nom_esp, i.ruta 
            FROM img_info_pagina ip 
            INNER JOIN imagen i ON ip.id_img_fk = i.id_imagen 
            WHERE ip.id_infop_fk = 1
        ");
        $sql->execute();
        return $sql;
    }

    /*--------- Modelo obtener ID de imagen vinculada ---------*
    public static function obtener_id_imagen_local_modelo($nom_esp){
        $sql = mainModel::conectar()->prepare("SELECT id_img_fk, i.ruta FROM img_info_pagina ip INNER JOIN imagen i ON ip.id_img_fk = i.id_imagen WHERE ip.id_infop_fk = 1 AND ip.nom_esp = :NomEsp");
        $sql->bindParam(":NomEsp", $nom_esp);
        $sql->execute();
        return $sql;
    }

    /*--------- Modelo vincular imagen al local ---------*
    public static function vincular_imagen_local_modelo($id_img, $nom_esp){
        $conexion = mainModel::conectar();
        
        $check = $conexion->prepare("SELECT id_img_infp FROM img_info_pagina WHERE id_infop_fk = 1 AND nom_esp = :NomEsp");
        $check->bindParam(":NomEsp", $nom_esp);
        $check->execute();
        
        if($check->rowCount() > 0){
            $update = $conexion->prepare("UPDATE img_info_pagina SET id_img_fk = :IdImg WHERE id_infop_fk = 1 AND nom_esp = :NomEsp");
            $update->bindParam(":IdImg", $id_img);
            $update->bindParam(":NomEsp", $nom_esp);
            $update->execute();
            return $update;
        } else {
            $insert = $conexion->prepare("INSERT INTO img_info_pagina (id_infop_fk, id_img_fk, nom_esp) VALUES (1, :IdImg, :NomEsp)");
            $insert->bindParam(":IdImg", $id_img);
            $insert->bindParam(":NomEsp", $nom_esp);
            $insert->execute();
            return $insert;
        }
    }

    /*--------- Modelo eliminar registro de imagen ---------*
    public static function eliminar_registro_imagen_modelo($id_img){
        $sql = mainModel::conectar()->prepare("DELETE FROM imagen WHERE id_imagen = :IdImg");
        $sql->bindParam(":IdImg", $id_img);
        $sql->execute();
        return $sql;
    }*/
}
?>