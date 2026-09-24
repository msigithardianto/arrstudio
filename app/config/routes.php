<?php
// app/config/routes.php — daftar route (?page=... atau /pretty-url)
// Middleware: 'auth' (wajib login), 'guest' (wajib belum login)

// ============================================================
// HALAMAN — bisa diakses guest
// ============================================================
$router->get('landing',   'LandingController@index');
$router->get('converter', 'ConverterController@index');
$router->get('library',   'LibraryController@index');
$router->get('docs',      'DocsController@index');
$router->get('prompt',    'PromptController@index');

// ============================================================
// AUTH
// ============================================================
$router->get('login',  'AuthController@loginForm', ['guest']);
$router->get('logout', 'AuthController@logout');

$router->get('auth_google',           'AuthController@googleRedirect');
$router->get('auth_google_callback',  'AuthController@googleCallback');
$router->get('auth_discord',          'AuthController@discordRedirect');
$router->get('auth_discord_callback', 'AuthController@discordCallback');

// ============================================================
// API (JSON) — juga bisa lewat api/convert.php & api/generate.php
// ============================================================
$router->post('api_convert',  'ConvertApiController@handle');
$router->post('api_generate', 'GenerateApiController@handle');
