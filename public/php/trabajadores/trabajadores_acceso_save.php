<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('trabajadores.edit');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data = null, ?string $error = null, int $code = 200): void {
  http_response_code($code);
  echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
  exit;
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) out(false, null, 'JSON inválido', 400);

$id_trabajador = (int)($body['id_trabajador'] ?? 0);
$correo = trim((string)($body['correo'] ?? ''));
$landpage = trim((string)($body['landpage'] ?? 'index.php?pagina=dashboard'));
$pass = (string)($body['pass'] ?? '');

// opcional: reflejar nombre/apellidos en AA_accesos
$nombre = trim((string)($body['nombre'] ?? ''));
$apellidos = trim((string)($body['apellidos'] ?? ''));

if ($id_trabajador <= 0) out(false, null, 'id_trabajador inválido', 422);
if ($correo === '') out(false, null, 'Correo obligatorio', 422);
if (mb_strlen($correo) > 50) out(false, null, 'Correo demasiado largo', 422);
if (mb_strlen($landpage) > 50) $landpage = mb_substr($landpage, 0, 50);

try {
  // ¿Ya existe acceso para este trabajador?
  $stmt = $mysql_db->prepare("SELECT id, correo FROM AA_accesos WHERE id_trabajador=? LIMIT 1");
  $stmt->bind_param('i', $id_trabajador);
  $stmt->execute();
  $res = $stmt->get_result();
  $existing = $res->fetch_assoc();

  $hashed = null;
  $doPass = trim($pass) !== '';
  if ($doPass) {
    // hash seguro
    $hashed = password_hash($pass, PASSWORD_DEFAULT);
  }

  if ($existing) {
    // update
    if ($doPass) {
      $stmt = $mysql_db->prepare("UPDATE AA_accesos SET correo=?, nombre=?, apellidos=?, landpage=?, pass=? WHERE id_trabajador=? LIMIT 1");
      $stmt->bind_param('sssssi', $correo, $nombre, $apellidos, $landpage, $hashed, $id_trabajador);
    } else {
      $stmt = $mysql_db->prepare("UPDATE AA_accesos SET correo=?, nombre=?, apellidos=?, landpage=? WHERE id_trabajador=? LIMIT 1");
      $stmt->bind_param('ssssi', $correo, $nombre, $apellidos, $landpage, $id_trabajador);
    }
    $stmt->execute();
    out(true, ['id_trabajador' => $id_trabajador]);
  }

  // insert
  if (!$doPass) {
    out(false, null, 'Debes indicar una contraseña para crear un acceso nuevo.', 422);
  }

  $stmt = $mysql_db->prepare("INSERT INTO AA_accesos (correo, nombre, apellidos, landpage, id_trabajador, pass) VALUES (?,?,?,?,?,?)");
  $stmt->bind_param('ssssis', $correo, $nombre, $apellidos, $landpage, $id_trabajador, $hashed);
  $stmt->execute();

  out(true, ['id_trabajador' => $id_trabajador]);

} catch (Throwable $e) {
  // Si el correo ya existe -> error más claro
  $msg = $e->getMessage();
  if (str_contains($msg, 'Duplicate') || str_contains($msg, 'correo')) {
    out(false, null, 'Ese correo ya está en uso.', 409);
  }
  out(false, null, 'SQL ERROR: ' . $msg, 500);
}
