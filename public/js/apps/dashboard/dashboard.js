/* public/js/apps/dashboard/dashboard.js
   Dashboard demo (datos inventados). Sin frameworks: JS vanilla + Chart.js.
*/

(function () {
  // =====================
  // 1) Contadores KPI
  // =====================
  function animateCounter(el, to, durationMs) {
    const start = 0;
    const startTs = performance.now();
    const easeOutCubic = (t) => 1 - Math.pow(1 - t, 3);

    function tick(now) {
      const p = Math.min(1, (now - startTs) / durationMs);
      const val = Math.round(start + (to - start) * easeOutCubic(p));
      el.textContent = String(val);
      if (p < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }

  document.querySelectorAll('.kpi-value[data-counter]').forEach((el) => {
    const target = Number(el.getAttribute('data-counter') || 0);
    animateCounter(el, target, 650);
  });

  // =====================
  // 2) Datos inventados
  // =====================
  const categorias = [
    'Vivienda',
    'Salud',
    'Psicológica',
    'Vacaciones',
    'Independencia',
    'Orientación',
    'Acogida',
    'Emergencias'
  ];

  const cantidades = [80, 120, 0, 40, 60, 0, 75, 30];

  const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo'];
  const evolucion = [20, 40, 60, 80, 100];

  const serieA = [85, 110, 35, 25, 45, 70, 60, 15];
  const serieB = [60, 90, 25, 40, 30, 55, 45, 25];

  // =====================
  // 3) Helpers de tema
  // =====================
  function cssVar(name, fallback) {
    const v = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    return v || fallback;
  }

  const textSoft = cssVar('--text-soft', '#9aa4b2');
  const grid = cssVar('--separator', 'rgba(148, 163, 184, 0.22)');

  function baseOptions() {
    return {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          labels: {
            color: textSoft,
            boxWidth: 14,
            boxHeight: 14,
            padding: 14
          }
        },
        tooltip: { enabled: true }
      },
      scales: {
        x: { ticks: { color: textSoft }, grid: { color: grid } },
        y: { ticks: { color: textSoft }, grid: { color: grid } }
      }
    };
  }

  // =====================
  // 4) Charts
  // =====================
  const ctxDistribucion = document.getElementById('chartDistribucion');
  const ctxPorcentaje = document.getElementById('chartPorcentaje');
  const ctxEvolucion = document.getElementById('chartEvolucion');
  const ctxComparativa = document.getElementById('chartComparativa');

  if (!window.Chart) {
    console.warn('Chart.js no está cargado.');
    return;
  }

  // Barras
  if (ctxDistribucion) {
    new Chart(ctxDistribucion, {
      type: 'bar',
      data: {
        labels: categorias,
        datasets: [{ label: 'Cantidad', data: cantidades }]
      },
      options: baseOptions()
    });
  }

  // Tarta
  if (ctxPorcentaje) {
    const opts = baseOptions();
    delete opts.scales;
    new Chart(ctxPorcentaje, {
      type: 'pie',
      data: {
        labels: categorias,
        datasets: [{ label: 'Porcentaje', data: cantidades }]
      },
      options: opts
    });
  }

  // Línea
  if (ctxEvolucion) {
    new Chart(ctxEvolucion, {
      type: 'line',
      data: {
        labels: meses,
        datasets: [{
          label: 'Ayudas Mensuales',
          data: evolucion,
          tension: 0.25,
          pointRadius: 3
        }]
      },
      options: baseOptions()
    });
  }

  // Radar
  if (ctxComparativa) {
    new Chart(ctxComparativa, {
      type: 'radar',
      data: {
        labels: categorias,
        datasets: [
          { label: 'Comparativa A', data: serieA },
          { label: 'Comparativa B', data: serieB }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { color: textSoft } } },
        scales: {
          r: {
            angleLines: { color: grid },
            grid: { color: grid },
            pointLabels: { color: textSoft },
            ticks: { color: textSoft, backdropColor: 'transparent' }
          }
        }
      }
    });
  }
})();
