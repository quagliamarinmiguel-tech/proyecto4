<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
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
//var_dump($nombreCorto,$nombreCompleto,$rutaIcono,$rutaLogo,$paletaInicioId);die();

$primario = $coloresInicio[0];
$secundario = $coloresInicio[1];
//$c1 = mainModel::hex2rgba($primario, 1);      // 100%
//$c2 = mainModel::hex2rgba($primario, 0.85);   // 85%
//$c3 = mainModel::hex2rgba($primario, 0.7);    // 70%
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







<div class="login-container">
    <div class="login-content" style="width:100%;">
        
        	<div style="display: flex; justify-content: center; align-items: center;">
                <!--img src="<c?php echo SERVERURL; ?>vistas/<c?= htmlspecialchars($rutaLogo) ?>"--> 
                	<img src="<?= mainModel::obtenerRutaImagenLocal('logo') ?>"
                     alt="Logo" 
                     style="width: 80px; height: auto;padding:0 5px;">
            </div>
        
        <p class="text-center">Nueva contraseña</p>

        <form id="form-cambiar-clave" style="display:none;">
            <div class="form-group">
                <label for="nueva_clave"><i class="fas fa-lock"></i> Nueva contraseña</label>
                <input type="password" class="form-control" id="nueva_clave"
                       minlength="7" maxlength="100" required>
            </div>
            <div class="form-group">
                <label for="confirmar_clave"><i class="fas fa-lock"></i> Confirmar contraseña</label>
                <input type="password" class="form-control" id="confirmar_clave"
                       minlength="7" maxlength="100" required>
            </div>

            <button type="submit" class="btn-login text-center"
                    style="padding: 4px 10px; font-size: 0.8rem; margin:0; width:100%;">
                Actualizar contraseña
            </button>
        </form>

        <div id="mensaje-error" class="text-danger text-center" style="display:none;">
            El enlace no es válido o ha expirado. Solicite uno nuevo.
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // El token viene en el hash de la URL
    const hash = window.location.hash.substring(1);
    const params = new URLSearchParams(hash);
    const accessToken = params.get('access_token');
    const type = params.get('type');

    if (!accessToken || type !== 'recovery') {
        document.getElementById('mensaje-error').style.display = 'block';
        return;
    }

    document.getElementById('form-cambiar-clave').style.display = 'block';

    document.getElementById('form-cambiar-clave').addEventListener('submit', async (e) => {
        e.preventDefault();

        const clave1 = document.getElementById('nueva_clave').value.trim();
        const clave2 = document.getElementById('confirmar_clave').value.trim();

        if (clave1.length < 7) {
            Swal.fire('Error', 'La contraseña debe tener al menos 7 caracteres.', 'error');
            return;
        }
        if (clave1 !== clave2) {
            Swal.fire('Error', 'Las contraseñas no coinciden.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('access_token', accessToken);
        formData.append('nueva_clave', clave1);

        try {
            const res = await fetch('<?= SERVERURL ?>ajax/loginAjax.php?accion=cambiar_clave', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.ok) {
                Swal.fire('Éxito', 'Contraseña actualizada. Ya puede iniciar sesión.', 'success')
                    .then(() => window.location.href = '<?= SERVERURL ?>?views=login');
            } else {
                Swal.fire('Error', data.mensaje || 'No se pudo actualizar.', 'error');
            }
        } catch (err) {
            Swal.fire('Error', 'Error de conexión.', 'error');
        }
    });
});
</script>
