<?php

namespace Remcosmits\PhpunitWrapper\Services;

use RuntimeException;
use Symfony\Component\Console\Style\SymfonyStyle;

final class PhpUnitWrapperService
{
    private const PRINTER_CLASS = 'NunoMaduro\\Collision\\Adapters\\Phpunit\\Printer';

    /**
     * @var SymfonyStyle
     */
    private static SymfonyStyle $io;

    /**
     * @var string[]
     */
    private static array $params = [
        '--colors=always',
        '--do-not-cache-result',
        "--printer='" . self::PRINTER_CLASS . "'",
    ];

    /**
     * @param SymfonyStyle $io
     * @param array<int, string> $params
     * @return void
     */
    public static function register(SymfonyStyle $io, array $params): void
    {
        self::$io = $io;

        self::addConfigurationFileParam();

        self::$params = [
            ...self::$params,
            ...$params
        ];

        echo self::wrapPhpUnitWithFormatter();
    }

    /**
     * @return void
     */
    private static function addConfigurationFileParam(): void
    {
        $configurationFilePath = self::getCommandCalledFromDirectory() . '/phpunit.xml.dist';

        // check if configuration file exists
        if (file_exists($configurationFilePath)) {
            self::$params[] = "--configuration='{$configurationFilePath}'";
        } else {
            self::$io->error('There was no phpunit configuration file found!');

            self::$params = [
                self::askForTestsFolderPath(),
                ...self::$params
            ];
        }
    }

    /**
     * @return string
     */
    private static function askForTestsFolderPath(): string
    {
        $answer = self::$io->ask('Enter the folder name/path where your tests live');

        if (!is_string($answer) ||
            trim($answer) === '' ||
            !file_exists(self::getCommandCalledFromDirectory() . '/' . trim($answer))
        ) {
            self::$io->error('Invalid tests folder [' . $answer . ']!');

            return self::askForTestsFolderPath();
        }

        return $answer;
    }

    /**
     * @return string
     */
    private static function getCommandCalledFromDirectory(): string
    {
        $fromPath = exec('pwd');

        if (!$fromPath) {
            return dirname(__DIR__) . '/../';
        }

        if (strpos($fromPath, '/src') === false) {
            return $fromPath;
        }

        return dirname($fromPath);
    }

    /**
     * @return string|false
     */
    private static function getPhpUnitRelativePath()
    {
        $relativePath = realpath(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . '..'
        );

        $phpUnitPath = $relativePath . '/vendor/bin/phpunit';

        // check if composer.json file exists
        if (!file_exists($relativePath . DIRECTORY_SEPARATOR . 'composer.json') && !file_exists($phpUnitPath)) {
            self::$io->error(
                'There was no `composer.json` file and `vendor` folder found! Make sure the package is installed correctly!'
            );

            return false;
        }

        // check if vendor dir exists
        if (!file_exists($phpUnitPath)) {
            shell_exec('cd ' . $relativePath . ' && composer install >/dev/null 2>&1');
        }

        return rtrim($phpUnitPath, DIRECTORY_SEPARATOR);
    }

    /**
     * @return string|null|false
     */
    private static function wrapPhpUnitWithFormatter()
    {
        $phpUnitPath = self::getPhpUnitRelativePath();

        if ($phpUnitPath === false) {
            return false;
        }

        return shell_exec(
            self::getPhpUnitRelativePath() . " " . implode(' ', self::$params)
        );
    }
}
