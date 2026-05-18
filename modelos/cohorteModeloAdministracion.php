<?php
require_once "mainModel.php";

class cohorteModeloAdministracion extends mainModel {

    /* Modelo para obtener datos por tipo e ID */
    public function datos_cohorte_modelo($tipo, $id) {
        $sql = self::conectar()->prepare("SELECT * FROM cohorte WHERE id_cohorte = :id LIMIT 1");
        $sql->bindParam(":id", $id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /* Modelo para paginación de cohortes con búsqueda */
    public function obtener_cohortes($inicio, $limite, $busqueda = "") {
        $conexion = self::conectar();
        if ($busqueda != "") {
            $consulta = "SELECT SQL_CALC_FOUND_ROWS * FROM cohorte 
                        WHERE archivar_cohorte = 1 AND estado_cohorte = 'Vigente'
                        AND (numero_cohorte LIKE '%$busqueda%' OR anio_cohorte LIKE '%$busqueda%' OR tipo_cohorte LIKE '%$busqueda%')
                        ORDER BY anio_cohorte DESC, numero_cohorte DESC 
                        LIMIT $inicio, $limite";
        } else {
            $consulta = "SELECT SQL_CALC_FOUND_ROWS * FROM cohorte 
                        WHERE archivar_cohorte = 1 AND estado_cohorte = 'Vigente'
                        ORDER BY anio_cohorte DESC, numero_cohorte DESC 
                        LIMIT $inicio, $limite";
        }

        $datos = $conexion->query($consulta)->fetchAll(PDO::FETCH_ASSOC);
        $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();

        return ["datos" => $datos, "total" => $total];
    }

    /* Modelo para búsqueda específica por número y año */
    public function buscar_cohorte($inicio, $limite, $numero, $anio) {
        $conexion = self::conectar();
        $consulta = $conexion->prepare("
            SELECT SQL_CALC_FOUND_ROWS * FROM cohorte 
            WHERE numero_cohorte = :numero AND anio_cohorte = :anio AND archivar_cohorte = 1
            ORDER BY id_cohorte DESC 
            LIMIT $inicio, $limite
        ");
        $consulta->bindParam(":numero", $numero);
        $consulta->bindParam(":anio", $anio);
        $consulta->execute();
        $datos = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();

        return ["datos" => $datos, "total" => $total];
    }

    /* Modelo para traer cohortes básicos */
    public function listar_cohortes_simples() {
        $conexion = self::conectar();
        $consulta = $conexion->query("
            SELECT id_cohorte, numero_cohorte, anio_cohorte, tipo_cohorte 
            FROM cohorte 
            WHERE archivar_cohorte = 0
            ORDER BY anio_cohorte DESC, numero_cohorte DESC
        ");
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
}