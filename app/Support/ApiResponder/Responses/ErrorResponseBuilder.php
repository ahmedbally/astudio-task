<?php

namespace App\Support\ApiResponder\Responses;

use App\Support\ApiResponder\Contracts\ResponseFactory as ResponseFactoryContract;
use InvalidArgumentException;

/**
 * Error Response Builder
 */
class ErrorResponseBuilder extends ResponseBuilder
{
    /**
     * A code representing the error.
     */
    protected ?string $errorCode;

    /**
     * A message describing the error.
     */
    protected ?string $message;

    /**
     * Additional data included with the response.
     */
    protected ?array $data = null;

    /**
     * A HTTP status code for the response.
     */
    protected int $status = 400;

    /**
     * Create a new error response builder.
     */
    public function __construct(ResponseFactoryContract $responseFactory, ?string $errorCode = null, ?string $message = null)
    {
        $this->errorCode = $errorCode;
        $this->message = $message ?:
            (trans()->has('errors.'.$errorCode)
            ? trans('errors.'.$errorCode)
            : null);

        parent::__construct($responseFactory);
    }

    /**
     * Add data to the error.
     */
    public function data(?array $data = null): static
    {
        $this->data = array_merge((array) $this->data, (array) $data);

        return $this;
    }

    /**
     * Get the serialized response output.
     */
    protected function getOutput(): array
    {
        $output = [
            'success' => false,
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->message,
            ]
        ];

        if (!empty($this->meta)) {
            $output['meta'] = $this->meta;
        }

        if (!empty($this->data)) {
            $output['error'] = array_merge($output['error'], $this->data);
        }

        return $output;
    }

    /**
     * Validate the HTTP status code for the response.
     */
    protected function validateStatusCode(int $status): void
    {
        if ($status < 400 || $status >= 600) {
            throw new InvalidArgumentException("{$status} is not a valid error HTTP status code.");
        }
    }
}
