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
$curso_vigente_id = $_GET['curso_vigente'] ?? 0;
$seccion_letra    = $_GET['seccion']       ?? '';
if ($curso_vigente_id == 0 || $seccion_letra == '') {
    die("Parámetros inválidos.");
}

// Traer datos
$datos = (new seccionControlador())->exsel_prueba_1($curso_vigente_id, $seccion_letra);
if (!$datos) {
    die("No se encontró la sección.");
}

// Crear spreadsheet
$spreadsheet = new Spreadsheet();
$sheet       = $spreadsheet->getActiveSheet();
$sheet->setTitle("Notas");

// 1) Agregar logo como encabezado
$drawing = new Drawing();
$drawing->setName('Encabezado');
$drawing->setDescription('Logo header');
$drawing->setPath(__DIR__ . '/../vistas/assets/img/logos7.png');
$drawing->setCoordinates('A1');
$drawing->setOffsetX(0);
$drawing->setOffsetY(0);
$drawing->setWorksheet($sheet);

// Estilos
$bold     = ['font' => ['bold' => true]];
$center   = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
$bgYellow = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF778']]];
$bgRed    = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F54927']]];
$bgGray   = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEEEEE']]];
$border   = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

// Máximo de estrategias a mostrar
$maxStrategies = 6;

// 2) Encabezado institucional (fila 2)
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

// Columna “Final”
$sheet->setCellValue("{$col}{$fila}", "Final");
$sheet->mergeCells("{$col}{$fila}:" . chr(ord($col) + 2) . "{$fila}");
$sheet->getStyle("{$col}{$fila}")->applyFromArray($center);

// 3) Filas de info adicional (fila 3–6)
$cohorte = $datos['seccion']['numero_cohorte'];
$anio    = $datos['seccion']['anio_cohorte'];
$tipo    = $datos['seccion']['tipo_cohorte'];
$seccion = $datos['seccion']['nombre_seccion'];

   // ['label' => 'COHORTE: ' . $datos['seccion']['tipo_cohorte']
  //             . ' ' . $datos['seccion']['numero_cohorte'] . '/' 
  //             . $datos['seccion']['anio_cohorte']
//               . ' | SECCIÓN: ' . $datos['seccion']['nombre_seccion'],              'field' => 'obj'],

$infoRows = [
    ['label' => 'VALLE DE LA PASCUA ESTADO-GUÁRICO  RIF-J-503182261',                 'field' => 'tipo_evaluacion'],
    ['label' => 'PROFESOR: ' . $datos['seccion']['nombre_profesor'] 
               . ' ' . $datos['seccion']['apellido_profesor'],                     'field' => 'fecha_evaluacion'],
    ['label' => 'CURSO: ' . $datos['seccion']['nombre_curso'],                      'field' => 'porcentaje'],

];

if ($cohorte === null || $anio === null || $tipo === null) {
    $labelCohorte = 'PERIODO INTENSIVO: ' 
                  . $datos['seccion']['fecha_inicio_int'] 
                  . ' al ' 
                  . $datos['seccion']['fecha_fin_int'] 
                  . ' | SECCIÓN: ' . $seccion;
} else {
    $labelCohorte = 'COHORTE: ' . $tipo . ' ' . $cohorte . '/' . $anio 
                  . ' | SECCIÓN: ' . $seccion;
}

$infoRows[] = ['label' => $labelCohorte, 'field' => 'obj'];
//foreach ($infoRows as $info) {
//    $fila++;
//    $sheet->setCellValue("A{$fila}", $info['label']);
//    $sheet->mergeCells("A{$fila}:C{$fila}");
//    $sheet->getStyle("A{$fila}:C{$fila}")->applyFromArray($bold);
//
//    $col = 'D';
//    foreach ($datos['evaluaciones'] as $eval) {
//        $valor = $eval[$info['field']];
//        if ($info['field'] === 'porcentaje') {
//            $valor .= '%';
//        }
//        if ($info['field'] === 'obj') {
//            $valor = 'Obj: ' . $valor;
//        }
//        $endCol = chr(ord($col) + 1);
//        $sheet->setCellValue("{$col}{$fila}", $valor);
//        $sheet->mergeCells("{$col}{$fila}:{$endCol}{$fila}");
//        $sheet->getStyle("{$col}{$fila}")->applyFromArray($center);
//        $col = chr(ord($endCol) + 1);
//    }
//    $sheet->mergeCells("{$col}{$fila}:" . chr(ord($col) + 2) . "{$fila}");
//}








