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

$idFormacion = (int)($_GET['id_formacion'] ?? 0);
if ($idFormacion <= 0) out(false, null, 'id_formacion inválido', 400);

$sql = "
  SELECT u.id, u.Nombre, u.Apellidos, u.Dni
  FROM AA_Formaciones_participantes p
  INNER JOIN AA_usuarios u ON u.id = p.id_usuario
  WHERE p.id_formacion = ?
  ORDER BY u.Apellidos ASC, u.Nombre ASC
";

$stmt = $mysql_db->prepare($sql);
if (!$stmt) out(false, null, 'SQL prepare error: '.$mysql_db->error, 500);

$stmt->bind_param('i', $idFormacion);
if (!$stmt->execute()) out(false, null, 'SQL execute error: '.$stmt->error, 500);

$res = $stmt->get_result();
$rows = [];
while ($row = $res->fetch_assoc()) {
  $uid = (int)$row['id'];
  $rows[] = [
    'id' => $uid,
    'Nombre' => $row['Nombre'],
    'Apellidos' => $row['Apellidos'],
    'Dni' => $row['Dni'],
    'foto_url' => '/webOrtzadar/uploads/usuarios/' . $uid . '.jpg'
  ];
}
$stmt->close();
out(true, $rows);
