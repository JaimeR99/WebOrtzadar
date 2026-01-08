<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('vida_independiente.view');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$idU = (int)($_GET['id_usuario'] ?? 0);
if ($idU <= 0) out(false, null, 'id_usuario inválido', 400);

try {
  $st = $mysql_db->prepare("
    SELECT id_usuario, responsable, integrador
    FROM AA_vida_independiente_participantes
    WHERE id_usuario=?
    LIMIT 1
  ");
  $st->bind_param('i', $idU);
  $st->execute();
  $p = $st->get_result()->fetch_assoc();

  if (!$p) $p = ['id_usuario'=>$idU, 'responsable'=>'', 'integrador'=>''];
  $p['responsable'] = (string)((int)($p['responsable'] ?? 0)) ?: '';
  $p['integrador']  = (string)((int)($p['integrador'] ?? 0)) ?: '';

  out(true, $p);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
