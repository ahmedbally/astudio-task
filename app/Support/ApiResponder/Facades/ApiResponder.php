<?php

namespace App\Support\ApiResponder\Facades;


use Illuminate\Support\Facades\Facade;

/**
 * Facade for easy access to the ApiResponder
 */
class ApiResponder extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'api.responder';
    }
}
