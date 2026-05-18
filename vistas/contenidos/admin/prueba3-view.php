<?php 

$pdo = mainModel::conectar();


$sqlCohortes = $pdo->query("
  SELECT id_cohorte, numero_cohorte, anio_cohorte
  FROM cohorte
  WHERE fecha_inicio BETWEEN CURDATE() - INTERVAL 12 MONTH AND CURDATE()
    AND archivar_cohorte = 1
  ORDER BY fecha_inicio ASC
");

$cohortes = $sqlCohortes->fetchAll(PDO::FETCH_ASSOC);
$labels = [];
$montos = [];

foreach ($cohortes as $cohorte) {
  $idCohorte = $cohorte['id_cohorte'];
  $nombre = "Cohorte {$cohorte['numero_cohorte']}-{$cohorte['anio_cohorte']}";
  $labels[] = $nombre;

  // Buscar curso_vigente válidos
  $sqlCV = $pdo->prepare("
    SELECT id_curso_vigente
    FROM curso_vigente
    WHERE id_cohorte_fk = :id AND estado_curso_vigente = 1
  ");
  $sqlCV->execute([':id' => $idCohorte]);
  $cursoVigenteIds = $sqlCV->fetchAll(PDO::FETCH_COLUMN);

  $inscripcionesValidas = [];

  foreach ($cursoVigenteIds as $idCV) {
    $sqlIns = $pdo->prepare("
      SELECT id_inscripcion
      FROM inscripcion
      WHERE id_curso_vigente_fk = :id AND archivada = 1
    ");
    $sqlIns->execute([':id' => $idCV]);
    $inscripcionesValidas = array_merge($inscripcionesValidas, $sqlIns->fetchAll(PDO::FETCH_COLUMN));
  }

  $total = 0;
  if ($inscripcionesValidas) {
    $placeholders = implode(',', array_fill(0, count($inscripcionesValidas), '?'));
    $sqlMontos = $pdo->prepare("
      SELECT SUM(monto) FROM transaccion
      WHERE id_inscripcion IN ($placeholders)
    ");
    $sqlMontos->execute($inscripcionesValidas);
    $total = (float) $sqlMontos->fetchColumn();
  }

  $montos[] = $total;
}


$dataset = [[
  "label" => "Monto total recaudado por cohorte",
  "data" => $montos,
  "backgroundColor" => "#6f42c1"
]];






?>






<div class="container mt-4">
  <h5 class="text-center">💸 Recaudación total por cohorte (últimos 12 meses)</h5>
  <canvas id="chartMontosCohorte"></canvas>
</div>
<script src="<?php echo SERVERURL; ?>vistas/js/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  new Chart(document.getElementById('chartMontosCohorte').getContext('2d'), {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($labels); ?>,
      datasets: <?php echo json_encode($dataset); ?>
    },
    options: {
      responsive: true,
      plugins: {
        title: {
          display: true,
          text: 'Monto total recaudado por cohorte'
        },
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          title: { display: true, text: 'Monto en dls.' }
        },
        x: {
          title: { display: true, text: 'Cohortes' }
        }
      }
    }
  });
});
</script>














