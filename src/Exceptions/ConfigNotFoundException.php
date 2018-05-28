<?php

namespace yedincisenol\UserProvider\Exceptions;

use Throwable;

class ConfigNotFoundException extends \Exception
{
    public function __construct($message = 'User provider config missing', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}