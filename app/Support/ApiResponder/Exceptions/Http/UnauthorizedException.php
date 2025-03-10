<?php

namespace App\Support\ApiResponder\Exceptions\Http;

/**
 * An exception thrown when a user is unauthorized. This exception replaces Laravel's
 * [\Illuminate\Auth\Access\AuthorizationException].
 */
class UnauthorizedException extends HttpException
{
    /**
     * An HTTP status code.
     */
    protected int $status = 403;

    /**
     * An error code.
     */
    protected ?string $errorCode = 'unauthorized';
}
