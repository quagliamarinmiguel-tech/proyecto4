<?php
// ajax/obtenerInfoCurso.php
require_once __DIR__ . "/../../config/APP.php";
require_once __DIR__ . "/../../modelos/mainModel.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_curso'])) {
    $id_curso = intval($_POST['id_curso']);
    
    try {
        $conexion = mainModel::conectar();
        
        // Consulta CORREGIDA para tu estructura de BD
        $consulta = "SELECT 
                    c.id_curso,
                    c.nombre_curso,
                    c.tipo_curso,
                    c.cupos,
                    ch.pago_inscripcion as monto_inscripcion,
                    (SELECT COUNT(*) 
                     FROM inscripcion i 
                     INNER JOIN curso_vigente cv ON i.id_curso_vigente_fk = cv.id_curso_vigente 
                     WHERE cv.id_curso_fk = c.id_curso 
                     AND i.archivada = 0) as inscritos_actuales,
                    (c.cupos - (SELECT COUNT(*) 
                               FROM inscripcion i 
                               INNER JOIN curso_vigente cv ON i.id_curso_vigente_fk = cv.id_curso_vigente 
                               WHERE cv.id_curso_fk = c.id_curso 
                               AND i.archivada = 0)) as cupos_disponibles
                FROM curso c
                LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
                LEFT JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
                WHERE c.id_curso = :id_curso
                AND cv.estado_curso_vigente = 1
                AND ch.estado_cohorte = 'Vigente'
                GROUP BY c.id_curso, c.nombre_curso, c.tipo_curso, c.cupos, ch.pago_inscripcion
                LIMIT 1";
        
        $stmt = $conexion->prepare($consulta);
        $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
        $stmt->execute();
        
        $curso = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($curso) {
            echo json_encode([
                'success' => true,
                'curso' => [
                    'id_curso' => $curso['id_curso'],
                    'nombre_curso' => $curso['nombre_curso'],
                    'tipo_curso' => $curso['tipo_curso'],
                    'monto_inscripcion' => $curso['monto_inscripcion'] ?? 0,
                    'cupos' => $curso['cupos'],
                    'inscritos_actuales' => $curso['inscritos_actuales'] ?? 0,
                    'cupos_disponibles' => $curso['cupos_disponibles'] ?? 0
                ]
            ]);
        } else {
            // Si no hay curso vigente, obtener info básica del curso
            $consulta_basica = "SELECT 
                                id_curso, 
                                nombre_curso, 
                                tipo_curso, 
                                cupos,
                                0 as monto_inscripcion,
                                0 as inscritos_actuales,
                                cupos as cupos_disponibles
                            FROM curso 
                            WHERE id_curso = :id_curso";
            
            $stmt_basica = $conexion->prepare($consulta_basica);
            $stmt_basica->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
            $stmt_basica->execute();
            
            $curso_basico = $stmt_basica->fetch(PDO::FETCH_ASSOC);
            
            if ($curso_basico) {
                echo json_encode([
                    'success' => true,
                    'curso' => $curso_basico,
                    'warning' => 'No hay cohorte vigente para este curso'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Curso no encontrado'
                ]);
            }
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error en la base de datos: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Solicitud inválida'
    ]);
}
?>