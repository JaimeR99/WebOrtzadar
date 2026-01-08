(() => {
  const $ = (sel, root = document) => root.querySelector(sel);

  // --- DOM ---
  const gridEl = $('#trabajadoresGrid');
  const statusEl = $('#trabajadoresStatus');
  const emptyEl = $('#trabajadoresEmpty');
  const btnNuevo = $('#btnNuevoTrabajador');
  const fTrab = $('#fTrab');
  const fAcceso = $('#fAcceso');

  // --- Modal base ---
  const modal = document.querySelector('.io-modal[data-program="trabajadores"]');
  if (!modal) return;
  const modalId = modal.id;
  const modalStatus = document.getElementById(`${modalId}_status`);
  const modalTitle = document.getElementById(`${modalId}_title`);
  const modalSub = document.getElementById(`${modalId}_sub`);
  const btnGuardarBase = document.getElementById(`${modalId}_btnGuardar`);

  // --- Campos (dentro del modal) ---
  const hidId = $('#t_id', modal);
  const inNombre = $('#t_nombre', modal);
  const inApellidos = $('#t_apellidos', modal);
  const selSexo = $('#t_sexo', modal);
  const inDni = $('#t_dni', modal);
  const inFechaNac = $('#t_fecha_nacimiento', modal);
  const inDireccion = $('#t_direccion', modal);
  const inTelefono = $('#t_telefono', modal);
  const inEmail = $('#t_email', modal);
  const inCuenta = $('#t_cuenta_corriente', modal);
  const selPuesto = $('#t_id_puesto', modal);

  const inFechaAlta = $('#t_fecha_alta', modal);

  // tabs
  const tabBar = modal.querySelector('[data-trab-subtabsbar]');
  const tabButtons = modal.querySelectorAll(
  '.io-subtabs[data-tab-scope="trabajadores"] .io-tab'
);
  const tabPanelsWrap = modal.querySelector('[data-tab-scope-panels="trabajadores"]');


  // foto
  const inFoto = $('#t_foto', modal);
  const avatarPrev = $('#trabajadorAvatarPreview', modal);
  const avatarPrevImg = avatarPrev?.querySelector('img');
  const avatarPrevInits = $('#trabajadorAvatarInitials', modal);

  // formación
  const fNombre = $('#f_nombre', modal);
  const fFecha = $('#f_fecha', modal);
  const fInstitucion = $('#f_institucion', modal);
  const fValoracion = $('#f_valoracion', modal);
  const btnAddFormacion = $('#btnAddFormacion', modal);
  const formacionList = $('#formacionList', modal);

  // acceso
  const chkAcceso = $('#a_enabled', modal);
  const accesoPanel = $('#accesoPanel', modal);
  const selLandpage = $('#a_landpage', modal);
  const inPass = $('#a_pass', modal);
  const btnGuardarAcceso = $('#btnGuardarAcceso', modal);
  const btnEliminarAcceso = $('#btnEliminarAcceso', modal);

  const BASE = '/webOrtzadar/public/';

  const API = {
    list: `${BASE}php/trabajadores/trabajadores_listar.php`,
    get: `${BASE}php/trabajadores/trabajadores_get.php`,
    save: `${BASE}php/trabajadores/trabajadores_save.php`,
    puestos: `${BASE}php/trabajadores/puestos_listar.php`,

    formSave: `${BASE}php/trabajadores/trabajadores_formacion_save.php`,
    formDelete: `${BASE}php/trabajadores/trabajadores_formacion_delete.php`,

    accessSave: `${BASE}php/trabajadores/trabajadores_acceso_save.php`,
    accessDelete: `${BASE}php/trabajadores/trabajadores_acceso_delete.php`,
    photoUpload: `${BASE}php/trabajadores/trabajadores_foto_upload.php`,
  };

  // --- State ---
  let rows = [];
  let current = null; // trabajador cargado
  let puestos = [];

  // --- Helpers UI ---
  const setStatus = (msg = '', type = '') => {
    if (!modalStatus) return;
    modalStatus.textContent = msg;
    modalStatus.className = 'io-status' + (type ? ` is-${type}` : '');
  };

  const setAccesoUI = (enabled) => {
    if (!chkAcceso || !accesoPanel) return;
    chkAcceso.checked = !!enabled;
    accesoPanel.classList.toggle('is-hidden', !enabled);
    btnGuardarAcceso.style.display = enabled ? '' : 'none';
  };
  const setActiveTab = (tabId) => {
    tabButtons.forEach(btn => {
      const active = btn.dataset.tab === tabId;
      btn.classList.toggle('is-active', active);
      btn.setAttribute('aria-selected', active ? 'true' : 'false');
      btn.tabIndex = active ? 0 : -1;
    });

    if (!tabPanelsWrap) return;
    tabPanelsWrap.querySelectorAll('.trab-tabpanel').forEach(p => {
      const active = p.id === tabId;
      p.classList.toggle('is-active', active);
      if (active) p.removeAttribute('hidden');
      else p.setAttribute('hidden', '');
    });
  };

  const lockBodyScroll = (lock) => { document.body.style.overflow = lock ? 'hidden' : ''; };

  const openModal = () => {
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    lockBodyScroll(true);
    (inNombre || modal)?.focus?.();
  };

  const closeModal = () => {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    lockBodyScroll(false);
    setStatus('');
  };

  const esc = (s) => String(s ?? '').replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
  const initials = (nombreCompleto) => {
    const parts = String(nombreCompleto || '').trim().split(/\s+/).filter(Boolean);
    const a = (parts[0] || '').slice(0, 1);
    const b = (parts[1] || '').slice(0, 1);
    return (a + b).toUpperCase() || 'TS';
  };

  const photoUrl = (id) => `/webOrtzadar/uploads/trabajadores/${id}.jpg`;

  const bindAvatar = (wrapEl, imgEl, initialsEl, id, fullName) => {
    if (!wrapEl || !imgEl) return;
    const inits = initials(fullName);
    if (initialsEl) initialsEl.textContent = inits;

    wrapEl.classList.add('is-no-photo');
    wrapEl.classList.remove('has-photo');

    imgEl.src = `${photoUrl(id)}?ts=${Date.now()}`;

    imgEl.onload = () => {
      wrapEl.classList.remove('is-no-photo');
      wrapEl.classList.add('has-photo');
    };
    imgEl.onerror = () => {
      wrapEl.classList.add('is-no-photo');
      wrapEl.classList.remove('has-photo');
    };

    if (imgEl.complete && imgEl.naturalWidth > 0) {
      wrapEl.classList.remove('is-no-photo');
      wrapEl.classList.add('has-photo');
    }
  };

  const fetchJSON = async (url, opts = {}) => {
    const res = await fetch(url, opts);
    const data = await res.json().catch(() => null);
    if (!data || data.ok !== true) {
      const msg = data?.error || `Error HTTP ${res.status}`;
      throw new Error(msg);
    }
    return data.data;
  };

  // --- Puestos ---
  const renderPuestosSelect = () => {
    if (!selPuesto) return;
    const currentVal = selPuesto.value;
    selPuesto.innerHTML = `<option value="">—</option>` + puestos.map(p =>
      `<option value="${p.id}">${esc(p.nombre)} (nivel ${p.nivel})</option>`
    ).join('');
    selPuesto.value = currentVal || '';
  };

  const loadPuestos = async () => {
    puestos = await fetchJSON(API.puestos);
    renderPuestosSelect();
  };

  // --- Formación ---
  const renderFormacion = (list = []) => {
    if (!formacionList) return;
    if (!list.length) {
      formacionList.innerHTML = `<div class="io-muted">Sin formaciones registradas.</div>`;
      return;
    }

    formacionList.innerHTML = list.map(f => {
      const sub = [
        f.fecha ? `Fecha: ${esc(f.fecha)}` : null,
        f.institucion ? `Inst.: ${esc(f.institucion)}` : null,
        (f.valoracion !== null && f.valoracion !== undefined) ? `Val.: ${esc(f.valoracion)}` : null
      ].filter(Boolean).join(' · ');

      return `
        <div class="trab-form-item" data-form-id="${f.id}">
          <div class="trab-form-item__meta">
            <div class="trab-form-item__title">${esc(f.nombre_formacion || '—')}</div>
            <div class="trab-form-item__sub">${esc(sub || '—')}</div>
          </div>
          <div class="trab-form-item__actions">
            <button class="btn btn-ghost" type="button" data-form-del="1"><i class="fa-regular fa-trash-can"></i> Borrar</button>
          </div>
        </div>
      `;
    }).join('');
  };

  const clearFormacionInputs = () => {
    if (fNombre) fNombre.value = '';
    if (fFecha) fFecha.value = '';
    if (fInstitucion) fInstitucion.value = '';
    if (fValoracion) fValoracion.value = '';
  };

  const addFormacion = async () => {
    const id_trabajador = Number(hidId.value || 0);
    if (!id_trabajador) {
      setStatus('Guarda primero el trabajador.', 'warn');
      return;
    }

    const payload = {
      id: 0,
      id_trabajador,
      nombre_formacion: (fNombre?.value || '').trim(),
      fecha: (fFecha?.value || '').trim(),
      institucion: (fInstitucion?.value || '').trim(),
      valoracion: (fValoracion?.value || '').trim(),
    };

    if (!payload.nombre_formacion) {
      setStatus('Indica el nombre de la formación.', 'warn');
      return;
    }

    setStatus('Añadiendo formación…');
    await fetchJSON(API.formSave, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });

    // recargar trabajador para ver lista actualizada
    const t = await fetchJSON(`${API.get}?id=${encodeURIComponent(id_trabajador)}`);
    current = t;
    renderFormacion(t.formacion || []);
    clearFormacionInputs();
    setStatus('Formación añadida.', 'ok');
  };

  const deleteFormacion = async (formId) => {
    const id_trabajador = Number(hidId.value || 0);
    if (!id_trabajador || !formId) return;
    if (!confirm('¿Borrar esta formación?')) return;

    setStatus('Borrando formación…');
    await fetchJSON(API.formDelete, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: formId, id_trabajador }),
    });

    const t = await fetchJSON(`${API.get}?id=${encodeURIComponent(id_trabajador)}`);
    current = t;
    renderFormacion(t.formacion || []);
    setStatus('Formación borrada.', 'ok');
  };

  // --- Listado ---
  const render = () => {
    const q = (fTrab?.value || '').trim().toLowerCase();
    const acceso = fAcceso?.value ?? '';

    const filtered = rows.filter(r => {
      const hayAcceso = r.tiene_acceso ? '1' : '0';
      if (acceso !== '' && hayAcceso !== acceso) return false;
      if (!q) return true;

      const hay = `${r.nombre} ${r.apellidos} ${r.email || ''} ${r.telefono || ''} ${r.acceso_correo || ''} ${r.dni || ''} ${r.puesto_nombre || ''}`
        .toLowerCase();
      return hay.includes(q);
    });

    if (!filtered.length) {
      gridEl.style.display = 'none';
      statusEl.style.display = 'none';
      emptyEl.style.display = '';
      return;
    }

    emptyEl.style.display = 'none';
    statusEl.style.display = 'none';
    gridEl.style.display = '';

    gridEl.innerHTML = filtered.map(r => {
      const full = `${r.nombre} ${r.apellidos}`.trim();
      const badge = r.tiene_acceso
        ? `<span class="badge badge--ok"><i class="fa-solid fa-key"></i> Con acceso</span>`
        : `<span class="badge badge--warn"><i class="fa-regular fa-circle-xmark"></i> Sin acceso</span>`;

      const sub = r.puesto_nombre
        ? `${esc(r.puesto_nombre)} · ${esc(r.acceso_correo || r.email || '—')}`
        : (r.acceso_correo ? esc(r.acceso_correo) : (r.email ? esc(r.email) : '—'));

      return `
        <article class="trabajador-card" data-id="${r.id}">
          <div class="trabajador-card__top">
            <div class="trabajador-avatar is-no-photo" aria-hidden="true" data-avatar-id="${r.id}">
              <img src="${photoUrl(r.id)}" alt="">
              <span class="trabajador-initials">${initials(full)}</span>
            </div>

            <div>
              <div class="trabajador-name">${esc(full || 'Sin nombre')}</div>
              <div class="trabajador-sub">${sub}</div>
            </div>
          </div>

          <div class="trabajador-meta">
            <div class="meta-row"><i class="fa-solid fa-phone"></i><span>${esc(r.telefono || '—')}</span></div>
            <div class="meta-row"><i class="fa-solid fa-id-card"></i><span>${esc(r.dni || '—')}</span></div>
          </div>

          <div class="trabajador-badges">${badge}</div>
        </article>
      `;
    }).join('');

    gridEl.querySelectorAll('.trabajador-avatar[data-avatar-id]').forEach(av => {
      const img = av.querySelector('img');
      const id = Number(av.dataset.avatarId || 0);
      if (!id || !img) return;

      img.addEventListener('load', () => {
        av.classList.remove('is-no-photo');
        av.classList.add('has-photo');
      }, { once: true });

      img.addEventListener('error', () => {
        av.classList.add('is-no-photo');
        av.classList.remove('has-photo');
      }, { once: true });

      if (img.complete && img.naturalWidth > 0) {
        av.classList.remove('is-no-photo');
        av.classList.add('has-photo');
      }
    });
  };

  const resetForm = () => {
    current = null;

    hidId.value = '';
    inNombre.value = '';
    inApellidos.value = '';
    inTelefono.value = '';
    inEmail.value = '';
    inFechaAlta.value = '';

    if (selSexo) selSexo.value = '';
    if (inDni) inDni.value = '';
    if (inFechaNac) inFechaNac.value = '';
    if (inDireccion) inDireccion.value = '';
    if (inCuenta) inCuenta.value = '';
    if (selPuesto) selPuesto.value = '';

    // formación
    clearFormacionInputs();
    renderFormacion([]);

    // acceso
    selLandpage.value = 'index.php?pagina=dashboard';
    inPass.value = '';

    modalTitle.textContent = 'Trabajador/a';
    modalSub.textContent = '—';
    setStatus('');

    if (inFoto) inFoto.value = '';
    if (avatarPrev && avatarPrevImg) {
      avatarPrev.classList.add('is-no-photo');
      avatarPrev.classList.remove('has-photo');
      avatarPrevImg.src = '';
    }
    if (avatarPrevInits) avatarPrevInits.textContent = 'TS';

    setAccesoUI(false);
    setActiveTab('tab_trabajador');
    

  };

  const fillForm = (t) => {
    current = t;

    hidId.value = t.id || '';
    inNombre.value = t.nombre || '';
    inApellidos.value = t.apellidos || '';
    inTelefono.value = t.telefono || '';
    inEmail.value = t.email || '';

    if (selSexo) selSexo.value = t.sexo || '';
    if (inDni) inDni.value = t.dni || '';
    if (inFechaNac) inFechaNac.value = t.fecha_nacimiento || '';
    if (inDireccion) inDireccion.value = t.direccion || '';
    if (inCuenta) inCuenta.value = t.cuenta_corriente || '';
    if (selPuesto) selPuesto.value = t.id_puesto ? String(t.id_puesto) : '';

    // datetime-local
    if (t.fecha_alta) {
      const v = String(t.fecha_alta).replace(' ', 'T').slice(0, 16);
      inFechaAlta.value = v;
    } else {
      inFechaAlta.value = '';
    }

    // formación
    renderFormacion(t.formacion || []);

    // acceso
    const hasAccess = !!t.acceso?.correo;
    setAccesoUI(hasAccess);
    selLandpage.value = t.acceso?.landpage || 'index.php?pagina=dashboard';
    inPass.value = '';

    const full = `${t.nombre || ''} ${t.apellidos || ''}`.trim();
    modalTitle.textContent = full || 'Trabajador/a';
    modalSub.textContent = (t.puesto_nombre ? `${t.puesto_nombre}` : '—') + (hasAccess ? ' · Con acceso' : ' · Sin acceso');

    if (inFoto) inFoto.value = '';
    if (t.id && avatarPrev && avatarPrevImg) {
      bindAvatar(avatarPrev, avatarPrevImg, avatarPrevInits, Number(t.id), full);
    }
  };

  const loadList = async () => {
    statusEl.textContent = 'Cargando…';
    statusEl.style.display = '';
    gridEl.style.display = 'none';
    emptyEl.style.display = 'none';

    try {
      rows = await fetchJSON(API.list);
      render();
    } catch (e) {
      statusEl.textContent = String(e.message || e);
      statusEl.style.display = '';
      gridEl.style.display = 'none';
      emptyEl.style.display = 'none';
    }
  };

  const openForNew = () => {
    resetForm();
    modalSub.textContent = 'Nuevo trabajador';
    openModal();
  };

  const openForEdit = async (id) => {
    resetForm();
    setStatus('Cargando…');
    openModal();
    setActiveTab('tab_trabajador');

    try {
      const t = await fetchJSON(`${API.get}?id=${encodeURIComponent(id)}`);
      fillForm(t);
      setStatus('');
    } catch (e) {
      setStatus(String(e.message || e), 'error');
    }
  };

  const uploadFotoIfAny = async (id) => {
    const file = inFoto?.files?.[0];
    if (!file) return;

    const fd = new FormData();
    fd.append('id_trabajador', String(id));
    fd.append('foto', file);

    const res = await fetch(API.photoUpload, { method: 'POST', body: fd });
    const j = await res.json().catch(() => null);
    if (!j || j.ok !== true) throw new Error(j?.error || `Error subiendo foto (HTTP ${res.status})`);
  };

  const saveTrabajador = async () => {
    const payload = {
      id: Number(hidId.value || 0),
      nombre: inNombre.value.trim(),
      apellidos: inApellidos.value.trim(),
      telefono: inTelefono.value.trim(),
      email: inEmail.value.trim(),

      sexo: selSexo?.value || '',
      dni: inDni?.value.trim() || '',
      fecha_nacimiento: inFechaNac?.value || '',
      direccion: inDireccion?.value.trim() || '',
      cuenta_corriente: inCuenta?.value.trim() || '',
      id_puesto: selPuesto?.value || '',
    };

    if (!payload.nombre && !payload.apellidos) {
      setStatus('Nombre o apellidos son obligatorios.', 'warn');
      return;
    }

    setStatus('Guardando trabajador…');
    try {
      const data = await fetchJSON(API.save, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      const finalId = Number(data?.id || 0) || Number(payload.id || 0);

      // subir foto si hay
      if (finalId) await uploadFotoIfAny(finalId);

      await loadList();
      if (finalId) await openForEdit(finalId);
      setStatus('Trabajador guardado.', 'ok');
    } catch (e) {
      setStatus(String(e.message || e), 'error');
    }
  };

  // --- Acceso (igual que el tuyo) ---
  const saveAcceso = async () => {
    const id_trabajador = Number(hidId.value || 0);
    if (!id_trabajador) {
      setStatus('Guarda primero el trabajador.', 'warn');
      setActiveTab('tab_trabajador');

      return;
    }

    const payload = {
      id_trabajador,
      correo: inEmail.value.trim(),
      landpage: selLandpage.value,
      pass: inPass.value,
      nombre: inNombre.value.trim(),
      apellidos: inApellidos.value.trim(),
    };

    if (!payload.correo) {
      setStatus('Para dar acceso necesitas rellenar el Email del trabajador.', 'warn');
      return;
    }

    setStatus('Guardando acceso…');
    try {
      await fetchJSON(API.accessSave, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      await loadList();
      await openForEdit(id_trabajador);
      setStatus('Acceso guardado.', 'ok');
    } catch (e) {
      setStatus(String(e.message || e), 'error');
    }
  };

  const deleteAcceso = async () => {
    const id_trabajador = Number(hidId.value || 0);
    if (!id_trabajador) return;
    if (!confirm('¿Eliminar el acceso de este trabajador/a?')) return;

    setStatus('Eliminando acceso…');
    try {
      await fetchJSON(API.accessDelete, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_trabajador }),
      });

      await loadList();
      await openForEdit(id_trabajador);
      setStatus('Acceso eliminado.', 'ok');
    } catch (e) {
      setStatus(String(e.message || e), 'error');
    }
  };

  // --- Events ---
  btnNuevo?.addEventListener('click', openForNew);
  fTrab?.addEventListener('input', () => render());
  fAcceso?.addEventListener('change', () => render());

  // formación
  btnAddFormacion?.addEventListener('click', addFormacion);
  formacionList?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-form-del="1"]');
    if (!btn) return;
    const item = e.target.closest('[data-form-id]');
    const formId = Number(item?.dataset?.formId || 0);
    if (formId) deleteFormacion(formId);
  });

  // foto
  inFoto?.addEventListener('change', async () => {
    const file = inFoto.files?.[0];
    if (!file || !avatarPrev || !avatarPrevImg) return;

    // preview inmediato
    const url = URL.createObjectURL(file);
    avatarPrevImg.onload = () => URL.revokeObjectURL(url);
    avatarPrevImg.src = url;
    avatarPrev.classList.remove('is-no-photo');
    avatarPrev.classList.add('has-photo');

    const id = Number(hidId.value || 0);
    if (!id) {
      setStatus('Guarda primero el trabajador para poder subir la foto.', 'warn');
      return;
    }

    try {
      setStatus('Subiendo foto…');
      await uploadFotoIfAny(id);

      const full = `${inNombre.value.trim()} ${inApellidos.value.trim()}`.trim();
      bindAvatar(avatarPrev, avatarPrevImg, avatarPrevInits, id, full);

      await loadList();
      setStatus('Foto actualizada.', 'ok');
    } catch (e) {
      setStatus(String(e.message || e), 'error');
    }
  });

  chkAcceso?.addEventListener('change', async () => {
    const id_trabajador = Number(hidId.value || 0);
    const hadAccess = !!current?.acceso?.correo;

    if (chkAcceso.checked) {
      setAccesoUI(true);
      if (!inEmail.value.trim()) setStatus('Rellena el Email del trabajador (se usará como usuario).', 'warn');
      return;
    }

    if (!hadAccess) {
      setAccesoUI(false);
      return;
    }

    if (!confirm('¿Quitar el acceso a este trabajador/a?')) {
      setAccesoUI(true);
      return;
    }

    if (!id_trabajador) {
      setAccesoUI(false);
      return;
    }

    try {
      setStatus('Eliminando acceso…');
      await fetchJSON(API.accessDelete, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_trabajador }),
      });

      await loadList();
      await openForEdit(id_trabajador);
      setStatus('Acceso eliminado.', 'ok');
    } catch (e) {
      setStatus(String(e.message || e), 'error');
      setAccesoUI(true);
    }
  });

  avatarPrev?.addEventListener('click', () => inFoto?.click());
  avatarPrev?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      inFoto?.click();
    }
  });
  tabBar?.addEventListener('click', (e) => {
    const btn = e.target.closest('.io-tab[data-tab]');
    if (!btn) return;
    setActiveTab(btn.dataset.tab);
  });

  gridEl?.addEventListener('click', (e) => {
    const card = e.target.closest('.trabajador-card');
    if (!card) return;
    const id = Number(card.dataset.id || 0);
    if (id) openForEdit(id);
  });

  // cerrar modal
  modal.addEventListener('click', (e) => {
    const close = e.target.closest('[data-close="1"]');
    if (close) closeModal();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') closeModal();
  });

  // guardar (botón del modal base)
  btnGuardarBase?.addEventListener('click', saveTrabajador);
  btnGuardarAcceso?.addEventListener('click', saveAcceso);
  btnEliminarAcceso?.addEventListener('click', deleteAcceso);

  // Init
  (async () => {
    try {
      await loadPuestos();
    } catch (e) {
      // si falla puestos, no rompemos todo
      console.warn('Error cargando puestos:', e);
    }
    await loadList();
  })();
})();
