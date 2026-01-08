<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('ocio.view');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) out(false, null, 'ID inválido', 400);

try {
  $st = $mysql_db->prepare("
    SELECT
      g.id,
      g.nombre,
      g.fecha,
      g.id_responsable,
      CONCAT_WS(' ', t.nombre, t.apellidos) AS responsable_nombre
    FROM AA_Ocio_grupos g
    LEFT JOIN AA_trabajadores t ON t.id = g.id_responsable
    WHERE g.id=?
    LIMIT 1
  ");
  $st->bind_param("i", $id);
  $st->execute();
  $row = $st->get_result()->fetch_assoc();

  if (!$row) out(false, null, 'Grupo no encontrado', 404);

  out(true, ['grupo' => $row]);
} catch (Throwable $e) {
  out(false, null, 'Error cargando grupo: ' . $e->getMessage(), 500);
}
