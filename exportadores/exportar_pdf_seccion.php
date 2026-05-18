<?php
require_once __DIR__ . '/../librerias/fpdf/fpdf.php';
require_once "../controladores/seccionControlador2.php";

$curso_vigente_id = $_GET['curso_vigente'] ?? 0;
$seccion_letra = $_GET['seccion'] ?? '';

if ($curso_vigente_id == 0 || $seccion_letra == '') {
    echo "Parámetros inválidos.";
    exit();
}

$ins_seccion = new seccionControlador();
$datos = $ins_seccion->exsel_prueba_1($curso_vigente_id, $seccion_letra);

if (!$datos) {
    echo "No se encontró la sección.";
    exit();
}

function calcularPonderado(array $estEvaluaciones, array $evaluacionesDef): float {
    $total = 0.0;
    foreach ($estEvaluaciones as $k => $ev) {
        if (!isset($evaluacionesDef[$k]['porcentaje'])) continue;
        $peso = floatval($evaluacionesDef[$k]['porcentaje']) / 100.0;
        $puntaje = isset($ev['puntaje']) ? floatval($ev['puntaje']) : 0.0;
        $total += $puntaje * $peso;
    }
    return $total;
}

// PDF en horizontal A4 con márgenes reducidos
$pdf = new FPDF('L','mm','A4');
$pdf->SetMargins(5,5,5);
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 8);

// Fuente y alturas reducidas
$fontRegular = 7;
$fontBold = 8;
$rowHeight = 5;    // alto fila mm
$headerHeight = 6; // alto header

// Calculo anchos dinámicos
$pageWidth = 297; // A4 landscape
$margins = 5 + 5;
$usable = $pageWidth - $margins;

$evalCount = max(1, count($datos['evaluaciones']));

// Bases iniciales
$baseNumber = 10;   // Nº
$baseCI     = 20;   // CI
$baseName   = 60;   // Nombre base antes de reducciones
$baseFinals = 12 * 3; // Acum, Cal, Red (cada una 12)

// Espacio restante para evaluaciones
$fixed = $baseNumber + $baseCI + $baseName + $baseFinals;
$remaining = max(20, $usable - $fixed);

// ancho por evaluación (total T+%)
$perEvalTotal = ($evalCount > 0) ? intval($remaining / $evalCount) : 0;
if ($perEvalTotal < 12) $perEvalTotal = 12; // forzar mínimo razonable

// Aplicar reducción solicitada:
//  - quitar más espacio horizontal a la columna Nombre (ej: -12 mm adicional)
//  - reducir las columnas de estrategias en 25% (cada subcolumna T y % se reduce en 25%)
$reduceName = 12; // quitar 12mm a la columna de nombres
$colNombre = max(30, $baseName - $reduceName); // deje mínimo 30mm

// distribuir perEvalTotal en T y % antes de reducción
$colT_before = max(8, intval($perEvalTotal * 0.55));
$colPerc_before = max(6, $perEvalTotal - $colT_before);

// aplicar 25% de reducción a cada subcolumna
//$colT = max(6, intval($colT_before * 0.50));
//$colPerc = max(5, intval($colPerc_before * 0.50));
$reduceName = 12; // quitar 12mm a la columna de nombres
$colNombre = max(30, $baseName - $reduceName); // deje mínimo 30mm

// distribuir perEvalTotal en T y % antes de reducción
$colT_before = max(8, intval($perEvalTotal * 0.55));
$colPerc_before = max(6, $perEvalTotal - $colT_before);

// valores fijos más estrechos para T y %
$colT = 12;
$colPerc = 10;


//$colT = 6;
//$colPerc = 5;

// recomputar usado por evaluaciones con nuevos anchos
$usedByEvals = ($colT + $colPerc) * $evalCount;
$otherFixed = $baseNumber + $baseCI + $baseFinals + $colNombre;

