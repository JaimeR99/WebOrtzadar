<div class="io-pane" id="tab_user_diag" role="tabpanel" data-tab-scope="user">
            <div class="io-card io-section">
              <div class="io-section__head">
                <div class="io-section__title">Diagnósticos</div>
                <div class="io-section__sub io-muted">Activa solo lo que aplique y completa los datos</div>
              </div>

              <div class="io-section__body">
                <div class="diag-grid">
                  <!-- DISCAPACIDAD -->
                  <section class="diag-card" data-diag="discapacidad">
                    <header class="diag-card__head">
                      <div>
                        <div class="diag-title">Discapacidad</div>
                        <div class="diag-sub">Activar y completar datos si aplica</div>
                      </div>

                      <label class="switch">
                        <input type="checkbox" id="chk_discapacidad">
                        <span class="switch__ui"></span>
                        <span class="switch__text">Activar</span>
                      </label>
                    </header>

                    <div class="diag-card__body" id="diag_discapacidad_box">
                      <div class="diag-form">
                        <div class="field">
                          <label for="disc_porcentaje">Porcentaje</label>
                          <input class="io-input" id="disc_porcentaje" type="number" min="0" max="100">
                        </div>

                        <div class="field">
                          <label for="disc_diagnostico">Diagnóstico</label>
                          <select class="io-input" id="disc_diagnostico">
                            <option value="">—</option>
                            <option value="Fisico">Físico</option>
                            <option value="Mental">Mental</option>
                            <option value="Intelectual">Intelectual</option>
                          </select>
                        </div>

                        <div class="field">
                          <label for="disc_fecha_reconocimiento">Fecha reconocimiento</label>
                          <input class="io-input" id="disc_fecha_reconocimiento" type="date">
                        </div>

                        <div class="field">
                          <label for="disc_fecha_caducidad">Fecha caducidad</label>
                          <input class="io-input" id="disc_fecha_caducidad" type="date">
                        </div>

                        <div class="field field--full">
                          <label for="disc_descripcion">Descripción</label>
                          <textarea class="io-textarea" id="disc_descripcion" rows="4"></textarea>
                        </div>
                      </div>
                    </div>
                  </section>

                  <!-- DEPENDENCIA -->
                  <section class="diag-card" data-diag="dependencia">
                    <header class="diag-card__head">
                      <div>
                        <div class="diag-title">Dependencia</div>
                        <div class="diag-sub">Grado y vigencia</div>
                      </div>

                      <label class="switch">
                        <input type="checkbox" id="chk_dependencia">
                        <span class="switch__ui"></span>
                        <span class="switch__text">Activar</span>
                      </label>
                    </header>

                    <div class="diag-card__body" id="diag_dependencia_box">
                      <div class="diag-form">
                        <div class="field">
                          <label for="dep_grado">Grado</label>
                          <select class="io-input" id="dep_grado">
                            <option value="">—</option>
                            <option value="GRADO I">GRADO I</option>
                            <option value="GRADO II">GRADO II</option>
                            <option value="GRADO III">GRADO III</option>
                          </select>
                        </div>

                        <div class="field">
                          <label for="dep_fecha_reconocimiento">Fecha reconocimiento</label>
                          <input class="io-input" id="dep_fecha_reconocimiento" type="date">
                        </div>

                        <div class="field">
                          <label for="dep_fecha_caducidad">Fecha caducidad</label>
                          <input class="io-input" id="dep_fecha_caducidad" type="date">
                        </div>

                        <div class="field field--full">
                          <label for="dep_descripcion">Descripción</label>
                          <textarea class="io-textarea" id="dep_descripcion" rows="4"></textarea>
                        </div>
                      </div>
                    </div>
                  </section>

                  <!-- EXCLUSIÓN -->
                  <section class="diag-card" data-diag="exclusion">
                    <header class="diag-card__head">
                      <div>
                        <div class="diag-title">Exclusión</div>
                        <div class="diag-sub">Tipo y vigencia</div>
                      </div>

                      <label class="switch">
                        <input type="checkbox" id="chk_exclusion">
                        <span class="switch__ui"></span>
                        <span class="switch__text">Activar</span>
                      </label>
                    </header>

                    <div class="diag-card__body" id="diag_exclusion_box">
                      <div class="diag-form">
                        <div class="field">
                          <label for="exc_tipo">Tipo</label>
                          <select class="io-input" id="exc_tipo">
                            <option value="">—</option>
                            <option value="Leve">Leve</option>
                            <option value="Moderada">Moderada</option>
                            <option value="Grave">Grave</option>
                          </select>
                        </div>

                        <div class="field">
                          <label for="exc_fecha_reconocimiento">Fecha reconocimiento</label>
                          <input class="io-input" id="exc_fecha_reconocimiento" type="date">
                        </div>

                        <div class="field">
                          <label for="exc_fecha_caducidad">Fecha caducidad</label>
                          <input class="io-input" id="exc_fecha_caducidad" type="date">
                        </div>

                        <div class="field field--full">
                          <label for="exc_descripcion">Descripción</label>
                          <textarea class="io-textarea" id="exc_descripcion" rows="4"></textarea>
                        </div>
                      </div>
                    </div>
                  </section>
                </div>

                <!-- Hidden diag IDs (tu JS los setea) -->
                <input type="hidden" id="u_ID_DIAG_Discapacidad" value="">
                <input type="hidden" id="u_ID_DIAG_Dependencia" value="">
                <input type="hidden" id="u_ID_DIAG_Exclusion" value="">
                <input type="hidden" id="u_ID_DIAG_Discapacidad_readonly" value="">
                <input type="hidden" id="u_ID_DIAG_Dependencia_readonly" value="">
                <input type="hidden" id="u_ID_DIAG_Exclusion_readonly" value="">
              </div>
            </div>
          </div>