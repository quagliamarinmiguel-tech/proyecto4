<?php
require_once "../controladores/seccionControlador2.php";

$curso_vigente_id = $_GET['curso_vigente'] ?? 0;
$seccion_letra = $_GET['seccion'] ?? '';

define("SERVERURL", "http://www.cecilioceproca.wuaze.com/");
$logo4 = SERVERURL . "vistas/assets/img/cexcel4.png";

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

// Headers para exportar como Excel (HTML)
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=Notas_Seccion_{$datos['seccion']['nombre_seccion']}_{$datos['seccion']['anio_cohorte']}.xls");
header("Pragma: no-cache");
header("Expires: 0");
echo "\xEF\xBB\xBF"; // BOM UTF-8

// Función auxiliar para calcular ponderado (robusta ante índices desalineados)
function calcularPonderado(array $estEvaluaciones, array $evaluacionesDef) : float {
    $total = 0.0;
    foreach ($estEvaluaciones as $k => $ev) {
        if (!isset($evaluacionesDef[$k]['porcentaje'])) continue;
        $peso = floatval($evaluacionesDef[$k]['porcentaje']) / 100.0;
        $puntaje = isset($ev['puntaje']) ? floatval($ev['puntaje']) : 0.0;
        $total += $puntaje * $peso;
    }
    return $total;
}

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
echo '<html><head>';
echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>';
echo '<style>
body,div,table,thead,tbody,tfoot,tr,th,td,p {
  font-family: Calibri;
  font-size: x-small;
}
</style>';
echo '</head><body>';
echo '<table border="1" cellspacing="0" cellpadding="4">';

// Imagen centrada
// Encabezado institucional
echo '<tr><td colspan="3" align="left"><strong>CENTRO EDUCATIVO PROFESIONAL "CECILIO ACOSTA"</strong></td>';
foreach ($datos['evaluaciones'] as $i => $eval) {
  echo '<td colspan="2" align="center"><strong>Estrategia ' . ($i + 1) . '</strong></td>';
}
echo '<td colspan="3" align="center"><strong>Final</strong></td></tr>';

echo '<tr><td colspan="3" align="left"><strong>VALLE DE LA PASCUA ESTADO-GUÁRICO  RIF-J-503182261</strong></td>';
foreach ($datos['evaluaciones'] as $eval) {
  echo '<td colspan="2" align="center">' . htmlspecialchars($eval['tipo_evaluacion']) . '</td>';
}
echo '<td colspan="3"></td></tr>';
// Encabezados informativos (Estrategias / Final)





//echo '<tr><td colspan="3"></td>';
//foreach ($datos['evaluaciones'] as $i => $eval) {
// echo '<td colspan="2" align="center"><strong>Estrategia ' . ($i + 1) . '</strong></td>';
//}





//echo '<td colspan="3" align="center"><strong>Final</strong></td></tr>';

//echo '<tr><td colspan="3"></td>';
//foreach ($datos['evaluaciones'] as $eval) {
//  echo '<td colspan="2" align="center">' . htmlspecialchars($eval['tipo_evaluacion']) . '</td>';
//}
//echo '<td colspan="3"></td></tr>';

echo '<tr><td colspan="3" align="left"><strong>PROFESOR:</strong> ' . htmlspecialchars($datos['seccion']['nombre_profesor'] . ' ' . $datos['seccion']['apellido_profesor']) . '</td>';
foreach ($datos['evaluaciones'] as $eval) {
  echo '<td colspan="2" align="center">' . htmlspecialchars($eval['fecha_evaluacion'] ?? '-') . '</td>';
}
echo '<td colspan="3"></td></tr>';

echo '<tr><td colspan="3" align="left"><strong>CURSO:</strong> ' . htmlspecialchars($datos['seccion']['nombre_curso']) . '</td>';
foreach ($datos['evaluaciones'] as $eval) {
  echo '<td colspan="2" align="center">' . htmlspecialchars($eval['porcentaje']) . '%</td>';
}
echo '<td colspan="3"></td></tr>';

