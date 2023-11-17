<?php

declare(strict_types=1);

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.0.0
 * Year: 2023
 */

namespace celionatti\Voltage\View;

use celionatti\Voltage\Exceptions\VoltException;

/**
 * ==============================================
 * ==================           =================
 * VoltTemplate Class
 * ==================           =================
 * ==============================================
 */

class VoltTemplate
{
    protected $data = [];
    protected $viewPath;
    protected $cachePath;
    protected $environment;

    // Constants for magic strings
    const SECTION_PREFIX = '@section';
    const EXTENDS_PREFIX = '@extends';

    public function __construct(string $viewPath, array $environment = [])
    {
        $this->viewPath = $viewPath;
        $this->environment = array_merge([
            'debug' => false,
            'autoescape' => true,
            'functions' => [],
        ], $environment);
    }

    public function render(string $view, array $data = [])
    {
        try {
            extract($data);
            $content = file_get_contents($this->viewPath . $view . '.php');
            $content = str_replace('{{', '<?php', $content);
            $content = str_replace('}}', '?>', $content);
            $content =preg_replace('/{{\$*(.+?)\$*}}/', '<?php echo $1; ?>', $content);
            $content = preg_replace('/@if\(\$*(.*?)\$*\)/','<?php if($1): ?>', $content);
            $content = str_replace('@endif', '<?php endif; ?>', $content);

            ob_start();
            eval('?>'.$content);
            $final = ob_get_clean();
            echo $final;
        } catch (VoltException $e) {
            throw new VoltException('Error rendering view: ' . $e->getMessage());
        }
    }
}