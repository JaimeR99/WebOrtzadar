<?php
// public/html/modals/vivienda_modal.php
?>

<!-- SUBTABS (FIJOS) -->
<div class="viv-subtabsbar" data-viv-subtabsbar>
  <div class="io-subtabs" role="tablist" data-tab-scope="vivienda" aria-label="Vivienda">
    <button class="io-tab is-active"
      type="button"
      role="tab"
      aria-selected="true"
      tabindex="0"
      data-tab="tab_viv_datos"
      aria-controls="tab_viv_datos">Datos</button>

    <button class="io-tab"
      type="button"
      role="tab"
      aria-selected="false"
      tabindex="-1"
      data-tab="tab_viv_participantes"
      aria-controls="tab_viv_participantes">Participantes</button>

    <button class="io-tab"
      type="button"
      role="tab"
      aria-selected="false"
      tabindex="-1"
      data-tab="tab_viv_dinamicas"
      aria-controls="tab_viv_dinamicas">Dinámicas</button>

    <button class="io-tab"
      type="button"
      role="tab"
      aria-selected="false"
      tabindex="-1"
      data-tab="tab_viv_alimentacion"
      aria-controls="tab_viv_alimentacion">Alimentación y limpieza</button>

    <button class="io-tab"
      type="button"
      role="tab"
      aria-selected="false"
      tabindex="-1"
      data-tab="tab_viv_equipamientos"
      aria-controls="tab_viv_equipamientos">Equipamientos</button>
  </div>
</div>

