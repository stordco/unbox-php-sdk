<?php

namespace StordUnbox\Exception;

class ServerErrorException extends UnboxException
{
    public function __construct(string $message)
    {
        parent::__construct('Penny Black API service gave a 500 error: ' . $message);
    }
}
