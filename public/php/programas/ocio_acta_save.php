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

$id_acta = (int)($body['id_acta'] ?? 0);
$id_grupo = (int)($body['id_grupo'] ?? 0);
$fecha = trim((string)($body['fecha'] ?? ''));
$valoracion = (string)($body['valoracion'] ?? '');
$presentes = $body['presentes'] ?? [];
$incidencias = $body['incidencias'] ?? [];

if ($id_grupo <= 0) out(false, null, 'id_grupo inválido', 422);
if ($fecha === '') out(false, null, 'Fecha es obligatoria', 422);

// normaliza fecha
$fecha = str_replace('T', ' ', $fecha);
if (strlen($fecha) === 16) $fecha .= ':00';

// normaliza arrays
if (!is_array($presentes)) $presentes = [];
$presentes = array_values(array_unique(array_map('intval', $presentes)));

if (!is_array($incidencias)) $incidencias = [];
$incidencias = array_values(array_filter(array_map(fn($x) => trim((string)$x), $incidencias), fn($t) => $t !== ''));

try {
  $mysql_db->begin_transaction();

  // crea o actualiza acta
  if ($id_acta > 0) {
    $up = $mysql_db->prepare("
      UPDATE AA_Ocio_actas
      SET fecha=?, valoracion=?
      WHERE id=? AND id_grupo=?
      LIMIT 1
    ");
    $up->bind_param("ssii", $fecha, $valoracion, $id_acta, $id_grupo);
    $up->execute();

  } else {
    $ins = $mysql_db->prepare("
      INSERT INTO AA_Ocio_actas (id_grupo, fecha, valoracion, id_created_by)
      VALUES (?, ?, ?, NULL)
    ");
    $ins->bind_param("iss", $id_grupo, $fecha, $valoracion);
    $ins->execute();
    $id_acta = (int)$mysql_db->insert_id;
  }

  // asistencia: borramos e insertamos presentes (fila = asistió)
  $delAs = $mysql_db->prepare("DELETE FROM AA_Ocio_asistencia WHERE id_acta=?");
  $delAs->bind_param("i", $id_acta);
  $delAs->execute();

  if (count($presentes) > 0) {
    $insAs = $mysql_db->prepare("INSERT INTO AA_Ocio_asistencia (id_usuario, id_acta) VALUES (?, ?)");
    foreach ($presentes as $id_usuario) {
      if ($id_usuario <= 0) continue;
      $id_usuario = (int)$id_usuario;
      $insAs->bind_param("ii", $id_usuario, $id_acta);
      $insAs->execute();
    }
  }

  // incidencias: insertamos nuevas (no borramos historial)
  if (count($incidencias) > 0) {
    $now = date('Y-m-d H:i:s');
    $insI = $mysql_db->prepare("
      INSERT INTO AA_Ocio_incidencias (id_acta, fecha, incidencia, id_created_by)
      VALUES (?, ?, ?, NULL)
    ");
    foreach ($incidencias as $txt) {
      $insI->bind_param("iss", $id_acta, $now, $txt);
      $insI->execute();
    }
  }

  $mysql_db->commit();
  out(true, ['id_acta' => $id_acta]);

} catch (Throwable $e) {
  $mysql_db->rollback();
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
