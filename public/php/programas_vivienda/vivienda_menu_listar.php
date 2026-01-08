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

$id_vivienda = (int)($_GET['id_vivienda'] ?? 0);
if ($id_vivienda <= 0) out(false, null, 'id_vivienda inválido', 400);

try {
  $sql = "
    SELECT
      m.id, m.fecha, m.responsable,
      CONCAT_WS(' ', t.nombre, t.apellidos) AS responsable_nombre,
      m.lunes, m.martes, m.miercoles, m.jueves, m.viernes, m.sabado, m.domingo
    FROM AA_vivienda_menu_semanal m
    LEFT JOIN AA_trabajadores t ON t.id = m.responsable
    WHERE m.id_vivienda=?
    ORDER BY m.fecha DESC, m.id DESC
  ";
  $st = $mysql_db->prepare($sql);
  $st->bind_param('i', $id_vivienda);
  $st->execute();

  $rows = [];
  $rs = $st->get_result();
  while ($r = $rs->fetch_assoc()) {
    $rows[] = [
      'id' => (int)$r['id'],
      'fecha' => $r['fecha'],
      'responsable' => $r['responsable'] !== null ? (int)$r['responsable'] : null,
      'responsable_nombre' => $r['responsable_nombre'] ?? '',
      'lunes' => $r['lunes'], 'martes' => $r['martes'], 'miercoles' => $r['miercoles'],
      'jueves' => $r['jueves'], 'viernes' => $r['viernes'], 'sabado' => $r['sabado'], 'domingo' => $r['domingo'],
    ];
  }
  out(true, $rows);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
