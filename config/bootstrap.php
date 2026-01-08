<?php
declare(strict_types=1);
date_default_timezone_set('Europe/Madrid');

// RAÍZ DE LA APP EN URL
if (!defined('APP_BASE_PATH')) {
    // en local: /ortzadar
    // en prod:  ''
    define('APP_BASE_PATH', '/webOrtzadar');
}

if (!defined('APP_DEBUG')) define('APP_DEBUG', true);

if (APP_DEBUG) { ini_set('display_errors','1'); error_reporting(E_ALL); }
else { ini_set('display_errors','0'); error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT); }

// cookies sesión seguras
$params = session_get_cookie_params();
session_set_cookie_params([
  'lifetime' => $params['lifetime'],
  'path'     => $params['path'] ?? '/',
  'domain'   => $params['domain'] ?? '',
  'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
  'httponly' => true,
  'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// ---- DB (ideal por env; si no, aquí fijo) ----
$dbHost = getenv('DB_SERVER') ?: 'ehost4036.hostinet.com:3306';
$dbUser = getenv('DB_USERNAME') ?: 'dcbxzdtm_ingenia';
$dbPass = getenv('DB_PASSWORD') ?: 'Ingenia*123';
$dbName = getenv('DB_NAMES') ?: 'dcbxzdtm_ortzadar';

$mysql_db = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysql_db->connect_error) { http_response_code(500); die('DB CONNECTION ERROR'); }
$mysql_db->set_charset('utf8mb4');