// si aún sobr/overflow, ajustar nombre y, si necesario, reducir levemente las subcols
$totalNeeded = $usedByEvals + $otherFixed;
if ($totalNeeded > $usable) {
    $overflow = $totalNeeded - $usable;
    // reducir columna nombre si posible hasta 20mm
    $canReduceName = max(0, $colNombre - 20);
    $take = min($overflow, $canReduceName);
    $colNombre -= $take;
    $overflow -= $take;
    // reducir proporcionalmente T and % si queda overflow
    if ($overflow > 0 && $evalCount > 0) {
        $reducePerEval = ceil($overflow / $evalCount);
        $reduceT = intval(ceil($reducePerEval * ($colT / ($colT + $colPerc))));
        $reducePerc = $reducePerEval - $reduceT;
        $colT = max(5, $colT - $reduceT);
        $colPerc = max(4, $colPerc - $reducePerc);
    }
}

// definicion final de columnas fijas
$colNumber = $baseNumber;
$colCI     = $baseCI;
$colAcum   = 12;
$colCal    = 12;
$colRedon  = 12;

// Titulos
$pdf->SetFont('Arial','B',$fontBold);
$pdf->Cell(0,8, utf8_decode('CENTRO EDUCATIVO PROFESIONAL "CECILIO ACOSTA"'), 0, 1, 'C');
$pdf->SetFont('Arial','', $fontRegular);
$pdf->Cell(0,5, utf8_decode('Sección: ' . $datos['seccion']['nombre_seccion'] . '  |  Año: ' . $datos['seccion']['anio_cohorte']), 0, 1, 'C');
$pdf->SetFont('Arial','I',6);
$pdf->Cell(0,4, utf8_decode('Generado: ' . date('d/m/Y H:i')), 0, 1, 'R');
$pdf->Ln(2);

// Cabecera informativa (Estrategias)
$pdf->SetFont('Arial','B',$fontBold);
$pdf->SetFillColor(230,230,230);
$leftW = $colNumber + $colCI + $colNombre;
$pdf->Cell($leftW, $headerHeight, '', 0, 0);
foreach ($datos['evaluaciones'] as $i => $eval) {
    $pdf->Cell($colT + $colPerc, $headerHeight, utf8_decode('Estrategia ' . ($i+1)), 1, 0, 'C', true);
}
$pdf->Cell($colAcum + $colCal + $colRedon, $headerHeight, utf8_decode('Final'), 1, 1, 'C', true);

// info profesor/curso/porcentaje
$pdf->SetFont('Arial','',$fontRegular);
$pdf->Cell($leftW, $rowHeight, utf8_decode('PROFESOR: ' . $datos['seccion']['nombre_profesor'] . ' ' . $datos['seccion']['apellido_profesor']), 0, 0);
foreach ($datos['evaluaciones'] as $eval) {
    $pdf->Cell($colT + $colPerc, $rowHeight, utf8_decode($eval['tipo_evaluacion'] ?? '-'), 1, 0, 'C');
}
$pdf->Cell($colAcum + $colCal + $colRedon, $rowHeight, '', 1, 1);

$pdf->Cell($leftW, $rowHeight, utf8_decode('CURSO: ' . $datos['seccion']['nombre_curso']), 0, 0);
foreach ($datos['evaluaciones'] as $eval) {
    $porcStr = isset($eval['porcentaje']) ? ($eval['porcentaje'] . '%') : '-';
    $pdf->Cell($colT + $colPerc, $rowHeight, utf8_decode($porcStr), 1, 0, 'C');
}
$pdf->Cell($colAcum + $colCal + $colRedon, $rowHeight, '', 1, 1);

// Encabezado columnas
$pdf->SetFont('Arial','B',$fontBold);
$pdf->Cell($colNumber, $headerHeight, 'Nº',1,0,'C',true);
$pdf->Cell($colCI, $headerHeight, 'C.I',1,0,'C',true);
$pdf->Cell($colNombre, $headerHeight, utf8_decode('Apellidos y Nombres'),1,0,'C',true);
foreach ($datos['evaluaciones'] as $eval) {
    $pdf->Cell($colT, $headerHeight, 'T',1,0,'C',true);
    $pdf->Cell($colPerc, $headerHeight, '%',1,0,'C',true);
}
$pdf->Cell($colAcum, $headerHeight, utf8_decode('Acum.'),1,0,'C',true);
$pdf->Cell($colCal, $headerHeight, utf8_decode('Cal.'),1,0,'C',true);
$pdf->Cell($colRedon, $headerHeight, utf8_decode('Red.'),1,1,'C',true);

