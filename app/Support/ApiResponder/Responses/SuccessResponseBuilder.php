<?php

namespace App\Support\ApiResponder\Responses;

use App\Support\ApiResponder\Contracts\ResponseFactory as ResponseFactoryContract;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

/**
 * Success Response Builder
 */
class SuccessResponseBuilder extends ResponseBuilder
{
    /**
     * The success message.
     */
    protected ?string $message;

    /**
     * The response data.
     */
    protected mixed $data = null;


    /**
     * A HTTP status code for the response.
     */
    protected int $status = 200;

    /**
     * Create a new success response builder.
     */
    public function __construct(ResponseFactoryContract $responseFactory, mixed $data = null, ?string $message = null)
    {
        $this->data = $data;
        $this->message = $message;

        parent::__construct($responseFactory);
    }

    /**
     * Get the serialized response output.
     */
    protected function getOutput(): array
    {
        $output = [
            'success' => true,
            'message' => $this->message,
        ];

        if (!empty($this->meta)) {
            $output['meta'] = $this->meta;
        }

        if ($this->hasPagination()) {
            /** @var LengthAwarePaginator $resource */
            $resource = $this->data->resource;
            $output['data'] = $resource->items();
            $output['pagination'] = [
                'total' => $resource->total(),
                'per_page' => $resource->perPage(),
                'current_page' => $resource->currentPage(),
                'last_page' => $resource->lastPage(),
                'from' => $resource->firstItem(),
                'to' => $resource->lastItem(),
            ];

            return $output;
        }

        $output['data'] = $this->data;

        return $output;
    }

    /**
     * Validate the HTTP status code for the response.
     */
    protected function validateStatusCode(int $status): void
    {
        if ($status < 100 || $status >= 400) {
            throw new InvalidArgumentException("{$status} is not a valid success HTTP status code.");
        }
    }

    private function hasPagination(): bool
    {
        return $this->data instanceof ResourceCollection &&
            $this->data->resource instanceof LengthAwarePaginator;
    }
}
