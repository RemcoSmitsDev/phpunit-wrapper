<?php

namespace Remcosmits\PhpunitWrapper\Services;

final class PhpUnitWrapperService
{
    private const PRINTER_CLASS = 'NunoMaduro\\Collision\\Adapters\\Phpunit\\Printer';

    /**
     * @var string
     */
    private static string $relativePath;

    /**
     * @var string[]
     */
    private static array $params = [
        '--colors=always',
        '--do-not-cache-result',
        "--printer='" . self::PRINTER_CLASS . "'",
    ];

    /**
     * @param string $relativePath
     * @return void
     */
    public static function register(string $relativePath): void
    {
        self::$relativePath = $relativePath;

        self::addConfigurationFileParam();

        echo self::wrapPhpUnitWithFormatter();
    }

    /**
     * @return void
     */
    private static function addConfigurationFileParam(): void
    {
        self::$params[] = "--configuration='" . self::getCommandCalledFromDirectory() . "/phpunit.xml.dist'";
    }

    /**
     * @return string
     */
    private static function getCommandCalledFromDirectory(): string
    {
        return dirname((string)exec('pwd'));
    }

    /**
     * @return string|false
     */
    private static function getPhpUnitRelativePath()
    {
        // check if vendor dir exists
        if (!file_exists(self::$relativePath . '/vendor/bin/phpunit')) {
            shell_exec('cd ' . self::$relativePath . ' && composer install');
        }

        return realpath(self::$relativePath . "/vendor/bin/phpunit");
    }

    /**
     * @return string|null|false
     */
    private static function wrapPhpUnitWithFormatter()
    {
        return shell_exec(
            self::getPhpUnitRelativePath() . " " . implode(' ', self::$params)
        );
    }
}
