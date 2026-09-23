<?php  
    if($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }

    // Validar parámetro obligatorio
    if (!isset($_GET['curso_vigente']) || !is_numeric($_GET['curso_vigente'])) {
        echo '<div class="alert alert-danger text-center">No se especificó el curso vigente.</div>';
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







    $curso_vigente_id = (int) $_GET['curso_vigente'];













 // Obtener datos del curso vigente
    require_once "./modelos/mainModel.php";
    $pdo = mainModel::conectar();
    
    $stmt = $pdo->prepare("
        SELECT 
            cv.id_curso_vigente,
            c.nombre_curso,
            c.tipo_curso,
            ch.numero_cohorte,
            ch.anio_cohorte,
            ch.tipo_cohorte,
            pi.fecha_inicio_int,
            pi.fecha_fin_int,
            CASE 
                WHEN cv.id_cohorte_fk IS NOT NULL THEN 'cohorte'
                ELSE 'intensivo'
            END AS tipo_curso_vigente
        FROM curso_vigente cv
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        LEFT JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
        LEFT JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk
        WHERE cv.id_curso_vigente = :curso_vigente_id
    ");
    $stmt->execute([':curso_vigente_id' => $curso_vigente_id]);
    $curso_vigente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$curso_vigente) {
        echo '<div class="alert alert-danger text-center">Curso vigente no encontrado.</div>';
        exit();
    }






?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-archive fa-fw"></i> &nbsp; SECCIONES ARCHIVADAS
    </h3>
    <p class="text-justify">
        Esta vista muestra todas las secciones archivadas del periodo de curso seleccionado. 
    </p>
</div>




<!-- Tarjeta con información del curso vigente -->
<div class="container-fluid mb-4">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-info-circle"></i> &nbsp; Información del Periodo de Curso</h5>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Curso:</strong> <?php echo $curso_vigente['nombre_curso']; ?></p>
                    <p><strong>ID del periodo del curso:</strong> <?php echo $curso_vigente['id_curso_vigente']; ?></p>
                    <p><strong>Tipo de curso:</strong> <?php echo $curso_vigente['tipo_curso']; ?></p>
                    
                    <?php if (!is_null($curso_vigente['numero_cohorte']) && !is_null($curso_vigente['anio_cohorte'])): ?>
                        <p><strong>Cohorte:</strong> <?php echo $curso_vigente['numero_cohorte'] . " / " . $curso_vigente['anio_cohorte']; ?></p>
                        <p><strong>Tipo de cohorte:</strong> <?php echo $curso_vigente['tipo_cohorte']; ?></p>
                    <?php else: ?>
                        <p><strong>Fecha de inicio del periodo intensivo:</strong> <?php echo date('d/m/Y', strtotime($curso_vigente['fecha_inicio_int'])); ?></p>
                        <p><strong>Fecha de culminación del periodo intensivo:</strong> <?php echo date('d/m/Y', strtotime($curso_vigente['fecha_fin_int'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<!--div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">


    </ul>   
</div-->


<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS </a>
            <!--a href="<?php echo SERVERURL; ?>course-list-archived"><i class="fas fa-plus fa-fw"></i> &nbsp; LISTA DE CURSOS ARCHIVADOS</a-->
        </li>

        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PERIODOS DE COHORTES </a>
            <!--a class="active" href="<?php echo SERVERURL; ?>seccion-archived"><i class="fas fa-plus fa-fw"></i> &nbsp; LISTA DE PERIODOS ARCHIVADOS</a-->
        </li>
        
        <li>
            <a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE SECCIONES ARCHIVADAS</a>
            <!--a href="<?php echo SERVERURL; ?>index.php?views=seccion-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PERIODOS DE CURSOS</a-->
        </li>
        
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cv-int-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PERIODOS INTENSIVOS </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cohorte-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE COHORTES </a>
            <!--a href="<?php echo SERVERURL; ?>cohorte-list-archived"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE COHORTES ARCHIVADOS</a-->
        </li>

    </ul>   
</div>










<div class="container-fluid">
    <?php
        require_once "./controladores/seccionControlador.php";
        $ins_seccion = new seccionControlador();

        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        // Llamada al paginador con solo_archivadas activado
        echo $ins_seccion->paginador_seccion_archivadas_controlador22($pagina, 10, $curso_vigente_id);
    ?>
</div>






