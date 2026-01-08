<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('vida_independiente.view');


mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data = null, ?string $error = null, int $code = 200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) out(false, null, 'ID inválido', 400);

try {
  $stmtU = $mysql_db->prepare("SELECT * FROM AA_usuarios WHERE id=? LIMIT 1");
  $stmtU->bind_param("i", $id);
  $stmtU->execute();
  $usuario = $stmtU->get_result()->fetch_assoc();
  if (!$usuario) out(false, null, 'Usuario no encontrado', 404);

  $diag = [
    'discapacidad' => null,
    'dependencia'  => null,
    'exclusion'    => null
  ];

  if (!empty($usuario['ID_DIAG_Discapacidad'])) {
    $did = (int)$usuario['ID_DIAG_Discapacidad'];
    $st = $mysql_db->prepare("SELECT * FROM AA_Discapacidad WHERE Id=? LIMIT 1");
    $st->bind_param("i", $did);
    $st->execute();
    $diag['discapacidad'] = $st->get_result()->fetch_assoc() ?: null;
  }

  if (!empty($usuario['ID_DIAG_Dependencia'])) {
    $did = (int)$usuario['ID_DIAG_Dependencia'];
    $st = $mysql_db->prepare("SELECT * FROM AA_Dependencia WHERE Id=? LIMIT 1");
    $st->bind_param("i", $did);
    $st->execute();
    $diag['dependencia'] = $st->get_result()->fetch_assoc() ?: null;
  }

  if (!empty($usuario['ID_DIAG_Exclusion'])) {
    $eid = (int)$usuario['ID_DIAG_Exclusion'];
    $st = $mysql_db->prepare("SELECT * FROM AA_Exclusion WHERE Id=? LIMIT 1");
    $st->bind_param("i", $eid);
    $st->execute();
    $diag['exclusion'] = $st->get_result()->fetch_assoc() ?: null;
  }

  out(true, ['usuario'=>$usuario, 'diag'=>$diag]);
} catch (mysqli_sql_exception $e) {
  out(false, null, 'SQL ERROR: ' . $e->getMessage(), 500);
} catch (Throwable $e) {
  out(false, null, 'Error: ' . $e->getMessage(), 500);
}
