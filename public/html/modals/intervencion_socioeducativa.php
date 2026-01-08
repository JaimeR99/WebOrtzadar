<?php
// public/html/modals/intervencion_socioeducativa.php
// Modal de usuario: Intervención Socioeducativa
?>

<!-- Fuente de verdad para JS/backend -->
<input type="hidden" id="is_ambito" value="BF">
<input type="hidden" id="is_categoria" value="OBJ">

<!-- Ámbitos -->
<div class="io-modal__tabs is-tabs is-tabs--wrap" id="isAmbitos" aria-label="Ámbitos">
  <button type="button" class="is-io-tab is-active" data-is-ambito="BF">Bienestar físico</button>
  <button type="button" class="is-io-tab" data-is-ambito="BE">Bienestar emocional</button>
  <button type="button" class="is-io-tab" data-is-ambito="BM">Bienestar material</button>
  <button type="button" class="is-io-tab" data-is-ambito="RS">Relaciones sociales</button>
  <button type="button" class="is-io-tab" data-is-ambito="AD">Autodeterminación</button>
  <button type="button" class="is-io-tab" data-is-ambito="DP">Desarrollo personal</button>
  <button type="button" class="is-io-tab" data-is-ambito="IS">Integración social</button>
  <button type="button" class="is-io-tab" data-is-ambito="DR">Derechos</button>
</div>

<!-- Categoría -->
<div class="io-subtabs is-subtabs" id="isCategorias" aria-label="Categoría">
  <button type="button" class="is-io-tab is-active" data-is-categoria="OBJ">Objetivos</button>
  <button type="button" class="is-io-tab" data-is-categoria="ACC">Acciones</button>
  <button type="button" class="is-io-tab" data-is-categoria="IND">Indicadores</button>
</div>

<!-- Registros (lista con scrollbar) -->
<div class="io-section is-registros" style="margin-top:12px; margin-bottom:10px;">
  <div id="isRegistrosStatus" class="io-status" aria-live="polite"></div>

  <!-- Este contenedor es el que scrollea -->
  <div class="is-registros__list" id="isRegistrosList"></div>

  <div class="is-registros__actions">
    <button type="button" class="btn btn-primary" id="is_btn_nuevo_registro">Nuevo registro</button>
  </div>
</div>

<!-- Modal de Nuevo Comentario (se superpone a los registros) -->
<div id="modalNuevoComentario" class="io-modal" aria-hidden="true">
  <div class="io-modal__overlay" onclick="closeNuevoComentario()"></div>
  <div class="io-modal__dialog">
    <div class="io-modal__top">
      <div class="io-modal__name">Nuevo Comentario</div>
      <button class="io-icon-btn" onclick="closeNuevoComentario()">×</button>
    </div>
    <div class="io-modal__content">
      <!-- Mostramos Ámbito y Categoría como texto (y se actualizan dinámicamente) -->

      <div id="modal_ambito" class="modal-info" style="margin-bottom: var(--space-2);">Bienestar físico</div> <!-- Mostrar Ámbito seleccionado -->

      <div id="modal_categoria" class="modal-info" style="margin-bottom: var(--space-2);">Objetivos</div> <!-- Mostrar Categoría seleccionada -->

      <div class="io-section">
        <label for="modal_comentario">Comentario</label>
        <textarea id="modal_comentario" rows="4" placeholder="Escribe tu comentario aquí..."></textarea>
      </div>
      <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 12px;">
        <button type="button" class="btn" onclick="closeNuevoComentario()">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarNuevoComentario()">Guardar</button>
      </div>
    </div>
  </div>
</div>



