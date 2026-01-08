(() => {
  const groups = document.querySelectorAll('[data-menu-group]');
  groups.forEach(group => {
    const key = `menu_group_${group.dataset.menuGroup}`;
    const btn = group.querySelector('.menu-group__toggle');
    if (!btn) return;

    // restaurar estado
    const saved = localStorage.getItem(key);
    if (saved === 'open') group.classList.add('is-open');

    btn.addEventListener('click', () => {
      group.classList.toggle('is-open');
      const isOpen = group.classList.contains('is-open');
      btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      localStorage.setItem(key, isOpen ? 'open' : 'closed');
    });
  });
})();
