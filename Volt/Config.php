<?php

declare(strict_types=1);

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.0.0
 * Year: 2023
 */

namespace celionatti\Voltage;

use celionatti\Voltage\Exceptions\VoltException;

/**
 * ==============================================
 * ==================           =================
 * Config Class
 * ==================           =================
 * ==============================================
 */

class Config
{
    private static array $config = [];
    private static array $cache = [];

    public static function load(string $configFile)
    {
        $fileExtension = pathinfo($configFile, PATHINFO_EXTENSION);

        if (!file_exists($configFile)) {
            throw new VoltException("Configuration file not found: $configFile");
        }

        switch ($fileExtension) {
            case 'json':
                self::$config = json_decode(file_get_contents($configFile), true);
                break;
            case 'ini':
                self::$config = parse_ini_file($configFile, true);
                break;
            default:
                throw new VoltException("Unsupported configuration file format: $fileExtension");
        }
    }

    public static function get($key, $default = null)
    {
        // Check if the value is already in the cache
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        // Retrieve the value from the $_ENV global
        $value = isset($_ENV[$key]) ? $_ENV[$key] : false;

        // Check if the environment variable is set and not empty
        if ($value !== false && $value !== '') {
            // Use the value directly without additional validation
            $validatedValue = $value;
        } else {
            // Use the default value if the environment variable is not set or empty
            $validatedValue = $default;
        }

        // Cache the validated value
        self::$cache[$key] = $validatedValue;

        return $validatedValue;
    }


    public static function isDebugMode()
    {
        return self::get('debug_mode', false);
    }
}