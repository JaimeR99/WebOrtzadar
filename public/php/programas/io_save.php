<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../funciones_sesion.php';

require_perm_json('informacion_orientacion.edit');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function out(bool $ok, $data = null, ?string $error = null, int $code = 200): void
{
  http_response_code($code);
  echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
  exit;
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) out(false, null, 'JSON inválido', 400);

$id_usuario = isset($body['id_usuario']) ? (int)$body['id_usuario'] : 0;
$u  = is_array($body['usuario'] ?? null) ? $body['usuario'] : [];
$io = is_array($body['io'] ?? null) ? $body['io'] : [];
$diagIn = is_array($body['diag'] ?? null) ? $body['diag'] : [];

/* =========================
   Usuario (AA_usuarios) - todos los campos
========================= */
$Nombre   = trim((string)($u['Nombre'] ?? ''));
$Apellidos = trim((string)($u['Apellidos'] ?? ''));
$Dni      = trim((string)($u['Dni'] ?? ''));

$Sexo = trim((string)($u['Sexo'] ?? ''));
$Direccion = trim((string)($u['Direccion'] ?? ''));
$Codigo_Postal = trim((string)($u['Codigo_Postal'] ?? ''));

$Fecha_Nacimiento = trim((string)($u['Fecha_Nacimiento'] ?? '')); // YYYY-MM-DD
$Fecha_Alta = trim((string)($u['Fecha_Alta'] ?? ''));             // YYYY-MM-DD HH:MM:SS

$Telefono_Usuario = trim((string)($u['Telefono_Usuario'] ?? ''));
$Telefono_Familia1 = trim((string)($u['Telefono_Familia1'] ?? ''));
$Telefono_Familia2 = trim((string)($u['Telefono_Familia2'] ?? ''));
$Telefono_Servicios_Sociales = trim((string)($u['Telefono_Servicios_Sociales'] ?? ''));
$Telefono_Trabajadora_Social = trim((string)($u['Telefono_Trabajadora_Social'] ?? ''));
$Telefono_Centro_Salud = trim((string)($u['Telefono_Centro_Salud'] ?? ''));
$Telefono_Medico_Cavecera = trim((string)($u['Telefono_Medico_Cavecera'] ?? ''));
$Telefono_Salud_Mental = trim((string)($u['Telefono_Salud_Mental'] ?? ''));
$Telefono_Referente_Salud = trim((string)($u['Telefono_Referente_Salud'] ?? ''));
$Telefono_Referente_Formativo = trim((string)($u['Telefono_Referente_Formativo'] ?? ''));
$Telefono_Otros1 = trim((string)($u['Telefono_Otros1'] ?? ''));
$Telefono_Otros2 = trim((string)($u['Telefono_Otros2'] ?? ''));

$Correo = trim((string)($u['Correo'] ?? ''));
$CCC = trim((string)($u['CCC'] ?? ''));
$Nacionalidad = trim((string)($u['Nacionalidad'] ?? '0'));
$N_TIS = (string)($u['N_TIS'] ?? '');

$Nivel_Estudios = (int)($u['Nivel_Estudios'] ?? 0);
$Tipo_Socio = (int)($u['Tipo_Socio'] ?? 0);
$ID_Situacion_Administrativa = (int)($u['ID_Situacion_Administrativa'] ?? 0);
$ID_Via_Comunicacion = (int)($u['ID_Via_Comunicacion'] ?? 0);

if ($Nombre === '' || $Dni === '') out(false, null, 'Nombre y DNI son obligatorios', 422);
if ($Fecha_Nacimiento === '') out(false, null, 'Fecha de nacimiento es obligatoria', 422);
if ($Fecha_Alta === '') $Fecha_Alta = date('Y-m-d H:i:s');

/* =========================
   IO (1 por usuario)
========================= */
$io_fecha = trim((string)($io['fecha'] ?? ''));
if ($io_fecha === '') $io_fecha = date('Y-m-d H:i:s');

