<?php

if($peticionAjax){
    require_once "../modelos/cohorteModelo.php";
}else{
    require_once "./modelos/cohorteModelo.php";
}

class cohorteControlador extends cohorteModelo {

    public function fecha_no_es_pasada($fecha){
        return strtotime($fecha) < strtotime(date('Y-m-d'));
    }


//---------------------------validaciones------------------------------
    
    

    public function validar_cohorte_no_duplicado($numero, $anio, $tipo, $alterno, $id=null){
        return cohorteModelo::validar_cohorte_no_duplicado_modelo($numero, $anio, $tipo, $alterno, $id);
    }

    public function validar_fecha_no_solapada($tipo, $fechaInicio, $fechaFin, $alterno, $excluir = '') {
        $resultado = cohorteModelo::validar_fecha_no_solapada_modelo($tipo, $fechaInicio, $fechaFin, $alterno, $excluir);

        if ($resultado && $resultado->rowCount() > 0) {
            $cohortes = $resultado->fetchAll(PDO::FETCH_ASSOC);
            $mensajes = [];
            foreach ($cohortes as $cohorte) {
                $nombre = ($cohorte['tipo_cohorte'] == "Otro") ? $cohorte['cohorte_alterno'] : $cohorte['tipo_cohorte'];
                $mensajes[] = "{$nombre} #{$cohorte['numero_cohorte']} ({$cohorte['fecha_inicio']} - {$cohorte['fecha_fin']})";
            }
            return [
                "solapado" => true,
                "mensaje" => "Conflicto con: " . implode(", ", $mensajes)
            ];
        }
        return ["solapado" => false, "mensaje" => ""];
    }

    public function validar_traer_ultimo_cohorte($tipo, $excluir=null){
        $resultado = cohorteModelo::validar_traer_ultimo_cohorte_modelo($tipo, $excluir);
        if ($resultado && $resultado->rowCount() > 0) {
            $cohorte = $resultado->fetch(PDO::FETCH_ASSOC);
            return [
                'existe' => true,
                'numero' => $cohorte['numero_cohorte'],
                'anio' => $cohorte['anio_cohorte'],
                'fecha_inicio' => $cohorte['fecha_inicio'],
                'fecha_fin' => $cohorte['fecha_fin']
            ];
        }
        return ['existe' => false];
    }
    
    
    /* esto no tiene lo de cohorte alterno
    public function validar_cohorte_no_duplicado($numero, $anio, $tipo, $id=null){
    	return cohorteModelo::validar_cohorte_no_duplicado_modelo($numero, $anio, $tipo, $id);
    }
    
    //public function validar_fecha_no_solapada($tipo, $fecha_inicio, $fecha_fin, $excluir=null){
     //   return cohorteModelo::validar_fecha_no_solapada_modelo($tipo, $fecha_inicio, $fecha_fin, $excluir);
    //}
    
    public function validar_fecha_no_solapada($tipo, $fechaInicio, $fechaFin, $excluir = '') {
        $resultado = cohorteModelo::validar_fecha_no_solapada_modelo($tipo, $fechaInicio, $fechaFin, $excluir);

        if ($resultado && $resultado->rowCount() > 0) {
            $cohortes = $resultado->fetchAll(PDO::FETCH_ASSOC);
            $mensajes = [];

            foreach ($cohortes as $cohorte) {
                $mensajes[] = "Cohorte #{$cohorte['numero_cohorte']} ({$cohorte['fecha_inicio']} - {$cohorte['fecha_fin']})";
            }

            return [
                "solapado" => true,
                "mensaje" => "Las fechas se solapan con: " . implode(", ", $mensajes),
                "cohortes_solapados" => $cohortes
            ];
        } else {
            return [
                "solapado" => false,
                "mensaje" => "",
                "cohortes_solapados" => []
            ];
        }
    }

    /*
    public function validar_traer_ultimo_cohorte($tipo, $excluir=null){
    	return cohorteModelo::validar_traer_ultimo_cohorte_modelo($tipo, $excluir);
	}*--------------------

    public function validar_traer_ultimo_cohorte($tipo, $excluir=null){
        $resultado = cohorteModelo::validar_traer_ultimo_cohorte_modelo($tipo, $excluir);

        if ($resultado && $resultado->rowCount() > 0) {
            $cohorte = $resultado->fetch(PDO::FETCH_ASSOC);
            return [
                'existe' => true,
                'numero' => $cohorte['numero_cohorte'],
                'anio' => $cohorte['anio_cohorte'],
                'fecha_inicio' => $cohorte['fecha_inicio'],
                'fecha_fin' => $cohorte['fecha_fin']
            ];
        } else {
            return [
                'existe' => false,
                'numero' => 0,
                'anio' => '',
                'fecha_inicio' => '',
                'fecha_fin' => ''
            ];
        }
    }
    */
    
    
    //datos-cohorte-controlador buscar es para cohorte update
    
    public function validar_fechas_duplicadas($fechas_criticas) {
        $fechas_array = [];

        foreach ($fechas_criticas as $fecha) {
            if (empty($fecha)) continue;

            if (in_array($fecha, $fechas_array)) {
                return true;
            }
            $fechas_array[] = $fecha;
        }

        return false; 
    }
    
    public function validar_fechas_en_rango($f_inicio, $f_fin, $fechas_pago) {
        $f_inicio_obj = new DateTime($f_inicio);
        $f_fin_obj = new DateTime($f_fin);

        foreach ($fechas_pago as $fecha) {
            if (empty($fecha)) continue;
            $fecha_obj = new DateTime($fecha);

            if ($fecha_obj < $f_inicio_obj || $fecha_obj > $f_fin_obj) {
                return true; 
            }
        }

        return false;
    }
    
    
    
    
//--------------------------------------------------------------------


/*
    public function cursos_vigentes_por_cohorte($idCohorte){
    return mainModel::ejecutar_consulta_simple("
        SELECT cv.id_curso_vigente, cv.id_curso_fk, cv.oferta, c.nombre_curso, c.tipo_curso,
            s.ci_profesor, p.ci_profesor, p.nombre_profesor, p.apellido_profesor
        FROM curso_vigente cv
        JOIN curso c ON cv.id_curso_fk = c.id_curso
        LEFT JOIN seccion s ON s.id_curso_vigente_fk = cv.id_curso_vigente AND s.nombre_seccion = 'A'
        LEFT JOIN profesor p ON p.id_profesor = s.ci_profesor
        WHERE cv.id_cohorte_fk = '$idCohorte'
    ");
    }*/


/*no desencripta el id
    public function cursos_vigentes_por_cohorte_controlador($idCohorte){
        $stmt = cohorteModelo::obtener_cursos_vigentes_por_cohorte_modelo($idCohorte);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }*/



/*
    private function fecha_dentro_del_margen($nueva_fecha, $fecha_original, $dias_margen = 112){
        $nueva = strtotime($nueva_fecha);
        $original = strtotime($fecha_original);
        $min_rango = $original - ($dias_margen * 86400);
        $max_rango = $original + ($dias_margen * 86400);
        return ($nueva >= $min_rango && $nueva <= $max_rango);
    }
*/
  

        /*    echo "<pre>"; // Formateo básico para que sea legible
            var_dump([
                "INFO_BASICA" => [
                    "numero" => $numero,
                    "anio" => $anio,
                    "tipo" => $tipo,
                    "alterno" => $alterno,
                    "estado_calculado" => $estado
                ],
                "FECHAS_DURACION" => [
                    "inicio" => $f_inicio,
                    "fin" => $f_fin
                ],
                "PAGOS_Y_MONTOS" => [
                    "inscripcion" => $pago_inscripcion,
                    "modulo_base" => $pago_modulo,
                    "f_art13" => $f_art13,
                    "f_arancel" => $f_arancel,
                    "f_mod1" => $f_mod1,
                    "f_mod2" => $f_mod2,
                    "f_mod3" => $f_mod3,
                    "art p" => $parancel,
                    "arancel p" => $p13
                ],
                "CURSOS_RECIBIDOS" => $_POST['cursos_vigentes'] ?? "Sin cursos",
                "POST_COMPLETO" => $_POST // Agregamos esto para ver si falta algún name en el HTML
            ]);
            echo "</pre>";
            die(); 
            // --- FIN BLOQUE DE DEPURACIÓN ---
            */
    
    
    


        public function agregar_cohorte_controlador(){
            // 1. Recoger y limpiar datos
            $numero = mainModel::limpiar_cadena($_POST['cohorte_numero_reg']);
            $anio = mainModel::limpiar_cadena($_POST['cohorte_anio_reg']);
            $tipo = mainModel::limpiar_cadena($_POST['cohorte_tipo_reg']);
            $pago_inscripcion = mainModel::limpiar_cadena($_POST['cohorte_pago_inscripcion_reg']);
            $pago_modulo = mainModel::limpiar_cadena($_POST['cohorte_pago_modulo_reg']);
            $f_inicio = $_POST['cohorte_fecha_inicio_reg'];
            $f_fin = $_POST['cohorte_fecha_fin_reg'];
                        
			$p_art13 = mainModel::limpiar_cadena($_POST['cohorte_pago_art13_reg']);
        	$p_arancel = mainModel::limpiar_cadena($_POST['cohorte_pago_aranceles_reg']);

            $alterno_raw = $_POST['cohorte_alterno'] ?? ""; 
            $alterno = mainModel::limpiar_cadena($alterno_raw);

            $hoy = date("Y-m-d");

            if ($hoy > $f_fin) {
                $estado = "Terminado";
            } elseif ($hoy >= $f_inicio && $hoy <= $f_fin) {
                $estado = "Vigente";
            } else {
                $estado = "Próximo";
            }
            
            $f_art13 = $_POST['cohorte_fecha_pago_art13_reg'];
            $f_arancel = $_POST['cohorte_fecha_pago_aranceles_reg'];
            $f_mod1 = $_POST['cohorte_fecha_pago_modulo1_reg'];
            $f_mod2 = $_POST['cohorte_fecha_pago_modulo2_reg'];
            $f_mod3 = $_POST['cohorte_fecha_pago_modulo3_reg'];

            if($numero=="" || $anio=="" || $tipo=="" || $f_inicio=="" || $f_fin==""){
                return $this->respuesta_json("Campos incompletos", "Debes llenar todos los campos obligatorios", "error");
            }

            if($this->validar_cohorte_no_duplicado($numero, $anio, $tipo, $alterno)){
                return $this->respuesta_json("Cohorte duplicada", "Ya existe una cohorte con estos datos", "error");
            }
            
            $validacionSolape = $this->validar_fecha_no_solapada($tipo, $f_inicio, $f_fin, $alterno);
        	if ($validacionSolape["solapado"]) {
          	  mainModel::generarAlertaControlador($validacionSolape["mensaje"],"error_simple");
        	}

            if(strtotime($f_inicio) > strtotime($f_fin)){
                return $this->respuesta_json("Error de fechas", "La fecha de inicio no puede ser posterior al fin", "error");
            }


            $fechas_criticas = [
                "Inicio del trimestre" => $f_inicio,
                "Fin del trimestre" => $f_fin,
                "Pago Artículo 13" => $f_art13,
                "Pago Aranceles" => $f_arancel,
                "Pago Módulo 1" => $f_mod1,
                "Pago Módulo 2" => $f_mod2,
                "Pago Módulo 3" => $f_mod3
            ];

            $fechas_pagos = [
                "Pago Artículo 13" => $f_art13,
                "Pago Aranceles" => $f_arancel,
                "Pago Módulo 1" => $f_mod1,
                "Pago Módulo 2" => $f_mod2,
                "Pago Módulo 3" => $f_mod3
            ];

            if($this->validar_fechas_duplicadas($fechas_criticas)){
                mainModel::generarAlertaControlador("Las fechas no pueden repetirse", "error_simple");
            }        

            if($this->validar_fechas_en_rango($f_inicio,$f_fin, $fechas_pagos)){
                mainModel::generarAlertaControlador("Las fechas de pago deben estar dentro del rango de $f_inicio y $f_fin ", "error_simple");
            }
        
            
            try {
                $conexion = mainModel::conectar(); 
                $conexion->beginTransaction();

                $id_duracion = cohorteModelo::agregar_duracion_modelo([
                    ':estado' => $estado,
                    ':inicio' => $f_inicio,
                    ':fin' => $f_fin
                ]);

                if($id_duracion <= 0) throw new Exception("Error al insertar la duración");

                // B. Insertar Cohorte
                $id_cohorte = cohorteModelo::agregar_cohorte_modelo([
                    ':id_duracion' => $id_duracion,
                    ':numero' => $numero,
                    ':anio' => $anio,
                    ':tipo' => $tipo,
                    ':p_art13' => $p_art13,
                    ':f_art13' => $f_art13,
                    ':p_arancel' => $p_arancel,
                    ':f_arancel' => $f_arancel,
                    ':f_mod1' => $f_mod1,
                    ':f_mod2' => $f_mod2,
                    ':f_mod3' => $f_mod3,
                    ':pago_inscripcion' => $pago_inscripcion,
                    ':pago_modulo' => $pago_modulo,
                    ':cohorte_alterno' => $alterno
                ]);

                if($id_cohorte <= 0) throw new Exception("Error al insertar la cohorte");

                $cursos = $_POST['cursos_vigentes'] ?? [];
                if(!empty($cursos)){
                    cohorteModelo::agregar_cursos_vigentes_modelo($id_duracion, $cursos);
                }
                
                $conexion->commit();

                return json_encode([
                    "Alerta" => "recargar",
                    "Titulo" => "Éxito",
                    "Texto" => "La cohorte se ha registrado correctamente",
                    "Tipo" => "success"
                ]);

            } catch (Exception $e) {
                if(isset($conexion)) $conexion->rollBack();
                return $this->respuesta_json("Error interno", $e->getMessage(), "error");
            }
        }

        // Función auxiliar para no repetir código de JSON
        private function respuesta_json($titulo, $texto, $tipo){
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => $titulo,
                "Texto" => $texto,
                "Tipo" => $tipo
            ]);
        }

    
    
    
    

//agregar_curso_vigente_con_seccionA_modelo pendiente con esto




