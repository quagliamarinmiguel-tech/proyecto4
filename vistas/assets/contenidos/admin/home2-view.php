<?php 
    if($_SESSION['cargo_spm'] != 1){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
	}
?>
<?php
require_once __DIR__ . "/../../../controladores/DashboardControlador.php";
require_once __DIR__ . "/../../../controladores/respaldoControlador.php";

$controlador = new DashboardControlador();

$respaldoControlador = new respaldoControlador();
$ultimoRespaldo = $respaldoControlador->obtener_fecha_respaldo_controlador();

    // Contadores principales
    $cohortes        = $controlador->contar_cohortes_vigentes();
    $estudiantes     = $controlador->contar_estudiantes_inscritos();
    $inscripciones   = $controlador->contar_inscripciones();
    $evaluaciones    = $controlador->contar_evaluaciones();
    $calificaciones  = $controlador->contar_calificaciones();
    $cursos          = $controlador->contar_cursos();
    $cursos_vigentes = $controlador->contar_cursos_vigentes_con_cohorte_activo();
    $usuarios        = $controlador->contar_usuarios();
    $profesores      = $controlador->contar_profesores();
    $solicitudes     = $controlador->contar_solicitudes();
    $transacciones   = $controlador->contar_transacciones();
    $secciones       = $controlador->contar_secciones();
$personal_con_usuario = $controlador->contar_personal_con_usuario();
$usuarios_estudiantes = $controlador->contar_usuarios_estudiantes();
?>


<div class="mx-auto" style="width: 90%;">
  <h2 class="text-center mb-4">📊 Resumen General del Sistema</h2>
 <!--class="container mt-5"
