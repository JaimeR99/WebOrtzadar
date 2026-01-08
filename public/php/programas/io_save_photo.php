<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('informacion_orientacion.edit');

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$id = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
$f  = $_FILES['foto'] ?? null;

if ($id <= 0 || !$f) out(false, null, 'Datos incompletos', 400);
if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) out(false, null, 'Error subiendo archivo', 400);

$tmp = $f['tmp_name'] ?? '';
if (!$tmp || !is_uploaded_file($tmp)) out(false, null, 'Upload inválido', 400);

// Validación mime real (no confiar en $f['type'])
$info = @getimagesize($tmp);
if (!$info || empty($info['mime'])) out(false, null, 'El archivo no es una imagen', 400);

$mime = $info['mime'];
$allowed = ['image/jpeg','image/png','image/webp'];
if (!in_array($mime, $allowed, true)) out(false, null, 'Formato no soportado (usa JPG/PNG/WebP)', 400);

// Carpeta destino
$dir = realpath(__DIR__ . '/../../../uploads');
if ($dir === false) out(false, null, 'No existe /uploads', 500);

$targetDir = $dir . '/usuarios';
if (!is_dir($targetDir)) {
  if (!mkdir($targetDir, 0775, true)) out(false, null, 'No se pudo crear /uploads/usuarios', 500);
}

$dest = $targetDir . '/' . $id . '.jpg';

// Cargar imagen con GD según mime
switch ($mime) {
  case 'image/jpeg': $img = @imagecreatefromjpeg($tmp); break;
  case 'image/png':  $img = @imagecreatefrompng($tmp);  break;
  case 'image/webp': $img = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmp) : null; break;
  default: $img = null;
}
if (!$img) out(false, null, 'No se pudo leer la imagen', 400);

// IMPORTANTE: si viene con alpha (png/webp), la convertimos a fondo blanco
$w = imagesx($img); $h = imagesy($img);
$canvas = imagecreatetruecolor($w, $h);
$white = imagecolorallocate($canvas, 255, 255, 255);
imagefilledrectangle($canvas, 0, 0, $w, $h, $white);
imagecopy($canvas, $img, 0, 0, 0, 0, $w, $h);

// Guardar SIEMPRE en JPG (calidad 85)
if (!@imagejpeg($canvas, $dest, 85)) {
  imagedestroy($img);
  imagedestroy($canvas);
  out(false, null, 'No se pudo guardar el JPG', 500);
}

imagedestroy($img);
imagedestroy($canvas);

// Si además quieres guardar “tiene_foto=1” en BD, aquí lo harías.
// O guardar la ruta 'uploads/usuarios/{id}.jpg' si tienes campo.

out(true, [
  'path' => "uploads/usuarios/{$id}.jpg",
  'cache' => time()
]);
