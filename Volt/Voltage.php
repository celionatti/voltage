<?php

declare(strict_types=1);

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.0.0
 * Year: 2023
 */

namespace celionatti\Voltage;

/**
 * ==============================================
 * ==================           =================
 * Voltage Class
 * ==================           =================
 * ==============================================
 */

class Voltage
{
    public function __construct()
    {
        $this->require_files();

        $packages = get_package_folders('packages/', function ($packagePath) {
            // Custom filter: Include only packages containing a specific file
            return file_exists($packagePath . DIRECTORY_SEPARATOR . 'install.json');
        }, true);

        var_dump($packages);
    }

    private function require_files()
    {
        return [
            require __DIR__ . "/Configs/functions.php",
            require __DIR__ . "/Configs/globals.php",
            // require rootDir() . "/configs/load.php",
            // require rootDir() . "/utils/functions.php"
        ];
    }

    public function run()
    {

    }
}