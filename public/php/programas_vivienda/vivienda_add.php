<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('viviendas.edit');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data = null, ?string $error = null, int $code = 200): void
{
  http_response_code($code);
  echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
  exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?: [];

$nombre = trim((string)($body['nombre'] ?? ''));
$direccion = trim((string)($body['direccion'] ?? ''));
$localidad = trim((string)($body['localidad'] ?? ''));
$responsable = (int)($body['responsable_programa'] ?? 0);
$integrador = (int)($body['integrador'] ?? 0);

if ($nombre === '' || $direccion === '' || $localidad === '' || $responsable <= 0 || $integrador <= 0) {
  out(false, null, 'Faltan campos obligatorios', 400);
}

try {
  $mysql_db->begin_transaction();

  // Insert vivienda (solo columnas reales en tu BBDD actual)
  $st = $mysql_db->prepare("
    INSERT INTO AA_viviendas
      (nombre, direccion, localidad, responsable_programa, integrador, dinamicas_grupales, ultimo_menu_id, ultima_incidencia_id)
    VALUES
      (?, ?, ?, ?, ?, NULL, NULL, NULL)
  ");
  $st->bind_param('sssii', $nombre, $direccion, $localidad, $responsable, $integrador);
  $st->execute();

  $idNuevo = (int)$mysql_db->insert_id;

  // Crear fila equipamientos (defaults 0)
  $st2 = $mysql_db->prepare("INSERT INTO AA_vivienda_equipamientos (id_vivienda) VALUES (?)");
  $st2->bind_param('i', $idNuevo);
  $st2->execute();

  $mysql_db->commit();

  out(true, ['id_nuevo' => $idNuevo]);
} catch (Throwable $e) {
  $mysql_db->rollback();

  // Error típico: nombre duplicado (unique)
  $msg = $e->getMessage();
  if (stripos($msg, 'Duplicate') !== false) {
    out(false, null, 'Ya existe una vivienda con ese nombre', 409);
  }

  out(false, null, 'SQL ERROR: ' . $msg, 500);
}
