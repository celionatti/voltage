<?php

declare(strict_types=1);

namespace celionatti\Voltage\View;

use celionatti\Voltage\Exceptions\VoltException;

class VoltTemplate
{
    protected $data = [];
    protected $viewPath;
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
            $this->data = array_merge($this->data, $data);

            $viewContent = $this->compile($view);
            eval('?>' . $viewContent);
        } catch (VoltException $e) {
            throw new VoltException('Error rendering view: ' . $e->getMessage());
        }
    }

    protected function compile($view)
    {
        $viewContent = file_get_contents($this->viewPath . '/' . $view . '.volt');
        $viewContent = $this->compileExtends($viewContent);
        $viewContent = $this->compileBlocks($viewContent);

        // Perform your custom compilation here
        $compiledContent = $this->compileSyntax($viewContent);

        return $compiledContent;
    }

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
                list($id, $text) = $token;

                switch ($id) {
                    case T_VARIABLE:
                    case T_STRING:
                        $compiled .= $text;
                        break;
                    case T_WHITESPACE:
                        $compiled .= ' ';
                        break;
                    default:
                        $compiled .= $text;
                }
            } else {
                $compiled .= $token;
            }
        }

        return $compiled;
    }

    protected function compileExtends(string $viewContent): string
    {
        return preg_replace_callback('/' . preg_quote(self::EXTENDS_PREFIX, '/') . '\(\'(.*?)\'\)/', function ($matches) {
            return '<?php $this->extend(\'' . $matches[1] . '\'); ?>';
        }, $viewContent);
    }

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
        ob_start();
    }

    protected function endSection($section)
    {
        $this->data['__sections'][$section] = ob_get_clean();
    }

    protected function escape($value)
    {
        return $this->environment['autoescape'] ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
    }

    public function registerFunction($name, $callback)
    {
        $this->environment['functions'][$name] = $callback;
    }
}
