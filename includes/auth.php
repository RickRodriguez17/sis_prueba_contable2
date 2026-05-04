<?php
/**
 * Control de sesión y roles.
 *
 * Incluir este archivo al inicio de cualquier página protegida.
 * Para páginas que requieran rol administrador, llamar requireAdmin().
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Exige que exista una sesión activa. Si no hay, redirige a login.
 */
function requireLogin() {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Exige que el usuario sea administrador. Si no lo es, muestra error.
 */
function requireAdmin() {
    requireLogin();
    if (($_SESSION['user_rol'] ?? '') !== 'administrador') {
        http_response_code(403);
        echo '<h2 style="font-family:sans-serif;color:#c00;padding:30px">';
        echo 'Acceso denegado: se requiere rol de administrador.';
        echo '</h2>';
        echo '<p style="font-family:sans-serif;padding:0 30px">';
        echo '<a href="' . BASE_URL . '/dashboard.php">Volver al panel</a></p>';
        exit;
    }
}

/**
 * Indica si el usuario actual es administrador.
 */
function esAdmin() {
    return ($_SESSION['user_rol'] ?? '') === 'administrador';
}

requireLogin();
