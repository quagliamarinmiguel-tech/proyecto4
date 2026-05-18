<?php

//var_dump($_GET);
//die();

require_once __DIR__ . "/../../../modelos/mainModel.php";

// ID recibido por GET (si existe)
$idSeleccionado = isset($_GET['id']) ? intval($_GET['id']) : null;
require_once "./vistas/inc/utilidadesPublicas2.php"; // Incluye las funciones

$pdo = conectarBase();
$ip = $_SERVER['REMOTE_ADDR'];
$idIP = registrar_ip_visitante($pdo, $ip); // Registra o recupera la IP

registrar_visita($pdo, $idIP); // Registra la visita general

// Si se recibió un id de curso, registrar la interacción
if ($idSeleccionado) {
    registrar_interaccion_banner($pdo, $idSeleccionado, $idIP, "Informativo");
}



// Consulta para traer todos los cursos activos
$sql = "
SELECT DISTINCT
    c.id_curso,
    c.nombre_curso,
    c.tipo_curso,
    co.pago_inscripcion
FROM curso_publicidad m
LEFT JOIN curso c ON m.id_curso = c.id_curso
LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
LEFT JOIN seccion s ON cv.id_curso_vigente = s.id_curso_vigente_fk AND s.nombre_seccion = 'A' AND s.archivar_seccion = 1
LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor AND p.estado_profesor = 1
WHERE
    co.estado_cohorte IN ('Vigente', 'Próximo') AND
    cv.estado_curso_vigente = 1 AND
    p.estado_profesor = 1 AND
    c.estado_curso = 1 AND
    s.archivar_seccion = 1 AND
    m.estado_curso_publicidad = 1 AND
    s.nombre_seccion = 'A'
ORDER BY c.nombre_curso ASC
";

$stmt = mainModel::conectar()->prepare($sql);
$stmt->execute();
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>








<style>
body {
//  background-color: #e3f2fd; /* Azul claro */
 // min-height: 100vh;
  padding: 30px 0;
background: #0440cc;
background: linear-gradient(90deg, rgba(4, 64, 204, 1) 0%, rgba(0, 141, 241, 1) 50%, rgba(16, 78, 179, 1) 99%);
}
</style>

