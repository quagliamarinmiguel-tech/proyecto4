<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();

require_once '../librerias/vendor/autoload.php';
require_once '../controladores/seccionControlador2.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

try {
    // Verificar salida previa
    if (strlen(ob_get_contents()) > 0) {
        throw new Exception("Hay salida previa: «" . ob_get_contents() . "»");
    }

    // Validar parámetros
    $id_cohorte = $_GET['id_cohorte'] ?? 0;
    if ($id_cohorte == 0) {
        throw new Exception("ID de cohorte inválido");
    }

    // Obtener datos
    $info = seccionControlador::exsel_cohorte($id_cohorte);
    if (!$info) {
        throw new Exception("No se pudo obtener información del cohorte");
    }
    
    if (empty($info['secciones'])) {
        throw new Exception("No hay secciones listas (verifica que las evaluaciones sumen 100%)");
    }

    // Preparar nombre del archivo
    $cohorte = $info['cohorte'];
    $tipo_abrev = ($cohorte['tipo_cohorte'] === 'Intensivo') ? 'Int' :
                  (($cohorte['tipo_cohorte'] === 'Extendido') ? 'Ext' : 'Tec');
    $nombre_archivo = "Cal Co {$cohorte['numero_cohorte']}_{$cohorte['anio_cohorte']} {$tipo_abrev}.xlsx";

    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $primera_hoja = true;

    // Procesar cada sección
    foreach ($info['secciones'] as $seccion) {
        $datos = $seccion['datos'];
        $nombre_curso = $seccion['nombre_curso'];
        $tipo_curso = $seccion['tipo_curso'];
        $nombre_seccion = $seccion['nombre_seccion'];

        $tipo_abrev_curso = ($tipo_curso === 'Intensivo') ? 'Int' :
                            (($tipo_curso === 'Extendido') ? 'Ext' : 'Tec');
        
        $nombre_hoja = substr(limpiar_texto("{$nombre_curso} {$tipo_abrev_curso} {$nombre_seccion}"), 0, 31);

        // Usar la primera hoja existente o crear una nueva
        if ($primera_hoja) {
            $sheet = $spreadsheet->getActiveSheet();
            $primera_hoja = false;
        } else {
            $sheet = $spreadsheet->createSheet();
        }
        
        $sheet->setTitle($nombre_hoja);

        // Renderizar la tabla en la hoja
        renderizar_tabla_excel($sheet, $datos);
    }

    // Configurar headers para descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$nombre_archivo\"");
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');

    // Limpiar buffer y generar archivo
    ob_end_clean();
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    // Limpiar buffer en caso de error
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Mostrar error
    http_response_code(500);
    echo "Error al generar Excel: " . $e->getMessage();
    error_log("Error en generar_excel.php: " . $e->getMessage());
    exit;
}

/**
 * Limpia texto de HTML entities y caracteres problemáticos
 * 
 * @param string $texto Texto a limpiar
 * @return string Texto limpio
 */
