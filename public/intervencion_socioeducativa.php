<?php
?>
<div class="app io">

  <div class="app__header">
    <div>
      <h1 class="app__title">Intervencion Socioeducativa</h1>
      <p class="app__subtitle">Altas IO (1 por persona) y edición de ficha.</p>
    </div>

    <button class="btn btn-primary" id="btnNuevaAcogida">+ Nueva Intervención Socioeducativa</button>
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


<div id="modalNuevaIntervencion" class="io-modal">
  <div class="io-modal__overlay"></div>

  <div class="io-modal__content">
    <header class="io-modal__header">
      <h2>Nueva Intervención Socioeducativa</h2>
      <button class="io-modal__close" id="closeNuevaIntervencion">×</button>
    </header>

    <section class="io-modal__body">
      <div id="usuariosNoRegistradosStatus" class="io-muted">Cargando usuarios…</div>
      <div id="usuariosNoRegistradosGrid" class="trabajadores-grid"></div>
    </section>

    <footer class="io-modal__footer">
      <div id="nuevaIntervencionStatus" class="io-muted">Selecciona un usuario para continuar.</div>

      <div class="io-modal__footer-actions">
        <button class="btn btn-primary" id="btnIniciarIntervencion" disabled>
          Iniciar intervención →
        </button>
      </div>
    </footer>
  </div>
</div>



<!-- ===========================
     MODAL FICHA (Usuario + Programa IO)
=========================== -->
<?php
// Modal reutilizable de Usuario + extensión del Programa Intervencion Social
$USER_MODAL_PROGRAM = [
  'key' => 'is',
  'label' => 'Intervencion Socioeducativa',
  'partial' => __DIR__ . '/html/modals/intervencion_socioeducativa.php',
];
include __DIR__ . '/html/modals/user_modal.php';
?>