<!-- SCROLL SOLO PARA EL CONTENIDO -->
<div class="io-modal__scroll" data-viv-scroll>

  <!-- PARTICIPANTES -->
  <div class="io-pane" id="tab_viv_participantes" role="tabpanel" data-tab-scope="vivienda" aria-hidden="true" hidden="hidden">
    <div class="io-sections">
      <section class="io-section">
        <div class="io-section__head io-section__head--row">
          <div>
            <div class="io-section__title">Participantes</div>
            <div class="io-section__sub io-muted">Usuarios asociados a la vivienda</div>
          </div>
          <button class="btn" type="button" id="viv_btn_add_part">+ Añadir participante</button>
        </div>

        <div class="io-section__body">
          <div class="io-form-grid">
            <div class="io-row io-row--full">
              <label class="io-label" for="viv_part_buscar">Filtrar participantes</label>
              <div class="io-value">
                <input type="text"
                  id="viv_part_buscar"
                  class="io-input"
                  placeholder="Buscar por nombre o DNI…"
                  autocomplete="off">

                <div id="viv_participantes_status" class="io-muted" style="margin-top:8px;"></div>
              </div>
            </div>

            <div class="io-row io-row--full">
              <div class="io-value">
                <div id="viv_part_header"></div>
                <div id="viv_participantes_grid" class="viv-part-grid"></div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>


  <!-- DINÁMICAS -->
  <div class="io-pane" id="tab_viv_dinamicas" role="tabpanel" data-tab-scope="vivienda" aria-hidden="true" hidden="hidden">
    <div class="io-section">
      <div class="io-section__head io-section__head--row">
        <div>
          <div class="io-section__title">Dinámicas grupales</div>
          <div class="io-section__sub">Histórico (ordenado por fecha)</div>
        </div>
        <button class="btn" type="button" id="viv_btn_add_dinamica">+ Añadir comentario</button>
      </div>

      <div class="io-muted" id="viv_dinamicas_status">Cargando…</div>
      <div class="viv-dyn-list" id="viv_dinamicas_list"></div>
    </div>
  </div>

  <!-- DATOS -->
  <div class="io-pane is-active" id="tab_viv_datos" role="tabpanel" data-tab-scope="vivienda" aria-hidden="false">
    <div class="io-section">
      <div class="io-section__head">
        <div class="io-section__title">Datos generales</div>
        <div class="io-section__sub">Dirección, localidad, responsables e imágenes</div>
      </div>

      <div class="io-section__body viv-datos-wrap">

        <!-- Foto vivienda -->
        <div class="viv-datos-foto">
          <div class="io-avatar viv-house-avatar is-no-photo"
            id="vivAvatar"
            role="button"
            tabindex="0"
            title="Cambiar foto vivienda">
            <img class="io-avatar__img" src="" alt="">
          </div>

          <input id="viv_foto" type="file" accept="image/jpeg,image/png,image/webp" style="display:none">
        </div>

        <!-- Campos -->
        <div class="viv-datos-fields">
          <div class="field">
            <label>Dirección</label>
            <input class="io-input" id="viv_direccion" type="text" placeholder="Calle..." />
          </div>

          <div class="field">
            <label>Localidad</label>
            <input class="io-input" id="viv_localidad" type="text" placeholder="Municipio..." />
          </div>

          <div class="viv-person-row">
            <div class="io-avatar viv-staff-avatar is-no-photo" id="vivRespAvatar" aria-hidden="true">
              <img class="io-avatar__img" alt="">
            </div>


            <div class="field" style="flex:1;">
              <label>Responsable programa</label>
              <select class="io-input" id="viv_responsable_programa">
                <option value="">—</option>
              </select>
            </div>
          </div>

          <div class="viv-person-row">
            <div class="io-avatar viv-staff-avatar is-no-photo" id="vivIntAvatar" aria-hidden="true">
              <img class="io-avatar__img" alt="">
            </div>


            <div class="field" style="flex:1;">
              <label>Integrador</label>
              <select class="io-input" id="viv_integrador">
                <option value="">—</option>
              </select>
            </div>
          </div>

          <!-- Meta -->
          <div class="viv-datos-meta">
            <div class="viv-meta-item">
              <div class="viv-meta-k">Creada en</div>
              <div class="viv-meta-v" id="viv_created_at">—</div>
            </div>
            <div class="viv-meta-item">
              <div class="viv-meta-k">Última actualización</div>
              <div class="viv-meta-v" id="viv_updated_at">—</div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- ALIMENTACIÓN Y LIMPIEZA -->
  <div class="io-pane" id="tab_viv_alimentacion" role="tabpanel" data-tab-scope="vivienda" aria-hidden="true" hidden="hidden">
    <div class="io-section">
      <div class="io-section__head io-section__head--row">
        <div>
          <div class="io-section__title">Menú semanal</div>
          <div class="io-section__sub">Histórico de menús (LMXJVSD)</div>
        </div>
        <button class="btn" type="button" id="viv_btn_add_menu">+ Añadir menú</button>
      </div>

      <div class="io-muted" id="viv_menu_status">Cargando…</div>
      <div class="viv-list" id="viv_menu_list"></div>
    </div>

    <div class="io-section" style="margin-top:16px;">
      <div class="io-section__head io-section__head--row">
        <div>
          <div class="io-section__title">Incidencias</div>
          <div class="io-section__sub">Histórico de incidencias</div>
        </div>
        <button class="btn" type="button" id="viv_btn_add_incidencia">+ Añadir incidencia</button>
      </div>

      <div class="io-muted" id="viv_inc_status">Cargando…</div>
      <div class="viv-list" id="viv_inc_list"></div>
    </div>
  </div>

  <!-- EQUIPAMIENTOS -->
  <div class="io-pane" id="tab_viv_equipamientos" role="tabpanel" data-tab-scope="vivienda" aria-hidden="true" hidden="hidden">
    <div class="io-section">
      <div class="io-section__head">
        <div class="io-section__title">Equipamientos</div>
        <div class="io-section__sub">Checklist y revisiones</div>
      </div>

      <div class="viv-eq-grid" id="viv_eq_checks">
        <label class="viv-eq-item"><input type="checkbox" data-eq="Caldera"> <span>Caldera</span></label>
        <label class="viv-eq-item"><input type="checkbox" data-eq="Gas"> <span>Gas</span></label>
        <label class="viv-eq-item"><input type="checkbox" data-eq="Extintores"> <span>Extintores</span></label>
        <label class="viv-eq-item"><input type="checkbox" data-eq="Contratos"> <span>Contratos</span></label>
        <label class="viv-eq-item"><input type="checkbox" data-eq="CEI"> <span>CEI</span></label>
        <label class="viv-eq-item" id="viv_eq_salto_wrap" hidden><input type="checkbox" data-eq="Salto"> <span>Salto</span></label>
      </div>

      <div class="viv-rev">
        <div class="viv-rev__head">
          <div>
            <div class="io-section__title" style="margin-bottom:2px;">Revisiones</div>
            <div class="io-muted">Registra fechas de revisión y próxima revisión</div>
          </div>
          <button class="btn" type="button" id="viv_btn_add_revision">+ Añadir revisión</button>
        </div>
        <div class="viv-rev__table" id="viv_rev_table"></div>
      </div>
    </div>
  </div>

  <input type="hidden" id="viv_id" value="">
