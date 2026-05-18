<?php
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

if (isset($_POST['id_inscripcion'])) {
    $id_inscripcion = mainModel::limpiar_cadena($_POST['id_inscripcion']);
    
    $conexion = mainModel::conectar();
    $consulta = $conexion->prepare("
        SELECT * FROM transaccion 
        WHERE id_inscripcion = :id_inscripcion 
        ORDER BY fecha_pago DESC
    ");
    $consulta->execute([':id_inscripcion' => $id_inscripcion]);
    $transacciones = $consulta->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($transacciones)) {
        echo '<p class="text-center text-muted">No hay transacciones registradas para esta inscripción.</p>';
    } else {
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered table-sm">';
        echo '<thead class="thead-light">';
        echo '<tr>';
        echo '<th>Motivo</th>';
        echo '<th>Monto</th>';
        echo '<th>Fecha</th>';
        echo '<th>Método</th>';
        echo '<th>Comprobante</th>';
        echo '<th>Acciones</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($transacciones as $trans) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($trans['motivo_pago']) . '</td>';
            echo '<td>$' . number_format($trans['monto'], 2) . '</td>';
            echo '<td>' . htmlspecialchars($trans['fecha_pago']) . '</td>';
            echo '<td>' . htmlspecialchars($trans['metodo_pago']) . '</td>';
            echo '<td>';
            if (!empty($trans['if_pagomovil_imagen'])) {
                echo '<a href="../comprobantes/' . htmlspecialchars($trans['if_pagomovil_imagen']) . '" target="_blank" class="btn btn-sm btn-info">Ver</a>';
            } else {
                echo '<span class="text-muted">N/A</span>';
            }
            echo '</td>';
            echo '<td>';
            echo '<button class="btn btn-sm btn-warning btn-editar-transaccion" data-id="' . $trans['id_transaccion'] . '">Editar</button>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }
}
?>