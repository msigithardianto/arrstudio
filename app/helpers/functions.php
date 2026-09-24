<?php
// app/helpers/functions.php

/**
 * Deteksi apakah .htaccess rewrite aktif
 * (cek dari environment variable yang di-set Apache)
 */
function pretty_urls_enabled(): bool
{
    // Cara simpel: kalau REQUEST_URI nggak ada 'index.php',
    // berarti pretty URL aktif
    static $enabled = null;
    if ($enabled !== null) return $enabled;

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $enabled = (strpos($requestUri, 'index.php') === false);
    return $enabled;
}

/**
 * Build URL — otomatis pakai pretty URL kalau aktif
 */
function url(string $page = 'converter', array $params = []): string
{
    if (pretty_urls_enabled()) {
        $base = BASE_URL . '/' . ltrim($page, '/');
        if (!empty($params)) {
            $base .= '?' . http_build_query($params);
        }
        return $base;
    }
    $params = array_merge(['page' => $page], $params);
    return 'index.php?' . http_build_query($params);
}

/**
 * URL ke asset (relative)
 */
function asset(string $path): string
{
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

/**
 * Escape HTML
 */
function e($str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Active class
 */
function is_active(string $page, string $current): string
{
    return $page === $current ? 'active' : '';
}