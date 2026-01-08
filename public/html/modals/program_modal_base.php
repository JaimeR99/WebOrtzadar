<?php
// public/html/modals/program_modal_base.php
// Modal base reutilizable para cualquier "programa" (ocio, vacaciones, formaciones, etc.)
// Requiere:
//   $PROGRAM_MODAL = ['key' => 'ocio', 'label' => 'Ocio', 'partial' => '/abs/path/al/partial.php'];

if (!isset($PROGRAM_MODAL) || !is_array($PROGRAM_MODAL)) {
  throw new RuntimeException('PROGRAM_MODAL no definido');
}

$key   = (string)($PROGRAM_MODAL['key'] ?? 'program');
$label = (string)($PROGRAM_MODAL['label'] ?? 'Programa');
$partial = (string)($PROGRAM_MODAL['partial'] ?? '');

if ($partial === '' || !is_file($partial)) {
  throw new RuntimeException('Partial no encontrado: ' . $partial);
}

$modalId = "programModal_" . preg_replace('/[^a-z0-9_]+/i', '_', $key);
?>

<!-- ===========================
     MODAL BASE (reutilizable)
=========================== -->
<div class="io-modal"
     id="<?= htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8') ?>"
     data-program="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
     aria-hidden="true">
  <div class="io-modal__overlay" data-close="1" tabindex="-1"></div>

  <div class="io-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="<?= $modalId ?>_title">
    <!-- TOP -->
    <div class="io-modal__top">
      <div class="io-modal__top-left">
        <div class="io-avatar" id="<?= $modalId ?>_avatar" aria-hidden="true"><?= htmlspecialchars(mb_substr($label, 0, 2), ENT_QUOTES, 'UTF-8') ?></div>
        <div class="io-modal__top-titles">
          <div class="io-modal__name" id="<?= $modalId ?>_title"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></div>
          <div class="io-modal__sub" id="<?= $modalId ?>_sub">—</div>
        </div>
      </div>

      <button class="io-icon-btn" type="button" data-close="1" aria-label="Cerrar">×</button>
    </div>

    <!-- TABS principales (opcional: el partial puede añadir más) -->
    <div class="io-modal__tabs" data-tab-scope="main" role="tablist" aria-label="Secciones">
      <button class="io-tab is-active" type="button" role="tab" aria-selected="true" tabindex="0"
              data-tab="tab_main_programa_<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
      </button>
    </div>

    <!-- CONTENT -->
    <div class="io-modal__content">
      <div class="io-pane is-active" id="tab_main_programa_<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
           data-tab-scope="main" role="tabpanel" aria-hidden="false">

        <!-- (Si algún programa necesita subtabs, los mete dentro del partial con .io-subtabs) -->
       
          <?php include $partial; ?>
        

      </div>
    </div>

    <!-- FOOTER -->
    <div class="io-modal__footer">
      <div class="io-status" id="<?= $modalId ?>_status"></div>
      <div class="io-modal__footer-actions">
        <button class="btn" type="button" data-close="1">Cerrar</button>
        <!-- Botón "Guardar" opcional: el JS del programa lo puede usar si existe -->
        <button class="btn btn-secondary" type="button"
        id="<?= $modalId ?>_btnGuardar"
        data-action="save">Guardar</button>
      </div>
    </div>
  </div>
</div>
