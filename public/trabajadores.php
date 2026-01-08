<?php
// public/trabajadores.php
?>

<div class="app-page app--trabajadores trabajadores-layout">

  <div class="app__header">
    <div>
      <h1 class="app__title">Trabajadores sociales</h1>
      <p class="app__subtitle">Gestión de trabajadores y credenciales de acceso.</p>
    </div>

    <div class="trabajadores-actions">
      <button class="btn btn-primary" type="button" id="btnNuevoTrabajador">+ Nuevo trabajador</button>
    </div>
  </div>

  <section class="panel trabajadores-panel">
    <div class="trabajadores-toolbar">
      <div class="field">
        <label class="label" for="fTrab">Buscar</label>
        <input class="control control--sm" id="fTrab" type="search" placeholder="Nombre, apellidos, email..." autocomplete="off">
      </div>

      <div class="field">
        <label class="label" for="fAcceso">Acceso</label>
        <select class="control control--sm" id="fAcceso">
          <option value="">Todos</option>
          <option value="1">Con acceso</option>
          <option value="0">Sin acceso</option>
        </select>
      </div>
    </div>

    <div id="trabajadoresStatus" class="trabajadores-status">Cargando…</div>
    <div class="trabajadores-grid" id="trabajadoresGrid" style="display:none;"></div>
    <div class="trabajadores-empty" id="trabajadoresEmpty" style="display:none;">
      <h3 class="trabajadores-empty__title">No hay trabajadores</h3>
      <p class="trabajadores-empty__text">Crea el primero con el botón <strong>+ Nuevo trabajador</strong>.</p>
    </div>
  </section>

  <?php
  $PROGRAM_MODAL = [
    'key' => 'trabajadores',
    'label' => 'Trabajador/a',
    'partial' => __DIR__ . '/html/modals/trabajadores_modal.php',
  ];
  include __DIR__ . '/html/modals/program_modal_base.php';
  ?>

</div>
