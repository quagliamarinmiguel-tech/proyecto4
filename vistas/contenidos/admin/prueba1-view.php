<?php
if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4) {
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

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








<!--div class="container-fluid mt-4">
<!--fecha y hora->
<canvas id="chartHora"></canvas>
</div-->

<!--clicks a cada curso por hora->
<div class="container-fluid mt-4">
<canvas id="chartClicksTasa"></canvas>
</div-->


<!--clicks a cada curso top->
<div class="container-fluid mt-4">
<canvas id="chartTopCursos"></canvas>
</div-->

<!--tipo curso->
<div class="container-fluid mt-4">
  <canvas id="chartClicksByType" style="max-width:600px;"></canvas>
</div-->









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









<!--script>
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
        plugins: { title: { display: true, text: 'Visitas diarias' } }
      }
    }
  );
</script>




<script>
//visitas unicas por dia
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
        plugins: { title: { display: true, text: 'Visitantes únicos diarios' } }
      }
    }
  );
</script>

<script>
//top cursos clicados por visita unica
const ctx = document.getElementById('chartTopCursos').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?php echo $labels; ?>,
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
});
</script>

<script>
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
</script-->












































<!--script>
//fecha y hora en que entran
  new Chart(
    document.getElementById('chartHora').getContext('2d'),
    {
      type: 'pie',
      data: {
        labels: <?php echo $labels4; ?>,
        datasets: [{
          label: 'Visitas por hora',
          data: <?php echo $data4; ?>,
          backgroundColor: [
            '#007bff','#28a745','#ffc107','#dc3545',
            '#6f42c1','#20c997','#17a2b8','#fd7e14'
          ]
        }]
      },
      options: {
        responsive: true,
        plugins: { title:{ display:true, text:'Distribución horaria de visitas' } }
      }
    }
  );
</script-->

<!--script>
  // Las variables PHP ya contienen arrays JSON
  const labels = <?php echo $labels; ?>;
  const data   = <?php echo $data;   ?>;
  
  const ctx = document.getElementById('graficoCursos').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Cursos sin publicidad por tipo',
        data: data,
        backgroundColor: [
          '#007bff','#28a745','#ffc107','#dc3545','#6f42c1'
        ]
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1 } }
      },
      plugins: {
        title: {
          display: true,
          text: 'Cantidad de cursos activos sin publicidad, por tipo'
        },
        legend: { display: false }
      }
    }
  });
</script-->



<!--script> este creo que funciona pero no es necesario
  new Chart(
    document.getElementById('graficoCursos').getContext('2d'),
    {
      type: 'bar',
      data: {
        labels: <?php echo $labels; ?>,
        datasets: [{
          label: 'Cursos sin publicidad por tipo',
          data: <?php echo $data; ?>,
          backgroundColor: [
            '#007bff',
            '#28a745',
            '#ffc107',
            '#dc3545',
            '#6f42c1'
          ]
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        },
        plugins: {
          title: {
            display: true,
            text: 'Cantidad de cursos activos sin publicidad, por tipo'
          },
          legend: {
            display: false
          }
        }
      }
    }
  );
</script-->































































<!--script>
//clicks por curso
  const ctx3 = document.getElementById('chartClicksTasa').getContext('2d');
  new Chart(ctx3, {
    type: 'bar',
    data: {
      labels: <?php echo $labels3; ?>,
      datasets: [
        {
          label: 'Clicks',
          data: <?php echo $clicks; ?>,
          backgroundColor: '#ffc107'
        },
        {
          label: 'Tasa (%)',
          data: <?php echo $tasas; ?>,
          type: 'line',
          borderColor: '#dc3545',
          order: 0,
          yAxisID: 'y2'
        }
      ]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true, position: 'left' },
        y2: {
          beginAtZero: true,
          position: 'right',
          grid: { drawOnChartArea: false }
        }
      },
      plugins: {
        title: { display: true, text: 'Clicks vs Tasa de conversión por curso' }
      }
    }
  });
</script-->

















<!--php 
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
>
<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-plus fa-fw"></i> &nbsp; para pruebas
    </h3>
</div>

<script src="<?php echo SERVERURL; ?>vistas/js/chart.umd.min.js"></script>

<div class="container-fluid">
<canvas id="graficoCursos" width="400" height="200"></canvas>
</div>

<script>
const ctx = document.getElementById('graficoCursos').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Intensivo', 'Extendido', 'Técnico'],
    datasets: [{
      label: 'Cursos por tipo',
      data: [12, 8, 5],
      backgroundColor: ['#007bff', '#28a745', '#ffc107']
    }]
  },
  options: {
    responsive: true,
    plugins: {
      title: {
        display: true,
        text: 'Distribución de cursos vigentes'
      }
    }
  }
});
</script-->