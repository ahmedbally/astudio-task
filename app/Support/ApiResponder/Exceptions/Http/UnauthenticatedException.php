<?php

namespace App\Support\ApiResponder\Exceptions\Http;

/**
 * An exception thrown when a user is unauthenticated. This exception replaces Laravel's
 * [\Illuminate\Auth\AuthenticationException].
 */
class UnauthenticatedException extends HttpException
{
    /**
     * An HTTP status code.
     */
    protected int $status = 401;

    /**
     * The error code.
     */
    protected ?string $errorCode = 'unauthenticated';
}
