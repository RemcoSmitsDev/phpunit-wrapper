<?php

declare(strict_types=1);

namespace Remcosmits\PhpunitWrapper\Exceptions\Services;

use Exception;

class InvalidInstallationException extends Exception
{
    /**
     * @var string
     */
    protected $message = 'There was no `composer.json` file and `vendor` folder found! Make sure the package is installed correctly!';
}
