<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
/*
session_start(['name'=>'SPM']);
    if (isset($_SESSION['id_spm'])) {
        session_unset();
        session_destroy();
        header("Location: " . SERVERURL . "login/");
        exit();
    }
*/

if (isset($_POST['usuario_log']) && isset($_POST['clave_log'])) {
    require_once "./controladores/loginControlador.php";
    $ins_login = new loginControlador();
    $ins_login->iniciar_sesion_controlador();
}

?>

<div class="login-container">
    <div class="login-content">
        <p class="text-center">
            <i class="fas fa-user-circle fa-5x"></i>
        </p>
        <p class="text-center">
            Inicia sesión con tu cuenta
        </p>        
        <p class="text-center">
            <a href="<?php echo SERVERURL; ?>index.php"><i class="fas fa-info-circle fa-fw"></i> - Volver al inicio</a>
        </p>
        
        <!--div class="text-center mb-4">
            <a href="<?php echo SERVERURL; ?>documentos/Manual%20de%20Usuario%20para%20ceproca%20Stage%20Alfa.pdf" 
               class="elegant-download-btn" 
               download="Manual_de_Usuario_CEPROCA.pdf"
               target="_blank">
                <i class="fas fa-book"></i>
                <span>Descargar Manual de Usuario</span>
                <div class="hover-effect"></div>
            </a>
        </div-->
        
<div class="text-danger small mt-1" id="alerta-ip-login" style="display:none;"></div>

        <form action="" method="POST" autocomplete="off" >
            <div class="form-group">
                <label for="UserName" class="bmd-label-floating"><i class="fas fa-user-secret"></i> &nbsp; Usuario</label>
                <input type="text" class="form-control" id="UserName" name="usuario_log" pattern="[a-zA-Z0-9\-]{1,35}" maxlength="35" required="" >
            </div>
            <div class="form-group password-container">
                <label for="UserPassword" class="bmd-label-floating"><i class="fas fa-key"></i> &nbsp; Contraseña</label>
                <input type="password" class="form-control" id="UserPassword" name="clave_log" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required="" >
                <button type="button" class="btn-toggle-password" id="togglePassword">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <button type="submit" class="btn-login text-center" style="padding: 4px 10px; font-size: 0.8rem;margin:0px;width:100%;">Iniciar Sesión</button>
        </form>

        <p class="text-center small">
            <a href="<?php echo SERVERURL; ?>recuperar-cuenta"> ¿Olvidaste tu clave?</a>  
        </p>

        <p class="text-center small">
            <a href="<?php echo SERVERURL; ?>crear-cuenta"> ¿Ya estas inscrito y no tienes cuenta?</a>
        </p>
    </div>
</div>

<!-- Estilos para el botón elegante y el toggle de contraseña -->
<style>
.elegant-download-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    padding: 14px 28px;
    border-radius: 25px;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    border: none;
}

.elegant-download-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    color: white;
    text-decoration: none;
}

.elegant-download-btn:active {
    transform: translateY(0);
}

.elegant-download-btn i {
    font-size: 1.1rem;
    transition: transform 0.3s ease;
}

.elegant-download-btn:hover i {
    transform: scale(1.1);
}

.elegant-download-btn .hover-effect {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s ease;
}

.elegant-download-btn:hover .hover-effect {
    left: 100%;
}

/* Estilos para el toggle de contraseña */
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
    const alertaIP = document.getElementById("alerta-ip-login");
    const btnSubmit = document.querySelector("button[type='submit']");
    const SERVERURL = "<?php echo SERVERURL; ?>";
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('UserPassword');

    // Función para mostrar/ocultar contraseña
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            // Cambiar el tipo de input
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Cambiar el ícono
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

    // Validación de IP (código existente)
    fetch(`${SERVERURL}ajax/recuperarAjax.php?accion=validar_ip&tipo=login`)
        .then(res => res.json())
        .then(data => {
            if (data.bloqueado) {
                alertaIP.textContent = data.mensaje;
                alertaIP.style.display = "block";
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.style.opacity = "0.6";
                    btnSubmit.style.cursor = "not-allowed";
                }
            } else {
                alertaIP.style.display = "none";
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.style.opacity = "1";
                    btnSubmit.style.cursor = "pointer";
                }
            }
        })
        .catch(err => {
            console.error("Error al validar IP:", err);
            alertaIP.textContent = "Error al verificar acceso. Intente más tarde.";
            alertaIP.style.display = "block";
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.style.opacity = "0.6";
                btnSubmit.style.cursor = "not-allowed";
            }
        });
});
</script>
