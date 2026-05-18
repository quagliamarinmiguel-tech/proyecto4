<?php
require_once __DIR__ . '/../librerias/fpdf/fpdf.php';

function exportar_pdf_cohortes($cohortes) {
    $pdf = new FPDF();
    $pdf->AddPage();

    // Título
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0,10, 'Listado de Cohortes', 0, 1, 'C');
    $pdf->SetFont('Arial', 'I', 11);
    $pdf->Cell(0,8, 'Generado: ' . date('d/m/Y H:i'), 0, 1, 'R');
    $pdf->Ln(4);

    // Encabezado tabla
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(15,8,'ID',1,0,'C',true);
    $pdf->Cell(30,8,'Tipo',1,0,'C',true);
    $pdf->Cell(25,8,'Estado',1,0,'C',true);
    $pdf->Cell(25,8,'Cohorte',1,0,'C',true);
    $pdf->Cell(20,8,'Año',1,0,'C',true);
    $pdf->Cell(30,8,'Inicio',1,0,'C',true);
    $pdf->Cell(30,8,'Fin',1,0,'C',true);
    $pdf->Cell(15,8,'Cursos',1,1,'C',true);

    // Cuerpo tabla
    $pdf->SetFont('Arial', '', 9);
    foreach ($cohortes as $c) {
        $pdf->Cell(15,7,$c['id_cohorte'],1,0,'C');
        $pdf->Cell(30,7,$c['tipo_cohorte'],1,0,'C');
        $pdf->Cell(25,7,$c['estado_cohorte'],1,0,'C');
        $pdf->Cell(25,7,$c['numero_cohorte'],1,0,'C');
        $pdf->Cell(20,7,$c['anio_cohorte'],1,0,'C');
        $pdf->Cell(30,7,$c['fecha_inicio'],1,0,'C');
        $pdf->Cell(30,7,$c['fecha_fin'],1,0,'C');
        $pdf->Cell(15,7,$c['cantidad_cursos'],1,1,'C');
    }

    // Generar archivo
    $pdf->Output('D', 'cohortes.pdf');
}
