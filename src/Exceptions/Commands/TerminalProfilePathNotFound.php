<?php

namespace Remcosmits\PhpunitWrapper\Exceptions\Commands;

use Exception;

class TerminalProfilePathNotFound extends Exception
{
    /**
     * @var string
     */
    protected $message = 'There was no terminal profile found inside the list of valid terminal profiles [zsh, bash]!';
}
