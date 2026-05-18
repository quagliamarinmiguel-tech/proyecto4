<?php
require_once __DIR__ . '/../modelos/mainModel.php';

class DashboardControlador extends mainModel {

    public function contar_cohortes_vigentes() {
        $sql = "SELECT COUNT(*) FROM cohorte WHERE archivar_cohorte = 1";
        return $this->ejecutar_contador($sql);
    }

    public function contar_estudiantes_inscritos() {
        $sql = "SELECT COUNT(*) FROM estudiante WHERE estado_estudiante =1";
        return $this->ejecutar_contador($sql);
    }
    
    
        public function contar_cursos() {
        $sql = "SELECT COUNT(*) FROM curso WHERE estado_curso =1";
        return $this->ejecutar_contador($sql);
    }

public function contar_cursos_vigentes_con_cohorte_activo() {
    $sql = "
        SELECT COUNT(*) 
        FROM curso_vigente cv
        INNER JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
        WHERE ch.estado_cohorte = 'Vigente' AND archivar_cohorte=1 AND estado_curso_vigente=1
    ";
    return $this->ejecutar_contador($sql);
}
    
    public function contar_inscripciones() {
        $sql = "SELECT COUNT(*) FROM inscripcion WHERE archivada=1";
        return $this->ejecutar_contador($sql);
    }

    public function contar_evaluaciones() {
        $sql = "SELECT COUNT(*) FROM evaluacion WHERE archivar_evaluacion=1";
        return $this->ejecutar_contador($sql);
    }

        public function contar_transacciones() {
        $sql = "SELECT COUNT(*) FROM transaccion";
        return $this->ejecutar_contador($sql);
    }
    
        public function contar_secciones() {
        $sql = "SELECT COUNT(*) FROM seccion WHERE archivar_seccion=1";
        return $this->ejecutar_contador($sql);
    }
    
    
    public function contar_calificaciones() {
        $sql = "SELECT COUNT(*) FROM puntaje_estudiante WHERE archivar_puntaje=1";
        return $this->ejecutar_contador($sql);
    }
    
        
    public function contar_profesores() {
        $sql = "SELECT COUNT(*) FROM profesor WHERE estado_profesor=1";
        return $this->ejecutar_contador($sql);
    }

        
    public function contar_solicitudes() {
        $sql = "SELECT COUNT(*) FROM solicitud_inscripcion WHERE estado_solicitud= 'Pendiente'";
        return $this->ejecutar_contador($sql);
    }
    
    public function contar_usuarios_estudiantes() {
    $sql = "SELECT COUNT(*) FROM usuario_estudiante";
    return $this->ejecutar_contador($sql);
}
    
      public function contar_personal_con_usuario() {
    $sql = "SELECT COUNT(*) FROM personal WHERE id_usuario_fk IS NOT NULL";
    return $this->ejecutar_contador($sql);
}
    public function contar_usuarios() {
        $sql = "SELECT COUNT(*) FROM usuario WHERE estado_usuario=1";
        return $this->ejecutar_contador($sql);
    }
    
    private function ejecutar_contador($sql) {
        $conexion = mainModel::conectar();
        $stmt = $conexion->query($sql);
        return $stmt->fetchColumn();
    }

}
