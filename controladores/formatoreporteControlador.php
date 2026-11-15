<?php
    
        require_once "../modelos/mainModel.php";

class formatoControlador {

// HELPERS INTERNOS

    public static function nombreArchivo(string $base, string $ext, bool $conFecha = true): string {
        $limpio = preg_replace('/[^A-Za-z0-9_-]/', '_', $base);
        if ($conFecha) {
            $limpio .= '_' . date('Y-m-d_His');
        }
        return "{$limpio}.{$ext}";
    }
    
    private static function rutaCintillo(): string {
        $r = mainModel::obtenerRutaImagenLocal('cintillo');
        if (stripos($r, 'http') === 0) $r = parse_url($r, PHP_URL_PATH);
        return dirname(__DIR__) . '/' . ltrim($r, '/');
    }

    public static function iso(string $t): string {
        return mb_convert_encoding((string)$t, 'ISO-8859-1', 'UTF-8');
    }

    
    public static function colLetra(int $n): string {
        return chr(64 + $n);
    }

    
    
    private static function formatear($valor, ?string $formato): string {
        if ($valor === null || $valor === '') return '';
        switch ($formato) {
            case 'money':     return '$' . number_format((float)$valor, 0, '.', ',');
            case 'money_dec': return '$' . number_format((float)$valor, 2, '.', ',');
            case 'percent':   return number_format((float)$valor, 1, '.', ',') . '%';
            case 'number':    return number_format((float)$valor, 2, '.', ',');
            case 'int':       return (string)(int)$valor;
            case 'date':
                try { return (new DateTime($valor))->format('d/m/Y'); }
                catch (Exception $e) { return (string)$valor; }
            default: return (string)$valor;
      	}
    }
    

    private static function calcularLineas($pdf, string $texto, float $ancho): int {
        if ($texto === '') return 1;
        $util = $ancho - 2;
        $esp  = $pdf->GetStringWidth(' ');
        $lineas = 1; $actual = 0;
        foreach (explode(' ', $texto) as $pal) {
            $w = $pdf->GetStringWidth($pal);
            if ($w > $util) {
                if ($actual > 0) { $lineas++; $actual = 0; }
                $partes = (int)ceil($w / $util);
                $lineas += $partes - 1;
                $actual = $w - ($partes - 1) * $util;
                continue;
            }
            if ($actual === 0) $actual = $w;
            else {
                $s = $actual + $esp + $w;
                if ($s > $util) { $lineas++; $actual = $w; }
                else $actual = $s;
            }
        }
        return $lineas;
    }

    
// ═══════════════════════════════════════════════════════════
// PDF
// ═══════════════════════════════════════════════════════════

    public static function pdfSubtitulo(FPDF $pdf, string $texto, int $size = 10): void {
        $pdf->SetFont('Arial', 'B', $size);
        $pdf->Cell(0, 6, self::iso($texto), 0, 1);
        $pdf->Ln(1);
    }
    
    
    public static function pdfLeyenda(FPDF $pdf, array $items): void {
        $pdf->SetFont('Arial', 'I', 6);
        $n = count($items);
        foreach ($items as $i => $it) {
            $pdf->SetFillColor(...($it['bg'] ?? [200,200,200]));
            $pdf->Cell(4, 3, '', 1, 0, 'C', true);
            $pdf->Cell($it['ancho'] ?? 25, 3, self::iso(' ' . ($it['label'] ?? '')), 0, ($i === $n - 1 ? 1 : 0), 'L');
        }
    }
    

    public static function pdfSinDatos(FPDF $pdf, string $mensaje = 'No hay datos para mostrar.'): void {
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->Cell(0, 8, self::iso($mensaje), 1, 1, 'C');
    }
    
    
// Inicia el PDF con cintillo y título. Devuelve el objeto FPDF listo.
    public static function pdfIniciar(string $titulo, string $orientacion = 'P'): FPDF {
        $path = dirname(__DIR__) . "/librerias/fpdf/fpdf.php";
        if (!file_exists($path)) die("No se encontró FPDF");
        require_once $path;

        $pdf = new FPDF($orientacion, 'mm', 'Letter');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        $ruta = self::rutaCintillo();
        if (file_exists($ruta)) {
            $info = @getimagesize($ruta);
            if ($info !== false) {
                $ratio = $info[1] / $info[0];
                $w = $orientacion === 'L' ? 279 : 216;
                $h = $w * $ratio;
                $pdf->Image($ruta, 0, 0, $w);
                $pdf->SetY($h + 3);
            } else $pdf->Ln(15);
        } else $pdf->Ln(15);

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, self::iso($titulo), 0, 1, 'C');
        $pdf->Ln(2);

