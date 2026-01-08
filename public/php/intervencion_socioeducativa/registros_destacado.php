<?php
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('intervencion.edit');
header('Content-Type: application/json; charset=utf-8');

function out($ok, $data = null, $error = '') {
  echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
  exit;
}

// Soporta JSON o form-data
$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

$id = (int)($payload['id'] ?? ($_POST['id'] ?? 0));
$idUsuario = (int)($payload['id_usuario'] ?? ($_POST['id_usuario'] ?? 0));
$destacado = (int)($payload['destacado'] ?? ($_POST['destacado'] ?? -1));

if ($id <= 0) out(false, null, 'ID de registro inválido');
if ($idUsuario <= 0) out(false, null, 'ID de usuario inválido');
if (!in_array($destacado, [0,1], true)) out(false, null, 'Valor destacado inválido');

try {
  $stmt = $mysql_db->prepare("
    UPDATE AA_Registros
    SET Destacado = ?
    WHERE id = ? AND ID_Usuario = ?
    LIMIT 1
  ");
  $stmt->bind_param("iii", $destacado, $id, $idUsuario);
  $stmt->execute();

  if ($stmt->affected_rows <= 0) {
    out(false, null, 'No se pudo actualizar (no existe o no pertenece al usuario)');
  }

  out(true, ['id' => $id, 'Destacado' => $destacado], '');

} catch (mysqli_sql_exception $e) {
  out(false, null, 'Error en la base de datos: ' . $e->getMessage());
}
