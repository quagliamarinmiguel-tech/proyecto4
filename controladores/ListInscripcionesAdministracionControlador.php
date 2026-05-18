<?php
require_once __DIR__ . '/../modelos/mainModel.php';

class ListInscripcionesControlador extends mainModel {

    public function listar_inscripciones_completas() {
        $conexion = mainModel::conectar();
        $consulta = $conexion->prepare("
            SELECT 
                i.id_inscripcion,
                i.fecha_inscripcion,
                i.if_fecha_abandono,
                i.estado_inscripcion,
                i.nota_final,
                i.observaciones,
                i.pago_faltante,

                e.ci_estudiante,
                e.nombre AS nombre_estudiante,
                e.apellido AS apellido_estudiante,

                s.nombre_seccion,

                c.nombre_curso,
                c.tipo_curso,

                COALESCE(co.numero_cohorte, 'Intensivo') AS numero_cohorte,
                COALESCE(co.anio_cohorte, YEAR(pi.fecha_inicio_int)) AS anio_cohorte,
                COALESCE(co.tipo_cohorte, 'Intensivo') AS tipo_cohorte,

                -- Información del período intensivo
                pi.fecha_inicio_int,
                pi.fecha_fin_int,
                pi.estado_periodo

            FROM inscripcion i
            INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
            INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
            INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
            INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
            
            -- LEFT JOIN para cohorte (cursos regulares)
            LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte AND co.archivar_cohorte = 1
            
            -- LEFT JOIN para periodo_intensivo (cursos intensivos)
            LEFT JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk
            
            WHERE i.archivada = 1  -- SOLO inscripciones NO archivadas (activas)
            AND e.estado_estudiante = 1  -- SOLO estudiantes activos
            AND s.archivar_seccion = 1  -- SOLO secciones NO archivadas
            AND cv.estado_curso_vigente = 1  -- SOLO cursos vigentes activos
            AND c.estado_curso = 1  -- SOLO cursos activos
            
            -- Incluir tanto cursos con cohorte como cursos intensivos
            AND (co.id_cohorte IS NOT NULL OR pi.id_periodo_intensivo IS NOT NULL)
            
            ORDER BY i.fecha_inscripcion DESC
        ");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listar_inscripciones_archivadas($inicio = 0, $por_pagina = 5) {
        $conexion = mainModel::conectar();
        $consulta = $conexion->prepare("
            SELECT 
                i.id_inscripcion,
                i.fecha_inscripcion,
                i.if_fecha_abandono,
                i.estado_inscripcion,
                i.nota_final,
                i.observaciones,
                i.pago_faltante,
                e.ci_estudiante,
                e.nombre AS nombre_estudiante,
                e.apellido AS apellido_estudiante,
                s.nombre_seccion,
                c.nombre_curso,
                c.tipo_curso,
                COALESCE(co.numero_cohorte, 'Intensivo') AS numero_cohorte,
                COALESCE(co.anio_cohorte, YEAR(pi.fecha_inicio_int)) AS anio_cohorte,
                COALESCE(co.tipo_cohorte, 'Intensivo') AS tipo_cohorte,
                pi.fecha_inicio_int,
                pi.fecha_fin_int
            FROM inscripcion i
            INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
            INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
            INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
            INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
            LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
            LEFT JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk
            WHERE i.archivada = 0  -- Inscripciones archivadas
            ORDER BY i.fecha_inscripcion DESC
            LIMIT :limite OFFSET :inicio
        ");

        $consulta->bindValue(':limite', $por_pagina, PDO::PARAM_INT);
        $consulta->bindValue(':inicio', $inicio, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function contar_inscripciones_activas() {
        $conexion = mainModel::conectar();
        $consulta = $conexion->prepare("
            SELECT COUNT(*) 
            FROM inscripcion i
            INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
            INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
            INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
            INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
            LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte AND co.archivar_cohorte = 1
            LEFT JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk
            
            WHERE i.archivada = 1  -- SOLO inscripciones NO archivadas (activas)
            AND e.estado_estudiante = 1
            AND s.archivar_seccion = 1
            AND cv.estado_curso_vigente = 1
            AND c.estado_curso = 1
            AND (co.id_cohorte IS NOT NULL OR pi.id_periodo_intensivo IS NOT NULL)
        ");
        $consulta->execute();
        return $consulta->fetchColumn();
    }

    public function contar_inscripciones_archivadas() {
        $conexion = mainModel::conectar();
        $consulta = $conexion->prepare("
            SELECT COUNT(*) 
            FROM inscripcion 
            WHERE archivada = 0  -- SOLO inscripciones archivadas (inactivas)
        ");
        $consulta->execute();
        return $consulta->fetchColumn();
    }
}
?>