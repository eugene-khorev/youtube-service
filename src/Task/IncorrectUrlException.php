<?php

namespace Task;

class IncorrectUrlException extends \Exception
{
    public function __construct(string $url = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct('Incorrect task URL: ' . $url, $code, $previous);
    }
}