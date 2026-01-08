<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('trabajadores.view');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) out(false, null, 'id inválido', 422);

try {
  // trabajador + puesto
  $stmt = $mysql_db->prepare('
    SELECT
      t.id, t.nombre, t.apellidos, t.sexo, t.dni, t.fecha_nacimiento, t.direccion,
      t.telefono, t.email, t.cuenta_corriente, t.fecha_alta, t.id_puesto,
      p.nombre AS puesto_nombre, p.nivel AS puesto_nivel
    FROM AA_trabajadores t
    LEFT JOIN AA_puestos p ON p.id = t.id_puesto
    WHERE t.id=? LIMIT 1
  ');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $res = $stmt->get_result();
  $t = $res->fetch_assoc();
  if (!$t) out(false, null, 'No encontrado', 404);

  // acceso
  $stmt = $mysql_db->prepare('SELECT id, correo, nombre, apellidos, landpage FROM AA_accesos WHERE id_trabajador=? LIMIT 1');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $res = $stmt->get_result();
  $a = $res->fetch_assoc();

  // formacion
  $stmt = $mysql_db->prepare('
    SELECT id, nombre_formacion, fecha, institucion, valoracion
    FROM AA_trabajadores_formacion
    WHERE id_trabajador=?
    ORDER BY (fecha IS NULL) ASC, fecha DESC, id DESC
    LIMIT 200
  ');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $res = $stmt->get_result();
  $form = [];
  while ($f = $res->fetch_assoc()) {
    $form[] = [
      'id' => (int)$f['id'],
      'nombre_formacion' => (string)($f['nombre_formacion'] ?? ''),
      'fecha' => $f['fecha'] !== null ? (string)$f['fecha'] : null,
      'institucion' => $f['institucion'] !== null ? (string)$f['institucion'] : null,
      'valoracion' => $f['valoracion'] !== null ? (int)$f['valoracion'] : null,
    ];
  }

  out(true, [
    'id' => (int)$t['id'],
    'nombre' => (string)($t['nombre'] ?? ''),
    'apellidos' => (string)($t['apellidos'] ?? ''),
    'sexo' => $t['sexo'] !== null ? (string)$t['sexo'] : null,
    'dni' => $t['dni'] !== null ? (string)$t['dni'] : null,
    'fecha_nacimiento' => $t['fecha_nacimiento'] !== null ? (string)$t['fecha_nacimiento'] : null,
    'direccion' => $t['direccion'] !== null ? (string)$t['direccion'] : null,
    'telefono' => (string)($t['telefono'] ?? ''),
    'email' => (string)($t['email'] ?? ''),
    'cuenta_corriente' => $t['cuenta_corriente'] !== null ? (string)$t['cuenta_corriente'] : null,
    'fecha_alta' => $t['fecha_alta'] ? (string)$t['fecha_alta'] : null,

    'id_puesto' => $t['id_puesto'] !== null ? (int)$t['id_puesto'] : null,
    'puesto_nombre' => $t['puesto_nombre'] !== null ? (string)$t['puesto_nombre'] : null,
    'puesto_nivel' => $t['puesto_nivel'] !== null ? (int)$t['puesto_nivel'] : null,

    'formacion' => $form,

    'acceso' => $a ? [
      'id' => (int)$a['id'],
      'correo' => (string)($a['correo'] ?? ''),
      'nombre' => (string)($a['nombre'] ?? ''),
      'apellidos' => (string)($a['apellidos'] ?? ''),
      'landpage' => (string)($a['landpage'] ?? ''),
    ] : null,
  ]);

} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: ' . $e->getMessage(), 500);
}
