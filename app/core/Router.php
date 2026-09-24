<?php
// app/core/Router.php

class Router
{
    private array $routes = [];

    /**
     * Daftar route GET
     */
    public function get(string $path, string $handler, array $middleware = []): void
    {
        $this->routes['GET'][$path] = [
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    /**
     * Daftar route POST
     */
    public function post(string $path, string $handler, array $middleware = []): void
    {
        $this->routes['POST'][$path] = [
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    /**
     * Dispatch request
     */
    public function dispatch(): void
    {
        // Ambil ?page=..., default 'landing'
        $page = $_GET['page'] ?? 'landing';

        // Sanitize: buang karakter aneh (tapi tetep allow underscore & slash)
        $page = preg_replace('/[^a-zA-Z0-9_\/-]/', '', $page);

        // Normalize: ganti '/' jadi '_' biar bisa dipakai sebagai key
        $lookup = str_replace('/', '_', $page);

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Cek route
        if (!isset($this->routes[$method][$lookup])) {
            $this->render404();
            return;
        }

        $route      = $this->routes[$method][$lookup];
        $handler    = $route['handler'];
        $middleware = $route['middleware'];

        // ==== MIDDLEWARE ====
        foreach ($middleware as $mw) {
            if (!$this->runMiddleware($mw)) {
                return;
            }
        }

        // ==== CONTROLLER ====
        [$controllerName, $methodName] = explode('@', $handler);

        $controllerFile = BASE_PATH . "/app/controllers/{$controllerName}.php";
        if (!file_exists($controllerFile)) {
            $this->render404();
            return;
        }

        require_once BASE_PATH . '/app/core/Controller.php';
        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            $this->render404();
            return;
        }

        $controller = new $controllerName();
        if (!method_exists($controller, $methodName)) {
            $this->render404();
            return;
        }

        $controller->$methodName();
    }

    /**
     * Jalankan middleware. Return false kalau harus stop.
     */
    private function runMiddleware(string $name): bool
    {
        switch ($name) {
            case 'auth':
                Auth::startSession();
                if (!Auth::check()) {
                    // Simpan URL yang diminta
                    $_SESSION['intended_url'] = url($_GET['page'] ?? 'converter');
                    header('Location: ' . url('login'));
                    exit;
                }
                return true;

            case 'guest':
                Auth::startSession();
                if (Auth::check()) {
                    header('Location: ' . url('converter'));
                    exit;
                }
                return true;
        }

        return true;
    }

    /**
     * Render halaman 404
     */
    private function render404(): void
    {
        http_response_code(404);

        View::render('errors/404', [
            'pageTitle'    => '404 — Halaman tidak ditemukan',
            'activePage'   => '',
            'navVariant'   => 'app',
            'extraStyles'  => [],
            'extraScripts' => [],
        ]);
    }
}