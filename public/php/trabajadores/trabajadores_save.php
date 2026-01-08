<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('trabajadores.edit');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) out(false, null, 'JSON inválido', 400);

$id = isset($body['id']) ? (int)$body['id'] : 0;

$nombre = trim((string)($body['nombre'] ?? ''));
$apellidos = trim((string)($body['apellidos'] ?? ''));
$telefono = trim((string)($body['telefono'] ?? ''));
$email = trim((string)($body['email'] ?? ''));

$sexo = $body['sexo'] ?? null;
$sexo = ($sexo === '' || $sexo === null) ? null : (string)$sexo;
$allowedSexo = ['hombre','mujer','no_binario'];
if ($sexo !== null && !in_array($sexo, $allowedSexo, true)) $sexo = null;

$dni = trim((string)($body['dni'] ?? ''));
$dni = ($dni === '') ? null : $dni;

$fecha_nacimiento = trim((string)($body['fecha_nacimiento'] ?? ''));
$fecha_nacimiento = ($fecha_nacimiento === '') ? null : $fecha_nacimiento;
if ($fecha_nacimiento !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_nacimiento)) {
  out(false, null, 'fecha_nacimiento inválida (YYYY-MM-DD)', 422);
}

$direccion = trim((string)($body['direccion'] ?? ''));
$direccion = ($direccion === '') ? null : $direccion;

$cuenta = trim((string)($body['cuenta_corriente'] ?? ''));
$cuenta = ($cuenta === '') ? null : $cuenta;

$id_puesto = $body['id_puesto'] ?? null;
$id_puesto = ($id_puesto === '' || $id_puesto === null) ? null : (int)$id_puesto;
if ($id_puesto !== null && $id_puesto <= 0) $id_puesto = null;

if ($nombre === '' && $apellidos === '') out(false, null, 'Nombre o apellidos son obligatorios', 422);

// recortes
if (mb_strlen($nombre) > 50) $nombre = mb_substr($nombre, 0, 50);
if (mb_strlen($apellidos) > 50) $apellidos = mb_substr($apellidos, 0, 50);
if (mb_strlen($telefono) > 50) $telefono = mb_substr($telefono, 0, 50);
if (mb_strlen($email) > 50) $email = mb_substr($email, 0, 50);
if ($dni !== null && mb_strlen($dni) > 20) $dni = mb_substr($dni, 0, 20);
if ($direccion !== null && mb_strlen($direccion) > 255) $direccion = mb_substr($direccion, 0, 255);
if ($cuenta !== null && mb_strlen($cuenta) > 34) $cuenta = mb_substr($cuenta, 0, 34);

try {
  if ($id > 0) {
    $stmt = $mysql_db->prepare('
      UPDATE AA_trabajadores
      SET nombre=?, apellidos=?, sexo=?, dni=?, fecha_nacimiento=?, direccion=?,
          telefono=?, email=?, cuenta_corriente=?, id_puesto=?
      WHERE id=? LIMIT 1
    ');
    // sssssssssis? -> en mysqli no hay tipo null: usamos bind con "s" y pasamos null como variable
    $stmt->bind_param(
      'sssssssssis',
      $nombre,
      $apellidos,
      $sexo,
      $dni,
      $fecha_nacimiento,
      $direccion,
      $telefono,
      $email,
      $cuenta,
      $id_puesto,
      $id
    );
    $stmt->execute();
    out(true, ['id' => $id]);
  }

  $fecha_alta = date('Y-m-d H:i:s');
  $stmt = $mysql_db->prepare('
    INSERT INTO AA_trabajadores
      (nombre, apellidos, sexo, dni, fecha_nacimiento, direccion, telefono, email, cuenta_corriente, id_puesto, fecha_alta)
    VALUES (?,?,?,?,?,?,?,?,?,?,?)
  ');
  $stmt->bind_param(
    'sssssssssis',
    $nombre,
    $apellidos,
    $sexo,
    $dni,
    $fecha_nacimiento,
    $direccion,
    $telefono,
    $email,
    $cuenta,
    $id_puesto,
    $fecha_alta
  );
  $stmt->execute();
  out(true, ['id' => (int)$mysql_db->insert_id]);

} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: ' . $e->getMessage(), 500);
}
