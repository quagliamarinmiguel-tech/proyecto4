<?php
require_once "./controladores/loginControlador.php";
$ctrl = new loginControlador();

// Validar IP bloqueada antes de renderizar
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$bloqueo = loginModelo::verificar_ip_bloqueada_supabase($ip, 'recuperacion');

$ipBloqueada      = !empty($bloqueo['bloqueado']);
$minutosRestantes = $ipBloqueada ? (int) ceil(($bloqueo['tiempo_restante'] ?? 0) / 60) : 0;

// Procesar POST
if (!$ipBloqueada && isset($_POST['correo'])) {
    $ctrl->solicitar_recuperacion_controlador();
}
?>
<script>window.DARK_MODE_BLOQUEADO = true;</script>


<?php
require_once "./controladores/publicoControlador.php";
$localCtrl = new publicoControlador();

//datos visuales de la paigna
$configVisual = mainModel::obtener_config_visual();
$coloresInicio = mainModel::obtener_colores_config();

$nombreCorto    = $configVisual['nombre_corto'];
$nombreCompleto = $configVisual['nombre_completo'];
$rutaIcono      = $configVisual['icono'];
$rutaLogo       = $configVisual['logo'];
$paletaInicioId = $configVisual['paleta_id'];

$primario = $coloresInicio[0];
$secundario = $coloresInicio[1];

$c1 = mainModel::hex2rgba($primario, 0.85);      
$c2 = mainModel::hex2rgba($primario, 1);    
$c3 = mainModel::hex2rgba($secundario, 1);  


?>

<style>
body {
  min-height: 100vh;
  height:auto;
  /*padding: 30px 0px;**/
  padding:1px;
  margin:0px;
  width:100%;
  overflow-x: hidden;
  overflow-y: auto;
  background: <?= $c2 ?>; 
  background: linear-gradient(90deg, <?= $c1 ?> 0%, <?= $c2 ?> 50%, <?= $c3 ?> 99%);
  background-size: cover;
}
.login-container{
	background-color: rgba(0, 0, 0, 0); 
	color: #ffffff; 
	background: rgba(0, 0, 0, 0);
}
html {overflow-y: hidden;}
</style>

<?php if ($ipBloqueada): ?>

    <div class="login-container">
        <div class="login-content text-center" style="width:100%;">
            <p><i class="fas fa-ban fa-5x" style="color:#dc3545;"></i></p>
            <h3 style="color:#dc3545;">Acceso bloqueado</h3>
            <p class="text-danger">
                Demasiados intentos. Intente más tarde en <b><?= $minutosRestantes ?></b> minutos.
            </p>
            <p class="mt-4">
                <a href="<?= SERVERURL ?>?views=login">
                    <i class="fas fa-arrow-left"></i> Volver al login
                </a>
            </p>
        </div>
    </div>

<?php else: ?>

    <div class="login-container">
        <div class="login-content" style="width:100%;">
            <p class="text-center">Recuperar cuenta</p>
                        
            <div style="display: flex; justify-content: center; align-items: center;">
                <!--img src="<c?php echo SERVERURL; ?>vistas/<c?= htmlspecialchars($rutaLogo) ?>"--> 
                   <img src="<?= mainModel::obtenerRutaImagenLocal('logo') ?>"
                     alt="Logo" 
                     style="width: 80px; height: auto;padding:0 5px;">
            </div>
            
            <p class="text-center">
                <a href="<?= SERVERURL ?>?views=login">
                    <i class="fas fa-arrow-left"></i> Volver al login
                </a>
            </p>

            <p class="text-center small">
                Le enviaremos un correo con un enlace para restablecer su contraseña.
            </p>

            <form action="" method="POST" autocomplete="off" id="form-recuperar">
                <div class="form-group">
                    <label for="Correo"><i class="fas fa-envelope"></i> Correo electrónico</label>
                    <input type="email" class="form-control" id="Correo" name="correo"
                           maxlength="100" required>
                </div>

                <button type="submit" class="btn-login text-center"
                        style="padding: 4px 10px; font-size: 0.8rem; margin:0; width:100%;">
                    Enviar enlace de recuperación
                </button>
            </form>
        </div>
    </div>

<?php endif; ?>
