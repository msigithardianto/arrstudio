<?php
// index.php — front controller

// Setup session SEBELUM apapun
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Lax',
]);

require_once __DIR__ . '/app/bootstrap.php';

$router = new Router();
require_once BASE_PATH . '/app/config/routes.php';
$router->dispatch();