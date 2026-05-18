<?php
require_once "../modelos/evaluacionModelo.php";

class evaluacionControlador extends evaluacionModelo {





//validar que el porcentaje de la evaluacion no pase del 100%
private function validar_porcentaje_total($id_seccion, $porcentaje_nuevo, $id_evaluacion_excluir = null){
    $total_actual = evaluacionModelo::sumar_porcentajes_por_seccion_modelo($id_seccion, $id_evaluacion_excluir)->fetchColumn();
    $porcentaje_nuevo = (float) $porcentaje_nuevo;

    if(($total_actual + $porcentaje_nuevo) > 100){
        return [
            "valido" => false,
            "alerta" => mainModel::sweet_alert(
                "simple",
                "Porcentaje excedido",
                "El total acumulado de evaluaciones supera el 100%. Ajusta el porcentaje.",
                "warning"
            )
        ];
    }
    return ["valido" => true];
}


// En seccionAjax.php, agregar esta función
public function eliminar_evaluacion_controlador() {
    // Verificar permisos
    session_start(['name'=>'SPM']);
    if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 2) {
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Acceso Denegado",
            "Texto" => "No tienes permisos para realizar esta acción",
            "Tipo" => "error"
        ];
        return json_encode($alerta);
    }

    $pdo = mainModel::conectar();

    try {
        // Obtener ID de la evaluación
        $evaluacion_id = mainModel::decryption($_POST['evaluacion_id_delete']);
        $evaluacion_id = mainModel::limpiar_cadena($evaluacion_id);

        if (empty($evaluacion_id)) {
            throw new Exception("ID de evaluación inválido");
        }

        // Iniciar transacción
        $pdo->beginTransaction();

        // 1. Eliminar los puntajes de estudiantes asociados
        $deletePuntajes = $pdo->prepare("
            DELETE FROM puntaje_estudiante 
            WHERE id_evaluacion = :id_evaluacion
        ");
        $deletePuntajes->execute([':id_evaluacion' => $evaluacion_id]);

        // 2. Eliminar la evaluación
        $deleteEvaluacion = $pdo->prepare("
            DELETE FROM evaluacion 
            WHERE id_evaluacion = :id_evaluacion
        ");
        $deleteEvaluacion->execute([':id_evaluacion' => $evaluacion_id]);

        $pdo->commit();

        $alerta = [
            "Alerta" => "recargar",
            "Titulo" => "Evaluación Eliminada",
            "Texto" => "La evaluación y sus calificaciones han sido eliminadas exitosamente",
            "Tipo" => "success"
        ];

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Error",
            "Texto" => "Error al eliminar la evaluación: " . $e->getMessage(),
            "Tipo" => "error"
        ];
    }

    return json_encode($alerta);
}


 public function actualizar_nota_final_seccion($id_seccion) {
    $pdo = mainModel::conectar();

    // 1. Obtener evaluaciones activas de la sección
    $stmtEval = $pdo->prepare("
        SELECT id_evaluacion, porcentaje 
        FROM evaluacion 
        WHERE id_seccion = :seccion AND archivar_evaluacion = 1
    ");
    $stmtEval->execute([':seccion' => $id_seccion]);
    $evaluaciones = $stmtEval->fetchAll(PDO::FETCH_ASSOC);

    // 2. Calcular suma de porcentajes
    $sumaPorcentajes = 0;
    foreach ($evaluaciones as $eval) {
        $sumaPorcentajes += floatval($eval['porcentaje']);
    }

    // 3. Obtener inscripciones de la sección
    $stmtInscritos = $pdo->prepare("
        SELECT id_inscripcion 
        FROM inscripcion 
        WHERE id_seccion_fk = :seccion
    ");
    $stmtInscritos->execute([':seccion' => $id_seccion]);
    $inscripciones = $stmtInscritos->fetchAll(PDO::FETCH_ASSOC);

    // 4. Si suma es 100, calcular nota final
    if ($sumaPorcentajes == 100) {
        foreach ($inscripciones as $inscrito) {
            $id_inscripcion = $inscrito['id_inscripcion'];
            $notaFinal = 0;

            foreach ($evaluaciones as $eval) {
                $id_eval = $eval['id_evaluacion'];
                $porcentaje = floatval($eval['porcentaje']);

                $stmtPuntaje = $pdo->prepare("
                    SELECT puntaje 
                    FROM puntaje_estudiante 
                    WHERE id_evaluacion = :eval AND id_inscripcion = :insc AND archivar_puntaje = 1
                ");
                $stmtPuntaje->execute([
                    ':eval' => $id_eval,
                    ':insc' => $id_inscripcion
                ]);
                $puntaje = $stmtPuntaje->fetchColumn();

                if ($puntaje !== false) {
                    
                    $valor = ($puntaje === "NP") ? 0 : floatval($puntaje);
					$notaFinal += ($valor * floatval($porcentaje)) / 100;
                    
                  //  $notaFinal += ($puntaje * $porcentaje) / 100;
                }
            }

            // Redondear y actualizar nota final
            $stmtUpdate = $pdo->prepare("
                UPDATE inscripcion 
                SET nota_final = :nota 
                WHERE id_inscripcion = :id
            ");
            $stmtUpdate->execute([
                ':nota' => round($notaFinal, 2),
                ':id' => $id_inscripcion
            ]);
        }
    } else {
        // 5. Si no hay 100%, eliminar nota_final
        $stmtReset = $pdo->prepare("
            UPDATE inscripcion 
            SET nota_final = NULL 
            WHERE id_seccion_fk = :seccion
        ");
        $stmtReset->execute([':seccion' => $id_seccion]);
    }
}








public function agregar_evaluacion_controlador($datos){
    $id_seccion = mainModel::limpiar_cadena($datos['id_seccion']);
    $tipo = mainModel::limpiar_cadena($datos['tipo_evaluacion']);
    $objetivo = mainModel::limpiar_cadena($datos['obj']);
    $porcentaje = mainModel::limpiar_cadena($datos['porcentaje']);
    $fecha = mainModel::limpiar_cadena($datos['fecha_evaluacion']);

    // Validaciones básicas
    if($tipo == "" || $objetivo == "" || $porcentaje == "" || $fecha == ""){
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Campos incompletos",
            "Texto" => "Todos los campos son obligatorios",
            "Tipo" => "error"
        ];
        echo json_encode($alerta);
        exit();
    }

    if($porcentaje < 1 || $porcentaje > 100){
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Porcentaje inválido",
            "Texto" => "El porcentaje debe estar entre 1 y 100",
            "Tipo" => "warning"
        ];
        echo json_encode($alerta);
        exit();
    }

    $validacion = $this->validar_porcentaje_total($id_seccion, $porcentaje);
    if(!$validacion['valido']){
        echo json_encode($validacion['alerta']);
        exit();
    }

    // Registrar evaluación
    $datos_eval = [
        "Tipo" => $tipo,
        "Objetivo" => $objetivo,
        "Porcentaje" => $porcentaje,
        "Fecha" => $fecha,
        "Seccion" => $id_seccion
    ];

    $id_evaluacion = evaluacionModelo::agregar_evaluacion_modelo($datos_eval); 

    if($id_evaluacion > 0){
        // Registrar puntajes
        foreach($datos as $key => $valor){
            if(strpos($key, "puntaje_") === 0){
                $id_inscripcion = str_replace("puntaje_", "", $key);
                $puntaje = mainModel::limpiar_cadena($valor);

                $datos_puntaje = [
                    "Evaluacion" => $id_evaluacion,
                    "Inscripcion" => $id_inscripcion,
                    "Puntaje" => $puntaje
                ];
                evaluacionModelo::agregar_puntaje_modelo($datos_puntaje);
            }
        }

        $this->actualizar_nota_final_seccion($id_seccion);
        
        $alerta = [
            "Alerta" => "recargar",
            "Titulo" => "Evaluación registrada",
            "Texto" => "La evaluación y los puntajes fueron guardados correctamente",
            "Tipo" => "success"
        ];
    } else {
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Error",
            "Texto" => "No se pudo registrar la evaluación",
            "Tipo" => "error"
        ];
    }

    echo json_encode($alerta);
}





public function actualizar_evaluacion_controlador($datos){
    $id_evaluacion = mainModel::decryption($datos['evaluacion_id_up']);
    $id_evaluacion = mainModel::limpiar_cadena($id_evaluacion);

    $id_seccion = mainModel::limpiar_cadena($datos['id_seccion']);
    $tipo = mainModel::limpiar_cadena($datos['tipo_evaluacion']);
    $objetivo = mainModel::limpiar_cadena($datos['obj']);
    $porcentaje = mainModel::limpiar_cadena($datos['porcentaje']);
    $fecha = mainModel::limpiar_cadena($datos['fecha_evaluacion']);

    // Validaciones básicas
    if($tipo == "" || $objetivo == "" || $porcentaje == "" || $fecha == ""){
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Campos incompletos",
            "Texto" => "Todos los campos son obligatorios",
            "Tipo" => "error"
        ]);
    }

    if($porcentaje < 1 || $porcentaje > 100){
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Porcentaje inválido",
            "Texto" => "El porcentaje debe estar entre 1 y 100",
            "Tipo" => "warning"
        ]);
    }

    // Validar porcentaje acumulado excluyendo esta evaluación
    $validacion = $this->validar_porcentaje_total($id_seccion, $porcentaje, $id_evaluacion);
    if(!$validacion['valido']){
        return json_encode($validacion['alerta']);
    }
    
    
    
    
