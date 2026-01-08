// public/js/apps/programas_vida_independiente/programas_vida_independiente.js
(() => {
  const $ = (s, r = document) => r.querySelector(s);

  const listEl = $('#viList');
  const btnNew = $('#vi_btn_new');

  if (!listEl) return;

  // mini add
  const miniAdd = document.getElementById('viMiniAddUser');
  const inBuscar = document.getElementById('vi_buscar');
  const resBuscar = document.getElementById('vi_buscar_res');

  let cache = [];
  let userModalHelpers = null;
  let booting = null;

  // trabajadores cache
  let workers = null;
  let workersLoading = null;

  const PUESTO_TRABAJADOR = 1;
  const PUESTO_RESPONSABLE = 2;
  const PUESTO_ADMIN = 3;

  function status(msg) {
    listEl.innerHTML = `<div class="empty">${escapeHtml(msg || '')}</div>`;
  }

  function initials(name) {
    const s = String(name || '').trim();
    if (!s) return 'U';
    const parts = s.split(/\s+/).filter(Boolean);
    return ((parts[0]?.[0] || 'U') + (parts[1]?.[0] || '')).toUpperCase();
  }
  function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
  }

  function loadScript(src) {
    return new Promise((resolve, reject) => {
      const s = document.createElement('script');
      s.src = src; s.async = true;
      s.onload = resolve;
      s.onerror = () => reject(new Error('No se pudo cargar: ' + src));
      document.head.appendChild(s);
    });
  }

  async function bootUserModal() {
    if (userModalHelpers) return userModalHelpers;
    if (booting) return booting;

    booting = (async () => {
      if (!window.UserModalBase) {
        await loadScript('public/js/apps/components/user_modal.base.js');
      }

      userModalHelpers = window.UserModalBase.init({
        modalSelector: '#ioModal',
        statusElId: 'ioModalStatus',
        hiddenUserIdId: 'ioModalUserId',
        endpoints: { savePhoto: 'public/php/programas/io_save_photo.php' }
      });

      // bind registros UI
      bindRegistrosUI();

      // bind guardar responsable/integrador
      bindGuardarRespInt();

      return userModalHelpers;
    })();

    return booting;
  }

  // -------------------------
  // LISTADO
  // -------------------------
  async function cargarListado() {
    status('Cargando…');
    try {
      const res = await fetch('public/php/programas_vida_independiente/vi_listar.php', { headers: { Accept: 'application/json' } });
      const json = await res.json();
      if (!json?.ok) throw new Error(json?.error || 'Error');
      cache = json.data || [];

      if (!cache.length) { status('No hay personas en el programa.'); return; }

      listEl.innerHTML = cache.map(u => {
        const id = Number(u.id_usuario || 0);
        const full = `${u.Nombre || ''} ${u.Apellidos || ''}`.trim() || 'Sin nombre';
        const dni = u.Dni || '—';
        const email = u.Correo || '—';
        const telefono = u.Telefono_Usuario || '—';
        const direccion = u.Direccion || '—';

        return `
          <div class="io-person" role="button" tabindex="0" data-id="${id}">
            <div class="io-avatar io-avatar--photo">
              <img
                class="io-avatar__img"
                src="uploads/usuarios/${encodeURIComponent(id)}.jpg?v=${Date.now()}"
                alt=""
                loading="lazy"
                onerror="this.style.display='none'; this.parentElement.querySelector('.io-avatar__txt').style.display='grid';"
              >
              <span class="io-avatar__txt">${escapeHtml(initials(full))}</span>
            </div>

            <div class="io-person__main">
              <div class="io-person__title">${escapeHtml(full)}</div>

              <div class="io-person__meta">
                <span>DNI: ${escapeHtml(dni)}</span>
                <span>Email: ${escapeHtml(email)}</span>
                <span class="io-muted">Vida independiente</span>
              </div>

              <div class="io-person__footer">
                <span class="io-muted">Teléfono: ${escapeHtml(telefono)}</span>
                <span class="io-muted">Dirección: ${escapeHtml(direccion)}</span>
              </div>
            </div>

            <div class="io-person__actions">
              <button class="btn btn-secondary" type="button" data-open="${id}">Abrir →</button>
            </div>
          </div>
        `;
      }).join('');
    } catch (e) {
      console.error(e);
      status('Error cargando listado.');
    }
  }

  listEl.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-open]');
    const card = e.target.closest('.io-person');
    const id = Number(btn?.dataset?.open || card?.dataset?.id || 0);
    if (id) abrirModalVI(id);
  });

  // -------------------------
  // ABRIR MODAL (user_modal)
  // -------------------------
  async function abrirModalVI(idUsuario) {
    const h = await bootUserModal();

    // 1) abrir con usuario/diag (tu user_modal.base espera data.usuario/diag)
    await h.abrirModalConUsuario(
      idUsuario,
      async (id) => {
        const r = await fetch(`public/php/programas_vida_independiente/vi_user_get.php?id=${encodeURIComponent(id)}`, {
          headers: { Accept: 'application/json' }
        });
        return await r.json();
      },
      async () => {
        // activar pestaña "programa" (Vida independiente)
        activarTabPrograma();

        // reset visual tabs (BF / OBJ)
        document.querySelectorAll('#ioModal [data-is-ambito]').forEach(b => b.classList.remove('is-active'));
        document.querySelector('#ioModal [data-is-ambito="BF"]')?.classList.add('is-active');
        document.querySelectorAll('#ioModal [data-is-categoria]').forEach(b => b.classList.remove('is-active'));
        document.querySelector('#ioModal [data-is-categoria="OBJ"]')?.classList.add('is-active');

        // cargar programa (ids responsable/integrador) + pintar selects
        await cargarYBindRespInt(idUsuario);

        // modo por defecto: registros (no RI)
        setRespIntMode(false);

        // cargar registros por defecto BF/OBJ
        document.getElementById('is_ambito').value = 'BF';
        document.getElementById('is_categoria').value = 'OBJ';
        await cargarRegistrosVI(idUsuario, 'BF', 'OBJ');
      }
    );
  }

  function activarTabPrograma() {
    const btn = document.querySelector('#ioModal [data-tab="tab_main_program"]');
    if (btn) btn.click();
  }

  // -------------------------
  // MODO (Registros vs Responsable/Integrador)
  // -------------------------
  function setRespIntMode(on) {
    const cats = document.getElementById('isCategorias');
    const registros = document.getElementById('viRegistrosPanel');
    const respint = document.getElementById('viRespIntPanel');

    if (cats) cats.style.display = on ? 'none' : '';
    if (registros) registros.style.display = on ? 'none' : '';
    if (respint) respint.style.display = on ? '' : 'none';
  }

  // -------------------------
  // RESPONSABLE / INTEGRADOR
  // -------------------------
  async function ensureWorkers() {
    if (workers) return workers;
    if (workersLoading) return workersLoading;

    workersLoading = (async () => {
      const r = await fetch('public/php/programas_vida_independiente/vi_trabajadores_listar.php', { headers: { Accept: 'application/json' } });
      const j = await r.json();
      if (!j?.ok) throw new Error(j?.error || 'Error cargando trabajadores');
      workers = (j.data || []).map(t => ({
        id: Number(t.id),
        nombre: t.nombre || '',
        apellidos: t.apellidos || '',
        id_puesto: Number(t.id_puesto || 0)
      }));
      return workers;
    })();

    return workersLoading;
  }

  function labelWorker(t) {
    return `${(t.nombre || '').trim()} ${(t.apellidos || '').trim()}`.trim();
  }

  function fillSelect(selectEl, selected, list) {
    if (!selectEl) return;
    const base = `<option value="">— Selecciona —</option>`;
    const opts = list.map(t => {
      const id = String(t.id);
      const sel = (String(selected || '') === id) ? ' selected' : '';
      return `<option value="${id}"${sel}>${escapeHtml(labelWorker(t))}</option>`;
    }).join('');
    selectEl.innerHTML = base + opts;
  }


  // Avatar mini (Responsable / Integrador) — usa uploads/trabajadores/<id>.jpg
  function initials2(labelText, fallback = 'T') {
    const s = String(labelText || '').trim();
    if (!s) return fallback;
    const parts = s.split(/\s+/).filter(Boolean);
    const a = (parts[0]?.[0] || fallback).toUpperCase();
    const b = (parts[1]?.[0] || '').toUpperCase();
    return (a + b) || fallback;
  }

  function workerPhotoUrl(workerId) {
    return `uploads/trabajadores/${encodeURIComponent(String(workerId))}.jpg`;
  }

  function setMiniAvatar(wrapper, imgEl, txtEl, workerId, labelText, fallbackLetter) {
    if (!wrapper || !imgEl || !txtEl) return;

    const id = Number(workerId || 0);
    if (!id) {
      wrapper.classList.add('is-no-photo');
      wrapper.classList.remove('has-photo');
      imgEl.src = '';
      txtEl.textContent = fallbackLetter || 'T';
      return;
    }

    txtEl.textContent = initials2(labelText, fallbackLetter || 'T');
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

    imgEl.src = `${workerPhotoUrl(id)}?ts=${Date.now()}`;

    if (imgEl.complete) {
      if (imgEl.naturalWidth > 0) imgEl.onload();
      else imgEl.onerror();
    }
  }

  function syncMiniAvatarsFromSelects() {
    const selResp = document.getElementById('vi_responsable_id');
    const selInt = document.getElementById('vi_integrador_id');

    const respWrap = document.getElementById('viRespAvatar');
    const integWrap = document.getElementById('viIntegAvatar');

    const respImg = respWrap?.querySelector('img');
    const respTxt = respWrap?.querySelector('.io-avatar__txt');

    const integImg = integWrap?.querySelector('img');
    const integTxt = integWrap?.querySelector('.io-avatar__txt');

    if (selResp && respWrap) {
      const id = Number(selResp.value || 0);
      const label = selResp.options?.[selResp.selectedIndex]?.textContent || '';
      setMiniAvatar(respWrap, respImg, respTxt, id, label, 'R');
    }

    if (selInt && integWrap) {
      const id = Number(selInt.value || 0);
      const label = selInt.options?.[selInt.selectedIndex]?.textContent || '';
      setMiniAvatar(integWrap, integImg, integTxt, id, label, 'I');
    }
  }

  function bindRespIntAvatarListeners() {
    const selResp = document.getElementById('vi_responsable_id');
    const selInt = document.getElementById('vi_integrador_id');

    if (selResp && selResp.dataset.boundAvatar !== '1') {
      selResp.dataset.boundAvatar = '1';
      selResp.addEventListener('change', syncMiniAvatarsFromSelects);
    }

    if (selInt && selInt.dataset.boundAvatar !== '1') {
      selInt.dataset.boundAvatar = '1';
      selInt.addEventListener('change', syncMiniAvatarsFromSelects);
    }
  }

  async function cargarYBindRespInt(idUsuario) {
    await ensureWorkers();

    const res = await fetch(`public/php/programas_vida_independiente/vi_programa_get.php?id_usuario=${encodeURIComponent(idUsuario)}`, {
      headers: { Accept: 'application/json' }
    });
    const json = await res.json();
    if (!json?.ok) throw new Error(json?.error || 'Error cargando programa');

    const programa = json.data || {};
    const idResp = String(programa.responsable || '');
    const idInt = String(programa.integrador || '');

    const selResp = document.getElementById('vi_responsable_id');
    const selInt = document.getElementById('vi_integrador_id');

    const listResp = workers.filter(w => [PUESTO_RESPONSABLE, PUESTO_ADMIN].includes(w.id_puesto));
    const listInt = workers.filter(w => w.id_puesto === PUESTO_TRABAJADOR);

    fillSelect(selResp, idResp, listResp);
    fillSelect(selInt, idInt, listInt);

    // sincronizar mini avatares al cargar / refrescar programa
    bindRespIntAvatarListeners();
    syncMiniAvatarsFromSelects();
  }

  function bindGuardarRespInt() {
    const btn = document.getElementById('vi_btn_save_respint');
    if (!btn || btn.dataset.bound === '1') return;
    btn.dataset.bound = '1';

    btn.addEventListener('click', async () => {
      const statusEl = document.getElementById('vi_respint_status');
      const idUsuario = Number(document.getElementById('ioModalUserId')?.value || 0);
      const responsable = Number(document.getElementById('vi_responsable_id')?.value || 0);
      const integrador = Number(document.getElementById('vi_integrador_id')?.value || 0);

      if (!idUsuario) return;
      if (!responsable || !integrador) {
        statusEl.textContent = 'Responsable e integrador son obligatorios.';
        return;
      }

      statusEl.textContent = 'Guardando…';
      try {
        const r = await fetch('public/php/programas_vida_independiente/vi_save.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
          body: JSON.stringify({ id_usuario: idUsuario, responsable, integrador })
        });
        const j = await r.json();
        if (!j?.ok) throw new Error(j?.error || 'Error');
        statusEl.textContent = 'Guardado ✅';
        setTimeout(() => statusEl.textContent = '', 1200);
      } catch (e) {
        console.error(e);
        statusEl.textContent = 'Error guardando.';
      }
    });
  }

  // -------------------------
  // REGISTROS
  // -------------------------
  function setActive(btns, activeBtn) {
    btns.forEach(b => b.classList.toggle('is-active', b === activeBtn));
  }

  function renderRegistros(container, registros) {
    if (!container) return;

    const header = `
      <div class="is-registros__header">
        <div>Destacado</div>
        <div>Comentario</div>
        <div class="is-registros__fecha">Fecha</div>
      </div>
    `;

    if (!Array.isArray(registros) || registros.length === 0) {
      container.innerHTML = header + `<div class="io-muted" style="padding:10px 2px;">Sin registros</div>`;
      return;
    }

    container.innerHTML = header + registros.map(r => {
      const id = Number(r.id || 0);
      const fecha = String(r.Fecha || '');
      const comentario = String(r.Comentario || '');
      const destacado = Number(r.Destacado || 0);

      return `
        <div class="is-registros__row" data-registro-id="${id}" data-destacado="${destacado}">
          <button type="button" class="is-star ${destacado ? 'is-on' : ''}" title="Marcar destacado">
            ${destacado ? '★' : '☆'}
          </button>
          <div class="is-registros__comentario">${escapeHtml(comentario)}</div>
          <div class="is-registros__fecha">${escapeHtml(fecha)}</div>
        </div>
      `;
    }).join('');
  }

  async function cargarRegistrosVI(idUsuario, ambito = 'BF', categoria = 'OBJ') {
    const statusEl = document.getElementById('isRegistrosStatus');
    const list = document.getElementById('isRegistrosList');
    if (list) list.innerHTML = `<div class="empty">Cargando…</div>`;
    if (statusEl) statusEl.textContent = '';

    try {
      const r = await fetch(
        `public/php/programas_vida_independiente/vi_registros_get.php?id_usuario=${encodeURIComponent(idUsuario)}&ambito=${encodeURIComponent(ambito)}&categoria=${encodeURIComponent(categoria)}`,
        { headers: { Accept: 'application/json' } }
      );
      const j = await r.json();
      if (!j?.ok) throw new Error(j?.error || 'Error');
      renderRegistros(list, j.data || []);
    } catch (e) {
      console.error(e);
      if (statusEl) statusEl.textContent = 'Error cargando registros.';
      if (list) list.innerHTML = `<div class="io-muted">Error cargando.</div>`;
    }
  }

  function bindFavoritos() {
    const list = document.getElementById('isRegistrosList');
    if (!list || list.dataset.bound === '1') return;
    list.dataset.bound = '1';

    list.addEventListener('click', async (e) => {
      const btn = e.target.closest('.is-star');
      if (!btn) return;
      e.preventDefault(); e.stopPropagation();

      const row = btn.closest('.is-registros__row');
      if (!row) return;

      const id = Number(row.dataset.registroId || 0);
      const idUsuario = Number(document.getElementById('ioModalUserId')?.value || 0);
      if (!id || !idUsuario) return;

      const statusEl = document.getElementById('isRegistrosStatus');
      const current = Number(row.dataset.destacado || 0);
      const next = current ? 0 : 1;

      btn.disabled = true;
      row.dataset.destacado = String(next);
      btn.classList.toggle('is-on', next === 1);
      btn.textContent = next ? '★' : '☆';

      try {
        const r = await fetch('public/php/programas_vida_independiente/vi_registros_destacado.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
          body: JSON.stringify({ id, id_usuario: idUsuario, destacado: next })
        });
        const j = await r.json();
        if (!j?.ok) throw new Error(j?.error || 'Error');
      } catch (err) {
        row.dataset.destacado = String(current);
        btn.classList.toggle('is-on', current === 1);
        btn.textContent = current ? '★' : '☆';
        if (statusEl) statusEl.textContent = err?.message || 'Error destacado';
      } finally {
        btn.disabled = false;
      }
    });
  }

  function bindRegistrosUI() {
    // evita doble binding si este script se reevalúa por cualquier motivo
    const scope = document.getElementById('ioModal');
    if (scope?.dataset?.viBound === '1') return;
    if (scope) scope.dataset.viBound = '1';

    const ambitoHidden = document.getElementById('is_ambito');
    const categoriaHidden = document.getElementById('is_categoria');

    const ambitoBtns = [...document.querySelectorAll('#ioModal [data-is-ambito]')];
    const catBtns = [...document.querySelectorAll('#ioModal [data-is-categoria]')];

    ambitoBtns.forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault(); e.stopPropagation();
        setActive(ambitoBtns, btn);

        const ambito = btn.dataset.isAmbito || 'BF';
        const ambitoHidden = document.getElementById('is_ambito');
        const categoriaHidden = document.getElementById('is_categoria');
        if (ambitoHidden) ambitoHidden.value = ambito;

        const idUsuario = Number(document.getElementById('ioModalUserId')?.value || 0);
        if (!idUsuario) return;

        // ✅ Si es RI, mostramos Resp/Int y ocultamos categorías+registros
        if (ambito === 'RI') {
          setRespIntMode(true);

          // Cargar selects (solo la primera vez o siempre, como prefieras)
          await cargarYBindRespInt(idUsuario);

          return;
        }

        // ✅ ámbito normal: volvemos a modo registros
        setRespIntMode(false);

        const categoria = categoriaHidden?.value || 'OBJ';
        await cargarRegistrosVI(idUsuario, ambito, categoria);
      });
    });


    catBtns.forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault(); e.stopPropagation();
        setActive(catBtns, btn);
        if (categoriaHidden) categoriaHidden.value = btn.dataset.isCategoria;

        const idUsuario = Number(document.getElementById('ioModalUserId')?.value || 0);
        if (!idUsuario) return;
        await cargarRegistrosVI(idUsuario, ambitoHidden.value, categoriaHidden.value);
      });
    });

    document.getElementById('is_btn_nuevo_registro')?.addEventListener('click', (e) => {
      e.preventDefault(); e.stopPropagation();
      openNuevoComentario();
    });

    bindFavoritos();
  }

  // Exponer para el modal de comentario
  window.__VI = window.__VI || {};
  window.__VI.cargarRegistrosVI = cargarRegistrosVI;

  // -------------------------
  // MINI AÑADIR USUARIO
  // -------------------------
  function openMini(el) {
    if (!el) return;
    el.classList.add('is-open');
    el.setAttribute('aria-hidden', 'false');
  }
  function closeMini(el) {
    if (!el) return;
    el.classList.remove('is-open');
    el.setAttribute('aria-hidden', 'true');
  }

  [miniAdd].forEach(el => {
    if (!el) return;
    if (el.parentElement !== document.body) document.body.appendChild(el);
    el.addEventListener('click', (e) => {
      const closeEl = e.target.closest('[data-close]');
      if (!closeEl) return;
      e.preventDefault(); e.stopPropagation();
      closeMini(el);
    });
  });

  function renderResultadosDisponibles(rows, q = '') {
    if (!resBuscar) return;
    const list = Array.isArray(rows) ? rows : [];
    if (!list.length) {
      resBuscar.innerHTML = `<div class="io-muted">${q ? 'Sin resultados.' : 'No hay usuarios disponibles.'}</div>`;
      return;
    }
    const hint = !q
      ? `<div class="io-muted" style="margin-bottom:8px;">Mostrando los primeros resultados. Usa el buscador para filtrar.</div>`
      : '';
    resBuscar.innerHTML = hint + list.map(u => `
      <button class="viv-mini__res-item" type="button" data-id="${u.id}">
        <div class="viv-mini__res-name">${escapeHtml(u.Nombre)} ${escapeHtml(u.Apellidos)}</div>
        <div class="viv-mini__res-sub">${escapeHtml(u.Dni || '')}</div>
      </button>
    `).join('');
  }

  async function cargarDisponibles(q = '') {
    if (!resBuscar) return;
    resBuscar.innerHTML = `<div class="io-muted">Cargando…</div>`;
    try {
      const r = await fetch(`public/php/programas_vida_independiente/vi_buscar.php?q=${encodeURIComponent(q)}&limit=80`, { headers: { Accept: 'application/json' } });
      const j = await r.json();
      if (!j?.ok) throw new Error(j?.error || 'Error');
      renderResultadosDisponibles(j.data || [], q);
    } catch (e) {
      console.error(e);
      resBuscar.innerHTML = `<div class="io-muted">Error cargando.</div>`;
    }
  }

  btnNew?.addEventListener('click', async () => {
    openMini(miniAdd);
    if (inBuscar) { inBuscar.value = ''; inBuscar.focus(); }
    await cargarDisponibles('');
  });

  let t = null;
  inBuscar?.addEventListener('input', () => {
    clearTimeout(t);
    t = setTimeout(() => cargarDisponibles(String(inBuscar.value || '').trim()), 250);
  });

  resBuscar?.addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-id]');
    if (!btn) return;
    const id_usuario = Number(btn.dataset.id || 0);
    if (!id_usuario) return;

    try {
      const r = await fetch('public/php/programas_vida_independiente/vi_add.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ id_usuario })
      });
      const j = await r.json();
      if (!j?.ok) throw new Error(j?.error || 'Error');

      closeMini(miniAdd);
      await cargarListado();
      await abrirModalVI(id_usuario);
    } catch (err) {
      console.error(err);
    }
  });

  // init
  cargarListado();
})();

