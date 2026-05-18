<?php
	if($_SESSION['cargo_spm']!=1){
		echo $lc->forzar_cierre_sesion_controlador();
		exit();
	}
?>
<div class="full-box page-header">
	<h3 class="text-left">
		<i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO USUARIO
	</h3>
	<p class="text-justify">
		Lorem ipsum dolor sit amet, consectetur adipisicing elit. Suscipit nostrum rerum animi natus beatae ex. Culpa blanditiis tempore amet alias placeat, obcaecati quaerat ullam, sunt est, odio aut veniam ratione.
	</p>
</div>

<div class="container-fluid">
	<ul class="full-box list-unstyled page-nav-tabs">
		<li>
			<a class="active" href="<?php echo SERVERURL; ?>user-new/"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO USUARIO</a>
		</li>
		<li>
			<a href="<?php echo SERVERURL; ?>user-list/"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE USUARIOS</a>
		</li>
		<li>
			<a href="<?php echo SERVERURL; ?>user-search/"><i class="fas fa-search fa-fw"></i> &nbsp; BUSCAR USUARIO</a>
		</li>
	</ul>	
</div>

<div class="container-fluid">
	<form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/usuarioAjax2.php" method="POST" data-form="save" autocomplete="off">
		<fieldset>
			<legend><i class="far fa-address-card"></i> &nbsp; Información personal</legend>
			<div class="container-fluid">
				<div class="row">
					
					<div class="col-12 col-md-4">
						<div class="form-group">
							<label for="usuario_nombre" class="bmd-label-floating">Nombres</label>
							<input type="text" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,35}" class="form-control" name="usuario_nombre_reg" id="usuario_nombre" maxlength="35" required="" >
						</div>
					</div>

				</div>
			</div>
		</fieldset>
		<br><br><br>
		<fieldset>
			<legend><i class="fas fa-user-lock"></i> &nbsp; Información de la cuenta</legend>
			<div class="container-fluid">
				<div class="row">


					<div class="col-12 col-md-6">
						<div class="form-group">
							<label for="usuario_clave_1" class="bmd-label-floating">Contraseña</label>
							<input type="password" class="form-control" name="usuario_clave_1_reg" id="usuario_clave_1" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required="" >
						</div>
					</div>
					<div class="col-12 col-md-6">
						<div class="form-group">
							<label for="usuario_clave_2" class="bmd-label-floating">Repetir contraseña</label>
							<input type="password" class="form-control" name="usuario_clave_2_reg" id="usuario_clave_2" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required="" >
						</div>
					</div>
				</div>
			</div>
		</fieldset>
		<br><br><br>
		<fieldset>
			<legend><i class="fas fa-medal"></i> &nbsp; Nivel de privilegio</legend>
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<p><span class="badge badge-info">Administrador principal</span> Permisos para registrar, actualizar y eliminar</p>
						<p><span class="badge badge-success">Control de estudio</span> Permisos para administrar las calificaciones de los estudiantes y consultar registros de estudiante, inscripcion, transaccion, seccion, curso y cohorte</p>
						<p><span class="badge badge-dark">Administracion de pagos</span> Permisos para administrar los registros de estudiantes, sus inscripciones y transacciones</p>
						<p><span class="badge badge-success">Supervisor de cursos</span> Permisos para administrar secciones, cursos, cohortes y el portal publicitario de CEPROCA</p>
						<p><span class="badge badge-info">Estudiante</span> Permisos de estudiante para consultar calificaciones, cursos, datos personales, sus inscripciones y transacciones</p>

						<div class="form-group">
							<select class="form-control" name="usuario_privilegio_reg">
								<option value="" selected="" disabled="">Seleccione una opción</option>
                                <option value="1">Administrador principal</option>

                                <option value="2">Control de estudio</option>

                                <option value="3">Administración de pagos</option>

                                <option value="4">Supervisor de cursos</option>

                                <option value="5">Estudiante</option>
							</select>
						</div>
					</div>
				</div>
			</div>
		</fieldset>
		<p class="text-center" style="margin-top: 40px;">
			<button type="reset" class="btn btn-raised btn-secondary btn-sm"><i class="fas fa-paint-roller"></i> &nbsp; LIMPIAR</button>
			&nbsp; &nbsp;
			<button type="submit" class="btn btn-raised btn-info btn-sm"><i class="far fa-save"></i> &nbsp; GUARDAR</button>
		</p>
	</form>
</div>