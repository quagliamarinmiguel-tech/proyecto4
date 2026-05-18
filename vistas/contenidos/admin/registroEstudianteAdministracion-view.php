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
    			<label for="ci">Cédula *</label>
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
    			<!-- Campo oculto para almacenar el valor completo -->
    			<input type="hidden" name="ci" id="ci_completo">
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
              <label for="telefono">Teléfono *</label>
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
            <div class="form-group col-md-6">
              <label for="correo">Correo electrónico *</label>
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

          <div class="form-group">
            <label for="usuario">Nombre de usuario *</label>
            <input type="text" class="form-control" name="usuario" id="usuario" readonly required>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="clave1">Contraseña *</label>
              <input type="password" class="form-control" name="clave1" id="clave1" 
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
              <div class="invalid-feedback" id="clave1-error">
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

 <script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado - Script funcionando');
    
    const form = document.getElementById('formEstudiante');
    const ciTipo = document.getElementById('ci_tipo');
    const ciNumero = document.getElementById('ci_numero');
    const ciCompleto = document.getElementById('ci_completo');
    const usuarioInput = document.getElementById('usuario');
    const clave1Input = document.getElementById('clave1');
    
    // Función para actualizar el valor completo de la cédula Y el usuario
    function actualizarCedulaCompleta() {
        const tipo = ciTipo.value;
        const numero = ciNumero.value;
        
        if (tipo && numero.length === 8) {
            ciCompleto.value = tipo + numero;
            // ACTUALIZACIÓN: El usuario ahora también incluye el tipo
            usuarioInput.value = tipo + numero;
        } else {
            ciCompleto.value = '';
            usuarioInput.value = '';
        }
    }
    
    // Capitalizar primera letra de cada palabra
    function capitalizarPalabras(texto) {
        return texto.replace(/\w\S*/g, function(txt) {
            return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        });
    }
    
    // Validar solo letras y espacios
    function validarSoloLetrasYEspacios(texto) {
        return /^[A-Za-záéíóúñÑ\s]+$/.test(texto);
    }
    
    // Validar contraseña (sin caracteres especiales)
    function validarContraseña(contraseña) {
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;
        return regex.test(contraseña);
    }
    
    // Actualizar indicadores de requisitos de contraseña
    function actualizarRequisitosContraseña(contraseña) {
        const tieneLongitud = contraseña.length >= 8;
        const tieneMayuscula = /[A-Z]/.test(contraseña);
        const tieneMinuscula = /[a-z]/.test(contraseña);
        const tieneNumero = /\d/.test(contraseña);
        const tieneCaracteresEspeciales = /[^a-zA-Z\d]/.test(contraseña);
        
        // Actualizar visualmente cada requisito
        actualizarRequisitoVisual('req-length', tieneLongitud);
        actualizarRequisitoVisual('req-uppercase', tieneMayuscula);
        actualizarRequisitoVisual('req-lowercase', tieneMinuscula);
        actualizarRequisitoVisual('req-number', tieneNumero);
        actualizarRequisitoVisual('req-special', !tieneCaracteresEspeciales);
        
        return tieneLongitud && tieneMayuscula && tieneMinuscula && tieneNumero && !tieneCaracteresEspeciales;
    }
    
    function actualizarRequisitoVisual(elementId, cumple) {
        const element = document.getElementById(elementId);
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
    
    // Solo permitir números en el campo de cédula
    ciNumero.addEventListener('input', function() {
        // Remover cualquier caracter que no sea número
        this.value = this.value.replace(/\D/g, '');
        
        // Limitar a 8 caracteres
        if (this.value.length > 8) {
            this.value = this.value.substring(0, 8);
        }
        
        // ACTUALIZACIÓN: Ya no actualizamos el usuario aquí, lo hacemos en actualizarCedulaCompleta
        actualizarCedulaCompleta();
    });
    
    // Actualizar cédula completa y usuario cuando cambie el tipo
    ciTipo.addEventListener('change', actualizarCedulaCompleta);
    
    // Validación específica para la cédula
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
    
    // Capitalizar nombre y apellido (cada palabra)
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
    
    // Solo números en teléfono
    document.getElementById('telefono_numero').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').substring(0, 7);
    });
    
    // Validar contraseña en tiempo real
    clave1Input.addEventListener('input', function() {
        actualizarRequisitosContraseña(this.value);
        
        // Validar visualmente
        if (this.value && validarContraseña(this.value)) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            if (this.value) {
                this.classList.add('is-invalid');
            }
        }
    });
    
    // Validar que las contraseñas coincidan
    document.getElementById('clave2').addEventListener('input', function() {
        const clave1 = document.getElementById('clave1').value;
        const clave2 = this.value;
        
        if (clave2 && clave1 !== clave2) {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
            document.getElementById('clave2-error').style.display = 'block';
        } else if (clave2 && clave1 === clave2) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
            document.getElementById('clave2-error').style.display = 'none';
        } else {
            this.classList.remove('is-valid', 'is-invalid');
        }
    });
    
    // Manejar envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Formulario enviado - Iniciando validación');
        
        // Validar cédula primero
        if (ciNumero.value.length !== 8) {
            alert('La cédula debe contener exactamente 8 dígitos');
            ciNumero.focus();
            return;
        }
        
        // Validaciones básicas
        const telefonoCodigo = document.getElementById('telefono_codigo').value;
        const telefonoNumero = document.getElementById('telefono_numero').value;
        const correoLocal = document.getElementById('correo_local').value;
        const correoDominio = document.getElementById('correo_dominio').value;
        const clave1 = document.getElementById('clave1').value;
        const clave2 = document.getElementById('clave2').value;
        
        // Validar teléfono
        if (!telefonoCodigo || telefonoNumero.length !== 7) {
            alert('Por favor complete correctamente el teléfono (código + 7 dígitos)');
            return;
        }
        
        // Validar correo
        if (!correoLocal || !correoDominio) {
            alert('Por favor complete correctamente el correo electrónico');
            return;
        }
        
        // Validar contraseña
        if (!validarContraseña(clave1)) {
            alert('La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número. No se permiten caracteres especiales.');
            return;
        }
        
        if (clave1 !== clave2) {
            alert('Las contraseñas no coinciden');
            return;
        }
        
        // Preparar datos para enviar
        const formData = new FormData();
        
        // Usamos ci_completo que contiene el formato "V-12345678"
        formData.append('ci', ciCompleto.value);
        formData.append('nombre', document.getElementById('nombre').value);
        formData.append('apellido', document.getElementById('apellido').value);
        formData.append('fecha_nacimiento', document.getElementById('fecha_nacimiento').value);
        formData.append('telefono', telefonoCodigo + telefonoNumero);
        formData.append('correo', correoLocal + correoDominio);
        formData.append('usuario', usuarioInput.value); // Ahora será "V-12345678" o "E-12345678"
        formData.append('clave1', clave1);
        formData.append('clave2', clave2);
        
        // Mostrar datos en consola para debug
        console.log('Datos a enviar:');
        console.log('Cédula completa:', ciCompleto.value);
        console.log('Usuario:', usuarioInput.value);
        for (let [key, value] of formData.entries()) {
            console.log(key + ': ' + value);
        }
        
        // Enviar formulario
        const btnSubmit = document.getElementById('btnSubmit');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');
        
        btnSubmit.disabled = true;
        btnText.textContent = 'Registrando...';
        btnSpinner.classList.remove('d-none');
        
        fetch('controladores/registroEstudianteControlador.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            document.querySelector('.RespuestaAjax').innerHTML = data;
            if (data.includes('alert-success')) {
                form.reset();
                // Limpiar el campo oculto también
                ciCompleto.value = '';
                // Resetear indicadores de contraseña
                document.querySelectorAll('.requirement').forEach(req => {
                    req.classList.remove('met');
                    req.classList.add('unmet');
                    req.innerHTML = req.innerHTML.replace('bi-check-circle', 'bi-circle');
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.querySelector('.RespuestaAjax').innerHTML = 
                '<div class="alert alert-danger">Error al enviar el formulario</div>';
        })
        .finally(() => {
            btnSubmit.disabled = false;
            btnText.textContent = 'Registrar Estudiante';
            btnSpinner.classList.add('d-none');
        });
    });
});
</script>
</body>
</html>