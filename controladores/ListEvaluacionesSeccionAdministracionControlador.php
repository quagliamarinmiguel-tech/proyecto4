<?php
require_once __DIR__ . '/../modelos/mainModel.php';

class ListEvaluacionesSeccionControlador extends mainModel {

    public function obtener_nombre_seccion($id_seccion) {
        $conexion = mainModel::conectar();
        $consulta = $conexion->prepare("
            SELECT nombre_seccion 
            FROM seccion 
            WHERE id_seccion = :id
            AND archivar_seccion = 1  -- SOLO secciones NO archivadas
        ");
        $consulta->execute([':id' => $id_seccion]);
        return $consulta->fetchColumn();
    }

    public function listar_evaluaciones_por_seccion($id_seccion) {
        $conexion = mainModel::conectar();
        $consulta = $conexion->prepare("
            SELECT 
                id_evaluacion,
                tipo_evaluacion,
                obj,
                porcentaje,
                fecha_evaluacion
            FROM evaluacion
            WHERE id_seccion = :id_seccion
            AND archivar_evaluacion = 1  -- SOLO evaluaciones NO archivadas
            ORDER BY fecha_evaluacion DESC
        ");
        $consulta->execute([':id_seccion' => $id_seccion]);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function paginador_evaluaciones_por_seccion($id_seccion, $pagina, $registros) {
        $id = mainModel::limpiar_cadena($id_seccion);
        $pagina = (int) mainModel::limpiar_cadena($pagina);
        $registros = (int) mainModel::limpiar_cadena($registros);
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        $conexion = mainModel::conectar();
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM evaluacion 
                WHERE id_seccion = :id 
                AND archivar_evaluacion = 1  -- SOLO evaluaciones NO archivadas
                ORDER BY fecha_evaluacion ASC
                LIMIT $inicio, $registros";

        $consulta = $conexion->prepare($sql);
        $consulta->bindParam(":id", $id, PDO::PARAM_INT);
        $consulta->execute();
        $datos = $consulta->fetchAll(PDO::FETCH_ASSOC);

        $total = $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
        $Npaginas = ceil($total / $registros);

        return ["datos" => $datos, "total" => $total, "paginas" => $Npaginas];
    }
}
?>