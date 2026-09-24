<?php
// app/config/routes.php

// ============================================================
// PUBLIC — bisa diakses guest
// ============================================================
$router->get('landing',   'LandingController@index');
$router->get('login',     'AuthController@loginForm',  ['guest']);  // guest only
$router->get('logout',    'AuthController@logout');

$router->get('auth_google',           'AuthController@googleRedirect');
$router->get('auth_google_callback',  'AuthController@googleCallback');
$router->get('auth_discord',          'AuthController@discordRedirect');
$router->get('auth_discord_callback', 'AuthController@discordCallback');

// Guest-friendly — TANPA middleware auth
$router->get('converter', 'ConverterController@index');
$router->get('library',   'LibraryController@index');
$router->get('docs',      'DocsController@index');
$router->get('prompt',    'PromptController@index');