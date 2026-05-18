<?php
require_once __DIR__ . "/../modelos/mainModel.php";

class inscripcionControlador extends mainModel {

    public function listar_inscripciones_estudiante() {
        $conexion = mainModel::conectar();

        //session_start(['name'=>'SPM']);
        $id_usuario = $_SESSION['id_spm'];

        $consultaEst = $conexion->prepare("SELECT id_estudiante_fk FROM usuario_estudiante WHERE id_usuario_fk = :id");
        $consultaEst->execute([':id' => $id_usuario]);

        if ($consultaEst->rowCount() !== 1) {
            return [];
        }

        $id_estudiante = $consultaEst->fetch()['id_estudiante_fk'];

        $consulta = $conexion->prepare("
            SELECT 
                i.id_inscripcion,
                i.fecha_inscripcion,
                i.if_fecha_abandono,
                i.estado_inscripcion,
                i.nota_final,
                i.observaciones,
                i.pago_faltante,
                
                c.nombre_curso,
                c.tipo_curso,
                
                s.nombre_seccion,
                
                co.numero_cohorte,
                co.anio_cohorte,
                co.tipo_cohorte,
                
                pi.fecha_inicio_int,
                pi.fecha_fin_int,
                pi.id_periodo_intensivo
                
            FROM inscripcion i
            INNER JOIN curso_vigente cv ON i.id_curso_vigente_fk = cv.id_curso_vigente
            INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
            INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
            LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
            LEFT JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk
            WHERE i.id_estudiante_fk = :id_estudiante
                AND i.archivada = 1
                AND c.estado_curso = 1
                AND cv.estado_curso_vigente = 1
                AND s.archivar_seccion = 1
                AND (co.archivar_cohorte = 1 OR pi.id_periodo_intensivo IS NOT NULL)
            ORDER BY i.fecha_inscripcion DESC
        ");
        $consulta->execute([':id_estudiante' => $id_estudiante]);

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>