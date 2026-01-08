<?php
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('intervencion.edit');
// Recibe el cuerpo de la solicitud como JSON
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

// Asegúrate de que los datos estén presentes
$usuario_id = isset($data['id_usuario']) ? $data['id_usuario'] : null;
$comentario = isset($data['comentario']) ? $data['comentario'] : null;
$ambito = isset($data['ambito']) ? $data['ambito'] : null;
$categoria = isset($data['categoria']) ? $data['categoria'] : null;

if (!$usuario_id || !$comentario || !$ambito || !$categoria) {
    echo json_encode(['ok' => false, 'error' => 'Faltan parámetros necesarios']);
    exit;
}

try {
    // Preparamos la consulta
    $stmt = $mysql_db->prepare(
        "INSERT INTO AA_Registros (ID_Usuario, Comentario, Ambito, Categoria, Fecha)
        VALUES (?, ?, ?, ?, NOW())"
    );

    if ($stmt === false) {
        throw new Exception('Error al preparar la consulta SQL.');
    }

    // Vinculamos los parámetros
    $stmt->bind_param("isss", $usuario_id, $comentario, $ambito, $categoria);
    
    // Ejecutamos la consulta
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['ok' => true, 'message' => 'Registro guardado correctamente']);
    } else {
        echo json_encode(['ok' => false, 'error' => 'No se insertó ningún registro']);
    }
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => 'Error al guardar el registro: ' . $e->getMessage()]);
}
?>
