<?php
//echo "iniciando a exsel_prueba_1<br>";

    require_once "../controladores/seccionControlador2.php";

echo "Llamando a exsel_prueba_1<br>";
$curso_vigente_id = $_GET['curso_vigente'] ?? 0;
$seccion_letra = $_GET['seccion'] ?? '';

var_dump($curso_vigente_id,$seccion_letra);


if ($curso_vigente_id == 0 || $seccion_letra == '') {
    echo "Parámetros inválidos.";
    exit();
}


$ins_seccion = new seccionControlador();
$datos = $ins_seccion->exsel_prueba_1($curso_vigente_id, $seccion_letra);
echo "Controlador instanciado<br>";

if (!$datos) {
    echo "No se encontró la sección.";
    exit();
}

// Nombre dinámico del archivo
$nombreArchivo = "Notas_Seccion_{$seccion_letra}_Cohorte_{$datos['seccion']['anio_cohorte']}.xls";

// Encabezados para Excel
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename={$nombreArchivo}");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF"; // BOM UTF-8
// Comienzo del contenido
echo "<table border='1' cellpadding='4' cellspacing='0'>";

// Encabezado de metadatos
echo "<tr><th colspan='100' style='background:#f2f2f2; font-size:14px; text-align:left'>
    <strong>Sección:</strong> {$datos['seccion']['nombre_seccion']} &nbsp;&nbsp;
    <strong>Letra:</strong> {$seccion_letra} &nbsp;&nbsp;
    <strong>Curso:</strong> {$datos['seccion']['nombre_curso']} &nbsp;&nbsp;
    <strong>Profesor:</strong> {$datos['seccion']['nombre_profesor']} {$datos['seccion']['apellido_profesor']}
    <strong>Cohorte:</strong> {$datos['seccion']['numero_cohorte']} - {$datos['seccion']['anio_cohorte']} ({$datos['seccion']['tipo_cohorte']})
</th></tr>";

// Encabezado de columnas
echo "<tr>
    <th>Cédula</th>
    <th>Nombre</th>
    <th>Apellido</th>";

foreach ($datos['evaluaciones'] as $eval) {
    echo "<th>{$eval['tipo_evaluacion']} (Obj {$eval['obj']})<br>{$eval['porcentaje']}%</th>";
}

echo "<th>Suma %</th>
      <th>Nota Final</th>
      <th>Redondeada</th>
</tr>";

// Filas de estudiantes
foreach ($datos['estudiantes'] as $est) {
    echo "<tr>
        <td>{$est['ci']}</td>
        <td>{$est['nombre']}</td>
        <td>{$est['apellido']}</td>";

    foreach ($est['evaluaciones'] as $ev) {
        $puntaje = $ev['puntaje'];
        $porcentaje = $ev['porcentaje_obtenido'];
        echo "<td>{$puntaje}<br><small>({$porcentaje}%)</small></td>";
    }

    echo "<td><strong>{$est['suma_porcentajes']}%</strong></td>
          <td>{$est['nota_final']}</td>
          <td><strong>{$est['nota_redondeada']}</strong></td>
    </tr>";
}

echo "</table>";
