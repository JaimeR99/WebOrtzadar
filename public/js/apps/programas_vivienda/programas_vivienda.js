// public/js/apps/programas_vivienda/programas_vivienda.js
// Vivienda: Participantes + Dinámicas + Alimentación (menú/incidencias) + Revisiones + Equipamientos + Datos
(() => {
  const $ = (s, r = document) => r.querySelector(s);

  const listEl = $('#viviendaList');

  const modal = document.querySelector('.io-modal[data-program="vivienda"]');
  if (!listEl || !modal) return;

  const modalId = modal.id;
  const statusModal = document.getElementById(`${modalId}_status`);
  const titleEl = document.getElementById(`${modalId}_title`);
  const subEl = document.getElementById(`${modalId}_sub`);
  const btnGuardar = document.getElementById(`${modalId}_btnGuardar`);

  // -------- Modal fields
  const hidId = $('#viv_id', modal);

  // Datos
  const inDireccion = $('#viv_direccion', modal);
  const inLocalidad = $('#viv_localidad', modal);
  const selResp = $('#viv_responsable_programa', modal);
  const selIntegrador = $('#viv_integrador', modal);

  // Avatares (si existen)
  const respAvatar = document.getElementById('vivRespAvatar');
  const intAvatar = document.getElementById('vivIntAvatar');

  // Foto vivienda + fechas (si existen en el HTML)
  const vivAvatar = document.getElementById('vivAvatar');
  const vivFotoInput = document.getElementById('viv_foto');
  const vivCreatedEl = document.getElementById('viv_created_at');
  const vivUpdatedEl = document.getElementById('viv_updated_at');

  // Participantes
  const pStatus = $('#viv_participantes_status', modal);
  const pGrid = $('#viv_participantes_grid', modal);
  const btnAddPart = $('#viv_btn_add_part', modal);
  const pBuscarInline = document.getElementById('viv_part_buscar');


  // Dinámicas
  const dynStatus = $('#viv_dinamicas_status', modal);
  const dynList = $('#viv_dinamicas_list', modal);
  const btnAddDyn = $('#viv_btn_add_dinamica', modal);

  // Alimentación: Menú semanal
  const menuStatus = $('#viv_menu_status', modal);
  const menuList = $('#viv_menu_list', modal);
  const btnAddMenu = $('#viv_btn_add_menu', modal);

  // Alimentación: Incidencias
  const incStatus = $('#viv_inc_status', modal);
  const incList = $('#viv_inc_list', modal);
  const btnAddInc = $('#viv_btn_add_incidencia', modal);

  // Equipamientos
  const eqWrapSalto = $('#viv_eq_salto_wrap', modal);
  const eqChecks = $('#viv_eq_checks', modal);

  // Revisiones
  const btnAddRevision = $('#viv_btn_add_revision', modal);
  const revTable = $('#viv_rev_table', modal);

  // -------- Mini modal participantes
  const miniPart = document.getElementById('vivMiniModal');
  const inBuscar = document.getElementById('viv_buscar');
  const resBuscar = document.getElementById('viv_buscar_res');

  // -------- Mini modal dinámicas
  const miniDyn = document.getElementById('vivMiniDinamica');
  const dynStars = document.getElementById('viv_dyn_stars');
  const dynVal = document.getElementById('viv_dyn_valoracion');
  const dynComentario = document.getElementById('viv_dyn_comentario');
  const dynGuardar = document.getElementById('viv_dyn_guardar');
  const dynMiniStatus = document.getElementById('viv_dyn_status');

  // -------- Mini modal menú
  const miniMenu = document.getElementById('vivMiniMenu');
  // OJO: ya no usamos "responsable" input
  const menuL = document.getElementById('viv_menu_lunes');
  const menuMa = document.getElementById('viv_menu_martes');
  const menuMi = document.getElementById('viv_menu_miercoles');
  const menuJ = document.getElementById('viv_menu_jueves');
  const menuV = document.getElementById('viv_menu_viernes');
  const menuS = document.getElementById('viv_menu_sabado');
  const menuD = document.getElementById('viv_menu_domingo');
  const menuGuardar = document.getElementById('viv_menu_guardar');
  const menuMiniStatus = document.getElementById('viv_menu_mini_status');

  // -------- Mini modal incidencia
  const miniInc = document.getElementById('vivMiniIncidencia');
  // OJO: ya no usamos "responsable" input
  const incText = document.getElementById('viv_inc_text');
  const incGuardar = document.getElementById('viv_inc_guardar');
  const incMiniStatus = document.getElementById('viv_inc_mini_status');

  // -------- Mini modal revisión
  const miniRev = document.getElementById('vivMiniRevision');
  const revEq = document.getElementById('viv_rev_equipo');
  const revFecha = document.getElementById('viv_rev_fecha');
  const revProx = document.getElementById('viv_rev_proxima');
  const revAvisar = document.getElementById('viv_rev_avisar');
  const revDias = document.getElementById('viv_rev_dias');
  const revObs = document.getElementById('viv_rev_obs');
  const revGuardar = document.getElementById('viv_rev_guardar');
  const revStatus = document.getElementById('viv_rev_status');

  // -------- Mini modal nueva vivienda
  const miniNewViv = document.getElementById('vivMiniNuevaVivienda');
  const newResp = document.getElementById('viv_new_responsable');
  const newInt = document.getElementById('viv_new_integrador');
  const newFotoInput = document.getElementById('viv_new_foto');
  const newRespAvatar = document.getElementById("vivNewRespAvatar");
  const newIntAvatar  = document.getElementById("vivNewIntAvatar");


  // -------- State
  let cacheViviendas = [];
  let current = null;
  let cacheParticipantes = [];

  // Trabajadores (para selects Responsable / Integrador)
  let cacheTrabajadores = null;
  let loadingTrabajadores = null;

  // -------- Helpers
  function status(msg) {
    listEl.innerHTML = `<div class="empty">${escapeHtml(msg || '')}</div>`;
  }
  function mstatus(msg) { if (statusModal) statusModal.textContent = msg || ''; }

  function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, (m) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[m]));
  }


  // ===== Vivienda: foto + iniciales (como Ocio/Vacaciones/Formaciones)
  function viviendaPhotoUrl(id){
    return `/webOrtzadar/uploads/viviendas/${encodeURIComponent(id)}.jpg`;
  }

  function viviendaInitials(nombre){
    const s = String(nombre || '').trim();
    if (!s) return 'V';
    const parts = s.split(/\s+/).filter(Boolean);
    const a = parts[0]?.[0] || 'V';
    const b = parts[1]?.[0] || '';
    return (a + b).toUpperCase();
  }

  function hydrateViviendaAvatars(){
    listEl.querySelectorAll('[data-viv-avatar]').forEach((wrap) => {
      const img = wrap.querySelector('img');
      const fb = wrap.querySelector('.viv-avatar__fallback');
      if (!img) return;

      // reset estado
      wrap.classList.add('is-no-photo');
      wrap.classList.remove('has-photo');
      if (fb) fb.style.display = '';

      img.onload = () => {
        wrap.classList.remove('is-no-photo');
        wrap.classList.add('has-photo');
        if (fb) fb.style.display = 'none';
      };
      img.onerror = () => {
        wrap.classList.add('is-no-photo');
        wrap.classList.remove('has-photo');
        if (fb) fb.style.display = '';
      };
    });
  }

  function fmtDateTime(mysqlDt) {
    if (!mysqlDt) return '';
    return String(mysqlDt).replace('T', ' ');
  }

  function openModal() {
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
  }

  function closeModal() {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
  }

  function ensureMiniMounted(el) {
    if (!el) return;
    // Si el mini modal vive dentro de un .io-modal cerrado, nunca se verá (display:none heredado).
    const hostModal = el.closest('.io-modal');
    const hostClosed = hostModal && !hostModal.classList.contains('is-open');

    if (hostClosed) {
      if (!el.__origParent) {
        el.__origParent = el.parentNode;
        el.__origNext = el.nextSibling;
      }
      if (el.parentNode !== document.body) document.body.appendChild(el);
    }
  }

  function restoreMiniMount(el) {
    if (!el || !el.__origParent) return;
    if (el.parentNode === document.body) {
      if (el.__origNext && el.__origNext.parentNode === el.__origParent) {
        el.__origParent.insertBefore(el, el.__origNext);
      } else {
        el.__origParent.appendChild(el);
      }
    }
  }

  function openMini(el) {
    if (!el) return;
    ensureMiniMounted(el);
    el.classList.add('is-open');
    el.setAttribute('aria-hidden', 'false');
  }

  function closeMini(el) {
    if (!el) return;
    el.classList.remove('is-open');
    el.setAttribute('aria-hidden', 'true');
    restoreMiniMount(el);
  }

  // Activa un subtab en el modal (Datos por defecto)
  function activateSubTab(paneId) {
    const tablist = modal.querySelector('.io-subtabs[data-tab-scope="vivienda"]');
    if (!tablist) return;

    tablist.querySelectorAll('.io-tab').forEach(t => {
      const on = (t.dataset.tab === paneId);
      t.classList.toggle('is-active', on);
      t.setAttribute('aria-selected', on ? 'true' : 'false');
      t.setAttribute('tabindex', on ? '0' : '-1');
    });

    modal.querySelectorAll(`.io-pane[role="tabpanel"][data-tab-scope="vivienda"]`).forEach(p => {
      const on = (p.id === paneId);
      p.classList.toggle('is-active', on);
      p.setAttribute('aria-hidden', on ? 'false' : 'true');
      if (on) p.removeAttribute('hidden');
      else p.setAttribute('hidden', 'hidden');
    });
  }

  // ===== Avatares Responsable/Integrador
  function trabajadorFotoUrl(id) {
    return `/webOrtzadar/uploads/trabajadores/${encodeURIComponent(id)}.jpg`;
  }

  function setAvatar(avatarEl, id, fallbackLetter = 'R') {
    if (!avatarEl) return;
    const img = avatarEl.querySelector('img');
    const fallback = avatarEl.querySelector('.io-avatar__txt');

    if (fallback) fallback.textContent = fallbackLetter;

    if (!id) {
      avatarEl.classList.add('is-no-photo');
      if (img) {
        img.removeAttribute('src');
        img.style.display = 'none';
      }
      if (fallback) fallback.style.display = '';
      return;
    }

    const url = trabajadorFotoUrl(id);
    avatarEl.classList.remove('is-no-photo');
    if (img) {
      img.style.display = '';
      img.src = url;
      img.onerror = () => {
        avatarEl.classList.add('is-no-photo');
        if (fallback) fallback.style.display = '';
        img.style.display = 'none';
      };
    }

    if (fallback) fallback.style.display = 'none';
  }

  // ===== Trabajadores (para selects Responsable / Integrador)
  const INTEGRADOR_MAX_NIVEL = 10; // en tu BBDD actual: "Trabajador" (nivel 10)

  function labelTrabajador(t) {
    const n = String(t?.nombre || '').trim();
    const a = String(t?.apellidos || '').trim();
    return (n + ' ' + a).trim() || `Trabajador ${t?.id ?? ''}`;
  }

  function isIntegrador(t) {
    if (t?.puesto_nivel == null) return false;
    return Number(t.puesto_nivel) <= INTEGRADOR_MAX_NIVEL;
  }

  function getIntegradoresList() {
    return (cacheTrabajadores || []).filter(isIntegrador);
  }

  function fillTrabajadoresSelect(selectEl, selectedValue = '', list = null) {
    if (!selectEl) return;

    const data = Array.isArray(list) ? list : (cacheTrabajadores || []);
    const placeholder =
      selectEl.querySelector('option[value=""]')?.outerHTML ||
      '<option value="">— Selecciona —</option>';

    const opts = data.map(t => {
      const id = String(t.id);
      const sel = String(selectedValue) === id ? ' selected' : '';
      return `<option value="${id}"${sel}>${escapeHtml(labelTrabajador(t))}</option>`;
    }).join('');

    selectEl.innerHTML = placeholder + opts;

    // Si el seleccionado actual NO está en la lista (por filtro), lo mantenemos visible para no “perderlo”
    const selVal = String(selectedValue || '');
    if (selVal && !data.some(t => String(t.id) === selVal)) {
      const cur = (cacheTrabajadores || []).find(t => String(t.id) === selVal);
      if (cur) {
        const warn = `⚠ No válido: ${labelTrabajador(cur)} (${cur.puesto_nombre || 'Sin puesto'})`;
        selectEl.insertAdjacentHTML(
          'beforeend',
          `<option value="${selVal}" selected>${escapeHtml(warn)}</option>`
        );
      }
    }
  }

  async function ensureTrabajadoresLoaded() {
    if (cacheTrabajadores) return cacheTrabajadores;
    if (loadingTrabajadores) return loadingTrabajadores;

    loadingTrabajadores = (async () => {
      const res = await fetch('public/php/trabajadores/trabajadores_listar.php', {
        headers: { 'Accept': 'application/json' }
      });
      const json = await res.json();
      if (!json?.ok) throw new Error(json?.error || 'Error cargando trabajadores');

      cacheTrabajadores = (json.data || []).map(t => ({
        id: Number(t.id),
        nombre: t.nombre,
        apellidos: t.apellidos,
        id_puesto: t.id_puesto != null ? Number(t.id_puesto) : null,
        puesto_nombre: t.puesto_nombre || null,
        puesto_nivel: t.puesto_nivel != null ? Number(t.puesto_nivel) : null,
      }));

      // Responsable: TODOS
      fillTrabajadoresSelect(selResp, selResp?.value || '', cacheTrabajadores);
      fillTrabajadoresSelect(newResp, newResp?.value || '', cacheTrabajadores);

      // Integrador: FILTRADOS por puesto
      fillTrabajadoresSelect(selIntegrador, selIntegrador?.value || '', getIntegradoresList());
      fillTrabajadoresSelect(newInt, newInt?.value || '', getIntegradoresList());

      // Avatares por si ya hay selección
      setAvatar(respAvatar, selResp?.value, 'R');
      setAvatar(intAvatar, selIntegrador?.value, 'I');
      setAvatar(newRespAvatar, newResp?.value, 'R');
      setAvatar(newIntAvatar, newInt?.value, 'I');


      return cacheTrabajadores;
    })();

    return loadingTrabajadores;
  }

  // ===== Foto vivienda
  function viviendaFotoUrl(id) {
    return `/webOrtzadar/uploads/viviendas/${encodeURIComponent(id)}.jpg`;
  }

  function setViviendaFoto(id, bustCache = false) {
    if (!vivAvatar) return;
    const img = vivAvatar.querySelector('img');
    const fallback = vivAvatar.querySelector('.ocio-avatar__fallback');

    if (fallback && !fallback.textContent) fallback.textContent = 'VI';

    if (!id || !img) {
      vivAvatar.classList.add('is-no-photo');
      if (img) img.src = '';
      if (fallback) fallback.style.display = '';
      return;
    }

    const url = viviendaFotoUrl(id) + (bustCache ? `?v=${Date.now()}` : '');
    vivAvatar.classList.remove('is-no-photo');
    img.style.display = '';
    img.src = url;
    img.onerror = () => {
      vivAvatar.classList.add('is-no-photo');
      if (fallback) fallback.style.display = '';
      img.style.display = 'none';
    };
  }

  function bindViviendaFotoUpload() {
    if (!vivAvatar || !vivFotoInput) return;

    const trigger = () => vivFotoInput.click();

    vivAvatar.addEventListener('click', trigger);
    vivAvatar.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); trigger(); }
    });

    vivFotoInput.addEventListener('change', async () => {
      const id = Number(hidId?.value || 0);
      const file = vivFotoInput.files?.[0];
      if (!id || !file) return;

      mstatus('Subiendo foto…');

      try {
        const fd = new FormData();
        fd.append('id_vivienda', String(id));
        fd.append('foto', file);

        const res = await fetch('public/php/programas_vivienda/vivienda_foto_upload.php', {
          method: 'POST',
          body: fd
        });
        const json = await res.json();
        if (!json?.ok) throw new Error(json?.error || 'Error');

        setViviendaFoto(id, true);
        mstatus('Foto actualizada ✅');
        setTimeout(() => mstatus(''), 1200);
      } catch (err) {
        console.error(err);
        mstatus('Error subiendo foto');
      } finally {
        vivFotoInput.value = '';
      }
    });
  }

  // Bind una vez
  bindViviendaFotoUpload();

  selResp?.addEventListener('change', () => setAvatar(respAvatar, selResp.value, 'R'));
  selIntegrador?.addEventListener('change', () => setAvatar(intAvatar, selIntegrador.value, 'I'));
  newResp?.addEventListener('change', () => setAvatar(newRespAvatar, newResp.value, 'R'));
  newInt?.addEventListener('change', () => setAvatar(newIntAvatar, newInt.value, 'I'));


  // ===== DEBUG/INIT =====
  console.log('[VIVIENDA] JS cargado OK');

  // Delegación: click en botón "+ Nueva vivienda" => abre SOLO mini modal crear
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('#viv_btn_new');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    console.log('[VIVIENDA] CLICK + Nueva vivienda');

    if (!miniNewViv) {
      console.error('[VIVIENDA] ERROR: no existe #vivMiniNuevaVivienda');
      return;
    }

    // Asegura lista de trabajadores para los desplegables
    try { await ensureTrabajadoresLoaded(); }
    catch (err) {
      console.error(err);
      const st = document.getElementById('viv_new_status');
      st && (st.textContent = 'No se pudieron cargar trabajadores.');
    }

    // Limpieza rápida
    const st = document.getElementById('viv_new_status');
    st && (st.textContent = '');
    ['viv_new_nombre', 'viv_new_direccion', 'viv_new_localidad'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.value = '';
    });
    if (newResp) newResp.value = '';
    if (newInt) newInt.value = '';
    if (newFotoInput) newFotoInput.value = '';

    openMini(miniNewViv);
  });

  // Cerrar mini modal nueva vivienda (overlay/x/cancel con [data-close])
  document.addEventListener('click', (e) => {
    const mini = e.target.closest('#vivMiniNuevaVivienda');
    if (!mini) return;

    const closeEl = e.target.closest('[data-close]');
    if (!closeEl) return;

    e.preventDefault();
    e.stopPropagation();
    closeMini(mini);
  });

  // Crear vivienda (mini modal nueva vivienda)
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('#viv_new_guardar');
    if (!btn) return;

    console.log('[VIVIENDA] CLICK Crear vivienda');

    const nombre = String(document.getElementById('viv_new_nombre')?.value || '').trim();
    const direccion = String(document.getElementById('viv_new_direccion')?.value || '').trim();
    const localidad = String(document.getElementById('viv_new_localidad')?.value || '').trim();
    const responsable_programa = Number(document.getElementById('viv_new_responsable')?.value || 0);
    const integrador = Number(document.getElementById('viv_new_integrador')?.value || 0);
    const st = document.getElementById('viv_new_status');

    if (!nombre || !direccion || !localidad || !responsable_programa || !integrador) {
      st && (st.textContent = 'Rellena todos los campos obligatorios (*)');
      return;
    }

    st && (st.textContent = 'Creando…');

    try {
      const res = await fetch('public/php/programas_vivienda/vivienda_add.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ nombre, direccion, localidad, responsable_programa, integrador })
      });

      const txt = await res.text();
      let json;
      try { json = JSON.parse(txt); }
      catch {
        console.error('[VIVIENDA] PHP no devolvió JSON:', txt.slice(0, 400));
        st && (st.textContent = 'Error backend (mira consola).');
        return;
      }

      if (!json?.ok) throw new Error(json?.error || 'Error');

      const newId = Number(json?.data?.id_nuevo || 0);
      const newFile = newFotoInput?.files?.[0];

      // Si viene foto en el mini modal, la subimos (opcional)
      if (newId && newFile) {
        try {
          const fd = new FormData();
          fd.append('id_vivienda', String(newId));
          fd.append('foto', newFile);

          const up = await fetch('public/php/programas_vivienda/vivienda_foto_upload.php', {
            method: 'POST',
            body: fd
          });
          const upj = await up.json();
          if (!upj?.ok) throw new Error(upj?.error || 'Error subiendo foto');
        } catch (err) {
          console.error('[VIVIENDA] Foto vivienda (crear) - error:', err);
        } finally {
          if (newFotoInput) newFotoInput.value = '';
        }
      }

      closeMini(miniNewViv);
      st && (st.textContent = '');

      await cargarViviendas();
    } catch (err) {
      console.error('[VIVIENDA] ERROR crear vivienda', err);
      st && (st.textContent = err?.message || 'No se pudo crear.');
    }
  });

  // Close modal (base)
  modal.addEventListener('click', (e) => {
    if (e.target?.dataset?.close) closeModal();
  });

  // Close with ESC
  document.addEventListener('keydown', (e) => {
    if (!modal.classList.contains('is-open')) return;
    if (e.key === 'Escape') { e.preventDefault(); closeModal(); }
  });

  // Tabs (subtabs)
  modal.addEventListener('click', (e) => {
    const tab = e.target.closest('.io-tab');
    if (!tab) return;
    const tablist = tab.closest('[data-tab-scope]');
    if (!tablist) return;

    const scope = tablist.dataset.tabScope; // "vivienda"
    const paneId = tab.dataset.tab;

    tablist.querySelectorAll('.io-tab').forEach(t => {
      const on = (t === tab);
      t.classList.toggle('is-active', on);
      t.setAttribute('aria-selected', on ? 'true' : 'false');
      t.setAttribute('tabindex', on ? '0' : '-1');
    });

    modal.querySelectorAll(`.io-pane[role="tabpanel"][data-tab-scope="${scope}"]`).forEach(p => {
      const on = (p.id === paneId);
      p.classList.toggle('is-active', on);
      p.setAttribute('aria-hidden', on ? 'false' : 'true');
      if (on) p.removeAttribute('hidden');
      else p.setAttribute('hidden', 'hidden');
    });
  });

  // Close mini-modals by overlay / X
  [miniPart, miniDyn, miniMenu, miniInc, miniRev, miniNewViv].forEach(el => {
    el?.addEventListener('click', (e) => {
      const closeEl = e.target.closest('[data-close]');
      if (!closeEl) return;

      e.preventDefault();
      e.stopPropagation();
      closeMini(el);
    });
  });

  // -------- Viviendas list
  async function cargarViviendas() {
    status('Cargando…');
    try {
      const res = await fetch('public/php/programas_vivienda/vivienda_listar.php', {
        headers: { 'Accept': 'application/json' }
      });
      const json = await res.json();
      if (!json?.ok) throw new Error(json?.error || 'Error');

      cacheViviendas = json.data || [];
      if (!cacheViviendas.length) {
        status('No hay viviendas.');
        return;
      }

      listEl.innerHTML = cacheViviendas.map(v => {
        const id = Number(v.id || 0);
        const nombre = v.nombre || 'Vivienda';
        const localidad = v.localidad || '—';
        const direccion = v.direccion || '—';
        const responsable = v.responsable_nombre || '—';
        const integrador = v.integrador_nombre || '—';

        return `
          <div class="io-person" data-id="${escapeHtml(id)}" role="button" tabindex="0">
            <div class="io-avatar io-avatar--photo">
              <img
                class="io-avatar__img"
                src="${escapeHtml(viviendaPhotoUrl(id))}?v=${Date.now()}"
                alt=""
                loading="lazy"
                onerror="this.style.display='none'; this.parentElement.querySelector('.io-avatar__txt').style.display='grid';"
              >
              <span class="io-avatar__txt" style="display:none;">${escapeHtml(viviendaInitials(nombre))}</span>
            </div>

            <div class="io-person__main">
              <div class="io-person__title">${escapeHtml(nombre)}</div>

              <div class="io-person__meta">
                <span>Localidad: ${escapeHtml(localidad)}</span>
                <span>Dirección: ${escapeHtml(direccion)}</span>
                <span class="io-muted">Responsable: ${escapeHtml(responsable)}</span>
              </div>

              <div class="io-person__footer">
                <span class="io-muted">Integrador: ${escapeHtml(integrador)}</span>
              </div>
            </div>

            <div class="io-person__actions">
              <button class="btn btn-secondary" type="button" data-open="${escapeHtml(id)}">Abrir →</button>
            </div>
          </div>
        `;
      }).join('');
    } catch (e) {
      console.error(e);
      status('Error cargando viviendas.');
    }
  }

  listEl.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-open]');
    const card = e.target.closest('.io-person');
    const id = Number(btn?.dataset?.open || card?.dataset?.id || 0);
    if (id) abrirVivienda(id);
  });

  // -------- Equipamientos
  function setEquipamientos(eq) {
    if (!eqChecks) return;
    const map = {
      Caldera: 'caldera',
      Gas: 'gas',
      Extintores: 'extintores',
      Contratos: 'contratos',
      CEI: 'cei',
      Salto: 'salto'
    };
    eqChecks.querySelectorAll('input[type="checkbox"][data-eq]').forEach(ch => {
      const k = map[ch.dataset.eq];
      ch.checked = !!(eq && eq[k]);
    });
  }

  function getEquipamientos() {
    const map = {
      Caldera: 'caldera',
      Gas: 'gas',
      Extintores: 'extintores',
      Contratos: 'contratos',
      CEI: 'cei',
      Salto: 'salto'
    };
    const eq = {};
    eqChecks?.querySelectorAll('input[type="checkbox"][data-eq]').forEach(ch => {
      const k = map[ch.dataset.eq];
      eq[k] = !!ch.checked;
    });
    return eq;
  }

  // -------- Revisiones
  function renderRevisiones(rows) {
    if (!revTable) return;
    const list = Array.isArray(rows) ? rows : [];

    if (!list.length) {
      revTable.innerHTML = `<div class="io-muted">No hay revisiones.</div>`;
      return;
    }

    revTable.innerHTML = `
      <div class="viv-rev__row viv-rev__row--head">
        <div>Equipo</div>
        <div>Fecha</div>
        <div>Próxima</div>
        <div>Avisar</div>
        <div>Comentario</div>
      </div>
      ${list.map(r => `
        <div class="viv-rev__row">
          <div>${escapeHtml(r.equipamiento)}</div>
          <div>${escapeHtml(r.fecha_revision || '')}</div>
          <div>${escapeHtml(r.proxima_revision || '')}</div>
          <div>${Number(r.avisar) ? 'Sí' : 'No'}</div>
          <div class="viv-rev__obs">${escapeHtml(r.observaciones || '')}</div>
        </div>
      `).join('')}
    `;
  }

  async function cargarRevisiones(idVivienda) {
    if (!revTable) return;
    try {
      const res = await fetch(`public/php/programas_vivienda/vivienda_revisiones_listar.php?id_vivienda=${encodeURIComponent(idVivienda)}`, {
        headers: { 'Accept': 'application/json' }
      });
      const json = await res.json();
      if (!json?.ok) throw new Error(json?.error || 'Error');
      renderRevisiones(json.data || []);
    } catch (e) {
      console.error(e);
      revTable.innerHTML = `<div class="io-muted">No se pudieron cargar revisiones.</div>`;
    }
  }

  btnAddRevision?.addEventListener('click', () => {
    revStatus && (revStatus.textContent = '');
    if (revEq) revEq.value = '';
    if (revFecha) revFecha.value = '';
    if (revProx) revProx.value = '';
    if (revAvisar) revAvisar.value = '0';
    if (revDias) revDias.value = '15';
    if (revObs) revObs.value = '';
    openMini(miniRev);
  });

  revGuardar?.addEventListener('click', async () => {
    const idVivienda = Number(hidId?.value || 0);
    const equipamiento = String(revEq?.value || '');
    const fecha_revision = String(revFecha?.value || '');
    const proxima_revision = String(revProx?.value || '');
    const avisar = Number(revAvisar?.value || 0) ? 1 : 0;
    const dias_antes_aviso = Number(revDias?.value || 15);
    const observaciones = String(revObs?.value || '').trim();

    if (!idVivienda) return;
    if (!equipamiento) { revStatus && (revStatus.textContent = 'Selecciona equipamiento.'); return; }
    if (!fecha_revision) { revStatus && (revStatus.textContent = 'Indica la fecha de revisión.'); return; }

    revStatus && (revStatus.textContent = 'Guardando…');

    try {
      const res = await fetch('public/php/programas_vivienda/vivienda_revision_add.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
          id_vivienda: idVivienda,
          equipamiento,
          fecha_revision,
          proxima_revision,
          observaciones,
          avisar,
          dias_antes_aviso
        })
      });
      const json = await res.json();
      if (!json?.ok) throw new Error(json?.error || 'Error');

      await cargarRevisiones(idVivienda);
      closeMini(miniRev);
    } catch (e) {
      console.error(e);
      revStatus && (revStatus.textContent = 'No se pudo guardar.');
    }
  });


  // -------- Participantes
  function userPhotoUrl(id) {
    return `/webOrtzadar/uploads/usuarios/${encodeURIComponent(id)}.jpg`;
  }

  function initials(nombre, apellidos) {
    const a = (String(nombre || '').trim()[0] || 'U').toUpperCase();
    const b = (String(apellidos || '').trim()[0] || '').toUpperCase();
    return (a + b).trim() || 'U';
  }

  function fullName(u){
    const n = `${u?.Nombre || ''} ${u?.Apellidos || ''}`.trim();
    return n || `Usuario #${u?.id ?? ''}`;
  }

  let partFilterQuery = '';

  function matchParticipante(u, q){
    const query = String(q || '').trim().toLowerCase();
    if (!query) return true;
    const hay = `${fullName(u)} ${u?.Dni || ''}`.toLowerCase();
    return hay.includes(query);
  }

  function renderParticipantes() {
    if (!pGrid) return;

    const all = Array.isArray(cacheParticipantes) ? cacheParticipantes : [];
    const q = String(partFilterQuery || '').trim();
    const view = q ? all.filter(u => matchParticipante(u, q)) : all;

    const pHeader = $('#viv_part_header', modal);

    // Header 1:1 con Ocio
    if (pHeader) {
      if (!all.length) {
        pHeader.innerHTML = '';
      } else {
        pHeader.innerHTML = `
          <div class="viv-section-title">
            <div class="viv-section-title__left">
              <span class="viv-section-title__dot"></span>
              <div>
                <div class="viv-section-title__text">Participantes del grupo</div>
                <div class="viv-section-title__sub">Click en la foto para quitar</div>
              </div>
            </div>
          </div>
        `;
      }
    }

    if (!all.length) {
      pStatus && (pStatus.textContent = 'Sin participantes.');
      pGrid.innerHTML = '';
      return;
    }

    // Estado del filtro
    if (pStatus) {
      if (q && view.length !== all.length) {
        pStatus.textContent = `Mostrando ${view.length} de ${all.length}`;
      } else if (q && !view.length) {
        pStatus.textContent = `Sin resultados para “${q}”`;
      } else {
        pStatus.textContent = '';
      }
    }

    if (!view.length) {
      pGrid.innerHTML = '';
      return;
    }

    pGrid.innerHTML = view.map(u => {
      const id = Number(u.id);
      const name = escapeHtml(fullName(u));
      const dni = escapeHtml(u.Dni || '—');
      const ini = escapeHtml(initials(u.Nombre, u.Apellidos));
      const img = `${userPhotoUrl(id)}?ts=${Date.now()}`;

      return `
        <div class="io-person" data-id="${id}">
          <button class="io-avatar is-clickable" type="button" data-del="${id}" title="Quitar">
            <img class="io-avatar__img" src="${img}" alt="${name}" loading="lazy"
              onerror="this.style.display='none'; this.parentNode.querySelector('.io-avatar__txt').style.display='grid';">
            <span class="io-avatar__txt" style="display:none;">${ini}</span>
            <span class="io-avatar__x" aria-hidden="true">×</span>
          </button>

          <div class="io-person__main">
            <div class="io-person__title">${name}</div>
            <div class="io-muted" style="font-size:12px;">${dni}</div>
          </div>
        </div>
      `;
    }).join('');
  }

  async function cargarParticipantes(idVivienda) {
    try {
      const res = await fetch(`public/php/programas_vivienda/vivienda_participantes_listar.php?id_vivienda=${encodeURIComponent(idVivienda)}`, {
        headers: { 'Accept': 'application/json' }
      });
      const json = await res.json();
      cacheParticipantes = json?.ok ? (json.data || []) : [];
    } catch (e) {
      console.error(e);
      cacheParticipantes = [];
    }
    renderParticipantes();
  }

  // ===== Catálogo participantes (mini modal: añadir)
  async function fetchCatalogParticipantes(idVivienda, q = '') {
    const url =
      `public/php/programas_vivienda/vivienda_participantes_catalogo.php` +
      `?id_vivienda=${encodeURIComponent(idVivienda)}` +
      `&q=${encodeURIComponent(q || '')}`;

    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const json = await res.json();
    if (!json?.ok) throw new Error(json?.error || 'Error catálogo participantes');
    return json.data || [];
  }

  function renderCatalogParticipantes(list) {
    if (!resBuscar) return;

    if (!list.length) {
      resBuscar.innerHTML = `<div class="io-muted">No hay usuarios para mostrar.</div>`;
      return;
    }

    resBuscar.innerHTML = list.map(u => {
      const full = `${u.Apellidos || ''} ${u.Nombre || ''}`.trim() || `Usuario #${u.id}`;
      const inOther = !!u.vivienda_actual_id;

      const badge = inOther
        ? `<span class="viv-mini__badge">En otra vivienda: ${escapeHtml(u.vivienda_actual_nombre || ('#' + u.vivienda_actual_id))}${u.vivienda_actual_localidad ? ' · ' + escapeHtml(u.vivienda_actual_localidad) : ''}</span>`
        : `<span class="viv-mini__badge viv-mini__badge--ok">Libre</span>`;

      const ini = escapeHtml(initials(u.Nombre, u.Apellidos));
      const img = `${userPhotoUrl(u.id)}?ts=${Date.now()}`;

      return `
        <button class="viv-mini__res-item ${inOther ? 'is-warn' : ''}"
                type="button"
                data-id="${u.id}"
                data-inother="${inOther ? '1' : '0'}"
                data-vname="${escapeHtml(u.vivienda_actual_nombre || '')}">
          <div class="viv-mini__res-left">
            <div class="trabajador-avatar" aria-hidden="true">
              <img src="${img}" alt="" loading="lazy"
                   onerror="this.style.display='none'; this.parentNode.querySelector('.io-avatar__txt').style.display='grid';">
              <span class="io-avatar__txt" style="display:none;">${ini}</span>
            </div>
            <div style="min-width:0;">
              <div class="viv-mini__res-name">${escapeHtml(full)}</div>
              <div class="viv-mini__res-sub">${escapeHtml(u.Dni || '')}</div>
            </div>
          </div>
          <div>${badge}</div>
        </button>
      `;
    }).join('');
  }

  async function addParticipanteNormal(idV, idU) {
    const res = await fetch('public/php/programas_vivienda/vivienda_participantes_add.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ id_vivienda: idV, id_usuario: idU })
    });
    const json = await res.json();
    if (!json?.ok) throw new Error(json?.error || 'Error al añadir participante');
    return json.data;
  }

  async function transferParticipante(idV, idU) {
    const res = await fetch('public/php/programas_vivienda/vivienda_participantes_transfer.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ id_vivienda: idV, id_usuario: idU })
    });
    const json = await res.json();
    if (!json?.ok) throw new Error(json?.error || 'Error al trasladar participante');
    return json.data;
  }

  async function refreshCatalogUI() {
    const idV = Number(hidId?.value || 0);
    if (!idV || !resBuscar) return;

    const q = String(inBuscar?.value || '').trim();
    resBuscar.innerHTML = `<div class="io-muted">Cargando usuarios…</div>`;
    try {
      const list = await fetchCatalogParticipantes(idV, q);
      renderCatalogParticipantes(list);
    } catch (e) {
      console.error(e);
      resBuscar.innerHTML = `<div class="io-muted">No se pudieron cargar usuarios.</div>`;
    }
  }

  // Filtrar participantes de la vivienda (solo frontend)
  let tPartFilter = null;
  pBuscarInline?.addEventListener('input', () => {
    clearTimeout(tPartFilter);
    tPartFilter = setTimeout(() => {
      partFilterQuery = String(pBuscarInline.value || '');
      renderParticipantes();
    }, 120);
  });

  // Botón abrir mini modal participantes => carga catálogo completo
  btnAddPart?.addEventListener('click', async () => {
    openMini(miniPart);
    if (inBuscar) { inBuscar.value = ''; inBuscar.focus(); }
    if (resBuscar) resBuscar.innerHTML = `<div class="io-muted">Cargando usuarios…</div>`;
    await refreshCatalogUI();
  });

  // Buscador mini modal: filtra el catálogo
  let tBuscar = null;
  inBuscar?.addEventListener('input', () => {
    clearTimeout(tBuscar);
    tBuscar = setTimeout(async () => {
      await refreshCatalogUI();
    }, 220);
  });

  // Click en resultado: añade o traslada con confirmación
  resBuscar?.addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-id]');
    if (!btn) return;

    const idUsuario = Number(btn.dataset.id || 0);
    const idVivienda = Number(hidId?.value || 0);
    if (!idVivienda || !idUsuario) return;

    const inOther = btn.dataset.inother === '1';
    const otherName = btn.dataset.vname || 'otra vivienda';

    try {
      if (inOther) {
        const ok = window.confirm(`Este usuario ya está en ${otherName}. ¿Quieres trasladarlo a esta vivienda?`);
        if (!ok) return;
        await transferParticipante(idVivienda, idUsuario);
      } else {
        await addParticipanteNormal(idVivienda, idUsuario);
      }

      // refrescar grid y catálogo (para que desaparezca del listado)
      await cargarParticipantes(idVivienda);
      await refreshCatalogUI();
    } catch (e2) {
      console.error(e2);
      alert('No se pudo completar la acción.');
    }
  });

  // Quitar participante (click en la foto)
  pGrid?.addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-del]');
    if (!btn) return;

    const idUsuario = Number(btn.dataset.del || 0);
    const idVivienda = Number(hidId?.value || 0);
    if (!idVivienda || !idUsuario) return;

    try {
      const res = await fetch('public/php/programas_vivienda/vivienda_participantes_del.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ id_vivienda: idVivienda, id_usuario: idUsuario })
      });
      const json = await res.json();
      if (!json?.ok) throw new Error(json?.error || 'Error');

      cacheParticipantes = cacheParticipantes.filter(x => Number(x.id) !== Number(idUsuario));
      renderParticipantes();

      // Si el mini modal está abierto, refrescamos catálogo para que vuelva a aparecer como candidato
      if (miniPart?.classList.contains('is-open')) await refreshCatalogUI();
    } catch (e3) {
      console.error(e3);
    }
  });

  // -------- Dinámicas
  function renderStars(n) {
    const v = Math.max(0, Math.min(5, Number(n) || 0));
    let out = '';
    for (let i = 1; i <= 5; i++) out += `<span class="viv-star ${i <= v ? 'is-on' : ''}" aria-hidden="true">★</span>`;
    return `<div class="viv-stars" aria-label="${v} de 5">${out}</div>`;
  }

  function renderDinamicas(rows) {
    if (!dynList) return;
    const list = Array.isArray(rows) ? rows : [];
    if (!list.length) {
      dynStatus && (dynStatus.textContent = 'No hay dinámicas registradas.');
      dynList.innerHTML = '';
      return;
    }
    dynStatus && (dynStatus.textContent = '');
    dynList.innerHTML = list.map(r => `
      <div class="viv-dyn-item" data-id="${r.id}">
        <div class="viv-dyn-item__head">
          <div class="viv-dyn-item__date">${escapeHtml(fmtDateTime(r.fecha))}</div>
          ${renderStars(r.valoracion)}
        </div>
        <div class="viv-dyn-item__comment">${escapeHtml(r.comentario || '')}</div>
      </div>
    `).join('');
  }

  async function cargarDinamicas(idVivienda) {
    dynStatus && (dynStatus.textContent = 'Cargando…');
    if (dynList) dynList.innerHTML = '';
    try {
      const res = await fetch(`public/php/programas_vivienda/vivienda_dinamicas_listar.php?id_vivienda=${encodeURIComponent(idVivienda)}`, {
        headers: { 'Accept': 'application/json' }
      });
      const json = await res.json();
      if (!json?.ok) throw new Error(json?.error || 'Error');
      renderDinamicas(json.data || []);
    } catch (e) {
      console.error(e);
      dynStatus && (dynStatus.textContent = 'No se pudieron cargar las dinámicas.');
    }
  }

  function setMiniStars(v) {
    const val = Math.max(0, Math.min(5, Number(v) || 0));
    if (dynVal) dynVal.value = String(val);
    dynStars?.querySelectorAll('.viv-star').forEach(btn => {
      const n = Number(btn.dataset.val || 0);
      btn.classList.toggle('is-on', n && n <= val);
    });
  }

  dynStars?.addEventListener('click', (e) => {
    const b = e.target.closest('button[data-val]');
    if (!b) return;
    setMiniStars(b.dataset.val);
  });

  btnAddDyn?.addEventListener('click', () => {
    dynMiniStatus && (dynMiniStatus.textContent = '');
    if (dynVal) dynVal.value = '0';
    if (dynComentario) dynComentario.value = '';
    setMiniStars(0);
    openMini(miniDyn);
  });

  dynGuardar?.addEventListener('click', async () => {
    const idVivienda = Number(hidId?.value || 0);
    const valoracion = Number(dynVal?.value || 0);
    const comentario = String(dynComentario?.value || '').trim();

    if (!idVivienda) return;
    if (valoracion < 1 || valoracion > 5) { dynMiniStatus && (dynMiniStatus.textContent = 'Selecciona una valoración (1–5).'); return; }
    if (!comentario) { dynMiniStatus && (dynMiniStatus.textContent = 'Escribe un comentario.'); return; }

    dynMiniStatus && (dynMiniStatus.textContent = 'Guardando…');
    try {
      const res = await fetch('public/php/programas_vivienda/vivienda_dinamicas_add.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ id_vivienda: idVivienda, valoracion, comentario })
      });
      const json = await res.json();
      if (!json?.ok) throw new Error(json?.error || 'Error');

      if (current) current.dinamicas_grupales = json?.data?.id_nuevo || current.dinamicas_grupales;
      await cargarDinamicas(idVivienda);
      closeMini(miniDyn);
    } catch (e) {
      console.error(e);
      dynMiniStatus && (dynMiniStatus.textContent = 'No se pudo guardar.');
    }
  });

  // -------- Menú semanal (histórico)
  function renderMenus(rows) {
    if (!menuList) return;
    const list = Array.isArray(rows) ? rows : [];
    if (!list.length) {
      menuStatus && (menuStatus.textContent = 'No hay menús registrados.');
      menuList.innerHTML = '';
      return;
    }
    menuStatus && (menuStatus.textContent = '');
    menuList.innerHTML = list.map(r => `
      <div class="viv-item">
        <div class="viv-item__head">
          <div class="viv-item__date">${escapeHtml(fmtDateTime(r.fecha))}</div>
          <div class="viv-item__meta">Responsable: <strong>${escapeHtml(r.responsable_nombre || r.responsable || '—')}</strong></div>
        </div>
        <div class="viv-menu-grid">
          <div><span>L</span> ${escapeHtml(r.lunes || '')}</div>
          <div><span>M</span> ${escapeHtml(r.martes || '')}</div>
          <div><span>X</span> ${escapeHtml(r.miercoles || '')}</div>
          <div><span>J</span> ${escapeHtml(r.jueves || '')}</div>
          <div><span>V</span> ${escapeHtml(r.viernes || '')}</div>
          <div><span>S</span> ${escapeHtml(r.sabado || '')}</div>
          <div><span>D</span> ${escapeHtml(r.domingo || '')}</div>
        </div>
      </div>
    `).join('');
  }

  async function cargarMenus(idVivienda) {
    menuStatus && (menuStatus.textContent = 'Cargando…');
    if (menuList) menuList.innerHTML = '';
    try {
      const res = await fetch(`public/php/programas_vivienda/vivienda_menu_listar.php?id_vivienda=${encodeURIComponent(idVivienda)}`, {
        headers: { 'Accept': 'application/json' }
      });
      const json = await res.json();
      if (!json?.ok) throw new Error(json?.error || 'Error');
      renderMenus(json.data || []);
    } catch (e) {
      console.error(e);
      menuStatus && (menuStatus.textContent = 'No se pudieron cargar los menús.');
    }
  }

  btnAddMenu?.addEventListener('click', () => {
    menuMiniStatus && (menuMiniStatus.textContent = '');
    [menuL, menuMa, menuMi, menuJ, menuV, menuS, menuD].forEach(i => { if (i) i.value = ''; });
    openMini(miniMenu);
    menuL?.focus();
  });

  menuGuardar?.addEventListener('click', async () => {
    const idVivienda = Number(hidId?.value || 0);
    if (!idVivienda) return;

    const payload = {
      id_vivienda: idVivienda,
      lunes: String(menuL?.value || '').trim(),
      martes: String(menuMa?.value || '').trim(),
      miercoles: String(menuMi?.value || '').trim(),
      jueves: String(menuJ?.value || '').trim(),
      viernes: String(menuV?.value || '').trim(),
      sabado: String(menuS?.value || '').trim(),
      domingo: String(menuD?.value || '').trim(),
    };

    menuMiniStatus && (menuMiniStatus.textContent = 'Guardando…');
    try {
      const response = await fetch('public/php/programas_vivienda/vivienda_menu_add.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload)
      });

      const txt = await response.text();
      let json = null;
      try { json = JSON.parse(txt); } catch { }

      if (!json?.ok) throw new Error(json?.error || txt || 'Error');

      await cargarMenus(idVivienda);
      closeMini(miniMenu);
    } catch (e) {
      console.error(e);
      menuMiniStatus && (menuMiniStatus.textContent = e?.message || 'No se pudo guardar.');
    }

  });

  // -------- Incidencias (histórico)
  function renderIncidencias(rows) {
    if (!incList) return;
    const list = Array.isArray(rows) ? rows : [];
    if (!list.length) {
      incStatus && (incStatus.textContent = 'No hay incidencias registradas.');
      incList.innerHTML = '';
      return;
    }
    incStatus && (incStatus.textContent = '');
    incList.innerHTML = list.map(r => `
      <div class="viv-item">
        <div class="viv-item__head">
          <div class="viv-item__date">${escapeHtml(fmtDateTime(r.fecha))}</div>
          <div class="viv-item__meta">Responsable: <strong>${escapeHtml(r.responsable_nombre || r.responsable || '—')}</strong></div>
        </div>
        <div class="viv-item__body">${escapeHtml(r.incidencia || '')}</div>
      </div>
    `).join('');
  }

  async function cargarIncidencias(idVivienda) {
    incStatus && (incStatus.textContent = 'Cargando…');
    if (incList) incList.innerHTML = '';
    try {
      const res = await fetch(`public/php/programas_vivienda/vivienda_incidencias_listar.php?id_vivienda=${encodeURIComponent(idVivienda)}`, {
        headers: { 'Accept': 'application/json' }
      });
      const json = await res.json();
      if (!json?.ok) throw new Error(json?.error || 'Error');
      renderIncidencias(json.data || []);
    } catch (e) {
      console.error(e);
      incStatus && (incStatus.textContent = 'No se pudieron cargar las incidencias.');
    }
  }

  btnAddInc?.addEventListener('click', () => {
    incMiniStatus && (incMiniStatus.textContent = '');
    if (incText) incText.value = '';
    openMini(miniInc);
    incText?.focus();
  });

  incGuardar?.addEventListener('click', async () => {
    const idVivienda = Number(hidId?.value || 0);
    const incidencia = String(incText?.value || '').trim();

    if (!idVivienda) return;
    if (!incidencia) { incMiniStatus && (incMiniStatus.textContent = 'Escribe una incidencia.'); return; }

    incMiniStatus && (incMiniStatus.textContent = 'Guardando…');
    try {
      const response = await fetch('public/php/programas_vivienda/vivienda_incidencias_add.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ id_vivienda: idVivienda, incidencia })
      });

      const txt = await response.text();
      let json = null;
      try { json = JSON.parse(txt); } catch { }

      if (!json?.ok) throw new Error(json?.error || txt || 'Error');

      await cargarIncidencias(idVivienda);
      closeMini(miniInc);
    } catch (e) {
      console.error(e);
      incMiniStatus && (incMiniStatus.textContent = e?.message || 'No se pudo guardar.');
    }

  });

  // -------- Abrir vivienda
  async function abrirVivienda(id) {
    mstatus('Cargando…');
    try {
      const v = cacheViviendas.find(x => Number(x.id) === Number(id));
      if (!v) throw new Error('No encontrada');
      current = v;

      // Cabecera modal
      titleEl.textContent = v.nombre || 'Vivienda';
      subEl.textContent = v.localidad || '—';
      hidId.value = v.id;

      // Datos tab por defecto
      activateSubTab('tab_viv_datos');
      if (pBuscarInline) pBuscarInline.value = '';
      partFilterQuery = '';
      renderParticipantes();


      // Foto vivienda
      setViviendaFoto(id, false);

      // Meta fechas
      if (vivCreatedEl) vivCreatedEl.textContent = '—';
      if (vivUpdatedEl) vivUpdatedEl.textContent = '—';

      // Limpieza equipamientos antes de pedir
      setEquipamientos({});

      // Trae datos completos
      let data = null;
      try {
        const r = await fetch(`public/php/programas_vivienda/vivienda_get.php?id=${encodeURIComponent(id)}`, {
          headers: { 'Accept': 'application/json' }
        });
        const j = await r.json();
        if (j?.ok) data = j.data || null;

        if (data?.equipamientos) setEquipamientos(data.equipamientos);

        if (vivCreatedEl) vivCreatedEl.textContent = fmtDateTime(data?.created_at) || '—';
        if (vivUpdatedEl) vivUpdatedEl.textContent = fmtDateTime(data?.updated_at) || '—';
      } catch (e) {
        console.error('Error vivienda_get', e);
      }

      if (eqWrapSalto) eqWrapSalto.hidden = false;

      // Campos base
      inDireccion.value = (data?.direccion ?? v.direccion ?? '');
      inLocalidad.value = (data?.localidad ?? v.localidad ?? '');

      // IDs reales
      const respId = (data?.responsable_programa ?? v.responsable_programa ?? '');
      const intId = (data?.integrador ?? v.integrador ?? '');

      await ensureTrabajadoresLoaded();

      fillTrabajadoresSelect(selResp, String(respId || ''), cacheTrabajadores);
      fillTrabajadoresSelect(selIntegrador, String(intId || ''), getIntegradoresList());

      if (selResp) selResp.value = String(respId || '');
      if (selIntegrador) selIntegrador.value = String(intId || '');

      setAvatar(respAvatar, selResp?.value, 'R');
      setAvatar(intAvatar, selIntegrador?.value, 'I');

      await Promise.all([
        cargarParticipantes(id),
        cargarDinamicas(id),
        cargarMenus(id),
        cargarIncidencias(id),
        cargarRevisiones(id),
      ]);

      mstatus('');
      openModal();
    } catch (e) {
      console.error(e);
      mstatus('Error cargando vivienda');
    }
  }

  // -------- Guardar vivienda
  btnGuardar?.addEventListener('click', async () => {
    const id = Number(hidId?.value || 0);
    if (!id) return;

    mstatus('Guardando…');

    const payload = {
      id,
      direccion: inDireccion?.value || '',
      localidad: inLocalidad?.value || '',
      responsable_programa: Number(selResp?.value || 0),
      integrador: Number(selIntegrador?.value || 0),
      equipamientos: getEquipamientos()
    };

    try {
      const res = await fetch('public/php/programas_vivienda/vivienda_save.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();
      if (!json?.ok) throw new Error(json?.error || 'Error');

      mstatus('Guardado ✅');
      await cargarViviendas();
      setTimeout(() => mstatus(''), 1200);
    } catch (e) {
      console.error(e);
      mstatus('Error guardando');
    }
  });

  // -------- Init
  cargarViviendas();
})();
