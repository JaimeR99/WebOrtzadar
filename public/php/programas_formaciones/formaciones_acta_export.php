<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('formaciones.view');

$idActa = (int)($_GET['id_acta'] ?? 0);
if ($idActa <= 0) { http_response_code(400); echo "id_acta inválido"; exit; }

$stmt = $mysql_db->prepare("
  SELECT a.id, a.id_formacion, a.fecha, a.valoracion,
         f.nombre AS formacion_nombre, f.fecha AS formacion_fecha,
         t.nombre AS tipo_nombre
  FROM AA_Formaciones_actas a
  INNER JOIN AA_Formaciones f ON f.id = a.id_formacion
  LEFT JOIN AA_Formaciones_tipos t ON t.id = f.id_tipo
  WHERE a.id = ?
  LIMIT 1
");
if (!$stmt) { http_response_code(500); echo "SQL error"; exit; }
$stmt->bind_param('i', $idActa);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();
if (!$row) { http_response_code(404); echo "Acta no encontrada"; exit; }

$presentes = [];
$st2 = $mysql_db->prepare("
  SELECT u.Nombre, u.Apellidos, u.Dni
  FROM AA_Formaciones_actas_presentes p
  INNER JOIN AA_usuarios u ON u.id = p.id_usuario
  WHERE p.id_acta=?
  ORDER BY u.Apellidos ASC, u.Nombre ASC
");
$st2->bind_param('i', $idActa);
$st2->execute();
$r2 = $st2->get_result();
while ($u = $r2->fetch_assoc()) $presentes[] = $u;
$st2->close();

$incidencias = [];
$st3 = $mysql_db->prepare("
  SELECT incidencia, created_at
  FROM AA_Formaciones_actas_incidencias
  WHERE id_acta=?
  ORDER BY created_at ASC, id ASC
");
$st3->bind_param('i', $idActa);
$st3->execute();
$r3 = $st3->get_result();
while ($x = $r3->fetch_assoc()) $incidencias[] = $x;
$st3->close();

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

?><!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Acta Formación #<?=h($idActa)?></title>
  <style>
    body{ font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; margin: 24px; }
    h1{ margin:0 0 12px; }
    .muted{ opacity:.75; }
    .box{ border:1px solid #ddd; border-radius:12px; padding:12px 14px; margin:12px 0; }
    .grid{ display:grid; grid-template-columns: 1fr 1fr; gap: 10px 16px; }
    ul{ margin:8px 0 0 18px; }
    @media print { body{ margin: 0; } }
  </style>
</head>
<body>
  <h1>Acta de formación</h1>
  <div class="muted">
    Acta #<?=h($row['id'])?> · Fecha acta: <?=h($row['fecha'] ?? '—')?>
  </div>

  <div class="box">
    <div class="grid">
      <div><b>Formación</b><br><?=h($row['formacion_nombre'])?></div>
      <div><b>Tipo</b><br><?=h($row['tipo_nombre'] ?? '—')?></div>
      <div><b>Fecha formación</b><br><?=h($row['formacion_fecha'] ?? '—')?></div>
      <div><b>Valoración</b><br><?=nl2br(h($row['valoracion'] ?? ''))?></div>
    </div>
  </div>

  <div class="box">
    <b>Asistentes (<?=count($presentes)?>)</b>
    <?php if (!$presentes): ?>
      <div class="muted">—</div>
    <?php else: ?>
      <ul>
        <?php foreach ($presentes as $p): ?>
          <li><?=h(trim(($p['Apellidos'] ?? '').' '.($p['Nombre'] ?? '')))?> <span class="muted">(<?=h($p['Dni'] ?? '—')?>)</span></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <div class="box">
    <b>Incidencias</b>
    <?php if (!$incidencias): ?>
      <div class="muted">—</div>
    <?php else: ?>
      <ul>
        <?php foreach ($incidencias as $i): ?>
          <li><span class="muted"><?=h($i['created_at'] ?? '')?></span> — <?=h($i['incidencia'] ?? '')?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <script>window.print();</script>
</body>
</html>
