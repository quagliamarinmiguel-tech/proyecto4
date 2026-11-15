(function() {
    const STORAGE_KEY = 'modo_oscuro';
    const html = document.documentElement;
    const btn = document.getElementById('btn-dark-mode');
    const icon = document.getElementById('icon-dark-mode');

    // ─────────────────────────────────────────────
    // ¿Esta vista permite modo oscuro?
    // PHP lo define con: window.DARK_MODE_BLOQUEADO = true;
    // ─────────────────────────────────────────────
    const bloqueado = window.DARK_MODE_BLOQUEADO === true;

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

    // ─────────────────────────────────────────────
    // Vista bloqueada → forzar claro y salir
    // (no leemos localStorage, no enganchamos el botón)
    // ─────────────────────────────────────────────
    if (bloqueado) {
        html.classList.remove('dark-mode');
        if (btn) btn.style.display = 'none';
        return;
    }

    // ─────────────────────────────────────────────
    // Vista normal → default CLARO.
    // Solo oscuro si el usuario lo pidió explícitamente.
    // ─────────────────────────────────────────────
    const guardado = localStorage.getItem(STORAGE_KEY);
    aplicarModo(guardado === 'true');

    // Toggle al hacer click
    if (btn) {
        btn.addEventListener('click', () => {
            const estaOscuro = html.classList.contains('dark-mode');
            aplicarModo(!estaOscuro);
            localStorage.setItem(STORAGE_KEY, String(!estaOscuro));
        });
    }
})();