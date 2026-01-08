<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';require_once __DIR__ . '/../funciones_sesion.php';

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
    SELECT id, equipamiento, fecha_revision, proxima_revision, observaciones, avisar, dias_antes_aviso
    FROM AA_vida_independiente_revisiones
    WHERE id_usuario=?
    ORDER BY fecha_revision DESC, id DESC
  ");
  $st->bind_param('i', $idU);
  $st->execute();
  out(true, $st->get_result()->fetch_all(MYSQLI_ASSOC));
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
