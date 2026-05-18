<?php
function exportar_excel_estudiantes($estudiantes) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=estudiantes_seccion.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "<table border='1'>";
    echo "<tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Fecha Nacimiento</th>
            <th>Teléfono</th>
            <th>Correo</th>
            <th>Usuario</th>
          </tr>";

    foreach ($estudiantes as $e) {
        echo "<tr>
                <td>{$e['id_estudiante']}</td>
                <td>{$e['nombre']}</td>
                <td>{$e['apellido']}</td>
                <td>{$e['fecha_nacimiento']}</td>
                <td>{$e['telefono_estudiante']}</td>
                <td>{$e['correo_estudiante']}</td>
                <td>{$e['nombre_usuario']}</td>
              </tr>";
    }

    echo "</table>";
}
