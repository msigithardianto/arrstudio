<?php
// app/controllers/AuthController.php

class AuthController extends Controller
{
    private array $oauth;

    public function __construct()
    {
        $this->oauth = require BASE_PATH . '/app/config/oauth.php';
    }

    // ============================================================
    // LOGIN PAGE
    // ============================================================
    public function loginForm(): void
    {
        if (Auth::check()) {
            $this->redirect(url('converter'));
        }

        $error = $_SESSION['auth_error'] ?? null;
        unset($_SESSION['auth_error']);

        $this->render('auth/login', [
            'pageTitle'    => 'Login — ARRR Studio',
            'activePage'   => 'login',
            'navVariant'   => 'landing',
            'extraStyles'  => ['partials/styles-auth'],
            'extraScripts' => [],
            'error'        => $error,
        ]);
    }

    // ============================================================
    // GOOGLE
    // ============================================================
    public function googleRedirect(): void
    {
        $cfg = $this->oauth['google'];
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;

        $params = [
            'client_id'     => $cfg['client_id'],
            'redirect_uri'  => $cfg['redirect_uri'],
            'response_type' => 'code',
            'scope'         => $cfg['scopes'],
            'state'         => $state,
            'access_type'   => 'online',
            'prompt'        => 'select_account',
        ];

        header('Location: ' . $cfg['auth_url'] . '?' . http_build_query($params));
        exit;
    }

    public function googleCallback(): void
    {
        // Verify state
        if (empty($_GET['state']) || $_GET['state'] !== ($_SESSION['oauth_state'] ?? '')) {
            $_SESSION['auth_error'] = 'Invalid state parameter (CSRF).';
            $this->redirect(url('login'));
        }
        unset($_SESSION['oauth_state']);

        if (empty($_GET['code'])) {
            $_SESSION['auth_error'] = 'Authorization dibatalkan.';
            $this->redirect(url('login'));
        }

        $cfg = $this->oauth['google'];

        // Exchange code → token
        $token = $this->httpPost($cfg['token_url'], [
            'code'          => $_GET['code'],
            'client_id'     => $cfg['client_id'],
            'client_secret' => $cfg['client_secret'],
            'redirect_uri'  => $cfg['redirect_uri'],
            'grant_type'    => 'authorization_code',
        ]);

        if (empty($token['access_token'])) {
            $_SESSION['auth_error'] = 'Gagal ambil access token: ' . json_encode($token);
            $this->redirect(url('login'));
        }

        // Fetch user info
        $user = $this->httpGet($cfg['user_url'] . '?access_token=' . urlencode($token['access_token']));
        if (empty($user['id'])) {
            $_SESSION['auth_error'] = 'Gagal ambil profil Google.';
            $this->redirect(url('login'));
        }

        $profile = [
            'id'         => $user['id'],
            'email'      => $user['email'] ?? '',
            'name'       => $user['name'] ?? ($user['email'] ?? 'User'),
            'avatar_url' => $user['picture'] ?? '',
        ];

        $result = Auth::loginOAuth('google', $profile);
        if (!$result['ok']) {
            $_SESSION['auth_error'] = $result['error'];
            $this->redirect(url('login'));
        }

        $intended = $_SESSION['intended_url'] ?? url('converter');
        unset($_SESSION['intended_url']);
        $this->redirect($intended);
    }

    // ============================================================
    // DISCORD
    // ============================================================
    public function discordRedirect(): void
    {
        $cfg = $this->oauth['discord'];
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;

        $params = [
            'client_id'     => $cfg['client_id'],
            'redirect_uri'  => $cfg['redirect_uri'],
            'response_type' => 'code',
            'scope'         => $cfg['scopes'],
            'state'         => $state,
        ];

        header('Location: ' . $cfg['auth_url'] . '?' . http_build_query($params));
        exit;
    }

    public function discordCallback(): void
    {
        if (empty($_GET['state']) || $_GET['state'] !== ($_SESSION['oauth_state'] ?? '')) {
            $_SESSION['auth_error'] = 'Invalid state parameter (CSRF).';
            $this->redirect(url('login'));
        }
        unset($_SESSION['oauth_state']);

        if (empty($_GET['code'])) {
            $_SESSION['auth_error'] = 'Authorization dibatalkan.';
            $this->redirect(url('login'));
        }

        $cfg = $this->oauth['discord'];

        // Exchange code → token (pakai Basic Auth)
        $token = $this->httpPost($cfg['token_url'], [
            'grant_type'   => 'authorization_code',
            'code'         => $_GET['code'],
            'redirect_uri' => $cfg['redirect_uri'],
        ], [
            'Authorization: Basic ' . base64_encode($cfg['client_id'] . ':' . $cfg['client_secret']),
        ]);

        if (empty($token['access_token'])) {
            $_SESSION['auth_error'] = 'Gagal ambil access token Discord: ' . json_encode($token);
            $this->redirect(url('login'));
        }

        // Fetch user
        $user = $this->httpGet($cfg['user_url'], [
            'Authorization: Bearer ' . $token['access_token'],
        ]);
        if (empty($user['id'])) {
            $_SESSION['auth_error'] = 'Gagal ambil profil Discord.';
            $this->redirect(url('login'));
        }

        $avatar = '';
        if (!empty($user['avatar'])) {
            $avatar = 'https://cdn.discordapp.com/avatars/' . $user['id'] . '/' . $user['avatar'] . '.png';
        }

        $profile = [
            'id'         => $user['id'],
            'email'      => $user['email'] ?? '',
            'name'       => $user['global_name'] ?? $user['username'] ?? 'User',
            'avatar_url' => $avatar,
        ];

        $result = Auth::loginOAuth('discord', $profile);
        if (!$result['ok']) {
            $_SESSION['auth_error'] = $result['error'];
            $this->redirect(url('login'));
        }

        $intended = $_SESSION['intended_url'] ?? url('converter');
        unset($_SESSION['intended_url']);
        $this->redirect($intended);
    }

    // ============================================================
    // LOGOUT
    // ============================================================
    public function logout(): void
    {
        Auth::logout();
        $this->redirect(url('landing'));
    }

    // ============================================================
    // HTTP HELPERS (cURL)
    // ============================================================
    private function httpPost(string $url, array $data, array $extraHeaders = []): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => array_merge([
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
            ], $extraHeaders),
            CURLOPT_TIMEOUT        => 15,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?: [];
    }

    private function httpGet(string $url, array $headers = []): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => array_merge([
                'Accept: application/json',
            ], $headers),
            CURLOPT_TIMEOUT        => 15,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?: [];
    }
}