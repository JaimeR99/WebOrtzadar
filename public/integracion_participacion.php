<?php
// public/integracion_participacion.php
// Subprogramas de "Integración y participación social"

$subprogramas = [
  [
    'id' => 'ips_ocio',
    'titulo' => 'Ocio',
    'icon' => 'fa-masks-theater',
    'href' => '#',
    'estado' => 'soon',
  ],
  [
    'id' => 'ips_vacaciones',
    'titulo' => 'Vacaciones',
    'icon' => 'fa-umbrella-beach',
    'href' => '#',
    'estado' => 'soon',
  ],
  [
    'id' => 'ips_formaciones',
    'titulo' => 'Formaciones',
    'icon' => 'fa-graduation-cap',
    'href' => '#',
    'estado' => 'soon',
  ],
];
?>

<div class="app-page app--programas app--subprogramas">

  <div class="app__header">
    <div>
      <h1 class="app__title">Integración y participación social</h1>
      <p class="app__subtitle">Selecciona un área: ocio, vacaciones o formaciones.</p>
    </div>

    <a class="btn" href="index.php?pagina=programas">← Volver a Programas</a>
  </div>

  <section class="programas-grid" aria-label="Selección de subprogramas">
    <?php foreach ($subprogramas as $p):
      $isDisabled = ($p['estado'] ?? '') !== 'ok';
      $classes = 'programa-tile panel' . ($isDisabled ? ' is-disabled' : '');
    ?>
      <a
        class="<?= $classes ?>"
        href="<?= htmlspecialchars($p['href'], ENT_QUOTES, 'UTF-8') ?>"
        data-programa="<?= htmlspecialchars($p['id'], ENT_QUOTES, 'UTF-8') ?>"
        <?= $isDisabled ? 'aria-disabled="true"' : '' ?>
      >
        <div class="programa-tile__icon" aria-hidden="true">
          <i class="fa-solid <?= htmlspecialchars($p['icon'], ENT_QUOTES, 'UTF-8') ?>"></i>
        </div>
        <div class="programa-tile__title"><?= htmlspecialchars($p['titulo'], ENT_QUOTES, 'UTF-8') ?></div>

        <?php if ($isDisabled): ?>
          <div class="programa-tile__badge">En construcción</div>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </section>

  <div class="programas-hint panel" role="note">
    <div class="programas-hint__title">Nota</div>
    <div class="programas-hint__text">
      Este apartado agrupa <strong>Ocio</strong>, <strong>Vacaciones</strong> y <strong>Formaciones</strong>.
      Cuando me digas estructura/BD, montamos la gestión como hicimos con IO.
    </div>
  </div>

</div>
