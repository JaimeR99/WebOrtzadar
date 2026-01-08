<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('vacaciones.edit');
function out($ok, $data=null, $error=null, $code=200){
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) out(false, null, 'ID inválido', 400);

if (!isset($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
  out(false, null, 'Fichero no recibido', 400);
}

$tmp  = $_FILES['file']['tmp_name'];
$mime = mime_content_type($tmp) ?: '';

if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) {
  out(false, null, 'Formato no permitido', 400);
}

$uploadDir = __DIR__ . '/../../../uploads/vacaciones_destinos/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

$src = match($mime) {
  'image/png'  => imagecreatefrompng($tmp),
  'image/webp' => imagecreatefromwebp($tmp),
  default      => imagecreatefromjpeg($tmp),
};
if (!$src) out(false, null, 'No se pudo leer la imagen', 500);

$w = imagesx($src); $h = imagesy($src);
$side = min($w, $h);
$srcX = (int)(($w - $side)/2);
$srcY = (int)(($h - $side)/2);

$size = 600;
$dst = imagecreatetruecolor($size, $size);
imagecopyresampled($dst, $src, 0,0, $srcX,$srcY, $size,$size, $side,$side);

$dest = $uploadDir . $id . '.jpg';
imagejpeg($dst, $dest, 85);

imagedestroy($src);
imagedestroy($dst);

out(true, ['id'=>$id, 'url'=>"php/programas/vacaciones_destino_foto.php?id=".$id."&t=".time()]);
