<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('viviendas.edit');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data = null, ?string $error = null, int $code = 200): void {
  http_response_code($code);
  echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
  exit;
}

function getLoggedTrabajadorId(mysqli $db): int {
  if (!empty($_SESSION['id_trabajador'])) return (int)$_SESSION['id_trabajador'];

  $email = $_SESSION['usuario']['email'] ?? null;
  if ($email) {
    $st = $db->prepare("SELECT id_trabajador FROM AA_accesos WHERE correo=? LIMIT 1");
    $st->bind_param('s', $email);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    if (!empty($r['id_trabajador'])) return (int)$r['id_trabajador'];
  }
  return 0;
}

$body = json_decode(file_get_contents('php://input'), true) ?: [];
$id_vivienda = (int)($body['id_vivienda'] ?? 0);

if ($id_vivienda <= 0) out(false, null, 'id_vivienda inválido', 400);

// ✅ Responsable: sesión si existe, si no 1 (temporal)
// TODO: cuando el login esté bien, usar solo sesión y quitar el fallback 1.
$responsable_id = getLoggedTrabajadorId($mysql_db);
if ($responsable_id <= 0) $responsable_id = 1;

// días
$lunes = trim((string)($body['lunes'] ?? ''));
$martes = trim((string)($body['martes'] ?? ''));
$miercoles = trim((string)($body['miercoles'] ?? ''));
$jueves = trim((string)($body['jueves'] ?? ''));
$viernes = trim((string)($body['viernes'] ?? ''));
$sabado = trim((string)($body['sabado'] ?? ''));
$domingo = trim((string)($body['domingo'] ?? ''));

try {
  $sql = "INSERT INTO AA_vivienda_menu_semanal
    (id_vivienda, lunes, martes, miercoles, jueves, viernes, sabado, domingo, responsable)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

  $stmt = $mysql_db->prepare($sql);
  $stmt->bind_param(
    "isssssssi",
    $id_vivienda,
    $lunes,
    $martes,
    $miercoles,
    $jueves,
    $viernes,
    $sabado,
    $domingo,
    $responsable_id
  );
  $stmt->execute();

  out(true, ['id_nuevo' => (int)$mysql_db->insert_id]);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: ' . $e->getMessage(), 500);
}
