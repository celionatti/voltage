<?php

declare(strict_types=1);

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.0.0
 * Year: 2023
 */

namespace celionatti\Voltage;

use celionatti\Voltage\HTTP\Request;
use celionatti\Voltage\HTTP\Response;
use celionatti\Voltage\Router\Router;
use celionatti\Voltage\Resolver\PathResolver;
use celionatti\Voltage\Resolver\AssetResolver;
use celionatti\Voltage\Exceptions\VoltException;

/**
 * ==============================================
 * ==================           =================
 * Voltage Class
 * ==================           =================
 * ==============================================
 */

class Voltage
{
    public Config $config;
    public ?Controller $controller;
    public VoltExtension $extension;
    public static Voltage $voltage;
    public PathResolver $pathResolver;
    public AssetResolver $assetResolver;
    public Request $request;
    public Response $response;
    public Router $router;

    public function __construct()
    {
        $this->require_files();
        $this->initializeComponents();
        self::$voltage = $this;
        $this->loadConfiguration();
    }

    private function require_files()
    {
        return [
            require __DIR__ . "/Configs/functions.php",
            require __DIR__ . "/Configs/globals.php",
            require __DIR__ . "/Configs/plugins.php",
            require __DIR__ . "/Configs/plugin-functions.php",
            require rootDir() . "/configs/load.php",
        ];
    }

    private function initializeComponents(): void
    {
        $this->extension = new VoltExtension();
        $this->extension->checkExtensions();
        $this->pathResolver = new PathResolver(rootDir());
        $this->assetResolver = new AssetResolver(URL_ROOT);
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
    }

    private function loadConfiguration(): void
    {
        $this->config = new Config();
        $this->config::load($this->pathResolver->basePath(CONFIG_ROOT));
    }

    private function loadPackages(): void
    {
        loadPackages('packages/', function ($packagePath) {
            // Custom filter: Include only packages containing a specific file
            return file_exists($packagePath . DIRECTORY_SEPARATOR . 'install.json');
        }, true, ['assets', 'resources']);
    }

    /**
     * Run the application.
     */
    public function run(): void
    {
        try {
            $this->loadPackages();
            $this->router->resolve();
        } catch (VoltException $e) {
            // Handle VoltException
            // Log or rethrow if necessary
            throw new VoltException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get the instance of Voltage.
     */
    public static function getInstance(): Voltage
    {
        return self::$voltage;
    }
}