<?php
function exportar_excel_evaluaciones($evaluaciones, $nombre_seccion = 'Seccion') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=evaluaciones_{$nombre_seccion}.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "<table border='1'>";
    echo "<tr><th colspan='5'>Evaluaciones de la Sección: {$nombre_seccion}</th></tr>";
    echo "<tr>
            <th>#</th>
            <th>Tipo</th>
            <th>Objetivo</th>
            <th>Porcentaje</th>
            <th>Fecha</th>
          </tr>";

    $contador = 1;
    foreach ($evaluaciones as $e) {
        echo "<tr>
                <td>{$contador}</td>
                <td>{$e['tipo_evaluacion']}</td>
                <td>{$e['objetivo_evaluacion']}</td>
                <td>" . (!empty($e['porcentaje']) ? $e['porcentaje'] . '%' : '—') . "</td>
                <td>{$e['fecha_evaluacion']}</td>
              </tr>";
        $contador++;
    }

    echo "</table>";
}
