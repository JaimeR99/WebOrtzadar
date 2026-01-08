<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('formaciones.view');

function out($ok, $data=null, $error=null, $code=200){
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$sql = "SELECT id, nombre FROM AA_Formaciones_tipos ORDER BY nombre ASC";
$res = $mysql_db->query($sql);
if (!$res) out(false, null, 'SQL error: '.$mysql_db->error, 500);

$rows = [];
while ($r = $res->fetch_assoc()) {
  $rows[] = ['id'=>(int)$r['id'], 'nombre'=>$r['nombre']];
}
out(true, $rows);
