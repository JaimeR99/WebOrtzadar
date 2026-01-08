// public/js/apps/components/user_modal.base.js
(() => {
  const $ = (sel) => document.querySelector(sel);

  // ---------------- Utils ----------------
  function escapeHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, (m) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[m]));
  }

  function initials(nombre) {
    const parts = String(nombre || '').split(' ').filter(Boolean);
    const a = parts[0]?.[0] || '';
    const b = parts[1]?.[0] || '';
    return (a + b).toUpperCase() || 'IO';
  }

  function dtToLocalInput(dt) {
    if (!dt) return '';
    const s = String(dt).replace(' ', 'T');
    return s.slice(0, 16);
  }

  function localInputToDb(dtLocal) {
    if (!dtLocal) return '';
    return String(dtLocal).replace('T', ' ') + ':00';
  }

  function dbToDateInput(d) {
    if (!d) return '';
    return String(d).slice(0, 10);
  }

  function dateToIntYYYYMMDD(d) {
    if (!d) return null;
    return Number(String(d).replaceAll('-', ''));
  }

  function intYYYYMMDDToDate(n) {
    if (!n) return '';
    const s = String(n);
    if (s.length !== 8) return '';
    return `${s.slice(0, 4)}-${s.slice(4, 6)}-${s.slice(6, 8)}`;
  }

  function setVal(id, v) {
    const el = document.getElementById(id);
    if (!el) return;
    el.value = v ?? '';
  }

  function setText(id, v) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = v ?? '';
  }

  // ---------------- Modal base ----------------
  function lockBodyScroll(lock) {
    if (lock) document.body.style.overflow = 'hidden';
    else document.body.style.overflow = '';
  }

  function getFocusable(root) {
    const sel = [
      'a[href]',
      'button:not([disabled])',
      'input:not([disabled])',
      'select:not([disabled])',
      'textarea:not([disabled])',
      '[tabindex]:not([tabindex="-1"])'
    ].join(',');
    return [...root.querySelectorAll(sel)].filter(el => el.offsetParent !== null);
  }

  // ---------------- Tabs PRO (ARIA + teclado + reset scroll) ----------------
  function setAriaTabs(modal, tablist, activeTab) {
    const tabs = [...tablist.querySelectorAll('.io-tab[role="tab"]')];
    tabs.forEach(t => {
      const isActive = (t === activeTab);
      t.classList.toggle('is-active', isActive);
      t.setAttribute('aria-selected', isActive ? 'true' : 'false');
      t.setAttribute('tabindex', isActive ? '0' : '-1');
    });
  }

  function setAriaPanels(modal, scope, activePaneId) {
    modal.querySelectorAll(`.io-pane[data-tab-scope="${scope}"][role="tabpanel"]`)
      .forEach(p => {
        const isActive = (p.id === activePaneId);
        p.classList.toggle('is-active', isActive);
        p.setAttribute('aria-hidden', isActive ? 'false' : 'true');
      });
  }

  function getActiveMainPane(modal) {
    return modal?.querySelector('.io-pane[data-tab-scope="main"].is-active');
  }

  function getScrollerForEventTarget(modal, targetEl) {
    const mainPane = targetEl?.closest?.('.io-pane[data-tab-scope="main"]') || getActiveMainPane(modal);
    return mainPane?.querySelector('.io-modal__scroll') || null;
  }

  function resetScrollForTarget(modal, targetEl) {
    const scroller = getScrollerForEventTarget(modal, targetEl);
    if (scroller) scroller.scrollTop = 0;
  }

  function activateTab(modal, scope, paneId) {
    const tablist = modal.querySelector(`[data-tab-scope="${scope}"]`);
    if (!tablist) return;
    const tab = tablist.querySelector(`.io-tab[data-tab="${paneId}"]`);
    if (!tab) return;

    setAriaTabs(modal, tablist, tab);
    setAriaPanels(modal, scope, paneId);

    // importantísimo: al cambiar tab, vuelve arriba
    const scroller = getActiveMainPane(modal)?.querySelector('.io-modal__scroll');
    if (scroller) scroller.scrollTop = 0;
  }

  // ---------------- Foto UI ----------------
  function userPhotoUrl(idUsuario) {
    if (!idUsuario) return '';
    return `uploads/usuarios/${encodeURIComponent(idUsuario)}.jpg?v=${Date.now()}`;
  }

  function setPhotoPreview(dataUrl) {
    const img = document.getElementById('u_photoImg');
    const fb = document.getElementById('u_photoFallback');
    if (!img || !fb) return;

    if (dataUrl) {
      img.src = dataUrl;
      img.style.display = 'block';
      fb.style.display = 'none';
    } else {
      img.removeAttribute('src');
      img.style.display = 'none';
      fb.style.display = 'grid';
    }
  }

  function setPhotoFromUserId(idUsuario) {
    const img = document.getElementById('u_photoImg');
    const fb = document.getElementById('u_photoFallback');
    if (!img || !fb) return;

    const url = userPhotoUrl(idUsuario);
    if (!url) {
      setPhotoPreview(null);
      return;
    }

    img.onload = () => {
      img.style.display = 'block';
      fb.style.display = 'none';
    };
    img.onerror = () => {
      img.removeAttribute('src');
      img.style.display = 'none';
      fb.style.display = 'grid';
    };

    img.src = url;
  }

  function setPhotoInitialsFromName(fullName) {
    const el = document.getElementById('u_photoInitials');
    if (!el) return;
    el.textContent = initials(fullName || '—');
  }

  // ---------------- Diagnósticos: colapsables ----------------
  function bindDiagToggle(chkId, cardSelector) {
    const chk = document.getElementById(chkId);
    const card = document.querySelector(cardSelector);
    if (!chk || !card) return;

    const apply = () => card.classList.toggle('is-off', !chk.checked);
    chk.addEventListener('change', apply);
    apply();
  }

  // =======================
  // API pública (reusable)
  // =======================
  window.UserModalBase = {
    init(cfg) {
      const modal = $(cfg.modalSelector || '#ioModal');
      const modalDialog = modal?.querySelector('.io-modal__dialog');

      const statusEl = document.getElementById(cfg.statusElId || 'ioModalStatus');
      const hiddenUserId = document.getElementById(cfg.hiddenUserIdId || 'ioModalUserId');

      const chkDisc = document.getElementById('chk_discapacidad');
      const chkDep = document.getElementById('chk_dependencia');
      const chkExc = document.getElementById('chk_exclusion');

      let lastFocusEl = null;
      let photoUIInited = false;

      function status(msg) { if (statusEl) statusEl.textContent = msg || ''; }

      async function saveUserPhoto(file) {
        const userId = hiddenUserId?.value;
        if (!userId) {
          status('Guarda primero el usuario.');
          return;
        }

        const formData = new FormData();
        formData.append('id_usuario', userId);
        formData.append('foto', file);

        status('Guardando foto…');

        try {
          const res = await fetch(cfg.endpoints.savePhoto, { method: 'POST', body: formData });
          const json = await res.json();

          if (!json?.ok) {
            status(json?.error || 'Error guardando foto');
            return;
          }

          setPhotoFromUserId(userId);
          status('Foto actualizada ✅');
          setTimeout(() => status(''), 1200);
        } catch {
          status('No se pudo subir la foto');
        }
      }

      function initPhotoUI() {
        if (photoUIInited) return;
        photoUIInited = true;

        const card = document.getElementById('u_photoCard');
        const file = document.getElementById('u_photoFile');
        if (!card || !file) return;

        const openPicker = () => file.click();

        card.addEventListener('click', openPicker);
        card.addEventListener('keydown', (e) => {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            openPicker();
          }
        });

        file.addEventListener('change', () => {
          const f = file.files?.[0];
          if (!f) return;

          const userId = hiddenUserId?.value;
          if (!userId) {
            status('Primero crea/guarda el usuario para poder subir foto.');
            file.value = '';
            return;
          }

          if (!f.type.startsWith('image/')) {
            status('Archivo no válido (debe ser imagen).');
            file.value = '';
            return;
          }

          const reader = new FileReader();
          reader.onload = () => {
            setPhotoPreview(String(reader.result || ''));
            saveUserPhoto(f);
          };
          reader.readAsDataURL(f);
        });
      }

      // bind diag toggles (idéntico)
      bindDiagToggle('chk_discapacidad', '[data-diag="discapacidad"]');
      bindDiagToggle('chk_dependencia', '[data-diag="dependencia"]');
      bindDiagToggle('chk_exclusion', '[data-diag="exclusion"]');

      function openModal() {
        lastFocusEl = document.activeElement;
        modal?.classList.add('is-open');
        modal?.setAttribute('aria-hidden', 'false');
        lockBodyScroll(true);
        initPhotoUI();

        const focusables = modalDialog ? getFocusable(modalDialog) : [];
        (focusables[0] || modalDialog)?.focus?.();
      }

      function closeModal() {
        modal?.classList.remove('is-open');
        modal?.setAttribute('aria-hidden', 'true');
        lockBodyScroll(false);

        if (lastFocusEl && typeof lastFocusEl.focus === 'function') lastFocusEl.focus();
        lastFocusEl = null;
      }

      // cerrar por overlay / botones
      modal?.addEventListener('click', (e) => {
        if (e.target?.dataset?.close) closeModal();
      });

      document.addEventListener('keydown', (e) => {
        if (!modal?.classList.contains('is-open')) return;

        if (e.key === 'Escape') {
          e.preventDefault();
          closeModal();
          return;
        }

        if (e.key === 'Tab' && modalDialog) {
          const focusables = getFocusable(modalDialog);
          if (!focusables.length) return;

          const first = focusables[0];
          const last = focusables[focusables.length - 1];

          if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
          } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
          }
        }
      });

      // click tabs (idéntico)
      modal?.addEventListener('click', (e) => {
        const tab = e.target.closest('.io-tab');
        if (!tab) return;

        const tablist = tab.closest('[data-tab-scope]');
        if (!tablist) return;

        const scope = tablist.dataset.tabScope;
        const id = tab.dataset.tab;

        setAriaTabs(modal, tablist, tab);
        setAriaPanels(modal, scope, id);

        resetScrollForTarget(modal, tab);
      });

      // teclas tabs (idéntico)
      modal?.addEventListener('keydown', (e) => {
        const tab = e.target.closest?.('.io-tab[role="tab"]');
        if (!tab) return;

        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(e.key)) return;

        const tablist = tab.closest('[data-tab-scope]');
        if (!tablist) return;

        const tabs = [...tablist.querySelectorAll('.io-tab[role="tab"]')];
        const idx = tabs.indexOf(tab);

        let next = idx;
        if (e.key === 'ArrowLeft') next = Math.max(0, idx - 1);
        if (e.key === 'ArrowRight') next = Math.min(tabs.length - 1, idx + 1);
        if (e.key === 'Home') next = 0;
        if (e.key === 'End') next = tabs.length - 1;

        e.preventDefault();
        tabs[next]?.click();
        tabs[next]?.focus();
      });

      // -------- Rellenar SOLO Usuario + Diag (idéntico a tu bloque) --------
      function rellenarUsuarioYDiag(data) {
        const u = data.usuario || {};
        const diag = data.diag || {};

        const nombreFull = `${u.Nombre || ''} ${u.Apellidos || ''}`.trim() || '—';

        setPhotoInitialsFromName(nombreFull);
        setPhotoFromUserId(u.id);

        setText('ioModalNombre', nombreFull);
        setText('ioModalSub', `ID: ${u.id ?? '—'} · DNI: ${u.Dni ?? '—'}`);
        setText('ioModalAvatar', initials(nombreFull));

        setText('u_displayName', nombreFull);
        setText('u_emailPreview', u.Correo ?? '—');
        setText('u_estado', u.Estado ?? 'Activo');
        setText('u_id', u.id ?? '—');

        // Usuario
        setVal('u_Nombre', u.Nombre);
        setVal('u_Apellidos', u.Apellidos);
        setVal('u_Dni', u.Dni);
        setVal('u_Sexo', u.Sexo);
        setVal('u_Fecha_Nacimiento', dbToDateInput(u.Fecha_Nacimiento));
        setVal('u_Nacionalidad', u.Nacionalidad);
        setVal('u_Fecha_Alta', dtToLocalInput(u.Fecha_Alta));

        setVal('u_Telefono_Usuario', u.Telefono_Usuario);
        setVal('u_Correo', u.Correo);
        setVal('u_Direccion', u.Direccion);
        setVal('u_Codigo_Postal', u.Codigo_Postal);

        setVal('u_Telefono_Familia1', u.Telefono_Familia1);
        setVal('u_Telefono_Familia2', u.Telefono_Familia2);
        setVal('u_Telefono_Servicios_Sociales', u.Telefono_Servicios_Sociales);
        setVal('u_Telefono_Trabajadora_Social', u.Telefono_Trabajadora_Social);
        setVal('u_Telefono_Centro_Salud', u.Telefono_Centro_Salud);
        setVal('u_Telefono_Medico_Cavecera', u.Telefono_Medico_Cavecera);
        setVal('u_Telefono_Salud_Mental', u.Telefono_Salud_Mental);
        setVal('u_Telefono_Referente_Salud', u.Telefono_Referente_Salud);
        setVal('u_Telefono_Referente_Formativo', u.Telefono_Referente_Formativo);
        setVal('u_Telefono_Otros1', u.Telefono_Otros1);
        setVal('u_Telefono_Otros2', u.Telefono_Otros2);

        setVal('u_CCC', u.CCC);
        setVal('u_N_TIS', u.N_TIS);
        setVal('u_Nivel_Estudios', u.Nivel_Estudios);
        setVal('u_Tipo_Socio', u.Tipo_Socio);
        setVal('u_ID_Situacion_Administrativa', u.ID_Situacion_Administrativa);
        setVal('u_ID_Via_Comunicacion', u.ID_Via_Comunicacion);

        // Diagnósticos: FKs en usuario
        setVal('u_ID_DIAG_Discapacidad', u.ID_DIAG_Discapacidad ?? '');
        setVal('u_ID_DIAG_Dependencia', u.ID_DIAG_Dependencia ?? '');
        setVal('u_ID_DIAG_Exclusion', u.ID_DIAG_Exclusion ?? '');

        setVal('u_ID_DIAG_Discapacidad_readonly', u.ID_DIAG_Discapacidad ?? '');
        setVal('u_ID_DIAG_Dependencia_readonly', u.ID_DIAG_Dependencia ?? '');
        setVal('u_ID_DIAG_Exclusion_readonly', u.ID_DIAG_Exclusion ?? '');

        const disc = diag.discapacidad || null;
        const dep = diag.dependencia || null;
        const exc = diag.exclusion || null;

        if (chkDisc) chkDisc.checked = !!disc;
        if (chkDep) chkDep.checked = !!dep;
        if (chkExc) chkExc.checked = !!exc;

        document.querySelector('[data-diag="discapacidad"]')?.classList.toggle('is-off', !chkDisc?.checked);
        document.querySelector('[data-diag="dependencia"]')?.classList.toggle('is-off', !chkDep?.checked);
        document.querySelector('[data-diag="exclusion"]')?.classList.toggle('is-off', !chkExc?.checked);

        // Discapacidad
        setVal('disc_porcentaje', disc?.Porcentaje ?? '');
        setVal('disc_diagnostico', disc?.Diagnostico ?? '');
        setVal('disc_fecha_reconocimiento', dbToDateInput(disc?.Fecha_Reconocimiento));
        setVal('disc_fecha_caducidad', dbToDateInput(disc?.Fecha_Caducidad));
        setVal('disc_descripcion', disc?.Descripcion ?? '');

        // Dependencia
        setVal('dep_grado', dep?.Grado ?? '');
        setVal('dep_fecha_reconocimiento', dbToDateInput(dep?.Fecha_Reconocimiento));
        setVal('dep_fecha_caducidad', dbToDateInput(dep?.Fecha_Caducidad));
        setVal('dep_descripcion', dep?.Descripcion ?? '');

        // Exclusión
        setVal('exc_tipo', exc?.Tipo ?? '');
        setVal('exc_fecha_reconocimiento', intYYYYMMDDToDate(exc?.Fecha_Reconocimiento));
        setVal('exc_fecha_caducidad', intYYYYMMDDToDate(exc?.Fecha_Caducidad));
        setVal('exc_descripcion', exc?.Descripcion ?? '');
      }

      // -------- Payload SOLO Usuario + Diag (idéntico a tu payload actual) --------
      function buildUsuarioDiagPayload() {
        return {
          usuario: {
            Nombre: $('#u_Nombre')?.value || '',
            Apellidos: $('#u_Apellidos')?.value || '',
            Dni: $('#u_Dni')?.value || '',
            Sexo: $('#u_Sexo')?.value || '',
            Direccion: $('#u_Direccion')?.value || '',
            Codigo_Postal: $('#u_Codigo_Postal')?.value || '',
            Fecha_Nacimiento: $('#u_Fecha_Nacimiento')?.value || '',
            Nacionalidad: $('#u_Nacionalidad')?.value || '0',

            Telefono_Usuario: $('#u_Telefono_Usuario')?.value || '',
            Telefono_Familia1: $('#u_Telefono_Familia1')?.value || '',
            Telefono_Familia2: $('#u_Telefono_Familia2')?.value || '',
            Telefono_Servicios_Sociales: $('#u_Telefono_Servicios_Sociales')?.value || '',
            Telefono_Trabajadora_Social: $('#u_Telefono_Trabajadora_Social')?.value || '',
            Telefono_Centro_Salud: $('#u_Telefono_Centro_Salud')?.value || '',
            Telefono_Medico_Cavecera: $('#u_Telefono_Medico_Cavecera')?.value || '',
            Telefono_Salud_Mental: $('#u_Telefono_Salud_Mental')?.value || '',
            Telefono_Referente_Salud: $('#u_Telefono_Referente_Salud')?.value || '',
            Telefono_Referente_Formativo: $('#u_Telefono_Referente_Formativo')?.value || '',
            Telefono_Otros1: $('#u_Telefono_Otros1')?.value || '',
            Telefono_Otros2: $('#u_Telefono_Otros2')?.value || '',

            Correo: $('#u_Correo')?.value || '',
            CCC: $('#u_CCC')?.value || '',
            N_TIS: $('#u_N_TIS')?.value || '',

            Nivel_Estudios: $('#u_Nivel_Estudios')?.value ? Number($('#u_Nivel_Estudios').value) : 0,
            Tipo_Socio: $('#u_Tipo_Socio')?.value ? Number($('#u_Tipo_Socio').value) : 0,
            ID_Situacion_Administrativa: $('#u_ID_Situacion_Administrativa')?.value ? Number($('#u_ID_Situacion_Administrativa').value) : 0,
            ID_Via_Comunicacion: $('#u_ID_Via_Comunicacion')?.value ? Number($('#u_ID_Via_Comunicacion').value) : 0,

            Fecha_Alta: localInputToDb($('#u_Fecha_Alta')?.value || '')
          },
          diag: {
            discapacidad: {
              enabled: !!document.getElementById('chk_discapacidad')?.checked,
              Porcentaje: $('#disc_porcentaje')?.value ? Number($('#disc_porcentaje').value) : 0,
              Diagnostico: $('#disc_diagnostico')?.value || '',
              Fecha_Reconocimiento: $('#disc_fecha_reconocimiento')?.value || '',
              Fecha_Caducidad: $('#disc_fecha_caducidad')?.value || '',
              Descripcion: $('#disc_descripcion')?.value || ''
            },
            dependencia: {
              enabled: !!document.getElementById('chk_dependencia')?.checked,
              Grado: $('#dep_grado')?.value || '',
              Fecha_Reconocimiento: $('#dep_fecha_reconocimiento')?.value || '',
              Fecha_Caducidad: $('#dep_fecha_caducidad')?.value || '',
              Descripcion: $('#dep_descripcion')?.value || ''
            },
            exclusion: {
              enabled: !!document.getElementById('chk_exclusion')?.checked,
              Tipo: $('#exc_tipo')?.value || '',
              Fecha_Reconocimiento: $('#exc_fecha_reconocimiento')?.value ? dateToIntYYYYMMDD($('#exc_fecha_reconocimiento').value) : null,
              Fecha_Caducidad: $('#exc_fecha_caducidad')?.value ? dateToIntYYYYMMDD($('#exc_fecha_caducidad').value) : null,
              Descripcion: $('#exc_descripcion')?.value || ''
            }
          }
        };
      }

      // -------- Nueva acogida SOLO Usuario+Diag (idéntico) --------
      function clearUsuarioYDiag() {
        setPhotoInitialsFromName('IO');
        setPhotoPreview(null);
        const file = document.getElementById('u_photoFile');
        if (file) file.value = '';

        const ids = [
          'u_Nombre', 'u_Apellidos', 'u_Dni', 'u_Sexo', 'u_Fecha_Nacimiento', 'u_Nacionalidad', 'u_Fecha_Alta',
          'u_Telefono_Usuario', 'u_Correo', 'u_Direccion', 'u_Codigo_Postal',
          'u_Telefono_Familia1', 'u_Telefono_Familia2', 'u_Telefono_Servicios_Sociales', 'u_Telefono_Trabajadora_Social',
          'u_Telefono_Centro_Salud', 'u_Telefono_Medico_Cavecera', 'u_Telefono_Salud_Mental',
          'u_Telefono_Referente_Salud', 'u_Telefono_Referente_Formativo', 'u_Telefono_Otros1', 'u_Telefono_Otros2',
          'u_CCC', 'u_N_TIS', 'u_Nivel_Estudios', 'u_Tipo_Socio', 'u_ID_Situacion_Administrativa', 'u_ID_Via_Comunicacion',
          'u_ID_DIAG_Discapacidad', 'u_ID_DIAG_Dependencia', 'u_ID_DIAG_Exclusion',
          'u_ID_DIAG_Discapacidad_readonly', 'u_ID_DIAG_Dependencia_readonly', 'u_ID_DIAG_Exclusion_readonly',

          'disc_porcentaje', 'disc_diagnostico', 'disc_fecha_reconocimiento', 'disc_fecha_caducidad', 'disc_descripcion',
          'dep_grado', 'dep_fecha_reconocimiento', 'dep_fecha_caducidad', 'dep_descripcion',
          'exc_tipo', 'exc_fecha_reconocimiento', 'exc_fecha_caducidad', 'exc_descripcion',
        ];
        ids.forEach(id => setVal(id, ''));

        if (chkDisc) chkDisc.checked = false;
        if (chkDep) chkDep.checked = false;
        if (chkExc) chkExc.checked = false;

        document.querySelector('[data-diag="discapacidad"]')?.classList.add('is-off');
        document.querySelector('[data-diag="dependencia"]')?.classList.add('is-off');
        document.querySelector('[data-diag="exclusion"]')?.classList.add('is-off');

        setText('ioModalNombre', 'Nueva acogida (IO)');
        setText('ioModalSub', 'Completa los datos y guarda');
        setText('ioModalAvatar', 'IO');
        setText('u_id', '—');
      }

      // -------- API: abrir con fetcher externo (para no cambiar endpoint) --------
      async function abrirModalConUsuario(idUsuario, fetcher, rellenadorExtra) {
        status('Cargando ficha…');
        openModal();

        hiddenUserId.value = idUsuario;

        let json;
        try {
          json = await fetcher(idUsuario);
        } catch {
          status('No se pudo conectar.');
          return;
        }

        if (!json?.ok) {
          status(json?.error || 'Error cargando ficha');
          return;
        }

        // rellena usuario+diag
        rellenarUsuarioYDiag(json.data);

        // rellena extra (IO u otro)
        if (typeof rellenadorExtra === 'function') {
          rellenadorExtra(json.data);
        }

        status('');
      }

      function nuevaAcogida(clearExtraCb, resetTabsCb) {
        hiddenUserId.value = '';
        clearUsuarioYDiag();
        if (typeof clearExtraCb === 'function') clearExtraCb();

        // tabs por defecto (idéntico: main usuario + user basico)
        activateTab(modal, 'main', 'tab_main_usuario');
        activateTab(modal, 'user', 'tab_user_basico');

        // extra default tabs (IO o lo que sea)
        if (typeof resetTabsCb === 'function') resetTabsCb();

        status('');
        openModal();
      }

      // init
      initPhotoUI();

      return {
        escapeHtml,
        initials,
        status,
        setVal,
        setText,
        dtToLocalInput,
        localInputToDb,
        dbToDateInput,
        dateToIntYYYYMMDD,
        intYYYYMMDDToDate,

        openModal,
        closeModal,
        activateTab: (scope, paneId) => activateTab(modal, scope, paneId),

        setPhotoInitialsFromName,
        setPhotoPreview,
        setPhotoFromUserId,

        buildUsuarioDiagPayload,
        rellenarUsuarioYDiag,
        abrirModalConUsuario,
        nuevaAcogida
      };
    }
  };
})();
