<?php
// app/bootstrap.php — bootstrap untuk semua entry point

define('BASE_PATH', dirname(__DIR__));
define('VIEW_PATH', BASE_PATH . '/app/views');
define('LIB_PATH',  BASE_PATH . '/app/libraries');

// Deteksi base URL (/ArrStudioWeb atau '')
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
define('BASE_URL', rtrim($scriptDir, '/'));

// ============================================================
// SESSION — HARUS START SEBELUM APAPUN
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    // Konfigurasi cookie aman + kompatibel OAuth
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');  // penting untuk OAuth redirect

    // (opsional) kalau HTTPS: ini_set('session.cookie_secure', '1');

    session_start();
}

// Autoload class
spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . '/app/core/'        . $class . '.php',
        BASE_PATH . '/app/libraries/'   . $class . '.php',
        BASE_PATH . '/app/controllers/' . $class . '.php',
    ];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Helper global
require_once BASE_PATH . '/app/helpers/functions.php';