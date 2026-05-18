<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(['name'=>'SPM']);

require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

$conexion = mainModel::conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 🔹 Obtener y limpiar datos
        $id_estudiante_fk = mainModel::limpiar_cadena($_POST['id_estudiante_fk'] ?? '');
        $id_seccion      = mainModel::limpiar_cadena($_POST['id_seccion_fk'] ?? '');
        $fecha_inscripcion = mainModel::limpiar_cadena($_POST['fecha_inscripcion'] ?? '');
        $fecha_abandono  = mainModel::limpiar_cadena($_POST['fecha_abono'] ?? '');
        $estado          = mainModel::limpiar_cadena($_POST['estado_inscripcion'] ?? '');
        $metodo_pago     = mainModel::limpiar_cadena($_POST['metodo_pago'] ?? '');
        $monto           = mainModel::limpiar_cadena($_POST['monto'] ?? '');
        $fecha_pago      = mainModel::limpiar_cadena($_POST['fecha_pago'] ?? '');
        $archivo         = $_FILES['comprobante'];

        // 🔒 Validar campos obligatorios
        if (
            empty($id_estudiante_fk) || empty($id_seccion) || empty($fecha_inscripcion) ||
            empty($estado) || empty($metodo_pago) || empty($monto) || empty($fecha_pago) || empty($archivo['name'])
        ) {
            throw new Exception("Faltan campos obligatorios.");
        }

        if ($monto <= 0) {
            throw new Exception("El monto pagado debe ser mayor a cero.");
        }

        // 🔍 Validar que el estudiante exista y esté activo
        $consultaEstudiante = $conexion->prepare("
            SELECT estado_estudiante FROM estudiante WHERE id_estudiante = :id AND estado_estudiante = 1
        ");
        $consultaEstudiante->execute([':id' => $id_estudiante_fk]);

        if ($consultaEstudiante->rowCount() !== 1) {
            throw new Exception("El estudiante no está activo o no existe.");
        }

        // 🔒 Validar tipo y tamaño del archivo
        $permitidos = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (!in_array(mime_content_type($archivo['tmp_name']), $permitidos)) {
            throw new Exception("Formato de imagen no permitido. Solo JPG, PNG o WEBP.");
        }

        if ($archivo['size'] > 5 * 1024 * 1024) {
            throw new Exception("El comprobante no puede exceder 5MB.");
        }

        $nombreArchivo = uniqid("comprobante_") . "." . pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $rutaDestino = "../comprobantes/" . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            throw new Exception("No se pudo guardar el comprobante.");
        }

        // 🔍 Verificar inscripción duplicada
        $consultaDuplicado = $conexion->prepare("
            SELECT COUNT(*) FROM inscripcion 
            WHERE id_estudiante_fk = :estudiante 
              AND id_seccion_fk = :seccion 
              AND estado_inscripcion IN ('Activa', 'Abandonada')
              AND archivada = 1
        ");
        $consultaDuplicado->execute([
            ':estudiante' => $id_estudiante_fk,
            ':seccion' => $id_seccion
        ]);
        if ($consultaDuplicado->fetchColumn() > 0) {
            throw new Exception("El estudiante ya está inscrito en esta sección.");
        }

        // 🔍 Verificar si la sección tiene cupo disponible
        $consultaCupoSeccion = $conexion->prepare("
            SELECT COUNT(*) FROM inscripcion 
            WHERE id_seccion_fk = :seccion AND archivada = 1
        ");
        $consultaCupoSeccion->execute([':seccion' => $id_seccion]);
        $totalInscritos = $consultaCupoSeccion->fetchColumn();

        if ($totalInscritos >= 20) {
            throw new Exception("La sección ya tiene 20 estudiantes inscritos. No se puede registrar más.");
        }

        // 🔍 OBTENER INFORMACIÓN DEL CURSO VIGENTE (CONSULTA ÚNICA)
        $consultaCurso = $conexion->prepare("
            SELECT 
                cv.id_curso_vigente, 
                cv.id_cohorte_fk,
                c.tipo_curso
            FROM seccion s 
            JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
            JOIN curso c ON cv.id_curso_fk = c.id_curso
            WHERE s.id_seccion = :id
              AND s.archivar_seccion = 1
              AND cv.estado_curso_vigente = 1
              AND c.estado_curso = 1
        ");
        $consultaCurso->execute([':id' => $id_seccion]);

        if ($consultaCurso->rowCount() !== 1) {
            throw new Exception("No se encontró el curso vigente asociado a la sección o no está activo.");
        }

        $datosSeccion = $consultaCurso->fetch();
        $id_curso = $datosSeccion['id_curso_vigente'];
        $id_cohorte = $datosSeccion['id_cohorte_fk'];
        $tipo_curso = $datosSeccion['tipo_curso'];

        // 🔍 OBTENER MONTO PERMITIDO SEGÚN TIPO DE CURSO
        if ($tipo_curso == 'Intensivo') {
            // PARA CURSOS INTENSIVOS: Obtener precio de periodo_intensivo
            $consultaMonto = $conexion->prepare("
                SELECT pago_inscripcion_int 
                FROM periodo_intensivo 
                WHERE id_curso_vigente_fk = :id_curso_vigente 
                AND estado_periodo = 'Vigente'
            ");
            $consultaMonto->execute([':id_curso_vigente' => $id_curso]);
            $montoPermitido = $consultaMonto->fetchColumn();
            
            if (!$montoPermitido) {
                throw new Exception("No se encontró un período intensivo vigente para este curso.");
            }
        } else {
            // PARA CURSOS NO INTENSIVOS: Obtener precio de cohorte
            $consultaCohorte = $conexion->prepare("
                SELECT estado_cohorte, pago_inscripcion 
                FROM cohorte 
                WHERE id_cohorte = :id AND archivar_cohorte = 1
            ");
            $consultaCohorte->execute([':id' => $id_cohorte]);
            
            $datosCohorte = $consultaCohorte->fetch();
            
            if (!$datosCohorte) {
                throw new Exception("No se pudo obtener la información de la cohorte.");
            }
            
            $estadoCohorte = $datosCohorte['estado_cohorte'];
            $montoPermitido = $datosCohorte['pago_inscripcion'];
            
            if ($estadoCohorte !== 'Vigente') {
                throw new Exception("La cohorte asociada está cerrada y no permite inscripciones.");
            }

            if (!$montoPermitido) {
                throw new Exception("No se pudo obtener el monto de inscripción desde la cohorte.");
            }
        }

        // 🔒 Validar que el monto ingresado no exceda el permitido
        if ($monto > $montoPermitido) {
            throw new Exception("El monto ingresado ($monto USD) excede el precio de inscripción permitido ($montoPermitido USD).");
        }

        // 🧮 Calcular pago faltante
        $pagoFaltante = max(0, $montoPermitido - $monto);

        // 🧾 Registrar inscripción + transacción
        $conexion->beginTransaction();

        $insertInscripcion = $conexion->prepare("
            INSERT INTO inscripcion (
                id_curso_vigente_fk,
                id_estudiante_fk,
                id_seccion_fk,
                fecha_inscripcion,
                if_fecha_abandono,
                estado_inscripcion,
                archivada,
                pago_faltante
            ) VALUES (
                :curso,
                :estudiante,
                :seccion,
                :fecha_inscripcion,
                :fecha_abandono,
                :estado,
                1,
                :pago_faltante
            )
        ");
        $insertInscripcion->execute([
            ':curso' => $id_curso,
            ':estudiante' => $id_estudiante_fk,
            ':seccion' => $id_seccion,
            ':fecha_inscripcion' => $fecha_inscripcion,
            ':fecha_abandono' => $fecha_abandono ?: null,
            ':estado' => $estado,
            ':pago_faltante' => $pagoFaltante
        ]);

        $idInscripcion = $conexion->lastInsertId();

        $insertTransaccion = $conexion->prepare("
            INSERT INTO transaccion (
                id_inscripcion,
                monto,
                estatus_pago,
                motivo_pago,
                fecha_pago,
                metodo_pago,
                if_pagomovil_imagen,
                pago_faltante
            ) VALUES (
                :inscripcion,
                :monto,
                1,
                'Inscripción',
                :fecha_pago,
                :metodo,
                :imagen,
                :pago_faltante
            )
        ");
        $insertTransaccion->execute([
            ':inscripcion' => $idInscripcion,
            ':monto' => $monto,
            ':fecha_pago' => $fecha_pago,
            ':metodo' => $metodo_pago,
            ':imagen' => $nombreArchivo,
            ':pago_faltante' => $pagoFaltante
        ]);

        $conexion->commit();

        echo json_encode([
            'status' => 'success',
            'mensaje' => 'Inscripción registrada exitosamente.'
        ]);
        exit;

    } catch (Exception $e) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        // Eliminar archivo si se subió pero falló la transacción
        if (isset($rutaDestino) && file_exists($rutaDestino)) {
            unlink($rutaDestino);
        }
        echo json_encode([
            'status' => 'error',
            'mensaje' => $e->getMessage()
        ]);
        exit;
    }

} else {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Acceso no permitido.'
    ]);
    exit;
}
?>