// public/js/apps/programas_io/programas_io.logic.js
(() => {
  const $ = (sel) => document.querySelector(sel);

  // filtros
  const listEl = $('#ioList');
  const fPersona = $('#fPersona');
  const fMunicipio = $('#fMunicipio');
  const fDesde = $('#fDesde');
  const fHasta = $('#fHasta');
  const btnAplicar = $('#btnAplicar');
  const btnLimpiar = $('#btnLimpiar');

  // expone funciones al main
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
        const res = await fetch(`public/php/programas/io_listar.php?${params.toString()}`, {
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

      // OJO: aquí mantengo tu id antiguo EXACTO aunque sea “feo”
      // (tú lo mapeas en rellenar y en guardar igual)
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

      // tabs por defecto al abrir (idéntico a tu final de rellenarModal)
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
})();
