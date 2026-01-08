<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('ocio.view');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$grupo = trim((string)($_GET['grupo'] ?? ''));
$anio  = trim((string)($_GET['anio'] ?? ''));
$resp  = trim((string)($_GET['responsable'] ?? ''));

try {
  $sql = "
    SELECT
      g.id,
      g.nombre,
      g.fecha,
      g.id_responsable,
      CONCAT(COALESCE(t.nombre,''), ' ', COALESCE(t.apellidos,'')) AS responsable_nombre
    FROM AA_Ocio_grupos g
    LEFT JOIN AA_trabajadores t ON t.id = g.id_responsable
    WHERE 1=1
  ";

  $types = '';
  $params = [];

  if ($grupo !== '') {
    $sql .= " AND g.nombre LIKE ? ";
    $types .= 's';
    $params[] = '%' . $grupo . '%';
  }

  if ($anio !== '' && ctype_digit($anio)) {
    $sql .= " AND YEAR(g.fecha) = ? ";
    $types .= 'i';
    $params[] = (int)$anio;
  }

  if ($resp !== '') {
    $sql .= " AND CONCAT(COALESCE(t.nombre,''), ' ', COALESCE(t.apellidos,'')) LIKE ? ";
    $types .= 's';
    $params[] = '%' . $resp . '%';
  }

  $sql .= " ORDER BY g.fecha DESC, g.nombre ASC ";

  $stmt = $mysql_db->prepare($sql);

  if ($types !== '') {
    // bind_param necesita referencias
    $bind = [];
    $bind[] = $types;
    for ($i=0; $i<count($params); $i++) {
      $bind[] = &$params[$i];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind);
  }

  $stmt->execute();
  $res = $stmt->get_result();

  $rows = [];
  while ($r = $res->fetch_assoc()) {
    $rows[] = [
      'id' => (int)$r['id'],
      'nombre' => $r['nombre'],
      'fecha' => $r['fecha'],
      'anio' => $r['fecha'] ? (int)substr($r['fecha'], 0, 4) : null,
      'id_responsable' => $r['id_responsable'] !== null ? (int)$r['id_responsable'] : null,
      'responsable' => trim((string)$r['responsable_nombre']) ?: null,
    ];
  }

  out(true, $rows);

} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
