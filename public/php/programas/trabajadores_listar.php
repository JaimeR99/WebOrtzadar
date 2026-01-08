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

$q = trim((string)($_GET['q'] ?? ''));

try {
  $sql = "SELECT id, nombre, apellidos FROM AA_trabajadores";
  $types = '';
  $params = [];

  if ($q !== '') {
    $sql .= " WHERE CONCAT(COALESCE(nombre,''), ' ', COALESCE(apellidos,'')) LIKE ? ";
    $types = 's';
    $params[] = "%$q%";
  }

  $sql .= " ORDER BY nombre ASC, apellidos ASC LIMIT 200";

  $stmt = $mysql_db->prepare($sql);
  if ($types !== '') {
    $stmt->bind_param($types, $params[0]);
  }
  $stmt->execute();
  $res = $stmt->get_result();

  $rows = [];
  while ($r = $res->fetch_assoc()) {
    $rows[] = [
      'id' => (int)$r['id'],
      'nombre' => trim(($r['nombre'] ?? '').' '.($r['apellidos'] ?? '')),
    ];
  }

  out(true, $rows);

} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
