<?php

declare(strict_types=1);

namespace celionatti\Voltage\View;

use Exception;

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.2.0
 * Year: 2023
 */

class VoltTemplate
{
    // ... (previous class code)

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
        } catch (Exception $e) {
            // Handle the exception (log it, display an error message, etc.)
            echo 'Error rendering view: ' . $e->getMessage();
        }
    }

    // ... (previous class code)

    protected function isCacheValid(string $compiledFilePath): bool
    {
        try {
            return $this->environment['cache'] && !$this->isExpired($compiledFilePath);
        } catch (Exception $e) {
            // Handle the exception (log it, display an error message, etc.)
            throw new Exception('Error checking cache validity: ' . $e->getMessage());
        }
    }

    // ... (previous class code)

    protected function compileSyntax(string $viewContent): string
    {
        try {
            $compiledContent = preg_replace_callback('/{{\s*(.*?)\s*}}/', function ($matches) {
                return '<?php echo $this->escape(' . $this->compileFunction($matches[1]) . '); ?>';
            }, $viewContent);

            return $compiledContent;
        } catch (Exception $e) {
            throw new Exception('Error during syntax compilation: ' . $e->getMessage());
        }
    }

    // ... (previous class code)

    protected function extend(string $view)
    {
        try {
            $parentContent = file_get_contents($this->viewPath . '/' . $view . '.volt');

            if ($parentContent === false) {
                throw new Exception('Error including parent view: Unable to read parent view file');
            }

            $this->data['__parent'] = $parentContent;
        } catch (Exception $e) {
            throw new Exception('Error extending view: ' . $e->getMessage());
        }
    }

    // ... (previous class code)

    protected function putCompiledContent(string $compiledFilePath, string $compiledContent)
    {
        try {
            $result = file_put_contents($compiledFilePath, $compiledContent);

            if ($result === false) {
                throw new Exception('Error writing compiled content to file');
            }
        } catch (Exception $e) {
            throw new Exception('Error putting compiled content: ' . $e->getMessage());
        }
    }

    // ... (previous class code)
}
