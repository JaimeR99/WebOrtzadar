<?php
// public/php/usuarios/obtener_usuarios.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('usuarios.view');

function respond(bool $ok, array $payload = [], int $code = 200): void {
  http_response_code($code);
  echo json_encode(array_merge(['ok' => $ok], $payload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

function clean_value($v) {
  if (is_string($v)) {
    // elimina NULL bytes que vienen de CHAR/BINARY mal formateados
    $v = str_replace("\0", "", $v);
    return trim($v);
  }
  return $v;
}

function clean_row(array $row): array {
  foreach ($row as $k => $v) {
    $row[$k] = clean_value($v);
  }
  return $row;
}

try {
  // /public/php/usuarios -> subir 3 niveles = raíz del proyecto
  $root = dirname(__DIR__, 3);
  $bootstrap = $root . '/config/bootstrap.php';

  if (!file_exists($bootstrap)) {
    respond(false, [
      'error' => 'No existe config/bootstrap.php en la ruta esperada.',
      'debug' => ['expected' => $bootstrap, 'this_file' => __FILE__]
    ], 500);
  }

  require_once $bootstrap;

  // Tu bootstrap crea: $mysql_db = new mysqli(...)
  $mysqli = null;
  if (isset($mysql_db) && $mysql_db instanceof mysqli) {
    $mysqli = $mysql_db;
  } elseif (isset($GLOBALS['mysql_db']) && $GLOBALS['mysql_db'] instanceof mysqli) {
    $mysqli = $GLOBALS['mysql_db'];
  }

  if (!$mysqli) {
    respond(false, [
      'error' => 'bootstrap cargado, pero no se encontró $mysql_db (mysqli).',
      'debug' => ['hint' => 'En config/bootstrap.php se crea $mysql_db.']
    ], 500);
  }

  // charset recomendado
  @$mysqli->set_charset('utf8mb4');

  $sql = "SELECT * FROM AA_usuarios ORDER BY 1 DESC";
  $res = $mysqli->query($sql);

  if (!$res) {
    respond(false, ['error' => 'Error SQL: ' . $mysqli->error], 500);
  }

  $usuarios = $res->fetch_all(MYSQLI_ASSOC);
  $usuarios = array_map('clean_row', $usuarios);

  $cols = !empty($usuarios) ? array_keys($usuarios[0]) : [];

  respond(true, [
    'usuarios' => $usuarios,
    'cols' => $cols,
    'count' => count($usuarios),
  ]);

} catch (Throwable $e) {
  respond(false, ['error' => $e->getMessage()], 500);
}
