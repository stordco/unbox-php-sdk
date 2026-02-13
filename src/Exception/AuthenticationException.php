<?php

namespace StordUnbox\Exception;

class AuthenticationException extends UnboxException
{
    public function __construct($statusCode)
    {
        parent::__construct(
            $statusCode . ': Authorization failed. Please check your API key is entered correctly',
            $statusCode
        );
    }
}
