<?php

declare(strict_types=1);

namespace Pin\Access;

use Pin\Errors\Errors;
use Pin\Exceptions\Exception;

/**
 * 权限校验失败异常。
 */
class UnauthorizedException extends Exception
{
    public function __construct(string $accessCode)
    {
        parent::__construct(Errors::AccessDenied);

        $this->withContext(['code' => $accessCode]);
    }
}
