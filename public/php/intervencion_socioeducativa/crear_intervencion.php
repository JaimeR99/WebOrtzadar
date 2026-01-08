<?php
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('intervencion.edit');
header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$id_usuario = isset($data['id_usuario']) ? (int)$data['id_usuario'] : 0;
if ($id_usuario <= 0) {
  echo json_encode(['ok' => false, 'error' => 'id_usuario inválido']);
  exit;
}

try {
  // 1) comprobar si ya existe (evita duplicados sin depender de UNIQUE)
  $check = $mysql_db->prepare("SELECT 1 FROM AA_intervencion_socioeducativa WHERE id_usuario = ? LIMIT 1");
  $check->bind_param("i", $id_usuario);
  $check->execute();
  $check->store_result();

  if ($check->num_rows > 0) {
    echo json_encode(['ok' => true, 'data' => ['id_usuario' => $id_usuario, 'already' => 1]]);
    exit;
  }

  // 2) insertar
  $ins = $mysql_db->prepare("INSERT INTO AA_intervencion_socioeducativa (id_usuario) VALUES (?)");
  $ins->bind_param("i", $id_usuario);

  if (!$ins->execute()) {
    echo json_encode(['ok' => false, 'error' => 'Error insertando: ' . $mysql_db->error]);
    exit;
  }

  echo json_encode(['ok' => true, 'data' => ['id_usuario' => $id_usuario]]);
} catch (Throwable $e) {
  echo json_encode(['ok' => false, 'error' => 'Excepción: ' . $e->getMessage()]);
}
