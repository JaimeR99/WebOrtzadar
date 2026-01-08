<?php
// public/dashboard.php
// Dashboard demo (datos inventados) – estructura por divs + paneles compartidos.
?>

<div class="app-page app--dashboard ayudas-dashboard">

  <!-- =====================
       KPI / TARJETAS
       ===================== -->
  <section class="kpi-grid">
    <div class="panel kpi-card">
      <div class="kpi-title">Personas Atendidas</div>
      <div class="kpi-value" data-counter="600">0</div>
    </div>

    <div class="panel kpi-card">
      <div class="kpi-title">Emergencias</div>
      <div class="kpi-value" data-counter="30">0</div>
    </div>

    <div class="panel kpi-card">
      <div class="kpi-title">Viviendas Gestionadas</div>
      <div class="kpi-value" data-counter="80">0</div>
    </div>

    <div class="panel kpi-card">
      <div class="kpi-title">Ayuda Salud</div>
      <div class="kpi-value" data-counter="120">0</div>
    </div>

    <div class="panel kpi-card">
      <div class="kpi-title">Ayuda Psicológica</div>
      <div class="kpi-value" data-counter="0">0</div>
    </div>

    <div class="panel kpi-card">
      <div class="kpi-title">Vacaciones</div>
      <div class="kpi-value" data-counter="40">0</div>
    </div>

    <div class="panel kpi-card">
      <div class="kpi-title">Independencia</div>
      <div class="kpi-value" data-counter="60">0</div>
    </div>

    <div class="panel kpi-card">
      <div class="kpi-title">Orientación</div>
      <div class="kpi-value" data-counter="0">0</div>
    </div>

    <div class="panel kpi-card">
      <div class="kpi-title">Acogidas</div>
      <div class="kpi-value" data-counter="75">0</div>
    </div>
  </section>


  <!-- =====================
       GRÁFICAS
       ===================== -->
  <section class="charts-grid">
    <div class="panel chart-card">
      <div class="chart-title">Distribución de Ayudas</div>
      <div class="chart-wrap">
        <canvas id="chartDistribucion" aria-label="Distribución de ayudas" role="img"></canvas>
      </div>
    </div>

    <div class="panel chart-card">
      <div class="chart-title">Porcentaje de Ayudas</div>
      <div class="chart-wrap">
        <canvas id="chartPorcentaje" aria-label="Porcentaje de ayudas" role="img"></canvas>
      </div>
    </div>

    <div class="panel chart-card">
      <div class="chart-title">Evolución Mensual</div>
      <div class="chart-wrap">
        <canvas id="chartEvolucion" aria-label="Evolución mensual" role="img"></canvas>
      </div>
    </div>

    <div class="panel chart-card">
      <div class="chart-title">Comparativa de Ayudas</div>
      <div class="chart-wrap">
        <canvas id="chartComparativa" aria-label="Comparativa de ayudas" role="img"></canvas>
      </div>
    </div>
  </section>

</div>

<!-- Chart.js (solo para este dashboard) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
