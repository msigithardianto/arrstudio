<?php
// app/config/oauth.php

return [
    'google' => [
        'client_id'     => 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com',
        'client_secret' => 'YOUR_GOOGLE_CLIENT_SECRET',
        'redirect_uri'  => 'http://localhost/ArrStudioWeb/index.php?page=auth/google/callback',
        'auth_url'      => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url'     => 'https://oauth2.googleapis.com/token',
        'user_url'      => 'https://www.googleapis.com/oauth2/v2/userinfo',
        'scopes'        => 'openid email profile',
    ],
    'discord' => [
        'client_id'     => '1552156118380445757',
        'client_secret' => 'sb7pE5c3j-yCAhzb8_A-ouTm38L53l28',
        'redirect_uri'  => 'http://localhost/ArrStudioWeb/index.php?page=auth_discord_callback',
        'auth_url'      => 'https://discord.com/api/oauth2/authorize',
        'token_url'     => 'https://discord.com/api/oauth2/token',
        'user_url'      => 'https://discord.com/api/users/@me',
        'scopes'        => 'identify email',
    ],
];