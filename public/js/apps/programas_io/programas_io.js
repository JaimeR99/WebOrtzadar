// public/js/apps/programas_io/programas_io.js
(() => {
  function loadScript(src){
    return new Promise((resolve, reject)=>{
      const s = document.createElement('script');
      s.src = src;
      s.async = true;
      s.onload = resolve;
      s.onerror = () => reject(new Error('No se pudo cargar: ' + src));
      document.head.appendChild(s);
    });
  }

  async function boot(){
    // 1) cargar base reusable de usuario
    if (!window.UserModalBase){
      await loadScript('public/js/apps/components/user_modal.base.js');
    }
    // 2) cargar lógica IO (listado + io fill/clear/payload)
    if (!window.ProgramasIOLogic){
      await loadScript('public/js/apps/programas_io/programas_io.logic.js');
    }

    const $ = (sel) => document.querySelector(sel);

    const btnNueva = $('#btnNuevaAcogida');
    const btnGuardarModal = $('#btnGuardarModal');
    const hiddenUserId = document.getElementById('ioModalUserId');

    // ===== inicializa el componente base con endpoints EXACTOS =====
    const helpers = window.UserModalBase.init({
      modalSelector: '#ioModal',
      statusElId: 'ioModalStatus',
      hiddenUserIdId: 'ioModalUserId',
      endpoints: {
        savePhoto: 'public/php/programas/io_save_photo.php'
      }
    });

    // ===== listado init (idéntico) =====
    async function recargarListado(){
      await window.ProgramasIOLogic.cargarListado(helpers);
    }

    window.ProgramasIOLogic.bindFiltros(recargarListado);
    window.ProgramasIOLogic.bindOpenFromList((id) => abrirModalConUsuario(id));

    // ===== abrir modal (MISMO endpoint y MISMO flujo) =====
    async function abrirModalConUsuario(idUsuario){
      await helpers.abrirModalConUsuario(
        idUsuario,
        async (id) => {
          const res = await fetch(`public/php/programas/io_get.php?id=${encodeURIComponent(id)}`, {
            headers: { 'Accept': 'application/json' }
          });
          return await res.json();
        },
        (data) => {
          // rellenar parte IO sin tocar usuario/diag
          window.ProgramasIOLogic.rellenarModalIO(data, helpers);
        }
      );
    }

    // ===== nueva acogida (MISMO flujo) =====
    btnNueva?.addEventListener('click', () => {
      helpers.nuevaAcogida(
        () => window.ProgramasIOLogic.clearIOFields(helpers),
        () => {
          // igual que tu JS: main usuario + user basico
          // y si quieres default io también:
          helpers.activateTab('io', 'tab_io_generales');
        }
      );
    });

    // ===== guardar (payload EXACTO + validaciones EXACTAS + endpoint EXACTO) =====
    btnGuardarModal?.addEventListener('click', async () => {
      helpers.status('Guardando…');

      const usuarioDiag = helpers.buildUsuarioDiagPayload();
      const io = window.ProgramasIOLogic.buildIOPayload(helpers);

      const payload = {
        id_usuario: hiddenUserId?.value ? Number(hiddenUserId.value) : null,
        usuario: usuarioDiag.usuario,
        io,
        diag: usuarioDiag.diag
      };

      // validaciones (idénticas)
      if (!payload.usuario.Nombre || !payload.usuario.Dni) {
        helpers.status('Nombre y DNI son obligatorios.');
        return;
      }
      if (!payload.usuario.Fecha_Nacimiento) {
        helpers.status('Fecha de nacimiento es obligatoria.');
        return;
      }

      let json;
      try {
        const res = await fetch('public/php/programas/io_save.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify(payload)
        });
        json = await res.json();
      } catch {
        helpers.status('No se pudo conectar.');
        return;
      }

      if (!json?.ok) {
        helpers.status(json?.error || 'Error guardando');
        return;
      }

      if (json.data?.id_usuario) hiddenUserId.value = String(json.data.id_usuario);

      if (json.data?.diag_ids) {
        helpers.setVal('u_ID_DIAG_Discapacidad', json.data.diag_ids.ID_DIAG_Discapacidad ?? '');
        helpers.setVal('u_ID_DIAG_Dependencia', json.data.diag_ids.ID_DIAG_Dependencia ?? '');
        helpers.setVal('u_ID_DIAG_Exclusion', json.data.diag_ids.ID_DIAG_Exclusion ?? '');

        helpers.setVal('u_ID_DIAG_Discapacidad_readonly', json.data.diag_ids.ID_DIAG_Discapacidad ?? '');
        helpers.setVal('u_ID_DIAG_Dependencia_readonly', json.data.diag_ids.ID_DIAG_Dependencia ?? '');
        helpers.setVal('u_ID_DIAG_Exclusion_readonly', json.data.diag_ids.ID_DIAG_Exclusion ?? '');
      }

      helpers.status('Guardado ✅');
      await recargarListado();
      setTimeout(() => helpers.status(''), 1200);
    });

    // init (idéntico)
    await recargarListado();
  }

  boot().catch(err => {
    console.error(err);
    const el = document.getElementById('ioList');
    if (el) el.innerHTML = `<div class="empty">Error cargando JS</div>`;
  });
})();
