<?php
// app/core/Auth.php

class Auth
{
    private static string $userFile = '';
    private const SESSION_KEY = 'user_id';

    private static function init(): void
    {
        if (self::$userFile === '') {
            self::$userFile = BASE_PATH . '/storage/users.json';
        }
    }

    /**
     * Start session dengan config aman (idempotent).
     */
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Cookie aman + kompatibel OAuth redirect
            if (!headers_sent()) {
                ini_set('session.cookie_httponly', '1');
                ini_set('session.use_only_cookies', '1');
                ini_set('session.cookie_samesite', 'Lax');
                // Kalau HTTPS: ini_set('session.cookie_secure', '1');
            }
            session_start();
        }
    }

    /**
     * Login / register via OAuth provider.
     */
    public static function loginOAuth(string $provider, array $profile): array
    {
        self::init();
        self::startSession(); // ← WAJIB: pastikan session aktif sebelum set $_SESSION

        $providerId = trim((string)($profile['id'] ?? ''));
        $email      = trim((string)($profile['email'] ?? ''));
        $name       = trim((string)($profile['name'] ?? $profile['username'] ?? 'User'));
        $avatar     = trim((string)($profile['avatar_url'] ?? $profile['picture'] ?? ''));

        if ($providerId === '') {
            return ['ok' => false, 'error' => 'Provider ID kosong'];
        }
        if ($name === '') {
            $name = 'User';
        }

        $users = self::loadUsers();
        $foundIndex = -1;

        foreach ($users as $i => $u) {
            // Match prioritas: provider + provider_id
            if (($u['provider'] ?? '') === $provider
                && (string)($u['provider_id'] ?? '') === $providerId) {
                $foundIndex = $i;
                break;
            }
        }

        // Fallback: match by email (hanya kalau belum ketemu & email valid)
        if ($foundIndex === -1 && $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            foreach ($users as $i => $u) {
                if (!empty($u['email'])
                    && strtolower($u['email']) === strtolower($email)) {
                    $foundIndex = $i;
                    break;
                }
            }
        }

        if ($foundIndex >= 0) {
            // === UPDATE USER EXISTING ===
            $users[$foundIndex]['provider']    = $provider;
            $users[$foundIndex]['provider_id'] = $providerId;
            $users[$foundIndex]['email']       = $email ?: ($users[$foundIndex]['email'] ?? '');
            $users[$foundIndex]['name']        = $name;
            $users[$foundIndex]['avatar']      = $avatar ?: ($users[$foundIndex]['avatar'] ?? '');
            $users[$foundIndex]['last_login']  = date('c');

            $userId   = $users[$foundIndex]['id'];
            $username = $users[$foundIndex]['username'];
        } else {
            // === REGISTER USER BARU ===
            $userId   = uniqid('u_', true);
            $username = self::uniqueUsername($name, $users);

            $users[] = [
                'id'          => $userId,
                'username'    => $username,
                'email'       => $email,
                'provider'    => $provider,
                'provider_id' => $providerId,
                'name'        => $name,
                'avatar'      => $avatar,
                'created_at'  => date('c'),
                'last_login'  => date('c'),
            ];
        }

        // === SAVE DULU, baru set session ===
        if (!self::saveUsers($users)) {
            return ['ok' => false, 'error' => 'Gagal menyimpan data user.'];
        }

        // === REGENERATE SESSION ID (anti session fixation) ===
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        // === SET SESSION ===
        $_SESSION[self::SESSION_KEY] = $userId;
        $_SESSION['username']        = $username;
        $_SESSION['provider']        = $provider;
        $_SESSION['logged_in_at']    = time();

        return ['ok' => true, 'user_id' => $userId, 'username' => $username];
    }

    public static function check(): bool
    {
        self::startSession();
        return !empty($_SESSION[self::SESSION_KEY]);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        $users = self::loadUsers();
        $id    = $_SESSION[self::SESSION_KEY];

        foreach ($users as $u) {
            if (($u['id'] ?? '') === $id) {
                return $u;
            }
        }

        // Session ada tapi user udah dihapus dari storage
        self::logout();
        return null;
    }

    public static function id(): ?string
    {
        self::startSession();
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    public static function username(): string
    {
        self::startSession();
        return $_SESSION['username'] ?? 'Guest';
    }

    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                [
                    'expires'  => time() - 42000,
                    'path'     => $p['path'] ?: '/',
                    'domain'   => $p['domain'] ?: '',
                    'secure'   => (bool)($p['secure'] ?? false),
                    'httponly' => (bool)($p['httponly'] ?? true),
                    'samesite' => $p['samesite'] ?? 'Lax',
                ]
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    // ============================================================
    // INTERNAL
    // ============================================================

    private static function uniqueUsername(string $base, array $users): string
    {
        $base = preg_replace('/[^a-zA-Z0-9_]/', '', $base);
        if ($base === '') {
            $base = 'user';
        }
        // Batasi panjang biar rapi
        $base = substr($base, 0, 20);

        $username = $base;
        $i = 1;
        while (self::usernameExists($username, $users)) {
            $username = $base . $i;
            $i++;
        }
        return $username;
    }

    private static function usernameExists(string $username, array $users): bool
    {
        $needle = strtolower($username);
        foreach ($users as $u) {
            if (strtolower($u['username'] ?? '') === $needle) {
                return true;
            }
        }
        return false;
    }

    private static function loadUsers(): array
    {
        self::init();

        if (!file_exists(self::$userFile)) {
            return [];
        }

        $raw = @file_get_contents(self::$userFile);
        if ($raw === false || $raw === '') {
            return [];
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            // Backup file corrupt, biar ga ilang total
            $backup = self::$userFile . '.corrupt.' . time();
            @copy(self::$userFile, $backup);
            return [];
        }

        return $data;
    }

    private static function saveUsers(array $users): bool
    {
        self::init();

        $dir = dirname(self::$userFile);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            return false;
        }

        // Atomic write: tulis ke temp dulu, baru rename
        $tmp = self::$userFile . '.tmp.' . getmypid();
        $json = json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            return false;
        }

        if (file_put_contents($tmp, $json, LOCK_EX) === false) {
            @unlink($tmp);
            return false;
        }

        if (!@rename($tmp, self::$userFile)) {
            @unlink($tmp);
            return false;
        }

        return true;
    }
}