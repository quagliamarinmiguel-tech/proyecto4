<?php
class ObjetivosModel {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    public function obtenerProgreso($id_estudiante) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN nota_final >= 10 AND nota_final IS NOT NULL THEN 1 ELSE 0 END) AS aprobadas
            FROM inscripcion
            WHERE id_estudiante_fk = ? AND archivada = 0 AND estado_inscripcion = 'Activa'
        ");
        
        if (!$stmt) {
            throw new Exception('Error preparando consulta: ' . $this->db->error);
        }
        
        $stmt->bind_param("i", $id_estudiante);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        if (!$data) {
            return [
                'aprobadas' => 0,
                'pendientes' => 0,
                'total' => 0
            ];
        }

        return [
            'aprobadas' => (int)$data['aprobadas'],
            'pendientes' => (int)$data['total'] - (int)$data['aprobadas'],
            'total' => (int)$data['total']
        ];
    }
}
?>