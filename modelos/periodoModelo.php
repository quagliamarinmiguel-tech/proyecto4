<?php

    require_once "mainModel.php";

    class periodoModelo extends mainModel{




//        obtener_evaluaciones_modelo    estos son los que nos competen, estos traen todo lo que necesitamos
  //      obtener_estudiantes_modelo
    //    obtener_puntajes_modelo








public static function obtener_evaluaciones_modelo($id_seccion){
    $sql = mainModel::conectar()->prepare("
        SELECT id_evaluacion, tipo_evaluacion, obj, porcentaje, fecha_evaluacion 
        FROM evaluacion 
        WHERE id_seccion = :id AND archivar_evaluacion = 1 
        ORDER BY obj ASC
    ");
    $sql->bindParam(":id", $id_seccion);
    $sql->execute();
    return $sql;
}

        
//tambien del paginador de estudiantes y sus puntajes
protected static function obtener_estudiantes_modelo($id_seccion, $inicio, $registros){
    $id_seccion = mainModel::limpiar_cadena($id_seccion);
    $inicio = (int) $inicio;
    $registros = (int) $registros;
    $limit="";
    if($inicio >= 0 && $registros > 0){
        $limit = "LIMIT :inicio, :registros";
    }

    //LIMIT :inicio, :registros
    $sql = mainModel::conectar()->prepare("
        SELECT 
            est.id_estudiante,
            est.ci_estudiante,
            est.nombre,
            est.apellido,
            i.id_inscripcion
        FROM inscripcion i
        INNER JOIN estudiante est ON i.id_estudiante_fk = est.id_estudiante
        INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
        WHERE 
            i.id_seccion_fk = :id_seccion
            AND i.archivada = 1
            AND est.estado_estudiante = 1
            AND s.archivar_seccion = 1
        ORDER BY est.apellido ASC, est.nombre ASC
        $limit
    ");

    $sql->bindParam(":id_seccion", $id_seccion, PDO::PARAM_INT);
    if($inicio >= 0 && $registros > 0){
        $sql->bindParam(":inicio", $inicio, PDO::PARAM_INT);
        $sql->bindParam(":registros", $registros, PDO::PARAM_INT);
    }
    $sql->execute();

    return $sql;
}
//paginador de estudiantes y puntos tambien
protected static function obtener_puntajes_modelo($id_inscripcion){
    $id_inscripcion = mainModel::limpiar_cadena($id_inscripcion);

    $sql = mainModel::conectar()->prepare("
        SELECT 
            pe.id_evaluacion,
            pe.puntaje
        FROM puntaje_estudiante pe
        INNER JOIN evaluacion ev ON pe.id_evaluacion = ev.id_evaluacion
        INNER JOIN inscripcion i ON pe.id_inscripcion = i.id_inscripcion
        INNER JOIN estudiante est ON i.id_estudiante_fk = est.id_estudiante
        INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
        WHERE 
            pe.id_inscripcion = :id_inscripcion
            AND pe.archivar_puntaje = 1
            AND ev.archivar_evaluacion = 1
            AND i.archivada = 1
            AND est.estado_estudiante = 1
            AND s.archivar_seccion = 1
    ");

    $sql->bindParam(":id_inscripcion", $id_inscripcion, PDO::PARAM_INT);
    $sql->execute();

    return $sql;
}


 
public function obtener_datos_seccion_para_ambos_casos_modelo($curso_vigente_id, $seccion_letra) {
    $pdo = mainModel::conectar();

    // Obtener sección, curso vigente, duración y luego determinar si es cohorte o intensivo
    $sql = $pdo->prepare("
        SELECT 
            s.id_seccion,
            s.dia,
            cv.id_curso_vigente,
            cv.id_duracion_fk,
            c.nombre_curso,
            dp.fecha_inicio,dp.fecha_fin,dp.estado_periodo,
            p.id_profesor,
            p.nombre_profesor,
            p.apellido_profesor,

            -- para evaluacion new y update
            (SELECT COUNT(*) FROM evaluacion e WHERE e.id_seccion = s.id_seccion AND e.archivar_evaluacion = 1) AS total_evaluaciones,
            (SELECT SUM(porcentaje) FROM evaluacion e WHERE e.id_seccion = s.id_seccion AND e.archivar_evaluacion = 1) AS total_porcentaje
        
            FROM seccion s
        INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        INNER JOIN duracion_periodo dp ON cv.id_duracion_fk = dp.id_duracion
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
        WHERE s.id_curso_vigente_fk = :curso 
          AND s.nombre_seccion = :seccion 
          AND s.archivar_seccion = 1
        LIMIT 1
    ");
    $sql->bindParam(":curso", $curso_vigente_id);
    $sql->bindParam(":seccion", $seccion_letra);
    $sql->execute();

    if ($sql->rowCount() === 0) {
        return $sql; // sin resultados
    }

    $datos = $sql->fetch(PDO::FETCH_ASSOC);
    $idDuracion = $datos['id_duracion_fk'];

    // Buscar si esta duración pertenece a una cohorte
    $sqlCohorte = $pdo->prepare("
        SELECT numero_cohorte, anio_cohorte
        FROM cohorte
        WHERE id_duracion_fk = :id_duracion
        LIMIT 1
    ");
    $sqlCohorte->bindParam(":id_duracion", $idDuracion);
    $sqlCohorte->execute();
    $cohorte = $sqlCohorte->fetch(PDO::FETCH_ASSOC);

    if ($cohorte) {
        $datos['numero_cohorte'] = $cohorte['numero_cohorte'];
        $datos['anio_cohorte'] = $cohorte['anio_cohorte'];
        $datos['fecha_inicio_int'] = null;
        $datos['fecha_fin_int'] = null;
    } else {
        // Si no es cohorte, buscar en periodo_intensivo
        $sqlIntensivo = $pdo->prepare("
            SELECT pago_inscripcion_int
            FROM periodo_intensivo
            WHERE id_duracion_fk = :id_duracion
            LIMIT 1
        ");
        $sqlIntensivo->bindParam(":id_duracion", $idDuracion);
        $sqlIntensivo->execute();
        $intensivo = $sqlIntensivo->fetch(PDO::FETCH_ASSOC);

        $datos['numero_cohorte'] = null;
        $datos['anio_cohorte'] = null;
        $datos['fecha_inicio'] = $datos['fecha_inicio'] ?? null;
        $datos['fecha_fin'] = $datos['fecha_fin'] ?? null;
    }

    return $datos;
}












protected function actualizar_profesor_seccion_modelo($idSeccion, $idProfesor) {
    $sql = mainModel::conectar()->prepare("
        UPDATE seccion 
        SET ci_profesor = :idProf 
        WHERE id_seccion = :idSec
    ");
    $sql->bindParam(':idProf', $idProfesor, PDO::PARAM_INT);
    $sql->bindParam(':idSec', $idSeccion, PDO::PARAM_INT);
    return $sql;
}
































/*
//evaluacion new, busca a los estudiantes inscritos en la seccion para ponerles nota
public static function obtener_estudiantes_inscritos_por_seccion_modelo($id_seccion) {
    $consulta = mainModel::conectar()->prepare("
        SELECT i.id_inscripcion, e.ci_estudiante, e.nombre, e.apellido
        FROM inscripcion i
        INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
        WHERE i.id_seccion_fk = :id AND i.archivada = 1
        ORDER BY e.apellido ASC
    ");
    $consulta->bindParam(":id", $id_seccion);
    $consulta->execute();
    return $consulta->fetchAll();
}
*/


//-----------------------------------------------------------------------------------------
//para el conteo de evaluaciones >6 de seccion consult
 
protected static function contar_evaluaciones_modelo($id_seccion) {
            $sql = mainModel::conectar()->prepare("
                SELECT COUNT(*) FROM evaluacion
                WHERE id_seccion = :id_seccion AND archivar_evaluacion=1;
            ");
            $sql->execute([':id_seccion' => $id_seccion]);
            return (int)$sql->fetchColumn();
        }
        
  //------------------------------------------------------------------------------------



















//para los datos de las calificaciones de los estudiantes en la paginacion de calificaciones y pagos osea seccion consult
/*
public function obtener_evaluaciones_sin_limite_modelo($id_seccion){
    $id_seccion = mainModel::limpiar_cadena($id_seccion);

    $sql = mainModel::conectar()->prepare("
       SELECT 
            id_evaluacion,
            tipo_evaluacion,
            porcentaje,
            obj,
            fecha_evaluacion
        FROM evaluacion
        WHERE id_seccion  = :id_seccion
          AND archivar_evaluacion = 1
        ORDER BY fecha_evaluacion ASC
    ");

    $sql->bindParam(":id_seccion", $id_seccion, PDO::PARAM_INT);
    $sql->execute();
    return $sql;
}
*/
        

        //contar estudiantes en el paginador de calificaciones de seccion consult
        //tengo q averigua pa q mierda me sirve este--------------------------------
        protected static function contar_estudiantes_modelo($id_seccion){
    $id_seccion = mainModel::limpiar_cadena($id_seccion);

    $sql = mainModel::conectar()->prepare("
        SELECT COUNT(*) FROM inscripcion i
        INNER JOIN estudiante est ON i.id_estudiante_fk = est.id_estudiante
        INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
        WHERE 
            i.id_seccion_fk = :id_seccion
            AND i.archivada = 1
            AND est.estado_estudiante = 1
            AND s.archivar_seccion = 1
    ");
    $sql->bindParam(":id_seccion", $id_seccion, PDO::PARAM_INT);
    $sql->execute();

    return (int) $sql->fetchColumn();
}




//estudiantes inscritos en la seccion
/*consulta q no excluye archivados

                SELECT e.id_estudiante, e.ci_estudiante, e.nombre, e.apellido, i.id_inscripcion
                FROM inscripcion i
                INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
                WHERE i.id_seccion_fk = :id
                LIMIT :inicio, :registros
*/

/*
        public function obtener_estudiantes_modelo($id_seccion, $inicio, $registros){
SELECT 
    est.id_estudiante,
    est.ci_estudiante,
    est.nombre,
    est.apellido,
    i.id_inscripcion,
    ev.id_evaluacion,
    ev.tipo_evaluacion,
    ev.fecha_evaluacion,
    pe.puntaje
FROM inscripcion i
INNER JOIN estudiante est ON i.id_estudiante_fk = est.id_estudiante
INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
INNER JOIN evaluacion ev ON ev.id_seccion_fk = s.id_seccion
INNER JOIN puntaje_estudiante pe 
    ON pe.id_inscripcion = i.id_inscripcion 
    AND pe.id_evaluacion = ev.id_evaluacion
WHERE 
    i.id_seccion_fk = :id_seccion
    AND i.archivada = 1
    AND est.estado_estudiante = 1
    AND s.archivar_seccion = 1
    AND ev.archivar_evaluacion = 1
    AND pe.archivado = 1
ORDER BY est.apellido ASC, est.nombre ASC, ev.fecha_evaluacion ASC
            $sql->bindParam(":id", $id_seccion);
            $sql->bindParam(":inicio", $inicio, PDO::PARAM_INT);
            $sql->bindParam(":registros", $registros, PDO::PARAM_INT);
            $sql->execute();
            return $sql;
        }
        */

//obtener_estudiantes_con_puntajes_modelo
protected static function obtener_estudiantes_con_puntajes_modelo($id_seccion){
    $id_seccion = mainModel::limpiar_cadena($id_seccion);

    try {
        $pdo = mainModel::conectar();

        $sql = $pdo->prepare("
SELECT 
    est.id_estudiante,
    est.ci_estudiante,
    est.nombre,
    est.apellido,
    i.id_inscripcion,
    ev.id_evaluacion,
    ev.tipo_evaluacion,
    ev.fecha_evaluacion,
    ev.porcentaje,
    ev.obj,
    pe.puntaje
FROM inscripcion i
INNER JOIN estudiante est ON i.id_estudiante_fk = est.id_estudiante
INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
INNER JOIN evaluacion ev ON ev.id_seccion = s.id_seccion
INNER JOIN puntaje_estudiante pe 
    ON pe.id_inscripcion = i.id_inscripcion 
    AND pe.id_evaluacion = ev.id_evaluacion
WHERE 
    i.id_seccion_fk = :id_seccion
    AND i.archivada = 1
    AND est.estado_estudiante = 1
    AND s.archivar_seccion = 1
    AND ev.archivar_evaluacion = 1
    AND pe.archivar_puntaje = 1
ORDER BY est.apellido ASC, est.nombre ASC, ev.fecha_evaluacion ASC
        ");

        $sql->bindParam(":id_seccion", $id_seccion, PDO::PARAM_INT);
        $sql->execute();

        return $sql->fetchAll();
    } catch (Exception $e) {
        error_log("Error al obtener estudiantes con puntajes: " . $e->getMessage());
        return [];
    }
}


//puntajes de los estudiantes(no se donde se usa esto)
/*
        public function obtener_puntajes_modelo($id_inscripcion){
            $sql = mainModel::conectar()->prepare("
                SELECT id_evaluacion, puntaje
                FROM puntaje_estudiante
                WHERE id_inscripcion = :id
            ");
            $sql->bindParam(":id", $id_inscripcion);
            $sql->execute();
            return $sql;
        }


*/

//datos de la seccion para seccion-list
/*
        public function obtener_datos_seccion_modelo($curso_vigente_id, $seccion_letra){
            $sql = mainModel::conectar()->prepare("
        SELECT 
            s.id_seccion, 
            cv.id_curso_vigente,
            c.nombre_curso, 
            ch.numero_cohorte, 
            ch.anio_cohorte,
            p.id_profesor,
            p.nombre_profesor,
            p.apellido_profesor
        FROM seccion s
        INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        INNER JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
        INNER JOIN profesor p ON s.ci_profesor = p.id_profesor
        WHERE s.id_curso_vigente_fk = :curso AND s.nombre_seccion = :seccion
        LIMIT 1

            ");
            $sql->bindParam(":curso", $curso_vigente_id);
            $sql->bindParam(":seccion", $seccion_letra);
            $sql->execute();
            return $sql;
        }

*/


public function obtener_datos_seccion_modelo($curso_vigente_id, $seccion_letra){
    $sql = mainModel::conectar()->prepare("
        SELECT 
            s.id_seccion, 
            cv.id_curso_vigente,
            c.nombre_curso, 
            ch.numero_cohorte, 
            ch.anio_cohorte,
            p.id_profesor,
            p.nombre_profesor,
            p.apellido_profesor
        FROM seccion s
        INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        INNER JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
        LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
        WHERE s.id_curso_vigente_fk = :curso AND s.nombre_seccion = :seccion AND archivar_seccion = 1
        LIMIT 1
    ");
    $sql->bindParam(":curso", $curso_vigente_id);
    $sql->bindParam(":seccion", $seccion_letra);
    $sql->execute();
    return $sql;
}

        
        
   

/* no trae id de curso vigente
                SELECT 
                    s.id_seccion, 
                    c.nombre_curso, 
                    ch.numero_cohorte, 
                    ch.anio_cohorte,
                    p.id_profesor,
                    p.nombre_profesor,
                    p.apellido_profesor
                FROM seccion s
                INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
                INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
                INNER JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
                INNER JOIN profesor p ON s.ci_profesor = p.id_profesor
                WHERE s.id_curso_vigente_fk = :curso AND s.nombre_seccion = :seccion
                LIMIT 1

*/


        //datos de profesor de seccion list
        public static function obtener_profesores_activos_modelo(){
            $sql = mainModel::conectar()->prepare("
                SELECT id_profesor, nombre_profesor, apellido_profesor 
                FROM profesor 
                WHERE estado_profesor = 1
                ORDER BY nombre_profesor ASC
            ");
            $sql->execute();
            return $sql;
        }





    protected static function agregar_seccion_modelo($datos){
        $sql = mainModel::conectar()->prepare("
            INSERT INTO seccion(nombre_seccion, id_curso_vigente_fk, ci_profesor, dia, archivar_seccion)
            VALUES(:Nombre, :CursoVigente, :Profesor, :Dia, 1)
        ");

        $sql->bindParam(":Nombre", $datos['Nombre']);
        $sql->bindParam(":CursoVigente", $datos['CursoVigente']);
        $sql->bindParam(":Profesor", $datos['Profesor']);
        $sql->bindParam(":Dia", $datos['Dia']);

        $sql->execute();
        return $sql;
    }

    protected static function actualizar_seccion_modelo($datos){
        $sql = mainModel::conectar()->prepare("
            UPDATE seccion SET id_profesor_fk = :Profesor WHERE id_seccion = :ID
        ");
        $sql->bindParam(":Profesor", $datos['Profesor']);
        $sql->bindParam(":ID", $datos['ID']);
        $sql->execute();
        return $sql;
    }













//                $evaluaciones = seccionModelo::obtener_evaluaciones_sin_limite_modelo($id_seccion)->fetchAll();
             //   $estudiantes = seccionModelo::obtener_estudiantes_modelo($id_seccion, $inicio, $registros)->fetchAll();


    }
?>
