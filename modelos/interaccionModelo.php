

<?php
require_once "mainModel.php";

class interaccionModelo extends mainModel {

    /* Contar IPs únicas */
    protected static function contar_ips_unicas_modelo() {
        $sql = mainModel::conectar()->prepare("SELECT COUNT(*) FROM ip_visitante");
        $sql->execute();
        return $sql;
    }

    /* Contar total de interacciones */
    protected static function contar_total_interacciones_modelo() {
        $sql = mainModel::conectar()->prepare("SELECT COUNT(*) FROM interaccion_banner");
        $sql->execute();
        return $sql;
    }

    /* Última interacción registrada */
    protected static function ultima_interaccion_modelo() {
        $sql = mainModel::conectar()->prepare("SELECT fecha_hora FROM interaccion_banner ORDER BY fecha_hora DESC LIMIT 1");
        $sql->execute();
        return $sql;
    }

    /* Cursos más clickeados en el último mes */
    protected static function cursos_top_mes_modelo() {
        $sql = mainModel::conectar()->prepare("
            SELECT c.nombre_curso, COUNT(*) AS total
            FROM interaccion_banner ib
            JOIN curso c ON ib.id_curso_fk = c.id_curso
            WHERE ib.fecha_hora >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
            GROUP BY c.id_curso
            ORDER BY total DESC
            LIMIT 10
        ");
        $sql->execute();
        return $sql;
    }

    protected static function eliminar_click_modelo($id) {
        $sql = mainModel::conectar()->prepare("DELETE FROM interaccion_banner WHERE id_interaccion_banner = :ID");
        $sql->bindParam(":ID", $id);
        $sql->execute();
        return $sql;
    }

    protected static function buscar_por_ip_modelo($ip) {
        $sql = mainModel::conectar()->prepare("
            SELECT ib.*, c.nombre_curso 
            FROM interaccion_banner ib
            JOIN curso c ON ib.id_curso_fk = c.id_curso
            JOIN ip_visitante ip ON ib.id_ip_fk = ip.id_ip_visitante
            WHERE ip.direccion_ip = :IP
            ORDER BY ib.fecha_hora DESC
        ");
        $sql->bindParam(":IP", $ip);
        $sql->execute();
        return $sql;
    }

    /* Buscar interacciones por curso */
    protected static function buscar_por_curso_modelo($idCurso) {
        $sql = mainModel::conectar()->prepare("
            SELECT ib.*, ip.direccion_ip 
            FROM interaccion_banner ib
            JOIN ip_visitante ip ON ib.id_ip_fk = ip.id_ip_visitante
            WHERE ib.id_curso_fk = :ID
            ORDER BY ib.fecha_hora DESC
        ");
        $sql->bindParam(":ID", $idCurso);
        $sql->execute();
        return $sql;
    }

    /* Buscar visitas por dirección IP */
    protected static function buscar_visitas_por_ip_modelo($ip) {
        $sql = mainModel::conectar()->prepare("
            SELECT v.*, ip.direccion_ip 
            FROM visitante v
            JOIN ip_visitante ip ON v.id_ip_fk = ip.id_ip_visitante
            WHERE ip.direccion_ip = :IP
            ORDER BY v.fecha_hora_entrada DESC
        ");
        $sql->bindParam(":IP", $ip);
        $sql->execute();
        return $sql;
    }

    /* Eliminar visita puntual */
    protected static function eliminar_visita_modelo($id) {
        $sql = mainModel::conectar()->prepare("DELETE FROM visitante WHERE id_visitante = :ID");
        $sql->bindParam(":ID", $id);
        $sql->execute();
        return $sql;
    }
}
?>