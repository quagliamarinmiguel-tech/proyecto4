<?php 
if($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}


$cargo = $_SESSION['cargo_spm'] ?? 1;

$carpetasPorCargo = [
    1 => "admin",
    2 => "Control de estudio",
    3 => "administrador de pagos",
    4 => "Supervisor de cursos",
    5 => "Estudiante"
];

$carpeta = $carpetasPorCargo[$cargo] ?? "admin";




// ID encriptado
$idEnc = $_GET['profesor_id_up'] ?? '';
require_once "./controladores/profesorControlador.php";
$ctrl = new profesorControlador();
$idReal = mainModel::decryption($idEnc); 
$resultado = $ctrl->datos_profesor_controlador($idEnc);

if (!$resultado['ok'] || empty($resultado['data'][0])) {
    echo '<div class="alert alert-danger text-center">No se encontró el profesor.</div>';
    exit();
}

$profesor = $resultado['data'][0];
//var_dump($profesor);
//die();
$idReal = mainModel::decryption($idEnc); 
?>


<style>
fieldset {
  border: 1px solid #ccc;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 30px;
  background-color: #f9f9f9;
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

<div class="full-box page-header">
    <h3 class="text-left"><i class="fas fa-chalkboard-teacher fa-fw"></i> &nbsp; ACTUALIZAR PROFESOR</h3>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=profesor-new"><i class="fas fa-calendar-plus fa-fw"></i> &nbsp; NUEVO PROFESOR</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=profesor-list"><i class="fas fa-calendar-alt fa-fw"></i> &nbsp; LISTA DE PROFESORES</a>
        </li>

    </ul>   
</div>


<div class="container-fluid">
    <form id="form-update-profesor" class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/profesorAjax.php" method="POST" data-form="update" enctype="multipart/form-data" autocomplete="off">

        <input type="hidden" name="profesor_id_up" value="<?php echo $idEnc; ?>">
        <input type="hidden" name="estado_profesor" value="<?php echo $profesor['state']; ?>">
        <!--input type="hidden" name="profe_mostrar" value="<?php echo $profesor['show_teacher']; ?>"-->
        <input type="hidden" name="imagen_fk" value="<?php echo $profesor['img_id']; ?>">
        <fieldset>
            <legend><i class="far fa-id-card"></i> &nbsp; Datos personales</legend>
            <div class="text-danger fw-bold" id="alerta-profesor" style="display:none; font-size: 1rem; margin-top: 0.5rem;"></div>

            <div class="row">


                <div class="col-md-6 mb-3">
                    <!--label class="form-label">Cédula</label-->
                    <div class="input-group">
                      
                        <!-- Cédula completa -->
                        <div class="col-md-6 mb-3">
                            <div class="text-success small mt-1" id="alerta-cedula-repetida" style="font-weight: bold;">
                                <i class="fas fa-check-circle"></i> La cédula no debe repetirse en el sistema
                            </div>
                            <label class="form-label fw-bold text-primary">Cédula</label>
                            <input type="text" id="ci_input" name="ci_reg" class="form-control" maxlength="10" pattern="[VEve]{1}[1-9]{1}[0-9]{6,7}" placeholder="ej:V12345678" value="<?php echo htmlspecialchars($profesor['ci']); ?>" required>
                            <small class="text-muted">Debe comenzar con V o E seguido de hasta 8 dígitos.</small>
                        </div>

                    </div>
                </div>


                <!-- Nombre -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">Nombre</label>
                    <input type="text" class="form-control" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,19}" name="nombre_reg" maxlength="20" required
                        value="<?php echo htmlspecialchars($profesor['name']); ?>">
                </div>

                <!-- Apellido -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">Apellido</label>
                    <input type="text" class="form-control" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,19}" name="apellido_reg" maxlength="20" required
                        value="<?php echo htmlspecialchars($profesor['last_name']); ?>">
                </div>

                <!-- Correo -->
                <div class="col-md-6 mb-3">
                    <div class="text-success small mt-1" id="alerta-correo-repetido" style="font-weight: bold;">
                        <i class="fas fa-check-circle"></i> El correo no debe repetirse en el sistema
                    </div>
                    <label class="form-label fw-bold text-primary">Correo electrónico</label>
                    <input type="text" class="form-control" name="correo_reg" maxlength="60" required
                        value="<?php echo htmlspecialchars($profesor['email']); ?>">
                </div>

                <!-- Teléfono -->
                <div class="col-md-6 mb-3">
                    <div class="text-success small mt-1" id="alerta-telefono-repetido" style="font-weight: bold;">
                        <i class="fas fa-check-circle"></i> El teléfono no debe repetirse en el sistema
                    </div>
                    <div class="text-success small mt-1" id="alerta-telefono-formato" style="font-weight: bold;">
                        <i class="fas fa-check-circle"></i> El teléfono debe tener 11 caracteres y prefijo válido
                    </div>
                    <label class="form-label fw-bold text-primary">Teléfono</label>
                    <input type="tel" class="form-control" name="telefono_reg" maxlength="12"
                        pattern="[0-9]{7,12}" value="<?php echo htmlspecialchars($profesor['phone']); ?>">
                </div>

                <!-- Descripción -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">Descripción profesional</label>
                    <textarea name="descripcion_reg" class="form-control" rows="3" maxlength="120"><?php echo htmlspecialchars($profesor['description'] ?? ''); ?></textarea>
                </div>

                <!-- Imagen actual -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">Imagen actual</label><br>
                    <img src="<?php echo SERVERURL.'vistas/'.$profesor['img']['route']; ?>" alt="Imagen actual" class="img-thumbnail" style="max-width:200px;">
                </div>

                <!-- Imagen nueva -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">Reemplazar imagen (opcional)</label>
                    <input type="file" id="archivo_imagen" name="archivo_imagen" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
                    <div class="mt-2" id="preview-imagen" style="display:none;">
                        <img src="" alt="Preview" style="max-width:200px;" class="img-thumbnail">
                    </div>
                    <small class="text-muted">Si no seleccionas una nueva imagen, se conservará la actual.</small>
                </div>

                <!-- Mostrar profesor (1 = sí, 2 = no) -->
                <!--div class="col-md-6 mb-3">
                    <label class="form-label">¿Mostrar al profesor en la plataforma?</label>
                    <select name="profe_mostrar" class="form-select" required>
                        <option value="1" <php echo ($profesor['profe_mostrar'] == 1) ? 'selected' : ''; ?>>Sí</option>
                        <option value="2" <php echo ($profesor['profe_mostrar'] == 2) ? 'selected' : ''; ?>>No</option>
                    </select>
                    <small class="text-muted">Esta opción controla si el perfil del profesor está visible públicamente.</small>
                </div-->

                <div class="col-md-6 mb-4">
                <label for="profe_mostrar" class="form-label fw-bold text-primary mb-2 d-flex align-items-center">
                    ¿Mostrar al profesor en la plataforma?   .
                    <button type="button" class="btn btn-sm ver-ayuda ms-2"
                            data-src="<?php echo SERVERURL; ?>vistas/assets/img/imagenprofecontexto.webp"
                            title="Ver explicación"
                            style="background-color: #3CA6A6; color: #fff; border: none; padding: 4px 8px; border-radius: 4px;">
                    <i class="fas fa-question"></i>
                    </button>
                </label>

                <select name="profe_mostrar" class="form-select" required>
                    <option value="1" <?php echo ($profesor['show_teacher'] == 1) ? 'selected' : ''; ?>>Sí</option>
                    <option value="0" <?php echo ($profesor['show_teacher'] == 0) ? 'selected' : ''; ?>>No</option>
                </select>

                <div class="form-text text-muted mt-1">
                    Esta opción controla si el perfil del profesor está visible públicamente.
                </div>
                </div>


            </div>
        </fieldset>

        <br>
        <p class="text-center">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-sync-alt"></i> ACTUALIZAR</button>
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
  document.querySelectorAll(".ver-ayuda").forEach(btn => {
    btn.addEventListener("click", () => {
      const modal = document.getElementById("modalAyuda");
      modal.querySelector("#imgAyuda").src = btn.dataset.src;
      modal.style.display = "block";
    });
  });
});
</script>


