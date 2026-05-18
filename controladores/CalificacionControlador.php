<?php

require_once __DIR__ . "/../modelos/calificacionModelo.php";


class calificacionControlador extends calificacionModelo {

    /*--------- Agregar Calificación ---------*/
    public function agregar_calificacion_controlador() {
        // 1. Limpiar entradas
        $inscripcion  = mainModel::limpiar_cadena($_POST['id_inscripcion_reg']);
        $evaluacion   = mainModel::limpiar_cadena($_POST['id_evaluacion_reg']);
        $puntaje      = mainModel::limpiar_cadena($_POST['puntaje_reg']);

        // 2. Validar campos obligatorios
        if($inscripcion == "" || $evaluacion == "" || $puntaje == ""){
            return [
                "Alerta"=>"simple",
                "Titulo"=>"Faltan datos",
                "Texto"=>"Debes completar todos los campos obligatorios",
                "Tipo"=>"error"
            ];
            exit();
        }

        // 3. Validar puntaje numérico entre 0 y 100
        if(!is_numeric($puntaje) || $puntaje < 0 || $puntaje > 100){
            return [
                "Alerta"=>"simple",
                "Titulo"=>"Puntaje inválido",
                "Texto"=>"El puntaje debe ser un número entre 0 y 100",
                "Tipo"=>"error"
            ];
            exit();
        }

        // 4. Verificar existencia de inscripción
        $check_ins = mainModel::ejecutar_consulta_simple(
            "SELECT id_inscripcion 
             FROM inscripcion 
             WHERE id_inscripcion='$inscripcion'"
        );
        if($check_ins->rowCount() <= 0){
            return [
                "Alerta"=>"simple",
                "Titulo"=>"Inscripción no encontrada",
                "Texto"=>"La inscripción seleccionada no existe",
                "Tipo"=>"error"
            ];
            exit();
        }

        // 5. Verificar existencia de evaluación
        $check_eval = mainModel::ejecutar_consulta_simple(
            "SELECT id_evaluacion 
             FROM evaluacion 
             WHERE id_evaluacion='$evaluacion'"
        );
        if($check_eval->rowCount() <= 0){
            return [
                "Alerta"=>"simple",
                "Titulo"=>"Evaluación no encontrada",
                "Texto"=>"La evaluación seleccionada no existe",
                "Tipo"=>"error"
            ];
            exit();
        }

        // 6. Preparar datos y llamar al modelo
        $datos = [
            "Inscripcion" => $inscripcion,
            "Evaluacion"  => $evaluacion,
            "Puntaje"     => $puntaje
        ];

        $insert = calificacionModelo::agregar_calificacion_modelo($datos);

        // 7. Devolver alerta según resultado
        if($insert->rowCount() == 1){
            return [
                "Alerta"=>"limpiar",
                "Titulo"=>"Calificación registrada",
                "Texto"=>"La calificación se ha almacenado correctamente",
                "Tipo"=>"success"
            ];
        } else {
            return [
                "Alerta"=>"simple",
                "Titulo"=>"Error al registrar",
                "Texto"=>"No se pudo guardar la calificación, intente de nuevo",
                "Tipo"=>"error"
            ];
        }

    }

