<?php

declare(strict_types=1);

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.0.0
 * Year: 2023
 */

namespace celionatti\Voltage\View;


/**
 * ==============================================
 * ==================           =================
 * VoltTemplate Class
 * ==================           =================
 * ==============================================
 */

interface TemplateEngineInterface
{
    public function getFileContent(string $filePath): string;

    public function replaceContentPlaceholder(string $content, string $view): string;

    public function writeToFile(string $filePath, string $content): void;
}