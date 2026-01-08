<?php
// public/programas_io.php
?>
<div class="app io">

  <div class="app__header">
    <div>
      <h1 class="app__title">Información y orientación</h1>
      <p class="app__subtitle">Altas IO (1 por persona) y edición de ficha.</p>
    </div>

    <button class="btn btn-primary" id="btnNuevaAcogida">+ Nueva acogida (IO)</button>
  </div>

  <div class="panel filters">
    <div class="filters__row">
      <div class="field">
        <label>Persona</label>
        <input type="text" id="fPersona" placeholder="Buscar por nombre o DNI...">
      </div>

      <div class="field">
        <label>Dirección / municipio</label>
        <input type="text" id="fMunicipio" placeholder="Ej: Donostia">
      </div>

      <div class="field">
        <label>Desde</label>
        <input type="date" id="fDesde">
      </div>

      <div class="field">
        <label>Hasta</label>
        <input type="date" id="fHasta">
      </div>

      <div class="filters__actions">
        <button class="btn" id="btnLimpiar" type="button">Limpiar</button>
        <button class="btn btn-secondary" id="btnAplicar" type="button">Aplicar</button>
      </div>
    </div>
  </div>

  <div class="list" id="ioList" aria-live="polite">
    <div class="empty">Cargando…</div>
  </div>
</div>


<!-- ===========================
     MODAL FICHA (Usuario + Programa IO)
=========================== -->
<!-- ===========================
     MODAL FICHA (Usuario + Programa IO)
=========================== -->
<?php
  // Modal reutilizable de Usuario + extensión del Programa IO
  $USER_MODAL_PROGRAM = [
    'key' => 'io',
    'label' => 'Programa IO',
    'partial' => __DIR__ . '/html/modals/io_modal_extension.php',
  ];
  include __DIR__ . '/html/modals/user_modal.php';
?>
