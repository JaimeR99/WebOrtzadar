<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('vida_independiente.edit');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?: [];

$idU = (int)($body['id_usuario'] ?? 0);
$equipamiento = (string)($body['equipamiento'] ?? '');
$fechaRev = (string)($body['fecha_revision'] ?? '');
$prox = (string)($body['proxima_revision'] ?? '');
$obs = trim((string)($body['observaciones'] ?? ''));
$avisar = (int)($body['avisar'] ?? 0);
$dias = (int)($body['dias_antes_aviso'] ?? 15);

if ($idU <= 0) out(false, null, 'id_usuario inválido', 400);

$validEq = ['extintores','contratos','cei','salto'];
if (!in_array($equipamiento, $validEq, true)) out(false, null, 'equipamiento inválido', 400);

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaRev)) out(false, null, 'fecha_revision inválida', 400);
if ($prox !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $prox)) out(false, null, 'proxima_revision inválida', 400);

$avisar = $avisar ? 1 : 0;
if ($dias < 1 || $dias > 365) $dias = 15;
if (mb_strlen($obs) > 1000) $obs = mb_substr($obs, 0, 1000);

try {
  $sql = "
    INSERT INTO AA_vida_independiente_revisiones
      (id_usuario, equipamiento, fecha_revision, proxima_revision, observaciones, avisar, dias_antes_aviso)
    VALUES
      (?, ?, ?, NULLIF(?, ''), ?, ?, ?)
  ";
  $st = $mysql_db->prepare($sql);
  $st->bind_param('issssii', $idU, $equipamiento, $fechaRev, $prox, $obs, $avisar, $dias);
  $st->execute();

  out(true, ['id_nuevo' => (int)$mysql_db->insert_id]);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
