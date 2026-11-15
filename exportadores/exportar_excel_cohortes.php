<?php
function exportar_excel_cohortes($cohortes) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=cohortes.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "<table border='1'>";
    echo "<tr>
            <th>ID</th>
            <th>Tipo</th>
            <th>Estado</th>
            <th>N° Cohorte</th>
            <th>Año</th>
            <th>Fecha Inicio</th>
            <th>Fecha Fin</th>
            <th>Cursos Vinculados</th>
          </tr>";

    foreach ($cohortes as $c) {
        echo "<tr>
                <td>{$c['id_cohorte']}</td>
                <td>{$c['tipo_cohorte']}</td>
                <td>{$c['estado_cohorte']}</td>
                <td>{$c['numero_cohorte']}</td>
                <td>{$c['anio_cohorte']}</td>
                <td>{$c['fecha_inicio']}</td>
                <td>{$c['fecha_fin']}</td>
                <td>{$c['cantidad_cursos']}</td>
              </tr>";
    }

    echo "</table>";
}
