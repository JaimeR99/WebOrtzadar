<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('formaciones.edit');

function out($ok, $data=null, $error=null, $code=200){
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) out(false, null, 'id inválido', 400);

if (!isset($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
  out(false, null, 'Falta file', 400);
}

$f = $_FILES['file'];
if (($f['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) out(false, null, 'Upload error', 400);

$maxBytes = 5 * 1024 * 1024;
if (($f['size'] ?? 0) > $maxBytes) out(false, null, 'Archivo demasiado grande (max 5MB)', 400);

// validar mime real
$fi = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($fi, $f['tmp_name']);
finfo_close($fi);

$allowed = ['image/jpeg','image/png','image/webp'];
if (!in_array($mime, $allowed, true)) out(false, null, 'Tipo no permitido (jpg/png/webp)', 400);

$dir = __DIR__ . '/../../../uploads/formaciones';
if (!is_dir($dir) && !mkdir($dir, 0775, true)) out(false, null, 'No se pudo crear uploads/formaciones', 500);

$dest = $dir . '/' . $id . '.jpg';

// convertir a JPG si viene PNG/WEBP (simple: guardar tal cual si JPEG; si no, reencode con GD si tienes)
if ($mime === 'image/jpeg') {
  if (!move_uploaded_file($f['tmp_name'], $dest)) out(false, null, 'No se pudo mover archivo', 500);
  out(true, ['path'=>"/webOrtzadar/uploads/formaciones/$id.jpg"]);
}

if (!extension_loaded('gd')) out(false, null, 'GD no disponible para convertir a JPG', 500);

switch ($mime) {
  case 'image/png':  $im = imagecreatefrompng($f['tmp_name']); break;
  case 'image/webp': $im = imagecreatefromwebp($f['tmp_name']); break;
  default: $im = null;
}
if (!$im) out(false, null, 'No se pudo leer imagen', 400);

imagejpeg($im, $dest, 88);
imagedestroy($im);

out(true, ['path'=>"/webOrtzadar/uploads/formaciones/$id.jpg"]);
