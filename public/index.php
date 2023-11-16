<?php

declare(strict_types=1);

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.0.0
 * Year: 2023
 */

/**
 * ===========================================
 * ================        ===================
 * Voltage Index Page
 * ================        ===================
 * ===========================================
 */


use Dotenv\Dotenv;
use celionatti\Voltage\Voltage;


require dirname(__DIR__) . "/vendor/autoload.php";

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$voltage = new Voltage();

require $voltage->pathResolver->router_path("web");
$routes = $voltage->pathResolver->package_router_path();

if ($routes !== null) {
    foreach ($routes as $routeFile) {
        require $routeFile;
    }
}

$voltage->run();