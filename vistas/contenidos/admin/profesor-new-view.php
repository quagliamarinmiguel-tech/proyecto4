<?php
    $state = $_SESSION['profile_status'] ?? 0;
	$cargo = $_SESSION['cargo_spm'] ?? 0;
	$carpeta =  "admin";

    if (!in_array($cargo, [1, 2,4]) || $state != 1) {
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }

?>
<style>
fieldset {
  border: 1px solid #ccc;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 30px;
  background-color: inherit;
}
legend {
  font-size: 1.2rem;
  font-weight: bold;
  color: #007bff;
}
input[type="text"],
input[type="email"],
input[type="tel"],
textarea,
select {
  border-radius: 6px;
}
#btn-guardar-profesor {
  padding: 10px 30px;
  font-size: 0.95rem;
}
#preview-imagen img {
  border: 2px solid #007bff;
}
</style>
<script>
    window.SERVERURL = "<?php echo SERVERURL; ?>";
</script>
<script src="<?php echo SERVERURL; ?>vistas/js/miscript.js"></script>
<div class="full-box page-header">
    <h3 class="text-left"><i class="fas fa-chalkboard-teacher fa-fw"></i> &nbsp; NUEVO PROFESOR</h3>
</div>



<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=profesor-new"><i class="fas fa-calendar-plus fa-fw"></i> &nbsp; NUEVO PROFESOR</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=profesor-list"><i class="fas fa-calendar-alt fa-fw"></i> &nbsp; LISTA DE PROFESORES</a>
        </li>
       <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=profesor-list-archived"><i class="fas fa-calendar-alt fa-fw"></i> &nbsp; LISTA DE PROFESORES ARCHIVADOS</a>
        </li>
    </ul>   
</div>

