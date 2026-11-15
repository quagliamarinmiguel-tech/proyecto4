<?php
function exportar_excel_secciones($secciones, $nombre_curso = 'Curso') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=secciones_{$nombre_curso}.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "<table border='1'>";
    echo "<tr><th colspan='4'>Secciones del Curso: {$nombre_curso}</th></tr>";
    echo "<tr>
            <th>Letra</th>
            <th>Profesor</th>
            <th>Estudiantes</th>
            <th>Evaluaciones</th>
          </tr>";

    foreach ($secciones as $s) {
        echo "<tr>
                <td>{$s['letra_seccion']}</td>
                <td>{$s['nombre_profesor']}</td>
                <td>{$s['cantidad_estudiantes']}</td>
                <td>{$s['cantidad_evaluaciones']}</td>
              </tr>";
    }

    echo "</table>";
}
