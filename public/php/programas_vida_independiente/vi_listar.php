<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('vida_independiente.view');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  $sql = "
    SELECT
      p.id_usuario,
      u.Nombre, u.Apellidos, u.Dni,
      u.Correo,
      u.Direccion,
      u.Telefono_Usuario
    FROM AA_vida_independiente_participantes p
    JOIN AA_usuarios u ON u.id = p.id_usuario
    ORDER BY u.Apellidos ASC, u.Nombre ASC
  ";
  $res = $mysql_db->query($sql);
  out(true, $res->fetch_all(MYSQLI_ASSOC));
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
