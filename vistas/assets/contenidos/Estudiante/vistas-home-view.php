<?php
if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm'] != 4){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

require_once "./controladores/interaccionControlador.php";
$ctrl = new interaccionControlador();

$panel = $ctrl->panel_general_interacciones_controlador_php();
$topCursos = $ctrl->top_cursos_del_mes_php();
$interacciones = $ctrl->interacciones_recientes_php(10); // muestra 10 registros
?>


<?php
$pdo = mainModel::conectar();

// 1. Cursos activos sin publicidad
$sqlCursos = $pdo->query("
    SELECT id_curso, nombre_curso, tipo_curso
    FROM curso
    WHERE estado_curso = 1
      AND id_curso NOT IN (
          SELECT id_curso FROM curso_publicidad
      )
    ORDER BY nombre_curso ASC
");
$cursos = $sqlCursos->fetchAll(PDO::FETCH_ASSOC);
$conteoPorTipo = [];
foreach ($cursos as $curso) {
    $tipo = $curso['tipo_curso'];
    $conteoPorTipo[$tipo] = ($conteoPorTipo[$tipo] ?? 0) + 1;
}
$labels = json_encode(array_keys($conteoPorTipo));
$data   = json_encode(array_values($conteoPorTipo));

// 2. Visitas totales por día
$sqlVisitasDia = $pdo->query("
    SELECT 
      DATE(STR_TO_DATE(fecha_hora_entrada, '%Y-%m-%d %H:%i:%s')) AS dia,
      COUNT(*) AS visitas
    FROM visitante
    GROUP BY dia
    ORDER BY dia
");
$rowsVisitasDia = $sqlVisitasDia->fetchAll(PDO::FETCH_ASSOC);
$labels1 = json_encode(array_column($rowsVisitasDia, 'dia'));
$data1   = json_encode(array_column($rowsVisitasDia, 'visitas'));

// 3. Visitantes únicos por día
$sqlUnicosDia = $pdo->query("
    SELECT 
      DATE(STR_TO_DATE(fecha_hora_entrada, '%Y-%m-%d %H:%i:%s')) AS dia,
      COUNT(DISTINCT id_ip_fk) AS unicos
    FROM visitante
    GROUP BY dia
    ORDER BY dia
");
$rowsUnicosDia = $sqlUnicosDia->fetchAll(PDO::FETCH_ASSOC);
$labels2 = json_encode(array_column($rowsUnicosDia, 'dia'));
$data2   = json_encode(array_column($rowsUnicosDia, 'unicos'));

// 4. Distribución horaria de visitas
$sqlHoras = $pdo->query("
    SELECT 
      HOUR(STR_TO_DATE(fecha_hora_entrada, '%Y-%m-%d %H:%i:%s')) AS hora,
      COUNT(*) AS visitas
    FROM visitante
    GROUP BY hora
    ORDER BY hora
");
$rowsHoras = $sqlHoras->fetchAll(PDO::FETCH_ASSOC);
$labels4 = json_encode(array_column($rowsHoras, 'hora'));
$data4   = json_encode(array_column($rowsHoras, 'visitas'));

// 5. Top 10 cursos por clicks e IPs únicas
$sqlTopCursos = $pdo->query("
    SELECT c.nombre_curso, 
           COUNT(*) AS clicks, 
           COUNT(DISTINCT ib.id_ip_fk) AS unicos
    FROM interaccion_banner ib
    JOIN curso c ON ib.id_curso_fk = c.id_curso
    GROUP BY c.id_curso
    ORDER BY clicks DESC
    LIMIT 10
");
$rowsTopCursos = $sqlTopCursos->fetchAll(PDO::FETCH_ASSOC);
$labelsTopCursos = json_encode(array_column($rowsTopCursos, 'nombre_curso'));
$clicks = json_encode(array_column($rowsTopCursos, 'clicks'));
$unicos = json_encode(array_column($rowsTopCursos, 'unicos'));

// 6. Clicks por tipo de curso
$sqlClicksTipo = $pdo->query("
    SELECT 
      c.tipo_curso,
      COUNT(*) AS clicks
    FROM interaccion_banner ib
    JOIN curso c ON ib.id_curso_fk = c.id_curso
    GROUP BY c.tipo_curso
    ORDER BY clicks DESC
");
$rowsClicksTipo = $sqlClicksTipo->fetchAll(PDO::FETCH_ASSOC);
$labelsType = json_encode(array_column($rowsClicksTipo, 'tipo_curso'));
$dataType   = json_encode(array_column($rowsClicksTipo, 'clicks'));
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
                                <h6>Última visita</h6>
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

        <!-- Top 10 cursos->
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
                            <tr><th>#</th><th>Curso</th><th>IP</th><th>Tipo</th><th>Fecha/Hora</th></tr>
                        </thead>
                        <tbody>
                            <php if(count($interacciones) > 0): ?>
                                <php foreach($interacciones as $index => $row): ?>
                                    <tr>
                                        <td><?= $index+1 ?></td>
                                        <td><?= htmlspecialchars($row['nombre_curso']) ?></td>
                                        <td><?= htmlspecialchars($row['direccion_ip']) ?></td>
                                        <td><?= htmlspecialchars($row['tipo_banner']) ?></td>
                                        <td><?= htmlspecialchars($row['fecha_hora']) ?></td>
                                    </tr>
                                <php endforeach; ?>
                            <php else: ?>
                                <tr><td colspan="5">No hay interacciones registradas.</td></tr>
                            <php endif; ?>
                        </tbody>
                    </table>
                </div>
            </fieldset>
        </div-->
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
<!--visitas al dia-->


<script>
document.addEventListener('DOMContentLoaded', () => {
  // Gráfico: Visitas por día
  new Chart(
    document.getElementById('chartVisitasDia').getContext('2d'),
    {
      type: 'bar',
      data: {
        labels: <?php echo $labels1; ?>,
        datasets: [{
          label: 'Visitas por día',
          data: <?php echo $data1; ?>,
          backgroundColor: '#007bff'
        }]
      },
      options: {
        responsive: true,
        scales: { y: { beginAtZero: true } },
        plugins: {
          title: {
            display: true,
            text: 'Visitas diarias'
          }
        }
      }
    }
  );

  // Gráfico: Visitantes únicos por día
  new Chart(
    document.getElementById('chartUnicosDia').getContext('2d'),
    {
      type: 'line',
      data: {
        labels: <?php echo $labels2; ?>,
        datasets: [{
          label: 'Visitantes únicos',
          data: <?php echo $data2; ?>,
          borderColor: '#28a745',
          fill: false
        }]
      },
      options: {
        responsive: true,
        scales: { y: { beginAtZero: true } },
        plugins: {
          title: {
            display: true,
            text: 'Visitantes únicos diarios'
          }
        }
      }
    }
  );

  // Gráfico: Top 10 cursos por clicks y visitantes únicos
  new Chart(
    document.getElementById('chartTopCursos').getContext('2d'),
    {
      type: 'bar',
      data: {
        labels: <?php echo $labelsTopCursos; ?>,
        datasets: [
          {
            label: 'Total Clicks',
            data: <?php echo $clicks; ?>,
            backgroundColor: '#007bff'
          },
          {
            label: 'Clickers únicos',
            data: <?php echo $unicos; ?>,
            backgroundColor: '#28a745'
          }
        ]
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true, ticks: { stepSize: 1 } }
        },
        plugins: {
          title: {
            display: true,
            text: 'Top 10 cursos por clicks y visitantes únicos'
          }
        }
      }
    }
  );

  // Gráfico: Distribución de clicks por tipo de curso
  new Chart(
    document.getElementById('chartClicksByType').getContext('2d'),
    {
      type: 'pie',
      data: {
        labels: <?php echo $labelsType; ?>,
        datasets: [{
          label: 'Clicks por tipo',
          data: <?php echo $dataType; ?>,
          backgroundColor: [
            '#007bff',
            '#28a745',
            '#ffc107',
            '#dc3545',
            '#6f42c1',
            '#20c997'
          ]
        }]
      },
      options: {
        responsive: true,
        plugins: {
          title: {
            display: true,
            text: 'Distribución de clicks por tipo de curso'
          },
          legend: {
            position: 'right'
          }
        }
      }
    }
  );
});
</script>







