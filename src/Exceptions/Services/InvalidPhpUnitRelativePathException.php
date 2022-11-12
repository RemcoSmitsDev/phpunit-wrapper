<?php

declare(strict_types=1);

namespace Remcosmits\PhpunitWrapper\Exceptions\Services;

use Exception;

class InvalidPhpUnitRelativePathException extends Exception
{
    /**
     * @var string
     */
    protected $message = "Couldn't find a valid relative path to the installation folder of this cli wrapper package!";
}
