<?php
require_once "./controladores/publicoControlador.php";
$localCtrl = new publicoControlador();

$courseIdRecibido = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Obtener todas las opciones de inscripción
$respuesta = $localCtrl->opciones_inscripcion_controlador();
if (!$respuesta['ok']) {
    echo '<div class="alert alert-danger text-center">' . htmlspecialchars($respuesta['message']) . '</div>';
    exit;
}
$cursosDisponibles = $respuesta['data'];

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

//traer datos de pagomovil
$pagoMovil = $localCtrl->obtener_datos_pagomovil();


?>
<script>window.DARK_MODE_BLOQUEADO = true;</script>

<style>
body {
  min-height: 100vh;
/*  //  max-height:300vh;*/
    height:auto;
  padding: 30px 0px;
    margin:0px;
    width:100%;
    /*  background-color: #e3f2fd; 
//background: #0440cc;
//background: linear-gradient(90deg, rgba(4, 64, 204, 1) 0%, rgba(0, 141, 241, 1) 50%, rgba(16, 78, 179, 1) 99%);*/
     overflow-x: hidden;
     overflow-y:hidden;
/*  scrollbar-gutter: stable;   */
    background: <?= $c2 ?>; /* fallback: color intermedio */
    background: linear-gradient(90deg,
        <?= $c1 ?> 0%,
        <?= $c2 ?> 50%,
        <?= $c3 ?> 99%
    );
    background-size: cover;

}
    
html {
    overflow-y: auto;
    overflow-x: hidden;
/*  overflow-y: auto;*
  scrollbar-gutter: stable;*/
}
</style>







<div class="container fondo-azul mt-2" style="height:100%;max-width:80vw;">


            <br>
        <h4 class="text-center mb-2" style="color:white;"><i class="fas fa-user-plus"></i> Registro de pago y datos personales
            <button type="button" class="btn btn-sm btn-ayuda" data-target="p1"style="background-color:#ff4221; color:#fff; border:none; padding:6px 10px; border-radius:50%; margin-right:10px;">
               <i class="fas fa-question"></i>
            </button>
        </h4>

                <br>

    
    
    <!--IMPORTANTE-->
    <!--El form apunta a login ajax porque es el ajax encargado de todas las peticiones de la vista publica-->
    <!--IMPORTANTE-->
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/loginAjax.php" method="POST" enctype="multipart/form-data" data-form="save" autocomplete="off">

        <div style="display: flex; justify-content: space-between; align-items: center;">
            <a href="<?php echo SERVERURL; ?>index.php" style="padding:0 5px;">
                <i class="fas fa-info-circle fa-fw"></i> - Volver al inicio
            </a>
            
            <img src="<?= mainModel::obtenerRutaImagenLocal('logo') ?>"
                 alt="Logo" 
                 style="width: 80px; height: auto;padding:0 5px;">
        </div>

        <br>
            
<!-- ---------------------------------------------------------------------------------------- -->
<!-- Honeypot: los bots lo rellenan, los humanos no lo ven -->
<div style="position:absolute; left:-9999px;" aria-hidden="true">
    <input type="text" name="website" tabindex="-1" autocomplete="off">
</div>

