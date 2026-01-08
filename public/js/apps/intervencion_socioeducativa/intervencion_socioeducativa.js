// public/js/apps/programas_io/programas_io.js
(() => {
  function loadScript(src) {
    return new Promise((resolve, reject) => {
      const s = document.createElement('script');
      s.src = src;
      s.async = true;
      s.onload = resolve;
      s.onerror = () => reject(new Error('No se pudo cargar: ' + src));
      document.head.appendChild(s);
    });
  }

  async function boot() {
    // 1) cargar base reusable de usuario
    if (!window.UserModalBase) {
      await loadScript('public/js/apps/components/user_modal.base.js');
    }
    // 2) cargar lógica Intervencion Social (listado + io fill/clear/payload)
    if (!window.IntervencionSocialLogic) {
      await loadScript('public/js/apps/intervencion_socioeducativa/intervencion_socioeducativa.logic.js');
    }

    const $ = (sel) => document.querySelector(sel);

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

    // registros: bind botones una sola vez
    window.IntervencionSocialLogic?.bindRegistros?.(helpers);

    // ===== listado init (idéntico) =====
    async function recargarListado() {
      await window.ProgramasIOLogic.cargarListado(helpers);
    }

    // ✅ Exponer recarga para que pueda usarse desde el modal "Nueva Intervención"
    window.IOApp = window.IOApp || {};
    window.IOApp.recargarListado = recargarListado;

    window.IOApp.abrirModalConUsuario = abrirModalConUsuario;

    window.ProgramasIOLogic.bindFiltros(recargarListado);
    window.ProgramasIOLogic.bindOpenFromList((id) => abrirModalConUsuario(id));

    // ===== abrir modal (MISMO endpoint y MISMO flujo) =====
    async function abrirModalConUsuario(idUsuario) {
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
          // Llamar a la función para cargar los registros con los nuevos parámetros
          window.IntervencionSocialLogic?.cargarRegistros?.(idUsuario, helpers, 'BF', 'OBJ');  // Por ejemplo, 'BF' para Bienestar físico y 'OBJ' para Objetivos

        }
      );

      // por defecto, deja la pestaña de Registros activa en el programa
      helpers.activateTab('intervencion_socioeducativa', 'tab_is_registros');
    }

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

    // (ya bindeado arriba)
  }

  boot().catch(err => {
    console.error(err);
    const el = document.getElementById('ioList');
    if (el) el.innerHTML = `<div class="empty">Error cargando JS</div>`;
  });
})();



// Abre el modal de Nuevo Comentario
function openNuevoComentario() {
  document.getElementById('modalNuevoComentario').classList.add('is-open');

  // valores actuales (códigos)
  const ambito = document.getElementById('is_ambito')?.value || 'BF';
  const categoria = document.getElementById('is_categoria')?.value || 'OBJ';

  // labels bonitos
  const ambitoTxt = getAmbitoLabel(ambito);
  const categoriaTxt = getCategoriaLabel(categoria);

  // mostrar como texto
  document.getElementById('modal_ambito').textContent = `Ámbito: ${ambitoTxt}`; // Muestra el valor de Ámbito
  document.getElementById('modal_categoria').textContent = `Categoría: ${categoriaTxt}`; // Muestra el valor de Categoría

  // (opcional pero recomendado) guardar los códigos reales para el POST sin tener que “parsear” texto
  document.getElementById('modalNuevoComentario').dataset.ambito = ambito;
  document.getElementById('modalNuevoComentario').dataset.categoria = categoria;

  // Ocultamos el panel de "Nuevo registro"
  const nuevoRegistroPanel = document.getElementById('isNuevoRegistro');
  if (nuevoRegistroPanel) {
    nuevoRegistroPanel.style.display = 'none'; // Aseguramos que no interfiera
  }

  // Limpiar el campo de comentario
  const modalComentario = document.getElementById('modal_comentario');
  if (modalComentario) {
    modalComentario.value = '';  // Limpiar texto del comentario
  }
}

