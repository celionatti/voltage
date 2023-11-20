<?php

declare(strict_types=1);

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.0.0
 * Year: 2023
 */

namespace celionatti\Voltage\Resolver;

use celionatti\Voltage\Exceptions\VoltException;

/**
 * ==============================================
 * ==================           =================
 * Path Resolver Class
 * ==================           =================
 * ==============================================
 */

class PathResolver
{
    private $basePath;

    public function __construct($basePath = null)
    {
        if ($basePath === null) {
            $this->basePath = dirname(__DIR__);
        } else {
            $this->basePath = $basePath;
        }
    }

    public function basePath($path = ''): string
    {
        $path = ltrim($path, '/'); // Remove leading slashes from the path
        return $this->basePath . DIRECTORY_SEPARATOR . $path;
    }

    public function configPath($path = ''): string
    {
        $configPath = $this->basePath . DIRECTORY_SEPARATOR . 'config';
        $path = ltrim($path, '/'); // Remove leading slashes from the path
        return $configPath . DIRECTORY_SEPARATOR . $path;
    }

    public function storagePath($path = ''): string
    {
        $storagePath = $this->basePath . DIRECTORY_SEPARATOR . 'storage';
        $path = ltrim($path, '/'); // Remove leading slashes from the path
        return $storagePath . DIRECTORY_SEPARATOR . $path;
    }

    public function routerPath($path = ''): string
    {
        $routerPath = $this->basePath . DIRECTORY_SEPARATOR . 'routes';
        $path = ltrim($path, '/'); // Remove leading slashes from the path

        $fullPath = $routerPath . DIRECTORY_SEPARATOR . $path . ".php";

        if (!file_exists($fullPath)) {
            throw new VoltException("Router path not found: $fullPath");
        }

        return $fullPath;
    }

    public function packageRouterPath(): ?array
    {
        $packagesDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'packages';
        $packages = glob($packagesDir . '/*', GLOB_ONLYDIR);
        $routes = [];

        foreach ($packages as $package) {
            $installJsonFile = $package . '/install.json';
            $pluginRoutesFile = $package . '/routes.php';

            // Check if install.json exists and is readable
            if (file_exists($installJsonFile) && is_readable($installJsonFile)) {
                $installData = json_decode(file_get_contents($installJsonFile), true);

                // Check if the package is marked as active in install.json
                if (isset($installData['active']) && $installData['active'] === true) {
                    // Check if routes.php exists and is readable
                    if (file_exists($pluginRoutesFile) && is_readable($pluginRoutesFile)) {
                        // Add the routes file to the array
                        $routes[] = $pluginRoutesFile;
                    }
                }
            }
        }

        // No active plugin with routes found, return null or the array of routes
        return empty($routes) ? null : $routes;
    }

    public function templatePath($path = ''): string
    {
        $templatePath = $this->basePath . DIRECTORY_SEPARATOR . 'templates';
        $path = ltrim($path, '/'); // Remove leading slashes from the path
        return $templatePath . DIRECTORY_SEPARATOR . $path;
    }

    public function packageTemplatePath($path = ''): string
    {
        $templatePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR . 'templates';
        $path = ltrim($path, '/'); // Remove leading slashes from the path
        return $templatePath . DIRECTORY_SEPARATOR . $path;
    }
}