(function () {
    const STORAGE_PREFIX = 'form_cache_';

    function getKey(form) {
        return STORAGE_PREFIX + (form.id || form.dataset.form || 'anonimo');
    }

    function esCacheable(el) {
        if (!el.name) return false;
        if (el.disabled) return false;
        if (el.dataset.noCache !== undefined) return false; // exclusión manual
        const tipo = (el.type || '').toLowerCase();
        if (['file', 'password', 'submit', 'button', 'reset', 'hidden'].includes(tipo)) {
            return false;
        }
        return true;
    }

    function guardar(form) {
        const datos = {};
        form.querySelectorAll('input, select, textarea').forEach(el => {
            if (!esCacheable(el)) return;
            if (el.type === 'checkbox') {
                datos[el.name] = el.checked;
            } else if (el.tagName === 'SELECT' && el.multiple) {
                datos[el.name] = Array.from(el.selectedOptions).map(o => o.value);
            } else {
                datos[el.name] = el.value;
            }
        });
        try {
            localStorage.setItem(getKey(form), JSON.stringify(datos));
        } catch (e) {}
    }

    function restaurar(form) {
        let datos;
        try {
            const raw = localStorage.getItem(getKey(form));
            if (!raw) return;
            datos = JSON.parse(raw);
        } catch (e) { return; }
        if (!datos || typeof datos !== 'object') return;

        form.querySelectorAll('input, select, textarea').forEach(el => {
            if (!esCacheable(el)) return;
            if (!(el.name in datos)) return;
            const valor = datos[el.name];

            if (el.type === 'checkbox') {
                el.checked = !!valor;
            } else if (el.tagName === 'SELECT' && el.multiple) {
                Array.from(el.options).forEach(o => {
                    o.selected = Array.isArray(valor) && valor.includes(o.value);
                });
            } else {
                el.value = valor;
            }

            el.dispatchEvent(new Event('input',  { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        });

        if (window.jQuery) {
            jQuery(form).find('select').trigger('change.select2');
        }
    }

    function limpiar(form) {
        try { localStorage.removeItem(getKey(form)); } catch (e) {}
    }

    function init(form) {
        if (!form || form.dataset.cacheInit === '1') return;
        form.dataset.cacheInit = '1';

        form.addEventListener('input',  () => guardar(form));
        form.addEventListener('change', () => guardar(form));
        restaurar(form);
    }

    // Auto-init SOLO si el form tiene data-cache="true"
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('form[data-cache="true"]').forEach(init);
    });

    window.FormCache = { init, guardar, restaurar, limpiar };
})();