<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('vacaciones.edit');
function out($ok, $data=null, $error=null, $code=200){
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) out(false, null, 'Body JSON inválido', 400);

$idActa    = (int)($body['id_acta'] ?? 0);
$idDestino = (int)($body['id_destino'] ?? 0);
$fecha     = $body['fecha'] ?? null;
$valoracion= (string)($body['valoracion'] ?? '');
$presentes = $body['presentes'] ?? [];
$incidencias = $body['incidencias'] ?? [];

if ($idDestino <= 0) out(false, null, 'id_destino inválido', 400);

// ⚠️ AJUSTA ESTO A TU SISTEMA DE LOGIN
$idCreatedBy = isset($_SESSION['id_trabajador']) ? (int)$_SESSION['id_trabajador'] : null;

$mysql_db->begin_transaction();

try {
  // 1) INSERT/UPDATE ACTA
  if ($idActa > 0) {
    $sql = "UPDATE AA_Vacaciones_actas
            SET fecha = ?, valoracion = ?, id_created_by = COALESCE(?, id_created_by)
            WHERE id = ? AND id_destino = ?";
    $stmt = $mysql_db->prepare($sql);
    if (!$stmt) throw new Exception('SQL prepare error: '.$mysql_db->error);
    $stmt->bind_param('ssiii', $fecha, $valoracion, $idCreatedBy, $idActa, $idDestino);
    if (!$stmt->execute()) throw new Exception('SQL execute error: '.$stmt->error);
    $stmt->close();
  } else {
    $sql = "INSERT INTO AA_Vacaciones_actas (id_destino, fecha, valoracion, id_created_by)
            VALUES (?, ?, ?, ?)";
    $stmt = $mysql_db->prepare($sql);
    if (!$stmt) throw new Exception('SQL prepare error: '.$mysql_db->error);
    $stmt->bind_param('issi', $idDestino, $fecha, $valoracion, $idCreatedBy);
    if (!$stmt->execute()) throw new Exception('SQL execute error: '.$stmt->error);
    $idActa = (int)$stmt->insert_id;
    $stmt->close();
  }

  // 2) ASISTENCIA (si usas AA_Vacaciones_asistencia)
  // Limpia e inserta presentes
  $del = $mysql_db->prepare("DELETE FROM AA_Vacaciones_asistencia WHERE id_acta=?");
  if (!$del) throw new Exception('SQL prepare error: '.$mysql_db->error);
  $del->bind_param('i', $idActa);
  if (!$del->execute()) throw new Exception('SQL execute error: '.$del->error);
  $del->close();

  if (is_array($presentes) && count($presentes)) {
    $ins = $mysql_db->prepare("INSERT INTO AA_Vacaciones_asistencia (id_usuario, id_acta) VALUES (?, ?)");
    if (!$ins) throw new Exception('SQL prepare error: '.$mysql_db->error);
    foreach ($presentes as $uid) {
      $uid = (int)$uid;
      if ($uid <= 0) continue;
      $ins->bind_param('ii', $uid, $idActa);
      if (!$ins->execute()) throw new Exception('SQL execute error: '.$ins->error);
    }
    $ins->close();
  }

  // 3) INCIDENCIAS (insertar SOLO nuevas con fecha NOW())
  if (is_array($incidencias) && count($incidencias)) {
    $insI = $mysql_db->prepare("
      INSERT INTO AA_Vacaciones_incidencias (id_acta, fecha, incidencia, id_created_by)
      VALUES (?, NOW(), ?, ?)
    ");
    if (!$insI) throw new Exception('SQL prepare error: '.$mysql_db->error);

    foreach ($incidencias as $txt) {
      $txt = trim((string)$txt);
      if ($txt === '') continue;

      $insI->bind_param('isi', $idActa, $txt, $idCreatedBy);
      if (!$insI->execute()) throw new Exception('SQL execute error: '.$insI->error);
    }
    $insI->close();
  }

  $mysql_db->commit();
  out(true, ['id_acta' => $idActa]);

} catch (Throwable $e) {
  $mysql_db->rollback();
  out(false, null, $e->getMessage(), 500);
}
