<?php
if ($peticionAjax) {
    require_once "../modelos/interaccionModelo.php";
} else {
    require_once "./modelos/interaccionModelo.php";
}

class interaccionControlador extends interaccionModelo {

    public function panel_general_interacciones_controlador_php() {
        $panel = [];

        try {
            $panel['ips'] = interaccionModelo::contar_ips_unicas_modelo()->fetchColumn();
            $panel['total'] = interaccionModelo::contar_total_interacciones_modelo()->fetchColumn();
            $panel['ultima'] = interaccionModelo::ultima_interaccion_modelo()->fetchColumn() ?? "Sin registro";

            $topCurso = interaccionModelo::cursos_top_mes_modelo()->fetch(PDO::FETCH_ASSOC);
            $panel['cursoTop'] = $topCurso["nombre_curso"] ?? "Sin datos";

            return $panel;
        } catch (Exception $e) {
            return [
                "ips" => "Error",
                "total" => "Error",
                "ultima" => "Error",
                "cursoTop" => "Error"
            ];
        }
    }

    public function top_cursos_del_mes_php() {
        $lista = [];

        try {
            $datos = interaccionModelo::cursos_top_mes_modelo();
            while ($row = $datos->fetch(PDO::FETCH_ASSOC)) {
                $lista[] = [
                    "nombre" => $row["nombre_curso"],
                    "total" => $row["total"]
                ];
            }
            return $lista;
        } catch (Exception $e) {
            return [];
        }
    }

    public function interacciones_recientes_php($limite = 10) {
        try {
            $sql = mainModel::conectar()->prepare("
                SELECT ib.*, c.nombre_curso, ip.direccion_ip
                FROM interaccion_banner ib
                JOIN curso c ON ib.id_curso_fk = c.id_curso
                JOIN ip_visitante ip ON ib.id_ip_fk = ip.id_ip_visitante
                ORDER BY ib.fecha_hora DESC
                LIMIT :limite
            ");
            $sql->bindValue(":limite", $limite, PDO::PARAM_INT);
            $sql->execute();
            return $sql->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
?>



