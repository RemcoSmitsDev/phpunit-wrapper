<?php

namespace Remcosmits\PhpunitWrapper\Services;

use Remcosmits\PhpunitWrapper\Exceptions\Services\InvalidInstallationException;
use Remcosmits\PhpunitWrapper\Exceptions\Services\InvalidPhpUnitRelativePathException;
use Remcosmits\PhpunitWrapper\Exceptions\Services\InvalidTerminalResponseException;
use Symfony\Component\Console\Style\SymfonyStyle;
use NunoMaduro\Collision\Adapters\Phpunit\Printer;

final class PhpUnitWrapperService
{
    private const PRINTER_CLASS = Printer::class;

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
     * @param string[] $params
     * @return void
     *
     * @throws InvalidInstallationException
     * @throws InvalidPhpUnitRelativePathException
     * @throws InvalidTerminalResponseException
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

        if (
            !is_string($answer) ||
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
     * @return string
     *
     * @throws InvalidInstallationException
     * @throws InvalidPhpUnitRelativePathException
     */
    private static function getPhpUnitRelativePath(): string
    {
        $relativePath = realpath(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . '..'
        );

        if ($relativePath === false) {
            throw new InvalidPhpUnitRelativePathException();
        }

        $phpUnitPath = $relativePath . '/vendor/bin/phpunit';

        // check if composer.json file exists
        if (!file_exists($relativePath . DIRECTORY_SEPARATOR . 'composer.json') && !file_exists($phpUnitPath)) {
            throw new InvalidInstallationException();
        }

        // check if vendor dir exists
        if (!file_exists($phpUnitPath)) {
            shell_exec('cd ' . $relativePath . ' && composer install >/dev/null 2>&1');
        }

        return rtrim($phpUnitPath, DIRECTORY_SEPARATOR);
    }

    /**
     * @return string
     *
     * @throws InvalidInstallationException
     * @throws InvalidTerminalResponseException
     * @throws InvalidPhpUnitRelativePathException
     */
    private static function wrapPhpUnitWithFormatter(): string
    {
        $response = shell_exec(
            self::getPhpUnitRelativePath() . " " . implode(' ', self::$params)
        );

        if ($response === null || $response === false) {
            throw new InvalidTerminalResponseException();
        }

        return $response;
    }
}
