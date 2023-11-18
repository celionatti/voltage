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

class working
{
    protected $data = [];
    protected $viewPath;
    protected $cachePath;
    protected $environment;

    // Constants for magic strings
    const SECTION_PREFIX = '@section';
    const EXTENDS_PREFIX = '@extends';

    public function __construct(string $viewPath, string $cachePath, array $environment = [])
    {
        $this->viewPath = $viewPath;
        $this->cachePath = $cachePath;
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
            $content =preg_replace('/{{\$*(.+?)\$*}}/', '<?php echo $1; ?>', $content);
            $content = preg_replace('/@if\(\$*(.*?)\$*\)/','<?php if($1): ?>', $content);
            $content = str_replace('@endif', '<?php endif; ?>', $content);
            $content = preg_replace('/@foreach\(\$*(.*?)\$*\)/','<?php foreach($1): ?>', $content);
            $content = str_replace('@endforeach', '<?php endforeach; ?>', $content);

            /**ob_start();
            eval('?>'.$content);
            $final = ob_get_clean();
            echo $final;**/
            $cache = $this->cachePath . $view . '.php';
            file_put_contents($cache, $content);
            require $cache;
        } catch (VoltException $e) {
            throw new VoltException('Error rendering view: ' . $e->getMessage());
        }
    }
}