-->
    
    
    
    <div class="row mb-4">
  <div class="col-md-6 mb-3">
      <div class="card border-primary shadow h-100">
        <div class="card-body text-center">
          <h5 class="card-title">Usuarios con Cuenta</h5>
          
          <div class="mb-3">
            <h6 class="text-muted">Personal</h6>
            <h3 class="text-primary"><?= $personal_con_usuario ?></h3>
          </div>

          <div>
            <h6 class="text-muted">Estudiantes</h6>
            <h3 class="text-info"><?= $usuarios_estudiantes ?></h3>
          </div>
          
        </div>
      </div>
  </div>
        
        
            <div class="col-md-6 mb-3">
      <div class="card border-dark shadow h-100">
        <div class="card-body text-center">
          <h5 class="card-title">Respaldo del Sistema</h5>
          <p class="text-muted mb-2" style="font-size: 0.85rem;">
            Se recomienda hacer un respaldo de los datos del sistema al menos una vez al día.
          </p>
          <?php if($ultimoRespaldo['success'] && $ultimoRespaldo['fecha']): ?>
            <p class="text-info mb-3" style="font-size: 0.9rem;">
              <strong>Último respaldo:</strong><br>
              <?php
                $fecha = new DateTime($ultimoRespaldo['fecha']);
                echo $fecha->format('d/m/Y H:i:s');
              ?>
            </p>
          <?php else: ?>
            <p class="text-warning mb-3" style="font-size: 0.9rem;">
              <strong>No hay respaldos registrados</strong>
            </p>
          <?php endif; ?>
          <button id="btnGenerarRespaldo" class="btn btn-dark btn-lg">
            <i class="fas fa-download"></i> Generar Respaldo
          </button>
          <small class="d-block mt-2 text-muted">Base de datos completa en formato SQL</small>
          <div id="respaldoStatus" class="mt-2"></div>
        </div>
      </div>
    </div>
  </div>
    
    
    
    


  <!-- Fila 2: Curso + Curso Vigente + Cohorte -->
  <div class="row mb-4" >
    <div class="col-md-4 mb-3">
        <div class="card border-info shadow h-100">
          <div class="card-body text-center">
            <h5 class="card-title">Cursos </h5>
            <h3 class="text-info"><?= $cursos ?></h3>
          </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card border-success shadow h-100">
          <div class="card-body text-center">
            <h5 class="card-title">Periodos de cursos vigentes</h5>
            <h3 class="text-success"><?= $cursos_vigentes ?></h3>
          </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card border-warning shadow h-100">
          <div class="card-body text-center">
            <h5 class="card-title">Cohortes </h5>
            <h3 class="text-warning"><?= $cohortes ?></h3>
          </div>
        </div>
    </div>
  </div>

  <!-- Fila 3: Secciones + Evaluaciones + Calificaciones (bloque único) -->
  <div class="row mb-4">
    <div class="col-md-12 mb-3">
        <div class="card border-secondary shadow h-100">
          <div class="card-body">
            <h5 class="card-title text-center">📚 Académico</h5>
            <div class="row text-center">
              <div class="col-md-4">
                <h6>Secciones</h6>
                <h4 class="text-secondary"><?= $secciones ?></h4>
              </div>
              <div class="col-md-4">
                <h6>Evaluaciones</h6>
                <h4 class="text-secondary"><?= $evaluaciones ?></h4>
              </div>
              <div class="col-md-4">
                <h6>Calificaciones</h6>
                <h4 class="text-secondary"><?= $calificaciones ?></h4>
              </div>
            </div>
          </div>
        </div>
    </div>
  </div>

  <!-- Fila 4: Estudiantes + Inscripciones + Transacciones -->
  <div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card border-primary shadow h-100">
          <div class="card-body text-center">
            <h5 class="card-title">Estudiantes Inscritos</h5>
            <h3 class="text-primary"><?= $estudiantes ?></h3>
          </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card border-info shadow h-100">
          <div class="card-body text-center">
            <h5 class="card-title">Inscripciones</h5>
            <h3 class="text-info"><?= $inscripciones ?></h3>
          </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card border-dark shadow h-100">
          <div class="card-body text-center">
            <h5 class="card-title">Transacciones</h5>
            <h3 class="text-dark"><?= $transacciones ?></h3>
          </div>
        </div>
    </div>
  </div>

  <!-- Fila 5: Profesores + Solicitudes -->
  <div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card border-success shadow h-100">
          <div class="card-body text-center">
            <h5 class="card-title">Profesores Activos</h5>
            <h3 class="text-success"><?= $profesores ?></h3>
          </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card border-warning shadow h-100">
          <div class="card-body text-center">
            <h5 class="card-title">Solicitudes Pendientes</h5>
            <h3 class="text-warning"><?= $solicitudes ?></h3>
          </div>
        </div>
    </div>
  </div>
</div>
<!--div class="container mt-5">
    <h2 class="text-center mb-4">📊 Resumen General del Sistema</h2>
    <div class="row justify-content-center">
    

        <div class="col-md-4 mb-3">
            <div class="card border-success shadow">
                <div class="card-body text-center">
                    <h5 class="card-title">Cohortes Vigentes</h5>
                    <h3 class="text-success"><?= $cohortes ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-primary shadow">
                <div class="card-body text-center">
                    <h5 class="card-title">Estudiantes Inscritos</h5>
                    <h3 class="text-primary"><?= $estudiantes ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-info shadow">
                <div class="card-body text-center">
                    <h5 class="card-title">Inscripciones</h5>
                    <h3 class="text-info"><?= $inscripciones ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-warning shadow">
                <div class="card-body text-center">
                    <h5 class="card-title">Evaluaciones</h5>
                    <h3 class="text-warning"><?= $evaluaciones ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card border-danger shadow">
                <div class="card-body text-center">
                    <h5 class="card-title">Calificaciones</h5>
                    <h3 class="text-danger"><?= $calificaciones ?></h3>
                </div>
            </div>
        </div>

        




