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

$q = trim((string)($_GET['q'] ?? ''));
$anio = trim((string)($_GET['anio'] ?? ''));
$tipo = (int)($_GET['tipo'] ?? 0);
$integrador = (int)($_GET['integrador'] ?? 0);

$where = [];
$params = [];
$types  = '';

if ($q !== '') { $where[] = "f.nombre LIKE ?"; $params[] = "%$q%"; $types .= 's'; }
if ($anio !== '' && preg_match('/^\d{4}$/', $anio)) {
  $where[] = "YEAR(f.fecha) = ?";
  $params[] = (int)$anio; $types .= 'i';
}
if ($tipo > 0) { $where[] = "f.id_tipo = ?"; $params[] = $tipo; $types .= 'i'; }
if ($integrador > 0) { $where[] = "f.id_integrador = ?"; $params[] = $integrador; $types .= 'i'; }

$sql = "
  SELECT
    f.id, f.nombre, f.fecha, f.id_tipo, t.nombre AS tipo_nombre,
    f.id_integrador,
    CONCAT_WS(' ', tr.nombre, tr.apellidos) AS integrador
  FROM AA_Formaciones f
  LEFT JOIN AA_Formaciones_tipos t ON t.id = f.id_tipo
  LEFT JOIN AA_trabajadores tr ON tr.id = f.id_integrador
";

if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY f.fecha DESC, f.id DESC";

$stmt = $mysql_db->prepare($sql);
if (!$stmt) out(false, null, 'SQL prepare error: '.$mysql_db->error, 500);

if ($params) {
  $stmt->bind_param($types, ...$params);
}

if (!$stmt->execute()) out(false, null, 'SQL execute error: '.$stmt->error, 500);
$res = $stmt->get_result();

$rows = [];
while ($r = $res->fetch_assoc()) {
  $rows[] = [
    'id' => (int)$r['id'],
    'nombre' => $r['nombre'],
    'fecha' => $r['fecha'],
    'id_tipo' => $r['id_tipo'] !== null ? (int)$r['id_tipo'] : null,
    'tipo' => $r['tipo_nombre'] ?? null,
    'id_integrador' => $r['id_integrador'] !== null ? (int)$r['id_integrador'] : null,
    'integrador' => $r['integrador'] ?? null,
  ];
}

$stmt->close();
out(true, $rows);
