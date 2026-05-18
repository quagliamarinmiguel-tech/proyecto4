<?php
require_once __DIR__ . '/../modelos/mainModel.php';

class ListSeccionesCursoControlador extends mainModel {

    public function obtener_nombre_curso($id_curso) {
        $conexion = mainModel::conectar();
        $consulta = $conexion->prepare("SELECT nombre_curso FROM curso WHERE id_curso = :id AND estado_curso = 1");
        $consulta->execute([':id' => $id_curso]);
        return $consulta->fetchColumn();
    }

    public function listar_secciones_por_curso($id_curso) {
        $conexion = mainModel::conectar();
        $consulta = $conexion->prepare("
            SELECT 
                s.id_seccion,
                s.nombre_seccion,
                CONCAT(p.nombre_profesor, ' ', p.apellido_profesor) AS nombre_completo_profesor,
                (
                    SELECT COUNT(*) 
                    FROM inscripcion i 
                    WHERE i.id_seccion_fk = s.id_seccion
                    AND i.archivada = 1
                ) AS cantidad_estudiantes,
                (
                    SELECT COUNT(*) 
                    FROM evaluacion e 
                    WHERE e.id_seccion = s.id_seccion
                    AND e.archivar_evaluacion = 1
                ) AS cantidad_evaluaciones
            FROM seccion s
            INNER JOIN profesor p ON s.ci_profesor = p.ci_profesor
            WHERE s.id_curso_vigente_fk = :id_curso
                AND s.archivar_seccion = 1
                AND p.estado_profesor = 1
        ");
        $consulta->execute([':id_curso' => $id_curso]);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function paginador_secciones_por_curso($id_curso, $pagina, $registros) {
        $id = mainModel::limpiar_cadena($id_curso);
        $pagina = (int) mainModel::limpiar_cadena($pagina);
        $registros = (int) mainModel::limpiar_cadena($registros);
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        $conexion = mainModel::conectar();

        // SQL corregido sin comentarios HTML
        $sql = "SELECT SQL_CALC_FOUND_ROWS 
                    s.id_seccion,
                    s.nombre_seccion,
                    CONCAT(p.nombre_profesor, ' ', p.apellido_profesor) AS nombre_completo_profesor,
                    (
                        SELECT COUNT(*) 
                        FROM inscripcion i 
                        WHERE i.id_seccion_fk = s.id_seccion
                        AND i.archivada = 1
                    ) AS cantidad_estudiantes,
                    (
                        SELECT COUNT(*) 
                        FROM evaluacion e 
                        WHERE e.id_seccion = s.id_seccion
                        AND e.archivar_evaluacion = 1
                    ) AS cantidad_evaluaciones
                FROM seccion s
                LEFT JOIN profesor p ON s.ci_profesor = p.ci_profesor
                WHERE s.id_curso_vigente_fk = :id_curso
                    AND s.archivar_seccion = 1
                    AND p.estado_profesor = 1
                ORDER BY s.nombre_seccion ASC
                LIMIT $inicio, $registros";

        $consulta = $conexion->prepare($sql);
        $consulta->bindParam(":id_curso", $id, PDO::PARAM_INT);
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
}