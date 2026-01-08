<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';


mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  $rows = [];
  $res = $mysql_db->query("SELECT id, nombre, apellidos, email FROM AA_trabajadores ORDER BY apellidos, nombre");
  while ($r = $res->fetch_assoc()) {
    $rows[] = [
      'id' => (int)$r['id'],
      'nombre' => $r['nombre'] ?? '',
      'apellidos' => $r['apellidos'] ?? '',
      'email' => $r['email'] ?? '',
      'label' => trim(($r['apellidos'] ?? '').', '.($r['nombre'] ?? '')),
    ];
  }
  out(true, $rows);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
