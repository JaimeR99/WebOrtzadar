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

$idActa = (int)($body['id_acta'] ?? 0);
$idFormacion = (int)($body['id_formacion'] ?? 0);
$fecha = $body['fecha'] ?? null;
$valoracion = trim((string)($body['valoracion'] ?? ''));

$presentes = $body['presentes'] ?? [];
$incidencias = $body['incidencias'] ?? [];

if ($idFormacion <= 0) out(false, null, 'id_formacion inválido', 400);
if ($fecha !== null && !is_string($fecha)) out(false, null, 'fecha inválida', 400);
if (!is_array($presentes)) out(false, null, 'presentes inválido', 400);
if (!is_array($incidencias)) out(false, null, 'incidencias inválido', 400);

// ⚠️ Ajusta a tu login
$idCreatedBy = isset($_SESSION['id_trabajador']) ? (int)$_SESSION['id_trabajador'] : null;

// normaliza arrays
$presentesClean = [];
foreach ($presentes as $p) {
  $n = (int)$p;
  if ($n > 0) $presentesClean[$n] = true;
}
$presentesClean = array_keys($presentesClean);

$incClean = [];
foreach ($incidencias as $t) {
  $s = trim((string)$t);
  if ($s !== '') $incClean[] = $s;
}

$mysql_db->begin_transaction();

try {
  // 1) acta
  if ($idActa > 0) {
    if ($fecha === null) {
      $st = $mysql_db->prepare("UPDATE AA_Formaciones_actas SET fecha=NULL, valoracion=? WHERE id=? AND id_formacion=?");
      if (!$st) throw new Exception('SQL prepare error: '.$mysql_db->error);
      $st->bind_param('sii', $valoracion, $idActa, $idFormacion);
    } else {
      $st = $mysql_db->prepare("UPDATE AA_Formaciones_actas SET fecha=?, valoracion=? WHERE id=? AND id_formacion=?");
      if (!$st) throw new Exception('SQL prepare error: '.$mysql_db->error);
      $st->bind_param('ssii', $fecha, $valoracion, $idActa, $idFormacion);
    }
    if (!$st->execute()) throw new Exception('SQL execute error: '.$st->error);
    $st->close();
  } else {
    if ($fecha === null) {
      $st = $mysql_db->prepare("INSERT INTO AA_Formaciones_actas (id_formacion, fecha, valoracion, created_by) VALUES (?, NULL, ?, ?)");
      if (!$st) throw new Exception('SQL prepare error: '.$mysql_db->error);
      $st->bind_param('isi', $idFormacion, $valoracion, $idCreatedBy);
    } else {
      $st = $mysql_db->prepare("INSERT INTO AA_Formaciones_actas (id_formacion, fecha, valoracion, created_by) VALUES (?, ?, ?, ?)");
      if (!$st) throw new Exception('SQL prepare error: '.$mysql_db->error);
      $st->bind_param('issi', $idFormacion, $fecha, $valoracion, $idCreatedBy);
    }
    if (!$st->execute()) throw new Exception('SQL execute error: '.$st->error);
    $idActa = (int)$st->insert_id;
    $st->close();
  }

  // 2) presentes: reescribir (simple y robusto)
  $stDel = $mysql_db->prepare("DELETE FROM AA_Formaciones_actas_presentes WHERE id_acta=?");
  if (!$stDel) throw new Exception('SQL prepare error: '.$mysql_db->error);
  $stDel->bind_param('i', $idActa);
  if (!$stDel->execute()) throw new Exception('SQL execute error: '.$stDel->error);
  $stDel->close();

  if (count($presentesClean) > 0) {
    $stIns = $mysql_db->prepare("INSERT INTO AA_Formaciones_actas_presentes (id_acta, id_usuario) VALUES (?, ?)");
    if (!$stIns) throw new Exception('SQL prepare error: '.$mysql_db->error);

    foreach ($presentesClean as $uid) {
      $uid = (int)$uid;
      $stIns->bind_param('ii', $idActa, $uid);
      if (!$stIns->execute()) throw new Exception('SQL execute error: '.$stIns->error);
    }
    $stIns->close();
  }

  // 3) incidencias: insert ONLY las nuevas (las que vienen en payload)
  // Si quieres “editar/borrar” incidencias antiguas, lo hacemos luego; por ahora: se agregan con fecha.
  if (count($incClean) > 0) {
    $stInc = $mysql_db->prepare("
      INSERT INTO AA_Formaciones_actas_incidencias (id_acta, incidencia, created_by)
      VALUES (?, ?, ?)
    ");
    if (!$stInc) throw new Exception('SQL prepare error: '.$mysql_db->error);

    foreach ($incClean as $txt) {
      $stInc->bind_param('isi', $idActa, $txt, $idCreatedBy);
      if (!$stInc->execute()) throw new Exception('SQL execute error: '.$stInc->error);
    }
    $stInc->close();
  }

  $mysql_db->commit();
  out(true, ['id_acta'=>$idActa]);

} catch (Throwable $e) {
  $mysql_db->rollback();
  out(false, null, $e->getMessage(), 500);
}
