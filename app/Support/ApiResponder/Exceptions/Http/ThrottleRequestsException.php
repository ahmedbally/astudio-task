<?php

namespace App\Support\ApiResponder\Exceptions\Http;

/**
 * An exception thrown when a relation is not found.
 */
class ThrottleRequestsException extends HttpException
{
    /**
     * An HTTP status code.
     */
    protected int $status = 429;

    /**
     * An error code.
     */
    protected ?string $errorCode = 'too_many_requests';
}
