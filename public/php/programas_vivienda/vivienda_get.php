<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('viviendas.view');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data = null, ?string $error = null, int $code = 200): void
{
  http_response_code($code);
  echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
  exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) out(false, null, 'id inválido', 400);

try {
  $sqlV = "
    SELECT
      v.*,
      CONCAT_WS(' ', tr.nombre, tr.apellidos) AS responsable_nombre,
      CONCAT_WS(' ', ti.nombre, ti.apellidos) AS integrador_nombre
    FROM AA_viviendas v
    LEFT JOIN AA_trabajadores tr ON tr.id = v.responsable_programa
    LEFT JOIN AA_trabajadores ti ON ti.id = v.integrador
    WHERE v.id=? LIMIT 1
  ";
  $st = $mysql_db->prepare($sqlV);
  $st->bind_param('i', $id);
  $st->execute();
  $viv = $st->get_result()->fetch_assoc();
  if (!$viv) out(false, null, 'Vivienda no encontrada', 404);

  // equipamientos
  $equip = ['caldera' => 0, 'gas' => 0, 'extintores' => 0, 'contratos' => 0, 'cei' => 0, 'salto' => 0];
  $stE = $mysql_db->prepare("SELECT caldera, gas, extintores, contratos, cei, salto FROM AA_vivienda_equipamientos WHERE id_vivienda=? LIMIT 1");
  $stE->bind_param('i', $id);
  $stE->execute();
  $rowE = $stE->get_result()->fetch_assoc();
  if ($rowE) foreach ($equip as $k => $_) $equip[$k] = (int)($rowE[$k] ?? 0);

  out(true, [
    'id' => (int)$viv['id'],
    'nombre' => $viv['nombre'],
    'direccion' => $viv['direccion'],
    'localidad' => $viv['localidad'],
    'responsable_programa' => $viv['responsable_programa'] !== null ? (int)$viv['responsable_programa'] : null,
    'integrador' => $viv['integrador'] !== null ? (int)$viv['integrador'] : null,
    'responsable_nombre' => $viv['responsable_nombre'] ?? '',
    'integrador_nombre' => $viv['integrador_nombre'] ?? '',
    'created_at' => $viv['created_at'] ?? null,
    'updated_at' => $viv['updated_at'] ?? null,
    'equipamientos' => $equip,
  ]);
  
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: ' . $e->getMessage(), 500);
}
