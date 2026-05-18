<?php
   // require_once "./modelos/seccionModelo.php";
    require_once "../modelos/seccionModelo.php";

    
    class seccionControlador extends seccionModelo{
                                                   
    
    
      public static function renderizar_tabla_excel($sheet, $datos) {
            // Estilos locales
            $bold     = ['font' => ['bold' => true]];
            $center   = ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];
            $bgYellow = ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF778']]];
            $bgRed    = ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F54927']]];
            $bgGray   = ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEEEEE']]];
            $border   = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];

            $maxStrategies = 6;

            // Empieza similar a tu código: fila base
            $fila = 13;

            // Encabezado institucional
            $sheet->setCellValue("A{$fila}", 'CENTRO EDUCATIVO PROFESIONAL "CECILIO ACOSTA"');
            $sheet->mergeCells("A{$fila}:C{$fila}");
            $sheet->getStyle("A{$fila}:C{$fila}")->applyFromArray($bold);

            // Estrategias 1–6 (cada estrategia ocupa 2 columnas)
            $col = 'D';
            for ($i = 0; $i < $maxStrategies; $i++) {
                $endCol = chr(ord($col) + 1);
                $sheet->setCellValue("{$col}{$fila}", "Estrategia " . ($i + 1));
                $sheet->mergeCells("{$col}{$fila}:{$endCol}{$fila}");
                $sheet->getStyle("{$col}{$fila}")->applyFromArray($center);
                $col = chr(ord($endCol) + 1);
            }

            // Columna Final (3 columnas)
            $sheet->setCellValue("{$col}{$fila}", "Final");
            $sheet->mergeCells("{$col}{$fila}:" . chr(ord($col) + 2) . "{$fila}");
            $sheet->getStyle("{$col}{$fila}")->applyFromArray($center);

            // Filas info adicional
            $infoRows = [
                ['label' => 'VALLE DE LA PASCUA ESTADO-GUÁRICO  RIF-J-503182261', 'field' => 'tipo_evaluacion'],
                ['label' => 'PROFESOR: ' . ($datos['seccion']['nombre_profesor'] ?? '') . ' ' . ($datos['seccion']['apellido_profesor'] ?? ''), 'field' => 'fecha_evaluacion'],
                ['label' => 'CURSO: ' . ($datos['seccion']['nombre_curso'] ?? ''), 'field' => 'porcentaje'],
                ['label' => 'COHORTE: ' . ($datos['seccion']['tipo_cohorte'] ?? '') . ' ' . ($datos['seccion']['numero_cohorte'] ?? '') . '/' . ($datos['seccion']['anio_cohorte'] ?? '') . ' | SECCIÓN: ' . ($datos['seccion']['nombre_seccion'] ?? ''), 'field' => 'obj'],
            ];

            foreach ($infoRows as $info) {
                $fila++;

                // A–C etiqueta
                $sheet->setCellValue("A{$fila}", $info['label']);
                $sheet->mergeCells("A{$fila}:C{$fila}");
                $sheet->getStyle("A{$fila}:C{$fila}")->applyFromArray($bold);

                // D–...: 6 bloques de 2 columnas
                $col = 'D';
                for ($i = 0; $i < $maxStrategies; $i++) {
                    $val = isset($datos['evaluaciones'][$i]) ? ($datos['evaluaciones'][$i][$info['field']] ?? '') : '';
                    if ($info['field'] === 'porcentaje' && $val !== '') $val .= '%';
                    if ($info['field'] === 'obj' && $val !== '') $val = 'Obj: ' . $val;
                    $endCol = chr(ord($col) + 1);
                    $sheet->setCellValue("{$col}{$fila}", $val);
                    $sheet->mergeCells("{$col}{$fila}:{$endCol}{$fila}");
                    $sheet->getStyle("{$col}{$fila}")->applyFromArray($center);
                    $col = chr(ord($endCol) + 1);
                }

                // Bajo "Final"
                if ($info['field'] === 'obj') {
                    $titles = ['Acumulado', 'Calificación', 'Calif. definitiva'];
                    foreach ($titles as $title) {
                        $sheet->setCellValue("{$col}{$fila}", $title);
                        $sheet->getStyle("{$col}{$fila}")->applyFromArray($center);
                        $col++;
                    }
                } else {
                    for ($k = 0; $k < 3; $k++) {
                        $sheet->setCellValue("{$col}{$fila}", '');
                        $col++;
                    }
                }
            }

            // Encabezado columnas de datos
            $fila++;
            $col = 'A';
            $sheet->setCellValue("{$col}{$fila}", "Nº"); $col++;
            $sheet->setCellValue("{$col}{$fila}", "C.I"); $col++;
            $sheet->setCellValue("{$col}{$fila}", "Apellidos y Nombres"); $col++;

            for ($i = 0; $i < $maxStrategies; $i++) {
                $sheet->setCellValue("{$col}{$fila}", "T");
                $sheet->getStyle("{$col}{$fila}")->applyFromArray($bgYellow);
                $col++;
                $sheet->setCellValue("{$col}{$fila}", "%");
                $sheet->getStyle("{$col}{$fila}")->applyFromArray($bgRed);
                $col++;
            }

            // Columnas finales
            $sheet->setCellValue("{$col}{$fila}", "100%");
            $sheet->getStyle("{$col}{$fila}")->applyFromArray($bgRed); $col++;
            $sheet->setCellValue("{$col}{$fila}", "");
            $sheet->getStyle("{$col}{$fila}")->applyFromArray($bgRed); $col++;
            $sheet->setCellValue("{$col}{$fila}", "");

            // Filas de estudiantes
            $fila++;
            $notas_redondeadas = [];

            foreach ($datos['estudiantes'] as $i => $est) {
                $col = 'A';
                $sheet->setCellValue("{$col}{$fila}", $i + 1); $col++;
                $sheet->setCellValue("{$col}{$fila}", $est['ci'] ?? ''); $col++;
                $sheet->setCellValue("{$col}{$fila}", trim(($est['apellido'] ?? '') . ' ' . ($est['nombre'] ?? ''))); $col++;

                // Calcular nota final (usando porcentajes y puntajes)
                $nota_final = 0;
                foreach ($est['evaluaciones'] as $k => $ev) {
                    if (!isset($datos['evaluaciones'][$k]['porcentaje'])) continue;
                    $peso = floatval($datos['evaluaciones'][$k]['porcentaje']) / 100;
                    $puntaje_val = floatval($ev['puntaje'] ?? 0);
                    $nota_final += $puntaje_val * $peso;
                }

                // Rellenar estrategias 1–6 (puntaje y % obtenido)
                for ($j = 0; $j < $maxStrategies; $j++) {
                    if (isset($est['evaluaciones'][$j])) {
                        $puntaje = floatval($est['evaluaciones'][$j]['puntaje']);
                        $porcObtenido = ($est['evaluaciones'][$j]['porcentaje_obtenido'] ?? '') . '%';
                    } else {
                        $puntaje = '';
                        $porcObtenido = '';
                    }
                    $sheet->setCellValue("{$col}{$fila}", $puntaje); $col++;
                    $sheet->setCellValue("{$col}{$fila}", $porcObtenido); $col++;
                }

                // Acumulado, calificación y definitiva
                $sheet->setCellValue("{$col}{$fila}", ($est['suma_porcentajes'] ?? '') . '%'); $col++;
                $sheet->setCellValue("{$col}{$fila}", round($nota_final, 2)); $col++;
                $nota_redondeada = ($nota_final - floor($nota_final) >= 0.5) ? ceil($nota_final) : floor($nota_final);
                $sheet->setCellValue("{$col}{$fila}", $nota_redondeada);

                $notas_redondeadas[] = $nota_redondeada;
                $fila++;
            }

            // Tabla resumen
            $fila++;
            $sheet->setCellValue("A{$fila}", "Resumen");
            $sheet->setCellValue("B{$fila}", "N°");
            $sheet->setCellValue("C{$fila}", "%");
            $sheet->getStyle("A{$fila}:C{$fila}")->applyFromArray($bgGray);
            $fila++;

            $total     = count($notas_redondeadas);
            $aprobados = count(array_filter($notas_redondeadas, fn($n) => $n >= 10));
            $reprobados= $total - $aprobados;

            $sheet->setCellValue("A{$fila}", "Cursantes");
            $sheet->setCellValue("B{$fila}", $total);
            $sheet->setCellValue("C{$fila}", "100"); $fila++;

            $sheet->setCellValue("A{$fila}", "Aprobados");
            $sheet->setCellValue("B{$fila}", $aprobados);
            $sheet->setCellValue("C{$fila}", round(($aprobados / max($total,1)) * 100, 2)); $fila++;

            $sheet->setCellValue("A{$fila}", "Reprobados");
            $sheet->setCellValue("B{$fila}", $reprobados);
            $sheet->setCellValue("C{$fila}", round(($reprobados / max($total,1)) * 100, 2));

            // Ajustes finales de ancho de columna
            $sheet->getColumnDimension('C')->setWidth(40);

            // Aplicar bordes simples a la zona de datos (opcional)
            // Determinar rango usado aproximado: desde fila 13 hasta $fila
            $start = 13;
            $end   = $fila;
            $sheet->getStyle("A{$start}:" . chr(ord('D') + ($maxStrategies * 2) + 3) . "{$end}")->applyFromArray($border);

            // No se hace output aquí; el caller guarda/descarga el spreadsheet
        }
        
        
        
        
        
        
        
        
        
        public static function exsel_cohorte($id_cohorte) {
        $pdo = mainModel::conectar();
        $id_cohorte = mainModel::limpiar_cadena($id_cohorte);

        // Metadatos del cohorte
        $cohorte_stmt = $pdo->prepare("
            SELECT numero_cohorte, anio_cohorte, tipo_cohorte
            FROM cohorte
            WHERE id_cohorte = :id
        ");
        $cohorte_stmt->bindParam(":id", $id_cohorte, PDO::PARAM_INT);
        $cohorte_stmt->execute();
        $cohorte = $cohorte_stmt->fetch(PDO::FETCH_ASSOC);
        if (!$cohorte) return null;

        $resultado = [
            'cohorte' => $cohorte,
            'secciones' => []
        ];

        // Cursos vigentes
        $cursos_stmt = $pdo->prepare("
            SELECT id_curso_vigente, id_curso_fk
            FROM curso_vigente
            WHERE id_cohorte_fk = :id AND estado_curso_vigente = 1 
        ");
        $cursos_stmt->bindParam(":id", $id_cohorte, PDO::PARAM_INT);
        $cursos_stmt->execute();
        $cursos = $cursos_stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cursos as $curso) {
            $curso_vigente_id = $curso['id_curso_vigente'];

            // Secciones activas
            $secciones_stmt = $pdo->prepare("
                SELECT s.id_seccion, s.nombre_seccion, c.nombre_curso, c.tipo_curso
                FROM seccion s
                INNER JOIN curso c ON c.id_curso = :id_curso
                WHERE s.id_curso_vigente_fk = :id_cv AND s.archivar_seccion = 1
            ");
            $secciones_stmt->bindParam(":id_cv", $curso_vigente_id, PDO::PARAM_INT);
            $secciones_stmt->bindParam(":id_curso", $curso['id_curso_fk'], PDO::PARAM_INT);
            $secciones_stmt->execute();
            $secciones = $secciones_stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($secciones as $seccion) {
                $id_seccion = $seccion['id_seccion'];
                $nombre_seccion = $seccion['nombre_seccion'];

                // Verificar suma de porcentajes
                $eval_stmt = $pdo->prepare("
                    SELECT SUM(porcentaje) as total
                    FROM evaluacion
                    WHERE id_seccion = :id AND archivar_evaluacion = 1
                ");
                $eval_stmt->bindParam(":id", $id_seccion, PDO::PARAM_INT);
                $eval_stmt->execute();
                $eval_total = $eval_stmt->fetchColumn();

                if (round($eval_total, 2) != 100.00) continue;

                // Obtener datos de la sección
                $datos = self::exsel_prueba_1($curso_vigente_id, $nombre_seccion);
                if (!$datos) continue;

                $resultado['secciones'][] = [
                    'datos' => $datos,
                    'nombre_curso' => $seccion['nombre_curso'],
                    'tipo_curso' => $seccion['tipo_curso'],
                    'nombre_seccion' => $seccion['nombre_seccion']
                ];
            }
        }

        return $resultado;
    }
        
        
        /* consulta de solo cohortes para exsel prueba 1
        $sec = $pdo->prepare("
    SELECT 
        s.id_seccion,
        s.nombre_seccion,
        p.nombre_profesor,
        p.apellido_profesor,
        c.nombre_curso,
        coh.numero_cohorte,
        coh.anio_cohorte,
        coh.tipo_cohorte,
        pi.fecha_inicio_int,
        pi.fecha_fin_int
    FROM seccion s
    INNER JOIN profesor p ON s.ci_profesor = p.id_profesor
    INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
    INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
    LEFT JOIN cohorte coh ON cv.id_cohorte_fk = coh.id_cohorte
    LEFT JOIN periodo_intensivo pi ON pi.id_curso_vigente_fk = cv.id_curso_vigente
    WHERE s.id_curso_vigente_fk = :cv AND s.nombre_seccion = :nombre
");
        */
        
        
    public static function exsel_prueba_1($id_curso_vigente, $nombre_seccion) {
    $pdo = mainModel::conectar();
    $id_curso_vigente = mainModel::limpiar_cadena($id_curso_vigente);
    $nombre_seccion = mainModel::limpiar_cadena($nombre_seccion);

    // 1. Buscar la sección con todos los metadatos
$sec = $pdo->prepare("
    SELECT 
        s.id_seccion,
        s.nombre_seccion,
        p.nombre_profesor,
        p.apellido_profesor,
        c.nombre_curso,
        coh.numero_cohorte,
        coh.anio_cohorte,
        coh.tipo_cohorte,
        pi.fecha_inicio_int,
        pi.fecha_fin_int
    FROM seccion s
    INNER JOIN profesor p ON s.ci_profesor = p.id_profesor
    INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
    INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
    LEFT JOIN cohorte coh ON cv.id_cohorte_fk = coh.id_cohorte
    LEFT JOIN periodo_intensivo pi ON pi.id_curso_vigente_fk = cv.id_curso_vigente
    WHERE s.id_curso_vigente_fk = :cv AND s.nombre_seccion = :nombre
");
    $sec->bindParam(":cv", $id_curso_vigente, PDO::PARAM_INT);
    $sec->bindParam(":nombre", $nombre_seccion, PDO::PARAM_STR);
    $sec->execute();
    $datos_seccion = $sec->fetch(PDO::FETCH_ASSOC);

    if (!$datos_seccion) return null;

    $id_seccion = $datos_seccion['id_seccion'];

    // 2. Evaluaciones
    $evaluaciones = seccionModelo::obtener_evaluaciones_modelo($id_seccion)->fetchAll(PDO::FETCH_ASSOC);

    // 3. Estudiantes
    $estudiantes = seccionModelo::obtener_estudiantes_modelo($id_seccion, 0, 10000)->fetchAll(PDO::FETCH_ASSOC);

    $resultado = [
        'seccion' => $datos_seccion,
        'evaluaciones' => $evaluaciones,
        'estudiantes' => []
    ];

    foreach ($estudiantes as $est) {
        $puntajes = seccionModelo::obtener_puntajes_modelo($est['id_inscripcion'])->fetchAll(PDO::FETCH_ASSOC);
        $puntajes_map = [];
        foreach ($puntajes as $p) {
            $puntajes_map[$p['id_evaluacion']] = $p['puntaje'];
        }

        $detalle = [
            'id_estudiante' => $est['id_estudiante'],
            'ci' => $est['ci_estudiante'],
            'nombre' => $est['nombre'],
            'apellido' => $est['apellido'],
            'evaluaciones' => [],
            'suma_porcentajes' => 0,
            'nota_final' => 0,
            'nota_redondeada' => '-'
        ];

        $notaFinal = 0;
        foreach ($evaluaciones as $eval) {
            $idEval = $eval['id_evaluacion'];
            $porcentaje = (float)$eval['porcentaje'];
            $puntaje = isset($puntajes_map[$idEval]) ? $puntajes_map[$idEval] : 'NP';
            $puntaje_valor = ($puntaje === 'NP') ? 0 : floatval($puntaje);

            $puntaje_maximo = 20; // Puedes ajustar si lo tienes en BD
            $porcentaje_obtenido = ($puntaje_maximo > 0) ? ($puntaje_valor / $puntaje_maximo) * $porcentaje : 0;

            $notaFinal += $porcentaje_obtenido;

            $detalle['evaluaciones'][] = [
                'id_evaluacion' => $idEval,
                'tipo' => $eval['tipo_evaluacion'],
                'obj' => $eval['obj'],
                'porcentaje' => $porcentaje,
                'puntaje' => $puntaje,
                'porcentaje_obtenido' => round($porcentaje_obtenido, 2)
            ];
        }

        $detalle['suma_porcentajes'] = round($notaFinal, 2);
        $detalle['nota_final'] = round($notaFinal, 2);
        $detalle['nota_redondeada'] = ($notaFinal - floor($notaFinal) >= 0.5) ? ceil($notaFinal) : floor($notaFinal);

        $resultado['estudiantes'][] = $detalle;
    }        
        
    return $resultado;
}    
    public static function exsel_prueba_1_solo_co($id_curso_vigente, $nombre_seccion) {
    $pdo = mainModel::conectar();
    $id_curso_vigente = mainModel::limpiar_cadena($id_curso_vigente);
    $nombre_seccion = mainModel::limpiar_cadena($nombre_seccion);

    // 1. Buscar la sección con todos los metadatos
    $sec = $pdo->prepare("
        SELECT 
            s.id_seccion,
            s.nombre_seccion,
            p.nombre_profesor,
            p.apellido_profesor,
            c.nombre_curso,
            coh.numero_cohorte,
            coh.anio_cohorte,
            coh.tipo_cohorte
        FROM seccion s
        INNER JOIN profesor p ON s.ci_profesor = p.id_profesor
        INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        INNER JOIN cohorte coh ON cv.id_cohorte_fk = coh.id_cohorte
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        WHERE s.id_curso_vigente_fk = :cv AND s.nombre_seccion = :nombre
        
        
    ");
    $sec->bindParam(":cv", $id_curso_vigente, PDO::PARAM_INT);
    $sec->bindParam(":nombre", $nombre_seccion, PDO::PARAM_STR);
    $sec->execute();
    $datos_seccion = $sec->fetch(PDO::FETCH_ASSOC);

    if (!$datos_seccion) return null;

    $id_seccion = $datos_seccion['id_seccion'];

    // 2. Evaluaciones
    $evaluaciones = seccionModelo::obtener_evaluaciones_modelo($id_seccion)->fetchAll(PDO::FETCH_ASSOC);

    // 3. Estudiantes
    $estudiantes = seccionModelo::obtener_estudiantes_modelo($id_seccion, 0, 10000)->fetchAll(PDO::FETCH_ASSOC);

    $resultado = [
        'seccion' => $datos_seccion,
        'evaluaciones' => $evaluaciones,
        'estudiantes' => []
    ];

    foreach ($estudiantes as $est) {
        $puntajes = seccionModelo::obtener_puntajes_modelo($est['id_inscripcion'])->fetchAll(PDO::FETCH_ASSOC);
        $puntajes_map = [];
        foreach ($puntajes as $p) {
            $puntajes_map[$p['id_evaluacion']] = $p['puntaje'];
        }

        $detalle = [
            'id_estudiante' => $est['id_estudiante'],
            'ci' => $est['ci_estudiante'],
            'nombre' => $est['nombre'],
            'apellido' => $est['apellido'],
            'evaluaciones' => [],
            'suma_porcentajes' => 0,
            'nota_final' => 0,
            'nota_redondeada' => '-'
        ];

        $notaFinal = 0;
        foreach ($evaluaciones as $eval) {
            $idEval = $eval['id_evaluacion'];
            $porcentaje = (float)$eval['porcentaje'];
            $puntaje = isset($puntajes_map[$idEval]) ? $puntajes_map[$idEval] : 'NP';
            $puntaje_valor = ($puntaje === 'NP') ? 0 : floatval($puntaje);

            $puntaje_maximo = 20; // Puedes ajustar si lo tienes en BD
            $porcentaje_obtenido = ($puntaje_maximo > 0) ? ($puntaje_valor / $puntaje_maximo) * $porcentaje : 0;

            $notaFinal += $porcentaje_obtenido;

            $detalle['evaluaciones'][] = [
                'id_evaluacion' => $idEval,
                'tipo' => $eval['tipo_evaluacion'],
                'obj' => $eval['obj'],
                'porcentaje' => $porcentaje,
                'puntaje' => $puntaje,
                'porcentaje_obtenido' => round($porcentaje_obtenido, 2)
            ];
        }

        $detalle['suma_porcentajes'] = round($notaFinal, 2);
        $detalle['nota_final'] = round($notaFinal, 2);
        $detalle['nota_redondeada'] = ($notaFinal - floor($notaFinal) >= 0.5) ? ceil($notaFinal) : floor($notaFinal);

        $resultado['estudiantes'][] = $detalle;
    }        
        
    return $resultado;
}
  
    
    }

