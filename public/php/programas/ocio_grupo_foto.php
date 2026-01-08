<?php
declare(strict_types=1);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(404); exit; }

$path = __DIR__ . '/../../../uploads/ocio_grupos/' . $id . '.jpg';
if (!is_file($path)) { http_response_code(404); exit; }

header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=86400');
readfile($path);
exit;
