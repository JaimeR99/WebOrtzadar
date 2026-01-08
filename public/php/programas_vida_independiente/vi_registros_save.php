<?php
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('vida_independiente.edit');

header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?: [];

$idUsuario = (int)($body['id_usuario'] ?? 0);
$comentario = trim((string)($body['comentario'] ?? ''));
$ambito = trim((string)($body['ambito'] ?? ''));
$categoria = trim((string)($body['categoria'] ?? ''));

if ($idUsuario <= 0 || $comentario === '' || $ambito === '' || $categoria === '') {
  out(false, null, 'Faltan parámetros', 400);
}

try {
  $st = $mysql_db->prepare("
    INSERT INTO AA_vida_independiente_registros (ID_Usuario, Comentario, Ambito, Categoria, Fecha)
    VALUES (?, ?, ?, ?, NOW())
  ");
  $st->bind_param("isss", $idUsuario, $comentario, $ambito, $categoria);
  $st->execute();
  out(true, ['id' => $st->insert_id]);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
