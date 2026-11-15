<?php
function exportar_excel_cursos_cohorte($cursos, $nombre_cohorte = "Cohorte") {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=cursos_{$nombre_cohorte}.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "<table border='1'>";
    echo "<tr><th colspan='3'>Cursos de la Cohorte: {$nombre_cohorte}</th></tr>";
    echo "<tr>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Secciones vinculadas</th>
          </tr>";

    foreach ($cursos as $c) {
        echo "<tr>
                <td>{$c['nombre_curso']}</td>
                <td>{$c['tipo_curso']}</td>
                <td>{$c['cantidad_secciones']}</td>
              </tr>";
    }

    echo "</table>";
}