$id_tipo_atencion = trim((string)($io['id_tipo_atencion'] ?? ''));
$id_orientado_por = trim((string)($io['id_orientado_por'] ?? ''));
$id_realizado_por = trim((string)($io['id_realizado_por'] ?? ''));

$tipo_demanda     = trim((string)($io['tipo_demanda'] ?? ''));
$lugar_entrevista = trim((string)($io['lugar_entrevista'] ?? ''));

$composicion_familiar = $io['composicion_familiar'] ?? null;
$h_escolar_formacion  = $io['h_escolar_formacion'] ?? null;
$relaciones_sociales  = $io['relaciones_sociales'] ?? null;
$salud                = $io['salud'] ?? null;
$autonomia            = $io['autonomia'] ?? null;

$red_apo_ss_base = $io['red_apo_ss_base'] ?? null;
$red_apo_csm     = $io['red_apo_csm'] ?? null;
$red_apo_familia = $io['red_apo_familia'] ?? null;
$red_apo_otros   = $io['red_apo_otros'] ?? null;

$demanda = $io['demanda'] ?? null;
$acuerdo = $io['acuerdo'] ?? null;

/* =========================
   Diagnósticos input
========================= */
$discIn = is_array($diagIn['discapacidad'] ?? null) ? $diagIn['discapacidad'] : [];
$depIn  = is_array($diagIn['dependencia'] ?? null) ? $diagIn['dependencia'] : [];
$excIn  = is_array($diagIn['exclusion'] ?? null) ? $diagIn['exclusion'] : [];

$mysql_db->begin_transaction();

