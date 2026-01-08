<?php
// public/html/modals/ocio_modal.php
?>

<!-- SUBTABS + ACTIONS BAR (FIJO, NO SCROLL) -->
<div class="ocio-subtabsbar" data-ocio-subtabsbar>
  <div class="io-subtabs" role="tablist" data-tab-scope="ocio" aria-label="Ocio">
    <button class="io-tab is-active"
      type="button"
      role="tab"
      aria-selected="true"
      tabindex="0"
      data-tab="tab_ocio_datos"
      aria-controls="tab_ocio_datos">Datos</button>

    <button class="io-tab"
      type="button"
      role="tab"
      aria-selected="false"
      tabindex="-1"
      data-tab="tab_ocio_participantes"
      aria-controls="tab_ocio_participantes">Participantes</button>

    <button class="io-tab"
      type="button"
      role="tab"
      aria-selected="false"
      tabindex="-1"
      data-tab="tab_ocio_acta"
      aria-controls="tab_ocio_acta">Acta</button>
  </div>

  <!-- Acciones SOLO para Acta (se muestran/ocultan vía clase en el modal) -->
  <div class="ocio-subtabs-actions" aria-label="Acciones de acta">
    <button class="btn" type="button" id="btnNuevaActa">+ Nueva acta</button>
    <button class="btn btn-secondary" type="button" id="btnExportActa">Exportar</button>
  </div>
</div>

<!-- ✅ SCROLL SOLO PARA EL CONTENIDO DE OCIO -->
<div class="io-modal__scroll" data-ocio-scroll>

  <!-- PANE: DATOS -->
  <div class="io-pane is-active" id="tab_ocio_datos" role="tabpanel" data-tab-scope="ocio" aria-hidden="false">
    <div class="io-sections">

      <section class="io-section">
        <div class="io-section__head">
          <div class="io-section__title">Datos del grupo</div>
          <div class="io-section__sub io-muted">Nombre, fecha y responsable</div>
        </div>

        <div class="io-section__body">
          <div class="io-row">
            <div class="io-value">
              <div class="ocio-avatar lg is-no-photo" id="ocioGroupAvatar" role="button" tabindex="0" title="Cambiar foto">
                <img src="" alt="">
                <span class="ocio-avatar__fallback">OC</span>
              </div>
              <input id="g_foto" type="file" accept="image/jpeg,image/png,image/webp" style="display:none">
            </div>
          </div>

          <div class="io-form-grid">
            <div class="io-row io-row--full">
              <label class="io-label" for="g_nombre">Nombre</label>
              <div class="io-value">
                <input class="io-input" id="g_nombre" type="text" placeholder="Grupo...">
              </div>
            </div>

            <div class="io-row">
              <label class="io-label" for="g_fecha">Fecha</label>
              <div class="io-value">
                <input class="io-input" id="g_fecha" type="datetime-local">
              </div>
            </div>

            <div class="io-row">
              <label class="io-label" for="g_id_responsable">Responsable</label>
              <div class="io-value">
                <div class="ocio-resp-picker">
                  <div class="ocio-avatar is-no-photo" id="ocioRespAvatar" aria-hidden="true">
                    <img src="" alt="">
                    <span class="ocio-avatar__fallback">TS</span>
                  </div>

                  <select class="io-select" id="g_id_responsable">
                    <option value="">—</option>
                  </select>
                </div>
              </div>

            </div>
          </div>

          <input type="hidden" id="g_id" value="">
        </div>
      </section>

    </div>
  </div>

  <!-- PANE: PARTICIPANTES -->
  <div class="io-pane" id="tab_ocio_participantes" role="tabpanel" data-tab-scope="ocio" aria-hidden="true">
    <div class="io-sections">

      <section class="io-section">
        <div class="io-section__head">
          <div class="io-section__title">Participantes</div>
          <div class="io-section__sub io-muted">Listado y gestión de participantes del grupo</div>
        </div>

        <div class="io-section__body">
          <div class="io-form-grid">

            <div class="io-row io-row--full">
              <label class="io-label" for="p_buscar">Añadir participante</label>
              <div class="io-value">
                <div style="display:flex; gap: var(--space-2);">
                  <input class="io-input" id="p_buscar" type="text" placeholder="Buscar por nombre o DNI...">
                </div>

                <div id="p_resultados" class="io-muted" style="margin-top:8px;"></div>
              </div>
            </div>

            <div class="io-row io-row--full">              
              <div class="io-value">
                <div id="p_list" class="io-muted">—</div>
              </div>
            </div>

          </div>
        </div>
      </section>

    </div>
  </div>

  <!-- PANE: ACTA -->
  <div class="io-pane" id="tab_ocio_acta" role="tabpanel" data-tab-scope="ocio" aria-hidden="true">
    <div class="io-sections">

      <section class="io-section">
        <div class="io-section__head">
          <div class="io-section__title">Acta de actividad</div>
          <div class="io-section__sub io-muted">Asistencia, fecha, valoración e incidencias</div>
        </div>

        <div class="io-section__body">
          <input type="hidden" id="a_id" value="">

          <div class="io-form-grid">

            <div class="io-row">
              <label class="io-label" for="a_fecha">Fecha actividad</label>
              <div class="io-value">
                <input class="io-input" id="a_fecha" type="datetime-local">
              </div>
            </div>

            <div class="io-row io-row--full">
              <label class="io-label">Asistencia</label>
              <div class="io-value">
                <div id="a_asistencia" class="io-muted">—</div>
              </div>
            </div>

            <div class="io-row io-row--full">
              <label class="io-label" for="a_valoracion">Valoración</label>
              <div class="io-value">
                <textarea class="io-input" id="a_valoracion" rows="4" placeholder="Valoración de la actividad..."></textarea>
              </div>
            </div>

            <div class="io-row io-row--full">
              <label class="io-label" for="a_incidencia">Incidencias</label>
              <div class="io-value">
                <textarea class="io-input" id="a_incidencia" rows="3" placeholder="Registrar incidencias..."></textarea>

                <div class="ocio-acta-actions">
                  <button class="btn btn-secondary" type="button" id="btnAddIncidencia">Añadir incidencia</button>
                  <button class="btn btn-primary" type="button" id="btnGuardarActa">Guardar acta</button>
                </div>

                <div id="a_incidencias_list" class="io-muted" style="margin-top:8px;">—</div>
              </div>
            </div>

          </div>
        </div>
      </section>

      <section class="io-section" style="margin-bottom: var(--space-3);">
        <div class="io-section__head">
          <div class="io-section__title">Histórico</div>
          <div class="io-section__sub io-muted">Actas anteriores del grupo</div>
        </div>

        <div class="io-section__body">
          <div id="a_hist" class="io-muted">—</div>
        </div>
      </section>

    </div>
  </div>

</div>