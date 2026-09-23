<?php
require_once "./config/APP.php";
require_once "./modelos/mainModel.php";

$pdo = mainModel::conectar();

// 1. Cohortes activas en los últimos 12 meses
$sqlCohortes = $pdo->query("
  SELECT id_cohorte, numero_cohorte, anio_cohorte
  FROM cohorte
  WHERE fecha_inicio BETWEEN CURDATE() - INTERVAL 12 MONTH AND CURDATE()
    AND archivar_cohorte = 1
  ORDER BY fecha_inicio ASC
");

$cohortes = $sqlCohortes->fetchAll(PDO::FETCH_ASSOC);
$labels = [];
$promedios = [];

foreach ($cohortes as $cohorte) {
  $idCohorte = $cohorte['id_cohorte'];
  $nombre = "Cohorte {$cohorte['numero_cohorte']}-{$cohorte['anio_cohorte']}";
  $labels[] = $nombre;

  // 2. Cursos vigentes de la cohorte
  $sqlCV = $pdo->prepare("
    SELECT id_curso_vigente
    FROM curso_vigente
    WHERE id_cohorte_fk = :id AND estado_curso_vigente = 1
  ");
  $sqlCV->execute([':id' => $idCohorte]);
  $cursoVigenteIds = $sqlCV->fetchAll(PDO::FETCH_COLUMN);

  $notasFinales = [];

  foreach ($cursoVigenteIds as $idCV) {
    // 3. Inscripciones archivadas
    $sqlIns = $pdo->prepare("
      SELECT id_inscripcion
      FROM inscripcion
      WHERE id_curso_vigente_fk = :id AND archivada = 1
    ");
    $sqlIns->execute([':id' => $idCV]);
    $inscripciones = $sqlIns->fetchAll(PDO::FETCH_COLUMN);

    foreach ($inscripciones as $idInscripcion) {
      // 4. Calcular nota final si evaluaciones suman 100%
      $stmtNota = $pdo->prepare("
        SELECT SUM(
          CASE
            WHEN pe.puntaje = 'NP' THEN 0
            ELSE CAST(pe.puntaje AS DECIMAL(5,2))
          END * e.porcentaje / 100
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

$dataset = [[
  "label" => "Promedio de calificaciones por cohorte",
  "data" => $promedios,
  "backgroundColor" => "#198754"
]];
?>

<div class="container mt-4">
  <h5 class="text-center">📊 Promedio de calificaciones por cohorte (últimos 12 meses)</h5>
  <canvas id="chartNotasCohorte"></canvas>
</div>

<script src="<?php echo SERVERURL; ?>vistas/js/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  new Chart(document.getElementById('chartNotasCohorte').getContext('2d'), {
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
          text: 'Promedio de calificaciones por cohorte'
        },
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          max: 20,
          title: { display: true, text: 'Calificación promedio' }
        },
        x: {
          title: { display: true, text: 'Cohortes' }
        }
      }
    }
  });
});
</script>
