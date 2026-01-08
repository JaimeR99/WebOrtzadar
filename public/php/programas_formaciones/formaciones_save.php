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

$id = (int)($body['id'] ?? 0);
$nombre = trim((string)($body['nombre'] ?? ''));
$fecha = $body['fecha'] ?? null;

$id_tipo = $body['id_tipo'] ?? null;
$id_integrador = $body['id_integrador'] ?? null;

if ($nombre === '') out(false, null, 'nombre obligatorio', 400);
if ($fecha !== null && !is_string($fecha)) out(false, null, 'fecha inválida', 400);

$id_tipo = ($id_tipo === null || $id_tipo === '' ? null : (int)$id_tipo);
$id_integrador = ($id_integrador === null || $id_integrador === '' ? null : (int)$id_integrador);

if ($id > 0) {
  $sql = "UPDATE AA_Formaciones SET nombre=?, fecha=?, id_tipo=?, id_integrador=? WHERE id=?";
  $stmt = $mysql_db->prepare($sql);
  if (!$stmt) out(false, null, 'SQL prepare error: '.$mysql_db->error, 500);

  // bind nulls
  $stmt->bind_param('ssiii',
    $nombre,
    $fecha,
    $id_tipo,
    $id_integrador,
    $id
  );

  // si fecha es null, forzamos null (mysqli no lo hace bien con 's')
  // solución: si fecha es null, actualizamos aparte
  if ($fecha === null) {
    $stmt->close();
    $sql2 = "UPDATE AA_Formaciones SET nombre=?, fecha=NULL, id_tipo=?, id_integrador=? WHERE id=?";
    $stmt2 = $mysql_db->prepare($sql2);
    if (!$stmt2) out(false, null, 'SQL prepare error: '.$mysql_db->error, 500);
    $stmt2->bind_param('siii', $nombre, $id_tipo, $id_integrador, $id);
    if (!$stmt2->execute()) out(false, null, 'SQL execute error: '.$stmt2->error, 500);
    $stmt2->close();
    out(true, ['id'=>$id]);
  }

  if (!$stmt->execute()) out(false, null, 'SQL execute error: '.$stmt->error, 500);
  $stmt->close();
  out(true, ['id'=>$id]);
}

// INSERT
if ($fecha === null) {
  $sql = "INSERT INTO AA_Formaciones (nombre, fecha, id_tipo, id_integrador) VALUES (?, NULL, ?, ?)";
  $stmt = $mysql_db->prepare($sql);
  if (!$stmt) out(false, null, 'SQL prepare error: '.$mysql_db->error, 500);
  $stmt->bind_param('sii', $nombre, $id_tipo, $id_integrador);
} else {
  $sql = "INSERT INTO AA_Formaciones (nombre, fecha, id_tipo, id_integrador) VALUES (?, ?, ?, ?)";
  $stmt = $mysql_db->prepare($sql);
  if (!$stmt) out(false, null, 'SQL prepare error: '.$mysql_db->error, 500);
  $stmt->bind_param('ssii', $nombre, $fecha, $id_tipo, $id_integrador);
}

if (!$stmt->execute()) out(false, null, 'SQL execute error: '.$stmt->error, 500);
$newId = (int)$stmt->insert_id;
$stmt->close();

out(true, ['id'=>$newId]);
