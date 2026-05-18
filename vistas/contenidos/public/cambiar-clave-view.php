<?php
session_start(['name' => 'SPM']);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<pre>";
print_r($_GET);
echo "</pre>";


if (!isset($_GET['id_usuario'])) {
    echo "Parámetro 'id_usuario' no recibido.";
    exit();
}
$id_usuario = $_GET['id_usuario'];

//if (!isset($_SESSION['recuperacion_autorizada']) || $_SESSION['recuperacion_autorizada'] !== true ) {
//    header("Location: " . SERVERURL . "index.php");
 //   exit();
//}
    $idiso = $_GET['id_usuario'] ?? '';

$id_usuario = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : null;
//$id_usuario = isset($_GET['id_usuario']) ? mainModel::decryption($_GET['id_usuario']) : null;
$ip = isset($_GET['id_ip']) ? mainModel::decryption($_GET['id_ip']) : null;
//echo $id_usuario;
// Buscar nombre del estudiante


echo "<pre>";
echo "Encriptado: " . $_GET['id_usuario'] . "\n";
echo "Encriptado: " . $idiso . "\n";
//echo "Desencriptado: " . mainModel::decryption($_GET['id_usuario']);
echo "</pre>";
//echo "<pre>";
//var_dump($id_usuario);
//echo "</pre>";
?>



<div class="login-container">
	<div class="login-content">
		<p class="text-center">
			<i class="fas fa-lock fa-5x"></i>
		</p>
		<p class="text-center">
			Establecer nueva contraseña
		</p>
		<p class="text-center">
			<a href="<?php echo SERVERURL; ?>login/"><i class="fas fa-arrow-left"></i> Volver al login</a>
		</p>


<p><?php echo $id_usuario; ?></p>
		<form action="<?php echo SERVERURL; ?>ajax/recuperarAjax.php" method="POST" autocomplete="off">
			<div class="form-group">
				<label for="NuevaClave" class="bmd-label-floating"><i class="fas fa-key"></i> &nbsp; Nueva contraseña</label>
				<input type="password" required pattern="[^ ].*" title="El nombre no debe comenzar con espacio." class="form-control" id="NuevaClave" name="nueva_clave" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required>
			</div>
			<div class="form-group">
				<label for="ConfirmarClave" class="bmd-label-floating"><i class="fas fa-key"></i> &nbsp; Confirmar contraseña</label>
				<input type="password" required pattern="[^ ].*" title="El nombre no debe comenzar con espacio." class="form-control" id="ConfirmarClave" name="confirmar_clave" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required>
			</div>

			<!-- Campo oculto para saber qué usuario actualizar -->
			<input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">

			<button type="submit" class="btn-login text-center">Cambiar contraseña</button>
		</form>
	</div>
</div>