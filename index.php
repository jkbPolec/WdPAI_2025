<?php

session_set_cookie_params([
    'lifetime' => 1800,        // 30 minut
    'path' => '/',
    'domain' => '', 
    'secure' => true,         // Tylko przez HTTPS (Cookie Secure)
    'httponly' => true,       // JavaScript nie ma dostępu do ciasteczka (HttpOnly)
    'samesite' => 'Lax'       // Ochrona przed CSRF (SameSite)
]);

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'Routing.php';

$path = trim($_SERVER['REQUEST_URI'], '/');
$path = parse_url($path, PHP_URL_PATH);

Routing::run($path);
