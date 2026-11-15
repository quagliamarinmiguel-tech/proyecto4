<?php

class SeccionReportes {
    public static function obtenerNomina(int $sectionId): array {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start(['name' => 'SPM']);
        }

        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            throw new RuntimeException('La sesión de Supabase no está disponible');
        }

        $resultado = seccionModelo::datos_seccion_reporte_supabase($sectionId, 2, $token);
        if (!$resultado['ok'] || empty($resultado['data']['ok'])) {
            throw new RuntimeException('No se pudieron cargar los datos de la sección');
        }

        $data = $resultado['data'];
        $section = $data['section'] ?? [];
        $teacher = $section['teacher'] ?? [];
        $periodCourse = $section['period_course'] ?? [];
        $course = $periodCourse['course'] ?? [];
        $period = $periodCourse['period'] ?? [];
        $sectionName = $section['name'] ?? 'seccion';

        $rows = [];
        foreach ($data['students'] ?? [] as $index => $student) {
            $rows[] = [
                'numero' => $index + 1,
                'ci' => $student['ci'] ?? '',
                'nombre' => trim(($student['last_name'] ?? '') . ' ' . ($student['name'] ?? '')),
            ];
        }

        return [
            'titulo' => 'NÓMINA DE ESTUDIANTES',
            'nombre_archivo' => 'Nomina_' . self::nombreSeguro($sectionName),
            'nombre_archivo_excel' => 'Nomina_Seccion_' . self::nombreSeguro($sectionName),
            'contexto' => [
                'PROFESOR' => trim(($teacher['name'] ?? '') . ' ' . ($teacher['last_name'] ?? '')) ?: 'Sin asignar',
                'CURSO' => ($course['name'] ?? '') . (!empty($course['type']) ? ' (' . $course['type'] . ')' : ''),
                'PERÍODO' => ($period['name'] ?? '') . (!empty($period['type']) ? ' - ' . $period['type'] : ''),
                'SECCIÓN' => $sectionName . (!empty($section['section_day']) ? ' (' . $section['section_day'] . ')' : ''),
            ],
            'columnas' => [
                ['clave' => 'numero', 'titulo' => 'Nº', 'titulo_pdf' => 'N', 'ancho_pdf' => 10, 'ancho_excel' => 8, 'alineacion' => 'C'],
                ['clave' => 'ci', 'titulo' => 'C.I', 'titulo_pdf' => 'CI', 'ancho_pdf' => 30, 'ancho_excel' => 20, 'alineacion' => 'C'],
                ['clave' => 'nombre', 'titulo' => 'Apellidos y Nombres', 'ancho_pdf' => 150, 'ancho_excel' => 45, 'alineacion' => 'L'],
            ],
            'filas' => $rows,
            'resumen' => [
                'etiqueta' => 'TOTAL DE ESTUDIANTES',
                'valor' => count($rows),
            ],
        ];
    }

    public static function emitirPdf(array $reporte): void {
        $fpdfPath = dirname(__DIR__, 2) . '/librerias/fpdf/fpdf.php';
        if (!is_file($fpdfPath)) {
            throw new RuntimeException('No se encontró FPDF en: ' . $fpdfPath);
        }
        require_once $fpdfPath;

        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();
        self::agregarEncabezadoPdf($pdf, $reporte);
        self::agregarTablaPdf($pdf, $reporte);

        $pdf->Output('I', $reporte['nombre_archivo'] . '.pdf');
        exit;
    }

    public static function emitirExcel(array $reporte): void {
        $autoloadPath = dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (!is_file($autoloadPath)) {
            throw new RuntimeException('No se encontró el autoload de composer en: ' . $autoloadPath);
        }
        require_once $autoloadPath;

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Nómina');

        $columns = $reporte['columnas'];
        $lastColumn = self::letraColumna(count($columns));
        $totalWidthPx = 0;
        foreach ($columns as $index => $column) {
            $letter = self::letraColumna($index + 1);
            $width = (float) $column['ancho_excel'];
            $sheet->getColumnDimension($letter)->setWidth($width);
            $totalWidthPx += (int) round($width * 7 + 5);
        }

        $bannerRows = self::agregarCintilloExcel($sheet, $totalWidthPx);
        $row = $bannerRows + 1;
        $bold = ['font' => ['bold' => true]];
        $center = ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];
        $headerFill = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'DCDCF0'],
            ],
        ];

        $sheet->setCellValue("A{$row}", $reporte['titulo']);
        $sheet->mergeCells("A{$row}:{$lastColumn}{$row}");
        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray($bold + $center);
        $row++;

        foreach ($reporte['contexto'] as $label => $value) {
            $sheet->setCellValue("A{$row}", $label . ': ' . $value);
            $sheet->mergeCells("A{$row}:{$lastColumn}{$row}");
            $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray($bold);
            $row++;
        }

        $row++;
        $headerRow = $row;
        foreach ($columns as $index => $column) {
            $letter = self::letraColumna($index + 1);
            $sheet->setCellValue($letter . $row, $column['titulo']);
        }
        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray($headerFill + $bold + $center);
        $row++;
        foreach ($reporte['filas'] as $values) {
            foreach ($columns as $index => $column) {
                $letter = self::letraColumna($index + 1);
                $sheet->setCellValue($letter . $row, $values[$column['clave']] ?? '');
                if ($column['alineacion'] === 'C') {
                    $sheet->getStyle($letter . $row)->applyFromArray($center);
                }
            }
            $row++;
        }

        $lastDataRow = $row - 1;
        if ($lastDataRow >= $headerRow) {
            $sheet->getStyle("A{$headerRow}:{$lastColumn}{$lastDataRow}")
                ->getBorders()->getAllBorders()->setBorderStyle(
                    \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                );
        }

        $row++;
        $summaryValueColumn = $lastColumn;
        $summaryLabelColumn = self::letraColumna(max(1, count($columns) - 1));
        if ($summaryLabelColumn !== $summaryValueColumn) {
            $sheet->mergeCells("A{$row}:{$summaryLabelColumn}{$row}");
        }
        $sheet->setCellValue("A{$row}", $reporte['resumen']['etiqueta']);
        $sheet->setCellValue("{$summaryValueColumn}{$row}", $reporte['resumen']['valor']);
        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray($bold + [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EEEEEE'],
            ],
        ]);
        $sheet->getStyle("{$summaryValueColumn}{$row}")->applyFromArray($center);

        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        error_reporting(0);
        if (ob_get_length()) {
            ob_end_clean();
        }

        $filename = ($reporte['nombre_archivo_excel'] ?? $reporte['nombre_archivo']) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    public static function emitirCsv(array $reporte): void {
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        error_reporting(0);
        if (ob_get_length()) {
            ob_end_clean();
        }

        $filename = $reporte['nombre_archivo'] . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');
        fputcsv($output, array_column($reporte['columnas'], 'titulo'), ';', '"', '');
        foreach ($reporte['filas'] as $row) {
            $values = [];
            foreach ($reporte['columnas'] as $column) {
                $values[] = $row[$column['clave']] ?? '';
            }
            fputcsv($output, $values, ';', '"', '');
        }

        fclose($output);
        exit;
    }

    private static function agregarEncabezadoPdf($pdf, array $reporte): void {
        $banner = self::rutaCintillo();
        if ($banner !== null) {
            $info = @getimagesize($banner);
            if ($info !== false && $info[0] > 0) {
                $width = 216;
                $pdf->Image($banner, 0, 0, $width);
                $pdf->SetY(($width * $info[1] / $info[0]) + 2);
            } else {
                $pdf->Ln(15);
            }
        } else {
            $pdf->Ln(15);
        }

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, self::textoPdf($reporte['titulo']), 0, 1, 'C');
        $pdf->Ln(2);
        $pdf->SetFont('Arial', 'B', 9);
        foreach ($reporte['contexto'] as $label => $value) {
            $pdf->Cell(0, 5, self::textoPdf($label . ': ' . $value), 0, 1);
        }
        $pdf->Ln(3);
    }

    private static function agregarTablaPdf($pdf, array $reporte): void {
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(220, 220, 255);
        foreach ($reporte['columnas'] as $column) {
            $pdf->Cell(
                $column['ancho_pdf'],
                7,
                self::textoPdf($column['titulo_pdf'] ?? $column['titulo']),
                1,
                0,
                $column['alineacion'],
                true
            );
        }
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 9);
        foreach ($reporte['filas'] as $row) {
            foreach ($reporte['columnas'] as $column) {
                $pdf->Cell(
                    $column['ancho_pdf'],
                    6,
                    self::textoPdf((string) ($row[$column['clave']] ?? '')),
                    1,
                    0,
                    $column['alineacion']
                );
            }
            $pdf->Ln();
        }

        $pdf->Ln(4);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(80, 6, self::textoPdf($reporte['resumen']['etiqueta']), 1, 0, 'L', true);
        $pdf->Cell(30, 6, (string) $reporte['resumen']['valor'], 1, 1, 'C', true);
    }

    private static function agregarCintilloExcel($sheet, int $widthPx): int {
        $banner = self::rutaCintillo();
        $bannerRows = 10;
        if ($banner === null) {
            return $bannerRows;
        }

        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Encabezado');
        $drawing->setDescription('Cintillo');
        $drawing->setPath($banner);
        $drawing->setCoordinates('A1');
        $drawing->setResizeProportional(true);
        $drawing->setWidth($widthPx);
        $drawing->setWorksheet($sheet);

        $info = @getimagesize($banner);
        if ($info !== false && $info[0] > 0) {
            $heightPt = ($widthPx * $info[1] / $info[0]) * 0.75;
            for ($row = 1; $row <= $bannerRows; $row++) {
                $sheet->getRowDimension($row)->setRowHeight($heightPt / $bannerRows);
            }
        }

        return $bannerRows;
    }

    private static function rutaCintillo(): ?string {
        $path = mainModel::obtenerRutaImagenLocal('cintillo');
        if (stripos($path, 'http') === 0) {
            $path = parse_url($path, PHP_URL_PATH) ?: '';
        }

        $path = dirname(__DIR__, 2) . '/' . ltrim($path, '/');
        return is_file($path) ? $path : null;
    }

    private static function textoPdf(string $text): string {
        return mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
    }

    private static function nombreSeguro(string $name): string {
        $safe = preg_replace('/[^A-Za-z0-9_-]/', '_', $name);
        return $safe !== '' ? $safe : 'seccion';
    }

    private static function letraColumna(int $index): string {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index);
    }
}