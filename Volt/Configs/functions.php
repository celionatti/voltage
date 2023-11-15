<?php

declare(strict_types=1);

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.0.0
 * Year: 2023
 */


function rootDir()
{
    // Get the current file's directory
    $currentDirectory = __DIR__;

    // Navigate up the directory tree until you reach the project's root
    while (!file_exists($currentDirectory . '/vendor')) {
        // Go up one level
        $currentDirectory = dirname($currentDirectory);

        // Check if you have reached the filesystem root (to prevent infinite loop)
        if ($currentDirectory === '/') {
            echo "Error: Project root not found.\n";
            exit(1);
        }
    }

    return $currentDirectory;
}

function addAction($hook, $callback, $priority = 10)
{
    global $actions;

    $actions[$hook][] = array(
        'callback' => $callback,
        'priority' => $priority
    );
}

function doAction($hook, $args = array())
{
    global $actions;

    if (isset($actions[$hook])) {
        // Sort actions by priority
        usort($actions[$hook], function ($a, $b) {
            return $a['priority'] - $b['priority'];
        });

        foreach ($actions[$hook] as $action) {
            call_user_func_array($action['callback'], $args);
        }
    }
}

function addFilter($hook, $callback, $priority = 10)
{
    global $filters;

    $filters[$hook][] = array(
        'callback' => $callback,
        'priority' => $priority
    );
}

function doFilter($hook, $value, $args = array())
{
    global $filters;

    if (isset($filters[$hook])) {
        // Sort filters by priority
        usort($filters[$hook], function ($a, $b) {
            return $a['priority'] - $b['priority'];
        });

        foreach ($filters[$hook] as $filter) {
            $value = call_user_func_array($filter['callback'], array_merge(array($value), $args));
        }
    }

    return $value;
}

function get_package_folders($packages_folder = 'packages/', $filter = null, $includeInfo = false)
{
    $result = [];

    // Ensure the packages folder path ends with a directory separator
    $packages_folder = rtrim($packages_folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

    $folders = scandir(rootDir() . DIRECTORY_SEPARATOR . $packages_folder);

    foreach ($folders as $folder) {
        $folderPath = rootDir() . DIRECTORY_SEPARATOR . $packages_folder . $folder;

        if ($folder != '.' && $folder != '..' && is_dir($folderPath)) {
            // Check if the package meets the filtering criteria
            if ($filter === null || call_user_func($filter, $folderPath)) {
                $packageInfo = [
                    'name' => $folder,
                    'path' => $folderPath,
                    'files' => [],
                    'subfolders' => [],
                ];

                // Include additional information about the plugin if requested
                if ($includeInfo) {
                    $packageInfo['files'] = get_package_files($folderPath);
                    $packageInfo['subfolders'] = get_package_subfolders($folderPath);
                }

                $result[] = $packageInfo;
            }
        }
    }

    return $result;
}

// Helper function to get files within a package folder
function get_package_files($packagePath)
{
    $files = scandir($packagePath);
    $result = [];

    foreach ($files as $file) {
        $filePath = $packagePath . DIRECTORY_SEPARATOR . $file;

        if (is_file($filePath)) {
            $result[] = $file;
        }
    }

    return $result;
}

// Helper function to get subfolders within a package folder
function get_package_subfolders($packagePath)
{
    $subfolders = scandir($packagePath);
    $result = [];

    foreach ($subfolders as $subfolder) {
        $subfolderPath = $packagePath . DIRECTORY_SEPARATOR . $subfolder;

        if ($subfolder != '.' && $subfolder != '..' && is_dir($subfolderPath)) {
            $result[] = $subfolder;
        }
    }

    return $result;
}

/**
 * For displaying a color message, on the screen or in the console.
 *
 * @param string $message
 * @param boolean $die
 * @param boolean $timestamp
 * @param string $level
 * @return void
 */
function consoleLogger(string $message, bool $die = false, bool $timestamp = true, string $level = 'info'): void
{
    $output = '';

    if ($timestamp) {
        $output .= "[" . date("Y-m-d H:i:s") . "] - ";
    }

    $output .= ucfirst($message) . PHP_EOL;

    switch ($level) {
        case 'info':
            $output = "\033[0;32m" . $output; // Green color for info
            break;
        case 'warning':
            $output = "\033[0;33m" . $output; // Yellow color for warning
            break;
        case 'error':
            $output = "\033[0;31m" . $output; // Red color for error
            break;
        default:
            break;
    }

    $output .= "\033[0m"; // Reset color

    echo $output;
    ob_flush();

    if ($die) {
        die();
    }
}

function dd($value): void
{
    echo <<<HTML
    <html>
    <head>
        <style>
            body {
                margin: 0;
                padding: 0;
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                background-color: #E6F7FF;
            }

            .dd-container {
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
            }

            .dd-box {
                background-color: #FFF;
                border: 1px solid #E0E0E0;
                border-radius: 5px;
                padding: 20px;
                text-align: center;
                max-width: 80%;
                overflow-x: auto;
            }

            h2 {
                text-transform: uppercase;
                color: #333;
                font-weight: bold;
                font-size: 24px;
            }

            pre {
                background-color: #000;
                color: lightgreen;
                margin: 5px;
                padding: 10px;
                border: 3px solid teal;
                white-space: pre-wrap;
                font-weight: bold;
                font-size: 18px;
            }
        </style>
    </head>
    <body>
        <div class="dd-container">
            <div class="dd-box">
                <h2>PHPStrike - Dump and Die</h2>
                <pre>
HTML;

    var_dump($value);

    echo <<<HTML
                </pre>
            </div>
        </div>
    </body>
    </html>
HTML;
    die;
}