</div>

<!-- Mini modal: buscar participante -->
<div class="viv-mini" id="vivMiniModal" aria-hidden="true">
  <div class="viv-mini__overlay" data-close="1"></div>
  <div class="viv-mini__card" role="dialog" aria-modal="true">
    <div class="viv-mini__head">
      <div class="viv-mini__title">Añadir participante</div>
      <button class="io-icon-btn" type="button" data-close="1" aria-label="Cerrar">×</button>
    </div>
    <div class="viv-mini__body">
      <div class="field">
        <label>Buscar (nombre, apellidos o DNI)</label>
        <input class="control" type="text" id="viv_buscar" placeholder="Ej: Jon Ander / 12345678A">
      </div>
      <div class="viv-mini__results" id="viv_buscar_res"></div>
    </div>
  </div>
</div>

<!-- Mini modal: nueva dinámica -->
<div class="viv-mini" id="vivMiniDinamica" aria-hidden="true">
  <div class="viv-mini__overlay" data-close="1"></div>
  <div class="viv-mini__card" role="dialog" aria-modal="true">
    <div class="viv-mini__head">
      <div class="viv-mini__title">Añadir dinámica</div>
      <button class="io-icon-btn" type="button" data-close="1" aria-label="Cerrar">×</button>
    </div>
    <div class="viv-mini__body">
      <div class="field">
        <label>Valoración</label>
        <div class="viv-stars" id="viv_dyn_stars" aria-label="Valoración 1 a 5">
          <button type="button" class="viv-star" data-val="1" aria-label="1 estrella">★</button>
          <button type="button" class="viv-star" data-val="2" aria-label="2 estrellas">★</button>
          <button type="button" class="viv-star" data-val="3" aria-label="3 estrellas">★</button>
          <button type="button" class="viv-star" data-val="4" aria-label="4 estrellas">★</button>
          <button type="button" class="viv-star" data-val="5" aria-label="5 estrellas">★</button>
        </div>
        <input type="hidden" id="viv_dyn_valoracion" value="0">
      </div>

      <div class="field">
        <label>Comentario (máx 500)</label>
        <textarea class="control" id="viv_dyn_comentario" rows="5" maxlength="500" placeholder="Escribe aquí..."></textarea>
      </div>

      <div class="viv-mini__actions">
        <button class="btn" type="button" data-close="1">Cancelar</button>
        <button class="btn btn-primary" type="button" id="viv_dyn_guardar">Guardar</button>
      </div>

      <div class="io-muted" id="viv_dyn_status" style="margin-top:8px;"></div>
    </div>
  </div>
</div>

