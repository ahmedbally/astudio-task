<?php

return [
    'client_id' => env('OAUTH_CLIENT_ID', ''),
    'client_secret' => env('OAUTH_CLIENT_SECRET', ''),
    'base_url' => env('OAUTH_BASE_URL', env('APP_URL')),
    'scopes' => env('OAUTH_SCOPES', ''),
];
