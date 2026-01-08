<?php
// public/html/modals/vacaciones_modal.php
?>

<!-- SUBTABS + ACTIONS BAR (FIJO, NO SCROLL) -->
<div class="vac-subtabsbar" data-vac-subtabsbar>
  <div class="io-subtabs" role="tablist" data-tab-scope="vacaciones" aria-label="Vacaciones">
    <button class="io-tab is-active"
      type="button"
      role="tab"
      aria-selected="true"
      tabindex="0"
      data-tab="tab_vac_datos"
      aria-controls="tab_vac_datos">Datos</button>

    <button class="io-tab"
      type="button"
      role="tab"
      aria-selected="false"
      tabindex="-1"
      data-tab="tab_vac_participantes"
      aria-controls="tab_vac_participantes">Participantes</button>

    <button class="io-tab"
      type="button"
      role="tab"
      aria-selected="false"
      tabindex="-1"
      data-tab="tab_vac_acta"
      aria-controls="tab_vac_acta">Acta</button>
  </div>

  <!-- Acciones SOLO para Acta (se muestran/ocultan vía clase en el modal) -->
  <div class="vac-subtabs-actions" aria-label="Acciones de acta" hidden>
    <button class="btn" type="button" id="btnNuevaActa">+ Acta</button>
    <button class="btn btn-secondary" type="button" id="vacBtnExportActa">Exportar</button>
  </div>

</div>

