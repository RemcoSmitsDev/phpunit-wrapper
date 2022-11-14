<?php

declare(strict_types=1);

namespace Remcosmits\PhpunitWrapper\Commands;

use Remcosmits\PhpunitWrapper\Exceptions\Commands\TerminalProfilePathNotFound;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SetupCommand extends Command
{
    private bool $reAsk = false;

    /** @var string */
    protected static $defaultName = 'setup';

    /** @var string */
    protected static $defaultDescription = 'SetupCommand PHPUnit wrapper command!';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputStyle = new OutputFormatterStyle('#fff', '#33cc33');
        $output->getFormatter()->setStyle('success', $outputStyle);

        $output->getFormatter()->setStyle(
            'code',
            new OutputFormatterStyle('#fff', '#33cc33', ['bold'])
        );

        $io = new SymfonyStyle($input, $output);

        $answer = $io->ask(
            $this->reAsk ? 'Retry to add your command!' : 'Specify the command that you want to use as replacement for `composer run phpunit`',
            't'
        );

        if (is_string($answer) === false) {
            $io->error('Your command must be a typeof string!');

            return $this->execute($input, $output);
        }

        // validate command characters
        if (!preg_match('/^[a-zA-Z_]+\d*$/', $answer)) {
            $io->error('Your command can only contain `A-z_0-9`!');

            return $this->execute($input, $output);
        }

        // check if command contains spaces
        if (strpos($answer, ' ') !== false) {
            $this->reAsk = true;

            $io->block("😕 The command can't contain spaces!", null, 'fg=white;bg=red', '  ', true);

            return $this->execute($input, $output);
        }

        // get config file
        try {
            $terminalConfigFile = $this->getTerminalProfileFilePath();
        } catch (TerminalProfilePathNotFound $exception) {
            $io->block([
                "😕 We couldn't found a valid terminal profile config file!",
                "If you still want to make this work you can add the following alias to your terminal config `alias {$answer}='phpUnitWrapper`",
            ], null, 'fg=white;bg=red', '  ', true);

            return Command::FAILURE;
        }

        $io->block([
            "Follow these steps: ",
            "• Run the following command `nano {$terminalConfigFile}`",
            "• Add the following code `alias {$answer}='phpUnitWrapper'`",
            "• Run the following command `source {$terminalConfigFile}`",
            "🎉 You can now use `{$answer}` as command to run your tests in all your projects!"
        ], null, 'success', '  ', true);

        return Command::SUCCESS;
    }

    /** @throws TerminalProfilePathNotFound */
    private function getTerminalProfileFilePath(): string
    {
        switch (rtrim((string)shell_exec('echo $SHELL'))) {
            case '/bin/zsh':
                return '~/.zshrc';
            case '/bin/bash':
                return '~/.bashrc';

            default:
                throw new TerminalProfilePathNotFound();
        }
    }
}
