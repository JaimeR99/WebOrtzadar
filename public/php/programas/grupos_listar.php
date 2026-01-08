<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$grupo = trim((string)($_GET['grupo'] ?? ''));
$anio  = trim((string)($_GET['anio'] ?? ''));
$responsable = trim((string)($_GET['responsable'] ?? ''));

// WHERE dinámico
$where = [];
$params = [];
$types = '';

if ($grupo !== '') {
  $where[] = "g.nombre LIKE ?";
  $types .= 's';
  $params[] = "%{$grupo}%";
}

if ($anio !== '' && preg_match('/^\d{4}$/', $anio)) {
  // filtramos por año de la fecha del grupo (campo nuevo que me dijiste)
  $where[] = "YEAR(g.fecha) = ?";
  $types .= 'i';
  $params[] = (int)$anio;
}

if ($responsable !== '') {
  $where[] = "(t.nombre LIKE ? OR t.apellidos LIKE ? OR CONCAT_WS(' ', t.nombre, t.apellidos) LIKE ?)";
  $types .= 'sss';
  $like = "%{$responsable}%";
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
}

$sql = "
  SELECT
    g.id,
    g.nombre,
    g.fecha,
    YEAR(g.fecha) AS anio,
    g.id_responsable,
    CONCAT_WS(' ', t.nombre, t.apellidos) AS responsable_nombre
  FROM AA_Ocio_grupos g
  LEFT JOIN AA_trabajadores t ON t.id = g.id_responsable
";

if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY g.fecha DESC, g.id DESC";

try {
  $stmt = $mysql_db->prepare($sql);

  if ($params) {
    // bind_param necesita variables por referencia
    $bind = [];
    $bind[] = $types;
    foreach ($params as $k => $v) $bind[] = &$params[$k];
    call_user_func_array([$stmt, 'bind_param'], $bind);
  }

  $stmt->execute();
  $rs = $stmt->get_result();

  $rows = [];
  while ($row = $rs->fetch_assoc()) {
    $rows[] = $row;
  }

  out(true, $rows);
} catch (Throwable $e) {
  out(false, null, 'Error listando grupos: ' . $e->getMessage(), 500);
}
