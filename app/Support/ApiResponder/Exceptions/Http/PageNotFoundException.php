<?php

namespace App\Support\ApiResponder\Exceptions\Http;

/**
 * An exception thrown when a page is not found.
*/
class PageNotFoundException extends HttpException
{
    /**
     * An HTTP status code.
     */
    protected int $status = 404;

    /**
     * An error code.
     */
    protected ?string $errorCode = 'page_not_found';
}
