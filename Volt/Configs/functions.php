<?php

declare(strict_types=1);

use celionatti\Voltage\Voltage;
use celionatti\Voltage\View\VoltTemplate;

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
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                font-family: 'Arial', sans-serif;
                margin: 0;
                padding: 0;
            }

            .sf-dump-container {
                display: grid;
                grid-template-columns: 1fr 1fr; /* Two equal-width columns */
                height: 100vh;
            }

            .sf-dump {
                font: 13px Menlo, Monaco, monospace;
                direction: ltr;
                text-align: left;
                white-space: pre;
                word-wrap: normal;
                background: #282828;
                color: #eeeeee;
                line-height: 1.2;
                margin: 0;
                padding: 16px;
                border-radius: 5px;
                overflow: hidden;
                z-index: 100000;
                grid-column: 2; /* Specify the column for the dump content */
            }

            .sf-dump-two {
                font: 12px 'Arial', sans-serif; /* Use a standard font for readability */
                background: #f0f0f0; /* Change to your desired background color */
                color: #333; /* Change to your desired text color */
                line-height: 1.2;
                margin: 0;
                padding: 16px;
                border-radius: 5px;
                overflow: hidden;
                z-index: 100000;
                grid-column: 1; /* Specify the column for the dump content */
            }

            .sf-dump span {
                display: inline;
            }

            .sf-dump a {
                color: #52e3f6;
                text-decoration: none;
            }

            .sf-dump a:hover {
                text-decoration: underline;
            }

            .sf-dump a:visited {
                color: #5e84ea;
            }

            .sf-dump .sf-dump-public {
                color: #568f3e;
            }

            .sf-dump .sf-dump-protected {
                color: #568f3e;
            }

            .sf-dump .sf-dump-private {
                color: #568f3e;
            }

            .sf-dump .sf-dump-ellipsis {
                font-weight: bold;
                color: #52e3f6;
            }

            .sf-dump .sf-dump-numeric {
                color: #a0a0a0;
            }

            .sf-dump .sf-dump-null {
                color: #aa0d91;
            }

            .sf-dump .sf-dump-bool {
                color: #4d73bf;
            }

            .sf-dump .sf-dump-resource {
                color: #6f42c1;
            }

            .sf-dump .sf-dump-string {
                color: #df9355;
            }

            .sf-dump .sf-dump-key {
                color: #a0a0a0;
            }

            .sf-dump .sf-dump-meta {
                color: #b729d9;
            }

            .sf-dump .sf-dump-public.sf-dump-ellipsis,
            .sf-dump .sf-dump-protected.sf-dump-ellipsis,
            .sf-dump .sf-dump-private.sf-dump-ellipsis {
                color: #52e3f6;
            }

            .sf-dump .sf-dump-sql {
                color: #52e3f6;
            }
        </style>
    </head>
    <body>
        <div class="sf-dump-container">
            <div class="sf-dump-two"></div>
            <pre class="sf-dump">
                <h4 class="sf-dump-public"><a>DETAILS</a></h4>
HTML;

    var_dump($value);

    echo <<<HTML
            </pre>
        </div>
    </body>
    </html>
HTML;

    die;
}

function dump($value, $die = true)
{
    echo "<pre style='background:#282828; color:#52e3f6; padding:16px;border-radius:6px;overflow:hidden;word-wrap:normal;font: 12px Menlo, Monaco, monospace;text-align: left;white-space: pre;direction: ltr;line-height: 1.2;z-index: 100000;margin:0;font-size:15px;margin-bottom:5px;'>";
    var_dump($value);
    echo "</pre>";

    if ($die) {
        die;
    }
}

function getAssetsDirectory($directory): string
{
    return Voltage::$voltage->assetResolver->getAssetPath("assets" . $directory);
}

function getPackageAssets(string $package): string
{
    return getAssetsDirectory(DIRECTORY_SEPARATOR . "packages" . DIRECTORY_SEPARATOR . $package);
}

function get_stylesheet(string $path): string
{
    return getAssetsDirectory(DIRECTORY_SEPARATOR . "css" . DIRECTORY_SEPARATOR . $path);
}

function get_script($path): string
{
    return getAssetsDirectory(DIRECTORY_SEPARATOR . "js" . DIRECTORY_SEPARATOR . $path);
}

function partials(string $path, $params = [])
{
    // $view = new VoltTemplate();

    // $view->partial($path, $params);
}

