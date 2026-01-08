<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('viviendas.view');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$idV = (int)($_GET['id_vivienda'] ?? 0);
$q   = trim((string)($_GET['q'] ?? ''));
if ($idV <= 0) out(false, null, 'id_vivienda inválido', 400);

try {
  $like = '%' . $q . '%';

  // Vivienda actual del usuario (si está en alguna), eligiendo una (MIN) para evitar duplicados.
  $sql = "
    SELECT
      u.id, u.Nombre, u.Apellidos, u.Dni,
      cur.id_vivienda AS vivienda_actual_id,
      v.nombre        AS vivienda_actual_nombre,
      v.localidad     AS vivienda_actual_localidad
    FROM AA_usuarios u
    LEFT JOIN (
      SELECT id_usuario, MIN(id_vivienda) AS id_vivienda
      FROM AA_vivienda_participantes
      GROUP BY id_usuario
    ) cur ON cur.id_usuario = u.id
    LEFT JOIN AA_viviendas v ON v.id = cur.id_vivienda
    WHERE
      NOT EXISTS (
        SELECT 1
        FROM AA_vivienda_participantes vp
        WHERE vp.id_usuario = u.id AND vp.id_vivienda = ?
      )
      AND (
        ? = '' OR
        u.Nombre LIKE ? OR u.Apellidos LIKE ? OR u.Dni LIKE ? OR
        CONCAT(COALESCE(u.Nombre,''),' ',COALESCE(u.Apellidos,'')) LIKE ?
      )
    ORDER BY u.Apellidos ASC, u.Nombre ASC
    LIMIT 250
  ";

  $st = $mysql_db->prepare($sql);
  $st->bind_param('isssss', $idV, $q, $like, $like, $like, $like);
  $st->execute();
  $rs = $st->get_result();

  $rows = [];
  while ($r = $rs->fetch_assoc()) {
    $rows[] = [
      'id' => (int)$r['id'],
      'Nombre' => $r['Nombre'],
      'Apellidos' => $r['Apellidos'],
      'Dni' => $r['Dni'],
      'vivienda_actual_id' => $r['vivienda_actual_id'] !== null ? (int)$r['vivienda_actual_id'] : null,
      'vivienda_actual_nombre' => $r['vivienda_actual_nombre'] ?? null,
      'vivienda_actual_localidad' => $r['vivienda_actual_localidad'] ?? null,
    ];
  }

  out(true, $rows);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
