<?php
// public/usuarios.php
?>
<div class="app-page app--usuarios usuarios-layout">

  <!-- CABECERA -->
  <header class="usuarios-header">
    <div class="usuarios-header__left">
      <div class="usuarios-header__title">
        <h1 class="usuarios-title">Usuarios</h1>
        <p class="usuarios-subtitle">Lista de usuarios. Haz clic en cualquiera para abrir su ficha.</p>
      </div>

      <div class="usuarios-filters">
        <div class="field">
          <label for="filtro_nombre" class="label">Nombre</label>
          <input
            id="filtro_nombre"
            class="control control--sm"
            type="search"
            placeholder="Buscar nombre…"
            autocomplete="off"
            list="dl_nombres"
          >
          <datalist id="dl_nombres"></datalist>
        </div>

        <div class="field">
          <label for="filtro_sexo" class="label">Sexo</label>
          <select id="filtro_sexo" class="control control--sm">
            <option value="">Cargando…</option>
          </select>
        </div>

        <div class="field">
          <label for="filtro_direccion" class="label">Dirección</label>
          <input
            id="filtro_direccion"
            class="control control--sm"
            type="search"
            placeholder="Buscar dirección…"
            autocomplete="off"
            list="dl_direcciones"
          >
          <datalist id="dl_direcciones"></datalist>
        </div>
      </div>
    </div>
  </header>

  <!-- LISTADO -->
  <section class="usuarios-list">
    <div id="usuariosStatus" class="usuarios-status usuarios-status--loading">
      Cargando usuarios…
    </div>

    <div class="usuarios-grid" id="usuariosGrid" style="display:none;"></div>

    <div id="usuariosEmpty" class="usuarios-empty" style="display:none;">
      <h3 class="usuarios-empty__title">No hay usuarios para mostrar</h3>
      <p class="usuarios-empty__text">
        Revisa la conexión y que exista la tabla <code>AA_usuarios</code>.
      </p>
    </div>
  </section>

  <!-- MODAL -->
  <div class="usuario-modal" id="usuarioModal" aria-hidden="true">
    <div class="usuario-modal__backdrop" data-close="1"></div>

    <div class="usuario-modal__panel" role="dialog" aria-modal="true" aria-labelledby="usuarioModalTitulo">
      <div class="usuario-modal__header">
        <div class="usuario-modal__header-left">
          <div class="usuario-modal__avatar">
            <img id="modalAvatar" src="https://ui-avatars.com/api/?name=Usuario&background=f97316&color=fff&size=128" alt="Foto usuario">
          </div>
          <div class="usuario-modal__titles">
            <h2 id="usuarioModalTitulo" class="usuario-modal__title">Ficha</h2>
            <p id="usuarioModalSub" class="usuario-modal__sub">—</p>
          </div>
        </div>

        <button class="btn btn--sm" type="button" id="usuarioModalClose" aria-label="Cerrar">✕</button>
      </div>

      <div class="usuario-modal__tabs">
        <button class="usuario-tab is-active" data-tab="tab-perfil" type="button">Perfil</button>
        <button class="usuario-tab" data-tab="tab-contacto" type="button">Contacto</button>
        <button class="usuario-tab" data-tab="tab-admin" type="button">Administrativo</button>
        <button class="usuario-tab" data-tab="tab-diagnostico" type="button">Diagnóstico</button>
      </div>

      <div class="usuario-modal__body">
        <section class="usuario-tabpanel is-active" id="tab-perfil"></section>
        <section class="usuario-tabpanel" id="tab-contacto"></section>
        <section class="usuario-tabpanel" id="tab-admin"></section>
        <section class="usuario-tabpanel" id="tab-diagnostico"></section>
      </div>

      <div class="usuario-modal__footer">
        <button class="btn btn--sm" type="button" data-close="1">Cerrar</button>
      </div>
    </div>
  </div>

</div>

<!-- ✅ Cargar primero el JS de peticiones (ruta correcta desde /index.php) -->
<script src="public/js/apps/usuarios/obtener_usuarios.js?v=1"></script>
