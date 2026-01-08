<?php
// public/formaciones.php
?>
<div class="app formaciones">

  <div class="app__header">
    <div>
      <h1 class="app__title">Formaciones</h1>
      <p class="app__subtitle">Gestión de formaciones, participantes y actas.</p>
    </div>

    <button class="btn btn-primary" id="btnNuevaFormacion">
      + Nueva formación
    </button>
  </div>

  <div class="panel filters">
    <div class="filters__row">
      <div class="field">
        <label>Formación</label>
        <input type="text" id="fFormacion" placeholder="Buscar formación…">
      </div>

      <div class="field">
        <label>Año</label>
        <input type="number" id="fAnio" placeholder="2025" min="2000" max="2100">
      </div>

      <div class="field">
        <label>Tipo</label>
        <select id="fTipo"></select>
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

  <div id="formacionesList" class="list__grid"></div>

</div>

<?php
$PROGRAM_MODAL = [
  'key' => 'formaciones',
  'label' => 'Formaciones',
  'partial' => __DIR__ . '/html/modals/formaciones_modal.php',
];
include __DIR__ . '/html/modals/program_modal_base.php';
?>
