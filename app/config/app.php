<?php
// app/config/app.php — konfigurasi umum aplikasi

return [
    'name' => 'ARRR Studio',

    // Ukuran canvas default converter (px)
    'canvas' => [
        'width'  => 800,
        'height' => 600,
    ],

    // Batas pemakaian converter untuk guest (belum login)
    'guest' => [
        'max_uses' => 3,
        'cookie'   => 'arrr_guest_uses_c',
    ],

    // Batas ukuran HTML yang boleh dikonversi (byte)
    'max_html_size' => 500_000,

    // Lokasi penyimpanan user
    'users_file' => STORAGE_PATH . '/users.json',
];