function limpiar_texto($texto) {
    $texto = html_entity_decode($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // Remover caracteres que pueden causar problemas en nombres de hojas
    $texto = str_replace(['/', '\\', '?', '*', '[', ']', ':'], '', $texto);
    return trim($texto);
}

/**
 * Convierte un valor a número, manejando casos especiales como "NP"
 * 
 * @param mixed $valor Valor a convertir
 * @return float Valor numérico
 */
function valor_a_numero($valor) {
    if ($valor === 'NP' || $valor === '' || $valor === null) {
        return 0;
    }
    return floatval($valor);
}

/**
 * Renderiza la tabla de calificaciones en una hoja de Excel
 * 
 * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet La hoja donde renderizar
 * @param array $datos Array con información de sección, evaluaciones y estudiantes
 */
function renderizar_tabla_excel($sheet, $datos) {
    // Estilos
    $bold     = ['font' => ['bold' => true]];
    $center   = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
    $bgYellow = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF778']]];
    $bgRed    = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F54927']]];
    $bgGray   = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEEEEE']]];
    $border   = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

    $maxStrategies = 6;
    $fila = 13;

    $nombre_profesor = limpiar_texto(($datos['seccion']['nombre_profesor'] ?? '') . ' ' . ($datos['seccion']['apellido_profesor'] ?? ''));
    $nombre_curso = limpiar_texto($datos['seccion']['nombre_curso'] ?? '');
    $tipo_cohorte = limpiar_texto($datos['seccion']['tipo_cohorte'] ?? '');
    $nombre_seccion = limpiar_texto($datos['seccion']['nombre_seccion'] ?? '');

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

    // Filas de información adicional
    $infoRows = [
        ['label' => 'VALLE DE LA PASCUA ESTADO-GUÁRICO  RIF-J-503182261', 'field' => 'tipo_evaluacion'],
        ['label' => "PROFESOR: {$nombre_profesor}", 'field' => 'fecha_evaluacion'],
        ['label' => "CURSO: {$nombre_curso}", 'field' => 'porcentaje'],
        ['label' => "COHORTE: {$tipo_cohorte} " . ($datos['seccion']['numero_cohorte'] ?? '') . '/' . ($datos['seccion']['anio_cohorte'] ?? '') . " | SECCIÓN: {$nombre_seccion}", 'field' => 'obj'],
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
            $val = isset($datos['evaluaciones'][$i]) ? limpiar_texto($datos['evaluaciones'][$i][$info['field']] ?? '') : '';
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
        $sheet->setCellValue("{$col}{$fila}", limpiar_texto($est['ci'] ?? '')); $col++;
        $sheet->setCellValue("{$col}{$fila}", limpiar_texto(trim(($est['apellido'] ?? '') . ' ' . ($est['nombre'] ?? '')))); $col++;

        $nota_final = valor_a_numero($est['nota_final'] ?? 0);
        $suma_porcentajes = valor_a_numero($est['suma_porcentajes'] ?? 0);
        $nota_redondeada = intval($est['nota_redondeada'] ?? round($nota_final));

        // Rellenar estrategias 1–6 (puntaje y % obtenido)
        for ($j = 0; $j < $maxStrategies; $j++) {
            if (isset($est['evaluaciones'][$j])) {
                $puntaje_raw = $est['evaluaciones'][$j]['puntaje'] ?? '';
                if ($puntaje_raw === 'NP' || $puntaje_raw === '') {
                    $puntaje = 'NP';
                    $porcObtenido = '0%';
                } else {
                    $puntaje = valor_a_numero($puntaje_raw);
                    $porc_val = valor_a_numero($est['evaluaciones'][$j]['porcentaje_obtenido'] ?? 0);
                    $porcObtenido = number_format($porc_val, 2) . '%';
                }
            } else {
                $puntaje = '';
                $porcObtenido = '';
            }
            $sheet->setCellValue("{$col}{$fila}", $puntaje); $col++;
            $sheet->setCellValue("{$col}{$fila}", $porcObtenido); $col++;
        }

        // Acumulado, calificación y definitiva
        $sheet->setCellValue("{$col}{$fila}", number_format($suma_porcentajes, 2) . '%'); $col++;
        $sheet->setCellValue("{$col}{$fila}", number_format($nota_final, 2)); $col++;
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
    $sheet->setCellValue("C{$fila}", $total > 0 ? number_format(($aprobados / $total) * 100, 2) : 0); $fila++;

    $sheet->setCellValue("A{$fila}", "Reprobados");
    $sheet->setCellValue("B{$fila}", $reprobados);
    $sheet->setCellValue("C{$fila}", $total > 0 ? number_format(($reprobados / $total) * 100, 2) : 0);

    // Ajustes de ancho de columna
    $sheet->getColumnDimension('C')->setWidth(40);

    // Aplicar bordes a toda la zona de datos
    $start = 13;
    $end   = $fila;
    $lastCol = chr(ord('D') + ($maxStrategies * 2) + 2);
    $sheet->getStyle("A{$start}:{$lastCol}{$end}")->applyFromArray($border);
}