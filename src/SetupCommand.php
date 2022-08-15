<?php

namespace Remcosmits\PhpunitWrapper;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetupCommand extends Command
{
    /**
     * @var boolean
     */
    private bool $reAsk = false;

    /**
     * The name of the command (the part after "bin/demo").
     *
     * @var string
     */
    protected static $defaultName = 'setup';

    /**
     * The command description shown when running "php bin/demo list".
     *
     * @var string
     */
    protected static $defaultDescription = 'SetupCommand PHPUnit wrapper command!';

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int 0 if everything went fine, or an exit code.
     */
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

        if (!is_string($answer)) {
            $io->error('Your command must be a typeof string!');

            return $this->execute($input, $output);
        }

        // validate command characters
        if (!preg_match('/^\w+[0-9]*$/', $answer)) {
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
        $terminalConfigFile = $this->getTerminalProfileFilePath();

        // when there was no config file found
        if ($terminalConfigFile === false) {
            $io->block([
                "😕 We couldn't found a valid terminal profile config file!",
                "If you still want to make this work you can add the following alias to your terminal config `alias {$answer}='~/.composer/vendor/bin/phpUnitWrapper`"
            ], null, 'fg=white;bg=red', '  ', true);

            return Command::FAILURE;
        }

        $io->block([
            "Follow these steps: ",
            "• Run the following command `nano {$terminalConfigFile}`",
            "• Add the following code `alias {$answer}='~/.composer/vendor/bin/phpUnitWrapper'`",
            "• Run the following command `source {$terminalConfigFile}`",
            "🎉 You can now use `{$answer}` as command to run your tests in all your projects!"
        ], null, 'success', '  ', true);

        return Command::SUCCESS;
    }

    /**
     * @return string|false
     */
    private function getTerminalProfileFilePath()
    {
        try {
            switch (rtrim((string)shell_exec('echo $SHELL'))) {
                case '/bin/zsh':
                    return '~/.zshrc';
                case '/bin/bash':
                    return '~/.bashrc';

                default:
                    return false;
            }
        } catch (\Throwable $th) {
            return false;
        }
    }
}
