<?php

namespace App\Support\ApiResponder\Contracts;

use Illuminate\Http\JsonResponse;

interface ResponseFactory
{
    /**
     * Create a new JSON response.
     */
    public function make($data, int $status = 200, array $headers = []): JsonResponse;
}
