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

$id_grupo = isset($_GET['id_grupo']) ? (int)$_GET['id_grupo'] : 0;
if ($id_grupo <= 0) out(false, null, 'id_grupo inválido', 422);

try {
  $sql = "
    SELECT a.id, a.fecha, a.valoracion,
      (SELECT COUNT(*) FROM AA_Ocio_asistencia s WHERE s.id_acta=a.id) AS n_presentes,
      (SELECT COUNT(*) FROM AA_Ocio_incidencias i WHERE i.id_acta=a.id) AS n_incidencias
    FROM AA_Ocio_actas a
    WHERE a.id_grupo=?
    ORDER BY a.fecha DESC, a.id DESC
    LIMIT 50
  ";
  $st = $mysql_db->prepare($sql);
  $st->bind_param("i", $id_grupo);
  $st->execute();
  $rs = $st->get_result();

  $rows = [];
  while ($r = $rs->fetch_assoc()) {
    $rows[] = [
      'id' => (int)$r['id'],
      'fecha' => $r['fecha'],
      'valoracion' => $r['valoracion'],
      'n_presentes' => (int)$r['n_presentes'],
      'n_incidencias' => (int)$r['n_incidencias'],
    ];
  }

  out(true, $rows);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
