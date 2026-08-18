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
    },

    // Validar teléfono venezolano (11 dígitos, prefijo válido, no todo ceros)
    validarTelefono: (tlf) => {
        if (!tlf) return false;
        const clean = tlf.trim();
        // Debe contener solo números
        if (!/^\d+$/.test(clean)) return false;
        // Debe tener exactamente 11 dígitos
        if (clean.length !== 11) return false;
        // El prefijo debe ser válido
        const prefijo = clean.substring(0, 4);
        const prefijosValidos = [
            '0212', '0412', '0414', '0415', '0416', '0417', '0418',
            '0424', '0426', '0246', '0247', '0235', '0238', '0239'
        ];
        if (!prefijosValidos.includes(prefijo)) return false;
        // Los siguientes 7 dígitos no deben ser todo ceros
        const sufijo = clean.substring(4);
        if (sufijo === '0000000') return false;

        return true;
    },

    // Validar formato y dominio permitido del correo
    validarCorreo: (correo) => {
        if (!correo) return false;
        const clean = correo.trim().toLowerCase();
        // Regex de formato de correo estándar
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!emailRegex.test(clean)) return false;
        // Dominios permitidos
        const dominiosValidos = [
            '@gmail.com', '@yahoo.com', '@hotmail.com', '@outlook.com',
            '@live.com', '@icloud.com', '@aol.com', '@protonmail.com',
            '@gmx.com', '@zoho.com'
        ];
        return dominiosValidos.some(dom => clean.endsWith(dom));
    },

    // Validar cédula venezolana (V/E + primer dígito de 1 a 9 + 6 o 7 dígitos)
    validarCedula: (cedula) => {
        if (!cedula) return false;
        const clean = cedula.trim().toUpperCase();
        // El patrón asegura que no sea V0000001 (el primer dígito numérico es de 1 a 9)
        const pattern = /^[VE][1-9][0-9]{6,7}$/;
        return pattern.test(clean);
    }
};

// ==============================================
// SISTEMA CENTRAL DE VALIDACIÓN
// ==============================================
const Validador = (function () {
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
    // OBTENER LA URL BASE DEL SERVIDOR DINÁMICAMENTE
    // ==========================================
    function obtenerServerURL() {
        if (typeof window.SERVERURL !== 'undefined') {
            return window.SERVERURL;
        }
        const formAjax = document.querySelector(".FormularioAjax");
        if (formAjax) {
            const action = formAjax.getAttribute("action");
            if (action && action.includes("ajax/")) {
                return action.split("ajax/")[0];
            }
        }
        const pathParts = window.location.pathname.split('/');
        if (pathParts.length > 1 && pathParts[1] !== '') {
            return window.location.origin + '/' + pathParts[1] + '/';
        }
        return window.location.origin + '/';
    }

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
        hayErrores: function () { return erroresActivos.size > 0; },
        obtenerErrores: function () { return Array.from(erroresActivos); },
        limpiar: function () {
            erroresActivos.clear();
            actualizarBoton();
            return this;
        },
        restaurarTexto: restaurarTextoOriginal,
        restaurarTodos: restaurarTodosLosTextos,

        /**
         * NUEVO: Valida que la fecha inicio no sea posterior a la fin.
         */
        validarRangoFechas: function (idInicio, idFin, idAlerta, mensaje = "La fecha de inicio no puede ser posterior a la fecha de fin") {
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
        validarFechasUnicas: function (idsInputs, idAlerta, mensaje = "Las fechas no pueden repetirse") {
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
        },

        /**
         * NUEVO: Valida un campo en tiempo real (formato local + duplicado en base de datos)
         */
        validarCampoUnicoAsync: function (params) {
            const {
                idInput,
                idAlerta,
                tipo,
                ajaxDestino = null,
                accion = "validar_campo",
                campo = null,
                idValor = null,
                idColumna = null,
                mensajes = {}
            } = params;

            const input = document.getElementById(idInput);
            if (!input) return;

            const valor = input.value.trim();

            // Si está vacío, limpiamos el error si no es obligatorio
            if (valor === "") {
                this.set(idAlerta, false);
                Utils.marcarInputError(idInput, false);
                return;
            }

            // 1. Validación de Formato Local
            let formatoValido = false;
            let msgErrorFormato = mensajes.formato || "El formato ingresado es incorrecto.";

            if (tipo === 'telefono') {
                formatoValido = Utils.validarTelefono(valor);
                msgErrorFormato = mensajes.formato || "Teléfono inválido. Debe tener 11 dígitos y un prefijo válido (ej: 04121234567).";
            } else if (tipo === 'correo') {
                formatoValido = Utils.validarCorreo(valor);
                msgErrorFormato = mensajes.formato || "Correo inválido. Solo se permiten dominios personales comunes.";
            } else if (tipo === 'cedula') {
                formatoValido = Utils.validarCedula(valor);
                msgErrorFormato = mensajes.formato || "Cédula inválida. Debe comenzar con V o E, ej: V12345678 (no se permiten ceros al inicio).";
            } else {
                formatoValido = true; // Pasa si no requiere formato predefinido
            }

            if (!formatoValido) {
                this.set(idAlerta, true, msgErrorFormato);
                Utils.marcarInputError(idInput, true);
                return;
            }

            // Si no se especificó archivo AJAX de destino, solo validamos formato y terminamos
            if (!ajaxDestino || !campo) {
                this.set(idAlerta, false);
                Utils.marcarInputError(idInput, false);
                return;
            }

            // 2. Validación de Unicidad en BD vía AJAX específico
            const serverurl = obtenerServerURL();
            const url = `${serverurl}ajax/${ajaxDestino}?accion=${encodeURIComponent(accion)}&campo=${encodeURIComponent(campo)}&valor=${encodeURIComponent(valor)}` +
                (idColumna ? `&id_columna=${encodeURIComponent(idColumna)}` : '') +
                (idValor ? `&id_valor=${encodeURIComponent(idValor)}` : '');

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data && typeof data === "object") {
                        if (!data.valido) {
                            const msgDuplicado = mensajes.duplicado || data.mensaje || "Este valor ya está registrado en el sistema.";
                            this.set(idAlerta, true, msgDuplicado);
                            Utils.marcarInputError(idInput, true);
                        } else {
                            this.set(idAlerta, false);
                            Utils.marcarInputError(idInput, false);
                        }
                    } else {
                        console.error("Validador: Respuesta inesperada del servidor.");
                    }
                })
                .catch(err => {
                    console.error("Validador: Error en la conexión AJAX:", err);
                });
        }
    };
})();

