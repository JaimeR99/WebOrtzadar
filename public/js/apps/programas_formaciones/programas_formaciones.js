/* public/js/apps/formaciones/formaciones.js */
(() => {
  const $ = (s, root = document) => root.querySelector(s);

  // ---------------- Listado + filtros ----------------
  const listEl = $('#formacionesList');
  const fFormacion = $('#fFormacion');
  const fAnio = $('#fAnio');
  const fTipo = $('#fTipo');
  const fIntegrador = $('#fIntegrador');
  const btnAplicar = $('#btnAplicar');
  const btnLimpiar = $('#btnLimpiar');
  const btnNuevaFormacion = $('#btnNuevaFormacion');

  // ---------------- Modal base ----------------
  const modal = document.querySelector('.io-modal[data-program="formaciones"]');
  if (!modal) return;

  const modalId = modal.id; // programModal_formaciones
  const statusEl = document.getElementById(`${modalId}_status`);
  const titleEl = document.getElementById(`${modalId}_title`);
  const subEl = document.getElementById(`${modalId}_sub`);

  // -------- Datos (Formación) --------
  const hidId = $('#f_id', modal);
  const inNombre = $('#f_nombre', modal);
  const inFecha = $('#f_fecha', modal);
  const selTipo = $('#f_id_tipo', modal);
  const selInteg = $('#f_id_integrador', modal);

  // foto formación
  const inFoto = $('#f_foto', modal);
  const formAvatar = $('#formacionAvatar', modal);
  const formAvatarImg = formAvatar?.querySelector('img');
  const formAvatarFallback = formAvatar?.querySelector('.form-photo__fallback');

  // mini avatar integrador
  const integAvatar = $('#formIntegAvatar', modal);
  const integAvatarImg = integAvatar?.querySelector('img');
  const integAvatarFallback = integAvatar?.querySelector('.form-mini-avatar__fallback');

  // -------- Participantes --------
  const inBuscarP = $('#formPartBuscar', modal);
  const pResultados = $('#formPartResultados', modal);
  const pList = $('#formPartList', modal);

  // -------- Acta --------
  const aId = $('#form_acta_id', modal);
  const aFecha = $('#form_acta_fecha', modal);
  const aAsistencia = $('#a_asistencia', modal);
  const aValoracion = $('#form_acta_valoracion', modal);
  const aIncidencia = $('#a_incidencia', modal);
  const aIncList = $('#formIncList', modal);
  const aHist = $('#a_hist', modal);

  // botones acta
  const btnNuevaActa = $('#btnNuevaActa', modal);
  const btnAddInc = $('#btnAddIncidencia', modal);
  const btnGuardarActa = $('#formBtnGuardarActa', modal);
  const btnExportActa = $('#formBtnExportActa', modal);

  const subtabsBar = modal.querySelector('.form-subtabsbar');
  const subtabsActions = modal.querySelector('.form-subtabs-actions');

  const TAB_DATOS = 'tab_form_datos';
  const TAB_PART = 'tab_form_participantes';
  const TAB_ACTA = 'tab_form_acta';

  // ---------------- Estado local ----------------
  let cacheListado = [];
  let cacheParticipantes = [];
  let cacheActa = null;

  let cachePresentes = new Set();         // ids usuario
  let incidenciasPendientes = [];         // texto nuevo antes de guardar
  let cacheIncidenciasServidor = [];      // incidencias devueltas por API

  // ---------------- Helpers ----------------
  const escapeHtml = (s) =>
    String(s ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');

  const status = (msg = '') => { if (statusEl) statusEl.textContent = msg; };

  function lockBodyScroll(lock) { document.body.style.overflow = lock ? 'hidden' : ''; }

  function openModal() {
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    lockBodyScroll(true);
    updateActaActionsVisibility();
    (inNombre || modal)?.focus?.();
  }

  function closeModal() {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    lockBodyScroll(false);
  }

  modal.addEventListener('click', (e) => {
    if (e.target.matches('[data-close="1"]')) closeModal();
  });

  async function fetchJson(url, opts = {}) {
    const res = await fetch(url, {
      headers: { Accept: 'application/json', ...(opts.headers || {}) },
      ...opts,
    });

    const text = await res.text();
    const ct = (res.headers.get('content-type') || '').toLowerCase();

    if (!ct.includes('application/json')) {
      throw new Error(`No es JSON (HTTP ${res.status}) URL final: ${res.url}\nInicio: ${text.slice(0, 200)}`);
    }

    let json;
    try { json = JSON.parse(text); }
    catch {
      throw new Error(`JSON inválido (HTTP ${res.status}) URL final: ${res.url}\nInicio: ${text.slice(0, 200)}`);
    }

    if (!res.ok) throw new Error(json?.error || `HTTP ${res.status}`);
    return json;
  }

  // ---------------- Fotos / avatares ----------------
  const formPhotoUrl = (id) => `/webOrtzadar/uploads/formaciones/${encodeURIComponent(String(id))}.jpg`;
  const workerPhotoUrl = (id) => `/webOrtzadar/uploads/trabajadores/${encodeURIComponent(String(id))}.jpg`;
  const userPhotoUrl = (id) => `/webOrtzadar/uploads/usuarios/${encodeURIComponent(String(id))}.jpg`;

  function formInitials(nombre) {
    const n = String(nombre || '').trim();
    if (!n) return 'FO';
    const parts = n.split(/\s+/).filter(Boolean);
    const a = (parts[0]?.[0] || 'F').toUpperCase();
    const b = (parts[1]?.[0] || 'O').toUpperCase();
    return a + b;
  }

  function syncPhotoBox(boxEl, imgEl) {
    if (!boxEl || !imgEl) return;

    const setHas = () => { boxEl.classList.add('has-photo'); boxEl.classList.remove('is-no-photo'); };
    const setNo = () => { boxEl.classList.add('is-no-photo'); boxEl.classList.remove('has-photo'); };

    setNo();
    imgEl.addEventListener('load', setHas, { once: true });
    imgEl.addEventListener('error', setNo, { once: true });

    if (imgEl.complete) {
      if (imgEl.naturalWidth > 0) setHas();
      else setNo();
    }
  }

  function setFormAvatar(id, nombre) {
    if (!formAvatar || !formAvatarImg) return;
    if (formAvatarFallback) formAvatarFallback.textContent = formInitials(nombre);
    syncPhotoBox(formAvatar, formAvatarImg);
    formAvatarImg.src = `${formPhotoUrl(id)}?ts=${Date.now()}`;
  }

  function initialsFromLabel(labelText, defA = 'I', defB = '') {
    const parts = String(labelText || '').trim().split(/\s+/).filter(Boolean);
    const a = (parts[0]?.[0] || defA).toUpperCase();
    const b = (parts[1]?.[0] || defB).toUpperCase();
    return a + b;
  }

  function setMiniAvatar(wrapper, imgEl, fbEl, workerId, labelText) {
    if (!wrapper || !imgEl) return;

    if (!workerId) {
      wrapper.classList.add('is-no-photo');
      wrapper.classList.remove('has-photo');
      imgEl.src = '';
      if (fbEl) fbEl.textContent = initialsFromLabel(labelText || '', '—', '');
      return;
    }

    if (fbEl) fbEl.textContent = initialsFromLabel(labelText || '', 'T', '');

    wrapper.classList.add('is-no-photo');
    wrapper.classList.remove('has-photo');

    imgEl.onload = () => {
      wrapper.classList.remove('is-no-photo');
      wrapper.classList.add('has-photo');
    };
    imgEl.onerror = () => {
      wrapper.classList.add('is-no-photo');
      wrapper.classList.remove('has-photo');
    };

    imgEl.src = `${workerPhotoUrl(workerId)}?ts=${Date.now()}`;

    if (imgEl.complete) {
      if (imgEl.naturalWidth > 0) imgEl.onload();
      else imgEl.onerror();
    }
  }

  function syncMiniAvatarFromSelect() {
    if (!selInteg || !integAvatar) return;
    const id = Number(selInteg.value || 0);
    const label = selInteg.options?.[selInteg.selectedIndex]?.textContent || '';
    setMiniAvatar(integAvatar, integAvatarImg, integAvatarFallback, id, label);
  }

  selInteg?.addEventListener('change', syncMiniAvatarFromSelect);

  // ---------------- Tabs ----------------
function setActaActionsVisible(visible) {
  // ✅ igual que Vacaciones: la clase va en el MODAL
  modal.classList.toggle('is-acta', !!visible);

  // ✅ hidden manda (tu HTML lo tiene)
  if (subtabsActions) subtabsActions.hidden = !visible;
}


  function updateActaActionsVisibility() {
    const activeTab = subtabsBar?.querySelector('.io-tab.is-active');
    const paneId = activeTab?.dataset?.tab || '';
    setActaActionsVisible(paneId === TAB_ACTA);
  }

  function activarSubtab(scope, paneId) {
    const tabs = modal.querySelectorAll(`.io-subtabs[data-tab-scope="${scope}"] .io-tab[data-tab]`);
    tabs.forEach((t) => {
      const is = t.dataset.tab === paneId;
      t.classList.toggle('is-active', is);
      t.setAttribute('aria-selected', is ? 'true' : 'false');
      t.tabIndex = is ? 0 : -1;
    });

    const panes = modal.querySelectorAll(`.io-pane[role="tabpanel"][data-tab-scope="${scope}"]`);
    panes.forEach((p) => {
      const is = p.id === paneId;
      p.classList.toggle('is-active', is);
      p.setAttribute('aria-hidden', is ? 'false' : 'true');
      if (is) p.removeAttribute('hidden');
      else p.setAttribute('hidden', 'hidden');
    });

    updateActaActionsVisibility();
  }

  modal.addEventListener('click', (e) => {
    const tab = e.target.closest('.io-tab');
    if (!tab) return;

    const tablist = tab.closest('[data-tab-scope]');
    if (!tablist) return;

    const scope = tablist.dataset.tabScope;
    const paneId = tab.dataset.tab;
    if (!scope || !paneId) return;

    activarSubtab(scope, paneId);

    const id = Number(hidId?.value || 0);
    if (!id) return;

    if (paneId === TAB_PART) cargarParticipantes(id);
    if (paneId === TAB_ACTA) cargarActaUltima(id);
  });

  // ---------------- Cargar combos (Tipos / Integradores / filtros) ----------------
  async function cargarTipos(selectedId = null) {
    if (!selTipo) return;
    selTipo.innerHTML = `<option value="">—</option>`;

    try {
      const json = await fetchJson('/webOrtzadar/public/php/programas_formaciones/formaciones_tipos_listar.php');
      const rows = json?.data || [];
      selTipo.innerHTML =
        `<option value="">—</option>` +
        rows.map(t => `<option value="${escapeHtml(t.id)}">${escapeHtml(t.nombre)}</option>`).join('');
      if (selectedId != null) selTipo.value = String(selectedId);
    } catch {
      /* noop */
    }
  }

  async function cargarIntegradores(selectedId = null) {
    if (!selInteg) return;
    selInteg.innerHTML = `<option value="">—</option>`;

    try {
      // puedes moverlo a /public/php/formaciones/ si quieres; de momento reutiliza el global:
      const json = await fetchJson('/webOrtzadar/public/php/programas/trabajadores_listar.php');
      const rows = json?.data || [];
      selInteg.innerHTML =
        `<option value="">—</option>` +
        rows.map(t => `<option value="${escapeHtml(t.id)}">${escapeHtml(`${t.nombre} ${t.apellidos}`.trim())}</option>`).join('');
      if (selectedId != null) selInteg.value = String(selectedId);

      syncMiniAvatarFromSelect();
    } catch {
      /* noop */
    }
  }

  async function cargarFiltros() {
    // Tipo filtro
    if (fTipo) {
      try {
        const json = await fetchJson('/webOrtzadar/public/php/programas_formaciones/formaciones_tipos_listar.php');
        const rows = json?.data || [];
        const prev = fTipo.value;
        fTipo.innerHTML =
          `<option value="">—</option>` +
          rows.map(t => `<option value="${escapeHtml(t.id)}">${escapeHtml(t.nombre)}</option>`).join('');
        if (prev) fTipo.value = prev;
      } catch { /* noop */ }
    }

    // Integrador filtro
    if (fIntegrador) {
      try {
        const json = await fetchJson('/webOrtzadar/public/php/programas/trabajadores_listar.php');
        const rows = json?.data || [];
        const prev = fIntegrador.value;
        fIntegrador.innerHTML =
          `<option value="">—</option>` +
          rows.map(t => `<option value="${escapeHtml(t.id)}">${escapeHtml(`${t.nombre} ${t.apellidos}`.trim())}</option>`).join('');
        if (prev) fIntegrador.value = prev;
      } catch { /* noop */ }
    }
  }

  // ---------------- Listado ----------------
  async function cargarListado() {
    if (!listEl) return;
    listEl.innerHTML = `<div class="empty">Cargando…</div>`;

    const params = new URLSearchParams({
      formacion: fFormacion?.value || '',
      anio: fAnio?.value || '',
      tipo: fTipo?.value || '',
      integrador: fIntegrador?.value || '',
    });

    try {
      const json = await fetchJson(`/webOrtzadar/public/php/programas_formaciones/formaciones_listar.php?${params.toString()}`);
      cacheListado = json.data || [];
      pintarListado(cacheListado);
    } catch (e) {
      listEl.innerHTML = `<div class="empty">❌ ${escapeHtml(e.message || 'Error')}</div>`;
    }
  }

  function pintarListado(items) {
    if (!items.length) {
      listEl.innerHTML = `<div class="empty">No hay formaciones con esos filtros.</div>`;
      return;
    }

    const ts = Date.now();

    listEl.innerHTML = items.map((x) => {
      const id = x.id;
      const nombre = x.nombre || '-';
      const fecha = x.fecha || '-';
      const tipo = x.tipo || '-';
      const integrador = x.integrador || '-';

      return `
        <div class="form-card" data-id="${escapeHtml(id)}">
          <div class="form-card__avatar" data-form-avatar="${escapeHtml(id)}" title="${escapeHtml(nombre)}">
            <img src="${escapeHtml(formPhotoUrl(id))}?ts=${ts}" alt="${escapeHtml(nombre)}" loading="eager">
            <span class="form-card__fallback">${escapeHtml(formInitials(nombre))}</span>
          </div>

          <div class="form-card__main">
            <div class="form-card__title">${escapeHtml(nombre)}</div>
            <div class="form-card__meta">
              <span class="io-muted">Fecha: ${escapeHtml(fecha)}</span>
              <span class="io-muted">Tipo: ${escapeHtml(tipo)}</span>
              <span class="io-muted">Integrador: ${escapeHtml(integrador)}</span>
            </div>
          </div>

          <div class="form-card__actions">
            <button class="btn btn-secondary" type="button" data-open="${escapeHtml(id)}">Abrir →</button>
          </div>
        </div>
      `;
    }).join('');

    // activar estado avatar por load/error
    listEl.querySelectorAll('[data-form-avatar]').forEach((box) => {
      const img = box.querySelector('img');
      if (!img) return;
      syncPhotoBox(box, img);
    });
  }

  listEl?.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-open]');
    const card = e.target.closest('.form-card');
    const id = Number(btn?.dataset.open || card?.dataset.id || 0);
    if (!id) return;
    await openEditarFormacion(id);
  });

  // ---------------- Reset modal ----------------
  function resetForm() {
    if (hidId) hidId.value = '0';
    if (inNombre) inNombre.value = '';
    if (inFecha) inFecha.value = '';
    if (selTipo) selTipo.value = '';
    if (selInteg) selInteg.value = '';

    if (inFoto) inFoto.value = '';

    cacheParticipantes = [];
    cacheActa = null;
    cachePresentes = new Set();
    incidenciasPendientes = [];
    cacheIncidenciasServidor = [];

    if (pResultados) pResultados.innerHTML = '';
    if (pList) pList.innerHTML = `<div class="io-muted">—</div>`;

    if (aId) aId.value = '0';
    if (aFecha) aFecha.value = '';
    if (aValoracion) aValoracion.value = '';
    if (aIncidencia) aIncidencia.value = '';
    if (aAsistencia) aAsistencia.innerHTML = `<div class="io-muted">—</div>`;
    if (aIncList) aIncList.innerHTML = `<div class="io-muted">—</div>`;
    if (aHist) aHist.innerHTML = `<div class="io-muted">—</div>`;

    activarSubtab('formaciones', TAB_DATOS);
    setActaActionsVisible(false);

    // avatar principal
    if (formAvatarFallback) formAvatarFallback.textContent = 'FO';
    if (formAvatarImg) formAvatarImg.src = '';
    formAvatar?.classList.add('is-no-photo');
    formAvatar?.classList.remove('has-photo');

    // mini avatar
    if (integAvatar) {
      integAvatar.classList.add('is-no-photo');
      integAvatar.classList.remove('has-photo');
      if (integAvatarImg) integAvatarImg.src = '';
      if (integAvatarFallback) integAvatarFallback.textContent = 'I';
    }
  }

  // ---------------- Crear / editar ----------------
  btnNuevaFormacion?.addEventListener('click', async () => {
    resetForm();
    await cargarTipos(null);
    await cargarIntegradores(null);
    syncMiniAvatarFromSelect();

    status('');
    if (titleEl) titleEl.textContent = 'Formación: nueva';
    if (subEl) subEl.textContent = 'Rellena y guarda';
    openModal();
  });

  async function openEditarFormacion(id) {
    resetForm();
    status('Cargando…');

    try {
      const json = await fetchJson(`/webOrtzadar/public/php/programas_formaciones/formaciones_get.php?id=${encodeURIComponent(id)}`);
      const f = json.data || {};

      if (hidId) hidId.value = String(f.id || id);
      if (inNombre) inNombre.value = f.nombre || '';
      if (inFecha) inFecha.value = (f.fecha || '').replace(' ', 'T').slice(0, 16);

      await cargarTipos(f.id_tipo ?? null);
      await cargarIntegradores(f.id_integrador ?? null);
      syncMiniAvatarFromSelect();

      // avatar formación
      setFormAvatar(f.id || id, f.nombre || '');

      status('');
      if (titleEl) titleEl.textContent = f?.nombre ? `Formación: ${f.nombre}` : `Formación #${id}`;
      if (subEl) subEl.textContent = 'Edita y guarda';

      openModal();

      // precargas según pestaña activa
      const activeTab = subtabsBar?.querySelector('.io-tab.is-active');
      if (activeTab?.dataset?.tab === TAB_PART) await cargarParticipantes(id);
      if (activeTab?.dataset?.tab === TAB_ACTA) await cargarActaUltima(id);
    } catch (e) {
      status(`❌ ${e.message || 'Error'}`);
    }
  }

  // botón guardar del modal base
  const btnGuardarBase = document.getElementById(`${modalId}_btnGuardar`);
  btnGuardarBase?.addEventListener('click', () => guardarFormacion());

  async function guardarFormacion() {
    const id = Number(hidId?.value || 0);
    const nombre = String(inNombre?.value || '').trim();
    if (!nombre) { status('⚠️ El nombre es obligatorio'); return; }

    const fechaRaw = String(inFecha?.value || '').trim();
    const fecha = fechaRaw ? fechaRaw.replace('T', ' ') + ':00' : null;

    const payload = {
      id: id || 0,
      nombre,
      fecha,
      id_tipo: selTipo?.value ? Number(selTipo.value) : null,
      id_integrador: selInteg?.value ? Number(selInteg.value) : null,
    };

    status('Guardando…');

    try {
      const json = await fetchJson('/webOrtzadar/public/php/programas_formaciones/formaciones_save.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      const newId = Number(json?.data?.id || payload.id || 0);
      if (hidId) hidId.value = String(newId);

      status('✅ Guardado');
      if (titleEl) titleEl.textContent = `Formación: ${nombre}`;

      // foto
      if (inFoto?.files?.[0] && newId) {
        await subirFotoFormacion(newId, inFoto.files[0]);
        inFoto.value = '';
      }

      if (newId) setFormAvatar(newId, nombre);
      await cargarListado();
    } catch (e) {
      status(`❌ ${e.message || 'Error guardando'}`);
    }
  }

  // click avatar -> abrir file
  formAvatar?.addEventListener('click', () => inFoto?.click?.());

  inFoto?.addEventListener('change', () => {
    const id = Number(hidId?.value || 0);
    if (!id) return; // aún no guardado
    const f = inFoto.files?.[0];
    if (!f) return;
    subirFotoFormacion(id, f);
  });

  async function subirFotoFormacion(id, file) {
    status('Subiendo foto…');
    const fd = new FormData();
    fd.append('id', String(id));
    fd.append('file', file);

    try {
      const json = await fetchJson('/webOrtzadar/public/php/programas_formaciones/formaciones_foto_upload.php', {
        method: 'POST',
        headers: {}, // fetchJson ya mete Accept
        body: fd,
      });
      if (!json?.ok) throw new Error(json?.error || 'No se pudo subir');

      status('✅ Foto actualizada');
      setFormAvatar(id, inNombre?.value || '');
    } catch (e) {
      status(`❌ ${e.message || 'Error subiendo foto'}`);
    }
  }

  // ---------------- Participantes ----------------
  function initials(u) {
    const n = `${u.Nombre || ''} ${u.Apellidos || ''}`.trim();
    const parts = n.split(/\s+/).filter(Boolean);
    const a = (parts[0]?.[0] || 'U').toUpperCase();
    const b = (parts[1]?.[0] || '').toUpperCase();
    return a + b;
  }

  function fullName(u) {
    const n = `${u.Nombre || ''} ${u.Apellidos || ''}`.trim();
    return n || `Usuario #${u.id}`;
  }

  function photoUrl(u) {
    if (u?.foto_url) return String(u.foto_url);
    return userPhotoUrl(u.id);
  }

  function renderUserTile(u, { mode }) {
    const id = escapeHtml(u.id);
    const name = escapeHtml(fullName(u));
    const dni = escapeHtml(u.Dni || '—');
    const ini = escapeHtml(initials(u));
    const img = escapeHtml(photoUrl(u));

    if (mode === 'result') {
      return `
        <div class="form-tile" data-add-user="${id}">
          <div class="form-tile__avatar">
            <div class="form-uavatar" title="${name}">
              <img src="${img}" alt="${name}" loading="lazy"
                onerror="this.style.display='none'; this.parentElement.classList.add('is-fallback');">
              <span class="form-uavatar__fallback">${ini}</span>
            </div>
          </div>
          <div class="form-tile__meta">
            <div class="form-tile__name">${name}</div>
            <div class="form-tile__dni io-muted">${dni}</div>
          </div>
          <div class="form-tile__actions"><span class="io-muted">Añadir</span></div>
        </div>
      `;
    }

    return `
      <div class="form-tile" data-del-user="${id}">
        <div class="form-tile__avatar">
          <button class="form-uavatar is-clickable" type="button" data-del-user="${id}" title="Quitar">
            <img src="${img}" alt="${name}" loading="lazy"
              onerror="this.style.display='none'; this.parentElement.classList.add('is-fallback');">
            <span class="form-uavatar__fallback">${ini}</span>
            <span class="form-uavatar__x">×</span>
          </button>
        </div>
        <div class="form-tile__meta">
          <div class="form-tile__name">${name}</div>
          <div class="form-tile__dni io-muted">${dni}</div>
        </div>
      </div>
    `;
  }

  function renderResultadosBusqueda(users) {
    if (!pResultados) return;
    if (!users.length) { pResultados.innerHTML = `<div class="io-muted">Sin resultados</div>`; return; }
    pResultados.innerHTML = `
      <div class="form-users form-users--results">
        ${users.map(u => renderUserTile(u, { mode: 'result' })).join('')}
      </div>
    `;
  }

  async function buscarUsuarios(query) {
    if (!query) return [];
    const json = await fetchJson(`/webOrtzadar/public/php/programas_formaciones/formaciones_participantes_buscar.php?q=${encodeURIComponent(query)}`);
    return json?.data || [];
  }

  async function cargarParticipantes(idFormacion) {
    if (!pList) return;
    pList.innerHTML = `<div class="io-muted">Cargando…</div>`;

    try {
      const json = await fetchJson(`/webOrtzadar/public/php/programas_formaciones/formaciones_participantes_listar.php?id_formacion=${encodeURIComponent(idFormacion)}`);
      cacheParticipantes = json.data || [];
      pintarParticipantes();
      pintarAsistencia(); // acta
    } catch (e) {
      pList.innerHTML = `<div class="io-muted">❌ ${escapeHtml(e.message || 'Error')}</div>`;
    }
  }

  function participantesHeaderHtml() {
  return `
    <div class="form-section-title">
      <div class="form-section-title__left">
        <span class="form-section-title__dot"></span>
        <div>
          <div class="form-section-title__text">Participantes del grupo</div>
          <div class="form-section-title__sub">Click en la foto para quitar</div>
        </div>
      </div>
    </div>
  `;
}

function pintarParticipantes() {
  if (!pList) return;

  const idFormacion = Number(hidId?.value || 0);
  if (!idFormacion) {
    pList.innerHTML = `<div class="io-muted">Guarda la formación primero.</div>`;
    return;
  }

  const header = participantesHeaderHtml();

  if (!cacheParticipantes.length) {
    pList.innerHTML = header + `<div class="io-muted">—</div>`;
    return;
  }

  pList.innerHTML = header + `
    <div class="form-users form-users--selected">
      ${cacheParticipantes.map(u => renderUserTile(u, { mode: 'selected' })).join('')}
    </div>
  `;
}


  async function addParticipante(idFormacion, idUsuario) {
    const json = await fetchJson('/webOrtzadar/public/php/programas_formaciones/formaciones_participantes_add.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id_formacion: idFormacion, id_usuario: idUsuario }),
    });
    if (!json?.ok) throw new Error(json?.error || 'No se pudo añadir');
  }

  async function delParticipante(idFormacion, idUsuario) {
    const json = await fetchJson('/webOrtzadar/public/php/programas_formaciones/formaciones_participantes_del.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id_formacion: idFormacion, id_usuario: idUsuario }),
    });
    if (!json?.ok) throw new Error(json?.error || 'No se pudo quitar');
  }

  // live search
  let tSearch = null;
  inBuscarP?.addEventListener('input', () => {
    const q = String(inBuscarP.value || '').trim();
    clearTimeout(tSearch);
    tSearch = setTimeout(async () => {
      if (q.length < 2) { if (pResultados) pResultados.innerHTML = ''; return; }
      const users = await buscarUsuarios(q);
      renderResultadosBusqueda(users);
    }, 250);
  });

  pResultados?.addEventListener('click', async (e) => {
    const row = e.target.closest('[data-add-user]');
    if (!row) return;

    const idFormacion = Number(hidId?.value || 0);
    const idUsuario = Number(row.dataset.addUser || 0);
    if (!idFormacion || !idUsuario) return;

    try {
      status('Añadiendo participante…');
      await addParticipante(idFormacion, idUsuario);
      status('✅ Participante añadido');
      if (inBuscarP) inBuscarP.value = '';
      if (pResultados) pResultados.innerHTML = '';
      await cargarParticipantes(idFormacion);
    } catch (e2) {
      status(`❌ ${e2.message || 'Error'}`);
    }
  });

  pList?.addEventListener('click', async (e) => {
    const row = e.target.closest('[data-del-user]');
    if (!row) return;

    const idFormacion = Number(hidId?.value || 0);
    const idUsuario = Number(row.dataset.delUser || 0);
    if (!idFormacion || !idUsuario) return;

    if (!confirm('¿Quitar participante?')) return;

    try {
      status('Quitando participante…');
      await delParticipante(idFormacion, idUsuario);
      status('✅ Participante quitado');

      // si estaba marcado como presente, lo quitamos del set (evita guardar ids huérfanos)
      if (cachePresentes.has(idUsuario)) cachePresentes.delete(idUsuario);

      await cargarParticipantes(idFormacion);
    } catch (e2) {
      status(`❌ ${e2.message || 'Error'}`);
    }
  });

  // ---------------- Acta + asistencia + incidencias ----------------
  function renderAsistenciaTile(u) {
    const idNum = Number(u.id);
    const id = escapeHtml(u.id);
    const name = escapeHtml(fullName(u));
    const dni = escapeHtml(u.Dni || '—');
    const ini = escapeHtml(initials(u));
    const img = escapeHtml(photoUrl(u));
    const isChecked = cachePresentes.has(idNum) ? 'is-checked' : '';

    return `
      <div class="form-as-tile ${isChecked}">
        <button class="form-as-avatar" type="button" data-as-toggle="${id}" title="Marcar / desmarcar asistencia">
          <img src="${img}" alt="${name}" loading="lazy"
            onerror="this.style.display='none'; this.parentElement.classList.add('is-fallback');">
          <span class="form-as-fallback">${ini}</span>
          <span class="form-as-check">✓</span>
        </button>
        <div class="form-as-meta">
          <div class="form-as-name">${name}</div>
          <div class="form-as-dni io-muted">${dni}</div>
        </div>
      </div>
    `;
  }

  function pintarAsistencia() {
    if (!aAsistencia) return;

    const idFormacion = Number(hidId?.value || 0);
    if (!idFormacion) { aAsistencia.innerHTML = `<div class="io-muted">Guarda la formación primero.</div>`; return; }

    if (!cacheParticipantes.length) { aAsistencia.innerHTML = `<div class="io-muted">No hay participantes.</div>`; return; }

    aAsistencia.innerHTML = `
      <div class="form-as-grid">
        ${cacheParticipantes.map(u => renderAsistenciaTile(u)).join('')}
      </div>
    `;
  }

  // toggle asistencia (event delegation)
  aAsistencia?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-as-toggle]');
    if (!btn) return;

    const id = Number(btn.dataset.asToggle || 0);
    if (!id) return;

    if (cachePresentes.has(id)) cachePresentes.delete(id);
    else cachePresentes.add(id);

    const tile = btn.closest('.form-as-tile');
    if (tile) tile.classList.toggle('is-checked', cachePresentes.has(id));
  });

  async function cargarActaUltima(idFormacion) {
    if (aHist) aHist.innerHTML = `<div class="io-muted">Cargando…</div>`;

    try {
      const json = await fetchJson(`/webOrtzadar/public/php/programas_formaciones/formaciones_acta_get_ultima.php?id_formacion=${encodeURIComponent(idFormacion)}`);

      cacheActa = json.data?.acta || null;
      cacheParticipantes = json.data?.participantes || [];
      cachePresentes = new Set((json.data?.presentes || []).map(n => Number(n)));

      cacheIncidenciasServidor = json.data?.incidencias || [];
      incidenciasPendientes = [];

      pintarActa(cacheActa, cacheIncidenciasServidor);
      pintarParticipantes();
      pintarAsistencia();
      await cargarHistActas(idFormacion);
      updateActaActionsVisibility();
    } catch (e) {
      if (aHist) aHist.innerHTML = `<div class="io-muted">❌ ${escapeHtml(e.message || 'Error')}</div>`;
    }
  }

  function pintarActa(acta, incidenciasServidor = []) {
    if (aId) aId.value = acta?.id ? String(acta.id) : '0';
    if (aFecha) aFecha.value = (acta?.fecha || '').replace(' ', 'T').slice(0, 16);
    if (aValoracion) aValoracion.value = acta?.valoracion || '';

    pintarAsistencia();
    pintarIncidenciasList(incidenciasServidor);
  }