// Cierra el modal de Nuevo Comentario
function closeNuevoComentario() {
  const modal = document.getElementById('modalNuevoComentario');
  if (modal) {
    modal.classList.remove('is-open');
    // Mostramos el panel de "Nuevo registro" cuando el modal se cierra
    const nuevoRegistroPanel = document.getElementById('isNuevoRegistro');
    if (nuevoRegistroPanel) {
      nuevoRegistroPanel.style.display = 'block';
    }
  } else {
    console.error("El modal de Nuevo Comentario no está disponible.");
  }
}

// Guarda el nuevo comentario
// Guarda el nuevo comentario
async function guardarNuevoComentario() {
  const comentario = document.getElementById('modal_comentario').value.trim();
  if (!comentario) {
    alert("Por favor, escribe un comentario.");
    return;
  }

  const modal = document.getElementById('modalNuevoComentario');
  const ambito = modal.dataset.ambito || document.getElementById('is_ambito').value;
  const categoria = modal.dataset.categoria || document.getElementById('is_categoria').value;

  const idUsuario = document.getElementById('ioModalUserId').value;
  const status = document.getElementById('isRegistrosStatus');
  setStatus(status, "Guardando…");

  try {
    const res = await fetch('public/php/intervencion_socioeducativa/registros_save.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ id_usuario: idUsuario, comentario, ambito, categoria })
    });

    const json = await res.json();
    if (!json.ok) {
      setStatus(status, json.error || 'Error guardando registro');
      return;
    }

    setStatus(status, "Guardado ✅");
    closeNuevoComentario();

    // Actualizamos los registros inmediatamente después de guardar el comentario
    await actualizarRegistros(idUsuario, ambito, categoria);  // Llamamos al nuevo método para recargar los registros
    setTimeout(() => setStatus(status, ''), 1200);

  } catch (error) {
    setStatus(status, 'No se pudo conectar.');
    console.error(error);
  }
}



// Actualiza el estado del mensaje
function setStatus(statusElement, message) {
  if (statusElement) {
    statusElement.textContent = message;
  }
}

// Añadir un listener para el botón de nuevo registro
document.getElementById('is_btn_nuevo_registro').addEventListener('click', () => {
  openNuevoComentario();
});

// Función para obtener la etiqueta del Ámbito desde el código
function getAmbitoLabel(code) {
  const btn = document.querySelector(`[data-is-ambito="${code}"]`);
  return btn ? btn.textContent.trim() : code;
}

// Función para obtener la etiqueta de la Categoría desde el código
function getCategoriaLabel(code) {
  const btn = document.querySelector(`[data-is-categoria="${code}"]`);
  return btn ? btn.textContent.trim() : code;
}

