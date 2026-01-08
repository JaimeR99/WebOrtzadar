<?php
// public/programas_vivienda.php
?>

<div class="app vivienda">

  <div class="app__header">
    <div>
      <h1 class="app__title">Vivienda</h1>
      <p class="app__subtitle">Gestión de viviendas, participantes, dinámicas y equipamientos.</p>
    </div>

    <div class="app__actions">
      <button class="btn btn-primary" type="button" id="viv_btn_new">+ Nueva vivienda</button>
    </div>
  </div>


  <div class="list" id="viviendaList" aria-live="polite">
    <div class="empty">Cargando…</div>
  </div>

</div>

<?php
// Modal específico de Vivienda
$PROGRAM_MODAL = [
  'key' => 'vivienda',
  'label' => 'Vivienda',
  'partial' => __DIR__ . '/html/modals/vivienda_modal.php',
];
include __DIR__ . '/html/modals/program_modal_base.php';
?>
