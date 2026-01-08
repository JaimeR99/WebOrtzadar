<?php
// public/html/cabecera.php
?>
<header id="cabecera-global" class="app-header">
  <div class="app-header__left">
    <button class="icon-btn" type="button" data-toggle="menu-izquierdo" title="Menú">
      <i class="fa-solid fa-bars"></i>
    </button>
    <div class="app-header__title">
      <span class="app-header__brand">ORTZADAR</span>
      <span class="app-header__subtitle">Panel de aplicaciones</span>
    </div>
  </div>

  <div class="app-header__right">
    <button class="icon-btn" type="button" data-toggle="tema" title="Cambiar tema">
      <i class="fa-solid fa-circle-half-stroke"></i>
    </button>

    <a class="btn btn--danger btn--sm" href="logout.php" title="Cerrar sesión">
      <i class="fa-solid fa-right-from-bracket"></i>
      Salir
    </a>
  </div>
</header>
