<?php
// public/programas_vida_independiente.php
?>

<div class="app vida-independiente">

  <div class="app__header">
    <div>
      <h1 class="app__title">Vida independiente</h1>
      <p class="app__subtitle">Personas usuarias (sin revisiones).</p>
    </div>

    <div class="app__actions">
      <button id="vi_btn_new" type="button" class="btn btn-primary">+ Añadir nuevo</button>
    </div>
  </div>

  <div class="list" id="viList" aria-live="polite">
    <div class="empty">Cargando…</div>
  </div>

</div>

<!-- Mini modal: añadir usuario existente (lo necesita el JS) -->
<div class="viv-mini" id="viMiniAddUser" aria-hidden="true">
  <div class="viv-mini__overlay" data-close="1"></div>
  <div class="viv-mini__card" role="dialog" aria-modal="true">
    <div class="viv-mini__head">
      <div class="viv-mini__title">Añadir usuario a Vida independiente</div>
      <button class="io-icon-btn" type="button" data-close="1" aria-label="Cerrar">×</button>
    </div>
    <div class="viv-mini__body">
      <div class="io-field">
        <label class="io-label">Buscar (nombre, apellidos o DNI)</label>
        <input class="io-input" id="vi_buscar" type="text" placeholder="Escribe al menos 2 letras…">
      </div>
      <div class="viv-mini__results" id="vi_buscar_res"></div>
    </div>
  </div>
</div>

<?php
// ✅ Enchufamos el modal reutilizable (Usuario + Vida independiente)
$USER_MODAL_PROGRAM = [
  'key' => 'vida_independiente',
  'label' => 'Vida independiente',
  'partial' => __DIR__ . '/html/modals/vida_independiente.php',
];
include __DIR__ . '/html/modals/user_modal.php';
?>