<!-- Updated JavaScript to reload page after backup to show new date -->
<script>
document.getElementById('btnGenerarRespaldo').addEventListener('click', function() {
    const btn = this;
    const status = document.getElementById('respaldoStatus');
    
    // Deshabilitar botón y mostrar estado
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
    status.innerHTML = '<small class="text-info">Generando respaldo, por favor espere...</small>';
    
    // Crear iframe oculto para descargar el archivo
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = '<?php echo SERVERURL; ?>ajax/respaldoAjax.php?accion=generar_respaldo';
    document.body.appendChild(iframe);
    
    // Restaurar botón y recargar página después de 3 segundos para mostrar nueva fecha
    setTimeout(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-download"></i> Generar Respaldo';
        status.innerHTML = '<small class="text-success">Respaldo generado correctamente. Actualizando...</small>';
        
        // Recargar página después de 2 segundos para mostrar la nueva fecha
        setTimeout(function() {
            location.reload();
        }, 2000);
    }, 3000);
});
</script>





<?php
require_once "./config/APP.php";
require_once "./modelos/mainModel.php";
$pdo = mainModel::conectar();
$fechaLimite = date('Y-m-d', strtotime('-12 months'));

// 1. Inscritos por cohorte y curso
$sqlCV = $pdo->prepare("
  SELECT cv.id_curso_vigente, c.nombre_curso, ch.id_cohorte, ch.numero_cohorte, ch.anio_cohorte, ch.tipo_cohorte, ch.fecha_inicio
  FROM curso_vigente cv
  JOIN curso c ON cv.id_curso_fk = c.id_curso
  JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
  WHERE c.estado_curso = 1
    AND cv.estado_curso_vigente = 1
    AND ch.archivar_cohorte = 1
    AND ch.fecha_inicio >= :fechaLimite
");
$sqlCV->execute([':fechaLimite' => $fechaLimite]);
$cursoVigente = $sqlCV->fetchAll(PDO::FETCH_ASSOC);

$conteoCohorte = [];
$labelsCohorte = [];
$conteoCurso = [];

foreach ($cursoVigente as $cv) {
  $idCV = $cv['id_curso_vigente'];
  $idCohorte = $cv['id_cohorte'];
  $nombreCurso = $cv['nombre_curso'];
  $cohorteLabel = "Cohorte {$cv['numero_cohorte']} {$cv['tipo_cohorte']} - {$cv['anio_cohorte']}";

  $sqlIns = $pdo->prepare("SELECT COUNT(*) FROM inscripcion WHERE id_curso_vigente_fk = :id");
  $sqlIns->execute([':id' => $idCV]);
  $total = (int) $sqlIns->fetchColumn();

  $conteoCohorte[$idCohorte] = ($conteoCohorte[$idCohorte] ?? 0) + $total;
  $labelsCohorte[$idCohorte] = $cohorteLabel;
  $conteoCurso[$nombreCurso] = ($conteoCurso[$nombreCurso] ?? 0) + $total;
}

arsort($conteoCohorte);
arsort($conteoCurso);

$topCohorteIDs = array_slice(array_keys($conteoCohorte), 0, 10);
$labelsCohorteFinal = [];
$dataCohorteFinal = [];
foreach ($topCohorteIDs as $id) {
  $labelsCohorteFinal[] = $labelsCohorte[$id];
  $dataCohorteFinal[] = $conteoCohorte[$id];
}

$labelsCurso = array_slice(array_keys($conteoCurso), 0, 10);
$dataCurso = array_slice(array_values($conteoCurso), 0, 10);

// 2. Recaudación por cohorte
$sqlCohortes = $pdo->query("
  SELECT id_cohorte, numero_cohorte, anio_cohorte
  FROM cohorte
  WHERE fecha_inicio BETWEEN CURDATE() - INTERVAL 12 MONTH AND CURDATE()
    AND archivar_cohorte = 1
  ORDER BY fecha_inicio ASC
");
$cohortes = $sqlCohortes->fetchAll(PDO::FETCH_ASSOC);
$labelsMontos = [];
$montos = [];

foreach ($cohortes as $cohorte) {
  $idCohorte = $cohorte['id_cohorte'];
  $nombre = "Cohorte {$cohorte['numero_cohorte']}-{$cohorte['anio_cohorte']}";
  $labelsMontos[] = $nombre;

  $sqlCV = $pdo->prepare("SELECT id_curso_vigente FROM curso_vigente WHERE id_cohorte_fk = :id AND estado_curso_vigente = 1");
  $sqlCV->execute([':id' => $idCohorte]);
  $cursoVigenteIds = $sqlCV->fetchAll(PDO::FETCH_COLUMN);

  $inscripcionesValidas = [];
  foreach ($cursoVigenteIds as $idCV) {
    $sqlIns = $pdo->prepare("SELECT id_inscripcion FROM inscripcion WHERE id_curso_vigente_fk = :id AND archivada = 1");
    $sqlIns->execute([':id' => $idCV]);
    $inscripcionesValidas = array_merge($inscripcionesValidas, $sqlIns->fetchAll(PDO::FETCH_COLUMN));
  }

  $total = 0;
  if ($inscripcionesValidas) {
    $placeholders = implode(',', array_fill(0, count($inscripcionesValidas), '?'));
    $sqlMontos = $pdo->prepare("SELECT SUM(monto) FROM transaccion WHERE id_inscripcion IN ($placeholders)");
    $sqlMontos->execute($inscripcionesValidas);
    $total = (float) $sqlMontos->fetchColumn();
  }

  $montos[] = $total;
}

// 3. Promedios por cohorte
$labelsNotas = [];
$promedios = [];

foreach ($cohortes as $cohorte) {
  $idCohorte = $cohorte['id_cohorte'];
  $nombre = "Cohorte {$cohorte['numero_cohorte']}-{$cohorte['anio_cohorte']}";
  $labelsNotas[] = $nombre;

  $sqlCV = $pdo->prepare("SELECT id_curso_vigente FROM curso_vigente WHERE id_cohorte_fk = :id AND estado_curso_vigente = 1");
  $sqlCV->execute([':id' => $idCohorte]);
  $cursoVigenteIds = $sqlCV->fetchAll(PDO::FETCH_COLUMN);

  $notasFinales = [];
  foreach ($cursoVigenteIds as $idCV) {
    $sqlIns = $pdo->prepare("SELECT id_inscripcion FROM inscripcion WHERE id_curso_vigente_fk = :id AND archivada = 1");
    $sqlIns->execute([':id' => $idCV]);
    $inscripciones = $sqlIns->fetchAll(PDO::FETCH_COLUMN);

    foreach ($inscripciones as $idInscripcion) {
      $stmtNota = $pdo->prepare("
        SELECT SUM(
          CASE WHEN pe.puntaje = 'NP' THEN 0 ELSE CAST(pe.puntaje AS DECIMAL(5,2)) END * e.porcentaje / 100
        ) AS nota_final
        FROM puntaje_estudiante pe
        INNER JOIN evaluacion e ON pe.id_evaluacion = e.id_evaluacion
        WHERE pe.id_inscripcion = :id
          AND pe.archivar_puntaje = 1
          AND e.archivar_evaluacion = 1
        GROUP BY pe.id_inscripcion
        HAVING SUM(e.porcentaje) = 100
      ");
      $stmtNota->execute([':id' => $idInscripcion]);
      $notaFinal = $stmtNota->fetchColumn();
      if ($notaFinal !== false) {
        $notasFinales[] = round($notaFinal, 2);
      }
    }
  }

  $promedio = $notasFinales ? round(array_sum($notasFinales) / count($notasFinales), 2) : 0;
  $promedios[] = $promedio;
}

// Preparar datasets
$datasetCohorte = [[ "label" => "Inscritos por Cohorte", "data" => $dataCohorteFinal, "backgroundColor" => "#007bff" ]];
$datasetCurso   = [[ "label" => "Inscritos por Curso", "data" => $dataCurso, "backgroundColor" => "#28a745" ]];
$datasetMontos  = [[ "label" => "Monto total recaudado por cohorte", "data" => $montos, "backgroundColor" => "#6f42c1" ]];
$datasetNotas   = [[ "label" => "Promedio de calificaciones por cohorte", "data" => $promedios, "backgroundColor" => "#198754" ]];
?>


        <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4): ?>
				
<div style="height:20vh;"></div>
<div class="container-fluid mt-4" style="height:80vh;">
  <div class="d-flex flex-row flex-wrap justify-content-around align-items-start" style="height:100%;">
    
    <div class="flex-grow-1" style="flex-basis: 45%; min-width: 300px; height:100%;">
      <h4 class="text-center">📊 Cohortes con más inscritos (últimos 12 meses)</h4>
      <canvas id="chartCohorte" style="width:90%; height:90%;"></canvas>
    </div>

    <div class="flex-grow-1" style="flex-basis: 45%; min-width: 300px; height:100%;">
      <h4 class="text-center">📘 Cursos con más inscritos (últimos 12 meses)</h4>
      <canvas id="chartCurso" style="width:90%; height:90%;"></canvas>
    </div>

  </div>
</div>
<?php endif; ?>


<div style="height:20vh;"></div>

<?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 3): ?>
<div class="container mt-4">
  <h5 class="text-center">💸 Recaudación total por cohorte (últimos 12 meses)</h5>
  <canvas id="chartMontosCohorte"></canvas>
</div>
<?php endif; ?>


<div style="height:20vh;"></div>

<?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 2): ?>
<div class="container mt-4">
  <h5 class="text-center">📊 Promedio de calificaciones por cohorte (últimos 12 meses)</h5>
  <canvas id="chartNotasCohorte"></canvas>
