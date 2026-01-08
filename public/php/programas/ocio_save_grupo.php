<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('ocio.edit');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) out(false, null, 'JSON inválido', 400);

$id = isset($body['id']) ? (int)$body['id'] : 0;
$nombre = trim((string)($body['nombre'] ?? ''));
$fecha  = trim((string)($body['fecha'] ?? ''));          // "YYYY-MM-DDTHH:MM" o "YYYY-MM-DD HH:MM:SS"
$id_responsable = $body['id_responsable'] ?? null;

if ($nombre === '') out(false, null, 'Nombre es obligatorio', 422);

// normaliza fecha
if ($fecha === '') {
  $fecha = date('Y-m-d H:i:s');
} else {
  $fecha = str_replace('T', ' ', $fecha);
  if (strlen($fecha) === 16) $fecha .= ':00';
}

// responsable puede ser null
if ($id_responsable === '' || $id_responsable === 0 || $id_responsable === '0') {
  $id_responsable = null;
} else {
  $id_responsable = (int)$id_responsable;
}

try {
  if ($id > 0) {
    $stmt = $mysql_db->prepare("
      UPDATE AA_Ocio_grupos
      SET nombre=?, fecha=?, id_responsable=?
      WHERE id=?
      LIMIT 1
    ");
    // i puede ser null -> bind como "i" pero pasando null funciona en mysqli (siempre que sea variable)
    $stmt->bind_param("ssii", $nombre, $fecha, $id_responsable, $id);
    $stmt->execute();

    out(true, ['id' => $id]);
  }

  $stmt = $mysql_db->prepare("
    INSERT INTO AA_Ocio_grupos (nombre, fecha, id_responsable)
    VALUES (?, ?, ?)
  ");
  $stmt->bind_param("ssi", $nombre, $fecha, $id_responsable);
  $stmt->execute();

  out(true, ['id' => (int)$mysql_db->insert_id]);

} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
