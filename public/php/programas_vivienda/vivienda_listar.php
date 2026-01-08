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

try {
  $sql = "
    SELECT
      v.id, v.nombre, v.localidad, v.direccion,
      v.responsable_programa AS responsable_id,
      v.integrador AS integrador_id,
      CONCAT_WS(' ', tr.nombre, tr.apellidos) AS responsable_nombre,
      CONCAT_WS(' ', ti.nombre, ti.apellidos) AS integrador_nombre
    FROM AA_viviendas v
    LEFT JOIN AA_trabajadores tr ON tr.id = v.responsable_programa
    LEFT JOIN AA_trabajadores ti ON ti.id = v.integrador
    ORDER BY v.id ASC
  ";

  $rows = [];
  $res = $mysql_db->query($sql);
  while ($r = $res->fetch_assoc()) {
    $rows[] = [
      'id' => (int)$r['id'],
      'nombre' => $r['nombre'],
      'localidad' => $r['localidad'],
      'direccion' => $r['direccion'],
      'responsable_programa' => $r['responsable_id'] !== null ? (int)$r['responsable_id'] : null,
      'integrador' => $r['integrador_id'] !== null ? (int)$r['integrador_id'] : null,
      'responsable_nombre' => $r['responsable_nombre'] ?: '',
      'integrador_nombre' => $r['integrador_nombre'] ?: '',
    ];
  }
  out(true, $rows);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
