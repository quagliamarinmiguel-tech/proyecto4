
<?php
// verificar_solicitud.php - Vista completa para verificar y procesar una solicitud
if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 3) {
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

$cargo = $_SESSION['cargo_spm'] ?? 1;
$carpetasPorCargo = [
    1 => "admin",
    2 => "Control de estudio",
    3 => "administrador de pagos",
    4 => "Supervisor de cursos",
    5 => "Estudiante"
];
$carpeta = $carpetasPorCargo[$cargo] ?? "admin";

require_once "./config/APP.php";
require_once "./modelos/mainModel.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

$idSolicitud = $_GET['solicitud_id'] ?? "";
$idSolicitud = mainModel::decryption($idSolicitud);
$idSolicitud = mainModel::limpiar_cadena($idSolicitud);
$pdo = mainModel::conectar();

// Obtener datos de la solicitud CORREGIDO
$stmt = $pdo->prepare("
    SELECT 
        si.*, 
        cv.id_curso_vigente,
        cv.estado_curso_vigente,
        c.nombre_curso, 
        c.tipo_curso,
        c.id_curso,
        -- Datos de cohorte (si existe)
        ch.numero_cohorte,
        ch.anio_cohorte,
        ch.tipo_cohorte,
        ch.estado_cohorte,
        ch.id_cohorte,
        ch.archivar_cohorte,
        ch.fecha_inicio,
        ch.fecha_fin,
        -- Datos de periodo intensivo (si existe)
        pi.fecha_inicio_int,
        pi.fecha_fin_int,
        pi.estado_periodo,
        pi.id_periodo_intensivo,
        -- Determinar tipo
        CASE 
            WHEN cv.id_cohorte_fk IS NOT NULL THEN 'cohorte'
            ELSE 'intensivo'
        END AS tipo_curso_vigente
    FROM solicitud_inscripcion si
    INNER JOIN curso_vigente cv ON si.id_curso_seleccionado = cv.id_curso_vigente
    INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
    LEFT JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
    LEFT JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk
    WHERE si.id_solicitud = :id
");
$stmt->execute([':id' => $idSolicitud]);
$solicitud = $stmt->fetch();

if (!$solicitud) {
    echo '<p class="text-center">Solicitud no encontrada</p>';
    exit();
}

// CORREGIDO: Validación existencia de estudiante (solo por cédula)
$stmtEst = $pdo->prepare("
    SELECT COUNT(*) FROM estudiante
    WHERE ci_estudiante = :ci
");
$stmtEst->execute([':ci' => $solicitud['cedula_estudiante']]);
$estudianteExiste = $stmtEst->fetchColumn() > 0;

// CORREGIDO: Verificar si el curso_vigente está activo y vigente/próximo
$cursoVigenteValido = false;
$mensajeEstadoCurso = '';
$motivoBloqueo = '';

if ($solicitud['tipo_curso_vigente'] === 'cohorte') {
    // Validar curso con cohorte
    $cursoVigenteValido = (
        $solicitud['estado_curso_vigente'] == 1 && 
        in_array($solicitud['estado_cohorte'], ['Vigente', 'Próximo']) &&
        $solicitud['archivar_cohorte'] == 1
    );
    $mensajeEstadoCurso = "Cohorte: {$solicitud['numero_cohorte']}.{$solicitud['anio_cohorte']} - {$solicitud['estado_cohorte']}";
    
    // Detalles del bloqueo para mensajes
    if ($solicitud['estado_curso_vigente'] != 1) {
        $motivoBloqueo = "El curso vigente no está activo";
    } elseif (!in_array($solicitud['estado_cohorte'], ['Vigente', 'Próximo'])) {
        $motivoBloqueo = "El cohorte no está Vigente o Próximo (estado: {$solicitud['estado_cohorte']})";
    } elseif ($solicitud['archivar_cohorte'] != 1) {
        $motivoBloqueo = "El cohorte está archivado";
    }
} else {
    // Validar curso intensivo
    $cursoVigenteValido = (
        $solicitud['estado_curso_vigente'] == 1 && 
        in_array($solicitud['estado_periodo'], ['Vigente', 'Próximo'])
    );
    $fechaInicio = $solicitud['fecha_inicio_int'] ? date('d/m/Y', strtotime($solicitud['fecha_inicio_int'])) : 'N/A';
    $fechaFin = $solicitud['fecha_fin_int'] ? date('d/m/Y', strtotime($solicitud['fecha_fin_int'])) : 'N/A';
    $mensajeEstadoCurso = "Periodo: {$fechaInicio} al {$fechaFin} - {$solicitud['estado_periodo']}";
    
    // Detalles del bloqueo para mensajes
    if ($solicitud['estado_curso_vigente'] != 1) {
        $motivoBloqueo = "El curso vigente no está activo";
    } elseif (!in_array($solicitud['estado_periodo'], ['Vigente', 'Próximo'])) {
        $motivoBloqueo = "El periodo intensivo no está Vigente o Próximo (estado: {$solicitud['estado_periodo']})";
    }
}

// ========== NUEVA LÓGICA PARA OBTENER CV VIGENTE Y PRÓXIMO ==========

// Obtener el CV VIGENTE del curso (solo 1) - Adaptado para cohorte e intensivo
$stmtCVVigente = $pdo->prepare("
    SELECT 
        cv.id_curso_vigente,
        cv.estado_curso_vigente,
        c.tipo_curso,
        c.nombre_curso,
        -- Datos de cohorte vigente
        co.id_cohorte,
        co.numero_cohorte,
        co.anio_cohorte,
        co.fecha_inicio,
        co.fecha_fin,
        co.estado_cohorte,
        co.tipo_cohorte,
        -- Datos de periodo intensivo vigente
        pi.id_periodo_intensivo,
        pi.fecha_inicio_int,
        pi.fecha_fin_int,
        pi.estado_periodo,
        -- Determinar tipo de CV
        CASE 
            WHEN cv.id_cohorte_fk IS NOT NULL THEN 'cohorte'
            ELSE 'intensivo'
        END AS tipo_curso_vigente,
        -- Fecha de inicio para ordenamiento
        CASE 
            WHEN cv.id_cohorte_fk IS NOT NULL THEN co.fecha_inicio
            ELSE pi.fecha_inicio_int
        END AS fecha_inicio_orden
    FROM curso_vigente cv
    INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
    LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte AND co.estado_cohorte = 'Vigente'
    LEFT JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk AND pi.estado_periodo = 'Vigente'
    WHERE cv.estado_curso_vigente = 1 
      AND c.id_curso = :id_curso
      AND (co.id_cohorte IS NOT NULL OR pi.id_periodo_intensivo IS NOT NULL)
    ORDER BY 
        -- Priorizar el que ya estaba seleccionado en la solicitud
        CASE WHEN cv.id_curso_vigente = :cv_actual THEN 1 ELSE 2 END,
        -- Luego por fecha más cercana
        fecha_inicio_orden ASC
    LIMIT 1
");
$stmtCVVigente->execute([
    ':id_curso' => $solicitud['id_curso'],
    ':cv_actual' => $solicitud['id_curso_seleccionado']
]);
$cvVigente = $stmtCVVigente->fetch();

// Obtener el CV PRÓXIMO más cercano (solo 1) - Adaptado para cohorte e intensivo
$stmtCVProximo = $pdo->prepare("
    SELECT 
        cv.id_curso_vigente,
        cv.estado_curso_vigente,
        c.tipo_curso,
        c.nombre_curso,
        -- Datos de cohorte próximo
        co.id_cohorte,
        co.numero_cohorte,
        co.anio_cohorte,
        co.fecha_inicio,
        co.fecha_fin,
        co.estado_cohorte,
        co.tipo_cohorte,
        -- Datos de periodo intensivo próximo
        pi.id_periodo_intensivo,
        pi.fecha_inicio_int,
        pi.fecha_fin_int,
        pi.estado_periodo,
        -- Determinar tipo de CV
        CASE 
            WHEN cv.id_cohorte_fk IS NOT NULL THEN 'cohorte'
            ELSE 'intensivo'
        END AS tipo_curso_vigente,
        -- Calcular proximidad (días hasta inicio)
        CASE 
            WHEN cv.id_cohorte_fk IS NOT NULL THEN DATEDIFF(co.fecha_inicio, CURDATE())
            ELSE DATEDIFF(pi.fecha_inicio_int, CURDATE())
        END AS dias_hasta_inicio,
        -- Fecha de inicio para formateo
        CASE 
            WHEN cv.id_cohorte_fk IS NOT NULL THEN co.fecha_inicio
            ELSE pi.fecha_inicio_int
        END AS fecha_inicio,
        -- Fecha de fin para formateo
        CASE 
            WHEN cv.id_cohorte_fk IS NOT NULL THEN co.fecha_fin
            ELSE pi.fecha_fin_int
        END AS fecha_fin
    FROM curso_vigente cv
    INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
    LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte AND co.estado_cohorte = 'Próximo'
    LEFT JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk AND pi.estado_periodo = 'Próximo'
    WHERE cv.estado_curso_vigente = 1 
      AND c.id_curso = :id_curso
      AND (co.id_cohorte IS NOT NULL OR pi.id_periodo_intensivo IS NOT NULL)
      AND cv.id_curso_vigente != COALESCE(:cv_vigente, 0)  -- Excluir el CV vigente si existe
    ORDER BY dias_hasta_inicio ASC
    LIMIT 1
");
$stmtCVProximo->execute([
    ':id_curso' => $solicitud['id_curso'],
    ':cv_vigente' => $cvVigente ? $cvVigente['id_curso_vigente'] : null
]);
$cvProximo = $stmtCVProximo->fetch();

// Obtener secciones de ambos CVs
$seccionesVigente = [];
$seccionesProximo = [];

if ($cvVigente) {
    $stmtSeccionesVigente = $pdo->prepare("
        SELECT  
            s.id_seccion,  
            s.nombre_seccion,
            s.dia,
            COUNT(i.id_inscripcion) AS inscritos,
            p.nombre_profesor,
            p.apellido_profesor
        FROM seccion s
        LEFT JOIN inscripcion i ON i.id_seccion_fk = s.id_seccion AND i.archivada = 1
        LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
        WHERE s.id_curso_vigente_fk = :id_curso_vigente
          AND s.archivar_seccion = 1
        GROUP BY s.id_seccion
        ORDER BY s.nombre_seccion ASC
    ");
    $stmtSeccionesVigente->execute([':id_curso_vigente' => $cvVigente['id_curso_vigente']]);
    $seccionesVigente = $stmtSeccionesVigente->fetchAll();
}

if ($cvProximo) {
    $stmtSeccionesProximo = $pdo->prepare("
        SELECT  
            s.id_seccion,  
            s.nombre_seccion,
            s.dia,
            COUNT(i.id_inscripcion) AS inscritos,
            p.nombre_profesor,
            p.apellido_profesor
        FROM seccion s
        LEFT JOIN inscripcion i ON i.id_seccion_fk = s.id_seccion AND i.archivada = 1
        LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
        WHERE s.id_curso_vigente_fk = :id_curso_vigente
          AND s.archivar_seccion = 1
        GROUP BY s.id_seccion
        ORDER BY s.nombre_seccion ASC
    ");
    $stmtSeccionesProximo->execute([':id_curso_vigente' => $cvProximo['id_curso_vigente']]);
    $seccionesProximo = $stmtSeccionesProximo->fetchAll();
}

// Combinar todas las secciones
$todasSecciones = array_merge($seccionesVigente, $seccionesProximo);

// Función para formatear fecha
function formatearFecha($fecha) {
    return $fecha ? date('d/m/y', strtotime($fecha)) : 'N/A';
}

// Función para generar texto de opción - ADAPTADA PARA AMBOS CASOS
function generarTextoOpcion($cv, $sec, $estado) {
    $tipo = $cv['tipo_curso']; // 'Int' o 'Ext' según el curso
    $estadoTexto = $estado === 'Vigente' ? 'Vig' : 'Prox';
    
    // Determinar prefijo según tipo de CV
    if ($cv['tipo_curso_vigente'] === 'cohorte') {
        $prefijo = "co {$cv['numero_cohorte']}-{$cv['anio_cohorte']} {$cv['tipo_cohorte']}";
        $fechaInicio = $cv['fecha_inicio'];
        $fechaFin = $cv['fecha_fin'];
    } else {
        $prefijo = "{$tipo}"; // Solo el tipo de curso para intensivos
        $fechaInicio = $cv['fecha_inicio_int'];
        $fechaFin = $cv['fecha_fin_int'];
    }
    
    $texto = "{$prefijo} {$estadoTexto}. - fi: " . formatearFecha($fechaInicio) . " - ff: " . formatearFecha($fechaFin);
    $texto .= " - Sec " . htmlspecialchars($sec['nombre_seccion']) . " (" . htmlspecialchars($sec['dia']) . ")";
    
    if ($sec['nombre_profesor']) {
        $texto .= " - Prof. " . htmlspecialchars($sec['nombre_profesor'] . ' ' . $sec['apellido_profesor']);
    }
    
    $texto .= " - Inscritos: " . $sec['inscritos'];
    
    return $texto;
}

// Verificar si hay secciones disponibles
$haySecciones = count($todasSecciones) > 0;

// Decisiones de bloqueo de botones
$estadoSolicitud = $solicitud['estado_solicitud'] ?? '';
$bloquearAceptar = false;
$bloquearRechazar = false;

// Rechazar se bloquea si ya fue Rechazada o Aceptada
if ($estadoSolicitud === 'Rechazada' || $estadoSolicitud === 'Aceptada') {
    $bloquearRechazar = true;
}

// CORREGIDO: Aceptar se bloquea si:
// - solicitud ya Aceptada
// - estudiante ya existe (por cédula)
// - NO existe curso_vigente válido (activo + cohorte/periodo Vigente/Próximo)
if ($estadoSolicitud === 'Aceptada' || $estudianteExiste || !$cursoVigenteValido) {
    $bloquearAceptar = true;
}

?>

<style>
.label-destacada {
  font-size: 1.15rem;
  font-weight: 600;
  color: #212529;
}
.text-explicativo {
  font-size: 1rem;
  color: #495057;
}
.captura-img {
  max-height: 500px;
  border: 2px solid #ccc;
}
.muted-note {
  color: #6c757d;
}
.info-curso {
  background: #e9f7ef;
  border-left: 4px solid #28a745;
  padding: 15px;
  border-radius: 4px;
}
.estado-activo {
  color: #28a745;
  font-weight: bold;
}
.estado-inactivo {
  color: #dc3545;
  font-weight: bold;
}
</style>

<div class="container mt-4">
    <div class="full-box page-header">
        <h3 class="text-left"><i class="fas fa-check-circle fa-fw"></i> &nbsp; VERIFICAR SOLICITUD</h3>
        <p class="text-justify">Revisa los datos de la solicitud antes de procesarla. Si el estudiante ya existe o el curso no está vigente, no se podrá aceptar.</p>
    </div>
    
    <div class="container-fluid">
        <ul class="full-box list-unstyled page-nav-tabs">
            <li>
                <a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/solicitud-check'; ?>"><i class="fas fa-plus fa-fw"></i> &nbsp; VERIFICAR SOLICITUD</a>
            </li>
            <li>
                <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/solicitud-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE SOLICITUDES</a>
            </li>
        </ul>   
    </div>

    <!-- Información del curso seleccionado -->
    <div class="card mb-3 info-curso">
        <div class="card-body">
            <h5 class="label-destacada mb-2">
                <i class="fas fa-book"></i> Curso a inscribir: 
                <?= htmlspecialchars($solicitud['nombre_curso']) ?> 
                <small class="text-muted">(<?= htmlspecialchars($solicitud['tipo_curso']) ?>)</small>
            </h5>
            <p class="mb-1"><strong>ID Curso Vigente:</strong> <?= htmlspecialchars($solicitud['id_curso_seleccionado']) ?></p>
            <p class="mb-1"><strong>Modalidad:</strong> 
                <?= $solicitud['tipo_curso_vigente'] === 'cohorte' ? 'Por Cohorte' : 'Intensivo' ?>
            </p>
            <p class="mb-1"><strong>Estado Curso Vigente:</strong> 
                <span class="<?= $solicitud['estado_curso_vigente'] == 1 ? 'estado-activo' : 'estado-inactivo' ?>">
                    <?= $solicitud['estado_curso_vigente'] == 1 ? 'ACTIVO' : 'INACTIVO' ?>
                </span>
            </p>
            <p class="mb-1"><strong>Periodo:</strong> <?= $mensajeEstadoCurso ?></p>
            <?php if ($solicitud['tipo_curso_vigente'] === 'cohorte'): ?>
                <p class="mb-0"><strong>Cohorte:</strong> 
                    <?= htmlspecialchars($solicitud['numero_cohorte'] . '.' . $solicitud['anio_cohorte']) ?> 
                    (<?= htmlspecialchars($solicitud['tipo_cohorte']) ?>)
                    <?php if ($solicitud['archivar_cohorte'] == 1): ?>
                        <span class="badge badge-success ml-2">Activo</span>
                    <?php else: ?>
                        <span class="badge badge-danger ml-2">Archivado</span>
                    <?php endif; ?>
                </p>
            <?php else: ?>
                <p class="mb-0"><strong>Periodo intensivo:</strong> 
                    ID <?= htmlspecialchars($solicitud['id_periodo_intensivo']) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card p-3">
                <h5 class="label-destacada mb-2">Datos del estudiante</h5>
                <p class="mb-1"><strong>Nombre:</strong> <?= htmlspecialchars($solicitud['nombre_estudiante'] . ' ' . $solicitud['apellido_estudiante']) ?></p>
                <p class="mb-1"><strong>Cédula:</strong> <?= htmlspecialchars($solicitud['cedula_estudiante']) ?></p>
                <p class="mb-1"><strong>Fecha Nac.:</strong> <?= htmlspecialchars($solicitud['fecha_nacimiento']) ?></p>
                <p class="mb-1"><strong>Teléfono:</strong> <?= htmlspecialchars($solicitud['telefono_estudiante']) ?></p>
                <p class="mb-1"><strong>Correo:</strong> <?= htmlspecialchars($solicitud['correo_Estudiante']) ?></p>
                <p class="mb-1"><strong>Estado solicitud:</strong> 
                    <span class="badge badge-<?= $solicitud['estado_solicitud'] === 'Pendiente' ? 'warning' : ($solicitud['estado_solicitud'] === 'Aceptada' ? 'success' : 'danger') ?>">
                        <?= htmlspecialchars($solicitud['estado_solicitud']) ?>
                    </span>
                </p>
                <?php if ($estudianteExiste): ?>
                    <div class="alert alert-danger mt-2 mb-0">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Estudiante ya registrado:</strong> Esta cédula ya existe en el sistema.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-6 text-center">
            <p class="label-destacada">Captura de pago</p>
            <?php if (!empty($solicitud['captura_pago'])): ?>
                <img src="<?= SERVERURL . 'vistas/' . $solicitud['captura_pago'] ?>" alt="Captura de pago" class="img-fluid captura-img">
            <?php else: ?>
                <div class="border p-4 muted-note">No hay captura de pago adjunta</div>
            <?php endif; ?>
            
            <?php if (!empty($solicitud['monto'])): ?>
                <div class="mt-3">
                    <p class="mb-1"><strong>Monto pagado:</strong> <?= htmlspecialchars($solicitud['monto']) ?></p>
                    <p class="mb-1"><strong>Fecha pago:</strong> <?= htmlspecialchars($solicitud['fecha_pago']) ?></p>
                    <p class="mb-0"><strong>Método pago:</strong> <?= htmlspecialchars($solicitud['metodo_pago']) ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <hr>

    <form class="FormularioAjax" action="<?= SERVERURL ?>ajax/recuperarAjax.php" method="POST" data-form="save" autocomplete="off">
        <input type="hidden" name="solicitud_id_check" value="<?= htmlspecialchars($_GET['solicitud_id'] ?? '') ?>">
        <input type="hidden" name="id_curso_vigente_fk" value="<?= htmlspecialchars($solicitud['id_curso_seleccionado']) ?>">
        
        <input type="hidden" name="nombre_estudiante" value="<?= htmlspecialchars($solicitud['nombre_estudiante']) ?>">
        <input type="hidden" name="apellido_estudiante" value="<?= htmlspecialchars($solicitud['apellido_estudiante']) ?>">
        <input type="hidden" name="cedula_estudiante" value="<?= htmlspecialchars($solicitud['cedula_estudiante']) ?>">
        <input type="hidden" name="telefono_estudiante" value="<?= htmlspecialchars($solicitud['telefono_estudiante']) ?>">
        <input type="hidden" name="correo_Estudiante" value="<?= htmlspecialchars($solicitud['correo_Estudiante']) ?>">
        <input type="hidden" name="fecha_nacimiento" value="<?= htmlspecialchars($solicitud['fecha_nacimiento']) ?>">
        <input type="hidden" name="monto" value="<?= htmlspecialchars($solicitud['monto']) ?>">
        <input type="hidden" name="fecha_pago" value="<?= htmlspecialchars($solicitud['fecha_pago']) ?>">
        <input type="hidden" name="metodo_pago" value="<?= htmlspecialchars($solicitud['metodo_pago']) ?>">
        <input type="hidden" name="captura_pago" value="<?= htmlspecialchars($solicitud['captura_pago']) ?>">
        <input type="hidden" name="clave_estudiante" value="<?= htmlspecialchars($solicitud['clave']) ?>">

        <div class="card mb-3 p-3">
            <label for="seccion" class="form-label label-destacada">Sección disponible</label>
            <div class="text-explicativo mb-2">Elige una sección para inscribir al estudiante en el curso seleccionado.</div>
            
            <?php if ($haySecciones): ?>
                <select name="seccion_id" id="seccion" class="form-control mb-3" required>
                    <option value="">-- Seleccione una sección --</option>
                    
                    <!-- Secciones del CV VIGENTE -->
                    <?php if (count($seccionesVigente) > 0): ?>
                        <optgroup label="🟢 Periodo Vigente">
                        <?php foreach ($seccionesVigente as $sec): ?>
                            <option value="<?= $sec['id_seccion'] ?>">
                                <?= generarTextoOpcion($cvVigente, $sec, 'Vigente') ?>
                            </option>
                        <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                    
                    <!-- Secciones del CV PRÓXIMO -->
                    <?php if (count($seccionesProximo) > 0): ?>
                        <optgroup label="🟡 Periodo Próximo">
                        <?php foreach ($seccionesProximo as $sec): ?>
                            <option value="<?= $sec['id_seccion'] ?>">
                                <?= generarTextoOpcion($cvProximo, $sec, 'Próximo') ?>
                            </option>
                        <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                    
                </select>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> No hay secciones disponibles para este curso vigente.
                </div>
                <input type="hidden" name="seccion_id" value="">
            <?php endif; ?>

            <div class="form-group text-center mt-3">
                <!-- Mensajes de validación -->
                <?php if ($estudianteExiste): ?>
                    <p class="text-danger mb-1"><i class="fas fa-exclamation-circle"></i> 
                        ❌ El estudiante con cédula <?= htmlspecialchars($solicitud['cedula_estudiante']) ?> ya está registrado en el sistema.
                    </p>
                <?php endif; ?>
                
                <?php if (!$cursoVigenteValido && $motivoBloqueo): ?>
                    <p class="text-danger mb-1"><i class="fas fa-exclamation-circle"></i> 
                        ❌ No se puede aceptar: <?= $motivoBloqueo ?>
                    </p>
                <?php endif; ?>
                
                <?php if (!$haySecciones): ?>
                    <p class="text-danger mb-1"><i class="fas fa-exclamation-circle"></i> 
                        ❌ No hay secciones disponibles para inscribir al estudiante.
                    </p>
                <?php endif; ?>
                
                <?php if ($estadoSolicitud === 'Aceptada'): ?>
                    <p class="text-center muted-note mt-2">⚠️ Esta solicitud ya fue aprobada y no puede volver a procesarse.</p>
                <?php endif; ?>

                <!-- Botón Aceptar con validación completa -->
                <button type="submit" class="btn btn-success" <?= $bloquearAceptar || !$haySecciones ? 'disabled' : '' ?>>
                    <i class="fas fa-check"></i> Aceptar solicitud
                </button>
                
                <?php if (!$bloquearAceptar && $haySecciones): ?>
                    <p class="text-success mt-2">
                        <i class="fas fa-check-circle"></i> La solicitud cumple con todos los requisitos para ser aceptada.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <div class="d-flex justify-content-center mt-3">
        <form action="<?php echo SERVERURL; ?>ajax/recuperarAjax.php" method="POST" class="FormularioAjax" data-form="update" data-entity="solicitud">
            <input type="hidden" name="id_solicitud_reprobar" value="<?php echo $_GET['solicitud_id']; ?>">
            <button type="submit" class="btn btn-danger" <?= $bloquearRechazar ? 'disabled' : '' ?>>
                <i class="fas fa-times"></i> Rechazar solicitud
            </button>
        </form>
    </div>
</div>