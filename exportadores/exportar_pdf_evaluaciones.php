<?php
require_once __DIR__ . '/../librerias/fpdf/fpdf.php';

function exportar_pdf_evaluaciones($evaluaciones, $nombre_seccion = 'Sección') {
    $pdf = new FPDF();
    $pdf->AddPage();

    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,'Evaluaciones de la Sección',0,1,'C');
    $pdf->SetFont('Arial','I',12);
    $pdf->Cell(0,8,'Sección: ' . $nombre_seccion, 0, 1, 'C');
    $pdf->SetFont('Arial','I',9);
    $pdf->Cell(0,6,'Generado: ' . date('d/m/Y H:i'), 0, 1, 'R');
    $pdf->Ln(5);

    $pdf->SetFont('Arial','B',10);
    $pdf->SetFillColor(220,220,220);
    $pdf->Cell(10,8,'#',1,0,'C',true);
    $pdf->Cell(40,8,'Tipo',1,0,'C',true);
    $pdf->Cell(45,8,'Objetivo',1,0,'C',true);
    $pdf->Cell(35,8,'Porcentaje',1,0,'C',true);
    $pdf->Cell(60,8,'Fecha',1,1,'C',true);

    $pdf->SetFont('Arial','',9);
    $i = 1;
    foreach ($evaluaciones as $e) {
        $pdf->Cell(10,7,$i,1,0,'C');
        $pdf->Cell(40,7,$e['tipo_evaluacion'],1,0,'L');
        $pdf->Cell(45,7,$e['objetivo_evaluacion'],1,0,'L');
        $pdf->Cell(35,7,$e['porcentaje_evaluacion'].'%',1,0,'C');
        $pdf->Cell(60,7,$e['fecha_evaluacion'],1,1,'C');
        $i++;
    }

    $pdf->Output('D', "evaluaciones_{$nombre_seccion}.pdf");
}
