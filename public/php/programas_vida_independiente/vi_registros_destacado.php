<?php
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('vida_independiente.edit');

header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?: [];
$id = (int)($body['id'] ?? 0);
$idUsuario = (int)($body['id_usuario'] ?? 0);
$destacado = (int)($body['destacado'] ?? -1);

if ($id <= 0 || $idUsuario <= 0 || !in_array($destacado, [0,1], true)) {
  out(false, null, 'Parámetros inválidos', 400);
}

try {
  $st = $mysql_db->prepare("
    UPDATE AA_vida_independiente_registros
    SET Destacado=?
    WHERE id=? AND ID_Usuario=?
    LIMIT 1
  ");
  $st->bind_param("iii", $destacado, $id, $idUsuario);
  $st->execute();
  if ($st->affected_rows <= 0) out(false, null, 'No actualizado', 400);
  out(true, ['id'=>$id, 'Destacado'=>$destacado]);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