<!-- Mini modal: nuevo menú semanal -->
<div class="viv-mini" id="vivMiniMenu" aria-hidden="true">
  <div class="viv-mini__overlay" data-close="1"></div>
  <div class="viv-mini__card" role="dialog" aria-modal="true">
    <div class="viv-mini__head">
      <div class="viv-mini__title">Añadir menú semanal</div>
      <button class="io-icon-btn" type="button" data-close="1" aria-label="Cerrar">×</button>
    </div>
    <div class="viv-mini__body">
      <div class="io-form-grid" style="margin-top:10px;">
        <div class="io-row"><label class="io-label">Lunes</label><input class="control" id="viv_menu_lunes" maxlength="500"></div>
        <div class="io-row"><label class="io-label">Martes</label><input class="control" id="viv_menu_martes" maxlength="500"></div>
        <div class="io-row"><label class="io-label">Miércoles</label><input class="control" id="viv_menu_miercoles" maxlength="500"></div>
        <div class="io-row"><label class="io-label">Jueves</label><input class="control" id="viv_menu_jueves" maxlength="500"></div>
        <div class="io-row"><label class="io-label">Viernes</label><input class="control" id="viv_menu_viernes" maxlength="500"></div>
        <div class="io-row"><label class="io-label">Sábado</label><input class="control" id="viv_menu_sabado" maxlength="500"></div>
        <div class="io-row"><label class="io-label">Domingo</label><input class="control" id="viv_menu_domingo" maxlength="500"></div>
      </div>

      <div class="viv-mini__actions">
        <button class="btn" type="button" data-close="1">Cancelar</button>
        <button class="btn btn-primary" type="button" id="viv_menu_guardar">Guardar</button>
      </div>

      <div class="io-muted" id="viv_menu_mini_status" style="margin-top:8px;"></div>
    </div>
  </div>
</div>

<!-- Mini modal: nueva incidencia -->
<div class="viv-mini" id="vivMiniIncidencia" aria-hidden="true">
  <div class="viv-mini__overlay" data-close="1"></div>
  <div class="viv-mini__card" role="dialog" aria-modal="true">
    <div class="viv-mini__head">
      <div class="viv-mini__title">Añadir incidencia</div>
      <button class="io-icon-btn" type="button" data-close="1" aria-label="Cerrar">×</button>
    </div>
    <div class="viv-mini__body">
      <div class="field" style="margin-top:10px;">
        <label>Incidencia (máx 500)</label>
        <textarea class="control" id="viv_inc_text" rows="5" maxlength="500" placeholder="Escribe aquí..."></textarea>
      </div>

      <div class="viv-mini__actions">
        <button class="btn" type="button" data-close="1">Cancelar</button>
        <button class="btn btn-primary" type="button" id="viv_inc_guardar">Guardar</button>
      </div>

      <div class="io-muted" id="viv_inc_mini_status" style="margin-top:8px;"></div>
    </div>
  </div>
</div>

<!-- Mini modal: nueva revisión -->
<div class="viv-mini" id="vivMiniRevision" aria-hidden="true">
  <div class="viv-mini__overlay" data-close="1"></div>
  <div class="viv-mini__card" role="dialog" aria-modal="true">
    <div class="viv-mini__head">
      <div class="viv-mini__title">Añadir revisión</div>
      <button class="io-icon-btn" type="button" data-close="1" aria-label="Cerrar">×</button>
    </div>

    <div class="viv-mini__body">
      <div class="io-form-grid">
        <div class="io-row">
          <label class="io-label">Equipamiento</label>
          <select class="control" id="viv_rev_equipo">
            <option value="">—</option>
            <option value="caldera">Caldera</option>
            <option value="gas">Gas</option>
            <option value="extintores">Extintores</option>
            <option value="contratos">Contratos</option>
            <option value="cei">CEI</option>
            <option value="salto">Salto</option>
          </select>
        </div>

        <div class="io-row">
          <label class="io-label">Fecha revisión</label>
          <input class="control" type="date" id="viv_rev_fecha">
        </div>

        <div class="io-row">
          <label class="io-label">Próxima revisión</label>
          <input class="control" type="date" id="viv_rev_proxima">
        </div>

        <div class="io-row">
          <label class="io-label">Avisar</label>
          <select class="control" id="viv_rev_avisar">
            <option value="0" selected>No</option>
            <option value="1">Sí</option>
          </select>
        </div>

        <div class="io-row">
          <label class="io-label">Días antes aviso</label>
          <input class="control" type="number" min="1" max="365" id="viv_rev_dias" value="15">
        </div>

        <div class="io-row io-row--full">
          <label class="io-label">Observaciones</label>
          <textarea class="control" id="viv_rev_obs" rows="4" maxlength="1000" placeholder="Notas..."></textarea>
        </div>
      </div>

      <div class="viv-mini__actions">
        <button class="btn" type="button" data-close="1">Cancelar</button>
        <button class="btn btn-primary" type="button" id="viv_rev_guardar">Guardar</button>
      </div>

      <div class="io-muted" id="viv_rev_status" style="margin-top:8px;"></div>
    </div>
  </div>
