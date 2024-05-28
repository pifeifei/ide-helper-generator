<?php
//declare(strict_types=1);

/**
 * most of parts is borrowed from zendframework/zend-code
 * https://github.com/zendframework/zend-code
 *
 * This source is aimed for hack to override zend-code.
 *
 * @license New BSD, code from Zend Framework
 * https://github.com/zendframework/zend-code/blob/master/LICENSE.md
 */

namespace IDEHelperGenerator\ZendCode;

use ReflectionFunction;
use Zend\Code\Generator\AbstractGenerator;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Reflection\ClassReflection;

class FunctionGenerator extends AbstractGenerator
{
    public static function generateByPrototypeArray(array $prototype)
    {
        $line = 'function' . ' ' . $prototype['name'] . '(';
        $args = [];
        foreach ($prototype['arguments'] as $name => $argument) {
            $type = ($argument['type'] && $argument['type'] !== 'resource') ? "{$argument['type']} " : "";
            $argsLine = $type . ($argument['by_ref'] ? '&' : '') . '$' . $name;
            if (!$argument['required']) {
                $argsLine .= ' = ' . var_export($argument['default'], true);
            }
            $args[] = $argsLine;
        }
        $line .= implode(', ', $args);
        $line .= ')' . ($prototype['return'] !== 'mixed' ? ": {$prototype['return']}" : "") . ' {}';

        return $line;
    }


    /**
     * Build a Code Generation Php Object from a Function Reflection
     *
     * @param  FunctionReflection $functionReflection
     * @return self
     */
    public static function fromReflection(FunctionReflection $functionReflection)
    {

    }

    /**
     * Generate from array
     *
     * @param  ReflectionFunction[] $array
     * @return self
     */
    public static function fromArray(array $array)
    {
        return new self;
    }

    public function generate()
    {
        // TODO: Implement generate() method.
    }
}
