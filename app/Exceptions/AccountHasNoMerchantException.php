<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class AccountHasNoMerchantException extends Exception
{
    public function __construct(
        string $message = 'Account has no merchant',
        int $code = Response::HTTP_FORBIDDEN,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
