<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('trabajadores.edit');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) out(false, null, 'JSON inválido', 400);

$id = (int)($body['id'] ?? 0);
$id_trabajador = (int)($body['id_trabajador'] ?? 0);

if ($id <= 0) out(false, null, 'id inválido', 422);
if ($id_trabajador <= 0) out(false, null, 'id_trabajador inválido', 422);

try {
  $stmt = $mysql_db->prepare('DELETE FROM AA_trabajadores_formacion WHERE id=? AND id_trabajador=? LIMIT 1');
  $stmt->bind_param('ii', $id, $id_trabajador);
  $stmt->execute();
  out(true, ['deleted' => $stmt->affected_rows > 0]);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: ' . $e->getMessage(), 500);
}
