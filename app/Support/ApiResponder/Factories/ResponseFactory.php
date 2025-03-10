<?php

namespace App\Support\ApiResponder\Factories;

use App\Support\ApiResponder\Contracts\ResponseFactory as ResponseFactoryContract;
use Illuminate\Http\JsonResponse;

class ResponseFactory implements ResponseFactoryContract
{
    /**
     * Create a new JSON response.
     */
    public function make(mixed $data, int $status = 200, array $headers = []): JsonResponse
    {
        return response()->json($data, $status, $headers);
    }
}