<script>
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("form-update-profesor");
    const ciInput = document.getElementById("ci_input");
    const inputImagen = document.getElementById("archivo_imagen");
    const preview = document.getElementById("preview-imagen");
    const previewImg = preview.querySelector("img");

    // 🖼️ Preview de imagen y detección de cambio
    inputImagen.addEventListener("change", () => {
        const file = inputImagen.files[0];
        if (!file) {
            preview.style.display = "none";
            return;
        }

        const ext = file.name.split('.').pop().toLowerCase();
        if (!["jpg", "jpeg", "png", "webp"].includes(ext)) {
            alert("Formato de imagen no permitido.");
            inputImagen.value = "";
            preview.style.display = "none";
            return;
        }

        // 📌 Crear campo oculto imagen_cambiada = 1 si aún no existe
        if (!form.querySelector('input[name="imagen_cambiada"]')) {
            const campoCambio = document.createElement("input");
            campoCambio.type = "hidden";
            campoCambio.name = "imagen_cambiada";
            campoCambio.value = "1";
            form.appendChild(campoCambio);
        }

        const reader = new FileReader();
        reader.onload = () => {
            previewImg.src = reader.result;
            preview.style.display = "block";
        };
        reader.readAsDataURL(file);
    });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // ==============================================
    // REFERENCIAS A ELEMENTOS
    // ==============================================
    const ciInput = document.getElementById("ci_input");
    const correoInput = document.querySelector("input[name='correo_reg']");
    const telefonoInput = document.querySelector("input[name='telefono_reg']");
    const btnSubmit = document.querySelector("button[type='submit']");
    const SERVERURL = window.SERVERURL || "<?php echo SERVERURL; ?>";
    const profesorId = <?php echo $idReal; ?>; // 🔥 ID REAL del profesor en edición

    // IDs de los divs de alerta (deben existir en el HTML)
    const ALERTAS = {
        CI: "alerta-cedula-repetida",
        CORREO: "alerta-correo-repetido",
        TELEFONO: "alerta-telefono-repetido",
        TELEFONO_FORMATO: "alerta-telefono-formato"
    };

    // ==============================================
    // ESTADO DE VALIDACIONES
    // ==============================================
    let estadoValidaciones = {
        ci: false,
        correo: false,
        telefono_duplicado: false,
        telefono_formato: false
    };

    // ==============================================
    // FUNCIÓN PARA ACTUALIZAR ALERTA (color, ícono, texto)
    // ==============================================
    function actualizarAlerta(idAlerta, hayError, mensaje = null) {
        const div = document.getElementById(idAlerta);
        if (!div) return;

        if (!div.getAttribute("data-mensaje-original")) {
            const textoOriginal = div.textContent.trim();
            div.setAttribute("data-mensaje-original", textoOriginal);
        }

        div.classList.remove("text-success", "text-danger");
        div.classList.add(hayError ? "text-danger" : "text-success");

        if (hayError) {
            div.innerHTML = `<i class="fas fa-times-circle"></i> ${mensaje || "Error"}`;
        } else {
            const mensajeOriginal = div.getAttribute("data-mensaje-original") || "Campo válido";
            div.innerHTML = `<i class="fas fa-check-circle"></i> ${mensajeOriginal}`;
        }
    }

    // ==============================================
    // FUNCIÓN PARA BLOQUEAR/DESBLOQUEAR BOTÓN
    // ==============================================
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

    // ==============================================
    // FUNCIÓN PARA LIMPIAR ERRORES DE UN CAMPO
    // ==============================================
    function limpiarErrorCampo(campo) {
        estadoValidaciones[campo] = false;
        if (campo === 'ci') actualizarAlerta(ALERTAS.CI, false);
        if (campo === 'correo') actualizarAlerta(ALERTAS.CORREO, false);
        if (campo === 'telefono_duplicado') actualizarAlerta(ALERTAS.TELEFONO, false);
        if (campo === 'telefono_formato') actualizarAlerta(ALERTAS.TELEFONO_FORMATO, false);
        verificarEstadoBoton();
    }

    // ==============================================
    // VALIDACIÓN INDIVIDUAL: CÉDULA (con ID)
    // ==============================================
    let timeoutCI = null;
    function validarCI() {
        clearTimeout(timeoutCI);
        const valor = ciInput.value.trim();

        if (!valor) {
            limpiarErrorCampo('ci');
            return;
        }

        timeoutCI = setTimeout(() => {
            fetch(`${SERVERURL}ajax/profesorAjax.php?accion=validar_cedula_duplicada&ci=${encodeURIComponent(valor)}&excluir=${profesorId}`)
                .then(res => res.json())
                .then(data => {
                    const duplicado = data.duplicado === true;
                    estadoValidaciones.ci = duplicado;
                    actualizarAlerta(ALERTAS.CI, duplicado, duplicado ? "La cédula ya está registrada" : null);
                    verificarEstadoBoton();
                })
                .catch(err => {
                    console.error("Error validando cédula:", err);
                    estadoValidaciones.ci = false;
                    verificarEstadoBoton();
                });
        }, 2000);
    }

    // ==============================================
    // VALIDACIÓN INDIVIDUAL: CORREO (con ID)
    // ==============================================
    let timeoutCorreo = null;
    function validarCorreo() {
        clearTimeout(timeoutCorreo);
        const valor = correoInput.value.trim();

        if (!valor) {
            limpiarErrorCampo('correo');
            return;
        }

        timeoutCorreo = setTimeout(() => {
            fetch(`${SERVERURL}ajax/profesorAjax.php?accion=validar_correo_duplicado&correo=${encodeURIComponent(valor)}&excluir=${profesorId}`)
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

    // ==============================================
    // VALIDACIÓN INDIVIDUAL: TELÉFONO (formato + duplicado con ID)
    // ==============================================
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
            // 1. Validar formato (sin ID)
            fetch(`${SERVERURL}ajax/profesorAjax.php?accion=validar_telefono_formato&telefono=${encodeURIComponent(valor)}`)
                .then(res => res.json())
                .then(data => {
                    const formatoValido = data.formato_valido === true;
                    estadoValidaciones.telefono_formato = !formatoValido;
                    actualizarAlerta(ALERTAS.TELEFONO_FORMATO, !formatoValido, !formatoValido ? "El teléfono no tiene un formato válido" : null);
                    verificarEstadoBoton();

                    // 2. Si el formato es válido, validar duplicado (con ID)
                    if (formatoValido) {
                        fetch(`${SERVERURL}ajax/profesorAjax.php?accion=validar_telefono_duplicado&telefono=${encodeURIComponent(valor)}&excluir=${profesorId}`)
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

    // ==============================================
    // EVENTOS: cada campo tiene su propio listener
    // ==============================================
    ciInput.addEventListener("input", validarCI);
    correoInput.addEventListener("input", validarCorreo);
    telefonoInput.addEventListener("input", validarTelefono);

    // ==============================================
    // INICIALIZAR
    // ==============================================
    Object.values(ALERTAS).forEach(id => {
        const div = document.getElementById(id);
        if (div) {
            const textoOriginal = div.textContent.trim();
            div.setAttribute("data-mensaje-original", textoOriginal);
        }
    });

    // Si hay valores precargados, validar al inicio
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