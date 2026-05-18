<?php
require_once __DIR__ . '/../modelos/mainModel.php';

class CrearEvaluacionControlador extends mainModel {

    public function obtener_cursos() {
        $conexion = mainModel::conectar();
        $consulta = $conexion->prepare("SELECT id_curso, nombre_curso FROM curso ORDER BY nombre_curso ASC");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener_secciones_por_curso($id_curso) {
        $conexion = mainModel::conectar();
        $consulta = $conexion->prepare("SELECT id_seccion, nombre_seccion FROM seccion WHERE id_curso_vigente_fk = :id");
        $consulta->execute([':id' => $id_curso]);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear_evaluacion($datos) {
        $conexion = mainModel::conectar();
        $consulta = $conexion->prepare("
            INSERT INTO evaluacion (tipo_evaluacion, obj, porcentaje, fecha_evaluacion, id_seccion)
            VALUES (:tipo, :obj, :porcentaje, :fecha, :id_seccion)
        ");

        $consulta->bindParam(':tipo', $datos['tipo'], PDO::PARAM_STR);
        $consulta->bindParam(':obj', $datos['obj'], PDO::PARAM_STR);
        $consulta->bindParam(':porcentaje', $datos['porcentaje'], PDO::PARAM_INT);
        $consulta->bindParam(':fecha', $datos['fecha'], PDO::PARAM_STR);
        $consulta->bindParam(':id_seccion', $datos['id_seccion'], PDO::PARAM_INT);

        return $consulta->execute();
    }


}
