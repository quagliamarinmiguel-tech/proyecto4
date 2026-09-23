<?php
if($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

require_once "./controladores/localControlador.php";
$ctrl = new localControlador();

$resp = $ctrl->estadisticas_home_controlador();
if (!$resp['ok']) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($resp['message'] ?? 'Error al cargar estadísticas') . '</div>';
    exit;
}

$d = $resp['data'];

$panel         = $d['panel'];
$topCursos     = $d['topCursos'];
$interacciones = $d['interacciones'];

// JSON para los charts
$labels1         = json_encode($d['labels1']);
$data1           = json_encode($d['data1']);
$labels2         = json_encode($d['labels2']);
$data2           = json_encode($d['data2']);
$labelsTopCursos = json_encode($d['labelsTopCursos']);
$clicks          = json_encode($d['clicks']);
$unicos          = json_encode($d['unicos']);
$labelsType      = json_encode($d['labelsType']);
$dataType        = json_encode($d['dataType']);
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-chart-line fa-fw"></i> &nbsp; ESTADÍSTICAS DE INTERACCIONES
    </h3>
    <p class="text-justify">Panel informativo que muestra métricas generales del sistema de interacción, cursos más clickeados y registros recientes.</p>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12 mb-4">
            <fieldset>
                <legend><i class="fas fa-info-circle"></i> &nbsp; Panel general de métricas</legend>
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h6>IPs únicas</h6>
                                <h2><?= htmlspecialchars($panel['ips']) ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h6>Total de interacciones</h6>
                                <h2><?= htmlspecialchars($panel['total']) ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h6>Última interacción</h6>
                                <h2><?= htmlspecialchars($panel['ultima']) ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h6>Curso destacado</h6>
                                <h2><?= htmlspecialchars($panel['cursoTop']) ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>

        <!-- Top 10 cursos -->
        <div class="col-12 mb-4">
            <fieldset>
                <legend><i class="fas fa-trophy"></i> &nbsp; Top 10 cursos más clickeados (último mes)</legend>
                <ul class="list-group">
                    <?php if(count($topCursos) > 0): ?>
                        <?php foreach($topCursos as $index => $curso): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong><?= ($index+1).". ".htmlspecialchars($curso['nombre']) ?></strong>
                                <span class="badge bg-dark" style="color:white;"><?= htmlspecialchars($curso['total']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item">No hay registros de clics recientes en cursos.</li>
                    <?php endif; ?>
                </ul>
            </fieldset>
        </div>

        <div class="col-12 mb-4">
            <fieldset>
                <legend><i class="fas fa-clock"></i> &nbsp; Interacciones recientes</legend>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm text-center">
                        <thead class="bg-dark text-white">
                            <tr><th>#</th><th>Curso</th><th>IP</th><th>Fecha/Hora</th></tr>
                        </thead>
                        <tbody>
                            <?php if(count($interacciones) > 0): ?>
                                <?php foreach($interacciones as $index => $row): ?>
                                    <tr>
                                        <td><?= $index+1 ?></td>
                                        <td><?= htmlspecialchars($row['nombre_curso']) ?></td>
                                        <td><?= htmlspecialchars($row['direccion_ip']) ?></td>
                                        <td><?= htmlspecialchars($row['fecha_hora']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4">No hay interacciones registradas.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </fieldset>
        </div>
    </div>
</div>

<div class="container-fluid mt-4" style="height:50vh;">
  <div class="row" style="height:100%;">
    <div class="col-sm-6" style="height:100%;">
      <canvas id="chartVisitasDia" style="width:100%; height:100%;"></canvas>
    </div>
    <div class="col-sm-6" style="height:100%;">
      <canvas id="chartUnicosDia" style="width:100%; height:100%;"></canvas>
    </div>
  </div>
</div>

<div class="container-fluid mt-4" style="height:50vh;">
  <div class="row" style="height:100%;">
    <div class="col-sm-6" style="height:100%;">
      <canvas id="chartClicksByType" style="width:100%; height:100%;"></canvas>
    </div>
    <div class="col-sm-6" style="height:100%;">
      <canvas id="chartTopCursos" style="width:100%; height:100%;"></canvas>
    </div>
  </div>
</div>

<script src="<?php echo SERVERURL; ?>vistas/js/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  new Chart(document.getElementById('chartVisitasDia').getContext('2d'), {
    type: 'bar',
    data: {
      labels: <?php echo $labels1; ?>,
      datasets: [{ label: 'Visitas por día', data: <?php echo $data1; ?>, backgroundColor: '#007bff' }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } }, plugins: { title: { display: true, text: 'Visitas diarias' } } }
  });

  new Chart(document.getElementById('chartUnicosDia').getContext('2d'), {
    type: 'line',
    data: {
      labels: <?php echo $labels2; ?>,
      datasets: [{ label: 'Visitantes únicos', data: <?php echo $data2; ?>, borderColor: '#28a745', fill: false }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } }, plugins: { title: { display: true, text: 'Visitantes únicos diarios' } } }
  });

  new Chart(document.getElementById('chartTopCursos').getContext('2d'), {
    type: 'bar',
    data: {
      labels: <?php echo $labelsTopCursos; ?>,
      datasets: [
        { label: 'Total Clicks', data: <?php echo $clicks; ?>, backgroundColor: '#007bff' },
        { label: 'Clickers únicos', data: <?php echo $unicos; ?>, backgroundColor: '#28a745' }
      ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }, plugins: { title: { display: true, text: 'Top 10 cursos por clicks y visitantes únicos (Últimos 30 días)' } } }
  });

  new Chart(document.getElementById('chartClicksByType').getContext('2d'), {
    type: 'pie',
    data: {
      labels: <?php echo $labelsType; ?>,
      datasets: [{ label: 'Clicks por tipo', data: <?php echo $dataType; ?>, backgroundColor: ['#007bff','#28a745','#ffc107','#dc3545','#6f42c1','#20c997'] }]
    },
    options: { responsive: true, plugins: { title: { display: true, text: 'Distribución de clicks por tipo de curso (Últimos 30 días)' }, legend: { position: 'right' } } }
  });
});
</script>