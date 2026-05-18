document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('formEstudiante');
  const btnSubmit = document.getElementById('btnSubmit');
  const btnText = document.getElementById('btnText');
  const btnSpinner = document.getElementById('btnSpinner');

  // Elementos principales
  const ciInput = document.getElementById('ci');
  const nombreInput = document.getElementById('nombre');
  const apellidoInput = document.getElementById('apellido');
  const usuarioInput = document.getElementById('usuario');
  const telefonoCodigo = document.getElementById('telefono_codigo');
  const telefonoNumero = document.getElementById('telefono_numero');
  const correoLocal = document.getElementById('correo_local');
  const correoDominio = document.getElementById('correo_dominio');
  const clave1 = document.getElementById('clave1');
  const clave2 = document.getElementById('clave2');

    
  // Capitalizar primera letra (Nombre y Apellido)
  function capitalizarPrimeraLetra(texto) {
    return texto.charAt(0).toUpperCase() + texto.slice(1).toLowerCase();
  }

  // Validar solo letras (sin números, espacios ni caracteres especiales)
  function validarSoloLetras(texto) {
    return /^[A-Za-záéíóúñÑ]+$/.test(texto);
  }

  // Eventos para nombre y apellido
  nombreInput.addEventListener('input', function() {
    let valor = this.value;
    
    // Remover espacios, números y caracteres especiales
    valor = valor.replace(/[^A-Za-záéíóúñÑ]/g, '');
    
    // Capitalizar primera letra
    if (valor.length > 0) {
      valor = capitalizarPrimeraLetra(valor);
    }
    
    this.value = valor;
    
    // Validación visual
    if (valor.length >= 2 && validarSoloLetras(valor)) {
      this.classList.remove('is-invalid');
      this.classList.add('is-valid');
    } else {
      this.classList.remove('is-valid');
      if (valor.length > 0) {
        this.classList.add('is-invalid');
      }
    }
  });

  apellidoInput.addEventListener('input', function() {
    let valor = this.value;
    
    // Remover espacios, números y caracteres especiales
    valor = valor.replace(/[^A-Za-záéíóúñÑ]/g, '');
    
    // Capitalizar primera letra
    if (valor.length > 0) {
      valor = capitalizarPrimeraLetra(valor);
    }
    
    this.value = valor;
    
    // Validación visual
    if (valor.length >= 2 && validarSoloLetras(valor)) {
      this.classList.remove('is-invalid');
      this.classList.add('is-valid');
    } else {
      this.classList.remove('is-valid');
      if (valor.length > 0) {
        this.classList.add('is-invalid');
      }
    }
  });

  // Cédula -> Usuario automático
  ciInput.addEventListener('input', function() {
    let valor = this.value;
    
    // Solo números
    valor = valor.replace(/\D/g, '');
    
    // Limitar a 8 caracteres
    if (valor.length > 8) {
      valor = valor.substring(0, 8);
    }
    
    this.value = valor;
    
    // Actualizar usuario automáticamente
    usuarioInput.value = valor;
    
    // Validación visual
    if (valor.length >= 7 && valor.length <= 8) {
      this.classList.remove('is-invalid');
      this.classList.add('is-valid');
    } else {
      this.classList.remove('is-valid');
      if (valor.length > 0) {
        this.classList.add('is-invalid');
      }
    }
  });

  // Validar teléfono
  telefonoNumero.addEventListener('input', function() {
    let valor = this.value;
    
    // Solo números
    valor = valor.replace(/\D/g, '');
    
    // Limitar a 7 caracteres
    if (valor.length > 7) {
      valor = valor.substring(0, 7);
    }
    
    this.value = valor;
    
    // Validación visual
    if (valor.length === 7 && telefonoCodigo.value) {
      this.classList.remove('is-invalid');
      this.classList.add('is-valid');
      telefonoCodigo.classList.remove('is-invalid');
      telefonoCodigo.classList.add('is-valid');
    } else {
      this.classList.remove('is-valid');
      if (valor.length > 0) {
        this.classList.add('is-invalid');
      }
    }
  });

  telefonoCodigo.addEventListener('change', function() {
    if (this.value && telefonoNumero.value.length === 7) {
      this.classList.remove('is-invalid');
      this.classList.add('is-valid');
      telefonoNumero.classList.remove('is-invalid');
      telefonoNumero.classList.add('is-valid');
    } else {
      this.classList.remove('is-valid');
      if (this.value) {
        this.classList.add('is-invalid');
      }
    }
  });

  // Validar correo
  correoLocal.addEventListener('input', function() {
    const valor = this.value;
    
    if (valor && correoDominio.value) {
      this.classList.remove('is-invalid');
      this.classList.add('is-valid');
      correoDominio.classList.remove('is-invalid');
      correoDominio.classList.add('is-valid');
    } else {
      this.classList.remove('is-valid');
      if (valor) {
        this.classList.add('is-invalid');
      }
    }
  });

  correoDominio.addEventListener('change', function() {
    if (this.value && correoLocal.value) {
      this.classList.remove('is-invalid');
      this.classList.add('is-valid');
      correoLocal.classList.remove('is-invalid');
      correoLocal.classList.add('is-valid');
    } else {
      this.classList.remove('is-valid');
      if (this.value) {
        this.classList.add('is-invalid');
      }
    }
  });

  // Validar contraseña profesional
  function validarContraseñaFuerte(contraseña) {
    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    return regex.test(contraseña);
  }

  clave1.addEventListener('input', function() {
    const valor = this.value;
    
    if (validarContraseñaFuerte(valor)) {
      this.classList.remove('is-invalid');
      this.classList.add('is-valid');
      document.getElementById('clave1-error').style.display = 'none';
    } else {
      this.classList.remove('is-valid');
      if (valor.length > 0) {
        this.classList.add('is-invalid');
        document.getElementById('clave1-error').style.display = 'block';
      }
    }
    
    // Validar confirmación también
    if (clave2.value) {
      validarContraseñas();
    }
  });

  // Validar que las contraseñas coincidan
  function validarContraseñas() {
    const clave1Value = clave1.value;
    const clave2Value = clave2.value;
    const clave2Error = document.getElementById('clave2-error');

    if (clave2Value && clave1Value !== clave2Value) {
      clave2.classList.add('is-invalid');
      clave2Error.style.display = 'block';
    } else {
      clave2.classList.remove('is-invalid');
      clave2Error.style.display = 'none';
      if (clave2Value && clave1Value === clave2Value) {
        clave2.classList.add('is-valid');
      }
    }
  }

  clave2.addEventListener('input', validarContraseñas);

  // Validar todo el formulario antes de enviar
  form.addEventListener('submit', function (e) {
    e.preventDefault();

    if (!validarFormularioCompleto()) {
      return;
    }

    // Deshabilitar botón para evitar múltiples envíos
    btnSubmit.disabled = true;
    btnText.textContent = 'Registrando...';
    btnSpinner.classList.remove('d-none');

    // Preparar datos para enviar
    const formData = new FormData();
    
    // Agregar datos básicos
    formData.append('ci', ciInput.value);
    formData.append('nombre', nombreInput.value);
    formData.append('apellido', apellidoInput.value);
    formData.append('fecha_nacimiento', document.getElementById('fecha_nacimiento').value);
    formData.append('usuario', usuarioInput.value);
    formData.append('clave1', clave1.value);
    formData.append('clave2', clave2.value);
    
    // Concatenar teléfono
    formData.append('telefono', telefonoCodigo.value + telefonoNumero.value);
    
    // Concatenar correo
    formData.append('correo', correoLocal.value + correoDominio.value);
      
        // Justo antes del fetch, agrega:
		console.log('Datos a enviar:');
		console.log('Cédula:', ciInput.value);
		console.log('Teléfono completo:', telefonoCodigo.value + telefonoNumero.value);
		console.log('Correo completo:', correoLocal.value + correoDominio.value);

    fetch('controladores/registroEstudianteControlador.php', {
      method: 'POST',
      body: formData
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Error en la respuesta del servidor');
      }
      return response.text();
    })
    .then(data => {
      document.querySelector('.RespuestaAjax').innerHTML = data;
      
      // Si fue exitoso, resetear formulario
      if (data.includes('alert-success')) {
        form.reset();
        resetearValidaciones();
      }
    })
    .catch((error) => {
      console.error('Error:', error);
      mostrarMensaje('Error al enviar el formulario. Por favor intente nuevamente.', 'danger');
    })
    .finally(() => {
      // Rehabilitar botón
      btnSubmit.disabled = false;
      btnText.textContent = 'Registrar Estudiante';
      btnSpinner.classList.add('d-none');
    });
  });

