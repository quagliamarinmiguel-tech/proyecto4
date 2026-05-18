<?php

require_once "mainModel.php";

class publicoModelo extends mainModel {

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


/*
                SELECT 
                    nombre_profesor, 
                    apellido_profesor, 
                    descripcion, 
                    telefono_profesor, 
                    correo, 
                    ci_profesor 
                FROM profesor 
                WHERE profe_mostrar = 1 AND estado_profesor = 1
                ORDER BY nombre_profesor ASC
*/


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
/*
            $sql = "
                SELECT  
                    m.id_materia,  
                    m.prefacio,
                    m.id_imagen_materia_fk,
                    img.ruta,
                    c.id_curso,  
                    c.nombre_curso,  
                    c.tipo_curso,  
                    c.cupos,  
                    cv.id_curso_vigente,  
                    co.pago_inscripcion,  
                    co.pago_modulo,  
                    cv.oferta AS oferta_vigente,  
                    co.numero_cohorte,  
                    co.anio_cohorte,  
                    co.tipo_cohorte  
                FROM curso_publicidad m  
                LEFT JOIN curso c ON m.id_curso = c.id_curso  
                LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk  
                LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte  
                LEFT JOIN imagen img ON m.id_imagen_materia_fk = img.id_imagen

                WHERE
                    co.estado_cohorte = 'Vigente' AND
                    cv.estado_curso_vigente = 1 AND
                    p.estado_profesor = 1 AND
                    c.estado_curso = 1 AND
                    s.archivar_seccion = 1 AND
                    m.estado_curso_publicidad = 1 AND
                    s.nombre_seccion = 'A'

                LIMIT 9
            ";
*/
/*

            $sql="
            SELECT  
                m.id_materia,  
                m.prefacio,
                m.id_imagen_materia_fk,
                img.ruta,
                c.id_curso,  
                c.nombre_curso,  
                c.tipo_curso,  
                c.cupos,  
                cv.id_curso_vigente,  
                co.pago_inscripcion,  
                co.pago_modulo,  
                co.numero_cohorte,  
                co.anio_cohorte,  
                co.tipo_cohorte  

            FROM curso_publicidad m  
            LEFT JOIN curso c ON m.id_curso = c.id_curso  
            LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk  
            LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte  
            LEFT JOIN imagen img ON m.id_imagen_materia_fk = img.id_imagen
            LEFT JOIN seccion s ON cv.id_curso_vigente = s.id_curso_vigente_fk AND s.nombre_seccion = 'A' AND s.archivar_seccion = 1
            LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor AND p.estado_profesor = 1

            WHERE
                co.estado_cohorte = 'Vigente' AND
                cv.estado_curso_vigente = 1 AND
                c.estado_curso = 1 AND
                m.estado_curso_publicidad = 1 AND
                s.nombre_seccion = 'A' AND
                s.archivar_seccion = 1 AND
                p.estado_profesor = 1

            LIMIT 9";

*/
            
            
            
            
            
            
            
            
            
            
            
            
            
            /* tiene un error q no se
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
         OR prox_co.id_curso_fk IS NOT NULL
         OR prox_pc.id_curso_fk IS NOT NULL)

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
LIMIT 9";
            */

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


}//fin clase