/*------------------------ cohorte update ----------------------------*/
    
    //trae los datos de la cohorte por su id
    public function datos_cohorte_controlador($tipo,$id){
        $tipo = mainModel::limpiar_cadena($tipo);
        $id = mainModel::decryption($id);
        $id = mainModel::limpiar_cadena($id);

        return cohorteModelo::datos_cohorte_modelo($tipo,$id);
    }
    
    
	//cursos asignados a la cohorte en cohorte update    
    public function cursos_vigentes_por_cohorte_controlador($tipo, $id) {
        $tipo = mainModel::limpiar_cadena($tipo);
        $id = mainModel::decryption($id);
        $id = mainModel::limpiar_cadena($id);
        return cohorteModelo::obtener_cursos_vigentes_por_cohorte_modelo($tipo, $id);
    }

	//cursos disponibles para la cohorte en cohorte update   
    public function listar_cursos_controlador_para_cohorte(){
        $stmt = cohorteModelo::obtener_todos_los_cursos_modelo();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

	//profesores disponibles para los cursos de la cohorte en cohorte update
    public function listar_profesores_controlador(){
        //$conexion = mainModel::conectar();
        //$stmt = $conexion->prepare("
          //  SELECT id_profesor, nombre_profesor, apellido_profesor
            //FROM profesor
            //WHERE estado_profesor = 1
            //ORDER BY nombre_profesor ASC
        //");
       // $stmt->execute();
        
        $stmt = cohorteModelo::obtener_todos_los_profesores_modelo();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public function actualizar_cohorte_controlador()
    {
        // 1. Recoger y limpiar datos básicos
        $idEnc = mainModel::limpiar_cadena($_POST['cohorte_id_up']);
        $id_cohorte = mainModel::decryption($idEnc);

        $numero = mainModel::limpiar_cadena($_POST['cohorte_numero_up']);
        $anio = mainModel::limpiar_cadena($_POST['cohorte_anio_up']);
        $tipo = mainModel::limpiar_cadena($_POST['cohorte_tipo_up']);
        $f_inicio = $_POST['cohorte_fecha_inicio_up'];
        $f_fin = $_POST['cohorte_fecha_fin_up'];

        $pago_inscripcion = mainModel::limpiar_cadena($_POST['cohorte_pago_inscripcion_up']);
        $pago_modulo = mainModel::limpiar_cadena($_POST['cohorte_pago_modulo_up']);

        $p_art13 = mainModel::limpiar_cadena($_POST['cohorte_pago_art13_up']);
        $f_art13 = $_POST['cohorte_fecha_pago_art13_up'];
        $p_arancel = mainModel::limpiar_cadena($_POST['cohorte_pago_aranceles_up']);
        $f_arancel = $_POST['cohorte_fecha_pago_aranceles_up'];

        $f_mod1 = $_POST['cohorte_fecha_pago_modulo1_up'] ?? null;
        $f_mod2 = $_POST['cohorte_fecha_pago_modulo2_up'] ?? null;
        $f_mod3 = $_POST['cohorte_fecha_pago_modulo3_up'] ?? null;

        $alterno = mainModel::limpiar_cadena($_POST['cohorte_alterno_up'] ?? "");        
        
        
        
        // 2. Validaciones de Campos Obligatorios
        if (
            $numero == "" || $anio == "" || $tipo == "" || $f_inicio == "" || $f_fin == "" || $pago_inscripcion == "" || $pago_modulo == ""
            || $p_art13 == "" || $f_art13 == "" || $p_arancel == "" || $f_arancel == "" || $f_mod1 == "" || $f_mod2 == "" || $f_mod3 == "" || $idEnc== ""
        ) {
            mainModel::generarAlertaControlador("No has llenado todos los campos obligatorios", "error_simple");
        }

        // 3. Validaciones de Lógica de Negocio
        if (strtotime($f_inicio) > strtotime($f_fin)) {
            mainModel::generarAlertaControlador("La fecha de inicio no puede ser posterior al fin", "error_simple");
        }

        if ($this->validar_cohorte_no_duplicado($numero, $anio, $tipo, $alterno, $id_cohorte)) {
            mainModel::generarAlertaControlador("Ya existe una cohorte con estos datos", "error_simple");
        }

        $validacionSolape = $this->validar_fecha_no_solapada($tipo, $f_inicio, $f_fin, $alterno, $id_cohorte);
        if ($validacionSolape["solapado"]) {
            mainModel::generarAlertaControlador($validacionSolape["mensaje"], "error_simple");
        }

        // Validar duplicidad de fechas y rangos
        $fechas_criticas = [
            "Inicio" => $f_inicio,
            "Fin" => $f_fin,
            "Art 13" => $f_art13,
            "Aranceles" => $f_arancel,
            "Mod 1" => $f_mod1,
            "Mod 2" => $f_mod2,
            "Mod 3" => $f_mod3
        ];
        if ($this->validar_fechas_duplicadas($fechas_criticas)) {
            mainModel::generarAlertaControlador("Las fechas no pueden repetirse", "error_simple");
        }

        // 4. Determinar nuevo estado basado en fechas
        $hoy = date("Y-m-d");
        if ($hoy > $f_fin) {
            $estado = "Terminado";
        } elseif ($hoy >= $f_inicio && $hoy <= $f_fin) {
            $estado = "Vigente";
        } else {
            $estado = "Próximo";
        }

        try {
            $conexion = mainModel::conectar();
            $conexion->beginTransaction();

            // A. Obtener el ID de la duración asociada a esta cohorte
            $datos_cohorte = $conexion->query("SELECT id_duracion_fk FROM cohorte WHERE id_cohorte = '$id_cohorte'")->fetch();
            $id_duracion = $datos_cohorte['id_duracion_fk'];

            // B. Actualizar tabla Duración
            $stmt_dur = $conexion->prepare("UPDATE duracion_periodo SET estado_periodo=:estado, fecha_inicio=:inicio, fecha_fin=:fin WHERE id_duracion=:id");
            $stmt_dur->execute([
                ':estado' => $estado,
                ':inicio' => $f_inicio,
                ':fin' => $f_fin,
                ':id' => $id_duracion
            ]);

            // C. Actualizar tabla Cohorte
            $stmt_coh = $conexion->prepare("UPDATE cohorte SET 
                numero_cohorte=:numero, anio_cohorte=:anio, tipo_cohorte=:tipo, 
                pago_art13=:p_art13, fecha_pago_art13=:f_art13, 
                pago_aranceles=:p_arancel, fecha_pago_aranceles=:f_arancel,
                fecha_pago_modulo1=:f_mod1, fecha_pago_modulo2=:f_mod2, fecha_pago_modulo3=:f_mod3,
                pago_inscripcion=:p_insc, pago_modulo=:p_mod, cohorte_alterno=:alt
                WHERE id_cohorte=:id");

            $stmt_coh->execute([
                ':numero' => $numero,
                ':anio' => $anio,
                ':tipo' => $tipo,
                ':p_art13' => $p_art13,
                ':f_art13' => $f_art13,
                ':p_arancel' => $p_arancel,
                ':f_arancel' => $f_arancel,
                ':f_mod1' => $f_mod1,
                ':f_mod2' => $f_mod2,
                ':f_mod3' => $f_mod3,
                ':p_insc' => $pago_inscripcion,
                ':p_mod' => $pago_modulo,
                ':alt' => $alterno,
                ':id' => $id_cohorte
            ]);

            // D. Procesar Cursos NUEVOS (los que se agregaron con el botón en la vista)
            $cursosNuevos = $_POST['cursos_vigentes'] ?? [];
            if (!empty($cursosNuevos)) {
                cohorteModelo::agregar_cursos_vigentes_modelo($id_duracion, $cursosNuevos,$conexion);
            }

            // E. Procesar Cursos YA ASIGNADOS (Archivar en cascada los marcados)
            $cursosAsignados = $_POST['cursos_asignados'] ?? [];

            // Mapa de archivado en cascada para curso_vigente
            // Usa la función genérica archivar_registros_cascada de mainModel
            $mapa_archivado_cv = [
                'columna_estado' => 'estado_curso_vigente',
                'hijos' => [
                    [
                        'tabla' => 'seccion',
                        'columna_id' => 'id_seccion',
                        'columna_fk' => 'id_curso_vigente_fk',
                        'columna_estado' => 'archivar_seccion',
                        'extra_sql' => "UPDATE inscripcion SET observaciones = CONCAT(IFNULL(observaciones, ''), ' | Periodo de Curso archivado o inactivo') WHERE id_seccion_fk = :id AND IFNULL(observaciones, '') NOT LIKE '%Periodo de Curso archivado o inactivo%'",
                        'hijos' => [
                            [
                                'tabla' => 'evaluacion',
                                'columna_id' => 'id_evaluacion',
                                'columna_fk' => 'id_seccion',
                                'columna_estado' => 'archivar_evaluacion',
                                'hijos' => [
                                    [
                                        'tabla' => 'puntaje_estudiante',
                                        'columna_id' => 'id_puntaje',
                                        'columna_fk' => 'id_evaluacion',
                                        'columna_estado' => 'archivar_puntaje'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            foreach ($cursosAsignados as $curso) {
                $idCursoVigente = mainModel::limpiar_cadena($curso['id_curso_vigente'] ?? '');
                $estado_cv = $curso['estado_curso_vigente'] ?? 1;

                if ($idCursoVigente != '' && $estado_cv == 0) {
                    // Archivado en cascada usando la función genérica de mainModel
                    // No genera mensajes, solo lanza excepción si falla
                    self::archivar_registros_cascada(
                        $conexion,
                        'curso_vigente',
                        'id_curso_vigente',
                        $idCursoVigente,
                        $mapa_archivado_cv
                    );
                }
            }

            $conexion->commit();

            echo json_encode([
                "Alerta" => "recargar",
                "Titulo" => "Cohorte actualizada",
                "Texto" => "Los cambios se guardaron correctamente.",
                "Tipo" => "success"
            ]);

        } catch (Exception $e) {
            if (isset($conexion))
                $conexion->rollBack();
            echo json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error al actualizar",
                "Texto" => "No se pudo completar el proceso: " . $e->getMessage(),
                "Tipo" => "error"
            ]);
        }
    }


    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
//en teoria soporta pasao de pagina con filtros
public function paginador_curso_vigente_por_curso_controlador($idCurso, $pagina, $registros) {
    $idCurso     = mainModel::limpiar_cadena($idCurso);
    $pagina      = ($pagina > 0) ? (int)$pagina : 1;
    $inicio      = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;
    $filtro_anio = isset($_GET['filtro_anio']) ? mainModel::limpiar_cadena($_GET['filtro_anio']) : "";

    $conexion = mainModel::conectar();

    // Consulta principal
    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS 
            cv.id_curso_vigente, 
            cv.id_cohorte_fk, 
            c.numero_cohorte, 
            c.anio_cohorte
        FROM curso_vigente cv
        INNER JOIN cohorte c ON cv.id_cohorte_fk = c.id_cohorte
        WHERE cv.id_curso_fk = :idCurso AND cv.estado_curso_vigente = 1 AND c.archivar_cohorte = 1
    ";
    if ($filtro_anio != "") {
        $consulta .= " AND c.anio_cohorte = :anioCohorte";
    }
    $consulta .= " ORDER BY c.anio_cohorte DESC, c.numero_cohorte DESC LIMIT $inicio, $registros";

    $stmt = $conexion->prepare($consulta);
    $stmt->bindParam(":idCurso", $idCurso, PDO::PARAM_INT);
    if ($filtro_anio != "") {
        $stmt->bindParam(":anioCohorte", $filtro_anio, PDO::PARAM_INT);
    }
    $stmt->execute();
    $datos    = $stmt->fetchAll();
    $total    = (int)$conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    // Construcción de tabla
    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>Id</th>
                    <th>Cohorte</th>
                    <th>Secciones</th>
                    <th>Inscripciones</th>
                    <th>Modificar</th>
                    <th>Archivar</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        foreach ($datos as $row) {
            $idCursoVigente = $row['id_curso_vigente'];
           // $oferta         = $row['oferta'];
            $cohorte        = $row['numero_cohorte'] . '.' . $row['anio_cohorte'];

            // Contar secciones
            $secciones = $conexion->prepare("
                SELECT COUNT(*) FROM seccion WHERE id_curso_vigente_fk = :id AND archivar_seccion = 1
            ");
            $secciones->bindParam(":id", $idCursoVigente, PDO::PARAM_INT);
            $secciones->execute();
            $numSecciones = $secciones->fetchColumn();

            // Contar inscripciones
            $estudiantes = $conexion->prepare("
                SELECT COUNT(DISTINCT i.id_estudiante_fk) AS total
                FROM inscripcion i
                INNER JOIN curso_vigente cv ON i.id_curso_vigente_fk = cv.id_curso_vigente
                WHERE i.id_curso_vigente_fk = :id
                AND i.archivada = 1
                AND cv.estado_curso_vigente = 1
            ");
            $estudiantes->bindParam(":id", $idCursoVigente, PDO::PARAM_INT);
            $estudiantes->execute();
            $numEstudiantes = $estudiantes->fetchColumn();

                            //<!--td>' . $oferta . '</td-->

            $tabla .= '
            <tr class="text-center">
                <td>' . $idCursoVigente . '</td>
                <td>' . $cohorte . '</td>
                <td>' . $numSecciones . '</td>
                <td>' . $numEstudiantes . '</td>
                <td>
                    <a href="' . SERVERURL . 'index.php?views=course-vigente-update&curso_vigente_id=' . mainModel::encryption($idCursoVigente) . '" class="btn btn-success btn-sm">
                        <i class="fas fa-edit"></i>
                    </a>
                </td>
                <td>
                    <form class="FormularioAjax" action="' . SERVERURL . 'ajax/cursoVigenteAjax.php" method="POST" data-form="delete" data-entity="curso_vigente" autocomplete="off">
                        <input type="hidden" name="curso_vigente_id_archive" value="' . mainModel::encryption($idCursoVigente) . '">
                        <button type="submit" class="btn btn-warning btn-sm">
                            <i class="fas fa-archive"></i>
                        </button>
                    </form>
                </td>
            </tr>';
        }
    } else {
        $tabla .= '<tr class="text-center"><td colspan="7">No hay cursos vigentes registrados para este curso</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    // Paginador con filtro persistente
    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando curso vigente ' . ($inicio + 1) . ' al ' . ($inicio + count($datos)) . ' de un total de ' . $total . '</p>';

        $queryParams = http_build_query([
            'curso_id' => mainModel::encryption($idCurso),
            'filtro_anio' => $filtro_anio
        ]);

        $urlCompleta = SERVERURL . "index.php?views=course-vigente-list&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }

    return $tabla;
}


/*
    public function paginador_cohorte_controlador($pagina, $registros, $vista, $es_activo) {
        $es_activo = ($es_activo == 1) ? 1 : 0;

        $pagina    = (int) mainModel::limpiar_cadena($pagina);
        $registros = (int) mainModel::limpiar_cadena($registros);
        $vista     = mainModel::limpiar_cadena($vista);

        // Filtros GET
        $filtro_id     = $_GET['filtro_id']     ?? "";
        $filtro_tipo   = $_GET['filtro_tipo']   ?? "";
        $filtro_anio   = $_GET['filtro_anio']   ?? "";
        $filtro_estado = $_GET['filtro_estado'] ?? "";
        $orden         = $_GET['orden']         ?? "normal";

        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;
        $conexion = mainModel::conectar();

//+
    $where = "dc.archivar_periodo = $es_activo";

        if ($filtro_id != "")    $where .= " AND c.id_cohorte = '$filtro_id'";
        if ($filtro_tipo != "")  $where .= " AND c.tipo_cohorte = '$filtro_tipo'";
        if ($filtro_anio != "")  $where .= " AND c.anio_cohorte = '$filtro_anio'";
        if ($filtro_estado != "") $where .= " AND dc.estado_periodo = '$filtro_estado'";

        $order_sql = match($orden) {
            "cursos"      => "ORDER BY total_cursos DESC",
            "estudiantes" => "ORDER BY total_estudiantes DESC",
            default       => "ORDER BY c.anio_cohorte DESC, c.numero_cohorte DESC"
        };

        $consulta = "
            SELECT SQL_CALC_FOUND_ROWS
                c.id_cohorte, c.numero_cohorte, c.anio_cohorte, c.tipo_cohorte,
                c.pago_inscripcion, c.pago_modulo,
                dc.estado_periodo, dc.fecha_inicio, dc.fecha_fin,
                COUNT(DISTINCT cv.id_curso_vigente) AS total_cursos,
                COUNT(DISTINCT i.id_inscripcion) AS total_estudiantes
            FROM cohorte c
            INNER JOIN duracion_periodo dc ON c.id_cohorte = dc.id_cohorte_fk
            LEFT JOIN curso_vigente cv ON cv.id_cohorte_fk = c.id_cohorte AND cv.estado_curso_vigente = $es_activo
            LEFT JOIN inscripcion i ON i.id_curso_vigente_fk = cv.id_curso_vigente AND i.archivada = $es_activo
            WHERE $where
            GROUP BY c.id_cohorte
            $order_sql
            LIMIT $inicio, $registros
        ";

        // Lógica de carpetas (Identidad del usuario)
        $cargo = $_SESSION['cargo_spm'] ?? 1;
        $carpetas = [1=>"admin", 2=>"estudio", 3=>"pagos", 4=>"cursos", 5=>"estudiante"];
        $carpeta = $carpetas[$cargo] ?? "admin";
        $vistaCompleta = urlencode($carpeta) . '/' . $vista;

        $datos    = $conexion->query($consulta)->fetchAll();
        $total    = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
        $Npaginas = ceil($total / $registros);

        // Renderizado de cabecera
        $tabla = '<div class="table-responsive"><table class="table table-dark table-sm"><thead><tr class="text-center">';
        $tabla .= '<th>Id</th><th>Cohorte</th><th>Tipo</th><th>Estado</th><th>F_inicio</th><th>F_fin</th><th>Inscripción</th><th>Módulos</th>';

        if ($cargo != 5) $tabla .= '<th>Cursos</th><th>Inscritos</th>';
        $tabla .= '<th>Ver</th>';
        if ($cargo == 1 || $cargo == 4) $tabla .= '<th>Acciones</th>';
        $tabla .= '</tr></thead><tbody>';

        if ($total >= 1 && $pagina <= $Npaginas) {
            $reg_inicio = $inicio + 1;
            foreach ($datos as $row) {
                $idEnc = mainModel::encryption($row['id_cohorte']);
                $nombre = $row['numero_cohorte'] . "." . substr($row['anio_cohorte'], -2);

                $tabla .= '<tr class="text-center">
                    <td>'.$row['id_cohorte'].'</td>
                    <td>'.$nombre.'</td>
                    <td>'.$row['tipo_cohorte'].'</td>
                    <td><span class="badge '.($row['estado_duracion']=="Activo"?"bg-success":"bg-secondary").'">'.$row['estado_duracion'].'</span></td>
                    <td>'.date("d/m/Y", strtotime($row['fecha_inicio'])).'</td>
                    <td>'.date("d/m/Y", strtotime($row['fecha_final'])).'</td>
                    <td>'.$row['pago_inscripcion'].'$</td>
                    <td>'.$row['pago_modulo'].'$</td>';

                if ($cargo != 5) {
                    $tabla .= '<td>'.$row['total_cursos'].'</td><td>'.$row['total_estudiantes'].'</td>';
                }

                // Enlace a periodos
                $tabla .= '<td>
                    <a href="'.SERVERURL.'index.php?views='.urlencode($carpeta).'/seccion-list&filtro_id_cohorte='.$row['id_cohorte'].'&origen=cohorte" class="btn btn-info btn-sm">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>';

                // Botones de Edición/Archivo
                if ($cargo == 1 || $cargo == 4) {
                    $tabla .= '<td>
                        <div class="d-flex justify-content-center">
                            <a href="'.SERVERURL.'index.php?views='.urlencode($carpeta).'/cohorte2-update&cohorte_id_up='.$idEnc.'" class="btn btn-success btn-sm mr-1">
                                <i class="fas fa-sync-alt"></i>
                            </a>
                            <form class="FormularioAjax" action="'.SERVERURL.'ajax/cohorteAjax.php" method="POST" data-form="delete" data-entity="cohorte">
                                <input type="hidden" name="cohorte_id_archivar" value="'.$idEnc.'">
                                <button type="submit" class="btn btn-warning btn-sm">
                                    <i class="fas fa-archive"></i>
                                </button>
                            </form>
                        </div>
                    </td>';
                }
                $tabla .= '</tr>';
            }
            $reg_final = $reg_inicio + count($datos) - 1;
        } else {
            $tabla .= '<tr class="text-center"><td colspan="12">No hay registros que coincidan.</td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        if ($total >= 1 && $pagina <= $Npaginas) {
            $queryParams = http_build_query([
                'status' => $es_activo, // Persistencia del parámetro binario
                'filtro_id' => $filtro_id,
                'filtro_tipo' => $filtro_tipo,
                'filtro_anio' => $filtro_anio,
                'filtro_estado' => $filtro_estado,
                'orden' => $orden
            ]);

            $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
            $tabla .= '<p class="text-right small">Registros '.$reg_inicio.' - '.$reg_final.' de '.$total.'</p>';
            $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
        }

        return $tabla;
    }
    
    */
    

    public function paginador_cohorte_controlador($pagina, $registros, $vista, $es_activo) {
        $es_activo = ($es_activo == 1) ? 1 : 0;

        $pagina    = (int) mainModel::limpiar_cadena($pagina);
        $registros = (int) mainModel::limpiar_cadena($registros);
        $vista     = mainModel::limpiar_cadena($vista);

        // Filtros GET
        $filtro_id      = $_GET['filtro_id']      ?? "";
        $filtro_tipo    = $_GET['filtro_tipo']    ?? "";
        $filtro_anio    = $_GET['filtro_anio']    ?? "";
        $filtro_estado  = $_GET['filtro_estado']  ?? "";
        $filtro_alterno = $_GET['filtro_alterno'] ?? ""; // Nuevo filtro solicitado
        $orden          = $_GET['orden']          ?? "normal";

        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        // WHERE dinámico: Filtramos por el archivado del periodo
        $where = "dc.archivar_periodo = $es_activo";

        if ($filtro_id != "")      $where .= " AND c.id_cohorte = '$filtro_id'";
        if ($filtro_tipo != "")    $where .= " AND c.tipo_cohorte = '$filtro_tipo'";
        if ($filtro_anio != "")    $where .= " AND c.anio_cohorte = '$filtro_anio'";
        if ($filtro_alterno != "") $where .= " AND c.cohorte_alterno LIKE '%$filtro_alterno%'";
        if ($filtro_estado != "")  $where .= " AND dc.estado_periodo = '$filtro_estado'";

        $order_sql = match($orden) {
            "cursos"      => "ORDER BY total_cursos DESC",
            "estudiantes" => "ORDER BY total_estudiantes DESC",
            default       => "ORDER BY c.anio_cohorte DESC, c.numero_cohorte DESC"
        };

        // Consulta: cohorte (c) tiene la FK id_duracion_fk -> duracion_periodo (dc)
        $consulta = "
            SELECT SQL_CALC_FOUND_ROWS
                c.id_cohorte, c.numero_cohorte, c.anio_cohorte, c.tipo_cohorte, c.cohorte_alterno,
                c.pago_inscripcion, c.pago_modulo,
                dc.id_duracion, dc.estado_periodo, dc.fecha_inicio, dc.fecha_fin,
                COUNT(DISTINCT cv.id_curso_vigente) AS total_cursos,
                COUNT(DISTINCT i.id_inscripcion) AS total_estudiantes
            FROM cohorte c
            INNER JOIN duracion_periodo dc ON c.id_duracion_fk = dc.id_duracion
            LEFT JOIN curso_vigente cv ON cv.id_duracion_fk = dc.id_duracion 
                 AND cv.estado_curso_vigente = 1
            LEFT JOIN inscripcion i ON i.id_curso_vigente_fk = cv.id_curso_vigente 
                 AND i.archivada = 1
            WHERE $where
            GROUP BY c.id_cohorte, dc.id_duracion
            $order_sql
            LIMIT $inicio, $registros
        ";

        // Ejecución mediante la función modular del modelo
        $resultado = mainModel::ejecutar_consulta_paginada($consulta, $inicio, $registros);
        $datos     = $resultado['datos'];
        $total     = $resultado['total'];
        $Npaginas  = $resultado['paginas'];

        // Lógica de carpetas por cargo
        $cargo = $_SESSION['cargo_spm'] ?? 1;
        $carpetas = [1=>"admin", 2=>"estudio", 3=>"pagos", 4=>"cursos", 5=>"estudiante"];
        $carpeta = $carpetas[$cargo] ?? "admin";
        $vistaCompleta = urlencode($carpeta) . '/' . $vista;

        // Construcción de la tabla
        $tabla = '<div class="table-responsive"><table class="table table-dark table-sm"><thead><tr class="text-center">
                    <th>Id</th><th>Cohorte</th><th>Tipo</th><th>Estado</th><th>F_inicio</th><th>F_fin</th><th>Inscripción</th><th>Módulos</th>';
//<th>Alterno</th>
        if ($cargo != 5) $tabla .= '<th>Cursos</th><th>Inscritos</th>';
        //$tabla .= '<th>Ver</th>';
        if ($cargo == 1 || $cargo == 4) $tabla .= '<th>Acciones</th>';
        $tabla .= '</tr></thead><tbody>';

        if ($total >= 1 && $pagina <= $Npaginas) {
            $reg_inicio = $inicio + 1;
            foreach ($datos as $row) {
                $idEnc = mainModel::encryption($row['id_cohorte']);
                $nombre = $row['numero_cohorte'] . "." . substr($row['anio_cohorte'], -2);
                
                $tipoMostrar = $row['tipo_cohorte'];
            	if (($row['tipo_cohorte'] == "Otro" || $row['tipo_cohorte'] == "otro") && !empty($row['cohorte_alterno'])) {
                	$tipoMostrar = $row['cohorte_alterno'] . " (Otro)";
            	}

                //					<td><span class="badge '.($row['estado_periodo']=="Activo"?"bg-success":"bg-secondary").'">'.$row['estado_periodo'].'</span></td>
                //                    <td>'.$row['cohorte_alterno'].'</td>

                $tabla .= '<tr class="text-center">
                    <td>'.$row['id_cohorte'].'</td>
                    <td>'.$nombre.'</td>
					<td>'.$tipoMostrar.'</td>
                    <td>'.$row['estado_periodo'].'</td>
                    <td>'.date("d/m/Y", strtotime($row['fecha_inicio'])).'</td>
                    <td>'.date("d/m/Y", strtotime($row['fecha_fin'])).'</td>
                    <td>'.$row['pago_inscripcion'].'$</td>
                    <td>'.$row['pago_modulo'].'$</td>';

                if ($cargo != 5) {
                    $tabla .= '<td>'.$row['total_cursos'].'</td><td>'.$row['total_estudiantes'].'</td>';
                }

             //   $tabla .= '<td>
               //     <a href="'.SERVERURL.'index.php?views='.urlencode($carpeta).'/seccion-list&filtro_id_cohorte='.$row['id_cohorte'].'&origen=cohorte" class="btn btn-info btn-sm">
                 //       <i class="fas fa-eye"></i>
                   // </a>
           //     </td>';

                if ($cargo == 1 || $cargo == 4) {
                    $tabla .= '<td>
                        <div class="d-flex justify-content-center">
                            <a href="'.SERVERURL.'index.php?views='.urlencode($carpeta).'/seccion-list&filtro_id_cohorte='.$row['id_cohorte'].'&origen=cohorte" class="btn btn-info btn-sm">
                                <i class="fas fa-eye" title="Ver periodos"></i>
                            </a>
                            <a href="'.SERVERURL.'index.php?views='.urlencode($carpeta).'/cohorte2-update&cohorte_id_up='.$idEnc.'" class="btn btn-success btn-sm mr-1">
                                <i class="fas fa-sync-alt" title="Modificar"></i>
                            </a>
                            <form class="FormularioAjax" action="'.SERVERURL.'ajax/cohorteAjax.php" method="POST" data-form="delete" data-entity="cohorte">
                                <input type="hidden" name="cohorte_id_archivar" value="'.$idEnc.'">
                                <button type="submit" class="btn btn-warning btn-sm" title="Archivar">
                                    <i class="fas fa-archive"></i>
                                </button>
                            </form>
                        </div>
                    </td>';
                }
                $tabla .= '</tr>';
            }
            $reg_final = $reg_inicio + count($datos) - 1;
        } else {
            $tabla .= '<tr class="text-center"><td colspan="12">No hay registros que coincidan.</td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        // Paginador y persistencia
        if ($total >= 1 && $pagina <= $Npaginas) {
            $queryParams = http_build_query([
                'status' => $es_activo,
                'filtro_id' => $filtro_id,
                'filtro_tipo' => $filtro_tipo,
                'filtro_anio' => $filtro_anio,
                'filtro_alterno' => $filtro_alterno,
                'filtro_estado' => $filtro_estado,
                'orden' => $orden
            ]);

            $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
            $tabla .= '<p class="text-right small">Registros '.$reg_inicio.' - '.$reg_final.' de '.$total.'</p>';
            $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
        }

        return $tabla;
    }
    
    
    
    
    
    



public function paginador_cohorte_controlador2025($pagina, $registros, $vista, $estado_cohorte) {
    $pagina         = (int) mainModel::limpiar_cadena($pagina);
    $registros      = (int) mainModel::limpiar_cadena($registros);
    $estado_cohorte = mainModel::limpiar_cadena($estado_cohorte);
    $vista          = mainModel::limpiar_cadena($vista);

    $filtro_id     = $_GET['filtro_id']     ?? "";
    $filtro_tipo   = $_GET['filtro_tipo']   ?? "";
    $filtro_anio   = $_GET['filtro_anio']   ?? "";
    $filtro_estado = $_GET['filtro_estado'] ?? "";
    $orden         = $_GET['orden']         ?? "normal";

    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;
    $conexion = mainModel::conectar();

    // WHERE dinámico
    $where = "c.archivar_cohorte = 1";
    if ($estado_cohorte != "")   $where .= " AND c.estado_cohorte = '$estado_cohorte'";
    if ($filtro_id != "")        $where .= " AND c.id_cohorte = '$filtro_id'";
    if ($filtro_tipo != "")      $where .= " AND c.tipo_cohorte = '$filtro_tipo'";
    if ($filtro_anio != "")      $where .= " AND c.anio_cohorte = '$filtro_anio'";
    if ($filtro_estado != "")    $where .= " AND c.estado_cohorte = '$filtro_estado'";

    // Ordenamiento SQL
    $order_sql = match($orden) {
        "cursos"      => "ORDER BY total_cursos DESC",
        "estudiantes" => "ORDER BY total_estudiantes DESC",
        default       => "ORDER BY c.anio_cohorte DESC, c.numero_cohorte DESC"
    };

    // Consulta con agregados
    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS
            c.id_cohorte, c.numero_cohorte, c.anio_cohorte, c.tipo_cohorte,
            c.estado_cohorte, c.fecha_inicio, c.fecha_fin,
            c.pago_inscripcion, c.pago_modulo,
            COUNT(DISTINCT cv.id_curso_vigente) AS total_cursos,
            COUNT(DISTINCT i.id_inscripcion) AS total_estudiantes
        FROM cohorte c
        LEFT JOIN curso_vigente cv ON cv.id_cohorte_fk = c.id_cohorte AND cv.estado_curso_vigente = 1
        LEFT JOIN inscripcion i ON i.id_curso_vigente_fk = cv.id_curso_vigente AND i.archivada = 1
        WHERE $where
        GROUP BY c.id_cohorte
        $order_sql
        LIMIT $inicio, $registros
    ";

    // Carpeta por cargo
    $cargo = $_SESSION['cargo_spm'] ?? 1;
    $carpetasPorCargo = [
        1 => "admin",
        2 => "Control de estudio",
        3 => "administrador de pagos",
        4 => "Supervisor de cursos",
        5 => "Estudiante"
    ];
    $carpeta = $carpetasPorCargo[$cargo] ?? "admin";
    $vistaCompleta = urlencode($carpeta) . '/' . $vista;

    // Ejecutar consulta
    $datos    = $conexion->query($consulta)->fetchAll();
    $total    = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    // Construcción de tabla
    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>Id</th>
                    <th>Cohorte</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>F_inicio</th>
                    <th>F_fin</th>
                    <th>Inscripción</th>
                    <th>Módulos</th>';

    if ($cargo != 5) {
        $tabla .= '<th>Cursos</th><th>Inscritos</th>';
    }

    $tabla .= '<th>Ver periodos</th>';

    if ($cargo == 1 || $cargo == 4) {
        $tabla .= '<th>Editar</th><th>Archivar</th>';
    }

    $tabla .= '</tr></thead><tbody>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;

        foreach ($datos as $row) {
            $idEnc = mainModel::encryption($row['id_cohorte']);
            $nombre = $row['numero_cohorte'] . "." . substr($row['anio_cohorte'], -2);

            $tabla .= '<tr class="text-center">
                <td>' . $row['id_cohorte'] . '</td>
                <td>' . $nombre . '</td>
                <td>' . $row['tipo_cohorte'] . '</td>
                <td>' . $row['estado_cohorte'] . '</td>
                <td>' . date("d/m/Y", strtotime($row['fecha_inicio'])) . '</td>
                <td>' . date("d/m/Y", strtotime($row['fecha_fin'])) . '</td>
                <td>' . $row['pago_inscripcion'] . '$</td>
                <td>' . $row['pago_modulo'] . '$</td>';

            if ($cargo != 5) {
                $tabla .= '<td>' . $row['total_cursos'] . '</td>
                           <td>' . $row['total_estudiantes'] . '</td>';
            }

            $tabla .= '<td>
                <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list&filtro_id_cohorte=' . $row['id_cohorte'] . '&origen=cohorte" class="btn btn-info btn-sm" title="Ver cursos vigentes">
                    <i class="fas fa-eye"></i>
                </a>
            </td>';

            if ($cargo == 1 || $cargo == 4) {
                $tabla .= '<td>
                    <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cohorte2-update&cohorte_id_up=' . $idEnc . '" class="btn btn-success btn-sm">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </td>
                <td>
                    <form class="FormularioAjax" action="' . SERVERURL . 'ajax/cohorteAjax.php" method="POST" data-form="delete" data-entity="cohorte" autocomplete="off">
                        <input type="hidden" name="cohorte_id_archivar" value="' . $idEnc . '">
                        <button type="submit" class="btn btn-warning btn-sm">
                            <i class="fas fa-archive"></i>
                        </button>
                    </form>
                </td>';
            }

            $tabla .= '</tr>';
        }

        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="12">';
        $tabla .= ($total >= 1)
            ? '<a href="' . SERVERURL . 'index.php?views=' . $vistaCompleta . '&estado=' . $estado_cohorte . '" class="btn btn-raised btn-primary btn-sm">Haga clic aquí para recargar el listado</a>'
            : 'No hay cohortes registradas con estado ' . $estado_cohorte;
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    // Paginador con filtros persistentes
    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando cohortes ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';

        $queryParams = http_build_query([
            'estado' => $estado_cohorte,
            'filtro_id' => $filtro_id,
            'filtro_tipo' => $filtro_tipo,
            'filtro_anio' => $filtro_anio,
            'filtro_estado' => $filtro_estado,
            'orden' => $orden
        ]);

        $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }

    return $tabla;
    }



    //archivar cohorte
    /*--------- Controlador archivar cohorte ---------*/
    public function archivar_cohorte_controlador() {

        // Desencriptar y limpiar
        $id = mainModel::decryption($_POST['cohorte_id_archivar']);
        $id = mainModel::limpiar_cadena($id);

        // Verificar existencia de la cohorte
        $check_cohorte = mainModel::ejecutar_consulta_simple("SELECT id_cohorte FROM cohorte WHERE id_cohorte = '$id'");
        if ($check_cohorte->rowCount() <= 0) {
            echo json_encode([
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "Texto" => "La cohorte que intenta archivar no existe en el sistema",
                "Tipo" => "error"
            ]);
            exit();
        }


        $conexion = mainModel::conectar();

        try {
            $conexion->beginTransaction();

            // Marcar cohorte como archivada
            $ps_up_cohorte = $conexion->prepare("UPDATE cohorte SET archivar_cohorte = 0 WHERE id_cohorte = :id");
            $ps_up_cohorte->bindValue(':id', $id, PDO::PARAM_INT);
            $ps_up_cohorte->execute();

            // Obtener cursos vigentes relacionados
            $ps_get_cursos_vig = $conexion->prepare("SELECT id_curso_vigente FROM curso_vigente WHERE id_cohorte_fk = :id");
            $ps_get_cursos_vig->bindValue(':id', $id, PDO::PARAM_INT);
            $ps_get_cursos_vig->execute();

            // Preparar statements reutilizables
            $ps_up_cv = $conexion->prepare("UPDATE curso_vigente SET estado_curso_vigente = 0 WHERE id_curso_vigente = :idCV");
            $ps_get_secciones = $conexion->prepare("SELECT id_seccion FROM seccion WHERE id_curso_vigente_fk = :idCV");
            $ps_up_seccion = $conexion->prepare("UPDATE seccion SET archivar_seccion = 0 WHERE id_seccion = :idSec");
            $ps_get_evals = $conexion->prepare("SELECT id_evaluacion FROM evaluacion WHERE id_seccion = :idSec");
            $ps_up_eval = $conexion->prepare("UPDATE evaluacion SET archivar_evaluacion = 0 WHERE id_evaluacion = :idEval");

            // Ajustar según el nombre real de la columna en puntaje_estudiante
            // Cambia 'id_evaluacion' por el nombre correcto si tu esquema usa otro campo
            $ps_up_puntaje = $conexion->prepare("UPDATE puntaje_estudiante SET archivar_puntaje = 0 WHERE id_evaluacion = :idEval");

            // Actualizar observaciones en inscripciones (evita duplicados)
            $ps_up_insc = $conexion->prepare("
                UPDATE inscripcion
                SET observaciones = CONCAT(IFNULL(observaciones, ''), ' | Cohorte archivado o inactivo')
                WHERE id_seccion_fk = :idSec AND IFNULL(observaciones, '') NOT LIKE '%Cohorte archivado o inactivo%'
            ");

            while ($cv = $ps_get_cursos_vig->fetch(PDO::FETCH_ASSOC)) {
                $idCV = (int)$cv['id_curso_vigente'];
                $ps_up_cv->execute([':idCV' => $idCV]);

                $ps_get_secciones->execute([':idCV' => $idCV]);
                while ($sec = $ps_get_secciones->fetch(PDO::FETCH_ASSOC)) {
                    $idSec = (int)$sec['id_seccion'];
                    $ps_up_seccion->execute([':idSec' => $idSec]);

                    $ps_get_evals->execute([':idSec' => $idSec]);
                    while ($eval = $ps_get_evals->fetch(PDO::FETCH_ASSOC)) {
                        $idEval = (int)$eval['id_evaluacion'];
                        $ps_up_eval->execute([':idEval' => $idEval]);

                        // Ejecutar solo si la columna existe en tu esquema; ajustar nombre si es necesario
                        $ps_up_puntaje->execute([':idEval' => $idEval]);
                    }

                    $ps_up_insc->execute([':idSec' => $idSec]);
                }
            }

            $conexion->commit();

            echo json_encode([
                "Alerta" => "recargar",
                "Titulo" => "Cohorte archivada",
                "Texto" => "La cohorte y sus registros relacionados han sido archivados exitosamente",
                "Tipo" => "success"
            ]);
        } catch (Exception $e) {
            $conexion->rollBack();
            echo json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error al archivar cohorte",
                "Texto" => "No se pudo completar el proceso: " . $e->getMessage(),
                "Tipo" => "error"
            ]);
        }
    }



    //restaurar cohorte
public function restaurar_cohorte_controlador() {
    $id = mainModel::decryption($_POST['cohorte_id_restore']);
    $id = mainModel::limpiar_cadena($id);

    $check_cohorte = mainModel::ejecutar_consulta_simple("SELECT id_cohorte FROM cohorte WHERE id_cohorte = '$id'");
    if ($check_cohorte->rowCount() <= 0) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error",
            "Texto" => "La cohorte no existe",
            "Tipo" => "error"
        ]);
        exit();
    }

    $pdo = mainModel::conectar();
    try {
        $pdo->beginTransaction();

        $countsRestored = ['cohorte'=>0,'curso_vigente'=>0,'secciones'=>0,'evaluaciones'=>0,'puntajes'=>0];
        $countsOmitted = ['curso_vigente'=>0,'secciones'=>0,'evaluaciones'=>0,'puntajes'=>0];
        $omittedExamples = [];

        $pushExample = function($tipo,$id) use (&$omittedExamples) {
            if (!isset($omittedExamples[$tipo])) $omittedExamples[$tipo] = [];
            if (count($omittedExamples[$tipo]) < 10) $omittedExamples[$tipo][] = $id;
        };

        // Restaurar cohorte
        $ps_up_cohorte = $pdo->prepare("UPDATE cohorte SET archivar_cohorte = 1 WHERE id_cohorte = :id");
        $ps_up_cohorte->execute([':id' => $id]);
        $countsRestored['cohorte'] = 1;

        // Obtener cursos vigentes
        $ps_get_cursos_vig = $pdo->prepare("SELECT id_curso_vigente, id_curso_fk FROM curso_vigente WHERE id_cohorte_fk = :id");
        $ps_get_cursos_vig->execute([':id' => $id]);

        $ps_check_curso = $pdo->prepare("SELECT estado_curso FROM curso WHERE id_curso = :id");
        $ps_up_cv = $pdo->prepare("UPDATE curso_vigente SET estado_curso_vigente = 1 WHERE id_curso_vigente = :idCV");

        $ps_get_secciones = $pdo->prepare("SELECT id_seccion, ci_profesor FROM seccion WHERE id_curso_vigente_fk = :idCV");
        $ps_check_prof = $pdo->prepare("SELECT estado_profesor FROM profesor WHERE id_profesor = :idProf");
        $ps_up_seccion = $pdo->prepare("UPDATE seccion SET archivar_seccion = 1 WHERE id_seccion = :idSec");

        $ps_get_evals = $pdo->prepare("SELECT id_evaluacion FROM evaluacion WHERE id_seccion = :idSec");
        $ps_up_eval = $pdo->prepare("UPDATE evaluacion SET archivar_evaluacion = 1 WHERE id_evaluacion = :idEval");

        $ps_up_puntaje = $pdo->prepare("UPDATE puntaje_estudiante SET archivar_puntaje = 1 WHERE id_evaluacion = :idEval");
        $ps_check_insc = $pdo->prepare("SELECT archivada FROM inscripcion WHERE id_inscripcion = (SELECT id_inscripcion FROM puntaje_estudiante WHERE id_evaluacion = :idEval LIMIT 1)");

        $ps_up_insc = $pdo->prepare("
            UPDATE inscripcion
            SET observaciones = CONCAT(IFNULL(observaciones, ''), ' | Cohorte reactivado')
            WHERE id_seccion_fk = :idSec AND IFNULL(observaciones, '') NOT LIKE '%Cohorte reactivado%'
        ");

        while ($cv = $ps_get_cursos_vig->fetch(PDO::FETCH_ASSOC)) {
            $idCV = (int)$cv['id_curso_vigente'];
            $idCurso = (int)$cv['id_curso_fk'];

            $ps_check_curso->execute([':id' => $idCurso]);
            $cursoRow = $ps_check_curso->fetch(PDO::FETCH_ASSOC);
            $cursoActivo = $cursoRow && (int)$cursoRow['estado_curso'] === 1;

            if (! $cursoActivo) {
                $countsOmitted['curso_vigente'] += 1;
                $pushExample('curso_vigente', $idCV);
                continue;
            }

            $ps_up_cv->execute([':idCV' => $idCV]);
            $countsRestored['curso_vigente'] += 1;

            $ps_get_secciones->execute([':idCV' => $idCV]);
            while ($sec = $ps_get_secciones->fetch(PDO::FETCH_ASSOC)) {
                $idSec = (int)$sec['id_seccion'];
                $idProf = (int)$sec['ci_profesor'];

                $ps_check_prof->execute([':idProf' => $idProf]);
                $profRow = $ps_check_prof->fetch(PDO::FETCH_ASSOC);
                $profActivo = $profRow && (int)$profRow['estado_profesor'] === 1;

                if (! $profActivo) {
                    $countsOmitted['secciones'] += 1;
                    $pushExample('secciones', $idSec);
                    continue;
                }

                $ps_up_seccion->execute([':idSec' => $idSec]);
                $countsRestored['secciones'] += 1;

                $ps_get_evals->execute([':idSec' => $idSec]);
                while ($eval = $ps_get_evals->fetch(PDO::FETCH_ASSOC)) {
                    $idEval = (int)$eval['id_evaluacion'];

                    $ps_up_eval->execute([':idEval' => $idEval]);
                    $countsRestored['evaluaciones'] += 1;

                    $ps_check_insc->execute([':idEval' => $idEval]);
                    $inscRow = $ps_check_insc->fetch(PDO::FETCH_ASSOC);
                    $inscActiva = $inscRow && (int)$inscRow['archivada'] === 1;

                    if (! $inscActiva) {
                        $countsOmitted['puntajes'] += 1;
                        $pushExample('puntajes', $idEval);
                        continue;
                    }

                    $ps_up_puntaje->execute([':idEval' => $idEval]);
                    $countsRestored['puntajes'] += $ps_up_puntaje->rowCount() ?: 1;
                }

                $ps_up_insc->execute([':idSec' => $idSec]);
            }
        }
// id_profesor_fk
        $pdo->commit();

        //$texto = "La cohorte y sus registros relacionados han sido restaurados.";
        //$tipo = "success";
      //  if (array_sum($countsOmitted) > 0) {
    //        $texto = "Restauración parcial: algunos registros fueron omitidos por dependencias inactivas.";
  //          $tipo = "warning";
//        }

$totalOmitidos = array_sum($countsOmitted);
if ($totalOmitidos > 0) {
    $texto = "Curso restaurado, pero se omitieron: " .
        $countsOmitted['curso_vigente'] . " curso(s) vigente(s), " .
        $countsOmitted['secciones'] . " sección(es), " .
        $countsOmitted['evaluaciones'] . " evaluación(es), " .
        $countsOmitted['puntajes'] . " puntaje(s).";
    $tipo = "warning";
} else {
    $texto = "Cohorte restaurado exitosamente. Todos los registros relacionados fueron activados.";
    $tipo = "success";
}

        echo json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Cohorte restaurada",
            "Texto" => $texto,
            "Tipo" => $tipo,
            "restored" => $countsRestored,
            "omitted" => $countsOmitted,
            "examples" => $omittedExamples
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error al restaurar cohorte",
            "Texto" => "No se pudo completar el proceso: " . $e->getMessage(),
            "Tipo" => "error"
        ]);
    }
}



    ///paginador de archivados


public function paginador_cohorte_archivado_controlador($pagina, $registros, $vista) {
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $vista     = mainModel::limpiar_cadena($vista); // solo el nombre de la vista, ej: "cohorte-archived"

    $filtro_id    = $_GET['filtro_id']    ?? "";
    $filtro_tipo  = $_GET['filtro_tipo']  ?? "";
    $filtro_anio  = $_GET['filtro_anio']  ?? "";
    $orden        = $_GET['orden']        ?? "normal";

    $cargo = $_SESSION['cargo_spm'] ?? 1;
$carpetasPorCargo = [
    1 => "admin",
    2 => "Control de estudio",
    3 => "administrador de pagos",
    4 => "Supervisor de cursos",
    5 => "Estudiante"
];

$carpeta = $carpetasPorCargo[$cargo] ?? "admin";
        $vistaCompleta = urlencode($carpeta) . '/' . $vista;
    
    
    
    
    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;
    $conexion = mainModel::conectar();

    // WHERE dinámico
    $where = "archivar_cohorte = 0";
    if ($filtro_id != "")   $where .= " AND id_cohorte = '$filtro_id'";
    if ($filtro_tipo != "") $where .= " AND tipo_cohorte = '$filtro_tipo'";
    if ($filtro_anio != "") $where .= " AND anio_cohorte = '$filtro_anio'";

    // Ordenamiento
    $order_sql = "ORDER BY anio_cohorte DESC, numero_cohorte DESC";
    if ($orden == "cursos" || $orden == "estudiantes") {
        $order_sql = ""; // orden manual
    }

    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS * FROM cohorte
        WHERE $where
        $order_sql
        LIMIT $inicio, $registros
    ";

    $datos    = $conexion->query($consulta)->fetchAll();
    $total    = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    // Reordenamiento manual si aplica
    if ($orden == "cursos" || $orden == "estudiantes") {
        foreach ($datos as &$row) {
            $idCohorte = $row['id_cohorte'];
            $cursos = $conexion->query("SELECT id_curso_vigente FROM curso_vigente WHERE id_cohorte_fk = '$idCohorte' AND estado_curso_vigente = 0")->fetchAll();
            $row['total_cursos'] = count($cursos);

            $row['total_estudiantes'] = 0;
            foreach ($cursos as $curso) {
                $idCV = $curso['id_curso_vigente'];
                $row['total_estudiantes'] += (int)$conexion->query("SELECT COUNT(*) FROM inscripcion WHERE id_curso_vigente_fk = '$idCV'")->fetchColumn();
            }
        }

        usort($datos, function($a, $b) use ($orden) {
            $key = ($orden == "cursos") ? 'total_cursos' : 'total_estudiantes';
            return $b[$key] <=> $a[$key];
        });
    }

    // Renderizado de tabla
    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>Id</th>
                    <th>Cohorte</th>
                    <th>Tipo</th>
                    <th>P_inscripción</th>
                    <th>P_módulos</th>
                    <th>Cursos</th>
                    <th>Estudiantes</th>
                    <th>Ver periodos</th>
                    <th>Restaurar</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;
        foreach ($datos as $row) {
            $idEnc = mainModel::encryption($row['id_cohorte']);
            $nombre = $row['numero_cohorte'] . "." . $row['anio_cohorte'];

            $cursos = $conexion->query("SELECT id_curso_vigente FROM curso_vigente WHERE id_cohorte_fk = '{$row['id_cohorte']}' AND estado_curso_vigente = 0")->fetchAll();
            $totalCursos = count($cursos);

            $totalEstudiantes = 0;
            foreach ($cursos as $curso) {
                $idCV = $curso['id_curso_vigente'];
                $totalEstudiantes += (int)$conexion->query("SELECT COUNT(*) FROM inscripcion WHERE id_curso_vigente_fk = '$idCV'")->fetchColumn();
            }

            $tabla .= '
                <tr class="text-center">
                    <td>' . $row['id_cohorte'] . '</td>
                    <td>' . $nombre . '</td>
                    <td>' . $row['tipo_cohorte'] . '</td>
                    <td>' . $row['pago_inscripcion'] . '</td>
                    <td>' . $row['pago_modulo'] . '</td>
                    <td>' . $totalCursos . '</td>
                    <td>' . $totalEstudiantes . '</td>
                    <td>
        <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-archived&filtro_id_cohorte=' . $row['id_cohorte'] . '&origen=cohorte" class="btn btn-info btn-sm" title="Ver cursos archivados">
            <i class="fas fa-eye"></i>
        </a>

                    </td>
                    <td>
                        <form class="FormularioAjax" action="' . SERVERURL . 'ajax/cohorteAjax.php" method="POST" data-form="restore" autocomplete="off">
                            <input type="hidden" name="cohorte_id_restore" value="' . $idEnc . '">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-undo"></i> Restaurar
                            </button>
                        </form>
                    </td>
                </tr>';
        }
        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="8">';
        $tabla .= ($total >= 1)
		    ? '<a href="' . SERVERURL . 'index.php?views=' . $vistaCompleta . '" class="btn btn-raised btn-primary btn-sm">Haga clic aquí para recargar el listado</a>'
            : 'No hay cohortes archivadas que coincidan con los filtros';
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    // Paginador con filtros persistentes
    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando cohortes ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';

        $queryParams = http_build_query([
            'filtro_id' => $filtro_id,
            'filtro_tipo' => $filtro_tipo,
            'filtro_anio' => $filtro_anio,
            'orden' => $orden
        ]);

        
        $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
       // $urlCompleta = SERVERURL . "index.php?views=" . $vista . "&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }

    return $tabla;
}




/* no pasa filtro a next page
public function paginador_cohorte_archivado_controlador($pagina, $registros, $url){
    $pagina = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $url = SERVERURL . "index.php?views=" . mainModel::limpiar_cadena($url) . "&page=";

    $filtro_id = isset($_GET['filtro_id']) ? mainModel::limpiar_cadena($_GET['filtro_id']) : "";
    $filtro_tipo = isset($_GET['filtro_tipo']) ? mainModel::limpiar_cadena($_GET['filtro_tipo']) : "";
    $filtro_anio = isset($_GET['filtro_anio']) ? mainModel::limpiar_cadena($_GET['filtro_anio']) : "";
    $orden = isset($_GET['orden']) ? mainModel::limpiar_cadena($_GET['orden']) : "normal";

    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;
    $conexion = mainModel::conectar();

    // WHERE dinámico
    $where = "archivar_cohorte = 0";
    if ($filtro_id != "") {
        $where .= " AND id_cohorte = '$filtro_id'";
    }
    if ($filtro_tipo != "") {
        $where .= " AND tipo_cohorte = '$filtro_tipo'";
    }
    if ($filtro_anio != "") {
        $where .= " AND anio_cohorte = '$filtro_anio'";
    }

    // Ordenamiento
    $order_sql = "ORDER BY anio_cohorte DESC, numero_cohorte DESC";
    if ($orden == "cursos" || $orden == "estudiantes") {
        $order_sql = "";
    }

    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS * FROM cohorte
        WHERE $where
        $order_sql
        LIMIT $inicio, $registros
    ";

    $datos = $conexion->query($consulta)->fetchAll();
    $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    // Reordenamiento manual si aplica
    if ($orden == "cursos" || $orden == "estudiantes") {
        foreach ($datos as &$row) {
            $idCohorte = $row['id_cohorte'];
            $cursos = $conexion->query("SELECT id_curso_vigente FROM curso_vigente WHERE id_cohorte_fk = '$idCohorte' AND estado_curso_vigente = 0")->fetchAll();
            $row['total_cursos'] = count($cursos);

            $row['total_estudiantes'] = 0;
            foreach ($cursos as $curso) {
                $idCV = $curso['id_curso_vigente'];
                $row['total_estudiantes'] += (int)$conexion->query("SELECT COUNT(*) FROM inscripcion WHERE id_curso_vigente_fk = '$idCV'")->fetchColumn();
            }
        }

        usort($datos, function($a, $b) use ($orden) {
            $key = ($orden == "cursos") ? 'total_cursos' : 'total_estudiantes';
            return $b[$key] <=> $a[$key];
        });
    }

    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>ID</th>
                    <th>COHORTE</th>
                    <th>TIPO</th>
                    <th>INSCRIPCIÓN</th>
                    <th>MÓDULOS</th>
                    <th>CURSOS</th>
                    <th>ESTUDIANTES</th>
                    <th>RESTAURAR</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;
        foreach ($datos as $row) {
            $idEnc = mainModel::encryption($row['id_cohorte']);
            $nombre = $row['numero_cohorte'].".".$row['anio_cohorte'];

            // Cursos vigentes
            $cursos = $conexion->query("SELECT id_curso_vigente FROM curso_vigente WHERE id_cohorte_fk = '{$row['id_cohorte']}' AND estado_curso_vigente = 0")->fetchAll();
            $totalCursos = count($cursos);

            // Estudiantes + pagos
            $totalEstudiantes = 0;
            $totalInscripcion = 0;
            $totalModulo = 0;

            foreach ($cursos as $curso) {
                $idCV = $curso['id_curso_vigente'];
                $totalEstudiantes += (int)$conexion->query("SELECT COUNT(*) FROM inscripcion WHERE id_curso_vigente_fk = '$idCV'")->fetchColumn();


            }

            $tabla .= '
                <tr class="text-center">
                    <td>'.$row['id_cohorte'].'</td>
                    <td>'.$nombre.'</td>
                    <td>'.$row['tipo_cohorte'].'</td>
                    <td>'.$row['pago_inscripcion'].'</td>
                    <td>'.$row['pago_modulo'].'</td>
                    <td>'.$totalCursos.'</td>
                    <td>'.$totalEstudiantes.'</td>
                    <td>
                        <form class="FormularioAjax" action="' . SERVERURL . 'ajax/cohorteAjax.php" method="POST" data-form="restore" autocomplete="off">
                            <input type="hidden" name="cohorte_id_restore" value="' . $idEnc . '">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-undo"></i> Restaurar
                            </button>
                        </form>
                    </td>
                </tr>';
        }
        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="8">';
        $tabla .= ($total >= 1)
            ? '<a href="'.$url.'" class="btn btn-raised btn-primary btn-sm">Haga clic aquí para recargar el listado</a>'
            : 'No hay cohortes archivadas que coincidan con los filtros';
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando cohortes '.$reg_inicio.' al '.$reg_final.' de un total de '.$total.'</p>';
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $url, 7);
    }

    return $tabla;
}

*/

    /*
    public function paginador_cohorte_archivado_controlador($pagina, $registros, $url, $busqueda){
        //$pagina = mainModel::limpiar_cadena($pagina);
        //$registros = mainModel::limpiar_cadena($registros);

        $pagina = (int) mainModel::limpiar_cadena($pagina);
        $registros = (int) mainModel::limpiar_cadena($registros);

        $url = SERVERURL.mainModel::limpiar_cadena($url)."/";
        $busqueda = mainModel::limpiar_cadena($busqueda);
        $inicio = ($pagina>0) ? (($pagina*$registros)-$registros) : 0;

        if($busqueda!=""){
            $consulta = "SELECT SQL_CALC_FOUND_ROWS * FROM cohorte 
                        WHERE archivar_cohorte = 0 AND 
                        (numero_cohorte LIKE '%$busqueda%' OR anio_cohorte LIKE '%$busqueda%' OR tipo_cohorte LIKE '%$busqueda%')
                        ORDER BY anio_cohorte DESC, numero_cohorte DESC LIMIT $inicio,$registros";
        } else {
            $consulta = "SELECT SQL_CALC_FOUND_ROWS * FROM cohorte 
                        WHERE archivar_cohorte = 0 
                        ORDER BY anio_cohorte DESC, numero_cohorte DESC LIMIT $inicio,$registros";
        }

        $conexion = mainModel::conectar();
        $datos = $conexion->query($consulta)->fetchAll();
        $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
        $Npaginas = ceil($total/$registros);

        $tabla = '<div class="table-responsive"><table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>ID</th>
                    <th>COHORTE</th>
                    <th>TIPO</th>
                    <th>ESTADO</th>
                    <th>TRIMESTRE</th>
                    <th>AÑO</th>
                    <th>RESTAURAR</th>
                </tr>
            </thead>
            <tbody>';

        if($total>=1 && $pagina<=$Npaginas){
            $reg_inicio = $inicio+1;
            foreach($datos as $row){
                $nombre = $row['numero_cohorte'].".".substr($row['anio_cohorte'],-2);
                $tabla .= '
                    <tr class="text-center">
                        <td>'.$row['id_cohorte'].'</td>
                        <td>'.$nombre.'</td>
                        <td>'.$row['tipo_cohorte'].'</td>
                        <td>'.$row['estado_cohorte'].'</td>
                        <td>'.$row['numero_cohorte'].'</td>
                        <td>'.$row['anio_cohorte'].'</td>
                        <td>
                            <form class="FormularioAjax" action="' . SERVERURL . 'ajax/cohorteAjax.php" method="POST" data-form="restore" autocomplete="off">
                                <input type="hidden" name="cohorte_id_restore" value="' . mainModel::encryption($row['id_cohorte']) . '">
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-undo"></i> Restaurar
                                </button>
                            </form>
                        </td>
                    </tr>';
            }
            $reg_final = $reg_inicio + count($datos) - 1;
        }else{
            $tabla .= '<tr class="text-center"><td colspan="6">No hay cohortes archivadas en el sistema</td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        if($total>=1 && $pagina<=$Npaginas){
            $tabla .= '<p class="text-right">Mostrando cohorte '.$reg_inicio.' al '.$reg_final.' de un total de '.$total.'</p>';
            $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $url, 7);
        }

        return $tabla;
    }
*/















    //actualizar cohorte
    public function actualizar_cursos_cohorte_controlador(){
        $id = mainModel::limpiar_cadena($_POST['cohorte_id_cursos_up']);
        $cursos = $_POST['cursos_seleccionados'] ?? [];

        try {
            $conexion = mainModel::conectar();
            $conexion->beginTransaction();

            // Eliminar asociaciones anteriores
            $del = $conexion->prepare("DELETE FROM curso_cohorte WHERE id_cohorte_fk = :ID");
            $del->bindParam(":ID", $id);
            $del->execute();

            // Insertar nuevas asociaciones
            $ins = $conexion->prepare("INSERT INTO curso_cohorte(id_curso_fk, id_cohorte_fk) VALUES(:Curso, :Cohorte)");
            foreach($cursos as $curso_id){
                $ins->bindParam(":Curso", $curso_id);
                $ins->bindParam(":Cohorte", $id);
                $ins->execute();
            }

            $conexion->commit();

            $alerta = [
                "Alerta" => "recargar",
                "Titulo" => "Actualización exitosa",
                "Texto" => "Los cursos asociados fueron actualizados correctamente.",
                "Tipo" => "success"
            ];

        } catch (Exception $e) {
            $conexion->rollBack();
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Error al actualizar",
                "Texto" => "No se pudo completar la asignación de cursos",
                "Tipo" => "error"
            ];
        }

        echo json_encode($alerta);
    }//fin controlador


    //otra funcion de traer cursos para ser o no ser asignados a cohorte
    public function cargar_cursos_para_cohorte_controlador($id){
        $conexion = mainModel::conectar();

        // Obtener tipo de cohorte
        $tipo_stmt = $conexion->prepare("SELECT tipo_cohorte FROM cohorte WHERE id_cohorte = :ID LIMIT 1");
        $tipo_stmt->bindParam(":ID", $id);
        $tipo_stmt->execute();

        if($tipo_stmt->rowCount()!=1){
            return json_encode(["error" => "Cohorte no encontrada"]);
        }

        $tipo = $tipo_stmt->fetch()['tipo_cohorte'];

        // Obtener cursos disponibles de ese tipo
        require_once "../modelos/cursoModelo.php";
        $curso_stmt = cursoModelo::obtener_cursos_por_tipo($tipo);
        $cursos = $curso_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener cursos ya asignados
        $asignados_stmt = $conexion->prepare("SELECT id_curso_fk FROM curso_cohorte WHERE id_cohorte_fk = :ID");
        $asignados_stmt->bindParam(":ID", $id);
        $asignados_stmt->execute();
        $seleccionados = $asignados_stmt->fetchAll(PDO::FETCH_COLUMN);

        // Preparar respuesta
        $respuesta = [
            "cursos" => [],
            "seleccionados" => $seleccionados
        ];

        foreach($cursos as $curso){
            $respuesta["cursos"][] = [
                "id" => $curso['id_curso'],
                "nombre" => $curso['nombre_curso']
            ];
        }

        return json_encode($respuesta);
    }// fin controlador de trar cursos a cohorte


    //traer cohortes para cohorte-curso-view
    public function listar_cohortes_controlador(){
        $conexion = mainModel::conectar();

        $consulta = $conexion->prepare("
            SELECT id_cohorte, numero_cohorte, anio_cohorte, tipo_cohorte 
            FROM cohorte 
            WHERE archivar_cohorte = 1 
            ORDER BY anio_cohorte DESC, numero_cohorte DESC
        ");

        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }





    public function obtener_cohortes_vigentes_controlador() {
        $conexion = mainModel::conectar();


        $consulta = $conexion->prepare("
            SELECT id_cohorte, numero_cohorte, anio_cohorte, tipo_cohorte
            FROM cohorte
            WHERE (estado_cohorte = 'Vigente' OR estado_cohorte = 'Próximo')
            AND archivar_cohorte = 1
            ORDER BY anio_cohorte DESC, numero_cohorte DESC
        ");

        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }



/*
        $consulta = $conexion->prepare("
            SELECT id_cohorte, numero_cohorte, anio_cohorte, tipo_cohorte
            FROM cohorte
            WHERE estado_cohorte = 'Vigente'
            ORDER BY anio_cohorte DESC, numero_cohorte DESC
        ");*/

/*
        if($f_mod1 && $f_mod2){
            $dias1 = (strtotime($f_mod2) - strtotime($f_mod1)) / 86400;
            if($dias1 < 28){
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Diferencia insuficiente",
                    "Texto" => "Debe haber al menos 28 días entre el pago del módulo 1 y el módulo 2.",
                    "Tipo" => "error"
                ]);
            }
        }

        if($f_mod2 && $f_mod3){
            $dias2 = (strtotime($f_mod3) - strtotime($f_mod2)) / 86400;
            if($dias2 < 28){
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Diferencia insuficiente",
                    "Texto" => "Debe haber al menos 28 días entre el pago del módulo 2 y el módulo 3.",
                    "Tipo" => "error"
                ]);
            }
        }


*/


//de la vista de curso de curso vigente
/*
public function agregar_curso_vigente_controlador_cv() {
    $idCursoEncriptado = $_POST['curso_id_fk_reg'] ?? "";
    $idCohorte = $_POST['id_cohorte_fk_reg'] ?? "";
    $oferta = $_POST['oferta_reg'] ?? "";
    $idProfesor = $_POST['id_profesor_fk_reg'] ?? "";

    // Validaciones básicas
    if ($idCursoEncriptado == "" || $idCohorte == "" || $oferta === "" || $idProfesor == "") {
        return [
            "status" => "error",
            "mensaje" => "Todos los campos son obligatorios."
        ];
    }

    $idCurso = mainModel::decryption($idCursoEncriptado);
    $idCurso = mainModel::limpiar_cadena($idCurso);
    $idCohorte = mainModel::limpiar_cadena($idCohorte);
    $oferta = mainModel::limpiar_cadena($oferta);
    $idProfesor = mainModel::limpiar_cadena($idProfesor);

    // Estructura compatible con el modelo
    $curso = [
        "id_curso_fk" => $idCurso,
        "oferta" => $oferta,
        "id_profesor_fk" => $idProfesor
    ];

    require_once "../modelos/cohorteModelo.php";
    $resultado = cohorteModelo::agregar_cursos_vigentes_modelo($idCohorte, [$curso]);

    if ($resultado > 0) {
        return [
            "status" => "success",
            "mensaje" => "Curso vigente registrado correctamente."
        ];
    } else {
        return [
            "status" => "error",
            "mensaje" => "No se pudo registrar el curso vigente."
        ];
    }
}
*/

public function agregar_curso_vigente_controlador_cv() {
    $idCursoEncriptado = $_POST['curso_id_fk_reg'] ?? "";
    $idCohorte = $_POST['id_cohorte_fk_reg'] ?? "";
    //$oferta = $_POST['oferta_reg'] ?? "";
//        "oferta" => $oferta,
  //
    //
    
    $idProfesor = $_POST['id_profesor_fk_reg'] ?? "";
    $dia = $_POST['seccion_dia_reg'] ?? "";

    // Validaciones básicas
    if ($idCursoEncriptado == "" || $idCohorte == "" || $idProfesor == "" || $dia == "") {
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Datos incompletos",
            "Texto" => "Todos los campos son obligatorios.",
            "Tipo" => "error"
        ];
        return json_encode($alerta);
    }

    $idCurso=$idCursoEncriptado;
    //$idCurso = mainModel::decryption($idCursoEncriptado);
    //$idCurso = mainModel::limpiar_cadena($idCurso);

    $idCohorte = mainModel::limpiar_cadena($idCohorte);
  //  $oferta = mainModel::limpiar_cadena($oferta);
    $idProfesor = mainModel::limpiar_cadena($idProfesor);

    // Estructura compatible con el modelo
    $curso = [
        "id_curso_fk" => $idCurso,
        "id_profesor_fk" => $idProfesor,
        "dia" => $dia
    ];

    //require_once "../modelos/cohorteModelo.php";
    $resultado = cohorteModelo::agregar_cursos_vigentes_modelo($idCohorte, [$curso]);

    if ($resultado > 0) {
        $alerta = [
            "Alerta" => "recargar",
            "Titulo" => "Periodo de curso registrado",
            "Texto" => "El periodo de curso fue creado exitosamente.",
            "Tipo" => "success"
        ];
    } else {
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Error inesperado",
            "Texto" => "No se pudo registrar el Periodo de curso.",
            "Tipo" => "error"
        ];
    }

    return json_encode($alerta);
}







// para course vigente update
public function actualizar_curso_vigente_controlador_cv() {
    $idCursoVigenteEncriptado = $_POST['curso_vigente_id_up'] ?? "";
   // $oferta = $_POST['oferta_up'] ?? "";
    $idProfesor = $_POST['id_profesor_fk_up'] ?? "";

    // Validaciones básicas
    if ($idCursoVigenteEncriptado == "" || $idProfesor == "") {
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Datos incompletos",
            "Texto" => "Todos los campos son obligatorios.",
            "Tipo" => "error"
        ]);


    }

    $idCursoVigente = mainModel::decryption($idCursoVigenteEncriptado);
    $idCursoVigente = mainModel::limpiar_cadena($idCursoVigente);
    //$oferta = mainModel::limpiar_cadena($oferta);
    $idProfesor = mainModel::limpiar_cadena($idProfesor);

    //require_once "../modelos/cohorteModelo.php";
    $actualizado = cohorteModelo::actualizar_curso_vigente_y_seccion_modelo_cv($idCursoVigenteEncriptado, $idProfesor);

    if ($actualizado) {
    
        return json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Curso actualizado",
            "Texto" => "El el docente fue actualizado correctamente.",
            "Tipo" => "success"
        ]);
    } else {
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error inesperado",
            "Texto" => "No se pudo actualizar el periodo.",
            "Tipo" => "error"
        ]);
    }

}


    
    public function obtener_cohortes_de_cv($idCurso) {
    $pdo = mainModel::conectar();

    // Obtener tipo del curso
    $stmt = $pdo->prepare("SELECT tipo_curso FROM curso WHERE id_curso = :id");
    $stmt->execute([':id' => $idCurso]);
    $curso = $stmt->fetch();

    if (!$curso) return [];

    $tipo = $curso['tipo_curso'];

    // Buscar cohortes del mismo tipo y estado, excluyendo los ya ligados al curso
    $stmt = $pdo->prepare("
        SELECT id_cohorte, numero_cohorte, anio_cohorte, estado_cohorte, tipo_cohorte
        FROM cohorte
        WHERE tipo_cohorte = :tipo
          AND estado_cohorte IN ('Vigente', 'Próximo')
          AND id_cohorte NOT IN (
              SELECT id_cohorte_fk
              FROM curso_vigente
              WHERE id_curso_fk = :id_curso
          )
        ORDER BY anio_cohorte DESC, numero_cohorte DESC
    ");
    $stmt->bindParam(":tipo", $tipo);
    $stmt->bindParam(":id_curso", $idCurso);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    
    



/*
    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS 
            cv.id_curso_vigente, cu.nombre_curso, cu.tipo_curso, 
            c.numero_cohorte, c.anio_cohorte,
            cv.id_curso_fk, 
            cv.id_cohorte_fk,
            cv.oferta
        FROM curso_vigente cv
        INNER JOIN curso cu ON cv.id_curso_fk = cu.id_curso
        INNER JOIN cohorte c ON cv.id_cohorte_fk = c.id_cohorte
        WHERE $where AND cv.estado_curso_vigente=0
        ORDER BY c.anio_cohorte DESC, c.numero_cohorte DESC
        LIMIT $inicio, $registros
    ";
*/
//general










public function paginador_curso_vigente_archivado_controlador($pagina, $registros, $vista) {
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $vista     = mainModel::limpiar_cadena($vista); // solo el nombre de la vista, ej: "course-vigente-archived"

    $filtro_tipo   = $_GET['filtro_tipo']   ?? "";
    $filtro_anio   = $_GET['filtro_anio']   ?? "";
    $filtro_curso  = $_GET['filtro_curso']  ?? "";
    $filtro_id_cv  = $_GET['filtro_id_cv']  ?? "";
    $orden         = $_GET['orden']         ?? "default";

    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

    $conexion = mainModel::conectar();

    // WHERE dinámico
    $where = "1=1";
    if ($filtro_tipo != "")   $where .= " AND cu.tipo_curso = '$filtro_tipo'";
    if ($filtro_id_cv != "")  $where .= " AND cv.id_curso_vigente = '$filtro_id_cv'";
    if ($filtro_anio != "")   $where .= " AND c.anio_cohorte = '$filtro_anio'";
    if ($filtro_curso != "")  $where .= " AND cu.id_curso = '$filtro_curso'";

    // Ordenamiento SQL
    $order_sql = "ORDER BY c.anio_cohorte DESC, c.numero_cohorte DESC";
    if ($orden == "nombre") {
        $order_sql = "ORDER BY cu.nombre_curso ASC";
    } elseif ($orden == "estudiantes") {
        $order_sql = ""; // se ordena manualmente después
    }

    // Consulta principal
    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS  
            cv.id_curso_vigente, cu.nombre_curso, cu.tipo_curso, 
            c.numero_cohorte, c.anio_cohorte,
            cv.id_curso_fk, 
            cv.id_cohorte_fk
        FROM curso_vigente cv
        INNER JOIN curso cu ON cv.id_curso_fk = cu.id_curso
        INNER JOIN cohorte c ON cv.id_cohorte_fk = c.id_cohorte
        WHERE $where AND cv.estado_curso_vigente = 0
        $order_sql
        LIMIT $inicio, $registros
    ";

    $datos    = $conexion->query($consulta)->fetchAll();
    $total    = (int)$conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    // Ordenamiento manual por estudiantes
    if ($orden == "estudiantes") {
        foreach ($datos as &$row) {
            $idCV = $row['id_curso_vigente'];
            $row['total_estudiantes'] = (int)$conexion->query("SELECT COUNT(*) FROM inscripcion WHERE id_curso_vigente_fk = '$idCV'")->fetchColumn();
        }

        usort($datos, fn($a, $b) => $b['total_estudiantes'] <=> $a['total_estudiantes']);
    }

    // Renderizado de tabla
    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>Id</th>
                    <th>Curso</th>
                    <th>Tipo</th>
                    <th>Cohorte</th>
                    <th>Cohorte y curso archivado</th>
                    <th>Secciones</th>
                    <th>Evaluaciones</th>
                    <th>Estudiantes</th>
                    <th>Restaurar</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;

        foreach ($datos as $row) {
            $idCV      = $row['id_curso_vigente'];
            $idCurso   = $row['id_curso_fk'];
            $idCohorte = $row['id_cohorte_fk'];

            // Estado archivado
            $estadoCurso      = $conexion->query("SELECT estado_curso FROM curso WHERE id_curso = '$idCurso'")->fetchColumn();
            $archivadoCohorte = $conexion->query("SELECT archivar_cohorte FROM cohorte WHERE id_cohorte = '$idCohorte'")->fetchColumn();
/*
            $estadoArchivo = ($estadoCurso == 0 && $archivadoCohorte == 0) ? "sí"
                            : ($estadoCurso == 0) ? "curso archivado"
                            : ($archivadoCohorte == 0) ? "cohorte archivado"
                            : "no";
*/

            if ($estadoCurso == 0 && $archivadoCohorte == 0) {
                $estadoArchivo = "sí";
            } elseif ($estadoCurso == 0) {
                $estadoArchivo = "curso archivado";
            } elseif ($archivadoCohorte == 0) {
                $estadoArchivo = "cohorte archivado";
            } else {
                $estadoArchivo = "no";
            }


            // Secciones
            $secciones         = $conexion->query("SELECT id_seccion FROM seccion WHERE id_curso_vigente_fk = '$idCV'")->fetchAll();
            $conteoSecciones   = count($secciones);

            // Evaluaciones
            $conteoEvaluaciones = 0;
            foreach ($secciones as $sec) {
                $idSec = $sec['id_seccion'];
                $conteoEvaluaciones += (int)$conexion->query("SELECT COUNT(*) FROM evaluacion WHERE id_seccion = '$idSec'")->fetchColumn();
            }

            // Estudiantes
            $conteoEstudiantes = $row['total_estudiantes'] ?? (int)$conexion->query("SELECT COUNT(*) FROM inscripcion WHERE id_curso_vigente_fk = '$idCV'")->fetchColumn();

            $tabla .= '
                <tr class="text-center">
                    <td>' . $idCV . '</td>
                    <td>' . $row['nombre_curso'] . '</td>
                    <td>' . $row['tipo_curso'] . '</td>
                    <td>' . $row['numero_cohorte'] . '.' . $row['anio_cohorte'] . '</td>
                    <td>' . $estadoArchivo . '</td>
                    <td>' . $conteoSecciones . '</td>
                   
                    <td>' . $conteoEvaluaciones . '</td>
                    <td>' . $conteoEstudiantes . '</td>
                    <td>
                        <form class="FormularioAjax" action="' . SERVERURL . 'ajax/cursoVigenteAjax.php" method="POST" data-form="restore" autocomplete="off">
                            <input type="hidden" name="curso_vigente_id_restore" value="' . mainModel::encryption($idCV) . '">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-undo"></i> Restaurar
                            </button>
                        </form>
                    </td>
                </tr>';
        }

        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="10">';
        $tabla .= ($total >= 1)
            ? '<a href="' . SERVERURL . 'index.php?views=' . $vista . '" class="btn btn-raised btn-primary btn-sm">Haga clic aquí para recargar el listado</a>'
            : 'No hay cursos vigentes archivados que coincidan con los filtros';
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    // Paginador con filtros persistentes
    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando curso vigente ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';

        $queryParams = http_build_query([
            'filtro_tipo' => $filtro_tipo,
            'filtro_anio' => $filtro_anio,
            'filtro_curso' => $filtro_curso,
            'filtro_id_cv' => $filtro_id_cv,
            'orden' => $orden
        ]);

        $urlCompleta = SERVERURL . "index.php?views=" . $vista . "&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }

    return $tabla;
}



/* no tiene filtros pa pagina siguiente
public function paginador_curso_vigente_archivado_controlador($pagina, $registros, $url) {
    $pagina = mainModel::limpiar_cadena($pagina);
    $registros = mainModel::limpiar_cadena($registros);
    $url = mainModel::limpiar_cadena($url);
    $url = SERVERURL . "index.php?views=" . $url . "&page=";

    $filtro_tipo = isset($_GET['filtro_tipo']) ? mainModel::limpiar_cadena($_GET['filtro_tipo']) : "";
    $filtro_anio = isset($_GET['filtro_anio']) ? mainModel::limpiar_cadena($_GET['filtro_anio']) : "";
    $filtro_curso = isset($_GET['filtro_curso']) ? mainModel::limpiar_cadena($_GET['filtro_curso']) : "";
    $filtro_id_cv = isset($_GET['filtro_id_cv']) ? mainModel::limpiar_cadena($_GET['filtro_id_cv']) : "";
    $orden = isset($_GET['orden']) ? mainModel::limpiar_cadena($_GET['orden']) : "default";

    $pagina = ($pagina > 0) ? (int)$pagina : 1;
    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

    $conexion = mainModel::conectar();

    // WHERE dinámico
    $where = "1=1";
    if ($filtro_tipo != "") {
        $where .= " AND cu.tipo_curso = '$filtro_tipo'";
    }
    if ($filtro_id_cv != "") {
        $where .= " AND cv.id_curso_vigente = '$filtro_id_cv'";
    }
    if ($filtro_anio != "") {
        $where .= " AND c.anio_cohorte = '$filtro_anio'";
    }
    if ($filtro_curso != "") {
        $where .= " AND cu.id_curso = '$filtro_curso'";
    }

    // Ordenamiento SQL
    $order_sql = "ORDER BY c.anio_cohorte DESC, c.numero_cohorte DESC";
    if ($orden == "nombre") {
        $order_sql = "ORDER BY cu.nombre_curso ASC";
    } elseif ($orden == "estudiantes") {
        $order_sql = ""; // se ordena manualmente después
    }

    // Consulta principal
    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS  
            cv.id_curso_vigente, cu.nombre_curso, cu.tipo_curso, 
            c.numero_cohorte, c.anio_cohorte,
            cv.id_curso_fk, 
            cv.id_cohorte_fk,
            cv.oferta
        FROM curso_vigente cv
        INNER JOIN curso cu ON cv.id_curso_fk = cu.id_curso
        INNER JOIN cohorte c ON cv.id_cohorte_fk = c.id_cohorte
        WHERE $where AND cv.estado_curso_vigente = 0
        $order_sql
        LIMIT $inicio, $registros
    ";

    $datos = $conexion->query($consulta)->fetchAll();
    $total = (int)$conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    // Ordenamiento manual por estudiantes
    if ($orden == "estudiantes") {
        foreach ($datos as &$row) {
            $idCV = $row['id_curso_vigente'];
            $row['total_estudiantes'] = (int)$conexion->query("SELECT COUNT(*) FROM inscripcion WHERE id_curso_vigente_fk = '$idCV'")->fetchColumn();
        }

        usort($datos, function($a, $b) {
            return $b['total_estudiantes'] <=> $a['total_estudiantes'];
        });
    }

    // Renderizado de tabla
    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>Id</th>
                    <th>Curso</th>
                    <th>Tipo</th>
                    <th>Cohorte</th>
                    <th>Cohorte y curso archivado</th>
                    <th>Secciones</th>
                    <th>Oferta</th>
                    <th>Evaluaciones</th>
                    <th>Estudiantes</th>
                    <th>Restaurar</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;

        foreach ($datos as $row) {
            $idCV = $row['id_curso_vigente'];
            $idCurso = $row['id_curso_fk'];
            $idCohorte = $row['id_cohorte_fk'];

            // Estado archivado
            $estadoCurso = $conexion->query("SELECT estado_curso FROM curso WHERE id_curso = '$idCurso'")->fetchColumn();
            $archivadoCohorte = $conexion->query("SELECT archivar_cohorte FROM cohorte WHERE id_cohorte = '$idCohorte'")->fetchColumn();

            if ($estadoCurso == 0 && $archivadoCohorte == 0) {
                $estadoArchivo = "sí";
            } elseif ($estadoCurso == 0) {
                $estadoArchivo = "curso archivado";
            } elseif ($archivadoCohorte == 0) {
                $estadoArchivo = "cohorte archivado";
            } else {
                $estadoArchivo = "no";
            }

            // Secciones
            $secciones = $conexion->query("SELECT id_seccion FROM seccion WHERE id_curso_vigente_fk = '$idCV'")->fetchAll();
            $conteoSecciones = count($secciones);

            // Evaluaciones
            $conteoEvaluaciones = 0;
            foreach ($secciones as $sec) {
                $idSec = $sec['id_seccion'];
                $conteoEvaluaciones += (int)$conexion->query("SELECT COUNT(*) FROM evaluacion WHERE id_seccion = '$idSec'")->fetchColumn();
            }

            // Estudiantes
            $conteoEstudiantes = isset($row['total_estudiantes']) ? $row['total_estudiantes'] : (int)$conexion->query("SELECT COUNT(*) FROM inscripcion WHERE id_curso_vigente_fk = '$idCV'")->fetchColumn();

            $tabla .= '
                <tr class="text-center">
                    <td>' . $idCV . '</td>
                    <td>' . $row['nombre_curso'] . '</td>
                    <td>' . $row['tipo_curso'] . '</td>
                    <td>' . $row['numero_cohorte'] . '.' . $row['anio_cohorte'] . '</td>
                    <td>' . $estadoArchivo . '</td>
                    <td>' . $conteoSecciones . '</td>
                    <td>' . $row['oferta'] . '</td>
                    <td>' . $conteoEvaluaciones . '</td>
                    <td>' . $conteoEstudiantes . '</td>
                    <td>
                        <form class="FormularioAjax" action="' . SERVERURL . 'ajax/cursoVigenteAjax.php" method="POST" data-form="restore" autocomplete="off">
                            <input type="hidden" name="curso_vigente_id_restore" value="' . mainModel::encryption($idCV) . '">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-undo"></i> Restaurar
                            </button>
                        </form>
                    </td>
                </tr>';
        }

        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="10">';
        $tabla .= ($total >= 1)
            ? '<a href="' . $url . '" class="btn btn-raised btn-primary btn-sm">Haga clic aquí para recargar el listado</a>'
            : 'No hay cursos vigentes archivados que coincidan con los filtros';
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando curso vigente ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $url, 7);
    }

    return $tabla;
}
*/




// Archivar curso vigente
public function archivar_curso_vigente_controlador() {

    // 1) Validar y normalizar entrada
    if (!isset($_POST['curso_vigente_id_archive'])) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Ocurrió un error inesperado",
            "Texto" => "Falta el identificador del curso vigente a archivar",
            "Tipo" => "error"
        ]);
        exit();
    }

    $id = mainModel::decryption($_POST['curso_vigente_id_archive']);
    $id = mainModel::limpiar_cadena($id);

    if (!ctype_digit((string)$id) || (int)$id <= 0) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Entrada inválida",
            "Texto" => "Identificador de curso inválido",
            "Tipo" => "error"
        ]);
        exit();
    }

    // 2) Verificar existencia del curso_vigente
    $check = mainModel::ejecutar_consulta_simple("SELECT id_curso_vigente FROM curso_vigente WHERE id_curso_vigente = '$id'");
    if ($check->rowCount() <= 0) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Ocurrió un error inesperado",
            "Texto" => "El periodo que intenta archivar no existe en el sistema",
            "Tipo" => "error"
        ]);
        exit();
    }

    $conexion = mainModel::conectar();

    try {
        $conexion->beginTransaction();

        // 3) Marcar curso_vigente como archivado (ajusta el nombre de columna si difiere)
        $ps_up_cv = $conexion->prepare("UPDATE curso_vigente SET estado_curso_vigente = 0 WHERE id_curso_vigente = :idCV");
        $ps_up_cv->bindValue(':idCV', $id, PDO::PARAM_INT);
        $ps_up_cv->execute();

        // 4) Obtener secciones relacionadas
        $ps_get_secciones = $conexion->prepare("SELECT id_seccion FROM seccion WHERE id_curso_vigente_fk = :idCV");
        $ps_get_secciones->bindValue(':idCV', $id, PDO::PARAM_INT);
        $ps_get_secciones->execute();

        // 5) Preparar statements reutilizables
        $ps_up_seccion = $conexion->prepare("UPDATE seccion SET archivar_seccion = 0 WHERE id_seccion = :idSec");
        $ps_get_evals = $conexion->prepare("SELECT id_evaluacion FROM evaluacion WHERE id_seccion = :idSec");
        $ps_up_eval = $conexion->prepare("UPDATE evaluacion SET archivar_evaluacion = 0 WHERE id_evaluacion = :idEval");

        // Ajusta el nombre del campo en puntaje_estudiante si tu esquema usa otro
        $ps_up_puntaje = $conexion->prepare("UPDATE puntaje_estudiante SET archivar_puntaje = 0 WHERE id_evaluacion = :idEval");

        $ps_up_insc = $conexion->prepare("
            UPDATE inscripcion
            SET observaciones = CONCAT(IFNULL(observaciones, ''), ' | Periodo de Curso archivado o inactivo')
            WHERE id_seccion_fk = :idSec AND IFNULL(observaciones, '') NOT LIKE '%Periodo de Curso archivado o inactivo%'
        ");

        // 6) Recorrer secciones -> evaluaciones -> actualizaciones
        while ($sec = $ps_get_secciones->fetch(PDO::FETCH_ASSOC)) {
            $idSec = (int)$sec['id_seccion'];

            // Archivar seccion
            $ps_up_seccion->execute([':idSec' => $idSec]);

            // Obtener evaluaciones de la sección
            $ps_get_evals->execute([':idSec' => $idSec]);
            while ($eval = $ps_get_evals->fetch(PDO::FETCH_ASSOC)) {
                $idEval = (int)$eval['id_evaluacion'];

                // Archivar evaluación
                $ps_up_eval->execute([':idEval' => $idEval]);

                // Archivar puntajes relacionados (si la columna existe y aplica)
                $ps_up_puntaje->execute([':idEval' => $idEval]);
            }

            // Actualizar observaciones en inscripciones relacionadas a la sección
            $ps_up_insc->execute([':idSec' => $idSec]);
        }

        $conexion->commit();

        echo json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Periodo archivado",
            "Texto" => "El periodo y sus registros relacionados han sido archivados exitosamente",
            "Tipo" => "success"
        ]);
    } catch (Exception $e) {
        $conexion->rollBack();
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error al archivar curso",
            "Texto" => "No se pudo completar el proceso: " . $e->getMessage(),
            "Tipo" => "error"
        ]);
    }
}








