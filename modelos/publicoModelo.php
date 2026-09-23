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
        //var_dump($ip);die();
        //return SupabaseApi::rpcWithoutToken('obtener_datos_inicio', []);
                
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
    
    
    
    
    
    
    
    
    
    
    /*----------------------------------------------------------------------------------------------*
    
    

    
  
    
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
    }*/
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /*
    
    // Registrar una vista pública
    protected static function registrar_vista_modelo($datos){
        $sql = mainModel::conectar()->prepare("INSERT INTO vistas(ip, origen, fecha) VALUES(:IP, :Origen, NOW())");
        $sql->bindParam(":IP", $datos['IP']);
        $sql->bindParam(":Origen", $datos['Origen']);
        $sql->execute();
        return $sql;
    }

    // Registrar un click
    protected static function registrar_click_modelo($datos){
        $sql = mainModel::conectar()->prepare("INSERT INTO clicks(ip, accion, fecha) VALUES(:IP, :Accion, NOW())");
        $sql->bindParam(":IP", $datos['IP']);
        $sql->bindParam(":Accion", $datos['Accion']);
        $sql->execute();
        return $sql;
    }



    public static function obtener_profesores_modelo() {
        try {
            $conexion = mainModel::conectar();
            $sql = "
            SELECT 
                p.nombre_profesor,
                p.apellido_profesor,
                p.descripcion,
                p.telefono_profesor,
                p.correo,
                p.ci_profesor,
                i.ruta AS imagen_profesor
            FROM profesor p
            LEFT JOIN imagen i ON p.imagen_profesor_fk = i.id_imagen
            WHERE p.profe_mostrar = 1 AND p.estado_profesor = 1
            ORDER BY p.nombre_profesor ASC;
            ";
            $consulta = $conexion->prepare($sql);
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log(" Error al obtener profesores: " . $e->getMessage());
            return [];
        }
    }



    public static function obtener_cursos_extendidos_modelo() {
        try {
            $conexion = mainModel::conectar();
            $sql = "
                SELECT 
                    m.id_materia,
                    m.prefacio,
                    m.materia_descripcion,
                    m.cont1, m.cont2, m.cont3, m.cont4, m.cont5,
                    m.cont6, m.cont7, m.cont8, m.cont9, m.cont10,
                    m.requisito1, m.requisito2, m.requisito3, m.requisito4,
                    m.icono1, m.icono2, m.icono3,
                    m.clase,
                    c.id_curso,
                    c.nombre_curso,
                    c.tipo_curso,
                    c.cupos,
                    cv.id_curso_vigente,
                    co.pago_inscripcion,
                    co.pago_modulo,
                    cv.oferta AS oferta_vigente,
                    co.id_cohorte,
                    co.numero_cohorte,
                    co.anio_cohorte,
                    co.tipo_cohorte,
                    s.ci_profesor,
                    p.nombre_profesor,
                    p.apellido_profesor,
                    p.descripcion
                FROM curso_publicidad m
                LEFT JOIN curso c ON m.id_curso = c.id_curso
                LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
                LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
                LEFT JOIN seccion s ON cv.id_curso_vigente = s.id_curso_vigente_fk
                LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor

                WHERE
                    co.estado_cohorte = 'Vigente' AND
                    cv.estado_curso_vigente = 1 AND
                    p.estado_profesor = 1 AND
                    c.estado_curso = 1 AND
                    s.archivar_seccion = 1 AND
                    m.estado_curso_publicidad = 1 AND
                    s.nombre_seccion = 'A'

                GROUP BY m.id_materia
                LIMIT 20
            ";

            $consulta = $conexion->prepare($sql);
            $consulta->execute();
            return $consulta;
        } catch (PDOException $e) {
            error_log(" Error al obtener cursos extendidos: " . $e->getMessage());
            return false;
        }
    }

  


    public static function obtener_info_ceproca_modelo() {
        try {
            $conexion = mainModel::conectar();
            $sql = "
                SELECT 
                    i.id_informacion_pagina,
                    i.telefono_contacto,
                    i.correo_contacto,
                    i.link_facebook,
                    i.link_instagram,
                    i.direccion_local_ciudad,
                    i.direccion_local_pais,
                    i.imagen_local,
                    i.imagen_local_mapa,
                    i.imagen_local_satelite,
                    img1.ruta AS ruta_local,
                    img2.ruta AS ruta_mapa,
                    img3.ruta AS ruta_satelite
                FROM informacion_pagina i
                LEFT JOIN imagen img1 ON i.imagen_local = img1.id_imagen
                LEFT JOIN imagen img2 ON i.imagen_local_mapa = img2.id_imagen
                LEFT JOIN imagen img3 ON i.imagen_local_satelite = img3.id_imagen
                WHERE i.id_informacion_pagina = 1
                LIMIT 1
            ";

            $consulta = $conexion->prepare($sql);
            $consulta->execute();
            return $consulta;

        } catch (PDOException $e) {
            error_log(" Error al obtener datos de informacion_pagina: " . $e->getMessage());
            return false;
        }
    }






public static function obtener_imagenes_slider_modelo() {
    try {
        $conexion = mainModel::conectar();
        $sql = "
            SELECT 
                s.id_slider_fk,
                i.ruta 
            FROM imagen_slider s
            INNER JOIN imagen i ON s.id_imagen_fk = i.id_imagen
            WHERE s.id_slider_fk IN (1, 2, 3)
            ORDER BY s.id_slider_fk ASC
        ";
        $consulta = $conexion->prepare($sql);
        $consulta->execute();

        $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $sliders = [
            'slider_1' => [],
            'slider_2' => [],
            'slider_3' => []
        ];

        foreach ($resultado as $img) {
            switch ((int)$img['id_slider_fk']) {
                case 1: $sliders['slider_1'][] = $img['ruta']; break;
                case 2: $sliders['slider_2'][] = $img['ruta']; break;
                case 3: $sliders['slider_3'][] = $img['ruta']; break;
            }
        }

        return $sliders;

    } catch (PDOException $e) {
        error_log(" Error al obtener imágenes de sliders: " . $e->getMessage());
        return [
            'slider_1' => [],
            'slider_2' => [],
            'slider_3' => []
        ];
    }
}






    public static function obtener_cursos_publicitarios_modelo() {
        try {
            $conexion = mainModel::conectar();
            if (!$conexion) {
                throw new Exception("No se pudo conectar a la base de datos.");
            }

            $sql="
            
SELECT  
    m.id_materia,
    m.prefacio,
    im_m.ruta AS ruta_materia,
    c.id_curso,
    c.nombre_curso,
    c.tipo_curso,
    
    -- CV principal (el que se usará)
    cv.id_curso_vigente,
    
    -- Datos del cohorte o periodo intensivo (priorizando Vigente)
    COALESCE(vig_co.pago_inscripcion, vig_pc.pago_inscripcion_int, prox_co.pago_inscripcion, prox_pc.pago_inscripcion_int, 0) AS pago_inscripcion,
    
    -- Determinar estado actual
    CASE 
        WHEN vig_co.id_cohorte IS NOT NULL OR vig_pc.id_periodo_intensivo IS NOT NULL THEN 'Vigente'
        WHEN prox_co.id_cohorte IS NOT NULL OR prox_pc.id_periodo_intensivo IS NOT NULL THEN 'Próximo'
        ELSE 'N/A'
    END AS estado_actual

FROM curso_publicidad m
LEFT JOIN imagen im_m ON m.id_imagen_materia_fk = im_m.id_imagen
LEFT JOIN curso c ON m.id_curso = c.id_curso
LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk

-- Sección y Profesor
LEFT JOIN seccion s ON cv.id_curso_vigente = s.id_curso_vigente_fk AND s.nombre_seccion = 'A' AND s.archivar_seccion = 1
LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor AND p.estado_profesor = 1

-- ========== VIGENTES ==========
-- Cohortes VIGENTES (del mismo CV)
LEFT JOIN cohorte vig_co ON cv.id_cohorte_fk = vig_co.id_cohorte AND vig_co.estado_cohorte = 'Vigente'

-- Periodos Intensivos VIGENTES (del mismo CV)
LEFT JOIN periodo_intensivo vig_pc ON cv.id_curso_vigente = vig_pc.id_curso_vigente_fk AND vig_pc.estado_periodo = 'Vigente'

-- ========== PRÓXIMOS ==========
-- Cohortes PRÓXIMOS (más cercano por curso)
LEFT JOIN (
    SELECT 
        cvp.id_curso_fk,
        cop.id_cohorte,  -- ← AGREGADO
        cop.pago_inscripcion,
        cop.fecha_inicio,
        ABS(DATEDIFF(CURDATE(), cop.fecha_inicio)) as dias_diferencia
    FROM curso_vigente cvp
    INNER JOIN cohorte cop ON cvp.id_cohorte_fk = cop.id_cohorte
    WHERE cop.estado_cohorte = 'Próximo'
    ORDER BY dias_diferencia ASC
) prox_co ON prox_co.id_curso_fk = c.id_curso

-- Periodos Intensivos PRÓXIMOS (más cercano por curso)
LEFT JOIN (
    SELECT 
        pcv.id_curso_fk,
        pcp.id_periodo_intensivo,  -- ← AGREGADO
        pcp.pago_inscripcion_int,
        pcp.fecha_inicio_int,
        ABS(DATEDIFF(CURDATE(), pcp.fecha_inicio_int)) as dias_diferencia
    FROM periodo_intensivo pcp
    INNER JOIN curso_vigente pcv ON pcp.id_curso_vigente_fk = pcv.id_curso_vigente
    WHERE pcp.estado_periodo = 'Próximo'
    ORDER BY dias_diferencia ASC
) prox_pc ON prox_pc.id_curso_fk = c.id_curso

WHERE 
    cv.estado_curso_vigente = 1 
    AND p.estado_profesor = 1
    AND c.estado_curso = 1
    AND s.archivar_seccion = 1
    AND m.estado_curso_publicidad = 1
    AND s.nombre_seccion = 'A'
    AND (vig_co.id_cohorte IS NOT NULL 
         OR vig_pc.id_periodo_intensivo IS NOT NULL
         OR prox_co.id_cohorte IS NOT NULL
         OR prox_pc.id_periodo_intensivo IS NOT NULL)

GROUP BY c.id_curso
ORDER BY 
    -- Priorizar Vigentes primero
    CASE 
        WHEN vig_co.id_cohorte IS NOT NULL OR vig_pc.id_periodo_intensivo IS NOT NULL THEN 1
        ELSE 2
    END ASC,
    -- Luego ordenar por proximidad de fecha (solo para Próximos)
    CASE 
        WHEN vig_co.id_cohorte IS NOT NULL OR vig_pc.id_periodo_intensivo IS NOT NULL THEN 0
        ELSE COALESCE(prox_co.dias_diferencia, prox_pc.dias_diferencia, 9999)
    END ASC,
    c.nombre_curso ASC
LIMIT 9            
            ";
            
            
            
            
            
            $consulta = $conexion->prepare($sql);
            if (!$consulta) {
                throw new Exception("No se pudo preparar la consulta.");
            }

            $consulta->execute();
            return $consulta;

        } catch (PDOException $e) {
            error_log(" Error PDO al obtener cursos publicitarios: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log(" Error general: " . $e->getMessage());
            return false;
        }
    }
*/

}//fin clase