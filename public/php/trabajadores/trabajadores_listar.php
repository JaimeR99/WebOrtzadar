<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('trabajadores.view');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data = null, ?string $error = null, int $code = 200): void {
  http_response_code($code);
  echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  $sql = "
    SELECT
      t.id, t.nombre, t.apellidos, t.sexo, t.dni, t.fecha_nacimiento, t.direccion,
      t.telefono, t.email, t.cuenta_corriente, t.fecha_alta, t.id_puesto,
      p.nombre AS puesto_nombre, p.nivel AS puesto_nivel,
      a.correo AS acceso_correo
    FROM AA_trabajadores t
    LEFT JOIN AA_puestos p ON p.id = t.id_puesto
    LEFT JOIN AA_accesos a ON a.id_trabajador = t.id
    ORDER BY t.nombre ASC, t.apellidos ASC
    LIMIT 500
  ";

  $res = $mysql_db->query($sql);
  $rows = [];
  while ($r = $res->fetch_assoc()) {
    $rows[] = [
      'id' => (int)$r['id'],
      'nombre' => (string)($r['nombre'] ?? ''),
      'apellidos' => (string)($r['apellidos'] ?? ''),
      'sexo' => $r['sexo'] !== null ? (string)$r['sexo'] : null,
      'dni' => $r['dni'] !== null ? (string)$r['dni'] : null,
      'fecha_nacimiento' => $r['fecha_nacimiento'] !== null ? (string)$r['fecha_nacimiento'] : null,
      'direccion' => $r['direccion'] !== null ? (string)$r['direccion'] : null,
      'telefono' => (string)($r['telefono'] ?? ''),
      'email' => (string)($r['email'] ?? ''),
      'cuenta_corriente' => $r['cuenta_corriente'] !== null ? (string)$r['cuenta_corriente'] : null,
      'fecha_alta' => $r['fecha_alta'] ? (string)$r['fecha_alta'] : null,
      'id_puesto' => $r['id_puesto'] !== null ? (int)$r['id_puesto'] : null,
      'puesto_nombre' => $r['puesto_nombre'] !== null ? (string)$r['puesto_nombre'] : null,
      'puesto_nivel' => $r['puesto_nivel'] !== null ? (int)$r['puesto_nivel'] : null,

      'tiene_acceso' => !empty($r['acceso_correo']),
      'acceso_correo' => $r['acceso_correo'] ? (string)$r['acceso_correo'] : null,
    ];
  }

  out(true, $rows);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: ' . $e->getMessage(), 500);
}
