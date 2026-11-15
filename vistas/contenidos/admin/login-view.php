<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once "./controladores/loginControlador.php";
$ins_login = new loginControlador();

$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$bloqueo = loginModelo::verificar_ip_bloqueada_supabase($ip, 'login');

$ipBloqueada      = !empty($bloqueo['bloqueado']);
$minutosRestantes = $ipBloqueada ? (int) ceil(($bloqueo['tiempo_restante'] ?? 0) / 60) : 0;

if (!$ipBloqueada && isset($_POST['usuario_log']) && isset($_POST['clave_log'])) {
    $ins_login->iniciar_sesion_controlador();
}
?>


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
  /*scrollbar-gutter: stable;*/   
  background: <?= $c2 ?>; /* fallback: color intermedio */
  background: linear-gradient(90deg, <?= $c1 ?> 0%, <?= $c2 ?> 50%, <?= $c3 ?> 99%);
    background-size: cover;
}

.login-container{
	background-color: rgba(0, 0, 0, 0); /* Negro con 100% de transparencia */
	color: #ffffff; /* El texto se mantiene totalmente visible */
	background: rgba(0, 0, 0, 0);
}
    
html {overflow-y: hidden;/*scrollbar-gutter: stable;*/}
</style>

<script>window.DARK_MODE_BLOQUEADO = true;</script>






<?php if ($ipBloqueada): ?>

    <!-- IP BLOQUEADA: solo mostrar aviso -->
    <div class="login-container">
        <div class="login-content text-center">
            <p>
                <i class="fas fa-ban fa-5x" style="color:#dc3545;"></i>
            </p>
            <h3 style="color:#dc3545;">Acceso bloqueado</h3>
            <p class="text-danger">
                Demasiados intentos fallidos.<br>
                Intente más tarde en <b><?= $minutosRestantes ?></b> minutos.
            </p>
            <p class="text-center mt-4">
                <a href="<?= SERVERURL ?>index.php">
                    <i class="fas fa-info-circle fa-fw"></i> Volver al inicio
                </a>
            </p>
            <p class="text-center mt-4">
                <a href="<?= SERVERURL ?>recuperar-cuenta"> ¿Olvidaste tu clave?</a>
            </p>
        </div>
    </div>

<?php else: ?>

    <!-- IP NO BLOQUEADA: formulario normal -->
    <div class="login-container">
        <div class="login-content">
            
		   <div style="display: flex; justify-content: center; align-items: center;">
                <img src="<?= mainModel::obtenerRutaImagenLocal('logo') ?>"
                     alt="Logo" 
                     style="width: 80px; height: auto;padding:0 5px;">
            </div>
            
            <p class="text-center">Inicia sesión con tu cuenta</p>
            <p class="text-center">
                <a href="<?= SERVERURL ?>index.php"><i class="fas fa-info-circle fa-fw"></i> - Volver al inicio</a>
            </p>

            <script src="https://www.google.com/recaptcha/api.js?render=<?= defined('RECAPTCHA_SITE_KEY') ? RECAPTCHA_SITE_KEY : '6Ld_sample_site_key_v3' ?>"></script>

            <form action="" method="POST" autocomplete="off" id="form-login">
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                <div class="form-group">
                    <label for="UserName" class="bmd-label-floating"><i class="fas fa-user-secret"></i> &nbsp; Usuario</label>
                    <input type="email" class="form-control" id="UserName" name="usuario_log" maxlength="100" required>
                </div>

                <div class="form-group password-container">
                    <label for="UserPassword" class="bmd-label-floating"><i class="fas fa-key"></i> &nbsp; Contraseña</label>
                    <input type="password" class="form-control" id="UserPassword" name="clave_log"
                           pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" minlength="7" required>
                    <button type="button" class="btn-toggle-password" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>

                <button type="submit" class="btn-login text-center"
                        style="padding: 4px 10px; font-size: 0.8rem;margin:0px;width:100%;">
                    Iniciar Sesión
                </button>
            </form>

            <p class="text-center small">
                <a href="<?= SERVERURL ?>recuperar-cuenta"> ¿Olvidaste tu clave?</a>
            </p>

     
        </div>
    </div>

<?php endif; ?>

<!-- Estilos para el toggle de contraseña -->
<style>
.password-container {
    position: relative;
}

.btn-toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 5px;
    border-radius: 4px;
    transition: all 0.3s ease;
    z-index: 10;
}

.btn-toggle-password:hover {
    background-color: rgba(0, 0, 0, 0.05);
    color: #495057;
}

.btn-toggle-password:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

/* Ajustar el padding del input de contraseña para que no se solape con el botón */
#UserPassword {
    padding-right: 40px;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput  = document.getElementById('UserPassword');

    // Mostrar/ocultar contraseña
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            const icon = this.querySelector('i');
            if (type === 'password') {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    }

    // reCAPTCHA v3 al enviar el formulario
    const formLogin = document.getElementById("form-login");
    if (formLogin) {
        formLogin.addEventListener("submit", function(e) {
            const siteKey = "<?= defined('RECAPTCHA_SITE_KEY') ? RECAPTCHA_SITE_KEY : '' ?>";
            if (typeof grecaptcha !== "undefined" && siteKey) {
                e.preventDefault();
                grecaptcha.ready(function() {
                    grecaptcha.execute(siteKey, {action: 'login'}).then(function(token) {
                        const inputToken = document.getElementById('g-recaptcha-response');
                        if (inputToken) inputToken.value = token;
                        formLogin.submit();
                    });
                });
            }
        });
    }
});
</script>