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

$q = trim((string)($_GET['q'] ?? ''));
$limit = (int)($_GET['limit'] ?? 80);
if ($limit < 10) $limit = 10;
if ($limit > 200) $limit = 200;

// ✅ Si q es muy corta, devolver listado inicial
if (mb_strlen($q) < 1) $q = '';

try {
  $sql = "
    SELECT u.id, u.Nombre, u.Apellidos, u.Dni
    FROM AA_usuarios u
    LEFT JOIN AA_vida_independiente_participantes p ON p.id_usuario = u.id
    WHERE p.id_usuario IS NULL
  ";

  $types = '';
  $params = [];

  if ($q !== '') {
    // Tokenizamos por espacios (para 'Jon Ander', 'Ander Jon', etc.)
    $tokens = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    foreach ($tokens as $t) {
      $like = '%'.$t.'%';
      $sql .= " AND (
        u.Nombre LIKE ? OR u.Apellidos LIKE ? OR u.Dni LIKE ? OR
        CONCAT(COALESCE(u.Nombre,''),' ',COALESCE(u.Apellidos,'')) LIKE ?
      )";
      $types .= 'ssss';
      array_push($params, $like, $like, $like, $like);
    }
  }

  $sql .= " ORDER BY u.Apellidos ASC, u.Nombre ASC LIMIT ".$limit;

  $st = $mysql_db->prepare($sql);
  if ($types !== '') $st->bind_param($types, ...$params);
  $st->execute();

  $res = $st->get_result();
  out(true, $res->fetch_all(MYSQLI_ASSOC));
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
