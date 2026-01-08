<?php
// public/html/menu_izquierdo.php
$pagina = $_GET['pagina'] ?? 'dashboard';
$isProgramas = ($pagina === 'programas') || str_starts_with($pagina, 'programas_');
?>
<aside id="menu-izquierdo" class="app-menu">
  <nav class="app-menu__nav">

    <a class="app-menu__item <?= $pagina === 'dashboard' ? 'is-active' : '' ?>" href="index.php?pagina=dashboard">
      <i class="fa-solid fa-house"></i><span>Dashboard</span>
    </a>

    <div class="menu-separador"></div>

    <a class="app-menu__item <?= $pagina === 'usuarios' ? 'is-active' : '' ?>" href="index.php?pagina=usuarios">
      <i class="fa-solid fa-users"></i><span>Usuarios</span>
    </a>

    <a class="app-menu__item <?= $isProgramas ? 'is-active' : '' ?>" href="index.php?pagina=programas">
      <i class="fa-solid fa-layer-group"></i><span>Programas</span>
    </a>
    <a class="app-menu__item <?= $pagina === 'trabajadores' ? 'is-active' : '' ?>" href="index.php?pagina=trabajadores">
      <i class="fa-solid fa-user-tie"></i><span>Trabajadores sociales</span>
    </a>
    <a class="app-menu__item <?= $pagina === 'gestion_documentos' ? 'is-active' : '' ?>" href="index.php?pagina=gestion_documentos">
      <i class="fa-solid fa-folder-open"></i><span>Documentos</span>
    </a>
    <!-- NOTA: Los servicios/programas se seleccionan dentro de la pantalla "Programas".
         Para no saturar el menú, aquí solo dejamos el acceso al selector. -->
  </nav>

  <!-- Footer fijo del menú -->
  <div class="app-menu__footer">
    <div class="menu-separador"></div>

    <div class="app-menu__status" title="Estado del sistema">
      <span class="status-text status-text--full">Estado del sistema</span>
      <span class="status-dot status-dot--ok" aria-hidden="true"></span>
    </div>
  </div>
</aside>