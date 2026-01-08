<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('formaciones.view');

function out($ok, $data=null, $error=null, $code=200){
  http_response_code($code);
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error], JSON_UNESCAPED_UNICODE);
  exit;
}

$idFormacion = (int)($_GET['id_formacion'] ?? 0);
if ($idFormacion <= 0) out(false, null, 'id_formacion inválido', 400);

// última acta
$stmt = $mysql_db->prepare("
  SELECT id, id_formacion, fecha, valoracion, created_at, created_by
  FROM AA_Formaciones_actas
  WHERE id_formacion = ?
  ORDER BY COALESCE(fecha, created_at) DESC, id DESC
  LIMIT 1
");
if (!$stmt) out(false, null, 'SQL prepare error: '.$mysql_db->error, 500);
$stmt->bind_param('i', $idFormacion);
if (!$stmt->execute()) out(false, null, 'SQL execute error: '.$stmt->error, 500);
$res = $stmt->get_result();
$acta = $res ? $res->fetch_assoc() : null;
$stmt->close();

$actaOut = null;
$presentes = [];
$incidencias = [];

if ($acta) {
  $idActa = (int)$acta['id'];
  $actaOut = [
    'id' => $idActa,
    'id_formacion' => (int)$acta['id_formacion'],
    'fecha' => $acta['fecha'],
    'valoracion' => $acta['valoracion'] ?? '',
  ];

  // presentes
  $st2 = $mysql_db->prepare("SELECT id_usuario FROM AA_Formaciones_actas_presentes WHERE id_acta=?");
  if (!$st2) out(false, null, 'SQL prepare error: '.$mysql_db->error, 500);
  $st2->bind_param('i', $idActa);
  if (!$st2->execute()) out(false, null, 'SQL execute error: '.$st2->error, 500);
  $r2 = $st2->get_result();
  while ($x = $r2->fetch_assoc()) $presentes[] = (int)$x['id_usuario'];
  $st2->close();

  // incidencias
  $st3 = $mysql_db->prepare("
    SELECT id, incidencia, created_at
    FROM AA_Formaciones_actas_incidencias
    WHERE id_acta=?
    ORDER BY created_at ASC, id ASC
  ");
  if (!$st3) out(false, null, 'SQL prepare error: '.$mysql_db->error, 500);
  $st3->bind_param('i', $idActa);
  if (!$st3->execute()) out(false, null, 'SQL execute error: '.$st3->error, 500);
  $r3 = $st3->get_result();
  while ($x = $r3->fetch_assoc()) {
    $incidencias[] = [
      'id' => (int)$x['id'],
      'incidencia' => $x['incidencia'],
      'fecha' => $x['created_at'], // <- fecha visible en UI
    ];
  }
  $st3->close();
}

// participantes de la formación (para pestaña participantes + asistencia)
$stp = $mysql_db->prepare("
  SELECT u.id, u.Nombre, u.Apellidos, u.Dni
  FROM AA_Formaciones_participantes p
  INNER JOIN AA_usuarios u ON u.id = p.id_usuario
  WHERE p.id_formacion = ?
  ORDER BY u.Apellidos ASC, u.Nombre ASC
");
if (!$stp) out(false, null, 'SQL prepare error: '.$mysql_db->error, 500);
$stp->bind_param('i', $idFormacion);
if (!$stp->execute()) out(false, null, 'SQL execute error: '.$stp->error, 500);

$rp = $stp->get_result();
$participantes = [];
while ($u = $rp->fetch_assoc()) {
  $uid = (int)$u['id'];
  $participantes[] = [
    'id' => $uid,
    'Nombre' => $u['Nombre'],
    'Apellidos' => $u['Apellidos'],
    'Dni' => $u['Dni'],
    'foto_url' => '/webOrtzadar/uploads/usuarios/' . $uid . '.jpg'
  ];
}
$stp->close();

out(true, [
  'acta' => $actaOut,
  'participantes' => $participantes,
  'presentes' => $presentes,
  'incidencias' => $incidencias
]);