public function restaurar_curso_vigente_controlador() {
    // 1 Validar input
    if (!isset($_POST['curso_vigente_id_restore'])) {
        echo json_encode(["Alerta"=>"simple","Titulo"=>"Error","Texto"=>"Falta id","Tipo"=>"error"]); exit;
    }
    $id = mainModel::decryption($_POST['curso_vigente_id_restore']);
    $id = mainModel::limpiar_cadena($id);
    if (!ctype_digit((string)$id) || (int)$id <= 0) {
        echo json_encode(["Alerta"=>"simple","Titulo"=>"Entrada inválida","Texto"=>"Id inválido","Tipo"=>"error"]); exit;
    }

    // 2 Verificar existencia
    $check = mainModel::ejecutar_consulta_simple("SELECT id_curso_vigente, id_curso_fk, id_cohorte_fk FROM curso_vigente WHERE id_curso_vigente = '$id'");
    if ($check->rowCount() <= 0) {
        echo json_encode(["Alerta"=>"simple","Titulo"=>"No existe","Texto"=>"Curso vigente no encontrado","Tipo"=>"error"]); exit;
    }
    $cursoV = $check->fetch(PDO::FETCH_ASSOC);

    $pdo = mainModel::conectar();
    try {
        $pdo->beginTransaction();

        // Preparar contadores y arrays de omitted (no listamos todo, solo counts y ejemplo ids)
        $countsRestored = ['curso_vigente'=>0,'secciones'=>0,'evaluaciones'=>0,'puntajes'=>0];
        $countsOmitted = ['curso_vigente'=>0,'secciones'=>0,'evaluaciones'=>0,'puntajes'=>0];
        $omittedExamples = []; // {tipo: [ids...]} máximo 10 por tipo

        // Helper para push ejemplo limitado
        $pushExample = function($tipo,$id) use (&$omittedExamples) {
            if (!isset($omittedExamples[$tipo])) $omittedExamples[$tipo] = [];
            if (count($omittedExamples[$tipo]) < 10) $omittedExamples[$tipo][] = $id;
        };

        // 3 Pre-check padres del curso_vigente: curso y cohorte
        $cursoFk = (int)$cursoV['id_curso_fk'];
        $cohorteFk = (int)$cursoV['id_cohorte_fk'];

        $stmtCurso = $pdo->prepare("SELECT estado_curso FROM curso WHERE id_curso = :id");
        $stmtCurso->execute([':id'=>$cursoFk]);
        $cursoRow = $stmtCurso->fetch(PDO::FETCH_ASSOC);
        $cursoActivo = $cursoRow && (int)$cursoRow['estado_curso'] === 1;

        $cohorteActivo = true;
		if ($cohorteFk !== null && $cohorteFk !== 0) {

            $stmtCoh = $pdo->prepare("SELECT archivar_cohorte FROM cohorte WHERE id_cohorte = :id");
            $stmtCoh->execute([':id'=>$cohorteFk]);
            $cohRow = $stmtCoh->fetch(PDO::FETCH_ASSOC);
            $cohorteActivo = $cohRow && (int)$cohRow['archivar_cohorte'] === 1;
            
            
            if (! $cohorteActivo) {
                $countsOmitted['curso_vigente'] = 1;
                $pushExample('curso_vigente', $id);
                $pdo->rollBack();
                echo json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Restauración parcial",
                    "Texto" => "No se pudo restaurar el curso vigente porque su cohorte padre está inactiva.",
                    "restored" => $countsRestored,
                    "omitted" => $countsOmitted,
                    "examples" => $omittedExamples,
                    "Tipo" => "warning"
                ]);
                return;
            }

        }
        // Si el curso o cohorte están inactivos, el curso_vigente se omite totalmente
        if (! $cursoActivo) {
            $countsOmitted['curso_vigente'] = 1;
            $pushExample('curso_vigente', $id);
            $pdo->rollBack();
            echo json_encode([
                "Alerta" => "simple",
                "Titulo" => "Restauración parcial",
                "Texto" => "No se pudo restaurar el curso vigente porque su curso padre está inactivo.",
                "restored" => $countsRestored,
                "omitted" => $countsOmitted,
                "examples" => $omittedExamples,
                "Tipo" => "warning"
            ]);
            return;
        }


        // 4 Restaurar curso_vigente
        $ps_up_cv = $pdo->prepare("UPDATE curso_vigente SET estado_curso_vigente = 1 WHERE id_curso_vigente = :idCV");
        $ps_up_cv->execute([':idCV'=>$id]);
        $countsRestored['curso_vigente'] = 1;

        // 5 Obtener secciones y decidir can_restore por profesor activo
        $ps_get_secciones = $pdo->prepare("SELECT id_seccion, ci_profesor FROM seccion WHERE id_curso_vigente_fk = :idCV");
        $ps_get_secciones->execute([':idCV'=>$id]);

        $ps_up_seccion = $pdo->prepare("UPDATE seccion SET archivar_seccion = 1 WHERE id_seccion = :idSec");
        $ps_get_evals = $pdo->prepare("SELECT id_evaluacion FROM evaluacion WHERE id_seccion = :idSec");
        $ps_up_eval = $pdo->prepare("UPDATE evaluacion SET archivar_evaluacion = 1 WHERE id_evaluacion = :idEval");
        $ps_up_puntaje = $pdo->prepare("UPDATE puntaje_estudiante SET archivar_puntaje = 1 WHERE id_evaluacion = :idEval");
        $ps_up_insc_append = $pdo->prepare("
            UPDATE inscripcion
            SET observaciones = CONCAT(IFNULL(observaciones, ''), ' | Periodo de curso reactivado')
            WHERE id_seccion_fk = :idSec AND IFNULL(observaciones, '') NOT LIKE '%Periodo de curso reactivado%'
        ");
        // Prepare stmt para verificar profesor
        $ps_prof = $pdo->prepare("SELECT estado_profesor FROM profesor WHERE id_profesor = :idProf");

        while ($sec = $ps_get_secciones->fetch(PDO::FETCH_ASSOC)) {
            $idSec = (int)$sec['id_seccion'];
            $idProf = (int)$sec['ci_profesor'];

            // Verificar profesor activo
            $ps_prof->execute([':idProf'=>$idProf]);
            $profRow = $ps_prof->fetch(PDO::FETCH_ASSOC);
            $profActivo = $profRow && (int)$profRow['estado_profesor'] === 1;

            if (! $profActivo) {
                // Omitir sección completa y no contar sus evaluaciones/puntajes
                $countsOmitted['secciones'] += 1;
                $pushExample('secciones', $idSec);
                continue;
            }

            // Restaurar sección
            $ps_up_seccion->execute([':idSec'=>$idSec]);
            $countsRestored['secciones'] += 1;

            // Evaluaciones de la sección
            $ps_get_evals->execute([':idSec'=>$idSec]);
            $skipSectionEvals = false; // no aplica aquí porque ya validamos profesor

            while ($eval = $ps_get_evals->fetch(PDO::FETCH_ASSOC)) {
                $idEval = (int)$eval['id_evaluacion'];

                // Restaurar evaluación
                $ps_up_eval->execute([':idEval'=>$idEval]);
                $countsRestored['evaluaciones'] += 1;

                // Puntajes: solo restaurar si su inscripción asociada está activa
                // Asumimos que puntaje_estudiante tiene id_inscripcion_fk; si no, ajustar
                $ps_get_insc = $pdo->prepare("SELECT id_inscripcion, archivada FROM inscripcion WHERE id_inscripcion = (SELECT id_inscripcion FROM puntaje_estudiante WHERE id_evaluacion = :idEval LIMIT 1)");
                $ps_get_insc->execute([':idEval'=>$idEval]);
                $inscRow = $ps_get_insc->fetch(PDO::FETCH_ASSOC);

                if (!$inscRow) {
                    // No hay inscripcion asociada encontrada para puntaje, considerar omitir puntajes encontrados
                    // Omite restaurar puntajes asociados a esta evaluación
                    // Para evitar múltiples queries, asumimos que si no hay inscripciones no hay puntajes a restaurar
                    continue;
                }

                $inscId = (int)$inscRow['id_inscripcion'];
                $inscActiva = (int)$inscRow['archivada'] === 1; // ajustar semántica si usas otro flag

                if (! $inscActiva) {
                    // Omitir puntajes de esta evaluación
                    $countsOmitted['puntajes'] += 1;
                    $pushExample('puntajes', $idEval);
                    continue;
                }

                // Restaurar puntajes para esta evaluación
                $ps_up_puntaje->execute([':idEval'=>$idEval]);
                // Sumar afectadas: podrías usar rowCount() para contarlas realmente
                $countsRestored['puntajes'] += $ps_up_puntaje->rowCount() ?: 1;
            } // end evaluaciones

            // Limpiar observaciones en inscripciones de la sección
            //$ps_up_insc_clean->execute([':idSec'=>$idSec]);
            $ps_up_insc_append->execute([':idSec' => $idSec]);
            
           // require_once "evaluacionControlador.php";
           // $evalController = new evaluacionControlador();
           // $evalController->actualizar_nota_final_seccion($idSec);
            
        } // end secciones


        $pdo->commit();

        // Construir mensaje y devolver conteos
        //$texto = "Restauración completada. Se restauró el curso vigente y los elementos con padres activos.";
        //$tipo = "success";
      //  if (array_sum($countsOmitted) > 0) {
    //        $texto = "Restauración parcial. Algunos elementos fueron omitidos por dependencias inactivas.";
  //          $tipo = "warning";
//        }

$totalOmitidos = array_sum($countsOmitted);
if ($totalOmitidos > 0) {
    $texto = "Curso vigente restaurado, pero se omitieron: " .
        $countsOmitted['secciones'] . " sección(es), " .
        $countsOmitted['evaluaciones'] . " evaluación(es), " .
        $countsOmitted['puntajes'] . " puntaje(s).";
    $tipo = "warning";
} else {
    $texto = "Curso vigente restaurado exitosamente. Todos los registros relacionados fueron activados.";
    $tipo = "success";
}

        echo json_encode([
            "Alerta"=>"recargar",
            "Titulo"=>"Restauración finalizada",
            "Texto"=>$texto,
            "Tipo"=>$tipo,
            "restored"=>$countsRestored,
            "omitted"=>$countsOmitted,
            "examples"=>$omittedExamples
        ]);
        return;

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["Alerta"=>"simple","Titulo"=>"Error","Texto"=>"No se pudo completar: ".$e->getMessage(),"Tipo"=>"error"]);
    }
}