<div class="container-fluid">
    <form id="form-profesor" class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/profesorAjax.php" method="POST" data-form="save" enctype="multipart/form-data" autocomplete="off"  data-cache="true">
        <input type="hidden" name="ci_reg" id="ci_reg_combinada">
        <input type="hidden" name="estado_profesor" value="1">

        <fieldset>
            <legend><i class="far fa-id-card"></i> &nbsp; Datos personales</legend>
            <div class="text-danger fw-bold" id="alerta-profesor" style="display:none; font-size: 1rem; margin-top: 0.5rem;"></div>
            <br><br>
            <div class="row">
                <!-- Cédula -->
                <div class="col-md-6 mb-3">
                    <div class="input-group">
                        <div class="col-md-6 mb-3">
                            <div class="text-success small mt-1" id="alerta-cedula-repetida" style="font-weight: bold;">
                                <i class="fas fa-check-circle"></i> La cédula no debe repetirse en el sistema
                            </div>
                            <label class="form-label fw-bold text-primary">Cédula</label>
                            <input type="text" id="ci_input" name="ci_reg" class="form-control" maxlength="10" pattern="[VE]{1}[1-9]{1}[0-9]{6,7}" placeholder="ej:V12345678" required>
                            <small class="text-muted">Debe comenzar con V o E seguido de hasta 8 dígitos.</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">Nombre</label>
                    <input type="text" class="form-control" name="nombre_reg" maxlength="20" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,19}"  required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">Apellido</label>
                    <input type="text" class="form-control" name="apellido_reg" maxlength="20" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,19}" required>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="text-success small mt-1" id="alerta-correo-repetido" style="font-weight: bold;">
                        <i class="fas fa-check-circle"></i> El correo no debe repetirse en el sistema
                    </div>
                    <label class="form-label fw-bold text-primary">Correo electrónico</label>
                    <input type="text" 
                           class="form-control"        
                           name="correo_reg" maxlength="60"  title="Solo se permiten correos personales como Gmail, Yahoo, Outlook, etc."
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="text-success small mt-1" id="alerta-telefono-repetido" style="font-weight: bold;">
                        <i class="fas fa-check-circle"></i> El teléfono no debe repetirse en el sistema
                    </div>
                    <div class="text-success small mt-1" id="alerta-telefono-formato" style="font-weight: bold;">
                        <i class="fas fa-check-circle"></i> El teléfono debe debe ser de 11 caracteres y pertenecer a una zona geográfica de Guárico o compañia movil del área
                    </div>
                    <label class="form-label fw-bold text-primary">Teléfono</label>
                    <input type="tel" class="form-control" name="telefono_reg" maxlength="12" pattern="[0-9]{7,12}">
                </div>

                <div class="col-md-6 mb-3" id="descripcion_profesor">
                    <label class="form-label fw-bold text-primary">Descripción profesional</label>
                    <textarea name="descripcion_reg" class="form-control" rows="3" maxlength="120" data-no-cache></textarea>
                </div>

                <div class="col-md-6 mb-4">
                <label for="profe_mostrar" class="form-label fw-bold text-primary mb-2">
                    Mostrar al profesor en la página publicitaria?

    <button type="button" class="btn btn-sm ver-ayuda" 
            data-src="<?php echo SERVERURL; ?>vistas/assets/img/imagenprofecontexto.webp" 
            title="Ver explicación"
            style="background-color: #3CA6A6; color: #fff; border: none; padding: 4px 8px; border-radius: 4px;">
    <i class="fas fa-question"></i>
    </button>

                </label>
                <select name="profe_mostrar" id="profe_mostrar" class="form-select" required data-no-cache>
                    <option value="" disabled selected> - Seleccione - </option>
                    <option value="1">Sí</option>
                    <option value="0">No</option>
                </select>
                <div class="form-text text-muted mt-1">
                    Esto determinará si los datos aquí registrados podrán ser vistos por los visitantes del portal publicitario.
                </div>
                </div>

                <!-- Imagen -->
                <div class="col-md-6 mb-3" id="imagen_profesor" >
                    <label class="form-label">Imagen del profesor (.png, .jpg, .webp)</label>
                    <input type="file" id="archivo_imagen" name="archivo_imagen" class="form-control-file" accept=".jpg,.jpeg,.png,.webp" data-no-cache>
                    <div class="mt-2" id="preview-imagen" style="display:none;">
                        <img src="" alt="Preview" style="max-width:200px;" class="img-thumbnail">
                    </div>
                </div>
            </div>
        </fieldset>

        <br>

        <br>
        <p class="text-center">
            <button type="submit" class="btn btn-info btn-sm" id="btn-guardar-profesor" >
                <i class="far fa-save"></i> GUARDAR
            </button>
        </p>
    </form>
</div>



<div id="modalAyuda" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75);">
  <div style="position:relative; margin:auto; top:10%; max-width:60%; background:#000; border:3px solid #fff; padding:20px; border-radius:8px;">
    <img id="imgAyuda" src="" class="img-thumbnail" style="max-width:100%; height:auto; display:block; margin:auto; border:3px solid white;">
    <button onclick="document.getElementById('modalAyuda').style.display='none'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light">
      <i class="fas fa-times"></i>
    </button>
  </div>
</div>