// Aquí se asegura de que `cargarRegistros` esté correctamente definido
async function cargarRegistros(idUsuario, helpers, ambito = 'BF', categoria = 'OBJ') {
  const status = document.getElementById('isRegistrosStatus');
  const list = document.getElementById('isRegistrosList');
  if (list) list.innerHTML = `<div class="empty">Cargando…</div>`;
  if (status) status.textContent = '';

  let json;
  try {
    const res = await fetch(`public/php/intervencion_socioeducativa/registros_get.php?id_usuario=${encodeURIComponent(idUsuario)}&ambito=${encodeURIComponent(ambito)}&categoria=${encodeURIComponent(categoria)}`, {
      headers: { 'Accept': 'application/json' }
    });
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

// Función para renderizar los registros
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

// Método para actualizar los registros
async function actualizarRegistros(idUsuario, ambito = 'BF', categoria = 'OBJ') {
  const status = document.getElementById('isRegistrosStatus');
  const list = document.getElementById('isRegistrosList');

  if (list) list.innerHTML = `<div class="empty">Cargando…</div>`;  // Indicador de carga
  if (status) status.textContent = '';  // Limpiar cualquier mensaje anterior

  let json;
  try {
    // Hacer la petición GET para obtener los registros
    const res = await fetch(`public/php/intervencion_socioeducativa/registros_get.php?id_usuario=${encodeURIComponent(idUsuario)}&ambito=${encodeURIComponent(ambito)}&categoria=${encodeURIComponent(categoria)}`, {
      headers: { 'Accept': 'application/json' }
    });
    json = await res.json();
  } catch (error) {
    if (status) status.textContent = 'No se pudo conectar.';
    if (list) list.innerHTML = `<div class="empty">No se pudo conectar.</div>`;  // Mensaje de error
    return;
  }

  if (!json?.ok) {
    const msg = json?.error || 'Error cargando registros';
    if (status) status.textContent = msg;
    if (list) list.innerHTML = `<div class="empty">${msg}</div>`;  // Mensaje de error
    return;
  }

  // Si la petición fue exitosa, renderizamos los registros
  renderRegistros(list, json.data);
}

// Función para renderizar los registros en la lista
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

// Helper para evitar XSS si el comentario viene con < >
function escapeHtml(str) {
  return String(str)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}



// ==============================
// MODAL: Nueva Intervención
// ==============================

document.getElementById('btnNuevaAcogida')?.addEventListener('click', () => {
  openModalNuevaIntervencion();
});

function openModalNuevaIntervencion() {
  const modal = document.getElementById('modalNuevaIntervencion');
  if (!modal) return console.warn('No existe #modalNuevaIntervencion');

  modal.classList.add('is-open');
  resetNuevaIntervencionUI();
  cargarUsuariosNoRegistrados();
}

document.getElementById('closeNuevaIntervencion')?.addEventListener('click', () => {
  closeModalNuevaIntervencion();
});

function closeModalNuevaIntervencion() {
  const modal = document.getElementById('modalNuevaIntervencion');
  if (!modal) return;
  modal.classList.remove('is-open');
  resetNuevaIntervencionUI();
}

function resetNuevaIntervencionUI() {
  const modal = document.getElementById('modalNuevaIntervencion');
  const btn = document.getElementById('btnIniciarIntervencion');
  const footerStatus = document.getElementById('nuevaIntervencionStatus');

  if (modal) modal.dataset.selectedUserId = '';
  if (btn) btn.disabled = true;
  if (footerStatus) footerStatus.textContent = 'Selecciona un usuario para continuar.';

  const grid = document.getElementById('usuariosNoRegistradosGrid');
  if (grid) {
    grid.querySelectorAll('.trabajador-card.is-selected').forEach(c => {
      c.classList.remove('is-selected');
      c.setAttribute('aria-selected', 'false');
    });
  }
}

async function cargarUsuariosNoRegistrados() {
  const grid = document.getElementById('usuariosNoRegistradosGrid');
  const status = document.getElementById('usuariosNoRegistradosStatus');

  if (!grid || !status) {
    console.warn('No existe usuariosNoRegistradosGrid/usuariosNoRegistradosStatus');
    return;
  }

  grid.innerHTML = '';
  status.style.display = 'block';
  status.textContent = 'Cargando usuarios…';

  try {
    const res = await fetch('public/php/intervencion_socioeducativa/no_registrados.php', {
      headers: { 'Accept': 'application/json' }
    });

    const json = await res.json();

    if (!json?.ok || !json.data?.length) {
      status.textContent = 'No hay usuarios pendientes.';
      return;
    }

    status.style.display = 'none';

    grid.innerHTML = json.data.map(u => `
      <article class="trabajador-card" data-id="${u.id}" role="button" tabindex="0" aria-selected="false">
        <div class="trabajador-card__top">
          <div class="trabajador-avatar is-no-photo" aria-hidden="true">
            <span class="trabajador-initials">${(u.Nombre?.[0] || '')}${(u.Apellidos?.[0] || '')}</span>
          </div>

          <div>
            <div class="trabajador-name">${u.Nombre || ''} ${u.Apellidos || ''}</div>
            <div class="trabajador-sub">DNI: ${u.Dni || '-'}</div>
          </div>
        </div>

        <div class="trabajador-meta">
          <div class="meta-row"><i class="fa-solid fa-phone"></i><span>${u.Telefono_Usuario || '-'}</span></div>
          <div class="meta-row"><i class="fa-solid fa-envelope"></i><span>${u.Correo || '-'}</span></div>
          <div class="meta-row"><i class="fa-solid fa-location-dot"></i><span>${u.Direccion || '-'}</span></div>
        </div>
      </article>
    `).join('');

    bindSeleccionUnicaNuevaIntervencion();

  } catch (e) {
    status.textContent = 'Error cargando usuarios.';
    console.error(e);
  }
}

function bindSeleccionUnicaNuevaIntervencion() {
  const modal = document.getElementById('modalNuevaIntervencion');
  const grid = document.getElementById('usuariosNoRegistradosGrid');
  const btn = document.getElementById('btnIniciarIntervencion');
  const footerStatus = document.getElementById('nuevaIntervencionStatus');

  if (!modal || !grid || !btn) return;

  if (grid.dataset.bound === '1') return;
  grid.dataset.bound = '1';

  function setSelected(card) {
    const id = card?.dataset?.id;
    if (!id) return;

    grid.querySelectorAll('.trabajador-card').forEach(c => {
      const on = (c === card);
      c.classList.toggle('is-selected', on);
      c.setAttribute('aria-selected', on ? 'true' : 'false');
    });

    modal.dataset.selectedUserId = id;
    btn.disabled = false;
    if (footerStatus) footerStatus.textContent = `Seleccionado ID: ${id}`;
  }

  grid.addEventListener('click', (e) => {
    const card = e.target.closest('.trabajador-card');
    if (!card) return;
    setSelected(card);
  });

  grid.addEventListener('keydown', (e) => {
    if (e.key !== 'Enter' && e.key !== ' ') return;
    const card = e.target.closest('.trabajador-card');
    if (!card) return;
    e.preventDefault();
    setSelected(card);
  });
}

// ==============================
// BOTÓN GENERAL: crear intervención + refrescar listado + abrir modal
// ==============================

document.getElementById('btnIniciarIntervencion')?.addEventListener('click', async () => {
  const modal = document.getElementById('modalNuevaIntervencion');
  const footerStatus = document.getElementById('nuevaIntervencionStatus');
  const btn = document.getElementById('btnIniciarIntervencion');

  const idUsuario = modal?.dataset?.selectedUserId ? Number(modal.dataset.selectedUserId) : 0;
  if (!idUsuario) {
    if (footerStatus) footerStatus.textContent = 'Selecciona un usuario primero.';
    return;
  }

  if (footerStatus) footerStatus.textContent = 'Creando intervención…';
  if (btn) btn.disabled = true;

  try {
    const res = await fetch('public/php/intervencion_socioeducativa/crear_intervencion.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ id_usuario: idUsuario })
    });

    const json = await res.json();
    if (!json?.ok) {
      if (footerStatus) footerStatus.textContent = json?.error || 'Error creando intervención';
      return;
    }

    // cerrar modal nuevo
    modal?.classList.remove('is-open');

    // ✅ recargar listado principal y abrir su ficha
    if (window.IOApp?.recargarListado) {
      await window.IOApp.recargarListado();
    } else {
      console.warn('window.IOApp.recargarListado NO existe');
    }
    if (window.IOApp?.abrirModalConUsuario) {
      await window.IOApp.abrirModalConUsuario(idUsuario);
    }else {
      console.warn('Ha recargado el listado pero no habre el modal');
    }

    if (footerStatus) footerStatus.textContent = '';

  } catch (e) {
    console.error(e);
    if (footerStatus) footerStatus.textContent = 'No se pudo conectar / PHP no devolvió JSON.';
  } finally {
    if (btn) btn.disabled = false;
    resetNuevaIntervencionUI();
  }
});
