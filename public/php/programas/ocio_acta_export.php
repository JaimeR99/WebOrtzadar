<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('ocio.view');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$id_acta = isset($_GET['id_acta']) ? (int)$_GET['id_acta'] : 0;
if ($id_acta <= 0) {
  http_response_code(422);
  echo "id_acta inválido";
  exit;
}

function h($s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

try {
  // acta + grupo + responsable (si existe)
  $sql = "
    SELECT
      a.id, a.id_grupo, a.fecha, a.valoracion,
      g.nombre AS grupo_nombre, g.fecha AS grupo_fecha,
      t.nombre AS resp_nombre, t.apellidos AS resp_apellidos
    FROM AA_Ocio_actas a
    LEFT JOIN AA_Ocio_grupos g ON g.id = a.id_grupo
    LEFT JOIN AA_trabajadores t ON t.id = g.id_responsable
    WHERE a.id = ?
    LIMIT 1
  ";
  $st = $mysql_db->prepare($sql);
  $st->bind_param("i", $id_acta);
  $st->execute();
  $acta = $st->get_result()->fetch_assoc();
  if (!$acta) {
    http_response_code(404);
    echo "Acta no encontrada";
    exit;
  }

  $id_grupo = (int)$acta['id_grupo'];

  // participantes del grupo
  $stP = $mysql_db->prepare("
    SELECT u.id, u.Nombre, u.Apellidos, u.Dni
    FROM AA_Ocio_participantes p
    INNER JOIN AA_usuarios u ON u.id = p.id_usuario
    WHERE p.id_grupo = ?
    ORDER BY u.Apellidos ASC, u.Nombre ASC
  ");
  $stP->bind_param("i", $id_grupo);
  $stP->execute();
  $rsP = $stP->get_result();

  $participantes = [];
  while ($r = $rsP->fetch_assoc()) $participantes[] = $r;

  // presentes
  $presentes = [];
  $stS = $mysql_db->prepare("SELECT id_usuario FROM AA_Ocio_asistencia WHERE id_acta=?");
  $stS->bind_param("i", $id_acta);
  $stS->execute();
  $rsS = $stS->get_result();
  while ($r = $rsS->fetch_assoc()) $presentes[(int)$r['id_usuario']] = true;

  // incidencias
  $incidencias = [];
  $stI = $mysql_db->prepare("
    SELECT fecha, incidencia
    FROM AA_Ocio_incidencias
    WHERE id_acta=?
    ORDER BY fecha ASC, id ASC
  ");
  $stI->bind_param("i", $id_acta);
  $stI->execute();
  $rsI = $stI->get_result();
  while ($r = $rsI->fetch_assoc()) $incidencias[] = $r;

  $grupoNombre = $acta['grupo_nombre'] ?: ("Grupo #".$id_grupo);
  $responsable = trim(($acta['resp_nombre'] ?? '').' '.($acta['resp_apellidos'] ?? '')) ?: '—';

  header('Content-Type: text/html; charset=utf-8');

  ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title><?php echo h($grupoNombre); ?> · Acta <?php echo (int)$acta['id']; ?></title>
  <style>
    body{ font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 28px; color:#0f172a; }
    .top{ display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:18px; }
    h1{ font-size:20px; margin:0 0 6px; }
    .meta{ font-size:13px; color:#334155; line-height:1.4; }
    .card{ border:1px solid #e2e8f0; border-radius:12px; padding:14px 16px; margin: 14px 0; }
    h2{ font-size:15px; margin:0 0 10px; }
    table{ width:100%; border-collapse:collapse; font-size:13px; }
    th,td{ padding:8px 10px; border-bottom:1px solid #e2e8f0; vertical-align:top; }
    th{ text-align:left; background:#f8fafc; }
    .badge{ display:inline-block; padding:2px 8px; border-radius:999px; font-size:12px; background:#e2e8f0; color:#0f172a; }
    .ok{ background:#dcfce7; }
    .no{ background:#fee2e2; }
    .muted{ color:#64748b; }
    @media print {
      body{ margin: 14mm; }
      .noprint{ display:none; }
    }
  </style>
</head>
<body>

<div class="top">
  <div>
    <h1>Acta de actividad · <?php echo h($grupoNombre); ?></h1>
    <div class="meta">
      <div><strong>Fecha actividad:</strong> <?php echo h($acta['fecha'] ?? '—'); ?></div>
      <div><strong>Responsable:</strong> <?php echo h($responsable); ?></div>
      <div class="muted"><strong>ID acta:</strong> <?php echo (int)$acta['id']; ?> · <strong>ID grupo:</strong> <?php echo (int)$id_grupo; ?></div>
    </div>
  </div>

  <div class="noprint">
    <button onclick="window.print()">Imprimir / Guardar PDF</button>
  </div>
</div>

<div class="card">
  <h2>Asistencia</h2>
  <?php if (!count($participantes)): ?>
    <div class="muted">No hay participantes en el grupo.</div>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Participante</th>
          <th>DNI</th>
          <th>Asistencia</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($participantes as $u):
          $idU = (int)$u['id'];
          $nombre = trim(($u['Nombre'] ?? '').' '.($u['Apellidos'] ?? '')) ?: ('Usuario #'.$idU);
          $dni = $u['Dni'] ?? '—';
          $asistio = isset($presentes[$idU]);
        ?>
          <tr>
            <td><?php echo h($nombre); ?></td>
            <td class="muted"><?php echo h($dni); ?></td>
            <td>
              <span class="badge <?php echo $asistio ? 'ok' : 'no'; ?>">
                <?php echo $asistio ? 'Asiste' : 'No asiste'; ?>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Valoración</h2>
  <div><?php echo nl2br(h($acta['valoracion'] ?? '')); ?></div>
  <?php if (!$acta['valoracion']): ?><div class="muted">—</div><?php endif; ?>
</div>

<div class="card">
  <h2>Incidencias</h2>
  <?php if (!count($incidencias)): ?>
    <div class="muted">—</div>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Incidencia</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($incidencias as $i): ?>
          <tr>
            <td class="muted"><?php echo h($i['fecha'] ?? ''); ?></td>
            <td><?php echo nl2br(h($i['incidencia'] ?? '')); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

</body>
</html>
<?php

} catch (Throwable $e) {
  http_response_code(500);
  echo "SQL ERROR: ".$e->getMessage();
}