        return $pdf;
    }

    /**
     * Dibuja líneas de contexto. $lineas puede ser:
     *   ['PERÍODO: 2026-1', 'CURSO: Inglés Básico']
     * o líneas con título en negrita:
     *   [['bold' => 'PROFESOR:', 'text' => 'Juan Pérez'], ...]
     */

    public static function pdfContexto(FPDF $pdf, array $lineas, int $size = 9): void {
        foreach ($lineas as $linea) {
            if (is_array($linea)) {
                // Acepta dos formatos:
                //   ['bold' => 'PROFESOR:', 'text' => 'Juan Pérez']
                //   ['PROFESOR:', 'Juan Pérez']   (array de dos elementos)
                $bold = $linea['bold'] ?? ($linea[0] ?? '');
                $text = $linea['text'] ?? ($linea[1] ?? '');

                $pdf->SetFont('Arial', 'B', $size);
                $pdf->Cell($pdf->GetStringWidth(self::iso($bold)) + 1, 5, self::iso($bold), 0, 0);
                $pdf->SetFont('Arial', '', $size);
                $pdf->Cell(0, 5, self::iso($text), 0, 1);
            } else {
                $pdf->SetFont('Arial', '', $size);
                $pdf->Cell(0, 5, self::iso($linea), 0, 1);
            }
        }
        $pdf->Ln(3);
    }
    
    
    
    
    /**
     * Tabla genérica en PDF.
     *
     * $columnas = [
     *   ['key'=>'name','label'=>'Nombre','ancho'=>60,'align'=>'L','wrap'=>true,'formato'=>'date|money|percent|int|null'],
     * ]
     *
     * $config = [
     *   'alto_header' => 8,
     *   'alto_fila'   => 6,
     *   'alto_linea'  => 4,
     *   'colores' => [
     *      ['cond'=>fn($r)=>$r['estado']==='Pagado','bg'=>[40,167,69],'fg'=>[255,255,255]],
     *   ],
     *   'totales' => [
     *      ['label'=>'TOTAL','colspan'=>1,'valor'=>'$1,200','align'=>'C'],
     *   ],
     * ]
     */


        public static function pdfTabla(FPDF $pdf, array $columnas, array $filas, array $config = []): void {
            $hH    = $config['alto_header'] ?? 8;
            $hF    = $config['alto_fila']   ?? 6;
            $hL    = $config['alto_linea']  ?? 4;
            $colores = $config['colores']   ?? [];

            // Header
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetFillColor(220, 220, 255);
            foreach ($columnas as $c) {
                $pdf->Cell($c['ancho'], $hH, self::iso($c['label']), 1, 0, 'C', true);
            }
            $pdf->Ln();

            // Filas
            $pdf->SetFont('Arial', '', 8);
            foreach ($filas as $fila) {
                $wrap = false;
                foreach ($columnas as $c) if (!empty($c['wrap'])) { $wrap = true; break; }

                // Color condicional
                $bg = null; $fg = null;
                foreach ($colores as $r) {
                    if ($r['cond']($fila)) { $bg = $r['bg']; $fg = $r['fg'] ?? null; break; }
                }

                
                if (!$wrap) {
                    foreach ($columnas as $c) {
                        $val = self::formatear($fila[$c['key']] ?? '', $c['formato'] ?? null);

                        // Color por celda (prioridad) o color de fila
                        $cellBg = $bg;
                        $cellFg = $fg;
                        if (isset($config['color_cell'])) {
                            $custom = $config['color_cell']($fila, $c);
                            if ($custom !== null) {
                                $cellBg = $custom['bg'] ?? null;
                                $cellFg = $custom['fg'] ?? null;
                            }
                        }

                        if ($cellBg) $pdf->SetFillColor(...$cellBg);
                        if ($cellFg) $pdf->SetTextColor(...$cellFg);
                        $pdf->Cell($c['ancho'], $hF, self::iso($val), 1, 0, $c['align'] ?? 'L', (bool)$cellBg);
                    }
                    $pdf->SetTextColor(0,0,0);
                    $pdf->Ln();
                }
                
                else {
                    // ── Fila con wrap ──
                    $x0 = $pdf->GetX(); $y0 = $pdf->GetY();

                    // Calcular alto real de la fila según la columna wrap más alta
                    $maxL = 1;
                    foreach ($columnas as $c) {
                        if (empty($c['wrap'])) continue;
                        $val = self::iso(self::formatear($fila[$c['key']] ?? '', $c['formato'] ?? null));
                        $maxL = max($maxL, self::calcularLineas($pdf, $val, $c['ancho']));
                    }
                    $hTotal = $maxL * $hL;

                    // FIX 4: bordes con relleno condicional
                    if ($bg) $pdf->SetFillColor(...$bg);
                    foreach ($columnas as $c) {
                        $pdf->Cell($c['ancho'], $hTotal, '', 1, 0, $c['align'] ?? 'L', (bool)$bg);
                    }
                    $pdf->Ln();

                    // FIX 4 + fix de posicionamiento: usar offset X acumulado en vez de GetX()
                    if ($fg) $pdf->SetTextColor(...$fg);
                    $curX = $x0;
                    foreach ($columnas as $c) {
                        $val = self::iso(self::formatear($fila[$c['key']] ?? '', $c['formato'] ?? null));
                        if (!empty($c['wrap'])) {
                            $pdf->SetXY($curX, $y0);
                            $pdf->MultiCell($c['ancho'], $hL, $val, 0, $c['align'] ?? 'L');
                        } else {
                            $off = ($hTotal - $hL) / 2;
                            $pdf->SetXY($curX, $y0 + $off);
                            $pdf->Cell($c['ancho'], $hL, $val, 0, 0, $c['align'] ?? 'L');
                        }
                        $curX += $c['ancho'];
                    }
                    $pdf->SetTextColor(0,0,0);
                    $pdf->SetXY($x0, $y0 + $hTotal);
                }
            }

            // Totales (opcional)
            if (!empty($config['totales'])) {
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetFillColor(240, 240, 240);

                // FIX 5: ancho útil calculado a partir de las columnas, no 196 hardcodeado
                $anchoUtil = 0;
                foreach ($columnas as $c) $anchoUtil += $c['ancho'];

                foreach ($config['totales'] as $t) {
                    $span  = $t['colspan'] ?? 1;
                    $ancho = 0;
                    for ($i = 0; $i < $span; $i++) $ancho += $columnas[$i]['ancho'];
                    $pdf->Cell($ancho, 7, self::iso($t['label']), 1, 0, 'R', true);
                    $pdf->Cell($anchoUtil - $ancho, 7, self::iso($t['valor'] ?? ''), 1, 1, $t['align'] ?? 'C', true);
                }
            }
        }
    

    public static function pdfSalida(FPDF $pdf, string $nombre): void {
        $pdf->Output('I', $nombre);
        exit;
    }

    
    
    