<script>
document.addEventListener("DOMContentLoaded", () => {
  const selectMostrar = document.getElementById("profe_mostrar");
  const descripcionDiv = document.getElementById("descripcion_profesor");
  const imagenDiv = document.getElementById("imagen_profesor");

  function actualizarVisibilidad() {
    const valor = selectMostrar.value;
    const mostrar = valor === "1";

    descripcionDiv.style.display = mostrar ? "block" : "none";
    imagenDiv.style.display = mostrar ? "block" : "none";
  }

  selectMostrar.addEventListener("change", actualizarVisibilidad);

  // Ejecutar al cargar por si ya hay un valor seleccionado
  actualizarVisibilidad();
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // REFERENCIAS A ELEMENTOS
    const ciInput = document.getElementById("ci_input");
    const correoInput = document.querySelector("input[name='correo_reg']");
    const telefonoInput = document.querySelector("input[name='telefono_reg']");
    const btnSubmit = document.querySelector("button[type='submit']");
    const SERVERURL = window.SERVERURL || "<?php echo SERVERURL; ?>";

    // IDs de los divs de alerta
    const ALERTAS = {
        CI: "alerta-cedula-repetida",
        CORREO: "alerta-correo-repetido",
        TELEFONO: "alerta-telefono-repetido",
        TELEFONO_FORMATO: "alerta-telefono-formato"
    };

   
    let estadoValidaciones = {
        ci: false,        // false = sin error
        correo: false,
        telefono_duplicado: false,
        telefono_formato: false
    };

   
    function actualizarAlerta(idAlerta, hayError, mensaje = null) {
        const div = document.getElementById(idAlerta);
        if (!div) return;

        // Guardar mensaje original si no se ha guardado
        if (!div.getAttribute("data-mensaje-original")) {
            const textoOriginal = div.textContent.trim();
            div.setAttribute("data-mensaje-original", textoOriginal);
        }

        // Cambiar color
        div.classList.remove("text-success", "text-danger");
        div.classList.add(hayError ? "text-danger" : "text-success");

        // Cambiar ícono y texto
        if (hayError) {
            div.innerHTML = `<i class="fas fa-times-circle"></i> ${mensaje || "Error"}`;
        } else {
            const mensajeOriginal = div.getAttribute("data-mensaje-original") || "Campo válido";
            div.innerHTML = `<i class="fas fa-check-circle"></i> ${mensajeOriginal}`;
        }
    }


    function verificarEstadoBoton() {
        const hayError = Object.values(estadoValidaciones).some(valor => valor === true);
        
        btnSubmit.disabled = hayError;
        if (hayError) {
            btnSubmit.classList.add("btn-disabled");
            btnSubmit.style.opacity = "0.5";
            btnSubmit.style.cursor = "not-allowed";
        } else {
            btnSubmit.classList.remove("btn-disabled");
            btnSubmit.style.opacity = "1";
            btnSubmit.style.cursor = "pointer";
        }
    }


    function limpiarErrorCampo(campo) {
        estadoValidaciones[campo] = false;
        if (campo === 'ci') actualizarAlerta(ALERTAS.CI, false);
        if (campo === 'correo') actualizarAlerta(ALERTAS.CORREO, false);
        if (campo === 'telefono_duplicado') actualizarAlerta(ALERTAS.TELEFONO, false);
        if (campo === 'telefono_formato') actualizarAlerta(ALERTAS.TELEFONO_FORMATO, false);
        verificarEstadoBoton();
    }


    let timeoutCI = null;
    function validarCI() {
        clearTimeout(timeoutCI);
        const valor = ciInput.value.trim();

        // Si está vacío, limpiar error y salir
        if (!valor) {
            limpiarErrorCampo('ci');
            return;
        }

        // Esperar 2 segundos después del último carácter
        timeoutCI = setTimeout(() => {
            fetch(`${SERVERURL}ajax/profesorAjax.php?accion=validar_cedula_duplicada&ci=${encodeURIComponent(valor)}`)
                .then(res => res.json())
                .then(data => {
                    const duplicado = data.duplicado === true;
                    estadoValidaciones.ci = duplicado;
                    actualizarAlerta(ALERTAS.CI, duplicado, duplicado ? "La cédula ya está registrada" : null);
                    verificarEstadoBoton();
                })
                .catch(err => {
                    console.error("Error validando cédula:", err);
                    estadoValidaciones.ci = false; // En caso de error, no bloquear
                    verificarEstadoBoton();
                });
        }, 2000);
    }

 
    let timeoutCorreo = null;
    function validarCorreo() {
        clearTimeout(timeoutCorreo);
        const valor = correoInput.value.trim();

        if (!valor) {
            limpiarErrorCampo('correo');
            return;
        }

        timeoutCorreo = setTimeout(() => {
            fetch(`${SERVERURL}ajax/profesorAjax.php?accion=validar_correo_duplicado&correo=${encodeURIComponent(valor)}`)
                .then(res => res.json())
                .then(data => {
                    const duplicado = data.duplicado === true;
                    estadoValidaciones.correo = duplicado;
                    actualizarAlerta(ALERTAS.CORREO, duplicado, duplicado ? "El correo ya está registrado" : null);
                    verificarEstadoBoton();
                })
                .catch(err => {
                    console.error("Error validando correo:", err);
                    estadoValidaciones.correo = false;
                    verificarEstadoBoton();
                });
        }, 2000);
    }

  
    let timeoutTelefono = null;
    function validarTelefono() {
        clearTimeout(timeoutTelefono);
        const valor = telefonoInput.value.trim();

        if (!valor) {
            limpiarErrorCampo('telefono_formato');
            limpiarErrorCampo('telefono_duplicado');
            return;
        }

        timeoutTelefono = setTimeout(() => {
            // 1. Validar formato
            fetch(`${SERVERURL}ajax/profesorAjax.php?accion=validar_telefono_formato&telefono=${encodeURIComponent(valor)}`)
                .then(res => res.json())
                .then(data => {
                    const formatoValido = data.formato_valido === true;
                    estadoValidaciones.telefono_formato = !formatoValido;
                    actualizarAlerta(ALERTAS.TELEFONO_FORMATO, !formatoValido, !formatoValido ? "El teléfono no tiene un formato válido" : null);
                    verificarEstadoBoton();

                    // 2. Si el formato es válido, validar duplicado
                    if (formatoValido) {
                        fetch(`${SERVERURL}ajax/profesorAjax.php?accion=validar_telefono_duplicado&telefono=${encodeURIComponent(valor)}`)
                            .then(res => res.json())
                            .then(data => {
                                const duplicado = data.duplicado === true;
                                estadoValidaciones.telefono_duplicado = duplicado;
                                actualizarAlerta(ALERTAS.TELEFONO, duplicado, duplicado ? "El teléfono ya está registrado" : null);
                                verificarEstadoBoton();
                            })
                            .catch(err => {
                                console.error("Error validando teléfono duplicado:", err);
                                estadoValidaciones.telefono_duplicado = false;
                                verificarEstadoBoton();
                            });
                    } else {
                        // Si formato inválido, limpiar error de duplicado
                        estadoValidaciones.telefono_duplicado = false;
                        actualizarAlerta(ALERTAS.TELEFONO, false);
                        verificarEstadoBoton();
                    }
                })
                .catch(err => {
                    console.error("Error validando formato de teléfono:", err);
                    estadoValidaciones.telefono_formato = false;
                    verificarEstadoBoton();
                });
        }, 2000);
    }

    ciInput.addEventListener("input", validarCI);
    correoInput.addEventListener("input", validarCorreo);
    telefonoInput.addEventListener("input", validarTelefono);

   
    // Guardar mensajes originales de los divs
    Object.values(ALERTAS).forEach(id => {
        const div = document.getElementById(id);
        if (div) {
            const textoOriginal = div.textContent.trim();
            div.setAttribute("data-mensaje-original", textoOriginal);
        }
    });

    // Si hay valores precargados (edición), validar al inicio
    if (ciInput.value) setTimeout(validarCI, 500);
    if (correoInput.value) setTimeout(validarCorreo, 500);
    if (telefonoInput.value) setTimeout(validarTelefono, 500);

    // Asegurar que los divs de alerta muestren mensajes originales (verde)
    Object.values(ALERTAS).forEach(id => {
        actualizarAlerta(id, false);
    });
    verificarEstadoBoton();
});
</script>