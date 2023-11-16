<?php

declare(strict_types=1);

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.0.0
 * Year: 2023
 */

if (!file_exists(__DIR__ . '/constants.php')) {
    die("Constants File Not Found!");
}

require __DIR__ . '/constants.php';

if (!defined('URL_ROOT')) {
    define('URL_ROOT', $_ENV["URL_ROOT"]);
}

if (!defined('ENABLE_BLADE')) {
    define('ENABLE_BLADE', false);
}

if (!defined('ENABLE_TWIG')) {
    define('ENABLE_TWIG', false);
}

if (!defined('CONFIG_ROOT')) {
    define('CONFIG_ROOT', "configs/config.json");
}

if (!defined('DEBUG')) {
    define('DEBUG', true);
}

if (!defined('VOLT_DATABASE')) {
    define('VOLT_DATABASE', "volt_database");
}