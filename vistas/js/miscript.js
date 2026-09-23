// ==============================================
// UTILIDADES GENERALES (SOLO DOM Y ERRORES)
// ==============================================
const Utils = {
    // Obtener valor de input por ID
    getVal: (id) => document.getElementById(id)?.value || '',

    // Obtener elemento por ID
    getEl: (id) => document.getElementById(id),

    // Agregar/remover clase de error en input
    marcarInputError: (id, esError) => {
        const input = document.getElementById(id);
        if (!input) return;
        if (esError) {
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    },

    // Limpiar todos los mensajes de error de inputs
    limpiarErroresInputs: () => {
        document.querySelectorAll('.is-invalid').forEach(input => {
            input.classList.remove('is-invalid');
        });
    }
};

// ==============================================
// SISTEMA CENTRAL DE VALIDACIÓN (LIMPIO)
// ==============================================
const Validador = (function() {
    // Lista de identificadores que tienen un error activo
    let erroresActivos = new Set();
    
    // Referencia al botón de submit
    let botonSubmit = null;
    
    // Inicializar el sistema pasándole el ID del botón
    function inicializar(idBoton) {
        botonSubmit = document.getElementById(idBoton);
        if (!botonSubmit) {
            console.error(`Validador: No se encontró el botón con id "${idBoton}"`);
        } else {
            console.log(`✅ Validador inicializado con botón: ${idBoton}`);
        }
        return this;
    }
    
    // Actualizar visualmente el estado del botón de submit
    function actualizarBoton() {
        if (!botonSubmit) return;
        
        const hayErrores = erroresActivos.size > 0;
        botonSubmit.disabled = hayErrores;
        
        if (hayErrores) {
            botonSubmit.classList.add("btn-disabled");
            botonSubmit.style.opacity = "0.5";
            botonSubmit.style.cursor = "not-allowed";
        } else {
            botonSubmit.classList.remove("btn-disabled");
            botonSubmit.style.opacity = "1";
            botonSubmit.style.cursor = "pointer";
        }
    }
    
    // Registrar o quitar un error de un campo/alerta
    function registrarError(idElemento, hayError) {
        if (hayError) {
            erroresActivos.add(idElemento);
        } else {
            erroresActivos.delete(idElemento);
        }
        
        actualizarBoton();
        return this;
    }

    // ==========================================
    // API Pública
    // ==========================================
    return {
        init: inicializar,
        set: registrarError,
        hayErrores: function() { return erroresActivos.size > 0; },
        limpiar: function() {
            erroresActivos.clear();
            actualizarBoton();
            return this;
        }
    };
})();