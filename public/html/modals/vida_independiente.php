<?php
// public/html/modals/vida_independiente.php
//
// OJO: Este fichero se incluye DENTRO de #tab_main_program (user_modal.php).
// Por tanto, NO debe crear otro .io-pane con id="tab_main_program".
?>

<div class="io-modal__scroll">

    <div class="io-modal__tabs" id="isAmbitos" role="tablist" aria-label="Ámbitos">
      <button class="io-tab is-active" type="button" data-is-ambito="BF">Bienestar físico</button>
      <button class="io-tab" type="button" data-is-ambito="BE">Bienestar emocional</button>
      <button class="io-tab" type="button" data-is-ambito="BM">Bienestar material</button>
      <button class="io-tab" type="button" data-is-ambito="RS">Relaciones sociales</button>
      <button class="io-tab" type="button" data-is-ambito="AD">Autodeterminación</button>
      <button class="io-tab" type="button" data-is-ambito="DP">Desarrollo personal</button>
      <button class="io-tab" type="button" data-is-ambito="IS">Integración social</button>
      <button class="io-tab" type="button" data-is-ambito="DR">Derechos</button>
      <button class="io-tab" type="button" data-is-ambito="RI">Responsable/Integrador</button>
    </div>

    <div class="io-subtabs" id="isCategorias" role="tablist" aria-label="Categorías">
      <button class="io-tab is-active" type="button" data-is-categoria="OBJ">Objetivos</button>
      <button class="io-tab" type="button" data-is-categoria="ACC">Acciones</button>
      <button class="io-tab" type="button" data-is-categoria="IND">Indicadores</button>
    </div>

    <div class="io-card io-section" id="viRespIntPanel" style="display:none;">
      <div class="io-section__head vi-head-row">
        <div>
          <div class="io-section__title">Responsable e integrador</div>
          <div class="io-section__sub io-muted">Asigna responsables del plan (obligatorio).</div>
        </div>
        <button type="button" class="btn btn-primary" id="vi_btn_save_respint">
          <i class="fa-solid fa-floppy-disk"></i> Guardar
        </button>
      </div>
      <div class="io-section__body">
        <div class="io-form-grid">
          <div class="io-row">
            <label class="io-label" for="vi_responsable_id">Responsable</label>
            <div class="io-value io-field-with-avatar">
              <div class="io-avatar io-avatar--mini is-no-photo" id="viRespAvatar" aria-hidden="true">
                <img class="io-avatar__img" src="" alt="">
                <span class="io-avatar__txt">R</span>
              </div>
              <select class="io-select" id="vi_responsable_id"></select>
            </div>
          </div>
          <div class="io-row">
            <label class="io-label" for="vi_integrador_id">Integrador</label>
            <div class="io-value io-field-with-avatar">
              <div class="io-avatar io-avatar--mini is-no-photo" id="viIntegAvatar" aria-hidden="true">
                <img class="io-avatar__img" src="" alt="">
                <span class="io-avatar__txt">I</span>
              </div>
              <select class="io-select" id="vi_integrador_id"></select>
            </div>
          </div>
        </div>
        <div class="io-status" id="vi_respint_status" style="margin-top:10px;"></div>
      </div>
    </div>

    <div class="io-card io-section is-registros" id="viRegistrosPanel">
      <div class="io-section__head">
        <div class="io-section__title">Registros</div>
        <div class="io-section__sub io-muted">Histórico por ámbito y categoría</div>
      </div>
      <div class="io-section__body">
        <input type="hidden" id="is_ambito" value="BF">
        <input type="hidden" id="is_categoria" value="OBJ">

        <div class="is-registros__list" id="isRegistrosList"></div>
        <div class="io-status" id="isRegistrosStatus" style="margin-top:8px;"></div>

        <div class="is-registros__actions">
          <button type="button" class="btn btn-primary" id="is_btn_nuevo_registro">
            <i class="fa-solid fa-plus"></i> Nuevo registro
          </button>
        </div>
      </div>
    </div>

  </div>

</div>

<div id="modalNuevoComentario" class="io-modal" aria-hidden="true">
  <div class="io-modal__overlay" onclick="closeNuevoComentario()"></div>
  <div class="io-modal__dialog" role="dialog" aria-modal="true" aria-label="Nuevo comentario">
    <div class="io-modal__top">
      <div class="io-modal__top-left">
        <div class="io-modal__top-titles">
          <div class="io-modal__name">Nuevo comentario</div>
          <div class="io-modal__sub io-muted" id="modal_ambito">—</div>
          <div class="io-modal__sub io-muted" id="modal_categoria">—</div>
        </div>
      </div>
      <button class="io-icon-btn" type="button" onclick="closeNuevoComentario()" aria-label="Cerrar">×</button>
    </div>

    <div class="io-modal__content">
      <div class="io-modal__scroll">
        <div class="io-row">
          <label class="io-label" for="modal_comentario">Comentario</label>
          <textarea class="io-textarea" id="modal_comentario" rows="5" placeholder="Escribe tu comentario aquí…"></textarea>
        </div>

        <div class="io-modal__footer-actions" style="justify-content:flex-end; margin-top:12px;">
          <button type="button" class="btn" onclick="closeNuevoComentario()">Cancelar</button>
          <button type="button" class="btn btn-primary" onclick="guardarNuevoComentario()">Guardar</button>
        </div>
      </div>
    </div>
  </div>
</div>
