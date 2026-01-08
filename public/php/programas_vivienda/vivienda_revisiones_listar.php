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

$idVivienda = (int)($_GET['id_vivienda'] ?? 0);
if ($idVivienda <= 0) out(false, null, 'id_vivienda inválido', 400);

try {
  $sql = "
    SELECT id, id_vivienda, equipamiento, fecha_revision, proxima_revision, observaciones, avisar, dias_antes_aviso, ultima_notificacion
    FROM AA_vivienda_revisiones
    WHERE id_vivienda=?
    ORDER BY fecha_revision DESC, id DESC
    LIMIT 200
  ";
  $st = $mysql_db->prepare($sql);
  $st->bind_param('i', $idVivienda);
  $st->execute();
  $rs = $st->get_result();

  $rows = [];
  while ($r = $rs->fetch_assoc()) {
    $rows[] = [
      'id' => (int)$r['id'],
      'id_vivienda' => (int)$r['id_vivienda'],
      'equipamiento' => (string)$r['equipamiento'],
      'fecha_revision' => (string)$r['fecha_revision'],
      'proxima_revision' => $r['proxima_revision'],
      'observaciones' => $r['observaciones'],
      'avisar' => (int)$r['avisar'],
      'dias_antes_aviso' => (int)($r['dias_antes_aviso'] ?? 15),
      'ultima_notificacion' => $r['ultima_notificacion'],
    ];
  }

  out(true, $rows);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
