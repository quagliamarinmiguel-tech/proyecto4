<?php
// ListaSeccionesGraduadosControlador.php - VERSIÓN MUY SIMPLE
    
require_once __DIR__ . '/../modelos/mainModel.php';

class ListaSeccionesGraduadosControlador extends mainModel {
    
    public function __construct() {
        // Constructor vacío
    }

public function obtener_secciones_graduados($pagina = 1, $registros = 10, $busqueda = '', $filtro_estado = 'all') {
    try {
        $offset = ($pagina > 0) ? (($pagina - 1) * $registros) : 0;
        
        // CONSULTA CON FILTRO POR TIPO DE CURSO
        $consulta = "SELECT 
                    s.id_seccion,
                    s.nombre_seccion,
                    c.nombre_curso,
                    c.tipo_curso,
                    CONCAT(p.nombre_profesor, ' ', p.apellido_profesor) as nombre_profesor,
                    COUNT(DISTINCT i.id_inscripcion) as total_estudiantes,
                    
                    -- PARA DEPURACIÓN: Verificar si hay notas finales
                    COUNT(DISTINCT CASE WHEN i.nota_final IS NOT NULL THEN i.id_inscripcion END) as estudiantes_con_nota,
                    AVG(i.nota_final) as nota_promedio,
                    MIN(i.nota_final) as nota_minima,
                    MAX(i.nota_final) as nota_maxima,
                    
                    COUNT(DISTINCT e.id_evaluacion) as total_evaluaciones,
                    -- SUMA CORRECTA DE PORCENTAJES
                    COALESCE((
                        SELECT SUM(e.porcentaje) 
                        FROM evaluacion e 
                        WHERE e.id_seccion = s.id_seccion 
                        AND e.archivar_evaluacion = 1
                    ), 0) as porcentaje_completitud,
                    
                    -- Contar estudiantes por categoría
                    COUNT(DISTINCT CASE WHEN i.nota_final >= 10 THEN i.id_inscripcion END) as aprobados,
                    COUNT(DISTINCT CASE WHEN i.nota_final < 10 THEN i.id_inscripcion END) as reprobados,
                    
                    -- Para depuración adicional
                    GROUP_CONCAT(DISTINCT i.nota_final) as todas_las_notas
                    
                FROM seccion s
                INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
                INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
                INNER JOIN profesor p ON s.ci_profesor = p.id_profesor
                LEFT JOIN inscripcion i ON s.id_seccion = i.id_seccion_fk 
                    AND i.archivada = 1
                LEFT JOIN evaluacion e ON s.id_seccion = e.id_seccion 
                    AND e.archivar_evaluacion = 1
                LEFT JOIN puntaje_estudiante pe ON i.id_inscripcion = pe.id_inscripcion 
                    AND pe.archivar_puntaje = 1
                WHERE s.archivar_seccion = 1
                AND cv.estado_curso_vigente = 1 
                AND c.estado_curso = 1
                AND c.tipo_curso IN ('Extendido', 'Tecnico Profesional', 'Intensivo')
                GROUP BY s.id_seccion, s.nombre_seccion, c.nombre_curso, c.tipo_curso, p.nombre_profesor, p.apellido_profesor
                HAVING total_estudiantes > 0
                ORDER BY porcentaje_completitud DESC
                LIMIT $offset, $registros";
        
        $resultado = $this->ejecutar_consulta_simple($consulta);
        $secciones = $resultado->fetchAll(PDO::FETCH_ASSOC);
        
        // Consulta para el total (con el mismo filtro)
        $consulta_total = "SELECT COUNT(*) as total FROM (
            SELECT s.id_seccion
            FROM seccion s
            INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
            INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
            INNER JOIN profesor p ON s.ci_profesor = p.id_profesor
            LEFT JOIN inscripcion i ON s.id_seccion = i.id_seccion_fk 
                AND i.archivada = 1
            WHERE s.archivar_seccion = 1
            AND cv.estado_curso_vigente = 1 
            AND c.estado_curso = 1
            AND c.tipo_curso IN ('Extendido', 'Tecnico Profesional', 'Intensivo') 
            GROUP BY s.id_seccion
            HAVING COUNT(DISTINCT i.id_inscripcion) > 0
        ) as subquery";
        
        $total_result = $this->ejecutar_consulta_simple($consulta_total);
        $total_registros = $total_result->fetch(PDO::FETCH_ASSOC)['total'];
        $total_paginas = ceil($total_registros / $registros);
        
        return [
            "datos" => $secciones,
            "total" => $total_registros,
            "paginas" => $total_paginas,
            "pagina_actual" => $pagina
        ];
        
    } catch (Exception $e) {
        throw new Exception("Error en BD: " . $e->getMessage());
    }
}


    
   public function obtener_estudiantes_seccion_controlador($id_seccion) {
    try {
        // Validar el ID de sección
        if (!is_numeric($id_seccion) || $id_seccion <= 0) {
            throw new Exception("ID de sección inválido: " . $id_seccion);
        }

        // Consulta SIMPLE para obtener estudiantes APROBADOS
        $consulta_aprobados = "SELECT 
                    est.ci_estudiante,
                    est.nombre,
                    est.apellido,
                    i.nota_final,
                    i.codigo_graduado,
                    i.id_inscripcion
                FROM inscripcion i
                INNER JOIN estudiante est ON i.id_estudiante_fk = est.id_estudiante
                WHERE i.id_seccion_fk = $id_seccion
                AND i.archivada = 1
                AND i.nota_final >= 10
                ORDER BY est.apellido, est.nombre";

        $resultado_aprobados = $this->ejecutar_consulta_simple($consulta_aprobados);
        $estudiantes_aprobados = $resultado_aprobados->fetchAll(PDO::FETCH_ASSOC);

        // Consulta SIMPLE para obtener estudiantes REPROBADOS
        $consulta_reprobados = "SELECT 
                    est.ci_estudiante,
                    est.nombre,
                    est.apellido,
                    i.nota_final,
                    i.codigo_graduado,
                    i.id_inscripcion
                FROM inscripcion i
                INNER JOIN estudiante est ON i.id_estudiante_fk = est.id_estudiante
                WHERE i.id_seccion_fk = $id_seccion
                AND i.archivada = 1
                AND i.nota_final < 10
                ORDER BY est.apellido, est.nombre";

        $resultado_reprobados = $this->ejecutar_consulta_simple($consulta_reprobados);
        $estudiantes_reprobados = $resultado_reprobados->fetchAll(PDO::FETCH_ASSOC);

        // Generar tabla HTML para estudiantes APROBADOS
        $tabla_aprobados = $this->generarTablaEstudiantes($estudiantes_aprobados, 'aprobados');
        
        // Generar tabla HTML para estudiantes REPROBADOS
        $tabla_reprobados = $this->generarTablaEstudiantes($estudiantes_reprobados, 'reprobados');

        // Botones para cargar códigos (solo para aprobados)
        $boton_codigos = '';
        if (!empty($estudiantes_aprobados)) {
            $boton_codigos = '
                <div class="text-right mb-3" style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button class="btn btn-success btn-cargar-codigos" 
                        data-seccion="' . $id_seccion . '"
                        data-cantidad="' . count($estudiantes_aprobados) . '">
                        <i class="fas fa-barcode"></i> Generar Códigos
                    </button>
                    <button class="btn btn-danger btn-borrar-codigos" 
                            data-seccion="' . $id_seccion . '"
                            data-nombre="' . htmlspecialchars($id_seccion) . '">
                        <i class="fas fa-trash"></i> Borrar Códigos
                    </button>
                </div>
            ';
        }

        return [
            'success' => true,
            'count_aprobados' => count($estudiantes_aprobados),
            'count_reprobados' => count($estudiantes_reprobados),
            'tabla_aprobados' => $tabla_aprobados,
            'tabla_reprobados' => $tabla_reprobados,
            'boton_codigos' => $boton_codigos
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => 'Error al cargar los estudiantes: ' . $e->getMessage(),
            'count_aprobados' => 0,
            'count_reprobados' => 0,
            'tabla_aprobados' => '<div class="alert alert-danger">Error al cargar los datos</div>',
            'tabla_reprobados' => ''
        ];
    }
}

