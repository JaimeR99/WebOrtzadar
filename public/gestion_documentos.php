<?php
// public/gestion_documentos.php
?>

<div class="app-page app--documentos documentos-layout">

    <!-- COLUMNA IZQUIERDA: ÁRBOL DE DIRECTORIOS -->
    <aside class="documentos-columna documentos-columna-izquierda">
        <!-- Cabecera izquierda -->
        <header class="documentos-izq-cabecera">
            <div class="documentos-ruta">
                <button class="documentos-accion-boton" title="Atrás">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>
                <button class="documentos-accion-boton" title="Adelante">
                    <i class="fa-solid fa-arrow-right"></i>
                </button>

                <div class="documentos-ruta-trazado">
                    <span class="documentos-ruta-segmento"><i class="fa-solid fa-house"></i></span>
                    <span class="documentos-ruta-separador">/</span>
                    <span class="documentos-ruta-segmento">Documentos</span>
                    <span class="documentos-ruta-separador">/</span>
                    <span class="documentos-ruta-segmento documentos-ruta-segmento-activo">
                        Proyecto demo
                    </span>
                </div>
            </div>

            <div class="documentos-busqueda">
                <input
                    type="text"
                    class="control documentos-busqueda-input"
                    placeholder="Buscar archivos (Ctrl+F)">
            </div>
        </header>

        <div class="menu-separador"></div>

        <!-- Centro: árbol de carpetas (con overflow) -->
        <div class="documentos-izq-arbol">
            <ul class="documentos-arbol-lista">
                <li class="documentos-arbol-item documentos-arbol-raiz">
                    <span class="documentos-arbol-icono documentos-icono-raiz"></span>
                    <span class="documentos-arbol-nombre">Raíz</span>
                </li>

                <li class="documentos-arbol-item documentos-arbol-carpeta documentos-arbol-abierta"
                    data-nodo="elementos">
                    <span class="documentos-arbol-icono documentos-icono-carpeta"></span>
                    <span class="documentos-arbol-nombre">ELEMENTOS INSTALADOS</span>
                </li>
                <ul class="documentos-arbol-sublista" data-nodo-contenido="elementos">
                    <li class="documentos-arbol-item documentos-arbol-carpeta" data-nodo="sensores">
                        <span class="documentos-arbol-icono documentos-icono-carpeta"></span>
                        <span class="documentos-arbol-nombre">SENSORES</span>
                    </li>
                    <ul class="documentos-arbol-sublista" data-nodo-contenido="sensores">
                        <li class="documentos-arbol-item documentos-arbol-archivo">
                            <span class="documentos-arbol-icono documentos-icono-img"></span>
                            <span class="documentos-arbol-nombre">
                                sensor_018CD-cent_config.json
                            </span>
                        </li>
                        <li class="documentos-arbol-item documentos-arbol-archivo">
                            <span class="documentos-arbol-icono documentos-icono-img"></span>
                            <span class="documentos-arbol-nombre">
                                sensor_022CC-via1_config.json
                            </span>
                        </li>
                    </ul>

                    <li class="documentos-arbol-item documentos-arbol-carpeta" data-nodo="equipos">
                        <span class="documentos-arbol-icono documentos-icono-carpeta"></span>
                        <span class="documentos-arbol-nombre">EQUIPOS AUXILIARES</span>
                    </li>
                    <ul class="documentos-arbol-sublista" data-nodo-contenido="equipos">
                        <li class="documentos-arbol-item documentos-arbol-archivo">
                            <span class="documentos-arbol-icono documentos-icono-pdf"></span>
                            <span class="documentos-arbol-nombre">manual_puertas_acceso.pdf</span>
                        </li>
                    </ul>
                </ul>

                <li class="documentos-arbol-item documentos-arbol-carpeta documentos-arbol-abierta"
                    data-nodo="fotos">
                    <span class="documentos-arbol-icono documentos-icono-carpeta"></span>
                    <span class="documentos-arbol-nombre">FOTOS</span>
                </li>
                <ul class="documentos-arbol-sublista" data-nodo-contenido="fotos">
                    <li class="documentos-arbol-item documentos-arbol-carpeta" data-nodo="obra">
                        <span class="documentos-arbol-icono documentos-icono-carpeta"></span>
                        <span class="documentos-arbol-nombre">OBRA</span>
                    </li>
                    <ul class="documentos-arbol-sublista" data-nodo-contenido="obra">
                        <li class="documentos-arbol-item documentos-arbol-archivo">
                            <span class="documentos-arbol-icono documentos-icono-img"></span>
                            <span class="documentos-arbol-nombre">Anclaje-24_situacion.jpg</span>
                        </li>
                        <li class="documentos-arbol-item documentos-arbol-archivo">
                            <span class="documentos-arbol-icono documentos-icono-img"></span>
                            <span class="documentos-arbol-nombre">Anclaje-45_situacion.jpg</span>
                        </li>
                    </ul>

                    <li class="documentos-arbol-item documentos-arbol-carpeta" data-nodo="inspecciones">
                        <span class="documentos-arbol-icono documentos-icono-carpeta"></span>
                        <span class="documentos-arbol-nombre">INSPECCIONES</span>
                    </li>
                    <ul class="documentos-arbol-sublista" data-nodo-contenido="inspecciones">
                        <li class="documentos-arbol-item documentos-arbol-archivo">
                            <span class="documentos-arbol-icono documentos-icono-img"></span>
                            <span class="documentos-arbol-nombre">Inspeccion_2025-06-01.jpg</span>
                        </li>
                    </ul>
                </ul>

                <li class="documentos-arbol-item documentos-arbol-carpeta documentos-arbol-abierta"
                    data-nodo="informes">
                    <span class="documentos-arbol-icono documentos-icono-carpeta"></span>
                    <span class="documentos-arbol-nombre">INFORMES</span>
                </li>
                <ul class="documentos-arbol-sublista" data-nodo-contenido="informes">
                    <li class="documentos-arbol-item documentos-arbol-archivo">
                        <span class="documentos-arbol-icono documentos-icono-pdf"></span>
                        <span class="documentos-arbol-nombre">
                            4-Proyectos.03-Resumen general.pdf
                        </span>
                    </li>
                    <li class="documentos-arbol-item documentos-arbol-archivo">
                        <span class="documentos-arbol-icono documentos-icono-excel"></span>
                        <span class="documentos-arbol-nombre">Listado_sensores.xlsx</span>
                    </li>
                </ul>
            </ul>
        </div>

        <div class="menu-separador"></div>

        <!-- Pie izquierda -->
        <footer class="documentos-izq-pie">
            <label for="documentos_obra" class="documentos-obra-etiqueta">Obra:</label>
            <select id="documentos_obra" class="control documentos-obra-select">
                <option value="">Selecciona una obra…</option>
                <option value="obra_demo_1">Taludes Túneles de Artxanda</option>
                <option value="obra_demo_2">Línea FGC L'Hospitalet</option>
            </select>
        </footer>
    </aside>

    <!-- COLUMNA CENTRAL: LISTA / GRID DE ARCHIVOS -->
    <section class="documentos-columna documentos-columna-central">
        <!-- Cabecera central -->
        <header class="documentos-centro-cabecera">
            <div class="documentos-centro-acciones">
                <button class="documentos-accion-boton" title="Atrás">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>
                <button class="documentos-accion-boton" title="Ir a la raíz">
                    <i class="fa-solid fa-house"></i>
                </button>

                <button class="documentos-accion-boton"
                    title="Seleccionar todo"
                    data-accion="seleccionar-todo">
                    <i class="fa-regular fa-square-check"></i>
                </button>
                <button class="documentos-accion-boton"
                    title="Deseleccionar todo"
                    data-accion="deseleccionar-todo">
                    <i class="fa-regular fa-square"></i>
                </button>
                <button class="documentos-accion-boton" title="Refrescar">
                    <i class="fa-solid fa-rotate"></i>
                </button>

                <button class="documentos-accion-boton" title="Descargar selección">
                    <i class="fa-solid fa-download"></i>
                </button>
                <button class="documentos-accion-boton" title="Subir archivo">
                    <i class="fa-solid fa-upload"></i>
                </button>
                <button class="documentos-accion-boton" title="Crear carpeta">
                    <i class="fa-solid fa-folder-plus"></i>
                </button>
                <button class="documentos-accion-boton" title="Editar nombre">
                    <i class="fa-regular fa-pen-to-square"></i>
                </button>

            </div>

            <div class="documentos-centro-vistas">
                <button type="button"
                    class="documentos-vista-boton documentos-vista-boton-activo"
                    data-vista="lista"
                    title="Vista en filas">
                    ☰
                </button>
                <button type="button"
                    class="documentos-vista-boton"
                    data-vista="iconos"
                    title="Vista en iconos">
                    ⬚
                </button>
            </div>
        </header>

        <div class="menu-separador"></div>

        <!-- Lista de archivos (overflow aquí) -->
        <div class="documentos-centro-lista" id="documentos_lista_contenedor">

            <!-- Vista LISTA (filas con atributos) -->
            <div class="documentos-vista documentos-vista-lista documentos-vista-activa" data-vista="lista">
                <div class="documentos-lista-encabezado">
                    <div class="documentos-columna-nombre">Nombre</div>
                    <div class="documentos-columna-modificado">Modificado</div>
                    <div class="documentos-columna-tamano">Tamaño</div>
                    <div class="documentos-columna-tipo">Tipo</div>
                </div>

                <div class="documentos-lista-cuerpo">
                    <article class="documentos-lista-item documentos-lista-carpeta" data-tamano="0">
                        <div class="documentos-columna-nombre">
                            <span class="documentos-icono documentos-icono-carpeta"></span>
                            <span>ELEMENTOS INSTALADOS</span>
                        </div>
                        <div class="documentos-columna-modificado">13/10/2025 00:59</div>
                        <div class="documentos-columna-tamano">—</div>
                        <div class="documentos-columna-tipo">Carpeta</div>
                    </article>

                    <article class="documentos-lista-item documentos-lista-carpeta" data-tamano="0">
                        <div class="documentos-columna-nombre">
                            <span class="documentos-icono documentos-icono-carpeta"></span>
                            <span>FOTOS</span>
                        </div>
                        <div class="documentos-columna-modificado">13/10/2025 01:01</div>
                        <div class="documentos-columna-tamano">—</div>
                        <div class="documentos-columna-tipo">Carpeta</div>
                    </article>

                    <article class="documentos-lista-item documentos-lista-carpeta" data-tamano="0">
                        <div class="documentos-columna-nombre">
                            <span class="documentos-icono documentos-icono-carpeta"></span>
                            <span>INFORMES</span>
                        </div>
                        <div class="documentos-columna-modificado">13/10/2025 01:01</div>
                        <div class="documentos-columna-tamano">—</div>
                        <div class="documentos-columna-tipo">Carpeta</div>
                    </article>

                    <article class="documentos-lista-item" data-tamano="0.53">
                        <div class="documentos-columna-nombre">
                            <span class="documentos-icono documentos-icono-pdf"></span>
                            <span>4-Proyectos.03-InstrumAuscult.00-Portada.pdf</span>
                        </div>
                        <div class="documentos-columna-modificado">13/10/2025 00:58</div>
                        <div class="documentos-columna-tamano">527 KB</div>
                        <div class="documentos-columna-tipo">PDF</div>
                    </article>

                    <article class="documentos-lista-item" data-tamano="6.3">
                        <div class="documentos-columna-nombre">
                            <span class="documentos-icono documentos-icono-img"></span>
                            <span>Anclaje-24_situacion.jpg</span>
                        </div>
                        <div class="documentos-columna-modificado">13/10/2025 00:59</div>
                        <div class="documentos-columna-tamano">6,3 MB</div>
                        <div class="documentos-columna-tipo">JPG</div>
                    </article>

                    <article class="documentos-lista-item" data-tamano="1.2">
                        <div class="documentos-columna-nombre">
                            <span class="documentos-icono documentos-icono-excel"></span>
                            <span>T0829-ALZADO-ELEMENTOS.xlsx</span>
                        </div>
                        <div class="documentos-columna-modificado">13/10/2025 01:05</div>
                        <div class="documentos-columna-tamano">1,2 MB</div>
                        <div class="documentos-columna-tipo">XLSX</div>
                    </article>
                </div>
            </div>

            <!-- Vista ICONOS (grid de tarjetas) -->
            <div class="documentos-vista documentos-vista-iconos" data-vista="iconos">
                <div class="documentos-iconos-grid">
                    <div class="documentos-icono-item documentos-lista-carpeta" data-tamano="0">
                        <div class="documentos-icono-thumbnail documentos-icono-thumbnail-carpeta"></div>
                        <p class="documentos-icono-nombre">ELEMENTOS INSTALADOS</p>
                    </div>

                    <div class="documentos-icono-item documentos-lista-carpeta" data-tamano="0">
                        <div class="documentos-icono-thumbnail documentos-icono-thumbnail-carpeta"></div>
                        <p class="documentos-icono-nombre">FOTOS</p>
                    </div>

                    <div class="documentos-icono-item documentos-lista-carpeta" data-tamano="0">
                        <div class="documentos-icono-thumbnail documentos-icono-thumbnail-carpeta"></div>
                        <p class="documentos-icono-nombre">INFORMES</p>
                    </div>

                    <div class="documentos-icono-item" data-tamano="0.53">
                        <div class="documentos-icono-thumbnail documentos-icono-thumbnail-pdf"></div>
                        <p class="documentos-icono-nombre">4-Proyectos.03-...</p>
                    </div>

                    <div class="documentos-icono-item" data-tamano="6.3">
                        <div class="documentos-icono-thumbnail documentos-icono-thumbnail-img"></div>
                        <p class="documentos-icono-nombre">Anclaje-24_sit...</p>
                    </div>

                    <div class="documentos-icono-item" data-tamano="1.2">
                        <div class="documentos-icono-thumbnail documentos-icono-thumbnail-excel"></div>
                        <p class="documentos-icono-nombre">T0829-ALZADO...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pie central -->
        <footer class="documentos-centro-pie">
            <div class="documentos-centro-info-izquierda">
                <span id="documentos_total_elementos">21</span> elementos
            </div>
            <div class="documentos-centro-info-derecha">
                <span id="documentos_total_seleccionados">0</span> seleccionados ·
                <span id="documentos_tamano_seleccionado">0,00 MB</span>
            </div>
        </footer>
    </section>

    <!-- COLUMNA DERECHA: VISOR DE ARCHIVOS -->
    <aside class="documentos-columna documentos-columna-derecha">
        <!-- Cabecera derecha -->
        <header class="documentos-der-cabecera">
            <div class="documentos-der-titulo">
                <i class="fa-regular fa-eye"></i>
                <span id="vw-filename">Visualizador de archivos</span>
            </div>
            <div class="documentos-der-acciones">
                <button class="documentos-der-boton" id="vw-open" title="Abrir en nueva pestaña">
                    <i class="fa-solid fa-up-right-from-square"></i>
                </button>
                <button class="documentos-der-boton" id="vw-download" title="Descargar">
                    <i class="fa-solid fa-download"></i>
                </button>
            </div>
        </header>

        <div class="menu-separador"></div>

        <!-- Zona de visualización -->
        <div class="documentos-der-visor">
            <div class="documentos-der-visor-contenido">
                <div class="documentos-der-preview-placeholder">
                    <div class="documentos-der-preview-marca">
                        Vista previa de archivo
                    </div>
                    <div class="documentos-der-preview-imagen">
                        <span>Contenido gráfico del archivo seleccionado</span>
                    </div>
                </div>
            </div>

            <!-- Barra de información sobrepuesta abajo -->
            <div class="documentos-der-info">
                <div class="documentos-der-info-izquierda">
                    JPG · 6,3 MB
                </div>
                <div class="documentos-der-info-derecha">
                    Modificado: 13/10/2025 00:59
                </div>
            </div>
        </div>
    </aside>
</div>
