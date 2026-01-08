<?php
// public/partials/programas/io_modal_extension.php
?>
<div class="io-subtabs" role="tablist" data-tab-scope="io" aria-label="Pestañas de programa IO">
  <button class="io-tab is-active" type="button" data-tab="tab_io_generales" role="tab" aria-controls="tab_io_generales">Datos generales</button>
  <button class="io-tab" type="button" data-tab="tab_io_entorno" role="tab" aria-controls="tab_io_entorno">Entorno personal</button>
  <button class="io-tab" type="button" data-tab="tab_io_redes" role="tab" aria-controls="tab_io_redes">Redes de apoyo</button>
  <button class="io-tab" type="button" data-tab="tab_io_demanda" role="tab" aria-controls="tab_io_demanda">Demanda y acuerdo</button>
</div>

<div class="io-modal__scroll">
  <div class="io-pane is-active" id="tab_io_generales" role="tabpanel" data-tab-scope="io">
    <div class="io-card io-section">
      <div class="io-section__head">
        <div class="io-section__title">Datos generales</div>
        <div class="io-section__sub io-muted">Datos principales del registro IO</div>
      </div>

      <div class="io-section__body">
        <div class="io-form-grid">
          <div class="io-row">
            <label class="io-label" for="io_fecha">Fecha IO</label>
            <div class="io-value"><input class="io-input" id="io_fecha" type="datetime-local"></div>
          </div>

          <div class="io-row">
            <label class="io-label" for="io_id_tipo_atencion">Tipo atención (id)</label>
            <div class="io-value"><input class="io-input" id="io_id_tipo_atencion" type="number" min="1"></div>
          </div>

          <div class="io-row">
            <label class="io-label" for="io_id_orientado_por">Orientado por (id)</label>
            <div class="io-value"><input class="io-input" id="io_id_orientado_por" type="number" min="1"></div>
          </div>

          <div class="io-row">
            <label class="io-label" for="io_id_realizado_por">Realizado por (id)</label>
            <div class="io-value"><input class="io-input" id="io_id_realizado_por" type="number" min="1"></div>
          </div>

          <div class="io-row">
            <label class="io-label" for="io_tipo_demanda">Tipo demanda</label>
            <div class="io-value"><input class="io-input" id="io_tipo_demanda" maxlength="50" type="text"></div>
          </div>

          <div class="io-row">
            <label class="io-label" for="io_lugar_entrevista">Lugar entrevista</label>
            <div class="io-value"><input class="io-input" id="io_lugar_entrevista" maxlength="50" type="text"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="io-pane" id="tab_io_entorno" role="tabpanel" data-tab-scope="io">
    <div class="io-card io-section">
      <div class="io-section__head">
        <div class="io-section__title">Entorno personal</div>
        <div class="io-section__sub io-muted">Contexto y situación</div>
      </div>

      <div class="io-section__body">
        <div class="io-form-grid">
          <div class="io-row io-row--textarea">
            <label class="io-label" for="io_composicion_familiar">Composición familiar</label>
            <div class="io-value"><textarea class="io-textarea" id="io_composicion_familiar" rows="2"></textarea></div>
          </div>

          <div class="io-row io-row--textarea">
            <label class="io-label" for="io_h_escolar_formacion">Escolar / formación</label>
            <div class="io-value"><textarea class="io-textarea" id="io_h_escolar_formacion" rows="2"></textarea></div>
          </div>

          <div class="io-row io-row--textarea">
            <label class="io-label" for="io_relaciones_sociales">Relaciones sociales</label>
            <div class="io-value"><textarea class="io-textarea" id="io_relaciones_sociales" rows="2"></textarea></div>
          </div>

          <div class="io-row io-row--textarea">
            <label class="io-label" for="io_salud">Salud</label>
            <div class="io-value"><textarea class="io-textarea" id="io_salud" rows="2"></textarea></div>
          </div>

          <div class="io-row io-row--textarea">
            <label class="io-label" for="io_autonomia">Autonomía</label>
            <div class="io-value"><textarea class="io-textarea" id="io_autonomia" rows="2"></textarea></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="io-pane" id="tab_io_redes" role="tabpanel" data-tab-scope="io">
    <div class="io-card io-section">
      <div class="io-section__head">
        <div class="io-section__title">Redes de apoyo</div>
        <div class="io-section__sub io-muted">Apoyos disponibles</div>
      </div>

      <div class="io-section__body">
        <div class="io-form-grid">
          <div class="io-row io-row--textarea">
            <label class="io-label" for="io_red_apo_ss_base">Red SS base</label>
            <div class="io-value"><textarea class="io-textarea" id="io_red_apo_ss_base" rows="2"></textarea></div>
          </div>

          <div class="io-row io-row--textarea">
            <label class="io-label" for="io_red_apo_csm">Red CSM</label>
            <div class="io-value"><textarea class="io-textarea" id="io_red_apo_csm" rows="2"></textarea></div>
          </div>

          <div class="io-row io-row--textarea">
            <label class="io-label" for="io_red_apo_familia">Red familia</label>
            <div class="io-value"><textarea class="io-textarea" id="io_red_apo_familia" rows="2"></textarea></div>
          </div>

          <div class="io-row io-row--textarea">
            <label class="io-label" for="io_red_apo_otros">Red otros</label>
            <div class="io-value"><textarea class="io-textarea" id="io_red_apo_otros" rows="2"></textarea></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="io-pane" id="tab_io_demanda" role="tabpanel" data-tab-scope="io">
    <div class="io-card io-section">
      <div class="io-section__head">
        <div class="io-section__title">Demanda y acuerdo</div>
        <div class="io-section__sub io-muted">Resumen y plan</div>
      </div>

      <div class="io-section__body">
        <div class="io-form-grid">
          <div class="io-row io-row--textarea">
            <label class="io-label" for="io_demanda">Demanda</label>
            <div class="io-value"><textarea class="io-textarea" id="io_demanda" rows="3"></textarea></div>
          </div>

          <div class="io-row io-row--textarea">
            <label class="io-label" for="io_acuerdo">Acuerdo</label>
            <div class="io-value"><textarea class="io-textarea" id="io_acuerdo" rows="3"></textarea></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
