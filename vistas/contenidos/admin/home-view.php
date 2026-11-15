<?php
    
	$cargo = (int) ($_SESSION['cargo_spm'] ?? 0);
    $state = $_SESSION['profile_status'] ?? 0;
	$carpeta =  "admin";

    if (!in_array($cargo, [1,2,3,4,5]) || $state != 1) {
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }

    require_once "./controladores/localControlador.php";
    $ctrl = new localControlador();

    $datos = $ctrl->dashboard_datos_controlador();
    //$datos = $ctrl->dashboard_contadores_controlador();
    if (!$datos['ok']) {
        echo '<div class="alert alert-danger text-center">' . htmlspecialchars($datos['message']) . '</div>';
        exit();
    }
//var_dump($datos);die();
    $c  = $datos['contadores'];
    $gr = $datos['graficos'];

    // Contadores (usa isset o ?? en cada bloque):
    $c['cursos']           ?? 0;
    $c['solicitudes']      ?? null ; // solo si el rol lo tiene
    $c['staff']            ?? null;

    // Gráficos:
    $gr['inscritos_periodo']   ?? null;
    $gr['recaudacion_periodo'] ?? null;
?>

<div class="mx-auto" style="width: 90%;">
  <h2 class="text-center mb-4">📊 Resumen General del Sistema</h2>

    <!-- ══════════ BLOQUE 1: cualquier rol ══════════ -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-info shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Cursos</h5>
                    <h3 class="text-info"><?= $c['cursos'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-success shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Cursos vigentes</h5>
                    <h3 class="text-success"><?= $c['cursos_vigentes'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-warning shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Periodos</h5>
                    <h3 class="text-warning"><?= $c['periodos'] ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card border-warning shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Periodos vigentes</h5>
                    <h3 class="text-warning"><?= $c['periodos_vigentes'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card border-success shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Profesores</h5>
                    <h3 class="text-success"><?= $c['profesores'] ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════ BLOQUE 2: cualquier rol ══════════ -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card border-primary shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Estudiantes</h5>
                    <h3 class="text-primary"><?= $c['estudiantes'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card border-primary shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Estudiantes en periodos vigentes</h5>
                    <h3 class="text-primary"><?= $c['estudiantes_vigentes'] ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════ BLOQUE 3: solo rol 1 y 2 ══════════ -->
    <?php if (in_array($cargo, [1, 2], true)): ?>
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card border-warning shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Solicitudes pendientes</h5>
                    <h3 class="text-warning"><?= $c['solicitudes'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card border-dark shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Transacciones</h5>
                    <h3 class="text-dark"><?= $c['transacciones'] ?></h3>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ══════════ BLOQUE 4: solo rol 1 ══════════ -->
    <?php if ($cargo === 1): ?>
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-secondary shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Personal</h5>
                    <h3 class="text-secondary"><?= $c['staff'] ?></h3>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

    <?php if ($cargo === 1): ?>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
           <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/reportes'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp;Reportes</a>
        </li>
    </ul>   
</div>
    <?php endif; ?>

    <?php if ($cargo != 1 || $cargo !=2): ?>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>Es posible copiar y guardar como imagen las gráficas usando el click derecho</li>
    </ul>   
</div>
    <?php endif; ?>



<?php
// $gr viene del controlador dashboard_datos_controlador()
// Cada entrada es: ['labels' => [...], 'data' => [...]]

$gr_inscritos   = $gr['inscritos_periodo']   ?? null;
$gr_cursos      = $gr['inscritos_curso']     ?? null;
$gr_recaudacion = $gr['recaudacion_periodo'] ?? null;
$gr_promedios   = $gr['promedios_periodo']   ?? null;
?>

<!-- ══════════════════════════════════════════════════════════════
      Inscritos por Periodo +  Inscritos por Curso
     Visible para roles 1 y 4
     ══════════════════════════════════════════════════════════════ -->
<?php if (in_array($cargo, [1, 4], true) && $gr_inscritos && $gr_cursos): ?>

<div style="height:20vh;"></div>

<div class="container-fluid mt-4" style="height:80vh;">
  <div class="d-flex flex-row flex-wrap justify-content-around align-items-start" style="height:100%;">

      <!-- min-width: 300px-->
    <div class="flex-grow-1" style="flex-basis: 45%;  width:95%; height:100%;">
      <h4 class="text-center">📊 Periodos con más inscritos (últimos 12 meses)</h4>
      <canvas id="chartPeriodo" style="width:90%; height:90%;"></canvas>
    </div>
  </div>
</div>

<div style="height:10vh;"></div>

<div class="container-fluid mt-4" style="height:80vh;">
  <div class="d-flex flex-row flex-wrap justify-content-around align-items-start" style="height:100%;">

    <div class="flex-grow-1" style="flex-basis: 45%;  width:95%; height:100%;">
      <h4 class="text-center">📘 Cursos con más inscritos (últimos 12 meses)</h4>
      <canvas id="chartCurso" style="width:90%; height:90%;"></canvas>
    </div>

  </div>
</div>


<?php endif; ?>


<!-- ══════════════════════════════════════════════════════════════
      Recaudación por Periodo
     Visible para roles 1 y 2
     ══════════════════════════════════════════════════════════════ -->
<?php if (in_array($cargo, [1, 2], true) && $gr_recaudacion): ?>

<div style="height:20vh;"></div>

<div class="container mt-4" style="max-height:90vh;">
  <h5 class="text-center">💸 Recaudación total por periodo (últimos 12 meses)</h5>
  <canvas id="chartMontos"></canvas>
</div>

<?php endif; ?>


<!-- ══════════════════════════════════════════════════════════════
      Promedios por Periodo
     Visible para roles 1 y 3
     ══════════════════════════════════════════════════════════════ -->
<?php if (in_array($cargo, [1, 3], true) && $gr_promedios): ?>

<!--div style="max-height:20vh;"></div>

<div class="container mt-4" style="max-height:80vh;">
  <h5 class="text-center"> Promedio de calificaciones por periodo (últimos 12 meses)</h5>
  <canvas id="chartNotas"></canvas>
</div-->

<?php endif; ?>
<div style="height:20vh;"></div>


<!-- ══════════════════════════════════════════════════════════════
     JS de Chart.js
     ══════════════════════════════════════════════════════════════ -->
<script src="<?php echo SERVERURL; ?>vistas/js/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

  // Inyectamos los datos ya procesados desde PHP
  const dataInscPeriodo   = <?php echo json_encode($gr_inscritos   ?? ['labels' => [], 'data' => []]); ?>;
  const dataInscCurso     = <?php echo json_encode($gr_cursos      ?? ['labels' => [], 'data' => []]); ?>;
  const dataRecaudacion   = <?php echo json_encode($gr_recaudacion ?? ['labels' => [], 'data' => []]); ?>;
  const dataPromedios     = <?php echo json_encode($gr_promedios   ?? ['labels' => [], 'data' => []]); ?>;

  const optBase = (yTitle, yMax) => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      title:  { display: false },
      legend: { display: false }
    },
    scales: {
      y: {
        beginAtZero: true,
        max: yMax || undefined,
        title: { display: !!yTitle, text: yTitle || '' }
      },
      x: {
        ticks: { autoSkip: false, maxRotation: 45, minRotation: 45 }
      }
    }
  });

  // ───  Inscripciones por Periodo ───
  const elPeriodo = document.getElementById('chartPeriodo');
  if (elPeriodo) {
    new Chart(elPeriodo.getContext('2d'), {
      type: 'bar',
      data: {
        labels: dataInscPeriodo.labels,
        datasets: [{
          label: 'Inscritos por Periodo',
          data: dataInscPeriodo.data,
          backgroundColor: '#007bff'
        }]
      },
      options: optBase('Inscritos')
    });
  }

  // ─── Inscripciones por Curso ───
  const elCurso = document.getElementById('chartCurso');
  if (elCurso) {
    new Chart(elCurso.getContext('2d'), {
      type: 'bar',
      data: {
        labels: dataInscCurso.labels,
        datasets: [{
          label: 'Inscritos por Curso',
          data: dataInscCurso.data,
          backgroundColor: '#28a745'
        }]
      },
      options: optBase('Inscritos')
    });
  }

  // ───  Recaudación por Periodo ───
  const elMontos = document.getElementById('chartMontos');
  if (elMontos) {
    new Chart(elMontos.getContext('2d'), {
      type: 'bar',
      data: {
        labels: dataRecaudacion.labels,
        datasets: [{
          label: 'Monto total recaudado por periodo',
          data: dataRecaudacion.data,
          backgroundColor: '#6f42c1'
        }]
      },
      options: optBase('Monto en dls.')
    });
  }

  // ───  Promedios por Periodo ───
  const elNotas = document.getElementById('chartNotas');
  if (elNotas) {
    new Chart(elNotas.getContext('2d'), {
      type: 'bar',
      data: {
        labels: dataPromedios.labels,
        datasets: [{
          label: 'Promedio de calificaciones por periodo',
          data: dataPromedios.data,
          backgroundColor: '#198754'
        }]
      },
      options: optBase('Calificación promedio', 20)
    });
  }

});
</script>
