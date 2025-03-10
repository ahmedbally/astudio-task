<?php

namespace App\Support\ApiResponder\Exceptions;

use App\Support\ApiResponder\ApiResponder;
use App\Support\ApiResponder\Exceptions\Http\ThrottleRequestsException;
use Exception;
use App\Support\ApiResponder\Exceptions\Http\HttpException;
use App\Support\ApiResponder\Exceptions\Http\PageNotFoundException;
use App\Support\ApiResponder\Exceptions\Http\RelationNotFoundException;
use App\Support\ApiResponder\Exceptions\Http\UnauthenticatedException;
use App\Support\ApiResponder\Exceptions\Http\UnauthorizedException;
use App\Support\ApiResponder\Exceptions\Http\ValidationFailedException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException as BaseRelationNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException as BaseThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * A trait used by exception handlers to transform and render error responses.
 */
trait ConvertsExceptions
{
    /**
     * A list of default exception types that should not be converted.
     */
    protected array $dontConvert = [];

    /**
     * Convert an exception to another exception
     */
    protected function convert(Exception|Throwable $exception, array $convert): void
    {
        foreach ($convert as $source => $target) {
            if ($exception instanceof $source) {
                if (is_callable($target)) {
                    $target($exception);
                }

                throw new $target;
            }
        }
    }

    /**
     * Convert a default exception to an API exception.
     */
    protected function convertDefaultException(Exception|Throwable $exception): void
    {
        $this->convert($exception, array_diff_key([
            AuthenticationException::class => UnauthenticatedException::class,
            AuthorizationException::class => UnauthorizedException::class,
            NotFoundHttpException::class => PageNotFoundException::class,
            ModelNotFoundException::class => PageNotFoundException::class,
            BaseRelationNotFoundException::class => RelationNotFoundException::class,
            BaseThrottleRequestsException::class => ThrottleRequestsException::class,
            ValidationException::class => function ($exception) {
                throw new ValidationFailedException($exception->validator);
            },
        ], array_flip($this->dontConvert)));
    }

    /**
     * Render an error response from an API exception.
     */
    protected function renderResponse(HttpException $exception): JsonResponse
    {
        return app(ApiResponder::class)
            ->error($exception->errorCode(), $exception->message())
            ->data($exception->data())
            ->respond($exception->statusCode(), $exception->getHeaders());
    }
}
