<?php 
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>
<?php
    require_once "./controladores/cohorteControlador.php";
    $ins_cohorte = new cohorteControlador();

    //$datos = $ins_cohorte->datos_cohorte_controlador("Unico", $pagina[1]);


    $cohorte_id = $_GET['cohorte_id_up'] ?? '';
    $datos = $ins_cohorte->datos_cohorte_controlador("Unico", $cohorte_id);

    if($datos->rowCount() == 1){
        $cohorte = $datos->fetch();
    } else {
        echo "<div class='alert alert-danger text-center'>No se encontró la cohorte.</div>";
        exit();
    }
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-calendar-alt fa-fw"></i> &nbsp; ACTUALIZAR COHORTE
    </h3>
    <p class="text-justify">
        Modifica la información de esta cohorte. Si deseas ajustar sus cursos, utiliza el botón “Ver Cursos” desde el listado principal.
    </p>
</div>

<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/cohorteAjax.php" method="POST" data-form="update" autocomplete="off">
        <!--input type="hidden" name="cohorte_id_up" value="<?php echo $pagina[1]; ?>"-->
        <input type="hidden" name="cohorte_id_up" value="<?php echo $cohorte_id; ?>">
        <fieldset>
            <legend><i class="fas fa-info-circle"></i> Información básica</legend>
            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Tipo</label>
                    <select class="form-control" name="cohorte_tipo_up" required>
                        <option <?php if($cohorte['tipo_cohorte'] == 'Intensivo') echo 'selected'; ?> value="Intensivo">Intensivo</option>
                        <option <?php if($cohorte['tipo_cohorte'] == 'Extendido') echo 'selected'; ?> value="Extendido">Extendido</option>
                        <option <?php if($cohorte['tipo_cohorte'] == 'Técnico Profesional') echo 'selected'; ?> value="Técnico Profesional">Técnico Profesional</option>
                        <option <?php if($cohorte['tipo_cohorte'] == 'Otro') echo 'selected'; ?> value="Otro">Otro</option>
                    </select>
                </div>
                <!--div class="col-md-4 form-group">
                    <label>Estado</label>
                    <select class="form-control" name="cohorte_estado_up" required>
                        <option <?php if($cohorte['estado_cohorte'] == 'Vigente') echo 'selected'; ?> value="Vigente">Vigente</option>
                        <option <?php if($cohorte['estado_cohorte'] == 'Próximo') echo 'selected'; ?> value="Próximo">Próximo</option>
                        <option <?php if($cohorte['estado_cohorte'] == 'Terminado') echo 'selected'; ?> value="Terminado">Terminado</option>
                    </select>
                </div-->
                <div class="col-md-2 form-group">
                    <label>N° Cohorte</label>
                    <input type="number" name="cohorte_numero_up" class="form-control" value="<?php echo $cohorte['numero_cohorte']; ?>" required>
                </div>
                <div class="col-md-2 form-group">
                    <label>Año</label>
                    <input type="number" name="cohorte_anio_up" class="form-control" value="<?php echo $cohorte['anio_cohorte']; ?>" required>
                </div>
            </div>
        </fieldset>

        <br>

        <fieldset>
            <legend><i class="fas fa-calendar-day"></i> Fechas</legend>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Fecha de inicio</label>
                    <input type="date" name="cohorte_fecha_inicio_up" class="form-control" value="<?php echo $cohorte['fecha_inicio']; ?>" required>
                </div>
                <div class="col-md-6 form-group">
                    <label>Fecha de fin</label>
                    <input type="date" name="cohorte_fecha_fin_up" class="form-control" value="<?php echo $cohorte['fecha_fin']; ?>" required>
                </div>
            </div>
        </fieldset>

        <br>

        <fieldset>
            <legend><i class="fas fa-dollar-sign"></i> Pagos</legend>
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Pago Art.13</label>
                    <input type="number" step="0.01" name="cohorte_pago_art13_up" class="form-control" value="<?php echo $cohorte['pago_art13']; ?>" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Fecha Art.13</label>
                    <input type="date" name="cohorte_fecha_pago_art13_up" class="form-control" value="<?php echo $cohorte['fecha_pago_art13']; ?>"  required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Pago Aranceles</label>
                    <input type="number" step="0.01" name="cohorte_pago_aranceles_up" class="form-control" value="<?php echo $cohorte['pago_aranceles']; ?>" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Fecha Aranceles</label>
                    <input type="date" name="cohorte_fecha_pago_aranceles_up" class="form-control" value="<?php echo $cohorte['fecha_pago_aranceles']; ?>"  required>
                </div>
            </div>

            <br>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Módulo 1</label>
                    <input type="date" name="cohorte_fecha_pago_modulo1_up" class="form-control" value="<?php echo $cohorte['fecha_pago_modulo1']; ?>"  required>
                </div>
                <div class="col-md-4 form-group">
                    <label>Módulo 2</label>
                    <input type="date" name="cohorte_fecha_pago_modulo2_up" class="form-control" value="<?php echo $cohorte['fecha_pago_modulo2']; ?>"  required>
                </div>
                <div class="col-md-4 form-group">
                    <label>Módulo 3</label>
                    <input type="date" name="cohorte_fecha_pago_modulo3_up" class="form-control" value="<?php echo $cohorte['fecha_pago_modulo3']; ?>"  required>
                </div>
            </div>
        </fieldset>

        <br><br>

        <p class="text-center">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> ACTUALIZAR</button>
        </p>
    </form>
</div>