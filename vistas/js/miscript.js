
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
    },
    
    // Limpiar todos los mensajes de error de inputs
    limpiarErroresInputs: () => {
        document.querySelectorAll('.is-invalid').forEach(input => {
            input.classList.remove('is-invalid');
        });
    }
};

// ==============================================
// SISTEMA CENTRAL DE VALIDACIÓN
// ==============================================
const Validador = (function() {
    // Lista central de errores activos
    let erroresActivos = new Set();
    
    // Referencia al botón de submit (se configura al iniciar)
    let botonSubmit = null;
    
    // Almacenar textos originales de los divs
    let textosOriginales = new Map();
    
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
    // GUARDAR TEXTO ORIGINAL DE UN DIV
    // ==========================================
    function guardarTextoOriginal(idDiv) {
        const div = document.getElementById(idDiv);
        if (!div) return false;
        
        if (!textosOriginales.has(idDiv)) {
            const contenidoOriginal = div.innerHTML;
            textosOriginales.set(idDiv, contenidoOriginal);
        }
        return true;
    }
    
    // ==========================================
    // ACTUALIZAR ESTADO DE UN DIV (SOLO COLOR E ÍCONO)
    // ==========================================
    function actualizarDiv(idDiv, hayError, mensajeTooltip = null) {
        const div = document.getElementById(idDiv);
        if (!div) return false;
        
        guardarTextoOriginal(idDiv);
        
        div.style.color = hayError ? COLORES.error : COLORES.exito;
        
        let icono = div.querySelector('i');
        
        if (icono) {
            icono.className = hayError 
                ? "fas fa-exclamation-circle" 
                : "fas fa-check-circle";
        } else {
            icono = document.createElement('i');
            icono.className = hayError 
                ? "fas fa-exclamation-circle" 
                : "fas fa-check-circle";
            icono.style.marginRight = "8px";
            div.insertBefore(icono, div.firstChild);
        }
        
        if (mensajeTooltip) {
            div.title = mensajeTooltip;
            div.setAttribute('data-error-message', mensajeTooltip);
        } else if (!hayError) {
            div.title = '';
            div.removeAttribute('data-error-message');
        }
        
        return true;
    }
    
    // ==========================================
    // REGISTRAR ESTADO DE UN ERROR
    // ==========================================
    function registrarError(idDiv, hayError, mensajeTooltip = null) {
        actualizarDiv(idDiv, hayError, mensajeTooltip);
        
        if (hayError) {
            erroresActivos.add(idDiv);
        } else {
            erroresActivos.delete(idDiv);
        }
        
        actualizarBoton();
        return this;
    }
    
    // ==========================================
    // RESTAURAR TEXTO ORIGINAL
    // ==========================================
    function restaurarTextoOriginal(idDiv) {
        const div = document.getElementById(idDiv);
        if (!div) return false;
        
        const textoOriginal = textosOriginales.get(idDiv);
        if (textoOriginal) {
            div.innerHTML = textoOriginal;
        }
        return true;
    }
    
    // ==========================================
    // RESTAURAR TODOS LOS TEXTOS ORIGINALES
    // ==========================================
    function restaurarTodosLosTextos() {
        textosOriginales.forEach((_, idDiv) => {
            restaurarTextoOriginal(idDiv);
        });
        return this;
    }
    
    // ==========================================
    // ACTUALIZAR ESTADO DEL BOTÓN
    // ==========================================
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

    // ==========================================
    // API Pública
    // ==========================================
    return {
        init: inicializar,
        set: registrarError,
        hayErrores: function() { return erroresActivos.size > 0; },
        obtenerErrores: function() { return Array.from(erroresActivos); },
        limpiar: function() {
            erroresActivos.clear();
            actualizarBoton();
            return this;
        },
        restaurarTexto: restaurarTextoOriginal,
        restaurarTodos: restaurarTodosLosTextos,

        /**
         * NUEVO: Valida que la fecha inicio no sea posterior a la fin.
         */
        validarRangoFechas: function(idInicio, idFin, idAlerta, mensaje = "La fecha de inicio no puede ser posterior a la fecha de fin") {
            const valInicio = Utils.getVal(idInicio);
            const valFin = Utils.getVal(idFin);

            // Si falta alguna, limpiamos el error de rango (pero no validamos todavía)
            if (!valInicio || !valFin) {
                this.set(idAlerta, false);
                return;
            }

            const hayError = (new Date(valInicio) > new Date(valFin));
            
            // Reportamos al sistema de errores
            this.set(idAlerta, hayError, hayError ? mensaje : null);
            
            // Efecto visual en los inputs
            Utils.marcarInputError(idInicio, hayError);
            Utils.marcarInputError(idFin, hayError);
        },

        /**
         * NUEVO: Valida que un array de IDs de inputs no tengan fechas repetidas.
         */
        validarFechasUnicas: function(idsInputs, idAlerta, mensaje = "Las fechas no pueden repetirse") {
            const valores = idsInputs
                .map(id => Utils.getVal(id))
                .filter(valor => valor && valor.trim() !== "");

            // Si el tamaño del Set es menor al array original, hay duplicados
            const hayError = (new Set(valores)).size !== valores.length;
            
            this.set(idAlerta, hayError, hayError ? mensaje : null);
        },

        fechaAObjeto: (fechaStr) => fechaStr ? new Date(fechaStr) : null,
        fechaEnRango: (fecha, inicio, fin) => {
            if (!fecha || !inicio || !fin) return true;
            return fecha >= inicio && fecha <= fin;
        }
    };
})();

