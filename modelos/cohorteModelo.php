<?php

require_once "mainModel.php";

class cohorteModelo extends mainModel {

    
    /*---------------validar cosas------------------*/
    
    
    

//---------------------------validaciones------------------------------
    
    // 1. Validar Duplicados (Número, Año y Nombre/Tipo)
    public static function validar_cohorte_no_duplicado_modelo($numero, $anio, $tipo, $alterno, $id=null){
        $numero = self::limpiar_cadena($numero);
        $anio = self::limpiar_cadena($anio);
        $tipo = self::limpiar_cadena($tipo);
     //   $alterno = self::limpiar_cadena($alterno);
		$alterno = self::limpiar_cadena($alterno ?? "");
        // JOIN: cohorte (C) tiene la FK id_duracion_fk que apunta a duracion_periodo (D)
        $consulta = "SELECT C.id_cohorte FROM cohorte C 
                     INNER JOIN duracion_periodo D ON C.id_duracion_fk = D.id_duracion 
                     WHERE C.numero_cohorte = '$numero' AND C.anio_cohorte = '$anio' 
                     AND D.archivar_periodo != 0";

        if($tipo === "Otro"){
            $consulta .= " AND C.tipo_cohorte = 'Otro' AND C.cohorte_alterno = '$alterno'";
        } else {
            $consulta .= " AND C.tipo_cohorte = '$tipo'";
        }

        if($id !== null){
           // $idDec = self::decryption($id);
         //   $consulta .= " AND C.id_cohorte != '$idDec'";
            $id = self::limpiar_cadena($id);
            $consulta .= " AND C.id_cohorte != '$id'";
        }

        return (parent::ejecutar_consulta_simple($consulta)->rowCount() > 0);
    }

    // 2. Validar Solapamiento
    protected static function validar_fecha_no_solapada_modelo($tipo, $fechaInicio, $fechaFin, $alterno, $excluir = null) {
        $tipo = self::limpiar_cadena($tipo);
        $fechaInicio = self::limpiar_cadena($fechaInicio);
        $fechaFin = self::limpiar_cadena($fechaFin);
       // $alterno = self::limpiar_cadena($alterno);
		$alterno = self::limpiar_cadena($alterno ?? "");
        
        $consulta = "
            SELECT C.numero_cohorte, C.tipo_cohorte, C.cohorte_alterno, D.fecha_inicio, D.fecha_fin
            FROM cohorte C
            INNER JOIN duracion_periodo D ON C.id_duracion_fk = D.id_duracion
            WHERE D.archivar_periodo != 0
            AND (
                ('$fechaInicio' BETWEEN D.fecha_inicio AND D.fecha_fin) OR
                ('$fechaFin' BETWEEN D.fecha_inicio AND D.fecha_fin) OR
                (D.fecha_inicio BETWEEN '$fechaInicio' AND '$fechaFin') OR
                (D.fecha_fin BETWEEN '$fechaInicio' AND '$fechaFin')
            )
        ";

        if($tipo === "Otro"){
            $consulta .= " AND C.tipo_cohorte = 'Otro' AND C.cohorte_alterno = '$alterno'";
        } else {
            $consulta .= " AND C.tipo_cohorte = '$tipo'";
        }

        if ($excluir) {
       //     $idExcluir = self::decryption($excluir);
         //   $consulta .= " AND C.id_cohorte != '$idExcluir'";
             $excluir = self::limpiar_cadena($excluir);
            $consulta .= " AND C.id_cohorte != '$excluir'";
        }

        return parent::ejecutar_consulta_simple($consulta);
    }