// =========================
// MODAL “NUEVO COMENTARIO” (inline onclick)
// =========================
function getAmbitoLabel(code) {
  const map = {
    BF: 'Bienestar físico', BE: 'Bienestar emocional', BM: 'Bienestar material', RS: 'Relaciones sociales',
    AD: 'Autodeterminación', DP: 'Desarrollo personal', IS: 'Integración social', DR: 'Derechos'
  };
  return map[code] || code;
}
function getCategoriaLabel(code) {
  const map = { OBJ: 'Objetivos', ACC: 'Acciones', IND: 'Indicadores' };
  return map[code] || code;
}

function openNuevoComentario() {
  const modal = document.getElementById('modalNuevoComentario');
  if (!modal) return;
  modal.classList.add('is-open');
  modal.setAttribute('aria-hidden', 'false');

  const ambito = document.getElementById('is_ambito')?.value || 'BF';
  const categoria = document.getElementById('is_categoria')?.value || 'OBJ';

  document.getElementById('modal_ambito').textContent = `Ámbito: ${getAmbitoLabel(ambito)}`;
  document.getElementById('modal_categoria').textContent = `Categoría: ${getCategoriaLabel(categoria)}`;

  modal.dataset.ambito = ambito;
  modal.dataset.categoria = categoria;

  const ta = document.getElementById('modal_comentario');
  if (ta) { ta.value = ''; ta.focus(); }
}

