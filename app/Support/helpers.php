<?php

use App\Support\ApiResponder\ApiResponder;

if (!function_exists('api')) {
    function api() {
        return app(ApiResponder::class);
    }
}