// FUNCIÓN AUXILIAR PARA GENERAR TABLAS DE ESTUDIANTES
private function generarTablaEstudiantes($estudiantes, $tipo) {
    if (empty($estudiantes)) {
        $mensaje = $tipo == 'aprobados' 
            ? 'No hay estudiantes aprobados en esta sección' 
            : 'No hay estudiantes reprobados en esta sección';
        
        return '<div class="alert alert-info text-center">' . $mensaje . '</div>';
    }

    $tabla = '<table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Cédula</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Nota Final</th>';
    
    // Solo mostrar columna de código para aprobados
    if ($tipo == 'aprobados') {
        $tabla .= '<th>Código Graduado</th>';
        $tabla .= '<th>Acciones</th>';
    }
    
    $tabla .= '</tr>
                </thead>
                <tbody>';
    
    foreach ($estudiantes as $estudiante) {
        $cedula = htmlspecialchars($estudiante['ci_estudiante']);
        $nombre = htmlspecialchars($estudiante['nombre']);
        $apellido = htmlspecialchars($estudiante['apellido']);
        $nota_final = htmlspecialchars($estudiante['nota_final']);
        $codigo_graduado = htmlspecialchars($estudiante['codigo_graduado'] ?? '');
        $id_inscripcion = htmlspecialchars($estudiante['id_inscripcion']);
        $nombre_completo = htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']);
        
        $tabla .= "
            <tr>
                <td>{$cedula}</td>
                <td>{$nombre}</td>
                <td>{$apellido}</td>
                <td><strong>{$nota_final}</strong></td>";
        
        // Solo mostrar código y acciones para aprobados
        if ($tipo == 'aprobados') {
            $codigo_display = $codigo_graduado ? $codigo_graduado : '<span class="text-muted">Sin código</span>';
            $tabla .= "<td>{$codigo_display}</td>";
            $tabla .= "
                <td>
                    <button class=\"btn btn-sm btn-primary btn-asignar-codigo\" 
                            data-inscripcion=\"{$id_inscripcion}\"
                            data-nombre=\"{$nombre_completo}\"
                            data-codigo=\"{$codigo_graduado}\">
                        <i class=\"fas fa-edit\"></i> " . ($codigo_graduado ? 'Editar' : 'Asignar') . "
                    </button>
                </td>
            ";
        }
        
        $tabla .= "</tr>";
    }
    
    $tabla .= '</tbody></table>';
    
    return $tabla;
}
    
    
public function generarCodigosGraduadosControlador($id_seccion, $numero_base) {
    try {
        // Validar parámetros
        if (!is_numeric($id_seccion) || $id_seccion <= 0) {
            throw new Exception("ID de sección inválido");
        }
        
        if (!is_numeric($numero_base) || $numero_base < 0 || $numero_base > 9999) {
            throw new Exception("Número base inválido. Debe estar entre 0 y 9999");
        }

        // Obtener estudiantes aprobados de la sección
        $consulta_estudiantes = "SELECT 
                                i.id_inscripcion,
                                est.nombre,
                                est.apellido
                            FROM inscripcion i
                            INNER JOIN estudiante est ON i.id_estudiante_fk = est.id_estudiante
                            WHERE i.id_seccion_fk = $id_seccion
                            AND i.archivada = 1
                            AND i.nota_final >= 10
                            ORDER BY i.id_inscripcion";

        $resultado = $this->ejecutar_consulta_simple($consulta_estudiantes);
        $estudiantes = $resultado->fetchAll(PDO::FETCH_ASSOC);

        if (empty($estudiantes)) {
            return [
                'success' => false,
                'message' => 'No hay estudiantes aprobados en esta sección'
            ];
        }

        // Generar y asignar códigos
        $codigos_asignados = [];
        $numero_actual = $numero_base;

        foreach ($estudiantes as $estudiante) {
            // Formatear el número con ceros a la izquierda (4 dígitos)
            $numero_formateado = str_pad($numero_actual, 4, '0', STR_PAD_LEFT);
            $codigo_graduado = "CPS-{$numero_formateado}-SNE-G";

            // Actualizar en la base de datos
            $consulta_update = "UPDATE inscripcion 
                               SET codigo_graduado = '$codigo_graduado' 
                               WHERE id_inscripcion = {$estudiante['id_inscripcion']}";
            
            $this->ejecutar_consulta_simple($consulta_update);

            $codigos_asignados[] = [
                'estudiante' => $estudiante['nombre_estudiante'] . ' ' . $estudiante['apellido_estudiante'],
                'codigo' => $codigo_graduado
            ];

            $numero_actual++;
        }

        return [
            'success' => true,
            'message' => 'Códigos de graduados asignados correctamente',
            'codigos_asignados' => $codigos_asignados,
            'total_asignados' => count($codigos_asignados)
        ];

    } catch (Exception $e) {
        error_log("Error en generarCodigosGraduadosControlador: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al generar códigos: ' . $e->getMessage()
        ];
    }
}
    
    public function guardar_codigo_graduado_controlador($id_inscripcion, $codigo_graduado) {
    try {
        // Validar parámetros
        if (!is_numeric($id_inscripcion) || $id_inscripcion <= 0) {
            throw new Exception("ID de inscripción inválido");
        }
        
        if (empty($codigo_graduado)) {
            throw new Exception("El código de graduado no puede estar vacío");
        }

        // Validar formato del código (opcional)
        if (!preg_match('/^CPS-\d{4}-SNE-G$/', $codigo_graduado)) {
            throw new Exception("Formato de código inválido. Debe ser: CPS-XXXX-SNE-G");
        }

        // Verificar que la inscripción existe
        $consulta_verificar = "SELECT id_inscripcion FROM inscripcion WHERE id_inscripcion = $id_inscripcion";
        $resultado_verificar = $this->ejecutar_consulta_simple($consulta_verificar);
        
        if ($resultado_verificar->rowCount() == 0) {
            throw new Exception("La inscripción no existe");
        }

        // Actualizar el código de graduado
        $consulta_update = "UPDATE inscripcion 
                           SET codigo_graduado = '" . $this->limpiar_cadena($codigo_graduado) . "' 
                           WHERE id_inscripcion = $id_inscripcion";
        
        $resultado_update = $this->ejecutar_consulta_simple($consulta_update);
        
        if ($resultado_update) {
            return [
                'success' => true,
                'message' => 'Código de graduado guardado correctamente'
            ];
        } else {
            throw new Exception("Error al actualizar en la base de datos");
        }

    } catch (Exception $e) {
        error_log("Error en guardar_codigo_graduado_controlador: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al guardar el código: ' . $e->getMessage()
        ];
    }
}
    
    
   // FUNCIÓN NUEVA: Borrar códigos de graduados de una sección
