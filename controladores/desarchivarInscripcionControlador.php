<?php
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = mainModel::limpiar_cadena($_POST['id_inscripcion'] ?? '');

    if (empty($id)) {
        echo json_encode(['status' => 'error', 'mensaje' => 'ID de inscripción no recibido.']);
        exit;
    }

    try {
        $conexion = mainModel::conectar();

        // Verificar que la inscripción exista y esté archivada
        $verificar = $conexion->prepare("SELECT archivada FROM inscripcion WHERE id_inscripcion = :id");
        $verificar->execute([':id' => $id]);

        if ($verificar->rowCount() !== 1) {
            throw new Exception("Inscripción no encontrada.");
        }

        $estado = $verificar->fetch()['archivada'];
        if ($estado == 0) {
            throw new Exception("La inscripción ya está activa.");
        }

        $conexion->beginTransaction();

        // Desarchivar inscripción
        $conexion->prepare("UPDATE inscripcion SET archivada = 1 WHERE id_inscripcion = :id")
                 ->execute([':id' => $id]);

        // Desarchivar transacciones relacionadas (usando estatus_pago = 0)
        $conexion->prepare("UPDATE transaccion SET estatus_pago = 0 WHERE id_inscripcion = :id")
                 ->execute([':id' => $id]);

        $conexion->commit();

        echo json_encode(['status' => 'success', 'mensaje' => 'Inscripción y transacciones desarchivadas correctamente.']);

    } catch (Exception $e) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
    }

} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Acceso no permitido.']);
}
