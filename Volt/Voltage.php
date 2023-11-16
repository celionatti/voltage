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
        $this->extension = new VoltExtension();
        $this->extension->checkExtensions();
        self::$voltage = $this;
        $this->pathResolver = new PathResolver(rootDir());
        $this->assetResolver = new AssetResolver(URL_ROOT);

        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);

        $this->config = new Config();
        $this->config::load($this->pathResolver->base_path(CONFIG_ROOT));

        load_packages('packages/', function ($packagePath) {
            // Custom filter: Include only packages containing a specific file
            return file_exists($packagePath . DIRECTORY_SEPARATOR . 'install.json');
        }, true, ['styles', 'scripts', 'images']);
    }

    private function require_files()
    {
        return [
            require __DIR__ . "/Configs/functions.php",
            require __DIR__ . "/Configs/plugins.php",
            require __DIR__ . "/Configs/globals.php",
            require rootDir() . "/configs/load.php",
        ];
    }

    public function include_package_routes(): void
    {
        $routes = $this->pathResolver->package_router_path();

        if ($routes !== null) {
            foreach ($routes as $routeFile) {
                require $routeFile;
            }
        }
    }

    public function run()
    {
        try {
            $this->router->resolve();
        } catch (VoltException $e) {
            throw $e;
        }
    }
}