echo '<tr><td colspan="3" align="left"><strong>COHORTE:</strong> ' . htmlspecialchars($datos['seccion']['tipo_cohorte'] . ' ' . $datos['seccion']['numero_cohorte'] . '/' . $datos['seccion']['anio_cohorte'] . ' | SECCIÓN: ' . $datos['seccion']['nombre_seccion']) . '</td>';
foreach ($datos['evaluaciones'] as $eval) {
  echo '<td colspan="2" align="center">OBJ ' . htmlspecialchars($eval['obj']) . '</td>';
}
echo '<td colspan="3"></td></tr>';

// Encabezado de columnas (con anchos ajustados)
echo '<tr>
  <th style="width:60px">Nº</th>
  <th style="width:100px">C.I</th>
  <th style="width:300px">Apellidos y Nombres</th>';
foreach ($datos['evaluaciones'] as $eval) {
  echo '<th style="background-color:#FFF778">T</th><th style="background-color:#F54927">%</th>';
}
echo '<th>Acumulado</th><th>Calificación</th><th>Redondeada</th></tr>';


$notas_redondeadas = [];

// Filas de estudiantes con cálculo ponderado seguro
foreach ($datos['estudiantes'] as $i => $est) {
    echo '<tr>';
    echo '<td style="width:60px">' . ($i + 1) . '</td>';
    echo '<td style="width:100px">' . htmlspecialchars($est['ci']) . '</td>';
    echo '<td style="width:250px">' . htmlspecialchars($est['nombre'] . ' ' . $est['apellido']) . '</td>';

    // Imprimir puntajes y % obtenidos; calcular ponderado en el mismo bucle
    $nota_final = 0.0;
    foreach ($est['evaluaciones'] as $k => $ev) {
        $puntaje = isset($ev['puntaje']) ? floatval($ev['puntaje']) : 0.0;
        $porc_obtenido = isset($ev['porcentaje_obtenido']) ? $ev['porcentaje_obtenido'] : '';
        echo '<td style="width:60px;">' . $puntaje . '</td><td style="width:60px;">' . ($porc_obtenido === '' ? '' : ($porc_obtenido . '%')) . '</td>';

        if (isset($datos['evaluaciones'][$k]['porcentaje'])) {
            $peso = floatval($datos['evaluaciones'][$k]['porcentaje']) / 100.0;
            $nota_final += $puntaje * $peso;
        }
    }

    // Acumulado, calificación (ponderada) y redondeada
    echo '<td>' . (isset($est['suma_porcentajes']) ? htmlspecialchars($est['suma_porcentajes']) . '%' : '') . '</td>';
    echo '<td>' . round($nota_final, 2) . '</td>';
    $nota_redondeada = isset($est['nota_redondeada']) ? $est['nota_redondeada'] : round($nota_final);
    $notas_redondeadas[] = $nota_redondeada;

    echo '<td>' . $nota_redondeada . '</td>';

    echo '</tr>';
}





$notas = array_values(array_map('floatval', $notas_redondeadas)); // índices reindexados por si acaso

$total_estudiantes = count($notas);
$threshold = 50.0; // criterio: aprobado si nota >= 50
$count_aprobados = 0;
foreach ($notas as $n) {
    if ($n >= $threshold) $count_aprobados++;
}
$count_reprobados = $total_estudiantes - $count_aprobados;

$porcentaje_aprobados = $total_estudiantes ? round(($count_aprobados / $total_estudiantes) * 100, 2) : 0.00;
$porcentaje_reprobados = $total_estudiantes ? round(($count_reprobados / $total_estudiantes) * 100, 2) : 0.00;






// Imprimir tabla resumen con anchos ajustados (columna % más estrecha)
echo '<tr>
  <th style="background-color:#EEE; width:100px">Resumen</th>
  <th style="background-color:#EEE; width:70px">N°</th>
  <th style="background-color:#EEE; width:30px">%</th>
</tr>';

echo '<tr>
  <td style="width:100px">Cursantes</td>
  <td style="width:70px">' . $total_estudiantes . '</td>
  <td style="width:30px">100</td>
</tr>';

echo '<tr>
  <td style="width:100px">Aprobados</td>
  <td style="width:70px">' . $count_aprobados . '</td>
  <td style="width:30px">' . $porcentaje_aprobados . '</td>
</tr>';

echo '<tr>
  <td style="width:100px">Reprobados</td>
  <td style="width:70px">' . $reprobados . '</td>
  <td style="width:30px">' . $porcentaje_reprobados . '</td>
</tr>';

echo '</table>';
echo '</body></html>';
?>
