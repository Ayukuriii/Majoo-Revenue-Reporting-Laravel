<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class OutletNotOwnedException extends Exception
{
    public function __construct(
        string $message = 'Outlet does not belong to the authenticated merchant',
        int $code = Response::HTTP_FORBIDDEN,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
