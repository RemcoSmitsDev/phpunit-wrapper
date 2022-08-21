<?php

namespace Remcosmits\PhpunitWrapper\Exceptions\Services;

use Exception;

class InvalidInstallationException extends Exception
{
    /**
     * @var string
     */
    protected $message = 'There was no `composer.json` file and `vendor` folder found! Make sure the package is installed correctly!';
}
