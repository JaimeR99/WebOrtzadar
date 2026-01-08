<?php
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('intervencion.view');
header('Content-Type: application/json; charset=utf-8');

$idUsuario = (int)($_GET['id_usuario'] ?? 0);
$ambito = trim((string)($_GET['ambito'] ?? ''));
$categoria = trim((string)($_GET['categoria'] ?? ''));

if ($idUsuario <= 0) {
  echo json_encode(['ok' => false, 'error' => 'ID de usuario inválido'], JSON_UNESCAPED_UNICODE);
  exit;
}
if ($ambito === '' || $categoria === '') {
  echo json_encode(['ok' => false, 'error' => 'Faltan ambito y/o categoria'], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  // IMPORTANTE:
  // - Ajusta ID_Registro y Comentario si tu tabla usa otros nombres.
  $sql = "
    SELECT
      id,
      ID_Usuario,
      Fecha,
      Comentario,
      Ambito,
      Categoria,
      COALESCE(Destacado, 0) AS Destacado
    FROM AA_Registros
    WHERE ID_Usuario = ?
      AND Ambito = ?
      AND Categoria = ?
    ORDER BY Destacado DESC, Fecha DESC, id DESC
  ";

  $stmt = $mysql_db->prepare($sql);
  $stmt->bind_param("iss", $idUsuario, $ambito, $categoria);
  $stmt->execute();
  $result = $stmt->get_result();

  $registros = [];
  while ($row = $result->fetch_assoc()) {
    // normaliza tipos para el frontend
    $row['id'] = (int)$row['id'];
    $row['ID_Usuario']  = (int)$row['ID_Usuario'];
    $row['Destacado']   = (int)$row['Destacado'];
    $registros[] = $row;
  }

  echo json_encode(['ok' => true, 'data' => $registros], JSON_UNESCAPED_UNICODE);

} catch (mysqli_sql_exception $e) {
  echo json_encode(['ok' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
