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

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) out(false, null, 'id inválido', 400);

$sql = "SELECT id, nombre, fecha, id_tipo, id_integrador FROM AA_Formaciones WHERE id = ? LIMIT 1";
$stmt = $mysql_db->prepare($sql);
if (!$stmt) out(false, null, 'SQL prepare error: '.$mysql_db->error, 500);

$stmt->bind_param('i', $id);
if (!$stmt->execute()) out(false, null, 'SQL execute error: '.$stmt->error, 500);

$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$row) out(false, null, 'No encontrado', 404);

out(true, [
  'id' => (int)$row['id'],
  'nombre' => $row['nombre'],
  'fecha' => $row['fecha'],
  'id_tipo' => $row['id_tipo'] !== null ? (int)$row['id_tipo'] : null,
  'id_integrador' => $row['id_integrador'] !== null ? (int)$row['id_integrador'] : null,
]);
