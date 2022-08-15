<?php

namespace Remcosmits\PhpunitWrapper\Services;

final class PhpUnitWrapperService
{
    private const PRINTER_CLASS = 'NunoMaduro\\Collision\\Adapters\\Phpunit\\Printer';

    /**
     * @var string[]
     */
    private static array $params = [
        '--colors=always',
        '--do-not-cache-result',
        "--printer='" . self::PRINTER_CLASS . "'",
    ];

    /**
     * @return void
     */
    public static function register(): void
    {
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
        $dir = realpath(dirname(__DIR__) . '/../');

        // check if vendor dir exists
        if (!file_exists($dir . '/vendor/bin/phpunit')) {
            shell_exec('cd ' . $dir . ' && composer install');
        }

        return realpath($dir . "/vendor/bin/phpunit");
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
