<?php
require_once __DIR__ . '/../modelos/mainModel.php';

class ListEstudiantesSeccionControlador extends mainModel {

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

    public function listar_estudiantes_por_seccion($id_seccion) {
        $conexion = mainModel::conectar();
        $consulta = $conexion->prepare("
            SELECT 
                e.id_estudiante,
                e.nombre,
                e.apellido,
                e.fecha_nacimiento,
                e.telefono_estudiante,
                e.correo_estudiante,
                u.Nombre AS nombre_usuario
            FROM inscripcion i
            INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
            LEFT JOIN usuario_estudiante ue ON ue.id_estudiante_fk = e.id_estudiante
            LEFT JOIN usuario u ON u.id_usuario = ue.id_usuario_fk
            WHERE i.id_seccion_fk = :id_seccion
            AND i.archivada = 1  -- SOLO inscripciones NO archivadas (activas)
            AND e.estado_estudiante = 1  -- SOLO estudiantes activos
        ");
        $consulta->execute([':id_seccion' => $id_seccion]);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function paginador_estudiantes_por_seccion($id_seccion, $pagina, $registros) {
        $id = mainModel::limpiar_cadena($id_seccion);
        $pagina = (int) mainModel::limpiar_cadena($pagina);
        $registros = (int) mainModel::limpiar_cadena($registros);
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        $conexion = mainModel::conectar();

        $sql = "SELECT SQL_CALC_FOUND_ROWS 
                    e.id_estudiante,
                    e.nombre,
                    e.apellido,
                    e.fecha_nacimiento,
                    e.telefono_estudiante,
                    e.correo_estudiante,
                    u.Nombre AS nombre_usuario
                FROM inscripcion i
                INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
                LEFT JOIN usuario_estudiante ue ON ue.id_estudiante_fk = e.id_estudiante
                LEFT JOIN usuario u ON u.id_usuario = ue.id_usuario_fk
                WHERE i.id_seccion_fk = :id
                AND i.archivada = 1  -- SOLO inscripciones NO archivadas (activas)
                AND e.estado_estudiante = 1  -- SOLO estudiantes activos
                ORDER BY e.apellido ASC, e.nombre ASC
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