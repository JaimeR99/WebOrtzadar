<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('informacion_orientacion.edit');

function out($ok, $data=null, $error=null, $code=200){
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$persona   = trim((string)($_GET['persona'] ?? ''));
$municipio = trim((string)($_GET['municipio'] ?? ''));
$desde     = trim((string)($_GET['desde'] ?? ''));
$hasta     = trim((string)($_GET['hasta'] ?? ''));

$sql = "
  SELECT
    u.id,
    u.Nombre, u.Apellidos, u.Dni,
    u.Telefono_Usuario, u.Correo, u.Direccion,
    io.fecha AS fecha_io,
    io.tipo_demanda,
    io.lugar_entrevista
  FROM AA_informacion_y_orientacion io
  JOIN AA_usuarios u ON u.id = COALESCE(io.id_usuario, io.id)
  WHERE 1=1
";
$params = [];
$types = "";

if ($persona !== '') {
  $sql .= " AND (CONCAT(COALESCE(u.Nombre,''),' ',COALESCE(u.Apellidos,'')) LIKE ? OR COALESCE(u.Dni,'') LIKE ?)";
  $like = "%$persona%";
  $params[] = $like; $types .= "s";
  $params[] = $like; $types .= "s";
}
if ($municipio !== '') {
  $sql .= " AND COALESCE(u.Direccion,'') LIKE ?";
  $params[] = "%$municipio%"; $types .= "s";
}
if ($desde !== '') {
  $sql .= " AND io.fecha >= ?";
  $params[] = $desde . " 00:00:00"; $types .= "s";
}
if ($hasta !== '') {
  $sql .= " AND io.fecha <= ?";
  $params[] = $hasta . " 23:59:59"; $types .= "s";
}

$sql .= " ORDER BY io.fecha DESC LIMIT 200";

$stmt = $mysql_db->prepare($sql);
if (!$stmt) out(false, null, 'Prepare failed', 500);

if ($types !== '') $stmt->bind_param($types, ...$params);
if (!$stmt->execute()) out(false, null, 'Execute failed', 500);

$res = $stmt->get_result();
$rows = [];
while ($r = $res->fetch_assoc()) {
  $nombre = trim(($r['Nombre'] ?? '') . ' ' . ($r['Apellidos'] ?? ''));
  $rows[] = [
    'id_usuario' => (int)$r['id'],
    'nombre' => $nombre,
    'dni' => $r['Dni'] ?? '',
    'telefono' => $r['Telefono_Usuario'] ?? '',
    'email' => $r['Correo'] ?? '',
    'direccion' => $r['Direccion'] ?? '',
    'fecha_io' => $r['fecha_io'],
    'tipo_demanda' => $r['tipo_demanda'] ?? '',
    'lugar_entrevista' => $r['lugar_entrevista'] ?? ''
  ];
}
out(true, $rows);
