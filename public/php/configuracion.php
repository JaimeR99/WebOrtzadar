<?php
// public/php/configuracion.php

/**
 * Configuración base del proyecto.
 * Aquí puedes meter más adelante:
 * - conexión BD
 * - constantes globales
 * - configuración de rutas
 */

declare(strict_types=1);

// Zona horaria (ajusta si quieres)
date_default_timezone_set('Europe/Madrid');

// Entorno (true = muestra errores, false = producción)
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', true);
}

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

// Nombre de la app (opcional)
if (!defined('APP_NAME')) {
    define('APP_NAME', 'AUSKULTATOOL');
}
