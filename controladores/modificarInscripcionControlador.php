<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

error_log("ID recibido para modificar: " . ($_POST['id_inscripcion'] ?? 'NULO'));
error_log("Curso nuevo: " . ($_POST['curso'] ?? 'NULO'));
error_log("Sección nueva: " . ($_POST['id_seccion_fk'] ?? 'NULO'));
error_log("Estado nuevo: " . ($_POST['estado_inscripcion'] ?? 'NULO'));

require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexion = mainModel::conectar();

    $id_inscripcion = mainModel::limpiar_cadena($_POST['id_inscripcion'] ?? '');
    $id_seccion_fk = mainModel::limpiar_cadena($_POST['id_seccion_fk'] ?? '');
    $estado = mainModel::limpiar_cadena($_POST['estado_inscripcion'] ?? '');

    if (empty($id_inscripcion) || empty($id_seccion_fk) || empty($estado)) {
        exit("Faltan datos obligatorios.");
    }

    try {
        // Obtener el nuevo curso desde la sección
        $consultaCurso = $conexion->prepare("SELECT id_curso_vigente_fk FROM seccion WHERE id_seccion = :id");
        $consultaCurso->execute([':id' => $id_seccion_fk]);

        if ($consultaCurso->rowCount() !== 1) {
            throw new Exception("No se encontró el curso asociado a la sección seleccionada.");
        }

        $id_curso_fk = $consultaCurso->fetch()['id_curso_vigente_fk'];

        // Obtener el estudiante de la inscripción actual
        $consultaEst = $conexion->prepare("SELECT id_estudiante_fk FROM inscripcion WHERE id_inscripcion = :id");
        $consultaEst->execute([':id' => $id_inscripcion]);

        if ($consultaEst->rowCount() !== 1) {
            throw new Exception("No se encontró la inscripción original.");
        }

        $id_estudiante = $consultaEst->fetch()['id_estudiante_fk'];

        // Verificar si ya está inscrito en ese curso (en otra inscripción)
        $verificar = $conexion->prepare("
            SELECT COUNT(*) FROM inscripcion 
            WHERE id_estudiante_fk = :estudiante 
              AND id_curso_vigente_fk = :curso 
              AND id_inscripcion != :actual 
              AND estado_inscripcion = 'Activa'
        ");
        $verificar->execute([
            ':estudiante' => $id_estudiante,
            ':curso' => $id_curso_fk,
            ':actual' => $id_inscripcion
        ]);

        if ($verificar->fetchColumn() > 0) {
            throw new Exception("Este estudiante ya está inscrito activamente en ese curso.");
        }

        // Actualizar inscripción
        $actualizar = $conexion->prepare("
            UPDATE inscripcion SET
                id_curso_vigente_fk = :curso,
                id_seccion_fk = :seccion,
                estado_inscripcion = :estado
            WHERE id_inscripcion = :id
        ");

        $actualizar->execute([
            ':curso' => $id_curso_fk,
            ':seccion' => $id_seccion_fk,
            ':estado' => $estado,
            ':id' => $id_inscripcion
        ]);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'mensaje' => 'Inscripción modificada correctamente.'
        ]);

    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'mensaje' => $e->getMessage()
        ]);
    }

} else {
    exit("Acceso no permitido.");
}