public function borrarCodigosSeccionControlador($id_seccion) {
    try {
        // Validar parámetros (igual que las funciones existentes)
        if (!is_numeric($id_seccion) || $id_seccion <= 0) {
            throw new Exception("ID de sección inválido");
        }

        // Verificar que la sección existe y tiene estudiantes aprobados
        $consulta_verificar = "SELECT COUNT(*) as total 
                              FROM inscripcion 
                              WHERE id_seccion_fk = $id_seccion 
                              AND archivada = 1 
                              AND nota_final >= 10";
        
        $resultado_verificar = $this->ejecutar_consulta_simple($consulta_verificar);
        $total_estudiantes = $resultado_verificar->fetch(PDO::FETCH_ASSOC)['total'];
        
        if ($total_estudiantes == 0) {
            throw new Exception("No hay estudiantes aprobados en esta sección");
        }

        // Actualizar: establecer todos los códigos a NULL
        $consulta_update = "UPDATE inscripcion 
                           SET codigo_graduado = NULL 
                           WHERE id_seccion_fk = $id_seccion 
                           AND archivada = 1 
                           AND nota_final >= 10";
        
        $resultado_update = $this->ejecutar_consulta_simple($consulta_update);
        
        if ($resultado_update) {
            return [
                'success' => true,
                'message' => "Se borraron los códigos de $total_estudiantes estudiantes aprobados"
            ];
        } else {
            throw new Exception("Error al actualizar la base de datos");
        }

    } catch (Exception $e) {
        error_log("Error en borrarCodigosSeccionControlador: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al borrar códigos: ' . $e->getMessage()
        ];
    }
}
    
    
}
?>
