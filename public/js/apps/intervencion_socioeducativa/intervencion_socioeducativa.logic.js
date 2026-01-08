// public/js/apps/programas_io/programas_io.logic.js
(() => {
  const $ = (sel) => document.querySelector(sel);

  // =====================================================
  // IO (listado + modal IO)
  // =====================================================
  const listEl = $('#ioList');
  const fPersona = $('#fPersona');
  const fMunicipio = $('#fMunicipio');
  const fDesde = $('#fDesde');
  const fHasta = $('#fHasta');
  const btnAplicar = $('#btnAplicar');
  const btnLimpiar = $('#btnLimpiar');

  window.ProgramasIOLogic = {
    async cargarListado(helpers) {
      if (!listEl) return;
      listEl.innerHTML = `<div class="empty">Cargando…</div>`;

      const params = new URLSearchParams({
        persona: fPersona?.value || '',
        municipio: fMunicipio?.value || '',
        desde: fDesde?.value || '',
        hasta: fHasta?.value || ''
      });

      let json;
      try {
        const res = await fetch(`public/php/intervencion_socioeducativa/cargar_usuarios.php?${params.toString()}`, {
          headers: { 'Accept': 'application/json' }
        });
        json = await res.json();
      } catch {
        listEl.innerHTML = `<div class="empty">No se pudo conectar.</div>`;
        return;
      }

      if (!json?.ok) {
        listEl.innerHTML = `<div class="empty">${helpers.escapeHtml(json?.error || 'Error cargando listado')}</div>`;
        return;
      }

      this.pintarListado(json.data || [], helpers);
    },

    pintarListado(items, helpers) {
      if (!listEl) return;

      if (!items.length) {
        listEl.innerHTML = `<div class="empty">No hay registros de IO con esos filtros.</div>`;
        return;
      }

      listEl.innerHTML = items.map(u => {
        const nombre = u.nombre || '-';
        return `
          <div class="io-person" data-id="${helpers.escapeHtml(u.id_usuario)}">
            <div class="io-avatar io-avatar--photo">
              <img
                class="io-avatar__img"
                src="uploads/usuarios/${encodeURIComponent(u.id_usuario)}.jpg?v=${Date.now()}"
                alt=""
                loading="lazy"
                onerror="this.style.display='none'; this.parentElement.querySelector('.io-avatar__txt').style.display='grid';"
              >
              <span class="io-avatar__txt">${helpers.escapeHtml(helpers.initials(nombre))}</span>
            </div>

            <div class="io-person__main">
              <div class="io-person__title">${helpers.escapeHtml(nombre)}</div>

              <div class="io-person__meta">
                <span>DNI: ${helpers.escapeHtml(u.dni || '-')}</span>
                <span>Email: ${helpers.escapeHtml(u.email || '-')}</span>
                <span class="io-muted">Fecha IO: ${helpers.escapeHtml(u.fecha_io || '-')}</span>
              </div>

              <div class="io-person__footer">
                <span class="io-muted">Teléfono: ${helpers.escapeHtml(u.telefono || '-')}</span>
                <span class="io-muted">Lugar entrevista: ${helpers.escapeHtml(u.lugar_entrevista || '-')}</span>
                <span class="io-muted">Tipo demanda: ${helpers.escapeHtml(u.tipo_demanda || '-')}</span>
              </div>
            </div>

            <div class="io-person__actions">
              <button class="btn btn-secondary" type="button" data-open="${helpers.escapeHtml(u.id_usuario)}">Ver ficha →</button>
            </div>
          </div>
        `;
      }).join('');
    },

    bindFiltros(onReload) {
      btnAplicar?.addEventListener('click', onReload);
      btnLimpiar?.addEventListener('click', () => {
        if (fPersona) fPersona.value = '';
        if (fMunicipio) fMunicipio.value = '';
        if (fDesde) fDesde.value = '';
        if (fHasta) fHasta.value = '';
        onReload();
      });
    },

    bindOpenFromList(openCb) {
      listEl?.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-open]');
        const card = e.target.closest('.io-person');
        const id = btn?.dataset?.open || card?.dataset?.id;
        if (id) openCb(id);
      });
    },

    rellenarModalIO(data, helpers) {
      const io = data.io || {};

      helpers.setVal('io_fecha', helpers.dtToLocalInput(io.fecha));
      helpers.setVal('io_id_tipo_atencion', io.id_tipo_atencion);
      helpers.setVal('io_id_orientado_por', io.id_orientado_por);
      helpers.setVal('io_id_realizado_por', io.id_realizado_por);
      helpers.setVal('io_tipo_demanda', io.tipo_demanda);
      helpers.setVal('io_lugar_entrevista', io.lugar_entrevista);

      helpers.setVal('io_composicion_familiar', io.composicion_familiar);

      // OJO: mantengo tu id antiguo EXACTO (tú lo mapeas en rellenar y en guardar igual)
      helpers.setVal('io_b_escolar_formacion', io.h_escolar_formacion);

      helpers.setVal('io_relaciones_sociales', io.relaciones_sociales);
      helpers.setVal('io_salud', io.salud);
      helpers.setVal('io_autonomia', io.autonomia);

      helpers.setVal('io_red_apo_ss_base', io.red_apo_ss_base);
      helpers.setVal('io_red_apo_csm', io.red_apo_csm);
      helpers.setVal('io_red_apo_familia', io.red_apo_familia);
      helpers.setVal('io_red_apo_otros', io.red_apo_otros);

      helpers.setVal('io_demanda', io.demanda);
      helpers.setVal('io_acuerdo', io.acuerdo);

      // tabs por defecto al abrir
      helpers.activateTab('main', 'tab_main_usuario');
      helpers.activateTab('user', 'tab_user_basico');
      helpers.activateTab('io', 'tab_io_generales');
    },

    clearIOFields(helpers) {
      const ids = [
        'io_fecha', 'io_id_tipo_atencion', 'io_id_orientado_por', 'io_id_realizado_por',
        'io_tipo_demanda', 'io_lugar_entrevista',
        'io_composicion_familiar', 'io_b_escolar_formacion', 'io_relaciones_sociales', 'io_salud', 'io_autonomia',
        'io_red_apo_ss_base', 'io_red_apo_csm', 'io_red_apo_familia', 'io_red_apo_otros',
        'io_demanda', 'io_acuerdo'
      ];
      ids.forEach(id => helpers.setVal(id, ''));
    },

    buildIOPayload(helpers) {
      const $ = (sel) => document.querySelector(sel);

      return {
        fecha: helpers.localInputToDb($('#io_fecha')?.value || ''),
        id_tipo_atencion: $('#io_id_tipo_atencion')?.value ? Number($('#io_id_tipo_atencion').value) : null,
        id_orientado_por: $('#io_id_orientado_por')?.value ? Number($('#io_id_orientado_por').value) : null,
        id_realizado_por: $('#io_id_realizado_por')?.value ? Number($('#io_id_realizado_por').value) : null,
        tipo_demanda: $('#io_tipo_demanda')?.value || '',
        lugar_entrevista: $('#io_lugar_entrevista')?.value || '',

        composicion_familiar: $('#io_composicion_familiar')?.value || null,
        h_escolar_formacion: $('#io_b_escolar_formacion')?.value || null,
        relaciones_sociales: $('#io_relaciones_sociales')?.value || null,
        salud: $('#io_salud')?.value || null,
        autonomia: $('#io_autonomia')?.value || null,

        red_apo_ss_base: $('#io_red_apo_ss_base')?.value || null,
        red_apo_csm: $('#io_red_apo_csm')?.value || null,
        red_apo_familia: $('#io_red_apo_familia')?.value || null,
        red_apo_otros: $('#io_red_apo_otros')?.value || null,

        demanda: $('#io_demanda')?.value || null,
        acuerdo: $('#io_acuerdo')?.value || null
      };
    }
  };

  // =====================================================
  // Intervención Social: REGISTROS (AA_Registros)
  // =====================================================

  // helper simple para evitar XSS si el comentario viene con < >
  function escapeHtml(str) {
    return String(str)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
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

    const rows = registros.map(r => {
      const id = Number(r.id || 0);
      const fecha = (r.Fecha ?? '').toString();
      const comentario = (r.Comentario ?? '').toString();
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

    container.innerHTML = header + rows;
  }

  async function cargarRegistros(idUsuario, helpers, ambito = 'BF', categoria = 'OBJ') {
    const status = document.getElementById('isRegistrosStatus');
    const list = document.getElementById('isRegistrosList');
    if (list) list.innerHTML = `<div class="empty">Cargando…</div>`;
    if (status) status.textContent = '';

    let json;
    try {
      const res = await fetch(
        `public/php/intervencion_socioeducativa/registros_get.php?id_usuario=${encodeURIComponent(idUsuario)}&ambito=${encodeURIComponent(ambito)}&categoria=${encodeURIComponent(categoria)}`,
        { headers: { 'Accept': 'application/json' } }
      );
      json = await res.json();
    } catch {
      if (status) status.textContent = 'No se pudo conectar.';
      if (list) list.innerHTML = `<div class="empty">No se pudo conectar.</div>`;
      return;
    }

    if (!json?.ok) {
      const msg = json?.error || 'Error cargando registros';
      if (status) status.textContent = msg;
      if (list) list.innerHTML = `<div class="empty">${helpers.escapeHtml(msg)}</div>`;
      return;
    }

    renderRegistros(list, json.data);
  }

  function bindFavoritos(helpers) {
    const list = document.getElementById('isRegistrosList');
    if (!list || list.dataset.favsBound === '1') return;
    list.dataset.favsBound = '1';

    list.addEventListener('click', async (e) => {
      const btn = e.target.closest('.is-star');
      if (!btn) return;

      e.preventDefault();
      e.stopPropagation();

      const row = btn.closest('.is-registros__row');
      if (!row) return;

      const id = Number(row.dataset.registroId || 0);
      if (!id) return;

      const hiddenUserId = document.getElementById('ioModalUserId');
      const idUsuario = hiddenUserId?.value ? Number(hiddenUserId.value) : 0;
      if (!idUsuario) return;

      const status = document.getElementById('isRegistrosStatus');

      const current = Number(row.dataset.destacado || 0);
      const next = current ? 0 : 1;

      // UI optimista
      btn.disabled = true;
      row.dataset.destacado = String(next);
      btn.classList.toggle('is-on', next === 1);
      btn.textContent = next ? '★' : '☆';
      if (status) status.textContent = '';

      try {
        const res = await fetch('public/php/intervencion_socioeducativa/registros_destacado.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          credentials: 'same-origin',
          body: JSON.stringify({ id, id_usuario: idUsuario, destacado: next })
        });

        const json = await res.json();
        if (!json?.ok) throw new Error(json?.error || 'Error actualizando destacado');

        const finalVal = Number(json?.data?.Destacado ?? next);
        row.dataset.destacado = String(finalVal);
        btn.classList.toggle('is-on', finalVal === 1);
        btn.textContent = finalVal ? '★' : '☆';

      } catch (err) {
        // revertir
        row.dataset.destacado = String(current);
        btn.classList.toggle('is-on', current === 1);
        btn.textContent = current ? '★' : '☆';
        if (status) status.textContent = err?.message || 'Error actualizando destacado';
      } finally {
        btn.disabled = false;
      }
    });
  }

  function bindRegistros(helpers) {
    const btnNuevo = document.getElementById('is_btn_nuevo_registro');
    const panelNuevo = document.getElementById('isNuevoRegistro');
    const fechaTxt = document.getElementById('is_registro_fecha_txt');
    const btnSave = document.getElementById('is_registro_guardar');
    const btnCancel = document.getElementById('is_registro_cancelar');
    const ta = document.getElementById('is_registro_comentario');
    const status = document.getElementById('isRegistrosStatus');
    const hiddenUserId = document.getElementById('ioModalUserId');

    const ambitoHidden = document.getElementById('is_ambito');
    const categoriaHidden = document.getElementById('is_categoria');
    const ambitoLbl = document.getElementById('is_ambito_label');
    const categoriaLbl = document.getElementById('is_categoria_label');

    const getFilters = () => ({
      ambito: ambitoHidden?.value || 'BF',
      categoria: categoriaHidden?.value || 'OBJ'
    });

    const pad = (n) => String(n).padStart(2, '0');
    const nowLocalInput = () => {
      const now = new Date();
      return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
    };

    let selectedFechaDb = '';

    function setStatus(msg) {
      if (!status) return;
      status.textContent = msg || '';
    }

    function openNuevoRegistro() {
      if (!panelNuevo) return;

      selectedFechaDb = helpers.localInputToDb(nowLocalInput());
      if (fechaTxt) fechaTxt.textContent = selectedFechaDb;

      panelNuevo.style.display = 'block';
      if (ta) {
        ta.value = '';
        ta.focus();
      }

      const ambitoBtn = document.querySelector('[data-is-ambito].is-active');
      const catBtn = document.querySelector('[data-is-categoria].is-active');
      if (ambitoLbl && ambitoBtn) ambitoLbl.textContent = ambitoBtn.textContent.trim();
      if (categoriaLbl && catBtn) categoriaLbl.textContent = catBtn.textContent.trim();
    }

    function closeNuevoRegistro() {
      if (!panelNuevo) return;
      panelNuevo.style.display = 'none';
      if (ta) ta.value = '';
      selectedFechaDb = '';
    }

    // selección ámbito / categoría
    const ambitoBtns = [...document.querySelectorAll('[data-is-ambito]')];
    const catBtns = [...document.querySelectorAll('[data-is-categoria]')];

    function setActive(btns, activeBtn) {
      btns.forEach(b => b.classList.toggle('is-active', b === activeBtn));
    }

    ambitoBtns.forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        e.stopPropagation();

        setActive(ambitoBtns, btn);
        if (ambitoHidden) ambitoHidden.value = btn.dataset.isAmbito;
        if (ambitoLbl) ambitoLbl.textContent = btn.textContent.trim();

        const idUsuario = hiddenUserId?.value ? Number(hiddenUserId.value) : 0;
        if (idUsuario) {
          const { ambito, categoria } = getFilters();
          await cargarRegistros(idUsuario, helpers, ambito, categoria);
        }
      });
    });

    catBtns.forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        e.stopPropagation();

        setActive(catBtns, btn);
        if (categoriaHidden) categoriaHidden.value = btn.dataset.isCategoria;
        if (categoriaLbl) categoriaLbl.textContent = btn.textContent.trim();

        const idUsuario = hiddenUserId?.value ? Number(hiddenUserId.value) : 0;
        if (idUsuario) {
          const { ambito, categoria } = getFilters();
          await cargarRegistros(idUsuario, helpers, ambito, categoria);
        }
      });
    });

    // botón nuevo
    btnNuevo?.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();

      const idUsuario = hiddenUserId?.value ? Number(hiddenUserId.value) : 0;
      if (!idUsuario) {
        setStatus('Guarda primero el usuario antes de añadir registros.');
        return;
      }
      openNuevoRegistro();
    });

    btnCancel?.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      closeNuevoRegistro();
      setStatus('');
    });

    // guardar registro
    btnSave?.addEventListener('click', async (e) => {
      e.preventDefault();
      e.stopPropagation();

      const idUsuario = hiddenUserId?.value ? Number(hiddenUserId.value) : 0;
      if (!idUsuario) {
        setStatus('Guarda primero el usuario antes de añadir registros.');
        return;
      }

      const comentario = (ta?.value || '').trim();
      if (!comentario) {
        setStatus('Escribe un comentario.');
        ta?.focus();
        return;
      }

      const { ambito, categoria } = getFilters();
      const fechaDb = selectedFechaDb || helpers.localInputToDb(nowLocalInput());

      setStatus('Guardando…');

      let json;
      try {
        const res = await fetch('public/php/intervencion_socioeducativa/registros_save.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          credentials: 'same-origin',
          body: JSON.stringify({
            id_usuario: idUsuario,
            fecha: fechaDb,
            comentario,
            ambito,
            categoria
          })
        });

        json = await res.json();
      } catch {
        setStatus('No se pudo conectar.');
        return;
      }

      if (!json?.ok) {
        setStatus(json?.error || 'Error guardando registro');
        return;
      }

      setStatus('Guardado ✅');
      closeNuevoRegistro();

      await cargarRegistros(idUsuario, helpers, ambito, categoria);
      setTimeout(() => setStatus(''), 1200);
    });

    // ✅ favoritos (delegado, una sola vez)
    bindFavoritos(helpers);
  }

  window.IntervencionSocialLogic = {
    cargarRegistros,
    bindRegistros
  };
  
})();



