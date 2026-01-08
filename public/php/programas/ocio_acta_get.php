<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('ocio.view');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data=null, ?string $error=null, int $code=200): void {
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$id_acta = isset($_GET['id_acta']) ? (int)$_GET['id_acta'] : 0;
if ($id_acta <= 0) out(false, null, 'id_acta inválido', 422);

try {
  // acta
  $stA = $mysql_db->prepare("SELECT id, id_grupo, fecha, valoracion FROM AA_Ocio_actas WHERE id=? LIMIT 1");
  $stA->bind_param("i", $id_acta);
  $stA->execute();
  $actaRow = $stA->get_result()->fetch_assoc();
  if (!$actaRow) out(false, null, 'Acta no encontrada', 404);

  $id_grupo = (int)$actaRow['id_grupo'];

  // participantes del grupo (para checks)
  $stP = $mysql_db->prepare("
    SELECT u.id, u.Nombre, u.Apellidos, u.Dni
    FROM AA_Ocio_participantes p
    INNER JOIN AA_usuarios u ON u.id=p.id_usuario
    WHERE p.id_grupo=?
    ORDER BY u.Apellidos ASC, u.Nombre ASC
  ");
  $stP->bind_param("i", $id_grupo);
  $stP->execute();
  $rsP = $stP->get_result();

  $participantes = [];
  while ($r = $rsP->fetch_assoc()) {
    $participantes[] = [
      'id' => (int)$r['id'],
      'Nombre' => $r['Nombre'],
      'Apellidos' => $r['Apellidos'],
      'Dni' => $r['Dni'],
    ];
  }

  // presentes
  $presentes = [];
  $stS = $mysql_db->prepare("SELECT id_usuario FROM AA_Ocio_asistencia WHERE id_acta=?");
  $stS->bind_param("i", $id_acta);
  $stS->execute();
  $rsS = $stS->get_result();
  while ($r = $rsS->fetch_assoc()) $presentes[] = (int)$r['id_usuario'];

  // incidencias
  $incidencias = [];
  $stI = $mysql_db->prepare("
    SELECT id, fecha, incidencia
    FROM AA_Ocio_incidencias
    WHERE id_acta=?
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

  out(true, [
    'acta' => [
      'id' => (int)$actaRow['id'],
      'id_grupo' => $id_grupo,
      'fecha' => $actaRow['fecha'],
      'valoracion' => $actaRow['valoracion'],
    ],
    'participantes' => $participantes,
    'presentes' => $presentes,
    'incidencias' => $incidencias,
  ]);
} catch (Throwable $e) {
  out(false, null, 'SQL ERROR: '.$e->getMessage(), 500);
}
