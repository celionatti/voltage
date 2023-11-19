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

class DefaultTemplateEngine implements TemplateEngineInterface
{
    protected array $data = [];

    public function getFileContent(string $filePath): string
    {
        return file_get_contents($filePath);
    }

    public function replaceContentPlaceholder(string $content, string $view): string
    {
        return str_replace('@yield(\'content\')', '<?php $this->includeTemplate(\'' . $view . '\'); ?>', $content);
    }

    public function writeToFile(string $filePath, string $content): void
    {
        file_put_contents($filePath, $content);
    }
}
