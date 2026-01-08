<?php
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('vida_independiente.view');

header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$idUsuario = (int)($_GET['id_usuario'] ?? 0);
$ambito = trim((string)($_GET['ambito'] ?? ''));
$categoria = trim((string)($_GET['categoria'] ?? ''));

if ($idUsuario <= 0) out(false, null, 'ID de usuario inválido', 400);
if ($ambito === '' || $categoria === '') out(false, null, 'Faltan ambito/categoria', 400);

try {
  $sql = "
    SELECT id, ID_Usuario, Fecha, Comentario, Ambito, Categoria, COALESCE(Destacado,0) AS Destacado
    FROM AA_vida_independiente_registros
    WHERE ID_Usuario = ? AND Ambito = ? AND Categoria = ?
    ORDER BY Destacado DESC, Fecha DESC, id DESC
  ";
  $st = $mysql_db->prepare($sql);
  $st->bind_param("iss", $idUsuario, $ambito, $categoria);
  $st->execute();
  out(true, $st->get_result()->fetch_all(MYSQLI_ASSOC));
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
