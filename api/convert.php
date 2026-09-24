<?php
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);
ob_start();

header('Content-Type: application/json; charset=utf-8');

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level() > 0) ob_end_clean();
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode([
            'error' => 'PHP Fatal: ' . $err['message'],
            'file'  => $err['file'],
            'line'  => $err['line'],
        ]);
    }
});

// ============================================================
// BOOTSTRAP — biar bisa akses Auth + url() + cookie session
// ============================================================
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../lib/Parser.php';
require_once __DIR__ . '/../lib/Naming.php';

// ============================================================
// GUEST LIMIT — server-side gate
// ============================================================
const GUEST_MAX_USES = 3;
const GUEST_COOKIE   = 'arrr_guest_uses_c';

function guest_get_uses(): int {
    return (int)($_COOKIE[GUEST_COOKIE] ?? 0);
}

function guest_increment_uses(): int {
    $next = guest_get_uses() + 1;
    // Set cookie — valid 1 tahun. httponly=false biar JS bisa baca juga
    setcookie(GUEST_COOKIE, (string)$next, [
        'expires'  => time() + 365 * 24 * 3600,
        'path'     => '/',
        'httponly' => false,
        'samesite' => 'Lax',
    ]);
    $_COOKIE[GUEST_COOKIE] = (string)$next;
    return $next;
}

// Cek & increment guest counter (kalau belum login)
if (!Auth::check()) {
    $uses = guest_get_uses();

    if ($uses >= GUEST_MAX_USES) {
        http_response_code(429);
        echo json_encode([
            'error'        => 'guest_limit_exceeded',
            'message'      => 'Kuota gratis habis. Login untuk lanjut.',
            'uses'         => $uses,
            'max'          => GUEST_MAX_USES,
            'login_url'    => url('login'),
            'auth_google'  => url('auth_google'),
            'auth_discord' => url('auth_discord'),
        ]);
        exit;
    }

    // Increment SEBELUM parsing — biar request yang gagal pun tetap dihitung
    guest_increment_uses();
}

// ============================================================
// PARSE REQUEST
// ============================================================
$raw = file_get_contents('php://input');
if ($raw === false || $raw === '') {
    echo json_encode(['error' => 'Empty request body']);
    exit;
}

$input = json_decode($raw, true);
if (!is_array($input)) {
    echo json_encode(['error' => 'Invalid JSON: ' . json_last_error_msg()]);
    exit;
}
if (empty($input['html'])) {
    echo json_encode(['error' => 'Empty input (html field missing)']);
    exit;
}

$html    = (string)$input['html'];
$rectMap = is_array($input['rectMap'] ?? null) ? $input['rectMap'] : [];

if (strlen($html) > 500_000) {
    echo json_encode(['error' => 'HTML terlalu besar (>500KB)']);
    exit;
}

// ============================================================
// PARSE HTML → NODES
// ============================================================
try {
    $parser = new Parser(800, 600);
    $nodes  = $parser->parse($html, $rectMap);

    if (!is_array($nodes) || empty($nodes)) {
        echo json_encode([
            'error' => 'Parser menghasilkan 0 nodes. Cek rectMap / struktur HTML.',
            'debug' => ['htmlLen' => strlen($html), 'rectCount' => count($rectMap)],
        ]);
        exit;
    }

    try {
        Naming::assignProfessionalNames($nodes);
    } catch (Throwable $e) {
        error_log('[convert] Naming error: ' . $e->getMessage());
    }

    // Kirim juga sisa kuota guest biar JS bisa sync
    $guestInfo = null;
    if (!Auth::check()) {
        $guestInfo = [
            'uses'      => guest_get_uses(),
            'remaining' => max(0, GUEST_MAX_USES - guest_get_uses()),
            'max'       => GUEST_MAX_USES,
        ];
    }

    echo json_encode([
        'nodes' => $nodes,
        'guest' => $guestInfo,   // ← null kalau sudah login
        'debug' => [
            'htmlLen'     => strlen($html),
            'rectCount'   => count($rectMap),
            'nodeCount'   => count($nodes),
            'hiddenCount' => count(array_filter($nodes, fn($n) => !empty($n['selfHidden']))),
            'buttonCount' => count(array_filter($nodes, fn($n) => in_array($n['robloxClass'] ?? '', ['TextButton','ImageButton']))),
            'actionCount' => count(array_filter($nodes, fn($n) => !empty($n['action']))),
            'targetCount' => count(array_filter($nodes, fn($n) => !empty($n['target']))),
        ],
    ]);
} catch (Throwable $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'file'  => $e->getFile(),
        'line'  => $e->getLine(),
    ]);
}