// Cuerpo
$pdf->SetFont('Arial','',$fontRegular);
$notas_redondeadas = [];
foreach ($datos['estudiantes'] as $i => $est) {
    if ($pdf->GetY() > 185) { $pdf->AddPage(); }

    $pdf->Cell($colNumber, $rowHeight, ($i+1),1,0,'C');
    $pdf->Cell($colCI, $rowHeight, utf8_decode($est['ci'] ?? ''),1,0,'C');

    $nombre = trim(($est['apellido'] ?? '') . ' ' . ($est['nombre'] ?? ''));
    $nombrePrint = (strlen($nombre) > 30) ? substr($nombre,0,28) . '..' : $nombre;
    $pdf->Cell($colNombre, $rowHeight, utf8_decode($nombrePrint),1,0,'L');

    $nota_final = 0.0;
    foreach ($est['evaluaciones'] as $k => $ev) {
        $puntaje = isset($ev['puntaje']) ? floatval($ev['puntaje']) : 0.0;
        $pdf->Cell($colT, $rowHeight, ($puntaje === 0 ? '' : $puntaje),1,0,'C');
        $porc_obtenido = isset($ev['porcentaje_obtenido']) ? $ev['porcentaje_obtenido'] : '';
        $pdf->Cell($colPerc, $rowHeight, ($porc_obtenido === '' ? '' : ($porc_obtenido . '%')),1,0,'C');

        if (isset($datos['evaluaciones'][$k]['porcentaje'])) {
            $peso = floatval($datos['evaluaciones'][$k]['porcentaje']) / 100.0;
            $nota_final += $puntaje * $peso;
        }
    }

    $pdf->Cell($colAcum, $rowHeight, isset($est['suma_porcentajes']) ? ($est['suma_porcentajes'] . '%') : '',1,0,'C');
    $pdf->Cell($colCal, $rowHeight, round($nota_final,2),1,0,'C');

    $nota_redondeada = isset($est['nota_redondeada']) ? floatval($est['nota_redondeada']) : round($nota_final);
    $notas_redondeadas[] = floatval($nota_redondeada);

    $pdf->Cell($colRedon, $rowHeight, $nota_redondeada,1,1,'C');
}

// Resumen (umbral 50/100)
$notas = array_map('floatval', $notas_redondeadas);
$total_estudiantes = count($notas);
$threshold = 50.0;
$count_aprobados = 0;
foreach ($notas as $n) if ($n >= $threshold) $count_aprobados++;
$count_reprobados = $total_estudiantes - $count_aprobados;
$porcentaje_aprobados = $total_estudiantes ? round(($count_aprobados / $total_estudiantes) * 100, 2) : 0;
$porcentaje_reprobados = $total_estudiantes ? round(($count_reprobados / $total_estudiantes) * 100, 2) : 0;

// Espacio y resumen compacto colPerc 
$pdf->Ln(4);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(30,6, utf8_decode('Resumen'),0,1);
$pdf->SetFont('Arial','',7);
$pdf->Cell(50,6, utf8_decode('Cursantes'),1,0,'L');
$pdf->Cell(20,6, $total_estudiantes,1,0,'C');
$pdf->Cell(20,6, '100',1,1,'C');

$pdf->Cell(50,6, utf8_decode('Aprobados'),1,0,'L');
$pdf->Cell(20,6, $count_aprobados,1,0,'C');
$pdf->Cell(20,6, $porcentaje_aprobados . '%',1,1,'C');

$pdf->Cell(50,6, utf8_decode('Reprobados'),1,0,'L');
$pdf->Cell(20,6, $count_reprobados,1,0,'C');
$pdf->Cell(20,6, $porcentaje_reprobados . '%',1,1,'C');

// Descargar PDF
$pdf->Output('D', 'Notas_Seccion_' . $datos['seccion']['nombre_seccion'] . '_' . $datos['seccion']['anio_cohorte'] . '.pdf');
exit();
?>