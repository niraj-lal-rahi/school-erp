<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class CrossTenantAccessException extends HttpException
{
    public function __construct(string $message = 'Cross-tenant access is not allowed.', int $statusCode = 403)
    {
        parent::__construct($statusCode, $message);
    }
}
