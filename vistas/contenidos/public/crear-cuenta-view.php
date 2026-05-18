
<div class="login-container">
	<div class="login-content" style="width:59vw;">
		<p class="text-center">
			Crear cuenta para estudiantes
		</p>
		<p class="text-center">
			<a href="<?php echo SERVERURL; ?>?views=login"><i class="fas fa-arrow-left"></i> Volver al login</a>
		</p>
        						<p class="text-center small">Reponda a las preguntas de verificación del formulario y si se encuentra inscrito y creará una cuenta.</p>
                    <div class="text-danger text-center small mt-1" id="alerta-ip-cc" style="display:none;"></div>

		<form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/recuperarAjax.php" method="POST" data-form="verificar"  autocomplete="off">

            <div class="row">
                <div class="col-md-6 form-group" style="width:35%;">
                    <label for="Cedula"><i class="fas fa-id-card"></i> Cédula </label>
                    <input type="text" class="form-control" id="Cedula" name="est-cedula" pattern="[0-9]{6,10}" maxlength="10" required>
                </div>

                <div class="col-md-6 form-group">
                    <label for="Telefono"><i class="fas fa-phone"></i> Teléfono</label>
                    <input type="text" class="form-control" id="Telefono" name="est-telefono" pattern="[0-9]{10,15}" maxlength="15" required>
                </div>
                        <button type="button" class="btn btn-sm ver-ayuda-texto"
                            data-text="Escriba el número de teléfono común (0412-123-1234) de esta manera: 04123334444. En el caso de que sea un número de Whatsapp (+58 412-123...) escríbalo así: 584123334444."
                            title="Ver explicación"
                            style="background-color: #3CA6A6; color: #fff; border: none; padding: 1px 8px; border-radius: 4px;margin: 0 0 0 85%;">
                        <i class="fas fa-question"></i>
                    </button>
                </div>

         
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="Correo"><i class="fas fa-envelope"></i> Correo electrónico</label>
                    <input type="email" class="form-control" id="Correo" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" name="est-correo" maxlength="60" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="FechaNacimiento"><i class="fas fa-calendar-alt"></i> Fecha de nacimiento</label>
                    <input type="date" class="form-control" id="FechaNacimiento" name="est-fecha_nacimiento" min="1900-01-01" max="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="Clave"><i class="fas fa-lock"></i> Nueva clave</label>
                    <input type="password" class="form-control" id="Clave" name="clave" minlength="7" maxlength="100" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="ConfirmarClave"><i class="fas fa-lock"></i> Confirmar clave</label>
                    <input type="password" class="form-control" id="ConfirmarClave" name="confirmar_clave" minlength="7" maxlength="100" required>
                </div>
            </div>
            <div class="text-danger text-center small mt-1" id="alerta-clave" style="display:none;"></div>


			<button type="submit" class="btn-login text-center"  style="padding: 4px 10px; font-size: 0.8rem;margin:0px;width:100%;">Verificar identidad</button>
		</form>
	</div>

    <div id="modalTextoAyuda" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75);">
  <div style="position:relative; margin:auto; top:10%; max-width:60%; background:#000; border:3px solid #fff; padding:20px; border-radius:8px; color:white;">
    <p id="textoAyuda" style="font-size:1.2rem; line-height:1.6; text-align:center;">Escriba el número de teléfono común (0412-123-1234) de esta manera: 04123334444. En el caso de que sea un número de Whatsapp (+58 412-123...) escríbalo así: 584123334444</p>
    <button onclick="document.getElementById('modalTextoAyuda').style.display='none'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light">
      <i class="fas fa-times"></i>
    </button>
  </div>
</div>
</div>
<script>
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".ver-ayuda-texto").forEach(btn => {
    btn.addEventListener("click", () => {
      const modal = document.getElementById("modalTextoAyuda");
      const texto = btn.dataset.text || "No hay ayuda disponible.";
      modal.querySelector("#textoAyuda").textContent = texto;
      modal.style.display = "block";
    });
  });
});
</script>


<script>
document.addEventListener("DOMContentLoaded", () => {
  const claveInput = document.getElementById("Clave");
  const confirmarInput = document.getElementById("ConfirmarClave");
  const alertaClave = document.getElementById("alerta-clave");

  function validarClaves() {
    const clave = claveInput.value.trim();
    const confirmar = confirmarInput.value.trim();

    if (clave.length >= 7 && confirmar.length >= 7) {
      if (clave !== confirmar) {
        alertaClave.textContent = "Las claves no coinciden.";
        alertaClave.style.display = "block";
      } else {
        alertaClave.style.display = "none";
      }
    } else {
      alertaClave.style.display = "none";
    }
  }

  claveInput.addEventListener("input", validarClaves);
  confirmarInput.addEventListener("input", validarClaves);
});
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const alertaIP = document.getElementById("alerta-ip-cc");
  const btnSubmit = document.querySelector("button[type='submit']");
  const SERVERURL = "<?php echo SERVERURL; ?>";

  fetch(`${SERVERURL}ajax/recuperarAjax.php?accion=validar_ip&tipo=cc`)
    .then(res => res.json())
    .then(data => {
      if (data.bloqueado) {
        alertaIP.textContent = data.mensaje;
        alertaIP.style.display = "block";
        btnSubmit.disabled = true;
        btnSubmit.style.opacity = "0.6";
        btnSubmit.style.cursor = "not-allowed";
      } else {
        alertaIP.style.display = "none";
        btnSubmit.disabled = false;
        btnSubmit.style.opacity = "1";
        btnSubmit.style.cursor = "pointer";
      }
    })
    .catch(err => {
      console.error("Error al validar IP:", err);
      alertaIP.textContent = "Error al verificar acceso. Intente más tarde.";
      alertaIP.style.display = "block";
      btnSubmit.disabled = true;
      btnSubmit.style.opacity = "0.6";
      btnSubmit.style.cursor = "not-allowed";
    });
});
</script>