    public function obtener_inscripciones_controlador() {
        $sql = mainModel::conectar()->prepare("
            SELECT i.id_inscripcion, i.id_seccion_fk, e.nombre, e.apellido
            FROM inscripcion i
            INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
            ORDER BY e.apellido ASC
        ");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener_evaluaciones_controlador() {
        $sql = mainModel::conectar()->prepare("
            SELECT id_evaluacion, tipo_evaluacion, fecha_evaluacion
            FROM evaluacion
            ORDER BY fecha_evaluacion DESC
        ");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }


    /*--------- Listar Calificaciones (paginador) ---------*/
    public function paginador_calificacion_controlador($pagina, $registros, $url, $busqueda) {
        $pagina   = (int) mainModel::limpiar_cadena($pagina);
        $registros= (int) mainModel::limpiar_cadena($registros);
        $url      = SERVERURL."index.php?views=". mainModel::limpiar_cadena($url) ."&page=";
        $busqueda = mainModel::limpiar_cadena($busqueda);
        $inicio   = ($pagina>1) ? (($pagina*$registros)-$registros) : 0;

        // Consulta uniendo inscripcion, estudiante y evaluación
        $sql = "
            SELECT 
                pe.id_inscripcion,
                pe.id_evaluacion,
                CONCAT(e.nombre,' ', e.apellido)    AS alumno,
                ev.tipo_evaluacion,
                pe.puntaje
            FROM puntaje_estudiante pe
            INNER JOIN inscripcion i ON i.id_inscripcion = pe.id_inscripcion
            INNER JOIN estudiante e     ON e.id_estudiante = i.id_estudiante_fk
            INNER JOIN evaluacion ev    ON ev.id_evaluacion = pe.id_evaluacion
            WHERE ev.tipo_evaluacion LIKE '%$busqueda%'
            ORDER BY e.apellido ASC
            LIMIT $inicio, $registros
        ";

        $conexion = mainModel::conectar();
        $datos    = $conexion->query($sql)->fetchAll();
        $total    = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
        $Npages   = ceil($total/$registros);

        // Genera la tabla HTML
        $tabla = '<div class="table-responsive"><table class="table table-bordered">';
        $tabla .= '<thead><tr>
                       <th>Inscripción</th>
                       <th>Evaluación</th>
                       <th>Alumno</th>
                       <th>Puntaje</th>
                       <th>Acciones</th>
                   </tr></thead><tbody>';

        if($total>0) {
            foreach($datos as $row){
                $tabla .= '<tr>
                    <td>'.$row['id_inscripcion'].'</td>
                    <td>'.$row['tipo_evaluacion'].'</td>
                    <td>'.$row['alumno'].'</td>
                    <td>'.$row['puntaje'].'</td>
                    <td>
                        <a href="'.SERVERURL.'index.php?views=calificacion-update&id_ins='.mainModel::encryption($row['id_inscripcion'])
                                                             .'&id_ev='.mainModel::encryption($row['id_evaluacion']).'" 
                           class="btn btn-sm btn-warning">Editar</a>
                        <button class="btn btn-sm btn-danger btnEliminarCalificacion" 
                                data-ins="'.mainModel::encryption($row['id_inscripcion']).'"
                                data-ev="'.mainModel::encryption($row['id_evaluacion']).'">
                                Eliminar
                        </button>
                    </td>
                </tr>';
            }
        } else {
            $tabla .= '<tr><td colspan="5" class="text-center">No hay calificaciones.</td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        // Paginador
        if($total>0 && $pagina <= $Npages){
            $tabla .= '<p class="text-right">
                Mostrando '.($inicio+1).' al '.min($inicio+$registros,$total).' de '.$total.'
            </p>';
            $tabla .= mainModel::paginador_tablas($pagina,$Npages,$url,7);
        }

        return $tabla;
    }


    /*--------- Obtener datos de una calificación ---------*/
    public function datos_calificacion_controlador($ins_enc, $ev_enc) {
        $ins = mainModel::decryption($ins_enc);
        $ev  = mainModel::decryption($ev_enc);
        return calificacionModelo::datos_calificacion_modelo($ins, $ev);
    }


    /*--------- Actualizar calificación ---------*/
    public function actualizar_calificacion_controlador() {
        // 1. Limpiar entradas
        $ins     = mainModel::limpiar_cadena($_POST['id_inscripcion_up']);
        $ev      = mainModel::limpiar_cadena($_POST['id_evaluacion_up']);
        $puntaje = mainModel::limpiar_cadena($_POST['puntaje_up']);

        // 2. Validar campos obligatorios
        if ($ins == "" || $ev == "" || $puntaje == "") {
            return([
                "Alerta" => "simple",
                "Titulo" => "Faltan datos",
                "Texto"  => "Debes completar todos los campos obligatorios",
                "Tipo"   => "error"
            ]);
            exit();
        }

        // 3. Validar puntaje numérico entre 0 y 100
        if (!is_numeric($puntaje) || $puntaje < 0 || $puntaje > 100) {
            return([
                "Alerta" => "simple",
                "Titulo" => "Puntaje inválido",
                "Texto"  => "Ingresa un puntaje numérico entre 0 y 100",
                "Tipo"   => "error"
            ]);
            exit();
        }

        // 4. Verificar existencia de inscripción
        $check_ins = mainModel::ejecutar_consulta_simple("
            SELECT id_inscripcion 
            FROM inscripcion 
            WHERE id_inscripcion = '$ins'
        ");
        if ($check_ins->rowCount() <= 0) {
            return([
                "Alerta" => "simple",
                "Titulo" => "Inscripción no encontrada",
                "Texto"  => "La inscripción seleccionada no existe",
                "Tipo"   => "error"
            ]);
            exit();
        }

        // 5. Verificar existencia de evaluación
        $check_eval = mainModel::ejecutar_consulta_simple("
            SELECT id_evaluacion 
            FROM evaluacion 
            WHERE id_evaluacion = '$ev'
        ");
        if ($check_eval->rowCount() <= 0) {
            return([
                "Alerta" => "simple",
                "Titulo" => "Evaluación no encontrada",
                "Texto"  => "La evaluación seleccionada no existe",
                "Tipo"   => "error"
            ]);
            exit();
        }

        // 6. Verificar que la calificación exista
        $check_calif = mainModel::ejecutar_consulta_simple("
            SELECT * 
            FROM puntaje_estudiante 
            WHERE id_inscripcion = '$ins' AND id_evaluacion = '$ev'
        ");
        if ($check_calif->rowCount() <= 0) {
            return([
                "Alerta" => "simple",
                "Titulo" => "Calificación no encontrada",
                "Texto"  => "No existe una calificación registrada para esta combinación",
                "Tipo"   => "error"
            ]);
            exit();
        }

        // 7. Preparar datos y actualizar
        $datos = [
            "Inscripcion" => $ins,
            "Evaluacion"  => $ev,
            "Puntaje"     => $puntaje
        ];

        $upd = calificacionModelo::actualizar_calificacion_modelo($datos);

        // 8. Respuesta
        return($upd->rowCount() == 1
            ? [
                "Alerta" => "recargar",
                "Titulo" => "Actualizado",
                "Texto"  => "Calificación actualizada correctamente",
                "Tipo"   => "success"
            ]
            : [
                "Alerta" => "simple",
                "Titulo" => "Sin cambios",
                "Texto"  => "No se realizaron modificaciones",
                "Tipo"   => "warning"
            ]
        );
    }


    public function eliminar_calificacion_controlador() {
        // 1. Validar y desencriptar entradas
        if (!isset($_POST['ins_enc']) || !isset($_POST['ev_enc'])) {
            return([
                "Alerta" => "simple",
                "Titulo" => "Datos faltantes",
                "Texto"  => "No se recibió la información necesaria para eliminar",
                "Tipo"   => "error"
            ]);
            exit();
        }

        $ins = mainModel::decryption($_POST['ins_enc']);
        $ev  = mainModel::decryption($_POST['ev_enc']);

        // 2. Verificar que la calificación exista
        $check = mainModel::ejecutar_consulta_simple("
            SELECT * 
            FROM puntaje_estudiante 
            WHERE id_inscripcion = '$ins' AND id_evaluacion = '$ev'
        ");
        if ($check->rowCount() <= 0) {
            return([
                "Alerta" => "simple",
                "Titulo" => "No encontrada",
                "Texto"  => "La calificación que intentas eliminar no existe",
                "Tipo"   => "warning"
            ]);
            exit();
        }

        // 3. Eliminar calificación
        $del = calificacionModelo::eliminar_calificacion_modelo($ins, $ev);

        // 4. Respuesta
        return($del->rowCount() == 1
            ? [
                "Alerta" => "recargar",
                "Titulo" => "Eliminado",
                "Texto"  => "La calificación ha sido eliminada correctamente",
                "Tipo"   => "success"
            ]
            :[
                "Alerta" => "simple",
                "Titulo" => "Error",
                "Texto"  => "No se pudo eliminar la calificación, intenta nuevamente",
                "Tipo"   => "error"
            ]   
        );
    }

}
?>