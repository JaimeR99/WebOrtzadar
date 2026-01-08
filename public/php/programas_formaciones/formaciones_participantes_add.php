<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('formaciones.edit');

function out($ok, $data=null, $error=null, $code=200){
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) out(false, null, 'Body JSON inválido', 400);

$idFormacion = (int)($body['id_formacion'] ?? 0);
$idUsuario = (int)($body['id_usuario'] ?? 0);
if ($idFormacion<=0 || $idUsuario<=0) out(false, null, 'id_formacion / id_usuario inválidos', 400);

// ya existe?
$chk = $mysql_db->prepare("SELECT id FROM AA_Formaciones_participantes WHERE id_formacion=? AND id_usuario=? LIMIT 1");
if (!$chk) out(false, null, 'SQL prepare error: '.$mysql_db->error, 500);
$chk->bind_param('ii', $idFormacion, $idUsuario);
if (!$chk->execute()) out(false, null, 'SQL execute error: '.$chk->error, 500);
$r = $chk->get_result();
if ($r && $r->num_rows > 0) { $chk->close(); out(true, ['already'=>true]); }
$chk->close();

$stmt = $mysql_db->prepare("INSERT INTO AA_Formaciones_participantes (id_formacion, id_usuario) VALUES (?, ?)");
if (!$stmt) out(false, null, 'SQL prepare error: '.$mysql_db->error, 500);
$stmt->bind_param('ii', $idFormacion, $idUsuario);
if (!$stmt->execute()) out(false, null, 'SQL execute error: '.$stmt->error, 500);
$newId = (int)$stmt->insert_id;
$stmt->close();

out(true, ['id'=>$newId]);
