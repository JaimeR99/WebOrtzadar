<?php
// public/html/modals/trabajadores_modal.php
?>

<!-- SUBTABS (dentro del modal) -->
<div class="trab-subtabsbar" data-trab-subtabsbar>
  <div class="io-subtabs" role="tablist" data-tab-scope="trabajadores" aria-label="Trabajadores">
    <button class="io-tab is-active"
      type="button"
      role="tab"
      aria-selected="true"
      tabindex="0"
      data-tab="tab_trabajador"
      aria-controls="tab_trabajador">Trabajador</button>

    <button class="io-tab"
      type="button"
      role="tab"
      aria-selected="false"
      tabindex="-1"
      data-tab="tab_formacion"
      aria-controls="tab_formacion">Formación</button>

    <button class="io-tab"
      type="button"
      role="tab"
      aria-selected="false"
      tabindex="-1"
      data-tab="tab_acceso"
      aria-controls="tab_acceso">Acceso</button>
  </div>
</div>

<div class="trab-tabpanels" data-tab-scope-panels="trabajadores">

  <!-- TAB: Trabajador -->
  <section id="tab_trabajador" class="trab-tabpanel is-active" role="tabpanel" tabindex="0">
    <div class="io-section">
      <div class="io-section__head">
        <div class="io-section__title">Datos del trabajador/a</div>
        <div class="io-section__sub io-muted">Información personal y contacto</div>
      </div>

      <div class="io-section__body">
        <div class="trabajadores-datos-grid">

          <!-- Columna 1 -->
          <div class="trabajadores-datos-col">
            <div class="io-row">
              <label class="io-label">Foto</label>
              <div class="io-value">
                <div class="trabajador-avatar lg is-no-photo" id="trabajadorAvatarPreview" aria-hidden="true" role="button" tabindex="0" title="Cambiar foto">
                  <img src="" alt="">
                  <span class="trabajador-initials" id="trabajadorAvatarInitials">TS</span>
                </div>

                <input id="t_foto" type="file" accept="image/jpeg,image/png,image/webp" style="display:none">
                <div class="io-muted" style="margin-top:6px;">Click en la foto para cambiarla.</div>
              </div>
            </div>

            <div class="io-row">
              <label class="io-label" for="t_fecha_alta">Fecha alta</label>
              <div class="io-value"><input class="io-input" id="t_fecha_alta" type="datetime-local" disabled></div>
            </div>

            <div class="io-row">
              <label class="io-label" for="t_id_puesto">Puesto</label>
              <div class="io-value">
                <select class="io-select" id="t_id_puesto">
                  <option value="">—</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Columna 2 -->
          <div class="trabajadores-datos-col">
            <div class="io-row">
              <label class="io-label" for="t_nombre">Nombre</label>
              <div class="io-value"><input class="io-input" id="t_nombre" type="text" maxlength="50"></div>
            </div>

            <div class="io-row">
              <label class="io-label" for="t_apellidos">Apellidos</label>
              <div class="io-value"><input class="io-input" id="t_apellidos" type="text" maxlength="50"></div>
            </div>

            <div class="io-row">
              <label class="io-label" for="t_sexo">Sexo</label>
              <div class="io-value">
                <select class="io-select" id="t_sexo">
                  <option value="">—</option>
                  <option value="hombre">Hombre</option>
                  <option value="mujer">Mujer</option>
                  <option value="no_binario">No binario</option>
                </select>
              </div>
            </div>

            <div class="io-row">
              <label class="io-label" for="t_dni">DNI</label>
              <div class="io-value"><input class="io-input" id="t_dni" type="text" maxlength="20"></div>
            </div>

            <div class="io-row">
              <label class="io-label" for="t_fecha_nacimiento">Fecha nacimiento</label>
              <div class="io-value"><input class="io-input" id="t_fecha_nacimiento" type="date"></div>
            </div>

            <div class="io-row">
              <label class="io-label" for="t_direccion">Dirección</label>
              <div class="io-value"><input class="io-input" id="t_direccion" type="text" maxlength="255"></div>
            </div>

            <div class="io-row">
              <label class="io-label" for="t_telefono">Teléfono</label>
              <div class="io-value"><input class="io-input" id="t_telefono" type="text" maxlength="50"></div>
            </div>

            <div class="io-row">
              <label class="io-label" for="t_email">Email</label>
              <div class="io-value"><input class="io-input" id="t_email" type="email" maxlength="50" autocomplete="off"></div>
            </div>

            <div class="io-row">
              <label class="io-label" for="t_cuenta_corriente">Cuenta corriente</label>
              <div class="io-value"><input class="io-input" id="t_cuenta_corriente" type="text" maxlength="34"></div>
            </div>

          </div>
        </div>

        <input type="hidden" id="t_id" value="">
      </div>
    </div>
  </section>

  <!-- TAB: Formación -->
  <section id="tab_formacion" class="trab-tabpanel" role="tabpanel" tabindex="0" hidden>
    <div class="io-section">
      <div class="io-section__head">
        <div class="io-section__title">Formación continua</div>
        <div class="io-section__sub io-muted">Registro de formaciones</div>
      </div>

      <div class="io-section__body">
        <div class="trab-form-grid">
          <div class="io-row">
            <label class="io-label" for="f_nombre">Nombre formación</label>
            <div class="io-value"><input class="io-input" id="f_nombre" type="text" maxlength="150"></div>
          </div>

          <div class="io-row">
            <label class="io-label" for="f_fecha">Fecha</label>
            <div class="io-value"><input class="io-input" id="f_fecha" type="date"></div>
          </div>

          <div class="io-row">
            <label class="io-label" for="f_institucion">Institución</label>
            <div class="io-value"><input class="io-input" id="f_institucion" type="text" maxlength="150"></div>
          </div>

          <div class="io-row">
            <label class="io-label" for="f_valoracion">Valoración</label>
            <div class="io-value">
              <select class="io-select" id="f_valoracion">
                <option value="">—</option>
                <option value="1">1</option><option value="2">2</option><option value="3">3</option>
                <option value="4">4</option><option value="5">5</option>
              </select>
            </div>
          </div>

          <div class="trab-form-actions">
            <button class="btn btn-primary" type="button" id="btnAddFormacion">+ Añadir formación</button>
          </div>
        </div>

        <div class="trab-form-list" id="formacionList"></div>
      </div>
    </div>
  </section>

  <!-- TAB: Acceso -->
  <section id="tab_acceso" class="trab-tabpanel" role="tabpanel" tabindex="0" hidden>
    <div class="io-section">
      <div class="io-section__head">
        <div class="io-section__title">Acceso</div>
        <div class="io-section__sub io-muted">Usuario, contraseña y página de entrada</div>
      </div>

      <div class="io-section__body">
        <div class="io-form-grid">
          <div class="io-row">
            <label class="io-label" for="a_enabled">Acceso</label>
            <div class="io-value">
              <label class="io-switch">
                <input type="checkbox" id="a_enabled">
                <span class="io-switch__track"></span>
                <span class="io-switch__thumb"></span>
              </label>
              <div class="io-muted" style="margin-top:6px;">
                Se usará el correo del trabajador (Email) como usuario.
              </div>
            </div>
          </div>

          <div class="trabajadores-acceso-panel" id="accesoPanel">
            <div class="io-row">
              <label class="io-label" for="a_landpage">Landpage</label>
              <div class="io-value">
                <select class="io-select" id="a_landpage">
                  <option value="index.php?pagina=dashboard">Dashboard</option>
                  <option value="index.php?pagina=usuarios">Usuarios</option>
                  <option value="index.php?pagina=programas">Programas</option>
                  <option value="index.php?pagina=programas_io">Información y orientación</option>
                  <option value="index.php?pagina=programas_ocio">Ocio</option>
                  <option value="index.php?pagina=trabajadores">Trabajadores sociales</option>
                </select>
              </div>
            </div>

            <div class="io-row">
              <label class="io-label" for="a_pass">Contraseña</label>
              <div class="io-value">
                <input class="io-input" id="a_pass" type="password" placeholder="(solo si quieres cambiarla)">
                <div class="io-muted" style="margin-top:6px;">Deja vacío para mantener la actual.</div>
              </div>
            </div>

            <div class="trabajadores-acceso-actions">
              <button class="btn btn-primary" type="button" id="btnGuardarAcceso">Guardar acceso</button>
              <button class="btn btn-ghost" type="button" id="btnEliminarAcceso">Eliminar acceso</button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </section>

</div>
