<?php

namespace App\Support\ApiResponder\Exceptions\Http;

use Illuminate\Contracts\Validation\Validator;

/**
 * An exception thrown whan validation fails. This exception replaces Laravel's
 * [\Illuminate\Validation\ValidationException].
 */
class ValidationFailedException extends HttpException
{
    /**
     * An HTTP status code.
     */
    protected int $status = 422;

    /**
     * An error code.
     */
    protected ?string $errorCode = 'validation_failed';

    /**
     * A validator for fetching validation messages.
     */
    protected Validator $validator;

    /**
     * Construct the exception class.
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;

        parent::__construct();
    }

    /**
     * Retrieve the error data.
     */
    public function data(): ?array
    {
        return ['fields' => $this->validator->getMessageBag()->toArray()];
    }
}
