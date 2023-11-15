<?php

declare(strict_types=1);

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.0.0
 * Year: 2023
 */

namespace celionatti\Voltage\Resolver;

/**
 * ==============================================
 * ==================           =================
 * Asset Resolver Class
 * ==================           =================
 * ==============================================
 */

class AssetResolver
{
    private $basePath;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    public function getAssetPath($assetName)
    {
        // Sanitize the asset name to prevent directory traversal attacks
        $sanitizedAssetName = preg_replace('/\.\.\//', '', $assetName);

        // Combine the base path with the asset name
        return $this->basePath . DIRECTORY_SEPARATOR . $sanitizedAssetName;
    }
}