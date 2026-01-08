<?php
// public/programas_vacaciones.php
?>
<div class="app vacaciones">

  <div class="app__header">
    <div>
      <h1 class="app__title">Vacaciones</h1>
      <p class="app__subtitle">Gestión de destinos y actividades.</p>
    </div>

    <button class="btn btn-primary" id="btnNuevoDestino">
      + Nuevo destino
    </button>
  </div>

  <div class="panel filters">
    <div class="filters__row">
      <div class="field">
        <label>Destino</label>
        <input type="text" id="fDestino" placeholder="Buscar destino…">
      </div>

      <div class="field">
        <label>Año</label>
        <input type="number" id="fAnio" placeholder="2025" min="2000" max="2100">
      </div>

      <div class="field">
        <label>Responsable</label>
        <select id="fResponsable"></select>
      </div>

      <div class="field">
        <label>Integrador</label>
        <select id="fIntegrador"></select>
      </div>

      <div class="filters__actions">
        <button class="btn" id="btnAplicar">Aplicar</button>
        <button class="btn btn-ghost" id="btnLimpiar">Limpiar</button>
      </div>
    </div>
  </div>

  <div id="vacacionesList" class="list__grid"></div>

</div>

<?php
$PROGRAM_MODAL = [
  'key' => 'vacaciones',
  'label' => 'Vacaciones',
  'partial' => __DIR__ . '/html/modals/vacaciones_modal.php',
];
include __DIR__ . '/html/modals/program_modal_base.php';
?>
