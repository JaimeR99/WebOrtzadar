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

// Puestos
const PUESTO_TRABAJADOR  = 1;
const PUESTO_RESPONSABLE = 2;
const PUESTO_ADMIN       = 3;

$body = json_decode(file_get_contents('php://input'), true) ?: [];

$idU = (int)($body['id_usuario'] ?? 0);
// responsable/integrador SON IDs
$idResp = (int)($body['responsable'] ?? 0);
$idInt  = (int)($body['integrador'] ?? 0);

if ($idU <= 0) out(false, null, 'id_usuario inválido', 400);
if ($idResp <= 0 || $idInt <= 0) out(false, null, 'Responsable e integrador son obligatorios', 400);

try {
  // validar responsable
  $stR = $mysql_db->prepare("SELECT id, id_puesto FROM AA_trabajadores WHERE id=? LIMIT 1");
  $stR->bind_param('i', $idResp);
  $stR->execute();
  $rowR = $stR->get_result()->fetch_assoc();
  if (!$rowR) out(false, null, 'Responsable no existe', 400);

  $puestoR = (int)$rowR['id_puesto'];
  if (!in_array($puestoR, [PUESTO_RESPONSABLE, PUESTO_ADMIN], true)) {
    out(false, null, 'El responsable debe ser Responsable o Administrador (id_puesto 2 o 3)', 400);
  }

  // validar integrador
  $stI = $mysql_db->prepare("SELECT id, id_puesto FROM AA_trabajadores WHERE id=? LIMIT 1");
  $stI->bind_param('i', $idInt);
  $stI->execute();
  $rowI = $stI->get_result()->fetch_assoc();
  if (!$rowI) out(false, null, 'Integrador no existe', 400);

  $puestoI = (int)$rowI['id_puesto'];
  if ($puestoI !== PUESTO_TRABAJADOR) {
    out(false, null, 'El integrador debe ser Trabajador (id_puesto 1)', 400);
  }

  // guardar ids (en tus columnas responsable/integrador)
  $st = $mysql_db->prepare("
    INSERT INTO AA_vida_independiente_participantes (id_usuario, responsable, integrador)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE responsable=VALUES(responsable), integrador=VALUES(integrador)
  ");
  $st->bind_param('iii', $idU, $idResp, $idInt);
  $st->execute();

  out(true, ['id_usuario'=>$idU, 'responsable'=>$idResp, 'integrador'=>$idInt]);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
