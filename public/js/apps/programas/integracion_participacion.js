// public/js/apps/programas/integracion_participacion.js
document.addEventListener('click', (e) => {
  const a = e.target.closest('a.programa-tile');
  if (!a) return;

  const isDisabled = a.classList.contains('is-disabled') || a.getAttribute('aria-disabled') === 'true';
  if (!isDisabled) return;

  e.preventDefault();
  const nombre = a.querySelector('.programa-tile__title')?.textContent?.trim() || 'Este programa';
  alert(`${nombre} todavía no está operativo.`);
});
