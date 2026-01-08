// public/js/apps/usuarios/usuarios.js
(() => {
  const grid = document.getElementById('usuariosGrid');
  const statusBox = document.getElementById('usuariosStatus');
  const emptyBox = document.getElementById('usuariosEmpty');

  const filtroNombre = document.getElementById('filtro_nombre');
  const filtroSexo = document.getElementById('filtro_sexo');
  const filtroDireccion = document.getElementById('filtro_direccion');
  const dlNombres = document.getElementById('dl_nombres');
  const dlDirecciones = document.getElementById('dl_direcciones');

  const modal = document.getElementById('usuarioModal');
  const modalClose = document.getElementById('usuarioModalClose');
  const modalTitulo = document.getElementById('usuarioModalTitulo');
  const modalSub = document.getElementById('usuarioModalSub');
  const modalAvatar = document.getElementById('modalAvatar');

  const panelPerfil = document.getElementById('tab-perfil');
  const panelContacto = document.getElementById('tab-contacto');
  const panelAdmin = document.getElementById('tab-admin');
  const panelDiag = document.getElementById('tab-diagnostico');

  const ENDPOINT_UPDATE = 'public/php/usuarios/actualizar_usuario.php';

  let users = [];
  let activeUser = null;

  // -----------------------------
  // Helpers
  // -----------------------------
  const cleanNullBytes = (v) => String(v ?? '').replace(/\u0000/g, '').trim();

  const safeText = (v) => {
    const s = cleanNullBytes(v);
    return s === '' ? '—' : s;
  };

  const isEmptyValue = (v) => {
    const s = cleanNullBytes(v);
    return s === '' || s.toLowerCase() === 'null';
  };

  const pick = (row, keys, def = '—') => {
    for (const k of keys) {
      if (row && Object.prototype.hasOwnProperty.call(row, k) && !isEmptyValue(row[k])) return row[k];
      const ku = String(k).toUpperCase();
      const kl = String(k).toLowerCase();
      if (row && Object.prototype.hasOwnProperty.call(row, ku) && !isEmptyValue(row[ku])) return row[ku];
      if (row && Object.prototype.hasOwnProperty.call(row, kl) && !isEmptyValue(row[kl])) return row[kl];
    }
    return def;
  };

  const escapeHtml = (str) =>
    String(str ?? '').replace(/[&<>"']/g, (m) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[m]));

  function fullNameFromRow(row) {
    const nombre = cleanNullBytes(pick(row, ['Nombre', 'nombre'], ''));
    const apellidos = cleanNullBytes(pick(row, ['Apellidos', 'apellidos'], ''));
    const full = (nombre + ' ' + apellidos).replace(/\s+/g, ' ').trim();
    return full || 'Usuario';
  }

  function avatarFromName(name) {
    return (
      'https://ui-avatars.com/api/?name=' +
      encodeURIComponent(name) +
      '&background=f97316&color=fff&size=256'
    );
  }

  function openModal() {
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    activeUser = null;
  }

  function setActiveTab(tabId) {
    const tabs = modal.querySelectorAll('.usuario-tab');
    const panels = modal.querySelectorAll('.usuario-tabpanel');
    tabs.forEach((t) => t.classList.toggle('is-active', t.dataset.tab === tabId));
    panels.forEach((p) => p.classList.toggle('is-active', p.id === tabId));
  }

  // -----------------------------
  // Filters
  // -----------------------------
  function uniqueSorted(values) {
    return Array.from(
      new Set(
        values
          .filter((v) => cleanNullBytes(v) !== '')
          .map((v) => cleanNullBytes(v))
      )
    ).sort((a, b) => a.localeCompare(b, 'es', { sensitivity: 'base' }));
  }

  function fillDatalist(dl, values) {
    if (!dl) return;
    dl.innerHTML = '';
    values.forEach((v) => {
      const opt = document.createElement('option');
      opt.value = v;
      dl.appendChild(opt);
    });
  }

  function fillSexoSelect(select, values) {
    if (!select) return;
    select.innerHTML = '';
    const optAll = document.createElement('option');
    optAll.value = '';
    optAll.textContent = 'Todos';
    select.appendChild(optAll);
    values.forEach((v) => {
      const o = document.createElement('option');
      o.value = v;
      o.textContent = v;
      select.appendChild(o);
    });
  }

  function applyFilters() {
    const qNombre = cleanNullBytes(filtroNombre?.value || '').toLowerCase();
    const qDir = cleanNullBytes(filtroDireccion?.value || '').toLowerCase();
    const sexo = cleanNullBytes(filtroSexo?.value || '');

    const cards = grid.querySelectorAll('.usuario-card');
    let visible = 0;

    cards.forEach((card) => {
      const n = (card.getAttribute('data-nombre') || '').toLowerCase();
      const d = (card.getAttribute('data-direccion') || '').toLowerCase();
      const s = card.getAttribute('data-sexo') || '';

      const okNombre = qNombre ? n.includes(qNombre) : true;
      const okDir = qDir ? d.includes(qDir) : true;
      const okSexo = sexo ? s === sexo : true;

      const ok = okNombre && okDir && okSexo;
      card.style.display = ok ? '' : 'none';
      if (ok) visible += 1;
    });

    if (!users.length) return;
    emptyBox.style.display = visible === 0 ? '' : 'none';
  }

  // -----------------------------
  // Cards
  // -----------------------------
  function cardHtmlFromUser(row) {
    const id = cleanNullBytes(pick(row, ['id', 'ID'], ''));
    const full = fullNameFromRow(row);

    const sexo = safeText(pick(row, ['Sexo', 'sexo'], '—'));
    const direccion = safeText(pick(row, ['Direccion', 'direccion'], '—'));
    const email = safeText(pick(row, ['Correo', 'correo', 'Email', 'email'], '—'));
    const tel = safeText(pick(row, ['Telefono_Usuario', 'telefono_usuario', 'Telefono', 'telefono'], '—'));

    const avatar = avatarFromName(full);

    const payload = {
      id,
      fullName: full,
      sexo,
      direccion,
      avatar,
      row
    };

    const payloadJson = escapeHtml(JSON.stringify(payload));

    return `
      <article class="usuario-card" data-id="${escapeHtml(id)}"
        data-user="${payloadJson}"
        data-nombre="${escapeHtml(full)}"
        data-sexo="${escapeHtml(sexo)}"
        data-direccion="${escapeHtml(direccion)}"
      >
        <div class="usuario-card__media">
          <img src="${escapeHtml(avatar)}" alt="Foto de ${escapeHtml(full)}">
        </div>

        <div class="usuario-card__keys">
          <h3 class="usuario-name">${escapeHtml(full)}</h3>
          <div class="usuario-kv">
            <div class="usuario-kv__row">
              <span class="usuario-kv__k">Email</span>
              <span class="usuario-kv__v">${escapeHtml(email)}</span>
            </div>
            <div class="usuario-kv__row">
              <span class="usuario-kv__k">Teléfono</span>
              <span class="usuario-kv__v">${escapeHtml(tel)}</span>
            </div>
          </div>
        </div>

        <div class="usuario-card__note">
          <p class="usuario-note">${escapeHtml('Haz clic para abrir la ficha.')}</p>
        </div>

        <div class="usuario-card__meta">
          <div class="usuario-badges">
            <span class="usuario-badge">${escapeHtml(sexo)}</span>
            <span class="usuario-badge usuario-badge--soft">${escapeHtml(direccion)}</span>
          </div>
          <span class="usuario-cta">Abrir <span class="usuario-cta__arrow">→</span></span>
        </div>
      </article>
    `;
  }

  // -----------------------------
  // Modal rendering (editable sections)
  // -----------------------------
  const FIELD_TYPES = {
    Fecha_Nacimiento: 'date',
    Fecha_Alta: 'datetime-local',
    ID_Situacion_Administrativa: 'number',
    Tipo_Socio: 'number'
  };

  const LABELS = {
    Nombre: 'Nombre',
    Apellidos: 'Apellidos',
    Dni: 'DNI',
    Fecha_Nacimiento: 'Fecha nacimiento',
    Nacionalidad: 'Nacionalidad',
    Nivel_Estudios: 'Nivel estudios',
    CCC: 'Cuenta corriente (CCC)',

    Correo: 'Correo',
    Telefono_Usuario: 'Teléfono usuario',
    Direccion: 'Dirección',
    Codigo_Postal: 'Código postal',

    Telefono_Familia1: 'Tel. familia 1',
    Telefono_Familia2: 'Tel. familia 2',
    Telefono_Servicios_Sociales: 'Tel. servicios sociales',
    Telefono_Trabajadora_Social: 'Tel. trabajadora social',
    Telefono_Centro_Salud: 'Tel. centro salud',
    Telefono_Medico_Cavecera: 'Tel. médico cabecera',
    Telefono_Salud_Mental: 'Tel. salud mental',
    Telefono_Referente_Salud: 'Tel. referente salud',
    Telefono_Referente_Formativo: 'Tel. referente formativo',
    Telefono_Otros1: 'Tel. otros 1',
    Telefono_Otros2: 'Tel. otros 2',
    ID_Via_Comunicacion: 'ID vía comunicación',

    Sexo: 'Sexo',
    Tipo_Socio: 'Tipo socio',
    Fecha_Alta: 'Fecha alta',
    ID_Situacion_Administrativa: 'ID situación administrativa',
    N_TIS: 'Nº TIS',

    // ✅ Etiquetas para Diagnóstico (aliased fields por JOIN)
    DEP_Grado: 'Grado',
    DEP_Fecha_Reconocimiento: 'Fecha reconocimiento',
    DEP_Fecha_Caducidad: 'Fecha caducidad',
    DEP_Descripcion: 'Descripción',

    DISC_Grado: 'Grado',
    DISC_Fecha_Reconocimiento: 'Fecha reconocimiento',
    DISC_Fecha_Caducidad: 'Fecha caducidad',
    DISC_Descripcion: 'Descripción',

    EXCL_Grado: 'Grado',
    EXCL_Fecha_Reconocimiento: 'Fecha reconocimiento',
    EXCL_Fecha_Caducidad: 'Fecha caducidad',
    EXCL_Descripcion: 'Descripción'
  };

  function renderDL(fields, row) {
    const dl = document.createElement('dl');
    dl.className = 'usuario-dl';
    fields.forEach((f) => {
      const dt = document.createElement('dt');
      dt.className = 'usuario-dt';
      dt.textContent = LABELS[f] || f;

      const dd = document.createElement('dd');
      dd.className = 'usuario-dd';
      dd.textContent = safeText(row?.[f]);

      dl.appendChild(dt);
      dl.appendChild(dd);
    });
    return dl;
  }

  function inputForField(field, value) {
    const type = FIELD_TYPES[field] || 'text';
    const input = document.createElement('input');
    input.className = 'control';
    input.type = type;

    if (type === 'datetime-local') {
      const v = cleanNullBytes(value);
      if (v && v.includes(' ')) {
        const parts = v.split(' ');
        input.value = `${parts[0]}T${(parts[1] || '').slice(0, 5)}`;
      } else {
        input.value = v;
      }
    } else {
      input.value = cleanNullBytes(value);
    }

    input.dataset.field = field;
    return input;
  }

  async function updateUser(id, updates) {
    const res = await fetch(ENDPOINT_UPDATE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ id, updates })
    });
    const json = await res.json();
    if (!json?.ok) throw new Error(json?.error || 'Error guardando');
    return json;
  }

  function renderEditableSection(container, title, fields, row, opts = {}) {
    const section = document.createElement('div');
    section.className = 'usuario-section';

    const head = document.createElement('div');
    head.className = 'usuario-section__head';

    const h = document.createElement('div');
    h.className = 'usuario-section__title';
    h.textContent = title;

    const actions = document.createElement('div');
    actions.className = 'usuario-section__actions';

    const btnEdit = document.createElement('button');
    btnEdit.type = 'button';
    btnEdit.className = 'btn btn--sm';
    btnEdit.textContent = 'Editar';

    const btnCancel = document.createElement('button');
    btnCancel.type = 'button';
    btnCancel.className = 'btn btn--sm';
    btnCancel.textContent = 'Cancelar';
    btnCancel.style.display = 'none';

    const btnSave = document.createElement('button');
    btnSave.type = 'button';
    btnSave.className = 'btn btn-primary btn--sm';
    btnSave.textContent = 'Guardar';
    btnSave.style.display = 'none';

    actions.appendChild(btnEdit);
    actions.appendChild(btnCancel);
    actions.appendChild(btnSave);

    head.appendChild(h);
    head.appendChild(actions);

    section.appendChild(head);

    const viewWrap = document.createElement('div');
    const formWrap = document.createElement('div');
    formWrap.style.display = 'none';

    viewWrap.appendChild(renderDL(fields, row));

    const form = document.createElement('div');
    form.className = 'usuario-form';

    fields.forEach((f) => {
      const field = document.createElement('div');
      field.className = 'field';

      const label = document.createElement('label');
      label.className = 'label';
      label.textContent = LABELS[f] || f;

      const inp = inputForField(f, row?.[f]);

      field.appendChild(label);
      field.appendChild(inp);
      form.appendChild(field);
    });

    formWrap.appendChild(form);

    section.appendChild(viewWrap);
    section.appendChild(formWrap);

    const setEditing = (on) => {
      viewWrap.style.display = on ? 'none' : '';
      formWrap.style.display = on ? '' : 'none';
      btnEdit.style.display = on ? 'none' : '';
      btnSave.style.display = on ? '' : 'none';
      btnCancel.style.display = on ? '' : 'none';
    };

    btnEdit.addEventListener('click', () => setEditing(true));

    btnCancel.addEventListener('click', () => {
      form.querySelectorAll('input[data-field]').forEach((inp) => {
        const f = inp.dataset.field;
        inp.value = cleanNullBytes(row?.[f]);
      });
      setEditing(false);
    });

    btnSave.addEventListener('click', async () => {
      if (!activeUser) return;
      const id = cleanNullBytes(activeUser.id);

      const updates = {};
      form.querySelectorAll('input[data-field]').forEach((inp) => {
        const f = inp.dataset.field;
        let v = inp.value;
        if ((FIELD_TYPES[f] || '') === 'datetime-local' && v && v.includes('T')) {
          v = v.replace('T', ' ') + ':00';
        }
        updates[f] = v;
      });

      btnSave.disabled = true;
      btnEdit.disabled = true;
      btnCancel.disabled = true;

      try {
        await updateUser(id, updates);

        Object.keys(updates).forEach((k) => {
          row[k] = updates[k];
        });

        viewWrap.innerHTML = '';
        viewWrap.appendChild(renderDL(fields, row));

        if (opts.onSaved) opts.onSaved(updates);

        setEditing(false);
      } catch (e) {
        alert(e?.message || 'Error guardando');
      } finally {
        btnSave.disabled = false;
        btnEdit.disabled = false;
        btnCancel.disabled = false;
      }
    });

    container.appendChild(section);
  }
  function renderMessageSection(container, title, message) {
    const section = document.createElement('div');
    section.className = 'usuario-section';

    const head = document.createElement('div');
    head.className = 'usuario-section__head';

    const h = document.createElement('div');
    h.className = 'usuario-section__title';
    h.textContent = title;

    head.appendChild(h);
    section.appendChild(head);

    const content = document.createElement('div');
    content.className = 'usuario-section__content';
    section.appendChild(content);

    const p = document.createElement('p');
    p.style.margin = '0';
    p.style.color = 'var(--text-soft)';
    p.textContent = message;
    content.appendChild(p);

    container.appendChild(section);
  }

  function renderDiagDynamicSection(container, title, diag) {
    // diag: {id, table, cols, row}
    if (!diag || !diag.id) {
      renderMessageSection(container, title, '—');
      return;
    }
    if (!diag.row) {
      renderMessageSection(container, title, '—');
      return;
    }

    const section = document.createElement('div');
    section.className = 'usuario-section';

    const head = document.createElement('div');
    head.className = 'usuario-section__head';

    const h = document.createElement('div');
    h.className = 'usuario-section__title';
    h.textContent = title;

    head.appendChild(h);
    section.appendChild(head);

    const content = document.createElement('div');
    content.className = 'usuario-section__content';
    section.appendChild(content);

    // Todas las columnas reales menos Id
    const cols = (diag.cols || []).filter(c => c !== 'Id' && c !== 'ID' && c !== 'id');
    const dl = document.createElement('dl');
    dl.className = 'usuario-dl';

    cols.forEach((c) => {
      const dt = document.createElement('dt');
      dt.className = 'usuario-dt';
      dt.textContent = c.replaceAll('_', ' ');

      const dd = document.createElement('dd');
      dd.className = 'usuario-dd';
      dd.textContent = safeText(diag.row[c]);

      dl.appendChild(dt);
      dl.appendChild(dd);
    });

    content.appendChild(dl);
    container.appendChild(section);
  }

  async function renderDiagnosticos(panel, userRow) {
    panel.innerHTML = '';

    const depId = userRow?.ID_DIAG_Dependencia ?? null;
    const discId = userRow?.ID_DIAG_Discapacidad ?? null;
    const exclId = userRow?.ID_DIAG_Exclusion ?? null;

    const norm = (v) => {
      const s = cleanNullBytes(v);
      if (s === '' || s === '—' || s.toLowerCase() === 'null') return null;
      return s;
    };

    // Si todos son null, pintamos 3 secciones con "—"
    if (!norm(depId) && !norm(discId) && !norm(exclId)) {
      renderMessageSection(panel, 'Diagnóstico de Dependencia', '—');
      renderMessageSection(panel, 'Diagnóstico de Discapacidad', '—');
      renderMessageSection(panel, 'Diagnóstico de Exclusión', '—');
      return;
    }

    renderMessageSection(panel, 'Diagnóstico', 'Cargando…');

    try {
      const json = await window.OrtzadarUsuariosAPI.fetchDiagnosticos({
        depId: norm(depId),
        discId: norm(discId),
        exclId: norm(exclId),
      });

      if (!json?.ok) throw new Error(json?.error || 'Error cargando diagnóstico');

      const d = json.diagnosticos || {};
      panel.innerHTML = '';

      renderDiagDynamicSection(panel, 'Diagnóstico de Dependencia', d.dependencia);
      renderDiagDynamicSection(panel, 'Diagnóstico de Discapacidad', d.discapacidad);
      renderDiagDynamicSection(panel, 'Diagnóstico de Exclusión', d.exclusion);

    } catch (e) {
      panel.innerHTML = '';
      renderMessageSection(panel, 'Diagnóstico', e?.message || 'Error');
    }
  }

  // -----------------------------
  // ✅ Diagnóstico (solo VISUALIZACIÓN por JOIN, sin IDs)
  // -----------------------------
  function renderDiagnosticoPanel(panel, title, fields, row) {
    const section = document.createElement('div');
    section.className = 'usuario-section';

    const head = document.createElement('div');
    head.className = 'usuario-section__head';

    const h = document.createElement('div');
    h.className = 'usuario-section__title';
    h.textContent = title;

    head.appendChild(h);
    section.appendChild(head);

    // content wrapper (para overflow interno con CSS .usuario-section__content)
    const content = document.createElement('div');
    content.className = 'usuario-section__content';
    section.appendChild(content);

    content.appendChild(renderDL(fields, row));
    panel.appendChild(section);
  }

  // -----------------------------
  // Render cards + modal
  // -----------------------------
  function renderCards() {
    if (!users.length) {
      grid.style.display = 'none';
      statusBox.style.display = 'none';
      emptyBox.style.display = '';
      return;
    }

    grid.innerHTML = users.map(cardHtmlFromUser).join('');

    statusBox.style.display = 'none';
    emptyBox.style.display = 'none';
    grid.style.display = '';

    grid.querySelectorAll('.usuario-card').forEach((card) => {
      card.addEventListener('click', () => {
        const payloadRaw = card.getAttribute('data-user') || '{}';
        let payload;
        try { payload = JSON.parse(payloadRaw); } catch { payload = null; }
        const row = payload?.row || null;
        if (!row) return;
        buildModalFromRow(row);
        openModal();
      });
    });

    applyFilters();
  }

  function refreshDatalistsAndFilters() {
    const nombres = uniqueSorted(users.map((u) => fullNameFromRow(u)));
    const dirs = uniqueSorted(
      users
        .map((u) => safeText(pick(u, ['Direccion', 'direccion'], '')))
        .filter((v) => v !== '—')
    );
    const sexos = uniqueSorted(
      users
        .map((u) => safeText(pick(u, ['Sexo', 'sexo'], '')))
        .filter((v) => v !== '—')
    );

    fillDatalist(dlNombres, nombres);
    fillDatalist(dlDirecciones, dirs);
    fillSexoSelect(filtroSexo, sexos);
    applyFilters();
  }

  function buildModalFromRow(row) {
    activeUser = {
      id: cleanNullBytes(pick(row, ['id', 'ID'], '')),
      row
    };

    const full = fullNameFromRow(row);
    modalTitulo.textContent = full;
    modalSub.textContent = `ID: ${safeText(activeUser.id)}`;
    modalAvatar.setAttribute('src', avatarFromName(full));

    panelPerfil.innerHTML = '';
    panelContacto.innerHTML = '';
    panelAdmin.innerHTML = '';
    panelDiag.innerHTML = '';

    // PERFIL
    const perfilFields = ['Nombre', 'Apellidos', 'Dni', 'Fecha_Nacimiento', 'Nacionalidad', 'Nivel_Estudios', 'CCC'];
    renderEditableSection(panelPerfil, 'Perfil', perfilFields, row, {
      onSaved: () => {
        modalTitulo.textContent = fullNameFromRow(row);
        modalAvatar.setAttribute('src', avatarFromName(fullNameFromRow(row)));
        renderCards();
        refreshDatalistsAndFilters();
      }
    });

    // CONTACTO (2 paneles)
    const contactoUsuarioFields = ['Correo', 'Telefono_Usuario', 'Direccion', 'Codigo_Postal'];
    const contactoServiciosFields = [
      'Telefono_Familia1', 'Telefono_Familia2',
      'Telefono_Servicios_Sociales', 'Telefono_Trabajadora_Social',
      'Telefono_Centro_Salud', 'Telefono_Medico_Cavecera',
      'Telefono_Salud_Mental', 'Telefono_Referente_Salud',
      'Telefono_Referente_Formativo', 'Telefono_Otros1', 'Telefono_Otros2',
      'ID_Via_Comunicacion'
    ];

    const panels = document.createElement('div');
    panels.className = 'usuario-panels';
    panelContacto.appendChild(panels);

    const left = document.createElement('div');
    const right = document.createElement('div');
    panels.appendChild(left);
    panels.appendChild(right);

    renderEditableSection(left, 'Contacto usuario', contactoUsuarioFields, row, {
      onSaved: () => {
        renderCards();
        refreshDatalistsAndFilters();
      }
    });
    renderEditableSection(right, 'Contacto servicios', contactoServiciosFields, row);

    // ADMINISTRATIVO
    const adminFields = ['Sexo', 'Tipo_Socio', 'Fecha_Alta', 'ID_Situacion_Administrativa', 'N_TIS'];
    renderEditableSection(panelAdmin, 'Administrativo', adminFields, row, {
      onSaved: () => renderCards()
    });

    // ✅ DIAGNÓSTICO (3 secciones full width, info por JOIN)
    renderDiagnosticoPanel(panelDiag, 'Diagnóstico de Dependencia', [
      'DEP_Grado',
      'DEP_Fecha_Reconocimiento',
      'DEP_Fecha_Caducidad',
      'DEP_Descripcion'
    ], row);

    renderDiagnosticoPanel(panelDiag, 'Diagnóstico de Discapacidad', [
      'DISC_Grado',
      'DISC_Fecha_Reconocimiento',
      'DISC_Fecha_Caducidad',
      'DISC_Descripcion'
    ], row);

    renderDiagnosticoPanel(panelDiag, 'Diagnóstico de Exclusión', [
      'EXCL_Grado',
      'EXCL_Fecha_Reconocimiento',
      'EXCL_Fecha_Caducidad',
      'EXCL_Descripcion'
    ], row);

    setActiveTab('tab-perfil');
  }

  // -----------------------------
  // Modal events
  // -----------------------------
  modalClose?.addEventListener('click', closeModal);

  modal?.addEventListener('click', (e) => {
    const t = e.target;
    if (t?.dataset?.close) closeModal();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal?.classList.contains('is-open')) closeModal();
  });

  modal?.querySelectorAll('.usuario-tab').forEach((b) => {
    b.addEventListener('click', () => setActiveTab(b.dataset.tab));
  });

  // Filters events
  filtroNombre?.addEventListener('input', applyFilters);
  filtroDireccion?.addEventListener('input', applyFilters);
  filtroSexo?.addEventListener('change', applyFilters);

  // -----------------------------
  // Load users
  // -----------------------------
  async function loadUsers() {
    statusBox.style.display = '';
    statusBox.textContent = 'Cargando usuarios…';
    statusBox.classList.add('usuarios-status--loading');

    try {
      if (!window.OrtzadarUsuariosAPI?.fetchUsuarios) {
        throw new Error('No está cargado obtener_usuarios.js');
      }
      const json = await window.OrtzadarUsuariosAPI.fetchUsuarios();
      if (!json?.ok) throw new Error(json?.error || 'Error cargando usuarios');

      users = Array.isArray(json.usuarios) ? json.usuarios : [];

      if (!users.length) {
        renderCards();
        return;
      }

      refreshDatalistsAndFilters();
      renderCards();
    } catch (e) {
      grid.style.display = 'none';
      emptyBox.style.display = '';
      statusBox.textContent = e?.message || 'Error cargando usuarios';
    }
  }

  loadUsers();
})();
