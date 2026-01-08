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
$remove_asistencia = (bool)($body['remove_asistencia'] ?? true);

if ($id_grupo <= 0) out(false, null, 'id_grupo inválido', 422);
if ($id_usuario <= 0) out(false, null, 'id_usuario inválido', 422);

try {
  $mysql_db->begin_transaction();

  // 1) borrar relación participante
  $del = $mysql_db->prepare("DELETE FROM AA_Ocio_participantes WHERE id_grupo=? AND id_usuario=?");
  $del->bind_param("ii", $id_grupo, $id_usuario);
  $del->execute();

  $deleted_asistencia = 0;

  // 2) opcional: borrar asistencias del usuario en actas del grupo
  if ($remove_asistencia) {
    $sql = "
      DELETE s
      FROM AA_Ocio_asistencia s
      INNER JOIN AA_Ocio_actas a ON a.id = s.id_acta
      WHERE a.id_grupo = ? AND s.id_usuario = ?
    ";
    $st = $mysql_db->prepare($sql);
    $st->bind_param("ii", $id_grupo, $id_usuario);
    $st->execute();
    $deleted_asistencia = $st->affected_rows;
  }

  $mysql_db->commit();

  out(true, [
    'deleted_participante' => true,
    'deleted_asistencia' => $deleted_asistencia,
    'remove_asistencia' => $remove_asistencia
  ]);
} catch (Throwable $e) {
  $mysql_db->rollback();
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
