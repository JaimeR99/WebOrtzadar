<?php
// public/programas.php
// Selector de programas (pantalla de entrada)

$programas = [
  [
    'id' => 'programas_io',
    'titulo' => 'Información y orientación',
    'icon' => 'fa-compass',
    'href' => 'index.php?pagina=programas_io',
    'estado' => 'ok',
  ],
  [
    'id' => 'servicio_intervencion_socioeducativa',
    'titulo' => 'Intervención socioeducativa',
    'icon' => 'fa-handshake-angle',
    'href' => 'index.php?pagina=intervencion_socioeducativa',
    'estado' => 'ok',
  ],

  // ✅ ESTE ES EL QUE DESPLIEGA SUBPROGRAMAS
  [
    'id' => 'integracion_participacion', // <- importante: id que usará el JS
    'titulo' => 'Integración y participación social',
    'icon' => 'fa-hands-holding-circle',
    'href' => '#',
    'estado' => 'ok', // <- importante: NO disabled
  ],

  [
    'id' => 'programas_sensibilizacion_voluntariado',
    'titulo' => 'Sensibilización y voluntariado',
    'icon' => 'fa-hand-holding-heart',
    'href' => '#',
    'estado' => 'soon',
  ],
  [
    'id' => 'programas_movimiento_asociativo',
    'titulo' => 'Movimiento asociativo',
    'icon' => 'fa-users-rectangle',
    'href' => '#',
    'estado' => 'soon',
  ],
  [
    'id' => 'programas_vivienda',
    'titulo' => 'Vivienda',
    'icon' => 'fa-house',
    'href' => 'index.php?pagina=programas_vivienda',
    'estado' => 'ok',
  ],
  [
    'id' => 'programas_vida_independiente',
    'titulo' => 'Vida independiente',
    'icon' => 'fa-person-walking',
    'href' => 'index.php?pagina=programas_vida_independiente',
    'estado' => 'ok',
  ],

];
?>

<div class="app-page app--programas">

  <div class="app__header">
    <div>
      <h1 class="app__title">Programas</h1>
      <p class="app__subtitle">Selecciona un programa para acceder a sus fichas y gestión.</p>
    </div>
  </div>

  <section class="programas-grid" aria-label="Selección de programas">
    <?php foreach ($programas as $p):
      $isDisabled = ($p['estado'] ?? '') !== 'ok';
      $classes = 'programa-tile panel' . ($isDisabled ? ' is-disabled' : '');
    ?>
      <a
        class="<?= $classes ?>"
        href="<?= htmlspecialchars($p['href'], ENT_QUOTES, 'UTF-8') ?>"
        data-programa="<?= htmlspecialchars($p['id'], ENT_QUOTES, 'UTF-8') ?>"
        <?= $isDisabled ? 'aria-disabled="true"' : '' ?>>
        <div class="programa-tile__icon" aria-hidden="true">
          <i class="fa-solid <?= htmlspecialchars($p['icon'], ENT_QUOTES, 'UTF-8') ?>"></i>
        </div>
        <div class="programa-tile__title"><?= htmlspecialchars($p['titulo'], ENT_QUOTES, 'UTF-8') ?></div>

        <?php if ($isDisabled): ?>
          <div class="programa-tile__badge">En construcción</div>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>

    <!-- ✅ PANEL DE SUBPROGRAMAS (DENTRO DEL GRID PARA PODER REUBICARLO) -->
    <div class="subprogramas-panel panel" id="subprogramasPanel" hidden aria-live="polite">
      <div class="subprogramas-panel__head">
        <div>
          <div class="subprogramas-panel__title">Integración y participación social</div>
          <div class="subprogramas-panel__sub">Selecciona un área</div>
        </div>
        <button class="btn" type="button" id="btnCerrarSubprogramas">Cerrar</button>
      </div>

      <div class="subprogramas-grid" role="list">
        <a class="subprograma-tile panel" href="index.php?pagina=programas_ocio" data-subprograma="programas_ocio" role="listitem">
          <div class="subprograma-tile__icon" aria-hidden="true"><i class="fa-solid fa-masks-theater"></i></div>
          <div class="subprograma-tile__title">Ocio</div>
        </a>


        <a class="subprograma-tile panel"
          href="index.php?pagina=programas_vacaciones"
          data-subprograma="programas_vacaciones"
          role="listitem">
          <div class="subprograma-tile__icon" aria-hidden="true"><i class="fa-solid fa-umbrella-beach"></i></div>
          <div class="subprograma-tile__title">Vacaciones</div>
        </a>


        <a class="subprograma-tile panel"
          href="index.php?pagina=programas_formaciones"
          data-subprograma="programas_formaciones"
          role="listitem">
          <div class="subprograma-tile__icon" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></div>
          <div class="subprograma-tile__title">Formaciones</div>
        </a>
      </div>
    </div>
  </section>


</div>