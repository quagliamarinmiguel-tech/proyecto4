<?php
require_once __DIR__ . '/../librerias/fpdf/fpdf.php';

function exportar_pdf_estudiantes($estudiantes, $nombre_seccion = 'Sin nombre') {
    $pdf = new FPDF();
    $pdf->AddPage();

    // Encabezado principal
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0,10,'Listado de Estudiantes',0,1,'C');
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell(0,8,'Sección: ' . $nombre_seccion, 0, 1, 'C');
    $pdf->Ln(5);

    // Cabecera de tabla
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(200, 200, 200);
    $pdf->Cell(15,8,'ID',1,0,'C',true);
    $pdf->Cell(50,8,'Nombre y Apellido',1,0,'C',true);
    $pdf->Cell(30,8,'Nacimiento',1,0,'C',true);
    $pdf->Cell(30,8,'Teléfono',1,0,'C',true);
    $pdf->Cell(45,8,'Correo',1,0,'C',true);
    $pdf->Cell(20,8,'Usuario',1,1,'C',true);

    // Contenido de tabla
    $pdf->SetFont('Arial', '', 9);
    foreach ($estudiantes as $e) {
        $nombre = $e['nombre'] . ' ' . $e['apellido'];
        $fecha = (!empty($e['fecha_nacimiento'])) ? date("d/m/Y", strtotime($e['fecha_nacimiento'])) : '—';
        $pdf->Cell(15,7,$e['id_estudiante'],1,0,'C');
        $pdf->Cell(50,7,$nombre,1,0,'L');
        $pdf->Cell(30,7,$fecha,1,0,'C');
        $pdf->Cell(30,7,$e['telefono_estudiante'],1,0,'C');
        $pdf->Cell(45,7,$e['correo_estudiante'],1,0,'L');
        $pdf->Cell(20,7,$e['nombre_usuario'],1,1,'C');
    }

    // Pie de página con fecha
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->Cell(0,10,'Generado: ' . date("d/m/Y H:i"), 0, 0, 'R');

    // Descargar
    $pdf->Output('D', 'estudiantes_seccion.pdf');
}
