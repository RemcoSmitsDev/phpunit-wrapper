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
     * @param array<string, string|null> $options
     * @return void
     */
    public static function register(array $options): void
    {
        self::addConfigurationFileParam();

        foreach (array_filter($options, fn($option) => $option) as $key => $value) {
            self::$params[] = "--{$key}='{$value}'";
        }

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
        $fromPath = exec('pwd');

        if (!$fromPath) {
            return (string)realpath(dirname(__DIR__) . '/../');
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
        $path = realpath(dirname(__DIR__) . '/../');

        // check if vendor dir exists
        if (!file_exists($path . '/vendor/bin/phpunit')) {
            shell_exec('cd ' . $path . ' && composer install');
        }

        return realpath($path . "/vendor/bin/phpunit");
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
