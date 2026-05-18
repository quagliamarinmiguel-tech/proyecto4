<?php
if ($peticionAjax) {
    require_once "../modelos/cvintModelo.php";
} else {
    require_once "./modelos/cvintModelo.php";
}

class cvintControlador extends cvintModelo {

    public function agregar_cv_int_controlador() {
        $cursoId = $_POST['curso_id_fk'] ?? "";
        $profesorId = $_POST['profesor_id_fk'] ?? "";
        $fechaInicio = $_POST['fecha_inicio_int'] ?? "";
        $fechaFin = $_POST['fecha_fin_int'] ?? "";
        $pagoInscripcion = $_POST['pago_inscripcion_int'] ?? 0;
        $pagoEncuentro = $_POST['pago_encuentro_int'] ?? 0;
        $pagoTitulo = $_POST['pago_titulo_int'] ?? 0;
        $nombreSeccion = $_POST['nombre_seccion'] ?? "A";
        $dia = $_POST['seccion_dia_reg'] ?? "";
        

           //     echo "<pre>";
//var_dump($nombreSeccion);
//var_dump($profesorId);
//var_dump($idSeccion);
//var_dump($profesorId);
//var_dump($fechaInicio);
//var_dump($fechaFin);
//echo "</pre>";
 //       exit();
        // Validación básica
        if ($cursoId == "" || $profesorId == "" || $fechaInicio == "" || $fechaFin == "" || $dia == "") {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Datos incompletos",
                "Texto" => "Todos los campos son obligatorios.",
                "Tipo" => "error"
            ]);
        }

        // 1. Crear curso vigente
        $cursoVigenteId = cvintModelo::crear_curso_vigente_modelo($cursoId);
        if (!$cursoVigenteId) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error al crear curso vigente",
                "Texto" => "No se pudo registrar el curso vigente.",
                "Tipo" => "error"
            ]);
        }

        // 2. Crear sección A
        $seccion = [
            "nombre_seccion" => $nombreSeccion,
            "id_curso_vigente_fk" => $cursoVigenteId,
            "id_profesor_fk" => $profesorId,
            "dia" => $dia
        ];
        //$seccionCreada = cvintModelo::crear_seccion_modelo($seccion);
		$seccionCreada = cvintModelo::crear_seccion_A_modelo($cursoVigenteId, $profesorId, $dia, $nombreSeccion);        
        // 3. Crear periodo intensivo
     
        $hoy = strtotime(date('Y-m-d'));
        $inicio = strtotime($fechaInicio);
        $fin = strtotime($fechaFin);

        if ($hoy < $inicio) {
            $estado = "Próximo";
        } elseif ($hoy >= $inicio && $hoy <= $fin) {
            $estado = "Vigente";
        } else {
            $estado = "Terminado";
        }
        
        $periodo = [
            "id_curso_vigente_fk" => $cursoVigenteId,
            "fecha_inicio_int" => $fechaInicio,
            "fecha_fin_int" => $fechaFin,
            "pago_inscripcion_int" => $pagoInscripcion,
            "pago_encuentro_int" => $pagoEncuentro,
            "pago_titulo_int" => $pagoTitulo,
            "estado_periodo" => $estado
        ];
        $periodoCreado = cvintModelo::crear_periodo_intensivo_modelo($periodo);

        if ($seccionCreada && $periodoCreado) {
            return json_encode([
                "Alerta" => "recargar",
                "Titulo" => "Curso intensivo registrado",
                "Texto" => "El curso y su sección fueron creados exitosamente.",
                "Tipo" => "success"
            ]);
        } else {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error parcial",
                "Texto" => "Se creó el curso pero falló la sección.",
                "Tipo" => "error"
            ]);
        }
    }

    
    //para cv int update
    public function obtener_datos_cv_intensivo_controlador($idCursoVigente) {
        $desencriptando = mainModel::decryption($idCursoVigente);
		$idCursoVigente = $desencriptando;       
        // $idCursoVigente = mainModel::decryption($idCursoVigenteEncriptado);
        if (!$idCursoVigente || !is_numeric($idCursoVigente)) {
            return null;
        }
      
//echo "<pre>";
//var_dump($idCursoVigente);
//echo "</pre>";
//        exit();
        return cvintModelo::obtener_datos_cv_intensivo_modelo($idCursoVigente);
    }
    
    
    public function actualizar_cv_int_controlador() {
    // 1. Capturar datos del formulario
    $idCursoVigente = $_POST['cv_int_id_up'] ?? "";
    $idPeriodo = $_POST['id_periodo_actual'] ?? "";
    $idSeccion = $_POST['id_seccion_a'] ?? "";
    $profesorId = $_POST['profesor_id_fk'] ?? "";
    $fechaInicio = $_POST['fecha_inicio_int'] ?? "";
    $fechaFin = $_POST['fecha_fin_int'] ?? "";
    $pagoInscripcion = $_POST['pago_inscripcion_int'] ?? 0;
    $pagoEncuentro = $_POST['pago_encuentro_int'] ?? 0;
    $pagoTitulo = $_POST['pago_titulo_int'] ?? 0;
    $dia = $_POST['seccion_dia_up'] ?? "";
     

   //     echo "<pre>";
//var_dump($idCursoVigente);
//var_dump($idPeriodo);
//var_dump($idSeccion);
//var_dump($profesorId);
//var_dump($fechaInicio);
//var_dump($fechaFin);
//echo "</pre>";
//        exit();
    // 2. Validación básica
    if (
        !$idCursoVigente || !$idPeriodo || !$idSeccion || !$profesorId ||
        !$fechaInicio || !$fechaFin || !$dia
    ) {
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Datos incompletos",
            "Texto" => "Todos los campos son obligatorios.",
            "Tipo" => "error"
        ]);
    }

    // 3. Calcular estado del periodo
    $hoy = strtotime(date('Y-m-d'));
    $inicio = strtotime($fechaInicio);
    $fin = strtotime($fechaFin);

    if ($hoy < $inicio) {
        $estado = "Próximo";
    } elseif ($hoy >= $inicio && $hoy <= $fin) {
        $estado = "Vigente";
    } else {
        $estado = "Terminado";
    }

    // 4. Preparar datos para actualizar periodo intensivo
    $periodo = [
        "id_periodo_intensivo" => $idPeriodo,
        "fecha_inicio_int" => $fechaInicio,
        "fecha_fin_int" => $fechaFin,
        "pago_inscripcion_int" => $pagoInscripcion,
        "pago_encuentro_int" => $pagoEncuentro,
        "pago_titulo_int" => $pagoTitulo,
        "estado_periodo" => $estado
    ];

    // 5. Ejecutar actualizaciones
    $periodoActualizado = cvintModelo::actualizar_periodo_intensivo_modelo($periodo);
    $seccionActualizada = cvintModelo::actualizar_profesor_seccion_modelo($idSeccion, $profesorId, $dia);

    // 6. Respuesta
    if ($periodoActualizado && $seccionActualizada) {
        return json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Curso intensivo actualizado",
            "Texto" => "Los datos fueron modificados correctamente.",
            "Tipo" => "success"
        ]);
    } else {
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error al actualizar",
            "Texto" => "No se pudo modificar el curso intensivo.",
            "Tipo" => "error"
        ]);
    }
}
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
?>