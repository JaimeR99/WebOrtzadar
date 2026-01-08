<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('viviendas.edit');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?: [];
$idVivienda = (int)($body['id_vivienda'] ?? 0);
$valoracion = (int)($body['valoracion'] ?? 0);
$comentario = trim((string)($body['comentario'] ?? ''));

if ($idVivienda <= 0) out(false, null, 'id_vivienda inválido', 400);
if ($valoracion < 1 || $valoracion > 5) out(false, null, 'valoracion inválida (1..5)', 400);
if ($comentario === '') out(false, null, 'comentario vacío', 400);
if (mb_strlen($comentario) > 500) out(false, null, 'comentario demasiado largo (máx. 500)', 400);

try {
  $mysql_db->begin_transaction();

  $st = $mysql_db->prepare(
    "INSERT INTO AA_vivienda_dinamicas_grupales (id_vivienda, fecha, valoracion, comentario)
     VALUES (?, NOW(), ?, ?)"
  );
  $st->bind_param('iis', $idVivienda, $valoracion, $comentario);
  $st->execute();

  $idNuevo = (int)$mysql_db->insert_id;

  // Guardar en AA_viviendas el id del último
  $st2 = $mysql_db->prepare("UPDATE AA_viviendas SET dinamicas_grupales=? WHERE id=?");
  $st2->bind_param('ii', $idNuevo, $idVivienda);
  $st2->execute();

  $mysql_db->commit();

  out(true, ['id_nuevo' => $idNuevo]);
} catch (Throwable $e) {
  try { $mysql_db->rollback(); } catch (Throwable $_) {}
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
