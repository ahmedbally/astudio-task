<?php

namespace App\Support\ApiResponder\Responses;

use App\Support\ApiResponder\Contracts\ResponseFactory as ResponseFactoryContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

abstract class ResponseBuilder implements Arrayable, Jsonable
{
    /**
     * A factory for making responses.
     */
    protected ResponseFactoryContract $responseFactory;

    /**
     * Additional metadata included with the response.
     */
    protected array $meta = [];

    /**
     * A HTTP status code for the response.
     */
    protected int $status;

    /**
     * Construct the builder class.
     */
    public function __construct(ResponseFactoryContract $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Add metadata to the response.
     */
    public function meta(array $meta): static
    {
        $this->meta = array_merge($this->meta, $meta);

        return $this;
    }

    /**
     * Respond with an HTTP response.
     */
    public function respond(int $status = null, array $headers = []): JsonResponse
    {
        if (!is_null($status)) {
            $this->setStatusCode($status);
        }

        return $this->responseFactory->make($this->getOutput(), $this->status, $headers);
    }

    /**
     * Convert the response to an array.
     */
    public function toArray(): array
    {
        return $this->respond()->getData(true);
    }

    /**
     * Convert the response to an Illuminate collection.
     */
    public function toCollection(): Collection
    {
        return new Collection($this->toArray());
    }

    /**
     * Convert the response to JSON.
     *
     * @param  int $options
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Set the HTTP status code for the response.
     */
    public function setStatus(int $status): static
    {
        $this->setStatusCode($status);

        return $this;
    }

    /**
     * Set the HTTP status code for the response.
     */
    protected function setStatusCode(int $status): void
    {
        $this->validateStatusCode($this->status = $status);
    }

    /**
     * Get the serialized response output.
     */
    abstract protected function getOutput(): array;

    /**
     * Validate the HTTP status code for the response.
     */
    abstract protected function validateStatusCode(int $status): void;
}
