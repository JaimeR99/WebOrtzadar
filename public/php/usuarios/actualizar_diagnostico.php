<?php
// public/php/usuarios/actualizar_diagnostico.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('usuarios.edit');

function json_out(bool $ok, $data = null, ?string $error = null, int $code = 200): void {
  http_response_code($code);
  echo json_encode([
    'ok'    => $ok,
    'data'  => $data,
    'error' => $error
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

function clean_str($v): string {
  $s = (string)($v ?? '');
  $s = str_replace("\0", "", $s);
  return trim($s);
}

try {
  // Inicializar conexión
  $root = dirname(__DIR__, 3);
  $bootstrap = $root . '/config/bootstrap.php';
  if (!file_exists($bootstrap)) {
    json_out(false, null, 'No existe config/bootstrap.php', 500);
  }
  require_once $bootstrap;
  if (isset($mysql_db) && $mysql_db instanceof mysqli) $mysqli = $mysql_db;
  elseif (isset($GLOBALS['mysql_db']) && $GLOBALS['mysql_db'] instanceof mysqli) $mysqli = $GLOBALS['mysql_db'];
  if (!$mysqli) json_out(false, null, 'No se encontró la conexión a la BD ($mysql_db)', 500);

  @$mysqli->set_charset('utf8mb4');

  // Leer cuerpo JSON de la petición
  $body = json_decode(file_get_contents('php://input'), true);
  if (!is_array($body)) {
    json_out(false, null, 'JSON inválido', 400);
  }

  // Validar y sanitizar parámetros esperados
  $tableKey = clean_str($body['table'] ?? $body['tipo'] ?? '');
  $id = $body['id'] ?? null;
  $userId = $body['userId'] ?? null;
  $updates = $body['updates'] ?? null;

  if ($tableKey === '' || !in_array($tableKey, ['dependencia','discapacidad','exclusion'], true)) {
    json_out(false, null, 'Tabla no permitida', 403);
  }
  $allowed = [
    'dependencia'  => 'AA_Dependencia',
    'discapacidad' => 'AA_Discapacidad',
    'exclusion'    => 'AA_Exclusion',
  ];
  $table = $allowed[$tableKey];

  // ID de diagnóstico
  if ($id !== null && $id !== '' && !ctype_digit((string)$id)) {
    json_out(false, null, 'ID inválido', 422);
  }
  $id = $id === null || $id === '' ? null : (int)$id;

  // Validar datos de actualización
  if (!is_array($updates)) {
    json_out(false, null, 'No hay cambios', 422);
  }

  // Obtener columnas válidas de la tabla
  $colsRes = $mysqli->query("DESCRIBE `$table`");
  if (!$colsRes) {
    json_out(false, null, 'Error describiendo tabla: '.$mysqli->error, 500);
  }
  $validCols = [];
  while ($col = $colsRes->fetch_assoc()) {
    $field = $col['Field'];
    if (strcasecmp($field, 'id') !== 0) {
      $validCols[] = $field;
    }
  }

  // Construir listas de campos a actualizar/insertar y sus valores
  $fields = [];
  $values = [];
  $types  = '';
  foreach ($updates as $field => $value) {
    $field = clean_str($field);
    if (!in_array($field, $validCols, true)) continue;  // ignorar campos no válidos
    $fields[] = "`$field`";
    $values[] = clean_str($value);
    $types   .= 's';
  }
  if (empty($fields)) {
    json_out(false, null, 'No hay campos válidos para actualizar', 422);
  }

  // Ejecutar INSERT o UPDATE según corresponda
  if ($id === null) {
    // Insertar nuevo diagnóstico
    $placeholders = implode(',', array_fill(0, count($fields), '?'));
    $sql = "INSERT INTO `$table` (" . implode(',', $fields) . ") VALUES ($placeholders)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
      json_out(false, null, 'Error al preparar inserción: ' . $mysqli->error, 500);
    }
    if (!$stmt->bind_param($types, ...$values)) {
      json_out(false, null, 'Error en bind (insertar): ' . $stmt->error, 500);
    }
    if (!$stmt->execute()) {
      json_out(false, null, 'Error al insertar: ' . $stmt->error, 500);
    }
    $newId = $mysqli->insert_id;
    // Vincular nuevo diagnóstico al usuario (actualizar campo ID_DIAG_* en tabla de usuarios)
    if ($userId === null || !ctype_digit((string)$userId)) {
      json_out(false, null, 'Diagnóstico creado (ID='.$newId.'), pero userId inválido para vincular', 200);
    }
    $userId = (int)$userId;
    $userTable = 'AA_usuarios';  // Nombre de la tabla de usuarios
    $diagField = '';
    if ($tableKey === 'dependencia') $diagField = 'ID_DIAG_Dependencia';
    if ($tableKey === 'discapacidad') $diagField = 'ID_DIAG_Discapacidad';
    if ($tableKey === 'exclusion') $diagField = 'ID_DIAG_Exclusion';
    if ($diagField !== '') {
      $sqlUser = "UPDATE `$userTable` SET `$diagField` = ? WHERE `Id` = ? LIMIT 1";
      $stmt2 = $mysqli->prepare($sqlUser);
      if ($stmt2) {
        $stmt2->bind_param('ii', $newId, $userId);
        $stmt2->execute();
        // (No se termina la ejecución si falla este update; simplemente no vincula)
      }
    }
    json_out(true, ['id' => $newId]);
  } else {
    // Actualizar diagnóstico existente
    $setSql = implode(' = ?, ', $fields) . ' = ?';
    $sql = "UPDATE `$table` SET $setSql WHERE `Id` = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
      json_out(false, null, 'Error al preparar actualización: ' . $mysqli->error, 500);
    }
    // Agregar tipo y valor para Id al final
    $types .= 'i';
    $values[] = $id;
    if (!$stmt->bind_param($types, ...$values)) {
      json_out(false, null, 'Error en bind (actualizar): ' . $stmt->error, 500);
    }
    if (!$stmt->execute()) {
      json_out(false, null, 'Error al actualizar: ' . $stmt->error, 500);
    }
    json_out(true, ['id' => $id]);
  }

} catch (Throwable $e) {
  json_out(false, null, $e->getMessage(), 500);
}
