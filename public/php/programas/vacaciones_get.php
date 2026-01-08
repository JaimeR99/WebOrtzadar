<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('vacaciones.view');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) out(false, null, 'ID inválido', 400);

try {
  $stmt = $mysql_db->prepare("
    SELECT id, nombre, fecha, id_responsable, id_integrador
    FROM AA_Vacaciones_destinos
    WHERE id=? LIMIT 1
  ");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  if (!$row) out(false, null, 'No encontrado', 404);

  out(true, $row);
} catch (Throwable $e) {
  out(false, null, $e->getMessage(), 500);
}
