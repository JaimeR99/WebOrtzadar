<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('vida_independiente.view');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$idU = (int)($_GET['id_usuario'] ?? 0);
if ($idU <= 0) out(false, null, 'id_usuario inválido', 400);

try {
  // Usuario (columnas reales)
  $stU = $mysql_db->prepare("
    SELECT id, Nombre, Apellidos, Dni, Direccion, Telefono_Usuario
    FROM AA_usuarios
    WHERE id=?
  ");
  $stU->bind_param('i', $idU);
  $stU->execute();
  $u = $stU->get_result()->fetch_assoc();
  if (!$u) out(false, null, 'Usuario no encontrado', 404);

  // Programa (responsable/integrador guardan ID del trabajador)
  $stP = $mysql_db->prepare("
    SELECT id_usuario, responsable, integrador
    FROM AA_vida_independiente_participantes
    WHERE id_usuario=?
  ");
  $stP->bind_param('i', $idU);
  $stP->execute();
  $p = $stP->get_result()->fetch_assoc();
  if (!$p) $p = ['id_usuario'=>$idU,'responsable'=>'','integrador'=>''];

  // Normalizamos a int (si vienen como string)
  $idResp = (int)($p['responsable'] ?? 0);
  $idInt  = (int)($p['integrador'] ?? 0);

  $resp = null;
  if ($idResp > 0) {
    $st = $mysql_db->prepare("SELECT id, nombre, apellidos, id_puesto FROM AA_trabajadores WHERE id=? LIMIT 1");
    $st->bind_param('i', $idResp);
    $st->execute();
    $resp = $st->get_result()->fetch_assoc() ?: null;
  }

  $integ = null;
  if ($idInt > 0) {
    $st = $mysql_db->prepare("SELECT id, nombre, apellidos, id_puesto FROM AA_trabajadores WHERE id=? LIMIT 1");
    $st->bind_param('i', $idInt);
    $st->execute();
    $integ = $st->get_result()->fetch_assoc() ?: null;
  }

  // Revisiones
  $stR = $mysql_db->prepare("
    SELECT id, equipamiento, fecha_revision, proxima_revision, observaciones, avisar, dias_antes_aviso
    FROM AA_vida_independiente_revisiones
    WHERE id_usuario=?
    ORDER BY fecha_revision DESC, id DESC
  ");
  $stR->bind_param('i', $idU);
  $stR->execute();
  $revs = $stR->get_result()->fetch_all(MYSQLI_ASSOC);

  // Devolvemos IDs normalizados
  $p['responsable'] = $idResp > 0 ? (string)$idResp : '';
  $p['integrador']  = $idInt  > 0 ? (string)$idInt  : '';

  out(true, [
    'usuario' => $u,
    'programa' => $p,
    'responsable_trabajador' => $resp,
    'integrador_trabajador' => $integ,
    'revisiones' => $revs
  ]);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
