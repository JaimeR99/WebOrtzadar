<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('viviendas.edit');


function out($ok, $data=null, $error=null, $code=200){
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$id = isset($_POST['id_vivienda']) ? (int)$_POST['id_vivienda'] : 0;
if ($id <= 0) out(false, null, 'ID inválido', 400);

if (empty($_FILES['foto']['tmp_name'])) out(false, null, 'No se recibió archivo', 400);

$tmp  = $_FILES['foto']['tmp_name'];
$mime = mime_content_type($tmp);

if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) {
  out(false, null, 'Formato de imagen no permitido', 400);
}

$uploadDir = __DIR__ . '/../../../uploads/viviendas/';
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

$dest = $uploadDir . $id . '.jpg';

/* Normalizar a JPG 800x600 (cover crop centrado) */
$src = match($mime){
  'image/png'  => imagecreatefrompng($tmp),
  'image/webp' => imagecreatefromwebp($tmp),
  default      => imagecreatefromjpeg($tmp),
};
if (!$src) out(false, null, 'No se pudo leer la imagen', 400);

$tw = 800; $th = 600;
$sw = imagesx($src); $sh = imagesy($src);

$srcRatio = $sw / max(1, $sh);
$dstRatio = $tw / $th;

if ($srcRatio > $dstRatio) {
  // recortar ancho
  $newW = (int)round($sh * $dstRatio);
  $newH = $sh;
  $sx = (int)round(($sw - $newW) / 2);
  $sy = 0;
} else {
  // recortar alto
  $newW = $sw;
  $newH = (int)round($sw / $dstRatio);
  $sx = 0;
  $sy = (int)round(($sh - $newH) / 2);
}

$dst = imagecreatetruecolor($tw, $th);
imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $tw, $th, $newW, $newH);
imagejpeg($dst, $dest, 85);

imagedestroy($src);
imagedestroy($dst);

out(true, ['path' => "uploads/viviendas/$id.jpg"]);
