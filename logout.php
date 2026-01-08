<?php
require_once __DIR__ . '/public/php/configuracion.php';
require_once __DIR__ . '/public/php/funciones_sesion.php';

cerrar_sesion_usuario();
header('Location: login.php');
exit;
 