// ═══════════════════════════════════════════════════════════
// EXCEL
// ═══════════════════════════════════════════════════════════

    public static function excelSubtitulo($sheet, int $fila, string $texto, int $numCols, int $size = 11): int {
        $colFin = self::colLetra($numCols);
        $sheet->setCellValue("A{$fila}", $texto);
        $sheet->mergeCells("A{$fila}:{$colFin}{$fila}");
        $sheet->getStyle("A{$fila}")->applyFromArray(['font'=>['bold'=>true,'size'=>$size]]);
        return $fila + 1;
    }
    
    public static function excelAlturaFilas($sheet, int $filaIni, int $filaFin, float $altura): void {
        for ($r = $filaIni; $r <= $filaFin; $r++) {
            $sheet->getRowDimension($r)->setRowHeight($altura);
        }
    }
    
 
    public static function excelSinDatos($sheet, int $fila, string $mensaje, int $numCols): int {
        $colFin = self::colLetra($numCols);
        $sheet->setCellValue("A{$fila}", $mensaje);
        $sheet->mergeCells("A{$fila}:{$colFin}{$fila}");
        $sheet->getStyle("A{$fila}")->getFont()->setItalic(true);
        return $fila + 1;
    }
    
    
    
    /**
     * Inicia el Excel con cintillo. Devuelve [spreadsheet, sheet, filaActual].
     * $anchosPorCol = ['A'=>20,'B'=>30,...] (opcional)
     */
    public static function excelIniciar(
        string $titulo,
        string $hoja,
        int $numCols = 6,
        ?int $anchoTotalPx = null
    ): array {
        $path = dirname(__DIR__) . "/vendor/autoload.php";
        if (!file_exists($path)) die("No se encontró autoload");
        require_once $path;

        $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sh = $ss->getActiveSheet();
        $sh->setTitle(substr($hoja, 0, 31));

        // Si no se pasa anchoTotalPx, estimarlo con 15 por columna
        if ($anchoTotalPx === null) {
            $anchoTotalPx = 0;
            for ($i = 1; $i <= $numCols; $i++) {
                $anchoTotalPx += (int)round(15 * 7 + 5);
            }
        }

        // Cintillo
        $ruta = self::rutaCintillo();
        if (file_exists($ruta)) {
            $info = @getimagesize($ruta);
            if ($info !== false) {
                $ratio = $info[1] / $info[0];
                $altoCintilloPt = ($anchoTotalPx * $ratio) * 0.75;

                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setPath($ruta);
                $drawing->setCoordinates('A1');
                $drawing->setResizeProportional(true);
                $drawing->setWidth($anchoTotalPx);
                $drawing->setWorksheet($sh);

                $filas = 10;
                $altoFila = $altoCintilloPt / $filas;
                for ($i = 1; $i <= $filas; $i++) {
                    $sh->getRowDimension($i)->setRowHeight($altoFila);
                }
                $fila = $filas + 1;
            } else $fila = 1;
        } else $fila = 1;

        $colFin = self::colLetra($numCols);
        $sh->setCellValue("A{$fila}", $titulo);
        $sh->mergeCells("A{$fila}:{$colFin}{$fila}");
        $sh->getStyle("A{$fila}")->applyFromArray(['font'=>['bold'=>true,'size'=>14]]);
        $sh->getStyle("A{$fila}")->getAlignment()->setHorizontal('center');
        $fila += 2;

        return ['spreadsheet' => $ss, 'sheet' => $sh, 'fila' => $fila];
    }
    
    /**
     * Calcula el ancho total en píxeles dado un array de columnas y sus anchos.
     * Debe llamarse ANTES de excelIniciar para pasarle el ancho correcto.
     */
    public static function excelAnchoTotalPx(array $columnas, array $anchos = []): int {
        $total = 0;
        foreach ($columnas as $c) {
            $w = $anchos[$c['key']] ?? $c['ancho'] ?? 15;
            $total += (int)round($w * 7 + 5);
        }
        return $total;
    }
    
    
    public static function excelContexto($sheet, int $fila, array $lineas, int $numCols): int {
        $colFin = self::colLetra($numCols);
        foreach ($lineas as $l) {
            $sheet->setCellValue("A{$fila}", is_array($l) ? ($l['bold'] . ' ' . $l['text']) : $l);
            $sheet->mergeCells("A{$fila}:{$colFin}{$fila}");
            if (is_array($l) || strpos($l, ':') !== false) {
                $sheet->getStyle("A{$fila}")->applyFromArray(['font'=>['bold'=>true]]);
            }
            $fila++;
        }
        return $fila + 1;
    }

    /**
     * Tabla en Excel. Devuelve la siguiente fila.
     * $anchos = ['key1'=>20, 'key2'=>15, ...] en unidades Excel.
     */

    public static function excelTabla($sheet, int $fila, array $columnas, array $filas, array $anchos = [], array $config = []): int {
        $colFin = self::colLetra(count($columnas));

        foreach ($columnas as $i => $c) {
            $letra = self::colLetra($i + 1);
            $ancho = $anchos[$c['key']] ?? $c['ancho'] ?? 15;
            $sheet->getColumnDimension($letra)->setWidth($ancho);
        }

        $bgBlue = ['fill'=>['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startColor'=>['rgb'=>'DCDCFF']]];
        $center = ['alignment'=>['horizontal'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];
        $bold   = ['font'=>['bold'=>true]];

        foreach ($columnas as $i => $c) {
            $sheet->setCellValue(self::colLetra($i + 1) . $fila, $c['label']);
        }
        $sheet->getStyle("A{$fila}:{$colFin}{$fila}")->applyFromArray($bgBlue);
        $sheet->getStyle("A{$fila}:{$colFin}{$fila}")->applyFromArray($center);
        $sheet->getStyle("A{$fila}:{$colFin}{$fila}")->applyFromArray($bold);
        $filaIni = $fila;
        $fila++;

        foreach ($filas as $row) {
            foreach ($columnas as $i => $c) {
                $letra = self::colLetra($i + 1);
                $sheet->setCellValue($letra . $fila, self::formatear($row[$c['key']] ?? '', $c['formato'] ?? null));

                if (!empty($c['wrap'])) {
                    $sheet->getStyle("{$letra}{$fila}")->getAlignment()->setWrapText(true);
                }
                if (($c['align'] ?? '') === 'C') {
                    $sheet->getStyle("{$letra}{$fila}")->applyFromArray($center);
                }

                
                if (isset($config['color_cell'])) {
                    $custom = $config['color_cell']($row, $c);
                    if ($custom !== null) {
                        $style = [];
                        if (!empty($custom['bg'])) {
                            $style['fill'] = ['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                              'startColor'=>['rgb'=>$custom['bg']]];
                        }
                        if (!empty($custom['fg']) || !empty($custom['bold'])) {
                            $fontStyle = [];
                            if (!empty($custom['fg']))   $fontStyle['color'] = ['rgb'=>$custom['fg']];
                            if (!empty($custom['bold'])) $fontStyle['bold']  = true;
                            $style['font'] = $fontStyle;
                        }
                        if ($style) $sheet->getStyle("{$letra}{$fila}")->applyFromArray($style);
                    }
                }
                
            }
            $fila++;
        }

        $sheet->getStyle("A{$filaIni}:{$colFin}" . ($fila - 1))
              ->getBorders()->getAllBorders()->setBorderStyle(
                  \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
              );

        return $fila;
    }
    

    public static function excelSalida($spreadsheet, string $nombre): void {
        ini_set('display_errors', 0);
        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$nombre\"");
        header('Cache-Control: max-age=0');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    
    
// ═══════════════════════════════════════════════════════════
// CSV
// ═══════════════════════════════════════════════════════════

    public static function csvSubtitulo($out, string $texto): void {
        fputcsv($out, [$texto], ';', '"', '');
    }
    
    
    public static function csvSinDatos($out, string $mensaje): void {
        fputcsv($out, [$mensaje], ';', '"', '');
    }
    
    
    
    public static function csvIniciar(string $nombre) {
        ini_set('display_errors', 0);
        if (ob_get_length()) ob_end_clean();
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"$nombre\"");
        header('Cache-Control: max-age=0');
        echo "\xEF\xBB\xBF";
        return fopen('php://output', 'w');
    }

    public static function csvContexto($out, array $lineas): void {
        foreach ($lineas as $l) {
            fputcsv($out, [is_array($l) ? $l['bold'] . ' ' . $l['text'] : $l], ';', '"', '');
        }
        fputcsv($out, [], ';', '"', '');
    }

    
    public static function csvTabla($out, array $columnas, array $filas): void {
        fputcsv($out, array_column($columnas, 'label'), ';', '"', '');
        foreach ($filas as $row) {
            $linea = [];
            foreach ($columnas as $c) {
                $val = $row[$c['key']] ?? '';
                if (isset($c['render']) && is_callable($c['render'])) {
                    $linea[] = $c['render']($val, $row);
                } else {
                    $linea[] = self::formatear($val, $c['formato'] ?? null);
                }
            }
            fputcsv($out, $linea, ';', '"', '');
        }
    }
    
    
    public static function csvSalida($out): void {
        fclose($out);
        exit;
    }
    

        /**
     * Dibuja un resumen clave-valor en el PDF. para el caso especifico del pdf de notas
     * $filas = [
     *   ['label'=>'Cursantes',  'valores'=>[30, '100']],
     *   ['label'=>'Aprobados',  'valores'=>[25, '83.3']],
     * ]
     * $anchos = [80, 30, 30]  (el primer ancho es del label)
     * $config = ['header'=>['RESUMEN','N','%']]  (opcional)
     */    
    public static function pdfResumen(FPDF $pdf, array $filas, array $anchos, array $config = []): void {
        $alto      = $config['alto']       ?? 6;
        $fill      = $config['fill']       ?? null;       // [r,g,b] o null
        $colorCell = $config['color_cell'] ?? null;

        if (!empty($config['header'])) {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetFillColor(220, 220, 255);
            foreach ($config['header'] as $i => $h) {
                $pdf->Cell($anchos[$i], $alto, self::iso($h), 1, 0, 'C', true);
            }
            $pdf->Ln();
        }

        $pdf->SetFont('Arial', '', 9);
        foreach ($filas as $fila) {
            // ── Celda label (col 0) ──
            $cellBg = $fill;
            $cellFg = null;
            if ($colorCell) {
                $custom = $colorCell($fila, ['key'=>'label', 'idx'=>0]);
                if ($custom) {
                    if (isset($custom['bg'])) $cellBg = $custom['bg'];
                    if (isset($custom['fg'])) $cellFg = $custom['fg'];
                }
            }
            if ($cellBg) $pdf->SetFillColor(...$cellBg);
            if ($cellFg) $pdf->SetTextColor(...$cellFg);
            $pdf->Cell($anchos[0], $alto, self::iso($fila['label']), 1, 0, 'L', (bool)$cellBg);
            $pdf->SetTextColor(0,0,0);

            // ── Celdas de valores (col 1..N) ──
            foreach ($fila['valores'] as $i => $v) {
                $cellBg = $fill;
                $cellFg = null;
                if ($colorCell) {
                    $custom = $colorCell($fila, ['key'=>"v$i", 'idx'=>$i + 1]);
                    if ($custom) {
                        if (isset($custom['bg'])) $cellBg = $custom['bg'];
                        if (isset($custom['fg'])) $cellFg = $custom['fg'];
                    }
                }
                if ($cellBg) $pdf->SetFillColor(...$cellBg);
                if ($cellFg) $pdf->SetTextColor(...$cellFg);
                $pdf->Cell($anchos[$i + 1], $alto, self::iso($v), 1, 0, 'C', (bool)$cellBg);
                $pdf->SetTextColor(0,0,0);
            }
            $pdf->Ln();
        }
    }
    
    
    
    
    
    


    /**
     * Dibuja un pie de página con ubicación+fecha y/o firmas.
     *
     * $config = [
     *   'ubicacion'  => 'Valle de la Pascua',   // null para omitir
     *   'con_fecha'  => true,                    // por defecto true
     *   'firmas'     => [
     *       ['nombre' => 'Juan Pérez', 'cargo' => 'Profesor de la asignatura'],
     *   ],
     *   'espacio_antes_firmas'    => 15,
     *   'espacio_antes_ubicacion' => 5,
     * ];
     */
    public static function pdfFooter(FPDF $pdf, array $config = []): void {
        $ubicacion = $config['ubicacion'] ?? null;
        $conFecha  = $config['con_fecha'] ?? true;
        $firmas    = $config['firmas']    ?? [];
        $espFirmas = $config['espacio_antes_firmas']    ?? 15;
        $espUbic   = $config['espacio_antes_ubicacion'] ?? 5;

        // ¿Cabe en la página actual? Si no, salto de página
        $alturaEstimada = 0;
        if (!empty($firmas))     $alturaEstimada += $espFirmas + 20;
        if ($ubicacion !== null) $alturaEstimada += $espUbic + 10;

        if ($pdf->GetY() + $alturaEstimada > $pdf->GetPageHeight() - 15) {
            $pdf->AddPage();
        }

        // ── Firmas ──
        if (!empty($firmas)) {
            $pdf->Ln($espFirmas);

            $anchoPagina = $pdf->GetPageWidth() - 20;   // márgenes 10+10
            $n = count($firmas);
            $anchoFirma = $anchoPagina / $n;

            // Línea
            foreach ($firmas as $i => $f) {
                $x = 10 + ($i * $anchoFirma);
                $pdf->SetXY($x, $pdf->GetY());
                $pdf->Cell($anchoFirma, 5, '_________________________________', 0, 0, 'C');
            }
            $pdf->Ln(6);

            // Nombre
            $pdf->SetFont('Arial', 'B', 9);
            foreach ($firmas as $i => $f) {
                $x = 10 + ($i * $anchoFirma);
                $pdf->SetXY($x, $pdf->GetY());
                $pdf->Cell($anchoFirma, 5, self::iso($f['nombre'] ?? ''), 0, 0, 'C');
            }
            $pdf->Ln(5);

            // Cargo
            $pdf->SetFont('Arial', '', 8);
            foreach ($firmas as $i => $f) {
                $x = 10 + ($i * $anchoFirma);
                $pdf->SetXY($x, $pdf->GetY());
                $pdf->Cell($anchoFirma, 5, self::iso($f['cargo'] ?? ''), 0, 0, 'C');
            }
            $pdf->Ln(5);
        }

        // ── Ubicación + fecha ──
        if ($ubicacion !== null) {
            $pdf->Ln($espUbic);
            $texto = $ubicacion;
            if ($conFecha) {
                $meses = ['01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio',
                          '07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'];
                $fecha = date('d') . ' de ' . $meses[date('m')] . ' de ' . date('Y');
                $texto .= ', ' . $fecha;
            }
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(0, 5, self::iso($texto), 0, 1, 'R');
        }
    }




    public static function excelFooter($sheet, int $fila, array $config = []): int {
        $ubicacion = $config['ubicacion'] ?? null;
        $conFecha  = $config['con_fecha'] ?? true;
        $firmas    = $config['firmas']    ?? [];
        $numCols   = $config['num_cols']  ?? 4;

        $colFin = self::colLetra($numCols);
        $center = ['alignment'=>['horizontal'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];
        $right  = ['alignment'=>['horizontal'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]];
        $bold   = ['font'=>['bold'=>true]];

        // Firmas
        if (!empty($firmas)) {
            $fila += 3;
            foreach ($firmas as $f) {
                $sheet->setCellValue("A{$fila}", '_________________________________');
                $sheet->mergeCells("A{$fila}:{$colFin}{$fila}");
                $sheet->getStyle("A{$fila}:{$colFin}{$fila}")->applyFromArray($center);
                $fila++;

                $sheet->setCellValue("A{$fila}", $f['nombre'] ?? '');
                $sheet->mergeCells("A{$fila}:{$colFin}{$fila}");
                $sheet->getStyle("A{$fila}:{$colFin}{$fila}")->applyFromArray($bold);
                $sheet->getStyle("A{$fila}:{$colFin}{$fila}")->applyFromArray($center);
                $fila++;

                $sheet->setCellValue("A{$fila}", $f['cargo'] ?? '');
                $sheet->mergeCells("A{$fila}:{$colFin}{$fila}");
                $sheet->getStyle("A{$fila}:{$colFin}{$fila}")->applyFromArray($center);
                $fila += 2;
            }
        }

        // Ubicación + fecha
        if ($ubicacion !== null) {
            $texto = $ubicacion;
            if ($conFecha) {
                $meses = ['01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio',
                          '07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'];
                $fecha = date('d') . ' de ' . $meses[date('m')] . ' de ' . date('Y');
                $texto .= ', ' . $fecha;
            }
            $fila++;
            $sheet->setCellValue("A{$fila}", $texto);
            $sheet->mergeCells("A{$fila}:{$colFin}{$fila}");
            $sheet->getStyle("A{$fila}:{$colFin}{$fila}")->applyFromArray($right);
            $fila++;
        }

        return $fila;
    }

    
    
    

// a partir de aqui empiezan las funciones de calculo de notas y pagos q son logica de negocio

    /**
     * Calcula la nota de UN estudiante en una sección.
     *
     * @param array $evals        Evaluaciones (cada una con id, percentage)
     * @param array $scoresMap    Mapa "regId_evalId" => score
     * @param int   $regId        registration_id
     * @param bool  $seccionCompleta  Si los porcentajes suman 100
     * @param int   $maxEval      Cuántas evaluaciones considerar
     */
    public static function calcularNotaEstudiante(
        array $evals,
        array $scoresMap,
        int $regId,
        bool $seccionCompleta,
        int $maxEval = 6
    ): array {
        $notaFinal = 0.0;
        $sumaPorc  = 0.0;
        $presentadas = 0;
        $noPresentadas = 0;
        $detalle = [];   // [ 't0'=>score, 'p0'=>aporte, ... ]

        for ($i = 0; $i < $maxEval; $i++) {
            $ev = $evals[$i] ?? null;
            if ($ev === null) {
                $detalle["t$i"] = '';
                $detalle["p$i"] = '';
                continue;
            }

            $evalId = (int) $ev['id'];
            $pct    = (float) ($ev['percentage'] ?? 0);
            $score  = $scoresMap[$regId . '_' . $evalId] ?? null;

            if ($score === null) {
                $detalle["t$i"] = 'NP';
                $detalle["p$i"] = 0;
                $sumaPorc += $pct;
                $noPresentadas++;
            } else {
                $aporte = $score * $pct / 100;
                $detalle["t$i"] = $score;
                $detalle["p$i"] = round($aporte, 1);
                $notaFinal += $aporte;
                $sumaPorc  += $pct;
                $presentadas++;
            }
        }

        // Nota definitiva solo si la sección está completa
        if ($seccionCompleta) {
            $def = ($notaFinal - floor($notaFinal) >= 0.5) ? ceil($notaFinal) : floor($notaFinal);
        } else {
            $def = null;
        }

        return [
            'detalle'        => $detalle,
            'nota_final'     => round($notaFinal, 2),
            'suma_porc'      => round($sumaPorc, 1),
            'definitiva'     => $def,
            'presentadas'    => $presentadas,
            'no_presentadas' => $noPresentadas,
        ];
    }

    /**
     * Calcula el resumen de aprobados/reprobados de una sección.
     */
    public static function calcularResumenNotas(
        array $definitivas,
        bool $seccionCompleta
    ): array {
        $total = count($definitivas);

        if (!$seccionCompleta) {
            return [
                'total' => $total,
                'aprobados' => 0,
                'reprobados' => 0,
                'pct_aprobados' => 0,
                'pct_reprobados' => 0,
            ];
        }

        $aprobados = count(array_filter($definitivas, fn($v) => $v !== null && $v >= 10));
        $reprobados = $total - $aprobados;

        return [
            'total'          => $total,
            'aprobados'      => $aprobados,
            'reprobados'     => $reprobados,
            'pct_aprobados'  => $total > 0 ? round(($aprobados / $total) * 100, 1) : 0,
            'pct_reprobados' => $total > 0 ? round(($reprobados / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Construye TODAS las filas de estudiantes con sus notas ya calculadas.
     * Devuelve [ 'filas' => [...], 'definitivas' => [...], 'resumen' => [...] ]
     */
    public static function construirFilasNotas(
        array $students,
        array $evals,
        array $scoresMap,
        bool $seccionCompleta,
        int $maxEval = 6
    ): array {
        $filas = [];
        $definitivas = [];

        foreach ($students as $i => $est) {
            $regId = (int) $est['registration_id'];
            $calc  = self::calcularNotaEstudiante($evals, $scoresMap, $regId, $seccionCompleta, $maxEval);

            $fila = [
                'n'      => $i + 1,
                'ci'     => mainModeL::decryption($est['ci'] ?? ''),
                'nombre' => trim(($est['last_name'] ?? '') . ' ' . ($est['name'] ?? '')),
                'acum'   => $calc['suma_porc'] . '%',
                'calif'  => $calc['nota_final'],
                'def'    => $calc['definitiva'] ?? '',
            ] + $calc['detalle'];

            $filas[] = $fila;
            $definitivas[] = $calc['definitiva'];
        }

        return [
            'filas'       => $filas,
            'definitivas' => $definitivas,
            'resumen'     => self::calcularResumenNotas($definitivas, $seccionCompleta),
        ];
    }


    
    
// PAGOS

    /**
     * Calcula el estado de pago por concepto de UN estudiante.
     * Devuelve [ 'filas' => [...], 'total_esperado', 'total_cobrado', 'total_deuda' ]
     */
    public static function calcularPagosEstudiante(
        array $periodCosts,
        array $pagosMap,
        ?string $hoy = null
    ): array {
        $hoy = $hoy ?? date('Y-m-d');
        $filas = [];
        $totalEsperado = 0;
        $totalCobrado  = 0;

        foreach ($periodCosts as $pc) {
            $motive = $pc['motive'];
            $label  = preg_match('/^pago_encuentro(\d+)$/', $motive, $m)
                ? 'Encuentro ' . $m[1]
                : ucfirst(str_replace('_', ' ', str_replace('pago_', '', $motive)));

            $price   = (int) ($pc['price'] ?? 0);
            $dueDate = $pc['due_date'] ?? null;
            $pagado  = (int) ($pagosMap[$motive] ?? 0);
            $deuda   = max(0, $price - $pagado);

            $totalEsperado += $price;
            $totalCobrado  += min($pagado, $price);

            if ($pagado >= $price) {
                $estado = 'Pagado';
            } elseif ($dueDate !== null && $hoy <= $dueDate) {
                $estado = 'Pendiente';
            } else {
                $estado = 'Atrasado';
            }

            $filas[] = [
                'concepto' => $label,
                'precio'   => $price,
                'pagado'   => $pagado,
                'deuda'    => $deuda,
                'vence'    => $dueDate,
                'estado'   => $estado,
            ];
        }

        return [
            'filas'          => $filas,
            'total_esperado' => $totalEsperado,
            'total_cobrado'  => $totalCobrado,
            'total_deuda'    => $totalEsperado - $totalCobrado,
        ];
    }

    /**
     * Resumen agregado de pagos de una sección (todos los estudiantes).
     */
    public static function calcularResumenPagosSeccion(
        array $students,
        array $periodCosts,
        array $pagosMap
    ): array {
        $totalEsperado = 0;
        $totalCobrado  = 0;

        foreach ($students as $est) {
            $regId = (int) $est['registration_id'];
            $pagosEst = [];
            foreach ($periodCosts as $pc) {
                $motive = $pc['motive'];
                $pagosEst[$motive] = (int) ($pagosMap[$regId][$motive] ?? 0);
            }
            $calc = self::calcularPagosEstudiante($periodCosts, $pagosEst);
            $totalEsperado += $calc['total_esperado'];
            $totalCobrado  += $calc['total_cobrado'];
        }

        $deuda = $totalEsperado - $totalCobrado;
        $pct   = $totalEsperado > 0 ? round(($totalCobrado / $totalEsperado) * 100, 1) : 0;

        return [
            'total_esperado' => $totalEsperado,
            'total_cobrado'  => $totalCobrado,
            'total_deuda'    => $deuda,
            'pct_cobrado'    => $pct,
        ];
    }

    
    
    
}
?>