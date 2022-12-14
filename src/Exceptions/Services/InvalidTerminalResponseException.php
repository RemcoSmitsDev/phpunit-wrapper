<?php

declare(strict_types=1);

namespace Remcosmits\PhpunitWrapper\Exceptions\Services;

use Exception;

class InvalidTerminalResponseException extends Exception
{
    /** @var string */
    protected $message = 'There was an invalid terminal response!';
}
