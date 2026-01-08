/* public/js/apps/programas_vacaciones/programas_vacaciones.js */
(() => {
    const $ = (s, root = document) => root.querySelector(s);

    // ---------------- Listado + filtros ----------------
    const listEl = $('#vacacionesList');
    const fDestino = $('#fDestino');
    const fAnio = $('#fAnio');
    const fResponsable = $('#fResponsable');
    const fIntegrador = $('#fIntegrador');
    const btnAplicar = $('#btnAplicar');
    const btnLimpiar = $('#btnLimpiar');
    const btnNuevoDestino = $('#btnNuevoDestino');

    // ---------------- Modal base ----------------
    const modal = document.querySelector('.io-modal[data-program="vacaciones"]');
    if (!modal) return;

    // ids generados por el modal base (programModal_vacaciones_*)
    const modalId = modal.id; // "programModal_vacaciones"
    const statusEl = document.getElementById(`${modalId}_status`);
    const titleEl = document.getElementById(`${modalId}_title`);
    const subEl = document.getElementById(`${modalId}_sub`);

    // Campos "Datos" (según el modal de Vacaciones que te pasé)
    const hidId = $('#v_id', modal);
    const inNombre = $('#v_nombre', modal);
    const inFecha = $('#v_fecha', modal);
    const selResp = $('#v_id_responsable', modal);
    const selInteg = $('#v_id_integrador', modal);

    // foto
    const inFotoDestino = $('#v_foto', modal) || $('#g_foto', modal); // compat si copiaste ocio
    const destinoAvatar = $('#vacDestinoAvatar', modal);
    const destinoAvatarImg = destinoAvatar?.querySelector('img');
    const destinoAvatarFallback = destinoAvatar?.querySelector('.vac-photo__fallback');


    // avatar responsable (solo existe en modal de ocio; si no existe, no pasa nada)
    const respAvatar = $('#ocioRespAvatar', modal);
    const respAvatarImg = respAvatar?.querySelector('img');
    const respAvatarFallback = respAvatar?.querySelector('.vac-avatar__fallback, .ocio-avatar__fallback');

    // ---------------- Participantes (compat 2 variantes) ----------------
    // Variante Ocio:
    const inBuscarP = $('#p_buscar', modal) || $('#vacPartBuscar', modal);
    const pResultados = $('#p_resultados', modal) || $('#vacPartResultados', modal);
    const pList = $('#p_list', modal) || $('#vacPartList', modal);

    // ---------------- Acta (compat 2 variantes) ----------------
    // Variante Ocio:
    const aId = $('#a_id', modal) || $('#vac_acta_id', modal);
    const aFecha = $('#a_fecha', modal) || $('#vac_acta_fecha', modal);
    const aAsistencia = $('#a_asistencia', modal);
    const aValoracion = $('#a_valoracion', modal) || $('#vac_acta_valoracion', modal);
    const aIncidencia =
        $('#a_incidencia', modal) ||
        $('#vac_inc_text', modal) ||
        $('#vac_acta_incidencia', modal) ||
        $('#vacIncText', modal);

    const aIncidenciasList = $('#a_incidencias_list', modal) || $('#vacIncList', modal);
    const aHist = $('#a_hist', modal);

    // botones acta (compat)
    const btnNuevaActa = $('#btnNuevaActa', modal) || $('#vacBtnNuevaActa', modal);
    const btnAddIncidencia = $('#btnAddIncidencia', modal) || $('#vacBtnNuevaIncidencia', modal);
    const btnGuardarActa = $('#btnGuardarActa', modal) || $('#vacBtnGuardarActa', modal);
    const btnExportActa = $('#btnExportActa', modal) || $('#vacBtnExportActa', modal);

    const subtabsBar = modal.querySelector('.vac-subtabsbar, .ocio-subtabsbar');
    const subtabsActions = modal.querySelector('.vac-subtabs-actions, .ocio-subtabs-actions');



    const respVacAvatar = $('#vacRespAvatar', modal);
    const respVacAvatarImg = respVacAvatar?.querySelector('img');
    const respVacAvatarFallback = respVacAvatar?.querySelector('.vac-mini-avatar__fallback');

    const integVacAvatar = $('#vacIntegAvatar', modal);
    const integVacAvatarImg = integVacAvatar?.querySelector('img');
    const integVacAvatarFallback = integVacAvatar?.querySelector('.vac-mini-avatar__fallback');


    // ids de panes (compat: si copiaste el modal de Ocio o el de Vacaciones)
    const TAB_DATOS = $('#tab_vac_datos', modal) ? 'tab_vac_datos' : 'tab_ocio_datos';
    const TAB_PART = $('#tab_vac_participantes', modal) ? 'tab_vac_participantes' : 'tab_ocio_participantes';
    const TAB_ACTA = $('#tab_vac_acta', modal) ? 'tab_vac_acta' : 'tab_ocio_acta';

    // ---------------- Estado local ----------------
    let cacheListado = [];
    let cacheParticipantes = [];
    let cacheActa = null;
    let cachePresentes = new Set(); // (si tu UI lo usa)
    let incidenciasPendientes = []; // textos “nuevos” antes de guardar
    let cacheIncidenciasServidor = [];

    // ---------------- Helpers ----------------
    function renderUserTile(u, { mode }) {
        const id = escapeHtml(u.id);
        const name = escapeHtml(fullName(u));
        const dni = escapeHtml(u.Dni || '—');
        const ini = escapeHtml(initials(u));
        const img = escapeHtml(photoUrl(u));

        // mode: "result" | "selected"
        if (mode === 'result') {
            return `
            <div class="vac-tile" data-add-user="${id}">
                <div class="vac-tile__avatar">
                <div class="vac-uavatar" title="${name}">
                    <img src="${img}" alt="${name}" loading="lazy"
                    onerror="this.style.display='none'; this.parentElement.classList.add('is-fallback');">
                    <span class="vac-uavatar__fallback">${ini}</span>
                </div>
                </div>
                <div class="vac-tile__meta">
                <div class="vac-tile__name">${name}</div>
                <div class="vac-tile__dni io-muted">${dni}</div>
                </div>
                <div class="vac-tile__actions">
                <span class="io-muted">Añadir</span>
                </div>
            </div>
            `;
        }

        // selected: quitamos clicando el avatar
        return `
            <div class="vac-tile" data-del-user="${id}">
            <div class="vac-tile__avatar">
                <button class="vac-uavatar is-clickable" type="button" data-del-user="${id}" title="Quitar">
                <img src="${img}" alt="${name}" loading="lazy"
                    onerror="this.style.display='none'; this.parentElement.classList.add('is-fallback');">
                <span class="vac-uavatar__fallback">${ini}</span>
                <span class="vac-uavatar__x">×</span>
                </button>
            </div>
            <div class="vac-tile__meta">
                <div class="vac-tile__name">${name}</div>
                <div class="vac-tile__dni io-muted">${dni}</div>
            </div>
            </div>
        `;
    }

    const escapeHtml = (s) =>
        String(s ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

    const status = (msg = '') => {
        if (statusEl) statusEl.textContent = msg;
    };

    function lockBodyScroll(lock) {
        document.body.style.overflow = lock ? 'hidden' : '';
    }

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        lockBodyScroll(true);

        // acciones de acta (solo cuando está activa la pestaña Acta)
        updateActaActionsVisibility();

        // foco (si existe)
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

    const groupPhotoUrl = (id) =>
        `/webOrtzadar/uploads/vacaciones_destinos/${encodeURIComponent(String(id))}.jpg`;

    function destinoInitials(d) {
        const n = String(d?.nombre || '').trim();
        if (!n) return 'VA';
        const parts = n.split(/\s+/).filter(Boolean);
        const a = (parts[0]?.[0] || 'V').toUpperCase();
        const b = (parts[1]?.[0] || 'A').toUpperCase();
        return a + b;
    }

    function setDestinoAvatar(id, nombre) {
        if (!destinoAvatar || !destinoAvatarImg) return;

        if (destinoAvatarFallback) destinoAvatarFallback.textContent = destinoInitials({ nombre });

        // engancha listeners/estado
        syncVacAvatar(destinoAvatar);

        // dispara carga (load/error)
        destinoAvatarImg.src = `${groupPhotoUrl(id)}?ts=${Date.now()}`;
    }


    function initialsFromLabel(labelText, defA = 'R', defB = '') {
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

        imgEl.src = `${respPhotoUrl(workerId)}?ts=${Date.now()}`;

        if (imgEl.complete) {
            if (imgEl.naturalWidth > 0) imgEl.onload();
            else imgEl.onerror();
        }
    }
    function syncMiniAvatarsFromSelects() {
        // Responsable
        if (selResp && respVacAvatar) {
            const id = Number(selResp.value || 0);
            const label = selResp.options?.[selResp.selectedIndex]?.textContent || '';
            setMiniAvatar(respVacAvatar, respVacAvatarImg, respVacAvatarFallback, id, label);
        }

        // Integrador
        if (selInteg && integVacAvatar) {
            const id = Number(selInteg.value || 0);
            const label = selInteg.options?.[selInteg.selectedIndex]?.textContent || '';
            setMiniAvatar(integVacAvatar, integVacAvatarImg, integVacAvatarFallback, id, label);
        }
    }



    const respPhotoUrl = (id) => `/webOrtzadar/uploads/trabajadores/${encodeURIComponent(String(id))}.jpg`;

    function setRespAvatar(id, labelText) {
        if (!respAvatar || !respAvatarImg) return;

        const parts = String(labelText || '').trim().split(/\s+/).filter(Boolean);
        const a = (parts[0]?.[0] || 'T').toUpperCase();
        const b = (parts[1]?.[0] || 'S').toUpperCase();
        if (respAvatarFallback) respAvatarFallback.textContent = a + b;

        respAvatar.classList.add('is-no-photo');
        respAvatar.classList.remove('has-photo');

        respAvatarImg.src = `${respPhotoUrl(id)}?ts=${Date.now()}`;
        respAvatarImg.onload = () => {
            respAvatar.classList.remove('is-no-photo');
            respAvatar.classList.add('has-photo');
        };
        respAvatarImg.onerror = () => {
            respAvatar.classList.add('is-no-photo');
            respAvatar.classList.remove('has-photo');
        };
    }
    function setActaActionsVisible(visible) {
        // ✅ CSS depende de .vac-subtabsbar.is-acta
        subtabsBar?.classList.toggle('is-acta', !!visible);

        // ✅ el HTML tiene "hidden" -> hay que levantarlo
        if (subtabsActions) subtabsActions.hidden = !visible;

        // (opcional) también en el modal si quieres usar tu regla alternativa del final:
        modal.classList.toggle('is-acta', !!visible);
    }

    function updateActaActionsVisibility() {
        // tab activo dentro del scope vacaciones
        const active = modal.querySelector(`.io-subtabs[data-tab-scope="vacaciones"] .io-tab.is-active`);
        const paneId = active?.dataset?.tab || '';
        setActaActionsVisible(paneId === TAB_ACTA);
    }




    selResp?.addEventListener('change', syncMiniAvatarsFromSelects);
    selInteg?.addEventListener('change', syncMiniAvatarsFromSelects);

    // Tabs subtabs
    modal.addEventListener('click', (e) => {
        const tab = e.target.closest('.io-tab');
        if (!tab) return;

        const tablist = tab.closest('[data-tab-scope]');
        if (!tablist) return;

        const scope = tablist.dataset.tabScope;
        const paneId = tab.dataset.tab;
        if (!scope || !paneId) return;

        activarSubtab(scope, paneId);


        if (paneId === TAB_PART) {
            const id = Number(hidId?.value || 0);
            if (id) cargarParticipantes(id);
        }

        if (paneId === TAB_ACTA) {
            const id = Number(hidId?.value || 0);
            if (id) cargarActaUltima(id);
        }
    });

    function activarSubtab(scope, paneId) {
        // Tabs SOLO del scope (vacaciones), NO los del tab-scope="main"
        const tabs = modal.querySelectorAll(`.io-subtabs[data-tab-scope="${scope}"] .io-tab[data-tab]`);
        tabs.forEach((t) => {
            const is = t.dataset.tab === paneId;
            t.classList.toggle('is-active', is);
            t.setAttribute('aria-selected', is ? 'true' : 'false');
            t.tabIndex = is ? 0 : -1;
        });

        // Panes SOLO del scope
        const panes = modal.querySelectorAll(`.io-pane[role="tabpanel"][data-tab-scope="${scope}"]`);
        panes.forEach((p) => {
            const is = p.id === paneId;
            p.classList.toggle('is-active', is);
            p.setAttribute('aria-hidden', is ? 'false' : 'true');
            if (is) p.removeAttribute('hidden');
            else p.setAttribute('hidden', 'hidden');
        });

        // acciones acta
        updateActaActionsVisibility();
    }

    // ---------------- Cargar trabajadores (responsable/integrador + filtros) ----------------
    async function cargarResponsables(selectedId = null) {
        if (!selResp) return;

        selResp.innerHTML = `<option value="">—</option>`;
        try {
            const res = await fetch('/webOrtzadar/public/php/programas/trabajadores_listar.php', {
                headers: { Accept: 'application/json' },
            });
            if (!res.ok) return;

            const json = await res.json();
            if (!json?.ok) return;

            selResp.innerHTML =
                `<option value="">—</option>` +
                (json.data || [])
                    .map(
                        (t) =>
                            `<option value="${escapeHtml(t.id)}">${escapeHtml(`${t.nombre} ${t.apellidos}`.trim())}</option>`
                    )
                    .join('');

            if (selectedId != null) selResp.value = String(selectedId);
            // mini avatar responsable (vacaciones)
            syncMiniAvatarsFromSelects();
            // si hay avatar de responsable (modal ocio), lo actualizamos
            const rid = Number(selResp.value || 0);
            const rtxt = selResp?.options?.[selResp.selectedIndex]?.textContent || '';
            if (rid) setRespAvatar(rid, rtxt);
        } catch {
            /* noop */
        }
    }

    async function cargarIntegradores(selectedId = null) {
        if (!selInteg) return;

        selInteg.innerHTML = `<option value="">—</option>`;
        try {
            const res = await fetch('/webOrtzadar/public/php/programas/trabajadores_listar.php', {
                headers: { Accept: 'application/json' },
            });
            if (!res.ok) return;

            const json = await res.json();
            if (!json?.ok) return;

            selInteg.innerHTML =
                `<option value="">—</option>` +
                (json.data || [])
                    .map(
                        (t) =>
                            `<option value="${escapeHtml(t.id)}">${escapeHtml(`${t.nombre} ${t.apellidos}`.trim())}</option>`
                    )
                    .join('');

            if (selectedId != null) selInteg.value = String(selectedId);
        } catch {
            /* noop */
        }
    }

    async function cargarFiltrosTrabajadores() {
        const targets = [fResponsable, fIntegrador].filter(Boolean);
        if (!targets.length) return;

        try {
            const res = await fetch('/webOrtzadar/public/php/programas/trabajadores_listar.php', {
                headers: { Accept: 'application/json' },
            });
            if (!res.ok) return;

            const json = await res.json();
            if (!json?.ok) return;

            const opts =
                `<option value="">—</option>` +
                (json.data || [])
                    .map(
                        (t) =>
                            `<option value="${escapeHtml(t.id)}">${escapeHtml(`${t.nombre} ${t.apellidos}`.trim())}</option>`
                    )
                    .join('');

            targets.forEach((sel) => {
                const prev = sel.value;
                sel.innerHTML = opts;
                if (prev) sel.value = prev;
            });
        } catch {
            /* noop */
        }
    }

    // ---------------- Listado ----------------
    async function cargarListado() {
        if (!listEl) return;

        listEl.innerHTML = `<div class="empty">Cargando…</div>`;

        const params = new URLSearchParams({
            destino: fDestino?.value || '',
            anio: fAnio?.value || '',
            responsable: fResponsable?.value || '',
            integrador: fIntegrador?.value || '',
        });

        try {
            const res = await fetch(`/webOrtzadar/public/php/programas/vacaciones_listar.php?${params.toString()}`, {
                headers: { Accept: 'application/json' },
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
    function syncVacAvatar(av) {
        const img = av?.querySelector?.('img');
        if (!img) return;

        const setHas = () => {
            av.classList.add('has-photo');
            av.classList.remove('is-no-photo');
        };
        const setNo = () => {
            av.classList.add('is-no-photo');
            av.classList.remove('has-photo');
        };

        // estado inicial
        setNo();

        img.addEventListener('load', setHas, { once: true });
        img.addEventListener('error', setNo, { once: true });

        if (img.complete) {
            if (img.naturalWidth > 0) setHas();
            else setNo();
        }
    }

    async function fetchJson(url, opts = {}) {
        const res = await fetch(url, {
            headers: { Accept: 'application/json', ...(opts.headers || {}) },
            ...opts,
        });

        const text = await res.text();
        const ct = (res.headers.get('content-type') || '').toLowerCase();

        if (!ct.includes('application/json')) {
            throw new Error(
                `No es JSON (HTTP ${res.status}) URL final: ${res.url}\nInicio: ${text.slice(0, 200)}`
            );
        }

        let json;
        try { json = JSON.parse(text); }
        catch {
            throw new Error(`JSON inválido (HTTP ${res.status}) URL final: ${res.url}\nInicio: ${text.slice(0, 200)}`);
        }

        if (!res.ok) throw new Error(json?.error || `HTTP ${res.status}`);
        return json;
    }


    function pintarListado(items) {
        if (!items.length) {
            listEl.innerHTML = `<div class="empty">No hay destinos con esos filtros.</div>`;
            return;
        }

        const ts = Date.now();

        listEl.innerHTML = items
            .map((g) => {
                const id = g.id;
                const nombre = g.nombre || '-';
                const fecha = g.fecha || '-';
                const responsable = g.responsable || '-';
                const integrador = g.integrador || '-';

                return `
          <div class="vac-destino" data-id="${escapeHtml(id)}">
            <div class="vac-avatar" data-destino-avatar="${escapeHtml(id)}" title="${escapeHtml(nombre)}">
              <img src="${escapeHtml(groupPhotoUrl(id))}?ts=${ts}" alt="${escapeHtml(nombre)}" loading="eager">
              <span class="vac-avatar__fallback">${escapeHtml(destinoInitials({ nombre }))}</span>
            </div>

            <div class="vac-destino__main">
              <div class="vac-destino__title">${escapeHtml(nombre)}</div>
              <div class="vac-destino__meta">
                <span class="io-muted">Fecha: ${escapeHtml(fecha)}</span>
                <span class="io-muted">Responsable: ${escapeHtml(responsable)}</span>
                <span class="io-muted">Integrador: ${escapeHtml(integrador)}</span>
              </div>
            </div>

            <div class="vac-destino__actions">
              <button class="btn btn-secondary" type="button" data-open="${escapeHtml(id)}">Abrir →</button>
            </div>
          </div>
        `;
            })
            .join('');

        listEl.querySelectorAll('[data-destino-avatar]').forEach(syncVacAvatar);


    }


    listEl?.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-open]');
        const card = e.target.closest('.vac-destino');

        const id = Number(btn?.dataset.open || card?.dataset.id || 0);
        if (!id) return;

        await openEditarDestino(id);
    });

    // ---------------- Reset formulario modal ----------------
    function resetDestinoForm() {
        if (hidId) hidId.value = '';
        if (inNombre) inNombre.value = '';
        if (inFecha) inFecha.value = '';
        if (selResp) selResp.value = '';
        if (selInteg) selInteg.value = '';

        if (inFotoDestino) inFotoDestino.value = '';

        cacheParticipantes = [];
        cacheActa = null;
        cachePresentes = new Set();
        incidenciasPendientes = [];

        if (pResultados) pResultados.textContent = '';
        if (pList) pList.innerHTML = `
        <div class="vac-section-title">
            <div class="vac-section-title__left">
            <span class="vac-section-title__dot"></span>
            <div>
                <div class="vac-section-title__text">Participantes del grupo</div>
                <div class="vac-section-title__sub">Click en la foto para quitar</div>
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
        if (aHist) aHist.innerHTML = `<div class="io-muted">—</div>`;

        activarSubtab('vacaciones', TAB_DATOS);
        setActaActionsVisible(false);
        cacheIncidenciasServidor = [];

        // avatar destino vacío
        if (destinoAvatarFallback) destinoAvatarFallback.textContent = 'VA';
        if (destinoAvatarImg) destinoAvatarImg.src = '';
        destinoAvatar?.classList.add('is-no-photo');
        destinoAvatar?.classList.remove('has-photo');

        // mini avatares (vacaciones)
        if (respVacAvatar) {
            respVacAvatar.classList.add('is-no-photo');
            respVacAvatar.classList.remove('has-photo');
            if (respVacAvatarImg) respVacAvatarImg.src = '';
            if (respVacAvatarFallback) respVacAvatarFallback.textContent = 'R';
        }

        if (integVacAvatar) {
            integVacAvatar.classList.add('is-no-photo');
            integVacAvatar.classList.remove('has-photo');
            if (integVacAvatarImg) integVacAvatarImg.src = '';
            if (integVacAvatarFallback) integVacAvatarFallback.textContent = 'I';
        }


    }

    // ---------------- Crear / editar ----------------
    btnNuevoDestino?.addEventListener('click', async () => {
        resetDestinoForm();

        await cargarResponsables(null);
        await cargarIntegradores(null);
        syncMiniAvatarsFromSelects();

        status('');
        if (titleEl) titleEl.textContent = 'Destino: nuevo';
        if (subEl) subEl.textContent = 'Rellena y guarda';

        openModal();
    });

    async function openEditarDestino(id) {
        resetDestinoForm();
        status('Cargando…');

        try {
            const res = await fetch(`/webOrtzadar/public/php/programas/vacaciones_get.php?id=${encodeURIComponent(id)}`, {
                headers: { Accept: 'application/json' },
            });
            const json = await res.json();
            if (!json?.ok) throw new Error(json?.error || 'No se pudo cargar');

            const g = json.data;

            if (hidId) hidId.value = String(g.id || id);
            if (inNombre) inNombre.value = g.nombre || '';
            if (inFecha) inFecha.value = (g.fecha || '').replace(' ', 'T').slice(0, 16);

            await cargarResponsables(g?.id_responsable ?? null);
            await cargarIntegradores(g?.id_integrador ?? null);
            syncMiniAvatarsFromSelects();

            // avatar destino
            setDestinoAvatar(g.id || id, g.nombre || '');

            status('');
            if (titleEl) titleEl.textContent = g?.nombre ? `Destino: ${g.nombre}` : `Destino #${id}`;
            if (subEl) subEl.textContent = 'Edita y guarda';

            openModal();

            // precargar participantes si ya estás en su tab
            const activeTab = subtabsBar?.querySelector('.io-tab.is-active');
            if (activeTab?.dataset?.tab === TAB_PART) await cargarParticipantes(id);
            if (activeTab?.dataset?.tab === TAB_ACTA) await cargarActaUltima(id);
        } catch (e) {
            status(e.message || 'Error');
        }
    }

    // botón guardar del modal base
    const btnGuardarBase = document.getElementById(`${modalId}_btnGuardar`);
    btnGuardarBase?.addEventListener('click', () => guardarDestino());

    // botón guardar del partial (si existe)
    const btnGuardarDestino = $('#vacBtnGuardarDestino', modal) || $('#btnGuardarGrupo', modal);
    btnGuardarDestino?.addEventListener('click', () => guardarDestino());

    async function guardarDestino() {
        const id = Number(hidId?.value || 0);
        const nombre = String(inNombre?.value || '').trim();
        if (!nombre) {
            status('⚠️ El nombre es obligatorio');
            return;
        }

        const fechaRaw = String(inFecha?.value || '').trim(); // datetime-local: "YYYY-MM-DDTHH:mm"
        const fecha = fechaRaw ? fechaRaw.replace('T', ' ') + ':00' : null;

        const payload = {
            id: id || 0,
            nombre,
            fecha,
            id_responsable: selResp?.value ? Number(selResp.value) : null,
            id_integrador: selInteg?.value ? Number(selInteg.value) : null,
        };

        status('Guardando…');

        try {
            const res = await fetch('/webOrtzadar/public/php/programas/vacaciones_save_destino.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify(payload),
            });

            const json = await res.json();
            if (!json?.ok) throw new Error(json?.error || 'No se pudo guardar');

            const newId = Number(json?.data?.id || payload.id || 0);
            if (hidId) hidId.value = String(newId);

            status('✅ Guardado');
            if (titleEl) titleEl.textContent = `Destino: ${nombre}`;

            // foto: si hay file seleccionado, subimos
            if (inFotoDestino?.files?.[0] && newId) {
                await subirFotoDestino(newId, inFotoDestino.files[0]);
                if (inFotoDestino) inFotoDestino.value = '';
            }

            // refrescar avatar destino
            if (newId) setDestinoAvatar(newId, nombre);

            await cargarListado();
        } catch (e) {
            status(`❌ ${e.message || 'Error guardando'}`);
        }
    }

    // click en avatar para seleccionar foto
    destinoAvatar?.addEventListener('click', () => {
        inFotoDestino?.click?.();
    });

    inFotoDestino?.addEventListener('change', () => {
        const id = Number(hidId?.value || 0);
        if (!id) return; // aún no guardado
        const f = inFotoDestino.files?.[0];
        if (!f) return;
        subirFotoDestino(id, f);
    });

    async function subirFotoDestino(id, file) {
        status('Subiendo foto…');

        const fd = new FormData();
        fd.append('id', String(id));
        fd.append('file', file);

        try {
            const res = await fetch('/webOrtzadar/public/php/programas/vacaciones_destino_foto_upload.php', {
                method: 'POST',
                body: fd,
            });

            const json = await res.json();
            if (!json?.ok) throw new Error(json?.error || 'No se pudo subir');

            status('✅ Foto actualizada');

            // recargar img
            setDestinoAvatar(id, inNombre?.value || '');
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
        // Preferimos foto_url si viene del backend
        if (u?.foto_url) return String(u.foto_url);
        // Fallback por convención
        return `/webOrtzadar/uploads/usuarios/${encodeURIComponent(String(u.id))}.jpg`;
    }

    function renderResultadosBusqueda(users) {
        if (!pResultados) return;
        if (!users.length) {
            pResultados.innerHTML = `<div class="io-muted">Sin resultados</div>`;
            return;
        }
        pResultados.innerHTML = `
            <div class="vac-users vac-users--results">
            ${users.map(u => renderUserTile(u, { mode: 'result' })).join('')}
            </div>
        `;
    }


    async function cargarParticipantes(idDestino) {
        if (!pList) return;

        pList.innerHTML = `<div class="io-muted">Cargando…</div>`;

        try {
            const json = await fetchJson(
                `/webOrtzadar/public/php/programas/vacaciones_participantes_listar.php?id_destino=${encodeURIComponent(idDestino)}`
            );

            if (!json?.ok) throw new Error(json?.error || 'Error cargando participantes');
            cacheParticipantes = json.data || [];
            pintarParticipantes();
            pintarAsistencia();
        } catch (e) {
            pList.innerHTML = `<div class="io-muted">❌ ${escapeHtml(e.message || 'Error')}</div>`;
        }
    }


    function pintarParticipantes() {
        if (!pList) return;

        // misma lógica que Ocio: si aún no hay destino guardado
        if (!hidId?.value) {
            pList.innerHTML = `<div class="io-muted">Guarda el destino primero.</div>`;
            return;
        }

        // ✅ Header clon Ocio (pero con clases VAC)
        const header = `
            <div class="vac-section-title">
            <div class="vac-section-title__left">
                <span class="vac-section-title__dot"></span>
                <div>
                <div class="vac-section-title__text">Participantes del grupo</div>
                <div class="vac-section-title__sub">Click en la foto para quitar</div>
                </div>
            </div>
            </div>
        `;

        if (!cacheParticipantes.length) {
            pList.innerHTML = header + `<div class="io-muted">—</div>`;
            return;
        }

        pList.innerHTML = header + `
            <div class="vac-users vac-users--selected">
            ${cacheParticipantes.map(u => renderUserTile(u, { mode: 'selected' })).join('')}
            </div>
        `;
    }


    // ----- Asistencia (Acta) -----


    async function addParticipante(idDestino, idUsuario) {
        const json = await fetchJson('/webOrtzadar/public/php/programas/vacaciones_participantes_add.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_destino: idDestino, id_usuario: idUsuario }),
        });
        if (!json?.ok) throw new Error(json?.error || 'No se pudo añadir');
    }

    async function delParticipante(idDestino, idUsuario) {
        const json = await fetchJson('/webOrtzadar/public/php/programas/vacaciones_participantes_del.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_destino: idDestino, id_usuario: idUsuario }),
        });
        if (!json?.ok) throw new Error(json?.error || 'No se pudo quitar');
    }


    async function buscarUsuarios(query) {
        if (!query) return [];
        const json = await fetchJson(
            `/webOrtzadar/public/php/programas/vacaciones_participantes_buscar.php?q=${encodeURIComponent(query)}`
        );
        if (!json?.ok) return [];
        return json.data || [];
    }



    // Buscar (si hay botón) o “live search” (si no hay botón)
    let tSearch = null;
    inBuscarP?.addEventListener('input', () => {
        if (!inBuscarP) return;
        const q = String(inBuscarP.value || '').trim();


        clearTimeout(tSearch);
        tSearch = setTimeout(async () => {
            if (q.length < 2) {
                if (pResultados) pResultados.innerHTML = '';
                return;
            }
            const users = await buscarUsuarios(q);
            renderResultadosBusqueda(users);
        }, 250);
    });



    pResultados?.addEventListener('click', async (e) => {
        const row = e.target.closest('[data-add-user]');
        if (!row) return;

        const idDestino = Number(hidId?.value || 0);
        const idUsuario = Number(row.dataset.addUser || 0);
        if (!idDestino || !idUsuario) return;

        try {
            status('Añadiendo participante…');
            await addParticipante(idDestino, idUsuario);
            status('✅ Participante añadido');
            if (inBuscarP) inBuscarP.value = '';
            if (pResultados) pResultados.innerHTML = '';
            await cargarParticipantes(idDestino);
        } catch (e2) {
            status(`❌ ${e2.message || 'Error'}`);
        }
    });

    pList?.addEventListener('click', async (e) => {
        const row = e.target.closest('[data-del-user]');
        if (!row) return;

        const idDestino = Number(hidId?.value || 0);
        const idUsuario = Number(row.dataset.delUser || 0);
        if (!idDestino || !idUsuario) return;

        if (!confirm('¿Quitar participante?')) return;

        try {
            status('Quitando participante…');
            await delParticipante(idDestino, idUsuario);
            status('✅ Participante quitado');
            await cargarParticipantes(idDestino);
        } catch (e2) {
            status(`❌ ${e2.message || 'Error'}`);
        }
    });

    // toggle asistencia: click en tile o botón
    aAsistencia?.addEventListener('click', (e) => {
        const tile = e.target.closest('.vac-as-tile');
        if (!tile) return;

        // id del usuario: lo sacamos del botón si existe, y si no del data-as-id del tile
        const btn = e.target.closest('[data-as-toggle]') || tile.querySelector('[data-as-toggle]');
        const id = Number(btn?.dataset?.asToggle || tile.dataset.asId || 0);
        if (!id) return;

        // toggle en Set
        if (cachePresentes.has(id)) cachePresentes.delete(id);
        else cachePresentes.add(id);

        // actualizar clase visual sin repintar todo
        tile.classList.toggle('is-checked', cachePresentes.has(id));

        // opcional accesibilidad
        if (btn) btn.setAttribute('aria-pressed', cachePresentes.has(id) ? 'true' : 'false');
    });




    // ---------------- Actas + incidencias ----------------
    function renderAsistenciaTile(u) {
        const idNum = Number(u.id);
        const id = escapeHtml(u.id);
        const name = escapeHtml(fullName(u));
        const dni = escapeHtml(u.Dni || '—');
        const ini = escapeHtml(initials(u));
        const img = escapeHtml(photoUrl(u));
        const isChecked = cachePresentes.has(idNum) ? 'is-checked' : '';

        return `
    <div class="vac-as-tile ${isChecked}" data-as-id="${id}">
      <button class="vac-as-avatar" type="button" data-as-toggle="${id}" title="Marcar / desmarcar asistencia">
        <img src="${img}" alt="${name}" loading="lazy"
          onerror="this.style.display='none'; this.parentElement.classList.add('is-fallback');">
        <span class="vac-as-fallback">${ini}</span>
        <span class="vac-as-check">✓</span>
      </button>
      <div class="vac-as-meta">
        <div class="vac-as-name">${name}</div>
        <div class="vac-as-dni io-muted">${dni}</div>
      </div>
    </div>
  `;
    }

    function pintarAsistencia() {
        if (!aAsistencia) return;

        const idDestino = Number(hidId?.value || 0);
        if (!idDestino) {
            aAsistencia.innerHTML = `<div class="io-muted">Guarda el destino primero.</div>`;
            return;
        }

        if (!cacheParticipantes.length) {
            aAsistencia.innerHTML = `<div class="io-muted">No hay participantes.</div>`;
            return;
        }

        aAsistencia.innerHTML = `
    <div class="vac-as-grid">
      ${cacheParticipantes.map(u => renderAsistenciaTile(u)).join('')}
    </div>
  `;
    }


    async function cargarActaUltima(idDestino) {
        if (aHist) aHist.innerHTML = `<div class="io-muted">Cargando…</div>`;
        try {
            const res = await fetch(
                `/webOrtzadar/public/php/programas/vacaciones_acta_get_ultima.php?id_destino=${encodeURIComponent(idDestino)}`,
                { headers: { Accept: 'application/json' } }
            );
            const json = await res.json();
            if (!json?.ok) throw new Error(json?.error || 'Error cargando acta');
            cacheIncidenciasServidor = json.data?.incidencias || [];

            cacheActa = json.data?.acta || null;
            cacheParticipantes = json.data?.participantes || [];
            cachePresentes = new Set((json.data?.presentes || []).map(n => Number(n)));
            incidenciasPendientes = [];

            pintarActa(cacheActa, cacheIncidenciasServidor);

            pintarParticipantes();
            pintarAsistencia();
            await cargarHistActas(idDestino);
            updateActaActionsVisibility();
        } catch (e) {
            if (aHist) aHist.innerHTML = `<div class="io-muted">${escapeHtml(e.message || 'Error')}</div>`;
        }
    }

    function pintarActa(acta, incidenciasServidor = []) {
        if (aId) aId.value = acta?.id ? String(acta.id) : '';
        if (aFecha) aFecha.value = (acta?.fecha || '').replace(' ', 'T').slice(0, 16);
        if (aValoracion) aValoracion.value = acta?.valoracion || '';

        // asistencia (chips)
        pintarAsistencia();

        pintarIncidenciasList(incidenciasServidor);
    }

function pintarIncidenciasList(incidenciasServidor = []) {
  if (!aIncidenciasList) return;

  const all = [
    ...(incidenciasServidor || []).map(x => ({ from: 'db', text: x.incidencia, fecha: x.fecha })),
    ...(incidenciasPendientes || []).map((t, pidx) => ({ from: 'new', text: t, fecha: '', pidx })),
  ].filter(x => String(x.text || '').trim() !== '');

  if (!all.length) {
    aIncidenciasList.textContent = '—';
    return;
  }

  aIncidenciasList.innerHTML = `
    <div class="vac-inc-list">
      ${all.map(x => {
        const isNew = x.from === 'new';
        const badge = isNew ? ` <span class="vac-pill vac-pill--pending">pendiente</span>` : '';
        const fecha = x.fecha ? ` <span class="io-muted">(${escapeHtml(x.fecha)})</span>` : '';
        const del = isNew ? `<button class="vac-inc-del" type="button" data-del-inc="${escapeHtml(x.pidx)}">×</button>` : '';

        return `
          <div class="vac-inc-item">
            <div class="vac-inc-text">
              ${escapeHtml(x.text)}${fecha}${badge}
            </div>
            ${del}
          </div>
        `;
      }).join('')}
    </div>
  `;
}



    aIncidenciasList?.addEventListener('click', (e) => {
        const b = e.target.closest('[data-del-inc]');
        if (!b) return;
        const idx = Number(b.dataset.delInc);
        if (!Number.isFinite(idx)) return;
        incidenciasPendientes = incidenciasPendientes.filter((_, i) => i !== idx);
        pintarIncidenciasList(cacheIncidenciasServidor);

    });

    btnNuevaActa?.addEventListener('click', () => {
        // crea acta “en blanco”
        if (aId) aId.value = '';
        if (aFecha) aFecha.value = '';
        if (aValoracion) aValoracion.value = '';
        cachePresentes = new Set();
        pintarAsistencia();
        incidenciasPendientes = [];
        cacheIncidenciasServidor = [];

        if (aIncidenciasList) aIncidenciasList.innerHTML = `<div class="io-muted">—</div>`;
        status('Acta nueva (sin guardar)');
    });

    btnAddIncidencia?.addEventListener('click', () => {
        const value = String(aIncidencia?.value || '').trim();
        if (!value) {
            status('⚠️ Escribe una incidencia primero');
            aIncidencia?.focus?.();
            return;
        }

        incidenciasPendientes.push(value);
        aIncidencia.value = '';
        status('');

        // ✅ repintar sin recargar
        pintarIncidenciasList(cacheIncidenciasServidor);
    });


    btnGuardarActa?.addEventListener('click', async () => {
        const idDestino = Number(hidId?.value || 0);
        if (!idDestino) return;

        const idActa = Number(aId?.value || 0);
        const fechaRaw = String(aFecha?.value || '').trim();
        const fecha = fechaRaw ? fechaRaw.replace('T', ' ') + ':00' : null;

        const payload = {
            id_acta: idActa || 0,
            id_destino: idDestino,
            fecha,
            valoracion: String(aValoracion?.value || '').trim(),
            presentes: [...cachePresentes].map(n => Number(n)),
            incidencias: incidenciasPendientes.slice(),
        };

        status('Guardando acta…');

        try {
            const res = await fetch('/webOrtzadar/public/php/programas/vacaciones_acta_save.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify(payload),
            });
            const json = await res.json();
            if (!json?.ok) throw new Error(json?.error || 'No se pudo guardar acta');

            status('✅ Acta guardada');
            incidenciasPendientes = [];
            await cargarActaUltima(idDestino);
        } catch (e) {
            status(`❌ ${e.message || 'Error guardando acta'}`);
        }
    });

    btnExportActa?.addEventListener('click', () => {
        const idActa = Number(aId?.value || 0);
        if (!idActa) {
            status('⚠️ No hay acta para exportar');
            return;
        }
        window.open(
            `/webOrtzadar/public/php/programas/vacaciones_acta_export.php?id_acta=${encodeURIComponent(idActa)}`,
            '_blank'
        );
    });

    async function cargarHistActas(idDestino) {
        if (!aHist) return;
        aHist.innerHTML = `<div class="io-muted">Cargando…</div>`;

        try {
            const res = await fetch(
                `/webOrtzadar/public/php/programas/vacaciones_actas_listar.php?id_destino=${encodeURIComponent(idDestino)}`,
                { headers: { Accept: 'application/json' } }
            );
            const json = await res.json();
            if (!json?.ok) throw new Error(json?.error || 'Error cargando histórico');

            const rows = json.data || [];
            if (!rows.length) {
                aHist.innerHTML = `<div class="io-muted">—</div>`;
                return;
            }

            aHist.innerHTML = `
            <div class="vac-list">
                ${(rows || []).map(a => `
                <div class="vac-row">
                    <div class="vac-row__name">
                    <strong>${escapeHtml((a.fecha || '').slice(0,16)) || '—'}</strong>
                    <span class="io-muted"> · presentes: ${escapeHtml(a.n_presentes ?? '—')} · incidencias: ${escapeHtml(a.n_incidencias ?? '—')}</span>
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

    aHist?.addEventListener('click', async (e) => {
        const chip = e.target.closest('[data-open-acta]');
        if (!chip) return;

        const idActa = Number(chip.dataset.openActa || 0);
        if (!idActa) return;

        try {
            status('Cargando acta…');
            const res = await fetch(
                `/webOrtzadar/public/php/programas/vacaciones_acta_get.php?id_acta=${encodeURIComponent(idActa)}`,
                { headers: { Accept: 'application/json' } }
            );
            const json = await res.json();
            if (!json?.ok) throw new Error(json?.error || 'Error cargando acta');

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
        if (fDestino) fDestino.value = '';
        if (fAnio) fAnio.value = '';
        if (fResponsable) fResponsable.value = '';
        if (fIntegrador) fIntegrador.value = '';
        cargarListado();
    });

    // ---------------- init ----------------
    cargarFiltrosTrabajadores();
    cargarResponsables();
    cargarIntegradores();
    cargarListado();
})();
