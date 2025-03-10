<?php

use App\Support\ApiResponder\Facades\ApiResponder;
use Illuminate\Support\Facades\Route;


Route::get('test', function () {
    return api()->error('test_code', trans('shittt'));
});
