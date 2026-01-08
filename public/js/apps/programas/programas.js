// public/js/apps/programas/programas.js
// UX: evitar navegación en tarjetas "En construcción" + desplegar subprogramas inline bajo la tarjeta clicada

document.addEventListener('click', (e) => {
  const a = e.target.closest('a.programa-tile');
  if (!a) return;

  const id = a.dataset.programa || '';
  const isDisabled = a.classList.contains('is-disabled') || a.getAttribute('aria-disabled') === 'true';

  // ✅ Caso especial: Integración y participación social => panel inline justo debajo
  if (id === 'integracion_participacion') {
    e.preventDefault();

    const panel = document.getElementById('subprogramasPanel');
    if (!panel) return;

    const grid = a.closest('.programas-grid');
    if (!grid) return;

    const isOpen = !panel.hasAttribute('hidden');
    const isJustAfterThisTile = (a.nextElementSibling === panel);

    // Si ya está abierto justo aquí => cerrar
    if (isOpen && isJustAfterThisTile) {
      panel.setAttribute('hidden', '');
      panel.classList.remove('is-open');
      return;
    }

    // Abrir (o reubicar)
    panel.removeAttribute('hidden');
    panel.classList.add('is-open');
    a.insertAdjacentElement('afterend', panel);

    panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    return;
  }

  // Resto: bloquear navegación si disabled
  if (!isDisabled) return;

  e.preventDefault();
  const nombre = a.querySelector('.programa-tile__title')?.textContent?.trim() || 'Este programa';
  alert(`${nombre} todavía no está operativo.`);
});

// Cerrar panel
document.addEventListener('click', (e) => {
  const btn = e.target.closest('#btnCerrarSubprogramas');
  if (!btn) return;

  const panel = document.getElementById('subprogramasPanel');
  if (!panel) return;

  panel.setAttribute('hidden', '');
  panel.classList.remove('is-open');
});

// Subprogramas: por ahora alert si disabled
document.addEventListener('click', (e) => {
  const a = e.target.closest('.subprograma-tile');
  if (!a) return;

  const isDisabled = a.classList.contains('is-disabled') || a.getAttribute('aria-disabled') === 'true';
  if (!isDisabled) return;

  e.preventDefault();
  const nombre = a.querySelector('.subprograma-tile__title')?.textContent?.trim() || 'Este subprograma';
  alert(`${nombre} todavía no está operativo.`);
});
(() => {
  const panel = document.getElementById('subprogramasPanel');
  if (panel) {
    panel.setAttribute('hidden', '');
    panel.classList.remove('is-open');
  }
})();