function pintarIncidenciasList(incidenciasServidor = []) {
  if (!aIncList) return;

  const all = [
    ...(incidenciasServidor || []).map(x => ({ from: 'db', text: x.incidencia, fecha: x.fecha })),
    ...(incidenciasPendientes || []).map((t, pidx) => ({ from: 'new', text: t, fecha: '', pidx })),
  ].filter(x => String(x.text || '').trim() !== '');

  if (!all.length) {
    aIncList.innerHTML = `<div class="io-muted">—</div>`;
    return;
  }

  aIncList.innerHTML = `
    <div class="form-inc-list">
      ${all.map(x => {
        const isNew = x.from === 'new';
        const badge = isNew ? ` <span class="form-pill form-pill--pending">pendiente</span>` : '';
        const fecha = x.fecha ? ` <span class="io-muted">(${escapeHtml(x.fecha)})</span>` : '';
        const del = isNew ? `<button class="form-inc-del" type="button" data-del-inc="${escapeHtml(x.pidx)}">×</button>` : '';

        return `
          <div class="form-inc-item">
            <div class="form-inc-text">
              ${escapeHtml(x.text)}${fecha}${badge}
            </div>
            ${del}
          </div>
        `;
      }).join('')}
    </div>
  `;
}


  aIncList?.addEventListener('click', (e) => {
    const b = e.target.closest('[data-del-inc]');
    if (!b) return;
    const idx = Number(b.dataset.delInc);
    if (!Number.isFinite(idx)) return;
    incidenciasPendientes = incidenciasPendientes.filter((_, i) => i !== idx);
    pintarIncidenciasList(cacheIncidenciasServidor);
  });

  btnNuevaActa?.addEventListener('click', () => {
    if (aId) aId.value = '0';
    if (aFecha) aFecha.value = '';
    if (aValoracion) aValoracion.value = '';
    if (aIncidencia) aIncidencia.value = '';

    cachePresentes = new Set();
    incidenciasPendientes = [];
    cacheIncidenciasServidor = [];

    pintarAsistencia();
    if (aIncList) aIncList.innerHTML = `<div class="io-muted">—</div>`;
    status('Acta nueva (sin guardar)');
  });

  btnAddInc?.addEventListener('click', () => {
    const value = String(aIncidencia?.value || '').trim();
    if (!value) { status('⚠️ Escribe una incidencia primero'); aIncidencia?.focus?.(); return; }
    incidenciasPendientes.push(value);
    aIncidencia.value = '';
    status('');
    pintarIncidenciasList(cacheIncidenciasServidor);
  });

  btnGuardarActa?.addEventListener('click', async () => {
    const idFormacion = Number(hidId?.value || 0);
    if (!idFormacion) return;

    const idActa = Number(aId?.value || 0);
    const fechaRaw = String(aFecha?.value || '').trim();
    const fecha = fechaRaw ? fechaRaw.replace('T', ' ') + ':00' : null;

    const payload = {
      id_acta: idActa || 0,
      id_formacion: idFormacion,
      fecha,
      valoracion: String(aValoracion?.value || '').trim(),
      presentes: [...cachePresentes].map(n => Number(n)),
      incidencias: incidenciasPendientes.slice(),
    };

    status('Guardando acta…');

    try {
      const json = await fetchJson('/webOrtzadar/public/php/programas_formaciones/formaciones_acta_save.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      if (!json?.ok) throw new Error(json?.error || 'No se pudo guardar acta');

      status('✅ Acta guardada');
      incidenciasPendientes = [];
      await cargarActaUltima(idFormacion);
    } catch (e) {
      status(`❌ ${e.message || 'Error guardando acta'}`);
    }
  });

  btnExportActa?.addEventListener('click', () => {
    const idActa = Number(aId?.value || 0);
    if (!idActa) { status('⚠️ No hay acta para exportar'); return; }
    window.open(`/webOrtzadar/public/php/programas_formaciones/formaciones_acta_export.php?id_acta=${encodeURIComponent(idActa)}`, '_blank');
  });

async function cargarHistActas(idFormacion) {
  if (!aHist) return;
  aHist.innerHTML = `<div class="io-muted">Cargando…</div>`;

  try {
    const json = await fetchJson(`/webOrtzadar/public/php/programas_formaciones/formaciones_actas_listar.php?id_formacion=${encodeURIComponent(idFormacion)}`);
    const rows = json.data || [];

    if (!rows.length) { aHist.innerHTML = `<div class="io-muted">—</div>`; return; }

    aHist.innerHTML = `
      <div class="form-list">
        ${(rows || []).map(a => `
          <div class="form-row">
            <div class="form-row__name">
              <strong>${escapeHtml((a.fecha || '').slice(0,16)) || '—'}</strong>
              <span class="io-muted"> · presentes: ${escapeHtml(a.n_presentes ?? '—')} · incidencias: ${escapeHtml(a.n_incidencias ?? '—')}</span>
            </div>
            <button class="btn btn-secondary" type="button" data-open-acta="${escapeHtml(a.id)}">Abrir</button>
          </div>
        `).join('')}
      </div>
    `;
  } catch (e) {
    aHist.innerHTML = `<div class="io-muted">❌ ${escapeHtml(e.message || 'Error')}</div>`;
  }
}


  aHist?.addEventListener('click', async (e) => {
    const chip = e.target.closest('[data-open-acta]');
    if (!chip) return;

    const idActa = Number(chip.dataset.openActa || 0);
    if (!idActa) return;

    try {
      status('Cargando acta…');
      const json = await fetchJson(`/webOrtzadar/public/php/programas_formaciones/formaciones_acta_get.php?id_acta=${encodeURIComponent(idActa)}`);

      cacheActa = json.data?.acta || null;
      cacheParticipantes = json.data?.participantes || cacheParticipantes || [];
      cachePresentes = new Set((json.data?.presentes || []).map(n => Number(n)));

      incidenciasPendientes = [];
      cacheIncidenciasServidor = json.data?.incidencias || [];

      pintarActa(cacheActa, cacheIncidenciasServidor);
      pintarParticipantes();
      pintarAsistencia();
      status('');
    } catch (e2) {
      status(`❌ ${e2.message || 'Error'}`);
    }
  });

  // ---------------- filtros ----------------
  btnAplicar?.addEventListener('click', () => cargarListado());
  btnLimpiar?.addEventListener('click', () => {
    if (fFormacion) fFormacion.value = '';
    if (fAnio) fAnio.value = '';
    if (fTipo) fTipo.value = '';
    if (fIntegrador) fIntegrador.value = '';
    cargarListado();
  });

  // ---------------- init ----------------
  (async () => {
    await cargarFiltros();
    await cargarTipos();
    await cargarIntegradores();
    await cargarListado();
  })();
})();
