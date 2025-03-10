<?php

namespace App\Support\ApiResponder\Exceptions\Http;
use Symfony\Component\HttpKernel\Exception\HttpException as BaseHttpException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Base API HTTP Exception Class
 */
class HttpException extends BaseHttpException
{
    /**
     * The HTTP status code for the exception.
     */
    protected int $status = HttpResponse::HTTP_INTERNAL_SERVER_ERROR;

    /**
     * The error code for the exception.
     */
    protected ?string $errorCode = null;


    /**
     * Additional data to include in the response.
     */
    protected ?array $data = null;

    /**
     * Custom headers to add to the response.
     */
    protected array $headers = [];

    public function __construct(string $message = null, array $headers = null)
    {
        parent::__construct($this->status, $message ?? $this->message ?? null, null, $headers ?? $this->headers);
    }

    /**
     * Get the HTTP status code for the exception.
     */
    public function statusCode(): int
    {
        return $this->status;
    }

    /**
     * Get the error code for the exception.
     */
    public function errorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * Get the error message.
     */
    public function message(): ?string
    {
        return $this->message ?: null;
    }

    /**
     * Get additional data for the exception.
     */
    public function data(): ?array
    {
        return $this->data;
    }

    /**
     * Get the custom headers for the exception.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
