<?php
// /index.php  (RAÍZ DEL PROYECTO)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/public/php/configuracion.php';
require_once __DIR__ . '/public/php/funciones_sesion.php';

/* ==========================
 *  CONTROL DE SESIÓN
 * ========================== */
if (function_exists('usuario_esta_autenticado')) {
    if (!usuario_esta_autenticado()) {
        header('Location: login.php');
        exit;
    }
}

/* ==========================
 *  PÁGINA ACTIVA
 * ========================== */
$pagina = $_GET['pagina'] ?? 'dashboard';
$pagina = basename($pagina);

$ruta_app = __DIR__ . "/public/{$pagina}.php";
if (!file_exists($ruta_app)) {
    $pagina = 'dashboard';
    $ruta_app = __DIR__ . "/public/dashboard.php";
}

/* ==========================
 *  RUTAS CSS / JS DE APP
 *  (RELATIVAS A LA RAÍZ)
 * ========================== */
$css_app = "public/css/apps/{$pagina}/{$pagina}.css";
$js_app  = "public/js/apps/{$pagina}/{$pagina}.js";

/* ==========================
 *  CABECERA Y MENÚ (tu estructura real)
 * ========================== */
$cabecera_file = __DIR__ . '/public/html/cabecera.php';
$menu_file     = __DIR__ . '/public/html/menu_izquierdo.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de aplicaciones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- FontAwesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- CSS GLOBAL (3 GRANDES) -->
    <link rel="stylesheet" href="public/css/shared/theme.css">
    <link rel="stylesheet" href="public/css/shared/base.css">
    <link rel="stylesheet" href="public/css/shared/components.css">
    <link rel="stylesheet" href="public/css/shared/modal_base.css">

    <!-- CSS ESPECÍFICO DE APP -->
    <?php if (file_exists(__DIR__ . '/' . $css_app)) : ?>
        <link rel="stylesheet" href="<?= $css_app ?>">
    <?php endif; ?>
</head>

<body class="page-<?= htmlspecialchars($pagina, ENT_QUOTES, 'UTF-8') ?>">

<div id="contenedor-general">

    <!-- CABECERA -->
    <?php if (file_exists($cabecera_file)) include $cabecera_file; ?>

    <div id="contenedor-principal">

        <!-- MENÚ -->
        <?php if (file_exists($menu_file)) include $menu_file; ?>

        <!-- APP -->
        <main id="zona-aplicaciones">
            <?php include $ruta_app; ?>
        </main>

    </div>
</div>

<!-- JS GLOBAL -->
<script src="public/js/cabecera.js"></script>
<script src="public/js/menu_izquierdo.js"></script>

<!-- JS ESPECÍFICO DE APP -->
<?php if (file_exists(__DIR__ . '/' . $js_app)) : ?>
    <script src="<?= $js_app ?>"></script>
<?php endif; ?>

</body>
</html>
