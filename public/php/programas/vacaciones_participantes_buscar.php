<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('vacaciones.view');
function out($ok, $data=null, $error=null, $code=200){
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$q = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($q) < 2) out(true, []);

// Búsqueda por Nombre/Apellidos/Dni
$like = '%'.$q.'%';

// Ajusta tabla/campos a tu esquema real
$sql = "
  SELECT id, Nombre, Apellidos, Dni
  FROM AA_usuarios
  WHERE Nombre LIKE ? OR Apellidos LIKE ? OR Dni LIKE ?
  ORDER BY Apellidos ASC, Nombre ASC
  LIMIT 20
";

$stmt = $mysql_db->prepare($sql);
if (!$stmt) out(false, null, 'SQL prepare error: '.$mysql_db->error, 500);

$stmt->bind_param('sss', $like, $like, $like);
if (!$stmt->execute()) out(false, null, 'SQL execute error: '.$stmt->error, 500);

$res = $stmt->get_result();
$rows = [];
while ($row = $res->fetch_assoc()) {
  $rows[] = [
    'id' => (int)$row['id'],
    'Nombre' => $row['Nombre'],
    'Apellidos' => $row['Apellidos'],
    'Dni' => $row['Dni'],
    'foto_url' => '/webOrtzadar/uploads/usuarios/' . $row['id'] . '.jpg',
  ];
}

$stmt->close();

out(true, $rows);
