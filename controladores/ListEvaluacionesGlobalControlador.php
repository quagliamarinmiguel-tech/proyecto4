<?php
require_once __DIR__ . '/../modelos/mainModel.php';

class ListEvaluacionesGlobalControlador extends mainModel {

    // Obtener lista de secciones con nombres de cursos
    public function obtener_secciones_con_cursos() {
        $conexion = mainModel::conectar();
        $sql = "SELECT 
                    s.id_seccion, 
                    s.nombre_seccion, 
                    c.nombre_curso
                FROM seccion s
                INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
                INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
                WHERE s.archivar_seccion = 1  -- SOLO secciones NO archivadas
                AND cv.estado_curso_vigente = 1  -- SOLO cursos vigentes activos
                AND c.estado_curso = 1  -- SOLO cursos activos
                ORDER BY c.nombre_curso ASC, s.nombre_seccion ASC";

        return $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Paginar evaluaciones según sección seleccionada
    public function paginador_evaluaciones_por_seccion($id_seccion, $pagina, $registros) {
        $id = mainModel::limpiar_cadena($id_seccion);
        $pagina = (int) mainModel::limpiar_cadena($pagina);
        $registros = (int) mainModel::limpiar_cadena($registros);
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        $conexion = mainModel::conectar();
        $sql = "SELECT SQL_CALC_FOUND_ROWS 
                    id_evaluacion,
                    tipo_evaluacion,
                    obj,
                    porcentaje,
                    fecha_evaluacion
                FROM evaluacion 
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

        return [
            "datos" => $datos,
            "total" => $total,
            "paginas" => $Npaginas
        ];
    }

    public function obtener_evaluacion_por_id($id) {
        $conexion = mainModel::conectar();
        $consulta = $conexion->prepare("
            SELECT * 
            FROM evaluacion 
            WHERE id_evaluacion = :id
            AND archivar_evaluacion = 1  -- SOLO evaluaciones NO archivadas
        ");
        $consulta->execute([':id' => $id]);
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar_evaluacion($datos) {
        $conexion = mainModel::conectar();
        $consulta = $conexion->prepare("
            UPDATE evaluacion 
            SET tipo_evaluacion = :tipo, 
                obj = :obj, 
                porcentaje = :porcentaje, 
                fecha_evaluacion = :fecha 
            WHERE id_evaluacion = :id
            AND archivar_evaluacion = 1  -- SOLO evaluaciones NO archivadas
        ");
        return $consulta->execute($datos);
    }
}
?>