<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<?php
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }

    $idCursoVigenteEncriptado = isset($_GET['curso_vigente_id']) ? $_GET['curso_vigente_id'] : "";
    $idCursoVigente = mainModel::decryption($idCursoVigenteEncriptado);


//obtene profe de seccion A registrado
    $pdo = mainModel::conectar();
    $stmt = $pdo->prepare("
        SELECT ci_profesor 
        FROM seccion 
        WHERE id_curso_vigente_fk = :id AND nombre_seccion = 'A'
        LIMIT 1
    ");
    $stmt->bindParam(":id", $idCursoVigente, PDO::PARAM_INT);
    $stmt->execute();
    $profesorActual = $stmt->fetchColumn(); // Devuelve el CI del profesor


    require_once "./controladores/cohorteControlador.php";
    $ctrl = new cohorteControlador();
    $datos = $ctrl->datos_curso_vigente_controlador("Unico", $idCursoVigenteEncriptado);

    if ($datos->rowCount() == 1) {
        $cursoVigente = $datos->fetch();
        $idCurso = $cursoVigente['id_curso_fk'];
        $idCohorte = $cursoVigente['id_cohorte_fk'];
        $oferta = $cursoVigente['oferta'];
    } 
    
  

    else {
echo '<div class="alert alert-danger">';
echo '<strong>Curso vigente no encontrado.</strong><br>';
echo 'ID desencriptado: ' . htmlspecialchars($idCursoVigente) . '<br>';
echo 'Resultado de consulta: ' . ($datos ? $datos->rowCount() : 'Consulta fallida');
echo '</div>';
exit();
    }

    // Obtener datos del curso base
    require_once "./controladores/cursoControlador.php";
    $cursoCtrl = new cursoControlador();
    $main = new mainModel();
    $cursoDatos = $cursoCtrl->datos_curso_controlador("Unico", $main->encryption($idCurso));
    //$cursoDatos = $cursoCtrl->datos_curso_controlador("Unico", mainModel::encryption($idCurso));
    $curso = $cursoDatos->fetch();

    // Obtener datos del cohorte destino
    require_once "./controladores/cohorteControlador.php";
    $cohorteCtrl = new cohorteControlador();
    $cohorteDatos = $cohorteCtrl->datos_cohorte_controlador("Unico", $main->encryption($idCohorte));
    $cohorte = $cohorteDatos->fetch();

    // Obtener profesores
    $profesores = $cohorteCtrl->listar_profesores_controlador();


?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-edit fa-fw"></i> &nbsp; ACTUALIZAR CURSO VIGENTE
    </h3>
    <p class="text-justify">
        Solo puedes modificar la oferta y el docente asignado. El curso base y el cohorte destino no pueden cambiarse.
    </p>
</div>

<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/cursoVigenteAjax.php" method="POST" data-form="update" autocomplete="off">
        <!--input type="hidden" name="curso_vigente_id_up" value="<hp echo mainModel::encryption($idCursoVigente); ?>"-->
        <input type="hidden" name="curso_vigente_id_up" value="<?php echo htmlspecialchars($idCursoVigente); ?>">
        <fieldset>
            <legend><i class="fas fa-book fa-fw"></i> &nbsp; Información del curso vigente</legend>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4 mb-2">
<!---->
                        <label class="form-label">Curso</label>
                        <div class="form-control text-muted fw-bold">
                            <?php echo $curso['nombre_curso'] . " (" . $curso['tipo_curso'] . ")"; ?>
                        </div>
                    </div>

                    <div class="col-md-4 mb-2">
                        <label class="form-label">Cohorte destino</label>
                        <div class="form-control text-muted fw-bold">
                            <?php echo $cohorte['numero_cohorte'] . "." . $cohorte['anio_cohorte']; ?>
                        </div>
                    </div>

                    <div class="col-md-4 mb-2">
                        <label class="form-label">Oferta (%)</label>
                        <input type="number" name="oferta_up" class="form-control" value="<?php echo $oferta; ?>" min="0" max="100" required>
                    </div>

                    <div class="col-md-4 mb-2">
                        <label class="form-label">Docente de la sección "A"</label>
                        <select name="id_profesor_fk_up" class="form-select" required>
                            <option value="" disabled>Seleccione...</option>
                            <?php foreach($profesores as $p): ?>
                                <option value="<?php echo $p['id_profesor']; ?>"
                                    <?php echo ($p['id_profesor'] == $profesorActual) ? 'selected' : ''; ?>>
                                    <?php echo $p['nombre_profesor'] . " " . $p['apellido_profesor']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>
            </div>
        </fieldset>

        <p class="text-center" style="margin-top: 40px;">
            <button type="submit" class="btn btn-raised btn-info btn-sm">
                <i class="far fa-save"></i> &nbsp; ACTUALIZAR
            </button>
        </p>
    </form>
</div>