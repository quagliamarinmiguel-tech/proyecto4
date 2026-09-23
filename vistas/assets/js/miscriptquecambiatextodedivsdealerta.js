//aqui guardo funciones para administrar las validaciones y manejar modales

// ==============================================
// SISTEMA CENTRAL DE VALIDACIÓN - miscript.js
// ==============================================
const Validador = (function() {
    // Lista central de errores activos
    let erroresActivos = new Set();
    
    // Referencia al botón de submit (se configura al iniciar)
    let botonSubmit = null;
    
    // Colores para los estados
    const COLORES = {
        error: "#dc3545",  // Rojo
        exito: "#28a745"   // Verde
    };
    
    // ==========================================
    // INICIALIZAR EL SISTEMA
    // ==========================================
    function inicializar(idBoton) {
        botonSubmit = document.getElementById(idBoton);
        if (!botonSubmit) {
            console.error(`Validador: No se encontró el botón con id "${idBoton}"`);
        } else {
            console.log(`✅ Validador inicializado con botón: ${idBoton}`);
        }
        return this;
    }
    
    // ==========================================
    // ACTUALIZAR ESTADO DE UN DIV
    // ==========================================
    function actualizarDiv(idDiv, hayError, mensaje = null) {
        const div = document.getElementById(idDiv);
        if (!div) {
            console.warn(`Validador: No se encontró el div con id "${idDiv}"`);
            return false;
        }
        
        // Cambiar color según estado
        div.style.color = hayError ? COLORES.error : COLORES.exito;
        
        // Actualizar ícono si existe
        const icono = div.querySelector('i');
        if (icono) {
            icono.className = hayError 
                ? "fas fa-exclamation-circle" 
                : "fas fa-check-circle";
        }
        
        // Actualizar mensaje si se proporciona
        if (mensaje !== null) {
            if (hayError && mensaje) {
                div.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${mensaje}`;
            } else if (!hayError && mensaje) {
                div.innerHTML = `<i class="fas fa-check-circle"></i> ${mensaje}`;
            }
        }
        
        return true;
    }
    
    // ==========================================
    // REGISTRAR ESTADO DE UN ERROR
    // ==========================================
    function registrarError(idDiv, hayError, mensaje = null) {
        // Actualizar el div visualmente
        actualizarDiv(idDiv, hayError, mensaje);
        
        // Actualizar la lista de errores
        if (hayError) {
            erroresActivos.add(idDiv);
            console.log(`❌ Error agregado: ${idDiv}`);
        } else {
            erroresActivos.delete(idDiv);
            console.log(`✅ Error removido: ${idDiv}`);
        }
        
        // Actualizar estado del botón
        actualizarBoton();
        
        return this;
    }
    
    // ==========================================
    // ACTUALIZAR ESTADO DEL BOTÓN
    // ==========================================
    function actualizarBoton() {
        if (!botonSubmit) return;
        
        const hayErrores = erroresActivos.size > 0;
        
        // Deshabilitar/habilitar botón
        botonSubmit.disabled = hayErrores;
        
        // Estilos visuales
        if (hayErrores) {
            botonSubmit.classList.add("btn-disabled");
            botonSubmit.style.opacity = "0.5";
            botonSubmit.style.cursor = "not-allowed";
        } else {
            botonSubmit.classList.remove("btn-disabled");
            botonSubmit.style.opacity = "1";
            botonSubmit.style.cursor = "pointer";
        }
        
        console.log(`📊 Errores activos (${erroresActivos.size}):`, Array.from(erroresActivos));
    }
    
    // ==========================================
    // VERIFICAR SI HAY ERRORES
    // ==========================================
    function hayErrores() {
        return erroresActivos.size > 0;
    }
    
    // ==========================================
    // OBTENER LISTA DE ERRORES
    // ==========================================
    function obtenerErrores() {
        return Array.from(erroresActivos);
    }
    
    // ==========================================
    // LIMPIAR TODOS LOS ERRORES
    // ==========================================
    function limpiarTodo() {
        erroresActivos.clear();
        actualizarBoton();
        return this;
    }
    
    // ==========================================
    // VALIDAR FECHAS (UTILIDAD)
    // ==========================================
    function fechaAObjeto(fechaStr) {
        return fechaStr ? new Date(fechaStr) : null;
    }
    
    function fechaEnRango(fecha, inicio, fin) {
        if (!fecha || !inicio || !fin) return true;
        return fecha >= inicio && fecha <= fin;
    }
    
    // API Pública
    return {
        init: inicializar,
        set: registrarError,
        hayErrores: hayErrores,
        obtenerErrores: obtenerErrores,
        limpiar: limpiarTodo,
        fechaAObjeto: fechaAObjeto,
        fechaEnRango: fechaEnRango
    };
})();

// ==============================================
// UTILIDADES GENERALES
// ==============================================
const Utils = {
    // Obtener valor de input por ID
    getVal: (id) => document.getElementById(id)?.value || '',
    
    // Obtener elemento por ID
    getEl: (id) => document.getElementById(id),
    
    // Formatear fecha para mostrar
    formatearFecha: (fechaStr) => {
        if (!fechaStr) return '';
        const fecha = new Date(fechaStr);
        return fecha.toLocaleDateString('es-ES');
    },
    
    // Validar que un campo no esté vacío
    noVacio: (valor) => valor && valor.trim() !== '',
    
    // Agregar/remover clase de error en input
    marcarInputError: (id, esError) => {
        const input = document.getElementById(id);
        if (!input) return;
        if (esError) {
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    }
};