const ModalHelper = {
    modalId: 'modalImagenContextual',
    buttonSelector: '.ver-ayuda',

    createModal() {
        if (document.getElementById(this.modalId)) {
            return;
        }

        const modal = document.createElement('div');
        modal.id = this.modalId;
        Object.assign(modal.style, {
            display: 'none',
            position: 'fixed',
            zIndex: '9999',
            top: '0',
            left: '0',
            width: '100%',
            height: '100%',
            background: 'rgba(0,0,0,0.75)',
            justifyContent: 'center',
            alignItems: 'center',
            overflowY: 'auto',
            padding: '30px',
            boxSizing: 'border-box'
        });

        const inner = document.createElement('div');
        Object.assign(inner.style, {
            position: 'relative',
            maxWidth: '900px',
            width: '100%',
            background: '#000',
            border: '3px solid #fff',
            padding: '20px',
            borderRadius: '8px',
            boxSizing: 'border-box',
            textAlign: 'center'
        });

        const img = document.createElement('img');
        img.id = 'imgAyudaContextual';
        img.alt = 'Imagen de ayuda';
        Object.assign(img.style, {
            maxWidth: '100%',
            height: 'auto',
            display: 'block',
            margin: '0 auto',
            border: '3px solid white',
            borderRadius: '4px'
        });

        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'btn btn-sm btn-light';
        closeButton.innerHTML = '<i class="fas fa-times"></i>';
        Object.assign(closeButton.style, {
            position: 'absolute',
            top: '10px',
            right: '10px',
            backgroundColor: 'red',
            color: '#fff',
            border: 'none',
            padding: '5px 8px',
            cursor: 'pointer'
        });
        closeButton.addEventListener('click', () => this.hideModal());

        inner.appendChild(img);
        inner.appendChild(closeButton);
        modal.appendChild(inner);

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                this.hideModal();
            }
        });

        document.body.appendChild(modal);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modal.style.display === 'block') {
                this.hideModal();
            }
        });
    },

    showModal(src, alt = 'Imagen de ayuda') {
        this.createModal();
        const modal = document.getElementById(this.modalId);
        const img = modal.querySelector('#imgAyudaContextual');
        if (!img) return;

        img.src = src;
        img.alt = alt;
        modal.style.display = 'flex';
    },

    hideModal() {
        const modal = document.getElementById(this.modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    },

    initImageViewer(selector = this.buttonSelector) {
        this.createModal();
        document.querySelectorAll(selector).forEach(btn => {
            btn.addEventListener('click', () => {
                const src = btn.dataset.src || btn.getAttribute('data-src');
                const alt = btn.dataset.alt || btn.getAttribute('data-alt') || 'Imagen de ayuda';
                if (!src) {
                    console.warn('ModalHelper: elemento sin data-src', btn);
                    return;
                }
                this.showModal(src, alt);
            });
        });
    }
};

(function ready(fn) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fn);
    } else {
        fn();
    }
})(() => {
    ModalHelper.initImageViewer();
});
