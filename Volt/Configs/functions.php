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

function dump($value, $die = true)
{
    echo "<pre>";
    var_dump($value);

    if($die) {
        die;
    }
}

