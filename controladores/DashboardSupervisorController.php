<?php
// Activar mostrar errores para debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Headers para JSON
header('Content-Type: application/json');

// Log para debugging
error_log("=== INICIANDO DASHBOARD SUPERVISOR ===");

try {
    require_once __DIR__ . '/../modelos/mainModel.php';
    error_log("✅ mainModel cargado correctamente");
    
    class DashboardSupervisorController extends mainModel {

        public function obtener_metricas_generales() {
            error_log("📊 Obteniendo métricas generales...");
            $conexion = mainModel::conectar();
            
            $metricas = [];
            
            try {
                // Total cohortes activos
                $sql = "SELECT COUNT(*) FROM cohorte WHERE archivar_cohorte = 1";
                $stmt = $conexion->prepare($sql);
                $stmt->execute();
                $metricas['total_cohortes'] = (int)$stmt->fetchColumn();
                error_log("Total cohortes: " . $metricas['total_cohortes']);

                // Total cursos vigentes
                $sql = "SELECT COUNT(*) FROM curso_vigente WHERE estado_curso_vigente = 1";
                $stmt = $conexion->prepare($sql);
                $stmt->execute();
                $metricas['total_cursos_vigentes'] = (int)$stmt->fetchColumn();
                error_log("Total cursos vigentes: " . $metricas['total_cursos_vigentes']);

                // Total profesores activos
                $sql = "SELECT COUNT(*) FROM profesor WHERE estado_profesor = 1";
                $stmt = $conexion->prepare($sql);
                $stmt->execute();
                $metricas['total_profesores'] = (int)$stmt->fetchColumn();
                error_log("Total profesores: " . $metricas['total_profesores']);

                // Total secciones activas
                $sql = "SELECT COUNT(*) FROM seccion WHERE archivar_seccion = 1";
                $stmt = $conexion->prepare($sql);
                $stmt->execute();
                $metricas['total_secciones'] = (int)$stmt->fetchColumn();
                error_log("Total secciones: " . $metricas['total_secciones']);

                // Total estudiantes inscritos
                $sql = "SELECT COUNT(*) FROM inscripcion WHERE archivada = 1 AND estado_inscripcion = 'Activa'";
                $stmt = $conexion->prepare($sql);
                $stmt->execute();
                $metricas['total_estudiantes'] = (int)$stmt->fetchColumn();
                error_log("Total estudiantes: " . $metricas['total_estudiantes']);

                // Cohortes por terminar (menos de 30 días)
                $sql = "SELECT COUNT(*) FROM cohorte WHERE archivar_cohorte = 1 AND DATEDIFF(fecha_fin, CURDATE()) <= 30 AND DATEDIFF(fecha_fin, CURDATE()) > 0";
                $stmt = $conexion->prepare($sql);
                $stmt->execute();
                $metricas['cohortes_por_terminar'] = (int)$stmt->fetchColumn();
                error_log("Cohortes por terminar: " . $metricas['cohortes_por_terminar']);

                // Profesores sin asignaciones (versión simplificada)
                $sql = "SELECT COUNT(*) FROM profesor p 
                        LEFT JOIN seccion s ON p.ci_profesor = s.ci_profesor AND s.archivar_seccion = 1 
                        WHERE p.estado_profesor = 1 AND s.id_seccion IS NULL";
                $stmt = $conexion->prepare($sql);
                $stmt->execute();
                $metricas['profesores_sin_asignaciones'] = (int)$stmt->fetchColumn();
                error_log("Profesores sin asignación: " . $metricas['profesores_sin_asignaciones']);

            } catch (Exception $e) {
                error_log("❌ Error en métricas: " . $e->getMessage());
                // Valores por defecto en caso de error
                $metricas = [
                    'total_cohortes' => 0,
                    'total_cursos_vigentes' => 0,
                    'total_profesores' => 0,
                    'total_secciones' => 0,
                    'total_estudiantes' => 0,
                    'cohortes_por_terminar' => 0,
                    'profesores_sin_asignaciones' => 0
                ];
            }
            
            return $metricas;
        }

        public function obtener_estado_cohortes() {
            error_log("📈 Obteniendo estado de cohortes...");
            try {
                $sql = "
                    SELECT 
                        ch.Id_cohorte,
                        ch.tipo_cohorte,
                        ch.estado_cohorte,
                        ch.numero_cohorte,
                        ch.anio_cohorte,
                        ch.fecha_inicio,
                        ch.fecha_fin,
                        ch.archivar_cohorte,
                        COUNT(cv.id_curso_vigente) as total_cursos,
                        SUM(CASE WHEN cv.estado_curso_vigente = 1 THEN 1 ELSE 0 END) as cursos_activos,
                        DATEDIFF(ch.fecha_fin, CURDATE()) as dias_restantes,
                        CASE 
                            WHEN DATEDIFF(ch.fecha_fin, CURDATE()) <= 30 THEN 'Por Terminar'
                            WHEN DATEDIFF(ch.fecha_fin, CURDATE()) <= 0 THEN 'Finalizado'
                            ELSE 'En Progreso'
                        END as estado_tiempo
                    FROM cohorte ch
                    LEFT JOIN curso_vigente cv ON ch.Id_cohorte = cv.id_cohorte_fk
                    WHERE ch.archivar_cohorte = 1
                    GROUP BY ch.Id_cohorte
                    ORDER BY ch.fecha_inicio DESC
                ";
                
                $conexion = mainModel::conectar();
                $stmt = $conexion->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                error_log("✅ Cohortes encontrados: " . count($result));
                return $result;
                
            } catch (Exception $e) {
                error_log("❌ Error en estado cohortes: " . $e->getMessage());
                return [];
            }
        }

        public function obtener_cohortes_incompletos() {
            error_log("⚠️ Buscando cohortes incompletos...");
            try {
                $sql = "
                    SELECT 
                        ch.Id_cohorte,
                        ch.tipo_cohorte,
                        ch.numero_cohorte,
                        ch.anio_cohorte,
                        ch.fecha_inicio,
                        ch.fecha_fin,
                        COUNT(cv.id_curso_vigente) as total_cursos,
                        CASE 
                            WHEN ch.fecha_inicio IS NULL THEN 'Sin fecha inicio'
                            WHEN ch.fecha_fin IS NULL THEN 'Sin fecha fin'
                            WHEN ch.pago_inscripcion IS NULL THEN 'Sin pago inscripción'
                            WHEN ch.pago_modulo IS NULL THEN 'Sin pago módulo'
                            WHEN COUNT(cv.id_curso_vigente) = 0 THEN 'Sin cursos asignados'
                            ELSE 'Otros datos faltantes'
                        END as problema
                    FROM cohorte ch
                    LEFT JOIN curso_vigente cv ON ch.Id_cohorte = cv.id_cohorte_fk
                    WHERE ch.archivar_cohorte = 1
                    GROUP BY ch.Id_cohorte
                    HAVING 
                        ch.fecha_inicio IS NULL 
                        OR ch.fecha_fin IS NULL 
                        OR ch.pago_inscripcion IS NULL 
                        OR ch.pago_modulo IS NULL 
                        OR COUNT(cv.id_curso_vigente) = 0
                ";
                
                $conexion = mainModel::conectar();
                $stmt = $conexion->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                error_log("✅ Cohortes incompletos: " . count($result));
                return $result;
                
            } catch (Exception $e) {
                error_log("❌ Error en cohortes incompletos: " . $e->getMessage());
                return [];
            }
        }

        // Métodos simplificados para empezar
        public function obtener_estado_cursos_vigentes() {
            error_log("📚 Obteniendo cursos vigentes...");
            return [];
        }

        public function obtener_estado_profesores() {
            error_log("👨‍🏫 Obteniendo estado profesores...");
            return [];
        }

        public function obtener_profesores_sin_asignaciones() {
            error_log("🔍 Buscando profesores sin asignación...");
            return [];
        }

        public function obtener_estado_secciones() {
            error_log("🏫 Obteniendo estado secciones...");
            return [];
        }

        public function obtener_datos_completos() {
            error_log("🎯 Obteniendo datos completos...");
            try {
                $datos = [
                    'metricas_generales' => $this->obtener_metricas_generales(),
                    'estado_cohortes' => $this->obtener_estado_cohortes(),
                    'cohortes_incompletos' => $this->obtener_cohortes_incompletos(),
                    'estado_cursos_vigentes' => $this->obtener_estado_cursos_vigentes(),
                    'estado_profesores' => $this->obtener_estado_profesores(),
                    'profesores_sin_asignaciones' => $this->obtener_profesores_sin_asignaciones(),
                    'estado_secciones' => $this->obtener_estado_secciones(),
                    'debug' => [
                        'timestamp' => date('Y-m-d H:i:s'),
                        'status' => 'success'
                    ]
                ];
                
                error_log("✅ Datos completos obtenidos exitosamente");
                return $datos;
                
            } catch (Exception $e) {
                error_log("❌ Error fatal en obtener_datos_completos: " . $e->getMessage());
                return [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'debug' => [
                        'timestamp' => date('Y-m-d H:i:s'),
                        'status' => 'error'
                    ]
                ];
            }
        }
    }

    // Ejecutar el controlador
    error_log("🚀 Inicializando controlador...");
    $controller = new DashboardSupervisorController();
    $datos = $controller->obtener_datos_completos();
    
    error_log("📤 Enviando respuesta JSON...");
    echo json_encode($datos);
    exit;

} catch (Exception $e) {
    error_log("💥 ERROR GLOBAL: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor: ' . $e->getMessage(),
        'debug' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'status' => 'fatal_error'
        ]
    ]);
    exit;
}
?>