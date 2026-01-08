<?php
// public/php/usuarios/obtener_diagnostico.php
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
    $v = str_replace("\0", "", $v);
    return trim($v);
  }
  return $v;
}

function clean_row(array $row): array {
  $out = [];
  foreach ($row as $k => $v) {
    $out[$k] = clean_value($v);
  }
  return $out;
}

// Función para obtener entero o null de _GET
function get_int_or_null(string $key): ?int {
  if (!isset($_GET[$key])) return null;
  $v = trim((string)$_GET[$key]);
  if ($v === '' || strtolower($v) === 'null' || !ctype_digit($v)) return null;
  return (int)$v;
}

try {
  // Cargar configuración y conexión (mysqli) desde bootstrap
  $root = dirname(__DIR__, 3);
  $bootstrap = $root . '/config/bootstrap.php';
  if (!file_exists($bootstrap)) {
    respond(false, ['error' => 'No existe config/bootstrap.php', 'expected' => $bootstrap], 500);
  }
  require_once $bootstrap;
  if (isset($mysql_db) && $mysql_db instanceof mysqli) $mysqli = $mysql_db;
  elseif (isset($GLOBALS['mysql_db']) && $GLOBALS['mysql_db'] instanceof mysqli) $mysqli = $GLOBALS['mysql_db'];
  if (!$mysqli) respond(false, ['error' => 'No se encontró $mysql_db (mysqli)'], 500);

  @$mysqli->set_charset('utf8mb4');

  // Mapear tipos de diagnóstico a sus tablas
  $allowed = [
    'dependencia'  => 'AA_Dependencia',
    'discapacidad' => 'AA_Discapacidad',
    'exclusion'    => 'AA_Exclusion',
  ];

  // Leer parámetros de IDs (pueden ser null)
  $ids = [
    'dependencia'  => get_int_or_null('dep_id'),
    'discapacidad' => get_int_or_null('disc_id'),
    'exclusion'    => get_int_or_null('excl_id'),
  ];

  $out = [];
  foreach ($ids as $key => $id) {
    if (!isset($allowed[$key])) continue;
    $table = $allowed[$key];

    // Si no se proporcionó ID (no hay diagnóstico existente), preparar campos vacíos
    if ($id === null) {
      // Obtener lista de columnas de la tabla para enviar al front-end
      $colsRes = $mysqli->query("DESCRIBE `$table`");
      $cols = [];
      if ($colsRes) {
        while ($col = $colsRes->fetch_assoc()) {
          $field = $col['Field'];
          if (strcasecmp($field, 'id') !== 0) {  // excluir columna Id
            $cols[] = $field;
          }
        }
      }
      $out[$key] = [
        'id'   => null,
        'table'=> $table,
        'cols' => $cols,
        'row'  => []  // sin datos
      ];
      continue;
    }

    // Buscar registro de diagnóstico existente
    $sql = "SELECT * FROM `$table` WHERE `Id` = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) respond(false, ['error' => "Error de preparación ($table): ".$mysqli->error], 500);
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) respond(false, ['error' => "Error en ejecución ($table): ".$stmt->error], 500);

    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $row = $row ? clean_row($row) : [];
    $cols = $row ? array_keys($row) : [];

    $out[$key] = [
      'id'    => $id,
      'table' => $table,
      'cols'  => $cols,
      'row'   => $row
    ];
  }

  respond(true, ['diagnosticos' => $out]);

} catch (Throwable $e) {
  respond(false, ['error' => $e->getMessage()], 500);
}