// ==============================================
// MODAL DE TEXTO/LINK 
// ==============================================
function mostrarModalTexto({ titulo = '', textos = [], link = null }) {
    let modal = document.getElementById('modalTexto');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'modalTexto';
        Object.assign(modal.style, {
            position: 'fixed',
            top: '0',
            left: '0',
            width: '100vw',
            height: '100vh',
            background: 'rgba(0,0,0,0.5)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            zIndex: '9999',
        });
        modal.innerHTML = '<div id="modalTextoContent" style="background:#fff;padding:24px 20px 16px 20px;border-radius:8px;min-width:300px;max-width:90vw;box-shadow:0 2px 16px #0002;position:relative;"></div>';
        document.body.appendChild(modal);
        // Cerrar al hacer click fuera del contenido
        modal.addEventListener('click', function(e) {
            if (e.target === modal) modal.style.display = 'none';
        });
    }
    const content = document.getElementById('modalTextoContent');
    let html = '';
    if (titulo) html += `<div style="font-weight:bold;font-size:1.2em;margin-bottom:10px;">${titulo}</div>`;
    if (Array.isArray(textos)) {
        textos.forEach(txt => {
            html += `<div style="margin-bottom:6px;">${txt}</div>`;
        });
    }
    if (link && link.text && link.href) {
        html += `<a href="${link.href}" target="_blank" style="display:inline-block;margin-top:10px;padding:6px 16px;background:#3CA6A6;color:#fff;border-radius:4px;text-decoration:none;">${link.text}</a>`;
    }
    html += '<button onclick="document.getElementById(\'modalTexto\').style.display=\'none\'" style="margin-top:16px;float:right;background:#ccc;border:none;padding:4px 12px;border-radius:4px;">Cerrar</button>';
    content.innerHTML = html;
    modal.style.display = 'flex';
}
// Ejemplo de uso:
// mostrarModalTexto({
//   titulo: 'Información',
//   textos: ['Línea 1', 'Línea 2'],
//   link: { text: 'Ir a Google', href: 'https://google.com' }
// });

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
