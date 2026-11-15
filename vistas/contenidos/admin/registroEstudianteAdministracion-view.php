<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro de Estudiante</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      padding-top: 40px;
    }
    .card {
      max-width: 700px;
      margin: auto;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    label {
      font-weight: 500;
    }
    .is-invalid {
      border-color: #dc3545;
    }
    .is-valid {
      border-color: #28a745;
    }
    .invalid-feedback {
      display: none;
      width: 100%;
      margin-top: 0.25rem;
      font-size: 0.875em;
      color: #dc3545;
    }
    .spinner-border-sm {
      width: 1rem;
      height: 1rem;
    }
    .input-group-prepend .input-group-text {
      background-color: #e9ecef;
      border: 1px solid #ced4da;
    }
    .password-requirements {
      font-size: 0.8rem;
      color: #6c757d;
      margin-top: 5px;
    }
    .requirement {
      display: flex;
      align-items: center;
      margin-bottom: 3px;
    }
    .requirement i {
      margin-right: 5px;
      font-size: 0.7rem;
    }
    .requirement.met {
      color: #28a745;
    }
    .requirement.unmet {
      color: #6c757d;
    }
    .checkbox-label {
      display: flex;
      align-items: center;
      margin-bottom: 0;
    }
    .checkbox-label input {
      margin-right: 8px;
    }
  </style>
</head>
<body>

  <div class="container">
    <div class="card">
      <div class="card-header bg-primary text-white text-center">
        <h4 class="mb-0">Registrar Nuevo Estudiante</h4>
      </div>
      <div class="card-body">
        <form action="controladores/registroEstudianteControlador.php" method="POST" id="formEstudiante" novalidate>
          <div class="form-row">
			<div class="form-group col-md-6">
    			<label for="ci_estudiante">Cédula *</label>
    			<div class="input-group">
        			<select class="form-control" name="ci_tipo" id="ci_tipo" required style="max-width: 80px;">
            			<option value="V">V</option>
            			<option value="E">E</option>
        			</select>
        			<input type="text" class="form-control" name="ci_numero" id="ci_numero" 
               			required pattern="\d{8}" 
               			title="Debe contener exactamente 8 dígitos"
               			maxlength="8"
               			placeholder="12345678">
    			</div>
    			<div class="invalid-feedback" id="ci-error">
        			La cédula debe contener exactamente 8 dígitos numéricos.
    			</div>
    			<input type="hidden" name="ci_estudiante" id="ci_completo">
			</div>
            <div class="form-group col-md-6">
              <label for="nombre">Nombre(s) *</label>
              <input type="text" class="form-control" name="nombre" id="nombre" 
                     required pattern="[A-Za-záéíóúñÑ\s]{2,100}"
                     title="Solo letras y espacios (2-100 caracteres)"
                     maxlength="100">
              <div class="invalid-feedback">
                El nombre solo puede contener letras y espacios (2-100 caracteres).
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="apellido">Apellido(s) *</label>
              <input type="text" class="form-control" name="apellido" id="apellido" 
                     required pattern="[A-Za-záéíóúñÑ\s]{2,100}"
                     title="Solo letras y espacios (2-100 caracteres)"
                     maxlength="100">
              <div class="invalid-feedback">
                El apellido solo puede contener letras y espacios (2-100 caracteres).
              </div>
            </div>
			<div class="form-group col-md-6">
    			<label for="fecha_nacimiento">Fecha de nacimiento *</label>
    			<input type="date" class="form-control" name="fecha_nacimiento" id="fecha_nacimiento" 
           			required 
           			max="<?php 
               			$fecha_maxima = (new DateTime())->modify('-6 years')->format('Y-m-d');
               			echo $fecha_maxima;
           			?>">
    			<div class="invalid-feedback">
        			El estudiante debe tener al menos 6 años de edad.
    			</div>
			</div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="lugar_nacimiento">Lugar de nacimiento</label>
              <input type="text" class="form-control" name="lugar_nacimiento" id="lugar_nacimiento" 
                     maxlength="100" placeholder="Ciudad, Estado">
            </div>
            
            <div class="form-group col-md-6">
              <label for="telefono_estudiante">Teléfono *</label>
              <div class="input-group">
                <select class="form-control" name="telefono_codigo" id="telefono_codigo" required style="max-width: 100px;">
                  <option value="">Código</option>
                  <option value="0412">0412</option>
                  <option value="0414">0414</option>
                  <option value="0416">0416</option>
                  <option value="0422">0422</option>
                  <option value="0424">0424</option>
                  <option value="0426">0426</option>
                </select>
                <input type="text" class="form-control" name="telefono_numero" id="telefono_numero" 
                       required pattern="\d{7}" 
                       title="Debe contener 7 dígitos"
                       maxlength="7" placeholder="1234567">
              </div>
              <div class="invalid-feedback" id="telefono-error">
                El teléfono debe tener un código y 7 dígitos.
              </div>
            </div>
          </div>


          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="wsp_estudiante">WhatsApp</label>
              <div class="input-group">
                <select class="form-control" name="wsp_codigo" id="wsp_codigo" style="max-width: 100px;">
                  <option value="">Código</option>
                  <option value="58412">58412</option>
                  <option value="58414">58414</option>
                  <option value="58416">58416</option>
                  <option value="58422">58422</option>
                  <option value="58424">58424</option>
                  <option value="58426">58426</option>
                </select>
                <input type="text" class="form-control" name="wsp_numero" id="wsp_numero" 
                       pattern="\d{7}" 
                       title="Debe contener 7 dígitos"
                       maxlength="7" placeholder="1234567">
              </div>
              <div class="invalid-feedback" id="whatsapp-error">
                El WhatsApp debe tener 7 dígitos si se proporciona un código.
              </div>
            </div>
            
            <div class="form-group col-md-6">
              <label for="correo_estudiante">Correo electrónico *</label>
              <div class="input-group">
                <input type="text" class="form-control" name="correo_local" id="correo_local" 
                       required maxlength="50" placeholder="usuario">
                <select class="form-control" name="correo_dominio" id="correo_dominio" required style="max-width: 150px;">
                  <option value="">Dominio</option>
                  <option value="@gmail.com">@gmail.com</option>
                  <option value="@hotmail.com">@hotmail.com</option>
                  <option value="@outlook.com">@outlook.com</option>
                  <option value="@yahoo.com">@yahoo.com</option>
                </select>
              </div>
              <div class="invalid-feedback" id="correo-error">
                Por favor ingrese un correo electrónico válido.
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="direccion_estudiante">Dirección</label>
              <textarea class="form-control" name="direccion_estudiante" id="direccion_estudiante" 
                        rows="2" maxlength="200" 
                        placeholder="Ingrese la dirección completa"></textarea>
            </div>
            
            <div class="form-group col-md-6">
              <label for="nivel_estudio_estudiante">Nivel de estudio</label>
              <select class="form-control" name="nivel_estudio_estudiante" id="nivel_estudio_estudiante">
                <option value="">Seleccione...</option>
                <option value="Primaria">Primaria</option>
                <option value="Secundaria">Secundaria</option>
                <option value="Técnico">Técnico</option>
                <option value="Universitario">Universitario</option>
                <option value="Posgrado">Posgrado</option>
                <option value="Ninguno">Ninguno</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="oficio_actual_estudiante">Oficio actual</label>
              <input type="text" class="form-control" name="oficio_actual_estudiante" id="oficio_actual_estudiante" 
                     maxlength="100" 
                     placeholder="Ej: Electricista, Docente, etc.">
            </div>
            
    		<div class="form-group col-md-6">
        		<label>Trabaja actualmente: </label><br>
        		<div class="form-check form-check-inline">
            		<input class="form-check-input" type="radio" name="if_trabaja_estudiante" id="trabaja_si" value="1" checked>
            		<label class="form-check-label" for="trabaja_si">Sí</label>
        		</div>
        		<div class="form-check form-check-inline">
            		<input class="form-check-input" type="radio" name="if_trabaja_estudiante" id="trabaja_no" value="0">
            		<label class="form-check-label" for="trabaja_no">No</label>
        		</div>
    		</div>
          </div>

          <div class="form-group">
            <label for="usuario">Nombre de usuario *</label>
            <input type="text" class="form-control" name="usuario" id="usuario" readonly required>
            <small class="form-text text-muted">Se genera automáticamente a partir de la cédula</small>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="clave">Contraseña *</label>
              <input type="password" class="form-control" name="clave" id="clave" 
                     required minlength="8" maxlength="20"
                     pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$"
                     title="La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número. No se permiten caracteres especiales.">
              <div class="password-requirements" id="password-requirements">
                <div class="requirement unmet" id="req-length">
                  <i class="bi bi-circle"></i> Mínimo 8 caracteres
                </div>
                <div class="requirement unmet" id="req-uppercase">
                  <i class="bi bi-circle"></i> Al menos una mayúscula
                </div>
                <div class="requirement unmet" id="req-lowercase">
                  <i class="bi bi-circle"></i> Al menos una minúscula
                </div>
                <div class="requirement unmet" id="req-number">
                  <i class="bi bi-circle"></i> Al menos un número
                </div>
                <div class="requirement met" id="req-special">
                  <i class="bi bi-check-circle"></i> No se permiten caracteres especiales
                </div>
              </div>
              <div class="invalid-feedback" id="clave-error">
                La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número. No se permiten caracteres especiales.
              </div>
            </div>
            <div class="form-group col-md-6">
              <label for="clave2">Confirmar contraseña *</label>
              <input type="password" class="form-control" name="clave2" id="clave2" 
                     required minlength="8" maxlength="20">
              <div class="invalid-feedback" id="clave2-error">
                Las contraseñas no coinciden.
              </div>
            </div>
          </div>

          <div class="text-center">
            <button type="submit" class="btn btn-success px-5" id="btnSubmit">
              <span id="btnText">Registrar Estudiante</span>
              <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
            </button>
          </div>
          <div class="RespuestaAjax mt-3"></div>
        </form>
      </div>
    </div>
  </div>

                  
<script src="https://www.google.com/recaptcha/api.js?render=6Lcdw9MtAAAAAD3APqH6WcMSKsX4gv3qHkCl7IpI"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    const form = document.getElementById('formEstudiante');
    const ciTipo = document.getElementById('ci_tipo');
    const ciNumero = document.getElementById('ci_numero');
    const ciCompleto = document.getElementById('ci_completo');
    const usuarioInput = document.getElementById('usuario');
    const claveInput = document.getElementById('clave');

    // ---------- FUNCIONES AUXILIARES ----------
    function actualizarCedulaCompleta() {
        const tipo = ciTipo.value;
        const numero = ciNumero.value;
        if (tipo && numero.length === 8) {
            ciCompleto.value = tipo + numero;
            usuarioInput.value = tipo + numero;
        } else {
            ciCompleto.value = '';
            usuarioInput.value = '';
        }
    }

    function capitalizarPalabras(texto) {
        return texto.replace(/\w\S*/g, function(txt) {
            return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        });
    }

    function validarContraseña(contraseña) {
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;
        return regex.test(contraseña);
    }

    function actualizarRequisitoVisual(elementId, cumple) {
        const element = document.getElementById(elementId);
        if (!element) return;
        if (cumple) {
            element.classList.remove('unmet');
            element.classList.add('met');
            element.innerHTML = '<i class="bi bi-check-circle"></i> ' + element.textContent.replace(/^.*?\] /, '');
        } else {
            element.classList.remove('met');
            element.classList.add('unmet');
            element.innerHTML = '<i class="bi bi-circle"></i> ' + element.textContent.replace(/^.*?\] /, '');
        }
    }

    function actualizarRequisitosContraseña(contraseña) {
        const tieneLongitud = contraseña.length >= 8;
        const tieneMayuscula = /[A-Z]/.test(contraseña);
        const tieneMinuscula = /[a-z]/.test(contraseña);
        const tieneNumero = /\d/.test(contraseña);
        const tieneCaracteresEspeciales = /[^a-zA-Z\d]/.test(contraseña);

        actualizarRequisitoVisual('req-length', tieneLongitud);
        actualizarRequisitoVisual('req-uppercase', tieneMayuscula);
        actualizarRequisitoVisual('req-lowercase', tieneMinuscula);
        actualizarRequisitoVisual('req-number', tieneNumero);
        actualizarRequisitoVisual('req-special', !tieneCaracteresEspeciales);

        return tieneLongitud && tieneMayuscula && tieneMinuscula && tieneNumero && !tieneCaracteresEspeciales;
    }

    // ---------- EVENTOS DE INPUTS ----------
    ciNumero.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
        if (this.value.length > 8) this.value = this.value.substring(0, 8);
        actualizarCedulaCompleta();
    });

    ciTipo.addEventListener('change', actualizarCedulaCompleta);

    ciNumero.addEventListener('blur', function() {
        if (this.value.length !== 8) {
            this.classList.add('is-invalid');
            document.getElementById('ci-error').style.display = 'block';
        } else {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
            document.getElementById('ci-error').style.display = 'none';
        }
    });

    document.getElementById('nombre').addEventListener('input', function() {
        let valor = this.value.replace(/[^A-Za-záéíóúñÑ\s]/g, '');
        if (valor.length > 0) valor = capitalizarPalabras(valor);
        this.value = valor;
    });

    document.getElementById('apellido').addEventListener('input', function() {
        let valor = this.value.replace(/[^A-Za-záéíóúñÑ\s]/g, '');
        if (valor.length > 0) valor = capitalizarPalabras(valor);
        this.value = valor;
    });

    document.getElementById('lugar_nacimiento').addEventListener('input', function() {
        if (this.value.length > 0) this.value = capitalizarPalabras(this.value);
    });

    document.getElementById('telefono_numero').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').substring(0, 7);
    });

    document.getElementById('wsp_numero').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').substring(0, 7);
    });

    document.getElementById('wsp_codigo').addEventListener('change', function() {
        const numero = document.getElementById('wsp_numero').value;
        if (this.value && numero.length !== 7) {
            document.getElementById('whatsapp-error').style.display = 'block';
        } else {
            document.getElementById('whatsapp-error').style.display = 'none';
        }
    });

    document.getElementById('wsp_numero').addEventListener('blur', function() {
        const codigo = document.getElementById('wsp_codigo').value;
        if (codigo && this.value.length !== 7) {
            this.classList.add('is-invalid');
            document.getElementById('whatsapp-error').style.display = 'block';
        } else if (codigo && this.value.length === 7) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
            document.getElementById('whatsapp-error').style.display = 'none';
        } else {
            this.classList.remove('is-invalid', 'is-valid');
        }
    });

    document.getElementById('oficio_actual_estudiante').addEventListener('input', function() {
        if (this.value.length > 0) this.value = capitalizarPalabras(this.value);
    });

    claveInput.addEventListener('input', function() {
        actualizarRequisitosContraseña(this.value);
        if (this.value && validarContraseña(this.value)) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            if (this.value) this.classList.add('is-invalid');
        }
    });

    document.getElementById('clave2').addEventListener('input', function() {
        const clave = document.getElementById('clave').value;
        const clave2 = this.value;
        if (clave2 && clave !== clave2) {
            this.classList.add('is-invalid');
            document.getElementById('clave2-error').style.display = 'block';
        } else if (clave2 && clave === clave2) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
            document.getElementById('clave2-error').style.display = 'none';
        } else {
            this.classList.remove('is-valid', 'is-invalid');
        }
    });

    // ---------- SUBMIT ----------
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (ciNumero.value.length !== 8) {
            alert('La cédula debe contener exactamente 8 dígitos');
            ciNumero.focus();
            return;
        }

        const telefonoCodigo = document.getElementById('telefono_codigo').value;
        const telefonoNumero = document.getElementById('telefono_numero').value;
        const correoLocal = document.getElementById('correo_local').value;
        const correoDominio = document.getElementById('correo_dominio').value;
        const clave = document.getElementById('clave').value;
        const clave2 = document.getElementById('clave2').value;
        const wspCodigo = document.getElementById('wsp_codigo').value;
        const wspNumero = document.getElementById('wsp_numero').value;

        if (!telefonoCodigo || telefonoNumero.length !== 7) {
            alert('Por favor complete correctamente el teléfono (código + 7 dígitos)');
            return;
        }
        if (!correoLocal || !correoDominio) {
            alert('Por favor complete correctamente el correo electrónico');
            return;
        }
        if (wspCodigo && wspNumero.length !== 7) {
            alert('Si proporciona un código de WhatsApp, debe completar los 7 dígitos');
            return;
        }
        if (!validarContraseña(clave)) {
            alert('La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número. No se permiten caracteres especiales.');
            return;
        }
        if (clave !== clave2) {
            alert('Las contraseñas no coinciden');
            return;
        }

        // Armar formData
        const formData = new FormData();
        formData.append('ci', ciCompleto.value);
        formData.append('nombre', document.getElementById('nombre').value);
        formData.append('apellido', document.getElementById('apellido').value);
        formData.append('fecha_nacimiento', document.getElementById('fecha_nacimiento').value);
        formData.append('lugar_nacimiento', document.getElementById('lugar_nacimiento').value);
        formData.append('telefono_estudiante', telefonoCodigo + telefonoNumero);
        formData.append('correo_estudiante', correoLocal + correoDominio);
        formData.append('usuario', usuarioInput.value);
        formData.append('clave', clave);
        formData.append('clave2', clave2);
        formData.append('wsp_estudiante', (wspCodigo && wspNumero) ? wspCodigo + wspNumero : '');
        formData.append('direccion_estudiante', document.getElementById('direccion_estudiante').value);
        formData.append('nivel_estudio_estudiante', document.getElementById('nivel_estudio_estudiante').value);
        formData.append('oficio_actual_estudiante', document.getElementById('oficio_actual_estudiante').value);

        let trabajaValor = '0';
        if (document.getElementById('trabaja_si').checked) trabajaValor = '1';
        formData.append('if_trabaja_estudiante', trabajaValor);
        formData.append('estado_estudiante', 'activo');

        // UI
        const btnSubmit = document.getElementById('btnSubmit');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');
        btnSubmit.disabled = true;
        btnText.textContent = 'Registrando...';
        btnSpinner.classList.remove('d-none');

        // Verificar grecaptcha antes de usarlo
        if (typeof grecaptcha === 'undefined') {
            document.querySelector('.RespuestaAjax').innerHTML =
                '<div class="alert alert-danger">Error: reCAPTCHA no se cargó. Recarga la página (Ctrl+Shift+R).</div>';
            btnSubmit.disabled = false;
            btnText.textContent = 'Registrar Estudiante';
            btnSpinner.classList.add('d-none');
            return;
        }

        // Ejecutar reCAPTCHA v3
        grecaptcha.ready(function() {
            grecaptcha.execute('6Lcdw9MtAAAAAD3APqH6WcMSKsX4gv3qHkCl7IpI', {action: 'registro_estudiante'})
                .then(function(token) {
                    formData.append('recaptcha_token', token);

                    fetch('../controladores/registroEstudianteControlador.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(response) { return response.text(); })
                    .then(function(data) {
                        document.querySelector('.RespuestaAjax').innerHTML = data;
                        if (data.includes('alert-success')) {
                            form.reset();
                            ciCompleto.value = '';
                            document.querySelectorAll('.requirement').forEach(function(req) {
                                req.classList.remove('met');
                                req.classList.add('unmet');
                                req.innerHTML = req.innerHTML.replace('bi-check-circle', 'bi-circle');
                            });
                        }
                    })
                    .catch(function(error) {
                        console.error('Error en fetch:', error);
                        document.querySelector('.RespuestaAjax').innerHTML =
                            '<div class="alert alert-danger">Error al enviar el formulario.</div>';
                    })
                    .finally(function() {
                        btnSubmit.disabled = false;
                        btnText.textContent = 'Registrar Estudiante';
                        btnSpinner.classList.add('d-none');
                    });
                })
                .catch(function(error) {
                    console.error('Error en reCAPTCHA:', error);
                    document.querySelector('.RespuestaAjax').innerHTML =
                        '<div class="alert alert-danger">Error de verificación de seguridad. Recarga e intenta de nuevo.</div>';
                    btnSubmit.disabled = false;
                    btnText.textContent = 'Registrar Estudiante';
                    btnSpinner.classList.add('d-none');
                });
        });
    });
});
</script>
</body>
</html>