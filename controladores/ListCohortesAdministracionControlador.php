<?php
require_once __DIR__ . '/../modelos/mainModel.php';

class ListCohortesControlador extends mainModel {

    public function listar_cohortes_con_cursos($inicio = 0, $limite = 5, $busqueda = "") {
        $conexion = mainModel::conectar();

        // Preparar el término de búsqueda con comodines
        $busqueda = "%$busqueda%";

        $consulta = $conexion->prepare("
            SELECT 
                coh.id_cohorte,
                coh.tipo_cohorte,
                coh.estado_cohorte,
                coh.numero_cohorte,
                coh.anio_cohorte,
                coh.fecha_inicio,
                coh.fecha_fin,
                COUNT(cv.id_curso_fk) AS cantidad_cursos
            FROM cohorte coh
            LEFT JOIN curso_vigente cv ON cv.id_cohorte_fk = coh.id_cohorte
            WHERE (coh.tipo_cohorte LIKE :buscar
               OR coh.estado_cohorte LIKE :buscar
               OR coh.numero_cohorte LIKE :buscar
               OR coh.anio_cohorte LIKE :buscar)
               AND coh.archivar_cohorte = 1  -- SOLO cohortes NO archivados (activos)
            GROUP BY coh.id_cohorte
            ORDER BY coh.anio_cohorte DESC, coh.numero_cohorte DESC
            LIMIT :inicio, :limite
        ");

        // Enlazar parámetros
        $consulta->bindParam(":buscar", $busqueda);
        $consulta->bindParam(":inicio", $inicio, PDO::PARAM_INT);
        $consulta->bindParam(":limite", $limite, PDO::PARAM_INT);

        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contar_cohortes_filtrados($busqueda = "") {
        $conexion = mainModel::conectar();
        $busqueda = "%$busqueda%";

        $consulta = $conexion->prepare("
            SELECT COUNT(DISTINCT id_cohorte) AS total
            FROM cohorte
            WHERE (tipo_cohorte LIKE :buscar
               OR estado_cohorte LIKE :buscar
               OR numero_cohorte LIKE :buscar
               OR anio_cohorte LIKE :buscar)
               AND archivar_cohorte = 1  -- SOLO cohortes NO archivados (activos)
        ");
        $consulta->bindParam(":buscar", $busqueda);
        $consulta->execute();

        return $consulta->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
?>