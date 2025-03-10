<?php

namespace App\Support\ApiResponder\Exceptions;

use App\Support\ApiResponder\Exceptions\Http\HttpException;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * An exception handler responsible for handling exceptions.
 */
class Handler extends ExceptionHandler
{
    use ConvertsExceptions;

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @throws Throwable
     */
    public function render($request, Exception|Throwable $e): JsonResponse|Response
    {
        if ($request->wantsJson()) {
            $this->convertDefaultException($e);

            if ($e instanceof HttpException) {
                return $this->renderResponse($e);
            }
        }

        return parent::render($request, $e);
    }
}
