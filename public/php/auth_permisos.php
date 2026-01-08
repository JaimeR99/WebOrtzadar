<?php
declare(strict_types=1);

/**
 * Devuelve puesto_id y perms[] para un trabajador.
 */
function cargar_permisos_por_puesto(mysqli $db, int $idTrabajador): array
{
    // 1) Obtener puesto del trabajador
    $puestoId = null;

    if ($stmt = $db->prepare("SELECT id_puesto FROM AA_trabajadores WHERE id = ? LIMIT 1")) {
        $stmt->bind_param("i", $idTrabajador);
        $stmt->execute();
        $stmt->bind_result($pid);
        if ($stmt->fetch()) {
            $puestoId = (int)$pid;
        }
        $stmt->close();
    }

    if (!$puestoId) {
        return ['puesto_id' => null, 'perms' => []];
    }

    // 2) Permisos del puesto
    $perms = [];

    $sql = "
        SELECT DISTINCT p.code
        FROM AA_puesto_permissions pp
        JOIN AA_permissions p ON p.id = pp.permission_id
        WHERE pp.puesto_id = ?
    ";

    if ($stmt = $db->prepare($sql)) {
        $stmt->bind_param("i", $puestoId);
        $stmt->execute();
        $stmt->bind_result($code);
        while ($stmt->fetch()) {
            $perms[] = $code;
        }
        $stmt->close();
    }

    return ['puesto_id' => $puestoId, 'perms' => $perms];
}