// Asegurar que todos los inscritos tengan puntaje_estudiante creado
$pdo = mainModel::conectar();
$inscritos = $pdo->prepare("
    SELECT i.id_inscripcion
    FROM inscripcion i
    WHERE i.id_seccion_fk = :id_seccion
      AND i.archivada = 1
");
$inscritos->execute([':id_seccion' => $id_seccion]);
$lista_inscripciones = $inscritos->fetchAll(PDO::FETCH_COLUMN);

foreach ($lista_inscripciones as $id_inscripcion) {
    $existe = $pdo->prepare("
        SELECT COUNT(*) FROM puntaje_estudiante
        WHERE id_evaluacion = :id_eval AND id_inscripcion = :id_insc
    ");
    $existe->execute([
        ':id_eval' => $id_evaluacion,
        ':id_insc' => $id_inscripcion
    ]);
    $yaExiste = $existe->fetchColumn();

    if (!$yaExiste) {
        $crear = $pdo->prepare("
            INSERT INTO puntaje_estudiante (id_evaluacion, id_inscripcion, puntaje, archivar_puntaje)
            VALUES (:id_eval, :id_insc, 0, 1)
        ");
        $crear->execute([
            ':id_eval' => $id_evaluacion,
            ':id_insc' => $id_inscripcion
        ]);
    }
}
    
    
    
    
    
    

    // Actualizar evaluación
    $datos_eval = [
        "ID" => $id_evaluacion,
        "Tipo" => $tipo,
        "Objetivo" => $objetivo,
        "Porcentaje" => $porcentaje,
        "Fecha" => $fecha
    ];

    $actualizado = evaluacionModelo::actualizar_evaluacion_modelo($datos_eval);

    if($actualizado){
        // Actualizar puntajes
        foreach($datos as $key => $valor){
            if(strpos($key, "puntaje_") === 0){
                $id_inscripcion = str_replace("puntaje_", "", $key);
                $puntaje = mainModel::limpiar_cadena($valor);

                $datos_puntaje = [
                    "Evaluacion" => $id_evaluacion,
                    "Inscripcion" => $id_inscripcion,
                    "Puntaje" => $puntaje
                ];

                evaluacionModelo::actualizar_o_insertar_puntaje_modelo($datos_puntaje);
            }
        }

                $this->actualizar_nota_final_seccion($id_seccion);

        return json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Evaluación actualizada",
            "Texto" => "La evaluación y los puntajes fueron modificados correctamente",
            "Tipo" => "success"
        ]);
    } else {
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error",
            "Texto" => "No se pudo actualizar la evaluación",
            "Tipo" => "error"
        ]);
    }
}




    /*--------- Archivar evaluación ---------*/
    public function archivar_evaluacion_controlador($id){
        
$id = mainModel::decryption($id);
        
        
// Obtener id_seccion desde la evaluación
 $pdo = mainModel::conectar();
$stmtSec = $pdo->prepare("SELECT id_seccion FROM evaluacion WHERE id_evaluacion = :id");
$stmtSec->execute([':id' => $id]);
$id_seccion = $stmtSec->fetchColumn();
        
$id = mainModel::limpiar_cadena($id);  
      //  $id = mainModel::limpiar_cadena($id);

        try {
         //   $pdo = mainModel::conectar();
            $pdo->beginTransaction();

            // 1. Archivar la evaluación
            $sqlEval = $pdo->prepare("
                UPDATE evaluacion 
                SET archivar_evaluacion = 0 
                WHERE id_evaluacion = :id
            ");
            $sqlEval->bindParam(":id", $id, PDO::PARAM_INT);
            if (!$sqlEval->execute()) {
                throw new Exception("Error al archivar la evaluación");
            }

            // 2. Archivar todos los puntajes relacionados
            $sqlPuntajes = $pdo->prepare("
                UPDATE puntaje_estudiante 
                SET archivar_puntaje = 0 
                WHERE id_evaluacion = :id
            ");
            $sqlPuntajes->bindParam(":id", $id, PDO::PARAM_INT);
            if (!$sqlPuntajes->execute()) {
                throw new Exception("Error al archivar los puntajes");
            }

            // 3. Confirmar todo
            $pdo->commit();
            $this->actualizar_nota_final_seccion($id_seccion);
         return json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Archivada",
            "Texto" => "La evaluación y sus puntajes fueron archivados correctamente",
            "Tipo" => "success"
        ]);
//            return mainModel::sweet_alert("recargar", "Archivada", "La evaluación y sus puntajes fueron archivados correctamente", "success");

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error al archivar evaluación: " . $e->getMessage());
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error",
            "Texto" => "No se pudo archivar evaluación ni sus puntajes",
            "Tipo" => "error"
        ]);
          //  return mainModel::sweet_alert("simple", "Error", "No se pudo archivar la evaluación ni sus puntajes", "error");
        }
    }

    /*--------- Restaurar evaluación ---------*/
    public function restaurar_evaluacion_controlador($id){
        
 
        $id = mainModel::limpiar_cadena($id);
        

        $sql = mainModel::conectar()->prepare("
            UPDATE evaluacion SET estado_evaluacion = 'Activa' WHERE id_evaluacion = :id
        ");
        $sql->bindParam(":id", $id);
        return $sql->execute()
            ? mainModel::sweet_alert("recargar", "Restaurada", "La evaluación fue restaurada correctamente", "success")
            : mainModel::sweet_alert("simple", "Error", "No se pudo restaurar la evaluación", "error");
    }

}