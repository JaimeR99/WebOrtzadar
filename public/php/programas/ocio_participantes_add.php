<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('ocio.edit');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) out(false, null, 'JSON inválido', 400);

$id_grupo = (int)($body['id_grupo'] ?? 0);
$id_usuario = (int)($body['id_usuario'] ?? 0);

if ($id_grupo <= 0) out(false, null, 'id_grupo inválido', 422);
if ($id_usuario <= 0) out(false, null, 'id_usuario inválido', 422);

try {
  // evitar duplicado
  $chk = $mysql_db->prepare("SELECT id FROM AA_Ocio_participantes WHERE id_grupo=? AND id_usuario=? LIMIT 1");
  $chk->bind_param("ii", $id_grupo, $id_usuario);
  $chk->execute();
  $exists = $chk->get_result()->fetch_assoc();

  if ($exists) out(true, ['id' => (int)$exists['id']]);

  $ins = $mysql_db->prepare("INSERT INTO AA_Ocio_participantes (id_grupo, id_usuario) VALUES (?, ?)");
  $ins->bind_param("ii", $id_grupo, $id_usuario);
  $ins->execute();

  out(true, ['id' => (int)$mysql_db->insert_id]);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