    // 3. Traer último (Basado en la fecha de inicio de la tabla duracion)
    public static function validar_traer_ultimo_cohorte_modelo($tipo, $id=null){
        $tipo = self::limpiar_cadena($tipo);

        $consulta = "
            SELECT C.numero_cohorte, C.anio_cohorte, D.fecha_inicio, D.fecha_fin
            FROM cohorte C
            INNER JOIN duracion_periodo D ON C.id_duracion_fk = D.id_duracion
            WHERE C.tipo_cohorte = '$tipo' AND D.archivar_periodo != 0
        ";

        if($id){
          //  $idDec = self::decryption($id);
            //$consulta .= " AND C.id_cohorte != '$idDec'";
            $id = self::limpiar_cadena($id);
            $consulta .= " AND C.id_cohorte != '$id'";
        }

        $consulta .= " ORDER BY D.fecha_inicio DESC LIMIT 1";
        return parent::ejecutar_consulta_simple($consulta);
    }
    
    /*   esta vaina es pa los casos q no tienen cohorte alterno
    //validar cohortes no duplicados
    public  static function validar_cohorte_no_duplicado_modelo($numero, $anio, $tipo, $id=null){
        $numero = self::limpiar_cadena($numero);
        $anio = self::limpiar_cadena($anio);
        $tipo = self::limpiar_cadena($tipo);
        $consulta="SELECT id_cohorte FROM cohorte WHERE numero_cohorte = '$numero' AND anio_cohorte = '$anio' AND tipo_cohorte = '$tipo' AND archivar_cohorte != 0";
        
        if($id !== null){
            $id = self::limpiar_cadena($id);
            $consulta.=" AND id_cohorte != '$id'";
        }
        
        $resultado=parent::ejecutar_consulta_simple($consulta);
        return ($resultado->rowCount() > 0);    
    }
    
    
    
    //valida fechas no solapadas
    protected static function validar_fecha_no_solapada_modelo($tipo, $fechaInicio, $fechaFin, $excluir = null) {
        $tipo = self::limpiar_cadena($tipo);
        $fechaInicio = self::limpiar_cadena($fechaInicio);
        $fechaFin = self::limpiar_cadena($fechaFin);

        $consulta = "
            SELECT id_cohorte, numero_cohorte, fecha_inicio, fecha_fin
            FROM cohorte
            WHERE tipo_cohorte = '$tipo'
              AND archivar_cohorte = 1
              AND (
                  ('$fechaInicio' BETWEEN fecha_inicio AND fecha_fin) OR
                  ('$fechaFin' BETWEEN fecha_inicio AND fecha_fin) OR
                  (fecha_inicio BETWEEN '$fechaInicio' AND '$fechaFin') OR
                  (fecha_fin BETWEEN '$fechaInicio' AND '$fechaFin')
              )
        ";

        if ($excluir !== null) {
            $excluir = self::limpiar_cadena($excluir);
            $idExcluir = self::decryption($excluir);
            $consulta .= " AND id_cohorte != '$idExcluir'";
        }

        $consulta .= " ORDER BY numero_cohorte ASC";
        return parent::ejecutar_consulta_simple($consulta);
    }
    
    
//trae el ultimo cohorte registrado del tipo seleccionado
    public static function validar_traer_ultimo_cohorte_modelo($tipo, $id=null){
            $tipo = self::limpiar_cadena($tipo);
            
            $consulta = "
                SELECT numero_cohorte, anio_cohorte, fecha_inicio, fecha_fin
                FROM cohorte
                WHERE tipo_cohorte = '$tipo'
                  AND archivar_cohorte = 1
            ";
        
            if($id !== null){
            	$id = self::limpiar_cadena($id);
            	$consulta.="AND id_cohorte != '$id'";
        	}
            
    		$consulta .= " ORDER BY fecha_inicio DESC LIMIT 1";
            $resultado=parent::ejecutar_consulta_simple($consulta);
            return $resultado;
    }*/

    
    //----------------------------------------------
    
    
    
