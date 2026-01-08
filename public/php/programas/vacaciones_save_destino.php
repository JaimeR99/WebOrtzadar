<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('vacaciones.edit');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) out(false, null, 'JSON inválido', 400);

$id             = (int)($body['id'] ?? 0);
$nombre         = trim((string)($body['nombre'] ?? ''));
$fecha          = trim((string)($body['fecha'] ?? ''));
$id_responsable = isset($body['id_responsable']) ? (int)$body['id_responsable'] : null;
$id_integrador  = isset($body['id_integrador']) ? (int)$body['id_integrador'] : null;

if ($nombre === '') out(false, null, 'El nombre es obligatorio', 422);

// normaliza fecha (acepta "YYYY-MM-DDTHH:MM" o "YYYY-MM-DD HH:MM:SS")
if ($fecha === '') {
  $fecha = date('Y-m-d H:i:s');
} else {
  $fecha = str_replace('T', ' ', $fecha);
  if (strlen($fecha) === 16) $fecha .= ':00';
}

// responsable/integrador pueden ser null
if ($id_responsable === 0) $id_responsable = null;
if ($id_integrador === 0) $id_integrador = null;

try {
  if ($id > 0) {
    $stmt = $mysql_db->prepare("
      UPDATE AA_Vacaciones_destinos
      SET nombre=?, fecha=?, id_responsable=?, id_integrador=?
      WHERE id=? LIMIT 1
    ");
    $stmt->bind_param("ssiii", $nombre, $fecha, $id_responsable, $id_integrador, $id);
    $stmt->execute();
    out(true, ['id'=>$id]);
  }

  $stmt = $mysql_db->prepare("
    INSERT INTO AA_Vacaciones_destinos (nombre, fecha, id_responsable, id_integrador)
    VALUES (?, ?, ?, ?)
  ");
  $stmt->bind_param("ssii", $nombre, $fecha, $id_responsable, $id_integrador);
  $stmt->execute();
  out(true, ['id'=>(int)$mysql_db->insert_id]);
} catch (Throwable $e) {
  out(false, null, $e->getMessage(), 500);
}