</div>

<!-- Mini modal: Nueva vivienda -->
<div class="viv-mini" id="vivMiniNuevaVivienda" aria-hidden="true">
  <div class="viv-mini__overlay" data-close="1"></div>
  <div class="viv-mini__card" role="dialog" aria-modal="true">
    <div class="viv-mini__head">
      <div class="viv-mini__title">Nueva vivienda</div>
      <button class="io-icon-btn" type="button" data-close="1" aria-label="Cerrar">×</button>
    </div>

    <div class="viv-mini__body">
      <div class="io-form-grid">
        <div class="io-row io-row--full">
          <div class="io-label">Foto vivienda</div>
          <div class="io-value">
            <input class="control" id="viv_new_foto" type="file" accept="image/jpeg,image/png,image/webp">
            <div class="io-muted" style="margin-top:6px;">(Opcional)</div>
          </div>
        </div>

        <div class="io-row io-row--full">
          <div class="io-label">Nombre *</div>
          <div class="io-value"><input class="control" id="viv_new_nombre" type="text" placeholder="Ej: Tolosa 3"></div>
        </div>

        <div class="io-row io-row--full">
          <div class="io-label">Dirección *</div>
          <div class="io-value"><input class="control" id="viv_new_direccion" type="text" placeholder="Calle..."></div>
        </div>

        <div class="io-row">
          <div class="io-label">Localidad *</div>
          <div class="io-value"><input class="control" id="viv_new_localidad" type="text" placeholder="Tolosa"></div>
        </div>

        <div class="io-row">
          <div class="io-label">Responsable *</div>
          <div class="io-value">
            <div style="display:flex; align-items:center; gap:10px;">
              <select class="control" id="viv_new_responsable" style="flex:1;">
                <option value="">— Selecciona —</option>
              </select>

              <div class="io-avatar io-avatar--xs is-no-photo" id="vivNewRespAvatar" aria-hidden="true" style="flex:0 0 auto;">
                <img class="io-avatar__img" alt="" style="display:none">
                <div class="io-avatar__txt">R</div>
              </div>
            </div>
          </div>
        </div>

        <div class="io-row">
          <div class="io-label">Integrador/a *</div>
          <div class="io-value">
            <div style="display:flex; align-items:center; gap:10px;">
              <select class="control" id="viv_new_integrador" style="flex:1;">
                <option value="">— Selecciona —</option>
              </select>

              <div class="io-avatar io-avatar--xs is-no-photo" id="vivNewIntAvatar" aria-hidden="true" style="flex:0 0 auto;">
                <img class="io-avatar__img" alt="" style="display:none">
                <div class="io-avatar__txt">I</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="viv-mini__actions" style="display:flex; justify-content:flex-end; gap:10px; margin-top:12px;">
        <button class="btn" type="button" data-close="1">Cancelar</button>
        <button class="btn btn-primary" type="button" id="viv_new_guardar">Crear</button>
      </div>

      <div class="io-muted" id="viv_new_status" style="margin-top:8px;"></div>
    </div>
  </div>
</div>
