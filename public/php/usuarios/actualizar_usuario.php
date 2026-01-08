<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('usuarios.edit');

function json_out($ok, $data = null, $error = null, $code = 200): void {
  http_response_code($code);
  echo json_encode([
    'ok' => $ok,
    'data' => $data,
    'error' => $error
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) json_out(false, null, 'JSON inválido', 400);

$id = $body['id'] ?? null;
if (!is_numeric($id)) json_out(false, null, 'ID inválido', 422);
$id = (int)$id;

$updates = $body['updates'] ?? null;
if (!is_array($updates) || !count($updates)) json_out(false, null, 'No hay cambios', 422);

// Whitelist de columnas actualizables (AA_usuarios)
$allowed = [
  'Nombre','Apellidos','Dni','Fecha_Nacimiento','Nacionalidad','Nivel_Estudios','CCC',
  'Correo','Telefono_Usuario','Direccion','Codigo_Postal',
  'Telefono_Familia1','Telefono_Familia2','Telefono_Servicios_Sociales','Telefono_Trabajadora_Social',
  'Telefono_Centro_Salud','Telefono_Medico_Cavecera','Telefono_Salud_Mental','Telefono_Referente_Salud',
  'Telefono_Referente_Formativo','Telefono_Otros1','Telefono_Otros2','ID_Via_Comunicacion',
  'Sexo','Tipo_Socio','Fecha_Alta','ID_Situacion_Administrativa','N_TIS',
  'ID_DIAG_Discapacidad','ID_DIAG_Dependencia','ID_DIAG_Exclusion','ID_DIAG_CapacidadJuridica'
];
$allowedSet = array_flip($allowed);

$fields = [];
$values = [];

foreach ($updates as $k => $v) {
  if (!isset($allowedSet[$k])) continue;
  $fields[] = $k;
  // Normaliza valores: strings, recorta null bytes
  if (is_string($v)) {
    $v = str_replace("\0", '', $v);
    $v = trim($v);
  }
  $values[] = $v;
}

if (!count($fields)) json_out(false, null, 'No hay campos permitidos para actualizar', 422);

$setSql = implode(', ', array_map(fn($f) => "`$f` = ?", $fields));
$sql = "UPDATE `AA_usuarios` SET $setSql WHERE `id` = ? LIMIT 1";

$stmt = $mysql_db->prepare($sql);
if (!$stmt) json_out(false, null, 'Prepare failed', 500);

// Tipos: todo string + id int. MySQL hará casting cuando proceda.
$types = str_repeat('s', count($fields)) . 'i';
$params = array_merge($values, [$id]);

// bind_param por referencia
$bind = [];
$bind[] = $types;
for ($i = 0; $i < count($params); $i++) {
  $bind[] = &$params[$i];
}

call_user_func_array([$stmt, 'bind_param'], $bind);

if (!$stmt->execute()) {
  json_out(false, null, 'Execute failed: ' . $stmt->error, 500);
}

json_out(true, [
  'id' => $id,
  'updated' => $fields,
  'affected_rows' => $stmt->affected_rows
]);
