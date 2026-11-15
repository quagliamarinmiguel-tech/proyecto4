<?php
class PagosModel {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    public function obtenerResumenPagos($id_estudiante) {
        $stmt = $this->db->prepare("
            SELECT id_inscripcion FROM inscripcion
            WHERE id_estudiante_fk = ? AND archivada = 0
        ");
        $stmt->bind_param("i", $id_estudiante);
        $stmt->execute();
        $result = $stmt->get_result();

        $ids = [];
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['id_inscripcion'];
        }
        $stmt->close();

        $pagado = 0;
        $faltante = 0;

        if (!empty($ids)) {
            $in = implode(',', array_map('intval', $ids));

            $query = "SELECT SUM(monto) FROM transaccion WHERE id_inscripcion IN ($in) AND estatus_pago = 1";
            $pagado = (int)$this->db->query($query)->fetch_column();

            $query = "SELECT SUM(pago_faltante) FROM transaccion WHERE id_inscripcion IN ($in)";
            $faltante = (int)$this->db->query($query)->fetch_column();
        }

        return [
            'pagado' => $pagado,
            'faltante' => $faltante
        ];
    }
}

