<?php

namespace Remcosmits\PhpunitWrapper\Commands;

use Remcosmits\PhpunitWrapper\Services\PhpUnitWrapperService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

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
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addOption('suite', 's', InputOption::VALUE_REQUIRED, 'Select the test suite that you want to run')
            ->addOption('filter', 'f', InputOption::VALUE_REQUIRED, 'Filter tests based on regex');
    }

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
            $io = new SymfonyStyle($input, $output);

            PhpUnitWrapperService::register($io, [
                'testsuite' => $input->getOption('suite'),
                'filter' => $input->getOption('filter')
            ]);

            return Command::SUCCESS;
        } catch (Throwable $th) {
            return Command::FAILURE;
        }
    }
}