function closeNuevoComentario() {
  const modal = document.getElementById('modalNuevoComentario');
  if (!modal) return;
  modal.classList.remove('is-open');
  modal.setAttribute('aria-hidden', 'true');
}

async function guardarNuevoComentario() {
  const comentario = (document.getElementById('modal_comentario')?.value || '').trim();
  if (!comentario) { alert('Por favor, escribe un comentario.'); return; }

  const modal = document.getElementById('modalNuevoComentario');
  const ambito = modal?.dataset?.ambito || document.getElementById('is_ambito')?.value || 'BF';
  const categoria = modal?.dataset?.categoria || document.getElementById('is_categoria')?.value || 'OBJ';

  const idUsuario = Number(document.getElementById('ioModalUserId')?.value || 0);
  if (!idUsuario) return;

  const status = document.getElementById('isRegistrosStatus');
  if (status) status.textContent = 'Guardando…';

  try {
    const r = await fetch('public/php/programas_vida_independiente/vi_registros_save.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ id_usuario: idUsuario, comentario, ambito, categoria })
    });
    const j = await r.json();
    if (!j?.ok) {
      if (status) status.textContent = j?.error || 'Error guardando';
      return;
    }

    if (status) status.textContent = 'Guardado ✅';
    closeNuevoComentario();

    await window.__VI?.cargarRegistrosVI?.(idUsuario, ambito, categoria);
    setTimeout(() => { if (status) status.textContent = ''; }, 1200);
  } catch (e) {
    console.error(e);
    if (status) status.textContent = 'No se pudo conectar.';
  }
}
