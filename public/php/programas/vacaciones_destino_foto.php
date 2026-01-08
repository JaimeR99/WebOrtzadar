<?php
declare(strict_types=1);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(404); exit; }

$path = __DIR__ . '/../../../uploads/vacaciones_destinos/' . $id . '.jpg';
if (!is_file($path)) { http_response_code(404); exit; }

header('Content-Type: image/jpeg');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
readfile($path);
