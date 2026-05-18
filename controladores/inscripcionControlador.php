<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(['name'=>'SPM']);

if (!isset($_SESSION['id_spm']) || $_SESSION['cargo_spm'] != 5) {
    exit(json_encode([
        'status' => 'error',
        'mensaje' => 'No se ha iniciado sesión como estudiante.'
    ]));
}

require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

$conexion = mainModel::conectar();

// Obtener el ID real del estudiante desde la tabla usuario_estudiante
$id_usuario = $_SESSION['id_spm'];
$consultaEst = $conexion->prepare("
    SELECT id_estudiante_fk 
    FROM usuario_estudiante 
    WHERE id_usuario_fk = :id
");
$consultaEst->execute([':id' => $id_usuario]);

if ($consultaEst->rowCount() !== 1) {
    exit(json_encode([
        'status' => 'error',
        'mensaje' => 'No se encontró el estudiante asociado a este usuario.'
    ]));
}

$id_estudiante = $consultaEst->fetch()['id_estudiante_fk'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 🔹 Obtener y limpiar datos
        $id_curso = mainModel::limpiar_cadena($_POST['id_curso'] ?? '');
        $id_seccion = mainModel::limpiar_cadena($_POST['id_seccion_fk'] ?? '');
        $fecha_inscripcion = mainModel::limpiar_cadena($_POST['fecha_inscripcion'] ?? '');
        $fecha_abandono = mainModel::limpiar_cadena($_POST['fecha_abono'] ?? '');
        $estado = mainModel::limpiar_cadena($_POST['estado_inscripcion'] ?? '');
        $metodo_pago = mainModel::limpiar_cadena($_POST['metodo_pago'] ?? '');
        $monto = mainModel::limpiar_cadena($_POST['monto'] ?? '');
        $fecha_pago = mainModel::limpiar_cadena($_POST['fecha_pago'] ?? '');
        $archivo = $_FILES['comprobante'];

        // 🔍 Validar que el estudiante exista y esté activo
        $consultaEstudiante = $conexion->prepare("
            SELECT estado_estudiante FROM estudiante WHERE id_estudiante = :id AND estado_estudiante = 1
        ");
        $consultaEstudiante->execute([':id' => $id_estudiante]);

        if ($consultaEstudiante->rowCount() !== 1) {
            throw new Exception("El estudiante no está activo o no existe.");
        }

        // 🔒 Validar campos obligatorios
        if (
            empty($id_curso) || empty($id_seccion) || empty($fecha_inscripcion) ||
            empty($estado) || empty($metodo_pago) || empty($monto) || empty($fecha_pago) || empty($archivo['name'])
        ) {
            throw new Exception("Faltan campos obligatorios.");
        }

        if ($monto <= 0) {
            throw new Exception("El monto pagado debe ser mayor a cero.");
        }

        if (!empty($fecha_abandono) && strtotime($fecha_abandono) < strtotime($fecha_inscripcion)) {
            throw new Exception("La fecha de abandono no puede ser anterior a la fecha de inscripción.");
        }

        // 🔒 Validar tipo y tamaño del archivo
        $permitidos = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        if (!in_array(mime_content_type($archivo['tmp_name']), $permitidos)) {
            throw new Exception("Formato de imagen no permitido. Solo JPG, PNG, GIF o WEBP.");
        }

        if ($archivo['size'] > 5 * 1024 * 1024) {
            throw new Exception("El comprobante no puede exceder 5MB.");
        }

        $nombreArchivo = uniqid("comprobante_") . "." . pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $rutaDestino = "../comprobantes/" . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            throw new Exception("No se pudo guardar el comprobante.");
        }

        // 🔍 OBTENER INFORMACIÓN DEL CURSO (INTENSIVO O NO)
        $consultaCursoInfo = $conexion->prepare("
            SELECT 
                c.tipo_curso,
                co.pago_inscripcion as pago_inscripcion_cohorte,
                pi.pago_inscripcion_int,
                cv.id_curso_vigente,
                cv.id_cohorte_fk,
                pi.id_periodo_intensivo
            FROM curso c
            INNER JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
            LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
            LEFT JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk
            WHERE c.id_curso = :id_curso
            AND c.estado_curso = 1
            AND cv.estado_curso_vigente = 1
        ");
        $consultaCursoInfo->execute([':id_curso' => $id_curso]);
        $cursoInfo = $consultaCursoInfo->fetch(PDO::FETCH_ASSOC);

        if (!$cursoInfo) {
            throw new Exception("No se pudo obtener la información del curso seleccionado.");
        }

        $tipo_curso = $cursoInfo['tipo_curso'];
        $id_curso_vigente = $cursoInfo['id_curso_vigente'];
        $id_cohorte = $cursoInfo['id_cohorte_fk'];
        $id_periodo_intensivo = $cursoInfo['id_periodo_intensivo'];

        // 🎯 DETERMINAR MONTO REQUERIDO SEGÚN TIPO DE CURSO
        $montoRequerido = 0;
        
        if ($tipo_curso === 'Intensivo') {
            $montoRequerido = $cursoInfo['pago_inscripcion_int'] ?? 0;
            
            // Validar que el período intensivo esté vigente
            $consultaPeriodo = $conexion->prepare("
                SELECT estado_periodo FROM periodo_intensivo 
                WHERE id_periodo_intensivo = :id AND estado_periodo = 'Vigente'
            ");
            $consultaPeriodo->execute([':id' => $id_periodo_intensivo]);
            
            if ($consultaPeriodo->rowCount() !== 1) {
                throw new Exception("El período intensivo no está vigente.");
            }
        } else {
            $montoRequerido = $cursoInfo['pago_inscripcion_cohorte'] ?? 0;
            
            // Validar que la cohorte esté vigente
            $consultaCohorte = $conexion->prepare("
                SELECT estado_cohorte FROM cohorte 
                WHERE id_cohorte = :id AND estado_cohorte = 'Vigente'
            ");
            $consultaCohorte->execute([':id' => $id_cohorte]);
            
            if ($consultaCohorte->rowCount() !== 1) {
                throw new Exception("La cohorte no está vigente.");
            }
        }

        if ($montoRequerido <= 0) {
            throw new Exception("No se pudo obtener el monto de inscripción para el curso seleccionado.");
        }

        // 🔒 Validar que el monto ingresado no exceda el permitido
        if ($monto > $montoRequerido) {
            throw new Exception("El monto ingresado ($monto USD) excede el precio de inscripción permitido ($montoRequerido USD).");
        }

        // 🔍 Verificar que la sección esté activa y pertenezca al curso
        $consultaSeccion = $conexion->prepare("
            SELECT s.id_seccion 
            FROM seccion s
            INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
            WHERE s.id_seccion = :id_seccion
            AND s.archivar_seccion = 1
            AND cv.id_curso_vigente = :id_curso_vigente
        ");
        $consultaSeccion->execute([
            ':id_seccion' => $id_seccion,
            ':id_curso_vigente' => $id_curso_vigente
        ]);

        if ($consultaSeccion->rowCount() !== 1) {
            throw new Exception("La sección seleccionada no es válida o no pertenece al curso.");
        }

        // 🔍 Verificar inscripción duplicada (solo en registros no archivados)
        $consultaDuplicado = $conexion->prepare("
            SELECT COUNT(*) FROM inscripcion 
            WHERE id_estudiante_fk = :estudiante 
              AND id_seccion_fk = :seccion 
              AND estado_inscripcion IN ('Activa', 'Abandonada')
              AND archivada = 1
        ");
        $consultaDuplicado->execute([
            ':estudiante' => $id_estudiante,
            ':seccion' => $id_seccion
        ]);
        if ($consultaDuplicado->fetchColumn() > 0) {
            throw new Exception("Ya estás inscrito en esta sección.");
        }

        // 🔍 Verificar si la sección tiene cupo disponible (solo registros no archivados)
        $consultaCupoSeccion = $conexion->prepare("
            SELECT COUNT(*) FROM inscripcion 
            WHERE id_seccion_fk = :seccion AND archivada = 1
        ");
        $consultaCupoSeccion->execute([':seccion' => $id_seccion]);
        $totalInscritos = $consultaCupoSeccion->fetchColumn();

        if ($totalInscritos >= 20) {
            throw new Exception("La sección ya tiene 20 estudiantes inscritos. No se puede registrar más.");
        }

        // 🔍 Verificar que el curso tenga cupos disponibles
        $consultaEstadoCupo = $conexion->prepare("
            SELECT c.cupos 
            FROM curso_vigente cv
            JOIN curso c ON cv.id_curso_fk = c.id_curso
            WHERE cv.id_curso_vigente = :id
              AND c.estado_curso = 1
              AND cv.estado_curso_vigente = 1
        ");
        $consultaEstadoCupo->execute([':id' => $id_curso_vigente]);

        $cupo = $consultaEstadoCupo->fetchColumn();
        if ($cupo === false) {
            throw new Exception("No se pudo verificar el estado del curso.");
        }

        // Si el curso tiene cupos definidos (diferente de 0), verificar disponibilidad
        if ($cupo != 0) {
            $consultaInscritosCurso = $conexion->prepare("
                SELECT COUNT(*) FROM inscripcion i
                WHERE i.id_curso_vigente_fk = :id_curso 
                  AND i.archivada = 1
            ");
            $consultaInscritosCurso->execute([':id_curso' => $id_curso_vigente]);
            $inscritosCurso = $consultaInscritosCurso->fetchColumn();
            
            if ($inscritosCurso >= $cupo) {
                throw new Exception("El curso ha alcanzado su límite de cupos.");
            }
        }

        // 🧮 Calcular pago faltante
        $pagoFaltante = max(0, $montoRequerido - $monto);

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
            ':curso' => $id_curso_vigente,
            ':estudiante' => $id_estudiante,
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
    exit(json_encode([
        'status' => 'error',
        'mensaje' => 'Acceso no permitido.'
    ]));
}
?>