<div class="container fondo-azul mt-2">
			<a href="<?php echo SERVERURL; ?>index.php" style="color:white;"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
  <h4 class="text-center mb-2" style="color:white;"><i class="fas fa-user-plus"></i> Registro de pago y datos personales
      <button type="button" class="btn btn-sm ver-ayuda-texto"
            data-text="Por favor, complete el formulario para iniciar el proceso de inscripción. Se solicitarán datos personales del estudiante, así como la realización de una transacción para el pago de la inscripción. Una vez que el personal reciba esta información y verifique su validez, se procederá a registrar al estudiante en el sistema y a crearle una cuenta de acceso con la clave registrada."
            title="Ver explicación"
            style="background-color: #FF4221; color: #fff; border: none; padding: 4px 8px; border-radius: 4px;">
      <i class="fas fa-question"></i>
      </button>
  </h4>

  <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/recuperarAjax.php" method="POST" enctype="multipart/form-data" autocomplete="off">

    <fieldset>
      <!--legend><i class="fas fa-id-card"></i> &nbsp; Datos personales</legend-->
      <legend style="font-size: 1rem;"><i class="fas fa-id-card"></i> &nbsp; Datos personales</legend>
    <div class="row">
        <div class="col-md-3 form-group">
          <label for="nombre">Nombre</label>
          <input type="text" class="form-control" name="nombre" id="nombre"  pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{1,30}" maxlength="30" required>
        </div>
        <div class="col-md-3 form-group">
          <label for="apellido">Apellido</label>
          <input type="text" class="form-control" name="apellido" id="apellido"  pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{1,30}" maxlength="30" required>
        </div>

        <div class="col-md-3 form-group">
          <label for="correo">Correo electrónico</label>
          <input type="email" class="form-control" name="correo" id="correo" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" maxlength="60" required>
        </div>

        <div class="col-md-3 form-group">
          <label for="id_curso">Curso</label>
          <select name="id_curso" id="curso_up" class="form-control select2" required>
            <option value="">Seleccione un curso</option>
            <?php foreach($cursos as $curso): ?>
              <option value="<?= $curso['id_curso'] ?>" <?= ($curso['id_curso'] == $idSeleccionado) ? 'selected' : '' ?> data-pago="<?= $curso['pago_inscripcion'] ?>">
                <?= htmlspecialchars($curso['nombre_curso']) ?> (<?= htmlspecialchars($curso['tipo_curso']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-2 form-group">
          <label for="fecha_nacimiento">Fecha de nacimiento</label>
          <input type="date" class="form-control" name="fecha_nacimiento" id="fecha_nacimiento" min="1900-01-01" max="<?= date('Y-m-d') ?>"  required>
        </div>
        <div class="col-md-2 form-group">
          <label for="cedula">Cédula</label>
          <input type="text" class="form-control" name="cedula" id="cedula" pattern="[0-9]{6,10}" maxlength="11" required>
        </div>
        <div class="col-md-2 form-group">
          <label for="telefono">Teléfono</label>
          <input type="text" class="form-control" name="telefono" id="telefono" pattern="[0-9]{10,13}" maxlength="13" required>
        </div>
        <div class="col-md-4 form-group" style="display:flex;justify-content:center;align-items:center;padding-top:10px;">
        	<p>Solo registre los dígitos de los números telefónicos, no se registran signos como " _ ", " + ", " - ". Ej: "04123334444" o "584123334444".</p>
        </div>

        <div class="col-md-2 form-group">
          <label>Pago de inscripción</label>
          <p id="pagoInscripcionTexto" style="font-weight:bold; margin-top:8px;">Pago de inscripción: —</p>
        </div>
        <!-- Escriba el número de teléfono común (0412-123-1234) de esta manera: 04123334444. En el caso de que sea un número de Whatsapp (+58 412-123...) escríbalo así: 584123334444.-->
        
        <!--div class="col-md-4 form-group">
          <label for="direccion">Dirección</label>
          <input type="text" class="form-control" name="direccion" id="direccion" maxlength="150" required>
        </div-->
      </div>
    </fieldset>

    <br>

<div class="row">
  <div class="col-md-7">
    <fieldset>
      <legend style="font-size: 1rem;"><i class="fas fa-money-check-alt"></i> &nbsp; Información de pago</legend>
      <div class="row">
        <!--div class="col-md-2 form-group">
          <label for="monto">Monto</label>
          <input type="number" class="form-control" name="monto" id="monto" min="0" step="0.01" required>
        </div-->

          
          
          <div class="col-md-3 form-group">
              <label for="monto">Monto</label>
              <input type="number" class="form-control" name="monto" id="monto" min="0" step="0.01" required>
              <small id="montoError" class="text-danger" style="display:none;">⚠️ El monto no puede exceder el pago de inscripción.</small>
            </div>
          
          
          
        <div class="col-md-3 form-group">
          <label for="fecha_pago">Fecha de pago</label>
          <input type="date" class="form-control" name="fecha_pago" min="2015-01-01" max="<?= date('Y-m-d', strtotime('+1 day')) ?>" id="fecha_pago" required>
        </div>
        <div class="col-md-3 form-group" >
          <label for="metodo_pago">Método de pago</label>
          <select class="form-control" name="metodo_pago" id="metodo_pago" required>
            <option value="" disabled selected>Seleccione una opción</option>
            <option value="Transferencia">Transferencia</option>
            <option value="Pago móvil">Pago móvil</option>
            <option value="Efectivo">Efectivo</option>
            <option value="Otro">Otro</option>
          </select>
        </div>
        <div class="col-md-3 form-group">
          <label for="captura_pago" style="font-size: 0.85rem;">Captura del pago</label>
          <input type="file" style="font-size: 0.85rem;" class="form-control-file" name="captura_pago" id="captura_pago" accept="image/*" required>
          <small id="nombre-archivo" class="form-text text-muted"></small>
        </div>
      </div>
    </fieldset>
  </div><!--row1-->
    
  <div class="col-md-5">
    <fieldset>
      <legend style="font-size: 1rem;"><i class="fas fa-lock"></i> &nbsp; Seguridad de acceso</legend>
      <div class="text-danger small mt-1" id="alerta-clave" style="display:none;"></div>
      <div class="row">
        <div class="col-md-6 form-group">
          <label for="clave">Clave de acceso</label>
          <input type="password" class="form-control" name="clave" id="clave" minlength="7" maxlength="100" required>
        </div>
        <div class="col-md-6 form-group">
          <label for="confirmar_clave">Confirmar clave</label>
          <input type="password" class="form-control" name="confirmar_clave" id="confirmar_clave" minlength="7" maxlength="100" required>
        </div>
      </div>
      <div class="text-danger small mt-1" id="alerta-clave" style="display:none;"></div>
    </fieldset>
  </div><!--row2-->
    

    
    
    
    
    
    
    
    
    
    
</div> <!--padre-->

    <br>
      
    <p class="text-center">
      <button type="submit" class="btn btn-info btn-sm" id="btn-guardar" style="width:100%;"><i class="far fa-save"></i> &nbsp; GUARDAR</button>
    </p>
  </form>
</div>

<div id="modalTextoAyuda" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75);">
  <div style="position:relative; margin:auto; top:10%; max-width:60%; background:#000; border:3px solid #fff; padding:20px; border-radius:8px; color:white;">
    <p id="textoAyuda" style="font-size:1.2rem; line-height:1.6; text-align:center;">Por favor, complete el formulario para iniciar el proceso de inscripción. Se solicitarán datos personales del estudiante, así como la realización de una transacción para el pago de la inscripción. Una vez que el personal reciba esta información y verifique su validez, se procederá a registrar al estudiante en el sistema y a crearle una cuenta de acceso con la clave registrada.</p>
    <button onclick="document.getElementById('modalTextoAyuda').style.display='none'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light">
      <i class="fas fa-times"></i>
    </button>
  </div>
</div>

<script>
//modal
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
//imagen
document.addEventListener("DOMContentLoaded", () => {
  const input = document.getElementById("captura_pago");
  const nombreArchivo = document.getElementById("nombre-archivo");

  input.addEventListener("change", () => {
    const archivo = input.files[0];
    if (archivo) {
      nombreArchivo.textContent = `Archivo seleccionado: ${archivo.name}`;
    } else {
      nombreArchivo.textContent = "";
    }
  });
});
</script>




<script>
document.addEventListener("DOMContentLoaded", () => {
  const selectCurso = document.getElementById("curso_up");
  const pagoTexto = document.getElementById("pagoInscripcionTexto");
  const montoInput = document.getElementById("monto");
  const montoError = document.getElementById("montoError");
  const claveInput = document.getElementById("clave");
  const confirmarInput = document.getElementById("confirmar_clave");
  const alertaClave = document.getElementById("alerta-clave");
  const btnGuardar = document.getElementById("btn-guardar");

  let pagoActual = null;
  let estadoMontoValido = false;
  let estadoClaveValida = false;

  function actualizarEstadoBoton() {
    if (estadoMontoValido && estadoClaveValida) {
      btnGuardar.disabled = false;
      btnGuardar.style.opacity = "1";
      btnGuardar.style.cursor = "pointer";
    } else {
      btnGuardar.disabled = true;
      btnGuardar.style.opacity = "0.6";
      btnGuardar.style.cursor = "not-allowed";
    }
  }

  function actualizarPago() {
    const selected = selectCurso.options[selectCurso.selectedIndex];
    const pago = parseFloat(selected.dataset.pago || 0);

    if (!isNaN(pago) && pago > 0) {
      pagoTexto.textContent = `Pago de inscripción: ${pago.toFixed(2)}$`;
      pagoActual = pago;
    } else {
      pagoTexto.textContent = "Pago de inscripción: —";
      pagoActual = null;
    }

    validarMonto();
  }

  function validarMonto() {
    const monto = parseFloat(montoInput.value);
    if (pagoActual !== null && !isNaN(monto)) {
      if (monto > pagoActual) {
        montoError.style.display = "block";
        estadoMontoValido = false;
      } else {
        montoError.style.display = "none";
        estadoMontoValido = true;
      }
    } else {
      montoError.style.display = "none";
      estadoMontoValido = false;
    }
    actualizarEstadoBoton();
  }

  function validarClaves() {
    const clave = claveInput.value.trim();
    const confirmar = confirmarInput.value.trim();

    if (clave.length >= 7 && confirmar.length >= 7) {
      if (clave !== confirmar) {
        alertaClave.textContent = "⚠️ Las claves no coinciden.";
        alertaClave.style.display = "block";
        estadoClaveValida = false;
      } else {
        alertaClave.style.display = "none";
        estadoClaveValida = true;
      }
    } else {
      alertaClave.style.display = "none";
      estadoClaveValida = false;
    }
    actualizarEstadoBoton();
  }

  // Eventos
  selectCurso.addEventListener("change", actualizarPago);
  montoInput.addEventListener("input", validarMonto);
  claveInput.addEventListener("input", validarClaves);
  confirmarInput.addEventListener("input", validarClaves);

  // Inicializar al cargar
  actualizarPago();
  validarClaves();
});
</script>







<!--script>
//clave
document.addEventListener("DOMContentLoaded", () => {
  const claveInput = document.getElementById("clave");
  const confirmarInput = document.getElementById("confirmar_clave");
  const alertaClave = document.getElementById("alerta-clave");
  const btnGuardar = document.getElementById("btn-guardar");

  function validarClaves() {
    const clave = claveInput.value.trim();
    const confirmar = confirmarInput.value.trim();

    if (clave.length >= 7 && confirmar.length >= 7) {
      if (clave !== confirmar) {
        alertaClave.textContent = "⚠️ Las claves no coinciden.";
        alertaClave.style.display = "block";
        btnGuardar.disabled = true;
        btnGuardar.style.opacity = "0.6";
        btnGuardar.style.cursor = "not-allowed";
      } else {
        alertaClave.style.display = "none";
        btnGuardar.disabled = false;
        btnGuardar.style.opacity = "1";
        btnGuardar.style.cursor = "pointer";
      }
    } else {
      alertaClave.style.display = "none";
      btnGuardar.disabled = true;
      btnGuardar.style.opacity = "0.6";
      btnGuardar.style.cursor = "not-allowed";
    }
  }

  claveInput.addEventListener("input", validarClaves);
  confirmarInput.addEventListener("input", validarClaves);

  // Inicializar estado del botón al cargar
  validarClaves();
});
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const selectCurso = document.getElementById("curso_up");
  const pagoTexto = document.getElementById("pagoInscripcionTexto");
  const montoInput = document.getElementById("monto");
  const montoError = document.getElementById("montoError");
  const btnGuardar = document.getElementById("btn-guardar");

  let pagoActual = null;

  function actualizarPago() {
    const selected = selectCurso.options[selectCurso.selectedIndex];
    const pago = parseFloat(selected.dataset.pago || 0);

    if (!isNaN(pago) && pago > 0) {
      pagoTexto.textContent = `Pago de inscripción: ${pago.toFixed(2)}$`;
      pagoActual = pago;
    } else {
      pagoTexto.textContent = "Pago de inscripción: —";
      pagoActual = null;
    }

    validarMonto();
  }

  function validarMonto() {
    const monto = parseFloat(montoInput.value);
    if (pagoActual !== null && !isNaN(monto)) {
      if (monto > pagoActual) {
        montoError.style.display = "block";
        btnGuardar.disabled = true;
        btnGuardar.style.opacity = "0.6";
        btnGuardar.style.cursor = "not-allowed";
      } else {
        montoError.style.display = "none";
        btnGuardar.disabled = false;
        btnGuardar.style.opacity = "1";
        btnGuardar.style.cursor = "pointer";
      }
    } else {
      montoError.style.display = "none";
      btnGuardar.disabled = true;
      btnGuardar.style.opacity = "0.6";
      btnGuardar.style.cursor = "not-allowed";
    }
  }

  selectCurso.addEventListener("change", actualizarPago);
  montoInput.addEventListener("input", validarMonto);

  // Inicializar al cargar
  actualizarPago();
});
</script-->




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Inicialización -->
<script>
  $(document).ready(function() {
    $('.select2').select2({
      placeholder: "Seleccione un curso",
      allowClear: true,
      width: '100%'
    });
  });
</script>