// Máximo de estrategias a mostrar
$maxStrategies = 6;

foreach ($infoRows as $info) {
    $fila++;

    // A–C: etiqueta
    $sheet->setCellValue("A{$fila}", $info['label']);
    $sheet->mergeCells("A{$fila}:C{$fila}");
    $sheet->getStyle("A{$fila}:C{$fila}")->applyFromArray($bold);

    // D–…: siempre 6 bloques de 2 columnas
    $col = 'D';
    for ($i = 0; $i < $maxStrategies; $i++) {
        $val = isset($datos['evaluaciones'][$i])
             ? $datos['evaluaciones'][$i][$info['field']]
             : '';
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

    // Bajo “Final”: si es fila Obj, ponemos los títulos; si no, 3 vacías rojas
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
            //$sheet->getStyle("{$col}{$fila}")->applyFromArray($bgRed);
            $col++;
        }
    }
}












// 4) Encabezado columnas de datos (fila 7)
$fila++;
$col = 'A';
$sheet->setCellValue("{$col}{$fila}", "Nº");                $col++;
$sheet->setCellValue("{$col}{$fila}", "C.I");               $col++;
$sheet->setCellValue("{$col}{$fila}", "Apellidos y Nombres"); $col++;

// Para cada una de las 6 estrategias: T y %
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
$sheet->getStyle("{$col}{$fila}")->applyFromArray($bgRed);		$col++;

$sheet->setCellValue("{$col}{$fila}", "");
$sheet->getStyle("{$col}{$fila}")->applyFromArray($bgRed);		$col++;

$sheet->setCellValue("{$col}{$fila}", "");

// 5) Filas de estudiantes (desde fila 8)
$fila++;
$notas_redondeadas = [];

foreach ($datos['estudiantes'] as $i => $est) {
    $col = 'A';
    $sheet->setCellValue("{$col}{$fila}", $i + 1); $col++;
    $sheet->setCellValue("{$col}{$fila}", $est['ci']); $col++;
    $sheet->setCellValue("{$col}{$fila}", $est['apellido'] . ' ' . $est['nombre']); $col++;

    // Calcular nota final
//    $nota_final = 0;
 //   foreach ($est['evaluaciones'] as $k => $ev) {
 //       $peso        = floatval($datos['evaluaciones'][$k]['porcentaje']) / 100;
 //       $puntaje_val = floatval($ev['puntaje'] ?? 0);
 //       $nota_final += $puntaje_val * $peso;
 //   }
        
    $nota_final = 0;
    foreach ($est['evaluaciones'] as $k => $ev) {
        if (!isset($datos['evaluaciones'][$k]['porcentaje'])) continue;
        $peso = floatval($datos['evaluaciones'][$k]['porcentaje']) / 100;
        $puntaje_val = floatval($ev['puntaje'] ?? 0);
        $nota_final += $puntaje_val * $peso;
    }
    

    // Rellenar estrategias 1–6
    for ($j = 0; $j < $maxStrategies; $j++) {
        if (isset($est['evaluaciones'][$j])) {
            $puntaje      = floatval($est['evaluaciones'][$j]['puntaje']);
            $porcObtenido = $est['evaluaciones'][$j]['porcentaje_obtenido'] . '%';
        } else {
            $puntaje      = '';
            $porcObtenido = '';
        }
        $sheet->setCellValue("{$col}{$fila}", $puntaje);      $col++;
        $sheet->setCellValue("{$col}{$fila}", $porcObtenido); $col++;
    }

    // Acumulado, calificación y definitiva
    $sheet->setCellValue("{$col}{$fila}", $est['suma_porcentajes'] . '%'); $col++;
    $sheet->setCellValue("{$col}{$fila}", round($nota_final, 2));         $col++;
    $nota_redondeada = ($nota_final - floor($nota_final) >= 0.5)
                     ? ceil($nota_final)
                     : floor($nota_final);
    $sheet->setCellValue("{$col}{$fila}", $nota_redondeada);

    $notas_redondeadas[] = $nota_redondeada;
    $fila++;
}

// 6) Tabla resumen (fila siguiente)
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

// Ajustes finales a ancho de columna
// Columna C 1/3 menos que 60 → 40
$sheet->getColumnDimension('C')->setWidth(40);

// 7) Salida al navegador
ini_set('display_errors',   0);
ini_set('display_startup_errors', 0);
error_reporting(0);

$nombre = "Notas_Seccion_{$datos['seccion']['nombre_seccion']}_{$datos['seccion']['anio_cohorte']}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$nombre\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;