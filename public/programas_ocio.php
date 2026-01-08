<?php
// public/programas_ocio.php
?>
<div class="app ocio">

  <div class="app__header">
    <div>
      <h1 class="app__title">Ocio</h1>
      <p class="app__subtitle">Gestión de grupos de ocio.</p>
    </div>

    <button class="btn btn-primary" id="btnNuevoGrupo">
      + Nuevo grupo
    </button>
  </div>

  <div class="panel filters">
    <div class="filters__row">
      <div class="field">
        <label>Nombre</label>
        <input type="text" id="fNombre" placeholder="Buscar grupo…">
      </div>

      <div class="field">
        <label>Responsable</label>
        <select id="fResponsable"></select>
      </div>

      <div class="filters__actions">
        <button class="btn" id="btnAplicar">Aplicar</button>
        <button class="btn btn-ghost" id="btnLimpiar">Limpiar</button>
      </div>
    </div>
  </div>

 
    <div id="ocioList" class="list__grid"></div>
 

</div>


<?php
// Modal específico de Ocio (placeholder por ahora)
$PROGRAM_MODAL = [
  'key' => 'ocio',
  'label' => 'Ocio',
  'partial' => __DIR__ . '/html/modals/ocio_modal.php',
];
include __DIR__ . '/html/modals/program_modal_base.php';
?>
