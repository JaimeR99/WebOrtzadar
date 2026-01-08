<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('vacaciones.view');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$destino = trim((string)($_GET['destino'] ?? ''));
$anio    = trim((string)($_GET['anio'] ?? ''));
$resp    = trim((string)($_GET['responsable'] ?? ''));
$integ   = trim((string)($_GET['integrador'] ?? ''));

try {
  $sql = "
    SELECT
      d.id,
      d.nombre,
      d.fecha,
      d.id_responsable,
      d.id_integrador,
      CONCAT(COALESCE(tr.nombre,''), ' ', COALESCE(tr.apellidos,'')) AS responsable_nombre,
      CONCAT(COALESCE(ti.nombre,''), ' ', COALESCE(ti.apellidos,'')) AS integrador_nombre
    FROM AA_Vacaciones_destinos d
    LEFT JOIN AA_trabajadores tr ON tr.id = d.id_responsable
    LEFT JOIN AA_trabajadores ti ON ti.id = d.id_integrador
    WHERE 1=1
  ";

  $types = '';
  $params = [];

  if ($destino !== '') { $sql .= " AND d.nombre LIKE ? "; $types.='s'; $params[]='%'.$destino.'%'; }
  if ($anio !== '' && ctype_digit($anio)) { $sql .= " AND YEAR(d.fecha) = ? "; $types.='i'; $params[]=(int)$anio; }
  if ($resp !== '') { $sql .= " AND d.id_responsable = ? "; $types.='i'; $params[]=(int)$resp; }
  if ($integ !== '') { $sql .= " AND d.id_integrador = ? "; $types.='i'; $params[]=(int)$integ; }

  $sql .= " ORDER BY d.fecha DESC, d.id DESC ";

  $stmt = $mysql_db->prepare($sql);
  if ($types !== '') $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $res = $stmt->get_result();

  $rows = [];
  while ($r = $res->fetch_assoc()) {
    $rows[] = [
      'id' => (int)$r['id'],
      'nombre' => $r['nombre'],
      'fecha' => $r['fecha'],
      'anio' => $r['fecha'] ? (int)substr((string)$r['fecha'], 0, 4) : null,
      'id_responsable' => $r['id_responsable'] !== null ? (int)$r['id_responsable'] : null,
      'id_integrador' => $r['id_integrador'] !== null ? (int)$r['id_integrador'] : null,
      'responsable' => trim((string)$r['responsable_nombre']) ?: null,
      'integrador' => trim((string)$r['integrador_nombre']) ?: null,
    ];
  }

  out(true, $rows);
} catch (Throwable $e) {
  out(false, null, $e->getMessage(), 500);
}
