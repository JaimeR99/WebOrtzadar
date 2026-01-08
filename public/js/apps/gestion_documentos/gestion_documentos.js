// public/js/apps/gestion_documentos/gestion_documentos.js

document.addEventListener('DOMContentLoaded', () => {
    /* ==========================
     *  CAMBIO DE VISTA LISTA / ICONOS
     * ========================== */
    const botonesVista = document.querySelectorAll('.documentos-vista-boton');
    const vistas = document.querySelectorAll('.documentos-vista');

    botonesVista.forEach(boton => {
        boton.addEventListener('click', () => {
            const vistaObjetivo = boton.dataset.vista;

            // Activar / desactivar botones
            botonesVista.forEach(b => b.classList.remove('documentos-vista-boton-activo'));
            boton.classList.add('documentos-vista-boton-activo');

            // Mostrar solo la vista seleccionada
            vistas.forEach(v => {
                if (v.dataset.vista === vistaObjetivo) {
                    v.classList.add('documentos-vista-activa');
                } else {
                    v.classList.remove('documentos-vista-activa');
                }
            });
        });
    });

    /* ==========================
     *  ABRIR / CERRAR CARPETAS ÁRBOL
     * ========================== */
    const carpetas = document.querySelectorAll('.documentos-arbol-carpeta');

    carpetas.forEach(carpeta => {
        carpeta.addEventListener('click', (e) => {
            e.stopPropagation();
            const nodo = carpeta.dataset.nodo;
            if (!nodo) return;

            const sublista = document.querySelector(
                `.documentos-arbol-sublista[data-nodo-contenido="${nodo}"]`
            );
            if (!sublista) return;

            const cerrada = sublista.classList.toggle('oculta');
            carpeta.classList.toggle('carpeta-cerrada', cerrada);
        });
    });

    /* ==========================
     *  SELECCIÓN / DESELECCIÓN DE ARCHIVOS
     * ========================== */
    const filas = document.querySelectorAll('.documentos-lista-item');
    const totalElementosSpan = document.getElementById('documentos_total_elementos');
    const totalSelSpan = document.getElementById('documentos_total_seleccionados');
    const tamSelSpan = document.getElementById('documentos_tamano_seleccionado');

    function recalcularSeleccion() {
        let seleccionados = 0;
        let tamanoTotal = 0;

        filas.forEach(fila => {
            if (fila.classList.contains('documentos-lista-item-seleccionado')) {
                seleccionados++;
                const t = parseFloat(fila.dataset.tamano || '0');
                if (!isNaN(t)) tamanoTotal += t;
            }
        });

        if (totalSelSpan) {
            totalSelSpan.textContent = seleccionados.toString();
        }

        if (tamSelSpan) {
            const texto = tamanoTotal.toFixed(2).replace('.', ',') + ' MB';
            tamSelSpan.textContent = texto;
        }
    }

    filas.forEach(fila => {
        fila.addEventListener('click', () => {
            fila.classList.toggle('documentos-lista-item-seleccionado');
            recalcularSeleccion();
        });
    });

    const botonesAccion = document.querySelectorAll('.documentos-accion-boton[data-accion]');

    botonesAccion.forEach(boton => {
        boton.addEventListener('click', () => {
            const accion = boton.dataset.accion;
            if (accion === 'seleccionar-todo') {
                filas.forEach(fila => fila.classList.add('documentos-lista-item-seleccionado'));
                recalcularSeleccion();
            } else if (accion === 'deseleccionar-todo') {
                filas.forEach(fila => fila.classList.remove('documentos-lista-item-seleccionado'));
                recalcularSeleccion();
            }
        });
    });

    // Inicializar contador total
    if (totalElementosSpan) {
        totalElementosSpan.textContent = filas.length.toString();
    }
});
