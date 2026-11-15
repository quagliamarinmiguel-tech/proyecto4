<?php
require_once '../librerias/vendor/autoload.php';
require_once '../controladores/seccionControlador2.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Parámetros
$cohorte_id = $_GET['id_cohorte'] ?? 0;
if ($cohorte_id == 0) {
    die("Parámetro de cohorte inválido.");
}

// Traer todas las secciones del cohorte con evaluaciones al 100%
$controlador = new seccionControlador();
$info_cohorte = $controlador->exsel_cohorte($cohorte_id);

if (!$info_cohorte || empty($info_cohorte['secciones'])) {
    die("No se encontraron secciones con evaluaciones completas para este cohorte.");
}

// Crear spreadsheet
$spreadsheet = new Spreadsheet();
$spreadsheet->removeSheetByIndex(0); // Eliminar hoja por defecto

// Estilos reutilizables
$bold     = ['font' => ['bold' => true]];
$center   = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
$bgYellow = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF778']]];
$bgRed    = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F54927']]];
$bgGray   = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEEEEE']]];
$border   = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

$maxStrategies = 6;

// Procesar cada sección
$hoja_num = 1;
foreach ($info_cohorte['secciones'] as $seccion_info) {
    $datos = $seccion_info['datos'];
    
    if (!$datos || empty($datos['estudiantes'])) {
        continue; // Saltar si no hay datos
    }

    // Crear nueva hoja
    $sheet = $spreadsheet->createSheet();
    $sheet->setTitle("Hoja{$hoja_num}");
    
    // 1) Agregar logo como encabezado
    $drawing = new Drawing();
    $drawing->setName('Encabezado');
    $drawing->setDescription('Logo header');
    $drawing->setPath(__DIR__ . '/../vistas/assets/img/logos7.png');
    $drawing->setCoordinates('A1');
    $drawing->setOffsetX(0);
    $drawing->setOffsetY(0);
    $drawing->setWorksheet($sheet);

    // 2) Encabezado institucional (fila 13)
    $fila = 13;
    $sheet->setCellValue("A{$fila}", 'CENTRO EDUCATIVO PROFESIONAL "CECILIO ACOSTA"');
    $sheet->mergeCells("A{$fila}:C{$fila}");
    $sheet->getStyle("A{$fila}:C{$fila}")->applyFromArray($bold);

    // Estrategias 1–6
    $col = 'D';
    for ($i = 0; $i < $maxStrategies; $i++) {
        $endCol = chr(ord($col) + 1);
        $sheet->setCellValue("{$col}{$fila}", "Estrategia " . ($i + 1));
        $sheet->mergeCells("{$col}{$fila}:{$endCol}{$fila}");
        $sheet->getStyle("{$col}{$fila}")->applyFromArray($center);
        $col = chr(ord($endCol) + 1);
    }

    // Columna "Final"
    $sheet->setCellValue("{$col}{$fila}", "Final");
    $sheet->mergeCells("{$col}{$fila}:" . chr(ord($col) + 2) . "{$fila}");
    $sheet->getStyle("{$col}{$fila}")->applyFromArray($center);

    // 3) Filas de info adicional
    $infoRows = [
        ['label' => 'VALLE DE LA PASCUA ESTADO-GUÁRICO  RIF-J-503182261', 'field' => 'tipo_evaluacion'],
        ['label' => 'PROFESOR: ' . $datos['seccion']['nombre_profesor'] . ' ' . $datos['seccion']['apellido_profesor'], 'field' => 'fecha_evaluacion'],
        ['label' => 'CURSO: ' . $datos['seccion']['nombre_curso'], 'field' => 'porcentaje'],
        ['label' => 'COHORTE: ' . $datos['seccion']['tipo_cohorte'] . ' ' . $datos['seccion']['numero_cohorte'] . '/' . $datos['seccion']['anio_cohorte'] . ' | SECCIÓN: ' . $datos['seccion']['nombre_seccion'], 'field' => 'obj'],
    ];

    foreach ($infoRows as $info) {
        $fila++;
        $sheet->setCellValue("A{$fila}", $info['label']);
        $sheet->mergeCells("A{$fila}:C{$fila}");
        $sheet->getStyle("A{$fila}:C{$fila}")->applyFromArray($bold);

        $col = 'D';
        for ($i = 0; $i < $maxStrategies; $i++) {
            $val = isset($datos['evaluaciones'][$i]) ? $datos['evaluaciones'][$i][$info['field']] : '';
            if ($info['field'] === 'porcentaje' && $val !== '') {
                $val .= '%';
            }
            if ($info['field'] === 'obj' && $val !== '') {
                $val = 'Obj: ' . $val;
            }
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

    // 4) Encabezado columnas de datos
    $fila++;
    $col = 'A';
    $sheet->setCellValue("{$col}{$fila}", "Nº");                $col++;
    $sheet->setCellValue("{$col}{$fila}", "C.I");               $col++;
    $sheet->setCellValue("{$col}{$fila}", "Apellidos y Nombres"); $col++;

    for ($i = 0; $i < $maxStrategies; $i++) {
        $sheet->setCellValue("{$col}{$fila}", "T");
        $sheet->getStyle("{$col}{$fila}")->applyFromArray($bgYellow);
        $col++;
        $sheet->setCellValue("{$col}{$fila}", "%");
        $sheet->getStyle("{$col}{$fila}")->applyFromArray($bgRed);
        $col++;
    }

    $sheet->setCellValue("{$col}{$fila}", "100%");
    $sheet->getStyle("{$col}{$fila}")->applyFromArray($bgRed); $col++;
    $sheet->setCellValue("{$col}{$fila}", "");
    $sheet->getStyle("{$col}{$fila}")->applyFromArray($bgRed); $col++;
    $sheet->setCellValue("{$col}{$fila}", "");

    // 5) Filas de estudiantes
    $fila++;
    $notas_redondeadas = [];

    foreach ($datos['estudiantes'] as $i => $est) {
        $col = 'A';
        $sheet->setCellValue("{$col}{$fila}", $i + 1); $col++;
        $sheet->setCellValue("{$col}{$fila}", $est['ci']); $col++;
        $sheet->setCellValue("{$col}{$fila}", $est['apellido'] . ' ' . $est['nombre']); $col++;

        // Rellenar estrategias 1–6
        for ($j = 0; $j < $maxStrategies; $j++) {
            if (isset($est['evaluaciones'][$j])) {
                $puntaje = $est['evaluaciones'][$j]['puntaje'];
                $porcObtenido = $est['evaluaciones'][$j]['porcentaje_obtenido'] . '%';
            } else {
                $puntaje      = '';
                $porcObtenido = '';
            }
            $sheet->setCellValue("{$col}{$fila}", $puntaje);      $col++;
            $sheet->setCellValue("{$col}{$fila}", $porcObtenido); $col++;
        }

        $sheet->setCellValue("{$col}{$fila}", $est['suma_porcentajes'] . '%'); $col++;
        $sheet->setCellValue("{$col}{$fila}", $est['nota_final']);         $col++;
        $sheet->setCellValue("{$col}{$fila}", $est['nota_redondeada']);

        $notas_redondeadas[] = $est['nota_redondeada'];
        $fila++;
    }

    // 6) Tabla resumen
    $fila++;
    $sheet->setCellValue("A{$fila}", "Resumen");
    $sheet->setCellValue("B{$fila}", "N°");
    $sheet->setCellValue("C{$fila}", "%");
    $sheet->getStyle("A{$fila}:C{$fila}")->applyFromArray($bgGray);
    $fila++;

    $total      = count($notas_redondeadas);
    $aprobados  = count(array_filter($notas_redondeadas, fn($n) => $n >= 10));
    $reprobados = $total - $aprobados;

    $sheet->setCellValue("A{$fila}", "Cursantes");
    $sheet->setCellValue("B{$fila}", $total);
    $sheet->setCellValue("C{$fila}", "100"); $fila++;

    $sheet->setCellValue("A{$fila}", "Aprobados");
    $sheet->setCellValue("B{$fila}", $aprobados);
    $sheet->setCellValue("C{$fila}", round(($aprobados / max($total,1)) * 100, 2)); $fila++;

    $sheet->setCellValue("A{$fila}", "Reprobados");
    $sheet->setCellValue("B{$fila}", $reprobados);
    $sheet->setCellValue("C{$fila}", round(($reprobados / max($total,1)) * 100, 2));

    // Ajustar ancho de columna C
    $sheet->getColumnDimension('C')->setWidth(40);

    $hoja_num++;
}

// 7) Salida al navegador
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

$nombre = "Notas_Cohorte_{$info_cohorte['cohorte']['tipo_cohorte']}_{$info_cohorte['cohorte']['numero_cohorte']}_{$info_cohorte['cohorte']['anio_cohorte']}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$nombre\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
