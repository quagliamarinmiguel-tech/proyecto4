<?php
// ajax/Lista-secciones-graduadosAjax.php - ACTUALIZADO CON NUEVOS CRITERIOS

// Activar todos los errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Limpiar cualquier buffer de salida
while (ob_get_level()) ob_end_clean();

// Iniciar buffer para capturar posibles errores
ob_start();

try {
    // Incluir configuraciones
    $config_path = __DIR__ . "/../config/APP.php";
    if (!file_exists($config_path)) {
        throw new Exception("No se encuentra APP.php en: " . $config_path);
    }
    require_once $config_path;
    
    $server_path = __DIR__ . "/../config/SERVER.php";
    if (!file_exists($server_path)) {
        throw new Exception("No se encuentra SERVER.php en: " . $server_path);
    }
    require_once $server_path;

    // Incluir controlador
    $controlador_path = __DIR__ . "/../controladores/ListaSeccionesGraduadosControlador.php";
    if (!file_exists($controlador_path)) {
        throw new Exception("No se encuentra ListaSeccionesGraduadosControlador.php en: " . $controlador_path);
    }
    require_once $controlador_path;

    // Crear instancia del controlador
    $controlador = new ListaSeccionesGraduadosControlador();

    // Determinar la acción
    $accion = isset($_POST['accion']) ? $_POST['accion'] : 'listar_secciones_graduados';

    switch($accion) {
        case 'listar_secciones_graduados':
            $pagina = isset($_POST['pagina']) ? max(1, intval($_POST['pagina'])) : 1;
            $registros = isset($_POST['registros']) ? intval($_POST['registros']) : 10;
            $busqueda = isset($_POST['busqueda']) ? trim($_POST['busqueda']) : '';
            $filtro_estado = isset($_POST['filtro_estado']) ? $_POST['filtro_estado'] : 'all';

            $resultado = $controlador->obtener_secciones_graduados($pagina, $registros, $busqueda, $filtro_estado);
            $secciones = $resultado['datos'];
            $total_registros = $resultado['total'];
            $total_paginas = $resultado['paginas'];

            
            $tablaHTML = '';
            if (!empty($secciones)) {
                $contador = ($pagina - 1) * $registros + 1;
                foreach($secciones as $seccion) {
                    $clase_progreso = '';
                    if ($seccion['porcentaje_completitud'] >= 100) {
                        $clase_progreso = 'progress-complete';
                    } elseif ($seccion['porcentaje_completitud'] >= 80) {
                        $clase_progreso = 'progress-high';
                    } elseif ($seccion['porcentaje_completitud'] >= 50) {
                        $clase_progreso = 'progress-medium';
                    } else {
                        $clase_progreso = 'progress-low';
                    }
                    
                    $tablaHTML .= '
						<tr>
    						<td>'.$contador.'</td>
    						<td>'.htmlspecialchars($seccion['nombre_seccion']).'</td>
    						<td>'.htmlspecialchars($seccion['nombre_curso']).'</td>
    						<td><span class="badge badge-info">'.htmlspecialchars($seccion['tipo_curso']).'</span></td>
    						<td>'.htmlspecialchars($seccion['nombre_profesor']).'</td>
    						<td>'.$seccion['total_evaluaciones'].'</td>
    						<td>
        						<div class="progress-bar-container">
           	 					<div class="progress-bar">
                					<div class="progress '.$clase_progreso.'" style="width: '.min($seccion['porcentaje_completitud'], 100).'%"></div>
            					</div>
            					<span class="progress-text">'.$seccion['porcentaje_completitud'].'%</span>
        						</div>
    						</td>
    						<td><span class="badge badge-success">'.$seccion['aprobados'].'</span></td>
    						<td><span class="badge badge-danger">'.$seccion['reprobados'].'</span></td>
    						<td>
        						<button class="btn-ver-estudiantes" 
                					data-id="'.$seccion['id_seccion'].'" 
                					data-nombre="'.htmlspecialchars($seccion['nombre_seccion'].' - '.$seccion['nombre_curso']).'">
            						<i class="fas fa-users"></i> Ver Estudiantes
        						</button>
    						</td>
						</tr>';
                    $contador++;
                }
            } else {
                $tablaHTML = '<tr><td colspan="9" class="text-center">No se encontraron secciones con estudiantes</td></tr>';
            }

            // Generar paginador mejorado
            $paginadorHTML = '';
            if ($total_paginas > 1) {
                $paginadorHTML .= '<div class="pagination-container">';
                $paginadorHTML .= '<div class="pagination-info">Mostrando '.count($secciones).' de '.$total_registros.' registros</div>';
                $paginadorHTML .= '<ul class="pagination">';
                
                // Botón anterior
                if ($pagina > 1) {
                    $paginadorHTML .= '<li class="page-item"><a class="page-link" href="#" data-pagina="'.($pagina - 1).'">Anterior</a></li>';
                }
                
                // Números de página
                $inicio_paginas = max(1, $pagina - 2);
                $fin_paginas = min($total_paginas, $pagina + 2);
                
                for ($i = $inicio_paginas; $i <= $fin_paginas; $i++) {
                    $active = ($i == $pagina) ? 'active' : '';
                    $paginadorHTML .= '<li class="page-item '.$active.'"><a class="page-link" href="#" data-pagina="'.$i.'">'.$i.'</a></li>';
                }
                
                // Botón siguiente
                if ($pagina < $total_paginas) {
                    $paginadorHTML .= '<li class="page-item"><a class="page-link" href="#" data-pagina="'.($pagina + 1).'">Siguiente</a></li>';
                }
                
                $paginadorHTML .= '</ul></div>';
            } else {
                $paginadorHTML = '<div class="pagination-info">Mostrando '.count($secciones).' de '.$total_registros.' registros</div>';
            }

            $response = [
                "success" => true,
                "tabla" => $tablaHTML,
                "paginador" => $paginadorHTML,
                "total" => $total_registros,
                "pagina_actual" => $pagina,
                "total_paginas" => $total_paginas,
                "debug" => "Consulta exitosa - " . count($secciones) . " secciones encontradas"
            ];
            break;
            
        case 'guardar_codigo_diploma':
            // Mantener compatibilidad con código existente
            $id_inscripcion = isset($_POST['id_inscripcion_codigo']) ? intval($_POST['id_inscripcion_codigo']) : 0;
            $codigo_graduado = isset($_POST['codigo_graduado']) ? trim($_POST['codigo_graduado']) : '';
            
            if ($id_inscripcion && $codigo_graduado) {
                $resultado = $controlador->guardar_codigo_graduado_controlador($id_inscripcion, $codigo_graduado);
                $response = $resultado;
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Datos insuficientes para guardar código'
                ];
            }
            break;

        case 'obtener_estudiantes_seccion':
            $id_seccion = isset($_POST['id_seccion']) ? intval($_POST['id_seccion']) : 0;
            if (!$id_seccion) {
                $response = [
                    'success' => false,
                    'error' => 'ID de sección no válido'
                ];
            } else {
                $resultado = $controlador->obtener_estudiantes_seccion_controlador($id_seccion);
                $response = $resultado;
            }
            break;

        case 'guardar_codigo_graduado':
            $id_inscripcion = isset($_POST['id_inscripcion']) ? intval($_POST['id_inscripcion']) : 0;
            $codigo_graduado = isset($_POST['codigo_graduado']) ? trim($_POST['codigo_graduado']) : '';
            
            if ($id_inscripcion && $codigo_graduado) {
                $resultado = $controlador->guardar_codigo_graduado_controlador($id_inscripcion, $codigo_graduado);
                $response = $resultado;
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Datos insuficientes: id_inscripcion y codigo_graduado son requeridos'
                ];
            }
            break;
            
        case 'generar_codigos_graduados':
            $id_seccion = isset($_POST['id_seccion']) ? intval($_POST['id_seccion']) : 0;
            $numero_base = isset($_POST['numero_base']) ? intval($_POST['numero_base']) : 0;
            
            if ($id_seccion && $numero_base !== 0) {
                $resultado = $controlador->generarCodigosGraduadosControlador($id_seccion, $numero_base);
                $response = $resultado;
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Datos insuficientes: id_seccion y numero_base son requeridos'
                ];
            }
            break;
            
        case 'borrar_codigos_seccion':
            $id_seccion = isset($_POST['id_seccion']) ? intval($_POST['id_seccion']) : 0;
            
            if ($id_seccion) {
                $resultado = $controlador->borrarCodigosSeccionControlador($id_seccion);
                $response = $resultado;
            } else {
                $response = [
                    'success' => false,
                    'message' => 'ID de sección no válido'
                ];
            }
            break;
            
        default:
            throw new Exception("Acción no válida: " . $accion);
    }

} catch (Exception $e) {
    // Capturar cualquier error
    $response = [
        "success" => false,
        "message" => "Error en el servidor: " . $e->getMessage(),
        "trace" => $e->getTraceAsString()
    ];
}

// Limpiar buffer y asegurar que no hay salida antes
$unwanted_output = ob_get_clean();
if (!empty($unwanted_output)) {
    $response['unwanted_output'] = $unwanted_output;
}

// Establecer headers y enviar respuesta
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
exit();
?>