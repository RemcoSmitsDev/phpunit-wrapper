<?php

namespace Remcosmits\PhpunitWrapper;

final class PhpUnitWrapper
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
        return exec('pwd');
    }

    /**
     * @return string
     */
    private static function getPhpUnitRelativePath(): string
    {
        return realpath(__DIR__ . "/../vendor/bin/phpunit");
    }

    /**
     * @return string
     */
    private static function wrapPhpUnitWithFormatter(): string
    {
        return shell_exec(
            self::getPhpUnitRelativePath() . " " . implode(' ', self::$params)
        );
    }
}