<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('trabajadores.view');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  $sql = "SELECT id, nombre, nivel, parent_id FROM AA_puestos ORDER BY nivel ASC, nombre ASC";
  $res = $mysql_db->query($sql);

  $rows = [];
  while ($r = $res->fetch_assoc()) {
    $rows[] = [
      'id' => (int)$r['id'],
      'nombre' => (string)$r['nombre'],
      'nivel' => (int)$r['nivel'],
      'parent_id' => $r['parent_id'] !== null ? (int)$r['parent_id'] : null,
    ];
  }
  out(true, $rows);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: ' . $e->getMessage(), 500);
}