<!-- Time-trap: mide cuánto tarda en rellenarse -->
<input type="hidden" name="form_started_at" value="<?= time() ?>">        
<!-- ---------------------------------------------------------------------------------------- -->
        
        <fieldset>
           <legend style="font-size: 1rem;"><i class="fas fa-money-check-alt"></i> &nbsp; Datos personales</legend>
            <div class="row">
                <div class="col-md-3 form-group">
                  <label for="nombre">Nombre</label>
                  <input type="text" class="form-control" name="nombre" id="nombre" maxlength="30" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,29}"  required>
                </div>
                <div class="col-md-3 form-group">
                  <label for="apellido">Apellido</label>
                  <input type="text" class="form-control" name="apellido" id="apellido" maxlength="30" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,29}"  required>
                </div>

               <div class="col-md-6 form-group">
                    <label for="curso_up">Curso</label>
                    <select name="id_curso" id="curso_up" class="form-control select2" required>
                        <option value="">Seleccione un curso disponible</option>
                        <?php foreach ($cursosDisponibles as $c): ?>
                            <?php
                            $periodo = $c['period'] ?? null;
                            $estado = $periodo['current_state'] ?? '';
                            $inscripcion = (float) ($periodo['costs']['pago_inscripcion'] ?? 0);
                            $nombrePeriodo = $periodo['name'] ?? '';
                        	$periodCourseId = (int) ($c['period_course_id'] ?? 0);
                        	$periodCourseId = (int) ($periodo['period_course_id'] ?? 0);   // AQUÍ

                            $abreviaciones = [
                                'Intensivo' => 'INT',
                                'Extendido' => 'EXT',
                                'Técnico Profesional' => 'Téc',
                            ];
                            $tipoAbrev = $abreviaciones[$c['course_type']] ?? $c['course_type'];

                            $textoMostrar = sprintf(
                                '%s (%s, %s)',
                                $c['course_name'],
                                $tipoAbrev,
                                $estado
                            );
                            ?><!--value="<?= (int) $c['course_id'] ?>"-->
                        <option value="<?= $periodCourseId ?>"
                                data-inscripcion="<?= $inscripcion ?>"
                                data-tipo="<?= htmlspecialchars($c['course_type']) ?>"
                                data-estado="<?= htmlspecialchars($estado) ?>"
                                data-periodo="<?= htmlspecialchars($nombrePeriodo) ?>"
                                <?= ($c['course_id'] === $courseIdRecibido) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($textoMostrar) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>



            <div class="row">
                <div class="col-md-3 form-group">
                    <label for="fecha_nacimiento">Fecha de nacimiento</label>
                    <div class="text-success small mt-1" id="alerta-fecha" style="font-weight:bold;">
                        <i class="fas fa-check-circle"></i> Debe tener al menos 9 años
                    </div>

                    <input type="date" class="form-control" name="fecha_nacimiento" min="1920-01-01" max="<?= date('Y-m-d') ?>" id="fecha_nac" required>
                </div>

                <div class="col-md-3 form-group">
                    <div class="text-success small mt-1" id="alerta-cedula" style="font-weight:bold;">
                        <i class="fas fa-check-circle"></i> Formato: V12345678
                    </div>
                    <label for="cedula">Cédula</label>
                    <input type="text" class="form-control" name="cedula" id="cedula" maxlength="10" pattern="[VE]{1}[1-9]{1}[0-9]{6,7}" placeholder="ej:V12345678" required>
                </div>


                <div class="col-md-3 form-group">
                      <div class="text-success small mt-1" id="alerta-telefono" style="font-weight:bold;">
                          <i class="fas fa-check-circle"></i> 11 dígitos con código de operadora válido
                      </div>
                      <label for="telefono">Teléfono</label>

                      <div style="display:flex; align-items:center;">
                          <input type="text" class="form-control" name="telefono" id="telefono" pattern="[0-9]{10,13}" maxlength="11" style="flex:1; margin-right:5px;" required>        
                          <button type="button" class="btn btn-sm btn-ayuda" data-target="p2" style="background-color:#9F27F5; color:#fff; border:none; border-radius:50%; padding:6px 10px;">
                               <i class="fas fa-question"></i>
                          </button>
                      </div>
                </div>


                <div class="col-md-3 form-group">
                      <label for="telefono">WhatsApp</label>
                      <div class="text-success small mt-1" id="alerta-wsp" style="font-weight:bold;">
                            <i class="fas fa-check-circle"></i> 12 dígitos: 58 + código + número
                      </div>

                      <div style="display:flex; align-items:center;">
                          <input type="text" class="form-control" name="wsp" id="wsp" pattern="[0-9]{10,13}" maxlength="12" required style="flex:1; margin-right:5px;">

                          <button type="button" class="btn btn-sm btn-ayuda" data-target="p3" style="background-color:#28a745; color:#fff; border:none; border-radius:50%; padding:6px 10px;">
                              <i class="fas fa-question"></i>
                          </button>
                      </div>
                </div>

            </div>
        </fieldset>
        <br>
      
            <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="correo">Correo electrónico</label>
                        <input type="text" class="form-control" name="correo" id="correo" maxlength="60" required>
                    </div>


                    <div class="col-md-4 form-group">
                        <label for="correo">Dirección</label>
                        <input type="text" class="form-control" name="direccion" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ,._°]{0,79}" id="direccion" maxlength="79" required>
                    </div>

                    <div class="col-md-4 form-group">
                        <label for="correo">Lugar de nacimiento</label>
                        <input type="text" class="form-control" name="lugar_n" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ,._°]{0,79}" id="lugar_n" maxlength="79" required>
                    </div>
            </div>      

           <div class="row">
                   <div class="col-md-3 form-group">
                       <label for="correo">Nivel de estudio</label>
                       <input type="text" class="form-control" name="nivel_est" id="nivel_est" maxlength="29" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,29}" required>
                    </div>

                    <div class="col-md-3 form-group">
                        <label for="correo">Oficio actual</label>
                        <input type="text" class="form-control" name="oficio" id="oficio" maxlength="29" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,29}" required>
                    </div>

                    <div class="col-md-2 form-group">
                        <label for="trabajo">Trabaja?</label>
                        <input type="checkbox" name="trabajo" value="1">
                    </div>
           </div>
      

          <div id="alerta-general" class="alert alert-danger" style="display:none;"></div>

            <?php if ($pagoMovil['banco1'] !== '' || $pagoMovil['banco2'] !== '' || $pagoMovil['ci_pagomovil'] !== '' || $pagoMovil['phone'] !== ''): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <!--5 class="card-title mb-3"><i class="fas fa-mobile-alt"></i> Pago Móvil</h5-->
	                <legend style="font-size: 1rem;"><i class="fas fa-mobile-alt"></i>  Datos de Pago Móvil</legend>

                        <div class="row">
                            <?php if ($pagoMovil['phone'] !== ''): ?>
                                <div class="col-md-3 mb-3">
                                    <p class="text-muted mb-0 small">Teléfono</p>
                                    <p class="fw-bold mb-0">
                                        <?= htmlspecialchars($pagoMovil['phone'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <?php if ($pagoMovil['ci_pagomovil'] !== ''): ?>
                                <div class="col-md-3 mb-3">
                                    <p class="text-muted mb-0 small">C.I. / RIF</p>
                                    <p class="fw-bold mb-0">
                                        <?= htmlspecialchars($pagoMovil['ci_pagomovil'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <?php if ($pagoMovil['banco1'] !== ''): ?>
                                <div class="col-md-3 mb-3">
                                    <p class="text-muted mb-0 small">Banco 1</p>
                                    <p class="fw-bold mb-0">
                                        <?= htmlspecialchars($pagoMovil['banco1'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <?php if ($pagoMovil['banco2'] !== ''): ?>
                                <div class="col-md-3 mb-3">
                                    <p class="text-muted mb-0 small">Banco 2</p>
                                    <p class="fw-bold mb-0">
                                        <?= htmlspecialchars($pagoMovil['banco2'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        
        
          <div class="row">

              <div class="col-md-6">
                  <fieldset>
                            <legend style="font-size: 1rem;"><i class="fas fa-money-check-alt"></i> &nbsp; Información de pago</legend>
                            <div class="row">
                                <!--div class="col-md-2 form-group">
                                  <label for="monto">Monto</label>
                                  <input type="number" class="form-control" name="monto" id="monto" min="0" step="0.01" required>
                                </div-->

                                 <div class="col-md-4 form-group">
                                     <p id="pagoInscripcionTexto" style="font-weight:bold; margin-top:8px;">Pago de inscripción: $</p>
                                 </div>
                                 <input type="hidden" name="monto" id="monto">

                                 <div class="col-md-4 form-group">
                                     <label for="fecha_pago">Fecha de pago</label>
                                     <input type="date" class="form-control" name="fecha_pago" min="2025-01-01" max="<?= date('Y-m-d') ?>" id="fecha_pago" required>
                                 </div>
                                 <div class="col-md-4 form-group" >
                                     <label for="metodo_pago">Método de pago</label>
                                     <select class="form-control" name="metodo_pago" id="metodo_pago" required>
                                        <option value="" disabled selected>Seleccione</option>
                                        <option value="Transferencia">Transferencia</option>
                                        <option value="Pago móvil">Pago móvil</option>
                                        <option value="Efectivo">Efectivo</option>
                                        <option value="Otro">Otro</option>
                                     </select>
                                 </div>
                            </div>
                    </fieldset>
                </div><!--aqui se cierra el colmd 6-->

    
                <div class="col-md-6">
                    <fieldset>
                        <legend style="font-size: 1rem;"><i class="fas fa-lock"></i> &nbsp; Seguridad de acceso
                            <button type="button" class="btn btn-sm btn-ayuda" data-target="p4"
                                style="background-color:#007bff; color:#fff; border:none; padding:6px 10px; border-radius:50%; margin-right:10px;">
                                <i class="fas fa-question"></i>
                            </button>
                        </legend>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <div class="text-success small mt-1" id="alerta-clave" style="font-weight:bold;">
                                    <i class="fas fa-check-circle"></i> Mínimo 7 caracteres
                                </div>
                                <label for="clave">Clave de acceso</label>
                                <input type="password" class="form-control" name="clave" id="clave" 
                                       pattern="[A-Za-z0-9]{8,100}" 
                                       minlength="8" maxlength="100" 
                                       title="Mínimo 8 caracteres. Solo letras y números. Debe contener al menos una mayúscula, una minúscula y un número."
                                       required>
                                <!--input type="password" class="form-control" name="clave" id="clave" pattern="[^ ].*" minlength="7" maxlength="100" required-->
                            </div>
                            <div class="col-md-6 form-group">
                                <div class="text-success small mt-1" id="alerta-confirmar-clave" style="font-weight:bold;">
                                    <i class="fas fa-check-circle"></i> Debe coincidir con la clave
                                </div>
                                <label for="confirmar_clave">Confirmar clave</label>
                                <input type="password" class="form-control" name="confirmar_clave" id="confirmar_clave" 
       pattern="[A-Za-z0-9]{8,100}" 
       minlength="8" maxlength="100" 
       title="Debe coincidir exactamente con la clave ingresada arriba."
       required>
                                <!--input type="password" class="form-control" name="confirmar_clave" id="confirmar_clave" pattern="[^ ].*" minlength="7" maxlength="100" required-->
                            </div>
                        </div>
    				</fieldset>
  				</div>
          </div><!--fin row de pagos y claves-->
    
    
                <div class="row">
                        <div class="col-md-6 form-group">
                           <label for="captura_pago" style="font-size: 0.85rem;">Captura o imagen del pago</label>
                            <input type="file" style="font-size: 0.85rem;" class="form-control-file" name="captura_pago" id="captura_pago" accept="image/*" required>
                            <small id="nombre-archivo" class="form-text text-muted"></small>
                        </div>
                    
                    	<div class="col-md-6 form-group">
                           <label for="Referencia" style="font-size: 0.85rem;">Referencia(opcional)</label>
							<input type="text" class="form-control" name="referencia" pattern="^[A-Za-z0-9-]{1,20}$" title="Introduce un número de referencia de pagomovil (opcional)">
                        </div>
                    
<!--por el momento no se incluye (a alguien se le olvido implementar funciones para guardar esto en el backend, el sabe q esto es con el)-->
                        <!--div class="col-md-6 form-group">
                            <label for="captura_ci" style="font-size: 0.85rem;">Imagen de la cédula de indentidad</label>
                            <input type="file" style="font-size: 0.85rem;" class="form-control-file" name="captura_ci" id="captura_ci" accept="image/*" required>
                            <small id="nombre-ci-archivo" class="form-text text-muted"></small>
                        </div-->
                </div>
          
<!--/div> <!--padre--><!--no tengo idea de q hace esto pero nose-->

    <br>
      
    <p class="text-center">
      <button type="submit" class="btn btn-info btn-sm" id="btn-guardar" style="width:100%;"><i class="far fa-save"></i> &nbsp; GUARDAR</button>
    </p>
  </form>
</div>




<!-- Modal con 3 párrafos -->
<div id="modalAyuda" class="modal" tabindex="-1" 
     style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.75);">
  <div style="position:relative; margin:auto; top:20%; max-width:60%; background:#000; border:3px solid #fff; padding:20px; border-radius:8px; color:white; text-align:center;">
    
    <p id="p1" style="display:none; font-size:1.2rem; line-height:1.6;">
        Por favor, complete el formulario para iniciar el proceso de inscripción. Se solicitarán datos personales del estudiante, así como la realización de una transacción para el pago de la inscripción. Una vez que el personal reciba esta información y verifique su validez, se procederá a registrar al estudiante en el sistema y a crearle una cuenta de acceso con la clave registrada."
    </p>
    
    <p id="p2" style="display:none; font-size:1.2rem; line-height:1.6;">
        Solo se registran dígitos de números telefónicos, no caracteres como " _ ", "()", " - ". Ej: "04123334444".
    </p>
    
    <p id="p3" style="display:none; font-size:1.2rem; line-height:1.6;">
        Solo se registran dígitos de números de WhatsApp, no caracteres como " _ ", "()", " - ", " + ". Ej: "584123334444".
    </p>

    <p id="p4" style="display:none; font-size:1.2rem; line-height:1.6;">
		Al concretar la solicitud de inscripción se creará también un usuario con el cual el estudiante podrá entrar en nuestra página web donde podrá registrar sus pagos y consultar sus calificaciones.
      </p>
    <button onclick="document.getElementById('modalAyuda').style.display='none'" 
            style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light">
      <i class="fas fa-times"></i>
    </button>
  </div>
</div>


<!-- Librerías CSS y JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// Inicializar Select2 (Asegúrate de que tu <select> tenga la clase "select2")
$(document).ready(function() {
    if (window.jQuery && $('.select2').length) {
        $('.select2').select2({
            placeholder: "Seleccione un curso",
            allowClear: true,
            width: '100%'
        });
    }
});
</script>

<script>
// Script para abrir el modal mostrando el párrafo correcto
document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("modalAyuda");

  document.querySelectorAll(".btn-ayuda").forEach(btn => {
    btn.addEventListener("click", () => {
      modal.querySelectorAll("p").forEach(p => p.style.display = "none");
      const target = btn.dataset.target;
      const parrafo = document.getElementById(target);
      if (parrafo) parrafo.style.display = "block";
      modal.style.display = "block";
    });
  });
});
</script>

<script>
// Imagen comprobante de pago
document.addEventListener("DOMContentLoaded", () => {
  const input = document.getElementById("captura_pago");
  const nombreArchivo = document.getElementById("nombre-archivo");
  if (input) {
    input.addEventListener("change", () => {
      const archivo = input.files[0];
      nombreArchivo.textContent = archivo ? `Archivo seleccionado: ${archivo.name}` : "";
    });
  }
});
</script>

<!--script>
// Imagen CI
document.addEventListener("DOMContentLoaded", () => {
  const input = document.getElementById("captura_ci");
  const nombreArchivo = document.getElementById("nombre-ci-archivo");
  if (input) {
    input.addEventListener("change", () => {
      const archivo = input.files[0];
      nombreArchivo.textContent = archivo ? `Archivo seleccionado: ${archivo.name}` : "";
    });
  }
});
</script-->

<script>
document.addEventListener("DOMContentLoaded", () => {
  const selectCurso = document.getElementById("curso_up");
  const pagoTexto = document.getElementById("pagoInscripcionTexto");
  const btnGuardar = document.getElementById("btn-guardar");

  const cedulaInput = document.getElementById("cedula");
  const telefonoInput = document.getElementById("telefono");
  const wspInput = document.getElementById("wsp");
  const fechaInput = document.getElementById("fecha_nac");
  const claveInput = document.getElementById("clave");
  const confirmarInput = document.getElementById("confirmar_clave");

  const estados = {
    cedula: false,
    telefono: false,
    wsp: false,
    fecha: false,
    clave: false,
    confirmar: false
  };

  function actualizarAlerta(idAlerta, hayError, mensajeError, mensajeOk) {
    const div = document.getElementById(idAlerta);
    if (!div) return;
    div.classList.remove("text-success", "text-danger");
    div.classList.add(hayError ? "text-danger" : "text-success");
    div.innerHTML = hayError
      ? `<i class="fas fa-times-circle"></i> ${mensajeError}`
      : `<i class="fas fa-check-circle"></i> ${mensajeOk}`;
  }

  function verificarEstadoBoton() {
    if (!btnGuardar) return;
    const hayError = Object.values(estados).some(v => v === true);
    btnGuardar.disabled = hayError;
    btnGuardar.style.opacity = hayError ? "0.5" : "1";
    btnGuardar.style.cursor = hayError ? "not-allowed" : "pointer";
  }

  function validarCedula() {
    if (!cedulaInput) return;
    const valor = cedulaInput.value.trim().toUpperCase();
    if (!valor) { estados.cedula = false; actualizarAlerta("alerta-cedula", false, "", "Formato: V12345678 (V o E + 7-8 dígitos)"); verificarEstadoBoton(); return; }
    const ok = /^[VE][1-9][0-9]{6,7}$/.test(valor);
    estados.cedula = !ok;
    actualizarAlerta("alerta-cedula", !ok, "Formato inválido. Ej: V12345678", "Cédula válida");
    verificarEstadoBoton();
  }

  function validarTelefono() {
    if (!telefonoInput) return;
    const valor = telefonoInput.value.trim();
    if (!valor) { estados.telefono = false; actualizarAlerta("alerta-telefono", false, "", "11 dígitos con código de operadora válido"); verificarEstadoBoton(); return; }
    const prefijos = ["0212","0412","0414","0415","0416","0417","0418","0424","0426","0246","0247","0235","0238","0239"];
    const prefijo = valor.substring(0, 4);
    const sufijo = valor.substring(4);
    const ok = /^[0-9]{11}$/.test(valor) && prefijos.includes(prefijo) && !/^0+$/.test(sufijo);
    estados.telefono = !ok;
    actualizarAlerta("alerta-telefono", !ok, "Debe tener 11 dígitos y código válido (ej: 04123334444)", "Teléfono válido");
    verificarEstadoBoton();
  }

  function validarWsp() {
    if (!wspInput) return;
    const valor = wspInput.value.trim();
    if (!valor) { estados.wsp = false; actualizarAlerta("alerta-wsp", false, "", "12 dígitos: 58 + código + número"); verificarEstadoBoton(); return; }
    const codigos = ["212","412","414","415","416","417","418","424","426","235"];
    const ok = /^\d{12}$/.test(valor)
      && /[1-9]/.test(valor.charAt(0))
      && codigos.includes(valor.substring(2, 5))
      && !/^0{7}$/.test(valor.substring(5));
    estados.wsp = !ok;
    actualizarAlerta("alerta-wsp", !ok, "Debe ser 58 + código (ej: 584123334444)", "WhatsApp válido");
    verificarEstadoBoton();
  }

  function validarFecha() {
    if (!fechaInput) return;
    const valor = fechaInput.value;
    if (!valor) { estados.fecha = false; actualizarAlerta("alerta-fecha", false, "", "Debe tener al menos 9 años"); verificarEstadoBoton(); return; }
    const fechaIngresada = new Date(valor);
    const hoy = new Date();
    const limite = new Date(hoy.getFullYear() - 9, hoy.getMonth(), hoy.getDate());
    const ok = fechaIngresada <= limite;
    estados.fecha = !ok;
    actualizarAlerta("alerta-fecha", !ok, "Debe tener al menos 9 años cumplidos", "Fecha válida");
    verificarEstadoBoton();
  }

    
function validarClave() {
    if (!claveInput) return;
    const valor = claveInput.value;

    // Vacío → no marcar error, solo mostrar la regla
    if (!valor) {
        estados.clave = false;
        actualizarAlerta("alerta-clave", false, "",
            "Mín. 8 caracteres, 1 mayúscula, 1 minúscula, 1 número");
        verificarEstadoBoton();
        return;
    }

    // Evaluar cada regla por separado para decir cuál falla
    const errores = [];
    if (valor.length < 8)          errores.push("mínimo 8 caracteres");
    if (!/[A-Z]/.test(valor))      errores.push("1 mayúscula");
    if (!/[a-z]/.test(valor))      errores.push("1 minúscula");
    if (!/[0-9]/.test(valor))      errores.push("1 número");
    if (!/^[A-Za-z0-9]+$/.test(valor)) errores.push("sin caracteres especiales");

    const ok = errores.length === 0;
    estados.clave = !ok;

    if (ok) {
        actualizarAlerta("alerta-clave", false, "", "Clave válida");
    } else {
        actualizarAlerta("alerta-clave", true,
            "Falta: " + errores.join(", "), "");
    }
    verificarEstadoBoton();
}
    
    

  function validarConfirmar() {
    if (!confirmarInput) return;
    const confirmar = confirmarInput.value;
    if (!confirmar) { estados.confirmar = false; actualizarAlerta("alerta-confirmar-clave", false, "", "Debe coincidir con la clave"); verificarEstadoBoton(); return; }
    const ok = confirmar.length >= 7 && confirmar === claveInput.value;
    estados.confirmar = !ok;
    actualizarAlerta("alerta-confirmar-clave", !ok, "Las claves no coinciden", "Las claves coinciden");
    verificarEstadoBoton();
  }

  // ACTUALIZAR PAGO 
  function actualizarPago() {
    if (!selectCurso) return;
    
    // Obtenemos el option seleccionado usando jQuery y los elementos nativos de forma segura
    let pago = 0;
    const selectedOption = selectCurso.options[selectCurso.selectedIndex];
    
    if (selectedOption && selectedOption.hasAttribute('data-inscripcion')) {
        pago = parseFloat(selectedOption.getAttribute('data-inscripcion')) || 0;
    }

    if (pagoTexto) {
        pagoTexto.textContent = pago > 0
            ? `Pago de inscripción: ${pago.toFixed(2)}$`
            : "Pago de inscripción: No definido";
    }
      
    const montoInput = document.getElementById("monto");
    if (montoInput) {
        montoInput.value = pago > 0 ? pago.toFixed(2) : "0";
    }
  }

  // Eventos para Select2 y Select Nativo sincronizados
  if (window.jQuery && $('#curso_up').length) {
      $('#curso_up').on('change select2:select select2:clear', function () {
          actualizarPago();
      });
  } else if (selectCurso) {
      selectCurso.addEventListener("change", actualizarPago);
  }

  // Listeners de los demás campos
  if (cedulaInput) cedulaInput.addEventListener("input", validarCedula);
  if (telefonoInput) telefonoInput.addEventListener("input", validarTelefono);
  if (wspInput) wspInput.addEventListener("input", validarWsp);
  if (fechaInput) fechaInput.addEventListener("change", validarFecha);
    
    
if (claveInput) {
    claveInput.addEventListener("input", function() {
        validarClave();
        // Si ya hay algo en "confirmar", revalidar porque la clave cambió
        if (confirmarInput && confirmarInput.value) {
            validarConfirmar();
        }
    });
}
if (confirmarInput) confirmarInput.addEventListener("input", validarConfirmar);
    

  // Inicialización de estados
  actualizarPago();
  validarCedula();
  validarTelefono();
  validarWsp();
  validarFecha();
  validarClave();
  validarConfirmar();
  verificarEstadoBoton();
});
</script>


<!--limpiar cache-->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('form.FormularioAjax');
    if (!form) return;

    const STORAGE_KEY = 'solicitud_inscripcion_cache';

    // CAMPOS QUE SÍ SE GUARDAN
    // (excluye contraseñas y archivos)
    const camposAGuardar = [
        'nombre', 'apellido',
        'fecha_nacimiento', 'cedula', 'telefono', 'wsp',
        'correo', 'direccion', 'lugar_n',
        'nivel_est', 'oficio', 'trabajo',
        'monto', 'fecha_pago', 'metodo_pago'
    ];

    function guardarCache() {
        const datos = {};

        camposAGuardar.forEach(nombre => {
            const input = form.querySelector(`[name="${nombre}"]`);
            if (!input) return;

            if (input.type === 'checkbox') {
                datos[nombre] = input.checked;
            } else {
                datos[nombre] = input.value;
            }
        });

        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(datos));
        } catch (e) {
            console.warn('No se pudo guardar en localStorage:', e);
        }
    }


    function restaurarCache() {
        let datos = null;
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return;
            datos = JSON.parse(raw);
        } catch (e) {
            return;
        }

        if (!datos || typeof datos !== 'object') return;

        camposAGuardar.forEach(nombre => {
            const input = form.querySelector(`[name="${nombre}"]`);
            if (!input || datos[nombre] === undefined) return;

            if (input.type === 'checkbox') {
                input.checked = !!datos[nombre];
            } else {
                input.value = datos[nombre];
            }

            // Disparar eventos para que las validaciones se actualicen
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    // LISTENERS
    form.addEventListener('input', guardarCache);
    form.addEventListener('change', guardarCache);

    // Restaurar al cargar
    restaurarCache();
});
</script>
