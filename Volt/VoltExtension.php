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
 * Volt Extension Class
 * ==================           =================
 * ==============================================
 */

class VoltExtension
{
    private $requiredExtensions = [];

    private $config = [
        'errorHandler' => 'default', // You can set this to 'exception' for exception-based error handling.
    ];

    public function addRequiredExtension(string $extension): void
    {
        $this->requiredExtensions[] = $extension;
    }

    public function removeRequiredExtension(string $extension): void
    {
        $index = array_search($extension, $this->requiredExtensions);
        if ($index !== false) {
            unset($this->requiredExtensions[$index]);
        }
    }

    public function setErrorHandler(string $handler): void
    {
        $this->config['errorHandler'] = $handler;
    }

    public function checkExtensions(): void
    {
        $notLoaded = $this->getNotLoadedExtensions();

        if (!empty($notLoaded)) {
            $this->handleError($notLoaded);
        }
    }

    private function getNotLoadedExtensions(): array
    {
        $notLoaded = [];

        $defaultExtensions = [
            'gd',
            'mysqli',
            'pdo_mysql',
            'pdo_sqlite',
            'curl',
            'fileinfo',
            'intl',
            'exif',
            'mbstring',
        ];

        $this->requiredExtensions = array_merge($defaultExtensions, $this->requiredExtensions);

        foreach ($this->requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $notLoaded[] = $ext;
            }
        }

        return $notLoaded;
    }

    private function handleError(array $notLoaded): void
    {
        $errorMessage = "Please load the following extensions in your php.ini file: <br>" . implode("<br>", $notLoaded);

        switch ($this->config['errorHandler']) {
            case 'exception':
                throw new VoltException($errorMessage);
            default:
                dd("{$errorMessage} Missing Extensions.");
        }
    }
}