try {
  /* =========================================================
     1) USUARIO: localizar/crear/actualizar
     ========================================================= */
  if ($id_usuario <= 0) {
    // localizar por DNI
    $stmtFind = $mysql_db->prepare("SELECT id FROM AA_usuarios WHERE Dni=? LIMIT 1");
    $stmtFind->bind_param("s", $Dni);
    $stmtFind->execute();
    $found = $stmtFind->get_result()->fetch_assoc();

    if ($found) {
      $id_usuario = (int)$found['id'];
    } else {
      $rs = $mysql_db->query("SELECT COALESCE(MAX(id),0)+1 AS next_id FROM AA_usuarios FOR UPDATE");
      $row = $rs->fetch_assoc();
      $id_usuario = (int)$row['next_id'];

      $stmtIns = $mysql_db->prepare("
        INSERT INTO AA_usuarios (
          id, Nombre, Apellidos, Sexo, Direccion, Codigo_Postal, Dni, Fecha_Nacimiento,
          Telefono_Usuario, Telefono_Familia1, Telefono_Familia2,
          Telefono_Servicios_Sociales, Telefono_Trabajadora_Social,
          Telefono_Centro_Salud, Telefono_Medico_Cavecera, Telefono_Salud_Mental,
          Telefono_Referente_Salud, Telefono_Referente_Formativo,
          Telefono_Otros1, Telefono_Otros2,
          Correo, CCC, Nivel_Estudios, Tipo_Socio, Fecha_Alta,
          ID_Situacion_Administrativa, Nacionalidad, N_TIS, ID_Via_Comunicacion,
          ID_DIAG_Discapacidad, ID_DIAG_Dependencia, ID_DIAG_Exclusion
        ) VALUES (
          ?, ?, ?, ?, ?, ?, ?, ?,
          ?, ?, ?,
          ?, ?,
          ?, ?, ?,
          ?, ?,
          ?, ?,
          ?, ?, ?, ?, ?,
          ?, ?, ?, ?,
          NULL, NULL, NULL
        )
      ");

      // ✅ CORREGIDO: 29 tipos para 29 variables
      // 1 int (id) + 21 strings + 2 ints + 1 string + 1 int + 2 strings + 1 int
      $stmtIns->bind_param(
        "isssssss" . "ssssssssssssss" . "iisissi",
        $id_usuario,
        $Nombre,
        $Apellidos,
        $Sexo,
        $Direccion,
        $Codigo_Postal,
        $Dni,
        $Fecha_Nacimiento,
        $Telefono_Usuario,
        $Telefono_Familia1,
        $Telefono_Familia2,
        $Telefono_Servicios_Sociales,
        $Telefono_Trabajadora_Social,
        $Telefono_Centro_Salud,
        $Telefono_Medico_Cavecera,
        $Telefono_Salud_Mental,
        $Telefono_Referente_Salud,
        $Telefono_Referente_Formativo,
        $Telefono_Otros1,
        $Telefono_Otros2,
        $Correo,
        $CCC,
        $Nivel_Estudios,
        $Tipo_Socio,
        $Fecha_Alta,
        $ID_Situacion_Administrativa,
        $Nacionalidad,
        $N_TIS,
        $ID_Via_Comunicacion
      );
      $stmtIns->execute();
    }
  }

  // update usuario (sin tocar diag aquí; diag lo gestionamos luego)
  $stmtUp = $mysql_db->prepare("
    UPDATE AA_usuarios
    SET
      Nombre=?, Apellidos=?, Sexo=?, Direccion=?, Codigo_Postal=?, Dni=?, Fecha_Nacimiento=?,
      Telefono_Usuario=?, Telefono_Familia1=?, Telefono_Familia2=?,
      Telefono_Servicios_Sociales=?, Telefono_Trabajadora_Social=?,
      Telefono_Centro_Salud=?, Telefono_Medico_Cavecera=?, Telefono_Salud_Mental=?,
      Telefono_Referente_Salud=?, Telefono_Referente_Formativo=?,
      Telefono_Otros1=?, Telefono_Otros2=?,
      Correo=?, CCC=?, Nivel_Estudios=?, Tipo_Socio=?, Fecha_Alta=?,
      ID_Situacion_Administrativa=?, Nacionalidad=?, N_TIS=?, ID_Via_Comunicacion=?
    WHERE id=?
    LIMIT 1
  ");

  // ✅ CORREGIDO: 29 tipos para 29 variables (21s + i i s i s s i i)
  $stmtUp->bind_param(
    "sssssssssssssssssssss" . "iisissii",
    $Nombre,
    $Apellidos,
    $Sexo,
    $Direccion,
    $Codigo_Postal,
    $Dni,
    $Fecha_Nacimiento,
    $Telefono_Usuario,
    $Telefono_Familia1,
    $Telefono_Familia2,
    $Telefono_Servicios_Sociales,
    $Telefono_Trabajadora_Social,
    $Telefono_Centro_Salud,
    $Telefono_Medico_Cavecera,
    $Telefono_Salud_Mental,
    $Telefono_Referente_Salud,
    $Telefono_Referente_Formativo,
    $Telefono_Otros1,
    $Telefono_Otros2,
    $Correo,
    $CCC,
    $Nivel_Estudios,
    $Tipo_Socio,
    $Fecha_Alta,
    $ID_Situacion_Administrativa,
    $Nacionalidad,
    $N_TIS,
    $ID_Via_Comunicacion,
    $id_usuario
  );
  $stmtUp->execute();

  // Leer FKs actuales (bloqueo lógico en transacción)
  $stFK = $mysql_db->prepare("
    SELECT ID_DIAG_Discapacidad, ID_DIAG_Dependencia, ID_DIAG_Exclusion
    FROM AA_usuarios
    WHERE id=?
    LIMIT 1
  ");
  $stFK->bind_param("i", $id_usuario);
  $stFK->execute();
  $fkRow = $stFK->get_result()->fetch_assoc() ?: [];

  $fkDisc = !empty($fkRow['ID_DIAG_Discapacidad']) ? (int)$fkRow['ID_DIAG_Discapacidad'] : null;
  $fkDep  = !empty($fkRow['ID_DIAG_Dependencia'])  ? (int)$fkRow['ID_DIAG_Dependencia']  : null;
  $fkExc  = !empty($fkRow['ID_DIAG_Exclusion'])    ? (int)$fkRow['ID_DIAG_Exclusion']    : null;

  /* =========================================================
     2) DIAGNÓSTICOS
     ========================================================= */

  // ---- Discapacidad ----
  $discEnabled = (bool)($discIn['enabled'] ?? false);

  if (!$discEnabled) {
    if ($fkDisc !== null) {

      $upd = $mysql_db->prepare("UPDATE AA_usuarios SET ID_DIAG_Discapacidad=NULL WHERE id=? LIMIT 1");
      $upd->bind_param("i", $id_usuario);
      $upd->execute();

      $chk = $mysql_db->prepare("SELECT COUNT(*) AS c FROM AA_usuarios WHERE ID_DIAG_Discapacidad=?");
      $chk->bind_param("i", $fkDisc);
      $chk->execute();
      $c = (int)($chk->get_result()->fetch_assoc()['c'] ?? 0);

      if ($c === 0) {
        $del = $mysql_db->prepare("DELETE FROM AA_Discapacidad WHERE Id=? LIMIT 1");
        $del->bind_param("i", $fkDisc);
        $del->execute();
      }

      $fkDisc = null;
    }
  } else {
    $Porcentaje = (int)($discIn['Porcentaje'] ?? 0);
    $Diagnostico = trim((string)($discIn['Diagnostico'] ?? ''));
    $FR = trim((string)($discIn['Fecha_Reconocimiento'] ?? ''));
    $FC = trim((string)($discIn['Fecha_Caducidad'] ?? ''));
    $Desc = trim((string)($discIn['Descripcion'] ?? ''));

    if ($Diagnostico === '' || $FR === '' || $FC === '') out(false, null, 'Discapacidad: faltan campos obligatorios', 422);

    if ($fkDisc === null) {
      $ins = $mysql_db->prepare("
        INSERT INTO AA_Discapacidad (Porcentaje, Fecha_Reconocimiento, Fecha_Caducidad, Diagnostico, Descripcion)
        VALUES (?, ?, ?, ?, ?)
      ");
      $ins->bind_param("issss", $Porcentaje, $FR, $FC, $Diagnostico, $Desc);
      $ins->execute();
      $fkDisc = (int)$mysql_db->insert_id;

      $upd = $mysql_db->prepare("UPDATE AA_usuarios SET ID_DIAG_Discapacidad=? WHERE id=? LIMIT 1");
      $upd->bind_param("ii", $fkDisc, $id_usuario);
      $upd->execute();
    } else {
      $up = $mysql_db->prepare("
        UPDATE AA_Discapacidad
        SET Porcentaje=?, Fecha_Reconocimiento=?, Fecha_Caducidad=?, Diagnostico=?, Descripcion=?
        WHERE Id=?
        LIMIT 1
      ");
      $up->bind_param("issssi", $Porcentaje, $FR, $FC, $Diagnostico, $Desc, $fkDisc);
      $up->execute();
    }
  }

  // ---- Dependencia ----
  $depEnabled = (bool)($depIn['enabled'] ?? false);

  if (!$depEnabled) {
    if ($fkDep !== null) {

      // 1) Primero soltamos la FK del usuario
      $upd = $mysql_db->prepare("UPDATE AA_usuarios SET ID_DIAG_Dependencia=NULL WHERE id=? LIMIT 1");
      $upd->bind_param("i", $id_usuario);
      $upd->execute();

      // 2) Solo borramos si ya no lo referencia nadie
      $chk = $mysql_db->prepare("SELECT COUNT(*) AS c FROM AA_usuarios WHERE ID_DIAG_Dependencia=?");
      $chk->bind_param("i", $fkDep);
      $chk->execute();
      $c = (int)($chk->get_result()->fetch_assoc()['c'] ?? 0);

      if ($c === 0) {
        $del = $mysql_db->prepare("DELETE FROM AA_Dependencia WHERE Id=? LIMIT 1");
        $del->bind_param("i", $fkDep);
        $del->execute();
      }

      $fkDep = null;
    }
  } else {
    $Grado = trim((string)($depIn['Grado'] ?? ''));
    $FR = trim((string)($depIn['Fecha_Reconocimiento'] ?? ''));
    $FC = trim((string)($depIn['Fecha_Caducidad'] ?? ''));
    $Desc = trim((string)($depIn['Descripcion'] ?? ''));

    if ($Grado === '' || $FR === '' || $FC === '') out(false, null, 'Dependencia: faltan campos obligatorios', 422);

    if ($fkDep === null) {
      $ins = $mysql_db->prepare("
        INSERT INTO AA_Dependencia (Grado, Fecha_Reconocimiento, Fecha_Caducidad, Descripcion)
        VALUES (?, ?, ?, ?)
      ");
      $ins->bind_param("ssss", $Grado, $FR, $FC, $Desc);
      $ins->execute();
      $fkDep = (int)$mysql_db->insert_id;

      $upd = $mysql_db->prepare("UPDATE AA_usuarios SET ID_DIAG_Dependencia=? WHERE id=? LIMIT 1");
      $upd->bind_param("ii", $fkDep, $id_usuario);
      $upd->execute();
    } else {
      $up = $mysql_db->prepare("
        UPDATE AA_Dependencia
        SET Grado=?, Fecha_Reconocimiento=?, Fecha_Caducidad=?, Descripcion=?
        WHERE Id=?
        LIMIT 1
      ");
      $up->bind_param("ssssi", $Grado, $FR, $FC, $Desc, $fkDep);
      $up->execute();
    }
  }

  // ---- Exclusión ----
  $excEnabled = (bool)($excIn['enabled'] ?? false);

  if (!$excEnabled) {
    if ($fkExc !== null) {

      $upd = $mysql_db->prepare("UPDATE AA_usuarios SET ID_DIAG_Exclusion=NULL WHERE id=? LIMIT 1");
      $upd->bind_param("i", $id_usuario);
      $upd->execute();

      $chk = $mysql_db->prepare("SELECT COUNT(*) AS c FROM AA_usuarios WHERE ID_DIAG_Exclusion=?");
      $chk->bind_param("i", $fkExc);
      $chk->execute();
      $c = (int)($chk->get_result()->fetch_assoc()['c'] ?? 0);

      if ($c === 0) {
        $del = $mysql_db->prepare("DELETE FROM AA_Exclusion WHERE Id=? LIMIT 1");
        $del->bind_param("i", $fkExc);
        $del->execute();
      }

      $fkExc = null;
    }
  } else {
    $Tipo = trim((string)($excIn['Tipo'] ?? ''));
    $FRi = $excIn['Fecha_Reconocimiento'] ?? null; // int(YYYYMMDD)
    $FCi = $excIn['Fecha_Caducidad'] ?? null;
    $Desc = trim((string)($excIn['Descripcion'] ?? ''));

    if ($Tipo === '' || $FRi === null || $FCi === null) out(false, null, 'Exclusión: faltan campos obligatorios', 422);

    $FRi = (int)$FRi;
    $FCi = (int)$FCi;

    if ($fkExc === null) {
      $rs = $mysql_db->query("SELECT COALESCE(MAX(Id),0)+1 AS next_id FROM AA_Exclusion FOR UPDATE");
      $row = $rs->fetch_assoc();
      $fkExc = (int)$row['next_id'];

      $ins = $mysql_db->prepare("
        INSERT INTO AA_Exclusion (Id, Fecha_Reconocimiento, Fecha_Caducidad, Tipo, Descripcion)
        VALUES (?, ?, ?, ?, ?)
      ");
      $ins->bind_param("iiiss", $fkExc, $FRi, $FCi, $Tipo, $Desc);
      $ins->execute();

      $upd = $mysql_db->prepare("UPDATE AA_usuarios SET ID_DIAG_Exclusion=? WHERE id=? LIMIT 1");
      $upd->bind_param("ii", $fkExc, $id_usuario);
      $upd->execute();
    } else {
      $up = $mysql_db->prepare("
        UPDATE AA_Exclusion
        SET Fecha_Reconocimiento=?, Fecha_Caducidad=?, Tipo=?, Descripcion=?
        WHERE Id=?
        LIMIT 1
      ");
      $up->bind_param("iissi", $FRi, $FCi, $Tipo, $Desc, $fkExc);
      $up->execute();
    }
  }

  /* =========================================================
     3) IO: 1 por usuario -> update / insert por id_usuario
     ========================================================= */
  $stmtIoFind = $mysql_db->prepare("SELECT id FROM AA_informacion_y_orientacion WHERE id_usuario=? LIMIT 1");
  $stmtIoFind->bind_param("i", $id_usuario);
  $stmtIoFind->execute();
  $ioRow = $stmtIoFind->get_result()->fetch_assoc();

  if ($ioRow) {
    $id_io = (int)$ioRow['id'];

    $stmtIoUp = $mysql_db->prepare("
      UPDATE AA_informacion_y_orientacion
      SET
        fecha=?,
        id_tipo_atencion = NULLIF(?, ''),
        id_orientado_por = NULLIF(?, ''),
        tipo_demanda=?,
        lugar_entrevista=?,
        id_realizado_por = NULLIF(?, ''),
        composicion_familiar=?,
        h_escolar_formacion=?,
        relaciones_sociales=?,
        salud=?,
        autonomia=?,
        red_apo_ss_base=?,
        red_apo_csm=?,
        red_apo_familia=?,
        red_apo_otros=?,
        demanda=?,
        acuerdo=?
      WHERE id_usuario=?
      LIMIT 1
    ");
    $stmtIoUp->bind_param(
      "sssssssssssssssssi",
      $io_fecha,
      $id_tipo_atencion,
      $id_orientado_por,
      $tipo_demanda,
      $lugar_entrevista,
      $id_realizado_por,
      $composicion_familiar,
      $h_escolar_formacion,
      $relaciones_sociales,
      $salud,
      $autonomia,
      $red_apo_ss_base,
      $red_apo_csm,
      $red_apo_familia,
      $red_apo_otros,
      $demanda,
      $acuerdo,
      $id_usuario
    );
    $stmtIoUp->execute();
  } else {
    $stmtIoIns = $mysql_db->prepare("
      INSERT INTO AA_informacion_y_orientacion (
        id_usuario,
        fecha,
        id_tipo_atencion,
        id_orientado_por,
        tipo_demanda,
        lugar_entrevista,
        id_realizado_por,
        composicion_familiar,
        h_escolar_formacion,
        relaciones_sociales,
        salud,
        autonomia,
        red_apo_ss_base,
        red_apo_csm,
        red_apo_familia,
        red_apo_otros,
        demanda,
        acuerdo
      ) VALUES (
        ?,
        ?,
        NULLIF(?, ''),
        NULLIF(?, ''),
        ?,
        ?,
        NULLIF(?, ''),
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?
      )
    ");
    $stmtIoIns->bind_param(
      "isssssssssssssssss",
      $id_usuario,
      $io_fecha,
      $id_tipo_atencion,
      $id_orientado_por,
      $tipo_demanda,
      $lugar_entrevista,
      $id_realizado_por,
      $composicion_familiar,
      $h_escolar_formacion,
      $relaciones_sociales,
      $salud,
      $autonomia,
      $red_apo_ss_base,
      $red_apo_csm,
      $red_apo_familia,
      $red_apo_otros,
      $demanda,
      $acuerdo
    );
    $stmtIoIns->execute();
    $id_io = (int)$mysql_db->insert_id;
  }

  $mysql_db->commit();

  out(true, [
    'id_usuario' => $id_usuario,
    'id_io' => $id_io,
    'diag_ids' => [
      'ID_DIAG_Discapacidad' => $fkDisc,
      'ID_DIAG_Dependencia' => $fkDep,
      'ID_DIAG_Exclusion' => $fkExc
    ]
  ]);
} catch (mysqli_sql_exception $e) {
  $mysql_db->rollback();
  out(false, null, 'SQL ERROR: ' . $e->getMessage(), 500);
} catch (Throwable $e) {
  $mysql_db->rollback();
  out(false, null, 'Error guardando: ' . $e->getMessage(), 500);
}
