<?php
// public/html/modals/formaciones_modal.php
?>

<!-- SUBTABS + ACTIONS BAR (FIJO, NO SCROLL) -->
<div class="form-subtabsbar" data-form-subtabsbar>
  <div class="io-subtabs" role="tablist" data-tab-scope="formaciones" aria-label="Formaciones">
    <button class="io-tab is-active"
      type="button"
      role="tab"
      aria-selected="true"
      tabindex="0"
      data-tab="tab_form_datos"
      aria-controls="tab_form_datos">Datos</button>

    <button class="io-tab"
      type="button"
      role="tab"
      aria-selected="false"
      tabindex="-1"
      data-tab="tab_form_participantes"
      aria-controls="tab_form_participantes">Participantes</button>

    <button class="io-tab"
      type="button"
      role="tab"
      aria-selected="false"
      tabindex="-1"
      data-tab="tab_form_acta"
      aria-controls="tab_form_acta">Acta</button>
  </div>

  <!-- Acciones SOLO para Acta -->
  <div class="form-subtabs-actions" aria-label="Acciones de acta" hidden>
    <button class="btn" type="button" id="btnNuevaActa">+ Acta</button>
    <button class="btn btn-secondary" type="button" id="formBtnExportActa">Exportar</button>
  </div>
</div>

<!-- ✅ SCROLL SOLO PARA EL CONTENIDO DE FORMACIONES -->
<div class="io-modal__scroll" data-form-scroll>

  <!-- PANE: DATOS -->
  <div class="io-pane is-active" id="tab_form_datos" role="tabpanel" data-tab-scope="formaciones" aria-hidden="false">
    <div class="io-sections">

      <section class="io-section">
        <div class="io-section__head">
          <div class="io-section__title">Datos de la formación</div>
          <div class="io-section__sub io-muted">Nombre, fecha, tipo e integrador</div>
        </div>

        <div class="io-section__body">

          <div class="form-datos-grid">
            <!-- FOTO (izquierda) -->
            <div class="form-photo-col">
              <div class="form-photo is-no-photo" id="formacionAvatar" role="button" tabindex="0" title="Cambiar foto">
                <img src="" alt="">
                <span class="form-photo__fallback">FO</span>
                <span class="form-photo__overlay">Cambiar foto</span>
              </div>
              <input id="f_foto" type="file" accept="image/jpeg,image/png,image/webp" style="display:none">
            </div>

            <!-- FORM (derecha) -->
            <div class="form-form-col">
              <div class="form-form-stack">

                <div class="io-row">
                  <label class="io-label" for="f_nombre">Nombre</label>
                  <div class="io-value">
                    <input class="io-input" id="f_nombre" type="text" placeholder="Formación...">
                  </div>
                </div>

                <div class="io-row">
                  <label class="io-label" for="f_fecha">Fecha</label>
                  <div class="io-value">
                    <input class="io-input" id="f_fecha" type="datetime-local">
                  </div>
                </div>

                <!-- ✅ Tipo de formación -->
                <div class="io-row">
                  <label class="io-label" for="f_id_tipo">Tipo de formación</label>
                  <div class="io-value">
                    <select class="io-select" id="f_id_tipo">
                      <option value="">—</option>
                    </select>
                  </div>
                </div>

                <div class="io-row form-person-row">
                  <label class="io-label" for="f_id_integrador">Integrador</label>
                  <div class="io-value form-person-control">
                    <div class="form-mini-avatar is-no-photo" id="formIntegAvatar" aria-hidden="true">
                      <img src="" alt="">
                      <span class="form-mini-avatar__fallback">I</span>
                    </div>
                    <select class="io-select" id="f_id_integrador">
                      <option value="">—</option>
                    </select>
                  </div>
                </div>

                <input type="hidden" id="f_id" value="0">
              </div>
            </div>
          </div>

        </div>
      </section>

    </div>
  </div>

  <!-- PANE: PARTICIPANTES -->
  <div class="io-pane" id="tab_form_participantes" role="tabpanel" data-tab-scope="formaciones" aria-hidden="true">
    <div class="io-sections">

      <section class="io-section">
        <div class="io-section__head">
          <div class="io-section__title">Participantes</div>
          <div class="io-section__sub io-muted">Listado y gestión de participantes</div>
        </div>

        <div class="io-section__body">
          <div class="io-form-grid">

            <div class="io-row io-row--full">
              <label class="io-label" for="formPartBuscar">Añadir participante</label>
              <div class="io-value">
                <input class="io-input" id="formPartBuscar" type="text" placeholder="Buscar por nombre o DNI...">
                <div id="formPartResultados" class="io-muted" style="margin-top:8px;"></div>
              </div>
            </div>

            <div class="io-row io-row--full">
              <div class="io-value">
                <div id="formPartList" class="io-muted">—</div>
              </div>
            </div>

          </div>
        </div>
      </section>

    </div>
  </div>

  <!-- PANE: ACTA -->
  <div class="io-pane" id="tab_form_acta" role="tabpanel" data-tab-scope="formaciones" aria-hidden="true">
    <div class="io-sections">

      <section class="io-section">
        <div class="io-section__head">
          <div class="io-section__title">Acta de formación</div>
          <div class="io-section__sub io-muted">Asistencia, fecha, valoración e incidencias</div>
        </div>

        <div class="io-section__body">
          <input type="hidden" id="form_acta_id" value="0">

          <div class="io-form-grid">

            <div class="io-row">
              <label class="io-label" for="form_acta_fecha">Fecha</label>
              <div class="io-value">
                <input class="io-input" id="form_acta_fecha" type="datetime-local">
              </div>
            </div>

            <!-- ASISTENCIA -->
            <div class="io-row io-row--full">
              <label class="io-label">Asistencia</label>
              <div class="io-value">
                <div id="a_asistencia" class="io-muted">—</div>
              </div>
            </div>

            <div class="io-row io-row--full">
              <label class="io-label" for="form_acta_valoracion">Valoración</label>
              <div class="io-value">
                <textarea class="io-input" id="form_acta_valoracion" rows="4" placeholder="Valoración..."></textarea>
              </div>
            </div>

            <!-- INCIDENCIAS -->
            <div class="io-row io-row--full">
              <label class="io-label">Incidencias</label>
              <div class="io-value">

                <textarea class="io-input" id="a_incidencia" rows="3"
                  placeholder="Registrar incidencias..."></textarea>

                <div class="form-acta-actions" style="margin-top:10px;">
                  <button class="btn btn-secondary" type="button" id="btnAddIncidencia">Añadir incidencia</button>
                  <button class="btn btn-primary" type="button" id="formBtnGuardarActa">Guardar acta</button>
                </div>

                <div id="formIncList" class="io-muted" style="margin-top:8px;">—</div>
              </div>
            </div>

          </div>

        </div>
      </section>

      <section class="io-section" style="margin-bottom: var(--space-3);">
        <div class="io-section__head">
          <div class="io-section__title">Histórico</div>
          <div class="io-section__sub io-muted">Actas anteriores</div>
        </div>

        <div class="io-section__body">
          <div id="a_hist" class="io-muted">—</div>
        </div>
      </section>

    </div>
  </div>

</div>
