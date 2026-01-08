<?php
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('intervencion.view');
header('Content-Type: application/json; charset=utf-8');

// Obtenemos los usuarios que NO están en la tabla AA_intervencion_socioeducativa
$query = "
  SELECT
    u.id,
    u.Nombre, u.Apellidos, u.Dni,
    u.Telefono_Usuario, u.Correo, u.Direccion
  FROM AA_usuarios AS u
  WHERE u.id NOT IN (SELECT id_usuario FROM AA_intervencion_socioeducativa)
";

$result = $mysql_db->query($query);

$usuarios = [];
while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}

echo json_encode(['ok' => true, 'data' => $usuarios]);
?>
