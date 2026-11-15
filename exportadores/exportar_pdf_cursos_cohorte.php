<?php
require_once __DIR__ . '/../librerias/fpdf/fpdf.php';

function exportar_pdf_cursos_cohorte($cursos, $nombre_cohorte = "Cohorte") {
    $pdf = new FPDF();
    $pdf->AddPage();

    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,'Cursos de la Cohorte',0,1,'C');
    $pdf->SetFont('Arial','I',12);
    $pdf->Cell(0,8,'Cohorte: ' . $nombre_cohorte, 0, 1, 'C');
    $pdf->SetFont('Arial','I',9);
    $pdf->Cell(0,6,'Generado: ' . date('d/m/Y H:i'), 0, 1, 'R');
    $pdf->Ln(5);

    $pdf->SetFont('Arial','B',10);
    $pdf->SetFillColor(220,220,220);
    $pdf->Cell(80,8,'Nombre',1,0,'C',true);
    $pdf->Cell(40,8,'Tipo',1,0,'C',true);
    $pdf->Cell(60,8,'Secciones vinculadas',1,1,'C',true);

    $pdf->SetFont('Arial','',9);
    foreach ($cursos as $c) {
        $pdf->Cell(80,7,$c['nombre_curso'],1,0,'L');
        $pdf->Cell(40,7,$c['tipo_curso'],1,0,'C');
        $pdf->Cell(60,7,$c['cantidad_secciones'],1,1,'C');
    }

    $pdf->Output('D', "cursos_{$nombre_cohorte}.pdf");
}