function validarFormularioCompleto() {
  let valido = true;

  // Validar cédula
  if (ciInput.value.length < 7 || ciInput.value.length > 8) {
    ciInput.classList.add('is-invalid');
    valido = false;
  }

  // Validar nombre
  if (!validarSoloLetras(nombreInput.value) || nombreInput.value.length < 2) {
    nombreInput.classList.add('is-invalid');
    valido = false;
  }

  // Validar apellido
  if (!validarSoloLetras(apellidoInput.value) || apellidoInput.value.length < 2) {
    apellidoInput.classList.add('is-invalid');
    valido = false;
  }

  // Validar fecha
  const fecha = new Date(document.getElementById('fecha_nacimiento').value);
  const hoy = new Date();
  if (fecha >= hoy) {
    document.getElementById('fecha_nacimiento').classList.add('is-invalid');
    valido = false;
  }

  // Validar teléfono - VERIFICAR QUE AMBOS CAMPOS ESTÉN COMPLETOS
  if (!telefonoCodigo.value || telefonoNumero.value.length !== 7) {
    telefonoCodigo.classList.add('is-invalid');
    telefonoNumero.classList.add('is-invalid');
    valido = false;
  } else {
    // Verificar que el teléfono completo tenga 11 dígitos
    const telefonoCompleto = telefonoCodigo.value + telefonoNumero.value;
    if (telefonoCompleto.length !== 11) {
      telefonoCodigo.classList.add('is-invalid');
      telefonoNumero.classList.add('is-invalid');
      valido = false;
    }
  }

  // Validar correo
  if (!correoLocal.value || !correoDominio.value) {
    correoLocal.classList.add('is-invalid');
    correoDominio.classList.add('is-invalid');
    valido = false;
  }

  // Validar contraseña
  if (!validarContraseñaFuerte(clave1.value)) {
    clave1.classList.add('is-invalid');
    valido = false;
  }

  if (clave1.value !== clave2.value) {
    clave2.classList.add('is-invalid');
    valido = false;
  }

  if (!valido) {
    mostrarMensaje('Por favor complete todos los campos correctamente.', 'danger');
  }

  return valido;
}

  function resetearValidaciones() {
    const inputs = form.querySelectorAll('input, select');
    inputs.forEach(input => {
      input.classList.remove('is-valid', 'is-invalid');
    });
  } 

  function mostrarMensaje(mensaje, tipo) {
    document.querySelector('.RespuestaAjax').innerHTML = `
      <div class="alert alert-${tipo} alert-dismissible fade show text-center mt-3">
        ${mensaje}
        <button type="button" class="close" data-dismiss="alert">
          <span>&times;</span>
        </button>
      </div>
    `;
  }
});