(() => {
  const list = document.getElementById('ocioList');
  const btnAplicar = document.getElementById('btnAplicar');
  const btnLimpiar = document.getElementById('btnLimpiar');

  async function cargar() {
    list.innerHTML = '<div class="empty">Cargando…</div>';

    const params = new URLSearchParams({
      grupo: document.getElementById('fGrupo')?.value || '',
      anio: document.getElementById('fAnio')?.value || '',
      responsable: document.getElementById('fResponsable')?.value || ''
    });

    const res = await fetch(`public/php/programas/ocio/grupos_listar.php?${params}`);
    const json = await res.json();

    if (!json.ok || !json.data.length) {
      list.innerHTML = '<div class="empty">No hay grupos.</div>';
      return;
    }

    list.innerHTML = json.data.map(g => `
      <div class="panel ocio-grupo" data-id="${g.id}">
        <div class="ocio-grupo__title">${g.nombre}</div>
        <div class="ocio-grupo__meta">
          <span>Año: ${g.anio}</span>
          <span>Responsable: ${g.responsable || '-'}</span>
          <span>Participantes: ${g.num_participantes}</span>
        </div>
        <button class="btn btn-secondary" data-open="${g.id}">Abrir grupo →</button>
      </div>
    `).join('');
  }

  btnAplicar?.addEventListener('click', cargar);
  btnLimpiar?.addEventListener('click', () => {
    ['fGrupo','fAnio','fResponsable'].forEach(id => document.getElementById(id).value = '');
    cargar();
  });

  cargar();
})();