</div>
<?php endif; ?>


<div style="height:20vh;"></div>
<!-- Carga Chart.js solo una vez -->
<script src="<?php echo SERVERURL; ?>vistas/js/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // 📊 Inscripciones por Cohorte
  new Chart(document.getElementById('chartCohorte').getContext('2d'), {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($labelsCohorteFinal); ?>,
      datasets: <?php echo json_encode($datasetCohorte); ?>
    },
    options: {
      responsive: true,
      plugins: {
        title: { display: true, text: 'Inscripciones por Cohorte' },
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true },
        x: { title: { display: true, text: 'Cohorte' } }
      }
    }
  });

  // 📘 Inscripciones por Curso
  new Chart(document.getElementById('chartCurso').getContext('2d'), {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($labelsCurso); ?>,
      datasets: <?php echo json_encode($datasetCurso); ?>
    },
    options: {
      responsive: true,
      plugins: {
        title: { display: true, text: 'Inscripciones por Curso' },
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true },
        x: { title: { display: true, text: 'Curso' } }
      }
    }
  });

  // 💸 Recaudación por Cohorte
  new Chart(document.getElementById('chartMontosCohorte').getContext('2d'), {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($labelsMontos); ?>,
      datasets: <?php echo json_encode($datasetMontos); ?>
    },
    options: {
      responsive: true,
      plugins: {
        title: { display: true, text: 'Monto total recaudado por cohorte' },
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          title: { display: true, text: 'Monto en dls.' }
        },
        x: { title: { display: true, text: 'Cohortes' } }
      }
    }
  });

  // 🧮 Promedio por Cohorte
  new Chart(document.getElementById('chartNotasCohorte').getContext('2d'), {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($labelsNotas); ?>,
      datasets: <?php echo json_encode($datasetNotas); ?>
    },
    options: {
      responsive: true,
      plugins: {
        title: { display: true, text: 'Promedio de calificaciones por cohorte' },
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          max: 20,
          title: { display: true, text: 'Calificación promedio' }
        },
        x: { title: { display: true, text: 'Cohortes' } }
      }
    }
  });
});
</script>
