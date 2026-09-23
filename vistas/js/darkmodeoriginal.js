
(function() {
    const STORAGE_KEY = 'modo_oscuro';
    const html = document.documentElement;
    const btn = document.getElementById('btn-dark-mode');
    const icon = document.getElementById('icon-dark-mode');

    // Función para aplicar el modo
    function aplicarModo(oscuro) {
        if (oscuro) {
            html.classList.add('dark-mode');
            if (icon) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            }
        } else {
            html.classList.remove('dark-mode');
            if (icon) {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        }
    }

    // Leer preferencia guardada
    const guardado = localStorage.getItem(STORAGE_KEY);
    if (guardado === 'true') {
        aplicarModo(true);
    } else if (guardado === 'false') {
        aplicarModo(false);
    } else {
        // Si no hay preferencia, usar la del sistema
        const prefiereOscuro = window.matchMedia('(prefers-color-scheme: dark)').matches;
        aplicarModo(prefiereOscuro);
    }

    // Toggle al hacer click
    if (btn) {
        btn.addEventListener('click', () => {
            const estaOscuro = html.classList.contains('dark-mode');
            aplicarModo(!estaOscuro);
            localStorage.setItem(STORAGE_KEY, String(!estaOscuro));
        });
    }
})();