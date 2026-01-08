<?php
// public/partials/user_modal.php
// Variables opcionales:
//   $USER_MODAL_PROGRAM = [
//     'key'   => 'io',                 // usado en data-tab-scope, ids...
//     'label' => 'Programa IO',         // texto tab
//     'partial' => __DIR__.'/programas/io_modal_extension.php' // include del programa
//   ];
$USER_MODAL_PROGRAM = $USER_MODAL_PROGRAM ?? null;
$programKey   = is_array($USER_MODAL_PROGRAM) ? ($USER_MODAL_PROGRAM['key'] ?? '') : '';
$programLabel = is_array($USER_MODAL_PROGRAM) ? ($USER_MODAL_PROGRAM['label'] ?? '') : '';
$programPartial = is_array($USER_MODAL_PROGRAM) ? ($USER_MODAL_PROGRAM['partial'] ?? '') : '';
?>

<div class="io-modal" id="ioModal" aria-hidden="true">
    <div class="io-modal__overlay" data-close="1"></div>

    <div class="io-modal__dialog" role="dialog" aria-modal="true" aria-label="Ficha de usuario">
        <!-- Header -->
        <div class="io-modal__top">
            <div class="io-modal__top-left">
                <div class="io-avatar" id="ioModalAvatar">U</div>
                <div class="io-modal__top-titles">
                    <div class="io-modal__name" id="ioModalNombre">—</div>
                    <div class="io-modal__sub" id="ioModalSub">—</div>
                </div>
            </div>

            <button class="io-icon-btn" type="button" data-close="1" aria-label="Cerrar">×</button>
        </div>

        <!-- Tabs principales: Usuario + (Programa enchufado) -->
        <div class="io-modal__tabs" role="tablist" data-tab-scope="main" aria-label="Secciones principales">
            <button class="io-tab is-active" type="button"
                data-tab="tab_main_usuario" role="tab" aria-controls="tab_main_usuario">
                Usuario
            </button>

            <?php if ($programKey && $programPartial && is_file($programPartial)): ?>
                <button class="io-tab" type="button"
                    data-tab="tab_main_program" role="tab" aria-controls="tab_main_program">
                    <?= htmlspecialchars($programLabel ?: 'Programa') ?>
                </button>
            <?php endif; ?>
        </div>

        <div class="io-modal__content">
            <!-- ======================
           MAIN: USUARIO
      ======================= -->
            <div class="io-pane is-active" id="tab_main_usuario" role="tabpanel" data-tab-scope="main">

                <div class="io-subtabs" role="tablist" data-tab-scope="user" aria-label="Pestañas de usuario">
                    <button class="io-tab is-active" type="button" data-tab="tab_user_basico" role="tab" aria-controls="tab_user_basico">Básico</button>
                    <button class="io-tab" type="button" data-tab="tab_user_contacto" role="tab" aria-controls="tab_user_contacto">Contacto</button>
                    <button class="io-tab" type="button" data-tab="tab_user_admin" role="tab" aria-controls="tab_user_admin">Administrativo</button>
                    <button class="io-tab" type="button" data-tab="tab_user_diag" role="tab" aria-controls="tab_user_diag">Diagnósticos</button>
                </div>

                <div class="io-modal__scroll">

                    <!-- ======================
               USUARIO: BÁSICO
          ======================= -->
                    <div class="io-pane is-active" id="tab_user_basico" role="tabpanel" data-tab-scope="user">
                        <div class="io-profile">
                            <div class="io-profile__layout">

                                <!-- ASIDE -->
                                <aside class="io-profile__aside">
                                    <div class="io-card io-profile__card">
                                        <div class="io-profile__avatarWrap">
                                            <button type="button" id="u_photoCard" class="io-profile__avatarBtn" aria-label="Cambiar foto">
                                                <img id="u_photoImg" alt="Foto de perfil" style="display:none;" />
                                                <span id="u_photoFallback" aria-hidden="true" style="display:grid;">
                                                    <span id="u_photoInitials">—</span>
                                                </span>
                                            </button>

                                            <input id="u_photoFile" type="file" accept="image/*" style="display:none;" />

                                            <div class="io-profile__identity">
                                                <div class="io-profile__name" id="u_displayName">—</div>
                                                <div class="io-profile__meta">
                                                    <span id="u_emailPreview" class="io-muted">—</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="io-profile__quick">
                                            <div class="io-profile__pill">
                                                <span class="io-profile__pillLabel">ID</span>
                                                <span id="u_id" class="io-profile__pillValue">—</span>
                                            </div>
                                            <div class="io-profile__pill">
                                                <span class="io-profile__pillLabel">Estado</span>
                                                <span id="u_estado" class="io-profile__pillValue">—</span>
                                            </div>
                                        </div>

                                        <p class="io-profile__hint">Pulsa en la foto para cambiarla.</p>
                                    </div>
                                </aside>

                                <!-- MAIN -->
                                <section class="io-profile__main">
                                    <div class="io-card io-section">
                                        <div class="io-section__head">
                                            <div class="io-section__title">Datos básicos</div>
                                            <div class="io-section__sub io-muted">Identificación y datos principales</div>
                                        </div>

                                        <div class="io-section__body">
                                            <div class="io-form-grid">
                                                <div class="io-row">
                                                    <label class="io-label" for="u_Nombre">Nombre</label>
                                                    <div class="io-value"><input class="io-input" id="u_Nombre" type="text" placeholder="Nombre"></div>
                                                </div>

                                                <div class="io-row">
                                                    <label class="io-label" for="u_Apellidos">Apellidos</label>
                                                    <div class="io-value"><input class="io-input" id="u_Apellidos" type="text" placeholder="Apellidos"></div>
                                                </div>

                                                <div class="io-row">
                                                    <label class="io-label" for="u_Dni">DNI</label>
                                                    <div class="io-value"><input class="io-input" id="u_Dni" type="text" placeholder="DNI"></div>
                                                </div>

                                                <div class="io-row">
                                                    <label class="io-label" for="u_Telefono_Usuario">Teléfono</label>
                                                    <div class="io-value"><input class="io-input" id="u_Telefono_Usuario" type="text" placeholder="+34 600 000 000"></div>
                                                </div>

                                                <div class="io-row">
                                                    <label class="io-label" for="u_Sexo">Sexo</label>
                                                    <div class="io-value">
                                                        <select class="io-select" id="u_Sexo">
                                                            <option value=""></option>
                                                            <option value="Hombre">Hombre</option>
                                                            <option value="Mujer">Mujer</option>
                                                            <option value="No binario">No binario</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="io-row">
                                                    <label class="io-label" for="u_Fecha_Nacimiento">Fecha nacimiento</label>
                                                    <div class="io-value"><input class="io-input" id="u_Fecha_Nacimiento" type="date"></div>
                                                </div>

                                                <div class="io-row">
                                                    <label class="io-label" for="u_Fecha_Alta">Fecha alta</label>
                                                    <div class="io-value"><input class="io-input" id="u_Fecha_Alta" type="datetime-local"></div>
                                                </div>

                                                <div class="io-row">
                                                    <label class="io-label" for="u_N_TIS">Nº TIS</label>
                                                    <div class="io-value"><input class="io-input" id="u_N_TIS" type="text" placeholder="Número TIS"></div>
                                                </div>

                                                <div class="io-row">
                                                    <label class="io-label" for="u_Nacionalidad">Nacionalidad</label>
                                                    <div class="io-value"><input class="io-input" id="u_Nacionalidad" type="text" placeholder="Nacionalidad"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="io-card io-section">
                                        <div class="io-section__head">
                                            <div class="io-section__title">Notas</div>
                                            <div class="io-section__sub io-muted">Observaciones internas</div>
                                        </div>
                                        <div class="io-section__body">
                                            <textarea class="io-textarea" rows="4" placeholder="Escribe aquí…"></textarea>
                                        </div>
                                    </div>
                                </section>

                            </div>
                        </div>
                    </div>

                    <!-- ======================
               USUARIO: CONTACTO
          ======================= -->
                    <div class="io-pane" id="tab_user_contacto" role="tabpanel" data-tab-scope="user">
                        <div class="io-card io-section">
                            <div class="io-section__head">
                                <div class="io-section__title">Contacto</div>
                                <div class="io-section__sub io-muted">Canales, dirección y teléfonos de referencia</div>
                            </div>

                            <div class="io-section__body">
                                <div class="io-form-grid">

                                    <div class="io-row">
                                        <label class="io-label" for="u_Correo">Email</label>
                                        <div class="io-value"><input class="io-input" id="u_Correo" type="email" placeholder="email@dominio.com"></div>
                                    </div>

                                    <div class="io-row io-row--split">
                                        <div class="io-row">
                                            <label class="io-label" for="u_Direccion">Dirección</label>
                                            <input class="io-input" id="u_Direccion" type="text" placeholder="Calle, número, piso…">
                                        </div>

                                        <div class="io-row io-row--cp">
                                            <label class="io-label" for="u_Codigo_Postal">CP</label>
                                            <input class="io-input" id="u_Codigo_Postal" type="text" placeholder="48930">
                                        </div>
                                    </div>


                                    <div class="io-subtitle">Teléfonos</div>

                                    <div class="io-subtitle">De la persona</div>
                                    <div class="io-row">
                                        <label class="io-label" for="u_Telefono_Usuario">Teléfono</label>
                                        <div class="io-value"><input class="io-input" id="u_Telefono_Usuario" type="text"></div>
                                    </div>

                                    <div class="io-subtitle">Familia o red</div>
                                    <div class="io-row">
                                        <label class="io-label" for="u_Telefono_Familia1">Teléfono 1</label>
                                        <div class="io-value"><input class="io-input" id="u_Telefono_Familia1" type="text"></div>
                                    </div>
                                    <div class="io-row">
                                        <label class="io-label" for="u_Telefono_Familia2">Teléfono 2</label>
                                        <div class="io-value"><input class="io-input" id="u_Telefono_Familia2" type="text"></div>
                                    </div>

                                    <div class="io-subtitle">Servicios sociales</div>
                                    <div class="io-row">
                                        <label class="io-label" for="u_Telefono_Servicios_Sociales">SS de referencia</label>
                                        <div class="io-value"><input class="io-input" id="u_Telefono_Servicios_Sociales" type="text"></div>
                                    </div>
                                    <div class="io-row">
                                        <label class="io-label" for="u_Telefono_Trabajadora_Social">Trabajadora social</label>
                                        <div class="io-value"><input class="io-input" id="u_Telefono_Trabajadora_Social" type="text"></div>
                                    </div>

                                    <div class="io-subtitle">Centro de salud y médico/a de cabecera</div>
                                    <div class="io-row">
                                        <label class="io-label" for="u_Telefono_Centro_Salud">Centro de salud</label>
                                        <div class="io-value"><input class="io-input" id="u_Telefono_Centro_Salud" type="text"></div>
                                    </div>
                                    <div class="io-row">
                                        <label class="io-label" for="u_Telefono_Medico_Cavecera">Médico/a de cabecera</label>
                                        <div class="io-value"><input class="io-input" id="u_Telefono_Medico_Cavecera" type="text"></div>
                                    </div>

                                    <div class="io-subtitle">Salud mental + referente</div>
                                    <div class="io-row">
                                        <label class="io-label" for="u_Telefono_Salud_Mental">Salud mental</label>
                                        <div class="io-value"><input class="io-input" id="u_Telefono_Salud_Mental" type="text"></div>
                                    </div>
                                    <div class="io-row">
                                        <label class="io-label" for="u_Telefono_Referente_Salud">Referente</label>
                                        <div class="io-value"><input class="io-input" id="u_Telefono_Referente_Salud" type="text"></div>
                                    </div>

                                    <div class="io-subtitle">Referente del ámbito formativo / laboral</div>
                                    <div class="io-row">
                                        <label class="io-label" for="u_Telefono_Referente_Formativo">Referente</label>
                                        <div class="io-value"><input class="io-input" id="u_Telefono_Referente_Formativo" type="text"></div>
                                    </div>

                                    <div class="io-subtitle">Otros referentes</div>
                                    <div class="io-row">
                                        <label class="io-label" for="u_Telefono_Otros1">Teléfono + quién</label>
                                        <div class="io-value"><input class="io-input" id="u_Telefono_Otros1" type="text" placeholder="Teléfono"></div>
                                    </div>
                                    <div class="io-row">
                                        <label class="io-label" for="u_Telefono_Otros2">Teléfono + quién</label>
                                        <div class="io-value"><input class="io-input" id="u_Telefono_Otros2" type="text" placeholder="Teléfono"></div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ======================
               USUARIO: ADMINISTRATIVO
          ======================= -->
                    <div class="io-pane" id="tab_user_admin" role="tabpanel" data-tab-scope="user">
                        <div class="io-card io-section">
                            <div class="io-section__head">
                                <div class="io-section__title">Administrativo</div>
                                <div class="io-section__sub io-muted">Datos internos y referencias</div>
                            </div>

                            <div class="io-section__body">
                                <div class="io-form-grid">
                                    <div class="io-row">
                                        <label class="io-label" for="u_CCC">CCC</label>
                                        <div class="io-value"><input class="io-input" id="u_CCC" type="text"></div>
                                    </div>

                                    <div class="io-row">
                                        <label class="io-label" for="u_Nivel_Estudios">Nivel estudios (id)</label>
                                        <div class="io-value"><input class="io-input" id="u_Nivel_Estudios" type="number" min="0"></div>
                                    </div>

                                    <div class="io-row">
                                        <label class="io-label" for="u_Tipo_Socio">Tipo socio (id)</label>
                                        <div class="io-value"><input class="io-input" id="u_Tipo_Socio" type="number" min="0"></div>
                                    </div>

                                    <div class="io-row">
                                        <label class="io-label" for="u_ID_Situacion_Administrativa">Situación administrativa (id)</label>
                                        <div class="io-value"><input class="io-input" id="u_ID_Situacion_Administrativa" type="number" min="0"></div>
                                    </div>

                                    <div class="io-row">
                                        <label class="io-label" for="u_ID_Via_Comunicacion">Vía comunicación (id)</label>
                                        <div class="io-value"><input class="io-input" id="u_ID_Via_Comunicacion" type="number" min="0"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ======================
     USUARIO: DIAGNÓSTICOS
======================= -->
                    <?php
                    // Diagnósticos (pane completo reutilizable)
                    $diagPartial = __DIR__ . '/user_diagnosticos.php';
                    if (is_file($diagPartial)) include $diagPartial;
                    ?>


                </div>
            </div>

            <!-- ======================
           MAIN: PROGRAMA (slot)
      ======================= -->
            <?php if ($programKey && $programPartial && is_file($programPartial)): ?>
                <div class="io-pane" id="tab_main_program" role="tabpanel" data-tab-scope="main">
                    <?php include $programPartial; ?>
                </div>
            <?php endif; ?>

        </div>

        <!-- Footer -->
        <div class="io-modal__footer">
            <div class="io-status" id="ioModalStatus"></div>
            <div class="io-modal__footer-actions">
                <button class="btn" type="button" data-close="1">Cerrar</button>
                <button class="btn btn-primary" type="button" id="btnGuardarModal">Guardar cambios</button>
            </div>
        </div>

        <input type="hidden" id="ioModalUserId" value="">

    </div>
</div>