<?php

declare(strict_types=1);

namespace Remcosmits\PhpunitWrapper\Commands;

use Remcosmits\PhpunitWrapper\Services\PhpUnitWrapperService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

final class PHPUnitWrapperRegisterCommand extends Command
{
    /**
     * The name of the command (the part after "bin/phpUnitWrapper").
     *
     * @var string
     */
    protected static $defaultName = 'phpUnitWrapper';

    /**
     * The command description shown when running "php bin/phpUnitWrapper list".
     *
     * @var string
     */
    protected static $defaultDescription = 'Register the PHPUnit formatter wrapper!';

    /**
     * @var array<int, string>
     */
    private array $params;

    /**
     * @return void
     */
    protected function configure(): void
    {
        $validParams = [
            'setup'
        ];

        $normalArgs = array_filter($_SERVER['argv'], static function ($value) use ($validParams) {
            return in_array($value, $validParams, true);
        });

        $paramsToSearchIn = array_slice($_SERVER['argv'], 1, count($_SERVER['argv']));

        $this->params = array_filter($paramsToSearchIn, static function ($value) use ($validParams) {
            return !in_array($value, $validParams, true);
        });

        $_SERVER['argv'] = [
            $_SERVER['argv'][0],
            ...$normalArgs,
        ];
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
        $styleOutput = new SymfonyStyle($input, $output);

        try {
            PhpUnitWrapperService::register(
                $this->params
            );

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $styleOutput->error($e->getMessage() ?: "Something when wrong");

            return Command::FAILURE;
        }
    }
}
