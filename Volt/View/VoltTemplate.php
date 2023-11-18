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

    private $extension;

    private $layout = 'default'; // Default layout

    protected $registeredFunctions = [];

    public function __construct(string $viewPath, string $cachePath, array $environment = [])
    {
        $this->viewPath = $viewPath;
        $this->cachePath = $cachePath;
        $this->extension = ".volt";

        $predefinedKeys = [
            'debug',
            'functions',
        ];

        // Check for predefined keys in the user-supplied environment
        foreach ($predefinedKeys as $key) {
            if (array_key_exists($key, $environment)) {
                throw new VoltException("The key '{$key}' cannot be overridden in the environment array.");
            }
        }

        $this->environment = array_merge([
            'debug' => true,
            'autoescape' => true,
            'functions' => [
                'moneyFormat' => 'moneyFormat',
            ],
        ], $environment);

        // Register user-defined functions
        $this->registerFunctions($this->environment['functions']);

        // Ensure cache path exists, create it if not
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }

        // Check if debugging is enabled and create the cache directory
        if ($this->environment['debug']) {
            // Ensure cache path exists, create it if not
            if (!is_dir($cachePath)) {
                mkdir($cachePath, 0755, true);
            }
        }
    }

    public function setLayout(string $layout)
    {
        $this->layout = $layout;
    }

    public function getLayout(): string
    {
        return $this->layout;
    }
    
    public function render(string $view, array $data = [])
    {
        try {
            $this->data = array_merge($this->data, $data);

            // Check if template caching is enabled
            if ($this->environment['debug'] || !$this->isCacheValid($view)) {
                $this->compileTemplate($view);
            }

            // Load the layout template dynamically
            $layoutContent = file_get_contents($this->viewPath . 'layouts/' . $this->getLayout() . '.volt');

            // Replace the content placeholder with the actual view content
            $layoutContent = str_replace('@yield(\'content\')', '<?php $this->includeTemplate(\'' . $view . '\'); ?>', $layoutContent);

            // Save the layout content to a temporary file
            $layoutCache = $this->getCacheFilename('layout_' . $this->getLayout());
            file_put_contents($layoutCache, $layoutContent);

            // Include the temporary layout file
            $this->includeTemplate('layout_' . $this->getLayout());
        } catch (VoltException $e) {
            throw new VoltException('Error rendering view: ' . $e->getMessage());
        }
    }

    protected function compileTemplate(string $view)
    {
        $content = file_get_contents($this->viewPath . $view . $this->extension);
        $content = preg_replace('/{{\$*(.+?)\$*}}/', $this->environment['autoescape'] ? '<?php echo htmlspecialchars($1, ENT_QUOTES, \'UTF-8\'); ?>' : '<?php echo $1; ?>', $content);


        // Handle @foreach loops
        $content = preg_replace_callback('/@foreach\(\$*(.*?)\$*\)/', function ($matches) {
            return '<?php foreach(' . $matches[1] . '): ?>';
        }, $content);

        // Handle @endforeach
        $content = str_replace('@endforeach', '<?php endforeach; ?>', $content);

        // Handle @if statements
        $content = preg_replace('/@if\(\$*(.*?)\$*\)/', '<?php if($1): ?>', $content);
        $content = str_replace('@else', '<?php else: ?>', $content);
        $content = str_replace('@elseif', '<?php elseif(', $content);
        $content = str_replace('@endif', '<?php endif; ?>', $content);

        // Handle method calls
        $content = preg_replace_callback('/@method\(\$*(.*?)\$*\)/', function ($matches) {
            return '<?php echo $this->' . $matches[1] . '(); ?>';
        }, $content);

        // Handle function calls
        $content = preg_replace_callback('/@function\(\$*(.*?)\$*\)/', function ($matches) {
            if (isset($this->registeredFunctions[$matches[1]])) {
                return '<?php echo ' . $this->registeredFunctions[$matches[1]] . '($1); ?>';
            }
            return $matches[0]; // Return the original function call if not found
        }, $content);

        // Add more template compilation logic here, e.g., for sections, inheritance, etc.

        $cache = $this->getCacheFilename($view);
        file_put_contents($cache, $content);
    }

    protected function includeTemplate(string $view)
    {
        $cache = $this->getCacheFilename($view);
        extract($this->data);

        require $cache;
    }

    protected function getCacheFilename(string $view): string
    {
        return $this->cachePath . md5($view) . '.php';
    }

    protected function isCacheValid(string $view): bool
    {
        $cache = $this->getCacheFilename($view);

        // Check if the cache file exists and is not older than the source file
        return file_exists($cache) && filemtime($cache) >= filemtime($this->viewPath . $view . $this->extension);
    }

    // Example method definition
    public function customMethod()
    {
        return 'This is a custom method!';
    }

    // Example helper function for currency conversion
    public function moneyFormat($amount)
    {
        // You can replace this with your actual currency conversion logic
        return '$' . number_format($amount, 2);
    }

    // Example custom function
    public function customFunction()
    {
        return 'This is a custom function!';
    }

    // Register user-defined functions
    public function registerFunctions(array $functions)
    {
        $this->registeredFunctions = array_merge($this->registeredFunctions, $functions);
    }
}