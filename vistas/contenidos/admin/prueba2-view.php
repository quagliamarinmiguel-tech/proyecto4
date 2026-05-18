<?php
//require_once "../modelos/mainModel.php";
$pdo = mainModel::conectar();
$fechaLimite = date('Y-m-d', strtotime('-12 months'));

// 1. Obtener curso_vigente válidos en ese rango
$sql = $pdo->prepare("
  SELECT cv.id_curso_vigente, c.nombre_curso, ch.id_cohorte, ch.numero_cohorte, ch.anio_cohorte, ch.tipo_cohorte, ch.fecha_inicio
  FROM curso_vigente cv
  JOIN curso c ON cv.id_curso_fk = c.id_curso
  JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
  WHERE c.estado_curso = 1
    AND cv.estado_curso_vigente = 1
    AND ch.archivar_cohorte = 1
    AND ch.fecha_inicio >= :fechaLimite
");
$sql->execute([':fechaLimite' => $fechaLimite]);
$cursoVigente = $sql->fetchAll(PDO::FETCH_ASSOC);

// 2. Contar inscripciones por cohorte única y por curso
$conteoCohorte = [];  // clave: id_cohorte
$labelsCohorte = [];  // clave: id_cohorte → etiqueta
$conteoCurso = [];

foreach ($cursoVigente as $cv) {
  $idCV = $cv['id_curso_vigente'];
  $idCohorte = $cv['id_cohorte'];
  $nombreCurso = $cv['nombre_curso'];
  $cohorteLabel = "Cohorte {$cv['numero_cohorte']} {$cv['tipo_cohorte']} - {$cv['anio_cohorte']}";

  // contar inscripciones por curso_vigente
  $sqlIns = $pdo->prepare("SELECT COUNT(*) FROM inscripcion WHERE id_curso_vigente_fk = :id");
  $sqlIns->execute([':id' => $idCV]);
  $total = (int) $sqlIns->fetchColumn();

  // agrupar por cohorte única
  $conteoCohorte[$idCohorte] = ($conteoCohorte[$idCohorte] ?? 0) + $total;
  $labelsCohorte[$idCohorte] = $cohorteLabel;

  // agrupar por curso
  $conteoCurso[$nombreCurso] = ($conteoCurso[$nombreCurso] ?? 0) + $total;
}

// 4. Ordenar y limitar a los 10 más altos
arsort($conteoCohorte);
arsort($conteoCurso);

// Obtener los 10 cohorte IDs más altos
$topCohorteIDs = array_slice(array_keys($conteoCohorte), 0, 10);
// Obtener los 10 cursos con más inscritos
$labelsCurso = array_slice(array_keys($conteoCurso), 0, 10);
$dataCurso = array_slice(array_values($conteoCurso), 0, 10);

// Usar esos IDs para construir etiquetas legibles
$labelsCohorteFinal = [];
$dataCohorteFinal = [];

foreach ($topCohorteIDs as $id) {
  $labelsCohorteFinal[] = $labelsCohorte[$id];   // etiqueta legible
  $dataCohorteFinal[] = $conteoCohorte[$id];     // cantidad
}


$datasetCohorte = [[
  "label" => "Inscritos por Cohorte",
  "data" => $dataCohorteFinal,
  "backgroundColor" => "#007bff"
]];



$datasetCurso = [[
  "label" => "Inscritos por Curso",
  "data" => $dataCurso,
  "backgroundColor" => "#28a745"
]];
?>





<!--
// 4. Ordenar y limitar a los 10 más altos
arsort($conteoCohorte);
arsort($conteoCurso);

$labelsCohorte = array_slice(array_keys($conteoCohorte), 0, 10);
$dataCohorte = array_slice(array_values($conteoCohorte), 0, 10);

$labelsCurso = array_slice(array_keys($conteoCurso), 0, 10);
$dataCurso = array_slice(array_values($conteoCurso), 0, 10);

// 5. Preparar datasets para Chart.js
$datasetCohorte = [[
  "label" => "Inscritos por Cohorte",
  "data" => $dataCohorte,
  "backgroundColor" => "#007bff"
]];-->


  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <script src="<?php echo SERVERURL; ?>vistas/js/chart.umd.min.js"></script>

<!--div class="container-fluid mt-4" style="height:120vh;">
  <div class="d-flex flex-column justify-content-between" style="height:100%;">
    
    <div class="flex-fill" style="height:45%;">
      <h4 class="text-center">📊 Cohortes con más inscritos (últimos 12 meses)</h4>
      <canvas id="chartCohorte" style="width:100%; height:100%;"></canvas>
    </div>
      <div style="height:5%;"></div>
    <div class="flex-fill" style="height:45%;">
      <h4 class="text-center">📘 Cursos con más inscritos (últimos 12 meses)</h4>
      <canvas id="chartCurso" style="width:100%; height:100%;"></canvas>
    </div>

  </div>
</div-->


<!--div class="container-fluid mt-4" style="height:120vh;">
  <div class="d-flex flex-column justify-content-between" style="height:100%;">
    
    <div class="flex-fill" style="height:45%;">
      <h4 class="text-center">📊 Cohortes con más inscritos (últimos 12 meses)</h4>
      <canvas id="chartCohorte" style="width:45%; height:100%;"></canvas>
    </div>
      <div style="height:5%;"></div>
    <div class="flex-fill" style="height:45%;">
      <h4 class="text-center">📘 Cursos con más inscritos (últimos 12 meses)</h4>
      <canvas id="chartCurso" style="width:45%; height:100%;"></canvas>
    </div>

  </div>
</div-->

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

<div style="height:20vh;"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  new Chart(document.getElementById('chartCohorte').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labelsCohorteFinal); ?>,
//      labels: <php echo json_encode($labelsCohorte); ?>,
      datasets: <?php echo json_encode($datasetCohorte); ?>
    },
    options: {
      responsive: true,
      plugins: {
        title: {
          display: true,
          text: 'Inscripciones por Cohorte'
        },
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true },
        x: { title: { display: true, text: 'Cohorte' } }
      }
    }
  });

  new Chart(document.getElementById('chartCurso').getContext('2d'), {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($labelsCurso); ?>,
      datasets: <?php echo json_encode($datasetCurso); ?>
    },
    options: {
      responsive: true,
      plugins: {
        title: {
          display: true,
          text: 'Inscripciones por Curso'
        },
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true },
        x: { title: { display: true, text: 'Curso' } }
      }
    }
  });
});
</script>





