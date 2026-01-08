<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('viviendas.edit');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?: [];
$idV = (int)($body['id_vivienda'] ?? 0);
$idU = (int)($body['id_usuario'] ?? 0);
if ($idV <= 0 || $idU <= 0) out(false, null, 'Parámetros inválidos', 400);

try {
  $st = $mysql_db->prepare("INSERT IGNORE INTO AA_vivienda_participantes (id_vivienda, id_usuario) VALUES (?, ?)");
  $st->bind_param('ii', $idV, $idU);
  $st->execute();
  out(true, ['id_vivienda'=>$idV,'id_usuario'=>$idU]);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
