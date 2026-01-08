(() => {
    const $ = (s, root = document) => root.querySelector(s);

    // ---------------- Listado + filtros ----------------
    const listEl = $('#ocioList');
    const fGrupo = $('#fGrupo');
    const fAnio = $('#fAnio');
    const fResponsable = $('#fResponsable');
    const btnAplicar = $('#btnAplicar');
    const btnLimpiar = $('#btnLimpiar');
    const btnNuevoGrupo = $('#btnNuevoGrupo');

    // ---------------- Modal base ----------------
    const modal = document.querySelector('.io-modal[data-program="ocio"]');
    if (!modal) return;

    const modalDialog = $('.io-modal__dialog', modal);

    const modalId = modal.id; // "programModal_ocio"
    const statusEl = document.getElementById(`${modalId}_status`);
    const titleEl = document.getElementById(`${modalId}_title`);
    const subEl = document.getElementById(`${modalId}_sub`);

    // ---------------- Campos (dentro del modal) ----------------
    const hidId = $('#g_id', modal);
    const inNombre = $('#g_nombre', modal);
    const inFecha = $('#g_fecha', modal);
    const selResp = $('#g_id_responsable', modal);
    const inFotoGrupo = $('#g_foto', modal);
    const groupAvatar = $('#ocioGroupAvatar', modal);
    const groupAvatarImg = groupAvatar?.querySelector('img');
    const groupAvatarFallback = groupAvatar?.querySelector('.ocio-avatar__fallback');

    // Participantes
    const inBuscarP = $('#p_buscar', modal);
    const btnBuscarP = $('#btnBuscarParticipante', modal);
    const pResultados = $('#p_resultados', modal);
    const pList = $('#p_list', modal);

    // Acta
    const aId = $('#a_id', modal);
    const aFecha = $('#a_fecha', modal);
    const aAsistencia = $('#a_asistencia', modal);
    const aValoracion = $('#a_valoracion', modal);
    const aIncidencia = $('#a_incidencia', modal);
    const btnAddIncidencia = $('#btnAddIncidencia', modal);
    const btnGuardarActa = $('#btnGuardarActa', modal);
    const btnNuevaActa = $('#btnNuevaActa', modal);
    const btnExportActa = $('#btnExportActa', modal);

    const aIncidenciasList = $('#a_incidencias_list', modal);
    const aHist = $('#a_hist', modal);
    // Subtabs actions (Nueva acta / Exportar) -> solo en Acta
    const subtabsBar = modal.querySelector('.ocio-subtabsbar');
    const subtabsActions = modal.querySelector('.ocio-subtabs-actions');
    const respAvatar = $('#ocioRespAvatar', modal);
    const respAvatarImg = respAvatar?.querySelector('img');
    const respAvatarFallback = respAvatar?.querySelector('.ocio-avatar__fallback');

    const respPhotoUrl = (id) => `/webOrtzadar/uploads/trabajadores/${encodeURIComponent(id)}.jpg`;


    function setRespAvatar(id, labelText = '') {
        if (!respAvatar || !respAvatarImg) return;

        const parts = String(labelText || '').trim().split(/\s+/).filter(Boolean);
        const a = (parts[0]?.[0] || 'T').toUpperCase();
        const b = (parts[1]?.[0] || 'S').toUpperCase();
        if (respAvatarFallback) respAvatarFallback.textContent = (a + b);

        respAvatar.classList.add('is-no-photo');
        respAvatar.classList.remove('has-photo');

        respAvatarImg.src = `${respPhotoUrl(id)}?ts=${Date.now()}`;

        respAvatarImg.onload = () => { respAvatar.classList.remove('is-no-photo'); respAvatar.classList.add('has-photo'); };
        respAvatarImg.onerror = () => { respAvatar.classList.add('is-no-photo'); respAvatar.classList.remove('has-photo'); };
    }




    let cacheListado = [];
    let cacheParticipantes = [];      // [{id, Nombre, Apellidos, Dni}]
    let cacheActa = null;             // {id, fecha, valoracion}
    let cachePresentes = new Set();   // ids usuarios presentes
    let incidenciasPendientes = [];   // textos (nuevos)
    let searchTimer = null;
    let searchAbort = null;
    let lastQuery = '';


    // ---------------- Utils ----------------
    function status(msg) { if (statusEl) statusEl.textContent = msg || ''; }
    const groupPhotoUrl = (id) => `/webOrtzadar/uploads/ocio_grupos/${encodeURIComponent(id)}.jpg`;


    const groupInitials = (g) => {
        const n = String(g?.nombre || '').trim();
        if (!n) return 'OC';
        const parts = n.split(/\s+/).filter(Boolean);
        const a = (parts[0]?.[0] || 'O').toUpperCase();
        const b = (parts[1]?.[0] || 'C').toUpperCase();
        return (a + b);
    };
    async function uploadGrupoFoto(idGrupo, file) {
        const fd = new FormData();
        fd.append('id_grupo', String(idGrupo));
        fd.append('foto', file);

        const res = await fetch('/webOrtzadar/public/php/programas/ocio_grupo_foto_upload.php', {
            method: 'POST',
            body: fd
        });

        const json = await res.json().catch(() => null);
        if (!res.ok || !json?.ok) throw new Error(json?.error || `Error subiendo foto (HTTP ${res.status})`);
    }

    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, m => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[m]));
    }
    function setActaActionsVisible(visible) {
        // ✅ la condición del CSS está en .ocio-subtabsbar.is-acta
        subtabsBar?.classList.toggle('is-acta', !!visible);

        // (opcional) si quieres mantener compat con tu regla extra del final:
        modal.classList.toggle('is-acta', !!visible);
    }


    function lockBodyScroll(lock) { document.body.style.overflow = lock ? 'hidden' : ''; }

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        lockBodyScroll(true);
        (inNombre || modalDialog)?.focus?.();
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        lockBodyScroll(false);
    }
    function setGrupoAvatar(idGrupo, nombreGrupo) {
        if (!groupAvatar || !groupAvatarImg) return;

        const ini = groupInitials({ nombre: nombreGrupo });
        if (groupAvatarFallback) groupAvatarFallback.textContent = ini;

        groupAvatar.classList.add('is-no-photo');
        groupAvatar.classList.remove('has-photo');

        groupAvatarImg.src = `${groupPhotoUrl(idGrupo)}?ts=${Date.now()}`;


        groupAvatarImg.onload = () => {
            groupAvatar.classList.remove('is-no-photo');
            groupAvatar.classList.add('has-photo');
        };
        groupAvatarImg.onerror = () => {
            groupAvatar.classList.add('is-no-photo');
            groupAvatar.classList.remove('has-photo');
        };
    }

    // cerrar
    modal.addEventListener('click', (e) => {
        if (e.target?.dataset?.close) closeModal();
    });
    document.addEventListener('keydown', (e) => {
        if (!modal.classList.contains('is-open')) return;
        if (e.key === 'Escape') { e.preventDefault(); closeModal(); }
    });

    // tabs (subtabs ocio)
    modal.addEventListener('click', (e) => {
        const tab = e.target.closest('.io-tab');
        if (!tab) return;

        const tablist = tab.closest('[data-tab-scope]');
        if (!tablist) return;

        const scope = tablist.dataset.tabScope;
        const paneId = tab.dataset.tab;

        // activa tabs
        tablist.querySelectorAll('.io-tab').forEach(t => {
            const isActive = (t === tab);
            t.classList.toggle('is-active', isActive);
            if (t.getAttribute('role') === 'tab') {
                t.setAttribute('aria-selected', isActive ? 'true' : 'false');
                t.setAttribute('tabindex', isActive ? '0' : '-1');
            }
        });

        // activa panes
        modal.querySelectorAll(`.io-pane[data-tab-scope="${scope}"]`).forEach(p => {
            const isActive = (p.id === paneId);
            p.classList.toggle('is-active', isActive);
            if (p.getAttribute('role') === 'tabpanel') {
                p.setAttribute('aria-hidden', isActive ? 'false' : 'true');
            }
        });
        setActaActionsVisible(paneId === 'tab_ocio_acta');

        // reset scroll (YA NO en io-modal__scroll)
        const ocioScroll = modal.querySelector('.io-modal__scroll[data-ocio-scroll]');
        if (ocioScroll) ocioScroll.scrollTop = 0;

        updateActaActionsVisibility();

    });
    function nowLocalInput() {
        const d = new Date();
        const pad = (n) => String(n).padStart(2, '0');
        const yyyy = d.getFullYear();
        const mm = pad(d.getMonth() + 1);
        const dd = pad(d.getDate());
        const hh = pad(d.getHours());
        const mi = pad(d.getMinutes());
        return `${yyyy}-${mm}-${dd}T${hh}:${mi}`;
    }

    function dbToLocalInput(dt) {
        if (!dt) return '';
        const s = String(dt).replace(' ', 'T');
        return s.slice(0, 16);
    }

    function localInputToDb(dtLocal) {
        if (!dtLocal) return '';
        return String(dtLocal).replace('T', ' ') + ':00';
    }

    function resetGrupoForm() {
        if (hidId) hidId.value = '';
        if (inNombre) inNombre.value = '';
        if (inFecha) inFecha.value = '';
        if (selResp) selResp.value = '';

        // participantes/acta
        cacheParticipantes = [];
        cacheActa = null;
        cachePresentes = new Set();
        incidenciasPendientes = [];

        if (pResultados) pResultados.textContent = '';
        if (pList) pList.innerHTML = `
        <div class="ocio-section-title">
            <div class="ocio-section-title__left">
            <span class="ocio-section-title__dot"></span>
            <div>
                <div class="ocio-section-title__text">Participantes del grupo</div>
                <div class="ocio-section-title__sub">Click en la foto para quitar</div>
            </div>
            </div>
        </div>
        <div class="io-muted">—</div>
        `;


        if (aId) aId.value = '';
        if (aFecha) aFecha.value = '';
        if (aValoracion) aValoracion.value = '';
        if (aIncidencia) aIncidencia.value = '';
        if (aAsistencia) aAsistencia.textContent = '—';
        if (aIncidenciasList) aIncidenciasList.textContent = '—';

        // dejar pestaña "Datos" activa al abrir
        activarSubtab('tab_ocio_datos');
        updateActaActionsVisibility();
        setActaActionsVisible(false);


    }

    function activarSubtab(paneId) {
        const tablist = modal.querySelector('[data-tab-scope="ocio"]');
        if (!tablist) return;
        const tab = tablist.querySelector(`.io-tab[data-tab="${paneId}"]`);
        if (!tab) return;
        tab.click();
    }
    function updateActaActionsVisibility() {
        // Acta está activa si el pane de acta tiene is-active
        const paneActa = modal.querySelector('#tab_ocio_acta');
        const isActa = paneActa?.classList.contains('is-active');

        // ✅ CLAVE: alinear con CSS
        modal.classList.toggle('is-acta', !!isActa);

        // (opcional) si quieres mantener el patrón antiguo también:
        modal.classList.toggle('ocio-show-acta-actions', !!isActa);

        // Extra: export solo si hay acta cargada
        if (btnExportActa) {
            const idActa = aId?.value ? Number(aId.value) : 0;
            btnExportActa.disabled = !idActa;
        }
    }


    // ---------------- Data loaders ----------------
    async function cargarResponsables(selectedId = null) {
        if (!selResp) return;

        selResp.innerHTML = `<option value="">—</option>`;
        try {
            const res = await fetch('/webOrtzadar/public/php/programas/trabajadores_listar.php', {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;

            const json = await res.json();
            if (!json?.ok) return;

            selResp.innerHTML =
                `<option value="">—</option>` +
                (json.data || []).map(t =>
                    `<option value="${escapeHtml(t.id)}">${escapeHtml(`${t.nombre || ''} ${t.apellidos || ''}`.trim())}</option>`
                ).join('');

            if (selectedId != null) selResp.value = String(selectedId);
            // set avatar responsable según selección
            const rid = Number(selResp.value || 0);
            const txt = selResp.options[selResp.selectedIndex]?.textContent || '';
            if (rid) setRespAvatar(rid, txt);
            else {
                if (respAvatar) { respAvatar.classList.add('is-no-photo'); respAvatar.classList.remove('has-photo'); }
                if (respAvatarImg) respAvatarImg.src = '';
                if (respAvatarFallback) respAvatarFallback.textContent = 'TS';
            }

        } catch { }
    }

    async function cargarListado() {
        if (!listEl) return;

        listEl.innerHTML = `<div class="empty">Cargando…</div>`;

        const params = new URLSearchParams({
            grupo: fGrupo?.value || '',
            anio: fAnio?.value || '',
            responsable: fResponsable?.value || ''
        });

        try {
            const res = await fetch(`/webOrtzadar/public/php/programas/ocio_listar.php?${params.toString()}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!res.ok) {
                listEl.innerHTML = `<div class="empty">HTTP ${res.status} (${res.statusText})</div>`;
                return;
            }

            const json = await res.json();
            if (!json?.ok) {
                listEl.innerHTML = `<div class="empty">${escapeHtml(json?.error || 'Error cargando')}</div>`;
                return;
            }

            cacheListado = json.data || [];
            pintarListado(cacheListado);

        } catch {
            listEl.innerHTML = `<div class="empty">No se pudo conectar.</div>`;
        }
    }

    function pintarListado(items) {
        if (!items.length) {
            listEl.innerHTML = `<div class="empty">No hay grupos con esos filtros.</div>`;
            return;
        }

        const ts = Date.now();

        listEl.innerHTML = items.map(g => {
            const id = g.id;
            const nombre = g.nombre || '-';
            const fecha = g.fecha || '-';
            const responsable = g.responsable || '-';
            console.log('foto grupo', id, `${groupPhotoUrl(id)}?ts=${ts}`);


            return `
            <div class="ocio-grupo" data-id="${escapeHtml(id)}">
                <div class="ocio-avatar" data-group-avatar="${escapeHtml(id)}" title="${escapeHtml(nombre)}">
                <img src="${escapeHtml(groupPhotoUrl(id))}?ts=${ts}" alt="${escapeHtml(nombre)}" loading="lazy">
                <span class="ocio-avatar__fallback">${escapeHtml(groupInitials(g))}</span>
                </div>

                <div class="ocio-grupo__main">
                <div class="ocio-grupo__title">${escapeHtml(nombre)}</div>
                <div class="ocio-grupo__meta">
                    <span class="io-muted">Fecha: ${escapeHtml(fecha)}</span>
                    <span class="io-muted">Responsable: ${escapeHtml(responsable)}</span>
                </div>
                </div>

                <div class="ocio-grupo__actions">
                <button class="btn btn-secondary" type="button" data-open="${escapeHtml(id)}">Abrir →</button>
                </div>
            </div>
            `;
        }).join('');

        listEl.querySelectorAll('[data-group-avatar]').forEach(av => {
            const img = av.querySelector('img');
            if (!img) return;

            const setHas = () => { av.classList.add('has-photo'); av.classList.remove('is-no-photo'); };
            const setNo = () => { av.classList.add('is-no-photo'); av.classList.remove('has-photo'); };

            img.addEventListener('load', setHas, { once: true });
            img.addEventListener('error', setNo, { once: true });

            // estado inicial fiable
            if (img.complete) {
                if (img.naturalWidth > 0) setHas();
                else setNo();
            } else {
                // mientras carga, NO fuerces is-no-photo (deja que se vea el loading si aplica)
            }
        });


    }

    // ---------------- Participantes UI ----------------

    function initials(u) {
        const n = `${u.Nombre || ''} ${u.Apellidos || ''}`.trim();
        const parts = n.split(/\s+/).filter(Boolean);
        const a = (parts[0]?.[0] || '').toUpperCase();
        const b = (parts[1]?.[0] || '').toUpperCase();
        return (a + b) || 'U';
    }

    function photoUrl(u) {
        // Preferimos foto_url si viene del backend
        if (u?.foto_url) return String(u.foto_url);
        // Fallback opcional por convención
        return `/webOrtzadar/public/uploads/usuarios/${encodeURIComponent(u.id)}.jpg`;
    }

    function renderUserRow(u, { mode }) {
        // mode: 'result' | 'selected'
        const id = escapeHtml(u.id);
        const name = escapeHtml(fullName(u));
        const dni = escapeHtml(u.Dni || '—');
        const ini = escapeHtml(initials(u));
        const img = escapeHtml(photoUrl(u));

        // RESULTADOS -> Añadir
        if (mode === 'result') {
            return `
            <div class="ocio-tile" data-user="${id}">
                <div class="ocio-tile__avatar">
                <div class="ocio-avatar" title="${name}">
                    <img src="${img}" alt="${name}" loading="lazy"
                    onerror="this.style.display='none'; this.parentElement.classList.add('is-fallback');">
                    <span class="ocio-avatar__fallback">${ini}</span>
                </div>
                </div>

                <div class="ocio-tile__meta">
                <div class="ocio-tile__name">${name}</div>
                <div class="ocio-tile__dni io-muted">${dni}</div>
                </div>

                <div class="ocio-tile__actions">
                <button class="btn btn-secondary" type="button" data-p-add="${id}">Añadir</button>
                </div>
            </div>
            `;
        }

        // SELECCIONADOS -> quitar clicando la foto (con X hover)
        return `
            <div class="ocio-tile" data-user="${id}">
            <div class="ocio-tile__avatar">
                <button class="ocio-avatar is-clickable" type="button"
                data-p-del="${id}" data-del-via="avatar" title="Quitar">
                <img src="${img}" alt="${name}" loading="lazy"
                    onerror="this.style.display='none'; this.parentElement.classList.add('is-fallback');">
                <span class="ocio-avatar__fallback">${ini}</span>
                <span class="ocio-avatar__x">×</span>
                </button>
            </div>

            <div class="ocio-tile__meta">
                <div class="ocio-tile__name">${name}</div>
                <div class="ocio-tile__dni io-muted">${dni}</div>
            </div>
            </div>
        `;
    }

    function renderAsistenciaTile(u) {
        const idNum = Number(u.id);
        const id = escapeHtml(u.id);
        const name = escapeHtml(fullName(u));
        const dni = escapeHtml(u.Dni || '—');
        const ini = escapeHtml(initials(u));
        const img = escapeHtml(photoUrl(u));
        const isChecked = cachePresentes.has(idNum) ? 'is-checked' : '';

        return `
            <div class="ocio-as-tile ${isChecked}" data-as-id="${id}">
            <button class="ocio-as-avatar" type="button" data-as-toggle="${id}" title="Marcar / desmarcar asistencia">
                <img src="${img}" alt="${name}" loading="lazy"
                onerror="this.style.display='none'; this.parentElement.classList.add('is-fallback');">
                <span class="ocio-as-fallback">${ini}</span>
                <span class="ocio-as-check">✓</span>
            </button>

            <div class="ocio-as-meta">
                <div class="ocio-as-name">${name}</div>
                <div class="ocio-as-dni io-muted">${dni}</div>
            </div>
            </div>
        `;
    }


    async function buscarParticipantesLive(q) {
        const idGrupo = hidId?.value;
        if (!idGrupo) { status('Guarda el grupo primero.'); return; }

        const query = (q || '').trim();
        lastQuery = query;

        if (!pResultados) return;

        if (query.length < 2) {
            pResultados.innerHTML = `<div class="io-muted">Escribe al menos 2 caracteres.</div>`;
            return;
        }

        // aborta búsqueda anterior
        if (searchAbort) searchAbort.abort();
        searchAbort = new AbortController();

        pResultados.innerHTML = `<div class="io-muted">Buscando…</div>`;

        try {
            const res = await fetch(
                `/webOrtzadar/public/php/programas/ocio_participantes_buscar.php?q=${encodeURIComponent(query)}`,
                { headers: { 'Accept': 'application/json' }, signal: searchAbort.signal }
            );

            const json = await res.json();
            if (!json?.ok) throw new Error(json?.error || 'Error búsqueda');

            // si el usuario cambió el texto mientras llegaba la respuesta, ignoramos
            if (lastQuery !== query) return;

            // filtra los ya seleccionados
            const selectedIds = new Set(cacheParticipantes.map(x => Number(x.id)));
            const items = (json.data || []).filter(u => !selectedIds.has(Number(u.id)));

            if (!items.length) {
                pResultados.innerHTML = `<div class="io-muted">Sin resultados.</div>`;
                return;
            }

            pResultados.innerHTML = `
            <div class="ocio-users ocio-users--results">

                ${items.map(u => renderUserRow(u, { mode: 'result' })).join('')}
            </div>
            `;
        } catch (e) {
            if (e?.name === 'AbortError') return;
            pResultados.innerHTML = `<div class="io-muted">${escapeHtml(e.message || 'Error búsqueda')}</div>`;
        }
    }

    function fullName(u) {
        const n = `${u.Nombre || ''} ${u.Apellidos || ''}`.trim();
        return n || `Usuario #${u.id}`;
    }

    function pintarParticipantes() {
        if (!pList) return;

        if (!hidId?.value) {
            pList.innerHTML = `<div class="io-muted">Guarda el grupo primero.</div>`;
            return;
        }

        const header = `
            <div class="ocio-section-title">
            <div class="ocio-section-title__left">
                <span class="ocio-section-title__dot"></span>
                <div>
                <div class="ocio-section-title__text">Participantes del grupo</div>
                <div class="ocio-section-title__sub">Click en la foto para quitar</div>
                </div>
            </div>
            </div>
        `;

        if (!cacheParticipantes.length) {
            pList.innerHTML = header + `<div class="io-muted">—</div>`;
            return;
        }

        pList.innerHTML = header + `
            <div class="ocio-users ocio-users--selected">
            ${cacheParticipantes.map(u => renderUserRow(u, { mode: 'selected' })).join('')}
            </div>
        `;
    }




    function pintarAsistencia() {
        if (!aAsistencia) return;

        if (!hidId?.value) {
            aAsistencia.innerHTML = `<div class="io-muted">Guarda el grupo primero.</div>`;
            return;
        }

        if (!cacheParticipantes.length) {
            aAsistencia.innerHTML = `<div class="io-muted">No hay participantes.</div>`;
            return;
        }

        aAsistencia.innerHTML = `
            <div class="ocio-as-grid">
            ${cacheParticipantes.map(u => renderAsistenciaTile(u)).join('')}
            </div>
        `;
    }


    function pintarIncidenciasList(incidenciasServidor = []) {
        if (!aIncidenciasList) return;

        const all = [
            ...incidenciasServidor.map(x => ({ from: 'db', text: x.incidencia, fecha: x.fecha })),
            ...incidenciasPendientes.map((t, pidx) => ({ from: 'new', text: t, fecha: '', pidx }))
        ].filter(x => String(x.text || '').trim() !== '');


        if (!all.length) {
            aIncidenciasList.textContent = '—';
            return;
        }

        aIncidenciasList.innerHTML = `
            <div class="ocio-inc-list">
                ${all.map((x, idx) => {
            const isNew = x.from === 'new';
            const badge = isNew ? ` <span class="ocio-pill ocio-pill--pending">pendiente</span>` : '';
            const fecha = x.fecha ? ` <span class="io-muted">(${escapeHtml(x.fecha)})</span>` : '';
            const del = isNew ? `<button class="ocio-inc-del" type="button" data-inc-del="${x.pidx}">×</button>` : '';


            return `
                    <div class="ocio-inc-item">
                    <div class="ocio-inc-text">
                        ${escapeHtml(x.text)}${fecha}${badge}
                    </div>
                    ${del}
                    </div>
                `;
        }).join('')}
            </div>
            `;

    }

    async function cargarParticipantes(idGrupo) {
        try {
            const res = await fetch(`/webOrtzadar/public/php/programas/ocio_participantes_listar.php?id_grupo=${encodeURIComponent(idGrupo)}`, {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();
            if (!json?.ok) throw new Error(json?.error || 'Error participantes');
            cacheParticipantes = json.data || [];
            pintarParticipantes();
            pintarAsistencia();
        } catch (e) {
            if (pList) pList.innerHTML = `<div class="io-muted">${escapeHtml(e.message || 'Error cargando participantes')}</div>`;
            if (aAsistencia) aAsistencia.innerHTML = `<div class="io-muted">—</div>`;
        }
    }
    async function cargarHistoricoActas(idGrupo) {
        if (!aHist) return;
        aHist.innerHTML = `<div class="io-muted">Cargando…</div>`;

        try {
            const res = await fetch(`/webOrtzadar/public/php/programas/ocio_actas_listar.php?id_grupo=${encodeURIComponent(idGrupo)}`, {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();
            if (!json?.ok) throw new Error(json?.error || 'Error histórico');

            const items = json.data || [];
            if (!items.length) {
                aHist.innerHTML = `<div class="io-muted">No hay actas todavía.</div>`;
                return;
            }

            aHist.innerHTML = `
      <div class="ocio-list">
        ${items.map(a => `
          <div class="ocio-row">
            <div class="ocio-row__name">
              <strong>${escapeHtml(a.fecha || '')}</strong>
              <span class="io-muted"> · presentes: ${escapeHtml(a.n_presentes)} · incidencias: ${escapeHtml(a.n_incidencias)}</span>
            </div>
            <button class="btn btn-secondary" type="button" data-open-acta="${escapeHtml(a.id)}">Abrir</button>
          </div>
        `).join('')}
      </div>
    `;
        } catch (e) {
            aHist.innerHTML = `<div class="io-muted">${escapeHtml(e.message || 'Error')}</div>`;
        }
    }

    async function abrirActaPorId(idActa) {
        status('Cargando acta…');

        try {
            const res = await fetch(`/webOrtzadar/public/php/programas/ocio_acta_get.php?id_acta=${encodeURIComponent(idActa)}`, {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();
            if (!json?.ok) throw new Error(json?.error || 'Error abrir acta');

            const data = json.data || {};
            cacheActa = data.acta || null;
            cacheParticipantes = data.participantes || cacheParticipantes || [];
            cachePresentes = new Set((data.presentes || []).map(n => Number(n)));

            if (aId) aId.value = cacheActa?.id ? String(cacheActa.id) : '';
            if (aFecha) aFecha.value = dbToLocalInput(cacheActa?.fecha || '');
            if (aValoracion) aValoracion.value = cacheActa?.valoracion || '';

            incidenciasPendientes = [];
            pintarParticipantes();
            pintarAsistencia();
            pintarIncidenciasList(data.incidencias || []);

            activarSubtab('tab_ocio_acta');
            updateActaActionsVisibility();

            status('');
        } catch (e) {
            status(e.message || 'Error');
        }
    }

    async function cargarUltimaActa(idGrupo) {
        try {
            const res = await fetch(`/webOrtzadar/public/php/programas/ocio_acta_get_ultima.php?id_grupo=${encodeURIComponent(idGrupo)}`, {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();
            if (!json?.ok) throw new Error(json?.error || 'Error acta');

            const data = json.data || {};
            cacheActa = data.acta || null;

            // participantes “fuente de verdad”
            cacheParticipantes = data.participantes || cacheParticipantes || [];

            // presentes
            cachePresentes = new Set((data.presentes || []).map(n => Number(n)));

            // rellenar campos acta
            if (aId) aId.value = cacheActa?.id ? String(cacheActa.id) : '';
            if (aFecha) aFecha.value = dbToLocalInput(cacheActa?.fecha || '');
            if (aValoracion) aValoracion.value = cacheActa?.valoracion || '';

            // incidencias desde db
            pintarParticipantes();
            pintarAsistencia();
            pintarIncidenciasList(data.incidencias || []);
            updateActaActionsVisibility();

        } catch (e) {
            if (aAsistencia) aAsistencia.innerHTML = `<div class="io-muted">${escapeHtml(e.message || 'Error cargando acta')}</div>`;
            if (aIncidenciasList) aIncidenciasList.textContent = '—';
        }
    }

    // ---------------- Open modal (new/edit) ----------------
    btnNuevoGrupo?.addEventListener('click', async () => {
        resetGrupoForm();

        // reset avatar grupo (sin id)
        if (groupAvatarFallback) groupAvatarFallback.textContent = 'OC';
        if (groupAvatar) { groupAvatar.classList.add('is-no-photo'); groupAvatar.classList.remove('has-photo'); }
        if (groupAvatarImg) groupAvatarImg.src = '';
        if (inFotoGrupo) inFotoGrupo.value = '';

        // reset avatar responsable
        if (respAvatar) { respAvatar.classList.add('is-no-photo'); respAvatar.classList.remove('has-photo'); }
        if (respAvatarImg) respAvatarImg.src = '';
        if (respAvatarFallback) respAvatarFallback.textContent = 'TS';

        await cargarResponsables(null);

        status('');
        if (titleEl) titleEl.textContent = 'Nuevo grupo';
        if (subEl) subEl.textContent = 'Completa y guarda';

        openModal();
        updateActaActionsVisibility();
    });


    listEl?.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-open]');
        const card = e.target.closest('.ocio-grupo');
        const id = btn?.dataset?.open || card?.dataset?.id;
        if (!id) return;

        const g = cacheListado.find(x => String(x.id) === String(id));

        resetGrupoForm();
        if (hidId) hidId.value = String(id);

        await cargarResponsables(g?.id_responsable ?? null);

        if (inNombre) inNombre.value = g?.nombre || '';
        if (inFecha) inFecha.value = dbToLocalInput(g?.fecha || '');
        if (inFotoGrupo) inFotoGrupo.value = '';
        setGrupoAvatar(Number(id), g?.nombre || '');

        // avatar responsable (si hay seleccionado)
        const rid = Number(selResp?.value || 0);
        const rtxt = selResp?.options?.[selResp.selectedIndex]?.textContent || '';
        if (rid) setRespAvatar(rid, rtxt);
        else {
            if (respAvatar) { respAvatar.classList.add('is-no-photo'); respAvatar.classList.remove('has-photo'); }
            if (respAvatarImg) respAvatarImg.src = '';
            if (respAvatarFallback) respAvatarFallback.textContent = 'TS';
        }


        status('');
        if (titleEl) titleEl.textContent = g?.nombre ? `Grupo: ${g.nombre}` : `Grupo #${id}`;
        if (subEl) subEl.textContent = 'Edita y guarda';

        openModal();
        updateActaActionsVisibility();


        // cargar participantes + acta
        await cargarParticipantes(id);
        await cargarUltimaActa(id);
        await cargarHistoricoActas(id);
    });

    // ---------------- Save grupo (delegated in modal base) ----------------
    modal.addEventListener('click', async (e) => {
        const incDelBtn2 = e.target.closest('[data-inc-del]');
        if (incDelBtn2) {
            const pidx = Number(incDelBtn2.dataset.incDel);
            if (Number.isFinite(pidx) && pidx >= 0) {
                if (!confirm('¿Quitar esta incidencia pendiente?')) return;
                incidenciasPendientes.splice(pidx, 1);
                pintarIncidenciasList([]); // repinta (db se volverá a mezclar si está cargada en abrir/cargarUltima)
            }
            return;
        }

        const saveBtn = e.target.closest('[data-action="save"]');
        if (!saveBtn) return;

        saveBtn.disabled = true;

        try {
            const payload = {
                id: hidId?.value ? Number(hidId.value) : null,
                nombre: inNombre?.value || '',
                fecha: localInputToDb(inFecha?.value || ''),
                id_responsable: selResp?.value ? Number(selResp.value) : null
            };

            if (!payload.nombre) { status('Nombre es obligatorio.'); return; }

            status('Guardando grupo…');

            const res = await fetch('/webOrtzadar/public/php/programas/ocio_save_grupo.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (!res.ok) { status(`HTTP ${res.status} (${res.statusText})`); return; }

            const json = await res.json();
            if (!json?.ok) { status(json?.error || 'Error guardando'); return; }

            if (json.data?.id) hidId.value = String(json.data.id);

            status('Grupo guardado ✅');
            await cargarListado();

            // si es nuevo, habilita y prepara tabs de participantes/acta
            const idGrupo = hidId?.value;
            if (idGrupo) {
                await cargarParticipantes(idGrupo);
                await cargarUltimaActa(idGrupo);
            }

            setTimeout(() => status(''), 1200);
        } catch {
            status('No se pudo conectar.');
        } finally {
            saveBtn.disabled = false;
        }
    });
    groupAvatar?.addEventListener('click', () => inFotoGrupo?.click());
    groupAvatar?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            inFotoGrupo?.click();
        }
    });
    selResp?.addEventListener('change', () => {
        const id = Number(selResp.value || 0);
        const txt = selResp.options[selResp.selectedIndex]?.textContent || '';
        if (!id) {
            respAvatar?.classList.add('is-no-photo');
            respAvatar?.classList.remove('has-photo');
            if (respAvatarFallback) respAvatarFallback.textContent = 'TS';
            if (respAvatarImg) respAvatarImg.src = '';
            return;
        }
        setRespAvatar(id, txt);
    });

    inFotoGrupo?.addEventListener('change', async () => {
        const file = inFotoGrupo.files?.[0];
        if (!file) return;

        const idGrupo = Number(hidId?.value || 0);
        if (!idGrupo) { status('Guarda el grupo primero para subir la foto.'); return; }

        // preview
        if (groupAvatarImg) {
            const url = URL.createObjectURL(file);
            groupAvatarImg.onload = () => URL.revokeObjectURL(url);
            groupAvatarImg.src = url;
            groupAvatar?.classList.remove('is-no-photo');
            groupAvatar?.classList.add('has-photo');
        }

        try {
            status('Subiendo foto…');
            await uploadGrupoFoto(idGrupo, file);

            // refrescar avatar (servidor)
            setGrupoAvatar(idGrupo, inNombre?.value || '');

            // refrescar listado para ver la foto ahí también
            await cargarListado();

            status('Foto actualizada ✅');
            setTimeout(() => status(''), 900);
        } catch (e) {
            status(e.message || 'Error subiendo foto');
        }
    });

    // ---------------- Participantes: buscar / añadir / quitar ----------------
    inBuscarP?.addEventListener('input', () => {
        const q = inBuscarP.value || '';
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => buscarParticipantesLive(q), 300);
    });

    // opcional: al enfocar, si ya hay texto, dispara
    inBuscarP?.addEventListener('focus', () => {
        const q = (inBuscarP.value || '').trim();
        if (q.length >= 2) {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => buscarParticipantesLive(q), 150);
        }
    });


    // delegación botones add/del
    modal.addEventListener('click', async (e) => {

        // --- ASISTENCIA: click en avatar O en cualquier parte del tile ---
        const asToggleBtn = e.target.closest('[data-as-toggle]');
        const asTile = e.target.closest('.ocio-as-tile');

        // si clicas en el nombre/DNI (tile), togglear igualmente
        const idAsistencia = asToggleBtn
            ? Number(asToggleBtn.dataset.asToggle)
            : asTile
                ? Number(asTile.dataset.asId)
                : NaN;

        if (Number.isFinite(idAsistencia)) {
            const tile = asTile || asToggleBtn.closest('.ocio-as-tile');
            const next = !cachePresentes.has(idAsistencia);

            if (next) cachePresentes.add(idAsistencia);
            else cachePresentes.delete(idAsistencia);

            tile?.classList.toggle('is-checked', next);
            return;
        }
        const openActaBtn = e.target.closest('[data-open-acta]');
        if (openActaBtn) {
            const idActa = Number(openActaBtn.dataset.openActa);
            if (idActa > 0) abrirActaPorId(idActa);
            return;
        }

        const addBtn = e.target.closest('[data-p-add]');
        const delBtn = e.target.closest('[data-p-del]');
        const idGrupo = hidId?.value;

        if (addBtn) {
            if (!idGrupo) { status('Guarda el grupo primero.'); return; }
            const idUsuario = Number(addBtn.dataset.pAdd);
            status('Añadiendo participante…');

            try {
                const res = await fetch('/webOrtzadar/public/php/programas/ocio_participantes_add.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ id_grupo: Number(idGrupo), id_usuario: idUsuario })
                });
                const json = await res.json();
                if (!json?.ok) throw new Error(json?.error || 'Error añadiendo');

                status('Participante añadido ✅');
                await cargarParticipantes(idGrupo);
                await cargarUltimaActa(idGrupo);
                const q = (inBuscarP?.value || '').trim();
                if (q.length >= 2) buscarParticipantesLive(q);

                setTimeout(() => status(''), 1200);

            } catch (e2) {
                status(e2.message || 'Error');
            }
            return;
        }

        if (delBtn) {
            if (!idGrupo) { status('Guarda el grupo primero.'); return; }
            const idUsuario = Number(delBtn.dataset.pDel);
            const viaAvatar = delBtn.dataset?.delVia === 'avatar';
            if (viaAvatar) {
                const u = cacheParticipantes.find(x => Number(x.id) === Number(idUsuario));
                const name = u ? fullName(u) : `Usuario #${idUsuario}`;
                if (!confirm(`¿Quitar a ${name} del grupo?`)) return;
            }

            status('Quitando participante…');
            try {
                const res = await fetch('/webOrtzadar/public/php/programas/ocio_participantes_del.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ id_grupo: Number(idGrupo), id_usuario: idUsuario, remove_asistencia: true })
                });
                const json = await res.json();
                if (!json?.ok) throw new Error(json?.error || 'Error quitando');

                status('Participante quitado ✅');
                await cargarParticipantes(idGrupo);
                await cargarUltimaActa(idGrupo);
                await cargarHistoricoActas(idGrupo);
                const q = (inBuscarP?.value || '').trim();
                if (q.length >= 2) buscarParticipantesLive(q);


                setTimeout(() => status(''), 1200);
            } catch (e2) {
                status(e2.message || 'Error');
            }
        }
    });
    btnNuevaActa?.addEventListener('click', async () => {
        const idGrupo = hidId?.value;
        if (!idGrupo) { status('Guarda el grupo primero.'); return; }

        // limpia “acta actual” pero NO toca participantes
        cacheActa = null;
        cachePresentes = new Set();
        incidenciasPendientes = [];

        if (aId) aId.value = '';
        if (aFecha) aFecha.value = nowLocalInput();
        if (aValoracion) aValoracion.value = '';
        if (aIncidencia) aIncidencia.value = '';
        if (aIncidenciasList) aIncidenciasList.textContent = '—';

        updateActaActionsVisibility();

        pintarAsistencia();
        activarSubtab('tab_ocio_acta');
        status('Nueva acta preparada');
        setTimeout(() => status(''), 1000);
    });
    btnExportActa?.addEventListener('click', () => {
        const idActa = aId?.value ? Number(aId.value) : 0;
        if (!idActa) { status('Guarda el acta antes de exportar.'); return; }
        window.open(`/webOrtzadar/public/php/programas/ocio_acta_export.php?id_acta=${encodeURIComponent(idActa)}`, '_blank');
    });

    // ---------------- Acta: incidencias + guardar ----------------
    btnAddIncidencia?.addEventListener('click', () => {
        const idGrupo = hidId?.value;
        if (!idGrupo) { status('Guarda el grupo primero.'); return; }

        const txt = (aIncidencia?.value || '').trim();
        if (!txt) { status('Escribe una incidencia.'); return; }

        incidenciasPendientes.push(txt);
        if (aIncidencia) aIncidencia.value = '';
        status('Incidencia añadida (pendiente)');

        // repintar lista uniendo db + pendientes (db se recupera en cargarUltimaActa; aquí mostramos solo pendientes si no hay db)
        pintarIncidenciasList([]);
        setTimeout(() => status(''), 900);
    });


    btnGuardarActa?.addEventListener('click', async () => {
        const idGrupo = hidId?.value;
        if (!idGrupo) { status('Guarda el grupo primero.'); return; }

        // si no hay participantes, no guardamos asistencia
        const presentes = [...cachePresentes].map(n => Number(n));

        const payload = {
            id_acta: aId?.value ? Number(aId.value) : 0,
            id_grupo: Number(idGrupo),
            fecha: localInputToDb(aFecha?.value || ''),
            valoracion: aValoracion?.value || '',
            presentes,
            incidencias: incidenciasPendientes.slice()
        };

        if (!payload.fecha) { status('Fecha de actividad es obligatoria.'); return; }

        status('Guardando acta…');
        btnGuardarActa.disabled = true;

        try {
            const res = await fetch('/webOrtzadar/public/php/programas/ocio_acta_save.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json();
            if (!json?.ok) throw new Error(json?.error || 'Error guardando acta');

            incidenciasPendientes = [];
            status('Acta guardada ✅');

            await cargarUltimaActa(idGrupo);
            await cargarHistoricoActas(idGrupo);

            setTimeout(() => status(''), 1200);
        } catch (e) {
            status(e.message || 'Error');
        } finally {
            btnGuardarActa.disabled = false;
        }
        updateActaActionsVisibility();

    });

    // ---------------- Filters ----------------
    btnAplicar?.addEventListener('click', cargarListado);
    btnLimpiar?.addEventListener('click', () => {
        if (fGrupo) fGrupo.value = '';
        if (fAnio) fAnio.value = '';
        if (fResponsable) fResponsable.value = '';
        cargarListado();
    });

    // ---------------- init ----------------
    cargarListado();
})();
