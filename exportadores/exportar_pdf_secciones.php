<?php
require_once __DIR__ . '/../librerias/fpdf/fpdf.php';

function exportar_pdf_secciones($secciones, $nombre_curso = 'Curso') {
    $pdf = new FPDF();
    $pdf->AddPage();

    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,'Secciones del Curso',0,1,'C');
    $pdf->SetFont('Arial','I',12);
    $pdf->Cell(0,8,'Curso: ' . $nombre_curso, 0, 1, 'C');
    $pdf->SetFont('Arial','I',9);
    $pdf->Cell(0,6,'Generado: ' . date('d/m/Y H:i'), 0, 1, 'R');
    $pdf->Ln(5);

    $pdf->SetFont('Arial','B',10);
    $pdf->SetFillColor(220,220,220);
    $pdf->Cell(30,8,'Letra',1,0,'C',true);
    $pdf->Cell(60,8,'Profesor',1,0,'C',true);
    $pdf->Cell(40,8,'Estudiantes',1,0,'C',true);
    $pdf->Cell(60,8,'Evaluaciones',1,1,'C',true);

    $pdf->SetFont('Arial','',9);
    foreach ($secciones as $s) {
        $pdf->Cell(30,7,$s['letra_seccion'],1,0,'C');
        $pdf->Cell(60,7,$s['nombre_profesor'],1,0,'L');
        $pdf->Cell(40,7,$s['cantidad_estudiantes'],1,0,'C');
        $pdf->Cell(60,7,$s['cantidad_evaluaciones'],1,1,'C');
    }

    $pdf->Output('D', "secciones_{$nombre_curso}.pdf");
}