/*
public function paginador_curso_vigente_archivado_controlador($pagina, $registros, $url) {
    $pagina = mainModel::limpiar_cadena($pagina);
    $registros = mainModel::limpiar_cadena($registros);
    $url = mainModel::limpiar_cadena($url);
    $url = SERVERURL . "index.php?views=" . $url . "&page=";

    $filtro_tipo = isset($_GET['filtro_tipo']) ? mainModel::limpiar_cadena($_GET['filtro_tipo']) : "";
    $filtro_anio = isset($_GET['filtro_anio']) ? mainModel::limpiar_cadena($_GET['filtro_anio']) : "";
    $filtro_curso = isset($_GET['filtro_curso']) ? mainModel::limpiar_cadena($_GET['filtro_curso']) : "";
    $filtro_id_cv = isset($_GET['filtro_id_cv']) ? mainModel::limpiar_cadena($_GET['filtro_id_cv']) : "";
    $orden = isset($_GET['orden']) ? mainModel::limpiar_cadena($_GET['orden']) : "default";

    $pagina = ($pagina > 0) ? (int)$pagina : 1;
    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

    $conexion = mainModel::conectar();

    // WHERE dinámico
    //$where = "c.archivar_cohorte = 0";
    $where = "1=1";
    //WHERE $where

    if ($filtro_tipo != "") {
        $where .= " AND cu.tipo_curso = '$filtro_tipo'";
    }

    if ($filtro_id_cv != "") {
    $where .= " AND cv.id_curso_vigente = '$filtro_id_cv'";
    }

    if ($filtro_anio != "") {
        $where .= " AND c.anio_cohorte = '$filtro_anio'";
    }

    if ($filtro_curso != "") {
    $where .= " AND cu.id_curso = '$filtro_curso'";
    }

    $order_sql = "ORDER BY c.anio_cohorte DESC, c.numero_cohorte DESC";

$consulta = "
    SELECT SQL_CALC_FOUND_ROWS  
        cv.id_curso_vigente, cu.nombre_curso, cu.tipo_curso, 
        c.numero_cohorte, c.anio_cohorte,
        cv.id_curso_fk, 
        cv.id_cohorte_fk,
        cv.oferta
    FROM curso_vigente cv
    INNER JOIN curso cu ON cv.id_curso_fk = cu.id_curso
    INNER JOIN cohorte c ON cv.id_cohorte_fk = c.id_cohorte
    WHERE $where AND cv.estado_curso_vigente=0
    $order_sql
    LIMIT $inicio, $registros
";




    if ($orden == "nombre") {
        $order_sql = "ORDER BY cu.nombre_curso ASC";
    } elseif ($orden == "estudiantes") {
        $order_sql = ""; // lo manejamos manualmente después
    }
    $datos = $conexion->query($consulta)->fetchAll();
    $total = (int)$conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>Id</th>
                    <th>Curso</th>
                    <th>Tipo</th>
                    <th>Cohorte</th>
                    <th>Cohorte y curso archivado</th>
                    <th>Secciones</th>
                    <th>Oferta</th>
                    <th>Evaluaciones</th>
                    <th>Estudiantes</th>
                    <th>Restaurar</th>
                </tr>
            </thead>
            <tbody>';




    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;

        foreach ($datos as $row) {
            $idCV = $row['id_curso_vigente'];

        $idCurso = $row['id_curso_fk'];
        $idCohorte = $row['id_cohorte_fk'];

        // Verificar estado del curso
        $estadoCurso = $conexion->query("SELECT estado_curso FROM curso WHERE id_curso = '$idCurso'")->fetchColumn();

        // Verificar estado del cohorte
        $archivadoCohorte = $conexion->query("SELECT archivar_cohorte FROM cohorte WHERE id_cohorte = '$idCohorte'")->fetchColumn();

        if ($estadoCurso == 0 && $archivadoCohorte == 0) {
            $estadoArchivo = "sí";
        } elseif ($estadoCurso == 0) {
            $estadoArchivo = "curso archivado";
        } elseif ($archivadoCohorte == 0) {
            $estadoArchivo = "cohorte archivado";
        } else {
            $estadoArchivo = "no";
        }


            // Secciones
            $secciones = $conexion->query("SELECT id_seccion FROM seccion WHERE id_curso_vigente_fk = '$idCV'")->fetchAll();
            $conteoSecciones = count($secciones);

            // Evaluaciones
            $conteoEvaluaciones = 0;
            foreach ($secciones as $sec) {
                $idSec = $sec['id_seccion'];
                $conteoEvaluaciones += (int)$conexion->query("SELECT COUNT(*) FROM evaluacion WHERE id_seccion = '$idSec'")->fetchColumn();
            }

            // Estudiantes
            $conteoEstudiantes = (int)$conexion->query("SELECT COUNT(*) FROM inscripcion WHERE id_curso_vigente_fk = '$idCV'")->fetchColumn();

            if ($orden == "estudiantes") {
                foreach ($datos as &$row) {
                    $idCV = $row['id_curso_vigente'];
                    $row['total_estudiantes'] = (int)$conexion->query("SELECT COUNT(*) FROM inscripcion WHERE id_curso_vigente_fk = '$idCV'")->fetchColumn();
                }

                usort($datos, function($a, $b) {
                    return $b['total_estudiantes'] <=> $a['total_estudiantes'];
                });
            }

            $tabla .= '
                <tr class="text-center">
                    <td>' . $idCV . '</td>
                    <td>' . $row['nombre_curso'] . '</td>
                    <td>' . $row['tipo_curso'] . '</td>
                    <td>' . $row['numero_cohorte'] . '.' . $row['anio_cohorte'] . '</td>
                    <td>' . $estadoArchivo . '</td>
                    <td>' . $conteoSecciones . '</td>
                    <td>' . $row['oferta'] . '</td>
                    <td>' . $conteoEvaluaciones . '</td>
                    <td>' . $conteoEstudiantes . '</td>
                    <td>
                        <form class="FormularioAjax" action="' . SERVERURL . 'ajax/cohorteAjax.php" method="POST" data-form="restore" autocomplete="off">
                            <input type="hidden" name="curso_vigente_id_restore" value="' . mainModel::encryption($idCV) . '">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-undo"></i> Restaurar
                            </button>
                        </form>
                    </td>
                </tr>';
        }

        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="7">';
        if ($total >= 1) {
            $tabla .= '<a href="' . $url . '" class="btn btn-raised btn-primary btn-sm">Haga clic aquí para recargar el listado</a>';
        } else {
            $tabla .= 'No hay cursos vigentes archivados que coincidan con los filtros';
        }
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando curso vigente ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $url, 7);
    }

    return $tabla;
}
*/