    protected static function agregar_duracion_modelo($datos){
        try {
            $pdo = mainModel::conectar();
            $sql = $pdo->prepare("
                INSERT INTO duracion_periodo (
                    estado_periodo,
                    fecha_inicio, fecha_fin,
                    archivar_periodo
                ) VALUES (
                    :estado,
                    :inicio, :fin,
                    1
                )
            ");

            $sql->execute($datos);
            return $pdo->lastInsertId();
        } catch (Exception $e) {
            error_log("Error al insertar cohorte: " . $e->getMessage());
            return 0;
        }
    }

    
    
    
    


    protected static function agregar_cohorte_modelo($datos){
        try {
            // estado_cohorte, fecha_inicio, fecha_fin,archivar_cohorte -- :estado, :inicio, :fin,

            $pdo = mainModel::conectar();
            $sql = $pdo->prepare("
                INSERT INTO cohorte (
                    id_duracion_fk,
                    numero_cohorte, anio_cohorte, tipo_cohorte,
                    
                    cohorte_alterno,
                    
                    pago_art13, fecha_pago_art13,
                    pago_aranceles, fecha_pago_aranceles,
                    fecha_pago_modulo1, fecha_pago_modulo2, fecha_pago_modulo3,
                    pago_inscripcion, pago_modulo
                ) VALUES (
                    :id_duracion,
                    
                    :numero, :anio, :tipo,
                    
                    :cohorte_alterno,
                    
                    :p_art13, :f_art13,
                    :p_arancel, :f_arancel,
                    :f_mod1, :f_mod2, :f_mod3,
                    :pago_inscripcion, :pago_modulo
                )
            ");

            $sql->execute($datos);
            return $pdo->lastInsertId();
        } catch (Exception $e) {
            error_log("Error al insertar cohorte: " . $e->getMessage());
            return 0;
        }
    }
//pepe

    
    
    
    
    protected static function agregar_cursos_vigentes_modelo($id_duracion, $cursos, $pdo = null){
        if ($pdo === null) {
            $pdo = mainModel::conectar();
        }
        $cursosRegistrados = 0;

        foreach ($cursos as $curso) {
            $idCurso = $curso['id_curso_fk'] ?? null;
           // $oferta = $curso['oferta'] ?? null;
            $idProfesor = $curso['id_profesor_fk'] ?? null;
            $dia = $curso['dia'] ?? null;

            if ($idCurso !== null) {
                //id_cohorte_fk
                                    //:cohorte

                $sql = $pdo->prepare("
                    INSERT INTO curso_vigente (id_curso_fk, id_duracion_fk, estado_curso_vigente)
                    VALUES (:curso, :id_duracion, 1)
                ");
                $sql->bindParam(":curso", $idCurso);
                $sql->bindParam(":id_duracion", $id_duracion);
				// $sql->bindParam(":oferta", $oferta);

                if ($sql->execute()) {
                    $idCursoVigente = $pdo->lastInsertId();

                    self::agregar_seccion_A($idCursoVigente, $idProfesor, "A", $dia, $pdo);
                    $cursosRegistrados++;
                }
            }
        }

        return $cursosRegistrados;
    }





    protected static function agregar_seccion_A($idCursoVigente, $idProfesor, $nombreSeccion, $dia, $pdo = null){
        try {
            if ($pdo === null) {
                $pdo = mainModel::conectar();
            }
            $sql = $pdo->prepare("
                INSERT INTO seccion (id_curso_vigente_fk, ci_profesor, nombre_seccion, dia, archivar_seccion)
                VALUES (:id_curso_vigente, :ci_profesor, :nombre_seccion, :dia, 1)
            ");
            $sql->bindParam(":id_curso_vigente", $idCursoVigente);
            $sql->bindParam(":ci_profesor", $idProfesor);
            $sql->bindParam(":nombre_seccion", $nombreSeccion);
            $sql->bindParam(":dia", $dia);
            $sql->execute();
        } catch (Exception $e) {
            error_log("Error al crear sección '{$nombreSeccion}': " . $e->getMessage());
        }
    }

    
    /* no tienen pdo
    protected static function agregar_cursos_vigentes_modelo($id_duracion, $cursos){
        $pdo = mainModel::conectar();
        $cursosRegistrados = 0;

        foreach ($cursos as $curso) {
            $idCurso = $curso['id_curso_fk'] ?? null;
           // $oferta = $curso['oferta'] ?? null;
            $idProfesor = $curso['id_profesor_fk'] ?? null;
            $dia = $curso['dia'] ?? null;

            if ($idCurso !== null) {
                //id_cohorte_fk
                                    //:cohorte

                $sql = $pdo->prepare("
                    INSERT INTO curso_vigente (id_curso_fk, id_duracion_fk, estado_curso_vigente)
                    VALUES (:curso, :id_duracion, 1)
                ");
                $sql->bindParam(":curso", $idCurso);
                $sql->bindParam(":id_duracion", $id_duracion);
				// $sql->bindParam(":oferta", $oferta);

                if ($sql->execute()) {
                    $idCursoVigente = $pdo->lastInsertId();

                    self::agregar_seccion_A($idCursoVigente, $idProfesor, "A", $dia);
                    $cursosRegistrados++;
                }
            }
        }

        return $cursosRegistrados;
    }





    protected static function agregar_seccion_A($idCursoVigente, $idProfesor, $nombreSeccion, $dia){
        try {
            $pdo = mainModel::conectar();
            $sql = $pdo->prepare("
                INSERT INTO seccion (id_curso_vigente_fk, ci_profesor, nombre_seccion, dia, archivar_seccion)
                VALUES (:id_curso_vigente, :ci_profesor, :nombre_seccion, :dia, 1)
            ");
            $sql->bindParam(":id_curso_vigente", $idCursoVigente);
            $sql->bindParam(":ci_profesor", $idProfesor);
            $sql->bindParam(":nombre_seccion", $nombreSeccion);
            $sql->bindParam(":dia", $dia);
            $sql->execute();
        } catch (Exception $e) {
            error_log("Error al crear sección '{$nombreSeccion}': " . $e->getMessage());
        }
    }
*/










/*



SELECT 
    cv.id_curso_vigente, 
    cv.id_curso_fk, 
    cv.oferta, 
    c.nombre_curso, 
    c.tipo_curso
FROM curso_vigente cv
JOIN curso c ON cv.id_curso_fk = c.id_curso
WHERE cv.id_cohorte_fk = :idCohorte
*/


    
    //traer cursos vigentes para cohorte update
    protected static function obtener_cursos_vigentes_por_cohorte_modelo($tipo,$idduracion){
        
        /*
                    SELECT cv.id_curso_vigente, cv.id_curso_fk, c.nombre_curso, c.tipo_curso,
                s.ci_profesor, s.dia, p.ci_profesor AS ci_profesor_real, p.nombre_profesor, p.apellido_profesor
            FROM curso_vigente cv
            JOIN curso c ON cv.id_curso_fk = c.id_curso
            LEFT JOIN seccion s ON s.id_curso_vigente_fk = cv.id_curso_vigente AND s.nombre_seccion = 'A'
            LEFT JOIN profesor p ON p.id_profesor = s.ci_profesor
            WHERE cv.id_duracion_fk = :idduracion
             AND cv.estado_curso_vigente = 1
        */
        $pdo = mainModel::conectar();
        $sql = $pdo->prepare("
             
             SELECT cv.id_curso_vigente, cv.id_curso_fk, c.nombre_curso, c.tipo_curso,
                   s.ci_profesor, s.dia, p.ci_profesor AS ci_profesor_real, p.nombre_profesor, p.apellido_profesor
            FROM curso_vigente cv
            JOIN cohorte coh ON coh.id_duracion_fk = cv.id_duracion_fk   
            JOIN curso c ON cv.id_curso_fk = c.id_curso
            LEFT JOIN seccion s ON s.id_curso_vigente_fk = cv.id_curso_vigente AND s.nombre_seccion = 'A'
            LEFT JOIN profesor p ON p.id_profesor = s.ci_profesor
            WHERE coh.id_cohorte = :id_cohorte
            AND cv.estado_curso_vigente = 1

        ");
        $sql->bindParam(":id_cohorte", $idduracion);
        $sql->execute();
        return $sql;
        //            WHERE cv.id_cohorte_fk = :idCohorte

    }



protected static function actualizar_curso_vigente_modelo($datos){
    try {
        $pdo = mainModel::conectar();
        $sql = $pdo->prepare("
            UPDATE curso_vigente SET
                id_curso_fk = :id_curso_fk
            WHERE id_curso_vigente = :id
        ");
        $sql->execute([
            ':id_curso_fk' => $datos['id_curso_fk'],
            ':id' => $datos['id']
        ]);
        return true;
    } catch (Exception $e) {
        error_log("Error al actualizar curso vigente: " . $e->getMessage());
        return false;
    }
}


/*-----------------------cohorte update----------------------------*/
    //traer datos para cohorte update 
    protected static function datos_cohorte_modelo($tipo, $id){
        $conexion = mainModel::conectar();

        if($tipo == "Unico"){
            $sql = $conexion->prepare("
                SELECT c.*, dp.* FROM cohorte c
                INNER JOIN duracion_periodo dp ON c.id_duracion_fk = dp.id_duracion
                WHERE c.id_cohorte = :ID
            ");
            $sql->bindParam(":ID", $id);

        } elseif($tipo == "Conteo"){
            $sql = $conexion->prepare("SELECT id_cohorte FROM cohorte");
        }

        $sql->execute();
        return $sql;
    }
    
    //traer profesores
    public static function obtener_todos_los_profesores_modelo(){
        
        $conexion = mainModel::conectar();    
        $sql = $conexion->prepare("
            SELECT id_profesor, nombre_profesor, apellido_profesor
            FROM profesor
            WHERE estado_profesor = 1
            ORDER BY nombre_profesor ASC
        ");

        $sql->execute();
        return $sql;
    }
    
    // Método para actualizar cohorte
    public static function actualizar_cohorte_modelo($datos) {
        $sql = "UPDATE cohorte SET
                numero_cohorte = :numero,
                anio_cohorte = :anio,
                tipo_cohorte = :tipo,
                fecha_inicio = :inicio,
                fecha_fin = :fin,
                pago_art13 = :p_art13,
                fecha_pago_art13 = :f_art13,
                pago_aranceles = :p_arancel,
                fecha_pago_aranceles = :f_arancel,
                fecha_pago_modulo1 = :f_mod1,
                fecha_pago_modulo2 = :f_mod2,
                fecha_pago_modulo3 = :f_mod3,
                pago_inscripcion = :pago_inscripcion,
                pago_modulo = :pago_modulo
                WHERE id_cohorte = :id";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);

        return $stmt->execute($datos);
    }

    // Método para procesar cursos asignados para cohorte update
    public static function procesar_cursos_asignados_modelo($id_cohorte, $cursosAsignados) {
        foreach ($cursosAsignados as $curso) {
            $idCursoVigente = mainModel::limpiar_cadena($curso['id_curso_vigente'] ?? '');
            $estado = $curso['estado_curso_vigente'] ?? 1;

            if ($estado == 0) {
                if ($idCursoVigente != '') {
                    self::archivar_curso_vigente_modelo($idCursoVigente);
                }
            } else {
                if (isset($curso['id_curso_fk'])) {
                    $idCurso = mainModel::limpiar_cadena($curso['id_curso_fk']);
                    self::actualizar_curso_vigente_modelo([
                        'id' => $idCursoVigente,
                        'id_curso_fk' => $idCurso,
                    ]);
                }
            }
        }
        return true;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    



    protected static function archivar_curso_vigente_modelo($idCursoVigente) {
        $pdo = mainModel::conectar();

        try {
            // Archivar curso vigente (fuera de transacción para asegurar que se archive sí o sí)
            $sqlCurso = $pdo->prepare("
                UPDATE curso_vigente 
                SET estado_curso_vigente = 0 
                WHERE id_curso_vigente = :id
            ");
            $sqlCurso->bindValue(":id", $idCursoVigente, PDO::PARAM_INT);
            $sqlCurso->execute();

            if ($sqlCurso->rowCount() === 0) {
                throw new Exception("No se pudo archivar curso vigente ID $idCursoVigente (no encontrado o ya archivado)");
            }

            // Iniciar transacción para hijos
            $pdo->beginTransaction();

            // Obtener secciones ligadas
            $sqlSecciones = $pdo->prepare("
                SELECT id_seccion 
                FROM seccion 
                WHERE id_curso_vigente_fk = :id
            ");
            $sqlSecciones->bindValue(":id", $idCursoVigente, PDO::PARAM_INT);
            $sqlSecciones->execute();
            $secciones = $sqlSecciones->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($secciones)) {
                // Preparar consultas
                $sqlArchivarSeccion = $pdo->prepare("
                    UPDATE seccion 
                    SET archivar_seccion = 0 
                    WHERE id_seccion = :id
                ");
                $sqlEvaluaciones = $pdo->prepare("
                    SELECT id_evaluacion 
                    FROM evaluacion 
                    WHERE id_seccion = :id
                ");
                $sqlArchivarEvaluacion = $pdo->prepare("
                    UPDATE evaluacion 
                    SET archivar_evaluacion = 0 
                    WHERE id_evaluacion = :id
                ");
                $sqlArchivarCalificacion = $pdo->prepare("
                    UPDATE puntaje_estudiante 
                    SET archivar_puntaje = 0 
                    WHERE id_evaluacion = :id
                ");

                $sqlActualizarInscripcion = $pdo->prepare("
                    UPDATE inscripcion
                    SET observaciones = CONCAT(IFNULL(observaciones, ''), ' | Periodo archivado o inactivo')
                    WHERE id_seccion_fk = :id
                      AND IFNULL(observaciones, '') NOT LIKE '%Periodo archivado o inactivo%'
                ");

                foreach ($secciones as $idSeccion) {
                    $sqlArchivarSeccion->bindValue(":id", $idSeccion, PDO::PARAM_INT);
                    $sqlArchivarSeccion->execute();

                    $sqlEvaluaciones->bindValue(":id", $idSeccion, PDO::PARAM_INT);
                    $sqlEvaluaciones->execute();
                    $evaluaciones = $sqlEvaluaciones->fetchAll(PDO::FETCH_COLUMN);

                    if (!empty($evaluaciones)) {
                        foreach ($evaluaciones as $idEval) {
                            $sqlArchivarEvaluacion->bindValue(":id", $idEval, PDO::PARAM_INT);
                            $sqlArchivarEvaluacion->execute();

                            $sqlArchivarCalificacion->bindValue(":id", $idEval, PDO::PARAM_INT);
                            $sqlArchivarCalificacion->execute();

                        }
                    }
                    $sqlActualizarInscripcion->bindValue(":id", $idSeccion, PDO::PARAM_INT);
                    $sqlActualizarInscripcion->execute();
                }
            }

            $pdo->commit();
            return true;

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error al archivar en cascada: " . $e->getMessage());
            return false;
        }
    }








    /*--------- Modelo obtener datos cohorte ---------
    protected static function datos_cohorte_modelo($tipo, $id){
        if($tipo=="Unico"){
            $sql = mainModel::conectar()->prepare("SELECT * FROM cohorte WHERE id_cohorte=:ID");
            $sql->bindParam(":ID", $id);
        } elseif($tipo=="Conteo"){
            $sql = mainModel::conectar()->prepare("SELECT id_cohorte FROM cohorte");
        }

        $sql->execute();
        return $sql;
    }*/


    

    //los  datos que necesita cursovigente new y update
    protected static function datos_curso_vigente_modelo($tipo, $id) {
        if ($tipo == "Unico") {
            $sql = mainModel::conectar()->prepare("SELECT * FROM curso_vigente WHERE id_curso_vigente = :ID");
            $sql->bindParam(":ID", $id, PDO::PARAM_INT);
        } elseif ($tipo == "Conteo") {
            $sql = mainModel::conectar()->prepare("SELECT id_curso_vigente FROM curso_vigente");
        }

        $sql->execute();
        return $sql;
    }



protected static function listar_cohortes_por_tipo_modelo($tipoCurso, $idCurso) {
    $sql = mainModel::conectar()->prepare("
        SELECT c.id_cohorte, c.numero_cohorte, c.anio_cohorte
        FROM cohorte c
        WHERE c.tipo_cohorte = :tipo
          AND c.archivar_cohorte != 0
          AND c.estado_cohorte IN ('Vigente', 'Próximo')
          AND c.id_cohorte NOT IN (
              SELECT cv.id_cohorte_fk
              FROM curso_vigente cv
              WHERE cv.id_curso_fk = :idCurso
          )
        ORDER BY c.anio_cohorte DESC, c.numero_cohorte DESC
    ");
    $sql->bindParam(":tipo", $tipoCurso, PDO::PARAM_STR);
    $sql->bindParam(":idCurso", $idCurso, PDO::PARAM_INT);
    $sql->execute();
    return $sql->fetchAll();
}


    /*
            // Actualizar oferta
        $sql1 = $pdo->prepare("
            UPDATE curso_vigente 
            SET oferta = :oferta 
            WHERE id_curso_vigente = :id
        ");
        $sql1->bindParam(":oferta", $oferta, PDO::PARAM_INT);
        $sql1->bindParam(":id", $idCursoVigente, PDO::PARAM_INT);
        $sql1->execute();
    */
    
    
//para course vigente update
protected static function actualizar_curso_vigente_y_seccion_modelo_cv($idCursoVigente, $idProfesor) {
    try {
        $pdo = mainModel::conectar();
        $pdo->beginTransaction();

        // Actualizar profesor en sección A
        $sql2 = $pdo->prepare("
            UPDATE seccion 
            SET ci_profesor = :profesor 
            WHERE id_curso_vigente_fk = :id AND nombre_seccion = 'A'
        ");
        $sql2->bindParam(":profesor", $idProfesor, PDO::PARAM_INT);
        $sql2->bindParam(":id", $idCursoVigente, PDO::PARAM_INT);
        $sql2->execute();

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error al actualizar curso vigente: " . $e->getMessage());
        return false;
    }
}










    /*--------- Modelo obtener cursos por tipo ---------*/
    protected static function obtener_cursos_por_tipo($tipo){
        $sql = mainModel::conectar()->prepare("
            SELECT id_curso, nombre_curso 
            FROM curso 
            WHERE tipo_curso = :Tipo AND estado_curso = 1
        ");
        $sql->bindParam(":Tipo", $tipo);
        $sql->execute();
        return $sql;
    }


    /*--------- Modelo obtener todos los cursos ---------*/
    protected static function obtener_todos_los_cursos_modelo(){
        $conexion = mainModel::conectar();
        $sql = $conexion->prepare("
            SELECT id_curso, nombre_curso, tipo_curso 
            FROM curso 
            WHERE estado_curso = 1 
            ORDER BY nombre_curso ASC
        ");
        $sql->execute();
        return $sql;
    }


/*
require_once "mainModel.php";

class cohorteModelo extends mainModel {


    protected static function agregar_cohorte_modelo($datos){
        try {
            $pdo = mainModel::conectar();
            $sql = $pdo->prepare("
                INSERT INTO cohorte (
                    numero_cohorte, anio_cohorte, tipo_cohorte, estado_cohorte,
                    fecha_inicio, fecha_fin,
                    pago_art13, fecha_pago_art13,
                    pago_aranceles, fecha_pago_aranceles,
                    fecha_pago_modulo1, fecha_pago_modulo2, fecha_pago_modulo3,
                    pago_inscripcion, pago_modulo,
                    archivar_cohorte
                ) VALUES (
                    :numero, :anio, :tipo, :estado,
                    :inicio, :fin,
                    :p_art13, :f_art13,
                    :p_arancel, :f_arancel,
                    :f_mod1, :f_mod2, :f_mod3,
                    :pago_inscripcion, :pago_modulo,
                    1
                )
            ");

            $sql->execute($datos);
            return $pdo->lastInsertId();
        } catch (Exception $e) {
            error_log("Error al insertar cohorte: " . $e->getMessage());
            return 0;
        }
    }


php
*/

}
?>