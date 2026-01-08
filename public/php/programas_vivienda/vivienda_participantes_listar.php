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

$id = (int)($_GET['id_vivienda'] ?? 0);
if ($id <= 0) out(false, null, 'id_vivienda inválido', 400);

try {
  $sql = "
    SELECT u.id, u.Nombre, u.Apellidos, u.Dni
    FROM AA_vivienda_participantes vp
    JOIN AA_usuarios u ON u.id = vp.id_usuario
    WHERE vp.id_vivienda = ?
    ORDER BY u.Apellidos ASC, u.Nombre ASC
  ";
  $st = $mysql_db->prepare($sql);
  $st->bind_param('i', $id);
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
