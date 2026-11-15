<?php
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conexion = mainModel::conectar();

        $id_inscripcion = mainModel::limpiar_cadena($_POST['id_inscripcion'] ?? '');

        if (empty($id_inscripcion)) {
            throw new Exception("No se recibió la inscripción a archivar.");
        }

        // Verificar que la inscripción exista
        $check = $conexion->prepare("SELECT id_inscripcion FROM inscripcion WHERE id_inscripcion = :id");
        $check->execute([':id' => $id_inscripcion]);
        if (!$check->fetch()) {
            throw new Exception("La inscripción no existe.");
        }

        // Iniciar transacción SQL
        $conexion->beginTransaction();

        // Archivar inscripción
        $updateInscripcion = $conexion->prepare("
            UPDATE inscripcion 
            SET archivada = 0 
            WHERE id_inscripcion = :id
        ");
        $updateInscripcion->execute([':id' => $id_inscripcion]);

        // Archivar transacciones relacionadas
        $updateTransacciones = $conexion->prepare("
            UPDATE transaccion 
            SET pago_faltante = 0, estatus_pago = 1
            WHERE id_inscripcion = :id
        ");
        $updateTransacciones->execute([':id' => $id_inscripcion]);

        $conexion->commit();

        echo json_encode([
            'status' => 'success',
            'mensaje' => 'Inscripción y transacciones archivadas correctamente.'
        ]);

    } catch (Exception $e) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'mensaje' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo "Método no permitido.";
}
