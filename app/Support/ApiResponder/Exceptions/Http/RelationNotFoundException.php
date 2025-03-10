<?php

namespace App\Support\ApiResponder\Exceptions\Http;

/**
 * An exception thrown when a relation is not found.
 */
class RelationNotFoundException extends HttpException
{
    /**
     * An HTTP status code.
     */
    protected int $status = 422;

    /**
     * An error code.
     */
    protected ?string $errorCode = 'relation_not_found';
}
