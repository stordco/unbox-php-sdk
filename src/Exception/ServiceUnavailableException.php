<?php

namespace StordUnbox\Exception;

class ServiceUnavailableException extends UnboxException
{
    public function __construct(string $message, int $code = 0)
    {
        parent::__construct(sprintf($code . ': Penny Black API service is unavailable: %s', $message), $code);
    }
}
