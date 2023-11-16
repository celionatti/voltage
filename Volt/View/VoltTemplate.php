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

    public function __construct(string $viewPath, string $cachePath, array $environment = [])
    {
        $this->viewPath = $viewPath;
        $this->cachePath = $cachePath;
        $this->environment = array_merge([
            'debug' => false,
            'autoescape' => true,
            'cache' => true,
            'functions' => [],
        ], $environment);
    }

    /**
     * Render the specified view with the given data.
     *
     * @param string $view
     * @param array $data
     */
    public function render(string $view, array $data = [])
    {
        try {
            $this->data = array_merge($this->data, $data);
            $compiledFilePath = $this->getCompiledFilePath($view);

            if (!$this->isCacheValid($compiledFilePath)) {
                $compiledContent = $this->compile($view);
                $this->putCompiledContent($compiledFilePath, $compiledContent);
            }

            include $compiledFilePath;
        } catch (VoltException $e) {
            // Handle the exception (log it, display an error message, etc.)
            throw new VoltException('Error rendering view: ' . $e->getMessage());
        }
    }

    /**
     * Check if the cached file is still valid.
     *
     * @param string $compiledFilePath
     * @return bool
     */
    protected function isCacheValid(string $compiledFilePath): bool
    {
        try {
            return $this->environment['cache'] && !$this->isExpired($compiledFilePath);
        } catch (VoltException $e) {
            // Handle the exception (log it, display an error message, etc.)
            throw new VoltException('Error checking cache validity: ' . $e->getMessage());
        }
    }

    protected function compile($view)
    {
        $viewContent = file_get_contents($this->viewPath . '/' . $view . '.volt');

        // Template inheritance: extend and block
        $viewContent = $this->compileExtends($viewContent);
        $viewContent = $this->compileBlocks($viewContent);

        // Perform your custom compilation here
        $compiledContent = $this->compileSyntax($viewContent);

        return $compiledContent;
    }

    /**
     * Perform custom compilation logic.
     *
     * @param string $viewContent
     * @return string
     */
    protected function compileSyntax(string $viewContent): string
    {
        try {
            $compiledContent = preg_replace_callback('/{{\s*(.*?)\s*}}/', function ($matches) {
                return '<?php echo $this->escape(' . $this->compileFunction($matches[1]) . '); ?>';
            }, $viewContent);

            return $compiledContent;
        } catch (VoltException $e) {
            throw new VoltException('Error during syntax compilation: ' . $e->getMessage());
        }
    }

    protected function compileFunction($function)
    {
        // Handle the function syntax
        $tokens = token_get_all('<?php ' . $function);
        $compiled = '';

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $compiled .= $token[1];
            } else {
                $compiled .= $token;
            }
        }

        return $compiled;
    }

    /**
     * Implements template inheritance logic.
     *
     * @param string $viewContent
     * @return string
     */
    protected function compileExtends(string $viewContent): string
    {
        return preg_replace_callback('/' . preg_quote(self::EXTENDS_PREFIX, '/') . '\(\'(.*?)\'\)/', function ($matches) {
            return '<?php $this->extend(\'' . $matches[1] . '\'); ?>';
        }, $viewContent);
    }

    /**
     * Implements block logic.
     *
     * @param string $viewContent
     * @return string
     */
    protected function compileBlocks(string $viewContent): string
    {
        return preg_replace_callback('/' . preg_quote(self::SECTION_PREFIX, '/') . '\(\'(.*?)\'\)/', function ($matches) {
            return '<?php $this->startSection(\'' . $matches[1] . '\'); ?>';
        }, $viewContent);
    }

    protected function extend(string $view)
    {
        try {
            $parentContent = file_get_contents($this->viewPath . '/' . $view . '.volt');

            if ($parentContent === false) {
                throw new VoltException('Error including parent view: Unable to read parent view file');
            }

            $this->data['__parent'] = $parentContent;
        } catch (VoltException $e) {
            throw new VoltException('Error extending view: ' . $e->getMessage());
        }
    }

    protected function startSection($section)
    {
        // Implement section logic here
        // In this example, we buffer the output for the specified section
        ob_start();
    }

    protected function endSection($section)
    {
        // Implement section logic here
        // In this example, we store the buffered output in the data array
        $this->data['__sections'][$section] = ob_get_clean();
    }

    protected function escape($value)
    {
        // Implement a more robust HTML escaping logic here
        return $this->environment['autoescape'] ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
    }

    protected function getCompiledFilePath($view)
    {
        return $this->cachePath . '/' . $view . '.php';
    }

    protected function putCompiledContent(string $compiledFilePath, string $compiledContent)
    {
        try {
            $result = file_put_contents($compiledFilePath, $compiledContent);

            if ($result === false) {
                throw new VoltException('Error writing compiled content to file');
            }
        } catch (VoltException $e) {
            throw new VoltException('Error putting compiled content: ' . $e->getMessage());
        }
    }

    protected function isExpired($compiledFilePath)
    {
        // You may implement a more sophisticated logic to check if the compiled file is expired
        // For simplicity, let's assume it's always expired for now
        return true;
    }

    // Register custom functions for use in the view
    public function registerFunction($name, $callback)
    {
        $this->environment['functions'][$name] = $callback;
    }
}