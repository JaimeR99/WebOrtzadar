<?php
// public/php/funciones_sesion.php

declare(strict_types=1);

/**
 * Gestión mínima de sesión:
 * - Arranca la sesión si no está iniciada
 * - Funciones para login/logout/check
 */

function asegurar_sesion_iniciada(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        // Seguridad básica de cookies (puedes ajustar a tu entorno)
        $params = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => $params['lifetime'],
            'path'     => $params['path'] ?? '/',
            'domain'   => $params['domain'] ?? '',
            'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }
}

/**
 * Devuelve true si el usuario está autenticado.
 * Se basa en un flag sencillo en $_SESSION.
 */
function usuario_esta_autenticado(): bool
{
    asegurar_sesion_iniciada();
    return !empty($_SESSION['auth']) && $_SESSION['auth'] === true;
}

/**
 * Marca al usuario como autenticado.
 * (Más adelante puedes guardar id usuario, roles, etc.)
 */
function iniciar_sesion_usuario(array $infoUsuario = []): void
{
    asegurar_sesion_iniciada();

    $_SESSION['auth'] = true;

    // Mantener lo que ya hubiera y mezclar con lo nuevo
    $actual = $_SESSION['usuario'] ?? [];

    $_SESSION['usuario'] = array_merge($actual, [
        // campos legacy (para no romper nada)
        'nombre' => $infoUsuario['nombre'] ?? ($actual['nombre'] ?? 'Usuario'),
        'email'  => $infoUsuario['email']  ?? ($actual['email'] ?? null),
        'rol'    => $infoUsuario['rol']    ?? ($actual['rol'] ?? null),
    ], $infoUsuario);
}


/**
 * Cierra sesión.
 */
function cerrar_sesion_usuario(): void
{
    asegurar_sesion_iniciada();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'] ?? '/', $params['domain'] ?? '');
    }

    session_destroy();
}



function permisos_usuario(): array {
    return $_SESSION['usuario']['perms'] ?? [];
}

function tiene_permiso(string $perm): bool {
    return in_array($perm, permisos_usuario(), true);
}

function require_login_json(): void {
    if (!usuario_esta_autenticado()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'NO_AUTH']);
        exit;
    }
}

function require_perm_json(string $perm): void {
    require_login_json();
    if (!tiene_permiso($perm)) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'NO_PERMISSION', 'required' => $perm]);
        exit;
    }
}

function require_login_page(): void {
    if (!usuario_esta_autenticado()) {
        header('Location: ' . (APP_BASE_PATH ?: '') . '/login.php');
        exit;
    }
}

function require_perm_page(string $perm): void {
    require_login_page();
    if (!tiene_permiso($perm)) {
        header('Location: ' . (APP_BASE_PATH ?: '') . '/index.php?e=403');
        exit;
    }
}
