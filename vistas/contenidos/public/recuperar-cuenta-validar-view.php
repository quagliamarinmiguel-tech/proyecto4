


<!--php
session_start(['name' => 'SPM']);

if (!isset($_SESSION['recuperacion_autorizada']) || $_SESSION['recuperacion_autorizada'] !== true) {
    header("Location: " . SERVERURL . "index.php");
    exit();
}

$id_usuario = isset($_GET['id_usuario']) ? mainModel::decryption($_GET['id_usuario']) : null;
$ip = isset($_GET['id_ip']) ? mainModel::decryption($_GET['id_ip']) : null;

if (!$id_usuario || !$ip) {
    header("Location: " . SERVERURL . "index.php");
    exit();
}
?-->


<div class="login-container">
	<div class="login-content">
		<p class="text-center">
			<i class="fas fa-key fa-5x"></i>
		</p>
		<p class="text-center">
			Verificación de código
		</p>
		<p class="text-center">
			<a href="<?php echo SERVERURL; ?>recuperar-cuenta"><i class="fas fa-arrow-left"></i> Volver</a>
		</p>
		<p class="text-center small">Se ha enviado a su correo un código de autenticación temporal de un solo uso que debe ingresar dentro de los próximos 5 minutos.</p>

		<form action="<?php echo SERVERURL; ?>ajax/recuperarAjax.php" method="POST" autocomplete="off">
			<div class="form-group">
				<label for="CodigoOTP" class="bmd-label-floating">
					<i class="fas fa-shield-alt"></i> &nbsp; Código enviado al correo
				</label>
				<p class="text-center small">
					Se ha enviado a su correo un código de autenticación temporal de un solo uso que debe ingresar dentro de los próximos 5 minutos.
				</p>
				<input type="text" class="form-control" id="CodigoOTP" name="codigo_otp" pattern="[0-9]{6}" maxlength="6" inputmode="numeric" required>
			</div>

			<!-- Campos ocultos -->
			<input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
			<input type="hidden" name="direccion_ip" value="<?php echo $ip; ?>">

			<button type="submit" class="btn-login text-center">Validar código</button>
		</form>

	<div class="text-center mt-4">
		<a href="<?php echo SERVERURL; ?>?views=login" class="btn btn-secondary">
			<i class="fas fa-sign-in-alt"></i> Volver al login
		</a>
		&nbsp;
		<form id="form-reenviar-codigo" method="POST" action="<?php echo SERVERURL; ?>ajax/recuperarAjax.php">
			<input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
			<input type="hidden" name="direccion_ip" value="<?php echo $ip; ?>">
			<button type="submit" class="btn btn-warning">
				<i class="fas fa-sync-alt"></i> Reenviar código
			</button>
		</form>
	</div>

	</div>
</div>