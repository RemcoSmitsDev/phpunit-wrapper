<?php

declare(strict_types=1);

namespace Remcosmits\PhpunitWrapper\Services;

use Remcosmits\PhpunitWrapper\Exceptions\Services\InvalidInstallationException;
use Remcosmits\PhpunitWrapper\Exceptions\Services\InvalidPhpUnitRelativePathException;
use Remcosmits\PhpunitWrapper\Exceptions\Services\InvalidTerminalResponseException;
use NunoMaduro\Collision\Adapters\Phpunit\Printer;

final class PhpUnitWrapperService
{
    private const PRINTER_CLASS = Printer::class;

    /**
     * @param array<int, string> $params
     *
     * @throws InvalidInstallationException
     * @throws InvalidPhpUnitRelativePathException
     * @throws InvalidTerminalResponseException
     */
    public static function register(array $params): void
    {
        echo self::wrapPhpUnitWithFormatter($params);
    }

    /** @return array<int, string|null> */
    private static function getDefaultParams(): array
    {
        return [
            self::addConfigurationFileParam(),
            '--colors=always',
            '--do-not-cache-result',
            sprintf('--printer="%s"', self::PRINTER_CLASS)
        ];
    }

    private static function addConfigurationFileParam(): ?string
    {
        $configurationFilePath = self::getCommandCalledFromDirectory() . '/phpunit.xml.dist';

        // check if configuration file exists
        if (file_exists($configurationFilePath)) {
            return sprintf('--configuration="%s"', $configurationFilePath);
        }

        return null;
    }

    private static function getCommandCalledFromDirectory(): string
    {
        $fromPath = getcwd();

        if ($fromPath === false) {
            return sprintf('%s/../', dirname(__DIR__));
        }

        return $fromPath;
    }

    /**
     * @throws InvalidInstallationException
     * @throws InvalidPhpUnitRelativePathException
     */
    private static function getPhpUnitRelativePath(): string
    {
        $relativePath = realpath(
            sprintf(
                '%s%s..',
                dirname(__DIR__),
                DIRECTORY_SEPARATOR
            )
        );

        if ($relativePath === false) {
            throw new InvalidPhpUnitRelativePathException();
        }

        $phpUnitPath = $relativePath . '/vendor/bin/phpunit';

        // check if composer.json file exists
        if (
            file_exists($relativePath . DIRECTORY_SEPARATOR . 'composer.json') === false &&
            file_exists($phpUnitPath) === false
        ) {
            throw new InvalidInstallationException();
        }

        // check if vendor dir exists
        if (file_exists($phpUnitPath) === false) {
            shell_exec(
                sprintf('cd %s && composer install >/dev/null 2>&1', $relativePath)
            );
        }

        return rtrim($phpUnitPath, DIRECTORY_SEPARATOR);
    }

    /** @return array<string, mixed>|null */
    private static function getPackageJson(): ?array
    {
        $path = sprintf('%s/composer.json', self::getCommandCalledFromDirectory());

        if (file_exists($path) === false) {
            return null;
        }

        return json_decode(
            file_get_contents($path) ?: '',
            true
        );
    }

    /** @return array<string, mixed> */
    private static function getScripts(): array
    {
        $packageJson = self::getPackageJson();

        $scripts = $packageJson['scripts'] ?? [];

        return is_array($scripts) ? $scripts : [];
    }

    /** @return array{envs: string, params: string} */
    private static function parsePHPUnitCliCommand(string $script): array
    {
        preg_match(
            '/^\s*(?<envs>(?:[A-z0-9\-]+\=[A-z0-9\-]+\s)+).*(?<params>(?<=\/bin\/phpunit\s)(.*))$/',
            trim($script),
            $match
        );

        return [
            'envs' => ltrim($match['envs'] ?? ''),
            'params' => trim($match['params'] ?? '')
        ];
    }

    /**
     * @param array<int, string> $params
     *
     * @throws InvalidInstallationException
     * @throws InvalidTerminalResponseException
     * @throws InvalidPhpUnitRelativePathException
     */
    private static function wrapPhpUnitWithFormatter(array $params): string
    {
        $envs = '';

        $scrips = self::getScripts();

        if (isset($params[0], $scrips[$params[0]])) {
            $info = self::parsePHPUnitCliCommand($scrips[$params[0]]);

            $envs = empty($info['envs']) ? '' : $info['envs'];

            if (empty($info['params']) === false) {
                $params[0] = $info['params'];
            } else {
                unset($params[0]);
            }
        }

        $params = array_filter([...$params, ...self::getDefaultParams()]);

        $response = shell_exec(
            sprintf(
                '%s%s %s',
                $envs,
                self::getPhpUnitRelativePath(),
                implode(' ', $params)
            )
        );

        if (empty($response)) {
            throw new InvalidTerminalResponseException();
        }

        return $response;
    }
}
