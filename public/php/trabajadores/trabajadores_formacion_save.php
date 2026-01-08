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

$id = (int)($body['id'] ?? 0);
$id_trabajador = (int)($body['id_trabajador'] ?? 0);
$nombre = trim((string)($body['nombre_formacion'] ?? ''));
$fecha = trim((string)($body['fecha'] ?? ''));
$institucion = trim((string)($body['institucion'] ?? ''));
$valoracion = $body['valoracion'] ?? null;

if ($id_trabajador <= 0) out(false, null, 'id_trabajador inválido', 422);
if ($nombre === '') out(false, null, 'Nombre de formación obligatorio', 422);

$fecha = ($fecha === '') ? null : $fecha;
if ($fecha !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) out(false, null, 'Fecha inválida (YYYY-MM-DD)', 422);

$institucion = ($institucion === '') ? null : $institucion;

if ($valoracion === '' || $valoracion === null) $valoracion = null;
else {
  $valoracion = (int)$valoracion;
  if ($valoracion < 1 || $valoracion > 5) out(false, null, 'Valoración debe ser 1..5', 422);
}

if (mb_strlen($nombre) > 150) $nombre = mb_substr($nombre, 0, 150);
if ($institucion !== null && mb_strlen($institucion) > 150) $institucion = mb_substr($institucion, 0, 150);

try {
  if ($id > 0) {
    $stmt = $mysql_db->prepare('
      UPDATE AA_trabajadores_formacion
      SET nombre_formacion=?, fecha=?, institucion=?, valoracion=?
      WHERE id=? AND id_trabajador=? LIMIT 1
    ');
    $stmt->bind_param('sssiii', $nombre, $fecha, $institucion, $valoracion, $id, $id_trabajador);
    $stmt->execute();
    out(true, ['id' => $id]);
  }

  $stmt = $mysql_db->prepare('
    INSERT INTO AA_trabajadores_formacion (id_trabajador, nombre_formacion, fecha, institucion, valoracion)
    VALUES (?,?,?,?,?)
  ');
  $stmt->bind_param('isssi', $id_trabajador, $nombre, $fecha, $institucion, $valoracion);
  $stmt->execute();
  out(true, ['id' => (int)$mysql_db->insert_id]);

} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: ' . $e->getMessage(), 500);
}