<!-- ✅ SCROLL SOLO PARA EL CONTENIDO DE VACACIONES -->
<div class="io-modal__scroll" data-vac-scroll>

  <!-- PANE: DATOS -->
  <!-- PANE: DATOS -->
  <div class="io-pane is-active" id="tab_vac_datos" role="tabpanel" data-tab-scope="vacaciones" aria-hidden="false">
    <div class="io-sections">

      <section class="io-section">
        <div class="io-section__head">
          <div class="io-section__title">Datos del destino</div>
          <div class="io-section__sub io-muted">Nombre, fecha, responsable e integrador</div>
        </div>

        <!-- ✅ ESTO TE FALTABA: body -->
        <div class="io-section__body">

          <div class="vac-datos-grid">
            <!-- FOTO (izquierda) -->
            <div class="vac-photo-col">
              <div class="vac-photo is-no-photo" id="vacDestinoAvatar" role="button" tabindex="0" title="Cambiar foto">
                <img src="" alt="">
                <span class="vac-photo__fallback">VA</span>
                <span class="vac-photo__overlay">Cambiar foto</span>
              </div>
              <input id="v_foto" type="file" accept="image/jpeg,image/png,image/webp" style="display:none">
            </div>

            <!-- FORM (derecha) -->
            <div class="vac-form-col">
              <div class="vac-form-stack">
                <div class="io-row">
                  <label class="io-label" for="v_nombre">Nombre</label>
                  <div class="io-value">
                    <input class="io-input" id="v_nombre" type="text" placeholder="Destino...">
                  </div>
                </div>

                <div class="io-row">
                  <label class="io-label" for="v_fecha">Fecha</label>
                  <div class="io-value">
                    <input class="io-input" id="v_fecha" type="datetime-local">
                  </div>
                </div>

                <div class="io-row vac-person-row">
                  <label class="io-label" for="v_id_responsable">Responsable</label>
                  <div class="io-value vac-person-control">
                    <div class="vac-mini-avatar is-no-photo" id="vacRespAvatar" aria-hidden="true">
                      <img src="" alt="">
                      <span class="vac-mini-avatar__fallback">R</span>
                    </div>
                    <select class="io-select" id="v_id_responsable">
                      <option value="">—</option>
                    </select>
                  </div>
                </div>

                <div class="io-row vac-person-row">
                  <label class="io-label" for="v_id_integrador">Integrador</label>
                  <div class="io-value vac-person-control">
                    <div class="vac-mini-avatar is-no-photo" id="vacIntegAvatar" aria-hidden="true">
                      <img src="" alt="">
                      <span class="vac-mini-avatar__fallback">I</span>
                    </div>
                    <select class="io-select" id="v_id_integrador">
                      <option value="">—</option>
                    </select>
                  </div>
                </div>

                <input type="hidden" id="v_id" value="0">
              </div>
            </div>
          </div>


        </div>
      </section>

    </div>
  </div>


  <!-- PANE: PARTICIPANTES -->
  <div class="io-pane" id="tab_vac_participantes" role="tabpanel" data-tab-scope="vacaciones" aria-hidden="true">
    <div class="io-sections">

      <section class="io-section">
        <div class="io-section__head">
          <div class="io-section__title">Participantes</div>
          <div class="io-section__sub io-muted">Listado y gestión de participantes del destino</div>
        </div>

        <div class="io-section__body">
          <div class="io-form-grid">

            <div class="io-row io-row--full">
              <label class="io-label" for="vacPartBuscar">Añadir participante</label>
              <div class="io-value">
                <input class="io-input" id="vacPartBuscar" type="text" placeholder="Buscar por nombre o DNI...">
                <div id="vacPartResultados" class="io-muted" style="margin-top:8px;"></div>
              </div>
            </div>

            <div class="io-row io-row--full">              
              <div class="io-value">
                <div id="vacPartList" class="io-muted">—</div>
              </div>
            </div>

          </div>
        </div>
      </section>

    </div>
  </div>

  <!-- PANE: ACTA -->
  <div class="io-pane" id="tab_vac_acta" role="tabpanel" data-tab-scope="vacaciones" aria-hidden="true">
    <div class="io-sections">

      <section class="io-section">
        <div class="io-section__head">
          <div class="io-section__title">Acta de actividad</div>
          <div class="io-section__sub io-muted">Asistencia, fecha, valoración e incidencias</div>
        </div>

        <div class="io-section__body">
          <input type="hidden" id="vac_acta_id" value="0">

          <div class="io-form-grid">

            <div class="io-row">
              <label class="io-label" for="vac_acta_fecha">Fecha actividad</label>
              <div class="io-value">
                <input class="io-input" id="vac_acta_fecha" type="datetime-local">
              </div>
            </div>

            <!-- ✅ ASISTENCIA (como Ocio) -->
            <div class="io-row io-row--full">
              <label class="io-label">Asistencia</label>
              <div class="io-value">
                <div id="a_asistencia" class="io-muted">—</div>
              </div>
            </div>

            <div class="io-row io-row--full">
              <label class="io-label" for="vac_acta_valoracion">Valoración</label>
              <div class="io-value">
                <textarea class="io-input" id="vac_acta_valoracion" rows="4" placeholder="Valoración de la actividad..."></textarea>
              </div>
            </div>

            <!-- INCIDENCIAS (del acta) -->
            <div class="io-row io-row--full">
              <label class="io-label">Incidencias</label>
              <div class="io-value">

                <!-- ✅ textarea real -->
                <textarea class="io-input" id="a_incidencia" rows="3"
                  placeholder="Registrar incidencias..."></textarea>

                <div class="vac-acta-actions" style="margin-top:10px;">
                  <button class="btn btn-secondary" type="button" id="btnAddIncidencia">Añadir incidencia</button>
                  <button class="btn btn-primary" type="button" id="vacBtnGuardarActa">Guardar acta</button>
                </div>

                <div id="vacIncList" class="io-muted" style="margin-top:8px;">—</div>
              </div>
            </div>



          </div>


        </div>
      </section>

      <section class="io-section" style="margin-bottom: var(--space-3);">
        <div class="io-section__head">
          <div class="io-section__title">Histórico</div>
          <div class="io-section__sub io-muted">Actas anteriores del destino</div>
        </div>

        <div class="io-section__body">
          <div id="a_hist" class="io-muted">—</div>
        </div>
      </section>

    </div>
  </div>

</div>