<?php

namespace App\Support\ApiResponder;
use App\Support\ApiResponder\Responses\ErrorResponseBuilder;
use App\Support\ApiResponder\Responses\SuccessResponseBuilder;
use App\Support\ApiResponder\Contracts\ResponseFactory as ResponseFactoryContract;

/**
 * Main API Responder class
 */
class ApiResponder
{
    /**
     * The response factory instance.
     */
    protected ResponseFactoryContract $factory;

    /**
     * Create a new API Responder instance.
     */
    public function __construct(ResponseFactoryContract $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Create a success response.
     *
     * @param mixed $data
     * @param string|null $message
     * @return SuccessResponseBuilder
     */
    public function success(mixed $data = null, ?string $message = null): SuccessResponseBuilder
    {
        return new SuccessResponseBuilder($this->factory, $data, $message);
    }

    /**
     * Create an error response.
     *
     * @param string|null $errorCode
     * @param string|null $message
     * @return ErrorResponseBuilder
     */
    public function error(?string $errorCode = null, ?string $message = null): ErrorResponseBuilder
    {
        return new ErrorResponseBuilder($this->factory, $errorCode, $message);
    }
}
