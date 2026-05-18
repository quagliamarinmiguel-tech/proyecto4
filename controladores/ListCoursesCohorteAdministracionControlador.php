<?php
require_once __DIR__ . '/../modelos/mainModel.php';

class ListCursosCohorteControlador extends mainModel {

    public function obtener_nombre_cohorte($id_encriptado) {
        $id = mainModel::decryption($id_encriptado);
        $id = mainModel::limpiar_cadena($id);

        $conexion = mainModel::conectar();
        $consulta = $conexion->prepare("
            SELECT tipo_cohorte, numero_cohorte, anio_cohorte 
            FROM cohorte 
            WHERE id_cohorte = :id 
            AND archivar_cohorte = 1  -- SOLO cohortes NO archivados
        ");
        $consulta->execute([':id' => $id]);
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public function listar_cursos_por_cohorte($id_encriptado) {
        $id = mainModel::decryption($id_encriptado);
        $id = mainModel::limpiar_cadena($id);

        $conexion = mainModel::conectar();
        $consulta = $conexion->prepare("
            SELECT
                c.id_curso,
                c.nombre_curso,
                c.tipo_curso,
                (
                    SELECT COUNT(*)
                    FROM seccion s
                    WHERE s.id_curso_vigente_fk = cv.id_curso_vigente
                    AND s.archivar_seccion = 1  -- SOLO secciones NO archivadas
                ) AS cantidad_secciones
            FROM curso c
            INNER JOIN curso_vigente cv ON cv.id_curso_fk = c.id_curso
            WHERE cv.id_cohorte_fk = :id_cohorte
            AND cv.estado_curso_vigente = 1  -- SOLO cursos vigentes activos
            AND c.estado_curso = 1  -- SOLO cursos activos
        ");
        $consulta->execute([':id_cohorte' => $id]);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function paginador_cursos_por_cohorte($id_encriptado, $pagina, $registros) {
        $id = mainModel::decryption($id_encriptado);
        $id = mainModel::limpiar_cadena($id);
        $pagina = (int) mainModel::limpiar_cadena($pagina);
        $registros = (int) mainModel::limpiar_cadena($registros);
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        $conexion = mainModel::conectar();

        $sql = "SELECT SQL_CALC_FOUND_ROWS 
                    c.id_curso,
                    c.nombre_curso,
                    c.tipo_curso,
                    (
                        SELECT COUNT(*) 
                        FROM seccion s 
                        WHERE s.id_curso_vigente_fk = cv.id_curso_vigente
                        AND s.archivar_seccion = 1  -- SOLO secciones NO archivadas
                    ) AS cantidad_secciones
                FROM curso c
                INNER JOIN curso_vigente cv ON cv.id_curso_fk = c.id_curso
                WHERE cv.id_cohorte_fk = :id
                AND cv.estado_curso_vigente = 1  -- SOLO cursos vigentes activos
                AND c.estado_curso = 1  -- SOLO cursos activos
                ORDER BY c.nombre_curso ASC
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
}
?>