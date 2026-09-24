<?php
// app/core/View.php

class View
{
    /** Data global yang di-share ke semua view/partial */
    private static array $shared = [];

    /**
     * Render view + layout
     */
    public static function render(string $view, array $data = []): void
    {
        // Simpan data asli ke shared
        self::$shared = $data;

        // Capture view content
        $content = self::capture($view, $data);

        // Render master dengan data + content
        $masterData = array_merge($data, ['content' => $content]);
        self::partial('layouts/master', $masterData);
    }

    /**
     * Capture view ke string (tanpa layout)
     */
    public static function capture(string $view, array $data = []): string
    {
        ob_start();
        self::partial($view, $data);
        return ob_get_clean();
    }

    /**
     * Render partial — inherit shared data
     */
    public static function partial(string $view, array $data = []): void
    {
        // Merge: shared dulu, lokal menang
        $vars = array_merge(self::$shared, $data);

        // Extract ke scope lokal
        extract($vars, EXTR_OVERWRITE);

        // Require file
        require BASE_PATH . "/app/views/{$view}.php";
    }
}