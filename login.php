<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/public/php/funciones_sesion.php';
require_once __DIR__ . '/public/php/auth_permisos.php'; // nuevo (recomendado)

asegurar_sesion_iniciada();

if (usuario_esta_autenticado()) {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['usuario'] ?? '');
    $pass = (string)($_POST['password'] ?? '');

    if ($user === '' || $pass === '') {
        $error = 'Usuario o contraseña incorrectos';
    } else {
        // Buscar acceso por correo
        $sql = "SELECT id, correo, pass, id_trabajador, landpage, nombre, apellidos
                FROM AA_accesos
                WHERE correo = ?
                LIMIT 1";
        $stmt = $mysql_db->prepare($sql);
        if (!$stmt) {
            $error = 'Error interno (prepare)';
        } else {
            $stmt->bind_param("s", $user);
            $stmt->execute();
            $res = $stmt->get_result();
            $acceso = $res ? $res->fetch_assoc() : null;
            $stmt->close();

            if (!$acceso || !password_verify($pass, $acceso['pass'])) {
                $error = 'Usuario o contraseña incorrectos';
            } else {
                $accesoId = (int)$acceso['id'];
                // Cargar permisos por puesto (desde el trabajador)
                $pp = cargar_permisos_por_puesto($mysql_db, (int)$acceso['id_trabajador']);

                iniciar_sesion_usuario([
                    'acceso_id'     => $accesoId,
                    'id_trabajador' => isset($acceso['id_trabajador']) ? (int)$acceso['id_trabajador'] : null,
                    'correo'        => $acceso['correo'] ?? '',
                    'nombre'        => $acceso['nombre'] ?? '',
                    'apellidos'     => $acceso['apellidos'] ?? '',
                    'landpage'      => $acceso['landpage'] ?? 'index.php',

                    // NUEVO
                    'puesto_id'     => $pp['puesto_id'],
                    'perms'         => $pp['perms'],

                    // Opcional: para compatibilidad si tenías UI vieja que mira "rol"
                    'rol'           => ($pp['puesto_id'] === 1 ? 'admin' : null),
                ]);
            }
        }
    }
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Login</title>

    <link rel="stylesheet" href="public/css/shared/theme.css">
    <link rel="stylesheet" href="public/css/shared/base.css">
    <link rel="stylesheet" href="public/css/shared/components.css">
    <link rel="stylesheet" href="public/css/apps/login/login.css">
</head>

<body class="login-body">
    <div class="login-wrap">
        <div class="login-card">
            <div class="login-head">
                <div class="login-logo">A</div>
                <div>
                    <h1 class="login-title">ORTZADAR</h1>
                    <p class="login-subtitle">Acceso al panel</p>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="login-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="login-form" autocomplete="off">
                <label class="login-label" for="usuario">Usuario</label>
                <input class="login-input" id="usuario" name="usuario" type="text" placeholder="correo@dominio.com" required>

                <label class="login-label" for="password">Contraseña</label>
                <input class="login-input" id="password" name="password" type="password" required>

                <button class="login-btn" type="submit">Entrar</button>
            </form>

            <div class="login-foot">
                <span>Introduce tu correo y contraseña</span>
            </div>
        </div>
    </div>
</body>

</html>