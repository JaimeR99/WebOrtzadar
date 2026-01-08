// public/js/cabecera.js
(() => {
  const KEY_TEMA = 'tema';      // 'claro' | 'oscuro'
  const KEY_MENU = 'menuModo';  // 'expandido' | 'compacto' | 'oculto'

  const MODOS_MENU = ['expandido', 'compacto', 'oculto'];

  function aplicarTema(modo) {
    const body = document.body;
    body.classList.toggle('tema-claro', modo === 'claro');
    body.classList.toggle('tema-oscuro', modo !== 'claro');
  }

  function aplicarMenuModo(modo) {
    const body = document.body;

    body.classList.remove('menu-expandido', 'menu-compacto', 'menu-oculto', 'menu-colapsado');

    // Compatibilidad: si en CSS usábamos menu-colapsado como compacto
    if (modo === 'expandido') {
      body.classList.add('menu-expandido');
    } else if (modo === 'compacto') {
      body.classList.add('menu-compacto');
      body.classList.add('menu-colapsado'); // por si tu CSS ya usa esta clase
    } else {
      body.classList.add('menu-oculto');
    }
  }

  function siguienteModoMenu(actual) {
    const i = MODOS_MENU.indexOf(actual);
    const next = MODOS_MENU[(i + 1) % MODOS_MENU.length];
    return next;
  }

  function cargarPreferencias() {
    // Tema
    let tema = 'claro';
    try {
      const savedTema = localStorage.getItem(KEY_TEMA);
      if (savedTema === 'claro' || savedTema === 'oscuro') tema = savedTema;
    } catch (e) {}
    aplicarTema(tema);

    // Menú
    let menuModo = 'expandido';
    try {
      const savedMenu = localStorage.getItem(KEY_MENU);
      if (MODOS_MENU.includes(savedMenu)) menuModo = savedMenu;
    } catch (e) {}
    aplicarMenuModo(menuModo);
  }

  function guardarTema(modo) {
    try { localStorage.setItem(KEY_TEMA, modo); } catch (e) {}
  }

  function guardarMenu(modo) {
    try { localStorage.setItem(KEY_MENU, modo); } catch (e) {}
  }

  function init() {
    cargarPreferencias();

    // Delegación para que funcione aunque el header venga por include
    document.addEventListener('click', (ev) => {
      const btnTema = ev.target.closest('[data-toggle="tema"]');
      if (btnTema) {
        const esClaro = document.body.classList.contains('tema-claro');
        const nuevo = esClaro ? 'oscuro' : 'claro';
        aplicarTema(nuevo);
        guardarTema(nuevo);
        return;
      }

      const btnMenu = ev.target.closest('[data-toggle="menu-izquierdo"]');
      if (btnMenu) {
        // modo actual
        const body = document.body;
        let actual = 'expandido';
        if (body.classList.contains('menu-oculto')) actual = 'oculto';
        else if (body.classList.contains('menu-compacto') || body.classList.contains('menu-colapsado')) actual = 'compacto';

        const next = siguienteModoMenu(actual);
        aplicarMenuModo(next);
        guardarMenu(next);
        return;
      }
    });
  }

  document.addEventListener('DOMContentLoaded', init);
})();
