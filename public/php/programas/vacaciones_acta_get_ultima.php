<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('vacaciones.view');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$id_destino = isset($_GET['id_destino']) ? (int)$_GET['id_destino'] : 0;
if ($id_destino <= 0) out(false, null, 'id_destino inválido', 422);

try {
  // participantes del destino
  $stP = $mysql_db->prepare("
    SELECT u.id, u.Nombre, u.Apellidos, u.Dni
    FROM AA_Vacaciones_participantes p
    INNER JOIN AA_usuarios u ON u.id = p.id_usuario
    WHERE p.id_destino = ?
    ORDER BY u.Apellidos ASC, u.Nombre ASC
  ");
  $stP->bind_param("i", $id_destino);
  $stP->execute();
  $rsP = $stP->get_result();

  $participantes = [];
  while ($r = $rsP->fetch_assoc()) {
    $participantes[] = [
      'id' => (int)$r['id'],
      'Nombre' => $r['Nombre'],
      'Apellidos' => $r['Apellidos'],
      'Dni' => $r['Dni'],
      'foto_url' => '/webOrtzadar/uploads/usuarios/' . $r['id'] . '.jpg',
    ];
  }

  // última acta
  $stA = $mysql_db->prepare("
    SELECT id, id_destino, fecha, valoracion
    FROM AA_Vacaciones_actas
    WHERE id_destino = ?
    ORDER BY fecha DESC, id DESC
    LIMIT 1
  ");
  $stA->bind_param("i", $id_destino);
  $stA->execute();
  $actaRow = $stA->get_result()->fetch_assoc();

  $acta = null;
  $presentes = [];
  $incidencias = [];

  if ($actaRow) {
    $id_acta = (int)$actaRow['id'];
    $acta = [
      'id' => $id_acta,
      'id_destino' => (int)$actaRow['id_destino'],
      'fecha' => $actaRow['fecha'],
      'valoracion' => $actaRow['valoracion'],
    ];

    // presentes
    $stAs = $mysql_db->prepare("SELECT id_usuario FROM AA_Vacaciones_asistencia WHERE id_acta = ?");
    $stAs->bind_param("i", $id_acta);
    $stAs->execute();
    $rsAs = $stAs->get_result();
    while ($r = $rsAs->fetch_assoc()) {
      $presentes[] = (int)$r['id_usuario'];
    }

    // incidencias
    $stI = $mysql_db->prepare("
      SELECT id, fecha, incidencia
      FROM AA_Vacaciones_incidencias
      WHERE id_acta = ?
      ORDER BY fecha ASC, id ASC
    ");
    $stI->bind_param("i", $id_acta);
    $stI->execute();
    $rsI = $stI->get_result();
    while ($r = $rsI->fetch_assoc()) {
      $incidencias[] = [
        'id' => (int)$r['id'],
        'fecha' => $r['fecha'],
        'incidencia' => $r['incidencia'],
      ];
    }
  }

  out(true, [
    'participantes' => $participantes,
    'acta' => $acta,
    'presentes' => $presentes,
    'incidencias' => $incidencias,
  ]);

} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
