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
$id = (int)($body['id'] ?? 0);
if ($id <= 0) out(false, null, 'id inválido', 400);

$direccion = (string)($body['direccion'] ?? '');
$localidad = (string)($body['localidad'] ?? '');
$responsable = (int)($body['responsable_programa'] ?? 0);
$integrador = (int)($body['integrador'] ?? 0);

try {
  $sql = "UPDATE AA_viviendas
        SET direccion=?,
            localidad=?,
            responsable_programa = NULLIF(?,0),
            integrador = NULLIF(?,0)
        WHERE id=?";
  $st = $mysql_db->prepare($sql);
  $st->bind_param('ssiii', $direccion, $localidad, $responsable, $integrador, $id);

  $st->execute();

  // Equipamientos
  if (isset($body['equipamientos']) && is_array($body['equipamientos'])) {
    $eq = $body['equipamientos'];
    $caldera = !empty($eq['caldera']) ? 1 : 0;
    $gas = !empty($eq['gas']) ? 1 : 0;
    $extintores = !empty($eq['extintores']) ? 1 : 0;
    $contratos = !empty($eq['contratos']) ? 1 : 0;
    $cei = !empty($eq['cei']) ? 1 : 0;
    $salto = !empty($eq['salto']) ? 1 : 0;

    $stIns = $mysql_db->prepare("INSERT IGNORE INTO AA_vivienda_equipamientos (id_vivienda) VALUES (?)");
    $stIns->bind_param('i', $id);
    $stIns->execute();

    $stE = $mysql_db->prepare("
      UPDATE AA_vivienda_equipamientos
      SET caldera=?, gas=?, extintores=?, contratos=?, cei=?, salto=?
      WHERE id_vivienda=?
    ");
    $stE->bind_param('iiiiiii', $caldera, $gas, $extintores, $contratos, $cei, $salto, $id);
    $stE->execute();
  }

  out(true, ['id' => $id]);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: ' . $e->getMessage(), 500);
}
