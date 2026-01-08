<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('viviendas.view');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$q = trim((string)($_GET['q'] ?? ''));
if ($q === '') out(true, []);

try {
  $like = '%' . $q . '%';
  $sql = "
    SELECT id, Nombre, Apellidos, Dni
    FROM AA_usuarios
    WHERE Nombre LIKE ? OR Apellidos LIKE ? OR Dni LIKE ? OR CONCAT(COALESCE(Nombre,''),' ',COALESCE(Apellidos,'')) LIKE ?
    ORDER BY Apellidos ASC, Nombre ASC
    LIMIT 25
  ";
  $st = $mysql_db->prepare($sql);
  $st->bind_param('ssss', $like, $like, $like, $like);
  $st->execute();
  $rs = $st->get_result();

  $rows = [];
  while ($r = $rs->fetch_assoc()) {
    $rows[] = [
      'id' => (int)$r['id'],
      'Nombre' => $r['Nombre'],
      'Apellidos' => $r['Apellidos'],
      'Dni' => $r['Dni'],
    ];
  }
  out(true, $rows);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
