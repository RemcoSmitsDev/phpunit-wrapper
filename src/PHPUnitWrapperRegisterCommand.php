<?php

namespace Remcosmits\PhpunitWrapper;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PHPUnitWrapperRegisterCommand extends Command
{
    /**
     * The name of the command (the part after "bin/demo").
     *
     * @var string
     */
    protected static $defaultName = 'PHPUnitWrapperRegisterCommand';

    /**
     * The command description shown when running "php bin/demo list".
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
            PhpUnitWrapper::register();

            return Command::SUCCESS;
        } catch (\Throwable $th) {
            return Command::FAILURE;
        }
    }
}
