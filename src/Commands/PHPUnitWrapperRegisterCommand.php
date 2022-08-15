<?php

namespace Remcosmits\PhpunitWrapper\Commands;

use Remcosmits\PhpunitWrapper\Services\PhpUnitWrapperService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PHPUnitWrapperRegisterCommand extends Command
{
    /**
     * The name of the command (the part after "bin/phpUnitWrapper").
     *
     * @var string
     */
    protected static $defaultName = 'PHPUnitWrapperRegisterCommand';

    /**
     * The command description shown when running "php bin/phpUnitWrapper list".
     *
     * @var string
     */
    protected static $defaultDescription = 'Register the PHPUnit formatter wrapper!';

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int 0 if everything went fine, or an exit code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            PhpUnitWrapperService::register();

            return Command::SUCCESS;
        } catch (\Throwable $th) {
            return Command::FAILURE;
        }
    }
}
