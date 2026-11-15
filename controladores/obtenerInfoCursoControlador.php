<?php
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

class ObtenerInfoCursoControlador extends mainModel {
    
    public function obtenerInformacionCompleta($id_inscripcion) {
        $conexion = mainModel::conectar();
        
        $consulta = $conexion->prepare("
            SELECT 
                i.id_inscripcion,
                c.tipo_curso,
                c.nombre_curso,
                co.pago_modulo,
                co.pago_aranceles,
                co.pago_art13,
                co.pago_inscripcion as pago_inscripcion_cohorte,
                pi.pago_encuentro_int,
                pi.pago_titulo_int,
                pi.pago_inscripcion_int,
                pi.id_periodo_intensivo
            FROM inscripcion i
            INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
            INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
            INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
            LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
            LEFT JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk
            WHERE i.id_inscripcion = :id_inscripcion
        ");
        
        $consulta->bindValue(':id_inscripcion', $id_inscripcion, PDO::PARAM_INT);
        $consulta->execute();
        
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }
    
    public function obtenerMontosPagados($id_inscripcion) {
        $conexion = mainModel::conectar();
        
        $consulta = $conexion->prepare("
            SELECT 
                motivo_pago,
                SUM(monto) as total_pagado
            FROM transaccion 
            WHERE id_inscripcion = :id_inscripcion 
            AND estatus_pago = 1
            GROUP BY motivo_pago
        ");
        
        $consulta->bindValue(':id_inscripcion', $id_inscripcion, PDO::PARAM_INT);
        $consulta->execute();
        
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Procesar la solicitud
if ($_POST['accion'] == 'obtener_info_curso') {
    $controlador = new ObtenerInfoCursoControlador();
    $id_inscripcion = $_POST['id_inscripcion'];
    
    $infoCurso = $controlador->obtenerInformacionCompleta($id_inscripcion);
    $montosPagados = $controlador->obtenerMontosPagados($id_inscripcion);
    
    if ($infoCurso) {
        echo json_encode([
            'success' => true,
            'curso' => $infoCurso,
            'montos_pagados' => $montosPagados
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró información del curso'
        ]);
    }
    exit;
}
?>