//datos pa curso vigente update
public function datos_curso_vigente_controlador($tipo, $id) {
    $tipo = mainModel::limpiar_cadena($tipo);
    $id = mainModel::decryption($id);
    $id = mainModel::limpiar_cadena($id);

    return cohorteModelo::datos_curso_vigente_modelo($tipo, $id);
}

//pa curso vigente new
/*
public function listar_cohortes_por_tipo_controlador($tipoCurso) {
    $tipoCurso = mainModel::limpiar_cadena($tipoCurso);
    return cohorteModelo::listar_cohortes_por_tipo_modelo($tipoCurso);
}
*/
public function listar_cohortes_por_tipo_controlador($tipoCurso, $idCurso) {
    $tipoCurso = mainModel::limpiar_cadena($tipoCurso);
    $idCurso = mainModel::limpiar_cadena($idCurso);
    return cohorteModelo::listar_cohortes_por_tipo_modelo($tipoCurso, $idCurso);
}

    
    
    
    
    
    
    
    
    
    
    
    
    
    

public function actualizar_cohorte_controlador_2025() {
    $idEnc = mainModel::limpiar_cadena($_POST['cohorte_id_up']);
    $id = mainModel::decryption($idEnc);

    $numero = mainModel::limpiar_cadena($_POST['cohorte_numero_up']);
    $anio = mainModel::limpiar_cadena($_POST['cohorte_anio_up']);
    $tipo = mainModel::limpiar_cadena($_POST['cohorte_tipo_up']);
   // $estado = mainModel::limpiar_cadena($_POST['cohorte_estado_up']);

    $f_inicio = $_POST['cohorte_fecha_inicio_up'];
    $f_fin = $_POST['cohorte_fecha_fin_up'];

    $p_art13 = mainModel::limpiar_cadena($_POST['cohorte_pago_art13_up']);
    $f_art13 = $_POST['cohorte_fecha_pago_art13_up'];

    $p_arancel = mainModel::limpiar_cadena($_POST['cohorte_pago_aranceles_up']);
    $f_arancel = $_POST['cohorte_fecha_pago_aranceles_up'];

    $f_mod1 = $_POST['cohorte_fecha_pago_modulo1_up'] ?? null;
    $f_mod2 = $_POST['cohorte_fecha_pago_modulo2_up'] ?? null;
    $f_mod3 = $_POST['cohorte_fecha_pago_modulo3_up'] ?? null;

    // Validaciones básicas
    /*
    if($numero=="" || $anio=="" || $tipo=="" || $f_inicio=="" || $f_fin==""){
        return $this->respuesta_error("Campos incompletos", "Debes completar todos los campos obligatorios.");
    }*/

    if($numero=="" || $anio=="" || $tipo=="" || $f_inicio=="" || $f_fin=="" || $pago_inscripcion=="" || $pago_modulo==""
       || $p_art13=="" || $f_art13=="" || $p_arancel=="" || $f_arancel=="" || $f_mod1=="" || $f_mod2=="" || $f_mod3==""){
        mainModel::generarAlertaControlador("No has llenado todos los campos obligatorios", "error_simple");
    }
    
    $diff_dias = (strtotime($f_fin) - strtotime($f_inicio)) / 86400;
    if($diff_dias > 365){
       mainModel::generarAlertaControlador("La cohorte no puede durar más de 365 días. Actualmente tiene una duración de ".round($diff_dias)." días.", "error_simple");
       // return $this->respuesta_error("Duración inválida", "La cohorte no puede durar más de 365 días. Actualmente tiene una duración de ".round($diff_dias)." días.");
    }

        if($this->validar_cohorte_no_duplicado($numero, $anio, $tipo, $id=null)){
        	mainModel::generarAlertaControlador("Ya existe una cohorte registrada con el mismo trimestre, año y tipo.", "error_simple");
        }

        $fechas_criticas = [
            "Inicio del trimestre" => $f_inicio,
            "Fin del trimestre" => $f_fin,
            "Pago Artículo 13" => $f_art13,
            "Pago Aranceles" => $f_arancel,
            "Pago Módulo 1" => $f_mod1,
            "Pago Módulo 2" => $f_mod2,
            "Pago Módulo 3" => $f_mod3
        ];
        
        $fechas_pagos = [
            "Pago Artículo 13" => $f_art13,
            "Pago Aranceles" => $f_arancel,
            "Pago Módulo 1" => $f_mod1,
            "Pago Módulo 2" => $f_mod2,
            "Pago Módulo 3" => $f_mod3
        ];
        
        if($this->validar_fechas_duplicadas($fechas_criticas)){
        	mainModel::generarAlertaControlador("Las fechas no pueden repetirse", "error_simple");
        }        
        
        if($this->validar_fechas_en_rango($f_inicio,$f_fin, $fechas_pagos)){
        	mainModel::generarAlertaControlador("Las fechas de pago deben estar dentro del rango de $f_inicio y $f_fin ", "error_simple");
        }
        
        $validacionSolape = $this->validar_fecha_no_solapada($tipo, $f_inicio, $f_fin);
        if ($validacionSolape["solapado"]) {
            mainModel::generarAlertaControlador($validacionSolape["mensaje"],"error_simple");
        }
    
        if(strtotime($f_inicio) > strtotime($f_fin)){
            mainModel::generarAlertaControlador("La fecha de inicio no puede ser posterior a la fecha de fin.","error_simple");
        }
    
    
    
    
    
    
    /*
    
    $datos_originales = mainModel::ejecutar_consulta_simple("
        SELECT fecha_inicio, fecha_fin FROM cohorte WHERE id_cohorte = '$id'
    ")->fetch();

    
/*    
    if(!$this->fecha_dentro_del_margen($f_inicio, $datos_originales['fecha_inicio'])){
        return $this->respuesta_error("Fecha fuera de margen", "La nueva fecha de inicio debe estar dentro de 4 meses respecto a su valor original.");
    }

    if(!$this->fecha_dentro_del_margen($f_fin, $datos_originales['fecha_fin'])){
        return $this->respuesta_error("Fecha fuera de margen", "La nueva fecha de fin debe estar dentro de 4 meses respecto a su valor original.");
    }
*
    
    
            //validar que las fechas  no sean iguales
        $validacionFechas = $this->fechas_no_repetidas([
            "Inicio del trimestre" => $f_inicio,
            "Fin del trimestre" => $f_fin,
            "Pago inscripción" => $f_inscripcion ?? null,
            "Pago Artículo 13" => $f_art13,
            "Pago Aranceles" => $f_arancel,
            "Pago Módulo 1" => $f_mod1,
            "Pago Módulo 2" => $f_mod2,
            "Pago Módulo 3" => $f_mod3
        ]);

        if ($validacionFechas !== true) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Fechas duplicadas",
                "Texto" => $validacionFechas,
                "Tipo" => "error"
            ]);
        }
    
    
    $validacionSolape = $this->fechas_no_solapadas($tipo, $f_inicio, $f_fin, $id);
if ($validacionSolape !== true) {
    return json_encode([
        "Alerta" => "simple",
        "Titulo" => "Conflicto de fechas",
        "Texto" => $validacionSolape,
        "Tipo" => "error"
    ]);
}
    
    
    if(strtotime($f_inicio) > strtotime($f_fin)){
        return $this->respuesta_error("Fechas inválidas", "La fecha de inicio no puede ser posterior a la de fin.");
    }

    foreach ([
        ["label" => "Artículo 13", "fecha" => $f_art13],
        ["label" => "Aranceles", "fecha" => $f_arancel],
        ["label" => "Módulo 1", "fecha" => $f_mod1],
        ["label" => "Módulo 2", "fecha" => $f_mod2],
        ["label" => "Módulo 3", "fecha" => $f_mod3]
    ] as $item) {
        if ($item['fecha'] && !$this->fecha_dentro_del_rango($item['fecha'], $f_inicio, $f_fin)) {
            return $this->respuesta_error("Fecha fuera de rango", "La fecha de pago de {$item['label']} debe estar dentro del trimestre.");
        }
    }
    */

    // Actualizar cohorte
    $conexion = mainModel::conectar();
    $stmt = $conexion->prepare("
        UPDATE cohorte SET
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
            fecha_pago_modulo3 = :f_mod3
        WHERE id_cohorte = :ID
    ");

    $stmt->bindParam(":numero", $numero);
    $stmt->bindParam(":anio", $anio);
    $stmt->bindParam(":tipo", $tipo);
    $stmt->bindParam(":inicio", $f_inicio);
    $stmt->bindParam(":fin", $f_fin);
    $stmt->bindParam(":p_art13", $p_art13);
    $stmt->bindParam(":f_art13", $f_art13);
    $stmt->bindParam(":p_arancel", $p_arancel);
    $stmt->bindParam(":f_arancel", $f_arancel);
    $stmt->bindParam(":f_mod1", $f_mod1);
    $stmt->bindParam(":f_mod2", $f_mod2);
    $stmt->bindParam(":f_mod3", $f_mod3);
    $stmt->bindParam(":ID", $id);

    if(!$stmt->execute()){
        return $this->respuesta_error("Error inesperado", "No se pudo actualizar la cohorte.");
    }

    // Procesar cursos nuevos
    $totalNuevos = $_POST['total_cursos_nuevos'] ?? 0;



$cursosNuevos = $_POST['cursos_vigentes'] ?? [];
cohorteModelo::agregar_cursos_vigentes_modelo($id, $cursosNuevos);


/*
    for ($i = 0; $i < $totalNuevos; $i++) {
        $curso = $_POST['cursos_vigentes'][$i];
        $idCurso = mainModel::limpiar_cadena($curso['id_curso_fk']);
        $oferta = mainModel::limpiar_cadena($curso['oferta']);
        $idProfesor = mainModel::limpiar_cadena($curso['id_profesor_fk']);

        cohorteModelo::agregar_cursos_vigentes_modelo([
            ':id_curso_fk' => $idCurso,
            ':oferta' => $oferta,
            ':id_profesor_fk' => $idProfesor,
            ':id_cohorte_fk' => $id
        ]);
    }
*/



    // Procesar cursos asignados
    $totalAsignados = $_POST['total_cursos_asignados'] ?? 0;
    
    
  /*  
    for ($i = 0; $i < $totalAsignados; $i++) {
        $curso = $_POST['cursos_asignados'][$i];
        $idCursoVigente = mainModel::limpiar_cadena($curso['id_curso_vigente']);
        $estado = $curso['estado_curso_vigente'] ?? 1;

        if ($estado == 0) {
            // Archivar
            cohorteModelo::archivar_curso_vigente_modelo($idCursoVigente);
        } else {
            // Actualizar
            $idCurso = mainModel::limpiar_cadena($curso['id_curso_fk']);
            $oferta = mainModel::limpiar_cadena($curso['oferta']);

            cohorteModelo::actualizar_curso_vigente_modelo([
                ':id' => $idCursoVigente,
                ':id_curso_fk' => $idCurso,
                ':oferta' => $oferta
            ]);
        }
    }
*/
for ($i = 0; $i < $totalAsignados; $i++) {
    $curso = $_POST['cursos_asignados'][$i] ?? [];

    $idCursoVigente = mainModel::limpiar_cadena($curso['id_curso_vigente'] ?? '');
    $estado = $curso['estado_curso_vigente'] ?? 1;

  /*  //if ($estado == 0) {
      //  cohorteModelo::archivar_curso_vigente_modelo($idCursoVigente);
    //} else {
      //  if (!isset($curso['id_curso_fk']) || !isset($curso['oferta'])) {
        //    echo "<script>console.error('Curso asignado incompleto en índice {$i}. Total asignados: {$totalAsignados}');</script>";
          //  continue;
       // }
*/

    if ($estado == 0) {
        if ($idCursoVigente !== '') {
            $resultado = cohorteModelo::archivar_curso_vigente_modelo($idCursoVigente);
           // echo "<script>console.log('Resultado archivado curso ID {$idCursoVigente}: " . ($resultado ? 'true' : 'false') . "');</script>";
            if (!$resultado) {
                error_log("Falló el archivado del curso vigente ID: $idCursoVigente");
            }
        } else {
            error_log("Curso sin ID válido para archivar en índice $i");
        }
    }


else {
        if (!isset($curso['id_curso_fk'])) {
          echo "<script>console.error('Curso asignado incompleto en índice {$i}. Total asignados: {$totalAsignados}');</script>";
        continue;
       }



        $idCurso = mainModel::limpiar_cadena($curso['id_curso_fk']);
        //$oferta = mainModel::limpiar_cadena($curso['oferta']);
		//            'oferta' => $oferta

        cohorteModelo::actualizar_curso_vigente_modelo([
            'id' => $idCursoVigente,
            'id_curso_fk' => $idCurso,
        ]);


    }
}

    return json_encode([
        "Alerta" => "recargar",
        "Titulo" => "Cohorte actualizada",
        "Texto" => "La cohorte y sus cursos han sido actualizados correctamente.",
        "Tipo" => "success"
    ]);
}




/*
    public function agregar_cohortesito_controlador(){
        // 1. Recoger y limpiar datos
        $numero = mainModel::limpiar_cadena($_POST['cohorte_numero_reg']);
        $anio = mainModel::limpiar_cadena($_POST['cohorte_anio_reg']);
        $tipo = mainModel::limpiar_cadena($_POST['cohorte_tipo_reg']);
        //$estado = mainModel::limpiar_cadena($_POST['cohorte_estado_reg']);
        $pago_inscripcion = mainModel::limpiar_cadena($_POST['cohorte_pago_inscripcion_reg']);
        $pago_modulo = mainModel::limpiar_cadena($_POST['cohorte_pago_modulo_reg']);
        $f_inicio = $_POST['cohorte_fecha_inicio_reg'];
        $f_fin = $_POST['cohorte_fecha_fin_reg'];
        
        $alterno = $_POST['cohorte_alterno'];

        $hoy = date("Y-m-d");

		$estado = ($hoy >= $f_inicio && $hoy <= $f_fin) ? "Vigente" : "Próximo"; 

        $p_art13 = mainModel::limpiar_cadena($_POST['cohorte_pago_art13_reg']);
        $f_art13 = $_POST['cohorte_fecha_pago_art13_reg'];
        $p_arancel = mainModel::limpiar_cadena($_POST['cohorte_pago_aranceles_reg']);
        $f_arancel = $_POST['cohorte_fecha_pago_aranceles_reg'];
        $f_mod1 = $_POST['cohorte_fecha_pago_modulo1_reg'];
        $f_mod2 = $_POST['cohorte_fecha_pago_modulo2_reg'];
        $f_mod3 = $_POST['cohorte_fecha_pago_modulo3_reg'];
        
        
        if($numero=="" || $anio=="" || $tipo=="" || $f_inicio=="" || $f_fin=="" || $pago_inscripcion=="" || $pago_modulo==""
           || $p_art13=="" || $f_art13=="" || $p_arancel=="" || $f_arancel=="" || $f_mod1=="" || $f_mod2=="" || $f_mod3==""){
        	mainModel::generarAlertaControlador("No has llenado todos los campos obligatorios", "error_simple");
        }


        if($this->validar_cohorte_no_duplicado($numero, $anio, $tipo, $id=null)){
        	mainModel::generarAlertaControlador("Ya existe una cohorte registrada con el mismo trimestre, año y tipo.", "error_simple");
        }

        $fechas_criticas = [
            "Inicio del trimestre" => $f_inicio,
            "Fin del trimestre" => $f_fin,
            "Pago Artículo 13" => $f_art13,
            "Pago Aranceles" => $f_arancel,
            "Pago Módulo 1" => $f_mod1,
            "Pago Módulo 2" => $f_mod2,
            "Pago Módulo 3" => $f_mod3
        ];
        
        $fechas_pagos = [
            "Pago Artículo 13" => $f_art13,
            "Pago Aranceles" => $f_arancel,
            "Pago Módulo 1" => $f_mod1,
            "Pago Módulo 2" => $f_mod2,
            "Pago Módulo 3" => $f_mod3
        ];
        
        if($this->validar_fechas_duplicadas($fechas_criticas)){
        	mainModel::generarAlertaControlador("Las fechas no pueden repetirse", "error_simple");
        }        
        
        if($this->validar_fechas_en_rango($f_inicio,$f_fin, $fechas_pagos)){
        	mainModel::generarAlertaControlador("Las fechas de pago deben estar dentro del rango de $f_inicio y $f_fin ", "error_simple");
        }
        
        $validacionSolape = $this->validar_fecha_no_solapada($tipo, $f_inicio, $f_fin);
        if ($validacionSolape["solapado"]) {
            mainModel::generarAlertaControlador($validacionSolape["mensaje"],"error_simple");

        }
        
        
        if(strtotime($f_inicio) > strtotime($f_fin)){
            mainModel::generarAlertaControlador("La fecha de inicio no puede ser posterior a la fecha de fin.","error_simple");
        }


        
        /*------------------------------------------------------*
                // 3. Insertar duracion usando modelo
        $id_duracion = cohorteModelo::agregar_duracion_modelo([
            ':estado' => $estado,
            ':inicio' => $f_inicio,
            ':fin' => $f_fin,
        ]);
        
        if($id_duracion > 0){
            //                        ':estado' => $estado,
            //            ':inicio' => $f_inicio,
             //           ':fin' => $f_fin,

                    // 3. Insertar cohorte usando modelo
                    $id_cohorte = cohorteModelo::agregar_cohorte_modelo([
                        ':id_duracion' => $id_duracion,
                        ':numero' => $numero,
                        ':anio' => $anio,
                        ':tipo' => $tipo,
                        ':p_art13' => $p_art13,
                        ':f_art13' => $f_art13,
                        ':p_arancel' => $p_arancel,
                        ':f_arancel' => $f_arancel,
                        ':f_mod1' => $f_mod1,
                        ':f_mod2' => $f_mod2,
                        ':f_mod3' => $f_mod3,
                        ':pago_inscripcion' => $pago_inscripcion,
                        ':pago_modulo' => $pago_modulo
                    ]);

                    if($id_cohorte > 0){
                        // 4. Insertar cursos vigentes si existen
                        $cursos = $_POST['cursos_vigentes'] ?? [];
                        $cursosRegistrados = cohorteModelo::agregar_cursos_vigentes_modelo($id_duracion, $cursos);

                        if ($cursosRegistrados === 0) {
                            return json_encode([
                                "Alerta" => "recargar",
                                "Titulo" => "Cohorte parcial",
                                "Texto" => "La cohorte se registró correctamente .",
                                "Tipo" => "warning"
                            ]);
                        }

                        return json_encode([
                            "Alerta" => "recargar",
                            "Titulo" => "Cohorte registrada",
                            "Texto" => "La cohorte y sus cursos vigentes se han creado correctamente.",
                            "Tipo" => "success"
                        ]);
                    } else {
                        return json_encode([
                            "Alerta" => "simple",
                            "Titulo" => "Error inesperado",
                            "Texto" => "No se pudo registrar la cohorte.",
                            "Tipo" => "error"
                        ]);
                    }
                
        }
        
        else {
                        return json_encode([
                            "Alerta" => "simple",
                            "Titulo" => "Error inesperado",
                            "Texto" => "Fallo al registrar la cohorte.",
                            "Tipo" => "error"
                        ]);
        }

    }*/


    
    
    
    
//listar_cohortes_por_tipo_controlador
//listar_profesores_controlador
//archivar_curso_vigente_controlador
//datos_curso_vigente_controlador
//archivar
//cursos_vigentes_por_cohorte_controlador
//listar_cursos_controlador_para_cohorte
    
    
    
    
    
    
    
}

