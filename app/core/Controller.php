<?php
// app/core/Controller.php — base controller

abstract class Controller
{
    /**
     * Render view + layout, langsung output
     */
    protected function render(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    /**
     * Return JSON response + exit
     */
    protected function json($data, int $status = 200): void
    {
        Response::json($data, $status);
    }

    /**
     * Redirect ke URL + exit
     */
    protected function redirect(string $url): void
    {
        Response::redirect($url);
    }

    /**
     * Ambil input dari POST/GET
     */
    protected function input(string $key, $default = null)
    {
        return Request::input($key, $default);
    }

    /**
     * Ambil POST saja
     */
    protected function post(string $key, $default = null)
    {
        return Request::post($key, $default);
    }

    /**
     * Ambil GET saja
     */
    protected function get(string $key, $default = null)
    {
        return Request::get($key, $default);
    }

    /**
     * Cek apakah request POST
     */
    protected function isPost(): bool
    {
        return Request::isPost();
    }

    /**
     * Kirim 404
     */
    protected function abort404(): void
    {
        Response::status(404);
        $this->render('errors/404');
        exit;
    }
}