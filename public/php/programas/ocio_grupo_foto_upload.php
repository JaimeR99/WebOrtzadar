<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('ocio.edit');

function out($ok, $data=null, $error=null, $code=200){
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$id = isset($_POST['id_grupo']) ? (int)$_POST['id_grupo'] : 0;
if ($id <= 0) out(false, null, 'ID inválido', 400);
if (empty($_FILES['foto']['tmp_name'])) out(false, null, 'No se recibió archivo', 400);

$tmp  = $_FILES['foto']['tmp_name'];
$mime = mime_content_type($tmp);
if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) {
  out(false, null, 'Formato no permitido', 400);
}

$uploadDir = __DIR__ . '/../../../uploads/ocio_grupos/';
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

$dest = $uploadDir . $id . '.jpg';

$src = match($mime){
  'image/png'  => imagecreatefrompng($tmp),
  'image/webp' => imagecreatefromwebp($tmp),
  default      => imagecreatefromjpeg($tmp),
};
if (!$src) out(false, null, 'No se pudo leer la imagen', 400);

$dst = imagecreatetruecolor(256, 256);
$w = imagesx($src); $h = imagesy($src);
imagecopyresampled($dst, $src, 0,0,0,0, 256,256, $w,$h);
imagejpeg($dst, $dest, 85);
imagedestroy($src); imagedestroy($dst);

out(true, ['id'=>$id]);
