<?php
require_once "../modelos/mainModel.php";

$conexion = mainModel::conectar();
$id_curso = $_POST['id_curso'] ?? '';

if ($id_curso === '') {
  echo "<option value=''>Seleccione una sección</option>";
  exit;
}

// PRIMERO: Obtener el tipo de curso
$consultaTipo = $conexion->prepare("
    SELECT tipo_curso 
    FROM curso 
    WHERE id_curso = :id_curso
");
$consultaTipo->execute([':id_curso' => $id_curso]);
$tipoCurso = $consultaTipo->fetchColumn();

if ($tipoCurso === 'Intensivo') {
    // CONSULTA PARA CURSOS INTENSIVOS
    $consulta = $conexion->prepare("
        SELECT 
            s.id_seccion, 
            s.nombre_seccion,
            c.nombre_curso,
            pi.fecha_inicio_int,
            pi.fecha_fin_int,
            (
                SELECT COUNT(*) 
                FROM inscripcion i 
                WHERE i.id_seccion_fk = s.id_seccion 
                    AND i.estado_inscripcion = 'Activa'
            ) AS inscritos
        FROM seccion s
        JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk
        JOIN curso c ON cv.id_curso_fk = c.id_curso
        WHERE cv.id_curso_fk = :id_curso
            AND cv.estado_curso_vigente = 1
            AND pi.estado_periodo = 'Vigente'
    ");
} else {
    // CONSULTA PARA CURSOS NO INTENSIVOS (Extendido y Técnico Profesional)
    $consulta = $conexion->prepare("
        SELECT 
            s.id_seccion, 
            s.nombre_seccion,
            co.numero_cohorte, 
            co.anio_cohorte, 
            c.nombre_curso,
            (
                SELECT COUNT(*) 
                FROM inscripcion i 
                WHERE i.id_seccion_fk = s.id_seccion 
                    AND i.estado_inscripcion = 'Activa'
            ) AS inscritos
        FROM seccion s
        JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
        JOIN curso c ON cv.id_curso_fk = c.id_curso
        WHERE cv.id_curso_fk = :id_curso
            AND cv.estado_curso_vigente = 1
            AND co.estado_cohorte = 'Vigente'
    ");
}

$consulta->execute([':id_curso' => $id_curso]);
$secciones = $consulta->fetchAll();

if (empty($secciones)) {
    if ($tipoCurso === 'Intensivo') {
        echo "<option value=''>No hay secciones disponibles para este curso intensivo</option>";
    } else {
        echo "<option value=''>No hay secciones disponibles para este curso</option>";
    }
} else {
    foreach ($secciones as $s) {
        if ($s['inscritos'] < 20) {
            if ($tipoCurso === 'Intensivo') {
                // Formato para cursos intensivos
                $fechaInicio = date('d/m/Y', strtotime($s['fecha_inicio_int']));
                $fechaFin = date('d/m/Y', strtotime($s['fecha_fin_int']));
                echo "<option value='{$s['id_seccion']}'>
                        Sección {$s['nombre_seccion']} - Intensivo ({$fechaInicio} al {$fechaFin}) - {$s['inscritos']}/20 disponibles
                    </option>";
            } else {
                // Formato para cursos no intensivos
                echo "<option value='{$s['id_seccion']}'>
                        Sección {$s['nombre_seccion']} - Cohorte {$s['numero_cohorte']}/{$s['anio_cohorte']} - {$s['inscritos']}/20 disponibles
                    </option>";
            }
        }
    }
    
    // Si no hay secciones con cupo disponible, mostrar mensaje
    $hayCupo = false;
    foreach ($secciones as $s) {
        if ($s['inscritos'] < 20) {
            $hayCupo = true;
            break;
        }
    }
    
    if (!$hayCupo) {
        echo "<option value=''>Todas las secciones están llenas (20/20)</option>";
    }
}
?>