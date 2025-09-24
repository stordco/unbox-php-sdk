<?php

namespace StordUnbox\Exception;

class ApiException extends UnboxException
{
    public function __construct(string $message, int $code = 0)
    {
        parent::__construct(sprintf($code . ': Stord Unbox API service error: %s', $message), $code);
    }
}
